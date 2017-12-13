<script type="text/javascript">
	var is_mobile = '<?php print $is_mobile ? 1 : 0;?>';
	
	var item_id = '<?php print $item_id; ?>';
	var user_id = '<?php print $user_id; ?>';
	var items_views_id = '<?php print $items_views_id; ?>';
	
	$(document).ready(function() {
		// Resize for Mobile devices
		getCustomerSuppliedCode();
	});
	
	function getCustomerSuppliedCode()
	{
	
		$("#loading_code").css('display', 'inline');
		//	4.	Call ajax-customer-supplied-code helper.
		$.ajax({
			type: "POST",
			url: "/helpers/ajax-customer-supplied-code.php",
			dataType: 'json',
			data: {'user_id': user_id, 'item_id': item_id, 'items_views_id': items_views_id, 'app_name': 'convercial', 'skip_user_registration': '1', 'redeem_coupon_code': '<?php print $redeem_coupon_code;?>'}, // Because we want to claim AND redeem
			success: function(data){
				if(data['error'] == undefined)
				{
					// console.log('data when Getting Your Code was clicked: ');
					// console.log(data);

					custom_code = data[0];
					expired_date = data[1];
					formatted_date = expired_date.replace(" ", ",").replace("-", ",").replace("-", ",").replace(":", ",").replace(":", ",");
					split_date = formatted_date.split(",");
					new_date = new Date(split_date[0], split_date[1]-1, split_date[2], split_date[3], split_date[4], split_date[5]);
					expired_html = "Offer valid until " + " " + (new_date.getMonth()+1) + "/" + new_date.getDate() + "/" + new_date.getFullYear();

					csc_reveal_deal_content = $('#hdn-csc-reveal-deal-content-' + item_id).val();
					csc_reveal_deal_content = csc_reveal_deal_content.replace('customCode', custom_code);
					$('#reveal-deal').html(csc_reveal_deal_content);

					//	5.	Display Code.
					$("#expires").html(expired_html);
					$("#codes").html(custom_code);

					// Reposition the modal dialog
					// var selected_item_top = $("[name='btn-share'][data-rel='" + item_id + "']").offset().top - 100;
					// $('#reveal-deal').css('top', 0);

					$("#loading_code").css('display', 'none');

					$('#reveal-deal').css("display","normal");
					$('.code_page').slideDown();
					
					// Resize for Mobile devices
					// if(is_mobile == '1')
					{
						myImage = $('#reveal-deal img');
						//check if the image is already on cache
						if(myImage.prop('complete')){ 
							 //codes here
							 console.log("Image Retrieved from cache");
							 resizeImageAndCode();
						}else{
							 /* Call the codes/function after the image is loaded */
							 myImage.on('load',function(){
								  //codes here
								  console.log("Image loaded from scratch");
								  resizeImageAndCode();
							 });
						}
					}
					
					return false;
				}
				else
				{
					$('#reveal-deal').html(data['error']);
					// $('#reveal-deal').css('top', selected_item_top);

					$("#loading_code").css('display', 'none');

					$('#reveal-deal').css("display","inline");
					$('.code_page').slideDown();
				}
			},
			error: function(xhr, error){
				// console.log(xhr);
				$("#loading_code").css('display', 'none');
				alert('An Error occurred: ' + xhr.responseText);
			}
		}); // End Ajax Helper

	}
	
	function resizeImageAndCode()
	{
		var screenWidth = $(window).width();
		var screenHeight = $(window).height();
		var imgWidth, imgHeight;
		var codeWidth, codeHeight;
		var imgLeftOffset, imgTopOffset;
		var codeLeftOffset, codeTopOffset;
		var myImage;
		var heightCodeArea = 70; // Height of the lower rectangular area where the code is displayed.
		
		$('#reveal-deal h2').css('background-color', 'white');
		$('#reveal-deal h2').css('color', 'black');
		$('#reveal-deal h2').css('border', '1px solid black');
		$('#reveal-deal h2').css('padding', '5px');
		$('#reveal-deal h2').css('font-weight', 'normal');
		
		imgWidth = $('#reveal-deal img').width();
		imgHeight = $('#reveal-deal img').height();
		codeWidth = $('#reveal-deal h2').outerWidth();
		codeHeight = $('#reveal-deal h2').outerHeight();
		imgLeftOffset = (screenWidth - imgWidth) / 2;
		imgTopOffset = (screenHeight - imgHeight) / 2;
		codeLeftOffset = (imgWidth - codeWidth) / 2;
		codeTopOffset = imgHeight - codeHeight - 17;
		
		// console.log('screenWidth: ' + screenWidth + ', screenHeight: ' + screenHeight + ', imgWidth: ' + imgWidth + ', imgHeight: ' + imgHeight  + ', codeWidth: ' + codeWidth + ', codeHeight: ' + codeHeight +', imgLeftOffset: ' + imgLeftOffset + ', codeLeftOffset: ' + codeLeftOffset + ', codeTopOffset: ' + codeTopOffset);

		if(is_mobile == '1')
			$('#reveal-deal div').css('left', imgLeftOffset + 'px');
		
		$('#reveal-deal h2').css('left', codeLeftOffset + 'px');
		$('#reveal-deal h2').css('top', codeTopOffset + 'px');
		
		
	}
</script>

<div id="instore-container">
	<div class="body-bg">
	</div>
	<!-- Page Header begins here	-->
	<div class="banner-row row">
		<table class="small-12 columns">
			<tr>
				<?php if(!empty($bg_img)){ ?>
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
	
	
	
	
	<!-- Coupon Code Content begins here -->
	<div id="loading_code" style="display:none;position:absolute;top:50px;">
		<div class="loading_block">
		<?php if($is_mobile) { ?>
			<h4>Please wait, your unique code is now being generated...</h4>
		<?php } else { ?>
			<h1>Please wait, your unique code is now being generated...</h1>
		<?php }?>
		<img src="/images/loading_gray_transparent.gif" alt="loading..." />
		</div>
	</div>
	
	<!-- Code page-->
	<div id="reveal-deal" class="coupon_page_background code_page" style="display:none;">
		<div class="custom_art_header">
		</div>
		<div class="codeinfo">
			<h3>Use the code below to claim your deal:</h3>
			<p> Some details about the code's usage</p>
					<div class="codespace"><!--<button class="right"> Copy code to clipboard </button>--></div>
			<div class="coupon_controls style2">
				<button class="backtodeals">Ok, back to deals </button>
			</div>
		</div>
	</div>
	<!-- Coupon Code Content ends here-->
	
	<div class="buttons row" style="position:absolute;left:<?php print $btn_done_left;?>px; top:<?php print $btn_done_top;?>px;width:<?php print $btn_done_width;?>px;">
		<a id="btn_done" class="button letsmove success expand pushtop" href="instore?p=<?php print $item_id;?>&btn_done=1&items_views_id=<?php print $items_views_id;?>" data-items-views-id="<?php print $items_views_id;?>">
			Done
		</a>
	</div>
	
	
	<input type="hidden" id="hdn-csc-reveal-deal-content-<?php echo $item_id;?>" value="<?php print htmlentities($coupon_code_content);?>" />
</div>