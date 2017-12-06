<?php
	require_once(dirname(__DIR__) . '/includes/app_config.php');
	require_once(dirname(__DIR__) . '/includes/facebook-php-sdk/src/facebook.php');
	
	require_once (dirname(__DIR__) . '/classes/Database.class.php');
	require_once (dirname(__DIR__) . '/classes/BasicDataObject.class.php');
	require_once(dirname(__DIR__) . '/classes/Session.class.php');
	require_once (dirname(__DIR__) . '/classes/User.class.php');

	global $app_id, $app_secret, $app_url;
	$page_id = !empty($_REQUEST['page_id']) ? $_REQUEST['page_id'] : '';
	
	global $app_version;
	if(!empty($app_version))
		print "app_version = '$app_version';";

	// error_log('js request env: ' . var_export($_REQUEST, true));

	$db = new Database();
	try{
		$db->connect();
	} catch(Exception $e)
	{
		Errors::show500();
	}



	$php_session = new Session();

	session_start();

	$facebook = new Facebook(array(
	  'appId'  => $app_id,
	  'secret' => $app_secret,
	  'cookie' => true,
	  'version' => 'v' . $app_version
	));
	$access_token = $facebook->getAccessToken();

// We may or may not have this data based on a $_GET or $_COOKIE based session.
//
// If we get a session here, it means we found a correctly signed session using
// the Application Secret only Facebook and the Application know. We dont know
// if it is still valid until we make an API call using the session. A session
// can become invalid if it has already expired (should not be getting the
// session back in this case) or if the user logged out of Facebook.
$session_user = $facebook->getUser();
	//post_to_friends_wall('100001941142373');

$me = null;
// Session based API call.
// error_log('session_user = ' . var_export($session_user, true));
if ($session_user) {
	$uid = $facebook->getUser();
	try
	{
	$me = $facebook->api('/me?access_token='.$access_token);
	}
	catch(Exception $e)
	{
		error_log("FAcebokk API error: ".$e->getMessage());
	}
	// error_log('me = ' . var_export($me, true));
}
	//error_log("Session in share-coupon: ".var_export($_SESSION, true));
	header('Content-Type: text/javascript');

?>
var curr_selected_image_id = 0;
var campaign_image = 0;
var image_file = '';
var success = null;


$("a.thumb").live("click", function() {
	var image_id = $(this).attr("image_id");

	image_file = $(this).children().attr("src");
	if(curr_selected_image_id > 0)
		$('#selected_img_' + curr_selected_image_id).hide();

	curr_selected_image_id = image_id;

	var position = $(this).position();
	var left = position.left;
	var top = position.top;

	$('#selected_img_' + image_id).css('left', left + 2);
	$('#selected_img_' + image_id).css('top', top + 2);
	$('#selected_img_' + image_id).show();


});

function SocialGiftShopSendRequest(user_id) {
  // console.log(user_id);
  FB.ui({method: 'apprequests',
    message: 'My Great Request',
    to: user_id
  }, function(response){
  	// console.log(response);
  
  });
}


