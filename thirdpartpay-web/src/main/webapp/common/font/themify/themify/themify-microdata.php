<?php
/**
 * Adds Schema.org Microdata Support
 * Adds Organization fields in User Profile Page
 * @since 2.6.6
 * @return json
 */

class Themify_Microdata {

	var $output = array();

	function __construct() {
		add_action( 'themify_body_start', array( $this, 'schema_markup_homepage' ) );
		add_action( 'themify_content_start', array( $this, 'schema_markup_page' ) );
		add_action( 'themify_post_start', array( $this, 'schema_markup_post' ) );
		add_action( 'themify_body_end', array( $this, 'display_schema_markup' ) );
		if( is_admin() ) {
			add_action( 'show_user_profile', array( $this, 'user_meta_org_field' ) );
			add_action( 'edit_user_profile', array( $this, 'user_meta_org_field' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_profile_scripts' ) );
			add_action( 'personal_options_update', array( $this, 'save_user_schema_publisher_field' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_user_schema_publisher_field' ) );
		}
		if ( themify_is_woocommerce_active() ) {
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'schema_markup_wc_product' ) );
		}
	}

	function schema_markup_homepage() {
		// Homepage
		if ( is_home() || is_front_page() && ! is_paged() ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_inactive( 'wordpress-seo/wp-seo.php' ) ) {
				$current_page_url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
				$microdata = array(
					"@context" => "http://schema.org",
					"@type" => "WebSite",
					"url" => esc_url( $current_page_url ),
					"potentialAction" => array(
						"@type" => "SearchAction",
						"target" => esc_url( $current_page_url ) .'?&s={search_term_string}',
						"query-input" => "required name=search_term_string"
					)
				);
				$this->output[] = $microdata;
			}
		}
	}

	// Pages
	function schema_markup_page() {
		global $post;

		if( ! isset( $post ) ) {
			return;
		}

		$post_title 		= get_the_title();
		$date_added 		= get_the_time('c');
		$date_modified 		= get_the_time('c');
		$permalink			= get_permalink();
		$excerpt			= $post->post_excerpt;
		$comments			= get_comments(array('post_id' => $post->ID));
		$comment_count		= get_comments_number($post->ID);
		$post_image 		= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size = 'large' );
		$current_page_url	= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$author				= get_the_author();
		$author_description	= get_the_author_meta('description');
		$author_url			= get_the_author_meta('user_url');
		$author_avatar		= get_avatar_url( get_the_author_meta('user_email') );
		$author_avatar_data	= get_avatar_data( get_the_author_meta('user_email') );

		if ( is_attachment() && is_single() ) {
			$post_schema_type = 'CreativeWork';
		} elseif ( is_author() ) {
			$post_schema_type = 'ProfilePage';
		} elseif ( is_search() ) {
			$post_schema_type = 'SearchResultsPage';
		} elseif ( themify_is_woocommerce_active() && is_shop() ) {
			$post_schema_type = 'Store';
		} elseif ( themify_is_woocommerce_active() && is_product() ) {
			$post_schema_type = 'Product';
		} elseif ( is_page() ) {
			$post_schema_type = 'WebPage';
		}

		// Page
		if( is_page() && ! post_password_required() ) {
	       if( ! ( themify_is_woocommerce_active() && is_shop() ) ) {
				$microdata = array(
					"@context" => "http://schema.org",
					"@type" => $post_schema_type,
					"mainEntityOfPage" => array(
						"@type" => "WebPage",
						"@id" => $permalink,
					),
					"headline" => $post_title,
					"datePublished" => $date_added,
					"dateModified" => $date_modified,
					"description" => $excerpt,
					"commentCount" => $comment_count
				);
				if( has_post_thumbnail() ) {
					$microdata['image'] = array(
						"@type" => "ImageObject",
						"url" => $post_image[0],
						"width" => $post_image[1],
						"height" => $post_image[2]
					);
				}
				if ( $comment_count > 0 ) {
					foreach ( $comments as $comment ) {
						$microdata['comment'][] = array(
							"@type" => "Comment",
							"author" => array(
								"@type" => "Person",
								"name" => $comment->comment_author
							),
							"text" => $comment->comment_content
						);
					}
				}
				$this->output[] = $microdata;
			}
		}

		// Profile Page
		if ( is_author() ) {
			$microdata = array(
				"@context" => "http://schema.org",
				"@type" => $post_schema_type,
				"mainEntityOfPage" => array(
					"@type" => "WebPage",
					"@id" => $current_page_url,
				),
				"author" => array(
					"@type" => 'Person',
					"name" => $author
				),
				"image" => array(
					"@type" => "ImageObject",
					"url" => $author_avatar,
					"width" => $author_avatar_data['width'],
					"height" => $author_avatar_data['height']
				),
				"description" => $author_description,
				"url" => $author_url
			);
			$this->output[] = $microdata;
		}

		// Search Page
		if ( is_search() ) {
			$microdata = array(
				"@context" => "http://schema.org",
				"@type" => $post_schema_type,
				"mainEntityOfPage" => array(
					"@type" => "WebPage",
					"@id" => $current_page_url,
				)
			);
			$this->output[] = $microdata;
		}

		// Shop Page
		if ( themify_is_woocommerce_active() && is_shop() ) {
			$microdata = array(
				"@context" => "http://schema.org",
				"@type" => $post_schema_type,
				"mainEntityOfPage" => array(
					"@type" => "WebPage",
					"@id" => $current_page_url,
				)
			);
			$this->output[] = $microdata;
		}

	}

