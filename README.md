# Woo_Monero _Formerly Monero Gateway for WooCommerce_

This project is a forked version of the abandoned "Monero Gateway for WooCommerce" and improved with updated support for the latest version of WordPress/WooCommerce.

**Version 4.0.0** - Modernized for WordPress 6.7+, WooCommerce 9.4+, and PHP 8.0+

A secure, feature-rich Monero (XMR) cryptocurrency payment gateway for WooCommerce that enables direct peer-to-peer payments with real-time validation and comprehensive integration.

## ✨ Key Features

- **Dual Validation Methods**: Choose between viewkey validation via blockchain explorer or local `monero-wallet-rpc` for maximum security
- **Background Processing**: Validates payments with cron jobs - customers don't need to stay on the confirmation page
- **Real-time Updates**: Order status updates via AJAX without page reloads for better user experience
- **Multi-transaction Support**: Customers can pay with multiple transactions and are notified instantly when payments hit the mempool
- **Flexible Confirmations**: Configurable block confirmations from `0` (instant) to `60` for high-value purchases
- **Live Exchange Rates**: Price updates every minute with locked rates after order placement (default 60 minutes)
- **Comprehensive Integration**: Seamlessly integrates with WooCommerce emails, order pages, and admin interface
- **Payment History**: View all received payments with blockchain explorer links and order associations
- **Optional Monero Pricing**: Display all store prices in Monero (experimental feature)
- **QR Code Support**: Generate QR codes for easy mobile wallet payments
- **Shortcode Support**: Display live exchange rates and "Monero Accepted" badges anywhere

## 📋 Requirements

### System Requirements

- **WordPress**: 5.0+ (recommended: 6.7+)
- **WooCommerce**: 5.0+ (recommended: 9.4+)
- **PHP**: 7.4+ (recommended: 8.0+)
- **MySQL**: 5.7+ or MariaDB 10.2+
- **SSL Certificate**: Required for production (recommended)

### PHP Extensions

- **BCMath**: Required for arbitrary precision mathematics
- **cURL**: For API communications
- **JSON**: For data processing

### Monero Requirements

