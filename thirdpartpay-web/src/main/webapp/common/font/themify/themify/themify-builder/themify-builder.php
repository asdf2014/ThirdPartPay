<?php
/**
 * Framework Name: Themify Builder
 * Framework URI: http://themify.me/
 * Description: Page Builder with interactive drag and drop features
 * Version: 1.0
 * Author: Themify
 * Author URI: http://themify.me
 *
 *
 * @package ThemifyBuilder
 * @category Core
 * @author Themify
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Layouts Constant
 */
define( 'THEMIFY_BUILDER_LAYOUTS_VERSION', '1.1.1' );

/**
 * Define builder constant
 */
define( 'THEMIFY_BUILDER_DIR', dirname(__FILE__) );
define( 'THEMIFY_BUILDER_MODULES_DIR', THEMIFY_BUILDER_DIR . '/modules' );
define( 'THEMIFY_BUILDER_TEMPLATES_DIR', THEMIFY_BUILDER_DIR . '/templates' );
define( 'THEMIFY_BUILDER_CLASSES_DIR', THEMIFY_BUILDER_DIR . '/classes' );
define( 'THEMIFY_BUILDER_INCLUDES_DIR', THEMIFY_BUILDER_DIR . '/includes' );
define( 'THEMIFY_BUILDER_LIBRARIES_DIR', THEMIFY_BUILDER_INCLUDES_DIR . '/libraries' );

// URI Constant
define( 'THEMIFY_BUILDER_URI', THEMIFY_URI . '/themify-builder' );

/**
 * Include builder class
 */
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-model.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-form.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-layouts.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-module.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-import-export.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-plugin-compat.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-visibility-controls.php' );
require_once( THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-options.php' );

/**
 * Init themify builder class
 */
add_action( 'after_setup_theme', 'themify_builder_init', 15 );
function themify_builder_init() {
	global $ThemifyBuilder, $Themify_Builder_Layouts;
	if ( class_exists( 'Themify_Builder') ) {
		if ( Themify_Builder_Model::builder_check() ) {
			$Themify_Builder_Layouts = new Themify_Builder_Layouts();

			$ThemifyBuilder = new Themify_Builder();
			$ThemifyBuilder->init();
			$themify_builder_plugin_compat = new Themify_Builder_Plugin_Compat();
			$themify_builder_import_export = new Themify_Builder_Import_Export();
			$themify_builder_visibility_controls = new Themify_Builder_Visibility_Controls();
		}
	} // class_exists check

	if( is_admin() && current_user_can( 'update_plugins' ) ) {
		include THEMIFY_BUILDER_DIR . '/themify-builder-updater.php';
	}
}

if ( ! function_exists('themify_builder_edit_module_panel') ) {
	/**
	 * Hook edit module frontend panel
	 * @param $mod_name
	 * @param $mod_settings
	 */
	function themify_builder_edit_module_panel( $mod_name, $mod_settings ) {
		do_action( 'themify_builder_edit_module_panel', $mod_name, $mod_settings );
	}
}

if(!function_exists('themify_manage_builder')) {
	/**
	 * Builder Settings
	 * @param array $data
	 * @return string
	 * @since 1.2.7
	 */
	function themify_manage_builder($data=array()) {
		global $ThemifyBuilder;
		$data = themify_get_data();
		$pre = 'setting-page_builder_';
		$output = '';
		$modules = $ThemifyBuilder->get_modules( 'all' );

		foreach ($modules as $m) {
			$exclude = $pre.'exc_'.$m['id'];
			$checked = isset($data[$exclude]) ? 'checked="checked"' : '';
			$output .= '<p>
						<span><input id="'.esc_attr( 'builder_module_'.$m['id'] ).'" type="checkbox" name="'.esc_attr( $exclude ).'" value="1" '.$checked.'/> <label for="'.esc_attr( 'builder_module_'.$m['id'] ).'">' . wp_kses_post( sprintf(__('Exclude %s module', 'themify'), $m['name'] ) ) . '</label></span>
					</p>';	
		}
		
		return $output;
	}
}

if(!function_exists('themify_manage_builder_active')) {
	/**
	 * Builder Settings
	 * @param array $data
	 * @return string
	 * @since 1.2.7
	 */
	function themify_manage_builder_active($data=array()) {
		$pre = 'setting-page_builder_';
		$output = '';
		$options = array(
			array('name' => __('Enable', 'themify'), 'value' => 'enable'),
			array('name' => __('Disable', 'themify'), 'value' =>'disable')
		);

		$output .= sprintf('<p><span class="label">%s</span><select id="%s" name="%s">%s</select></p>',
			esc_html__( 'Themify Builder:', 'themify' ),
			esc_attr( $pre . 'is_active' ),
			esc_attr( $pre . 'is_active' ),
			themify_options_module( $options, $pre . 'is_active' )
		);

		if ( 'disable' != themify_get( $pre . 'is_active' ) ) {
			
			$output .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
				esc_attr( $pre . 'disable_shortcuts' ),
				esc_attr( $pre . 'disable_shortcuts' ),
				esc_attr( $pre . 'disable_shortcuts' ),
				checked( true, themify_check( $pre . 'disable_shortcuts' ), false ),
				wp_kses_post( __( 'Disable Builder shortcuts (eg. disable shortcut like Cmd+S = save)', 'themify') )
			);
                       
		}

		return $output;
	}
}

