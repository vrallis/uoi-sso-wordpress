# UOI SSO WordPress Plugin

Welcome to the documentation for the **UOI SSO WordPress Plugin**.

This plugin provides a seamless Single Sign-On (SSO) experience for the [University of Ioannina](https://www.uoi.gr) using the CAS protocol.

## Features

*   **CAS Authentication**: Securely authenticate users via `sso.uoi.gr`.
*   **Attribute Mapping**: Map CAS attributes (e.g., `givenName`, `sn`) to WordPress user fields.
*   **Role Mapping**: Automatically assign WordPress roles (e.g., Subscriber, Editor) based on CAS roles (e.g., Student, Teacher).
*   **CSRF Protection**: State tokens prevent cross-site request forgery during the SSO flow.
*   **Ticket Replay Prevention**: Each CAS ticket can only be used once.
*   **Single Logout (SLO)**: Logging out of WordPress also logs the user out of the CAS server.
*   **Redirect Preservation**: The `redirect_to` parameter is preserved through the SSO flow.
*   **Email Conflict Handling**: Prevents account hijacking when a CAS email matches a different existing user.
*   **Input Validation**: All admin settings are sanitized with strict validation (port range, role format, URL format).
*   **Accessibility**: SSO button includes ARIA attributes for screen readers.
*   **Clean Uninstall**: All plugin data (options and transients) is removed on uninstall.
*   **Localization**: Fully localized interface in English and Greek.
*   **Developer Friendly**: Extensible architecture with a provider interface and the `uoi_sso_auth_provider` filter.

## Quick Links

*   [Installation Guide](user-guide/installation.md)
*   [Configuration Guide](user-guide/configuration.md)
*   [Customization Guide](user-guide/customization.md)
*   [Developer Guide](developer-guide/architecture.md)
*   [Hooks & Filters](developer-guide/hooks.md)
