<div class="themify_builder_options_tab_wrapper">
	<div class="themify_builder_options_tab_content">
		<form id="themify_builder_load_template_form" method="POST">

			<div id="themify_builder_lightbox_options_tab_items">
				<li class="title"><?php _e('Layouts', 'themify'); ?></li>
			</div>

			<div id="themify_builder_lightbox_actions_items">
			</div>

			<p><?php _e('Builder Layouts are the pre-designed layouts which you can apply to any page for quicker prototyping. Click on the thumbnail to apply.', 'themify') ?></p>

			<?php if ( count( $layouts ) > 0 ): ?>
			<div id="themify_builder_options_styling">
				<div class="themify_builder_tabs">
					<ul class="themify_builder_tab clearfix">
						<li class="title"><a href="#themify_builder_tabs_pre-designed"><?php _e( 'Pre-designed', 'themify' ); ?></a></li>
						<li class="title"><a href="#themify_builder_tabs_custom"><?php _e( 'Custom', 'themify' ); ?></a></li>
					</ul>
					<div id="themify_builder_tabs_pre-designed" class="themify_builder_tab">
						<ul class="themify_builder_layout_lists">

							<?php foreach( $layouts as $layout ):
								if( 1 == $layout['prebuilt'] ):
							?>
							<li class="layout_preview_list">
								<div class="layout_preview" data-layout-slug="<?php echo esc_attr( $layout['slug'] ); ?>" data-builtin="<?php echo $layout['prebuilt'] == true ? 'yes' : 'no' ?>">
									<div class="thumbnail"><?php echo $layout['thumbnail']; ?></div>
									<!-- /thumbnail -->
									<div class="layout_action">
										<div class="layout_title"><?php echo $layout['title']; ?></div>
										<!-- /template_title -->
									</div>
									<!-- /template_action -->
								</div>
								<!-- /template_preview -->
							</li>
							<?php endif;
							endforeach; ?>
						</ul>
					</div>
					<div id="themify_builder_tabs_custom" class="themify_builder_tab">
						<ul class="themify_builder_layout_lists">

							<?php foreach( $layouts as $layout ):
								if( "" == $layout['prebuilt'] ):
							?>
							<li class="layout_preview_list">
								<div class="layout_preview" data-layout-slug="<?php echo esc_attr( $layout['slug'] ); ?>" data-builtin="<?php echo $layout['prebuilt'] == true ? 'yes' : 'no' ?>">
									<div class="thumbnail"><?php echo $layout['thumbnail']; ?></div>
									<!-- /thumbnail -->
									<div class="layout_action">
										<div class="layout_title"><?php echo $layout['title']; ?></div>
										<!-- /template_title -->
									</div>
									<!-- /template_action -->
								</div>
								<!-- /template_preview -->
							</li>
							<?php endif;
							endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
			<?php endif; ?>
			<div class="clearfix"></div>


			<a href="<?php echo admin_url('post-new.php?post_type=' . $this->layout->post_type_name); ?>" target="_blank"
			   class="add_new">
				<span class="themify_builder_icon add"></span>
				<?php _e('Add new layout', 'themify') ?>
			</a>
			<a href="<?php echo admin_url('edit.php?post_type=' . $this->layout->post_type_name); ?>" target="_blank"
			   class="add_new">
				<span class="themify_builder_icon ti-folder"></span>
				<?php _e('Manage Layouts', 'themify') ?>
			</a>

		</form>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	// tabular options
	$('.themify_builder_tabs').tabs();
});
</script>