<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Widgetized
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('widgetized', self::$post_id, array('ID' => $module_ID))):
    
    $fields_default = array(
        'mod_title_widgetized' => '',
        'sidebar_widgetized' => '',
        'custom_css_widgetized' => '',
        'background_repeat' => '',
        'animation_effect' => ''
    );
    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $custom_css_widgetized, $background_repeat, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    ?>

    <!-- module widgetized -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
        <?php
        if ($mod_title_widgetized != '')
            echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_widgetized, $fields_args)) . $mod_settings['after_title'];

        do_action('themify_builder_before_template_content_render');

        if ($sidebar_widgetized != '') {
            if (!function_exists('dynamic_sidebar') || !dynamic_sidebar($sidebar_widgetized))
                ;
        }

        do_action('themify_builder_after_template_content_render');
        ?>
    </div>
    <!-- /module widgetized -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>