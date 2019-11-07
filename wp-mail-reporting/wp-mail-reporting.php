<?php
/* 
Plugin Name: WP Mail Reporting
Plugin URI: https://xyz.com/
Description: wp-mail error reporting.
Version: 1.0.0
Author: YDO
Author URI: https://xyz.com/
*/
if ( ! defined( 'ABSPATH' ) ) 
{
  die();
}


global $jal_db_version;
$jal_db_version = '1.0';
function wp_mail_table_creation() {
	global $wpdb;
	global $jal_db_version;
	$table_name = $wpdb->prefix . 'wp_mail_reporting';	
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		user_id bigint(20),
		error_data longtext,
		PRIMARY KEY  (id)
	) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	add_option( 'jal_db_version', $jal_db_version );
}
register_activation_hook( __FILE__, 'wp_mail_table_creation' );

add_action( 'wp_mail_failed', 'onMailError', 10, 1 );
function onMailError( $wp_error )
{
	//echo '<pre>';
	//print_r($wp_error);die;
	global $wpdb;
	$get_current_user_id = get_current_user_id();
	$table_name = $wpdb->prefix . 'wp_mail_reporting';
	$wpdb->insert( $table_name, array( 'user_id' => $get_current_user_id, 'error_data' => json_encode($wp_error) ));
}