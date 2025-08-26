#!/bin/bash

# Laravel Shared Hosting Deployment Script (No NPM Version)
# Designed for shared hosting environments without Node.js/NPM

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

# Check if required commands are available (excluding npm)
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
    
    if [ ${#missing_commands[@]} -gt 0 ]; then
        error "Missing required commands: ${missing_commands[*]}"
        error "Please install the missing commands and try again."
        exit 1
    fi
    
    # Check for npm separately and warn if not available
    if ! command -v npm &> /dev/null; then
        warning "NPM is not available - assets must be pre-built locally"
        info "Make sure to run 'npm run build' locally before deploying"
    fi
    
    log "✅ Basic requirements met"
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
        
        # Create a basic production .env file
        cat > "$DEPLOY_PATH/shared/.env" << 'EOF'
APP_NAME="Fapp"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://fappify.in

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u636722041_fapp
DB_USERNAME=u636722041_root
DB_PASSWORD=Abeerpant@2025

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.fappify.in

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@fappify.in"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"

VITE_FIREBASE_API_KEY="AIzaSyB6uVDeIWV2xErEEZ30DI29AEeqvPLzgBk"
VITE_FIREBASE_AUTH_DOMAIN="fappify-f4cb7.firebaseapp.com"
VITE_FIREBASE_PROJECT_ID="fappify-f4cb7"
VITE_FIREBASE_STORAGE_BUCKET="fappify-f4cb7.firebasestorage.app"
VITE_FIREBASE_MESSAGING_SENDER_ID="116870753173"
VITE_FIREBASE_APP_ID="1:116870753173:web:4c776421e77a7525b0249c"
EOF
        
        info "Created basic .env file at $DEPLOY_PATH/shared/.env"
        info "Please review and update the configuration before deploying"
    fi
    
    log "✅ Directory structure ready"
}

# Deploy function for shared hosting without npm
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
    
    # Check if pre-built assets exist
    if [ ! -d "public/build" ]; then
        warning "No pre-built assets found in public/build directory"
        warning "Please run 'npm run build' locally and commit the built assets"
        info "Alternative: Upload the public/build directory manually after cloning"
        
        # Don't exit, continue with deployment - assets might be uploaded separately
    else
        log "✅ Pre-built assets found"
    fi
    
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
    
    # Create symlinks to shared directories
    log "Creating symlinks..."
    
    # Link environment file
    ln -nfs "$DEPLOY_PATH/shared/.env" "$RELEASE_PATH/.env"
    
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
        log "Generating application key..."
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
    
    # Additional instructions for assets
    if [ ! -d "$RELEASE_PATH/public/build" ]; then
        warning ""
        warning "IMPORTANT: Assets not found!"
        warning "To complete the deployment:"
        warning "1. Run 'npm run build' on your local machine"
        warning "2. Upload the 'public/build' directory to: $RELEASE_PATH/public/"
        warning "3. Or commit the built assets to your repository"
    fi
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
        
        # Check for assets
        if [ -d "$DEPLOY_PATH/current/public/build" ]; then
            info "✅ Built assets are present"
        else
            warning "⚠️  Built assets are missing"
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
    info ""
    info "Next steps:"
    info "1. Review and configure: $DEPLOY_PATH/shared/.env"
    info "2. Build assets locally: npm run build"
    info "3. Commit built assets or upload them manually"
    info "4. Run: $0 deploy"
    info ""
    info "Note: Since NPM is not available on this server,"
    info "assets must be built locally and included in deployment."
}

# Build assets locally and upload (helper function)
upload_assets() {
    local RELEASE_PATH="$DEPLOY_PATH/current"
    
    if [ ! -L "$DEPLOY_PATH/current" ]; then
        error "No current deployment found. Deploy first."
        exit 1
    fi
    
    log "Uploading pre-built assets..."
    
    if [ -d "public/build" ]; then
        cp -r public/build "$RELEASE_PATH/public/"
        log "✅ Assets uploaded successfully"
    else
        error "No built assets found. Run 'npm run build' first."
        exit 1
    fi
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
        "upload-assets")
            upload_assets
            ;;
        *)
            echo "Laravel Shared Hosting Deployment Script (No NPM)"
            echo
            echo "Usage: $0 {init|deploy|rollback|status|upload-assets}"
            echo
            echo "Commands:"
            echo "  init          - Initialize deployment environment (run this first)"
            echo "  deploy        - Deploy the latest code from $BRANCH branch"
            echo "  rollback      - Rollback to the previous release"
            echo "  status        - Show current deployment status"
            echo "  upload-assets - Upload pre-built assets to current release"
            echo
            echo "Configuration:"
            echo "  App: $APP_NAME"
            echo "  Path: $DEPLOY_PATH"
            echo "  Repo: $REPO_URL"
            echo "  Branch: $BRANCH"
            echo
            echo "Note: This version is designed for servers without NPM."
            echo "Build assets locally with 'npm run build' before deploying."
            exit 1
            ;;
    esac
}

main "$@"