function send_fb_share_request(item_id, share_bonus, name, deal, company_name, social_name, social_deal, ptype)
{
	// console.log('send_fb_share_request');
	if(curr_selected_image_id == 0 && campaign_image == 1)
	{
		alert('Please select an image');
		return false;
	}
	
	var user_id = 0;
	var request_id = '';
	if(deal === null || deal == ''){
		deal = '';
	}
	var share_msg = 'I thought you might like this coupon for ' + name + ' ' + deal + ' from ' + company_name + '.';
	if (share_bonus == 1 && ptype != 1) {
		//share_msg += ' Share it with more friends to get ' + social_name + ' ' + social_deal + '.';
		share_msg = ' Share it with more friends to get ' + social_name + ' ' + social_deal + '.';
	}

	FB.init({
		appId  : '<?php echo $app_id; ?>',
		status : true, // check login status
		cookie : true, // enable cookies to allow the server to access the session
		xfbml  : true,  // parse XFBML
		oauth  : true,
		version    : 'v' + app_version
	});
	
	var access_token = null;
	var permission_granted = 0;
	FB.getLoginStatus(function(response) {
		if (response.authResponse) {
		} else {
			FB.login(function (response) {});
		}
		if (response.status == 'connected') {
			permission_granted = 1;

			access_token = response.authResponse.accessToken;
			//console.log("access_token: ");
			//console.log(access_token);


			FB.ui({method: 'apprequests', display: 'popup', message: share_msg, data: item_id}, function(response) {
				if (response && response.request_ids) {
					// console.log("Logging Response: ");
					// console.log(response);
					FB.api('/me/apprequests/?access_token=' + encodeURIComponent(access_token) + '&request_ids=' + encodeURIComponent(response.request_ids),
						function(response2) {
					//		console.log("Logging Response2: ");
					//		console.log(response2);
						}
					);


					var request_ids = response.request_ids;



					//alert("User id: " + user_id);

					$.ajax({
						type: "POST",
						url: "/helpers/ajax-referral.php",
						dataType: 'json',
						data: "operation=SEND_SHARE_REQUEST&request_ids=" + request_ids + "&item_id=" + item_id + "&image_id=" + curr_selected_image_id,
						success: function(data){
							// set html of the iframe
							var num_friends_left_to_share = parseInt(data[0]);
							if (share_bonus == 1 && ptype != 1) {
								if(num_friends_left_to_share == 0)
								{
									// In case of regular coupons, do not redirect to the print screen
									if (ptype != 1)
										top.location.href = "/signup?print=" + item_id + "&ptype=" + ptype;
								} else {
									$('#span-num-friends-left').html(num_friends_left_to_share);
									$('#div-share-with-friends').show();
								}
							}
						}
					});
				}
				else
				{
				}
			});
		} else {
			// console.log('User is logged out');
		}

		$.ajax({
			type: "POST",
			url: "/helpers/ajax-fb-perms-req-log.php",
			dataType: 'json',
			data: "permission_granted=" + permission_granted + "&item_id=" + item_id,
			success: function(data){
				//alert(data);
			}
		});
	});
}




function show_facebook_friends_for_post()
{
	// console.log('show_facebook_friends_for_post');
	window.open('show-facebook-friends.php','');
}



function show_share_smart_dialog(item_id, ptype)
{	
	// console.log('show_share_smart_dialog');
	var sharing_bonus;
	var name;
	var offer;
	var display_name;
	var social_offer_service_name;
	var social_offer;
	var dlg_title = '';
	var share_bonus;
	$.ajax({
		type: "POST",
		url: "/components/campaign-images.php",
		dataType: "json",
		data: "item_id=" + item_id + "&ptype=" + ptype,
		success: function(data){
			$('#ul_share_images').empty();
			if(data['images'] == '' || data['images'] == null){
				campaign_image = 0;
				$('#ul_share_images').hide();
				$('#click-images').hide();
			}else{
				campaign_image = 1;
				$('#ul_share_images').html(data['images']);
				$('#ul_share_images').show();
				$('#click-images').show();
			}
			sharing_bonus = data['sharing_bonus'];
			name = data['name'];
			offer = data['offer'];
			display_name = data['display_name'];
                        social_offer_service_name = data['social_offer_service_name'];
                        social_offer = data['social_offer'];
                                 
			if(data['sharing_bonus'] && ptype == 2){
				dlg_title = 'Share for Bonus';
			}else{
				dlg_title = 'Share this Deal';
			}

		}
	});
	
	setTimeout(function() {
		var thedialog = $("#div_smart_share_dialog").dialog({
			height: 200,
			width: 220,
			autoOpen: false, 
			modal: true,
			draggable: true,
			resizable: true,
			title: dlg_title,
			buttons: {
				"Post to own profile": function(){
					post_to_own_profile(item_id <?php echo (!is_null($me['id']))? ", " . $me['id'] : ',0';?>, ptype);
				},
				"Post to friends' profile": function(){
					post_to_friends_profile(item_id, ptype);
				},
				"Send request to friends": function(){
					send_fb_share_request(item_id, sharing_bonus, name, offer, display_name, social_offer_service_name, social_offer, ptype);
				                                
					                                                        
				}
			}
		});
 		if(!thedialog.parents(".ui-dialog").is(":visible")){
 			thedialog.dialog('open');
		}
		$('.ui-widget-overlay').attr('style', 'max-height: 2001px !important;background-color:#AAAAAA; z-index: 1001;'); 
		return false;
	}, 750);
	$('.ui-widget-overlay').attr('style', 'max-height: 2001px !important;background-color:#AAAAAA; z-index: 1001;');
	return false;
}
function log_claimed_attempts(query_string)
{

	$.ajax({
		type: "POST",
		url: "/helpers/ajax-claim-attempts.php",
		dataType: 'json',
		data: query_string,
		success: function(data) {
			// console.log(data);
			//alert(data);
			return data;
		}
	});
}

