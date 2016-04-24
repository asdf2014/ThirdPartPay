<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Buttons
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('buttons', self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_button' => '',
        'buttons_size' => '',
        'buttons_style'=>'',
        'content_button' => array(),
        'animation_effect' => '',
        'css_button' => ''
    );


    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $css_button, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    $ui_class = implode(' ', array('module-' . $mod_name,$buttons_size,$buttons_style));
    
    ?>
    <!-- module buttons -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">

        <?php if ($mod_title_button != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_button, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <div class="<?php echo esc_attr($ui_class); ?>">
            <?php
            foreach (array_filter($content_button) as $content):
               
                $content = wp_parse_args($content, array(
                    'label' => '',
                    'link' => '',
                    'icon' => '',
                    'new_window'=>false,
                    'button_color_bg'=>false
                ));
                ?>
                <div class="module-buttons-item">
                    <?php if($content['link']):?>
                        <a class="ui builder_button  <?php echo $content['button_color_bg']?>" <?php if($content['new_window']):?>target="_blank"<?php endif;?> href="<?php echo esc_url($content['link'])?>">
                    <?php endif;?>
                        <?php if($content['icon']):?>
                            <i class="fa <?php echo $content['icon'];?>"></i>
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
    <!-- /module buttons -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>