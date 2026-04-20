# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Bagisto is an open-source Laravel-based eCommerce framework built on Laravel 11 and Vue.js. It's designed as a modular, extensible platform for building online stores with enterprise features.

**Key Stack:**
- Backend: Laravel 11 (PHP 8.2+)
- Frontend: Vue.js with Vite 5
- Database: MySQL 8.0
- Cache/Queue: Redis
- Search: Elasticsearch 7.17
- Testing: Pest 3.0 + PHPUnit 11.0
- Code Style: PSR-2 (enforced via Laravel Pint)

## Architecture: Monorepo with Modular Packages

This is a monorepo with a clear separation of concerns:

```
bagisto/
├── app/                          # Minimal main app code
├── packages/Webkul/              # Core feature packages (DO NOT modify)
│   ├── Admin/                    # Admin dashboard
│   ├── Shop/                     # Storefront (default theme)
│   ├── Product/                  # Product management
│   ├── Catalog/                  # Catalog rules
│   ├── CartRule/                 # Shopping cart rules
│   ├── Category/                 # Category management
│   ├── Checkout/                 # Checkout flow
│   ├── Customer/                 # Customer management
│   ├── Sales/                    # Orders and sales
│   ├── Shipping/                 # Shipping methods
│   ├── Tax/                      # Tax calculations
│   ├── Payment/                  # Payment processing
│   ├── Inventory/                # Stock management
│   ├── Marketplace/              # Multi-vendor support
│   ├── Theme/                    # Theme system
│   ├── DataGrid/                 # Data grid component
│   ├── DataTransfer/             # Import/export
│   ├── Core/                     # Core utilities
│   ├── CMS/                      # Static content pages
│   ├── Marketing/                # Marketing tools
│   ├── MagicAI/                  # AI integration
│   └── 20+ other packages        # Various features
├── packages/Kun/                 # Custom Kun packages (our code)
│   └── Theme/                    # Kun theme package (CSS, JS, Vite, seeders)
├── config/                       # Laravel config files
├── database/                     # Migrations and seeders
├── resources/
│   └── themes/kun/views/         # Kun theme view overrides
├── routes/                       # API and web routes
├── tests/                        # Integration test setup
└── vendor/                       # Composer dependencies
```

**Important:** Each package in `packages/Webkul/` is self-contained with:
- `src/` - Package source code
- `tests/` - Package-specific tests
- `composer.json` - Package dependencies
- `package.json` - Frontend dependencies
- `vite.config.js` - Frontend build config
- Custom test case base class (e.g., `AdminTestCase`)

**Critical Rule:** Never modify `packages/Webkul/` files. All customizations go through theme view overrides in `resources/themes/kun/views/` or the `packages/Kun/` package.

## Kun Theme Architecture

The Kun theme is a custom child theme that extends the default Shop theme. It uses Bagisto's theme inheritance system to override views while reusing core functionality.

### Theme Configuration

- **Theme config**: `config/themes.php` — Kun theme has `parent: 'default'`
- **Vite config**: `config/bagisto-vite.php` — defines `shop` (default) and `shop-kun` viters
- **Kun Vite build**: `packages/Kun/Theme/vite.config.js` — builds to `public/themes/shop/kun/build/`

### Asset Loading Strategy

The Kun layout (`resources/themes/kun/views/components/layouts/index.blade.php`) loads assets in two layers:

```blade
{{-- Default Shop assets (JS + CSS — provides Vue app, core components) --}}
@bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'], 'shop')

{{-- Kun Theme assets (CSS only — overrides/extends default styles) --}}
@bagistoVite(['src/Resources/assets/css/app.css'], 'shop-kun')
```

**Key rules:**
- `@bagistoVite` without a namespace resolves to the CURRENT theme's Vite build — use explicit `'shop'` or `'shop-kun'` namespace
- Kun's Vite build does NOT include Shop's JS (`app.js` creates `window.app` Vue instance) — always load Shop's JS via `'shop'` namespace
- Kun's `src/Resources/assets/images/` must contain copies of Shop's images (thank-you.png, logo.svg, etc.) because `bagisto_asset()` resolves through the current theme's Vite manifest and `Theme::url()` calls `abort(404)` on missing assets with no parent fallback
- After adding/removing images, rebuild: `cd packages/Kun/Theme && npm run build`

