# Shop Page: Wireframe vs Real Page Analysis

**Report Date:** December 2, 2025  
**Project:** Stirjoy WordPress Site  
**Purpose:** Detailed analysis of differences between wireframe Shop page and current implementation

---

## Executive Summary

The wireframe shows a **specialized meal customization interface** ("Customize Your Box") designed for subscription box services, while the real page shows a **generic WooCommerce product listing** ("Our Shop"). The pages serve different purposes and have significantly different user experiences.

**Key Finding:** The real Shop page needs to be completely rebuilt to match the wireframe's meal-focused customization interface.

---

## 1. PAGE IDENTITY & PURPOSE

### Wireframe
- **Title:** "Customize Your Box"
- **Purpose:** Meal selection and subscription box customization
- **User Flow:** Select/swap meals for monthly subscription box
- **Subtitle:** "Your box is pre-filled with this month's selection. Swap meals to customize!"

### Real Page
- **Title:** "Our Shop"
- **Purpose:** General e-commerce product catalog
- **User Flow:** Browse and purchase various products
- **Breadcrumbs:** "Home / Shop"
- **Info:** "Showing 1-12 of 23 results"

### Impact
❌ **CRITICAL DIFFERENCE** - Completely different page concepts and user goals.

---

## 2. SEARCH FUNCTIONALITY

### Wireframe
- **Location:** Prominently placed below page title, centrally positioned
- **Design:** Large search input field with placeholder "Search meals..."
- **Button:** Green magnifying glass icon button on the right
- **Visibility:** Always visible, primary feature
- **Purpose:** Quick meal filtering

### Real Page
- **Location:** Search icon only in top header
- **Design:** No visible search field in main content area
- **Visibility:** Hidden behind icon click
- **Purpose:** General site search

### Impact
❌ **MISSING FEATURE** - No prominent meal search functionality in main content area.

---

## 3. CATEGORY NAVIGATION

### Wireframe
- **Type:** Horizontal tab system
- **Position:** Directly below search bar, centrally aligned
- **Categories:** "All", "Breakfast", "Mains", "Snacks", "Desserts"
- **Active State:** Darker background on selected tab (e.g., "Mains")
- **Interaction:** Single-click filtering
- **Design:** Clean, minimal, horizontal layout

### Real Page
- **Type:** Vertical sidebar list
- **Position:** Right sidebar
- **Categories:** 
  - Breakfast (3)
  - Coffee Crates (3)
  - Crates & Boxes (3)
  - Fresh Boxes (8)
  - Mains (2)
  - Meal Kits Crates (3)
  - Past Crates (4)
  - Snacks & Desserts (1)
  - Uncategorized (1)
- **Display:** Shows item counts in parentheses
- **Design:** Standard WooCommerce sidebar widget

### Impact
❌ **WRONG NAVIGATION PATTERN** - Sidebar list vs prominent horizontal tabs.  
❌ **WRONG CATEGORIES** - Includes non-meal products (Coffee Crates, Crates & Boxes).

---

## 4. PRODUCT LAYOUT & ORGANIZATION

### Wireframe
- **Layout:** 3-column grid with consistent spacing
- **Organization:** Grouped by category sections with visible headings:
  - "Mains" section (8 products shown)
  - "Breakfast" section (1 product shown)
  - "Snacks" section (1 product shown)
  - "Desserts" section (1 product shown)
- **Display:** All products on single scrollable page
- **Product Types:** Only meal items
- **Visual Hierarchy:** Clear section separation

### Real Page
- **Layout:** 3-column grid
- **Organization:** No section groupings or headings
- **Display:** Paginated (showing 1-12 of 23, with page numbers "1 2 →")
- **Product Types:** Mixed (subscriptions, coffee crates, meal kits, individual meals)
- **Visual Hierarchy:** Flat list with no organization

### Impact
❌ **MISSING STRUCTURE** - No category section headings.  
❌ **WRONG PAGINATION** - Should show all on one page.  
❌ **WRONG PRODUCT MIX** - Shows non-meal items.

---

## 5. PRODUCT CARD DESIGN

### Wireframe Product Card Components

