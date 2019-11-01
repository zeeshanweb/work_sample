<?php
class EditDimenison {
	// class instance
	static $instance;
	// customer WP_List_Table object
	public $customers_obj;
	var $pricing_id;
	var $acf_group_id = 261;
	public function __construct() 
	{
		global $wpdb;
		$this->wpdb = $wpdb;
		if( isset($_GET['id']) && !empty($_GET['id']) && $_GET['page'] == 'edit_dimension' )
		{
			$this->pricing_id = $_GET['id'];
		}
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
		add_filter('acf/load_field', array( $this, 'edit_dimension_acf_load_field'));
		add_action( 'admin_init', array( $this, 'edit_dimension_init_verify_post' ) );
		add_action('acf/validate_value', array( $this, 'ken_acf_validate_save_post'), 10, 4);
		add_action('acf/save_post', array( $this, 'my_acf_save_post'), 10, 1);
		//add_action('acf/validate_save_post', array( 'my_acf_validate_save_post'), 10, 0);
		add_action('admin_footer', array( $this,'admin_footer_js'));
	}
	public function admin_footer_js()
	{
		if( isset($_GET['page']) && $_GET['page'] == 'add_new_item' )
		{
			$item_code = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}woo_pricing WHERE tooth_count = 'Standard Tooth' order by id DESC" );
			$item_code = json_encode($item_code[0]);
			?>
            <script>
			var test_data = '<?php echo  $item_code;?>';
			jQuery(document).ready(function (){
				jQuery('select#acf-field_5db7e60d05f68').on('change', function() {
				 if( this.value == 'Standard Tooth' )
				 {
					var data = jQuery.parseJSON(test_data);
					 jQuery.each(data, function(key,value) {
						 //console.log(data.item_code);
						 if( key == 'status' || key == 'selected_box' )
						 {
							jQuery("div."+key+" select").val(value).change();
						 }else
						 {
							 jQuery("div."+key+" input").val(value);
						 }
					 });
				 }else
				 {
					jQuery(this).closest('form').find("input[type=text], textarea").val("");
					jQuery("div.status select").val('').change();
				 }				 
				});
			});			
            </script>
            <?php
		}
		?>
        <style>
        /*li.toplevel_page_wp_dimension_listing ul.wp-submenu{ display:none;}*/
		li.toplevel_page_wp_dimension_listing ul.wp-submenu li:nth-child(3),li.toplevel_page_wp_dimension_listing ul.wp-submenu li:nth-child(4),li.toplevel_page_wp_dimension_listing ul.wp-submenu li:nth-child(7){ display:none;}
        </style>
        <?php
	}
	public function my_acf_save_post( $post_id )
	{
		$this->acf_verify_post_data();
	}
	public function my_acf_validate_save_post()
	{
		acf_add_validation_error( 'my_input', 'Please check this input to proceed' );
	}
	public function ken_acf_validate_save_post($valid, $value, $field, $input)
	{
		if( !$valid )
		{
			return $valid;
		}
		if( ($field['name'] == 'width' && !is_numeric($value)) || ($field['name'] == 'thickness' && !is_numeric($value)) || ($field['name'] == 'sales_price_ft' && !is_numeric($value)) || ($field['name'] == 'sales_price_weld' && !is_numeric($value)) || ($field['name'] == 'packaging' && !is_numeric($value)) )
		{
			$valid = 'Please enter numeric no';
		}else if( $field['name'] == 'item_code' && !empty($value) )
		{
			$this->pricing_id = $_POST['edit_id'];
			if( !empty($this->pricing_id) )
			{
				$item_code = $this->wpdb->get_row( "SELECT * FROM {$this->wpdb->prefix}woo_pricing WHERE item_code = '$value' and id != {$this->pricing_id}" );
			}else
			{
				$item_code = $this->wpdb->get_row( "SELECT * FROM {$this->wpdb->prefix}woo_pricing WHERE item_code = '$value'" );
			}			
			if( $item_code )
			{
				$valid = 'This code is already exist.';
			}
		}
		return $valid;
	}
	public function acf_verify_post_data()
	{
		if ( isset( $_POST['add_new_item_name'] ) || wp_verify_nonce( $_POST['add_new_item_name'], 'add_new_item_action' ))
		{
			$update_array = $this->get_array_field();
			if( !empty($update_array) )
			{
				$table = $this->wpdb->prefix.woo_pricing;
				$this->wpdb->insert($table,$update_array);				
			}			
		}else if ( isset( $_POST['edit_dimension_name'] ) || wp_verify_nonce( $_POST['edit_dimension_name'], 'edit_dimension_action' ))
		{
			$update_array = $this->get_array_field();
			if( !empty($update_array) && !empty($this->pricing_id) )
			{
				$table = $this->wpdb->prefix.woo_pricing;
				$this->wpdb->update($table,$update_array,array( 'id' => $this->pricing_id ));				
			}			
		}
	}
	public function edit_dimension_init_verify_post()
	{
		if (!defined('DOING_AJAX') || !DOING_AJAX) 
		{
			acf_form_head();
		}		
	}
	public function get_array_field()
	{
		$acf_key = array( 'tooth_count'=> stripslashes_deep($_POST['acf']['field_5db7e60d05f68']) , 'description'=> stripslashes_deep($_POST['acf']['field_5db7eb5a830a5']) , 'item_code'=> stripslashes_deep($_POST['acf']['field_5db7eb67830a6']) , 'width'=> stripslashes_deep($_POST['acf']['field_5db7ec0f830a7']) , 'thickness'=> stripslashes_deep($_POST['acf']['field_5db7ec15830a8']) , 'tooth_spacing'=> stripslashes_deep($_POST['acf']['field_5db7ec1e830a9']) , 'sales_price_ft'=> stripslashes_deep($_POST['acf']['field_5db7ec5c830aa']), 'sales_price_weld'=> stripslashes_deep($_POST['acf']['field_5db7ec68830ab']), 'packaging'=> stripslashes_deep($_POST['acf']['field_5db7ec74830ac']) , 'selected_box'=> stripslashes_deep($_POST['acf']['field_5dbab389c981f']), 'status'=> stripslashes_deep($_POST['acf']['field_5db7e5d405f67']) );
		return $acf_key;
	}
	public function redirect_to_dimension_lidting()
	{
		wp_redirect( admin_url( '/admin.php?page=wp_dimension_listing' ) );
        exit;
	}
	public function edit_dimension_acf_load_field( $field )
	{
		$pricing_data = '';
		if( !empty($this->pricing_id) )
		{
			$pricing_data = $this->wpdb->get_row( "SELECT * FROM {$this->wpdb->prefix}woo_pricing WHERE id = {$this->pricing_id}" );			
		}
		if( empty($pricing_data) && $_GET['page'] == 'edit_dimension' )
		{
			$this->redirect_to_dimension_lidting();
		}		
		$key_array = array('status','tooth_count','description','item_code','width','thickness','tooth_spacing','sales_price_ft','sales_price_weld','packaging','box_length','box_width','box_height');
		if( !empty($pricing_data) && in_array($field['name'], $key_array) )
		{
			$acf_key = $field['name'];
			if( isset($pricing_data->$acf_key) )
			{
				$field['value'] = $pricing_data->$acf_key;//get_term_meta( '', $field['name'], true);
			}			
		}
		if( $field['name'] == 'selected_box' )
		{
		    $field['choices'] = array();
			$choices = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}ken_boxes" );
			foreach( $choices as $choice )
			{
				$field['choices'][ $choice->id ] = $choice->boxes;
			}
			if( isset($pricing_data->selected_box) && !empty($pricing_data->selected_box) )
			$field['value'] = $pricing_data->selected_box;
		}
		return $field;
	}
	public static function set_screen( $status, $option, $value ) 
	{
		return $value;
	}
	public function plugin_menu() {

		$hook = add_menu_page(
			'Product Grid',
			'Product Grid',
			'manage_options',
			'wp_dimension_listing',
			[ $this, 'plugin_settings_page' ]
		);

		add_submenu_page( 'wp_dimension_listing', 'Edit Dimension', 'Edit Dimension','manage_options', 'edit_dimension',[ $this,'edit_dimension_page_callback' ]);
		add_submenu_page( 'wp_dimension_listing', 'Add Dimension', 'Add Dimension','manage_options', 'add_new_item',[ $this,'add_new_item_page_callback' ]);
		add_action( "load-$hook", [ $this, 'screen_option' ] );

	}
	public function add_new_item_page_callback()
	{
		?>
        <?php //acf_form_head(); 
	      $options = array( 'field_groups' => array($this->acf_group_id),'html_after_fields' => wp_nonce_field( 'add_new_item_action', 'add_new_item_name', true, false ),'html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="Submit" />','updated_message' => __("Item added successfully", 'acf'),);?>
    <div class="wrap">
        <h1><?php _e( 'Add Item', 'textdomain' ); ?></h1>
        <?php acf_form($options);?>
    </div>
        <?php
	}
	public function edit_dimension_page_callback() { 
    ?>
    <?php //acf_form_head(); 
	      $options = array( 'field_groups' => array($this->acf_group_id),'html_after_fields' => wp_nonce_field( 'edit_dimension_action', 'edit_dimension_name', true, false ).'<input type="hidden" name="edit_id" value="'.$_GET['id'].'">','html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="Update" />','updated_message' => __("Dimension updated", 'acf'),);?>
    <div class="wrap">
        <h1><?php _e( 'Edit Dimension', 'textdomain' ); ?></h1>
        <?php acf_form($options);?>
    </div>
    <?php
}
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2>Product Grid</h2>
            <p style="float:right;" class="add_manufacturer"><a href="/wp-admin/admin.php?page=add_new_item"><input type="button" name="button" id="button" class="button button-primary" value="Add New Item"></a></p>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2" style="margin: 0;">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Dimension',
			'default' => 5,
			'option'  => 'customers_per_page'
		];
		add_screen_option( $option, $args );
		$this->customers_obj = new Customers_List();
	}
	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}
add_action( 'plugins_loaded', function () {
	EditDimenison::get_instance();
} );