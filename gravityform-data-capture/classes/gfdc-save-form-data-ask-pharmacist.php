<?php
/**
 * Abstract class which has helper functions to get data from the database
 */
if ( !class_exists('GFDC_ASK_PHARMACIST') )
{
	class GFDC_ASK_PHARMACIST extends GFDC_FORMS
	{   
			private $inert_id = false ;
			public $query_error = false;
			public $data_array = array();
			public function __construct()
			{
				add_action( 'gform_pre_submission_6', array( $this,'gform_pre_submission' ));
				add_action( 'gform_after_submission_6', array( $this,'remove_form_entry' ));
				add_filter( 'gform_confirmation_6', array( $this,'custom_confirmation'), 10, 4 );
			}
			public function fire_insert_query()
			{
				$objFrom = new GFDC_DB_BASE('form_ask_pharmacist');
				$this->inert_id =  $objFrom->insert( $this->data_array ) ;	
				if( $this->inert_id == 0 )
				{
					$this->query_error = true;
				}
				//$this->remove_form_entry( $entry );
			}
			public function custom_confirmation( $confirmation, $form, $entry, $ajax )
			{
				$fire_insert_query = $this->fire_insert_query();
				if( $this->query_error === true )
				{
					$confirmation = "<span style='color:red;'>Could not save data into DB.</span>".do_shortcode( '[gravityform id='.$form['id'].' title=false description=false]' );
				}
				return $confirmation;
			}
			public function gform_pre_submission()
			{
				$data_array = array();
				$data_array ['fname'] =  rgpost( 'input_1_3', true ); 
				$data_array ['lname'] =  rgpost( 'input_1_6', true ); 
				$data_array ['dob'] =  rgpost( 'input_2', true ); 
				$data_array ['phone'] =  rgpost( 'input_4', true ); 
				$data_array ['best_time_call'] =  rgpost( 'input_5', true ); 
				$data_array ['email'] =  rgpost( 'input_7', true ); 
				$data_array ['question'] =  rgpost( 'input_8', true ); 
				$data_array ['date_added'] =  date_i18n('Y-m-d H:i:s');
				$this->data_array = $data_array;
				//echo "<pre>"; print_r($data_array);
			}
			public function remove_form_entry( $entry ) {
				if( $this->inert_id )
				GFAPI::delete_entry( $entry['id'] );
			}
	}
	$objGFDC_ASK_PHARMACIST = new GFDC_ASK_PHARMACIST();
}
?>