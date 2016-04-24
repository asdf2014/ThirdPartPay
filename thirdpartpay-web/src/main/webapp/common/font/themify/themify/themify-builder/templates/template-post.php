<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Post
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
$fields_default = array(
    'mod_title_post' => '',
    'layout_post' => '',
    'post_type_post' => 'post',
    'type_query_post' => 'category',
    'category_post' => '',
    'query_slug_post' => '',
    'post_per_page_post' => '',
    'offset_post' => '',
    'order_post' => 'desc',
    'orderby_post' => 'date',
    'display_post' => 'content',
    'hide_feat_img_post' => 'no',
    'image_size_post' => '',
    'img_width_post' => '',
    'img_height_post' => '',
    'unlink_feat_img_post' => 'no',
    'hide_post_title_post' => 'no',
    'unlink_post_title_post' => 'no',
    'hide_post_date_post' => 'no',
    'hide_post_meta_post' => 'no',
    'hide_page_nav_post' => 'yes',
    'animation_effect' => '',
    'css_post' => ''
);

if (isset($mod_settings['category_post']))
    $mod_settings['category_post'] = $this->get_param_value($mod_settings['category_post']);

$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args, EXTR_SKIP);
$animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

$container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $mod_name, $module_ID, $css_post
                ), $mod_name, $module_ID, $fields_args)
);

