<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Gallery Lightboxed
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

extract( $settings, EXTR_SKIP );

$alt = isset( $gallery_images[0]->post_excerpt ) ? $gallery_images[0]->post_excerpt : '';

/* if no thumbnail is set for the gallery, use the first image */
if( ! isset( $thumbnail_gallery ) ) {
	$thumbnail_gallery = wp_get_attachment_url( $gallery_images[0]->ID );
}
$thumbnail = themify_get_image( "ignore=true&src={$thumbnail_gallery}&w={$thumb_w_gallery}&h={$thumb_h_gallery}&alt={$alt}" );

	foreach ( $gallery_images as $key => $image ): ?>
		<dl class="gallery-item" style="<?php echo 0 == $key ? '' : 'display: none;'; ?>">
			<?php
			$link = wp_get_attachment_url( $image->ID );

			$img = wp_get_attachment_image_src( $image->ID, 'full' );

			if ( ! empty( $link ) ) {
				echo '<dt class="gallery-icon"><a href="' . esc_url( $link ) . '" title="' . esc_attr( $image->post_title ) . '">';
			}
			echo wp_kses_post( ( 0 == $key ) ? $thumbnail : $img[1] );
			if ( ! empty( $link ) ) {
				echo '</a></dt>';
			}

			if( $key != 0 && isset( $image->post_excerpt ) && '' != $image->post_excerpt ) : ?>
			<dd class="wp-caption-text gallery-caption">
				<?php echo wp_kses_post( $image->post_excerpt ); ?>
			</dd>
			<?php endif; ?>

		</dl>

	<?php endforeach; // end loop ?>
