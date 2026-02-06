( function() {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function() {
		var container = document.getElementById( 'uoi-sso-container' );
		var loginForm = document.getElementById( 'loginform' );

		if ( container && loginForm ) {
			container.style.display = 'block';
			// Insert after the submit button
			var submitPara = loginForm.querySelector( 'p.submit' );
			if ( submitPara ) {
				submitPara.parentNode.insertBefore( container, submitPara.nextSibling );
			} else {
				loginForm.appendChild( container );
			}
		}
	} );
} )();
