# Contributing

Thanks for considering a contribution to this snippet library! It's a small, curated collection, so the bar is "does this fit the existing structure and quality" more than "does this add a new process."

## Ways to contribute

- **Bug reports** — something in a snippet doesn't behave as documented, or breaks on a supported WooCommerce/WordPress version.
- **Improvements** — a cleaner or more compatible way to implement an existing snippet, without changing what it does.
- **New snippets** — a small, focused, reusable WooCommerce/WordPress snippet that isn't already covered.

Before opening a pull request for anything non-trivial (a new snippet, a behavior change), please open an issue first to discuss it.

## Ground rules for snippets

Every snippet in this repository must be:

- **Self-contained** — one folder per snippet, and it should work if that folder is the only one installed. If it shares helper functions with another snippet, guard the declarations with `function_exists()`.
- **Configurable, not hardcoded** — store-specific values (text, addresses, attribute slugs, SKU rules, etc.) belong in `define()` constants and/or `apply_filters()` hooks near the top of the file, not buried inside hook callbacks.
- **Namespaced** — functions, filters and constants use the `wcsnip_` / `WCSNIP_` prefix to avoid collisions with themes/plugins. Pick a name that doesn't already exist in the repo.
- **Defensive** — guard against missing WooCommerce objects/data (`null` products, unmanaged stock, missing attributes, etc.) rather than assuming happy-path input.
- **Documented** — a docblock at the top of the file explaining what it does, requirements, and configuration; plus a per-snippet `README.md` (see any existing snippet for the expected sections: description, requirements, installation, configuration table, notes).

## Adding a new snippet

1. Create a new folder named after the snippet (e.g. `My New Snippet/`).
2. Add `my-new-snippet.php` — the plain PHP version (`<?php` + `defined( 'ABSPATH' ) || exit;` guard at the top).
3. Add `my-new-snippet.code-snippets.json` — the same code in [Code Snippets](https://wordpress.org/plugins/code-snippets/) plugin export format (same `code` field, without the opening `<?php` tag or the `ABSPATH` guard).
4. Add `README.md` describing the snippet, following the structure used by the existing snippets.
5. Add a row for it in the table in the root [`README.md`](README.md).
6. Add an entry under `[Unreleased]` in [`CHANGELOG.md`](CHANGELOG.md).

## Code style

- Match [WordPress PHP coding standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) (tab indentation, spaces inside parentheses, Yoda conditions where the original code uses them) — see [`.editorconfig`](.editorconfig).
- Comments and documentation in this repository are written in English.
- Before submitting, at minimum run `php -l` on any changed `.php` file and make sure changed `.code-snippets.json` files are valid JSON. CI will also check both.

## Pull requests

- Keep PRs focused — one snippet or one fix per PR.
- Describe what changed and why, and reference the related issue if there is one.
- Update the relevant `README.md`(s) and `CHANGELOG.md` alongside the code change.

## Reporting security issues

Please don't open a public issue for a security vulnerability — see [`SECURITY.md`](SECURITY.md) instead.

## Code of conduct

This project follows the [Contributor Covenant](CODE_OF_CONDUCT.md). By participating, you're expected to uphold it.
