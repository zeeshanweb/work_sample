<?php
if ( !class_exists('YdoMigrationStagingToProd') )
{
	class YdoMigrationStagingToProd extends YdoMigrationProdToStaging
	{   
			var $connect_it = '';
			var $verify_nonce_field = true;
			var $transfer_message;
			var $zip_creation_message;
			var $host;
			var $port;
			var $username;
			var $password;
			var $db_created_message = '';
			var $user_created_message = '';
			var $userassign_message = '';
			var $databasename;
			var $databaseuser;
			var $databasepass;
			var $db_name_cpanel;
			var $user_name_cpanel;
			var $migration_cred = array();
			public function __construct()
			{
				
				global $wpdb;
				$this->prefix = $wpdb->prefix;
				$this->migration_cred = get_option( 'migration_credential' );
				add_action( 'admin_init', array( $this, 'admin_init_verify_post' ) );
				add_action( 'admin_notices', array( $this,'ydo_migrate_error_notice') );
				$this->host = $_POST['host'];
				$this->username = $_POST['username'];
				$this->password = $_POST['password'];
				$this->port = $_POST['port'];
			}
			public function ydo_migrate_error_notice()
			{
			}
			public function admin_init_verify_post()
			{
				if ( isset( $_POST['staging_to_prod_name'] ) || wp_verify_nonce( $_POST['staging_to_prod_name'], 'staging_to_prod_action' ))
				{
					$this->migration_cred['cred']['staging_to_prod'] = array( "host"=>$this->host , "username"=>$this->username , "password"=>$this->password , "port"=>$this->port );
					if( !empty($this->migration_cred) )
					{
						update_option( 'migration_credential', $this->migration_cred );
					}					
					YdoMigrationDisplayError::begin();
					YdoMigrationDisplayError::print_message("Moving from staging to prodcution<br>",'', true);
					$this->verify_nonce_field = $this->get_ftp_connect();//ftp_connect_result
					if( $this->verify_nonce_field === true )
					{
						$get_directory = $this->get_directory();
						$sorce_url = ABSPATH;//$_SERVER['DOCUMENT_ROOT'];
						$des_url = $get_directory;
						YdoMigrationDisplayError::print_message("Importing staging database.",'', true);
						$this->dump_sql_data();
						YdoMigrationDisplayError::print_message("Successfully imported staging database.",'success', false);
						
						YdoMigrationDisplayError::print_message("<br>Copying files from staging to prodcution",'', true);
						$this->beliefmedia_recurse_copy($sorce_url , $des_url, 'staging');
						YdoMigrationDisplayError::print_message("Successfully Copied files on prodcution",'success', false);
						YdoMigrationDisplayError::print_message("<br>Creating DB on PROD",'', true);
						$find_db_name = $this->find_db_name();
						$this->craete_db_staging( $find_db_name , ABSPATH );						
						
						$db_array = array( 'db'=>$this->databasename , 'user'=>$this->databaseuser , 'pass'=>$this->databasepass );
						YdoMigrationDisplayError::print_message("<br>Changing in wp-config file.",'', true);
						$this->wpa_replace_config( $des_url , $db_array );
						YdoMigrationDisplayError::print_message("Changing in wp-config file done.",'success', false);
					}else
					{
					    YdoMigrationDisplayError::print_message("Could not connect to server.",'failed', true);
					}	
					die;	
								
				}
			}
			public function find_db_name()
			{
				$find_db_name = DB_NAME;
				if( strpos($find_db_name, '_staging') !== false )
				{
					$find_db_name = str_replace("_staging","",$find_db_name);;					
				}else
				{
					$find_db_name = DB_NAME.'_live';
				}
				return $find_db_name;
			}
	}
	$YdoMigrationStagingToProd = new YdoMigrationStagingToProd();
}
?>