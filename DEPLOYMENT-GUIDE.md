# Fapp Deployment Guide for fappify.in

This guide will help you deploy your Laravel application to your shared hosting environment at `/home/u636722041/domains/fappify.in`.

## Prerequisites

Before starting the deployment, ensure you have:

1. **SSH Access** to your hosting account
2. **Database** created with the following credentials:
   - Database: `u636722041_fapp`
   - Username: `u636722041_root`
   - Password: `Abeerpant@2025`
3. **Domain** pointing to your hosting account (fappify.in)
4. **Git, PHP, Composer, and Node.js** available on the server

## Quick Deployment Steps

### Step 1: Initial Setup

1. **SSH into your server:**
   ```bash
   ssh your-username@your-server-ip
   ```

2. **Navigate to your domain directory:**
   ```bash
   cd /home/u636722041/domains/fappify.in
   ```

3. **Upload the deployment script:**
   Upload the `shared-hosting-deploy.sh` file to your server, or clone this repository:
   ```bash
   git clone https://github.com/moryapant/piffy.git temp-repo
   cp temp-repo/shared-hosting-deploy.sh ./
   chmod +x shared-hosting-deploy.sh
   rm -rf temp-repo
   ```

### Step 2: Initialize Deployment Environment

```bash
./shared-hosting-deploy.sh init
```

This will:
- Create necessary directory structure
- Set up shared directories for storage and cache
- Create a sample .env file

### Step 3: Configure Environment

Edit the production environment file:
```bash
nano /home/u636722041/domains/fappify.in/shared/.env
```

Copy the contents from `.env.production` and update any missing configurations:
- Google OAuth credentials
- Mail settings (SMTP)
- Any other service API keys

### Step 4: Deploy the Application

```bash
./shared-hosting-deploy.sh deploy
```

This will:
- Clone the latest code from GitHub
- Install PHP dependencies with Composer
- Install Node.js dependencies and build assets
- Run database migrations
- Set up symbolic links
- Switch to the new release

### Step 5: Verify Deployment

1. **Check deployment status:**
   ```bash
   ./shared-hosting-deploy.sh status
   ```

2. **Visit your website:**
   Open https://fappify.in in your browser

3. **Check Laravel logs if needed:**
   ```bash
   tail -f /home/u636722041/domains/fappify.in/shared/storage/logs/laravel.log
   ```

## Directory Structure

After deployment, your directory structure will look like:

```
/home/u636722041/domains/fappify.in/
├── current/                    # Symlink to current release
├── public_html/               # Symlink to current/public (web root)
├── releases/                  # All deployment releases
│   ├── 20250826120000/       # Release directories (timestamped)
│   └── 20250826130000/
├── shared/                    # Shared files across releases
│   ├── .env                  # Production environment file
│   ├── storage/              # Application storage
│   └── bootstrap/cache/      # Bootstrap cache
└── shared-hosting-deploy.sh   # Deployment script
```

## Environment Configuration

Your production `.env` file should contain:

```env
# Application Settings
APP_NAME="Fapp"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fappify.in

# Database (already configured)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u636722041_fapp
DB_USERNAME=u636722041_root
DB_PASSWORD=Abeerpant@2025

# Session (important for shared hosting)
SESSION_DOMAIN=.fappify.in

# File Storage
FILESYSTEM_DISK=public

# Firebase (already configured)
VITE_FIREBASE_API_KEY="AIzaSyB6uVDeIWV2xErEEZ30DI29AEeqvPLzgBk"
# ... other Firebase settings

# Add your production settings:
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
MAIL_HOST=your_smtp_host
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_email_password
```

## Common Commands

### Deploy New Version
```bash
./shared-hosting-deploy.sh deploy
```

### Rollback to Previous Version
```bash
./shared-hosting-deploy.sh rollback
```

### Check Current Status
```bash
./shared-hosting-deploy.sh status
```

### View Application Logs
```bash
tail -f /home/u636722041/domains/fappify.in/shared/storage/logs/laravel.log
```

### Clear Application Cache
```bash
cd /home/u636722041/domains/fappify.in/current
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Run Database Migrations Manually
```bash
cd /home/u636722041/domains/fappify.in/current
php artisan migrate --force
```

## Troubleshooting

### 1. File Permissions Issues
```bash
cd /home/u636722041/domains/fappify.in
chmod -R 775 shared/storage
chmod -R 775 shared/bootstrap/cache
```

### 2. Database Connection Issues
- Verify database credentials in `.env` file
- Test connection: `php artisan tinker` then `DB::connection()->getPdo()`

### 3. Assets Not Loading
- Ensure `npm run build` completed successfully
- Check if `public_html` symlink points to correct release
- Verify file permissions on public directory

### 4. Laravel Errors
- Check logs: `tail -f shared/storage/logs/laravel.log`
- Clear all caches: `php artisan optimize:clear`
- Regenerate application key if needed: `php artisan key:generate --force`

### 5. Storage Link Issues
```bash
cd /home/u636722041/domains/fappify.in/current
php artisan storage:link
```

## Security Considerations

1. **Never commit `.env` file** to your repository
2. **Use strong database passwords**
3. **Keep your dependencies updated**
4. **Monitor your application logs**
5. **Use HTTPS only** (already configured)

## Performance Optimization

1. **Cache configurations** (done automatically during deployment):
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Optimize Composer autoloader** (done automatically):
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Use database sessions** (already configured in .env)

## Maintenance

### Regular Tasks

1. **Monitor disk space**: Shared hosting has limited space
2. **Clean old releases**: Script keeps only 3 releases automatically
3. **Update dependencies**: Regularly update composer and npm packages
4. **Backup database**: Consider automated database backups

### Health Monitoring

Check application health:
```bash
curl -I https://fappify.in
```

Monitor error logs:
```bash
tail -f /home/u636722041/domains/fappify.in/shared/storage/logs/laravel.log
```

## Support

If you encounter issues:

1. Check the deployment script output for errors
2. Review Laravel logs for application errors
3. Verify all environment variables are correctly set
4. Ensure database is accessible and configured properly

## Next Steps After Deployment

1. **Test all functionality**:
   - User registration/login
   - Post creation and voting
   - Comment system
   - File uploads
   - Community features

2. **Set up monitoring**:
   - Consider using services like UptimeRobot for uptime monitoring
   - Set up error tracking (Sentry, Bugsnag)

3. **Configure backups**:
   - Set up automated database backups
   - Consider file storage backups

4. **SSL Certificate**:
   - Ensure SSL is properly configured for your domain
   - Verify HTTPS redirects are working

5. **Performance optimization**:
   - Monitor application performance
   - Consider CDN for static assets
   - Optimize database queries if needed

Your Laravel application should now be successfully deployed and accessible at https://fappify.in!