<script type="text/html" id="tmpl-builder_sub_row">
	<div class="themify_builder_sub_row clearfix gutter-default">
		<div class="themify_builder_sub_row_top">
			<?php themify_builder_grid_lists('sub_row'); ?>
			<ul class="sub_row_action">
				<li><a href="#" class="sub_row_duplicate"><span class="ti-layers"></span></a></li>
				<li><a href="#" class="sub_row_delete"><span class="ti-close"></span></a></li>
			</ul>
		</div>

		<div class="themify_builder_sub_row_content">
			<div class="themify_builder_col {{ data.newclass }}">
				<div class="themify_builder_column_styling_icon ti-brush themify_builder_option_column" title="<?php esc_attr_e( 'Column Styling', 'themify' ); ?>"></div>
				<div class="themify_module_holder">
					<div class="empty_holder_text">{{ data.placeholder }}</div>
				</div>
				<div class="column-data-styling" data-styling=""></div>
			</div>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-builder_module_item">
	<div class="themify_builder_module {{ data.slug }} active_module" data-mod-name="{{ data.slug }}">
		<div class="module_menu">
			<div class="menu_icon">
			</div>
			<ul class="themify_builder_dropdown" style="display:none;">
				<li><a href="#" class="themify_module_options" data-module-name="{{ data.slug }}"><?php _e('Edit', 'themify') ?></a></li>
				<li><a href="#" class="themify_module_duplicate"><?php _e('Duplicate', 'themify') ?></a></li>
				<li><a href="#" class="themify_module_delete"><?php _e('Delete', 'themify') ?></a></li>
			</ul>
		</div>
		<div class="module_label">
			<strong class="module_name">{{ data.name }}</strong>
			<em class="module_excerpt"></em>
		</div>
		<div class="themify_module_settings"><script type="text/json"></script></div>
	</div>
</script>

<script type="text/html" id="tmpl-builder_row">
	<div class="themify_builder_row module_row clearfix gutter-default">
		<div class="row_inner_wrapper">
			<div class="row_inner">

				<div class="themify_builder_row_top">
					<div class="row_menu">
						<div class="menu_icon"></div>
						<ul class="themify_builder_dropdown">
							<li><a href="#" class="themify_builder_option_row"><?php _e('Options', 'themify') ?></a></li>
							<li><a href="#" class="themify_builder_duplicate_row"><?php _e('Duplicate', 'themify') ?></a></li>
							<li><a href="#" class="themify_builder_delete_row"><?php _e('Delete', 'themify') ?></a></li>
						</ul>
					</div>
					<?php themify_builder_grid_lists(); ?>
					<div class="toggle_row"></div>
				</div>

				<div class="themify_builder_row_content">

					<div class="themify_builder_col col-full first last">
						<div class="themify_builder_column_styling_icon ti-brush themify_builder_option_column" title="<?php esc_attr_e( 'Column Styling', 'themify' ); ?>"></div>
						<div class="themify_module_holder">
							<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div>
						</div>
						<div class="column-data-styling" data-styling=""></div>
					</div>

				</div>

				<div class="row-data-styling" data-styling=""></div>

			</div>
		</div>
	</div>
</script>