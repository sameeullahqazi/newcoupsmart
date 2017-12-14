<?php
	// require_once ((dirname(__DIR__)) . '/includes/constants.php');
	require_once ((dirname(__DIR__)) . '/includes/app_config.php');
	/*
	require_once ((dirname(__DIR__)) . '/classes/BasicDataObject.class.php');
	require_once ((dirname(__DIR__)) . '/classes/Database.class.php');
	require_once ((dirname(__DIR__)) . '/classes/Customer.class.php');
	require_once ((dirname(__DIR__)) . '/classes/Common.class.php');
	require_once ((dirname(__DIR__)) . '/classes/Company.class.php');
	require_once ((dirname(__DIR__)) . '/classes/Item.class.php');
	require_once ((dirname(__DIR__)) . '/classes/UserItems.class.php');
	require_once ((dirname(__DIR__)) . '/classes/CacheImage.class.php');
*/
	global $app_id, $app_secret, $app_url, $app_ns;
	global $app_version;
	$facebook = new Facebook(array(
		'appId'  => $app_id,
  		'secret' => $app_secret,
  		'cookie' => true,
  		'version' => 'v' . $app_version
	));
	
	header('P3P: CP="CAO DSP CURa ADMa OUR SAMi IND PHY ONL COM NAV DEM LOC"');
	header('X-Frame-Options: GOFORIT');
	$fb_app_url = '//apps.facebook.com/' . $app_ns;
	$app_class = empty($company_sdw_unique_codes_id) ? 'smartdeals' : 'webdeals';
	if (strstr($_SERVER['HTTP_REFERER'], $fb_app_url)) {
		$app_class = 'smartdeals smartdeals-index';
		
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
	<title>Social Deals</title>
	<meta charset="utf-8" />
<!-- Externally Hosted Scripts -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
	<!-- <script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js" type="text/javascript" charset="utf-8"></script>-->
	<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
	<?php if(!$bees_on){ ?>
		<!--<script src="//connect.facebook.net/en_US/sdk.js"></script>-->
		<script type="text/javascript">
			var app_version = '<?php print $app_version;?>';
		</script>
	<?php } ?>
	
<!-- Internally Hosted Scripts -->
	<script src="/js/jquery.example.min.js" type="text/javascript"></script>
	<script src="/js/share-coupon.php" type="text/javascript"></script>
	<script src="/js/jquery.reveal.js" type="text/javascript"></script>
	<script src="/js/jqhelpers.js" type="text/javascript"></script>
	<script src="/js/ddslick.js" type="text/javascript"></script>
	<script src="/js/common_js.php" type="text/javascript"></script>
	<script src="/js/global/modernizr.foundation.js" type="text/javascript"></script>
	<script src="/js/objects.js" type="text/javascript"></script>
	
	
<!-- Internally Hosted Styles -->
	<link rel="stylesheet" href="/helpers/facebook/application/library/styles/facebookstyles.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="/css/coupicon.css" />
	<link rel="stylesheet" href="/css/jquery-ui-1.8.6.custom.css" type="text/css" />
	<link rel="stylesheet" href="/css/friendslist.css" type="text/css" />
	<link rel="stylesheet" href="/css/share.css" type="text/css" />
<!-- Fan Deal Specific Styles -->
	<link rel="stylesheet" href="/css/coupon_layouts.css?<?php print md5(uniqid());?>" type="text/css" />
	<?php if(!empty($meta_for_layout)) print $meta_for_layout; ?>

</head>
<body class="<?php echo $app_class; ?>">
	
	<?php if(!$bees_on){ ?>
	<script src="/js/basic/foundation.js" type="text/javascript"></script>
	<script src="/js/basic/foundation.reveal.js" type="text/javascript"></script>
	<script src="/js/basic/foundation.tooltips.js" type="text/javascript"></script>
	<script type="text/javascript">
    </script>
	<?php } ?>

	<?php print $content_for_layout; ?>
	<div id="fb-root"></div>
<script type="text/javascript">
<!--
$(document).foundation();
var basic_permissions_granted = false;
var publish_actions_permission_granted = false;
var status_change_triggered = false;

window.fbAsyncInit = function() {
	FB.init({appId: '<?php print $app_id;?>', status: true, cookie: true, xfbml: true, version: 'v' + app_version});
	FB.Event.subscribe('edge.create', function(href, widget) {
		// Do something, e.g. track the click on the "Like" button here
		var user_id = '<?php print $user_id;?>';
		var item_id = '<?php print $item_id;?>';
		var company_id = '<?php print $company_id;?>';
		var facebook_page_id = '<?php print $page_id;?>';
		var page_link = '<?php print $app_link;?>';
		// alert('You just liked the page ' + href + '.user_id: ' + user_id + ', item_id: ' + item_id + '. Reloading now...'); 
		$.ajax({
			type: "POST",
			url: "/helpers/ajax-fb-likes.php",
			dataType: 'json',
			data: "user_id=" + user_id + "&item_id=" + item_id + "&company_id=" + company_id + "&facebook_page_id=" + facebook_page_id + "&page_link=" + page_link,
			success: function(data){
					if(data != 1)
							alert("Error logging the facebook like: " + data);
					 if(page_link.indexOf('smart-deals-web') >= 0)
					{
							window.location.href = '<?php print $app_link; ?>';
					}
					else
					{
							top.location.href = '<?php print $app_link; ?>';
					}

			}
		});
	});

	FB.Event.subscribe('auth.statusChange', function(response) {
		if(!status_change_triggered)
		{
			status_change_triggered = true;
			if(response && response.status == 'connected')
			{
				var access_token = response.authResponse.accessToken;

				var permissions = null;
				FB.api('/v' + app_version + '/me/permissions?access_token=' + access_token, function(response2) {
					if(response2.data[0])
					{
						permissions = response2.data[0];
						if(permissions['publish_actions'] != undefined)
							publish_actions_permission_granted = true;

						basic_permissions_granted = true;
	
					}
				});

			}
		}

	});

	FB.Canvas.setAutoGrow();
};
// FB.Canvas.setAutoGrow();
(function() {
	 var e = document.createElement('script');
	 e.type = 'text/javascript';
	 e.src = document.location.protocol + '//connect.facebook.net/en_US/sdk.js';
	 e.async = true;
	 document.getElementById('fb-root').appendChild(e);
 }());
//-->
</script>
</body>
</html>