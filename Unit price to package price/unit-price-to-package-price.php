<?php
/**
 * Snippet: Unit Price to Package Price
 *
 * Displays and charges WooCommerce product prices per PACKAGE instead of
 * per single unit, based on a "units per package" product attribute.
 * Individual products can be excluded from the recalculation (e.g. products
 * that are already priced per package) via a configurable SKU suffix rule.
 *
 * What it does:
 *   1. Multiplies the displayed price (shop & product page) by the number
 *      of units in a package.
 *   2. Recalculates the real cart/checkout line price using the same
 *      multiplier, so totals, taxes and order data stay correct.
 *   3. Lets you exclude individual products from the recalculation
 *      (by default: any product whose SKU ends with "Q").
 *
 * Requirements:
 *   - WooCommerce
 *   - A product attribute that stores the number of units per package
 *     (default expected taxonomy: `pa_units-per-package`). Create it under
 *     Products > Attributes and assign a numeric value (e.g. "6") to each
 *     product/variation that is sold by package.
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
 * SKU suffix used to exclude a product from package pricing
 * (case-insensitive). Set to '' (empty string) to disable this rule
 * entirely. Change this constant, or use the
 * `wcsnip_package_excluded_product` filter below.
 */
if ( ! defined( 'WCSNIP_PACKAGE_EXCLUDE_SKU_SUFFIX' ) ) {
	define( 'WCSNIP_PACKAGE_EXCLUDE_SKU_SUFFIX', 'Q' );
}

/**
 * Helper: is this product excluded from package pricing?
 * Override via the `wcsnip_package_excluded_product` filter for custom
 * logic (e.g. a dedicated checkbox/attribute instead of an SKU suffix).
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

/**
 * Helper: number of units in one package for a given product (minimum 1).
 * Override via the `wcsnip_package_units` filter, or the
 * `wcsnip_package_attribute` filter to change which attribute is read.
 */
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
 * 1) Displayed price (shop & product page) = unit price * units per package.
 */
add_filter( 'woocommerce_get_price_html', function ( $price_html, $product ) {
	if ( ! $product ) {
		return $price_html;
	}

	$units = wcsnip_package_get_units( $product );
	if ( $units <= 1 ) {
		return $price_html;
	}

	$unit_price    = wc_get_price_to_display( $product );
	$package_price = $unit_price * $units;

	return wc_price( $package_price );
}, 20, 2 );

/**
 * 2a) Store the unit ("base") price on the cart item when it is added, so
 * we always have a stable reference price to multiply from later.
 */
add_filter( 'woocommerce_add_cart_item_data', function ( $cart_item_data, $product_id, $variation_id ) {
	$id      = $variation_id ? $variation_id : $product_id;
	$product = wc_get_product( $id );

	if ( $product ) {
		$cart_item_data['wcsnip_base_price'] = (float) $product->get_price( 'edit' );
	}

	return $cart_item_data;
}, 10, 3 );

/**
 * 2b) Carry the base price over when the cart item is restored from session.
 */
add_filter( 'woocommerce_get_cart_item_from_session', function ( $cart_item, $values ) {
	if ( isset( $values['wcsnip_base_price'] ) ) {
		$cart_item['wcsnip_base_price'] = (float) $values['wcsnip_base_price'];
	}

	return $cart_item;
}, 10, 2 );

/**
 * 2c) Recalculate the real cart/checkout line price from the stored base
 * price, so it stays stable across multiple totals recalculations.
 */
add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	if ( ! $cart || $cart->is_empty() ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item ) {
		if ( empty( $cart_item['data'] ) || ! is_a( $cart_item['data'], 'WC_Product' ) ) {
			continue;
		}

		$product = $cart_item['data'];
		$units   = wcsnip_package_get_units( $product );

		if ( $units <= 1 ) {
			continue;
		}

		$base_price = isset( $cart_item['wcsnip_base_price'] )
			? (float) $cart_item['wcsnip_base_price']
			: (float) $product->get_price( 'edit' ); // Fallback.

		$product->set_price( $base_price * $units );
	}
}, 10 );
