<p align="center">
  <a href="http://www.bagisto.com">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/bagisto/temp-media/0b0984778fae92633f57e625c5494ead1fe320c3/dark-logo-P5H7MBtx.svg">
      <source media="(prefers-color-scheme: light)" srcset="https://bagisto.com/wp-content/themes/bagisto/images/logo.png">
      <img src="https://bagisto.com/wp-content/themes/bagisto/images/logo.png" alt="Bagisto logo">
    </picture>
  </a>
</p>

<p align="center">
    <a href="https://packagist.org/packages/bagisto/bagisto"><img src="https://poser.pugx.org/bagisto/bagisto/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/bagisto/bagisto"><img src="https://poser.pugx.org/bagisto/bagisto/v/stable.svg" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/bagisto/bagisto"><img src="https://poser.pugx.org/bagisto/bagisto/license.svg" alt="License"></a>
    <a href="#backers"><img src="https://opencollective.com/bagisto/backers/badge.svg" alt="Backers on Open Collective"></a>
    <a href="#sponsors"><img src="https://opencollective.com/bagisto/sponsors/badge.svg" alt="Sponsors on Open Collective"></a>
    <a href="https://www.codetriage.com/bagisto/bagisto"><img src="https://www.codetriage.com/bagisto/bagisto/badges/users.svg" alt="Open Source Helpers"></a>
</p>

<p align="center">
    <a href="https://twitter.com/intent/follow?screen_name=bagistoshop"><img src="https://img.shields.io/twitter/follow/bagistoshop?style=social"></a>
    <a href="https://www.youtube.com/channel/UCbrfqnhyiDv-bb9QuZtonYQ"><img src="https://img.shields.io/youtube/channel/subscribers/UCbrfqnhyiDv-bb9QuZtonYQ?style=social"></a>
</p>

<p align="center">
    <a href="https://bagisto.com/en/">Website</a> | <a href="https://devdocs.bagisto.com/">Documentation</a> | <a href="https://forums.bagisto.com/">Forums</a> | <a href="https://www.facebook.com/groups/bagisto/">Community</a>
</p>

<p align="center" style="display: inline;">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/ar.svg" alt="Arabic" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/de.svg" alt="German" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/us.svg" alt="English" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/es.svg" alt="Spanish" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/ir.svg" alt="Persian" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/it.svg" alt="Italian" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/nl.svg" alt="Dutch" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/pl.svg" alt="Polish" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/pt.svg" alt="Portuguese" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/tr.svg" alt="Turkish" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/eg.svg" alt="Egyptian" width="24" height="24">
    <img class="flag-img" src="https://flagicons.lipis.dev/flags/4x3/cn.svg" alt="Chinese" width="24" height="24">
</p>

<a href="https://www.youtube.com/watch?v=OHbte7hdxYU">
    <img class="flag-img" src="https://raw.githubusercontent.com/bagisto/temp-media/master/bagisto-featured.png" alt="Bagisto Featured" width="100%">
</a>

# Introduction

