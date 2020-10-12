<?php
/*
 * Plugin Name: WooCommerce - Disable Variable Product Price Range
 * Plugin URI: https://wordpress.org/plugins/disable-variable-product-price-range-show-only-lowest-price-in-variable-products/
 * Description: Disable Price Range and shows only the lowest price and sale price in the WooCommerce variable products.
 * Author: Tanvirul Haque
 * Version: 1.0.1
 * Author URI: http://wpxpress.net
 * Text Domain: woo-disable-variable-product-price-range
 * Domain Path: /languages
 * WC requires at least: 3.2
 * WC tested up to: 4.5.2
 * License: GPLv2+
*/

// Don't call the file directly
defined( 'ABSPATH' ) or die( 'Keep Silent' );

if ( ! class_exists( 'Woo_Disable_Variable_Price_Range' ) ) {

    /**
     * Main Class
     * @since 1.0.0
     */
    class Woo_Disable_Variable_Price_Range {

        /**
         * Version
         *
         * @since 1.0.0
         * @var  string
         */
        public $version = '1.0.1';


        /**
         * The single instance of the class.
         */
        protected static $instance = null;


        /**
         * Constructor for the class
         *
         * Sets up all the appropriate hooks and actions
         *
         * @return void
         * @since 1.0.0
         */
        public function __construct() {
            // Initialize the action hooks
            $this->init_hooks();
        }


        /**
         * Initializes the class
         *
         * Checks for an existing instance
         * and if it does't find one, creates it.
         *
         * @return object Class instance
         * @since 1.0.0
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }


        /**
         * Init Hooks
         *
         * @return void
         * @since 1.0.0
         */
        private function init_hooks() {
            add_action( 'init', array( $this, 'localization_setup' ) );
            add_action( 'admin_notices', array( $this, 'php_requirement_notice' ) );
            add_action( 'admin_notices', array( $this, 'wc_requirement_notice' ) );
            add_action( 'admin_notices', array( $this, 'wc_version_requirement_notice' ) );

            add_filter( 'woocommerce_variable_sale_price_html', array( $this, 'disable_variable_price_range' ), 10, 2 );
            add_filter( 'woocommerce_variable_price_html', array( $this, 'disable_variable_price_range' ), 10, 2 );
        }


        /**
         * Initialize plugin for localization
         *
         * @return void
         * @since 1.0.0
         *
         */
        public function localization_setup() {
            load_plugin_textdomain( 'woo-disable-variable-product-price-range', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }


        /**
         * Disable Variable Price Range Function
         *
         * @param $price
         * @param $product
         *
         * @return string
         * @since 1.0.0
         */
        public function disable_variable_price_range( $price, $product ) {
            $prefix = apply_filters( 'wdvpr_price_title', sprintf( '%s ', __( 'From:', 'woo-disable-variable-product-price-range' ) ) );

            $min_var_reg_price = $product->get_variation_regular_price( 'min', true );
            $min_var_sale_price = $product->get_variation_sale_price( 'min', true );
//            $max_var_reg_price  = $product->get_variation_regular_price( 'max', true );
//            $max_var_sale_price = $product->get_variation_sale_price( 'max', true );
//            $min_price          = $product->get_variation_price( 'min', true );
//            $max_price          = $product->get_variation_price( 'max', true );

            $price = ( $product->is_on_sale() ) ? sprintf( '%1$s <del>%2$s</del> <ins>%3$s</ins>', $prefix, wc_price( $min_var_reg_price ), wc_price( $min_var_sale_price ) ) : sprintf( '%1$s %2$s', $prefix, wc_price( $min_var_reg_price ) );

            return $price;
        }


        /**
         * PHP Version
         *
         * @return bool|int
         */
        public function is_required_php_version() {
            return version_compare( PHP_VERSION, '5.6.0', '>=' );
        }


        /**
         * PHP Requirement Notice
         */
        public function php_requirement_notice() {
            if ( ! $this->is_required_php_version() ) {
                $class   = 'notice notice-error';
                $text    = esc_html__( 'Please check PHP version requirement.', 'woo-disable-variable-product-price-range' );
                $link    = esc_url( 'https://docs.woocommerce.com/document/server-requirements/' );
                $message = wp_kses( __( "It's required to use latest version of PHP to use <strong>WooCommerce - Disable Variable Product Price Range</strong>.", 'woo-disable-variable-product-price-range' ), array( 'strong' => array() ) );

                printf( '<div class="%1$s"><p>%2$s <a target="_blank" href="%3$s">%4$s</a></p></div>', $class, $message, $link, $text );
            }
        }


        /**
         * WooCommerce Requirement Notice
         */
        public function wc_requirement_notice() {
            if ( ! $this->is_wc_active() ) {
                $class = 'notice notice-error';
                $text  = esc_html__( 'WooCommerce', 'woo-disable-variable-product-price-range' );

                $link = esc_url( add_query_arg( array(
                    'tab'       => 'plugin-information',
                    'plugin'    => 'woocommerce',
                    'TB_iframe' => 'true',
                    'width'     => '640',
                    'height'    => '500',
                ), admin_url( 'plugin-install.php' ) ) );

                $message = wp_kses( __( "<strong>WooCommerce - Disable Variable Product Price Range</strong> is an add-on of ", 'woo-disable-variable-product-price-range' ), array( 'strong' => array() ) );

                printf( '<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>', $class, $message, $link, $text );
            }
        }


        /**
         * WooCommerce Version
         */
        public function is_required_wc_version() {
            return version_compare( WC_VERSION, '3.2', '>' );
        }


        /**
         * WooCommerce Version Requirement Notice
         */
        public function wc_version_requirement_notice() {
            if ( $this->is_wc_active() && ! $this->is_required_wc_version() ) {
                $class   = 'notice notice-error';
                $message = sprintf( esc_html__( "Currently, you are using older version of WooCommerce. It's recommended to use latest version of WooCommerce to work with %s.", 'woo-disable-variable-product-price-range' ), esc_html__( 'WooCommerce - Disable Variable Product Price Range', 'woo-disable-variable-product-price-range' ) );
                printf( '<div class="%1$s"><p><strong>%2$s</strong></p></div>', $class, $message );
            }
        }


        /**
         * Check WooCommerce Activated
         */
        public function is_wc_active() {
            return class_exists( 'WooCommerce' );
        }
    }
}

/**
 * Initialize the plugin
 *
 * @return object
 */
function woo_disable_variable_price_range() {
    return Woo_Disable_Variable_Price_Range::instance();
}

// Kick Off
woo_disable_variable_price_range();
