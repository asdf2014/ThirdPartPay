<form id="themify_builder_import_form" method="POST">

	<div id="themify_builder_lightbox_options_tab_items">
		<li class="title"><?php _e( 'Import From', 'themify' ); ?></li>
	</div>

	<div id="themify_builder_lightbox_actions_items">
	</div>

	<div class="themify_builder_options_tab_wrapper">
		<div class="themify_builder_options_tab_content">
			<?php foreach( $data as $field ): ?>
			<div class="themify_builder_field">
				<div class="themify_builder_label"><?php echo esc_html( $field['label'] ); ?></div>
				<div class="themify_builder_input">
					<select name="<?php echo esc_attr( $field['post_type'] ); ?>">
						<option value=""></option>
						<?php foreach( $field['items'] as $option ): ?>
						<option value="<?php echo esc_attr( $option->ID ); ?>"><?php echo esc_html( $option->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<?php endforeach; ?>

			<button id="builder_submit_import_form" class="builder_button"><?php _e('Import', 'themify') ?></button>
		</div>
	</div>

</form>
