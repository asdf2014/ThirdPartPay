<?php
/**
 * Routines for generation of custom image sizes and deletion of these sizes.
 *
 * @since 1.9.0
 * @package themify
 */

if ( ! function_exists( 'themify_do_img' ) ) {
	/**
	 * Resize images dynamically using wp built in functions
	 *
	 * @param string|int $image Image URL or an attachment ID
	 * @param int $width
	 * @param int $height
	 * @param bool $crop
	 * @return array
	 */
	function themify_do_img( $image = null, $width, $height, $crop = false ) {
		// if an attachment ID has been sent
		if( is_int( $image ) ) {
			$post = get_post( $image );
			if( $post ) {
				$attachment_id = $post->ID;
				$img_url = wp_get_attachment_url( $attachment_id );
			}
		} else {
			// URL has been passed to the function
			$img_url = esc_url( $image );

			$upload_dir = wp_upload_dir();
			$base_url = $upload_dir['baseurl'];

			// Check if the image is an attachment. If it's external return url, width and height.
			if ( substr( $img_url, -strlen( $base_url ) ) === $base_url ) {
				return array(
					'url' => $img_url,
					'width' => $width,
					'height' => $height,
				);
			}

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = themify_get_attachment_id_from_url( $img_url, $base_url );
		}

		// If no relationship between a post and a image size was found, return url, width and height.
		if ( ! $attachment_id ) {
			return array(
				'url' => $img_url,
				'width' => $width,
				'height' => $height,
			);
		}
		
		// Fetch attachment meta data. Up to this point we know the attachment ID is valid.
		$meta = wp_get_attachment_metadata( $attachment_id );

		// Go through the attachment meta data sizes looking for an image size match.
		if ( is_array( $meta ) && isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
			// Perform calculations when height = 0
			if ( is_null( $height ) || 0 === $height || '0' === $height ) {
				// If width and height or original image are available as metadata
				if ( isset( $meta['width'] ) && isset( $meta['height'] ) ) {
					// Divide width by original image aspect ratio to obtain projected height
					// The floor function is used so it returns an int and metadata can be written
					$height = floor( $width / ( $meta['width'] / $meta['height'] ) );
				} else {
					$height = 0;
				}
			}
			foreach( $meta['sizes'] as $key => $size ) {
				if ( $size['width'] == $width && $size['height'] == $height ) {
					setlocale( LC_CTYPE, get_locale() . '.UTF-8' );
					$split_url = explode( '/', $img_url );
					$split_url[ count( $split_url ) - 1 ] = $size['file'];
					return array(
						'url' => implode( '/', $split_url ),
						'width' => $width,
						'height' => $height,
						'attachment_id' => $attachment_id,
					);
				}
			}
		} elseif ( is_array( $meta ) && ( ! isset( $meta['sizes'] ) || ! is_array( $meta['sizes'] ) ) ) {
			// If the meta is an array, but doesn't have the 'sizes' element or if it has it, it's not an array,
			// return original image since there's something wrong with this attachment meta data: it exists,
			// but its format is not correct.
			return array(
				'url' => $img_url,
				'width' => $width,
				'height' => $height,
				'attachment_id' => $attachment_id,
			);
		}

		// Requested image size doesn't exists, so let's create one
		if ( true == $crop ) {
			add_filter( 'image_resize_dimensions', 'themify_img_resize_dimensions', 10, 5 );
		}
		// Patch meta because if we're here, there's a valid attachment ID for sure, but maybe the meta data is not ok.
		if ( ! is_array( $meta ) && empty( $meta ) ) {
			$meta['sizes'] = array( 'large' => array() );
		}
		// Generate image returning an array with image url, width and height. If image can't generated, original url, width and height are used.
		$image = themify_make_image_size( $attachment_id, $width, $height, $meta, $img_url );
		
		if ( true == $crop ) {
			remove_filter( 'image_resize_dimensions', 'themify_img_resize_dimensions', 10 );
		}
		$image['attachment_id'] = $attachment_id;

		return $image;
	}
}

