<?php
		$_error = NULL;
		if (array_key_exists('error', $_REQUEST)) {
			$_error = $_REQUEST['error'];
		}
?>
	<div class="row">

		<div class="seven columns">
			<form id="wpe_migrate_form" dummy=">" action="" target="_blank" method="post" name="signup">
            <?php //settings_fields( 'my_option_group' ); ?>
            <?php //do_settings_sections( 'my_option_group' ); ['cred']['prod_to_staging']$credential['cred']['prod_to_staging']['port'] ? $credential['cred']['prod_to_staging']['port'] : '2083';
			$url = '';
			if (class_exists('YdoMigrationStagingToProd'))
			{
				$credential = get_option( 'migration_credential' );
			    $YdoMigrationProdToStaging = new YdoMigrationProdToStaging();
			    $url = $YdoMigrationProdToStaging->get_replaced_site_url('');
				$get_directory = $YdoMigrationProdToStaging->get_directory();
			}			
			?>
				<h1>Migrate My Site to PROD</h1>
				<p>The YDO Automated Migration plugin allows you to easily migrate your entire WordPress site from
					staging to PROD.</p>
                <p><!--You PROD document root will be: <span style="color:red;"><?php echo $get_directory;?></span><br />-->
                You PROD url will be: <span style="color:red;"><?php echo $url;?></span></p>
				<p>Please enter the values into the fields below, and click "Migrate".</p>

                <?php echo wp_nonce_field( 'staging_to_prod_action', 'staging_to_prod_name' ) ?>
				<div class="row">
					<div class="six columns">
								<label class="control-label" for="inputip"> Cpanel Host </label>
								<input type="text" value="<?php echo $credential['cred']['prod_to_staging']['host'];?>" required="required" class="u-full-width" placeholder="ex. 123.456.789.101" name="host">
								<p class="help-block"></p>
					</div>
                    <div class="six columns">
								<label class="control-label" for="inputip"> Cpanel Port </label>
								<input type="text" value="<?php echo $credential['cred']['prod_to_staging']['port'] ? $credential['cred']['prod_to_staging']['port'] : '2083';?>" required="required" class="u-full-width" placeholder="2083" name="port">
								<p class="help-block"></p>
					</div>
				</div>
				<div class="row">
					<div class="six columns">
						<label class="control-label" for="input01">Cpanel Username</label>
								<input type="text" value="<?php echo $credential['cred']['prod_to_staging']['username'];?>" required="required" class="u-full-width" placeholder="" name="username">
								<p class="help-block"></p>
					</div>
					<div class="six columns">
						<label class="control-label" for="input02">Cpanel Password</label>
								<input type="password" value="<?php echo $credential['cred']['prod_to_staging']['password'];?>" required="required" class="u-full-width" placeholder="" name="password">
					</div>
				</div>
					<hr/>
						<?php //submit_button(); ?>
                        <input type='submit' id='migratesubmit' value='Copy staging to production' class="button button-primary">
			</form>
		</div>
		</div>
        
	<script type="text/javascript">
		jQuery(document).ready(function () {
			<?php if (array_key_exists('auth_required_dest', $_REQUEST)) { ?>
					jQuery('#password-auth').show();
					jQuery('.sourceAuthError').show();
					jQuery('#dest-auth').addClass("attentionNeeded");
			<?php } ?>

			<?php if (array_key_exists('auth_required_source', $_REQUEST)) { ?>
					jQuery('#password-auth').show();
					jQuery('.destAuthError').show();
					jQuery('#source-auth').addClass("attentionNeeded");
			<?php } ?>
			jQuery('#advanced-options-toggle').click(function() {
				jQuery('#password-auth').toggle();
				return false;
			});
			jQuery('#wpe_migrate_form').submit(function() {
				var c = confirm("Are you sure you want to migrate?");
				return c; //you can just return c because it will be true or false
			});
		});
	</script>