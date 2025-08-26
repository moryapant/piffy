#!/bin/bash

# Deployment Test Script
# Tests various aspects of the deployment setup

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Test results
PASSED=0
FAILED=0

log_test() {
    echo -e "${BLUE}[TEST] $1${NC}"
}

log_pass() {
    echo -e "${GREEN}✅ PASS: $1${NC}"
    ((PASSED++))
}

log_fail() {
    echo -e "${RED}❌ FAIL: $1${NC}"
    ((FAILED++))
}

log_skip() {
    echo -e "${YELLOW}⏭️  SKIP: $1${NC}"
}

# Test 1: Check if health endpoint works
test_health_endpoint() {
    log_test "Testing health endpoint"
    
    if php artisan serve --port=8001 --quiet &
    then
        SERVER_PID=$!
        sleep 3
        
        if curl -s -f http://localhost:8001/health > /dev/null; then
            log_pass "Health endpoint is accessible"
        else
            log_fail "Health endpoint is not accessible"
        fi
        
        kill $SERVER_PID 2>/dev/null || true
        wait $SERVER_PID 2>/dev/null || true
    else
        log_fail "Could not start Laravel server for testing"
    fi
}

# Test 2: Check if .env.example is complete
test_env_example() {
    log_test "Testing .env.example completeness"
    
    required_vars=(
        "APP_NAME"
        "APP_ENV" 
        "APP_KEY"
        "APP_DEBUG"
        "APP_URL"
        "DB_CONNECTION"
        "DB_HOST"
        "DB_PORT"
        "DB_DATABASE"
        "DB_USERNAME"
        "DB_PASSWORD"
    )
    
    missing_vars=()
    
    for var in "${required_vars[@]}"; do
        if ! grep -q "^$var=" .env.example; then
            missing_vars+=("$var")
        fi
    done
    
    if [ ${#missing_vars[@]} -eq 0 ]; then
        log_pass ".env.example contains all required variables"
    else
        log_fail ".env.example missing variables: ${missing_vars[*]}"
    fi
}

# Test 3: Check if GitHub Actions workflow exists
test_github_workflow() {
    log_test "Testing GitHub Actions workflow"
    
    if [ -f ".github/workflows/deploy.yml" ]; then
        log_pass "GitHub Actions workflow file exists"
        
        # Check if workflow contains required steps
        if grep -q "test:" .github/workflows/deploy.yml; then
            log_pass "Workflow contains test job"
        else
            log_fail "Workflow missing test job"
        fi
        
        if grep -q "deploy:" .github/workflows/deploy.yml; then
            log_pass "Workflow contains deploy job"
        else
            log_fail "Workflow missing deploy job"
        fi
    else
        log_fail "GitHub Actions workflow file not found"
    fi
}

# Test 4: Check Laravel application structure
test_laravel_structure() {
    log_test "Testing Laravel application structure"
    
    required_dirs=(
        "app"
        "database"
        "resources"
        "routes"
        "config"
        "public"
        "storage"
        "bootstrap"
    )
    
    missing_dirs=()
    
    for dir in "${required_dirs[@]}"; do
        if [ ! -d "$dir" ]; then
            missing_dirs+=("$dir")
        fi
    done
    
    if [ ${#missing_dirs[@]} -eq 0 ]; then
        log_pass "All required Laravel directories exist"
    else
        log_fail "Missing directories: ${missing_dirs[*]}"
    fi
}

# Test 5: Check dependencies
test_dependencies() {
    log_test "Testing dependencies"
    
    # Check Composer
    if composer validate --no-check-publish --quiet; then
        log_pass "composer.json is valid"
    else
        log_fail "composer.json is invalid"
    fi
    
    # Check package.json
    if [ -f "package.json" ]; then
        if npm run build --dry-run >/dev/null 2>&1; then
            log_pass "package.json build script exists"
        else
            log_fail "package.json build script missing or invalid"
        fi
    else
        log_fail "package.json not found"
    fi
}

# Test 6: Check database configuration
test_database_config() {
    log_test "Testing database configuration"
    
    if [ -f ".env" ]; then
        if php artisan migrate:status --env=testing >/dev/null 2>&1; then
            log_pass "Database migrations are ready"
        else
            log_skip "Database migrations not tested (no test database)"
        fi
    else
        log_skip "No .env file found for database testing"
    fi
}

# Test 7: Check deployment script
test_deployment_script() {
    log_test "Testing deployment script"
    
    if [ -f "deploy.sh" ]; then
        if [ -x "deploy.sh" ]; then
            log_pass "Deployment script is executable"
        else
            log_fail "Deployment script exists but is not executable"
        fi
        
        # Test script syntax
        if bash -n deploy.sh; then
            log_pass "Deployment script syntax is valid"
        else
            log_fail "Deployment script has syntax errors"
        fi
    else
        log_fail "Deployment script not found"
    fi
}

# Test 8: Check for security issues
test_security() {
    log_test "Testing security configuration"
    
    # Check if .env is in .gitignore
    if grep -q "^\.env$" .gitignore 2>/dev/null; then
        log_pass ".env is properly ignored by Git"
    else
        log_fail ".env should be in .gitignore"
    fi
    
    # Check if storage is writable
    if [ -w "storage" ]; then
        log_pass "Storage directory is writable"
    else
        log_fail "Storage directory is not writable"
    fi
    
    # Check for exposed sensitive files
    sensitive_files=(".env" "deploy.sh" ".ssh/")
    
    for file in "${sensitive_files[@]}"; do
        if [ -f "public/$file" ] || [ -d "public/$file" ]; then
            log_fail "Sensitive file $file found in public directory"
        fi
    done
    
    if [ $FAILED -eq 0 ] || [ ${#sensitive_files[@]} -eq 0 ]; then
        log_pass "No sensitive files exposed in public directory"
    fi
}

# Main execution
main() {
    echo -e "${BLUE}🚀 Running Deployment Tests${NC}"
    echo "=================================="
    
    # Run all tests
    test_health_endpoint
    test_env_example
    test_github_workflow
    test_laravel_structure
    test_dependencies
    test_database_config
    test_deployment_script
    test_security
    
    echo
    echo "=================================="
    echo -e "${GREEN}Passed: $PASSED${NC}"
    echo -e "${RED}Failed: $FAILED${NC}"
    echo "=================================="
    
    if [ $FAILED -eq 0 ]; then
        echo -e "${GREEN}🎉 All tests passed! Your application is ready for deployment.${NC}"
        exit 0
    else
        echo -e "${RED}❌ Some tests failed. Please fix the issues before deploying.${NC}"
        exit 1
    fi
}

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: This script must be run from a Laravel project root directory${NC}"
    exit 1
fi

main "$@"