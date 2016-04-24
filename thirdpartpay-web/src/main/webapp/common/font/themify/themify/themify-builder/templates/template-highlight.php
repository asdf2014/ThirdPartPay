<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Highlight
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
$fields_default = array(
    'mod_title_highlight' => '',
    'layout_highlight' => '',
    'type_query_highlight' => 'category',
    'category_highlight' => '',
    'query_slug_highlight' => '',
    'post_per_page_highlight' => '',
    'offset_highlight' => '',
    'order_highlight' => '',
    'orderby_highlight' => '',
    'display_highlight' => '',
    'hide_feat_img_highlight' => '',
    'image_size_highlight' => '',
    'img_width_highlight' => '',
    'img_height_highlight' => '',
    'hide_post_title_highlight' => '',
    'hide_post_date_highlight' => '',
    'hide_post_meta_highlight' => '',
    'hide_page_nav_highlight' => '',
    'animation_effect' => '',
    'css_highlight' => ''
);

$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args, EXTR_SKIP);
$animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

$container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $mod_name, $module_ID, $css_highlight
                ), $mod_name, $module_ID, $fields_args)
);

global $paged;
$this->in_the_loop = true;
$paged = $this->get_paged_query();
$this->add_post_class($animation_effect);
?>
<?php if ($order_highlight == 'rand' || TFCache::start_cache('highlight', self::$post_id, array('page' => $paged, 'ID' => $module_ID))): ?>

    <!-- module highlight -->
    <div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
        <?php if ($mod_title_highlight != ''): ?>
            <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_highlight, $fields_args)) . $mod_settings['after_title']; ?>
        <?php endif; ?>

        <?php
        do_action('themify_builder_before_template_content_render');


        // The Query

        $order = $order_highlight;
        $orderby = $orderby_highlight;

        $limit = $post_per_page_highlight;
        $terms = ( $category_highlight != '' ) ? $this->get_param_value($category_highlight) : '';
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
            'post_type' => 'highlight',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'order' => $order,
            'orderby' => $orderby,
            'suppress_filters' => false,
            'paged' => $paged
        );

        if (count($new_terms) > 0 && !in_array('0', $new_terms) && 'category' == $type_query_highlight) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'highlight-category',
                    'field' => $tax_field,
                    'terms' => $new_terms
                )
            );
        }

        if (!empty($query_slug_highlight) && 'post_slug' == $type_query_highlight) {
            $args['post__in'] = $this->parse_slug_to_ids($query_slug_highlight, 'highlight');
        }

        // add offset posts
        if ($offset_highlight != '') {
            if (empty($limit))
                $limit = get_option('posts_per_page');

            $args['offset'] = ( ( $paged - 1 ) * $limit ) + $offset_highlight;
        }

        $the_query = new WP_Query();
        $args = apply_filters("themify_builder_module_{$mod_name}_query_args", $args, $fields_args);
        $posts = $the_query->query($args);
        ?>
        <div class="builder-posts-wrap highlight clearfix loops-wrapper <?php echo $layout_highlight ?>">
            <?php
            // check if theme loop template exists
            $is_theme_template = $this->is_loop_template_exist('loop-highlight.php', 'includes');

            // use theme template loop
            if ($is_theme_template) {
                // save a copy
                global $themify;
                $themify_save = clone $themify;

                // override $themify object
                $themify->hide_image = $hide_feat_img_highlight;
                $themify->hide_title = $hide_post_title_highlight;
                $themify->width = $img_width_highlight;
                $themify->height = $img_height_highlight;
                $themify->image_setting = 'ignore=true&';
                if ($this->is_img_php_disabled())
                    $themify->image_setting .= $image_size_highlight != '' ? 'image_size=' . $image_size_highlight . '&' : '';
                $themify->display_content = $display_highlight;
                $themify->hide_date = $hide_post_date_highlight;
                $themify->hide_meta = $hide_post_meta_highlight;
                $themify->post_layout = $layout_highlight;

                // hooks action
                do_action_ref_array('themify_builder_override_loop_themify_vars', array($themify, $mod_name));

                $out = '';
                if ($posts) {
                    $out .= themify_get_shortcode_template($posts, 'includes/loop', 'highlight');
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

                    <article id="post-<?php echo esc_attr($post->ID); ?>" <?php post_class("post highlight-post clearfix"); ?>>

                        <?php themify_post_start(); // hook    ?>

                        <?php
                        $post_link = 'post' . $post->ID;
                        $linked = themify_check('external_link') || themify_check('lightbox_link'); // check highlight custom field link
                        $linked_url = '';

                        if (themify_get('external_link') != '') {
                            $linked_url = esc_url(themify_get('external_link'));
                        } elseif (themify_get('lightbox_link') != '') {
                            $linked_url = esc_url(themify_get('lightbox_link')) . '" class="themify_lightbox" rel="prettyPhoto[' . esc_attr($post_link) . ']';
                        }

                        // check featured image hide or not
                        if ($hide_feat_img_highlight != 'yes') {
                            $width = $img_width_highlight;
                            $height = $img_height_highlight;
                            $param_image = 'w=' . $width . '&h=' . $height . '&ignore=true';
                            if ($this->is_img_php_disabled()) {
                                $param_image .= $image_size_highlight != '' ? '&image_size=' . $image_size_highlight : '';
                            }

                            if ($post_image = themify_get_image($param_image)) {
                                themify_before_post_image(); // Hook 
                                ?>
                                <figure class="post-image">
                                    <?php if (!$linked): ?>
                                        <?php echo wp_kses_post($post_image); ?>
                                    <?php else: ?>
                                        <a href="<?php echo esc_attr($linked_url); ?>"><?php echo wp_kses_post($post_image); ?></a>
                                    <?php endif; ?>
                                </figure>
                                <?php
                                themify_after_post_image(); // Hook
                            }
                        }
                        ?>

                        <div class="post-content">

                            <?php if ($hide_post_title_highlight != 'yes'): ?>
                                <?php themify_before_post_title(); // Hook   ?>
                                <?php if (!$linked): ?>
                                    <h1 class="post-title"><?php the_title(); ?></h1>
                                <?php else: ?>
                                    <h1 class="post-title"><a href="<?php echo esc_url($linked_url); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
                                <?php endif; //unlink post title  ?>
                                <?php themify_after_post_title(); // Hook   ?> 
                            <?php endif; //post title ?>

                            <?php
                            // fix the issue more link doesn't output
                            global $more;
                            $more = 0;
                            ?>    

                            <?php if ($display_highlight == 'excerpt'): ?>

                                <?php the_excerpt(); ?>

                            <?php elseif ($display_highlight == 'none'): ?>

                            <?php else: ?>

                                <?php the_content(themify_check('setting-default_more_text') ? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>

                            <?php endif; //display content  ?>

                            <?php edit_post_link(__('Edit', 'themify'), '[', ']'); ?>

                        </div>
                        <!-- /.post-content -->
                        <?php themify_post_end(); // hook    ?>

                    </article>
                    <?php themify_post_after(); // hook   ?>

                    <?php
                endforeach;
                wp_reset_postdata();
                $post = $temp_post;
            } // end $is_theme_template
            ?>
        </div><!-- .builder-posts-wrap -->
        <?php if ('yes' != $hide_page_nav_highlight): ?>
             <?php echo $this->get_pagenav('', '', $the_query) ?>
        <?php endif; ?>
        <?php
        do_action('themify_builder_after_template_content_render');
        $this->remove_post_class($animation_effect);
        ?>
    </div>
    <!-- /module highlight -->
<?php endif; ?>
<?php if ($order_highlight != 'rand'): ?>
    <?php TFCache::end_cache(); ?>
<?php endif; ?>
<?php $this->in_the_loop = false; ?>