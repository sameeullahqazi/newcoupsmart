<?php
	$js_url = '/js/sd_sgs_share.php?app_link=' . urlencode($app_link) . '&booking_link=' . urlencode($booking_link) . '&url_to_share=' . urlencode($url_to_share) . '&referral_code=' . urlencode($referral_code);
	global $current_app;
?>
<!--[if lt IE 9]>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#like_required_content').hide();
			$('#like_required_txt_message').hide();
			$('#no_coupons_txt_message').hide();
			$('#notify_content').hide();
			$('#coupon_page_background').hide();
			$('#old-ie-content').show();
		});
	</script>
<![endif]-->

<script type="text/javascript" src="/js/trigger-tracking-pixel.js?rnd=<?php print md5(uniqid());?>"></script>
<script type="text/javascript" src="<?php echo $js_url ?>"></script>
<script type='text/javascript'>
	// console.log(window.top.opener.top);
	// if(window.top.opener.top != undefined)
	//      window.top.opener.top.close();
	
	var mypub_id = <?php echo json_encode($mypub_id); ?>;
	var striderite_id = <?php echo json_encode($striderite_id); ?>;
	var striderite_campaign_id = <?php echo json_encode($striderite_campaign_id); ?>;
	var e = new Log();
	var app = '<?php echo $current_app; ?>';
	var roomations_id = <?php echo $roomations_id; ?>;
	var mycompany_id = <?php echo $company_id; ?>;
	var requires_sharing = new Array();
	<?php if (isset($requires_sharing) && !empty($requires_sharing)) {
		foreach ($requires_sharing as $coupon_id => $sharing_required) {
			echo 'requires_sharing[' . $coupon_id . '] = ' . ($sharing_required ? 'true' : 'false') . ";\n";
		}
	} ?>
	var selected_coupon_id = null;
	var fb_user_data;
	var arr_fb_friends = {};
	var processing = false;
	var user_details;
	var booking_installed = '<?php echo $booking_installed; ?>';
	var num_friends_left = 0;
	var arr_friends_shared_with = [];
	var arr_share_methods = <?php print $json_share_methods;?>;
	var last_action_performed = '';
	var arr_item_views = {};
	var bees_on = '<?php echo $bees_on ? 1 : 0; ?>';
	var access_token;
	var custom_code;
	var arr_selector_data = []; // This will temporarily contain the selector data

	var is_notified = <?php echo json_encode($is_notified) ?>;
	var no_coupons = <?php echo json_encode($no_coupons) ?>;
	var fb_id = <?php echo isset($fb_uid) ? $fb_uid : 0 ?>;
	var new_referral_id;
	var app_version = '<?php print $app_version;?>';
	var load_testing = "<?php print $company->load_testing; ?>" === "1" ? true : false;
	
	var is_mypub = false;
	var is_striderite = false;
	if (mycompany_id instanceof Array) {
		is_mypub = mycompany_id.indexOf(mypub_id);
		is_striderite = mycompany_id.indexOf(striderite_id);
	} else {
		is_mypub = (mycompany_id == mypub_id) ? true : false;
		is_striderite = (mycompany_id == striderite_id) ? true : false;
	}


	function truncateName(full_name) {
		var max_len = 15;
		var names = new Array();
		var out_names = new Array();
		names = full_name.split(" ");
		$.each(names, function (index, value) {
			new_val = value;
			while (new_val.length > max_len) {
				new_val = new_val.substring(0, new_val.length-1);
			}
			if (new_val.length != value.length) {
				new_val = new_val + "…";
			}
			out_names.push(new_val);
		});
		return out_names.join(" ");
	}


	function filter(selector, query) {
		query	=	$.trim(query); //trim white space
		query = query.replace(/ /gi, '|'); //add OR for regex

		$(selector).each(function() {
			($(this).text().search(new RegExp(query, "i")) < 0) ? $(this).hide() : $(this).show();
		});
	}

	// var loopCount = 0;
	// var popup_exists;
	// var popup;


	function checkPopupBlocked(poppedWindow) {
	 setTimeout(function(){doCheckPopupBlocked(poppedWindow);}, 2000);
	}

	function doCheckPopupBlocked(poppedWindow) {

		 var result = false;

		 try {
		     if (typeof poppedWindow == 'undefined') {
		         // Safari with popup blocker... leaves the popup window handle undefined
		         result = true;
		     }
		     else if (poppedWindow && poppedWindow.closed) {
		         // This happens if the user opens and closes the client window...
		         // Confusing because the handle is still available, but it's in a "closed" state.
		         // We're not saying that the window is not being blocked, we're just saying
		         // that the window has been closed before the test could be run.
		         result = false;
		     }
		     else if (poppedWindow && poppedWindow.outerWidth == 0) {
		         // This is usually Chrome's doing. The outerWidth (and most other size/location info)
		      // will be left at 0, EVEN THOUGH the contents of the popup will exist (including the
		      // test function we check for next). The outerWidth starts as 0, so a sufficient delay
		      // after attempting to pop is needed.
		         result = true;
		     }
		     else if (poppedWindow && poppedWindow.test) {
		         // This is the actual test. The client window should be fine.
		         result = false;
		     }
		     else {
		         // Else we'll assume the window is not OK
		         result = true;
		     }

		 } catch (err) {
		     //if (console) {
		     //    console.warn("Could not access popup window", err);
		     //}
		 }

		if(result)
			alert("A Pop-Up Blocker was detected. Please disable it in order to use these deals.");
		else
			poppedWindow.close();
	}

	function setUserDealNotification(params)
	{
		params['app'] = '<?php echo $app_ns?>';
		$.ajax({
			type: "POST",
			url: "/helpers/ajax-user-set-notification.php",
			dataType: 'json',
			data: params,
			success: function(data){
			}
		});

		is_notified = params['notify_user'];
		setNotifications();
	}

	function setNotifications()
	{
		if(is_notified == 1){
			$('#msg_notify_me').hide();
			$('#btn_notify_me').hide();
			$('#msg_turn_off_notification').show();
			$('#btn_turn_off_notification').show();
		} else {
			$('#msg_notify_me').show();
			$('#btn_notify_me').show();
			$('#msg_turn_off_notification').hide();
			$('#btn_turn_off_notification').hide();
		}
	}

	function noNotifications()
	{
		$('#msg_notify_me').hide();
		$('#btn_notify_me').hide();
		$('#msg_turn_off_notification').hide();
		$('#btn_turn_off_notification').hide();
	}

	$(document).ready(function() {
		
		<?php if(!empty($company->load_testing)) { ?>
		if(top.opener)
		{
			var html = '';
			top.opener.time_for_view = (Date.now() - top.opener.current_time) / 1000;
			top.opener.num_views++;
			
			window.setTimeout(function() {
				var run_once = false;
				$('.coupon_controls_print').each(function(index, value)
				{
					if(!run_once)
					{
						top.opener.current_time = Date.now();
						$(this).trigger('click');
						run_once = true;
					}
				});
			}, 1500);
		}
		<?php } ?>
		// Styles the buttons accordingly
		var smart_deals_styled_buttons = '<?php print $smart_deals_styled_buttons;?>';
		if(smart_deals_styled_buttons == '1')
			$('.coupon_controls').addClass('style2');
		else
			$('.coupon_controls').removeClass('style2');


		// Temporary solution for FB.login() issue
		var print_url = $('#hdn_print_url').val();
		if(print_url != '')
		{
			window.location.href = print_url;
		}

		if(no_coupons){
			setNotifications();
		} else {
			//noNotifications();
		}

		var share_and_print = false;

		$('#btn_turn_off_notification').live('click', function(){
			var notify_user = '0';
			var params = {'user_id':<?php echo $user_id?> , 'fb_id': fb_id, 'notify_user': notify_user, 'company_id': '<?php echo $company_id;?>'};
			setUserDealNotification(params);
		});

		$('#btn_notify_me').live('click', function(){
			var user_registered = <?php echo ($user_registered) ? '1' : '0'; ?>;
			var access_token_exists = <?php echo ($access_token_exists) ? '1' : '0'; ?>;
			var notify_user = '1';

			var params = {'user_id':<?php echo $user_id?>, 'fb_id': fb_id, 'notify_user': notify_user, 'company_id': '<?php echo $company_id;?>'};

			// If user is not registered or access_token doesn't exist
			if(user_registered == '0' || access_token_exists == '0')
			{
				// Get Permissions
				var permissions = 'email';
				params['permissions'] = permissions;

				FB.login(function(response) {
					if(response.authResponse)
					{
						//	Get facebook user details
						access_token = response.authResponse.accessToken;
						var expires_in = response.authResponse.expiresIn;
						params['access_token'] = access_token;
						params['expire_time'] = expires_in;

						FB.api('/v' + app_version + '/me', function(response)
						{
							user_details = response;
							params['fb_user_details'] = user_details;
							setUserDealNotification(params);
						});
					}
				}, {scope: permissions});

			}
			else
			{
				params['user_id'] = '<?php echo $existing_user->id; ?>';
				params['access_token'] = '<?php echo $access_token; ?>';
				params['expire_time'] = '<?php echo $expire_time; ?>';
				params['permissions'] = '<?php echo $permissions; ?>';

				setUserDealNotification(params);
			}
		});

		// Show Message When User Denys App Access
		$('.trigger-permissions-rejected').click(function () {
			$('#permissions-rejected').reveal();
		});

		// Reveal Share Message
		<?php if($share_message_reveal) { ?>
			$('#share_message_reveal').reveal(animation: 'fade');
		<?php } ?>

		$("#fb_friendslist_search").keyup(function(event){
			if (event.keyCode == 27 || $(this).val() == '') {
				//if esc is pressed we want to clear the value of search box
				$(this).val('');

				// make each friend visible
				$('#friends-list-ul li').show();
			}else{
				filter('#friends-list-ul li', $(this).val());
			}

		});



		var layoutType =  "<?php print $smart_deals_layout;?>";

		/*Kara's functioning mockup code -- Causes Disappearing text in IE 8 */
		if (layoutType == 'grid') {
			$( '.coupon_controls_print' ).each(function ( i, box ) {
				var width = $( box ).width();
				html = '<span style="white-space:nowrap">';
				line = $( box ).wrapInner( html ).children()[ 0 ];
				n = 18;

				$( box ).css( 'font-size', n );
				while ( $( line ).width() > width ) {
					$( box ).css( 'font-size', --n + "px");
				}

				$( box ).text( $( line ).text() );
			});
		}

		$(".coupon_controls_details").click(function() {
			var sel_item_id = $(this).attr('data-rel');

			// Setting Item Name
			var item_name	= $('#span-item-name-' + sel_item_id).html().trim();
			var item_value	= $('#p-deal-offer-value-' + sel_item_id).html().trim();
			$('#h3-deal-title-details').html(item_name);
			$('#h4-deal-title-value').html(item_value);

			// Setting Item Description
			var item_description = $('#div-item-description-' + sel_item_id).html().trim();
			$('#p-deal-description').html(item_description);

			// Setting the Item image
			var picture = $("#div-item-image-" + sel_item_id).css('background-image').trim();
			// picture = picture.replace(/"/g,"").replace(/url\(|\)$/ig, "");
			$('#div-deal-background-image').css('background-image', picture);
			
			var banner_image_link_url = $('#hdn-banner-image-link-url-' + sel_item_id).val(); 
			if(banner_image_link_url != '')
			{
				var existing_banner_image_link_html = $('#div-deal-background-image').parent().html();
				// $('#div-deal-background-image').parent().html("<a href='" + banner_image_link_url + "' target='_blank'>" + existing_banner_image_link_html + "</a>");	
			}

			// Setting the Item Expiry date
			var expires = $("#hdn-expires-" + sel_item_id).val().trim();
			$('#span-deal-expiry-date').html(expires);

			var scroller = new Scroller();
			scroller.update();
			scroller.attachToModal('detailsmodal');
		  	$("#detailsmodal").foundation('reveal', 'open', {
		  		animation: 'fadeAndPop'
		 	 });
		  	$('.reveal-modal').css("display","inline");
		});


		$(".shareit").click(function() {
			$("#sharemodal").trigger('reveal:close');
			$("#sharesuccess").reveal({
				animation: 'fade'
			 });
			$('.reveal-modal').css("display","inline");
			setTimeout(function() {
				$("#sharesuccess").trigger('reveal:close');
			}, 2000);
		});

		// Checking whether any Saved Deals exist, hiding the Saved Deals section if none exist
		<?php print	empty($saved_deals_for_later) ? '$(".printsaveddeal").hide();' : '$(".printsaveddeal").delay(1000).fadeOut().fadeIn(1000);'; ?>


		var ddData = [
			{
				text: "On My Timeline",
				value: 1,
				selected: false,
				description: "Post this deal onto your wall.",
				imageSrc: "https://s3.amazonaws.com/siteimg.coupsmart.com/apps/method_selector_my_wall.png"
			},
			{
				text: "On a Friend's Timeline",
				value: 2,
				selected: false,
				description: "Post this deal onto your friend's wall.",
				imageSrc: "https://s3.amazonaws.com/siteimg.coupsmart.com/apps/method_selector_friends_wall.png"
			},
			{
				text: "In a Private Message",
				value: 3,
				selected: false,
				description: "Send this deal to a friend in a private message.",
				imageSrc: "https://s3.amazonaws.com/siteimg.coupsmart.com/apps/method_selector_private_message.png"
			},
			{
				text: "In a Group",
				value: 4,
				selected: false,
				description: "Post this deal into a group you are a member of.",
				imageSrc: "https://s3.amazonaws.com/siteimg.coupsmart.com/apps/method_selector_group.png"
			},
			{
				text: "On your Page",
				value: 5,
				selected: false,
				description: "Post this deal onto a Facebook page that you own.",
				imageSrc: "https://s3.amazonaws.com/siteimg.coupsmart.com/apps/method_selector_my_page.png"
			}
		];

//ddSlick is currently not being used...
		$('#selectshare').ddslick({
			data: ddData,
			width: 300,
			imagePosition: "left",
			selectText: "Select your share method..."
		});

		$('#selectshare').bind('click', function () {
			var ddData = $('#selectshare').data('ddslick');
			//console.log(ddData.selectedData.value);
			if(ddData.selectedData.value == 2){
				$('.recipient_box').css('display','none');
				$('.friends_wall').css('display', 'inline');
			} else if (ddData.selectedData.value == 3){
				$('.recipient_box').css('display','none');
				$('.friends_wall').css('display', 'inline');
			} else if(ddData.selectedData.value == 4){
				$('.recipient_box').css('display','none');
				$('.in_group').css('display', 'inline');
			}else if (ddData.selectedData.value == 5){
				$('.recipient_box').css('display','none');
				$('.your_page').css('display', 'inline');
			}else{
			$('.recipient_box').css('display','none');
			}
		});

		var share_method; // global var for setting sharing type

		$('#lnk-try-again').live('click', function(){

			// alert('last_action_performed: ' + last_action_performed);
			switch(last_action_performed)
			{
				case 'print':
					$(".coupon_controls_print[rel='0']").click();
					break;

				case 'share_to_print':
					$(".coupon_controls_print[rel='1']").click();
					break;

				case 'share':
					$('.coupon_controls_share').click();
					break;

				case 'confirm_share':
					$('#confirm_share').click();
					break;

				case '':
					location.reload(true);
					break;
			}

			// location.reload(true);
		});


	});

