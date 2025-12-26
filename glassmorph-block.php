<?php
/**
 * Plugin Name: Glassmorphism Background for Blocks
 * Plugin URI: https://github.com/exzenter/glassmorphism-blocks
 * Description: Adds glassmorphism-style background options to Gutenberg core blocks and Kadence Blocks.
 * Version: 1.0.0
 * Author: EX·ZENT
 * License: GPL-2.0-or-later
 * Text Domain: glassmorph-block
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GLASSMORPH_VERSION', '1.0.0' );
define( 'GLASSMORPH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GLASSMORPH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Convert any color format to rgba with given opacity
 * Supports: hex (#fff, #ffffff, #ffffffff), rgb(), rgba()
 */
function glassmorph_color_to_rgba( $color, $opacity ) {
    $color = trim( $color );
    
    // Already rgba - extract and multiply alpha
    if ( preg_match( '/rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/', $color, $m ) ) {
        $r = intval( $m[1] );
        $g = intval( $m[2] );
        $b = intval( $m[3] );
        $a = isset( $m[4] ) ? floatval( $m[4] ) : 1;
        $final_opacity = $a * $opacity;
        return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, round( $final_opacity, 2 ) );
    }
    
    // Hex color
    if ( preg_match( '/^#?([a-fA-F0-9]{3,8})$/', $color, $m ) ) {
        $hex = $m[1];
        $len = strlen( $hex );
        
        if ( $len === 3 ) {
            // #fff
            $r = hexdec( $hex[0] . $hex[0] );
            $g = hexdec( $hex[1] . $hex[1] );
            $b = hexdec( $hex[2] . $hex[2] );
            $a = 1;
        } elseif ( $len === 4 ) {
            // #fffa
            $r = hexdec( $hex[0] . $hex[0] );
            $g = hexdec( $hex[1] . $hex[1] );
            $b = hexdec( $hex[2] . $hex[2] );
            $a = hexdec( $hex[3] . $hex[3] ) / 255;
        } elseif ( $len === 6 ) {
            // #ffffff
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );
            $a = 1;
        } elseif ( $len === 8 ) {
            // #ffffffaa
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );
            $a = hexdec( substr( $hex, 6, 2 ) ) / 255;
        } else {
            // Fallback
            return sprintf( 'rgba(255, 255, 255, %s)', round( $opacity, 2 ) );
        }
        
        $final_opacity = $a * $opacity;
        return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, round( $final_opacity, 2 ) );
    }
    
    // Unknown format - return with opacity applied to white
    return sprintf( 'rgba(255, 255, 255, %s)', round( $opacity, 2 ) );
}

/**
 * Enqueue editor assets
 */
function glassmorph_enqueue_editor_assets() {
    $asset_file = GLASSMORPH_PLUGIN_DIR . 'build/index.asset.php';
    
    if ( ! file_exists( $asset_file ) ) {
        return;
    }
    
    $asset = include $asset_file;
    
    wp_enqueue_script(
        'glassmorph-editor',
        GLASSMORPH_PLUGIN_URL . 'build/index.js',
        $asset['dependencies'],
        $asset['version'],
        true
    );
    
    wp_enqueue_style(
        'glassmorph-editor',
        GLASSMORPH_PLUGIN_URL . 'src/editor.css',
        array(),
        GLASSMORPH_VERSION
    );
}
add_action( 'enqueue_block_editor_assets', 'glassmorph_enqueue_editor_assets' );

/**
 * Enqueue frontend assets
 */
