<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class to create a control to accept CSS rules and preview them instantly.
 *
 * @since 1.0.0
 */
class Themify_CustomCSS_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_customcss';

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {
		$v = $this->value();

		// Remove JSON stuff
		$v = str_replace( '{"css":"', '', $v );
		$v = str_replace( '"}', '', $v );

		// If it was escaped as a single quote, undo it as an unescaped double quote
		$v = preg_replace( '/\\\'/', '"', $v );
		// Escape square brackets for cases like input[type=text]
		$v = str_replace( array( '[', ']' ), array( '\\[', '\\]' ), $v );
		// Escape backslashes, single and double quotes
		$v = addslashes( $v );
		// Remove double backslashes inside strings, cases like \e456
		$v = preg_replace( '/\:(\s*?)(\"|\')(\\+)(.*?)(\"|\')/', ': $2\\$4$5', $v );

		// Rebuild JSON
		$v = '{"css":"' . $v . '"}';

		$values = json_decode( $v );
		wp_enqueue_script( 'json2' );

		// Custom CSS
		$css = isset( $values->css ) ? $values->css : '';
		$css = preg_replace( '/(\{|\;)(\s*?)([a-z]+)/', '$1$3', $css );
		$css = str_replace( array( '{', '}', ';', '\\[', '\\]' ), array( "{\n  ", "}\n", ";\n", '[', ']' ), $css );
		?>

		<?php if ( $this->show_label && ! empty( $this->label ) ) : ?>
			<span class="customize-control-title themify-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>

		<div class="themify-customizer-brick">
			<a class="themify-expand ti ti-new-window"></a>
			<textarea class="customcss" rows="20"><?php echo esc_textarea( $css ); ?></textarea>
		</div>

		<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
		<?php
	}
}