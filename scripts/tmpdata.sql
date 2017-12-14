-- MySQL dump 10.13  Distrib 5.6.12, for osx10.7 (x86_64)
--
-- Host: localhost    Database: newcoupsmart
-- ------------------------------------------------------
-- Server version	5.6.12

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` bigint(32) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL,
  `sort_order` int(10) DEFAULT NULL,
  `manufacturer_id` bigint(32) unsigned NOT NULL,
  `deal_id` int(10) DEFAULT NULL,
  `upc` varchar(15) DEFAULT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `name` varchar(65) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `details_preview` varchar(255) DEFAULT NULL,
  `small_type` text,
  `small_type_preview` text,
  `claim_button_text` varchar(255) DEFAULT NULL,
  `gmap` text,
  `us_time_zone` varchar(5) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `delivery_method` int(10) unsigned NOT NULL,
  `limit_per_person` int(16) unsigned NOT NULL,
  `retail_price` decimal(9,2) DEFAULT NULL,
  `savings` decimal(9,2) DEFAULT NULL,
  `offer_code` char(3) DEFAULT NULL,
  `offer_value` varchar(255) DEFAULT NULL,
  `offer_value_preview` varchar(255) DEFAULT NULL,
  `social_offer_service_name` varchar(65) DEFAULT NULL,
  `social_offer_code` char(3) DEFAULT NULL,
  `social_offer_value` varchar(255) DEFAULT NULL,
  `platform_social_small_type` text,
  `num_friends` int(10) unsigned DEFAULT NULL,
  `social_small_type` text,
  `new_fan_offer` varchar(250) DEFAULT NULL,
  `use_coupon_barcode` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `barcode_co_prfx` char(6) DEFAULT NULL,
  `barcode_family_code` char(3) DEFAULT NULL,
  `barcode_social_family_code` char(3) DEFAULT NULL,
  `barcode_offer_code` char(5) DEFAULT NULL,
  `barcode_social_offer_code` char(5) DEFAULT NULL,
  `barcode_social_offer_service_name` varchar(100) DEFAULT NULL,
  `expire_month` int(3) unsigned DEFAULT NULL,
  `expire_year` int(3) unsigned DEFAULT NULL,
  `inventory_count` bigint(20) DEFAULT NULL,
  `controlled_printable_image` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_approval` bit(1) DEFAULT NULL,
  `supplied` bigint(20) DEFAULT NULL,
  `committed` bigint(20) DEFAULT NULL,
  `shipped` bigint(20) DEFAULT NULL,
  `social_print_count` bigint(20) DEFAULT NULL,
  `status` enum('pending','running','finished','stopped','paused','deleted') DEFAULT NULL,
  `needs_clearinghouse` tinyint(1) unsigned DEFAULT '0',
  `needs_clearinghouse_barcode` varchar(15) DEFAULT NULL,
  `campaign_name` varchar(150) DEFAULT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `view_count` bigint(20) unsigned DEFAULT '0',
  `platform_social_offer_service_name` varchar(65) DEFAULT NULL,
  `platform_social_offer_code` char(3) DEFAULT NULL,
  `platform_social_offer_value` varchar(255) DEFAULT NULL,
  `platform_social_offer_small_type` text,
  `platform_num_friends` int(10) unsigned DEFAULT NULL,
  `instore_main_text` text,
  `instore_btn_view_offers_text` varchar(50) DEFAULT NULL,
  `instore_email_print_btn` tinyint(1) unsigned DEFAULT '1',
  `instore_email_onscreen_btn` tinyint(1) unsigned DEFAULT '1',
  `instore_email_footer_content` text,
  `instore_email_from` varchar(100) DEFAULT NULL,
  `instore_email_subject` varchar(100) DEFAULT NULL,
  `instore_email_header_img` varchar(100) DEFAULT NULL,
  `instore_email_header_caption` varchar(100) DEFAULT NULL,
  `banner_image_link_url` text,
  `voucher_layout_id` int(16) unsigned DEFAULT NULL,
  `redirect_url` text,
  `static_fulfillment_html` text,
  `hotel_discount_id` varchar(50) DEFAULT NULL,
  `hotel_discount_percent` decimal(9,2) DEFAULT NULL,
  `hotel_discount_amount` decimal(9,2) DEFAULT NULL,
  `share_own_wall` tinyint(4) DEFAULT '1',
  `share_friends_wall` tinyint(4) DEFAULT '1',
  `share_send_request` tinyint(1) DEFAULT '0',
  `magento_email_check` tinyint(4) DEFAULT NULL,
  `magento_landing_page` varchar(255) DEFAULT NULL,
  `magento_landing_page_url` text,
  `magento_landing_page_setup_header` text,
  `magento_landing_page_setup_body` text,
  `white_label_css` longtext,
  `white_label_css_1` text,
  `white_label_css_2` text,
  `white_label_css_3` text,
  `white_label_css_4` text,
  `button_color` varchar(30) DEFAULT NULL,
  `button_text_color` varchar(30) DEFAULT NULL,
  `button_details_color` varchar(30) DEFAULT NULL,
  `mo_headline_bg` varchar(30) DEFAULT NULL,
  `mo_headline_text_color` varchar(30) DEFAULT NULL,
  `mo_header_color` varchar(30) DEFAULT NULL,
  `mo_body_color` varchar(30) DEFAULT NULL,
  `company_sdw_unique_codes_id` int(11) DEFAULT NULL,
  `show_print_options` tinyint(1) DEFAULT '0',
  `csc_reveal_deal_content` text,
  `csc_reveal_deal_content_mobile` text,
  `csc_cta_heading` varchar(150) DEFAULT NULL,
  `csc_cta_url` varchar(255) DEFAULT NULL,
  `csc_custom_code` varchar(50) DEFAULT NULL,
  `csc_email_header_image` varchar(100) DEFAULT NULL,
  `csc_email_store_url` text,
  `footer_content` longtext,
  `csc_email_from` varchar(100) DEFAULT NULL,
  `csc_email_subject` varchar(100) DEFAULT NULL,
  `csc_email_template` text,
  `parent_item_id` bigint(32) unsigned DEFAULT NULL,
  `app_id` int(3) DEFAULT NULL,
  `deliverable_id` int(3) DEFAULT NULL,
  `e_commerce_code` text,
  `use_rolling_expiry_date` tinyint(1) DEFAULT NULL,
  `days_rolling_expiry_date` int(11) DEFAULT NULL,
  `unique_email_code` varchar(36) DEFAULT NULL,
  `email_code_snippet` text,
  `email_code_integration_type` enum('sp','et') DEFAULT NULL,
  `email_code_content_type` enum('qr','upc','upc-a','upc-e','alpha-numeric','text') DEFAULT 'alpha-numeric',
  `email_code_service_url` text,
  `email_code_color` varchar(20) DEFAULT NULL,
  `email_code_size` varchar(20) DEFAULT NULL,
  `use_bundled_coupons` tinyint(1) DEFAULT NULL,
  `bundled_coupon_copy` varchar(100) DEFAULT NULL,
  `for_consultants` tinyint(1) DEFAULT NULL,
  `deal_upon_out_of_stock` int(10) DEFAULT NULL,
  `out_of_stock_deal` int(10) DEFAULT NULL,
  `coupon_age_limit` int(11) DEFAULT NULL,
  `location_specific_vouchers` enum('city','state') DEFAULT NULL,
  `trigger_url` text,
  PRIMARY KEY (`id`),
  KEY `index_deal_id` (`deal_id`),
  KEY `index_manufacturer_id` (`manufacturer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2412 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (2394,NULL,NULL,1147,1280,NULL,'TestCorporate Deal 1','TestCorporate Deal 1',NULL,NULL,NULL,'TestCorporate Deal 1 - Details. TestCorporate Deal 1 - Details. TestCorporate Deal 1 - Details. ',NULL,NULL,NULL,NULL,'2016-07-05 15:00:00',NULL,NULL,3,100000,NULL,NULL,NULL,'TestCorporate Deal 1 - Subheading',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10000,NULL,'2016-07-05 10:02:53',NULL,NULL,0,NULL,NULL,'running',0,NULL,'TestCorporate Deal 1',2394,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'div#div-deal-background-image {\n			display: none !important\n			}\n\n			div.overlay {\n			display: none !important;\n			}\n\n			#company_logo{\n				border:0px solid red; \n				width:100%; \n				display:block;\n			}\n\n\n			.ui-btn-text{\n				color: #111;\n				width: 200px;\n				font-size: 10px;\n				margin-top: -19px;\n				margin-left: -10px;\n			}\n\n			#logoblock{\n				display:table-cell; \n				max-height:80px;\n				max-width:80px;\n				float:left; \n				vertical-align:middle;\n				background-repeat:no-repeat;\n				background-size:100%;\n				margin:8px;\n			}\n\n			#location{\n				display:table-cell; \n				height:100%;\n				width:170px;\n			}\n			/* Header */\n			.dealheader {\n				background-color: rgb(153, 138, 205);\n				color: white;\n			}\n			/* Use Now Button */\n			#btn_print_now {\n				border: 1px solid #145072;\n				color: white;\n				background: rgb(199, 199, 32);\n				background-image: -moz-linear-gradient(top, #4E89C5, #2567AB);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #5F9CC5), color-stop(1, #396B9E));\n				border-radius:10px;\n				margin-top:10px;\n			}\n\n			/* Use Now Button Hover*/\n			#btn_print_now:hover {\n				background: orange;\n			}\n\n			/* Use Now Button Text*/\n			#btn_print_now span {\n				padding: .6em 25px;\n				display: block;\n				height: 100%;\n				text-overflow: ellipsis;\n				overflow: hidden;\n				white-space: nowrap;\n				position: relative;\n			}\n\n			/* Terms Details Text */\n			p[name=\'p_instore_discount_instructions\'] {\n				font-size: 8px;\n			}\n\n			/* Terms Button */\n			.terms_button {\n				text-align: center;\n				border: 1px solid gray;\n				background: #FDFDFD;\n				border-radius:10px;\n				background-image: -moz-linear-gradient(top, #EEE, #FDFDFD);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #EEE), color-stop(1, #FDFDFD));}\n\n			.banner-row {background: rgb(209, 209, 129);}\n			.companyname {text-shadow: none;}\n			a.button.success,.button.success:active,.button.success:hover,.button.success:focus {background-color:rgb(199, 199, 32); color: white}\n			body {background:rgb(241, 241, 205);}\n			a.button.details,.button.details:active,.button.details:hover,.button.details:focus {background-color:rgb(48, 191, 57); color: white;}\n			.offerimage div {background-image: none !important;}\n			div.offerimage {background-image:url(\'http://uploads.coupsmart.com.s3.amazonaws.com/04485f294238663f767d73dfffed084d.jpg\')!important;background-position: center center;background-size:contain;background-repeat:no-repeat;height: 300px;}\n			a#change_email {color:#D60000;text-decoration:underline;}\n			div#loaded button#print {display:none !important}',NULL,NULL,'rgb(223, 97, 50)','white',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'alpha-numeric',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2395,NULL,NULL,1147,1280,NULL,'TestCorporate Deal 1','TestCorporate Deal 1',NULL,NULL,NULL,'TestCorporate Deal 1 - Details. TestCorporate Deal 1 - Details. TestCorporate Deal 1 - Details. ',NULL,NULL,NULL,NULL,'2016-07-05 15:00:00',NULL,NULL,6,100000,NULL,NULL,NULL,'TestCorporate Deal 1 - Subheading',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10000,NULL,'2016-07-05 10:02:53',NULL,NULL,0,NULL,NULL,'running',0,NULL,'TestCorporate Deal 1',2395,39,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'div#div-deal-background-image {\n			display: none !important\n			}\n\n			div.overlay {\n			display: none !important;\n			}\n\n			#company_logo{\n				border:0px solid red; \n				width:100%; \n				display:block;\n			}\n\n\n			.ui-btn-text{\n				color: #111;\n				width: 200px;\n				font-size: 10px;\n				margin-top: -19px;\n				margin-left: -10px;\n			}\n\n			#logoblock{\n				display:table-cell; \n				max-height:80px;\n				max-width:80px;\n				float:left; \n				vertical-align:middle;\n				background-repeat:no-repeat;\n				background-size:100%;\n				margin:8px;\n			}\n\n			#location{\n				display:table-cell; \n				height:100%;\n				width:170px;\n			}\n			/* Header */\n			.dealheader {\n				background-color: rgb(153, 138, 205);\n				color: white;\n			}\n			/* Use Now Button */\n			#btn_print_now {\n				border: 1px solid #145072;\n				color: white;\n				background: rgb(199, 199, 32);\n				background-image: -moz-linear-gradient(top, #4E89C5, #2567AB);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #5F9CC5), color-stop(1, #396B9E));\n				border-radius:10px;\n				margin-top:10px;\n			}\n\n			/* Use Now Button Hover*/\n			#btn_print_now:hover {\n				background: orange;\n			}\n\n			/* Use Now Button Text*/\n			#btn_print_now span {\n				padding: .6em 25px;\n				display: block;\n				height: 100%;\n				text-overflow: ellipsis;\n				overflow: hidden;\n				white-space: nowrap;\n				position: relative;\n			}\n\n			/* Terms Details Text */\n			p[name=\'p_instore_discount_instructions\'] {\n				font-size: 8px;\n			}\n\n			/* Terms Button */\n			.terms_button {\n				text-align: center;\n				border: 1px solid gray;\n				background: #FDFDFD;\n				border-radius:10px;\n				background-image: -moz-linear-gradient(top, #EEE, #FDFDFD);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #EEE), color-stop(1, #FDFDFD));}\n\n			.banner-row {background: rgb(209, 209, 129);}\n			.companyname {text-shadow: none;}\n			a.button.success,.button.success:active,.button.success:hover,.button.success:focus {background-color:rgb(199, 199, 32); color: white}\n			body {background:rgb(241, 241, 205);}\n			a.button.details,.button.details:active,.button.details:hover,.button.details:focus {background-color:rgb(48, 191, 57); color: white;}\n			.offerimage div {background-image: none !important;}\n			div.offerimage {background-image:url(\'/images/uploads/s3bucket/04485f294238663f767d73dfffed084d.jpg\')!important;background-position: center center;background-size:contain;background-repeat:no-repeat;height: 300px;}\n			a#change_email {color:#D60000;text-decoration:underline;}\n			div#loaded button#print {display:none !important}',NULL,NULL,'rgb(199, 199, 32)','white','rgb(48, 191, 57)','rgb(153, 138, 205)','white','rgb(209, 209, 129)','rgb(241, 241, 205)',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'alpha-numeric',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2411,NULL,NULL,1147,1290,NULL,'Free coupon code','Get a free coupon code!',NULL,NULL,NULL,'Just hit the Get Code button and view your code! Just hit the Get Code button and view your code! Just hit the Get Code button and view your code!',NULL,NULL,NULL,NULL,'2017-12-13 14:21:43',NULL,NULL,12,100,NULL,NULL,NULL,'Get your very own free coupon code now!',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10000,NULL,'2017-12-13 09:21:43',NULL,NULL,6,NULL,NULL,'running',0,NULL,'Get your free Code - Report',2411,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'Test Corporate','Here\'s your free coupon code!','2b9ef43ea92a1ae43cb9db029c5c3375.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'div#div-deal-background-image {\n			display: none !important\n			}\n\n			div.overlay {\n			display: none !important;\n			}\n\n			#company_logo{\n				border:0px solid red; \n				width:100%; \n				display:block;\n			}\n\n\n			.ui-btn-text{\n				color: #111;\n				width: 200px;\n				font-size: 10px;\n				margin-top: -19px;\n				margin-left: -10px;\n			}\n\n			#logoblock{\n				display:table-cell; \n				max-height:80px;\n				max-width:80px;\n				float:left; \n				vertical-align:middle;\n				background-repeat:no-repeat;\n				background-size:100%;\n				margin:8px;\n			}\n\n			#location{\n				display:table-cell; \n				height:100%;\n				width:170px;\n			}\n			/* Header */\n			.dealheader {\n				background-color: rgb(56, 97, 173);\n				color: white;\n			}\n			/* Use Now Button */\n			#btn_print_now {\n				border: 1px solid #145072;\n				color: white;\n				background: rgb(197, 73, 114);\n				background-image: -moz-linear-gradient(top, #4E89C5, #2567AB);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #5F9CC5), color-stop(1, #396B9E));\n				border-radius:10px;\n				margin-top:10px;\n			}\n\n			/* Use Now Button Hover*/\n			#btn_print_now:hover {\n				background: orange;\n			}\n\n			/* Use Now Button Text*/\n			#btn_print_now span {\n				padding: .6em 25px;\n				display: block;\n				height: 100%;\n				text-overflow: ellipsis;\n				overflow: hidden;\n				white-space: nowrap;\n				position: relative;\n			}\n\n			/* Terms Details Text */\n			p[name=\'p_instore_discount_instructions\'] {\n				font-size: 8px;\n			}\n\n			/* Terms Button */\n			.terms_button {\n				text-align: center;\n				border: 1px solid gray;\n				background: #FDFDFD;\n				border-radius:10px;\n				background-image: -moz-linear-gradient(top, #EEE, #FDFDFD);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #EEE), color-stop(1, #FDFDFD));}\n\n			.banner-row {background: rgb(154, 164, 237);}\n			.companyname {text-shadow: none;}\n			a.button.success,.button.success:active,.button.success:hover,.button.success:focus {background-color:rgb(197, 73, 114); color: white}\n			body {background:rgb(206, 209, 233);}\n			a.button.details,.button.details:active,.button.details:hover,.button.details:focus {background-color:rgb(197, 73, 114); color: white;}\n			.offerimage div {background-image: none !important;}\n			div.offerimage {background-image:url(\'/images/uploads/s3bucket/31b9477b2f8c4c7eec37a5e7b285dc07.jpg\')!important;background-position: center center;background-size:contain;background-repeat:no-repeat;height: 300px;}\n			a#change_email {color:#D60000;text-decoration:underline;}\n			div#loaded button#print {display:none !important}',NULL,NULL,'rgb(197, 73, 114)','white','rgb(197, 73, 114)','rgb(56, 97, 173)','white','rgb(154, 164, 237)','rgb(206, 209, 233)',NULL,0,'<div style=\"position:relative;top:100px;height:600px;\">\n	<img src=\"/images/uploads/s3bucket/960761819d4c27cd6fb5973b45963b42.jpg\" alt=\"\" style=\"z-index: -1\"/>\n	<h2 style=\"position: absolute; top: 235px; left: 10px; font-size: 25px; color: black; background-color:white;border: 1px solid black; padding: 5px; font-weight: normal;\">A882645-43</h2>\n</div>','<div style=\"position:relative;top:100px;height:600px;\">\n	<img src=\"http://uploads.coupsmart.com.s3.amazonaws.com/960761819d4c27cd6fb5973b45963b42.jpg\" alt=\"\" style=\"z-index: -1\"/>\n	<h2 style=\"position: absolute; top: 235px; left: 10px; font-size: 25px; color: black; background-color:white;border: 1px solid black; padding: 5px; font-weight: normal;\">A882645-43</h2>\n</div>',NULL,NULL,'A882645-43',NULL,NULL,NULL,NULL,NULL,'csc_email_template_with_claim_URL',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'alpha-numeric',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `total_methods` int(10) unsigned DEFAULT NULL,
  `allow_overage` bit(1) DEFAULT NULL,
  `logo_file_name` varchar(255) DEFAULT NULL,
  `logo_preview_file_name` varchar(255) DEFAULT NULL,
  `product_selling_options` enum('mine','other','','both') DEFAULT NULL,
  `other_business_options` enum('nolist','','list') DEFAULT NULL,
  `use_product_barcode` enum('Y','','N') DEFAULT NULL,
  `use_coupon_barcode` enum('Y','','N') DEFAULT NULL,
  `use_offer_code` enum('Y','','N') DEFAULT NULL,
  `use_share_bonus` enum('Y','','N') DEFAULT NULL,
  `campaign_email_80` tinyint(4) DEFAULT NULL,
  `campaign_email_90` tinyint(4) DEFAULT NULL,
  `campaign_email_full` tinyint(4) DEFAULT NULL,
  `auto_renew` tinyint(1) unsigned DEFAULT '0',
  `num_auto_renewals` int(10) DEFAULT '0',
  `num_renewals_performed` int(10) DEFAULT '0',
  `birthday_coupon_image` varchar(100) DEFAULT NULL,
  `birthday_coupon_text` varchar(255) DEFAULT NULL,
  `birthday_coupon_num_days_weeks_months` int(10) unsigned DEFAULT NULL,
  `birthday_coupon_day_week_month` enum('day','week','month') DEFAULT NULL,
  `disable_expire` tinyint(1) unsigned DEFAULT '0',
  `status` enum('pending','running','finished','stopped','paused','deleted') DEFAULT NULL,
  `stats_campaign` longtext,
  `stats_social` longtext,
  `stats_user` longtext,
  `voucher_layout_id` int(16) DEFAULT '1',
  `img_voucher_background` varchar(255) DEFAULT NULL,
  `email_layout_id` int(10) unsigned DEFAULT '1',
  `email_file` varchar(255) DEFAULT NULL,
  `convercial_type` enum('location','product') DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `canvas_feature` tinyint(1) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `sidebar_image` varchar(255) DEFAULT NULL,
  `turn_on_deal_when` enum('now','specific_time') DEFAULT NULL,
  `turn_off_deal_when` enum('runs_out','specific_time','auto_renew','never') DEFAULT NULL,
  `expire_when` enum('turns_off','specific_time','never') DEFAULT NULL,
  `use_deal_voucher_image` enum('yes_own','yes_fb_photo','yes_company_logo','no') DEFAULT NULL,
  `use_preview_deal_voucher_image` enum('yes_own','yes_fb_photo','yes_company_logo','no','voucher_img') DEFAULT NULL,
  `auto_post` tinyint(4) DEFAULT NULL,
  `post_text` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `require_like` tinyint(3) unsigned DEFAULT '0',
  `require_info` tinyint(1) DEFAULT '0',
  `require_share` tinyint(1) DEFAULT '0',
  `hide_share_button` tinyint(1) DEFAULT NULL,
  `img_fan_deals` varchar(255) DEFAULT NULL,
  `img_instore_deals` varchar(255) DEFAULT NULL,
  `img_sharing` varchar(255) DEFAULT NULL,
  `img_placeholder` varchar(255) DEFAULT NULL,
  `csc_report_recipients` text,
  `img_expired_used_up` varchar(255) DEFAULT NULL,
  `add_likebar` tinyint(1) DEFAULT NULL,
  `img_likebar` varchar(255) DEFAULT NULL,
  `likebar_content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2412 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
INSERT INTO `campaigns` VALUES (2394,'TestCorporate Deal 1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,0,'running',NULL,NULL,NULL,210,'f5501873a0e5a70399eee5f7cdf24605.jpg',1,NULL,NULL,1,NULL,'a4d624581253d06b4f1cc452d508888f.jpg',NULL,NULL,NULL,NULL,NULL,'yes_own',NULL,NULL,'2016-07-05 10:02:53',0,0,0,0,'a4d624581253d06b4f1cc452d508888f.jpg',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(2395,'TestCorporate Deal 1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,0,'running',NULL,NULL,NULL,209,'f5501873a0e5a70399eee5f7cdf24605.jpg',1,NULL,NULL,0,NULL,'04485f294238663f767d73dfffed084d.jpg',NULL,NULL,NULL,NULL,NULL,'yes_own',NULL,NULL,'2016-07-05 10:02:53',0,0,0,0,'04485f294238663f767d73dfffed084d.jpg',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(2411,'Get your free Code - Report',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,0,'running',NULL,NULL,NULL,1,'960761819d4c27cd6fb5973b45963b42.jpg',1,NULL,NULL,0,NULL,'31b9477b2f8c4c7eec37a5e7b285dc07.jpg',NULL,NULL,NULL,NULL,NULL,'yes_own',NULL,NULL,'2017-12-13 09:21:43',0,0,0,0,'31b9477b2f8c4c7eec37a5e7b285dc07.jpg',NULL,NULL,'5b0e118a3d7520cdd777a603ba921f63.jpg',NULL,NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-12-14 10:46:27