</script>

<link rel="stylesheet" media="screen and (min-width: 480px) and (max-width:768px)" type="text/css" href="/css/sd_outside_fb.css">
<!-- Step 1 - this div holds the content for facebook coupons to select from-->
<?php
$path = "/" . $upload_bucket . "/"; // 'https://s3.amazonaws.com/uploads.coupsmart.com/';
$img_width = 810;
$img_height = 606;
if(empty($coupons) || empty($company_id) || is_null($company_id)) {
	// If no coupons exist at all. We display the no-coupons-exist image
	
	//	SHOW LIKEBAR CONTENT IF IMAGE IS PRESENT
	if(!empty($company->likebar_image))
	{
		$likebar_content = Common::getLikebarContent($company->likebar_image, $company->facebook_page_id);
		print $likebar_content;
	}
	
	// Check for the image field name in the db
	if(!empty($company->img_no_coupons_exist)) {
		// Check for the image file location
		// $img = CacheImage::getImg($company->img_no_coupons_exist, $img_width, $img_height);
		$img = $company->img_no_coupons_exist;
		echo '<div id="like_required_content" style="height:auto;"><img id="like_required_txt_message" src="'.$path.$img.'" alt="" /></div>';
	} else {
		echo '
		<!--<div id="notify_content" class="open-notify WLcolor1">
			<div class="row">
				<div class="column seven">
					<div id="msg_notify_me">
						<p><span style="font-size:17px;">Let Us Notify You When Deals Become Available</span><br /><br>
						We will send you a message on Facebook when these deals go live.</p>
					</div>
					<div id="msg_turn_off_notification">
						<p><span style="font-size:17px;">We Will Notify You When Deals Become Available</span><br /><br>
						You are currently set to receive a message on Facebook when these deals go live.</p>
					</div>
				</div>
				<div class="">
					<br />
					<button id="btn_notify_me" class="notify button success" >Notify Me</button>
					<button id="btn_turn_off_notification" class="turn_off_notify button success">Turn Off Notification</button>
				</div>
			</div>
		</div>-->';
		echo $no_coupons_txt;
		// Lindt customization
		//if (in_array($company_id, $lindt_id)) {
		//	echo '<a class="lindt_banner_link" href="//www.facebook.com/LindtChocolate/app_292524714092254" target="_top"><img class="lindt_banner" src="' . $path . 'LindtBrandInteractBanner.jpg" width="520" height="74" alt="" /></a>';
	//	}
	}
} else { // We have coupons to show
	if ($company->require_like && !$liked) {
		// if user has not liked the page, we display the like-page image
		// Check for the image field name in the db
		echo '<div id="fb_like_button_container"><fb:like href="' . $company_page_link . '" layout="button" action="like" show_faces="false" share="false"></fb:like></div>';
		if(!empty($company->img_page_not_liked)) {
			// Check for the image file location
			// $img = CacheImage::getImg($company->img_page_not_liked, $img_width, $img_height);
			$img = $company->img_page_not_liked;
			echo '<div id="like_required_content" style="height:auto;"><img id="like_required_txt_message" src="'.$path.$img.'" alt="" /></div>';
		} else {
			echo $like_required_txt;
		}
		/*
		$page_link = "https://www.facebook.com/pages/Al-Burooj-Medical-Center/177479482288153";
		global $app_id;
		$query_string = http_build_query(array(
				'href' => $page_link,
				'send' => 'false',
				'layout' => 'standard',
				'width' => '450',
				'height' => '90',
				'show_faces' => 'false',
				'action' => 'like',
				'colorscheme' => 'light',
				'font' => 'arial',
				'appId' => $app_id
			));
		echo '<div class="small-3 columns likeButtonBigger">
						<iframe class="like_iframe" src="//www.facebook.com/plugins/like.php?' . $query_string . '" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:50px; height:80px; position: relative; left:20px;" allowTransparency="true"></iframe>
					</div>';*/
		// Lindt customization
		if (in_array($company_id, $lindt_id)) {
			echo '<br /><div class="lindt_footer"><div class="lindt_disclaimer"></div><div class="powered_by_canvas_block"><img class="powered_by_canvas" src="' . $powered_by_logo . '" alt="Powered by CoupSmart" /></div></div>';
		}

} else { ?>
		<div class="bannedcontent" style="display:none;">
				<div class="row">
					<div class="twelve columns">
						<div class="banned bannedexpand light" >
							<h5> Your account has been banned.</h5>
							<div class="row">
								<div class="six columns">
									<p> We have recorded fraudulent activity linked to your account. This includes one or more of the following:</p>
									<ul>
										<li>Copying coupons</li>
										<li>Printing more than allowed</li>
										<li>Going against the terms of an offered deal</li>
									</ul>
									<p>Such actions have resulted in the ineligibilty of your use of this app. </p>
								</div>
								<div class="six columns">
									<span>
										<b>Name:</b><span class="name"> Kara Loo </span><br>
										<b>Date fraud recorded:</b><span class="daterecorded"> 01/10/13</span><br>
										<b>Date account will be reactivated:</b><span class="reactivated"> 01/10/14 </span>
									</span><br><br>
									<span> <b>If you feel that this ban is in error or have a legitimate claim against this ban, you can <br>appeal <a href=''>here</a>.</b></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="twelve columns">
						<div class="banned bannedexpand heavy">
							<h5> Your account has been banned due to blatant fraudulent activity.</h5>
							<div class="row">
								<div class="eight columns">
									<p>This includes one or more of the following:</p>
									<ul>
										<li>Copying coupons</li>
										<li>Printing more than allowed</li>
										<li>Going against the terms of an offered deal</li>
									</ul>
									<p><b>Such actions have resulted in the ineligibilty of your use of this app.</b> </p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<div class="coupon_page_background"  id="coupon_page_list">
			<!--<a href="#" class="trigger-permissions-rejected">Trigger Permissions Denied Message</a>-->

			<div class="custom_art_header">

				<div class="printsaveddeal" <?php print !empty($saved_deals_for_later) ? "" : "style='display:none;'";?>>
					<p> You have one or more saved deals waiting for you to print!
					<ul>
						<?php if (!empty($saved_deals_for_later)) {
							foreach($saved_deals_for_later as $saved_deal) {?>
						<li><a data-item-id="<?php print $saved_deal['item_id'];?>" data-saved-for-later-id="<?php print $saved_deal['saved_for_later_id'];?>">Click here to print your saved deal now </a></li>
						<?php }
						} ?>
					</ul>
					</p>
				</div>

				<!--
				<div class="fb_iframe_like">
					<span>You must like our page to view these deals: </span>
					<fb:like href="http://www.facebook.com/pages/The-Coupon-Galleries/469897963041589?sk=app_171818396196950"  width="260" height="70" data-show-faces="false" data-layout="button_count" class="fb_iframe_likebutton"></fb:like-box>
				</div>
				<div class="next_page_temp">
					<button class="nextpage" style="display:block; width:100px; float:right; clear:right;margin:200px 10px 0px 0px; cursor:pointer;padding:10px 5px; ">Next Page</button>
				</div>
				-->
			</div>
			<div id="deals_<?php print $smart_deals_layout;?>" class="">
				<ul id="coupon_page" class="coupon_page">
					<?php
					// $start_time = array_sum( explode( ' ' , microtime() ) ); //gives a more exact time since this view is called in less than one second
					foreach($coupons as $i => $coupon) {
						// $iteration_start_time = array_sum( explode( ' ' , microtime() ) );
						// error_log('coupon = ' . var_export($coupon, true));
						$class_li = '';

						$caption_print_btn = $translations['Print Deal'];
						$trigger_url = $coupon['trigger_url'];

						if (isset($coupon['claim_button_text']) && !empty($coupon['claim_button_text'])) {
							$caption_print_btn = $translations[$coupon['claim_button_text']];
						} else {
							if (in_array($coupon['delivery_method'], array(7,10,15))) {
								$caption_print_btn = $translations['Use Deal'];
							}
							if ($coupon['delivery_method'] == 8) {
								$caption_print_btn = 'Book Deal';
							}
							if ($coupon['delivery_method'] == 9) {
								$caption_print_btn = 'Claim Deal';
							}
							if ($coupon['delivery_method'] == 11) {
								$caption_print_btn = 'Claim Deal';
							}
						}

						$rel_attr = "";
						$featured_image = '';
						// error_log("coupon['is_featured']: ".var_export($coupon['is_featured'], true));
						// Whether its a featured coupon or not
						if($coupon['is_featured'] == '1')
						{
							$class_li = 'featured';
							$featured_image = "background-image:url('" . $path . $coupon['featured_image'] ."');";

						}

						// Whether its a share coupon or  not
						$sharing_bonus = is_share_bonus_coupon($coupon);

						if($sharing_bonus)
						{
							if(empty($class_li))
								$class_li = 'share';
							else
								$class_li = 'featured share';

							$caption_print_btn = $translations['Share to Print Deal'];
							if (isset($coupon['claim_button_text']) && !empty($coupon['claim_button_text'])) {
								$caption_print_btn = $translations[$coupon['claim_button_text']];
							} else {
								if ($coupon['delivery_method'] == 7 || $coupon['delivery_method'] == 8) {
									$caption_print_btn = $translations['Share to Use Deal'];
								}
								if ($coupon['delivery_method'] == 8) {
									$caption_print_btn = 'Share to Book Deal';
								}
								if ($coupon['delivery_method'] == 9) {
									$caption_print_btn = 'Share to Claim Deal';
								}
								if ($coupon['delivery_method'] == 11) {
									$caption_print_btn = 'Share to Claim Deal';
								}
							}
						}

						// Coupon Title
						$offer_value = !empty($coupon['offer_code']) ? $coupon['value'] : $coupon['offer_value'];
						$deal_name = $coupon['name'];
						// Coupon Description
						$small_type	= $coupon['small_type'];

						// Coupon Details
						$details 	= $coupon['details'];

						// Coupon Expiry Date
						$expires = "N/A";
						if(!empty($coupon['expires'])){
							if (in_array($company_id, $mypub_id)){ //if it is the mypublisher campaign
								//error_log("hit inside my pub");
								$expires = "Your coupon code will expire on February 28th, 2013.";
							} else if ($company_id == $roomations_id) {
								$expires = "Your coupon code will expire on Monday November 26th at 11:59 PM EST.";
							} else{
								$expires	= date('m/d/Y', strtotime($coupon['expires']));
							}

							/* if($coupon['delivery_method'] == '11')
							{
								$expires = $csc_expires;
							} */
						}
						else if(!empty($coupon['expire_month']) && !empty($coupon['expire_year']) )
							$expires = $coupon['expire_month'] . ' - ' . $coupon['expire_year'];

						// Coupon Image
						$img_width = 372;
						$img_height = 175;

						/*
						$logo_path = '';
						switch($coupon['use_deal_voucher_image'])
						{
							case 'yes_own':
								$logo_path = "http://uploads.coupsmart.com/" . CacheImage::getImg($coupon['logo_file_name'], $img_width, $img_height);
								break;

							case 'yes_fb_photo':
								$logo_path = "https://graph.facebook.com/v" . $app_version . "/".$coupon['facebook_page_id']."/picture?img_width=$width&height=$img_height";
								break;

							case 'yes_company_logo':
								$logo_path = "http://uploads.coupsmart.com/" . CacheImage::getImg($coupon['default_coupon_image'], $img_width, $img_height);
								break;

							default:

						}
						*/

						$img = !empty($coupon['image_file_preview']) ? $path . $coupon['image_file_preview'] : null;
						$img_sharing = !empty($coupon['img_sharing']) ? $path . $coupon['img_sharing'] : '';
						// $start_switch = array_sum(explode(" ", microtime()));

						switch($coupon['use_preview_deal_voucher_image'])
						{
							case 'yes_own':
							case 'yes_company_logo':
								$img = !empty($coupon['img_fan_deals']) ? $path . $coupon['img_fan_deals'] : null;
								break;


							case 'yes_fb_photo':
								$img = "https://graph.facebook.com/v" . $app_version . "/$page_id/picture?width=$img_width&height=$img_height";
								break;


							case 'voucher_img':
								switch($coupon['use_deal_voucher_image'])
								{
									case 'yes_own':
									case 'yes_company_logo':
										$img = !empty($coupon['img_fan_deals']) ? $path . $coupon['img_fan_deals'] : null;
										break;


									case 'yes_fb_photo':
										$img = "https://graph.facebook.com/v" . $app_version . "/$page_id/picture?width=$img_width&height=$img_height";
										break;

									default:
								}
								break;

							default:

						}
						// error_log("img for coupon $coupon['id'] in coupsmart view: " . var_export($img, true));
						
						$style_button_color = "";
						
						if(!empty($coupon['button_color']) || !empty($coupon['button_text_color']))
						{
							$style_button_color = "style='";

							if(!empty($coupon['button_color']))
								$style_button_color .= "background-color:" . $coupon['button_color'] . ";";

							if(!empty($coupon['button_text_color']))
								$style_button_color .= "color:" . $coupon['button_text_color'] . ";";
								
							$style_button_color .= "'";
						}

						$silver_pop_click_info_id = isset($silver_pop_click_info['id']) ? $silver_pop_click_info['id'] : 0;
						$smart_link_id = isset($smart_link_click_info['smart_link_id']) ? $smart_link_click_info['smart_link_id'] : 0;
						$item_view_id = Item::addItemView($coupon['id'], $coupon['comp_id'], $user_id, $smart_link_id, $silver_pop_click_info_id, $referral_id, $shortened_url_hit_id);
						
						if($company->is_campaign_monitor_company == '1' && !empty($coupon['cm_list_id']))
							CampaignMonitor::checkAndUpsertMember($user_id, $coupon['cm_list_id'], $company->cm_client_id, $company->cm_api_key, $coupon['deal_id'], $company_id);

						// UserActivityLog::log_user_activity($user_id, 'viewed', 'fan_deals', $coupon['id']);

					?>
					 <script type='text/javascript'>
						var item_view_id = "<?php print $item_view_id; ?>";
						var item_id = "<?php print $coupon['id']; ?>";
						arr_item_views[item_id] = item_view_id;
					 </script>
					<li class="module<?php print !empty($class_li) ? ' ' . $class_li : ''; ?>" rel="<?php echo $coupon['delivery_method'];?>" id="c<?php print $coupon['id']; ?>" style="<?php echo $featured_image; ?>">
					<div class="row">
						<ul class="share_methods" style="display:none;" data-rel="<?php print $coupon['id']; ?>">
							<li class="sharemethod own_wall"><div></div>My wall </li>
							<li class="sharemethod friend_wall"><div></div>Friend's wall</li>
							<li class="sharemethod private_message"><div></div>Private Message</li>
							<li class="sharemethod own_group"><div></div>My Group</li>
							<li class="sharemethod own_page"><div></div>My Page</li>
						</ul>
						<div id="div-item-image-<?php print $coupon['id']; ?>" class="coupon_image five columns" <?php print !empty($img) ? 'style="background-image: url(\'' . $img . '\')" rel="' . $img . '"' : '' ?>></div>
						<input type="hidden" id="hdn-img-sharing-<?php print $coupon['id']; ?>" value="<?php print $img_sharing;?>" />
							<div class="coupon_info seven columns">
								<div class="row">
									<div class="coupon_data twelve columns">
										<div class="coupon_data_info">
											<h2 id="span-item-name-<?php print $coupon['id']; ?>">
												<?php print $deal_name;?>
											</h2>
											<p id="p-deal-offer-value-<?php print $coupon['id']; ?>">
												<?php print $offer_value;?>
											</p>
											<p style="display:none;" id="div-item-description-<?php print $coupon['id']; ?>"><?php print $small_type;?></p>
										</div>

									</div> <!--coupon_data-->
								</div>
								<div class="row" data-rel="<?php print $coupon['id']; ?>">
									<div class="coupon_controls ia style2 twelve">
										<button class="coupon_controls_print" rel="<?php print $sharing_bonus ? '1' : '0';?>" <?php print $sharing_bonus ? "style='display:none;'" : "";?> data-item-id="<?php print $coupon['id'];?>" onclick='javascript:triggerURL("<?php print $trigger_url;?>");' <?php print $style_button_color;?>><?php print $caption_print_btn;?></button>
										<!-- mypub button customization -->
										<?php // if (in_array($company_id, $mypub_id) || in_array($company_id, $striderite_id)) {
											if(empty($coupon['hide_share_button'])) { ?>
											<!--<button class="coupon_controls_share_stride"><?php echo $translations['Share to Claim Deal']; ?></button>-->
											<button class="coupon_controls_share" <?php print $sharing_bonus ? "style='width:55%'" : "";?> name="btn-share" data-rel="<?php print $coupon['id']; ?>" data-share-to-print="<?php print $sharing_bonus ? '1' : '0';?>" <?php print $style_button_color;?>><?php echo $sharing_bonus ? $translations[$caption_print_btn] : $translations['Share']; ?></button>
										<?php } ?>
										<button class="coupon_controls_details" data-rel="<?php print $coupon['id']; ?>" <?php print $style_button_color;?>><?php echo $translations['Details']; ?></button>
									</div>
								</div>
							</div><!--seven columns-->
						</div><!--row-->


						<input type="hidden" id="hdn_<?php echo $coupon['id'];?>" name="hdn-item-name" data-rel="<?php echo $coupon['id'];?>" value="<?php echo(htmlentities($deal_name));?>" />
						<input type="hidden" id="hdn-campaign-id-<?php echo $coupon['id'];?>" value="<?php echo(htmlentities($coupon['campaign_id']));?>" />
						<input type="hidden" id="hdn-delivery-method-<?php echo $coupon['id'];?>" value="<?php echo(htmlentities($coupon['delivery_method']));?>" />
						<input type="hidden" id="hdn-expires-<?php echo $coupon['id'];?>" value="<?php echo(htmlentities($expires));?>" />
						<input type="hidden" id="hdn-show-print-options-<?php echo $coupon['id'];?>" value="<?php print $coupon['show_print_options'];?>" />
						<input type="hidden" id="hdn-csc-reveal-deal-content-<?php echo $coupon['id'];?>" value="<?php print $coupon['delivery_method'] == '11' ? htmlentities($coupon['csc_reveal_deal_content']) : '';?>" />
						<input type="hidden" id="hdn-banner-image-link-url-<?php echo $coupon['id'];?>" value="<?php print htmlentities($coupon['banner_image_link_url']);?>" />
					</li>
					<?php

				} // End foreach - All Coupons displayed and viewed

				// Check if this is a Silverpop Company
				if(!empty($company->is_silverpop_company))
				{
					if(empty($company->sp_is_ubx))
					{
						$start_time = time();
						// Check for user email and Upsert the contact here
						$sp_recipient_id = SilverPop::checkAndUpsertContact($user_id, $company_id);
						$time_upsert_contact = time() - $start_time;

						$start_time = time();
						// And if the Upsert was successful, POST a UB Event
					
						if(!empty($sp_recipient_id))
							SilverPop::triggerViewedOffer($company_id, $sp_recipient_id, $user_id, $page_id, $facebook_page_name);
					}
					else
					{
						// TODOS: Call UBX version of the Event
						UBX::triggerViewedOffer($company_id, UBX::$channel_social, $user_id, $page_id, $facebook_page_name, $coupons);
					}
					$time_trigger_sp_view = time() - $start_time;
				}
				
				if($company->is_et_company == '1')
					ExactTarget::checkAndUpsertSubscriber($user_id, $company_id, array('action' => 'view', 'facebook_page' => $facebook_page_name, 'facebook_page_id' => $page_id));
				
				if($company->is_mailchimp_company == '1' && !empty($company->mc_list_id))
					MailChimp::checkAndUpsertMember($user_id, $company->mc_list_id, $company->mc_api_key, $company_id);


				// Trigger the Silver Pop Viewed Event here
				if($trigger_sp_event_viewed)
				{
					$recipient_id = $silver_pop_click_info['sp_contact_id_unencoded'];
				}



				?>
				</ul>
			</div><!-- deals_vertical deals_grid-->
			<?php if ($company->show_fb_comments) { ?>
				<div class="comments_section">
					<h5> Let us know how you like these deals: </h5>
					<div id="fb-root"></div>
					<script src="https://connect.facebook.net/en_US/sdk.js#xfbml=1" type="text/javascript"></script>
					<fb:comments href="<?php print $app_link;?>" num_posts="4" width="700" class="fb_iframe_widget"></fb:comments></div>
				</div>
			<?php } ?>

			<div class="signup" style="display:none;">
				<h3>Want to be notified when our deals become available? </h3>
				<span>Sign up here we'll let you know (No spam! Just deal notifications!)</span>
				<div class="coupon_controls">
					<!--<input type="text" id="emailinput" placeholder="Type your email address..."/>-->
					<button> I want to be notified! </button>
				</div>
			</div>
			<?php
				// Lindt customization
			  if (in_array($company_id, $lindt_id)) {
				echo '<br />
				<div class="lindt_footer">
				<div class="lindt_disclaimer">
				Should you have any technical issues, please contact <a href="mailto:support@coupsmart.com">support@coupsmart.com</a><br />
				For questions or instructions about this deal, visit the <a href="//www.lindtusa.com/lindtcoupontroubleshooting" target="_blank">Lindt Troubleshooting Guide</a>.
				</div>
				<div class="powered_by_canvas_block">
				<img class="powered_by_canvas" src="' . $powered_by_logo . '" alt="Powered by CoupSmart" />
				</div>
				</div>';
				} ?>
		<!--</div>-->
	<?php }
}
?>

