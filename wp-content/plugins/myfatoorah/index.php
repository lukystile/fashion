<?php

/*
  Plugin Name: WooCommerce Myfatoorah Payment Gateway
  Plugin URI: https://www.myfatoorah.com
  Description: Myfatoorah Payment gateway for woocommerce
  Version: 1.1
  Author: Nermeen Shoman
  Author URI:
 */

add_action('plugins_loaded', 'woocommerce_myfatoorah_init', 0);

if (!defined('ABSPATH')) {
    exit;
}

function woocommerce_myfatoorah_init() {
    if (!class_exists('WC_Payment_Gateway'))
        return;

    class WC_Myfatoorah extends WC_Payment_Gateway {
public $api_username;

        /** @var bool Whether or not logging is enabled */
        public static $log_enabled = false;

        /** @var WC_Logger Logger instance */
        public static $log = false;

        /**
         * Constructor for the gateway.
         */
        public function __construct() {
            $this->id = 'myfatoorah';
            $this->has_fields = false;
            $this->lang =get_bloginfo("language");
            $this->order_button_text = __('Proceed to Myfatoorah', 'woocommerce');
            $this->lang_id = 2;
            if($this->lang == 'ar'){
                $this->order_button_text = __(' استكمال الدفع', 'woocommerce');
                $this->lang_id = 1;

            }
            $this->method_title = __('Myfatoorah', 'woocommerce');
            $this->method_description = sprintf(__('Myfatoorah standard sends customers to Myfatoorah to enter their payment information.', 'woocommerce'), '<a href="' . admin_url('admin.php?page=wc-status') . '">', '</a>');
            $this->supports = array(
                'products',
                'refunds'
            );

            // Load the settings.
            $this->getVendorGateway();
            $this->getDHLStatus();
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables.
            $this->title = $this->get_option('title');
            $this->logo_url =  $this->get_option('logo_url'); 
            $this->testmode = 'yes' === $this->get_option('testmode', 'no');
            $this->debug = 'yes' === $this->get_option('debug', 'no');
            $this->receiver_email = $this->get_option('receiver_email', $this->email);
            $this->identity_token = $this->get_option('identity_token');
// Define user set variables.
            $this->api_username = $this->get_option('api_username');
            $this->api_password = $this->get_option('api_password');
            $this->api_gateway_payment = $this->get_option('api_gateway_payment');
	        $url = parse_url($this->get_option('api_url'));
            $this->api_url = $url['scheme'].'://'.$url['host'];

            $this->token = $this->getToken();

            self::$log_enabled = $this->debug;
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        }

        /**
         * Logging method.
         * @param string $message
         */
        public static function log($message) {
            if (self::$log_enabled) {
                if (empty(self::$log)) {
                    self::$log = new WC_Logger();
                }
                self::$log->add('myfatoorah', $message);
            }
        }
    
        /**
         * list avail Payment Gateways.
         */
        function getVendorGateway(){
            $this->paymentGateways =  array('myfatoorah'=>'MyFatoorah', 
    		                    'md'=>'Mada KSA',
    	                        'kn'=>'Knet',
    	                        'vm'=>'Visa / Master',
    	                        'b'=>'Benefit',
    	                        'np'=>'Qatar Debit Card - NAPS',
    	                        'uaecc'=>'Debit Cards UAE - VISA UAE',
    	                        's'=> 'Sadad',
    	                        'ae'=>'AMEX');
            
        }
        
        /**
         * Get DHL status.
         */
        function getDHLStatus(){
            $this->DHLStatus =  true;
            
        }
        /**
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields() {
            $this->form_fields = include( 'includes/settings-myfatoorah.php' );
        }

        /**
         * Get the transaction URL.
         * @param  WC_Order $order
         * @return string
         */
        public function get_transaction_url($order) {
            return parent::get_transaction_url($order);
        }

        /**
         * Process the payment and return the result.
         * @param  int $order_id
         * @return array
         */
        public function process_payment($order_id) {
            include_once( 'includes/class-wc-myfatoorah-request.php' );
            //$order          = wc_get_order( $order_id );
            $order = new WC_Order($order_id);
            $myfatoorah_request = new WC_Myfatootah_Request($this);
            if(empty($this->token)){
                if($this->lang == 'ar'){
                   throw new Exception( __( 'نعتذر عن تكمله طلبك من خلال ماي فاتوره، نرجو مراجعه اسم المستخدم و كلمه السر الخاصه بماي فاتوره أو الرجوع لخدمه العملاء', 'woo' ) );

                }else{
                    throw new Exception( __( 'MyFatoorah can not authorize your request. Please try again later or contact MyFatoorah Support', 'woo' ) );
                }
            }
            $curl_data = $myfatoorah_request->get_request_url($order, $this->testmode, $this->api_username, $this->api_password, $this->api_url);
            $url = $this->api_url;
//echo $curl_data; die;
// call rest api 
        $api_invoice = $this->api_url . '/ApiInvoices/CreateInvoiceIso';
        $result = '';
        do {
            $retry = false;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_invoice);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_data);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: $this->token"
            ));

            $res = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            $result = json_decode($res);
            if ($httpcode === 401) { // unauthorized
                $this->getToken();
                $retry = true;
            }
            curl_close($ch);
        } while ($retry);
        
            if (isset($result->IsSuccess) && $result->IsSuccess ) {
                
                foreach($result->PaymentMethods as $PaymentMethod){
                    if($PaymentMethod->PaymentMethodCode === $this->api_gateway_payment){
                        $redirectUrl = $PaymentMethod->PaymentMethodUrl;
                    }
                }
                if($redirectUrl =='' || $this->api_gateway_payment === 'myfatoorah'){
                    $redirectUrl =  $result->RedirectUrl;
                }
                
                return array(
                    'result' => 'success',
                    'redirect' => $redirectUrl
                );            
                
            } else {
                foreach($result->FieldsErrors as $error){
                    if($error->Name == 'CustomerMobile'){
                        if($this->lang == 'ar'){
                            throw new Exception( __( 'يرجى إدخال رقم الهاتف الذي يبدأ به رمز البلد  + XXX أو 00XXX ، مع الحد الأقصى لطول الهاتف 10 (على سبيل المثال +9661234567890 أو 009661234567890)', 'woo' ) );
                        }else{
                            throw new Exception( __('Please, enter phone number starts with country phone code +XXX or 00XXX, with maximum telephone length 10 (ex. +9661234567890 or 009661234567890)', 'woo' ) );
                        }
                    }else{
                        throw new Exception( __( $error->Name .' '.$error->Error, 'woo' ) );
    
                    }
                }       
                
            }
        }
        
    /*
    * get token 
    */
    function getToken() {
        $url_token = $this->api_url . '/Token';
        $params = "grant_type=password"
                . "&username=" . $this->api_username
                . "&password=" . $this->api_password;

        $curl = curl_init($url_token);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $json_response = curl_exec($curl);
        $response = json_decode($json_response, true);
        if(isset($response['token_type'])){
            return $response['token_type'] . ' ' . $response['access_token'];
        }
    }
        /**
         * Get gateway icon.
         *
         * @access public
         * @return string
         */
        public function get_icon() {
            if(empty($this->logo_url) && empty($this->title)){
                $icon = 'MyFatoorah';
            }
            if(!empty($this->logo_url)){
                $icon = '<img src="'.$this->logo_url.'" style="height: 40px; width: 240px;" alt="MyFatoorah_logo" />';
            }
            
            return apply_filters('woocommerce_gateway_icon', $icon, $this->id);

        }

        public function myfatoorah_sucess() {
            echo "in myfatoorah sucess";
        }

    }

    /**
     * Add the Gateway to WooCommerce
     * */
    function woocommerce_add_myfatoorah_gateway($methods) {
        $methods[] = 'WC_Myfatoorah';
        return $methods;
    }

    function callback_handler($id) {
        //$methods[] = 'WC_Myfatoorah';
        //return $methods;
        echo "i am in";
//print_r($id);
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_myfatoorah_gateway');
    // add_filter('parse_request', 'callback_handler' );

    add_action('template_redirect', 'wc_custom_redirect_after_purchase');

    function wc_custom_redirect_after_purchase() {
        global $wp;


        if (is_checkout() && !empty($wp->query_vars['order-received'])) {
            $order_id = $wp->query_vars['order-received'];
            $order = new WC_Order($order_id);
            if($order->payment_method == 'myfatoorah'){
                $order->update_status('processing');
                $url = get_site_url();
            }

        }
    }

}


