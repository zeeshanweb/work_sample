<?php
class WC_Product_CSV_Importer_Frugal extends WC_Product_CSV_Importer {

	/**
	 * Tracks current row being parsed.
	 *
	 * @var integer
	 */
	protected $parsing_raw_data_index = 0;

	/**
	 * Initialize importer.
	 *
	 * @param string $file   File to read.
	 * @param array  $params Arguments for the parser.
	 */
	public function __construct( $file, $params = array() ) {
		$default_args = array(
			'start_pos'        => 0, // File pointer start.
			'end_pos'          => -1, // File pointer end.
			'lines'            => -1, // Max lines to read.
			'mapping'          => array(), // Column mapping. csv_heading => schema_heading.
			'parse'            => false, // Whether to sanitize and format data.
			'update_existing'  => false, // Whether to update existing items.
			'delimiter'        => ',', // CSV delimiter.
			'prevent_timeouts' => true, // Check memory and time usage and abort if reaching limit.
			'enclosure'        => '"', // The character used to wrap text in the CSV.
			'escape'           => "\0", // PHP uses '\' as the default escape character. This is not RFC-4180 compliant. This disables the escape character.
		);

		$this->params = wp_parse_args( $params, $default_args );
		$this->file   = $file;

		if ( isset( $this->params['mapping']['from'], $this->params['mapping']['to'] ) ) {
			$this->params['mapping'] = array_combine( $this->params['mapping']['from'], $this->params['mapping']['to'] );
		}
		$this->read_file();
	}

	/**
	 * Read file.
	 */
	protected function read_file() {
		if ( ! WC_Product_CSV_Importer_Controller::is_file_valid_csv( $this->file ) ) {
			wp_die( esc_html__( 'Invalid file type. The importer supports CSV and TXT file formats.', 'woocommerce' ) );
		}

		$handle = fopen( $this->file, 'r' ); // @codingStandardsIgnoreLine.
		$description_index = -1;
		$SKU_index =  -1;	
		if ( false !== $handle ) {
			$this->raw_keys = version_compare( PHP_VERSION, '5.3', '>=' ) ? array_map( 'trim', fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'], $this->params['escape'] ) ) : array_map( 'trim', fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'] ) ); // @codingStandardsIgnoreLine
			
			
			$all_key_rows = array('Price', 'Name','Manufacturer','Product Sub Line', 'Description','SKU', 'Product Category','Product Sub-Category');
			
			array_push($this->raw_keys, "Price");
			array_push($this->raw_keys, "Name");
			array_push($this->raw_keys, "Manufacturer");
			array_push($this->raw_keys, "Product Sub Line");
			
			$raw_key_arrays = $this->raw_keys;
			
			// Remove BOM signature from the first item.
			if ( isset( $this->raw_keys[0] ) ) {
				$this->raw_keys[0] = $this->remove_utf8_bom( $this->raw_keys[0] );
			}
			$description_index = array_search("Description",$this->raw_keys); 
			$SKU_index = array_search("SKU",$this->raw_keys); ;
			$PT_index= array_search("Product Category",$this->raw_keys); ;
			$SPT_index= array_search("Product Sub-Category",$this->raw_keys); ;
			$price_index= array_search("Price",$this->raw_keys); ;
			$name_index = array_search("Name",$this->raw_keys); ;
			$manufacturer_index= array_search("Manufacturer",$this->raw_keys); ;
			$product_sub_line_index= array_search("Product Sub Line",$this->raw_keys); ;
			
			if ( 0 !== $this->params['start_pos'] ) {
				fseek( $handle, (int) $this->params['start_pos'] );
			}
			$row_count = 0 ;
			$raw_data_modify_array = array();  
			while ( 1 ) {
				$row = version_compare( PHP_VERSION, '5.3', '>=' ) ? fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'], $this->params['escape'] ) : fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'] ); // @codingStandardsIgnoreLine

				if ( false !== $row ) {
					
					$orginal_row = $row;
					$SKU_Value = $row[$SKU_index];
					//$this->raw_data[] = $row;
					for( $i=($description_index+1) ; $i<= count($row); $i++ )
					{
						if( isset($row[$i]) && !empty($row[$i]) && trim($row[$i]) && trim($raw_key_arrays[$i]) && !empty($raw_key_arrays[$i]))
						{
							$row_modified = $row;
							$row_modified[$description_index] = $row[$description_index];
							$row_modified[$PT_index] = $row[$PT_index];
							$row_modified[$SPT_index] = $row[$SPT_index];
							 
							$row_modified[$SKU_index] = $raw_key_arrays[$i].'-'.$SKU_Value;
							$row_modified[$name_index] = $raw_key_arrays[$i].'-'.$SKU_Value;
							$row_modified[$manufacturer_index] = $this->find_manufacturer($raw_key_arrays[$i], true);
							$row_modified[$product_sub_line_index] = $this->find_manufacturer($raw_key_arrays[$i], false);
							$row_modified[$price_index] = $row[$i];
							//$row['product_type'] = $row[$PT_index];
							//$row['sub_product_type'] = $row[$SPT_index];
							$this->raw_data[] = $row_modified;
							//echo "<pre>"; print_r($this->raw_data);
							//unset($this->raw_keys[$i]);
						} 
					}
					$this->file_positions[ count( $this->raw_data ) ] = ftell( $handle );

					if ( ( $this->params['end_pos'] > 0 && ftell( $handle ) >= $this->params['end_pos'] ) || 0 === --$this->params['lines'] ) {
						break;
					}
				} else {
					break;
				}
			}

			$this->file_position = ftell( $handle );
		}
		foreach($this->raw_keys as $r_key => $r_val )
		{
			if(!in_array($r_val , $all_key_rows) )
			{
				//unset($this->raw_keys[$r_key]);
			}
		}
		//$this->raw_data = $raw_data_modify_array;
		if ( ! empty( $this->params['mapping'] ) ) {
			$this->set_mapped_keys();
		}
		
		if ( $this->params['parse'] ) {
			$this->set_parsed_data();
		}
		//echo "<pre>";
		//print_r($this->raw_data);
	}
	public function find_manufacturer( $sku_kitchen = '', $parent = true  )
	{
		if( empty($sku_kitchen) )
		{
			return false;
		}
		$args = array(
				'hide_empty' => false,
				"orderby" => "name",
				'meta_query' => array(
					array(
					   'key'       => 'sku_kitchen',
					   'value'     => trim($sku_kitchen),
					   'compare'   => '='
					)
				)
			);
		$terms = get_terms( FRUG_TAXONOMY, $args );
		$manufacturer_id = '';
		$product_line_id = '';
		if( $terms )
		{
			if( isset($terms[0]->parent) )
				$manufacturer_id = $terms[0]->parent;
			$product_line_id = $terms[0]->term_id;
			
		}
		if($parent) return $manufacturer_id;
		else return $product_line_id;

	}
}