### Theme View Overrides

Kun overrides Shop views by placing files at matching paths under `resources/themes/kun/views/`:

```
resources/themes/kun/views/
├── components/
│   ├── layouts/
│   │   ├── index.blade.php          # Main layout (asset loading, fonts)
│   │   ├── header.blade.php         # Dynamic header (locales, categories from API, config-driven icons)
│   │   └── footer.blade.php         # Dynamic footer (links from DB, newsletter from config)
│   └── products/
│       └── card.blade.php           # Global product card (Kun styling, used on all pages)
└── home/
    ├── index.blade.php              # Dynamic homepage (@foreach $customizations)
    ├── kun-image-carousel.blade.php # Image carousel (vanilla JS, drag/swipe)
    ├── kun-category-carousel.blade.php  # Category carousel (Vue component)
    └── kun-product-carousel.blade.php   # Product carousel (Vue component, inline card)
```

### Dynamic Content System

The homepage and footer are driven by the `theme_customizations` / `theme_customization_translations` DB tables, managed from Admin > Settings > Themes.

**Homepage** (`home/index.blade.php`) loops through `$customizations` and renders by type:
- `IMAGE_CAROUSEL` → `kun-image-carousel.blade.php`
- `STATIC_CONTENT` → inline HTML/CSS from DB
- `CATEGORY_CAROUSEL` → `kun-category-carousel.blade.php`
- `PRODUCT_CAROUSEL` → `kun-product-carousel.blade.php`

**Header** components fetch data dynamically:
- Locale switcher: `core()->getCurrentChannel()->locales()`
- Promo banner: `general.content.header_offer.*` admin config
- Navigation + category pills: Vue components fetching `shop.api.categories.tree`
- Action icons: conditional on `catalog.products.settings.compare_option`, `customer.settings.wishlist.wishlist_option`, `sales.checkout.shopping_cart.cart_page`

**Footer** links come from `footer_links` type in theme customizations (same system as default Shop).

**Seeder**: `php artisan db:seed --class="Kun\Theme\Database\Seeders\KunThemeCustomizationSeeder"` — populates homepage sections + footer links for the Kun theme.

### Product Card

The Kun product card override at `components/products/card.blade.php` replaces the default Shop's `v-product-card` Vue component globally. It's automatically used on category pages, search, compare, etc.

The homepage carousel (`kun-product-carousel.blade.php`) uses its own inline card markup because it renders inside a parent Vue component (`v-kun-product-carousel`) — Blade components like `<x-shop::products.card>` can't be used inside Vue `<script type="text/x-template">` blocks.

### Vue Component Registration

- Components are registered via `@pushOnce('scripts')` with `<script type="text/x-template">` for templates and `<script type="module">` for `app.component()` calls
- The `app` variable (Vue instance) comes from Shop's `app.js` — Kun must NOT load its own `app.js`
- Components registered inside a `v-for` loop in a Vue template must use the Vue tag directly (`<v-product-card>`) not the Blade component (`<x-shop::products.card>`)

### CSS Design Tokens

Kun's design system is in `packages/Kun/Theme/src/Resources/assets/css/tokens.css`:
- Font families: `--kun-font-body` (Poppins), `--kun-font-display` (DM Serif Display)
- Brand colors: `--kun-color-brand-*` (terracotta, olive, cream palettes)
- Spacing: `--kun-space-section-x` (90px), `--kun-space-section-y` (48px)

The `.kun-section` utility class provides consistent section spacing with responsive breakpoints.

### Kun Theme Development Commands

```bash
# Build Kun theme assets (after CSS/image changes)
cd packages/Kun/Theme && npm run build

# Seed Kun theme customizations (homepage + footer)
php artisan db:seed --class="Kun\Theme\Database\Seeders\KunThemeCustomizationSeeder"

# Clear view cache (after changing blade files)
php artisan view:clear
```

## Common Development Commands

