<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_fields = array(
    'enabled' => array(
        'title'       => 'Enable/Disable',
        'label'       => 'Enable Payflexi Flexible Checkout',
        'type'        => 'checkbox',
        'description' => 'Enable Payflexi as a payment option on the checkout page.',
        'default'     => 'no',
        'desc_tip'    => true
    ),
    'title' => array(
        'title'         => 'Title',
        'type'          => 'text',
        'description'   => 'This controls the payment method title which the user sees during checkout.',
        'default'       => 'PayFlexi Flexible Checkout',
        'desc_tip'      => true,
    ),
    'env' => array(
        'title'   => 'Environment',
        'type'    => 'select',
        'label'   => 'Choose whether to activate the plugin in live or test mode',
        'options' => array(
            'live'    => 'Live',
            'test' => 'Test'
        ),
        'default' => 'live',
    ),
	'test_api_credentials' => array(
		'title'       => 'Enter your test credentials here and connect your Payflexi account',
		'type'        => 'title',
		'class'       => 'test',
		'description' => 'You can connect an existing account or create a new one',
	),
    'test_api_secret_key' => array(
        'title'       => 'Test Secret Key',
        'type'        => 'text'
    ),
    'test_api_public_key' => array(
        'title'       => 'Test Public Key',
        'type'        => 'text'
    ),
    'live_api_credentials' => array(
		'title'       => 'Enter your live credentials here and connect your Payflexi account',
		'type'        => 'title',
		'class'       => 'live',
		'description' => 'You can connect an existing account or create a new one',
	),
    'live_api_secret_key' => array(
        'title'       => 'Live Secret Key',
        'type'        => 'text'
    ),
    'live_api_public_key' => array(
        'title'       => 'Live Public Key',
        'type'        => 'text'
    ),
    'button_payflexi'  => array(
        'title' => 'Choose where to show Payflexi Flexible Checkout option',
        'type'  => 'title',
    ),
    'on_checkout'  => array(
        'title'       => 'Enable PayFlexi on Checkout page',
        'type'        => 'checkbox',
        'label'       => 'Show Payflexi Flexible Checkout on WooCommerce checkout page.',
        'description' => 'Show Payflexi Flexible Checkout like any other regular WooCommerce gateway on checkout page.',
        'default'     => 'yes',
    ),
    'description'     => array(
        'title'       => 'Description',
        'type'        => 'text',
        'default'     => 'Pay via Payflexi; checkout with one-click or pay in instalments.',
        'description' => 'This is the description that the customer will see on checkout page',
        'desc_tip'    => true,
    ),
    // 'on_cart_page'  => array(
    //     'title'       => __( 'Enable PayFlexi Flexible Checkout in cart', 'payflexi-flexible-checkout-for-woocommerce' ),
    //     'label'       => __( 'Enable PayFlexi Flexible Checkout in cart', 'payflexi-flexible-checkout-for-woocommerce' ),
    //     'type'        => 'checkbox',
    //     'description' => __('Enable this to allow customers to shop using PayFlexi Flexible Checkout directly from the cart with no login or address input needed', 'payflexi-flexible-checkout-for-woocommerce') . '.<br>' .
    //     __('Please note that for PayFlexi Flexible Checkout, shipping must be calculated in a callback from the PayFlexi API, without any knowledge of the customer. This means that PayFlexi Flexible Checkout may not be compatible with all Shipping plugins or setup if your product is a physical product that requires shipping. You should test that your setup works if you intend to provide this feature.', 'payflexi-flexible-checkout-for-woocommerce'),
    //     'default'     => 'yes',
    // ),
    // 'on_single_product_page'  => array(
    //     'title'       => __( 'Enable PayFlexi Flexible Checkout for single products', 'payflexi-flexible-checkout-for-woocommerce'),
    //     'label'       => __( 'Enable PayFlexi Flexible Checkout for single products', 'payflexi-flexible-checkout-for-woocommerce'),
    //     'type'        => 'select',
    //     'options' => array(
    //         'none' => __('No products','payflexi-flexible-checkout-for-woocommerce'),
    //         'some' => __('Some products', 'payflexi-flexible-checkout-for-woocommerce'),
    //         'all' => __('All products','payflexi-flexible-checkout-for-woocommerce')
    //     ), 
    //     'description' => __('Enable this to allow customers to buy a product using PayFlexi Flexible Checkout directly from the product page. If you choose \'some\', you must enable this on the relevant products', 'payflexi-flexible-checkout-for-woocommerce'),
    //     'default'     => 'all',
    // ),
    // 'on_catalog_page' => array(
    //     'title'       => __( 'Add \'Buy now\' button on catalog pages too', 'payflexi-flexible-checkout-for-woocommerce' ),
    //     'label'       => __( 'Add the button for all relevant products on catalog pages', 'payflexi-flexible-checkout-for-woocommerce' ),
    //     'type'        => 'checkbox',
    //     'description' => __('If PayFlexi Flexible Checkout is enabled for a product, add the \'Buy now\' button to catalog pages too', 'payflexi-flexible-checkout-for-woocommerce'),
    //     'default'     => 'no',
    // ),
    // 'gateway_send_image' => array(
    //     'title' => __('Send product images to Payflexi', 'payflexi-flexible-checkout-for-woocommerce'),
    //     'type' => 'checkbox',
    //     'default' => 'yes',
    //     'description' => __('Send product thumbnails to Payflexi to display in Payflexi checkout', 'payflexi-flexible-checkout-for-woocommerce'),
    //     'desc_tip' => true
    // ),
    'popup_information_options'   => array(
        'title' => 'Popup Information options',
        'type'  => 'title',
    ),
    'popup_information_enabled' => array(
        'title' => __('Payflexi Popup information enabled', 'payflexi-flexible-checkout-for-woocommerce'),
        'label' => __('Enable Payflexi popup information for your product page.', 'payflexi-flexible-checkout-for-woocommerce'),
        'type' => 'checkbox',
        'default' => 'yes'
    ),
    'popup_trigger_text' => array(
        'title' => __( 'Popup trigger text', 'payflexi-flexible-checkout-for-woocommerce' ),
        'type' => 'text',
        'default'=>'Pay in flexible instalment - Learn more'
    )
 
);

return $form_fields;
