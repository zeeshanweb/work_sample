<?php
/**
 * Abstract class which has helper functions to get data from the database
 */
if ( !class_exists('GFDC_Medication_Management_Enrollment') )
{
	class GFDC_Medication_Management_Enrollment extends GFDC_FORMS
	{   
			private $inert_id = false ;
			public $query_error = false;
			public $data_array = array();
			public function __construct()
			{
				add_action( 'gform_pre_submission_13', array( $this,'gform_pre_submission' ));
				add_action( 'gform_after_submission_13', array( $this,'remove_form_entry' ));
				add_filter( 'gform_confirmation_13', array( $this,'custom_confirmation'), 10, 4 );
			}
			public function fire_insert_query()
			{
				$objFrom = new GFDC_DB_BASE('form_mediation_management_enrollment_form');
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
				$physician_name = '';
				$physician_phone_number = '';
				$time_for_contact = '';
				$input_46 = rgpost( 'input_46', true );
				$i = 1;
				foreach( $input_46 as $val )
				{
				  if ($i % 2 == 0)
				  {
					 $physician_phone_number .= $val.','; 
				  }else
				  {
					  $physician_name .= $val.','; 
				  }
				  $i++;	
				}
				$input_51_1 = rgpost( 'input_51_1', true );
				$input_51_2 = rgpost( 'input_51_2', true );
				if( isset($input_51_1) && !empty($input_51_1) )
				{
					$time_for_contact = $input_51_1;
				}
				if( isset($input_51_2) && !empty($input_51_2) )
				{
					if( isset($time_for_contact) && !empty($time_for_contact) )
					$time_for_contact .= ','.$input_51_2;
					else
					$time_for_contact = $input_51_2;
				}
				$data_array = array();
				$data_array ['patient_first_name'] =  rgpost( 'input_1_3', true ); 
				$data_array ['patient_last_name'] =  rgpost( 'input_1_6', true ); 
				$data_array ['patient_dob'] =  rgpost( 'input_2', true ); 
				$data_array ['patient_street_address'] =  rgpost( 'input_3_1', true ); 
				$data_array ['patient_street_address2'] =  rgpost( 'input_3_2', true ); 
				$data_array ['patient_city'] =  rgpost( 'input_3_3', true ); 
				$data_array ['patient_state'] =  rgpost( 'input_3_4', true );
				$data_array ['patient_zip'] =  rgpost( 'input_3_5', true );
				$data_array ['patient_country'] =  rgpost( 'input_3_6', true );
				$data_array ['patient_phone_no'] =  rgpost( 'input_6', true );
				$data_array ['patient_alternate_phone_no'] =  rgpost( 'input_26', true );
				$data_array ['patient_email'] =  rgpost( 'input_4', true ); 
				$data_array ['patient_contact_time'] =  $time_for_contact; 
				$data_array ['patient_medications_on_daily_basis'] =  rgpost( 'input_38', true ); 
				$data_array ['physician_name'] =  rtrim($physician_name,',');
				$data_array ['physician_phone_number'] =  rtrim($physician_phone_number,','); 
				$data_array ['date_added'] =  date_i18n('Y-m-d H:i:s');
				$this->data_array = $data_array;
				//echo "<pre>"; print_r($data_array);
			}
			public function remove_form_entry( $entry ) {
				if( $this->inert_id )
				GFAPI::delete_entry( $entry['id'] );
			}
	}
	$GFDC_Medication_Management_Enrollment = new GFDC_Medication_Management_Enrollment();
}
?>