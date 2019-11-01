<?php
/*
Plugin Name: Gravity Woo Addon
Plugin URI:  https://yourdesignonline.com/
Description: Creates an woocommerce product using gravity form.
Version:     1.0.0
Author:      YDO
Author URI:  https://yourdesignonline.com/
*/
if ( ! defined( 'ABSPATH' ) ) 
{
	die();
}
define( 'KEN_PATH', plugin_basename( __FILE__ ) );
define( 'KEN_URL', plugins_url( '', __FILE__ ) );
//include functions
include(plugin_dir_path(__FILE__) . 'inc/gravity_woo_function.php');
//include(plugin_dir_path(__FILE__) . 'inc/gravity_chain_function.php');
include(plugin_dir_path(__FILE__) . 'inc/gravity_woo_extended_function.php');
include(plugin_dir_path(__FILE__) . 'inc/dimension_listing.php');
include(plugin_dir_path(__FILE__) . 'inc/edit_dimension_action.php');
include(plugin_dir_path(__FILE__) . 'inc/ken_custom_chaining.php');
include(plugin_dir_path(__FILE__) . 'inc/discount_setting.php');
include(plugin_dir_path(__FILE__) . 'inc/box_list_class.php');
include(plugin_dir_path(__FILE__) . 'inc/box_wp_list.php');