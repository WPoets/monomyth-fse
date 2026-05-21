# Monomyth FSE Theme - Awesome Enterprise Integration Guide

This theme is designed to work seamlessly with the Awesome No Code Platform (Awesome Enterprise). Template parts like header, footer, and sidebar are rendered dynamically from Gutenberg blocks that call Awesome Enterprise services.

## Overview

The theme provides:
1. **Awesome Block** - A Gutenberg block that renders any Awesome Enterprise module/template
2. **Dynamic Template Parts** - Header, footer, and sidebar that use the Awesome Block
3. **Fallback Template Parts** - Standard WordPress blocks for when AE isn't configured

## Quick Start

### Step 1: Install Prerequisites
1. Install and activate the [Awesome No Code Platform](https://github.com/WPoets/awesome-no-code-platform)
2. Install and activate the Monomyth FSE theme

### Step 2: Create Your Template Parts in Awesome Enterprise

Create a collection called `theme_parts` (or use any name you prefer) and add these modules:

#### Header Module (`theme_parts.header`)

```
[templates.add main]
<header class="custom-header">
    <div class="header-container">
        <div class="site-branding">
            <a href="/" class="site-logo">Your Site Name</a>
        </div>
        <nav class="main-navigation">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About</a></li>
                <li><a href="/services">Services</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>
[/templates.add]

[templates.main /]
```

#### Footer Module (`theme_parts.footer`)

```
[templates.add main]
<footer class="custom-footer">
    <div class="footer-container">
        <div class="footer-columns">
            <div class="footer-column">
                <h4>About Us</h4>
                <p>Your site description here.</p>
            </div>
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/terms">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; [date.create 'now' m.date_format='Y' /] Your Site. All rights reserved.</p>
        </div>
    </div>
</footer>
[/templates.add]

[templates.main /]
```

#### Sidebar Module (`theme_parts.sidebar`)

```
[templates.add main]
<aside class="custom-sidebar">
    <div class="widget">
        <h3>Search</h3>
        <form class="search-form" action="/" method="get">
            <input type="text" name="s" placeholder="Search...">
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="widget">
        <h3>Categories</h3>
        <ul>
            [loop.@cat wp_categories]
                <li><a href="{@cat.item.link}">{@cat.item.name}</a></li>
            [/loop.@cat]
        </ul>
    </div>
</aside>
[/templates.add]

[templates.main /]
```

### Step 3: Configure the Theme

The template parts are pre-configured to look for:
- `theme_parts.header` for the header
- `theme_parts.footer` for the footer
- `theme_parts.sidebar` for the sidebar

You can change these in the Site Editor:
1. Go to **Appearance → Editor**
2. Click on **Template Parts**
3. Select the template part you want to edit
4. Click on the Awesome Block and change the **Service Path** in the sidebar

## Using the Awesome Block

### In the Site Editor

1. Open any template or template part
2. Add a new block and search for "Awesome Block"
3. Configure the block:
   - **Service Path**: The AE service to call (e.g., `my_collection.my_module`)
   - **Parameters**: Additional parameters (e.g., `param1=value1 param2=value2`)
   - **Wrapper Element**: HTML tag to wrap the output (div, section, header, footer, etc.)
   - **Wrapper CSS Class**: Additional CSS classes
   - **Fallback Content**: Content to show if the service returns nothing

### Service Path Format

The service path follows Awesome Enterprise naming conventions:

```
collection_name.module_name              → Runs the module (or its 'main' template)
collection_name.module_name.template     → Runs a specific template within the module
```

Examples:
- `theme_parts.header` - Runs the header module
- `theme_parts.footer.compact` - Runs the 'compact' template in the footer module
- `my_app.products.list` - Runs the 'list' template in the products module

### Passing Parameters

You can pass parameters to the service:

```
param1=value1 param2=value2 count=int:10 active=bool:true
```

These become available in the module/template as:
```
[template.param1 /]
[template.param2 /]
[template.count /]
```

## Template Parts Reference

### Dynamic Template Parts (Awesome Enterprise)

| Template Part | Default Service Path | Description |
|--------------|---------------------|-------------|
| `header.html` | `theme_parts.header` | Site header |
| `footer.html` | `theme_parts.footer` | Site footer |
| `sidebar.html` | `theme_parts.sidebar` | Sidebar/aside |

### Fallback Template Parts (Standard WordPress)

| Template Part | Description |
|--------------|-------------|
| `header-fallback.html` | WordPress-native header with logo, title, navigation |
| `footer-fallback.html` | WordPress-native footer with columns, copyright |
| `sidebar-fallback.html` | WordPress-native sidebar with search, categories, recent posts |

## Switching Between Dynamic and Fallback

In the Site Editor:
1. Edit a template (e.g., index.html)
2. Click on the header/footer template part reference
3. In the sidebar, click "Replace" or delete and add a new template part
4. Choose between "Header (Awesome Enterprise)" or "Header (Standard)"

## Advanced Usage

### Dynamic Service Path from Environment

You can use curly braces to reference environment variables:

```
Service Path: my_collection.{template.header_type}
```

### Multiple Headers for Different Pages

Create different header modules and use them in custom templates:

1. Create `theme_parts.header_home` for the homepage
2. Create `theme_parts.header_inner` for inner pages
3. Create custom templates in the Site Editor that reference different services

### Conditional Content

In your Awesome Enterprise modules, use conditions:

```
[if.equal lhs='{request.page_type}' rhs='home']
    <!-- Homepage header -->
[/if.equal]
[if.else]
    <!-- Standard header -->
[/if.else]
```

## Styling

The Awesome Block adds these CSS classes automatically:

- `.wp-block-monomyth-awesome-block` - Base class
- `.ae-service--{service-name}` - Service-specific class (dots replaced with dashes)
- `.alignwide` / `.alignfull` - Alignment classes
- Custom classes from the "Wrapper CSS Class" setting

Example CSS:
```css
.ae-service--theme_parts-header {
    /* Styles for the header */
}

.ae-service--theme_parts-footer {
    /* Styles for the footer */
}
```

## Troubleshooting

### Block Shows "Service returned no content"

1. Verify Awesome Enterprise is active
2. Check the service path is correct
3. Ensure the module/template exists
4. Check for syntax errors in your Awesome Enterprise code

### Block Shows "Configure this block"

The service path is empty. Click on the block and enter a service path in the sidebar.

### Styles Not Applying

1. Check if your Awesome Enterprise output has the expected HTML structure
2. Add CSS classes in the "Wrapper CSS Class" field
3. Use the browser inspector to debug

## Support

For issues with:
- **Theme**: Check the theme documentation or open an issue
- **Awesome Enterprise**: Refer to the [Awesome No Code Platform documentation](https://github.com/WPoets/awesome-no-code-platform)
