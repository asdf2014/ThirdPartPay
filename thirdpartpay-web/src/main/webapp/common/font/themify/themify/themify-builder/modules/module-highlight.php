<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Highlight
 * Description: Display highlight custom post type
 */
class TB_Highlight_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Highlight', 'themify'),
			'slug' => 'highlight'
		));

		///////////////////////////////////////
		// Load Post Type
		///////////////////////////////////////
		$this->meta_box = $this->set_metabox();
		$this->initialize_cpt( array(
			'plural' => __('Highlights', 'themify'),
			'singular' => __('Highlight', 'themify'),
			'menu_icon' => 'dashicons-welcome-write-blog'
		));

		if ( ! shortcode_exists( 'themify_highlight_posts' ) ) {
			add_shortcode( 'themify_highlight_posts', array( $this, 'do_shortcode' ) );
		}
	}

	public function get_title( $module ) {
		$type = isset( $module['mod_settings']['type_query_highlight'] ) ? $module['mod_settings']['type_query_highlight'] : 'category';
		$category = isset( $module['mod_settings']['category_highlight'] ) ? $module['mod_settings']['category_highlight'] : '';
		$slug_query = isset( $module['mod_settings']['query_slug_highlight'] ) ? $module['mod_settings']['query_slug_highlight'] : '';

		if ( 'category' == $type ) {
			return sprintf( '%s : %s', __('Category', 'themify'), $category );
		} else {
			return sprintf( '%s : %s', __('Slugs', 'themify'), $slug_query );
		}
	}

	public function get_options() {
		$image_sizes = themify_get_image_sizes_list( false );
		$options = array(
			array(
				'id' => 'mod_title_highlight',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large'
			),
			array(
				'id' => 'layout_highlight',
				'type' => 'layout',
				'label' => __('Highlight Layout', 'themify'),
				'options' => array(
					array('img' => 'grid4.png', 'value' => 'grid4', 'label' => __('Grid 4', 'themify')),
					array('img' => 'grid3.png', 'value' => 'grid3', 'label' => __('Grid 3', 'themify')),
					array('img' => 'grid2.png', 'value' => 'grid2', 'label' => __('Grid 2', 'themify')),
					array('img' => 'fullwidth.png', 'value' => 'fullwidth', 'label' => __('fullwidth', 'themify'))
				)
			),
			array(
				'id' => 'type_query_highlight',
				'type' => 'radio',
				'label' => __('Query by', 'themify'),
				'options' => array(
					'category' => __('Category', 'themify'),
					'post_slug' => __('Slug', 'themify')
				),
				'default' => 'category',
				'option_js' => true,
			),
			array(
				'id' => 'category_highlight',
				'type' => 'query_category',
				'label' => __('Category', 'themify'),
				'options' => array(
					'taxonomy' => 'highlight-category'
				),
				'help' => sprintf(__('Add more <a href="%s" target="_blank">highlight posts</a>', 'themify'), admin_url('post-new.php?post_type=highlight')),
				'wrap_with_class' => 'tf-group-element tf-group-element-category'
			),
			array(
				'id' => 'query_slug_highlight',
				'type' => 'text',
				'label' => __('Highlight Slugs', 'themify'),
				'class' => 'large',
				'wrap_with_class' => 'tf-group-element tf-group-element-post_slug',
				'help' => '<br/>' . __( 'Insert Highlight slug. Multiple slug should be separated by comma (,)', 'themify')
			),
			array(
				'id' => 'post_per_page_highlight',
				'type' => 'text',
				'label' => __('Limit', 'themify'),
				'class' => 'xsmall',
				'help' => __('number of posts to show', 'themify')
			),
			array(
				'id' => 'offset_highlight',
				'type' => 'text',
				'label' => __('Offset', 'themify'),
				'class' => 'xsmall',
				'help' => __('number of post to displace or pass over', 'themify')
			),
			array(
				'id' => 'order_highlight',
				'type' => 'select',
				'label' => __('Order', 'themify'),
				'help' => __('Descending = show newer posts first', 'themify'),
				'options' => array(
					'desc' => __('Descending', 'themify'),
					'asc' => __('Ascending', 'themify')
				)
			),
			array(
				'id' => 'orderby_highlight',
				'type' => 'select',
				'label' => __('Order By', 'themify'),
				'options' => array(
					'date' => __('Date', 'themify'),
					'id' => __('Id', 'themify'),
					'author' => __('Author', 'themify'),
					'title' => __('Title', 'themify'),
					'name' => __('Name', 'themify'),
					'modified' => __('Modified', 'themify'),
					'rand' => __('Random', 'themify'),
					'comment_count' => __('Comment Count', 'themify')
				)
			),
			array(
				'id' => 'display_highlight',
				'type' => 'select',
				'label' => __('Display', 'themify'),
				'options' => array(
					'content' => __('Content', 'themify'),
					'excerpt' => __('Excerpt', 'themify'),
					'none' => __('None', 'themify')
				)
			),
			array(
				'id' => 'hide_feat_img_highlight',
				'type' => 'select',
				'label' => __('Hide Featured Image', 'themify'),
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'options' => array(
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			array(
				'id' => 'image_size_highlight',
				'type' => 'select',
				'label' => Themify_Builder_Model::is_img_php_disabled() ? __('Image Size', 'themify') : false,
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'hide' => Themify_Builder_Model::is_img_php_disabled() ? false : true,
				'options' => $image_sizes
			),
			array(
				'id' => 'img_width_highlight',
				'type' => 'text',
				'label' => __('Image Width', 'themify'),
				'class' => 'xsmall'
			),
			array(
				'id' => 'img_height_highlight',
				'type' => 'text',
				'label' => __('Image Height', 'themify'),
				'class' => 'xsmall'
			),
			array(
				'id' => 'hide_post_title_highlight',
				'type' => 'select',
				'label' => __('Hide Post Title', 'themify'),
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'options' => array(
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			array(
				'id' => 'hide_page_nav_highlight',
				'type' => 'select',
				'label' => __('Hide Page Navigation', 'themify'),
				'options' => array(
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			)
		);
		return $options;
	}

	public function get_animation() {
		$animation = array(
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __('Effect', 'themify'),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'label' => __( 'Effect', 'themify' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'label' => __( 'Delay', 'themify' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'themify' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'label' => __( 'Repeat', 'themify' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'themify' ),
					),
				)
			)
		);

		return $animation;
	}

	public function get_styling() {
		$styling = array(
			// Background
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Background', 'themify').'</h4>'),
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __('Background Color', 'themify'),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => array( '.module-highlight .post' )
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'themify').'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'themify'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-highlight .post-title', '.module-highlight .post-title a' ),
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __('Font Color', 'themify'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-highlight .post', '.module-highlight h1', '.module-highlight h2', '.module-highlight h3:not(.module-title)', '.module-highlight h4', '.module-highlight h5', '.module-highlight h6', '.module-highlight .post-title', '.module-highlight .post-title a' ),
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
				'label' => __( 'Text Align', 'themify' ),
				'type' => 'radio',
				'meta' => array(
					array( 'value' => '', 'name' => __( 'Default', 'themify' ), 'selected' => true ),
					array( 'value' => 'left', 'name' => __( 'Left', 'themify' ) ),
					array( 'value' => 'center', 'name' => __( 'Center', 'themify' ) ),
					array( 'value' => 'right', 'name' => __( 'Right', 'themify' ) ),
					array( 'value' => 'justify', 'name' => __( 'Justify', 'themify' ) )
				),
				'prop' => 'text-align',
				'selector' => '.module-highlight .post',
			),
			// Link
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_link',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Link', 'themify').'</h4>'),
			),
			array(
				'id' => 'link_color',
				'type' => 'color',
				'label' => __('Color', 'themify'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-highlight a'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> array(
					array('value' => '',   'name' => '', 'selected' => true),
					array('value' => 'underline',   'name' => __('Underline', 'themify')),
					array('value' => 'overline', 'name' => __('Overline', 'themify')),
					array('value' => 'line-through',  'name' => __('Line through', 'themify')),
					array('value' => 'none',  'name' => __('None', 'themify'))
				),
				'prop' => 'text-decoration',
				'selector' => '.module-highlight a'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Padding', 'themify').'</h4>'),
			),
			array(
				'id' => 'multi_padding_top',
				'type' => 'multi',
				'label' => __('Padding', 'themify'),
				'fields' => array(
					array(
						'id' => 'padding_top',
						'type' => 'text',
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-top',
						'selector' => '.module-highlight .post',
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
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-right',
						'selector' => '.module-highlight .post',
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
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-bottom',
						'selector' => '.module-highlight .post',
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
						'class' => 'style_padding style_field xsmall',
						'prop' => 'padding-left',
						'selector' => '.module-highlight .post',
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
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_margin',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Margin', 'themify').'</h4>'),
			),
			array(
				'id' => 'multi_margin_top',
				'type' => 'multi',
				'label' => __('Margin', 'themify'),
				'fields' => array(
					array(
						'id' => 'margin_top',
						'type' => 'text',
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-top',
						'selector' => '.module-highlight .post',
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
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-right',
						'selector' => '.module-highlight .post',
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
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-bottom',
						'selector' => '.module-highlight .post',
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
						'class' => 'style_margin style_field xsmall',
						'prop' => 'margin-left',
						'selector' => '.module-highlight .post',
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
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_border',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Border', 'themify').'</h4>'),
			),
			array(
				'id' => 'multi_border_top',
				'type' => 'multi',
				'label' => __('Border', 'themify'),
				'fields' => array(
					array(
						'id' => 'border_top_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'border-top-color',
						'selector' => '.module-highlight .post',
					),
					array(
						'id' => 'border_top_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-top-width',
						'selector' => '.module-highlight .post',
					),
					array(
						'id' => 'border_top_style',
						'type' => 'select',
						'description' => __('top', 'themify'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-top-style',
						'selector' => '.module-highlight .post',
					),
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
						'class' => 'small',
						'prop' => 'border-right-color',
						'selector' => '.module-highlight .post',
					),
					array(
						'id' => 'border_right_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-right-width',
						'selector' => '.module-highlight .post',
					),
					array(
						'id' => 'border_right_style',
						'type' => 'select',
						'description' => __('right', 'themify'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-right-style',
						'selector' => '.module-highlight .post',
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
						'class' => 'small',
						'prop' => 'border-bottom-color',
						'selector' => '.module-highlight .post',
					),
					array(
						'id' => 'border_bottom_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-bottom-width',
						'selector' => '.module-highlight .post',
					),
					array(
						'id' => 'border_bottom_style',
						'type' => 'select',
						'description' => __('bottom', 'themify'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-bottom-style',
						'selector' => '.module-highlight .post',
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
						'class' => 'small',
						'prop' => 'border-left-color',
						'selector' => '.module-highlight .post',
					),
					array(
						'id' => 'border_left_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_border style_field xsmall',
						'prop' => 'border-left-width',
						'selector' => '.module-highlight .post',
					),
					array(
						'id' => 'border_left_style',
						'type' => 'select',
						'description' => __('left', 'themify'),
						'meta' => Themify_Builder_model::get_border_styles(),
						'prop' => 'border-left-style',
						'selector' => '.module-highlight .post',
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
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_highlight',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'class' => 'large exclude-from-reset-field',
				'description' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') )
			)
		);
		return $styling;
	}

	function set_metabox() {
		// Highlight Meta Box Options
		$meta_box = array(
			// Feature Image
			Themify_Builder_Model::$post_image,
			// Featured Image Size
			Themify_Builder_Model::$featured_image_size,
			// Image Width
			Themify_Builder_Model::$image_width,
			// Image Height
			Themify_Builder_Model::$image_height,
			// External Link
			Themify_Builder_Model::$external_link,
			// Lightbox Link
			Themify_Builder_Model::$lightbox_link
		);
		return $meta_box;
	}

	function do_shortcode( $atts ) {
		global $ThemifyBuilder;

		extract( shortcode_atts( array(
			'id' => '',
			'title' => 'yes', // no
			'image' => 'yes', // no
			'image_w' => 68,
			'image_h' => 68,
			'display' => 'content', // excerpt, none
			'more_link' => false, // true goes to post type archive, and admits custom link
			'more_text' => __('More &rarr;', 'themify'),
			'limit' => 6,
			'category' => 0, // integer category ID
			'order' => 'DESC', // ASC
			'orderby' => 'date', // title, rand
			'style' => 'grid3', // grid4, grid2, list-post
			'section_link' => false // true goes to post type archive, and admits custom link
		), $atts ) );

		$sync = array(
			'mod_title_highlight' => '',
			'layout_highlight' => $style,
			'category_highlight' => $category,
			'post_per_page_highlight' => $limit,
			'offset_highlight' => '',
			'order_highlight' => $order,
			'orderby_highlight' => $orderby,
			'display_highlight' => $display,
			'hide_feat_img_highlight' => $image == 'yes' ? 'no' : 'yes',
			'image_size_highlight' => '',
			'img_width_highlight' => $image_w,
			'img_height_highlight' => $image_h,
			'hide_post_title_highlight' => $title == 'yes' ? 'no' : 'yes',
			'hide_post_date_highlight' => '',
			'hide_post_meta_highlight' => '',
			'hide_page_nav_highlight' => 'yes',
			'animation_effect' => '',
			'css_highlight' => ''
		);
		$module = array(
			'module_ID' => $this->slug . '-' . rand(0,10000),
			'mod_name' => $this->slug,
			'mod_settings' => $sync
		);

		return $ThemifyBuilder->retrieve_template( 'template-' . $this->slug . '.php', $module, '', '', false );
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
if( $this->is_cpt_active( 'highlight' ) ) {
	Themify_Builder_Model::register_module( 'TB_Highlight_Module' );
}