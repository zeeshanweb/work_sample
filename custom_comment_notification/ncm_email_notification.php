<?php
/* 
Plugin Name: Custom Comment Notification
Plugin URI: https://xyz.com/
Description: This WordPress Plugin send comment notification to user based on their setiing(every day, every week or on each comment)
Version: 1.0.0
Author: YDO
Author URI: https://xyz.com/
*/
// Plugin Folder Path
if( ! defined( 'ABSPATH' ) )
{
	die();
}
if( !defined( 'NCM_PLUGIN_DIR' ) ) {
   define( 'NCM_PLUGIN_DIR', plugin_dir_path(  __FILE__ ) );
}
// Plugin Folder Path
if( !defined( 'NCM_PLUGIN_FILE' ) ) {
   define( 'NCM_PLUGIN_FILE', __FILE__ );
}
define( 'NCM_PLUGIN_URL' , plugin_dir_url( __FILE__ ) );
class Ncm_Email_Notification
{
	var $people_array = array(20 ,21 ,22 ,23 ,19, 1);
	static $instance;
	public function __construct()
	{
		add_action( 'comment_post', array( $this, 'ncm_show_message_function'), 10, 2 );
		add_action( 'transition_post_status', array( $this,'action_set_object_terms'), 10, 3 );	
		add_action( 'init' , array( $this,'ncm_function_for_email_digest') );
		add_action( 'wp' , array( $this,'ncm_run_cron') );	
	}
	public function action_set_object_terms( $new_status, $old_status, $post ) 
	{ 
    	if ( $new_status == 'publish' && $old_status != 'publish' )
		{
			$terms = get_the_category( $post->ID );
			$terms = $terms[0]->term_id;
			$this->update_post_id_into_usermeta( $terms , $post->ID );
		}
    } 
	public function update_post_id_into_usermeta( $terms = '' , $post_id = '' )
	{
		if( empty($terms) || empty($post_id) )
		return;
		global $wpdb;
		$object_id = $post_id;
		$get_current_post_data = get_post( $object_id );
		$current_post_author = $get_current_post_data->post_author;
		$user_meta_value = array();
		$myqueyry = $wpdb->get_results($wpdb->prepare( "SELECT * FROM $wpdb->usermeta WHERE meta_key = 'wpeddit_subs' and meta_value like %s ",'%'.$terms.'%') );		
		foreach( $myqueyry as $myqueyry_val )
		{
			if( $this->send_mail_only_to_selected_user($myqueyry_val->user_id) === false )
		    continue;			
			if($current_post_author == $myqueyry_val->user_id) continue;
			$user_meta_value = array();
			$subs_arr = explode(',', $myqueyry_val->meta_value);
			if ( !in_array($terms, $subs_arr) )
			continue;
			$user_meta_value = get_user_meta( $myqueyry_val->user_id, 'email_digest_post_id' , true );
			
			if( isset($user_meta_value) && !empty($user_meta_value) )
			{
				$user_meta_value = $user_meta_value;				
				array_push( $user_meta_value , $object_id );				
				$user_meta_value = array_unique($user_meta_value);				
			}else
			{
				$user_meta_value[] = $object_id;
				$user_meta_value = array_unique($user_meta_value);
			}
			update_user_meta( $myqueyry_val->user_id, 'email_digest_post_id', $user_meta_value );
		}	
		$this->for_sending_digest_mail_to_user_for_post('eachupdate');
  
	}
	public function ncm_show_message_function( $comment_ID, $comment_approved ) {
	//echo $comment_ID;die;
	$email = array();
	$meta_value = array();
	$user_meta_value = array();
	$comment = get_comment( $comment_ID );
	$current_comment_author_email = $comment->comment_author_email;
	$post    = get_post( $comment->comment_post_ID );
	//post author comment id updtae
	$post_author_user_meta_value = get_user_meta( $post->post_author, 'email_digest_comment_id' , true );
	if( isset($post_author_user_meta_value) && !empty($user_meta_value) )
	{
		$post_author_user_meta_value = $post_author_user_meta_value;				
		array_push( $post_author_user_meta_value , $comment_ID );				
		$post_author_user_meta_value_val = $post_author_user_meta_value;				
	}else
	{
		$post_author_user_meta_value[] = $comment_ID;
		$post_author_user_meta_value_val = $post_author_user_meta_value;
	}
	array_push( $email , $post->post_author );
	if( $this->send_mail_only_to_selected_user($post->post_author) === true )
	update_user_meta( $post->post_author, 'email_digest_comment_id', $post_author_user_meta_value_val );	
	//commente
	$args = array( 'post_id' => $post->ID );
	$comments = get_comments($args);
	foreach($comments as $comment)
	{
		if( $current_comment_author_email == $comment->comment_author_email )
		continue;
		$user_meta_value = array();
		$author  = get_user_by( 'email' , $comment->comment_author_email );
		if( $this->send_mail_only_to_selected_user($author->ID) === false )
		continue;
		//delete_user_meta($author->ID, 'email_digest_comment_id');
		if( !in_array( $comment->user_id , $email ) )
		{
			array_push( $email , $comment->user_id );
			$user_meta_value = get_user_meta( $author->ID, 'email_digest_comment_id' , true );
			if( isset($user_meta_value) && !empty($user_meta_value) )
			{
				$user_meta_value = $user_meta_value;				
				array_push( $user_meta_value , $comment_ID );				
				$user_meta_value_val = $user_meta_value;				
			}else
			{
				$user_meta_value[] = $comment_ID;
				$user_meta_value_val = $user_meta_value;
			}
			update_user_meta( $author->ID, 'email_digest_comment_id', $user_meta_value_val );
		}		
		//echo '<pre>';
		//print_r($user_meta_value);die;		
	}
	/*print_r($email);
	die;
	if( 1 === $comment_approved ){
		//function logic goes here
	}*/
	$this->for_sending_digest_mail_to_user_for_comment('eachupdate');
}
//sending mail to user for post
public function for_sending_digest_mail_to_user_for_post( $get_cron_run_value = '' )
{
	global $wpdb;
	$myqueyry_for_post = $wpdb->get_results( "SELECT * FROM $wpdb->usermeta WHERE meta_key = 'email_digest_post_id' and meta_value != ''" );
	foreach( $myqueyry_for_post as $myqueyry_for_post_val )
	{
		if( $this->send_mail_only_to_selected_user($myqueyry_for_post_val->user_id) === false )
		continue;
		$post_digest_setting = get_user_meta( $myqueyry_for_post_val->user_id, 'post_digest_setting_key' , true );
		$get_all_user_detail = get_user_by( 'id' , $myqueyry_for_post_val->user_id );
		if( !$get_all_user_detail )
		continue;
		if( !empty($post_digest_setting) && $post_digest_setting != $get_cron_run_value )
		continue;
		$get_post_id = get_user_meta( $myqueyry_for_post_val->user_id, 'email_digest_post_id' , true );
		$message_content = '';
		if (is_array($get_post_id) || is_object($get_post_id))
		{
			foreach( $get_post_id as $get_post_id_val )
			{
				$get_commented_id_mail_not_sent = array();
				$content = '';
				$get_post_data = get_post( $get_post_id_val );
				if( empty($get_post_data) || $get_post_data->post_status == 'trash' )
				continue;
				//echo '<pre>';
				//print_r($get_post_id_val);die;
				if( $get_cron_run_value == 'eachupdate' )
					{
						$date = date('F-d-Y');
						$content_html_file = file_get_contents(get_template_directory_uri().'/html_template/'.$get_cron_run_value.'_posts.html');
						$message_content = 'There is a new post posted in your subscribed forum.<br>'.'<a href="'.get_permalink($get_post_id_val).'">'.$get_post_data->post_title.'</a>';
						
						$content = str_replace( array('~~DATE~~', '~~CONTENT~~'), array($date, $message_content) ,$content_html_file);
						// $get_all_user_detail->user_email;
						$subject = 'NCM Post Update Notification';
						$function_send_mail_to_user_response = $this->send_mail_to_user( $this->get_email($get_all_user_detail->user_email) , $content , $subject );
						if( $function_send_mail_to_user_response === false )
						{
							array_push( $get_commented_id_mail_not_sent , $get_commented_id_val );
						}
						$message_content ='';
					}				
					else
					{
						$message_content .= 'There is a new post posted in your subscribed forum.<br>'.'<a href="'.get_permalink($get_post_id_val).'">'.$get_post_data->post_title.'</a>';
					
					}			
			}
		}
		if( $message_content && $get_cron_run_value != 'eachupdate' )
			{
				$date = date('F-d-Y');
				$content_html_file = file_get_contents(get_template_directory_uri().'/html_template/'.$get_cron_run_value.'_posts.html');
				
				$content = str_replace( array('~~DATE~~', '~~CONTENT~~'), array($date, $message_content) ,$content_html_file);
				// $get_all_user_detail->user_email;
				if( $get_cron_run_value == 'weekly' )
				{
					$subject = 'NCM Weekly Post Update Notification';
				}else
				{
					$subject = 'NCM Daily Post Update Notification';
				}
				$function_send_mail_to_user_response = $this->send_mail_to_user( $this->get_email($get_all_user_detail->user_email)  , $content , $subject );
				if( $function_send_mail_to_user_response === true )
				{
					update_user_meta( $myqueyry_for_post_val->user_id, 'email_digest_post_id', '' );
				}
			}
			else
			{
				if ( empty($get_commented_id_mail_not_sent) ) 
				{
					$update_usermeta_val = '';
				}else
				{
					$update_usermeta_val = $get_commented_id_mail_not_sent;
				}
				update_user_meta( $myqueyry_for_post_val->user_id, 'email_digest_post_id', $update_usermeta_val );
			}
	}
}
public function for_sending_digest_mail_to_user_for_comment( $get_cron_run_value = '' )
{
	//if( empty($get_cron_run_value) )
	//return;
	global $wpdb;
	//$get_cron_run_value = 'daily';
	$myqueyry = $wpdb->get_results( "SELECT * FROM $wpdb->usermeta WHERE meta_key = 'email_digest_comment_id' and meta_value != ''" );
	if( !empty($myqueyry) )
	{
		foreach( $myqueyry as $myqueyry_val )
		{
			if( $this->send_mail_only_to_selected_user($myqueyry_val->user_id) === false )
		    continue;
			$content = '';
			$message_content = '';
			$email_digest_setting = get_user_meta( $myqueyry_val->user_id, 'email_digest_setting_key' , true );
			$get_all_user_detail = get_user_by( 'id' , $myqueyry_val->user_id );
			if( !$get_all_user_detail )
		    continue;
			if( !empty($email_digest_setting) && $email_digest_setting != $get_cron_run_value )
			continue;
			$get_commented_id = get_user_meta( $myqueyry_val->user_id, 'email_digest_comment_id' , true );//unserialize($myqueyry_val->meta_value);
			if (is_array($get_commented_id) || is_object($get_commented_id))
			{
				foreach( $get_commented_id as $get_commented_id_val )
				{
					//$get_commented_id_val = 80;
					$get_commented_id_mail_not_sent = array();
					//$content = '';
					$get_comment_data = get_comment( $get_commented_id_val );					
					if( empty($get_comment_data) )
					continue;
					$get_post_data = get_post( $get_comment_data->comment_post_ID );
					$get_comment_link = get_comment_link( $get_commented_id_val );
					
					//$content .= $get_comment_data->comment_content.'-'.$get_all_user_detail->user_email .'<br>';
					//$content .= $get_comment_link;
					
					if( $get_cron_run_value == 'eachupdate' )
					{
						$date = date('F-d-Y');
						$content_html_file = file_get_contents(get_template_directory_uri().'/html_template/'.$get_cron_run_value.'_comments.html');
						$message_content = 'There is a new comment posted by '.$get_comment_data->comment_author .' on post <a href="'.$get_comment_link.'">'.$get_post_data->post_title.'</a><br>Comment: '.$get_comment_data->comment_content;
						
						$content = str_replace( array('~~DATE~~', '~~CONTENT~~'), array($date, $message_content) ,$content_html_file);
						// $get_all_user_detail->user_email;
						$subject = 'NCM Comment Notification';
						$function_send_mail_to_user_response = $this->send_mail_to_user( $this->get_email($get_all_user_detail->user_email)  , $content , $subject );
						if( $function_send_mail_to_user_response === false )
						{
							array_push( $get_commented_id_mail_not_sent , $get_commented_id_val );
						}
						$message_content ='';
					}				
					else
					{
						$message_content.= 'Comment posted by '.$get_comment_data->comment_author .' on post <a href="'.$get_comment_link.'">'.$get_post_data->post_title.'</a><br>Comment: '.$get_comment_data->comment_content.' <br><br>';
					
					}
				}				
			}
			if( $message_content && $get_cron_run_value != 'eachupdate' )
			{
				$date = date('F-d-Y');
				$content_html_file = file_get_contents(get_template_directory_uri().'/html_template/'.$get_cron_run_value.'_comments.html');
				
				$content = str_replace( array('~~DATE~~', '~~CONTENT~~'), array($date, $message_content) ,$content_html_file);
				// $get_all_user_detail->user_email;
				if( $get_cron_run_value == 'weekly' )
				{
					$subject = 'NCM Weekly Comment Notification';
				}else
				{
					$subject = 'NCM Daily Comment Notification';
				}
				$function_send_mail_to_user_response = $this->send_mail_to_user( $this->get_email($get_all_user_detail->user_email) , $content , $subject );
				if( $function_send_mail_to_user_response === true )
				{
					update_user_meta( $myqueyry_val->user_id, 'email_digest_comment_id', '' );
				}
			}
			else
			{
				if ( empty($get_commented_id_mail_not_sent) ) 
				{
					$update_usermeta_val = '';
				}else
				{
					$update_usermeta_val = $get_commented_id_mail_not_sent;
				}
				update_user_meta( $myqueyry_val->user_id, 'email_digest_comment_id', $update_usermeta_val );
			}
			//echo '<pre>';
			//print_r($get_commented_id);die;
		}
	}
	//return;	
}
public function send_mail_to_user( $email = '' , $content = '' , $subject = 'NCM Training Notification' )
{	
	if( empty($email) || empty($content) )
	return;
	//echo $content;die;
	$admin_email              = get_option( 'admin_email' );
	$headers                  = 'From: NCM <' . $admin_email . '>' . "\r\n";
	$headers                  .= 'Reply-To: NCM <' . $admin_email . '>' . "\r\n";
	$headers                  .= 'MIME-Version: 1.0' . "\r\n";
	$headers                  .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers                  .= 'X-Mailer: PHP/' . phpversion();
	$mailR = wp_mail( $email, $subject, $content, $headers );
	if ( $mailR ) {
		return true;
		//$error_message = "Mail Sent Successfully";

	} else {
		return false;
		//$error_message = "Unable to send mail";
	}
}

public function ncm_function_for_email_digest()
{
	if ( !empty( $_POST['ncm_email_digest_nonce_field'] ) && wp_verify_nonce( $_POST['ncm_email_digest_nonce_field'], 'ncm_email_digest_nonce' ))
	{
		//$meta_value = array();
		//echo '<pre>';
		$user_id = get_current_user_id();
		//print_r($_POST['digest_frequency_period']);die;
		$meta_value_comment = $_POST['digest_frequency_period'];
		$meta_value_post = $_POST['digest_frequency_period_post'];
		//$meta_value['hour'] = $_POST['digest_frequency_hour'];
		//$meta_value['day'] = $_POST['digest_frequency_day'];
		//$meta_value = serialize($meta_value);
		if( isset($meta_value_comment) && !empty($meta_value_comment) )
		{
			update_user_meta( $user_id, 'email_digest_setting_key', $meta_value_comment );
		}
		if( isset($meta_value_post) && !empty($meta_value_post) )
		{
			update_user_meta( $user_id, 'post_digest_setting_key', $meta_value_post );
		}		
	}
}

public function ncm_run_cron()
{
	$get_query_string_for_cron = $_REQUEST['ncm_run_cron_daily_and_weekly'];
	$date = date('YYYY-MM-DD');
	$weekendDay = false;
	$day = date("D", strtotime($date));
	if( $day == 'Sat' )
	{
		$weekendDay = true;
	}
	if( isset($get_query_string_for_cron) && !empty($get_query_string_for_cron) )
	{
		//echo 'asdasd';die;
		$this->for_sending_digest_mail_to_user_for_comment('daily');
		$this->for_sending_digest_mail_to_user_for_post('daily');
		if( $weekendDay )
		{
			$this->for_sending_digest_mail_to_user_for_comment('weekly');
		    $this->for_sending_digest_mail_to_user_for_post('weekly');
		}die;
	}
}
public function send_mail_only_to_selected_user( $user_id = '' )
{
	return true;
	if( empty($user_id) )
	return false;
	if( !empty($this->people_array) && in_array($user_id, $this->people_array) )
	{
		return true;
	}else
	{
		return false;
	}
	
}
public function get_email($email)
{
	//return 'ydo@mailinator.com';
	return $email;
}
public static function get_instance()
{
	if ( ! isset( self::$instance ) )
	{
		self::$instance = new self();
	}
	return self::$instance;
}
}
add_action( 'plugins_loaded', function ()
{
	Ncm_Email_Notification::get_instance();
});