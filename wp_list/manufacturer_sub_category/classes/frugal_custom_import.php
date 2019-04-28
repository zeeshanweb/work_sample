<?php
if ( !class_exists('Frugal_Custom_Import') )
{
	class Frugal_Custom_Import
	{
		var $product_updating = false;
		public function __construct()
		{
			add_filter("woocommerce_product_importer_memory_exceeded", array( $this,  "frugal_woocommerce_product_importer_memory_exceeded"), 10, 1);
			add_filter("woocommerce_product_importer_time_exceeded", array( $this,  "frugal_woocommerce_product_importer_time_exceeded"), 10, 1);
			
			add_filter("woocommerce_product_csv_importer_class", array( $this,  "frugal_woocommerce_product_csv_importer_class"), 10, 1);
			add_action("plugins_loaded", array( $this, "frugal_import_plugins_loaded"), 100);
			
			add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_column_to_importer') );
			add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this,'add_column_to_mapping_screen'));
			add_filter( 'woocommerce_product_import_pre_insert_product_object', array( $this,'process_import'), 10, 2 );
			add_action( 'woocommerce_product_import_inserted_product_object', array( $this,'process_import_success'), 10, 2 );
			//add_action( 'woocommerce_product_import_before_process_item', array( $this,'before_process_item') );
			//add_filter( 'woocommerce_product_importer_parsed_data', array( $this,'frugal_parsed_data'), 10, 2 );
			//add_filter( 'woocommerce_product_import_get_product_object', array( $this,'get_product_object_func'), 10, 2 );
			//add_filter( 'woocommerce_csv_product_import_mapped_columns', array( $this,'frugal_mapped_custom_column'), 10, 2 );
		}
		public function frugal_woocommerce_product_importer_memory_exceeded( $return )
		{
			return false;			
		}
		public function frugal_woocommerce_product_importer_time_exceeded( $return )
		{
			return false;			
		}
		public function frugal_woocommerce_product_csv_importer_class( $class )
		{
		 return 'WC_Product_CSV_Importer_Frugal';
		}
		public function frugal_import_plugins_loaded()
		{
			if ( ! class_exists( 'WC_Importer_Interface', false ) ) {
				include_once  WC_ABSPATH.'includes/import/class-wc-product-csv-importer.php';
			}
			include_once dirname( __FILE__ ) . '/import-class.php';
		}
		
		public function frugal_mapped_custom_column( $headers, $raw_headers )
		{
			return $headers;
		}
		
		
		/**
		 * Register the 'Custom Column' column in the importer.
		 *
		 * @param array $options
		 * @return array $options
		 */
		public function add_column_to_importer( $options ) 
		{
		
			// column slug => column name
			$options['product_type'] = 'Product Type';
			$options['sub_product_type'] = 'Sub Product Type';
			$options['manufacturer'] = 'Manufacturer';
			$options['product_sub_line'] = 'Product Sub Line';
			
			return $options;
		}
		/**
		 * Add automatic mapping support for 'Custom Column'. 
		 * This will automatically select the correct mapping for columns named 'Custom Column' or 'custom column'.
		 *
		 * @param array $columns
		 * @return array $columns
		 */
		public function add_column_to_mapping_screen( $columns ) {
			
			// potential column name => column slug
			$columns['Product Category'] = 'product_type';
			$columns['Product Sub-Category'] = 'sub_product_type';
			$columns['SKU Kitchen'] = 'sku_kitchen';
			//$columns['custom column'] = 'custom_column';
			$columns['Manufacturer'] = 'manufacturer';
			$columns['Product Sub Line'] = 'product_sub_line';
			
			return $columns;
		}
		/**
		 * Process the data read from the CSV file.
		 * This just saves the value in meta data, but you can do anything you want here with the data.
		 *
		 * @param WC_Product $object - Product being imported or updated.
		 * @param array $data - CSV data read for the product.
		 * @return WC_Product $object
		 */
		public function process_import( $object, $data ) {
			
			if ( $object->get_id() && 'importing' !== $object->get_status() )
			{
				$this->product_updating = true;
			}
			if ( ! empty( $data['product_type'] ) ) {
				$object->update_meta_data( 'product_type', $data['product_type'] );
			}
		
			return $object;
		}
		public function import_update_date( $post_id = '' )
		{
			if( empty($post_id) )
			{
				return false;
			}
			//updtating import date to product
			if( $this->product_updating === true )
			update_post_meta( $post_id, 'frugal_import_update_date', date("Y-m-d") );
			else
			update_post_meta( $post_id, 'frugal_import_date', date("Y-m-d") );
		}
		public function process_import_success( $object, $data )
		{
			//echo '<pre>';
			//echo $object->get_id();			
			$this->import_update_date( $object->get_id() );
			
			$frugal_product_type = term_exists( $data['product_type'], 'frugal_product_type' );
			if( isset($frugal_product_type) && !empty($frugal_product_type) )
			{
				wp_set_post_terms( $object->get_id(), $frugal_product_type['term_id'], 'frugal_product_type' );
			}
			//Create the Product Type
			elseif( isset($data['product_type']) )
			{
				$create_term = wp_insert_term(
				  $data['product_type'], // the term 
				  'frugal_product_type' // the taxonomy
				);
				if( isset($create_term) && !empty($create_term) )
					wp_set_post_terms( $object->get_id(), $create_term['term_id'], 'frugal_product_type' );
							
			}
			$frugal_sub_product_type = term_exists( $data['sub_product_type'], 'frugal_sub_product_type' );
			if( isset($frugal_sub_product_type) && !empty($frugal_sub_product_type) )
			{
				wp_set_post_terms( $object->get_id(), $frugal_sub_product_type['term_id'], 'frugal_sub_product_type' );
			}
			//Create Sub Product Type
			else if( isset($data['sub_product_type']) )
			{
				$create_term = wp_insert_term(
				  $data['sub_product_type'], // the term 
				  'frugal_sub_product_type' // the taxonomy
				);
				if( isset($create_term) && !empty($create_term) )
					wp_set_post_terms( $object->get_id(), $create_term['term_id'], 'frugal_sub_product_type' );
							
			}
			//Assign Manufacturer
			if( isset($data['manufacturer']) && isset($data['product_sub_line']) && $data['manufacturer'] )
			{
				wp_set_post_terms( $object->get_id(), array($data['manufacturer'] , $data['product_sub_line']), FRUG_TAXONOMY );
			}
		}
	}
	$Frugal_Custom_Import = new Frugal_Custom_Import();
}