<?php
/**
 * Snippet: Custom Order Email Notice
 *
 * Adds a short custom message to WooCommerce customer order emails — by
 * default only the "Processing order" email. Useful for a temporary
 * announcement (a dispatch delay during a stock take, holiday hours, a
 * thank-you note, ...). The styling is deliberately understated — an
 * optional bold heading and the text, set off by a thin rule, with no
 * coloured box — so it fits any kind of message, not just warnings.
 *
 * What it does:
 *   1. Prints an optional heading + your message after (or before) the
 *      order details table, separated from it by a thin horizontal rule.
 *   2. Adds the same text to the plain-text version of those emails.
 *   3. Prints nothing until you fill in the message, so activating it on a
 *      live site is always safe.
 *   4. Multilingual, two ways — no extra snippet either way:
 *        a) set WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION to true and translate
 *           the message/heading from wp-admin via Polylang or WPML, or
 *        b) set the message constant to a `locale => text` array and the
 *           snippet picks the row for the language each email is sent in.
 *
 * Requirements:
 *   - WooCommerce
 *
 * Configuration:
 *   WCSNIP_EMAIL_NOTICE_MESSAGE is EMPTY by default on purpose — this
 *   snippet never prints placeholder/example content on a live site, and
 *   adds nothing to any email until the message is set. Edit the constants
 *   below, or override the filters from your theme/plugin instead of
 *   editing this file directly.
 *
 * @link https://github.com/Braska-botmaker/woocommerce-snippets
 */

defined( 'ABSPATH' ) || exit;

/**
 * The message shown to the customer. Plain text; line breaks are preserved.
 * Empty by default = nothing is added to any email.
 *
 * Set it in one of two ways — no extra code anywhere else:
 *
 *   - ONE language: a plain string.
 *       define( 'WCSNIP_EMAIL_NOTICE_MESSAGE',
 *           'From 20 to 23 September 2026 we are doing a stock take, so your'
 *           . ' order may be dispatched a few days later than usual.' );
 *
 *   - SEVERAL languages, from wp-admin: keep this a plain string and set
 *     WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION to true (Polylang / WPML).
 *
 *   - SEVERAL languages, in code: a locale => text array. The row is chosen
 *     from the language the email is sent in — exact locale (`cs_CZ`), then
 *     language only (`cs`), then a `default` key for anything else.
 *       define( 'WCSNIP_EMAIL_NOTICE_MESSAGE', array(
 *           'cs_CZ'   => 'Od 20. do 23. září 2026 probíhá inventura skladu, '
 *                      . 'objednávka se může o pár dní zpozdit.',
 *           'en_US'   => 'From 20 to 23 September 2026 we are doing a stock '
 *                      . 'take, so your order may be delayed.',
 *           'default' => 'From 20 to 23 September 2026 we are doing a stock '
 *                      . 'take, so your order may be delayed.',
 *       ) );
 */
if ( ! defined( 'WCSNIP_EMAIL_NOTICE_MESSAGE' ) ) {
	define( 'WCSNIP_EMAIL_NOTICE_MESSAGE', '' );
}

/**
 * Optional short bold heading shown above the message. Empty by default =
 * just the message, with no heading. Like the message, this can be a plain
 * string or a locale => text array.
 */
if ( ! defined( 'WCSNIP_EMAIL_NOTICE_HEADING' ) ) {
	define( 'WCSNIP_EMAIL_NOTICE_HEADING', '' );
}

/**
 * Translate the message/heading from wp-admin instead of this file. Set to
 * true if you run Polylang or WPML: the plain-string message and heading
 * are registered for string translation (Polylang: Languages -> Strings;
 * WPML: String Translation) under the name "Custom order email notice", and
 * the translation for each customer's language is used when the email is
 * sent. Leave false, or use a `locale => text` array above, if you don't
 * use one of those plugins. A `locale => text` array is never sent through
 * string translation — it already carries every language itself.
 */
if ( ! defined( 'WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION' ) ) {
	define( 'WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION', false );
}

/**
 * Comma-separated list of WooCommerce email IDs the message is added to.
 * Other customer email IDs you might use: customer_completed_order,
 * customer_on_hold_order, customer_refunded_order, customer_invoice,
 * customer_note. Admin email IDs (e.g. new_order) work too.
 */
