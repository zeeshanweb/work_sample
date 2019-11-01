<?php
if ( !class_exists('GravityWooChaining') )
{
	class GravityWooChaining
	{
		public function __construct()
		{
			global $wpdb;
			$this->wpdb = $wpdb;
			add_action( 'wp_footer', array( $this,'wp_chain_footer_func') );
		}
		public function get_data_from_db()
		{
			$tooth_style = $_POST['tooth_style'];
			$item_code = $this->wpdb->get_results( "SELECT DISTINCT width FROM {$this->wpdb->prefix}woo_pricing WHERE tooth_count = '$tooth_style'" );
			$html = '';
			foreach( $item_code as $item_code_val )
			{
				$html .= "<option value='".$item_code_val->width."'>".$item_code_val->width."</option>";
			}
			echo json_encode($html);
			die;
		}
		public function wp_chain_footer_func()
		{
			if ( !is_front_page() )
			{
				return;
			}
			$item_code = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}woo_pricing where status = 'ON'" );
			$item_code = wp_slash(json_encode($item_code));
			$tooth_style = '';
			$width = '';
			$thickness = '';
			$tooth_spacing = '';
			if( isset($_POST['input_1']) )
			$tooth_style = $_POST['input_1'];
			if( isset($_POST['input_20']) )
			$width = $_POST['input_20'];
			if( isset($_POST['input_21']) )
			$thickness = $_POST['input_21'];
			if( isset($_POST['input_22']) )
			$tooth_spacing = $_POST['input_22'];
			?>
            <script>
			var all_data = '<?php echo  $item_code;?>';
			function populate_width( post_tooth_style = '' , post_width = '' )
			{
				var tooth_style = jQuery('input[name=input_1]:checked').val();
				var html = "<option value=''>Select a Width</option>";
				var data = jQuery.parseJSON(all_data);
				var arr = [];
				jQuery.each(data, function(key,value) {
					if( tooth_style != value.tooth_count )
					{
						return;
					}
					if (arr.indexOf(value.width) == -1)
					{
						arr.push(value.width);
						var select_var = '';
						if( post_width != '' && post_width == value.width )
						{
							select_var = 'selected';
						}
						html += "<option "+select_var+" value='"+value.width+"'>"+value.width+"</option>";
					}						
				});
				jQuery('select#input_1_20').html(html);
			}
			function populate_thickness( post_thickness = '' )
			{
				var width_val = jQuery("select#input_1_20").val();
				var data = jQuery.parseJSON(all_data);
				var tooth_style = jQuery('input[name=input_1]:checked').val();
				var html = "<option value=''>Select a Thickness</option>";
				var arr = [];
				jQuery.each(data, function(key,value) {
					if( value.width != width_val || tooth_style != value.tooth_count )
					{
						return;
					}
					if (arr.indexOf(value.thickness) == -1)
					{
						arr.push(value.thickness);
						var select_var = '';
						if( post_thickness != '' && post_thickness == value.thickness )
						{
							select_var = 'selected';
						}
						html += "<option "+select_var+" value='"+value.thickness+"'>"+value.thickness+"</option>";
					}						
				});
				jQuery('select#input_1_21').html(html);
			}
			function populate_toothspacing( post_toothspacing = '' )
			{
				var width_val = jQuery("select#input_1_20").val();
				var tooth_spacing = jQuery("select#input_1_21").val();
				var data = jQuery.parseJSON(all_data);
				var tooth_style = jQuery('input[name=input_1]:checked').val();
				var html = "<option value=''>Select a tooth spacing</option>";
				var arr = [];
				jQuery.each(data, function(key,value) {
				if( value.width != width_val && value.tooth_spacing != tooth_spacing || tooth_style != value.tooth_count )
				{
					return;
				}
				if (arr.indexOf(value.tooth_spacing) == -1)
				{
					arr.push(value.tooth_spacing);
					var select_var = '';
					if( post_toothspacing != '' && post_toothspacing == value.tooth_spacing )
					{
						select_var = 'selected';
					}
					html += "<option "+select_var+" value='"+value.tooth_spacing+"'>"+value.tooth_spacing+"</option>";
				}						
				});
				jQuery('select#input_1_22').html(html);
			}
            jQuery(document).ready(function(){	
			    var post_tooth_style = '<?php echo $tooth_style; ?>';
				var post_width = '<?php echo $width; ?>';
				var post_thickness = '<?php echo $thickness; ?>';
				var post_tooth_spacing = '<?php echo $tooth_spacing; ?>';
				populate_width( post_tooth_style , post_width );
				populate_thickness( post_thickness );
				populate_toothspacing( post_tooth_spacing );			
				var ajaxurl = "<?php echo admin_url('admin-ajax.php');?>";
				jQuery('input[name=input_1]').click(function(){
					populate_width();
				});
				jQuery('select#input_1_20').on('change', function() {
					populate_thickness();
				});
				jQuery('select#input_1_21').on('change', function() {
					populate_toothspacing();
				});
			});
            </script>
            <?php
		}
	}
	$GravityWooChaining = new GravityWooChaining();
}