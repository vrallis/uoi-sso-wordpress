# Architecture

The UOI SSO plugin follows a modular architecture to separate concerns and allow for future extensibility (e.g., adding SAML support).

## Directory Structure

*   `admin/`: Code specific to the WordPress Admin area (Settings page).
*   `public/`: Code for the frontend (Login button, Shortcode).
*   `includes/`: Core logic and classes.
    *   `interface-uoi-sso-provider.php`: Interface for Auth Providers.
    *   `class-uoi-sso-cas-provider.php`: Implementation of the CAS protocol.
*   `languages/`: Translation files (`.pot`, `.po`, `.mo`).

## Authentication Flow

1.  **User Clicks Button**: Redirects to `sso.uoi.gr/cas/login` with a `service` parameter pointing back to the current page.
2.  **CAS Login**: User logs in at the university portal.
3.  **Redirect Back**: CAS redirects user back to WordPress with a `ticket` parameter.
4.  **Validation**:
    *   The plugin detects the `ticket` parameter.
    *   It sends a background request to `sso.uoi.gr/cas/serviceValidate` to verify the ticket.
5.  **User Creation/Login**:
    *   If valid, the plugin parses the XML response for attributes.
    *   It finds an existing user by username/email OR creates a new one.
    *   It logs the user in and redirects them to the home page.
