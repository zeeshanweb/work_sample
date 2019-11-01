<?php
if ( !class_exists('GravityWooFunction') )
{
	class GravityWooFunction
	{
		var $custom_list_validation = false;
		var $product_title;
		var $variable = array();
		var $product_id = 212;
		var $invoice_total;
		var $woo_session_var = 'invoice_total';
		var $all_entered_data = 'all_entered_data';
		var $no_of_boxes = 1;
		var $product_qty = 1;
		var $options;
		public function __construct()
		{
			global $wpdb;
			$this->wpdb = $wpdb;			
			$this->check_for_custom_validation();
			add_action( 'gform_after_submission_1', array( $this,'gform_after_submission_func'), 10, 2 );	
			add_filter( 'gform_validation', array( $this,'ken_custom_validation') );
			add_action( 'wp_footer', array( $this,'wp_footer_func') );
			//add_filter('woocommerce_product_get_price', array( $this,'kenne_product_custom_price'), 10, 2);	
			add_action( 'woocommerce_payment_complete', array( $this,'woocommerce_payment_complete_func') );
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this,'ken_add_custom_order_line_item_meta'),10,4 );
			//add_action( 'woocommerce_add_order_item_meta', array( $this,'add_order_item_meta'), 10, 2 );	
			add_filter( 'woocommerce_get_item_data', array( $this,'woocommerce_get_item_data_func'), 10 , 2 );
			add_filter( 'woocommerce_cart_item_name', array( $this,'woocommerce_cart_item_name_func') );
			add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this,'woocommerce_cart_item_name_func') );
			add_action( 'woocommerce_before_calculate_totals', array( $this,'woocommerce_cart_product_price_func'), 10 , 1 );
			add_action( 'woocommerce_checkout_before_order_review',  array( $this,'woocommerce_checkout_billing_func') ); 
			add_filter( 'woocommerce_checkout_fields', array( $this,'woocommerce_checkout_fields_func') , 10 , 1 );
			add_action( 'wp', array( $this,'condition_func') );
			add_filter( 'woocommerce_product_get_length', array( $this,'woocommerce_product_get_length_func') );
			add_filter( 'woocommerce_product_get_width', array( $this,'woocommerce_product_get_width_func') );
			add_filter( 'woocommerce_product_get_height', array( $this,'woocommerce_product_get_height_func') );
			add_filter( 'woocommerce_product_get_weight', array( $this,'woocommerce_product_get_weight_func') );
			add_action('admin_footer', array( $this,'admin_head_func'));
			add_action('woocommerce_admin_order_item_headers', array( $this,'woocommerce_admin_order_item_headers_func'));
			//add_filter( 'woocommerce_package_rates', array( $this,'unset_ups_shipping_method') , 10, 2 );
			//echo $this->get_discount_amount( 34.75 );die;
		}
		public function woocommerce_admin_order_item_headers_func()
		{
			?>
            <th class="item_costs" data-sort="float"></th>
            <th class="item_costs" data-sort="float"></th>
            <?php
		}
		public function admin_head_func()
		{
			?>
            <style>
            table.woocommerce_order_items tr.item  td.item_cost,table.woocommerce_order_items tr.item  td.quantity { visibility:hidden;}table.woocommerce_order_items tr th.item_cost,table.woocommerce_order_items tr th.quantity{ display:none;}
            </style>
            <?php
		}
		public function get_cart_item_data()
		{
			$cart_data = WC()->cart->get_cart();
			if( is_array($cart_data) || is_object($cart_data) )
			{
				foreach ( $cart_data as $cart_item )
				{
					return $cart_item;
				}
			}
		}
		public function woocommerce_product_get_length_func()
		{
			//echo '<pre>';
			//print_r($this->variable['product_dimension']);die;
			$cart_item = $this->get_cart_item_data();
			return $cart_item['product_dimension']['length'];
		}
		public function woocommerce_product_get_width_func()
		{
			$cart_item = $this->get_cart_item_data();
			return $cart_item['product_dimension']['width'];
		}
		public function woocommerce_product_get_height_func()
		{
			$cart_item = $this->get_cart_item_data();
			return $cart_item['product_dimension']['height'];
		}
		public function woocommerce_product_get_weight_func()
		{
			$cart_item = $this->get_cart_item_data();
			return $cart_item['calculated_val']['box_weight'];
		}
		public function condition_func()
		{
			if( is_product() )
			{
				wp_redirect( home_url() ); exit;
			}
		}
		public function woocommerce_checkout_fields_func($checkout_fields )
		{
			$cart_data = WC()->cart->get_cart();
			//echo '<pre>';
			//print_r($cart_data);die;
			if( is_array($cart_data) || is_object($cart_data) )
			{
				foreach ( $cart_data as $cart_item )
				{
					$email = $cart_item['contact']['email'];
					$phone = $cart_item['contact']['phone'];
				}			
				if( !is_user_logged_in() )
				{
					$checkout_fields['billing']['billing_email']['default'] = $email;
					$checkout_fields['billing']['billing_phone']['default'] = $phone;
				}
			}						
	        return $checkout_fields;
		}
		public function woocommerce_checkout_billing_func( )
		{
			echo '<p>if you needs a different qty than the standard then you will need to call our toll free number and we will take those orders over the phone</p>';
		}
		public function woocommerce_cart_product_price_func( $cart )
		{
			if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			return;
			//if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
			//return;			
			foreach ( $cart->get_cart() as $item ) 
			{
               //echo '<pre>';
			   //print_r($item);die;total_revenue
			   //$item_ptice = $item['calculated_val']['invoice_total'];
			   $item_ptice = $item['calculated_val']['total_revenue'];
			   $item_weight = $item['calculated_val']['box_weight'];
			   $width = $item['input_val']['width'];
			   $length = $item['input_val']['length'] / 12;
			   $tooth_spacing = $item['input_val']['tooth_spacing'];
			   $box_quantity = $item['input_val']['box_quantity'];
			   if( !empty($item_ptice) )
			   {
				  $item['data']->set_price( $item_ptice / $box_quantity );
				  //$item['data']->set_weight( $item_weight ); 
				 // $item['data']->set_width( $width );
				 // $item['data']->set_length( $length ); 
			   }			   
            }
		}
		public function woocommerce_cart_item_name_func()
		{
			return '';
		}
		public function woocommerce_get_item_data_func( $item_data, $cart_item )
		{
			$input_val = $cart_item['item_meta'];
			if( is_array($input_val) || is_object($input_val) )
			{
				foreach( $input_val as $key => $val )
				{
					$item_data[] = array(
					'key'     => __( $key, 'iconic' ),
					'value'   => wc_clean( $val ),
					'display' => '',
					 );
				}
			}		
			return $item_data;
		}
		/*public static function add_order_item_meta($item_id, $values)
		{
			$get_all_entered_data = WC()->session->get( $this->all_entered_data );
			$input_data = $get_all_entered_data['input_val'];
			//wc_update_order_item_meta($item_id, $key, $value);
		}*/
		public function ken_add_custom_order_line_item_meta( $item, $cart_item_key, $values, $order )
		{
			$get_all_entered_data = WC()->session->get( $this->all_entered_data );
			$meta_value = $values['item_meta'];
			$input_val = $values['input_val'];
			foreach( $meta_value as $key=>$value )
			{
				$item->add_meta_data( $key , $value );
			}
			foreach( $input_val as $key=>$value )
			{
				if( $key == 'length' )
				{
					continue;
				}
				$item->add_meta_data( ucwords(str_replace('_',' ',$key)) , $value );
			}
			//$item->add_meta_data('all_entered_data','3sdds');
		}
		public function unset_woo_variable()
		{
			WC()->session->set( $this->woo_session_var, '' );
		}
		public function woocommerce_payment_complete_func( $order_id )
		{
			$this->unset_woo_variable();
		}
		public function kenne_product_custom_price( $price, $product )
		{
			$get_price = WC()->session->get( $this->woo_session_var );
			if( !empty($get_price) )
			{
				return $get_price;
			}
			return $price;
		}
		public function check_for_custom_validation()
		{
			//$get_post = @$_POST['input_17'];
			//if( isset($get_post) && count(array_filter($get_post)) != count($get_post) )
			//{
				//$this->custom_list_validation = true;//commented for new functionality
			//}
		}
		public function get_no_of_boxes( $data = '' )
		{
			if( empty($data) )
			{
				return '';
			}
			/*if( $length < 18 )
			{
				$this->no_of_boxes = 1;
				$dimension_array = array( 'length' => '18','width' => '18','height' => '18' );
			}else if( $length > 18 && $length <= 20 )
			{
				$this->no_of_boxes = 3;
				$dimension_array = array( 'length' => '20','width' => '20','height' => '20' );
			}else if( $length > 20 && $length <= 24 )
			{
				$this->no_of_boxes = 4;
				$dimension_array = array( 'length' => '24','width' => '24','height' => '12' );
			}else if( $length > 24 && $length <= 26 )
			{
				$this->no_of_boxes = 6;
				$dimension_array = array( 'length' => '26','width' => '26','height' => '22' );
			}else if( $length > 27 && $length <= 34 )
			{
				$this->no_of_boxes = 7;
				$dimension_array = array( 'length' => '34','width' => '17','height' => '24' );
			}else if( $length > 34 && $length <= 42 )
			{
				$this->no_of_boxes = 8;
				$dimension_array = array( 'length' => '42','width' => '30','height' => '3' );
			}*/
			//echo $this->no_of_boxes;die;
			//echo $length;
			//echo '<pre>';
			//print_r($dimension_array);die;
			$box_data = $this->wpdb->get_row( "SELECT * FROM {$this->wpdb->prefix}ken_boxes WHERE id = {$data->selected_box}" );
			if( !empty($box_data) )
			{
				$dimension_array = array( 'length' => $box_data->length,'width' => $box_data->width,'height' => $box_data->height );
			}else
			{
				$dimension_array = array( 'length' => $data->box_length,'width' => $data->box_width,'height' => $data->box_height );
			}		
			$this->variable['product_dimension'] = $dimension_array;
			$this->variable['product_dimension']['no_of_boxes'] = $this->no_of_boxes;
		}
		public function gform_after_submission_func( $entry, $form )
		{
			$this->unset_woo_variable();
			//$cal_var = unserialize($entry['17']);
			$this->variable['contact']['email'] = $entry['10'];
			$this->variable['contact']['phone'] = $entry['11'];
			//$cal_var = $cal_var[0];
			$this->variable['input_val']['tooth_style'] = $entry['1'];
			$width = $entry['20'];
			$this->variable['input_val']['width'] = $width;
			$thickness = $entry['21'];
			$this->variable['input_val']['thickness'] = $thickness;
			$tooth_spacing = $entry['22'];
			$this->variable['input_val']['tooth_spacing'] = $tooth_spacing;
			$length = round($entry['23'] / 12 , 2);			
			
			$length_in_inches = $entry['23'];
			$this->variable['input_val']['length'] = $length_in_inches;
			$box_quantity = $entry['16'];
			//$this->variable['item_meta']['Qty of boxes'] = $box_quantity;
			$this->variable['item_meta']['Length'] = $length_in_inches;
			$this->product_qty = $box_quantity;
			$this->variable['input_val']['box_quantity'] = $box_quantity;
			if( $width == '1.25' && $tooth_spacing == '5-8T' )
			{
				$sales_price_lbs_ft = $width*$thickness*3.4;
			}else
			{
				$sales_price_lbs_ft = $width*$thickness*3.4*0.95;
			}			
			$pricing_data = $this->wpdb->get_row( "SELECT * FROM {$this->wpdb->prefix}woo_pricing WHERE width = '$width' and thickness = '$thickness' and tooth_spacing = '$tooth_spacing'" );
			if( empty($pricing_data) )
			{
				wp_redirect( home_url() ); exit;
				echo "SELECT * FROM {$this->wpdb->prefix}woo_pricing WHERE width = '$width' and thickness = '$thickness' and tooth_spacing = '$tooth_spacing'";
				echo '<pre>';
				print_r($pricing_data);die;
			}			
			//echo '<pre>';
			//print_r($pricing_data);die;	
			//calculate no of boxes
			$this->variable['input_val']['item_code'] = $pricing_data->item_code;
			$this->get_no_of_boxes($pricing_data);		
			$scrap = round($pricing_data->sales_price_ft * 0.03 * $length,2);
			$this->variable['calculated_val']['scrap'] = $scrap;
			
			$raw_standard_price = $length * $pricing_data->sales_price_ft + $pricing_data->sales_price_weld + $pricing_data->packaging + $scrap;
			$this->variable['calculated_val']['raw_standard_price'] = $raw_standard_price;
			
			$this->options = get_option( 'discount_option_name' );
			$one_to_ceiling = ($this->options['box_one_to_three']) ? $this->options['box_one_to_three'] : .25;
			$rounded_standard_price = $this->ceiling($raw_standard_price , $one_to_ceiling);
			$this->variable['calculated_val']['rounded_standard_price'] = $rounded_standard_price;
			//echo $rounded_standard_price;die;
			
			$discount_price = $this->get_discount_amount($rounded_standard_price);
			$this->variable['calculated_val']['discount_price'] = $discount_price;
			
			$four_ore_more_ceiling = ($this->options['box_four_ore_more']) ? $this->options['box_four_ore_more'] : .25;
			$discount_rounded_price = $this->ceiling($discount_price , $four_ore_more_ceiling);
			$this->variable['calculated_val']['discount_rounded_price'] = $discount_rounded_price;
			//blade per box calculation
			if( $width == '2.00' )
			{
				$blades_per_box = 8;
			}else if( $width == '1.25' && $tooth_spacing == '5-8T' )
			{
				if( $length_in_inches < 171 )
				{
					$blades_per_box = 10;
				}else if( $length_in_inches > 207 )
				{
					$blades_per_box = 10;
				}else
				{
					$blades_per_box = 10;
				}
			}else if( $width == '1.50' )
			{
				if( $length_in_inches < 171 )
				{
					$blades_per_box = 10;
				}else if( $length_in_inches > 206 )
				{
					$blades_per_box = 8;
				}else
				{
					$blades_per_box = 10;
				}
			}else if( $length_in_inches < 172 )
			{
				$blades_per_box = 15;
			}else if( $length_in_inches > 207 )
			{
				$blades_per_box = 10;
			}else
			{
				$blades_per_box = 10;
			}	
			$this->variable['calculated_val']['blades_per_box'] = $blades_per_box;
			//box quantity		
			if( $box_quantity < 4 )
			{
				$unit_price = $rounded_standard_price;
			}else
			{
				$unit_price = $discount_rounded_price;
			}	
			$this->variable['calculated_val']['unit_price'] = $unit_price;
			$this->variable['item_meta']['Unit Price'] = number_format(round($unit_price,2),'2','.','').' per blade';
			//echo $unit_price;die;		
			$total_blades = $box_quantity * $blades_per_box;
			$this->variable['item_meta']['Qty Ordered'] = $total_blades." ($box_quantity box)";
			$this->variable['calculated_val']['total_blades'] = $total_blades;
			
			$total_revenue = $total_blades * $unit_price;
			//$this->variable['item_meta']['Subtotal'] = round($total_revenue,2);
			$this->variable['calculated_val']['total_revenue'] = $total_revenue;
			
			$box_weight = round($total_blades * $sales_price_lbs_ft * $length + 4) / $box_quantity;
			$this->variable['calculated_val']['box_weight'] = $box_weight;
			
			$taxes = $this->get_percentage( $total_revenue , 7 );
			$this->variable['calculated_val']['taxes'] = $taxes;
			
			$this->invoice_total = $total_revenue + $taxes;
			$this->variable['calculated_val']['invoice_total'] = $this->invoice_total;
			//$this->variable['item_meta']['Taxes, Order Total'] = $this->invoice_total;
			$this->set_session_data();
			//echo '<pre>';
			//print_r($this->variable);die;
			//$post_id = $this->create_product( $entry , $invoice_total );
			$this->add_product_to_cart();
		}
		public function set_session_data()
		{
			WC()->session->set( $this->woo_session_var , $this->invoice_total );
			WC()->session->set( $this->all_entered_data , $this->variable );
		}
		public function ken_custom_validation( $validation_result )
		{
			$form = $validation_result['form'];
			if( $this->custom_list_validation == true )
			{
				// set the form validation to false
				$validation_result['is_valid'] = false;		 
				//finding Field with ID of 1 and marking it as failed validation
				foreach( $form['fields'] as &$field )
				{
					if ( $field->id == '17' ) 
					{
						$field->failed_validation = true;
						$field->validation_message = 'This field is required.';
						break;
					}
				}
			}
			//Assign modified $form object back to the validation result
			$validation_result['form'] = $form;
			return $validation_result;
		}
		public function create_product( $entry = '' , $raw_standard_price = '' )
		{
			$this->product_title = 'Tooth style';
			$post = array(
				'post_content' => '',
				'post_status' => "publish",
				'post_title' => 'test',
				'post_type' => "product",
			);			
			//Create post
			$post_id = wp_insert_post( $post, $wp_error );			
			update_post_meta( $post_id, '_visibility', 'visible' );
			//update_post_meta( $post_id, '_regular_price', "1" );
			//update_post_meta( $post_id, '_sale_price', "1" );
			update_post_meta( $post_id, '_weight', "" );
			update_post_meta( $post_id, '_length', "" );
			update_post_meta($post_id, '_sku', "");
			update_post_meta( $post_id, '_price', $raw_standard_price );
			update_post_meta( $post_id, 'gf_id', $entry['id'] );
			update_post_meta( $post_id, 'all_calculated_data', serialize($this->variable) );
			return $post_id;			
		}
		public function add_product_to_cart( $product_id = '' )
		{
			if( empty($this->product_id) )
			{
				return;
			}
			if( ! WC()->cart->is_empty())
			{
				WC()->cart->empty_cart();
			}        
			WC()->cart->add_to_cart( $this->product_id , $this->product_qty , '' , '' , $this->variable );
			wp_redirect( wc_get_checkout_url() );exit;
		}
		public function wp_footer_func()
		{
			?>
            <?php /*?><input type="hidden" class="save_json_data" name="save_json_data" />
            <script>
			jQuery(document).ready(function(){
			  var get_val = jQuery('input[name=input_1]:checked').val();
			  jQuery(".gfield_list_17_cell4 > input").attr('placeholder', 'Length (inches)');
			  <?php if( empty($_POST['input_1']) ) { ?>
			  jQuery("td.gfield_list_17_cell1 > select").prop("disabled", true);	
			  <?php } ?>
			  <?php 
			  $input_1 = $_POST['input_1'];
			  if( $input_1 == 'Deck Duster' ) { ?>		  
			  jQuery('td.gfield_list_17_cell1 > select option').each(function() {
				if ( jQuery(this).val() != '1.14' && jQuery(this).val() != '1.25' && jQuery(this).val() != '' ) {
					jQuery(this).remove();
				}
			   });
			   var select_val = jQuery('td.gfield_list_17_cell2 > select').find(":selected").text();	
			   jQuery('td.gfield_list_17_cell3 > select option').each(function() {
				if ( (select_val == '0.035' || select_val == '0.041') && jQuery(this).val() != '1.3T DeckDuster®' && jQuery(this).val() != '' ) {
					jQuery(this).remove();
				}
			   });
			  <?php } else if( $input_1 == 'Pallet Dismantle' ) { ?>
			  jQuery('td.gfield_list_17_cell1 > select option').each(function() {
				if ( jQuery(this).val() != '1.25' && jQuery(this).val() != '' ) {
					jQuery(this).remove();
				}
			   });
			  <?php } else if( $input_1 == 'Standard Tooth' ) {?>
			  var selected_val = jQuery('td.gfield_list_17_cell1 > select').find(":selected").text();
			  jQuery('td.gfield_list_17_cell2 > select option').each(function() {
				if ( selected_val == '1.25' && jQuery(this).val() != '0.041' && jQuery(this).val() != '' ) {
					jQuery(this).remove();
				}
				});
			  <?php } ?>		
			jQuery('input[name=input_1]').click(function(){					
				var get_val = jQuery('input[name=input_1]:checked').val();
				jQuery("td.gfield_list_17_cell1 > select").prop("disabled", false);
				var check = jQuery(".save_json_data").val();
				var total_data = "<option value='' selected='selected'>Select a Width *</option>";
				var data = jQuery.parseJSON(check);
				jQuery.each(data, function(i, item) {
					if( get_val == 'Deck Duster' && (data[i].value != '1.14' && data[i].value != '1.25') )
					{
						 return true;
					}else if( get_val == 'Pallet Dismantle' && data[i].value != '1.25' )
					{
						return true;
					}
					total_data += "<option value='"+data[i].value+"'>"+data[i].text+"</option>";
				});
				jQuery('td.gfield_list_17_cell1 > select').html(total_data);
				//console.log(total_data);
				
			});
			var ajaxurl = "<?php echo admin_url('admin-ajax.php');?>";
			jQuery.ajax({
				  type: "post",
				  url : ajaxurl,
				  dataType: "json",
				  data : {
							action : 'save_json_data_function',
						},
				  success : function(data) {
						xml = data;
						jQuery(".save_json_data").val(JSON.stringify(data));
					}
				});
			});
            </script><?php */?>
            <?php
		}
		public function get_discount_amount( $number = '' )
		{
			if( empty($number) )
			{
				return '';
			}
			$discount = ($this->options['discount']) ? $this->options['discount'] : 10;
			$get_percentage = $this->get_percentage( $number , $discount );
			$discounted_price = $number - $get_percentage;
			return $discounted_price;
		}
		public function get_percentage( $number = '' , $percentage = '' )
		{
			if( empty($number) || empty($percentage) )
			{
				return '';
			}
			$cal_precentage = ($percentage / 100) * $number;
			return $cal_precentage;
		}
		public function ceiling( $number, $significance = 1 )
		{
			return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
		}
	}
	$GravityWooFunction = new GravityWooFunction();
}