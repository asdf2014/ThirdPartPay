<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Menu
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('menu', self::$post_id, array('ID' => $module_ID))):
    
    $fields_default = array(
        'mod_title_menu' => '',
        'layout_menu' => '',
        'custom_menu' => '',
        'color_menu' => '',
        'according_style_menu' => '',
        'css_menu' => '',
        'animation_effect' => ''
    );

    if (isset($mod_settings['according_style_menu']))
        $mod_settings['according_style_menu'] = $this->get_checkbox_data($mod_settings['according_style_menu']);

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $css_menu, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    ?>

    <!-- module menu -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
        <?php if ($mod_title_menu != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_menu, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php
        $args = array(
            'menu' => $custom_menu,
            'menu_class' => 'ui nav ' . $layout_menu . ' ' . $color_menu . ' ' . $according_style_menu
        );
        wp_nav_menu($args);
        ?>
    </div>
    <!-- /module menu -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>