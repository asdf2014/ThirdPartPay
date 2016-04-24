<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly
/**
 * Template Text
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
$fields_default = array(
	'mod_title_text' => '',
	'content_text' => '',
	'add_css_text' => '',
	'background_repeat' => '',
	'animation_effect' => ''
);

$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args, EXTR_SKIP);
$animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

$container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
	'module', 'module-' . $mod_name, $module_ID, $add_css_text, $background_repeat, $animation_effect
				), $mod_name, $module_ID, $fields_args)
);
?>
<!-- module text -->
<div id="<?php echo esc_attr($module_ID); ?>" class="<?php echo esc_attr($container_class); ?>">
	<?php if ($mod_title_text != ''): ?>
		<?php echo $mod_settings['before_title'] . wp_kses_post(apply_filters('themify_builder_module_title', $mod_title_text, $fields_args)) . $mod_settings['after_title']; ?>
	<?php endif; ?>

	<?php do_action('themify_builder_before_template_content_render'); ?>

	<?php echo apply_filters('themify_builder_module_content', $content_text); ?>

	<?php do_action('themify_builder_after_template_content_render'); ?>
</div>
<!-- /module text -->