Bagisto is an opensource [Laravel eCommerce](https://www.bagisto.com/) framework built on [Laravel](https://laravel.com/) (PHP) and [Vue.js](https://vuejs.org/). It helps you build online stores quickly with a modular, extensible architecture.

![Repo Stats](https://raw.githubusercontent.com/bagisto/temp-media/master/stats.webp)

---

# Table of Contents

- [Requirements](#requirements)
- [Installation Walkthrough](#installation-walkthrough)
  - [Step 1: Install PHP](#step-1-install-php)
  - [Step 2: Install Composer](#step-2-install-composer)
  - [Step 3: Install Node.js & npm](#step-3-install-nodejs--npm)
  - [Step 4: Install & Configure MySQL](#step-4-install--configure-mysql)
  - [Step 5: Clone & Install Bagisto](#step-5-clone--install-bagisto)
  - [Step 6: Configure Environment](#step-6-configure-environment)
  - [Step 7: Run Migrations & Seed Data](#step-7-run-migrations--seed-data)
  - [Step 8: Build Frontend Assets](#step-8-build-frontend-assets)
  - [Step 9: Start the Application](#step-9-start-the-application)
- [Docker Installation (All Platforms)](#docker-installation-all-platforms)
- [Cloud Installation via Amazon AMI](#cloud-installation-via-amazon-ami)
- [Multi Vendor Marketplace Module Installation](#multi-vendor-marketplace-module-installation)
- [Troubleshooting](#troubleshooting)
- [Features](#features)
- [Community](#community)
- [License](#license)

---

# Requirements

| Dependency | Version |
|---|---|
| PHP | 8.2 or higher |
| Composer | 2.x |
| Node.js | 18.x or higher |
| npm | 9.x or higher |
| MySQL | 8.0 |

**Required PHP Extensions:** `calendar`, `curl`, `intl`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `gd` or `imagick`, `fileinfo`

---

# Installation Walkthrough

## Step 1: Install PHP

### Linux (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-common php8.3-mysql php8.3-xml php8.3-curl php8.3-mbstring php8.3-intl php8.3-gd php8.3-zip php8.3-bcmath php8.3-tokenizer php8.3-fileinfo php8.3-calendar -y
```

### macOS

```bash
# Using Homebrew (https://brew.sh)
brew install php@8.3
```

Or use [Laravel Herd](https://herd.laravel.com/) which bundles PHP, Composer, and nginx in one installer.

### Windows

**Option A: Laravel Herd (Recommended)**

Download and install [Laravel Herd for Windows](https://herd.laravel.com/windows) — it bundles PHP, Composer, Node.js, and nginx.

**Option B: Manual Install**

1. Download PHP 8.3 from [windows.php.net](https://windows.php.net/download/)
2. Extract to `C:\php`
3. Add `C:\php` to your system PATH
4. Copy `php.ini-development` to `php.ini`
5. Enable required extensions in `php.ini` by removing the `;` prefix:
   ```ini
   extension=curl
   extension=fileinfo
   extension=gd
   extension=intl
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   extension=tokenizer
   extension=calendar
   ```

**Verify PHP is installed:**

```bash
php -v
php -m  # List enabled modules
```

---

## Step 2: Install Composer

### Linux / macOS

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Windows

Download and run the [Composer Windows Installer](https://getcomposer.org/Composer-Setup.exe).

If using Laravel Herd, Composer is already included.

**Verify:**

```bash
composer --version
```

---

## Step 3: Install Node.js & npm

### Linux (Ubuntu/Debian)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y
```

### macOS

```bash
brew install node@20
```

### Windows

Download and install from [nodejs.org](https://nodejs.org/) (LTS version recommended).

If using Laravel Herd, Node.js is already included.

**Verify:**

```bash
node -v
npm -v
```

---

## Step 4: Install & Configure MySQL

### Linux (Ubuntu/Debian)

```bash
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo systemctl enable mysql
sudo mysql_secure_installation
```

Create the database and user:

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE bagisto;
CREATE USER 'bagisto'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON bagisto.* TO 'bagisto'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### macOS

```bash
brew install mysql@8.0
brew services start mysql
mysql_secure_installation
```

Then create the database and user using the same SQL commands above.

### Windows

**Option A:** Download and install [MySQL 8.0 Installer](https://dev.mysql.com/downloads/installer/) (use the full installer, select "Server only" or "Developer Default").

**Option B:** Install via [XAMPP](https://www.apachefriends.org/) which bundles MySQL (MariaDB), PHP, and Apache.

**Option C:** Install via [Laragon](https://laragon.org/) which bundles MySQL, PHP, Node.js, and more.

After installation, open MySQL Workbench or a terminal and run:

```sql
CREATE DATABASE bagisto;
CREATE USER 'bagisto'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON bagisto.* TO 'bagisto'@'localhost';
FLUSH PRIVILEGES;
```

---

## Step 5: Clone & Install Bagisto

```bash
git clone https://github.com/bagisto/bagisto.git
cd bagisto
composer install
```

---

## Step 6: Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=bagisto
DB_PASSWORD=your_secure_password
```

Other important settings you may want to configure:

```dotenv
APP_NAME=Bagisto
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC
APP_LOCALE=en
APP_CURRENCY=USD
```

---

## Step 7: Run Migrations & Seed Data

```bash
php artisan migrate
php artisan db:seed
```

Or use the Bagisto installer which handles both:

```bash
php artisan bagisto:install
```

The installer will interactively ask for your database credentials, admin details, and store configuration.

---

## Step 8: Build Frontend Assets

Install frontend dependencies and build assets:

```bash
# Admin panel assets
cd packages/Webkul/Admin && npm install && npm run build && cd ../../..

# Storefront assets
cd packages/Webkul/Shop && npm install && npm run build && cd ../../..
```

For development with hot-reload:

```bash
npm run dev
```

---

## Step 9: Start the Application

```bash
php artisan serve
```

Visit the application:

| URL | Purpose |
|---|---|
| `http://localhost:8000` | Storefront |
| `http://localhost:8000/admin` | Admin Panel |

Default admin credentials (if seeded):
- **Email:** `admin@example.com`
- **Password:** `admin123`

---

# Docker Installation (All Platforms)

Docker provides the easiest cross-platform setup with all services pre-configured.

### Prerequisites

Install [Docker Desktop](https://www.docker.com/products/docker-desktop/) for your platform (Linux, macOS, or Windows).

### Setup

```bash
git clone https://github.com/bagisto/bagisto.git
cd bagisto
cp .env.example .env
```

Start all services using Laravel Sail:

```bash
# Install Composer dependencies via Docker (no local PHP needed)
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

# Start all services
./vendor/bin/sail up -d

# Generate app key
./vendor/bin/sail artisan key:generate

# Run the Bagisto installer
./vendor/bin/sail artisan bagisto:install
```

**Services included in Docker:**
| Service | Port |
|---|---|
| Application | 80 |
| MySQL 8.0 | 3306 |
| Redis | 6379 |
| Elasticsearch | 9200 |
| Kibana | 5601 |
| Mailpit (email testing) | 8025 |

**Common Sail commands:**

```bash
./vendor/bin/sail up -d        # Start services in background
./vendor/bin/sail down          # Stop services
./vendor/bin/sail artisan ...   # Run artisan commands
./vendor/bin/sail php ...       # Run PHP commands
./vendor/bin/sail npm ...       # Run npm commands
./vendor/bin/sail mysql         # Open MySQL CLI
```

> **Windows note:** Use WSL 2 (Windows Subsystem for Linux) with Docker Desktop for best performance. Clone the repo inside WSL, not on the Windows filesystem.

---

# Cloud Installation via Amazon AMI

Deploy Bagisto quickly using the pre-configured Amazon Machine Image (AMI):

[**Launch Bagisto on AWS**](https://aws.amazon.com/marketplace/pp/prodview-r3xv62axcqkpa)

This AMI has everything pre-installed and configured for production use.

---

# Multi Vendor Marketplace Module Installation

The [Multi Vendor Marketplace](https://bagisto.com/en/laravel-multi-vendor-marketplace/) module transforms your Bagisto store into a full-featured marketplace with seller management, commissions, product approvals, and order handling.

> **Note:** This is a paid extension. You will receive a zip file after purchasing it from the [Webkul Store](https://store.webkul.com/laravel-multi-vendor-marketplace.html).

### Prerequisites

- A working Bagisto installation (see [Installation Walkthrough](#installation-walkthrough) above)
- The Marketplace extension zip file from Webkul

### Step 1: Extract & Merge Package Files

Unzip the downloaded extension file and merge the `packages` folder into your Bagisto root directory:

```
bagisto/
└── packages/
    └── Webkul/
        └── Marketplace/    ← extracted from the zip
```

### Step 2: Register the Package in `composer.json`

Open the `composer.json` file in the Bagisto root directory and add the following line under the `psr-4` autoload section:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Webkul\\Marketplace\\": "packages/Webkul/Marketplace/src"
    }
}
```

Then regenerate the autoload files:

```bash
composer dump-autoload
```

### Step 3: Register the Service Provider

Add the Marketplace service provider in `bootstrap/providers.php`:

```php
return [
    // ... existing providers
    Webkul\Marketplace\Providers\MarketplaceServiceProvider::class,
];
```

### Step 4: Run Migrations & Publish Assets

```bash
php artisan marketplace:install
php artisan optimize:clear
```

### Verification

After completing these steps, log in to the admin panel at `http://localhost:8000/admin`. You should see the **Marketplace** icon in the left-hand menu bar.

For detailed usage documentation, visit the [Multi Vendor Marketplace User Guide](https://docs.bagisto.com/multi-vendor-marketplace/introduction.html).

---

# Troubleshooting

### PHP extension missing

```bash
# Check installed extensions
php -m

# Linux: install missing extension (example: intl)
sudo apt install php8.3-intl

# macOS (Homebrew): extensions are typically included
# Windows: enable in php.ini
```

### Composer memory limit

```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### MySQL connection refused

- Verify MySQL is running: `sudo systemctl status mysql` (Linux) or `brew services list` (macOS)
- Check credentials in `.env` match your MySQL user
- Ensure the database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Permission issues (Linux/macOS)

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Node.js / npm build errors

```bash
# Clear npm cache and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

# Features

## Starter Pack

Empower your e-commerce journey with the [Bagisto Starter Pack](https://store.webkul.com/bagisto-starter-pack.html), streamlining setup and integration for a seamless online store launch.

## B2B eCommerce Platform

The [B2B eCommerce Platform](https://bagisto.com/en/b2b-commerce-platform/) enhances your store with company-based purchasing, multi-user access, quote negotiation, and procurement management.

## Multi Vendor Marketplace

[Multi Vendor Marketplace](https://bagisto.com/en/laravel-multi-vendor-marketplace/) transforms a standard store into a complete marketplace with seller management, commissions, and vendor dashboards.

## Multi Tenant eCommerce

[Multi Tenant eCommerce](https://bagisto.com/en/laravel-multi-tenant-saas/) enables a SaaS-based platform where multiple merchants manage individual stores under one system.

## POS

[Point of Sale](https://bagisto.com/en/laravel-pos/) system for retail operations, inventory management, and fast checkout.

## Headless Commerce (Next.js)

Build headless storefronts with Next.js: [github.com/bagisto/nextjs-commerce](https://github.com/bagisto/nextjs-commerce)

## Mobile eCommerce

Open source mobile app powered by Flutter & Laravel: [github.com/bagisto/opensource-ecommerce-mobile-app](https://github.com/bagisto/opensource-ecommerce-mobile-app)

## AI Powered eCommerce

Integrate LLMs (GPT, Gemini, Mistral, LLaMA, etc.) for chatbots, product descriptions, customer support, and recommendations. [Learn more](https://bagisto.com/en/extensions/laravel-chatbot-using-openai-chatgpt-llm/)

## Decentralised eCommerce

Build dApps on Ethereum/Solana with smart contracts, NFT marketplaces, and more. [Learn more](https://bagisto.com/en/services/blockchain-commerce/)

## 200+ Extensions

Browse pre-built extensions from the [Bagisto Extension Marketplace](https://bagisto.com/en/extensions/)

---

# Community

Get support on [Facebook Group](https://www.facebook.com/groups/bagisto) and [Forum](https://forums.bagisto.com/).

Want to contribute? Read our [Contributing Guide](https://github.com/bagisto/bagisto/blob/master/.github/CONTRIBUTING.md).

Follow the [Getting Started with Bagisto](https://www.youtube.com/watch?v=s_DhQrjK8Tw&list=PLe30vg_FG4OS3BU8rHUKQZ2mnX45xwSMc) video tutorial.

Browse the free [Live Demo](https://demo.bagisto.com/).

# License

Bagisto is a fully open-source Laravel eCommerce framework under the [MIT License](https://github.com/bagisto/bagisto/blob/2.3/LICENSE).

# Security Vulnerabilities

Please do not use the issue tracker for security issues. Send all security reports to [support@bagisto.com](mailto:support@bagisto.com).

# Contributors

This project is on [Open Collective](https://opencollective.com/bagisto), and it exists thanks to the people who contribute.

<a href="https://github.com/bagisto/bagisto/graphs/contributors"><img src="https://opencollective.com/bagisto/contributors.svg?width=890&button=false"/></a>

# Backers

Thank you to all our backers!

<a href="https://opencollective.com/bagisto" target="_blank"><img src="https://opencollective.com/bagisto/backers.svg?width=890"></a>

# Sponsors

Support this project by becoming a sponsor. Your logo will show up here with a link to your website.

<a href="https://opencollective.com/bagisto" target="_blank"><img src="https://opencollective.com/bagisto/sponsors.svg?width=890&isActive=true"></a>


