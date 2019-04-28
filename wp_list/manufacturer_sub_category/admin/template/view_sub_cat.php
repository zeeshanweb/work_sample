<?php
if ( !class_exists('CreateManufacturer') )
{
	class CreateManufacturer extends Create_manufacturer_action 
	{
		var $frug_taxonomy = 'product_manufacturer';
		var $success_message;
		var $manufacturer_id;
		public function __construct()
		{
			add_action( 'admin_init', array( $this,'wporg_settings_init') );
			add_action( 'admin_menu', array( $this,'wporg_options_page') );
			add_action( 'admin_notices', array( $this,'frug_admin_notice__success') );
			add_action( 'admin_enqueue_scripts', array( $this,'load_custom_wp_admin_style') );
			$this->manufacturer_id = $_GET['manufacturer_id'];
		}
		public function load_custom_wp_admin_style($hook)
		{
			if($hook != 'toplevel_page_man_setting' && $hook != 'manufacturer-setting_page_product_line')
			{
				//return;
			}
			wp_enqueue_script( 'frug_custom', FRUG_PLUGIN_URL . 'assets/js/frug_custom.js' );
		}
		public function frug_admin_notice__success()
		{ 
		    if( isset($_GET['page']) && $_GET['page'] == 'man_setting' )
			{
				$message = 'Manufacturer';
			}else
			{
				return;
				$message = 'Product Line';
			}
			if( isset($_GET['message']) && $_GET['message'] ==1 )
			{		
				
				?>
				 <div class="notice notice-success is-dismissible">
					<p><?php _e( $message.' Created Successfully!', 'sample-text-domain' ); ?></p>
				 </div>
			<?php	
			  }else if( isset($_GET['message']) && $_GET['message'] == 2 )
			  { ?>
				 <div class="notice notice-error is-dismissible">
					<p><?php _e( $message.' is already exist!', 'sample-text-domain' ); ?></p>
				 </div> 
			  <?php }else if( isset($_GET['message']) && $_GET['message'] == 3 )
			  { ?>
				 <div class="notice notice-error is-dismissible">
					<p><?php _e( 'Error creating '.$message, 'sample-text-domain' ); ?></p>
				 </div> 
			  <?php }
		}	
		public function wporg_settings_init() {
		 // register a new setting for "wporg" page
		 register_setting( 'wporg', 'wporg_options' );
		 
		 // register a new section in the "wporg" page
		 add_settings_section('wporg_section_developers',__( 'The Matrix has you.', 'wporg' ),array($this,'wporg_section_developers_cb'),'wporg');
		 
		 // register a new field in the "wporg_section_developers" section, inside the "wporg" page
		 /*add_settings_field('wporg_field_pill',__( 'Pill', 'wporg' ),'wporg_field_pill_cb','wporg','wporg_section_developers', [
		 'label_for' => 'wporg_field_pill','class' => 'wporg_row','wporg_custom_data' => 'custom',]);*/
		}
	 
		public function wporg_section_developers_cb( $args ) {
		 ?>
		 <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'wporg' ); ?></p>
		 <?php
		}
		public function wporg_field_pill_cb( $args ) {
		 // get the value of the setting we've registered with register_setting()
		 $options = get_option( 'wporg_options' );
		 // output the field
		 ?>
		
		 <?php
		}
		public function wporg_options_page() {
		 // add top level menu page
		 add_submenu_page(
		 NULL,//'WPOrg',
		 'Manufacturer Setting',
		 'Manufacturer Setting',
		 'manage_options',
		 'man_setting',
		 array($this,'wporg_options_page_html')
		 );
		 add_submenu_page(NULL, __('My SubMenu Page'), __('Product Line Setting'), 'edit_themes', 'product_line', array($this,'product_line_render'));
		// add_submenu_page('man_setting', __('My SubMenu Page'), __('Manufacturer Listing'), 'edit_themes', 'manufacturer_listing', array($this,'manufacturer_listing_render'));
		}
		public function manufacturer_listing_render()
		{
			if ( ! current_user_can( 'manage_options' ) )
			{
				return;
			}
			$exampleListTable = new Example_List_Table();
            $exampleListTable->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2>Manufacture Listing</h2>
                <?php $exampleListTable->display(); ?>
            </div>
        <?php
			
		}
		public function product_line_render()
		{
			if ( ! current_user_can( 'manage_options' ) )
			{
				return;
			}
			$get_manufacturer = get_terms( array('taxonomy' => $this->frug_taxonomy,'hide_empty' => false,'depth' => 1) );
			//echo '<pre>';
			//print_r($get_manufacturer);die;
			echo $this->message;	
			acf_form_head();
			$options = array( 'field_groups' => array('1415'),'html_after_fields' => wp_nonce_field( 'create_product_line_action', 'create_product_line_name' ).'<input type="hidden" name="cat" value="'.$this->manufacturer_id.'" />','html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="Submit" />');
					
			?>
			<div class="wrap">
			 <h1>Create Product Line</h1>
			 <?php acf_form($options);?>
			 </div>
			
		<?php	
		}
		public function wporg_options_page_html() {
		 // check user capabilities
		 if ( ! current_user_can( 'manage_options' ) ) {
		 return;
		 }
		    acf_form_head();
			$options = array( 'field_groups' => array('1355'),'html_after_fields' => wp_nonce_field( 'create_manufacturer_action', 'create_manufacturer_name' ),'html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="Submit" />');
			//acf_form($options);
	 
			 ?>
			 <div class="wrap">
			 <h1>Create Manufacturer</h1>
			 <?php acf_form($options);?>
			 </div>
			 <?php
			}
	}
	if( is_admin() )
    $CreateManufacturer = new CreateManufacturer();
}