<?php
/**
 * WooCommerce Snippets
 * Requires WooCommerce. Paste into child theme functions.php.
 */

// 1. Products per shop page.
add_filter('loop_shop_per_page', function () {
    return 12;
});

// 2. Change the "Sale!" badge text.
add_filter('woocommerce_sale_flash', function () {
    return '<span class="onsale">Discount</span>';
});

// 3. Hide SKU on product pages (cleaner look).
add_filter('wc_product_sku_enabled', '__return_false');

// 4. Redirect to checkout after add-to-cart (fewer abandoned carts for
//    single-product stores). Comment out if you prefer the cart page.
// add_filter('woocommerce_add_to_cart_redirect', function () {
//     return wc_get_checkout_url();
// });

// 5. Remove WooCommerce styles/scripts you don't use (example: on non-shop pages).
add_action('wp_enqueue_scripts', function () {
    if (function_exists('is_woocommerce') && !is_woocommerce()
        && !is_cart() && !is_checkout() && !is_account_page()) {
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
    }
}, 99);
