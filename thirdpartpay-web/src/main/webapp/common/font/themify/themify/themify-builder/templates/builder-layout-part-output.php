<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $ThemifyBuilder;
?>

<div class="themify_builder_content themify_builder_content-<?php echo $builder_id; ?> themify_builder not_editable_builder">

	<?php
	foreach ( $builder_output as $rows => $row ) :
		if ( 0 == count( $row ) ) continue;

		if( ! Themify_Builder_Model::is_frontend_editor_page() ) { // prevent duplicate CSS output
			// output styles for layout parts as inline CSS
			echo $ThemifyBuilder->render_row_styling( $builder_id, $row );
			// Column Styling
			add_action('themify_builder_column_start', array($ThemifyBuilder, 'render_column_styling'), 10, 3);

			// Sub-Column Styling
			add_action('themify_builder_sub_column_start', array($ThemifyBuilder, 'render_sub_column_styling'), 10, 5);
		}
		echo $ThemifyBuilder->get_template_row( $rows, $row, $builder_id, false, false );

	endforeach; // end row loop
	?>

</div>