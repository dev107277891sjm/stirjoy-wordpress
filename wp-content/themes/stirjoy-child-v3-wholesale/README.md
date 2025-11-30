# Stirjoy Child Theme

A child theme for TheCrate, customized for Stirjoy meal subscription service.

## Requirements

- WordPress 5.8+
- WooCommerce 6.0+
- TheCrate parent theme (must be installed)
- PHP 7.4+

## Installation

1. **Install TheCrate parent theme first**
   - Upload and activate TheCrate theme
   - Do NOT delete TheCrate - it's required!

2. **Install Stirjoy Child Theme**
   - Go to WordPress Admin → Appearance → Themes → Add New
   - Click "Upload Theme"
   - Choose `stirjoy-child.zip`
   - Click "Install Now"
   - Click "Activate"

3. **Verify Installation**
   - Go to Appearance → Themes
   - You should see "Stirjoy Child" as active
   - Parent theme should show as "TheCrate"

## Features

### ✅ Included

- Custom product meta fields (prep time, cook time, calories, protein)
- Cart confirmation system
- Custom styling matching Stirjoy wireframes
- Responsive design
- AJAX functionality

### 🔄 To Be Implemented by Your Developer

- Complete cart sidebar with live updates
- Subscription management
- Account dashboard
- Wholesale portal
- Bilingual support (WPML/Polylang)
- Email automation
- Payment gateway integration

## Configuration

### Add Product Meta

When adding/editing products:

1. Go to Products → Add New (or edit existing)
2. Scroll to "Product Data" section
3. Under "General" tab, you'll see:
   - Prep Time (min)
   - Cook Time (min)
   - Calories
   - Protein (g)
4. Fill in these fields
5. Publish/Update

These will display on product cards automatically.

### Cart Confirmation

The cart confirmation button appears automatically on shop pages. When customers click "Confirm My Box":
- Their selection is locked
- A success message appears
- They can click "Modify Selection" to make changes

## File Structure

```
stirjoy-child/
├── style.css                 # Main stylesheet (inherits from TheCrate)
├── functions.php             # Theme setup and hooks
├── screenshot.png            # Theme thumbnail
├── README.md                 # This file
├── assets/
│   └── js/
│       └── stirjoy.js       # Custom JavaScript
└── inc/
    ├── cart-confirmation.php # Cart confirmation functionality
    └── product-meta.php      # Custom product fields
```

## Customization

### Colors

Edit `style.css` and change the CSS variables:

```css
:root {
    --stirjoy-primary: #4CAF50;      /* Main brand color */
    --stirjoy-secondary: #FF9800;    /* Secondary color */
    --stirjoy-accent: #2196F3;       /* Accent color */
}
```

### Layout

The child theme inherits TheCrate's layout. To customize:

1. Copy template files from TheCrate parent theme
2. Paste into child theme (same directory structure)
3. Modify the copied files

Example:
- Parent: `/themes/thecrate/header.php`
- Child: `/themes/stirjoy-child/header.php`

## Support

For theme issues:
- Check that TheCrate parent theme is installed and activated
- Ensure WooCommerce is active
- Clear WordPress cache
- Check PHP error logs

## Changelog

### Version 1.0.0
- Initial release
- Basic styling and layout
- Product meta fields
- Cart confirmation system
- AJAX functionality

## Credits

- Parent Theme: TheCrate by ThemesLR
- Child Theme: Stirjoy Team
- Based on wireframes: https://stirjoy-wireframes.netlify.app/

## License

GPL v2 or later