function post_to_own_profile(item_id, user_id, ptype)
{
	// console.log('post_to_own_profile(' + item_id + ', ' + user_id + ', ' + ptype + ')');
	var share_bonus;
	var name;

	var company_name;
	var social_name;
	var deal;
	var social_deal;
	var share_msg = "";
	
	if(curr_selected_image_id == 0 && campaign_image == 1)
	{
		alert('Please select an image');
		return false;
	}
	var refer_id = null;

	FB.init({
		appId  : '<?php echo $app_id; ?>',
		status : true, // check login status
		cookie : true, // enable cookies to allow the server to access the session
		xfbml  : true,  // parse XFBML
		oauth  : true, // enable OAuth 2.0
		version    : 'v' + app_version
	});
	// console.log('FB.init complete');
	var access_token = null;
	FB.login(function (response) {
		if (response.authResponse) {
			access_token = response.authResponse.accessToken;
			
			var user_likes_page = 0;
			FB.api('/me/likes/<?php echo $page_id; ?>', function(response){
				if (response.data.length > 0) {
					user_likes_page = 1;
				}
			});

			$.ajax({
				type: "POST",
				url: "/components/campaign-images.php",
				dataType: "json",
				data: "item_id=" + item_id + "&ptype=" + ptype,
				success: function(data){
					// console.log('campaign-images success');
					if(data['images'] == '' || data['images'] == null){
						campaign_image = 0;
					}else{
						campaign_image = 1;
					}
					share_bonus = data['sharing_bonus'];
					name = data['name'];
					deal = data['offer'] === null ? '' : data['offer'];
					company_name = data['display_name'];
					social_name =  data['social_offer_service_name'];
					social_deal = data['social_offer'];
					share_ptype = data['share_ptype'];
					//share_msg = name + ' for ' + deal + ' from ' + company_name + '.';
					share_msg = name + ' ' + deal + ' from ' + company_name + '.';
					if (share_bonus && ptype != 1) {
						//share_msg += ' Share it with more friends to get ' + social_name + ' ' + social_deal + '.';
						share_msg = ' Share it with more friends to get ' + social_name + ' ' + social_deal + '.';
					}

					var message = 'I just got this great coupon from ' + company_name + '.';
			
					// console.log("operation=INSERT_REFERRAL&item_id=" + item_id + "&image_id=" + curr_selected_image_id + "&user_id=" + user_id);
					$.ajax({
						type: "POST",
						url: "/helpers/ajax-referral.php",
						dataType: 'json',
						data: "operation=INSERT_REFERRAL&item_id=" + item_id + "&image_id=" + curr_selected_image_id + "&user_id=" + user_id,
						success: function(data){
							// console.log('ajax-referral success');
							refer_id = parseInt(data[0]);
							// alert(refer_id);
							var url = '<?php echo $app_url . '/coupon-preview?referral_id='; ?>' + refer_id + '&user_likes_page=' + user_likes_page;
					
							FB.ui({
								method: 'feed',
								display: 'popup',
								name: share_msg,
								link: url,
								picture:  image_file,
								caption: 'Brought to you by CoupSmart.com',
								description: '<?php echo $app_url; ?>'
							},
							function(response) {
								// console.log('response = ');
								// console.log(response);
								if (response && response.post_id) {
									var request_ids = response.post_id;
									// In case of regular coupons, do not redirect to the print screen
									if (ptype != 1)
										top.location.href = "/signup?ptype="+ptype + "&print=" + item_id;
										// alert('Post was published.');
									} else {
										// alert('Post was not published.');
										delete_referral(refer_id);
									}
								}
							);
						}
					});
				}
			});
		} else {
			alert("You're logged out somehow, please close the sharing window and try again.");
		}	
	},{scope: 'user_likes'});
	
}

