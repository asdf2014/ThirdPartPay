<?php

class Themify_Hooks {

	/**
	 * Multi-dimensional array of hooks in a theme
	 */
	private $hook_locations;

	/**
	 * list of hooks, visible to the current page context
	 */
	private $action_map;

	var $pre = 'setting-hooks';
	var $data;

	public function __construct() {
		if( is_admin() ) {
			add_filter( 'themify_theme_config_setup', array( $this, 'config_setup' ), 12 );
			add_action( 'wp_ajax_themify_hooks_add_item', array( $this, 'ajax_add_button' ) );
			add_action( 'wp_ajax_themify_get_visibility_options', array( $this, 'ajax_get_visibility_options' ) );
			add_action( 'admin_footer', array( $this, 'visibility_dialog' ) );
			add_filter( 'themify_hooks_visibility_post_types', array( $this, 'exclude_attachments_from_visibility' ) );
		} else {
			add_action( 'template_redirect', array( $this, 'hook_locations_view_setup' ), 9 );
			add_action( 'template_redirect', array( $this, 'hooks_setup' ) );
			add_filter( 'themify_hooks_item_content', 'do_shortcode' );
		}
		add_action( 'init', array( $this, 'register_default_hook_locations' ) );
	}

	function hooks_setup() {
		$this->data = themify_get_data();
		if ( isset( $this->data["{$this->pre}_field_ids"] ) ) {
			$ids = json_decode( $this->data["{$this->pre}_field_ids"] );
			if( ! empty( $ids ) ) : foreach( $ids as $id ) :
				if( $this->check_visibility( $id ) ) {
					$location = $this->data["{$this->pre}-{$id}-location"];
					/* cache the ID of the item we have to display, so we don't have to re-run the conditional tags */
					$this->action_map[$location][] = $id;
					add_action( $location, array( &$this, 'output_item' ) );
				}
			endforeach; endif;
		}
	}

