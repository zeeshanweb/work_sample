<?php
if ( !class_exists('View_manufacturer_action') )
{
	class View_manufacturer_action
	{
		var $taxonomy_id;
		public function __construct()
		{
			add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
			add_action( 'admin_menu', array( $this,'delete_product_line') );
			add_action( 'admin_notices', array( $this,'frug_admin_notice_success') );
			$this->taxonomy_id = $_GET['taxonomy_id'];
		}
		public function frug_admin_notice_success()
		{
			if( isset($_GET['message']) && $_GET['message'] == 'deleted' && @$_GET['page'] == 'view_manufacturer' )
			{		
				
				?>
				 <div class="notice notice-success is-dismissible">
					<p><?php _e( 'Product line deleted Successfully!', 'sample-text-domain' ); ?></p>
				 </div>
			<?php	
			  }
		}
		public function delete_product_line()
		{
			$nonce = esc_attr( $_REQUEST['product_none'] );
			if ( isset($_GET['action']) && $_GET['action'] == 'product_line_delete' && isset($_GET['product_line_id']) && !empty($_GET['product_line_id']) && wp_verify_nonce( $nonce, 'frug_delete_product' ) )
			{
				//echo 'hello';die;
				$referer = '/wp-admin/?page=view_manufacturer&taxonomy_id='.$this->taxonomy_id;
				$product_line_id = $_GET['product_line_id'];
				$get_term = get_term($product_line_id,FRUG_TAXONOMY);
				if( $get_term )
				{
					if( wp_delete_term( $product_line_id, FRUG_TAXONOMY ) )
					{
						$location = add_query_arg( 'message', 'deleted', $referer );
						wp_redirect( $location  );exit;
					}
					wp_redirect( $referer );exit;
				}
			}
		}
		public function plugin_menu()
		{
			add_submenu_page(NULL, __('My SubMenu Page'), __('Manufacturers'), 'manage_options', 'view_manufacturer', array($this,'view_manufacturer_setting'));
		}
		public function view_manufacturer_setting()
		{
			
		 // check user capabilities
		 if ( ! current_user_can( 'manage_options' ) ) {
		 return;
		 }
	     $delete_nonce_product = wp_create_nonce( 'frug_delete_product' );
			 ?>
			 <div class="wrap">
			 <h1>Manufacturer Detail</h1>
             
             <h4><?php echo get_term_meta( $this->taxonomy_id, 'short_name', true);?><a class="edit_manufacturer_class" style="float:right;" href="/wp-admin/?page=edit_manufacturer&taxonomy_id=<?php echo $this->taxonomy_id;?>"><input type="button" name="button" id="button" class="button button-primary" value="Edit Manufacturer"></a></h4>
             <p class="cabinatory"><strong><?php echo get_term_meta( $this->taxonomy_id, 'company_name', true);?></strong></p>
             <p><?php echo get_term_meta( $this->taxonomy_id, 'phone', true);?></p>
             
             <div class="location" style="float:left; width:30%"><p><strong>Location:</strong></p>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'address_street_1', true);?></span>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'address_street_2', true);?></span>
             <br /><span><?php echo get_term_meta( $this->taxonomy_id, 'city', true);?></span>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'state', true);?></span>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'zip', true);?></span>
             </div>
             
             <div class="mailing" style="float:right; width:70%"><p><strong>Mailing:</strong></p>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'address_street_1', true);?></span>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'address_street_2', true);?></span>
             <br /><span><?php echo get_term_meta( $this->taxonomy_id, 'city', true);?></span>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'state', true);?></span>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'zip', true);?></span>
             </div>
             <br /><br />             
			 <div class="contact" style="float:left;">
             <p><strong>Contact(s):</strong></p>
             <span><?php echo get_term_meta( $this->taxonomy_id, 'primary_poc', true);?></span>&nbsp;&nbsp;
             <span><?php echo get_term_meta( $this->taxonomy_id, 'email1', true);?></span>&nbsp;&nbsp;
             <span><?php echo get_term_meta( $this->taxonomy_id, 'phone', true);?></span>
             </div>
             <br /><br />
             <div class="contact" style="float:left; clear:both;">
             <p><strong>Multiplier:</strong> <?php echo get_term_meta( $this->taxonomy_id, 'multiplier', true);?></p>
             <p><strong>Markup Value:</strong> <?php echo get_term_meta( $this->taxonomy_id, 'markup_value', true);?></p>
             <p><strong>Lowest Allowable selling value:</strong> <?php echo get_term_meta( $this->taxonomy_id, 'lowest_allowable_selling_value', true);?></p>
             </div>
             <?php $get_term_children = get_term_children( $this->taxonomy_id, FRUG_TAXONOMY );  ?>
            <div style="float: left;clear: both; width:100%;"> <h2 style="float: left;">RTA Product Lines</h2><p class="add_product_line" style="float:right; margin:5px 0 0 0;"><a href="/wp-admin/admin.php?page=product_line&manufacturer_id=<?php echo $this->taxonomy_id;?>"><input type="button" name="button" id="button" class="button button-primary" value="Add Product Line"></a></p></div>
             <table>
                  <tr>
                    <th>ID</th>
                    <th>Mfg Name</th>
                    <th>Frugal Name</th>
                    <th>Style</th>
                    <th>SKU Kitchen</th>
                    <th>SKU Vanity</th>
                    <th>Product Class</th>
                    <th>Active</th>                    
                    <th>Action</th>
                  </tr>
                  <?php foreach( $get_term_children as $get_term_children_val ) { 
				  ?>
                  <tr>
                    <td><?php echo $get_term_children_val;?></td>
                    <td><?php echo get_term_meta( $get_term_children_val, 'mfg_name', true);?></td>
                    <td><?php echo get_term_meta( $get_term_children_val, 'frugal_name', true);?></td>
                    <td><?php echo get_term_meta( $get_term_children_val, 'style', true);?></td>
                    <td><?php echo get_term_meta( $get_term_children_val, 'sku_kitchen', true);?></td>
                    <td><?php echo get_term_meta( $get_term_children_val, 'sku_vanity', true);?></td>
                    <td><?php 
					$product_class = get_term_meta( $get_term_children_val, 'product_class', true);
					if( $product_class == 'rta' )
					{
						$product_class = strtoupper($product_class);
					}else
					{
						$product_class = ucfirst($product_class);
					}
					echo $product_class;?></td>
                    <td><?php echo get_term_meta( $get_term_children_val, 'active_option', true);?></td>                    
                    <td><a href="/wp-admin/admin.php?page=edit_product_line&product_line_id=<?php echo $get_term_children_val;?>&manufacturer_id=<?php echo $this->taxonomy_id;?>">Edit</a>&nbsp;|&nbsp;<a class="delete_product_line" href="/wp-admin/?page=view_manufacturer&taxonomy_id=<?php echo $this->taxonomy_id;?>&action=product_line_delete&product_line_id=<?php echo $get_term_children_val;?>&product_none=<?php echo $delete_nonce_product;?>" onclick="return confirm('Are you sure you want to delete?');">Delete</a></td>
                  </tr> 
                  <?php } ?>         
            </table>
             <style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
			 </div>
			 <?php
			
		}
	}
	if( is_admin() )
    $View_manufacturer_action = new View_manufacturer_action();
}