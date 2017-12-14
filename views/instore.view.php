<style type="text/css">
</style>

<?php 
// if(!empty($enable_pixel_tracking)) {
// print '<script src="/js/tracking-pixel.js?rnd=' . md5(uniqid()) . '"></script>';
// } 
 ?>

<script type="text/javascript">
	var item_id = '<?php print $item_id; ?>';
	var company_id = '<?php print $company_id; ?>';
	var user_id = '<?php print $user_id; ?>';
	var items_views_id = '<?php print $items_views_id; ?>';
	// console.log('items_views_id: ', items_views_id);
	var delivery_method = '<?php print $delivery_method; ?>';
	var csc_email_template = '<?php print $csc_email_template; ?>';
	
	var back_url = '<?php print urlencode($redirect_uri);?>';
	var use_bundled_coupons = '<?php print $use_bundled_coupons;?>';
	
	$(document).ready(function() {
		$('#btn-use-offer, #btn-get-code').click(function() {
			// console.log('btn get offer clicked!'); 
			triggerURL('<?php print $trigger_url;?>');
			trackButtonClick('print_clicked', '1');
			$('#hdn_form_submit').val(1);
			$('#frm_submit').submit();
		});
		
		$(".workflow").click(function() {
			var div_to_hide = $(this).attr('data-hide');
			var div_to_show = $(this).attr('data-show');
			$('#' + div_to_hide).hide();
			$('#' + div_to_show).show();
		});
		
		// When Send to a different Email Address link is clicked
		$('a#change_email').click(function() {
			if($('.changeform').css('display')=='none'){
				$('.changeform').css("display","block");
				$('#send_email').addClass("disabled");
				$(this).html("Don't send to a different address");
			} else {
				$('.changeform').css("display","none");
				$('#send_email').removeClass("disabled");
				$(this).html("Send to a different email address");
			}
		});
		
		$('#proceed_with_print').click(function() {
			trackButtonClick('proceeded_with_print_email', '1');
			var url = '/helpers/facebook/application/print.php?item_id=' + item_id + '&user_id=' + user_id + '&items_views_id=' + items_views_id + '&app_name=convercial&company_url=' + back_url;
			if(use_bundled_coupons == '1')
				url = 'bundled-coupons?item_id=' + item_id + '&user_id=' + user_id + '&items_views_id=' + items_views_id + '&app_name=convercial&company_url=' + back_url + '&c=<?php print $_SESSION["bundled_coupons_code"]; ?>';
			top.location.href = url;
		});
		
		
		$('#btn_show_and_redeem_now').click(function() {
			var url = 'print-coupon-code?<?php print $str_query_string;?>';
			top.location.href = url;
		});
		
		$("#send_email, #btn-send-different-email").click(function(){
			var data_in = {};
			data_in['user_id'] = user_id;
			data_in['operation'] = 'instore_email';
			data_in['item_id'] = item_id;
			data_in['company_id'] = company_id;
			data_in['items_views_id'] = items_views_id;
			data_in['email'] = $('#user-email').html();
			data_in['csc_email_template'] = csc_email_template;
			

			var obj_id = $(this).attr('id');
			if(obj_id == 'btn-send-different-email')
			{
				var email_different = $("#txt-different-email").val();
			 	if(email_different == '')
			 	{
			 		alert('Please enter an email address!');
			 		return false;
			 	}
			 	else
			 	{
			 		data_in['email_different'] = email_different;
			 		data_in['email'] = email_different;
			 	}
			}
			data_in['delivery_method'] = delivery_method;
			// if(delivery_method == '12')
			// {
				// data_in['user_details'] = <?php print $json_user_details?>;
				// data_in['access_token'] = access_token;
				// data_in['permissions'] = str_required_permissions;
			// }
			data_in.stringify;
			$('#send_email').html('<i class="icon-refresh icon-spin"></i> Sending Email');
			
			/*basicAjax('json', "/helpers/ajax-instore.php", data_in, function(data){
				$("#email").hide();
				$("#thankyou").show();
			});*/
			
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '/helpers/ajax-instore.php',
				data: data_in,
				success: function(data) {
					console.log('data upon success: ', data);
					if(data != null && data['error'] != undefined)
					{
						alert(data['error']);
					}
					else
					{
						$('#div_email_sent').show();
						$('#div_email_for_later').hide();
					}
					$('#send_email').html('Send Email Now');
					$('#span_email_sent_to').html(data_in['email']);
					
				},
				error: function(data) {
					console.log('data upon error: ', data);
					alert("An error occurred while sending the email. Please see log.");
				}
			});
		});
		
		$("#btn-print-now, #btn-email-for-later").click(function() {
			var id = $(this).attr('id');
			switch(id)
			{
				// case 'btn-use-offer':
					// console.log('btn get offer clicked!'); 
					// triggerURL('<?php print $trigger_url;?>');
					// trackButtonClick('print_clicked', '1');
					// break;
				
				case 'btn-print-now':
					trackButtonClick('email_for_later_print_now', 'print_now');
					break;
				
				case 'btn-email-for-later':
					trackButtonClick('email_for_later_print_now', 'email_for_later');
					break;
			}
			
		});
	});

	function trackButtonClick(column_name, column_value)
	{
		$.ajax({
				type: 'post',
				dataType: 'json',
				url: '/helpers/ajax-instore.php',
				data: {'operation': 'track_button_click', 'column_name': column_name, 'column_value': column_value, 'items_views_id': items_views_id},
				success: function(data) {
					console.log('data upon success: ', data);
				},
				error: function(data) {
					console.log('data upon error: ', data);
				}
			});
	}
