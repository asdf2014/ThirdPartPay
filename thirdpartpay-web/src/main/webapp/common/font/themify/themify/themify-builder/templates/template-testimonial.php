<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Testimonial
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
$fields_default = array(
    'mod_title_testimonial' => '',
    'layout_testimonial' => '',
    'type_query_testimonial' => 'category',
    'category_testimonial' => '',
    'query_slug_testimonial' => '',
    'post_per_page_testimonial' => '',
    'offset_testimonial' => '',
    'order_testimonial' => 'desc',
    'orderby_testimonial' => 'date',
    'display_testimonial' => 'content',
    'hide_feat_img_testimonial' => '',
    'image_size_testimonial' => '',
    'img_width_testimonial' => '',
    'img_height_testimonial' => '',
    'unlink_feat_img_testimonial' => 'no',
    'hide_post_title_testimonial' => 'no',
    'unlink_post_title_testimonial' => 'no',
    'hide_post_date_testimonial' => 'no',
    'hide_post_meta_testimonial' => 'no',
    'hide_page_nav_testimonial' => 'yes',
    'animation_effect' => '',
    'css_testimonial' => ''
);

if (isset($mod_settings['category_testimonial']))
    $mod_settings['category_testimonial'] = $this->get_param_value($mod_settings['category_testimonial']);

$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args, EXTR_SKIP);
$animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

