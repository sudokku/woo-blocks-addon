## Woo Blocks Addon – Frontend Styleguide

This styleguide defines how we style **all Woo Blocks Addon Gutenberg blocks** so they:

- **Look “WooCommerce‑native” by default**
- **Inherit as much as possible from the active theme**
- **Remain easy to override and extend** (no hard locks via `!important`, deep selectors, etc.)

Use this document as a reference when creating or updating any block in `src/blocks`.

---

### 1. Class naming and structure

- **Prefix for all custom classes**
  - Use the prefix **`wcba-`** (WooCommerce Blocks Addon).
  - Example block root class: `wcba-advanced-product-card`.

- **BEM‑style naming inside each block**
  - **Block**: `wcba-advanced-product-card`
  - **Element**: `wcba-advanced-product-card__title`, `wcba-advanced-product-card__price`, `wcba-advanced-product-card__image-wrapper`
  - **Modifier**: `wcba-advanced-product-card--compact`, `wcba-advanced-product-card__badge--sale`

- **Block wrappers**
  - Every block should have **one main wrapper** with the block’s base class:
    - Editor: applied via `edit.js` and/or `block.json` `className`.
    - Frontend: applied in `render.php` / `save.js`.
  - If we need integration with core/Woo styles, we can **add extra classes** on the wrapper (e.g. `wc-block-grid__product`) but **never rely solely on those** – always keep the `wcba-` root.

---

### 2. Styling layers and responsibility

We separate styling into **three layers**, from most to least theme‑controlled:

- **Layer 1 – Theme base**
  - Typography, base colors, link styles, headings, body background, etc.
  - We **do not reset** these inside our blocks unless strictly necessary.
  - Use semantic HTML (`h*`, `p`, `ul`, `a`, `button`) so theme styles apply naturally.

- **Layer 2 – Layout (our responsibility)**
  - Structure (grid/flex), card layout, alignment, spacing between components, responsive behavior.
  - Implemented with `wcba-` classes and **no visual assumptions about brand**.
  - Examples: product card columns, image aspect ratio, gap between title and price.

- **Layer 3 – Skin (our defaults, themable)**
  - Card backgrounds, borders, subtle shadows, hover effects, badge shapes, etc.
  - Implemented using **CSS custom properties with Woo‑like defaults** so themes can override easily.

---

### 3. CSS variables and theme integration

- **General rule**
  - Always try to use **WordPress + Woo presets first**, then **our own variables as a thin layer**, then **hardcoded values only as a last fallback**.

- **Using WordPress presets**
  - Use Global Styles / `theme.json` tokens where available:
    - Colors: `var(--wp--preset--color--primary)`, `var(--wp--preset--color--foreground)`, `var(--wp--preset--color--background)`
    - Typography: `var(--wp--preset--font-size--small)`, `var(--wp--preset--font-size--medium)` etc.
  - Always include **sensible fallbacks** that match Woo defaults if possible:

```css
.wcba-advanced-product-card {
  color: var(--wp--preset--color--foreground, #1d1d1d);
  background-color: var(--wp--preset--color--background, #ffffff);
}
```

- **Our block‑scoped variables**
  - Define custom properties on the **block root** only:

```css
.wcba-advanced-product-card {
  /* Spacing & layout */
  --wcba-card-padding: var(--wp--custom--wcba-card-padding, 1.5rem);
  --wcba-card-gap: var(--wp--custom--wcba-card-gap, 0.75rem);

  /* Visual skin (Woo‑like defaults) */
  --wcba-card-bg: var(--wp--preset--color--background, #ffffff);
  --wcba-card-border-color: var(--wp--preset--color--secondary, #e5e5e5);
  --wcba-card-radius: var(--wp--custom--radius--small, 4px);
  --wcba-card-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
}
```

  - Use those variables inside the block rather than repeating values:

```css
.wcba-advanced-product-card {
  padding: var(--wcba-card-padding);
  background-color: var(--wcba-card-bg);
  border-radius: var(--wcba-card-radius);
  border: 1px solid var(--wcba-card-border-color);
  box-shadow: var(--wcba-card-shadow);
}
```

  - Themes (or site owners) can override variables by targeting the wrapper:

```css
.wcba-advanced-product-card.is-style-minimal {
  --wcba-card-bg: transparent;
  --wcba-card-border-color: transparent;
  --wcba-card-shadow: none;
}
```

---

### 4. Block styles and customization

- **Use block styles rather than multiple similar blocks**
  - Register styles like:
    - `woo-default` – WooCommerce‑like, opinionated card.
    - `minimal` – nearly unskinned, relies on theme for most visuals.
  - These appear as `.is-style-woo-default` / `.is-style-minimal` on the root block element.

- **Combine block styles with variables**
  - Use the `.is-style-*` classes to tweak variables and small layout differences rather than rewriting entire rule sets.

- **Rely on block supports where possible**
  - In `block.json`, enable:
    - **`supports.color`** for text/background/link colors.
    - **`supports.typography`** for font size, line height, etc.
    - **`supports.spacing`** for margin/padding controls.
    - **`supports.border`** for border color/width/radius.
  - Prefer these **built‑in controls** to custom inspector panels when they cover the use case.

---

### 5. CSS authoring rules

- **No `!important` unless absolutely necessary**
  - Blocks should be overridable by:
    - Theme `theme.json` / Global Styles
    - Theme stylesheets
    - Custom CSS added by users
  - If you feel forced to use `!important`, consider:
    - Simplifying or shortening selectors
    - Avoiding conflicts with Woo core classes

- **Selector scope**
  - Always start with the block root:
    - ✅ `.wcba-advanced-product-card__title`
    - ✅ `.wcba-advanced-product-card .wcba-advanced-product-card__price`
    - ❌ `body .woocommerce .products .wcba-advanced-product-card__price`
  - Avoid type‑only selectors that might hit unrelated content (`.woocommerce a` etc.) in this plugin.

- **Editor vs frontend**
  - Styles should work **in both editor and frontend**:
    - Use block stylesheets registered with `register_block_type` so they are loaded in both contexts.
    - Only add editor‑only tweaks (e.g. outlines, placeholder visuals) to editor‑specific files.

---

### 6. WooCommerce & core re‑use

- **Re‑use WooCommerce and core classes carefully**
  - It’s OK to add Woo/WP classes in addition to our own to benefit from existing styles (e.g. `wc-block-grid__product`, `wp-block-button__link`), but:
    - Never depend solely on them for layout.
    - Avoid styling those classes globally from this plugin.

- **Follow WooCommerce patterns where helpful**
  - Match general patterns from core WooCommerce blocks:
    - Title above price
    - Metadata grouping
    - Button placement and size hierarchy
  - This keeps our blocks visually coherent with “native” Woo components, even across themes.

---

### 7. When introducing new blocks

For every new block:

- **Name**
  - Choose a clear root class: `wcba-{block-name}`.

- **Structure**
  - Define key elements with BEM (`__title`, `__image`, `__price`, `__meta`, etc.).

- **Variables**
  - Declare all block‑specific custom properties on the root.
  - Prefer WordPress preset tokens as the first value, with Woo‑like fallbacks.

- **Styles**
  - Implement layout and base skin without `!important`.
  - Provide at least two block styles when it makes sense:
    - A Woo‑like default
    - A minimal/theme‑leaning variant

Following these guidelines should give us **WooCommerce‑native, theme‑friendly, and highly customizable** blocks across the entire addon.


