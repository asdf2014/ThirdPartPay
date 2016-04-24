<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Slider Portfolio
 * 
 * Access original fields: $settings
 * @author Themify
 */

$fields_default = array(
	'mod_title_slider' => '',
	'layout_display_slider' => '',
	'testimonial_category_slider' => '',
	'posts_per_page_slider' => '',
	'offset_slider' => '',
	'order_slider' => 'desc',
	'orderby_slider' => 'date',
	'display_slider' => 'content',
	'hide_post_title_slider' => 'no',
	'hide_feat_img_slider' => '',
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

if ( isset( $settings['testimonial_category_slider'] ) )	
	$settings['testimonial_category_slider'] = $this->get_param_value( $settings['testimonial_category_slider'] );

if ( isset( $settings['auto_scroll_opt_slider'] ) )	
	$settings['auto_scroll_opt_slider'] = $settings['auto_scroll_opt_slider'];

$fields_args = wp_parse_args( $settings, $fields_default );
extract( $fields_args, EXTR_SKIP );
$animation_effect = $this->parse_animation_effect( $animation_effect, $fields_args );

$class = $css_slider . ' ' . $layout_slider . ' module-' . $mod_name;
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
<!-- module slider testimonial -->
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
		
		<?php
		do_action( 'themify_builder_before_template_content_render' );

		$terms = $testimonial_category_slider;
		$temp_terms = explode(',', $terms);
		$new_terms = array();
		$is_string = false;
		foreach ( $temp_terms as $t ) {
			if ( ! is_numeric( $t ) )
				$is_string = true;
			if ( '' != $t ) {
				array_push( $new_terms, trim( $t ) );
			}
		}
		$tax_field = ( $is_string ) ? 'slug' : 'id';
		
		// The Query
		$args = array(
			'post_type' => 'testimonial',
			'post_status' => 'publish',
			'order' => $order_slider,
			'orderby' => $orderby_slider,
			'suppress_filters' => false
		);

		if ( $posts_per_page_slider != '' ) 
			$args['posts_per_page'] = $posts_per_page_slider;

		// tax query
		if ( count( $new_terms ) > 0 && ! in_array( '0', $new_terms ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'testimonial-category',
					'field' => $tax_field,
					'terms' => $new_terms
				)
			);
		}

		// add offset posts
		if ( $offset_slider != '' ) 
			$args['offset'] = $offset_slider;

		$args = apply_filters( 'themify_builder_slider_portfolio_query_args', $args );
		global $post;
		$temp_post = $post;
		$posts = get_posts( $args );
		if ( count( $posts ) > 0 ):
			foreach( $posts as $post ): setup_postdata( $post );
		?>

		<li style="<?php echo !empty($left_margin) ? 'margin-left:'.$left_margin.';' : ''; ?> <?php echo !empty($right_margin) ? 'margin-right:'.$right_margin.';' : ''; ?>">
			<?php
				$width = $img_w_slider;
				$height = $img_h_slider;
				$param_image = 'w='.$width .'&h='.$height.'&ignore=true';
				$attr_link_target = 'yes' == $open_link_new_tab_slider ? ' target="_blank"' : '';
				if ( $this->is_img_php_disabled() ) 
					$param_image .= $image_size_slider != '' ? '&image_size=' . $image_size_slider : '';
			
			if ( $hide_feat_img_slider == '' || $hide_feat_img_slider == 'no' ) {   
				// Display Featured Image
				if ( $post_image = themify_get_image( $param_image ) ){ ?>
					<?php themify_before_post_image(); // Hook ?>
					<figure class="slide-image">
						<?php echo wp_kses_post( $post_image ); ?>
					</figure>
					<?php themify_after_post_image(); // Hook ?>
				<?php } ?>
			
			<?php } ?>

			<?php if ( $hide_post_title_slider != 'yes' || $display_slider != 'none' ): ?>
			<div class="slide-content">
				
				<?php if ( $hide_post_title_slider == '' || $hide_post_title_slider == 'no'): ?>
					<h3 class="slide-title"><?php the_title(); ?></h3>
				<?php endif; // hide post title ?>

				<?php
				// fix the issue more link doesn't output
				global $more;
				$more = 0;
				?>
				
				<?php
				if ( $display_slider == 'content' ) {
					the_content();
				} elseif ( $display_slider == 'excerpt' ) {
					the_excerpt();
				}
				?>

				<p class="testimonial-author">
					<?php
						echo themify_builder_testimonial_author_name($post, 'yes');
					?>
				</p>

			</div>
			<!-- /slide-content -->
			<?php endif; ?>
		</li>
		<?php endforeach; wp_reset_postdata(); $post = $temp_post; ?>
	<?php endif; ?>

		<?php do_action( 'themify_builder_after_template_content_render' );  ?>

	</ul>
	<!-- /themify_builder_slider -->

</div>
<!-- /module slider portfolio -->