<?php 
	require_once(dirname(__DIR__) . '/includes/app_config.php');
	global $app_version;
	if(!empty($app_version))
		print "app_version = '$app_version';";
?>
function fbInit(app_id){
	FB.init({
		appId:app_id,
		status:true,
		cookie:true,
		xfbml:true,
		oauth:true,
		version    : 'v' + app_version
	});

}

if(typeof String.prototype.trim !== 'function') {
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, ''); 
	}
}

function authButton(triggerId,callback, permissions, mode){
	var trigger = document.getElementById(triggerId),
		perms = (typeof permissions !== 'undefined')? {scope:permissions}: null;
	if (!trigger) {
		throw new Error('No element with ID "'+triggerId+'" exists in DOM');
	}

	if (typeof callback !== 'function') {
		throw new Error('authButton: must pass a callback function');
	}

	trigger.onclick = function(){
	   FB.login(callback,perms);
	   return false;
	};

}

function getManagedPages(callback){
	FB.api('/v' + app_version + '/me/accounts',function(response){
		// console.log('response:');
		// console.log(response);
		var data=response.data;
		callback(data);
	});

}

function userCommonLogin(response){
	if (response.authResponse) {
		var access_token = response.authResponse.accessToken;
		FB.api('/v' + app_version + '/me', function(user_details) {

			var data_in = {};
			data_in['user_details'] = user_details;
			data_in['mode'] = 'login';
			data_in.stringify;
			coupsmartAjax('json', "/helpers/ajax-user-register-fb.php", data_in, function(data){
				$("#new_user_id").val(data.id);
				if(data[0] == "customer" || data[0] == "reseller")
				  {
					createCookie("cs_login",data.join('|')	,7);

					var link = "<?php echo 'http://'. $_SERVER['SERVER_NAME'] . '/'; ?>" + data[0];
					// console.log(link);
					window.location.href = link;
				  }
			});
		});
	}
}

function createCookie(name,value,days) {
	  if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	  }
	  else var expires = "";
	  document.cookie = name+"="+value+expires+"; path=/";
}

function facebookLogin(callback, permissions){
	perms = (typeof permissions !== 'undefined')? {scope:permissions}: null;
	trigger = document.getElementsByTagName('body')[0];
	trigger.onload = function(){
		FB.login(callback,perms);
		return false;

	}
}

function addProfileTab(page, app, access_token, callback){
	FB.api('/v' + app_version + '/' + page + '/tabs', 'POST', { app_id: app, access_token: access_token }, function(response) {
		callback(response, app, page);
	});

}

function deleteProfileTab(page, app, access_token, callback){
	FB.api('/v' + app_version + '/' + page + '/tabs/app_' + app, 'DELETE', {access_token: access_token }, function(response) {
		callback(response, app, page);
	});

}
/*
	Example way to call this function:
	var data_in = {};
	data_in['form_data'] = form_data;
	data_in['mode'] = 'edit';
	data_in.stringify;

	coupsmartAjax('json', "/helpers/ajax-anet.php", data_in, function(data){
		if(data != null && data != true){
			fillErrorDiv(data);
		}else{
			$("#success-box").show();
		}

	});
*/
function coupsmartAjax(data_type, post_url, data_in, callback){
	$.ajax({
		type: "POST",
		url: post_url,
		dataType: data_type,
		data: data_in,
		success: function(data){

			callback(data);
		},
		error:function (xhr, ajaxOptions, thrownError){
			// console.log(xhr);
			// alert(xhr.status);
			// alert(thrownError);
		}
	});
}

// NOTE: THIS FUNCTION MAY NOT WORK FOR OBJECTS!
function removeArrayElement(arr, index)
{
	arr.splice(index, 1);
	return arr;
}

function FBFeedDialog(params, callback_helper, helper_data)
{
	FB.ui(params, function(response) {
		// console.log('response in FBFeedDialog:');
		// console.log(response);
		if(helper_data != undefined)
			helper_data['response'] = response;

		if(callback_helper != undefined)
		{
			$.ajax({
				type: "POST",
				url: '/helpers/' + callback_helper,
				dataType: 'json',
				data: helper_data,
				success: function(data){
				},
				error:function (xhr, ajaxOptions, thrownError){
					// console.log(xhr);
					alert(thrownError);
				}
			});
		}
	});
}

function resetSelectorForm()
{
	$("#txt-choose-friend-share, #txt-choose-group-share, #txt-choose-page-share").val('');
	$('#frm-share-private-message textarea').val('');
}

function pad(a,b){return(1e15+a+"").slice(-b)}

function inArray(myNumber, myArray){
	var length= myArray.length;
	for (var i = 0; i < length; i++){
		if(myArray[i] == myNumber) return true;
	}
	return false;
}