<!-- Plain HTML page-->
<div class="coupon_page_background custom_html" style="height:100px; background:#eee; display:none;">

</div>

<div id="loading_code" style="display:none;">
	<div class="loading_block">
	<h1>Please wait, your unique code is now being generated...</h1>
	<img src="//s3.amazonaws.com/siteimg.coupsmart.com/general/loading_gray.gif" alt="loading..." />
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

<!-- The Second Print Page-->
<div class="coupon_page_background" id="coupon_print_page" style="display:none;">
	<div class="header">
		<h4 id="h4-deal-name-print-options"> Print My Deal </h4>
		<div class="row">
			<div class="two columns">
				<img id="img-deal-image-print-options" class="dealimg" src="" alt="" />
			</div>
			<div class="eight column end">
				<span class="deal_description" id="span-deal-description-print-options"> The deal description and details are reiterated here. The deal description and details are reiterated here. The deal description and details are reiterated here. The deal description and details are reiterated here.</span>
			</div>
		</div>
	</div>
	<div class="printbody">
		<div id="printnowlater">
			<div class="printnow">
				<h4> Ready to Print Now? </h4>
				<span>This option will render your voucher in order to print now. You must be on a device that is connected to a printer.</span>
				<div class="printchecklist">
					<img src="https://s3.amazonaws.com/siteimg.coupsmart.com/apps/printicon.png" alt="Printer"/>
					<h4> Printer Checklist-</h4>
					<ul>
						<li>My printer is turned on and connected to my device </li>
						<li>My printer has paper and ink </li>
					</ul>
					<div class="coupon_controls style2">
						<button class="printnowbutton">Print Now</button>
					</div>
				</div>
			</div>
			<div class="printlater">
				<h4> Not Ready to Print? </h4>
				<span>No Problem! We'll remember you and you can come back to view and print your deal at a future time.</span>
				<br><br>
				<span>You can come back to claim this deal until <span class="dynamic_date"><strong>02/01/2013</strong></span></span>
				<div class="coupon_controls style2">
					<button class="printlaterbutton">Print Later</button>
				</div>
			</div>
		</div>
		<div id="dealtimeout" style="display:none;">
			<h4 class="errormsg"> Your deal has timed out... </h4>
			<span> Your deal took longer than normal to render and has timed out. Please try again. If you continue experiencing issues, please visit our customer support knowledge base - we'll help you find and correct your issue. </span>
			<br> <br>
			<a href="knowledgebase" target="_blank"> Go to the knowledge base now >> </a>
		</div>
		<div id="couponrendered">
			<div class="renderingtext">
				<h4> Rendering your unique deal</h4>
				<img src="//s3.amazonaws.com/siteimg.coupsmart.com/apps/printloading.gif" alt="Rendering Icon" />
			</div>
			<div>
				<div class="render_coupon_area"><img style="width:800px;height:300px;" src="" alt="voucher" /></div>
				<div class="coupon_controls style2">
					<button class="printbutton" data-item-id="" data-saved-for-later-id="">PRINT</button>
					<button class="coupon_controls_share" name="btn-share" data-rel='' id="btn-share-deal">Share Deal</button>
					<ul class="share_methods" style="display:none;" id="ul-share-methods" data-rel=''>
						<li class="sharemethod own_wall"><div></div>My wall </li>
						<li class="sharemethod friend_wall"><div></div>Friend's wall</li>
						<li class="sharemethod private_message"><div></div>Private Message</li>
						<li class="sharemethod own_group"><div></div>My Group</li>
						<li class="sharemethod own_page"><div></div>My Page</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="coupon_controls style2">
		<button class="cancel_print"> Cancel </button>
	</div>
