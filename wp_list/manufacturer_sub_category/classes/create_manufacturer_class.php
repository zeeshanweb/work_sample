<?php
if ( !class_exists('Create_manufacturer_action') )
{
	class Create_manufacturer_action
	{
		var $frug_taxonomy = 'product_manufacturer';
		var $message = '';
		public function __construct()
		{
			global $wpdb;			
			add_action( 'admin_init', array( $this, 'admin_init_verify_post' ) );
		}
		public function admin_init_verify_post()
		{
			$referer = wp_unslash( $_SERVER['REQUEST_URI'] );
			if ( isset( $_POST['create_manufacturer_name'] ) || wp_verify_nonce( $_POST['create_manufacturer_name'], 'create_manufacturer_action' ))
			{
				$term = $_POST['acf']['field_5c7eaa3005b69'];
				if( empty($term) )
				{
					$location = add_query_arg( 'message', 3, $referer );
					wp_redirect( $location );exit;
				}
				if( isset($term) && !empty($term) && !term_exists( $term , $this->frug_taxonomy ) )
				{
					$term_id = wp_insert_term( $term, $this->frug_taxonomy );
					$manufaturer_term_meta = array( 'company_name'=>$_POST['acf']['field_5c7eaa3005b69'] , 'short_name'=>$_POST['acf']['field_5c7eac1a11e60'] , 'address_street_1'=>$_POST['acf']['field_5c7ebad434fa0'] , 'address_street_2'=>$_POST['acf']['field_5c7ebb0e34fa1'] , 'city'=>$_POST['acf']['field_5c7ebb1e34fa2'] , 'state'=>$_POST['acf']['field_5c7ebb2a34fa3'] , 'zip'=>$_POST['acf']['field_5c7ebb3434fa4'] , 'phone'=>$_POST['acf']['field_5c7ebb3f34fa5'] , 'primary_poc'=>$_POST['acf']['field_5c7ebb4c34fa6'] , 'email1'=>$_POST['acf']['field_5c7ebb5234fa7'] , 'email2'=>$_POST['acf']['field_5c7ebb5834fa8'] , 'multiplier'=>$_POST['acf']['field_5c7ebb6034fa9'] , 'markup_value'=>$_POST['acf']['field_5c7ebb7234faa'] , 'lowest_allowable_selling_value'=>$_POST['acf']['field_5c7ebb7f34fab'] );
					if ( $term_id && !is_wp_error( $term_id ) )
					{
						$term_id = $term_id['term_id'];						
						$location = add_query_arg( 'message', 1, $referer );	
						foreach( $manufaturer_term_meta as $man_key=>$man_val )
						{
						  //if( empty($man_val) || !array_key_exists( $man_key , $_POST ) )
						  //continue;	
						  update_term_meta($term_id, $man_key, $man_val);
						}
						wp_redirect( manufacturer_view_url().$term_id );exit;
					}else
					{
						$location = add_query_arg( 'message', 3, $referer );
					    wp_redirect( $location );exit;
					}	            
				}else
				{
					$location = add_query_arg( 'message', 2, $referer );
					wp_redirect( $location );exit;
				}
			}
			if ( isset( $_POST['create_product_line_name'] ) || wp_verify_nonce( $_POST['create_product_line_name'], 'create_product_line_action' ))
			{
				//echo '<pre>';
				//print_r($_POST['acf']['field_5c84bb987b7ca']);die;
				$term_name_product_line = $_POST['acf']['field_5c84bbac7b7cb'];
				$parent_term = $_POST['cat'];
				if( empty($term_name_product_line) )
				{
					$location = add_query_arg( 'message', 3, $referer );
					wp_redirect( $location );exit;
				}
				if( isset($parent_term) && !empty($parent_term) && !term_exists( $term_name_product_line , $this->frug_taxonomy ) )
				{
					$term_id_product_line = wp_insert_term( $term_name_product_line, $this->frug_taxonomy,array('parent'=> $parent_term ) );
					$product_term_meta = array( 'mfg_name'=>$_POST['acf']['field_5c84bb987b7ca'] , 'frugal_name'=>$_POST['acf']['field_5c84bbac7b7cb'] , 'style'=>$_POST['acf']['field_5c84bbb67b7cc'] , 'sku_kitchen'=>$_POST['acf']['field_5c84bbc07b7cd'] , 'sku_vanity'=>$_POST['acf']['field_5c84bbc87b7ce'] , 'active_option'=>$_POST['acf']['field_5c84bbd87b7d0'] , 'product_class'=>$_POST['acf']['field_5c84bbd07b7cf'], 'product_line_image_1'=>$_POST['acf']['field_5c84bc207b7d1'], 'product_line_image_2'=>$_POST['acf']['field_5c84bc327b7d2'] , 'finish'=>$_POST['acf']['field_5c8fe620a73be'] , 'tint'=>$_POST['acf']['field_5c8fe7b8a73bf'] , 'design'=>$_POST['acf']['field_5c8fe7d1a73c0'] , 'collection'=>$_POST['acf']['field_5c8fe7e0a73c1'] , 'minus_sale_amount'=>$_POST['acf']['field_5c929954e6a0e'] );
					if ( $term_id_product_line && !is_wp_error( $term_id_product_line ) )
					{
						$term_id = $term_id_product_line['term_id'];
						$referer = wp_unslash( $_SERVER['REQUEST_URI'] );
						$location = add_query_arg( 'message', 1, $referer );	
						foreach( $product_term_meta as $man_key=>$man_val )
						{
						  //if( empty($man_val) || !array_key_exists( $man_key , $_POST ) )
						  //continue;	
						  update_term_meta($term_id, $man_key, $man_val);
						}
						wp_redirect( manufacturer_view_url().$parent_term );exit;
					}else
					{
						$location = add_query_arg( 'message', 3, $referer );
					    wp_redirect( $location );exit;
					}
				}else
				{
					$location = add_query_arg( 'message', 2, $referer );
					wp_redirect( $location );exit;
				}
			}
		}
	}
	if( is_admin() )
    $Create_manufacturer_action = new Create_manufacturer_action();
}