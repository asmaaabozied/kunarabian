# ADR 005: BNPL + Shipping integration

**Status:** Accepted  
**Date:** 2026  
**References:** ADR-001 (Event-driven monolith), ADR-002 (Payment integration), ADR-004 (Webkul upgrade policy)

## Context

KUN bundles two major custom payment services: **BNPL** (Tamara, Tabby via `Kun\PaymentGateway`) and **Shipping** (Aramex, Jeebly via `Kun\Shipping`). These packages must integrate without direct coupling. The order lifecycle often requires both:

1. Customer selects a BNPL method at checkout.
2. Order is placed (payment gateway processes redirect).
3. Order arrives at processing state (waiting for fulfillment).
4. Seller ships via Aramex or Jeebly; shipment tracking begins.
5. Tracking updates reflect on the order (customer sees status in timeline).

The challenge: OrderService, ShipmentService, and payment drivers must remain independent modules. Events per ADR-001 are the communication mechanism.

## Decision

### 1. Event Order & Capture Policy (after shipment, not before)

**Shipping precedes payment capture:**

- When an order is placed with BNPL, it enters a `pending` or `on_hold` state.
- The payment **redirect URL does not guarantee completion**; capture happens asynchronously via webhook or polling.
- **Shipments are created after order confirmation**, not before payment capture (seller/admin action → shipment).
- **Capture policy:** Trigger capture **after shipment is created**, not as a preventive condition.

**Rationale:**  
- Some BNPL providers (Tamara, Tabby) require a shipment to be associated with the order before marking it as "completed" or "shipped."
- Separating shipment creation from payment capture allows parallel processing: payment processes asynchronously; seller creates shipment independently.
- If a shipment fails to persist, the payment capture is not affected (idempotent retry).

**Event sequence:**

```
Order placed
  → kun.payment.order.placed (optional internal event)
    → Redirect to BNPL provider
      → Provider webhook (asynchronously)
        → kun.payment.order.captured (BNPL confirms)
          → Order state: confirmed
Seller creates shipment
  → kun.shipping.shipment.created
    → May trigger downstream actions (email, fulfillment system, etc.)
Tracking updates received
  → kun.shipping.status.updated
    → Timeline events for customer
```

### 2. Event Contracts & Payloads

Events emitted by shipping and payment packages follow a stable contract (see ADR-001 for naming). Listeners must not depend on internal models; instead, they consume:

- **Event object** (typed) with public properties or `toArray()` method.
- **Event payload** (documented JSON shapes) in `docs/shipping-events.md` and `docs/payment-events.md`.

**Shipping Events:**

Event Name | Emitted From | Payload Reference | Stability
---|---|---|---
`kun.shipping.rates.fetched` | `Carrier::getQuotes()` | `docs/shipping-events.md` | Core fields stable
`kun.shipping.shipment.created` | `ShipmentService::createShipment()` | `docs/shipping-events.md` | Core fields stable
`kun.shipping.status.updated` | `ShipmentService::persistTrackingUpdate()` | `docs/shipping-events.md` | Core fields stable

**Payment Events:**

Event Name | Emitted From | Payload Reference | Stability
---|---|---|---
`kun.payment.order.placed` | `PaymentController` (post-redirect) | `docs/payment-events.md` | Core fields stable
`kun.payment.order.captured` | `WebhookController` (provider callback) | `docs/payment-events.md` | Core fields stable
`kun.payment.order.failed` | `WebhookController` (failure callback) | `docs/payment-events.md` | Core fields stable

### 3. Listener Registration & Package Isolation

**Listeners live in the listening package's own Listeners directory or service provider:**

- `Kun\Shipping\Listeners\*` listen to payment events → to trigger post-shipment actions.
- `Kun\PaymentGateway\Listeners\*` listen to shipping events → to update order state or capture logic.

**Registration pattern:**

