<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Portfolio
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
$fields_default = array(
    'mod_title_portfolio' => '',
    'layout_portfolio' => '',
    'type_query_portfolio' => 'category',
    'category_portfolio' => '',
    'query_slug_portfolio' => '',
    'post_per_page_portfolio' => '',
    'offset_portfolio' => '',
    'order_portfolio' => 'desc',
    'orderby_portfolio' => 'date',
    'display_portfolio' => 'content',
    'hide_feat_img_portfolio' => 'no',
    'image_size_portfolio' => '',
    'img_width_portfolio' => '',
    'img_height_portfolio' => '',
    'unlink_feat_img_portfolio' => 'no',
    'hide_post_title_portfolio' => 'no',
    'unlink_post_title_portfolio' => 'no',
    'hide_post_date_portfolio' => 'no',
    'hide_post_meta_portfolio' => 'no',
    'hide_page_nav_portfolio' => 'yes',
    'animation_effect' => '',
    'css_portfolio' => ''
);

if (isset($mod_settings['category_portfolio']))
    $mod_settings['category_portfolio'] = $this->get_param_value($mod_settings['category_portfolio']);

$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args, EXTR_SKIP);
$animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

$container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $mod_name, $module_ID, $css_portfolio
                ), $mod_name, $module_ID, $fields_args)
);

