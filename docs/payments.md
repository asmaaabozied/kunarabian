# Plan: Add Stripe Payment Method (with PaymentBNPL → PaymentGateway rename)

## Context

The project needs Stripe as a payment gateway. Per the ADRs:
- **ADR-004**: All custom code in `packages/Kun/`, no modifications to `packages/Webkul/`
- **ADR-002**: Extend Webkul Payment base class, redirect flow, webhook signature verification, idempotency
- **ADR-001**: Events use `kun.{domain}.{entity}.{action}` naming

The existing `Kun\PaymentBNPL` package is a scaffold with only config + providers (no payment classes yet). Per user request, rename it to `Kun\PaymentGateway` to hold **all** Kun payment methods (Tamara, Tabby, Stripe, future gateways) in one package.

**Stripe approach:** Stripe Checkout Sessions (redirect-based) for PCI compliance.

---

## Part 1: Rename `Kun\PaymentBNPL` → `Kun\PaymentGateway`

### Files to rename/move

Move entire directory: `packages/Kun/PaymentBNPL/` → `packages/Kun/PaymentGateway/`

### Files inside the package to update

| File | Changes |
|------|---------|
| `composer.json` | name → `kun/payment-gateway`, namespace → `Kun\\PaymentGateway\\` |
| `src/Providers/PaymentBNPLServiceProvider.php` | Rename file to `PaymentGatewayServiceProvider.php`, update namespace to `Kun\PaymentGateway\Providers`, config key `payment-bnpl` → `payment-gateway`, views namespace → `payment-gateway` |
| `src/Providers/ModuleServiceProvider.php` | Update namespace to `Kun\PaymentGateway\Providers` |
| `src/Config/payment-bnpl.php` | Rename to `paymentmethods.php`, update class references from `Kun\PaymentBNPL\Payment\*` → `Kun\PaymentGateway\Payment\*` |
| `src/Resources/manifest.php` | Update name to `Kun PaymentGateway` |
| `tests/Unit/PaymentBNPLServiceProviderTest.php` | Rename to `PaymentGatewayServiceProviderTest.php`, update namespace/class references, config key |

### External files to update

| File | Changes |
|------|---------|
| `composer.json` (root) | `autoload.psr-4`: `Kun\\PaymentBNPL\\` → `Kun\\PaymentGateway\\`; `autoload-dev.psr-4`: same; directory paths updated |
| `bootstrap/providers.php` | `Kun\PaymentBNPL\Providers\PaymentBNPLServiceProvider::class` → `Kun\PaymentGateway\Providers\PaymentGatewayServiceProvider::class` |
| `config/concord.php` | `Kun\PaymentBNPL\Providers\ModuleServiceProvider::class` → `Kun\PaymentGateway\Providers\ModuleServiceProvider::class` |
| `phpunit.xml` | Testsuite name → `PaymentGateway Unit Test`, directory → `packages/Kun/PaymentGateway/tests/Unit` |

**Note:** ADR docs reference `PaymentBNPL` historically — leave them unchanged.

---

## Part 2: Add Stripe to `Kun\PaymentGateway`

### Payment Flow

```
Customer clicks "Place Order"
  → OnepageController::storeOrder() calls Payment::getRedirectUrl()
  → Returns route('stripe.checkout.redirect')
  → CheckoutController::redirect() creates Stripe Checkout Session
  → Customer redirected to checkout.stripe.com
  → Customer pays → Stripe redirects to /stripe/checkout/success?session_id=xxx
  → CheckoutController::success() verifies payment, creates order + invoice
  → Fires kun.payment.order.captured event
  → Redirect to success page
  → Webhook (async safety net) for cases where customer doesn't return
```

### New files to create inside `packages/Kun/PaymentGateway/`

#### `src/Payment/StripeCheckout.php`
Extends `Webkul\Payment\Payment\Payment` (ADR-002 §1).

- `protected $code = 'stripe';`
- **`getRedirectUrl()`** — Returns `route('stripe.checkout.redirect')`
- **`createCheckoutSession($cart)`** — Creates `\Stripe\Checkout\Session`:
  - `mode: 'payment'`, `line_items` from cart (amounts in cents)
  - Shipping/tax as separate line items
  - `success_url` with `{CHECKOUT_SESSION_ID}` placeholder
  - `cancel_url` → cart, `customer_email` from billing
  - `metadata: ['cart_id' => $cart->id]`
- **`getStripeClient()`** — `\Stripe\StripeClient` with secret from `$this->getConfigData('secret_key')`
- **`getImage()`** — Stripe logo

#### `src/Http/Controllers/Stripe/CheckoutController.php`
Injects `StripeCheckout`, `OrderRepository`, `InvoiceRepository`.

