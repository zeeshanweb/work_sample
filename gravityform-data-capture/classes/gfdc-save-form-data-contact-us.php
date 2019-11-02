<?php
/**
 * Abstract class which has helper functions to get data from the database
 */
if ( !class_exists('GFDC_CONTACT_US') )
{
	class GFDC_CONTACT_US extends GFDC_FORMS
	{   
			private $inert_id = false ;
			public $query_error = false;
			public $data_array = array();
			public function __construct()
			{
				add_action( 'gform_pre_submission_1', array( $this,'gform_pre_submission' ));
				//add_action( 'gform_after_submission_1', array( $this,'gform_after_submission' ));
				add_filter( 'gform_confirmation_1', array( $this,'custom_confirmation'), 10, 4 );
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
				$objFrom = new GFDC_DB_BASE('form_contact_us');
				$this->inert_id =  $objFrom->insert( $this->data_array ) ;
				if( $this->inert_id == 0 )
				{
					$this->query_error = true;
				}
				$this->remove_form_entry( $entry );
			}
			public function gform_pre_submission()
			{
				$i_am_a = array_filter(array( rgpost( 'input_4_1', true ) , rgpost( 'input_4_2', true ) , rgpost( 'input_4_3', true ) , rgpost( 'input_4_4', true ) , rgpost( 'input_4_5', true ) ));
				if( !empty($i_am_a) )
				{
					$i_am_a = implode(",",$i_am_a);
				}else
				{
					$i_am_a = '';
				}
				$data_array = array();
				$data_array ['name'] =  rgpost( 'input_1', true ); 
				$data_array ['phone'] =  rgpost( 'input_2', true ); 
				$data_array ['email'] =  rgpost( 'input_3', true );; 
				$data_array ['i_am_a'] =  $i_am_a; 
				$data_array ['message'] =  rgpost( 'input_5', true ); 
				$data_array ['date_added'] =  date_i18n('Y-m-d H:i:s');
				$this->data_array = $data_array;	
				//echo "<pre>"; print_r($data_array);
			}
			public function remove_form_entry( $entry ) {
				if( $this->inert_id )
				GFAPI::delete_entry( $entry['id'] );
			}
	}
	$GFDC_CONTACT_US = new GFDC_CONTACT_US();
}
?>