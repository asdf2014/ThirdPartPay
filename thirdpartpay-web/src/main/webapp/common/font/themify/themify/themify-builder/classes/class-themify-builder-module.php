<?php
/**
 * This file contain abstraction class to create module object.
 *
 * Themify_Builder_Module class should be used as main class and
 * create any child extend class for module.
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * The abstract class for Module.
 *
 * Abstraction class to initialize module object, don't initialize
 * this class directly but please create child class of it.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
abstract class Themify_Builder_Module {

	/**
	 * Module Name.
	 * 
	 * @access public
	 * @var string $name
	 */
	public $name;

	/**
	 * Module Slug.
	 * 
	 * @access public
	 * @var string $slug
	 */
	public $slug;

	/**
	 * Custom Post Type arguments.
	 * 
	 * @access public
	 * @var array $cpt_args
	 */
	public $cpt_args = array();

	/**
	 * Custom Post Type options
	 * 
	 * @access public
	 * @var array $cpt_options
	 */
	public $cpt_options = array();

	/**
	 * Taxonomy options.
	 * 
	 * @access public
	 * @var array $tax_options
	 */
	public $tax_options = array();

	/**
	 * Metabox options.
	 * 
	 * @access public
	 * @var array $meta_box
	 */
	public $meta_box = array();

	/**
	 * Compatibility with legacy versions of Builder, stores the array of options containing the module options
	 * 
	 * @access public
	 * @var array $_legacy_options
	 */
	public $_legacy_options = array();

	/**
	 * Constructor.
	 * 
	 * @access public
	 * @param array $params 
	 */
	public function __construct( $params ) {
		$this->name = $params['name'];
		$this->slug = $params['slug'];
	}

	/**
	 * Get module options.
	 * 
	 * @access public
	 */
	public function get_options() {
		if( isset( $this->_legacy_options['options'] ) ) {
			return $this->_legacy_options['options'];
		}
	}

	/**
	 * Get module styling options.
	 * 
	 * @access public
	 */
	public function get_styling() {
		if( isset( $this->_legacy_options['styling'] ) ) {
			return $this->_legacy_options['styling'];
		}
	}

	public function render( $mod_id, $builder_id, $settings ) {
		global $ThemifyBuilder;
		return $ThemifyBuilder->retrieve_template('template-' . $this->slug . '.php', array(
			'module_ID' => $mod_id,
			'mod_name' => $this->slug,
			'builder_id' => $builder_id,
			'mod_settings' => $settings
		), '', '', false);
	}

	/**
	 * Get module styling CSS Selectors.
	 * 
	 * @access public
	 */
	public function get_css_selectors() {
		if( isset( $this->_legacy_options['styling_selector'] ) ) {
			return $this->_legacy_options['styling_selector'];
		}
	}

	/**
	 * Initialize Custom Post Type.
	 * 
	 * @access public
	 * @param array $args 
	 */
	public function initialize_cpt( $args ) {
		$this->cpt_args = $args;
		add_action( 'init', array( $this, 'load_cpt' ) );
		add_filter( 'post_updated_messages', array( $this, 'cpt_updated_messages' ) );
	}

	/**
	 * Load Custom Post Type.
	 * 
	 * @access public
	 */
	public function load_cpt() {
		global $ThemifyBuilder;

		if ( post_type_exists( $this->slug ) ) {
			// check taxonomy register
			if ( ! taxonomy_exists( $this->slug . '-category' ) ) {
				$this->register_taxonomy();
			}
		} else {
			$this->register_cpt();
			$this->register_taxonomy();
			add_filter( 'themify_do_metaboxes', array( $this, 'cpt_meta_boxes' ) );
			
			// push to themify builder class
			$ThemifyBuilder->push_post_types( $this->slug );
		}
	}

	/**
	 * Customize post type updated messages.
	 * 
	 * @access public
	 * @param $messages
	 * @return mixed
	 */
	public function cpt_updated_messages( $messages ) {
		global $post, $post_ID;
		$view = get_permalink( $post_ID );

		$messages[ $this->slug ] = array(
			0 => '',
			1 => sprintf( __('%s updated. <a href="%s">View %s</a>.', 'themify'), $this->name, esc_url( $view ), $this->name ),
			2 => __( 'Custom field updated.', 'themify' ),
			3 => __( 'Custom field deleted.', 'themify' ),
			4 => sprintf( __('%s updated.', 'themify'), $this->name ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'themify' ), $this->name, wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('%s published.', 'themify'), $this->name ),
			7 => sprintf( __('%s saved.', 'themify'), $this->name ),
			8 => sprintf( __('%s submitted.', 'themify'), $this->name ),
			9 => sprintf( __( '%s scheduled for: <strong>%s</strong>.', 'themify' ),
				$this->name, date_i18n( __( 'M j, Y @ G:i', 'themify' ), strtotime( $post->post_date ) ) ),
			10 => sprintf( __( '%s draft updated.', 'themify' ), $this->name )
		);
		return $messages;
	}

	/**
	 * Register Post type.
	 * 
	 * @access public
	 * @param array $cpt 
	 * @return void
	 */
	public function register_cpt( $cpt = array() ) {
		$cpt = $this->cpt_args;
		$options = array(
			'labels' => array(
				'name' => $cpt['plural'],
				'singular_name' => $cpt['singular'],
				'add_new' => __( 'Add New', 'themify' ),
				'add_new_item' => sprintf(__( 'Add New %s', 'themify' ), $cpt['singular']),
				'edit_item' => sprintf(__( 'Edit %s', 'themify' ), $cpt['singular']),
				'new_item' => sprintf(__( 'New %s', 'themify' ), $cpt['singular']),
				'view_item' => sprintf(__( 'View %s', 'themify' ), $cpt['singular']),
				'search_items' => sprintf(__( 'Search %s', 'themify' ), $cpt['plural']),
				'not_found' => sprintf(__( 'No %s found', 'themify' ), $cpt['plural']),
				'not_found_in_trash' => sprintf(__( 'No %s found in Trash', 'themify' ), $cpt['plural']),
				'menu_name' => $cpt['plural']
			),
			'supports' => isset($cpt['supports'])? $cpt['supports'] : array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'),
			//'menu_position' => $position++,
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'publicly_queryable' => true,
			'rewrite' => array( 'slug' => isset($cpt['rewrite'])? $cpt['rewrite']: strtolower($cpt['singular']) ),
			'query_var' => true,
			'can_export' => true,
			'capability_type' => 'post',
			'menu_icon' => isset( $cpt['menu_icon'] ) ? $cpt['menu_icon'] : ''
		);

		$options = wp_parse_args( $this->cpt_options, $options );

		register_post_type( $this->slug, $options );
	}

	/**
	 * Register Taxonomy.
	 * 
	 * @access public
	 * @param array $cpt 
	 * @return void
	 */
	public function register_taxonomy( $cpt = array() ) {
		global $ThemifyBuilder;

		$cpt = $this->cpt_args;
		$options = array(
			'labels' => array(
				'name' => sprintf(__( '%s Categories', 'themify' ), $cpt['singular']),
				'singular_name' => sprintf(__( '%s Category', 'themify' ), $cpt['singular']),
				'search_items' => sprintf(__( 'Search %s Categories', 'themify' ), $cpt['singular']),
				'popular_items' => sprintf(__( 'Popular %s Categories', 'themify' ), $cpt['singular']),
				'all_items' => sprintf(__( 'All Categories', 'themify' ), $cpt['singular']),
				'parent_item' => sprintf(__( 'Parent %s Category', 'themify' ), $cpt['singular']),
				'parent_item_colon' => sprintf(__( 'Parent %s Category:', 'themify' ), $cpt['singular']),
				'edit_item' => sprintf(__( 'Edit %s Category', 'themify' ), $cpt['singular']),
				'update_item' => sprintf(__( 'Update %s Category', 'themify' ), $cpt['singular']),
				'add_new_item' => sprintf(__( 'Add New %s Category', 'themify' ), $cpt['singular']),
				'new_item_name' => sprintf(__( 'New %s Category', 'themify' ), $cpt['singular']),
				'separate_items_with_commas' => sprintf(__( 'Separate %s Category with commas', 'themify' ), $cpt['singular']),
				'add_or_remove_items' => sprintf(__( 'Add or remove %s Category', 'themify' ), $cpt['singular']),
				'choose_from_most_used' => sprintf(__( 'Choose from the most used %s Category', 'themify' ), $cpt['singular']),
				'menu_name' => sprintf(__( '%s Category', 'themify' ), $cpt['singular']),
			),
			'public' => true,
			'show_in_nav_menus' => false,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_tagcloud' => true,
			'hierarchical' => true,
			'rewrite' => true,
			'query_var' => true
		);
		$options = wp_parse_args( $this->tax_options, $options );

		register_taxonomy( $this->slug . '-category', array( $this->slug ), $options );
		add_filter( 'manage_edit-' . $this->slug .'-category_columns', array($ThemifyBuilder, 'taxonomy_header'), 10, 2 );
		add_filter( 'manage_'. $this->slug .'-category_custom_column', array($ThemifyBuilder, 'taxonomy_column_id'), 10, 3 );

		// admin column custom taxonomy
		add_filter( 'manage_taxonomies_for_'. $this->slug .'_columns', array( $this, 'category_columns' ) );
	}

	/**
	 * Category Columns.
	 * 
	 * @access public
	 * @param array $taxonomies 
	 * @return array
	 */
	public function category_columns( $taxonomies ) {
		$taxonomies[] = $this->slug . '-category';
		return $taxonomies;
	}

	/**
	 * If there's not an options tab in Themify Custom Panel meta box already defined for this post type, like "Portfolio Options", add one.
	 *
	 * @since 2.3.8
	 *
	 * @param array $meta_boxes
	 *
	 * @return array
	 */
	public function cpt_meta_boxes( $meta_boxes = array() ) {
		$meta_box_id = $this->slug . '-options';
		if ( ! in_array( $meta_box_id, wp_list_pluck( $meta_boxes, 'id' ) ) ) {
			$meta_boxes = array_merge( $meta_boxes, array(
				array(
					'name'	  => esc_html__( sprintf( __( '%s Options', 'themify' ), $this->cpt_args['singular'] ) ),
					'id' 	  => $meta_box_id,
					'options' => $this->meta_box,
					'pages'	  => $this->slug
				)
			));
		}
		return $meta_boxes;
	}

	/**
	 * Get Module Title.
	 * 
	 * @access public
	 * @param object $module 
	 */
	public function get_title( $module ) {
		return '';
	}
}