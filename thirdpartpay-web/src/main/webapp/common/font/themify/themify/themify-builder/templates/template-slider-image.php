<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Slider Image
 * 
 * Access original fields: $settings
 * @author Themify
 */
global $_wp_additional_image_sizes;

$fields_default = array(
	'mod_title_slider' => '',
	'layout_display_slider' => '',
	'display_slider' => 'content',
	'img_content_slider' => array(),
	'open_link_new_tab_slider' => 'no',
	'layout_slider' => '',
	'image_size_slider' => '',
	'img_w_slider' => '',
	'img_h_slider' => '',
	'visible_opt_slider' => '',
	'auto_scroll_opt_slider' => 0,
	'scroll_opt_slider' => '',
	'speed_opt_slider' => '',
	'pause_on_hover_slider' => 'resume',
	'effect_slider' => 'scroll',
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
<!-- module slider image -->
<div id="<?php echo esc_attr( $module_ID ); ?>-loader" class="themify_builder_slider_loader" style="<?php echo !empty($img_h_slider) ? 'height:'.$img_h_slider.'px;' : 'height:50px;'; ?>"></div>
<div id="<?php echo esc_attr( $module_ID ); ?>" class="<?php echo esc_attr( $container_class ); ?>">

	<?php if ( $mod_title_slider != '' ): ?>
		<?php echo $settings['before_title'] . wp_kses_post( apply_filters( 'themify_builder_module_title', $mod_title_slider, $fields_args ) ) . $settings['after_title']; ?>
	<?php endif; ?>

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
		data-pause-on-hover="<?php echo esc_attr( $pause_on_hover_slider ); ?>" >
		
		<?php foreach ( $img_content_slider as $content ): ?>
		<li style="<?php echo !empty($left_margin) ? 'margin-left:'.$left_margin.';' : ''; ?> <?php echo !empty($right_margin) ? 'margin-right:'.$right_margin.';' : ''; ?>">
			<?php if( ! empty( $content['img_url_slider'] ) ): ?>
			<div class="slide-image">
				<?php
				$image_url = isset( $content['img_url_slider'] )? esc_url( $content['img_url_slider'] ) : '';
				$image_w = $img_w_slider;
				$image_h = $img_h_slider;
				$image_title = isset( $content['img_title_slider'] )? $content['img_title_slider'] : '';
				if ( $alt_by_url = Themify_Builder_Model::get_alt_by_url( $image_url ) ) {
					$image_alt = $alt_by_url;
				} else {
					$image_alt = $image_title;
				}
				$param_image_src = 'src='.$image_url.'&w='.$image_w .'&h='.$image_h.'&alt='.$image_alt.'&ignore=true';
				if ( $this->is_img_php_disabled() ) {
					// get image preset
					$preset = $image_size_slider != '' ? $image_size_slider : themify_get('setting-global_feature_size');
					if ( isset( $_wp_additional_image_sizes[ $preset ]) && $image_size_slider != '') {
						$image_w = intval( $_wp_additional_image_sizes[ $preset ]['width'] );
						$image_h = intval( $_wp_additional_image_sizes[ $preset ]['height'] );
					} else {
						$image_w = $image_w != '' ? $image_w : get_option($preset.'_size_w');
						$image_h = $image_h != '' ? $image_h : get_option($preset.'_size_h');
					}
					$image = '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '" width="' . esc_attr( $image_w ) . '" height="' . esc_attr( $image_h ) . '">';
				} else {
					$image = themify_get_image( $param_image_src );
				}
				?>
				<?php if ( ! empty( $content['img_link_slider'] ) ): ?>
					<?php
					if( isset( $content['img_link_params'] ) && $content['img_link_params'] == 'lightbox' ) {
						$content['img_link_slider'] = themify_get_lightbox_iframe_link( $content['img_link_slider'] );
						$attr = ' class="themify_lightbox"';
					} elseif( isset( $content['img_link_params'] ) && $content['img_link_params'] == 'newtab' ) {
						$attr = ' target="_blank"';
					} else {
						$attr = '';
					}
					?>
				<a href="<?php echo esc_url( trim($content['img_link_slider'] )); ?>" alt="<?php echo esc_attr( $image_alt ); ?>"<?php echo $attr; ?>>
					<?php echo wp_kses_post( $image ); ?>
				</a>
				<?php else: ?>
					<?php echo wp_kses_post( $image ); ?>
				<?php endif; ?>
			</div>
			<!-- /slide-image -->
			<?php endif; ?>

			<?php if ( $image_title != '' || isset( $content['img_caption_slider'] ) ):  ?>
			<div class="slide-content">
				<?php if ( $image_title != '' ): ?>
				<h3 class="slide-title">
					<?php if ( isset( $content['img_link_slider'] ) && $content['img_link_slider'] != '' ): ?>
					<a href="<?php echo esc_url( $content['img_link_slider'] ); ?>"<?php echo 'yes' == $open_link_new_tab_slider ? ' target="_blank"': ''; ?>><?php echo wp_kses_post( $image_title ); ?></a>
					<?php else: ?>
					<?php echo wp_kses_post( $image_title ); ?>
					<?php endif; ?>
				</h3>
				<?php endif; ?>
				
				<?php 
					if( isset( $content['img_caption_slider'] ) ) {
						echo apply_filters( 'themify_builder_module_content', $content['img_caption_slider'] );
					}
				?>
			</div>
			<!-- /slide-content -->
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<!-- /themify_builder_slider -->

</div>
<!-- /module slider image -->