<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for MyFatoorah Gateway.
 */
return array(
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable MyFatoorah', 'woocommerce' ),
		'default' => 'yes'
	),
	'title' => array(
		'title'       => __( 'Title', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'MyFatoorah', 'woocommerce' ),
		'desc_tip'    => true,
	),
	'logo_url' => array(
		'title'       => __( 'MyFatoorah Logo URL', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Please insert your logo url which the user sees during checkout.', 'woocommerce' ),
		'default'     =>  __( '', 'woocommerce' ),
		'desc_tip'    => true,
		'placeholder'=>'https://www.exampleurl.com',
	),
	'debug' => array(
		'title'       => __( 'Debug Log', 'woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log MyFatoorah events, ', 'woocommerce' ), wc_get_log_file_path( 'myfatoorah' ) )
	),
	
	'api_details' => array(
		'title'       => __( 'API Credentials', 'woocommerce' ),
		'type'        => 'title',
		'description' => sprintf( __( 'Enter your  MyFatoorah API credentials ', 'woocommerce' ), '', '</a>' ),
	),
       'api_url' => array(
		'title'       => __( 'API URL', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Get your API credentials from MyFatoorah.', 'woocommerce' ),
		'desc_tip'    => true,
		'placeholder'=>'https://apidemo.myfatoorah.com',
		
	),
	'api_username' => array(
		'title'       => __( 'API Username', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Get your API credentials from MyFatoorah.', 'woocommerce' ),
		'default'     => get_option( 'admin_email' ),
		'desc_tip'    => true
		
	),
	'api_password' => array(
		'title'       => __( 'API Password', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Get your API credentials from MyFatoorah.', 'woocommerce' ),
		'default'     => '',
		'desc_tip'    => true
		
	),
	'api_gateway_payment' => array(
		'title'       => __( 'Checkout Payment Gateway', 'woocommerce' ),
		'type'        => 'select',
		'description' => __( 'MyFatoorah is default gateway. You can select one of below payment gateway  which the user can checkout. ', 'woocommerce' ),
		'options' => $this->paymentGateways,
		'default'     => 'mf',
		'desc_tip'    => true
		
	),
/*	'dhl' => array(
		'title'   => __( 'Enable DHL', 'woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'DHL', 'woocommerce' ),
		'default' => 'no',
		'disabled'=> $this->DHLStatus,
	),*/
);

