<div align="center">

# WooCommerce Snippets

**A curated library of small, focused WordPress & WooCommerce snippets** — hooks, filters and functions, each self-contained, configurable, and ready to drop into any site.

[![Release](https://img.shields.io/github/v/release/Braska-botmaker/woocommerce-snippets?label=release&color=2f81f7)](https://github.com/Braska-botmaker/woocommerce-snippets/releases/latest)
[![CI](https://img.shields.io/github/actions/workflow/status/Braska-botmaker/woocommerce-snippets/lint.yml?branch=main&label=CI)](https://github.com/Braska-botmaker/woocommerce-snippets/actions/workflows/lint.yml)
[![License](https://img.shields.io/badge/License-MIT-2f81f7)](LICENSE)
[![Issues](https://img.shields.io/github/issues/Braska-botmaker/woocommerce-snippets?label=issues&color=3fb950)](https://github.com/Braska-botmaker/woocommerce-snippets/issues)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](README.md#requirements)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)](README.md#requirements)

</div>

---

Every snippet here started life on a real, live WordPress/WooCommerce store, then got generalized: store-specific values (addresses, attribute slugs, SKU rules, text) were pulled out into constants and filters so the same file works on any site, not just the one it was written for.

## Table of Contents

- [Snippets](#snippets)
- [What's in each folder](#whats-in-each-folder)
- [Installation](#installation)
- [Conventions](#conventions-used-across-snippets)
- [Requirements](#requirements)
- [Contributing](#contributing)
- [Security](#security)
- [Changelog](#changelog)
- [License](#license)
- [Author](#author)

## Snippets

| Snippet | Description |
| --- | --- |
| [Unit price to package price](Unit%20price%20to%20package%20price/) | Displays and charges prices per package instead of per unit, based on a "units per package" product attribute. |
| [Stock quantity per package display](Stock%20quantity%20per%20package%20display/) | Shows stock as a number of packages on the frontend while keeping stock stored in units in the database. |
| [Custom pickup point checkout note](Custom%20pickup%20point%20checkout%20note/) | Adds a short note under the selected Local Pickup shipping option at checkout, and full pickup details in order emails. |

Not every future snippet in this repo will necessarily touch WooCommerce — some may be plain WordPress. Each one lists its own requirements in its folder's `README.md`.

## What's in each folder

Every snippet is one self-contained folder:

```text
Snippet Name/
├── snippet-name.php                  # plain PHP version
├── snippet-name.code-snippets.json   # Code Snippets plugin export
└── README.md                         # what it does, requirements, configuration
```

- **`*.php`** — ready to paste into `functions.php` or a site-specific plugin.
- **`*.code-snippets.json`** — the same snippet in [Code Snippets](https://wordpress.org/plugins/code-snippets/) plugin export format, for one-click import via **Snippets → Import** in wp-admin.
- **`README.md`** — description, requirements, installation, and every configuration option (constants/filters) it exposes.

## Installation

Pick whichever fits your workflow, per snippet:

1. **Plain PHP** — copy the contents of the `.php` file into your child theme's `functions.php`, or into a small site-specific plugin. Most portable option, no extra plugin needed.
2. **Code Snippets plugin** — install [Code Snippets](https://wordpress.org/plugins/code-snippets/), then go to **Snippets → Import** and upload the `.code-snippets.json` file. Review the imported snippet and activate it.

Then open the snippet's own README for its configuration — store-specific values (attribute slugs, text, addresses, etc.) are exposed as constants and filters at the top of the file, so you shouldn't need to touch the hook logic itself.

## Conventions used across snippets

- **Prefix** — functions, filters and constants use the `wcsnip_` / `WCSNIP_` prefix to avoid collisions with your theme or other plugins.
- **Configuration over editing** — store-specific values are exposed as `define()` constants and/or `apply_filters()` hooks near the top of each file, so you override them rather than editing the snippet's logic.
- **Safe to combine** — snippets that share helper functions (e.g. the two "package" snippets) guard their declarations with `function_exists()`, so each one also works completely standalone.
- **No hard dependencies between snippets** — every snippet can be installed on its own.
- **No fake content on activation** — a snippet that needs store-specific text (an address, a note, etc.) ships with those fields empty and silently does nothing until you fill them in, instead of printing example/placeholder content on a live site.

## Requirements

- WordPress
- PHP 7.4+
- Some snippets additionally require [WooCommerce](https://woocommerce.com/) — check the individual snippet's `README.md`.

## Contributing

Bug reports, improvements and new snippets are welcome — see [`CONTRIBUTING.md`](CONTRIBUTING.md) for the ground rules snippets in this repo follow, and [`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md) for community expectations.

## Security

Please don't file public issues for security concerns — see [`SECURITY.md`](SECURITY.md) for how to report them privately.

## Changelog

See [`CHANGELOG.md`](CHANGELOG.md) for release history.

## License

Released under the [MIT License](LICENSE) — use, modify, and reuse freely, including in commercial projects.

## Author

<div align="center">

Maintained by **Matěj Horák** ([Horabrand](https://www.horakbrand.cz)) · [@Braska-botmaker](https://github.com/Braska-botmaker)

</div>
