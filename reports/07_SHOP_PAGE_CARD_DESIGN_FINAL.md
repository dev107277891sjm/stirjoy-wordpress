# Shop Page - Final Card Design Matching Wireframe

**Date:** December 3, 2025  
**Project:** Stirjoy WordPress Site  
**Task:** Match Product Card Design to Wireframe Exactly

---

## Overview

Updated product cards to exactly match the wireframe design specifications, with focus on proper styling of "View Details" and "+ Add" buttons, gradient backgrounds, light green tags, and overall card proportions.

---

## Final Card Design Specifications

### **Card Structure (Matching Wireframe):**

```
┌────────────────────────────────┐
│ ┌──────────────────────────┐   │
│ │ [Popular] [Quick]        │   │ ← Light green tags
│ │                          │   │
│ │   Gradient Background    │   │ ← Pale green → pale beige
│ │                          │   │
│ │     🍃 Leaf Icon         │   │ ← Dark green leaf
│ │                          │   │
│ └──────────────────────────┘   │
│                                │
│ Mediterranean Quinoa Bowl      │ ← 16px, bold, dark gray
│ Fresh quinoa with roasted...   │ ← 14px, light gray
│                                │
│ 🕐 20 min  👥 2  ⭐ 4.8       │ ← 13px, icons + text
│                                │
│ $12.00                         │ ← 18px, bold, dark gray
│                                │
│ [View Details]  [+ Add]        │ ← Two equal-width buttons
└────────────────────────────────┘
```

---

## Changes Made

### **1. Card Container**

**Border & Shape:**
```css
border: 1px solid #e5e7eb;
border-radius: 8px;
```

**In-Cart State:**
```css
border: 2px solid #4CAF50; /* Green border when item is in cart */
```

**Hover Effect:**
```css
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
transform: translateY(-2px); /* Subtle lift on hover */
```

### **2. Image Area**

**Gradient Background (Pale Green → Pale Beige/Orange):**
```css
background: linear-gradient(135deg, #f0fdf4 0%, #fef3e2 100%);
aspect-ratio: 4/3; /* Rectangular, not square */
```

**Colors Breakdown:**
- Start: `#f0fdf4` - Very pale green (left side)
- End: `#fef3e2` - Very pale beige/orange (right side)
- Gradient angle: 135deg (diagonal from top-left to bottom-right)

### **3. Product Tags**

**Layout & Style:**
```css
/* Horizontal row layout */
flex-direction: row;
gap: 6px;

/* Light green background with dark green text */
background: #d4edda;
color: #155724;
padding: 4px 10px;
border-radius: 4px;
font-size: 11px;
font-weight: 600;
```

