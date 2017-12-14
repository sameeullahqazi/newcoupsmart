<?php
require_once(__DIR__ . '/php_mailer/class.phpmailer.php');
require_once(dirname(__DIR__) . '/includes/email_parsing.php');
require_once(dirname(__DIR__) . '/includes/app_config.php');
require_once(dirname(__DIR__) . '/includes/UUID.php');
// require_once(dirname(__DIR__) . '/includes/ses.php');
require_once(dirname(__DIR__) . '/libraries/emogrifier/emogrifier.php');
/*
require_once(dirname(__DIR__) . '/classes/Common.class.php');
require_once(dirname(__DIR__) . '/classes/Company.class.php');
require_once(dirname(__DIR__) . '/classes/PrintMethods.class.php');
require_once(dirname(__DIR__) . '/classes/Resubscribe.class.php');
require_once(dirname(__DIR__) . '/classes/Campaign.class.php');
*/
/**
* Mailer Class
*/

class Mailer
{

	var $to, $cc, $bcc, $subject, $message, $headers, $attachment_paths = array();
	var $user_id = 0, $email_template = "";
	var $from_email = 'support@coupsmart.com', $from_name = 'Coupsmart Support';
	var $foreign_key_table, $foreign_key;
	static $tester_emails = array('mccosham80@hotmail.com', 'fldsofglry@aol.com', 'sweeney.nicholas@gmail.com', 'coupsmartfacebook@gmail.com', 'nickelodeonns@hotmail.com', 'troy@glyss.com', 'troy@digissance.com', 'ethermeme@gmail.com', 'conroye1@nku.edu', 'conroyel@gmail.com', 'jrabinowitz@coupsmart.com', 'kloo@coupsmart.com', 'mtsurov@coupsmart.com', 'fbertsch@coupsmart.com', 'frank.bertsch@gmail.com', 'kloo012+employee@gmail.com', 'untchac@mail.uc.edu', 'auntch@coupsmart.com', 'sqazi@coupsmart.com', 'jheath@coupsmart.com', 'heathj@xavier.edu', 'chefj1991@netscape.net', 'amurray@coupsmart.com', 'apocalypticpony@gmail.com', 'coupsmartmstest@live.com', 'coupsmartytest@yahoo.com', 'lloyd.van1@yahoo.com', 'ethermeme+digissance@gmail.com');
	
	public function __construct()
	{
		$this->headers = 'MIME-Version: 1.0' . "\r\n" .
			'Content-type: text/html; charset=utf8' . "\r\n" .
			'From: support@coupsmart.com' . "\r\n" .
		    'Reply-To: support@coupsmart.com' . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
	}

	public function send_reset_password($user, $hash)
	{
		$this->to = $user->email;
		$this->subject = 'CoupSmart.com Password Reset';
		$this->message = '
		Your password for CoupSmart.com has been flagged for a reset.
		<br /><br />
		<strong>Username:</strong> '. $user->username.' <br /><br />
		<a href="http://' . $_SERVER['SERVER_NAME'] . '/login/confirm/'.$user->id.'/'.$hash.'">Please click here to update your password.</a>
		';
		$this->user_id = $user->id;
		return $this->send();
	}

	

	public function send_age_limit_notification($user)
	{
		$this->to = "info@coupsmart.com";
		$this->subject = 'Registerd user under aged';
		$this->message = '
		The following registered user is aged under 18: <br><br>
		<b>User Name: <b>'.$user->username.'<br>
		<b>First Name: <b>'.$user->firstname.'<br>
		<b>Last Name: <b>'.$user->lastname.'<br>
		';
		$this->user_id = $user->id;
		return $this->send();
	}

	public function send_barcode_approval_notification($user)
	{
		$this->to = $user->email;
		$this->subject = 'Bar code approved';
		$this->message = '
		Your barcode has been approved!
		';
		$this->user_id = $user->id;
		return $this->send();
	}

	public function send_barcode_denial_notification($user)
	{
		$this->to = $user->email;
		$this->subject = 'Bar code denied';
		$this->message = '
		Sorry! Your barcode has been denied!
		';
		$this->user_id = $user->id;
		return $this->send();
	}


