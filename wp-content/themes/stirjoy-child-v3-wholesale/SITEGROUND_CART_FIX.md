# SiteGround Cart Transfer Fix - Quick Guide

## Problem
Guest cart doesn't transfer to logged-in user on SiteGround hosting (works fine on local server).

## Solution Implemented

### 1. Enhanced Cart Transfer Function
- Uses WooCommerce's persistent cart mechanism (more reliable on hosting)
- Stores cart in transient as backup (5-minute expiry)
- Saves to user meta for persistence
- Clears all cart-related caches

### 2. Cart Restoration After Login
- Checks transient backup if session cart is empty
- Forces cart recalculation
- Processes persistent cart flag

### 3. SiteGround-Specific Fixes
- Adds no-cache headers for cart/checkout/account pages
- Prevents caching of cart-related AJAX requests
- Tracks login time for cart restoration

## Additional SiteGround Configuration Steps

### Step 1: SiteGround Speed Optimizer Settings

1. Go to **Site Tools → WordPress → Speed Optimizer**
2. Navigate to **Caching → Exclude URLs**
3. Add these URLs to exclude from cache:
   ```
   /cart/
   /checkout/
   /my-account/
   /wc-api/*
   /?wc-ajax=*
   ```

### Step 2: Exclude WooCommerce Cookies

1. Go to **Site Tools → WordPress → Speed Optimizer**
2. Navigate to **Caching → Exclude Cookies**
3. Add these cookies (if option available):
   ```
   woocommerce_items_in_cart
   woocommerce_cart_hash
   wordpress_logged_in_*
   wp_woocommerce_session_*
   ```

### Step 3: Disable File-Based Caching for WooCommerce Pages

1. Go to **Site Tools → WordPress → Speed Optimizer**
2. Navigate to **Caching → File-Based Caching**
3. Ensure WooCommerce pages are excluded (should be automatic, but verify)

### Step 4: Check wp-config.php Settings

Ensure these settings are correct in `wp-config.php`:

```php
// Cookie domain (usually not needed, but check if you have subdomain issues)
// define('COOKIE_DOMAIN', '.yourdomain.com');

// Session settings (if needed)
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Only if using HTTPS
```

### Step 5: Clear All Caches

After making changes:
1. Clear SiteGround cache
2. Clear WordPress cache (if using additional caching plugin)
3. Clear browser cache
4. Test cart transfer

## Testing Steps

1. **Add items to cart as guest**
   - Add 2-3 products to cart
   - Verify cart shows items

2. **Login while cart has items**
   - Go to login page
   - Login with existing account
   - **Expected**: Cart items should transfer to logged-in user

3. **Verify cart persistence**
   - Logout and login again
   - Cart should still have items

4. **Test social login**
   - Add items as guest
   - Use social login (Google/Facebook/Apple)
   - **Expected**: Cart should transfer

## Troubleshooting

### Cart Still Not Transferring

1. **Check if hooks are firing:**
   - Add temporary logging in `stirjoy_transfer_guest_cart_to_user()`
   - Check if function is called on login

2. **Check session data:**
   ```php
   // Add to functions.php temporarily
   add_action('wp_footer', function() {
       if (is_user_logged_in() && WC()->session) {
           echo '<pre>';
           print_r(WC()->session->get('cart'));
           echo '</pre>';
       }
   });
   ```

3. **Check persistent cart:**
   ```php
   // Check user meta
   $user_id = get_current_user_id();
   $persistent_cart = get_user_meta($user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true);
   var_dump($persistent_cart);
   ```

4. **Verify SiteGround cache exclusions:**
   - Check Speed Optimizer settings
   - Ensure cart/checkout pages are excluded
   - Clear all caches

### Common Issues

**Issue**: Cart transfers but disappears on page reload
- **Solution**: Check if persistent cart is being saved correctly
- Verify `_woocommerce_load_saved_cart_after_login` flag is set

**Issue**: Cart works on local but not on SiteGround
- **Solution**: Ensure SiteGround cache exclusions are set
- Check cookie settings in wp-config.php
- Verify session handling is working

**Issue**: Social login cart doesn't transfer
- **Solution**: Check `stirjoy_social_login_handler()` function
- Verify cart is saved before login
- Check if cart is restored after login

## Code Changes Made

1. **Enhanced `stirjoy_transfer_guest_cart_to_user()`**
   - Uses persistent cart mechanism
   - Stores backup in transient
   - Clears caches

2. **Enhanced `stirjoy_ensure_cart_after_login()`**
   - Checks transient backup
   - Restores cart if session is empty
   - Forces cart recalculation

3. **New `stirjoy_siteground_cart_fixes()`**
   - Adds no-cache headers
   - Handles SiteGround-specific caching

4. **New `stirjoy_preserve_cart_on_login_redirect()`**
   - Preserves cart during redirect
   - Restores from persistent storage

5. **New `stirjoy_track_login_time()`**
   - Tracks login time for cart restoration

## Support

If issues persist after following these steps:
1. Check WordPress error logs
2. Check SiteGround error logs
3. Enable WordPress debug mode temporarily
4. Contact SiteGround support if cache exclusions aren't working

