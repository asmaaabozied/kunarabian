# Kun Shipping Event Payload Specifications

**Program Reference:** §3.10.1 | US-1c.2.3, US-1c.2.4, US-1c.2.5

This document specifies the JSON payload shapes for Kun shipping events emitted per ADR-001 (Event-driven monolith).

---

## Event 1: `kun.shipping.rates.fetched`

**Program Reference:** US-1c.2.3  
**Emitted From:**
- `Kun\Shipping\Carriers\Aramex::getQuotes()`
## - `Kun\Shipping\Carriers\Jeebly::getQuotes()`

**When:** After successfully fetching or generating shipping rate quotes from a carrier.

**Event Class:** `Kun\Shipping\Events\RatesFetched`

### Payload Shape

```json
{
  "carrier": "string",
  "shipment_data": {
    "origin": {
      "country_code": "string (ISO 3166-1 alpha-2, e.g., 'AE')",
      "city": "string",
      "address_line1": "string",
      "address_line2": "string (optional)",
      "state": "string (state/region code)",
      "postal_code": "string"
    },
    "destination": {
      "country_code": "string (ISO 3166-1 alpha-2)",
      "city": "string",
      "address_line1": "string",
      "address_line2": "string (optional)",
      "state": "string (state/region code)",
      "postal_code": "string"
    },
    "weight": "number (float, kg)",
    "dimensions": {
      "length": "number (cm)",
      "width": "number (cm)",
      "height": "number (cm)"
    }
  },
  "quotes": [
    {
      "carrier": "string (e.g., 'aramex', 'jeebly')",
      "service_type": "string (e.g., 'international_express', 'domestic_standard')",
      "service_name": "string (human-readable service name)",
      "price": "number (float)",
      "currency": "string (currency code, e.g., 'AED', 'USD')",
      "estimated_delivery": "string (e.g., '3-5 business days')",
      "raw_data": {
        "description": "object (carrier-specific response fields)"
      }
    }
  ],
  "environment": "string ('sandbox' or 'production')",
  "fetched_at": "string (ISO 8601 timestamp, e.g., '2026-04-15T10:30:00Z')"
}
```

### Example

```json
{
  "carrier": "aramex",
  "shipment_data": {
    "origin": {
      "country_code": "AE",
      "city": "Dubai",
      "address_line1": "Dubai Marina",
      "state": "DU",
      "postal_code": "00000"
    },
    "destination": {
      "country_code": "US",
      "city": "New York",
      "address_line1": "123 Broadway",
      "state": "NY",
      "postal_code": "10001"
    },
    "weight": 2.5,
    "dimensions": {
      "length": 30,
      "width": 20,
      "height": 15
    }
  },
  "quotes": [
    {
      "carrier": "aramex",
      "service_type": "international_express",
      "service_name": "Aramex International Express",
      "price": 150.00,
      "currency": "AED",
      "estimated_delivery": "3-5 business days",
      "raw_data": {
        "TotalAmount": 150.00,
        "CurrencyCode": "AED",
        "ProductGroup": "EXP"
      }
    }
  ],
  "environment": "sandbox",
  "fetched_at": "2026-04-15T10:30:00Z"
}
```

---

## Event 2: `kun.shipping.shipment.created`

**Program Reference:** US-1c.2.4  
**Emitted From:**
- `Kun\Shipping\Services\AramexShipmentService::createShipment()`

**When:** After a shipment record is successfully persisted to the database following a booking with a carrier.

**Event Class:** `Kun\Shipping\Events\ShipmentCreated`

### Payload Shape

```json
{
  "shipment_id": "integer (Bagisto shipment.id) or null",
  "order_id": "integer (Bagisto orders.id) or null",
  "carrier": "string (e.g., 'aramex', 'jeebly')",
  "tracking_number": "string (AWB / carrier-assigned number)",
  "label_url": "string (URL to shipping label/documentation) or null",
  "status": "string ('booked', 'processing', 'in_transit', etc.)",
  "shipment_data": {
    "description": "object (the shipment parameters sent to carrier.bookShipment())"
  },
  "booking_data": {
    "carrier": "string",
    "tracking_number": "string",
    "label_url": "string or null",
    "status": "string",
    "booked_at": "string (ISO 8601 timestamp)",
    "raw_data": {
      "description": "object (carrier-specific booking response)"
    }
  },
  "environment": "string ('sandbox' or 'production')",
  "created_at": "string (ISO 8601 timestamp)"
}
```

### Example

```json
{
  "shipment_id": 42,
  "order_id": 1,
  "carrier": "aramex",
  "tracking_number": "ARAMEX-SBX-1a2b3c4d5e",
  "label_url": "https://sandbox.aramex.com/labels/ARAMEX-SBX-1a2b3c4d5e.pdf",
  "status": "booked",
  "shipment_data": {
    "origin": {
      "country_code": "AE",
      "city": "Dubai",
      "address_line1": "Dubai Marina"
    },
    "destination": {
      "country_code": "US",
      "city": "New York",
      "address_line1": "123 Broadway"
    },
    "weight": 2.5,
    "service_type": "standard",
    "order_id": 1
  },
  "booking_data": {
    "carrier": "aramex",
    "tracking_number": "ARAMEX-SBX-1a2b3c4d5e",
    "label_url": "https://sandbox.aramex.com/labels/ARAMEX-SBX-1a2b3c4d5e.pdf",
    "status": "booked",
    "booked_at": "2026-04-15T10:30:00Z",
    "raw_data": {
      "HasErrors": false,
      "Shipments": [
        {
          "ID": 97531,
          "Reference1": ""
        }
      ]
    }
  },
  "environment": "sandbox",
  "created_at": "2026-04-15T10:30:05Z"
}
```

---

