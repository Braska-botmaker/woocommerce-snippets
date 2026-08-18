<?php
/**
 * Snippet: Stock Quantity per Package Display
 *
 * Shows WooCommerce stock quantity as a number of PACKAGES instead of
 * single units on the frontend, while keeping WooCommerce's original stock
 * text and translations intact. Also converts ordered quantities (entered
 * as packages) back into units when WooCommerce reduces stock on an order.
 *
 * How it works:
 *   - Stock is stored in the database in UNITS.
 *   - The frontend displays floor(units_in_stock / units_per_package).
 *   - When an order is placed, the ordered quantity (in packages) is
 *     converted back to units before WooCommerce reduces stock.
 *   - Products excluded from package pricing (see the shared helpers below)
 *     are shown/handled in plain units.
 *
 * Requirements:
 *   - WooCommerce
 *   - A product attribute storing units per package, see
 *     `WCSNIP_PACKAGE_ATTRIBUTE` below (same convention as the companion
 *     "Unit price to package price" snippet — install both together if you
 *     sell by package, or just this one if you only need the stock display).
 *
 * Configuration:
 *   Edit the constants below, or override the filters from your theme/
 *   plugin instead of editing this file directly.
 *
 * @link https://github.com/Braska-botmaker/woocommerce-snippets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Global attribute (taxonomy) slug that stores the number of units per
 * package. Change this constant, or use the `wcsnip_package_attribute`
 * filter below.
 */
if ( ! defined( 'WCSNIP_PACKAGE_ATTRIBUTE' ) ) {
	define( 'WCSNIP_PACKAGE_ATTRIBUTE', 'pa_units-per-package' );
}

/**
 * SKU suffix used to exclude a product from package handling
 * (case-insensitive). Set to '' (empty string) to disable this rule
 * entirely. Change this constant, or use the
 * `wcsnip_package_excluded_product` filter below.
 */
if ( ! defined( 'WCSNIP_PACKAGE_EXCLUDE_SKU_SUFFIX' ) ) {
	define( 'WCSNIP_PACKAGE_EXCLUDE_SKU_SUFFIX', 'Q' );
}

/*
 * --- Shared helpers ---------------------------------------------------
 * Identical to the ones in the "Unit price to package price" snippet, and
 * guarded with function_exists() so this snippet also works completely on
 * its own, or side by side with that one, without a redeclaration error.
 * ------------------------------------------------------------------------
 */

if ( ! function_exists( 'wcsnip_package_product_excluded' ) ) {
	function wcsnip_package_product_excluded( $product ) {
		$excluded = false;

		if ( $product && WCSNIP_PACKAGE_EXCLUDE_SKU_SUFFIX !== '' ) {
			$sku    = $product->get_sku();
			$suffix = WCSNIP_PACKAGE_EXCLUDE_SKU_SUFFIX;

			if ( $sku && strtoupper( substr( $sku, - strlen( $suffix ) ) ) === strtoupper( $suffix ) ) {
				$excluded = true;
			}
		}

		return (bool) apply_filters( 'wcsnip_package_excluded_product', $excluded, $product );
	}
}

if ( ! function_exists( 'wcsnip_package_get_units' ) ) {
	function wcsnip_package_get_units( $product ) {
		if ( ! $product || wcsnip_package_product_excluded( $product ) ) {
			return 1;
		}

		$attribute = apply_filters( 'wcsnip_package_attribute', WCSNIP_PACKAGE_ATTRIBUTE, $product );
		$value     = $product->get_attribute( $attribute );

		$units = 0;
		if ( is_string( $value ) && $value !== '' ) {
			// Be forgiving of values like "6 pcs" and just pull the number out.
			if ( preg_match( '/\d+/', $value, $matches ) ) {
				$units = (int) $matches[0];
			}
		} else {
			$units = (int) $value;
		}

		$units = $units > 1 ? $units : 1;

		return (int) apply_filters( 'wcsnip_package_units', $units, $product );
	}
}

/**
 * Stock quantity to display for a product, converted to packages.
 * Returns null when there is nothing to override (e.g. unmanaged stock).
 */
if ( ! function_exists( 'wcsnip_sqpd_get_display_qty' ) ) {
	function wcsnip_sqpd_get_display_qty( $product ) {
		if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->managing_stock() ) {
			return null;
		}

		$qty = $product->get_stock_quantity();
		if ( $qty === null ) {
			return null;
		}

		$units = wcsnip_package_get_units( $product );
		if ( $units <= 1 ) {
			return (int) $qty;
		}

		return (int) floor( (int) $qty / $units );
	}
}

/**
 * 1) Frontend stock text: swap the first number in WooCommerce's stock HTML
 * for the package count, keeping the original wording and translations.
 */
add_filter( 'woocommerce_get_stock_html', function ( $html, $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $html;
	}

	// Leave backorder messaging untouched.
	if ( $product->managing_stock() && $product->backorders_allowed() && $product->is_on_backorder( 1 ) ) {
		return $html;
	}

	$qty = wcsnip_sqpd_get_display_qty( $product );
	if ( $qty === null ) {
		return $html;
	}

	$new_html = preg_replace( '/\d+/', (string) $qty, $html, 1 );

	return is_string( $new_html ) && $new_html !== '' ? $new_html : $html;
}, 20, 2 );

/**
 * 2) Stock reduction: convert the ordered quantity (entered as packages)
 * back to units before WooCommerce reduces stock on the product.
 */
add_filter( 'woocommerce_order_item_quantity', function ( $qty, $order, $item ) {
	$qty = (int) $qty;
	if ( $qty <= 0 ) {
		return $qty;
	}

	$product = $item->get_product(); // Also resolves variations.
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->managing_stock() ) {
		return $qty;
	}

	$units = wcsnip_package_get_units( $product );
	if ( $units <= 1 ) {
		return $qty;
	}

	return $qty * $units;
}, 20, 3 );