</div>


<!-- uncomment these to implement user terms agreement
<div id="policy_share_accept" class="share_block" style="display:none;">
	<h1> We need you to accept our <a href="//coupsmart.com/privacy" target="_blank">Privacy Policy</A> and <A href="//coupsmart.com/terms" target="_blank">Terms of Service</A>!</h1>
	<div id="share_accept" class="share_block_content">
		<label id="accept terms">
			<input type="checkbox" name="Accept Policy and Terms" id="policy_accept_checkbox"> I accept policy and terms <br>
		</label>
	</div>
</div>
<div id="policy_accept" class="share_block" style="display:none;">
	<h1> We need you to accept our <A href="//coupsmart.com/privacy" target="_blank">Privacy Policy</A> and <A href="//coupsmart.com/terms" target="_blank">Terms of Service</A>! </h1>
	<div id="<?php print $coupon['id']; ?>" class="share_block_content">
		<label id="accept_terms">
			<button class="coupon_controls_print" rel='2' style="margin-left:180px; padding: 10px 20px;border:1px solid #333; color:#fff;border-radius:5px;background-color:#3B5998;font-weight:bold; font-size: 12px;text-shadow: 1px 1px 0 #333"> <?php print "I accept!";?></button>
		</label>
	</div>
</div>
 -->



