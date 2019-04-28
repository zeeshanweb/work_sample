<?php
/**
 * Create a new table class that will extend the WP_List_Table
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Example_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
       $this->process_bulk_action();
	    $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
        $perPage = 2;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}
    public function get_columns()
    {
        $columns = array(
            'id'          => 'ID',
            'mfgshort'       => 'Mfg Short',
            'mfgname' => 'Mfg Name',
            'quoteprovided'        => 'Quote Provided',
            'sold'    => 'Sold',
            'rta'      => 'RTA',
			'assm'      => 'Assm'
        );
        return $columns;
    }
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}
    public function get_hidden_columns()
    {
        return array();
    }
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('mfgshort' => array('mfgshort', false),'id' => array('id', false));
    }
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = array();
		$get_terms = get_terms( array('taxonomy' => 'product_manufacturer', 'hide_empty' => false,) );
		foreach( $get_terms as $get_terms_val )
		{
			//echo '<pre>';
			//print_r($get_terms_val);die;
			$mfgshort = get_term_meta( $get_terms_val->term_id, 'short_name', true);
			$data[] = array(
                    'id'          => $get_terms_val->term_id,
                    'mfgshort'       => $mfgshort,
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
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'mfgshort':
            case 'mfgname':
            case 'quoteprovided':
            case 'quoteprovided':
            case 'sold':
			case 'rta':
			case 'assm':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
	public function process_bulk_action() {
    $referer = wp_unslash( $_SERVER['REQUEST_URI'] );
	$location = add_query_arg( 'message', 3, $referer );
  //Detect when a bulk action is being triggered...
  if ( 'delete' === $this->current_action() ) {

    // In our file that handles the request, verify the nonce.
    $nonce = esc_attr( $_REQUEST['_wpnonce'] );

    if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
      die( 'Go get a life script kiddies' );
    }
    else {
      self::delete_customer( absint( $_GET['customer'] ) );

      //wp_redirect( esc_url( add_query_arg() ) );
      //exit;
	   //wp_redirect( '/wp-admin/admin.php?page=manufacturer_listing' );exit;
    }

  }

  // If the delete bulk action is triggered
  if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
       || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
  ) {

    $delete_ids = esc_sql( $_POST['bulk-delete'] );

    // loop over the array of record IDs and delete them
    foreach ( $delete_ids as $id ) {
      self::delete_customer( $id );

    }

    wp_redirect( esc_url( add_query_arg() ) );
    exit;
  }
}
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'mfgshort';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc')
        {
            return $result;
        }
        return -$result;
    }
}