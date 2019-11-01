<?php
class GW_List_Field_Chained_Selects {

	private $_choices         = null;
	private $_enable_product  = null;
	private $_product_id      = null;
	private $_input_counter   = 0;

    protected static $is_script_output = false;

    public function __construct( $args = array() ) {

        // set our default arguments, parse against the provided arguments, and store for use throughout the class
        $this->_args = wp_parse_args( $args, array(
            'form_id'  => false,
            'field_id' => false
        ) );

        // do version check in the init to make sure if GF is going to be loaded, it is already loaded
        add_action( 'init', array( $this, 'init' ) );

    }

    function init() {

        // make sure we're running the required minimum version of Gravity Forms
        if( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
            return;
        }

        // rendering
	    add_filter( 'gform_form_post_get_meta',    array( $this, 'add_product_field' ) );
	    add_filter( 'gform_pre_render',            array( $this, 'load_form_script' ) );
	    add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );
	    add_filter( 'gform_column_input',          array( $this, 'modify_list_field_input_type' ), 10, 6 );
	    add_filter( 'gform_list_field_parameter_delimiter', array( $this, 'set_custom_list_field_delimiter' ), 10, 14 );

	    // submission
	    add_filter( 'gform_product_info', array( $this, 'add_list_field_products' ), 20, 3 );

