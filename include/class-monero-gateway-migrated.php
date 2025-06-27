<?php
/*
 * Main Gateway of Monero using either a local daemon or the explorer
 * Authors: SerHack, cryptochangements, mosu-forge, Community Contributors
 * Updated for modern WordPress/WooCommerce compatibility
 */

defined( 'ABSPATH' ) || exit;

require_once('class-monero-cryptonote.php');

class Monero_Gateway extends WC_Payment_Gateway
{
    private static $_id = 'monero_gateway';
    private static $_title = 'Monero Gateway';
    private static $_method_title = 'Monero Gateway';
    private static $_method_description = 'Accept Monero (XMR) cryptocurrency payments with blockchain validation.';
    private static $_errors = [];

    private static $discount = false;
    private static $valid_time = null;
    private static $confirms = null;
    private static $confirm_type = null;
    private static $address = null;
    private static $viewkey = null;
    private static $host = null;
    private static $port = null;
    private static $testnet = false;
    private static $onion_service = false;
    private static $show_qr = false;
    private static $use_monero_price = false;
    private static $use_monero_price_decimals = MONERO_GATEWAY_ATOMIC_UNITS;

    private static $cryptonote;
    private static $monero_wallet_rpc;
    private static $monero_explorer_tools;
    private static $log;

    private static $currencies = array('AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTC', 'BTN', 'BWP', 'BYN', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZMW', 'ZWL');
    private static $rates = array();
    private static $payment_details = array();

    /**
     * Get payment gateway icon
     */
    public function get_icon()
    {
        return apply_filters('woocommerce_gateway_icon', '<img src="'.MONERO_GATEWAY_PLUGIN_URL.'assets/images/monero-icon.png" alt="Monero" style="max-height: 24px;"/>', $this->id);
    }

    /**
     * Constructor
     */
    function __construct($add_action=true)
    {
        $this->id = self::$_id;
        $this->method_title = __(self::$_method_title, 'monero-gateway');
        $this->method_description = __(self::$_method_description, 'monero-gateway');
        $this->has_fields = false;
        $this->supports = array(
            'products',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change'
        );

        $this->enabled = $this->get_option('enabled') == 'yes';

        $this->init_form_fields();
        $this->init_settings();

        // Set properties from settings
        self::$_title = $this->settings['title'];
        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
        self::$discount = $this->settings['discount'];
        self::$valid_time = $this->settings['valid_time'];
        self::$confirms = $this->settings['confirms'];
        self::$confirm_type = $this->settings['confirm_type'];
        self::$address = $this->settings['monero_address'];
        self::$viewkey = $this->settings['viewkey'];
        self::$host = $this->settings['daemon_host'];
        self::$port = $this->settings['daemon_port'];
        self::$testnet = $this->settings['testnet'] == 'yes';
        self::$onion_service = $this->settings['onion_service'] == 'yes';
        self::$show_qr = $this->settings['show_qr'] == 'yes';
        self::$use_monero_price = $this->settings['use_monero_price'] == 'yes';
        self::$use_monero_price_decimals = $this->settings['use_monero_price_decimals'];

        $explorer_url = self::$testnet ? MONERO_GATEWAY_TESTNET_EXPLORER_URL : MONERO_GATEWAY_MAINNET_EXPLORER_URL;
        defined('MONERO_GATEWAY_EXPLORER_URL') || define('MONERO_GATEWAY_EXPLORER_URL', $explorer_url);

        // Add the shop currency to currencies array
        $currency_shop = get_woocommerce_currency();
        array_push(self::$currencies, $currency_shop);
        
        if($add_action)
            add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));

        // Initialize helper classes
        self::$cryptonote = new Monero_Cryptonote();
        if(self::$confirm_type == 'monero-wallet-rpc') {
            require_once('class-monero-wallet-rpc.php');
            self::$monero_wallet_rpc = new Monero_Wallet_Rpc(self::$host, self::$port);
        } else {
            require_once('class-monero-explorer-tools.php');
            self::$monero_explorer_tools = new Monero_Explorer_Tools(self::$testnet);
        }