<?php print $csc_reveal_deal_content; ?>

<!-- Container for the customer supplied code deal
<div id="reveal-deal" style="display:none;" class="reveal-modal large">
</div>-->

<!-- Thankyou message
<div id="thankyou_message" class="reveal-modal medium">
	<a class="close-reveal-modal">&#215;</a>
	<h2><?php echo $translations['Thank you for sharing!']; ?></h2>
</div>-->

<!-- Share message -->
<?php if(!empty($user_info)) { ?>
<div id="share_message_reveal" class="reveal-modal medium">
	<a class="close-reveal-modal">&#215;</a>
	<img src="<?php print 'https://graph.facebook.com/v' . $app_version . '/'.$user_info['id'].'/picture';?>" alt="">
	<h3><?php echo $user_info['name']; ?> sent you this deal along with this message</h3>
	<p>
		<?php print $user_info['share_msg'];?>
	</p>
</div>
<?php } ?>

<div class="clear"></div>

<!-- Message for those who decide to block permissions to our app -->
<div id="permissions-rejected" class="reveal-modal large">
	<a class="close-reveal-modal">&#215;</a>
	<div class="container_8">
		<h2>Please Allow Access To Use This App</h2>
		<p>If you do not provide access by clicking the blue buttons for these pop-ups, you may not be able to access the deal. Follow the instructions below.</p>
		<hr />
		<div class="clear"></div>
		<br />
		<div class="grid_4 permission-popup-photo" style="background-image:url('https://s3.amazonaws.com/siteimg.coupsmart.com/support/sd_permissions_data.jpg');height:200px;"></div>
		<div class="grid_4 permission-popup-text">
			<p class="leadtext" style="margin-top:35px;">Click <b>Go To App</b></p>
			<p>So we can make the deal specifically for you.</p>
		</div>
		<div class="clear"></div>
		<div class="grid_4 permission-popup-photo" style="background-image:url('https://s3.amazonaws.com/siteimg.coupsmart.com/support/sd_permissions_post.jpg');height:100px;margin-top:8px;"></div>
		<div class="grid_4 permission-popup-text">
			<p class="leadtext" style="margin-top:0px;">Click <b>Allow</b></p>
			<p>So you can share deals with your friends. It will not post without asking first.</p>
		</div>
		<div class="clear"></div>
		<hr />
		<p>If you would like to know more about the permissions we request for this app,  please visit our <a href="http://support.coupsmart.com" target="_blank">Support Center</a> (will open in new window.)</p>
	</div>
	<!--</div>-->
