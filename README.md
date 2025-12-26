# Glassmorphism Background for Blocks

A WordPress plugin that adds glassmorphism-style background options to Gutenberg core blocks and Kadence Blocks.

## Features

- **6 Configurable Settings:**
  - Enable/Disable toggle
  - Blur Amount (0-50px)
  - Background Opacity (0-100%)
  - Background Tint Color (with alpha)
  - Saturation (0-200%)
  - Border Opacity (0-100%)

- **Supports 18 Blocks:**
  - 10 Core Blocks: Group, Columns, Column, Cover, Media & Text, Buttons, Button, Quote, Pullquote, Table
  - 8 Kadence Blocks: Row Layout, Section, Tabs, Accordion, Info Box, Testimonials, Advanced Buttons, Advanced Form

- **Graceful Fallback:** When the plugin is disabled, content remains visible and settings are preserved.

## Installation

1. Upload the `glassmorph-block` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open any supported block in the editor and find the "Glassmorphism" panel in the sidebar

## Development

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Development with watch mode
npm run start
```

## Requirements

- WordPress 6.0+
- PHP 7.4+
