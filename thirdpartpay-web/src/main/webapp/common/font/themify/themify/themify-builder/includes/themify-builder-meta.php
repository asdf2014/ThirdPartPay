<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Builder Main Meta Box HTML
 */
?>

<div class="themify_builder themify_builder_admin clearfix">

	<div class="themify_builder_module_panel clearfix">
		
		<?php foreach( Themify_Builder_Model::$modules as $module ): ?>
		<?php $class = "themify_builder_module module-type-{$module->slug}"; ?>

		<div class="<?php echo esc_attr($class); ?>" data-module-slug="<?php echo esc_attr( $module->slug ); ?>" data-module-name="<?php echo esc_attr( $module->name ); ?>">
			<strong class="module_name"><?php echo esc_html( $module->name ); ?></strong>
			<a href="#" class="add_module" data-module-name="<?php echo esc_attr( $module->slug ); ?>"><?php _e('Add', 'themify') ?></a>
		</div>
		<!-- /module -->
		<?php endforeach; ?>
	</div>
        <div id="themify_builder_module_tmp"></div>
	<!-- /themify_builder_module_panel -->

	<div class="themify_builder_row_panel clearfix">

		<div id="themify_builder_row_wrapper" class="themify_builder_row_js_wrapper themify_builder_editor_wrapper">

		<?php if( ! empty( $builder_data ) && is_array( $builder_data ) ): ?>
		<!-- from database -->
		<?php foreach( $builder_data as $rows => $row ):
			$row_gutter_class = isset( $row['gutter'] ) && ! empty( $row['gutter'] ) ? $row['gutter'] : 'gutter-default';
			$row_column_equal_height = isset( $row['equal_column_height'] ) && ! empty( $row['equal_column_height'] ) ? $row['equal_column_height'] : '';
		?>
		<div data-gutter="<?php echo esc_attr( $row_gutter_class ); ?>" class="themify_builder_row clearfix <?php echo esc_attr( $row_column_equal_height ); ?>"
			 data-equal-column-height="<?php echo esc_attr( $row_column_equal_height ); ?>">

			<div class="themify_builder_row_top">
				<div class="row_menu">
					<div class="menu_icon">
					</div>
					<ul class="themify_builder_dropdown">
						<li><a href="#" class="themify_builder_option_row"><?php _e('Options', 'themify') ?></a></li>
						<li><a href="#" class="themify_builder_duplicate_row"><?php _e('Duplicate', 'themify') ?></a></li>
						<li><a href="#" class="themify_builder_delete_row"><?php _e('Delete', 'themify') ?></a></li>
					</ul>
				</div>
				<!-- /row_menu -->
				<?php themify_builder_grid_lists( 'row', $row_gutter_class, $row_column_equal_height ); ?>
				<div class="toggle_row"></div><!-- /toggle_row -->
			</div>
			<!-- /row_top -->

			<div class="themify_builder_row_content">
				
				<?php if ( isset( $row['cols'] ) ) : ?>
					<?php foreach ( $row['cols'] as $cols => $col ): ?>
					<div class="themify_builder_col <?php echo esc_attr( $col['grid_class'] ); ?>">
						<div class="themify_builder_column_styling_icon ti-brush themify_builder_option_column"></div>
						<div class="themify_module_holder">
							<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->

							<?php if ( isset( $col['modules'] ) && count( $col['modules'] ) > 0 ): ?>
							<?php foreach ( $col['modules'] as $mod ): ?>
							
							<?php if ( isset( $mod['mod_name'] ) ): ?>
							<div class="themify_builder_module module-<?php echo esc_attr( $mod['mod_name'] ); ?> active_module" data-mod-name="<?php echo esc_attr( $mod['mod_name'] ); ?>">
								<div class="module_menu">
									<div class="menu_icon">
									</div>
									<ul class="themify_builder_dropdown">
										<li><a href="#" class="themify_module_options" data-module-name="<?php echo esc_attr( $mod['mod_name'] ); ?>"><?php _e('Edit', 'themify') ?></a></li>
										<li><a href="#" class="themify_module_duplicate"><?php _e('Duplicate', 'themify') ?></a></li>
										<li><a href="#" class="themify_module_delete"><?php _e('Delete', 'themify') ?></a></li>
									</ul>
								</div>
								<div class="module_label">
									<strong class="module_name"><?php echo Themify_Builder_Model::get_module_name( $mod['mod_name'] ); ?></strong>
									<em class="module_excerpt"><?php echo isset( Themify_Builder_Model::$modules[ $mod['mod_name'] ] ) ? wp_trim_words( Themify_Builder_Model::$modules[ $mod['mod_name'] ]->get_title( $mod ), 10, '...' ) : ''; ?></em>
								</div>
								<div class="themify_module_settings">
									<script type="text/json"><?php echo json_encode( $mod['mod_settings'] ); ?></script>
								</div>
							</div>
							<!-- /active_module -->
							<?php elseif( isset( $mod['cols'] ) && count( $mod['cols'] ) > 0 ):
								$sub_row_gutter = isset( $mod['gutter'] ) && ! empty( $mod['gutter'] ) ? $mod['gutter'] : 'gutter-default';
								$sub_row_column_equal_height = isset( $mod['equal_column_height'] ) &&
															   ! empty( $mod['equal_column_height'] ) ? $mod['equal_column_height'] : '';
							?>
								<div data-gutter="<?php echo esc_attr( $sub_row_gutter ); ?>" class="themify_builder_sub_row clearfix <?php echo esc_attr( $sub_row_column_equal_height ); ?>"
								     data-equal-column-height="<?php echo esc_attr( $sub_row_column_equal_height ); ?>">
									<div class="themify_builder_sub_row_top">
										<?php themify_builder_grid_lists('sub_row', $sub_row_gutter, $sub_row_column_equal_height ); ?>
										<ul class="sub_row_action">
											<li><a href="#" class="sub_row_duplicate"><span class="ti-layers"></span></a></li>
											<li><a href="#" class="sub_row_delete"><span class="ti-close"></span></a></li>
										</ul>
									</div>
									<!-- /row_top -->
									<div class="themify_builder_sub_row_content">
										<?php foreach( $mod['cols'] as $sub_row => $sub_col ): ?>
										<div class="themify_builder_col <?php echo esc_attr( $sub_col['grid_class'] ); ?>">
											<div class="themify_builder_column_styling_icon ti-brush themify_builder_option_column"></div>
											<div class="themify_module_holder">
												<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
												<?php if ( isset( $sub_col['modules'] ) && count( $sub_col['modules'] ) > 0 ):
													foreach( $sub_col['modules'] as $sub_module ): ?>
													<div class="themify_builder_module module-<?php echo esc_attr( $sub_module['mod_name'] ); ?> active_module" data-mod-name="<?php echo esc_attr( $sub_module['mod_name'] ); ?>">
														<div class="module_menu">
															<div class="menu_icon">
															</div>
															<ul class="themify_builder_dropdown">
																<li><a href="#" class="themify_module_options" data-module-name="<?php echo esc_attr( $sub_module['mod_name'] ); ?>"><?php _e('Edit', 'themify') ?></a></li>
																<li><a href="#" class="themify_module_duplicate"><?php _e('Duplicate', 'themify') ?></a></li>
																<li><a href="#" class="themify_module_delete"><?php _e('Delete', 'themify') ?></a></li>
															</ul>
														</div>
														<div class="module_label">
															<strong class="module_name"><?php echo Themify_Builder_Model::get_module_name( $sub_module['mod_name'] ); ?></strong>
															<em class="module_excerpt"><?php echo isset( Themify_Builder_Model::$modules[ $sub_module['mod_name'] ] ) ? wp_trim_words( Themify_Builder_Model::$modules[ $sub_module['mod_name'] ]->get_title( $sub_module ), 10, '...' ) : ''; ?></em>
														</div>
														<div class="themify_module_settings">
															<script type="text/json"><?php echo json_encode( $sub_module['mod_settings'] ); ?></script>
														</div>
													</div>
													<!-- /active_module -->
												<?php endforeach; endif; ?>
											</div>
											<?php $sub_column_data_styling = isset( $sub_col['styling'] ) ? json_encode( $sub_col['styling'] ) : json_encode( array() ); ?>
											<div class="column-data-styling" data-styling="<?php echo esc_attr( $sub_column_data_styling ); ?>"></div>
										</div>
										<?php endforeach; ?>
									</div>
									<!-- /sub_row_content -->
								</div>
							<?php endif; ?>

							<?php endforeach; // end modules loop ?>
							<?php endif; // end modules count check ?>

						</div>
						<?php $column_data_styling = isset( $col['styling'] ) ? json_encode( $col['styling'] ) : json_encode( array() ); ?>
						<div class="column-data-styling" data-styling="<?php echo esc_attr( $column_data_styling ); ?>"></div>
						<!-- /module_holder -->
					</div>
					<!-- /builder_col -->
					<?php endforeach; // end col loop ?>
				<?php else: ?>
				<div class="themify_builder_col col-full first last">
					<div class="themify_module_holder">
						<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div>
					</div>
				</div>
				<?php endif; // isset row cols ?>

			</div> <!-- /themify_builder_row_content -->
			<?php $row_data_styling = isset( $row['styling'] ) && is_array( $row['styling'] ) ? json_encode( $row['styling'] ) : json_encode( array() ); ?>
			<div class="row-data-styling" data-styling="<?php echo esc_attr( $row_data_styling ); ?>"></div>
		</div>
		<!-- /builder_row -->
		<?php endforeach; // end rows loop ?>

		<!-- /from database -->
		<?php else: ?>

		<div class="themify_builder_row clearfix">
			
			<div class="themify_builder_row_top">
				<div class="row_menu">
					<div class="menu_icon">
					</div>
					<ul class="themify_builder_dropdown">
						<li><a href="#" class="themify_builder_option_row"><?php _e('Options', 'themify') ?></a></li>
						<li><a href="#" class="themify_builder_duplicate_row"><?php _e('Duplicate', 'themify') ?></a></li>
						<li><a href="#" class="themify_builder_delete_row"><?php _e('Delete', 'themify') ?></a></li>
					</ul>
				</div>
				<!-- /row_menu -->
				<?php themify_builder_grid_lists( 'row' ); ?>
				<div class="toggle_row"></div><!-- /toggle_row -->
			</div>
			<!-- /row_top -->

			<div class="themify_builder_row_content">
				<div class="themify_builder_col col4-1 first">
					<div class="themify_module_holder">
						<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
					</div>
					<!-- /module_holder -->
				</div>
				<!-- /builder_col -->

				<div class="themify_builder_col col4-1">
					<div class="themify_module_holder">
						<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
					</div>
					<!-- /module_holder -->
				</div>
				<!-- /builder_col -->

				<div class="themify_builder_col col4-1">
					<div class="themify_module_holder">
						<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
					</div>
					<!-- /module_holder -->
				</div>
				<!-- /builder_col -->

				<div class="themify_builder_col col4-1 last">
					<div class="themify_module_holder">
						<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
					</div>
					<!-- /module_holder -->
				</div>
				<!-- /builder_col -->
			</div> <!-- /themify_builder_row_content -->

			<div class="row-data-styling" data-styling=""></div>
		</div>
		<!-- /builder_row -->
		<?php endif; // end count data rows ?>

		</div> <!-- /#themify_builder_row_wrapper -->
		<?php if($post->post_status!='auto-draft'):?>
                    <div class="themify_builder_save">
                            <ul class="themify_builder_backend_options_menu left">
                                    <li>
                                            <div class="ti ti-menu"></div>
                                            <ul>
                                                    <li><a href="#" id="themify_builder_duplicate"><?php _e('Duplicate this page', 'themify') ?></a></li>
                                                    <li class="has-children">
                                                            <a href="#"><?php esc_html_e( 'Import From', 'themify' );?></a>
                                                            <ul>
                                                                    <li><a href="#" class="themify_builder_import_page"><?php esc_html_e( 'Existing Pages', 'themify' );?></a></li>
                                                                    <li><a href="#" class="themify_builder_import_post"><?php esc_html_e( 'Existing Posts', 'themify' );?></a></li>
                                                            </ul>
                                                    </li>
                                                    <li class="has-children">
                                                            <a href="#"><?php esc_html_e( 'Import / Export', 'themify' );?></a>
                                                            <ul>
                                                                    <li><a href="#" class="themify_builder_import_file"><?php esc_html_e( 'Import', 'themify' );?></a></li>
                                                                    <li><a href="<?php echo wp_nonce_url('?themify_builder_export_file=true&postid=' . $post->ID, 'themify_builder_export_nonce') ?>"><?php esc_html_e( 'Export', 'themify' );?></a></li>
                                                            </ul>
                                                    </li>
                                                    <li class="has-children">
                                                            <a href="#"><?php esc_html_e( 'Layouts', 'themify' );?></a>
                                                            <ul>
                                                                    <li><a href="#" class="themify_builder_load_layout"><?php esc_html_e( 'Load Layout', 'themify' );?></a></li>
                                                                    <li><a href="#" class="themify_builder_save_layout"><?php esc_html_e( 'Save as Layout', 'themify' );?></a></li>
                                                            </ul>
                                                    </li>
                                            </ul>
                                    </li>
                                    <li class="separator"></li>
                                    <li>
                                            <span class="themify_builder_undo_tools">
                                                    <a class="themify-builder-undo-btn js-themify-builder-undo-btn" rel="themify-tooltip-top" data-title="<?php esc_html_e( 'Undo', 'themify' );?>"><i class="ti-back-left"></i></a>
                                                    <a class="themify-builder-redo-btn js-themify-builder-redo-btn" rel="themify-tooltip-top" data-title="<?php esc_html_e( 'Redo', 'themify' );?>"><i class="ti-back-right"></i></a>
                                            </span>
                                    </li>
                            </ul>
                            <?php if ( isset( $pagenow ) && $pagenow !== 'post-new.php' ): ?>
                            <a href="#" id="themify_builder_switch_frontend" class="themify_builder_switch_frontend"><?php _e('Switch to frontend', 'themify') ?></a>
                            <a href="#" id="themify_builder_main_save" class="builder_button"><?php _e('Save', 'themify') ?></a>
                            <?php endif; ?>
                    </div>
                <?php endif;?>

	</div>
	<!-- /themify_builder_row_panel -->

	<div style="display: none;">
		<?php
			wp_editor( ' ', 'tfb_lb_hidden_editor' );
		?>
	</div>

</div>
<!-- /themify_builder -->