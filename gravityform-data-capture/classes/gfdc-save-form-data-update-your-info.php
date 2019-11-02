<?php
/**
 * Abstract class which has helper functions to get data from the database
 */
if ( !class_exists('GFDC_UPDATE_YOUR_INFO') )
{
	class GFDC_UPDATE_YOUR_INFO extends GFDC_FORMS
	{   
			private $inert_id = false ;
			public $query_error = false;
			public $data_array = array();
			public function __construct()
			{
				add_action( 'gform_pre_submission_5', array( $this,'gform_pre_submission' ));
				add_action( 'gform_after_submission_5', array( $this,'remove_form_entry' ));
				add_filter( 'gform_confirmation_5', array( $this,'custom_confirmation'), 10, 4 );
			}
			public function custom_confirmation( $confirmation, $form, $entry, $ajax )
			{
				$fire_insert_query = $this->fire_insert_query( $confirmation, $form, $entry, $ajax );
				if( $this->query_error === true )
				{
					$confirmation = "<span style='color:red;'>Could not save data into DB.</span>".do_shortcode( '[gravityform id='.$form['id'].' title=false description=false]' );
				}
				return $confirmation;
			}
			public function fire_insert_query( $confirmation, $form, $entry, $ajax )
			{
				$objFrom = new GFDC_DB_BASE('form_update_your_info');
				$this->inert_id = $objFrom->insert( $this->data_array ) ;
				if( $this->inert_id == 0 )
				{
					$this->query_error = true;
				}
				//$this->remove_form_entry( $entry );
			}
			public function gform_pre_submission()
			{
				$data_array = array();
				
				$data_array ['fname'] =  rgpost( 'input_1_3', true ); 
				$data_array ['lname'] =  rgpost( 'input_1_6', true ); 
				$data_array ['dob'] =  rgpost( 'input_2', true ); 
				$data_array ['phone'] =  rgpost( 'input_3', true ); 
				
				$data_array ['new_fname'] =  rgpost( 'input_5_3', true ); 
				$data_array ['new_lname'] =  rgpost( 'input_5_6', true ); 
				$data_array ['new_address1'] =  rgpost( 'input_6_1', true ); 
				$data_array ['new_address2'] =  rgpost( 'input_6_2', true ); 
				$data_array ['new_city'] =  rgpost( 'input_6_3', true ); 
				$data_array ['new_state'] =  rgpost( 'input_6_4', true ); 
				$data_array ['new_zip'] =  rgpost( 'input_6_5', true ); 
				$data_array ['new_country'] =  rgpost( 'input_6_6', true ); 
				$data_array ['new_phone'] =  rgpost( 'input_7', true ); 
				$data_array ['new_email'] =  rgpost( 'input_8', true ); 
				
				$data_array ['change_effective_date'] =  rgpost( 'input_9', true ); 
				$data_array ['new_insurance_name'] =  rgpost( 'input_11', true ); 
				$data_array ['member_id'] =  rgpost( 'input_12', true ); 
				$data_array ['rx_group'] =  rgpost( 'input_13', true ); 
				$data_array ['rx_bin'] =  rgpost( 'input_14', true ); 
				$data_array ['customer_service_phone'] =  rgpost( 'input_15', true ); 
				$data_array ['insurance_effective_date'] =  rgpost( 'input_16', true ); 
				
				
				$data_array ['date_added'] =  date_i18n('Y-m-d H:i:s');
				$this->data_array = $data_array;			
				//echo "<pre>"; print_r($data_array);
			}
			public function remove_form_entry( $entry ) {
				if( $this->inert_id )
				GFAPI::delete_entry( $entry['id'] );
			}
	}
	$objGFDC_UPDATE_YOUR_INFO = new GFDC_UPDATE_YOUR_INFO();
}
?>