```php
// In Kun\Shipping\Providers\ShippingServiceProvider
protected $listen = [
    // Listen to EXTERNAL events
    'kun.payment.order.captured' => [
        'Kun\Shipping\Listeners\OnPaymentCaptured',
    ],
];
```

**No direct imports across packages.** Listeners access data only from the event payload, not by importing payment/shipping models.

### 4. Idempotency & Duplicate Handling

**Shipping:** Tracking numbers and source context provide idempotency.

```php
// In a listener, detect duplicate status updates:
Event::listen(StatusUpdated::class, function ($event) {
    $existing = ShipmentTracking::whereTrackingNumber($event->trackingNumber)
        ->whereStatus($event->newStatus)
        ->whereSource($event->source)
        ->exists();
    if ($existing) return; // Idempotent, skip.
});
```

**Payment:** Provider transaction IDs (per ADR-002) ensure idempotency.

```php
// In a listener:
Event::listen('kun.payment.order.captured', function ($event) {
    $captured = OrderPayment::whereProviderTransactionId($event->providerTxId)->exists();
    if ($captured) return; // Already processed.
});
```

### 5. No Direct BNPL-Shipping Coupling

The following **MUST NOT** happen:

```php
// ❌ BAD: Direct class import across packages
namespace Kun\Shipping\Services;
use Kun\PaymentGateway\Models\PaymentCapture; // FORBIDDEN

// ❌ BAD: Direct service call
class ShipmentService {
    public function bookShipment() {
        $checker = new PaymentGatewayService(); // FORBIDDEN
    }
}

// ❌ BAD: Private API dependency
$order->paymentGateway()->capture(); // If PaymentGateway internals change, shipping breaks
```

**Allowed interactions:**

```php
// ✅ GOOD: Event listener approach
Event::listen('kun.payment.order.captured', function ($event) {
    // Access only event properties, not models
    $orderId = $event->order_id;
    $providerId = $event->provider_id;
    // Emit our own event if needed
});

// ✅ GOOD: Contract interface
use Kun\PaymentGateway\Contracts\PaymentProvider;
class ShippingGateway {
    public function supportsCarrier(PaymentProvider $provider) {
        // Compare types, not instances
    }
}
```

### 6. Testing Event Integration

Test listeners and event chains in isolation:

```php
// In Kun\Shipping\Tests\Feature\
class ShipmentCreatedEventTest extends TestCase {
    public function test_shipment_created_event_is_dispatched() {
        Event::fake();
        
        $shipmentService = new AramexShipmentService();
        $shipmentService->createShipment($booking, $shipmentData);
        
        Event::assertDispatched('kun.shipping.shipment.created', function ($event) {
            return $event->carrier === 'aramex';
        });
    }
}
```

## Consequences

### Positive

- BNPL and Shipping packages remain independent; upgrade or replace one without affecting the other.
- Clear event boundaries (ADR-001) mean new payment methods or carriers can be added safely.
- Listeners are testable in isolation (unit test a listener without spinning up full Aramex API).
- Order state is managed by a neutral orchestrator (OrderService, checkout flow), not by payment/shipping internals.

### Negative

- Developers must remember **not to** import across packages; linting/IDE may not catch violations.
- Event payloads must be versioned and documented; breaking changes require communication.
- More boilerplate (listeners, event registration) than tightly-coupled code, but safer long-term.

## References

- **ADR-001:** Event-driven monolith (event naming, cross-package boundaries, Contracts).
- **ADR-002:** Payment integration strategy (BNPL, webhook idempotency, capture policy).
- **ADR-004:** Webkul upgrade policy (no edits under `packages/Webkul/`).
- **Shipping events spec:** `docs/shipping-events.md` (payload details for rates, shipments, tracking).
- **Payment events spec:** `docs/payment-events.md` (payload details for BNPL, captures).
- **Implementation:** 
  - `packages/Kun/Shipping/src/Events/` (RatesFetched, ShipmentCreated, StatusUpdated)
  - `packages/Kun/PaymentGateway/src/Events/` (OrderPlaced, OrderCaptured, OrderFailed)
