<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="themify_builder_content-<?php echo esc_attr( $builder_id ); ?>" data-postid="<?php echo esc_attr( $builder_id ); ?>" class="themify_builder_content themify_builder_content-<?php echo esc_attr( $builder_id ); ?> themify_builder themify_builder_front">

	<?php
		foreach ( $builder_output as $key => $row ) {
			if ( 0 == count( $row ) ) continue;
			$this->get_template_row( $key, $row, $builder_id, true );
		} // end row loop
	?>
</div>
<!-- /themify_builder_content -->