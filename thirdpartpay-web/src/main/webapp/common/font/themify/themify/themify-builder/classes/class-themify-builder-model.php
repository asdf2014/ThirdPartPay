<?php

final class Themify_Builder_Model {
	/**
	 * Feature Image
	 * @var array
	 */
	static public $post_image = array();
	
	/**
	 * Feature Image Size
	 * @var array
	 */
	static public $featured_image_size = array();

	/**
	 * Image Width
	 * @var array
	 */
	static public $image_width = array();

	/**
	 * Image Height
	 * @var array
	 */
	static public $image_height = array();

	/**
	 * External Link
	 * @var array
	 */
	static public $external_link = array();

	/**
	 * Lightbox Link
	 * @var array
	 */
	static public $lightbox_link = array();

	static public $modules = array();

	static public $layouts_version_name = 'tbuilder_layouts_version';

	/**
	 * Color definitions
	 *
	 * @var array
	 */
	static public $colors;

	/**
	 * Appearance definitions
	 *
	 * @var array
	 */
	static public $appearance;

	/**
	 * Border style definitions
	 *
	 * @var array
	 */
	static public $border_style;

	static public function register_module( $module_class, $options = null ) {
		if ( class_exists( $module_class ) ) {

			$instance = new $module_class();

			if( null != $options ) {
				$instance->_legacy_options['options'] = isset( $options['options'] ) ? $options['options'] : array();
				$instance->_legacy_options['styling'] = isset( $options['styling'] ) ? $options['styling'] : array();
				$instance->_legacy_options['styling_selector'] = isset( $options['styling_selector'] ) ? $options['styling_selector'] : array();
			}

			self::$modules[ $instance->slug ] = $instance;
		}
	}