**Visual Structure:**
1. **Image Area:**
   - Large rectangular area (beige/cream background)
   - Placeholder: Green leaf icon
   - Aspect ratio optimized for meal photos
   - Clean, spacious design

2. **Tags (Top-Left Overlay on Image):**
   - Multiple small rectangular badges
   - Examples: "Popular", "Quick", "High Protein", "Seasonal", "New"
   - Green background with white text
   - Stacked vertically if multiple

3. **Meal Name:**
   - Bold, large text
   - Examples: "Mediterranean Quinoa Bowl", "Asian Fusion Stir-fry"

4. **Description:**
   - Short, specific meal description
   - Examples: "Fresh quinoa with roasted vegetables", "Crispy tofu with seasonal vegetables"
   - 1-2 lines of text

5. **Metadata Row (Icons + Text):**
   - **Prep Time:** Clock icon + "20 min" / "15 min" / "23 min"
   - **Serving Size:** Person/users icon + "2" / "1"
   - **Rating:** Star icon + "4.8" / "4.9" / "4.7"
   - All three displayed horizontally in a row
   - Small, muted styling

6. **Price:**
   - Bold text
   - Examples: "$12.00", "$8.00", "$6.00", "$7.00"

7. **Action Buttons (Bottom):**
   - **Two buttons side by side:**
     - "View Details" - Secondary style (white background, grey text, grey border)
     - "Add" - Primary style (green background, white text) OR
     - "Remove" - Destructive style (red background, white text)
   - Full-width button layout
   - Clear visual states

**Card Spacing:**
- Generous padding
- Clear visual separation between cards
- Consistent card heights

### Real Page Product Card Components

**Visual Structure:**
1. **Image Area:**
   - Small square product image
   - Mixed image types (boxes, food photos, coffee machines, etc.)
   - Some images have "SALE!" badge (red)
   - Some images have "Popular" badge

2. **Product Title:**
   - Bold text
   - Examples: "Annual Subscription", "Arabica Coffee Crate", "Burrito bowl"

3. **"View Details" Link:**
   - Text link
   - Appears near image

4. **Quantity Selector:**
   - Small input field showing "0"
   - Plus (+) and minus (-) buttons
   - Positioned prominently

5. **Rating:**
   - Star icons display (e.g., ★★★☆☆)
   - No numerical rating shown

6. **Description:**
   - Generic placeholder text for most products: "Pellentesque habitant morbi tristique senectus..."
   - Some products have specific descriptions: "Burrito bowl 17 min prep with quinoa and dried tomatoes"

7. **Action Area:**
   - Quantity selector is primary interaction
   - No visible "Add to Cart" button in card

**Card Spacing:**
- Tighter spacing
- Less visual separation
- Inconsistent card heights

### Detailed Comparison Table

| Component | Wireframe | Real Page | Status |
|-----------|-----------|-----------|--------|
| **Image Size** | Large, prominent | Small, compact | ❌ Too small |
| **Image Style** | Clean placeholder with leaf icon | Mixed product photos | ❌ Inconsistent |
| **Tags/Badges** | Multiple tags per product (Popular, Quick, High Protein, etc.) | Occasional "SALE!" or "Popular" badge | ❌ Missing tags |
| **Product Name** | Meal-specific names | Mixed product types | ❌ Wrong products |
| **Description** | Specific, relevant meal descriptions | Generic placeholder text | ❌ Wrong content |
| **Prep Time** | Clock icon + time (e.g., "20 min") | Only in some descriptions | ❌ Missing icon/field |
| **Serving Size** | Person icon + number (e.g., "2") | Not shown | ❌ Missing |
| **Rating Display** | Star icon + decimal (e.g., "4.8") | Star icons only | ❌ No number |
| **Price** | Clear, prominent | Present | ✅ Similar |
| **Primary Action** | "Add" (green) / "Remove" (red) button | Quantity selector | ❌ Different UX |
| **Secondary Action** | "View Details" button | "View Details" link | ⚠️ Similar concept, different style |

