<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Gallery Showcase
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

extract( $settings, EXTR_SKIP );

if ( ! empty( $gallery_images ) ) :
	$first_image = '';
        $disable =  $this->is_img_php_disabled();
        if(!$disable){
            $s_image_size_gallery = 'full';
        }
	if ( is_array( $gallery_images ) ) {
		if ( is_object( $gallery_images[0] ) ) {
                    $first_image = wp_get_attachment_image_src( $gallery_images[0]->ID,$s_image_size_gallery);
                    $alt = get_post_meta($gallery_images[0]->ID, '_wp_attachment_image_alt', true);
                    $first_image = !$disable?themify_get_image( "ignore=true&src={$first_image[0]}&w={$s_image_w_gallery}&h={$s_image_h_gallery}&urlonly=1" ):$first_image[0];
		}
	}
	?>

	<div class="gallery-showcase-image">
           <img src="<?php echo esc_url( $first_image); ?>" alt="<?php esc_attr_e($alt)?>" />
	</div>

	<div class="gallery-images">

		<?php
		$i = 0;
		foreach ( $gallery_images as $image ):
			$link = wp_get_attachment_image_src( $image->ID,$s_image_size_gallery );
                        $link = $link[0];
			if( $disable ) {
				$img = wp_get_attachment_image( $image->ID, $image_size_gallery );
			} else {
				$img = wp_get_attachment_image_src( $image->ID, 'full' );
				$img = themify_get_image( "ignore=true&src={$img[0]}&w={$thumb_w_gallery}&h={$thumb_h_gallery}" );
                                $link = themify_get_image( "ignore=true&src={$link}&w={$s_image_w_gallery}&h={$s_image_h_gallery}&urlonly=1" );
			}

			if ( ! empty( $link ) ) {
				echo '<a data-image="' . esc_url( $link ) . '" title="' . esc_attr( $image->post_title ) . '" href="#">';
			}
			echo wp_kses_post( $img );
			if ( ! empty( $link ) ) {
				echo '</a>';
			}

		endforeach; // end loop ?>
	</div>

<?php endif; ?>