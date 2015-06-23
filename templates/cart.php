<?php if(!empty($cart)): ?>
	<div class="col-md-12">
		<h3>Your Cart</h3>
		<table id="cart" class="table table-hover table-condensed">
			<thead>
				<tr>
					<th style="width:50%">Product</th>
					<th style="width:10%">Price</th>
					<th style="width:8%">Quantity</th>
					<th style="width:17%" class="text-center">Subtotal</th>
					<th style="width:15%"></th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($cart as $product_id => $products): foreach($products as $product):
						$price = get_field('base_price', $product_id);

						//Check if colors exist
						foreach($product['meta'] as $meta_key => $meta_value){
							if($meta_key = 'color'){
								$meta = get_field('meta', $product_id);
								if($meta){
									foreach($meta as $m){
										if($m['meta_colors']){
											foreach($m['meta_colors'] as $colors){
												if($colors['color'] == ucwords(str_replace('-', ' ', $meta_value))){
													$product_image = wp_get_attachment_image_src( $colors['photos'][0]['image']['ID'], 'square-thumb');
												}
											}
										}
									}
									
								}
								
							}
						}
				?>
					<tr class="product-row" data-product-id="<?php echo $product_id; ?>" data-unique-id="<?php echo $product['unique_id']; ?>" data-product-price="<?php echo $price; ?>">
						<td data-th="Product">
							<div class="row">
								<div class="col-sm-3 hidden-xs">
								<?php if($product_image): ?>									
									<a href="<?php echo get_permalink($product_id); ?>"><img src="<?php echo $product_image[0]; ?>" class='img-responsive' /></a>
								<?php else: ?>
									<a href="<?php echo get_permalink($product_id); ?>"><?php echo get_the_post_thumbnail( $product_id, 'square-thumb', array('class' => 'img-responsive') ); ?></a>
								<?php endif; ?>
								</div>
								<div class="col-sm-9">
									<h4 class="nomargin"><a href="<?php echo get_permalink($product_id); ?>"><?php echo get_the_title($product_id); ?></a></h4>
									<?php foreach($product['meta'] as $meta_key => $meta_value): ?>
									<p><strong><?php echo ucwords(str_replace('meta_', '', str_replace('-', ' ', $meta_key))); ?>:</strong> <?php echo ucwords(str_replace('-', ' ', $meta_value)); ?></p>
									<?php endforeach; ?>
								</div>
							</div>
						</td>
						<td data-th="Price">$<?php echo number_format($price/100); ?></td>
						<td data-th="Quantity">
							<input type="number" class="product-quantity form-control text-center" value="<?php echo $product['quantity']; ?>" min="1">
						</td>
						<td data-th="Subtotal" class="subtotal text-center">$<?php echo number_format(($price * $product['quantity'])/100); ?></td>
						<td class="actions text-right" data-th="">
							<button class="cart-update btn btn-info btn-sm"><i class="fa fa-refresh"></i></button>
							<button class="cart-remove btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button>								
						</td>
					</tr>
				<?php endforeach; endforeach; ?>
			</tbody>
		</table>
		<div class="row">
			<div class="col-md-4 col-md-offset-8">
				<div class="row">
					<div class="col-md-6"><h4 class="margin-top-none">Subtotal</h4></div>
					<div class="col-md-6 text-right"><p><strong>$<span class="ufstore-cart-subtotal"></span></strong></p></div>
				</div>
				<hr>
				<a href="<?php echo get_permalink($checkout_page); ?>" class="btn btn-success pull-right">Checkout <i class="fa fa-angle-right"></i></a>
			</div>
		</div>

	</div>
<?php else: ?>
	<div class="col-md-12">
		<p>Your shopping cart is currently empty.</p>
		<?php if($store_page): ?>
			<a href="<?php echo get_permalink($store_page); ?>" class="btn btn-success"><i class="fa fa-angle-left"></i> View Store</a>
		<?php endif; ?>
	</div>
<?php endif; ?>