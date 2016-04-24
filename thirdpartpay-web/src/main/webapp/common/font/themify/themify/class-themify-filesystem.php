<?php
/**
 * The file that defines File System class
 *
 * Themify_Filesystem class provide instance of Filesystem Api to do some file operation.
 * Based on WP_Filesystem the class method will remain same.
 * 
 *
 * @package    Themify
 * @subpackage Filesystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Themify_Filesystem' ) ) {

	/**
	 * The Themify_Filesystem class.
	 *
	 * This is used to initialize WP_Filesytem Api instance
	 * check for filesytem method and return correct filesystem method
	 *
	 * @package    Themify
	 * @subpackage Filesystem
	 * @author     Themify
	 */
	class Themify_Filesystem {

		/**
		 * The class instance variable.
		 * the plugin.
		 *
		 * @access   protected
		 * @var      $instace    class instance.
		 */
		protected static $instance = null;

		/**
		 * Filesytem method info.
		 * 
		 * @access protected
		 * @var $direct Store information current filesystem method.
		 */
		protected static $direct = null;

		/**
		 * Instance of WP_Filesytem api class.
		 * 
		 * @access public
		 * @var $execute Store the instance of WP_Filesystem class being used.
		 */
		public $execute = null;

		/**
		 * Class constructor.
		 * 
		 * @access public
		 */
		public function __construct() {
			$this->initialize();
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Initialize filesystem method.
		 */
		public function initialize() {
			// Load WP Filesystem
			if ( ! function_exists('WP_Filesystem') ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
			global $wp_filesystem;
			$access_type = get_filesystem_method();

			if ( 'direct' == $access_type ) {
				$this->execute = $wp_filesystem;
			} else {
				self::load_direct();
				$this->execute = self::$direct;
			}
		}

		/**
		 * Initialize Filesystem direct method.
		 */
		public static function load_direct() {
			if ( self::$direct === null ) {
				require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
				require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
				self::$direct = new WP_Filesystem_Direct( array() );
			}
		}
	}
}