	/**
	 * Check whether builder is active or not
	 * @return bool
	 */
	static public function builder_check() {
		$enable_builder = apply_filters( 'themify_enable_builder', themify_get('setting-page_builder_is_active') );
		if ( 'disable' == $enable_builder ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check whether module is active
	 * @param $name
	 * @return boolean
	 */
	static public function check_module_active( $name ) {
		if ( isset( self::$modules[ $name ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check is frontend editor page
	 */
	static public function is_frontend_editor_page() {
		$active = false;
		if ( is_user_logged_in() && current_user_can( 'edit_pages', get_the_ID() ) ) {
			$active = true;
		}

		return apply_filters( 'themify_builder_is_frontend_editor', $active );
	}

	/**
	 * Load general metabox fields
	 */
	static public function load_general_metabox() {
		// Feature Image
		self::$post_image = apply_filters( 'themify_builder_metabox_post_image', array(
			'name' 		=> 'post_image',	
			'title' 	=> __('Featured Image', 'themify'),
			'description' => '', 				
			'type' 		=> 'image',			
			'meta'		=> array()
		) );
		// Featured Image Size
		self::$featured_image_size = apply_filters( 'themify_builder_metabox_featured_image_size', array(
			'name'	=>	'feature_size',
			'title'	=>	__('Image Size', 'themify'),
			'description' => sprintf( __('Image sizes can be set at <a href="%s">Media Settings</a>', 'themify'), admin_url( 'options-media.php' ) ),
			'type'		 =>	'featimgdropdown'
		) );
		// Image Width
		self::$image_width = apply_filters( 'themify_builder_metabox_image_width', array(
			'name' 		=> 'image_width',
			'title' 	=> __('Image Width', 'themify'),
			'description' => '',			
			'type' 		=> 'textbox',
			'meta'		=> array('size'=>'small')
		) );
		// Image Height
		self::$image_height = apply_filters( 'themify_builder_metabox_image_height', array(
			'name' 		=> 'image_height',
			'title' 	=> __('Image Height', 'themify'),
			'description' => '',
			'type' 		=> 'textbox',
			'meta'		=> array('size'=>'small'),
			'class'		=> self::is_img_php_disabled() ? 'builder_show_if_enabled_img_php' : '',
		) );
		// External Link
		self::$external_link = apply_filters( 'themify_builder_metabox_external_link', array(
			'name' 		=> 'external_link',
			'title' 	=> __('External Link', 'themify'),
			'description' => __('Link Featured Image and Post Title to external URL', 'themify'),
			'type' 		=> 'textbox',
			'meta'		=> array()
		) );
		// Lightbox Link
		self::$lightbox_link = apply_filters( 'themify_builder_metabox_lightbox_link', array(
			'name' 		=> 'lightbox_link',
			'title' 	=> __('Lightbox Link', 'themify'),
			'description' => __('Link Featured Image to lightbox image, video or external iframe', 'themify'),
			'type' 		=> 'textbox',
			'meta'		=> array()
		) );
	}

	/**
	 * Get module name by slug
	 * @param string $slug 
	 * @return string
	 */
	static public function get_module_name( $slug ) {
		if ( isset( self::$modules[ $slug ] ) && is_object( self::$modules[ $slug ] ) ) {
			return self::$modules[ $slug ]->name;
		} else {
			return $slug;
		}
	}

	/**
	 * Set Pre-built Layout version
	 */
	static public function set_current_layouts_version( $version ) {
		return set_transient( self::$layouts_version_name, $version );
	}

	/**
	 * Get current Pre-built Layout version
	 */
	static public function get_current_layouts_version() {
		$current_layouts_version = get_transient( self::$layouts_version_name );
		if ( false === $current_layouts_version ) {
			self::set_current_layouts_version( '0' );
			$current_layouts_version = '0';
		}
		return $current_layouts_version;
	}

	/**
	 * Check if there Pre-built Layout updates
	 */
	static public function find_layout_updates() {
		if ( version_compare( THEMIFY_BUILDER_LAYOUTS_VERSION, self::get_current_layouts_version(), '>' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check whether layout is pre-built layout or custom
	 */
	static public function is_prebuilt_layout( $id ) {
		$protected = get_post_meta( $id, '_themify_builder_prebuilt_layout', true );

		if ( ! empty( $protected ) && 'yes' == $protected ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return animation presets
	 */
	static public function get_preset_animation() {
		$animation = array(
			array( 'group_label' => __( 'Attention Seekers', 'themify' ), 'options' => array(
				array( 'value' => 'bounce', 'name' => __( 'bounce', 'themify' ) ),
				array( 'value' => 'flash', 'name' => __( 'flash', 'themify' ) ),
				array( 'value' => 'pulse', 'name' => __( 'pulse', 'themify' ) ),
				array( 'value' => 'rubberBand', 'name' => __( 'rubberBand', 'themify' ) ),
				array( 'value' => 'shake', 'name' => __( 'shake', 'themify' ) ),
				array( 'value' => 'swing', 'name' => __( 'swing', 'themify' ) ),
				array( 'value' => 'tada', 'name' => __( 'tada', 'themify' ) ),
				array( 'value' => 'wobble', 'name' => __( 'wobble', 'themify' ) ),
				array( 'value' => 'jello', 'name' => __( 'jello', 'themify' ) ),
			)),

			array( 'group_label' => __( 'Bouncing Entrances', 'themify' ), 'options' => array(
				array( 'value' => 'bounceIn', 'name' => __( 'bounceIn', 'themify' ) ),
				array( 'value' => 'bounceInDown', 'name' => __( 'bounceInDown', 'themify' ) ),
				array( 'value' => 'bounceInLeft', 'name' => __( 'bounceInLeft', 'themify' ) ),
				array( 'value' => 'bounceInRight', 'name' => __( 'bounceInRight', 'themify' ) ),
				array( 'value' => 'bounceInUp', 'name' => __( 'bounceInUp', 'themify' ) ),
			)),

			array( 'group_label' => __( 'Bouncing Exits', 'themify' ), 'options' => array(
				array( 'value' => 'bounceOut', 'name' => __( 'bounceOut', 'themify' ) ),
				array( 'value' => 'bounceOutDown', 'name' => __( 'bounceOutDown', 'themify' ) ),
				array( 'value' => 'bounceOutLeft', 'name' => __( 'bounceOutLeft', 'themify' ) ),
				array( 'value' => 'bounceOutRight', 'name' => __( 'bounceOutRight', 'themify' ) ),
				array( 'value' => 'bounceOutUp', 'name' => __( 'bounceOutUp', 'themify' ) ),
			)),

			array( 'group_label' => __( 'Fading Entrances', 'themify' ), 'options' => array(
				array( 'value' => 'fadeIn', 'name' => __( 'fadeIn', 'themify' ) ),
				array( 'value' => 'fadeInDown', 'name' => __( 'fadeInDown', 'themify' ) ),
				array( 'value' => 'fadeInDownBig', 'name' => __( 'fadeInDownBig', 'themify' ) ),
				array( 'value' => 'fadeInLeft', 'name' => __( 'fadeInLeft', 'themify' ) ),
				array( 'value' => 'fadeInLeftBig', 'name' => __( 'fadeInLeftBig', 'themify' ) ),
				array( 'value' => 'fadeInRight', 'name' => __( 'fadeInRight', 'themify' ) ),
				array( 'value' => 'fadeInRightBig', 'name' => __( 'fadeInRightBig', 'themify' ) ),
				array( 'value' => 'fadeInUp', 'name' => __( 'fadeInUp', 'themify' ) ),
				array( 'value' => 'fadeInUpBig', 'name' => __( 'fadeInUpBig', 'themify' ) ),
			)),

			array( 'group_label' => __( 'Fading Exits', 'themify' ), 'options' => array(
				array( 'value' => 'fadeOut', 'name' => __( 'fadeOut', 'themify' ) ),
				array( 'value' => 'fadeOutDown', 'name' => __( 'fadeOutDown', 'themify' ) ),
				array( 'value' => 'fadeOutDownBig', 'name' => __( 'fadeOutDownBig', 'themify' ) ),
				array( 'value' => 'fadeOutLeft', 'name' => __( 'fadeOutLeft', 'themify' ) ),
				array( 'value' => 'fadeOutLeftBig', 'name' => __( 'fadeOutLeftBig', 'themify' ) ),
				array( 'value' => 'fadeOutRight', 'name' => __( 'fadeOutRight', 'themify' ) ),
				array( 'value' => 'fadeOutRightBig', 'name' => __( 'fadeOutRightBig', 'themify' ) ),
				array( 'value' => 'fadeOutUp', 'name' => __( 'fadeOutUp', 'themify' ) ),
				array( 'value' => 'fadeOutUpBig', 'name' => __( 'fadeOutUpBig', 'themify' ) ),
			)),

			array( 'group_label' => __( 'Flippers', 'themify' ), 'options' => array(
				array('value' => 'flip',   'name' => __('flip', 'themify')),
				array('value' => 'flipInX', 'name' => __('flipInX', 'themify')),
				array('value' => 'flipInY',  'name' => __('flipInY', 'themify')),
				array('value' => 'flipOutX',  'name' => __('flipOutX', 'themify')),
				array('value' => 'flipOutY',  'name' => __('flipOutY', 'themify'))
			)),

			array( 'group_label' => __( 'Lightspeed', 'themify' ), 'options' => array(
				array('value' => 'lightSpeedIn',   'name' => __('lightSpeedIn', 'themify')),
				array('value' => 'lightSpeedOut', 'name' => __('lightSpeedOut', 'themify')),
			)),

			array( 'group_label' => __( 'Rotating Entrances', 'themify' ), 'options' => array(
				array('value' => 'rotateIn',   'name' => __('rotateIn', 'themify')),
				array('value' => 'rotateInDownLeft', 'name' => __('rotateInDownLeft', 'themify')),
				array('value' => 'rotateInDownRight',  'name' => __('rotateInDownRight', 'themify')),
				array('value' => 'rotateInUpLeft',  'name' => __('rotateInUpLeft', 'themify')),
				array('value' => 'rotateInUpRight',  'name' => __('rotateInUpRight', 'themify'))
			)),

			array( 'group_label' => __( 'Rotating Exits', 'themify' ), 'options' => array(
				array('value' => 'rotateOut',   'name' => __('rotateOut', 'themify')),
				array('value' => 'rotateOutDownLeft', 'name' => __('rotateOutDownLeft', 'themify')),
				array('value' => 'rotateOutDownRight',  'name' => __('rotateOutDownRight', 'themify')),
				array('value' => 'rotateOutUpLeft',  'name' => __('rotateOutUpLeft', 'themify')),
				array('value' => 'rotateOutUpRight',  'name' => __('rotateOutUpRight', 'themify'))
			)),

			array( 'group_label' => __( 'Specials', 'themify' ), 'options' => array(
				array('value' => 'hinge',   'name' => __('hinge', 'themify')),
				array('value' => 'rollIn', 'name' => __('rollIn', 'themify')),
				array('value' => 'rollOut',  'name' => __('rollOut', 'themify')),
			)),

			array( 'group_label' => __( 'Zoom Entrances', 'themify' ), 'options' => array(
				array('value' => 'zoomIn',   'name' => __('zoomIn', 'themify')),
				array('value' => 'zoomInDown', 'name' => __('zoomInDown', 'themify')),
				array('value' => 'zoomInLeft',  'name' => __('zoomInLeft', 'themify')),
				array('value' => 'zoomInRight',  'name' => __('zoomInRight', 'themify')),
				array('value' => 'zoomInUp',  'name' => __('zoomInUp', 'themify'))
			)),

			array( 'group_label' => __( 'Zoom Exits', 'themify' ), 'options' => array(
				array('value' => 'zoomOut',   'name' => __('zoomOut', 'themify')),
				array('value' => 'zoomOutDown', 'name' => __('zoomOutDown', 'themify')),
				array('value' => 'zoomOutLeft',  'name' => __('zoomOutLeft', 'themify')),
				array('value' => 'zoomOutRight',  'name' => __('zoomOutRight', 'themify')),
				array('value' => 'zoomOutUp',  'name' => __('zoomOutUp', 'themify'))
			)),

			array( 'group_label' => __( 'Slide Entrance', 'themify' ), 'options' => array(
				array('value' => 'slideInDown',   'name' => __('slideInDown', 'themify')),
				array('value' => 'slideInLeft',   'name' => __('slideInLeft', 'themify')),
				array('value' => 'slideInRight',   'name' => __('slideInRight', 'themify')),
				array('value' => 'slideInUp',   'name' => __('slideInUp', 'themify')),
			)),

			array( 'group_label' => __( 'Slide Exit', 'themify' ), 'options' => array(
				array('value' => 'slideOutDown',   'name' => __('slideOutDown', 'themify')),
				array('value' => 'slideOutLeft',   'name' => __('slideOutLeft', 'themify')),
				array('value' => 'slideOutRight',   'name' => __('slideOutRight', 'themify')),
				array('value' => 'slideOutUp',   'name' => __('slideOutUp', 'themify')),
			)),

		);
		return $animation;
	}

	/**
	 * Get Post Types which ready for an operation
	 * @return array
	 */
	static public function get_post_types() {

		// If it's not a product search, proceed: retrieve the post types.
		$types = get_post_types( array( 'exclude_from_search' => false ) );

		// Exclude pages /////////////////
		$exclude_pages = themify_get( 'setting-search_settings_exclude' );
		if ( isset( $exclude_pages ) && $exclude_pages ) {
			unset( $types['page'] );
		}

		// Exclude custom post types /////
		$exclude_types = apply_filters( 'themify_types_excluded_in_search', get_post_types( array(
			'_builtin' => false,
			'public' => true,
			'exclude_from_search' => false
		)));

		foreach( array_keys( $exclude_types ) as $type ) {
			$exclude_type = null;
			$exclude_type = themify_get( 'setting-search_exclude_' . $type );
			if ( isset( $exclude_type ) && $exclude_type ) {
				unset( $types[$type] );
			}
		}

		// Section post type is always excluded
		if ( isset( $types['section'] ) ) {
			unset( $types['section'] );
		}

		// Exclude Layout and Layout Part custom post types /////
		unset( $types['tbuilder_layout'] );
		unset( $types['tbuilder_layout_part'] );

		return $types;
	}

	/**
	 * Check whether builder animation is active
	 * @return boolean
	 */
	static public function is_animation_active() {
		// check if mobile exclude disabled OR disabled all transition
		if ( ( themify_check('setting-page_builder_animation_mobile_exclude') && themify_is_touch() ) 
			|| themify_check('setting-page_builder_animation_disabled') ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check whether builder parallax is active
	 * @return boolean
	 */
	static public function is_parallax_active() {
		// check if mobile exclude disabled OR disabled all transition
		if ( ( themify_check('setting-page_builder_parallax_mobile_exclude') && themify_is_touch() ) 
			|| themify_check('setting-page_builder_parallax_disabled') ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get Grid Settings
	 * @return array
	 */
	public static function get_grid_settings( $setting = 'grid') {
		$path = THEMIFY_BUILDER_URI . '/img/builder/';
		$grid_lists = array(
			array(
				// Grid FullWidth
				array( 'img' => $path . '1-col.png', 'data' => array( '-full') ),
				// Grid 2
				array( 'img' => $path . '2-col.png', 'data' => array( '4-2', '4-2' ) ),
				// Grid 3
				array( 'img' => $path . '3-col.png', 'data' => array( '3-1', '3-1', '3-1' ) ),
				// Grid 4
				array( 'img' => $path . '4-col.png', 'data' => array( '4-1', '4-1', '4-1', '4-1') ),
				// Grid 5
				array( 'img' => $path . '5-col.png', 'data' => array( '5-1', '5-1', '5-1', '5-1', '5-1' ) ),
				// Grid 6
				array( 'img' => $path . '6-col.png', 'data' => array( '6-1', '6-1', '6-1', '6-1', '6-1', '6-1' ) )
			),
			array(
				array( 'img' => $path . '1.4_3.4.png', 'data' => array( '4-1', '4-3' ) ),
				array( 'img' => $path . '1.4_1.4_2.4.png', 'data' => array( '4-1', '4-1', '4-2' ) ),
				array( 'img' => $path . '1.4_2.4_1.4.png', 'data' => array( '4-1', '4-2', '4-1') ),
				array( 'img' => $path . '2.4_1.4_1.4.png', 'data' => array( '4-2', '4-1', '4-1' ) ),
				array( 'img' => $path . '3.4_1.4.png', 'data' => array( '4-3', '4-1' ) )
			),
			array(
				array( 'img' => $path . '2.3_1.3.png', 'data' => array( '3-2', '3-1' ) ),
				array( 'img' => $path . '1.3_2.3.png', 'data' => array( '3-1', '3-2' ) )
			)
		);

		$gutters = array(
			array( 'name' => __('Default', 'themify'), 'value' => 'gutter-default' ),
			array( 'name' => __('Narrow', 'themify'), 'value' => 'gutter-narrow' ),
			array( 'name' => __('None', 'themify'), 'value' => 'gutter-none' ),
		);

		if ( 'grid' == $setting ) {
			return $grid_lists;
		} elseif( 'gutter_class' == $setting ) {
			$guiterClass = array();
			foreach( $gutters as $g ) {
				array_push( $guiterClass, $g['value'] );
			}
			return implode( ' ', $guiterClass );
		} else {
			return $gutters;
		}
	}

	/**
	 * Returns list of colors and thumbnails
	 *
	 * @return array
	 */
	static public function get_colors() {
		if ( ! isset( self::$colors ) ) {
			self::$colors = array(
				array('img' => 'color-default.png', 'value' => 'default', 'label' => __('default', 'themify')),
				array('img' => 'color-black.png', 'value' => 'black', 'label' => __('black', 'themify')),
				array('img' => 'color-grey.png', 'value' => 'gray', 'label' => __('gray', 'themify')),
				array('img' => 'color-blue.png', 'value' => 'blue', 'label' => __('blue', 'themify')),
				array('img' => 'color-light-blue.png', 'value' => 'light-blue', 'label' => __('light-blue', 'themify')),
				array('img' => 'color-green.png', 'value' => 'green', 'label' => __('green', 'themify')),
				array('img' => 'color-light-green.png', 'value' => 'light-green', 'label' => __('light-green', 'themify')),
				array('img' => 'color-purple.png', 'value' => 'purple', 'label' => __('purple', 'themify')),
				array('img' => 'color-light-purple.png', 'value' => 'light-purple', 'label' => __('light-purple', 'themify')),
				array('img' => 'color-brown.png', 'value' => 'brown', 'label' => __('brown', 'themify')),
				array('img' => 'color-orange.png', 'value' => 'orange', 'label' => __('orange', 'themify')),
				array('img' => 'color-yellow.png', 'value' => 'yellow', 'label' => __('yellow', 'themify')),
				array('img' => 'color-red.png', 'value' => 'red', 'label' => __('red', 'themify')),
				array('img' => 'color-pink.png', 'value' => 'pink', 'label' => __('pink', 'themify'))
			);
		}
		return self::$colors;
	}

	/**
	 * Returns list of appearance
	 *
	 * @return array
	 */
	static public function get_appearance() {
		if ( ! isset( self::$appearance ) ) {
			self::$appearance = array(
				array( 'name' => 'rounded', 'value' => __('Rounded', 'themify')),
				array( 'name' => 'gradient', 'value' => __('Gradient', 'themify')),
				array( 'name' => 'glossy', 'value' => __('Glossy', 'themify')),
				array( 'name' => 'embossed', 'value' => __('Embossed', 'themify')),
				array( 'name' => 'shadow', 'value' => __('Shadow', 'themify'))
			);
		}
		return self::$appearance;
	}

	/**
	 * Returns list of border styles
	 *
	 * @return array
	 */
	static public function get_border_styles() {
		if ( ! isset( self::$border_style ) ) {
			self::$border_style = array(
				array( 'value' => '', 'name' => '' ),
				array( 'value' => 'solid', 'name' => __( 'Solid', 'themify' ) ),
				array( 'value' => 'dashed', 'name' => __( 'Dashed', 'themify' ) ),
				array( 'value' => 'dotted', 'name' => __( 'Dotted', 'themify' ) ),
				array( 'value' => 'double', 'name' => __( 'Double', 'themify' ) )
			);
		}
		return self::$border_style;
	}

	/**
	 * Check whether image script is use or not
	 *
	 * @since 2.4.2 Check if it's a Themify theme or not. If it's not, it's Builder standalone plugin.
	 *
	 * @return boolean
	 */
	public static function is_img_php_disabled() {
		global $ThemifyBuilder;
		if ( $ThemifyBuilder->is_themify_theme() ) {
			if ( themify_check( 'setting-img_settings_use' ) ) {
				return true;
			} else{
				return false;
			}
		} else {
			if ( themify_builder_get( 'image_setting-img_settings_use' ) ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Get attachment ID for image from its url.
	 * 
	 * @since 2.2.5
	 *
	 * @param string $url
	 * @param string $base_url
	 *
	 * @return bool|null|string
	 */
	public static function get_attachment_id_by_url( $url = '', $base_url = '' ) {
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', str_replace( $base_url . '/', '', $url ) );

		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $url ) );
	}

	/**
	 * Get alt text defined in WP Media attachment by a given URL
	 *
	 * @since 2.2.5
	 * 
	 * @param string $image_url
	 * 
	 * @return string
	 */
	public static function get_alt_by_url( $image_url ) {
		$upload_dir = wp_upload_dir();
		$attachment_id = self::get_attachment_id_by_url( $image_url, $upload_dir['baseurl'] );
		if ( $attachment_id ) {
			if ( $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) {
				return $alt;	
			}
		}
		return '';
	}
}