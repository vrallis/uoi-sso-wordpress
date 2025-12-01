<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<div class="card" style="max-width: 100%; margin-top: 20px;">
		<h2><?php _e( 'Plugin Status', 'uoi-sso' ); ?></h2>
		<p>
			<strong><?php _e( 'Version:', 'uoi-sso' ); ?></strong> <?php echo UOI_SSO_VERSION; ?><br>
			<strong><?php _e( 'PHP Version:', 'uoi-sso' ); ?></strong> <?php echo phpversion(); ?><br>
			<strong><?php _e( 'CAS Server:', 'uoi-sso' ); ?></strong> <?php echo esc_html( get_option( 'uoi_sso_cas_url' ) ); ?>
		</p>
	</div>

	<form method="post" action="options.php">
		<?php
			settings_fields( 'uoi-sso' );
			do_settings_sections( 'uoi-sso' );
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'CAS Server URL', 'uoi-sso' ); ?></th>
				<td><input type="text" name="uoi_sso_cas_url" value="<?php echo esc_attr( get_option( 'uoi_sso_cas_url' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'CAS Server Port', 'uoi-sso' ); ?></th>
				<td><input type="number" name="uoi_sso_cas_port" value="<?php echo esc_attr( get_option( 'uoi_sso_cas_port' ) ); ?>" class="small-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'CAS Context', 'uoi-sso' ); ?></th>
				<td><input type="text" name="uoi_sso_cas_context" value="<?php echo esc_attr( get_option( 'uoi_sso_cas_context' ) ); ?>" class="regular-text" /></td>
			</tr>
		</table>

		<hr>

		<h2><?php _e( 'Attribute Mapping', 'uoi-sso' ); ?></h2>
		<p class="description"><?php _e( 'Map CAS attributes to WordPress user fields. Change these if the SSO provider attributes change.', 'uoi-sso' ); ?></p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'First Name Attribute', 'uoi-sso' ); ?></th>
				<td><input type="text" name="uoi_sso_attr_firstname" value="<?php echo esc_attr( get_option( 'uoi_sso_attr_firstname' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Last Name Attribute', 'uoi-sso' ); ?></th>
				<td><input type="text" name="uoi_sso_attr_lastname" value="<?php echo esc_attr( get_option( 'uoi_sso_attr_lastname' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Email Attribute', 'uoi-sso' ); ?></th>
				<td><input type="text" name="uoi_sso_attr_email" value="<?php echo esc_attr( get_option( 'uoi_sso_attr_email' ) ); ?>" class="regular-text" /></td>
			</tr>
		</table>

		<hr>

		<h2><?php _e( 'Role Mapping', 'uoi-sso' ); ?></h2>
		<p class="description"><?php _e( 'Map CAS role values to WordPress roles. Format: <code>cas_value:wp_role</code> (one per line).', 'uoi-sso' ); ?></p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Role Attribute', 'uoi-sso' ); ?></th>
				<td>
					<input type="text" name="uoi_sso_attr_role" value="<?php echo esc_attr( get_option( 'uoi_sso_attr_role' ) ); ?>" class="regular-text" />
					<p class="description"><?php _e( 'The CAS attribute that contains the user role (e.g., eduPersonAffiliation).', 'uoi-sso' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Mapping Rules', 'uoi-sso' ); ?></th>
				<td>
					<textarea name="uoi_sso_role_mapping" rows="5" cols="50" class="large-text code"><?php echo esc_textarea( get_option( 'uoi_sso_role_mapping' ) ); ?></textarea>
					<p class="description"><?php _e( 'Example:', 'uoi-sso' ); ?><br>student:subscriber<br>teacher:editor</p>
				</td>
			</tr>
		</table>
		
		<?php submit_button(); ?>
	</form>
</div>
