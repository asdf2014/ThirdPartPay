<script type="text/html" id="tmpl-builder_sub_row">
	<div class="themify_builder_sub_row clearfix gutter-default">
		<div class="themify_builder_sub_row_top">
			<?php themify_builder_grid_lists('sub_row'); ?>
			<ul class="sub_row_action">
				<li><a href="#" data-title="<?php _e('Export', 'themify') ?>" rel="themify-tooltip-bottom"
				       class="themify_builder_export_component" data-component="sub-row">
						<span class="ti-export"></span>
				</a></li>
				<li><a href="#" data-title="<?php _e('Import', 'themify') ?>" rel="themify-tooltip-bottom"
					   class="themify_builder_import_component" data-component="sub-row">
						<span class="ti-import"></span>
				</a></li>
				<li class="separator"></li>
				<li><a href="#" data-title="<?php _e('Copy', 'themify') ?>" rel="themify-tooltip-bottom"
				       class="themify_builder_copy_component" data-component="sub-row">
						<span class="ti-files"></span>
					</a></li>
				<li><a href="#" data-title="<?php _e('Paste', 'themify') ?>" rel="themify-tooltip-bottom"
				       class="themify_builder_paste_component" data-component="sub-row">
						<span class="ti-clipboard"></span>
					</a></li>
				<li class="separator"></li>
				<li><a href="#" class="sub_row_duplicate" rel="themify-tooltip-bottom"
				       data-title="<?php _e('Duplicate', 'themify') ?>">
						<span class="ti-layers"></span>
				</a></li>
				<li><a href="#" class="sub_row_delete" rel="themify-tooltip-bottom"
				       data-title="<?php _e('Delete', 'themify') ?>">
						<span class="ti-close"></span>
				</a></li>
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
				<li><a href="#" class="themify_builder_export_component ti-export" rel="themify-tooltip-bottom"
				       data-component="module" data-title="<?php _e('Export', 'themify') ?>">
						<?php _e('Export', 'themify') ?>
				</a></li>
				<li><a href="#" class="themify_builder_import_component ti-import" rel="themify-tooltip-bottom"
				       data-component="module" data-title="<?php _e('Import', 'themify') ?>">
						<?php _e('Import', 'themify') ?>
				</a></li>
				<li class="separator"><div></div></li>
				<li><a href="#" class="themify_builder_copy_component ti-files" rel="themify-tooltip-bottom"
				       data-component="module" data-title="<?php _e('Copy', 'themify') ?>">
						<?php _e('Copy', 'themify') ?>
				</a></li>
				<li><a href="#" class="themify_builder_paste_component ti-clipboard" rel="themify-tooltip-bottom"
				       data-component="module" data-title="<?php _e('Paste', 'themify') ?>">
						<?php _e('Paste', 'themify') ?>
				</a></li>
				<li class="separator"><div></div></li>
				<li><a href="#" class="themify_module_options" rel="themify-tooltip-bottom"
				       data-module-name="{{ data.slug }}">
							<?php _e('Edit', 'themify') ?>
				</a></li>
				<li><a href="#" data-title="<?php _e('Duplicate', 'themify') ?>" class="themify_module_duplicate"
				       rel="themify-tooltip-bottom">
						<?php _e('Duplicate', 'themify') ?>
				</a></li>
				<li><a href="#" data-title="<?php _e('Delete', 'themify') ?>" class="themify_module_delete"
				       rel="themify-tooltip-bottom">
						<?php _e('Delete', 'themify') ?>
				</a></li>
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

					<?php themify_builder_grid_lists(); ?>

					<ul class="row_action">
						<li><a href="#" data-title="<?php _e('Export', 'themify') ?>" class="themify_builder_export_component"
								data-component="row" rel="themify-tooltip-bottom">
									<span class="ti-export"></span>
						</a></li>
						<li><a href="#" data-title="<?php _e('Import', 'themify') ?>" class="themify_builder_import_component"
								data-component="row" rel="themify-tooltip-bottom">
									<span class="ti-import"></span>
						</a></li>
						<li class="separator"></li>
						<li><a href="#" data-title="<?php _e('Copy', 'themify') ?>" class="themify_builder_copy_component"
								data-component="row" rel="themify-tooltip-bottom">
									<span class="ti-files"></span>
						</a></li>
						<li><a href="#" data-title="<?php _e('Paste', 'themify') ?>" class="themify_builder_paste_component"
								data-component="row" rel="themify-tooltip-bottom">
									<span class="ti-clipboard"></span>
						</a></li>
						<li class="separator"></li>
						<li><a href="#" data-title="<?php _e('Options', 'themify') ?>" class="themify_builder_option_row"
								rel="themify-tooltip-bottom">
									<span class="ti-pencil"></span>
						</a></li>
                                                <li><a href="#" data-title="<?php _e('Styling', 'themify') ?>" class="themify_builder_style_row"
                                                    rel="themify-tooltip-bottom">
                                                     <span class="ti-brush"></span>
                                                </a></li>
						<li><a href="#" data-title="<?php _e('Duplicate', 'themify') ?>" class="themify_builder_duplicate_row"
								rel="themify-tooltip-bottom">
									<span class="ti-layers"></span>
						</a></li>
						<li><a href="#" data-title="<?php _e('Delete', 'themify') ?>" class="themify_builder_delete_row"
								rel="themify-tooltip-bottom">
									<span class="ti-close"></span>
						</a></li>
						<li class="separator"></li>
						<li><a href="#" data-title="<?php _e('Toggle Row', 'themify') ?>" class="themify_builder_toggle_row">
							<span class="ti-angle-up"></span>
						</a></li>
					</ul>
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