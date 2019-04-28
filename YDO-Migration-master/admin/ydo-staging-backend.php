<?php
class YdoSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
	var $values = array();
	var $connect_it = '';
	var $login_result = false;
	var $ftp_connect_result = '';

    /**
     * Start up
     */
    public function __construct()
    {
        $this->values = array( 'host' => get_option('host') , 'username' => get_option('username') , 'password' => get_option('password') );
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );	
		add_action('admin_enqueue_scripts', array($this, 'ydoAdminMenu'));	
    }    
    /**
     * Add options page
     */
    public function ydoAdminMenu()
	{
		if ($_GET['page'] === 'ydo_migration') {
			wp_register_style('ydo_normalize', plugins_url('ydo-migration/assets/css/normalize.css' ));
			wp_register_style('ydo_skeleton', plugins_url('ydo-migration/assets/css/skeleton.css'));
			wp_register_style('ydo_form-styles', plugins_url('ydo-migration/assets/css/form-styles.css' ));
			wp_enqueue_style('ydo_skeleton');
			wp_enqueue_style('ydo_skeleton');
			wp_enqueue_style('ydo_form-styles');
		}
	}
	public static function getSiteType()
	{
		$site_url = get_site_url();
		if (strpos($site_url, 'ydodev') !== false)
		{
		  return 'dev';
		}else if (strpos($site_url, 'staging') !== false)
		{
			return 'staging';
		}
		else if (strpos($site_url, 'staging') === false || strpos($site_url, 'ydodev') === false)
		{
			return 'live';
		}else
		{
			return '';
		}
	}	
	public function add_plugin_page()
    {
		// This page will be under "Settings"
        add_menu_page('Migration', 'YDO Migration', 'manage_options', 'ydo_migration',
					array($this, 'adminPage'), WP_PLUGIN_URL.'/ydo-migration/assets/img/favicon.ico');
		
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'my_option_name' );
        ?>
        <div class="wrap">
            <h1>Plugin Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );
                do_settings_sections( 'ydo-setting-admin' );
                submit_button('Migrate');
            ?>
            </form>
        </div>
        <?php
    }
	public function adminPage() {
		require_once YDO_PLUGIN_DIR . '/admin/templates/global.php';			
	}

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting('my_option_group', 'email'/*, array( $this, 'sanitize' )*/);
		register_setting( 'my_option_group', 'url' );
		register_setting( 'my_option_group', 'host' );
		register_setting( 'my_option_group', 'username' );
		register_setting( 'my_option_group', 'password' );		
             
    }
}

if( is_admin() )
    $YdoSettingsPage = new YdoSettingsPage();