<?php
/* 
Plugin Name: Gravityform Data Capture
Plugin URI: http://xyz.com/
Description: This WordPress Plugin is to capture gravity form data and save in different db.
Version: 1.0.0
Author: YDO
Author URI: http://xyz.com/
*/
if ( ! defined( 'ABSPATH' ) ) {

	die();

}
define( 'YDO_GFDC_PATH', plugin_basename( __FILE__ ) );

define( 'YDO_GFDC_HOST', 'localhost' );
define( 'YDO_GFDC_DB', 'xyz' );
define( 'YDO_GFDC_USER', 'xyz' );
define( 'YDO_GFDC_PASS', 'xyz' );

//include nbp api plugin
global $gfdc_wpdb;

$gfdc_wpdb = false;

include_once( plugin_dir_path( __FILE__ ).'admin/gravityform-data-capture-setting.php' );
include_once( plugin_dir_path( __FILE__ ).'inc/function.php' );

include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-database-abstract.php' );
include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-database.php' );

include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-save-form-data.php' );
include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-save-form-data-ask-pharmacist.php' ); //Form id id 6
include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-save-form-data-update-your-info.php' ); //Form id id 5
include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-save-form-data-transfer-rx.php' ); //Form id id 3 //form_transfer_rx
include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-save-form-data-enrollment-form.php' ); //Form id id 7 //enrollment
include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-save-form-data-contact-us.php' ); //Form id id 1 //contact us
include_once( plugin_dir_path( __FILE__ ).'classes/gfdc-save-form-data-medication_management_enrollment_form.php' ); //Form id id 11 //gfdc-save-form-data-medication_management_enrollment_form
