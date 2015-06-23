<?php if(!empty($cart)): ?>
	<div class="col-md-7">
		<form method="POST" id="checkout-form" class="form-horizontal">
		<fieldset>
			<legend>Customer Information</legend>

			<div class="form-group">
				<label class="col-md-3 control-label" for="fullname">Name</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="fullname" id="fullname" placeholder="Full Name" requireda>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label" for="email">Email</label>
				<div class="col-md-9">
					<input type="email" class="form-control" name="email" id="email" placeholder="Email Address" requireda>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label" for="address1">Address</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="address1" id="address1" placeholder="Shipping Address" requireda>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label" for="address2">&nbsp;</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="address2" id="address2">
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label" for="textinput">City</label>
				<div class="col-md-9">
					<input type="text" placeholder="City" name="city" id="city" class="form-control" requireda>
				</div>
			</div>

			<?php if(sizeof(get_field('allowed_countries', 'option')) > 1): ?>
				<div class="form-group">
					<label class="col-md-3 control-label" for="textinput">Country</label>
					<div class="col-md-9">
						<select name="country" id="country" class="form-control" requireda>
							<?php foreach(get_field('allowed_countries', 'option') as $country): ?>
								<option value="<?php echo $country; ?>"><?php echo $country; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php else: ?>
				<input type="hidden" id="country" name="country" value="United States">
			<?php endif; ?>

			<div class="form-group">
				<label class="col-md-3 control-label" for="textinput">State/Province</label>
				<div class="col-md-9">
					<input type="text" placeholder="State/Province" name="state" id="state" class="form-control" requireda>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label" for="textinput">Postal code</label>
				<div class="col-md-9">
					<input type="text" placeholder="Postal Code" id="zipcode" name="zipcode" class="form-control">
				</div>
			</div>
		</fieldset>

		<fieldset>
			<legend>Credit Card</legend>

			<div class="form-group">
				<label class="col-md-3 control-label" for="card-number">Card Number</label>
				<div class="col-md-9">
					<input type="text" class="form-control" id="card-number" size="20" data-stripe="number" placeholder="Debit/Credit Card Number"/>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label" for="expiry-month">Expiration Date</label>
				<div class="col-md-3">
					<select class="form-control" id="expiry-month" data-stripe="exp-month">
						<option value="01">January</option>
						<option value="02">February</option>
						<option value="03">March</option>
						<option value="04">April</option>
						<option value="05">May</option>
						<option value="06">June</option>
						<option value="07">July</option>
						<option value="08">August</option>
						<option value="09">September</option>
						<option value="10">October</option>
						<option value="11">November</option>
						<option value="12">December</option>
					</select>
				</div>
				<div class="col-md-3">
					<select class="form-control" data-stripe="exp-year">
						<?php
							$startdate = date("Y");
							$enddate = date("Y", strtotime('+6 year')); 
							//echo $startdate;
							$years = range ($startdate, $enddate);
							foreach($years as $year){
								echo "<option value='".$year."'>".$year."</option>";
							}
						?>
						
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label" for="cvv">Card CVV</label>
				<div class="col-md-2">
					<input type="text" class="form-control" id="cvv" data-stripe="cvc" placeholder="Security Code">
				</div>
			</div>

			<div class="alert alert-danger col-md-offset-3" id="paymentErrors" style="display: none;"></div>

			<input type="hidden" name="shipping_name" id="shipping_name" value="">
			<input type="hidden" name="shipping_cost" id="shipping_cost" value="">
			<input type="hidden" name="shipping_speed" id="shipping_speed" value="">


			<div class="form-group">
				<div class="col-md-12">
					<button type="submit" class="btn btn-success center-block">Pay Now</button>
				</div>
			</div>

			<div class="payment-errors"></div>
		</fieldset>
	</form>
	</div>
	<div class="col-md-5">
		<h3 class="margin-top-none">Order Summary</h3>
		<div class="well">
			<?php
				$cart_subtotal = 0;
				$cart_shipping = 0;
				foreach($cart as $product_id => $products): foreach($products as $product):
					$price = get_field('base_price', $product_id);
					$cart_subtotal += ($price * $product['quantity']);
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
				<div class="row">
					<div class="col-sm-3 hidden-xs">
						<?php if($product_image): ?>
							<img src="<?php echo $product_image[0]; ?>" class='img-responsive' />
						<?php else: ?>
							<?php echo get_the_post_thumbnail( $product_id, 'square-thumb', array('class' => 'img-responsive') ); ?>
						<?php endif; ?>
					</div>
					<div class="col-sm-6">
						<p class="margin-top-none"><strong><?php echo get_the_title($product_id); ?></strong></p>
						<?php foreach($product['meta'] as $meta_key => $meta_value): ?>
						<p><strong><?php echo ucwords(str_replace('meta_', '', str_replace('-', ' ', $meta_key))); ?>:</strong> <?php echo ucwords(str_replace('-', ' ', $meta_value)); ?></p>
						<?php endforeach; ?>
					</div>
					<div class="col-sm-3 text-right">
						<p><?php echo $product['quantity'] .' x $'. ($price/100); ?></p>
					</div>

				</div>
				<hr>
			<?php endforeach; endforeach; ?>

			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-6"><h4 class="margin-top-none">Subtotal</h4></div>
						<div class="col-md-6 text-right">
							<p><strong>$<span class="ufstore-checkout-subtotal" data-subtotal="<?php echo $cart_subtotal; ?>"><?php echo number_format($cart_subtotal/100, 2); ?></span></strong></p>
						</div>
					</div>					
					<hr>
					<div class="row">
						<div class="col-md-6"><h4 class="margin-top-none">Shipping</h4></div>
						<div class="col-md-6 text-right">
							<p><strong><span class="ufstore-checkout-shipping"><small>Please enter a shipping address</small></span></strong></p>
						</div>
					</div>

					<div class="row" id="shipping-options-row">
						<div class="col-md-12">
							<div class="alert alert-danger" id="shippingError" style="display: none;"></div>
							<form id="shipping-options">
							</form>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12" id="shipping-flat-row">
						</div>
					</div>

					<hr>
					<div class="row" id="ufstore-checkout-total" style="display:none;">
						<div class="col-md-6"><h4 class="margin-top-none">Total</h4></div>
						<div class="col-md-6 text-right">
							<p><strong>$<span class="ufstore-checkout-total"></span></strong></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		

		<div class="row">
			<div class="col-md-12">
				<a href="<?php echo get_permalink($cart_page->ID); ?>" class="btn btn-warning"><i class="fa fa-angle-left"></i> Edit Shopping Cart</a>
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