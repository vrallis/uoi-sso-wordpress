# Architecture

The UOI SSO plugin follows a modular architecture to separate concerns and allow for future extensibility (e.g., adding SAML support).

## Directory Structure

*   `admin/`: Code specific to the WordPress Admin area (Settings page).
*   `public/`: Code for the frontend (Login button, Shortcode).
    *   `css/uoi-sso-public.css`: Stylesheet for the SSO button.
    *   `js/uoi-sso-public.js`: Script for button positioning on the login form.
*   `includes/`: Core logic and classes.
    *   `interface-uoi-sso-provider.php`: Interface for Auth Providers.
    *   `class-uoi-sso-cas-provider.php`: Implementation of the CAS protocol.
*   `languages/`: Translation files (`.pot`, `.po`, `.mo`).
*   `uninstall.php`: Cleanup handler — removes all plugin options and transients on uninstall.

## Authentication Flow

1.  **User Clicks Button**: The plugin generates a CSRF state token, stores it in a WordPress transient along with the `redirect_to` URL, and redirects the user to `sso.uoi.gr/cas/login` with `service` and `uoi_sso_state` parameters.
2.  **CAS Login**: User logs in at the university portal.
3.  **Redirect Back**: CAS redirects user back to WordPress with a `ticket` parameter.
4.  **Validation**:
    *   The plugin verifies the CSRF state token. If invalid or expired (10-minute window), the request is rejected.
    *   It checks the ticket against a replay cache (SHA-256 hashed, stored in a transient for 5 minutes). Replayed tickets are rejected.
    *   It sends a server-side HTTPS request to `sso.uoi.gr/cas/serviceValidate` to verify the ticket, enforcing SSL and checking the HTTP response status.
    *   The XML response is parsed securely using `DOMDocument` with XXE protection (`LIBXML_NONET | LIBXML_NOENT`).
5.  **User Creation/Login**:
    *   If valid, the plugin extracts the username and attributes from the CAS response.
    *   It searches for an existing user by username. If not found, it checks by email — but rejects the login if the email belongs to a different existing account (prevents account hijacking).
    *   It creates a new user or updates the existing one with CAS attributes (name, email, role).
    *   It logs the user in and redirects them to the original `redirect_to` destination (or the home page as fallback).

## Single Logout (SLO)

When a user logs out of WordPress, the plugin redirects them through the CAS server's logout endpoint, ensuring they are also signed out of the university SSO session.

## Security Features

*   **CSRF State Tokens**: Single-use tokens prevent cross-site request forgery during the OAuth-like redirect flow.
*   **Ticket Replay Prevention**: SHA-256 hashed tickets are cached to prevent reuse.
*   **SSL Enforcement**: All CAS server communication uses HTTPS with SSL verification.
*   **XXE Prevention**: XML parsing uses `LIBXML_NONET | LIBXML_NOENT` flags.
*   **Email Conflict Protection**: Prevents account takeover when a CAS email matches a different WordPress user.
*   **Input Sanitization**: All admin settings are validated and sanitized (URL format, port range 1–65535, role mapping format).
*   **Output Escaping**: All user-facing output uses `esc_html()`, `esc_attr()`, `esc_url()` etc.
*   **Capability Checks**: Admin pages verify `manage_options` capability.
*   **Generic Error Messages**: Authentication errors shown to users are generic; real details are logged server-side when `WP_DEBUG` is enabled.
