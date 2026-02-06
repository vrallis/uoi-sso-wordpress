# Hooks & Filters

Το πρόσθετο παρέχει hooks και filters για την επέκταση της συμπεριφοράς του.

## Filters (Φίλτρα)

### `uoi_sso_auth_provider`

Φιλτράρισμα του αντικειμένου παρόχου πιστοποίησης που χρησιμοποιεί το πρόσθετο. Αυτό σας επιτρέπει να αντικαταστήσετε τον προεπιλεγμένο πάροχο CAS με μια προσαρμοσμένη υλοποίηση (π.χ. SAML, OAuth).

**Παράμετροι:**

*   `$provider` *(Uoi_Sso_Provider_Interface)* — Η προεπιλεγμένη instance του παρόχου (CAS).

**Επιστρέφει:** Ένα αντικείμενο που υλοποιεί την `Uoi_Sso_Provider_Interface`.

**Παράδειγμα:**
```php
add_filter( 'uoi_sso_auth_provider', function( $provider ) {
    return new My_Custom_Saml_Provider();
} );
```

Ο προσαρμοσμένος πάροχος πρέπει να υλοποιεί τη διεπαφή `Uoi_Sso_Provider_Interface`, η οποία απαιτεί:

*   `authenticate()` — Επιστρέφει `WP_User` σε επιτυχία ή `WP_Error` σε αποτυχία.
*   `get_login_url( $service_url )` — Επιστρέφει το URL σύνδεσης SSO.
*   `get_logout_url( $service_url )` — Επιστρέφει το URL αποσύνδεσης SSO.
*   `is_callback()` — Επιστρέφει `true` εάν το τρέχον αίτημα είναι callback από τον διακομιστή SSO.

## Actions (Ενέργειες)

Τα ακόλουθα actions είναι προγραμματισμένα για μελλοντικές εκδόσεις:

*   `uoi_sso_before_auth`: Εκτελείται πριν ξεκινήσει η διαδικασία πιστοποίησης.
*   `uoi_sso_after_auth`: Εκτελείται αφού ένας χρήστης συνδεθεί επιτυχώς μέσω SSO.

## Hooks WordPress που Χρησιμοποιούνται

Το πρόσθετο χρησιμοποιεί τα ακόλουθα τυπικά WordPress actions:

| Hook | Σκοπός |
|------|--------|
| `login_form` | Εμφανίζει το κουμπί SSO στο `wp-login.php` |
| `init` | Διαχειρίζεται το callback CAS (επικύρωση εισιτηρίου) |
| `wp_logout` | Ανακατευθύνει στο CAS logout για Ενιαία Αποσύνδεση |
| `login_enqueue_scripts` | Φορτώνει τα CSS και JS του προσθέτου στη σελίδα σύνδεσης |
