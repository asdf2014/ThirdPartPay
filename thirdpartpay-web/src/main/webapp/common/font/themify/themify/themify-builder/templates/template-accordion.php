<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Accordion
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('accordion', self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_accordion' => '',
        'layout_accordion' => 'plus-icon-button',
        'expand_collapse_accordion' => 'toggle',
        'color_accordion' => '',
        'accordion_appearance_accordion' => '',
        'content_accordion' => array(),
        'animation_effect' => '',
        'css_accordion' => ''
    );

    if (isset($mod_settings['accordion_appearance_accordion'])) {
        $mod_settings['accordion_appearance_accordion'] = $this->get_checkbox_data($mod_settings['accordion_appearance_accordion']);
    }

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $css_accordion, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    $ui_class = implode(' ', array('ui', 'module-' . $mod_name, $layout_accordion, $accordion_appearance_accordion, $color_accordion));
    ?>
    <!-- module accordion -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>" data-behavior="<?php echo esc_attr($expand_collapse_accordion); ?>">

        <?php if ($mod_title_accordion != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_accordion, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <ul class="<?php echo esc_attr($ui_class); ?>">
            <?php
            foreach (array_filter($content_accordion) as $content):
                $content = wp_parse_args($content, array(
                    'title_accordion' => '',
                    'default_accordion' => '',
                    'text_accordion' => '',
                ));
                ?>
                <li <?php if ($content['default_accordion'] == 'open') echo 'class="builder-accordion-active"'; ?>>
                    <div class="accordion-title"><a href="#"><?php echo wp_kses_post($content['title_accordion']); ?></a></div>
                    <div class="accordion-content <?php if ($content['default_accordion'] != 'open') echo 'default-closed'; ?> clearfix">
                        <?php
                        if (!empty($content['text_accordion'])) {
                            echo apply_filters('themify_builder_module_content', $content['text_accordion']);
                        }
                        ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php do_action('themify_builder_after_template_content_render'); ?>

    </div>
    <!-- /module accordion -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>