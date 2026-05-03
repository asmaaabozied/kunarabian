## Summary

<!-- Brief description of what this PR does and why -->

## Related Issue / Ticket

<!-- Link to issue, ticket, or ADR. Use "Closes #123" to auto-close -->

## Type of Change

- [ ] Bug fix (non-breaking change that fixes an issue)
- [ ] New feature (non-breaking change that adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to change)
- [ ] Refactor / code improvement
- [ ] Configuration / infrastructure
- [ ] Documentation

## Target Branch

<!--
  Bug fixes         → latest development branch (dev)
  Minor features    → latest stable branch
  Major features    → master branch
  See CONTRIBUTING.md for details
-->

## Packages Affected

- [ ] `packages/Kun/` — specify: <!-- e.g. SEO, Analytics, SmartLinks -->
- [ ] `resources/themes/kun/`
- [ ] Root config (`composer.json`, `phpunit.xml`, `bootstrap/providers.php`)
- [ ] Other — specify:

## Changes

<!-- List the key changes made -->
-

## How to Test

<!-- Steps for reviewers to verify the change -->
1.
2.
3.

## Checklist

### Code Quality
- [ ] Code follows PSR-2 style (`./vendor/bin/pint` passes)
- [ ] PHPDoc added for new public methods (`@param`, `@return`, `@throws`)
- [ ] No compiled assets committed (js/css) — maintainers generate these

### Architecture (ADR Compliance)
- [ ] No edits under `packages/Webkul/` (ADR-004)
- [ ] Cross-package communication uses events & Contracts only (ADR-001)
- [ ] New package registered in `bootstrap/providers.php` (if applicable)
- [ ] Namespace added to `composer.json` autoload (if applicable)

### Testing
- [ ] Unit tests added / updated
- [ ] Test suite added to `phpunit.xml` (if new package)
- [ ] All existing tests still pass (`php artisan test`)
- [ ] Manual verification done

### Database
- [ ] Migrations included (if applicable)
- [ ] Migrations are reversible (`down()` method implemented)

## Screenshots / Evidence

<!-- If UI change, attach before/after screenshots -->
