<?php
/**
 * Plugin Name: Glassmorphism Background for Blocks
 * Plugin URI: https://github.com/your-repo/glassmorph-block
 * Description: Adds glassmorphism-style background options to Gutenberg core blocks and Kadence Blocks.
 * Version: 1.0.0
 * Author: Your Name
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
    
    // Get glassmorphism values with defaults
    $blur = isset( $attrs['glassmorphismBlur'] ) ? floatval( $attrs['glassmorphismBlur'] ) : 10;
    $opacity = isset( $attrs['glassmorphismOpacity'] ) ? floatval( $attrs['glassmorphismOpacity'] ) : 50;
    $tint = isset( $attrs['glassmorphismTint'] ) ? $attrs['glassmorphismTint'] : 'rgba(255,255,255,0.1)';
    $saturation = isset( $attrs['glassmorphismSaturation'] ) ? floatval( $attrs['glassmorphismSaturation'] ) : 100;
    $borderOpacity = isset( $attrs['glassmorphismBorderOpacity'] ) ? floatval( $attrs['glassmorphismBorderOpacity'] ) : 30;
    
    // Build inline style
    $style = sprintf(
        '--glass-blur: %spx; --glass-opacity: %s; --glass-tint: %s; --glass-saturation: %s; --glass-border-opacity: %s;',
        esc_attr( $blur ),
        esc_attr( $opacity / 100 ),
        esc_attr( $tint ),
        esc_attr( $saturation / 100 ),
        esc_attr( $borderOpacity / 100 )
    );
    
    // Add glassmorphism class and inline styles
    if ( strpos( $block_content, 'class="' ) !== false ) {
        $block_content = preg_replace(
            '/class="([^"]*)"/',
            'class="$1 has-glassmorphism" style="' . $style . '"',
            $block_content,
            1
        );
    } elseif ( strpos( $block_content, "class='" ) !== false ) {
        $block_content = preg_replace(
            "/class='([^']*)'/",
            "class='$1 has-glassmorphism' style='" . $style . "'",
            $block_content,
            1
        );
    } else {
        // Add class to first tag
        $block_content = preg_replace(
            '/<([a-z][a-z0-9]*)/i',
            '<$1 class="has-glassmorphism" style="' . $style . '"',
            $block_content,
            1
        );
    }
    
    return $block_content;
}
add_filter( 'render_block', 'glassmorph_render_block', 10, 2 );