## Event 3: `kun.shipping.status.updated`

**Program Reference:** US-1c.2.5  
**Emitted From:**
- `Kun\Shipping\Services\AramexShipmentService::persistTrackingUpdate()`
- Triggered by tracking API responses or webhooks

**When:** After a shipment status changes in the system based on carrier tracking updates.

**Event Class:** `Kun\Shipping\Events\StatusUpdated`

### Payload Shape

```json
{
  "shipment_id": "integer (Bagisto shipment.id) or null",
  "order_id": "integer (Bagisto orders.id) or null",
  "carrier": "string (e.g., 'aramex', 'jeebly')",
  "tracking_number": "string (AWB / carrier-assigned number)",
  "old_status": "string (previous status)",
  "new_status": "string (current status after update)",
  "source": "string ('poll' or 'webhook' or other)",
  "context": {
    "description": "object (additional context about the source)"
  },
  "updated_at": "string (ISO 8601 timestamp)"
}
```

### Example (Poll Source)

```json
{
  "shipment_id": 42,
  "order_id": 1,
  "carrier": "aramex",
  "tracking_number": "ARAMEX-SBX-1a2b3c4d5e",
  "old_status": "booked",
  "new_status": "in_transit",
  "source": "poll",
  "context": {
    "polling_timestamp": "2026-04-16T14:30:00Z"
  },
  "updated_at": "2026-04-16T14:30:15Z"
}
```

### Example (Webhook Source)

```json
{
  "shipment_id": 42,
  "order_id": 1,
  "carrier": "aramex",
  "tracking_number": "ARAMEX-SBX-1a2b3c4d5e",
  "old_status": "in_transit",
  "new_status": "delivered",
  "source": "webhook",
  "context": {
    "webhook_timestamp": "2026-04-20T09:15:00Z",
    "webhook_path": "/api/kun/shipping/webhook/aramex"
  },
  "updated_at": "2026-04-20T09:15:10Z"
}
```

---

## Event Listener Pattern

To listen to these events, register a listener in your service provider or directly in `routes/console.php`:

```php
use Kun\Shipping\Events\RatesFetched;
use Kun\Shipping\Events\ShipmentCreated;
use Kun\Shipping\Events\StatusUpdated;

// Optionally in a service provider boot() method:
Event::listen(RatesFetched::class, function (RatesFetched $event) {
    // Handle rates fetched
    \Log::info('Rates fetched', $event->toArray());
});

Event::listen(ShipmentCreated::class, function (ShipmentCreated $event) {
    // Handle shipment created
    \Log::info('Shipment created', $event->toArray());
});

Event::listen(StatusUpdated::class, function (StatusUpdated $event) {
    // Handle status update
    \Log::info('Shipment status updated', $event->toArray());
});
```

Alternatively, using string-based event names (legacy Laravel):

```php
Event::listen('kun.shipping.rates.fetched', function ($event) {
    // Handle with event object or array payload
});

Event::listen('kun.shipping.shipment.created', function ($event) {
    // Handle
});

Event::listen('kun.shipping.status.updated', function ($event) {
    // Handle
});
```

---

## Constraints & Implementation Notes

### Per ADR-001 (Event-driven monolith)

- **Event naming:** `kun.{domain}.{entity}.{action}` pattern.
- **Cross-package communication:** Events are the only allowed interface; direct class imports are forbidden.
# - **Carrier isolation:** Jeebly (UAE local) and Aramex (international) emit  # identical event shapes; consumers do not depend on carrier internals.

### Per ADR-004 (Webkul upgrade policy)

- **No Webkul edits:** All event code lives in `packages/Kun/Shipping/`.
- **No vendor coupling:** Events use only Bagisto public models and Laravel facades.

### Per ADR-005 (BNPL + Shipping integration)

- **Event boundaries:** Events fire at clear integration points (rate fetch, shipment creation, status change).
- **Idempotency:** Status events include tracking number and source for duplicate detection by listeners.
- **Payload versioning:** Each event includes a `fetched_at` / `created_at` / `updated_at` timestamp for tracking; future versions may add fields without breaking current listeners.

---

## Testing Events in Sandbox

Use the test routes in `packages/Kun/Shipping/src/Routes/shipping-routes.php`:

```bash
# Test rates.fetched event (Aramex sandbox)
GET /api/kun/shipping/test-aramex

# Test shipment.created + rates.fetched (Jeebly sandbox)
 # GET /api/kun/shipping/test-jeebly

# Test status.updated (polling)
GET /api/kun/shipping/test-tracking-sync?shipment_id=42
```

Event listeners can be temporarily added to `routes/console.php` or a test listener to verify payloads:

```php
use Kun\Shipping\Events\RatesFetched;

// In routes/console.php or a Kernel listener:
Event::listen(RatesFetched::class, function ($event) {
    dump($event->toArray());
});
```

---

## Versioning & Backward Compatibility

- **Current version:** 1.0
- **Payload stability:** Fields marked as core (`carrier`, `tracking_number`, `status`, `context`) are stable.
- **Non-breaking additions:** Timestamps and metadata fields may be added to future versions without breaking existing listeners.
- **Removal:** Fields are never removed; deprecation follows semantic versioning.

---

## References

- **ADR-001:** Event-driven monolith (`docs/adr/001-event-driven-monolith.md`)
- **ADR-004:** Webkul upgrade policy (`docs/adr/004-upgrade-policy.md`)
- **ADR-005:** BNPL + Shipping integration (see `docs/adr/005-bnpl-shipping-integration.md`)
- **Program Reference:** §3.10.1 (Shipping integration)
- **Carriers:** `packages/Kun/Shipping/src/Carriers/` (Aramex)
- **Event Classes:** `packages/Kun/Shipping/src/Events/`
