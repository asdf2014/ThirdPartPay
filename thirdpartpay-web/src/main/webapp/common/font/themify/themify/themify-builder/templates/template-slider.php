<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
///////////////////////////////////////
// Switch Template Layout Types
///////////////////////////////////////
$template_name = isset($mod_settings['layout_display_slider']) && !empty($mod_settings['layout_display_slider']) ? $mod_settings['layout_display_slider'] : 'blog';
if(in_array($template_name,array('blog','portfolio','testimonial','slider'))){
    $this->in_the_loop = true;
}
if (TFCache::start_cache('slider', self::$post_id, array('ID' => $module_ID))) {
   
    $this->retrieve_template('template-' . $mod_name . '-' . $template_name . '.php', array(
        'module_ID' => $module_ID,
        'mod_name' => $mod_name,
        'settings' => ( isset($mod_settings) ? $mod_settings : array() )
            ), '', '', true);
}
TFCache::end_cache();
$this->in_the_loop = false;