if ( ! defined( 'WCSNIP_EMAIL_NOTICE_EMAIL_IDS' ) ) {
	define( 'WCSNIP_EMAIL_NOTICE_EMAIL_IDS', 'customer_processing_order' );
}

/**
 * Where to put the message relative to the order details table in the
 * email: 'after' (default) or 'before'.
 */
if ( ! defined( 'WCSNIP_EMAIL_NOTICE_POSITION' ) ) {
	define( 'WCSNIP_EMAIL_NOTICE_POSITION', 'after' );
}

/**
 * Pick the right string for the current email language out of a
 * `locale => text` map: exact locale first (e.g. `cs_CZ`), then language
 * only (`cs`), then a `default` / `''` key, then the given fallback.
 * WooCommerce switches the locale to the recipient's language while it
 * renders a customer email, so `get_locale()` is the recipient's language
 * on a properly configured multilingual store, and the site language
 * otherwise.
 */
if ( ! function_exists( 'wcsnip_email_notice_pick_locale' ) ) {
	function wcsnip_email_notice_pick_locale( $map, $fallback ) {
		if ( ! is_array( $map ) || ! $map ) {
			return $fallback;
		}

		$locale = get_locale();

		foreach ( array( $locale, substr( $locale, 0, 2 ), 'default', '' ) as $key ) {
			if ( isset( $map[ $key ] ) && '' !== trim( (string) $map[ $key ] ) ) {
				return (string) $map[ $key ];
			}
		}

		return $fallback;
	}
}

/**
 * Run a plain-string message/heading through Polylang or WPML string
 * translation, so it can be edited from wp-admin. No-op when
 * WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION is off, the text is empty, or
 * neither plugin is active. `$name` is the string's label in the
 * translation UI ("Message" or "Heading").
 */
if ( ! function_exists( 'wcsnip_email_notice_string_translate' ) ) {
	function wcsnip_email_notice_string_translate( $text, $name, $email = null ) {
		$text = (string) $text;

		if ( ! WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION || '' === trim( $text ) ) {
			return $text;
		}

		if ( function_exists( 'pll__' ) ) {
			return (string) pll__( $text );
		}

		if ( has_filter( 'wpml_translate_single_string' ) ) {
			// Read the order's own WPML language directly instead of relying
			// on WPML's ambient "current language", which isn't always in
			// sync with the locale WooCommerce switched to for this email
			// (e.g. when the email is sent from a cron job or wp-admin).
			$order = $email && is_a( $email->object, 'WC_Order' ) ? $email->object : null;
			$lang  = $order ? $order->get_meta( 'wpml_language' ) : '';

			return (string) apply_filters( 'wpml_translate_single_string', $text, 'Custom order email notice', $name, $lang ?: null );
		}

		return $text;
	}
}

/**
 * Register the plain-string message/heading with Polylang / WPML so a
 * translator can find them in the string-translation screen. Runs on `init`
 * only when string translation is enabled; array values are skipped (they
 * already hold every language).
 */
if ( WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION ) {
	add_action( 'init', 'wcsnip_email_notice_register_strings' );
}
if ( ! function_exists( 'wcsnip_email_notice_register_strings' ) ) {
	function wcsnip_email_notice_register_strings() {
		$strings = array(
			'Message' => is_array( WCSNIP_EMAIL_NOTICE_MESSAGE ) ? '' : (string) WCSNIP_EMAIL_NOTICE_MESSAGE,
			'Heading' => is_array( WCSNIP_EMAIL_NOTICE_HEADING ) ? '' : (string) WCSNIP_EMAIL_NOTICE_HEADING,
		);

		foreach ( $strings as $name => $value ) {
			if ( '' === trim( $value ) ) {
				continue;
			}

			if ( function_exists( 'pll_register_string' ) ) {
				pll_register_string( 'wcsnip_email_notice_' . strtolower( $name ), $value, 'Custom order email notice', false !== strpos( $value, "\n" ) );
			}

			do_action( 'wpml_register_single_string', 'Custom order email notice', $name, $value );
		}
	}
}

/**
 * Message text for the given email. Resolves the constant (a `locale => text`
 * array picks by language; a plain string optionally goes through Polylang /
 * WPML string translation), then an optional `wcsnip_email_notice_messages`
 * filter map, then the `wcsnip_email_notice_message` filter has the final say.
 */