	public function newTestimonial($user, $testimonial) {
		$errors = array();
		$this->to = array('bshipley@coupsmart.com', 'tdavis@coupsmart.com');
		$this->subject = 'New testimonial from ' . $user->firstname . " " . $user->lastname . " (" . $user->username . ")";
		$this->message = "New testimonial from:\r\n" . $user->username . "<br>\r\n" . $user->firstname . " " . $user->lastname . "<br>\r\n" . $user->address1 . "<br>\r\n" . (!empty($user->address2) ? $user->address2 . "<br>\r\n" : '') . $user->city . ", " . $user->state . " " . $user->zip . "<br>\r\n<br>\r\nTestimonial:<br>\r\n" . $testimonial;
		if(!$this->send()) {
			$errors["There was an error sending an e-mail to " . $this->to . "."] = 1;
			error_log('There was an error sending an e-mail to ' . $this->to. '.');
		} else {
			error_log('Sent new testimonial to ' . $this->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}


	public function address_invalid($data) {
		if(!empty($data['email'])) {
			if(!$this->email_validate($data['email'])) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $data['email'];
				$this->subject = 'Please verify your mailing address';

				if (!empty($data['is_winner']) &&  $data['is_winner']) {
					$email_template = '../emails/verifyAddressWinner.html';
					$altbody = '
Hi' . (!empty($data['firstname']) ? ' ' . $data['firstname'] : '') . ',

Thanks for signing up for CoupSmart!  We\'re excited to have you as part of the CoupSmart community and start earning great rewards and free stuff.

However, to send any free samples, coupons, gift cards, and products out to you we need to ensure that we have your correct address.  Please click on the link below to either edit your home address or verify that it is correct so we can get your free stuff out to you as soon as possible!

Verify My Address (can we make this a blue button with text in it or something?)

Don\'t forget that every month we give out gift cards and free products to the first few thousand users who scan 30 items or more, and we\'re always adding new deals and free stuff on our website, http://www.coupsmart.com.

Sincerely,

The CoupSmart Team';
				} else {
					$email_template = '../emails/verifyAddress.html';
					$altbody = '
Hi' . (!empty($data['firstname']) ? ' ' . $data['firstname'] : '') . ',

Thanks for signing up for CoupSmart!  We\'re excited to have you as part of the CoupSmart community and start earning great rewards and free stuff.

However, to send any free samples, coupons, gift cards, and products out to you we need to ensure that we have your correct address.  Please click on the link below to either edit your home address or verify that it is correct so we can get your free stuff out to you as soon as possible!

Verify My Address: http://coupsmart.com/address-verify

Don\'t forget that every month we give out gift cards and free products to the first few thousand users who scan 30 items or more, and we\'re always adding new deals and free stuff on our website, http://www.coupsmart.com.

Sincerely,

The CoupSmart Team';
				}

				$htmlBody = file_get_contents($email_template);
				if (!empty($data['firstname'])) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (!empty($data['firstname']) ? ' ' . $data['firstname'] : ''), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->to = $email;
				if(!$this->send()) {
					$errors["There was an error sending an e-mail to $email."] = 1;
					error_log('There was an error sending an e-mail to ' . $email. '.');
				} else {
					error_log('Sent address verification request to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}

		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
	}

	public function phoenix($user) {
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'Don\'t miss out on your CoupSmart rewards this month';
				$email_template = '../emails/Phoenix.html';
				$altbody = '
Hi' . (!empty($user->firstname) ? ' ' . $user->firstname : '') . ',

We\'d just like to remind you that the month is coming to a close, and so is your chance to earn rewards for this month!  Just 30 scans can earn cash, gift cards, coupons, and free samples, as well as entries into our monthly cash giveaway.

As one of our members we\'ve sent rewards to in the past, we\'d also like to let you know that everything is reset each month, so you can get the same rewards again!

Sincerely,

The CoupSmart Team';

				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->user_id = $user->id;
				$this->to = $email;
				if(!$this->send()) {
					$errors["There was an error sending a Phoenix e-mail to $email."] = 1;
					error_log('There was an error sending a Phoenix e-mail to ' . $email. '.');
				} else {
					error_log('Sent Phoenix email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}

		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
	}

	public function minor_alert($user) {
		$mailer = new Mailer;
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = array('bshipley@coupsmart.com');
		$mailer->subject = 'Age change from ' . $user->firstname . " " . $user->lastname . " (" . $user->username . ")";
		$mailer->message = "Age change from:\r\n" . $user->username . "<br>\r\n" . $user->firstname . " " . $user->lastname . "<br>\r\n" . $user->address1 . "<br>\r\n" . (!empty($user->address2) ? $user->address2 . "<br>\r\n" : '') . $user->city . ", " . $user->state . " " . $user->zip;

		$this->user_id = $user->id;
		if(!$mailer->send()) {
			$errors["There was an error sending an e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending an e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent user age change to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public function facebookPhoto($user) {
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'CoupSmart rewards Facebook photo contest';
				$email_template = '../emails/FacebookPhoto.html';
				$altbody = '
Hi' . (!empty($user->firstname) ? ' ' . $user->firstname : '') . ',

I hope you\'ve enjoyed the rewards you\'ve been receiving from CoupSmart.  We try to reward our members as much as possible, so I just wanted to remind you to check out our Facebook fan page.  We\'re working on adding more contests for our members, but right now you can post a picture of you with your rewards for the chance to win a $20 gift card!  We\'ll be giving away one at the end of this month, so be sure to post it before October 1st.

You can find our Facebook Fan Page by clicking here:

href="http://www.facebook.com/CoupSmart

Thanks for using CoupSmart!

Sincerely,

Blake Shipley
Founder & CEO
CoupSmart';

				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->user_id = $user->id;
				$this->to = $email;
				if(!$this->send()) {
					$errors["There was an error sending a FacebookPhoto e-mail to $email."] = 1;
					error_log('There was an error sending a FacebookPhoto e-mail to ' . $email. '.');
				} else {
					error_log('Sent FacebookPhoto email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}

		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
	}
	
	public function emailAlreadySent($email, $template)
	{
		$result = false;
		
		$email = Database::mysqli_real_escape_string($email);
		$template = Database::mysqli_real_escape_string($template);
		
		$sql = "select * from user_emails where email = '$email' and email_template = '$template' and status = 'success'";
		$rs = Database::mysqli_query($sql);
		if(!$rs)
		{
			error_log("Error in the select query in the function, emailAlreadySent(): ".Database::mysqli_error());
		}
		else if(Database::mysqli_num_rows($rs) > 0)
		{
			$result = true;
		}
		return $result;
	}
	
	public function emailWithTemplate($user, $template, $subject, $exempt = 0, $rate = 0, $company_id=null) {
		//error_log("template: ".$template. ", subject: ".$subject);
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = $subject;
				$html_template = dirname(__DIR__). "/emails/" . $template;
				$altbody = ''; // no easy way to do this
				$htmlBody = file_get_contents($html_template);
				$cssBody = '';
				
				$linkStr = "/<link .+?>/";
				$commentStr = "/<!--.*?-->/";
				
				preg_match_all($commentStr, $htmlBody, $comments);
				foreach($comments as $currentComment)
				{
					str_replace($currentComment, '', $htmlBody);
				}
				
				preg_match_all($linkStr, $htmlBody, $cssLinks);
				foreach($cssLinks as $currentCSSLink)
				{
					foreach($currentCSSLink as $currentLink)
					{
						if(preg_match('/text\/css/', $currentLink) && preg_match('/rel="stylesheet"/', $currentLink)){
							preg_match("/\/css\/.+\.css/", $currentLink, $match);
							$cssLocation=$match[0];
							$cssBody.=file_get_contents(dirname(__DIR__).$cssLocation);
						}
					}
				}
				//error_log('css body: '.$cssBody);
				//error_log('htmlFile: '.$emailBody);
				$emo = new emogrifier($htmlBody, $cssBody);
				$emailBody = $emo->emogrify();
				//error_log('emogrified body: '.$htmlBody);
				// $emailBody = $htmlBody;
				
				$vars = get_object_vars($user);
				foreach ($vars as $var => $val) {
					if (!empty($val)) {
						$repl_str = '&lt;!-- tmpl_' . $var . ' --&gt;';
						$emailBody = str_replace($repl_str, $val, $emailBody);
						
						$repl_str = '<!-- tmpl_' . $var . ' -->';
						if ($var == 'firstname') {
							$val = ' ' . $val;
						}
						$emailBody = str_replace($repl_str, $val, $emailBody);
					}
				}
				
				// Add the unsubscribe link at the end of the email if it is not exempted
				if(!$exempt)
				{
					if (is_null($company_id)) //the company id wasn't set
						{
						$baseURL = Common::getBaseURL();
						$emailBody = str_replace('</body>', "<table width='600' cellpadding='0' cellspacing='0' border='0' align='center' style='background-color:#FFFFFF;margin-top:10px;padding-top:10px;padding-bottom:10px;border-top:1px #333 solid;''><tbody><tr><td align='center'><span class='unsubscribe_link'>If you don't want to receive any more emails from us, please <a href=" . $baseURL . "/unsubscribe?email=" . urlencode($user->email). ">click here to unsubscribe.</a></span></td></tr></tbody></table></body>", $emailBody);
						}
					else{ //the company id was set
						$baseURL = Common::getBaseURL();
						$emailBody = str_replace('</body>', "<table width='600' cellpadding='0' cellspacing='0' border='0' align='center' style='background-color:#FFFFFF;margin-top:10px;padding-top:10px;padding-bottom:10px;border-top:1px #333 solid;'><tbody><tr><td align='center'><span class='unsubscribe_link'>If you don't want to receive any more emails from us, please <a href=" . $baseURL . "/unsubscribe?email=" . urlencode($user->email). ";company_id=" . $company_id. ">click here to unsubscribe.</a></span></td></tr></tbody></table></body>", $emailBody);
					}
				}
			}
				// Populate the campaign engagement rates for the particular case
				if(!empty($rate))
				{
					$percent = $rate * 100;
					$emailBody = str_replace('[<i>x</i>%]', "<i>$percent%</i>", $emailBody);
				}

				$this->message = ($emailBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				$this->email_template = $template;

				if(!$this->send('html', NULL, $company_id)) {
					$errors["There was an error sending a emailWithTemplate e-mail to $email."] = 1;
					error_log('There was an error sending a emailWithTemplate e-mail to ' . $email. '.');
				} else {
					error_log('Sent emailWithTemplate message to ' . $email . '.');
				}
				
				
			}
		 else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}

		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
	}
	
	public function HTMLBlockReplace($htmlBody, $items, $htmlDelimiter)
	{
		$delimiter = '<!-- ' . $htmlDelimiter . ' -->';
		list($head, $body, $foot) = explode($delimiter, $htmlBody);
		$finalHtml = '';
		foreach($items as $item){
			$currentBody = $body;
			//each item has own html, and replace each element of that html
			foreach($item as $element => $data){
				$currentBody = Mailer::StringReplace($element, $data, $currentBody);
			}
			$finalHtml .= $currentBody;
		}
		return ($head . $finalHtml . $foot);
	}




	public function SetBasicEmailInfo($subject, $email, $alt_body, $template){
		$this->altbody = $alt_body;
		// error_log("this email is going to :" . $email);
		$this->to = $email;
		$this->email_template = $template;
		$this->subject = $subject;
	}
	
	public function SetEmailBody($htmlBody){
		$this->message = ($htmlBody);
	}
	
	public function AddUnsubscribeLink($htmlBody, $email){
		$baseURL = Common::getBaseURL();
					$htmlBody .= "<hr>If you don't want to receive any more emails from us, please <a href="
					. $baseURL . "/unsubscribe?email=" . urlencode($email)
					. ">click here to unsubscribe.</a>";
		return $htmlBody;
	}
	
	public static function GetTemplateBody($template){
		
		$the_template = dirname(__DIR__)."/emails/" . $template;
		$htmlBody = file_get_contents($the_template);
		
		return $htmlBody;
	}
	
	public static function ReplaceObjectVars($object, $htmlBody){
		
		$vars = get_object_vars($object);
		
		foreach ($vars as $var => $val) {
			if (!empty($val)) {
				$repl_str = '&lt;!-- tmpl_' . $var . ' --&gt;';
				$htmlBody = str_replace($repl_str, $val, $htmlBody);
				
				$repl_str = '<!-- tmpl_' . $var . ' -->';
				if ($var == 'firstname') {
					$val = ' ' . $val;
				}
				$htmlBody = str_replace($repl_str, $val, $htmlBody);
			}
		}
		
		return $htmlBody;
	}
	
	public static function StringReplace($template_string, $replace_string,$htmlBody){
		$htmlBody = str_replace("<!-- $template_string -->", ($replace_string), $htmlBody);
		return $htmlBody;
	}
	
	public function replaceAnchorLinks($emailBody, $user_email_code)
	{
		// Replace anchor links with coupsmart links
		$dom = new DOMDocument();
		$dom->loadHTML($emailBody);
		// error_log("emailBody before replacing: ".$emailBody);
		$tag_names = array('a', 'area');
		foreach($tag_names as $tag_name)
		{
			$lnk_elements = $dom->documentElement->getElementsByTagName($tag_name);
			foreach($lnk_elements as $i => $elem)
			{
				$url_href = $elem->getAttribute('href');
				$url_href = str_replace("'", "\'", $url_href);
				$email_link_code = Common::generate_unique_sig('user_email_links', 'email_link_code');
				
				// Insert email link
				Mailer::insert_email_link($url_href, $email_link_code, $user_email_code);
		
				// Replace href with coupsmart url
				$elem->setAttribute('href', Common::getBaseURL(). "/email-redirect?c=$email_link_code");
			}
		}
	
		// Save changes back to the email body
		$emailBody = $dom->saveHTML();

		// Add a transparent gif image just before the html content ends
		$img_URL = Common::getBaseURL() . '/helpers/ajax-email-img-loaded.php?c='.$user_email_code;
		$emailBody =  str_replace("</body></html>", "<img src='$img_URL' height='1' width='1' alt=''></body></html>", $emailBody);
		
		// error_log("emailBody after replacing: ".$emailBody);
		return $emailBody;
	}
	
	public function send($format = 'html', $user_email_code = null, $company_id=null, $exempt=null)
	{
		global $ses_smtp_username, $ses_smtp_password, $ses_smtp_host;
		
		//error_log("in the mailer!");
		$production = false;
		if (!isset($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] == 'coupsmart.com' || $_SERVER['SERVER_NAME'] == 'www.coupsmart.com') $production = true;
		if ($production || Mailer::all_internal_addresses($this->to)) {
			//error_log("inside the send method.  Checking if the to addresses have been set..." . var_export($this,true));
			if(!empty($this->to) && !empty($this->subject) && !empty($this->message))
			{
				$mail = new PHPMailer();
				$mail->CharSet = 'UTF-8';
				$mail->IsSMTP(); // telling the class to use SMTP
				$mail->Host       = $ses_smtp_host; // "email-smtp.us-east-1.amazonaws.com"; // SMTP server
				$mail->SMTPDebug  = false;                     // enables SMTP debug information (for testing)
												   // 1 = errors and messages
												   // 2 = messages only
				$mail->SMTPAuth   = true;                  // enable SMTP authentication
				$mail->Port       = 465;                    // set the SMTP port for the SES server
				$mail->SMTPSecure = 'ssl';
				$mail->Username   = $ses_smtp_username; // "AKIAIBQXMIRDF65ZYXRQ"; 			// SMTP account username
				$mail->Password   = $ses_smtp_password; // "AgF+CeKN3+YK32WuIkC8hUKsezoXuayMlWdtK6Isa4w0";        // SMTP account password
				// $mail->SetFrom('support@coupsmart.com', 'CoupSmart Support');
				$mail->SetFrom($this->from_email, $this->from_name);
				// $mail->AddReplyTo("support@coupsmart.com","CoupSmart Support");
				$mail->AddReplyTo($this->from_email, $this->from_name);
				$mail->Subject    = $this->subject;
				// $mail->AltBody($this->altbody); // optional, comment out and test
				if ($format == 'html') {
					//error_log("the format was html");
					// Generate unique email signature if not aready passed as a param
					if(empty($user_email_code))
						$user_email_code = Common::generate_unique_sig("user_emails", "user_email_code");
					
					// Replace anchor links with coupsmart links and add the transparent image
					if ($this->email_template != 'lindtchocolate_1milsurveyb.html') {
						$this->message = $this->replaceAnchorLinks($this->message, $user_email_code);
					}
					$mail->MsgHTML($this->message);
				} else {
					$mail->Body = $this->message;
				}
				
				// Add attachment if any
				if(!empty($this->attachment_paths))
				{
					foreach($this->attachment_paths as $attachment_path)
						$mail->AddAttachment($attachment_path);
				}
				
				//error_log("almost there");
				if (is_array($this->to)) {
					$any_addresses_added = false;
					foreach ($this->to as $to) {
						// error_log('$this->to: ' . var_export($this->to, true));
						// error_log('$to: '. var_export($to, true));
						//check to see if the user is unsubscribed from the company you are sending from
						if(!empty($company_id)){
							$unsubscribed = Resubscribe::isUnsubscribed($company_id, $to);
							if((!$unsubscribed || $exempt == '1') && is_rfc3696_valid_email_address($to)){//they are not unsubscribed or the message is exempt from unsubscribe, and it looks like a valid email, so send them an email
								try {
									$mail->AddAddress($to);
									$any_addresses_added = true;
								} catch (Exception $e) {
									error_log("couldn't add address for phpmailer: " . $e->getMessage());
								}
							} else {
								//error_log("they have unsubscribed");
								//we don't want to send them anything, so go to the next person
							}
						}else{
							if (is_rfc3696_valid_email_address($to)) {
								try {
									$mail->AddAddress($to);
									$any_addresses_added = true;
								} catch (Exception $e) {
									error_log("couldn't add address for phpmailer: " . $e->getMessage());
								}
							}
						}
					}
					if(!$any_addresses_added)
						return false;
				} else {
					//check to see if the user is unsubscribed from the company you are sending from
					//error_log("this to" . var_export($company_id,true));
					if(!empty($company_id)){
						//error_log("this: " . var_export($this,true));
						$unsubscribed = Resubscribe::isUnsubscribed($company_id, $this->to);
						//error_log("subscribed status " . var_export($subscribed_status,true));
						if((!$unsubscribed || $exempt == '1') && is_rfc3696_valid_email_address($this->to)) {//they are not unsubscribed, so send them an email
							try {
								$mail->AddAddress($this->to);
							} catch (Exception $e) {
								error_log("couldn't add address for phpmailer: " . $e->getMessage());
							}
						} else {
						//	error_log("they have unsubscribed");
							//we don't want to send them anything, so exit (since there is only one person)
							return false;
						}
					}else{
						if (is_rfc3696_valid_email_address($this->to)) {
							try {
								$mail->AddAddress($this->to);
							} catch (Exception $e) {
									error_log("couldn't add address for phpmailer: " . $e->getMessage());
							}
						}
					}
				}
				
				if (!empty($this->bcc)) {
					if (is_array($this->bcc)) {
						foreach($this->bcc as $bcc) {
							try {
								$mail->AddBCC($bcc);
							} catch (Exception $e) {
								error_log("couldn't add bcc address for phpmailer: " . $e->getMessage());
							}
						}
					} else {
						try {
							$mail->AddBCC($this->bcc);
						} catch (Exception $e) {
							error_log("couldn't add bcc address for phpmailer: " . $e->getMessage());
						}
					}
				}
				
				if (!empty($this->cc)) {
					if (is_array($this->cc)) {
						foreach($this->cc as $cc) {
							try {
								$mail->AddCC($cc);
							} catch (Exception $e) {
								error_log("couldn't add cc address for phpmailer: " . $e->getMessage());
							}
						}
					} else {
						try {
							$mail->AddCC($this->cc);
						} catch (Exception $e) {
							error_log("couldn't add cc address for phpmailer: " . $e->getMessage());
						}
					}
				}
				
				//error_log("hit here!");
				
				$return = $mail->Send();
				$ses_error = 'NULL';
				
				if ($return == false) {
					error_log('Mail send() failed: ' . $mail->ErrorInfo . ", mailer object: " . var_export($this, true));
					$ses_error = "'" . Database::mysqli_real_escape_string($mail->ErrorInfo) . "'";
				}
				$email_status = $return ? "success":"failure";
				$foreign_key_table = empty($this->foreign_key_table) ? 'NULL' : "'".$this->foreign_key_table."'";
				$foreign_key = empty($this->foreign_key) ? 'NULL' : "'".$this->foreign_key."'";
	
				// Log the sent email here
				$to_addresses = array();
				if(is_array($this->to))
					$to_addresses = $this->to;
				else
					$to_addresses[] = $this->to;
				// error_log("to_addresses in mailer::send(): ".var_export($to_addresses, true));
				foreach($to_addresses as $i => $to_address)
				{
					$sql_user_email = "insert into user_emails (user_id, email, email_template, sent, status, foreign_key_table, foreign_key, user_email_code, ses_error) values ('$this->user_id', '".Database::mysqli_real_escape_string($to_address)."', '$this->email_template', NOW(), '$email_status', $foreign_key_table, $foreign_key, '$user_email_code', $ses_error)";
					Database::mysqli_query($sql_user_email);
					//error_log('SQL: ' . $sql_user_email);
					// error_log('THIS: ' . var_export($this, true));
					// error_log('Sent email to ' . $to_address . ', subject: "' . $mail->Subject . '" on ' . date('Y-m-d H:i:s'));
				}
				return $return;
			} else {
				error_log("Cannot send email! Either the email, or the subject or the message is empty.");
				//error_log("mailer object: ".var_export($this, true));
				return false;
			}
		} else {
			error_log('Not in production mode and recipient not a tester, would have sent email: ' . var_export($this, true));
		}
	}

	public function emailWithTemplate2($user, $template, $subject, $exempt = 0, $rate = 0, $company_id=null) {
		//error_log("template: ".$template. ", subject: ".$subject);
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = $subject;
				$html_template = dirname(__DIR__). "/emails/" . $template;
				$altbody = ''; // no easy way to do this
				$htmlBody = file_get_contents($html_template);
				$cssBody = '';
				
				$linkStr = "/<link .+?>/";
				$commentStr = "/<!--.*?-->/";
				
				preg_match_all($commentStr, $htmlBody, $comments);
				foreach($comments as $currentComment)
				{
					str_replace($currentComment, '', $htmlBody);
				}
				
				preg_match_all($linkStr, $htmlBody, $cssLinks);
				foreach($cssLinks as $currentCSSLink)
				{
					foreach($currentCSSLink as $currentLink)
					{
						if(preg_match('/text\/css/', $currentLink) && preg_match('/rel="stylesheet"/', $currentLink)){
							preg_match("/\/css\/.+\.css/", $currentLink, $match);
							$cssLocation=$match[0];
							$cssBody.=file_get_contents(dirname(__DIR__).$cssLocation);
						}
					}
				}
				//error_log('css body: '.$cssBody);
				//error_log('htmlFile: '.$emailBody);
				$emo = new emogrifier($htmlBody, $cssBody);
				$emailBody = $emo->emogrify();
				//error_log('emogrified body: '.$htmlBody);
				// $emailBody = $htmlBody;
				
				$vars = get_object_vars($user);
				foreach ($vars as $var => $val) {
					if (!empty($val)) {
						$repl_str = '&lt;!-- tmpl_' . $var . ' --&gt;';
						$emailBody = str_replace($repl_str, $val, $emailBody);
						
						$repl_str = '<!-- tmpl_' . $var . ' -->';
						if ($var == 'firstname') {
							$val = ' ' . $val;
						}
						$emailBody = str_replace($repl_str, $val, $emailBody);
					}
				}
				
				// Add the unsubscribe link at the end of the email if it is not exempted
				if(!$exempt)
				{
					if (empty($company_id)) //the company id wasn't set
						{
						$baseURL = Common::getBaseURL(true);
						$emailBody = str_replace('</body>', "<table width='600' cellpadding='0' cellspacing='0' border='0' align='center' style='background-color:#FFFFFF;margin-top:10px;padding-top:10px;padding-bottom:10px;border-top:1px #333 solid;''><tbody><tr><td align='center'><span class='unsubscribe_link'>If you don't want to receive any more emails from us, please <a href=" . $baseURL . "/unsubscribe?email=" . urlencode($user->email). ">click here to unsubscribe.</a></span></td></tr></tbody></table></body>", $emailBody);
						}
					else{ //the company id was set
						$baseURL = Common::getBaseURL();
						$emailBody = str_replace('</body>', "<table width='600' cellpadding='0' cellspacing='0' border='0' align='center' style='background-color:#FFFFFF;margin-top:10px;padding-top:10px;padding-bottom:10px;border-top:1px #333 solid;'><tbody><tr><td align='center'><span class='unsubscribe_link'>If you don't want to receive any more emails from us, please <a href=" . $baseURL . "/unsubscribe?email=" . urlencode($user->email). ";company_id=" . $company_id. ">click here to unsubscribe.</a></span></td></tr></tbody></table></body>", $emailBody);
					}
				}
			}
				// Populate the campaign engagement rates for the particular case
				if(!empty($rate))
				{
					$percent = $rate * 100;
					$emailBody = str_replace('[<i>x</i>%]', "<i>$percent%</i>", $emailBody);
				}

				$this->message = ($emailBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				$this->email_template = $template;

				$result = $this->send2('html', NULL, $company_id);

				if(!$result[0]) {
					$errors = $result[2];
					// $errors["There was an error sending a emailWithTemplate e-mail to $email."] = 1;
					error_log('There was an error sending a emailWithTemplate e-mail to ' . $email. '.');
				} else {
					error_log('Sent emailWithTemplate message to ' . $email . '.');
				}
				
				
			}
		 else {
			$errors['Email is empty'] = 1;
			$result = array(false, null, array('errors' => 'Email is empty'));
			error_log('Email is empty');
		}

		/*
		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
		*/
		return $result;
	}
	
	public function replaceAnchorLinks2($emailBody, $user_email_code)
	{
		// Replace anchor links with coupsmart links
		$dom = new DOMDocument();
		$dom->loadHTML($emailBody);
		// error_log("emailBody before replacing: ".$emailBody);
		$tag_names = array('a', 'area');
		$email_links = array();
		foreach($tag_names as $tag_name)
		{
			$lnk_elements = $dom->documentElement->getElementsByTagName($tag_name);
			foreach($lnk_elements as $i => $elem)
			{
				$url_href = $elem->getAttribute('href');
				
				// Do not replace unsubscribe links for the time being
				if(!strpos($url_href, "/unsubscribe?email="))
				{
					// $email_link_code = Common::generate_unique_sig('user_email_links', 'email_link_code');
					$email_link_code = UUID::v4();
				
					// Add it to an array
					$email_links[$email_link_code] = $url_href;
		
					// Replace href with coupsmart url
					$elem->setAttribute('href', Common::getBaseURL(). "/email-redirect?c=$email_link_code");
				}
			}
		}
		
		// Insert email_link entries
		if(!empty($email_links))
		{
			$email_links_data = array();
			foreach($email_links as $email_link_code => $url_href)
			{
				$email_links_data[] = "('$user_email_code', '$url_href', '$email_link_code')";
			}
			$sql = "insert into user_email_links (`user_email_code`, `url_href`, `email_link_code`) values " . implode(',', $email_links_data);
			if(!Database::mysqli_query($sql))
				error_log("SQL Error inserting the email_links: ".Database::mysqli_error()."\nSQL: " . $sql);
		}
	
		// Save changes back to the email body
		$emailBody = $dom->saveHTML();

		// Add a transparent gif image just before the html content ends
		$img_URL = Common::getBaseURL() . '/helpers/ajax-email-img-loaded.php?c='.$user_email_code;
		$emailBody =  str_replace("</body></html>", "<img src='$img_URL' style='display:none;' id='img_transparent_gif'></body></html>", $emailBody);
		
		// error_log("emailBody after replacing: ".$emailBody);
		return $emailBody;
	}
	
	public function send2($format = 'html', $user_email_code = null, $company_id=null)
	{
		global $ses_smtp_username, $ses_smtp_password, $ses_smtp_host;
		$result = array();
		
		//error_log("in the mailer!");
		$production = true;
		if (!isset($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] == 'coupsmart.com' || $_SERVER['SERVER_NAME'] == 'www.coupsmart.com') $production = true;
		if ($production || Mailer::all_internal_addresses($this->to)) {
			//error_log("inside the send method.  Checking if the to addresses have been set..." . var_export($this,true));
			if(!empty($this->to) && !empty($this->subject) && !empty($this->message))
			{
				$mail = new PHPMailer();
				$mail->CharSet = 'UTF-8';
				$mail->IsSMTP(); // telling the class to use SMTP
				$mail->Host       = $ses_smtp_host; // "email-smtp.us-east-1.amazonaws.com"; // SMTP server
				$mail->SMTPDebug  = false;                     // enables SMTP debug information (for testing)
												   // 1 = errors and messages
												   // 2 = messages only
				$mail->SMTPAuth   = true;                  // enable SMTP authentication
				$mail->Port       = 465;                    // set the SMTP port for the SES server
				$mail->SMTPSecure = 'ssl';
				$mail->Username   = $ses_smtp_username; // "AKIAIBQXMIRDF65ZYXRQ"; 			// SMTP account username
				$mail->Password   = $ses_smtp_password; // "AgF+CeKN3+YK32WuIkC8hUKsezoXuayMlWdtK6Isa4w0";        // SMTP account password
				// $mail->SetFrom('support@coupsmart.com', 'CoupSmart Support');
				$mail->SetFrom($this->from_email, $this->from_name);
				// $mail->AddReplyTo("support@coupsmart.com","CoupSmart Support");
				$mail->AddReplyTo($this->from_email, $this->from_name);
				$mail->Subject    = $this->subject;
				// $mail->AltBody($this->altbody); // optional, comment out and test
				$email_links = array();
				if ($format == 'html') {

					// Generate unique email signature if not aready passed as a param
					if(empty($user_email_code))
						$user_email_code = UUID::v4();
					
					//	Temporarily disabling replacement of email links
					// Replace anchor links with coupsmart links and add the transparent image
					$email_msg = $this->replaceAnchorLinks2($this->message, $user_email_code);
					$this->message = $email_msg;
					$mail->MsgHTML($this->message);
				} else {
					$mail->Body = $this->message;
				}
				//error_log("almost there");
				if (is_array($this->to)) {
					$any_addresses_added = false;
					foreach ($this->to as $to) {
						$to = trim($to);
						//check to see if the user is unsubscribed from the company you are sending from
						if(!empty($company_id)){
							$unsubscribed = Resubscribe::isUnsubscribed($company_id, $to);
							if(!$unsubscribed && is_rfc3696_valid_email_address($to)){//they are not unsubscribed, so send them an email
								try {
									$mail->AddAddress($to);
									$any_addresses_added = true;
								} catch (Exception $e) {
								error_log("couldn't add address '" . $to . "' for phpmailer: " . $e->getMessage());
								}
								
							} else {
								//error_log("they have unsubscribed");
								//we don't want to send them anything, so go to the next person
							}
						}
					}
					if(!$any_addresses_added)
						return array(false, null, array('errors' => 'User not subscribed'));
				} else {
					//check to see if the user is unsubscribed from the company you are sending from
					//error_log("this to" . var_export($company_id,true));
					if(!empty($company_id)){
						//error_log("this: " . var_export($this,true));
						$unsubscribed = Resubscribe::isUnsubscribed($company_id, $this->to);
						//error_log("subscribed status " . var_export($subscribed_status,true));
						if(!$unsubscribed && is_rfc3696_valid_email_address($this->to)){//they are not unsubscribed, so send them an email
							try {
								$mail->AddAddress($this->to);
							} catch (Exception $e) {
								error_log("couldn't add address for phpmailer: " . $e->getMessage());
							}
						} else {
						//	error_log("they have unsubscribed");
							//we don't want to send them anything, so exit (since there is only one person)
							// return false;
							return array(false, null, array('errors' => 'User not subscribed'));
						}
					}
				}
				//error_log("hit here!");
				ob_start();
				// $return = $mail->Send();
				global $from;
				$ses 				= new SimpleEmailService();
				$ses->from 		= $from;
				$ses->to 		= is_array($this->to) ? $this->to : array($this->to);
				$ses->subject 	= $this->subject;
				$ses->body 		= $this->message;
				$ses->sendEmail($format);
				// error_log("ses object after sending email: " . var_export($ses, true));
				$return = $ses->status == 200;
				
				// SES Email Send Info
				$ses_status 		= $ses->status;
				$ses_request_id 	= $ses->request_id;
				if($ses->status == 200)
				{
					$ses_error = "NULL";
					$email_status = "'success'";
					$ses_message_id = "'" . $ses->message_id . "'";
					$result = array(true, null, $ses->message_id);
				}
				else
				{
					$return = $ses->error;
					
					
					$ses_error = "'" . Database::mysqli_real_escape_string(json_encode($ses->error)) . "'";
					$email_status = "'failure'";
					$ses_message_id = "NULL";
					$result = array(false, null, $ses->error);
					
					error_log('Mail send() failed: ' . $mail->ErrorInfo);
					
					

				}
				$company_id = !empty($company_id) ? "'" . $company_id . "'" : "NULL";
				// $ses_error = $return ? "NULL" : "'" . Database::mysqli_real_escape_string(json_encode($ses->error)) . "'";
				
				ob_get_clean();
				
				$foreign_key_table = empty($this->foreign_key_table) ? 'NULL' : "'".$this->foreign_key_table."'";
				$foreign_key = empty($this->foreign_key) ? 'NULL' : "'".$this->foreign_key."'";
	
				// Log the sent email here
				$to_addresses = array();
				if(is_array($this->to))
					$to_addresses = $this->to;
				else
					$to_addresses[] = $this->to;
				// error_log("to_addresses in mailer::send(): ".var_export($to_addresses, true));
				
				$user_email_ids = array();
				foreach($to_addresses as $i => $to_address)
				{
					$sql_user_email = "insert into user_emails (user_id, company_id, email, email_template, sent, status, foreign_key_table, foreign_key, user_email_code, ses_status, ses_request_id, ses_message_id, ses_error) values ('$this->user_id', $company_id, '".Database::mysqli_real_escape_string($to_address)."', '$this->email_template', NOW(), $email_status, $foreign_key_table, $foreign_key, '$user_email_code', '$ses_status', '$ses_request_id', $ses_message_id, $ses_error)";
					
					if(!Database::mysqli_query($sql_user_email))
						error_log("Insert SQL error in Mailer::send2(): ".Database::mysqli_error(). "\nSQL: " .$sql_user_email);
						
					$user_email_id = Database::mysqli_insert_id();
					$user_email_ids[] = $user_email_id;
					
					// Updating user_email_link entries (This is optional)
					$sql_update_user_email_links = "update user_email_links set user_email_id = '$user_email_id' where user_email_code = '$user_email_code'";
					Database::mysqli_query($sql_update_user_email_links);
					
					/**************************** Write email message to disk ******************************/
				
					// Open File
					$root_path = Common::getRootPath(true);
					// $msg_file_name = $root_path."/scripts/logged_emails/".$user_email_id.".txt";
					
					// $dirname = "/var/www/dev/scripts/logged_emails/";
					$dirname = dirname(__DIR__) . "/scripts/logged_emails";
					$msg_file_name =  $dirname ."/". $user_email_id.".txt";
					$file_handle = fopen($msg_file_name, 'w');
					if(!$file_handle)
						error_log("Error opening file: ".$msg_file_name);
				
					// Write 'To: Address'
					$chars_written = fwrite($file_handle, $to_address . "\n");
				
					// Write 'Subject'
					$chars_written = fwrite($file_handle, $this->subject . "\n");
				
					// Write 'Body'
					$chars_written = fwrite($file_handle, $this->message . "\n");
				
					// Close file
					fclose($file_handle);

				}
				$result[1] = $user_email_ids;
				
				// return $return;
				return $result;
			} else {
				error_log("Cannot send email! Either the email, or the subject or the message is empty.");
				//error_log("mailer object: ".var_export($this, true));
				// return false;
				return array(false, null, array("errors" => "Cannot send email! Either the email, or the subject or the message is empty."));
			}
		} else {
			error_log('Not in production mode and recipient not a tester, would have sent email: ' . var_export($this, true));
		}
	}

	private function email_validate($email)
	{
		return is_rfc3696_valid_email_address($email);
	}

	public function daily($user) {
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'CoupSmart Seek & Scan Winner';
				$email_template = '../emails/DailyWinner.html';
				$altbody = '';


				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				if(!$this->send()) {
					$errors["There was an error sending a Daily Winner e-mail to $email."] = 1;
					error_log('There was an error sending a Daily Winner e-mail to ' . $email. '.');
				} else {
					error_log('Sent Daily Winner email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}

		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
	}

	public function weekly_leaders_10($user) {
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'CoupSmart Top Scanner';
				$email_template = '../emails/PrizeWinnerWeekly10.html';
				$altbody = '';


				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				if(!$this->send()) {
					$errors["There was an error sending a Weekly Leader 2-5 e-mail to $email."] = 1;
					error_log('There was an error sending a Weekly Leader 2-5 e-mail to ' . $email. '.');
				} else {
					error_log('Sent Weekly Leader 2-5 email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}

		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
	}

	public function weekly_leaders_5($user) {
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'CoupSmart Top Scanner';
				$email_template = '../emails/PrizeWinnerWeekly5.html';
				$altbody = '';


				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				if(!$this->send()) {
					$errors["There was an error sending a Weekly Leader 6-10 e-mail to $email."] = 1;
					error_log('There was an error sending a Weekly Leader 6-10 e-mail to ' . $email. '.');
				} else {
					error_log('Sent Weekly Leader 6-10 email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}

		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
	}

	public function weekly_winner($user) {
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'CoupSmart Top Scanner Winner';
				$email_template = '../emails/PrizeWinnerWeekly25.html';
				$altbody = '';


				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				if(!$this->send()) {
					$errors["There was an error sending a Weekly Winner e-mail to $email."] = 1;
					error_log('There was an error sending a Weekly Winner e-mail to ' . $email. '.');
				} else {
					error_log('Sent Weekly Winner email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}

		if(!empty($errors)) {
			return $errors;
		} else {
			return true;
		}
	}

	public function weekly_winner_admin_notify($body) {
		$this->to = array('bshipley@coupsmart.com', 'tdavis@coupsmart.com');
		$this->subject = 'Weekly Winners Announced';
		$this->message = $body;
		if(!$this->send()) {
			$errors["There was an error sending a Weekly Winner e-mail to $email."] = 1;
			error_log('There was an error sending a Weekly Winner e-mail to ' . $email. '.');
		} else {
			error_log('Sent Weekly Winner email to ' . $email . '.');
		}
	}

	public function user_contrib_barcode_approved($user, $barcode) {
		error_log('sending email to ' . $user->email . ' about ' . $barcode);
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'Your CoupSmart product info has been accepted';
				$email_template = '../emails/UserContribProdAccepted.html';
				$altbody = '';


				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				if(!$this->send()) {
					$errors["There was an error sending a User Contributed Product Approved e-mail to $email."] = 1;
					error_log('There was an error sending a User Contributed Product Approved e-mail to ' . $email. '.');
				} else {
					error_log('Sent User Contributed Product Approved email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}
	}

	public function user_contrib_barcode_denied($user, $barcode) {
		error_log('sending email to ' . $user->email . ' about ' . $barcode);
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'Your CoupSmart product info has been rejected';
				$email_template = '../emails/UserContribProdRejected.html';
				$altbody = '';


				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				if(!$this->send()) {
					$errors["There was an error sending a User Contributed Product Denied e-mail to $email."] = 1;
					error_log('There was an error sending a User Contributed Product Denied e-mail to ' . $email. '.');
				} else {
					error_log('Sent User Contributed Product Approved email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}
	}

	public function user_contrib_barcode_passed($user, $barcode) {
		error_log('sending email to ' . $user->email . ' about ' . $barcode);
		if(!empty($user->email)) {
			if(!$this->email_validate($user->email)) {
				$errors['email'] = 'Email address is invalid';
			} else {
				$email = $user->email;
				$this->subject = 'Your CoupSmart product info has been passed';
				$email_template = '../emails/UserContribProdPassed.html';
				$altbody = '';


				$htmlBody = file_get_contents($email_template);
				if (!empty($user->firstname)) {
					$htmlBody = str_replace('<!-- tmpl_firstname -->', (' ' . $user->firstname), $htmlBody);
				}

				$this->message = ($htmlBody);
				$this->altbody = $altbody;

				$this->to = $email;
				$this->user_id = $user->id;
				if(!$this->send()) {
					$errors["There was an error sending a User Contributed Product Passed e-mail to $email."] = 1;
					error_log('There was an error sending a User Contributed Product Passed e-mail to ' . $email. '.');
				} else {
					error_log('Sent User Contributed Product Approved email to ' . $email . '.');
				}
			}
		} else {
			$errors['Email is empty'] = 1;
			error_log('Email is empty');
		}
	}

	public function leaderboard_users_with_unverified_addresses($user) {
		$template = "verifyAddressLeaderboard.html";
		$subject = "You're missing out, please verify your address";
		$this->emailWithTemplate($user, $template, $subject);
	}

	public function leaderboard_users_with_unverified_addresses_err($user) {
		$template = "verifyAddressLeaderboard2d.html";
		$subject = "Issue resolved - please verify your address now";
		$this->emailWithTemplate($user, $template, $subject);
	}

	public function input_process_recall($user, $recall_id) {
		$template = "RecallAlert.html";
		$subject = "Product Recall Notification from CoupSmart Defender";
		$this->foreign_key_table = "recall";
		$this->foreign_key = $recall_id;
		$recall = new Recall($recall_id);
		$user->recall_id = $recall_id;
		$user->recall_title = $recall->title;
		$user->recall_description = $recall->description;
		$user->recall_link = $recall->link;
		$user->recall_source = Recall::$sources[$recall->source];
		$this->emailWithTemplate($user, $template, $subject);
	}
	
	public function admin_reseller_signup_alert($reseller, $page_count, $industry, $email) {
		$message = file_get_contents(dirname(__DIR__)."/emails/MarketerSignup.html");
		$marketer_attributes = get_object_vars($reseller);
		require_once(__DIR__ . '/User.class.php');
		$user = new User($marketer_attributes['users_id']);
		$marketer_attributes['firstname'] = $user->firstname;
		$marketer_attributes['lastname'] = $user->lastname;
		$marketer_attributes['email'] = $user->email;
		$marketer_attributes['pages'] = $page_count;
		$marketer_attributes['industry'] = $industry;
		$marketer_attributes['company_email'] = $email;
		foreach ($marketer_attributes as $name => $value) {
			$message = str_replace('<!-- '.$name.' -->', (' ' . $value), $message);
		}
		
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		
		$errors = array();
		//$mailer->to = array('tdavis@coupsmart.com');
		$mailer->to = 'newaccounts@coupsmart.com';
		
		$subj = 'Marketer sign up alert.';
		$mailer->subject = $subj;
		$mailer->message = $message;
		// error_log('mailer: ' . var_export($mailer, true));
		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent marketer signup registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public static function admin_business_signup_alert($company, $user, $facebook_link, $industry, $email, $business_type = array('7'))
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/BusinessSignup.html");
		$company_attributes = get_object_vars($company);
		foreach($company_attributes as $name => $value) {
			$message = str_replace('<!-- '.$name.' -->', (' ' . $value), $message);
		}
		
		$message = str_replace('<!-- company_name -->', $company->display_name, $message);
		$message = str_replace('<!-- page_id -->', $facebook_link, $message);
		$message = str_replace('<!-- firstname -->', $user->firstname, $message);
		$message = str_replace('<!-- lastname -->', $user->lastname, $message);
		$message = str_replace('<!-- email -->', $user->email, $message);
		$message = str_replace('<!-- industry -->', $industry, $message);
		$message = str_replace('<!-- company_email -->', $email, $message);
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		//$mailer->to = array('bshipley@coupsmart.com');
		// $mailer->to = array();
		$mailer->to = 'newaccounts@coupsmart.com';
		$subj = 'Business sign up alert.';
		if (in_array('7', $business_type) && in_array('8', $business_type)) {
			$subj = 'Marketer & Business sign up alert.';
		} else if (in_array('8', $business_type)) {
			$subj = 'Marketer sign up alert.';
		}
		$mailer->subject = $subj;
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent business signup registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	//notify resellers when a new customer is signed up under them
	public function customer_signup_alert($customer)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/welcome_new_user.txt");
		
		$message = str_replace('<!-- tmpl_username -->', $customer->username, $message);
		$message = str_replace('<!-- tmpl_firstname -->', $customer->firstname, $message);
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $customer->email;
		$mailer->subject = 'Welcome to CoupSmart';
		$mailer->message = $message;

		if(!$mailer->send('txt')) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer signup registration to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($customer->id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "customer" => $customer));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	
	
	public static function campaign_launched_alert($email, $campaign_name, $customer_id, $send_to_user)
	{
		if($send_to_user){
			$message = file_get_contents(dirname(__DIR__)."/emails/customer_campaign_launched.html");

			$message = str_replace('<!-- campaign_name -->', $campaign_name, $message);


			$mailer = new Mailer();
			$mailer->CharSet = 'UTF-8';

			$errors = array();
			$mailer->to = $email;
			$mailer->subject = 'CoupSmart Campaign Launched';
			$mailer->message = $message;

			if(!$mailer->send()) {
				$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
				error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
			} else {
				error_log('Sent Customer signup registration to ' . $mailer->to . '.');
			}
		}
		$reseller = Customer::get_customer_reseller($customer_id);
		if(is_object($reseller))
		{
			//self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "campaign_name"=>$campaign_name, "customer_id"=>$customer_id));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public function customer_campaign_cannotstart($email, $campaign_name, $customer_id)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/customer_campaign_cannotstart.html");

		$message = str_replace('<!-- campaign_name -->', $campaign_name, $message);


		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = 'CoupSmart Campaign Can\'t Start';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer campaign can\'t start to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($customer_id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "campaign_name"=>$campaign_name, "customer_id"=>$customer_id));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public static function campaign_finished_alert($email, $campaign_name, $customer_id)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/customer_campaign_finished.html");

		$message = str_replace('<!-- campaign_name -->', $campaign_name, $message);


		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = 'CoupSmart Campaign Finished';
		$mailer->message = $message;
		$user = User::findByEmail($email);
		$mailer->user_id = $user->id;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer signup registration to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($customer_id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "campaign_name"=>$campaign_name, "customer_id"=>$customer_id));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	public static function customer_phoenix_alert($email, $days, $customer_id)
	{

		$message = file_get_contents(dirname(__DIR__)."/emails/customer_campaign_phoenix.html");
		$message = str_replace('<!-- days -->', $days, $message);
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = 'Launch your CoupSmart Campaign';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Phoenix email to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($customer_id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "days"=>$days, "customer_id"=>$customer_id));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public static function customer_trial_ending($email, $days, $customer_id)
	{

		$message = file_get_contents(dirname(__DIR__)."/emails/customer_campaign_phoenix.html");
		$message = str_replace('<!-- days -->', $days, $message);
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = 'Launch your CoupSmart Campaign';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent trial ending email to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($customer_id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "days"=>$days, "customer_id"=>$customer_id));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	public static function customer_payment_alert($email, $customer_id)
	{

		$message = file_get_contents(dirname(__DIR__)."/emails/customer_payment_needed.html");

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = 'CoupSmart Customer Payment Needed';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer signup registration to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($customer_id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "customer_id"=>$customer_id));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	public static function customer_fullness_alert($email, $alert)
	{

		$message = file_get_contents(dirname(__DIR__)."/emails/customer_account_" . $alert . ".html");

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = 'CoupSmart Customer Prints Alert';
		$mailer->message = $message;
		$user = User::findByEmail($email);
		$mailer->user_id = $user->id;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer signup registration to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($user->id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "alert"=>$alert));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public function campaign_fullness_alert($email, $alert)
	{

		$message = file_get_contents(dirname(__DIR__)."/emails/customer_campaign_" . $alert . ".html");

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = 'CoupSmart Campaign Prints Alert';
		$mailer->message = $message;
		$user = User::findByEmail($email);
		$mailer->user_id = $user->id;
		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer signup registration to ' . $mailer->to . '.');
		}
		
		
		$reseller = Customer::get_customer_reseller($user->id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "alert"=>$alert));
		}
		
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	
	public function customer_notice($email, $first_name, $alert, $subject)
	{

		$message = file_get_contents(dirname(__DIR__)."/emails/customer_notice_" . $alert . ".html");
		$message = str_replace('&lt;!-- tmpl_firstname --&gt;', $first_name, $message);
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = $subject;
		$mailer->message = $message;
		$user = User::findByEmail($email);
		$mailer->user_id = $user->id;
		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent customer_notice_' . $alert . ' to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($user->id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "first_name"=>$first_name, "alert"=>$alert, "subject"=>$subject));
		}
		
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	public function customer_finished_notice($email, $first_name, $alert, $subject, $rate = 0)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/customer_notice_" . $alert . ".html");
		$message = str_replace('&lt;!-- tmpl_firstname --&gt;', $first_name, $message);
		$percent = $rate * 100;
		$message = str_replace('[<i>x</i>%]', "<i>$percent%</i>", $message);
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = $subject;
		$mailer->message = $message;
		$user = User::findByEmail($email);
		
		$mailer->user_id = $user->id;
		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent customer_notice_' . $alert . ' to ' . $mailer->to . '.');
		}
		
		
		$reseller = Customer::get_customer_reseller($user->id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "first_name"=>$first_name, "alert"=>$alert, "subject"=>$subject, "rate"=>$rate));
		}
		
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	public function customer_upgrade($email, $first_name, $alert, $subject)
	{

		$message = file_get_contents(dirname(__DIR__)."/emails/customer_upgrade_" . $alert . ".html");
		$message = str_replace('&lt;!-- tmpl_firstname --&gt;', $first_name, $message);
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $email;
		$mailer->subject = $subject;
		$mailer->message = $message;
		$user = User::findByEmail($email);
		$mailer->user_id = $user->id;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent account downgrade notice to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($user->id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "first_name"=>first_name, "alert"=>$alert, "subject"=>$subject));
		}
		
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	public function customer_approved($customer)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/customer_reg_approved.html");

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $customer->email;
		$mailer->subject = 'Your CoupSmart account is approved';
		$mailer->message = $message;

		error_log('mailer = ' . var_export($mailer, true));
		error_log('customer = ' . var_export($customer, true));

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer approval notification to ' . $mailer->to . '.');
		}
		
		$reseller = Customer::get_customer_reseller($customer->id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "customer"=>$customer));
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public static function payment_success($email, $subject)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/failed_payment_alert.html");

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		//$mailer->to = array('bshipley@coupsmart.com');
		$mailer->to = $email;
		$mailer->subject = $subject;
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent payment success registration to ' . $mailer->to . '.');
		}
		
		$user = User::findByEmail($email);
		$reseller = Customer::get_customer_reseller($user->id);
		if(is_object($reseller))
		{
			self::reseller_alert(array("type"=>__FUNCTION__, "reseller"=>$reseller, "email"=>$email, "subject"=>$subject));
		}
		
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}

	}


	public static function payment_failed_alert($customer, $subject){
		$message = file_get_contents(dirname(__DIR__)."/emails/failed_payment_alert.html");


		$message = str_replace('<!-- customer_id -->', ($customer->id), $message);
		$message = str_replace('<!-- firstname -->', ($customer->firstname), $message);
		$message = str_replace('<!-- lastname -->', ($customer->lastname), $message);
		$message = str_replace('<!-- email -->', ($customer->email), $message);
		$message = str_replace('<!-- phone -->', ($customer->phone), $message);

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = array('bshipley@coupsmart.com');

		$mailer->subject = $subject;
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent business signup registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}

	}
	public function customer_rejected($customer)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/customer_reg_rejected.html");

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $customer->email;
		$mailer->subject = 'Important information from CoupSmart';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer rejection notifcation to ' . $mailer->to . '.');
		}

		$reseller = Customer::get_customer_reseller($customer->id);
		if(is_object($reseller))
		{
			self::reseller_alert($reseller);
		}
		
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public function user_signup_alert($user)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/user_random_code.html");
		/*
		$company_attributes = get_object_vars($company);
		foreach($company_attributes as $name => $value)
			$message = str_replace('<!-- '.$name.' -->', (' ' . $value), $message);
		*/
		$mailer = new Mailer();
		$message = str_replace('<!-- username -->', (' ' . $user->username), $message);
		$message = str_replace('<!-- password -->', (' ' . $user->password), $message);
		$mailer->CharSet = 'UTF-8';
		error_log('USER INFORMATION: ' . var_export($user, true));
		$errors = array();
		$mailer->to = $user->email;
		$mailer->subject = 'Thank you for signing up!!';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Customer signup registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public function affiliate_signup_alert($affiliate)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/affiliate_sign_up.html");

		//$mailer = new Mailer();
		$this->CharSet = 'UTF-8';

		$errors = array();
		$this->to = $affiliate->email;
		$this->subject = 'Welcome to CoupSmart';
		$this->message = $message;

		if(!$this->send()) {
			$errors["There was an error sending the e-mail to " . $this->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $this->to. '.');
		} else {
			error_log('Sent Customer signup registration to ' . $this->to . '.');
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public function admin_affiliate_signup_alert($affiliate)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/AffiliateSignup.html");
		foreach($affiliate as $name => $value) {
			$message = str_replace('<!-- '.$name.' -->', (' ' . $value), $message);
		}

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		//$mailer->to = array('sqazi@coupsmart.com');
		//$mailer->to = 'sqazi@coupsmart.com';
		$mailer->to = array('sales@coupsmart.com');
		$mailer->subject = 'Affiliate sign up alert.';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Affiliate signup registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	
	public static function admin_new_reseller_business($reseller_id, $company_info)
	{
		if(!empty($reseller_id))
		{
			$reseller = new Reseller($reseller_id);
			$company_info['reseller_name'] = $reseller->name;
		}
		$message = file_get_contents(dirname(__DIR__)."/emails/NewResellerBusiness.html");
		foreach($company_info as $name => $value) {
			$message = str_replace('<!-- '.$name.' -->', (' ' . $value), $message);
		}

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		//$mailer->to = array('sqazi@coupsmart.com');
		//$mailer->to = 'sqazi@coupsmart.com';
		$mailer->to = array('newaccounts@coupsmart.com', 'khoeffer@coupsmart.com', 'sqazi@coupsmart.com');
		$mailer->subject = 'New Reseller Business alert.';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent New Reseller Businesss registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			// return $errors;
		}
	}
	
	public function contact_form_message($post) {
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $post['team'] . '@coupsmart.com';
		$mailer->subject = "New contact form submission from CoupSmart.com";
		$mailer->message = "<html><body>Name: " . $post['name'] . "<br />\nEmail: " . $post['email'] . "<br />\nMessage:<br />\n" . $post['message'];
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $post['team'] . '@coupsmart.com' . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $post['team'] . '@coupsmart.com' . '.');
		} else {
			error_log('Sent contact form submission to ' . $post['team'] . '@coupsmart.com' . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}

	public function notify_birthday_coupon_users($user, $message)
	{
		
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $user->email;
		$mailer->subject = 'Birthday Coupons Notification';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Birthday Coupons Notification to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	
	public function device_disconnected_alert($company_info)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/DeviceDisconnected.html");
		foreach($company_info as $name => $value) {
			$message = str_replace('<!-- '.$name.' -->', (' ' . $value), $message);
		}

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = array('support@coupsmart.com');
		$mailer->subject = 'CoupCheck Device Disconnected Alert.';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Affiliate signup registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	
	public static function reseller_alert($allInfo)
	{
		//message located at emails/reseller/__FUNCTION__
		$message = file_get_contents(dirname(__DIR__)."/emails/reseller/".$allInfo["type"].".html");
		
		$message = str_replace("<!-- RESELLER -->",$allInfo["reseller"]->name,$message);
		$message=str_replace("<!-- CAMPAIGN_NAME -->", $allInfo["campaign_name"], $message);
		$message=str_replace("<!-- CUSTOMER_ID -->", $allInfo["customer_id"], $message);
		$message=str_replace("<!-- CUSTOMER -->", $allInfo["customer"]->username, $message);
		$message=str_replace("<!-- DAYS -->", $allInfo["days"], $message);
		$message=str_replace("<!-- ALERT -->", $allInfo["alert"], $message);
		$message=str_replace("<!-- SUBJECT -->", $allInfo["subject"], $message);
		$message=str_replace("<!-- RATE -->", $allInfo["rate"], $message);
		
		$reseller = $allInfo["reseller"];
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $reseller->email;
		$mailer->subject = 'Reseller Alert';
		$mailer->message = $message;
		
		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent reseller signup registration to ' . $mailer->to . '.');
		}

		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	
	public static function send_signed_url_for_coupon($user, $signed_url, $expiry_date)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/signed_url_for_coupon.html");
		$message = str_replace('<!-- username -->', ($user->username), $message);
		$message = str_replace('<!-- signed_url -->', ($signed_url), $message);
		$expiry_date = date('m/d/Y', $expiry_date);
		$message = str_replace('<!-- expiry_date -->', ($expiry_date), $message);
		

		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		$mailer->to = $user->email;
		$mailer->subject = 'URL for Coupon.';
		$mailer->message = $message;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Coupon URL to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	
	public static function email_coupon($user, $item_id, $location_id = null, $product_id = null)
	{
		
		if(!empty($location_id)){
			$screen_url = Common::getCouptBaseURL() . "/instore?l=" . $location_id;
		}
		
		$item = new Item($item_id);
		$company = new Company($item->manufacturer_id);
		error_log('item id: '. var_export($item, true));
		$url = Common::getBaseURL() . "/print?item_id=" . urlencode($item_id) . ";user_id=" . urlencode($user->id) ;
		//error_log("url: ".$url);
		$message = file_get_contents(dirname(__DIR__). "/emails/mobileoffers_0513.html");
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $user->email;
		$mailer->subject = "Here's your walk-in coupon";

		$offer_value = !empty($item->platform_social_offer_value) ? $item->platform_social_offer_value : $item->name;
		if(!empty($item->expires)){
			$exp_message = 'You have until <span class="expirationdate" style="font-weight:bold;">' 
					. Common::convertMySQLDateToSpecifiedFormat('m/d/Y', $item->expires) 
					. '</span> to use this offer.';
			$message = str_replace("<!-- exp_message -->", $exp_message, $message);
		}
		$message = str_replace("<!-- print_url -->", $url, $message);
		$message = str_replace("<!-- screen_url -->", $screen_url, $message);
		$message = str_replace("<!-- deal_name -->", $offer_value, $message);
		$message = str_replace("<!-- company_name -->", $company->display_name, $message);
		$message = str_replace("<!-- unsubscribe_link -->", Common::getBaseURL() . "/unsubscribe", $message);
		$mailer->message = $message;
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $user->email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $user->email . '.');
		} else {
			error_log('Sent email to ' . $user->email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function email_buyer_receipt($user, $sgs_item_id)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/social_gift_receipt.html");
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $user->email;
		// $mailer->bcc = 'archive@coupsmart.com';
		$mailer->subject = "Your Social Gift Store receipt";
		$mailer->message = "<html><body>Dear ".$user->firstname . " " .$user->lastname. ", <p>Here is your social gift store receipt:<hr>
		stuff here
		</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $user->email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $user->email . '.');
		} else {
			error_log('Sent email to ' . $user->email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function sgs_email_company_owner($company_owner_name, $email, $social_gift_item_name)
	{
		$message = file_get_contents(dirname(__DIR__)."/emails/social_gift_company_receipt.html");
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $email;
		$mailer->subject = "Your Social Gift Store receipt";
		$mailer->message = "<html><body>Dear ".$company_owner_name. ", <p>The social gift item $social_gift_item has just been sent.<hr>
		stuff here
		</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function email_sgs_item($username, $email, $sgs_uiid) {
		$url = Common::getBaseURL() . "/signup?sgs_uiid=" . $sgs_uiid;
		//error_log("url: ".$url);
		
		$message = file_get_contents(dirname(__DIR__)."/emails/social_gift.html");
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $email;
		$mailer->subject = "Someone gave you a gift.";
		$mailer->message = "<html><body>Dear ".$username . ", <br>Please click <a href = '" . $url . "'>here</a> to print your Social Gift Item<hr>
		</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function email_sgs_redeemed($username, $email, $sgs_uiid) {
		$url = Common::getBaseURL() . "/signup?sgs_uiid=" . $sgs_uiid;
		//error_log("url: ".$url);
		
		$message = file_get_contents(dirname(__DIR__)."/emails/social_gift_redeemed.html");
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $email;
		$mailer->subject = "Your gift was redeemed.";
		$mailer->message = "<html><body>Dear ".$username . ", <br>Your friend has picked up their gift from the store. Scrooge <i>who</i>?<hr>
		</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function email_sgs_confirmation($username, $email, $sgs_uiid) {
		$url = Common::getBaseURL() . "/signup?sgs_uiid=" . $sgs_uiid;
		//error_log("url: ".$url);
		
		$recipient = "Your friend";
		$query = "select recipient_first_name, recipient_last_name, recipient_email from sgs_order_recipients where sgs_uiid = '" . Database::mysqli_real_escape_string($sgs_uiid) . "'";
		$rs = Database::mysqli_query($query);
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			$row = Database::mysqli_fetch_assoc($rs);
			if (!empty($row['recipient_first_name']) || !empty($row['recipient_last_name']) || !empty($row['recipient_email'])) {
				if (!empty($row['recipient_first_name']) || !empty($row['recipient_last_name'])) {
					$recipient = $row['recipient_first_name'];
					if (!empty($row['recipient_last_name'])) {
						if (!empty($recipient)) {
							$recipient .= ' ' . $row['recipient_last_name'];
						} else {
							$recipient = $row['recipient_last_name'];
						}
						if (!empty($row['recient_email'])) {
							$recipient .= ' (';
						}
					}
				} else {
					$recipient = $row['recipient_email'];
				}
				if ((!empty($row['recipient_first_name']) || !empty($row['recipient_last_name'])) && !empty($row['recipient_email'])) {
					$recipient .= ')';
				}
			}
		}
		
		$message = file_get_contents(dirname(__DIR__)."/emails/social_gift_confirmation.html");
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $email;
		$mailer->subject = "Your gift has been sent.";
		$mailer->message = "<html><body>Dear " . $username . ", <br>Well aren't you nice, sending a gift to your friend like that?<br />Kudos for you!<br />. It looks like we're all set. " . htmlspecialchars($recipient) . " should receive an email with instructions on how to redeem their gift.<br />Thanks so much!<br />The CoupSmart Team<hr>
		</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function email_sgs_item_printed($username, $email, $date_claimed, $company_name) 
	{
		global $socgift_app_url;
		// $url = Common::getBaseURL() . "/signup?sgs_uiid=" . $sgs_uiid;
		// error_log("url: ".$url);
		$user = User::findByUsername($username);
		
		// $message = file_get_contents(dirname(__DIR__)."/emails/social_gift_redeemed.html");
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $email;
		$mailer->subject = "Your voucher has been printed.";
		$mailer->message = "<html><body>Dear " . $user->firstname . ", <br><br>You, or your friend whom you purchased a gift for, have/has printed out the voucher from the " . $company_name . " Social Gift Shop on " . date('m/d/Y', strtotime($date_claimed)). " <br /><br />If you would like to check the status of your order, please visit the <a href='" . $socgift_app_url . "'>Gift Store application</a>.<hr>
		</body></html>";
		// error_log("mailer->message in Mailer::email_sgs_item_printed(): " . $mailer->message);
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function send_demo_request($request_demo_name, $request_demo_lname, $request_demo_email, $request_demo_compname, $request_demo_message)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = 'newaccounts@coupsmart.com';
		$email = $mailer->to;
		$mailer->subject = "Demo Request from ". $request_demo_name." ". $request_demo_lname;
		$mailer->message = "<html><body>Hello, <br>Somebody has requested a demo tour to see how CoupSmart apps can benefit them. See info below. <br/> <br/> Name: ". $request_demo_name . " "  . $request_demo_lname ." <br/> Email: ".$request_demo_email." <br/> Company: ".$request_demo_compname." <br/> Message: ".$request_demo_message." </body></html>";
		
		// add company name and message to the text above
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public static function send_cs_message($request_demo_email, $request_demo_message)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = 'newaccounts@coupsmart.com';
		$email = $mailer->to;
		$mailer->subject = "Demo Request from ". $request_demo_name." ". $request_demo_lname;
		$mailer->message = "<html><body>Hello, <br>Somebody has sent us an email. See info below. <br/> <br/> Email: ".$request_demo_email." <br/> Message: ".$request_demo_message." </body></html>";
		
		// add company name and message to the text above
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public static function send_customer_payment_notification($name, $company_name, $charge_id, $amount, $currency, $invoice_numbers)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = array('bshipley@coupsmart.com', 'khoeffer@coupsmart.com');
		$mailer->cc = array('sqazi@coupsmart.com');
		$email = $mailer->to;
		$mailer->subject = "Payment Received!";
		$mailer->message = "<html><body>Hello, <br>A Payment has just been received. See info below. <br/> <br/> Name: ". $name . " <br/> Company Name: ". $company_name . " <br/> Charge Id: ". $charge_id . " <br/> Amount: ".$amount." <br/> Currency: ".$currency." <br/> Invoice Numbers: ".$invoice_numbers." </body></html>";
		
		// add company name and message to the text above
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . implode(',', $email) . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public static function send_whitepaper_downloaded_notification($name, $whitepaper_email, $phone, $company)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = array('bshipley@coupsmart.com', 'jmoorman@coupsmart.com', 'khoeffer@coupsmart.com');
		$mailer->cc = array('sqazi@coupsmart.com');
		$email = $mailer->to;
		$mailer->subject = "Whitepaper downloaded by ". $name;
		$mailer->message = "<html><body>Hello, <br>Somebody has downloaded one of our white papers (HowToDriveTraffic.pdf). See info below. <br/> <br/> Name: ". $name ." <br/> Email: ".$whitepaper_email." <br/> Phone: ".$phone." <br/> Company: ".$company;
		
		// add company name and message to the text above
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public static function email_contact_form($contactus_name, $contactus_email, $contactus_compname, $contactus_message)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = 'sales@coupsmart.com';
		$email = $mailer->to;
		$mailer->subject = "Contact message from ". $contactus_name." ";
		$mailer->message = "<html><body>Hello, <br>Somebody has submitted a message or inquiry through the contact form on Coupsmart.com. See info below. <br/> <br/> Name: ". $contactus_name." <br/> Email: ".$contactus_email." <br/> Message: ".$contactus_message." </body></html>";
		
		// add company name and message to the text above
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public static function send_job_application($apply_job_name, $apply_job_email, $apply_job_phone, $apply_job_li, $apply_job_message, $resume_url, $apply_job_position)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = 'jobs@coupsmart.com';
		$email = $mailer->to;
		$mailer->subject = "Job application from ". $apply_job_name." ";
		$mailer->message = "<html><body>Hey you guys,<br><br>Somebody just submitted their resume for an open position at CoupSmart, aren't they just a go-getter! Here's their info. <br/> <br/> <ul style='list-style:none;'><li><b>Position Requested:</b> " . $apply_job_position . " </li><li><b>Name:</b> ". $apply_job_name." </li><li><b>Email:</b> ".$apply_job_email." </li><li><b>Phone:</b> ".$apply_job_phone." </li><li><b>Message:</b> ".$apply_job_message." </li><li><b>Resume:</b> " . $resume_url . " </li></ul></body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	
	}
	
	
	public static function send_redeemed_codes_report($all_results)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = array('sqazi@coupsmart.com', 'khoeffer@coupsmart.com');
		$email = $mailer->to;
		$mailer->subject = "Redeemed Codes Report ";
		
		$html_results = "";
		
		foreach($all_results as $file_name => $results)
		{
			$html_results .= "<h3>" . $file_name . "</h3><ul style='list-style:none;'>";
			foreach($results as $barcode => $msg)
			{
				$style = "";
				if($msg['status'] == 'success')
					$style = "style='color: green;'";
				else if($msg['status'] == 'warning')
					$style = "style='color: orange;'";
				else
					$style = "style='color: red;'";
				
				$html_results .= "<li " . $style . "><b>$barcode</b>:<span>" . $msg['msg']. "</span></li>";
			}
			$html_results .= "</ul><hr />";
		}
		$mailer->message = "<html><body>Hi,<br><br>Here are the redemption results from the previously processed files:<br/> <br/>" . $html_results . "</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	
	}
	
	public static function sendEmailCodesAlert($arr_urls_having_issues)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = array('sqazi@coupsmart.com'); // , 'sameeullahqazi@yahoo.com', 'jfox@coupsmart.com', 'khoeffer@coupsmart.com');
		$email = implode(', ', $mailer->to);
		$mailer->subject = "Warning - Tani Email Codes not Retrieved!";
		
		$email_codes_content = "<table style='border: 1px solid #cccccc;'><tr><th>Item Id</th><th>Service URL</th><th>Response</th></tr>";
		foreach($arr_urls_having_issues as $item_id => $url)
		{
			$email_codes_content .= "<tr><td>" . $item_id. "</td><td>" . $url[0] . "</td><td>" . $url[1] . "</td></tr>";
		}
		$email_codes_content .= "</table>";
		$mailer->message = "<html><body>Attention!,<br><br>The following URLs did not successfully return Email codes: " . $email_codes_content . "<br /><br />You may want to login to AWS (Oregon) and ensure that the Elastic IP '50-112-124-58' is still associated with all running instances.<br /><br />Thank you!</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	
	}
	
	public static function sendLowV2CRateAlert($low_v2c_rates)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = array('bshipley@coupsmart.com', 'khoeffer@coupsmart.com', 'sqazi@coupsmart.com');
		$email = implode(', ', $mailer->to);
		$mailer->subject = "Campaigns having low V2C Rates!";
		
		$content = "<table style='border: 1px solid #cccccc;'><tr><th>Company</th><th>Campaign</th><th>Views</th><th>Claims</th><th>V2C</th></tr>";
		foreach($low_v2c_rates as $deal_id => $v2c_data)
		{
			$content .= "<tr><td>" . htmlentities($v2c_data['company']). "</td><td>" . htmlentities($v2c_data['deal']) . "</td><td>" . $v2c_data['num_views'] . "</td><td>" . $v2c_data['num_claims'] . "</td><td>" . number_format($v2c_data['v2c'], 2) . "</td></tr>";
		}
		$content .= "</table>";
		$mailer->message = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>FYI,<br><br>The following campaigns have a V2C rate below 10%: " . $content . "<br /><br /><br />Thank you!</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	
	}
	
	
	public static function sendEmailCodesCreatedNotification($params)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = $_SESSION['user']->email; // array('altugi@tani.com.tr');
		$mailer->cc = array('sqazi@coupsmart.com', 'khoeffer@coupsmart.com');
		$email = implode(', ', $mailer->to);
		$mailer->subject = "Your Campaign Code Snippet";
		
		$campaign_name = $params['campaign_name'];
		$web_service_url = $params['web_service_url'];
		$num_codes_fetched = $params['num_codes_fetched'];
		$email_code_snippet = $params['email_code_snippet'];
		
		$mailer->message = "Hi,\n\nHere's your new campaign info:\n 
CAMPAIGN NAME:\t$campaign_name
WEB SERVICE URL:\t$web_service_url
CODES FETCHED:\t$num_codes_fetched
HTML SNIPPET:\t$email_code_snippet
\nThank you!\nThe Coupsmart Team";
		// error_log("Message in Mailer::sendEmailCodesCreatedNotification(): " . $mailer->message);
		
		if (!$mailer->send('plain')) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	
	
	}
	
	public static function email_employee_invitation($email, $unique_key, $company)
	{
		$url = Common::getBaseURL() . "/signup?employee_sig=" . $unique_key;
		//error_log("url: ".$url);
		
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$errors = array();
		$mailer->to = $email;
		$mailer->subject = "Please create your user account for " . htmlspecialchars($company) . "";
		$mailer->message = "<html><body>Hello, <br>Please <a href = '" . $url . "'>click here</a> to signup as a " . htmlspecialchars($company) . " Employee.<br />" . htmlspecialchars($company) . " is using CoupSmart&#39;s products and you need to create an account in order to redeem coupons or other promotional material that a customers brings in. Once your account is created, go to cu.pn/e to start checking!<br /><br />Thanks!<br />The CoupSmart Team<hr><br />If you believe you received this email in error or need help with creating your account, please contact us at support@coupsmart.com
		</body></html>";
		
		if (!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $email . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $email . '.');
		} else {
			error_log('Sent email to ' . $email . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function silent_post_errors($error, $subject = "Authorize.net Silent Post had problems")
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		//$mailer->to = array('sqazi@coupsmart.com');
		//$mailer->to = 'sqazi@coupsmart.com';
		$mailer->to = array('sqazi@coupsmart.com');
		$mailer->subject = $subject;
		$mailer->message = $error;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Affiliate signup registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	
	public static function coupon_display_errors($error, $subject = "Coupons are not displayed for Busken")
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		$errors = array();
		//$mailer->to = array('sqazi@coupsmart.com');
		$mailer->to = 'sqazi@coupsmart.com';

		$mailer->subject = $subject;
		$mailer->message = $error;

		if(!$mailer->send()) {
			$errors["There was an error sending the e-mail to " . $mailer->to . "."] = 1;
			error_log('There was an error sending the e-mail to ' . $mailer->to. '.');
		} else {
			error_log('Sent Affiliate signup registration to ' . $mailer->to . '.');
		}
		if (empty($errors)) {
			return true;
		} else {
			return $errors;
		}
	}
	public static function send_test_email($user, $item_id)
	{
		$item = Item::getCouponInfo($item_id);
		if (isset($item) && !empty($item)) {
			foreach ($item as $i=>$field)
			{
				$bc_pt1 = $field['barcode_nsc'] . $field['barcode_co_prfx'] . $field['barcode_family_code'] . $field['offer_code'];
				$share_bc_pt1 = $field['barcode_nsc'] . $field['barcode_co_prfx'] . $field['barcode_social_family_code'] . $field['social_offer_code'];
				$bc_pt2 = "(8101)" . $field['barcode_mfg_nsc'] . " " . $field['barcode_offer_code'] . " " . sprintf('%02d', $field['expire_month']) . sprintf('%02d', $field['expire_year']);
				$share_bc_pt2 = "(8101)" . $field['barcode_mfg_nsc'] . " " . $field['barcode_social_offer_code'] . " " . sprintf('%02d', $field['expire_month']) . sprintf('%02d', $field['expire_year']);
				$barcode_social_offer_service_name = $field['barcode_social_offer_service_name'];
				$use_share_bonus = $field['use_share_bonus'];
				
				// Fields specific to share coupon
				$prod_name = !empty($use_share_bonus) ? $field['barcode_social_offer_service_name'] : $field['name'];
				$small_print = !empty($use_share_bonus) ? $field['social_small_type'] : $field['small_type'];
				$offer_code = !empty($use_share_bonus) ? $field['social_offer_code'] : $field['offer_code'];
				$value = !empty($use_share_bonus) ? $field['social_offer_value'] : $field['offer_value'];
	
				$url = Common::getBaseURL() . "/helpers/render-coupon.php?prod_name=" . $prod_name. "&customer_id=" . $user->id . "&small_print=" . $small_print . "&expiration_date=" . $expiration_date . "&upc=" . $upc . "&offer_code=" . $offer_code . "&value=" . $value . "&logo_file_name=" . $logo_file_name . "&bc_pt1=" . $bc_pt1 . "&bc_pt2=" . $bc_pt2 . "&default_coupon_image=" . $default_coupon_image . "&expire_month=" . 8 . "&expire_year=" . 2013;
				
			}
			
		}
		error_log("URL: ".$url);
		
	
		$from = "support@coupsmart.com";
		$headers = "From: Coupsmart Support <$from>";//put you own stuff here or use a variable
		$to = $user->email;// same as above
		$subject = 'Testing Inline attachment HTML Emails';//your own stuff goes here
		$html ="<img src='beerchug.gif'><br /><br />
<b>This</b> is HTML <span style='background:cyan'>and this is a cyan highlight</span>
<br />So this should be a new line.<br /><br />This should be a new line with a space between the above.
<br />Here's dead Al<br><img src='DeadAl.jpg'><br />He is dead in this photo!<br />This is a martyr, well
OK then I think I will pass on looking like that all blowed up and all.<br /><br />So much for being a martyr!<br /> He's just another dead terrorist in the pile of the others ... ougggh nooooo!";//make up your own html or use an include

		//the below is your own plain text message (all the $message(x))
		$message0 = 'Dear valued customer,';// or make up your own for plain text message
		$message1 = 'NukeXtra just released our new search engine optimisation (SEO) services.
		We have exciting new packages from Cost-Per-Click (CPC, Paid advertising) to specialised optimization of your website by a designated SEO campaign manager.';
		$message2 = 'Studies have proven that top placement in search engines, among other forms of online marketing, provide a more favourable return on investment compared to traditional forms of advertising such as, email marketing, radio commercials and television.';
		$message3 = 'Search engine optimization is the ONLY fool proof method to earning guaranteed Top 10 search engine placement.';
		$message4 = '95% of monthly Internet users utilize search engines to find and access websites';
		$message5 = 'Attached is the NukeXtra SEO & CPC packages guide for your information.';
		$message6 = 'If you have any questions or are interested in proceeding with our SEO services, please do not hesitate to contact us.';
		$message7 = 'I look forward to this opportunity for us to work together.';
		$message8 = 'With Kindest regards,';
		$message9 = 'Someone';
		$message10 = 'PHP Web Programmer';
		$message11 = 'NukeXtra - stevedemarcus@ahost.com - http://dhost.info/stevedemarcus/steve/' ;
		$message12 = '218 Some Court<br />Somewhere, ST 55555';
		$message12 = 'Tel: (xxx)-xxx-xxx | Fax: {xxx)-xxx-xxxx';
		
		// Generate a boundary string that is unique
		$semi_rand = md5(time());
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
		
		// Add the headers for a file attachment
		$headers .= "\nMIME-Version: 1.0\n" .
		"Content-Type: multipart/alternative;\n" .
		" boundary=\"{$mime_boundary}\"";
		$message = "--{$mime_boundary}\n" .
		"Content-Type: text/html; charset=\"iso-8859-1\"\n" .
		"Content-Transfer-Encoding: 7bit\n\n" .
		"<font face=Arial>" .
		$html."\r\n";
		$message .= "--{$mime_boundary}\n" .
		"Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
		"Content-Transfer-Encoding: 7bit\n\n" .
		$message0 . "\n\n" .
		$message1 . "\n\n" .
		$message2 . "\n\n" .
		$message3 . "\n\n" .
		$message4 . "\n\n" .
		$message5 . "\n\n" .
		$message6 . "\n\n" .
		$message7 . "\n\n" .
		$message8 . "\n\n" .
		$message9 . "\n" .
		$message10 . "\n" .
		$message11 . "\n" .
		$message12 . "\n\n";
		// Add the headers for a file attachment
		$headers .= "\nMIME-Version: 1.0\n" .
		"Content-Type: multipart/mixed;\n" .
		" boundary=\"{$mime_boundary}\"";
		
		//first file to attach
		/*
		$fileatt2 = '../images/beerchug.gif';//put the relative path to the file here on your server
		$fileatt_name2 = 'beerchug.gif';//just the name of the file here
		$fileatt_type2 = filetype($fileatt2);
		$file2 = fopen($fileatt2,'rb');
		$data2 = fread($file2,filesize($fileatt2));
		fclose($file2);
		*/
		$data = file_get_contents($url);
		$fileatt_name = "Testfile.png";
		
		// Base64 encode the file data
		$data = chunk_split(base64_encode($data));
		
		
		// Add file attachment to the message
		$message .= "--{$mime_boundary}\n" .
		"Content-Type: image/gif;\n" . // {$fileatt_type}
		" name=\"{$fileatt_name}\"\n" .
		"Content-Disposition: inline;\n" .
		" filename=\"{$fileatt_name}\"\n" .
		"Content-Transfer-Encoding: base64\n\n" .
		$data . "\n\n" .
		"--{$mime_boundary}--\n";
		
		// Send the message
		$send = mail($to, $subject, $message, $headers);
		if ($send) {
		echo "<p>Email Sent to $to from $from successfully!</p>";
		} else {
		echo "<p>Mail could not be sent. You missed something in the script. Sorry!</p>";
		}
	}
	
	public static function all_internal_addresses($emails) {
		$all_internal = false;
		// error_log('testers: ' . var_export(self::$tester_emails, true));
		// error_log('emails = ' . var_export($emails, true));
		if (is_array($emails)) {
			// echo("is array\n");
			$all_internal = true;
			foreach ($emails as $email) {
				// echo("checking $email\n");
				if (!preg_match('/@coupsmart.com$/', $email) && (!in_array($email, self::$tester_emails)) && !preg_match('/kinlaar/', $email)) {
					$all_internal = false;
					break;
				}
			}
		} else {
			// error_log('string');
			if (preg_match('/@coupsmart.com$/', $emails) || (in_array($emails, self::$tester_emails)) || preg_match('/kinlaar/', $emails)) {
				$all_internal = true;
			}
		}
		return $all_internal;
	}
	
	public static function getEmailSentInfoByCode($user_email_code)
	{
		$email_info = array();
		$sql = "select * from user_emails where user_email_code='".Database::mysqli_real_escape_string($user_email_code)."'";
		$email_info = BasicDataObject::getDataRow($sql);
		return $email_info;
	}
	
	public static function log_user_email_opened($user_email_id)
	{
		$sql = "insert into `user_emails_opened` (`user_email_id`, `created`) values ('$user_email_id', now())";
		if(!Database::mysqli_query($sql))
			error_log("SQL error in Mailer::log_user_email_opened(): ".Database::mysqli_error()."\SQL: ".$sql);
	}
	
	public static function getEmailLinkInfo($email_link_code)
	{
		$sql = "select * from user_email_links where email_link_code='$email_link_code'";
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function getEmailLinkClickInfo($email_click_code)
	{
		$sql = "select link.user_email_id, click.id
		from user_email_clicks click
		inner join user_email_links link on click.user_email_link_id = link.id
		where email_click_code = '$email_click_code'
		and page_loaded != '1'";
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function log_user_email_click($user_email_link_id, $email_click_code = null)
	{
		$user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$ip = Common::GetUserIp();
		$email_click_code = !empty($email_click_code) ? "'" . $email_click_code . "'" : "NULL";
		
		$sql = "insert into user_email_clicks (`user_email_link_id`, `ip`, `user_agent`, `created`, `email_click_code`) values ('$user_email_link_id', '$ip', '$user_agent', now(), $email_click_code)";
		if(!Database::mysqli_query($sql))
			error_log("Insert SQL error in Mailer::log_user_email_click(): ".Database::mysqli_error()."\nSQL: ".$sql);
	}
	
	public static function updateUserEmailClick($id)
	{
		$sql = "update user_email_clicks set page_loaded = '1' where id = '$id'";
		Database::mysqli_query($sql);
	}
	
	public static function insert_email_link($url_href, $email_link_code, $user_email_code)
	{
		$sql = "insert into user_email_links (`url_href`, `email_link_code`, `user_email_code`) values ('$url_href', '$email_link_code', '$user_email_code')";
		if(!Database::mysqli_query($sql))
			error_log("Insert SQL error in Mailer::insert_email_link(): ".Database::mysqli_error()."\nSQL: ".$sql);
	}
	
	public static function send_magento_email($coupon_code, $user_id){
		
	}
	
	public function SendRatesApproval($reseller)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = array('bshipley@coupsmart.com');
		$mailer->subject = 'CoupSmart.com Rate Approval Needed';
		$mailer->message = '
		The rates for the following reseller have been customized:
		<br /><br />
		<strong>Reseller Name:</strong> '. $reseller->name.' <br /><br />
		<strong>Fan Count 0-250:</strong> '. $reseller->rate_level_1.' <br /><br />
		<strong>Fan Count 251-2,500:</strong> '. $reseller->rate_level_2.' <br /><br />
		<strong>Fan Count 2,501-5,000:</strong> '. $reseller->rate_level_3.' <br /><br />
		<strong>Fan Count 5,001-15,00:</strong> '. $reseller->rate_level_4.' <br /><br />
		<strong>Fan Count 15,001-30,000:</strong> '. $reseller->rate_level_5.' <br /><br />
		<strong>Setup Rate: </strong> '. $reseller->company_setup_price.' <br /><br />
		<strong>Custom Basic Rate: </strong> '. $reseller->company_base_price.' <br /><br />
		
		
		<a href="http://' . $_SERVER['SERVER_NAME'] . '/admin/edit-rates?reseller_id=' . $reseller->id .'" >Check these Rates</a><br />
		<a href="http://' . $_SERVER['SERVER_NAME'] . '/admin/edit-rates?approve=true&reseller_id=' . $reseller->id .'" >Quick Approval</a>
		';
		
		return $mailer->send();
	}
	
	public static function SendCustomCustomerCode($user, $custom_code, $expired_date, $from_name, $subject, $template)
	{
		/*
		$expired_year = substr($expired_date, 0, 4);
		$expired_month_day = str_replace(" ", "", str_replace("-", "/", substr($expired_date, 5, 6)));
		$expired_date = $expired_month_day . "/" . $expired_year;
		*/
		
		global $ses_smtp_username, $ses_smtp_password, $ses_smtp_host;

		$mail = new PHPMailer();
		$mail->CharSet = 'UTF-8';

		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host       = $ses_smtp_host; // "email-smtp.us-east-1.amazonaws.com"; // SMTP server
		$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
										   // 1 = errors and messages
										   // 2 = messages only
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		$mail->Port       = 465;                    // set the SMTP port for the SES server
		$mail->SMTPSecure = 'ssl';
		$mail->Username   = $ses_smtp_username; // "AKIAIBQXMIRDF65ZYXRQ"; 			// SMTP account username
		$mail->Password   = $ses_smtp_password; // "AgF+CeKN3+YK32WuIkC8hUKsezoXuayMlWdtK6Isa4w0";        // SMTP account password

		if(empty($from_name)){
			$from_name = "noreply@coupsmart.com";
		}
		$mail->SetFrom('support@coupsmart.com', $from_name);

		$mail->AddReplyTo("support@coupsmart.com","CoupSmart Support");

		if(empty($subject)){
			$subject = "CoupSmart Coupon";
		}
		$mail->Subject    = $subject;
		if (is_rfc3696_valid_email_address($user['email'])) {
			try {
				$mail->AddAddress($user['email']);
			} catch (Exception $e) {
				error_log("couldn't add address for phpmailer: " . $e->getMessage());
			}
		}
		
		// Commenting this out since the template is no longer a filename but the body itself.
		// $fh = fopen($template, 'r');
		// $body = fread($fh, filesize($template));
		if(empty($template)){
			//error_log("the template was empty");
			//path to our generic email template if none is chosen for this item or company
			
			$filePath = "/var/www/html/coupsmart/dev/emails/genericCustomCodeEmail.html";
			//open the file
			$file = fopen($filePath, "r");
			//read it
			$template = fread($file, filesize($filePath));
		}

		$body = $template;
		// error_log('replacing "customCode" with ' . $custom_code);
		$body = str_replace("customCode", $custom_code, $body);
		$body = str_replace("facebookImage", $user['id'], $body);
		$body = str_replace("expirationDate", $expired_date, $body);
		$body = str_replace("facebookName", $user['name'], $body);
		
		$mail->MsgHTML($body);
		$mail->Send();
	}
	
	public static function sendWeeklyData()
	{
		//necessities for an email
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';

		//these need to be changed for the Customer
		$mailer->to = 'jheath@coupsmart.com';
		$mailer->subject = "Weekly Data";



		//grabs the reseller companies
		$resellerCompanies = "select r.`name`, c.display_name
								from companies c
								left join reseller_companies rc
								on c.id = rc.company_id
								left join resellers r
								on r.id = rc.reseller_id
								where c.`status`= 'active'
								order by (CASE WHEN r.name IS NULL then 1 ELSE 0 END) ASC, r.name, c.display_name;";
		$rs = Database::mysqli_query($resellerCompanies);
		$results = array();

		//set date

		$oneDay = 86400; //The number of seconds in one day
		$oneWeek = 7*$oneDay; //The number of seconds in one week

		//calculate today's date
		$currentTime =  time(); //1300752000;
		$todayDate = date("D, d M Y", $currentTime);
		//error_log("Today's date is ACTUALLY: " . $currentTime);

		//change current time so that it lines up with campaign data
		$currentTime = strtotime($todayDate);
		$todayDate = date("D, d M Y", $currentTime);

		//go back 7 days
		$thisWeek = $currentTime - $oneWeek;
		$startDate = date("D, d M Y", $thisWeek);
		//error_log("Going back to 7 days from today: " . $startDate);

		//error_log("Today's date is CLOSE TO: " . $todayDate . " Numeral today: " . $currentTime . " Numeral 7 days ago: " .$thisWeek);



		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			while ($row = Database::mysqli_fetch_assoc($rs)) {
				 //error_log("Got the company list");

				//from those companies, grab the number of running campaigns
				$runningCampaigns = "select count(*)
									from companies c
									left join reseller_companies rc
									on c.id = rc.company_id
									left join resellers r
									on r.id = rc.reseller_id
									left join users_companies_campaigns uc
									on c.id = uc.companies_id
									left join campaigns camp
									on uc.campaigns_id = camp.id
									where c.`status`= 'active'
									and uc.campaigns_id IS NOT NULL
									and camp.`status`= 'running'
									and c.display_name = '".Database::mysqli_real_escape_string($row['display_name'])."';";



				//from those campaigns, grab the campaign data
				$runningCampaignData = "select camp.stats_campaign
									from companies c
									left join reseller_companies rc
									on c.id = rc.company_id
									left join resellers r
									on r.id = rc.reseller_id
									left join users_companies_campaigns uc
									on c.id = uc.companies_id
									left join campaigns camp
									on uc.campaigns_id = camp.id
									where c.`status`= 'active'
									and uc.campaigns_id IS NOT NULL
									and camp.`status`= 'running'
									and c.display_name = '".Database::mysqli_real_escape_string($row['display_name'])."';";

				//from those campaigns, grab the social data
				$runningSocialData = "select camp.stats_social
									from companies c
									left join reseller_companies rc
									on c.id = rc.company_id
									left join resellers r
									on r.id = rc.reseller_id
									left join users_companies_campaigns uc
									on c.id = uc.companies_id
									left join campaigns camp
									on uc.campaigns_id = camp.id
									where c.`status`= 'active'
									and uc.campaigns_id IS NOT NULL
									and camp.`status`= 'running'
									and c.display_name = '".Database::mysqli_real_escape_string($row['display_name'])."';";

				//select the name of the campaigns
				$campaignNames = "select camp.`name`
									from companies c
									left join reseller_companies rc
									on c.id = rc.company_id
									left join resellers r
									on r.id = rc.reseller_id
									left join users_companies_campaigns uc
									on c.id = uc.companies_id
									left join campaigns camp
									on uc.campaigns_id = camp.id
									where c.`status`= 'active'
									and uc.campaigns_id IS NOT NULL
									and camp.`status`= 'running'
									and c.display_name = '".Database::mysqli_real_escape_string($row['display_name'])."';";

				//grab the reseller names for the print
				$resellerNames = "select r.`name`
									from companies c
									left join reseller_companies rc
									on c.id = rc.company_id
									left join resellers r
									on r.id = rc.reseller_id
									left join users_companies_campaigns uc
									on c.id = uc.companies_id
									left join campaigns camp
									on uc.campaigns_id = camp.id
									where c.`status`= 'active'
									and uc.campaigns_id IS NOT NULL
									and camp.`status`= 'running'
									and c.display_name = '".Database::mysqli_real_escape_string($row['display_name'])."';";


				$rs2 = Database::mysqli_query($runningCampaigns); //query number of campaigns
				$campaigns = Database::mysqli_fetch_assoc($rs2);
				$rs3 = Database::mysqli_query($runningCampaignData); //query data
				$rs4 = Database::mysqli_query($campaignNames);
				$sharesData = Database::mysqli_query($runningSocialData); // query social data
				$reseller_names = Database::mysqli_query($resellerNames); //query reseller names

				//if there are running campaigns (which means there are also stats for that campaign):
				if ($rs2 && Database::mysqli_num_rows($rs2) > 0)
				{
					while($row2 = Database::mysqli_fetch_assoc($rs3)) //loops through as many times as there are campaigns
					{
						//create data variables
						$views = 0;
						$prints = 0;
						$redeems = 0;
						$fancount = 0;
						$oldfancount = 1;
						$shares = 0;
						$percViews = 1;
						$percPrints = 1;
						$percRedeems = 1;
						$percShares = 1;
						$sign = "increase";
						$a = 1;

						//error_log("Cycling through new campaign....");
						//error_log("Extracting data............");
						//fetch data for each loop
						$stats = $row2;
						$campaignName = Database::mysqli_fetch_assoc($rs4);
						$socialData = Database::mysqli_fetch_assoc($sharesData);
						$reseller_name = Database::mysqli_fetch_assoc($reseller_names);
						if($reseller_name['name'] == '' || $reseller_name['name'] = NULL) {$reseller_name['name'] = 'No reseller for this company';}

						// decode the campaigns data
						$decodedData = json_decode($stats["stats_campaign"]);

						//decode the social data
						$decodedSocial = json_decode($socialData["stats_social"]);

						//check if there is social data for this campaign
						if(empty($decodedSocial[2])) { error_log("There was no social data.");} //there isn't data
						else //there is data
						{
							//error_log("decodedSocial: " . var_export($decodedSocial[2], true));
							//error_log("There was social data........extracting........");
							$insideDecodedSocial = get_object_vars($decodedSocial[2]);
							
							//loop through all the delivery methods
							for ($a; $a < 10; $a++)
							{
								$test = empty($insideDecodedSocial[$a]);
								if(!$test)
								{
									//error_log("A: " . $a);
									$actualShares = get_object_vars($insideDecodedSocial[$a]);
								}
							}

							//error_log("Gathering this week's shares........");

							//gather shares for that week of the campaign
							foreach($actualShares as $day => $val) //traverse through the array until there are no more elements
							{
								if ($day >= $thisWeek && $day <= $currentTime)
								{
									//error_log("HIT HIT");
									$thisDaysShares = 0;
									$thisDaysShares = $val[0];
									$shares += $thisDaysShares;
								}
								else
								{ //add them to percShares
									//error_log("Entry into percShares");
									$thisDaysShares = 0;
									$thisDaysShares = $val[0];
									$percShares += $thisDaysShares;
								}
							}

							//error_log("PERCSHARE: " . $percShares);

						}
						//error_log("DONE");
						$a = 1; //reset $a

						//error_log("Gathering this week's campaign data........");
						//gather views, prints, redeems, fancount for that week of the campaign
						if(empty($decodedData[0])){ error_log("There was no campaign data...");} //there was no campaign data
						else{

							//error_log("There was campaign data..............extracting..............");
							$insideDecodedData = get_object_vars($decodedData[0]);
							//error_log("inside : " . var_export($insideDecodedSocial, true));
							//check all the delivery methods, only act on the that has data in it
							for ($a; $a < 10; $a++)
							{
								$test = empty($insideDecodedData[$a]);
								if(!$test)
								{
									//error_log("A: " . $a);
									$actualData = get_object_vars($insideDecodedData[$a]);
									//error_log('actual data: ' .var_export($actualData, true));

								}
							}

							//error_log("Gathering data..............");
							//error_log(var_export($actualData, true));
							//gather shares for that week of the campaign
							foreach($actualData as $day => $val) //traverse through the array until there are no more elements
							{

								if ($day >= $thisWeek && $day <= $currentTime)

								{
									//error_log("HIT HIT");
									$thisDaysViews = $val[0];
									$thisDaysPrints = $val[1];
									$thisDaysRedeems = $val[2];
									$thisDaysFancount = $val[3];
									$views += $thisDaysViews;
									$prints += $thisDaysPrints;
									$redeems += $thisDaysRedeems;

									$oldfancount = $fancount;
									$fancount = $thisDaysFancount;

								}
								else
								{ //add them to percShares
									//error_log("adding to total campaign data...");
									$thisDaysViews = $val[0];
									$thisDaysPrints = $val[1];
									$thisDaysRedeems = $val[2];
									$thisDaysFancount = $val[3];
									$percViews += $thisDaysViews;
									$percPrints += $thisDaysPrints;
									$percRedeems += $thisDaysRedeems;

								}
							}
						}
						$a = 1; //reset $a
						//error_log("DONE");
						//error_log("Gathered all the data");

						$percentIncreaseV = number_format(100*($views/$percViews),2);
						$percentIncreaseP = number_format(100*($prints/$percPrints),2);
						$percentIncreaseR = number_format(100*($redeems/$percRedeems),2);
						$percentIncreaseF = number_format(100*($fancount-$oldfancount)/$oldfancount,1);
						if($percentIncreaseF < 0){$sign = "decrease"; $percentIncreaseF = $percentIncreaseF*(-1);} //it was a decrease in fans this week
						$percentIncreaseS = number_format(100*($shares/$percShares),2);
						//error_log("printing results to team");



						$results[] = "<br></br><br></br><b>" .
										$reseller_name['name']." <br></br><i>".
										$row['display_name']."</i></b><br></br><i>".
										$campaigns["count(*)"]." running campaign(s) </i><br></br>
										You are looking at the <u>" . $campaignName["name"] ."</u> campaign <br></br>" .
										$views . " new views (". $percentIncreaseV . "% increase);    " .
										$prints . " new prints (". $percentIncreaseP ."% increase);    " .
										$redeems . " new redeems (". $percentIncreaseR ."% increase);
										They have a new fancount of " .$fancount." (". $percentIncreaseF ."%  <b>" . $sign . "</b>); "  .
										$shares . " new shares (" . $percentIncreaseS . "% increase)";
										//error_log("RESULTS: " . var_export($results,true));
					}
				}

				//if there are no running campaigns:
				else
				{
					error_log("There were no campaigns..."); //don't do anything
				}
			}
		}
		$mailer->message = "This is a list of all the campaigns that are currently
										active and owned by a reseller. This data
										pertains to this past week: <br></br><b>" .
										$startDate . "</b> to <b>" . $todayDate .
										"</b><br></br> <i>Real knowledge is to know
										the extent of one's ignorance.</i> <br></br>--Confucius <br></br>
										" . implode(",", $results);
		return $mailer->send();
	}
	
	public function sendFeedback($message)
	{
		$mailer = new Mailer();
		$mailer->CharSet = 'UTF-8';
		$mailer->to = 'support@coupsmart.com';
		$mailer->subject = 'Form submission';
		$mailer->message = 'You submitted a form: <br></br>' . $message;
		return $mailer->send();
	}
	
	public function sendCouponReprintURL($email, $user_item_id)
	{
		$reprint_info = UserItems::getReprintInfoById($user_item_id);
		$reprint_code = $reprint_info['reprint_code'];
		$url = Common::getBaseURL(true) . "/support-request?c=" . urlencode($reprint_code);
		// if(!empty($uiids))
		//	$url .= "&uiids=$uiids";
			
		$emailBody = "<html><body><p>This is an automated message sent by CoupSmart Customer Support to help to print a coupon or voucher you previously attempted.</p><p>Before using the reprint tool, please make sure that your computer is connected to the printer and that it is able to print since this link only allows one extra chance.</p><p>Using a desktop or laptop that is logged into your Facebook account, please go here to reprint your coupon: <a href=\"$url\">CoupSmart Reprint Tool</a></p><p>Follow the instructions on-screen and if you are asked to accept permissions to Smart Deals, the Facebook App that provides the coupon, please accept in order for us to display your new coupon.</p><br/><hr /><br /><p>If you continue to experience any further issues, please respond to your CoupSmart Support Representative in the original email chain so they may better assist you.</p></body></html>";
		$this->subject = "Your Coupon Reprint Link from CoupSmart Support";
		$this->message = $emailBody;
		$this->to = $email;
		// error_log("mailer in Mailer::sendCouponReprintURL(): ".var_export($this, true));
		
		$res = $this->send('html');
		if(!$res) {
			$errors["There was an error sending a e-mail to $email."] = 1;
			error_log('There was an error sending a e-mail to ' . $email. '.');
		} else {
			error_log('Sent email message to ' . $email . '.');
			$sql = "update user_items set reprint_url_sent = '1' where id = '$user_item_id'";
			if(!Database::mysqli_query($sql))
				error_log("SQL Update error in Mailer::sendCouponReprintURL()! " . Database::mysqli_error() . "\nSQL: " . $sql);
		}
		return $res;
	}
	
	public function sendEmailForSupportRequestWithPrintLink($email, $user_id, $item_id, $uiids)
	{
		$url = Common::getBaseURL(true) . "/support-request?user_id=$user_id&item_id=$item_id";
		// if(!empty($uiids))
		//	$url .= "&uiids=$uiids";
			
		$emailBody = "<html><body><a href='$url'>Click here to retry printing your coupon!</a></body></html>";
		$this->subject = "In response to your Support Request";
		$this->message = $emailBody;
		$this->to = $email;
		error_log("mailer: ".var_export($this, true));
		
		if(!$this->send('html')) {
			$errors["There was an error sending a e-mail to $email."] = 1;
			error_log('There was an error sending a e-mail to ' . $email. '.');
		} else {
			error_log('Sent email message to ' . $email . '.');
		}
	}
	
	public function sendEmailForSupportRequest($email, $company_id = null)
	{
		$company_name = "company";
		if(!empty($company_id))
		{
			$company = new Company($company_id);
			$company_name = $company->display_name;
		}
		$emailBody = "<html><body>Hi,<p> go back to the $company_name page and try to print again</p><p>Regards,<br>The Coupsmart Team.</p></body></html>";
		$this->subject = "In response to your Support Request";
		$this->message = $emailBody;
		$this->to = $email;
		// error_log("mailer: ".var_export($this, true));
		
		if(!$this->send('html')) {
			$errors["There was an error sending a e-mail to $email."] = 1;
			error_log('There was an error sending a e-mail to ' . $email. '.');
		} else {
			error_log('Sent email message to ' . $email . '.');
		}
	}

	public static function add_sender_to_blacklist($domain_name, $sender_email)
	{
		$sql = "insert into `isp_blacklists` (`domain_name`, `sender_email`) values ('$domain_name', '$sender_email')";
		if(!Database::mysqli_query($sql))
			error_log("SQL Insert error in Mailer::add_sender_to_blacklist(): ".Database::mysqli_error()."\nSQL: ".$sql);
		
	}
	
	public static function getUserEmailInfoBySESMessageId($ses_message_id)
	{
		$sql = "select ue.* from user_emails ue where ue.ses_message_id = '$ses_message_id'";
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function log_sns_feedback_notifications($notification)
	{
		$message_id = '';
		$input= json_decode( $notification, true);
		if(isset($input['Message']))
		{
			$message = json_decode($input['Message'], true);
			if(isset($message['mail']['messageId']))
				$message_id = $message['mail']['messageId'];
		}
		
		$sql = "insert into sns_feedback_notifications (`notification`, `message_id`) values ('".Database::mysqli_real_escape_string($notification)."', '$message_id')";
		if(!Database::mysqli_query($sql))
			error_log("SQL Insert error in Mailer::log_sns_feedback_notifications(): ".Database::mysqli_error()."\nSQL: ".$sql);
	}
	
	public static function sendCustomReportCodes($campaign_id)
	{
		$usedCodes = PrintMethods::gather_codes($campaign_id);
		$today_date = time();
		//error_log("usedCodes: " . var_export($usedCodes,true));
		$results = array();
		$fp = fopen('file.csv','w');
		//error_log("here inside the sendPubCodes");
		fputcsv($fp, array("ID", "Code", "Issued Status", "BLANK", "User ID", "Date Issued"));
		foreach($usedCodes as $code){
			fputcsv($fp, $code);
		}
		fclose($fp);
		
		$company_id = Common::getCompanyIdByCampaignId($campaign_id);
		$company = new Company($company_id);
		
		$arr_to = array();
		// $campaign = new Campaign($campaign_id);
		// $csc_report_recipients = $campaign->csc_report_recipients;
		$csc_report_recipients = $company->analytics_report_recipients;
		if(!empty($csc_report_recipients))
		{
			$arr_tmp = explode(',', $csc_report_recipients);
	
			// Remove any white spaces from the email addresses in array
			// $arr_to = array_map('trim', $arr_to);
			foreach ($arr_tmp as  $to_email) {
				$to_email = trim($to_email);
				if (!empty($to_email) && $to_email != null && is_rfc3696_valid_email_address($to_email)) {
					$arr_to[] = $to_email;
				}
			}
			
			// $coupsmart_recipients = array('sqazi@coupsmart.com', 'tdavis@coupsmart.com', 'khoeffer@coupsmart.com');
			$coupsmart_recipients = array('sqazi@coupsmart.com', 'khoeffer@coupsmart.com');
			$arr_to = array_values(array_unique(array_merge($arr_to, $coupsmart_recipients)));
		}
		else
		{
			return null;
		}
			
		$to = implode(',', $arr_to);
		// error_log("to: " . var_export($to, true));
		
		
		// $subject = $company['display_name'] . " Custom Code Usage Report from CoupSmart";
		$subject = $company->display_name . " Custom Code Usage Report from CoupSmart";
		$attachment = chunk_split(base64_encode(file_get_contents("file.csv")));
		$headers = "From: noreply@coupsmart.com\r\nReply-To: noreply@coupsmart.com";
		$semi_rand = md5(time()); //random number generated used to make a custom boundary line
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; //the boundary used to distinguish between what goes into the body and what goes into the attachment

		  $headers .= "\nMIME-Version: 1.0\n" .  //identify the version of MIME format
		  "Content-Type: multipart/mixed;\n" .   //the container for the body and attachment must be mixed so the original header knows that it is a mixed message
		  " boundary=\"{$mime_boundary}\"";      //use this as the boundary to distinguish between attachment and body
		  
		  // $headers .= "\nBcc: sqazi@coupsmart.com" . "\r\n";	// For testing purposes 

		  $email_message = "This is a multi-part message in MIME format.\n\n" .
		  "--{$mime_boundary}\n" . //boundary
		  "Content-Type:text/html; charset=\"iso-8859-1\"\n" . //HTML of the body of the email
		  "Content-divansfer-Encoding: 7bit\n\n" .
		  "This is an email that provides the used codes and their issued dates from your campaign. The attachment contains all codes and they are sorted " .
		  "from earliest issued date to latest issue date. The columns should be read from left to right as shown below:<br></br><br></br>" .
		  "ID <b>|</b>  Code  <b>|</b>  Issued Status (1 = issued, 0 = not issued)  <b>|</b>  BLANK <b>|</b>  User Id (user that was issued this code) <b>|</b>  Date Issued</b>" . "\n\n";

		  $email_message .= "--{$mime_boundary}\n" .  //boundary
		  "Content-Type: text/csv;\n" .  //attachment, csv file
		  " name=\"Codes_".$today_date.".csv\"\n" .	//name of attachment
		  "Content-Disposition: attachment;\n" .
		  //" filename=\"{$fileatt_name}\"\n" .
		  "Content-Transfer-Encoding: base64\n\n" .
		  $attachment . "\n\n" .
		  "--{$mime_boundary}--\n";

		$ret = mail($to, $subject, $email_message, $headers);
		error_log("Result of sending emails in Mailer::sendCustomReportCodes(): " . var_export($ret, true));
	}
	
	public static function getUserEmailIdByEmailCode($user_email_code)
	{
		$sql = "select id from user_emails where user_email_code = '" .$user_email_code. "'";
		$row = BasicDataObject::getDataRow($sql);
		$user_email_id = !empty($row['id']) ? $row['id'] : null;
		return $user_email_id;
	}
	
	public static function sendTaskAssignedNotification($task_data)
	{
		
	}
}
?>