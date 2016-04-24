<?php

if( !class_exists( 'Themify_Access_Role' ) ){
	class Themify_Access_Role{

		function __construct(){
			add_filter( 'themify_theme_config_setup', array( $this, 'config_setup' ),14 );
			add_filter( 'themify_customizer_settings', array( $this, 'tf_themify_hide_customizer' ), 99 );
			add_filter( 'themify_builder_is_frontend_editor', array( $this, 'tf_themify_hide_builder_frontend' ), 99 );
			add_filter( 'themify_do_metaboxes', array( $this, 'tf_themify_hide_custom_panel_and_backend_builder' ),99 );
		}
		/**
		 * Renders the options for role access control
		 *
		 * @param array $data
		 * @return string
		 */
		function config_view( $data = array() ){
			global $wp_roles;
		    $roles = $wp_roles->get_names();

		    // Remove the adminitrator and subscriber user role from the array
		    unset( $roles['administrator']);

		    // Remove all the user roles with no "edit_posts" capability
		    foreach( $roles as $role => $slug ) {
		    	$userCapabilities = $wp_roles->roles[$role]['capabilities'];
		    	if( !isset( $userCapabilities['edit_posts'] ) ){
		    		unset( $roles[$role] );
		    	} elseif ( false == $userCapabilities['edit_posts'] ) {
		    		unset( $roles[$role] );
		    	}
		    }

		    // Get the unique setting name
		    $setting = $data['attr']['setting'];

		    // Generate prefix with the setting name
		    $prefix = 'setting-'.$setting.'-';

		    ob_start();
		    ?>
		    <ul>
			<?php foreach( $roles as $role => $slug ) {
				// Get value from the database
				$value = themify_get( $prefix.$role );

				// Check if the user has not saved any setting till now, if so, set the 'default' as value
				$value = ( null !== $value ) ? $value : 'default';
				?>
			   	<li class="role-access-controller">
				   	<!-- Set the column title -->
				   	<div class="role-title">
				   		<?php echo esc_attr( $slug ); ?>
				   	</div>

				   	<!-- Set option to default -->
				   	<div class="role-option role-default">
					   	<input type="radio" id="default-<?php echo esc_attr( $prefix.$role ); ?>" name="<?php echo esc_attr( $prefix.$role ); ?>" value="default" <?php echo checked( $value, 'default', false ); ?>/>
					   	<label for="default-<?php echo esc_attr( $prefix.$role ); ?>"><?php _e( 'Default', 'themify' ); ?></label>
				   	</div>

					<!-- Set option to enable -->
				   	<div class="role-option role-enable">
					   	<input type="radio" id="enable-<?php echo esc_attr( $prefix.$role ); ?>" name="<?php echo esc_attr( $prefix.$role ); ?>" value="enable" <?php echo checked( $value, 'enable', false ); ?>/>
					   	<label for="enable-<?php echo esc_attr( $prefix.$role ); ?>"><?php _e( 'Enable', 'themify' ); ?></label>
				   	</div>

				   	<!-- Set option to disable -->
				   	<div class="role-option role-disable">
					   	<input type="radio" id="disable-<?php echo esc_attr( $prefix.$role ); ?>" name="<?php echo esc_attr( $prefix.$role ); ?>" value="disable" <?php echo checked( $value, 'disable', false ); ?>/>
					   	<label for="disable-<?php echo esc_attr( $prefix.$role ); ?>"><?php _e( 'Disable', 'themify' ); ?></label>
				   	</div>
			   </li>
			<?php }//end foreach ?>
			</ul>
			<?php
			return ob_get_clean();
		}

		/**
		 * Role Access Control
		 * @param array $themify_theme_config
		 * @return array
		 */
		function config_setup( $themify_theme_config ) {
			// Add role acceess control tab on settings page
			$themify_theme_config['panel']['settings']['tab']['role_access'] = array(
				'title' => __('Role Access', 'themify'),
				'id' => 'role_access',
				'custom-module' => array(
					array(
						'title' => __('Themify Custom Panel (In Post/Page Edit)', 'themify'),
						'function' => 'themify_access_role_config_view',
						'setting' => 'custom_panel'
					),
					array(
						'title' => __('Customizer', 'themify'),
						'function' => 'themify_access_role_config_view',
						'setting' => 'customizer'
					),
					array(
						'title' => __('Builder Backend', 'themify'),
						'function' => 'themify_access_role_config_view',
						'setting' => 'backend'
					),
					array(
						'title' => __('Builder Frontend', 'themify'),
						'function' => 'themify_access_role_config_view',
						'setting' => 'frontend'
					)
				)
			);

			return $themify_theme_config;
		}

		// Hide Themify Custom Panel and Backend Builder
		function tf_themify_hide_custom_panel_and_backend_builder( $meta ) {
			if( is_user_logged_in() ){
				$return = $meta;
				// Get current user's properties
				$user = wp_get_current_user();
				// Retrieve user role
			    $userRole = isset( $user->roles[0] ) ? $user->roles[0] : '';

			    // Generate prefix with the setting name for custom panel
			    $prefix = 'setting-custom_panel-';
			    $custom_panel = themify_get( $prefix.$userRole );

			    // Generate prefix with the setting name for backend builder
			    $prefix = 'setting-backend-';
			    $backend_builder = themify_get( $prefix.$userRole );

			    // Remove Page Builde if disabled from role access control
			    if( "disable" == $backend_builder ){
			    	$count = 0;
			    	// Check each meta box for panels
					foreach( $meta as $panel ) {
						// if page builder id found in meta boxes, unset it
						if ( 'page-builder' == $panel['id'] ) {
							unset( $meta[$count] );
						}
						$count++;
					}
			    }

			    // Remove Custom Panel if disabled from role access control
			    if( "disable" == $custom_panel ) {
			    	global $themify_write_panels;
			    	$count = 0;
			    	// Check each meta box for panels
			    	foreach( $meta as $panel ){
			    		// Check if meta box id matches registered panel id
			    		foreach ($themify_write_panels as $key => $write_panel) {
			    			// if registered panel ID found in meta box, unset it
			    			if ( $write_panel['id'] == $panel['id'] ) {
								unset( $meta[$count] );
							}
			    		}
			    		$count++;
			    	}
			    }
			}
			// Return generated meta box settings
			return $meta;
		}

                //check if user has access to builder in backend
                public static function check_access_backend(){
                    if( is_user_logged_in() ){
                        $user = wp_get_current_user();
                        $userRole = isset( $user->roles[0] ) ? $user->roles[0] : '';
                        $prefix = 'setting-backend-';
                        $backend_builder = themify_get( $prefix.$userRole );
                        return "disable" != $backend_builder;
                    }
                    return false;
                }

		// Hide Themify Builder Frontend
		function tf_themify_hide_builder_frontend( $return ) {
			if( is_user_logged_in() ){
				$user = wp_get_current_user();
			    $userRole = isset( $user->roles[0] ) ? $user->roles[0] : '';

			    // Generate prefix with the setting name
			    $prefix = 'setting-frontend-';
			    $value = themify_get( $prefix.$userRole );
				if ( "enable" == $value ) {
					return true;
			    } elseif( "disable" == $value ) {
			        return false;
			    } elseif( current_user_can( 'edit_posts', get_the_ID() ) ){
			    	return $return;
			    }
		   	}
		}

		// Hide Themify Builder Customizer
		function tf_themify_hide_customizer( $data ) {
			if( is_user_logged_in() ){
				$user = wp_get_current_user();
			    $userRole = isset( $user->roles[0] ) ? $user->roles[0] : '';

			    // Generate prefix with the setting name
			    $prefix = 'setting-customizer-';
			    $value = themify_get( $prefix.$userRole );
				// get the the role object
				$editor = get_role($userRole);
				if ( "enable" == $value ) {
					// add $cap capability to this role object
					$editor->add_cap('edit_theme_options');
			    } elseif( "disable" == $value ) {
			        // add $cap capability to this role object
					$editor->remove_cap('edit_theme_options');
			    }
			}

		    return $data;
		}
	}

	$GLOBALS['themify_access_role'] = new Themify_Access_Role();
}