	// Posts
	function schema_markup_post() {
		global $post, $themify;

		$post_title 	= get_the_title();
		$date_added 	= get_the_time('c');
		$date_modified 	= get_the_time('c');
		$permalink		= get_permalink();
		$author			= get_the_author();
		$excerpt		= get_the_excerpt();
		$publisher_logo = get_the_author_meta('user_meta_org_logo');
		$publisher_name = get_the_author_meta('user_meta_org_name');
		$logo_width 	= get_the_author_meta('user_meta_org_logo_width'); settype($logo_width,"integer");
		$logo_height 	= get_the_author_meta('user_meta_org_logo_height'); settype($logo_height,"integer");
		$comments		= get_comments( array('post_id' => $post->ID) );
		$comment_count	= get_comments_number($post->ID);
		$post_types		= array( 'post', 'press' );
		$creative_types	= array( 'audio', 'highlight', 'quote', 'portfolio', 'testimonial', 'video' );
		$post_image 	= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size = 'large' );
		// Cases
		if ( is_singular('post') ) {
			$post_schema_type = 'BlogPosting';
		} elseif ( in_array( $post->post_type, $creative_types ) ) {
			$post_schema_type = 'CreativeWork';
		} elseif ( $post->post_type == 'team' ) {
			$post_schema_type = 'Person';
		} elseif ( $post->post_type == 'event' ) {
			$post_schema_type = 'Event';
		} elseif ( $post->post_type == 'gallery' ) {
			$post_schema_type = 'ImageGallery';
		} elseif ( $post->post_type == 'press' ) {
			$post_schema_type = 'NewsArticle';
		} else {
			$post_schema_type = 'Article';
		}

		// Post
		if ( in_array( $post->post_type, $post_types ) && ! post_password_required() ) {
			$microdata = array(
				"@context" => 'http://schema.org',
				"@type" => $post_schema_type,
				"mainEntityOfPage" => array(
					"@type" => "WebPage",
					"@id" => $permalink
				),
				"headline" => $post_title,
				"datePublished" => $date_added,
				"dateModified" => $date_modified,
				"author" => array(
					"@type" => 'Person',
					"name" => $author
				),
				"publisher" => array(
					"@type" => "Organization",
					"name" => $publisher_name,
					"logo" => array(
						"@type" => "ImageObject",
						"url" => $publisher_logo,
						"width" => $logo_width,
						"height" => $logo_height
					),
				),
				"description" => $excerpt,
				"commentCount" => $comment_count
			);
			if ( has_post_thumbnail() ) {
				$microdata['image'] = array(
					"@type" => "ImageObject",
					"url" => $post_image[0],
					"width" => $post_image[1],
					"height" => $post_image[2]
				);
			}
			if ( is_single() && $comment_count > 0 ) {
				foreach ( $comments as $comment ) {
					$microdata['comment'][] = array(
						"@type" => "Comment",
						"author" => array(
							"@type" => "Person",
							"name" => $comment->comment_author
						),
						"text" => $comment->comment_content
					);
				}
			}
			$this->output[] = $microdata;
		}

