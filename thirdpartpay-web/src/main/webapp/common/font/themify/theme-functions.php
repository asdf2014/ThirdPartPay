<?php


/*
To add custom PHP functions to the theme, create a new 'custom-functions.php' file in the theme folder. 
They will be added to the theme automatically.
*/

/* 	Enqueue Stylesheets and Scripts
/***************************************************************************/
add_action('wp_enqueue_scripts', 'themify_theme_enqueue_scripts', 11);
function themify_theme_enqueue_scripts()
{

    ///////////////////
    //Enqueue styles
    ///////////////////

    //Themify base styling
    wp_enqueue_style('theme-style', get_stylesheet_uri(), array(), wp_get_theme()->display('Version'));

    //Themify Media Queries CSS
    wp_enqueue_style('themify-media-queries', THEME_URI . '/media-queries.css');


    //Google Web Fonts embedding
    wp_enqueue_style('google-fonts', themify_https_esc('http://fonts.googleapis.com/css') . '?family=Old+Standard+TT:400,400italic,700&subset=latin,latin-ext');

    ///////////////////
    //Enqueue scripts
    ///////////////////

    //Themify internal scripts
    wp_enqueue_script('theme-script', THEME_URI . '/js/themify.script.js', array('jquery'), false, true);

    //Inject variable values in gallery script
    wp_localize_script('theme-script', 'themifyScript', array(
            'lightbox' => themify_lightbox_vars_init(),
            'lightboxContext' => apply_filters('themify_lightbox_context', '#pagewrap')
        )
    );

    //WordPress internal script to move the comment box to the right place when replying to a user
    if (is_single() || is_page()) wp_enqueue_script('comment-reply');

}

/**
 * Add JavaScript files if IE version is lower than 9
 */
function themify_ie_enhancements()
{
    echo '
	<!-- media-queries.js -->
	<!--[if lt IE 9]>
		<script src="' . THEME_URI . '/js/respond.js"></script>
	<![endif]-->
	
	<!-- html5.js -->
	<!--[if lt IE 9]>
		<script src="' . themify_https_esc('http://html5shim.googlecode.com/svn/trunk/html5.js') . '"></script>
	<![endif]-->
	';
}

add_action('wp_head', 'themify_ie_enhancements');

/**
 * Add viewport tag for responsive layouts
 */
function themify_viewport_tag()
{
    echo "\n" . '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">' . "\n";
}

add_action('wp_head', 'themify_viewport_tag');

/**
 * Make IE behave like a standards-compliant browser
 */
function themify_ie_standards_compliant()
{
    echo '
	<!--[if lt IE 9]>
	<script src="' . themify_https_esc('http://s3.amazonaws.com/nwapi/nwmatcher/nwmatcher-1.2.5-min.js') . '"></script>
	<script type="text/javascript" src="' . themify_https_esc('http://cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js') . '"></script> 
	<![endif]-->
	';
}

add_action('wp_head', 'themify_ie_standards_compliant');

/* Custom Write Panels
/***************************************************************************/

///////////////////////////////////////
// Setup Write Panel Options
///////////////////////////////////////