function post_to_friends_profile(item_id, ptype)
{
	// console.log('post_to_freinds_profile');
	check_permissions('tab', item_id, ptype);
	if(curr_selected_image_id == 0 && campaign_image == 1)
	{
		alert('Please select an image');
		return false;
	}
	// top.location.href = '<?php echo $app_url; ?>/show-facebook-friends?item_id=' + item_id + '&image_id=' + curr_selected_image_id;
	//post_to_friends_wall('100001941142373');
}

function post_to_friends_wall(friend_id, arr_params, refer_id)
{
	// console.log('post_to_friends_wall');
	FB.init({
		appId  : '<?php echo $app_id; ?>',
		status : true, // check login status
		cookie : true, // enable cookies to allow the server to access the session
		xfbml  : true,  // parse XFBML
		oauth	: true, // enable OAuth 2.0
		version    : 'v' + app_version
	});

	var body = 'Reading Connect JS documentation';

	var link = '/' + friend_id + '/feed';
	eval("var json_arr_params = { " + arr_params.join(',') + " };");
	// alert("json_arr_params: " + arr_params);
/*

	var json_arr_params = { "message": "test message", "picture": "http://profile.ak.fbcdn.net/hprofile-ak-snc4/161432_100001941142373_886410_n.jpg", "link": "http://coupsmart.com", "description": "test descrption", "name": "test name", "caption": "test caption", "source": "http://www.youtube.com/watch?v=8Af372EQLck&feature=related" };
*/
	//alert("json_arr_params: " + json_arr_params);
	FB.api(link, 'post', json_arr_params, function(response) {
		
	  if (!response || response.error) {
		// alert("Error posting to friend's profile!");
		// console.log(response);
		delete_referral(refer_id);
	    return false;
	  } else {
		return true;
	  }
	});
}

function finished_dialog(){
	FB.ui({
		method: 'fbml.dialog',
		display: 'popup',
		message: 'Thank you!',
		name: 'Dialog',
		caption: 'dialog',
		description: 'hello',
		user_message_prompt: 'Share your thoughts about Connect',
		width: 100,
		height: 100
	});

}

function check_permissions(source, item_id, ptype){
	FB.getLoginStatus(function(response) {
  		if (response.authResponse) {
    			// logged in and connected user, someone you know
			// console.log("logged in and connected");
			// alert("authResponse true");
			window.open('<?php echo $app_url; ?>/show-facebook-friends?item_id=' + item_id + '&image_id=' + curr_selected_image_id + '&ptype=' + ptype, '');
		} else {
			// console.log(response.authResponse);
			// no user session available, someone you dont know
			// alert("authResponse false");
			FB.login(function(response) {
				if (response.session) {
					// console.log(response.session);
					if(source == 'tab'){
						//top.location.href = '<?php echo $app_url; ?>/show-facebook-friends?item_id=' + item_id + '&image_id=' + curr_selected_image_id + '&ptype=' + ptype;
						window.open('<?php echo $app_url; ?>/show-facebook-friends?item_id=' + item_id + '&image_id=' + curr_selected_image_id + '&ptype=' + ptype, '');
					} else {
						// console.log('logged in, but no permissions');
						// console.log('logged in, but no session line 468');
						// user is logged in, but did not grant any permissions
					}
				} else {
					// console.log('user is not logged in');
					// user is not logged in
				}
			}, {scope:'publish_actions,email,user_birthday,user_location,user_about_me,user_relationships'});
		}
	});
}

