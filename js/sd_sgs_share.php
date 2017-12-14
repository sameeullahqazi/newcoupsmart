<?php 
	header('Content-type: text/javascript'); 
	$app_link 					= urldecode($_REQUEST['app_link']);
	$booking_link				= !empty($_REQUEST['booking_link']) ? urldecode($_REQUEST['booking_link']): '';
	$url_to_share				= !empty($_REQUEST['url_to_share']) ? urldecode($_REQUEST['url_to_share']): '';
	$referral_code				= !empty($_REQUEST['referral_code']) ? urldecode($_REQUEST['referral_code']): '';
	require_once("share-coupon.php");
	require_once(dirname(__DIR__) . '/includes/app_config.php');
	global $app_version;
	// error_log("REQUEST in sd_sgs_share.php: ". var_export($_REQUEST, true));
?>
//<script>
var mouse_is_inside = false;
var print_timeout_value = 300; // Timeout Value = 5 minutes
var start_date_time;
var func_params = []; // This will hold the function and the params to run when 'Show me the Permissions Again' button is clicked
							// 0 - 1st param, 1 - 2nd param, 2 - The function to run. 
var url_to_share = '<?php print $url_to_share; ?>';
var app; // I'm not sure if this variable is already defined some place else; currently it's giving me a 'variable undefined' error
var print_permissions = ['email','user_birthday','user_location','user_relationships', 'user_likes', 'user_friends'];
var print_permissions_granted = false;

<?php 
	if(!empty($app_version))
		print "app_version = '$app_version';";
