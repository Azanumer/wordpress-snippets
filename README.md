# WordPress Snippets

Copy-paste PHP snippets for WordPress — security, speed, and customization without installing another plugin.

## How to use

1. Pick a snippet file from `/snippets`.
2. Copy what you need into your **child theme's** `functions.php` (never edit the parent theme — updates will wipe it).
3. For a single site-wide snippet, a plugin like "Code Snippets" also works.

> Back up `functions.php` before editing. One PHP syntax error = white screen.

## Snippets

| File | What it does |
|---|---|
| `snippets/01-security.php` | Disable XML-RPC, hide WP version, generic login errors, block REST user enumeration |
| `snippets/02-performance.php` | Disable emojis, embeds & bloat; limit Heartbeat; clean up `wp_head` |
| `snippets/03-customization.php` | Custom excerpt length, login page logo link, remove "Howdy", custom footer text |
| `snippets/04-woocommerce.php` | Products per page, change sale badge text, hide SKU, redirect after add-to-cart |

## Notes

- Snippets marked `// wp-config.php` belong in `wp-config.php`, not `functions.php`.
- Test on staging first. Snippets are provided as-is (MIT).
