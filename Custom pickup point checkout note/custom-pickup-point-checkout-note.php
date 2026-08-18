<?php
/**
 * Snippet: Custom Pickup Point Checkout Note
 *
 * Adds a short note under WooCommerce's "Local pickup" shipping option at
 * checkout (shown only when it is selected), and a full set of pickup
 * details (address, phone, opening hours) in the customer order emails
 * whenever the order uses local pickup.
 *
 * Requirements:
 *   - WooCommerce
 *   - A shipping method with method ID `local_pickup` (WooCommerce's
 *     built-in Local Pickup, or any custom method registered with that ID).
 *     Add it under WooCommerce → Settings → Shipping → (your zone) →
 *     Add shipping method → Local pickup.
 *
 * Configuration:
 *   All text fields below are EMPTY by default on purpose — this snippet
 *   never prints placeholder/example content on a live site. Nothing is
 *   shown at checkout or in emails until you fill in at least one field.
 *   Edit the constants below with your own pickup point details, or
 *   override the `wcsnip_pickup_note_short` / `wcsnip_pickup_note_details`
 *   filters from your theme/plugin instead of editing this file directly.
 *
 * @link https://github.com/Braska-botmaker/woocommerce-snippets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Short note shown at checkout under the selected Local Pickup option, e.g.
 * "Orders are dispatched to the pickup point on Wednesdays and are ready for
 * collection on Thursdays." Empty by default = nothing is printed.
 */
if ( ! defined( 'WCSNIP_PICKUP_NOTE_SHORT' ) ) {
	define( 'WCSNIP_PICKUP_NOTE_SHORT', '' );
}

/** Pickup point address, shown in order emails. Empty by default. */
if ( ! defined( 'WCSNIP_PICKUP_ADDRESS' ) ) {
	define( 'WCSNIP_PICKUP_ADDRESS', '' );
}

/** Pickup point phone number, shown in order emails. Empty by default. */
if ( ! defined( 'WCSNIP_PICKUP_PHONE' ) ) {
	define( 'WCSNIP_PICKUP_PHONE', '' );
}

/** Pickup point opening hours, shown in order emails. Empty by default. */
if ( ! defined( 'WCSNIP_PICKUP_HOURS' ) ) {
	define( 'WCSNIP_PICKUP_HOURS', '' );
}

/**
 * Short checkout note. Override with the `wcsnip_pickup_note_short` filter.
 */
if ( ! function_exists( 'wcsnip_pickup_note_short' ) ) {
	function wcsnip_pickup_note_short() {
		return (string) apply_filters( 'wcsnip_pickup_note_short', WCSNIP_PICKUP_NOTE_SHORT );
	}
}

/**
 * Full pickup details as an ordered list of lines, used in order emails.
 * Empty fields are left out automatically. Override with the
 * `wcsnip_pickup_note_details` filter.
 */
if ( ! function_exists( 'wcsnip_pickup_note_details' ) ) {
	function wcsnip_pickup_note_details() {
		$lines = array(
			WCSNIP_PICKUP_ADDRESS,
			WCSNIP_PICKUP_PHONE !== '' ? __( 'Phone', 'woocommerce-snippets' ) . ': ' . WCSNIP_PICKUP_PHONE : '',
			WCSNIP_PICKUP_HOURS,
			wcsnip_pickup_note_short(),
		);

		// Drop empty fields so unfilled details never show up as blank lines.
		$lines = array_values( array_filter( $lines, function ( $line ) {
			return trim( (string) $line ) !== '';
		} ) );

		return apply_filters( 'wcsnip_pickup_note_details', $lines );
	}
}

/**
 * 1) Checkout: short note shown only under the currently selected Local
 * Pickup shipping option.
 */
add_action( 'woocommerce_after_shipping_rate', 'wcsnip_pickup_note_checkout', 10, 2 );
function wcsnip_pickup_note_checkout( $method, $index ) {

	if ( empty( $method->method_id ) || 'local_pickup' !== $method->method_id ) {
		return;
	}

	$chosen_methods = WC()->session ? WC()->session->get( 'chosen_shipping_methods' ) : array();
	$chosen_method  = isset( $chosen_methods[ $index ] ) ? $chosen_methods[ $index ] : '';

	// WooCommerce stores the chosen method as a rate ID, e.g. local_pickup:3
	if ( empty( $chosen_method ) || $chosen_method !== $method->id ) {
		return;
	}

	$note = wcsnip_pickup_note_short();
	if ( trim( $note ) === '' ) {
		return; // Nothing configured — print nothing.
	}

	printf(
		'<p class="wcsnip-pickup-note" style="margin:6px 0 0; font-size:13px; line-height:1.45;">%s</p>',
		esc_html( $note )
	);
}

/**
 * 2) Order emails: full pickup details, only for orders using local pickup.
 */
add_action( 'woocommerce_email_after_order_table', 'wcsnip_pickup_note_email', 10, 4 );
function wcsnip_pickup_note_email( $order, $sent_to_admin, $plain_text, $email ) {

	if ( $sent_to_admin || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	$has_local_pickup = false;

	foreach ( $order->get_shipping_methods() as $shipping_method ) {
		if ( 'local_pickup' === $shipping_method->get_method_id() ) {
			$has_local_pickup = true;
			break;
		}
	}

	if ( ! $has_local_pickup ) {
		return;
	}

	$lines = wcsnip_pickup_note_details();
	if ( empty( $lines ) ) {
		return; // Nothing configured — print nothing.
	}

	if ( $plain_text ) {
		echo "\n" . strtoupper( __( 'Pickup point information', 'woocommerce-snippets' ) ) . "\n";
		foreach ( $lines as $line ) {
			echo $line . "\n";
		}
		echo "\n";
	} else {
		echo '<p class="wcsnip-pickup-note" style="margin:16px 0 0; font-size:14px; line-height:1.5;"><br><strong>' . esc_html__( 'Pickup point information', 'woocommerce-snippets' ) . '</strong><br>';
		foreach ( $lines as $line ) {
			echo nl2br( esc_html( $line ) ) . '<br>';
		}
		echo '</p>';
	}
}

/**
 * 3) Small scoped CSS for the note.
 */
add_action( 'wp_head', 'wcsnip_pickup_note_css' );
function wcsnip_pickup_note_css() {
	echo '<style>.woocommerce .wcsnip-pickup-note { color:#6e6e6e; }</style>';
}