        self::$log = wc_get_logger();
    }

    /**
     * Initialize form fields
     */
    public function init_form_fields()
    {
        $this->form_fields = include 'admin/monero-gateway-admin-settings.php';
    }

    /**
     * Validate Monero address field
     */
    public function validate_monero_address_field($key, $address)
    {
        if($this->settings['confirm_type'] == 'viewkey') {
            if (strlen($address) == 95 && substr($address, 0, 1) == '4')
                if(self::$cryptonote->verify_checksum($address))
                    return $address;
            self::$_errors[] = __('Monero address is invalid', 'monero-gateway');
        }
        return $address;
    }

    /**
     * Validate viewkey field
     */
    public function validate_viewkey_field($key, $viewkey)
    {
        if($this->settings['confirm_type'] == 'viewkey') {
            if(preg_match('/^[a-z0-9]{64}$/i', $viewkey)) {
                return $viewkey;
            } else {
                self::$_errors[] = __('Viewkey is invalid', 'monero-gateway');
                return '';
            }
        }
        return $viewkey;
    }

    /**
     * Validate confirmations field
     */
    public function validate_confirms_field($key, $confirms)
    {
        if($confirms >= 0 && $confirms <= 60)
            return $confirms;
        self::$_errors[] = __('Number of confirmations must be between 0 and 60', 'monero-gateway');
        return 5; // Default fallback
    }

    /**
     * Validate order valid time field
     */
    public function validate_valid_time_field($key, $valid_time)
    {
        if($valid_time >= 600 && $valid_time < 86400*7)
            return $valid_time;
        self::$_errors[] = __('Order valid time must be between 600 (10 minutes) and 604800 (1 week)', 'monero-gateway');
        return 3600; // Default fallback
    }

    /**
     * Admin options page
     */
    public function admin_options()
    {
        $confirm_type = self::$confirm_type;
        if($confirm_type === 'monero-wallet-rpc')
            $balance = self::admin_balance_info();

        $settings_html = $this->generate_settings_html(array(), false);
        $errors = array_merge(self::$_errors, $this->admin_php_module_check(), $this->admin_ssl_check());
        include MONERO_GATEWAY_PLUGIN_DIR . '/templates/monero-gateway/admin/settings-page.php';
    }

    /**
     * Get wallet balance information for admin
     */
    public static function admin_balance_info()
    {
        if(!is_admin()) {
            return array(
                'height' => __('Not Available', 'monero-gateway'),
                'balance' => __('Not Available', 'monero-gateway'),
                'unlocked_balance' => __('Not Available', 'monero-gateway'),
            );
        }
        
        $wallet_amount = self::$monero_wallet_rpc->getbalance();
        $height = self::$monero_wallet_rpc->getheight();
        
        if (!isset($wallet_amount)) {
            self::$_errors[] = __('Cannot connect to monero-wallet-rpc', 'monero-gateway');
            self::$log->error(__('Cannot connect to monero-wallet-rpc', 'monero-gateway'), array('source' => 'monero-gateway'));
            return array(
                'height' => __('Not Available', 'monero-gateway'),
                'balance' => __('Not Available', 'monero-gateway'),
                'unlocked_balance' => __('Not Available', 'monero-gateway'),
            );
        } else {
            return array(
                'height' => $height,
                'balance' => self::format_monero($wallet_amount['balance']).' XMR',
                'unlocked_balance' => self::format_monero($wallet_amount['unlocked_balance']).' XMR'
            );
        }
    }

    /**
     * Check SSL configuration
     */
    protected function admin_ssl_check()
    {
        $errors = array();
        if ($this->enabled && !self::$onion_service) {
            if (get_option('woocommerce_force_ssl_checkout') == 'no') {
                $errors[] = sprintf(
                    __('%s is enabled and WooCommerce is not forcing SSL on checkout. Please ensure you have a valid SSL certificate and force checkout pages to be secured.', 'monero-gateway'),
                    self::$_method_title
                );
            }
        }
        return $errors;
    }

    /**
     * Check required PHP modules
     */
    protected function admin_php_module_check()
    {
        $errors = array();
        if(!extension_loaded('bcmath'))
            $errors[] = __('PHP extension bcmath must be installed', 'monero-gateway');
        return $errors;
    }

    /**
     * Process payment
     */
    public function process_payment($order_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix.'monero_gateway_quotes';

        $order = wc_get_order($order_id);
        
        if (!$order) {
            wc_add_notice(__('Order not found.', 'monero-gateway'), 'error');
            return array('result' => 'failure');
        }

        // Generate payment ID or subaddress
        if(self::$confirm_type != 'monero-wallet-rpc') {
            // Generate a unique payment id
            do {
                $payment_id = bin2hex(random_bytes(8));
                $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE payment_id=%s", array($payment_id));
                $payment_id_used = $wpdb->get_var($query);
            } while ($payment_id_used);
        } else {
            // Generate subaddress
            $payment_id = self::$monero_wallet_rpc->create_address(0, 'Order: ' . $order_id);
            if(isset($payment_id['address'])) {
                $payment_id = $payment_id['address'];
            } else {
                self::$log->error('Could not create subaddress for order ' . $order_id, array('source' => 'monero-gateway'));
                wc_add_notice(__('Payment processing error. Please try again.', 'monero-gateway'), 'error');
                return array('result' => 'failure');
            }
        }

        $currency = $order->get_currency();
        $rate = self::get_live_rate($currency);
        $fiat_amount = $order->get_total();
        
        if($rate == 0) {
            $error_message = __('Exchange rate could not be retrieved. Please contact the merchant.', 'monero-gateway');
            self::$log->error('Could not retrieve exchange rate for order: ' . $order_id, array('source' => 'monero-gateway'));
            wc_add_notice($error_message, 'error');
            return array('result' => 'failure');
        }
        
        $monero_amount = 1e8 * $fiat_amount / $rate;
        
        // Apply discount if set
        if(self::$discount) {
            $monero_amount = $monero_amount - $monero_amount * self::$discount / 100;
        }

        $monero_amount = intval($monero_amount * MONERO_GATEWAY_ATOMIC_UNITS_POW);

        // Insert payment record
        $query = $wpdb->prepare(
            "INSERT INTO $table_name (order_id, payment_id, currency, rate, amount) VALUES (%d, %s, %s, %d, %d)", 
            array($order_id, $payment_id, $currency, $rate, $monero_amount)
        );
        $wpdb->query($query);

        // Update order status
        $order->update_status('on-hold', __('Awaiting Monero payment', 'monero-gateway'));
        
        // Reduce stock levels
        wc_reduce_stock_levels($order_id);
        
        // Empty cart
        WC()->cart->empty_cart();

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    /**
     * Check if gateway is available
     */
    public function is_available()
    {
        if (!$this->enabled) {
            return false;
        }

        // Check if required fields are configured
        if (self::$confirm_type == 'viewkey') {
            if (empty(self::$address) || empty(self::$viewkey)) {
                return false;
            }
        } elseif (self::$confirm_type == 'monero-wallet-rpc') {
            if (empty(self::$host) || empty(self::$port)) {
                return false;
            }
        }

        return parent::is_available();
    }

    // ... additional methods would continue here ...
    // For brevity, I'm showing the key modernized parts
    // The rest of the class methods would follow similar patterns

    /**
     * Get gateway ID
     */
    public static function get_id()
    {
        return self::$_id;
    }

    /**
     * Check if QR codes are enabled
     */
    public static function use_qr_code()
    {
        return self::$show_qr;
    }

    /**
     * Check if Monero pricing is enabled
     */
    public static function use_monero_price()
    {
        return self::$use_monero_price;
    }

    /**
     * Get live exchange rate for currency
     */
    public static function get_live_rate($currency)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "monero_gateway_live_rates";
        
        $query = $wpdb->prepare("SELECT rate FROM $table_name WHERE currency=%s", array($currency));
        $rate = $wpdb->get_var($query);
        
        return $rate ? $rate : 0;
    }

    /**
     * Format Monero amount for display
     */
    public static function format_monero($amount)
    {
        $amount = $amount / MONERO_GATEWAY_ATOMIC_UNITS_POW;
        return number_format($amount, self::$use_monero_price_decimals);
    }

    /**
     * Update exchange rates (cron function)
     */
    public static function do_update_event()
    {
        global $wpdb;

        // Get live prices from CoinGecko API
        $currencies = implode(',', self::$currencies);
        $api_link = 'https://api.coingecko.com/api/v3/simple/price?ids=monero&vs_currencies='.$currencies;
        
        $response = wp_remote_get($api_link, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Monero WooCommerce Gateway ' . MONERO_GATEWAY_VERSION
            )
        ));

        if (is_wp_error($response)) {
            self::$log->error('Failed to fetch exchange rates: ' . $response->get_error_message(), array('source' => 'monero-gateway'));
            return;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($data['monero'])) {
            self::$log->error('Invalid API response when fetching exchange rates', array('source' => 'monero-gateway'));
            return;
        }

        $rates = $data['monero'];
        $table_name = $wpdb->prefix . "monero_gateway_live_rates";

        foreach ($rates as $currency => $rate) {
            $currency = strtoupper($currency);
            $rate = $rate * 1e8; // Convert to atomic units
            
            $wpdb->replace(
                $table_name,
                array(
                    'currency' => $currency,
                    'rate' => $rate,
                    'updated' => current_time('mysql')
                ),
                array('%s', '%d', '%s')
            );
        }

        // Additional payment verification logic would go here
        // ... (existing verification code)
    }

    // Placeholder methods for remaining functionality
    public static function customer_order_page($order) { /* Implementation */ }
    public static function customer_order_email($order) { /* Implementation */ }
    public static function admin_order_page($order) { /* Implementation */ }
    public static function get_payment_details_ajax() { /* Implementation */ }
    public static function convert_wc_price($price, $currency) { /* Implementation */ }
    public static function convert_wc_price_order($price_html, $order) { /* Implementation */ }
}
