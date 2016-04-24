<form id="tfb_save_layout_form">

	<div id="themify_builder_lightbox_options_tab_items">
		<li class="title"><?php _e('Save as Layout', 'themify'); ?></li>
	</div>

	<div id="themify_builder_lightbox_actions_items">
		<button id="builder_submit_layout_form" class="builder_button"><?php _e('Save', 'themify') ?></button>
	</div>

	<input type="hidden" name="postid" value="<?php echo esc_attr( $postid ); ?>">
	<div class="themify_builder_options_tab_wrapper">
		<div class="themify_builder_options_tab_content">
			<?php Themify_Builder_Form::render( $fields ); ?>
		</div>
	</div>
</form>