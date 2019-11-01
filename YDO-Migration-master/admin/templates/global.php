<div class="wrap">

	<header>

	<a href="https://xyz.com/"><img src="<?php echo WP_PLUGIN_URL.'/ydo-migration/assets/img/logo-YDO-2018-300.png'; ?>" width="280px" /></a>

  <p class="poweredBy u-pull-right"><a href=""><img src="" width="140px"/></a></p>

	</header>

    

	<?php 

	if ( YdoSettingsPage::getSiteType() == 'dev' )

		{

		  if ( isset( $_GET['settings-updated'] ) ) {

			 // add settings saved message with the class of "updated"

			 //add_settings_error( 'wporg_messages', 'wporg_message', __( 'Settings Saved', 'wporg' ), 'updated' );

		 }

		    settings_errors( 'wporg_messages' );

		   require_once YDO_PLUGIN_DIR . '/admin/templates/dev_tostaging.php';

		}else if ( YdoSettingsPage::getSiteType() == 'staging' )

		{

			require_once YDO_PLUGIN_DIR . '/admin/templates/staging_to_prod.php';

		}

		else if ( YdoSettingsPage::getSiteType() == 'live' )

		{

			require_once YDO_PLUGIN_DIR . '/admin/templates/prod_to_staging.php';

		}else

		{

			require_once YDO_PLUGIN_DIR . '/admin/templates/tabs.php';

		}	

	?>

</div>    
