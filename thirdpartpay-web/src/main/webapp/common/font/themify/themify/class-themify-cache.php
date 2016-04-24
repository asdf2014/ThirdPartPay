<?php

/**
 * Class to work with  post cache
 *
 * @package default
 */
class TFCache {

    private static $upload_dir = false;
    private static $cache = array();
    public static $turnoff_cache = NULL;
    private static $role = array();
    private static $script_iscreated = false;
    private static $style_iscreated = false;
    private static $is_footer = false;
    private static $id = false;
    private static $started = 0;

    /**
     * Start Caching
     *
     * @param string $tag
     * @param integer $post_id
     * @param array $args
     * @param integer $time
     *
     * return boolean
     */
    public static function start_cache($tag, $post_id = false, array $args = array(), $time = 30) {
        if (self::$turnoff_cache === NULL) {
            self::$turnoff_cache = self::is_cache_activate();
        }
        if (!self::$turnoff_cache) {
            self::$started++;
            if (self::$started == 1) {
                $dir = self::get_tag_cache_dir($tag, $post_id, $args);
                self::$cache = array('cache_dir' => $dir, 'time' => $time);
                if (!self::check_cache($dir, $time)) {
                    ob_start();
                    return true;
                }
                return false;
            }
        }
        return true;
    }

    /**
     * End Caching
     *
     * return void
     */
    public static function end_cache() {
        if (!self::$turnoff_cache && !empty(self::$cache)) {
            self::$started--;
            if (self::$started == 0) {
                $content = '';
                if (!self::check_cache(self::$cache['cache_dir'], self::$cache['time'])) {
                    $content = ob_get_contents();
                    ob_end_clean();
                    $dir = pathinfo(self::$cache['cache_dir'], PATHINFO_DIRNAME);
                    $wp_filesystem = Themify_Filesystem::get_instance();
                    if (!$wp_filesystem->execute->is_dir($dir)) {
                        wp_mkdir_p($dir);
                    }
                    unset($dir);
                    self::$turnoff_cache = !$wp_filesystem->execute->put_contents(self::$cache['cache_dir'], self::minify_html($content));
                }
                if (!self::$turnoff_cache) {
                    readfile(self::$cache['cache_dir']);
                } else {
                    echo $content;
                    self::removeDirectory(self::get_cache_dir());
                    $data = themify_get_data();
                    $data['setting-page_builder_cache'] = 'on';
                    themify_set_data($data);
                    self::$turnoff_cache = true;
                }
                self::$cache = 0;
            }
        }
    }

    /**
     * Check cache is disabled or builder is active or in admin
     *
     * return boolean
     */
    public static function is_cache_activate() {
        $active = (is_user_logged_in() && current_user_can('manage_options'))  || is_admin() || themify_get('setting-page_builder_cache') || themify_get('setting-page_builder_is_active') != 'enable' ? true : false;
        return apply_filters( 'themify_builder_is_cache_active', $active );
    }

    /**
     * Get tag cached directory
     *
     * @param string $tag
     * @param integer $post_id
     * @param array $args
     *
     * return string
     */
    public static function get_tag_cache_dir($tag, $post_id = false, array $args = array()) {
        $cache_dir = self::get_cache_dir();
        if ($post_id) {
            $cache_dir.=$post_id . '/';
        }
        if ($tag) {
            $tag = trim($tag);
            $cache_dir.=$tag . '/';
            $tag = !empty($args) ? sprintf("%u", crc32(serialize(array_change_key_case($args, CASE_LOWER)))) : 'default';
            if (!self::$role) {
                $current_user = wp_get_current_user();
                self::$role = ($current_user instanceof WP_User) ? sprintf("-%u", crc32(serialize(array_keys(array_change_key_case($current_user->roles, CASE_LOWER))))) : '';
            }
            $cache_dir.=$tag . self::$role . '.html';
        }
        return $cache_dir;
    }

