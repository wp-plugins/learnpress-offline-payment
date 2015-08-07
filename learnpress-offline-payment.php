<?php
/*
Plugin Name: LearnPress Offline Payment
Plugin URI: http://thimpress.com/learnpress
Description: Pay with check
Author: thimpress
Version: 0.9.1
Author URI: http://thimpress.com
Tags: learnpress
*/

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
// Addon path
define( 'LPR_OFFLINE_PAYMENT_PLUGIN_PATH', dirname( __FILE__ ) );
/**
 * Register offline payment addon
 */
function learn_press_register_offline_payment() {

    require_once( LPR_OFFLINE_PAYMENT_PLUGIN_PATH . '/class-lpr-payment-gateway-offline-payment.php' );
}
add_action( 'learn_press_register_add_ons', 'learn_press_register_offline_payment' );

add_action('plugins_loaded','learnpress_offline_payment_translations');
function learnpress_offline_payment_translations(){          
    $textdomain = 'learnpress_offline_payment';
    $locale = apply_filters("plugin_locale", get_locale(), $textdomain);                   
    $lang_dir = dirname( __FILE__ ) . '/lang/';
    $mofile        = sprintf( '%s.mo', $locale );
    $mofile_local  = $lang_dir . $mofile;    
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
    if ( file_exists( $mofile_global ) ) {      
        load_textdomain( $textdomain, $mofile_global );
    } else {        
        load_textdomain( $textdomain, $mofile_local );
    }  
}