### Setup & Dependencies
```bash
# Install PHP dependencies
composer install

# Install frontend dependencies (each package)
cd packages/Webkul/Admin && npm install

# Docker-based development (uses Laravel Sail)
./vendor/bin/sail up -d          # Start services (MySQL, Redis, Elasticsearch, etc.)
./vendor/bin/sail down           # Stop services
./vendor/bin/sail artisan key:generate  # Generate app key
./vendor/bin/sail artisan migrate       # Run migrations
```

### Running the Application
```bash
# Start Vite dev server (handles frontend hot reload)
npm run dev                      # From root or individual package directory

# Build frontend assets
npm run build

# Run Laravel development server (if not using Sail)
php artisan serve              # Accessible at http://localhost:8000
```

### Testing
```bash
# Run all tests (Pest/PHPUnit)
php artisan test

# Run specific test suite
php artisan test --testsuite="Admin Feature Test"
php artisan test --testsuite="Shop Feature Test"

# Run tests with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/SomeTest.php

# Run tests in watch mode (with Pest)
./vendor/bin/pest --watch
```

### Code Quality
```bash
# Lint PHP code (Laravel Pint - PSR-2)
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test

# Format specific files
./vendor/bin/pint app/ packages/Webkul/Core/src
```

### Database & Migrations
```bash
# Run migrations
php artisan migrate

# Create a new migration
php artisan make:migration create_products_table

# Rollback last migration batch
php artisan migrate:rollback

# Reset database (careful!)
php artisan migrate:reset
```

### Useful Artisan Commands
```bash
# List all artisan commands
php artisan list

# Clear application cache
php artisan cache:clear
php artisan config:cache

# Publish package assets
php artisan vendor:publish --provider="Webkul\Product\Providers\ProductServiceProvider"

# Generate API documentation (if configured)
php artisan scribe:generate
```

## Code Style & Standards

- **PSR-2 Coding Style**: Enforced via Laravel Pint
- **PSR-4 Autoloading**: Defined in `composer.json` `autoload` section
- **PHPDoc Format** (from CONTRIBUTING.md):
  ```php
  /**
   * Brief description.
   *
   * @param  string  $param      Two spaces before type, two after type, before variable
   * @return void
   * @throws \Exception
   */
  ```

Run `./vendor/bin/pint` before committing to ensure compliance.

## Key File Locations

| Purpose | Location |
|---------|----------|
| Laravel config | `config/*.php` |
| Theme config | `config/themes.php` |
| Vite viters config | `config/bagisto-vite.php` |
| Routes | `routes/` (web, api, etc.) |
| Controllers | `app/Http/Controllers/` or `packages/Webkul/*/src/Http/Controllers/` |
| Models | `packages/Webkul/*/src/Models/` |
| Migrations | `database/migrations/` |
| Service Providers | `packages/Webkul/*/src/Providers/` |
| Tests | `packages/Webkul/*/tests/` |
| Translations | `lang/{locale}/` |
| Default Shop views | `packages/Webkul/Shop/src/Resources/views/` |
| Kun theme overrides | `resources/themes/kun/views/` |
| Kun theme package | `packages/Kun/Theme/` (CSS, JS, Vite, seeders, images) |
| Kun CSS/tokens | `packages/Kun/Theme/src/Resources/assets/css/` |
| Kun images | `packages/Kun/Theme/src/Resources/assets/images/` |
| Kun Vite build output | `public/themes/shop/kun/build/` |
| Frontend assets | `resources/js/`, `resources/css/` |
| Package assets | `packages/Webkul/*/src/Resources/` |

## Testing Strategy

- Uses **Pest 3.0** (modern PHPUnit wrapper)
- Each package has its own test directory with custom base test class
- Tests use database transactions for isolation
- Test suites configured in `phpunit.xml`:
  - Admin Feature Test
  - Core Unit Test
  - DataGrid Unit Test
  - Installer Feature Test
  - Shop Feature Test

**Adding tests to a package:**
1. Create test in `packages/Webkul/YourPackage/tests/Feature/` or `Unit/`
2. Extend the package's base test case (e.g., `AdminTestCase`)
3. Run with `php artisan test --testsuite="Your Test Suite"`

