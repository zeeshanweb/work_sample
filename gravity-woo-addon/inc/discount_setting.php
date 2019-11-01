<?php
class DiscountSettingPage
{
    static $instance;
    private $options;
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }
    public function add_plugin_page()
    {
        add_submenu_page( 'wp_dimension_listing', 'Discount Setting', 'Discount Setting','manage_options', 'discount_setting',[ $this,'discount_setting_page_callback' ]);
    }
    public function discount_setting_page_callback()
    {
        // Set class property
        $this->options = get_option( 'discount_option_name' );
        ?>
        <div class="wrap">
            <h1>Discount Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'discount_option_group' );
                do_settings_sections( 'discount-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }
    public function page_init()
    {        
        register_setting(
            'discount_option_group', // Option group
            'discount_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'discount-setting-admin' // Page
        );  

        add_settings_field(
            'box_one_to_three', // ID
            'Box Price 1 to 3', // Title 
            array( $this, 'box_one_to_three_callback' ), // Callback
            'discount-setting-admin', // Page
            'setting_section_id' // Section           
        );
		add_settings_field(
            'discount', // ID
            'Discount', // Title 
            array( $this, 'discount_callback' ), // Callback
            'discount-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'box_four_ore_more', 
            'Box Price 4 or More', 
            array( $this, 'box_four_ore_more_callback' ), 
            'discount-setting-admin', 
            'setting_section_id'
        );      
    }
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['box_one_to_three'] ) )
            $new_input['box_one_to_three'] = sanitize_text_field( $input['box_one_to_three'] );

        if( isset( $input['discount'] ) )
            $new_input['discount'] = sanitize_text_field( $input['discount'] );
			
		if( isset( $input['box_four_ore_more'] ) )
            $new_input['box_four_ore_more'] = sanitize_text_field( $input['box_four_ore_more'] );

        return $new_input;
    }
    public function print_section_info()
    {
        
    }
    public function box_one_to_three_callback()
    {
        printf(
            '<input type="text" id="box_one_to_three" name="discount_option_name[box_one_to_three]" value="%s" />',
            isset( $this->options['box_one_to_three'] ) ? esc_attr( $this->options['box_one_to_three']) : ''
        );
    }
	public function discount_callback()
    {
        printf(
            '<input type="text" id="discount" name="discount_option_name[discount]" value="%s" />',
            isset( $this->options['discount'] ) ? esc_attr( $this->options['discount']) : ''
        );
    }
    public function box_four_ore_more_callback()
    {
        printf(
            '<input type="text" id="box_four_ore_more" name="discount_option_name[box_four_ore_more]" value="%s" />',
            isset( $this->options['box_four_ore_more'] ) ? esc_attr( $this->options['box_four_ore_more']) : ''
        );
    }
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

add_action( 'plugins_loaded', function () {
	DiscountSettingPage::get_instance();
} );