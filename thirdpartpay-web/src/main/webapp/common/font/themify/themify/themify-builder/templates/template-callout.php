<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Callout
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('callout', self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_callout' => '',
        'appearance_callout' => '',
        'layout_callout' => '',
        'color_callout' => '',
        'heading_callout' => '',
        'text_callout' => '',
        'action_btn_link_callout' => '#',
        'open_link_new_tab_callout' => '',
        'action_btn_text_callout' => false,
        'action_btn_color_callout' => '',
        'action_btn_appearance_callout' => '',
        'css_callout' => '',
        'background_repeat' => '',
        'animation_effect' => ''
    );

    if (isset($mod_settings['appearance_callout']))
        $mod_settings['appearance_callout'] = $this->get_checkbox_data($mod_settings['appearance_callout']);

    if (isset($mod_settings['action_btn_appearance_callout']))
        $mod_settings['action_btn_appearance_callout'] = $this->get_checkbox_data($mod_settings['action_btn_appearance_callout']);

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, 'ui', $layout_callout, $color_callout, $css_callout, $appearance_callout, $background_repeat, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    $ui_class = implode(' ', array('ui', 'builder_button', $action_btn_color_callout, $action_btn_appearance_callout));
    ?>
    <!-- module callout -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">

        <?php if ($mod_title_callout != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_callout, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <div class="callout-inner">
            <div class="callout-content">
                <h3 class="callout-heading"><?php echo wp_kses_post($heading_callout); ?></h3>
                <?php
                echo apply_filters('themify_builder_module_content', $text_callout);
                ?>
            </div>
            <!-- /callout-content -->

            <?php if ($action_btn_text_callout) : ?>
                <p class="callout-button">
                    <a href="<?php echo esc_url($action_btn_link_callout); ?>" class="<?php echo esc_attr($ui_class); ?>"<?php echo 'yes' == $open_link_new_tab_callout ? ' target="_blank"' : ''; ?>>
                        <?php echo wp_kses_post($action_btn_text_callout); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <!-- /callout-content -->

        <?php do_action('themify_builder_after_template_content_render'); ?>
    </div>
    <!-- /module callout -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>