if ( ! function_exists( 'wcsnip_email_notice_message' ) ) {
	function wcsnip_email_notice_message( $email = null ) {
		$message = is_array( WCSNIP_EMAIL_NOTICE_MESSAGE )
			? wcsnip_email_notice_pick_locale( WCSNIP_EMAIL_NOTICE_MESSAGE, '' )
			: wcsnip_email_notice_string_translate( WCSNIP_EMAIL_NOTICE_MESSAGE, 'Message', $email );

		$message = wcsnip_email_notice_pick_locale(
			(array) apply_filters( 'wcsnip_email_notice_messages', array(), $email ),
			$message
		);

		return (string) apply_filters( 'wcsnip_email_notice_message', $message, $email );
	}
}

/**
 * Heading text for the given email. Same resolution as the message.
 */
if ( ! function_exists( 'wcsnip_email_notice_heading' ) ) {
	function wcsnip_email_notice_heading( $email = null ) {
		$heading = is_array( WCSNIP_EMAIL_NOTICE_HEADING )
			? wcsnip_email_notice_pick_locale( WCSNIP_EMAIL_NOTICE_HEADING, '' )
			: wcsnip_email_notice_string_translate( WCSNIP_EMAIL_NOTICE_HEADING, 'Heading', $email );

		$heading = wcsnip_email_notice_pick_locale(
			(array) apply_filters( 'wcsnip_email_notice_headings', array(), $email ),
			$heading
		);

		return (string) apply_filters( 'wcsnip_email_notice_heading', $heading, $email );
	}
}

/**
 * WooCommerce email IDs the message should appear in, as a trimmed array.
 * Override with the `wcsnip_email_notice_email_ids` filter.
 */
if ( ! function_exists( 'wcsnip_email_notice_email_ids' ) ) {
	function wcsnip_email_notice_email_ids() {
		$ids = array_filter( array_map( 'trim', explode( ',', (string) WCSNIP_EMAIL_NOTICE_EMAIL_IDS ) ) );

		return (array) apply_filters( 'wcsnip_email_notice_email_ids', array_values( $ids ) );
	}
}

/**
 * Render the message. Hooked on both the "before" and "after" order-table
 * email actions; only the one matching WCSNIP_EMAIL_NOTICE_POSITION
 * actually prints. The email is targeted by its ID (see
 * WCSNIP_EMAIL_NOTICE_EMAIL_IDS), so $sent_to_admin is not checked here —
 * the default target, customer_processing_order, is customer-only anyway.
 */
add_action( 'woocommerce_email_before_order_table', 'wcsnip_email_notice_render', 10, 4 );
add_action( 'woocommerce_email_after_order_table', 'wcsnip_email_notice_render', 10, 4 );
function wcsnip_email_notice_render( $order, $sent_to_admin, $plain_text, $email ) {

	$position = 'before' === strtolower( trim( (string) WCSNIP_EMAIL_NOTICE_POSITION ) ) ? 'before' : 'after';
	if ( current_action() !== 'woocommerce_email_' . $position . '_order_table' ) {
		return;
	}

	if ( ! is_a( $email, 'WC_Email' ) || empty( $email->id ) ) {
		return;
	}

	if ( ! in_array( $email->id, wcsnip_email_notice_email_ids(), true ) ) {
		return;
	}

	$message = trim( wcsnip_email_notice_message( $email ) );
	if ( '' === $message ) {
		return; // Nothing configured — print nothing.
	}

	$heading = trim( wcsnip_email_notice_heading( $email ) );

	if ( $plain_text ) {
		echo "\n";
		if ( '' !== $heading ) {
			echo $heading . "\n";
		}
		echo $message . "\n\n";

		return;
	}

	$heading_html = '' !== $heading
		? '<strong style="display:block; margin-bottom:4px;">' . esc_html( $heading ) . '</strong>'
		: '';

	$html = sprintf(
		'<div style="margin:24px 0; padding-top:14px; border-top:1px solid #e0e0e0; font-size:14px; line-height:1.6;">%s%s</div>',
		$heading_html,
		nl2br( esc_html( $message ) )
	);

	echo apply_filters( 'wcsnip_email_notice_html', $html, $message, $heading, $email );
}
