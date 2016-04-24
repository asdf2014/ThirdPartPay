<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (current_user_can('manage_options')) {
    remove_action('admin_init', array('WP_Customize', 'admin_init'));
}

/**
 * Themify customizer controls and settings.
 */
class Themify_Customizer {

    /**
     * Settings to build controls in Theme Customizer
     * @var array
     */
    var $settings = array();

    /**
     * List of selector/property/property value to build CSS rules.
     * @var array
     */
    var $styles = array();

    /**
     * Customizer mode, initially unset, later set to 'basic' or 'advanced'.
     * @var string
     */
    var $mode;

    /**
     * Accordion identified
     * @var string
     */
    var $current_accordion_slug = 0;

    /**
     * Flag to know if we're in the middle of saving the stylesheet.
     * @var string
     */
    var $saving_stylesheet = false;

    /**
     * Flag to know if we're in the middle of saving the stylesheet.
     * @var string
     */
    var $customizer_fonts = array();

    /**
     * Initialize class
     */
    function __construct() {

        define('THEMIFY_CUSTOMIZER_URI', THEMIFY_URI . '/customizer');
        define('THEMIFY_CUSTOMIZER_DIR', THEMIFY_DIR . '/customizer');

        if ($this->is_advanced_mode()) {
            if (is_file(THEME_DIR . '/customizer-config.php')) {
                require_once THEME_DIR . '/customizer-config.php';
            }
        } else {
            if (is_file(THEME_DIR . '/customizer-basic-config.php')) {
                require_once THEME_DIR . '/customizer-basic-config.php';
            } elseif (is_file(THEME_DIR . '/customizer-config.php')) {
                // This is for backwards compatibility in case user upgrades framework but not theme
                // in which case the customizer-basic-config.php won't be there yet.
                require_once THEME_DIR . '/customizer-config.php';
            }
        }

        // Build list of settings for live preview and CSS generation
        add_action('customize_register', array($this, 'build_settings_and_styles'), 12);
        add_action('after_setup_theme', array($this, 'build_settings_and_styles'));

        // Initialize Theme Customizer
        add_action('customize_register', array($this, 'customize_register'), 14);

        // Enqueue Javascript for Theme Customizer control
        add_action('customize_controls_enqueue_scripts', array($this, 'customize_control_scripts'));

        // Enqueue Javascript for Theme Customizer live preview
        add_action('customize_preview_init', array($this, 'live_preview_scripts'));

        add_action('wp_ajax_themify_customizer_save_option', array($this, 'save_option'));
        add_action('wp_ajax_themify_customizer_get_option', array($this, 'get_option'));

        // Build CSS and save stylesheet
        add_action('customize_save_after', array($this, 'write_stylesheet'));

        // Output custom styling
        add_action('wp_enqueue_scripts', array($this, 'enqueue_stylesheet'), 14);

        // Remove stylesheet on theme switch
        add_action('switch_theme', array($this, 'delete_stylesheet'));
    }

    /**
     * Build list of styling settings with:
     * - controls for live preview
     * - selectors/properties for CSS rules generation
     *
     * @since 1.0.0
     */
    function build_settings_and_styles() {
        ////////////////////////
        // Build Controls
        ////////////////////////
        $this->settings = apply_filters('themify_customizer_settings', array());

        ////////////////////////
        // Build CSS Styling
        ////////////////////////
        foreach ($this->settings as $key => $setting) {
            if (isset($setting['selector'])) {
                $this->styles[$setting['selector']][] = array(
                    'prop' => isset($setting['prop']) ? $setting['prop'] : '',
                    'key' => isset($key) ? $key : '',
                    'prefix' => isset($setting['prefix']) ? $setting['prefix'] : '',
                );
            }
        }
    }

    /**
     * Parameters for accordion start.
     *
     * @param string $label
     * @param string $section
     * @return array
     */
    function accordion_start($label = '', $section = 'themify_options') {
        return array(
            'control' => array(
                'type' => 'Themify_Sub_Accordion_Start',
                'label' => $label,
                'section' => $section,
            ),
        );
    }

    /**
     * Parameters for accordion end.
     *
     * @param string $label
     * @param string $section
     * @return array
     */
    function accordion_end($label = '', $section = 'themify_options') {
        return array();
    }

