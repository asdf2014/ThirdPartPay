<?php
if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly
/**
 * Template Plain Text
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
$fields_default = array(
	'plain_text' => '',
	'add_css_text' => '',
	'animation_effect' => ''
);

$fields_args = wp_parse_args($mod_settings, $fields_default);
extract($fields_args, EXTR_SKIP);
$animation_effect = $this->parse_animation_effect($animation_effect, $fields_args);

$container_class = implode( ' ',
	apply_filters('themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, $add_css_text, $animation_effect
	), $mod_name, $module_ID, $fields_args )
);
?>
<!-- module text -->
<div id="<?php echo esc_attr( $module_ID ); ?>" class="<?php echo esc_attr( $container_class ); ?>">

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<?php echo do_shortcode( $plain_text ); ?>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module text -->