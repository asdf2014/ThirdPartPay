<form id="tfb_exp_component_form">

	<div id="themify_builder_lightbox_options_tab_items">
		<li class="title"><?php printf( __( 'Export %s', 'themify' ), $component ); ?></li>
	</div>

	<input type="hidden" name="component" value="<?php echo esc_attr( $component ); ?>">
	<div class="themify_builder_options_tab_wrapper">
		<div class="themify_builder_options_tab_content">
			<?php Themify_Builder_Form::render( $fields ); ?>
		</div>
	</div>
</form>