</div>
<!-- End Denied Permissions Message -->

<div id="detailsmodal" class="reveal-modal<?php print !empty($class_li) ? ' ' . $class_li : ''; ?>" rel="<?php echo $coupon['delivery_method'];?>" id="<?php print $coupon['id']; ?>">
	
	<div id="div-banner-image-link-url-content">
		<div id="div-deal-background-image" class="coupon_image five" style="" rel=""></div>
	</div>
	<h3 id="h3-deal-title-details">&hellip;</h3>
	<h4 id="h4-deal-title-value">&hellip;</h4>
	<div class="coupon_data_details">
		<p id="p-deal-description"><?php print $small_type; ?></p>
		<p class="expires"><?php echo $translations['Expires']; ?>: <span id="span-deal-expiry-date"><?php print $expires;?></span></p>
	</div>
	<a class="close-reveal-modal">&#215;</a>
</div>

<div id="timeoutmodal" class="reveal-modal">
	<h3>Your deal has timed out.</h3>
	<span class="dealdetails">The window has expired, and your deal is no longer available. </span> <br> <br>
	<span class="dealdetails">If you were unable to claim your deal, please visit our <a href="knowledgebase" target="_blank">customer knowledge base</a> and let us help you with your issue. </span>
	<div class="coupon_controls style2">
		<button class="backtodeals">No issues, back to deals</button>
		<!--<button class="close">Report a Problem</button>-->
	</div>
	<a class="close-reveal-modal">&#215;</a>