// Post Meta Box Options
$post_meta_box_options = array(
    // Layout
    array(
        "name" => "layout",
        "title" => __('Sidebar Option', 'themify'),
        "description" => "",
        "type" => "layout",
        'show_title' => true,
        "meta" => array(
            array("value" => "default", "img" => "images/layout-icons/default.png", "selected" => true, 'title' => __('Default', 'themify')),
            array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
            array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
            array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify'))
        )
    ),
    // Content Width
    array(
        'name' => 'content_width',
        'title' => __('Content Width', 'themify'),
        'description' => '',
        'type' => 'layout',
        'show_title' => true,
        'meta' => array(
            array(
                'value' => 'default_width',
                'img' => 'themify/img/default.png',
                'selected' => true,
                'title' => __('Default', 'themify')
            ),
            array(
                'value' => 'full_width',
                'img' => 'themify/img/fullwidth.png',
                'title' => __('Fullwidth', 'themify')
            )
        )
    ),
    // Post Image
    array(
        "name" => "post_image",
        "title" => __('Featured Image', 'themify'),
        "description" => '',
        "type" => "image",
        "meta" => array()
    ),
    // Featured Image Size
    array(
        'name' => 'feature_size',
        'title' => __('Image Size', 'themify'),
        'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerated</a>', 'themify'),
        'type' => 'featimgdropdown'
    ),
    // Image Width
    array(
        "name" => "image_width",
        "title" => __('Image Width', 'themify'),
        "description" => "",
        "type" => "textbox",
        "meta" => array("size" => "small")
    ),
    // Image Height
    array(
        "name" => "image_height",
        "title" => __('Image Height', 'themify'),
        "description" => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'),
        "type" => "textbox",
        "meta" => array("size" => "small")
    ),
    // Hide Post Title
    array(
        "name" => "hide_post_title",
        "title" => __('Hide Post Title', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Unlink Post Title
    array(
        "name" => "unlink_post_title",
        "title" => __('Unlink Post Title', 'themify'),
        "description" => __('Unlink post title (it will display the post title without link)', 'themify'),
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Hide Post Meta
    array(
        "name" => "hide_post_meta",
        "title" => __('Hide Post Meta', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Hide Post Date
    array(
        "name" => "hide_post_date",
        "title" => __('Hide Post Date', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Hide Post Image
    array(
        "name" => "hide_post_image",
        "title" => __('Hide Featured Image', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Unlink Post Image
    array(
        "name" => "unlink_post_image",
        "title" => __('Unlink Featured Image', 'themify'),
        "description" => __('Display the Featured Image without link', 'themify'),
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Video URL
    array(
        'name' => 'video_url',
        'title' => __('Video URL', 'themify'),
        'description' => __('Video embed URL such as YouTube or Vimeo video url (<a href="http://themify.me/docs/video-embeds">details</a>).', 'themify'),
        'type' => 'textbox',
        'meta' => array()
    ),
    // External Link
    array(
        "name" => "external_link",
        "title" => __('External Link', 'themify'),
        "description" => __('Link Featured Image and Post Title to external URL', 'themify'),
        "type" => "textbox",
        "meta" => array()
    ),
    // Lightbox Link + Zoom icon
    themify_lightbox_link_field()
);


// Page Meta Box Options
$page_meta_box_options = array(
    // Page Layout
    array(
        "name" => "page_layout",
        "title" => __('Sidebar Option', 'themify'),
        "description" => "",
        "type" => "layout",
        'show_title' => true,
        "meta" => array(
            array("value" => "default", "img" => "images/layout-icons/default.png", "selected" => true, 'title' => __('Default', 'themify')),
            array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
            array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
            array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify'))
        )
    ),
    // Content Width
    array(
        'name' => 'content_width',
        'title' => __('Content Width', 'themify'),
        'description' => '',
        'type' => 'layout',
        'show_title' => true,
        'meta' => array(
            array(
                'value' => 'default_width',
                'img' => 'themify/img/default.png',
                'selected' => true,
                'title' => __('Default', 'themify')
            ),
            array(
                'value' => 'full_width',
                'img' => 'themify/img/fullwidth.png',
                'title' => __('Fullwidth', 'themify')
            )
        )
    ),
    // Hide page title
    array(
        "name" => "hide_page_title",
        "title" => __('Hide Page Title', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Custom menu for page
    array(
        'name' => 'custom_menu',
        'title' => __('Custom Menu', 'themify'),
        'description' => '',
        'type' => 'dropdown',
        'meta' => themify_get_available_menus(),
    ),
);

// Query Post Meta Box Options
$query_post_meta_box_options = array(
    // Notice
    array(
        'name' => '_query_posts_notice',
        'title' => '',
        'description' => '',
        'type' => 'separator',
        'meta' => array(
            'html' => '<div class="themify-info-link">' . sprintf(__('<a href="%s">Query Posts</a> allows you to query WordPress posts from any category on the page. To use it, select a Query Category.', 'themify'), 'http://themify.me/docs/query-posts') . '</div>'
        ),
    ),
    // Query Category
    array(
        "name" => "query_category",
        "title" => __('Query Category', 'themify'),
        "description" => __('Select a category or enter multiple category IDs (eg. 2,5,6). Enter 0 to display all category.', 'themify'),
        "type" => "query_category",
        "meta" => array()
    ),
    // Descending or Ascending Order for Posts
    array(
        'name' => 'order',
        'title' => __('Order', 'themify'),
        'description' => '',
        'type' => 'dropdown',
        'meta' => array(
            array('name' => __('Descending', 'themify'), 'value' => 'desc', 'selected' => true),
            array('name' => __('Ascending', 'themify'), 'value' => 'asc')
        )
    ),
    // Criteria to Order By
    array(
        'name' => 'orderby',
        'title' => __('Order By', 'themify'),
        'description' => '',
        'type' => 'dropdown',
        'meta' => array(
            array('name' => __('Date', 'themify'), 'value' => 'content', 'selected' => true),
            array('name' => __('Random', 'themify'), 'value' => 'rand'),
            array('name' => __('Author', 'themify'), 'value' => 'author'),
            array('name' => __('Post Title', 'themify'), 'value' => 'title'),
            array('name' => __('Comments Number', 'themify'), 'value' => 'comment_count'),
            array('name' => __('Modified Date', 'themify'), 'value' => 'modified'),
            array('name' => __('Post Slug', 'themify'), 'value' => 'name'),
            array('name' => __('Post ID', 'themify'), 'value' => 'ID')
        )
    ),
    // Section Categories
    array(
        "name" => "section_categories",
        "title" => __('Section Categories', 'themify'),
        "description" => __('Display multiple query categories separately', 'themify'),
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Post Layout
    array(
        "name" => "layout",
        "title" => __('Query Post Layout', 'themify'),
        "description" => "",
        "type" => "layout",
        'show_title' => true,
        "meta" => array(
            array('value' => 'list-post', 'img' => 'images/layout-icons/list-post.png', 'selected' => true, 'title' => __('List Post', 'themify')),
            array('value' => 'grid4', 'img' => 'images/layout-icons/grid4.png', 'title' => __('Grid 4', 'themify')),
            array('value' => 'grid3', 'img' => 'images/layout-icons/grid3.png', 'title' => __('Grid 3', 'themify')),
            array('value' => 'grid2', 'img' => 'images/layout-icons/grid2.png', 'title' => __('Grid 2', 'themify')),
            array('value' => 'list-large-image', 'img' => 'images/layout-icons/list-large-image.png', 'title' => __('List Large Image', 'themify')),
            array('value' => 'list-thumb-image', 'img' => 'images/layout-icons/list-thumb-image.png', 'title' => __('List Thumb Image', 'themify')),
            array('value' => 'grid2-thumb', 'img' => 'images/layout-icons/grid2-thumb.png', 'title' => __('Grid 2 Thumb', 'themify'))
        )
    ),
    // Posts Per Page
    array(
        "name" => "posts_per_page",
        "title" => __('Posts per page', 'themify'),
        "description" => "",
        "type" => "textbox",
        "meta" => array("size" => "small")
    ),

    // Display Content
    array(
        "name" => "display_content",
        "title" => __('Display Content', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array('name' => __('Full Content', 'themify'), "value" => "content", "selected" => true),
            array('name' => __('Excerpt', 'themify'), "value" => "excerpt"),
            array('name' => __('None', 'themify'), "value" => "none")
        )
    ),
    // Featured Image Size
    array(
        'name' => 'feature_size_page',
        'title' => __('Image Size', 'themify'),
        'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerated</a>', 'themify'),
        'type' => 'featimgdropdown'
    ),
    // Image Width
    array(
        "name" => "image_width",
        "title" => __('Image Width', 'themify'),
        "description" => "",
        "type" => "textbox",
        "meta" => array("size" => "small")
    ),
    // Image Height
    array(
        "name" => "image_height",
        "title" => __('Image Height', 'themify'),
        "description" => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'),
        "type" => "textbox",
        "meta" => array("size" => "small")
    ),
    // Hide Title
    array(
        "name" => "hide_title",
        "title" => __('Hide Post Title', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Unlink Post Title
    array(
        "name" => "unlink_title",
        "title" => __('Unlink Post Title', 'themify'),
        "description" => __('Unlink post title (it will display the post title without link)', 'themify'),
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Hide Post Date
    array(
        "name" => "hide_date",
        "title" => __('Hide Post Date', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Hide Post Meta
    array(
        "name" => "hide_meta",
        "title" => __('Hide Post Meta', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Hide Post Image
    array(
        "name" => "hide_image",
        "title" => __('Hide Featured Image', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Unlink Post Image
    array(
        "name" => "unlink_image",
        "title" => __('Unlink Featured Image', 'themify'),
        "description" => __('Display the Featured Image without link', 'themify'),
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    ),
    // Page Navigation Visibility
    array(
        "name" => "hide_navigation",
        "title" => __('Hide Page Navigation', 'themify'),
        "description" => "",
        "type" => "dropdown",
        "meta" => array(
            array("value" => "default", "name" => "", "selected" => true),
            array("value" => "yes", 'name' => __('Yes', 'themify')),
            array("value" => "no", 'name' => __('No', 'themify'))
        )
    )

);

///////////////////////////////////////
// Build Write Panels
///////////////////////////////////////
themify_build_write_panels(array(
        array(
            "name" => __('Post Options', 'themify'), // Name displayed in box
            'id' => 'post-options',
            "options" => $post_meta_box_options,    // Field options
            "pages" => "post"                    // Pages to show write panel
        ),
        array(
            "name" => __('Page Options', 'themify'),
            'id' => 'page-options',
            "options" => $page_meta_box_options,
            "pages" => "page"
        ),
        array(
            "name" => __('Query Posts', 'themify'),
            'id' => 'query-posts',
            "options" => $query_post_meta_box_options,
            "pages" => "page"
        )
    )
);


/* 	Custom Functions
/***************************************************************************/

///////////////////////////////////////
// Enable WordPress feature image
///////////////////////////////////////
add_theme_support('post-thumbnails');

///////////////////////////////////////
// Register Custom Menu Function
///////////////////////////////////////
function themify_register_custom_nav()
{
    if (function_exists('register_nav_menus')) {
        register_nav_menus(array(
            'main-nav' => __('Main Navigation', 'themify'),
            'footer-nav' => __('Footer Navigation', 'themify'),
        ));
    }
}

// Register Custom Menu Function - Action
add_action('init', 'themify_register_custom_nav');

///////////////////////////////////////
// Default Main Nav Function
///////////////////////////////////////
function themify_default_main_nav()
{
    echo '<ul id="main-nav" class="main-nav clearfix">';
    wp_list_pages('title_li=');
    echo '</ul>';
}

///////////////////////////////////////
// Register Sidebars
///////////////////////////////////////
if (function_exists('register_sidebar')) {
    register_sidebar(array(
        'name' => __('Sidebar', 'themify'),
        'id' => 'sidebar-main',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widgettitle">',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => __('Social Widget', 'themify'),
        'id' => 'social-widget',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<strong class="widgettitle">',
        'after_title' => '</strong>',
    ));
}

///////////////////////////////////////
// Footer Sidebars
///////////////////////////////////////
themify_register_grouped_widgets();

if (!function_exists('themify_theme_comment')) {
    /**
     * Custom Theme Comment
     * @param object $comment Current comment.
     * @param array $args Parameters for comment reply link.
     * @param int $depth Maximum comment nesting depth.
     * @since 1.0.0
     */
    function themify_theme_comment($comment, $args, $depth)
    {
        $GLOBALS['comment'] = $comment;
        ?>

    <li id="comment-<?php comment_ID() ?>">
        <p class="comment-author"> <?php echo get_avatar($comment, $size = '48'); ?> <?php printf('<cite>%s</cite>', get_comment_author_link()) ?>
            <br/>
            <small class="comment-time"><strong>
                    <?php comment_date(apply_filters('themify_comment_date', '')); ?>
                </strong> @
                <?php comment_time(apply_filters('themify_comment_time', '')); ?>
                <?php edit_comment_link(__('Edit', 'themify'), ' [', ']') ?>
            </small>
        </p>
        <div class="commententry">
            <?php if ($comment->comment_approved == '0') : ?>
                <p><em>
                        <?php _e('Your comment is awaiting moderation.', 'themify') ?>
                    </em></p>
            <?php endif; ?>
            <?php comment_text() ?>
        </div>
        <p class="reply">
            <?php comment_reply_link(array_merge($args, array('add_below' => 'comment', 'depth' => $depth, 'reply_text' => __('Reply', 'themify'), 'max_depth' => $args['max_depth']))) ?>
        </p>
        <?php
    }
}

/**
 * Handle Builder's JavaScript fullwidth rows, forces fullwidth rows if sidebar is disabled
 *
 * @return bool
 */
function themify_theme_fullwidth_layout($support)
{
    global $themify;

    /* if Content Width option is set to Fullwidth, do not use JavaScript */
    if (themify_get('content_width') == 'full_width') {
        return true;
    }

    /* using sidebar-none layout, force fullwidth rows using JavaScript */
    if ($themify->layout == 'sidebar-none') {
        return false;
    }

    return true;
}

add_filter('themify_builder_fullwidth_layout_support', 'themify_theme_fullwidth_layout');
