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

            <?php //do_settings_sections( 'my_option_group' ); 

			$credential = get_option( 'migration_credential' );

			?>

				<h1>Migrate My Site to staging server</h1>

				<p>The YDO Automated Migration plugin allows you to easily migrate your entire WordPress site from

					staging to dev.</p>

				<p>Please enter the values into the fields below, and click "Migrate".</p>



                <?php echo wp_nonce_field( 'dev_to_staging_action', 'dev_to_staging_name' ) ?>

				<!--<div class="row">

						<div class="six columns">

								<label id='label_email'>Email</label>

								<input class="u-full-width" type="text" id="email" name="email">

								<p class="help-block"></p>

						</div>

						<div class="six columns">

								<label class="control-label" for="input02">Destination Site URL</label>

								<input type="text" class="u-full-width" name="url" placeholder="site.ydo.com">

					</div>

				</div>-->

				<div class="row">

					<div class="four columns">

								<label class="control-label" for="inputip"> Cpanel Host </label>

								<input type="text" id="host" required="required" value="<?php echo $credential['cred']['dev_to_staging']['host'];?>" class="u-full-width" placeholder="ex. 123.456.789.101" name="host">

								<p class="help-block"></p>

					</div>

                    <div class="four columns">

								<label class="control-label" for="inputip"> Cpanel Port </label>

								<input type="text" value="<?php echo $credential['cred']['dev_to_staging']['port'] ? $credential['cred']['dev_to_staging']['port'] : '2083';?>" required="required" class="u-full-width" placeholder="2083" name="port">

								<p class="help-block"></p>

					</div>
                    <div class="four columns">

								<label class="control-label" for="inputip"> Domain Name </label>

								<input style="margin:0 0 0 0;" type="text" value="<?php echo $credential['cred']['dev_to_staging']['root_domain'];?>" id="domain_name" required="required" class="u-full-width" placeholder="Enter the domain name under which you want to create subdomain." name="root_domain">

								<span class="help-block" style="font-size:12px;">Enter the domain name under which you want to create subdomain.</span>

					</div>

				</div>  <br />              

				<div class="row">

					<div class="six columns">

						<label class="control-label" for="input01">Cpanel Username</label>

								<input type="text" required="required" value="<?php echo $credential['cred']['dev_to_staging']['username'];?>" class="u-full-width" placeholder="" name="username">

								<p class="help-block"></p>

					</div>

					<div class="six columns">

						<label class="control-label" for="input02">Cpanel Password</label>

								<input type="password" value="<?php echo $credential['cred']['dev_to_staging']['password'];?>" required="required" class="u-full-width" placeholder="" name="password">

					</div>

				</div>

					<hr/>



							<!--<h3>Is Your Site Password Protected?</h3>

							<p>If your current host or your WP Engine install is password protected, you'll need to enter that information here so

							that the migration plugin can access all of your site.

							</p>



								<button name="password-protected" id="advanced-options-toggle" class="button" onclick="javascript; return false">My site is password protected</button>



							<div id="password-auth" style="display:none">

								<div id="source-auth" class="six columns">

									<div class="row">

										<div class="twelve columns">

											<h3>Current</h3>

											<label class="control-label" for="httpauth_src_user">User</label>

											<input type="text" class="u-full-width" name="httpauth_src_user">

											<p class="help-block"></p>

										</div>

									</div>

									<div class="row">

										<div class="twelve columns">

											<label class="control-label" for="httpauth_src_password">Password</label>

											<input type="password" class="u-full-width" name="httpauth_src_password">

											<p class="help-block sourceAuthError error" style="display:none">It appears that your current site that does not exist on WP Engine is password protected. Please provide your username and password

											   for this password protection.</p>

										</div>

									</div>

								</div>



							<div id="dest-auth" class="six columns">

								<div class="row">

									<div class="twelve columns">

										<h3>WP Engine</h3>

										<label class="control-label" for="httpauth_dest_user">Username</label>

										<input type="text" class="u-full-width" name="httpauth_dest_user">

										<p class="help-block"></p>

									</div>

								</div>

								<div class="row">

									<div class="twelve columns">

										<label class="control-label" for="httpauth_dest_password">Password</label>

										<input type="password" class="u-full-width" name="httpauth_dest_password">

										<p class="help-block destAuthError error" style="display:none">It appears that your site on WP Engine is password protected. Please provide your username and password

											 for the password protection.</p>

									</div>

								</div>

						</div>

					</div>



				<hr/>-->

					<!--<div>

						<input type="checkbox" name="consent" onchange="document.getElementById('migratesubmit').disabled = !this.checked;" value="1"/>I agree to WP Engine's <a href="https://xyz.com/" target="_blank" rel="noopener noreferrer">Terms of Service</a>

					</div>--><br />

						<?php //submit_button(); ?>

                        <input type='submit' id='migratesubmit' value='Migrate' class="button button-primary">

			</form>

		</div>



			<!--<div class="five columns">

				<h1>Resources</h1>

				<div style="padding:10px; background-color:#FFF; margin-top:15px;">

					<iframe src="//fast.wistia.net/embed/iframe/0rrkl3w1vu?videoFoam=true" allowtransparency="true" frameborder="0" scrolling="no" class="wistia_embed" name="wistia_embed" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen width="500" height="313"></iframe><script src="//fast.wistia.net/assets/external/E-v1.js"></script>

					<p><i>For full instructions and solutions to common errors, please visit our <a href="http://wpengine.com/support/wp-engine-automatic-migration/">WP Engine Automated Migration</a> support garage article.</i></p>

				</div>

			</div>-->

		</div><!--row end-->



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
			jQuery('#host').on('blur', function() {
			var doma = jQuery(this).val();
			if(/\.(com|net|in|org|co)\/?$/i.test(doma))
			{
				jQuery("#domain_name").val(doma);
				//alert('invalid domain name');
                //return false;
			}
			});
		});

	</script>