- **`redirect()`** — Gets cart, creates session, redirects to `$session->url`
- **`success()`** — Verifies session via Stripe API, checks idempotency (ADR-002 §4), creates order (same `OrderResource` pattern as `SmartButtonController::saveOrder()`), sets status `processing`, creates invoice, stores payment intent ID in `additional` JSON, fires `kun.payment.order.captured`, deactivates cart
- **`cancel()`** — Flash error, redirect to cart
- **`webhook()`** — Verify signature via `\Stripe\Webhook::constructEvent()` **before any persistence** (ADR-002 §3), reject invalid with 400 + log, handle `checkout.session.completed` idempotently, return 200
- **`validateOrder()`** / **`prepareInvoiceData()`** — Same pattern as `SmartButtonController` lines 252-299

#### `src/Http/routes.php`
```php
Route::group(['middleware' => ['web']], function () {
    Route::prefix('stripe/checkout')->group(function () {
        Route::get('/redirect', ...)->name('stripe.checkout.redirect');
        Route::get('/success', ...)->name('stripe.checkout.success');
        Route::get('/cancel', ...)->name('stripe.checkout.cancel');
    });
});

Route::post('stripe/webhook', ...)
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
    ->name('stripe.webhook');
```

#### `src/Listeners/Transaction.php`
Listens to `sales.invoice.save.after`. When method is `stripe`, saves to `order_transactions` with payment intent ID, status, amount, session data. Pattern: `packages/Webkul/Paypal/src/Listeners/Transaction.php`.

#### `src/Providers/EventServiceProvider.php`
Register: `sales.invoice.save.after` → `Transaction@saveTransaction`

#### `src/Config/admin/system.php`
Admin config fields (merged into `'core'` — ADR-004 compliant, same pattern as Marketplace package). Key: `sales.payment_methods.stripe`. Fields:
- `title` (text, channel/locale), `description` (textarea, channel/locale)
- `image` (image upload, mimes validation)
- `publishable_key` (text, depends/required_if active)
- `secret_key` (password, depends/required_if active)
- `webhook_secret` (password, depends active)
- `active` (boolean, channel), `sandbox` (boolean, channel)
- `sort` (select 1-4)

#### `src/Resources/lang/en/app.php`
Translation strings for errors, messages, and admin config labels.

### Files to modify inside `packages/Kun/PaymentGateway/`

| File | Changes |
|------|---------|
| `src/Config/paymentmethods.php` (renamed from payment-bnpl.php) | Add `stripe` entry with `class => Kun\PaymentGateway\Payment\StripeCheckout` |
| `src/Providers/PaymentGatewayServiceProvider.php` | Add `mergeConfigFrom()` for `paymentmethods.php` → `'payment_methods'` key; add `mergeConfigFrom()` for `admin/system.php` → `'core'` key; `loadRoutesFrom()`; `loadTranslationsFrom('payment-gateway')`; register `EventServiceProvider` |

### External files to modify (in addition to Part 1)

| File | Changes |
|------|---------|
| `composer.json` (root) | Add `"stripe/stripe-php": "^16.0"` to `require` |

---

## ADR Compliance

| ADR | Requirement | Compliance |
|-----|------------|------------|
| ADR-004 §1 | No edits under `packages/Webkul/` | Package in `packages/Kun/PaymentGateway/`, admin config via `mergeConfigFrom('core')` |
| ADR-002 §1 | Extend Webkul Payment base class | `StripeCheckout extends Webkul\Payment\Payment\Payment` |
| ADR-002 §2 | Redirect-based flow | Stripe Checkout Sessions (redirect to stripe.com) |
| ADR-002 §3 | Webhook signature verification before persistence | `\Stripe\Webhook::constructEvent()` before any DB writes |
| ADR-002 §4 | Idempotency via provider transaction ID | Check order exists for cart_id; store payment intent ID |
| ADR-001 | Event naming `kun.{domain}.{entity}.{action}` | Fires `kun.payment.order.captured` |

---

## Verification

1. `composer dump-autoload` after all changes
2. Run existing test: `php artisan test --testsuite="PaymentGateway Unit Test"` — verify rename didn't break config registration
3. Configure Stripe keys in **Admin > Configuration > Sales > Payment Methods > Stripe**
4. **Checkout flow**: Select Stripe → Place Order → Redirect to Stripe → Pay → Order created with status `processing` + invoice
5. **Cancel flow**: Cancel on Stripe → Redirect to cart with error
6. **Webhook**: `stripe listen --forward-to localhost/stripe/webhook` → verify idempotent processing
7. Verify transaction in `order_transactions` table
8. Confirm zero files modified under `packages/Webkul/`

---

## Key Reference Files

- `packages/Webkul/Payment/src/Payment/Payment.php` — Base abstract class to extend
- `packages/Webkul/Paypal/src/Http/Controllers/SmartButtonController.php` — Order creation pattern (saveOrder, validateOrder, prepareInvoiceData)
- `packages/Webkul/Paypal/src/Providers/PaypalServiceProvider.php` — Service provider pattern
- `packages/Webkul/Paypal/src/Http/routes.php` — Route pattern + CSRF exclusion for webhooks
- `packages/Webkul/Paypal/src/Listeners/Transaction.php` — Transaction listener pattern
- `packages/Webkul/Admin/src/Config/system.php` lines 2113-2201 — PayPal config fields structure (reference for admin/system.php)
