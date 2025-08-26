#!/bin/bash

# Laravel Production Deployment Script
# This script handles zero-downtime deployments

set -e

# Configuration
APP_NAME="fapp"
DEPLOY_PATH="/home/u636722041/domains/fappify.in"
REPO_URL="https://github.com/moryapant/piffy.git"
PHP_FPM_SERVICE="php8.2-fpm"
WEB_SERVER_SERVICE="nginx"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" >&2
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check if running as correct user
check_user() {
    if [ "$EUID" -eq 0 ]; then
        error "Do not run this script as root. Run as the web server user."
        exit 1
    fi
}

# Create deployment directory structure
setup_directories() {
    log "Setting up directory structure..."
    
    mkdir -p "$DEPLOY_PATH"/{releases,shared/{storage/app,storage/framework,storage/logs,bootstrap/cache}}
    
    # Create shared directories
    mkdir -p "$DEPLOY_PATH/shared/storage/app/public"
    mkdir -p "$DEPLOY_PATH/shared/storage/framework"/{cache,sessions,testing,views}
    mkdir -p "$DEPLOY_PATH/shared/storage/logs"
    mkdir -p "$DEPLOY_PATH/shared/bootstrap/cache"
}

# Deploy function
deploy() {
    local RELEASE_NAME=$(date +%Y%m%d%H%M%S)
    local RELEASE_PATH="$DEPLOY_PATH/releases/$RELEASE_NAME"
    
    log "Starting deployment: $RELEASE_NAME"
    
    # Create release directory
    mkdir -p "$RELEASE_PATH"
    cd "$RELEASE_PATH"
    
    # Clone repository
    log "Cloning repository..."
    git clone --depth 1 --branch main "$REPO_URL" .
    
    # Remove git directory
    rm -rf .git
    
    # Install PHP dependencies
    log "Installing PHP dependencies..."
    
    # Check platform requirements first
    log "Checking platform requirements..."
    if ! /usr/local/bin/composer check-platform-reqs --no-dev; then
        warning "Platform requirements check failed, proceeding with ignore-platform-reqs"
        /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
    else
        /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
    fi
    
    # Install Node dependencies and build assets
    log "Building frontend assets..."
    npm ci --only=production
    npm run build
    
    # Remove node_modules to save space
    rm -rf node_modules
    
    # Create symlinks to shared directories
    log "Creating symlinks..."
    ln -nfs "$DEPLOY_PATH/shared/.env" "$RELEASE_PATH/.env"
    
    # Remove default storage and bootstrap/cache directories
    rm -rf "$RELEASE_PATH/storage"
    rm -rf "$RELEASE_PATH/bootstrap/cache"
    
    # Create symlinks
    ln -nfs "$DEPLOY_PATH/shared/storage" "$RELEASE_PATH/storage"
    ln -nfs "$DEPLOY_PATH/shared/bootstrap/cache" "$RELEASE_PATH/bootstrap/cache"
    
    # Set permissions
    log "Setting permissions..."
    find "$RELEASE_PATH" -type f -exec chmod 644 {} \;
    find "$RELEASE_PATH" -type d -exec chmod 755 {} \;
    chmod -R 775 "$DEPLOY_PATH/shared/storage"
    chmod -R 775 "$DEPLOY_PATH/shared/bootstrap/cache"
    
    # Laravel optimizations
    log "Running Laravel optimizations..."
    cd "$RELEASE_PATH"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    # Run migrations
    log "Running database migrations..."
    php artisan migrate --force
    
    # Create backup of current release
    if [ -L "$DEPLOY_PATH/current" ]; then
        CURRENT_RELEASE=$(readlink "$DEPLOY_PATH/current" | xargs basename)
        log "Backing up current release: $CURRENT_RELEASE"
        cp -r "$DEPLOY_PATH/current" "$DEPLOY_PATH/backup-$CURRENT_RELEASE-$(date +%Y%m%d%H%M%S)" 2>/dev/null || true
    fi
    
    # Switch to new release (atomic operation)
    log "Switching to new release..."
    ln -nfs "$RELEASE_PATH" "$DEPLOY_PATH/current"
    
    # Restart services
    log "Restarting services..."
    sudo systemctl reload "$PHP_FPM_SERVICE"
    sudo systemctl reload "$WEB_SERVER_SERVICE"
    
    # Health check
    log "Performing health check..."
    sleep 3
    if curl -f -s "https://fappify.in/health" > /dev/null; then
        log "✅ Health check passed!"
    else
        error "❌ Health check failed!"
        rollback
        exit 1
    fi
    
    # Clean up old releases (keep last 5)
    log "Cleaning up old releases..."
    cd "$DEPLOY_PATH/releases"
    ls -t | tail -n +6 | xargs -r rm -rf
    
    log "🎉 Deployment completed successfully!"
    info "Release: $RELEASE_NAME"
    info "Path: $RELEASE_PATH"
}

# Rollback function
rollback() {
    log "Starting rollback..."
    
    cd "$DEPLOY_PATH/releases"
    RELEASES=($(ls -t))
    
    if [ ${#RELEASES[@]} -lt 2 ]; then
        error "No previous release to rollback to"
        exit 1
    fi
    
    PREVIOUS_RELEASE=${RELEASES[1]}
    log "Rolling back to: $PREVIOUS_RELEASE"
    
    ln -nfs "$DEPLOY_PATH/releases/$PREVIOUS_RELEASE" "$DEPLOY_PATH/current"
    
    # Restart services
    sudo systemctl reload "$PHP_FPM_SERVICE"
    sudo systemctl reload "$WEB_SERVER_SERVICE"
    
    # Health check
    sleep 3
    if curl -f -s "https://fappify.in/health" > /dev/null; then
        log "✅ Rollback completed successfully!"
    else
        error "❌ Rollback health check failed!"
    fi
}

# Main script
main() {
    case "$1" in
        "deploy")
            check_user
            setup_directories
            deploy
            ;;
        "rollback")
            check_user
            rollback
            ;;
        "status")
            if [ -L "$DEPLOY_PATH/current" ]; then
                CURRENT_RELEASE=$(readlink "$DEPLOY_PATH/current" | xargs basename)
                info "Current release: $CURRENT_RELEASE"
                info "Release path: $(readlink -f "$DEPLOY_PATH/current")"
            else
                warning "No current deployment found"
            fi
            ;;
        *)
            echo "Usage: $0 {deploy|rollback|status}"
            echo
            echo "Commands:"
            echo "  deploy   - Deploy the latest code from main branch"
            echo "  rollback - Rollback to the previous release"
            echo "  status   - Show current deployment status"
            exit 1
            ;;
    esac
}

main "$@"