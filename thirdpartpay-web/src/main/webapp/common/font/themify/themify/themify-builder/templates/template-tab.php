<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Tab
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('tab', self::$post_id, array('ID' => $module_ID))):
    $fields_default = array(
        'mod_title_tab' => '',
        'layout_tab' => 'tab-top',
        'color_tab' => '',
        'tab_appearance_tab' => '',
        'tab_content_tab' => array(),
        'css_tab' => '',
        'animation_effect' => ''
    );

    if (isset($mod_settings['tab_appearance_tab']))
        $mod_settings['tab_appearance_tab'] = $this->get_checkbox_data($mod_settings['tab_appearance_tab']);

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $tab_id = $module_ID . '-' . $builder_id;
    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, 'ui', $layout_tab, $tab_appearance_tab, $color_tab, $css_tab, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    ?>

    <!-- module tab -->
    <div id="<?php echo esc_attr($tab_id); ?>" class="<?php echo esc_attr($container_class); ?>">
        <?php if ($mod_title_tab != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_tab, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <div class="builder-tabs-wrap">
            <ul class="tab-nav">
                <?php foreach ($tab_content_tab as $k => $tab): ?>
                    <li <?php echo 0 == $k ? 'aria-expanded="true"' : 'aria-expanded="false"'; ?>><a href="#tab-<?php echo esc_attr($tab_id . '-' . $k); ?>"><?php echo isset($tab['title_tab']) ? $tab['title_tab'] : ''; ?></a></li>
                <?php endforeach; ?>
            </ul>

            <?php foreach ($tab_content_tab as $k => $tab): ?>
                <div id="tab-<?php echo esc_attr($tab_id . '-' . $k); ?>" class="tab-content" <?php echo $k == 0 ? 'aria-hidden="false"' : 'aria-hidden="true"' ?>>
                    <?php
                    if (isset($tab['text_tab'])) {
                        echo apply_filters('themify_builder_module_content', $tab['text_tab']);
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php do_action('themify_builder_after_template_content_render'); ?>
    </div>
    <!-- /module tab -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>