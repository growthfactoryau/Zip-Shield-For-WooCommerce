# Zip Shield for WooCommerce

Modern WooCommerce plugin for restricting product purchases by ZIP/postal codes.

Zip Shield allows store owners to create advanced ZIP-based restriction rules for WooCommerce products and categories. Automatically remove restricted products from checkout, display compliance notices, and maintain compatibility with modern WooCommerce features like Checkout Blocks and HPOS.

---

# Features

## Advanced ZIP Restrictions

Restrict purchases using:

* Individual products
* Product categories
* Product variations
* Allow-only ZIP lists
* Block ZIP lists

---

## Automatic Cart Validation

When a customer enters an unsupported ZIP/postal code:

* Restricted products are automatically removed
* Custom compliance notices are displayed
* Other non-restricted products remain in cart

---

## Modern Admin Dashboard

Includes a beautiful 2026-ready admin interface with:

* Modern card layout
* AJAX product search
* Category multi-select
* Smooth toggles
* Responsive design
* WooCommerce-native experience

---

## WooCommerce Compatibility

Built for modern WooCommerce stores:

* WooCommerce HPOS support
* Checkout Blocks support
* Cart Blocks support
* Store API compatibility
* Variable product support
* AJAX cart validation

---

# Example Use Cases

Perfect for stores selling regulated products such as:

* THC / CBD products
* Alcohol-related products
* Restricted supplements
* State-regulated items
* Dealer-only products
* Local delivery products

---

# Installation

1. Upload the plugin to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin
3. Go to:

WooCommerce → Zip Shield

4. Create your first ZIP restriction rule

---

# Creating Rules

Each rule supports:

* Rule Name
* Restriction Type
* Product Assignment
* Category Assignment
* ZIP Code Lists
* Custom Restriction Messages
* Enable/Disable Toggle

---

# Restriction Modes

## Allow Only

Only customers from specified ZIP codes can purchase products.

Example:

90210
10001
33101

---

## Block ZIPs

Customers from specified ZIP codes are blocked from purchasing products.

---

# How It Works

1. Customer adds restricted product to cart
2. Customer enters ZIP/postal code
3. Zip Shield validates cart contents
4. Invalid products are automatically removed
5. Customer receives compliance notice

---

# Performance Focused

Zip Shield is built with scalability and performance in mind.

Features include:

* Optimized rule caching
* Lightweight validation engine
* Minimal database queries
* Efficient WooCommerce hooks

---

# Developer Friendly

* OOP architecture
* Namespaced classes
* WooCommerce coding standards
* HPOS-ready
* Extensible rule engine

---

# Roadmap

Planned future features:

* ZIP range support
* Wildcard ZIP matching
* State restrictions
* Dealer locator integration
* CSV ZIP import/export
* Restriction analytics
* Logging system
* GeoIP validation
* REST API endpoints

---

# Screenshots

## Suggested GitHub Screenshots

### 1. Main Admin Dashboard

Show the modern Zip Shield dashboard with:

* Create Rule form
* Existing Rules list
* Modern card layout

Recommended filename:
`screenshot-dashboard.png`

---

### 2. Rule Creation Interface

Show:

* Product AJAX search
* Category selection
* ZIP textarea
* Enable toggle

Recommended filename:
`screenshot-rule-editor.png`

---

### 3. Product Restriction Notice

Frontend screenshot showing:

* Restricted product removed
* WooCommerce error notice

Recommended filename:
`screenshot-checkout-notice.png`

---

### 4. Checkout Blocks Compatibility

Show validation working inside WooCommerce Checkout Blocks.

Recommended filename:
`screenshot-block-checkout.png`

---

### 5. Mobile Responsive Admin UI

Show modern responsive admin design on smaller screen.

Recommended filename:
`screenshot-mobile-admin.png`

---

# Requirements

* WordPress 6.5+
* WooCommerce 8.0+
* PHP 8.1+

---

# License

GPL v2 or later

---

# Contributing

Contributions, feature suggestions, and pull requests are welcome.

---

# Author

Developed for modern WooCommerce compliance workflows.
