# KUN Developer Guide

Guide for everyone working on the `KUNARABIAN/kunarabian` fork of Bagisto. This covers branching, PR hygiene, what *never* to commit, and the rules the Kun theme imposes on top of upstream Bagisto.

If this guide conflicts with upstream `CONTRIBUTING.md`, **this guide wins** for the Kun fork.

---

## 1. Branches

### Trunk branches
- **`dev`** — default integration branch. All feature branches merge here.
- **`feature/refactor-enhance-frontend`** — long-lived refactor branch being prepared as a single merge to `dev`. Only refactor-scope PRs target this.
- **`main` / `master`** — release snapshots. Not touched directly.

### Feature branches

```
feature/KNRBN-<ticket>-<short-kebab-desc>
fix/KNRBN-<ticket>-<short-kebab-desc>
chore/KNRBN-<ticket>-<short-kebab-desc>
```

Examples: `feature/KNRBN-528-bnpl-payment-cards`, `fix/KNRBN-705-cms-page-404`.

**Why the ticket in the branch name matters:** it lets the Jira↔GitHub audit match PRs to tickets automatically. Branches without a KNRBN ref will be flagged in the weekly drift report.

### Golden rule: branch from `dev`, not from another feature branch

Do **not** branch `feature/foo` off `feature/bar`. Every PR we reviewed in April 2026 that did this ended up dragging 60–100 unrelated files into its diff because the upstream branch hadn't merged yet. The fix is brutal (full rebase) and sometimes not possible.

```bash
# Correct
git checkout dev && git pull
git checkout -b feature/KNRBN-123-my-feature

# Wrong — you will carry every file from Asmaa's branch into your PR
git checkout feature/shipping-http-client-policies
git checkout -b feature/KNRBN-123-my-feature
```

If you *must* build on unmerged work, say so explicitly in the PR body (`Depends on #XYZ`) and rebase the moment the dependency lands.

---

## 2. Commits

- One logical change per commit. If your commit message has "and" in it, it should probably be two commits.
- Format: `type(scope): imperative summary` — e.g., `feat(shipping): add Aramex rate-quote client`.
- Types: `feat`, `fix`, `refactor`, `chore`, `test`, `docs`, `perf`.
- Include the ticket ref in the first line or the body: `[KNRBN-528]`.
- **Never** commit compiled assets, generated docs, or AI-assistant artefacts (see §4).

---

## 3. Pull requests

### Before opening

1. Rebase onto the current target branch (`dev` or `feature/refactor-enhance-frontend`).
2. Run `./vendor/bin/pint` (PSR-2 clean).
3. Run `php artisan test` for the packages you touched.
4. If you touched theme CSS/JS: `cd packages/Kun/Theme && npm run build` and verify locally — but **do not commit the build output**.
5. Check `git diff --stat origin/dev...HEAD` — if you see files you don't recognise, you have branch drift.

### Opening the PR

- Use the PR template (`.github/pull_request_template.md`). It will auto-load when you open a PR.
- **Fill in the KNRBN ref.** No ref = auto-flagged by the drift audit.
- Mark the PR **draft** if it's work-in-progress. Drafts don't get reviewed unless you ask — that's the point.
- One PR = one ticket. If you're closing KNRBN-123 *and* KNRBN-456, that's two PRs unless the tickets are explicitly paired (e.g., implement+verify).

### Review flow

- **Draft** → you're working on it. No review expected.
- **Ready for review** → at least one approving review required before merge.
- **Changes requested** → address the comments, re-request review.
- **Approved** → merge yourself (author merges, reviewer does not).

### Merging

- Use **Squash and merge** by default. Keeps `dev` history linear.
- Use **Rebase and merge** only when the PR has meaningful commit-by-commit history that's worth preserving.
- Never **Create a merge commit** — it clutters the history.
- Delete the branch after merge (GitHub does this automatically if configured).

---

## 4. Things that must never be committed

### Generated / compiled

- `public/build/**` — Vite output
- `public/themes/*/build/**` — Kun Theme Vite output
- `public/images/**` — images go through the Kun theme Vite pipeline at `packages/Kun/Theme/src/Resources/assets/images/`, not the public root
- Any `.css` or `.js` file with `-[hash]` in the name

### Documentation dumped by tools

- `docs/jira-export-*.{json,md}` — Jira exports. Put them on Drive/Confluence.
- `docs/*-verification-report.md` — one-off audit reports
- `docs/kun-theme-developer-guide.md` — this file is the developer guide; we don't need a second one
- `TASK_COMPLETION_SUMMARY.md`, `IMPLEMENTATION_NOTES.md`, `FINAL_SUMMARY.md`, `*_RETURN_FLOW.md` — LLM/assistant dumps
- Any new `.md` file not explicitly requested by a ticket

### Secrets

- `.env` — never. Use `.env.example` with placeholder values.
- Any file containing an actual API key, password, or certificate
- Credentials hardcoded in `config/*.php` (use `env()` / `config()` indirection)

### Webkul vendor code

