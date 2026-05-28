=== Monomyth FSE ===
Contributors: wpoets
Tags: full-site-editing, block-patterns, block-styles, custom-colors, custom-logo, custom-menu, editor-style, featured-images, wide-blocks, blank-canvas, awesome-enterprise
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A minimal Full Site Editing (FSE) theme with dynamic template parts powered by Awesome Enterprise.

== Description ==

Monomyth FSE is a minimal Full Site Editing (FSE) theme designed to work seamlessly with the Awesome No Code Platform. **Template parts (header, footer, sidebar) are rendered dynamically from Gutenberg blocks** that call Awesome Enterprise services.

= Key Feature: Dynamic Template Parts =

Unlike traditional themes with static template parts, Monomyth FSE uses the **Awesome Block** - a custom Gutenberg block that renders content from Awesome Enterprise modules. This means:

* **Header** - Comes from `theme_parts.header` (or any AE service you configure)
* **Footer** - Comes from `theme_parts.footer` (or any AE service you configure)  
* **Sidebar** - Comes from `theme_parts.sidebar` (or any AE service you configure)

You can build your entire site structure using Awesome Enterprise shortcode-based modules!

= Features =

* **Awesome Block** - A Gutenberg block that renders any Awesome Enterprise module/template
* **Dynamic Template Parts** - Header, footer, sidebar powered by AE services
* **Fallback Template Parts** - Standard WordPress blocks when AE isn't configured
* **Full Site Editing Ready** - Built for WordPress Site Editor
* **Minimal & Clean** - A blank canvas for your custom designs
* **Performance Focused** - Lightweight and optimized

= Awesome Block Usage =

Add the Awesome Block to any template and configure:

* **Service Path** - The AE service to call (e.g., `my_collection.my_module.my_template`)
* **Parameters** - Pass parameters to the service
* **Wrapper Element** - Choose the HTML wrapper (div, header, footer, nav, etc.)
* **Fallback Content** - Content to display when service returns nothing

= Templates Included =

* Index (blog listing)
* Single Post
* Page
* Archive
* 404 Error
* Search Results
* Blank (for custom pages)
* Page (No Title)

= Template Parts =

**Dynamic (Awesome Enterprise):**
* Header - calls `theme_parts.header`
* Footer - calls `theme_parts.footer`
* Sidebar - calls `theme_parts.sidebar`

**Fallback (Standard WordPress):**
* Header (Standard) - WordPress blocks
* Footer (Standard) - WordPress blocks  
* Sidebar (Standard) - WordPress blocks

= Requirements =

* WordPress 6.0 or higher
* PHP 7.4 or higher
* Awesome No Code Platform (optional but recommended)

== Installation ==

1. Upload the `monomyth-fse` folder to `/wp-content/themes/`
2. Activate the theme through Appearance > Themes
3. Install and activate the Awesome No Code Platform
4. Create your AE modules for `theme_parts.header`, `theme_parts.footer`, etc.
5. Navigate to Appearance > Editor to customize your site

See `docs/AWESOME-ENTERPRISE-INTEGRATION.md` for detailed setup instructions.

== Frequently Asked Questions ==

= Does this theme require the Awesome No Code Platform? =

No, Monomyth FSE works as a standalone WordPress theme. Without Awesome Enterprise, you can use the fallback template parts (Header Standard, Footer Standard, etc.) which use native WordPress blocks. However, the full power comes when using it with the Awesome No Code Platform.

= How do I configure which AE service is called? =

In the Site Editor (Appearance > Editor), click on any Awesome Block and configure the "Service Path" in the block settings sidebar. Example: `my_collection.header_module`

= What if my AE service returns nothing? =

The Awesome Block supports fallback content. Configure the "Fallback Content" field in the block settings, or use the fallback template parts.

= Can I use different headers on different pages? =

Yes! Create multiple header modules in Awesome Enterprise, then:
1. Create custom templates in the Site Editor
2. Use Awesome Blocks with different service paths

= How do I pass parameters to my AE modules? =

In the Awesome Block settings, use the "Parameters" field:
`param1=value1 param2=value2 count=int:10`

These become available in your template as `[template.param1 /]`, etc.

== Changelog ==

= 1.0.0 =
* Initial release
* Awesome Block for rendering AE services
* Dynamic template parts (header, footer, sidebar)
* Fallback template parts with standard WordPress blocks
* Full Site Editor support

== Resources ==

* Theme developed by WPoets (https://wpoets.com)
* Awesome No Code Platform: https://github.com/WPoets/awesome-no-code-platform
* Integration documentation: See `docs/AWESOME-ENTERPRISE-INTEGRATION.md`

== Credits ==

* Built with WordPress Full Site Editing
* System fonts used for optimal performance

== License ==

Monomyth FSE WordPress Theme, Copyright 2025 WPoets
Monomyth FSE is distributed under the terms of the GNU GPL v2 or later.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
