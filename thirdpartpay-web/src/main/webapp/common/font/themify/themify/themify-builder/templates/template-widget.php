<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Widget
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('widget', self::$post_id, array('ID' => $module_ID))):
    
    $fields_default = array(
        'mod_title_widget' => '',
        'class_widget' => '',
        'instance_widget' => array(),
        'custom_css_widget' => '',
        'background_repeat' => '',
        'animation_effect' => ''
    );
    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);
    $new_instance = array();

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $custom_css_widget, $background_repeat, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    ?>

    <!-- module widget -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
        <?php
        if ($mod_title_widget != '')
            echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_widget, $fields_args)) . $mod_settings['after_title'];

        do_action('themify_builder_before_template_content_render');

        if (is_array($instance_widget) && count($instance_widget) > 0) {
            foreach ($instance_widget as $key => $val) {
                preg_match_all('/\[([^\]]*)\]/', $key, $matches);
                if (is_array($matches)) {
                    if (isset($matches[count($matches) - 1])) {
                        if (isset($matches[count($matches) - 1][1])) {
                            $new_instance[$matches[count($matches) - 1][1]] = $val;
                        }
                    }
                }
            }
        }

        if ($class_widget != '' && class_exists($class_widget))
            the_widget($class_widget, $new_instance);

        do_action('themify_builder_after_template_content_render');
        ?>
    </div>
    <!-- /module widget -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>