    /**
     * Get cached directory
     *
     * return string
     */
    public static function get_cache_dir($base = false) {
        $upload_dir = !self::$upload_dir ? wp_upload_dir() : self::$upload_dir;

        $dir_info = $base ? (is_ssl()?str_replace('http://','https://',$upload_dir['baseurl']):$upload_dir['baseurl']) : $upload_dir['basedir'];
        $dir_info.='/themify-builder/cache/' . get_template() . '/';
        if (!$base && !is_dir($dir_info)) {
            wp_mkdir_p($dir_info);
        }
        return $dir_info;
    }

    /**
     * Check if cache time
     *
     * return boolean
     */
    public static function check_cache($cache_dir, $time = 30) {

        if (!is_file($cache_dir)) {
            return false;
        } else {
            $last = (strtotime('now') - filemtime($cache_dir)) / 60;
            if ($last >= $time) {
                return false;
            }
        }
        return true;
    }

    /**
     * Remove cache by params
     *
     * @param string $tag
     * @param integer $post_id
     * @param array $args
     *
     * return boolean
     */
    public static function remove_cache($tag = '', $post_id = false, array $args = array()) {
        $cache_dir = self::get_tag_cache_dir($tag, $post_id, $args);
        if($post_id){
            if(function_exists('wp_cache_post_change')){
               wp_cache_post_change($post_id);
            }
            elseif (function_exists('w3tc_pgcache_flush_post')){
                w3tc_pgcache_flush_post($post_id);
            }
        }
        $wp_filesystem = Themify_Filesystem::get_instance();
        $remove = $wp_filesystem->execute->exists($cache_dir) ? $wp_filesystem->execute->delete($cache_dir, true) : true;
        if ($remove) {
            $dir = self::get_cache_dir();
            $styles = $dir . 'styles/' . $post_id . '/';
            $scripts = $dir . 'scripts/' . $post_id . '/';
            self::removeDirectory($styles);
            self::removeDirectory($scripts);
            return true;
        }
        return false;
    }

    /**
     * Remove directory recursively
     *
     * return boolean
     */
    public static function removeDirectory($path) {
        $wp_filesystem = Themify_Filesystem::get_instance();
        return $wp_filesystem->execute->exists($path) ? $wp_filesystem->execute->rmdir($path, true) : true;
    }

    public static function minify_css_callback($matches) {
        return self::minify_css($matches[0]);
    }

    /**
     * Minify html
     *
     * @param string $input
     *
     * return string
     */
    public static function minify_html($input) {
        if (trim($input) === "")
            return $input;

        // Minify Inline <style> Tag CSS.
        $input = preg_replace_callback('|<style\b[^>]*>(.*?)</style>|s', array('TFCache', 'minify_css_callback'), $input);
        return Minify_HTML::minify($input, array('jsCleanComments' => false));
    }

