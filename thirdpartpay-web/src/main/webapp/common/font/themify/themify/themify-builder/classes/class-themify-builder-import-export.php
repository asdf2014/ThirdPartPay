<?php
/**
 * This file provide a class for Builder Import Export.
 *
 * a class to perform builder import/export operation
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * The Builder Import Export class.
 *
 * This is used to provide a hook and ajax action to perform Import Export for Builder.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class Themify_Builder_Import_Export {
	/**
	 * Class constructor.
	 * 
	 * @access public
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'do_export_file' ) );
		add_action( 'wp_ajax_builder_import_file', array( &$this, 'builder_import_file_ajaxify' ), 10 );
	}

	/**
	 * Perform export file.
	 * 
	 * @access public
	 */
	public function do_export_file() {

		if ( is_user_logged_in() && isset( $_GET['themify_builder_export_file'] ) && $_GET['themify_builder_export_file'] == true && 
			check_admin_referer( 'themify_builder_export_nonce' ) ) {
			
			$postid = (int) $_GET['postid'];
			$postdata = get_post( $postid );
			$data_name = $postdata->post_name;

			global $ThemifyBuilder;
			$builder_data = $ThemifyBuilder->get_builder_data( $postid );
						if (is_array($builder_data) && !empty($builder_data)) {
							foreach ($builder_data as &$row) {
								if(isset($row['styling']) && isset($row['styling']['background_slider']) && $row['styling']['background_slider']){
									$row['styling']['background_slider'] = self::replace_with_image_path($row['styling']['background_slider']);
								}
								if (isset($row['cols']) && !empty($row['cols'])) {
									foreach ($row['cols'] as &$col) {
										if(isset($col['styling']) && isset($col['styling']['background_slider']) && $col['styling']['background_slider']){
											$col['styling']['background_slider']  = self::replace_with_image_path($col['styling']['background_slider']);
										}
										if (isset($col['modules']) && !empty($col['modules'])) {
											foreach ($col['modules'] as &$mod) {
												if (isset($mod['mod_name']) && $mod['mod_name']=='gallery' && $mod['mod_settings']['shortcode_gallery']) {
													$mod['mod_settings']['shortcode_gallery'] = self::replace_with_image_path($mod['mod_settings']['shortcode_gallery']);
												}
												// Check for Sub-rows
												if (isset($mod['cols']) && !empty($mod['cols'])) {
													foreach ($mod['cols'] as &$sub_col) {
														if(isset($sub_col['styling']) && isset($sub_col['styling']['background_slider']) && $sub_col['styling']['background_slider']){
															$sub_col['styling']['background_slider'] = self::replace_with_image_path($sub_col['styling']['background_slider']);
														}
														if (isset($sub_col['modules']) && !empty($sub_col['modules'])) {
															foreach ($sub_col['modules'] as &$sub_module) {
																if (isset($sub_module['mod_name']) && $sub_module['mod_name']=='gallery' && $sub_module['mod_settings']['shortcode_gallery']) {
																	$sub_module['mod_settings']['shortcode_gallery'] = self::replace_with_image_path($sub_module['mod_settings']['shortcode_gallery']);
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
			$builder_data = json_encode( $builder_data );

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			WP_Filesystem();
			global $wp_filesystem;

			if ( class_exists('ZipArchive') ) {
				$datafile = 'builder_data_export.txt';
				$wp_filesystem->put_contents( $datafile, $builder_data, FS_CHMOD_FILE );

				$files_to_zip = array( $datafile );
				$file = $data_name . '_themify_builder_export_' . date( 'Y_m_d' ) . '.zip';
				$result = themify_create_zip( $files_to_zip, $file, true );
			}

			if ( isset( $result ) && $result ) {
				if ( ( isset( $file ) ) && ( file_exists( $file ) ) ) {
					ob_start();
					header('Pragma: public');
					header('Expires: 0');
					header("Content-type: application/force-download");
					header('Content-Disposition: attachment; filename="' . $file . '"');
					header("Content-Transfer-Encoding: Binary"); 
					header("Content-length: ".filesize( $file ) );
					header('Connection: close');
					ob_clean();
					flush(); 
					echo $wp_filesystem->get_contents( $file );
					unlink( $datafile );
					unlink( $file );
					exit();
				} else {
					return false;
				}
			} else {
				if ( ini_get('zlib.output_compression') ) {
					/**
					 * Turn off output buffer compression for proper zip download.
					 * @since 2.0.2
					 */
					$srv_stg = 'ini' . '_' . 'set';
					call_user_func( $srv_stg, 'zlib.output_compression', 'Off');
				}
				ob_start();
				header('Content-Type: application/force-download');
				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Cache-Control: private',false);
				header('Content-Disposition: attachment; filename="'.$data_name.'_themify_builder_export_'.date("Y_m_d").'.txt"');
				header('Content-Transfer-Encoding: binary');
				ob_clean();
				flush();
				echo $builder_data;
				exit();
			}
		}
	}
		
	/**
	 * Replace shortcode gallery with image path
	 * 
	 * @access public
	 * @param string $shortcode
	 * @return string
	 */
	public static function replace_with_image_path($shortcode){
		global $ThemifyBuilder;
		$images = $ThemifyBuilder->get_images_from_gallery_shortcode($shortcode);
		if(!empty($images)){
			preg_match('/\[gallery.*ids=.(.*).\]/', $shortcode, $ids);
			$ids = trim($ids[1],'\\');
			$ids = trim($ids,'"');
			$path = array();
			foreach($images as $img){
			   $path[] = wp_get_attachment_image_url($img->ID,'full');
			}
			if(!empty($path)){
				$path = implode(',',$path);
				$shortcode  = str_replace('[gallery','[gallery path="'.$path.'" ',$shortcode);
			}
		}
		return $shortcode;
	}
	
	/**
	 * Get attachment ID by URL.
	 * 
	 * @access public
	 * @param string $url 
	 * @return string
	 */
	public static function get_attachment_id_by_url($url){
		// Split the $url into two parts with the wp-content directory as the separator
		$parsed_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );
		// Get the host of the current site and the host of the $url, ignoring www
		$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
		$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );
		// Return nothing if there aren't any $url parts or if the current host and $url host do not match
 
		if ( ! isset( $parsed_url[1] ) || empty( $parsed_url[1] ) || ( $this_host != $file_host ) ) {
				return false;
		}
		// Now we're going to quickly search the DB for any attachment GUID with a partial path match
		// Example: /uploads/2013/05/test-image.jpg
		global $wpdb;
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type='attachment' AND guid RLIKE %s;", $parsed_url[1] ) );
		return $attachment?$attachment[0]:false;
	}
		
	/**
	 * Replace image path if it doesn't exist and replace with the new ids
	 * 
	 * @access public
	 * @param string $shortcode 
	 * @param int $post_id 
	 * @return string
	 */
	public static function replace_ids_image_path($shortcode,$post_id=false){
		
		preg_match('/\[gallery.*path.*?=.*?[\'"](.+?)[\'"].*?\]/i', $shortcode, $path);
		if(isset($path[1]) && $path[1]){
			$path = trim($path[1],'\\');
			$path = trim($path,'"');   
			$image_path = explode(",", $path);
			if(!empty($image_path)){
				$attachment_id = array();
				$wp_upload_dir = wp_upload_dir();
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				foreach($image_path as $img){
				   
					$img_id = self::get_attachment_id_by_url($img);
					if(!$img_id){
						// extract the file name and extension from the url
						$file_name = basename( $img );

						// get placeholder file in the upload dir with a unique, sanitized filename
						$upload = wp_upload_bits($file_name,NULL,'');
						if ( $upload['error'] ){
							continue;
						}
						// fetch the remote url and write it to the placeholder file
						$request = new WP_Http;
						$response = $request->request( $img,  array( 'sslverify' => false));

						// request failed and make sure the fetch was successful
						if ( ! $response  || is_wp_error($response)|| wp_remote_retrieve_response_code($response) != '200' ) {
							continue;
						}

						$out_fp = fopen($upload['file'], 'w');
						if ( !$out_fp ){
							continue;
						}

						fwrite($out_fp,  wp_remote_retrieve_body( $response ) );
						fclose($out_fp);
						clearstatcache();
						$filetype = wp_check_filetype( $file_name, null );
						$attachment = array(
							'guid'           => $wp_upload_dir['url'] . '/' . $file_name, 
							'post_mime_type' => $filetype['type'],
							'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
							'post_content'   => '',
							'post_status'    => 'inherit'
						);
						
						$img_id = wp_insert_attachment( $attachment, $upload['file'], 369 );
						if($img_id){
							$attach_data = wp_generate_attachment_metadata( $img_id, $upload['file'] );
							wp_update_attachment_metadata( $img_id, $attach_data );
						}
					}
					if($img_id){
						$attachment_id[] = $img_id;
					}
				}
			}
			$shortcode = str_replace('path="'.$path.'"','',$shortcode);
			if(!empty($attachment_id)){
				$attachment_id = implode(',',$attachment_id);
				preg_match('/\[gallery.*ids.*?=.*?[\'"](.+?)[\'"].*?\]/i', $shortcode, $ids);
				$ids = trim($ids[1],'\\');
				$ids = trim($ids,'"');
				$shortcode = str_replace('ids="'.$ids.'"','ids="'.$attachment_id.'"',$shortcode);
			}
		}
		return $shortcode;
	}

	/**
	 * Builder Import Lightbox
	 * 
	 * @access public
	 * @return html
	 */
	public function builder_import_file_ajaxify(){
		check_ajax_referer( 'tfb_load_nonce', 'nonce' );

		$output = '<div class="lightbox_inner themify-builder-import-file-inner">';
		$output .= wp_kses_post( sprintf( '<h3>%s</h3>', __( 'Select a file to import', 'themify') ) );

		if ( is_multisite() && !is_upload_space_available() ) {
			$output .= wp_kses_post( sprintf( __( '<p>Sorry, you have filled your %s MB storage quota so uploading has been disabled.</p>', 'themify' ), get_space_allowed() ) );
		} else {
			$output .= sprintf( '<p><div class="themify-builder-plupload-upload-uic hide-if-no-js tf-upload-btn" id="%sthemify-builder-plupload-upload-ui">
										<input id="%sthemify-builder-plupload-browse-button" type="button" value="%s" class="builder_button" />
										<span class="ajaxnonceplu" id="ajaxnonceplu%s"></span>
								</div></p>', 'themify_builder_import_file', 'themify_builder_import_file', __('Upload', 'themify'), wp_create_nonce('themify_builder_import_filethemify-builder-plupload') );
			
			$max_upload_size = (int) wp_max_upload_size() / ( 1024 * 1024 );
			$output .= wp_kses_post( sprintf( __( '<p>Maximum upload file size: %d MB.</p>', 'themify' ), $max_upload_size ) );
		}
		
		$output .= '</div>';
		echo $output;
		die();
	}
}