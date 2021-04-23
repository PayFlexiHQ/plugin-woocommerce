<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Payflexi_Flexible_Checkout_Gateway extends WC_Payment_Gateway {
    /**
     * Class constructor, more about it in Step 3
    */
    public function __construct() {
        $this->id = 'payflexi-flexible-checkout';
        $this->method_title = 'Payflexi Flexible Checkout';
		$this->method_description = sprintf( 'Payflexi Flexible Checkout allow your customers to pay in installment and also with one-click checkout <a href="%1$s" target="_blank">Sign up</a> for a Payflexi account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'https://payflexi.co', 'https://merchant.payflexi.co/settings' );
        $this->has_fields = true;

        $this->supports = array(
			'products',
			'refunds'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings
        $this->init_settings();

        // Get setting values
        $this->title  = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
		$this->enabled  = $this->get_option( 'enabled' ) != '' ? $this->get_option( 'enabled' ) : 'no';
		$this->env      = $this->get_option( 'env' ) != '' ? $this->get_option( 'env' ) : 'test';
        $this->testmode = 'test' === $this->env;
            
        if ( $this->testmode ) {
			$this->description .= ' ' . sprintf( __( 'TEST MODE ENABLED. You can only use testing accounts. See the <a href="%s" target="_blank">Payflexi Testing Guide</a> for more details.', 'payflexi-flexible-checkout' ), 'https://developers.payflexi.co/' );
			$this->description  = trim( $this->description );
		}
		
		$this->public_key  = $this->get_option( $this->env . '_api_public_key' );
        $this->secret_key  = $this->get_option( $this->env . '_api_secret_key' ); 
        
		// Hooks
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles_scripts' ) );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

        // Payment listener/API hook
        add_action('woocommerce_api_payflexi_flexible_checkout_gateway', array( $this, 'verify_payflexi_transaction'));

        // Webhook listener/API hook
        add_action('woocommerce_api_payflexi_flexible_checkout_webhook', array( $this, 'process_webhooks' ) );
    }

    /**
     * Display payflexi payment icon
     */
    public function get_icon() {
		$icon  = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/payflexi-wc.png' , PAYFLEXI_FLEXIBLE_CHECKOUT_FILE ) ) . '" alt="PayFlexi Checkout" />';
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
    }

    /**
     * Check if Payflexi merchant details is filled
     */
    public function admin_notices() {
        if ( $this->enabled == 'no' ) {
            return;
		}
        // Check required fields
        if ( ! ( $this->public_key && $this->secret_key ) ) {
            echo '<div class="error"><p>' . sprintf( 'Please enter your Payflexi merchant details <a href="%s">here</a> to be able to use the Payflexi WooCommerce plugin.', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payflexi-flexible-checkout' ) ) . '</p></div>';
            return;
        }
	}
	/**
     * Admin Panel Options
    */
    public function admin_options() {

        ?>
        <h2>PayFlexi Flexible Checkout
        <?php
            if ( function_exists( 'wc_back_link' ) ) {
                wc_back_link( 'Return to payments', admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
            }
        ?>
        </h2>

        <h4>Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="https://merchant.payflexi.co/settings" target="_blank" rel="noopener noreferrer">here</a> to the URL below<strong style="color: red"><pre><code><?php echo WC()->api_request_url( 'Payflexi_Flexible_Checkout_Webhook' ); ?></code></pre></strong></h4>

        <?php
			
			echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
    }
	 /**
     * Load admin scripts
     */
    public function admin_enqueue_styles_scripts() {

        if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
            return;
        }

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
		$payflexi_flexible_checkout_admin_params = array(
			'plugin_url'    => PAYFLEXI_FLEXIBLE_CHECKOUT_URL
		);

		wp_enqueue_script( 'payflexi_flexible_checkout_admin', PAYFLEXI_FLEXIBLE_CHECKOUT_ASSETS_URL . '/js/pf-checkout-admin' . $suffix . '.js', array( 'jquery' ), PAYFLEXI_FLEXIBLE_CHECKOUT_VERSION, true );	
		wp_localize_script( 'payflexi_flexible_checkout_admin', 'payflexi_flexible_checkout_admin_params', $payflexi_flexible_checkout_admin_params );
    }
    /**
     * Check if this gateway is enabled
     */
    public function is_available() {

        if ( $this->enabled == "yes" ) {

            if ( ! ( $this->public_key && $this->secret_key ) ) {

                return false;

            }

            return true;

        }
        return false;

    }
    /**
     * Initialise Gateway Settings Form Fields
    */
    public function init_form_fields() {
		$this->form_fields = include 'admin/gateway-settings.php';
    }
    /**
     * Payment form on checkout page
     */
    public function payment_fields() {

        if ( $this->description ) {
            echo wpautop( wptexturize( $this->description ) );
        }

        if ( ! is_ssl() ){
            return;
        }

	}
    /**
     * Process the payment
     */
    public function process_payment( $order_id ) {
        // we need it to get any order detailes
        $order = wc_get_order( $order_id );
        /*
        * Array with parameters for API interaction
        */
        return array(
           'result'   => 'success',
           'redirect' => $order->get_checkout_payment_url( true )
        );
    }
    /**
     * Displays the payment page
     */
    public function receipt_page( $order_id ) {
        $order = wc_get_order( $order_id );
        echo '<p>Thank you for your order, please click the button below to pay with Payflexi.</p>';

        echo '<div id="payflexi_flexible_checkout_form"><form id="order_review" method="post" action="'. WC()->api_request_url( 'Payflexi_Flexible_Checkout_Gateway' ) .'"></form><button class="button alt" id="payflexi-flexible-checkout-payment-button">Pay with PayFlexi</button> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">Cancel order &amp; restore cart</a></div>
        ';
    }

    /**
     * Verify Payflexi payment
     */
    public function verify_payflexi_transaction() {

        @ob_clean();

        if (isset($_REQUEST['payflexi_txnref'])){

            $payflexi_url = 'https://api.payflexi.co/merchants/transactions/' . sanitize_text_field( $_REQUEST['payflexi_txnref']);
           
            $headers = array(
                'Authorization' => 'Bearer ' . $this->secret_key
            );

            $args = array(
                'sslverify' => false, //Set to true on production
                'headers'   => $headers,
                'timeout'   => 60
            );
      
            $request = wp_remote_get($payflexi_url, $args);

            if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {
                $payflexi_response = json_decode( wp_remote_retrieve_body( $request ));
          
                if (!$payflexi_response->errors) {
                    $order_details  = explode( '_', $payflexi_response->data->reference );
                    $order_id  = (int) $order_details[0];
                    $order  = wc_get_order( $order_id );
                
                    if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {
                        wp_redirect( $this->get_return_url( $order ) );
                        exit;
                    }

                    $order_total        = $order->get_total();
                    $order_currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
                    $currency_symbol    = get_woocommerce_currency_symbol( $order_currency );
                    $order_amount       = $payflexi_response->data->amount;
                    $amount_paid        = $payflexi_response->data->txn_amount;
                    $payflexi_ref       = $payflexi_response->data->reference;
                    $payment_currency   = strtoupper( $payflexi_response->data->currency);
                    $gateway_symbol     = get_woocommerce_currency_symbol($payment_currency);

                    // check if the amount paid is equal to the order amount.
					if ($amount_paid < $order_total ) {
                        add_post_meta( $order_id, '_woo_payflexi_transaction_id', $payflexi_ref, true );
                        update_post_meta( $order_id, '_woo_payflexi_order_amount', $order_amount);
                        update_post_meta( $order_id, '_woo_payflexi_installment_amount_paid', $amount_paid);
                        $order->update_status( 'on-hold', '' );
						$admin_order_note = sprintf( __( '<strong>New Installment Order</strong>%1$sThis order is partially paid using PayFlexi Flexible Checkout.%2$sAmount Paid was <strong>%3$s (%4$s)</strong> while the total order amount is <strong>%5$s (%6$s)</strong>%7$s<strong>PayFlexi Transaction Reference:</strong> %8$s', 'payflexi-flexible-checkout-for-woocommerce' ), '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $payflexi_ref );
						$order->add_order_note( $admin_order_note );

						function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                        wc_empty_cart();
                    }else{
                        $order->payment_complete( $payflexi_ref );
                        $order->add_order_note( sprintf( 'Payment via PayFlexi Flexible Checkout successful (Transaction Reference: %s)', $payflexi_ref ) );
                        function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();
                        wc_empty_cart();
                    }
                } else {

                    $order_details  = explode( '_', $_REQUEST['payflexi_txnref'] );

                    $order_id       = (int) $order_details[0];

                    $order          = wc_get_order( $order_id );

                    $order->update_status( 'failed', 'Payment was declined by Payflexi.' );

                }

            }

            wp_redirect( $this->get_return_url( $order ) );

            exit;
        }

        wp_redirect( wc_get_page_permalink( 'cart' ) );

        exit;

    }



    /**
     * Process Webhook
     */
    public function process_webhooks() {
   
        if ( ( strtoupper( $_SERVER['REQUEST_METHOD'] ) != 'POST' ) || ! array_key_exists('HTTP_X_PAYFLEXI_SIGNATURE', $_SERVER) ) {
            exit;
        }

        $json = file_get_contents( "php://input" );

        // validate event do all at once to avoid timing attack
        if ( $_SERVER['HTTP_X_PAYFLEXI_SIGNATURE'] !== hash_hmac( 'sha512', $json, $this->secret_key ) ) {
            exit;
        }

        $event = json_decode( $json );

        ray($event);

        if ('transaction.approved' == $event->event && 'approved' == $event->data->status) {
            http_response_code(200);
            $order_details = explode( '_', $event->data->initial_reference);
            $order_id = (int) $order_details[0];
            $order = wc_get_order($order_id);

            if ( in_array( $order->get_status(), array( 'processing', 'completed' ) ) ) {
                exit;
            }

            $order_currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
            $currency_symbol    = get_woocommerce_currency_symbol( $order_currency );
            $order_total        = $order->get_total();
            $order_amount       = get_post_meta($order_id, '_woo_payflexi_order_amount', true);
            $order_amount       = $order_amount ? $order_amount : $event->data->amount;
            $amount_paid        = $event->data->txn_amount;
            
            $saved_txn_ref      = get_post_meta( $order_id, '_woo_payflexi_transaction_id', true );
            $txn_ref            = $event->data->reference;
            $initial_txn_ref    = $event->data->initial_reference;

            $installment_amount_paid = get_post_meta($order_id, '_woo_payflexi_installment_amount_paid', true );

            if ($amount_paid < $order_amount ) {
                if($txn_ref === $initial_txn_ref && (!$saved_txn_ref || empty($saved_txn_ref))){
                    add_post_meta( $order_id, '_woo_payflexi_transaction_id', $txn_ref, true );
                    update_post_meta( $order_id, '_woo_payflexi_order_amount', $order_amount);
                    update_post_meta( $order_id, '_woo_payflexi_installment_amount_paid', $amount_paid);
                    $order->update_status('on-hold', '');
                    $admin_order_note = sprintf( __( '<strong>New Installment Order</strong>%1$sThis order is partial paid using PayFlexi Flexible Checkout.%2$sAmount Paid was <strong>%3$s (%4$s)</strong> while the total order amount is <strong>%5$s (%6$s)</strong>%7$s<strong>PayFlexi Transaction Reference:</strong> %8$s', 'payflexi-flexible-checkout-for-woocommerce' ), '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_amount, '<br />', $txn_ref );
                    $order->add_order_note( $admin_order_note );
                    wc_empty_cart();
                }
                if($txn_ref !== $initial_txn_ref && (!$saved_txn_ref || !empty($saved_txn_ref))){
                    $total_installment_amount_paid = $installment_amount_paid + $amount_paid;
                    if($total_installment_amount_paid >= $order_amount){
                        update_post_meta($order_id, '_woo_payflexi_installment_amount_paid', $total_installment_amount_paid);
                        $order->payment_complete( $txn_ref );
                        $order->add_order_note( sprintf( 'PayFlexi Installment Payment Completed (Transaction Reference: %s)', $txn_ref ) );
                    }else{
                        update_post_meta($order_id, '_woo_payflexi_installment_amount_paid', $total_installment_amount_paid);
                        $order->update_status('on-hold', '');
                        $admin_order_note = sprintf( __( '%1$sThis order is currently partially paid using PayFlexi Flexible Checkout.%2$sAmount Paid was <strong>%3$s (%4$s)</strong> while the total order amount is <strong>%5$s (%6$s)</strong>%7$s<strong>PayFlexi Transaction Reference:</strong> %8$s', 'payflexi-flexible-checkout-for-woocommerce' ), '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_amount, '<br />', $txn_ref );
                        $order->add_order_note( $admin_order_note );
                    }
                }
            }else{
                $order->payment_complete( $txn_ref );
                $order->add_order_note( sprintf( 'Payment via PayFlexi Flexible Checkout successful (Transaction Reference: %s)', $txn_ref ) );
                wc_empty_cart();
            }
        }

        exit;
	}
    /**
     * Checks if WC version is less than passed in version.
     *
     * @since 5.6.0
     * @param string $version Version to check against.
     * @return bool
     */
    public function is_wc_lt( $version ) {
        return version_compare( WC_VERSION, $version, '<' );
    }


}
