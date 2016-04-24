<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Box
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('box', self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_box' => '',
        'content_box' => '',
        'appearance_box' => '',
        'color_box' => '',
        'add_css_box' => '',
        'background_repeat' => '',
        'animation_effect' => ''
    );

    if (isset($mod_settings['appearance_box']))
        $mod_settings['appearance_box'] = $this->get_checkbox_data($mod_settings['appearance_box']);

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    $inner_container_classes = implode(' ', apply_filters('themify_builder_module_inner_classes', array(
        'module-' . $mod_name . '-content', 'ui', $appearance_box, $color_box, $add_css_box, $background_repeat
            ))
    );
    ?>
    <!-- module box -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
        <?php if ($mod_title_box != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_box, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <div class="<?php echo esc_attr($inner_container_classes); ?>">
            <?php echo apply_filters('themify_builder_module_content', $content_box); ?>
        </div>

        <?php do_action('themify_builder_after_template_content_render'); ?>
    </div>
    <!-- /module box -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>