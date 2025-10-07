# 🧩 WooCommerce Blocks Addon – Project Setup Guide

This document is written as an instruction set for a GPT-5 model (or similar) inside **Cursor IDE** to help scaffold and build a **premium WordPress plugin** that extends **WooCommerce Blocks** with new Gutenberg blocks and customization features.

---

## 🎯 Project Overview

**Goal:**  
Create a WordPress plugin that adds **new WooCommerce-compatible Gutenberg blocks** and extends **existing WooCommerce blocks** with **extra design controls** (colors, typography, margins, paddings, borders, layout controls, etc.).

**Plugin Type:**  
WooCommerce Addon Plugin (for `@woocommerce/blocks`)

**Purpose:**  
Offer merchants, theme developers, and page builders **greater layout flexibility** and **custom block options**, potentially as a **premium plugin** later.

---

## 🧱 Existing WooCommerce Blocks Reference

WooCommerce core already provides blocks like:
- Product Grid (All Products, Hand-picked, On Sale, etc.)
- Cart, Checkout, Mini Cart
- Product Title, Image, Price, Button
- Filters (Attributes, Price, Stock)
- Category List
- Related Products
- Featured Product
- Reviews, Ratings

These are built on the `@woocommerce/blocks` package.

---

## 🚀 Planned Custom Blocks (Phase 1)

We will start with 3–5 minimal example blocks that are easy to expand later:

| Block Name | Description | Example Features |
|-------------|--------------|------------------|
| **Product Grid Pro** | Customizable grid of products | Adjustable columns, gap, card style |
| **Product Carousel** | Slider for featured/on-sale products | Arrows, autoplay, visible count |
| **Category Showcase** | Visual grid of product categories | Background, hover, text overlay |
| **Add to Cart Button Pro** | Customizable button | Styles, icon, animation, colors |
| **Price Badge / Sale Badge** | Decorative badge overlay | Shape, color, position |

---

## 🧩 Extended Block Controls

We will add **Block Inspector Controls** to extend styling and layout of all new blocks:

- `ColorControl` → background, text, button
- `SpacingControl` → margin, padding, gap
- `BorderControl` → border-radius, width, color
- `TypographyControl` → font-size, weight, align
- Responsive breakpoints (optional later)

---

## 🗂️ Project Folder Structure

Use this structure for the plugin:

woocommerce-blocks-addon/
│
├── includes/
│ ├── class-plugin-loader.php # Handles init hooks, asset loading
│ ├── class-register-blocks.php # Registers all custom Gutenberg blocks
│ ├── class-extend-core-blocks.php # Adds settings to existing WooCommerce blocks
│
├── src/
│ ├── blocks/
│ │ ├── product-grid-pro/
│ │ │ ├── block.json
│ │ │ ├── edit.js
│ │ │ ├── save.js
│ │ │ └── style.scss
│ │ ├── product-carousel/
│ │ ├── category-showcase/
│ │ ├── add-to-cart-pro/
│ │ └── price-badge/
│ │
│ └── utils/
│ ├── controls/
│ │ ├── ColorControl.js
│ │ ├── SpacingControl.js
│ │ └── BorderControl.js
│ └── helpers.js
│
├── build/ # Webpack output (auto-generated)
│
├── woocommerce-blocks-addon.php # Main plugin bootstrap file
├── package.json
├── webpack.config.js
├── readme.txt
└── README.md
