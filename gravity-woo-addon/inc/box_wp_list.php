<?php
if ( !class_exists('BoxWpList') )
{
	class BoxWpList
	{
		static $instance;
		public $box_obj;
		var $acf_group_id = 277;
		var $box_id;
		public function __construct()
		{
			global $wpdb;
			$this->wpdb = $wpdb;
			if( isset($_GET['box_id']) && !empty($_GET['box_id']) && $_GET['page'] == 'edit_boxes' )
			{
				$this->box_id = $_GET['box_id'];
			}
			add_filter( 'set-screen-option', [ __CLASS__, 'box_set_screen' ], 10, 3 );
			add_action( 'admin_menu', array( $this, 'box_add_plugin_page' ) );
			add_filter('acf/load_field', array( $this, 'edit_box_acf_load_field'));
			add_action('acf/save_post', array( $this, 'acf_save_post_boxes'), 10, 1);
		}
		public function acf_save_post_boxes( $post_id )
		{
			$this->acf_verify_box_post_data();
		}
		public function acf_verify_box_post_data()
		{
			if ( isset( $_POST['edit_box_name'] ) || wp_verify_nonce( $_POST['edit_box_name'], 'edit_box_action' ))
			{
				$update_array = $this->get_box_array_field();
				if( !empty($update_array) && !empty($this->box_id) )
				{
					$table = $this->wpdb->prefix.ken_boxes;
					$this->wpdb->update($table,$update_array,array( 'id' => $this->box_id ));				
				}			
			}
		}
		public function get_box_array_field()
		{
			$acf_key = array( 'boxes'=> stripslashes_deep($_POST['acf']['field_5dbaa8661fb96']) , 'length'=> stripslashes_deep($_POST['acf']['field_5dbaa8701fb97']) , 'width'=> stripslashes_deep($_POST['acf']['field_5dbaa87f1fb98']) , 'height'=> stripslashes_deep($_POST['acf']['field_5dbaa8881fb99']));
			return $acf_key;
		}
		public function box_set_screen( $status, $option, $value )
		{
			return $value;
			$option = 'per_page';
			$args   = [
				'label'   => 'Box',
				'default' => 20,
				'option'  => 'box_per_page'
			];
			add_screen_option( $option, $args );
			$this->box_obj = new BoxListClass();
		}
		public function box_add_plugin_page()
		{
			$hook = add_submenu_page( 'wp_dimension_listing', 'Box Listing', 'Box Listing','manage_options', 'box_listing',[ $this,'box_listing_page_callback' ]);
			add_submenu_page( 'wp_dimension_listing', 'Edit Boxes', 'Edit Boxes','manage_options', 'edit_boxes',[ $this,'edit_boxes_page_callback' ]);
			add_action( "load-$hook", [ $this, 'screen_option' ] );
		}
		public function screen_option() 
		{
			$option = 'per_page';
			$args   = [
				'label'   => 'Box',
				'default' => 20,
				'option'  => 'box_per_page'
			];
			add_screen_option( $option, $args );
			$this->box_obj = new BoxListClass();
		}
		public function redirect_to_box_listing()
		{
			wp_redirect( admin_url( '/admin.php?page=box_listing' ) );
			exit;
		}
		public function edit_box_acf_load_field( $field )
		{
			$box_data = '';
			if( !empty($this->box_id) )
			{
				$box_data = $this->wpdb->get_row( "SELECT * FROM {$this->wpdb->prefix}ken_boxes WHERE id = {$this->box_id}" );			
			}
			if( empty($box_data) && $_GET['page'] == 'edit_boxes' )
			{
				$this->redirect_to_box_listing();
			}		
			$key_array = array('boxes','length','width','height');
			if( in_array($field['name'], $key_array) )
			{
				$acf_key = $field['name'];
				if( isset($box_data->$acf_key) )
				{
					$field['value'] = $box_data->$acf_key;//get_term_meta( '', $field['name'], true);
				}			
			}
			return $field;
		}
		public function edit_boxes_page_callback()
		{
			 $options = array( 'field_groups' => array($this->acf_group_id),'html_after_fields' => wp_nonce_field( 'edit_box_action', 'edit_box_name', true, false ).'<input type="hidden" name="edit_box_id" value="'.$_GET['box_id'].'">','html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="Update" />','updated_message' => __("Box updated", 'acf'),);?>
             <div class="wrap">
                <h1><?php _e( 'Edit Box', 'textdomain' ); ?></h1>
                <?php acf_form($options);?>
            </div>
            <?php
		}
		public function box_listing_page_callback()
		{
			?>
            <div class="wrap">
			<h2>Box Grid</h2>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2" style="margin: 0;">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->box_obj->prepare_items();
								$this->box_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
            <?php
		}
		public static function get_instance()
		{
			if ( ! isset( self::$instance ) )
			{
				self::$instance = new self();
			}
			return self::$instance;
		}
	}
}
add_action( 'plugins_loaded', function ()
{
	BoxWpList::get_instance();
});