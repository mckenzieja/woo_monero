=== Monero WooCommerce Gateway ===
Contributors: serhack, mosu-forge, monero-integrations
Donate link: https://monerointegrations.com/donate.html
Tags: monero, woocommerce, cryptocurrency, payment, gateway, blockchain, xmr
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 4.0.0
WC requires at least: 5.0
WC tested up to: 9.4
License: MIT
License URI: https://opensource.org/licenses/MIT

Accept Monero (XMR) cryptocurrency payments in your WooCommerce store with real-time validation and flexible confirmation options.

== Description ==

The Monero WooCommerce Gateway allows you to accept Monero (XMR) cryptocurrency payments directly in your WooCommerce store. This plugin provides secure, decentralized payment processing with multiple validation methods and extensive customization options.

= Key Features =

* **Multiple Validation Methods**: Use either your wallet viewkey with blockchain explorer or run your own monero-wallet-rpc for maximum security
* **Real-time Updates**: Payment validation through cron jobs - customers don't need to stay on the page
* **AJAX Order Updates**: Status updates without page reloads for better user experience  
* **Multi-transaction Support**: Customers can pay with multiple transactions
* **Configurable Confirmations**: Set confirmation requirements from 0 (instant) to 60 blocks
* **Live Exchange Rates**: Updated every minute with locked-in rates after order placement
* **Comprehensive Integration**: Works with emails, order pages, and admin interface
* **Payment History**: View all received payments with blockchain explorer links
* **Optional Monero Pricing**: Display all store prices in Monero
* **QR Code Support**: Generate QR codes for easy mobile payments
* **Shortcodes**: Display exchange rates and "Monero Accepted" badges

= Security & Privacy =

**Viewkey Method**: Uses your wallet's viewkey to verify incoming payments via blockchain explorer. Your viewkey is transmitted over HTTPS but allows viewing incoming transactions. Funds remain completely secure.

**Wallet RPC Method**: Run monero-wallet-rpc on your server for maximum privacy and security. Recommended to use view-only wallets to prevent hot wallet risks.

= Technical Requirements =

* PHP 7.4 or higher
* WordPress 5.0 or higher  
* WooCommerce 5.0 or higher
* BCMath PHP extension
* SSL certificate (recommended)

= Supported Currencies =

Works with 100+ fiat currencies and Bitcoin for exchange rate calculation including USD, EUR, GBP, JPY, CAD, AUD and many more.

== Installation ==

= Automatic Installation =

1. Go to Plugins > Add New in your WordPress admin
2. Search for "Monero WooCommerce Gateway"
3. Click "Install Now" and then "Activate"
4. Go to WooCommerce > Settings > Payments
5. Enable "Monero Gateway" and configure your settings

= Manual Installation =

1. Download the plugin from GitHub or WordPress.org
2. Upload the plugin files to `/wp-content/plugins/monero-woocommerce-gateway/`
3. Activate the plugin through the WordPress admin
4. Configure the plugin in WooCommerce > Settings > Payments > Monero Gateway

= Cron Setup (Recommended) =

For better performance, disable WordPress cron and use system cron:

1. Add `define('DISABLE_WP_CRON', true);` to your wp-config.php
2. Add this line to your server's crontab:
   `* * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1`

== Configuration ==

= Method 1: Viewkey (Easiest) =

1. Set Confirmation Type to "viewkey"
2. Enter your Monero wallet address (starts with 4)
3. Enter your wallet's secret viewkey
4. Configure other settings as needed

= Method 2: Monero Wallet RPC (Most Secure) =

1. Install Monero binaries on your server
2. Run monerod and monero-wallet-rpc (preferably as system services)
3. Set Confirmation Type to "monero-wallet-rpc"
4. Configure host/port settings
5. Use a view-only wallet for security

= Configuration Options =

* **Enable/Disable**: Turn the gateway on or off
* **Title**: Payment method name shown to customers
* **Discount**: Percentage discount (or surcharge if negative) for Monero payments
* **Order Valid Time**: How long orders remain valid (default: 1 hour)
* **Confirmations**: Required block confirmations (0-60)
* **Confirmation Type**: Choose viewkey or wallet RPC validation
* **QR Codes**: Enable QR code generation
* **Monero Pricing**: Show all prices in Monero (experimental)
* **Testnet**: Use testnet for development/testing

== Frequently Asked Questions ==

= Is this plugin secure? =

Yes. When using viewkey method, only your viewkey is transmitted (your funds cannot be stolen). With wallet RPC, everything stays on your server. Always use view-only wallets when possible.

= Do I need to run a Monero node? =

No. The viewkey method uses public blockchain explorers. However, running your own node with wallet RPC provides maximum privacy and security.

= What happens if a customer underpays? =

The plugin supports partial payments and multiple transactions. Customers can send additional payments to complete their order.

= Can I use this on testnet? =

Yes. Enable the testnet option in settings and use testnet addresses and viewkeys.

= Does this work with WooCommerce subscriptions? =

The plugin has subscription support hooks, but recurring Monero payments require manual processing since cryptocurrency payments can't be automatically charged.

== Screenshots ==

1. Plugin settings page with configuration options
2. Checkout page showing Monero payment option  
3. Order confirmation page with payment details and QR code
4. Admin order details with Monero payment information
5. Payment history page showing all transactions

== Shortcodes ==

= Display Exchange Rates =

`[monero-price]` - Shows price in store's default currency
`[monero-price currency="USD"]` - Shows price in USD
`[monero-price currency="BTC"]` - Shows price in Bitcoin

= Display Monero Accepted Badge =

`[monero-accepted-here]` - Shows "Monero Accepted Here" image

== Changelog ==

= 4.0.0 =
* **MAJOR UPDATE**: Complete plugin modernization
* Updated for WordPress 6.7+ and WooCommerce 9.4+ compatibility
* Added PHP 8.0+ support and improved performance
* Enhanced security with better input validation
* Added High Performance Order Storage (HPOS) compatibility
* Improved admin interface and user experience
* Updated text domain and translation support
* Better error handling and logging
* Code refactoring for maintainability
* Added proper plugin header information

= 3.0.5 =
* Previous stable release
* Basic WordPress 5.7.2 compatibility
* Legacy WooCommerce support

== Upgrade Notice ==

= 4.0.0 =
Major update with modern WordPress/WooCommerce compatibility. Test on staging environment before updating production sites. Backup your site before upgrading.

== Development ==

This plugin is open source and developed on GitHub. Contributions welcome!

* GitHub: https://github.com/monero-integrations/monerowp
* Issues: Report bugs and request features on GitHub
* Documentation: https://monerointegrations.com/

== Privacy Policy ==

This plugin may transmit your wallet viewkey to blockchain explorers when using viewkey validation method. No other personal data is transmitted to external services. When using wallet RPC method, all validation is done locally on your server.

== Support ==

For support, please:
1. Check the FAQ section
2. Visit our documentation
3. Create an issue on GitHub
4. Contact the Monero Integrations community
