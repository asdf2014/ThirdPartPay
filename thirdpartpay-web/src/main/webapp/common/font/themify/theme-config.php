<?php
$themify_theme_config = array();

$themify_theme_config['folders'] = array(
    'images' => array(
        'src' => 'uploads/'
    )
);

/* 	Settings Panel
/***************************************************************************/
$themify_theme_config['panel']['settings']['tab']['general'] = array(
    'title' => __('General', 'themify'),
    'id' => 'general',
    'custom-module' => array(
        array(
            'title' => __('Favicon', 'themify'),
            'function' => 'favicon',
            'target' => 'uploads/favicon/'
        ),
        array(
            'title' => __('Custom Feed URL', 'themify'),
            'function' => 'custom_feed_url'
        ),
        array(
            'title' => __('Header Code', 'themify'),
            'function' => 'header_html'
        ),
        array(
            'title' => __('Footer Code', 'themify'),
            'function' => 'footer_html'
        ),
        array(
            'title' => __('Search Settings', 'themify'),
            'function' => 'search_settings'
        ),
        array(
            'title' => __('404 Page', 'themify'),
            'function' => 'page_404_settings'
        ),
        array(
            'title' => __('Feed Settings', 'themify'),
            'function' => 'feed_settings'
        ),

    )
);

$themify_theme_config['panel']['settings']['tab']['default_layouts'] = array(
    'title' => __('Default Layouts', 'themify'),
    'id' => 'default_layouts',
    'custom-module' => array(
        array(
            'title' => __('Default Archive Post Layout', 'themify'),
            'function' => 'default_layout'
        ),
        array(
            'title' => __('Default Single Post Layout', 'themify'),
            'function' => 'default_post_layout'
        ),
        array(
            'title' => __('Default Page Layout', 'themify'),
            'function' => 'default_page_layout'
        )
    )
);

$themify_theme_config['panel']['settings']['tab']['theme_settings'] = array(
    'title' => __('Theme Settings', 'themify'),
    'id' => 'theme_settings',
    'custom-module' => array(
        array(
            'title' => __('Responsive Design', 'themify'),
            'function' => 'disable_responsive_design_option'
        ),
        array(
            'title' => __('WordPress Gallery Lightbox', 'themify'),
            'function' => 'gallery_plugins'
        ),
        array(
            'title' => __('Page Navigation', 'themify'),
            'function' => 'entries_navigation'
        ),
        array(
            'title' => __('Exclude RSS Link', 'themify'),
            'function' => 'exclude_rss'
        ),
        array(
            'title' => __('Exclude Search Form', 'themify'),
            'function' => 'exclude_search_form'
        ),
        array(
            'title' => __('Footer Widgets', 'themify'),
            'function' => 'footer_widgets'
        ),
        array(
            'title' => __('Footer Text 1', 'themify'),
            'function' => 'footer_text_left'
        ),
        array(
            'title' => __('Footer Text 2', 'themify'),
            'function' => 'footer_text_right'
        )
    )
);

$themify_theme_config['panel']['settings']['tab']['image_script'] = array(
    'title' => __('Image Script', 'themify'),
    'id' => 'image_script',
    'custom-module' => array(
        array(
            'title' => __('Image Script Settings', 'themify'),
            'function' => 'img_settings'
        )
    )
);

$themify_theme_config['panel']['settings']['tab']['social_links'] = array(
    'title' => __('Social Links', 'themify'),
    'id' => 'social_links',
    'custom-module' => array(
        array(
            'title' => __('Manage Social Links', 'themify'),
            'function' => 'themify_manage_links'
        )
    )
);