    /**
     * Enqueue script for custom control.
     */
    function customize_control_scripts() {

        // If Customizer is active and user cleared all styling.
        if (isset($_GET['cleared']) && 'true' == $_GET['cleared']) {
            $this->delete_stylesheet();
        }

        // Font Icon CSS
        wp_enqueue_style('themify-icons', THEMIFY_URI . '/themify-icons/themify-icons.css', array(), THEMIFY_VERSION);

        // Minicolors CSS
        wp_enqueue_style('themify-colorpicker', THEMIFY_URI . '/css/jquery.minicolors.css', array(), THEMIFY_VERSION);

        // Enqueue media scripts
        wp_enqueue_media();

        // Controls CSS
        wp_enqueue_style('themify-customize-control', THEMIFY_CUSTOMIZER_URI . '/css/themify.customize-control.css', array(), THEMIFY_VERSION);

        // Minicolors JS
        wp_enqueue_script('themify-colorpicker-js', THEMIFY_URI . '/js/jquery.minicolors.js', array('jquery'), THEMIFY_VERSION);


        //Combobox JS
        wp_enqueue_style('themify-combobox', THEMIFY_CUSTOMIZER_URI . '/css/jquery.scombobox.min.css', array(), THEMIFY_VERSION);
        wp_enqueue_script('themify-combobox', THEMIFY_CUSTOMIZER_URI . '/js/jquery.scombobox.min.js', array('jquery'), THEMIFY_VERSION, true);


        // Controls JS
        wp_enqueue_script('themify-customize-control', THEMIFY_CUSTOMIZER_URI . '/js/themify.customize-control.js', array('jquery', 'customize-controls', 'underscore', 'backbone'), THEMIFY_VERSION, true);
        $controls = array(
            'nonce' => wp_create_nonce('ajax-nonce'),
            'clearMessage' => __('This will reset all styling and customization. Do you want to proceed?', 'themify'),
            'confirm_on_unload' => __('You have unsaved data.', 'themify'),
        );

        // Pass JS variables to controls
        wp_localize_script('themify-customize-control', 'themifyCustomizerControls', $controls);
    }

