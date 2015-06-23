<?php if ( $query->have_posts() ) : ?>
	<div class="row">
		<?php $count = 1; while ( $query->have_posts() ) : $query->the_post(); ?>
			<div class="col-md-3 text-center">
				<a href="<?php echo the_permalink(); ?>"><?php the_post_thumbnail('store-thumb', array('class' => 'img-responsive')); ?></a>
				<h4><a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a></h4>
				<p><strong>$<?php echo number_format(get_field('base_price')/100); ?></strong></p>
			</div>
			<?php if($count % 4 == 0): ?>
				</div>
				<div class="row margin-top">
			<?php endif; ?>
		<?php $count++; endwhile; ?>
	</div>
	<?php wp_reset_postdata(); ?>

<?php else : ?>
	<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
<?php endif; ?>


<?php wp_reset_postdata(); ?>