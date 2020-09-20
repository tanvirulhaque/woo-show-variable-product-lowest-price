<?php
/*
 * Plugin Name: WooCommerce Show Variable Product Lowest Price
 * Plugin URI: https://wordpress.org/
 * Description: Shows only the lowest price and sale in variable WooCommerce products.
 * Author: Tanvirul Haque
 * Version: 1.0.0
 * Author URI: http://wpxpress.net
 * Text Domain: woo-show-variable-product-lowest-price
 * Domain Path: /languages
 * WC requires at least: 3.0
 * WC tested up to: 4.5.1
 * License: GPLv2+
*/

function wvlp_variable_price_range( $price, $product ) {
    $prefix = sprintf( '%s: ', __( 'From', 'woo-show-variable-product-lowest-price' ) );

    $wvlp_min_regular_price = $product->get_variation_regular_price( 'min', true );
    $wvlp_min_sale_price    = $product->get_variation_sale_price( 'min', true );
    $wvlp_max_price         = $product->get_variation_price( 'max', true );
    $wvlp_min_price         = $product->get_variation_price( 'min', true );
    $wvlp_price             = ( $wvlp_min_sale_price == $wvlp_min_regular_price ) ? wc_price( $wvlp_min_regular_price ) : '<del>' . wc_price( $wvlp_min_regular_price ) . '</del> ' . '<ins>' . wc_price( $wvlp_min_sale_price ) . '</ins>';

    return ( $wvlp_min_price == $wvlp_max_price ) ? $wvlp_price : sprintf( '%s%s', $prefix, $wvlp_price );
}

add_filter( 'woocommerce_variable_sale_price_html', 'wvlp_variable_price_range', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'wvlp_variable_price_range', 10, 2 );