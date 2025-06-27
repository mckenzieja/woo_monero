<?php
/*
Plugin Name: Monero WooCommerce Gateway
Plugin URI: https://github.com/monero-integrations/monerowp
Description: Extends WooCommerce by adding a Monero cryptocurrency payment gateway with support for wallet RPC and blockchain validation
Version: 4.0.0
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
WC requires at least: 5.0
WC tested up to: 9.4
Network: false
Author: mosu-forge, SerHack, Community Contributors
Author URI: https://monerointegrations.com/
License: MIT
License URI: https://opensource.org/licenses/MIT
Text Domain: monero-gateway
Domain Path: /languages
*/

// This code isn't for Dark Net Markets, please report them to Authority!

defined( 'ABSPATH' ) || exit;

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

// Constants, you can edit these if you fork this repo
define('MONERO_GATEWAY_MAINNET_EXPLORER_URL', 'https://xmrchain.net/');
define('MONERO_GATEWAY_TESTNET_EXPLORER_URL', 'https://testnet.xmrchain.com/');
define('MONERO_GATEWAY_ADDRESS_PREFIX', 0x12);
define('MONERO_GATEWAY_ADDRESS_PREFIX_INTEGRATED', 0x13);
define('MONERO_GATEWAY_ATOMIC_UNITS', 12);
define('MONERO_GATEWAY_ATOMIC_UNIT_THRESHOLD', 10); // Amount under in atomic units payment is valid
define('MONERO_GATEWAY_DIFFICULTY_TARGET', 120);

// Do not edit these constants
define('MONERO_GATEWAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MONERO_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MONERO_GATEWAY_VERSION', '4.0.0');
define('MONERO_GATEWAY_ATOMIC_UNITS_POW', pow(10, MONERO_GATEWAY_ATOMIC_UNITS));
define('MONERO_GATEWAY_ATOMIC_UNITS_SPRINTF', '%.'.MONERO_GATEWAY_ATOMIC_UNITS.'f');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'monero_init', 1);

/**
 * Initialize the Monero gateway
 */
function monero_init() {
    // If WooCommerce isn't installed, return
    if (!class_exists('WC_Payment_Gateway')) {
        add_action('admin_notices', 'monero_woocommerce_missing_notice');
        return;
    }

    // Check minimum WooCommerce version
    if (version_compare(WC_VERSION, '5.0', '<')) {
        add_action('admin_notices', 'monero_woocommerce_version_notice');
        return;
    }

    // Include our Gateway Class
    require_once('include/class-monero-gateway.php');

    // Create a new instance of the gateway
    new Monero_Gateway($add_action=false);

    // Include our Admin interface class
    require_once('include/admin/class-monero-admin-interface.php');

    // Register the gateway with WooCommerce
    add_filter('woocommerce_payment_gateways', 'monero_add_gateway');
    
    // Add plugin action links
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'monero_payment_links');
    
    // Setup cron schedules
    add_filter('cron_schedules', 'monero_cron_add_one_minute');
    add_action('wp', 'monero_activate_cron');
    add_action('monero_update_event', 'monero_update_event');
    
    // Setup order hooks
    add_action('woocommerce_thankyou_'.Monero_Gateway::get_id(), 'monero_order_confirm_page');
    add_action('woocommerce_order_details_after_order_table', 'monero_order_page');
    add_action('woocommerce_email_after_order_table', 'monero_order_email');
    
    // Setup AJAX hooks
    add_action('wc_ajax_monero_gateway_payment_details', 'monero_get_payment_details_ajax');
    
    // Setup currency hooks
    add_filter('woocommerce_currencies', 'monero_add_currency');
    add_filter('woocommerce_currency_symbol', 'monero_add_currency_symbol', 10, 2);
    
    // Setup scripts and styles
    add_action('wp_enqueue_scripts', 'monero_enqueue_scripts');
    
    // Setup shortcodes
    add_shortcode('monero-price', 'monero_price_func');
    add_shortcode('monero-accepted-here', 'monero_accepted_func');

    // Initialize Monero price hooks if enabled
    if(Monero_Gateway::use_monero_price()) {
        add_filter('wc_price', 'monero_live_price_format', 10, 3);
        add_filter('woocommerce_order_formatted_line_subtotal', 'monero_order_item_price_format', 10, 3);
        add_filter('woocommerce_get_formatted_order_total', 'monero_order_total_price_format', 10, 2);
        add_filter('woocommerce_get_order_item_totals', 'monero_order_totals_price_format', 10, 3);
    }
}

