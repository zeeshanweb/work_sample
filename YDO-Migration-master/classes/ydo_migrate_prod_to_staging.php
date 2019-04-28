<?php
if ( !class_exists('YdoMigrationProdToStaging') )
{
	class YdoMigrationProdToStaging extends YdoMigrationBaseCalss
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
			var $migrate_db;
			var $prefix;
			var $tis_production;
			public function __construct()
			{
				global $wpdb;
				$this->prefix = $wpdb->prefix;
				$this->migration_cred = get_option( 'migration_credential' );
				add_action( 'admin_init', array( $this, 'admin_init_verify_post' ) );
				$this->host = $_POST['host'];
				$this->username = $_POST['username'];
				$this->password = $_POST['password'];
				$this->port = $_POST['port'];
			}
			public function admin_init_verify_post()
			{
				if ( isset( $_POST['prod_to_staging_name'] ) || wp_verify_nonce( $_POST['prod_to_staging_name'], 'prod_to_staging_action' ))
				{
					$this->tis_production = true;
					$this->migration_cred['cred']['prod_to_staging'] = array( "host"=>$this->host , "username"=>$this->username , "password"=>$this->password , "port"=>$this->port );
					if( !empty($this->migration_cred) )
					{
						update_option( 'migration_credential', $this->migration_cred );
					}					
					YdoMigrationDisplayError::begin();
					YdoMigrationDisplayError::print_message("Moving from prodcution to staging<br>",'', true);
					$this->verify_nonce_field = $this->get_ftp_connect();//ftp_connect_result
					if( $this->verify_nonce_field === true )
					{
						$sorce_url = $_SERVER['DOCUMENT_ROOT'];
						$des_url = $_SERVER['DOCUMENT_ROOT'].'/staging';
						YdoMigrationDisplayError::print_message("Importing production database.",'', true);
						$this->dump_sql_data();
						YdoMigrationDisplayError::print_message("Successfully imported PROD database.",'success', false);
						
						YdoMigrationDisplayError::print_message("<br>Copying file from prodcution to staging",'', true);
						$this->beliefmedia_recurse_copy($sorce_url , $des_url, 'staging');
						YdoMigrationDisplayError::print_message("Successfully Copied files on staging",'success', false);
						
						YdoMigrationDisplayError::print_message("<br>Creating DB on staging",'', true);
						$this->craete_db_staging( DB_NAME.'_staging' , ABSPATH , 'staging' );						
						
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
			public function craete_db_staging( $new_db_name = '' , $db_restore_path = '' , $domain_to_replace = '' )
			{
				try
				{
				$xmlapi = new xmlapi($this->host); 
				$xmlapi->set_port($this->port);  
				$xmlapi->set_output("json"); 
				$xmlapi->password_auth($this->username,$this->password);    
				$xmlapi->set_debug(0);//output actions in the error log 1 for true and 0 false 
				$cpaneluser= $this->username;
				if( $this->tis_production === true )
				{
					$root_domain = $this->get_root_domain();
					YdoMigrationDisplayError::print_message("Creating subdomain.",'', true);
					$subdomainsList = $xmlapi->api2_query( $this->username , 'SubDomain', 'addsubdomain',array('domain'=> 'staging','rootdomain'=> $root_domain,'dir'=> '/public_html/staging'));
					$subdomainsList = json_decode($subdomainsList);
					if( isset($subdomainsList->cpanelresult->data[0]) && $subdomainsList->cpanelresult->data[0]->result == 1 )
					{
						YdoMigrationDisplayError::print_message("Subdomain created successfully.",'success', false);
					}else
					{
						YdoMigrationDisplayError::print_message($subdomainsList->cpanelresult->error,'failed', false);
					}
				}
				$listdbs = $xmlapi->api2_query($cpaneluser, 'MysqlFE', 'listdbs');
				$listdbs = json_decode($listdbs);
				if( isset($listdbs->cpanelresult->data[0]) && !empty($listdbs->cpanelresult->data[0]->userlist[0]->db) )
				{
					$this->db_name_cpanel = $listdbs->cpanelresult->data[0]->userlist[0]->db;
				    $this->db_name_cpanel = strstr($this->db_name_cpanel, '_', true);
				}
				$this->databasename= $new_db_name;
				$this->databaseuser= DB_USER;
				$this->databasepass= DB_PASSWORD;
				$local_basedir = ABSPATH;
				//create database    
				$createdb = $xmlapi->api1_query($cpaneluser, "Mysql", "adddb", array($this->databasename));
				$createdb = json_decode($createdb);
				if( $createdb->event->result != 1 )
				{
				  $this->db_created_message = $createdb->error;	
				  YdoMigrationDisplayError::print_message(esc_html($createdb->error),'', true);
				}else
				{
					YdoMigrationDisplayError::print_message("Successfully created DB on staging",'success', false);
				}
				//add user 
				$addusr = $xmlapi->api1_query($cpaneluser, 'Mysql', 'adduserdb', array('' . $this->databasename . '', '' . $this->databaseuser . '', 'all'));
				$addusr = json_decode($addusr);
				if( $addusr->event->result != 1 )
				{
				  $this->userassign_message = $addusr->error;	
				}
				if( !empty($this->databasename) )
				{
					$this->new_db_connection();
					if( $this->exec_enabled('exec') )
					{
						exec("mysql --user=$this->databaseuser --password='$this->databasepass' $this->databasename < ".$db_restore_path."ydo_migrat_dump.sql");
					}else
					{
						$this->copy_db_to();
					}					
					YdoMigrationDisplayError::print_message("Replacing site url in option table",'', false);
					$this->replace_in_db( $domain_to_replace );
					YdoMigrationDisplayError::print_message("Replacing site url in option table Done",'success', false);
				}	
				}catch(Exception $e)
				{
					YdoMigrationDisplayError::print_message($e->getMessage(),'failed', true);
				}
			}
			public function get_replaced_site_url( $text = '' )
			{
				if( empty($text) )
				{
					$site_url = get_site_url();
					$site_url = str_replace("/staging","",str_replace("staging.","",$site_url));
					return $site_url;
				}
				$replace_text = $text;
				$site_url = get_site_url();
				$parsed = parse_url($site_url);
				$new_site_url = '';
				if( isset($parsed['scheme']) && !empty($parsed['scheme']) )
				{
					$new_site_url = $parsed['scheme'].'://';
				}
				$www = strstr($parsed['host'], '.', true);
				if( 0 && isset($www) && !empty($www) && $www == 'www' )
				{
					$new_site_url .= $www.'.'.$replace_text.'.';
				}else
				{
					$new_site_url .= $replace_text.'.';
				}
				$new_site_url .= $this->get_without_w($site_url);
				return $new_site_url;
			}
			public function new_db_connection()
			{
				if( !empty($this->databaseuser) && !empty($this->databasename) && !empty($this->databasepass) )
				{
					$this->migrate_db = new wpdb($this->databaseuser,$this->databasepass,$this->databasename,DB_HOST);
				}
			}
			public function replace_in_db( $domain_to_replace = '' )
			{
				$new_site_url = $this->get_replaced_site_url( $domain_to_replace );
				if( $this->migrate_db )
				{
					if( !$this->migrate_db->query("UPDATE ".$this->prefix."options SET option_value = '$new_site_url' WHERE option_name = 'home' OR option_name = 'siteurl'"))
					YdoMigrationDisplayError::print_message("Could not update site_url.",'failed', true);
				}else
				{
					YdoMigrationDisplayError::print_message("Could not update site_url.",'failed', true);
				}							
				
			}
			public function copy_db_to()
			{
				global $wpdb;
				$database1 = $this->databasename;
                $database = DB_NAME;
				set_time_limit(0);
                $tables = $wpdb->get_results("SHOW TABLES FROM $database");
				foreach( $tables as $table )
				{
					foreach( $table as $val )
					{
						$tab = $val;//$line[0];die;
						$this->migrate_db->query("DROP TABLE IF EXISTS $database1.$tab");
						$this->migrate_db->query("CREATE TABLE $database1.$tab LIKE $database.$tab");
						$this->migrate_db->query("INSERT INTO $database1.$tab SELECT * FROM $database.$tab");
						YdoMigrationDisplayError::print_message("Table: <b>" . $tab . " </b>Done",'success', false);
					}
				}
			}
			public function get_without_w( $input = '' )
			{
				$input = trim($input, '/');
			
				// If scheme not included, prepend it
				if (!preg_match('#^http(s)?://#', $input)) {
					$input = 'http://' . $input;
				}	
				$urlParts = parse_url($input);
				// remove www
				$domain = preg_replace('/^www\./', '', $urlParts['host']);
				return $domain;
			}
			public function get_root_domain()
			{
				$url = site_url();
				$url = parse_url($url);
				$url = $this->get_without_w($url['host']);
				return $url;
			}
	}
	$YdoMigrationProdToStaging = new YdoMigrationProdToStaging();
}
?>