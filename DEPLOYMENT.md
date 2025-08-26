# Production Deployment Guide

This guide covers deploying your Laravel application to production using GitHub Actions.

## Prerequisites

- Ubuntu/Debian server with PHP 8.2+, MySQL, Nginx
- SSH access to your server
- GitHub repository with your code
- Domain pointing to your server

## Server Setup

### 1. Server Requirements

```bash
# Install required packages
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath \
    nginx mysql-server composer nodejs npm git curl unzip

# Install Composer globally
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 2. Create Application User

```bash
# Create user for the application
sudo useradd -m -s /bin/bash fapp
sudo usermod -aG www-data fapp

# Set up SSH keys for deployment user
sudo -u fapp mkdir -p /home/fapp/.ssh
sudo -u fapp chmod 700 /home/fapp/.ssh
```

### 3. Directory Structure

```bash
# Create application directories
sudo -u fapp mkdir -p /var/www/fapp/{releases,shared,backup}
sudo -u fapp mkdir -p /var/www/fapp/shared/storage/{app,framework,logs}
sudo -u fapp mkdir -p /var/www/fapp/shared/storage/framework/{cache,sessions,views}
sudo -u fapp mkdir -p /var/www/fapp/shared/bootstrap/cache

# Set permissions
sudo chown -R fapp:www-data /var/www/fapp
sudo chmod -R 755 /var/www/fapp
sudo chmod -R 775 /var/www/fapp/shared/storage
sudo chmod -R 775 /var/www/fapp/shared/bootstrap/cache
```

### 4. Environment Configuration

```bash
# Create production .env file
sudo -u fapp nano /var/www/fapp/shared/.env
```

Copy your production environment variables:

```env
APP_NAME="Fapp"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://fappify.in

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Add all other necessary environment variables
```

### 5. Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/fapp
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name fappify.in www.fappify.in;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name fappify.in www.fappify.in;
    root /var/www/fapp/current/public;

    index index.php;

    # SSL Configuration (use Certbot for Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/fappify.in/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/fappify.in/privkey.pem;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Handle static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/fapp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 6. SSL Certificate (Optional but Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d fappify.in -d www.fappify.in
```

## GitHub Actions Setup

### 1. Required Secrets

Go to your GitHub repository → Settings → Secrets and variables → Actions, and add these secrets:

| Secret Name | Description | Example Value |
|-------------|-------------|---------------|
| `HOST` | Server IP or domain | `123.456.789.0` |
| `USERNAME` | SSH username | `fapp` |
| `PRIVATE_KEY` | SSH private key | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `PORT` | SSH port (optional) | `22` |
| `DEPLOY_PATH` | Deployment path | `/var/www/fapp` |
| `APP_URL` | Application URL | `https://fappify.in` |
| `GITHUB_TOKEN` | GitHub token | Automatically available |

### 2. Generate SSH Keys

On your local machine:

```bash
# Generate SSH key pair for deployment
ssh-keygen -t rsa -b 4096 -C "deployment@fappify.in" -f ~/.ssh/fapp_deploy

# Copy public key to server
ssh-copy-id -i ~/.ssh/fapp_deploy.pub fapp@your-server-ip

# Add private key to GitHub secrets
cat ~/.ssh/fapp_deploy  # Copy this to PRIVATE_KEY secret
```

### 3. Server Permissions for Deployment User

```bash
# Add deployment user to sudoers for service restart
echo "fapp ALL=(ALL) NOPASSWD: /bin/systemctl reload php8.2-fpm, /bin/systemctl reload nginx" | sudo tee /etc/sudoers.d/fapp-deploy
```

## Manual Deployment Script

The included `deploy.sh` script allows manual deployments:

```bash
# Make executable
chmod +x deploy.sh

# Deploy
./deploy.sh deploy

# Rollback
./deploy.sh rollback

# Check status
./deploy.sh status
```

## Deployment Process

### Automatic Deployment (GitHub Actions)

1. Push to `main` branch
2. GitHub Actions will:
   - Run tests
   - Build assets
   - Deploy to production
   - Run health checks
   - Notify on failure

### Manual Deployment

Use the deployment script for manual deployments:

```bash
ssh fapp@your-server
cd /var/www/fapp
./deploy.sh deploy
```

## Zero-Downtime Deployment

The deployment strategy uses:

1. **Releases directory**: Each deployment creates a new timestamped directory
2. **Shared directories**: Storage and cache directories are shared between releases
3. **Atomic switching**: Symlink switch is atomic
4. **Health checks**: Automatic verification after deployment
5. **Rollback capability**: Quick rollback to previous release

## Monitoring and Logs

### Application Logs

```bash
# View Laravel logs
tail -f /var/www/fapp/shared/storage/logs/laravel.log

# View Nginx logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# View PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

### Health Monitoring

The application includes a health check endpoint: `/health`

```bash
curl https://fappify.in/health
```

## Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   sudo chown -R fapp:www-data /var/www/fapp
   sudo chmod -R 775 /var/www/fapp/shared/storage
   sudo chmod -R 775 /var/www/fapp/shared/bootstrap/cache
   ```

2. **Database Connection Issues**
   - Check `.env` file
   - Verify MySQL is running
   - Check database credentials

3. **Asset Build Issues**
   - Ensure Node.js and npm are installed
   - Check `package.json` and `vite.config.js`

4. **SSH Connection Issues**
   - Verify SSH keys are properly configured
   - Check server firewall settings
   - Ensure user has proper permissions

### Rollback Process

If deployment fails:

```bash
# Automatic rollback via script
./deploy.sh rollback

# Manual rollback
cd /var/www/fapp/releases
PREVIOUS=$(ls -t | head -2 | tail -1)
ln -nfs "/var/www/fapp/releases/$PREVIOUS" /var/www/fapp/current
sudo systemctl reload php8.2-fpm nginx
```

## Security Considerations

1. **Environment Variables**: Never commit `.env` to repository
2. **SSH Keys**: Use separate deployment keys with limited permissions
3. **Server Access**: Limit SSH access and use key-based authentication
4. **SSL/TLS**: Always use HTTPS in production
5. **File Permissions**: Follow principle of least privilege
6. **Database**: Use separate database user with limited permissions

## Backup Strategy

```bash
# Database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# File backup (automated via deployment script)
# Previous releases are kept for rollback
```