/**
 * Register Monero Gateway with WooCommerce
 */
function monero_add_gateway($methods) {
    $methods[] = 'Monero_Gateway';
    return $methods;
}

/**
 * Admin notice if WooCommerce is missing
 */
function monero_woocommerce_missing_notice() {
    echo '<div class="error notice"><p>';
    echo '<strong>' . esc_html__('Monero Gateway', 'monero-gateway') . '</strong>: ';
    echo esc_html__('WooCommerce is required for this plugin to work. Please install and activate WooCommerce.', 'monero-gateway');
    echo '</p></div>';
}

/**
 * Admin notice if WooCommerce version is too old
 */
function monero_woocommerce_version_notice() {
    echo '<div class="error notice"><p>';
    echo '<strong>' . esc_html__('Monero Gateway', 'monero-gateway') . '</strong>: ';
    echo esc_html__('This plugin requires WooCommerce 5.0 or higher. Please update WooCommerce.', 'monero-gateway');
    echo '</p></div>';
}

/**
 * Add plugin action links
 */
function monero_payment_links($links) {
    $plugin_links = array(
        '<a href="'.admin_url('admin.php?page=monero_gateway_settings').'">'.__('Settings', 'monero-gateway').'</a>'
    );
    return array_merge($plugin_links, $links);
}

/**
 * Add custom cron schedule for Monero updates
 */
function monero_cron_add_one_minute($schedules) {
    $schedules['one_minute'] = array(
        'interval' => 60,
        'display' => __('Once every minute', 'monero-gateway')
    );
    return $schedules;
}

/**
 * Activate the cron event for Monero updates
 */
function monero_activate_cron() {
    if(!wp_next_scheduled('monero_update_event')) {
        wp_schedule_event(time(), 'one_minute', 'monero_update_event');
    }
}

/**
 * Handle the cron update event
 */
function monero_update_event() {
    Monero_Gateway::do_update_event();
}

/**
 * Display Monero payment details on order confirmation page
 */
function monero_order_confirm_page($order_id) {
    Monero_Gateway::customer_order_page($order_id);
}

/**
 * Display Monero payment details on order details page
 */
function monero_order_page($order) {
    if(!is_wc_endpoint_url('order-received'))
        Monero_Gateway::customer_order_page($order);
}

/**
 * Display Monero payment details in order emails
 */
function monero_order_email($order) {
    Monero_Gateway::customer_order_email($order);
}

/**
 * Handle AJAX request for payment details
 */
function monero_get_payment_details_ajax() {
    Monero_Gateway::get_payment_details_ajax();
}

/**
 * Add Monero to WooCommerce currencies
 */
function monero_add_currency($currencies) {
    $currencies['Monero'] = __('Monero', 'monero-gateway');
    return $currencies;
}

/**
 * Add Monero currency symbol
 */
function monero_add_currency_symbol($currency_symbol, $currency) {
    switch ($currency) {
    case 'Monero':
        $currency_symbol = 'XMR';
        break;
    }
    return $currency_symbol;
}

/**
 * Replace prices with Monero live rates
 */
function monero_live_price_format($price_html, $price_float, $args) {
    $price_float = wc_format_decimal($price_float);
    if(!isset($args['currency']) || !$args['currency']) {
        global $woocommerce;
        $currency = strtoupper(get_woocommerce_currency());
    } else {
        $currency = strtoupper($args['currency']);
    }
    return Monero_Gateway::convert_wc_price($price_float, $currency);
}

