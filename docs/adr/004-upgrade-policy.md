# ADR 004: Webkul upgrade policy

**Status:** Accepted
**Date:** 2026

## Context

The project is built on Bagisto (Laravel + Webkul packages under `packages/Webkul/`). To upgrade Webkul/Bagisto safely and avoid merge conflicts or lost customisations, we need a strict rule for where custom code lives and a documented procedure for performing upgrades.

## Decision

### 1. Zero modifications to packages/Webkul/

- **Do not edit, patch, or add files under `packages/Webkul/`.** All Webkul packages (e.g. Marketplace, Payment, Paypal, Checkout, Core) are treated as **vendor**: we consume their public API, events, and extension points only.
- **Rationale:** Unmodified core can be upgraded via Composer without overwriting our changes. Any change inside Webkul would be lost on upgrade or cause merge conflicts. If we need different behaviour, we extend or override in our own packages or theme (see below).
- **If custom code was ever placed in `packages/Webkul/`:** Refactor it out to `packages/Kun/` or `resources/themes/kun/` before running an upgrade.

### 2. Customizations in packages/Kun/ and resources/themes/kun/

All project-specific code MUST live in one of these two places:

- **`packages/Kun/`** — KUN packages (e.g. PaymentBNPL, Shipping, SmartLinks, CodVerification, Search, Analytics, Theme). Namespaced under `Kun\*`. These packages register services, listeners, routes, and config that extend or hook into Webkul/Bagisto. They do not modify Webkul files; they add or override via Laravel/Bagisto extension mechanisms (service providers, events, view precedence).
- **`resources/themes/kun/`** — KUN theme: views, assets, and components that extend the default theme. No KUN-specific edits in `resources/themes/default/`. Overriding Webkul views is done via theme/view precedence (e.g. same path under the active theme), not by changing files inside Webkul.

**Optional:** Config overrides in `config/` at application root and routes in `routes/` are acceptable when necessary, as long as they do not require changing files under `packages/Webkul/`.

### 3. Upgrade procedure

When upgrading Webkul/Bagisto (core or Webkul packages), follow this procedure:

**Before**

- **Backup** database and code (e.g. tag or branch).
- **Read** release notes / changelog for the version(s) you are upgrading to. Check for breaking changes and required migrations.
- **Confirm** no custom code remains under `packages/Webkul/` (see §1). If any is found, move it to `packages/Kun/` or theme first.

**Upgrade**

- Run **Composer update** for the relevant packages (e.g. `composer update webkul/*` or project-wide as appropriate). Resolve version constraints if needed.
- Run **migrations** if the upgrade introduces new ones: `php artisan migrate`.
- Clear caches if required: `php artisan config:clear`, `php artisan cache:clear`, etc., per Bagisto/Webkul upgrade notes.

**After**

- **Run tests** (if available) and **smoke-check** critical flows: checkout, payment, marketplace seller/customer flows, admin.
- **Check for breaking changes** in the release notes (e.g. renamed methods, changed events). Update `packages/Kun/` or theme code if anything we depend on has changed.
- **Document** the upgrade (e.g. "Upgraded to Bagisto X.Y / Webkul package Z") in your internal changelog or runbook.

**If something breaks**

- Do not patch `packages/Webkul/`. Fix the issue in `packages/Kun/` or theme (e.g. update our listener, our view override, or our config) or report upstream if it is a Webkul bug.

## Consequences

### Positive

- Safe, repeatable upgrades; no merge conflicts with vendor code.
- Clear boundaries for developers — anyone knows immediately where custom code belongs.
- Webkul bugs can be reported upstream without our patches complicating the picture.

### Negative

- We must use extension points only; some desired behaviour may require upstream changes or more elaborate wiring in Kun packages.
- Developers must resist "quick fixes" directly in Webkul code.

## References

- ADR 001: Event-driven monolith (Kun packages consume Webkul events).
- ADR 002: Payment integration (extend Webkul Payment base, do not modify it).
- ADR 003: Smart Links architecture (listeners on Webkul order events, no Webkul edits).
- Bagisto/Webkul upgrade and release documentation (check their docs for version-specific steps).