function glassmorph_enqueue_frontend_assets() {
    wp_enqueue_style(
        'glassmorph-frontend',
        GLASSMORPH_PLUGIN_URL . 'src/frontend.css',
        array(),
        GLASSMORPH_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'glassmorph_enqueue_frontend_assets' );
add_action( 'enqueue_block_editor_assets', 'glassmorph_enqueue_frontend_assets' );

/**
 * Filter block content to add glassmorphism styles
 */
function glassmorph_render_block( $block_content, $block ) {
    // List of supported blocks
    $supported_blocks = array(
        'core/paragraph',
        'core/heading',
        'core/group',
        'core/columns',
        'core/column',
        'core/cover',
        'core/media-text',
        'core/buttons',
        'core/button',
        'core/quote',
        'core/pullquote',
        'core/table',
        'kadence/rowlayout',
        'kadence/column',
        'kadence/tabs',
        'kadence/accordion',
        'kadence/infobox',
        'kadence/testimonials',
        'kadence/advancedbtn',
        'kadence/form',
    );
    
    if ( ! in_array( $block['blockName'], $supported_blocks, true ) ) {
        return $block_content;
    }
    
    // Check if glassmorphism is enabled
    if ( empty( $block['attrs']['glassmorphismEnabled'] ) ) {
        return $block_content;
    }
    
    $attrs = $block['attrs'];
    
    // Generate unique ID for this block instance
    $glass_id = 'glass-' . wp_unique_id();
    
    // Get glassmorphism values with defaults
    $blur = isset( $attrs['glassmorphismBlur'] ) ? floatval( $attrs['glassmorphismBlur'] ) : 10;
    $opacity = isset( $attrs['glassmorphismOpacity'] ) ? floatval( $attrs['glassmorphismOpacity'] ) / 100 : 0.5;
    $tint = isset( $attrs['glassmorphismTint'] ) ? $attrs['glassmorphismTint'] : 'rgba(255,255,255,0.1)';
    $saturation = isset( $attrs['glassmorphismSaturation'] ) ? floatval( $attrs['glassmorphismSaturation'] ) : 100;
    $borderOpacity = isset( $attrs['glassmorphismBorderOpacity'] ) ? floatval( $attrs['glassmorphismBorderOpacity'] ) / 100 : 0.3;
    
    // Convert tint color to rgba with opacity baked in
    $tint_rgba = glassmorph_color_to_rgba( $tint, $opacity );
    
    // Build scoped style block
    // IMPORTANT: backdrop-filter goes on MAIN element (to blur what's behind it)
    // ::before is used ONLY for the tint overlay (sits on top of blur, below content)
    // NOTE: Do NOT use "isolation: isolate" - it creates a stacking context that blocks blur
    $scoped_style = sprintf(
        '<style>
            #%1$s {
                position: relative;
                backdrop-filter: blur(%3$spx) saturate(%4$s%%);
                -webkit-backdrop-filter: blur(%3$spx) saturate(%4$s%%);
            }
            #%1$s::before {
                content: "";
                position: absolute;
                inset: 0;
                z-index: -1;
                pointer-events: none;
                background: %2$s;
                border: 1px solid rgba(255, 255, 255, %5$s);
                border-radius: inherit;
            }
        </style>',
        esc_attr( $glass_id ),
        esc_attr( $tint_rgba ),
        esc_attr( $blur ),
        esc_attr( $saturation ),
        esc_attr( $borderOpacity )
    );
    
    // Add ID to first element
    if ( preg_match( '/<([a-z][a-z0-9]*)\s+/i', $block_content, $tag_match ) ) {
        // Check if element already has an id
        if ( preg_match( '/\s+id=["\'][^"\']*["\']/', $block_content ) ) {
            // Replace existing id
            $block_content = preg_replace(
                '/\s+id=["\'][^"\']*["\']/',
                ' id="' . esc_attr( $glass_id ) . '"',
                $block_content,
                1
            );
        } else {
            // Add id after tag name
            $block_content = preg_replace(
                '/<([a-z][a-z0-9]*)\s+/i',
                '<$1 id="' . esc_attr( $glass_id ) . '" ',
                $block_content,
                1
            );
        }
    }
    
    // Add glassmorphism class
    if ( strpos( $block_content, 'class="' ) !== false ) {
        $block_content = preg_replace(
            '/class="([^"]*)"/',
            'class="$1 has-glassmorphism"',
            $block_content,
            1
        );
    } elseif ( strpos( $block_content, "class='" ) !== false ) {
        $block_content = preg_replace(
            "/class='([^']*)'/",
            "class='$1 has-glassmorphism'",
            $block_content,
            1
        );
    }
    
    // Prepend scoped style to block content
    return $scoped_style . $block_content;
}
add_filter( 'render_block', 'glassmorph_render_block', 10, 2 );
