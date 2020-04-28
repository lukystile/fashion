<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates requests to send to Myfatoorah.
 */
class WC_Myfatootah_Request {

    /**
     * Stores line items to send to myfatoorah .
     * @var array
     */
    protected $line_items = array();

    /**
     * Pointer to gateway making the request.
     * @var WC_Gateway_myfatoorah 
     */
    protected $gateway;

    /**
     * Endpoint for requests from myfatoorah .
     * @var string
     */
    protected $notify_url;

    /**
     * Constructor.
     * @param WC_Gateway_myfatoorah  $gateway
     */
    public function __construct($gateway) {
        $this->gateway = $gateway;
        $this->notify_url = WC()->api_request_url('WC_Myfatoorah');
    }

    /**
     * Get the myfatoorah request URL for an order.
     * @param  WC_Order $order
     * @param  bool     $sandbox
     * @return string
     */
    public function get_request_url($order, $sandbox = false) {
        $myfatoorah_args = $this->get_myfatoorah_args($order);
        return $myfatoorah_args;
    }

    /**
     * Get Args for passing to Myfatoorah.
     * @param  WC_Order $order
     * @return array
     */
    protected function get_myfatoorah_args($order) {

        WC_Myfatoorah::log('Generating payment form for order ' . $order->get_order_number() . '. Notify URL: ' . $this->notify_url);
        $itemss['data'] = $this->get_line_item_args($order);
        // exchange rate start
        $order_total = $order->get_total();
        // Get order total in base currency
        $order_total_base_currency = $order->get_meta('_order_total_base_currency');
        // Set a a default exchange rate, in case any of the totals is not valid.
        // Here we return "1", implying that no currency conversion was performed.
        $exchange_rate = 1;
        // Ensure that we are dealing with two valid values
        if (($order_total > 0) && ($order_total_base_currency > 0)) {
            // Calculate the exchange rate from the two totals
            $exchange_rate = $order_total_base_currency / $order_total;
        }
        // exchange rate end
        $invoiceItemsArr = array();
        foreach (WC()->cart->get_cart() as $item => $values) {
            $_product = wc_get_product($values['data']->get_id());
            $invoiceItemsArr[] = array('ProductId' => '', 'ProductName' => $_product->get_title(), 'Quantity' => $values['quantity'], 'UnitPrice' => $values['data']->get_price());
        }

        $shipping_total = WC()->cart->shipping_total;

        if ($shipping_total > 0) {
            $invoiceItemsArr[] = array('ProductId' => '', 'ProductName' => 'Shipping amount', 'Quantity' => 1, 'UnitPrice' => $shipping_total);
        }
 
        $discount = WC()->cart->get_cart_discount_total();
        if($discount>0)
            $invoiceItemsArr[] = array('ProductId' => '', 'ProductName' => 'Discount amount', 'Quantity' => 1, 'UnitPrice' => - $discount );
        
        

        //$callBackurl = get_site_url().'/myfatoorah_redirect/callback?orderId='. $order->get_id();
	$sucess_url = $order->get_checkout_order_received_url();
        $err_url = $order->get_cancel_order_url_raw();

        $currencyCode =  $order->get_currency();
        $subtotal = $order->get_total();
        $t = $order->get_id();
        // check currency in myfatoorah
        $currencyIso = $this->checkCountryAndCurrency($currencyCode);
        if(!$currencyIso && $this->gateway->token!== null){
            // go to checkout and err msg Curreny not supported with payment gateway
            if($this->gateway->lang == 'ar'){
                throw new Exception( __( $currencyCode.' غير معتمد من قبل بوابة الدفع.', 'woo' ) );
            }else{
                throw new Exception( __( $currencyCode.' is not supported by payment gateway.', 'woo' ) );
            }
        }
        // json data 
        $curl_data = '{
	  "InvoiceValue": ' . $t . ',
	  "CustomerName": "' . $order->billing_first_name . '",
	  "CustomerBlock": "string",
	  "CustomerStreet": "string",
	  "CustomerHouseBuildingNo": "string",
	  "CustomerCivilId": "2",
	  "CustomerAddress": "string",
	  "CustomerAddress": "'.$order->billing_address_1.'",
	  "CustomerReference": "' . $order->id . '",
	  "DisplayCurrencyIsoAlpha":"'.$currencyIso.'",
	  "CountryCodeId": 0,
	  "CustomerMobile": "' . $this->checkTelephone($order->billing_phone) . '",
	  "CustomerEmail": "' . $order->billing_email . '",
	  "SendInvoiceOption": 2,
	  "InvoiceItemsCreate": ' . json_encode($invoiceItemsArr) . ',
        "CallBackUrl": "' .  htmlentities($sucess_url)  . '",
	  "Language": '.$this->gateway->lang_id.',
	  "ExpireDate": "'.gmdate("D, d M Y H:i:s", time() + 7 * 24 * 60 * 60).'",
      "ApiCustomFileds": "string",
      "ErrorUrl": "' .  htmlentities($err_url)  . '"
      }';
       