$container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $mod_name, $module_ID, $css_testimonial, $animation_effect
                ), $mod_name, $module_ID, $fields_args)
);
$this->in_the_loop = true;
global $paged;
$paged = $this->get_paged_query();
?>
<?php if ($orderby_testimonial == 'rand' || TFCache::start_cache('testimonial', self::$post_id, array('page' => $paged, 'ID' => $module_ID))): ?>
    <!-- module testimonial -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
        <?php if ($mod_title_testimonial != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_testimonial, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php
        do_action('themify_builder_before_template_content_render');

        // The Query
        $order = $order_testimonial;
        $orderby = $orderby_testimonial;
        $limit = $post_per_page_testimonial;
        $terms = $category_testimonial;
        $temp_terms = explode(',', $terms);
        $new_terms = array();
        $is_string = false;
        foreach ($temp_terms as $t) {
            if (!is_numeric($t))
                $is_string = true;
            if ('' != $t) {
                array_push($new_terms, trim($t));
            }
        }
        $tax_field = ( $is_string ) ? 'slug' : 'id';

        $args = array(
            'post_type' => 'testimonial',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'order' => $order,
            'orderby' => $orderby,
            'suppress_filters' => false,
            'paged' => $paged
        );

        if (count($new_terms) > 0 && !in_array('0', $new_terms) && 'category' == $type_query_testimonial) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'testimonial-category',
                    'field' => $tax_field,
                    'terms' => $new_terms
                )
            );
        }

        if (!empty($query_slug_testimonial) && 'post_slug' == $type_query_testimonial) {
            $args['post__in'] = $this->parse_slug_to_ids($query_slug_testimonial, 'testimonial');
        }

        // add offset posts
        if ($offset_testimonial != '') {
            if (empty($limit))
                $limit = get_option('posts_per_page');

            $args['offset'] = ( ( $paged - 1 ) * $limit ) + $offset_testimonial;
        }

        $the_query = new WP_Query();
        $args = apply_filters("themify_builder_module_{$mod_name}_query_args", $args, $fields_args);
        $posts = $the_query->query($args);
        ?>
        <div class="builder-posts-wrap testimonial clearfix loops-wrapper <?php echo $layout_testimonial ?>">
            <?php
            // check if theme loop template exists
            $is_theme_template = $this->is_loop_template_exist('loop-testimonial.php', 'includes');

            // use theme template loop
            if ($is_theme_template) {
                // save a copy
                global $themify;
                $themify_save = clone $themify;

                // override $themify object
                $themify->hide_image = $hide_feat_img_testimonial;
                $themify->unlink_image = $unlink_feat_img_testimonial;
                $themify->hide_title = $hide_post_title_testimonial;
                $themify->width = $img_width_testimonial;
                $themify->height = $img_height_testimonial;
                $themify->image_setting = 'ignore=true&';
                if ($this->is_img_php_disabled())
                    $themify->image_setting .= $image_size_testimonial != '' ? 'image_size=' . $image_size_testimonial . '&' : '';
                $themify->unlink_title = $unlink_post_title_testimonial;
                $themify->display_content = $display_testimonial;
                $themify->hide_date = $hide_post_date_testimonial;
                $themify->hide_meta = $hide_post_meta_testimonial;
                $themify->post_layout = $layout_testimonial;

                // hooks action
                do_action_ref_array('themify_builder_override_loop_themify_vars', array($themify, $mod_name));

                $out = '';
                if ($posts) {
                    $out .= themify_get_shortcode_template($posts, 'includes/loop', 'testimonial');
                }

                // revert to original $themify state
                $themify = clone $themify_save;
                echo $out;
            } else {
                // use builder template
                global $post;
                $temp_post = $post;
                foreach ($posts as $post): setup_postdata($post);
                    ?>

                    <?php themify_post_before(); // hook  ?>

                    <article id="post-<?php echo esc_attr($post->ID); ?>" <?php post_class("post testimonial-post clearfix"); ?>>

                        <?php themify_post_start(); // hook  ?>

                        <?php
                        if ($hide_feat_img_testimonial != 'yes') {
                            $width = $img_width_testimonial;
                            $height = $img_height_testimonial;
                            $param_image = 'w=' . $width . '&h=' . $height . '&ignore=true';
                            if ($this->is_img_php_disabled())
                                $param_image .= $image_size_testimonial != '' ? '&image_size=' . $image_size_testimonial : '';

                            if ($post_image = themify_get_image($param_image)) {
                                themify_before_post_image(); // Hook 
                                ?>
                                <figure class="post-image">
                                    <?php echo wp_kses_post($post_image); ?>
                                </figure>
                                <?php
                                themify_after_post_image(); // Hook
                            }
                        }
                        ?>

                        <div class="post-content">

                            <?php if ($hide_post_title_testimonial != 'yes'): ?>
                                <?php themify_before_post_title(); // Hook ?>
                                <h1 class="post-title"><?php the_title(); ?></h1>
                                <?php themify_after_post_title(); // Hook   ?> 
                            <?php endif; //post title ?>    

                            <?php
                            // fix the issue more link doesn't output
                            global $more;
                            $more = 0;
                            ?>

                            <?php if ($display_testimonial == 'excerpt'): ?>

                                <?php the_excerpt(); ?>

                            <?php elseif ($display_testimonial == 'none'): ?>

                            <?php else: ?>

                                <?php the_content(themify_check('setting-default_more_text') ? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>

                            <?php endif; //display content  ?>

                            <?php edit_post_link(__('Edit', 'themify'), '[', ']'); ?>

                            <p class="testimonial-author">
                                <?php
                                echo themify_builder_testimonial_author_name($post, 'yes');
                                ?>
                            </p>

                        </div>
                        <!-- /.post-content -->
                        <?php themify_post_end(); // hook   ?>

                    </article>
                    <?php themify_post_after(); // hook  ?>

                    <?php
                endforeach;
                wp_reset_postdata();
                $post = $temp_post;
            } // endif $is_theme_template
            ?>
        </div><!-- .builder-posts-wrap -->
        <?php if ('yes' != $hide_page_nav_testimonial): ?>
            <?php echo $this->get_pagenav('', '', $the_query) ?>
        <?php endif; ?>     
        <?php
        do_action('themify_builder_after_template_content_render');
        $this->remove_post_class($animation_effect);
        ?>
    </div>
    <!-- /module testimonial -->
<?php endif; ?>
<?php if ($orderby_testimonial != 'rand'): ?>
    <?php TFCache::end_cache(); ?>
<?php endif; ?>
<?php $this->in_the_loop = false; ?>