    /**
     * Minify Css
     *
     * @param string $input
     *
     * return string
     */
    public static function minify_css($input) {

        return preg_replace(
                array(
            // Remove comments
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)#s',
            // Remove unused white-spaces
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
            '#(?<=[:\s])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
            // Replace `:0 0 0 0` with `:0`
            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
            // Replace `background-position:0` with `background-position:0 0`
            '#(background-position):0(?=[;\}])#si',
            // Replace `0.6` with `.6`, but only when preceded by `:`, `-`, `,` or a white-space
            '#(?<=[:\-,\s])0+\.(\d+)#s',
            // Minify string value
            '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
            '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
            // Minify HEX color code
            '#(?<=[:\-,\s]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
            // Remove empty selectors
            '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
                ), array(
            '$1',
            '$1$2$3$4$5$6$7',
            '$1',
            ':0',
            '$1:0 0',
            '.$1',
            '$1$3',
            '$1$2$4$5',
            '$1$2$3',
            '$1$2'
                ), trim($input));
    }

    public static function get_current_id() {
        if (self::$id) {
            return self::$id;
        }
        if (is_singular()) {
            self::$id = array('single' => get_the_ID());
        } elseif (is_archive() || is_post_type_archive()) {
            $cat = get_queried_object();
            self::$id = array('loop' => is_post_type_archive() ? $cat->query_var : $cat->term_id);
        } elseif (is_front_page()) {
            self::$id = get_option('page_on_front');
            self::$id = self::$id > 0 ? array('single' => self::$id) : array('' => 'home');
        } elseif (is_home()) {
            self::$id = get_option('page_for_posts');
            self::$id = self::$id > 0 ? array('single' => self::$id) : array('' => 'posts');
        }
        return self::$id ? self::$id : false;
    }

    /**
     * Check if ajax request
     *
     * @param void
     *
     * return boolean
     */
    public static function is_ajax() {
        return defined('DOING_AJAX') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    /**
     * actions for change/print styles and javascript
     *
     * @param void
     *
     * return void
     */
    public static function wp_enque_scripts() {
        add_action('init', array(__CLASS__, 'gzip_buffer'));
        add_filter('print_scripts_array', array(__CLASS__, 'scripts'), 9999, 1);
        add_action('wp_head', array(__CLASS__, 'header_scripts'));
        add_filter('print_styles_array', array(__CLASS__, 'styles'), 9999, 1);
        add_filter('script_loader_tag', array(__CLASS__, 'script_loader_tag'), 10, 3);
        add_filter('style_loader_tag', array(__CLASS__, 'style_loader_tag'), 10, 3);
    }

    public static function header_scripts() {
        self::$is_footer = 1;
    }

    public static function script_loader_tag($script_tag, $handler, $src) {
        if (self::$script_iscreated || self::$turnoff_cache) {
            self::$script_iscreated  = false;
            return $script_tag;//remove async because in some themes there is a js in wp_footer which uses the function from this file
        }
        $dir = self::get_page_cache_dir();

        if (!$dir) {
            return $script_tag;
        }
        if (strpos($src, '//') === 0) {
            $src = is_ssl() ? 'https:' . $src : 'http:' . $src;
        }
        $result = wp_remote_get($src, array('sslverify' => false));
        if(is_array($result) && $result['response']['code']=='200'){
            $response = wp_remote_retrieve_body($result);
            if ($response) {
                global $wp_scripts;
                $js = '';
                if (isset($wp_scripts->registered[$handler]) && $wp_scripts->registered[$handler]->src) {
                    if (isset($wp_scripts->registered[$handler]->extra['data'])) {
                        $js = $wp_scripts->registered[$handler]->extra['data'] . PHP_EOL;
                    }
                }
                $fname = self::$is_footer ? 'footer' : 'header';
                $cache_dir = self::create_scripts_dir('scripts', $fname);
                $file_path = self::get_cache_dir(false);
                $file_path.= $dir . pathinfo($cache_dir, PATHINFO_BASENAME);
                $js.='/* ' . $src . ' */' . PHP_EOL;
                $js.=$response;
                $js.="\n";
                $wp_filesystem = Themify_Filesystem::get_instance();
                $output = $wp_filesystem->execute->get_contents( $cache_dir );
                $output .= $js;
                $wp_filesystem->execute->put_contents( $cache_dir, $output );
            }
        }
        return $script_tag;
    }

    public static function style_loader_tag($style_tag, $handler, $href) {
        if (self::$style_iscreated || self::$turnoff_cache) {
            self::$style_iscreated = false;
            return $style_tag;
        }

        $dir = self::get_page_cache_dir();
        if (!$dir) {
            return $style_tag;
        }
        global $wp_styles;
        if (isset($wp_styles->registered[$handler])) {
            if ((isset($wp_styles->registered[$handler]->extra['conditional'])) || (isset($wp_styles->registered[$handler]->args) && $wp_styles->registered[$handler]->args != 'screen' && $wp_styles->registered[$handler]->args != 'all')) {
                return $style_tag;
            }
        }
        if (strpos($href, '//') === 0) {
            $href = is_ssl() ? 'https:' . $href : 'http:' . $href;
        }
        $result = wp_remote_get($href, array('sslverify' => false));
        if(is_array($result) && $result['response']['code']=='200'){
            $response = wp_remote_retrieve_body($result);
            if($response){
                $minifier = new CSS();
                $path = pathinfo($href, PATHINFO_DIRNAME);
                if (!self::is_remote($path)) {
                    $minifier->add($response, $path);
                } else {
                    $minifier->add($response);
                }
                $fname = self::$is_footer ? 'footer' : 'header';
                $cache_dir = self::create_scripts_dir('styles', $fname);
                $css = '/* ' . $href . ' */' . PHP_EOL;
                $css.=$minifier->minify();
                $css.="\n";
                $wp_filesystem = Themify_Filesystem::get_instance();
                $output = $wp_filesystem->execute->get_contents( $cache_dir );
                $output .= $css;
                $output = $minifier->moveImportsToTop( $output );
                $wp_filesystem->execute->put_contents( $cache_dir, $output );
            }
        }
        return $style_tag;
    }

    /**
     * check if file is load from wp core or not
     *
     * @param string
     *
     * return boolean
     */
    public static function is_local($file) {
        return strpos($file, 'http://') === 0 || strpos($file, 'https://') === 0 || strpos($file, '//') === 0;
    }

    /**
     * check if file is load from remote url
     *
     * @param string
     *
     * return boolean
     */
    public static function is_remote($file) {
        if (self::is_local($file)) {
            $url = parse_url($file);
            return $url['scheme'] . '://' . $url['host'] != get_site_url() && strpos($file, get_site_url()) !== 0;
        } else {
            false;
        }
    }

    /**
     * get full path of file
     *
     * @param string
     *
     * return string
     */
    public static function get_full_path($file) {
        if (self::is_local($file)) {
            $url = parse_url($file);
            $siteurl = get_site_url();
            if ($url['scheme'] . '://' . $url['host'] == $siteurl || strpos($file, $siteurl) === 0) {
                if (is_multisite()) {
                    $details = get_blog_details();
                    $url['path'] = str_replace($details->siteurl, '', $file);
                } else {
                    $url['path'] = str_replace(get_site_url(), '', $file);
                }
                return ABSPATH . trim($url['path'], '/');
            } else {
                return $file;
            }
        } else {
            return ABSPATH . trim($file, '/');
        }
    }

    /**
     * Scripts output, if cache exsists will return cached file, else will cache then return cached file
     *
     * @param array
     *
     * return array
     */
    public static function scripts($todo) {
        self::$turnoff_cache = self::disable_cache();
        if (!empty($todo) && !self::$turnoff_cache) {
            $dir = self::get_page_cache_dir();
            if (!$dir) {
                return $todo;
            }
            global $wp_scripts;
            $fname = self::$is_footer ? 'footer' : 'header';
            $cache_dir = self::create_scripts_dir('scripts', $fname);
            $file_path = self::get_cache_dir(true);
            $file_path.= $dir . pathinfo($cache_dir, PATHINFO_BASENAME);
            if (is_file($cache_dir)) {
                foreach ($todo as $handler) {
                    if (isset($wp_scripts->registered[$handler])) {
                        unset($wp_scripts->registered[$handler]);
                         $wp_scripts->done[] = $handler;
                    }
                }
                $wp_scripts->groups['themify_cache_' . $fname] = self::$is_footer;
                wp_enqueue_script('themify_cache_' . $fname, $file_path, array(),THEMIFY_VERSION, self::$is_footer);
                self::$script_iscreated = true;
                return array('themify_cache_' . $fname);
            }
        }
        return $todo;
    }

    /**
     * check if cache directory doesn't exists, create it
     *
     * @param $type string
     * @param $filename string
     *
     * return string
     */
    private static function create_scripts_dir($type = 'scripts', $filename = false) {
        $dir = self::get_page_cache_dir($type);
        $cache_dir = self::get_cache_dir();
        $cache_dir.=trim($dir, '/') . '/';
        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        if ($filename) {
            if (!self::$role) {
                $current_user = wp_get_current_user();
                self::$role = ($current_user instanceof WP_User) ? sprintf("-%u", crc32(serialize(array_keys(array_change_key_case($current_user->roles, CASE_LOWER))))) : '';
            }
            $ext = $type == 'scripts' ? 'js' : 'css';
            $cache_dir.=$filename . self::$role . '.' . $ext;
        }
        return $cache_dir;
    }

    /**
     * return cached directory of page
     *
     * @param string
     *
     * return string|boolean
     */
    private static function get_page_cache_dir($type = 'scripts') {
        $dir = self::get_current_id();
        if (!$dir) {
            return false;
        }
        $cache_dir = $type . '/' . current($dir) . '/';
        if (key($dir)) {
            $cache_dir.=key($dir) . '/';
        }
        if(themify_is_touch()){
            $cache_dir.='mobile/';
            if (!is_dir($cache_dir)) {
                    wp_mkdir_p($cache_dir);
            }
        }
        return $cache_dir;
    }

    /**
     * Styles output, if cache exsists will return cached file, else will cache then return cached file
     *
     * @param array
     *
     * return array
     */
    public static function styles($todo) {

        self::$turnoff_cache = self::disable_cache();
        if (!empty($todo) && !self::$turnoff_cache) {
            $dir = self::get_page_cache_dir('styles');
            if (!$dir) {
                return $todo;
            }
            $fname = self::$is_footer ? 'footer' : 'header';
            $cache_dir = self::create_scripts_dir('styles', $fname);
            $file_path = self::get_cache_dir(true);
            $file_path.= $dir . pathinfo($cache_dir, PATHINFO_BASENAME);

            if (is_file($cache_dir)) {
                $enque_styles = array('themify_cache_' . $fname);
                global $wp_styles;
                foreach ($todo as $handler) {
                    if (isset($wp_styles->registered[$handler])) {
                        if ((!isset($wp_styles->registered[$handler]->extra['conditional']) || !$wp_styles->registered[$handler]->extra['conditional']) && (!isset($wp_styles->registered[$handler]->args) || !$wp_styles->registered[$handler]->args || $wp_styles->registered[$handler]->args == 'screen' || $wp_styles->registered[$handler]->args == 'all')) {
                            unset($wp_styles->registered[$handler]);
                            $wp_styles->done[] = $handler;
                        } else {
                            $enque_styles[] = $handler;
                        }
                    }
                }
                wp_enqueue_style('themify_cache_' . $fname, $file_path, array(), THEMIFY_VERSION);
                self::$style_iscreated = true;
                return $enque_styles;
            }
        }
        return $todo;
    }

    public static function cache_update($post_id, $post, $update) {
        if ($post->post_status != 'publish' || in_array($post->post_type, array('attachment', 'page', 'nav_menu_item', 'tbuilder_layout_part', 'tbuilder_layout')) || wp_is_post_revision($post) || wp_is_post_autosave($post)) {
            return;
        }
        $cache_dir = self::get_cache_dir();
        if (is_dir($cache_dir) && $dh = opendir($cache_dir)) {
            while (($dir = readdir($dh)) !== false) {
                if (!in_array($dir, array('.', '..', 'scripts', 'styles', "$post_id"))) {
                    self::removeDirectory($cache_dir . '/' . $dir);
                }
            }
            closedir($dh);
        }
    }

    public static function check_version() {
        return version_compare(PHP_VERSION, '5.4', '>=');
    }

    public static function gzip_buffer() {
        if (false !== strpos(strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') && !ini_get('zlib.output_compression') && function_exists('ob_gzhandler')  && !in_array('ob_gzhandler', ob_list_handlers())) {
            ob_start('ob_gzhandler');
        }
    }

    public static function mod_rewrite($rules) {

        return PHP_EOL . '#BEGIN GZIP COMPRESSION BY THEMIFY BUILDER
                <IfModule mod_deflate.c>
                        #add content typing
                        AddType application/x-gzip .gz .tgz
                        AddEncoding x-gzip .gz .tgz

                        # Insert filters
                        AddOutputFilterByType DEFLATE text/plain
                        AddOutputFilterByType DEFLATE text/html
                        AddOutputFilterByType DEFLATE text/xml
                        AddOutputFilterByType DEFLATE text/css
                        AddOutputFilterByType DEFLATE application/xml
                        AddOutputFilterByType DEFLATE application/xhtml+xml
                        AddOutputFilterByType DEFLATE application/rss+xml
                        AddOutputFilterByType DEFLATE application/javascript
                        AddOutputFilterByType DEFLATE application/x-javascript
                        AddOutputFilterByType DEFLATE application/x-httpd-php
                        AddOutputFilterByType DEFLATE application/x-httpd-fastphp
                        AddOutputFilterByType DEFLATE image/svg+xml

                        # Drop problematic browsers
                        BrowserMatch ^Mozilla/4 gzip-only-text/html
                        BrowserMatch ^Mozilla/4\.0[678] no-gzip
                        BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html
                        <IfModule mod_headers.c>
                            # Make sure proxies don\'t deliver the wrong content
                            Header append Vary User-Agent env=!dont-vary
                        </IfModule>
                </IfModule>
                # END GZIP COMPRESSION
                ## EXPIRES CACHING ##
                <IfModule mod_expires.c>
                    ExpiresActive On
                    ExpiresDefault "access plus 1 week"
                    ExpiresByType image/jpg "access plus 1 year"
                    ExpiresByType image/jpeg "access plus 1 year"
                    ExpiresByType image/gif "access plus 1 year"
                    ExpiresByType image/png "access plus 1 year"
                    ExpiresByType image/svg+xml "access plus 1 month"
                    ExpiresByType text/css "access plus 1 month"
                    ExpiresByType text/html "access plus 1 minute"
                    ExpiresByType text/plain "access plus 1 month"
                    ExpiresByType text/x-component "access plus 1 month"
                    ExpiresByType text/javascript "access plus 1 month"
                    ExpiresByType text/x-javascript "access plus 1 month"
                    ExpiresByType application/pdf "access plus 1 month"
                    ExpiresByType application/javascript "access plus 1 months"
                    ExpiresByType application/x-javascript "access plus 1 months"
                    ExpiresByType application/x-shockwave-flash "access plus 1 month"
                    ExpiresByType image/x-icon "access plus 1 year"
                    ExpiresByType application/xml "access plus 0 seconds"
                    ExpiresByType application/json "access plus 0 seconds"
                    ExpiresByType application/ld+json "access plus 0 seconds"
                    ExpiresByType application/xml "access plus 0 seconds"
                    ExpiresByType text/xml "access plus 0 seconds"
                    ExpiresByType application/x-web-app-manifest+json "access plus 0 seconds"
                    ExpiresByType text/cache-manifest "access plus 0 seconds"
                    ExpiresByType audio/ogg "access plus 1 month"
                    ExpiresByType video/mp4 "access plus 1 month"
                    ExpiresByType video/ogg "access plus 1 month"
                    ExpiresByType video/webm "access plus 1 month"
                    ExpiresByType application/atom+xml "access plus 1 hour"
                    ExpiresByType application/rss+xml "access plus 1 hour"
                    ExpiresByType application/font-woff "access plus 1 month"
                    ExpiresByType application/vnd.ms-fontobject "access plus 1 month"
                    ExpiresByType application/x-font-ttf "access plus 1 month"
                    ExpiresByType font/opentype "access plus 1 month"
                    </IfModule>
                    #Alternative caching using Apache`s "mod_headers", if it`s installed.
                    #Caching of common files - ENABLED
                    <IfModule mod_headers.c>
                    <FilesMatch "\.(ico|pdf|flv|swf|js|css|gif|png|jpg|jpeg|ico|txt|html|htm)$">
                    Header set Cache-Control "max-age=2592000, public"
                    </FilesMatch>
                    </IfModule>


                    <IfModule mod_headers.c>
                      <FilesMatch "\.(js|css|xml|gz)$">
                        Header append Vary Accept-Encoding
                      </FilesMatch>
                    </IfModule>


                    <IfModule mod_gzip.c>
                      mod_gzip_on Yes
                      mod_gzip_dechunk Yes
                      mod_gzip_item_include file \.(html?|txt|css|js|php|pl)$
                      mod_gzip_item_include handler ^cgi-script$
                      mod_gzip_item_include mime ^text/.*
                      mod_gzip_item_include mime ^application/x-javascript.*
                      mod_gzip_item_exclude mime ^image/.*
                      mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
                    </IfModule>

                    # Set Keep Alive Header
                    <IfModule mod_headers.c>
                        Header set Connection keep-alive
                    </IfModule>

                    # If your server don`t support ETags deactivate with "None" (and remove header)
                    <IfModule mod_expires.c>
                      <IfModule mod_headers.c>
                        Header unset ETag
                      </IfModule>
                      FileETag None
                    </IfModule>
                    ## EXPIRES CACHING ##
                    #END GZIP COMPRESSION BY THEMIFY BUILDER
                ' . PHP_EOL . $rules;
    }

    public static function admin() {
        add_action('save_post', array('TFCache', 'cache_update'), 10, 3);
        if (get_transient('themify_flush_htaccess') !== 1) {
            set_transient('themify_flush_htaccess', 1, YEAR_IN_SECONDS);//temprorary code to set gzip in htaccess
            self::rewrite_htaccess();
        }
    }

    public static function rewrite_htaccess($remove = false) {
        $wp_filesystem = Themify_Filesystem::get_instance();
        $htaccess_file = get_home_path() . '.htaccess';
        if ($wp_filesystem->execute->exists($htaccess_file) && $wp_filesystem->execute->is_writable($htaccess_file)) {
            $rules = $wp_filesystem->execute->get_contents( $htaccess_file );
            if (!$remove && strpos($rules, 'mod_deflate.c') === false && strpos($rules, 'mod_gzip.c') === false) {
                $content = $rules;
                $content .= TFCache::mod_rewrite('');
                return $wp_filesystem->execute->put_contents( $htaccess_file, $content );
            } elseif ($remove) {
                $rules = str_replace(TFCache::mod_rewrite(''), '', $rules);
                return $wp_filesystem->execute->put_contents( trim( $htaccess_file ), trim( $rules ) );
            }
        }
    }

    public static function disable_cache(){
        self::$turnoff_cache = !self::$turnoff_cache && class_exists( 'WooCommerce' ) && (is_checkout() || is_order_received_page() || is_wc_endpoint_url() || is_checkout_pay_page());
        if(self::$turnoff_cache){
            remove_filter('print_scripts_array', array(__CLASS__, 'scripts'), 9999);
            remove_filter('wp_head', array(__CLASS__, 'header_scripts'));
            remove_filter('print_styles_array', array(__CLASS__, 'styles'), 9999);
            remove_filter('script_loader_tag', array(__CLASS__, 'script_loader_tag'), 10);
            remove_filter('style_loader_tag', array(__CLASS__, 'style_loader_tag'), 10);
        }
        return self::$turnoff_cache ;
    }

}

if (TFCache::check_version()) {
    TFCache::$turnoff_cache = TFCache::is_cache_activate();
    if(!TFCache::$turnoff_cache){
        if (!is_admin() && !TFCache::is_ajax()) {
            $dirname = dirname(__FILE__);
            require_once $dirname . '/minify/minify.php';
            require_once $dirname . '/minify/css.php';
            require_once $dirname . '/minify/html.php';
            require_once $dirname . '/minify/converter.php';
            TFCache::wp_enque_scripts();
        }
        elseif (is_admin()) {
            TFCache::admin();
        }
    }
}
else {
    TFCache::$turnoff_cache = true;
}