        // echo $curl_data; die;
        return $curl_data;
    }
    function checkTelephone($phone){
        $code = array('+973','+965','+968','+974','+962','+966','+971','00973','00965','00968','00974','00962','00966','00971');
        $result = trim($phone);
        foreach ( $code as $value){
            if(strpos($phone,$value) !== false){
                $result = str_replace($value,'',trim($phone));
            }
        }
        return $result; 
    }
    function checkCountryAndCurrency($cur) {
        $url = $this->gateway->api_url . '/AuthLists/GetCountiesWithCurrenciesIso';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Content-Length: 0",
            "Authorization: ".$this->gateway->token
        ));

        $res = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        $result = json_decode($res);
        curl_close($ch);
//print_r($result); die;
        if($httpcode == 200){
            foreach ($result as $value) {
                if (strpos($value->Text, $cur) !== false || strpos($value->Value, $cur) !== false) {
                    return $value->Value;
                    exit;
                }
            }
        }
        return false;
    }


    /**
     * Get phone number args for myfatoorah request.
     * @param  WC_Order $order
     * @return array
     */
    protected function get_phone_number_args($order) {
        if (in_array($order->billing_country, array('US', 'CA'))) {
            $phone_number = str_replace(array('(', '-', ' ', ')', '.'), '', $order->billing_phone);
            $phone_number = ltrim($phone_number, '+1');
            $phone_args = array(
                'night_phone_a' => substr($phone_number, 0, 3),
                'night_phone_b' => substr($phone_number, 3, 3),
                'night_phone_c' => substr($phone_number, 6, 4),
                'day_phone_a' => substr($phone_number, 0, 3),
                'day_phone_b' => substr($phone_number, 3, 3),
                'day_phone_c' => substr($phone_number, 6, 4)
            );
        } else {
            $phone_args = array(
                'night_phone_b' => $order->billing_phone,
                'day_phone_b' => $order->billing_phone
            );
        }
        return $phone_args;
    }

    /**
     * Get shipping args for myfatoorah request.
     * @param  WC_Order $order
     * @return array
     */
    protected function get_shipping_args($order) {
        $shipping_args = array();

        if ('yes' == $this->gateway->get_option('send_shipping')) {
            $shipping_args['address_override'] = $this->gateway->get_option('address_override') === 'yes' ? 1 : 0;
            $shipping_args['no_shipping'] = 0;

            // If we are sending shipping, send shipping address instead of billing
            $shipping_args['first_name'] = $order->shipping_first_name;
            $shipping_args['last_name'] = $order->shipping_last_name;
            $shipping_args['company'] = $order->shipping_company;
            $shipping_args['address1'] = $order->shipping_address_1;
            $shipping_args['address2'] = $order->shipping_address_2;
            $shipping_args['city'] = $order->shipping_city;
            $shipping_args['state'] = $this->get_myfatoorah_state($order->shipping_country, $order->shipping_state);
            $shipping_args['country'] = $order->shipping_country;
            $shipping_args['zip'] = $order->shipping_postcode;
        } else {
            $shipping_args['no_shipping'] = 1;
        }

        return $shipping_args;
    }

    /**
     * Get line item args for myfatoorah request.
     * @param  WC_Order $order
     * @return array
     */
    protected function get_line_item_args($order) {

        /**
         * Try passing a line item per product if supported.
         */
        if ((!wc_tax_enabled() || !wc_prices_include_tax() ) && $this->prepare_line_items($order)) {

            $line_item_args = $this->get_line_items();
            $line_item_args['tax_cart'] = $this->number_format($order->get_total_tax(), $order);

            if ($order->get_total_discount() > 0) {
                $line_item_args['discount_amount_cart'] = $this->number_format($this->round($order->get_total_discount(), $order), $order);
            }

            /**
             * Send order as a single item.
             *
             * For shipping, we longer use shipping_1 because myfatoorah ignores it if *any* shipping rules are within myfatoorah, and myfatoorah ignores anything over 5 digits (999.99 is the max).
             */
        } else {

            $this->delete_line_items();

            $all_items_name = $this->get_order_item_names($order);
            $this->add_line_item($all_items_name ? $all_items_name : __('Order', 'woocommerce'), 1, $this->number_format($order->get_total() - $this->round($order->get_total_shipping() + $order->get_shipping_tax(), $order), $order), $order->get_order_number());
            $this->add_line_item(sprintf(__('Shipping via %s', 'woocommerce'), ucwords($order->get_shipping_method())), 1, $this->number_format($order->get_total_shipping() + $order->get_shipping_tax(), $order));

            $line_item_args = $this->get_line_items();
        }

        return $line_item_args;
    }

    /**
     * Get order item names as a string.
     * @param  WC_Order $order
     * @return string
     */
    protected function get_order_item_names($order) {
        $item_names = array();

        foreach ($order->get_items() as $item) {
            $item_names[] = $item['name'] . ' x ' . $item['qty'];
        }

        return implode(', ', $item_names);
    }

    /**
     * Get order item names as a string.
     * @param  WC_Order $order
     * @param  array $item
     * @return string
     */
    protected function get_order_item_name($order, $item) {
        $item_name = $item['name'];
        $item_meta = new WC_Order_Item_Meta($item);

        if ($meta = $item_meta->display(true, true)) {
            $item_name .= ' ( ' . $meta . ' )';
        }

        return $item_name;
    }

    /**
     * Return all line items.
     */
    protected function get_line_items() {
        return $this->line_items;
    }

    /**
     * Remove all line items.
     */
    protected function delete_line_items() {
        $this->line_items = array();
    }

    /**
     * Get line items to send to myfatoorah.
     * @param  WC_Order $order
     * @return bool
     */
    protected function prepare_line_items($order) {
        $this->delete_line_items();
        $calculated_total = 0;

        // Products
        foreach ($order->get_items(array('line_item', 'fee')) as $item) {
            if ('fee' === $item['type']) {
                $item_line_total = $this->number_format($item['line_total'], $order);
                $line_item = $this->add_line_item($item['name'], 1, $item_line_total);
                $calculated_total += $item_line_total;
            } else {
                $product = $order->get_product_from_item($item);
                $item_line_total = $this->number_format($order->get_item_subtotal($item, false), $order);
                $line_item = $this->add_line_item($this->get_order_item_name($order, $item), $item['qty'], $item_line_total, $product->get_sku());
                $calculated_total += $item_line_total * $item['qty'];
            }

            if (!$line_item) {
                return false;
            }
        }

        // Shipping Cost item - myfatoorah only allows shipping per item, we want to send shipping for the order.
        if ($order->get_total_shipping() > 0 && !$this->add_line_item(sprintf(__('Shipping via %s', 'woocommerce'), $order->get_shipping_method()), 1, $this->round($order->get_total_shipping(), $order))) {
            return false;
        }

        // Check for mismatched totals.
        if ($this->number_format($calculated_total + $order->get_total_tax() + $this->round($order->get_total_shipping(), $order) - $this->round($order->get_total_discount(), $order), $order) != $this->number_format($order->get_total(), $order)) {
            return false;
        }

        return true;
    }

    /**
     * Add Myfatoorah Line Item.
     * @param  string  $item_name
     * @param  int     $quantity
     * @param  int     $amount
     * @param  string  $item_number
     * @return bool successfully added or not
     */
    protected function add_line_item($item_name, $quantity = 1, $amount = 0, $item_number = '') {
        $index = ( sizeof($this->line_items) / 4 ) + 1;

        if ($amount < 0 || $index > 9) {
            return false;
        }

        $this->line_items['item_name_' . $index] = html_entity_decode(wc_trim_string($item_name ? $item_name : __('Item', 'woocommerce'), 127), ENT_NOQUOTES, 'UTF-8');
        $this->line_items['quantity_' . $index] = $quantity;
        $this->line_items['amount_' . $index] = $amount;
        $this->line_items['item_number_' . $index] = $item_number;

        return true;
    }

    /**
     * Get the state to send to Myfatoorah.
     * @param  string $cc
     * @param  string $state
     * @return string
     */
    protected function get_myfatoorah_state($cc, $state) {
        if ('US' === $cc) {
            return $state;
        }

        $states = WC()->countries->get_states($cc);

        if (isset($states[$state])) {
            return $states[$state];
        }

        return $state;
    }

    /**
     * Check if currency has decimals.
     * @param  string $currency
     * @return bool
     */
    protected function currency_has_decimals($currency) {
        if (in_array($currency, array('KWD', 'HUF', 'JPY', 'TWD', 'USD'))) {
            return false;
        }

        return true;
    }

    /**
     * Round prices.
     * @param  double $price
     * @param  WC_Order $order
     * @return double
     */
    protected function round($price, $order) {
        $precision = 3;

        if (!$this->currency_has_decimals($order->get_order_currency())) {
            $precision = 3;
        }

        return round($price, $precision);
    }

    /**
     * Format prices.
     * @param  float|int $price
     * @param  WC_Order $order
     * @return string
     */
    protected function number_format($price, $order) {
        $decimals = 3;

        if (!$this->currency_has_decimals($order->get_order_currency())) {
            $decimals = 3;
        }

        return number_format($price, $decimals, '.', '');
    }

}




