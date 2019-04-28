<?php
if ( !class_exists('Edit_manufacturer_action') )
{
	class Edit_manufacturer_action
	{
		var $taxonomy_id;
		public function __construct()
		{
			$this->taxonomy_id = $_GET['taxonomy_id'];
			add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
			add_action( 'admin_init', array( $this, 'edit_manufacturer_init_verify_post' ) );
			add_filter('acf/load_field', array( $this, 'edit_manufacturer_acf_load_field'));			
		}
		public function edit_manufacturer_acf_load_field( $field )
		{
			//echo '<pre>';
			//print_r($field);die;
			$key_array = array('company_name','short_name','address_street_1','address_street_2','city','state','zip','phone','primary_poc','email1','email2','multiplier','markup_value','lowest_allowable_selling_value');
			if( isset($this->taxonomy_id) && !empty($this->taxonomy_id) && in_array($field['name'], $key_array) )
			{
				$field['value'] = get_term_meta( $this->taxonomy_id, $field['name'], true);
			}
			return $field;
		}
		public function plugin_menu()
		{
			add_submenu_page(NULL, __('Edit Manufacturer'), __('Edit Manufacturer'), 'manage_options', 'edit_manufacturer', array($this,'edit_manufacturer_setting'));			
		}
		public function edit_manufacturer_init_verify_post()
		{
			if ( isset( $_POST['edit_manufacturer_name'] ) || wp_verify_nonce( $_POST['edit_manufacturer_name'], 'edit_manufacturer_action' ))
			{
				$manufa_name = $_POST['acf']['field_5c7eaa3005b69'];
				$manufaturer_term_meta = array( 'company_name'=>$manufa_name , 'short_name'=>$_POST['acf']['field_5c7eac1a11e60'] , 'address_street_1'=>$_POST['acf']['field_5c7ebad434fa0'] , 'address_street_2'=>$_POST['acf']['field_5c7ebb0e34fa1'] , 'city'=>$_POST['acf']['field_5c7ebb1e34fa2'] , 'state'=>$_POST['acf']['field_5c7ebb2a34fa3'] , 'zip'=>$_POST['acf']['field_5c7ebb3434fa4'] , 'phone'=>$_POST['acf']['field_5c7ebb3f34fa5'] , 'primary_poc'=>$_POST['acf']['field_5c7ebb4c34fa6'] , 'email1'=>$_POST['acf']['field_5c7ebb5234fa7'] , 'email2'=>$_POST['acf']['field_5c7ebb5834fa8'] , 'multiplier'=>$_POST['acf']['field_5c7ebb6034fa9'] , 'markup_value'=>$_POST['acf']['field_5c7ebb7234faa'] , 'lowest_allowable_selling_value'=>$_POST['acf']['field_5c7ebb7f34fab'] );
				if ( !empty($this->taxonomy_id) && isset($manufa_name) && !empty($manufa_name) )
				{
					wp_update_term($this->taxonomy_id, FRUG_TAXONOMY, array('name' => $manufa_name));
					$term_id = $term_id['term_id'];						
					$location = add_query_arg( 'message', 1, $referer );	
					foreach( $manufaturer_term_meta as $man_key=>$man_val )
					{
					  //if( empty($man_val) || !array_key_exists( $man_key , $_POST ) )
					  //continue;	
					  update_term_meta($this->taxonomy_id, $man_key, $man_val);
					}
				}
			}
		}
		public function edit_manufacturer_setting()
		{
			
		 // check user capabilities
		 if ( ! current_user_can( 'manage_options' ) ) {
		 return;
		 }
		 $check_if_exist = get_term( $this->taxonomy_id , FRUG_TAXONOMY );
		 if( empty($check_if_exist) )
		 {
			 wp_redirect( manufacturer_redirect_url() );exit;
	     }
		 acf_form_head();
			$options = array( 'field_groups' => array('1355'),'html_after_fields' => wp_nonce_field( 'edit_manufacturer_action', 'edit_manufacturer_name' ),'html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="Update" />','updated_message' => __("Manufacturer updated", 'acf'));
			//acf_form($options);
	 
			 ?>
			 <div class="wrap">
			 <h1>Edit Manufacturer</h1>
			 <?php acf_form($options);?>             
			 </div>
			 <?php
			
		}
	}
	if( is_admin() )
    $Edit_manufacturer_action = new Edit_manufacturer_action();
}