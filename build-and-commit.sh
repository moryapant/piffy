#!/bin/bash

# Build assets and commit them for deployment to shared hosting
# This script builds the assets locally and commits them to the repository

set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo "Error: Not in a git repository"
    exit 1
fi

# Check if package.json exists
if [ ! -f "package.json" ]; then
    echo "Error: package.json not found"
    exit 1
fi

log "Building production assets..."

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    log "Installing npm dependencies..."
    npm install
fi

# Build production assets
log "Running production build..."
npm run build

# Check if build was successful
if [ ! -d "public/build" ]; then
    echo "Error: Build failed - public/build directory not found"
    exit 1
fi

log "✅ Build completed successfully"

# Show what will be committed
log "Built assets to be committed:"
find public/build -type f | head -10
if [ $(find public/build -type f | wc -l) -gt 10 ]; then
    info "... and $(( $(find public/build -type f | wc -l) - 10 )) more files"
fi

# Check git status
CHANGES=$(git status --porcelain)
if [ -z "$CHANGES" ]; then
    info "No changes to commit"
    exit 0
fi

# Add built assets to git
log "Adding built assets to git..."
git add public/build

# Check if there are other changes that shouldn't be committed
OTHER_CHANGES=$(git status --porcelain | grep -v "public/build" || true)
if [ -n "$OTHER_CHANGES" ]; then
    warning "Other changes detected (not being committed):"
    echo "$OTHER_CHANGES"
    warning "Only built assets in public/build will be committed"
fi

# Commit the built assets
COMMIT_MESSAGE="Build production assets for deployment

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>"

log "Committing built assets..."
git commit -m "$COMMIT_MESSAGE"

log "🎉 Built assets committed successfully!"
info "You can now push to your repository and deploy on the server"
info ""
info "Next steps:"
info "1. git push origin main"
info "2. On server: ./shared-hosting-deploy-no-npm.sh deploy"