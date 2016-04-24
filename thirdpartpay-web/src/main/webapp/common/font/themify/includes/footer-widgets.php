<?php
/**
 * Template to load footer widgets.
 */
 
$footer_widget_option = ( '' == themify_get('setting-footer_widgets') ) ? 'footerwidget-3col' : themify_get('setting-footer_widgets');

if($footer_widget_option != 'none') { ?>
	<?php
	$columns = array('footerwidget-4col' => array('col4-1','col4-1','col4-1','col4-1'),
					 'footerwidget-3col' => array('col3-1','col3-1','col3-1'),
					 'footerwidget-2col' => array('col4-2','col4-2'),
					 'footerwidget-1col' => array('') );
	$x = 0;
	?>

	<div class="footer-widgets clearfix">

		<?php foreach($columns[$footer_widget_option] as $col): ?>
			<?php 
				 $x++;
				 if( 1 == $x ){ 
					  $class = 'first'; 
				 } else {
					  $class = '';	
				 }
			?>
			<div class="<?php echo $col;?> <?php echo $class; ?>">
				<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-widget-'.$x) ) ?>
			</div>
		<?php endforeach; ?>

	</div>
	<!-- /.footer-widgets -->

<?php } ?>