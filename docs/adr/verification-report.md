# ADR Verification Report: Code vs. ADR Compliance

**Date:** 2026-03-15
**Scope:** All four ADRs (001–004) verified against current `packages/Kun/` codebase

---

## Current State of `packages/Kun/`

7 scaffolded packages exist, all at **early scaffold stage** (service provider + config + manifest only):

| Package | Service Provider | Config | Routes | Models | Controllers | Listeners | Contracts | Payment Driver |
|---------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| PaymentBNPL | YES | YES | — | — | — | — | — | — |
| Shipping | YES | YES | — | — | — | — | — | — |
| SmartLinks | YES | YES | YES | — | — | — | — | — |
| CodVerification | YES | YES | — | — | — | — | — | — |
| Search | YES | YES | YES | — | — | — | — | — |
| Analytics | YES | YES | — | — | — | — | — | — |

No package has any Models, Controllers, Listeners, Contracts, or Payment driver classes yet.

---

## ADR 001: Event-driven monolith

| Rule | Code Status | Finding |
|------|-------------|---------|
| Event naming `kun.{domain}.{entity}.{action}` | **N/A — no events emitted yet** | No `Event::dispatch()`, `Event::fire()`, or `event()` calls exist in any Kun package. No violations, but also nothing to verify. |
| No cross-package direct use of internal classes | **PASS** | Grep for `use Kun\` found only self-references (each package's own test importing its own ServiceProvider). Zero cross-package imports. |
| Contracts as public API | **GAP — no Contracts directories exist** | No `Contracts/` namespace in any Kun package. No interfaces or DTOs defined. |

### Gaps to address

1. No `kun.*` events are emitted yet — when they are, they must follow the naming convention.
2. No `Contracts/` directories — packages that will expose behaviour (PaymentBNPL, Shipping, SmartLinks) need to create these.
3. No event listeners registered — cross-package communication via events is not wired yet.

---

## ADR 002: Payment integration strategy

| Rule | Code Status | Finding |
|------|-------------|---------|
| PaymentBNPL extends `Webkul\Payment\Payment\Payment` | **GAP — no payment driver class exists** | `PaymentBNPLServiceProvider` is scaffolded but there is no class extending the Webkul Payment base. No `Payment/` directory in PaymentBNPL. |
| Redirect flow (`getRedirectUrl()`) | **GAP** | No driver means no redirect implementation. |
| Webhook signature verification | **GAP** | No webhook controller or handler exists. |
| Idempotency via provider transaction ID | **GAP** | No transaction ID storage or duplicate-check logic. |
| Registered in payment config | **PARTIAL** | Config file `payment-bnpl.php` exists but has no driver class registration yet. |

### Gaps to address

1. Create a driver class (e.g. `src/Payment/Tamara.php`) extending `Webkul\Payment\Payment\Payment`.
2. Implement `getCode()`, `getTitle()`, `getRedirectUrl()`.
3. Add webhook controller with signature verification.
4. Add idempotency check using provider transaction ID.
5. Register the driver in config.

---

## ADR 003: Smart Links architecture

| Rule | Code Status | Finding |
|------|-------------|---------|
| Short-code generation | **GAP — no model or service** | No DB migration, model, or code-generation logic. |
| Redirect flow with ref param | **GAP** | Routes exist (`shop-routes.php`, `admin-routes.php`) but are empty/minimal — no `/go/{code}` route or redirect controller. |
| Conversion tracking (session/cookie + order listener) | **GAP** | No middleware to capture ref, no listener for order events. |
| Express checkout scope | **GAP** | Not documented in code or config. |

### Gaps to address

1. Create short-code model and migration (e.g. `smart_links` table with code, target URL, metadata).
2. Add `/go/{code}` route with redirect controller that appends `ref` param.
3. Add middleware to capture `ref` into session/cookie on landing.
4. Add order listener (e.g. on `marketplace.sales.order.save.after`) to persist attribution.
5. Document express checkout scope decision in code or config.

---

## ADR 004: Webkul upgrade policy

| Rule | Code Status | Finding |
|------|-------------|---------|
| Zero modifications to `packages/Webkul/` | **PASS** | Grep for `Kun\\`, `kun_`, `kun.` inside `packages/Webkul/` returned zero matches. No KUN-specific code has been added to any Webkul package. |
| Customizations in `packages/Kun/` | **PASS** | All 7 Kun packages live under `packages/Kun/` with proper `Kun\*` namespaces. |
| Customizations in `resources/themes/kun/` | **GAP — directory does not exist** | No `resources/themes/kun/` directory yet. Theme package is referenced in ADR 001 context but not scaffolded. |
| Upgrade procedure | **N/A** | Documentation-only rule; no code to verify. |

### Gaps to address

1. Create `resources/themes/kun/` directory when UI customizations begin.

---

## Summary

| ADR | Violations | Gaps |
|-----|:----------:|:----:|
| 001 — Event-driven monolith | **0** | 3 |
| 002 — Payment integration | **0** | 5 |
| 003 — Smart Links | **0** | 5 |
| 004 — Upgrade policy | **0** | 1 |
| **Total** | **0** | **14** |

---

## Conclusion

**Zero violations found** — no ADR rule is being broken. All packages are at scaffold stage (ServiceProvider + config only), so there are **14 implementation gaps** that must be filled as development progresses. The ADRs are ready and consistent; the code has not caught up yet.

### Priority actions when implementation begins

1. **Contracts directories** — Create `Contracts/` namespace in PaymentBNPL, Shipping, and SmartLinks (ADR 001 §3).
2. **Payment driver** — Create Tamara/Tabby driver extending `Webkul\Payment\Payment\Payment` (ADR 002 §1).
3. **Smart Links model + routes** — Create short-code model, `/go/{code}` route, ref middleware, order listener (ADR 003 §1–§3).
4. **KUN theme** — Create `resources/themes/kun/` for UI customizations (ADR 004 §2).
5. **Events** — Emit `kun.{domain}.{entity}.{action}` events as features are built (ADR 001 §1).
