<?php
function check_if_term_exist_and_child( $taxonomy_id = '' )
{
	echo $taxonomy_id;
	echo '<pre>';
	$term = term_exists( $taxonomy_id , FRUG_TAXONOMY );
	print_r($term);die;
	if( term_exists( $taxonomy_id , FRUG_TAXONOMY ) )
	{
		echo 'hello';die;
		$is_subcategory = is_subcategory($taxonomy_id);
		if( $is_subcategory )
		{
			return true;
		}else
		{
			return false;
		}
	}else
	{
		return false;
	}
}
function manufacturer_redirect_url()
{
	return '/wp-admin/edit.php?post_type=product&page=manufacturer_listing';
}
function manufacturer_view_url()
{
	return "/wp-admin/edit.php?page=view_manufacturer&taxonomy_id=";
}
add_action( 'admin_head', 'load_custom_wp_admin_script' );
function load_custom_wp_admin_script()
{
	$current_page = $_GET['page'];
	if( isset($current_page) && ( $current_page == 'edit_manufacturer' || $current_page == 'view_manufacturer' || $current_page == 'product_line' ) || $current_page == 'man_setting' || $current_page == 'edit_product_line' )
	{ ?>
    <script>
             jQuery(document).ready(function(){
				 jQuery("li#menu-posts-product,li#menu-posts-product a.menu-icon-product").removeClass('wp-not-current-submenu');
				 jQuery("li#menu-posts-product,li#menu-posts-product .menu-icon-product").addClass("wp-has-current-submenu");
				 jQuery("li#menu-posts-product").addClass("wp-menu-open");
				 jQuery("li#menu-posts-product ul.wp-submenu-wrap li:last-child").addClass('current');
				 jQuery("li#menu-dashboard,li#menu-dashboard a.menu-icon-dashboard").removeClass('wp-has-current-submenu');
				 jQuery("li#menu-dashboard").removeClass('wp-menu-open');
				 jQuery("li#menu-dashboard").addClass('wp-not-current-submenu');
			 });
             </script>
		
	<?php }
}