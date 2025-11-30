# Hardcoded Values Report

**Project:** Stirjoy WordPress E-Commerce Site  
**Theme:** stirjoy-child-v3-wholesale  
**Report Date:** 2025-01-XX  
**Purpose:** Identify all hardcoded values that should be made configurable

---

## Executive Summary

This report identifies **all hardcoded values** in the child theme that should ideally be:
- Moved to WordPress options/settings
- Made configurable via admin panel
- Stored as constants
- Retrieved from database/API

**Total Hardcoded Values Found:** 50+ instances across multiple categories

---

## Table of Contents

1. [Critical Security Issues](#critical-security-issues)
2. [Business Logic Values](#business-logic-values)
3. [UI/Design Values](#uidesign-values)
4. [Date/Time Values](#datetime-values)
5. [Product/Page IDs](#productpage-ids)
6. [API Endpoints](#api-endpoints)
7. [Text/Strings](#textstrings)
8. [Recommendations](#recommendations)

---

## 1. Critical Security Issues

### ⚠️ **CRITICAL: API Consumer Secret in JavaScript**

**Location:** `assets/js/stirjoy.js` (Line 209)

```javascript
consumer_secret: 'wps_a50a76fd27b5076c5807b7469594a721a2d00dc7'
```

**Issue:**
- API secret is exposed in client-side JavaScript
- Visible to anyone viewing page source
- Security vulnerability - secrets should never be in frontend code

**Impact:** 🔴 **CRITICAL** - Security risk

**Recommendation:**
```php
// In functions.php - Pass via wp_localize_script
wp_localize_script('stirjoy-child-script', 'stirjoyData', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('stirjoy_nonce'),
    'consumerSecret' => get_option('stirjoy_consumer_secret', ''), // Store in options
));
```

**Priority:** 🔴 **IMMEDIATE FIX REQUIRED**

---

## 2. Business Logic Values

### 2.1 Free Shipping Threshold

**Location:** `functions.php` (Line 219)

```php
$freeShippingThreshold = 80;
```

**Issue:**
- Hardcoded dollar amount ($80)
- Cannot be changed without code modification
- Should be configurable in admin

**Current Usage:**
- Used in progress bar calculation
- Displayed in mini cart
- Referenced in JavaScript

**Recommendation:**
```php
$freeShippingThreshold = get_option('stirjoy_free_shipping_threshold', 80);
// Or use WooCommerce shipping settings
```

**Priority:** 🟡 **HIGH** - Business requirement may change

---

### 2.2 Free Gift Threshold

**Location:** `functions.php` (Line 240)

```php
$freeGiftThreshold = 120;
```

**Issue:**
- Hardcoded dollar amount ($120)
- Same concerns as free shipping threshold

**Recommendation:**
```php
$freeGiftThreshold = get_option('stirjoy_free_gift_threshold', 120);
```

**Priority:** 🟡 **HIGH** - Business requirement may change

---

### 2.3 Default Shipping Cost

**Location:** `woocommerce/cart/mini-cart.php` (Lines 117, 125)

```php
<span class="price" data-price="15">$15.00</span>
```

**Issue:**
- Hardcoded $15.00 shipping cost
- Used as fallback when cart is empty
- Should use WooCommerce shipping settings

**Recommendation:**
```php
$default_shipping = get_option('woocommerce_default_shipping_cost', 15);
// Or calculate from WooCommerce shipping zones
```

**Priority:** 🟡 **HIGH** - Pricing accuracy

---

### 2.4 Delivery Days Offset

**Location:** `woocommerce/myaccount/dashboard.php` (Lines 115, 121, 122, 142)

```php
$first_delivery_date = date('M Y', strtotime('+5 days', $first_schedule_start));
$next_delivery_date = date('M d', strtotime('+5 days', $wps_schedule_start));
$delivery_date = date('F d, Y', strtotime('+5 days', $timestamp));
```

**Issue:**
- Hardcoded "+5 days" for delivery calculation
- Business logic should be configurable
- May need to change based on shipping zones

**Recommendation:**
```php
$delivery_days = get_option('stirjoy_delivery_days_offset', 5);
$delivery_date = date('F d, Y', strtotime("+{$delivery_days} days", $timestamp));
```

**Priority:** 🟡 **MEDIUM** - Business logic flexibility

---

## 3. UI/Design Values

### 3.1 Brand Colors

**Location:** `style.css` (Lines 25-33)

```css
:root {
    --stirjoy-primary: #4CAF50;
    --stirjoy-secondary: #FF9800;
    --stirjoy-accent: #2196F3;
    --stirjoy-text: #333333;
    --stirjoy-light-bg: #F5F5F5;
    --stirjoy-border: #E0E0E0;
    --stirjoy-success: #4CAF50;
    --stirjoy-warning: #FFC107;
    --stirjoy-error: #F44336;
}
```

**Issue:**
- Colors hardcoded in CSS
- Cannot be changed without editing theme files
- Should use WordPress Customizer

**Recommendation:**
- Use WordPress Customizer API
- Store colors as theme mods
- Generate CSS dynamically

**Priority:** 🟢 **LOW** - Design preference, but good UX improvement

---

### 3.2 Status Colors

**Location:** `woocommerce/myaccount/dashboard.php` (Lines 160-179)

```php
switch ($wps_status) {
    case 'active':
        $bgColor = '#2d5a27';
        break;
    case 'on-hold':
        $bgColor = '#e67e22';
        break;
    case 'cancelled':
        $bgColor = '#c10007';
        break;
    // ... more cases
}
```

**Location:** `assets/js/stirjoy.js` (Lines 220, 228, 236)

```javascript
$(".subscription-status .icon-block span").css('background', '#e67e22');
$(".subscription-status .icon-block span").css('background', '#2d5a27');
$(".subscription-status .icon-block span").css('background', '#c10007');
```

**Issue:**
- Status colors hardcoded in both PHP and JavaScript
- Duplicated logic
- Should be centralized

**Recommendation:**
```php
// In functions.php
function stirjoy_get_status_color($status) {
    $colors = array(
        'active' => '#2d5a27',
        'on-hold' => '#e67e22',
        'cancelled' => '#c10007',
        // ...
    );
    return get_option("stirjoy_status_color_{$status}", $colors[$status] ?? '#666');
}
```

**Priority:** 🟢 **LOW** - Code organization

---

## 4. Date/Time Values

### 4.1 Cutoff Time

**Location:** `woocommerce/cart/mini-cart.php` (Line 133)

```php
<span>Cutoff: Tonight at 11:59 PM</span>
Your box will be charged automatically at midnight
```

**Issue:**
- Hardcoded cutoff time text
- Static message doesn't reflect actual cutoff
- Should calculate dynamically

**Recommendation:**
```php
$cutoff_time = get_option('stirjoy_cutoff_time', '23:59');
$cutoff_timestamp = strtotime("today {$cutoff_time}");
$cutoff_display = date('g:i A', $cutoff_timestamp);
echo "<span>Cutoff: Tonight at {$cutoff_display}</span>";
```

**Priority:** 🟡 **MEDIUM** - User experience

---

### 4.2 Delivery Schedule Text

**Location:** `woocommerce/myaccount/dashboard.php` (Line 269)

```php
<h5>Monthly (15th of each month)</h5>
```

**Issue:**
- Hardcoded "15th of each month"
- Should be calculated from subscription data
- May vary per subscription

**Recommendation:**
```php
$delivery_day = get_option('stirjoy_default_delivery_day', 15);
// Or get from subscription meta
$subscription_delivery_day = wps_sfw_get_meta_data($last_subscription_id, 'delivery_day', true);
$delivery_day = $subscription_delivery_day ?: $delivery_day;
echo "<h5>Monthly ({$delivery_day}th of each month)</h5>";
```

**Priority:** 🟡 **MEDIUM** - Accuracy

---

## 5. Product/Page IDs

### 5.1 Specific Post ID

**Location:** `style.css` (Lines 836-843)

```css
.postid-8480 .site-main,
.postid-8480 .site-main .summary {
    width: 100% !important;
}
.postid-8480 .site-main .summary > *:not(#wps_sfw_subs_box-popup),
.postid-8480 .site-main .woocommerce-product-gallery,
.postid-8480 .header-title-breadcrumb,
.postid-8480 .woocommerce-tabs,
```

**Location:** `assets/js/stirjoy.js` (Line 319)

```javascript
$(".postid-8480 .fixed-sidebar-menu.fixed-sidebar-menu-minicart").addClass('open');
```

**Location:** `assets/js/subscriptions-for-woocommerce-public.js` (Lines 138, 183)

```javascript
$(".postid-8480 .fixed-sidebar-menu.fixed-sidebar-menu-minicart").addClass('open');
```

**Issue:**
- Hardcoded post ID (8480)
- Specific to one product/page
- Will break if post is deleted or ID changes
- Should use post slug or meta field

**Recommendation:**
```php
// Use post slug or custom meta
$subscription_box_page = get_option('stirjoy_subscription_box_page_id', 8480);
// Or use slug
$subscription_box_page = get_page_by_path('subscription-box');
if ($subscription_box_page) {
    $page_id = $subscription_box_page->ID;
}
```

**Priority:** 🟡 **MEDIUM** - Maintenance issue

---

### 5.2 Hardcoded Order Number

**Location:** `woocommerce/myaccount/dashboard.php` (Line 238)

```php
<span>Order #1001</span>
```

**Issue:**
- Example/dummy order number
- Should use actual order data
- Appears to be placeholder code

**Recommendation:**
```php
// Use actual order number from database
$order_number = $past_order->get_order_number();
echo "<span>Order #{$order_number}</span>";
```

**Priority:** 🟢 **LOW** - Appears to be incomplete feature

---

## 6. API Endpoints

### 6.1 REST API Endpoint

**Location:** `assets/js/stirjoy.js` (Line 205)

```javascript
url: '/wp-json/wsp-route/v1/wsp-update-subscription/' + subscription_id,
```

**Issue:**
- Hardcoded API endpoint path
- Should be configurable
- May change with plugin updates

**Recommendation:**
```php
// Pass via wp_localize_script
wp_localize_script('stirjoy-child-script', 'stirjoyData', array(
    'subscriptionApiUrl' => rest_url('wsp-route/v1/wsp-update-subscription/'),
));
```

**Priority:** 🟢 **LOW** - Unlikely to change, but good practice

---

### 6.2 WooCommerce REST API

**Location:** `assets/js/stirjoy.js` (Line 93)

```javascript
fetch('/wp-json/wc/store/v1/cart')
```

**Issue:**
- Hardcoded WooCommerce REST API endpoint
- Should use localized URL

**Recommendation:**
```php
wp_localize_script('stirjoy-child-script', 'stirjoyData', array(
    'wcCartApiUrl' => rest_url('wc/store/v1/cart'),
));
```

**Priority:** 🟢 **LOW** - Standard endpoint, unlikely to change

---

## 7. Text/Strings

### 7.1 Subscription Plan Names

**Location:** `woocommerce/myaccount/dashboard.php` (Lines 259-260)

```php
<h3>Monthly Plan</h3>
<div class="subscription-name">6 meals per month</div>
```

**Issue:**
- Hardcoded plan name and description
- Should be retrieved from subscription/product data
- May vary per customer

**Recommendation:**
```php
// Get from subscription meta or product
$plan_name = wps_sfw_get_meta_data($last_subscription_id, 'plan_name', true) ?: 'Monthly Plan';
$meals_per_month = wps_sfw_get_meta_data($last_subscription_id, 'meals_per_month', true) ?: 6;
echo "<h3>{$plan_name}</h3>";
echo "<div class='subscription-name'>{$meals_per_month} meals per month</div>";
```

**Priority:** 🟡 **MEDIUM** - Data accuracy

---

### 7.2 Button Text

**Location:** `woocommerce/cart/mini-cart.php` (Line 143)

```php
Confirm My Box
```

**Location:** `woocommerce/cart/mini-cart.php` (Line 145)

```php
Click to confirm your box selection before the cutoff time
```

**Issue:**
- Hardcoded button and description text
- Should be translatable
- Should use WordPress i18n functions

**Recommendation:**
```php
_e('Confirm My Box', 'stirjoy-child');
_e('Click to confirm your box selection before the cutoff time', 'stirjoy-child');
```

**Priority:** 🟢 **LOW** - Internationalization

---

### 7.3 Alert Messages

**Location:** `assets/js/stirjoy.js` (Lines 224, 232, 240)

```javascript
alert('Current subscription was paused successfully.');
alert('Current subscription was reactivated successfully.');
alert('Current subscription was cancelled successfully.');
```

**Issue:**
- Hardcoded alert messages
- Not translatable
- Should use WordPress localization

**Recommendation:**
```php
// In functions.php
wp_localize_script('stirjoy-child-script', 'stirjoyMessages', array(
    'subscriptionPaused' => __('Current subscription was paused successfully.', 'stirjoy-child'),
    'subscriptionReactivated' => __('Current subscription was reactivated successfully.', 'stirjoy-child'),
    'subscriptionCancelled' => __('Current subscription was cancelled successfully.', 'stirjoy-child'),
));
```

**Priority:** 🟢 **LOW** - Internationalization

---

## 8. Layout/Spacing Values

### 8.1 Container Widths

**Location:** `style.css` (Line 1389)

```css
@media (min-width: 1280px) {
    .container {
        max-width: 1280px;
    }
}
```

**Location:** `template-wholesale-portal.php` (Line 225)

```css
.container-wide {
    max-width: 1200px;
}
```

**Issue:**
- Hardcoded container widths
- Should be configurable via Customizer
- May need different widths for different pages

**Priority:** 🟢 **LOW** - Design preference

---

### 8.2 Responsive Breakpoints

**Location:** `style.css` (Multiple locations)

```css
@media (max-width: 1024px) { ... }
@media (max-width: 767px) { ... }
@media (max-width: 480px) { ... }
```

**Issue:**
- Standard breakpoints, but hardcoded
- Could be made configurable

**Priority:** 🟢 **VERY LOW** - Standard practice

---

## 9. Summary Table

| Category | Count | Priority | Impact |
|----------|-------|----------|--------|
| **Security Issues** | 1 | 🔴 CRITICAL | High |
| **Business Logic** | 4 | 🟡 HIGH | High |
| **UI/Design** | 15+ | 🟢 LOW | Medium |
| **Date/Time** | 3 | 🟡 MEDIUM | Medium |
| **Product/Page IDs** | 2 | 🟡 MEDIUM | Medium |
| **API Endpoints** | 2 | 🟢 LOW | Low |
| **Text/Strings** | 5+ | 🟢 LOW | Low |
| **Layout/Spacing** | 10+ | 🟢 LOW | Low |

**Total:** 42+ hardcoded values identified

---

## 10. Recommendations by Priority

### 🔴 **IMMEDIATE (Security)**

1. **Move API Consumer Secret to PHP**
   - Remove from JavaScript
   - Store in WordPress options
   - Pass via `wp_localize_script`
   - **File:** `assets/js/stirjoy.js:209`

### 🟡 **HIGH PRIORITY (Business Logic)**

2. **Make Thresholds Configurable**
   - Free shipping threshold ($80)
   - Free gift threshold ($120)
   - Add admin settings page
   - **Files:** `functions.php:219, 240`

3. **Make Shipping Cost Dynamic**
   - Remove hardcoded $15.00
   - Use WooCommerce shipping settings
   - **File:** `woocommerce/cart/mini-cart.php:117, 125`

4. **Make Delivery Days Configurable**
   - Remove hardcoded "+5 days"
   - Add to admin settings
   - **File:** `woocommerce/myaccount/dashboard.php:115, 121, 122, 142`

### 🟡 **MEDIUM PRIORITY (Maintenance)**

5. **Replace Post ID with Dynamic Reference**
   - Remove hardcoded post ID 8480
   - Use post slug or option
   - **Files:** `style.css:836`, `assets/js/stirjoy.js:319`, `subscriptions-for-woocommerce-public.js:138, 183`

6. **Make Cutoff Time Dynamic**
   - Calculate from settings
   - Display actual cutoff time
   - **File:** `woocommerce/cart/mini-cart.php:133`

7. **Retrieve Subscription Plan Data**
   - Get from subscription meta
   - Remove hardcoded "Monthly Plan" and "6 meals"
   - **File:** `woocommerce/myaccount/dashboard.php:259-260`

### 🟢 **LOW PRIORITY (Nice to Have)**

8. **Add WordPress Customizer for Colors**
   - Make brand colors configurable
   - **File:** `style.css:25-33`

9. **Add Translation Support**
   - Use `__()` and `_e()` functions
   - Make all strings translatable
   - Multiple files

10. **Centralize Status Colors**
    - Create function for status colors
    - Remove duplication
    - **Files:** `dashboard.php`, `stirjoy.js`

---

## 11. Implementation Example

### Creating Admin Settings Page

```php
// Add to functions.php

/**
 * Register Stirjoy Settings
 */
function stirjoy_register_settings() {
    // Free Shipping Threshold
    register_setting('stirjoy_settings', 'stirjoy_free_shipping_threshold', array(
        'type' => 'number',
        'default' => 80,
        'sanitize_callback' => 'absint',
    ));
    
    // Free Gift Threshold
    register_setting('stirjoy_settings', 'stirjoy_free_gift_threshold', array(
        'type' => 'number',
        'default' => 120,
        'sanitize_callback' => 'absint',
    ));
    
    // Delivery Days Offset
    register_setting('stirjoy_settings', 'stirjoy_delivery_days_offset', array(
        'type' => 'number',
        'default' => 5,
        'sanitize_callback' => 'absint',
    ));
    
    // Cutoff Time
    register_setting('stirjoy_settings', 'stirjoy_cutoff_time', array(
        'type' => 'string',
        'default' => '23:59',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    // Consumer Secret (for API)
    register_setting('stirjoy_settings', 'stirjoy_consumer_secret', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ));
}
add_action('admin_init', 'stirjoy_register_settings');

/**
 * Add Settings Page
 */
function stirjoy_add_settings_page() {
    add_options_page(
        'Stirjoy Settings',
        'Stirjoy Settings',
        'manage_options',
        'stirjoy-settings',
        'stirjoy_settings_page'
    );
}
add_action('admin_menu', 'stirjoy_add_settings_page');

/**
 * Settings Page HTML
 */
function stirjoy_settings_page() {
    ?>
    <div class="wrap">
        <h1>Stirjoy Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('stirjoy_settings'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="stirjoy_free_shipping_threshold">Free Shipping Threshold ($)</label></th>
                    <td><input type="number" name="stirjoy_free_shipping_threshold" value="<?php echo esc_attr(get_option('stirjoy_free_shipping_threshold', 80)); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="stirjoy_free_gift_threshold">Free Gift Threshold ($)</label></th>
                    <td><input type="number" name="stirjoy_free_gift_threshold" value="<?php echo esc_attr(get_option('stirjoy_free_gift_threshold', 120)); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="stirjoy_delivery_days_offset">Delivery Days Offset</label></th>
                    <td><input type="number" name="stirjoy_delivery_days_offset" value="<?php echo esc_attr(get_option('stirjoy_delivery_days_offset', 5)); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="stirjoy_cutoff_time">Cutoff Time (HH:MM)</label></th>
                    <td><input type="time" name="stirjoy_cutoff_time" value="<?php echo esc_attr(get_option('stirjoy_cutoff_time', '23:59')); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="stirjoy_consumer_secret">API Consumer Secret</label></th>
                    <td><input type="password" name="stirjoy_consumer_secret" value="<?php echo esc_attr(get_option('stirjoy_consumer_secret', '')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
```

### Updating JavaScript to Use Localized Data

```php
// In functions.php - Update wp_localize_script
wp_localize_script('stirjoy-child-script', 'stirjoyData', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('stirjoy_nonce'),
    'consumerSecret' => get_option('stirjoy_consumer_secret', ''),
    'freeShippingThreshold' => get_option('stirjoy_free_shipping_threshold', 80),
    'freeGiftThreshold' => get_option('stirjoy_free_gift_threshold', 120),
    'subscriptionApiUrl' => rest_url('wsp-route/v1/wsp-update-subscription/'),
    'wcCartApiUrl' => rest_url('wc/store/v1/cart'),
));
```

### Updating PHP Functions

```php
// In functions.php - Update free shipping function
function add_free_shipping_bar_to_mini_cart() {
    $freeShippingThreshold = get_option('stirjoy_free_shipping_threshold', 80);
    // ... rest of function
}

// In dashboard.php - Update delivery calculation
$delivery_days = get_option('stirjoy_delivery_days_offset', 5);
$delivery_date = date('F d, Y', strtotime("+{$delivery_days} days", $timestamp));
```

---

## 12. Testing Checklist

After implementing fixes:

- [ ] API secret no longer visible in page source
- [ ] Thresholds can be changed in admin without code modification
- [ ] Shipping cost uses WooCommerce settings
- [ ] Delivery dates calculate correctly with configurable offset
- [ ] Post ID references work with different IDs
- [ ] Cutoff time displays correctly
- [ ] Subscription plan data comes from database
- [ ] All strings are translatable
- [ ] Settings page saves and loads correctly

---

## Conclusion

The codebase contains **42+ hardcoded values** across multiple categories. While many are low-priority design choices, there are **critical security issues** and **important business logic values** that should be made configurable.

**Immediate Action Required:**
1. Move API consumer secret to secure storage
2. Make business thresholds configurable
3. Replace hardcoded shipping costs

**Recommended Timeline:**
- **Week 1:** Fix critical security issue
- **Week 2:** Make business logic configurable
- **Week 3:** Add admin settings page
- **Week 4:** Refactor remaining hardcoded values

---

*End of Report*