$this->add_post_class($animation_effect);
$this->in_the_loop = true;
global $paged, $wp;
$paged = $this->get_paged_query();
?>
<?php if ($orderby_post == 'rand' || TFCache::start_cache('post', self::$post_id, array('page' => $paged, 'ID' => $module_ID))): ?>
    <!-- module post -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php esc_attr_e($container_class); ?>">
        <?php if ($mod_title_post != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_post, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php
        do_action('themify_builder_before_template_content_render');
        // The Query

        $order = $order_post;
        $orderby = $orderby_post;
        $limit = $post_per_page_post;
        $terms = isset($fields_args["{$type_query_post}_post"]) ? $fields_args["{$type_query_post}_post"] : $category_post;
        // deal with how category fields are saved
        $terms = preg_replace('/\|[multiple|single]*$/', '', $terms);

        $temp_terms = explode(',', $terms);
        $exclude = ( '-' == substr($terms, 0, 1) );
        $new_terms = array();
        $is_string = false;
        foreach ($temp_terms as $t) {
            if (!is_numeric($t))
                $is_string = true;
            if ('' != $t) {
                $new_terms[] = $is_string ? trim($t) : ($exclude ? abs($t) : intval($t));
            }
        }
        $tax_field = ( $is_string ) ? 'slug' : 'id';

        $args = array(
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'order' => $order,
            'orderby' => $orderby,
            'suppress_filters' => false,
            'paged' => $paged,
            'post_type' => $post_type_post
        );

        if (count($new_terms) > 0 && !in_array('0', $new_terms) && 'post_slug' !== $type_query_post) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $type_query_post,
                    'field' => $tax_field,
                    'terms' => $new_terms,
                    'operator' => $exclude ? 'NOT IN' : 'IN',
                )
            );
        }

        if (!empty($query_slug_post) && 'post_slug' == $type_query_post) {
            $args['post__in'] = $this->parse_slug_to_ids($query_slug_post, $post_type_post);
        }

        // add offset posts
        if ($offset_post != '') {
            if (empty($limit))
                $limit = get_option('posts_per_page');

            $args['offset'] = ( ( $paged - 1 ) * $limit ) + $offset_post;
        }

        $the_query = new WP_Query();
        $args = apply_filters("themify_builder_module_{$mod_name}_query_args", $args, $fields_args);
        $posts = $the_query->query($args);
        ?>
        <div class="builder-posts-wrap clearfix loops-wrapper <?php echo $layout_post ?>">
            <?php
            // if the active theme is using Themify framework use theme template loop (includes/loop.php file)
            if (themify_is_themify_theme()) {
                // save a copy
                global $themify;
                $themify_save = clone $themify;

                // override $themify object
                $themify->hide_image = $hide_feat_img_post;
                $themify->unlink_image = $unlink_feat_img_post;
                $themify->hide_title = $hide_post_title_post;
                $themify->width = $img_width_post;
                $themify->height = $img_height_post;
                $themify->image_setting = 'ignore=true&';
                $themify->is_builder_loop = true;
                if ($this->is_img_php_disabled())
                    $themify->image_setting .= $image_size_post != '' ? 'image_size=' . $image_size_post . '&' : '';
                $themify->unlink_title = $unlink_post_title_post;
                $themify->display_content = $display_post;
                $themify->hide_date = $hide_post_date_post;
                $themify->hide_meta = $hide_post_meta_post;
                $themify->post_layout = $layout_post;

                // hooks action
                do_action_ref_array('themify_builder_override_loop_themify_vars', array($themify, $mod_name));

                $out = '';
                if ($posts) {
                    $out .= themify_get_shortcode_template($posts);
                }

                // revert to original $themify state
                $themify = clone $themify_save;
                echo!empty($out) ? $out : '';
            } else {
                // use builder template
                global $post;
                $temp_post = $post;
                foreach ($posts as $post): setup_postdata($post);
                    ?>

                    <?php themify_post_before(); // hook   ?>

                    <article id="post-<?php echo esc_attr($post->ID); ?>" <?php post_class("post clearfix"); ?>>

                        <?php themify_post_start(); // hook   ?>

                        <?php
                        if ($hide_feat_img_post != 'yes') {
                            $width = $img_width_post;
                            $height = $img_height_post;
                            $param_image = 'w=' . $width . '&h=' . $height . '&ignore=true';
                            if ($this->is_img_php_disabled())
                                $param_image .= $image_size_post != '' ? '&image_size=' . $image_size_post : '';

                            //check if there is a video url in the custom field
                            if (themify_get('video_url') != '') {
                                global $wp_embed;

                                themify_before_post_image(); // Hook

                                echo $wp_embed->run_shortcode('[embed]' . esc_url(themify_get('video_url')) . '[/embed]');

                                themify_after_post_image(); // Hook
                            } elseif ($post_image = themify_get_image($param_image)) {

                                themify_before_post_image(); // Hook 
                                ?>

                                <figure class="post-image">
                                    <?php if ($unlink_feat_img_post == 'yes'): ?>
                                        <?php echo wp_kses_post($post_image); ?>
                                    <?php else: ?>
                                        <a href="<?php echo themify_get_featured_image_link(); ?>"><?php echo wp_kses_post($post_image); ?></a>
                                    <?php endif; ?>
                                </figure>

                                <?php
                                themify_after_post_image(); // Hook
                            }
                        }
                        ?>

                        <div class="post-content">

                            <?php if ($hide_post_date_post != 'yes'): ?>
                                <time datetime="<?php the_time('o-m-d') ?>" class="post-date" pubdate><?php the_date(apply_filters('themify_loop_date', '')) ?></time>
                            <?php endif; //post date   ?>

                            <?php if ($hide_post_title_post != 'yes'): ?>
                                <?php themify_before_post_title(); // Hook ?>
                                <?php if ($unlink_post_title_post == 'yes'): ?>
                                    <h1 class="post-title"><?php the_title(); ?></h1>
                                <?php else: ?>
                                    <h1 class="post-title"><a href="<?php echo themify_get_featured_image_link(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
                                <?php endif; //unlink post title    ?>
                                <?php themify_after_post_title(); // Hook ?> 
                            <?php endif; //post title  ?>    

                            <?php if ($hide_post_meta_post != 'yes'): ?>
                                <p class="post-meta"> 
                                    <span class="post-author"><?php the_author_posts_link() ?></span>
                                    <span class="post-category"><?php the_category(', ') ?></span>
                                    <?php the_tags(' <span class="post-tag">', ', ', '</span>'); ?>
                                    <?php if (!themify_get('setting-comments_posts') && comments_open()) : ?>
                                        <span class="post-comment"><?php comments_popup_link(__('0 Comments', 'themify'), __('1 Comment', 'themify'), __('% Comments', 'themify')); ?></span>
                                    <?php endif; //post comment    ?>
                                </p>
                            <?php endif; //post meta   ?>    

                            <?php
                            // fix the issue more link doesn't output
                            global $more;
                            $more = 0;
                            ?>

                            <?php if ($display_post == 'excerpt'): ?>
                                <?php the_excerpt(); ?>
                            <?php elseif ($display_post != 'none'): ?>
                                <?php the_content(themify_check('setting-default_more_text') ? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>
                            <?php endif; //display content  ?>

                            <?php edit_post_link(__('Edit', 'themify'), '[', ']'); ?>

                        </div>
                        <!-- /.post-content -->
                        <?php themify_post_end(); // hook   ?>

                    </article>
                    <?php themify_post_after(); // hook    ?>

                    <?php
                endforeach;
                wp_reset_postdata();
                $post = $temp_post;
            } // end $is_theme_template
            ?>
        </div><!-- .builder-posts-wrap -->
        <?php if ('yes' != $hide_page_nav_post): ?>
           <?php echo $this->get_pagenav('', '', $the_query) ?>
        <?php endif; ?>
        <?php
        do_action('themify_builder_after_template_content_render');
        $this->remove_post_class($animation_effect);
        ?>
    </div>
    <!-- /module post -->
<?php endif; ?>
<?php if ($orderby_post != 'rand'): ?>
    <?php TFCache::end_cache(); ?>
<?php endif; ?>
<?php $this->in_the_loop = false; ?>