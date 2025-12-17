# Social Login Setup Guide

This guide explains how to configure Google, Facebook, and Apple social login for the registration page.

## Overview

The social login functionality allows users to register and log in using their Google, Facebook, or Apple accounts. After successful authentication, users are automatically redirected to the checkout page.

## Setup Instructions

### 1. Google Sign-In Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the "Google+ API" or "Google Identity Services API"
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"
5. Configure the consent screen if prompted
6. Set application type to "Web application"
7. Add authorized JavaScript origins:
   - `http://yourdomain.com` (or `https://yourdomain.com` for production)
   - `http://localhost` (for local development)
8. Add authorized redirect URIs:
   - `http://yourdomain.com/wp-admin/admin-ajax.php` (or `https://yourdomain.com/wp-admin/admin-ajax.php`)
9. Copy the **Client ID**
10. Add it to `wp-config.php`:
    ```php
    define( 'STIRJOY_GOOGLE_CLIENT_ID', 'your-google-client-id.apps.googleusercontent.com' );
    ```

### 2. Facebook Login Setup

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app or select an existing one
3. Add "Facebook Login" product to your app
4. Go to "Settings" → "Basic"
5. Add your site URL to "App Domains"
6. Add "Website" platform and enter your site URL
7. Go to "Facebook Login" → "Settings"
8. Add valid OAuth redirect URIs:
   - `http://yourdomain.com/wp-admin/admin-ajax.php`
   - `https://yourdomain.com/wp-admin/admin-ajax.php` (for production)
9. Copy the **App ID**
10. Add it to `wp-config.php`:
    ```php
    define( 'STIRJOY_FACEBOOK_APP_ID', 'your-facebook-app-id' );
    ```

### 3. Apple Sign-In Setup

1. Go to [Apple Developer Portal](https://developer.apple.com/)
2. Navigate to "Certificates, Identifiers & Profiles"
3. Go to "Identifiers" → "Services IDs"
4. Create a new Service ID or select an existing one
5. Enable "Sign In with Apple"
6. Configure the service:
   - Add your domain and return URLs
   - Add return URL: `http://yourdomain.com/wp-admin/admin-ajax.php?action=stirjoy_apple_callback`
7. Go to "Keys" → Create a new key
8. Enable "Sign In with Apple"
9. Download the key file (you'll need this for server-side verification)
10. Copy the **Service ID** (Client ID)
11. Add it to `wp-config.php`:
    ```php
    define( 'STIRJOY_APPLE_CLIENT_ID', 'your.apple.service.id' );
    ```

## Configuration

Add all three constants to your `wp-config.php` file:

```php
// Social Login Credentials
define( 'STIRJOY_GOOGLE_CLIENT_ID', 'your-google-client-id.apps.googleusercontent.com' );
define( 'STIRJOY_FACEBOOK_APP_ID', 'your-facebook-app-id' );
define( 'STIRJOY_APPLE_CLIENT_ID', 'your.apple.service.id' );
```

## How It Works

1. User clicks a social login button (Google, Facebook, or Apple)
2. The respective SDK opens an authentication popup
3. User authenticates with their social account
4. User data is sent to WordPress via AJAX
5. WordPress creates a new user account (if doesn't exist) or logs in existing user
6. User is automatically redirected to the checkout page

## User Account Creation

When a user logs in via social login:

- **New User**: A WordPress user account is created with:
  - Username: Generated from email or name
  - Email: From social provider (or placeholder if not provided)
  - First Name & Last Name: From social provider profile
  - Role: WooCommerce Customer
  - Password: Auto-generated (user won't need it)

- **Existing User**: If email already exists, user is logged in and social provider info is linked

## Security Notes

- All authentication tokens are verified server-side
- Nonces are used to prevent CSRF attacks
- User data is sanitized before database insertion
- Social provider IDs are stored for account linking

## Troubleshooting

### Buttons Not Working

1. Check browser console for JavaScript errors
2. Verify SDK scripts are loading (check Network tab)
3. Ensure API credentials are correctly set in `wp-config.php`
4. Check that authorized domains/URLs match your site URL

### Authentication Fails

1. Verify API credentials are correct
2. Check that redirect URIs are properly configured
3. Ensure your site URL matches the configured domains
4. Check WordPress debug log for PHP errors

### User Not Redirected to Checkout

1. Verify WooCommerce is active
2. Check that checkout page exists and is published
3. Check browser console for AJAX errors
4. Verify AJAX handler is registered (check Network tab)

## Testing

1. Clear browser cache
2. Go to registration page (`/my-account/?action=register`)
3. Click each social login button
4. Complete authentication flow
5. Verify redirect to checkout page
6. Check that user account was created in WordPress admin

## Support

For issues or questions, check:
- WordPress debug log: `wp-content/debug.log`
- Browser console for JavaScript errors
- Network tab for AJAX request/response details

