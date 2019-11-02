<?php
/**
 * Abstract class which has helper functions to get data from the database
 */
if ( !class_exists('GFDC_Enrollment_FORM') )
{
	class GFDC_Enrollment_FORM extends GFDC_FORMS
	{   
			private $inert_id = false ;
			public $query_error = false;
			public $data_array = array();
			public function __construct()
			{
				//add_action( 'gform_pre_submission_7', array( $this,'gform_pre_submission' ));
				add_action( 'gform_pre_submission_7', array( $this,'remove_form_entry' ));
				add_filter( 'gform_confirmation_7', array( $this,'custom_confirmation'), 10, 4 );
			}
			
			public function gform_pre_submission()
			{
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
				$objFrom = new GFDC_DB_BASE('form_enrollment_form');
				$this->inert_id =  $objFrom->insert( $this->data_array ) ;
				if( $this->inert_id )
				{
					if(class_exists('gp_nested_forms'))
					{
						$nested_entry_ids = gp_nested_forms()->get_field_value( $form, $entry, 21 );
						if($nested_entry_ids):
							$entriesPM = gp_nested_forms()->get_entries( $nested_entry_ids );
							if($entriesPM)
							{
								$objMIFrom = new GFDC_DB_BASE('form_patient_medication_information');
								foreach($entriesPM as $entryPM)
								{
									$data_mi_array = array();
									$data_mi_array['parent_form_id'] = $this->inert_id;
									$data_mi_array['form_id'] = '7';
									$data_mi_array['patient_medication'] = $entryPM[1];
									$data_mi_array['patient_medication_rx_no'] = $entryPM[2];
									$data_mi_array['remaining_medication_days'] = $entryPM[3];
									//echo "<pre>"; print_r($data_mi_array);
									
									$pm_insert_id  =  $objMIFrom->insert( $data_mi_array ) ;
									if($pm_insert_id)
									{
										//Delete Nested entry
										GFAPI::delete_entry( $entryPM['id'] );
									}else
									{
										$this->query_error = true;
									}
								}
							}
						endif;
					}
					//Delete Mail Entry
					GFAPI::delete_entry( $entry['id'] );
				}else
				{
					$this->query_error = true;
				}
			}
			public function remove_form_entry( $entry, $form ) {
				
				$data_array = array();
				
				$data_array ['fname'] =  rgpost( 'input_1_3', true ); 
				$data_array ['lname'] =  rgpost( 'input_1_6', true ); 
				$data_array ['dob'] =  rgpost( 'input_2', true ); 
				$data_array ['address1'] =  rgpost( 'input_3_1', true ); 
				$data_array ['address2'] =  rgpost( 'input_3_2', true ); 
				$data_array ['city'] =  rgpost( 'input_3_3', true ); 
				$data_array ['state'] =  rgpost( 'input_3_4', true ); 
				$data_array ['zip'] =  rgpost( 'input_3_5', true ); 
				$data_array ['country'] =  rgpost( 'input_3_6', true ); 
				$data_array ['phone'] =  rgpost( 'input_6', true ); 
				$data_array ['email'] =  rgpost( 'input_4', true ); 
				$data_array ['pharmacy_name'] =  rgpost( 'input_5', true ); 
				$data_array ['pharmacy_phone'] =  rgpost( 'input_7', true ); 
				$data_array ['physician_fname'] =  rgpost( 'input_8_1', true ); 
				$data_array ['physician_lname'] =  rgpost( 'input_8_2', true ); 
				$data_array ['physician_phone'] =  rgpost( 'input_23', true ); 
				$data_array ['prescription_card_member_id'] =  rgpost( 'input_10', true ); 
				$data_array ['prescription_card_bin'] =  rgpost( 'input_11', true ); 
				$data_array ['prescription_card_group'] =  rgpost( 'input_12', true ); 
				$data_array ['prescription_info'] =  rgpost( 'input_24', true ); 
				$data_array ['request_brand_medication'] =  rgpost( 'input_20', true ); 				
				
				$data_array ['date_added'] =  date_i18n('Y-m-d H:i:s');
				$this->data_array = $data_array;				
				
			}
	}
	$objGFDC_Enrollment_FORM = new GFDC_Enrollment_FORM();
}
?>