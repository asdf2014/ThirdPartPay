<?php

/** Themify Default Variables
 *  @var object */
global $themify;

if(themify_check('setting-post_author_box')) { ?>

	<div class="clearfix author-box">

		<p class="author-avatar">
			<?php echo get_avatar( get_the_author_meta('user_email'), $themify->avatar_size, '' ); ?>
		</p>

		<div class="author-bio">

			<h4 class="author-name">
				<span>
				<?php // Check whether user url exists or not
					if(get_the_author_meta('user_url')){ ?>
						<a href="<?php echo get_the_author_meta('user_url'); ?>"><?php echo get_the_author_meta('first_name').' '.get_the_author_meta('last_name'); ?></a>
				<?php } else { ?>
						<?php echo get_the_author_meta('first_name').' '.get_the_author_meta('last_name'); ?>
					<?php } ?>
				</span>
			</h4>
				<?php echo get_the_author_meta('description'); ?>
				<?php // Check whether user url exists or not
					 if(get_the_author_meta('user_url')){ ?>
						<p class="author-link">
							<a href="<?php echo get_the_author_meta('user_url'); ?>">&rarr; <?php echo get_the_author_meta('user_firstname').' '.get_the_author_meta('user_lastname'); ?> </a>
						</p>
				<?php } ?>
		</div> <!-- / author-bio -->

	</div>	<!-- / author-box -->

<?php } ?>
