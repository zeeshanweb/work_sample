<?php
/* 
Plugin Name: Ydo Migration
Plugin URI: https://xyz.com/
Description: This WordPress Plugin is an easy way to push PROD site to staging and vice versa.
Version: 1.0.0
Author: YDO
Author URI: https://xyz.com/
*/
// Plugin Folder Path
if( ! defined( 'ABSPATH' ) )
{
	die();
}
if( !defined( 'YDO_PLUGIN_DIR' ) ) {
   define( 'YDO_PLUGIN_DIR', plugin_dir_path(  __FILE__ ) );
}
// Plugin Folder Path
if( !defined( 'YDO_PLUGIN_FILE' ) ) {
   define( 'YDO_PLUGIN_FILE', __FILE__ );
}
define( 'GIT_USER_NAME' , 'ydodev' );
define( 'GIT_REPO' , 'YDO-Migration' );
define( 'GIT_TOKEN' , 'dfgd' );
define( 'YDO_PLUGIN_URL' , plugin_dir_url( __FILE__ ) );
define( 'MIGRATE_DB_NAME', 'dfg' );
define( 'MIGRATE_DB_USER', 'dfg' );
define( 'MIGRATE_DB_PASS', 'gfdfgdf' );
require_once YDO_PLUGIN_DIR . "classes/xmlapi.php";
require_once YDO_PLUGIN_DIR . "classes/display-error.php";
require_once YDO_PLUGIN_DIR . "classes/ydo_migration_base_class.php";
require_once YDO_PLUGIN_DIR . "admin/ydo-staging-backend.php";
require_once YDO_PLUGIN_DIR . "classes/ydo_migrate_zip_to_dev.php";
require_once YDO_PLUGIN_DIR . "classes/ydo_migrate_prod_to_staging.php";
require_once YDO_PLUGIN_DIR . "classes/ydo_migrate_staging_to_prod.php";

if( ! class_exists( 'Migration_Updater' ) ){
	include_once( YDO_PLUGIN_DIR . 'inc/updater.php' );
}

$updater = new Migration_Updater( __FILE__ );
$updater->set_username( GIT_USER_NAME );
$updater->set_repository( GIT_REPO );
$updater->authorize( GIT_TOKEN ); // Your auth code goes here for private repos
$updater->initialize();
