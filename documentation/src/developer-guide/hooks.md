# Hooks & Filters

Currently, the plugin uses standard WordPress hooks. Future versions may include custom hooks for deeper integration.

## Actions

*   `uoi_sso_before_auth`: (Planned) Fires before the authentication process starts.
*   `uoi_sso_after_auth`: (Planned) Fires after a user is successfully logged in via SSO.

## Filters

*   `uoi_sso_cas_args`: (Planned) Filter the arguments sent to the CAS server.

*Note: As of version 1.0.3, the plugin primarily relies on internal logic. If you need specific hooks, please submit a feature request.*
