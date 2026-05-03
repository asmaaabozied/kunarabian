# ADR 003: Smart Links architecture

**Status:** Accepted
**Date:** 2026

## Context

KUN needs **social commerce** support: shareable links (e.g. from sellers or influencers) that let us attribute visits and conversions to a specific link, campaign, or seller. The Smart Links package (`packages/Kun/SmartLinks/`) will provide short URLs, redirect to the shop, and record when a user who came from a link completes an order. We need a single architecture so that short-code generation, redirect behaviour, attribution, and express-checkout scope are clear.

## Decision

### 1. Short-code generation

- The system **generates and stores short codes** (e.g. alphanumeric or base62) that map to a target URL and optional metadata (seller, campaign, product).
- Codes are **collision-safe** (e.g. random with retry, or deterministic with sufficient entropy) and of configurable length. They are stored in a persistent store (e.g. DB table or cache with persistence).
- The **public entry point** is a short URL (e.g. `/go/{code}` or a dedicated domain alias). Resolving the code yields the target (e.g. product page, shop homepage) and metadata used for attribution.
- Admin or seller tools create links; the same code always resolves to the same target and metadata until explicitly updated or retired.

### 2. Redirect flow with ref param

- When a user hits the short link, the flow is **redirect-based**: resolve the short code to the **target URL**, then **redirect** the user (HTTP 302/303) to that URL.
- The redirect **MUST** attach a **ref** (or equivalent) query parameter so the landing page and downstream logic know the visit came from that link. Parameter name is fixed (e.g. `ref`, `sl_ref`, or `utm_ref`); document it in config or this ADR.
- The **ref** value identifies the short code, campaign, or seller (e.g. encoded code or stable ID). It is **preserved** across the redirect and must be available to the front-end and server for the duration of the session or attribution window.
- Do not serve the target page in an iframe; the user must land on the real URL with the ref param set.

### 3. Conversion tracking via session/cookie + order listener

Attribution has two parts:

- **Session/cookie:** When the user lands with the ref param, store the ref (or a derived attribution ID) in **session** and/or a **first-party cookie** so we can associate later actions (browse, add-to-cart, checkout) with that link. TTL and scope (session vs cookie) are implementation choices; document them.
- **Order listener:** When an **order is created** (e.g. listener for `marketplace.sales.order.save.after` or the appropriate Bagisto/KUN order event), read the stored ref from session/cookie, **persist attribution** on the order (e.g. `smart_link_ref` column or link to a tracking row), then clear or rotate as needed so the same ref is not reused inappropriately.
- **Conversion** is defined as: an order placed while a ref (from a smart link) was set. The order record must allow reporting on "orders from smart links" and, if needed, by campaign/seller.

### 4. Express checkout scope

- Define explicitly whether ref attribution applies to **standard checkout only**, **express checkout only**, or **both**.
- If **express checkout** is in scope: ensure the ref (or attribution ID) is available in the express-checkout path (e.g. session or token) and that the order listener runs for express-checkout orders so attribution is stored.
- If **out of scope** for express checkout: document that smart-link attribution is not recorded for express-checkout orders; only standard checkout is attributed. Revisit when product requirements change.
- Document the chosen scope in this ADR or in the SmartLinks package README so implementers and product know the behaviour.

## Consequences

### Positive

- Clear contract for the SmartLinks package; consistent ref handling across the application.
- Attribution data available for reporting on "orders from smart links" by campaign/seller.
- Scope is explicit — no ambiguity about express checkout behaviour.

### Negative

- Cookie/session and privacy implications must be documented and compliant with policy.
- Duplicate or stale refs need clear rules (e.g. last-touch, or first-touch within window).
- Order listener must handle edge cases (guest checkout, session expiry).

## References

- ADR 001: Event naming (`kun.smartlinks.*` if we emit events) and Contracts as public API.
- ADR 004: Customizations in `packages/Kun/` and `resources/themes/kun/`; no edits under `packages/Webkul/`.
- Webkul order events: `marketplace.sales.order.save.after` (available in `packages/Webkul/Marketplace`).