</div>

<div id="permissionsmodal" class="reveal-modal" style="background-color: #ffffff; left:460px;">
	<h3 style="color: #228acb; font-family: 'Open Sans'; font-size: 21px;">Please Accept The Permissions Of Our App!</h3>
	<p style="font-size: 16px; font-family: 'Open Sans'; color: #333333; line-height: 21px;">We use the information in your Facebook profile to provide you with deals you're more likely to be interested in plus verify that you're a real person. We will never sell or give your data to a third party.<br/><br/>See the sample image below. Click the blue 'Okay' button when the dialogue appears to accept our apps permissions, and enjoy your deal!</p><br/>
	<img style="border: 5px solid #75ac28; margin-left:2px;" src="http://siteimg.coupsmart.com.s3.amazonaws.com/apps/permissions-sample.jpg" />
	<br/><br/><br/>
	<div style="margin-bottom: 0px !important; width: 95%; position: relative !important;">
		<ul style="list-style-type: none; overflow: hidden; padding-left:78px;">
			<li>
		<button class="backtoperms" style="border: 0px; box-shadow: none; background-color: #75ac28; color: #ffffff; text-shadow: none; background-image: none !important; width: 320px; height: 50px; font-size: 14px;">GO BACK TO DEAL</button>
			</li>
		</ul>
	</div>
	<a class="close-reveal-modal">&#215;</a>
</div>

<div id="accessdenied" class="reveal-modal" style="display:none;">
	<h4 class="errormsg"> Sorry! Looks like you are currently unable to access this deal... </h4>
	<span> You may have already claimed this deal as many times as the deal's limit allows. If you think that this is incorrect, please visit our customer support page- we'll help you find and correct your issue. </span>
	<br> <br>
	<a href="http://support.coupsmart.com/forums/20418862-Facebook-Coupons" target="_blank"> Go to the support page now >> </a>
	<a class="close-reveal-modal">&#215;</a>
</div>

