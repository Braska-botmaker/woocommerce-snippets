# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-09-23

### Added

- **Custom order email notice** — adds a short custom message to WooCommerce customer order emails, targeted by email ID (the `customer_processing_order` "Processing order" email by default). Understated styling (optional heading + text, thin rule, no coloured box) so it suits any message, not just warnings. Multilingual two ways, no extra snippet: `WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION` registers the message/heading for Polylang or WPML string translation, or the message constant can be a `locale => text` array. Message, heading, target emails and placement are configurable via constants/filters, and the snippet adds nothing to any email until the message is filled in.

## [1.0.0] - 2026-08-18

### Added

- Initial public release of the snippet library.
- **Unit price to package price** — displays and charges WooCommerce prices per package instead of per unit, based on a configurable "units per package" product attribute, with a configurable SKU-based exclusion rule.
- **Stock quantity per package display** — shows stock as a number of packages on the frontend while stock stays stored in units, and converts ordered package quantities back to units on stock reduction.
- **Custom pickup point checkout note** — short note under the selected Local Pickup shipping option at checkout, plus full pickup details in customer order emails.
- Per-snippet `.php` and `.code-snippets.json` (Code Snippets plugin export) versions.
- Per-snippet `README.md` with requirements, installation, and configuration reference, including step-by-step instructions for setting up the "units per package" global product attribute used by the two package-pricing snippets.
- Repository-level `README.md`, `LICENSE` (MIT), `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`, and `SECURITY.md`.
- `.github/` scaffolding: issue templates (bug report, feature request), pull request template, and a CI workflow that runs `php -l` on every `.php` file and validates every `.code-snippets.json` file.
- `.editorconfig` and `.gitignore` for consistent formatting and a clean working tree.

### Changed

- All snippets were generalized from their original store-specific implementations: hardcoded values (addresses, attribute slugs, SKU rules, text) were replaced with `define()` constants and `apply_filters()` hooks so each snippet can be configured without editing its logic.
- **Custom pickup point checkout note** now ships with all text fields (`WCSNIP_PICKUP_NOTE_SHORT`, `WCSNIP_PICKUP_ADDRESS`, `WCSNIP_PICKUP_PHONE`, `WCSNIP_PICKUP_HOURS`) empty by default instead of placeholder example text. Nothing is printed at checkout or in order emails until at least one field is filled in, and any field left empty is silently skipped rather than shown as a blank line.

[Unreleased]: https://github.com/Braska-botmaker/woocommerce-snippets/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/Braska-botmaker/woocommerce-snippets/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/Braska-botmaker/woocommerce-snippets/releases/tag/v1.0.0
