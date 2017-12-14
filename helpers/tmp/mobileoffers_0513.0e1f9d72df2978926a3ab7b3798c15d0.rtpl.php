<?php if(!class_exists('raintpl')){exit;}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Your Mobile Offers Deal</title>
		<style type="text/css">
			/* ===== Client-Specific Styles ===== */
			img { display: block; margin-bottom: 0 !important; }
			#outlook a{padding:0;}
			body{width:100% !important;} .ReadMsgBody{width:100%;} .ExternalClass{width:100%;}
			.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
			body{-webkit-text-size-adjust:none;} 
			p {margin: 1em 0;}
			/* ==== Reset Styles ===== */
			body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
			img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
			a img {border:none;} 
			.image_fix {display:block;}
			table td{border-collapse:collapse;}
			#backgroundTable{height:100% !important; margin:0; padding:0; width:100% !important;}
			body, #backgroundTable{
				background-color:#FAFAFA;
			}
			h1, .h1{
				color:#202020;
				display:block;
				font-family:Arial;
				font-size:34px;
				font-weight:bold;
				line-height:120%;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				text-align:left;
			}
			h2, .h2{
				color:#202020;
				display:block;
				font-family:Arial;
				font-size:30px;
				font-weight:bold;
				line-height:120%;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				text-align:left;
			}
			h3, .h3{
				color:#202020;
				display:block;
				font-family:Arial;
				font-size:26px;
				font-weight:bold;
				line-height:120%;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				text-align:left;
			}
			h4, .h4{
				color:#202020;
				display:block;
				font-family:Arial;
				font-size:22px;
				font-weight:bold;
				line-height:120%;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				text-align:left;
			}
			h5, .h5{
				color:#202020;
				display:block;
				font-family:Arial;
				font-size:18px;
				font-weight:bold;
				line-height:120%;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				text-align:left;
			}
			/* ==== Main Email Styles ===== */
			#templateContainer{
				border: 1px solid #DDDDDD;
			}
			#templateHeader{
				background-color:#FFFFFF;
				border-bottom:0;
			}
			.headerContent{
				color:#202020;
				font-family:Arial;
				font-size:34px;
				font-weight:bold;
				line-height:100%;
				padding:0;
				text-align:center;
				vertical-align:middle;
			}
			.headerContent a:link, .headerContent a:visited{
				color:#336699;
				font-weight:normal;
				text-decoration:underline;
			}
			#headerImage{
				height:auto;
				max-width:600px !important;
			}
			#templateContainer, .bodyContent{
				background-color:#FFFFFF;
			}
			.bodyContent div{
				color:#505050;
				font-family:Arial;
				font-size:16px;
				line-height:150%;
				text-align:left;
			}
			.bodyContent div a:link, .bodyContent div a:visited {
				color:#336699;
				font-weight:normal;
				text-decoration:underline;
			}
			.bodyContent img{
				display:inline;
				height:auto;
			}
			.bodyContent div p.error{
				color: #c60f13;
			}
			.footerContent div{
				color:#505050;
				font-family:Arial;
				font-size:14px;
				line-height:150%;
				text-align:left;
			}
			.footerContent div a:link, .footerContent div a:visited {
				color:#336699;
				font-weight:normal;
				text-decoration:underline;
			}
			.footerContent img{
				display:inline;
				height:auto;
			}
			div#requiredUnsubscribe{
				font-size: 12px;
			}
			table.recipientItems {
				border: 1px solid #ccc;
			}
		</style>
	</head>
	<body>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="backgroundTable">
			<tr>
				<td align="center" valign="top">
					<br />
					<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer">
						<tr>
							<td align="center" valign="top">
								<!-- ===== Begin  Header ===== -->
								<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader">
									<tr>
										<td class="headerContent">
											<!-- ==== begin GIANT HEADER IMAGE ==== -->
											<img src="<?php echo $emailHeaderImageNew;?>" alt="" style="width:600px;" class="image_fix" id="headerImage" />
											<!-- ==== end GIANT HEADER IMAGE ==== -->
										</td>
									</tr>
								</table>
								<!-- ===== End Header ===== -->
							</td>
						</tr>
						<tr>
							<td align="center" valign="top">
								<!-- ==== Begin Content ==== -->
								<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody">
									<tr>
										<td valign="top" class="bodyContent">
											<!-- ==== Main Message Content ==== -->
											<table border="0" cellpadding="20" cellspacing="0" width="100%">
												<tr>
													<td valign="top">
														<!-- img src="http://i.imgur.com/dxjpPTJ.jpg" class="image_fix" id="companyLogoImg" alt="" style="width:160px;height:160px;" / -->
													</td>
													<td valign="middle">
														<div>
															<h2 class="h2">Here is your deal from <?php echo $emailHeaderCaption;?></h2>
														</div>
													</td>
												</tr>
												<tr>
													<td colspan="2" valign="top">
														<div>
															<p>Thank you for being our fan! Here is the link to the deal you claimed earlier.</p>
															<p>Please make sure that your printer is turned on, connected to your device, has paper and is ready to print before you click the link.</p>
															<p><a style="font-size:16px;" href="<?php echo $print_url;?>">Click Here To Print Your Deal.</a></p>
														</div>
													</td>
												</tr>
											</table>
											<!-- ==== End Main Message Content ==== -->
										</td>
									</tr>
									<tr>
										<td valign="top" class="bodyContent">
											<table border="0" cellpadding="20" cellspacing="0" width="100%">
												<tr>
													<td valign="top">
														<!-- ==== Begin Client Custom Content ==== -->
															<!-- ////NEEDS HOOKED UP!!div id="clientCustomText">
																Visit our Facebook page at <a href="https://www.facebook.com/<?php echo $facebook_page_id;?>"><?php echo $compName;?></a>.
															</div -->
														<!-- ==== End Client Custom Content ==== -->
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
								<!-- ==== End Content ==== -->
							</td>
						</tr>
					</table>
					<br />
				</td>
			</tr>
			<tr>
				<td align="center" valign="top">
					<br />
					<!-- ==== Start Footer ==== -->
					<table border="0" cellpadding="0" cellspacing="0" width="600" class="footerContent">
						<tr>
							<td style="width:350px;" valign="top">
								<div id="requiredWhyReceive">
									<!-- Feel free to edit this blurb but you are not allowed to delete it, it is legal requirement by the CAN-SPAM Act to be included in all emails -->
									<strong>Why did you receive this email?</strong>
									<div id="receiveMessage">
										<?php echo $receiveMessage;?>

										<!-- You received this email because you claimed a Facebook fan-only offer and elected to have it emailed to this address. This email contains the link to print out your deal voucher. If you did not claim a deal, or believe this email is in error, let us know by reporting it to our <a href="http://support.coupsmart.com">Support Center</a> or by emailing us at support@coupsmart.com. -->
									</div>
								</div>
							</td>
							<td style="width:50px;" valign="top">
								&nbsp;
							</td>
							<td style="width:200px;" valign="top">
								<div id="requiredAddress">
									<!-- Feel free to edit this blurb but you are not allowed to delete it, it is legal requirement by the CAN-SPAM Act to be included in all emails -->
									<strong>Our Mailing Address:</strong><br />
									<div id="compName"><?php echo $compName;?></div>
									<div id="compAdd1"><?php echo $compAdd1;?></div>
									<div id="compAdd2"><?php echo $compAdd2;?></div>
									<div id="compTel"><?php echo $compTel;?></div>
									<div id="compCSZ"><?php echo $compCity;?>, <?php echo $compState;?> <?php echo $compZip;?></div>
									<div id="compEmail"><a href="mailto:<?php echo $email;?>"><?php echo $email;?></a></div>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="3" style="width:600px;" valign="top">
								<br />
								<div id="requiredUnsubscribe">We value your privacy. You're opted in to receive <span class="clientCopyName"><?php echo $compName;?></span> communications. If you no longer wish to receive our marketing e-mails, you may unsubscribe by using the <a href="<?php echo $unsubscribe_link;?>">email settings</a> page. For more information, please read our <a href="http://coupsmart.com/privacy">Privacy Policy</a>.
								<br /><br />
								<span class="clientCopyright">&#169; <?php echo $clientCopyRight;?>. All rights reserved.</span>
								</div>
							</td>
						</tr>
					</table>
					<!-- ==== End Footer ==== -->
					<br />
				</td>
			</tr>
		</table>
	</body>
</html>
