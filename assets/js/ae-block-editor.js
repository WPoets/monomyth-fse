/**
 * Awesome Enterprise Blocks - Gutenberg Editor Script
 * 
 * Registers three blocks for dynamic content rendering from Awesome Enterprise:
 * 
 * 1. Awesome Block - Generic service caller for any AE module/template
 * 2. AE Content Layout - Dynamic single content resolution (like single.php)
 *    Tries {post_type}-{module} first, then falls back to {module}
 * 3. AE Archive Layout - Dynamic archive content resolution (like archive.php)
 *
 * @package Monomyth_FSE
 * @version 1.0.0
 */

( function( blocks, element, blockEditor, components, serverSideRender, i18n ) {
    'use strict';

    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps = blockEditor.useBlockProps;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var TextareaControl = components.TextareaControl;
    var ToggleControl = components.ToggleControl;
    var Placeholder = components.Placeholder;
    var Spinner = components.Spinner;
    var Notice = components.Notice;
    var __ = i18n.__;
    var ServerSideRender = serverSideRender;

    // ========================================================================
    // CONFIGURATION & SHARED RESOURCES
    // ========================================================================

    // Get localized data from PHP
    var blockData = window.monomythAEBlock || {};
    var isAwesomeActive = blockData.isAwesomeActive || false;
    var i18nStrings = blockData.i18n || {};

    // Shared wrapper element options
    var wrapperOptions = [
        { label: __( 'Div', 'monomyth-fse' ), value: 'div' },
        { label: __( 'Section', 'monomyth-fse' ), value: 'section' },
        { label: __( 'Article', 'monomyth-fse' ), value: 'article' },
        { label: __( 'Header', 'monomyth-fse' ), value: 'header' },
        { label: __( 'Footer', 'monomyth-fse' ), value: 'footer' },
        { label: __( 'Nav', 'monomyth-fse' ), value: 'nav' },
        { label: __( 'Aside', 'monomyth-fse' ), value: 'aside' },
        { label: __( 'Main', 'monomyth-fse' ), value: 'main' },
        { label: __( 'Span', 'monomyth-fse' ), value: 'span' }
    ];

    // Content wrapper options (subset for content blocks)
    var contentWrapperOptions = [
        { label: __( 'Article', 'monomyth-fse' ), value: 'article' },
        { label: __( 'Div', 'monomyth-fse' ), value: 'div' },
        { label: __( 'Section', 'monomyth-fse' ), value: 'section' },
        { label: __( 'Main', 'monomyth-fse' ), value: 'main' }
    ];

    // Archive wrapper options
    var archiveWrapperOptions = [
        { label: __( 'Div', 'monomyth-fse' ), value: 'div' },
        { label: __( 'Section', 'monomyth-fse' ), value: 'section' },
        { label: __( 'Main', 'monomyth-fse' ), value: 'main' }
    ];

    // ========================================================================
    // BLOCK ICONS
    // ========================================================================

    // Awesome Block icon - layered hexagon
    var awesomeIcon = el( 'svg', { 
        width: 24, 
        height: 24, 
        viewBox: '0 0 24 24',
        xmlns: 'http://www.w3.org/2000/svg'
    },
        el( 'path', { 
            d: 'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5',
            fill: 'none',
            stroke: 'currentColor',
            strokeWidth: 2,
            strokeLinecap: 'round',
            strokeLinejoin: 'round'
        })
    );

    // Content Layout icon - document with sections
    var layoutIcon = el( 'svg', { 
        width: 24, 
        height: 24, 
        viewBox: '0 0 24 24',
        xmlns: 'http://www.w3.org/2000/svg'
    },
        el( 'rect', { 
            x: 3, y: 3, width: 18, height: 18, rx: 2, 
            fill: 'none', stroke: 'currentColor', strokeWidth: 2 
        }),
        el( 'line', { 
            x1: 3, y1: 9, x2: 21, y2: 9, 
            stroke: 'currentColor', strokeWidth: 2 
        }),
        el( 'line', { 
            x1: 9, y1: 9, x2: 9, y2: 21, 
            stroke: 'currentColor', strokeWidth: 2 
        })
    );

    // Archive Layout icon - grid
    var archiveIcon = el( 'svg', { 
        width: 24, 
        height: 24, 
        viewBox: '0 0 24 24',
        xmlns: 'http://www.w3.org/2000/svg'
    },
        el( 'rect', { x: 3, y: 3, width: 7, height: 7, fill: 'currentColor' }),
        el( 'rect', { x: 14, y: 3, width: 7, height: 7, fill: 'currentColor' }),
        el( 'rect', { x: 3, y: 14, width: 7, height: 7, fill: 'currentColor' }),
        el( 'rect', { x: 14, y: 14, width: 7, height: 7, fill: 'currentColor' })
    );

    // ========================================================================
    // SHARED COMPONENTS
    // ========================================================================

    /**
     * Loading placeholder for ServerSideRender
     */
    function LoadingPlaceholder() {
        return el( 'div', { className: 'ae-block-loading' },
            el( Spinner, {} ),
            el( 'span', {}, __( 'Loading preview...', 'monomyth-fse' ) )
        );
    }

    /**
     * Error placeholder for ServerSideRender
     */
    function ErrorPlaceholder() {
        return el( 'div', { className: 'ae-block-error' },
            el( 'strong', {}, __( 'Error', 'monomyth-fse' ) ),
            el( 'p', {}, __( 'There was an error loading the block preview. Please check the service path and try again.', 'monomyth-fse' ) )
        );
    }

    /**
     * Empty response placeholder factory
     * @param {string} serviceName - The service/module name to display
     */
    function createEmptyPlaceholder( serviceName ) {
        return function() {
            return el( 'div', { className: 'ae-block-empty' },
                el( 'p', {}, __( 'No content returned from:', 'monomyth-fse' ) + ' ' + serviceName ),
                el( 'p', { className: 'ae-block-help' }, 
                    __( 'Make sure the service path is correct and Awesome Enterprise is active.', 'monomyth-fse' ) 
                )
            );
        };
    }

    /**
     * Notice component for when Awesome Enterprise is not active
     */
    function AENotActiveNotice() {
        if ( isAwesomeActive ) {
            return null;
        }
        return el( Notice, {
            status: 'warning',
            isDismissible: false,
            className: 'ae-block-notice'
        }, __( 'Awesome Enterprise is not active. Content will not render until the plugin is installed and activated.', 'monomyth-fse' ) );
    }

    /**
     * Resolution order info box for Inspector
     * @param {string} baseModule - The base module name
     * @param {boolean} usePostTypePrefix - Whether post type prefix is enabled
     */
    function ResolutionOrderInfo( baseModule, usePostTypePrefix ) {
        var items = [];
        
        if ( usePostTypePrefix ) {
            items.push( el( 'li', { key: 'pt-app' }, '{post_type}-' + baseModule + ' (' + __( 'app collection', 'monomyth-fse' ) + ')' ) );
            items.push( el( 'li', { key: 'pt-core' }, '{post_type}-' + baseModule + ' (' + __( 'core', 'monomyth-fse' ) + ')' ) );
        }
        items.push( el( 'li', { key: 'base-app' }, baseModule + ' (' + __( 'app collection', 'monomyth-fse' ) + ')' ) );
        items.push( el( 'li', { key: 'base-core' }, baseModule + ' (' + __( 'core', 'monomyth-fse' ) + ')' ) );

        return el( 'div', { 
            className: 'ae-resolution-info', 
            style: { 
                marginTop: '16px', 
                padding: '12px', 
                background: '#f0f0f0', 
                borderRadius: '4px',
                fontSize: '12px'
            } 
        },
            el( 'strong', {}, __( 'Resolution Order:', 'monomyth-fse' ) ),
            el( 'ol', { style: { margin: '8px 0 0 16px', padding: 0 } }, items )
        );
    }

    /**
     * Visual Editor Preview for Content/Archive Layout blocks
     * Shows a clear visual representation of what the block does
     */
    function LayoutBlockPreview( props ) {
        var type = props.type || 'content';
        var baseModule = props.baseModule || '';
        var usePostTypePrefix = props.usePostTypePrefix !== false;
        var icon = props.icon;
        
        var bgColor = type === 'content' ? '#10b981' : '#6366f1';
        var typeLabel = type === 'content' 
            ? __( 'AE Content Layout', 'monomyth-fse' )
            : __( 'AE Archive Layout', 'monomyth-fse' );
        var typeDesc = type === 'content'
            ? __( 'Dynamic single post/page content', 'monomyth-fse' )
            : __( 'Dynamic archive/listing content', 'monomyth-fse' );

        // Build resolution steps for preview
        var resolutionSteps = [];
        if ( usePostTypePrefix ) {
            resolutionSteps.push( '{post_type}-' + baseModule );
        }
        resolutionSteps.push( baseModule );

        return el( 'div', {
            className: 'ae-layout-preview',
            style: {
                background: 'linear-gradient(135deg, ' + bgColor + ' 0%, ' + bgColor + 'dd 100%)',
                borderRadius: '8px',
                padding: '24px',
                color: '#ffffff',
                textAlign: 'center',
                minHeight: '120px',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '12px'
            }
        },
            // Icon and title row
            el( 'div', { 
                style: { 
                    display: 'flex', 
                    alignItems: 'center', 
                    gap: '8px',
                    marginBottom: '4px'
                } 
            },
                el( 'span', { 
                    style: { 
                        background: 'rgba(255,255,255,0.2)', 
                        borderRadius: '6px', 
                        padding: '6px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                    } 
                }, icon ),
                el( 'strong', { 
                    style: { 
                        fontSize: '16px',
                        fontWeight: '600'
                    } 
                }, typeLabel )
            ),
            
            // Description
            el( 'div', { 
                style: { 
                    fontSize: '13px', 
                    opacity: '0.9',
                    marginBottom: '8px'
                } 
            }, typeDesc ),
            
            // Module info box
            el( 'div', {
                style: {
                    background: 'rgba(255,255,255,0.15)',
                    borderRadius: '6px',
                    padding: '12px 16px',
                    width: '100%',
                    maxWidth: '400px'
                }
            },
                el( 'div', { 
                    style: { 
                        fontSize: '11px', 
                        textTransform: 'uppercase', 
                        letterSpacing: '0.5px',
                        opacity: '0.8',
                        marginBottom: '6px'
                    } 
                }, __( 'Module Resolution', 'monomyth-fse' ) ),
                
                // Resolution steps
                el( 'div', { 
                    style: { 
                        fontFamily: 'monospace', 
                        fontSize: '13px',
                        display: 'flex',
                        flexDirection: 'column',
                        gap: '4px'
                    } 
                },
                    resolutionSteps.map( function( step, index ) {
                        return el( 'div', { 
                            key: index,
                            style: {
                                display: 'flex',
                                alignItems: 'center',
                                gap: '6px'
                            }
                        },
                            el( 'span', { 
                                style: { 
                                    background: 'rgba(255,255,255,0.3)',
                                    borderRadius: '3px',
                                    padding: '1px 6px',
                                    fontSize: '10px'
                                } 
                            }, index + 1 ),
                            el( 'span', {}, step )
                        );
                    })
                )
            ),
            
            // Help text
            el( 'div', { 
                style: { 
                    fontSize: '11px', 
                    opacity: '0.7',
                    marginTop: '8px'
                } 
            }, __( 'Configure in block settings →', 'monomyth-fse' ) )
        );
    }

    // ========================================================================
    // BLOCK 1: AWESOME BLOCK - Generic Service Caller
    // ========================================================================

    registerBlockType( 'monomyth/awesome-block', {
        title: __( 'Awesome Block', 'monomyth-fse' ),
        description: __( 'Render content from any Awesome Enterprise module or template. Enter a service path like "collection.module.template" to display dynamic content.', 'monomyth-fse' ),
        category: 'awesome-enterprise',
        icon: awesomeIcon,
        keywords: [ 
            __( 'awesome', 'monomyth-fse' ),
            __( 'enterprise', 'monomyth-fse' ),
            __( 'dynamic', 'monomyth-fse' ),
            __( 'module', 'monomyth-fse' ),
            __( 'template', 'monomyth-fse' ),
            __( 'shortcode', 'monomyth-fse' )
        ],
        supports: {
            html: false,
            align: [ 'wide', 'full' ],
            className: true,
            anchor: true
        },
        attributes: {
            service: { type: 'string', default: '' },
            wrapper: { type: 'string', default: 'div' },
            wrapperClass: { type: 'string', default: '' },
            params: { type: 'string', default: '' },
            fallback: { type: 'string', default: '' },
            align: { type: 'string', default: '' },
            className: { type: 'string', default: '' },
            anchor: { type: 'string', default: '' }
        },

        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var service = attributes.service;
            var wrapper = attributes.wrapper;
            var wrapperClass = attributes.wrapperClass;
            var params = attributes.params;
            var fallback = attributes.fallback;
            var blockProps = useBlockProps();

            // Inspector controls (sidebar)
            var inspectorControls = el( InspectorControls, {},
                // Warning if AE not active
                AENotActiveNotice(),

                // Service Settings Panel
                el( PanelBody, { 
                    title: __( 'Service Settings', 'monomyth-fse' ),
                    initialOpen: true
                },
                    el( TextControl, {
                        label: __( 'Service Path', 'monomyth-fse' ),
                        help: blockData.helpText || __( 'Enter the Awesome Enterprise service path (e.g., collection.module.template or theme_parts.header)', 'monomyth-fse' ),
                        value: service,
                        onChange: function( value ) {
                            setAttributes( { service: value } );
                        },
                        placeholder: blockData.placeholder || 'collection.module.template'
                    } ),

                    el( TextControl, {
                        label: __( 'Parameters', 'monomyth-fse' ),
                        help: __( 'Additional parameters to pass to the service (e.g., param1=value1 param2=value2)', 'monomyth-fse' ),
                        value: params,
                        onChange: function( value ) {
                            setAttributes( { params: value } );
                        },
                        placeholder: 'param1=value1 param2=value2'
                    } )
                ),

                // Wrapper Settings Panel
                el( PanelBody, { 
                    title: __( 'Wrapper Settings', 'monomyth-fse' ),
                    initialOpen: false
                },
                    el( SelectControl, {
                        label: __( 'Wrapper Element', 'monomyth-fse' ),
                        help: __( 'Choose the HTML element that wraps the rendered content.', 'monomyth-fse' ),
                        value: wrapper,
                        options: wrapperOptions,
                        onChange: function( value ) {
                            setAttributes( { wrapper: value } );
                        }
                    } ),

                    el( TextControl, {
                        label: __( 'Wrapper CSS Class', 'monomyth-fse' ),
                        help: __( 'Additional CSS class(es) for the wrapper element.', 'monomyth-fse' ),
                        value: wrapperClass,
                        onChange: function( value ) {
                            setAttributes( { wrapperClass: value } );
                        },
                        placeholder: 'my-custom-class'
                    } )
                ),

                // Fallback Settings Panel
                el( PanelBody, { 
                    title: __( 'Fallback Content', 'monomyth-fse' ),
                    initialOpen: false
                },
                    el( TextareaControl, {
                        label: __( 'Fallback Content', 'monomyth-fse' ),
                        help: __( 'Content to display if the service returns nothing or is unavailable. Supports shortcodes.', 'monomyth-fse' ),
                        value: fallback,
                        onChange: function( value ) {
                            setAttributes( { fallback: value } );
                        },
                        placeholder: __( 'Enter fallback content or shortcode...', 'monomyth-fse' )
                    } )
                )
            );

            // Main block content
            var blockContent;

            if ( ! service ) {
                // Show placeholder when no service is set
                blockContent = el( Placeholder, {
                    icon: awesomeIcon,
                    label: __( 'Awesome Block', 'monomyth-fse' ),
                    instructions: __( 'Enter an Awesome Enterprise service path to render dynamic content. Configure in the block settings sidebar.', 'monomyth-fse' )
                },
                    el( TextControl, {
                        label: __( 'Service Path', 'monomyth-fse' ),
                        value: service,
                        onChange: function( value ) {
                            setAttributes( { service: value } );
                        },
                        placeholder: 'collection.module.template'
                    } )
                );
            } else {
                // Show styled preview with service info
                blockContent = el( 'div', {
                    className: 'ae-awesome-block-preview',
                    style: {
                        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        borderRadius: '8px',
                        padding: '20px',
                        color: '#ffffff',
                        textAlign: 'center'
                    }
                },
                    el( 'div', { 
                        style: { 
                            display: 'flex', 
                            alignItems: 'center', 
                            justifyContent: 'center',
                            gap: '8px',
                            marginBottom: '8px'
                        } 
                    },
                        el( 'span', { 
                            style: { 
                                background: 'rgba(255,255,255,0.2)', 
                                borderRadius: '6px', 
                                padding: '6px',
                                display: 'flex'
                            } 
                        }, awesomeIcon ),
                        el( 'strong', {}, __( 'Awesome Block', 'monomyth-fse' ) )
                    ),
                    el( 'div', {
                        style: {
                            background: 'rgba(255,255,255,0.15)',
                            borderRadius: '4px',
                            padding: '8px 12px',
                            fontFamily: 'monospace',
                            fontSize: '13px',
                            marginTop: '8px'
                        }
                    }, service ),
                    params && el( 'div', {
                        style: {
                            fontSize: '11px',
                            opacity: '0.8',
                            marginTop: '6px'
                        }
                    }, __( 'Params:', 'monomyth-fse' ) + ' ' + params )
                );
            }

            return el( 'div', blockProps,
                inspectorControls,
                blockContent
            );
        },

        save: function() {
            // Server-side rendered block returns null
            return null;
        }
    } );

    // ========================================================================
    // BLOCK 2: AE CONTENT LAYOUT - Dynamic Single Content (like single.php)
    // ========================================================================

    registerBlockType( 'monomyth/ae-content-layout', {
        title: __( 'AE Content Layout', 'monomyth-fse' ),
        description: __( 'Dynamically resolves content layout based on post type, just like classic single.php. Tries {post_type}-{module} first, then falls back to {module}.', 'monomyth-fse' ),
        category: 'awesome-enterprise',
        icon: layoutIcon,
        keywords: [ 
            __( 'single', 'monomyth-fse' ),
            __( 'content', 'monomyth-fse' ),
            __( 'layout', 'monomyth-fse' ),
            __( 'dynamic', 'monomyth-fse' ),
            __( 'post', 'monomyth-fse' ),
            __( 'page', 'monomyth-fse' )
        ],
        supports: {
            html: false,
            align: [ 'wide', 'full' ],
            className: true
        },
        attributes: {
            baseModule: { type: 'string', default: 'single-content-layout' },
            usePostTypePrefix: { type: 'boolean', default: true },
            wrapper: { type: 'string', default: 'article' },
            wrapperClass: { type: 'string', default: 'ae-content-layout' },
            params: { type: 'string', default: '' },
            fallbackContent: { type: 'string', default: '' },
            align: { type: 'string', default: '' },
            className: { type: 'string', default: '' }
        },

        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var baseModule = attributes.baseModule;
            var usePostTypePrefix = attributes.usePostTypePrefix;
            var wrapper = attributes.wrapper;
            var wrapperClass = attributes.wrapperClass;
            var params = attributes.params;
            var fallbackContent = attributes.fallbackContent;
            var blockProps = useBlockProps();

            // Inspector controls
            var inspectorControls = el( InspectorControls, {},
                // Warning if AE not active
                AENotActiveNotice(),

                // Layout Resolution Panel
                el( PanelBody, { 
                    title: __( 'Layout Resolution', 'monomyth-fse' ),
                    initialOpen: true
                },
                    el( TextControl, {
                        label: __( 'Base Module Name', 'monomyth-fse' ),
                        help: __( 'The module name to look for in Awesome Enterprise (e.g., single-content-layout)', 'monomyth-fse' ),
                        value: baseModule,
                        onChange: function( value ) {
                            setAttributes( { baseModule: value } );
                        },
                        placeholder: 'single-content-layout'
                    } ),

                    el( ToggleControl, {
                        label: __( 'Prefix with post type', 'monomyth-fse' ),
                        help: __( 'When enabled, first tries {post_type}-{module} (e.g., product-single-content-layout), then falls back to {module}', 'monomyth-fse' ),
                        checked: usePostTypePrefix,
                        onChange: function( value ) {
                            setAttributes( { usePostTypePrefix: value } );
                        }
                    } ),

                    // Resolution order info
                    ResolutionOrderInfo( baseModule, usePostTypePrefix ),

                    el( TextControl, {
                        label: __( 'Additional Parameters', 'monomyth-fse' ),
                        help: __( 'Extra parameters to pass to the module', 'monomyth-fse' ),
                        value: params,
                        onChange: function( value ) {
                            setAttributes( { params: value } );
                        },
                        placeholder: 'param1=value1'
                    } )
                ),

                // Wrapper Settings Panel
                el( PanelBody, { 
                    title: __( 'Wrapper Settings', 'monomyth-fse' ),
                    initialOpen: false
                },
                    el( SelectControl, {
                        label: __( 'Wrapper Element', 'monomyth-fse' ),
                        help: __( 'HTML element to wrap the content output.', 'monomyth-fse' ),
                        value: wrapper,
                        options: contentWrapperOptions,
                        onChange: function( value ) {
                            setAttributes( { wrapper: value } );
                        }
                    } ),

                    el( TextControl, {
                        label: __( 'Wrapper CSS Class', 'monomyth-fse' ),
                        help: __( 'CSS class(es) for the wrapper element.', 'monomyth-fse' ),
                        value: wrapperClass,
                        onChange: function( value ) {
                            setAttributes( { wrapperClass: value } );
                        },
                        placeholder: 'ae-content-layout'
                    } )
                ),

                // Fallback Settings Panel
                el( PanelBody, { 
                    title: __( 'Fallback Content', 'monomyth-fse' ),
                    initialOpen: false
                },
                    el( TextareaControl, {
                        label: __( 'Fallback Content', 'monomyth-fse' ),
                        help: __( 'Content to display if no module is found. Supports shortcodes or HTML.', 'monomyth-fse' ),
                        value: fallbackContent,
                        onChange: function( value ) {
                            setAttributes( { fallbackContent: value } );
                        },
                        placeholder: __( 'Enter fallback content...', 'monomyth-fse' )
                    } )
                )
            );

            // Visual preview (not server-side render)
            var blockContent = el( LayoutBlockPreview, {
                type: 'content',
                baseModule: baseModule,
                usePostTypePrefix: usePostTypePrefix,
                icon: layoutIcon
            } );

            return el( 'div', blockProps,
                inspectorControls,
                blockContent
            );
        },

        save: function() {
            return null;
        }
    } );

    // ========================================================================
    // BLOCK 3: AE ARCHIVE LAYOUT - Dynamic Archive Content (like archive.php)
    // ========================================================================

    registerBlockType( 'monomyth/ae-archive-layout', {
        title: __( 'AE Archive Layout', 'monomyth-fse' ),
        description: __( 'Dynamically resolves archive layout based on post type or taxonomy, just like classic archive.php. Ideal for category, tag, and custom post type archives.', 'monomyth-fse' ),
        category: 'awesome-enterprise',
        icon: archiveIcon,
        keywords: [ 
            __( 'archive', 'monomyth-fse' ),
            __( 'category', 'monomyth-fse' ),
            __( 'taxonomy', 'monomyth-fse' ),
            __( 'list', 'monomyth-fse' ),
            __( 'index', 'monomyth-fse' ),
            __( 'loop', 'monomyth-fse' )
        ],
        supports: {
            html: false,
            align: [ 'wide', 'full' ],
            className: true
        },
        attributes: {
            baseModule: { type: 'string', default: 'archive-content-layout' },
            usePostTypePrefix: { type: 'boolean', default: true },
            wrapper: { type: 'string', default: 'div' },
            wrapperClass: { type: 'string', default: 'ae-archive-layout' },
            params: { type: 'string', default: '' },
            fallbackContent: { type: 'string', default: '' },
            align: { type: 'string', default: '' },
            className: { type: 'string', default: '' }
        },

        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var baseModule = attributes.baseModule;
            var usePostTypePrefix = attributes.usePostTypePrefix;
            var wrapper = attributes.wrapper;
            var wrapperClass = attributes.wrapperClass;
            var params = attributes.params;
            var fallbackContent = attributes.fallbackContent;
            var blockProps = useBlockProps();

            // Inspector controls
            var inspectorControls = el( InspectorControls, {},
                // Warning if AE not active
                AENotActiveNotice(),

                // Layout Resolution Panel
                el( PanelBody, { 
                    title: __( 'Layout Resolution', 'monomyth-fse' ),
                    initialOpen: true
                },
                    el( TextControl, {
                        label: __( 'Base Module Name', 'monomyth-fse' ),
                        help: __( 'The module name to look for in Awesome Enterprise (e.g., archive-content-layout)', 'monomyth-fse' ),
                        value: baseModule,
                        onChange: function( value ) {
                            setAttributes( { baseModule: value } );
                        },
                        placeholder: 'archive-content-layout'
                    } ),

                    el( ToggleControl, {
                        label: __( 'Prefix with post type', 'monomyth-fse' ),
                        help: __( 'When enabled, first tries {post_type}-{module} (e.g., product-archive-content-layout), then falls back to {module}', 'monomyth-fse' ),
                        checked: usePostTypePrefix,
                        onChange: function( value ) {
                            setAttributes( { usePostTypePrefix: value } );
                        }
                    } ),

                    // Resolution order info
                    ResolutionOrderInfo( baseModule, usePostTypePrefix ),

                    el( TextControl, {
                        label: __( 'Additional Parameters', 'monomyth-fse' ),
                        help: __( 'Extra parameters to pass to the module', 'monomyth-fse' ),
                        value: params,
                        onChange: function( value ) {
                            setAttributes( { params: value } );
                        },
                        placeholder: 'param1=value1'
                    } )
                ),

                // Wrapper Settings Panel
                el( PanelBody, { 
                    title: __( 'Wrapper Settings', 'monomyth-fse' ),
                    initialOpen: false
                },
                    el( SelectControl, {
                        label: __( 'Wrapper Element', 'monomyth-fse' ),
                        help: __( 'HTML element to wrap the archive output.', 'monomyth-fse' ),
                        value: wrapper,
                        options: archiveWrapperOptions,
                        onChange: function( value ) {
                            setAttributes( { wrapper: value } );
                        }
                    } ),

                    el( TextControl, {
                        label: __( 'Wrapper CSS Class', 'monomyth-fse' ),
                        help: __( 'CSS class(es) for the wrapper element.', 'monomyth-fse' ),
                        value: wrapperClass,
                        onChange: function( value ) {
                            setAttributes( { wrapperClass: value } );
                        },
                        placeholder: 'ae-archive-layout'
                    } )
                ),

                // Fallback Settings Panel
                el( PanelBody, { 
                    title: __( 'Fallback Content', 'monomyth-fse' ),
                    initialOpen: false
                },
                    el( TextareaControl, {
                        label: __( 'Fallback Content', 'monomyth-fse' ),
                        help: __( 'Content to display if no module is found. Supports shortcodes or HTML.', 'monomyth-fse' ),
                        value: fallbackContent,
                        onChange: function( value ) {
                            setAttributes( { fallbackContent: value } );
                        },
                        placeholder: __( 'Enter fallback content...', 'monomyth-fse' )
                    } )
                )
            );

            // Visual preview (not server-side render)
            var blockContent = el( LayoutBlockPreview, {
                type: 'archive',
                baseModule: baseModule,
                usePostTypePrefix: usePostTypePrefix,
                icon: archiveIcon
            } );

            return el( 'div', blockProps,
                inspectorControls,
                blockContent
            );
        },

        save: function() {
            return null;
        }
    } );

} )( 
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.serverSideRender,
    window.wp.i18n
);