if(!function_exists('themify_manage_builder_cache')){
    /**
	 * Builder Setting cache
	 * @param array $data
	 * @return string
	 * @since 2.0.0
	 */
        function themify_manage_builder_cache($data=array()) {
            if( TFCache::check_version()){
                $pre = 'setting-page_builder_';
                $output = sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
                        esc_attr( $pre . 'cache' ),
                        esc_attr( $pre . 'cache' ),
                        esc_attr( $pre . 'cache' ),
                        checked( true, themify_check( $pre . 'cache' ), false ),
                        __('Disable Builder caching (if you experience Builder issues and conflicts, disable it)', 'themify')
                );

                $output .= sprintf( '<p data-show-if-element="%s" data-show-if-value="false"><a href="#" data-clearing-text="%s" data-done-text="%s" data-default-text="%s" data-default-icon="ti-eraser" class="button button-outline js-clear-builder-cache"> <i class="ti-eraser"></i> <span>%s</span></a><br><small>%s</small></p>',
                        esc_attr( '#' . $pre . 'cache' ),
                        esc_html__( 'Clearing cache...', 'themify' ),
                        esc_html__( 'Done', 'themify' ),
                        esc_html__( 'Clear cache', 'themify' ),
                        esc_html__( 'Clear cache', 'themify' ),
                        esc_html__( 'Clear all Builder cache', 'themify' )
                );
            }
            else{
                $output = __('Your server does not support Builder cache, thus it is disabled. It requires PHP 5.4+','themify');
            }
            return $output;
        }
}

if(!function_exists('themify_manage_builder_animation')) {
	/**
	 * Builder Setting Animations
	 * @param array $data
	 * @return string
	 * @since 2.0.0
	 */
	function themify_manage_builder_animation($data=array()) {
		$opt_data = themify_get_data();
		$pre = 'setting-page_builder_animation_';
		$mobile_checked = '';
		$disabled_checked = '';

		if ( isset( $opt_data[ $pre.'mobile_exclude' ] ) && $opt_data[ $pre.'mobile_exclude' ] ) 
			$mobile_checked = " checked='checked'";
		
		if ( isset( $opt_data[ $pre.'disabled' ] ) && $opt_data[ $pre . 'disabled' ] ) 
			$disabled_checked = " checked='checked'";

		$out = '';
		$out .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
			esc_attr( $pre . 'mobile_exclude' ),
			esc_attr( $pre . 'mobile_exclude' ),
			esc_attr( $pre . 'mobile_exclude' ),
			$mobile_checked,
			wp_kses_post( __( 'Disable Builder animation on mobile and tablet only', 'themify') )
		);
		$out .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
			esc_attr( $pre . 'disabled' ),
			esc_attr( $pre . 'disabled' ),
			esc_attr( $pre . 'disabled' ),
			$disabled_checked,
			wp_kses_post( __( 'Disable Builder animation on all devices (all row and module animation will not have any effect)', 'themify') )
		);

		return $out;
	}
}

