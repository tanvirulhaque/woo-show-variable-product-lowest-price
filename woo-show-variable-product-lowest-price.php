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
 * WC requires at least: 3.2
 * WC tested up to: 4.5.2
 * License: GPLv2+
*/

// Don't call the file directly
defined( 'ABSPATH' ) or die( 'Keep Silent' );

if( ! class_exists('Woo_Variable_Lowest_Price') ) {

	/**
	 * Main Class
	 * @since 1.0.0
	 */
	class Woo_Variable_Lowest_Price {
		
		/**
		 * Version
		 *
		 * @since 1.0.0
		 * @var  string
		 */
		public $version = '1.0.0';


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
		 *
		 */
		public function __construct() {

			// Define constants
			$this->define_constants();

			// Include required files
			// $this->includes();

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
		 *
		 */
		public static function instance() {

			if ( null === self::$instance ) {

				self::$instance = new self();

			}

			return self::$instance;

		}


		/**
		 * Define constants
		 *
		 * @return void
		 * @since 1.0.0
		 *
		 */
		private function define_constants() {

			define( 'WVLP_VERSION', $this->version );
			define( 'WVLP_FILE', __FILE__ );
			define( 'WVLP_DIR_PATH', plugin_dir_path( WVLP_FILE ) );
			define( 'WVLP_DIR_URI', plugin_dir_url( WVLP_FILE ) );
			define( 'WVLP_ADMIN', WVLP_DIR_PATH . 'admin' );
			define( 'WVLP_ASSETS', WVLP_DIR_URI . 'assets' );

		}


		/**
		 * Include required files
		 *
		 * @return void
		 * @since 1.0.0
		 *
		 */
		private function includes() {

			if ( is_admin() ) {
				require_once WVLP_ADMIN . '/class-wvlp-settings-api.php';
				require_once WVLP_ADMIN . '/class-wvlp-settings.php';
			}

		}


		/**
		 * Init Hooks
		 *
		 * @return void
		 * @since 1.0.0
		 *
		 */
		private function init_hooks() {

			add_action( 'init', array( $this, 'localization_setup' ) );
			add_action( 'admin_notices', array( $this, 'php_requirement_notice' ) );
			add_action( 'admin_notices', array( $this, 'wc_requirement_notice' ) );
			add_action( 'admin_notices', array( $this, 'wc_version_requirement_notice' ) );

			// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			// add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_settings_links' ) );

			add_filter( 'woocommerce_variable_sale_price_html', array( $this, 'variable_price_range' ), 10, 2 );
			add_filter( 'woocommerce_variable_price_html', array( $this, 'variable_price_range' ), 10, 2 );

		}


		/**
		 * Initialize plugin for localization
		 *
		 * @return void
		 * @since 1.0.0
		 *
		 */
		public function localization_setup() {

			load_plugin_textdomain( 'woo-show-variable-product-lowest-price', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		}

		
		public function variable_price_range( $price, $product ) {
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


		public function is_required_php_version() {
			return version_compare( PHP_VERSION, '5.6.0', '>=' );
		}


		public function php_requirement_notice() {
			if ( ! $this->is_required_php_version() ) {
				$class   = 'notice notice-error';
				$text    = esc_html__( 'Please check PHP version requirement.', 'woo-show-variable-product-lowest-price' );
				$link    = esc_url( 'https://docs.woocommerce.com/document/server-requirements/' );
				$message = wp_kses( __( "It's required to use latest version of PHP to use <strong>WooCommerce Show Variable Product Lowest Price</strong>.", 'woo-show-variable-product-lowest-price' ), array( 'strong' => array() ) );
				
				printf( '<div class="%1$s"><p>%2$s <a target="_blank" href="%3$s">%4$s</a></p></div>', $class, $message, $link, $text );
			}
		}


		public function wc_requirement_notice() {
			
			if ( ! $this->is_wc_active() ) {
				
				$class = 'notice notice-error';
				
				$text    = esc_html__( 'WooCommerce', 'woo_variable_lowest_price' );
				$link    = esc_url( add_query_arg( array(
					                                   'tab'       => 'plugin-information',
					                                   'plugin'    => 'woocommerce',
					                                   'TB_iframe' => 'true',
					                                   'width'     => '640',
					                                   'height'    => '500',
				                                   ), admin_url( 'plugin-install.php' ) ) );
				$message = wp_kses( __( "<strong>WooCommerce Show Variable Product Lowest Price</strong> is an add-on of ", 'woo_variable_lowest_price' ), array( 'strong' => array() ) );
				
				printf( '<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>', $class, $message, $link, $text );
			}
		}


		public function is_required_wc_version() {
			return version_compare( WC_VERSION, '3.2', '>' );
		}


		public function wc_version_requirement_notice() {
			if ( $this->is_wc_active() && ! $this->is_required_wc_version() ) {
				$class   = 'notice notice-error';
				$message = sprintf( esc_html__( "Currently, you are using older version of WooCommerce. It's recommended to use latest version of WooCommerce to work with %s.", 'woo_variable_lowest_price' ), esc_html__( 'WooCommerce Show Variable Product Lowest Price', 'woo_variable_lowest_price' ) );
				printf( '<div class="%1$s"><p><strong>%2$s</strong></p></div>', $class, $message );
			}
		}


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
function woo_variable_lowest_price() {
	return Woo_Variable_Lowest_Price::instance();
}

// Kick Off
woo_variable_lowest_price();
