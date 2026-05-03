#!/bin/bash
# ============================================================
# Bagisto VPS Server Setup Script
# Tested on: Ubuntu 22.04 / 24.04 LTS
#
# This script installs and configures:
#   - PHP 8.3 + required extensions
#   - Composer
#   - MySQL 8.0
#   - Redis
#   - Elasticsearch 7.17
#   - Nginx
#   - Node.js 20 (for asset building)
#   - Supervisor (for queue workers)
#   - UFW Firewall
#   - Deploy user with SSH key auth
#
# Usage:
#   chmod +x server-setup.sh
#   sudo ./server-setup.sh
# ============================================================

set -euo pipefail

# ── Colors ──
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log()   { echo -e "${GREEN}[✓]${NC} $1"; }
warn()  { echo -e "${YELLOW}[!]${NC} $1"; }
error() { echo -e "${RED}[✗]${NC} $1"; exit 1; }

# ── Must be root ──
if [[ $EUID -ne 0 ]]; then
    error "This script must be run as root (use sudo)"
fi

# ── Configuration (edit these) ──
APP_DIR="/var/www/bagisto"
DOMAIN="your-domain.com"          # Change this
DB_NAME="bagisto"
DB_USER="bagisto"
DB_PASS="$(openssl rand -base64 24)"
DEPLOY_USER="deploy"
GITHUB_REPO=""                    # e.g. git@github.com:KUNARABIAN/kunarabian.git
BRANCH="dev"

echo ""
echo "============================================"
echo "  Bagisto Server Setup"
echo "============================================"
echo ""

# ── 1. System Update ──
log "Updating system packages..."
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq \
    software-properties-common \
    curl \
    wget \
    unzip \
    git \
    acl \
    jq \
    htop

# ── 2. Create deploy user ──
if ! id "$DEPLOY_USER" &>/dev/null; then
    log "Creating deploy user: $DEPLOY_USER"
    adduser --disabled-password --gecos "" "$DEPLOY_USER"
    usermod -aG www-data "$DEPLOY_USER"

    # Setup SSH directory for deploy user
    mkdir -p /home/$DEPLOY_USER/.ssh
    chmod 700 /home/$DEPLOY_USER/.ssh
    touch /home/$DEPLOY_USER/.ssh/authorized_keys
    chmod 600 /home/$DEPLOY_USER/.ssh/authorized_keys
    chown -R $DEPLOY_USER:$DEPLOY_USER /home/$DEPLOY_USER/.ssh

    warn "Add your GitHub Actions SSH public key to: /home/$DEPLOY_USER/.ssh/authorized_keys"
else
    log "Deploy user '$DEPLOY_USER' already exists"
fi

# ── 3. PHP 8.3 ──
log "Installing PHP 8.3..."
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-intl \
    php8.3-gd \
    php8.3-bcmath \
    php8.3-redis \
    php8.3-tokenizer \
    php8.3-calendar \
    php8.3-soap

# Tune PHP-FPM for production
PHP_INI="/etc/php/8.3/fpm/php.ini"
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' "$PHP_INI"
sed -i 's/post_max_size = .*/post_max_size = 64M/' "$PHP_INI"
sed -i 's/memory_limit = .*/memory_limit = 512M/' "$PHP_INI"
sed -i 's/max_execution_time = .*/max_execution_time = 120/' "$PHP_INI"
sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' "$PHP_INI"

systemctl restart php8.3-fpm
log "PHP 8.3 installed"

# ── 4. Composer ──
log "Installing Composer..."
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi
log "Composer installed: $(composer --version 2>&1 | head -1)"

# ── 5. MySQL 8.0 ──
log "Installing MySQL 8.0..."
apt-get install -y -qq mysql-server

systemctl enable mysql
systemctl start mysql

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

log "MySQL database '$DB_NAME' and user '$DB_USER' created"

# ── 6. Redis ──
log "Installing Redis..."
apt-get install -y -qq redis-server

sed -i 's/supervised no/supervised systemd/' /etc/redis/redis.conf
systemctl enable redis-server
systemctl restart redis-server
log "Redis installed"

# ── 7. Elasticsearch 7.17 ──
log "Installing Elasticsearch 7.17..."
if ! command -v /usr/share/elasticsearch/bin/elasticsearch &>/dev/null; then
    wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | gpg --dearmor -o /usr/share/keyrings/elasticsearch-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/elasticsearch-keyring.gpg] https://artifacts.elastic.co/packages/7.x/apt stable main" \
        > /etc/apt/sources.list.d/elastic-7.x.list
    apt-get update -qq
    apt-get install -y -qq elasticsearch
fi

# Limit ES memory (adjust based on your VPS RAM)
cat > /etc/elasticsearch/jvm.options.d/memory.options <<'ESJVM'
-Xms512m
-Xmx512m
ESJVM