<div id="sharemodal" class="reveal-modal" data-rel="share">
	<!--<div class="gift_to_share">
		<img alt="" src=""/>
	</div>-->
	<div style="display:none" class="fbfriends-messages" id="nofbfriends">No Friends Found<br />
		<p>There are no results with the typed name.</p>
	</div>
	<div class="sharehead">
		<h5 class="my_wall_header" style="display:none;">Share on My Wall</h5>
	</div>
	<div class="friends_wall recipient_box row" id="div-choose-friend">
		<div class="sharehead">
			<h5>Share on a Friend's Wall</h5>
		</div>
		<fieldset>
			<input type="text" placeholder="Friend's name" id="txt-choose-friend-share" data-rel="friend" class="txt-choose-friend-share">
			<div id="div-friend-selector-content" style="display:none;">
				<ul class="friend-dropdown-list" id="ul_friend_selector">
				</ul>
			</div>
		</fieldset>
	</div>
	<div class="in_group recipient_box row" id="div-choose-group">
		<div class="sharehead">
			<h5>Share in My Group</h5>
		</div>
		<fieldset>
			<input type="text" placeholder="Group name" id="txt-choose-group-share" data-rel="group">
			<div id="div-group-selector-content" style="display:none;">
				<ul class="friend-dropdown-list" id="ul_group_selector">
				</ul>
			</div>
		</fieldset>
	</div>
	<div class="your_page recipient_box row" id="div-choose-page">
		<div class="sharehead">
			<h5>Share on my Page</h5>
		</div>
		<fieldset>
			<input type="text" placeholder="Page name" id="txt-choose-page-share" data-rel="page">
			<div id="div-page-selector-content" style="display:none;">
				<ul class="friend-dropdown-list" id="ul_page_selector">
				</ul>
			</div>
		</fieldset>
	</div>
	<input type="hidden" id="hdn_selected_selector" />
	<input type="hidden" id="hdn-share-to-print" />

	<div class="sharedetails">
		<div class="sharebox">
			<form id="frm-share-private-message" method="post">
				<textarea placeholder="Write something..." name="txt_share_message"></textarea>
				<input type="hidden" name="hdn_share_op" id="hdn_share_op" />
				<input type="hidden" name="hdn_selected_object_id" id="hdn_selected_object_id" />
				<input type="hidden" name="hdn_item_id" />
				<input type="hidden" name="hdn_picture" id="hdn_picture" />
				<input type="hidden" name="hdn_sgs_item_name" id="hdn_sgs_item_name" />
				<input type="hidden" name="hdn_sgs_item_description" id="hdn_sgs_item_description" />
				<input type="hidden" name="hdn_access_token" id="hdn_access_token" />
				<input type="hidden" name="hdn_app_name" id="hdn_app_name" value="fan_deals" />
				<input type="hidden" name="hdn_company_id" id="hdn_company_id" value="<?php print $company_id;?>" />
				<input type="hidden" name="hdn_facebook_user_id" id="hdn_facebook_user_id" value="<?php print $facebook_user_id;?>" />
				<input type="hidden" name="hdn_referral_id" id="hdn_referral_id" value="<?php print $referral_id;?>" />
			</form>
			<div class="sharepreview" id="div_share_preview">
				<div class="row">
					<div class="three columns">
						<img src="" alt="" />
					</div>
					<div class="nine columns">
						<span class="itemname"><b>The Item Name</b></span><br>
						<span class="itemdesc"> Item Description</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="sharefoot">
		<div class="coupon_controls share-controls style2">
			<button class="button shareitemfinal" id="btn_share_it">Share it</button>
			<button class="button shareitemfinal" id="btn_proceed_to_share" style="display:none;">Add a message and share</button>
		</div>
	</div>
	<a class="close-reveal-modal">&#215;</a>
</div>


<div id="printlatermodal" class="reveal-modal">
	<h3>We have saved your unique deal for you!</h3>
	<p> Come back to this app at any time before the deal expires to print your deal. We will send you a reminder notification 3 days before this deal expires.</p>
	<div class="coupon_controls style2">
		<button class="backtodeals">Ok, back to deals </button>
	</div>
	<a class="close-reveal-modal">&#215;</a>
</div>

<!--<div id="sharesuccess" class="reveal-modal">
	<h3>Thanks for sharing!</h3>
	<a class="close-reveal-modal">&#215;</a>
</div>-->

<form id="frm_facebook" name="frm_facebook" method="POST">
	<input type='hidden' id='hdn_item_id' name='hdn_item_id' />
	<input type='hidden' id='hdn_print_button_clicked' name='hdn_print_button_clicked' />
	<input type='hidden' id='hdn_print_url' name='hdn_print_url' value="<?php print $print_url;?>" />
</form>

<div class="alert-box-ie" id="old-ie-content" style="display:none">
	<div class="ie-text">
		<b>Outdated Browser Warning</b>If you are using Internet Explorer 8 or earlier you may run into problems using this Facebook App. Please upgrade your browser, or use a different browser, to avoid any problems.
	</div>
	<div class="ie-link">
		<a href="//windows.microsoft.com/en-US/internet-explorer/downloads/ie-9/worldwide-languages" target="_top">
		<img src="<?php echo $path; ?>download_ie9.jpg"  alt="Download Internet Explorer 9" width="300" height="62" alt="download IE 9" />
		<p>This link goes to windows.microsoft.com</p>
		</a>
	</div>
</div>

<div id="the_footer">
	<!-- div id='div-try-again'>
		If this is not working for you, please disable your browser's popup blocker and <a href='javascript:;' id='lnk-try-again'>try again.</a>
	</div -->
	<div id='support-footer'>
		<?php print $support_footer_content;?>
		<!--<span class="vitalicious">To learn more, please visit <a href="http://bit.ly/VitaCoup" target="_blank" style="color:#B80200;">Vitalicious.com</a>.<br></span>
		<span class="striderite">Watch the <a href="http://youtu.be/WlcEj6mhVSA" target="_blank">Stride Rite Made 2 Play Collection Video</a> now!</span><br>
		<span>Need help getting this deal? Visit our <a href='http://support.coupsmart.com' target='_blank' id='support-link'>Support Center</a>.</span>
		<span style="float: right;"><a href='//coupsmart.com/privacy#toc' target='_blank' id='toc-link'>Terms Of Use</a></span -->
	</div>
	
	<!--Display a Privacy Policy tab in case the company is a Silverpop Company-->
	<?php // if(is_numeric($company->sp_endpoint)) {?>
	<div id='footer-privacy-policy' class='footer-privacy-policy' data-reveal-id='footer-privacy-policy-content'>
		<b>Privacy Policy</b>
	</div>

	<div class="app-support-footer" style="display:inline-block;">
		<?php include_once dirname(dirname(__DIR__)) . '/components/app-support-footer.php'; ?>
	</div>
	<div id='footer-privacy-policy-content' class='reveal-modal expand' style='max-height:500px;overflow-y:auto !important;'>
		<a class="close-reveal-modal">&#215;</a>
		<h1><?php print $facebook_page_name;?> - Privacy Policy Content</h1>

		<!-- And here's the unsubscribe link that will be displayed only if the logged in facebook user is a coupsmart user who is not already unsubscribed -->
		<div id="silverpop-email-setting" style="padding-bottom:50px;">
		<?php
			if(!empty($existing_user->email)) {
				if($is_unsubscribed) { ?>
				<div>
					You have chosen to opt-out from automatically receiving emails for the owner of this page about special offers and deals. If you wish to start receiving deals and offers from this company, you may <a href="<?php print $subscribe_url;?>">subscribe to this company.</a><br>
				</div>
			<?php } else { ?>
				<div>
					By using this app, you are automatically subscribed to receive emails for the owner of this page about special offers and deals. If you would not like to receive emails you may
					<a href="<?php print $unsubscribe_url;?>">unsubscribe from this company.</a><br>
				</div>
			<?php }
			}
		?>
			<h1>CoupSmart Privacy Policy</h1>
			<?php include_once dirname(dirname(__DIR__)) . '/components/privacy.php'; ?>
		</div>
	</div>
	<?php // } ?>


</div>