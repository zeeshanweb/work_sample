<?php
if ( !class_exists('YdoMigrationBaseCalss') )
{
	class YdoMigrationBaseCalss
	{   
			var $zip_file_name = 'ydo_migration.zip';			
			var $local_dump_file = 'ydo_migrat_dump.sql';			
			var $run_script = '/public_html/ydo_extract_zip.php';
			public function __construct()
			{
				
			}			
			public function get_ftp_connect( $ftp_detail = array() )
			{
			  $ftp_host = $_POST['host']; /* host */
			  $ftp_user_name = $_POST['username']; /* username */
			  $ftp_user_pass = $_POST['password']; /* password */
			  $this->connect_it = @ftp_connect( $ftp_host );
			  if( $this->connect_it )
			  {
				 if(@ftp_login( $this->connect_it, $ftp_user_name, $ftp_user_pass ))
				  {
					 ftp_pasv($this->connect_it, true) or die("Cannot switch to passive mode");
					 return true; 
				  }else
				  {
					  return false;
				  } 
			  }else
			  {
				  return false;
			  }			  
			}
			public function close_ftp_connection()
			{
				ftp_close( $this->connect_it );
			}
			public function create_zip_of_files( $path = '' )
			{
				if( empty($path) )
				{
					return false;
				}
				$zip_file = $path.$this->zip_file_name;
				//define('ABSPATH', dirname(__FILE__) . '/'); 
				/* Exclude Files */
				$exclude_files = array();
				$exclude_files[] = realpath( $zip_file );
				$exclude_files[] = realpath( 'zip.php' );
				 
				/* Path of current folder, need empty or null param for current folder */
				$root_path = realpath( $path );
				 
				/* Initialize archive object */
				$zip = new ZipArchive;
				$zip_open = $zip->open( $zip_file, ZipArchive::CREATE );
				 
				/* Create recursive files list */
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $root_path ),
					RecursiveIteratorIterator::LEAVES_ONLY
				);
				 
				/* For each files, get each path and add it in zip */
				if( !empty( $files ) ){
				 
					foreach( $files as $name => $file ) {
				 
						/* get path of the file */
						$file_path = $file->getRealPath();
				 
						/* only if it's a file and not directory, and not excluded. */
						if( !is_dir( $file_path ) && !in_array( $file_path, $exclude_files ) ){
				 
							/* get relative path */
							$file_relative_path = str_replace( $root_path, '', $file_path );
				 
							/* Add file to zip archive */
							$zip_addfile = $zip->addFile( $file_path, $file_relative_path );
						}
					}
				}
				 
				/* Create ZIP after closing the object. */
				$zip_close = $zip->close();
				return $zip_close;
			}	
			public function addhttp( $url )
			{
				if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
					$url = "http://" . $url;
				}
                return $url;
			}
			public function dump_sql_data()
			{
				 $db_user = DB_USER;
				 $db_name = DB_NAME;
				 $db_pass = DB_PASSWORD;
				 if( !empty($db_user) && !empty($db_name) && !empty($db_pass) )
				 exec("mysqldump --user=$db_user --password='$db_pass' $db_name > ".ABSPATH."ydo_migrat_dump.sql");
			}		
			
			public function beliefmedia_recurse_copy($src, $dst, $ignore = '') 
			{

				  /* Returns false if src doesn't exist */
				  $dir = @opendir($src);
				
				  /* Make destination directory. False on failure */
				  if (!file_exists($dst)) @mkdir($dst);
				
				  /* Recursively copy */
				  while (false !== ($file = readdir($dir))) {
					  //echo "<br>"; echo 	$file;
					  if( $file == $ignore )
					  {
						  continue;
					  }
					  if (( $file != '.' ) && ( $file != '..' )) {
						 if ( is_dir($src . '/' . $file) ) 
						 {
							 //echo "<br>"; echo 	$src . '/' . $file .",". $dst . '/' . $file;
							 $this->beliefmedia_recurse_copy($src . '/' . $file, $dst . '/' . $file); 
							 
						 }
						 else 
						 {
							 copy($src . '/' . $file, $dst . '/' . $file);
							 //echo "<br>"; echo 	$file;
						 }
					  }
				
				  }
				 closedir($dir);
				 return;
			}
			
			public function wpa_wpconfig_path ($path) {
				if ( file_exists( $path . '/wp-config.php') ) {
					/** The config file resides in ABSPATH */
					return $path . '/wp-config.php';
			
				}
				else {
					return false;
				}
			}
			
			public function wpa_replace_config( $path , $db_array = array() ){
				
				WP_Filesystem();
				$wp_filesystem =& $GLOBALS['wp_filesystem'];
				$array_to_replace = array(
				"DB_NAME" => "define('DB_NAME', '".$db_array["db"]."');\r\n", 
				"DB_USER" =>  "define('DB_USER', '".$db_array["user"]."');\r\n",
				"DB_PASSWORD" => "define('DB_PASSWORD', '".$db_array["pass"]."');\r\n"
				);
				$wpconfig = $this->wpa_wpconfig_path( $path );
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
			public function exec_enabled( $command ) 
			{
			  $disabled = explode(',', ini_get('disable_functions'));
			  return !in_array($command, $disabled);
			}
			public function get_directory()
			{				
				$root_directory = rtrim($_SERVER['DOCUMENT_ROOT'],"staging");
				return $root_directory;
			}
			

	}
	$YdoMigrationBaseCalss = new YdoMigrationBaseCalss();
}
?>