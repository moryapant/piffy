#!/bin/bash

# Laravel Shared Hosting Deployment Script
# Designed for shared hosting environments like cPanel

set -e

# Configuration
APP_NAME="fapp"
DEPLOY_PATH="/home/u636722041/domains/fappify.in"
REPO_URL="https://github.com/moryapant/piffy.git"
BRANCH="main"

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

# Check if required commands are available
check_requirements() {
    log "Checking requirements..."
    
    local missing_commands=()
    
    if ! command -v git &> /dev/null; then
        missing_commands+=("git")
    fi
    
    if ! command -v php &> /dev/null; then
        missing_commands+=("php")
    fi
    
    if ! command -v composer &> /dev/null; then
        missing_commands+=("composer")
    fi
    
    if ! command -v npm &> /dev/null; then
        missing_commands+=("npm")
    fi
    
    if [ ${#missing_commands[@]} -gt 0 ]; then
        error "Missing required commands: ${missing_commands[*]}"
        error "Please install the missing commands and try again."
        exit 1
    fi
    
    log "✅ All requirements met"
}

# Create deployment directory structure for shared hosting
setup_directories() {
    log "Setting up directory structure..."
    
    # Main deployment directories
    mkdir -p "$DEPLOY_PATH"/{releases,shared}
    
    # Shared directories that persist across deployments
    mkdir -p "$DEPLOY_PATH/shared/storage"/{app,framework,logs}
    mkdir -p "$DEPLOY_PATH/shared/storage/framework"/{cache,sessions,testing,views}
    mkdir -p "$DEPLOY_PATH/shared/storage/app/public"
    mkdir -p "$DEPLOY_PATH/shared/bootstrap/cache"
    
    # Create .env file if it doesn't exist
    if [ ! -f "$DEPLOY_PATH/shared/.env" ]; then
        warning "Creating sample .env file - please configure it with your production settings"
        cp .env.example "$DEPLOY_PATH/shared/.env" 2>/dev/null || {
            error "Could not create .env file. Please create $DEPLOY_PATH/shared/.env manually"
            exit 1
        }
    fi
    
    log "✅ Directory structure ready"
}

# Deploy function for shared hosting
deploy() {
    local RELEASE_NAME=$(date +%Y%m%d%H%M%S)
    local RELEASE_PATH="$DEPLOY_PATH/releases/$RELEASE_NAME"
    
    log "Starting deployment: $RELEASE_NAME"
    
    # Create release directory
    mkdir -p "$RELEASE_PATH"
    cd "$RELEASE_PATH"
    
    # Clone repository
    log "Cloning repository..."
    git clone --depth 1 --branch "$BRANCH" "$REPO_URL" . || {
        error "Failed to clone repository"
        exit 1
    }
    
    # Remove git directory to save space
    rm -rf .git
    
    # Link environment file early so it's available during asset building
    log "Linking environment file..."
    ln -nfs "$DEPLOY_PATH/shared/.env" "$RELEASE_PATH/.env"
    
    # Install PHP dependencies
    log "Installing PHP dependencies..."
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader --no-interaction || {
            warning "Composer install failed, trying with --ignore-platform-reqs"
            composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
        }
    else
        error "Composer is not available"
        exit 1
    fi
    
    # Install Node dependencies and build assets
    log "Installing Node dependencies..."
    if [ -f "package.json" ]; then
        npm ci || npm install
        
        log "Building frontend assets..."
        npm run build || {
            error "Asset build failed"
            exit 1
        }
        
        # Remove node_modules to save space on shared hosting
        rm -rf node_modules
    else
        warning "No package.json found, skipping Node.js dependencies"
    fi
    
    # Create symlinks to shared directories
    log "Creating symlinks..."
    
    # Environment file already linked earlier for asset building
    
    # Remove default storage and bootstrap/cache directories
    rm -rf "$RELEASE_PATH/storage"
    rm -rf "$RELEASE_PATH/bootstrap/cache"
    
    # Create symlinks to shared directories
    ln -nfs "$DEPLOY_PATH/shared/storage" "$RELEASE_PATH/storage"
    ln -nfs "$DEPLOY_PATH/shared/bootstrap/cache" "$RELEASE_PATH/bootstrap/cache"
    
    # Set permissions (adjusted for shared hosting)
    log "Setting permissions..."
    find "$RELEASE_PATH" -type f -exec chmod 644 {} \; 2>/dev/null || true
    find "$RELEASE_PATH" -type d -exec chmod 755 {} \; 2>/dev/null || true
    chmod -R 775 "$DEPLOY_PATH/shared/storage" 2>/dev/null || true
    chmod -R 775 "$DEPLOY_PATH/shared/bootstrap/cache" 2>/dev/null || true
    
    # Laravel optimizations
    log "Running Laravel optimizations..."
    cd "$RELEASE_PATH"
    
    # Generate application key if not exists
    if ! grep -q "APP_KEY=base64:" "$DEPLOY_PATH/shared/.env"; then
        php artisan key:generate --force
    fi
    
    # Clear and cache configurations
    php artisan config:clear
    php artisan cache:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Run migrations
    log "Running database migrations..."
    php artisan migrate --force || {
        error "Database migrations failed"
        exit 1
    }
    
    # Create storage link
    php artisan storage:link || warning "Storage link creation failed (may already exist)"
    
    # Backup current release if it exists
    if [ -L "$DEPLOY_PATH/public_html" ]; then
        CURRENT_RELEASE=$(readlink "$DEPLOY_PATH/public_html" | sed 's|.*/releases/||' | sed 's|/public||')
        if [ -n "$CURRENT_RELEASE" ] && [ "$CURRENT_RELEASE" != "$RELEASE_NAME" ]; then
            log "Backing up current release: $CURRENT_RELEASE"
        fi
    fi
    
    # Switch to new release (atomic operation)
    log "Switching to new release..."
    
    # For shared hosting, we need to point public_html to the public folder of our release
    ln -nfs "$RELEASE_PATH/public" "$DEPLOY_PATH/public_html"
    
    # Also create a 'current' symlink for easier management
    ln -nfs "$RELEASE_PATH" "$DEPLOY_PATH/current"
    
    # Basic health check
    log "Performing health check..."
    if [ -f "$DEPLOY_PATH/public_html/index.php" ]; then
        log "✅ Application files are accessible"
    else
        error "❌ Application files are not accessible"
        exit 1
    fi
    
    # Clean up old releases (keep last 3 for shared hosting space constraints)
    log "Cleaning up old releases..."
    cd "$DEPLOY_PATH/releases"
    ls -t | tail -n +4 | xargs -r rm -rf
    
    log "🎉 Deployment completed successfully!"
    info "Release: $RELEASE_NAME"
    info "Path: $RELEASE_PATH"
    info "Public URL should be accessible via your domain"
}

# Rollback function for shared hosting
rollback() {
    log "Starting rollback..."
    
    if [ ! -d "$DEPLOY_PATH/releases" ]; then
        error "No releases directory found"
        exit 1
    fi
    
    cd "$DEPLOY_PATH/releases"
    RELEASES=($(ls -t))
    
    if [ ${#RELEASES[@]} -lt 2 ]; then
        error "No previous release to rollback to"
        exit 1
    fi
    
    PREVIOUS_RELEASE=${RELEASES[1]}
    PREVIOUS_PATH="$DEPLOY_PATH/releases/$PREVIOUS_RELEASE"
    
    log "Rolling back to: $PREVIOUS_RELEASE"
    
    # Switch symlinks
    ln -nfs "$PREVIOUS_PATH/public" "$DEPLOY_PATH/public_html"
    ln -nfs "$PREVIOUS_PATH" "$DEPLOY_PATH/current"
    
    log "✅ Rollback completed successfully!"
    info "Active release: $PREVIOUS_RELEASE"
}

# Show current deployment status
status() {
    if [ -L "$DEPLOY_PATH/current" ]; then
        CURRENT_RELEASE=$(readlink "$DEPLOY_PATH/current" | xargs basename)
        info "Current release: $CURRENT_RELEASE"
        info "Release path: $(readlink -f "$DEPLOY_PATH/current")"
        info "Public path: $(readlink -f "$DEPLOY_PATH/public_html")"
        
        # Show available releases
        if [ -d "$DEPLOY_PATH/releases" ]; then
            info "Available releases:"
            cd "$DEPLOY_PATH/releases"
            ls -la | grep "^d" | awk '{print "  - " $9}' | grep -v "^\.$\|^\.\.$"
        fi
    else
        warning "No current deployment found"
    fi
    
    # Show disk usage
    info "Deployment disk usage:"
    du -sh "$DEPLOY_PATH" 2>/dev/null || warning "Could not check disk usage"
}

# Initialize deployment environment
init() {
    log "Initializing deployment environment..."
    
    check_requirements
    setup_directories
    
    log "✅ Initialization complete!"
    info "Next steps:"
    info "1. Configure your database settings in: $DEPLOY_PATH/shared/.env"
    info "2. Run: $0 deploy"
}

# Main script
main() {
    case "$1" in
        "init")
            init
            ;;
        "deploy")
            check_requirements
            setup_directories
            deploy
            ;;
        "rollback")
            rollback
            ;;
        "status")
            status
            ;;
        *)
            echo "Laravel Shared Hosting Deployment Script"
            echo
            echo "Usage: $0 {init|deploy|rollback|status}"
            echo
            echo "Commands:"
            echo "  init     - Initialize deployment environment (run this first)"
            echo "  deploy   - Deploy the latest code from $BRANCH branch"
            echo "  rollback - Rollback to the previous release"
            echo "  status   - Show current deployment status"
            echo
            echo "Configuration:"
            echo "  App: $APP_NAME"
            echo "  Path: $DEPLOY_PATH"
            echo "  Repo: $REPO_URL"
            echo "  Branch: $BRANCH"
            exit 1
            ;;
    esac
}

main "$@"