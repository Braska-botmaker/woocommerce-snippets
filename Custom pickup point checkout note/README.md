# Custom Pickup Point Checkout Note

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](README.md#requirements)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)](README.md#requirements)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588A?logo=woocommerce&logoColor=white)](README.md#requirements)

Adds a short note under WooCommerce's **Local pickup** shipping option at checkout, shown only when it's selected, plus full pickup point details (address, phone, opening hours) in the customer's order emails whenever the order uses local pickup.

## What it does

1. **Checkout**: a short note is printed right under the Local Pickup shipping option, but only once the customer has actually selected it — not for every shipping option shown.
2. **Order emails** (customer-facing only): if the order used local pickup, a full details block (address, phone, hours, and the short note) is added after the order table, in both HTML and plain-text emails.
3. Ships with a tiny scoped CSS rule so the note is visually muted by default.

### When does the note actually show up?

The whole snippet is conditional on the shipping method with **method ID `local_pickup`** — not on its label. Concretely:

- The checkout note only renders once the customer has **selected** the shipping rate whose method ID is `local_pickup` (WooCommerce's built-in "Local pickup" method, or any custom method registered under that same ID). Any other shipping method (flat rate, free shipping, a custom courier, etc.) is ignored entirely.
- The email block only renders when the **placed order** contains a shipping line with that same method ID.
- Renaming the method's *label* in WooCommerce settings (e.g. "Pickup in our store") is fine and does not break anything — what matters is the underlying method ID, not the text the customer sees.
- If you're using a custom/third-party shipping method with a different ID, either register it as `local_pickup`, or change `'local_pickup'` in `wcsnip_pickup_note_checkout()` and `wcsnip_pickup_note_email()` to match your method's ID.

## Requirements

- WooCommerce
- A shipping method with method ID `local_pickup` — WooCommerce's built-in Local Pickup method, or any custom method registered under that ID. Enable it under **WooCommerce → Settings → Shipping → (your shipping zone) → Add shipping method → Local pickup**.

## Installation

**Option A — plain PHP**
Copy [`custom-pickup-point-checkout-note.php`](custom-pickup-point-checkout-note.php) into your child theme's `functions.php`, or into a small site-specific plugin.

**Option B — Code Snippets plugin**
In WordPress admin, go to **Snippets → Import** and upload [`custom-pickup-point-checkout-note.code-snippets.json`](custom-pickup-point-checkout-note.code-snippets.json).

## Configuration

**All four fields are empty by default, on purpose.** This snippet never ships with fake example text — until you fill in at least one field, it prints nothing at all, at checkout or in emails. That also means: activating it is always safe, and there's no example content to remember to remove before going live.

Edit the constants near the top of the file:

| Constant | Default | Purpose |
|---|---|---|
| `WCSNIP_PICKUP_NOTE_SHORT` | `''` (empty) | Short note shown at checkout under the selected Local Pickup option, e.g. `'Orders are dispatched on Wednesdays, ready for pickup on Thursdays.'` |
| `WCSNIP_PICKUP_ADDRESS` | `''` (empty) | Pickup point address, shown in order emails, e.g. `'Example Street 123, 100 00 City'`. |
| `WCSNIP_PICKUP_PHONE` | `''` (empty) | Pickup point phone number, shown in order emails, e.g. `'+1 234 567 890'`. Left out of the email entirely if empty. |
| `WCSNIP_PICKUP_HOURS` | `''` (empty) | Opening hours, shown in order emails, e.g. `"Mon–Fri: 10:00–20:00\nSat–Sun: 10:00–18:00"`. |

Each field is independent — fill in only the ones you need. The order-email detail block automatically skips any field left empty (no blank lines).

Or override the content programmatically instead of editing the file:

| Filter | Arguments | Purpose |
|---|---|---|
| `wcsnip_pickup_note_short` | `$note` | Replace the short checkout note. |
| `wcsnip_pickup_note_details` | `$lines` (array) | Replace the full list of detail lines shown in emails, after empty fields have already been filtered out. |

## Notes

- The checkout note only renders once the customer has explicitly selected Local Pickup as their shipping method — it won't show up next to every shipping option in the list. See [When does the note actually show up?](#when-does-the-note-actually-show-up) above.
- Strings are wrapped with `__()`/`esc_html__()` under the `woocommerce-snippets` text domain, so the snippet is translation-ready if you load a `.mo`/`.po` file for that domain.
