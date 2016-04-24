<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Gallery
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('gallery', self::$post_id, array('ID' => $module_ID))):
    
    $fields_default = array(
        'mod_title_gallery' => '',
        'layout_gallery' => 'grid',
        'image_size_gallery' => 'thumbnail',
        'shortcode_gallery' => '',
        'thumb_w_gallery' => '',
        'thumb_h_gallery' => '',
        's_image_w_gallery'=>'',
        's_image_h_gallery'=>'',
        's_image_size_gallery'=>'full',
        'appearance_gallery' => '',
        'css_gallery' => '',
        'gallery_images' => array(),
        'gallery_columns'=>false,
        'link_opt' => false,
        'rands' => '',
        'animation_effect' => ''
    );

    if (isset($mod_settings['appearance_gallery']))
        $mod_settings['appearance_gallery'] = $this->get_checkbox_data($mod_settings['appearance_gallery']);

    if (isset($mod_settings['shortcode_gallery'])) {
        $mod_settings['gallery_images'] = $this->get_images_from_gallery_shortcode($mod_settings['shortcode_gallery']);
    }

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);
    
    if (isset($mod_settings['shortcode_gallery'])) {
        $fields_args['link_opt'] = !$link_opt?$this->get_gallery_param_option($mod_settings['shortcode_gallery']):$link_opt;
    }
    
    if(!$gallery_columns){
        $columns = ( $shortcode_gallery != '' ) ? $this->get_gallery_param_option($shortcode_gallery, 'columns') : '';
        $columns = ( $columns == '' ) ? 3 : $columns;
        $columns = intval($columns);
    }
    else{
        $columns = $gallery_columns;
    }

    // Get image size attribute from shortcode
    $sc_image_size = ( '' !== $shortcode_gallery ) ? $this->get_gallery_param_option( $shortcode_gallery, 'size' ) : '';
    if ( '' != $sc_image_size ) 
        $fields_args['image_size_gallery'] = $sc_image_size;

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, 'gallery', 'gallery-columns-' . $columns, 'layout-' . $layout_gallery, $appearance_gallery, $css_gallery, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    ?>
    <!-- module gallery -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">

        <?php if ($mod_title_gallery != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_gallery, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php
        // render the template
        $this->retrieve_template('template-' . $mod_name . '-' . $layout_gallery . '.php', array(
            'module_ID' => $module_ID,
            'mod_name' => $mod_name,
            'gallery_images' => $gallery_images,
            'columns' => $columns,
            'settings' => ( isset($fields_args) ? $fields_args : array() )
                ), '', '', true);
        ?>

    </div>
    <!-- /module gallery -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>