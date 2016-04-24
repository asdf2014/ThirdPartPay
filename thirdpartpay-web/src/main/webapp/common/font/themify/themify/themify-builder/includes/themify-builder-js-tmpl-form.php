<?php
/**
 * The file defines javascript templates for Forms in Builder.
 *
 * Defines javascript template form for Modules, Row, and Column options.
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/includes
 */

/* Modules */
foreach( Themify_Builder_Model::$modules as $module ):
$module_settings = apply_filters('themify_builder_module_settings_fields', $module->get_options(), $module);
$styling_settings = apply_filters('themify_builder_styling_settings_fields', $module->get_styling(), $module);
$animation_settings = array();

if (method_exists($module, 'get_animation')) {
	$animation_settings = apply_filters('themify_builder_animation_settings_fields', $module->get_animation(), $module);
}
?>
<script type="text/html" id="tmpl-builder_form_module_<?php echo esc_attr( $module->slug ); ?>">
	<form id="tfb_module_settings">

		<div id="themify_builder_lightbox_options_tab_items">
			<li><a href="#themify_builder_options_setting"><?php echo ucfirst($module->name); ?></a></li>
			<?php if (count($styling_settings) > 0): ?>
				<li><a href="#themify_builder_options_styling"><?php _e('Styling', 'themify') ?></a></li>
			<?php endif; ?>
			<?php if (count($animation_settings) > 0): ?>
				<li><a href="#themify_builder_options_animation"><?php _e('Animation', 'themify') ?></a></li>
			<?php endif; ?>
		</div>

		<div id="themify_builder_lightbox_actions_items">
			<button id="builder_preview_module" class="builder_button builder_preview_lightbox"><?php _e('Preview', 'themify') ?></button>
			<button id="builder_submit_module_settings" class="builder_button"><?php _e('Save', 'themify') ?></button>
		</div>

		<div id="themify_builder_options_setting" class="themify_builder_options_tab_wrapper">
			<div class="themify_builder_options_tab_content">
				<?php
				if (count($module_settings) > 0) {
					themify_builder_module_settings_field($module_settings, $module->slug);
				}
				?>
			</div>
		</div>

		<?php if (count($styling_settings) > 0) : ?>
			<div id="themify_builder_options_styling" class="themify_builder_options_tab_wrapper">
				<div class="themify_builder_options_tab_content">

					<?php themify_render_styling_settings($styling_settings); ?>

					<p>
						<a href="#" class="reset-styling" data-reset="module">
							<i class="ti ti-close"></i>
							<?php _e('Reset Styling', 'themify') ?>
						</a>
					</p>
				</div>
				<!-- /themify_builder_options_tab_content -->
			</div>
		<?php endif; ?>

		<?php if (count($animation_settings) > 0) : ?>
			<div id="themify_builder_options_animation" class="themify_builder_options_tab_wrapper">
				<div class="themify_builder_options_tab_content">
					<?php themify_render_styling_settings($animation_settings); ?>
				</div>
				<!-- /themify_builder_options_tab_content -->
			</div>
		<?php endif; ?>

	</form>
</script>
<?php
endforeach;

// Image size
$image_size = themify_get_image_sizes_list( true );
unset( $image_size[ key( $image_size ) ] );

// Rows
$row_fields_options = apply_filters('themify_builder_row_fields_options', array(
	// Row Width
	array(
		'id' => 'row_width',
		'label' => __('Row Width', 'themify'),
		'type' => 'radio',
		'description' => '',
		'meta' => array(
			array('value' => '', 'name' => __('Default', 'themify'), 'selected' => true),
			array('value' => 'fullwidth', 'name' => __('Fullwidth', 'themify'))
		),
		'wrap_with_class' => '',
	),
	// Row Height
	array(
		'id' => 'row_height',
		'label' => __('Row Height', 'themify'),
		'type' => 'radio',
		'description' => '',
		'meta' => array(
			array('value' => '', 'name' => __('Default', 'themify'), 'selected' => true),
			array('value' => 'fullheight', 'name' => __('Fullheight (100% viewport height)', 'themify'))
		),
		'wrap_with_class' => '',
	),
	// Additional CSS
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr/>')
	),
	array(
		'id' => 'custom_css_row',
		'type' => 'text',
		'label' => __('Additional CSS Class', 'themify'),
		'class' => 'large exclude-from-reset-field',
		'description' => sprintf('<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify'))
	),
	array(
		'id' => 'row_anchor',
		'type' => 'text',
		'label' => __('Row Anchor', 'themify'),
		'class' => 'large exclude-from-reset-field',
		'description' => sprintf('<br/><small>%s</small>', __('Example: enter ‘about’ as row anchor and add ‘#about’ link in navigation menu. When link is clicked, it will scroll to this row.', 'themify'))
	),
));

