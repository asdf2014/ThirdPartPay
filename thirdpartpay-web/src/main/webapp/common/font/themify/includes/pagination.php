<?php
/**
 * Partial template for pagination
 */

/** Themify Default Variables
 *  @var object */
global $themify;

if ( 'numbered' == themify_get( 'setting-entries_nav' ) || '' == themify_get( 'setting-entries_nav' ) ) {
	themify_pagenav();
} else { ?>
	<div class="post-nav">
		<span class="prev"><?php next_posts_link(__('&laquo; Older Entries', 'themify')) ?></span>
		<span class="next"><?php previous_posts_link(__('Newer Entries &raquo;', 'themify')) ?></span>
	</div>
<?php 
}