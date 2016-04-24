<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Part
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

$fields_default = array(
    'mod_title_layout_part' => '',
    'selected_layout_part' => '',
    'add_css_layout_part' => ''
);
$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args, EXTR_SKIP);
if(!self::$layout_part_id){
    self::$layout_part_id = self::$post_id;
}
self::$post_id = $selected_layout_part;
$container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $mod_name, $module_ID, $add_css_layout_part
                ), $mod_name, $module_ID, $fields_args)
);
?>
<!-- module template_part -->
<div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
    <?php if ($mod_title_layout_part != ''): ?>
        <?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_layout_part, $fields_args)) . $mod_settings['after_title']; ?>
    <?php endif; ?>

    <?php do_action('themify_builder_before_template_content_render'); ?>

    <?php echo do_shortcode('[themify_layout_part slug=' . $selected_layout_part . ']'); ?>

    <?php do_action('themify_builder_after_template_content_render'); ?>
</div>
<!-- /module template_part -->
<?php
self::$post_id = self::$layout_part_id ;