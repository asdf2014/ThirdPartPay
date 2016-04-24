<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="themify_builder_content-<?php echo esc_attr( $builder_id ); ?> themify_builder<?php if ( $this->in_the_loop ) echo ' in_the_loop not_editable_builder'; ?>">

<?php
	foreach ( $builder_output as $key => $row ) {
		if ( 0 == count( $row ) ) continue;
		$this->get_template_row( $key, $row, $builder_id, true, false );
	} // end row loop
?>
</div>