	    // magic
	    add_action( 'wp_ajax_gwlfcs_get_next_chained_select_choices',        array( $this, 'ajax_get_next_chained_select_choices' ) );
	    add_action( 'wp_ajax_nopriv_gwlfcs_get_next_chained_select_choices', array( $this, 'ajax_get_next_chained_select_choices' ) );
		add_action( 'wp_ajax_save_json_data_function', array( $this, 'save_json_data_function' ) );
        add_action( 'wp_ajax_nopriv_save_json_data_function', array( $this, 'save_json_data_function' ) );

    }

    function load_form_script( $form ) {

        if( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( __class__, 'output_script' ) ) ) {
            add_action( 'wp_footer', array( __class__, 'output_script' ) );
            add_action( 'gform_footer', array( __class__, 'output_script' ) );
        }

        return $form;
    }

    static function output_script() {
        ?>

        <script type="text/javascript">

            ( function( $ ) {

                window.GWListFieldChainedSelects = function( args ) {

	                var self = this;

	                // copy all args to current object: (list expected props)
	                for( prop in args ) {
		                if( args.hasOwnProperty( prop ) ) {
			                self[prop] = args[prop];
		                }
	                }

	                var $form    = $( '#gform_' + self.formId ),
		                $field   = $( '#field_' + self.formId + '_' + self.fieldId ),
		                $product = $( '#ginput_base_price_' + self.formId + '_' + self.productFieldId );

	                self.init = function() {

		                $form.on( 'submit', function() {
			                $field.find( 'select' ).prop( 'disabled', false );
		                } );

		                $field.on( 'change', self.getSelectSelectors().join( ',' ), function() {

			                var $select = $( this ),
				                inputId = self.getInputId( $select );

			                self.populateNextChoices( inputId, $select.val(), $select );
			                self.updatePricing();

			                //$( document ).trigger( 'gform_post_conditional_logic' );

		                } );

		                gform.addFilter( 'gform_list_item_pre_add', function( $clone ) {
			                var selectors = self.getSelectSelectors();
			                selectors.shift();
			                $clone.find( selectors.join( ',' ) ).prop( 'disabled', true );
			                return $clone;
		                } );

		                $field.find( 'img.delete_list_item' ).data( 'onclick', $field.find( 'img.delete_list_item' ).attr( 'onclick' ) ).attr( 'onclick', '' );
		                $field.on( 'click', 'img.delete_list_item', function() {
			                $( this ).parents( '.gfield_list_group' ).detach();
			                self.updatePricing();
			                eval( $field.find( 'img.delete_list_item' ).data( 'onclick' ) );
		                } );

		                self.initSelects();
		                self.updatePricing();

		                $( document ).bind( 'gform_post_conditional_logic', function() {
			                self.updatePricing();
		                } );

	                };

	                self.getInputId = function( $select ) {

		                var $parent = $select.parents( '.gfield_list_cell' ),
			                $row    = $parent.parents( '.gfield_list_group' ),
			                inputId = $row.find( '.gfield_list_cell' ).index( $parent ) + 1;

		                return inputId;
	                };

	                self.populateNextChoices = function( inputId, selectedValue, $select ) {

		                var nextInputId = self.getNextInputId( inputId ),
			                $nextSelect = self.$selects( $select ).filter( '.gfield_list_' + self.fieldId + '_cell' + nextInputId + ' select' );

		                // if there is no $nextSelect, we're at the end of our chain
		                if( $nextSelect.length <= 0 ) {
			                self.resetSelects( $select, true );
			                return;
		                } else {
			               //self.resetSelects( $select );
		                }

		                if( ! selectedValue ) {
			                return;
		                }
						if( selectedValue == 'Computer' )
						{
							//return false;
							$nextSelect.html('<select name="input_' + self.fieldId + '[]" disabled=""><option value="">No options</option></select>');
							$nextSelect.css('opacity', '0.6');
							$nextSelect.prop( 'disabled', true );	
							$nextSelect = self.$selects( $select ).filter( '.gfield_list_' + self.fieldId + '_cell3 > select' );
							//$nextSelect.prop( 'disabled', false );
							//jQuery('.gfield_list_' + self.fieldId + '_cell' + nextInputId + ' select').hide();									
							//$nextSelect.prop( 'disabled', true );					
							$nextSelect.css('opacity', '0.6');
							$nextSelect.html('<select name="input_' + self.fieldId + '[]" disabled=""><option value="">No options</option></select>');
						return false;
						}

		                var loadingText     = 'Loading',
			                $loadingOption  = $( '<option value="">' + loadingText + '...</option>' ),
			                dotCount        = 2,
			                loadingInterval = setInterval( function() {
				                $loadingOption.text( loadingText + ( new Array( dotCount ).join( '.' ) )  );
				                dotCount = dotCount > 3 ? 0 : dotCount + 1;
			                }, 250 );

		                $loadingOption.prependTo( $nextSelect ).prop( 'selected', true );
		                $nextSelect.css( { minWidth: $nextSelect.width() } );
		                $loadingOption.text( loadingText + '.' );
						var get_tooth_val = jQuery('input[name=input_1]:checked').val();

		                $.post( self.ajaxUrl, {
			                action:        'gwlfcs_get_next_chained_select_choices',
			                input_id:      inputId,
			                next_input_id: self.getNextInputId( inputId ),
			                form_id:       self.formId,
			                field_id:      self.fieldId,
							get_tooth_val: get_tooth_val,
			                value:         self.getChainedSelectsValue( $select )
		                }, function( response ) {

			                clearInterval( loadingInterval );
			                $loadingOption.remove();

			                if( ! response ) {
				                return;
			                }

			                var choices       = $.parseJSON( response ),
				                optionsMarkup = '';

			                $nextSelect.find( 'option:not(:first)' ).remove();
							if( nextInputId == 2 )
							{
								$nextSelect.css('opacity', '1');
								$nextSelect.html('<option value="">Select a thickness</option></select>');
							}else
							{
								$nextSelect.css('opacity', '1');
								$nextSelect.html('<option value="">Select a tooth spacing</option></select>');
							}						

			                if( choices.length <= 0 ) {

				                self.resetSelects( $select, true );

			                } else {

				                $.each( choices, function( i, choice ) {
					                optionsMarkup += '<option value="' + choice.value + '">' + choice.text + '</option>';
				                } );

				                $nextSelect.show().append( optionsMarkup );

				                // the placeholder will be selected by default, rather than removing it and re-adding, just force the noOptions option to be selected
				                if( choices[0].noOptions ) {

					                var $noOption = $nextSelect.find( 'option:last-child' ).clone(),
						                $nextSelects = $nextSelect.parents( 'span' ).nextAll().find( 'select' );

					                $nextSelects.append( $noOption );

					                $nextSelects.add( $nextSelect )
						                .addClass( 'gf_no_options' )
						                .find( 'option:last-child' )
						                .prop( 'selected', true );

				                } else {
					                $nextSelect
						                .removeClass( 'gf_no_options' )
						                .prop( 'disabled', false );
				                }

			                }

		                } );

	                };

	                self.getChainedSelectsValue = function( $select ) {

		                var value = {};

		                self.$selects( $select ).each( function() {
			                var inputId = self.getInputId( $( this ) );
			                value[ inputId ] = $( this ).val();
		                } );

		                return value;
	                };

	                self.getNextInputId = function( currentInputId ) {

		                var nextInputIndex = self.getInputIndex( currentInputId ) + 1;

		                return self.columns[ nextInputIndex ];
	                };

	                self.getInputIndex = function( inputId ) {

		                var index = [];

		                $.each( self.columns, function( key, value ) {
			                index[ value ] = key;
		                } );

		                return index[ inputId ];
	                };

	                self.initSelects = function( $selects ) {

		                if( typeof $selects == 'undefined' ) {
			                $selects = self.$selects();
		                }

		                $selects.filter( function() {
			                return $( this ).hasClass( 'gf_no_options' ) || $( this ).find( 'option' ).length <= 1 || $( this ).find( 'option' ).length == $( this ).find( 'option[value=""]' ).length;
		                } ).prop( 'disabled', true );

	                };

	                self.resetSelects = function( $currentSelect ) {

		                var currentInputId    = self.getInputId( $currentSelect ),
			                currentInputIndex = self.getInputIndex( currentInputId ),
			                $nextSelects      = self.$selects( $currentSelect ).filter( ':gt(' + currentInputIndex + ')' );

		                $nextSelects
		                    .prop( 'disabled', true )
			                .find( 'option:not(:first)' )
			                .remove()
			                .val( '' )
			                .change();

	                };

	                self.getSelectSelectors = function() {

		                var selectors = [];

		                for( var i = 0; i < self.columns.length; i++ ) {
			                selectors.push( '.gfield_list_' + self.fieldId + '_cell' + self.columns[i] + ' select' );
		                }

		                return selectors;
	                };

	                self.$selects = function( $currentSelect ) {

		                var $parent = $field;

		                // if current select is provided, find selects of the current row only
		                if( typeof $currentSelect != 'undefined' ) {
			                $parent = $currentSelect.parents( '.gfield_list_group' );
		                }

		                return $parent.find( self.getSelectSelectors().join( ',' ) );
	                };

	                self.updatePricing = function() {

		                var total = 0;

		                if( $field.css( 'display' ) != 'none' ) {

			                var $inputs = $field.find( 'input[name="input_' + self.fieldId + '[]"], select[name="input_' + self.fieldId + '[]"]' );

			                $inputs.each( function( i, input ) {

				                var value = $( input ).val(),
					                bits  = value.split( '|' ),
					                price = bits[1] ? parseFloat( bits[1] ) : 0;

				                total += price;

			                } );

		                }

		                if( $product.val() != total ) {
			                $product.val( total ).change();
			                gformCalculateTotalPrice( self.formId );
		                }

	                };

	                self.init();

                };

            } )( jQuery );

        </script>

        <?php

        self::$is_script_output = true;

    }

    function add_init_script( $form ) {

        if( ! $this->is_applicable_form( $form ) ) {
            return;
        }

        $args = array(
            'formId'  => $this->_args['form_id'],
            'fieldId' => $this->_args['field_id'],
	        'columns' => $this->_args['columns'],
	        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	        'productFieldId' => $this->_product_id,
        );

        $script = 'new GWListFieldChainedSelects( ' . json_encode( $args ) . ' );';
        $slug   = implode( '_', array( 'gw_list_field_chained_selects', $this->_args['form_id'], $this->_args['field_id'] ) );

        GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

    }

	function modify_list_field_input_type( $input, $field, $column, $value, $form_id, $input_id /* aka $column_index */ ) {

		if( ! $this->is_applicable_field( $field ) ) {
			return $input;
		}

		$this->_input_counter++;
		$row = ceil( $this->_input_counter / count( $field->choices ) );
		$full_chain_value = $this->get_chain_value_by_row( $field, $row );

		$input_id = $this->get_column_index( $column, $field );

		if( $this->is_applicable_input( $input_id, $field ) ) {

			$choices    = $this->get_input_choices( $full_chain_value, $input_id );
			$no_options = empty( $choices );

			if( $no_options ) {
				array_unshift( $choices, array(
					'text'       => __( 'No options' ),
					'value'      => '',
					'isSelected' => true,
					'noOptions'  => true,
				) );
			}

			array_unshift( $choices, array(
				'text'       => sprintf( __( 'Select a %s' ), $column ),
				'value'      => '',
				'isSelected' => ! $no_options,
			) );

			$input = array(
				'type'    => 'select',
				'choices' => $choices
			);

		}

	    return $input;
    }

	function get_chain_value_by_row( $field, $row ) {

		$full_list_value = GFFormsModel::get_field_value( $field );

		if( empty( $full_list_value ) ) {
			$return = array_map(
				function( $value ) {
					return '';
				},
				array_flip( $this->_args['columns'] )
			);
		} else {
			$return = array_values( $full_list_value[ $row - 1 ] );
		}

		return $return;
	}

    function is_applicable_form( $form ) {

        $form_id = isset( $form['id'] ) ? $form['id'] : $form;

        return $form_id == $this->_args['form_id'];
    }

	function is_applicable_field( $field ) {
		return $field->id == $this->_args['field_id'] && $this->is_applicable_form( $field->formId );
	}

	function is_applicable_input( $index, $field ) {
		return $this->is_applicable_field( $field ) && in_array( $index, $this->_args['columns'] );
	}

	function get_input_choices( $chain_value, $input_id = false, $depth = false, $choices = null, $full_chain_value = null ) {

		$full_chain_value = $full_chain_value !== null ? $full_chain_value : $chain_value;
		$value            = array_shift( $chain_value );
		$index            = $input_id ? $this->get_input_index( $input_id ) : 0;
		$depth            = $depth ? $depth : 0;
		$choices          = $choices !== null ? $choices: $this->get_choices();
		$input_choices    = array();

		if ( $depth == $index ) {
			$input_choices = $choices;
		} else {
			foreach ( $choices as $choice ) {
				if ( $choice['value'] == $value ) {
					$input_choices = $this->get_input_choices( $chain_value, $input_id, $depth + 1, isset( $choice['choices'] ) ? $choice['choices'] : array(), $full_chain_value );
					break;
				}
			}
		}

		if ( empty( $input_choices ) ) {
			if( $this->get_previous_input_value( $input_id, $full_chain_value ) ) {
				$input_choices = array(
					array(
						'text'       => __( 'No options' ),
						'value'      => '',
						'isSelected' => true,
						'noOptions'  => true,
					)
				);
			}
		}

		return $input_choices;
	}

	function get_choices() {

		if( $this->_choices != null ) {
			return $this->_choices;
		}

		$choices = $this->_args['choices'];

		if( ! is_array( $choices ) ) {

			$form  = GFAPI::get_form( $this->_args['form_id'] );
			$field = GFFormsModel::get_field( $form, $choices );

			if( is_callable( array( $field, 'get_input_type' ) ) && $field->get_input_type() == 'html' ) {
				$choices = $this->convert_string_to_choices( $field->content );
			} else {
				$choices = $field['choices']; // $field->choices; @todo
			}

		}

		$this->_choices = $choices;

		return $this->_choices;
	}

	function convert_string_to_choices( &$string, $depth = 0 ) {

		if( is_array( $string ) ) {
			$lines = &$string;
		} else {
			$lines = explode( "\n", $string );
		}

		$choices = array();

		while( count( $lines ) > 0 ) {

			$line       = reset( $lines );
			$dash_count = $this->get_dash_count( $line );

			if( $dash_count > $depth ) {

				$choices[ count( $choices ) - 1 ]['choices'] = $this->convert_string_to_choices( $lines, $dash_count );

			} else if( $dash_count < $depth ) {

				break;

			} else {

				// remove current line
				array_shift( $lines );

				$cleaned = trim( $line, ' -' );

				list( $text, $value, $price ) = array_pad( explode( '|', $cleaned ), 3, false );

				if( ! $value ) {
					$value = $text;
				}

				if( $price ) {
					$value .= '|' . $price;
					$this->_enable_product = true; // used to flag the addition of a hidden product field for displaying list field total on the frontend
				}

				$choices[] = array(
					'text'  => $text,
					'value' => $value,
					'price' => $price,
				);

			}

		}

		return $choices;
	}

	function get_input_index( $input_id ) {
		$index = array_flip( $this->_args['columns'] );
		return $index[ $input_id ];
	}

	function get_previous_input_value( $current_input_id, $full_chain_value ) {

		$current_input_index = $this->get_input_index( $current_input_id );
		$prev_input_index    = $current_input_index - 1;
		$prev_input_id       = $this->_args['columns'][ $prev_input_index ];

		return $full_chain_value[ $prev_input_id ];
	}

	function modify_submitted_data( $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return;
		}

	}

	function add_product_field( $form ) {

		if( GFCommon::is_form_editor() || ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		// avoid infinite recursion issue
		remove_filter( 'gform_form_post_get_meta', array( $this, 'add_product_field' ) );

		$is_product_mode_enabled = $this->is_product_mode_enabled();

		add_filter( 'gform_form_post_get_meta', array( $this, 'add_product_field' ) );

		if( ! $is_product_mode_enabled || ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		$ids               = wp_list_pluck( $form['fields'], 'id' );
		$this->_product_id = max( $ids ) + 1;
		$label             = __( 'Hidden Product Field for List Field Products' );

		$product_field = new GF_Field_HiddenProduct( array(
			'id'               => $this->_product_id,
			'type'             => 'product',
			'inputType'        => 'hiddenproduct',
			'label'            => $label,
			'basePrice'        => 0,
			'conditionalLogic' => 0, // @todo copy from list field
			'inputs'           => array(
				array(
					'id' => $this->_product_id . '.1',
					'label' => $label,
					'name' => ''
				),
				array(
					'id' => $this->_product_id . '.2',
					'label' => sprintf( '%s ( %s )', $label, __( 'Price' ) ),
					'name' => ''
				),
				array(
					'id' => $this->_product_id . '.3',
					'label' => sprintf( '%s ( %s )', $label, __( 'Quantity' ) ),
					'name' => ''
				)
			),
		) );

		$form['fields'][] = $product_field;

		return $form;
	}

	function add_list_field_products( $products, $form, $entry ) {

		if( ! $this->is_applicable_form( $form ) || ! $this->is_product_mode_enabled() ) {
			return $products;
		}

		/**
		 * Add each List field row as a product
		 */
		foreach( $form['fields'] as $field ) {


			if( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$value = $this->get_stashed_list_field_value( $entry['id'], $field->id, rgar( $entry, $field->id ) );
			if( ! $value ) {
				continue;
			}

			$groups = maybe_unserialize( $value );

			foreach( $groups as $group_index => $group ) {

				$group_total   = 0;
				$group_product = array();

				foreach( $group as $value ) {
					list( $text, $price ) = array_pad( explode( '|', $value ), 2, false );
					if( $price ) {
						$group_total += $price;
					}
				}

				if( $group_total > 0 ) {
					$group_product = array(
						'name'     => implode( ' / ', $this->remove_prices( array_filter( $group ) ) ),
						'price'    => $group_total,
						'quantity' => 1
					);
				}

				$group_id = sprintf( '%d.%d', $field->id, $group_index );
				$products['products'] = array( $group_id => $group_product ) + $products['products'];

			}

		}

		/**
		 * Remove Placeholder Product
		 */
		unset( $products['products'][ $this->_product_id ] );

		return $products;
	}

	function get_stashed_list_field_value( $entry_id, $field_id, $default_value = array() ) {
        global $_gform_lead_meta;

		if( $entry_id == null ) {
			return $default_value;
		}

		$key   = 'gwlfcs_stashed_list_field_value_' . $field_id;
		$value = gform_get_meta( $entry_id, $key );
		if( $value === false ) {
			gform_add_meta( $entry_id, $key, $default_value );
			if( isset( $_gform_lead_meta[ $entry_id . '_' . $key ] ) ) {
			    unset( $_gform_lead_meta[ $entry_id . '_' . $key ] );
            }
			GFAPI::update_entry_field( $entry_id, $field_id, null ); // delete list field value from the entry
			$value = $default_value;
		}

		return $value;
	}

	function get_column_index( $column, $field ) {

		$column_index = 1;

		if ( is_array( $field->choices ) ) {
			foreach ( $field->choices as $choice ) {
				if ( $choice['text'] == $column ) {
					break;
				}
				$column_index ++;
			}
		}

		return $column_index;
	}

	public function remove_prices( $group ) {
		foreach( $group as &$value ) {
			list( $text, $price ) = array_pad( explode( '|', $value ), 2, false );
			$value = $text;
		}
		return $group;
	}

	public function is_product_mode_enabled() {
		if( $this->_enable_product == null ) {
			// get_choices() will set the _enable_product flag
			$this->get_choices();
		}
		return $this->_enable_product;
	}

	public function get_dash_count( $string ) {
		$chars = str_split( $string );
		$count = 0;
		foreach( $chars as $char ) {
			if( $char == '-' ) {
				$count++;
			} else {
				break;
			}
		}
		return $count;
	}

	public function set_custom_list_field_delimiter( $delimiter, $field ) {
		if( $this->is_applicable_field( $field ) ) {
			$delimiter = '||';
		}
		return $delimiter;
	}

	public function ajax_get_next_chained_select_choices() {

		$form_id  = rgpost( 'form_id' );
		$field_id = rgpost( 'field_id' );
		$form     = GFAPI::get_form( $form_id );
		$field    = GFFormsModel::get_field( $form, $field_id );

		if( ! $this->is_applicable_field( $field ) ) {
			return;
		}

		$next_input_id = rgpost( 'next_input_id' );
		$get_tooth_val = rgpost( 'get_tooth_val' );
		$value         = rgpost( 'value' );
		$choices       = $next_input_id ? $this->get_input_choices( $value, $next_input_id ) : array();
		//echo '<pre>';
		//print_r($value);
		//print_r($choices);die;
		if( $get_tooth_val == 'Standard Tooth' && $value['1'] == '1.25' )
		{
			unset($choices[1]);
		}else if( $get_tooth_val == 'Pallet Dismantle' && $value['1'] == '1.25' )
		{
			rsort($choices);
			unset($choices[1]);
		}else if( $get_tooth_val == 'Deck Duster' )
		{
			if( $value['1'] == '1.25' && empty($value['2']) )
			unset($choices[1]);
			if( $value['1'] == '1.25' && $value['2'] == '0.041' )
			{
				unset($choices[2]);
				rsort($choices);
				unset($choices[1]);
			}
			if( $value['1'] == '1.14' && $value['2'] == '0.035' )
			{
				unset($choices[1]);
				sort($choices);
				unset($choices[1]);
			}			
		}

		die( json_encode( $choices ) );
	}
	public function save_json_data_function()
	{
		$form_id  = 1;
		$field_id = 17;
		$form     = GFAPI::get_form( $form_id );
		$field    = GFFormsModel::get_field( $form, $field_id );
		if( ! $this->is_applicable_field( $field ) ) 
		{
			return;
		}
		$choices  = $this->get_input_choices( '', 1 );
		//echo '<pre>';
		//print_r($choices);die;
		die( json_encode( $choices ) );
	}

}

# Configuration

new GW_List_Field_Chained_Selects( array(
	'form_id'          => 1,
	'field_id'         => 17,
	'columns'          => array( 1,2, 3 ),
	'choices'          => 18, // takes a field ID or array of choices
) );