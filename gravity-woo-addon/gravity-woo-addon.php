<?php
/*
Plugin Name: Gravity Woo Addon
Plugin URI:  https://xyz.com/
Description: Calculates woocommerce product dimension based user input value and dynamically calculate product price and also calculates taxes
Version:     1.0.0
Author:      YDO
Author URI:  https://xyz.com/
*/
if ( ! defined( 'ABSPATH' ) ) 
{
	die();
}
define( 'KEN_PATH', plugin_basename( __FILE__ ) );
define( 'KEN_URL', plugins_url( '', __FILE__ ) );
//include functions
include_once(plugin_dir_path(__FILE__) . 'inc/gravity_woo_function.php');
//include(plugin_dir_path(__FILE__) . 'inc/gravity_chain_function.php');
include_once(plugin_dir_path(__FILE__) . 'inc/gravity_woo_extended_function.php');
include_once(plugin_dir_path(__FILE__) . 'inc/dimension_listing.php');
include_once(plugin_dir_path(__FILE__) . 'inc/edit_dimension_action.php');
include_once(plugin_dir_path(__FILE__) . 'inc/ken_custom_chaining.php');
include_once(plugin_dir_path(__FILE__) . 'inc/discount_setting.php');
include_once(plugin_dir_path(__FILE__) . 'inc/box_list_class.php');
include_once(plugin_dir_path(__FILE__) . 'inc/box_wp_list.php');
