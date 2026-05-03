# Deployment Guide

## Quick Start

### 1. Run Server Setup

On a fresh Ubuntu 22.04/24.04 VPS:

```bash
# Upload the script
scp deployment/server-setup.sh root@YOUR_SERVER_IP:/tmp/

# SSH into server
ssh root@YOUR_SERVER_IP

# Edit configuration variables at the top of the script
nano /tmp/server-setup.sh
# Change: DOMAIN, GITHUB_REPO

# Run it
chmod +x /tmp/server-setup.sh
/tmp/server-setup.sh
```

**Save the database password** printed at the end!

### 2. Configure SSH Key for GitHub Actions

```bash
# On your local machine, generate a deploy key
ssh-keygen -t ed25519 -f ~/.ssh/bagisto-deploy -C "github-actions-deploy"

# Copy public key to server
ssh-copy-id -i ~/.ssh/bagisto-deploy.pub deploy@YOUR_SERVER_IP

# Test the connection
ssh -i ~/.ssh/bagisto-deploy deploy@YOUR_SERVER_IP
```

### 3. Add GitHub Secrets

Go to **GitHub repo > Settings > Secrets and variables > Actions** and add:

| Secret | Value |
|--------|-------|
| `VPS_HOST` | Your server IP address |
| `VPS_USER` | `deploy` |
| `VPS_SSH_KEY` | Contents of `~/.ssh/bagisto-deploy` (private key) |
| `VPS_PORT` | `22` (or your custom SSH port) |
| `VPS_APP_DIR` | `/var/www/bagisto` |

### 4. Setup SSL

```bash
ssh root@YOUR_SERVER_IP
chmod +x /var/www/bagisto/deployment/ssl-setup.sh
./ssl-setup.sh your-domain.com
```

### 5. DNS

Point your domain's A record to your server's IP address.

## What Gets Installed

| Service | Version | Purpose |
|---------|---------|---------|
| PHP-FPM | 8.3 | Application runtime |
| Nginx | Latest | Web server + reverse proxy |
| MySQL | 8.0 | Database |
| Redis | Latest | Cache, sessions, queues |
| Elasticsearch | 7.17 | Product search |
| Supervisor | Latest | Queue worker management |
| Node.js | 20 | Frontend asset building |
| Composer | Latest | PHP dependency manager |
| UFW | Latest | Firewall (SSH + HTTP/HTTPS only) |

## CI/CD Flow

```
Push to dev ──> GitHub Actions
                  ├── Lint (Pint PSR-2)
                  ├── Test (MySQL + Redis)
                  └── Build Assets (npm)
                         │
                  All pass?
                         │
                  Deploy to VPS via SSH
                    ├── git pull
                    ├── composer install --no-dev
                    ├── php artisan migrate
                    ├── Cache config/routes/views
                    ├── Upload built assets
                    └── Restart queue workers
```

## Maintenance Commands

```bash
# SSH as deploy user
ssh deploy@YOUR_SERVER_IP

# View logs
tail -f /var/www/bagisto/storage/logs/laravel.log

# Restart queue workers
sudo supervisorctl restart bagisto-worker:*

# Clear all caches
cd /var/www/bagisto
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check service status
sudo systemctl status php8.3-fpm nginx mysql redis elasticsearch
```
