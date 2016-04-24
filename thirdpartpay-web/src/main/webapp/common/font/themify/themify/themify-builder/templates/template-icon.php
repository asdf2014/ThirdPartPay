<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Icon
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('icon', self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_icon' => '',
        'icon_size' => '',
        'icon_style'=>'',
        'content_icon' => array(),
        'animation_effect' => '',
        'css_icon' => ''
    );

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module',$module_ID, $css_icon, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    $ui_class = implode(' ', array('module-' . $mod_name, $icon_size, $icon_style));
    ?>
    <!-- module icon -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">

        <?php if ($mod_title_icon != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_icon, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <div class="<?php echo esc_attr($ui_class); ?>">
            <?php
            foreach (array_filter($content_icon) as $content):
                $content = wp_parse_args($content, array(
                    'label' => '',
                    'link' => '',
                    'icon' => '',
                    'new_window'=>false,
                    'icon_color_bg'=>false,
                ));
                ?>
                <div class="module-icon-item">
                    <?php if($content['link']):?>
                        <a <?php if($content['new_window']):?>target="_blank"<?php endif;?> href="<?php echo esc_url($content['link'])?>">
                    <?php endif;?>
                        <?php if($content['icon']):?>
                            <i class="fa <?php echo $content['icon'];?> ui <?php echo $content['icon_color_bg']?>"></i>
                        <?php endif;?>
                        <span><?php esc_attr_e($content['label'])?></span>
                    <?php if($content['link']):?>
                        </a>
                    <?php endif;?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php do_action('themify_builder_after_template_content_render'); ?>

    </div>
    <!-- /module icon -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>