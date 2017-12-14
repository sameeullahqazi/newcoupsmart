<?php
	// require_once ((dirname(__DIR__)) . '/includes/constants.php');
	require_once ((dirname(__DIR__)) . '/includes/app_config.php');
	global $app_version;
?>
<!DOCTYPE html>
<html xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:og="http://opengraphprotocol.org/schema/">
<head>
	<script type="text/javascript">
		var app_version = '<?php print $app_version;?>';
	</script>
	
	<link rel="stylesheet" id="nonmobile" href="/css/mobileoffers.css" type="text/css" />
	<link rel="stylesheet" id="coupicon" href="/css/coupicon.css" type="text/css" />
	<link rel="stylesheet" id="share" href="/css/share.css" type="text/css" />
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.22/jquery-ui.min.js"></script>
	<script src="//connect.facebook.net/en_US/sdk.js" type="text/javascript"></script>
	<script src="/js/mobileesp.js" charset="utf-8"></script>
	<script src="/js/mobileoffers/foundation/foundation.js"></script>
	<script src="/js/mobileoffers/foundation/foundation.forms.js"></script>
	<script type="text/javascript" src="/js/serializeForm.js"></script>
	<script type="text/javascript" src="/js/share-coupon.php"></script>
	<script src="/js/basic/foundation.reveal.js" type="text/javascript"></script>
	<script type="text/javascript" src="/js/country-state-drop-downs.js"></script>
	<script src="/js/objects.js" type="text/javascript"></script>
	
	<script>
  		$(document).foundation();
	</script>
	
	<title><?php echo htmlspecialchars($company->display_name); ?> In-Store Offer</title>
	
	<meta name="viewport" content="initial-scale=1.0, width=device-width" />
	
	<?php
		if(!empty($meta_for_layout)) print $meta_for_layout;
	?>
	<style type="text/css">
		<?php 
			echo $white_label_css;
		?>
	</style>
</head>

<body class="instore">
	<?php print $content_for_layout; ?>
</body>

</html>