## Important Configuration Files

- `config/app.php` - Application configuration (timezone, locale, etc.)
- `config/database.php` - Database connections
- `config/cache.php` - Cache driver configuration
- `config/bagisto-vite.php` - Vite asset configuration
- `config/concord.php` - Concord package manager configuration
- `.env` - Environment variables (create from `.env.example`)
- `composer.json` - PHP dependencies and autoloading
- `vite.config.js` - Vite bundler configuration

## Package Development Notes

When working on packages in `packages/Webkul/`:

1. **Namespace**: Each package uses `Webkul\PackageName\` namespace
2. **Service Provider**: Register services via package's `ServiceProvider`
3. **Configuration**: Use `config:publish` or `vendor:publish` to publish configs
4. **Translations**: Place translation files in `src/Resources/lang/`
5. **Assets**: Frontend resources in `src/Resources/` (views, css, js)
6. **Routes**: Define in package's service provider or separate routes file
7. **Database**: Migrations in package root or database directory

## Database & Migrations

- **Driver**: MySQL 8.0
- **Migrations**: Run `php artisan migrate`
- **Seeders**: Place in `database/seeders/` and run with `php artisan db:seed`
- **Test Database**: Automatically set up by Laravel Sail with transactions

The docker-compose.yml includes a testing database setup script.

## Frontend Asset Building

- **Tool**: Vite 5 with Laravel Vite Plugin
- **Config**: `vite.config.js` at root (symlinked packages have their own)
- **Dev Server**: `npm run dev` (port 5173 by default)
- **Production Build**: `npm run build`
- **Entry Points**: `resources/css/app.css`, `resources/js/app.js`

PostCSS and Tailwind are configured in package-level configs (see `packages/Webkul/Admin/postcss.config.cjs`).

## Docker Development (Laravel Sail)

The project includes a complete Docker setup via `docker-compose.yml`:

**Services:**
- `laravel.test` - PHP 8.3 application server
- `mysql` - MySQL 8.0 database
- `redis` - Redis cache
- `elasticsearch` - Full-text search
- `kibana` - Elasticsearch UI (port 5601)
- `mailpit` - Email testing (UI on port 8025)

**Ports:**
- App: 80 (configurable via APP_PORT env var)
- Vite Dev: 5173 (configurable via VITE_PORT env var)
- MySQL: 3306
- Redis: 6379
- Elasticsearch: 9200, 9300
- Mailpit UI: 8025

## Contributing Guidelines

From CONTRIBUTING.md:
- Bug fixes → latest development branch
- Minor backwards-compatible features → latest stable branch
- Major features → master branch
- **Don't commit compiled assets** (js/css) - maintainers will generate
- Follow PSR-2 code style
- Provide PHPDoc for all functions

## Extension Points

Bagisto is highly extensible:
- **Service Container**: Use Laravel's DI container in service providers
- **Events**: Fire and listen to events throughout the application
- **Models**: Extend core models via `Eloquent::extend()`
- **Routes**: Register additional routes in service providers
- **Views**: Override package views in main `resources/views/`
- **Config**: Publish and override package configs
- **Middleware**: Register custom middleware in providers

## Common Debugging

- **Laravel DebugBar**: Installed in dev dependencies, shows queries/timing
- **XDebug**: Enabled via `SAIL_XDEBUG_MODE` env var
- **Error Logs**: Check `storage/logs/laravel.log`
- **Database**: Access MySQL at localhost:3306 (credentials in `.env`)
- **Cache Issues**: Run `php artisan cache:clear`

## Performance Considerations

- **Full Page Caching**: Spatie Laravel Response Cache configured
- **Elasticsearch**: Used for product search (optional but recommended for large catalogs)
- **Redis**: Used for caching, sessions, and queue operations
- **Lazy Loading**: Use in Eloquent queries to avoid N+1 problems
- **Asset Caching**: Vite handles cache busting in production

## References

- [Bagisto Docs](https://devdocs.bagisto.com/)
- [Laravel Docs](https://laravel.com/docs/11.x)
- [Pest Testing](https://pestphp.com/)
- [Vite Guide](https://vitejs.dev/)
