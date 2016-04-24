<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Themify_Builder_Updater {

	var $name;
	/**
	 * @var string $nicename Human-readable name of the plugin.
	 */
	var $nicename = '';
	/**
	 * @var string $nicename_short Human-readable name of the plugin where 'Builder' or other prefixes have been removed.
	 */
	var $nicename_short = '';
	/**
	 * @var string $update_type Whether this is a 'plugin' update or an 'addon' update.
	 */
	var $update_type = '';
	var $version;
	var $versions_url;
	var $package_url;

	public function __construct( $name, $version, $slug ) {
		if ( is_array( $name ) ) {
			// New name parameter
			$this->name = $name['name'];
			$this->nicename = $name['nicename'];
			$this->update_type = $name['update_type'];
		} else {
			// Backwards compatibility
			$this->name = $name;
			$this->nicename = ucwords( str_replace( '-', ' ', $name ) );
			$this->update_type = ( stripos( $name, 'builder-' ) !== false ) ? 'addon' : 'plugin';
		}

		/**
		 * Filter to enable or disable the update notifier
		 *
		 * @param bool whether update notifier should be enabled
		 * @param string $name the name of the addon or plugin instantiating the update notifier
		 * @param string $update_type either "addon" or "plugin"
		 */
		if( ! apply_filters( 'themify_builder_updater_enabled', true, $this->name, $this->update_type ) ) {
			return false;
		}

		$this->nicename_short = str_replace( 'Builder ', '', $this->nicename );
		$this->version = $version;
		$this->slug = $slug;
		$this->versions_url = 'http://themify.me/versions/versions.xml';
		$this->package_url = "http://themify.me/files/{$this->name}/{$this->name}.zip";

		if( isset( $_GET['page'] ) && ! isset( $_GET['action'] ) && ( $_GET['page'] == 'themify-builder' || $_GET['page'] == 'themify' ) ) {
			add_action( 'admin_notices', array( $this, 'check_version' ), 3 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		} elseif( isset( $_GET['page'] ) && isset( $_GET['action'] ) && ( $_GET['page'] == 'themify-builder' || $_GET['page'] == 'themify' ) ) {
			add_action( 'admin_notices', 'themify_builder_updater', 3 );
		}

		if( defined('WP_DEBUG') && WP_DEBUG ) {
			delete_transient( "{$this->name}_new_update" );
			delete_transient( "{$this->name}_check_update" );
		}

		//Executes themify_updater function using wp_ajax_ action hook
		add_action( 'wp_ajax_themify_builder_validate_login', array( $this, 'themify_builder_validate_login' ) );
	}

	public function check_version() {
		$notifications = '';

		// Check update transient
		$current = get_transient( "{$this->name}_check_update" ); // get last check transient
		$timeout = 60;
		$time_not_changed = isset( $current->lastChecked ) && $timeout > ( time() - $current->lastChecked );
		$newUpdate = get_transient( "{$this->name}_new_update" ); // get new update transient

		if ( is_object( $newUpdate ) && $time_not_changed ) {
			if ( version_compare( $this->version, $newUpdate->version, '<') ) {
				$notifications .= sprintf( __('<p class="update %s">%s version %s is now available. <a href="%s" title="" class="%s" target="%s" data-plugin="%s"
data-package_url="%s" data-nicename_short="%s" data-update_type="%s">Update now</a> or view the <a href="%s" title=""
class="themify_changelogs" target="_blank" data-changelog="%s">change
log</a> for details.</p>', 'themify'),
					esc_attr( $newUpdate->login ),
					$this->nicename,
					$newUpdate->version,
					esc_url( $newUpdate->url ),
					esc_attr( $newUpdate->class ),
					esc_attr( $newUpdate->target ),
					esc_attr( $this->slug ),
					esc_attr( $this->package_url ),
					esc_attr( $this->nicename_short ),
					esc_attr( $this->update_type ),
					esc_url( 'http://themify.me/changelogs/' . $this->name . '.txt' ),
					esc_url( 'http://themify.me/changelogs/' . $this->name . '.txt' )
				);
				echo '<div class="notifications">'. $notifications . '</div>';
			}
			return;
		}

		// get remote version
		$remote_version = $this->get_remote_version();

		// delete update checker transient
		delete_transient( "{$this->name}_check_update" );

		$class = "";
		$target = "";
		$url = "#";
		
		$new = new stdClass();
		$new->login = 'login';
		$new->version = $remote_version;
		$new->url = $url;
		$new->class = 'themify-builder-upgrade-plugin';
		$new->target = $target;

		if ( version_compare( $this->version, $remote_version, '<' ) ) {
			set_transient( 'themify_builder_new_update', $new );
			$notifications .= sprintf( __('<p class="update %s">%s version %s is now available. <a href="%s" title="" class="%s" target="%s" data-plugin="%s"
data-package_url="%s" data-nicename_short="%s" data-update_type="%s">Update now</a> or view the <a href="%s" title=""
class="themify_changelogs" target="_blank" data-changelog="%s">change
log</a> for details.</p>', 'themify'),
				esc_attr( $new->login ),
				$this->nicename,
				$new->version,
				esc_url( $new->url ),
				esc_attr( $new->class ),
				esc_attr( $new->target ),
				esc_attr( $this->slug ),
				esc_attr( $this->package_url ),
				esc_attr( $this->nicename_short ),
				esc_attr( $this->update_type ),
				esc_url( 'http://themify.me/changelogs/' . $this->name . '.txt' ),
				esc_url( 'http://themify.me/changelogs/' . $this->name . '.txt' )
			);
		}

		// update transient
		$this->set_update();

		echo '<div class="notifications">'. $notifications . '</div>';
	}

	public function get_remote_version() {
		$version = '';

		$response = wp_remote_get( $this->versions_url );
		if( is_wp_error( $response ) ) {
			return $version;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( is_wp_error( $body ) || empty( $body ) ) {
			return $version;
		}

		$xml = new DOMDocument;
		$xml->loadXML( trim( $body ) );
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;
		$xpath = new DOMXPath($xml);
		$query = "//version[@name='".$this->name."']";
		$elements = $xpath->query($query);
		if( $elements->length ) {
			foreach ($elements as $field) {
				$version = $field->nodeValue;
			}
		}

		return $version;
	}

	public function set_update() {
		$current = new stdClass();
		$current->lastChecked = time();
		set_transient( "{$this->name}_check_update", $current );
	}

	public function is_update_available() {
		$newUpdate = get_transient( "{$this->name}_new_update" ); // get new update transient

		if ( false === $newUpdate ) {
			$new_version = $this->get_remote_version( $this->name );
		} else {
			$new_version = $newUpdate->version;
		}

		if ( version_compare( $this->version, $new_version, '<') ) {
			return true;
		} else {
			return false;
		}
	}

	public function enqueue() {
		wp_enqueue_script( 'themify-builder-plugin-upgrade', THEMIFY_BUILDER_URI . '/js/themify.builder.upgrader.js', array('jquery'), false, true );
	}

	/**
	 * Validate login credentials against Themify's membership system
	 */
	function themify_builder_validate_login(){
		$response = wp_remote_post(
			'http://themify.me/files/themify-login.php',
			array(
				'timeout' => 300,
				'headers' => array(),
				'body' => array(
					'amember_login' => $_POST['username'],
					'amember_pass'  => $_POST['password']
				)
		    )
		);

		//Was there some error connecting to the server?
		if( is_wp_error( $response ) ) {
			echo 'Error ' . $response->get_error_code() . ': ' . $response->get_error_message( $response->get_error_code() );
			die();
		}

		//Connection to server was successful. Test login cookie
		$amember_nr = false;
		foreach($response['cookies'] as $cookie){
			if($cookie->name == 'amember_nr'){
				$amember_nr = true;
			}
		}
		if(!$amember_nr){
			echo 'invalid';
			die();
		}

		$subs = json_decode($response['body'], true);
		$sub_match = 'false';

		foreach ($subs as $key => $value) {
			if ( isset( $_POST['update_type'] ) && 'addon' === $_POST['update_type'] ) {
				if(stripos($value['title'], 'Addon Bundle') !== false){
					$sub_match = 'true';
					break;
				}
			}
			if ( isset( $_POST['nicename_short'] ) && stripos($value['title'], $_POST['nicename_short'] ) !== false ) {
				$sub_match = 'true';
				break;
			}
			if(stripos($value['title'], 'Master Club') !== false){
				$sub_match = 'true';
				break;
			}
			if(stripos($value['title'], 'Lifetime Master Club') !== false){
				$sub_match = 'true';
				break;
			}
		}
		echo $sub_match;
		die();
	}
}

/**
 * Updater called through wp_ajax_ action
 */
function themify_builder_updater(){
	
	$url = isset( $_POST['package_url'] ) ? $_POST['package_url'] : null;
	$plugin_slug = isset( $_POST['plugin'] ) ? $_POST['plugin'] : null;

	if( ! $url || ! $plugin_slug ) return;

	//If login is required
	if($_GET['login'] == 'true'){

			if(isset($_POST['password'])){
	            $cred = $_POST;
	            $filesystem = WP_Filesystem($cred);
	        }
			else{
				$filesystem = WP_Filesystem();
			}

			$response = wp_remote_post(
				'http://themify.me/member/login.php',
				array(
					'timeout' => 300,
					'headers' => array(
						
					),
					'body' => array(
						'amember_login' => $_POST['username'],
						'amember_pass'  => $_POST['password']
					)
			    )
			);

			//Was there some error connecting to the server?
			if( is_wp_error( $response ) ) {
				$errorCode = $response->get_error_code();
				echo 'Error: ' . $errorCode;
				die();
			}

			//Connection to server was successful. Test login cookie
			$amember_nr = false;
			foreach($response['cookies'] as $cookie){
				if($cookie->name == 'amember_nr'){
					$amember_nr = true;
				}
			}
			if(!$amember_nr){
				_e('You are not a Themify Member.', 'themify');
				die();
			}
	}

	//remote request is executed after all args have been set
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once(THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-upgrader.php');
	$title = __('Update Themify Builder plugin', 'themify');
	$nonce = 'upgrade-themify_plugin';

	$upgrader = new Themify_Builder_Upgrader( new Plugin_Upgrader_Skin(
		array(
			'plugin' => $plugin_slug,
			'title' => __( 'Update Builder', 'themify' )
		)
	));
	$upgrader->upgrade( $plugin_slug, $url, $response['cookies'] );

	// Clear builder cache
	if ( class_exists( 'TFCache' ) && TFCache::check_version() ) {
		TFCache::removeDirectory( TFCache::get_cache_dir() );
	}

	//if we got this far, everything went ok!	
	die();
}

function themify_builder_upgrade_complete($update_actions, $plugin) {
	if ( defined( 'THEMIFY_BUILDER_SLUG' ) && $plugin == THEMIFY_BUILDER_SLUG ) {
		$update_actions['themify_complete'] = '<a href="' . self_admin_url('admin.php?page=themify-builder') . '" title="' . __('Return to Builder Settings', 'themify') . '" target="_parent">' . __('Return to Builder Settings', 'themify') . '</a>';
	} elseif ( defined( 'THEMIFY_VERSION' ) ) {
		$update_actions['plugins_page'] = '<a href="' . esc_url( self_admin_url( 'admin.php?page=themify' ) ) . '" title="' . __( 'Return to Themify Panel', 'themify' ) . '" target="_parent">' . __( 'Return to Themify Panel', 'themify' ) . '</a>';
	}
	return $update_actions;
}
add_filter( 'update_plugin_complete_actions', 'themify_builder_upgrade_complete', 10, 2 );