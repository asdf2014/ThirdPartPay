<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Image
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('feature', self::$post_id, array('ID' => $module_ID))):

    $chart_vars = apply_filters('themify_chart_init_vars', array(
        'trackColor' => 'rgba(0,0,0,.1)',
        'scaleColor' => 0,
        'scaleLength' => 0,
        'lineCap' => 'butt',
        'rotate' => 0,
        'size' => 150,
        'lineWidth' => 3,
        'animate' => 2000
    ));

    $fields_default = array(
        'mod_title_feature' => '',
        'title_feature' => '',
        'layout_feature' => 'icon-left',
        'content_feature' => '',
        'circle_percentage_feature' => '',
        'circle_color_feature' => 'de5d5d',
        'circle_stroke_feature' => $chart_vars['lineWidth'],
        'icon_type_feature' => 'icon',
        'image_feature' => '',
        'icon_feature' => '',
        'icon_color_feature' => '000000',
        'icon_bg_feature' => '',
        'circle_size_feature' => 'medium',
        'link_feature' => '',
        'param_feature' => array(),
        'css_feature' => '',
        'animation_effect' => ''
    );

    if (isset($mod_settings['param_feature']))
        $mod_settings['param_feature'] = explode('|', $mod_settings['param_feature']);

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    /* configure the chart size based on the option */
    if ($circle_size_feature == 'large') {
        $chart_vars['size'] = 200;
    } elseif ($circle_size_feature == 'small') {
        $chart_vars['size'] = 100;
    }

	$circle_percentage_feature = preg_replace( '/%$/', '', $circle_percentage_feature ); // remove % if added by user
    $chart_class = ( $circle_percentage_feature == '' ) ? 'no-chart' : 'with-chart';
    $circle_percentage_feature = do_shortcode($circle_percentage_feature);
    if ('' == $circle_percentage_feature || '0' == $circle_percentage_feature) {
        $circle_percentage_feature = '0';
        $chart_vars['trackColor'] = 'rgba(0,0,0,0)'; // transparent
    }
    $link_type = '';
    if ('' != $link_feature) {
        if (in_array('lightbox', $param_feature)) {
            $link_type = 'lightbox';
        } elseif (in_array('newtab', $param_feature)) {
            $link_type = 'newtab';
        }
    }

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $chart_class, 'layout-' . $layout_feature, 'size-' . $circle_size_feature, $css_feature, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    ?>
    <!-- module feature -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">

        <?php if ($mod_title_feature != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_feature, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <div class="module-feature-image">

            <?php if ('' != $link_feature) : ?>
                <a href="<?php echo esc_url('lightbox' == $link_type ? themify_get_lightbox_iframe_link($link_feature) : $link_feature ); ?>" <?php
                if ('lightbox' == $link_type) : echo 'class="themify_lightbox"';
                endif;
                if ('newtab' == $link_type) : echo 'target="_blank"';
                endif;
                ?>>
                   <?php endif; ?>

                <div class="module-feature-chart-html5"
                    data-progress="0"
                    data-progress-end="<?php echo esc_attr($circle_percentage_feature) ?>"
                    data-bgcolor="<?php echo esc_attr($this->get_rgba_color($icon_bg_feature)); ?>"
                    data-color="<?php echo esc_attr($this->get_rgba_color($circle_color_feature)); ?>"
                    data-trackcolor="<?php echo esc_attr($chart_vars['trackColor']); ?>"
                    data-size="<?php echo esc_attr($chart_vars['size']); ?>"
                    data-linewidth="<?php echo esc_attr($circle_stroke_feature); ?>"
                    data-animate="<?php echo esc_attr($chart_vars['animate']); ?>"
                    
                    data-linecap="<?php echo esc_attr($chart_vars['lineCap']); ?>"
                    data-scalelength="<?php echo esc_attr($chart_vars['scaleLength']); ?>"
                    data-rotate="<?php echo esc_attr($chart_vars['rotate']); ?>">
                    
                    <div class="chart-html5-circle">
                        <div class="chart-html5-mask chart-html5-full"><div class="chart-html5-fill"></div></div>
                        <div class="chart-html5-mask chart-html5-half"><div class="chart-html5-fill"></div></div>
                    </div>

                    <div class="chart-html5-inset <?php if ('icon' == $icon_type_feature && !empty($icon_feature)) echo 'chart-html5-inset-icon' ?>">
                        
                        <?php if ('image' == $icon_type_feature && !empty($image_feature)) : ?>
                            <?php $alt = ( $alt_text = get_post_meta(TB_Feature_Module::get_attachment_id_by_url($image_feature), '_wp_attachment_image_alt', true) ) ? $alt_text : $title_feature; ?>
                            <img src="<?php echo esc_url($image_feature); ?>" alt="<?php echo esc_attr($alt); ?>" />
                        <?php else : ?>
                            <?php if ('' != $icon_bg_feature) : ?><div class="module-feature-background" style="background: <?php echo esc_attr($this->get_rgba_color($icon_bg_feature)); ?>"></div><?php endif; ?>
                            <?php if ('' != $icon_feature) : ?><i class="module-feature-icon fa <?php echo esc_attr(themify_get_fa_icon_classname($icon_feature)); ?>" style="color: <?php echo esc_attr($this->get_rgba_color($icon_color_feature)); ?>"></i><?php endif; ?>
                        <?php endif; ?>

                    </div>
                </div>

                <?php if ('' != $link_feature) : ?>
                </a>
            <?php endif; ?>

        </div>

        <div class="module-feature-content">
            <?php if ('' != $title_feature) : ?>
                <h3 class="module-feature-title">
                    <?php if ('' != $link_feature) : ?>
                        <a href="<?php echo esc_url('lightbox' == $link_type ? themify_get_lightbox_iframe_link($link_feature) : $link_feature ); ?>" <?php
                        if ('lightbox' == $link_type) : echo 'class="themify_lightbox"';
                        endif;
                        if ('newtab' == $link_type) : echo 'target="_blank"';
                        endif;
                        ?>>
                           <?php endif; ?>

                        <?php echo wp_kses_post($title_feature); ?>

                        <?php if ('' != $link_feature) : ?>
                        </a>
                    <?php endif; ?>
                </h3>
            <?php endif; ?>

            <?php echo apply_filters('themify_builder_module_content', $content_feature); ?>
        </div>

        <?php do_action('themify_builder_after_template_content_render'); ?>
    </div>
    <!-- /module feature -->

    <?php
        // DYNAMIC STYLE

        $id = esc_attr($module_ID);
        $circleSize = (int) esc_attr( $chart_vars['size'] );
        $circleBackground = esc_attr( $chart_vars['trackColor'] );
        $circleColor = esc_attr( $this->get_rgba_color( $circle_color_feature ) );
        $insetSize = ( (int) esc_attr( $chart_vars['size'] ) ) - ( ( (int) esc_attr( $circle_stroke_feature ) ) * 2 );
        $insetColor = ! empty( $icon_bg_feature ) ? esc_attr( $this->get_rgba_color( $icon_bg_feature ) ) : '';
        $transitionLength = (int) esc_attr( $chart_vars['animate'] );

        $style = '';
        $style .= '<style>';

        $style .= "
            .{$id} .module-feature-chart-html5 {
                width: {$circleSize}px;
                height: {$circleSize}px;
                -webkit-box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleBackground};
                -moz-box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleBackground};
                -ms-box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleBackground};
                -o-box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleBackground};
                box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleBackground};
            }
            .{$id} .module-feature-chart-html5 .chart-html5-circle .chart-html5-mask,
            .{$id} .module-feature-chart-html5 .chart-html5-circle .chart-html5-fill {
                width: {$circleSize}px;
                height: {$circleSize}px;
                -webkit-transition: all {$transitionLength}ms ease 0s;
                -moz-transition: all {$transitionLength}ms ease 0s;
                -ms-transition: all {$transitionLength}ms ease 0s;
                -o-transition: all {$transitionLength}ms ease 0s;
                transition: all {$transitionLength}ms ease 0s;
            }
            .{$id} .module-feature-chart-html5 .chart-html5-circle .chart-html5-mask {
                border-radius: 0 ".($circleSize/2)."px ".($circleSize/2)."px 0;
                clip: rect(0px, {$circleSize}px, {$circleSize}px, ".($circleSize/2)."px);
            }
            .{$id} .module-feature-chart-html5 .chart-html5-circle .chart-html5-fill {
                border-radius: ".($circleSize/2)."px 0 0 ".($circleSize/2)."px;
            }
            .{$id} .module-feature-chart-html5 .chart-html5-circle .chart-html5-mask .chart-html5-fill {
                clip: rect(0px, ".($circleSize/2)."px, {$circleSize}px, 0px);
                -webkit-box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleColor};
                -moz-box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleColor};
                -ms-box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleColor};
                -o-box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleColor};
                box-shadow: inset 0 0 0 ".(($circleSize-$insetSize)/2)."px {$circleColor};
            }
            .{$id} .module-feature-chart-html5 .chart-html5-inset {
                background-color: {$insetColor};
            }
        ";

        $style .= '</style>';
        echo $style;
    ?>
<?php endif; ?>
<?php TFCache::end_cache(); ?>