		// Event
		if ( $post->post_type == 'event' && ! post_password_required() ) {
			global $themify_event;
			$microdata = array(
				"@context" => 'http://schema.org',
				"@type" => $post_schema_type,
				"mainEntityOfPage" => array(
					"@type" => "WebPage",
					"@id" => $permalink
				),
				"name" => $post_title,
				"description" => $excerpt,
				"startDate" => themify_get( 'start_date' ),
				"location" => array(
					"@type" => "Place",
					"name" => themify_get( 'location' ),
					"address" => themify_get( 'map_address' )
				)
			);
			if ( themify_check( 'buy_tickets' ) ) {
				$microdata['offers'] = array(
					"@type" => "Offer",
					// "price" => "",
					"url" => themify_get( 'buy_tickets' )
				);
			}
			if( has_post_thumbnail() ) {
				$microdata['image'] = array(
					"@type" => "ImageObject",
					"url" => $post_image[0],
					"width" => $post_image[1],
					"height" => $post_image[2]
				);
			}
			$this->output[] = $microdata;
		}

		// Gallery
		if ( $post->post_type == 'gallery' && ! post_password_required() ) {
			$microdata = array(
				"@context" => 'http://schema.org',
				"@type" => $post_schema_type,
				"mainEntityOfPage" => array(
					"@type" => "WebPage",
					"@id" => $permalink
				),
				"headline" => $post_title,
				"datePublished" => $date_added,
				"dateModified" => $date_modified,
				"author" => array(
					"@type" => 'Person',
					"name" => $author
				),
				"publisher" => array(
					"@type" => "Organization",
					"name" => $publisher_name,
					"logo" => array(
						"@type" => "ImageObject",
						"url" => $publisher_logo,
						"width" => $logo_width,
						"height" => $logo_height
					),
				),
				"description" => $excerpt,
				"commentCount" => $comment_count
			);
			if ( has_post_thumbnail() ) {
				$microdata['image'] = array(
					"@type" => "ImageObject",
					"url" => $post_image[0],
					"width" => $post_image[1],
					"height" => $post_image[2]
				);
			}
			if ( is_single() && $comment_count > 0 ) {
				foreach ( $comments as $comment ) {
					$microdata['comment'][] = array(
						"@type" => "Comment",
						"author" => array(
							"@type" => "Person",
							"name" => $comment->comment_author
						),
						"text" => $comment->comment_content
					);
				}
			}
			$this->output[] = $microdata;
		}

		// Audio, Highlight, Quote, Portfolio, Testimonial, Video
		if ( in_array( $post->post_type, $creative_types ) && ! post_password_required() ) {
			$microdata = array(
				"@context" => 'http://schema.org',
				"@type" => $post_schema_type,
				"mainEntityOfPage" => array(
					"@type" => "WebPage",
					"@id" => $permalink
				),
				"headline" => $post_title,
				"datePublished" => $date_added,
				"dateModified" => $date_modified,
				"description" => $excerpt,
				"commentCount" => $comment_count
			);
			if( has_post_thumbnail() ) {
				$microdata['image'] = array(
					"@type" => "ImageObject",
					"url" => $post_image[0],
					"width" => $post_image[1],
					"height" => $post_image[2]
				);
			}
			if ( $post->post_type == 'post' && is_single() ) {
				if ( $comment_count > 0 ) {
					foreach ( $comments as $comment ) {
						$microdata['comment'][] = array(
							"@type" => "Comment",
							"author" => array(
								"@type" => "Person",
								"name" => $comment->comment_author
							),
							"text" => $comment->comment_content
						);
					}
				}
			}
			if ( themify_get( 'video_url' ) != '' ) {
				$post_video = themify_get('video_url');
				$video_meta = $this->fetch_video_meta( $post_video );
				if( $video_meta ) {
					$microdata['video'] = array(
						"@type" => "VideoObject",
						"url" => $post_video
					);
					if( isset( $video_meta->thumbnail_url ) ) {
						$microdata['video']['thumbnailUrl'] = $video_meta->thumbnail_url;
					}
					if( isset( $video_meta->upload_date ) ) {
						$microdata['video']['uploadDate'] = $video_meta->upload_date;
					} else {
						$microdata['video']['uploadDate'] = $date_added;
					}
					if( isset( $video_meta->description ) ) {
						$microdata['video']['description'] = $video_meta->description;
					} else {
						$microdata['video']['description'] = $excerpt;
					}
					if( isset( $video_meta->title ) ) {
						$microdata['video']['name'] = $video_meta->title;
					} else {
						$microdata['video']['name'] = $post_title;
					}
				}
			}
			$this->output[] = $microdata;
		}

