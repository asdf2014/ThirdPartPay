<script type="text/html" id="tmpl-builder_column">
	<div class="themify_builder_col {{ data.newclass }}">
		<div class="themify_builder_column_styling_icon ti-brush themify_builder_option_column" title="<?php esc_attr_e( 'Column Styling', 'themify' ); ?>"></div>
		<div class="themify_module_holder">
			<div class="empty_holder_text">{{ data.placeholder }}</div>
		</div>
		<div class="column-data-styling" data-styling=""></div>
	</div>
</script>

<script type="text/html" id="tmpl-builder_grid_menu">
	<?php themify_builder_grid_lists('module'); ?>
</script>

<script type="text/html" id="tmpl-builder_lightbox">
	<div id="themify_builder_lightbox_parent" class="themify_builder themify_builder_admin builder-lightbox clearfix {{ data.is_themify_theme }}">
		<div class="themify_builder_lightbox_top_bar clearfix">
			<ul class="themify_builder_options_tab clearfix">
			</ul>

			<div class="themify_builder_lightbox_actions">
				<a class="builder_cancel_lightbox"><?php _e( 'Cancel', 'themify' ) ?><i class="ti ti-close"></i></a>
			</div>
		</div>
		<div id="themify_builder_lightbox_container"></div>
	</div>
	<div id="themify_builder_overlay"></div>
</script>

<script type="text/html" id="tmpl-builder_lite_lightbox_confirm">
	<p>{{ data.message }}</p>
	<p>
	<# _.each(data.buttons, function(value, key) { #> 
		<button data-type="{{ key }}">{{ value.label }}</button> 
	<# }); #>
	</p>
</script>

<script type="text/html" id="tmpl-builder_lite_lightbox_prompt">
	<p>{{ data.message }}</p>
	<p><input type="text" class="themify_builder_litelightbox_prompt_input"></p>
	<p>
	<# _.each(data.buttons, function(value, key) { #> 
		<button data-type="{{ key }}">{{ value.label }}</button> 
	<# }); #>
	</p>
</script>