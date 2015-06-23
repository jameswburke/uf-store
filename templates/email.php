<?php
$cart = UFStoreCart::getCart();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<!-- NAME: 1 COLUMN -->
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo get_field('store_title', 'option'); ?> Order Confirmation</title>
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="margin: 0;padding: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #F2F2F2;height: 100% !important;width: 100% !important;">
	<center>
		<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;margin: 0;padding: 0;background-color: #F2F2F2;height: 100% !important;width: 100% !important;">
			<tr>
				<td align="center" valign="top" id="bodyCell" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;margin: 0;padding: 20px;border-top: 0;height: 100% !important;width: 100% !important;">
					<!-- BEGIN TEMPLATE // -->
					<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;border: 0;">
						<tr>
							<td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
								<!-- BEGIN PREHEADER // -->
								<table border="0" cellpadding="0" cellspacing="0" width="600" id="templatePreheader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
									<tr>
										<td valign="top" class="preheaderContainer" style="padding-top: 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
										</td>
									</tr>
								</table>
								<!-- // END PREHEADER -->
							</td>
						</tr>
						<tr>
							<td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
								<!-- BEGIN HEADER // -->
								<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
									<tr>
										<td valign="top" class="headerContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
											<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
												<tbody class="mcnImageBlockOuter">
													<tr>
														<td valign="top" style="padding: 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" class="mcnImageBlockInner">
															<table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
																<tbody>
																	<tr>
																		<td class="mcnImageContent" valign="top" style="padding-right: 9px;padding-left: 9px;padding-top: 0;padding-bottom: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">


																			<img align="left" alt="" src="<?php echo ufstore_acf_image(get_field('email_header_image', 'option'), 'ufstore-email-banner'); ?>" width="558" style="max-width: 558px;padding-bottom: 0;display: inline !important;vertical-align: bottom;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" class="mcnImage">


																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								</table>
								<!-- // END HEADER -->
							</td>
						</tr>
						<tr>
							<td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
								<!-- BEGIN BODY // -->
								<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
									<tr>
										<td valign="top" class="bodyContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
											<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
												<tbody class="mcnTextBlockOuter">
													<tr>
														<td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">

															<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
																<tbody>
																	<tr>

																		<td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-right: 18px;padding-bottom: 9px;padding-left: 18px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">

																			<?php echo get_field('email_content', 'option'); ?>
																			<br/>

																			<table id="cart" class="table table-hover table-condensed" style="width: 100%;">
																				<thead>
																					<tr>
																						<th style="width:20%; text-align:right;"></th>
																						<th style="width:35%; text-align:left;"></th>
																						<th style="width:15%; text-align:right;"></th>
																						<th style="width:15%; text-align:right;" class="text-center">Subtotal</th>
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

																					$price = get_field('base_price', $product_id);
																					?>
																						<tr style="border-bottom: #CCC 1px solid;">
																							<td data-th="Product" style="vertical-align:top;">
																								<?php if($product_image): ?>
																									<img src="<?php echo $product_image[0]; ?>" class='img-responsive' />
																								<?php else: ?>
																									<?php echo get_the_post_thumbnail( $product_id, 'square-thumb', array('class' => 'img-responsive') ); ?>
																								<?php endif; ?>
																							</td>
																							<td data-th="Description" style="vertical-align:top;">
																									<p><strong><?php echo get_the_title($product_id); ?></strong></p>
																								<?php foreach($product['meta'] as $meta_key => $meta_value): ?>
																									<p><small><strong><?php echo ucwords(str_replace('meta_', '', str_replace('-', ' ', $meta_key))); ?>:</strong> <?php echo ucwords(str_replace('-', ' ', $meta_value)); ?></small></p>
																								<?php endforeach; ?>
																							</td>
																							<td data-th="Price" style="vertical-align: top; text-align:right;"><?php echo $product['quantity']; ?> x $<?php echo ($price/100); ?></td>
																							<td data-th="Subtotal" style="vertical-align:top; text-align:right;">$<?php echo (($price/100) * $product['quantity']); ?></td>
																						</tr>
																					<?php endforeach; endforeach; ?>
																				</tbody>
																				<tfoot>
																					<tr>
																						<td colspan="2">
																							<br />
																							<h3 style="margin-bottom: 0px; color: #606060;">Ship to</h3>
																							<?php echo $customer['name']; ?><br/>
																							<?php echo $customer['email']; ?><br/>
																							<?php echo $customer['address1']; ?><br/>
																							<?php if($customer['address2'] !== ''): ?>
																							<?php echo $customer['address2']; ?><br/>
																						<?php endif; ?>
																						<?php echo $customer['city']; ?>, <?php echo $customer['state']; ?> <?php echo $customer['zipcode']; ?>
																					</td>
																					<td style="text-align: right;" colspan="1">
																						<br />
																						<p><strong>Subtotal</strong></p>
																						<p><strong>Shipping</strong></p>
																						<p><strong>Total</strong></p>
																					</td>
																					<td style="text-align: right;" colspan="1">
																						<br />
																						<p>$<?php echo ($cart_subtotal/100); ?></p>
																						<p>$<?php echo ($cart_shipping/100); ?></p>
																						<p>$<?php echo ($cart_total/100); ?></p>
																					</td>
																				</tr>
																			</tfoot>
																		</table>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</table>
							<!-- // END BODY -->
						</td>
					</tr>
				</table>
			<!-- // END TEMPLATE -->
			</td>
		</tr>
	</table>
</center>
</body>
</html>