#!/bin/bash
# ============================================================
# SSL Setup with Certbot (Let's Encrypt)
# Usage: sudo ./ssl-setup.sh your-domain.com
# ============================================================

set -euo pipefail

DOMAIN="${1:-}"

if [[ -z "$DOMAIN" ]]; then
    echo "Usage: sudo ./ssl-setup.sh your-domain.com"
    exit 1
fi

if [[ $EUID -ne 0 ]]; then
    echo "Run as root: sudo ./ssl-setup.sh $DOMAIN"
    exit 1
fi

echo "Installing Certbot..."
apt-get install -y -qq certbot python3-certbot-nginx

echo "Requesting SSL certificate for $DOMAIN..."
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@$DOMAIN" --redirect

echo "Setting up auto-renewal..."
systemctl enable certbot.timer
systemctl start certbot.timer

echo ""
echo "SSL installed! Your site is now available at: https://$DOMAIN"
echo "Certificates auto-renew via systemd timer."
