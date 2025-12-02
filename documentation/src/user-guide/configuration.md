# Configuration

After activating the plugin, navigate to **Settings > UOI SSO** to configure the plugin.

## CAS Settings

These settings connect your WordPress site to the University's CAS server.

*   **CAS Server URL**: The hostname of the CAS server. Default: `sso.uoi.gr`.
*   **CAS Server Port**: The port number. Default: `443` (HTTPS).
*   **CAS Context**: The URI path to the CAS application. Default: `/cas`.

## Attribute Mapping

Map data from the CAS response to WordPress user profile fields.

*   **First Name Attribute**: The CAS attribute for the user's first name. Default: `givenName`.
*   **Last Name Attribute**: The CAS attribute for the user's last name. Default: `sn`.
*   **Email Attribute**: The CAS attribute for the user's email address. Default: `mail`.

!!! tip "Finding Attributes"
    If you are unsure of the attribute names, ask your CAS administrator for a list of released attributes or check your own profile data at [sso.uoi.gr](https://sso.uoi.gr).

## Role Mapping

Control what role users are assigned when they log in.

*   **Role Attribute**: The CAS attribute that defines the user's role or affiliation. Default: `eduPersonAffiliation`.
*   **Mapping Rules**: Define how CAS values map to WordPress roles.
    *   Format: `cas_value:wordpress_role`
    *   One rule per line.

**Example:**
```text
student:subscriber
teacher:editor
staff:author
```

*   **Logic**: The plugin checks if the user's `Role Attribute` contains the `cas_value`. If it matches, the user is assigned the corresponding `wordpress_role`.
*   **Default**: If no match is found, the user is assigned the `subscriber` role.

## Localization

The plugin automatically detects the language of your WordPress site (configured in **Settings > General > Site Language**).

*   **Supported Languages**: English (default), Greek.
*   **Fallback**: If your site uses a language that is not supported, the plugin will display in **English**.
