<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Divider
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('divider', self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_divider' => '',
        'style_divider' => '',
        'stroke_w_divider' => '',
        'color_divider' => '',
        'top_margin_divider' => '',
        'bottom_margin_divider' => '',
        'css_divider' => ''
    );

    if (isset($mod_settings['stroke_w_divider']))
        $mod_settings['stroke_w_divider'] = 'border-width: ' . $mod_settings['stroke_w_divider'] . 'px;';

    if (isset($mod_settings['color_divider']))
        $mod_settings['color_divider'] = 'border-color: ' . $this->get_rgba_color($mod_settings['color_divider']) . ';';

    if (isset($mod_settings['top_margin_divider']))
        $mod_settings['top_margin_divider'] = 'margin-top: ' . $mod_settings['top_margin_divider'] . 'px;';

    if (isset($mod_settings['bottom_margin_divider']))
        $mod_settings['bottom_margin_divider'] = 'margin-bottom: ' . $mod_settings['bottom_margin_divider'] . 'px;';

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);

    $style = $stroke_w_divider . $color_divider . $top_margin_divider . $bottom_margin_divider;
    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $style_divider, $css_divider
                    ), $mod_name, $module_ID, $fields_args)
    );
    ?>
    <!-- module divider -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>" style="<?php echo esc_attr($style); ?>">
        <?php if ($mod_title_divider != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_divider, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>
    </div>
    <!-- /module divider -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>