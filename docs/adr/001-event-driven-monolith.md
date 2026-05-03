# ADR 001: Event-driven monolith

**Status:** Accepted
**Date:** 2026

## Context

KUN is built as a monolith (single Laravel/Bagisto application) with multiple custom packages under `packages/Kun/*` (PaymentBNPL, Shipping, SmartLinks, CodVerification, Search, Analytics, Theme). We need a clear rule for how these packages communicate so that boundaries stay intact, upgrades remain safe, and new developers know what is allowed. An event-driven approach keeps packages decoupled while still allowing them to react to each other's actions.

## Decision

### 1. Event naming: `kun.{domain}.{entity}.{action}`

All domain events emitted by KUN packages MUST follow this pattern:

- **`kun`** — namespace prefix for KUN (avoids clashes with Webkul/Bagisto events).
- **`{domain}`** — package or domain (e.g. `payment`, `shipping`, `analytics`, `smartlinks`).
- **`{entity}`** — main entity (e.g. `order`, `shipment`, `cart`).
- **`{action}`** — what happened (e.g. `captured`, `created`, `placed`, `failed`).

**Examples:**

- `kun.payment.order.captured`
- `kun.payment.order.failed`
- `kun.shipping.shipment.created`
- `kun.shipping.rates.fetched`
- `kun.analytics.order.placed`

Packages that need to react to something in another package subscribe to these events. No package may invoke another package's internal services or models directly for cross-package behaviour; use events (and Contracts when data must be passed).

### 2. No cross-package direct use of internal classes

Packages under `packages/Kun/*` MUST NOT depend on another Kun package's internal implementation:

- No `use Kun\Shipping\Models\...` from PaymentBNPL.
- No `use Kun\PaymentBNPL\Services\...` from Shipping.
- No direct instantiation or method calls on another package's Repositories, Controllers, or concrete Services across package boundaries.

Rationale: internal classes can change or be removed; coupling to them blocks independent deployment and upgrade. Cross-package integration is only via **Contracts** (see below) and **events**.

### 3. Contracts as the public API

The only allowed cross-package surface is **Contracts** (interfaces and, where needed, shared DTOs):

- Each package that exposes behaviour to others defines **interfaces** in a `Contracts` namespace (e.g. `Kun\Payment\Contracts\PaymentProvider`, `Kun\Shipping\Contracts\CarrierInterface`).
- Other packages may depend only on these Contracts (and on event payloads that are typed or documented). Implementations stay inside the owning package.
- Events may carry payloads; prefer simple objects or documented arrays so listeners don't depend on internal models. When in doubt, use a Contract or a dedicated DTO.

Summary: **Contracts (and events) are the public API between Kun packages; internal classes are not.**

## Consequences

### Positive

- Clear boundaries; packages can be tested in isolation.
- Webkul upgrades are safer (ADR 004); no coupling to upstream internals from Kun packages.
- Event naming is consistent and discoverable via the `kun.*` prefix.
- New developers can quickly understand inter-package communication by scanning event listeners.

### Negative

- More upfront design (Contracts, event schema) before a feature can span packages.
- Developers must resist "quick" cross-package `use` of internals.
- Event payloads must be documented and versioned to avoid silent breakage.

## References

- ADR 004: Webkul upgrade policy (no edits under `packages/Webkul/`).
- ADR 002: Payment integration strategy (extend Webkul Payment base).
- ADR 005: BNPL + Shipping integration (event contracts between payment and shipping).
- Existing Webkul event patterns: `marketplace.seller.create.after`, `catalog.product.update.after`.
