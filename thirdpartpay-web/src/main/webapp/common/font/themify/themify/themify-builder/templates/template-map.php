<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Map
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('map', self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_map' => '',
        'address_map' => '',
        'latlong_map' => '',
        'zoom_map' => 15,
        'w_map' => '100%',
        'w_map_static' => 500,
        'unit_w' => '',
        'h_map' => '300px',
        'unit_h' => '',
        'b_style_map' => '',
        'b_width_map' => '',
        'b_color_map' => '',
        'type_map' => 'ROADMAP',
        'scrollwheel_map' => 'disable',
        'draggable_map' => 'enable',
        'draggable_disable_mobile_map' => 'yes',
        'info_window_map' => '',
        'map_display_type' => 'dynamic',
        'css_map' => '',
        'animation_effect' => ''
    );

    if (isset($mod_settings['address_map']))
        $mod_settings['address_map'] = preg_replace('/\s+/', ' ', trim($mod_settings['address_map']));

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);
    $info_window_map = empty($info_window_map) ? sprintf('<b>%s</b><br/><p>%s</p>', __('Address', 'themify'), $address_map) : $info_window_map;

// Check if draggable should be disabled on mobile devices
    if ('enable' == $draggable_map && 'yes' == $draggable_disable_mobile_map && wp_is_mobile())
        $draggable_map = 'disable';

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $css_map, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    $style = '';

// specify border
    if (isset($mod_settings['b_width_map'])) {
        $style .= 'border: ';
        $style .= ( isset($mod_settings['b_style_map']) ) ? $mod_settings['b_style_map'] : '';
        $style .= ( isset($mod_settings['b_width_map']) ) ? ' ' . $mod_settings['b_width_map'] . 'px' : '';
        $style .= ( isset($mod_settings['b_color_map']) ) ? ' ' . $this->get_rgba_color($mod_settings['b_color_map']) : '';
        $style .= ';';
    }
    ?>
    <!-- module map -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">

        <?php if ($mod_title_map != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_map, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php if ($map_display_type == 'static') : ?>
            <?php
            $args = '';
            if (!empty($address_map)) {
                $args .= 'center=' . $address_map;
            } elseif (!empty($latlong_map)) {
                $args .= 'center=' . $latlong_map;
            }
            $args .= '&zoom=' . $zoom_map;
            $args .= '&maptype=' . strtolower($type_map);
            $args .= '&size=' . preg_replace("/[^0-9]/", "", $w_map_static) . 'x' . preg_replace("/[^0-9]/", "", $h_map);
            ?>
            <img style="<?php echo esc_attr($style); ?>" src="//maps.googleapis.com/maps/api/staticmap?<?php echo $args; ?>" />

        <?php else : ?>
            <?php
            $style .= 'width:';
            $style .= ( isset($mod_settings['w_map']) ) ? $mod_settings['w_map'] . $mod_settings['unit_w'] : '100%';
            $style .= ';';
            $style .= 'height:';
            $style .= ( isset($mod_settings['h_map']) ) ? $mod_settings['h_map'] . $mod_settings['unit_h'] : '300px';
            $style .= ';';

            if (!empty($address_map) || !empty($latlong_map)) {
                $geo_address = !empty($address_map) ? $address_map : $latlong_map;
                ?>
                <?php
                $data['address'] = $geo_address;
                $data['zoom'] = $zoom_map;
                $data['type'] = $type_map;
                $data['scroll'] = $scrollwheel_map == 'enable';
                $data['drag'] = 'enable' == $draggable_map;
                ?>
                <div data-map="<?php esc_attr_e(base64_encode(json_encode($data))) ?>" class="themify_map map-container"  style="<?php echo esc_attr($style); ?>"  data-info-window="<?php echo esc_attr($info_window_map); ?>" data-reverse-geocoding="<?php echo ( empty($address_map) && !empty($latlong_map) ) ? true : false; ?>"></div>
            <?php } ?>
        <?php endif; ?>

    </div>
    <!-- /module map -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>