/**
 * Glassmorphism Background for Blocks
 * Extends core and Kadence blocks with glassmorphism background options
 */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    ToggleControl,
    RangeControl,
    ColorPicker,
    __experimentalText as Text,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Blocks that support glassmorphism
const SUPPORTED_BLOCKS = [
    // Core blocks
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
    // Kadence blocks
    'kadence/rowlayout',
    'kadence/column',
    'kadence/tabs',
    'kadence/accordion',
    'kadence/infobox',
    'kadence/testimonials',
    'kadence/advancedbtn',
    'kadence/form',
];

/**
 * Add glassmorphism attributes to supported blocks
 */
function addGlassmorphismAttributes( settings, name ) {
    if ( ! SUPPORTED_BLOCKS.includes( name ) ) {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            glassmorphismEnabled: {
                type: 'boolean',
                default: false,
            },
            glassmorphismBlur: {
                type: 'number',
                default: 10,
            },
            glassmorphismOpacity: {
                type: 'number',
                default: 50,
            },
            glassmorphismTint: {
                type: 'string',
                default: 'rgba(255,255,255,0.1)',
            },
            glassmorphismSaturation: {
                type: 'number',
                default: 100,
            },
            glassmorphismBorderOpacity: {
                type: 'number',
                default: 30,
            },
        },
    };
}

addFilter(
    'blocks.registerBlockType',
    'glassmorph-block/add-attributes',
    addGlassmorphismAttributes
);

/**
 * Add glassmorphism controls to the block inspector
 */
const withGlassmorphismControls = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        const { name, attributes, setAttributes } = props;

        if ( ! SUPPORTED_BLOCKS.includes( name ) ) {
            return <BlockEdit { ...props } />;
        }

        const {
            glassmorphismEnabled,
            glassmorphismBlur,
            glassmorphismOpacity,
            glassmorphismTint,
            glassmorphismSaturation,
            glassmorphismBorderOpacity,
        } = attributes;

        return (
            <Fragment>
                <BlockEdit { ...props } />
                <InspectorControls>
                    <PanelBody
                        title={ __( 'Glassmorphism', 'glassmorph-block' ) }
                        initialOpen={ false }
                        className="glassmorph-panel"
                    >
                        <ToggleControl
                            label={ __( 'Enable Glassmorphism', 'glassmorph-block' ) }
                            checked={ glassmorphismEnabled }
                            onChange={ ( value ) =>
                                setAttributes( { glassmorphismEnabled: value } )
                            }
                        />

                        { glassmorphismEnabled && (
                            <Fragment>
                                <RangeControl
                                    label={ __( 'Blur Amount', 'glassmorph-block' ) }
                                    value={ glassmorphismBlur }
                                    onChange={ ( value ) =>
                                        setAttributes( { glassmorphismBlur: value } )
                                    }
                                    min={ 0 }
                                    max={ 50 }
                                    step={ 1 }
                                    help={ __( 'Controls the blur intensity (px)', 'glassmorph-block' ) }
                                />

                                <RangeControl
                                    label={ __( 'Background Opacity', 'glassmorph-block' ) }
                                    value={ glassmorphismOpacity }
                                    onChange={ ( value ) =>
                                        setAttributes( { glassmorphismOpacity: value } )
                                    }
                                    min={ 0 }
                                    max={ 100 }
                                    step={ 1 }
                                    help={ __( 'Transparency of the glass layer (%)', 'glassmorph-block' ) }
                                />

                                <RangeControl
                                    label={ __( 'Saturation', 'glassmorph-block' ) }
                                    value={ glassmorphismSaturation }
                                    onChange={ ( value ) =>
                                        setAttributes( { glassmorphismSaturation: value } )
                                    }
                                    min={ 0 }
                                    max={ 200 }
                                    step={ 1 }
                                    help={ __( 'Color saturation of backdrop (%)', 'glassmorph-block' ) }
                                />

                                <RangeControl
                                    label={ __( 'Border Opacity', 'glassmorph-block' ) }
                                    value={ glassmorphismBorderOpacity }
                                    onChange={ ( value ) =>
                                        setAttributes( { glassmorphismBorderOpacity: value } )
                                    }
                                    min={ 0 }
                                    max={ 100 }
                                    step={ 1 }
                                    help={ __( 'Opacity of the glass border (%)', 'glassmorph-block' ) }
                                />

                                <Text
                                    as="label"
                                    style={ {
                                        display: 'block',
                                        marginBottom: '8px',
                                        marginTop: '16px',
                                    } }
                                >
                                    { __( 'Tint Color', 'glassmorph-block' ) }
                                </Text>
                                <ColorPicker
                                    color={ glassmorphismTint }
                                    onChange={ ( value ) =>
                                        setAttributes( { glassmorphismTint: value } )
                                    }
                                    enableAlpha={ true }
                                />
                            </Fragment>
                        ) }
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withGlassmorphismControls' );

addFilter(
    'editor.BlockEdit',
    'glassmorph-block/with-controls',
    withGlassmorphismControls
);

/**
 * Add glassmorphism styles to the block wrapper in editor
 */
const withGlassmorphismStyles = createHigherOrderComponent( ( BlockListBlock ) => {
    return ( props ) => {
        const { name, attributes } = props;

        if ( ! SUPPORTED_BLOCKS.includes( name ) ) {
            return <BlockListBlock { ...props } />;
        }

        const {
            glassmorphismEnabled,
            glassmorphismBlur,
            glassmorphismOpacity,
            glassmorphismTint,
            glassmorphismSaturation,
            glassmorphismBorderOpacity,
        } = attributes;

        if ( ! glassmorphismEnabled ) {
            return <BlockListBlock { ...props } />;
        }

        const glassStyles = {
            '--glass-blur': `${ glassmorphismBlur }px`,
            '--glass-opacity': glassmorphismOpacity / 100,
            '--glass-tint': glassmorphismTint,
            '--glass-saturation': glassmorphismSaturation / 100,
            '--glass-border-opacity': glassmorphismBorderOpacity / 100,
        };

        return (
            <BlockListBlock
                { ...props }
                wrapperProps={ {
                    ...props.wrapperProps,
                    style: {
                        ...( props.wrapperProps?.style || {} ),
                        ...glassStyles,
                    },
                    className: `${ props.wrapperProps?.className || '' } has-glassmorphism`.trim(),
                } }
            />
        );
    };
}, 'withGlassmorphismStyles' );

addFilter(
    'editor.BlockListBlock',
    'glassmorph-block/with-styles',
    withGlassmorphismStyles
);