function create_referral_id(arr_params, selected_values, item_id, image_id, ptype){

	var share_bonus;
	var name;

	var company_name;
	var social_name;

	var deal;
	var social_deal;
	var share_msg;

	var refer_id = null;

	$.ajax({
		type: "POST",
		url: "/components/campaign-images.php",
		dataType: "json",
		data: "item_id=" + item_id,
		success: function(data){


			share_bonus = data['sharing_bonus'];
			name = data['name'];
			deal = data['offer'] === null ? '' : data['offer'];
			company_name = data['display_name'];
			social_name =  data['social_offer_service_name'];
			social_deal = data['social_offer'];
			share_ptype = data['share_ptype'];

			share_msg = 'I thought you might like this coupon for ' + name + ' ' + deal + ' from ' + company_name + '.';
			if (share_ptype == 2) {
				//share_msg += ' Share it with more friends to get ' + social_name + ' ' + social_deal + '.';
				share_msg = ' Share it with more friends to get ' + social_name + ' ' + social_deal + '.';
			}

			for(var i in selected_values){
				insert_friend_referral(share_msg, image_id, item_id, arr_params, selected_values, i);

			}

			if(ptype != 1){
				var t = setTimeout(function(){top.location.href = "/signup?ptype=" + share_ptype + "&print=" + item_id;}, 1000);
			}else{
				alert('Thank you for sharing!');
			}
			
		}
	});

}

function insert_friend_referral(share_msg, image_id, item_id, arr_params, selected_values, i){
	var user_id = <?php echo (!is_null($me['id']))? $me['id']: 0; ?>;
	$.ajax({
		type: "POST",
		url: "/helpers/ajax-referral.php",
		dataType: 'json',
		data: "operation=INSERT_FRIEND_REFERRAL&item_id=" + item_id + "&image_id=" + image_id + "&recipient_id=" + selected_values[i] + "&user_id=" + user_id,
		success: function(data){
			refer_id = parseInt(data[0]);
			// alert(refer_id);
			var url = '<?php echo $app_url . '/coupon-preview?referral_id='; ?>' + refer_id + "&user_id=" + user_id;
			arr_params.push('"link": ' + '"' + url + '"');
			arr_params.push('"name": ' + '"' + share_msg + '"');
			var result = post_to_friends_wall(selected_values[i], arr_params, refer_id);


		}
	});

}

function delete_referral(refer_id){
	$.ajax({
		type: "POST",
		url: "/helpers/ajax-referral.php",
		dataType: 'json',
		data: "operation=DELETE_REFERRAL&refer_id=" + refer_id,
		success: function(data){

		}
	});

}

function getImg(src, width, height, bucket) {
	var result;
	if(bucket==null){
		bucket = 'uploads.coupsmart.com';
	}
	$.ajax({
		type: "POST",
		url: "/helpers/ajax-get-img.php",
		dataType: 'json',
		async: false,
		data: "bucket=" + encodeURIComponent(bucket) + "&src=" + encodeURIComponent(src) + "&w=" + encodeURIComponent(width) + "&h=" + encodeURIComponent(height),
		success: function(data) {
			result = data;
			return data;
		},
		error:function (xhr, textStatus, thrownError){
			alert(textStatus);
			alert(thrownError);
		} 
	});
	return result;
}
