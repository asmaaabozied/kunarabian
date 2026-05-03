<!--
  KUN PR template. Fill in every section before marking ready for review.
  If a section doesn't apply, write "N/A — <one-line reason>".
  Draft PRs are fine and encouraged for work-in-progress.
-->

## Ticket
<!-- Link the Jira ticket. Use one of: Closes, Fixes, Refs. -->
Closes KNRBN-XXX

## Summary
<!-- 2–4 sentences. What does this change and why? -->


## What changed
<!-- Bullet list of the concrete changes. Group by area if the PR is large. -->
- 

## Testing
<!-- How did you verify this works? Manual steps + automated tests. -->
- [ ] Manual: 
- [ ] Tests added/updated under `packages/Kun/<package>/tests/`
- [ ] `./vendor/bin/pint` clean
- [ ] `php artisan test` green locally

## Screenshots / video
<!-- UI changes only. Delete the section if not applicable. -->


## Scope checklist
<!-- All boxes must be checked before "Ready for review". -->
- [ ] Diff only touches files related to the linked ticket — no stacked-branch drag-in
- [ ] No edits under `packages/Webkul/` (vendor code is off-limits)
- [ ] No edits under `packages/Webkul/*/lang/` (including translations)
- [ ] No new `.md` files (unless the ticket explicitly requires one)
- [ ] No generated artefacts: `public/build/**`, `public/themes/**/build/**`, `docs/*-export-*.{json,md}`, `TASK_COMPLETION_SUMMARY.md`, `BNPL_RETURN_FLOW.md`
- [ ] No compiled CSS/JS snapshots (Vite regenerates these at build time)
- [ ] No secrets in `.env.example` — placeholder keys only
- [ ] Branch is rebased onto the current target branch (`dev` or `feature/refactor-enhance-frontend`)

## Payment / security checklist
<!-- Only for PRs touching payments, auth, or external webhooks. Delete if N/A. -->
- [ ] IPN / webhook handlers verify signature (HMAC) **before** mutating any DB state
- [ ] Callback routes are CSRF-exempt but signature-validated
- [ ] Unsigned / invalid-signature requests are logged and rejected
- [ ] Credentials read from `config('...')`, not `env()` directly (cached-config safe)
- [ ] No secrets committed; all new env keys added to `.env.example` with placeholder values

## Database changes
<!-- Migrations, seeders, schema changes. Delete if none. -->
- [ ] Migrations are additive (no destructive column drops on live tables)
- [ ] New columns are nullable or have a default
- [ ] Seeders are idempotent (`updateOrInsert` / `firstOrCreate`, not plain `insert`)
- [ ] Rollback path tested locally (`php artisan migrate:rollback`)

## Dependencies
<!-- If you changed composer.json, package.json, or config/concord.php. Delete if none. -->
- Added:
- Removed:
- Reason:

## Merge order / dependencies
<!-- If this PR depends on another PR landing first, say so. -->
- Depends on: #
- Blocks: #
