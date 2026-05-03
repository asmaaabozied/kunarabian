# ADR 002: Payment integration strategy

**Status:** Accepted
**Date:** 2026

## Context

KUN needs to add payment methods beyond the ones shipped with Bagisto/Webkul, in particular **BNPL** (Tamara, Tabby) and potentially other gateways later. We must do this without modifying Webkul core (ADR 004) and with a consistent, secure pattern so that every new method follows the same rules: same base, same flow shape, webhook verification, and idempotency.

The existing Webkul Paypal package (`Webkul\Paypal\Payment\Paypal` extends `Webkul\Payment\Payment\Payment`) demonstrates the extension pattern. KUN drivers follow the same approach but add stricter rules around webhook security and duplicate handling.

## Decision

### 1. Extend Webkul Payment base class

All new payment methods (BNPL and any future gateways) MUST extend the existing Webkul base:

- **Base class:** `Webkul\Payment\Payment\Payment` (in `packages/Webkul/Payment/src/Payment/Payment.php`).
- **Do not fork or replace** this class. Implement a concrete driver in `packages/Kun/` (e.g. `packages/Kun/PaymentBNPL/`) that extends it.
- **Implement** at least: `getCode()`, `getTitle()`, `getRedirectUrl()` (abstract in base), and use `getConfigData()` for settings.
- **Register** the driver class in payment config (e.g. `paymentmethods.php`) with a `class` key pointing to the concrete driver so Bagisto's payment flow picks it up automatically.
- **Reference:** Existing Webkul Paypal package extends this base (`Webkul\Paypal\Payment\Paypal` extends `Webkul\Payment\Payment\Payment`); KUN drivers follow the same pattern.

### 2. Redirect-based flow for BNPL

BNPL (Tamara, Tabby) uses a **redirect flow**:

1. Customer selects BNPL at checkout.
2. Application redirects to the **provider's hosted page** (with cart/order context as required by the provider).
3. Provider redirects the customer back to our **success** or **cancel** URLs.
4. Payment confirmation may arrive **asynchronously** via webhook; the return URL is for UX, not necessarily the source of truth for "paid".

Rules:

- We do **not** embed the provider's full checkout in an iframe; redirect is the standard.
- Success/cancel/return URLs are configured per method and must be stable and HTTPS.
- The `getRedirectUrl()` method on the driver returns the provider URL to redirect to after order placement.

### 3. Webhook signature verification

All provider callbacks (webhooks / IPN) MUST be **verified** before updating order or payment state:

- Use the provider's documented **signature mechanism** (e.g. HMAC, shared secret, or signed payload in headers/body).
- **Reject** requests that are unsigned, invalid, or fail verification (respond with 4xx, do not update state).
- Verification MUST run in the webhook controller/handler **before** any persistence or side effects.
- Log verification failures for debugging and security monitoring.

### 4. Idempotency via provider transaction ID

Use the **provider's transaction (or reference) ID** as the idempotency key:

- When receiving a webhook or processing a return, check whether we have **already processed** this transaction ID.
- If **yes:** return success to the provider and do **not** re-apply the operation (e.g. do not double-capture, do not create duplicate invoice or order update).
- If **no:** process the event, then **store** the provider transaction ID on the order or payment record.
- Document where the transaction ID is stored (e.g. which table/column) in implementation notes for each payment method.

## Consequences

### Positive

- Consistent behaviour across all payment methods; new developers follow the same pattern.
- Safe Webkul upgrades; we extend, not modify, the base class.
- Clear security rules for webhooks — no unverified state changes.
- Duplicate webhooks or retries are safe due to idempotency.

### Negative

- Every new payment method must implement verification and idempotency logic.
- Provider APIs may change and require adapter updates.
- Redirect flow adds latency compared to inline checkout (acceptable trade-off for BNPL).

## References

- ADR 001: Event-driven monolith (events for payment lifecycle, e.g. `kun.payment.order.captured`).
- ADR 004: No modifications under `packages/Webkul/`.
- ADR 005: BNPL + Shipping integration (capture policy, event contracts between payment and shipping).
- Webkul Payment base: `packages/Webkul/Payment/src/Payment/Payment.php`.
- Webkul Paypal extension: `packages/Webkul/Paypal/src/Payment/Paypal.php`.
