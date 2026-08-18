# Security Policy

## Supported Versions

This repository is a collection of independent, self-contained WordPress/WooCommerce snippets rather than a versioned application. The latest state of the `main` branch is the only supported version — always pull the current version of a snippet rather than relying on an older copy.

## Reporting a Vulnerability

If you believe you've found a security issue in one of these snippets (e.g. missing input sanitization/escaping, a privilege check that can be bypassed, or anything that could expose store or customer data):

1. **Do not open a public GitHub issue for it.**
2. Please report it privately via [GitHub Security Advisories](https://github.com/Braska-botmaker/woocommerce-snippets/security/advisories/new) for this repository, or through the contact form on [horakbrand.cz](https://www.horakbrand.cz).

Please include:

- The affected snippet/file.
- Steps to reproduce, or a proof of concept.
- The potential impact.

You should expect an initial response within a few days. Once a fix is available, it will be released and credited in [`CHANGELOG.md`](CHANGELOG.md), unless you'd prefer to remain anonymous.

## Scope Notes

These snippets run with the full trust level of your WordPress installation (same as any `functions.php` code or Code Snippets entry). As with any snippet you install, review the code before deploying it, keep WordPress/WooCommerce/PHP up to date, and restrict who can edit snippets/theme files on your site.
