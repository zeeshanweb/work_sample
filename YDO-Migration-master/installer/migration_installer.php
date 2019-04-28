<?php
if ( !class_exists('YdoMigrationInstaller') )
{
	class YdoMigrationInstaller
	{ 
	   var $db = 'test_db_name';
	   var $user = 'test_user_name';
	   var $pass = 'test_password';
	   var $prefix = 'test_prefix';
	   public function __construct()
	   {
		   $this->import_db();
	   }
	   public function extract_zip()
	   {
		   $file = 'ydo_migration.zip';
		   $path = pathinfo( realpath( $file ), PATHINFO_DIRNAME );
		   $zip = new ZipArchive;
		   $res = $zip->open($file);
		   if ($res === TRUE)
		   {
			   $zip->extractTo( $path );
			   $zip->close();
		   }else
		   {
			   echo "Doh! I couldn't open $file";
		   }
		   $this->repace_config_file();
	   }
	   public function repace_config_file()
	   {
		   $array_to_replace = array(
				"DB_NAME" => "define('DB_NAME', '".$this->db."');", 
				"DB_USER" =>  "define('DB_USER', '".$this->user."');",
				"DB_PASSWORD" => "define('DB_PASSWORD', '".$this->pass."');"
				);
			$wpconfig = dirname( __FILE__ ).'/wp-config.php';
			$content = file( $wpconfig );
			foreach( $content as $key => $value ) {
					
					foreach( $array_to_replace as $r_key => $r_value ) {
						if( false !== strpos( $value, $r_key ) ) {
							$content[$key] = $r_value;
						}	
					}
			
				}
				$content = implode( $content );
				file_put_contents( $wpconfig, $content );
				unlink(dirname( __FILE__ )."/migration_installer.php");
	   }
	   public function import_db()
	   {
		  exec("mysql --user=$this->user --password='$this->pass' $this->db < ".dirname( __FILE__ )."/ydo_migrat_dump.sql");
		   $this->update_site_url();
	   }
	   public function update_site_url()
	   {
		   $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		   $actual_link = str_replace( 'migration_installer.php' , '' , $actual_link );
		   $conn = new mysqli('localhost', $this->user, $this->pass, $this->db);
		   $sql = "UPDATE ".$this->prefix."options SET option_value = '$actual_link' WHERE option_name = 'home' OR option_name = 'siteurl'";
		   if ($conn->query($sql) === TRUE)
		   {
			   echo "Site is successfully imported.<br> You can now access your site <a href=".$actual_link.">Here</a>";
		   }else
		   {
			   echo "Error updating record: " . $conn->error;
		   }
		   $conn->close();
		   $this->extract_zip();
	   }
	}
	$YdoMigrationInstaller = new YdoMigrationInstaller();
}