/**
 * Format order item prices with locked exchange rate
 */
function monero_order_item_price_format($price_html, $item, $order) {
    return Monero_Gateway::convert_wc_price_order($price_html, $order);
}

/**
 * Format order total with locked exchange rate
 */
function monero_order_total_price_format($price_html, $order) {
    return Monero_Gateway::convert_wc_price_order($price_html, $order);
}

/**
 * Format order totals with locked exchange rate
 */
function monero_order_totals_price_format($total_rows, $order, $tax_display) {
    foreach($total_rows as &$row) {
        $price_html = $row['value'];
        $row['value'] = Monero_Gateway::convert_wc_price_order($price_html, $order);
    }
    return $total_rows;
}

/**
 * Enqueue scripts and styles
 */
function monero_enqueue_scripts() {
    if(Monero_Gateway::use_monero_price())
        wp_dequeue_script('wc-cart-fragments');
    if(Monero_Gateway::use_qr_code())
        wp_enqueue_script('monero-qr-code', MONERO_GATEWAY_PLUGIN_URL.'assets/js/qrcode.min.js');

    wp_enqueue_script('monero-clipboard-js', MONERO_GATEWAY_PLUGIN_URL.'assets/js/clipboard.min.js');
    wp_enqueue_script('monero-gateway', MONERO_GATEWAY_PLUGIN_URL.'assets/js/monero-gateway-order-page.js');
    wp_enqueue_style('monero-gateway', MONERO_GATEWAY_PLUGIN_URL.'assets/css/monero-gateway-order-page.css');
}

/**
 * Shortcode to display Monero price
 * [monero-price currency="USD"]
 */
function monero_price_func( $atts ) {
    global  $woocommerce;
    $a = shortcode_atts( array(
        'currency' => get_woocommerce_currency()
    ), $atts );

    $currency = strtoupper($a['currency']);
    $rate = Monero_Gateway::get_live_rate($currency);
    if($currency == 'BTC')
        $rate_formatted = sprintf('%.8f', $rate / 1e8);
    else
        $rate_formatted = sprintf('%.5f', $rate / 1e8);

    return "<span class=\"monero-price\">1 XMR = $rate_formatted $currency</span>";
}

/**
 * Shortcode to display Monero accepted badge
 * [monero-accepted-here]
 */
function monero_accepted_func() {
    return '<img src="'.MONERO_GATEWAY_PLUGIN_URL.'assets/images/monero-accepted-here.png" alt="Monero Accepted Here" />';
}

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, 'monero_deactivate');
function monero_deactivate() {
    $timestamp = wp_next_scheduled('monero_update_event');
    wp_unschedule_event($timestamp, 'monero_update_event');
}

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'monero_install');
function monero_install() {
    global $wpdb;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "monero_gateway_quotes";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               order_id BIGINT(20) UNSIGNED NOT NULL,
               payment_id VARCHAR(95) DEFAULT '' NOT NULL,
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               paid TINYINT NOT NULL DEFAULT 0,
               confirmed TINYINT NOT NULL DEFAULT 0,
               pending TINYINT NOT NULL DEFAULT 1,
               created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (order_id)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "monero_gateway_quotes_txids";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               payment_id VARCHAR(95) DEFAULT '' NOT NULL,
               txid VARCHAR(64) DEFAULT '' NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               height MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
               PRIMARY KEY (id),
               UNIQUE KEY (payment_id, txid, amount)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "monero_gateway_live_rates";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (currency)
               ) $charset_collate;";
        dbDelta($sql);
    }
}

// Load text domain for translations
add_action('init', 'monero_gateway_load_textdomain');
function monero_gateway_load_textdomain() {
    load_plugin_textdomain('monero-gateway', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

// High Performance Order Storage (HPOS) compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