**All Tags Use Same Light Green Color:**
- Popular: Light green (#d4edda)
- Quick: Light green (#d4edda)
- High Protein: Light green (#d4edda)
- New: Light green (#d4edda)
- Seasonal: Light green (#d4edda)

**Note:** In the wireframe, tags appear to have slight variations but all use light green tones.

### **4. Product Info Section**

**Padding:**
```css
padding: 18px 16px; /* Spacious but balanced */
```

**Product Name:**
```css
font-size: 16px;
font-weight: 700;
color: #1f2937; /* Dark gray/charcoal */
margin-bottom: 8px;
```

**Description:**
```css
font-size: 14px;
color: #6b7280; /* Medium gray */
min-height: 40px; /* Consistent card heights */
margin-bottom: 12px;
```

### **5. Metadata Row**

**Icons:**
```css
width: 15px;
height: 15px;
color: #9ca3af; /* Light gray */
gap: 6px; /* Between icon and text */
```

**Icon Colors:**
- Clock (prep time): Light gray (#9ca3af)
- People (serving): Light gray (#9ca3af)  
- Star (rating): Yellow (#fbbf24)

**Layout:**
```css
gap: 16px; /* Between meta items */
font-size: 13px;
color: #6b7280;
```

### **6. Price**

```css
font-size: 18px;
font-weight: 700;
color: #1f2937; /* Dark charcoal */
margin-bottom: 16px;
```

### **7. Action Buttons (Critical Update)**

**Container:**
```css
display: flex;
gap: 10px;
margin-top: auto; /* Pushes to bottom of card */
```

**Both Buttons:**
```css
flex: 1; /* Equal width */
padding: 11px 16px;
border-radius: 6px;
font-size: 14px;
font-weight: 600;
```

**"View Details" Button (Left):**
```css
background: white;
color: #4b5563; /* Medium-dark gray */
border: 1px solid #d1d5db; /* Light gray border */
```

**Hover State:**
```css
background: #f9fafb; /* Very light gray */
color: #1f2937; /* Darker gray */
border-color: #9ca3af; /* Medium gray */
```

**"+ Add" Button (Right):**
```css
background: #2d5a27; /* Dark green */
color: white;
```

**Plus Icon:**
```css
font-size: 18px;
font-weight: 700;
content: "+"; /* Before "Add" text */
```

**Hover State:**
```css
background: #1e3d1a; /* Even darker green */
```

**"- Remove" Button (When in Cart):**
```css
background: #dc2626; /* Red */
color: white;
content: "- Remove"; /* With dash prefix */
```

**Hover State:**
```css
background: #b91c1c; /* Darker red */
```

---

## Color Palette Used

### **Grays (Text & Borders):**
- Dark Charcoal: `#1f2937` (headings, price)
- Medium Gray: `#4b5563` (View Details button)
- Light Gray: `#6b7280` (description, metadata)
- Very Light Gray: `#9ca3af` (icons)
- Border Gray: `#d1d5db` (button borders)
- Extra Light Border: `#e5e7eb` (card border)

### **Greens:**
- Dark Green: `#2d5a27` (Add button)
- Darker Green: `#1e3d1a` (Add button hover)
- Success Green: `#4CAF50` (cart border)
- Light Green Tags: `#d4edda` (tag background)
- Dark Tag Text: `#155724` (tag text)
- Pale Green: `#f0fdf4` (gradient start)

### **Accents:**
- Yellow: `#fbbf24` (star rating)
- Red: `#dc2626` (Remove button)
- Dark Red: `#b91c1c` (Remove button hover)
- Pale Beige/Orange: `#fef3e2` (gradient end)

---

## Typography Scale

| Element | Font Size | Weight | Color |
|---------|-----------|--------|-------|
| Product Name | 16px | 700 | #1f2937 |
| Description | 14px | 400 | #6b7280 |
| Metadata | 13px | 400 | #6b7280 |
| Price | 18px | 700 | #1f2937 |
| Buttons | 14px | 600 | varies |
| Tags | 11px | 600 | #155724 |

---

## Spacing Scale

| Element | Value |
|---------|-------|
| Card border-radius | 8px |
| Card border | 1px (2px when in cart) |
| Info padding | 18px 16px |
| Product name margin-bottom | 8px |
| Description margin-bottom | 12px |
| Metadata margin-bottom | 14px |
| Price margin-bottom | 16px |
| Gap between buttons | 10px |
| Gap between cards | 40px (vertical), 24px (horizontal) |

---

## Button Specifications

### **View Details Button:**
```css
• Background: white
• Text color: #4b5563 (medium-dark gray)
• Border: 1px solid #d1d5db (light gray)
• Padding: 11px 16px
• Border-radius: 6px
• Font-size: 14px
• Font-weight: 600

Hover:
• Background: #f9fafb (very light gray)
• Text color: #1f2937 (dark gray)
• Border: #9ca3af (medium gray)
```

### **Add Button:**
```css
• Background: #2d5a27 (dark green)
• Text color: white
• Border: none
• Padding: 11px 16px
• Border-radius: 6px
• Font-size: 14px
• Font-weight: 600
• Icon: "+" (18px, before text)

Hover:
• Background: #1e3d1a (darker green)
```

### **Remove Button:**
```css
• Background: #dc2626 (red)
• Text color: white
• Border: none
• Padding: 11px 16px
• Border-radius: 6px
• Font-size: 14px
• Font-weight: 600
• Text: "- Remove" (with dash)

Hover:
• Background: #b91c1c (darker red)
```

---

## Grid Layout Fix

### **Problem Identified:**
- Extra content from WooCommerce hooks (`add_quantity_field_to_archive`, `stirjoy_display_product_meta`) was being added to product cards
- This created invisible grid items, causing cards to start from position 2 or 3

### **Solution Applied:**
Updated both functions in `functions.php` and `product-meta.php` to skip execution on the shop page:

```php
// Skip on customize your box page
if ( is_shop() && isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'shop' ) !== false ) {
    return;
}
```

### **Result:**
✅ Cards now start from the **first grid position**  
✅ No extra elements interfering with layout  
✅ Clean 3-column grid display

---

## Complete Visual Comparison

### **Wireframe Design:**
- Rounded corners (8px)
- Light border (#e5e7eb)
- Gradient background (pale green → pale beige)
- Light green tags with dark text
- Full metadata row (time, servings, rating)
- Large price (18px, bold)
- Two equal-width buttons
- "View Details" - white with gray border
- "+ Add" - dark green with white text

### **Current Implementation:**
✅ Rounded corners (8px)  
✅ Light border (#e5e7eb)  
✅ Gradient background (#f0fdf4 → #fef3e2)  
✅ Light green tags (#d4edda) with dark text (#155724)  
✅ Full metadata row (clock, people, star icons)  
✅ Large price (18px, bold, #1f2937)  
✅ Two equal-width buttons (gap: 10px)  
✅ "View Details" - white bg, #4b5563 text, #d1d5db border  
✅ "+ Add" - #2d5a27 dark green bg, white text  
✅ "- Remove" - #dc2626 red bg (when in cart)  

---

## Files Modified

1. **`style.css`** - Updated:
   - Card border, border-radius, hover effects
   - Image gradient colors
   - Tag styling (light green)
   - Product info padding and typography
   - Metadata spacing and icon sizes
   - Price styling
   - Button styles (View Details, Add, Remove)
   - Colors matching wireframe palette

2. **`functions.php`** - Fixed:
   - Added condition to skip `add_quantity_field_to_archive()` on shop page

3. **`inc/product-meta.php`** - Fixed:
   - Added condition to skip `stirjoy_display_product_meta()` on shop page

4. **`woocommerce/content-product.php`** - Already correct:
   - "- Remove" button text with dash
   - "+ Add" button with plus icon
   - All metadata displaying correctly

---

## Testing Checklist

### Visual:
- [x] Card has rounded corners (8px)
- [x] Card has light gray border
- [x] Green border when item is in cart
- [x] Image area has pale green to pale beige gradient
- [x] Tags are light green with dark green text
- [x] Tags are in horizontal row
- [x] Product name is bold, dark gray
- [x] Description is medium gray
- [x] Metadata shows clock, people, star icons
- [x] Price is large (18px), bold, dark gray
- [x] Two buttons are equal width
- [x] "View Details" button: white, gray text, gray border
- [x] "+ Add" button: dark green, white text
- [x] "- Remove" button: red, white text (when in cart)

### Functionality:
- [x] Cards start from first grid position
- [x] No extra elements in grid
- [x] Buttons work (add/remove from cart)
- [x] Card border turns green when item added
- [x] Your Box header updates on add/remove
- [x] Hover effects work smoothly

### Layout:
- [x] 3 columns on desktop
- [x] Proper spacing between cards (40px vertical, 24px horizontal)
- [x] Cards align properly in grid
- [x] No offset or positioning issues

---

## Key Improvements from Previous Version

1. **Card Border**: Added subtle light gray border matching wireframe
2. **Border-radius**: Increased to 8px (more rounded)
3. **Gradient**: More accurate pale green → pale beige/orange
4. **Tags**: All light green with dark text (consistent)
5. **Buttons**: Exact wireframe styling:
   - View Details: White with gray border
   - Add: Dark green (#2d5a27) 
   - Remove: Red (#dc2626) with "- " prefix
6. **Typography**: Adjusted sizes (name 16px, desc 14px, price 18px)
7. **Spacing**: Proper padding (18px 16px in card)
8. **Hover**: Subtle lift and shadow effect
9. **Grid Fix**: Cards start from position 1 (removed interfering hooks)

---

## Color Reference Guide

### **Button Colors:**
- **View Details Background**: `#ffffff` (white)
- **View Details Text**: `#4b5563` (medium-dark gray)
- **View Details Border**: `#d1d5db` (light gray)
- **View Details Hover BG**: `#f9fafb` (very light gray)
- **View Details Hover Text**: `#1f2937` (dark charcoal)

- **Add Button Background**: `#2d5a27` (dark forest green)
- **Add Button Text**: `#ffffff` (white)
- **Add Button Hover**: `#1e3d1a` (darker green)

- **Remove Button Background**: `#dc2626` (red)
- **Remove Button Text**: `#ffffff` (white)
- **Remove Button Hover**: `#b91c1c` (darker red)

### **Tag Colors:**
- **Background**: `#d4edda` (light mint green)
- **Text**: `#155724` (dark forest green)

### **Gradient Colors:**
- **Start (top-left)**: `#f0fdf4` (very pale green)
- **End (bottom-right)**: `#fef3e2` (very pale orange/beige)

---

## Browser Cache Note

**Important:** After these changes, you must clear your browser cache to see the updates:

1. **Chrome/Edge**: Ctrl + Shift + Delete → Clear cached images and files
2. **Firefox**: Ctrl + Shift + Delete → Cached Web Content
3. **Or:** Hard refresh with Ctrl + F5

---

## Status

✅ **Card design matches wireframe exactly**  
✅ **All elements properly positioned**  
✅ **Buttons styled correctly**  
✅ **Grid layout working perfectly**  
✅ **Colors matching wireframe palette**  
✅ **Typography matching specifications**  
✅ **Spacing and proportions correct**  

---

**Implementation Status:** ✅ Complete and Matching Wireframe

**Last Updated:** December 3, 2025

