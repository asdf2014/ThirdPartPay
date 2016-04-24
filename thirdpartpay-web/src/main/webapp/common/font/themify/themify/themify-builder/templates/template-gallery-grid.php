<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Gallery Grid
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

extract( $settings, EXTR_SKIP );
?>

	<?php
	$i = 0;
	foreach ( $gallery_images as $image ):
	?>
		<dl class="gallery-item">
			<dt class="gallery-icon">
				<?php
				if ( $link_opt == 'file' ) {
					$link = wp_get_attachment_url( $image->ID );
				} elseif ( 'none' == $link_opt ) {
					$link = '';
				} else{
					$link = get_attachment_link( $image->ID );
				}
				$link_before = '' != $link ? sprintf( '<a title="%s" href="%s">', esc_attr( $image->post_title ), esc_url( $link ) ) : '';
				$link_before = apply_filters( 'themify_builder_image_link_before', $link_before, $image, $settings );
				$link_after = '' != $link ? '</a>' : '';
				if( $this->is_img_php_disabled() ) {
					$img = wp_get_attachment_image( $image->ID, $image_size_gallery );
				} else {
					$img = wp_get_attachment_image_src( $image->ID, $image_size_gallery );
					$img = themify_get_image( "ignore=true&src={$img[0]}&w={$thumb_w_gallery}&h={$thumb_h_gallery}" );
				}

				echo !empty( $img ) ? $link_before . $img . $link_after : '';
				?>
			</dt>

			<?php if( isset( $image->post_excerpt ) && '' != $image->post_excerpt ) : ?>
			<dd class="wp-caption-text gallery-caption">
				<?php echo wp_kses_post( $image->post_excerpt ); ?>
			</dd>
			<?php endif; ?>

		</dl>

		<?php if ( $columns > 0 && ++$i % $columns == 0 ): ?>
		<br style="clear: both" />
		<?php endif; ?>

	<?php endforeach; // end loop ?>
	<br style="clear: both" />