		// Team
		if ( $post->post_type == 'team' && ! post_password_required() ) {
			$microdata = array(
				"@context" => 'http://schema.org',
				"@type" => $post_schema_type,
				"mainEntityOfPage" => array(
					"@type" => "WebPage",
					"@id" => $permalink
				),
				"name" => $post_title,
				"description" => $excerpt
			);
			if( has_post_thumbnail() ) {
				$microdata['image'] = array(
					"@type" => "ImageObject",
					"url" => $post_image[0],
					"width" => $post_image[1],
					"height" => $post_image[2]
				);
			}
			$this->output[] = $microdata;
		}

	}

	// WooCommerce Products
	function schema_markup_wc_product() {
		if( themify_is_woocommerce_active() ) {
			global $post, $product;

			$post_title 	= get_the_title();
			$permalink		= get_permalink();
			$excerpt		= wp_strip_all_tags(get_the_excerpt());
			$post_image 	= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size = 'large' );
			$price 			= $product->get_price();
			$currency		= apply_filters( 'woocommerce_currency', get_option('woocommerce_currency') );

			// Product
			if ( !is_singular('product') && ! post_password_required() ) {
				// Output only for product loops, not single product.
				// Single product metadata added by WooCommerce.
				$microdata = array(
					"@context" => 'http://schema.org',
					"@type" => "Product",
					"name" => $post_title,
					"description" => $excerpt,
					"offers" => array(
						"@type" => "Offer",
						"price" => $price,
						"priceCurrency" => $currency,
						"availability" => "http://schema.org/InStock"
					)
				);
				if( has_post_thumbnail() ) {
					$microdata['image'] = array(
						"@type" => "ImageObject",
						"url" => $post_image[0],
						"width" => $post_image[1],
						"height" => $post_image[2]
					);
				}
				$this->output[] = $microdata;
			}
		}
	}

	// Output Schema.org JSON-LD
	function display_schema_markup() {
		$this->output = apply_filters( 'themify_microdata', $this->output );
		if( ! empty( $this->output ) ) {
			echo '<!-- SCHEMA BEGIN --><script type="application/ld+json">';
			echo json_encode( $this->output );
			echo '</script><!-- /SCHEMA END -->';
		}
	}

	function user_meta_org_field( $user ) {
		$user_org_name = get_the_author_meta( 'user_meta_org_name', $user->ID );
		$user_org_logo = get_the_author_meta( 'user_meta_org_logo', $user->ID );
		$user_org_logo_width = get_the_author_meta( 'user_meta_org_logo_width', $user->ID );
		$user_org_logo_height = get_the_author_meta( 'user_meta_org_logo_height', $user->ID );
	?>
	    <h3><?php _e( 'Organization', 'themify' ); ?></h3>
		<p><?php printf( __( 'These fields are required to fully comply with <a href="%s" name="Schema.org Markup" target="_blank">Rich Snippets</a> standards.', 'themify' ), 'https://developers.google.com/structured-data/rich-snippets/articles' ); ?></p>
	    <table class="form-table">
			<tr>
	            <th><label for="user_organization_name"><?php _e( 'Organization Name', 'themify' ); ?></label></th>
	            <td><input type="text" name="user_meta_org_name" id="'user_meta_org_name" value="<?php echo esc_html( $user_org_name ); ?>" class="regular-text" /></td>
			</tr>
	        <tr>
	            <th><label for="user_meta_org_logo"><?php _e( 'Organization Logo', 'themify' ); ?></label></th>
	            <td>
					<input type="hidden" name="user_meta_org_logo" id="user_meta_org_logo" value="<?php echo esc_url_raw( $user_org_logo ); ?>">
					<input type="hidden" name="user_meta_org_logo_width" id="user_meta_org_logo_width" value="<?php echo esc_html( $user_org_logo_width ); ?>">
					<input type="hidden" name="user_meta_org_logo_height" id="user_meta_org_logo_height" value="<?php echo esc_html( $user_org_logo_height ); ?>">
	                <input type="button" class="button-primary" value="<?php _e( 'Upload Image', 'themify' ); ?>" id="upload-org-logo">
					<?php if (!empty($user_org_logo)) { ?>
						<input type="button" class="button-primary" value="<?php _e( 'Remove Image', 'themify' ); ?>" id="remove-org-logo">
						<img id="user_meta_org_placeholder" src="<?php echo esc_url( $user_org_logo ); ?>" style="max-width: 600px; height: auto; margin-top: 1em; display: block;">
					<?php } else { ?>
						<img id="user_meta_org_placeholder" src="//placehold.it/600x60" style="max-width: 600px; height: auto; margin-top: 1em; display: block;">
					<?php } ?>
	                <p class="description">
						<?php _e( 'Organizaition Logo should be no wider than 600px, and no taller than 60px.', 'themify' ); ?>
					</p>

	            </td>
	        </tr>

	    </table><!-- end form-table -->
	<?php }

	function enqueue_admin_profile_scripts(){
		global $pagenow;
		if ( in_array($pagenow, array('profile.php', 'user-edit.php')) ) {
			wp_enqueue_media();
			wp_register_script( 'themify-profile-meta', THEMIFY_URI . '/js/themify-profile-meta.js', array('jquery'), THEMIFY_VERSION, true );
			wp_enqueue_script('themify-profile-meta');
		}
	}

	/**
	* Saves additional user fields to the database
	*/
	function save_user_schema_publisher_field( $user_id ) {

	    // only saves if the current user can edit user profiles
	    if ( !current_user_can( 'edit_user', $user_id ) )
	        return false;

	    update_user_meta( $user_id, 'user_meta_org_name', $_POST['user_meta_org_name'] );
		update_user_meta( $user_id, 'user_meta_org_logo', $_POST['user_meta_org_logo'] );
		update_user_meta( $user_id, 'user_meta_org_logo_width', $_POST['user_meta_org_logo_width'] );
		update_user_meta( $user_id, 'user_meta_org_logo_height', $_POST['user_meta_org_logo_height'] );
	}

	function fetch_video_meta( $video_url ) {
		$image_url = '';

		if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match ) ) {
			$request = wp_remote_get( "http://www.youtube.com/oembed?url=". urlencode( $video_url ) ."&format=json" );
		} elseif ( false !== stripos( $video_url, 'vimeo' ) ) {
			$request = wp_remote_get( 'http://vimeo.com/api/oembed.json?url='.urlencode( $video_url ) );
		} elseif( false !== stripos( $video_url, 'funnyordie' ) ) {
			$request = wp_remote_get( 'http://www.funnyordie.com/oembed.json?url='.urlencode( $video_url ) );
		} elseif( false !== stripos( $video_url, 'dailymotion' ) ) {
			$video_id = parse_url( $video_url, PHP_URL_PATH );
			$request = wp_remote_get( 'https://api.dailymotion.com/' . str_replace( '/embed/', '', $video_id ) . '?fields=thumbnail_large_url', array( 'sslverify' => false ) );
		} elseif( false !== stripos( $video_url, 'blip' ) ) {
			$request = wp_remote_get( 'http://blip.tv/oembed?url=' . $video_url, array( 'sslverify' => false ) );
		}

		if ( ! is_wp_error( $request ) ) {
			$response_body = wp_remote_retrieve_body( $request );
			if ( '' != $response_body ) {
				$video = json_decode( $response_body );
				return $video;
			}
		}

		return false;
	}
}
$GLOBALS['themify_microdata'] = new Themify_Microdata;

/**
 * Deprecated functions
 * Keep these for backward compatibility
 */
if ( ! function_exists( 'themify_get_author_link' ) ) {
	function themify_get_author_link( ) {}
}

if ( ! function_exists( 'themify_display_publisher_microdata' ) ) {
	function themify_display_publisher_microdata( ) {}
}

if ( ! function_exists( 'themify_display_date_microdata' ) ) {
	function themify_display_date_microdata( ) {}
}

if ( ! function_exists( 'themify_schema_markup' ) ) {
	function themify_schema_markup( $args ) {}
}

if ( ! function_exists( 'themify_get_html_schema' ) ) {
	function themify_get_html_schema() {}
}
