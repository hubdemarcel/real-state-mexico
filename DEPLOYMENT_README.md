# Deployment Guide for Tierras Real Estate Website

## Target Environment

- **Subdomain**: tierras.inteligencias.info
- **Hosting**: Hostinger
- **Upload Path**: public_html/tierras/
- **Database**: u453889947_tierras_stagin on mysql.hostinger.com

## Pre-Deployment Checklist

- [ ] Hostinger account is active
- [ ] Subdomain tierras.inteligencias.info is configured
- [ ] Database u453889947_tierras_stagin exists on mysql.hostinger.com
- [ ] Database user 'tierras_user' has access to the database
- [ ] File upload access to public_html/tierras/

## Files to Upload

Upload ALL files from the project root to `public_html/tierras/` on your Hostinger account.

### Important Files:

- All PHP files (\*.php)
- All HTML files (\*.html)
- Tierrasmx/ directory (assets, CSS, JS, images)
- .htaccess
- .env (updated for production)
- config.php

### Files to Exclude:

- .env.example (not needed in production)
- \*.zip files (archived files)
- DEPLOYMENT_README.md (this file)

## Database Setup Steps

### 1. Create Main Tables

After uploading files, access: `https://tierras.inteligencias.info/create_main_tables.php`

This will create:

- users table
- properties table

### 2. Create Additional Tables

Run these scripts in order:

1. `https://tierras.inteligencias.info/create_agents_table.php`
2. `https://tierras.inteligencias.info/setup_user_tables.php`
3. `https://tierras.inteligencias.info/create_settings_table.php`
4. `https://tierras.inteligencias.info/create_notifications_table.php`

### 3. Verify Database Connection

Access: `https://tierras.inteligencias.info/test_integration.php`

This will check:

- Database connection
- Required tables exist
- Basic functionality

## Post-Deployment Steps

### 1. Test Core Functionality

- [ ] Visit https://tierras.inteligencias.info
- [ ] Test user registration
- [ ] Test login functionality
- [ ] Test property search/listing
- [ ] Test agent features

### 2. Configure SSL (if needed)

- In Hostinger control panel, enable free SSL for the subdomain
- Update any hardcoded HTTP URLs to HTTPS

### 3. File Permissions

Ensure these directories are writable (755):

- Any upload directories (if applicable)

### 4. Clean Up

After successful deployment:

- Delete setup scripts from server:
  - create_main_tables.php
  - create_agents_table.php
  - setup_user_tables.php
  - create_settings_table.php
  - create_notifications_table.php
  - test_integration.php

## Troubleshooting

### Database Connection Issues

- Verify DB_HOST is set to `mysql.hostinger.com`
- Check database credentials in .env
- Ensure database user has proper permissions

### File Path Issues

- All file paths should be relative
- Assets should load from Tierrasmx/ directory

### 404 Errors

- Ensure .htaccess is uploaded
- Check that index.php exists in root

## Support

If you encounter issues:

1. Check browser developer console for errors
2. Verify all files were uploaded correctly
3. Check Hostinger error logs
4. Test database connection manually

## Production Optimizations

- Enable GZIP compression (already configured in .htaccess)
- Set up browser caching (already configured in .htaccess)
- Consider CDN for assets if needed
- Set up automated backups in Hostinger

---

**Deployment completed on:** [Date]
**Deployed by:** [Your Name]