- Monero wallet to receive payments:
  - **GUI Wallet**: [Latest Release](https://github.com/monero-project/monero-gui/releases)
  - **CLI Wallet**: [Latest Release](https://github.com/monero-project/monero/releases)
  - **Paper Wallet**: [MoneroAddress.org](https://moneroaddress.org/)
- For RPC method: Access to run `monerod` and `monero-wallet-rpc` on your server

## 🚀 Installing the Plugin

### Automatic Method (Recommended)

1. In your WordPress admin, go to **Plugins** → **Add New**
2. Search for "**Monero WooCommerce Gateway**"
3. Click **Install Now** next to the plugin by mosu-forge, SerHack
4. Click **Activate** after installation
5. Go to **WooCommerce** → **Settings** → **Payments** → **Monero Gateway** to configure

> **Note**: Automatic installation provides auto-updates for official releases. For development or custom versions, use the manual method below.

### Manual Method

1. **Download**: Get the latest version from the [releases page](https://github.com/mckenzieja/woo_monero) or clone:

   ```bash
   git clone https://github.com/mckenzieja/woo_monero
   ```

2. **Upload**: Place the `woo_monero` folder in your `/wp-content/plugins/` directory

3. **Activate**: Go to **Plugins** in your WordPress admin and activate "**Monero WooCommerce Gateway**"

4. **Configure**: Navigate to **WooCommerce** → **Settings** → **Payments** → **Monero Gateway**

### ⚡ Performance Optimization (Highly Recommended)

For better performance and reliability, use system cron instead of WordPress cron:

1. Add this line to your `wp-config.php` file:

   ```php
   define('DISABLE_WP_CRON', true);
   ```

2. Add this to your server's crontab:
   ```bash
   * * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
   ```
   or for curl:
   ```bash
   * * * * * curl -s https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
   ```

## ⚙️ Configuration Methods

### Option 1: Viewkey Method (Easiest Setup)

**Best for**: Beginners, small to medium stores, quick setup

**You'll need**:

- Your Monero wallet address (starts with `4` for mainnet, `9A` for testnet)
- Your wallet's private viewkey (64-character hex string)

**Setup**:

1. In the plugin settings, set **Validation Method** to "**Viewkey (via blockchain explorer)**"
2. Enter your **Monero Address** and **Private Viewkey**
3. Configure other settings as needed
4. Save settings

**Privacy Note**: Your viewkey is transmitted to blockchain explorers (xmrchain.net) over HTTPS for transaction validation. While your funds remain completely secure, this allows viewing incoming transactions. For maximum privacy, use the Wallet RPC method below.

### Option 2: Monero Wallet RPC (Maximum Security & Privacy)

**Best for**: Advanced users, high-volume stores, maximum privacy

**You'll need**:

- Root/admin access to your web server
- [Latest Monero binaries](https://github.com/monero-project/monero/releases)
- Basic command line knowledge

**Setup**:

1. **Install Monero**: Download and install Monero binaries on your server
2. **Setup Services**: Install the provided [systemd unit files](https://github.com/monero-integrations/monerowp/tree/master/assets/systemd-unit-files) or run with `screen`/`tmux`
3. **Configure Plugin**: Set **Validation Method** to "**Monero Wallet RPC**"
4. **Set Connection**: Configure host (usually `127.0.0.1`) and port (default `18082`)

**Remote Node Option**: Skip running `monerod` locally by using a remote node:

```bash
monero-wallet-rpc --daemon-address node.moneroworld.com:18089
```

**Security Best Practice**: Always use a **view-only wallet** for the RPC service to prevent funds theft in case of server compromise.

## 🔧 Configuration Settings

### Basic Settings

- **Enable/Disable** - Enable or disable Monero payments _(Default: Disabled)_
- **Title** - Payment method name shown to customers _(Default: "Monero (XMR)")_
- **Description** - Payment method description during checkout
- **Discount/Surcharge (%)** - Percentage discount (positive) or surcharge (negative) for Monero payments _(Default: 0)_

### Payment Settings

- **Payment Timeout** - Seconds after order placement before expiration _(Default: 3600 [1 hour])_
- **Required Confirmations** - Blockchain confirmations needed before completion (0-60) _(Default: 5)_
  - `0` = Instant (mempool detection)
  - `1-5` = Fast confirmation (~2-10 minutes)
  - `6+` = Secure confirmation (12+ minutes)

### Validation Method

- **Validation Method** - Choose validation approach _(Default: Viewkey)_
  - **Viewkey**: Uses blockchain explorer validation
  - **Wallet RPC**: Uses local monero-wallet-rpc

#### Viewkey Settings (if selected)

- **Monero Address** - Your wallet address starting with `4` (mainnet) or `9A` (testnet)
- **Private Viewkey** - Your 64-character hexadecimal viewkey

#### Wallet RPC Settings (if selected)

- **Wallet RPC Host** - IP where monero-wallet-rpc runs _(Default: 127.0.0.1)_
- **Wallet RPC Port** - Port for wallet RPC connection _(Default: 18082)_

### Advanced Settings

- **Testnet Mode** - Enable for development/testing _(Default: Disabled)_
- **Onion Service** - Check if site accessible via Tor _(Default: Disabled)_
- **QR Code** - Show payment QR codes for mobile wallets _(Default: Enabled)_
- **Monero Pricing** - Display all prices in Monero _(Experimental - Default: Disabled)_
- **Price Decimals** - Decimal places for Monero prices when enabled _(Default: 12)_
- **SSL Warnings** - Disable SSL validation warnings _(Default: Disabled)_

## 🏷️ Shortcodes

The plugin provides convenient shortcodes for displaying Monero information on your site.

### Live Exchange Rate Shortcode

Display current Monero exchange rates in various currencies:

```html
[monero-price]
<!-- Store's default currency -->
[monero-price currency="USD"]
<!-- US Dollars -->
[monero-price currency="EUR"]
<!-- Euros -->
[monero-price currency="GBP"]
<!-- British Pounds -->
[monero-price currency="BTC"]
<!-- Bitcoin -->
[monero-price currency="CAD"]
<!-- Canadian Dollars -->
```

**Example Output**:

```
1 XMR = 123.68000 USD
1 XMR = 0.01827000 BTC
1 XMR = 105.54000 EUR
1 XMR = 94.84000 GBP
1 XMR = 168.43000 CAD
```

### Monero Accepted Badge

Display a "Monero Accepted Here" badge to promote cryptocurrency payments:

```html
[monero-accepted-here]
```

![Monero Accepted Here](assets/images/monero-accepted-here.png)

**Usage Tips**:

- Add shortcodes to pages, posts, widgets, or theme templates
- Rates update automatically every minute
- Supports 100+ fiat currencies and major cryptocurrencies
- Customize styling with CSS targeting `.monero-price` class

## 🔒 Security & Privacy

### Security Features

- **Input Validation**: All user inputs are sanitized and validated
- **SQL Injection Protection**: Uses prepared statements throughout
- **XSS Prevention**: Proper output escaping for all displayed data
- **Secure Communications**: HTTPS required for API calls and external services
- **View-Only Wallets**: Recommended for RPC method to prevent fund theft

### Privacy Considerations

- **Viewkey Method**: Your viewkey is transmitted to blockchain explorers but funds remain secure
- **RPC Method**: All validation happens locally on your server for maximum privacy
- **No Personal Data**: Plugin doesn't collect or store customer personal information
- **Tor Support**: Compatible with Tor hidden services via onion service option

## 🚀 Performance & Optimization

### Caching

- Exchange rates cached for 60 seconds to reduce API calls
- Database queries optimized for better performance
- Efficient cron job scheduling for payment validation

### Recommendations

- Use system cron instead of WP-Cron for better reliability
- Enable object caching (Redis/Memcached) for high-traffic sites
- Keep WordPress, WooCommerce, and PHP updated for optimal performance
- Use a CDN for static assets if serving global customers

## 🛠️ Troubleshooting

### Common Issues

**Plugin won't activate**

- Check PHP version (7.4+ required)
- Ensure BCMath extension is installed
- Verify WooCommerce is installed and active

**Exchange rates not updating**

- Check if cron jobs are running
- Verify external API connectivity
- Ensure proper file permissions

**Payments not detected**

- Validate wallet address and viewkey format
- Check RPC connection settings
- Verify network connectivity to blockchain explorers

**Settings won't save**

- Check file permissions on wp-content directory
- Disable conflicting plugins temporarily
- Clear any caching plugins

### Getting Help

- 📖 **Documentation**: [GitHub Wiki](https://github.com/monero-integrations/monerowp/wiki)
- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/monero-integrations/monerowp/issues)
- 💬 **Community Support**: [Monero Integrations Community](https://monerointegrations.com/)
- 📧 **Professional Support**: Available for enterprise customers

## 📈 Changelog

### Version 4.0.0 (2025-06-27)

- **🎉 Major Update**: Complete modernization for 2025
- **✅ Compatibility**: WordPress 6.7+, WooCommerce 9.4+, PHP 8.0+
- **🔒 Security**: Enhanced input validation and XSS protection
- **⚡ Performance**: Improved API handling and database queries
- **🎨 UI/UX**: Better admin interface with clearer descriptions
- **🌍 Accessibility**: Full translation support and HPOS compatibility
- **🔧 Developer**: Modern coding standards and better documentation

### Version 3.0.5 (Previous)

- Legacy version with basic WordPress 5.7.2 compatibility
- Original feature set with limited modern platform support

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Development

- **Fork** the repository on GitHub
- **Create** a feature branch for your changes
- **Submit** a pull request with detailed description
- **Follow** WordPress coding standards

### Testing

- Test on different WordPress/WooCommerce versions
- Report bugs with detailed reproduction steps
- Verify compatibility with various themes and plugins

### Documentation

- Improve existing documentation
- Create tutorials and guides
- Translate the plugin into other languages

### Support

- Help other users in the support forums
- Share your knowledge and experiences
- Provide feedback on new features

## 📄 License

This plugin is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

**Key Points**:

- Free to use, modify, and distribute
- Commercial use permitted
- No warranty provided
- Attribution appreciated but not required

---

## 💝 Support the Project

### Donations

Support the continued development of this plugin:

**Woo Monero (This Project)**:

```
4ACjFfzbTAuLmPMZADYZFAFTNodY8BG9bAiA9Ruedoj91Wav6uNhwcSiXmx41GZgmWXNsFwAFXbdtYgyvVqtGgjZ8AWytKk
```

### Other Ways to Support

- ⭐ **Star** the repository on GitHub
- 🐛 **Report bugs** and suggest improvements
- 📖 **Contribute** documentation and tutorials
- 💬 **Share** the plugin with other Monero enthusiasts
- 🔧 **Submit** code improvements and new features

---

**Made with ❤️ by the Monero Integrations Community**

_Empowering merchants worldwide to accept Monero payments securely and privately._
