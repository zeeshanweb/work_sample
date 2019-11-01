<?php
if ( !class_exists('GravityWooExtendedFunction') )
{
	class GravityWooExtendedFunction extends GravityWooFunction
	{
		var $ken_rate_data = 'ken_rate_data';
		var $ken_billing_bounty = 'ken_billing_bounty';
		var $ken_billing_state = 'ken_billing_state';
		var $ken_state = 'GA';
		public function __construct()
		{
			global $wpdb;
			$this->wpdb = $wpdb;
			add_action( 'wp_footer', array( $this,'wp_footer_func_ext'), 10, 1 );
			add_filter( 'woocommerce_billing_fields', array( $this,'woocommerce_billing_fields_func'), 10, 1 );
			add_action( 'wp_ajax_woo_get_ajax_data', array( $this,'woo_get_ajax_data') );
            add_action( 'wp_ajax_nopriv_woo_get_ajax_data', array( $this,'woo_get_ajax_data') );
			add_action( 'woocommerce_cart_calculate_fees', array( $this,'woocommerce_cart_calculate_fees_func') );
			add_action( 'woocommerce_checkout_update_order_review', array( $this,'woocommerce_checkout_update_order_review_func') );
			add_filter( 'woocommerce_admin_billing_fields', array( $this,'woocommerce_admin_billing_fields_func') );
			add_filter( 'woocommerce_update_order_review_fragments', array( $this,'woocommerce_update_order_review_fragments_func'));
		}
		public function woocommerce_update_order_review_fragments_func( $fragments )
		{
			ob_start();
			?>
			<table class="my-custom-shipping-table">
				<tbody>
			<tr class="cart-subtotal">
					<th><?php _e( 'Subtotal', 'woocommerce' ); ?></th>
					<td><?php wc_cart_totals_subtotal_html(); ?></td>
				</tr>
		
				<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
					<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
						<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
					</tr>
				<?php endforeach; ?>
		
				<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
		
					<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
		
					<?php wc_cart_totals_shipping_html(); ?>
		
					<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		
				<?php endif; ?>
		
				<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
					<tr class="fee">
						<th><?php echo esc_html( $fee->name ); ?></th>
						<td><?php wc_cart_totals_fee_html( $fee );echo " (sales tax charged on subtotal and shipping)"; ?></td>
					</tr>
				<?php endforeach; ?>
		
				<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
					<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
						<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
							<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
								<th><?php echo esc_html( $tax->label ); ?></th>
								<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr class="tax-total">
							<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
							<td><?php wc_cart_totals_taxes_total_html(); ?></td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>
		
				<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
		
				<tr class="order-total">
					<th><?php _e( 'Total Charges', 'woocommerce' ); ?></th>
					<td><?php wc_cart_totals_order_total_html(); ?></td>
				</tr>
			</tbody>
			</table>
			<?php
			$woocommerce_shipping_methods = ob_get_clean();
			$fragments['.my-custom-shipping-table'] = $woocommerce_shipping_methods;
			
			return $fragments;
		}
		public function get_bounty_name_using_order_id( $order_id = '' )
		{
			if( empty($order_id) )
			{
				return '';
			}
			$order = wc_get_order($order_id);
			$get_bounty = $order->get_meta('_billing_bounty');
			$billing_state = $order->get_billing_state();
			$get_bounty_data = $this->get_tax_data_from_db($get_bounty);
			if( $billing_state == $this->ken_state && !empty($get_bounty_data) )
			{
				return $get_bounty_data;
			}
			return '';
		}
		public function woocommerce_admin_billing_fields_func( $fields )
		{
			if( isset($_GET['post']) && !empty($_GET['post']) )
			{
				$get_bounty = $this->get_bounty_name_using_order_id($_GET['post']);
				if( !empty($get_bounty) )
				{
					$fields['bounty'] = array(
						'label' => __( 'County' ),
						'show' => true,
						'value' => $get_bounty->bounty,
					    );					
				}				
			}	
			return $fields;
		}
		public function woocommerce_checkout_update_order_review_func( $posted_data )
		{
			$post = array();
			$vars = explode('&', $posted_data);
			foreach ($vars as $k => $value)
			{
				$v = explode('=', urldecode($value));
				$post[$v[0]] = $v[1];
			}
			$get_bounty = $post['billing_bounty'];
			$ken_billing_state = $post['billing_state'];
			if( !empty($get_bounty) && $ken_billing_state == $this->ken_state )
			{
				 $get_bounty_data = $this->get_tax_data_from_db($get_bounty);
				 if( !empty($get_bounty_data) )
			     WC()->session->set($this->ken_rate_data, json_encode($get_bounty_data) );
			}else
			{
				WC()->session->set($this->ken_rate_data, '' );
			}			
			WC()->session->set($this->ken_billing_state, $ken_billing_state );			
		}
		public function get_total_shipping_detai()
		{
			$packages = WC()->shipping->get_packages();
			foreach ($packages as $i => $package) 
			{
				$chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
				if( $chosen_method )
				{
					$rate = $package['rates'][$chosen_method]->cost;
					return $rate;
				}
			}
			/*foreach( WC()->session->get('shipping_for_package_0')['rates'] as $method_id => $rate )
			{
				if( WC()->session->get('chosen_shipping_methods')[0] == $method_id )
				{
					$rate_cost_excl_tax = floatval($rate->cost);	
					return $rate_cost_excl_tax;	
					break;
				}
			}*/
			return 0;
		}
		public function woocommerce_cart_calculate_fees_func()
		{
			$get_state = WC()->customer->billing_state;
			$shipping_total = $this->get_total_shipping_detai();
			$total_cart  = WC()->cart->cart_contents_total;
			$total_checkout = $shipping_total + $total_cart;
			$get_tax_rate = json_decode(WC()->session->get($this->ken_rate_data));
			$get_percentage = $this->get_percentage( $total_checkout , $get_tax_rate->rate );
			//echo $get_tax_rate->rate.'<br>';
			//echo $get_percentage;
			if( $get_state == $this->ken_state && is_checkout() && !empty($get_tax_rate) && !empty($get_percentage) )
			{
				WC()->cart->add_fee( __('GA Sales Tax', 'woocommerce'), $get_percentage );
			}
		}
		public function get_tax_data_from_db( $get_bounty = '' )
		{
			if( empty($get_bounty) )
			{
				return '';
			}
			$get_rate = $this->wpdb->get_row( "SELECT * FROM {$this->wpdb->prefix}ken_tax_rates WHERE id = $get_bounty");
			return $get_rate;
		}
		public function woo_get_ajax_data() 
		{
			$get_bounty = $_POST['get_bounty'];
			$response_array = array();
			if( !empty($get_bounty) )
			{
				$get_rate = $this->get_tax_data_from_db($get_bounty);
				if( !empty($get_rate) )
				{
					WC()->session->set($this->ken_rate_data, json_encode($get_rate) );
				}				
			}			
			$response_array['response'] = true;
			$response_array['msg'] = 'success';
			echo json_encode( $response_array );
			die;
		}
		public function woocommerce_billing_fields_func( $fields )
		{
			$get_bounty = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}ken_tax_rates" );
			$bounty_data[''] = 'Choose a county';
			foreach( $get_bounty as $get_bounty_val )
			{
				$bounty_data[$get_bounty_val->id] = $get_bounty_val->bounty;
			}
			$fields['billing_bounty'] = array(
				'label'     => __('county', 'woocommerce'),
				'placeholder'   => _x('Choose a county', 'placeholder', 'woocommerce'),
				'required'  => true,
				'priority'  => 95,
				'class'     => array('form-row-wide'),
				'id'     => 'select_bounty',
				'clear'     => true,
				'type'      => 'select',
				 'options'     => $bounty_data
				 );
			$billing_state = WC()->customer->billing_state;
			if( !empty($billing_state) && $billing_state != $this->ken_state )
			{
				$fields['billing_bounty']['required'] = false;
			}			
			return $fields;
		}
		public function wp_footer_func_ext()
		{
			?>
            <script>
            jQuery(document).ready(function() {
				jQuery('#select_bounty').select2({
			    containerCssClass: "bounty_select",
                dropdownCssClass: "bounty_select",
			    });
				
				var countryCode = '<?php echo $this->ken_state;?>';	  
				jQuery('select#billing_state').change(function(){					
					selectedCountry = jQuery('select#billing_state').val();
					//alert(selectedCountry);					  
					if( selectedCountry == countryCode ){
						jQuery('#select_bounty_field').show();
					}
					else {
						jQuery('#select_bounty_field').hide();
					}
				});
				//ajax call
				jQuery('select#select_bounty').change(function(){
					jQuery('body').trigger('update_checkout');
				});
			});
            </script>
            <style>.woocommerce .select2-container, .woocommerce-page .select2-container{ width:100% !important;}</style>
            <?php
		}
	}
	$GravityWooExtendedFunction = new GravityWooExtendedFunction();
}