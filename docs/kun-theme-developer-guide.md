# Kun Theme — Frontend Developer Guide

Complete guide for creating new pages, components, and customizations in the Kun theme.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Project Structure](#2-project-structure)
3. [How Theme Inheritance Works](#3-how-theme-inheritance-works)
4. [Overriding an Existing Blade View](#4-overriding-an-existing-blade-view)
5. [Creating a New Blade Page](#5-creating-a-new-blade-page)
6. [Creating a Vue Component (Inside Blade)](#6-creating-a-vue-component-inside-blade)
7. [Creating a Reusable Blade Component](#7-creating-a-reusable-blade-component)
8. [Creating a Homepage Section](#8-creating-a-homepage-section)
9. [Adding CSS Styles](#9-adding-css-styles)
10. [Design Tokens & Tailwind](#10-design-tokens--tailwind)
11. [Working with Images & Assets](#11-working-with-images--assets)
12. [Translations (i18n)](#12-translations-i18n)
13. [Using Bagisto APIs in Vue Components](#13-using-bagisto-apis-in-vue-components)
14. [Adding Custom API Routes](#14-adding-custom-api-routes)
15. [RTL Support](#15-rtl-support)
16. [Build & Development Commands](#16-build--development-commands)
17. [Common Patterns & Examples](#17-common-patterns--examples)
18. [Gotchas & Rules](#18-gotchas--rules)
19. [Component Reference: Vue Components](#19-component-reference-vue-components)
20. [Component Reference: Kun Blade Components](#20-component-reference-kun-blade-components)
21. [Component Reference: CSS Components & Utility Classes](#21-component-reference-css-components--utility-classes)
22. [Component Reference: Shop Blade Components Used in Kun](#22-component-reference-shop-blade-components-used-in-kun)

---

## 1. Architecture Overview

The Kun theme is a **child theme** that extends Bagisto's default Shop theme. It uses a two-layer system:

```
Default Shop Theme (packages/Webkul/Shop/)
    └── Kun Child Theme (resources/themes/kun/views/ + packages/Kun/Theme/)
```

**Key principle:** We NEVER modify files in `packages/Webkul/`. All customizations happen in:
- `resources/themes/kun/views/` — Blade view overrides
- `packages/Kun/Theme/` — CSS, JS, images, Vite config, translations, routes

**Asset loading** (defined in `resources/themes/kun/views/components/layouts/index.blade.php`):
```blade
{{-- Layer 1: Default Shop JS + CSS (provides Vue app, core components) --}}
@bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'], 'shop')

{{-- Layer 2: Kun CSS only (overrides/extends default styles) --}}
@bagistoVite(['src/Resources/assets/css/app.css'], 'shop-kun')
```

The `app` Vue instance is created by the Shop's `app.js` — Kun does NOT create its own.

---

## 2. Project Structure

```
bagisto/
├── config/
│   ├── themes.php              # Theme registry (kun has parent: 'default')
│   └── bagisto-vite.php        # Vite viters: 'shop', 'shop-kun', 'admin'
│
├── resources/themes/kun/views/ # Blade view overrides (mirrors Shop's view paths)
│   ├── components/
│   │   ├── layouts/
│   │   │   ├── index.blade.php     # Main layout (fonts, asset loading)
│   │   │   ├── header.blade.php    # Header with Vue nav components
│   │   │   └── footer.blade.php    # Footer with dynamic links
│   │   └── products/
│   │       └── card.blade.php      # Product card (global override)
│   ├── home/
│   │   ├── index.blade.php                 # Homepage (loops $customizations)
│   │   ├── kun-image-carousel.blade.php    # Image slider component
│   │   ├── kun-category-carousel.blade.php # Category carousel
│   │   ├── kun-product-carousel.blade.php  # Product grid component
│   │   ├── kun-vendor-carousel.blade.php   # Vendor cards
│   │   ├── kun-vendor-stories.blade.php    # Vendor stories
│   │   └── kun-products-with-promo.blade.php
│   ├── checkout/                           # Checkout page overrides
│   └── products/                           # Product page overrides
│
├── packages/Kun/Theme/         # Kun theme package
│   ├── src/
│   │   ├── KunThemeServiceProvider.php     # Routes, views, translations
│   │   └── Resources/
│   │       ├── assets/
│   │       │   ├── css/
│   │       │   │   ├── app.css             # Entry point (imports tokens + components)
│   │       │   │   ├── tokens.css          # Design tokens (colors, fonts, spacing)
│   │       │   │   └── components/         # CSS component files
│   │       │   │       ├── badge.css
│   │       │   │       ├── vendor-card.css
│   │       │   │       ├── story-card.css
│   │       │   │       └── ghost-button.css
│   │       │   ├── js/
│   │       │   │   └── app.js              # Kun JS entry (minimal)
│   │       │   └── images/                 # Theme images (built into manifest)
│   │       │       ├── logo.svg
│   │       │       ├── favicon.ico
│   │       │       ├── hero-image.webp
│   │       │       ├── spinner.svg
│   │       │       ├── thank-you.png
│   │       │       └── ...
│   │       └── lang/
│   │           ├── en/app.php              # English translations
│   │           └── ar/app.php              # Arabic translations
│   ├── vite.config.js                      # Vite build config
│   ├── tailwind.config.cjs                 # Tailwind config with Kun tokens
│   └── package.json                        # npm dependencies
│
└── public/themes/shop/kun/build/           # Vite build output (git-ignored)
```

---

## 3. How Theme Inheritance Works

Bagisto resolves views by checking the **active theme first**, then falling back to the **parent theme**.

When you write `<x-shop::layouts.header />` or `@include('shop::home.index')`:

1. Bagisto looks in `resources/themes/kun/views/` (active theme)
2. If not found, falls back to `packages/Webkul/Shop/src/Resources/views/` (parent)

**To override any Shop view**, place a file at the **same relative path** under `resources/themes/kun/views/`.

### Example: Overriding the search page

The default Shop's search page is at:
```
packages/Webkul/Shop/src/Resources/views/search/index.blade.php
```

To override it, create:
```
resources/themes/kun/views/search/index.blade.php
```

That's it — Bagisto automatically picks up your override.

---

## 4. Overriding an Existing Blade View

### Step-by-step

1. **Find the original view** in the default Shop:
   ```
   packages/Webkul/Shop/src/Resources/views/
   ```

2. **Copy it** to the same relative path under Kun:
   ```
   resources/themes/kun/views/<same/path/here>.blade.php
   ```

3. **Modify** the copied file. You have full control over the HTML/CSS/JS.

4. **Clear view cache** so Laravel picks up the new file:
   ```bash
   php artisan view:clear
   ```

### Example: Overriding the compare page

```bash
# 1. See original
cat packages/Webkul/Shop/src/Resources/views/compare/index.blade.php

# 2. Create override directory
mkdir -p resources/themes/kun/views/compare

# 3. Copy and modify
cp packages/Webkul/Shop/src/Resources/views/compare/index.blade.php \
   resources/themes/kun/views/compare/index.blade.php

# 4. Edit the file...

# 5. Clear cache
php artisan view:clear
```

---

## 5. Creating a New Blade Page

If you need a completely new page (not overriding an existing one), you need a **route** and a **view**.

### Step 1: Create the Blade view

Create your view file under `resources/themes/kun/views/`:

```blade
{{-- resources/themes/kun/views/custom/my-page.blade.php --}}

<x-shop::layouts>
    <x-slot:title>
        My Custom Page
    </x-slot>

    <section class="kun-section">
        <h1 class="kun-section-title">My Custom Page</h1>
        <p class="font-poppins text-base text-slate-700">
            Your content here.
        </p>
    </section>
</x-shop::layouts>
```

**Key points:**
- Always wrap in `<x-shop::layouts>` — this gives you the header, footer, fonts, and Vue app
- Use `<x-slot:title>` for the `<title>` tag
- Use `kun-section` class for consistent section spacing

### Step 2: Add a route

Add your route in `packages/Kun/Theme/src/KunThemeServiceProvider.php`:

```php
protected function registerRoutes(): void
{
    Route::group([
        'middleware' => ['web', 'theme', 'locale', 'currency'],
    ], function () {
        Route::get('/my-page', function () {
            return view('shop::custom.my-page');
        })->name('kun.my-page');
    });
}
```

**Middleware breakdown:**
- `web` — session, CSRF, cookies
- `theme` — activates the current shop theme
- `locale` — sets the active locale
- `currency` — sets the active currency

**View resolution:** `'shop::custom.my-page'` resolves to `resources/themes/kun/views/custom/my-page.blade.php` because of theme inheritance.

### Step 3: Clear cache and test

```bash
php artisan route:clear
php artisan view:clear
```

Visit `http://your-domain.test/my-page`

---

## 6. Creating a Vue Component (Inside Blade)

Vue components in Kun are registered **inline** in Blade files using two `<script>` blocks pushed to the `scripts` stack.

### Pattern

```blade
{{-- resources/themes/kun/views/some-page.blade.php --}}

{{-- Use the component in HTML --}}
<v-my-component title="Hello" :count="5">
    {{-- Optional: SSR fallback / shimmer shown before Vue mounts --}}
    <div class="shimmer w-full h-48 rounded-lg"></div>
</v-my-component>

{{-- Register scripts (only once, even if component appears multiple times) --}}
@pushOnce('scripts')
    {{-- Template --}}
    <script type="text/x-template" id="v-my-component-template">
        <div class="kun-section">
            <h2 class="kun-section-title">@{{ title }}</h2>

            <div v-if="isLoading">
                <div class="shimmer w-full h-48 rounded-lg"></div>
            </div>

            <div v-else>
                <p>Loaded @{{ items.length }} items</p>
                {{-- Use @{{ }} for Vue interpolation (@ escapes Blade) --}}
            </div>
        </div>
    </script>

    {{-- Component registration --}}
    <script type="module">
        app.component('v-my-component', {
            template: '#v-my-component-template',

            props: {
                title: { type: String, default: '' },
                count: { type: Number, default: 10 },
            },

            data() {
                return {
                    isLoading: true,
                    items: [],
                };
            },

            mounted() {
                this.fetchData();
            },

            methods: {
                fetchData() {
                    this.$axios.get('/api/some-endpoint')
                        .then(response => {
                            this.items = response.data.data || [];
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error(error);
                            this.isLoading = false;
                        });
                },
            },
        });
    </script>
@endPushOnce
```

### Rules for Vue components

| Rule | Details |
|------|---------|
| **Template ID** | Must match: `id="v-my-component-template"` in `<script type="text/x-template">` and `template: '#v-my-component-template'` in JS |
| **Tag name** | Use kebab-case: `<v-my-component>`. Prefix with `v-` by convention |
| **Registration** | Use `app.component('v-my-component', {...})` — `app` is the global Vue instance from Shop's `app.js` |
| **Script type** | Template: `type="text/x-template"`. JS: `type="module"` |
| **@pushOnce** | Use `@pushOnce('scripts')` (not `@push`) to avoid duplicate registration |
| **Vue interpolation** | Use `@{{ variable }}` — the `@` prevents Blade from parsing it |
| **Blade inside template** | `@lang()`, `@if`, `@auth`, `{{ route() }}` work inside `<script type="text/x-template">` because Blade processes them server-side before they reach Vue |
| **Blade components inside template** | `<x-shop::something>` does **NOT** work inside `<script type="text/x-template">` — use raw HTML instead |
| **SSR fallback** | Place shimmer/skeleton HTML as default slot content of the component tag |

### Available Vue globals

These are provided by Shop's `app.js` and available in every component:

```javascript
this.$axios    // Axios instance (pre-configured with CSRF, base URL)
this.$emitter  // Event bus for cross-component communication

// Common events:
this.$emitter.emit('add-flash', { type: 'success', message: 'Done!' });
this.$emitter.emit('update-mini-cart', cartData);

// Listening:
this.$emitter.on('some-event', (data) => { ... });
```

---

## 7. Creating a Reusable Blade Component

Blade components use the `<x-shop::component-name>` syntax and are resolved through theme inheritance.

### Option A: Override an existing Shop component

Place your file at the matching path:
```
resources/themes/kun/views/components/my-component.blade.php
```

Usage: `<x-shop::my-component />`

### Option B: Create under the Kun package namespace

If you want a component that's namespaced to Kun:

1. Create the view in the package:
   ```
   packages/Kun/Theme/src/Resources/views/components/my-widget.blade.php
   ```

2. Use it as:
   ```blade
   @include('kun-theme::components.my-widget', ['title' => 'Hello'])
   ```

   (The `kun-theme` namespace is registered in `KunThemeServiceProvider.php`)

### Component with props

```blade
{{-- resources/themes/kun/views/components/section-header.blade.php --}}
@props([
    'title' => '',
    'subtitle' => '',
    'link' => '',
    'linkText' => 'View All',
])

<div class="kun-section-header">
    <div class="flex flex-col gap-5">
        @if ($subtitle)
            <div class="flex items-center gap-2.5">
                <div class="kun-accent-line"></div>
                <span class="kun-subtitle-text">{{ $subtitle }}</span>
            </div>
        @endif
        <h2 class="kun-section-title">{{ $title }}</h2>
    </div>

    @if ($link)
        <a href="{{ $link }}" class="kun-link-accent">
            {{ $linkText }}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3.5 12h16.83" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    @endif
</div>
```

Usage:
```blade
<x-shop::section-header
    title="New Arrivals"
    subtitle="Fresh Picks"
    :link="route('shop.search.index', ['new' => 1])"
/>
```

---

## 8. Creating a Homepage Section

The homepage is dynamic — sections come from the `theme_customizations` database table and are rendered in `resources/themes/kun/views/home/index.blade.php`.

### How homepage rendering works

```blade
@foreach ($customizations as $customization)
    @php $data = $customization->options; @endphp

    @switch ($customization->type)
        @case ($customization::IMAGE_CAROUSEL)
            @include('shop::home.kun-image-carousel', [...])
            @break

        @case ($customization::STATIC_CONTENT)
            {{-- Custom Kun components via 'kun_component' key --}}
            @if (! empty($data['kun_component']))
                @include('shop::home.kun-' . $data['kun_component'], [...])
            @else
                {{-- Raw HTML/CSS from admin --}}
            @endif
            @break

        @case ($customization::CATEGORY_CAROUSEL)
            @include('shop::home.kun-category-carousel', [...])
            @break

        @case ($customization::PRODUCT_CAROUSEL)
            @include('shop::home.kun-product-carousel', [...])
            @break
    @endswitch
@endforeach
```

### Step-by-step: Adding a new homepage section

#### 1. Create the Blade partial

```blade
{{-- resources/themes/kun/views/home/kun-testimonials.blade.php --}}
@php
    $title = $options['title'] ?? 'What Our Customers Say';
@endphp

<v-kun-testimonials title="{{ $title }}">
    {{-- Shimmer fallback --}}
    <section class="kun-section" aria-label="{{ $title }}">
        <div class="grid grid-cols-3 gap-6">
            @for ($i = 0; $i < 3; $i++)
                <div class="shimmer rounded-2xl h-[200px]"></div>
            @endfor
        </div>
    </section>
</v-kun-testimonials>

@pushOnce('scripts')
    <script type="text/x-template" id="v-kun-testimonials-template">
        <section class="kun-section" :aria-label="title">
            <h2 class="kun-section-title">@{{ title }}</h2>
            <div class="grid grid-cols-3 gap-6 max-md:grid-cols-1">
                <div v-for="item in testimonials" :key="item.id"
                     class="bg-white rounded-2xl p-6 border border-slate-200">
                    <p class="text-slate-700 font-poppins text-sm">
                        "@{{ item.text }}"
                    </p>
                    <p class="text-slate-900 font-medium mt-4">@{{ item.name }}</p>
                </div>
            </div>
        </section>
    </script>

    <script type="module">
        app.component('v-kun-testimonials', {
            template: '#v-kun-testimonials-template',
            props: ['title'],
            data() {
                return { testimonials: [] };
            },
            mounted() {
                // Fetch from your API or use static data
                this.testimonials = [
                    { id: 1, name: 'Sarah', text: 'Amazing quality!' },
                    { id: 2, name: 'Ahmed', text: 'Fast shipping!' },
                    { id: 3, name: 'Maria', text: 'Love the variety!' },
                ];
            },
        });
    </script>
@endPushOnce
```

#### 2. Wire it into the homepage

In `resources/themes/kun/views/home/index.blade.php`, add a case:

```blade
@case ($customization::STATIC_CONTENT)
    @if (! empty($data['kun_component']))
        @include('shop::home.kun-' . $data['kun_component'], [
            'options' => $data,
            'customization' => $customization,
        ])
    @else
        ...
    @endif
    @break
```

Then in the **Admin panel** (Settings > Themes), create a STATIC_CONTENT customization with this JSON in options:
```json
{
    "kun_component": "testimonials",
    "title": "What Our Customers Say"
}
```

The `kun_component` value maps to `kun-testimonials.blade.php` (prefixed with `kun-`).

#### 3. Or add it to the seeder

In `packages/Kun/Theme/Database/Seeders/KunThemeCustomizationSeeder.php`, add a new entry to auto-populate the section when seeding.

---

## 9. Adding CSS Styles

### Where CSS lives

```
packages/Kun/Theme/src/Resources/assets/css/
├── app.css            # Entry point — imports everything
├── tokens.css         # Design tokens (CSS custom properties)
└── components/        # Component-specific CSS files
    ├── badge.css
    ├── vendor-card.css
    ├── story-card.css
    └── ghost-button.css
```

### Adding a new CSS component

1. **Create the CSS file:**

```css
/* packages/Kun/Theme/src/Resources/assets/css/components/testimonial.css */

.kun-testimonial-card {
    background: var(--kun-color-white);
    border: 1px solid var(--kun-slate-200);
    border-radius: var(--kun-radius-2xs);
    padding: var(--kun-space-xl);
    transition: box-shadow 0.2s ease;
}

.kun-testimonial-card:hover {
    box-shadow: var(--kun-shadow-md);
}

/* Always add RTL support */
[dir="rtl"] .kun-testimonial-card {
    text-align: right;
}
```

2. **Import it in `app.css`:**

```css
@import './tokens.css';
@import './components/badge.css';
@import './components/vendor-card.css';
@import './components/story-card.css';
@import './components/ghost-button.css';
@import './components/testimonial.css';  /* Add here */
```

3. **Rebuild:**
```bash
cd packages/Kun/Theme && npm run build
```

### Tailwind vs Custom CSS

- **Use Tailwind classes** for layout, spacing, and one-off styles in Blade templates
- **Use custom CSS files** for reusable component styles (like `.kun-product-card`, `.kun-vendor-card`)
- **Use CSS custom properties** from `tokens.css` in your custom CSS for consistency

### Available Tailwind utilities

Tailwind is configured in `packages/Kun/Theme/tailwind.config.cjs`. Key extensions:

```
Colors:     kun-primary-{50-950}, kun-secondary-{50-950}, kun-black-{50-950}, kun-slate-{50-950}
Fonts:      font-poppins, font-dmserif, font-kun-display, font-kun-body
Spacing:    kun-4xs through kun-8xl
Radius:     rounded-kun-4xs through rounded-kun-full
Shadows:    shadow-kun-sm, shadow-kun-md, shadow-kun-lg, shadow-kun-xl
```

---

## 10. Design Tokens & Tailwind

### CSS Custom Properties (tokens.css)

Available everywhere in your CSS:

```css
/* Colors */
--kun-primary-500: #DFA913;         /* Gold */
--kun-secondary-500: #E86A27;       /* Orange */
--kun-slate-950: #020617;           /* Near-black */

/* Fonts */
--kun-font-display: 'DM Serif Display', serif;   /* Headings */
--kun-font-body: 'Poppins', sans-serif;           /* Body text */

/* Spacing */
--kun-space-section-x: 90px;        /* Horizontal page padding */
--kun-space-section-y: 48px;        /* Vertical section padding */
--kun-space-s: 16px;
--kun-space-xl: 24px;

/* Border Radius */
--kun-radius-2xs: 12px;
--kun-radius-3xl: 32px;
--kun-radius-full: 9999px;          /* Pill shape */

/* Shadows */
--kun-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.08);
--kun-shadow-md: 0 4px 16px rgba(15, 23, 42, 0.12);

/* RTL */
--kun-direction: ltr;               /* Automatically set to rtl in [dir="rtl"] */
```

### Tailwind equivalents

These are configured in `tailwind.config.cjs` and mirror the CSS tokens:

```html
<!-- Colors -->
<div class="bg-kun-primary-500 text-kun-slate-950">

<!-- Fonts -->
<p class="font-poppins">Body text</p>
<h1 class="font-dmserif">Display heading</h1>

<!-- Spacing -->
<div class="p-kun-xl gap-kun-s">

<!-- Radius -->
<div class="rounded-kun-2xs">

<!-- Shadows -->
<div class="shadow-kun-md">
```

### The brand-color classes (from design system)

The Tailwind config also imports colors from `tailwind.colors.config.cjs`. These include:
- `brand-color-01-*` (brown/terracotta palette)
- `brand-color-02-*` (olive/dark palette)
- `brand-color-03-*` (cream/light palette)

---

## 11. Working with Images & Assets

### Adding images

1. Place image files in:
   ```
   packages/Kun/Theme/src/Resources/assets/images/
   ```

2. Rebuild so Vite adds them to the manifest:
   ```bash
   cd packages/Kun/Theme && npm run build
   ```

3. Reference in Blade using `bagisto_asset()`:
   ```blade
   <img src="{{ bagisto_asset('images/my-image.png') }}" alt="...">
   ```

### Important: Image fallback

`bagisto_asset()` resolves through the **current theme's Vite manifest**. There is **NO automatic fallback** to the parent theme's images. If the Shop theme has an image that Kun needs (e.g., `thank-you.png`, `logo.svg`), you must **copy it into Kun's images directory**.

Current images in Kun:
```
cash-on-delivery.png    hero-image.webp        small-product-placeholder.webp
default-language.svg    large-product-placeholder.webp  spinner.svg
empty-dwn-product.png   logo.svg               thank-you.png
favicon.ico             medium-product-placeholder.webp  user-placeholder.png
hero-image.jpg          money-transfer.png     wishlist.png
                        no-address.png
                        paypal.png
                        review.png
```

### Static images (not in Vite manifest)

For images that don't need cache-busting (uploaded via admin, external URLs), use direct paths:
```blade
<img src="{{ asset('themes/shop/kun/images/something.png') }}">
```

---

## 12. Translations (i18n)

### File locations

```
packages/Kun/Theme/src/Resources/lang/
├── en/app.php    # English
└── ar/app.php    # Arabic
```

### Translation file structure

```php
<?php
// packages/Kun/Theme/src/Resources/lang/en/app.php

return [
    'home' => [
        'discover-more'    => 'Discover More',
        'add-to-cart'      => 'Add to cart',
        'visit-shop'       => 'Visit shop',
    ],

    'layout' => [
        'header' => [
            'cart'           => 'Cart',
            'all-categories' => 'All Categories',
        ],
    ],

    'my-new-section' => [
        'title' => 'My Section Title',
    ],
];
```

### Using translations

In Blade templates:
```blade
{{-- Kun theme translations --}}
@lang('kun-theme::app.home.discover-more')

{{-- Default Shop translations (also available) --}}
@lang('shop::app.components.products.card.add-to-cart')
```

Inside Vue templates (`<script type="text/x-template">`):
```blade
{{-- Blade @lang() works here because it's processed server-side --}}
<button>@lang('kun-theme::app.home.add-to-cart')</button>
```

### Adding new translations

1. Add the key to `en/app.php`
2. Add the Arabic translation to `ar/app.php`
3. Use `@lang('kun-theme::app.your.key')` in templates

**Namespace:** `kun-theme` is the namespace registered in `KunThemeServiceProvider.php`.

---

## 13. Using Bagisto APIs in Vue Components

The Shop's `app.js` provides a pre-configured `$axios` instance on every Vue component.

### Common API endpoints

```javascript
// Categories
this.$axios.get('{{ route("shop.api.categories.tree") }}')
this.$axios.get('{{ route("shop.api.categories.index") }}')

// Products
this.$axios.get('{{ route("shop.api.products.index") }}', {
    params: { limit: 10, sort: 'created_at', order: 'desc' }
})

// Cart
this.$axios.post('{{ route("shop.api.checkout.cart.store") }}', {
    product_id: productId,
    quantity: 1,
})

// Wishlist
this.$axios.post('{{ route("shop.api.customers.account.wishlist.store") }}', {
    product_id: productId,
})

// Compare
this.$axios.post('{{ route("shop.api.compare.store") }}', {
    product_id: productId,
})
```

### Using route() in Vue templates

Because Vue templates are inside `<script type="text/x-template">`, Blade processes them first. This means `{{ route('...') }}` works:

```blade
<script type="text/x-template" id="v-my-component-template">
    <a :href="'{{ route('shop.product_or_category.index', '') }}/' + product.url_key">
        @{{ product.name }}
    </a>
</script>
```

### Flash messages

```javascript
// Success
this.$emitter.emit('add-flash', { type: 'success', message: 'Added to cart!' });

// Warning
this.$emitter.emit('add-flash', { type: 'warning', message: 'Already in compare' });

// Error
this.$emitter.emit('add-flash', { type: 'error', message: 'Something went wrong' });
```

### Cross-component communication

```javascript
// Emit an event
this.$emitter.emit('my-custom-event', { data: 'value' });

// Listen in another component
mounted() {
    this.$emitter.on('my-custom-event', (data) => {
        console.log(data); // { data: 'value' }
    });
}
```

---

## 14. Adding Custom API Routes

Add routes in `packages/Kun/Theme/src/KunThemeServiceProvider.php`:

```php
protected function registerRoutes(): void
{
    Route::group([
        'middleware' => ['web', 'theme', 'locale', 'currency'],
        'prefix' => 'kun/api',
    ], function () {
        // Your API routes
        Route::get('my-endpoint', function () {
            // Your logic
            return new JsonResponse($data);
        })->name('kun.api.my_endpoint');
    });
}
```

Use in Vue:
```javascript
this.$axios.get('{{ route("kun.api.my_endpoint") }}')
```

---

## 15. RTL Support

The Kun theme supports both LTR and RTL layouts (English + Arabic).

### How it works

- The `<html>` tag gets `dir="{{ core()->getCurrentLocale()->direction }}"` automatically
- CSS tokens switch: `--kun-direction: rtl` when `[dir="rtl"]` is active
- The font switches to Tajawal for Arabic: `--kun-font-body: 'Tajawal', sans-serif`
- Tailwind RTL plugin (`tailwindcss-rtl`) is installed

### Writing RTL-compatible CSS

```css
/* In your custom component CSS */
.my-component {
    padding-left: 16px;
    text-align: left;
}

/* Add RTL override */
[dir="rtl"] .my-component {
    padding-left: 0;
    padding-right: 16px;
    text-align: right;
}
```

### Using Tailwind RTL utilities

The `tailwindcss-rtl` plugin provides direction-aware classes:

```html
<!-- Instead of pl-4 / pr-4, use: -->
<div class="ps-4">  <!-- padding-start: works in both LTR and RTL -->
<div class="pe-4">  <!-- padding-end -->
<div class="ms-4">  <!-- margin-start -->
<div class="me-4">  <!-- margin-end -->
```

---

## 16. Build & Development Commands

### Daily development

```bash
# Start Vite dev server (hot reload for Kun theme CSS)
cd packages/Kun/Theme && npm run dev

# In another terminal, start the Laravel app
php artisan serve
```

### After making changes

```bash
# After changing Blade files
php artisan view:clear

# After changing CSS/images in packages/Kun/Theme/src/Resources/assets/
cd packages/Kun/Theme && npm run build

# After changing routes
php artisan route:clear

# After changing translations
php artisan cache:clear

# Seed homepage content (if needed)
php artisan db:seed --class="Kun\Theme\Database\Seeders\KunThemeCustomizationSeeder"
```

### Installing dependencies

```bash
cd packages/Kun/Theme && npm install
```

---

## 17. Common Patterns & Examples

### Pattern: Section with API data

A typical Kun homepage section follows this pattern:

```
1. Blade partial with <v-component> tag and shimmer fallback
2. @pushOnce('scripts') with template + component registration
3. Component fetches data from API on mount
4. Shimmer → real content transition
```

### Pattern: Shimmer loading states

Always provide a skeleton UI while data loads:

```blade
{{-- Shimmer card --}}
<div class="shimmer rounded-[32px] h-[434px]"></div>

{{-- Shimmer text line --}}
<div class="shimmer w-32 h-4 rounded"></div>

{{-- Shimmer circle --}}
<div class="shimmer w-10 h-10 rounded-full"></div>
```

### Pattern: Conditional features from admin config

```blade
@if (core()->getConfigData('catalog.products.settings.compare_option'))
    {{-- Show compare button --}}
@endif

@if (core()->getConfigData('customer.settings.wishlist.wishlist_option'))
    {{-- Show wishlist button --}}
@endif

@if (core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
    {{-- Show cart button --}}
@endif
```

### Pattern: Auth-aware actions

```blade
<script type="text/x-template" id="v-my-template">
    <button @click="doAction()">Action</button>
</script>

<script type="module">
    app.component('v-my-component', {
        template: '#v-my-template',
        data() {
            return {
                isCustomer: '{{ auth()->guard("customer")->check() }}',
            };
        },
        methods: {
            doAction() {
                if (this.isCustomer) {
                    // Do the thing
                } else {
                    window.location.href = '{{ route("shop.customer.session.index") }}';
                }
            },
        },
    });
</script>
```

### Pattern: Product URL generation

```javascript
// In a Vue component
computed: {
    productUrl() {
        return `{{ route('shop.product_or_category.index', '') }}/${this.product.url_key}`;
    }
}
```

---

## 18. Gotchas & Rules

### Critical rules

1. **NEVER modify files in `packages/Webkul/`** — always override in `resources/themes/kun/views/` or `packages/Kun/Theme/`

2. **Kun does NOT load its own `app.js`** — the Vue instance (`window.app`) comes from the Shop's JS. Never create a second Vue app.

3. **`@bagistoVite` requires an explicit namespace** — always use `'shop'` or `'shop-kun'`. Without a namespace, it resolves to the current theme, which can cause confusion.

4. **No `<x-shop::...>` inside `<script type="text/x-template">`** — Blade components don't work inside Vue templates. Use raw HTML instead.

5. **Use `@{{ }}` for Vue interpolation** — the `@` escapes Blade. Without it, Blade tries to parse it as a PHP expression and fails.

6. **Use `@pushOnce` not `@push` for component scripts** — prevents duplicate registration when a component appears multiple times on a page.

7. **Images must exist in Kun's assets** — `bagisto_asset()` has NO parent theme fallback. If you need a Shop image, copy it to `packages/Kun/Theme/src/Resources/assets/images/` and rebuild.

8. **Always rebuild after CSS/image changes:**
   ```bash
   cd packages/Kun/Theme && npm run build
   ```

9. **Clear view cache after Blade changes:**
   ```bash
   php artisan view:clear
   ```

### Common mistakes

| Mistake | Fix |
|---------|-----|
| Vue component not rendering | Check: template ID matches, `app.component()` name matches tag, scripts in `@pushOnce('scripts')` |
| Styles not applying | Did you run `npm run build` in `packages/Kun/Theme`? |
| Image 404 | Image must be in `packages/Kun/Theme/src/Resources/assets/images/` AND you must rebuild |
| Blade `{{ }}` inside Vue template | Use `@{{ }}` for Vue variables |
| Component registered twice | Use `@pushOnce` not `@push` |
| Route not found | Run `php artisan route:clear`. Check middleware includes `web`, `theme`, `locale`, `currency` |
| Translations not showing | Check namespace: `@lang('kun-theme::app.section.key')`. Run `php artisan cache:clear` |
| `<x-shop::component>` not working in Vue | Can't use Blade components inside `<script type="text/x-template">` — write raw HTML |

---

## Quick Reference: File Checklist for New Features

When adding a new feature, you typically touch these files:

| What | File |
|------|------|
| New page view | `resources/themes/kun/views/<section>/<page>.blade.php` |
| New homepage section | `resources/themes/kun/views/home/kun-<name>.blade.php` |
| Page route | `packages/Kun/Theme/src/KunThemeServiceProvider.php` |
| CSS component | `packages/Kun/Theme/src/Resources/assets/css/components/<name>.css` |
| CSS import | `packages/Kun/Theme/src/Resources/assets/css/app.css` (add `@import`) |
| Images | `packages/Kun/Theme/src/Resources/assets/images/` |
| English text | `packages/Kun/Theme/src/Resources/lang/en/app.php` |
| Arabic text | `packages/Kun/Theme/src/Resources/lang/ar/app.php` |
| Homepage seed data | `packages/Kun/Theme/Database/Seeders/KunThemeCustomizationSeeder.php` |

After changes:
```bash
cd packages/Kun/Theme && npm run build   # CSS/image changes
php artisan view:clear                    # Blade changes
php artisan route:clear                   # Route changes
php artisan cache:clear                   # Translation/config changes
```

---

## 19. Component Reference: Vue Components

All Vue components are registered inline in Blade files via `app.component(...)` inside `@pushOnce('scripts')` blocks. They use the global `app` Vue instance from Shop's `app.js`.

---

### 19.1 `<v-kun-locale-switcher>`

**File:** `resources/themes/kun/views/components/layouts/header.blade.php`
**Props:** None (locale list is rendered server-side in the template)

Renders the top-bar locale switcher. Shows one link per locale; clicking appends `?locale=X` to the current URL and navigates.

**Usage:**
```blade
<v-kun-locale-switcher></v-kun-locale-switcher>
```

---

### 19.2 `<v-kun-header-nav>`

**File:** `resources/themes/kun/views/components/layouts/header.blade.php`
**Props:** None
**Data:** `categories` (array), `openCategory` (null | id)

Desktop category mega-navigation (hidden below `lg` breakpoint). Fetches categories from `shop.api.categories.tree` on mount. Emits `categories-loaded` event for the scroll bar to reuse. Shows hover-triggered dropdown panels with child and grandchild links.

**Usage:**
```blade
<v-kun-header-nav></v-kun-header-nav>
```

---

### 19.3 `<v-kun-category-scroll>`

**File:** `resources/themes/kun/views/components/layouts/header.blade.php`
**Props:** None
**Data:** `categories` (array), `activeCategory` (null | id), drag state vars

Horizontal drag-scrollable category pill bar below the main header. Listens for `categories-loaded` event from `v-kun-header-nav` (falls back to independent fetch after 3s). Renders an "All Categories" pill + one pill per top-level category. Supports mouse drag scrolling.

**Usage:**
```blade
<v-kun-category-scroll></v-kun-category-scroll>
```

---

### 19.4 `<v-kun-image-carousel>`

**File:** `resources/themes/kun/views/home/kun-image-carousel.blade.php`
**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `carousel-id` | String | required | Unique ID for the carousel instance |
| `:slides` | Array | `[]` | Array of slide objects: `{ image, title, subtitle, item_count, link }` |
| `:auto-play` | Boolean | `true` | Auto-advance every 5 seconds |
| `label` | String | `''` | ARIA label for the section |

Full-width hero image carousel. Shows one slide at a time with text overlay (subtitle, title, item count) and a CTA link. Supports mouse drag and touch swipe (50px threshold). Dot pagination at bottom.

**Usage:**
```blade
<v-kun-image-carousel
    carousel-id="hero-1"
    :slides='@json($images)'
    :auto-play="true"
    label="Hero Banner"
>
    {{-- SSR fallback --}}
    <section class="kun-section">
        <div class="kun-hero">
            <div class="shimmer absolute inset-0"></div>
        </div>
    </section>
</v-kun-image-carousel>
```

---

### 19.5 `<v-kun-category-carousel>`

**File:** `resources/themes/kun/views/home/kun-category-carousel.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `src` | String | Category tree API URL |
| `flat-src` | String | Flat categories API URL (has images) |
| `title` | String | Section title |
| `carousel-id` | String | Unique ID |

Horizontal scrollable category image strip. Fetches tree + flat APIs in parallel, merges to get images. Left/right arrow navigation scrolls 380px per click. Shows shimmer while loading.

**Usage:**
```blade
<v-kun-category-carousel
    src="{{ route('shop.api.categories.tree') }}"
    flat-src="{{ route('shop.api.categories.index', ['limit' => 100]) }}"
    title="Shop by Category"
    carousel-id="cats-1"
>
    <section class="kun-section">
        <div class="flex gap-4 overflow-hidden">
            @for ($i = 0; $i < 6; $i++)
                <div class="shimmer rounded-[14px] w-[170px] h-[130px]"></div>
            @endfor
        </div>
    </section>
</v-kun-category-carousel>
```

---

### 19.6 `<v-kun-product-carousel>`

**File:** `resources/themes/kun/views/home/kun-product-carousel.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `src` | String | Products API URL (with filters) |
| `title` | String | Section title |
| `subtitle` | String | Subtitle above title |
| `navigation-link` | String | "View All" link URL |
| `carousel-id` | String | Unique ID |
| `:grid-rows` | Number | `1` = 3 products, `2` = 6 products |

Homepage product grid section. Fetches products from API. Renders section header with accent line + subtitle + title + "View All" link. Products displayed in `kun-grid-3`. Each card has image, tags, rating, price, add-to-cart, and wishlist buttons.

**Usage:**
```blade
<v-kun-product-carousel
    src="{{ route('shop.api.products.index', ['new' => 1, 'limit' => 6]) }}"
    title="New Arrivals"
    subtitle="Fresh Picks"
    navigation-link="{{ route('shop.search.index', ['new' => 1]) }}"
    carousel-id="new-arrivals"
    :grid-rows="2"
>
    <section class="kun-section">
        <div class="kun-grid-3">
            @for ($i = 0; $i < 6; $i++)
                <div class="shimmer rounded-[32px] h-[434px]"></div>
            @endfor
        </div>
    </section>
</v-kun-product-carousel>
```

---

### 19.7 `<v-kun-products-with-promo>`

**File:** `resources/themes/kun/views/home/kun-products-with-promo.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `src` | String | Products API URL |
| `title` | String | Section title |
| `subtitle` | String | Subtitle |
| `:promo` | Object | `{ title, highlight, text, background }` — promo banner config |
| `carousel-id` | String | Unique ID |

"Everyone's Watching" layout — 2-column product grid (max 6 products) alongside a tall promotional banner on the right. The promo banner has configurable background color, diagonal stripe decoration, and three text sizes.

**Usage:**
```blade
<v-kun-products-with-promo
    src="{{ route('shop.api.products.index', ['sort' => 'most_viewed']) }}"
    title="Most Viewed"
    subtitle="Everyone's Watching"
    :promo='@json(["title" => "Spring", "highlight" => "50% OFF", "text" => "Limited Time", "background" => "#3B3D2B"])'
    carousel-id="most-viewed"
>
    <section class="kun-section">
        <div class="shimmer rounded-[32px] h-[500px]"></div>
    </section>
</v-kun-products-with-promo>
```

---

### 19.8 `<v-kun-vendor-carousel>`

**File:** `resources/themes/kun/views/home/kun-vendor-carousel.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `title` | String | Section title |

Horizontal scrollable vendor/shop cards fetching from `kun.api.featured_sellers`. Each card (417x500px, 36px radius) shows banner image, circular logo, business name, category, product/sales stats, 5-star rating, and "Visit Shop" button. Left/right arrows scroll 441px. Dot pagination when sellers > 3.

**Usage:**
```blade
<v-kun-vendor-carousel title="Our Premium Shops">
    <section class="kun-section">
        <div class="kun-grid-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="shimmer rounded-[36px] h-[500px]"></div>
            @endfor
        </div>
    </section>
</v-kun-vendor-carousel>
```

---

### 19.9 `<v-kun-vendor-stories>`

**File:** `resources/themes/kun/views/home/kun-vendor-stories.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `title` | String | Section title |
| `subtitle` | String | Subtitle |

Full-width vendor story slider fetching from `kun.api.featured_sellers`. Renders one story at a time as a tall card with full-bleed banner, gradient overlay, tag pills, text content, and vendor logo box. Left/right arrows and dot pagination.

**Usage:**
```blade
<v-kun-vendor-stories
    title="Vendor Stories"
    subtitle="Meet the Makers"
>
    <section class="kun-section">
        <div class="shimmer rounded-2xl h-[500px]"></div>
    </section>
</v-kun-vendor-stories>
```

---

### 19.10 `<v-product-card>`

**File:** `resources/themes/kun/views/components/products/card.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `mode` | String | `'list'` for horizontal layout, omit for grid card |
| `:product` | Object | Bagisto product API object |

The global product card used on category pages, search, compare, etc. Renders image, brand/sale/new tag pills, view count, star rating, short description, price, and action buttons (Add to Cart, Wishlist, Compare in list mode). Handles all cart/wishlist/compare API calls internally.

**Usage (in Blade, outside Vue):**
```blade
<x-shop::products.card :product="$product" />
```

**Usage (in a parent Vue component):**
```html
<v-product-card :product="product" mode="grid"></v-product-card>
```

---

### 19.11 `<v-product-gallery>`

**File:** `resources/themes/kun/views/products/view/gallery.blade.php`
**Data:** `isImageZooming`, `isMediaLoading`, `media.images`, `media.videos`, `baseFile`, `activeIndex`

Product detail page image gallery. Manages active image/video, syncs thumbnail scroll, delegates to `<x-shop::image-zoomer>` for zoom. Gallery images are reactive — `v-product-configurable-options` can replace them on variant selection.

**Usage (inside product view):**
```blade
<v-product-gallery ref="gallery">
    <x-shop::shimmer.products.gallery />
</v-product-gallery>
```

---

### 19.12 `<v-product-configurable-options>`

**File:** `resources/themes/kun/views/products/view/types/configurable.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `:errors` | Object | VeeValidate error bag |

Configurable product attribute selector. Supports four swatch types: `dropdown` (select), `color` (circular swatches), `image` (square thumbnails), `text` (pill buttons). On selection, cascades to dependent attributes, updates price in DOM, and replaces gallery images.

**Usage (inside product form):**
```blade
<v-product-configurable-options
    :errors="errors"
>
    <x-shop::shimmer.products.view />
</v-product-configurable-options>
```

---

### 19.13 `<v-product>`

**File:** `resources/themes/kun/views/products/view.blade.php`
**Data:** `isWishlist`, `isCustomer`, `is_buy_now`, `isStoring`

Root product detail page component. Wraps the entire product form. Handles Add to Cart, Buy Now, Wishlist toggle, Compare, Share (Web Share API or clipboard). On mount checks wishlist status.

**Usage:**
```blade
<v-product>
    {{-- Product detail content --}}
</v-product>
```

---

### 19.14 `<v-kun-product-tabs>`

**File:** `resources/themes/kun/views/products/view.blade.php`
**Data:** `activeTab` — one of `'description'`, `'additional'`, `'reviews'`

Desktop product detail tabs. Three tab buttons with `.kun-tab-active` on the active one. Toggling shows/hides content panels via `v-show`.

**Usage (inside product view):**
```blade
<v-kun-product-tabs>
    {{-- Tab content panels --}}
</v-kun-product-tabs>
```

---

### 19.15 `<v-product-associations>`

**File:** `resources/themes/kun/views/products/view.blade.php`
**Data:** `isVisible` (bool)

Lazy-rendered related products and up-sell carousels. Uses `IntersectionObserver` to defer rendering until the component scrolls into view.

**Usage:**
```blade
<v-product-associations>
    <x-shop::shimmer.products.carousel />
</v-product-associations>
```

---

### 19.16 `<v-cart>`

**File:** `resources/themes/kun/views/checkout/cart/index.blade.php`
**Data:** `cart`, `allSelected`, `applied.quantity`, `displayTax`, `isLoading`, `isStoring`

Full cart page component. Fetches cart via API, renders items with checkboxes, quantity controls, delete buttons. Supports bulk remove, bulk move-to-wishlist. Contains `<v-cart-coupon>` as child.

**Usage:**
```blade
<v-cart>
    <x-shop::shimmer.checkout.cart :count="3" />
</v-cart>
```

---

### 19.17 `<v-cart-coupon>`

**File:** `resources/themes/kun/views/checkout/cart/summary.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `:cart` | Object | The full cart object from API |

**Emits:** `coupon-applied`, `coupon-removed`

Coupon code widget in the cart summary. Shows applied coupon with remove button, or input + apply button.

**Usage (inside v-cart):**
```html
<v-cart-coupon
    :cart="cart"
    @coupon-applied="getCart"
    @coupon-removed="getCart"
></v-cart-coupon>
```

---

### 19.18 `<v-checkout>`

**File:** `resources/themes/kun/views/checkout/onepage/index.blade.php`
**Data:** `cart`, `displayTax`, `isPlacingOrder`, `currentStep`, `shippingMethods`, `paymentMethods`, `canPlaceOrder`

Root one-page checkout orchestrator. Manages the multi-step flow: `address` → `shipping` → `payment` → `review`. Shows/hides child components based on `currentStep`. Handles Place Order API call and redirect.

**Usage:**
```blade
<v-checkout>
    <x-shop::shimmer.checkout.onepage />
</v-checkout>
```

---

### 19.19 `<v-checkout-address-customer>`

**File:** `resources/themes/kun/views/checkout/onepage/address/customer.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `:cart` | Object | The cart object |

**Emits:** `processing`, `processed`

Authenticated-user address step. Fetches saved addresses, renders selectable radio cards in a 2-column grid. Has "Add new address" button. Supports "use billing for shipping" checkbox. Uses `<v-checkout-address-form>` for inline create/edit.

---

### 19.20 `<v-checkout-address-guest>`

**File:** `resources/themes/kun/views/checkout/onepage/address/guest.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `:cart` | Object | The cart object |

**Emits:** `processing`, `processed`

Guest address step. Renders billing form and optional shipping form when "use billing for shipping" is unchecked.

---

### 19.21 `<v-checkout-address-form>`

**File:** `resources/themes/kun/views/checkout/onepage/address/form.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `control-name` | String | Field name prefix (`billing` or `shipping`) |
| `:address` | Object | Address data with fields: `id`, `company_name`, `first_name`, `last_name`, `email`, `address[]`, `country`, `state`, `city`, `postcode`, `phone` |

Reusable address field form (9 fields). Fetches countries and states from API on mount. Uses VeeValidate for validation.

---

### 19.22 `<v-shipping-methods>`

**File:** `resources/themes/kun/views/checkout/onepage/shipping.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `:methods` | Object | Array of available shipping methods |

**Emits:** `processing`, `processed`

Grid of shipping rate cards (title, description, price). On click stores selection via API and advances to payment step.

---

### 19.23 `<v-payment-methods>`

**File:** `resources/themes/kun/views/checkout/onepage/payment.blade.php`
**Props:**

| Prop | Type | Description |
|------|------|-------------|
| `:methods` | Object | Array of available payment methods |

**Emits:** `processing`, `processed`

Horizontal grid of payment method cards (image + title). On click stores selection via API and advances to review step.

---

## 20. Component Reference: Kun Blade Components

These are server-side Blade components defined in `packages/Kun/Theme/src/Resources/views/components/`. Use with the `kun-theme::` namespace.

---

### 20.1 `<x-kun-theme::badge>`

**File:** `packages/Kun/Theme/src/Resources/views/components/badge.blade.php`
**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `type` | string | `'verified-vendor'` | Badge type |
| `country` | string\|null | `null` | Country name for "made-in" type |
| `label` | string\|null | `null` | Custom label (overrides type default) |

Available types and their appearance:

| Type | CSS Class | Color | Icon |
|------|-----------|-------|------|
| `verified-vendor` | `.kun-badge--verified` | Gold (yellow bg, amber text) | ✓ |
| `handmade` | `.kun-badge--handmade` | Orange (peach bg, orange text) | ✦ |
| `made-in` | `.kun-badge--made-in` | Slate (gray bg, dark text) | ◈ |
| `origin-verified` | `.kun-badge--origin` | Green (green bg, dark green text) | ⊕ |
| `limited-run` | `.kun-badge--limited` | Dark (black bg, white text) | ◆ |

**Usage:**
```blade
<x-kun-theme::badge type="verified-vendor" />
<x-kun-theme::badge type="made-in" country="Morocco" />
<x-kun-theme::badge type="handmade" label="Handcrafted" />
<x-kun-theme::badge type="limited-run" />
```

**Output:**
```html
<span class="kun-badge kun-badge--verified">
    <span class="kun-badge__icon">✓</span> Verified Vendor
</span>
```

---

### 20.2 `<x-kun-theme::vendor-card>`

**File:** `packages/Kun/Theme/src/Resources/views/components/vendor-card.blade.php`
**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `seller` | array | `[]` | Seller data object |

**Expected `seller` keys:**

```php
[
    'business_name' => 'Artisan Goods',
    'name'          => 'vendor-slug',
    'country'       => 'Morocco',
    'rating'        => 4.5,        // float 0-5
    'review_count'  => 128,        // int
    'logo_url'      => 'https://...', // nullable
    'profile_url'   => '/vendor/artisan-goods',
    'badges'        => [           // array of badge configs
        ['type' => 'verified-vendor'],
        ['type' => 'made-in', 'country' => 'Morocco'],
    ],
]
```

Renders a full vendor card with: logo area (image or initials), name link, country, star rating, review count, badges row, and "Visit Shop" CTA button.

**Usage:**
```blade
<x-kun-theme::vendor-card :seller="[
    'business_name' => 'Desert Craft',
    'country' => 'UAE',
    'rating' => 4.8,
    'review_count' => 56,
    'logo_url' => '/images/vendors/desert-craft.jpg',
    'profile_url' => '/vendor/desert-craft',
    'badges' => [
        ['type' => 'verified-vendor'],
        ['type' => 'origin-verified'],
    ],
]" />
```

---

### 20.3 `<x-kun-theme::story-card>`

**File:** `packages/Kun/Theme/src/Resources/views/components/story-card.blade.php`
**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `seller` | array | `[]` | Seller/story data object |

**Expected `seller` keys:**

```php
[
    'business_name' => 'Artisan Goods',
    'story_short'   => 'Three generations of...',  // 3-line excerpt
    'origin_region' => 'Atlas Mountains',           // nullable
    'banner_url'    => 'https://...',               // nullable (gradient fallback)
    'profile_url'   => '/vendor/artisan-goods',
    'country'       => 'Morocco',                   // nullable
]
```

Renders a story card with: 16:9 banner image (or gradient placeholder), optional origin pill overlay, vendor name (DM Serif heading), 3-line clamped excerpt, and "Read story →" CTA link.

**Usage:**
```blade
<x-kun-theme::story-card :seller="[
    'business_name' => 'Sahara Textiles',
    'story_short' => 'From the heart of the desert, weaving stories into every thread...',
    'origin_region' => 'Sahara Desert',
    'banner_url' => '/images/stories/sahara.jpg',
    'profile_url' => '/vendor/sahara-textiles',
]" />
```

---

## 21. Component Reference: CSS Components & Utility Classes

All CSS is in `packages/Kun/Theme/src/Resources/assets/css/`. Use these classes in your Blade templates and Vue component templates.

---

### 21.1 Layout & Section

| Class | Description | Responsive |
|-------|-------------|------------|
| `.kun-section` | Full-width section with 90px H / 48px V padding | 32px H at ≤1024, 16px H / 32px V at ≤640 |
| `.kun-section--tight` | Same horizontal, reduced vertical (16px) | Same H responsive |
| `.kun-section-title` | 40px Poppins medium heading | 32px at ≤1024, 24px at ≤640 |
| `.kun-section-header` | Flex row (space-between) for title + "View All" link | Stacks vertically at ≤1024 |
| `.kun-accent-line` | Short decorative horizontal rule before subtitles | — |
| `.kun-subtitle-text` | Small label text above section titles | — |
| `.kun-link-accent` | "View All" arrow link in section headers | — |

**Usage:**
```html
<section class="kun-section">
    <div class="kun-section-header">
        <div class="flex flex-col gap-5">
            <div class="flex items-center gap-2.5">
                <div class="kun-accent-line"></div>
                <span class="kun-subtitle-text">Fresh Picks</span>
            </div>
            <h2 class="kun-section-title">New Arrivals</h2>
        </div>
        <a href="/search" class="kun-link-accent">View All →</a>
    </div>
    <!-- content -->
</section>
```

---

### 21.2 Grids

| Class | Columns | Gap | Responsive |
|-------|---------|-----|------------|
| `.kun-grid-3` | 3 columns | 24px | 2 cols at ≤1024, 1 col at ≤640 |
| `.kun-grid-2` | 2 columns | 24px | 2 cols at ≤1024, 1 col at ≤640 |

**Usage:**
```html
<div class="kun-grid-3">
    <div>Card 1</div>
    <div>Card 2</div>
    <div>Card 3</div>
</div>
```

---

### 21.3 Product Card

| Class | Description |
|-------|-------------|
| `.kun-product-card` | Card wrapper — `#f9fafb` bg, 32px radius, flex-column |
| `.kun-product-image` | Image area — 168px height (140px at ≤640) |
| `.kun-product-info` | Card body with text content |
| `.kun-product-vendor` | Vendor/brand name text |
| `.kun-product-name-link` | Product title link (1-line truncate) |
| `.kun-product-desc` | Short description (2-line clamp) |
| `.kun-product-price` | Price HTML wrapper |
| `.kun-product-actions` | Flex row for action buttons |
| `.kun-btn-cart` | Primary "Add to Cart" pill button |
| `.kun-btn-wishlist` | Circular wishlist icon button (40x40) |
| `.kun-tag` | Base tag pill (absolute positioned) |
| `.kun-tag--brand` | Brand name tag — olive bg |
| `.kun-tag--sale` | Sale tag — red bg |
| `.kun-tag--new` | New tag — blue bg |
| `.kun-tag--origin` | Origin tag — green bg |
| `.kun-views` | Views counter (eye icon + number) |
| `.kun-rating` | Star rating flex row |

**Usage:**
```html
<div class="kun-product-card">
    <div class="kun-product-image">
        <img src="..." alt="..." class="w-full h-full object-cover">
        <div class="absolute top-4 left-4">
            <span class="kun-tag kun-tag--new">New</span>
        </div>
    </div>
    <div class="kun-product-info">
        <p class="kun-product-vendor">Brand Name</p>
        <h3><a href="..." class="kun-product-name-link">Product Name</a></h3>
        <div class="kun-product-actions">
            <button class="kun-btn-cart">Add to Cart</button>
            <button class="kun-btn-wishlist">♡</button>
        </div>
    </div>
</div>
```

---

### 21.4 Hero Carousel

| Class | Description |
|-------|-------------|
| `.kun-hero` | 480px tall rounded container (380px at ≤1024, 300px at ≤640) |
| `.kun-hero-slide` | Absolute fill slide (invisible by default) |
| `.kun-hero-slide--active` | Makes slide visible |
| `.kun-hero-text` | Text overlay — bottom-left, max 50% width |
| `.kun-hero-cta` | CTA button — bottom-right |
| `.kun-btn-cta` | Hero CTA arrow link |
| `.kun-dot` | Pagination dot (base) |
| `.kun-dot--active` | Active wider dot |
| `.kun-gradient-hero-bottom` | Bottom gradient overlay |
| `.kun-gradient-hero-left` | Left gradient overlay |

---

### 21.5 Category Card

| Class | Description |
|-------|-------------|
| `.kun-category-card` | 170x130px tile, 14px radius (140x110 at ≤640) |
| `.kun-gradient-bottom` | Bottom gradient for text overlay |

---

### 21.6 Navigation (Header)

| Class | Description |
|-------|-------------|
| `.kun-nav-item` | Relative wrapper for header nav items |
| `.kun-dropdown` | Mega-menu panel — white, 12px radius, shadow, animated entry |
| `.kun-dropdown-grid` | Flex row with 32px gap for columns |
| `.kun-dropdown-col` | Min-width 150px column |
| `.kun-dropdown-heading` | Bold category link (14px), bottom border |
| `.kun-dropdown-list` | Unstyled link list, 8px gap |
| `.kun-dropdown-link` | Child link (13px), turns terracotta on hover |

---

### 21.7 Badge (CSS)

| Class | Description |
|-------|-------------|
| `.kun-badge` | Base pill — 11px bold, pill radius |
| `.kun-badge__icon` | Icon span (10px) |
| `.kun-badge--verified` | Gold/yellow |
| `.kun-badge--handmade` | Warm orange |
| `.kun-badge--made-in` | Slate gray |
| `.kun-badge--origin` | Green |
| `.kun-badge--limited` | Dark/black |

---

### 21.8 Ghost Button

| Class | Description |
|-------|-------------|
| `.ghost-button` | Outlined CTA — gold border/text, fills on hover |
| `.ghost-button--light` | White variant for dark backgrounds |
| `.ghost-button--sm` | Small size (12px font) |
| `.ghost-button--lg` | Large size (16px font) |

**Usage:**
```html
<a href="/shop" class="ghost-button">Browse Collection</a>
<a href="/shop" class="ghost-button ghost-button--sm">View More</a>
<a href="/shop" class="ghost-button ghost-button--light">Discover</a>
```

---

### 21.9 Vendor Card (CSS)

| Class | Description |
|-------|-------------|
| `.kun-vendor-card` | Card wrapper — white, border, 8px radius, hover lift |
| `.kun-vendor-card__logo-wrap` | 16:9 logo/image area |
| `.kun-vendor-card__logo` | Full-cover logo image |
| `.kun-vendor-card__logo-placeholder` | Initials fallback (DM Serif, gold) |
| `.kun-vendor-card__body` | Card body (16px padding) |
| `.kun-vendor-card__name` | Vendor name (15px, 600 weight) |
| `.kun-vendor-card__name-link` | Name link (turns gold on hover) |
| `.kun-vendor-card__country` | Country text (12px, slate) |
| `.kun-vendor-card__rating` | Star rating row |
| `.kun-star--full` / `--half` | Gold star |
| `.kun-star--empty` | Gray star |
| `.kun-vendor-card__badges` | Badge flex-wrap row |
| `.kun-vendor-card__cta` | Full-width gold "Visit Shop" pill button |

---

### 21.10 Story Card (CSS)

| Class | Description |
|-------|-------------|
| `.kun-story-card` | Card wrapper — white, border, 8px radius, hover lift |
| `.kun-story-card__banner` | 16:9 image area |
| `.kun-story-card__banner-img` | Full-cover image (scales 1.03x on hover) |
| `.kun-story-card__banner-placeholder` | Gradient fallback |
| `.kun-story-card__origin` | Absolute pill overlay at bottom-left |
| `.kun-story-card__body` | Card body (16px padding, 10px gap) |
| `.kun-story-card__name` | Title (DM Serif, 17px) |
| `.kun-story-card__excerpt` | Body text (3-line clamp) |
| `.kun-story-card__cta` | "Read story" link (gold, arrow animates) |

---

### 21.11 Promo Section

| Class | Description |
|-------|-------------|
| `.kun-promo-layout` | Flex row, 24px gap (stacks at ≤1024) |
| `.kun-promo-banner` | 538px wide promo panel, 32px radius |
| `.kun-promo-title` | 48px / 800 weight |
| `.kun-promo-highlight` | 72px / 900 weight |
| `.kun-promo-text` | 36px / 800 weight |
| `.kun-promo-stripes` | Decorative diagonal stripe overlay |

---

### 21.12 Vendor Carousel & Stories

| Class | Description |
|-------|-------------|
| `.kun-vendor-track` | Scroll-snap horizontal flex container |
| `.kun-nav-arrow` | Circular 48px nav arrow button |
| `.kun-nav-arrow--left` / `--right` | Positional modifiers |
| `.kun-stories-card` | Full-width 500px story container, 16px radius |
| `.kun-stories-text` | Absolute text overlay |
| `.kun-stories-logo` | Vendor logo box (135x124px, bottom-right) |
| `.kun-stories-tags` | Tag pill row (top-left) |
| `.kun-stories-name` | 40px Poppins title (white) |
| `.kun-stories-desc` | 18px description (white) |

---

### 21.13 Product Detail Page (PDP)

| Class | Description |
|-------|-------------|
| `.kun-pdp-grid` | 2-column layout (588px + 1fr), 64px gap |
| `.kun-pdp-badge` | Inline pill badge |
| `.kun-pdp-btn-primary` | Filled dark-green pill CTA |
| `.kun-pdp-btn-outline` | Outlined dark-green pill CTA |
| `.kun-tab-btn` | Tab button (transparent bg, border-bottom) |
| `.kun-tab-active` | Active tab (terracotta text + border) |
| `.kun-pdp-description` | 4-line clamped description |

---

### 21.14 Cart Page

| Class | Description |
|-------|-------------|
| `.kun-cart-grid` | 2-column layout (1fr + 560px), 56px gap |
| `.kun-cart-item` | Individual cart item row |
| `.kun-cart-qty` | Quantity pill control (–, value, +) |
| `.kun-cart-trash` | Trash icon delete button |
| `.kun-cart-summary` | Order summary panel |
| `.kun-cart-promo` | Coupon input/display row |
| `.kun-cart-proceed-btn` | Full-width "Proceed to Checkout" CTA |

---

### 21.15 Checkout Page

| Class | Description |
|-------|-------------|
| `.kun-checkout-grid` | 2-column layout (1fr + 560px), 60px gap |
| `.kun-checkout-input` | Slate-100 bg input field, no border, focus ring |
| `.kun-checkout-select` | Select field with custom chevron SVG |
| `.kun-checkout-label` | Field label (16px) |
| `.kun-checkout-field` | Field wrapper (24px bottom margin) |
| `.kun-checkout-row-2` | 2-column form row |
| `.kun-checkout-row-3` | 3-column form row |
| `.kun-checkout-card` | Shipping/payment method card (100px, 24px radius) |
| `.kun-checkout-card--selected` | Selected state |
| `.kun-checkout-btn` | Full-width terracotta CTA |
| `.kun-checkout-breadcrumb` | Breadcrumb row with separators |
| `.kun-checkout-summary` | Cart summary panel |
| `.kun-checkout-item` | Cart item row in summary |

---

## 22. Component Reference: Shop Blade Components Used in Kun

These are default Bagisto Shop components (from `packages/Webkul/Shop/`) that Kun templates use directly. You don't need to redefine these — they work via theme inheritance.

### Layout & Structure

| Component | Description | Usage |
|-----------|-------------|-------|
| `<x-shop::layouts>` | Root page layout (overridden by Kun's `index.blade.php`) | Wrap every page |
| `<x-shop::layouts.header />` | Header (overridden by Kun) | Auto-included by layout |
| `<x-shop::layouts.footer />` | Footer (overridden by Kun) | Auto-included by layout |
| `<x-shop::layouts.services />` | Features/services bar | Auto-included by layout |
| `<x-shop::layouts.cookie />` | GDPR cookie consent banner | Auto-included by layout |
| `<x-shop::flash-group />` | Toast notification queue | Auto-included by layout |
| `<x-shop::modal.confirm />` | Confirmation dialog modal | Auto-included by layout |

### Forms

| Component | Description | Usage |
|-----------|-------------|-------|
| `<x-shop::form>` | Form wrapper with VeeValidate | `<x-shop::form v-slot="{ meta, errors, handleSubmit }" as="div">` |
| `<x-shop::form.control-group>` | Field group wrapper | Wraps label + input + error |
| `<x-shop::form.control-group.control>` | Input field | `type="text"`, `type="email"`, `type="select"`, etc. |
| `<x-shop::form.control-group.error>` | Validation error message | `control="field-name"` |
| `<x-shop::form.control-group.label>` | Field label | `:required="true"` |

### Products

| Component | Description | Usage |
|-----------|-------------|-------|
| `<x-shop::products.card>` | Product card (overridden by Kun) | `:product="$product"` |
| `<x-shop::products.carousel>` | Product carousel | `:title="'Related'" :src="route(...)"` |

### UI Components

| Component | Description | Usage |
|-----------|-------------|-------|
| `<x-shop::breadcrumbs>` | Breadcrumb trail | `name="product" :entity="$product"` |
| `<x-shop::quantity-changer>` | +/- quantity input | `name="quantity" value="1"` |
| `<x-shop::image-zoomer>` | Image lightbox/zoom | `::attachments ::is-image-zooming` |
| `<x-shop::accordion>` | Collapsible accordion | `<x-slot:header>` + `<x-slot:content>` |
| `<x-shop::dropdown>` | Dropdown menu | — |
| `<x-shop::drawer>` | Side drawer panel | — |
| `<x-shop::tabs>` | Tab navigation | — |

### Shimmer (Loading Skeletons)

| Component | Description |
|-----------|-------------|
| `<x-shop::shimmer.checkout.cart :count="3" />` | Cart page skeleton |
| `<x-shop::shimmer.checkout.onepage />` | Checkout page skeleton |
| `<x-shop::shimmer.checkout.onepage.address />` | Address step skeleton |
| `<x-shop::shimmer.checkout.onepage.shipping-method />` | Shipping step skeleton |
| `<x-shop::shimmer.checkout.onepage.payment-method />` | Payment step skeleton |
| `<x-shop::shimmer.products.gallery />` | Product gallery skeleton |
| `<x-shop::shimmer.products.view />` | Product page skeleton |
| `<x-shop::shimmer.products.carousel />` | Product carousel skeleton |

**Usage pattern:**
```blade
<v-my-component>
    {{-- Shimmer shown before Vue mounts --}}
    <x-shop::shimmer.products.gallery />
</v-my-component>
```
