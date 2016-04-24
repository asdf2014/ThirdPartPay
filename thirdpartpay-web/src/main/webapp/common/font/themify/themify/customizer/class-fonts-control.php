<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class to create a select element for web safe fonts and Google Fonts.
 *
 * @since 1.0.0
 */
class Themify_Font_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_font';

	/**
	 * @param WP_Customize_Manager $manager
	 * @param string               $id
	 * @param array                $args
	 * @param array                $options
	 */
	function __construct( $manager, $id, $args = array(), $options = array() ) {
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Display the font control.
	 *
	 * @since 1.0.0
	 */
	function render_content() {
		$v = $this->value();
		$values = json_decode( $v );
		wp_enqueue_script( 'json2' );
		$font_options = isset( $this->font_options ) ? $this->font_options : array();
		?>

		<?php if ( $this->show_label && ! empty( $this->label ) ) : ?>
			<span class="customize-control-title themify-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>

		<?php $this->render_fonts( $values, $font_options ); ?>

		<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
	<?php
	}

}