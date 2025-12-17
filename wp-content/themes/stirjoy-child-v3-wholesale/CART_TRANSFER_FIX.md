# Guest Cart Transfer Fix for Hosting Servers

## Problem
Guest cart doesn't transfer to logged-in user on hosting server (works fine on local server).

## Root Causes
1. **Caching Issues**: Speed optimizers may cache AJAX requests or session data
2. **Session Cookie Issues**: Cookies may not be set properly due to domain/path configuration
3. **Timing Issues**: Cart transfer may happen before WooCommerce session is fully initialized
4. **AJAX Caching**: Cart-related AJAX endpoints may be cached

## Solutions Implemented

### 1. Explicit Cart Transfer Hook
Added `stirjoy_transfer_guest_cart_to_user()` hook that:
- Saves guest cart before login
- Ensures session data is persisted
- Preserves cart contents during login

### 2. Cart Persistence After Login
Added `stirjoy_ensure_cart_after_login()` hook that:
- Forces WooCommerce session initialization
- Ensures cart is loaded after login
- Handles cases where session migration doesn't happen immediately

### 3. AJAX Caching Prevention
Added `stirjoy_prevent_cart_ajax_caching()` function that:
- Prevents caching of cart-related AJAX requests
- Sets proper no-cache headers
- Ensures session is started for AJAX requests

### 4. Social Login Cart Transfer
Updated social login handler to:
- Save guest cart before authentication
- Merge guest cart with user cart after login
- Avoid duplicate products
- Update quantities if product already exists

## Speed Optimizer Configuration

### Required Exclusions
Add these URLs/patterns to your speed optimizer's exclude list:

**AJAX Endpoints:**
- `/wp-admin/admin-ajax.php`
- `*admin-ajax.php*`
- `*action=stirjoy_*`
- `*action=woocommerce_*`

**Cart-Related Pages:**
- `/cart/`
- `/checkout/`
- `/my-account/`
- `/register/`

**Session Cookies:**
- `woocommerce_cart_hash`
- `woocommerce_items_in_cart`
- `wp_woocommerce_session_*`

### Additional Settings

1. **Disable AJAX Caching**
   - In your speed optimizer, find "AJAX Caching" or "Dynamic Content"
   - Disable caching for `/wp-admin/admin-ajax.php`
   - Add cart-related actions to exclusion list

2. **Session Cookie Configuration**
   - Ensure cookies are set with proper domain
   - Check cookie path matches your WordPress installation
   - Verify `COOKIE_DOMAIN` in `wp-config.php` if needed

3. **Page Caching Exclusions**
   - Exclude cart, checkout, and my-account pages from page caching
   - Use "Don't Cache" rules for these pages

## Testing

1. **Test Guest Cart Transfer:**
   - Add products to cart as guest
   - Log in or register
   - Verify cart items are still present
   - Check cart count matches

2. **Test Social Login Cart Transfer:**
   - Add products to cart as guest
   - Click social login button (Google/Facebook/Apple)
   - Complete authentication
   - Verify cart items transfer to logged-in user

3. **Test AJAX Cart Operations:**
   - Add/remove products via AJAX
   - Check browser Network tab - requests should not be cached
   - Verify `Cache-Control: no-cache` headers are present

## Troubleshooting

### Cart Still Not Transferring

1. **Check Session Cookies:**
   ```javascript
   // In browser console
   document.cookie
   // Look for woocommerce_cart_hash and wp_woocommerce_session_*
   ```

2. **Check AJAX Headers:**
   - Open Network tab in browser DevTools
   - Look for cart AJAX requests
   - Verify `Cache-Control: no-cache` header is present
   - Check response is not cached (Status 200, not 304)

3. **Check Server Logs:**
   - Look for session-related errors
   - Check if cookies are being set properly
   - Verify session storage is working

4. **Test Without Speed Optimizer:**
   - Temporarily disable speed optimizer
   - Test cart transfer
   - If it works, adjust optimizer settings

### Common Issues

**Issue**: Cart disappears after login
- **Solution**: Ensure `stirjoy_transfer_guest_cart_to_user` hook is firing
- Check that WooCommerce session is initialized before cart operations

**Issue**: AJAX requests return cached data
- **Solution**: Verify AJAX endpoints are excluded from caching
- Check that `nocache_headers()` is being called

**Issue**: Session cookies not set
- **Solution**: Check `COOKIE_DOMAIN` in `wp-config.php`
- Verify cookie path matches WordPress installation path
- Check server PHP session configuration

## WordPress Configuration

### wp-config.php Settings

```php
// Ensure cookies work properly
define( 'COOKIE_DOMAIN', '' ); // Leave empty for auto-detect, or set your domain
define( 'COOKIEPATH', '/' );
define( 'SITECOOKIEPATH', '/' );

// Session configuration (if using custom session handler)
ini_set( 'session.cookie_httponly', 1 );
ini_set( 'session.cookie_secure', 0 ); // Set to 1 if using HTTPS
```

## Server Configuration

### PHP Settings
- Ensure `session.save_path` is writable
- Check `session.gc_maxlifetime` is sufficient (default 1440 seconds)
- Verify session cookies are enabled

### .htaccess (if applicable)
```apache
# Prevent caching of AJAX requests
<FilesMatch "admin-ajax\.php">
    Header set Cache-Control "no-cache, no-store, must-revalidate"
    Header set Pragma "no-cache"
    Header set Expires 0
</FilesMatch>
```

## Additional Notes

- Cart transfer relies on WooCommerce's built-in session migration
- The hooks ensure cart persists even with aggressive caching
- Social login cart transfer is handled separately to ensure compatibility
- All cart operations use AJAX to avoid page caching issues

## Support

If issues persist:
1. Check WordPress debug log: `wp-content/debug.log`
2. Enable WordPress debug mode: `define( 'WP_DEBUG', true );`
3. Check browser console for JavaScript errors
4. Verify WooCommerce session handler is working: `WC()->session->has_session()`

