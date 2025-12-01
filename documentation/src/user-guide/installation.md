# Installation

## Requirements

*   WordPress 6.0 or higher
*   PHP 8.0 or higher
*   OpenSSL extension enabled (for secure CAS communication)

## Installation Methods

### Option 1: Upload via WordPress Admin

1.  Download the latest release `.zip` file from [GitHub Releases](https://github.com/vrallis/uoi-sso-wordpress/releases).
2.  Log in to your WordPress Admin Dashboard.
3.  Navigate to **Plugins > Add New**.
4.  Click **Upload Plugin** at the top.
5.  Select the `.zip` file and click **Install Now**.
6.  Click **Activate Plugin**.

### Option 2: Manual Installation (FTP/SFTP)

1.  Unzip the plugin archive.
2.  Upload the `uoi-sso` folder to your server's `wp-content/plugins/` directory.
3.  Log in to your WordPress Admin Dashboard.
4.  Navigate to **Plugins**.
5.  Locate **UOI SSO** and click **Activate**.

## Updates

### Option 1: Upload via WordPress Admin (Recommended)

1.  Download the new version `.zip` file.
2.  Navigate to **Plugins > Add New**.
3.  Click **Upload Plugin**.
4.  Select the new `.zip` file and click **Install Now**.
5.  WordPress will detect the existing plugin. Click **Replace current with uploaded**.

### Option 2: Manual Update (FTP/SFTP)

1.  Unzip the new plugin archive.
2.  Connect to your server via FTP/SFTP.
3.  Replace the existing `uoi-sso` folder in `wp-content/plugins/` with the new one.

!!! note "Settings Preservation"
    Your settings are stored in the database and **will be preserved** during updates.