    /**
     * Enqueue script for live preview.
     */
    function live_preview_scripts() {
        // Live preview JS
        wp_enqueue_script('themify-customize-preview', THEMIFY_CUSTOMIZER_URI . '/js/themify.customize-preview.js', array('jquery', 'customize-preview', 'underscore', 'backbone'), THEMIFY_VERSION, true);
        $controls = array(
            'nonce' => wp_create_nonce('ajax-nonce'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'isRTL' => is_rtl(),
        );
        foreach ($this->settings as $key => $params) {
            if (!isset($params['selector'])) {
                continue;
            }
            if ($this->endsWith($key, '_font')) {
                $controls['fontControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_background')) {
                $controls['backgroundControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_position')) {
                $controls['positionControls'][$key] = $params['selector'];
            } elseif (false !== stripos($key, '-logo_')) {
                $controls['logoControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '-tagline')) {
                $controls['taglineControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_border')) {
                $controls['borderControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_margin')) {
                $controls['marginControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_padding')) {
                $controls['paddingControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_width')) {
                $controls['widthControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_height')) {
                $controls['heightControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_color')) {
                $controls['colorControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, 'customcss')) {
                $controls['customcssControls'][$key] = $params['selector'];
            } elseif ($this->endsWith($key, '_imageselect')) {
                $controls['imageselectControls'][$key] = $params['selector'];
            }
        }

        // Pass JS variables to live preview scripts
        wp_localize_script('themify-customize-preview', 'themifyCustomizer', $controls);
    }

    /**
     * Checks if the string ends with a certain substring.
     * 
     * @since 2.1.9
     * 
     * @param string $haystack Main string to search in.
     * @param string $needle Substring that must be found at the end of main string.
     * 
     * @return bool Whether the substring is found at the end of the main string.
     */
    function endsWith($haystack, $needle) {
        $needle_length = strlen($needle);
        $offset = strlen($haystack) - $needle_length;
        $length = $needle_length;
        return @substr_compare($haystack, $needle, $offset, $length) === 0;
    }

    /**
     * Save a blog option.
     *
     * @since 1.0.0
     */
    function save_option() {
        check_ajax_referer('ajax-nonce', 'nonce');
        if (isset($_POST['option']) && isset($_POST['value'])) {
            update_option($_POST['option'], stripslashes($_POST['value']));
            echo 'saved';
        } else {
            echo 'notsaved';
        }
        die();
    }

    /**
     * Get a blog option.
     *
     * @since 1.0.0
     */
    function get_option() {
        check_ajax_referer('ajax-nonce', 'nonce');
        if (isset($_POST['option']) && '' != $_POST['option']) {
            switch ($_POST['option']) {
                case 'blogname':
                    echo preg_replace_callback("/(&#[0-9]+;)/", array($this, 'decode_entities'), html_entity_decode(get_bloginfo('name')));
                    break;
                case 'blogdescription':
                    echo preg_replace_callback("/(&#[0-9]+;)/", array($this, 'decode_entities'), html_entity_decode(get_bloginfo('description')));
                    break;
                default:
                    echo get_option($_POST['option']);
                    break;
            }
        } else {
            echo 'notfound';
        }
        die();
    }

    /**
     * Checks if the user enabled advanced or basic mode.
     * Saves option to database to fetch when customizer mode is not indicated by $_GET var.
     *
     * @since 2.0.6
     *
     * @return bool
     */
    function is_advanced_mode() {
        if (!isset($this->mode)) {
            $key = 'themify_customizer';
            // Check that var set is only 'advanced' or 'basic' since it will be saved to db
            if (isset($_GET[$key]) && ( 'advanced' == $_GET[$key] || 'basic' == $_GET[$key] )) {
                update_option($key, $_GET[$key]);
                $this->mode = ( 'advanced' == $_GET[$key] ) ? 'advanced' : 'basic';
            } else {
                $this->mode = ( 'advanced' == get_option($key, 'basic') ) ? 'advanced' : 'basic';
            }
        }
        return 'advanced' == $this->mode;
    }

    /**
     * Converts encoding for HTML entities not catched by html_entity_decode.
     * @param array $matches
     * @return string
     */
    function decode_entities($matches) {
        return mb_convert_encoding($matches[1], 'UTF-8', 'HTML-ENTITIES');
    }

    /**
     * Add customizer controls.
     * @param \WP_Customize_Manager $wp_customize
     */
    function customize_register($wp_customize) {

        foreach (array(
    'themify-control',
    'fonts-control',
    'image-select-control',
    'text-decoration-control',
    'background-control',
    'border-control',
    'margin-control',
    'padding-control',
    'color-control',
    'color-transparent-control',
    'width-control',
    'height-control',
    'position-control',
    'customcss-control',
    'image-control',
    'logo-control',
    'tagline-control',
    'clear-control',
    'sub-accordion',) as $control) {
            require_once THEMIFY_CUSTOMIZER_DIR . "/class-$control.php";
        }

        /**
         * Fires before the main Themify Options section has been added.
         * 
         * @param object $wp_customize
         */
        do_action('themify_customizer_before_add_section', $wp_customize);

        $wp_customize->add_section('themify_options', array(
            'title' => __('Themify Options', 'themify'),
            'description' => sprintf('
				<span class="themify-customizer-switcher">
					<a %s class="themify-customizer-switch basic %s"><strong>%s</strong>%s</a><a %s class="themify-customizer-switch advanced %s"><strong>%s</strong>%s</a>
 				</span>', $this->is_advanced_mode() ? 'href="' . esc_url(admin_url('customize.php?themify_customizer=basic')) . '"' : '', $this->is_advanced_mode() ? 'switchto' : 'selected', __('Basic', 'themify'), __('Less Options', 'themify'), $this->is_advanced_mode() ? '' : 'href="' . esc_url(admin_url('customize.php?themify_customizer=advanced')) . '"', $this->is_advanced_mode() ? 'selected' : 'switchto', __('Advanced', 'themify'), __('More Options', 'themify')
            ),
        ));

        /**
         * Fires after the main Themify Options section has been added.
         * 
         * @param object $wp_customize
         */
        do_action('themify_customizer_after_add_section', $wp_customize);

        $priority = 10;

        foreach ($this->settings as $setting_id => $field) {

            $setting = isset($field['setting']) ? $field['setting'] : array('default' => '');
            $wp_customize->add_setting(
                    $setting_id, // serialized solo cuando type es 'option'
                    array(
                'default' => isset($setting['default']) ? $setting['default'] : '',
                'type' => isset($setting['type']) ? $setting['type'] : 'theme_mod',
                'capability' => isset($setting['capability']) ? $setting['capability'] : 'edit_theme_options',
                'transport' => isset($setting['transport']) ? $setting['transport'] : 'postMessage',
                'sanitize_callback' => isset($setting['sanitize']) ? $setting['sanitize'] : false,
                    )
            );

            if (isset($field['control'])) {
                if ('Themify_Sub_Accordion_Start' == $field['control']['type']) {
                    $this->set_accordion_id();
                }

                $control = $field['control'];
                $class = $control['type'];
                if (class_exists($class)) {
                    $wp_customize->add_control(new $class
                            (
                            $wp_customize, $setting_id . '_ctrl', array(
                        'label' => isset($control['label']) ? $control['label'] : '',
                        'show_label' => isset($control['show_label']) ? $control['show_label'] : true,
                        'color_label' => isset($control['color_label']) ? $control['color_label'] : __('Color', 'themify'),
                        'image_options' => isset($control['image_options']) ? $control['image_options'] : array(),
                        'font_options' => isset($control['font_options']) ? $control['font_options'] : array(),
                        'section' => isset($control['section']) ? $control['section'] : 'themify_options',
                        'settings' => isset($control['settings']) ? $control['settings'] : $setting_id,
                        'priority' => $priority,
                        'accordion_id' => $this->get_accordion_id(),
                            )
                    ));
                } elseif ('nav_menu' == $class) {
                    $this->add_nav_menu_control($wp_customize, $control['location'], array(
                        'priority' => $priority,
                        'accordion_id' => $this->get_accordion_id(),
                    ));
                } else {
                    $options = array(
                        'label' => isset($control['label']) ? $control['label'] : '',
                        'show_label' => isset($control['show_label']) ? $control['show_label'] : true,
                        'color_label' => isset($control['color_label']) ? $control['color_label'] : __('Color', 'themify'),
                        'image_options' => isset($control['image_options']) ? $control['image_options'] : array(),
                        'font_options' => isset($control['font_options']) ? $control['font_options'] : array(),
                        'section' => isset($control['section']) ? $control['section'] : 'themify_options',
                        'settings' => isset($control['settings']) ? $control['settings'] : $setting_id,
                        'priority' => $priority,
                        'type' => $class,
                        'accordion_id' => $this->get_accordion_id(),
                    );
                    if ('select' == $class) {
                        $options['choices'] = $control['choices'];
                    }
                    $wp_customize->add_control($setting_id . '_ctrl', $options);
                }
            } elseif (isset($field['builtin'])) {
                
            }
            $priority++;
        }

        // Remove Nav Menus Section
        $wp_customize->remove_section('nav');

        // Remove title and tagline section
        $wp_customize->remove_setting('blogname');
        $wp_customize->remove_setting('blogdescription');
        $wp_customize->remove_control('blogname');
        $wp_customize->remove_control('blogdescription');

        // Remove control for Posts Page in Static Front Page section
        $wp_customize->remove_control('page_for_posts');
    }

    /**
     * Sets the current accordion being rendered. Used to identify controls that are nested inside it.
     *
     * @return number
     */
    function set_accordion_id() {
        $this->current_accordion_slug++;
    }

    /**
     * Returns the current accordion being rendered. Set initially when accordion_start() is called.
     *
     * @return number
     */
    function get_accordion_id() {
        return $this->current_accordion_slug;
    }

    /**
     * Add the control to render a navigation menu selector
     *
     * @param object $wp_customize Customizer instance.
     * @param string $menu_location Location to add menu to.
     * @param array $args Extra arguments to setup the control.
     */
    function add_nav_menu_control($wp_customize, $menu_location = '', $args = array()) {
        $locations = get_registered_nav_menus();
        $menus = wp_get_nav_menus();

        if ($menus) {
            $choices = array(0 => __('&mdash; Select &mdash;', 'themify'));
            foreach ($menus as $menu) {
                $choices[$menu->term_id] = wp_html_excerpt($menu->name, 40, '&hellip;');
            }

            foreach ($locations as $location => $description) {
                if ($location == $menu_location) {
                    $menu_setting_id = "nav_menu_locations[{$location}]";

                    $wp_customize->add_setting($menu_setting_id, array(
                        'sanitize_callback' => 'absint',
                        'theme_supports' => 'menus',
                    ));

                    $wp_customize->add_control($menu_setting_id, array(
                        'label' => $description,
                        'section' => 'themify_options',
                        'type' => 'select',
                        'choices' => $choices,
                        'priority' => $args['priority'],
                    ));
                    break;
                }
            }
        }
    }

    /**
     * Return the URL or the directory path for the global styling stylesheet.
     * 
     * @since 2.2.5
     *
     * @param string $mode Whether to return the directory or the URL. Can be 'bydir' or 'byurl' correspondingly. 
     *
     * @return string
     */
    function get_stylesheet($mode = 'bydir') {

        static $before;
        if (!isset($before)) {
            $upload_dir = wp_upload_dir();
            $before = array(
                'bydir' => $upload_dir['basedir'],
                'byurl' => $upload_dir['baseurl'],
            );
        }

        $stylesheet = "$before[$mode]/themify-customizer.css";

        /**
         * Filters the return URL or directory path including the file name.
         *
         * @since 2.2.5
         *
         * @param string $stylesheet Path or URL for the global styling stylesheet.
         * @param string $mode What was being retrieved, 'bydir' or 'byurl'.
         */
        return apply_filters('themify_customizer_stylesheet', $stylesheet, $mode);
    }

    /**
     * Write stylesheet file.
     * 
     * @since 2.2.5
     * 
     * @return bool
     */
    function write_stylesheet($delete_empty = true) {
        $this->saving_stylesheet = true;

        $css_file = $this->get_stylesheet();
        $css_to_save = $this->generate_css();
        $filesystem = Themify_Filesystem::get_instance();

        if (!empty($css_to_save)) {
            $filesystem->execute->delete($css_file);
            if ($filesystem->execute->put_contents($css_file, $css_to_save)) {
                update_option('themify_customizer_stylesheet_timestamp', current_time('y.m.d.H.i.s'));
                TFCache::removeDirectory(TFCache::get_cache_dir().'styles/');
            }
        } elseif ($delete_empty) {
            $filesystem->execute->delete($css_file);
            TFCache::removeDirectory(TFCache::get_cache_dir().'styles/');
        }

        $this->saving_stylesheet = false;
    }

    /**
     * Checks if the customize stylesheet exists and enqueues it. Otherwise hooks an action to wp_head to build the CSS and output it.
     * 
     * @since 2.2.5
     */
    function delete_stylesheet() {
        $css_file = $this->get_stylesheet();
        $filesystem = Themify_Filesystem::get_instance();

        if ( $filesystem->execute->is_file( $css_file ) ) {
            $filesystem->execute->delete( $css_file );
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
        if (apply_filters('themify_customizer_enqueue_stylesheet', true)) {
            global $wp_customize;
            if (( $wp_customize instanceof WP_Customize_Manager ) && $wp_customize->is_preview()) {
                add_action('wp_head', array($this, 'output_css'));
            } else {
                // If enqueue fails, maybe the file doesn't exist...
                if (!$this->test_and_enqueue()) {
                    // so try to generate stylesheet...
                    $this->write_stylesheet(false);
                    // and check again
                    if (!$this->test_and_enqueue()) {
                        // No luck. Let's do it inline.
                        add_action('wp_head', array($this, 'output_css'));
                    }
                }
            }
            $this->enqueue_fonts();
        }
    }

    /**
     * Checks if the customize stylesheet exists and enqueues it.
     * 
     * @since 2.2.5
     * 
     * @return bool True if enqueue was successful, false otherwise.
     */
    function test_and_enqueue() {
        if ($this->is_readable_and_not_empty($this->get_stylesheet())) {
            wp_enqueue_style('themify-customize', themify_https_esc($this->get_stylesheet('byurl')), array(), $this->get_stylesheet_version());
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
        return get_option('themify_customizer_stylesheet_timestamp');
    }

    /**
     * Builds the CSS and outputs it.
     * 
     * @since 2.2.5
     */
    function output_css() {
        $css = $this->generate_css();
        $custom_css = $this->custom_css();
        if (!empty($css)) {
            echo "<!--Themify Customize Styling-->\n<style id=\"themify-customize\" type=\"text/css\">\n$css\n</style>\n<!--/Themify Customize Styling-->";
        }
        if (!empty($custom_css)) {
            echo "<!--Themify Custom CSS-->\n<style id=\"themify-customize-customcss\" type=\"text/css\">\n$custom_css\n</style>\n<!--/Themify Custom CSS-->";
        }
    }

    /**
     * Enqueues Google Fonts
     * 
     * @since 2.2.6
     * @since 2.2.7 Fonts are enqueued in a single call.
     */
    function enqueue_fonts() {
        $this->generate_css(true); // Call only to enqueue fonts.
        if (!empty($this->customizer_fonts)) {
            $subsets = 'latin';
            $user_subsets = str_replace(' ', '', themify_get('setting-webfonts_subsets'));
            if (!empty($user_subsets)) {
                $subsets .= ",$user_subsets";
            }
            $customizer_fonts = str_replace(' ', '+', implode('|', $this->customizer_fonts));
            wp_enqueue_style('customizer-google-fonts', themify_https_esc('http://fonts.googleapis.com/css') . '?family=' . $customizer_fonts . '&subset=' . $subsets);
        }
    }

    /**
     * Generate CSS rules and output them.
     * @uses filter 'themify_theme_styling' over output.
     * 
     * @param bool $fonts_only Whether to extracts fonts or not.
     *
     * @return string
     */
    function generate_css($fonts_only = false) {
        global $wp_customize;
        $is_customize = isset($wp_customize);

        // Styles are saved by selector to later output them all at once
        $css = array();
        $custom_css = '';

        foreach ($this->styles as $selector => $style) {
            if (!isset($css[$selector])) {
                $css[$selector] = '';
            }

            if (!$fonts_only) {
              
                if ('customcss' == $selector && (!$is_customize || $this->saving_stylesheet )) {
                    $custom_css = $this->custom_css();
                    continue;
                }
            }
            if (isset($style[0])) {
                if (is_array($style[0])) {
                    foreach (array_map('unserialize', array_unique(array_map('serialize', $style))) as $mstyle) {
                        if ('logo' == $mstyle['prop'] || 'tagline' == $mstyle['prop']) {
                            if ($logo_props = $this->build_image_size_rule($mstyle['key'])) {
                                $css[$selector . ' img'] = $logo_props;
                            }
                            if ('logo' == $mstyle['prop']) {
                                $css[$selector . ' a'] = $this->build_color_rule($mstyle['key']);
                            }
                        }
                        $css[$selector] .= $this->build_css_rule($selector, $mstyle['prop'], $mstyle['key'], isset($mstyle['prefix']) ? $mstyle['prefix'] : '', isset($mstyle['suffix']) ? $mstyle['suffix'] : '', $fonts_only
                        );
                    }
                } else {
                    if ('logo' == $style['prop'] || 'tagline' == $style['prop']) {
                        if ($logo_props = $this->build_image_size_rule($style['key'])) {
                            $css[$selector . ' img'] = $logo_props;
                        }
                        if ('logo' == $style['prop']) {
                            $css[$selector . ' a'] = $this->build_color_rule($style['key']);
                        }
                    }
                    $css[$selector] .= $this->build_css_rule($selector, $style['prop'], $style['key'], isset($style['prefix']) ? $style['prefix'] : '', isset($style['suffix']) ? $style['suffix'] : '', $fonts_only
                    );
                }
            }
        }
        $out = '';

        if (!$fonts_only) {
            if (!empty($css)) {
                foreach ($css as $selector => $properties) {
                    $out .= '' != $properties ? "$selector {\t$properties \n}\n" : '';
                }
                if (!empty($out)) {
                    $out = "/* Themify Customize Styling */\n" . apply_filters('themify_customizer_styling', $out);
                }
            }
            if (!empty($custom_css)) {
                $out .= "\n/* Themify Custom CSS */\n" . apply_filters('themify_customizer_custom_css', $custom_css);
            }
        }
        
        return $out;
    }

    /**
     * Outputs image width and height for the logo/description image if they are available.
     *
     * @param string $mod_name
     * @return string
     */
    function build_image_size_rule($mod_name) {
        $element = json_decode($this->get_cached_mod($mod_name));
        $element_props = '';
        if (!empty($element->imgwidth)) {
            $element_props .= "\twidth: {$element->imgwidth}px;";
        }
        if (!empty($element->imgheight)) {
            $element_props .= "\n\theight: {$element->imgheight}px;";
        }
        return $element_props;
    }

    /**
     * Outputs color for the logo in text mode since it's needed for the <a>.
     *
     * @param string $mod_name
     * @return string
     */
    function build_color_rule($mod_name) {
        $element = json_decode($this->get_cached_mod($mod_name));
        $element_props = '';
        if (!empty($element->imgwidth)) {
            $element_props .= "\twidth: {$element->imgwidth}px;";
        }
        if (isset($element->color) && '' != $element->color) {
            $element_props .= "\n\tcolor: #$element->color;";
            $opacity = ( isset($element->opacity) && '' != $element->opacity ) ? $element->opacity : '1';
            $element_props .= "\n\tcolor: rgba(" . $this->hex2rgb($element->color) . ',' . $opacity . ');';
        }
        return $element_props;
    }

    /**
     * Checks if there's a Custom CSS text stored, formats it and returns it to be output.
     *
     * @return string
     */
    function custom_css() {
        $mod = $this->get_cached_mod('customcss');

        // Remove JSON stuff
        $mod = str_replace('{"css":"', '', $mod);
        $mod = str_replace('"}', '', $mod);

        // If it was escaped as a single quote, undo it as an unescaped double quote
        $mod = preg_replace('/\\\'/', '"', $mod);

        // Escape backslashes, single and double quotes
        $mod = addslashes($mod);

        // Remove double backslashes inside strings, cases like \e456
        $mod = preg_replace('/\:(\s*?)(\"|\')(\\+)(.*?)(\"|\')/', ': $2\\$4$5', $mod);

        // Rebuild JSON
        $mod = '{"css":"' . $mod . '"}';

        $customcss = json_decode($mod);
        $out = '';
        if (isset($customcss->css) && '' != $customcss->css) {
            $out .= str_replace(array('{', '}'), array("{\n\t", "\n}\n"), trim($customcss->css));
        }
        return $out;
    }

    /**
     * Return theme mod using cached static var if possible.
     *
     * @param      $name
     * @param bool $default
     *
     * @return mixed|void
     */
    function get_cached_mod($name, $default = false) {
        static $mods;

        if (!isset($mods)) {
            $mods = get_theme_mods();
        }

        if (isset($mods[$name])) {
            /**
             * Filter the theme modification, or 'theme_mod', value.
             *
             * The dynamic portion of the hook name, $name, refers to
             * the key name of the modification array. For example,
             * 'header_textcolor', 'header_image', and so on depending
             * on the theme options.
             *
             * @since 2.2.0
             *
             * @param string $current_mod The value of the current theme modification.
             */
            return apply_filters("theme_mod_{$name}", $mods[$name]);
        }

        if (is_string($default))
            $default = sprintf($default, get_template_directory_uri(), get_stylesheet_directory_uri());

        /** This filter is documented in wp-includes/theme.php */
        return apply_filters("theme_mod_{$name}", $default);
    }

    /**
     * Build a CSS rule.
     *
     * @param string $selector CSS selector.
     * @param string $style CSS property to write.
     * @param string $mod_name The 'theme_mod' option to fetch.
     * @param string $prefix Prefix for CSS property value.
     * @param string $suffix Suffix for CSS property value.
     * @param bool $fonts_only Whether to extracts fonts or not.
     * @return string CSS rule: selector, property and property value. Empty if 'theme_mod' option specified is empty.
     */
    function build_css_rule($selector, $style, $mod_name, $prefix = '', $suffix = '', $fonts_only = false) {
        $mod = $this->get_cached_mod($mod_name);
        $out = '';
        if (!empty($mod)) {
            if ('font' == $style || 'logo' == $style || 'tagline' == $style || 'decoration' == $style) {
                // Font Rule
                $font = json_decode($mod);
                if (isset($font->family->name) && '' != $font->family->name) {
                    if (isset($font->family->fonttype) && 'google' == $font->family->fonttype) {
                        $this->customizer_fonts[] = $font->family->name;
                    }
                    $out .= sprintf("\n\tfont-family:%s;", $prefix . $font->family->name . $suffix);
                }
                if (!isset($font->nostyle) || '' == $font->nostyle) {
                    if (isset($font->italic) && '' != $font->italic) {
                        $out .= sprintf("\tfont-style:%s;\n", $prefix . $font->italic . $suffix);
                    }
                    if (isset($font->bold) && '' != $font->bold) {
                        $out .= sprintf("\tfont-weight:%s;\n", $prefix . $font->bold . $suffix);
                    }
                    if (isset($font->underline) && '' != $font->underline) {
                        $out .= sprintf("\ttext-decoration:%s;\n", $prefix . $font->underline . $suffix);
                    } elseif (isset($font->linethrough) && '' != $font->linethrough) {
                        $font->linethrough = 'linethrough' == $font->linethrough ? 'line-through' : $font->linethrough;
                        $out .= sprintf("\ttext-decoration:%s;\n", $prefix . $font->linethrough . $suffix);
                    }
                } else {
                    $out .= sprintf("\tfont-style:%s;\n", $prefix . 'normal' . $suffix);
                    $out .= sprintf("\tfont-weight:%s;\n", $prefix . 'normal' . $suffix);
                    $out .= sprintf("\ttext-decoration:%s;\n", $prefix . 'none' . $suffix);
                }
                if (!isset($font->normal) || '' == $font->normal) {
                    if (isset($font->italic) && '' != $font->italic) {
                        $out .= sprintf("\tfont-style:%s;\n", $prefix . $font->italic . $suffix);
                    } elseif (isset($font->bold) && '' != $font->bold) {
                        $out .= sprintf("\tfont-weight:%s;\n", $prefix . $font->bold . $suffix);
                    }
                } else {
                    $out .= sprintf("\tfont-style:%s;\n", $prefix . $font->normal . $suffix);
                }

                if (isset($font->sizenum) && '' != $font->sizenum) {
                    $unit = isset($font->sizeunit) && '' != $font->sizeunit ? $font->sizeunit : 'px';
                    $out .= sprintf("\tfont-size:%s;\n", $prefix . $font->sizenum . $unit . $suffix);
                }
                if (isset($font->linenum) && '' != $font->linenum) {
                    $unit = isset($font->lineunit) && '' != $font->lineunit ? $font->lineunit : 'px';
                    $out .= sprintf("\tline-height:%s;\n", $prefix . $font->linenum . $unit . $suffix);
                }
                if (isset($font->texttransform) && '' != $font->texttransform) {
                    if ('notexttransform' == $font->texttransform) {
                        $out .= sprintf("\ttext-transform:%s;", $prefix . 'none' . $suffix);
                    } else {
                        $out .= sprintf("\ttext-transform:%s;", $prefix . $font->texttransform . $suffix);
                    }
                }
                if (isset($font->align) && '' != $font->align) {
                    if ('noalign' != $font->align) {
                        $out .= sprintf("\ttext-align:%s;", $prefix . $font->align . $suffix);
                    } else {
                        if ('' == is_rtl()) {
                            $out .= sprintf("\ttext-align:%s;", $prefix . 'left' . $suffix);
                        } else {
                            $out .= sprintf("\ttext-align:%s;", $prefix . 'right' . $suffix);
                        }
                    }
                }
                if (isset($font->color) && '' != $font->color) {
                    $out .= "\n\tcolor: #$font->color;";
                    $opacity = ( isset($font->opacity) && '' != $font->opacity ) ? $font->opacity : '1';
                    $out .= "\n\tcolor: rgba(" . $this->hex2rgb($font->color) . ',' . $opacity . ');';
                }
            }
            if ($fonts_only) {
                return '';
            }
            if ('logo' == $style || 'tagline' == $style) {
                // Logo/description Rule
                $element = json_decode($mod);
                if (isset($element->mode)) {
                    if ('none' == $element->mode) {
                        $out .= 'display: none;';
                    }
                }
            } elseif ('color' == $style) {
                // Color Rule
                $color = json_decode($mod);
                if (isset($color->color) && '' != $color->color) {
                    $out .= "\n\tcolor: #$color->color;";
                    $opacity = ( isset($color->opacity) && '' != $color->opacity ) ? $color->opacity : '1';
                    $out .= "\n\tcolor: rgba(" . $this->hex2rgb($color->color) . ',' . $opacity . ');';
                }
            } elseif ('background' == $style) {
                // Background Rule
                $bg = json_decode($mod);

                if (isset($bg->noimage) && 'noimage' == $bg->noimage) {
                    $out .= 'background-image: none;';
                } elseif (isset($bg->src) && '' != $bg->src) {
                    $out .= sprintf("background-image: url(%s);", $prefix . $bg->src . $suffix);
                }
                if (isset($bg->style) && '' != $bg->style) {
                    if ('fullcover' == $bg->style) {
                        $out .= "\n\tbackground-size: cover;";
                    } else {
                        $out .= "\n\tbackground-repeat: {$bg->style};";
                    }
                }
                if (isset($bg->position) && '' != $bg->position) {
                    $out .= "\n\tbackground-position: {$bg->position};";
                }
                if (isset($bg->transparent) && '' != $bg->transparent) {
                    $out .= "\n\tbackground-color: $bg->transparent;";
                } elseif (isset($bg->color) && '' != $bg->color) {
                    $out .= "\n\tbackground-color: #$bg->color;";
                    $opacity = ( isset($bg->opacity) && '' != $bg->opacity ) ? $bg->opacity : '1';
                    $out .= "\n\tbackground-color: rgba(" . $this->hex2rgb($bg->color) . ',' . $opacity . ');';
                }
            } elseif ('border' == $style) {
                // Border Rule
                $border = json_decode($mod);
                if (isset($border->disabled) && 'disabled' == $border->disabled) {
                    $out .= 'border: none;';
                } else {
                    $same = ( isset($border->same) && '' != $border->same ) ? 'same' : '';
                    if ('' == $same) {
                        foreach (array('top', 'right', 'bottom', 'left') as $side) {
                            if (isset($border->{$side})) {
                                $border_side = $border->{$side};
                                $out .= $this->setBorder($border_side, 'border-' . $side);
                            }
                        }
                    } else {
                        $out .= $this->setBorder($border);
                    }
                }
            } elseif ('margin' == $style || 'padding' == $style) {
                // Margin/Padding Rule
                $marginpadding = json_decode($mod);

                $same = ( isset($marginpadding->same) && '' != $marginpadding->same ) ? 'same' : '';
                if ('' == $same) {
                    foreach (array('top', 'right', 'bottom', 'left') as $side) {
                        if (isset($marginpadding->{$side})) {
                            if ('margin' == $style && isset($marginpadding->{$side}->auto) && 'auto' == $marginpadding->{$side}->auto) {
                                $out .= $style . '-' . $side . ': auto;';
                            } else {
                                $this_side = $marginpadding->{$side};
                                $out .= $this->setDimension($this_side, $style . '-' . $side);
                            }
                        }
                    }
                } else {
                    if ('margin' == $style && isset($marginpadding->auto) && 'auto' == $marginpadding->auto) {
                        $out .= $style . ': auto;';
                    } else {
                        $out .= $this->setDimension($marginpadding, $style);
                    }
                }
            } elseif ('width' == $style || 'height' == $style) {
                // Width/Height Rule
                $widthheight = json_decode($mod);

                if (isset($widthheight->auto) && 'auto' == $widthheight->auto) {
                    $out .= $style . ': auto;';
                } else {
                    $out .= $this->setDimension($widthheight, $style);
                }
            } elseif ('position' == $style) {
                // Position Rule
                $position = json_decode($mod);

                if (isset($position->position) && '' != $position->position) {
                    $out .= sprintf("\tposition:%s;\n", $prefix . $position->position . $suffix);
                }

                foreach (array('top', 'right', 'bottom', 'left') as $side) {
                    if (isset($position->{$side})) {
                        if (isset($position->{$side}->auto) && 'auto' == $position->{$side}->auto) {
                            $out .= $side . ': auto;';
                        } else {
                            $this_side = $position->{$side};
                            $out .= $this->setDimension($this_side, $side);
                        }
                    }
                }
            } elseif ('image' == $style) {
                // Image Rule does nothing because it's not styling by markup injected in a php function.
            }
        }
        // Build rule to return

        return $out;
    }

    /**
     * Generate border properties.
     *
     * @uses hex2rgb()
     *
     * @param object $border Object with all the necessary values.
     * @param string $property Property to set, can be border or border-left for example
     * @return string
     */
    function setBorder($border, $property = 'border') {
        $out = '';
        if (isset($border->style) && 'none' != $border->style) {
            if ('' != $border->style) {
                $out .= "\n\t$property-style: $border->style;";
            }
            if (isset($border->width) && '' != $border->width) {
                $out .= "\n\t$property-width: {$border->width}px;";
            }
            if (isset($border->color) && '' != $border->color) {
                $out .= "\n\t$property-color: #$border->color;";
                $opacity = ( isset($border->opacity) && '' != $border->opacity ) ? $border->opacity : '1';
                $out .= "\n\t$property-color: rgba(" . $this->hex2rgb($border->color) . ',' . $opacity . ');';
            }
        } else {
            $out .= "\n\t$property: none;";
        }
        return $out;
    }

    /**
     * Generate dimension properties for cases like padding or margin.
     *
     * @param object $object Object with all the necessary values.
     * @param string $property Property to set, can be margin or padding-left for example
     * @return string
     */
    function setDimension($object, $property = 'margin') {
        $out = '';
        if (isset($object->unit) && 'px' != $object->unit) {
            $unit = $object->unit;
        } else {
            $unit = 'px';
        }
        if (isset($object->width) && '' != $object->width) {
            $out .= "\n\t$property: {$object->width}$unit;";
        }
        return $out;
    }

    /**
     * Converts color in hexadecimal format to RGB format.
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
     * Inserts logo image in site title or hides it.
     *
     * @param string $location
     * @return string
     */
    function site_logo($location = '') {
        $site_name = get_bloginfo('name');
        $logo = json_decode($this->get_cached_mod($location . '_image'));
        $has_image_src = isset($logo->src) && '' != $logo->src;
        $is_image_mode = isset($logo->mode) && 'image' == $logo->mode;
        if ($is_image_mode) {
            $url = apply_filters('themify_customizer_logo_home_url', isset($logo->link) && !empty($logo->link) ? $logo->link : home_url() );
        } else {
            $url = apply_filters('themify_customizer_logo_home_url', isset($logo->link) && !empty($logo->link) ? $logo->link : home_url() );
        }
        $html = '<a href="' . esc_url($url) . '" title="' . esc_attr($site_name) . '">';
        if (isset($this->settings[$location . '_image']) && $has_image_src && $is_image_mode) {
            $html .= '<img src="' . esc_url(themify_https_esc($logo->src)) . '" alt="' . esc_attr($site_name) . '" title="' . esc_attr($site_name) . '" />';
            $html .= '<span style="display: none;">' . esc_html($site_name) . '</span>';
        } else {
            $html .= '<span>' . esc_html($site_name) . '</span>';
        }
        $html .= '</a>';
        return $html;
    }

    /**
     * Inserts image in site description or hides it.
     *
     * @param string $site_desc Site tagline.
     * @param string $location
     * @return string
     */
    function site_description($site_desc = '', $location = 'site-tagline') {
        $desc = json_decode($this->get_cached_mod($location));
        $has_image_src = isset($desc->src) && '' != $desc->src;
        $is_image_mode = isset($desc->mode) && 'image' == $desc->mode;
        $html = '';
        if (isset($this->settings[$location]) && $has_image_src && $is_image_mode) {
            $html .= '<img src="' . esc_url(themify_https_esc($desc->src)) . '" alt="' . esc_attr($site_desc) . '" title="' . esc_attr($site_desc) . '" />';
            $html .= '<span style="display: none;">' . esc_html($site_desc) . '</span>';
        } else {
            $html .= '<span>' . esc_html($site_desc) . '</span>';
        }
        return $html;
    }

}

$GLOBALS['themify_customizer'] = new Themify_Customizer;
