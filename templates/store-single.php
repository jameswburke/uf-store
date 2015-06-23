<?php get_header(); ?>
<div id="content-container" class="container">
	<div class="row">
		<div id="content" class="col-md-12">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<div class="row">
					<div class="col-md-6">						
						<?php ufstore_custom_featured_image('ufstore-large', array('class' => 'img-responsive', 'id' => 'ufstore-product-large-photo')); ?>

						<?php if(have_rows('meta')): while(have_rows('meta')): the_row(); ?>
							<?php if(have_rows('meta_colors')): $count = 0; while(have_rows('meta_colors')): the_row(); ?>
								<ul class="list-inline margin-top" data-ufstore-color-show="<?php echo sanitize_title(get_sub_field('color')); ?>" <?php if($count != 0): echo 'style="display:none;"'; endif; ?>>
									<?php if(have_rows('photos')): while(have_rows('photos')): the_row(); ?>
											<li><a href="#" data-ufstore-large-image="<?php echo custom_acf_image(get_sub_field('image'), 'ufstore-large'); ?>"><img src="<?php echo custom_acf_image(get_sub_field('image'), 'ufstore-tiny'); ?>" alt=""></a></li>
									<?php endwhile; endif; ?>
								</ul>
							<?php $count++; endwhile; endif; ?>

							<?php if(have_rows('photos')): while(have_rows('photos')): the_row(); ?>
							<?php  endwhile; endif; ?>

						<?php endwhile; endif; ?>

					</div>
					<div class="col-md-6">
						<h1><?php echo the_title(); ?></h1>
						<?php echo the_content(); ?>
						<h3>$<?php echo number_format(get_field('base_price')/100); ?></h3>
						<form role="form" class="cart-add">
							<input type="hidden" name="product_id" id="product_id" value="<?php the_ID(); ?>">
							<input type="hidden" name="action" id="action" value="add_to_cart">

							<!-- Loop through meta values -->
							<?php if(have_rows('meta')): while(have_rows('meta')): the_row(); ?>
								<div class="form-group">

									<!-- Text Field -->
									<?php if(get_sub_field('type') == 'text'): ?>
										<label for="product_meta_<?php echo sanitize_title(get_sub_field('title')); ?>"><?php echo get_sub_field('title'); ?></label>
										<input type="text" name="meta_<?php echo sanitize_title(get_sub_field('title')); ?>" id="meta_<?php echo sanitize_title(get_sub_field('title')); ?>" class="form-control">

									<!-- Drop Down -->
									<?php elseif(get_sub_field('type') == 'dropdown'): ?>
										<label for="product_meta_<?php echo sanitize_title(get_sub_field('title')); ?>"><?php echo get_sub_field('title'); ?></label>
										<select name="meta_<?php echo sanitize_title(get_sub_field('title')); ?>" id="meta_<?php echo sanitize_title(get_sub_field('title')); ?>" class="form-control" required>
											<option selected disabled value="">Select <?php echo get_sub_field('title'); ?></option>
											<?php
												$dropdown_options = explode(',', get_sub_field('dropdown_type'));
												foreach($dropdown_options as $option):
											?>
												<option value="<?php echo sanitize_title($option); ?>"><?php echo $option; ?></option>
											<?php endforeach;?>
										</select>

									<!-- Sizes -->
									<?php elseif(get_sub_field('type') == 'sizes'): ?>
										<label for="product_meta_<?php echo sanitize_title(get_sub_field('title')); ?>"><?php echo get_sub_field('title'); ?></label>
										<select name="meta_size" id="meta_size" class="form-control" required>
											<option selected disabled value="">Select <?php echo get_sub_field('title'); ?></option>
											<?php while(have_rows('meta_sizes')): the_row(); ?>
												<option value="<?php echo sanitize_title(get_sub_field('size')); ?>" <?php if(get_sub_field('inventory') == 0): echo 'disabled'; endif;?>><?php echo get_sub_field('size'); ?><?php if(get_sub_field('extra_cost')): echo ' (additional $'.get_sub_field('extra_cost').')'; endif;?><?php if(get_sub_field('inventory') == 0): echo ' - Out of Stock'; endif;?></option>
											<?php endwhile;?>
												
										</select>

									<!-- Colors + Sizes -->
									<?php elseif(get_sub_field('type') == 'colors'): ?>
										<?php
											$rows = get_sub_field('meta_colors' ); // get all the rows
											$first_row = $rows[0]; // get the first row
											$first_row_color = sanitize_title($first_row['color']);; // get the sub field value 
										?>

										<input type="hidden" name="meta_color" id="meta_color" value="<?php echo $first_row_color; ?>">
										<p><strong>Color:</strong> <span data-ufstore-current-color="<?php echo $first_row_color; ?>"><?php echo $first_row['color']; ?></span></p>
										<ul class="list-inline">
											<!-- display color selectors -->
											<?php if(have_rows('meta_colors')): $count = 0; while(have_rows('meta_colors')): the_row(); ?>
												<?php $color = sanitize_title(get_sub_field('color')); ?>
												<?php if(have_rows('photos')): while(have_rows('photos')): the_row(); ?>
														<li><a href="#" data-ufstore-new-color="<?php echo $color; ?>" data-ufstore-large-image="<?php echo custom_acf_image(get_sub_field('image'), 'ufstore-large'); ?>"><img src="<?php echo custom_acf_image(get_sub_field('image'), 'ufstore-tiny'); ?>" alt=""></a></li>
												<?php break; endwhile; endif; ?>
											<?php $count++; endwhile; endif; ?>
										</ul>

										<?php if(have_rows('meta_colors')): $count = 0; while(have_rows('meta_colors')): the_row(); ?>
											<?php $color = sanitize_title(get_sub_field('color')); ?>
											<?php if(have_rows('sizes')): ?>
												<div id="product_meta_size_<?php echo $color; ?>" data-ufstore-color-show="<?php echo $color; ?>" <?php if($count != 0): echo 'style="display:none;"'; endif; ?>>
													<label for="product_meta_size_<?php echo $color; ?>">Sizes</label>
													<select name="meta_size" id="meta_size_<?php echo $color; ?>" class="form-control" <?php if($count == 0): echo 'required'; endif; ?>>
														<option selected disabled value="">Select Size</option>
															<?php while(have_rows('sizes')): the_row(); ?>
																<option value="<?php echo sanitize_title(get_sub_field('size')); ?>" <?php if(get_sub_field('inventory') == 0): echo 'disabled'; endif;?>><?php echo get_sub_field('size'); ?><?php if(get_sub_field('extra_cost')): echo ' (additional $'.get_sub_field('extra_cost').')'; endif;?><?php if(get_sub_field('inventory') == 0): echo ' - Out of Stock'; endif;?></option>
															<?php endwhile; ?>
															
													</select>
												</div>
											<?php endif; ?>
										<?php $count++; endwhile; endif; ?>
									<?php endif; ?>

								</div>
							<?php endwhile; endif; ?>

							<div class="form-group">
								<label for="product_quantity">Quantity</label>
								<input type="number" name="product_quantity" id="product_quantity" class="form-control" value="1" min="1" max="100">
							</div>

							<div id="cart-add-form-alert" class="alert alert-success" role="alert" style="display:none;">
								<button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
								<p><strong>Thanks!</strong></p>
								<p>We've added this item to your shopping cart.</p>
							</div>

							<button type="submit" class="btn btn-success" id="add-to-cart">Add to Cart</button>
						</form>


					</div>
				</div>
			<?php endwhile; endif; ?>
		</div>
	</div>	
</div>
<?php get_footer(); ?>