/* 	Styling Panel
/***************************************************************************/
$themify_theme_config['panel']['styling']['tab']['background'] = array(
    'title' => __('Background', 'themify'),
    'id' => 'background',
    'element' => array(
        array(
            'title' => __('Body Background', 'themify'),
            'id' => 'body_background',
            'selector' => 'body',
            'module' => array(
                array(
                    'name' => 'image-preview',
                    'src' => 'uploads/bg/'
                ),
                array(
                    'name' => 'background-image',
                    'target' => 'uploads/bg/'
                ),
                'background-color',
                'background-repeat',
                'background-position'
            )
        ),

        array(
            'title' => __('Header Wrap Background', 'themify'),
            'id' => 'header_wrap_background',
            'selector' => '#headerwrap',

            'module' => array(
                array(
                    'name' => 'image-preview',
                    'src' => 'uploads/bg/'
                ),
                array(
                    'name' => 'background-image',
                    'target' => 'uploads/bg/'
                ),
                'background-color',
                'background-repeat',
                'background-position'
            )
        ),

        array(
            'title' => __('Body (Middle) Wrap Background', 'themify'),
            'id' => 'body_wrap_background',
            'selector' => '#body',

            'module' => array(
                array(
                    'name' => 'image-preview',
                    'src' => 'uploads/bg/'
                ),
                array(
                    'name' => 'background-image',
                    'target' => 'uploads/bg/'
                ),
                'background-color',
                'background-repeat',
                'background-position'
            )
        ),

        array(
            'title' => __('Footer Wrap Background', 'themify'),
            'id' => 'footer_wrap_background',
            'selector' => '#footerwrap',

            'module' => array(
                array(
                    'name' => 'image-preview',
                    'src' => 'uploads/bg/'
                ),
                array(
                    'name' => 'background-image',
                    'target' => 'uploads/bg/'
                ),
                'background-color',
                'background-repeat',
                'background-position'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['body_font'] = array(
    'title' => __('Body Font', 'themify'),
    'id' => 'body_font',
    'element' => array(
        array(
            'title' => __('Body Font Styles', 'themify'),
            'id' => 'body_styles',
            'selector' => 'body',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'line-height'
            )
        ),

        array(
            'title' => __('Body Link', 'themify'),
            'id' => 'body_link',
            'selector' => 'a',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Body Hover', 'themify'),
            'id' => 'body_hover',
            'selector' => 'a:hover',
            'module' => array(
                'color',
                'text-decoration'
            )
        )
    )
);

$themify_theme_config['panel']['styling']['tab']['headings'] = array(
    'title' => __('Headings', 'themify'),
    'id' => 'headings',
    'element' => array(
        array(
            'title' => __('Heading 1 (h1)', 'themify'),
            'id' => 'heading_h1',
            'selector' => 'h1',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Heading 2 (h2)', 'themify'),
            'id' => 'heading_h2',
            'selector' => 'h2',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Heading 3 (h3)', 'themify'),
            'id' => 'heading_h3',
            'selector' => 'h3',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Heading 4 (h4)', 'themify'),
            'id' => 'heading_h4',
            'selector' => 'h4',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Heading 5 (h5)', 'themify'),
            'id' => 'heading_h5',
            'selector' => 'h5',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Heading 6 (h6)', 'themify'),
            'id' => 'heading_h6',
            'selector' => 'h6',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration'
            )
        )
    )
);

$themify_theme_config['panel']['styling']['tab']['form_elements'] = array(
    'title' => __('Form Elements', 'themify'),
    'id' => 'form_elements',
    'element' => array(
        array(
            'title' => __('Form Fields (input, textarea, etc.)', 'themify'),
            'id' => 'form_fields_input_textarea',
            'selector' => 'textarea, input[type=text], input[type=password], input[type=search], input[type=email], input[type=url]',
            'module' => array(
                'background-color',
                'border',
                'padding',
                'font-family',
                'color',
                'font-size'
            )
        ),

        array(
            'title' => __('Form Fields :focus', 'themify'),
            'id' => 'form_fields_focus',
            'selector' => 'textarea:focus, input[type=text]:focus, input[type=password]:focus, input[type=search]:focus, input[type=email]:focus, input[type=url]:focus',
            'module' => array(
                'background-color',
                'border',
                'color'
            )
        ),

        array(
            'title' => __('Form Button', 'themify'),
            'id' => 'form_button',
            'selector' => 'input[type=reset], input[type=submit]',
            'module' => array(
                'background-color',
                'border',
                'color'
            )
        ),

        array(
            'title' => __('Form Button Hover', 'themify'),
            'id' => 'form_button_hover',
            'selector' => 'input[type=reset]:hover, input[type=submit]:hover',
            'module' => array(
                'background-color',
                'border',
                'color'
            )
        ),

    )
);

$themify_theme_config['panel']['styling']['tab']['header'] = array(
    'title' => __('Header', 'themify'),
    'id' => 'header',
    'element' => array(
        array(
            'title' => __('Header', 'themify'),
            'id' => 'header',
            'selector' => '#header',
            'module' => array(
                array(
                    'name' => 'image-preview',
                    'src' => 'uploads/bg/'
                ),
                array(
                    'name' => 'background-image',
                    'target' => 'uploads/bg/'
                ),
                'background-color',
                'background-repeat',
                'background-position',
                'divider',
                'color',
                'height',
                'border'
            )
        ),

        array(
            'title' => __('Header Link', 'themify'),
            'id' => 'header_link',
            'selector' => '#header a',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Header Hover', 'themify'),
            'id' => 'header_hover',
            'selector' => '#header a:hover',
            'module' => array(
                'color',
                'text-decoration'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['site_logo'] = array(
    'title' => __('Site Logo', 'themify'),
    'id' => 'site_logo',
    'element' => array(
        array(
            'title' => __('Site Logo', 'themify'),
            'id' => 'site_logo',
            'selector' => '#site-logo a',
            'module' => array(
                array(
                    'name' => 'site_logo',
                    'target' => 'uploads/logo/'
                ),
                'divider',
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'text-transform',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Site Logo Hover', 'themify'),
            'id' => 'site_logo_hover',
            'selector' => '#site-logo a:hover',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Site Logo Position', 'themify'),
            'id' => 'site_logo_position',
            'selector' => '#site-logo',
            'module' => array(
                'position'
            )
        )
    )
);

$themify_theme_config['panel']['styling']['tab']['site_description'] = array(
    'title' => __('Site Description', 'themify'),
    'id' => 'site_description',
    'element' => array(
        array(
            'title' => __('Site Description', 'themify'),
            'id' => 'site_description',
            'selector' => '#site-description',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'text-transform',
                'divider',
                'position'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['main_navigation'] = array(
    'title' => __('Main Navigation', 'themify'),
    'id' => 'main_navigation',
    'element' => array(
        array(
            'title' => __('Main Navigation Position', 'themify'),
            'id' => 'main_navigation_position',
            'selector' => '#main-nav',
            'module' => array(
                'position'
            )
        ),

        array(
            'title' => __('Main Navigation Link', 'themify'),
            'id' => 'main_navigation_link',
            'selector' => '#main-nav a',
            'module' => array(
                'font-family',
                'font-size',
                'color',
                'text-decoration',
                'text-transform',
                'divider',
                'padding',
                'background-color'
            )
        ),

        array(
            'title' => __('Main Navigation Hover', 'themify'),
            'id' => 'main_navigation_hover',
            'selector' => '#main-nav a:hover, #main-nav li:hover > a',
            'module' => array(
                'color',
                'text-decoration',
                'background-color'
            )
        ),

        array(
            'title' => __('Main Navigation Active (current)', 'themify'),
            'id' => 'main_navigation_active',
            'selector' => '#main-nav .current_page_item a, #main-nav .current-menu-item a',
            'module' => array(
                'color',
                'background-color'
            )
        ),

        array(
            'title' => __('Main Navigation Active :hover', 'themify'),
            'id' => 'main_navigation_active_hover',
            'selector' => '#main-nav .current_page_item a:hover, #main-nav .current-menu-item a:hover',
            'module' => array(
                'color',
                'background-color'
            )
        ),

        array(
            'title' => __('Dropdown', 'themify'),
            'id' => 'dropdown',
            'selector' => '#main-nav ul',
            'module' => array(
                'background-color',
                'padding',
                'border'

            )
        ),

        array(
            'title' => __('Dropdown Link', 'themify'),
            'id' => 'dropdown_link',
            'selector' => '#main-nav ul a, #main-nav .current_page_item ul a, #main-nav ul .current_page_item a, #main-nav .current-menu-item ul a, #main-nav ul .current-menu-item a, #main-nav li:hover > ul a',
            'module' => array(
                'font-family',
                'font-size',
                'color',
                'background-color',
                'padding',
                'border'
            )
        ),

        array(
            'title' => __('Dropdown Hover', 'themify'),
            'id' => 'dropdown_hover',
            'selector' => '#main-nav ul a:hover, #main-nav .current_page_item ul a:hover, #main-nav ul .current_page_item a:hover, #main-nav .current-menu-item ul a:hover, #main-nav ul .current-menu-item a:hover, #main-nav li:hover > ul a:hover',
            'module' => array(
                'color',
                'background-color'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['post_title'] = array(
    'title' => __('Post Title', 'themify'),
    'id' => 'post_title',
    'element' => array(
        array(
            'title' => __('Post-Title Font Styles', 'themify'),
            'id' => 'post_title_styles',
            'selector' => '.post-title',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Post-Title Link', 'themify'),
            'id' => 'post_title_link',
            'selector' => '.post-title a',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Post-Title Hover', 'themify'),
            'id' => 'post_title_hover',
            'selector' => '.post-title a:hover',
            'module' => array(
                'color',
                'text-decoration'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['page_title'] = array(
    'title' => __('Page Title', 'themify'),
    'id' => 'page_title',
    'element' => array(
        array(
            'title' => __('Page Title Font Styles', 'themify'),
            'id' => 'page_title_styles',
            'selector' => '.page-title',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform'
            )
        )
    )
);

$themify_theme_config['panel']['styling']['tab']['post_meta'] = array(
    'title' => __('Post Meta', 'themify'),
    'id' => 'post_meta',
    'element' => array(
        array(
            'title' => __('Post Meta Font Styles', 'themify'),
            'id' => 'post_meta_styles',
            'selector' => '.post-meta, .post-meta em',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight'
            )
        ),

        array(
            'title' => __('Post Meta Link', 'themify'),
            'id' => 'post_meta_link',
            'selector' => '.post-meta a',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Post Meta Hover', 'themify'),
            'id' => 'post_meta_hover',
            'selector' => '.post-meta a:hover',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Post Date', 'themify'),
            'id' => 'post_date',
            'selector' => '.post-date',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight'
            )
        ),

        array(
            'title' => __('More Link', 'themify'),
            'id' => 'more_link',
            'selector' => '.more-link',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'divider',
                'background-color',
                'padding',
                'margin',
                'border'
            )
        ),

        array(
            'title' => __('More Link Hover', 'themify'),
            'id' => 'more_link_hover',
            'selector' => '.more-link:hover',
            'module' => array(
                'color',
                'background-color'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['post_navigation'] = array(
    'title' => __('Post Navigation', 'themify'),
    'id' => 'post_navigation',
    'element' => array(
        array(
            'title' => __('Post Navigation (next/prev post link)', 'themify'),
            'id' => 'post_navigation',
            'selector' => '.post-nav',
            'module' => array(
                'border'
            )
        ),

        array(
            'title' => __('Post Navigation Link', 'themify'),
            'id' => 'post_navigation_link',
            'selector' => '.post-nav a',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'line-height',
                'font-weight',
                'font-style',
                'font-variant',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Post Navigation Hover', 'themify'),
            'id' => 'post_navigation_link_hover',
            'selector' => '.post-nav a:hover',
            'module' => array(
                'color',
                'text-decoration'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['sidebar'] = array(
    'title' => __('Sidebar', 'themify'),
    'id' => 'sidebar',
    'element' => array(
        array(
            'title' => __('Sidebar Font Styles', 'themify'),
            'id' => 'sidebar_styles',
            'selector' => '#sidebar',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'line-height'
            )
        ),

        array(
            'title' => __('Sidebar Link', 'themify'),
            'id' => 'sidebar_link',
            'selector' => '#sidebar a',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Sidebar Hover', 'themify'),
            'id' => 'sidebar_hover',
            'selector' => '#sidebar a:hover',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Sidebar Widget Box', 'themify'),
            'id' => 'sidebar_widget_box',
            'selector' => '#sidebar .widget',
            'module' => array(
                'background-color',
                'padding',
                'margin',
                'border'
            )
        ),

        array(
            'title' => __('Sidebar Widget Title', 'themify'),
            'id' => 'sidebar_widget_title',
            'selector' => '#sidebar .widgettitle',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration',
                'divider',
                'border',
                'background-color',
                'padding',
                'margin'
            )
        ),

        array(
            'title' => __('Sidebar Widget List Item (li)', 'themify'),
            'id' => 'sidebar_widget_list_item',
            'selector' => '#sidebar .widget li ',
            'module' => array(
                'background-color',
                'margin',
                'padding',
                'border'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['footer'] = array(
    'title' => __('Footer', 'themify'),
    'id' => 'footer',
    'element' => array(
        array(
            'title' => __('Footer Font Styles', 'themify'),
            'id' => 'footer_styles',
            'selector' => '#footer',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'line-height'
            )
        ),

        array(
            'title' => __('Footer Link', 'themify'),
            'id' => 'footer_link',
            'selector' => '#footer a',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Footer Hover', 'themify'),
            'id' => 'footer_hover',
            'selector' => '#footer a:hover',
            'module' => array(
                'color',
                'text-decoration'
            )
        ),

        array(
            'title' => __('Footer Widget Box', 'themify'),
            'id' => 'footer_widget_box',
            'selector' => '#footer .widget',
            'module' => array(
                'background-color',
                'padding',
                'margin',
                'border'
            )
        ),

        array(
            'title' => __('Footer Widget Title', 'themify'),
            'id' => 'footer_widget_title',
            'selector' => '#footer .widgettitle',
            'module' => array(
                'font-family',
                'color',
                'font-size',
                'font-weight',
                'font-style',
                'font-variant',
                'line-height',
                'text-transform',
                'text-decoration',
                'divider',
                'border',
                'background-color',
                'padding',
                'margin'
            )
        ),

        array(
            'title' => __('Footer Widget List Item (li)', 'themify'),
            'id' => 'footer_widget_list_item',
            'selector' => '#footer .widget li ',
            'module' => array(
                'background-color',
                'margin',
                'padding',
                'border'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['footer_navigation'] = array(
    'title' => __('Footer Navigation', 'themify'),
    'id' => 'footer_navigation',
    'element' => array(
        array(
            'title' => __('Footer Navigation Link', 'themify'),
            'id' => 'footer_navigation_link',
            'selector' => '#footer-nav a',
            'module' => array(
                'font-family',
                'font-size',
                'color',
                'text-decoration',
                'text-transform',
                'divider',
                'padding',
                'background-color'
            )
        ),

        array(
            'title' => __('Footer Navigation Hover', 'themify'),
            'id' => 'footer_navigation_hover',
            'selector' => '#footer-nav a:hover, #footer-nav li:hover > a',
            'module' => array(
                'color',
                'text-decoration',
                'background-color'
            )
        ),

        array(
            'title' => __('Footer Navigation Active (current)', 'themify'),
            'id' => 'footer_navigation_active',
            'selector' => '#footer-nav .current_page_item a, #footer-nav .current-menu-item a',
            'module' => array(
                'color',
                'background-color'
            )
        ),

        array(
            'title' => __('Footer Navigation Active :hover', 'themify'),
            'id' => 'footer_navigation_active_hover',
            'selector' => '#footer-nav .current_page_item a:hover, #footer-nav .current-menu-item a:hover',
            'module' => array(
                'color',
                'background-color'
            )
        )

    )
);

$themify_theme_config['panel']['styling']['tab']['custom_css'] = array(
    'title' => __('Custom CSS', 'themify'),
    'id' => 'custom_css',
    'element' => array(
        array(
            'title' => __('Custom CSS', 'themify'),
            'id' => 'custom_css',
            'selector' => 'html',
            'module' => array(
                'custom_css'
            )
        )
    )
);

?>