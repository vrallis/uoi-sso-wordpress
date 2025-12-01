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

**CSS Selector:**
```css
.uoi-sso-container .button {
    /* Your custom styles here */
    background-color: #0055a5;
}
```