if ( ! function_exists( 'themify_make_image_size' ) ) {
	/**
	 * Creates new image size.
	 *
	 * @uses get_attached_file()
	 * @uses image_make_intermediate_size()
	 * @uses wp_update_attachment_metadata()
	 * @uses get_post_meta()
	 * @uses update_post_meta()
	 *
	 * @param int $attachment_id
	 * @param int $width
	 * @param int $height
	 * @param array $meta
	 * @param string $img_url
	 *
	 * @return array
	 */
	function themify_make_image_size( $attachment_id, $width, $height, $meta, $img_url ) {
		setlocale( LC_CTYPE, get_locale() . '.UTF-8' );
		$attached_file = get_attached_file( $attachment_id );

		$base_size = 'full';
		/* is Large image size big enough that can be used for resizing the image? */
		if(
			isset( $meta['sizes']['large'] )
			&& $meta['sizes']['large']['width'] > $width
			&& $meta['sizes']['large']['height'] > $height
		) {
			$base_size = 'large';
		}

		if ( isset( $meta['sizes'][$base_size]['file'] ) ) {
			$attached_file = str_replace( $meta['file'], trailingslashit( dirname( $meta['file'] ) ) . $meta['sizes'][$base_size]['file'], $attached_file );
		}

		$resized = image_make_intermediate_size( $attached_file, $width, $height, true );
		if ( $resized && ! is_wp_error( $resized ) ) {

			// Save the new size in meta data
			$key = sprintf( 'resized-%dx%d', $width, $height );
			$meta['sizes'][$key] = $resized;
			$img_url = str_replace( basename( $img_url ), $resized['file'], $img_url );

			wp_update_attachment_metadata( $attachment_id, $meta );

			// Save size in backup sizes so it's deleted when original attachment is deleted.
			$backup_sizes = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );
			if ( ! is_array( $backup_sizes ) ) $backup_sizes = array();
			$backup_sizes[$key] = $resized;
			update_post_meta( $attachment_id, '_wp_attachment_backup_sizes', $backup_sizes );

			// Return resized image url, width and height.
			return array(
				'url' => esc_url( $img_url ),
				'width' => $width,
				'height' => $height,
			);
		}
		// Return original image url, width and height.
		return array(
			'url' => $img_url,
			'width' => $width,
			'height' => $height,
		);
	}
}

/**
 * Disable the min commands to choose the minimum dimension, thus enabling image enlarging.
 *
 * @param $default
 * @param $orig_w
 * @param $orig_h
 * @param $dest_w
 * @param $dest_h
 * @return array
 */
function themify_img_resize_dimensions( $default, $orig_w, $orig_h, $dest_w, $dest_h ) {
	// set portion of the original image that we can size to $dest_w x $dest_h
	$aspect_ratio = $orig_w / $orig_h;
	$new_w = $dest_w;
	$new_h = $dest_h;

	if ( !$new_w ) {
		$new_w = intval( $new_h * $aspect_ratio );
	}

	if ( !$new_h ) {
		$new_h = intval( $new_w / $aspect_ratio );
	}

	$size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );

	$crop_w = round( $new_w / $size_ratio );
	$crop_h = round( $new_h / $size_ratio );

	$s_x = floor( ( $orig_w - $crop_w ) / 2 );
	$s_y = floor( ( $orig_h - $crop_h ) / 2 );

	// the return array matches the parameters to imagecopyresampled()
	// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
	return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
}

/**
 * Get attachment ID for image from its url.
 *
 * @param string $url
 * @param string $base_url
 * @return bool|null|string
 */
function themify_get_attachment_id_from_url( $url = '', $base_url = '' ) {
	// If this is the URL of an auto-generated thumbnail, get the URL of the original image
	$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', str_replace( $base_url . '/', '', $url ) );

	// Finally, run a custom database query to get the attachment ID from the modified attachment URL
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $url ) );
}