	/**
	 * Check if an item is visible for the current context
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function check_visibility( $id ) {
		$visible = true;
		$logic = $this->data["{$this->pre}-{$id}-visibility"];
		parse_str( $logic, $logic );
		$query_object = get_queried_object();

		if( ! empty( $logic['roles'] ) ) {
			if( ! in_array( $GLOBALS['current_user']->roles[0] , array_keys( $logic['roles'] ) ) ) {
				return false; // bail early.
			}
		}
		unset( $logic['roles'] );

		if( ! empty( $logic ) ) {
			$visible = false; // if any condition is set for a hook, hide it on all pages of the site except for the chosen ones.

			if( ( is_front_page() && isset( $logic['general']['home'] ) )
				|| ( is_page() && isset( $logic['general']['page'] ) && ! is_front_page() )
				|| ( is_single() && isset( $logic['general']['single'] ) )
				|| ( is_search() && isset( $logic['general']['search'] ) )
				|| ( is_author() && isset( $logic['general']['author'] ) )
				|| ( is_category() && isset( $logic['general']['category'] ) )
				|| ( is_tag() && isset( $logic['general']['tag'] ) )
				|| ( is_singular() && isset( $logic['general'][$query_object->post_type] ) && $query_object->post_type != 'page' && $query_object->post_type != 'post' )
				|| ( is_tax() && isset( $logic['general'][$query_object->taxonomy] ) )
			) {
				$visible = true;
			} else { // let's dig deeper into more specific visibility rules
				if( ! empty( $logic['tax'] ) ) {
                                         if(is_single()){
                                            if(isset($logic['tax']['category_single']) && !empty($logic['tax']['category_single'])){
                                                $cat = get_the_category();
                                                if(!empty($cat)){
                                                    foreach($cat as $c){
                                                        if($c->taxonomy == 'category' && isset($logic['tax']['category_single'][$c->slug])){
                                                            $visible = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else{
                                            foreach( $logic['tax'] as $tax => $terms ) {
                                                    $terms = array_keys( $terms );
                                                    if( ( $tax == 'category' && is_category( $terms ) )
                                                            || ( $tax == 'post_tag' && is_tag( $terms ) )
                                                            || ( is_tax( $tax, $terms ) )
                                                    ) {
                                                            $visible = true;
                                                            break;
                                                    }
                                            }
                                        }
				}
				if(!$visible && ! empty( $logic['post_type'] ) ) {
					foreach( $logic['post_type'] as $post_type => $posts ) {
						$posts = array_keys( $posts );
						if( ( $post_type == 'post' && is_single() && is_single( $posts ) )
							|| ( $post_type == 'page' && (
								( is_page() && is_page( $posts ) )
								|| ( ! is_front_page() && is_home() &&  in_array( get_post_field( 'post_name', get_option( 'page_for_posts' ) ), $posts ) ) // check for Posts page
							) )
							|| ( is_singular( $post_type ) && in_array( $query_object->post_name, $posts ) )
						) {
							$visible = true;
							break;
						}
					}
				}
			}
		}

		return $visible;
	}

	public function output_item() {
		$hook = current_filter();
		foreach( $this->action_map[$hook] as $id ) {
			/* do_shortcode is applied via the themify_hooks_item_content filter */
			echo apply_filters( 'themify_hooks_item_content', '<!-- hook content: ' . $hook . ' -->' . $this->data["{$this->pre}-{$id}-code"] . '<!-- /hook content: ' . $hook . ' -->', $this );
		}
	}

	/**
	 * Returns a list of available hooks for the current theme.
	 *
	 * @param bool $flat Whether to return a one dimensional array of hook locations.
	 *
	 * @return mixed
	 */
	public function get_locations( $flat = false ) {
		if( $flat ) {
			return call_user_func_array( 'array_merge', $this->hook_locations );
		} else {
			return $this->hook_locations;
		}
	}

	public function register_location( $id, $label, $group = 'layout' ) {
		$this->hook_locations[$group][$id] = $label;
	}

	public function unregister_location( $id ) {
		foreach( $this->hook_locations as $group => $hooks ) {
			unset( $this->hook_locations[$group][$id] );
		}
	}

	public function get_location_groups() {
		return array(
			'layout' => __( 'Layout', 'themify' ),
			'general' => __( 'General', 'themify' ),
			'post' => __( 'Post', 'themify' ),
			'comments' => __( 'Comments', 'themify' ),
			'ecommerce' => __( 'eCommerce', 'themify' ),
		);
	}

	public function register_default_hook_locations() {
		foreach( array(
			array( 'wp_head',                     'wp_head', 'general' ),
			array( 'wp_footer',                   'wp_footer', 'general' ),
			array( 'themify_body_start',          'body_start', 'layout' ),
			array( 'themify_header_before',       'header_before', 'layout' ),
			array( 'themify_header_start',        'header_start', 'layout' ),
			array( 'themify_header_end',          'header_end', 'layout' ),
			array( 'themify_header_after',        'header_after', 'layout' ),
			array( 'themify_layout_before',       'layout_before', 'layout' ),
			array( 'themify_content_before',      'content_before', 'layout' ),
			array( 'themify_content_start',       'content_start', 'layout' ),
			array( 'themify_post_before',         'post_before', 'post' ),
			array( 'themify_post_start',          'post_start', 'post' ),
			array( 'themify_before_post_image',   'before_post_image', 'post' ),
			array( 'themify_after_post_image',    'after_post_image', 'post' ),
			array( 'themify_before_post_title',   'before_post_title', 'post' ),
			array( 'themify_after_post_title',    'after_post_title', 'post' ),
			array( 'themify_post_end',            'post_end', 'post' ),
			array( 'themify_post_after',          'post_after', 'post' ),
			array( 'themify_comment_before',      'comment_before', 'comments' ),
			array( 'themify_comment_start',       'comment_start', 'comments' ),
			array( 'themify_comment_end',         'comment_end', 'comments' ),
			array( 'themify_comment_after',       'comment_after', 'comments' ),
			array( 'themify_content_end',         'content_end', 'layout' ),
			array( 'themify_content_after',       'content_after', 'layout' ),
			array( 'themify_sidebar_before',      'sidebar_before', 'layout' ),
			array( 'themify_sidebar_start',       'sidebar_start', 'layout' ),
			array( 'themify_sidebar_end',         'sidebar_end', 'layout' ),
			array( 'themify_sidebar_after',       'sidebar_after', 'layout' ),
			array( 'themify_layout_after',        'layout_after', 'layout' ),
			array( 'themify_footer_before',       'footer_before', 'layout' ),
			array( 'themify_footer_start',        'footer_start', 'layout' ),
			array( 'themify_footer_end',          'footer_end', 'layout' ),
			array( 'themify_footer_after',        'footer_after', 'layout' ),
			array( 'themify_body_end',            'body_end', 'layout' ),
		) as $key => $value ) {
			$this->register_location( $value[0], $value[1], $value[2] );
		}

		/* register ecommerce hooks group only if current theme supports WooCommerce */
		if( ! themify_is_woocommerce_active() ) {
			return;
		}
		foreach( array(
			array( 'themify_product_slider_add_to_cart_before', 'product_slider_add_to_cart_before', 'ecommerce' ),
			array( 'themify_product_slider_add_to_cart_after',  'product_slider_add_to_cart_after', 'ecommerce' ),
			array( 'themify_product_slider_image_start',        'product_slider_image_start', 'ecommerce' ),
			array( 'themify_product_slider_image_end',          'product_slider_image_end', 'ecommerce' ),
			array( 'themify_product_slider_title_start',        'product_slider_title_start', 'ecommerce' ),
			array( 'themify_product_slider_title_end',          'product_slider_title_end', 'ecommerce' ),
			array( 'themify_product_slider_price_start',        'product_slider_price_start', 'ecommerce' ),
			array( 'themify_product_slider_price_end',          'product_slider_price_end', 'ecommerce' ),
			array( 'themify_product_image_start',               'product_image_start', 'ecommerce' ),
			array( 'themify_product_image_end',                 'product_image_end', 'ecommerce' ),
			array( 'themify_product_title_start',               'product_title_start', 'ecommerce' ),
			array( 'themify_product_title_end',                 'product_title_end', 'ecommerce' ),
			array( 'themify_product_price_start',               'product_price_start', 'ecommerce' ),
			array( 'themify_product_price_end',                 'product_price_end', 'ecommerce' ),
			array( 'themify_product_cart_image_start',          'product_cart_image_start', 'ecommerce' ),
			array( 'themify_product_cart_image_end',            'product_cart_image_end', 'ecommerce' ),
			array( 'themify_product_single_image_before',       'product_single_image_before', 'ecommerce' ),
			array( 'themify_product_single_image_end',          'product_single_image_end', 'ecommerce' ),
			array( 'themify_product_single_title_before',       'product_single_title_before', 'ecommerce' ),
			array( 'themify_product_single_title_end',          'product_single_title_end', 'ecommerce' ),
			array( 'themify_product_single_price_end',          'product_single_price_end', 'ecommerce' ),
			array( 'themify_shopdock_before',                   'shopdock_before', 'ecommerce' ),
			array( 'themify_checkout_start',                    'checkout_start', 'ecommerce' ),
			array( 'themify_checkout_end',                      'checkout_end', 'ecommerce' ),
			array( 'themify_shopdock_end',                      'shopdock_end', 'ecommerce' ),
			array( 'themify_shopdock_after',                    'shopdock_after', 'ecommerce' ),
			array( 'themify_sorting_before',                    'sorting_before', 'ecommerce' ),
			array( 'themify_sorting_after',                     'sorting_after', 'ecommerce' ),
			array( 'themify_related_products_start',            'related_products_start', 'ecommerce' ),
			array( 'themify_related_products_end',              'related_products_end', 'ecommerce' ),
			array( 'themify_breadcrumb_before',                 'breadcrumb_before', 'ecommerce' ),
			array( 'themify_breadcrumb_after',                  'breadcrumb_after', 'ecommerce' ),
			array( 'themify_ecommerce_sidebar_before',          'ecommerce_sidebar_before', 'ecommerce' ),
			array( 'themify_ecommerce_sidebar_after',           'ecommerce_sidebar_after', 'ecommerce' ),
		) as $key => $value ) {
			$this->register_location( $value[0], $value[1], $value[2] );
		}
	}

	function config_setup( $themify_theme_config ) {
		$themify_theme_config['panel']['settings']['tab']['hook-content'] = array(
			'title' => __('Hook Content', 'themify'),
			'id' => 'hooks',
			'custom-module' => array(
				array(
					'title' => __( 'Hook Content', 'themify' ),
					'function' => 'themify_hooks_config_view',
				),
			)
		);

		return $themify_theme_config;
	}

	function config_view( $data = array() ) {
		$data = themify_get_data();
		$field_ids_json = isset( $data["{$this->pre}_field_ids"] ) ? $data["{$this->pre}_field_ids"] : '';
		$field_ids = json_decode( $field_ids_json );
		if( ! is_array( $field_ids ) ) {
			$field_ids = array();
		}

		$out = '<div class="themify-info-link">'. sprintf( __( 'Use <a href="%s">Hook Content</a> to add content to the theme without editing any template file.', 'themify' ), 'http://themify.me/docs/hook-content' ) .'</div>';

		$out .= '<ul id="hook-content-list">';
			if( ! empty( $field_ids ) ) : foreach( $field_ids as $key => $value ) :
				$out .= $this->item_template( $value );
			endforeach; endif;
		$out .= '</ul>';
		$out .= '<p class="add-link themify-add-hook alignleft"><a href="#">' . __( 'Add item', 'themify' ) . '</a></p>';
		$out .= '<a class="button button-secondary see-hook-locations alignright" href="'. add_query_arg( array( 'tp' => 1 ), home_url() ) .'">'. __( 'See Hook Locations', 'themify' ) .'</a>';
		$out .= '<input type="hidden" id="themify-hooks-field-ids" name="' . esc_attr( $this->pre . '_field_ids' ) . '" value=\'' . json_encode( $field_ids ) . '\' />';
		return $out;
	}

	function ajax_add_button() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		if( isset( $_POST['field_id'] ) ) {
			echo $this->item_template( $_POST['field_id'] );
		}

		die;
	}

	function item_template( $id ) {
		$output = '<li class="social-link-item" data-id="' . esc_attr( $id ) . '">';
			$output .= '<div class="social-drag">'. esc_html__( 'Drag to Sort &#8597;', 'themify' ) .'</div>';
			$output .= '<div class="row"><select name="' . esc_attr( $this->pre . '-' . $id . '-location' ) . '" class="width6">';
			$locations = $this->get_locations();
			foreach( $this->get_location_groups() as $group => $label ) {
				if( ! empty( $locations[$group] ) ) {
					$output .= '<optgroup label="' . esc_attr( $label ) . '">';
					foreach( $locations[$group] as $key => $value ) {
						$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( themify_get( "{$this->pre}-{$id}-location" ), $key, false ) . '>' . esc_html( $value ) . '</option>';
					}
					$output .= '</optgroup>';
				}
			}
			$output .= '</select>';
			$output .= '&nbsp; <a class="button button-secondary themify-visibility-toggle" href="#" data-target="#' . $this->pre . '-' . $id . '-visibility"> ' . __( '+ Conditions', 'themify' ) . ' </a> <input type="hidden" id="' . $this->pre . '-' . $id . '-visibility" name="' . esc_attr( $this->pre . '-' . $id . '-visibility' ) . '" value="' . esc_attr( themify_get( $this->pre . '-' . $id . '-visibility' ) ) . '" /></div>';
			$output .= '<div class="row"><textarea class="widthfull" name="' . esc_attr( $this->pre . '-' . $id . '-code' ) . '" rows="6" cols="73">' . esc_html( themify_get( "{$this->pre}-{$id}-code" ) ) . '</textarea>';
			$output .= '<a href="#" class="remove-item"><i class="ti-close"></i></a>';
		$output .= '</li>';
		return $output;
	}

	public function get_visibility_dialog() {
		$output = '
			<div id="themify_lightbox_visibility" class="themify-admin-lightbox clearfix" style="display: none;">
				<h3 class="themify_lightbox_title">' . __( 'Condition', 'themify' ) . '</h3>
				<a href="#" class="close_lightbox"><i class="ti-close"></i></a>
				<div class="lightbox_container">
				</div>
				<a href="#" class="button uncheck-all">'. __( 'Uncheck All', 'themify' ) .'</a>
				<a href="#" class="button button-primary visibility-save alignright">' . __( 'Save', 'themify' ) . '</a>
			</div>
			<div id="themify_lightbox_overlay"></div>
		';

		return $output;
	}

	public function visibility_dialog() {
		global $hook_suffix;

		if( 'toplevel_page_themify' == $hook_suffix ) {
			echo $this->get_visibility_dialog();
		}
	}

	function exclude_attachments_from_visibility( $post_types ) {
		unset( $post_types['attachment'] );
		return $post_types;
	}

	function ajax_get_visibility_options() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		$selected = array();
		if( isset( $_POST['selected'] ) ) {
			parse_str( $_POST['selected'], $selected );
		}
		echo $this->get_visibility_options( $selected );
		die;
	}

	public function get_visibility_options( $selected = array() ) {
		$post_types = apply_filters( 'themify_hooks_visibility_post_types', get_post_types( array( 'public' => true ) ) );
		unset( $post_types['page'] );
		$post_types = array_map( 'get_post_type_object', $post_types );

		$taxonomies = apply_filters( 'themofy_hooks_visibility_taxonomies', get_taxonomies( array( 'public' => true ) ) );
		unset( $taxonomies['category'] );
		$taxonomies = array_map( 'get_taxonomy', $taxonomies );

		$output = '<form id="visibility-tabs" class="ui-tabs"><ul class="clearfix">';

		/* build the tab links */
		$output .= '<li><a href="#visibility-tab-general">' . __( 'General', 'themify' ) . '</a></li>';
		$output .= '<li><a href="#visibility-tab-pages">' . __( 'Pages', 'themify' ) . '</a></li>';
                $output .= '<li><a href="#visibility-tab-categories-singles">' . __( 'Category Singles', 'themify' ) . '</a></li>';
		$output .= '<li><a href="#visibility-tab-categories">' . __( 'Categories', 'themify' ) . '</a></li>';
		$output .= '<li><a href="#visibility-tab-post-types">' . __( 'Post Types', 'themify' ) . '</a></li>';
		$output .= '<li><a href="#visibility-tab-taxonomies">' . __( 'Taxonomies', 'themify' ) . '</a></li>';
		$output .= '<li><a href="#visibility-tab-userroles">' . __( 'User Roles', 'themify' ) . '</a></li>';
		$output .= '</ul>';

		/* build the tab items */
		$output .= '<div id="visibility-tab-general" class="themify-visibility-options clearfix">';
			$checked = isset( $selected['general']['home'] ) ? checked( $selected['general']['home'], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="general[home]" '. $checked .' />' . __( 'Home page', 'themify' ) . '</label>';
			$checked = isset( $selected['general']['page'] ) ? checked( $selected['general']['page'], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="general[page]" '. $checked .' />' . __( 'Page views', 'themify' ) . '</label>';
			$checked = isset( $selected['general']['single'] ) ? checked( $selected['general']['single'], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="general[single]" '. $checked .' />' . __( 'Single post views', 'themify' ) . '</label>';
			$checked = isset( $selected['general']['search'] ) ? checked( $selected['general']['search'], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="general[search]" '. $checked .' />' . __( 'Search pages', 'themify' ) . '</label>';
			$checked = isset( $selected['general']['category'] ) ? checked( $selected['general']['category'], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="general[category]" '. $checked .' />' . __( 'Category archive', 'themify' ) . '</label>';
			$checked = isset( $selected['general']['tag'] ) ? checked( $selected['general']['tag'], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="general[tag]" '. $checked .' />' . __( 'Tag archive', 'themify' ) . '</label>';
			$checked = isset( $selected['general']['author'] ) ? checked( $selected['general']['author'], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="general[author]" '. $checked .' />' . __( 'Author pages', 'themify' ) . '</label>';

			/* General views for CPT */
			foreach( get_post_types( array( 'public' => true, 'exclude_from_search' => false, '_builtin' => false ) ) as $key => $post_type ) {
				$post_type = get_post_type_object( $key );
				$checked = isset( $selected['general'][$key] ) ? checked( $selected['general'][$key], 'on', false ) : '';
				$output .= '<label><input type="checkbox" name="' . esc_attr( 'general[' . $key . ']' ) . '" '. $checked .' />' . sprintf( __( 'Single %s View', 'themify' ), $post_type->labels->singular_name ) . '</label>';
			}

			/* Custom taxonomies archive view */
			foreach( get_taxonomies( array( 'public' => true, '_builtin' => false ) ) as $key => $tax ) {
				$tax = get_taxonomy( $key );
				$checked = isset( $selected['general'][$key] ) ? checked( $selected['general'][$key], 'on', false ) : '';
				$output .= '<label><input type="checkbox" name="' . esc_attr( 'general[' . $key . ']' ) . '" '. $checked .' />' . sprintf( __( '%s Archive View', 'themify' ), $tax->labels->singular_name ) . '</label>';
			}

		$output .= '</div>'; // tab-general

		// Pages tab
		$output .= '<div id="visibility-tab-pages" class="themify-visibility-options clearfix">';
			$key = 'page';
			$posts = get_posts( array( 'post_type' => $key, 'posts_per_page' => -1, 'status' => 'published' ) );
			if( ! empty( $posts ) ) : foreach( $posts as $post ) :
				$checked = isset( $selected['post_type'][$key][$post->post_name] ) ? checked( $selected['post_type'][$key][$post->post_name], 'on', false ) : '';
				/* note: slugs are more reliable than IDs, they stay unique after export/import */
				$output .= '<label><input type="checkbox" name="' . esc_attr( 'post_type[' . $key . '][' . $post->post_name . ']' ) . '" ' . $checked . ' />' . esc_html( $post->post_title ) . '</label>';
			endforeach; endif;
		$output .= '</div>'; // tab-pages

                 // Category Singles tab
                $output .= '<div id="visibility-tab-categories-singles" class="themify-visibility-options clearfix">';
			$key = 'category_single';
			$terms = get_terms( 'category', array( 'hide_empty' => true ) );
			if( ! empty( $terms ) ) :
                            foreach( $terms as $term ) :
                                    $checked = isset( $selected['tax'][$key][$term->slug] ) ? checked( $selected['tax'][$key][$term->slug], 'on', false ) : '';
                                    $output .= '<label><input type="checkbox" name="tax['. $key .']['. $term->slug .']" '. $checked .' />' . $term->name . '</label>';
                            endforeach;
                        endif;
		$output .= '</div>';

		// Categories tab
		$output .= '<div id="visibility-tab-categories" class="themify-visibility-options clearfix">';
			$key = 'category';
			if( ! empty( $terms ) ) :
                            foreach( $terms as $term ) :
                                    $checked = isset( $selected['tax'][$key][$term->slug] ) ? checked( $selected['tax'][$key][$term->slug], 'on', false ) : '';
                                    $output .= '<label><input type="checkbox" name="' . esc_attr( 'tax[' . $key . '][' . $term->slug . ']' ) . '" ' . $checked . ' />' . esc_html( $term->name ) . '</label>';
                            endforeach;
                        endif;
		$output .= '</div>'; // tab-categories

		// Post types tab
		$output .= '<div id="visibility-tab-post-types" class="themify-visibility-options clearfix">';
			$output .= '<div id="themify-visibility-post-types-inner-tabs" class="themify-visibility-inner-tabs">';
			$output .= '<ul class="inline-tabs clearfix">';
				foreach( $post_types as $key => $post_type ) {
					$output .= '<li><a href="#visibility-tab-' . $key . '">' . esc_html( $post_type->label ) . '</a></li>';
				}
			$output .= '</ul>';
			foreach( $post_types as $key => $post_type ) {
				$output .= '<div id="visibility-tab-' . $key . '" class="clearfix">';
				$posts = get_posts( array( 'post_type' => $key, 'posts_per_page' => -1, 'status' => 'published' ) );
				if( ! empty( $posts ) ) : foreach( $posts as $post ) :
					$checked = isset( $selected['post_type'][$key][$post->post_name] ) ? checked( $selected['post_type'][$key][$post->post_name], 'on', false ) : '';
					/* note: slugs are more reliable than IDs, they stay unique after export/import */
					$output .= '<label><input type="checkbox" name="' . esc_attr( 'post_type[' . $key . ']['. $post->post_name .']' ) . '" '. $checked . ' />' . esc_html( $post->post_title ) . '</label>';
				endforeach; endif;
				$output .= '</div>';
			}
			$output .= '</div>';
		$output .= '</div>'; // tab-post-types

		// Taxonomies tab
		$output .= '<div id="visibility-tab-taxonomies" class="themify-visibility-options clearfix">';
			$output .= '<div id="themify-visibility-taxonomies-inner-tabs" class="themify-visibility-inner-tabs">';
			$output .= '<ul class="inline-tabs clearfix">';
				foreach( $taxonomies as $key => $tax ) {
					$output .= '<li><a href="#visibility-tab-' . $key . '">' . esc_html( $tax->label ) . '</a></li>';
				}
			$output .= '</ul>';
			foreach( $taxonomies as $key => $tax ) {
				$output .= '<div id="visibility-tab-'. $key .'" class="clearfix">';
				$terms = get_terms( $key, array( 'hide_empty' => true ) );
				if( ! empty( $terms ) ) : foreach( $terms as $term ) :
					$checked = isset( $selected['tax'][$key][$term->slug] ) ? checked( $selected['tax'][$key][$term->slug], 'on', false ) : '';
					$output .= '<label><input type="checkbox" name="' . esc_attr( 'tax[' . $key . '][' . $term->slug . ']' ) . '" ' . $checked . ' />' . esc_html( $term->name ) . '</label>';
				endforeach; endif;
				$output .= '</div>';
			}
			$output .= '</div>';
		$output .= '</div>'; // tab-taxonomies

		// User Roles tab
		$output .= '<div id="visibility-tab-userroles" class="themify-visibility-options clearfix">';
		foreach( $GLOBALS['wp_roles']->roles as $key => $role ) {
			$checked = isset( $selected['roles'][$key] ) ? checked( $selected['roles'][$key], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="' . esc_attr( 'roles['. $key .']' ) . '" '. $checked .' />' . esc_html( $role['name'] ) . '</label>';
		}
		$output .= '</div>'; // tab-userroles

		$output .= '</form>';
		return $output;
	}

	function hook_locations_view_setup() {
		if( isset( $_GET['tp'] ) && $_GET['tp'] == 1 ) {
			show_admin_bar( false );

			/* enqueue url fix script */
			wp_enqueue_script( 'hook-locations-urlfix', THEMIFY_URI . '/js/hook-locations-urlfix.js', array( 'jquery' ), false, false );

			foreach( $this->get_locations( true ) as $location => $label ) {
				add_action( $location, array( $this, 'print_hook_label' ) );
			}
		}
	}

	function print_hook_label() {
		$hook = current_filter();
		$locations = $this->get_locations( true );
		echo '<div class="hook-location-hint">' . esc_html( $locations[ $hook ] ) . '</div>';
	}
}
$GLOBALS['themify_hooks'] = new Themify_Hooks();