systemctl enable elasticsearch
systemctl start elasticsearch
log "Elasticsearch 7.17 installed"

# ── 8. Node.js 20 ──
log "Installing Node.js 20..."
if ! command -v node &>/dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y -qq nodejs
fi
log "Node.js $(node --version) installed"

# ── 9. Nginx ──
log "Installing and configuring Nginx..."
apt-get install -y -qq nginx

# Create Nginx config for Bagisto
cat > /etc/nginx/sites-available/bagisto <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    root ${APP_DIR}/public;

    index index.php index.html;

    charset utf-8;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Max upload size (match PHP)
    client_max_body_size 64M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/bagisto /etc/nginx/sites-enabled/bagisto
rm -f /etc/nginx/sites-enabled/default

nginx -t && systemctl restart nginx
log "Nginx configured for $DOMAIN"

# ── 10. Supervisor (Queue Worker) ──
log "Setting up Supervisor for queue workers..."
apt-get install -y -qq supervisor

cat > /etc/supervisor/conf.d/bagisto-worker.conf <<SUPERVISOR
[program:bagisto-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=${DEPLOY_USER}
numprocs=2
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker.log
stopwaitsecs=3600
SUPERVISOR

supervisorctl reread
supervisorctl update
log "Supervisor configured"

# ── 11. Application Directory ──
log "Setting up application directory..."
mkdir -p "$APP_DIR"
chown -R $DEPLOY_USER:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR"

# ── 12. Clone Repository ──
if [[ -n "$GITHUB_REPO" ]]; then
    if [[ ! -d "$APP_DIR/.git" ]]; then
        log "Cloning repository..."
        sudo -u $DEPLOY_USER git clone -b "$BRANCH" "$GITHUB_REPO" "$APP_DIR"
    else
        log "Repository already cloned"
    fi
else
    warn "GITHUB_REPO not set — clone manually:"
    warn "  sudo -u $DEPLOY_USER git clone -b $BRANCH <your-repo> $APP_DIR"
fi

# ── 13. Firewall ──
log "Configuring UFW firewall..."
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw --force enable
log "Firewall enabled (SSH + HTTP/HTTPS)"

# ── 14. Create .env if app is cloned ──
if [[ -f "$APP_DIR/.env.example" && ! -f "$APP_DIR/.env" ]]; then
    log "Creating .env file from .env.example..."
    sudo -u $DEPLOY_USER cp "$APP_DIR/.env.example" "$APP_DIR/.env"

    cd "$APP_DIR"

    # Update .env with production values
    sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
    sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env
    sed -i "s|CACHE_STORE=.*|CACHE_STORE=redis|" .env
    sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=redis|" .env
    sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=redis|" .env
    sed -i "s|REDIS_CLIENT=.*|REDIS_CLIENT=phpredis|" .env

    # Install dependencies
    log "Installing Composer dependencies..."
    sudo -u $DEPLOY_USER composer install --no-dev --optimize-autoloader --working-dir="$APP_DIR"

    # Generate app key
    php artisan key:generate

    # Run migrations
    log "Running migrations..."
    php artisan migrate --force

    # Set permissions
    chown -R $DEPLOY_USER:www-data "$APP_DIR"
    chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

    # Cache config
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Create storage link
    php artisan storage:link

    log "Application configured and migrated"
fi

# ── Summary ──
echo ""
echo "============================================"
echo "  Setup Complete!"
echo "============================================"
echo ""
echo "  Domain:       $DOMAIN"
echo "  App Dir:      $APP_DIR"
echo "  Deploy User:  $DEPLOY_USER"
echo "  DB Name:      $DB_NAME"
echo "  DB User:      $DB_USER"
echo "  DB Password:  $DB_PASS"
echo ""
echo "  SAVE THE DB PASSWORD ABOVE!"
echo ""
echo "  Next steps:"
echo "  ─────────────────────────────────────────"
echo "  1. Update DOMAIN in /etc/nginx/sites-available/bagisto"
echo "  2. Add your SSH public key to:"
echo "     /home/$DEPLOY_USER/.ssh/authorized_keys"
echo "  3. If not cloned, clone the repo:"
echo "     sudo -u $DEPLOY_USER git clone -b $BRANCH <repo> $APP_DIR"
echo "  4. Set up SSL with Certbot:"
echo "     apt install certbot python3-certbot-nginx"
echo "     certbot --nginx -d $DOMAIN"
echo "  5. Add GitHub Secrets (for CI/CD):"
echo "     VPS_HOST     = <your-server-ip>"
echo "     VPS_USER     = $DEPLOY_USER"
echo "     VPS_SSH_KEY  = <private key matching authorized_keys>"
echo "     VPS_APP_DIR  = $APP_DIR"
echo ""
