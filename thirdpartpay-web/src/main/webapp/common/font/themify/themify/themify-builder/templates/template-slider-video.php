<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Slider Text
 * 
 * Access original fields: $settings
 * @author Themify
 */

$fields_default = array(
	'mod_title_slider' => '',
	'layout_display_slider' => '',
	'display_slider' => 'content',
	'video_content_slider' => array(),
	'open_link_new_tab_slider' => 'no',
	'layout_slider' => '',
	'visible_opt_slider' => '',
	'auto_scroll_opt_slider' => 0,
	'scroll_opt_slider' => '',
	'speed_opt_slider' => '',
	'effect_slider' => 'scroll',
	'pause_on_hover_slider' => 'resume',
	'wrap_slider' => 'yes',
	'show_nav_slider' => 'yes',
	'show_arrow_slider' => 'yes',
	'left_margin_slider' => '',
	'right_margin_slider' => '',
	'css_slider' => '',
	'animation_effect' => '',
	'height_slider' => 'variable'
);

if ( isset( $settings['auto_scroll_opt_slider'] ) )	
	$settings['auto_scroll_opt_slider'] = $settings['auto_scroll_opt_slider'];

$fields_args = wp_parse_args( $settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );

$container_class = implode(' ', 
	apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $mod_name, $module_ID, 'themify_builder_slider_wrap', 'clearfix', $css_slider, $layout_slider, $animation_effect
	), $mod_name, $module_ID, $fields_args )
);
$visible = $visible_opt_slider;
$scroll = $scroll_opt_slider;
$auto_scroll = $auto_scroll_opt_slider;
$arrow = $show_arrow_slider;
$pagination = $show_nav_slider;
$left_margin = ! empty( $left_margin_slider ) ? $left_margin_slider .'px' : '';
$right_margin = ! empty( $right_margin_slider ) ? $right_margin_slider .'px' : '';
$effect = $effect_slider;

switch ( $speed_opt_slider ) {
	case 'slow':
		$speed = 4;
	break;
	
	case 'fast':
		$speed = '.5';
	break;

	default:
	 $speed = 1;
	break;
}
?>
<!-- module slider video -->
<div id="<?php echo esc_attr( $module_ID ); ?>-loader" class="themify_builder_slider_loader" style="<?php echo !empty( $img_h_slider ) ? 'height:'.$img_h_slider.'px;' : 'height:50px;'; ?>"></div>
<div id="<?php echo esc_attr( $module_ID ); ?>" class="<?php echo esc_attr( $container_class ); ?>">
	<?php if ( $mod_title_slider != '' ): ?>
		<?php echo $settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_slider, $fields_args ) ) . $settings['after_title']; ?>
	<?php endif; ?>

	<?php do_action( 'themify_builder_before_template_content_render' ); ?>
	
	<ul class="themify_builder_slider" 
		data-id="<?php echo esc_attr( $module_ID ); ?>" 
		data-visible="<?php echo esc_attr( $visible ); ?>" 
		data-scroll="<?php echo esc_attr( $scroll ); ?>" 
		data-auto-scroll="<?php echo esc_attr( $auto_scroll ); ?>"
		data-speed="<?php echo esc_attr( $speed ); ?>"
		data-wrap="<?php echo esc_attr( $wrap_slider ); ?>"
		data-arrow="<?php echo esc_attr( $arrow ); ?>"
		data-pagination="<?php echo esc_attr( $pagination ); ?>"
		data-effect="<?php echo esc_attr( $effect ); ?>" 
		data-height="<?php echo esc_attr( $height_slider ); ?>" 
		data-pause-on-hover="<?php echo esc_attr( $pause_on_hover_slider ); ?>" 
		data-type="video">
		
		<?php foreach ( $video_content_slider as $video ): ?>
		<li style="<?php echo ! empty( $left_margin ) ? 'margin-left:'.$left_margin.';' : ''; ?> <?php echo ! empty( $right_margin ) ? 'margin-right:'.$right_margin.';' : ''; ?>">
			<?php if ( ! empty( $video['video_url_slider'] ) ): ?>
			<?php $video_maxwidth = isset( $video['video_width_slider'] ) && ! empty( $video['video_width_slider'] ) ? $video['video_width_slider'] : ''; ?>
			<div class="slide-image video-wrap"<?php echo '' != $video_maxwidth ? 'style="max-width:' . esc_attr( $video_maxwidth ) . 'px;"' : ''; ?>>
				<?php echo wp_oembed_get( esc_url( $video['video_url_slider'] ) ); ?>
			</div>
			<!-- /video-wrap -->
			<?php endif; ?>
			
			<div class="slide-content">
				<h3 class="slide-title">
					<?php if ( isset( $video['video_title_link_slider'] ) && ! empty( $video['video_title_link_slider'] ) ): ?>
					<a href="<?php echo esc_url( $video['video_title_link_slider'] ); ?>"<?php echo 'yes' == $open_link_new_tab_slider ? ' target="_blank"': ''; ?>><?php echo wp_kses_post( $video['video_title_slider'] ); ?></a>
					<?php else : ?>
					<?php echo isset( $video['video_title_slider'] ) ? wp_kses_post( $video['video_title_slider'] ) : ''; ?>
					<?php endif; ?>
				</h3>
				<div class="video-caption">
					<?php 
						if ( isset( $video['video_caption_slider'] ) ) {
							echo apply_filters( 'themify_builder_module_content', $video['video_caption_slider'] );
						}
					?>
				</div>
				<!-- /video-caption -->
			</div>
			<!-- /video-content -->
		</li>
		<?php endforeach; // end loop video ?>
	</ul>
	<!-- /themify_builder_slider -->

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module slider video -->