# Stock Quantity per Package Display

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](README.md#requirements)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)](README.md#requirements)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588A?logo=woocommerce&logoColor=white)](README.md#requirements)

Displays WooCommerce stock quantity as a number of **packages** on the frontend, while stock in the database stays in units — and converts ordered quantities back to units when an order reduces stock.

## What it does

1. **Frontend stock text**: the first number in WooCommerce's stock HTML (e.g. "54 in stock") is swapped for `floor(units_in_stock / units_per_package)`. The original wording and translations are kept — only the number changes. Backorder messaging is left untouched.
2. **Stock reduction**: when an order is placed, the ordered quantity (entered in packages) is multiplied back into units before WooCommerce reduces stock, so the database quantity stays accurate in units.
3. **Exclusions**: any product whose SKU ends with a configurable suffix (default `Q`) is shown/handled in plain units.

## Requirements

- WooCommerce
- A global product attribute that stores the number of units per package (default expected taxonomy: `pa_units-per-package`) — see [Setting up the "units per package" attribute](#setting-up-the-units-per-package-attribute) below. Same convention as the companion [Unit price to package price](../Unit%20price%20to%20package%20price/) snippet.

Install this snippet on its own if you only need the stock display, or together with "Unit price to package price" if you also sell by package. Both snippets guard their shared helper functions with `function_exists()`, so using them together is safe.

## Setting up the "units per package" attribute

This snippet reads the package size from a **global WooCommerce product attribute** (the kind managed under Products → Attributes, as opposed to a per-product "custom attribute"). If you haven't used one before:

1. In wp-admin, go to **Products → Attributes** and click **Add new attribute**.
2. Set the **Name** to something like `Units per package`. WooCommerce turns this into a slug automatically (`units-per-package`) and, because it's a *global* attribute, stores it internally as the taxonomy `pa_units-per-package` — which is exactly the default this snippet expects. If you name it something else, either rename the slug to `units-per-package`, or point the snippet at your slug (see Configuration below).
3. Click **Configure terms** next to your new attribute and add the values you need, e.g. `6`, `12`, `24` — one term per package size you sell.
4. Open a product for editing, go to the **Attributes** tab, add your `Units per package` attribute, and select the term (e.g. `6`) that applies to it. Click **Save attributes**.
5. For variable products, you can instead set a value **per variation** in the Variations tab — the snippet reads whatever value WooCommerce resolves for that specific product/variation via `get_attribute()`.
6. Products that have no value set for this attribute (or a value of `0`/`1`) are treated as **not sold by package** — their stock display is left untouched (shown in plain units).

If your store already uses a differently-named attribute for this, you don't need to rename anything: point the snippet at it instead, either by changing the `WCSNIP_PACKAGE_ATTRIBUTE` constant to your taxonomy slug, or with the `wcsnip_package_attribute` filter (see Configuration below).

## Installation

**Option A — plain PHP**
Copy [`stock-quantity-per-package-display.php`](stock-quantity-per-package-display.php) into your child theme's `functions.php`, or into a small site-specific plugin.

**Option B — Code Snippets plugin**
In WordPress admin, go to **Snippets → Import** and upload [`stock-quantity-per-package-display.code-snippets.json`](stock-quantity-per-package-display.code-snippets.json).

## Configuration

Edit the constants at the top of the file, or hook into the filters from your theme/plugin instead:

| Constant | Default | Purpose |
|---|---|---|
| `WCSNIP_PACKAGE_ATTRIBUTE` | `pa_units-per-package` | Attribute (taxonomy) slug that stores units per package. |
| `WCSNIP_PACKAGE_EXCLUDE_SKU_SUFFIX` | `Q` | SKU suffix that excludes a product from package handling. Set to `''` to disable this rule. |

| Filter | Arguments | Purpose |
|---|---|---|
| `wcsnip_package_attribute` | `$attribute, $product` | Change which attribute is read per product. |
| `wcsnip_package_excluded_product` | `$excluded, $product` | Fully replace the exclusion logic. |
| `wcsnip_package_units` | `$units, $product` | Override the final units-per-package value. |

## Notes

- Only affects products with **Manage stock** enabled at the product/variation level.
- The stock text replacement uses a "first number in the string" pattern match, so it works with WooCommerce's default stock strings out of the box; a heavily customized stock HTML string may need adjusting.
