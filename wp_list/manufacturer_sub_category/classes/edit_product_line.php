<?php
if ( !class_exists('Edit_Product_line') )
{
	class Edit_Product_line
	{
		var $taxonomy_id;
		var $location;
		var $manufacturer_id;
		public function __construct()
		{
			add_action( 'admin_menu', array( $this, 'edit_product_plugin_menu' ) );
			add_action( 'admin_init', array( $this, 'edit_manufacturer_init_verify_post' ) );
			$this->location = '/wp-admin/edit.php?post_type=product&page=manufacturer_listing';
			$this->taxonomy_id = $_GET['product_line_id'];
			$this->manufacturer_id = $_GET['manufacturer_id'];			
			add_filter('acf/load_field', array( $this, 'edit_product_acf_load_field'));
		}
		public function edit_product_acf_load_field( $field )
		{
			//echo '<pre>';
			//print_r($field);die;
			$key_array = array('mfg_name','frugal_name','style','sku_kitchen','sku_vanity','product_class','active_option','product_line_image_1','product_line_image_2','finish','tint','design','collection','minus_sale_amount');
			if( isset($this->taxonomy_id) && !empty($this->taxonomy_id)&& in_array($field['name'], $key_array) )
			{
				$field['value'] = get_term_meta( $this->taxonomy_id, $field['name'], true);
			}
			return $field;
		}
		public function edit_product_plugin_menu()
		{
			add_submenu_page('man_setting', __('Edit Product Line'), __('Edit Product Line'), 'manage_options', 'edit_product_line', array($this,'edit_product_line_setting'));			
		}
		public function edit_manufacturer_init_verify_post()
		{
			if ( isset( $_POST['edit_product_line_name'] ) || wp_verify_nonce( $_POST['edit_product_line_name'], 'edit_product_line_action' ))
			{
				$frugal_name_category = $_POST['acf']['field_5c84bbac7b7cb'];
				$product_term_meta = array( 'mfg_name'=>$_POST['acf']['field_5c84bb987b7ca'] , 'frugal_name'=>$_POST['acf']['field_5c84bbac7b7cb'] , 'style'=>$_POST['acf']['field_5c84bbb67b7cc'] , 'sku_kitchen'=>$_POST['acf']['field_5c84bbc07b7cd'] , 'sku_vanity'=>$_POST['acf']['field_5c84bbc87b7ce'] , 'active_option'=>$_POST['acf']['field_5c84bbd87b7d0'] , 'product_class'=>$_POST['acf']['field_5c84bbd07b7cf'], 'product_line_image_1'=>$_POST['acf']['field_5c84bc207b7d1'], 'product_line_image_2'=>$_POST['acf']['field_5c84bc327b7d2'] , 'finish'=>$_POST['acf']['field_5c8fe620a73be'] , 'tint'=>$_POST['acf']['field_5c8fe7b8a73bf'] , 'design'=>$_POST['acf']['field_5c8fe7d1a73c0'] , 'collection'=>$_POST['acf']['field_5c8fe7e0a73c1'] , 'minus_sale_amount'=>$_POST['acf']['field_5c929954e6a0e'] );
				if ( !empty($this->taxonomy_id) )
				{
					if( isset($frugal_name_category) && !empty($frugal_name_category) )
					wp_update_term($this->taxonomy_id, FRUG_TAXONOMY, array('name' => $frugal_name_category));
					$term_id = $term_id['term_id'];						
					$location = add_query_arg( 'message', 1, $referer );	
					foreach( $product_term_meta as $man_key=>$man_val )
					{
					  //if( empty($man_val) || !array_key_exists( $man_key , $_POST ) )
					  //continue;	
					  update_term_meta($this->taxonomy_id, $man_key, $man_val);
					}
				}
			}
		}
		public function edit_product_line_setting()
		{
			
		 // check user capabilities
		 if ( ! current_user_can( 'manage_options' ) ) {
		 return;
		 }
		 if( is_subcategory($this->taxonomy_id) === false )
		 {
			wp_redirect( manufacturer_redirect_url() );exit;
		 }
		 acf_form_head();
		 $options = array( 'field_groups' => array('1415'),'html_after_fields' => wp_nonce_field( 'edit_product_line_action', 'edit_product_line_name' ),'html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="Update" />','updated_message' => __("Product line updated", 'acf'),);
			//acf_form($options);	 
			 ?>
			 <div class="wrap">
			 <h1>Edit Product Line</h1>
			 <?php acf_form($options);?>
			 </div>
			 <?php
			
		}
	}
	if( is_admin() )
    $Edit_Product_line = new Edit_Product_line();
}