### Impact
❌ **COMPLETE CARD REDESIGN NEEDED** - All components need to be rebuilt.  
❌ **MISSING METADATA ICONS** - No prep time, serving size visual indicators.  
❌ **MISSING TAGS SYSTEM** - No Popular, Quick, High Protein, Seasonal, New badges.  
❌ **WRONG ACTION PATTERN** - Quantity selector vs Add/Remove buttons.

---

## 6. PRODUCT INFORMATION

### Wireframe - Specific Examples

**Mains Products:**
1. **Mediterranean Quinoa Bowl**
   - Tags: Popular, Quick
   - Description: "Fresh quinoa with roasted vegetables"
   - Prep: 20 min | Servings: 2 | Rating: 4.8
   - Price: $12.00
   - Status: In box (Remove button)

2. **Asian Fusion Stir-fry**
   - Tags: Popular, High Protein
   - Description: "Crispy tofu with seasonal vegetables"
   - Prep: 15 min | Servings: 2 | Rating: 4.9
   - Price: $12.00
   - Status: In box (Remove button)

3. **Thai Green Curry**
   - Tags: Popular
   - Description: "Coconut curry with jasmine rice"
   - Prep: 23 min | Servings: 2 | Rating: 4.8
   - Price: $12.00
   - Status: In box (Remove button)

4. **Italian Pasta Primavera**
   - Tags: Seasonal
   - Description: "Whole wheat pasta with seasonal vegetables"
   - Prep: 20 min | Servings: 2 | Rating: 4.4
   - Price: $12.00
   - Status: Not in box (Add button)

**Breakfast Products:**
- **Overnight Oats Parfait**
  - Tags: Quick
  - Description: "Creamy oats with fresh berries"
  - Prep: 0 min | Servings: 1 | Rating: 4.7
  - Price: $8.00

**Snacks Products:**
- **Energy Bites**
  - Tags: Quick
  - Description: "No-bake protein-packed snacks"
  - Prep: 0 min | Servings: 12 | Rating: 4.8
  - Price: $6.00

**Desserts Products:**
- **Chocolate Avocado Mousse**
  - Tags: New
  - Description: "Rich and creamy chocolate dessert"
  - Prep: 0 min | Servings: 1 | Rating: 4.6
  - Price: $7.00

### Real Page - Examples

1. **Annual Subscription**
   - Image: Black box
   - Description: "Pellentesque habitant morbi tristique senectus..."
   - Rating: Stars display
   - No prep time, no serving info

2. **Burrito bowl**
   - Image: Green container with food
   - Description: "Burrito bowl 17 min prep with quinoa and dried tomatoes"
   - Rating: Stars display
   - Has some prep time info in description

3. **Meal Kit Slim**
   - Badge: "SALE!"
   - Image: Black containers
   - Description: "Pellentesque habitant morbi tristique senectus..."

4. **Arabica Coffee Crate**
   - Image: Coffee machine
   - Description: "Pellentesque habitant morbi tristique senectus..."
   - Not a meal item

### Impact
❌ **WRONG PRODUCT TYPE** - Includes subscriptions, coffee crates, not just meals.  
❌ **GENERIC CONTENT** - Placeholder "Lorem ipsum" style text instead of real descriptions.  
❌ **MISSING MEAL DATA** - No consistent prep time, serving size information.  
❌ **INCONSISTENT PRICING** - Wide range due to mixed product types.

---

## 7. SIDEBAR & FILTERS

### Wireframe
- **Sidebar:** None
- **Filtering:** All done through top category tabs ("All", "Breakfast", "Mains", "Snacks", "Desserts")
- **Philosophy:** Simple, clean, focused on quick category switching

### Real Page
- **Sidebar:** Right side, extensive filter options
- **Filter Categories:**
  1. **CATEGORIES** (with counts)
  2. **PRICE FILTER**
     - Slider with two handles
     - Range: $0 to $30
     - "FILTER" button (green)
     - Display: "Price: $0 - $30"
  3. **CANCEL ANYTIME**
     - No (2)
     - Yes (20)
  4. **COLORS**
     - Dark (1), Green (1), Platinum (2), Red (1), Rose (2), Yellow (1)
  5. **AVERAGE RATING**
     - 5 stars (4), 4 stars (2), 3 stars (1)
  6. **PRODUCT TAGS** (cloud format)
     - ANUAL, BOX, COFFEE, COFFEE CRATES, FRESH, FRESH BOX, GIFTBOX, HEALTHY MEAL, HIGH PROTEIN, LUXURY, MEAL, NEW, POPULAR, QUICK, SUBSCRIPTION BOXES, SUBSCRIPTIONS

