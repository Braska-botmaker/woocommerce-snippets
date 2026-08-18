# Unit Price to Package Price

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](README.md#requirements)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)](README.md#requirements)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588A?logo=woocommerce&logoColor=white)](README.md#requirements)

Sells products by the package while keeping unit prices in the database. Displays the package price on the shop/product page and recalculates the real cart & checkout line price to match, so totals, taxes and order records stay correct.

## What it does

1. **Displayed price** (shop & single product page) = unit price × units per package.
2. **Cart/checkout price** is recalculated the same way, based on a base price stored on the cart item — stable even if totals are recalculated multiple times (coupons, shipping changes, etc.).
3. **Exclusions**: any product whose SKU ends with a configurable suffix (default `Q`) is left untouched and sold per unit, e.g. for products you already price per package.

## Requirements

- WooCommerce
- A global product attribute that stores the number of units per package (default expected taxonomy: `pa_units-per-package`) — see [Setting up the "units per package" attribute](#setting-up-the-units-per-package-attribute) below.

## Setting up the "units per package" attribute

This snippet reads the package size from a **global WooCommerce product attribute** (the kind managed under Products → Attributes, as opposed to a per-product "custom attribute"). If you haven't used one before:

1. In wp-admin, go to **Products → Attributes** and click **Add new attribute**.
2. Set the **Name** to something like `Units per package`. WooCommerce turns this into a slug automatically (`units-per-package`) and, because it's a *global* attribute, stores it internally as the taxonomy `pa_units-per-package` — which is exactly the default this snippet expects. If you name it something else, either rename the slug to `units-per-package`, or point the snippet at your slug (see Configuration below).
3. Click **Configure terms** next to your new attribute and add the values you need, e.g. `6`, `12`, `24` — one term per package size you sell.
4. Open a product for editing, go to the **Attributes** tab, add your `Units per package` attribute, and select the term (e.g. `6`) that applies to it. Click **Save attributes**.
5. For variable products, you can instead set a value **per variation** in the Variations tab — the snippet reads whatever value WooCommerce resolves for that specific product/variation via `get_attribute()`.
6. Products that have no value set for this attribute (or a value of `0`/`1`) are treated as **not sold by package** — their price/stock is left untouched.

If your store already uses a differently-named attribute for this, you don't need to rename anything: point the snippet at it instead, either by changing the `WCSNIP_PACKAGE_ATTRIBUTE` constant to your taxonomy slug, or with the `wcsnip_package_attribute` filter (see Configuration below).

## Installation

**Option A — plain PHP**
Copy [`unit-price-to-package-price.php`](unit-price-to-package-price.php) into your child theme's `functions.php`, or into a small site-specific plugin.

**Option B — Code Snippets plugin**
In WordPress admin, go to **Snippets → Import** and upload [`unit-price-to-package-price.code-snippets.json`](unit-price-to-package-price.code-snippets.json).

## Configuration

Edit the constants at the top of the file, or hook into the filters from your theme/plugin instead:

| Constant | Default | Purpose |
|---|---|---|
| `WCSNIP_PACKAGE_ATTRIBUTE` | `pa_units-per-package` | Attribute (taxonomy) slug that stores units per package. |
| `WCSNIP_PACKAGE_EXCLUDE_SKU_SUFFIX` | `Q` | SKU suffix that excludes a product from package pricing. Set to `''` to disable this rule. |

| Filter | Arguments | Purpose |
|---|---|---|
| `wcsnip_package_attribute` | `$attribute, $product` | Change which attribute is read per product. |
| `wcsnip_package_excluded_product` | `$excluded, $product` | Fully replace the exclusion logic (e.g. use a checkbox instead of an SKU suffix). |
| `wcsnip_package_units` | `$units, $product` | Override the final units-per-package value. |

## Notes

- Shares its `wcsnip_package_*` helper functions with the [Stock quantity per package display](../Stock%20quantity%20per%20package%20display/) snippet (same attribute/exclusion convention). Both snippets guard their function declarations with `function_exists()`, so it's safe to use either one alone or both together.
- Assumes displayed prices already include/exclude tax the way your store is configured (`wc_get_price_to_display()` is used, so it follows your WooCommerce tax display settings automatically).
