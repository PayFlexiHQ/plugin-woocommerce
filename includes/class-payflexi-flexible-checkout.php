<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class PayFlexi_Flexible_Checkout {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.1.0
     * @access   protected
     * @var      Payflexi_Checkout_Checkout_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;
    /**
     * The current version of the plugin.
     *
     * @since    1.1.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    public $version;
    /**
     * Plugins Path
     *
     * @since    1.1.0
     * @access   protected
     */
    public $ameliaBookingPluginPath;
    public $rnbRentalBookingPluginPath;
    public $eventOnWordPressEventPluginPath;
    public $calendaristaPluginPath;
    public $wooCommerceAppointmentsPluginPath;
    public $bookedAppointmentPluginPath;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
		$this->version = '1.1.0';
        $this->ameliaBookingPluginPath = 'ameliabooking/ameliabooking.php';
        $this->rnbRentalBookingPluginPath = 'woocommerce-rental-and-booking/redq-rental-and-bookings.php';
        $this->eventOnWordPressEventPluginPath = 'eventON/eventon.php';
        $this->calendaristaPluginPath = 'calendarista/Calendarista.php';
        $this->wooCommerceAppointmentsPluginPath = 'woocommerce-appointments/woocommerce-appointments.php';
        $this->bookedAppointmentPluginPath = 'booked/booked.php';
        $this->load_dependencies();
		$this->woo_gateway_hooks();
		$prefix = is_network_admin() ? 'network_admin_' : '';
		add_filter("{$prefix}plugin_action_links_" . PAYFLEXI_FLEXIBLE_CHECKOUT_INIT, array($this, 'woocommerce_payflexi_flexible_checkout_plugin_action_links'), 10, 4);
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Payflexi_Flexible_Checkout_Loader. Orchestrates the hooks of the plugin.
     * - Payflexi_Flexible_Checkout_Admin. Defines all hooks for the admin area.
     * - Payflexi_Flexible_Checkout_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.1.0
     * @access   private
     */
    private function load_dependencies() {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payflexi-flexible-checkout-loader.php';

        if (class_exists('WC_Payment_Gateway')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payflexi-flexible-checkout-gateway.php';
		}

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payflexi-flexible-checkout-helper.php';

		$this->loader = new Payflexi_Flexible_Checkout_Loader();

		$this->payflexi_checkout_helper = new PayFlexi_Flexible_Checkout_Helper();
    }

    private function woo_gateway_hooks() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', array($this, 'payflexi_flexible_checkout_woocommerce_admin_notice'));
			return;
		} 
        add_action('admin_notices', array($this, 'payflexi_flexible_checkout_testmode_notice'));
        
        if ($this->payflexi_checkout_helper->payflexi_checkout_get_option('popup_information_enabled') == "yes") {
            add_action('woocommerce_before_add_to_cart_form', array($this, 'print_payflexi_info_for_product_detail_page'), 22);
        }

        // Order statuses and filter
        add_filter('wc_order_statuses', array($this, 'add_installment_paid_to_order_statuses'));
        add_action('init', array($this, 'register_order_status'));
        add_filter('manage_edit-shop_order_columns', array($this, 'add_custom_balance_and_paid_column'));
        add_action('manage_shop_order_posts_custom_column' , array($this, 'custom_orders_list_column_content'));

        add_filter('woocommerce_payment_gateways', array($this, 'woocommerce_add_payflexi_flexible_checkout_gateway'), 10, 1);

        add_action('wp_enqueue_scripts', array($this, 'init_website_assets'));
    }
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Payflexi_Flexible_Checkout_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

   	/**
    * Add Payflexi Gateway to WC
    **/
    public function woocommerce_add_payflexi_flexible_checkout_gateway($methods) {
		$methods[] = 'Payflexi_Flexible_Checkout_Gateway';
        return $methods;
	}
	/**
	* Note: Hooked onto the "wp_enqueue_scripts" Action to avoid the Wordpress Notice warnings
	*
	* @since	1.0.0
	* @see		self::__construct()		For hook attachment.
	*/
    public function init_website_assets() {

        $payflexi_flexible_checkout_params = array(
            'key'  => $this->payflexi_checkout_helper->payflexi_flexible_checkout_public_key(),
            'gateway'  => $this->payflexi_checkout_helper->payflexi_flexible_checkout_gateway()
        );

        if (is_product()) {
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            wp_enqueue_script( 'payflexi_flexible_checkout_sdk', 'https://payflexi.co/js/v1/global-payflexi.js', array(), null, false);
            wp_enqueue_script(
                'payflexi_flexible_checkout_frontend',
                PAYFLEXI_FLEXIBLE_CHECKOUT_ASSETS_URL . '/js/pf-checkout-frontend' . $suffix . '.js',
                array(
                    'jquery',
                    'payflexi_flexible_checkout_sdk',
                    ),
                PAYFLEXI_FLEXIBLE_CHECKOUT_VERSION,
                true
            );
            wp_localize_script('payflexi_flexible_checkout_frontend', 'payflexi_flexible_checkout_params', $payflexi_flexible_checkout_params );
        }

        if (is_cart()) {
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            wp_enqueue_script( 'payflexi_flexible_checkout_sdk', 'https://payflexi.co/js/v1/global-payflexi.js', array(), null, false);
            wp_enqueue_script(
                'payflexi_flexible_checkout_frontend',
                PAYFLEXI_FLEXIBLE_CHECKOUT_ASSETS_URL . '/js/pf-checkout-frontend' . $suffix . '.js',
                array(
                    'jquery',
                    'payflexi_flexible_checkout_sdk',
                    ),
                PAYFLEXI_FLEXIBLE_CHECKOUT_VERSION,
                true
            );
            wp_localize_script('payflexi_flexible_checkout_frontend', 'payflexi_flexible_checkout_params', $payflexi_flexible_checkout_params );
        }

        if (is_checkout()) {
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            wp_enqueue_script( 'payflexi_flexible_checkout_sdk', 'https://payflexi.co/js/v1/global-payflexi.js', array(), null, false);
            wp_enqueue_script(
                'payflexi_flexible_checkout_frontend',
                PAYFLEXI_FLEXIBLE_CHECKOUT_ASSETS_URL . '/js/pf-checkout-frontend' . $suffix . '.js',
                array(
                    'jquery',
                    'payflexi_flexible_checkout_sdk',
                    ),
                PAYFLEXI_FLEXIBLE_CHECKOUT_VERSION,
                true
            );
            wp_localize_script('payflexi_flexible_checkout_frontend', 'payflexi_flexible_checkout_params', $payflexi_flexible_checkout_params );
        }

        if (is_checkout_pay_page()) {

            $order_key = urldecode( $_GET['key'] );
            $order_id  = absint( get_query_var( 'order-pay' ) );

            $order = wc_get_order( $order_id );
    
            $payment_method = method_exists( $order, 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;
    
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            wp_enqueue_script( 'payflexi_flexible_checkout_sdk', 'https://payflexi.co/js/v1/global-payflexi.js', array(), null, false);
            wp_enqueue_script(
                'payflexi_flexible_checkout_frontend',
                PAYFLEXI_FLEXIBLE_CHECKOUT_ASSETS_URL . '/js/pf-checkout-frontend' . $suffix . '.js',
                array(
                    'jquery',
                    'payflexi_flexible_checkout_sdk',
                    ),
                PAYFLEXI_FLEXIBLE_CHECKOUT_VERSION,
                true
            );            
        
            if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

                    //Travelo Hotel Booking Theme
                    if('Travelo' == get_option( 'template' ) ) {
                        $traveloBookingName = $cart_item['data']->name;
                        $traveloBookingMeta = isset($traveloBookingName) ? $traveloBookingName : null;
                        if( $traveloBookingMeta){
                            $pattern = "#\d{2}/\d{2}/\d{4}#";
                            preg_match_all($pattern, $traveloBookingMeta, $matches);
                            $bookingDate = $matches[0][0];
                            $booking_date = \DateTime::createFromFormat('d/m/Y', $bookingDate)->format('Y-m-d');
                        }
                    }

                    //Adventure Tour Theme
                    if('adventure-tours' == get_option( 'template' ) ) {
                        $booking_date = isset($cart_item['date']) ? $cart_item['date'] : null;
                    }

                    //Traveler Booking Theme
                    if('traveler' == get_option( 'template' ) ) {
                        $travelerBookingMeta = isset($cart_item['st_booking_data']) ? $cart_item['st_booking_data'] : null;
                        if($travelerBookingMeta){
                            $booking_date = date('Y-m-d', strtotime($travelerBookingMeta['check_in']));
                        }
                    }

                    if ( is_plugin_active( $this->bookedAppointmentPluginPath) ) {
                        $bookedPluginMeta = $cart_item['booked_wc_appointment_timerange'];
                        $bookedPluginBookingMeta = isset($bookedPluginMeta) ? $bookedPluginMeta : null;
                        if($bookedPluginBookingMeta){
                            $pattern = "/(\w+) (\d{1,2}), (\d{4})/";
                            preg_match_all($pattern, $bookedPluginBookingMeta, $matches);
                            $bookingDate = $matches[0][0];
                            $booking_date = \DateTime::createFromFormat('F j, Y', $bookingDate)->format('Y-m-d');
                        }
                    }

                    if ( is_plugin_active( $this->wooCommerceAppointmentsPluginPath) ) {
                        $booking_date = isset($cart_item['appointment']['_date']) ? $cart_item['appointment']['_date'] : null;
                    }

                    if ( is_plugin_active( $this->calendaristaPluginPath ) ) {
                        $calendaristaMeta = unserialize(stripslashes($cart_item['_calendarista_summary']));
                        $calendaristaBookingMeta = isset($calendaristaMeta) ? $calendaristaMeta : null;
                        if($calendaristaBookingMeta){
                            $pattern = "/(\d{2}\/\d{2}\/\d{4})/";
                            preg_match_all($pattern, $calendaristaBookingMeta, $matches);
                            $bookingDate = $matches[0][0];
                            $booking_date = \DateTime::createFromFormat('d/m/Y', $bookingDate)->format('Y-m-d');
                        }
                    }
    
                    if ( is_plugin_active( $this->ameliaBookingPluginPath ) ) {
                        $ameliaBookingMeta = isset($cart_item['ameliabooking']) ? $cart_item['ameliabooking'] : null;
                        if($ameliaBookingMeta){
                            $booking_date = date('Y-m-d', strtotime($ameliaBookingMeta['bookingStart']));
                        }
                    }

                    if ( is_plugin_active( $this->rnbRentalBookingPluginPath) ) {
                        $rnbWocommerceRentalBookingMeta = isset($cart_item['rental_data']) ? $cart_item['rental_data'] : null;
                        if($rnbWocommerceRentalBookingMeta){
                            $booking_date = $rnbWocommerceRentalBookingMeta['pickup_date'];
                        }
                    }
                }

                $email = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
                $first_name = method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
                $last_name  = method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;
                $amount = $order->get_total();
                $txnref = $order_id . '_' .time();
    
                $line_items = $order->get_items();
                $products = '';

                foreach ( $line_items as $item_id => $item ) {

                    if('goto' == get_option( 'template' ) ) {
                        $gotoBookingDate = wc_get_order_item_meta( $item_id, 'booking_date', true);
                        $gotoBookingMeta = isset($gotoBookingDate) ? $gotoBookingDate : null;
                        if($gotoBookingMeta){
                            $booking_date = date('Y-m-d', strtotime($gotoBookingMeta));
                        }
                    }

                    if ( is_plugin_active( $this->eventOnWordPressEventPluginPath ) ) {
                        $eventOnEventTimeMeta = wc_get_order_item_meta($item_id, 'Event-Time', true);
                        $eventOnEventTimeMeta = isset($eventOnEventTimeMeta) ? $eventOnEventTimeMeta : null;
                        if($eventOnEventTimeMeta){
                            $eventOnEventDates = explode('-', $eventOnEventTimeMeta);
                            $eventOnEventDate = trim($eventOnEventDates[0]);
                            $booking_date = date('Y-m-d', strtotime($eventOnEventDate));
                        }
                    }

                    $name     = $item['name'];
                    $quantity = $item['qty'];
                    $products .= $name . ' (Qty: ' . $quantity . ')';
                    $products .= ' | ';
                }

                $booking_date = isset($booking_date) ? $booking_date : null;

                $products = rtrim( $products, ' | ' );
    
                $the_order_id   = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
                $the_order_key  = method_exists( $order, 'get_order_key' ) ? $order->get_order_key() : $order->order_key;
    
                if ( $the_order_id == $order_id && $the_order_key == $order_key ) {
                    $payflexi_flexible_checkout_params['email']  = $email;
                    $payflexi_flexible_checkout_params['name'] = $first_name . ' ' . $last_name;
                    $payflexi_flexible_checkout_params['amount'] = $amount;
                    $payflexi_flexible_checkout_params['txnref']  = $txnref;
                    $payflexi_flexible_checkout_params['currency'] = get_woocommerce_currency();
                    $payflexi_flexible_checkout_params['products'] = $products;
                    $payflexi_flexible_checkout_params['booking_date']  = $booking_date;
                }

                $payflexi_flexible_checkout_params['meta_order_id'] = $order_id;
                $first_name = method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
                $last_name  = method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;
                $payflexi_flexible_checkout_params['meta_name'] = $first_name . ' ' . $last_name;
                $payflexi_flexible_checkout_params['meta_email'] = $email;
                $billing_phone = method_exists( $order, 'get_billing_phone' ) ? $order->get_billing_phone() : $order->billing_phone;
                $payflexi_flexible_checkout_params['meta_phone'] = $billing_phone;
                $billing_address = $order->get_formatted_billing_address();
                $billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );
                $payflexi_flexible_checkout_params['meta_billing_address'] = $billing_address;
                $shipping_address = $order->get_formatted_shipping_address();
                $shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );
                if ( empty( $shipping_address ) ) {
                    $billing_address = $order->get_formatted_billing_address();
                    $billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );
                    $shipping_address = $billing_address;
                }
                $payflexi_flexible_checkout_params['meta_shipping_address'] = $shipping_address;

                update_post_meta( $order_id, '_payflexi_txn_ref', $txnref );
            }
            // in most payment processors you have to use PUBLIC KEY to obtain a token
            wp_localize_script('payflexi_flexible_checkout_frontend', 'payflexi_flexible_checkout_params', $payflexi_flexible_checkout_params );
        }

        return true;
    }
    /**
	* Print a paragraph of Payflexi info onto the individual product pages if enabled and the product is valid.
	*
	 * Note:	Hooked onto the "woocommerce_single_product_summary" Action.
	*/
	public function print_payflexi_info_for_product_detail_page() {
		?>
		<div class="what-is-payflexi-container">
			<a href="#" id="payflexi_popup_info_link">
				<?php echo $this->payflexi_checkout_helper->payflexi_checkout_get_option('popup_trigger_text') ?>
			</a>
		</div>
	<?php }
	 /**
    * Add Settings link to the plugin entry in the plugins menu
    **/
    public function woocommerce_payflexi_flexible_checkout_plugin_action_links( $links ) {
        $settings_link = array(
            'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payflexi-flexible-checkout' ) . '" title="View Payflexi Flexible Checkout Settings">Settings</a>'
        );
        return array_merge( $links, $settings_link );
    }
	/**
	* Print an admin notice if woocommerce is deactivated
	* @return void
	* @use    admin_notices hooks
	 */
	public function payflexi_flexible_checkout_woocommerce_admin_notice() { 
		echo '<div class="error"><p><strong>' . sprintf( __( 'Payflexi requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'payflexi-flexible-checkout' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
	} 
	/**
	* Display the test mode notice on admin end
	**/
	public function payflexi_flexible_checkout_testmode_notice(){
		$payflexi_flexible_checkout_settings = get_option( 'woocommerce_payflexi-flexible-checkout_settings' );
		$test_mode  = isset( $payflexi_flexible_checkout_settings['env'] ) ? $payflexi_flexible_checkout_settings['env'] : '';
		if ( 'test' == $test_mode ) {
		?>
			<div class="update-nag">
				Payflexi Flexible Checkout test mode is still enabled, Click <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=payflexi-flexible-checkout' ) ?>">here</a> to disable it when you want to start accepting live payment on your site.
			</div>
		<?php
		}
    }
    /**
    * @brief Register a custom order status
    *
    * @return void
    * @since 1.3
    *
    */
    public function register_order_status()
    {
        register_post_status('wc-installment-paid', array(
            'label' => _x('Installment Paid', 'Order status', 'payflexi-flexible-checkout-for-woocommerce'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Installment Paid <span class="count">(%s)</span>',
                'Installment Paid <span class="count">(%s)</span>', 'payflexi-flexible-checkout-for-woocommerce')
        ));

    }
     /**
     * @brief Add the new 'Installment paid' status to orders
     *
     * @return array
     */
    public function add_installment_paid_to_order_statuses($order_statuses)
    {
        $new_statuses = array();
        foreach ($order_statuses as $key => $value) {
            $new_statuses[$key] = $value;
            if ('wc-pending' === $key) {
                $new_statuses['wc-installment-paid'] = __('Installment Payment', 'payflexi-flexible-checkout-for-woocommerce');
            }
        }
        return $new_statuses;
    }
    // Adding custom status to admin order list bulk actions dropdown
    public function custom_dropdown_bulk_actions_installment_paid( $actions ) 
    {
        $new_actions = array();
        // Add new order status before processing
        foreach ($actions as $key => $action) {
            $new_actions[$key] = $action;
            if ('mark_pending' === $key) {
                $actions['mark_installment-paid'] = __( 'Installment Payment', 'woocommerce' );
            }
        }

        return $actions;
    }
    // ADDING 2 NEW COLUMNS WITH THEIR TITLES (keeping "Actions" columns at the end)
    public function add_custom_balance_and_paid_column($columns)
    {
        $reordered_columns = array();
        foreach( $columns as $key => $column){
            $reordered_columns[$key] = $column;
            if( $key ==  'order_status' ){
                $reordered_columns['order-amount-paid'] = __( 'Installment Paid','payflexi-flexible-checkout-for-woocommerce');
                $reordered_columns['order-amount-balance'] = __( 'Balance Amount','payflexi-flexible-checkout-for-woocommerce');
            }
        }
        return $reordered_columns;
    }
    
    // Adding custom fields meta data for each new column
    public function custom_orders_list_column_content($column)
    {
        global $post;
        $post_type = get_post_type($post->ID);
        if ($post_type === 'shop_order') {
            $order = wc_get_order( $post->ID );
            if (!$order) return;
            $payment_method = $order->get_payment_method(); 
            if($payment_method === 'payflexi-flexible-checkout'){
                $amount_paid = get_post_meta($order->get_id(), '_woo_payflexi_installment_amount_paid', true );
                $installment_amount_paid = $amount_paid ? $amount_paid : 0;
                $order_total = $order->get_total();
                $balance_amount = $amount_paid ? number_format(($order_total - $installment_amount_paid), 2) : 0;
                $order_currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
                $currency_symbol    = get_woocommerce_currency_symbol( $order_currency );
                switch ($column)
                {
                    case 'order-amount-paid' :
                        echo '<strong>'. wc_price($installment_amount_paid, $currency_symbol)  .'</strong>';
                        break;

                    case 'order-amount-balance' :
                        echo '<strong>'.  wc_price($balance_amount, $currency_symbol)  .'</strong>';
                        break;
                }
            }
        }
    }
	

}