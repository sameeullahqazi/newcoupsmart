<?php

$canvas_request = null;
if(!empty($_GET['request'])) {
	//clean it
	$request = Database::mysqli_real_escape_string($_GET['request']);

	//now we need to get which controller/view from the full path
	$request_params = explode('/', $request);
	$request = $request_params[0];
	// error_log('request_params = ' . var_export($request_params, true));


	if(strtolower($request) == 'canvas') {
		$canvas_request = $request_params[1];
	}
}

echo "
	<style type='text/css' scoped>
	#support-footer-bar .row {
		width: 100%;
		margin-left: auto;
		margin-right: auto;
		margin-top: 0;
		margin-bottom: 0;
		max-width: 62.5em;
		*zoom: 1;
		}
	#support-footer-bar .row:before, .row:after {
		content: ' ';
		display: table; }
		.row:after {
		clear: both; }
	#support-footer-bar .row .column,
	#support-footer-bar .row .columns {
		position: relative;
		padding-left: 0.9375em;
		padding-right: 0.9375em;
		width: 100%;
		float: left; }
	#support-footer-bar .row.collapse .column,
	#support-footer-bar .row.collapse .columns {
		position: relative;
		padding-left: 0;
		padding-right: 0;
		float: left; }
	#support-footer-bar .row .row {
		width: auto;
		margin-left: -0.9375em;
		margin-right: -0.9375em;
		margin-top: 0;
		margin-bottom: 0;
		max-width: none;
		*zoom: 1; }
	#support-footer-bar .row .row:before, #support-footer-bar .row .row:after {
		content: ' ';
		display: table; }
	#support-footer-bar .row .row:after {
		clear: both; }
	#support-footer-bar .row .row.collapse {
		width: auto;
		margin: 0;
		max-width: none;
		*zoom: 1; }
	#support-footer-bar .row .row.collapse:before, #support-footer-bar .row .row.collapse:after {
		content: ' ';
		display: table; }
	#support-footer-bar .row .row.collapse:after {
		clear: both; }
	#support-footer-bar .row .column,
	#support-footer-bar .row .columns {
		position: relative;
		padding-left: 0.9375em;
		padding-right: 0.9375em;
		float: left; }
	#support-footer-bar .row .small-1 {
		position: relative;
		width: 8.33333%; }
	#support-footer-bar .row .small-2 {
		position: relative;
		width: 16.66667%; }
	#support-footer-bar .row .small-3 {
		position: relative;
		width: 25%; }
	#support-footer-bar .row .small-4 {
		position: relative;
		width: 33.33333%; }
	#support-footer-bar .row .small-5 {
		position: relative;
		width: 41.66667%; }
	#support-footer-bar .row .small-6 {
		position: relative;
		width: 50%; }
	#support-footer-bar .row .small-7 {
		position: relative;
		width: 58.33333%; }
	#support-footer-bar .row .small-8 {
		position: relative;
		width: 66.66667%; }
	#support-footer-bar .row .small-9 {
		position: relative;
		width: 75%; }
	#support-footer-bar .row .small-10 {
		position: relative;
		width: 83.33333%; }
	#support-footer-bar .row .small-11 {
		position: relative;
		width: 91.66667%; }
	#support-footer-bar .row .small-12 {
		position: relative;
		width: 100%; }
	.right {
		float: right;
		}
	.left {
		float: left;
		}

	#support-footer-bar {
		font-size: 14px;
		position: relative;
		display:inline-block;
		bottom: 0;
		width: 100%;
		margin-top: 25px;
		}
	#support-footer-bar #footer-bar-tab, #support-footer-bar #footer-bar-content {
		font-family: 'Helvetica Neue', HelveticaNeue, Helvetica, Arial, 'Lucida Grande', sans-serif;
		font-size: 1em;
		}
	#support-footer-bar #footer-bar-tab, #footer-privacy-policy {
		display: inline-block;
		background-color: #1B89CA;
		padding: 12px 10px;
		color: #fff;
		border-radius: 6px;
		}
	#support-footer-bar .footer-bar-tab {
		cursor: pointer;
		}
	#support-footer-bar.content-shown #footer-bar-tab {
		border: none;
		}
	#support-footer-bar #footer-bar-content {
		background-color: #EAE9EC;
		color: #1B89CA;
		/* padding: 12px 10px; */
		display: block;
		border: 1px solid #1B89CA;
		}
	#support-footer-bar .support-content-header {
		background-color:#1B89CA;
		color:#fff;
		padding: 10px 12px;
		font-size: 1.2em;
		margin-bottom: 10px;
		}
	#support-footer-bar a {
		text-decoration: underline;
		color: #333;
		}
	#support-footer-bar p {
		text-decoration: none;
		color: #333;
		margin-top: 5px;
		line-height: 1.4em;
		}
	#support-footer-bar ul li {
		margin-top: 5px;
		list-style: none;
		line-height: 1.4em;
		}
	#support-footer-bar.content-shown {
		border-bottom: 0 !important;
		}
	</style>
	<div id='support-footer-bar'>
		<div id='footer-bar-tab' class='footer-bar-tab'>
			<b>Need Help?</b>
		</div>
		<div style='display:block;'><!-- Necessary for IE8 hide/show bug fix, for show with custom timed animation. Div that is shown must follow a block element. Weird. --></div>
		<div id='footer-bar-content' style='display:none;'>
			<div class='support-content-header'>
					Help Center <i class='footer-bar-tab icon-remove right' style='position:relative;'><b>close</b></i>
				</div>
			<div class='row'>
