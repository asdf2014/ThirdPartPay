<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ThemifyConfig {

	var $theme_dir;

	/**
	 * Theme configuration settings, styling and so on.
	 *
	 * @since 2.0.3
	 *
	 * @var array
	 */
	var $themify_theme_config;

	function __construct() {
		$this->theme_dir = get_template_directory(); 
	}

	function get_config() {
		// Read config file once only if config variable is empty
		if ( empty( $this->themify_theme_config ) ) {
			$this->themify_theme_config = $this->read_config();
		}
		return $this->themify_theme_config;
	}

	function read_config() {
		$the_config_file = is_file( $this->theme_dir .'/custom-config.php' ) ? 'custom-config.php' : 'theme-config.php';
		$the_config_file = $this->theme_dir . '/' . $the_config_file;

		// This variable is overwritten by file included but it's initialized to avoid PHP warnings
		$themify_theme_config = array();
		
		include_once $the_config_file;
		
		return apply_filters( 'themify_theme_config_setup', $themify_theme_config );
	}

}


/**
 * Initializes Themify class
 */
function themify_theme_config_init(){
	/**
	 * Themify initialization class
	 */
	$GLOBALS['ThemifyConfig'] = new ThemifyConfig();
}
add_action( 'after_setup_theme', 'themify_theme_config_init', 8 );