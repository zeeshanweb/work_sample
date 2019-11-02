<?php
if ( !class_exists('YDO_GFDC_SettingsPage') )
{   
class YDO_GFDC_SettingsPage
{
    
	private $options;
	public function __construct()
	{
			add_action( 'admin_menu', array( $this, 'ydo_gfdc_settings_admin' ) );
	}
	function ydo_gfdc_settings_admin() 
	{  
	
		add_menu_page(
		'Gravityform Data Capture',
		'Gravityform Data Capture',
		'manage_options',
		'benefit-options',
		array( $this,'ydo_gfdc_setting_index'));
	}
	/* Display Page
	-----------------------------------------------------------------*/
	function ydo_gfdc_setting_index() 
	{
		global $gfdc_wpdb;
		gfdf_connect_db();
	?>
		<div class="wrap">  
        	<h2>Gravityform Data Capture</h2>
			<div>
			<?php if( isset($gfdc_wpdb->error->errors['db_connect_fail'][0]) ):?>
            <?php 
			echo $gfdc_wpdb->error->errors['db_connect_fail'][0];?>
            <?php else :?>
            Database Connected Successfully!
            <?php endif;?>
            </div>  
		</div> 
	<?php
  }
}
if( is_admin() )
    $ydo_gfdc_settings_page = new YDO_GFDC_SettingsPage();
}