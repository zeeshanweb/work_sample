<?php
/* 
Plugin Name: View Subcategory
Version: 3.1.8.4
Author: YDO
Author URI: https://xyz.com/
*/
if ( ! defined( 'ABSPATH' ) ) 
{
		die();
}
if( !defined( 'FRUG_PLUGIN_DIR' ) ) {
   define( 'FRUG_PLUGIN_DIR', plugin_dir_path(  __FILE__ ) );
}
if( !defined( 'FRUG_PLUGIN_URL' ) ) {
   define( 'FRUG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
// Plugin Folder Path
if( !defined( 'FRUG_PLUGIN_FILE' ) ) {
   define( 'FRUG_PLUGIN_FILE', __FILE__ );
}
define( 'FRUG_TAXONOMY', 'product_manufacturer' );
require_once FRUG_PLUGIN_DIR . "inc/function.php";
require_once FRUG_PLUGIN_DIR . "inc/custom_list_table.php";
require_once FRUG_PLUGIN_DIR . "classes/create_manufacturer_class.php";
require_once FRUG_PLUGIN_DIR . "admin/template/view_sub_cat.php";
require_once FRUG_PLUGIN_DIR . "inc/manufacturing_listing.php";
require_once FRUG_PLUGIN_DIR . "classes/edit_manufacturer_class.php";
require_once FRUG_PLUGIN_DIR . "classes/view_manufacturer_class.php";
require_once FRUG_PLUGIN_DIR . "classes/edit_product_line.php";
require_once FRUG_PLUGIN_DIR . "classes/frugal_custom_import.php";

// function that gets the Ajax data
