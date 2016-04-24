<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class to create a control that wipes all the styling settings and refreshes live preview.
 *
 * @since 1.0.0
 */
class Themify_Clear_Control extends WP_Customize_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_clear';

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {
		?>

		<span class="customize-control-title themify-control-title">
			<a href="#" class="clearall" data-sitename="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-tagline="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>">
				<span class="ti-close clearall-icon"></span>
				<?php echo esc_html( $this->label ); ?>
			</a>
		</span>

		<input <?php $this->link(); ?> value="" type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control"/>
		<?php
	}
}