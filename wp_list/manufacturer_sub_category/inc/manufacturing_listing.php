<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Manufacturer_List extends WP_List_Table {

	/** Class constructor */
	var $frug_taxonomy = 'product_manufacturer';
	var $location;
	public function __construct() {

		$referer = wp_unslash( '/wp-admin/edit.php?post_type=product&page=manufacturer_listing' );
	    $this->location = add_query_arg( 'deleted', 1, $referer );
		parent::__construct( [
			'singular' => __( 'Customer', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Customers', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_manufacturer( $per_page = 5, $page_number = 1 ) {

		$data = array();
		$page = $page_number;//( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
		$taxonomy = FRUG_TAXONOMY;
		$offset = ( $page-1 ) * $per_page;
		$args = array( 'number' => $per_page, 'offset' => $offset, 'hide_empty' => false, 'parent' => 0 );
		//$get_terms = get_terms( array('taxonomy' => 'product_manufacturer', 'hide_empty' => false,) );
		$get_terms = get_terms( $taxonomy , $args );
		//echo '<pre>';
		//print_r($args);
		//print_r($get_terms);die;
		foreach( $get_terms as $get_terms_val )
		{
			//echo '<pre>';
			//print_r($get_terms_val);die;
			$mfgshort = get_term_meta( $get_terms_val->term_id, 'short_name', true);
			$data[] = array(
                    'sp'          => $get_terms_val->term_id,
                    'name'       => $mfgshort,
                    'mfgname' => $get_terms_val->name,
                    'quoteprovided'        => 'N/A',
                    'sold'    => 'N/A',
                    'rta'      => 'N/A',
					'assm'      => 'N/A'
                    );
		} 
        return $data;
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_taxonomy( $id ) {
		global $wpdb;

		if( isset($id) && !empty($id) )
		{
			$get_term_children = get_term_children( $id, FRUG_TAXONOMY );
			if( !empty($get_term_children) )
			{
				foreach( $get_term_children as $get_term_children_val )
				{
					wp_delete_term( $get_term_children_val, FRUG_TAXONOMY );
				}
			}			
			wp_delete_term( $id, FRUG_TAXONOMY );
		}		
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		//$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}customers";
        $get_terms = get_terms( array('taxonomy' => FRUG_TAXONOMY, 'hide_empty' => false, 'parent' => 0) );
		return count($get_terms);//$wpdb->get_var( $sql );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No manufacturer avaliable.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
            case 'mfgname':
            case 'quoteprovided':
            case 'quoteprovided':
            case 'sold':
			case 'rta':
			case 'assm':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['sp']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?post_type=%s&page=%s&action=%s&taxonomy_id=%s&_wpnonce=%s">Delete</a>', 'product',esc_attr( $_REQUEST['page'] ), 'delete', $item['sp'], $delete_nonce ),'edit' => sprintf( '<a href="/wp-admin/?page=%s&taxonomy_id=%s">Edit</a>', esc_attr( 'edit_manufacturer' ),$item['sp'] ),'view' => sprintf( '<a href="/wp-admin/?page=%s&taxonomy_id=%s">View</a>', esc_attr( 'view_manufacturer' ),$item['sp'] )
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'name'    => __( 'Mfg Short', 'sp' ),
			'mfgname' => __( 'Mfg Name', 'sp' ),
			'quoteprovided'        => 'Quote Provided',
            'sold'    => 'Sold',
            'rta'      => 'RTA',
			'assm'      => 'Assm'
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			//'name' => array( 'name', true ),
			//'city' => array( 'city', false )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'manufacturer_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_manufacturer( $per_page, $current_page );
	}

	public function process_bulk_action() {
		
		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_taxonomy( absint( $_GET['taxonomy_id'] ) );

		                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		                // add_query_arg() return the current url
		                //wp_redirect( esc_url_raw(add_query_arg()) );
				//exit;
				wp_redirect( $this->location );exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_taxonomy( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        //wp_redirect( esc_url_raw(add_query_arg()) );
			//exit;
			wp_redirect( $this->location );exit;
		}
	}

}


class Frug_Listing_Class {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $manufacturer_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function plugin_menu() {

		
		$hook = add_submenu_page('edit.php?post_type=product', __('My SubMenu Page'), __('Manufacturers'), 'manage_options', 'manufacturer_listing', array($this,'plugin_settings_page'));
		
		add_action( "load-$hook", [ $this, 'screen_option' ] );
		add_action( 'admin_notices', array( $this,'frug_admin_notice__success') );

	}
    public function frug_admin_notice__success()
		{ 
		   
			if( isset($_GET['deleted']) && $_GET['deleted'] == 1 )
			{		
				
				?>
				 <div class="notice notice-success is-dismissible">
					<p><?php _e( 'Manufacturer deleted successfully!', 'sample-text-domain' ); ?></p>
				 </div>			
			  <?php }
		}

	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2>Manufacturers</h2><p style="float:right;" class="add_manufacturer"><a href="/wp-admin/admin.php?page=man_setting"><input type="button" name="button" id="button" class="button button-primary" value="Add Manufacturer"></a></p>
							<form method="post">
								<?php
								$this->manufacturer_obj->prepare_items();
								$this->manufacturer_obj->display(); ?>
						
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Manufacturer',
			'default' => 5,
			'option'  => 'manufacturer_per_page'
		];

		add_screen_option( $option, $args );

		$this->manufacturer_obj = new Manufacturer_List();
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
	Frug_Listing_Class::get_instance();
} );
