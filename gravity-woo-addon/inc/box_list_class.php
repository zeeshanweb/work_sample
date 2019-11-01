<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BoxListClass extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Box', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Box', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}
	public static function get_boxes( $per_page = 5, $page_number = 1 ) {

		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}ken_boxes";
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		return $result;
	}
	public static function record_count() {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ken_boxes";
		return $wpdb->get_var( $sql );
	}
	public function no_items() {
		_e( 'No box avaliable.', 'sp' );
	}
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'boxes':
			case 'length':
			case 'width':
			case 'height':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}
	function column_boxes( $item ) {

		$title = '<strong>' . $item['boxes'] . '</strong>';
		$actions = [
			'edit' => sprintf( '<a href="?page=%s&action=%s&box_id=%s">Edit</a>', esc_attr( 'edit_boxes' ), 'edit', absint( $item['id'] ) )
		];
		return $title . $this->row_actions( $actions );
	}
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'boxes' => __( 'Boxes', 'sp' ),
			'length' => __( 'Length', 'sp' ),
			'width' => __( 'Width', 'sp' ),
			'height' => __( 'Height', 'sp' )
		];
		return $columns;
	}
	public function get_sortable_columns() {
		$sortable_columns = array(
			'width' => array( 'width', true ),
			'length' => array( 'Length', true ),
		);

		return $sortable_columns;
	}
	public function get_bulk_actions() {
		$actions = [
			//'bulk-status-on' => 'ON'
		];
		return $actions;
	}
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();
		/** Process bulk action */
		$this->process_bulk_action();
		$per_page     = $this->get_items_per_page( 'box_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();
		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );
		$this->items = self::get_boxes( $per_page, $current_page );
	}

}