$row_fields_styling = apply_filters('themify_builder_row_fields_styling', array(
	// Background
	array(
		'id' => 'separator_image_background',
		'title' => '',
		'description' => '',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Background', 'themify') . '</h4>'),
	),
	array(
		'id' => 'background_type',
		'label' => __('Background Type', 'themify'),
		'type' => 'radio',
		'meta' => array(
			array('value' => 'image', 'name' => __('Image', 'themify')),
			array('value' => 'video', 'name' => __('Video', 'themify')),
			array('value' => 'slider', 'name' => __('Slider', 'themify')),
		),
		'option_js' => true,
	),
	// Background Slider
	array(
		'id' => 'background_slider',
		'type' => 'textarea',
		'label' => __('Background Slider', 'themify'),
		'class' => 'tf-hide tf-shortcode-input',
		'wrap_with_class' => 'tf-group-element tf-group-element-slider',
		'description' => sprintf('<a href="#" class="builder_button tf-gallery-btn">%s</a>', __('Insert Gallery', 'themify'))
	),
	 // Background Slider Image Size
	array(
		'id' => 'background_slider_size',
		'label' => __('Image Size', 'themify'),
		'type' => 'select',
		'default' => '',
		'meta' => $image_size,
		'wrap_with_class' => 'tf-group-element tf-group-element-slider',
	),
	// Background Slider Mode
	array(
		'id' => 'background_slider_mode',
		'label' => __('Background Slider Mode', 'themify'),
		'type' => 'select',
		'default' => '',
		'meta' => array(
			array('value' => 'best-fit', 'name' => __('Best Fit', 'themify')),
			array('value' => 'fullcover', 'name' => __('Fullcover', 'themify')),
		),
		'wrap_with_class' => 'tf-group-element tf-group-element-slider',
	),
	// Video Background
	array(
		'id' => 'background_video',
		'type' => 'video',
		'label' => __('Background Video', 'themify'),
		'description' => __('Video format: mp4. Note: video background does not play on mobile, background image will be used as fallback.', 'themify'),
		'class' => 'xlarge',
		'wrap_with_class' => 'tf-group-element tf-group-element-video'
	),
	array(
		'id' => 'background_video_options',
		'type' => 'checkbox',
		'label' => '',
		'default' => array(),
		'options' => array(
			array('name' => 'unloop', 'value' => __('Disable looping', 'themify')),
			array('name' => 'mute', 'value' => __('Disable audio', 'themify')),
		),
		'wrap_with_class' => 'tf-group-element tf-group-element-video',
	),
	// Background Image
	array(
		'id' => 'background_image',
		'type' => 'image',
		'label' => __('Background Image', 'themify'),
		'class' => 'xlarge',
		'wrap_with_class' => 'tf-group-element tf-group-element-image tf-group-element-video',
	),
	// Background repeat
	array(
		'id' => 'background_repeat',
		'label' => __('Background Mode', 'themify'),
		'type' => 'select',
		'default' => '',
		'meta' => array(
			array('value' => 'repeat', 'name' => __('Repeat All', 'themify')),
			array('value' => 'repeat-x', 'name' => __('Repeat Horizontally', 'themify')),
			array('value' => 'repeat-y', 'name' => __('Repeat Vertically', 'themify')),
			array('value' => 'repeat-none', 'name' => __('Do not repeat', 'themify')),
			array('value' => 'fullcover', 'name' => __('Fullcover', 'themify')),
			array('value' => 'best-fit-image', 'name' => __('Best Fit', 'themify')),
			array('value' => 'builder-parallax-scrolling', 'name' => __('Parallax Scrolling', 'themify'))
		),
		'wrap_with_class' => 'tf-group-element tf-group-element-image',
	),
	// Background position
	array(
		'id' => 'background_position',
		'label' => __('Background Position', 'themify'),
		'type' => 'select',
		'default' => '',
		'meta' => array(
			array('value' => 'left-top', 'name' => __('Left Top', 'themify')),
			array('value' => 'left-center', 'name' => __('Left Center', 'themify')),
			array('value' => 'left-bottom', 'name' => __('Left Bottom', 'themify')),
			array('value' => 'right-top', 'name' => __('Right top', 'themify')),
			array('value' => 'right-center', 'name' => __('Right Center', 'themify')),
			array('value' => 'right-bottom', 'name' => __('Right Bottom', 'themify')),
			array('value' => 'center-top', 'name' => __('Center Top', 'themify')),
			array('value' => 'center-center', 'name' => __('Center Center', 'themify')),
			array('value' => 'center-bottom', 'name' => __('Center Bottom', 'themify'))
		),
		'wrap_with_class' => 'tf-group-element tf-group-element-image',
	),
	// Background Color
	array(
		'id' => 'background_color',
		'type' => 'color',
		'label' => __('Background Color', 'themify'),
		'class' => 'small'
	),
	// Overlay Color
	array(
		'id' => 'separator_cover',
		'title' => '',
		'description' => '',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Row Overlay', 'themify') . '</h4>'),
	),
	array(
		'id' => 'cover_color',
		'type' => 'color',
		'label' => __('Overlay Color', 'themify'),
		'class' => 'small'
	),
	array(
		'id' => 'cover_color_hover',
		'type' => 'color',
		'label' => __('Overlay Hover Color', 'themify'),
		'class' => 'small'
	),
	// Font
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_font',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Font', 'themify') . '</h4>'),
	),
	array(
		'id' => 'font_family',
		'type' => 'font_select',
		'label' => __('Font Family', 'themify'),
		'class' => 'font-family-select'
	),
	array(
		'id' => 'font_color',
		'type' => 'color',
		'label' => __('Font Color', 'themify'),
		'class' => 'small'
	),
	array(
		'id' => 'multi_font_size',
		'type' => 'multi',
		'label' => __('Font Size', 'themify'),
		'fields' => array(
			array(
				'id' => 'font_size',
				'type' => 'text',
				'class' => 'xsmall'
			),
			array(
				'id' => 'font_size_unit',
				'type' => 'select',
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => 'em', 'name' => __('em', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'multi_line_height',
		'type' => 'multi',
		'label' => __('Line Height', 'themify'),
		'fields' => array(
			array(
				'id' => 'line_height',
				'type' => 'text',
				'class' => 'xsmall'
			),
			array(
				'id' => 'line_height_unit',
				'type' => 'select',
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => 'em', 'name' => __('em', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'text_align',
		'label' => __('Text Align', 'themify'),
		'type' => 'radio',
		'meta' => array(
			array('value' => '', 'name' => __('Default', 'themify'), 'selected' => true),
			array('value' => 'left', 'name' => __('Left', 'themify')),
			array('value' => 'center', 'name' => __('Center', 'themify')),
			array('value' => 'right', 'name' => __('Right', 'themify')),
			array('value' => 'justify', 'name' => __('Justify', 'themify'))
		)
	),
	// Link
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_link',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Link', 'themify') . '</h4>'),
	),
	array(
		'id' => 'link_color',
		'type' => 'color',
		'label' => __('Color', 'themify'),
		'class' => 'small'
	),
	array(
		'id' => 'text_decoration',
		'type' => 'select',
		'label' => __('Text Decoration', 'themify'),
		'meta' => array(
			array('value' => '', 'name' => '', 'selected' => true),
			array('value' => 'underline', 'name' => __('Underline', 'themify')),
			array('value' => 'overline', 'name' => __('Overline', 'themify')),
			array('value' => 'line-through', 'name' => __('Line through', 'themify')),
			array('value' => 'none', 'name' => __('None', 'themify'))
		)
	),
	// Padding
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_padding',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Padding', 'themify') . '</h4>'),
	),
	array(
		'id' => 'multi_padding_top',
		'type' => 'multi',
		'label' => __('Padding', 'themify'),
		'fields' => array(
			array(
				'id' => 'padding_top',
				'type' => 'text',
				'class' => 'style_padding style_field xsmall'
			),
			array(
				'id' => 'padding_top_unit',
				'type' => 'select',
				'description' => __('top', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_padding_right',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'padding_right',
				'type' => 'text',
				'class' => 'style_padding style_field xsmall'
			),
			array(
				'id' => 'padding_right_unit',
				'type' => 'select',
				'description' => __('right', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_padding_bottom',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'padding_bottom',
				'type' => 'text',
				'class' => 'style_padding style_field xsmall'
			),
			array(
				'id' => 'padding_bottom_unit',
				'type' => 'select',
				'description' => __('bottom', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_padding_left',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'padding_left',
				'type' => 'text',
				'class' => 'style_padding style_field xsmall'
			),
			array(
				'id' => 'padding_left_unit',
				'type' => 'select',
				'description' => __('left', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	// "Apply all" // apply all padding
	array(
		'id' => 'checkbox_padding_apply_all',
		'class' => 'style_apply_all style_apply_all_padding',
		'type' => 'checkbox',
		'label' => false,
		'options' => array(
			array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'themify' ) )
		)
	),
	// Margin
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_margin',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Margin', 'themify') . '</h4>'),
	),
	array(
		'id' => 'multi_margin_top',
		'type' => 'multi',
		'label' => __('Margin', 'themify'),
		'fields' => array(
			array(
				'id' => 'margin_top',
				'type' => 'text',
				'class' => 'style_margin style_field xsmall'
			),
			array(
				'id' => 'margin_top_unit',
				'type' => 'select',
				'description' => __('top', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_margin_right',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'margin_right',
				'type' => 'text',
				'class' => 'style_margin style_field xsmall'
			),
			array(
				'id' => 'margin_right_unit',
				'type' => 'select',
				'description' => __('right', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_margin_bottom',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'margin_bottom',
				'type' => 'text',
				'class' => 'style_margin style_field xsmall'
			),
			array(
				'id' => 'margin_bottom_unit',
				'type' => 'select',
				'description' => __('bottom', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_margin_left',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'margin_left',
				'type' => 'text',
				'class' => 'style_margin style_field xsmall'
			),
			array(
				'id' => 'margin_left_unit',
				'type' => 'select',
				'description' => __('left', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	// "Apply all" // apply all margin
	array(
		'id' => 'checkbox_margin_apply_all',
		'class' => 'style_apply_all style_apply_all_margin',
		'type' => 'checkbox',
		'label' => false,
		'options' => array(
			array( 'name' => 'margin', 'value' => __( 'Apply to all margin', 'themify' ) )
		)
	),
	// Border
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_border',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Border', 'themify') . '</h4>'),
	),
	array(
		'id' => 'multi_border_top',
		'type' => 'multi',
		'label' => __('Border', 'themify'),
		'fields' => array(
			array(
				'id' => 'border_top_color',
				'type' => 'color',
				'class' => 'small'
			),
			array(
				'id' => 'border_top_width',
				'type' => 'text',
				'description' => 'px',
				'class' => 'style_border style_field xsmall'
			),
			array(
				'id' => 'border_top_style',
				'type' => 'select',
				'description' => __('top', 'themify'),
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'solid', 'name' => __('Solid', 'themify')),
					array('value' => 'dashed', 'name' => __('Dashed', 'themify')),
					array('value' => 'dotted', 'name' => __('Dotted', 'themify')),
					array('value' => 'double', 'name' => __('Double', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'multi_border_right',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'border_right_color',
				'type' => 'color',
				'class' => 'small'
			),
			array(
				'id' => 'border_right_width',
				'type' => 'text',
				'description' => 'px',
				'class' => 'style_border style_field xsmall'
			),
			array(
				'id' => 'border_right_style',
				'type' => 'select',
				'description' => __('right', 'themify'),
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'solid', 'name' => __('Solid', 'themify')),
					array('value' => 'dashed', 'name' => __('Dashed', 'themify')),
					array('value' => 'dotted', 'name' => __('Dotted', 'themify')),
					array('value' => 'double', 'name' => __('Double', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'multi_border_bottom',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'border_bottom_color',
				'type' => 'color',
				'class' => 'small'
			),
			array(
				'id' => 'border_bottom_width',
				'type' => 'text',
				'description' => 'px',
				'class' => 'style_border style_field xsmall'
			),
			array(
				'id' => 'border_bottom_style',
				'type' => 'select',
				'description' => __('bottom', 'themify'),
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'solid', 'name' => __('Solid', 'themify')),
					array('value' => 'dashed', 'name' => __('Dashed', 'themify')),
					array('value' => 'dotted', 'name' => __('Dotted', 'themify')),
					array('value' => 'double', 'name' => __('Double', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'multi_border_left',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'border_left_color',
				'type' => 'color',
				'class' => 'small'
			),
			array(
				'id' => 'border_left_width',
				'type' => 'text',
				'description' => 'px',
				'class' => 'style_border style_field xsmall'
			),
			array(
				'id' => 'border_left_style',
				'type' => 'select',
				'description' => __('left', 'themify'),
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'solid', 'name' => __('Solid', 'themify')),
					array('value' => 'dashed', 'name' => __('Dashed', 'themify')),
					array('value' => 'dotted', 'name' => __('Dotted', 'themify')),
					array('value' => 'double', 'name' => __('Double', 'themify'))
				)
			)
		)
	),
	// "Apply all" // apply all border
	array(
		'id' => 'checkbox_border_apply_all',
		'class' => 'style_apply_all style_apply_all_border',
		'type' => 'checkbox',
		'label' => false,
		'options' => array(
			array( 'name' => 'border', 'value' => __( 'Apply to all border', 'themify' ) )
		)
	),
));

$row_fields_animation = apply_filters('themify_builder_row_fields_animation', array(
	// Animation
	array(
		'id' => 'multi_Animation Effect',
		'type' => 'multi',
		'label' => __('Effect', 'themify'),
		'fields' => array(
			array(
				'id' => 'animation_effect',
				'type' => 'animation_select',
				'label' => __('Effect', 'themify')
			),
			array(
				'id' => 'animation_effect_delay',
				'type' => 'text',
				'label' => __('Delay', 'themify'),
				'class' => 'xsmall',
				'description' => __('Delay (s)', 'themify'),
			),
			array(
				'id' => 'animation_effect_repeat',
				'type' => 'text',
				'label' => __('Repeat', 'themify'),
				'class' => 'xsmall',
				'description' => __('Repeat (x)', 'themify'),
			),
		)
	)
)); ?>


<script type="text/html" id="tmpl-builder_form_row">
	<form id="tfb_row_settings">
		<div id="themify_builder_lightbox_options_tab_items">
			<li><a href="#themify_builder_row_fields_options"><?php _e('Row Options', 'themify') ?></a></li>
			<li><a href="#themify_builder_row_fields_styling"><?php _e('Styling', 'themify') ?></a></li>
			<li><a href="#themify_builder_row_fields_animation"><?php _e('Animation', 'themify') ?></a></li>
		</div>

		<div id="themify_builder_lightbox_actions_items">
			<button id="builder_submit_row_settings" class="builder_button"><?php _e('Save', 'themify') ?></button>
		</div>

		<div id="themify_builder_row_fields_options" class="themify_builder_options_tab_wrapper">
			<div class="themify_builder_options_tab_content">
				<?php themify_render_row_fields($row_fields_options); ?>
			</div>
		</div>

		<div id="themify_builder_row_fields_styling" class="themify_builder_options_tab_wrapper">
			<div class="themify_builder_options_tab_content">
				<?php themify_render_row_fields($row_fields_styling); ?>

				<p>
					<a href="#" class="reset-styling" data-reset="row">
						<i class="ti ti-close"></i>
						<?php _e('Reset Styling', 'themify') ?>
					</a>
				</p>
			</div>
		</div>

		<div id="themify_builder_row_fields_animation" class="themify_builder_options_tab_wrapper">
			<div class="themify_builder_options_tab_content">
				<?php themify_render_row_fields($row_fields_animation); ?>
			</div>
		</div>
	</form>
</script>

<?php
$column_settings = apply_filters('themify_builder_column_fields', array(
	// Background
	array(
		'id' => 'separator_image_background',
		'title' => '',
		'description' => '',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Background', 'themify') . '</h4>'),
	),
	array(
		'id' => 'background_type',
		'label' => __('Background Type', 'themify'),
		'type' => 'radio',
		'meta' => array(
			array('value' => 'image', 'name' => __('Image', 'themify')),
			array('value' => 'video', 'name' => __('Video', 'themify')),
			array('value' => 'slider', 'name' => __('Slider', 'themify')),
		),
		'option_js' => true,
	),
	// Background Slider
	array(
		'id' => 'background_slider',
		'type' => 'textarea',
		'label' => __('Background Slider', 'themify'),
		'class' => 'tf-hide tf-shortcode-input',
		'wrap_with_class' => 'tf-group-element tf-group-element-slider',
		'description' => sprintf('<a href="#" class="builder_button tf-gallery-btn">%s</a>', __('Insert Gallery', 'themify'))
	),
	// Background Slider Image Size
	array(
		'id' => 'background_slider_size',
		'label' => __('Image Size', 'themify'),
		'type' => 'select',
		'default' => '',
		'meta' => $image_size,
		'wrap_with_class' => 'tf-group-element tf-group-element-slider',
	),
	// Background Slider Mode
	array(
		'id' => 'background_slider_mode',
		'label' => __('Background Slider Mode', 'themify'),
		'type' => 'select',
		'default' => '',
		'meta' => array(
			array('value' => 'best-fit', 'name' => __('Best Fit', 'themify')),
			array('value' => 'fullcover', 'name' => __('Fullcover', 'themify')),
		),
		'wrap_with_class' => 'tf-group-element tf-group-element-slider',
	),
	// Video Background
	array(
		'id' => 'background_video',
		'type' => 'video',
		'label' => __('Background Video', 'themify'),
		'description' => __('Video format: mp4. Note: video background does not play on mobile, background image will be used as fallback.', 'themify'),
		'class' => 'xlarge',
		'wrap_with_class' => 'tf-group-element tf-group-element-video'
	),
	array(
		'id' => 'background_video_options',
		'type' => 'checkbox',
		'label' => '',
		'default' => array(),
		'options' => array(
			array('name' => 'unloop', 'value' => __('Disable looping', 'themify')),
			array('name' => 'mute', 'value' => __('Disable audio', 'themify')),
		),
		'wrap_with_class' => 'tf-group-element tf-group-element-video',
	),
	// Background Image
	array(
		'id' => 'background_image',
		'type' => 'image',
		'label' => __('Background Image', 'themify'),
		'class' => 'xlarge',
		'wrap_with_class' => 'tf-group-element tf-group-element-image tf-group-element-video',
	),
	// Background repeat
	array(
		'id' => 'background_repeat',
		'label' => __('Background Mode', 'themify'),
		'type' => 'select',
		'default' => '',
		'meta' => array(
			array('value' => 'repeat', 'name' => __('Repeat All', 'themify')),
			array('value' => 'repeat-x', 'name' => __('Repeat Horizontally', 'themify')),
			array('value' => 'repeat-y', 'name' => __('Repeat Vertically', 'themify')),
			array('value' => 'repeat-none', 'name' => __('Do not repeat', 'themify')),
			array('value' => 'fullcover', 'name' => __('Fullcover', 'themify')),
			array('value' => 'builder-parallax-scrolling', 'name' => __('Parallax Scrolling', 'themify'))
		),
		'wrap_with_class' => 'tf-group-element tf-group-element-image',
	),
	// Background position
	array(
		'id' => 'background_position',
		'label' => __('Background Position', 'themify'),
		'type' => 'select',
		'default' => '',
		'meta' => array(
			array('value' => 'left-top', 'name' => __('Left Top', 'themify')),
			array('value' => 'left-center', 'name' => __('Left Center', 'themify')),
			array('value' => 'left-bottom', 'name' => __('Left Bottom', 'themify')),
			array('value' => 'right-top', 'name' => __('Right top', 'themify')),
			array('value' => 'right-center', 'name' => __('Right Center', 'themify')),
			array('value' => 'right-bottom', 'name' => __('Right Bottom', 'themify')),
			array('value' => 'center-top', 'name' => __('Center Top', 'themify')),
			array('value' => 'center-center', 'name' => __('Center Center', 'themify')),
			array('value' => 'center-bottom', 'name' => __('Center Bottom', 'themify'))
		),
		'wrap_with_class' => 'tf-group-element tf-group-element-image',
	),
	// Background Color
	array(
		'id' => 'background_color',
		'type' => 'color',
		'label' => __('Background Color', 'themify'),
		'class' => 'small'
	),
	// Overlay Color
	array(
		'id' => 'separator_cover',
		'title' => '',
		'description' => '',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Column Overlay', 'themify') . '</h4>'),
	),
	array(
		'id' => 'cover_color',
		'type' => 'color',
		'label' => __('Overlay Color', 'themify'),
		'class' => 'small'
	),
	array(
		'id' => 'cover_color_hover',
		'type' => 'color',
		'label' => __('Overlay Hover Color', 'themify'),
		'class' => 'small'
	),
	// Font
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_font',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Font', 'themify') . '</h4>'),
	),
	array(
		'id' => 'font_family',
		'type' => 'font_select',
		'label' => __('Font Family', 'themify'),
		'class' => 'font-family-select'
	),
	array(
		'id' => 'font_color',
		'type' => 'color',
		'label' => __('Font Color', 'themify'),
		'class' => 'small'
	),
	array(
		'id' => 'multi_font_size',
		'type' => 'multi',
		'label' => __('Font Size', 'themify'),
		'fields' => array(
			array(
				'id' => 'font_size',
				'type' => 'text',
				'class' => 'xsmall'
			),
			array(
				'id' => 'font_size_unit',
				'type' => 'select',
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => 'em', 'name' => __('em', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'multi_line_height',
		'type' => 'multi',
		'label' => __('Line Height', 'themify'),
		'fields' => array(
			array(
				'id' => 'line_height',
				'type' => 'text',
				'class' => 'xsmall'
			),
			array(
				'id' => 'line_height_unit',
				'type' => 'select',
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => 'em', 'name' => __('em', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'text_align',
		'label' => __('Text Align', 'themify'),
		'type' => 'radio',
		'meta' => array(
			array('value' => '', 'name' => __('Default', 'themify'), 'selected' => true),
			array('value' => 'left', 'name' => __('Left', 'themify')),
			array('value' => 'center', 'name' => __('Center', 'themify')),
			array('value' => 'right', 'name' => __('Right', 'themify')),
			array('value' => 'justify', 'name' => __('Justify', 'themify'))
		)
	),
	// Link
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_link',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Link', 'themify') . '</h4>'),
	),
	array(
		'id' => 'link_color',
		'type' => 'color',
		'label' => __('Color', 'themify'),
		'class' => 'small'
	),
	array(
		'id' => 'text_decoration',
		'type' => 'select',
		'label' => __('Text Decoration', 'themify'),
		'meta' => array(
			array('value' => '', 'name' => '', 'selected' => true),
			array('value' => 'underline', 'name' => __('Underline', 'themify')),
			array('value' => 'overline', 'name' => __('Overline', 'themify')),
			array('value' => 'line-through', 'name' => __('Line through', 'themify')),
			array('value' => 'none', 'name' => __('None', 'themify'))
		)
	),
	// Padding
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_padding',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Padding', 'themify') . '</h4>'),
	),
	array(
		'id' => 'multi_padding_top',
		'type' => 'multi',
		'label' => __('Padding', 'themify'),
		'fields' => array(
			array(
				'id' => 'padding_top',
				'type' => 'text',
				'class' => 'style_padding style_field xsmall'
			),
			array(
				'id' => 'padding_top_unit',
				'type' => 'select',
				'description' => __('top', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_padding_right',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'padding_right',
				'type' => 'text',
				'class' => 'style_padding style_field xsmall'
			),
			array(
				'id' => 'padding_right_unit',
				'type' => 'select',
				'description' => __('right', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_padding_bottom',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'padding_bottom',
				'type' => 'text',
				'class' => 'style_padding style_field xsmall'
			),
			array(
				'id' => 'padding_bottom_unit',
				'type' => 'select',
				'description' => __('bottom', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	array(
		'id' => 'multi_padding_left',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'padding_left',
				'type' => 'text',
				'class' => 'style_padding style_field xsmall'
			),
			array(
				'id' => 'padding_left_unit',
				'type' => 'select',
				'description' => __('left', 'themify'),
				'meta' => array(
					array('value' => 'px', 'name' => __('px', 'themify')),
					array('value' => '%', 'name' => __('%', 'themify'))
				)
			),
		)
	),
	// "Apply all" // apply all padding
	array(
		'id' => 'checkbox_padding_apply_all',
		'class' => 'style_apply_all style_apply_all_padding',
		'type' => 'checkbox',
		'label' => false,
		'options' => array(
			array( 'name' => 'padding', 'value' => __( 'Apply to all padding', 'themify' ) )
		)
	),
	// Border
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr />')
	),
	array(
		'id' => 'separator_border',
		'type' => 'separator',
		'meta' => array('html' => '<h4>' . __('Border', 'themify') . '</h4>'),
	),
	array(
		'id' => 'multi_border_top',
		'type' => 'multi',
		'label' => __('Border', 'themify'),
		'fields' => array(
			array(
				'id' => 'border_top_color',
				'type' => 'color',
				'class' => 'small'
			),
			array(
				'id' => 'border_top_width',
				'type' => 'text',
				'description' => 'px',
				'class' => 'style_border style_field xsmall'
			),
			array(
				'id' => 'border_top_style',
				'type' => 'select',
				'description' => __('top', 'themify'),
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'solid', 'name' => __('Solid', 'themify')),
					array('value' => 'dashed', 'name' => __('Dashed', 'themify')),
					array('value' => 'dotted', 'name' => __('Dotted', 'themify')),
					array('value' => 'double', 'name' => __('Double', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'multi_border_right',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'border_right_color',
				'type' => 'color',
				'class' => 'small'
			),
			array(
				'id' => 'border_right_width',
				'type' => 'text',
				'description' => 'px',
				'class' => 'style_border style_field xsmall'
			),
			array(
				'id' => 'border_right_style',
				'type' => 'select',
				'description' => __('right', 'themify'),
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'solid', 'name' => __('Solid', 'themify')),
					array('value' => 'dashed', 'name' => __('Dashed', 'themify')),
					array('value' => 'dotted', 'name' => __('Dotted', 'themify')),
					array('value' => 'double', 'name' => __('Double', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'multi_border_bottom',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'border_bottom_color',
				'type' => 'color',
				'class' => 'small'
			),
			array(
				'id' => 'border_bottom_width',
				'type' => 'text',
				'description' => 'px',
				'class' => 'style_border style_field xsmall'
			),
			array(
				'id' => 'border_bottom_style',
				'type' => 'select',
				'description' => __('bottom', 'themify'),
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'solid', 'name' => __('Solid', 'themify')),
					array('value' => 'dashed', 'name' => __('Dashed', 'themify')),
					array('value' => 'dotted', 'name' => __('Dotted', 'themify')),
					array('value' => 'double', 'name' => __('Double', 'themify'))
				)
			)
		)
	),
	array(
		'id' => 'multi_border_left',
		'type' => 'multi',
		'label' => '',
		'fields' => array(
			array(
				'id' => 'border_left_color',
				'type' => 'color',
				'class' => 'small'
			),
			array(
				'id' => 'border_left_width',
				'type' => 'text',
				'description' => 'px',
				'class' => 'style_border style_field xsmall'
			),
			array(
				'id' => 'border_left_style',
				'type' => 'select',
				'description' => __('left', 'themify'),
				'meta' => array(
					array('value' => '', 'name' => ''),
					array('value' => 'solid', 'name' => __('Solid', 'themify')),
					array('value' => 'dashed', 'name' => __('Dashed', 'themify')),
					array('value' => 'dotted', 'name' => __('Dotted', 'themify')),
					array('value' => 'double', 'name' => __('Double', 'themify'))
				)
			)
		)
	),
	// "Apply all" // apply all border
	array(
		'id' => 'checkbox_border_apply_all',
		'class' => 'style_apply_all style_apply_all_border',
		'type' => 'checkbox',
		'label' => false,
		'options' => array(
			array( 'name' => 'border', 'value' => __( 'Apply to all border', 'themify' ) )
		)
	),
	array(
		'type' => 'separator',
		'meta' => array('html' => '<hr/>')
	),
	array(
		'id' => 'custom_css_column',
		'type' => 'text',
		'label' => __('Additional CSS Class', 'themify'),
		'class' => 'large exclude-from-reset-field',
		'description' => sprintf('<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify'))
	),
)); ?>
<script type="text/html" id="tmpl-builder_form_column">
	<form id="tfb_column_settings">

        <div id="themify_builder_lightbox_options_tab_items">
            <li class="title"><?php _e('Column Styling', 'themify'); ?></li>
        </div>

        <div id="themify_builder_lightbox_actions_items">
            <button id="builder_submit_column_settings" class="builder_button"><?php _e('Save', 'themify') ?></button>
        </div>

        <div class="themify_builder_options_tab_wrapper">
            <div class="themify_builder_options_tab_content">
                <?php
                foreach ($column_settings as $styling):

                    $wrap_with_class = isset($styling['wrap_with_class']) ? $styling['wrap_with_class'] : '';
                    echo ( $styling['type'] != 'separator' ) ? '<div class="themify_builder_field ' . esc_attr($wrap_with_class) . '">' : '';
                    if (isset($styling['label'])) {
                        echo '<div class="themify_builder_label">' . esc_html($styling['label']) . '</div>';
                    }
                    echo ( $styling['type'] != 'separator' ) ? '<div class="themify_builder_input">' : '';
                    if ($styling['type'] != 'multi') {
                        themify_builder_styling_field($styling);
                    } else {
                        foreach ($styling['fields'] as $field) {
                            themify_builder_styling_field($field);
                        }
                    }
                    echo ( $styling['type'] != 'separator' ) ? '</div>' : ''; // themify_builder_input
                    echo ( $styling['type'] != 'separator' ) ? '</div>' : ''; // themify_builder_field

                endforeach;
                ?>

                <p>
                    <a href="#" class="reset-styling" data-reset="column">
                        <i class="ti ti-close"></i>
                        <?php _e('Reset Styling', 'themify') ?>
                    </a>
                </p>

            </div>
        </div>
        <!-- /.themify_builder_options_tab_wrapper -->

    </form>
</script>