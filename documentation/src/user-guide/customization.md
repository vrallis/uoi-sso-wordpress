# Customization

## The "Sign in to UOI" Button

By default, the plugin automatically adds the "Sign in to UOI" button to the standard WordPress login form (`/wp-login.php`).

### Custom Placement (Shortcode)

If you are using a custom login page or want to place the button elsewhere (e.g., in a sidebar widget), use the shortcode:

```
[uoi_sso_button]
```

### Styling

The button uses the standard WordPress button classes (`button button-primary button-large`). You can override the styles in your theme's CSS or the Customizer.

The plugin loads its own stylesheet (`public/css/uoi-sso-public.css`) and script (`public/js/uoi-sso-public.js`) on the login page only.

**CSS Selector:**
```css
.uoi-sso-container .button {
    /* Your custom styles here */
    background-color: #0055a5;
}
```

### Accessibility

The SSO button includes ARIA attributes for screen readers:

*   `role="complementary"` on the container
*   `aria-label="Single Sign-On"` on the container
*   `aria-label="Sign in with your University of Ioannina account"` on the link
*   `role="button"` on the link

## Single Logout (SLO)

When a user logs out of WordPress, they are automatically redirected through the CAS server's logout endpoint. This ensures the user's SSO session is terminated across all CAS-enabled applications.

No additional configuration is required — SLO is enabled by default.

## Custom Authentication Provider

The plugin architecture supports swapping the CAS provider with a custom implementation via the `uoi_sso_auth_provider` filter. See the [Hooks & Filters](../developer-guide/hooks.md) page for details.
