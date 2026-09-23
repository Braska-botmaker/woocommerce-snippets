# Custom Order Email Notice

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](README.md#requirements)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)](README.md#requirements)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588A?logo=woocommerce&logoColor=white)](README.md#requirements)

Adds a short custom message to WooCommerce customer order emails — by default only the **Processing order** email. Handy for a temporary announcement such as a dispatch delay, e.g. *"From 20 September 2026 to 23 September 2026 we are doing a stock take, so your order may be dispatched a few days later than usual."*

The styling is deliberately **understated** — an optional bold heading and the text, set off from the order details by a thin horizontal rule, with no coloured box — so it fits any kind of extra message, not just warnings. (If you *do* want a coloured call-out, there's a one-filter recipe below.)

## What it does

1. **HTML emails**: prints an optional heading plus your message after the order details table, separated from it by a thin `1px` rule and some spacing.
2. **Plain-text emails**: adds the same heading and message, set off with blank lines.
3. **Empty by default**: nothing is added to any email until you set the message text, so activating the snippet on a live site is always safe — there is no example content to remember to remove.
4. **Multilingual-ready**: flip one constant to translate the message from wp-admin via Polylang / WPML, or set it to a `locale => text` array — either way, no extra snippet. See [Translating the message](#translating-the-message).

## Requirements

- WooCommerce

## Installation

**Option A — plain PHP**
Copy [`custom-order-email-notice.php`](custom-order-email-notice.php) into your child theme's `functions.php`, or into a small site-specific plugin.

**Option B — Code Snippets plugin**
In WordPress admin, go to **Snippets → Import** and upload [`custom-order-email-notice.code-snippets.json`](custom-order-email-notice.code-snippets.json).

## Configuration

**`WCSNIP_EMAIL_NOTICE_MESSAGE` is empty by default, on purpose.** Until you fill it in, the snippet adds nothing to any email.

Edit the constants near the top of the file:

| Constant | Default | Purpose |
|---|---|---|
| `WCSNIP_EMAIL_NOTICE_MESSAGE` | `''` (empty) | The message shown to the customer — a plain string, or a `locale => text` array for [multiple languages](#translating-the-message). Line breaks are preserved. Empty = nothing is added to any email. |
| `WCSNIP_EMAIL_NOTICE_HEADING` | `''` (empty) | Optional short bold heading above the message (string or `locale => text` array). Empty = just the message, no heading. |
| `WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION` | `false` | Set to `true` if you use Polylang or WPML: the message and heading become translatable strings in that plugin's translation screen. See [Translating the message](#translating-the-message). |
| `WCSNIP_EMAIL_NOTICE_EMAIL_IDS` | `'customer_processing_order'` | Comma-separated list of WooCommerce email IDs the message is added to. See [Which emails?](#which-emails) below. |
| `WCSNIP_EMAIL_NOTICE_POSITION` | `'after'` | Where the message sits relative to the order details table: `'after'` or `'before'`. |

Or set the content from your theme/plugin with filters instead of editing the file:

| Filter | Arguments | Purpose |
|---|---|---|
| `wcsnip_email_notice_message` | `$message, $email` | Replace the final message text (runs last). |
| `wcsnip_email_notice_heading` | `$heading, $email` | Replace the final heading. |
| `wcsnip_email_notice_messages` | `$map (array), $email` | Optional `locale => text` map, e.g. to pull wording from WPML/Polylang — see [Translating the message](#translating-the-message). |
| `wcsnip_email_notice_headings` | `$map (array), $email` | Optional `locale => text` map for the heading. |
| `wcsnip_email_notice_email_ids` | `$ids (array)` | Replace the list of email IDs the message appears in. |
| `wcsnip_email_notice_html` | `$html, $message, $heading, $email` | Replace the full rendered HTML block — for custom styling, a coloured call-out, or to allow links/markup (the default block escapes the message as plain text). |

## Translating the message

The message is **your own text**, not a plugin string, so there is no `.po` file. Pick whichever fits — all three are set at the top of the snippet, nothing extra to install:

### A. One language

A plain string:

```php
define( 'WCSNIP_EMAIL_NOTICE_MESSAGE', 'Od 20. 9. 2026 do 23. 9. 2026 probíhá inventura skladu, objednávka se může o pár dní zpozdit.' );
```

### B. Translate from wp-admin — Polylang / WPML *(recommended if you run one of them)*

Keep the message a **plain string** (in whatever language you write in) and turn on:

```php
define( 'WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION', true );
```

The message and heading are then registered as translatable strings under the name **"Custom order email notice"**, and each customer gets the translation for their language automatically. To translate them:

- **Polylang** → *Languages → Strings* (filter by group "Custom order email notice").
- **WPML** → *WPML → String Translation* (search the same name).

The strings appear there after the snippet has run once in wp-admin — just open that screen. No code, and a translator never touches the file.

**WPML note:** the message/heading are translated via a WPML API call that reads the language **stored on the order** (`wpml_language` order meta, set by WooCommerce Multilingual), not whatever language WPML considers "current" at that moment — those two aren't always the same during an email send (cron, wp-admin, etc.), and relying on the wrong one is the most common reason the rest of the email is correctly translated but this message isn't. If the message still shows the wrong language:

1. Confirm **WooCommerce Multilingual & Multicurrency** (a separate add-on from core WPML) is active, and that the specific order actually has a language assigned.
2. In *WPML → String Translation*, check the entry under "Custom order email notice" shows your **current** message text as the original (retranslate it if you changed the message afterwards) and that its status is **Complete**, not just started.

### C. Translate in code — `locale => text` array

For a store **without** Polylang/WPML, or if you prefer keeping the wording in the file, set the constant to a map instead of a string:

```php
define( 'WCSNIP_EMAIL_NOTICE_MESSAGE', array(
    'cs_CZ'   => 'Od 20. 9. do 23. 9. 2026 probíhá inventura skladu, objednávka se může o pár dní zpozdit.',
    'sk_SK'   => 'Od 20. 9. do 23. 9. 2026 prebieha inventúra skladu, objednávka sa môže o pár dní oneskoriť.',
    'en_US'   => 'From 20 to 23 September 2026 we are doing a stock take, so your order may be dispatched a few days later than usual.',
    'default' => 'From 20 to 23 September 2026 we are doing a stock take, so your order may be delayed.',
) );
```

Row picked, in order: exact locale (`cs_CZ`) → language only (`cs`) → the `default` key → otherwise nothing. (`WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION` is ignored for an array — it already holds every language.)

`WCSNIP_EMAIL_NOTICE_HEADING` works the same in all three.

### When does a non-default language actually get used?

Options B and C both key off the language WooCommerce renders the email in (`get_locale()`):

- **With a multilingual plugin** — it stores each order's language and switches to it while building the email, so the customer gets their language automatically.
- **Plain WooCommerce**, no such plugin — customer emails always go out in the **site** language, so there is effectively one language and a single string (option A) is enough.

To check what the snippet sees, temporarily add `error_log( get_locale() );` inside the render and send yourself a test email.

### Advanced — a different plugin or string name

Option B covers Polylang and WPML with a fixed string name. For a custom name/context, or another plugin (TranslatePress, Loco, …), leave `WCSNIP_EMAIL_NOTICE_STRING_TRANSLATION` off and use the `wcsnip_email_notice_message` filter yourself:

```php
add_filter( 'wcsnip_email_notice_message', function ( $message ) {
    return apply_filters( 'wpml_translate_single_string', $message, 'My context', 'My string name' );
} );
```

### No multilingual plugin, but customers in different countries

There is no per-recipient language to switch on, so key off the order instead (`$email->object` is the `WC_Order`):

```php
add_filter( 'wcsnip_email_notice_message', function ( $message, $email ) {
    $order   = $email && is_a( $email->object, 'WC_Order' ) ? $email->object : null;
    $country = $order ? $order->get_billing_country() : '';

    return 'DE' === $country
        ? 'Vom 20. bis 23. September 2026 führen wir eine Inventur durch …'
        : $message;
}, 10, 2 );
```

## Which emails?

The message is targeted by WooCommerce **email ID**, not by order status. The default, `customer_processing_order`, is the "Processing order" email sent to the customer once payment is received. Other IDs you can add to the comma-separated list:

| Email ID | Sent to | When |
|---|---|---|
| `customer_processing_order` | Customer | Payment received, order is processing |
| `customer_completed_order` | Customer | Order marked completed |
| `customer_on_hold_order` | Customer | Order placed on hold (e.g. awaiting bank transfer) |
| `customer_refunded_order` | Customer | Order refunded |
| `customer_invoice` | Customer | Manually sent invoice / order details |
| `customer_note` | Customer | A note is added to the order |
| `new_order` | Admin | New order received |

Example — show the message in both the processing and on-hold emails:

```php
define( 'WCSNIP_EMAIL_NOTICE_EMAIL_IDS', 'customer_processing_order, customer_on_hold_order' );
```

## Recipes

### Auto-expiring message

Because `wcsnip_email_notice_message` is a filter, you can make the message switch itself off after a date without touching the snippet again:

```php
add_filter( 'wcsnip_email_notice_message', function ( $message ) {
    // Show the stock-take message only up to and including 23 Sept 2026.
    if ( current_time( 'Y-m-d' ) > '2026-09-23' ) {
        return '';
    }

    return $message;
} );
```

### Coloured call-out box

If you want the message to stand out more, replace the rendered HTML via `wcsnip_email_notice_html` (the plain-text version is unaffected):

```php
add_filter( 'wcsnip_email_notice_html', function ( $html, $message, $heading ) {
    $head = $heading !== ''
        ? '<strong style="display:block; margin-bottom:6px;">' . esc_html( $heading ) . '</strong>'
        : '';

    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0 0;"><tr>'
        . '<td style="padding:16px 20px; background-color:#fff8e5; border-left:4px solid #f0b429; font-size:14px; line-height:1.6; color:#43380a;">'
        . $head . nl2br( esc_html( $message ) )
        . '</td></tr></table>';
}, 10, 3 );
```

## Notes

- The message only renders inside emails that show an order details table (all the customer order emails do). It is added via `woocommerce_email_after_order_table` / `woocommerce_email_before_order_table`.
- The message is output as **plain text** (`esc_html` + `nl2br`) — HTML in the message is escaped, not rendered. To include a link or other markup, return your own markup from the `wcsnip_email_notice_html` filter.
- `$sent_to_admin` is not checked — the snippet trusts the configured email ID list. The default (`customer_processing_order`) is a customer-only email; only add an admin ID like `new_order` if you actually want the message there too.
