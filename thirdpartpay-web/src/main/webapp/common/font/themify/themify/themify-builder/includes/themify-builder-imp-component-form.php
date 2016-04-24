<form id="tfb_imp_component_form">

	<div id="themify_builder_lightbox_options_tab_items">
		<li class="title"><?php printf( __( 'Import %s', 'themify' ), $component ); ?></li>
	</div>

	<div id="themify_builder_lightbox_actions_items">
		<button id="builder_submit_import_component_form" class="builder_button"><?php _e('Save', 'themify') ?></button>
	</div>

	<input type="hidden" name="component" value="<?php echo esc_attr( $component ); ?>">
	<div class="themify_builder_options_tab_wrapper">
		<div class="themify_builder_options_tab_content">
			<?php Themify_Builder_Form::render( $fields ); ?>
		</div>
	</div>
</form>