- **Anything under `packages/Webkul/`** — including translations in `packages/Webkul/*/lang/`. Upstream Bagisto code is off-limits. Customisations live in `packages/Kun/` and `resources/themes/kun/views/`.

---

## 5. Kun theme rules

Full background is in `CLAUDE.md` at the repo root. The short version:

- Kun theme views override Shop views by placing files at matching paths under `resources/themes/kun/views/`.
- `@bagistoVite` calls must use explicit namespaces (`'shop'` or `'shop-kun'`) — never bare.
- Kun does **not** load its own `app.js`; Shop's JS provides the Vue app.
- Images referenced via `bagisto_asset()` must exist in the Kun Vite manifest — copy them to `packages/Kun/Theme/src/Resources/assets/images/` and rebuild, don't dump them in `public/images/`.
- Design tokens live in `packages/Kun/Theme/src/Resources/assets/css/tokens.css`. Use `--kun-color-brand-*` custom properties, not raw hex.
- Homepage sections are DB-driven via `theme_customizations` — don't add hand-coded blade files for homepage carousels.

---

## 6. Code style

- **PSR-2 via Laravel Pint.** Run `./vendor/bin/pint` before pushing. CI enforces it.
- **PSR-4 autoloading.** Namespaces mirror directories exactly.
- **No default comments.** Don't add PHPDoc blocks that just restate the type (`/** @var string */` above `protected string $name;`). Only write a comment when the *why* is non-obvious.
- **No TODO markers in merged code.** Either fix it in this PR or file a ticket.

---

## 7. Payments, webhooks, external APIs

If your PR touches PayTabs, Tabby, Tamara, Jeebly, Aramex, or any external HTTP endpoint:

1. **IPN / webhook signature verification is mandatory.** Verify the HMAC *before* any DB write. Log and reject invalid signatures.
2. **Callback routes are CSRF-exempt but signature-gated.** Don't trust `order_id` from the request body without it being covered by the signature.
3. **Credentials come from `config()`, not `env()`.** `env()` outside config files breaks `config:cache`.
4. **HTTP clients must redact secrets before logging.** Auth headers and body fields like `Password`, `AccountPin`, `Authorization` must never reach `storage/logs/laravel.log`.
5. **Retries on 5xx and 429 only.** Don't retry 4xx — it's wasted calls.
6. **Timeouts from config**, not hardcoded.

---

## 8. Database

- **Migrations** live under `packages/Kun/<Package>/src/Database/Migrations/` and run in timestamp order.
- **Additive only** on tables that might have live data: new columns nullable or with defaults. No destructive drops.
- **Seeders** must be idempotent. Use `updateOrInsert` / `firstOrCreate` keyed on a unique column, never plain `insert`. Re-running the seeder should not duplicate rows.
- **Test seeders** belong under `packages/Kun/<Package>/tests/Fixtures/`, not `database/seeders/`, so they don't get run in production by accident.

---

## 9. Jira status workflow

| Status | Who moves it | When |
|---|---|---|
| **To Do** | — | Default |
| **In Progress** | Developer | First commit pushed |
| **Tech Review** | Developer | PR is non-draft and CI green |
| **QA** | Reviewer | After code review approved |
| **Done** | QA / owner | After PR merged + QA sign-off |

The Jira audit runs weekly and flags:
- Tickets in `Tech Review` with no open PR → candidate for `Done`
- Tickets in `Done` with an open PR → close the PR or re-open the ticket
- Tickets in `To Do` with an open PR → move to `In Progress` or `Tech Review`

---

## 10. When things go wrong

### "My PR has 80 files and I only changed 3"
Stacked-branch drag-in. Rebase onto current `dev`:
```bash
git fetch origin
git rebase origin/dev
# resolve conflicts
git push --force-with-lease
```
If the conflicts are unreasonable, cherry-pick your 3 on-topic commits onto a fresh branch from `dev`.

### "CI fails on Pint"
Run `./vendor/bin/pint` locally. It auto-fixes PSR-2 violations. Commit the fixes.

### "A seeder is creating duplicate rows"
Change `->insert(...)` to `->updateOrInsert(['slug' => $slug, 'channel_id' => $channel], $row)`.

### "My theme CSS changes aren't showing up"
1. `cd packages/Kun/Theme && npm run build`
2. `php artisan view:clear`
3. Hard-refresh the browser (Cmd/Ctrl+Shift+R)
4. Check `public/themes/shop/kun/build/manifest.json` — but **don't commit it**.

### "I accidentally committed a secret"
1. **Rotate the secret immediately** — assume it's leaked.
2. `git rebase -i` to drop the commit (if not pushed yet).
3. If already pushed, talk to Ayman — we'll need to force-rewrite history and it affects everyone.

---

## 11. Questions

- Code / architecture → ask Ayman
- Jira / tickets / process → ask the project lead
- PayTabs / shipping integrations → ask Asmaa
- UI / theme / Figma → ask Rana or Martina

Don't sit on a blocker for more than half a day — ask.