</script>
<script type="text/javascript" src="/js/trigger-tracking-pixel.js?rnd=<?php print md5(uniqid());?>"></script>

<div id="instore-container">
	<div class="body-bg">
	</div>
	<!-- Page Header begins here	-->
	<div class="banner-row row">
		<table class="small-12 columns">
			<tr>
				<?php if(false) { // HIDING THIS IMAGE FOR ALL DEALS if(!empty($bg_img)){ ?>
				<td style="align: left;">
						<img class="companylogo" src="<?php echo 'http://uploads.coupsmart.com.s3.amazonaws.com/' . $bg_img; ?>" alt="companylogo" />
				</td>
				<?php }	?>
				<td style="text-align:center;">
					<span class="companyname"><?php /*echo !empty($location_name) ? $location_name : $company->display_name ;*/ echo !empty($mo_header_caption) ? strtoupper($mo_header_caption) : $company_name ;?></span>
				</td>
			</tr>
		</table>
	</div>
	<!-- Page Header ends here	-->
		
	
	<!-- Deal content begins here	-->
	<div id="page1" class="row deals-body">
		<br /><br />
		<?php if($status != 'running') {
			if(!empty($mobile_placeholder_image))
			{
				print '<div class="row" id="div_deal_content"><img src="http://uploads.coupsmart.com/' . $mobile_placeholder_image . '" style="margin-left:auto; margin-right:auto; display:block;margin-top:auto;margin-bottom:auto;" /></div>';
			}
			else
			{
				print "Sorry, there are no running deals.";
			}
		}
		else { ?>
		<div class="row" id="div_deal_content" <?php print empty($fb_authentication_valid) ? "" : " style='display:none;'";?> >
			<div class="dealheader large-8 small-11 small-centered columns">
				<span id="span-item-name-<?php print $item_id; ?>" name="span-offer-value" data-rel="<?php print $item_id;?>"><?php print $deal_name;?> 
				</span>
			</div>
			<div class="deal large-8 small-11 small-centered columns zerolrpadding">
				<div class="row">
					<div  id="div-item-image-<?php print $deal['id'];?>" name="div-img-instore-deals" data-rel="<?php print $deal['id'];?>" class="small-12 large-6 columns offerimage" <?php print !empty($deal['img_instore_deals']) ? 'style="background-image:url(\'http://uploads.coupsmart.com/'. $deal['img_instore_deals']. '\');"' : ''; ?>>
						<div>
						</div>
					</div>
					<div class="small-12 large-6 columns dealbuttons zerolrpadding">
						<div class="row">
							<a name="btn-deal-details" data-reveal-id="detailsmodal" class="button details small-6 large-12 columns" data-rel="<?php print $deal['id'];?>" data-items-views-id="<?php print $items_views_id;?>" >
								Details
							</a>
							<a id="btn-use-offer" name="btn-use-offer" class="button success letsmove small-6 large-12 columns" data-rel="<?php print $deal['id'];?>" data-items-views-id="<?php print $items_views_id;?>">
								<?php print $caption_get_offer;?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
	<!-- Deal content ends here	-->
	
	<?php if(!empty($fb_authentication_valid)) { ?>
	<!-- The Deals Preview Section begins here -->
	<div id="div_deals_preview" class="row deals-body">
		<div class="row">
			<div class="dealheader large-8 small-11 small-centered columns">
				<span name="span-deal-name"><?php print $deal_name;?> </span>
			</div>
			<div class="deal large-8 small-11 small-centered columns">
				<div id="details2" class="details">
					<div class="buttons row pushtop">
						<?php if(!empty($instore_email_print_btn)) { ?>
						<a id="btn-email-for-later" class="success button letsmove expand workflow" data-show="div_email_for_later" data-hide="div_deals_preview" data-items-views-id="<?php print $items_views_id;?>">
							Email for Later
						</a>
						<?php } ?>
						<?php if($delivery_method == '12') { ?>
						<a id="btn-show-code-now" class="success button letsmove expand workflow" data-show="div_show_code_now" data-hide="div_deals_preview" data-items-views-id="<?php print $items_views_id;?>">
							Show Code Now
						</a>
						<?php } else if($delivery_method == '6') { ?>
						<a id="btn-print-now" class="success button expand letsmove workflow" data-show="div_print_now" data-hide="div_deals_preview" data-items-views-id="<?php print $items_views_id;?>">
							Print Now
						</a>
						<?php } ?>
					</div>
					<div class="buttons row">
						<a name="btn-back-to-deals" data-rel="page3" class="secondary button letsmove expand pushtop" href="<?php print $redirect_uri;?>" data-items-views-id="<?php print $items_views_id;?>">
							Back to Deals
						</a>
					</div>
				</div>
			</div>
		</div> 
	</div>
	<!-- The Deals Preview Section ends here -->
	<?php } ?>
	
	
	<!-- Div for EMAIL FOR LATER begins here -->
	<div id="div_email_for_later" class="row deals-body" style="display:none;">
		<div class="row">
			<div class="dealheader large-8 small-11 small-centered columns">
				<span name="span-deal-name"><?php print $deal_name;?> </span>
			</div>
			<div class="deal large-8 small-11 small-centered columns">
		  		<h5>This offer will be emailed to you to print at your convenience.</h5>
				<?php if(is_rfc3696_valid_email_address($user->email)) {?>
				<!--TEXT TO DISPLAY IF WE CAN PULL THEIR EMAIL FROM FACEBOOK PROFILE-->
						<div id="haveEmail">
						<p>Your email will be sent to the following address:&nbsp; <span id="user-email" class="emailaddress" style="font-weight:bold;"><?php echo $user->email; ?></span>
						</div>	
				<?php } else { ?>
				<!--TEXT TO DISPLAY IF WE CANNOT PULL THEIR EMAIL FROM FACEBOOK PROFILE-->	
						<div id="needEmail">
							<p>We don't have your email address on file! Please use the link below to enter it!</p>
						</div>	
				<?php } ?>	
					<br>
		  		<a id="change_email"> Send to a different email address </a></p>
		  		<div class="changeform" style="display:none;">
			  		<div class="row">
						<div class="large-7 small-12 columns pushtop">
				        	<input type="text" placeholder="New email address" id="txt-different-email" name="txt-different-email">
						</div>
						<div class="large-5 small-12 columns pushtop">
					  		<a href="#" id="btn-send-different-email" class="button small prefix" style="background-color:#1B89CA" data-items-views-id="<?php print $items_views_id;?>">Send to this address!</a>
						</div>
					</div>
				</div>
				<div class="buttons row">
					<a id="send_email" class="button success expand pushtop" style="background-color:#1B89CA" data-items-views-id="<?php print $items_views_id;?>"><i></i> Send Email Now </a>
					<a id="back_send_email" class="secondary button letsmove expand workflow" data-show="div_deals_preview" data-hide="div_email_for_later">
						Go Back
					</a>
				</div>
		  	</div>
		</div>
	</div>
	<!-- Div for EMAIL FOR LATER ends here -->


	<!-- Div for SHOW CODE NOW begins here -->
	<div id="div_show_code_now" style="display:none;">
		<div class="row">
			<div class="dealheader large-8 small-11 small-centered columns">
				<span name="span-deal-name"><?php print $deal_name;?> </span>
			</div>
			<div class="deal large-8 small-11 small-centered columns">
		  		<h5>You're about to use your deal.</h5>
				
				<div>
				<p>Only click the 'Show Now' button if you're ready to display and use your code in the store. You have a limited number of uses.
				</p>
				<p>If you're not ready to use your code now, you can email it to yourself to save it for later.</p>
		  		</div>	
				<div class="buttons row">
					<a id="btn_show_and_redeem_now" class="button success expand pushtop workflow" data-show="reveal-deal" data-hide="div_show_code_now"><i></i> Show & Redeem Now </a>
					<a id="btn_email_for_later" class="secondary button letsmove expand workflow" data-show="div_email_for_later" data-hide="div_show_code_now" data-items-views-id="<?php print $items_views_id;?>">
						Email for Later
					</a>
				</div>
		  	</div>
		</div>
	</div>
	<!-- Div for SHOW CODE NOW ends here -->

	
	<!-- Div for PRINT NOW begins here -->
	<div id="div_print_now" class="row deals-body" style="display:none;">
		<div class="row">
			<div class="dealheader large-8 small-11 small-centered columns">
				<span name="span-deal-name"><?php print $deal_name;?> </span>
			</div>
			<div class="deal large-8 small-11 small-centered columns">
				<div class="row">
					<div class="small-3 columns">
						<div class="redeemimg printimg">
						</div>
					</div>
					<div class="small-9 columns">
						<h6> You are about to print this deal. </h6>
						<span> Make sure you are on a device that you can easily print from (i.e. a tablet or personal computer that is connected to a printer). </span>
					</div>
				</div>
				<div class="buttons row">
					<a id="proceed_with_print" class="button letsmove success expand pushtop" href="#thankyou" data-items-views-id="<?php print $items_views_id;?>">
						Proceed
					</a>
				</div>
				<div class="buttons row">
					<a id='btn-go-back-print' class="secondary button letsmove expand back workflow" data-show="div_deals_preview" data-hide="div_print_now">
						Go Back
					</a>
				</div>
			</div>
		</div> 
	</div>
	<!-- Div for PRINT NOW ends here -->
	
	<!-- Div for EMAIL SENT begins here -->
	<div id="div_email_sent" class="row deals-body" style="display:none;">
		<div class="row">
			<div class="deal large-8 small-11 small-centered columns">
		  		<h5>Thanks!</h5>
				<div>
				<p>A link to claim your offer later has been emailed to: <span id="span_email_sent_to" class="emailaddress" style="font-weight:bold;"><?php print $user->email;?></span></p>
				<?php if(!empty($expiry) && $expiry != '0000-00-00') { ?>
					<p>Be sure to claim your deal by: <?php print date('M jS, Y', strtotime($item_info['expiry']));?></p>
				<?php } ?>
		  		</div>	
				<div class="buttons row">
					<a class="secondary button letsmove expand" href="<?php print $redirect_uri;?>">
						Go Back
					</a>
				</div>
		  	</div>
		</div>
	</div>
	<!-- Div for EMAIL SENT ends here -->
		

	<!-- Details Content begins here -->
	<div id="detailsmodal" class="reveal-modal medium">
		<div id="div-deal-background-image" class="coupon_image five" style="<?php print !empty($img) ? 'background-image: url(\''.$img.'\')' : '' ?>" rel="<?php print $img; ?>"></div>
		<div class="details">
			<h2> <?php print $deal_name;?> </h2>
			<p>
				<h4><?php print $offer_value;?></h4>
			</p>
			<span id="spandetails1"> 
				<?php print $details;?>
			</span>	
		</div>
		<a class="close-reveal-modal" style="font-size:1.1em;display:block;text-align:center;position:relative;top:0;right:0;text-decoration:underline;margin-top:15px;">Close Details</a>
		<a class="close-reveal-modal"><i class="icon-remove-sign"></i></a>
	</div>
	<!-- Details Content ends here -->
	
	
	<!-- Permissions Error Content begins here -->
	<?php if($isPermissionRejected) { ?>
	<div id="permissionsErrorModal" class="reveal-modal medium"  data-reveal>
		<div class="details">
			<h6> Please Accept App Permissions! </h6>
			<span> 
				<p>
				We use the info in your Facebook profile to provide you with more deals you like plus verify you're a real person.
				</p>
				<p>
					Click the blue "Continue As" button in the dialogue that appears when you try to claim the offer to accept them.
				</p>
			</span>	
		</div>
		<a class="close-reveal-modal" style="font-size:1.1em;display:block;text-align:center;position:relative;top:0;right:0;text-decoration:underline;margin-top:15px;">Back to Deal</a>
		<a class="close-reveal-modal"><i class="icon-remove-sign"></i></a>
	</div>
	<script>$('#permissionsErrorModal').foundation('reveal', 'open');</script>
	<?php } ?>
	<!-- Permissions Error ends here -->
	
	<form method='post' id='frm_submit' name='frm_submit'>
		<input type='hidden' id='hdn_form_submit' name='hdn_form_submit' />
	</form>
</div>