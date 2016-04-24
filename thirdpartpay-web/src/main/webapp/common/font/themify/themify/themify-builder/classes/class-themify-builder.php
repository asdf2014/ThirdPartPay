<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Themify_Builder')) {

    /**
     * Main Themify Builder class
     * 
     * @package default
     */
    class Themify_Builder {

        /**
         * @var string
         */
        public $meta_key;

        /**
         * @var string
         */
        public $meta_key_transient;

        /**
         * @var array
         */
        var $builder_settings = array();

        /**
         * @var array
         */
        var $module_settings = array();

        /**
         * @var array
         */
        var $registered_post_types = array();

        /**
         * Define builder grid active or not
         * @var bool
         */
        private static $frontedit_active = false;

        /**
         * Define load form
         * @var string
         */
        var $load_form = 'module';

        /**
         * Directory Registry
         */
        var $directory_registry = array();

        /**
         * Array of classnames to add to post objects
         */
        var $_post_classes = array();

        /**
         * Get status of builder content whether inside builder content or not
         */
        public $in_the_loop = false;

        /**
         * The original post id
         */
        public static $post_id = false;

        /**
         * The layout_part_id
         */
        public static $layout_part_id = false;

        /**
         * Active custom post types registered by Builder.
         *
         * @var array
         */
        var $builder_cpt = array();

        /**
         * List of all public post types.
         *
         * @since 1.1.9
         *
         * @var array
         */
        var $public_post_types = array();

        /**
         * A list of posts which have been rendered by Builder
         */
        private $post_ids = array();

        /**
         * Flag to know if we're in the middle of saving the stylesheet.
         * @var string
         */
        var $saving_stylesheet = true;

        /**
         * Flag to know if we're rendering the style inline.
         * @var string
         */
        var $is_front_end_style_inline = false;

        /**
         * Selectors for CSS transition animations.
         * @var string
         */
        var $transition_selectors = '';

        /**
         * Themify Builder Constructor
         */
        function __construct() {
            
        }

        /**
         * Class Init
         */
        function init() {
            // Include required files
            $this->includes();
            $this->setup_default_directories();

            /* git #1862 */
            $this->builder_cpt_check();

            do_action('themify_builder_setup_modules', $this);

            // Init
            Themify_Builder_Model::load_general_metabox(); // setup metabox fields
            $this->load_modules(); // load builder modules
            // Builder write panel
            add_filter('themify_do_metaboxes', array($this, 'builder_write_panels'), 11);

            // Filtered post types
            add_filter('themify_post_types', array($this, 'extend_post_types'));
            add_filter('themify_builder_module_content', 'wptexturize');
            add_filter('themify_builder_module_content', 'convert_smilies');
            add_filter('themify_builder_module_content', 'convert_chars');
            add_filter('themify_builder_module_content', array($this, 'the_module_content'));

            /**
             * WordPress 4.4 Responsive Images support */
            global $wp_version;
            if (version_compare($wp_version, '4.4', '>=')) {
                add_filter('themify_builder_module_content', 'wp_make_content_images_responsive');
                add_filter('themify_image_make_responsive_image', 'wp_make_content_images_responsive');
            }

            // Actions
            add_action('init', array($this, 'setup'), 10);
            add_action('themify_builder_metabox', array($this, 'add_builder_metabox'), 10);
            //add_action( 'media_buttons_context', array( $this, 'add_custom_switch_btn' ), 10 );
            add_action('admin_enqueue_scripts', array($this, 'load_admin_interface'), 10);
            add_action('wp_head', array($this, 'ie_enhancements'));

            // Asynchronous Loader
            add_action('wp_enqueue_scripts', array($this, 'register_frontend_js_css'), 9);
            if (Themify_Builder_Model::is_frontend_editor_page()) {
                add_action('wp_enqueue_scripts', array($this, 'async_load_builder_js'), 9);
                add_action('wp_footer', array($this, 'async_load_assets_loaded'), 99);
                add_action('wp_ajax_themify_builder_loader', array($this, 'async_load_builder'));
                add_action('wp_ajax_nopriv_themify_builder_loader', array($this, 'async_load_builder'));
                // load module panel frontend
                add_action('wp_footer', array($this, 'builder_module_panel_frontedit'), 10);
                add_action('wp_footer', array($this, 'load_javascript_template_common'), 10);
                add_action('wp_footer', array($this, 'load_javascript_template_front'), 10);
                add_action('wp_footer', 'themify_font_icons_dialog', 10);
                // Row Styling
                add_action('themify_builder_row_start', array($this, 'render_row_styling'), 10, 2);

                // Column Styling
                add_action('themify_builder_column_start', array($this, 'render_column_styling'), 10, 3);

                // Sub-Column Styling
                add_action('themify_builder_sub_column_start', array($this, 'render_sub_column_styling'), 10, 5);

                // Google Fonts
                add_action('wp_enqueue_scripts', array($this, 'enqueue_fonts'), 10);
            } else {
                // If user not logged in and is not a Builder editor view, enqueue static stylesheet
                if (isset($_GET['themify_builder_infinite_scroll']) && 'yes' == $_GET['themify_builder_infinite_scroll']) {
                    add_action('themify_builder_row_start', array($this, 'render_row_styling'), 10, 2);
                } else {
                    add_action('wp_enqueue_scripts', array($this, 'enqueue_stylesheet'), 14);
                    add_action('themify_builder_before_template_content_render', array($this, 'enqueue_stylesheet'), 10, 2);
                }
            }

            // Ajax Actions
            add_action('wp_ajax_tfb_add_element', array($this, 'add_element_ajaxify'), 10);
            add_action('wp_ajax_tfb_add_wp_editor', array($this, 'add_wp_editor_ajaxify'), 10);
            add_action('wp_ajax_tfb_load_shortcode_preview', array($this, 'shortcode_preview'), 10);
            add_action('wp_ajax_builder_import', array($this, 'builder_import_ajaxify'), 10);
            add_action('wp_ajax_builder_import_submit', array($this, 'builder_import_submit_ajaxify'), 10);
            add_action('wp_ajax_builder_render_duplicate_row', array($this, 'render_duplicate_row_ajaxify'), 10);
            add_action('wp_ajax_tfb_imp_component_data_lightbox_options', array($this, 'imp_component_data_lightbox_options_ajaxify'), 10);
            add_action('wp_ajax_tfb_exp_component_data_lightbox_options', array($this, 'exp_component_data_lightbox_options_ajaxify'), 10);

            // Live styling
            add_action('wp_ajax_tfb_slider_live_styling', array($this, 'slider_live_styling'), 10);

            // WP_AJAX Live styling hooks (from addons/plugins).
            do_action('themify_builder_live_styling_ajax', $this);

            // Builder Save Data
            add_action('wp_ajax_tfb_save_data', array($this, 'save_data_builder'), 10);

            // Duplicate page / post action
            add_action('wp_ajax_tfb_duplicate_page', array($this, 'duplicate_page_ajaxify'), 10);

            // Hook to frontend
            add_action('wp_head', array($this, 'load_inline_js_script'), 10);
            add_filter('the_content', array($this, 'builder_show_on_front'), 11);
            add_action('wp_ajax_tfb_toggle_frontend', array($this, 'load_toggle_frontend_ajaxify'), 10);
            add_action('wp_ajax_tfb_load_module_partial', array($this, 'load_module_partial_ajaxify'), 10);
            add_action('wp_ajax_tfb_load_row_partial', array($this, 'load_row_partial_ajaxify'), 10);
            add_filter('body_class', array($this, 'body_class'), 10);

            // Shortcode
            add_shortcode('themify_builder_render_content', array($this, 'do_shortcode_builder_render_content'));

            // Plupload Action
            add_action('admin_enqueue_scripts', array($this, 'plupload_admin_head'), 10);
            // elioader
            //add_action( 'wp_head', array( $this, 'plupload_front_head' ), 10 );

            add_action('wp_ajax_themify_builder_plupload_action', array($this, 'builder_plupload'), 10);

            add_action('admin_bar_menu', array($this, 'builder_admin_bar_menu'), 100);

            // Frontend editor
            add_action('themify_builder_edit_module_panel', array($this, 'module_edit_panel_front'), 10, 2);

            // Checks if a stylesheet with the proper slug exists, otherwise generates it.
            add_action('save_post', array($this, 'build_stylesheet_if_needed'), 77, 1);

            // Switch to frontend
            add_action('save_post', array($this, 'switch_frontend'), 999, 1);

            // Reset Builder Filter
            // Non active at the moment
            //add_action( 'themify_builder_before_template_content_render', array( $this, 'do_reset_before_template_content_render' ) );
            //add_action( 'themify_builder_after_template_content_render', array( $this, 'do_reset_after_template_content_render' ) );
            // WordPress Search
            add_filter('posts_where', array($this, 'do_search'));

            add_filter('post_class', array($this, 'filter_post_class'));

            if (Themify_Builder_Model::is_animation_active()) {
                add_filter('themify_builder_animation_inview_selectors', array($this, 'add_inview_selectors'));
            }

            // Render any js classname
            add_action('wp_head', array($this, 'render_javascript_classes'));
            add_action('wp_head', array($this, 'add_builder_inline_css'), 0);

            // Add extra protocols like skype: to WordPress allowed protocols.
            if (!has_filter('kses_allowed_protocols', 'themify_allow_extra_protocols')) {
                add_filter('kses_allowed_protocols', 'themify_allow_extra_protocols');
            }

            // Clear All builder caches in Themify Settings > Builder with ajax
            if (defined('DOING_AJAX')) {
                add_action('wp_ajax_themify_builder_clear_all_caches', array($this, 'clear_all_builder_caches'), 10);
            }

            add_filter('themify_builder_is_frontend_editor', array($this, 'post_type_editor_support_check'));

            // Themify Builder Revisions
            new Themify_Builder_Revisions($this);
        }

        /**
         * Return Builder data for a post
         *
         * @since 1.4.2
         * @return array
         */
        public function get_builder_data($post_id) {
            $builder_data = get_post_meta($post_id, $this->meta_key, true);
            $builder_data = stripslashes_deep(maybe_unserialize($builder_data));
            if (!is_array($builder_data)) {
                $builder_data = array();
            }

            return apply_filters('themify_builder_data', $builder_data, $post_id);
        }

        /**
         * Return all modules for a post as a two-dimensional array
         *
         * @since 1.4.2
         * @return array
         */
        public function get_flat_modules_list($post_id = null, $builder_data = null) {
            if ($builder_data == null) {
                $builder_data = $this->get_builder_data($post_id);
            }

            $_modules = array();
            // loop through modules in Builder
            if (is_array($builder_data)) {
                foreach ($builder_data as $row_id => $row) {
                    if (isset($row['cols']) && !empty($row['cols'])) {
                        foreach ($row['cols'] as $cols => $col) {
                            if (isset($col['modules']) && !empty($col['modules'])) {
                                foreach ($col['modules'] as $modules => $mod) {
                                    if (isset($mod['mod_name'])) {
                                        $_modules[] = $mod;
                                    }
                                    // Check for Sub-rows
                                    if (isset($mod['cols']) && !empty($mod['cols'])) {
                                        foreach ($mod['cols'] as $col_key => $sub_col) {
                                            if (isset($sub_col['modules']) && !empty($sub_col['modules'])) {
                                                foreach ($sub_col['modules'] as $sub_module_k => $sub_module) {
                                                    $_modules[] = $sub_module;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $_modules;
        }

        /**
         * Return first not empty text module
         *
         * @since 1.4.2
         * @return string
         */
        public function get_first_text($post_id = null, $builder_data = null) {
            if ($builder_data == null) {
                $builder_data = $this->get_builder_data($post_id);
            }
            // loop through modules in Builder
            if (is_array($builder_data)) {
                foreach ($builder_data as $row) {
                    if (isset($row['cols']) && !empty($row['cols'])) {
                        foreach ($row['cols'] as $col) {
                            if (isset($col['modules']) && !empty($col['modules'])) {
                                foreach ($col['modules'] as $mod) {
                                    if (isset($mod['mod_name']) && $mod['mod_name'] == 'text' && isset($mod['mod_settings']) && isset($mod['mod_settings']['content_text']) && $mod['mod_settings']['content_text']) {
                                        return $mod['mod_settings']['content_text'];
                                    }
                                    // Check for Sub-rows
                                    if (isset($mod['cols']) && !empty($mod['cols'])) {
                                        foreach ($mod['cols'] as $sub_col) {
                                            if (isset($sub_col['modules']) && !empty($sub_col['modules'])) {
                                                foreach ($sub_col['modules'] as $sub_module) {
                                                    if (isset($sub_module['mod_name']) && $sub_module['mod_name'] == 'text' && isset($sub_module['mod_settings']) && isset($sub_module['mod_settings']['content_text']) && $sub_module['mod_settings']['content_text']) {
                                                        return $sub_module['mod_settings']['content_text'];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return '';
        }

        /**
         * Load JS and CSs for async loader.
         *
         * @since 2.1.9
         */
        public function async_load_builder_js() {

            wp_enqueue_style('themify-builder-loader', THEMIFY_BUILDER_URI . '/css/themify.builder.loader.css');
            wp_enqueue_script('themify-builder-loader', THEMIFY_BUILDER_URI . '/js/themify.builder.loader.js', array('jquery'));
            wp_localize_script('themify-builder-loader', 'tbLoaderVars', array(
                'ajaxurl' => admin_url('admin-ajax.php', 'relative'),
                'assets' => array(
                    'scripts' => array(),
                    'styles' => array(),
                ),
                'post_ID' => get_the_ID(),
                'progress' => '<div id="builder_progress"><div></div></div>',
                'turnOnBuilder' => __('Turn On Builder', 'themify'),
            ));

            if (function_exists('wp_enqueue_media')) {
                wp_enqueue_media();
            }
        }

        /**
         * Called by AJAX action themify_builder_loader.
         * 1. Hooks the load_front_js_css function to wp_footer
         * 2. Saves scripts and styles already loaded in page
         * 3. Executes wp_head and wp_footer to load new scripts from load_front_js_css. Dismisses output
         * 4. Compiles list of new styles and scripts to load and js vars to pass
         * 5. Echoes list
         *
         * @since 2.1.9
         * @since 2.4.2 Clear all output buffers.
         */
        public function async_load_builder() {
            add_action('wp_footer', array($this, 'load_frontend_interface'));

            global $wp_scripts, $wp_styles;

            $done_styles = isset($_POST['styles']) ? ( $_POST['styles'] ) : array();
            $done_scripts = isset($_POST['scripts']) ? ( $_POST['scripts'] ) : array();

            ob_start();
            wp_head();
            wp_footer();
            while (ob_get_length()) {
                ob_end_clean();
            }

            $results = array();

            $new_styles = array_diff($wp_styles->done, $done_styles);
            $new_scripts = array_diff($wp_scripts->done, $done_scripts);

            if (!empty($new_styles)) {
                $results['styles'] = array();

                foreach ($new_styles as $handle) {
                    // Abort if somehow the handle doesn't correspond to a registered stylesheet
                    if (!isset($wp_styles->registered[$handle]))
                        continue;

                    // Provide basic style data
                    $style_data = array(
                        'handle' => $handle,
                        'media' => 'all'
                    );

                    // Base source
                    $src = $wp_styles->registered[$handle]->src;

                    // Take base_url into account
                    if (strpos($src, 'http') !== 0)
                        $src = $wp_styles->base_url . $src;

                    // Version and additional arguments
                    if (null === $wp_styles->registered[$handle]->ver)
                        $ver = '';
                    else
                        $ver = $wp_styles->registered[$handle]->ver ? $wp_styles->registered[$handle]->ver : $wp_styles->default_version;

                    if (isset($wp_styles->args[$handle]))
                        $ver = $ver ? $ver . '&amp;' . $wp_styles->args[$handle] : $wp_styles->args[$handle];

                    // Full stylesheet source with version info
                    $style_data['src'] = add_query_arg('ver', $ver, $src);

                    // Parse stylesheet's conditional comments if present, converting to logic executable in JS
                    if (isset($wp_styles->registered[$handle]->extra['conditional']) && $wp_styles->registered[$handle]->extra['conditional']) {
                        // First, convert conditional comment operators to standard logical operators. %ver is replaced in JS with the IE version
                        $style_data['conditional'] = str_replace(array(
                            'lte',
                            'lt',
                            'gte',
                            'gt'
                                ), array(
                            '%ver <=',
                            '%ver <',
                            '%ver >=',
                            '%ver >',
                                ), $wp_styles->registered[$handle]->extra['conditional']);

                        // Next, replace any !IE checks. These shouldn't be present since WP's conditional stylesheet implementation doesn't support them, but someone could be _doing_it_wrong().
                        $style_data['conditional'] = preg_replace('#!\s*IE(\s*\d+){0}#i', '1==2', $style_data['conditional']);

                        // Lastly, remove the IE strings
                        $style_data['conditional'] = str_replace('IE', '', $style_data['conditional']);
                    }

                    // Parse requested media context for stylesheet
                    if (isset($wp_styles->registered[$handle]->args))
                        $style_data['media'] = esc_attr($wp_styles->registered[$handle]->args);

                    // Add stylesheet to data that will be returned to IS JS
                    array_push($results['styles'], $style_data);
                }
            }

            if (!empty($new_scripts)) {
                $results['scripts'] = array();

                foreach ($new_scripts as $handle) {
                    // Abort if somehow the handle doesn't correspond to a registered script
                    if (!isset($wp_scripts->registered[$handle])) {
                        continue;
                    }

                    // Provide basic script data
                    $script_data = array(
                        'handle' => $handle,
                        'footer' => ( is_array($wp_scripts->in_footer) && in_array($handle, $wp_scripts->in_footer) ),
                        'jsVars' => $wp_scripts->print_extra_script($handle, false)
                    );

                    // Base source
                    $src = $wp_scripts->registered[$handle]->src;

                    // Take base_url into account
                    if (strpos($src, 'http') !== 0) {
                        $src = $wp_scripts->base_url . $src;
                    }

                    // Version and additional arguments
                    if (null === $wp_scripts->registered[$handle]->ver) {
                        $ver = '';
                    } else {
                        $ver = $wp_scripts->registered[$handle]->ver ? $wp_scripts->registered[$handle]->ver : $wp_scripts->default_version;
                    }

                    if (isset($wp_scripts->args[$handle])) {
                        $ver = $ver ? $ver . '&amp;' . $wp_scripts->args[$handle] : $wp_scripts->args[$handle];
                    }

                    // Full script source with version info
                    $script_data['src'] = add_query_arg('ver', $ver, $src);

                    // Add script to data that will be returned to IS JS
                    array_push($results['scripts'], $script_data);
                }
            }

            ob_start();
                // Include module form template
                include_once( sprintf("%s/themify-builder-js-tmpl-form.php", THEMIFY_BUILDER_INCLUDES_DIR) );
                $results['tmpl'] = ob_get_contents();
            ob_end_clean();

            echo json_encode($results);

            die();
        }

        /**
         * Print scripts that are already loaded.
         *
         * @since 2.1.9
         *
         * @global $wp_scripts, $wp_styles
         * @action wp_footer
         * @return string
         */
        function async_load_assets_loaded() {
            global $wp_scripts, $wp_styles;

            wp_editor('', '');

            $scripts = is_a($wp_scripts, 'WP_Scripts') ? $wp_scripts->done : array();
            $styles = is_a($wp_styles, 'WP_Styles') ? $wp_styles->done : array();
            ?><script type="text/javascript">
                jQuery.extend(tbLoaderVars.assets.scripts, <?php echo json_encode($scripts); ?>);
                jQuery.extend(tbLoaderVars.assets.styles, <?php echo json_encode($styles); ?>);
            </script><?php
        }

        public function builder_cpt_check() {
            $post_types = get_option('builder_cpt', null);
            if (!is_array($post_types)) {
                global $wpdb;
                foreach (array('slider', 'highlight', 'testimonial', 'portfolio') as $post_type) {
                    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = '%s'", $post_type));
                    if ($count > 0) {
                        $this->builder_cpt[] = $post_type;
                    }
                }
                update_option('builder_cpt', $this->builder_cpt);
            } else {
                $this->builder_cpt = $post_types;
            }
        }

        public function is_cpt_active($post_type) {
            $active = false;
            if (in_array($post_type, $this->builder_cpt)) {
                $active = true;
            }

            return apply_filters("builder_is_{$post_type}_active", $active);
        }

        /**
         * Register default directories used to load modules and their templates
         */
        function setup_default_directories() {
            $this->register_directory('templates', THEMIFY_BUILDER_TEMPLATES_DIR, 1);
            $this->register_directory('templates', get_template_directory() . '/themify-builder/', 5);
            if (is_child_theme()) {
                $this->register_directory('templates', get_stylesheet_directory() . '/themify-builder/', 9);
            }
            $this->register_directory('modules', THEMIFY_BUILDER_MODULES_DIR, 1);
            $this->register_directory('modules', get_template_directory() . '/themify-builder-modules/', 5);
        }

        /**
         * Init function
         */
        function setup() {
            // Define builder path
            $this->builder_settings = array(
                'template_url' => 'themify-builder/',
                'builder_path' => THEMIFY_BUILDER_TEMPLATES_DIR . '/'
            );

            // Define meta key name
            $this->meta_key = apply_filters('themify_builder_meta_key', '_themify_builder_settings');
            $this->meta_key_transient = apply_filters('themify_builder_meta_key_transient', 'themify_builder_settings_transient');

            // Check whether grid edit active
            self::is_front_builder_activate();
        }

        function get_meta_key() {
            return $this->meta_key;
        }

        /**
         * Include required files
         */
        function includes() {
            // Class duplicate page
            include_once THEMIFY_BUILDER_CLASSES_DIR . '/class-builder-duplicate-page.php';
            include_once THEMIFY_BUILDER_CLASSES_DIR . '/class-builder-data-manager.php';
            include_once THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-revisions.php';
        }

        /**
         * List of post types that support the editor
         *
         * @since 2.4.8
         */
        function builder_post_types_support() {
            $public_post_types = get_post_types(array(
                'public' => true,
                '_builtin' => false,
                'show_ui' => true,
            ));
            $post_types = array_merge($public_post_types, array('post', 'page'));
            foreach ($post_types as $key => $type) {
                if (!post_type_supports($type, 'editor')) {
                    unset($post_types[$key]);
                }
            }

            return apply_filters('themify_builder_post_types_support', $post_types);
        }

        /**
         * Disable Builder front end editor for post types that do not support "editor".
         *
         * @since 2.4.8
         */
        function post_type_editor_support_check($active) {
            $post_type = get_post_type();
            if (!defined('DOING_AJAX') /* check for Ajax requests, this prevents Builder frontend editor not loading via Ajax */ && !in_array($post_type, $this->builder_post_types_support())
            ) {
                $active = false;
            }

            return $active;
        }

        /**
         * Builder write panels
         *
         * @param $meta_boxes
         *
         * @return array
         */
        function builder_write_panels($meta_boxes) {
            global $pagenow;

            // Page builder Options
            $page_builder_options = apply_filters('themify_builder_write_panels_options', array(
                // Notice
                array(
                    'name' => '_builder_notice',
                    'title' => '',
                    'description' => '',
                    'type' => 'separator',
                    'meta' => array(
                        'html' => '<div class="themify-info-link">' . wp_kses_post(sprintf(__('<a href="%s">Themify Builder</a> is a drag &amp; drop tool that helps you to create any type of layouts. To use it: drop the module on the grid where it says "drop module here". Once the post is saved or published, you can click on the "Switch to frontend" button to switch to frontend edit mode.', 'themify'), 'http://themify.me/docs/builder')) . '</div>'
                    ),
                ),
                array(
                    'name' => 'page_builder',
                    'title' => __('Themify Builder', 'themify'),
                    'description' => '',
                    'type' => 'page_builder',
                    'meta' => array()
                ),
                array(
                    'name' => 'builder_switch_frontend',
                    'title' => false,
                    'type' => 'textbox',
                    'value' => 0,
                    'meta' => array('size' => 'small')
                )
            ));

            $types = $this->builder_post_types_support();
            $all_meta_boxes = array();
            foreach ($types as $type) {
                $all_meta_boxes[] = apply_filters('themify_builder_write_panels_meta_boxes', array(
                    'name' => __('Themify Builder', 'themify'),
                    'id' => 'page-builder',
                    'options' => $page_builder_options,
                    'pages' => $type
                ));
            }

            return array_merge($meta_boxes, $all_meta_boxes);
        }

        function register_directory($context, $path, $priority = 10) {
            $this->directory_registry[$context][$priority][] = trailingslashit($path);
        }

        function get_directory_path($context) {
            return call_user_func_array('array_merge', $this->directory_registry[$context]);
        }

        /**
         * Load builder modules
         */
        function load_modules() {
            // load modules
            $active_modules = $this->get_modules('active');

            foreach ($active_modules as $m) {
                $path = $m['dirname'] . '/' . $m['basename'];
                require_once( $path );
            }
        }

        /**
         * Get module php files data
         * @param string $select
         * @return array
         */
        function get_modules($select = 'all') {
            $_modules = array();
            foreach ($this->get_directory_path('modules') as $dir) {
                if (file_exists($dir)) {
                    $d = dir($dir);
                    while (( false !== ( $entry = $d->read() ))) {
                        if ($entry !== '.' && $entry !== '..' && $entry !== '.svn') {
                            $path = $d->path . $entry;
                            $module_name = basename($path);
                            $_modules[$module_name] = $path;
                        }
                    }
                }
            }
            ksort($_modules);

            foreach ($_modules as $value) {
                if (is_dir($value))
                    continue; /* clean-up, make sure no directories is included in the list */
                $path_info = pathinfo($value);
                if (!preg_match('/^module-/', $path_info['filename']))
                    continue; /* convention: module's file name must begin with module-* */
                $id = str_replace('module-', '', $path_info['filename']);
                $module_data = get_file_data($value, array('Module Name'));
                $modules[$id] = array(
                    'name' => $module_data[0],
                    'id' => $id,
                    'dirname' => $path_info['dirname'],
                    'extension' => $path_info['extension'],
                    'basename' => $path_info['basename'],
                );
            }

            if ('active' == $select) {
                $pre = 'setting-page_builder_';
                $data = themify_get_data();
                if (count($modules) > 0) {
                    foreach ($modules as $key => $m) {
                        $exclude = $pre . 'exc_' . $m['id'];
                        if (isset($data[$exclude]))
                            unset($modules[$m['id']]);
                    }
                }
            } elseif ('registered' == $select) {
                foreach ($modules as $key => $m) {
                    /* check if module is registered */
                    if (!Themify_Builder_Model::check_module_active($key)) {
                        unset($modules[$key]);
                    }
                }
            }

            return $modules;
        }

        /**
         * Check if builder frontend edit being invoked
         */
        public static function is_front_builder_activate() {
            if (isset($_POST['builder_grid_activate']) && $_POST['builder_grid_activate'] == 1) {
                self::$frontedit_active = true;
            }
            return self::$frontedit_active;
        }

        /**
         * Add builder metabox
         */
        function add_builder_metabox() {
            global $post, $pagenow;

            $builder_data = $this->get_builder_data($post->ID);

            if (empty($builder_data)) {
                $builder_data = array();
            }

            include THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-meta.php';
        }

        /**
         * Load admin js and css
         * @param $hook
         */
        function load_admin_interface($hook) {
            global $pagenow, $current_screen;

            if (in_array($hook, array('post-new.php', 'post.php')) && in_array(get_post_type(), themify_post_types()) && Themify_Access_Role::check_access_backend()) {

                add_action('admin_footer', array(&$this, 'load_javascript_template_common'), 10);
                add_action('admin_footer', array(&$this, 'load_javascript_template_admin'), 10);

                wp_enqueue_style('themify-builder-main', THEMIFY_BUILDER_URI . '/css/themify-builder-main.css', array(), THEMIFY_VERSION);
                wp_enqueue_style('themify-builder-admin-ui', THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui.css', array(), THEMIFY_VERSION);
                wp_enqueue_style('themify-builder-animate-css', THEMIFY_BUILDER_URI . '/css/animate.min.css', array(), THEMIFY_VERSION); // required to load for lightbox animation
                if (is_rtl()) {
                    wp_enqueue_style('themify-builder-admin-ui-rtl', THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui-rtl.css', array('themify-builder-admin-ui'), THEMIFY_VERSION);
                }

                // Enqueue builder admin scripts
                $enqueue_scripts = array(
                    'jquery-ui-core',
                    'jquery-ui-accordion',
                    'jquery-ui-droppable',
                    'jquery-ui-sortable',
                    'jquery-ui-resizable',
                    'jquery-effects-core',
                    'themify-builder-undo-manager-js',
                    'themify-builder-common-js',
                    'themify-builder-admin-ui-js'
                );

                foreach ($enqueue_scripts as $script) {
                    switch ($script) {

                        case 'themify-builder-undo-manager-js':
                            wp_enqueue_script($script, THEMIFY_BUILDER_URI . '/js/undomanager.js', array('jquery'));
                            break;

                        case 'themify-builder-common-js':
                            wp_register_script('themify-builder-common-js', THEMIFY_BUILDER_URI . "/js/themify.builder.common.js", array('jquery'), THEMIFY_VERSION, true);
                            wp_enqueue_script('themify-builder-common-js');

                            wp_localize_script('themify-builder-common-js', 'themifyBuilderCommon', apply_filters('themify_builder_common_vars', array(
                                'text_no_localStorage' =>
                                __("Your browser does not support this feature. Please use a modern browser such as Google Chrome or Safari.", 'themify'),
                                'text_confirm_data_paste' => __('This will overwrite the data. Ok to proceed?', 'themify'),
                                'text_alert_wrong_paste' => __('Error: Paste valid data only (paste row data to row, sub-row data to sub-row, module data to module).', 'themify')
                            )));
                            break;

                        case 'themify-builder-admin-js':
                            wp_register_script('themify-builder-admin-js', THEMIFY_BUILDER_URI . "/js/themify.builder.admin.js", array('jquery', 'themify-builder-common-js'), THEMIFY_VERSION, true);
                            wp_enqueue_script('themify-builder-admin-js');

                            wp_localize_script('themify-builder-admin-js', 'TBuilderAdmin_Settings', apply_filters('themify_builder_ajax_admin_vars', array(
                                'home_url' => get_home_url(),
                                'permalink' => get_permalink(),
                                'tfb_load_nonce' => wp_create_nonce('tfb_load_nonce')
                            )));
                            break;

                        case 'themify-builder-admin-ui-js':
                            wp_register_script('themify-builder-admin-ui-js', THEMIFY_BUILDER_URI . "/js/themify.builder.admin.ui.js", array('jquery'), THEMIFY_VERSION, true);
                            wp_enqueue_script('themify-builder-admin-ui-js');
                            wp_localize_script('themify-builder-admin-ui-js', 'themifyBuilder', apply_filters('themify_builder_ajax_admin_vars', array(
                                'ajaxurl' => admin_url('admin-ajax.php'),
                                'tfb_load_nonce' => wp_create_nonce('tfb_load_nonce'),
                                'tfb_url' => THEMIFY_BUILDER_URI,
                                'dropPlaceHolder' => __('drop module here', 'themify'),
                                'draggerTitleMiddle' => __('Drag left/right to change columns', 'themify'),
                                'draggerTitleLast' => __('Drag left to add columns', 'themify'),
                                'confirm_on_duplicate_page' => __('Save the Builder before duplicating this page?', 'themify'),
                                'textRowStyling' => __('Row Styling', 'themify'),
                                'textColumnStyling' => __('Column Styling', 'themify'),
                                'permalink' => get_permalink(),
                                'isTouch' => themify_is_touch() ? 'true' : 'false',
                                'isThemifyTheme' => $this->is_themify_theme() ? 'true' : 'false',
                                'moduleDeleteConfirm' => __('Press OK to remove this module', 'themify'),
                                'rowDeleteConfirm' => __('Press OK to remove this row', 'themify'),
                                'subRowDeleteConfirm' => __('Press OK to remove this sub row', 'themify'),
                                'disableShortcuts' => themify_check('setting-page_builder_disable_shortcuts'),
                                'importFileConfirm' => __('This import will override all current Builder data. Press OK to continue', 'themify'),
                                'confirm_template_selected' => __('Would you like to replace or append the layout?', 'themify'),
                                'confirm_delete_layout' => __('Are you sure want to delete this layout ?', 'themify'),
                                'isFrontend' => 'false',
                            )));
                            break;

                        default:
                            wp_enqueue_script($script);
                            break;
                    }
                }

                do_action('themify_builder_admin_enqueue', $this);
            }
        }

        /**
         * Load inline js script
         * Frontend editor
         */
        function load_inline_js_script() {
            global $post;
            if (Themify_Builder_Model::is_frontend_editor_page()) {
                ?>
                <script type="text/javascript">
                    var ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative'); ?>',
                            isRtl = <?php echo (int) is_rtl(); ?>;
                </script>
                <?php
            }
        }

        function is_fullwidth_layout_supported() {
            return apply_filters('themify_builder_fullwidth_layout_support', false);
        }

        /**
         * Register styles and scripts necessary for Builder template output.
         * These are enqueued when user initializes Builder or from a template output.
         *
         * Registered style handlers:
         *
         * Registered script handlers:
         * themify-builder-module-plugins-js
         * themify-builder-script-js
         *
         * @since 2.1.9
         */
        function register_frontend_js_css() {

            // Builder main scripts
            wp_enqueue_style('themify-builder-style', THEMIFY_BUILDER_URI . '/css/themify-builder-style.css', array(), THEMIFY_VERSION);

            ////Enqueue main js that will load others needed js
            wp_register_script('themify-main-script', THEMIFY_URI . '/js/main.js', array('jquery'), THEMIFY_VERSION, true);
            wp_localize_script('themify-main-script', 'themify_vars', array(
                'version' => THEMIFY_VERSION,
                'url' => THEMIFY_URI,
                'TB' => 1
            ));

            wp_localize_script('themify-main-script', 'tbLocalScript', apply_filters('themify_builder_script_vars', array(
                'isAnimationActive' => Themify_Builder_Model::is_animation_active(),
                'isParallaxActive' => Themify_Builder_Model::is_parallax_active(),
                'animationInviewSelectors' => self::$inview_selectors,
                'createAnimationSelectors' => self::$new_selectors,
                'backgroundSlider' => array(
                    'autoplay' => 5000,
                    'speed' => 2000,
                ),
                'animationOffset' => 100,
                'videoPoster' => THEMIFY_BUILDER_URI . '/img/blank.png',
                'backgroundVideoLoop' => 'yes',
                'builder_url' => THEMIFY_BUILDER_URI,
                'framework_url' => THEMIFY_URI,
                'version' => THEMIFY_VERSION,
                'fullwidth_support' => $this->is_fullwidth_layout_supported(),
                'fullwidth_container' => 'body',
                'loadScrollHighlight' => true
            )));

            //Inject variable values in gallery script
            wp_localize_script('themify-main-script', 'themifyScript', array(
                'lightbox' => themify_lightbox_vars_init(),
                'lightboxContext' => apply_filters('themify_lightbox_context', 'body')
                    )
            );

            //Inject variable values in Scroll-Highlight script
            wp_localize_script('themify-main-script', 'tbScrollHighlight', apply_filters('themify_builder_scroll_highlight_vars', array(
                'fixedHeaderSelector' => '',
                'speed' => 900,
                'navigation' => '#main-nav',
                'scrollOffset' => 0
            )));

            wp_enqueue_script('themify-main-script');
        }

        static $inview_selectors;
        static $new_selectors;

        /**
         * Defines selectors for CSS animations and transitions.
         *
         * @param $selectors
         *
         * @return array
         */
        public function add_inview_selectors($selectors) {
            $extends = array(
                '.module.wow',
                '.themify_builder_content .themify_builder_row.wow',
                '.module_row.wow',
                '.builder-posts-wrap > .post.wow',
                '.fly-in > .post', '.fly-in .row_inner > .tb-column',
                '.fade-in > .post', '.fade-in .row_inner > .tb-column',
                '.slide-up > .post', '.slide-up .row_inner > .tb-column'
            );
            return array_merge($selectors, $extends);
        }

        /**
         * Add inline CSS styles for animations
         * @since 2.2.7
         */
        public function add_builder_inline_css() {
            // Setup Animation
            self::$inview_selectors = apply_filters('themify_builder_animation_inview_selectors', array());
            self::$new_selectors = apply_filters('themify_builder_create_animation_selectors', array());

            $global_selectors = isset(self::$new_selectors['selectors']) ? self::$new_selectors['selectors'] : array();
            $specific_selectors = isset(self::$new_selectors['specificSelectors']) ? array_keys(self::$new_selectors['specificSelectors']) : array();
            $instyle_selectors = array_merge(self::$inview_selectors, $global_selectors, $specific_selectors);

            if (count($instyle_selectors) > 0) {

                $this->transition_selectors = '.js.csstransitions ' . join(', .js.csstransitions ', $instyle_selectors);
                $inline_style = $this->transition_selectors . ' { visibility:hidden; }';
                printf('<style type="text/css">%s</style>', $inline_style);
                add_action('wp_footer', array($this, 'write_transition_selectors'), 77);
            }
        }

        /**
         * Write CSS transition selectors for manipulation inside theme.
         *
         * @since 2.3.2
         */
        function write_transition_selectors() {
            ?>
            <script type="text/javascript">
                if ('object' === typeof tbLocalScript) {
                    tbLocalScript.transitionSelectors = <?php echo json_encode($this->transition_selectors); ?>;
                }
            </script>
            <?php
        }

        /**
         * Load interface js and css
         *
         * @since 2.1.9
         */
        function load_frontend_interface() {



            // load only when editing and login
            if (Themify_Builder_Model::is_frontend_editor_page()) {
                wp_enqueue_style('themify-builder-main', THEMIFY_BUILDER_URI . '/css/themify-builder-main.css', array(), THEMIFY_VERSION);
                wp_enqueue_style('themify-builder-admin-ui', THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui.css', array(), THEMIFY_VERSION);
                if (is_rtl()) {
                    wp_enqueue_style('themify-builder-admin-ui-rtl', THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui-rtl.css', array('themify-builder-admin-ui'), THEMIFY_VERSION);
                }
                wp_enqueue_style('themify-icons', THEMIFY_URI . '/themify-icons/themify-icons.css', array(), THEMIFY_VERSION);
                wp_enqueue_style('google-fonts-builder', themify_https_esc('http://fonts.googleapis.com/css') . '?family=Open+Sans:400,300,600|Montserrat');
                wp_enqueue_style('themify-colorpicker', THEMIFY_URI . '/css/jquery.minicolors.css'); // from themify framework
                // Icon picker
                wp_enqueue_script('themify-font-icons-js', THEMIFY_URI . '/js/themify.font-icons-select.js', array('jquery'), THEMIFY_VERSION, true);
                wp_localize_script('themify-font-icons-js', 'themifyIconPicker', array(
                    'icons_list' => THEMIFY_URI . '/fontawesome/list.html',
                ));

                do_action('themify_builder_admin_enqueue', $this);
            }

            if (Themify_Builder_Model::is_frontend_editor_page()) {

                if (class_exists('Jetpack_VideoPress')) {
                    // Load this so submit_button() is available in VideoPress' print_media_templates().
                    require_once ABSPATH . 'wp-admin/includes/template.php';
                }

                $enqueue_scripts = array(
                    'underscore',
                    'jquery-ui-core',
                    'jquery-ui-accordion',
                    'jquery-ui-droppable',
                    'jquery-ui-sortable',
                    'jquery-ui-resizable',
                    'jquery-ui-tooltip',
                    'jquery-effects-core',
                    'media-upload',
                    'jquery-ui-dialog',
                    'wpdialogs',
                    'wpdialogs-popup',
                    'wplink',
                    'word-count',
                    'editor',
                    'quicktags',
                    'wp-fullscreen',
                    'admin-widgets',
                    'themify-colorpicker',
                    'themify-builder-google-webfont',
                    'themify-builder-undo-manager-js',
                    'themify-builder-common-js',
                    'themify-builder-front-ui-js',
                    'jss'
                );

                // For editor
                wp_enqueue_style('buttons');

                // is mobile version
                if ($this->isMobile()) {
                    wp_register_script('themify-builder-mobile-ui-js', THEMIFY_BUILDER_URI . "/js/jquery.ui.touch-punch.js", array('jquery'), THEMIFY_VERSION, true);
                    wp_enqueue_script('jquery-ui-mouse');
                    wp_enqueue_script('themify-builder-mobile-ui-js');
                }

                foreach ($enqueue_scripts as $script) {
                    switch ($script) {
                        case 'admin-widgets':
                            wp_enqueue_script($script, admin_url('/js/widgets.min.js'), array('jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable'));
                            break;

                        case 'themify-colorpicker':
                            wp_enqueue_script($script, THEMIFY_URI . '/js/jquery.minicolors.js', array('jquery')); // grab from themify framework
                            break;

                        case 'themify-builder-google-webfont':
                            wp_enqueue_script($script, themify_https_esc('http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js'));
                            break;

                        case 'themify-builder-undo-manager-js':
                            wp_enqueue_script($script, THEMIFY_BUILDER_URI . '/js/undomanager.js', array('jquery'));
                            break;

                        case 'themify-builder-common-js':
                            // front ui js
                            wp_register_script($script, THEMIFY_BUILDER_URI . "/js/themify.builder.common.js", array('jquery'), THEMIFY_VERSION, true);
                            wp_enqueue_script($script);

                            wp_localize_script('themify-builder-common-js', 'themifyBuilderCommon', apply_filters('themify_builder_common_vars', array(
                                'text_no_localStorage' =>
                                __("Your browser does not support this feature. Please use a modern browser such as Google Chrome or Safari.", 'themify'),
                                'text_confirm_data_paste' => __('This will overwrite the data. Ok to proceed?', 'themify'),
                                'text_alert_wrong_paste' => __('Error: Paste valid data only (paste row data to row, sub-row data to sub-row, module data to module).', 'themify')
                            )));
                            break;

                        case 'jss':
                            wp_register_script($script, THEMIFY_BUILDER_URI . "/js/jss.min.js", null, THEMIFY_VERSION, true);
                            wp_enqueue_script($script);
                            break;

                        case 'themify-builder-front-ui-js':
                            // front ui js
                            wp_register_script($script, THEMIFY_BUILDER_URI . "/js/themify.builder.front.ui.js", array('jquery', 'jquery-ui-tabs', 'themify-builder-common-js', 'jss'), THEMIFY_VERSION, true);
                            wp_enqueue_script($script);

                            $gutterClass = Themify_Builder_Model::get_grid_settings('gutter_class');
                            wp_localize_script($script, 'themifyBuilder', apply_filters('themify_builder_ajax_front_vars', array(
                                'ajaxurl' => admin_url('admin-ajax.php'),
                                'isTouch' => themify_is_touch() ? 'true' : 'false',
                                'tfb_load_nonce' => wp_create_nonce('tfb_load_nonce'),
                                'tfb_url' => THEMIFY_BUILDER_URI,
                                'post_ID' => get_the_ID(),
                                'dropPlaceHolder' => __('drop module here', 'themify'),
                                'draggerTitleMiddle' => __('Drag left/right to change columns', 'themify'),
                                'draggerTitleLast' => __('Drag left to add columns', 'themify'),
                                'moduleDeleteConfirm' => __('Press OK to remove this module', 'themify'),
                                'rowDeleteConfirm' => __('Press OK to remove this row', 'themify'),
                                'toggleOn' => __('Turn On Builder', 'themify'),
                                'toggleOff' => __('Turn Off Builder', 'themify'),
                                'confirm_on_turn_off' => __('Do you want to save the changes made to this page?', 'themify'),
                                'confirm_on_duplicate_page' => __('Save the Builder before duplicating this page?', 'themify'),
                                'confirm_on_unload' => __('You have unsaved data.', 'themify'),
                                'isFrontend' => 'true',
                                // TODO: remove these (both here and backend)
                                'textImportBuilder' => __('Import From', 'themify'),
                                'textRowStyling' => __('Row Styling', 'themify'),
                                'textColumnStyling' => __('Column Styling', 'themify'),
                                'load_layout_title' => __('Layouts', 'themify'),
                                'save_as_layout_title' => __('Save as Layout', 'themify'),
                                'text_import_module_data' => __('Import Module'),
                                'text_export_module_data' => __('Export Module'),
                                'text_import_row_data' => __('Import Row'),
                                'text_export_row_data' => __('Export Row'),
                                'text_import_sub_row_data' => __('Import Sub-Row'),
                                'text_export_sub_row_data' => __('Export Sub-Row'),
                                // END TODO
                                'importFileConfirm' => __('This import will override all current Builder data. Press OK to continue', 'themify'),
                                'confirm_template_selected' => __('Would you like to replace or append the layout?', 'themify'),
                                'confirm_delete_layout' => __('Are you sure want to delete this layout ?', 'themify'),
                                'isThemifyTheme' => $this->is_themify_theme() ? 'true' : 'false',
                                'gutterClass' => $gutterClass,
                                'subRowDeleteConfirm' => __('Press OK to remove this sub row', 'themify'),
                                'disableShortcuts' => themify_check('setting-page_builder_disable_shortcuts'),
                                // for live styling
                                'webSafeFonts' => themify_get_web_safe_font_list(true),
                                'errorSaveBuilder' => esc_html__('Error saving. Please save again.', 'themify'),
                                // Revisions
                                'enterRevComment' => esc_html__('Add optional revision comment:', 'themify'),
                                'confirmRestoreRev' => esc_html__('Save the current state as a revision before replacing?', 'themify'),
                                'confirmDeleteRev' => esc_html__('Are you sure want to delete this revision', 'themify')
                            )));
                            wp_localize_script($script, 'themify_builder_plupload_init', $this->get_builder_plupload_init());
                            break;

                        default:
                            wp_enqueue_script($script);
                            break;
                    }
                }
            }
        }

        /**
         * Add element via ajax
         * Drag / drop / add + button
         */
        function add_element_ajaxify() {
            check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');

            $template_name = $_POST['tfb_template_name'];

            if ('module_front' == $template_name) {
                $mod = array('mod_name' => $_POST['tfb_module_name']);
                $this->get_template_module($mod);
            }

            die();
        }

        public function slider_live_styling() {
            check_ajax_referer('tfb_load_nonce', 'nonce');

            $bg_slider_data = $_POST['tfb_background_slider_data'];

            $row_or_col = array(
                'styling' => array(
                    'background_slider' => urldecode($bg_slider_data['shortcode']),
                    'background_type' => 'slider',
                    'background_slider_mode' => $bg_slider_data['mode'],
                    'background_slider_size' => $bg_slider_data['size'],
                )
            );

            $this->do_slider_background($row_or_col, $bg_slider_data['order'], $bg_slider_data['size'], $bg_slider_data['type']);

            die();
        }

        /**
         * Duplicate page
         */
        function duplicate_page_ajaxify() {
            global $themifyBuilderDuplicate;
            check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');
            $post_id = (int) $_POST['tfb_post_id'];
            $post = get_post($post_id);
            $themifyBuilderDuplicate->edit_link = $_POST['tfb_is_admin'];
            $themifyBuilderDuplicate->duplicate($post);
            $response['status'] = 'success';
            $response['new_url'] = $themifyBuilderDuplicate->new_url;
            echo json_encode($response);
            die();
        }

        /**
         * Render component import form in lightbox
         */
        function imp_component_data_lightbox_options_ajaxify() {
            check_ajax_referer('tfb_load_nonce', 'nonce');

            $component = $_POST['component'];

            $id = '';
            $label = '';
            $description = '';

            switch ($component) {
                case 'row':
                    $id = 'tfb_imp_row_data_field';
                    $label = __('Row data', 'themify');
                    $description = __('Paste row data here', 'themify');
                    break;

                case 'sub-row':
                    $id = 'tfb_imp_sub_row_data_field';
                    $label = __('Sub-Row data', 'themify');
                    $description = __('Paste sub-row data here', 'themify');
                    break;

                case 'module':
                    $id = 'tfb_imp_module_data_field';
                    $label = __('Module data', 'themify');
                    $description = __('Paste module data here', 'themify');
                    break;
            }

            $fields = array(
                array(
                    'id' => $id,
                    'type' => 'textarea',
                    'label' => $label,
                    'class' => 'xlarge',
                    'description' => $description,
                    'rows' => 13
                )
            );

            include_once THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-imp-component-form.php';
            die();
        }

        /**
         * Render component export form in lightbox.
         */
        function exp_component_data_lightbox_options_ajaxify() {
            check_ajax_referer('tfb_load_nonce', 'nonce');

            $component = $_POST['component'];

            $id = '';
            $label = '';
            $description = '';

            switch ($component) {
                case 'row':
                    $id = 'tfb_exp_row_data_field';
                    $label = __('Row data', 'themify');
                    $description = __('You can copy & paste this data to another Builder site', 'themify');
                    break;

                case 'sub-row':
                    $id = 'tfb_exp_sub_row_data_field';
                    $label = __('Sub-Row data', 'themify');
                    $description = __('You can copy & paste this data to another Builder site', 'themify');
                    break;

                case 'module':
                    $id = 'tfb_exp_module_data_field';
                    $label = __('Module data', 'themify');
                    $description = __('You can copy & paste this data to another Builder site', 'themify');
                    break;
            }

            $fields = array(
                array(
                    'id' => $id,
                    'type' => 'textarea',
                    'label' => $label,
                    'class' => 'xlarge',
                    'description' => $description,
                    'rows' => 13 //300px height
                )
            );

            include_once THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-exp-component-form.php';
            die();
        }

        function shortcode_preview() {
            check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');
            if (isset($_POST['shortcode']) && $_POST['shortcode']) {
                $shortcode = sanitize_text_field($_POST['shortcode']);
                $images = $this->get_images_from_gallery_shortcode($shortcode);
                if (!empty($images)) {
                    $html = '<div class="themify_builder_shortcode_preview">';
                    foreach ($images as $image) {
                        $img_data = wp_get_attachment_image_src($image->ID, 'thumbnail');
                        $html.='<img src="' . $img_data[0] . '" width="50" height="50" />';
                    }
                    $html.='</div>';
                    echo $html;
                }
            }
            wp_die();
        }

        /**
         * Add wp editor element
         */
        function add_wp_editor_ajaxify() {
            check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');

            $txt_id = $_POST['txt_id'];
            $class = $_POST['txt_class'];
            $txt_name = $_POST['txt_name'];
            $txt_val = stripslashes_deep($_POST['txt_val']);
            wp_editor($txt_val, $txt_id, array('textarea_name' => $txt_name, 'editor_class' => $class, 'textarea_rows' => 20));

            die();
        }

        /**
         * Load Editable builder grid
         */
        function load_toggle_frontend_ajaxify() {
            check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');

            $response = array();
            $post_ids = isset($_POST['tfb_post_ids']) ? $_POST['tfb_post_ids'] : array();
            global $post;

            foreach ($post_ids as $k => $id) {
                $sanitize_id = (int) $id;
                $post = get_post($sanitize_id);
                setup_postdata($post);


                $builder_data = $this->get_builder_data($post->ID);
                $response[$k]['builder_id'] = $post->ID;
                $response[$k]['markup'] = $this->retrieve_template('builder-output.php', array('builder_output' => $builder_data, 'builder_id' => $post->ID), '', '', false);
            } wp_reset_postdata();

            echo json_encode($response);

            die();
        }

        /**
         * Load module partial when update live content
         */
        function load_module_partial_ajaxify() {
            check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');
            global $post;

            $temp_post = $post;
            $post_id = (int) $_POST['tfb_post_id'];
            $post = get_post($post_id);
            $module_slug = $_POST['tfb_module_slug'];
            $module_settings = json_decode(stripslashes($_POST['tfb_module_data']), true);
            $identifier = array(uniqid());
            $response = array();

            $new_modules = array(
                'mod_name' => $module_slug,
                'mod_settings' => $module_settings
            );

            $response['html'] = $this->get_template_module($new_modules, $post_id, false, true, null, $identifier);
            $response['gfonts'] = $this->get_custom_google_fonts();

            $post = $temp_post;
            echo json_encode($response);

            die();
        }

        /**
         * Load row partial when update live content
         */
        function load_row_partial_ajaxify() {
            check_ajax_referer('tfb_load_nonce', 'nonce');
            global $themify;

            $post_id = (int) $_POST['post_id'];
            $row = stripslashes_deep($_POST['row']);
            $uniqid = uniqid();
            $response = array();

            if (isset($row['row_order']))
                unset($row['row_order']);

            $response['html'] = $this->get_template_row($uniqid, $row, $post_id);
            $response['gfonts'] = $this->get_custom_google_fonts();

            echo json_encode($response);

            die();
        }

        /**
         * Render duplicate row
         */
        function render_duplicate_row_ajaxify() {
            check_ajax_referer('tfb_load_nonce', 'nonce');

            $row = stripslashes_deep($_POST['row']);
            $post_id = $_POST['id'];
            $response = array();
            $uniqid = uniqid();

            if (isset($row['row_order']))
                unset($row['row_order']);

            $response['html'] = $this->get_template_row($uniqid, $row, $post_id);

            echo json_encode($response);

            die();
        }

        public static function remove_cache($post_id, $tag = false, array $args = array()) {
            TFCache::remove_cache($tag, $post_id, $args);
        }

        /**
         * Save builder main data
         */
        function save_data_builder() {
            check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');

            // Information about writing process.
            $results = array();

            $saveto = $_POST['tfb_saveto'];
            $ids = json_decode(stripslashes_deep($_POST['ids']), true);

            if (is_array($ids) && count($ids) > 0) {
                foreach ($ids as $v) {
                    $post_id = isset($v['id']) ? $v['id'] : '';
                    $post_data = ( isset($v['data']) && is_array($v['data']) && count($v['data']) > 0 ) ? $v['data'] : array();
                    if ('main' == $saveto) {

                        $GLOBALS['ThemifyBuilder_Data_Manager']->save_data($post_data, $post_id);

                        if (!empty($post_data)) {
                            // Write Stylesheet
                            $results = $this->write_stylesheet(array('id' => $post_id, 'data' => $post_data));
                        }
                        do_action('themify_builder_save_data', $post_id, $this->meta_key, $post_data); // hook save data

                        self::remove_cache($post_id);
                    } else {
                        $transient = $this->meta_key_transient . '_' . $post_id;
                        set_transient($transient, $post_data, 60 * 60);
                    }
                }
            }

            wp_send_json_success($results);
        }

        /**
         * Hook to content filter to show builder output
         * @param $content
         * @return string
         */
        function builder_show_on_front($content) {
            global $post, $wp_query;
            // Exclude builder output in admin post list mode excerpt, Dont show builder on product single description
            if (( is_admin() && !defined('DOING_AJAX') ) || ( is_post_type_archive() && !is_post_type_archive('product') ) || post_password_required() || isset($wp_query->query_vars['product_cat']) || is_tax('product_tag') || (is_singular('product') && 'product' == get_post_type())
            ) {
                return $content;
            }

            if (is_post_type_archive('product') && get_query_var('paged') == 0 && $this->builder_is_plugin_active('woocommerce/woocommerce.php')) {
                $post = get_post(woocommerce_get_page_id('shop'));
            }

            if (!is_object($post))
                return $content;

            $display = apply_filters('themify_builder_display', true, $post->ID);
            if (false === $display) {
                return $content;
            }

            //the_excerpt
            global $wp_current_filter;
            if (in_array('get_the_excerpt', $wp_current_filter)) {
                if ($content) {
                    return $content;
                }
                return $this->get_first_text($post->ID);
            }

            // Infinite-loop prevention
            if (empty($this->post_ids)) {
                $this->post_ids[] = $post->ID;
            } elseif (in_array($post->ID, $this->post_ids)) {
                // we have already rendered this, go back.
                return $content;
            }

            // Builder display position
            $display_position = apply_filters('themify_builder_display_position', 'below', $post->ID);

            $this->post_ids[] = $post->ID;

            $builder_data = $this->get_builder_data($post->ID);

            if (!is_array($builder_data) || strpos($content, '#more-')) {
                $builder_data = array();
            }
            self::$post_id = get_the_ID();
            if ($this->in_the_loop) {
                $builder_output = $this->retrieve_template('builder-output-in-the-loop.php', array('builder_output' => $builder_data, 'builder_id' => $post->ID), '', '', false);
            } else {
                $builder_output = $this->retrieve_template('builder-output.php', array('builder_output' => $builder_data, 'builder_id' => $post->ID), '', '', false);
            }

            if ('above' == $display_position) {
                $content = $builder_output . $content;
            } else {
                $content .= $builder_output;
            }

            $this->post_ids = array_unique($this->post_ids);
            if (array_shift($this->post_ids) == $post->ID) {
                // the loop is finished, reset the ID list
                $this->post_ids = array();
            }

            return $content;
        }

        /**
         * Display module panel on frontend edit
         */
        function builder_module_panel_frontedit() {
            include_once THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-module-panel.php';
        }

        /**
         * Loads JS templates for front-end editor.
         */
        public function load_javascript_template_front() {
            include_once( sprintf("%s/themify-builder-js-tmpl-front.php", THEMIFY_BUILDER_INCLUDES_DIR) );
        }

        /**
         * Loads JS templates for WordPress admin dashboard editor.
         */
        public function load_javascript_template_admin() {
            include_once( sprintf("%s/themify-builder-js-tmpl-admin.php", THEMIFY_BUILDER_INCLUDES_DIR) );
            include_once( sprintf("%s/themify-builder-js-tmpl-form.php", THEMIFY_BUILDER_INCLUDES_DIR) );
        }

        /**
         * Loads JS templates shared between front-end and WordPress admin dashboard editors.
         */
        public function load_javascript_template_common() {
            include_once( sprintf("%s/themify-builder-js-tmpl-common.php", THEMIFY_BUILDER_INCLUDES_DIR) );
        }

        /**
         * Get initialization parameters for plupload. Filtered through themify_builder_plupload_init_vars.
         * @return mixed|void
         * @since 1.4.2
         */
        function get_builder_plupload_init() {
            return apply_filters('themify_builder_plupload_init_vars', array(
                'runtimes' => 'html5,flash,silverlight,html4',
                'browse_button' => 'themify-builder-plupload-browse-button', // adjusted by uploader
                'container' => 'themify-builder-plupload-upload-ui', // adjusted by uploader
                'drop_element' => 'drag-drop-area', // adjusted by uploader
                'file_data_name' => 'async-upload', // adjusted by uploader
                'multiple_queues' => true,
                'max_file_size' => wp_max_upload_size() . 'b',
                'url' => admin_url('admin-ajax.php'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
                'filters' => array(array(
                        'title' => __('Allowed Files', 'themify'),
                        'extensions' => 'jpg,jpeg,gif,png,zip,txt'
                    )),
                'multipart' => true,
                'urlstream_upload' => true,
                'multi_selection' => false, // added by uploader
                // additional post data to send to our ajax hook
                'multipart_params' => array(
                    '_ajax_nonce' => '', // added by uploader
                    'action' => 'themify_builder_plupload_action', // the ajax action name
                    'imgid' => 0 // added by uploader
                )
            ));
        }

        /**
         * Inject plupload initialization variables in Javascript
         * @since 1.4.2
         */
        function plupload_front_head() {
            wp_localize_script('themify-builder-front-ui-js', 'themify_builder_plupload_init', $this->get_builder_plupload_init());
        }

        /**
         * Plupload initialization parameters
         * @since 1.4.2
         */
        function plupload_admin_head() {
            wp_localize_script('themify-builder-admin-ui-js', 'themify_builder_plupload_init', $this->get_builder_plupload_init());
        }

        /**
         * Plupload ajax action
         */
        function builder_plupload() {
            // check ajax nonce
            $imgid = $_POST['imgid'];
            //check_ajax_referer( $imgid . 'themify-builder-plupload' );
            check_ajax_referer('tfb_load_nonce');

            /** If post ID is set, uploaded image will be attached to it. @var String */
            $postid = $_POST['topost'];

            /** Handle file upload storing file|url|type. @var Array */
            $file = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'themify_builder_plupload_action'));

            //let's see if it's an image, a zip file or something else
            $ext = explode('/', $file['type']);

            // Import routines
            if ('zip' == $ext[1] || 'rar' == $ext[1] || 'plain' == $ext[1]) {

                $url = wp_nonce_url('admin.php?page=themify');

                if (false === ( $creds = request_filesystem_credentials($url) )) {
                    return true;
                }
                if (!WP_Filesystem($creds)) {
                    request_filesystem_credentials($url, '', true);
                    return true;
                }

                global $wp_filesystem;

                if ('zip' == $ext[1] || 'rar' == $ext[1]) {
                    $destination = wp_upload_dir();
                    $destination_path = $destination['path'];

                    unzip_file($file['file'], $destination_path);
                    if ($wp_filesystem->exists($destination_path . '/builder_data_export.txt')) {
                        $data = $wp_filesystem->get_contents($destination_path . '/builder_data_export.txt');

                        if (is_serialized($data)) {
                            /* serialized string */
                            $data = @unserialize($data);
                        } else {
                            /* must be the new JSON format */
                            $data = json_decode($data);
                        }
                        $GLOBALS['ThemifyBuilder_Data_Manager']->save_data($data, $postid, true);

                        $wp_filesystem->delete($destination_path . '/builder_data_export.txt');
                        $wp_filesystem->delete($file['file']);
                    } else {
                        _e('Data could not be loaded', 'themify');
                    }
                } else {
                    if ($wp_filesystem->exists($file['file'])) {
                        $data = $wp_filesystem->get_contents($file['file']);

                        if (is_serialized($data)) {
                            /* serialized string */
                            $data = @unserialize($data);
                        } else {
                            /* must be the new JSON format */
                            $data = json_decode($data);
                        }
                        // set data here
                        $GLOBALS['ThemifyBuilder_Data_Manager']->save_data($data, $postid, true);

                        $wp_filesystem->delete($file['file']);
                    } else {
                        _e('Data could not be loaded', 'themify');
                    }
                }
            } else {
                // Insert into Media Library
                // Set up options array to add this file as an attachment
                $attachment = array(
                    'post_mime_type' => sanitize_mime_type($file['type']),
                    'post_title' => str_replace('-', ' ', sanitize_file_name(pathinfo($file['file'], PATHINFO_FILENAME))),
                    'post_status' => 'inherit'
                );

                if ($postid)
                    $attach_id = wp_insert_attachment($attachment, $file['file'], $postid);

                // Common attachment procedures
                require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
                $attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);

                if ($postid) {
                    $large = wp_get_attachment_image_src($attach_id, 'large');
                    $thumb = wp_get_attachment_image_src($attach_id, 'thumbnail');

                    //Return URL for the image field in meta box
                    $file['large_url'] = $large[0];
                    $file['thumb'] = $thumb[0];
                    $file['id'] = $attach_id;
                }
            }

            $file['type'] = $ext[1];
            // send the uploaded file url in response
            echo json_encode($file);
            exit;
        }

        /**
         * Display Toggle themify builder
         * wp admin bar
         */
        function builder_admin_bar_menu($wp_admin_bar) {
            if (is_admin() || !Themify_Builder_Model::is_frontend_editor_page() || ( is_post_type_archive() && !is_post_type_archive('product') ) || !is_admin_bar_showing() || isset($wp_query->query_vars['product_cat']) || is_tax('product_tag')) {
                return;
            }
            $p = get_queried_object(); //get_the_ID can back wrong post id
            $post_id = isset($p->ID) ? $p->ID : false;
            unset($p);
            if (!$post_id || !current_user_can('edit_page', $post_id))
                return;

            $args = array(
                array(
                    'id' => 'themify_builder',
                    'title' => sprintf('<span class="themify_builder_front_icon"></span> %s', __('Themify Builder', 'themify')),
                    'href' => '#'
                ),
                array(
                    'id' => 'toggle_themify_builder',
                    'parent' => 'themify_builder',
                    'title' => __('Turn On Builder', 'themify'),
                    'href' => '#',
                    'meta' => array('class' => 'toggle_tf_builder')
                ),
                array(
                    'id' => 'duplicate_themify_builder',
                    'parent' => 'themify_builder',
                    'title' => __('Duplicate This Page', 'themify'),
                    'href' => '#',
                    'meta' => array('class' => 'themify_builder_dup_link')
                )
            );


            $help_args = array(
                array(
                    'id' => 'help_themify_builder',
                    'parent' => 'themify_builder',
                    'title' => __('Help', 'themify'),
                    'href' => '//themify.me/docs/builder',
                    'meta' => array('target' => '_blank', 'class' => '')
                )
            );

            if (is_singular() || is_page()) {
                $import_args = array(
                    array(
                        'id' => 'import_themify_builder',
                        'parent' => 'themify_builder',
                        'title' => __('Import From', 'themify'),
                        'href' => '#'
                    ),
                    // Sub Menu
                    array(
                        'id' => 'from_existing_pages_themify_builder',
                        'parent' => 'import_themify_builder',
                        'title' => __('Existing Pages', 'themify'),
                        'href' => '#',
                        'meta' => array('class' => 'themify_builder_import_page')
                    ),
                    array(
                        'id' => 'from_existing_posts_themify_builder',
                        'parent' => 'import_themify_builder',
                        'title' => __('Existing Posts', 'themify'),
                        'href' => '#',
                        'meta' => array('class' => 'themify_builder_import_post')
                    ),
                    array(
                        'id' => 'import_export_themify_builder',
                        'parent' => 'themify_builder',
                        'title' => __('Import / Export', 'themify'),
                        'href' => '#'
                    ),
                    // Sub Menu
                    array(
                        'id' => 'import_file_themify_builder',
                        'parent' => 'import_export_themify_builder',
                        'title' => __('Import', 'themify'),
                        'href' => '#',
                        'meta' => array('class' => 'themify_builder_import_file')
                    ),
                    array(
                        'id' => 'export_file_themify_builder',
                        'parent' => 'import_export_themify_builder',
                        'title' => __('Export', 'themify'),
                        'href' => wp_nonce_url('?themify_builder_export_file=true&postid=' . $post_id, 'themify_builder_export_nonce'),
                        'meta' => array('class' => 'themify_builder_export_file')
                    ),
                    array(
                        'id' => 'layout_themify_builder',
                        'parent' => 'themify_builder',
                        'title' => __('Layouts', 'themify'),
                        'href' => '#'
                    ),
                    // Sub Menu
                    array(
                        'id' => 'load_layout_themify_builder',
                        'parent' => 'layout_themify_builder',
                        'title' => __('Load Layout', 'themify'),
                        'href' => '#',
                        'meta' => array('class' => 'themify_builder_load_layout')
                    ),
                    array(
                        'id' => 'save_layout_themify_builder',
                        'parent' => 'layout_themify_builder',
                        'title' => __('Save as Layout', 'themify'),
                        'href' => '#',
                        'meta' => array('class' => 'themify_builder_save_layout')
                    ),
                );
                $args = array_merge($args, apply_filters('themify_builder_admin_bar_menu_single_page', $import_args));
            }

            $args = array_merge($args, $help_args);

            foreach ($args as $arg) {
                $wp_admin_bar->add_node($arg);
            }
        }

        /**
         * Switch to frontend
         * @param int $post_id
         */
        function switch_frontend($post_id) {
            //verify post is not a revision
            if (!wp_is_post_revision($post_id)) {
                $redirect = isset($_POST['builder_switch_frontend']) ? $_POST['builder_switch_frontend'] : 0;
                self::remove_cache($post_id);
                // redirect to frontend
                if (1 == $redirect) {
                    $_POST['builder_switch_frontend'] = 0;
                    $post_url = get_permalink($post_id);
                    wp_redirect(themify_https_esc($post_url) . '#builder_active');
                    exit;
                }
            }
        }

        /**
         * Editing module panel in frontend
         * @param $mod_name
         * @param $mod_settings
         */
        function module_edit_panel_front($mod_name, $mod_settings) {
            ?>
            <div class="module_menu_front">
                <ul class="themify_builder_dropdown_front">
                    <li class="themify_module_menu"><span class="ti-menu"></span>
                        <ul>
                            <li><a href="#" data-title="<?php _e('Export', 'themify') ?>" rel="themify-tooltip-bottom"
                                   class="themify_builder_export_component ti-export" data-component="module">
                                       <?php _e('Export', 'themify') ?>
                                </a></li>
                            <li><a href="#" data-title="<?php _e('Import', 'themify') ?>" rel="themify-tooltip-bottom"
                                   class="themify_builder_import_component ti-import" data-component="module">
                                       <?php _e('Import', 'themify') ?>
                                </a></li>
                            <li class="separator">
                                <div></div>
                            </li>
                            <li><a href="#" data-title="<?php _e('Copy', 'themify') ?>" rel="themify-tooltip-bottom"
                                   class="themify_builder_copy_component ti-files" data-component="module">
                                       <?php _e('Copy', 'themify') ?>
                                </a></li>
                            <li><a href="#" data-title="<?php _e('Paste', 'themify') ?>" rel="themify-tooltip-bottom"
                                   class="themify_builder_paste_component ti-clipboard" data-component="module">
                                       <?php _e('Paste', 'themify') ?>
                                </a></li>
                            <li class="separator"><div></div></li>
                            <li><a href="#" data-title="<?php _e('Edit', 'themify') ?>" rel="themify-tooltip-bottom"
                                   class="themify_module_options" data-module-name="<?php echo esc_attr($mod_name); ?>">
                                       <?php _e('Edit', 'themify') ?>
                                </a></li>
                            <li><a href="#" data-title="<?php _e('Styling', 'themify') ?>" rel="themify-tooltip-bottom"
                                   class="themify_builder_module_styling js--themify_builder_module_styling ti-brush" data-module-name="<?php echo esc_attr($mod_name); ?>">
                                       <?php _e('Styling', 'themify') ?>
                                </a></li>
                            <li><a href="#" data-title="<?php _e('Duplicate', 'themify') ?>" rel="themify-tooltip-bottom"
                                   class="themify_module_duplicate">
                                       <?php _e('Duplicate', 'themify') ?>
                                </a></li>
                            <li><a href="#" data-title="<?php _e('Delete', 'themify') ?>" rel="themify-tooltip-bottom"
                                   class="themify_module_delete">
                                       <?php _e('Delete', 'themify') ?>
                                </a></li>
                        </ul>
                    </li>
                </ul>
                <div class="front_mod_settings mod_settings_<?php echo esc_attr($mod_name); ?>" data-mod-name="<?php echo esc_attr($mod_name); ?>">
                    <script type="text/json"><?php echo json_encode($this->clean_json_bad_escaped_char($mod_settings)); ?></script>
                </div>
            </div>
            <div class="themify_builder_data_mod_name"><?php echo Themify_Builder_model::get_module_name($mod_name); ?></div>
            <?php
        }

        /**
         * Add Builder body class
         * @param $classes
         * @return mixed|void
         */
        function body_class($classes) {
            if (Themify_Builder_Model::is_frontend_editor_page()) {
                $classes[] = 'frontend';
            }

            if (themify_is_touch()) {
                $classes[] = 'istouch';
            }
            // return the $classes array
            return apply_filters('themify_builder_body_class', $classes);
        }

        /**
         * Just print the shortcode text instead of output html
         * @param array $array
         * @return array
         */
        function return_text_shortcode($array) {
            if (count($array) > 0) {
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $this->return_text_shortcode($value);
                    } else {
                        $array[$key] = str_replace("[", "&#91;", $value);
                        $array[$key] = str_replace("]", "&#93;", $value);
                    }
                }
            } else {
                $array = array();
            }
            return $array;
        }

        /**
         * Clean bad escape char for json
         * @param array $array 
         * @return array
         */
        function clean_json_bad_escaped_char($array) {
            if (count($array) > 0) {
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $this->clean_json_bad_escaped_char($value);
                    } else {
                        $array[$key] = str_replace("<wbr />", "<wbr>", $value);
                    }
                }
            } else {
                $array = array();
            }
            return $array;
        }

        /**
         * Retrieve builder templates
         * @param $template_name
         * @param array $args
         * @param string $template_path
         * @param string $default_path
         * @param bool $echo
         * @return string
         */
        function retrieve_template($template_name, $args = array(), $template_path = '', $default_path = '', $echo = true) {
            ob_start();
            $this->get_template($template_name, $args, $template_path = '', $default_path = '');
            if ($echo)
                echo ob_get_clean();
            else
                return ob_get_clean();
        }

        /**
         * Get template builder
         * @param $template_name
         * @param array $args
         * @param string $template_path
         * @param string $default_path
         */
        function get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
            if ($args && is_array($args))
                extract($args);

            $located = $this->locate_template($template_name, $template_path, $default_path);

            include( $located );
        }

        /**
         * Locate a template and return the path for inclusion.
         *
         * This is the load order:
         *
         * 		yourtheme		/	$template_path	/	$template_name
         * 		$default_path	/	$template_name
         */
        function locate_template($template_name, $template_path = '', $default_path = '') {
            $template = '';
            foreach ($this->get_directory_path('templates') as $dir) {
                if (is_file($dir . $template_name)) {
                    $template = $dir . $template_name;
                }
            }

            // Get default template
            if (!$template)
                $template = $default_path . $template_name;

            // Return what we found
            return apply_filters('themify_builder_locate_template', $template, $template_name, $template_path);
        }

        /**
         * Get template for module
         * @param $mod
         * @param bool $echo
         * @param bool $wrap
         * @param null $class
         * @param array $identifier
         * @return bool|string
         */
        function get_template_module($mod, $builder_id = 0, $echo = true, $wrap = true, $class = null, $identifier = array()) {
            /* allow addons to control the display of the modules */
            $display = apply_filters('themify_builder_module_display', true, $mod, $builder_id, $identifier);
            if (false === $display) {
                return false;
            }

            $output = '';
            $mod['mod_name'] = isset($mod['mod_name']) ? $mod['mod_name'] : '';
            $mod['mod_settings'] = isset($mod['mod_settings']) ? $mod['mod_settings'] : array();

            $mod_id = $mod['mod_name'] . '-' . $builder_id . '-' . implode('-', $identifier);
            $output .= PHP_EOL; // add line break
            // check whether module active or not
            if (!Themify_Builder_Model::check_module_active($mod['mod_name']))
                return false;

            if ($wrap) {
                ob_start();
                ?>
                <div class="themify_builder_module_front clearfix module-<?php echo esc_attr($mod['mod_name']); ?> active_module <?php echo esc_attr($class); ?>" data-module-name="<?php echo esc_attr($mod['mod_name']); ?>">
                    <div class="themify_builder_module_front_overlay"></div>
                    <?php
                    themify_builder_edit_module_panel($mod['mod_name'], $mod['mod_settings']);
                    $output .= ob_get_clean();
                }

                $module_args = apply_filters('themify_builder_module_args', array(
                    'before_title' => '<h3 class="module-title">',
                    'after_title' => '</h3>',
                ));
                $mod['mod_settings'] = wp_parse_args($mod['mod_settings'], $module_args);

                // render the module
                $output .= Themify_Builder_Model::$modules[$mod['mod_name']]->render($mod_id, $builder_id, $mod['mod_settings']);

                $style_id = '.themify_builder .' . $mod_id;
                $post = get_post($builder_id);
                if (
                        Themify_Builder_Model::is_frontend_editor_page() || ( isset($_GET['themify_builder_infinite_scroll']) && 'yes' == $_GET['themify_builder_infinite_scroll'] ) || $this->is_front_end_style_inline || ( is_object($post) && $post->post_type == 'tbuilder_layout_part' )
                ) {
                    $output .= $this->get_custom_styling($style_id, $mod['mod_name'], $mod['mod_settings']);
                }

                if ($wrap)
                    $output .= '</div>';

                // add line break
                $output .= PHP_EOL;

                if ($echo) {
                    echo $output;
                } else {
                    return $output;
                }
            }

            /**
             * Check whether theme loop template exist
             * @param string $template_name 
             * @param string $template_path 
             * @return boolean
             */
            function is_loop_template_exist($template_name, $template_path) {
                $template = locate_template(
                        array(
                            trailingslashit($template_path) . $template_name
                        )
                );

                if (!$template) {
                    return false;
                } else {
                    return true;
                }
            }

            /**
             * Get checkbox data
             * @param $setting
             * @return string
             */
            function get_checkbox_data($setting) {
                return implode(' ', explode('|', $setting));
            }

            /**
             * Return only value setting
             * @param $string 
             * @return string
             */
            function get_param_value($string) {
                $val = explode('|', $string);
                return $val[0];
            }

            /**
             * Get custom menus
             * @param int $term_id
             */
            function get_custom_menus($term_id) {
                $menu_list = '';
                ob_start();
                wp_nav_menu(array('menu' => $term_id));
                $menu_list .= ob_get_clean();

                return $menu_list;
            }

            /**
             * Display an additional column in categories list
             * @since 1.1.8
             */
            function taxonomy_header($cat_columns) {
                $cat_columns['cat_id'] = 'ID';
                return $cat_columns;
            }

            /**
             * Display ID in additional column in categories list
             * @since 1.1.8
             */
            function taxonomy_column_id($null, $column, $termid) {
                return $termid;
            }

            /**
             * Includes this custom post to array of cpts managed by Themify
             * @param Array
             * @return Array
             */
            function extend_post_types($types) {
                if (empty($this->public_post_types)) {
                    $this->public_post_types = array_unique(array_merge(
                                    $this->registered_post_types, array_values(get_post_types(array(
                        'public' => true,
                        '_builtin' => false,
                        'show_ui' => true,
                                    ))), array('post', 'page')
                    ));
                }

                return array_unique(array_merge($types, $this->public_post_types));
            }

            /**
             * Push the registered post types to object class
             * @param $type
             */
            function push_post_types($type) {
                array_push($this->registered_post_types, $type);
            }

            /**
             * Detect mobile browser
             */
            function isMobile() {
                return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
            }

            /**
             * Get images from gallery shortcode
             * @return object
             */
            function get_images_from_gallery_shortcode($shortcode) {
                preg_match('/\[gallery.*ids=.(.*).\]/', $shortcode, $ids);
                $ids = trim($ids[1], '\\');
                $ids = trim($ids, '"');
                $image_ids = explode(",", $ids);
                $orderby = $this->get_gallery_param_option($shortcode, 'orderby');
                $orderby = $orderby != '' ? $orderby : 'post__in';
                $order = $this->get_gallery_param_option($shortcode, 'order');
                $order = $order != '' ? $order : 'ASC';

                // Check if post has more than one image in gallery
                return get_posts(array(
                    'post__in' => $image_ids,
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'numberposts' => -1,
                    'orderby' => $orderby,
                    'order' => $order
                ));
            }

            /**
             * Get gallery shortcode options
             * @param $shortcode
             * @param $param
             */
            function get_gallery_param_option($shortcode, $param = 'link') {
                $pattern = '/\[gallery .*?(?=' . $param . ')' . $param . '=.([^\']+)./si';
                preg_match($pattern, $shortcode, $out);

                $out = isset($out[1]) ? explode('"', $out[1]) : array('');
                return $out[0];
            }

            /**
             * Reset builder query
             * @param $action
             */
            function reset_builder_query($action = 'reset') {
                if ('reset' == $action) {
                    remove_filter('the_content', array(&$this, 'builder_show_on_front'), 11);
                } elseif ('restore' == $action) {
                    add_filter('the_content', array(&$this, 'builder_show_on_front'), 11);
                }
            }

            /**
             * Check whether image script is in use or not
             * @return boolean
             */
            function is_img_php_disabled() {
                return themify_check('setting-img_settings_use'); // Themify FW setting name
            }

            /**
             * Checks whether the url is an img link, youtube, vimeo or not.
             * @param string $url
             * @return bool
             */
            function is_img_link($url) {
                $parsed_url = parse_url($url);
                $pathinfo = isset($parsed_url['path']) ? pathinfo($parsed_url['path']) : '';
                $extension = isset($pathinfo['extension']) ? strtolower($pathinfo['extension']) : '';
                $image_extensions = array('png', 'jpg', 'jpeg', 'gif');
                return in_array($extension, $image_extensions) || stripos('youtube', $url) || stripos('vimeo', $url);
            }

            /**
             * Get query page
             */
            function get_paged_query() {
                global $wp;
                $page = 1;
                $qpaged = get_query_var('paged');
                if (!empty($qpaged)) {
                    $page = $qpaged;
                } else {
                    $qpaged = wp_parse_args($wp->matched_query);
                    if (isset($qpaged['paged']) && $qpaged['paged'] > 0) {
                        $page = $qpaged['paged'];
                    }
                }
                return $page;
            }

            /**
             * Returns page navigation
             * @param string Markup to show before pagination links
             * @param string Markup to show after pagination links
             * @param object WordPress query object to use
             * @return string
             */
            function get_pagenav($before = '', $after = '', $query = false) {
                global $wpdb, $wp_query;

                if (false == $query) {
                    $query = $wp_query;
                }
                $request = $query->request;
                $posts_per_page = intval(get_query_var('posts_per_page'));
                $paged = intval($this->get_paged_query());
                $numposts = $query->found_posts;
                $max_page = $query->max_num_pages;
                $out = '';

                if (empty($paged) || $paged == 0) {
                    $paged = 1;
                }
                $pages_to_show = apply_filters('themify_filter_pages_to_show', 5);
                $pages_to_show_minus_1 = $pages_to_show - 1;
                $half_page_start = floor($pages_to_show_minus_1 / 2);
                $half_page_end = ceil($pages_to_show_minus_1 / 2);
                $start_page = $paged - $half_page_start;
                if ($start_page <= 0) {
                    $start_page = 1;
                }
                $end_page = $paged + $half_page_end;
                if (($end_page - $start_page) != $pages_to_show_minus_1) {
                    $end_page = $start_page + $pages_to_show_minus_1;
                }
                if ($end_page > $max_page) {
                    $start_page = $max_page - $pages_to_show_minus_1;
                    $end_page = $max_page;
                }
                if ($start_page <= 0) {
                    $start_page = 1;
                }

                if ($max_page > 1) {
                    $out .= $before . '<div class="pagenav clearfix">';
                    if ($start_page >= 2 && $pages_to_show < $max_page) {
                        $first_page_text = "&laquo;";
                        $out .= '<a href="' . esc_url(get_pagenum_link()) . '" title="' . esc_attr($first_page_text) . '" class="number">' . $first_page_text . '</a>';
                    }
                    if ($pages_to_show < $max_page)
                        $out .= get_previous_posts_link('&lt;');
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        if ($i == $paged) {
                            $out .= ' <span class="number current">' . $i . '</span> ';
                        } else {
                            $out .= ' <a href="' . esc_url(get_pagenum_link($i)) . '" class="number">' . $i . '</a> ';
                        }
                    }
                    if ($pages_to_show < $max_page)
                        $out .= get_next_posts_link('&gt;');
                    if ($end_page < $max_page) {
                        $last_page_text = "&raquo;";
                        $out .= '<a href="' . esc_url(get_pagenum_link($max_page)) . '" title="' . esc_attr($last_page_text) . '" class="number">' . $last_page_text . '</a>';
                    }
                    $out .= '</div>' . $after;
                }
                return $out;
            }

            /**
             * Reset builder filter before template content render
             */
            function do_reset_before_template_content_render() {
                //$this->reset_builder_query();
            }

            /**
             * Reset builder filter after template content render
             */
            function do_reset_after_template_content_render() {
                //$this->reset_builder_query('restore');
            }

            /**
             * Check is plugin active
             */
            function builder_is_plugin_active($plugin) {
                return in_array($plugin, apply_filters('active_plugins', get_option('active_plugins')));
            }

            /**
             * Include builder in search
             * @param string $where 
             * @return string
             */
            function do_search($where) {
                if (!is_admin()) {
                    if (is_search()) {
                        global $wpdb;
                        $query = get_search_query();
                        if (method_exists($wpdb, 'esc_like')) {
                            $query = $wpdb->esc_like($query);
                        } else {
                            /**
                             * If this is not WP 4.0 or above, use old method to escape db query.
                             * @since 2.0.2
                             */
                            $do = 'like';
                            $it = 'escape';
                            $query = call_user_func($do . '_' . $it, $query);
                        }
                        $types = Themify_Builder_Model::get_post_types();

                        $where .= " OR {$wpdb->posts}.ID IN (
							SELECT {$wpdb->postmeta}.post_id FROM {$wpdb->posts}, {$wpdb->postmeta}";

                        global $sitepress;
                        if (isset($sitepress) && method_exists($sitepress, 'get_current_language')) {
                            $current_language = $sitepress->get_current_language();
                            $where .= " LEFT JOIN {$wpdb->prefix}icl_translations ON( {$wpdb->prefix}icl_translations.element_id = {$wpdb->postmeta}.post_id )
							WHERE {$wpdb->prefix}icl_translations.language_code = '$current_language'
							AND";
                        } else {
                            $where .= ' WHERE'; // if WPML doesn't exist, execution enters this branch and is needed for proper query
                        }

                        $where .= " {$wpdb->postmeta}.meta_key = '_themify_builder_settings_json' AND `post_status`='publish'
							AND {$wpdb->postmeta}.meta_value LIKE '%$query%' AND {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
							AND {$wpdb->posts}.post_type IN ('" . implode("', '", $types) . "'))";
                    }
                }
                return $where;
            }

            /**
             * Builder Import Lightbox
             */
            function builder_import_ajaxify() {
                check_ajax_referer('tfb_load_nonce', 'nonce');

                $type = $_POST['type'];
                $data = array();

                if ('post' == $type) {
                    $post_types = get_post_types(array('_builtin' => false, 'public' => true));
                    $exclude_post_types = array('shop_order');
                    $data[] = array(
                        'post_type' => 'post',
                        'label' => __('Post', 'themify'),
                        'items' => get_posts(array('posts_per_page' => -1, 'post_type' => 'post'))
                    );
                    foreach ($post_types as $post_type) {
                        $data[] = array(
                            'post_type' => $post_type,
                            'label' => ucfirst($post_type),
                            'items' => get_posts(array('posts_per_page' => -1, 'post_type' => $post_type))
                        );
                    }
                } else if ('page' == $type) {
                    $data[] = array(
                        'post_type' => 'page',
                        'label' => __('Page', 'themify'),
                        'items' => get_pages()
                    );
                } else {
                    die();
                }

                include_once THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-import.php';
                die();
            }

            /**
             * Process import builder
             */
            function builder_import_submit_ajaxify() {
                check_ajax_referer('tfb_load_nonce', 'nonce');
                parse_str($_POST['data'], $imports);
                $import_to = (int) $_POST['importTo'];

                if (count($imports) > 0 && is_array($imports)) {
                    $meta_values = array();

                    // get current page builder data

                    $meta_values[] = $this->get_builder_data($import_to);

                    foreach ($imports as $post_type => $post_id) {
                        if (empty($post_id) || $post_id == 0)
                            continue;

                        $builder_data = $this->get_builder_data($post_id);
                        $meta_values[] = $builder_data;
                    }

                    if (count($meta_values) > 0) {
                        $result = array();
                        foreach ($meta_values as $meta) {
                            $result = array_merge($result, (array) $meta);
                        }
                        $GLOBALS['ThemifyBuilder_Data_Manager']->save_data($result, $import_to);
                    }
                }

                die();
            }

            /**
             * Output row styling style
             * @param int $builder_id
             * @param array $row
             * @return string
             */
            function render_row_styling($builder_id, $row) {
                $row['styling'] = isset($row['styling']) ? $row['styling'] : '';
                $row['row_order'] = isset($row['row_order']) ? $row['row_order'] : '';
                $settings = $row['styling'];
                $style_id = '.themify_builder_' . $builder_id . '_row.module_row_' . $row['row_order'];
                echo $this->get_custom_styling($style_id, 'row', $settings);
            }

            /**
             * Output column styling style
             * @param int $builder_id
             * @param array $row
             * @param array $column
             * @return string
             */
            function render_column_styling($builder_id, $row, $column) {
                $column['styling'] = isset($column['styling']) ? $column['styling'] : '';
                $column['column_order'] = isset($column['column_order']) ? $column['column_order'] : '';
                $settings = $column['styling'];
                $style_id = '.module_row_' . $row['row_order'] .
                        ' .tb_' . $builder_id . '_column.module_column_' . $column['column_order'];
                echo $this->get_custom_styling($style_id, 'column', $settings);
            }

            /**
             * Output sub-column styling style
             * @param int $builder_id
             * @param int $rows
             * @param int $cols
             * @param int $modules
             * @param array $sub_column
             * @return string
             */
            function render_sub_column_styling($builder_id, $rows, $cols, $modules, $sub_column) {
                $sub_column['styling'] = isset($sub_column['styling']) ? $sub_column['styling'] : '';
                $sub_column['column_order'] = isset($sub_column['column_order']) ? $sub_column['column_order'] : '';
                $settings = $sub_column['styling'];
                $style_id = '.sub_column_post_' . $builder_id . '.sub_column_' . $rows . '-' .
                        $cols . '-' . $modules . '-' . $sub_column['column_order'];
                echo $this->get_custom_styling($style_id, 'sub_column', $settings);
            }

            /**
             * Generate CSS styling.
             * 
             * @since 1.0.0
             * @since 2.2.5 Added the ability to return pure CSS without <style> tags for stylesheet generation.
             *
             * @param int $style_id
             * @param string $mod_name Name of the module to build styles for. Example 'row' for row styling.
             * @param array $settings List of settings to generate style.
             * @param bool $array Used for legacy styling generation.
             * @param string $format Use 'tag' to return the CSS enclosed in <style> tags. This mode is used while user is logged in and Builder is active. Use 'css' to return only the CSS. This mode is used on stylesheet generation.
             *
             * @return string
             */
            function get_custom_styling($style_id, $mod_name, $settings, $array = false, $format = 'tag') {
                global $themify;

                /**
                 * Filter style id selector. This can be used to modify the selector on a theme by theme basis.
                 * 
                 * @since 2.3.1
                 *
                 * @param string $style_id Full selector string to be filtered.
                 * @param string $builder_id ID of Builder instance.
                 * @param array $row Current row.
                 */
                $style_id = apply_filters('themify_builder_row_styling_style_id', $style_id);

                if (!isset($themify->builder_google_fonts)) {
                    $themify->builder_google_fonts = '';
                }

                // legacy module def support
                if (
                        'row' == $mod_name ||
                        'column' == $mod_name ||
                        'sub_column' == $mod_name ||
                        ( isset(Themify_Builder_model::$modules[$mod_name]) && is_array(Themify_Builder_model::$modules[$mod_name]->get_css_selectors()) )
                ) {
                    return $this->get_custom_styling_legacy($style_id, $mod_name, $settings, $array, $format);
                }

                $styling = isset(Themify_Builder_model::$modules[$mod_name]) ? Themify_Builder_model::$modules[$mod_name]->get_styling() : array();
                $rules = $this->make_styling_rules($styling, $settings);

                if (!empty($rules)) {
                    $css = array();
                    foreach ($rules as $value) {
                        $css[$value['selector']] = isset($css[$value['selector']]) ? $css[$value['selector']] : '';
                        if (in_array($value['prop'], array('background-color', 'color', 'border-top-color', 'border-bottom-color', 'border-left-color', 'border-right-color'))) {
                            // Split color and opacity
                            $temp_color = explode('_', $value['value']);
                            $temp_opacity = isset($temp_color[1]) ? $temp_color[1] : '1';
                            // Write hexadecimal color.
                            $css[$value['selector']] .= sprintf('%s: #%s; ', $value['prop'], $temp_color[0]);
                            // If there's opacity, that is, if it's not 1 or 1.00 write RGBA color.
                            if ('1' != $temp_opacity && '1.00' != $temp_opacity) {
                                $css[$value['selector']] .= sprintf('%s: %s; ', $value['prop'], $this->get_rgba_color($value['value']));
                            }
                        } elseif ($value['prop'] == 'font-family' && $value['value'] != 'default') {
                            if (!in_array($value['value'], themify_get_web_safe_font_list(true))) {
                                $themify->builder_google_fonts .= str_replace(' ', '+', $value['value'] . '|');
                            }
                            $css[$value['selector']] .= sprintf('font-family: %s; ', $value['value']);
                        } elseif (in_array($value['prop'], array('font-size', 'line-height', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width'))) {
                            $unit = isset($settings[$value['id'] . '_unit']) ? $settings[$value['id'] . '_unit'] : 'px';
                            $css[$value['selector']] .= sprintf('%s: %s%s; ', $value['prop'], $value['value'], $unit);
                        } elseif (in_array($value['prop'], array('text-decoration', 'text-align', 'background-repeat', 'background-position', 'border-top-style', 'border-right-style', 'border-bottom-style', 'border-left-style'))) {
                            $css[$value['selector']] .= sprintf('%s: %s; ', $value['prop'], $value['value']);
                        } elseif ($value['prop'] == 'background-image') {
                            $css[$value['selector']] .= sprintf('%s: url("%s"); ', $value['prop'], themify_https_esc($value['value']));
                        }
                    }
                }

                $output = '';
                if (!empty($css)) {
                    foreach ($css as $selector => $defs) {
                        if (empty($defs)) {
                            continue;
                        }
                        $output .= "{$style_id}{$selector} { {$defs} } \n";
                    }
                    if ('tag' == $format && !empty($output)) {
                        $output = '<style type="text/css">' . $output . '</style>';
                    }
                }

                return $output;
            }

            function make_styling_rules($def, $settings) {
                $result = array();
                if (empty($def)) {
                    return $result;
                }

                foreach ($def as $option) {
                    if ($option['type'] == 'multi') {
                        $result = array_merge($result, $this->make_styling_rules($option['fields'], $settings));
                    } elseif ($option['type'] == 'tabs') {
                        foreach ($option['tabs'] as $tab) {
                            $result = array_merge($result, $this->make_styling_rules($tab['fields'], $settings));
                        }
                    } elseif (isset($option['prop']) && isset($settings[$option['id']])) {
                        foreach ((array) $option['selector'] as $selector) {
                            $result[] = array(
                                'id' => $option['id'],
                                'prop' => $option['prop'],
                                'selector' => $selector,
                                'value' => $settings[$option['id']]
                            );
                        }
                    }
                }

                return $result;
            }

            /**
             * Get custom style
             *
             * @since 2.2.5 New parameter $format to return output enclosed in style tags or not.
             *
             * @param string $style_id 
             * @param string $mod_name 
             * @param array $settings 
             * @param boolean $array 
             * @param string $format Use 'tag' to return the CSS enclosed in <style> tags. This mode is used while user is logged in and Builder is active. Use 'css' to return only the CSS. This mode is used on stylesheet generation.
             *
             * @return string|array
             */
            function get_custom_styling_legacy($style_id, $mod_name, $settings, $array = false, $format = 'tag') {
                global $themify;

                if (!isset($themify->builder_google_fonts)) {
                    $themify->builder_google_fonts = '';
                }

                $rules_arr = array(
                    'font_size' => array(
                        'prop' => 'font-size',
                        'key' => array('font_size', 'font_size_unit')
                    ),
                    'font_family' => array(
                        'prop' => 'font-family',
                        'key' => 'font_family'
                    ),
                    'line_height' => array(
                        'prop' => 'line-height',
                        'key' => array('line_height', 'line_height_unit')
                    ),
                    'text_align' => array(
                        'prop' => 'text-align',
                        'key' => 'text_align'
                    ),
                    'color' => array(
                        'prop' => 'color',
                        'key' => 'font_color'
                    ),
                    'link_color' => array(
                        'prop' => 'color',
                        'key' => 'link_color'
                    ),
                    'text_decoration' => array(
                        'prop' => 'text-decoration',
                        'key' => 'text_decoration'
                    ),
                    'background_color' => array(
                        'prop' => 'background-color',
                        'key' => 'background_color'
                    ),
                    'background_image' => array(
                        'prop' => 'background-image',
                        'key' => 'background_image'
                    ),
                    'background_repeat' => array(
                        'prop' => 'background-repeat',
                        'key' => 'background_repeat'
                    ),
                    'background_position' => array(
                        'prop' => 'background-position',
                        'key' => array('background_position_x', 'background_position_y')
                    ),
                    'padding' => array(
                        'prop' => 'padding',
                        'key' => array('padding_top', 'padding_right', 'padding_bottom', 'padding_left')
                    ),
                    'margin' => array(
                        'prop' => 'margin',
                        'key' => array('margin_top', 'margin_right', 'margin_bottom', 'margin_left')
                    ),
                    'border_top' => array(
                        'prop' => 'border-top',
                        'key' => array('border_top_color', 'border_top_width', 'border_top_style')
                    ),
                    'border_right' => array(
                        'prop' => 'border-right',
                        'key' => array('border_right_color', 'border_right_width', 'border_right_style')
                    ),
                    'border_bottom' => array(
                        'prop' => 'border-bottom',
                        'key' => array('border_bottom_color', 'border_bottom_width', 'border_bottom_style')
                    ),
                    'border_left' => array(
                        'prop' => 'border-left',
                        'key' => array('border_left_color', 'border_left_width', 'border_left_style')
                    )
                );


                if ($mod_name == 'row') {
                    $styles_selector = array(
                        '.module_row' => array(
                            'background_image', 'background_color', 'font_family', 'font_size', 'line_height', 'text_align', 'color', 'padding', 'margin', 'border_top', 'border_right', 'border_bottom', 'border_left'
                        ),
                        '.module_row a' => array(
                            'link_color', 'text_decoration'
                        ),
                        '.module_row h1' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_row h2' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_row h3:not(.module-title)' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_row h4' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_row h5' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_row h6' => array('color', 'font_family', 'font_size', 'line_height'),
                    );
                } else if ($mod_name == 'column') {
                    $styles_selector = array(
                        '.module_column' => array(
                            'background_image', 'background_color', 'font_family', 'font_size', 'line_height', 'text_align', 'color', 'padding', 'margin', 'border_top', 'border_right', 'border_bottom', 'border_left'
                        ),
                        '.module_column a' => array(
                            'link_color', 'text_decoration'
                        ),
                        '.module_column h1' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_column h2' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_column h3:not(.module-title)' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_column h4' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_column h5' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.module_column h6' => array('color', 'font_family', 'font_size', 'line_height'),
                    );
                } else if ($mod_name == 'sub_column') {
                    $styles_selector = array(
                        '.sub_column' => array(
                            'background_image', 'background_color', 'font_family', 'font_size', 'line_height', 'text_align', 'color', 'padding', 'margin', 'border_top', 'border_right', 'border_bottom', 'border_left'
                        ),
                        '.sub_column a' => array(
                            'link_color', 'text_decoration'
                        ),
                        '.sub_column h1' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.sub_column h2' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.sub_column h3:not(.module-title)' => array('color'),
                        '.sub_column h4' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.sub_column h5' => array('color', 'font_family', 'font_size', 'line_height'),
                        '.sub_column h6' => array('color', 'font_family', 'font_size', 'line_height'),
                    );
                } else {
                    $styles_selector = Themify_Builder_Model::$modules[$mod_name]->get_css_selectors();
                }
                $rules = array();
                $css = array();
                $style = '';

                foreach ($styles_selector as $selector => $properties) {
                    $property_arr = array();
                    foreach ($properties as $property) {
                        array_push($property_arr, $rules_arr[$property]);
                    }
                    $rules[$style_id . $selector] = $property_arr;
                }

                foreach ($rules as $selector => $property) {
                    foreach ($property as $val) {
                        $prop = $val['prop'];
                        $key = $val['key'];

                        if (is_array($key)) {
                            if ($prop == 'font-size' && isset($settings[$key[0]]) && '' != $settings[$key[0]]) {
                                $css[$selector][$prop] = $prop . ': ' . $settings[$key[0]] . $settings[$key[1]];
                            } else if ($prop == 'line-height' && isset($settings[$key[0]]) && '' != $settings[$key[0]]) {
                                $css[$selector][$prop] = $prop . ': ' . $settings[$key[0]] . $settings[$key[1]];
                            } else if ($prop == 'background-position' && isset($settings[$key[0]]) && '' != $settings[$key[0]]) {
                                $css[$selector][$prop] = $prop . ': ' . $settings[$key[0]] . ' ' . $settings[$key[1]];
                            } else if ($prop == 'padding') {
                                $padding['top'] = isset($settings[$key[0]]) && '' != $settings[$key[0]] ? $settings[$key[0]] : '';
                                $padding['right'] = isset($settings[$key[1]]) && '' != $settings[$key[1]] ? $settings[$key[1]] : '';
                                $padding['bottom'] = isset($settings[$key[2]]) && '' != $settings[$key[2]] ? $settings[$key[2]] : '';
                                $padding['left'] = isset($settings[$key[3]]) && '' != $settings[$key[3]] ? $settings[$key[3]] : '';

                                foreach ($padding as $k => $v) {
                                    if ('' == $v)
                                        continue;
                                    $unit = isset($settings["padding_{$k}_unit"]) ? $settings["padding_{$k}_unit"] : 'px';
                                    $css[$selector]['padding-' . $k] = 'padding-' . $k . ' : ' . $v . $unit;
                                }
                            } else if ($prop == 'margin') {
                                $margin['top'] = isset($settings[$key[0]]) && '' != $settings[$key[0]] ? $settings[$key[0]] : '';
                                $margin['right'] = isset($settings[$key[1]]) && '' != $settings[$key[1]] ? $settings[$key[1]] : '';
                                $margin['bottom'] = isset($settings[$key[2]]) && '' != $settings[$key[2]] ? $settings[$key[2]] : '';
                                $margin['left'] = isset($settings[$key[3]]) && '' != $settings[$key[3]] ? $settings[$key[3]] : '';

                                foreach ($margin as $k => $v) {
                                    if ('' == $v)
                                        continue;
                                    $unit = isset($settings["margin_{$k}_unit"]) ? $settings["margin_{$k}_unit"] : 'px';
                                    $css[$selector]['margin-' . $k] = 'margin-' . $k . ' : ' . $v . $unit;
                                }
                            } else if (in_array($prop, array('border-top', 'border-right', 'border-bottom', 'border-left'))) {
                                $border['color'] = isset($settings[$key[0]]) && '' != $settings[$key[0]] ? '#' . $settings[$key[0]] : '';
                                $border['width'] = isset($settings[$key[1]]) && '' != $settings[$key[1]] ? $settings[$key[1]] . 'px' : '';
                                $border['style'] = isset($settings[$key[2]]) && '' != $settings[$key[2]] ? $settings[$key[2]] : '';
                                $css[$selector][$prop] = $this->build_color_props(array(
                                    'color_opacity' => $border['color'],
                                    'property' => $prop,
                                    'border_width' => $border['width'],
                                    'border_style' => $border['style'],
                                        )
                                );

                                if (empty($border['color']) && empty($border['width']) && empty($border['style']))
                                    unset($css[$selector][$prop]);
                            }
                        } elseif (isset($settings[$key]) && 'default' != $settings[$key] && '' != $settings[$key]) {
                            if ($prop == 'color' || stripos($prop, 'color')) {
                                $css[$selector][$prop] = $this->build_color_props(array(
                                    'color_opacity' => $settings[$key],
                                    'property' => $prop,
                                        )
                                );
                            } elseif ($prop == 'background-image' && 'default' != $settings[$key]) {
                                $css[$selector][$prop] = $prop . ': url(' . themify_https_esc($settings[$key]) . ')';
                                if (isset($settings['background_type']) && 'video' == $settings['background_type']) {
                                    $css[$selector][$prop] .= ";\n\tbackground-size: cover";
                                }
                            } elseif ($prop == 'font-family') {
                                $font = $settings[$key];
                                $css[$selector][$prop] = $prop . ': ' . $font;
                                if (!in_array($font, themify_get_web_safe_font_list(true))) {
                                    $themify->builder_google_fonts .= str_replace(' ', '+', $font . '|');
                                }
                            } else {
                                $css[$selector][$prop] = $prop . ': ' . $settings[$key];
                            }
                        }
                    }

                    if (!empty($css[$selector])) {
                        $style .= "$selector {\n\t" . implode(";\n\t", array_map(array($this, 'trim_last_semicolon'), $css[$selector])) . "\n}\n";
                    }
                }

                if (!$array) {
                    if ('' != $style) {
                        if ('tag' == $format) {
                            return "\n<!-- $style_id Style -->\n<style type=\"text/css\" >\n$style</style>\n<!-- End $style_id Style -->\n";
                        } else {
                            return "/* $style_id Style */\n$style\n";
                        }
                    }
                } else if ($array) {
                    return $css;
                }
            }

            /**
             * If string has an semicolon at the end, it will be stripped.
             *
             * @since 2.3.3
             *
             * @param string $string
             * @return string
             */
            function trim_last_semicolon($string) {
                return rtrim($string, ';');
            }

            /**
             * Outputs color for the logo in text mode since it's needed for the <a>.
             *
             * @since 1.9.6
             *
             * @param array $args
             * @return string
             */
            function build_color_props($args = array()) {
                $args = wp_parse_args($args, array(
                    'color_opacity' => '',
                    'property' => 'color',
                    'border_width' => '1px',
                    'border_style' => 'solid',
                ));
                // Strip any lingering hashes just in case
                $args['color_opacity'] = str_replace('#', '', $args['color_opacity']);
                // Separator between color and opacity
                $sep = '_';

                if (false !== stripos($args['color_opacity'], $sep)) {
                    // If it's the new color+opacity, an underscore separates color from opacity
                    $all = explode($sep, $args['color_opacity']);
                    $color = isset($all[0]) ? $all[0] : '';
                    $opacity = isset($all[1]) ? $all[1] : '';
                } else {
                    // If it's the traditional, it's a simple color
                    $color = $args['color_opacity'];
                    $opacity = '';
                }
                $element_props = '';
                if ('' != $color) {
                    // Setup opacity value or solid
                    $opacity = ( '' != $opacity ) ? $opacity : '1';
                    if (false !== stripos($args['property'], 'border')) {
                        // It's a border property, a composite of border size style
                        $element_props .= "{$args['property']}: #$color {$args['border_width']} {$args['border_style']};";
                        if ('1' != $opacity && '1.00' != $opacity) {
                            $element_props .= "\n\t{$args['property']}: rgba(" . $this->hex2rgb($color) . ",  $opacity) {$args['border_width']} {$args['border_style']}";
                        }
                    } else {
                        // It's either background-color or color, a simple color
                        $element_props .= "{$args['property']}: #$color;";
                        if ('1' != $opacity && '1.00' != $opacity) {
                            $element_props .= "\n\t{$args['property']}: rgba(" . $this->hex2rgb($color) . ", $opacity)";
                        }
                    }
                }
                return $element_props;
            }

            /**
             * Converts color in hexadecimal format to RGB format.
             *
             * @since 1.9.6
             *
             * @param string $hex Color in hexadecimal format.
             * @return string Color in RGB components separated by comma.
             */
            function hex2rgb($hex) {
                $hex = str_replace("#", "", $hex);

                if (strlen($hex) == 3) {
                    $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                    $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                    $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
                } else {
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                }
                return implode(',', array($r, $g, $b));
            }

            /**
             * Get RGBA color format from hex color
             *
             * @return string
             */
            function get_rgba_color($color) {
                $color = explode('_', $color);
                $opacity = isset($color[1]) ? $color[1] : '1';
                return 'rgba(' . $this->hex2rgb($color[0]) . ', ' . $opacity . ')';
            }

            /**
             * Get google fonts
             */
            function get_custom_google_fonts() {
                global $themify;
                $fonts = array();

                if (!isset($themify->builder_google_fonts) || '' == $themify->builder_google_fonts)
                    return $fonts;
                $themify->builder_google_fonts = substr($themify->builder_google_fonts, 0, -1);
                $fonts = explode('|', $themify->builder_google_fonts);
                return $fonts;
            }

            /**
             * Add filter to module content
             * @param string $content 
             * @return string
             */
            function the_module_content($content) {
                global $wp_embed;
                $content = $wp_embed->run_shortcode($content);
                $content = do_shortcode(shortcode_unautop($content));
                $content = $this->autoembed_adjustments($content);
                $content = $wp_embed->autoembed($content);
                $content = htmlspecialchars_decode($content);
                return $content;
            }

            /**
             * Adjust autoembed filter
             * @param string $content 
             * @return string
             */
            function autoembed_adjustments($content) {
                $pattern = '|<p>\s*(https?://[^\s"]+)\s*</p>|im'; // pattern to check embed url
                $to = '<p>' . PHP_EOL . '$1' . PHP_EOL . '</p>'; // add line break 
                $content = preg_replace($pattern, $to, $content);
                return $content;
            }

            /**
             * Add custom Themify Builder button after Add Media btn
             * @param string $context 
             * @return string
             */
            function add_custom_switch_btn($context) {
                global $pagenow;
                $post_types = themify_post_types();
                if ('post.php' == $pagenow && in_array(get_post_type(), $post_types)) {
                    $context .= sprintf('<a href="#" class="button themify_builder_switch_btn">%s</a>', __('Themify Builder', 'themify'));
                }
                return $context;
            }

            /**
             * Computes and returns data for Builder row or column video background.
             *
             * @since 2.3.3
             *
             * @param array $styling The row's or column's styling array.
             *
             * @return bool|string Return video data if row/col has a background video, else return false.
             */
            private function get_video_background($styling) {
                $is_type_video = isset($styling['background_type']) && 'video' == $styling['background_type'];
                $has_video = isset($styling['background_video']) && !empty($styling['background_video']);

                if (!$is_type_video || !$has_video) {
                    return false;
                }


                $video_data = 'data-fullwidthvideo="' . esc_url(themify_https_esc($styling['background_video'])) . '"';

                // Will only be written if they exist, for backwards compatibility with global JS variable tbLocalScript.backgroundVideoLoop
                if (isset($styling['background_video_options'])) {
                    if (is_array($styling['background_video_options'])) {
                        $video_data .= in_array('mute', $styling['background_video_options']) ? ' data-mutevideo="mute"' : ' data-mutevideo="unmute"';
                        $video_data .= in_array('unloop', $styling['background_video_options']) ? ' data-unloopvideo="unloop"' : ' data-unloopvideo="loop"';
                    } else {
                        $video_data .= ( false !== stripos('mute', $styling['background_video_options']) ) ? ' data-mutevideo="mute"' : ' data-mutevideo="unmute"';
                        $video_data .= ( false !== stripos('unloop', $styling['background_video_options']) ) ? ' data-unloopvideo="unloop"' : ' data-unloopvideo="loop"';
                    }
                }

                return apply_filters('themify_builder_row_video_background', $video_data, $styling);
            }

            /**
             * Computes and returns the HTML for a background slider.
             *
             * @since 2.3.3
             *
             * @param array  $row_or_col   Row or column definition.
             * @param string $order        Order of row/column (e.g. 0 or 0-1-0-1 for sub columns)
             * @param string $size         The size of images(thumbails,medium,large and etc.)
             * @param string $builder_type Accepts 'row', 'col', 'sub-col'
             *
             * @return bool Returns false if $row_or_col doesn't have a bg slider. Otherwise outputs the HTML for the slider.
             */
            private function do_slider_background($row_or_col, $order, $size = false, $builder_type = 'row') {
                if (!isset($row_or_col['styling']['background_slider']) ||
                        empty($row_or_col['styling']['background_slider']) ||
                        'slider' != $row_or_col['styling']['background_type']) {

                    return false;
                }

                if ($images = $this->get_images_from_gallery_shortcode($row_or_col['styling']['background_slider'])) :
                    $bgmode = isset($row_or_col['styling']['background_slider_mode']) &&
                            !empty($row_or_col['styling']['background_slider_mode']) ?
                            $row_or_col['styling']['background_slider_mode'] : 'fullcover';
                    if (!$size) {
                        $size = $this->get_gallery_param_option($row_or_col['styling']['background_slider'], 'size');
                    }
                    if (!$size) {
                        $size = 'large';
                    }
                    ?>

                    <div id="<?php echo $builder_type; ?>-slider-<?php echo esc_attr($order); ?>" class="<?php echo $builder_type; ?>-slider"
                         data-bgmode="<?php echo esc_attr($bgmode); ?>">
                        <ul class="row-slider-slides clearfix">
                            <?php
                            $dot_i = 0;
                            foreach ($images as $image) :
                                $img_data = wp_get_attachment_image_src($image->ID, $size);
                                ?>
                                <li data-bg="<?php echo esc_url(themify_https_esc($img_data[0])); ?>">
                                    <a class="row-slider-dot" data-index="<?php echo esc_attr($dot_i); ?>"></a>
                                </li>
                                <?php
                                $dot_i++;
                            endforeach;
                            ?>
                        </ul>
                        <div class="row-slider-nav">
                            <a class="row-slider-arrow row-slider-prev">&lsaquo;</a>
                            <a class="row-slider-arrow row-slider-next">&rsaquo;</a>
                        </div>
                    </div>
                    <!-- /.row-bgs -->
                    <?php
                endif; // images
            }

            /**
             * Computes and returns the HTML a color overlay.
             *
             * @since 2.3.3
             *
             * @param array $styling The row's or column's styling array.
             *
             * @return bool Returns false if $styling doesn't have a color overlay. Otherwise outputs the HTML;
             */
            private function do_color_overlay($styling) {
                if (!isset($styling['cover_color']) && !isset($styling['cover_color_hover']) ||
                        empty($styling['cover_color']) && empty($styling['cover_color_hover'])) {
                    return false;
                }

                $cover_color = ( isset( $styling['cover_color'] ) && $styling['cover_color'] != '' ) ? 'style="background-color: ' . esc_attr($this->get_rgba_color($styling['cover_color'])) . '" data-color="' . esc_attr($this->get_rgba_color($styling['cover_color'])) . '"' : '';
                $cover_hover_color = ( isset( $styling['cover_color_hover'] ) && $styling['cover_color_hover'] != '' ) ? 'data-hover-color="' . esc_attr($this->get_rgba_color($styling['cover_color_hover'])) . '"' : '';
                ?>

                <div class="builder_row_cover" <?php echo "$cover_color $cover_hover_color"; ?>></div>

                <?php
            }

            /**
             * Get template row
             *
             * @param array  $rows
             * @param array  $row
             * @param string $builder_id
             * @param bool   $echo
             *
             * @return string
             */
            public function get_template_row($rows, $row, $builder_id, $echo = false, $frontedit_active = null) {
                /* allow addons to control the display of the rows */
                $display = apply_filters('themify_builder_row_display', true, $row, $builder_id);
                if (false === $display) {
                    return false;
                }

                if (null === $frontedit_active) {
                    $frontedit_active = self::$frontedit_active;
                }

                // prevent empty rows from being rendered
                if (!$frontedit_active) {
                    if (
                            (!isset($row['cols']) && !isset($row['styling']) ) || ( isset($row['cols']) && empty($row['cols']) && !isset($row['styling']) ) || ( isset($row['cols']) && count($row['cols']) == 1 && empty($row['cols'][0]['modules']) && !isset($row['styling']) ) // there's only one column and it's empty
                    ) {
                        return '';
                    }
                }

                $row['row_order'] = isset($row['row_order']) ? $row['row_order'] : uniqid();
                $row_classes = array('themify_builder_row', 'themify_builder_' . $builder_id . '_row', 'module_row', 'module_row_' . $row['row_order'], 'clearfix');
                $class_fields = array('custom_css_row', 'background_repeat', 'animation_effect', 'row_width', 'row_height');
                $row_gutter_class = isset($row['gutter']) && !empty($row['gutter']) ? $row['gutter'] : 'gutter-default';
                $row_column_equal_height = isset($row['equal_column_height']) && !empty($row['equal_column_height']) ? $row['equal_column_height'] : '';

                // Set Gutter Class
                if ('' != $row_gutter_class)
                    $row_classes[] = $row_gutter_class;

                // Set column equal height
                if ('' != $row_column_equal_height) {
                    $row_classes[] = $row_column_equal_height;
                }

                // Class for Scroll Highlight
                if (isset($row['styling']) && isset($row['styling']['row_anchor']) && '' != $row['styling']['row_anchor']) {
                    $row_classes[] = 'tb_section-' . $row['styling']['row_anchor'];
                }

                // @backward-compatibility
                if (!isset($row['styling']['background_type']) && isset($row['styling']['background_video']) && '' != $row['styling']['background_video']) {
                    $row['styling']['background_type'] = 'video';
                }

                foreach ($class_fields as $field) {
                    if (isset($row['styling'][$field]) && !empty($row['styling'][$field])) {
                        if ('animation_effect' == $field) {
                            $row_classes[] = 'wow';
                        }
                        $row_classes[] = $row['styling'][$field];
                    }
                }
                if (isset($row['styling']['animation_effect_delay']) && !empty($row['styling']['animation_effect_delay'])) {
                    $row_classes[] = 'animation_effect_delay_' . $row['styling']['animation_effect_delay'];
                }
                if (isset($row['styling']['animation_effect_repeat']) && !empty($row['styling']['animation_effect_repeat'])) {
                    $row_classes[] = 'animation_effect_repeat_' . $row['styling']['animation_effect_repeat'];
                }
                if (isset($row['styling']['background_image']) && $row['styling']['background_image'] && isset($row['styling']['background_position']) && !empty($row['styling']['background_position'])) {
                    $row_classes[] = 'bg-position-' . $row['styling']['background_position'];
                }
                $row_classes = apply_filters('themify_builder_row_classes', $row_classes, $row, $builder_id);

                // background video
                $video_data = '';
                if (isset($row['styling'])) {
                    $video_data = $this->get_video_background($row['styling']);
                }

                $output = PHP_EOL; // add line break
                ob_start();
                ?>
                <!-- module_row -->
                <div data-gutter="<?php echo esc_attr($row_gutter_class); ?>" class="<?php echo esc_attr(implode(' ', $row_classes)); ?>" <?php echo $video_data; ?>
                     data-equal-column-height="<?php echo esc_attr($row_column_equal_height); ?>">

                    <?php if ($frontedit_active): ?>
                        <div class="themify_builder_row_top">

                            <?php themify_builder_grid_lists('row', $row_gutter_class, $row_column_equal_height); ?>

                            <ul class="row_action">
                                <li><a href="#" data-title="<?php _e('Export', 'themify') ?>" class="themify_builder_export_component"
                                       data-component="row" rel="themify-tooltip-bottom">
                                        <span class="ti-export"></span>
                                    </a></li>
                                <li><a href="#" data-title="<?php _e('Import', 'themify') ?>" class="themify_builder_import_component"
                                       data-component="row" rel="themify-tooltip-bottom">
                                        <span class="ti-import"></span>
                                    </a></li>
                                <li class="separator"></li>
                                <li><a href="#" data-title="<?php _e('Copy', 'themify') ?>" class="themify_builder_copy_component"
                                       data-component="row" rel="themify-tooltip-bottom">
                                        <span class="ti-files"></span>
                                    </a></li>
                                <li><a href="#" data-title="<?php _e('Paste', 'themify') ?>" class="themify_builder_paste_component"
                                       data-component="row" rel="themify-tooltip-bottom">
                                        <span class="ti-clipboard"></span>
                                    </a></li>
                                <li class="separator"></li>
                                <li><a href="#" data-title="<?php _e('Options', 'themify') ?>" class="themify_builder_option_row"
                                       rel="themify-tooltip-bottom">
                                        <span class="ti-pencil"></span>
                                    </a></li>
                                <li><a href="#" data-title="<?php _e('Styling', 'themify') ?>" class="themify_builder_style_row"
                                       rel="themify-tooltip-bottom">
                                        <span class="ti-brush"></span>
                                    </a></li>
                                <li><a href="#" data-title="<?php _e('Duplicate', 'themify') ?>" class="themify_builder_duplicate_row"
                                       rel="themify-tooltip-bottom">
                                        <span class="ti-layers"></span>
                                    </a></li>
                                <li><a href="#" data-title="<?php _e('Delete', 'themify') ?>" class="themify_builder_delete_row"
                                       rel="themify-tooltip-bottom">
                                        <span class="ti-close"></span>
                                    </a></li>
                                <li class="separator"></li>
                                <li><a href="#" data-title="<?php _e('Toggle Row', 'themify') ?>" class="themify_builder_toggle_row">
                                        <span class="ti-angle-up"></span>
                                    </a></li>
                            </ul>
                        </div>
                        <!-- /row_top -->
                    <?php endif; // builder edit active    ?>

                    <?php
                    if (isset($row['styling'])) {

                        // Background cover color
                        $this->do_color_overlay($row['styling']);
                        $size = isset($row['styling']['background_slider_size']) ? $row['styling']['background_slider_size'] : false;
                        // Background Slider
                        $this->do_slider_background($row, $row['row_order'], $size, 'row');
                    }
                    ?>

                    <div class="row_inner_wrapper">
                        <div class="row_inner">

                            <?php do_action('themify_builder_row_start', $builder_id, $row); ?>

                            <?php if ($frontedit_active): ?>
                                <div class="themify_builder_row_content">
                                <?php endif; // builder edit active    ?>

                                <?php
                                if (isset($row['cols']) && count($row['cols']) > 0):

                                    $count = count($row['cols']);

                                    switch ($count) {

                                        case 6:
                                            $order_classes = array('first', 'second', 'third', 'fourth', 'fifth', 'last');
                                            break;

                                        case 5:
                                            $order_classes = array('first', 'second', 'third', 'fourth', 'last');
                                            break;

                                        case 4:
                                            $order_classes = array('first', 'second', 'third', 'last');
                                            break;

                                        case 3:
                                            $order_classes = array('first', 'middle', 'last');
                                            break;

                                        case 2:
                                            $order_classes = array('first', 'last');
                                            break;

                                        default:
                                            $order_classes = array('first');
                                            break;
                                    }

                                    foreach ($row['cols'] as $cols => $col):
                                        $columns_class = array();
                                        $grid_class = explode(' ', $col['grid_class']);
                                        $dynamic_class = array('', '');
                                        $dynamic_class[0] = $frontedit_active ? 'themify_builder_col' : $order_classes[$cols];
                                        $dynamic_class[1] = $frontedit_active ? '' : 'tb-column';
                                        $dynamic_class[2] = ( isset($col['modules']) && count($col['modules']) > 0 ) ? '' : 'empty-column';
                                        $dynamic_class[3] = 'tb_' . $builder_id . '_column'; // who's your daddy?

                                        if (isset($col['column_order'])) {
                                            array_push($dynamic_class, 'module_column_' . $col['column_order']);
                                        }

                                        array_push($dynamic_class, 'module_column');

                                        if (isset($col['styling']['background_repeat']) && !empty($col['styling']['background_repeat'])) {
                                            $dynamic_class[] = $col['styling']['background_repeat'];
                                        }

                                        $columns_class = array_merge($columns_class, $grid_class);
                                        foreach ($dynamic_class as $class) {
                                            array_push($columns_class, $class);
                                        }
                                        $columns_class = array_unique($columns_class);
                                        // remove class "last" if the column is fullwidth
                                        if (1 == $count) {
                                            if (( $key = array_search('last', $columns_class) ) !== false) {
                                                unset($columns_class[$key]);
                                            }
                                        }
                                        if (isset($col['styling']['background_image']) && $col['styling']['background_image'] && isset($col['styling']['background_position']) && !empty($col['styling']['background_position'])) {
                                            $columns_class[] = 'bg-position-' . $col['styling']['background_position'];
                                        }
                                        if (isset($col['styling']['custom_css_column']) && $col['styling']['custom_css_column']) {
                                            $columns_class[] = $col['styling']['custom_css_column'];
                                        }
                                        $print_column_classes = implode(' ', $columns_class);

                                        // background video
                                        $video_data = '';
                                        if (isset($col['styling'])) {
                                            $video_data = $this->get_video_background($col['styling']);
                                        }
                                        ?>

                                        <div class="<?php echo esc_attr($print_column_classes); ?>" <?php echo $video_data; ?>>

                                            <?php
                                            if (isset($col['styling'])) {
                                                // Background cover color
                                                $this->do_color_overlay($col['styling']);

                                                // Background Slider
                                                $column_order = $row['row_order'] . '-' . $col['column_order'];
                                                $size = isset($col['styling']['background_slider_size']) ? $col['styling']['background_slider_size'] : false;
                                                $this->do_slider_background($col, $column_order, $size, 'col');
                                            }
                                            ?>

                                            <?php if ($frontedit_active) : ?>
                                                <div class="themify_builder_column_styling_icon ti-brush themify_builder_option_column" title="<?php esc_attr_e('Column Styling', 'themify'); ?>"></div>
                                            <?php endif; ?>

                                            <div class="tb-column-inner">

                                                <?php do_action('themify_builder_column_start', $builder_id, $row, $col); ?>

                                                <?php if ($frontedit_active): ?>
                                                    <div class="themify_module_holder">
                                                        <div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
                                                    <?php endif; ?>

                                                    <?php
                                                    if (isset($col['modules']) && count($col['modules']) > 0) {
                                                        foreach ($col['modules'] as $modules => $mod) {

                                                            if (isset($mod['mod_name'])) {
                                                                $w_wrap = ( $frontedit_active ) ? true : false;
                                                                $w_class = ( $frontedit_active ) ? 'r' . $rows . 'c' . $cols . 'm' . $modules : '';
                                                                $identifier = array($rows, $cols, $modules); // define module id
                                                                $this->get_template_module($mod, $builder_id, true, $w_wrap, $w_class, $identifier);
                                                            }

                                                            // Check for Sub-rows
                                                            if (isset($mod['cols']) && count($mod['cols']) > 0) {
                                                                $dynamic_class = array();

                                                                $dynamic_class[] = $sub_row_gutter = isset($mod['gutter']) && !empty($mod['gutter']) ? $mod['gutter'] : 'gutter-default';
                                                                $dynamic_class[] = $sub_row_column_equal_height = isset($mod['equal_column_height']) &&
                                                                        !empty($mod['equal_column_height']) ? $mod['equal_column_height'] : '';
                                                                $dynamic_class[] = $sub_row_class = 'sub_row_' . $rows . '-' . $cols . '-' . $modules;

                                                                $sub_row_attr = $frontedit_active ? '
															data-gutter="' . esc_attr($sub_row_gutter) . '"' : '';
                                                                $sub_row_column_equal_height_data = $frontedit_active ? 'data-equal-column-height="' .
                                                                        esc_attr($sub_row_column_equal_height) . '"' : '';

                                                                $print_sub_row_classes = implode(' ', $dynamic_class);

                                                                echo sprintf('<div class="themify_builder_sub_row
															clearfix %s"%s %s>', esc_attr($print_sub_row_classes), $sub_row_attr, $sub_row_column_equal_height_data);
                                                                ?>

                                                                <?php if ($frontedit_active): ?>
                                                                    <div class="themify_builder_sub_row_top">
                                                                        <?php themify_builder_grid_lists('sub_row', $sub_row_gutter, $sub_row_column_equal_height); ?>
                                                                        <ul class="sub_row_action">
                                                                            <li><a href="#" data-title="<?php _e('Export', 'themify') ?>" rel="themify-tooltip-bottom"
                                                                                   class="themify_builder_export_component" data-component="sub-row">
                                                                                    <span class="ti-export"></span>
                                                                                </a></li>
                                                                            <li><a href="#" data-title="<?php _e('Import', 'themify') ?>" rel="themify-tooltip-bottom"
                                                                                   class="themify_builder_import_component" data-component="sub-row">
                                                                                    <span class="ti-import"></span>
                                                                                </a></li>
                                                                            <li class="separator"></li>
                                                                            <li><a href="#" data-title="<?php _e('Copy', 'themify') ?>" rel="themify-tooltip-bottom"
                                                                                   class="themify_builder_copy_component" data-component="sub-row">
                                                                                    <span class="ti-files"></span>
                                                                                </a></li>
                                                                            <li><a href="#" data-title="<?php _e('Paste', 'themify') ?>" rel="themify-tooltip-bottom"
                                                                                   class="themify_builder_paste_component" data-component="sub-row">
                                                                                    <span class="ti-clipboard"></span>
                                                                                </a></li>
                                                                            <li class="separator"></li>
                                                                            <li><a href="#" data-title="<?php _e('Duplicate', 'themify') ?>" rel="themify-tooltip-bottom"
                                                                                   class="sub_row_duplicate">
                                                                                    <span class="ti-layers"></span>
                                                                                </a></li>
                                                                            <li><a href="#" data-title="<?php _e('Delete', 'themify') ?>" rel="themify-tooltip-bottom"
                                                                                   class="sub_row_delete">
                                                                                    <span class="ti-close"></span>
                                                                                </a></li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="themify_builder_sub_row_content">
                                                                    <?php endif; ?>

                                                                    <?php
                                                                    foreach ($mod['cols'] as $col_key => $sub_col) {
                                                                        $dynamic_class = array();

                                                                        $dynamic_class[] = $frontedit_active ? 'themify_builder_col ' . $sub_col['grid_class'] : $sub_col['grid_class'];
                                                                        $dynamic_class[] = 'sub_column sub_column_' . $rows . '-' . $cols . '-' . $modules . '-' . $col_key;
                                                                        $dynamic_class[] = "sub_column_post_{$builder_id}";

                                                                        if (isset($sub_col['styling']['background_repeat']) && !empty($sub_col['styling']['background_repeat'])) {
                                                                            $dynamic_class[] = $sub_col['styling']['background_repeat'];
                                                                        }
                                                                        if (isset($sub_col['styling']['background_image']) && $sub_col['styling']['background_image'] && isset($sub_col['styling']['background_position']) && !empty($sub_col['styling']['background_position'])) {
                                                                            $dynamic_class[] = 'bg-position-' . $sub_col['styling']['background_position'];
                                                                        }
                                                                        if (isset($sub_col['styling']['custom_css_column']) && $sub_col['styling']['custom_css_column']) {
                                                                            $dynamic_class[] = $sub_col['styling']['custom_css_column'];
                                                                        }
                                                                        $print_sub_col_classes = implode(' ', $dynamic_class);

                                                                        // background video
                                                                        $video_data = '';
                                                                        if (isset($sub_col['styling'])) {
                                                                            $video_data = $this->get_video_background($sub_col['styling']);
                                                                        }

                                                                        echo sprintf('<div class="%s" %s>', esc_attr($print_sub_col_classes), $video_data);
                                                                        ?>

                                                                        <?php
                                                                        if (isset($sub_col['styling'])) {
                                                                            // Background cover color
                                                                            $this->do_color_overlay($sub_col['styling']);


                                                                            // Background Slider
                                                                            $sub_column_order = $rows . '-' . $cols . '-' . $modules . '-' . $col_key;
                                                                            $size = isset($sub_col['styling']['background_slider_size']) ? $sub_col['styling']['background_slider_size'] : false;
                                                                            $this->do_slider_background($sub_col, $sub_column_order, $size, 'sub-col');
                                                                        }
                                                                        ?>

                                                                        <?php do_action('themify_builder_sub_column_start', $builder_id, $rows, $cols, $modules, $sub_col); ?>

                                                                        <?php if ($frontedit_active): ?>
                                                                            <div class="themify_builder_column_styling_icon ti-brush themify_builder_option_column" title="<?php esc_attr_e('Column Styling', 'themify'); ?>"></div>
                                                                            <div class="themify_module_holder">
                                                                                <div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
                                                                            <?php endif; ?>
                                                                            <?php
                                                                            if (isset($sub_col['modules']) && count($sub_col['modules']) > 0) {
                                                                                foreach ($sub_col['modules'] as $sub_module_k => $sub_module) {
                                                                                    $sw_wrap = ( $frontedit_active ) ? true : false;
                                                                                    $sw_class = ( $frontedit_active ) ? 'r' . $sub_row_class . 'c' . $col_key . 'm' . $sub_module_k : '';
                                                                                    $sub_identifier = array($sub_row_class, $col_key, $sub_module_k); // define module id
                                                                                    $this->get_template_module($sub_module, $builder_id, true, $sw_wrap, $sw_class, $sub_identifier);
                                                                                }
                                                                            }
                                                                            ?>

                                                                            <?php if ($frontedit_active): ?>
                                                                            </div>
                                                                            <?php $sub_column_data_styling = isset($sub_col['styling']) ? json_encode($sub_col['styling']) : json_encode(array()); ?>
                                                                            <div class="column-data-styling" data-styling="<?php echo esc_attr($sub_column_data_styling); ?>"></div>
                                                                            <!-- /module_holder -->
                                                                        <?php endif; ?>
                                                                        <?php
                                                                        echo '</div>';
                                                                    }

                                                                    if ($frontedit_active) {
                                                                        echo '</div>';
                                                                    }

                                                                    echo '</div>';
                                                                }
                                                            }
                                                        } elseif (!$frontedit_active) {
                                                            echo '&nbsp;'; // output empty space
                                                        }
                                                        ?>

                                                    </div>
                                                    <!-- /.tb-column-inner -->
                                                    <?php if ($frontedit_active): ?>
                                                    </div>
                                                    <?php $column_data_styling = isset($col['styling']) ? json_encode($col['styling']) : json_encode(array()); ?>
                                                    <div class="column-data-styling" data-styling="<?php echo esc_attr($column_data_styling); ?>"></div>
                                                    <!-- /module_holder -->
                                                <?php endif; ?>
                                            </div>
                                            <!-- /.tb-column -->
                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <div class="themify_builder_col col-full first last">
                                            <?php if ($frontedit_active): ?>
                                                <div class="themify_module_holder">
                                                    <div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
                                                <?php endif; ?>

                                                <?php
                                                if (!$frontedit_active) {
                                                    echo '&nbsp;'; // output empty space
                                                }
                                                ?>

                                                <?php if ($frontedit_active): ?>
                                                </div>
                                                <!-- /module_holder -->
                                            <?php endif; ?>
                                        </div>
                                        <!-- /col -->

                                    <?php endif; // end col loop    ?>

                                    <?php if ($frontedit_active): ?>
                                    </div> <!-- /themify_builder_row_content -->

                                    <?php $row_data_styling = isset($row['styling']) ? json_encode($row['styling']) : json_encode(array()); ?>
                                    <div class="row-data-styling" data-styling="<?php echo esc_attr($row_data_styling); ?>"></div>
                                <?php endif; ?>

                                <?php do_action('themify_builder_row_end', $builder_id, $row); ?>

                            </div>
                            <!-- /row_inner -->
                        </div>
                        <!-- /row_inner_wrapper -->
                    </div>
                    <!-- /module_row -->
                    <?php
                    $output .= ob_get_clean();
                    // add line break
                    $output .= PHP_EOL;

                    if ($echo) {
                        echo $output;
                    } else {
                        return $output;
                    }
                }

                /**
                 * Return the correct animation css class name
                 * @param string $effect 
                 * @return string
                 */
                function parse_animation_effect($effect, $mod_settings = null) {
                    if (!Themify_Builder_Model::is_animation_active())
                        return '';

                    $class = ( '' != $effect && !in_array($effect, array('fade-in', 'fly-in', 'slide-up')) ) ? 'wow ' . $effect : $effect;
                    if (isset($mod_settings['animation_effect_delay']) && !empty($mod_settings['animation_effect_delay'])) {
                        $class .= ' animation_effect_delay_' . $mod_settings['animation_effect_delay'];
                    }
                    if (isset($mod_settings['animation_effect_repeat']) && !empty($mod_settings['animation_effect_repeat'])) {
                        $class .= ' animation_effect_repeat_' . $mod_settings['animation_effect_repeat'];
                    }

                    return $class;
                }

                /**
                 * Add classes to post_class
                 * @param string|array $classes 
                 */
                function add_post_class($classes) {
                    foreach ((array) $classes as $class) {
                        $this->_post_classes[$class] = $class;
                    }
                }

                /**
                 * Remove sepecified classnames from post_class
                 * @param string|array $classes 
                 */
                function remove_post_class($classes) {
                    foreach ((array) $classes as $class) {
                        unset($this->_post_classes[$class]);
                    }
                }

                /**
                 * Filter post_class to add the classnames to posts
                 *
                 * @return array
                 */
                function filter_post_class($classes) {
                    $classes = array_merge($classes, $this->_post_classes);
                    return $classes;
                }

                /**
                 * Return whether this is a Themify theme or not.
                 *
                 * @return bool
                 */
                function is_themify_theme() {
                    // Check if THEMIFY_BUILDER_VERSION constant is defined.
                    if (defined('THEMIFY_BUILDER_VERSION')) {
                        // Check if it's defined with an expected value and not something odd.
                        if (preg_match('/[1-9].[0-9].[0-9]/', THEMIFY_BUILDER_VERSION)) {
                            return false;
                        }
                    }
                    // It's a Themify theme.
                    return true;
                }

                /**
                 * Add any js classname to html element when JavaScript is enabled
                 */
                function render_javascript_classes() {
                    echo '<script type="text/javascript">';
                    ?>
                    function isSupportTransition() {
                    var b = document.body || document.documentElement,
                    s = b.style,
                    p = 'transition';

                    if (typeof s[p] == 'string') { return true; }

                    // Tests for vendor specific prop
                    var v = ['Moz', 'webkit', 'Webkit', 'Khtml', 'O', 'ms'];
                    p = p.charAt(0).toUpperCase() + p.substr(1);

                    for (var i=0; i<v.length; i++) {
                        if (typeof s[v[i] + p] == 'string') { return true; }
                        }
                        return false;
                        }
                        if ( isSupportTransition() ) {
                        document.documentElement.className += " csstransitions";	
                        }
                        <?php
                        echo '</script>';
                    }

                    function parse_slug_to_ids($slug_string, $post_type = 'post') {
                        $slug_arr = explode(',', $slug_string);
                        $return = array();
                        if (count($slug_arr) > 0) {
                            foreach ($slug_arr as $slug) {
                                array_push($return, $this->get_id_by_slug(trim($slug), $post_type));
                            }
                        }
                        return $return;
                    }

                    function get_id_by_slug($slug, $post_type = 'post') {
                        $args = array(
                            'name' => $slug,
                            'post_type' => $post_type,
                            'post_status' => 'publish',
                            'numberposts' => 1
                        );
                        $my_posts = get_posts($args);
                        if ($my_posts) {
                            return $my_posts[0]->ID;
                        } else {
                            return null;
                        }
                    }

                    /**
                     * Get a list of post types that can be accessed publicly
                     *
                     * does not include attachments, Builder layouts and layout parts,
                     * and also custom post types in Builder that have their own module.
                     *
                     * @return array of key => label pairs
                     */
                    function get_public_post_types($exclude_builder_post_types = true) {
                        $result = array();
                        $post_types = get_post_types(array('public' => true, 'publicly_queryable' => 'true'), 'objects');
                        $excluded_types = array('attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section');
                        if ($exclude_builder_post_types) {
                            $excluded_types = array_merge($this->builder_cpt, $excluded_types);
                        }
                        foreach ($post_types as $key => $value) {
                            if (!in_array($key, $excluded_types)) {
                                $result[$key] = $value->labels->singular_name;
                            }
                        }

                        return apply_filters('builder_get_public_post_types', $result);
                    }

                    /**
                     * Get a list of taxonomies that can be accessed publicly
                     *
                     * does not include post formats, section categories (used by some themes),
                     * and also custom post types in Builder that have their own module.
                     *
                     * @return array of key => label pairs
                     */
                    function get_public_taxonomies($exclude_builder_post_types = true) {
                        $result = array();
                        $taxonomies = get_taxonomies(array('public' => true), 'objects');
                        $excludes = array('post_format', 'section-category');
                        if ($exclude_builder_post_types) { // exclude taxonomies from Builder CPTs
                            foreach ($this->builder_cpt as $value) {
                                $excludes[] = "{$value}-category";
                            }
                        }
                        foreach ($taxonomies as $key => $value) {
                            if (!in_array($key, $excludes)) {
                                $result[$key] = $value->labels->name;
                            }
                        }

                        return apply_filters('builder_get_public_taxonomies', $result);
                    }

                    /**
                     * If installation is in debug mode, returns '' to load non-minified scripts and stylesheets.
                     *
                     * @since 1.0.3
                     */
                    function minified() {
                        return ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';
                    }

                    /**
                     * Return the URL or the directory path for a template, template part or content builder styling stylesheet.
                     * 
                     * @since 2.2.5
                     *
                     * @param string $mode Whether to return the directory or the URL. Can be 'bydir' or 'byurl' correspondingly. 
                     * @param int $single ID of layout, layour part or entry that we're working with.
                     *
                     * @return string
                     */
                    public static function get_stylesheet($mode = 'bydir', $single = null) {
                        static $before;
                        if (!isset($before)) {
                            $upload_dir = wp_upload_dir();
                            $before = array(
                                'bydir' => $upload_dir['basedir'],
                                'byurl' => $upload_dir['baseurl'],
                            );
                        }
                        if (is_null($single)) {
                            $single = get_the_ID();
                        }

                        $single = is_int($single) ? get_post($single) : get_page_by_path($single, OBJECT, 'tbuilder_layout_part');

                        if (!is_object($single)) {
                            return '';
                        }

                        $single = $single->ID;

                        $path = "$before[$mode]/themify-css";

                        if ('bydir' == $mode) {
                            if (!function_exists('WP_Filesystem')) {
                                require_once ABSPATH . 'wp-admin/includes/file.php';
                            }
                            WP_Filesystem();
                            global $wp_filesystem;
                            $dir_exists = $wp_filesystem->is_dir($path);
                            if (!$dir_exists) {
                                $dir_exists = $wp_filesystem->mkdir($path, FS_CHMOD_DIR);
                            }
                        }

                        $stylesheet = "$path/themify-builder-$single-generated.css";

                        /**
                         * Filters the return URL or directory path including the file name.
                         *
                         * @param string $stylesheet Path or URL for the global styling stylesheet.
                         * @param string $mode What was being retrieved, 'bydir' or 'byurl'.
                         * @param int $single ID of the template, template part or content builder that we're fetching.
                         *
                         */
                        return apply_filters('themify_builder_get_stylesheet', $stylesheet, $mode, $single);
                    }

                    /**
                     * Build style recursively. Written for sub_row styling generation.
                     * 
                     * @since 2.2.6
                     * 
                     * @param array $data Collection of styling data.
                     * @param int $style_id ID of the current entry.
                     * @param string $sub_row Row ID when it's a sub row. This is used starting from second level depth.
                     *
                     * @return string
                     */
                    function recursive_style_generator($data, $style_id, $sub_row = '') {
                        $css_to_save = '';
                        if (!is_array($data)) {
                            return $css_to_save;
                        }
                        foreach ($data as $row_index => $row) {
                            $row_order = $row_index;

                            if (isset($row['row_order'])) {
                                $row_order = $row['row_order'];
                            }

                            if (isset($row['styling']) && !empty($row['styling']) && is_array($row['styling'])) {
                                $selector = ".themify_builder_{$style_id}_row.module_row_{$row_order}";

                                $css_to_save .= $this->get_custom_styling(
                                        $selector, 'row', $row['styling'], false, 'css'
                                );
                            }
                            if (!isset($row['cols']) || !is_array($row['cols'])) {
                                continue;
                            }
                            foreach ($row['cols'] as $col_index => $col) {
                                $column_order = $col_index;

                                if (isset($col['column_order'])) {
                                    $column_order = $col['column_order'];
                                }

                                // column styling
                                if (isset($col['styling']) && !empty($col['styling']) && is_array($col['styling'])) {

                                    $col_or_sub_col = 'column';

                                    // dealing with 1st level columns
                                    if (empty($sub_row)) {
                                        $selector = ".module_row_{$row_order}" .
                                                " .module_column_{$column_order}.tb_{$style_id}_column";
                                    } else { // dealing with 2nd level columns (sub-columns)
                                        $row_col = $sub_row[8] . '-' . $sub_row[10];
                                        $selector = ".sub_column_post_{$style_id}.sub_column_{$row_col}-{$row_order}-{$column_order}";

                                        $col_or_sub_col = 'sub_column';
                                    }

                                    $css_to_save .= $this->get_custom_styling(
                                            $selector, $col_or_sub_col, $col['styling'], false, 'css'
                                    );
                                }

                                if (!isset($col['modules']) || !is_array($col['modules'])) {
                                    continue;
                                }
                                foreach ($col['modules'] as $mod_index => $mod) {
                                    if (is_null($mod)) {
                                        continue;
                                    }
                                    if (isset($mod['mod_name'])) {
                                        if ('layout-part' == $mod['mod_name']) {
                                            $lp = get_page_by_path($mod['mod_settings']['selected_layout_part'], OBJECT, 'tbuilder_layout_part');
                                            $lp_meta = get_post_meta($lp->ID, $this->meta_key, true);
                                            self::remove_cache($lp->ID);
                                            if (!empty($lp_meta)) {
                                                foreach ($lp_meta as $lp_row_index => $lp_row) {
                                                    if (!empty($lp_row['styling']) && is_array($lp_row['styling'])) {
                                                        $css_to_save .= $this->get_custom_styling(
                                                                ".themify_builder_content-$lp->ID .module_row_{$lp_row['row_order']}", 'row', $lp_row['styling'], false, 'css'
                                                        );
                                                    }
                                                    if (isset($lp_row['cols']) && is_array($lp_row['cols'])) {
                                                        foreach ($lp_row['cols'] as $lp_col_index => $lp_col) {
                                                            if (isset($lp_col['modules']) && is_array($lp_col['modules'])) {
                                                                foreach ($lp_col['modules'] as $lp_mod_index => $lp_mod) {
                                                                    if (is_null($lp_mod)) {
                                                                        continue;
                                                                    }
                                                                    if (empty($sub_row)) {
                                                                        $this_index = "$lp_row_index-$lp_col_index-$lp_mod_index";
                                                                    } else {
                                                                        if (isset($row['row_order'])) {
                                                                            $this_index = $sub_row . "{$row['row_order']}-$lp_col_index-$lp_mod_index";
                                                                        } else {
                                                                            $sr_index = $row_index + 1;
                                                                            $this_index = $sub_row . "$sr_index-$lp_col_index-$lp_mod_index";
                                                                        }
                                                                    }
                                                                    $css_to_save .= $this->get_custom_styling(
                                                                            ".themify_builder .{$lp_mod['mod_name']}-$lp->ID-$this_index", $lp_mod['mod_name'], $lp_mod['mod_settings'], false, 'css'
                                                                    );
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            if (empty($sub_row)) {
                                                $this_index = "$row_index-$col_index-$mod_index";
                                            } else {
                                                if (isset($row['row_order'])) {
                                                    $this_index = $sub_row . "{$row['row_order']}-$col_index-$mod_index";
                                                } else {
                                                    $sr_index = $row_index + 1;
                                                    $this_index = $sub_row . "$sr_index-$col_index-$mod_index";
                                                }
                                            }
                                            $css_to_save .= $this->get_custom_styling(
                                                    ".themify_builder .{$mod['mod_name']}-$style_id-$this_index", $mod['mod_name'], $mod['mod_settings'], false, 'css'
                                            );
                                        }
                                    }
                                    if (isset($mod['row_order'])) {
                                        $css_to_save .= $this->recursive_style_generator(array($mod), $style_id, "sub_row_$row_index-$col_index-");
                                    }
                                }
                            }
                        }
                        return $css_to_save;
                    }

                    /**
                     * Write stylesheet file.
                     * 
                     * @since 2.2.5
                     * 
                     * @return array
                     */
                    function write_stylesheet($data_set) {
                        // Information about how writing went.
                        $results = array();

                        $this->saving_stylesheet = true;
                        $style_id = $data_set['id'];

                        $css_to_save = $this->recursive_style_generator($data_set['data'], $style_id);

                        $css_file = $this->get_stylesheet('bydir', (int) $style_id);

                        $filesystem = Themify_Filesystem::get_instance();

                        if ($filesystem->execute->is_file($css_file)) {
                            $filesystem->execute->delete($css_file);
                        }

                        // Write file information to be returned.
                        $results['css_file'] = $css_file;

                        if (!empty($css_to_save)) {
                            /**
                             * Filters the CSS that will be saved for modules that output inline <style> tags for styling changes not managed by get_styling().
                             * 
                             * @since 2.2.5
                             *
                             * @param string $css_to_save CSS text right before it's saved.
                             */
                            $css_to_save = apply_filters('themify_builder_css_to_stylesheet', $css_to_save);
                            if ($write = $filesystem->execute->put_contents($css_file, $css_to_save, FS_CHMOD_FILE)) {
                                update_option('themify_builder_stylesheet_timestamp', current_time('y.m.d.H.i.s'));
                            }

                            // Add information about writing.
                            $results['write'] = $write;

                            // Save Google Fonts
                            global $themify;
                            if (isset($themify->builder_google_fonts) && !empty($themify->builder_google_fonts)) {
                                $builder_fonts = get_option('themify_builder_google_fonts');
                                if (empty($builder_fonts) || !is_array($builder_fonts)) {
                                    $builder_fonts = array();
                                }
                                if (isset($builder_fonts[$style_id])) {
                                    $builder_fonts[$style_id] = $themify->builder_google_fonts;
                                    $entry_fonts = $builder_fonts;
                                } else {
                                    $entry_fonts = array($style_id => $themify->builder_google_fonts) + $builder_fonts;
                                }
                                update_option('themify_builder_google_fonts', $entry_fonts);
                            }
                        } else {
                            // Add information about writing.
                            $results['write'] = esc_html__('Nothing written. Empty CSS.', 'themify');
                        }

                        $this->saving_stylesheet = false;

                        return $results;
                    }

                    /**
                     * Checks if the builder stylesheet exists and enqueues it. Otherwise hooks an action to wp_head to build the CSS and output it.
                     * 
                     * @since 2.2.5
                     */
                    function delete_stylesheet() {
                        $css_file = $this->get_stylesheet();
                        $filesystem = Themify_Filesystem::get_instance();

                        if ($filesystem->execute->is_file($css_file)) {
                            $filesystem->execute->delete($css_file);
                        }
                    }

                    /**
                     * If there wasn't a proper stylesheet, that is, one that matches this slug, generate it.
                     *
                     * @since 2.2.5
                     *
                     * @param int $post_id
                     */
                    function build_stylesheet_if_needed($post_id) {
                        //verify post is not a revision
                        if (!wp_is_post_revision($post_id)) {
                            if (!$this->is_readable_and_not_empty($this->get_stylesheet('bydir', $post_id))) {
                                if ($post_data = get_post_meta($post_id, $this->meta_key, true)) {
                                    // Write Stylesheet
                                    $this->write_stylesheet(array('id' => $post_id, 'data' => $post_data));
                                }
                            }
                        }
                    }

                    /**
                     * Checks whether a file exists, can be loaded and is not empty.
                     * 
                     * @since 2.2.5
                     * 
                     * @param string $file_path Path in server to the file to check.
                     * 
                     * @return bool
                     */
                    function is_readable_and_not_empty($file_path = '') {
                        if (empty($file_path)) {
                            return false;
                        }
                        return is_readable($file_path) && 0 !== filesize($file_path);
                    }

                    /**
                     * Tries to enqueue stylesheet. If it's not possible, it hooks an action to wp_head to build the CSS and output it.
                     * 
                     * @since 2.2.5
                     */
                    function enqueue_stylesheet() {
                        if (apply_filters('themify_builder_enqueue_stylesheet', true)) {
                            // If enqueue fails, maybe the file doesn't exist...
                            if (!$this->test_and_enqueue()) {
                                // Try to generate it right now.
                                if ($post_data = get_post_meta(get_the_ID(), $this->meta_key, true)) {
                                    // Write Stylesheet
                                    $this->write_stylesheet(array('id' => get_the_ID(), 'data' => $post_data));
                                }
                                if (!$this->test_and_enqueue()) {
                                    // No luck. Let's do it inline.
                                    $this->is_front_end_style_inline = true;
                                    add_action('themify_builder_row_start', array($this, 'render_row_styling'), 10, 2);
                                    add_action('themify_builder_column_start', array($this, 'render_column_styling'), 10, 3);
                                }
                            }
                        }
                    }

                    /**
                     * Checks if the builder stylesheet exists and enqueues it.
                     * 
                     * @since 2.2.5
                     * 
                     * @return bool True if enqueue was successful, false otherwise.
                     */
                    function test_and_enqueue() {
                        $stylesheet_path = $this->get_stylesheet();
                        if ($this->is_readable_and_not_empty($stylesheet_path)) {
                            setlocale(LC_CTYPE, get_locale() . '.UTF-8');
                            $handler = pathinfo($stylesheet_path);
                            wp_enqueue_style($handler['filename'], themify_https_esc($this->get_stylesheet('byurl')), array(), $this->get_stylesheet_version());
                            // Load Google Fonts. Despite this function is hit twice while on-the-fly stylesheet generation, they're loaded only once.
                            $this->enqueue_fonts();
                            return true;
                        }
                        return false;
                    }

                    /**
                     * Return timestamp to use as stylesheet version.
                     * 
                     * @since 2.2.5
                     */
                    function get_stylesheet_version() {
                        return get_option('themify_builder_stylesheet_timestamp');
                    }

                    /**
                     * Enqueues Google Fonts
                     * 
                     * @since 2.2.6
                     */
                    function enqueue_fonts() {
                        $entry_google_fonts = get_option('themify_builder_google_fonts');
                        if (isset($entry_google_fonts) && !empty($entry_google_fonts) && is_array($entry_google_fonts)) {
                            $entry_id = get_the_ID();
                            if (isset($entry_google_fonts[$entry_id])) {
                                wp_enqueue_style('builder-google-fonts', themify_https_esc('http://fonts.googleapis.com/css') . '?family=' . rtrim($entry_google_fonts[$entry_id], '|'));
                            }
                        }
                    }

                    /**
                     * Ajax Clear all builder caches.
                     * 
                     * @since 2.4.2
                     * @access public
                     * @return json
                     */
                    public function clear_all_builder_caches() {
                        check_ajax_referer('ajax-nonce', 'nonce');
                        // Clear the cache
                        TFCache::removeDirectory(TFCache::get_cache_dir());
                        wp_send_json_success('success');
                    }

                    /**
                     * IE enhancements scripts.
                     * 
                     * @since 2.5.1
                     * @access public
                     */
                    public function ie_enhancements() {
                        echo '
					<!-- equalcolumn-ie-fix.js -->
					<!--[if IE 9]>
						<script src="' . THEMIFY_BUILDER_URI . '/js/equalcolumn-ie-fix.js"></script>
					<![endif]-->
					';
                    }

                    /**
                     * Merge user defined arguments into defaults array
                     *
                     * @return array
                     */
                    function parse_args($args, $defaults = '', $filter_key = '') {
                        // Setup a temporary array from $args
                        if (is_object($args))
                            $r = get_object_vars($args);
                        elseif (is_array($args))
                            $r = & $args;
                        else
                            wp_parse_str($args, $r);

                        // Passively filter the args before the parse
                        if (!empty($filter_key))
                            $r = apply_filters('themify_builder_before_' . $filter_key . '_parse_args', $r);

                        // Parse
                        if (is_array($defaults))
                            $r = array_merge($defaults, $r);

                        // Aggressively filter the args after the parse
                        if (!empty($filter_key))
                            $r = apply_filters('themify_builder_after_' . $filter_key . '_parse_args', $r);

                        // Return the parsed results
                        return $r;
                    }

                }

            } // class_exists check