$this->add_post_class($animation_effect);
$this->in_the_loop = true;
global $paged;
$paged = $this->get_paged_query();
?>
<?php if ($orderby_portfolio == 'rand' || TFCache::start_cache('portfolio', self::$post_id, array('page' => $paged, 'ID' => $module_ID))): ?>
    <!-- module portfolio -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
        <?php if ($mod_title_portfolio != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_portfolio, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php
        do_action('themify_builder_before_template_content_render');

        // The Query
        $order = $order_portfolio;
        $orderby = $orderby_portfolio;
        $limit = $post_per_page_portfolio;
        $terms = $category_portfolio;
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
            'post_type' => 'portfolio',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'order' => $order,
            'orderby' => $orderby,
            'suppress_filters' => false,
            'paged' => $paged
        );

        if (count($new_terms) > 0 && !in_array('0', $new_terms) && 'category' == $type_query_portfolio) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'portfolio-category',
                    'field' => $tax_field,
                    'terms' => $new_terms
                )
            );
        }

        if (!empty($query_slug_portfolio) && 'post_slug' == $type_query_portfolio) {
            $args['post__in'] = $this->parse_slug_to_ids($query_slug_portfolio, 'portfolio');
        }

        // add offset posts
        if ($offset_portfolio != '') {
            if (empty($limit))
                $limit = get_option('posts_per_page');

            $args['offset'] = ( ( $paged - 1) * $limit ) + $offset_portfolio;
        }

        $the_query = new WP_Query();
        $args = apply_filters("themify_builder_module_{$mod_name}_query_args", $args, $fields_args);
        $posts = $the_query->query($args);
        ?>
        <div class="builder-posts-wrap portfolio clearfix loops-wrapper <?php echo $layout_portfolio ?>">
            <?php
            // check if theme loop template exists
            $is_theme_template = $this->is_loop_template_exist('loop-portfolio.php', 'includes');

            // use theme template loop
            if ($is_theme_template) {
                // save a copy
                global $themify;
                $themify_save = clone $themify;

                // override $themify object
                $themify->hide_image = $hide_feat_img_portfolio;
                $themify->unlink_image = $unlink_feat_img_portfolio;
                $themify->hide_title = $hide_post_title_portfolio;
                $themify->width = $img_width_portfolio;
                $themify->height = $img_height_portfolio;
                $themify->image_setting = 'ignore=true&';
                if ($this->is_img_php_disabled())
                    $themify->image_setting .= $image_size_portfolio != '' ? 'image_size=' . $image_size_portfolio . '&' : '';
                $themify->unlink_title = $unlink_post_title_portfolio;
                $themify->display_content = $display_portfolio;
                $themify->hide_date = $hide_post_date_portfolio;
                $themify->hide_meta = $hide_post_meta_portfolio;
                $themify->post_layout = $layout_portfolio;

                // hooks action
                do_action_ref_array('themify_builder_override_loop_themify_vars', array($themify, $mod_name));

                $out = '';
                if ($posts) {
                    $out .= themify_get_shortcode_template($posts, 'includes/loop', 'portfolio');
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

                    <?php themify_post_before(); // hook ?>

                    <article id="post-<?php echo esc_attr($post->ID); ?>" <?php post_class("post portfolio-post clearfix"); ?>>

                        <?php themify_post_start(); // hook ?>

                        <?php
                        if ($hide_feat_img_portfolio != 'yes') {
                            $width = $img_width_portfolio;
                            $height = $img_height_portfolio;
                            $param_image = 'w=' . $width . '&h=' . $height . '&ignore=true';
                            if ($this->is_img_php_disabled())
                                $param_image .= $image_size_portfolio != '' ? '&image_size=' . $image_size_portfolio : '';

                            // Check if there is a video url in the custom field
                            if (themify_get('video_url') != '') {
                                global $wp_embed;

                                themify_before_post_image(); // Hook

                                echo $wp_embed->run_shortcode('[embed]' . esc_url(themify_get('video_url')) . '[/embed]');

                                themify_after_post_image(); // Hook
                            } elseif ($post_image = themify_get_image($param_image)) {
                                themify_before_post_image(); // Hook 
                                ?>
                                <figure class="post-image">
                                    <?php if ($unlink_feat_img_portfolio == 'yes'): ?>
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

                            <?php if ($hide_post_date_portfolio == 'no'): ?>
                                <time datetime="<?php the_time('o-m-d') ?>" class="post-date" pubdate><?php the_date(apply_filters('themify_loop_date', '')) ?></time>
                            <?php endif; //post date  ?>

                            <?php if ($hide_post_title_portfolio != 'yes'): ?>
                                <?php themify_before_post_title(); // Hook ?>
                                <?php if ($unlink_post_title_portfolio == 'yes'): ?>
                                    <h1 class="post-title"><?php the_title(); ?></h1>
                                <?php else: ?>
                                    <h1 class="post-title"><a href="<?php echo themify_get_featured_image_link(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
                                <?php endif; //unlink post title ?>
                                <?php themify_after_post_title(); // Hook ?> 
                            <?php endif; //post title  ?>    

                            <?php if ($hide_post_meta_portfolio == 'no'): ?>
                                <p class="post-meta"> 
                                    <span class="post-author"><?php the_author_posts_link() ?></span>
                                    <span class="post-category"><?php the_terms($post->ID, 'portfolio-category'); ?></span>
                                    <?php the_tags(' <span class="post-tag">', ', ', '</span>'); ?>
                                    <?php if (!themify_get('setting-comments_posts') && comments_open()) : ?>
                                        <span class="post-comment"><?php comments_popup_link(__('0 Comments', 'themify'), __('1 Comment', 'themify'), __('% Comments', 'themify')); ?></span>
                                    <?php endif; //post comment  ?>
                                </p>
                            <?php endif; //post meta  ?>    

                            <?php
                            // fix the issue more link doesn't output
                            global $more;
                            $more = 0;
                            ?>

                            <?php if ($display_portfolio == 'excerpt'): ?>

                                <?php the_excerpt(); ?>

                            <?php elseif ($display_portfolio == 'none'): ?>

                            <?php else: ?>

                                <?php the_content(themify_check('setting-default_more_text') ? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>

                            <?php endif; //display content  ?>

                            <?php edit_post_link(__('Edit', 'themify'), '[', ']'); ?>

                        </div>
                        <!-- /.post-content -->
                        <?php themify_post_end(); // hook  ?>

                    </article>
                    <?php themify_post_after(); // hook ?>
                    <?php
                endforeach;
                wp_reset_postdata();
                $post = $temp_post;
            } // end $is_theme_template
            ?>
        </div><!-- .builder-posts-wrap -->
        <?php if ('yes' != $hide_page_nav_portfolio): ?>
             <?php echo $this->get_pagenav('', '', $the_query) ?>
        <?php endif; ?>
        <?php
        do_action('themify_builder_after_template_content_render');
        $this->remove_post_class($animation_effect);
        ?>
    </div>
    <!-- /module portfolio -->
<?php endif; ?>
<?php if ($orderby_portfolio != 'rand'): ?>
    <?php TFCache::end_cache(); ?>
<?php endif; ?>
<?php $this->in_the_loop = false; ?>