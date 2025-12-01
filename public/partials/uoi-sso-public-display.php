<div class="uoi-sso-container" id="uoi-sso-container" style="margin-bottom: 20px; text-align: center; display: none; clear: both; padding-top: 20px;">
	<div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px;">
		<a href="<?php echo esc_url( $login_url ); ?>" class="button button-primary button-large" style="background-color: #0055a5; border-color: #0055a5; width: 100%; padding: 5px 0; font-size: 16px; border-radius: 4px; height: auto; line-height: normal;">
			<?php _e( 'Sign in to UOI', 'uoi-sso' ); ?>
		</a>
	</div>
</div>
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		var container = document.getElementById('uoi-sso-container');
		var loginForm = document.getElementById('loginform');
		
		if (container && loginForm) {
			container.style.display = 'block';
			// Try to find the submit paragraph to insert after
			var submitPara = loginForm.querySelector('p.submit');
			if (submitPara) {
				submitPara.parentNode.insertBefore(container, submitPara.nextSibling);
			} else {
				// Fallback: append to end of form
				loginForm.appendChild(container);
			}
		}
	});
</script>
<style>
	.uoi-sso-container .button:hover {
		background-color: #004484 !important;
		border-color: #004484 !important;
	}
</style>
