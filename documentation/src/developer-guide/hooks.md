# Hooks & Filters

The plugin provides hooks and filters for extending its behavior.

## Filters

### `uoi_sso_auth_provider`

Filter the authentication provider instance used by the plugin. This allows you to replace the default CAS provider with a custom implementation (e.g., SAML, OAuth).

**Parameters:**

*   `$provider` *(Uoi_Sso_Provider_Interface)* — The default provider instance (CAS).

**Return:** An object implementing `Uoi_Sso_Provider_Interface`.

**Example:**
```php
add_filter( 'uoi_sso_auth_provider', function( $provider ) {
    return new My_Custom_Saml_Provider();
} );
```

Your custom provider must implement the `Uoi_Sso_Provider_Interface` interface, which requires:

*   `authenticate()` — Returns a `WP_User` on success or `WP_Error` on failure.
*   `get_login_url( $service_url )` — Returns the SSO login URL.
*   `get_logout_url( $service_url )` — Returns the SSO logout URL.
*   `is_callback()` — Returns `true` if the current request is a callback from the SSO server.

## Actions

The following actions are planned for future releases:

*   `uoi_sso_before_auth`: Fires before the authentication process starts.
*   `uoi_sso_after_auth`: Fires after a user is successfully logged in via SSO.

## WordPress Hooks Used

The plugin hooks into the following standard WordPress actions:

| Hook | Purpose |
|------|---------|
| `login_form` | Renders the SSO button on `wp-login.php` |
| `init` | Handles the CAS callback (ticket validation) |
| `wp_logout` | Redirects to CAS logout for Single Logout |
| `login_enqueue_scripts` | Enqueues the plugin's CSS and JS on the login page |