" ?>
<?php if ($canvas_request == "coupsmart") {
	echo "
		<div class='support-popular-link small-6 column'>
			<b>Popular Troubleshooting Articles</b><br>
			<ul>
				<li><a href='http://support.coupsmart.com/entries/23005278-How-To-Access-Facebook-Coupons-Getting-A-Blank-Page' target='_blank'>How To Access Coupons On Facebook/Getting A Blank Page</a></li>
				<li><a href='http://support.coupsmart.com/entries/23005358-VIDEO-Printing-a-CoupSmart-Coupon' target='_blank'>VIDEO: Printing a Coupon</a></li>
				<li><a href='http://support.coupsmart.com/entries/23005398-I-don-t-have-a-printer-Can-I-have-this-coupon-sent-to-me-' target='_blank'>I don't have a printer, can this offer be sent to me?</a></li>
			</ul>
		</div>
		<div class='support-page-link small-6 column'>
			<b>Still Having Problems?</b>
			<p><a href='http://support.coupsmart.com/forums/20418862-Facebook-Coupons' target='_blank'>Visit our Support Page</a> to find out more information about this app or to contact our support team.</p>
		</div>
		";
	} else if ($canvas_request == "socialgiftshop") {
	echo "
		<div class='support-popular-link small-6 column'>
			<b>Popular Troubleshooting Articles</b><br>
			<ul>
				<li><a href='http://support.coupsmart.com/entries/22996627-How-to-Purchase-a-Gift-From-The-Gift-Shop' target='_blank'>How to Purchase a Gift From The Gift Shop</a></li>
				<li><a href='http://support.coupsmart.com/entries/22981201-I-can-t-seem-to-get-my-voucher-to-print-Why-am-I-having-difficulty-' target='_blank'>I can't seem to get my voucher to print. Why am I having difficulty?</a></li>
				<li><a href='http://support.coupsmart.com/entries/23005858-How-do-I-redeem-my-voucher-' target='_blank'>How do I redeem my voucher?</a></li>
			</ul>
		</div>
		<div class='support-page-link small-6 column'>
			<b>Review Your Orders And Gifts You Have Received.</b>
			<p><a href='http://apps.facebook.com/" . $socgift_app_ns . "' target='_blank'>Visit the Gift Shop App Page</a> to see your activity, check the status on gifts you have sent, and view the gifts that are sent to you.</p>
			<b>Still Having Problems?</b>
			<p><a href='http://support.coupsmart.com/forums/20418862-Facebook-Coupons' target='_blank'>Visit our Support Page</a> to find out more information about this app or to contact our support team.</p>
		</div>
		";
	} else {
	echo "<div class='support-page-link small-12 column'>
			<b>Having Problems With This App?</b>
			<p>Visit our <a href='http://support.coupsmart.com/' target='_blank'>Support Center</a> to find out more information about this app or to contact our support team.</p><!-- canvas_request = " . $canvas_request . ", create new section inside includes/app-support-footer.php that points to this canvas_request to make a section specifically for this app. Otherwise this default area will continue to show. -->
		</div>";
	} ?>
<?php echo "
			</div>
		</div>
	</div>
	<script type='text/javascript'>
			$(document).ready(function() {
				$('.footer-bar-tab').click(function () {
					if ($('#footer-bar-content').is(':hidden')) {
						$('#footer-bar-content').delay(100).slideDown(300);
						$('#support-footer-bar').addClass('content-shown');
						$('#footer-bar-tab').fadeOut(100);
					} else {
						$('#footer-bar-content').slideUp(300);
						$('#support-footer-bar').removeClass('content-shown');
						$('#footer-bar-tab').delay(350).fadeIn(100);
					};
				});
				$('#footer-privacy-policy').click(function () {
					$('#footer-privacy-policy-content').toggle('slow');
				});
			});
	</script>
	"

?>