function removeArrayItem(arr, index)
{
	var res = [];
	for(i in arr)
	{
		if(i != index)
			res.push(arr[i])
	}
	return res;
}

function array_unique(arr)
{
	var res = [];
	res = arr.filter(function(elem, pos) {
		 return arr.indexOf(elem) == pos;
	});
	return res;
}

// Prints a voucher item
function print_voucher_item(item_id, user_id, company_url)
{
	var distributor = 1, ptype = 1;
	// Print the coupon
	FB.Canvas.scrollTo(0,0);
	window.location.href = "//<?php echo $_SERVER['SERVER_NAME'];  ?>/helpers/facebook/application/print.php?item_id=" + item_id + "&d=" + distributor + "&ptype=" + ptype + "&user_id=" + user_id + "&company_url=" + encodeURIComponent(company_url) + "&app_name=fan_deals";
}

function callAjaxHelper(data_type, post_url, data_in, callback){
	$.ajax({
		type: "POST",
		url: post_url,
		dataType: data_type,
		data: data_in,
		success: function(data){
			if(callback != undefined)
				callback(data);
		},
		error:function (xhr, ajaxOptions, thrownError){
			// console.log(xhr);
			// alert(xhr.status);
			// alert(thrownError);
		}
	});
}
function http_build_query(query_obj, separator)
{
	if(separator == undefined)
		separator = '&';
		
	var query = "";
	var arr_query = [];
	for(key in query_obj)
	{
		var val = query_obj[key];
		arr_query.push(key + '=' + val);
	}
	query = arr_query.join(separator);
	return query;
}

function parse_url (url) {
	var arr_url_components = url.split('?');
	var uri = arr_url_components[1] != undefined ? arr_url_components[1] : "";
	return uri;
}

function getFBLikesUsingPagination(access_token, limit)
{
	var res;
	$.ajax({
		type: "POST",
		url: '/helpers/ajax-common.php',
		dataType: 'json',
		async: false, // So that the data can be returned back successfully from the function
		data: {'op': 'get_fb_likes', 'access_token': access_token, 'limit': limit},
		success: function(data){
			res = data;
		},
		error:function (xhr, ajaxOptions, thrownError){
			// console.log(xhr);
		}
	});
	return res;
}

function explode(delimeter, str, limit)
{
	limit = limit == undefined ? 1 : limit;
	
	var res = [];
	res = str.split(delimeter);
	
	if(limit > 1)
	{
		var result = [];
		var len = res.length;
		var start = limit - 1;
		var i;
		
		for(i = 0; i < start; i++)
			result.push(res[i]);
		
		var rest_of_the_string = '';
		var rest_of_the_array = [];
		for(var i = start; i < len; i++)
			rest_of_the_array.push(res[i]);
			
		rest_of_the_string = rest_of_the_array.join(delimeter);
		result.push(rest_of_the_string);
		
		return result;
		
	}
	return res;
}

// Returns firstname and lastname
function getFirstAndLastName(name)
{
	var res = [];
	
	var n = name.lastIndexOf(" ");
	var len = name.length;

	res.push(name.substring(0, n));
	res.push(name.substring(n+1, len));

	return res;
}

function isPermissionRejected(granted_permissions, required_permissions)
{
	var permissions_rejected = false;
	
	// Checking for Basic Permissions
	if(granted_permissions['error'] != undefined) // || granted_permissions['data'][0]['installed'] != 1)
	{
		// console.log('Basic Permissions not granted!');
		permissions_rejected = true;
	}
	else
	{
		var tmp_granted_permissions = {};
		for(i in granted_permissions['data'])
		{
			var permission = granted_permissions['data'][i]['permission'];
			var status = granted_permissions['data'][i]['status'];
	
			if(status == 'granted')
				tmp_granted_permissions[permission] = 1;
		}
		// console.log(tmp_granted_permissions);
		
		// Checking for Required Permissions
		for(i in required_permissions)
		{
			// if(granted_permissions['data'][0][required_permissions[i]] != 1)
			if(tmp_granted_permissions[required_permissions[i]] != 1)
			{
				// console.log('One or more Required Permissions not granted!');
				permissions_rejected = true;
				break;
			}
		}
	}
	return permissions_rejected;
}

function share_on_friends_wall(to, link, picture, name, description)
{

	// alert('share_to_friends_wall() called');
	var params = {
		'link': link,
		'method': 'feed',
		'to': to,
		// 'name': name,
		// 'description': description,
		// 'picture': picture,
	};
	
	if(picture != undefined && picture != 'none')
		params['picture'] = picture;
	
	if(name != undefined)
		params['name'] = name;
	
	if(description != undefined)
		params['description'] = description;
	
	// console.log('params when sharing to a friends wall: ');
	// console.log(params);

	FB.ui(params, function(response) {
		
		// console.log('response when posting to a friends wall: ');
		// console.log(response);
	});
}