### Impact
❌ **WRONG LAYOUT APPROACH** - Wireframe has no sidebar, uses top tabs instead.  
❌ **UNNECESSARY FILTERS** - Colors, Cancel Anytime not relevant to meal selection.  
⚠️ **TAGS IN WRONG PLACE** - Tags should be on product cards, not in sidebar filter.

---

## 8. CART/BOX INTEGRATION

### Wireframe
- **Header Element:** Secondary header bar showing "Your Box"
  - Cart icon with item count badge
  - Display format: Shopping cart icon + "Your Box" text
  - Shows cart total: "$77.99" and item count "1"
  
- **Product State Indication:**
  - Items already in box show **"Remove"** button (red background, white text)
  - Items not in box show **"Add"** button (green background, white text)
  - Clear visual distinction of what's selected

- **User Experience:**
  - User can see current box contents at a glance
  - Easy to add/remove items with single click
  - Real-time feedback on box contents

### Real Page
- **Header Element:** Standard cart icon in main header
  - No special "Your Box" indicator
  - Standard WooCommerce cart

- **Product State Indication:**
  - Quantity selectors default to "0"
  - No visual indication of items currently in cart
  - No "in cart" vs "not in cart" distinction on product cards

- **User Experience:**
  - Must manually adjust quantity numbers
  - No clear feedback on cart contents from shop page
  - Standard e-commerce flow

### Impact
❌ **MISSING "YOUR BOX" INDICATOR** - No dedicated box/subscription cart display.  
❌ **WRONG INTERACTION MODEL** - Quantity selectors vs Add/Remove toggle.  
❌ **NO STATE INDICATION** - Can't see which meals are already selected.

---

## 9. VISUAL DESIGN & STYLING

### Wireframe Design Language