if(!function_exists('themify_manage_builder_parallax')) {
	/**
	 * Builder Setting Animations
	 * @param array $data
	 * @return string
	 * @since 2.0.2
	 */
	function themify_manage_builder_parallax($data=array()) {
		$opt_data = themify_get_data();
		$pre = 'setting-page_builder_parallax_';
		$mobile_checked = '';
		$disabled_checked = '';
                
		if ( isset( $opt_data[ $pre.'mobile_exclude' ] ) && $opt_data[ $pre.'mobile_exclude' ] ) 
			$mobile_checked = " checked='checked'";
		
		if ( isset( $opt_data[ $pre.'disabled' ] ) && $opt_data[ $pre . 'disabled' ] ) 
			$disabled_checked = " checked='checked'";

		$out = '';
		$out .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
			esc_attr( $pre . 'mobile_exclude' ),
			esc_attr( $pre . 'mobile_exclude' ),
			esc_attr( $pre . 'mobile_exclude' ),
			$mobile_checked,
			wp_kses_post( __( 'Disable Builder parallax on mobile and tablet only', 'themify') )
		);
		$out .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
			esc_attr( $pre . 'disabled' ),
			esc_attr( $pre . 'disabled' ),
			esc_attr( $pre . 'disabled' ),
			$disabled_checked,
			wp_kses_post( __( 'Disable Builder parallax on all devices (all row will not have any effect)', 'themify') )
		);

		return $out;
	}
}

/**
 * Add Builder to all themes using the themify_theme_config_setup filter.
 * @param $themify_theme_config
 * @return mixed
 * @since 1.4.2
 */
function themify_framework_theme_config_add_builder($themify_theme_config) {
	$themify_theme_config['panel']['settings']['tab']['page_builder'] = array(
		'title' => __('Themify Builder', 'themify'),
		'id' => 'themify-builder',
		'custom-module' => array(
			array(
				'title' => __('Themify Builder Options', 'themify'),
				'function' => 'themify_manage_builder_active'
			),
		)
	);
        if ( 'disable' != themify_get('setting-page_builder_is_active' )) {
            $themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
                    'title' => __('Builder Cache', 'themify'),
                    'function' => 'themify_manage_builder_cache'
            );
        }
	if ( 'disable' != apply_filters( 'themify_enable_builder', themify_get('setting-page_builder_is_active') ) ) {
		$themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
			'title' => __('Animation Effects', 'themify'),
			'function' => 'themify_manage_builder_animation'
		);

		$themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
			'title' => __('Parallax Effects', 'themify'),
			'function' => 'themify_manage_builder_parallax'
		);

		$themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
			'title' => __('Exclude Builder Modules', 'themify'),
			'function' => 'themify_manage_builder'
		);
	}
	return $themify_theme_config;
};
add_filter('themify_theme_config_setup', 'themify_framework_theme_config_add_builder');

if ( ! function_exists( 'themify_builder_grid_lists' ) ) {
	/**
	 * Get Grid menu list
	 */
	function themify_builder_grid_lists( $handle = 'row', $set_gutter = null, $set_column_equal_height = null ) {
		$grid_lists = Themify_Builder_Model::get_grid_settings();
		$gutters = Themify_Builder_Model::get_grid_settings( 'gutter' );
		$selected_gutter = is_null( $set_gutter ) ? '' : $set_gutter; ?>
		<div class="grid_menu" data-handle="<?php echo esc_attr( $handle ); ?>">
			<div class="grid_icon ti-layout-column3"></div>
			<div class="themify_builder_grid_list_wrapper">
				<ul class="themify_builder_grid_list clearfix">
					<?php foreach( $grid_lists as $row ): ?>
					<li>
						<ul>
							<?php foreach( $row as $li ): ?>
								<li><a href="#" class="themify_builder_column_select <?php echo esc_attr( 'grid-layout-' . implode( '-', $li['data'] ) ); ?>" data-handle="<?php echo esc_attr( $handle ); ?>" data-grid="<?php echo esc_attr( json_encode( $li['data'] ) ); ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
							<?php endforeach; ?>
						</ul>
					</li>
					<?php endforeach; ?>
				</ul>

				<div class="themify_builder_equal_column_height themify_builder_admin">
					<input type="checkbox" class="themify_builder_equal_column_height_checkbox" data-handle="<?php echo esc_attr( $handle ); ?>"
						<?php if ($set_column_equal_height != null) { echo ' checked="checked"'; } ?>>
					<span><?php esc_html_e( 'Equal Column Height', 'themify' ) ?></span>
				</div>

				<select class="gutter_select" data-handle="<?php echo esc_attr( $handle ); ?>">
					<?php foreach( $gutters as $gutter ): ?>
					<option value="<?php echo esc_attr( $gutter['value'] ); ?>"<?php selected( $selected_gutter, $gutter['value'] ); ?>><?php echo esc_html( $gutter['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<small><?php esc_html_e('Gutter Spacing', 'themify') ?></small>

			</div>
			<!-- /themify_builder_grid_list_wrapper -->
		</div>
		<!-- /grid_menu -->
		<?php
	}
}