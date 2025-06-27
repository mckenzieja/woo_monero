<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled' => array(
        'title' => __('Enable / Disable', 'monero-gateway'),
        'label' => __('Enable Monero payments', 'monero-gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this payment gateway to accept Monero cryptocurrency payments.', 'monero-gateway'),
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'monero-gateway'),
        'type' => 'text',
        'desc_tip' => __('Payment method title shown to customers during checkout.', 'monero-gateway'),
        'default' => __('Monero (XMR)', 'monero-gateway'),
        'custom_attributes' => array(
            'maxlength' => 50
        )
    ),
    'description' => array(
        'title' => __('Description', 'monero-gateway'),
        'type' => 'textarea',
        'desc_tip' => __('Payment method description shown to customers during checkout.', 'monero-gateway'),
        'default' => __('Pay securely with Monero cryptocurrency. You will receive payment details after placing your order.', 'monero-gateway'),
        'css' => 'height: 80px;'
    ),
    'discount' => array(
        'title' => __('Discount/Surcharge (%)', 'monero-gateway'),
        'type' => 'number',
        'desc_tip' => __('Percentage discount (positive number) or surcharge (negative number) for Monero payments.', 'monero-gateway'),
        'description' => __('Enter a percentage (e.g., 5 for 5% discount, -2 for 2% surcharge). Leave 0 for no adjustment.', 'monero-gateway'),
        'default' => '0',
        'custom_attributes' => array(
            'step' => '0.1',
            'min' => '-100',
            'max' => '100'
        )
    ),
    'valid_time' => array(
        'title' => __('Payment Timeout (seconds)', 'monero-gateway'),
        'type' => 'number',
        'desc_tip' => __('Time limit for customers to complete payment before order expires.', 'monero-gateway'),
        'description' => __('Payment must be received within this time (600 = 10 minutes, 3600 = 1 hour).', 'monero-gateway'),
        'default' => '3600',
        'custom_attributes' => array(
            'min' => '600',
            'max' => '604800'
        )
    ),
    'confirms' => array(
        'title' => __('Required Confirmations', 'monero-gateway'),
        'type' => 'number',
        'desc_tip' => __('Number of blockchain confirmations required before payment is considered complete.', 'monero-gateway'),
        'description' => __('0 = instant (mempool), 1-10 = fast, 5+ = secure. Each confirmation takes ~2 minutes.', 'monero-gateway'),
        'default' => '5',
        'custom_attributes' => array(
            'min' => '0',
            'max' => '60'
        )
    ),
    'confirm_type' => array(
        'title' => __('Validation Method', 'monero-gateway'),
        'type' => 'select',
        'desc_tip' => __('Choose how to validate Monero payments.', 'monero-gateway'),
        'description' => __('Viewkey method is easier to setup. Wallet RPC is more secure and private.', 'monero-gateway'),
        'default' => 'viewkey',
        'options' => array(
            'viewkey' => __('Viewkey (via blockchain explorer)', 'monero-gateway'),
            'monero-wallet-rpc' => __('Monero Wallet RPC (local validation)', 'monero-gateway')
        )
    ),
    'monero_address' => array(
        'title' => __('Monero Address', 'monero-gateway'),
        'type' => 'text',
        'desc_tip' => __('Your Monero wallet address where payments will be received.', 'monero-gateway'),
        'description' => __('Must start with "4" (mainnet) or "9A" (testnet). Required for viewkey validation.', 'monero-gateway'),
        'css' => 'font-family: monospace;',
        'custom_attributes' => array(
            'maxlength' => 95
        )
    ),
    'viewkey' => array(
        'title' => __('Private Viewkey', 'monero-gateway'),
        'type' => 'password',
        'desc_tip' => __('Your wallet\'s private viewkey for transaction validation.', 'monero-gateway'),
        'description' => __('64-character hexadecimal string. Required for viewkey validation. Keep this secure!', 'monero-gateway'),
        'css' => 'font-family: monospace;',
        'custom_attributes' => array(
            'maxlength' => 64,
            'autocomplete' => 'off'
        )
    ),
    'daemon_host' => array(
        'title' => __('Wallet RPC Host', 'monero-gateway'),
        'type' => 'text',
        'desc_tip' => __('IP address or hostname where monero-wallet-rpc is running.', 'monero-gateway'),
        'description' => __('Usually 127.0.0.1 for local installation. Required for wallet RPC validation.', 'monero-gateway'),
        'default' => '127.0.0.1'
    ),
    'daemon_port' => array(
        'title' => __('Wallet RPC Port', 'monero-gateway'),
        'type' => 'number',
        'desc_tip' => __('Port number where monero-wallet-rpc is listening.', 'monero-gateway'),
        'description' => __('Default Monero RPC port. Must match your wallet RPC configuration.', 'monero-gateway'),
        'default' => '18082',
        'custom_attributes' => array(
            'min' => '1',
            'max' => '65535'
        )
    ),
    'testnet' => array(
        'title' => __('Testnet Mode', 'monero-gateway'),
        'label' => __('Enable testnet mode', 'monero-gateway'),
        'type' => 'checkbox',
        'description' => __('Use Monero testnet for development and testing. Never enable on production sites.', 'monero-gateway'),
        'default' => 'no'
    ),
    'onion_service' => array(
        'title' => __('Onion Service', 'monero-gateway'),
        'label' => __('Site accessible via Tor', 'monero-gateway'),
        'type' => 'checkbox',
        'description' => __('Check if your site is accessible via Tor hidden service (disables some SSL checks).', 'monero-gateway'),
        'default' => 'no'
    ),
    'show_qr' => array(
        'title' => __('QR Code', 'monero-gateway'),
        'label' => __('Show payment QR codes', 'monero-gateway'),
        'type' => 'checkbox',
        'description' => __('Display QR codes for easy mobile wallet payments.', 'monero-gateway'),
        'default' => 'yes'
    ),
    'use_monero_price' => array(
        'title' => __('Monero Pricing', 'monero-gateway'),
        'label' => __('Display all prices in Monero', 'monero-gateway'),
        'type' => 'checkbox',
        'description' => __('Experimental: Convert all store prices to Monero. Only use if Monero is your only payment method.', 'monero-gateway'),
        'default' => 'no'
    ),
    'use_monero_price_decimals' => array(
        'title' => __('Price Decimal Places', 'monero-gateway'),
        'type' => 'number',
        'desc_tip' => __('Number of decimal places to show when displaying Monero prices.', 'monero-gateway'),
        'description' => __('Only applies when Monero pricing is enabled. Final order amounts use full precision.', 'monero-gateway'),
        'default' => '12',
        'custom_attributes' => array(
            'min' => '0',
            'max' => '12'
        )
    ),
    'ssl_warnings' => array(
        'title' => __('SSL Warnings', 'monero-gateway'),
        'label' => __('Disable SSL warnings', 'monero-gateway'),
        'type' => 'checkbox',
        'description' => __('Only check this if you understand the security implications.', 'monero-gateway'),
        'default' => 'no'
    )
);