**Colors:**
- Primary action: Green (#2d5a27 or similar)
- Destructive action: Red
- Secondary: White/Grey
- Background: Clean white
- Card background: Light beige/cream for image areas
- Text: Dark grey/black

**Typography:**
- Large, bold headings
- Clear hierarchy
- Readable body text
- Consistent sizing

**Spacing:**
- Generous padding around elements
- Clear separation between cards
- Breathing room in layout
- Consistent margins

**Card Design:**
- Rounded corners (subtle)
- Subtle shadows or borders
- Clean, modern aesthetic
- Professional meal photography placeholders

**Buttons:**
- Full-width in cards
- Clear states (hover, active)
- Icon + text combinations
- Bold, readable labels

**Icons:**
- Clock for prep time
- Person/users for serving size
- Star for ratings
- Magnifying glass for search
- Consistent icon style throughout

### Real Page Design Language

**Colors:**
- WooCommerce default styling
- Green for "Subscribe" button
- Red for "SALE!" badges
- Standard product grid colors

**Typography:**
- Standard WordPress/WooCommerce fonts
- Less hierarchy
- Smaller text overall

**Spacing:**
- Tighter spacing
- Less padding
- More content per viewport
- WooCommerce default margins

**Card Design:**
- Standard WooCommerce product cards
- Basic borders
- Minimal styling
- Mixed image quality/styling

**Buttons/Controls:**
- Quantity selectors prominent
- Standard button styling
- "View Details" text links
- Less visual polish

**Icons:**
- Star icons for ratings
- Plus/minus for quantity
- Cart icon in header
- Inconsistent icon usage

### Impact
❌ **INCONSISTENT DESIGN LANGUAGE** - Needs custom styling to match wireframe.  
❌ **LESS POLISHED** - Generic WooCommerce vs custom meal-focused design.  
❌ **WRONG VISUAL HIERARCHY** - Important information not emphasized.

---

## 10. USER EXPERIENCE & FLOW

### Wireframe User Journey

1. **Arrival:** User lands on "Customize Your Box" page
2. **Orientation:** Reads subtitle explaining pre-filled box concept
3. **Quick Filter:** Uses category tabs (All/Breakfast/Mains/Snacks/Desserts) for quick browsing
4. **Search (Optional):** Uses search bar to find specific meals
5. **Browse:** Scrolls through categorized sections with clear headings
6. **Evaluate Meal:** Views meal card with all key information:
   - Visual appeal (image)
   - Attributes (tags: Popular, Quick, High Protein)
   - Details (prep time, servings, rating)
   - Description
   - Price
7. **Action:** Clicks "Add" (green) to add meal or "Remove" (red) to remove from box
8. **Feedback:** Immediate visual change (button switches from Add to Remove)
9. **Monitor:** Checks "Your Box" header indicator for cart total and item count
10. **Details (Optional):** Clicks "View Details" for more meal information

**Key UX Principles:**
- Simple, focused interaction
- Clear feedback
- Minimal steps to add/remove
- Visual state indication
- Category-based browsing
- Quick filtering

### Real Page User Journey

1. **Arrival:** User lands on "Our Shop" page
2. **Orientation:** Sees breadcrumbs and product count
3. **Filter (Optional):** Uses sidebar filters (categories, price, rating, tags, colors)
4. **Browse:** Scrolls through paginated product grid
5. **Evaluate Product:** Views product card:
   - Small image
   - Generic description
   - Star rating
   - Price
6. **Action:** Adjusts quantity selector from 0 to desired number
7. **Add to Cart:** Must find and click add to cart button (not visible in card)
8. **Navigate:** Uses pagination to see more products
9. **Details (Optional):** Clicks "View Details" link

**Key UX Principles:**
- Standard e-commerce flow
- Multiple filter options
- Quantity-based selection
- Paginated browsing
- Less immediate feedback

### Impact
❌ **DIFFERENT USER MENTAL MODEL** - Subscription box customization vs shopping cart.  
❌ **MORE COMPLEX FLOW** - More steps to add items in real page.  
❌ **LESS IMMEDIATE FEEDBACK** - Quantity selectors don't show "in box" state clearly.  
❌ **PAGINATION INTERRUPTS FLOW** - Wireframe shows all items, better for customization.

---

## 11. CONTENT ORGANIZATION

### Wireframe Content Structure

```
Customize Your Box
└── Subtitle: "Your box is pre-filled..."
    └── Search Bar: "Search meals..."
        └── Category Tabs: [All | Breakfast | Mains | Snacks | Desserts]
            └── Mains Section
                ├── Mediterranean Quinoa Bowl
                ├── Asian Fusion Stir-fry
                ├── Mexican Fiesta Bowl
                ├── Creamy Mushroom Risotto
                ├── Thai Green Curry
                ├── Protein Power Bowl
                ├── Italian Pasta Primavera
                └── Buddha Bowl Deluxe
            └── Breakfast Section
                └── Overnight Oats Parfait
            └── Snacks Section
                └── Energy Bites
            └── Desserts Section
                └── Chocolate Avocado Mousse
```

**Characteristics:**
- Hierarchical organization
- Clear section boundaries
- Logical grouping
- All content on one scrollable page
- Top-to-bottom reading flow

### Real Page Content Structure

```
Our Shop
├── Breadcrumbs: Home / Shop
├── Results Count: "Showing 1-12 of 23 results"
├── Sort Dropdown: "Default sorting"
└── Two-Column Layout
    ├── Left: Product Grid (3 columns)
    │   ├── Annual Subscription
    │   ├── Arabica Coffee Crate
    │   ├── Bi-Annual Subscription
    │   ├── Burrito bowl
    │   ├── Colombie Coffee Crate
    │   ├── Fresh Box
    │   ├── Kenya Coffee Crate
    │   ├── Lentil curry
    │   ├── Meal Kit Slim
    │   ├── Meal Kit Yum
    │   ├── Meal Kit Zen
    │   └── Monthly 6-Meal Box
    ├── Pagination: [1] [2] [→]
    └── Right: Sidebar Filters
        ├── CATEGORIES (9 categories)
        ├── PRICE FILTER (slider)
        ├── CANCEL ANYTIME (2 options)
        ├── COLORS (6 colors)
        ├── AVERAGE RATING (3 levels)
        └── PRODUCT TAGS (16 tags)
```

**Characteristics:**
- Flat product list
- No section grouping
- Sidebar-heavy layout
- Paginated content
- Left-to-right + top-to-bottom reading

### Impact
❌ **WRONG INFORMATION ARCHITECTURE** - Flat list vs hierarchical sections.  
❌ **NO CONTENT GROUPING** - All products mixed together.  
❌ **PAGINATION FRAGMENTS EXPERIENCE** - Breaks up browsing flow.

---

## 12. TECHNICAL IMPLEMENTATION DIFFERENCES

### Wireframe Implied Technical Requirements

1. **Custom Page Template:**
   - Not standard WooCommerce shop
   - Custom layout and structure
   - Specialized for meal subscription

2. **Product Filtering:**
   - AJAX-based category tab switching
   - No page reload when switching tabs
   - Real-time search filtering

3. **Product Data Structure:**
   - Custom fields: prep_time, serving_size
   - Tag system: Popular, Quick, High Protein, Seasonal, New
   - Meal-specific categorization

4. **Cart/Box Integration:**
   - Custom "Your Box" display
   - Real-time cart total in header
   - Add/Remove toggle functionality
   - Visual state management (in box vs not in box)

5. **Product Cards:**
   - Custom card layout template
   - Icon + data display for metadata
   - Conditional button rendering (Add vs Remove)
   - Tag badge display system

6. **No Pagination:**
   - All products loaded at once
   - Smooth scrolling experience
   - Category sections with headings

### Real Page Current Implementation

1. **Standard WooCommerce:**
   - Default shop template
   - Standard WooCommerce loop
   - Generic product display

2. **Product Filtering:**
   - Sidebar widget filters
   - Page reload on filter change
   - Standard WooCommerce filtering

3. **Product Data Structure:**
   - Standard WooCommerce fields
   - Some products have prep time in description
   - Mixed product types in catalog

4. **Cart Integration:**
   - Standard WooCommerce cart
   - Cart icon in header
   - Quantity selectors on cards
   - No special "box" concept

5. **Product Cards:**
   - Standard WooCommerce product loop template
   - Basic product information
   - Generic "View Details" links
   - Some "SALE!" and "Popular" badges

6. **Pagination:**
   - Standard WooCommerce pagination
   - 12 products per page
   - Page numbers at bottom

### Impact
❌ **COMPLETE REBUILD NEEDED** - Can't use standard WooCommerce shop template.  
❌ **CUSTOM DEVELOPMENT REQUIRED** - Need custom page template, AJAX filtering, card layouts.  
❌ **PRODUCT DATA CHANGES** - Need custom fields for prep time, servings, proper tagging.  
❌ **CART CUSTOMIZATION** - Need custom "Your Box" functionality.

---

## 13. MISSING FEATURES SUMMARY

### Critical Missing Features (Must Have)

1. ❌ **"Customize Your Box" Page Concept**
   - Real page is generic shop, not box customization interface

2. ❌ **Prominent Search Bar**
   - Needs "Search meals..." field below title with green search button

3. ❌ **Category Tab Navigation**
   - Needs horizontal tabs: All, Breakfast, Mains, Snacks, Desserts
   - Should replace sidebar filters

4. ❌ **Section Headings**
   - Need visible "Mains", "Breakfast", "Snacks", "Desserts" headings
   - Group products by category on page

5. ❌ **Product Tags on Cards**
   - Need badges: Popular, Quick, High Protein, Seasonal, New
   - Display on product image area

6. ❌ **Prep Time Display**
   - Clock icon + minutes (e.g., "20 min")
   - Needs custom field and display logic

7. ❌ **Serving Size Display**
   - Person icon + number (e.g., "2")
   - Needs custom field and display logic

8. ❌ **Numerical Ratings**
   - Star icon + decimal rating (e.g., "4.8")
   - Not just star icons

9. ❌ **Add/Remove Button System**
   - Green "Add" button for items not in box
   - Red "Remove" button for items in box
   - Replace quantity selectors

10. ❌ **"Your Box" Header Indicator**
    - Secondary header bar
    - Shows cart icon, "Your Box" text, item count, total price

11. ❌ **Meal-Only Product Filter**
    - Should only show meal items
    - Exclude subscriptions, coffee crates, other products

12. ❌ **Single-Page Display**
    - All products on one page
    - Remove pagination

### Important Missing Features (Should Have)

13. ⚠️ **Subtitle/Instruction Text**
    - "Your box is pre-filled with this month's selection. Swap meals to customize!"

14. ⚠️ **Meal-Specific Descriptions**
    - Replace "Pellentesque habitant..." placeholder text
    - Real meal descriptions

15. ⚠️ **Consistent Product Cards**
    - All cards same height
    - Standardized layout

16. ⚠️ **Visual State Indication**
    - Clear visual difference for items in box
    - Beyond just button change

17. ⚠️ **AJAX Category Switching**
    - No page reload when clicking category tabs
    - Smooth filtering experience

### Nice-to-Have Features

18. ○ **Hover States**
    - Card hover effects
    - Button hover animations

19. ○ **Loading States**
    - When filtering or searching
    - Skeleton screens

20. ○ **Empty States**
    - When search returns no results
    - When category has no items

---

## 14. REQUIRED CHANGES BY COMPONENT

### A. Page Structure
- [ ] Change page title from "Our Shop" to "Customize Your Box"
- [ ] Remove breadcrumbs
- [ ] Remove "Showing X of Y results" text
- [ ] Remove sorting dropdown
- [ ] Add subtitle: "Your box is pre-filled with this month's selection. Swap meals to customize!"

### B. Search & Navigation
- [ ] Add prominent search bar below title
- [ ] Implement "Search meals..." placeholder
- [ ] Add green search button with magnifying glass icon
- [ ] Replace sidebar categories with horizontal tab system
- [ ] Create tabs: All, Breakfast, Mains, Snacks, Desserts
- [ ] Implement active tab styling (darker background)
- [ ] Remove sidebar entirely

### C. Product Filtering
- [ ] Filter products to show only meals (exclude subscriptions, coffee, etc.)
- [ ] Organize products by category (Mains, Breakfast, Snacks, Desserts)
- [ ] Add visible section headings for each category
- [ ] Remove pagination - show all products on one page
- [ ] Implement AJAX category tab filtering

### D. Product Card Redesign
- [ ] Increase image size (large rectangular area)
- [ ] Add beige/cream background to image placeholder
- [ ] Add green leaf icon placeholder for meals without images
- [ ] Implement tag badge system (Popular, Quick, High Protein, Seasonal, New)
- [ ] Display tags overlaid on top-left of image
- [ ] Add prep time field and display (clock icon + "X min")
- [ ] Add serving size field and display (person icon + number)
- [ ] Change rating display to star icon + decimal number (e.g., "4.8")
- [ ] Display metadata in horizontal row (prep | servings | rating)
- [ ] Add meal-specific descriptions
- [ ] Implement two-button layout:
  - "View Details" (secondary style: white bg, grey text, grey border)
  - "Add" / "Remove" (primary/destructive style: green or red bg, white text)
- [ ] Remove quantity selectors
- [ ] Add consistent spacing and padding

### E. Cart/Box Integration
- [ ] Create secondary header bar for "Your Box" display
- [ ] Add cart icon with item count badge
- [ ] Display "Your Box" text next to icon
- [ ] Show current cart total (e.g., "$77.99")
- [ ] Implement Add button functionality (add to cart, change to Remove)
- [ ] Implement Remove button functionality (remove from cart, change to Add)
- [ ] Add real-time cart state updates (no page reload)
- [ ] Visual distinction for items in box vs not in box

### F. Product Data
- [ ] Add custom field: `_prep_time` (minutes)
- [ ] Add custom field: `_serving_size` (number)
- [ ] Create product tag taxonomy or use existing with specific tags:
  - Popular
  - Quick
  - High Protein
  - Seasonal
  - New
- [ ] Add real meal descriptions to products
- [ ] Ensure all meal products have proper categories
- [ ] Set up proper meal photography

### G. Styling & Visual Design
- [ ] Apply custom CSS for wireframe-matching design
- [ ] Implement clean, spacious card layout
- [ ] Style category tabs (horizontal, active state)
- [ ] Style search bar (prominent, centered)
- [ ] Style Add/Remove buttons (green/red, full-width)
- [ ] Style metadata icons (clock, person, star)
- [ ] Style tag badges (green background, white text, stacked)
- [ ] Add hover states for cards and buttons
- [ ] Ensure responsive design for mobile/tablet

### H. Functionality
- [ ] Implement real-time search filtering (AJAX)
- [ ] Implement category tab filtering (AJAX, no reload)
- [ ] Handle Add to Box interaction
- [ ] Handle Remove from Box interaction
- [ ] Update "Your Box" display on any cart change
- [ ] Add loading states during AJAX operations
- [ ] Add empty state for "no products found" in search
- [ ] Implement smooth scrolling between sections

---

## 15. DEVELOPMENT PRIORITY

### Phase 1: Critical Foundation (Week 1)
1. Create custom page template for "Customize Your Box"
2. Filter products to show only meals
3. Add prep_time and serving_size custom fields to products
4. Implement basic product card layout with correct information display
5. Add category tab navigation (can use page reload initially)

### Phase 2: Core Functionality (Week 2)
6. Implement search bar and filtering
7. Create Add/Remove button system
8. Integrate "Your Box" header display
9. Add section headings and product grouping
10. Style product cards to match wireframe

### Phase 3: Enhanced UX (Week 3)
11. Convert to AJAX filtering (no page reload)
12. Add tag badge system to products
13. Implement real-time cart updates
14. Add hover states and animations
15. Polish visual design

### Phase 4: Content & Testing (Week 4)
16. Add real meal descriptions and images
17. Tag all products appropriately
18. Test add/remove functionality
19. Test search and filtering
20. Mobile/responsive testing and fixes

---

## 16. RECOMMENDATIONS

### Immediate Actions
1. **Create New Custom Template:** Don't try to modify the existing shop page. Create a completely new custom page template for "Customize Your Box".

2. **Product Data Audit:** Review all products in the catalog:
   - Remove or hide non-meal items from this page
   - Add prep_time and serving_size to all meal products
   - Write proper meal descriptions
   - Tag products appropriately

3. **Cart Customization Strategy:** Decide if "Your Box" is:
   - A specialized cart for subscriptions only
   - A renamed cart for all products
   - A separate selection interface that generates a subscription product

### Technical Considerations
1. **WooCommerce Compatibility:** Ensure Add/Remove functionality works with WooCommerce cart system
2. **Performance:** Loading all products on one page - consider lazy loading images
3. **Mobile Experience:** Card layout needs to be responsive (possibly 2 columns on tablet, 1 on mobile)
4. **SEO:** Consider if this should be the main shop page or a separate subscription page

### Content Needs
1. **Photography:** Professional meal photography for all products
2. **Copywriting:** Unique, appetizing descriptions for each meal
3. **Product Data:** Accurate prep times and serving sizes
4. **Tags:** Consistent tagging system across all meals

---

## 17. CONCLUSION

The wireframe and real page represent fundamentally different concepts:
- **Wireframe:** Specialized meal subscription box customization interface
- **Real Page:** Generic WooCommerce product catalog

**To implement the wireframe, you need:**
1. ✅ Complete page rebuild (new custom template)
2. ✅ Custom product card design
3. ✅ Add/Remove button cart system
4. ✅ Category tab navigation
5. ✅ Prominent search functionality
6. ✅ Product data additions (prep time, servings, tags)
7. ✅ "Your Box" header integration
8. ✅ Single-page display (no pagination)
9. ✅ Meal-only product filtering
10. ✅ Section-based organization

**Estimated Development Time:** 3-4 weeks for complete implementation

**Technical Complexity:** High - requires custom WooCommerce integration, AJAX functionality, and significant frontend development

---

**End of Report**

