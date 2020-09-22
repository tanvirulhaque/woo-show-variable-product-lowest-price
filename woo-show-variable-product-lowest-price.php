<?php
/*
 * Plugin Name: WooCommerce Show Variable Product Lowest Price
 * Plugin URI: https://wordpress.org/woo-show-variable-product-lowest-price/
 * Description: Shows only the lowest price and sale price in the WooCommerce variable products.
 * Author: Tanvirul Haque
 * Version: 1.0.0
 * Author URI: http://wpxpress.net
 * Text Domain: woo-show-variable-product-lowest-price
 * Domain Path: /languages
 * WC requires at least: 3.0
 * WC tested up to: 4.5.2
 * License: GPLv2+
*/

function wvlp_variable_price_range( $price, $product ) {
    $prefix = apply_filters( 'wvlp_price_title', sprintf( '%s: ', __( 'From', 'woo-show-variable-product-lowest-price' ) ) );

    $min_var_reg_price  = $product->get_variation_regular_price( 'min', true );
    $max_var_reg_price  = $product->get_variation_regular_price( 'max', true );
    $min_var_sale_price = $product->get_variation_sale_price( 'min', true );
    $max_var_sale_price = $product->get_variation_sale_price( 'max', true );
    $min_price          = $product->get_variation_price( 'min', true );
    $max_price          = $product->get_variation_price( 'max', true );

    $price = ( $product->is_on_sale() ) ? sprintf( '%1$s <del>%2$s</del> <ins>%3$s</ins>', $prefix, wc_price( $min_var_reg_price ), wc_price( $min_var_sale_price ) ) : sprintf( '%1$s %2$s', $prefix, wc_price( $min_var_reg_price ) );

    return $price;
}

add_filter( 'woocommerce_variable_sale_price_html', 'wvlp_variable_price_range', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'wvlp_variable_price_range', 10, 2 );

