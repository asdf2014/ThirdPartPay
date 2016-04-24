<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Video
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache('video', self::$post_id, array('ID' => $module_ID))):
    
    $fields_default = array(
        'mod_title_video' => '',
        'style_video' => 'video-top',
        'url_video' => '',
        'width_video' => '',
        'unit_video' => '',
        'title_video' => '',
        'title_link_video' => false,
        'caption_video' => '',
        'css_video' => '',
        'animation_effect' => ''
    );

    $fields_args = wp_parse_args($mod_settings, $fields_default);
    extract($fields_args, EXTR_SKIP);
    $animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

    $video_maxwidth = ( empty($width_video) ) ? '' : $width_video . $unit_video;
    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $style_video, $css_video, $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    add_filter('oembed_result', 'themify_modify_youtube_embed_url', 10, 3);
    if(!function_exists('themify_modify_youtube_embed_url')){
        function themify_modify_youtube_embed_url($html, $url, $args) {
            $parse_url = parse_url($url);
            if((isset($parse_url['query']) && $parse_url['query']) || (isset($parse_url['fragment']) && $parse_url['fragment'])){
                $parse_url['host'] = str_replace('www.','',$parse_url['host']);
                $query = isset($parse_url['query']) && $parse_url['query']?$parse_url['query']:false;
                $query.= isset($parse_url['fragment']) && $parse_url['fragment']?$parse_url['fragment']:'';
                if($parse_url['host']=='youtu.be' || $parse_url['host']=='youtube.com'){
                    $query = preg_replace('@v=([^"&]*)@','',$query);
                    $query = str_replace('&038;','&',$query);
                    return  $query?preg_replace('@embed/([^"&]*)@', 'embed/$1?'.$query, $html):$html;
                }
                elseif($parse_url['host']=='vimeo.com'){
                     $query = str_replace('&038;','&',$query);
                     return  $query?preg_replace('@video/([^"&]*)@', 'video/$1?'.$query, $html):$html;
                }
            }
            return $html;
        }
    }
    ?>

    <!-- module video -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">

        <?php if ($mod_title_video != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_video, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <div class="video-wrap" <?php echo '' != $video_maxwidth ? 'style="max-width:' . esc_attr($video_maxwidth) . ';"' : ''; ?>>
            <?php echo wp_oembed_get(esc_url($url_video)); ?>
        </div>
        <!-- /video-wrap -->

        <?php if ('' != $title_video || '' != $caption_video): ?>
            <div class="video-content">
                <?php if ('' != $title_video): ?>
                    <h3 class="video-title">
                        <?php if ($title_link_video) : ?>
                            <a href="<?php echo esc_url($title_link_video); ?>"><?php echo wp_kses_post($title_video); ?></a>
                        <?php else: ?>
                            <?php echo wp_kses_post($title_video); ?>
                        <?php endif; ?>
                    </h3>
                <?php endif; ?>

                <?php if ('' != $caption_video): ?>
                    <div class="video-caption">
                        <?php echo apply_filters('themify_builder_module_content', $caption_video); ?>
                    </div>
                    <!-- /video-caption -->
                <?php endif; ?>
            </div>
            <!-- /video-content -->
        <?php endif; ?>

        <?php do_action('themify_builder_after_template_content_render'); ?>
    </div>
    <!-- /module video -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>