?>
// Action to be performed once the Fb feed dialog runs
function postFBFeedDialogAction(callback_helper, helper_data)
{
	$.ajax({
		type: "POST",
		url: '/helpers/' + callback_helper,
		dataType: 'json',
		data: helper_data,
		success: function(data){
			if($('#hdn_app_name').val() == 'convercial')
			{
				if(!shared_at_the_end)
					hideShareScreenAndShowPage3();
				else
					alert('Thank you! Your deal has been shared successfully!');
			}
			else
			{
				alert('Thank you! Your deal has been shared successfully!');
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			// console.log(xhr);
			alert(thrownError);
		}
	});
}

function sortByFBName(a, b) {
	var x = a.name.toLowerCase();
	var y = b.name.toLowerCase();
	return ((x < y) ? -1 : ((x > y) ? 1 : 0));
}

//filter facebook friends for the friend selector
function filterFacebookFriends(selector){
	var str_filter = $(selector).val().trim().toLowerCase();
	var selector_type = $(selector).attr('data-rel');

	// Setting the selected selector type (friend/page/group)
	$('#hdn_selected_selector').val(selector_type);

	if(str_filter == '')
	{
		$('#div-' + selector_type + '-selector-content').hide();
	}
	else
	{
		var selector_data = arr_selector_data;
		selector_data = jQuery.grep(selector_data, function(elem, i){
			return elem['name'].toLowerCase().indexOf(str_filter) >= 0;
		});
		selector_data.sort(sortByFBName);
		if(selector_data.length == 0){
			$('#div-' + selector_type + '-selector-content').hide();
		} else {
			var html_ul_selector = '';
			var count = 0;
			for(i in selector_data)
			{
				if(count <= 4){ //only display 5
					html_ul_selector += '<li data-rel="' + selector_data[i]["id"] + '"><div class="row"><div class="small-2 columns"><div class="fbuserimg-small" style="background-image:url(\'https://graph.facebook.com/v' + app_version + '/' + selector_data[i]["id"] + '/picture?type=small\')"></div></div><div class="small-10 columns fbusername" id="selector_fb_name_' + selector_data[i]["id"] + '">' + selector_data[i]["name"] + '</div></div></li>';
					count++;
				}
			}
		
			$('#ul_' + selector_type + '_selector').html(html_ul_selector);
			// Filter the content
			$('#div-' + selector_type + '-selector-content').show();
		}
	}
}

// Refreshes the list showing saved deals for later
function refreshSavedDealsForLater(data)
{
	
	if(data.length > 0)
	{
		var html_content = '';
		for(i in data)
		{
			html_content += '<li><a data-item-id="' + data[i]['item_id'] + '" data-saved-for-later-id="' + data[i]['saved_for_later_id'] + '">Click here to print your saved deal now </a></li>';
		}

		$('.printsaveddeal ul').html(html_content);
		$('.printsaveddeal').show();
	}
	else
	{
		$('.printsaveddeal').hide();
	}

}

// Resets the fields on the Modal Dialog that is shown when selecting a share option
function resetSelectorForm()
{
	$("#txt-choose-friend-share, #txt-choose-group-share, #txt-choose-page-share").val('');
	$('#frm-share-private-message textarea').val('');
	$("#frm-share-private-message input[name='hdn_selected_object_id']").val('');
}

// Determine whether to show the print options or to directly print the coupon
function invokePrintItem(item_id)
{
	
	var show_print_options = $('#hdn-show-print-options-' + item_id).val();
	if(show_print_options != '1')
	{
		printItem(item_id);
	}
	else
	{
		$('.printbutton').attr('data-saved-for-later-id', '');
		showDealDecisionsArea(item_id);
	}
}

function populateItemData(item_id)
{
	FB.Canvas.scrollTo(0, 0);
	
	// Start timer
	start_date_time = new Date();
	
	// Set Item Title
	var item_name = $('#span-item-name-' + item_id).html().trim();
	$('#h4-deal-name-print-options').html(item_name);
	
	// Set Item Description
	var item_description = $('#div-item-description-' + item_id).html().trim();
	$('#span-deal-description-print-options').html(item_description);
	
	// Set Item Image
	var picture = $("#div-item-image-" + item_id).css('background-image').trim();
	picture = picture.replace(/"/g,"").replace(/url\(|\)$/ig, "");
	picture = (picture == 'none' || picture == undefined || picture == null) ? '' : picture;
	$('#img-deal-image-print-options').attr('src', picture);

	
	// Render Coupon Preview
	var arr_params = [];
	
	arr_params.push("item_id=" + item_id);
	arr_params.push("is_preview=1");
	// arr_params.push("expiration_date=" + expiry_date);
	
	var str_params = "";
	if(arr_params.length > 0)
		str_params = "?" + arr_params.join('&');
		
	var rendered_url = '/helpers/render-coupon-using-layout.php' + str_params;
			
	$(".render_coupon_area img").attr('src', rendered_url);
	
		
	// Set data-rel attribute of Share button
	$('#btn-share-deal, #ul-share-methods').attr('data-rel', item_id);
}

// Shows the Print Now / Print Later page
function showDealDecisionsArea(item_id)
{
	populateItemData(item_id);
	
	$('#coupon_page_list').slideUp({ duration: 1000, easing: 'linear'});
	$('#coupon_print_page').slideDown({ duration: 1000, easing: 'linear'});
	$('#couponrendered').slideUp({ duration: 500, easing: 'linear'});
	$('#printnowlater').slideDown({ duration: 1000, easing: 'linear'});
}

function getCustomerSuppliedCode(sel_item_id)
{
	var required_permissions = print_permissions; //['email','user_interests','user_likes','user_birthday','user_location','user_relationships'];
	var str_required_permissions = required_permissions.join(',');
	var field_list = "id,first_name,last_name,gender,email,birthday,location,relationship_status,friends.offset(0).limit(999999)"; // ,interests.offset(0).limit(999999999),likes.offset(0).limit(999999999)";
	var items_views_id =  arr_item_views[sel_item_id];
	
	FB.login(function(response){
		if (response.authResponse) {
			var permissions_rejected;
			var access_token = response.authResponse.accessToken;

			FB.api('/v' + app_version + '/me/permissions', function(granted_permissions){

				permissions_rejected = isPermissionRejected(granted_permissions, required_permissions);
				
				if(permissions_rejected)
				{
					FB.api("/v" + app_version + "/me/permissions","DELETE",function(response){});
					$('#permissionsmodal').reveal();
					// updatePermissionsRejected(sel_item_id);
					trackButtonClick('permissions_rejected', '1', items_views_id);
					$('.reveal-modal').css("display","inline");
					return false;
				}
		
				trackButtonClick('permissions_rejected', '0', items_views_id);
				// Get User's Facebook data
				FB.api("/v" + app_version + "/me?access_token=" + access_token + "&fields=" + field_list, function(user_details){
					fb_user_data = user_details;

					// Call customer supplied code helper
					var campaign_id = $('#hdn-campaign-id-' + sel_item_id).val();

					var printed_date = new Date();
					var expired_date = new Date(printed_date.getTime() + 604800000); //it takes 7 days to expire

					var custom_code, formatted_date, split_date, new_date, expired_html, csc_reveal_deal_content;

					$('.coupon_page').slideUp();
					FB.Canvas.scrollTo(0,0);
					$("#loading_code").css('display', 'inline');
					
					var items_views_id =  arr_item_views[item_id];
					$.ajax({
						type: "POST",
						url: "/helpers/ajax-customer-supplied-code.php",
						dataType: 'json',
						data: {'user_details': user_details, 'campaign_id': campaign_id, 'item_id': sel_item_id, 'items_views_id': items_views_id, 'new_referral_id': new_referral_id, 'expired_date': expired_date.getTime(), 'access_token': access_token, 'permissions': print_permissions.join(',')},
						success: function(data){
							if(data['error'] == undefined)
							{
								custom_code = data[0]
								expired_date = data[1];
								formatted_date = expired_date.replace(" ", ",").replace("-", ",").replace("-", ",").replace(":", ",").replace(":", ",");
								split_date = formatted_date.split(",");
								new_date = new Date(split_date[0], split_date[1]-1, split_date[2], split_date[3], split_date[4], split_date[5]);
								expired_html = "Offer valid until " + " " + (new_date.getMonth()+1) + "/" + new_date.getDate() + "/" + new_date.getFullYear();
					
								csc_reveal_deal_content = $('#hdn-csc-reveal-deal-content-' + sel_item_id).val();
								csc_reveal_deal_content = csc_reveal_deal_content.replace('customCode', custom_code);
								$('#reveal-deal').html(csc_reveal_deal_content);
					
								$("#expires").html(expired_html);
								$("#codes").html(custom_code);

								// Reposition the modal dialog
								var selected_item_top = $("[name='btn-share'][data-rel='" + sel_item_id + "']").offset().top - 100;
								$('#reveal-deal').css('top', selected_item_top);

								$("#loading_code").css('display', 'none');

								$('#reveal-deal').css("display","inline");
								$('.code_page').slideDown();
								return false;
							}
							else if(data['error'] && data['redirect']){
								if(data['error'] == 'none'){
									window.open(data['redirect']);
								} else if(data['error'] == 'denied'){
									window.location.href = "//<?php echo $_SERVER['SERVER_NAME']; ?>" + data['redirect'];
								}
							}
							else
							{
								$('#reveal-deal').html(data['error']);
								$('#reveal-deal').css('top', selected_item_top);

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
				}); 	// End FB.api('/me')
			});		// End FB.api('/me/permissions')
		}				//	End if response.authResponse
		else
		{
			$('#permissionsmodal').reveal();
			// updatePermissionsRejected(sel_item_id);
			trackButtonClick('permissions_rejected', '1', items_views_id);
			$('.reveal-modal').css("display","inline");
		}
	},{scope: str_required_permissions});
}

// Checks and prints the item if Share to Print was clicked.
function checkAndPrintIfShared()
{
	var sel_item_id = $("[name='hdn_item_id']").val();
	// DETERMINING WHETHER THE BUTTON CLICKED WAS SHARE TO PRINT OR A REGULAR SHARE	
	var share_to_print = $("#hdn-share-to-print").val();
	
	if(share_to_print == '1')
	{
		var delivery_method = $('#hdn-delivery-method-' + sel_item_id).val();
		callDeliveryMethodFunc(sel_item_id, delivery_method);
	}
}

function callDeliveryMethodFunc(item_id, delivery_method)
{
	if(delivery_method == '11') // 11: Customer Supplied Code
	{
		func_params = ['', item_id, 'customer_supplied_code'];

		getCustomerSuppliedCode(item_id);
	}
	else if(delivery_method == '8') // Social Booking
	{
		var params = "&app_data=" + item_id;
		top.location.href = '<?php print $booking_link;?>' + params;
	}
	else // Regular Facebook Coupon (delivery method assumed to be 3)
	{
		invokePrintItem(item_id);
	}
}


// Invokes the permissions dialog requesting the necessary permissions, prints the coupon if they are granted, 
//	shows an appropriate message otherwise. 
//	If for some reason, the coupon rendering times out, an appropriate dialog is shown.
function printItem(item_id, saved_for_later_id)
{
	if(print_permissions_granted)
	{
		// In this case permissions have already been granted so simply get users FB data and print
		getUsersFBDataAndPrint(item_id, saved_for_later_id, $("[name='hdn_access_token']").val());
	}
	else
	{

		// Permissions required to get user's basic facebook data
		var required_permissions = print_permissions;
		var str_required_permissions = required_permissions.join(',');
		var items_views_id =  arr_item_views[item_id];

		FB.login(function(response){
			if (response.authResponse) {
				var permissions_rejected;
				var access_token = response.authResponse.accessToken;

				FB.api('/v' + app_version + '/me/permissions', function(granted_permissions){

					permissions_rejected = isPermissionRejected(granted_permissions, required_permissions);
				
					if(permissions_rejected)
					{
						FB.api("/v" + app_version + "/me/permissions","DELETE",function(response){});
						$('#permissionsmodal').reveal();
						$('.reveal-modal').css("display","inline");
						// updatePermissionsRejected(item_id);
						trackButtonClick('permissions_rejected', '1', items_views_id);
						return false;
					}
					else
					{
						trackButtonClick('permissions_rejected', '0', items_views_id);
						getUsersFBDataAndPrint(item_id, saved_for_later_id, access_token);
					}

				});
			}
			else
			{
				$('#permissionsmodal').reveal();
				$('.reveal-modal').css("display","inline");
				// updatePermissionsRejected(item_id);
				trackButtonClick('permissions_rejected', '1', items_views_id);
			}
		},{scope: str_required_permissions});
	}
}

function updatePermissionsRejected(item_id, permissions_accepted)
{
	$.ajax({
		type: "POST",
		url: "/helpers/ajax-smart-deals.php",
		dataType: 'json',
		data: {'op': 'permissions_rejected', 'item_id': item_id, 'permissions_accepted': permissions_accepted},
		success: function(data){

		},
		error: function(xhr, error){
		}
	});
}

function getUsersFBDataAndPrint(item_id, saved_for_later_id, access_token)
{
	// This should be constant
	var distributor = 1, ptype = 1, company_url = '<?php print $app_link;?>';
	var items_views_id =  arr_item_views[item_id];
	var field_list = "id,first_name,last_name,gender,email,birthday,location,relationship_status,friends.offset(0).limit(999999)"; // ,interests.offset(0).limit(999999999),likes.offset(0).limit(999999999)";
	
	// Get User's Facebook data
	FB.api("/v" + app_version + "/me?access_token=" + access_token + "&fields=" + field_list, function(user_details){
		fb_user_data = user_details;

		// Get User interests once user's basic data has been retrieved
		// FB.api('/v' + app_version + '/me/interests?access_token=' + access_token, function(user_interests) {
			
		// Set the printed flag to 1 indicating that the coupon has now been saved.
		if(saved_for_later_id != undefined)
		{
			$.ajax({
				type: "POST",
				url: "/helpers/ajax-smart-deals.php",
				dataType: 'json',
				data: {'op': 'update_saved_for_later_deal', 'saved_for_later_id': saved_for_later_id},
				success: function(data){

				},
				error: function(xhr, error){
				}
			});
		}
		
		// Register the user if doesn't exist; update existing user in case they already exist
		$.ajax({
			type: "POST",
			url: "/helpers/ajax-user-register-fb.php",
			dataType: 'json',
			// data: {'user_details': JSON.stringify(user_details), 'item_id': item_id},
			data: {'user_details': user_details, 'item_id': item_id, 'items_views_id': items_views_id, 'new_referral_id': new_referral_id, 'access_token': access_token, 'app_name': 'fan_deals', 'permissions': print_permissions.join(',')},
			success: function(data){
				// console.log('data returned from ajax-user-register-fb.php: ');
				// console.log(data);
				if(data['error'] && data['redirect']){
					if(data['error'] == 'none'){
						window.open(data['redirect']);
					} else if(data['error'] == 'denied'){
						window.location.href = "//<?php echo $_SERVER['SERVER_NAME']; ?>" + data['redirect'];
					}
				} else if(data['bundled_coupons_code'] != undefined) {
						FB.Canvas.scrollTo(0,0);
						window.location.href = "//<?php echo $_SERVER['SERVER_NAME'];  ?>/bundled-coupons?item_id=" + item_id + "&items_views_id=" + items_views_id + "&d=" + distributor + "&ptype=" + ptype + "&user_id=" + data['id'] + "&company_url=" + encodeURIComponent(company_url) + "&app_name=fan_deals&c=" + data['bundled_coupons_code'];
				}
				else {
					// if(data['print_access_denied'] != undefined)
					// {
						// Reveal Access Denied Modal Message
					//	$('#accessdenied').reveal({animation: 'fade'});
					//	$('.reveal-modal').css("display","inline");
					//	return false;
					// }
					// else
					// {
						// Print the coupon
						FB.Canvas.scrollTo(0,0);
						window.location.href = "//<?php echo $_SERVER['SERVER_NAME'];  ?>/helpers/facebook/application/print.php?item_id=" + item_id + "&items_views_id=" + items_views_id + "&d=" + distributor + "&ptype=" + ptype + "&user_id=" + data['id'] + "&company_url=" + encodeURIComponent(company_url) + "&app_name=fan_deals";
					// }
				}
		
			},
			error: function(xhr, error){
			}
		});
		// });
	});
}

function clearModal(){
	$('[name="txt_share_message"]').val('');
	$('#txt-choose-friend-share').val('');
	$('#txt-choose-group-share').val('');
	$('#txt-choose-page-share').val('');
	filterFacebookFriends($('#txt-choose-friend-share'));
}

function getPermissionsAndShowDialog(sel_option, sel_item_id)
{
	var str_required_permissions = '';
	var required_permissions = [];
	var items_views_id =  arr_item_views[sel_item_id];
	
	// In case of a Share to Print coupon, request both share and print permissions together
	var share_to_print = $("#hdn-share-to-print").val();
	if(share_to_print == '1')
		required_permissions = print_permissions;
	
	var scroller = new Scroller();
	scroller.update();
	scroller.attachToModal('sharemodal', clearModal, null);
	scroller.attachToModal('permissionsmodal', clearModal, null);

	var object = ''; // friends, accounts or groups

	if(sel_option == 'own_wall')
	{
		// Needs just pubish_stream permission and show header
		$(".my_wall_header").show();
		required_permissions.push('publish_actions');
	}


	if(sel_option == 'friend_wall')
	{
		// Show 'Choose Friend' selector for Private Message
		$("#div-choose-friend").show();

		// Hide the Text area and Share It button
		$('#frm-share-private-message, #btn_share_it').hide();

		// Show the Proceed to Share button
		$('#btn_proceed_to_share').css("display", "inline-block");

		// Need the user_friends permissions
		required_permissions.push('user_friends');
					
		// Need the email permission
		object = 'friends';
	}

	// Show 'Choose Page' selector for 'My Page'
	if(sel_option == 'own_page')
	{
		$("#div-choose-page").show();

		// Need the manage_pages and pubish_stream permissions
		required_permissions.push('publish_actions', 'manage_pages');

		object = 'accounts';

	}

	// Show 'Choose group' selector for 'My Group'
	if(sel_option == 'own_group')
	{
		$("#div-choose-group").show();

		// Need the user_groups and publish_actions permissions
		required_permissions.push('user_groups','publish_actions');

		object = 'groups';
	}
	str_required_permissions = required_permissions.join(',');
	// console.log('str_required_permissions: ' + str_required_permissions);

	
	//Reposition the modal dialog -- not sure that we need this anymore because of the scroller objects
	//var selected_item_top = $("button.button.shareitem.[data-rel='" + sel_item_id + "']").offset().top - 100;
	//var selected_item_top = $("[name='btn-share'][data-rel='" + sel_item_id + "'], .shareitem[data-rel='" + sel_item_id + "']").offset().top - 500;
	//$('#sharemodal, #permissionsmodal').css('top', selected_item_top);

	// Get appropriate permissions
	FB.login(function(response){
		if (response.authResponse) {
			var permissions_rejected;
			var tmp_access_token = response.authResponse.accessToken;
			$("[name='hdn_access_token']").val(tmp_access_token);
			FB.api('/v' + app_version + '/me', function(user_details){
				fb_user_data = user_details;
				FB.api('/v' + app_version + '/me/permissions', function(granted_permissions){

					permissions_rejected = isPermissionRejected(granted_permissions, required_permissions);
					
					if(permissions_rejected)
					{
						$("#permissionsmodal").foundation('reveal', 'open');
						// updatePermissionsRejected(sel_item_id);
						trackButtonClick('share_permissions_rejected', '1', items_views_id);
					}
					else
					{
						if(share_to_print == '1')
							print_permissions_granted = true;
						trackButtonClick('share_permissions_rejected', '0', items_views_id);
						if(sel_option != 'own_wall')
						{
							// Get Data
							FB.api('/v' + app_version + '/me/' + object, function(selector_data){
								// console.log(selector_data);
								fb_user_data = selector_data;
								arr_selector_data = selector_data['data'];
							});
						}

						// Show custom Dialog
						$("#sharemodal").foundation('reveal', 'open');
					}
				});
			});
		}
		else
		{
			$("#permissionsmodal").foundation('reveal', 'open');
			// updatePermissionsRejected(sel_item_id);
			trackButtonClick('share_permissions_rejected', '1', items_views_id);
		}
		$('.reveal-modal').css("display","inline");
		
	},{scope: str_required_permissions});
}

function getFBCDNPicture(picture)
{
	if(picture.indexOf("graph.facebook.com") > 0 || picture.indexOf("fbcdn.net") > 0)
	{
	
		$.ajax({
			type: "POST",
			url: "/helpers/ajax-common.php",
			dataType: 'json',
			async: false,
			data: {'op' : 'add_to_s3', 'img_path': picture},
			success: function(data){
				picture = "http://s3.amazonaws.com/uploads.coupsmart.com/" + data;
				return picture;
			},
			error: function(xhr, error){
				console.log(xhr);
				alert(error);
			}
		});
	}
	return picture;
}

$(document).ready(function() {

/*********************************************************
*			FUNCTIONS FOR SHARE OPTIONS BEGIN HERE				*
**********************************************************/
	$("[name='btn-share'], .shareitem").live('click',
		function() {
			var tmp_item_id = $(this).attr('data-rel');
			$("ul.share_methods[data-rel='" + tmp_item_id + "'], .item-action ul[data-rel='" + tmp_item_id + "']").css("display", "block");
			
			//preventing redirect - not sure how though
			if($(this).attr('id') == 'share_shop'){
				//return false;
			}
			
			// Setting Share / Share to Print
			var share_to_print = $(this).attr('data-share-to-print') != undefined ? $(this).attr('data-share-to-print') : '0';
			$("#hdn-share-to-print").val(share_to_print);
			return false;
		}/*, function(){
			$('ul.share_methods, .item-action ul').css("display", "none");*/
	);
	/*
	$('.coupon_controls_share, .shareitem').hover(function(){
		mouse_is_inside=true;
	}, function(){
		mouse_is_inside=false;
	});*/
	$("*").click(function(){
		$('ul.share_methods, .item-action ul').css("display", "none");
	});
	$(".sharemethod").click(function() {
		// console.log("just clicked a share method");
		// Setting the selected share option
		// console.log('class name: ' + $(this).attr('class'));
		// var sel_option = $(this).attr('class').substring(12);
		var arr_sel_options = $(this).attr('class').split(' ');
		sel_option = arr_sel_options[arr_sel_options.length - 1];
		// console.log('sel_option when sharemethod is clicked: ' + sel_option);
		$("[name='hdn_share_op']").val(sel_option);

		// Setting the selected item id
		var sel_item_id = $(this).parent(".share_methods").attr('data-rel');
		// console.log('sel_item_id when sharemethod is clicked: ' + sel_item_id);

		$("[name='hdn_item_id']").val(sel_item_id);
		
		var items_views_id =  arr_item_views[sel_item_id];
		trackButtonClick('share_clicked', '1', items_views_id);
			
		var picture;
		var item_name;
		var item_description;
		if(sel_item_id == '1'){ //its the share button on the thank you page
			//get the profile picture
			//console.log("we are on the thank you page!");
			picture = 'https://graph.facebook.com/v' + app_version + '/' + $("#share_shop").attr("data-company-facebook-id") + '/picture?type=large';
			//console.log("picture");
			//console.log(picture);
			$("[name='hdn_picture']").val(picture);
			$('#div_share_preview img').attr('src', picture);

			//instead of item name, use the company name
			item_name =  $("#share_shop").attr('data-company-name');
			$("[name='hdn_sgs_item_name']").val(item_name);
			$('#div_share_preview .itemname').html('<b>' + item_name + '</b>');

			item_description = item_name + "s Social Gift Shop. They have great deals!";
			$("[name='hdn_sgs_item_description']").val(item_description);
			$('#div_share_preview .itemdesc').html(item_description);
		} else {
			//console.log("we are not on the thank you page!");
			// Setting the selected SD or SGS image
			
			var picture = $("#div-item-image-" + sel_item_id).css('background-image').trim();
			picture = picture.replace(/"/g,"").replace(/url\(|\)$/ig, "");
			picture = (picture == 'none' || picture == undefined || picture == null) ? '' : picture;
			
			// This hidden field has only been added to the canvas/coupsmart view page not components/sgs-items-display
			var obj_img_sharing = $("#hdn-img-sharing-" + sel_item_id);
			// The if condition is to check for both SD and SGS
			if(obj_img_sharing != undefined && obj_img_sharing != null)
				if(obj_img_sharing.val() != '' && obj_img_sharing.val() != undefined)
					picture = obj_img_sharing.val();
			
			
			var url_parts = picture.split("/");
			var orig_img = url_parts[url_parts.length - 1];
			var new_img = app == 'coupsmart' 
							? getImg(orig_img, 128, 128)// "sgsimg.coupsmart.com");
							: getImg(orig_img, 128, 128, "sgsimg.coupsmart.com")
			;
			var new_img_url = picture.replace(orig_img, new_img);
			$("[name='hdn_picture']").val(picture);
			$('#div_share_preview img').attr('src', new_img_url);

			// Setting Item Name
			// item_name = $('#span-item-name-' + sel_item_id).html().trim();
			item_name = $("[name='hdn-item-name'][data-rel='" + sel_item_id+ "']").val();
			$("[name='hdn_sgs_item_name']").val(item_name);
			$('#div_share_preview .itemname').html('<b>' + item_name + '</b>');

			// Setting Item Description
			// This only works for SGS, not Smart Deals:
			// item_description = $('#detailsmodal-' + sel_item_id).children().children("#details-body").html().trim();
			// This one works for Smart Deals:
			item_description = $('#div-item-description-' + sel_item_id).html().trim();
			$("[name='hdn_sgs_item_description']").val(item_description);
			$('#div_share_preview .itemdesc').html(item_description);
		}

		if(sel_option == 'private_message') {
			var params = {
				'link': url_to_share,
				'method': 'send',
				'display': 'popup',
				/*'picture': picture,
				'name': item_name,
				'caption': item_name*/
			};
			
			/*
			picture = getFBCDNPicture(picture);
			if(picture != '' && picture != 'none')
				params['picture'] = picture;
			*/
			
			// By Samee, Jul 26th, 2013
			// We have removed the above parameters since they are no longer applicable to the FB.ui send method.

			var form_data 		= $('#frm-share-private-message').serialize();
			var helper_data 	= {'form_data': form_data, 'app_link': url_to_share, 'referral_code': '<?php print $referral_code?>'};
			// FBFeedDialog(params, 'ajax-sgs-send-messages.php', helper_data);
			FB.ui(params, function(response) {
				if(response != undefined) // If response is a valid object
				{
					// Perform post action
					helper_data['response'] = response;
					postFBFeedDialogAction('ajax-sgs-send-messages.php', helper_data);
					
					// Check if Share to Print was clicked, act accordingly
					checkAndPrintIfShared();
				}
			});

			// Reset the selector form
			resetSelectorForm();
			
		}
		else if(sel_option == 'own_wall')
		{
			var params = {
				'link': url_to_share,
				'method': 'feed',
				'name': item_name,
				'caption': item_name,
				// 'description': item_description
			};
			
			if(item_description.length < 5000)
					params['description'] = item_description;
			
			/*
			picture = getFBCDNPicture(picture);
			if(picture != '' && picture != 'none')
				params['picture'] = picture;
			*/
			
			// console.log("params when posting to own wall:");
			// console.log(params);
			// By Samee, Jul 26th, 2013
			// We have removed the above parameters since they are no longer applicable to the FB.ui send method.
			
			var form_data;
			var helper_data;
			var app_name = $('#hdn_app_name').val();
			var sender_id;
			// console.log("app_name: " +  app_name);
			
			FB.login(function(response){
				if (response.authResponse) {
					console.log("response in FB.login when selecting own wall: ", response);
					sender_id = response.authResponse.userID;
					$('#hdn_facebook_user_id').val(sender_id);
					$("[name='hdn_access_token']").val(response.authResponse.accessToken)
					
					form_data 		= $('#frm-share-private-message').serialize();
					helper_data 	= {'form_data': form_data, 'app_link': url_to_share, 'referral_code': '<?php print $referral_code?>'};
					trackButtonClick('share_permissions_rejected', '0', items_views_id);
					// FBFeedDialog(params, 'ajax-sgs-send-messages.php', helper_data);
					FB.ui(params, function(response) {
						
						if(response != undefined) // If response is a valid object
						{
							// Perform post action
							helper_data['response'] = response;
							postFBFeedDialogAction('ajax-sgs-send-messages.php', helper_data);
					
							// Check if Share to Print was clicked, act accordingly
							checkAndPrintIfShared();
						}
					});

				}
				else
				{
					alert("Please provide the requested permissions in order to share.");
					trackButtonClick('share_permissions_rejected', '1', items_views_id);
				}
			},{scope: 'publish_actions'});

			

			// Reset the selector form
			resetSelectorForm();
		
		}
		else
		{
			// Hide all selectors, and default header
			$("#div-choose-friend, #div-choose-page, #div-choose-group, .my_wall_header").hide();

			// Show the Text area and Share It button
			$('#frm-share-private-message, #btn_share_it').show();

			// Hide the Proceed to Share button
			$('#btn_proceed_to_share').hide();

			// Get selected Item ID
			var sel_item_id = $("[name='hdn_item_id']").val();
			
			// Save this function to the process queue before executing it
			func_params = [sel_option, sel_item_id, 'share'];
			
			getPermissionsAndShowDialog(sel_option, sel_item_id);

		}

	});

	// When the 'Share It' button is clicked from the custom modal dialog on the main page
	$('#btn_share_it').live('click', function() {
		if($('[name="txt_share_message"]').val() == 'Type Your Message Here...'){
			$('[name="txt_share_message"]').val('')
		}
		//e.log($('[name="txt_share_message"]').val(), 'the message');
		var form_data = $('#frm-share-private-message').serialize();
		$.ajax({
			type: "POST",
			url: "/helpers/ajax-sgs-send-messages.php",
			dataType: 'json',
			data: {'form_data' : form_data, 'app_link': url_to_share, 'referral_code': '<?php print $referral_code?>'},
			success: function(data){
				// console.log('INSIDE AJAX BTN SHARE IT');

				// Close the Share Modal
				$("#sharemodal").foundation('reveal', 'close');

				// And if sharing was successful, then show the success dialog
				if(data > '0')
				{	// console.log('SUCCESS SHARE');
					new_referral_id = data;
					$('#sharesuccess').reveal({
						animation: 'fade'
					 });
					 
					 // Check if Share to Print was clicked, act accordingly
					 if($('#hdn_app_name').val() == 'convercial')
						if(!shared_at_the_end)
							hideShareScreenAndShowPage3();
						else
							alert('Thank you! Your deal has been shared successfully!');
							
					checkAndPrintIfShared();
				} else {
					// console.log('FAILED TO SHARE');
				}
				// Reset the selector form
				resetSelectorForm();
			},
			error: function(xhr, error){
				console.log(xhr);
				alert(error);
			}
		});
	});

	$('#btn_proceed_to_share').live('click', function() {
		// Close the Share modal
		$("#sharemodal").foundation('reveal', 'close');

		// Show the FB.dialog
		var picture = $("[name='hdn_picture']").val();
		var to = $("[name='hdn_selected_object_id']").val();
		var item_name = $("[name='hdn_sgs_item_name']").val();
		var item_description = $("[name='hdn_sgs_item_description']").val();

		var params = {
			'link': url_to_share,
			'method': 'feed',
			'to': to,
			'name': item_name,
			'description': item_description
		};

		picture = getFBCDNPicture(picture);
		if(picture != '' && picture != 'none')
			params['picture'] = picture;

		var form_data 		= $('#frm-share-private-message').serialize();
		var helper_data 	= {'form_data': form_data, 'app_link': url_to_share, 'referral_code': '<?php print $referral_code?>'};
		// FBFeedDialog(params, 'ajax-sgs-send-messages.php', helper_data);
		FB.ui(params, function(response) {
			// console.log('params when posting to a friends wall: ');
			// console.log(params);
			// console.log('response when posting to a friends wall: ');
			// console.log(response);
			
			if(response != undefined) // If response is a valid object
			{
				// Perform post action
				helper_data['response'] = response;
				postFBFeedDialogAction('ajax-sgs-send-messages.php', helper_data);
				
				// Check if Share to Print was clicked, act accordingly
				checkAndPrintIfShared();
			}
		});
		
		// Reset the selector form
		resetSelectorForm();

	});
	
	$("#txt-choose-friend-share, #txt-choose-group-share, #txt-choose-page-share").live('keyup', function(){
		filterFacebookFriends(this);
	});

	$("#ul_friend_selector li, #ul_page_selector li, #ul_group_selector li").live('click', function() {
		var selected_value = $(this).attr('data-rel');
		$("[name='hdn_selected_object_id']").val(selected_value);


		// Getting the selected selector type
		var selector_type = $('#hdn_selected_selector').val();

		// Fill in the name
		$("#txt-choose-" + selector_type + "-share").val($('#selector_fb_name_' + selected_value).html());

		// Hide the selector content
		$('#div-' + selector_type + '-selector-content').hide();
	});
	
	
	// When the 'Print Deal' button is clicked
	$('.coupon_controls_print').live('click', function(){
		var item_id = $(this).attr('data-item-id');
		
		// Setting the selected item id
		$("[name='hdn_item_id']").val(item_id);
		
		var items_views_id =  arr_item_views[item_id];
		trackButtonClick('print_clicked', '1', items_views_id);
		
		var delivery_method = $('#hdn-delivery-method-' + item_id).val();
		callDeliveryMethodFunc(item_id, delivery_method);
	});
	
	
	// When the Print Now button is clicked
	$(".printnowbutton").click(function(){
		$('#printnowlater').slideUp({ duration: 0, easing: 'linear'});
		$('#couponrendered').slideDown({ duration: 500, easing: 'linear'});
		$('.cancel_print').html('Back to Deals');
	});
	
	// When the Print Later button is clicked
	$(".printlaterbutton").click(function(){
		var item_id 	= $("[name='hdn_item_id']").val();
		var user_fb_id	= $("[name='hdn_facebook_user_id']").val();
		
		$.ajax({
			type: "POST",
			url: "/helpers/ajax-smart-deals.php",
			dataType: 'json',
			data: {'op': 'save_deal_for_later', 'item_id': item_id, 'user_fb_id': user_fb_id},
			success: function(data){
				if(parseInt(data[0]) > 0){
					$("#printlatermodal").reveal({
						animation: 'fade'
					 });
					 $('.reveal-modal').css("display","inline");
					 
					 // Refresh Saved Deals List
					 refreshSavedDealsForLater(data[1]);
				}
			},
			error: function(xhr, error){
			}
		});
	});


	// When the PRINT button is clicked
	$(".printbutton").click(function() {
		
		// Checking for timeout
		var current_date_time = new Date();
		var time_diff = (current_date_time - start_date_time) / 1000;
		if(time_diff > print_timeout_value)
		{
			$('#timeoutmodal').reveal({
				animation: 'fade'
			 });
			$('.reveal-modal').css("display","inline");
		}
		else
		{
			var item_id = $("[name='hdn_item_id']").val();
			var saved_for_later_id = $(this).attr('data-saved-for-later-id');
			func_params = [item_id, saved_for_later_id, 'print'];
			printItem(item_id, saved_for_later_id);
		}

	});
	
	// When the 'Click here to Print your Saved Deal Now' link is clicked
	$(".printsaveddeal ul li a").live('click', function(){
		var item_id = $(this).attr('data-item-id');
		var saved_for_later_id = $(this).attr('data-saved-for-later-id');
		$('.printbutton').attr('data-saved-for-later-id', saved_for_later_id);
		$("[name='hdn_item_id']").val(item_id);

		populateItemData(item_id);
		
		$('#coupon_page_list').slideUp({ duration: 10, easing: 'linear'});
		$('#coupon_print_page').slideDown({ duration: 1000, easing: 'linear'});
		$('#printnowlater').slideUp({ duration: 0, easing: 'linear'});
		$('#couponrendered').slideDown({ duration: 500, easing: 'linear'});
		$('.cancel_print').html('Back to Deals');
	});
	
	
	// When the Cancel or Back to Deals button is clicked
	$('.cancel_print, .backtodeals').click(function(){
		$('#coupon_print_page').slideUp({ duration: 1000, easing: 'linear'});
		$('#coupon_page_list').slideDown({ duration: 1000, easing: 'linear'});
		$("#timeoutmodal").trigger('reveal:close');
		$("#printlatermodal").trigger('reveal:close');
	});
	
	// When the 'Show me the Permissions' button is clicked
	$('.backtoperms').click(function() {
		// console.log('func_params: ' + func_params);
		$('#permissionsmodal').trigger('reveal:close');

		
		switch(func_params[2])
		{
			case 'share':
				getPermissionsAndShowDialog(func_params[0], func_params[1]);
				break;
			
			case 'print':
				printItem(func_params[0], func_params[1]);
				break;
			
			case 'customer_supplied_code':
				getCustomerSuppliedCode(func_params[1]);
				break;
		}
	});
	
	
	// When the 'Close' button on 'permissionsmodal' is clicked
	$('#backtoperms-close').click(function() {
		$('#permissionsmodal').trigger('reveal:close');
	});
	
/******************************************************************
*					FUNCTIONS FOR SHARE OPTIONS END HERE					*
*******************************************************************/
	/*
	$('.renderingtext')
	.hide()  // hide it initially
	.ajaxStart(function() {
		$(this).show();
		console.log($(this));
	})
	.ajaxStop(function() {
		$(this).hide();
		console.log($(this));
	});
	*/
});

function trackButtonClick(column_name, column_value, items_views_id)
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