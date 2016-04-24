<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class to create a control to set the site tagline or remove it.
 *
 * @since 1.0.0
 */
class Themify_Tagline_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_tagline';

	/**
	 * Render the control's content.
	 * @since 1.0.0
	 */
	public function render_content() {
		$v = $this->value();
		$values = json_decode( $v );
		wp_enqueue_script( 'json2' );
		wp_enqueue_media();

		// Mode
		$mode = isset( $values->mode ) ? $values->mode : 'text';
		?>

		<?php if ( $this->show_label && ! empty( $this->label ) ) : ?>
			<span class="customize-control-title themify-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>

		<!-- Tagline Mode Selector -->
		<div class="themify-customizer-brick mode-switcher tagline-modes">
			<label><input type="radio" value="text" class="tagline-mode" name="tagline_mode" <?php checked( $mode, 'text' ); ?> /><?php _e( 'Text', 'themify' ); ?></label>
			<label><input type="radio" value="image" class="tagline-mode" name="tagline_mode" <?php checked( $mode, 'image' ); ?>/><?php _e( 'Image', 'themify' ); ?></label>
			<label><input type="radio" value="none" class="tagline-mode" name="tagline_mode" <?php checked( $mode, 'none' ); ?>/><?php _e( 'None', 'themify' ); ?></label>
		</div>

		<!-- Tagline Text Mode -->
		<div class="tagline-mode-wrap tagline-text-mode">
			<label><?php _e( 'Tagline', 'themify' ); ?><input type="text" class="site-description" value="<?php echo esc_attr( get_bloginfo('description') ); ?>"/></label>
		</div>

		<div class="tagline-mode-wrap tagline-text-mode">
			<?php $this->render_fonts( $values ); ?>
			<div class="themify-customizer-brick">
				<?php $this->render_color( $values, array( 'transparent' => false, 'side_label' => true ) ); ?>
			</div>
		</div>

		<!-- Tagline Image Mode -->
		<div class="tagline-mode-wrap tagline-image-mode">
			<div class="themify-customizer-brick">
				<?php $this->render_image( $values, array( 'show_size_fields' => true ) ); ?>
			</div>
		</div>

		<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
		<?php
	}
}