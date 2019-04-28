<?php

if ( !class_exists('YdoMigrationZipToDev') )

{

	class YdoMigrationZipToDev extends YdoMigrationBaseCalss

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

			var $dir;

			var $prefix;
			var $root_domain;

			public function __construct()

			{

				

				global $wpdb;

				$this->prefix = $wpdb->prefix;

				add_action( 'admin_init', array( $this, 'admin_init_verify_post' ) );

				$this->host = $_POST['host'];

				$this->username = $_POST['username'];

				$this->password = $_POST['password'];

				$this->port = $_POST['port'];
				$this->root_domain = $this->only_domain($_POST['root_domain']);

				$this->dir = '/public_html/staging';

				$this->remote_file = $this->dir.'/ydo_migration.zip';

				$this->remote_local_dump_file = $this->dir.'/ydo_migrat_dump.sql';

			}

			public function admin_init_verify_post()

			{

				if ( isset( $_POST['dev_to_staging_name'] ) || wp_verify_nonce( $_POST['dev_to_staging_name'], 'dev_to_staging_action' ))

				{

					$this->migration_cred = get_option('migration_credential');

					$this->migration_cred['cred']['dev_to_staging'] = array( "host"=>$this->host , "username"=>$this->username , "password"=>$this->password , "port"=>$this->port , "root_domain"=>$this->root_domain );

					if( !empty($this->migration_cred) )

					{

						update_option( 'migration_credential', $this->migration_cred );

					}

					

					YdoMigrationDisplayError::begin();

					YdoMigrationDisplayError::print_message("Moving from dev to staging<br>",'', true);

					//creating zip here

					YdoMigrationDisplayError::print_message("Creating zip of files begins",'', true);

					$this->zip_creation_message = $this->create_zip_of_files( ABSPATH );

					YdoMigrationDisplayError::print_message("Creating zip of files done<br>",'success', true);					

					//connect to ftp

					$this->verify_nonce_field = $this->get_ftp_connect();//ftp_connect_result	

					//check if ftp is connected and zip created

					if( $this->verify_nonce_field === true && $this->zip_creation_message )

					{

						//create db and user here

						YdoMigrationDisplayError::print_message("Creating db begins here",'', true);

					    $this->creat_db_user_assign = $this->creat_db_user_assign();

						YdoMigrationDisplayError::print_message("Creating db process done<br>",'success', true);

						//$this->create_zip_of_files( ABSPATH );

						$migrate_zip_to_remote_server = $this->migrate_zip_to_remote_server();

						if ( $migrate_zip_to_remote_server )

						{

							$this->transfer_message = true;

							//$extract_file_here = file_get_contents($this->addhttp($_POST['host'])."/ydo_extract_zip.php");

						}else

						{

							$this->transfer_message = false;

						}

					}else

					{

						YdoMigrationDisplayError::print_message("Could not connect to server.",'failed', true);

					}

					die;				

				}

			}

			public function migrate_zip_to_remote_server()

			{

				YdoMigrationDisplayError::print_message("Migrating zip to remote server",'', true);

				@ftp_mkdir($this->connect_it, $this->dir);

				if( 1 )

				{

					$ftp_put = ftp_put( $this->connect_it, $this->remote_file, ABSPATH.$this->zip_file_name, FTP_BINARY );

					if( $ftp_put )

					{

						$dump_sql_data = $this->dump_sql_data();

						$ftp_put = ftp_put( $this->connect_it, $this->remote_local_dump_file, ABSPATH.$this->local_dump_file, FTP_BINARY );

						$ftp_put = ftp_put( $this->connect_it, $this->dir.'/index.php', YDO_PLUGIN_DIR.'installer/index_intaller.php', FTP_BINARY );

						$ftp_put = ftp_put( $this->connect_it, $this->dir.'/migration_installer.php', YDO_PLUGIN_DIR.'installer/migration_installer.php', FTP_BINARY );

						$this->get_file_back();

						YdoMigrationDisplayError::print_message("Migrating zip to remote server done<br>",'success', true);

						YdoMigrationDisplayError::print_message("When you open your site an installer will be run to update the some settings for you staging site.",'failed', true);

						

					}	

				}else

				{

					YdoMigrationDisplayError::print_message("Could not create directory staging.",'failed', true);

					die;

				}								

				return $ftp_put;

			}

			public function creat_db_user_assign()

			{

				try

				{

				$xmlapi = new xmlapi($this->host);

				$xmlapi->set_port($this->port);   

				$xmlapi->set_output("json"); 

				$xmlapi->password_auth($this->username,$this->password);    

				$xmlapi->set_debug(0);//output actions in the error log 1 for true and 0 false 

				$all_database = array();
				YdoMigrationDisplayError::print_message("Creating subdomain.",'', true);
				$subdomainsList = $xmlapi->api2_query( $this->username , 'SubDomain', 'addsubdomain',array('domain'=> 'staging','rootdomain'=> $this->root_domain,'dir'=> '/public_html/staging'));
				$subdomainsList = json_decode($subdomainsList);
				if( isset($subdomainsList->cpanelresult->data[0]) && $subdomainsList->cpanelresult->data[0]->result == 1 )
				{
					YdoMigrationDisplayError::print_message("Subdomain created successfully.",'success', false);
				}else
				{
					YdoMigrationDisplayError::print_message($subdomainsList->cpanelresult->error,'failed', false);
				}			

				$cpaneluser= $this->username;

				$dbname = 'ydo_migrat_dump.sql';

				$get_doc_root = $xmlapi->api2_query($this->username, 'DomainLookup', 'getdocroot',array('domain'=> $this->host,));

                $get_doc_root = json_decode($get_doc_root);

				if( empty( $get_doc_root->cpanelresult->error ) && isset($get_doc_root->cpanelresult->data[0]) && !empty($get_doc_root->cpanelresult->data[0]->docroot) )

				{

					//$this->dir = $get_doc_root->cpanelresult->data[0]->docroot.'/staging';

				}

				$listdbs = $xmlapi->api2_query($cpaneluser, 'MysqlFE', 'listdbs');

				$listdbs = json_decode($listdbs);

				if( empty( $listdbs->cpanelresult->error ) && isset($listdbs->cpanelresult->data[0]) && !empty($listdbs->cpanelresult->data[0]->userlist[0]->db) )

				{

					$this->db_name_cpanel = $listdbs->cpanelresult->data[0]->userlist[0]->db;

				    $this->db_name_cpanel = strstr($this->db_name_cpanel, '_', true);

				}	

				if( empty( $listdbs->cpanelresult->error ) && isset($listdbs->cpanelresult->data[0]) && !empty($listdbs->cpanelresult->data[0]->userlist[0]->db) )

				{

					$this->user_name_cpanel = $listdbs->cpanelresult->data[0]->userlist[0]->user;

				    $this->user_name_cpanel = strstr($this->user_name_cpanel, '_', true);

				}			

				$this->databasename = substr(DB_NAME,strpos(DB_NAME,'_')+1);

				$this->databasename= $this->db_name_cpanel.'_'.$this->databasename;

				

				$this->databaseuser = substr(DB_USER,strpos(DB_USER,'_')+1);

				$this->databaseuser= $this->user_name_cpanel.'_'.$this->databaseuser;

				

				$this->databasepass= MIGRATE_DB_PASS;

				$local_basedir = ABSPATH;				

				//create database    

				$createdb = $xmlapi->api1_query($cpaneluser, "Mysql", "adddb", array($this->databasename));

				$createdb = json_decode($createdb);

				if( $createdb->event->result != 1 )

				{

				  //$this->db_created_message = $createdb->error;	

				  YdoMigrationDisplayError::print_message(esc_html($createdb->error),'failed', true);

				}else

				{

					YdoMigrationDisplayError::print_message("Successfully created DB.",'success', false);

				}

				//create user 

				$usr = $xmlapi->api1_query($cpaneluser, "Mysql", "adduser", array($this->databaseuser, $this->databasepass));   

				$usr = json_decode($usr);

				if( $usr->event->result != 1 )

				{

				 // $this->userassign_message = $usr->error;	

				 YdoMigrationDisplayError::print_message(esc_html($usr->error),'failed', true);

				}else

				{

					YdoMigrationDisplayError::print_message("Successfully created user.",'success', false);

				}

				//add user 

				$addusr = $xmlapi->api1_query($cpaneluser, 'Mysql', 'adduserdb', array('' . $this->databasename . '', '' . $this->databaseuser . '', 'all'));

				$addusr = json_decode($addusr);

				if( $addusr->event->result != 1 )

				{

				  //$this->db_created_message = $addusr->error;	

				  YdoMigrationDisplayError::print_message(esc_html($addusr->error),'failed', true);

				}

				}catch(Exception $e)

				{

					YdoMigrationDisplayError::print_message($e->getMessage(),'failed', true);

					die;

				}

				$this->change_wp_config_file();

			}			

			public function change_wp_config_file( $file_name_path = '' )

			{

				/*if( !empty($this->db_name_cpanel) )

				$this->databasename = $this->db_name_cpanel.'_'.$this->databasename;;

				if( !empty($this->user_name_cpanel) )

				$this->databaseuser = $this->user_name_cpanel.'_'.$this->databaseuser;*/

				$wp_config_file = YDO_PLUGIN_DIR . "installer/migration_installer.php";

				$db_array = array( 'db'=>$this->databasename , 'user'=>$this->databaseuser , 'pass'=>$this->databasepass );

				$this->wpa_replace_config( $wp_config_file , $db_array );

			}

			public function wpa_replace_config( $path , $db_array = array() ){

				

				WP_Filesystem();

				$wp_filesystem =& $GLOBALS['wp_filesystem'];

				$array_to_replace = array(

				"test_db_name" => 'var $db ='.'"'.$db_array['db'].'"'.';',

				"test_user_name" => 'var $user ='.'"'.$db_array['user'].'"'.';', 

				"test_password" => 'var $pass ='.'"'.$db_array['pass'].'"'.';',

				"test_prefix" => 'var $prefix ='.'"'.$this->prefix.'"'.';'

				);

				$wpconfig = $path;

				//if(!$wpconfig) return;

				if ( 0 && ! $wp_filesystem->is_writable($wpconfig) ) {

					if( false === $wp_filesystem->chmod( $wpconfig ) )

					{

						/*wpa_backup_error('wpconfig', sprintf( __( "<code>%s</code> is not writable and wpclone was unable to change the 

			

			file permissions." ), $wpconfig ), true );*/

					}

				}

				$content = file( $wpconfig );

			

				foreach( $content as $key => $value ) {

					

					foreach( $array_to_replace as $r_key => $r_value ) {

						if( false !== strpos( $value, $r_key ) ) {

							$content[$key] = $r_value;

						}	

					}

			

				}

				

				$content = implode( $content );

				//print_r($wp_filesystem);

				$wp_filesystem->put_contents( $wpconfig, $content ); //,0600

			

			}

			public function get_file_back()

			{

				WP_Filesystem();

				$wp_filesystem =& $GLOBALS['wp_filesystem'];

				$wp_config_file = YDO_PLUGIN_DIR . "installer/migration_installer.php";

				$wp_config_file_main = YDO_PLUGIN_DIR . "installer/migration_installer_dummy.php";

				$wpconfig = $wp_config_file;

				$content = implode(file( $wp_config_file_main ));

				$wp_filesystem->put_contents( $wpconfig, $content );

			}
			public function only_domain( $input = '' )
			{
				$input = trim($input, '/');
				if (!preg_match('#^http(s)?://#', $input)) {
					$input = 'http://' . $input;
				}
				$urlParts = parse_url($input);
				$domain = preg_replace('/^www\./', '', $urlParts['host']);
				return $domain;
			}

	}

	$YdoMigrationZipToDev = new YdoMigrationZipToDev();

}

?>