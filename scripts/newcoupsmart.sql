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
-- Table structure for table `app_config`
--

DROP TABLE IF EXISTS `app_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_config` (
  `id` tinyint(1) NOT NULL DEFAULT '0',
  `email_queue_locked` int(1) DEFAULT '0',
  `last_locked` timestamp NULL DEFAULT NULL,
  `enable_fb_realtime_updates` tinyint(1) DEFAULT NULL,
  `smart_email_lindt_offset` bigint(30) DEFAULT '0',
  `short_url_domain` varchar(255) DEFAULT NULL,
  `srv_portal_script_last_updated` timestamp NULL DEFAULT NULL,
  `enable_sp_column_mapping` tinyint(1) DEFAULT '0',
  `tmp_html_content` longtext,
  `screw_up` tinyint(1) DEFAULT NULL,
  `screw_up_msg` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_config`
--

LOCK TABLES `app_config` WRITE;
/*!40000 ALTER TABLE `app_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocked_app_users`
--

DROP TABLE IF EXISTS `blocked_app_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_app_users` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `facebook_id` varchar(255) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `app_name` enum('fan_deals','sgs','instore','countmein','booking','web','convercial') DEFAULT NULL,
  `reason` text,
  `automatically_banned` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocked_app_users`
--

LOCK TABLES `blocked_app_users` WRITE;
/*!40000 ALTER TABLE `blocked_app_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_app_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_images`
--

DROP TABLE IF EXISTS `cache_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_images` (
  `id` bigint(30) unsigned NOT NULL AUTO_INCREMENT,
  `original_image` varchar(50) DEFAULT NULL,
  `width` int(10) DEFAULT NULL,
  `height` int(10) DEFAULT NULL,
  `resized_image` varchar(50) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_images`
--

LOCK TABLES `cache_images` WRITE;
/*!40000 ALTER TABLE `cache_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_resellers_stats`
--

DROP TABLE IF EXISTS `cache_resellers_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_resellers_stats` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `companies_id` int(10) unsigned DEFAULT NULL,
  `prints` int(10) unsigned DEFAULT '0',
  `views` int(10) unsigned DEFAULT '0',
  `redeemed` int(10) unsigned DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_resellers_stats`
--

LOCK TABLES `cache_resellers_stats` WRITE;
/*!40000 ALTER TABLE `cache_resellers_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_resellers_stats` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
INSERT INTO `campaigns` VALUES (2395,'TestCorporate Deal 1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,0,'running',NULL,NULL,NULL,209,'f5501873a0e5a70399eee5f7cdf24605.jpg',1,NULL,NULL,0,NULL,'04485f294238663f767d73dfffed084d.jpg',NULL,NULL,NULL,NULL,NULL,'yes_own',NULL,NULL,'2016-07-05 10:02:53',0,0,0,0,'04485f294238663f767d73dfffed084d.jpg',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns_locations`
--

DROP TABLE IF EXISTS `campaigns_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaigns_id` int(10) unsigned NOT NULL,
  `companies_id` int(10) unsigned NOT NULL,
  `locations_id` int(10) unsigned NOT NULL,
  `is_backup_deal` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns_locations`
--

LOCK TABLES `campaigns_locations` WRITE;
/*!40000 ALTER TABLE `campaigns_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaigns_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `claim_attempts`
--

DROP TABLE IF EXISTS `claim_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `claim_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `item_id` bigint(20) unsigned DEFAULT NULL,
  `sgs_uiid` varchar(12) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `claimed` tinyint(3) unsigned DEFAULT '0',
  `source` enum('facebook','coupsmart','referrer','shared','distributor') DEFAULT NULL,
  `facebook_id` varchar(50) DEFAULT NULL,
  `distributor_id` varchar(50) DEFAULT NULL,
  `shared_referral_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `claim_attempts`
--

LOCK TABLES `claim_attempts` WRITE;
/*!40000 ALTER TABLE `claim_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `claim_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `use_defender` tinyint(1) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `status` enum('pending','new','inactive','active') DEFAULT 'new',
  `priority` tinyint(1) unsigned DEFAULT '0',
  `demo` tinyint(1) unsigned DEFAULT '0',
  `display_name` varchar(150) DEFAULT NULL,
  `facebook_page_id` varchar(255) DEFAULT NULL,
  `initial_fan_count` int(10) DEFAULT NULL,
  `current_fan_count` int(10) DEFAULT NULL,
  `facebook_admin_email` text,
  `require_like` tinyint(4) DEFAULT '1',
  `subdomain` varchar(50) DEFAULT NULL,
  `img_no_coupons_exist` varchar(100) DEFAULT NULL,
  `mobile_placeholder_image` varchar(100) DEFAULT NULL,
  `likebar_image` varchar(100) DEFAULT NULL,
  `img_page_not_liked` varchar(100) DEFAULT NULL,
  `default_coupon_image` varchar(100) DEFAULT NULL,
  `fb_listing_css` text,
  `no_coupons_running` varchar(100) DEFAULT NULL,
  `open_sgs_store_when` enum('now','specific_time') DEFAULT 'now',
  `close_sgs_store_when` enum('never','specific_time') DEFAULT 'never',
  `sgs_open_date` datetime DEFAULT NULL,
  `sgs_close_date` datetime DEFAULT NULL,
  `sgs_header_img` varchar(255) DEFAULT NULL,
  `sgs_teaser_img` varchar(255) DEFAULT NULL,
  `sgs_closed_img` varchar(255) DEFAULT NULL,
  `sgs_out_of_stock_img` varchar(255) DEFAULT NULL,
  `sgs_email_header_img` varchar(255) DEFAULT NULL,
  `sgs_email_css` text,
  `sgs_css` text,
  `sgs_fee_rate` decimal(5,2) unsigned DEFAULT '10.00',
  `sgs_cc_fee_rate` decimal(5,2) unsigned DEFAULT '3.50',
  `convercial_css` text,
  `countmein_css` text,
  `countmein_title` varchar(255) DEFAULT NULL,
  `countmein_subtitle` varchar(255) DEFAULT NULL,
  `countmein_email_template` varchar(255) DEFAULT NULL,
  `countmein_email_subject` varchar(255) DEFAULT NULL,
  `countmein_redeemed_img` varchar(255) DEFAULT NULL,
  `countmein_expired_img` varchar(255) DEFAULT NULL,
  `countmein_denied_img` varchar(255) DEFAULT NULL,
  `location_finder_link` varchar(255) DEFAULT NULL,
  `max_synch_campaigns` int(11) DEFAULT '1',
  `base_price` decimal(10,2) DEFAULT '0.00',
  `setup_price` decimal(10,2) DEFAULT '249.00',
  `total_prints` int(11) DEFAULT '10',
  `cost_overage_prints` decimal(5,2) DEFAULT '0.26',
  `approved_overage` tinyint(4) DEFAULT NULL,
  `transaction_amount` decimal(10,2) unsigned DEFAULT NULL,
  `sent_email_trial_expiring` tinyint(4) DEFAULT NULL,
  `sent_email_80` tinyint(4) DEFAULT NULL,
  `sent_email_90` tinyint(4) DEFAULT NULL,
  `sent_email_full` tinyint(4) DEFAULT NULL,
  `sent_payment_email` tinyint(4) DEFAULT NULL,
  `affiliate_id` int(10) unsigned DEFAULT NULL,
  `trial_end_date` date DEFAULT NULL,
  `packages_id` int(11) DEFAULT '1',
  `locations` int(10) unsigned DEFAULT '1',
  `annual_pay` decimal(10,2) DEFAULT NULL,
  `platforms` int(10) unsigned DEFAULT '1',
  `profile_id` int(10) unsigned DEFAULT NULL,
  `notify_date` date DEFAULT NULL,
  `fan_page_auto_posting` tinyint(1) DEFAULT '1',
  `access_token` text,
  `app_access_token` text,
  `shown_payment_options` tinyint(1) DEFAULT '0',
  `stats_campaign` longtext,
  `stats_social` longtext,
  `stats_user` longtext,
  `stats_sgs` longtext,
  `coupcheck_css` longtext,
  `coupcheck_domain` text,
  `white_label` tinyint(1) DEFAULT '0',
  `pdf_graph_colors` text,
  `pdf_background` text,
  `tax_exempt_id` varchar(255) DEFAULT NULL,
  `tax_exempt_information` longtext,
  `sgs_show_out_of_stock_items` int(2) DEFAULT '1',
  `open_hotel_id` bigint(20) DEFAULT NULL,
  `open_hotel_username` varchar(255) DEFAULT NULL,
  `open_hotel_password` varchar(255) DEFAULT NULL,
  `booking_css` text,
  `unsubscribe_css` text,
  `magento_url` text,
  `magento_running` tinyint(1) NOT NULL DEFAULT '0',
  `grace_days` int(10) unsigned DEFAULT '7',
  `industry_id` int(11) DEFAULT NULL,
  `phone` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `discount_months` int(10) unsigned NOT NULL DEFAULT '1',
  `website` varchar(250) DEFAULT NULL,
  `sgs_giftshop_status` enum('closed','open') DEFAULT 'closed',
  `smart_deals_layout` enum('vertical','grid') DEFAULT 'vertical',
  `smart_deals_styled_buttons` tinyint(1) DEFAULT '1',
  `support_footer_content` longtext,
  `smart_emails_template` longtext,
  `show_fb_comments` tinyint(1) DEFAULT '0',
  `copyright` text,
  `receive_message` text,
  `analytics_report_recipients` text,
  `analytics_report_frequency` enum('daily','weekly','biweekly','monthly') DEFAULT 'daily',
  `sgs_report_recipients` text,
  `sgs_order_receipt_recipients` text,
  `source_tracking_report_recipients` text,
  `instore_background_img` varchar(255) DEFAULT NULL,
  `mo_header_caption` varchar(150) DEFAULT NULL,
  `notes` text,
  `description` text,
  `send_order_notification` tinyint(1) DEFAULT '1',
  `time_zone` varchar(50) DEFAULT NULL,
  `sgs_message_to_post` text,
  `sgs_cart_timeout` int(10) DEFAULT NULL,
  `sgs_terms` text,
  `sgs_allow_po_box_delivery` tinyint(1) DEFAULT '0',
  `sgs_currency` varchar(10) DEFAULT 'USD',
  `sgs_language` varchar(10) DEFAULT NULL,
  `enable_ftp_upload` tinyint(1) DEFAULT NULL,
  `ftp_hostname` varchar(100) DEFAULT NULL,
  `ftp_username` varchar(100) DEFAULT NULL,
  `ftp_password` varchar(100) DEFAULT NULL,
  `ftp_sub_folder` varchar(50) DEFAULT NULL,
  `ftp_is_ssl` tinyint(1) DEFAULT '0',
  `ftp_ssl_cert_file` varchar(100) DEFAULT NULL,
  `is_silverpop_company` tinyint(1) DEFAULT NULL,
  `sp_access_token` text,
  `sp_access_token_expire_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sp_endpoint` varchar(15) DEFAULT NULL,
  `sp_app_name` varchar(100) DEFAULT NULL,
  `sp_client_id` varchar(100) DEFAULT NULL,
  `sp_client_secret` varchar(200) DEFAULT NULL,
  `sp_refresh_token` varchar(200) DEFAULT NULL,
  `sp_username` varchar(50) DEFAULT NULL,
  `sp_password` varchar(100) DEFAULT NULL,
  `sp_api_host` varchar(100) DEFAULT NULL,
  `sp_list_id` int(20) DEFAULT NULL,
  `sp_contact_list_id` int(20) DEFAULT NULL,
  `sp_user_mapped_columns` text,
  `sp_contact_interests_id` int(20) DEFAULT NULL,
  `sp_contact_likes_id` int(20) DEFAULT NULL,
  `sp_contact_shares_id` int(20) DEFAULT NULL,
  `sp_contact_qr_codes_id` int(20) DEFAULT NULL,
  `sp_is_ubx` tinyint(1) DEFAULT NULL,
  `sp_ubx_auth_key` varchar(100) DEFAULT NULL,
  `sp_ubx_api_url` varchar(255) DEFAULT NULL,
  `sp_ubx_app_name` varchar(255) DEFAULT NULL,
  `sp_ubx_app_desc` text,
  `sdw_unique_code` varchar(36) DEFAULT NULL,
  `sdw_url_to_share` varchar(255) DEFAULT NULL,
  `send_email_after_printing` tinyint(1) DEFAULT '0',
  `srv_portal_script_last_updated` timestamp NULL DEFAULT NULL,
  `is_et_company` tinyint(1) DEFAULT NULL,
  `et_data_extension_claims` varchar(100) DEFAULT NULL,
  `et_subscriber_list_id` bigint(32) DEFAULT NULL,
  `et_data_extension_behaviours` varchar(100) DEFAULT NULL,
  `is_mailchimp_company` tinyint(1) DEFAULT '1',
  `mc_list_id` varchar(30) DEFAULT NULL,
  `mc_list_name` varchar(200) DEFAULT NULL,
  `mc_api_key` varchar(200) DEFAULT NULL,
  `is_campaign_monitor_company` tinyint(1) DEFAULT NULL,
  `cm_client_id` varchar(50) DEFAULT NULL,
  `cm_api_key` varchar(50) DEFAULT NULL,
  `sgs_fixed_shipping_cost` decimal(10,2) unsigned DEFAULT NULL,
  `sgs_fixed_country` varchar(10) DEFAULT NULL,
  `use_location_based_deals` tinyint(1) DEFAULT NULL,
  `loc_zipgate_norm` text,
  `loc_zipgate_error_nodeal` text,
  `loc_dmagate_norm` text,
  `loc_dmagate_error_nodma` text,
  `loc_dmagate_error_nodeal` text,
  `loc_zipgate_norm_mo` text,
  `loc_zipgate_error_nodeal_mo` text,
  `loc_dmagate_norm_mo` text,
  `loc_dmagate_error_nodma_mo` text,
  `loc_dmagate_error_nodeal_mo` text,
  `enable_user_blocking` tinyint(1) DEFAULT '0',
  `use_donation_based_deals` tinyint(1) DEFAULT NULL,
  `donation_min_val` decimal(10,2) DEFAULT NULL,
  `donation_email_subject` varchar(100) DEFAULT NULL,
  `donation_charity` varchar(200) DEFAULT NULL,
  `consultant_of` int(10) DEFAULT NULL,
  `is_corporate` tinyint(1) DEFAULT NULL,
  `webhook_url` text,
  `webhook_data_last_posted` timestamp NULL DEFAULT NULL,
  `load_testing` tinyint(1) DEFAULT NULL,
  `stripe_publishable_key` varchar(255) DEFAULT NULL,
  `stripe_secret_key` varchar(255) DEFAULT NULL,
  `self_service_type` enum('full','self') DEFAULT 'full',
  `enable_pixel_tracking` tinyint(1) DEFAULT '1',
  `auth_salt_value` varchar(255) DEFAULT NULL COMMENT 'If set to a non-empty value, this will require user authentication in order to be able to access SD, MO or SDW.',
  `license_start_date` date DEFAULT NULL,
  `license_grace_period` int(3) DEFAULT NULL,
  `license_period_months` enum('6','12') DEFAULT NULL,
  `account_type` enum('corporate','franchisee','reseller','resellerclient','demo') DEFAULT NULL,
  `dependent_dropdown` int(10) DEFAULT NULL,
  `license_currency` enum('USD','ZAR') DEFAULT NULL,
  `license_rate` decimal(12,2) DEFAULT NULL,
  `claim_credits` int(10) DEFAULT NULL,
  `service_package` enum('silver','gold') DEFAULT NULL,
  `service_package_rate` decimal(12,2) DEFAULT NULL,
  `service_package_day_of_month` int(2) DEFAULT NULL,
  `license_expire_date` date DEFAULT NULL,
  `accounting_contact` tinyint(1) DEFAULT NULL,
  `accounting_contact_name` varchar(100) DEFAULT NULL,
  `accounting_contact_email` varchar(100) DEFAULT NULL,
  `payment_method_type` enum('creditcard','invoice') DEFAULT NULL,
  `default_credit_card_id` int(6) DEFAULT NULL,
  `auto_process_credit_card` tinyint(1) DEFAULT NULL,
  `email_invoice_to` enum('me','another') DEFAULT NULL,
  `name_another_invoice` varchar(100) DEFAULT NULL,
  `email_another_invoice` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_demo` (`demo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1147,NULL,'2016-07-05 11:02:58','active',1,0,'Test Corporate',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'li.featured {\nwidth: 810px !important;\nheight: 250px !important;\n}\n\n#detailsmodal .coupon_image {\nheight: 205px !important;\n}\n\n/* CSS NEEDED FOR LIKEBAR */\ndiv.overlay {\ntop: 66px !important;\nheight: 85% !important;\ndisplay: none !important;\n}\n\n\n#topbar {\nmargin: 0 0 0 0 !important;\n}\n\n#coupon-container {\nwidth: 810px;\nheight: 100%;\n}\n\n#loading {\nwidth: 810px;\n}\n\ndiv#more-savings {\ndisplay: none !important;\n}',NULL,'now','never',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10.00,3.50,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0.00,249.00,10,0.26,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,1,NULL,NULL,1,'',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,0,7,NULL,'','',1,'','closed','vertical',1,NULL,NULL,0,NULL,NULL,NULL,'daily',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,'USD',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,'2016-08-11 15:51:24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'self',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_email_codes`
--

DROP TABLE IF EXISTS `customer_email_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_email_codes` (
  `id` bigint(33) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `item_id` int(32) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mailing_id` bigint(33) DEFAULT NULL,
  `unique_code` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_claimed` timestamp NULL DEFAULT NULL,
  `date_redeemed` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `type` enum('qr','upc','upc-a','upc-e','alpha-numeric') DEFAULT 'qr',
  `size` enum('medium','large','small','xsmall') DEFAULT 'medium',
  `mailing_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code_item_id` (`item_id`,`unique_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_email_codes`
--

LOCK TABLES `customer_email_codes` WRITE;
/*!40000 ALTER TABLE `customer_email_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_email_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_supplied_code`
--

DROP TABLE IF EXISTS `customer_supplied_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_supplied_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_code` varchar(255) NOT NULL,
  `issued_status` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `deal_id` int(11) DEFAULT NULL,
  `user_item_id` bigint(20) unsigned DEFAULT NULL,
  `date_printed` timestamp NULL DEFAULT NULL,
  `facebook_id` bigint(20) unsigned DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `index_deal_id` (`deal_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_supplied_code`
--

LOCK TABLES `customer_supplied_code` WRITE;
/*!40000 ALTER TABLE `customer_supplied_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_supplied_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deals`
--

DROP TABLE IF EXISTS `deals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `deal_name` varchar(255) NOT NULL,
  `cm_list_id` varchar(50) DEFAULT NULL,
  `cm_list_name` varchar(200) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','running','finished','stopped','paused','deleted') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deals`
--

LOCK TABLES `deals` WRITE;
/*!40000 ALTER TABLE `deals` DISABLE KEYS */;
INSERT INTO `deals` VALUES (1280,1147,'TestCorporate Deal 1',NULL,NULL,'2016-07-05 10:02:53',NULL);
/*!40000 ALTER TABLE `deals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` text,
  `days_to_send` int(10) DEFAULT NULL,
  `group` enum('free_active','free_inactive','paying','added_convercial','all','campaign_finished') DEFAULT NULL,
  `exempt` tinyint(1) DEFAULT '0',
  `reason_receiving_email` text,
  `defaults` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_templates`
--

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `error_log`
--

DROP TABLE IF EXISTS `error_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `error_log` (
  `id` bigint(33) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) DEFAULT NULL,
  `line_number` varchar(255) DEFAULT NULL,
  `var_name` varchar(255) DEFAULT NULL,
  `var_data` text,
  `description` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `error_log`
--

LOCK TABLES `error_log` WRITE;
/*!40000 ALTER TABLE `error_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `error_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fb_access_tokens`
--

DROP TABLE IF EXISTS `fb_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fb_access_tokens` (
  `id` bigint(30) unsigned NOT NULL AUTO_INCREMENT,
  `app_name` varchar(20) DEFAULT NULL,
  `app_id` varchar(100) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `permissions` text,
  `object_type` enum('user','company') DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `access_token` text,
  `expire_time` timestamp NULL DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fb_access_tokens`
--

LOCK TABLES `fb_access_tokens` WRITE;
/*!40000 ALTER TABLE `fb_access_tokens` DISABLE KEYS */;
INSERT INTO `fb_access_tokens` VALUES (7941,'convercial','171818396196950','524947534','email,user_birthday,user_friends,user_likes,user_location,user_relationships','user',459326,'EAACcRJZA4oFYBABwtgNUIZCHPotVsrGU8koWbZA1OiZBc4DCBEP3CMWznlj4ZCvrjZAPKgUiggDw2bslTnS17UvmuRdaeXBhWlNdPtvIG294d2Gr9tpkEP9ZAdHZCF6l7yBjFWK22BD9QMCQeSZAvXdRni0zlEgcWZB9QZD','2018-02-04 04:20:03','2017-12-04 12:11:33','2017-12-06 10:57:56');
/*!40000 ALTER TABLE `fb_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fb_likes`
--

DROP TABLE IF EXISTS `fb_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fb_likes` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `fb_id` bigint(20) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `like_count` int(15) DEFAULT '0',
  `item_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_fb_id` (`fb_id`),
  KEY `index_fb_likes` (`like_count`),
  KEY `index_category` (`category`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fb_likes`
--

LOCK TABLES `fb_likes` WRITE;
/*!40000 ALTER TABLE `fb_likes` DISABLE KEYS */;
INSERT INTO `fb_likes` VALUES (1355916,377654122397067,'','Quranic Reminders','2017-12-04 12:09:25',0,2395),(1355917,1411069552479292,'','Emco','2017-12-04 12:09:25',0,2395),(1355918,284639525600,'','Codility','2017-12-04 12:09:25',0,2395),(1355919,388655174641163,'','Free Quran Education','2017-12-04 12:09:25',0,2395),(1355920,616275045191283,'','Wild Darlings','2017-12-04 12:09:25',0,2395),(1355921,9131624063,'','Shutterfly','2017-12-04 12:09:25',0,2395),(1355922,1281575515216511,'','Peace Tv English','2017-12-04 12:09:25',0,2395),(1355923,228598547167475,'','Yasmin Mogahed','2017-12-04 12:09:25',0,2395),(1355924,650031875152853,'','Airplane','2017-12-04 12:09:25',0,2395),(1355925,328714893905778,'','Lindt','2017-12-04 12:09:25',0,2395),(1355926,61533584890,'','Productive Muslim','2017-12-04 12:09:25',0,2395),(1355927,722320304489125,'','Mufti Menk\'s reminders','2017-12-04 12:09:25',0,2395),(1355928,134230726685897,'','Phalcon Framework','2017-12-04 12:09:25',0,2395),(1355929,407180966044415,'','Mind Your Language - The Virtual Reunion','2017-12-04 12:09:25',0,2395),(1355930,280685912042335,'','Coupsmart Client Demo, The First','2017-12-04 12:09:25',0,2395),(1355931,113584042000230,'','Ten Apart Hotel','2017-12-04 12:09:25',0,2395),(1355932,5785888111,'','PADI','2017-12-04 12:09:25',0,2395),(1355933,1462514000646983,'','The Hungry Tummy','2017-12-04 12:09:25',0,2395),(1355934,371166336301427,'','Peace tv Urdu','2017-12-04 12:09:25',0,2395),(1355935,153848297996307,'','Abu Abdissalam','2017-12-04 12:09:25',0,2395),(1355936,207242941103,'','The Deen Show','2017-12-04 12:09:25',0,2395),(1355937,183182701889893,'','Haroon Qureshi','2017-12-04 12:09:25',0,2395),(1355938,115488351871810,'','The Sims Social','2017-12-04 12:09:25',0,2395),(1355939,104078649628422,'','Arabic','2017-12-04 12:09:25',0,2395),(1355940,208821641772,'','Unity','2017-12-04 12:09:25',0,2395),(1355941,178150122234830,'','Pathumwan Princess Hotel, Bangkok','2017-12-04 12:09:25',0,2395),(1355942,440516446042048,'','Coupsmart Client Demo, The Seventh','2017-12-04 12:09:25',0,2395),(1355943,1018656214825522,'','Cooking Fever','2017-12-04 12:09:25',0,2395),(1355944,1035007933199433,'','Dr. Babur & Associates','2017-12-04 12:09:25',0,2395),(1355945,440049246077861,'','Coupsmart Client Demo, The Sixth','2017-12-04 12:09:25',0,2395),(1355946,177479482288153,'','Al Burooj Medical Center','2017-12-04 12:09:25',0,2395),(1355947,465484170192228,'','Coupsmart Client Demo, The Eighth','2017-12-04 12:09:25',0,2395),(1355948,402205806628343,'','Peace House','2017-12-04 12:09:25',0,2395),(1355949,262812503876937,'','Photographers of Pakistan','2017-12-04 12:09:25',0,2395),(1355950,27214717206,'','Bayyinah','2017-12-04 12:09:25',0,2395),(1355951,181836051979249,'','Dr Zakir Naik','2017-12-04 12:09:25',0,2395),(1355952,388789491178687,'','Outstanding Muslim Parents, Inc.','2017-12-04 12:09:25',0,2395),(1355953,179324715436841,'','Happy Time Soda Company','2017-12-04 12:09:25',0,2395),(1355954,373835176075100,'','Granny\'s','2017-12-04 12:09:25',0,2395),(1355955,294129947386891,'','Islamic Parenting','2017-12-04 12:09:25',0,2395),(1355956,1052657861417389,'','Perdesi Express','2017-12-04 12:09:25',0,2395),(1355957,426799910743781,'','Zo\'s Sweetopia','2017-12-04 12:09:25',0,2395),(1355958,657474500930493,'','Wild Selections','2017-12-04 12:09:25',0,2395),(1355959,942105872468739,'','Pakistan Dawah Movement','2017-12-04 12:09:25',0,2395),(1355960,401076480009378,'','Learnarabiconline.com','2017-12-04 12:09:25',0,2395),(1355961,207940109253729,'','DOOM3','2017-12-04 12:09:25',0,2395),(1355962,132458570107873,'','Saad Tasleem','2017-12-04 12:09:25',0,2395),(1355963,424805904343004,'','Lampros Labs','2017-12-04 12:09:25',0,2395),(1355964,365237010300742,'','Khadija Kazi Ali Memorial High School','2017-12-04 12:09:25',0,2395),(1355965,463589100427031,'','Today\'s Mod','2017-12-04 12:09:25',0,2395),(1355966,135369639936403,'','Mariner\'s Village Resort','2017-12-04 12:09:25',0,2395),(1355967,228097472888,'','Islamic Online University','2017-12-04 12:09:25',0,2395),(1355968,263347690347646,'','Morning & evening Azkaar/supplications.','2017-12-04 12:09:25',0,2395),(1355969,421178164588486,'','C & CPP Programming','2017-12-04 12:09:25',0,2395),(1355970,223664513280,'','PHP Programmer','2017-12-04 12:09:25',0,2395),(1355971,909968889019626,'','Nouman Ali Khan Urdu','2017-12-04 12:09:25',0,2395),(1355972,883155701710373,'','Darul Arqam Studios','2017-12-04 12:09:25',0,2395),(1355973,1393363860894103,'','Just Peachie','2017-12-04 12:09:25',0,2395),(1355974,728399760530607,'','KLC Technology','2017-12-04 12:09:25',0,2395),(1355975,1476202695989229,'','FlexiQurbani','2017-12-04 12:09:25',0,2395),(1355976,625279687553560,'','Ingress Pakistan','2017-12-04 12:09:25',0,2395),(1355977,642398602460705,'','No to GMOs in Pakistan','2017-12-04 12:09:25',0,2395),(1355978,626167990751548,'','Valley Ranch Islamic Center','2017-12-04 12:09:25',0,2395),(1355979,605284766258972,'','GAZA in ALL Languages','2017-12-04 12:09:25',0,2395),(1355980,290519967724952,'','CoupSmart Client Demo, The Third','2017-12-04 12:09:25',0,2395),(1355981,339901979506866,'','Alburooj','2017-12-04 12:09:25',0,2395),(1355982,128762207148321,'','Quran Weekly','2017-12-04 12:09:25',0,2395),(1355983,118863654899331,'','Mohamed Zeyara','2017-12-04 12:09:25',0,2395),(1355984,133071576854296,'','Wael Ibrahim','2017-12-04 12:09:25',0,2395),(1355985,23919966078,'','Back To the Future','2017-12-04 12:09:25',0,2395),(1355986,7601848283,'','Indiana Jones','2017-12-04 12:09:25',0,2395),(1355987,84296203938,'','Half-Life 2','2017-12-04 12:09:25',0,2395),(1355988,776743242359880,'','Bristol Palace Banquets & Legendary Catering','2017-12-04 12:09:25',0,2395),(1355989,72792772943,'','Half-Life','2017-12-04 12:09:25',0,2395),(1355990,103632155147,'','Valve','2017-12-04 12:09:25',0,2395),(1355991,558579467549087,'','Lantana Foods','2017-12-04 12:09:25',0,2395),(1355992,349408568475529,'','Muslim Speakers','2017-12-04 12:09:25',0,2395),(1355993,303469146460899,'','Designers Wardrobe','2017-12-04 12:09:25',0,2395),(1355994,103956339643635,'','HAPPY MUSLIM FAMILY','2017-12-04 12:09:25',0,2395),(1355995,1426304847622698,'','روضة الافاق الجديدة','2017-12-04 12:09:25',0,2395),(1355996,248641545315765,'','Illumination Of Quraan','2017-12-04 12:09:25',0,2395),(1355997,186591423034,'','Dingo','2017-12-04 12:09:25',0,2395),(1355998,228945120550535,'','Spare Time Texas','2017-12-04 12:09:25',0,2395),(1355999,217852431596941,'','Tire Discounters','2017-12-04 12:09:25',0,2395),(1356000,139870006056973,'','IslamicTube','2017-12-04 12:09:25',0,2395),(1356001,532893600115756,'','TransTec','2017-12-04 12:09:25',0,2395),(1356002,143833268960901,'','3D computer graphics','2017-12-04 12:09:25',0,2395),(1356003,109711005714865,'','John Romero','2017-12-04 12:09:25',0,2395),(1356004,283424951770524,'','Muslim Central','2017-12-04 12:09:25',0,2395),(1356005,108440279180316,'','John D. Carmack','2017-12-04 12:09:25',0,2395),(1356006,330295590333026,'','OpenGL','2017-12-04 12:09:25',0,2395),(1356007,645147105522331,'','Justice for syria','2017-12-04 12:09:25',0,2395),(1356008,220600661434559,'','Beacon Light Academy','2017-12-04 12:09:25',0,2395),(1356009,219543788065670,'','Omar Suleiman','2017-12-04 12:09:25',0,2395),(1356010,19667888299,'','Yasir Qadhi','2017-12-04 12:09:25',0,2395),(1356011,161022577382527,'','Youth Talk','2017-12-04 12:09:25',0,2395),(1356012,377824368949711,'','Florida Fashion Week','2017-12-04 12:09:25',0,2395),(1356013,301652076603591,'','Coupsmart Client Demo, The Fourth','2017-12-04 12:09:25',0,2395),(1356014,434141020173,'','Shaykh Abdullah Hakim Quick','2017-12-04 12:09:25',0,2395),(1356015,165025270191774,'','Sears Carpet Cleaning & Air Duct Cleaning','2017-12-04 12:09:25',0,2395),(1356016,96112593327,'','C/C++ Programming Language','2017-12-04 12:09:25',0,2395),(1356017,260669114058638,'','3D graphics programming','2017-12-04 12:09:25',0,2395),(1356018,112006705481887,'','OpenGL','2017-12-04 12:09:25',0,2395),(1356019,107806402575300,'','Game programming','2017-12-04 12:09:25',0,2395),(1356020,108056209216783,'','Assembly language','2017-12-04 12:09:25',0,2395),(1356021,323467368576,'','Ghirardelli Chocolate Company','2017-12-04 12:09:25',0,2395),(1356022,204650212883479,'','Sheikh Ahmed Deedat','2017-12-04 12:09:25',0,2395),(1356023,134134356682978,'','Sheikh Abdurraheem Green','2017-12-04 12:09:25',0,2395),(1356024,44778876691,'','Zain Bhikha','2017-12-04 12:09:25',0,2395),(1356025,267860699936929,'','IQRAdTRUTH','2017-12-04 12:09:25',0,2395),(1356026,122705147806615,'','Islam For Kids','2017-12-04 12:09:25',0,2395),(1356027,144606842284439,'','Yusuf Estes','2017-12-04 12:09:25',0,2395),(1356028,228528829088,'','Dr. Bilal Philips','2017-12-04 12:09:25',0,2395),(1356029,123308931071795,'','Nour Academy - Teaching Arabic and Quraan to Non-Arabic Speaking Muslims','2017-12-04 12:09:25',0,2395),(1356030,198954490635,'','DJ KAZI','2017-12-04 12:09:25',0,2395),(1356031,105531406147141,'','AvoDerm Natural Pet Foods','2017-12-04 12:09:25',0,2395),(1356032,477320599010373,'','Sparky Kids','2017-12-04 12:09:25',0,2395),(1356033,452194698166656,'','Hotel De Bishop','2017-12-04 12:09:25',0,2395),(1356034,126400034058038,'','MELT Organic','2017-12-04 12:09:25',0,2395),(1356035,151699318332634,'','Coupsmart Client Demo, The Ninth','2017-12-04 12:09:25',0,2395),(1356036,185523868247030,'','Nouman Ali Khan','2017-12-04 12:09:25',0,2395),(1356037,59816241970,'','Mufti Ismail Menk','2017-12-04 12:09:25',0,2395),(1356038,42018925791,'','Stride Rite','2017-12-04 12:09:25',0,2395),(1356039,156479261161924,'','CoupSmart Client Demo, The Second','2017-12-04 12:09:25',0,2395),(1356040,129079313911235,'','Criminal Case','2017-12-04 12:09:25',0,2395),(1356041,100866456625312,'','Lexington Hotel & Conference Center - Jacksonville Riverwalk','2017-12-04 12:09:25',0,2395),(1356042,190365107721132,'','Holiday Inn Express - Juno Beach/North Palm Beach','2017-12-04 12:09:25',0,2395),(1356043,101283183252635,'','Days Hotel Egg Harbor Township','2017-12-04 12:09:25',0,2395),(1356044,153043971384512,'','Holiday Inn Newark Airport','2017-12-04 12:09:25',0,2395),(1356045,120848314601013,'','Days Inn Harrisburg North','2017-12-04 12:09:25',0,2395),(1356046,167296289989498,'','Quran Explorer','2017-12-04 12:09:25',0,2395),(1356047,126698214009979,'','Sheikh Tauseef ur rehman(TRUE ISLAM)','2017-12-04 12:09:25',0,2395),(1356048,107947649233689,'','Bilal Philips','2017-12-04 12:09:25',0,2395),(1356049,110186325659396,'','Abdurraheem Green','2017-12-04 12:09:25',0,2395),(1356050,111257452230362,'','Yusuf Chambers','2017-12-04 12:09:25',0,2395),(1356051,404499476228367,'','Islamic Research Foundation','2017-12-04 12:09:25',0,2395),(1356052,96958028188,'','Mead','2017-12-04 12:09:25',0,2395),(1356053,9209579703,'','Yusuf Estes','2017-12-04 12:09:25',0,2395),(1356054,170709640375,'','Ahmed Bukhatir - أحمد بوخاطر','2017-12-04 12:09:25',0,2395),(1356055,373712003255,'','Shujauddin Sheikh','2017-12-04 12:09:25',0,2395),(1356056,50391186019,'','Prof.Dr. Atta ur Rahman','2017-12-04 12:09:25',0,2395),(1356057,136458526428291,'','Pakistan Armed Forces Under Attack of Conspiracy','2017-12-04 12:09:25',0,2395),(1356058,177870388923788,'','Bots B Us','2017-12-04 12:09:25',0,2395),(1356059,23009582266,'','Dr. Israr Ahmad','2017-12-04 12:09:25',0,2395),(1356060,110485178971978,'','Arabic','2017-12-04 12:09:25',0,2395),(1356061,130632613675863,'','Spicy Monkey Clothing','2017-12-04 12:09:25',0,2395),(1356062,151298688319140,'','Alumeed Rehabilitation Association for Cerebal Palsy','2017-12-04 12:09:25',0,2395),(1356063,506829562667293,'','Sweet F.R.O.G (DEMO)','2017-12-04 12:09:25',0,2395),(1356064,224233197595808,'','Gramp\'s Grocers','2017-12-04 12:09:25',0,2395),(1356065,161718533963957,'','Pepsi Next Demo','2017-12-04 12:09:25',0,2395),(1356066,455004127867635,'','Propel Fitness Water (demo)','2017-12-04 12:09:25',0,2395),(1356067,177012332362501,'','Design Molvi','2017-12-04 12:09:25',0,2395),(1356068,145280508872637,'','Busken Bakery','2017-12-04 12:09:25',0,2395),(1356069,124159324315594,'','yousuf estes','2017-12-04 12:09:25',0,2395),(1356070,112682218746818,'','Michael Abrash','2017-12-04 12:09:25',0,2395),(1356071,111213574427,'','SpringHill Suites by Marriott Vero Beach','2017-12-04 12:09:25',0,2395),(1356072,122796661112082,'','Magnolia Hotels','2017-12-04 12:09:25',0,2395),(1356073,122099002176,'','Hyatt Place Blacksburg / University','2017-12-04 12:09:25',0,2395),(1356074,318572324862813,'','Holiday Inn Dallas Market Center','2017-12-04 12:09:25',0,2395),(1356075,343172054518,'','Park Inn by Radisson Beaver Falls, PA','2017-12-04 12:09:25',0,2395),(1356076,156506151040801,'','Ramada Fort Lauderdale Airport/Cruise Port','2017-12-04 12:09:25',0,2395),(1356077,124005460975076,'','Hampton Inn Okeechobee','2017-12-04 12:09:25',0,2395),(1356078,453098445092,'','Treasure Bay Resort & Marina','2017-12-04 12:09:25',0,2395),(1356079,181231925289755,'','Highlands Inn & Conference Center-Sebring','2017-12-04 12:09:25',0,2395),(1356080,233604595279,'','Wyndham Garden Hotel Duluth','2017-12-04 12:09:25',0,2395),(1356081,72669560198,'','Lakeside Inn - Wakefield/Boston Metro','2017-12-04 12:09:25',0,2395),(1356082,140679009365613,'','Brookshire Suites Inner Harbor Hotel','2017-12-04 12:09:25',0,2395),(1356083,136036049834743,'','Book Nerd','2017-12-04 12:09:25',0,2395),(1356084,242314195778846,'','Adam\'s Awesome Apples','2017-12-04 12:09:25',0,2395),(1356085,141570949236385,'','Digissance','2017-12-04 12:09:25',0,2395),(1356086,187447351290396,'','Napa Auto Parts Cincinnati','2017-12-04 12:09:25',0,2395),(1356087,134444489959880,'','Nailtique','2017-12-04 12:09:25',0,2395),(1356088,162177176598,'','SmartMouth','2017-12-04 12:09:25',0,2395),(1356089,250485181659872,'','Venue Magazine','2017-12-04 12:09:25',0,2395),(1356090,113489671998014,'','Mind Your Language','2017-12-04 12:09:25',0,2395),(1356091,108546492510312,'','Caesar 4','2017-12-04 12:09:25',0,2395),(1356092,85644967685,'','Doom 4','2017-12-04 12:09:25',0,2395),(1356093,109389585749978,'','Terminator 2 Theme','2017-12-04 12:09:25',0,2395),(1356094,102537169818659,'','Padaria Arte & Pão','2017-12-04 12:09:25',0,2395),(1356095,81032658156,'','The Public House','2017-12-04 12:09:25',0,2395),(1356096,279972722631,'','Hofbrauhaus Newport','2017-12-04 12:09:25',0,2395),(1356097,221701994564161,'','Mathnasium Waialae','2017-12-04 12:09:25',0,2395),(1356098,169949339748979,'','Die Hard','2017-12-04 12:09:25',0,2395),(1356099,144388918981474,'','Facebook for Websites','2017-12-04 12:09:25',0,2395),(1356100,112145245463728,'','Broken Arrow','2017-12-04 12:09:25',0,2395),(1356101,103129676393728,'','Ankahi','2017-12-04 12:09:25',0,2395),(1356102,178283982223290,'','Cutting Edge 3D Game Programming with C Plus Plus','2017-12-04 12:09:25',0,2395),(1356103,108271955867852,'','Die Hard','2017-12-04 12:09:25',0,2395),(1356104,124147434325361,'','Mio\'s Hyde Park','2017-12-04 12:09:25',0,2395),(1356105,193637063994029,'','Lee\'s Famous Recipe Chicken - Cincinnati','2017-12-04 12:09:25',0,2395),(1356106,286005100658,'','MadinahArabic.com - Free Arabic Learning Website and 1-to-1 Tuition','2017-12-04 12:09:25',0,2395);
/*!40000 ALTER TABLE `fb_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fb_locations_dma_regions`
--

DROP TABLE IF EXISTS `fb_locations_dma_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fb_locations_dma_regions` (
  `fb_location_id` varchar(50) DEFAULT NULL,
  `city_dma_region_id` int(10) DEFAULT NULL,
  `session_id` varchar(20) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `fb_location_id` (`fb_location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fb_locations_dma_regions`
--

LOCK TABLES `fb_locations_dma_regions` WRITE;
/*!40000 ALTER TABLE `fb_locations_dma_regions` DISABLE KEYS */;
/*!40000 ALTER TABLE `fb_locations_dma_regions` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (2395,NULL,NULL,1147,1280,NULL,'TestCorporate Deal 1','TestCorporate Deal 1',NULL,NULL,NULL,'TestCorporate Deal 1 - Details. TestCorporate Deal 1 - Details. TestCorporate Deal 1 - Details. ',NULL,NULL,NULL,NULL,'2016-07-05 15:00:00',NULL,NULL,6,100000,NULL,NULL,NULL,'TestCorporate Deal 1 - Subheading',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10000,NULL,'2016-07-05 10:02:53',NULL,NULL,0,NULL,NULL,'running',0,NULL,'TestCorporate Deal 1',2395,33,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'div#div-deal-background-image {\n			display: none !important\n			}\n\n			div.overlay {\n			display: none !important;\n			}\n\n			#company_logo{\n				border:0px solid red; \n				width:100%; \n				display:block;\n			}\n\n\n			.ui-btn-text{\n				color: #111;\n				width: 200px;\n				font-size: 10px;\n				margin-top: -19px;\n				margin-left: -10px;\n			}\n\n			#logoblock{\n				display:table-cell; \n				max-height:80px;\n				max-width:80px;\n				float:left; \n				vertical-align:middle;\n				background-repeat:no-repeat;\n				background-size:100%;\n				margin:8px;\n			}\n\n			#location{\n				display:table-cell; \n				height:100%;\n				width:170px;\n			}\n			/* Header */\n			.dealheader {\n				background-color: rgb(153, 138, 205);\n				color: white;\n			}\n			/* Use Now Button */\n			#btn_print_now {\n				border: 1px solid #145072;\n				color: white;\n				background: rgb(199, 199, 32);\n				background-image: -moz-linear-gradient(top, #4E89C5, #2567AB);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #5F9CC5), color-stop(1, #396B9E));\n				border-radius:10px;\n				margin-top:10px;\n			}\n\n			/* Use Now Button Hover*/\n			#btn_print_now:hover {\n				background: orange;\n			}\n\n			/* Use Now Button Text*/\n			#btn_print_now span {\n				padding: .6em 25px;\n				display: block;\n				height: 100%;\n				text-overflow: ellipsis;\n				overflow: hidden;\n				white-space: nowrap;\n				position: relative;\n			}\n\n			/* Terms Details Text */\n			p[name=\'p_instore_discount_instructions\'] {\n				font-size: 8px;\n			}\n\n			/* Terms Button */\n			.terms_button {\n				text-align: center;\n				border: 1px solid gray;\n				background: #FDFDFD;\n				border-radius:10px;\n				background-image: -moz-linear-gradient(top, #EEE, #FDFDFD);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #EEE), color-stop(1, #FDFDFD));}\n\n			.banner-row {background: rgb(209, 209, 129);}\n			.companyname {text-shadow: none;}\n			a.button.success,.button.success:active,.button.success:hover,.button.success:focus {background-color:rgb(199, 199, 32); color: white}\n			body {background:rgb(241, 241, 205);}\n			a.button.details,.button.details:active,.button.details:hover,.button.details:focus {background-color:rgb(48, 191, 57); color: white;}\n			.offerimage div {background-image: none !important;}\n			div.offerimage {background-image:url(\'/images/uploads/s3bucket/04485f294238663f767d73dfffed084d.jpg\')!important;background-position: center center;background-size:contain;background-repeat:no-repeat;height: 300px;}\n			a#change_email {color:#D60000;text-decoration:underline;}\n			div#loaded button#print {display:none !important}',NULL,NULL,'rgb(199, 199, 32)','white','rgb(48, 191, 57)','rgb(153, 138, 205)','white','rgb(209, 209, 129)','rgb(241, 241, 205)',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'alpha-numeric',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items_views`
--

DROP TABLE IF EXISTS `items_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items_views` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `items_id` int(10) unsigned NOT NULL,
  `distributors_id` int(11) unsigned DEFAULT NULL,
  `user_id` bigint(10) unsigned DEFAULT NULL,
  `user_agent` varchar(200) DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_click_referral` tinyint(1) DEFAULT '0',
  `smart_link_id` int(10) DEFAULT NULL,
  `instore_email_code` varchar(36) DEFAULT NULL,
  `silver_pop_click_id` bigint(30) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `shortened_url_hit_id` bigint(20) DEFAULT NULL,
  `print_clicked` tinyint(1) DEFAULT NULL,
  `email_for_later_print_now` enum('email_for_later','print_now') DEFAULT NULL,
  `proceeded_with_print_email` tinyint(1) DEFAULT NULL,
  `share_clicked` tinyint(1) DEFAULT NULL,
  `permissions_rejected` tinyint(1) DEFAULT NULL,
  `share_permissions_rejected` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_items_id` (`items_id`),
  KEY `index_created` (`created`),
  KEY `index_company_id` (`company_id`),
  KEY `index_user_id` (`user_id`),
  KEY `index_shortened_url_hit_id` (`shortened_url_hit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items_views`
--

LOCK TABLES `items_views` WRITE;
/*!40000 ALTER TABLE `items_views` DISABLE KEYS */;
INSERT INTO `items_views` VALUES (1837218,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','sklv3kkb6hf06bc5rpj5hn48o3','2017-12-04 12:09:10',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL),(1837219,2395,NULL,459326,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','sklv3kkb6hf06bc5rpj5hn48o3','2017-12-04 12:11:25',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL),(1837220,2395,NULL,459326,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','sklv3kkb6hf06bc5rpj5hn48o3','2017-12-04 12:25:02',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL),(1837221,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','cddalkl8k909shd67mqo2dcqqv','2017-12-05 13:43:56',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837222,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','f40ivbadnr9klp9gefa24o2g1i','2017-12-06 04:18:17',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837223,2395,NULL,459326,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 04:19:59',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL),(1837224,2395,NULL,459326,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 04:25:03',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL),(1837225,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:22:18',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837226,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:24:33',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837227,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:27:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837228,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:27:01',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837229,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:27:23',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837230,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:28:38',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837231,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:28:59',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837232,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:33:46',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837233,2395,NULL,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:34:37',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1837234,2395,NULL,459326,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:35:22',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL),(1837235,2395,NULL,459326,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 07:38:07',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL),(1837236,2395,NULL,459326,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',1147,'127.0.0.1','hr1faqelq6m29f3d3ji7nbfg2l','2017-12-06 10:57:48',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `items_views` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`coupsmart`@`%`*/ /*!50003 TRIGGER AddViewCount AFTER INSERT ON items_views
  FOR EACH ROW BEGIN
    UPDATE items SET items.view_count = items.view_count + 1 WHERE NEW.items_id = items.id;
  END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `location_specific_vouchers`
--

DROP TABLE IF EXISTS `location_specific_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location_specific_vouchers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `bg_img` varchar(100) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location_specific_vouchers`
--

LOCK TABLES `location_specific_vouchers` WRITE;
/*!40000 ALTER TABLE `location_specific_vouchers` DISABLE KEYS */;
/*!40000 ALTER TABLE `location_specific_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `companies_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address1` varchar(150) DEFAULT NULL,
  `address2` varchar(150) DEFAULT NULL,
  `city` varchar(75) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `country` char(2) DEFAULT NULL,
  `address_lat` decimal(10,6) DEFAULT NULL,
  `address_lon` decimal(10,6) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  `can_spam_address` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suspicious_user_activity`
--

DROP TABLE IF EXISTS `suspicious_user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suspicious_user_activity` (
  `id` bigint(32) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(10) DEFAULT NULL,
  `reason` enum('no_friends','fraudulent_activity') DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suspicious_user_activity`
--

LOCK TABLES `suspicious_user_activity` WRITE;
/*!40000 ALTER TABLE `suspicious_user_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `suspicious_user_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_fb_likes`
--

DROP TABLE IF EXISTS `user_fb_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_fb_likes` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `fb_like_id` int(16) unsigned DEFAULT NULL,
  `fb_id` bigint(20) DEFAULT NULL,
  `fb_category` varchar(255) DEFAULT NULL,
  `fb_like` varchar(255) DEFAULT NULL,
  `user_id` int(16) unsigned DEFAULT NULL,
  `date_first_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_last_seen` datetime DEFAULT NULL,
  `date_removed` datetime DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_user_id` (`user_id`),
  KEY `index_fb_like_id` (`fb_like_id`),
  KEY `index_item_id` (`item_id`),
  KEY `index_fb_id` (`fb_id`),
  KEY `index_fb_category` (`fb_category`),
  KEY `index_fb_like` (`fb_like`),
  KEY `index_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_fb_likes`
--

LOCK TABLES `user_fb_likes` WRITE;
/*!40000 ALTER TABLE `user_fb_likes` DISABLE KEYS */;
INSERT INTO `user_fb_likes` VALUES (8172037,1355916,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172038,1355917,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172039,1355918,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172040,1355919,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172041,1355920,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172042,1355921,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172043,1355922,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172044,1355923,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172045,1355924,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172046,1355925,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172047,1355926,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172048,1355927,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172049,1355928,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172050,1355929,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172051,1355930,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172052,1355931,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172053,1355932,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172054,1355933,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172055,1355934,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172056,1355935,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172057,1355936,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172058,1355937,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172059,1355938,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172060,1355939,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172061,1355940,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172062,1355941,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172063,1355942,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172064,1355943,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172065,1355944,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172066,1355945,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172067,1355946,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172068,1355947,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172069,1355948,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172070,1355949,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172071,1355950,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172072,1355951,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172073,1355952,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172074,1355953,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172075,1355954,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172076,1355955,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172077,1355956,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172078,1355957,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172079,1355958,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172080,1355959,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172081,1355960,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172082,1355961,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172083,1355962,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172084,1355963,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172085,1355964,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172086,1355965,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172087,1355966,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172088,1355967,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172089,1355968,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172090,1355969,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172091,1355970,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172092,1355971,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172093,1355972,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172094,1355973,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172095,1355974,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172096,1355975,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172097,1355976,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172098,1355977,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172099,1355978,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172100,1355979,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172101,1355980,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172102,1355981,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172103,1355982,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172104,1355983,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172105,1355984,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172106,1355985,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172107,1355986,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172108,1355987,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172109,1355988,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172110,1355989,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172111,1355990,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172112,1355991,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172113,1355992,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172114,1355993,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172115,1355994,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172116,1355995,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172117,1355996,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172118,1355997,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172119,1355998,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172120,1355999,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172121,1356000,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172122,1356001,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172123,1356002,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172124,1356003,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172125,1356004,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172126,1356005,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172127,1356006,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172128,1356007,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172129,1356008,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172130,1356009,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172131,1356010,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172132,1356011,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172133,1356012,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172134,1356013,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172135,1356014,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172136,1356015,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172137,1356016,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172138,1356017,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172139,1356018,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172140,1356019,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172141,1356020,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172142,1356021,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172143,1356022,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172144,1356023,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172145,1356024,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172146,1356025,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172147,1356026,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172148,1356027,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172149,1356028,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172150,1356029,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172151,1356030,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172152,1356031,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172153,1356032,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172154,1356033,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172155,1356034,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172156,1356035,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172157,1356036,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172158,1356037,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172159,1356038,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172160,1356039,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172161,1356040,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172162,1356041,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172163,1356042,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172164,1356043,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172165,1356044,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172166,1356045,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172167,1356046,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172168,1356047,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172169,1356048,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172170,1356049,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172171,1356050,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172172,1356051,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172173,1356052,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172174,1356053,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172175,1356054,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172176,1356055,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172177,1356056,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172178,1356057,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172179,1356058,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172180,1356059,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172181,1356060,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172182,1356061,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172183,1356062,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172184,1356063,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172185,1356064,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172186,1356065,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172187,1356066,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172188,1356067,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172189,1356068,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172190,1356069,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172191,1356070,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172192,1356071,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172193,1356072,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172194,1356073,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172195,1356074,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172196,1356075,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172197,1356076,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172198,1356077,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172199,1356078,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172200,1356079,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172201,1356080,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172202,1356081,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172203,1356082,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172204,1356083,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172205,1356084,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172206,1356085,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172207,1356086,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172208,1356087,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172209,1356088,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172210,1356089,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172211,1356090,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172212,1356091,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172213,1356092,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172214,1356093,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172215,1356094,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172216,1356095,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172217,1356096,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172218,1356097,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172219,1356098,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172220,1356099,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172221,1356100,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172222,1356101,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172223,1356102,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172224,1356103,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172225,1356104,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172226,1356105,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147),(8172227,1356106,NULL,NULL,NULL,459326,'2017-12-04 12:09:25','2017-12-06 15:57:55',NULL,2395,1147);
/*!40000 ALTER TABLE `user_fb_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_items`
--

DROP TABLE IF EXISTS `user_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_items` (
  `id` bigint(32) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `uiid` varchar(255) DEFAULT NULL,
  `is_regular_coupon` tinyint(1) DEFAULT NULL,
  `date_committed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivery_center_arrival` datetime DEFAULT NULL,
  `date_sent` datetime DEFAULT NULL,
  `expected_delivery_date` datetime DEFAULT NULL,
  `date_claimed` datetime DEFAULT NULL,
  `date_redeemed` datetime DEFAULT NULL,
  `distributors_id` int(10) unsigned DEFAULT NULL,
  `coupchecked` tinyint(1) unsigned DEFAULT '0',
  `num_scans` int(10) unsigned DEFAULT '0',
  `walk_in_status` tinyint(1) DEFAULT NULL,
  `new_fan_first_flag` tinyint(1) DEFAULT '0',
  `walkin_device_id` int(10) unsigned DEFAULT NULL,
  `walkin_location_id` int(10) DEFAULT NULL,
  `is_click_referral` tinyint(1) DEFAULT '0',
  `reprinted` tinyint(1) DEFAULT '0',
  `has_hit_magento_website` tinyint(1) DEFAULT NULL,
  `allow_reprint` tinyint(1) DEFAULT NULL,
  `reprint_code` varchar(36) DEFAULT NULL,
  `reprint_url_sent` tinyint(1) DEFAULT '0',
  `distribution_method` int(3) DEFAULT NULL,
  `smart_link_id` int(10) DEFAULT NULL,
  `items_views_id` bigint(30) DEFAULT NULL,
  `silver_pop_click_id` bigint(30) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `is_new_user_claim` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uiid` (`uiid`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`),
  KEY `index_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_items`
--

LOCK TABLES `user_items` WRITE;
/*!40000 ALTER TABLE `user_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prev_id` int(11) unsigned DEFAULT NULL,
  `facebook_id` bigint(20) DEFAULT NULL,
  `fb_app_scoped_user_id` bigint(32) DEFAULT NULL,
  `twitter_id` bigint(20) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `gender` char(1) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `age_group` smallint(5) unsigned DEFAULT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `state` char(2) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `address_lat` float(10,6) DEFAULT NULL,
  `address_lon` float(10,6) DEFAULT NULL,
  `address_last_verified` timestamp NULL,
  `delivery_status` enum('unverified','autoverified','verification failed','corrections requested','user verified','delivery failed') NOT NULL DEFAULT 'unverified',
  `email_notify` tinyint(1) unsigned DEFAULT '0',
  `sms_notify` tinyint(1) unsigned DEFAULT '0',
  `push_notify` tinyint(1) unsigned DEFAULT '0',
  `send_replacement_items` tinyint(1) unsigned DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  `time_allotted` int(10) unsigned DEFAULT NULL,
  `facebook_location_id` varchar(50) DEFAULT NULL,
  `facebook_location_name` varchar(50) DEFAULT NULL,
  `relationship_status` varchar(255) DEFAULT '',
  `fb_friend_count` int(11) DEFAULT NULL,
  `fb_interests` longtext,
  `fb_likes` longtext,
  `status` enum('new','active','inactive','cancelled','rejected','suspended','suspicious') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `customers_id` bigint(20) DEFAULT NULL,
  `reseller_id` int(10) unsigned DEFAULT NULL,
  `unsubscribed` tinyint(1) DEFAULT '0',
  `exempt_fanalytics` tinyint(1) DEFAULT '0',
  `agreed_to_terms` tinyint(1) DEFAULT '0',
  `capi_key` varchar(16) DEFAULT NULL,
  `capi_secret` varchar(32) DEFAULT NULL,
  `magento_api_key` varchar(16) DEFAULT NULL,
  `magento_api_secret` varchar(32) DEFAULT NULL,
  `tester_status` tinyint(4) DEFAULT '0',
  `sp_recipient_id` bigint(32) DEFAULT NULL,
  `et_subscriber_id` bigint(32) DEFAULT NULL,
  `is_test_account` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `email_idx` (`email`) USING HASH,
  KEY `facebook_idx` (`facebook_id`) USING HASH,
  KEY `index_facebook_location_name` (`facebook_location_name`),
  KEY `index_date_of_birth` (`date_of_birth`),
  KEY `index_facebook_location_id` (`facebook_location_id`),
  KEY `index_gender` (`gender`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (459326,NULL,524947534,NULL,NULL,'sameeullahqazi','sameeullahqazi@yahoo.com','564aadf9e2a9c2a74d4a1f4acea5f340','Samee','Qazi','M','1978-03-04',NULL,'',NULL,'','','',NULL,NULL,'2017-12-04 12:09:23','unverified',0,0,0,NULL,'2017-12-04 12:09:23','2017-12-04 17:09:23',NULL,'110713778953693','Karachi, Pakistan','Married',121,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,0,NULL,NULL,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_companies`
--

DROP TABLE IF EXISTS `users_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_companies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` bigint(20) unsigned DEFAULT NULL,
  `companies_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_companies`
--

LOCK TABLES `users_companies` WRITE;
/*!40000 ALTER TABLE `users_companies` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_companies_campaigns`
--

DROP TABLE IF EXISTS `users_companies_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_companies_campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` bigint(20) unsigned DEFAULT NULL,
  `companies_id` int(10) unsigned NOT NULL,
  `campaigns_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_companies_campaigns`
--

LOCK TABLES `users_companies_campaigns` WRITE;
/*!40000 ALTER TABLE `users_companies_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_companies_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voucher_layout_parts`
--

DROP TABLE IF EXISTS `voucher_layout_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher_layout_parts` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `voucher_layout_id` int(16) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` enum('text1','text2','text3','expiry','barcode','coupcheck_barcode','user_image','user_name','image','background','coupcheck_logo','cashier_code','sgs_item_option_0','sgs_item_option_1','manufacturer_caption','expiry_caption') DEFAULT NULL,
  `x` int(16) unsigned DEFAULT '0',
  `y` int(16) unsigned DEFAULT '0',
  `width` int(16) unsigned DEFAULT '500',
  `height` int(16) unsigned DEFAULT '200',
  `layer` int(16) unsigned DEFAULT '0',
  `bg_color` varchar(16) DEFAULT NULL,
  `opacity` decimal(10,2) DEFAULT NULL,
  `style` text,
  `default_content` varchar(255) DEFAULT NULL,
  `is_dynamic` tinyint(1) DEFAULT '0',
  `alt_width` int(16) DEFAULT NULL,
  `alt_height` int(16) DEFAULT NULL,
  `color` varchar(16) DEFAULT NULL,
  `font` varchar(100) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_voucher_layout_id` (`voucher_layout_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voucher_layout_parts`
--

LOCK TABLES `voucher_layout_parts` WRITE;
/*!40000 ALTER TABLE `voucher_layout_parts` DISABLE KEYS */;
INSERT INTO `voucher_layout_parts` VALUES (1546,205,'Background','background',0,0,2244,945,0,NULL,NULL,NULL,'default_coupon_bg.png',0,NULL,NULL,NULL,NULL,NULL),(1547,205,'Image','image',12,12,1172,922,2,NULL,NULL,NULL,'http://uploads.coupsmart.com/DefaultVoucherImagePlaceholder.png',0,1172,604,NULL,NULL,NULL),(1548,205,'UPCA','barcode',24,700,354,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_upca.jpg',0,NULL,NULL,NULL,NULL,NULL),(1549,205,'GS1','barcode',401,700,757,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_gs1.jpg',0,NULL,NULL,NULL,NULL,NULL),(1550,205,'Heading','text1',1206,36,1026,135,3,'transparent',NULL,NULL,'Heading',0,NULL,NULL,NULL,NULL,NULL),(1551,205,'Sub Heading','text2',1206,196,1026,90,3,'transparent',NULL,NULL,'Sub Heading',0,NULL,NULL,NULL,NULL,NULL),(1552,205,'Body','text3',1206,309,1026,236,3,'transparent',NULL,NULL,'Description',0,NULL,NULL,NULL,NULL,NULL),(1553,205,'Expiration Date',NULL,1206,568,1026,48,3,'transparent',NULL,NULL,'Expiration Date',1,NULL,NULL,NULL,NULL,NULL),(1554,205,'Coupcheck Logo','coupcheck_logo',1886,661,315,44,4,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_logo.jpg',0,NULL,NULL,NULL,NULL,NULL),(1555,205,'Coupcheck Barcode','coupcheck_barcode',1847,722,360,200,4,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_testbarcode.jpg',1,NULL,NULL,NULL,NULL,NULL),(1556,205,'Cashier Code','cashier_code',1925,870,275,50,4,'#FFFFFF',NULL,NULL,'123-456-789',1,NULL,NULL,NULL,NULL,NULL),(1557,205,'User Image','user_image',1228,661,248,248,5,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/mockup_coupon_fbuserimg.png',1,NULL,NULL,NULL,NULL,NULL),(1558,205,'User Name','user_name',1500,661,284,248,5,'#FFFFFF',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(1559,206,'Background','background',0,0,2244,945,0,NULL,NULL,NULL,'default_coupon_bg.png',0,NULL,NULL,NULL,NULL,NULL),(1560,206,'Image','image',12,12,1172,922,2,NULL,NULL,NULL,'http://uploads.coupsmart.com/DefaultVoucherImagePlaceholder.png',0,1172,604,NULL,NULL,NULL),(1561,206,'UPCA','barcode',24,700,354,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_upca.jpg',0,NULL,NULL,NULL,NULL,NULL),(1562,206,'GS1','barcode',401,700,757,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_gs1.jpg',0,NULL,NULL,NULL,NULL,NULL),(1563,206,'Heading','text1',1206,36,1026,135,3,'transparent',NULL,NULL,'Heading',0,NULL,NULL,NULL,NULL,NULL),(1564,206,'Sub Heading','text2',1206,196,1026,90,3,'transparent',NULL,NULL,'Sub Heading',0,NULL,NULL,NULL,NULL,NULL),(1565,206,'Body','text3',1206,309,1026,236,3,'transparent',NULL,NULL,'Description',0,NULL,NULL,NULL,NULL,NULL),(1566,206,'Expiration Date',NULL,1206,568,1026,48,3,'transparent',NULL,NULL,'Expiration Date',1,NULL,NULL,NULL,NULL,NULL),(1567,206,'Coupcheck Logo','coupcheck_logo',1886,661,315,44,4,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_logo.jpg',0,NULL,NULL,NULL,NULL,NULL),(1568,206,'Coupcheck Barcode','coupcheck_barcode',1847,722,360,200,4,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_testbarcode.jpg',1,NULL,NULL,NULL,NULL,NULL),(1569,206,'Cashier Code','cashier_code',1925,870,275,50,4,'#FFFFFF',NULL,NULL,'123-456-789',1,NULL,NULL,NULL,NULL,NULL),(1570,206,'User Image','user_image',1228,661,248,248,5,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/mockup_coupon_fbuserimg.png',1,NULL,NULL,NULL,NULL,NULL),(1571,206,'User Name','user_name',1500,661,284,248,5,'#FFFFFF',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(1572,207,'Background','background',0,0,2244,945,0,NULL,NULL,NULL,'default_coupon_bg.png',0,NULL,NULL,NULL,NULL,NULL),(1573,207,'Image','image',12,12,1172,922,2,NULL,NULL,NULL,'http://uploads.coupsmart.com/DefaultVoucherImagePlaceholder.png',0,1172,604,NULL,NULL,NULL),(1574,207,'UPCA','barcode',24,700,354,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_upca.jpg',0,NULL,NULL,NULL,NULL,NULL),(1575,207,'GS1','barcode',401,700,757,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_gs1.jpg',0,NULL,NULL,NULL,NULL,NULL),(1576,207,'Heading','text1',1206,36,1026,135,3,'transparent',NULL,NULL,'Heading',0,NULL,NULL,NULL,NULL,NULL),(1577,207,'Sub Heading','text2',1206,196,1026,90,3,'transparent',NULL,NULL,'Sub Heading',0,NULL,NULL,NULL,NULL,NULL),(1578,207,'Body','text3',1206,309,1026,236,3,'transparent',NULL,NULL,'Description',0,NULL,NULL,NULL,NULL,NULL),(1579,207,'Expiration Date',NULL,1206,568,1026,48,3,'transparent',NULL,NULL,'Expiration Date',1,NULL,NULL,NULL,NULL,NULL),(1580,207,'Coupcheck Logo','coupcheck_logo',1886,661,315,44,4,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_logo.jpg',0,NULL,NULL,NULL,NULL,NULL),(1581,207,'Coupcheck Barcode','coupcheck_barcode',1847,722,360,200,4,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_testbarcode.jpg',1,NULL,NULL,NULL,NULL,NULL),(1582,207,'Cashier Code','cashier_code',1925,870,275,50,4,'#FFFFFF',NULL,NULL,'123-456-789',1,NULL,NULL,NULL,NULL,NULL),(1583,207,'User Image','user_image',1228,661,248,248,5,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/mockup_coupon_fbuserimg.png',1,NULL,NULL,NULL,NULL,NULL),(1584,207,'User Name','user_name',1500,661,284,248,5,'#FFFFFF',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(1585,208,'Background','background',0,0,2244,945,0,NULL,NULL,NULL,'default_coupon_bg.png',0,NULL,NULL,NULL,NULL,NULL),(1586,208,'Image','image',12,12,1172,922,2,NULL,NULL,NULL,'http://uploads.coupsmart.com/DefaultVoucherImagePlaceholder.png',0,1172,604,NULL,NULL,NULL),(1587,208,'UPCA','barcode',24,700,354,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_upca.jpg',0,NULL,NULL,NULL,NULL,NULL),(1588,208,'GS1','barcode',401,700,757,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_gs1.jpg',0,NULL,NULL,NULL,NULL,NULL),(1589,208,'Heading','text1',1206,36,1026,135,3,'transparent',NULL,NULL,'Heading',0,NULL,NULL,NULL,NULL,NULL),(1590,208,'Sub Heading','text2',1206,196,1026,90,3,'transparent',NULL,NULL,'Sub Heading',0,NULL,NULL,NULL,NULL,NULL),(1591,208,'Body','text3',1206,309,1026,236,3,'transparent',NULL,NULL,'Description',0,NULL,NULL,NULL,NULL,NULL),(1592,208,'Expiration Date',NULL,1206,568,1026,48,3,'transparent',NULL,NULL,'Expiration Date',1,NULL,NULL,NULL,NULL,NULL),(1593,208,'Coupcheck Logo','coupcheck_logo',1886,661,315,44,4,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_logo.jpg',0,NULL,NULL,NULL,NULL,NULL),(1594,208,'Coupcheck Barcode','coupcheck_barcode',1847,722,360,200,4,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_testbarcode.jpg',1,NULL,NULL,NULL,NULL,NULL),(1595,208,'Cashier Code','cashier_code',1925,870,275,50,4,'#FFFFFF',NULL,NULL,'123-456-789',1,NULL,NULL,NULL,NULL,NULL),(1596,208,'User Image','user_image',1228,661,248,248,5,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/mockup_coupon_fbuserimg.png',1,NULL,NULL,NULL,NULL,NULL),(1597,208,'User Name','user_name',1500,661,284,248,5,'#FFFFFF',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(1598,209,'Background','background',0,0,2244,945,0,NULL,NULL,NULL,'default_coupon_bg.png',0,NULL,NULL,NULL,NULL,NULL),(1599,209,'Image','image',12,12,1172,922,2,NULL,NULL,NULL,'http://uploads.coupsmart.com/DefaultVoucherImagePlaceholder.png',0,1172,604,NULL,NULL,NULL),(1600,209,'UPCA','barcode',24,700,354,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_upca.jpg',0,NULL,NULL,NULL,NULL,NULL),(1601,209,'GS1','barcode',401,700,757,247,1,'#FFFFFF',NULL,NULL,'http://uploads.coupsmart.com/mockup_coupon_gs1.jpg',0,NULL,NULL,NULL,NULL,NULL),(1602,209,'Heading','text1',1206,36,1026,135,3,'transparent',NULL,NULL,'Heading',0,NULL,NULL,NULL,NULL,NULL),(1603,209,'Sub Heading','text2',1206,196,1026,90,3,'transparent',NULL,NULL,'Sub Heading',0,NULL,NULL,NULL,NULL,NULL),(1604,209,'Body','text3',1206,309,1026,236,3,'transparent',NULL,NULL,'Description',0,NULL,NULL,NULL,NULL,NULL),(1605,209,'Expiration Date',NULL,1206,568,1026,48,3,'transparent',NULL,NULL,'Expiration Date',1,NULL,NULL,NULL,NULL,NULL),(1606,209,'Coupcheck Logo','coupcheck_logo',1886,661,315,44,4,NULL,NULL,NULL,'http://uploads.coupsmart.com.s3.amazonaws.com/coupcheck_logo.jpg',0,NULL,NULL,NULL,NULL,NULL),(1607,209,'Coupcheck Barcode','coupcheck_barcode',1847,722,360,200,4,'#FFFFFF',NULL,NULL,'coupcheck_testbarcode.jpg',1,NULL,NULL,NULL,NULL,NULL),(1608,209,'Cashier Code','cashier_code',1925,870,275,50,4,'#FFFFFF',NULL,NULL,'123-456-789',1,NULL,NULL,NULL,NULL,NULL),(1609,209,'User Image','user_image',1228,661,248,248,5,NULL,NULL,NULL,'mockup_coupon_fbuserimg.png',1,NULL,NULL,NULL,NULL,NULL),(1610,209,'User Name','user_name',1500,661,284,248,5,'#FFFFFF',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `voucher_layout_parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voucher_layouts`
--

DROP TABLE IF EXISTS `voucher_layouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher_layouts` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `width` int(16) unsigned DEFAULT NULL,
  `height` int(16) unsigned DEFAULT NULL,
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `private_type` enum('public','company','affiliate','reseller') DEFAULT NULL,
  `private_entity_id` int(16) unsigned DEFAULT NULL,
  `bg_color` varchar(16) DEFAULT NULL,
  `voucher_type` enum('coupon','sgs') DEFAULT 'coupon',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voucher_layouts`
--

LOCK TABLES `voucher_layouts` WRITE;
/*!40000 ALTER TABLE `voucher_layouts` DISABLE KEYS */;
INSERT INTO `voucher_layouts` VALUES (205,'No Expiration Date',2244,945,0,'public',NULL,'#ffffff','coupon'),(206,'E-COMMERCE',2244,945,0,'public',NULL,'#ffffff','coupon'),(207,'BLANK',2244,945,0,'public',NULL,'#ffffff','coupon'),(208,'COUPCHECK ONLY',2244,945,0,'public',NULL,'#ffffff','coupon'),(209,'FB PROFILE INFO',2244,945,0,'public',NULL,'#ffffff','coupon'),(210,'FB PROFILE INFO',2244,945,0,'public',NULL,'#ffffff','coupon');
/*!40000 ALTER TABLE `voucher_layouts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-12-06 18:28:52

INSERT INTO `items` VALUES (2411,NULL,NULL,1147,1290,NULL,'Free coupon code','Get a free coupon code!',NULL,NULL,NULL,'Just hit the Get Code button and view your code! Just hit the Get Code button and view your code! Just hit the Get Code button and view your code!',NULL,NULL,NULL,NULL,'2017-12-13 14:21:43',NULL,NULL,12,100,NULL,NULL,NULL,'Get your very own free coupon code now!',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10000,NULL,'2017-12-13 09:21:43',NULL,NULL,6,NULL,NULL,'running',0,NULL,'Get your free Code - Report',2411,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'Test Corporate','Here\'s your free coupon code!','2b9ef43ea92a1ae43cb9db029c5c3375.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'div#div-deal-background-image {\n			display: none !important\n			}\n\n			div.overlay {\n			display: none !important;\n			}\n\n			#company_logo{\n				border:0px solid red; \n				width:100%; \n				display:block;\n			}\n\n\n			.ui-btn-text{\n				color: #111;\n				width: 200px;\n				font-size: 10px;\n				margin-top: -19px;\n				margin-left: -10px;\n			}\n\n			#logoblock{\n				display:table-cell; \n				max-height:80px;\n				max-width:80px;\n				float:left; \n				vertical-align:middle;\n				background-repeat:no-repeat;\n				background-size:100%;\n				margin:8px;\n			}\n\n			#location{\n				display:table-cell; \n				height:100%;\n				width:170px;\n			}\n			/* Header */\n			.dealheader {\n				background-color: rgb(56, 97, 173);\n				color: white;\n			}\n			/* Use Now Button */\n			#btn_print_now {\n				border: 1px solid #145072;\n				color: white;\n				background: rgb(197, 73, 114);\n				background-image: -moz-linear-gradient(top, #4E89C5, #2567AB);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #5F9CC5), color-stop(1, #396B9E));\n				border-radius:10px;\n				margin-top:10px;\n			}\n\n			/* Use Now Button Hover*/\n			#btn_print_now:hover {\n				background: orange;\n			}\n\n			/* Use Now Button Text*/\n			#btn_print_now span {\n				padding: .6em 25px;\n				display: block;\n				height: 100%;\n				text-overflow: ellipsis;\n				overflow: hidden;\n				white-space: nowrap;\n				position: relative;\n			}\n\n			/* Terms Details Text */\n			p[name=\'p_instore_discount_instructions\'] {\n				font-size: 8px;\n			}\n\n			/* Terms Button */\n			.terms_button {\n				text-align: center;\n				border: 1px solid gray;\n				background: #FDFDFD;\n				border-radius:10px;\n				background-image: -moz-linear-gradient(top, #EEE, #FDFDFD);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #EEE), color-stop(1, #FDFDFD));}\n\n			.banner-row {background: rgb(154, 164, 237);}\n			.companyname {text-shadow: none;}\n			a.button.success,.button.success:active,.button.success:hover,.button.success:focus {background-color:rgb(197, 73, 114); color: white}\n			body {background:rgb(206, 209, 233);}\n			a.button.details,.button.details:active,.button.details:hover,.button.details:focus {background-color:rgb(197, 73, 114); color: white;}\n			.offerimage div {background-image: none !important;}\n			div.offerimage {background-image:url(\'/images/uploads/s3bucket/31b9477b2f8c4c7eec37a5e7b285dc07.jpg\')!important;background-position: center center;background-size:contain;background-repeat:no-repeat;height: 300px;}\n			a#change_email {color:#D60000;text-decoration:underline;}\n			div#loaded button#print {display:none !important}',NULL,NULL,'rgb(197, 73, 114)','white','rgb(197, 73, 114)','rgb(56, 97, 173)','white','rgb(154, 164, 237)','rgb(206, 209, 233)',NULL,0,'<div style=\"position:relative;top:100px;height:600px;\">\n	<img src=\"/images/uploads/s3bucket/960761819d4c27cd6fb5973b45963b42.jpg\" alt=\"\" style=\"z-index: -1\"/>\n	<h2 style=\"position: absolute; top: 235px; left: 10px; font-size: 25px; color: black; background-color:white;border: 1px solid black; padding: 5px; font-weight: normal;\">A882645-43</h2>\n</div>','<div style=\"position:relative;top:100px;height:600px;\">\n	<img src=\"http://uploads.coupsmart.com.s3.amazonaws.com/960761819d4c27cd6fb5973b45963b42.jpg\" alt=\"\" style=\"z-index: -1\"/>\n	<h2 style=\"position: absolute; top: 235px; left: 10px; font-size: 25px; color: black; background-color:white;border: 1px solid black; padding: 5px; font-weight: normal;\">A882645-43</h2>\n</div>',NULL,NULL,'A882645-43',NULL,NULL,NULL,NULL,NULL,'csc_email_template_with_claim_URL',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'alpha-numeric',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

insert into campaigns values (2411,'Get your free Code - Report',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,0,'running',NULL,NULL,NULL,1,'960761819d4c27cd6fb5973b45963b42.jpg',1,NULL,NULL,0,NULL,'31b9477b2f8c4c7eec37a5e7b285dc07.jpg',NULL,NULL,NULL,NULL,NULL,'yes_own',NULL,NULL,'2017-12-13 09:21:43',0,0,0,0,'31b9477b2f8c4c7eec37a5e7b285dc07.jpg',NULL,NULL,'5b0e118a3d7520cdd777a603ba921f63.jpg',NULL,NULL,0,NULL,NULL);

INSERT INTO `deals` VALUES (1290,1147,'Free coupon code',NULL,NULL,'2017-12-13 09:21:42',NULL);


-- Deployed last
update companies set facebook_page_id = '280685912042335' where id = 1147;

drop table if exists `users_notifications`;
CREATE TABLE `users_notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `fb_id` bigint(20) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `notify` int(1) DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `app_index` int(11) DEFAULT NULL,
  `date_notification_sent` datetime DEFAULT NULL,
  `access_token` varchar(255) DEFAULT NULL,
  `permissions` text,
  `expires_on` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

drop table if exists `smart_link_clicks`;
CREATE TABLE `smart_link_clicks` (
  `id` bigint(30) unsigned NOT NULL AUTO_INCREMENT,
  `smart_link_id` int(10) unsigned DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `viewed` tinyint(1) DEFAULT '0',
  `claimed` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

drop table if exists `unsubscribed_emails`;
CREATE TABLE `unsubscribed_emails` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `email_template_id` int(10) unsigned DEFAULT NULL,
  `company_id` int(11) unsigned DEFAULT NULL,
  `type` enum('none','user','company') DEFAULT 'none',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

insert into items values (2394,NULL,NULL,1147,1280,NULL,'TestCorporate Deal 1','TestCorporate Deal 1',NULL,NULL,NULL,'TestCorporate Deal 1 - Details. TestCorporate Deal 1 - Details. TestCorporate Deal 1 - Details. ',NULL,NULL,NULL,NULL,'2016-07-05 15:00:00',NULL,NULL,3,100000,NULL,NULL,NULL,'TestCorporate Deal 1 - Subheading',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10000,NULL,'2016-07-05 10:02:53',NULL,NULL,0,NULL,NULL,'running',0,NULL,'TestCorporate Deal 1',2394,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'div#div-deal-background-image {\n			display: none !important\n			}\n\n			div.overlay {\n			display: none !important;\n			}\n\n			#company_logo{\n				border:0px solid red; \n				width:100%; \n				display:block;\n			}\n\n\n			.ui-btn-text{\n				color: #111;\n				width: 200px;\n				font-size: 10px;\n				margin-top: -19px;\n				margin-left: -10px;\n			}\n\n			#logoblock{\n				display:table-cell; \n				max-height:80px;\n				max-width:80px;\n				float:left; \n				vertical-align:middle;\n				background-repeat:no-repeat;\n				background-size:100%;\n				margin:8px;\n			}\n\n			#location{\n				display:table-cell; \n				height:100%;\n				width:170px;\n			}\n			/* Header */\n			.dealheader {\n				background-color: rgb(153, 138, 205);\n				color: white;\n			}\n			/* Use Now Button */\n			#btn_print_now {\n				border: 1px solid #145072;\n				color: white;\n				background: rgb(199, 199, 32);\n				background-image: -moz-linear-gradient(top, #4E89C5, #2567AB);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #5F9CC5), color-stop(1, #396B9E));\n				border-radius:10px;\n				margin-top:10px;\n			}\n\n			/* Use Now Button Hover*/\n			#btn_print_now:hover {\n				background: orange;\n			}\n\n			/* Use Now Button Text*/\n			#btn_print_now span {\n				padding: .6em 25px;\n				display: block;\n				height: 100%;\n				text-overflow: ellipsis;\n				overflow: hidden;\n				white-space: nowrap;\n				position: relative;\n			}\n\n			/* Terms Details Text */\n			p[name=\'p_instore_discount_instructions\'] {\n				font-size: 8px;\n			}\n\n			/* Terms Button */\n			.terms_button {\n				text-align: center;\n				border: 1px solid gray;\n				background: #FDFDFD;\n				border-radius:10px;\n				background-image: -moz-linear-gradient(top, #EEE, #FDFDFD);\n				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #EEE), color-stop(1, #FDFDFD));}\n\n			.banner-row {background: rgb(209, 209, 129);}\n			.companyname {text-shadow: none;}\n			a.button.success,.button.success:active,.button.success:hover,.button.success:focus {background-color:rgb(199, 199, 32); color: white}\n			body {background:rgb(241, 241, 205);}\n			a.button.details,.button.details:active,.button.details:hover,.button.details:focus {background-color:rgb(48, 191, 57); color: white;}\n			.offerimage div {background-image: none !important;}\n			div.offerimage {background-image:url(\'http://uploads.coupsmart.com.s3.amazonaws.com/04485f294238663f767d73dfffed084d.jpg\')!important;background-position: center center;background-size:contain;background-repeat:no-repeat;height: 300px;}\n			a#change_email {color:#D60000;text-decoration:underline;}\n			div#loaded button#print {display:none !important}',NULL,NULL,'rgb(223, 97, 50)','white',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'alpha-numeric',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

insert into campaigns values (2394,'TestCorporate Deal 1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,0,'running',NULL,NULL,NULL,209,'f5501873a0e5a70399eee5f7cdf24605.jpg',1,NULL,NULL,1,NULL,'a4d624581253d06b4f1cc452d508888f.jpg',NULL,NULL,NULL,NULL,NULL,'yes_own',NULL,NULL,'2016-07-05 10:02:53',0,0,0,0,'a4d624581253d06b4f1cc452d508888f.jpg',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL);

drop table if exists `sgs_discounts`;
CREATE TABLE `sgs_discounts` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `smart_deal_id` int(16) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `begin_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `amount` int(16) DEFAULT NULL,
  `allowed_uses` int(11) DEFAULT NULL,
  `conditions` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

update campaigns set voucher_layout_id = 209 where id = 2394;

INSERT INTO `email_templates` VALUES (1,'customer_free_w1.html','CoupSmart Tips For Your Campaign','Free Customer, Active, Week 1',3,'free_active',0,NULL,NULL),(2,'customer_free_w2.html','An offer you can’t refuse','Free Customer, Active, Week 2',10,'free_active',0,NULL,NULL),(3,'customer_free_w3.html','CoupSmart Tips For Your Campaign','Free Customer, Active, Week 3',17,'free_active',0,NULL,NULL),(4,'customer_free_w4.html','Did you miss the email I sent you?','Free Customer, Active, Week 4',24,'free_active',0,NULL,NULL),(5,'customer_free_w5.html','Do you need more tips?','Free Customer, Active, Week 5',31,'free_active',0,NULL,NULL),(6,'customer_free_inactive_w1.html','Checking In','Free Customer,  Inactive, Week 1',7,'free_inactive',0,NULL,NULL),(7,'customer_free_inactive_w2.html','Need help getting started?','Free Customer,  Inactive, Week 2',14,'free_inactive',0,NULL,NULL),(8,'customer_free_inactive_w3.html','Where have you been?','Free Customer,  Inactive, Week 3',21,'free_inactive',0,NULL,NULL),(9,'customer_free_inactive_w4.html','What are you waiting for?','Free Customer,  Inactive, Week 4',28,'free_inactive',0,NULL,NULL),(10,'customer_upgrade_birthday.html','Do you want a birthday present?','Paying Customer, Day 14',14,'paying',0,NULL,NULL),(11,'customer_upgrade_convercial.html','Why don\'t your customers Like you?','Paying Customer, Day 28',28,'paying',0,NULL,NULL),(12,'customer_upgrade_webcoupons.html','Q. How do you get more customers from your website?','Paying Customer, Day 42',42,'paying',0,NULL,NULL),(13,'customer_upgrade_training.html','All You Need to Know About Convercial','Paying Customer, Added Convercial',NULL,'added_convercial',0,NULL,NULL),(14,'customer_notice_comingsoon.html','Your campaign starts in a week','All Customers',NULL,'all',0,NULL,NULL),(15,'customer_notice_itemshipped.html','Your Items Have Been Shipped','All Customers',NULL,'all',1,NULL,NULL),(16,'customer_notice_paid.html','','All Customers',NULL,'all',0,NULL,NULL),(17,'customer_notice_newaccount.html','Welcome to CoupSmart!','All Customers, New',NULL,'all',0,NULL,NULL),(18,'customer_notice_successfulcamp.html','Congratulations on your successful campaign!','All Customers, Campaign Finished',NULL,'all',0,NULL,NULL),(19,'customer_notice_lowecamp.html','Your campaign is completed!','All Customers, Campaign Finished Poor (engagement rate)',NULL,'all',0,NULL,NULL),(20,'customer_notice_lowccamp.html','Your campaign is completed!','All Customers, Campaign Finished Poor (conversion rate)',NULL,'all',0,NULL,NULL),(21,'customer_notice_stats.html','(TBD)','',NULL,'all',0,NULL,NULL),(22,'customer_engagement_energy.html','An energy boost from CoupSmart (TBD)','All Customers, New, 3 hours',NULL,'all',0,NULL,NULL),(23,'customer_upgrade_freetraining.html','All You Need to Know About Convercial','Free Customer, Added Convercial',NULL,'added_convercial',0,NULL,NULL),(24,'social_gift_receipt.html','Your Social Gift Shop Receipt','Buyer payment receipt',NULL,'all',0,'You received this email because you purchased an item in our Facebook Gift Shop, this email is just to confirm your purchase. If you did not make a purchase, or believe this email is in error, let us know by emailing our <a href=\"mailto:support@coupsmart.com\">Support Team</a>.\n',NULL),(25,'social_gift_company_receipt.html','Social Gift Shop Purchase','Company being informed of SGS purchase',NULL,'all',0,NULL,NULL),(26,'social_gift.html','Somebody sent you a gift','Email to recipient (Email subject varies depending of gift)',NULL,'all',0,NULL,NULL),(27,'social_gift_redeemed.html','Your friend has redeemed your gift','Email sent to buyer of gift after offer redemption',NULL,'all',0,NULL,NULL),(28,'social_gift_confirmation.html','Your gift has been sent','Email sent to buyer after FB post and/or email',NULL,'all',0,NULL,NULL),(29,'social_gift_company_ship_order_email.html','Social Gift Shipment Order','Email sent to company when an order is placed to be shipped out.',NULL,'all',0,NULL,NULL),(30,'social_gift_anonymous_ship_email.html','You Have A Gift On The Way!','Email sent to recipient of anonymous gift in gift store',NULL,'all',0,NULL,NULL),(31,'social_gift_order_updated_email.html','An Order Has Been Updated','Email to send to companies when a shipping order has been updated',NULL,'all',0,NULL,NULL),(32,'smartgift0313_receipt.html','Your Social Gift Shop Receipt','The SGS Receipt sent to the buyer when purchasing a new Order.',NULL,NULL,1,'You received this email because you purchased an item in our Facebook Gift Shop, this email is just to confirm your purchase. If you did not make a purchase, or believe this email is in error, let us know by emailing our Support Team: support@coupsmart.com','{\"emailHeaderImage\":\"https://s3.amazonaws.com/siteimg.coupsmart.com/email/csSGS_emailheader.jpg\",\"unsubscribeLink\":\"http://coupsmart.com/unsubscribe\",\"receiveMessage\":\"You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to inform you that someone made a purchase using the app. If you believe this email is in error, let us know by emailing our <a href=\'mailto:support@coupsmart.com\'>Support Team</a>\"}'),(33,'smartgift0313_notifyOrderNew.html','You have a new Order','The Order Details sent to the company when a new Order has been placed from the SGS Gift Shop.',NULL,NULL,1,'You received this email because someone made a purchase from the Gift Shop on Facebook App on your Facebook Page, this email is informing you of the order details. If you do not have a Gift Shop on Facebook App, or believe this email is in error, let us know by emailing our Support Team: support@coupsmart.com','{\"emailHeaderImage\":\"https://s3.amazonaws.com/siteimg.coupsmart.com/email/csSGS_emailheader.jpg\",\"clientCustomText\":\"Visit our shop at the Facebook Page\",\"receiveMessage\":\"You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to inform you that someone made a purchase using the app. If you believe this email is in error, let us know by emailing our Support Team\",\"unsubscribe_link\":\"http://coupsmart.com/unsubscribe\"}'),(34,'smartgift0313_recipientNotification.html','You have received a new Gift!','A notification sent to the recipient when they are sent a gift via the Anonymous/Email option selected from the SGS Gift Shop.',NULL,NULL,1,'You received this email because someone purchased an item for you in our Facebook Gift Shop, this email is just to notify you that you have a gift waiting for you on Facebook. If you believe this email is in error, let us know by emailing our Support Team: support@coupsmart.com',NULL),(35,'smartgift0313_shippingAddressNeeded.html','There was a problem with the gift you sent','An email reminder sent to the buyer if the recipient address has not been provided when physcially mailing them the gift.',NULL,NULL,1,'You received this email because you purchased an item in our Facebook Gift Shop, this email is alerting you that there is a problem fulfilling your order completely. If you did not make a purchase, or believe this email is in error, let us know by emailing our Support Team: support@coupsmart.com',NULL),(36,'instore_savelater2.html','Here\'s Your Fan-Only Coupon!','An email sent to the user via the instore UI if they click \"Email to Use Later\" button.',NULL,NULL,0,NULL,NULL),(37,'send_payment_request.html','Coupsmart Payment Request','An email sent to the client requesting payment.',NULL,NULL,0,NULL,NULL),(39,'payment_confirmation.html','Coupsmart Payment Confirmed','An confirmation email sent to the sales person when a payment is made.',NULL,NULL,0,NULL,NULL),(40,'mobileoffers_0513.html','Here is Your Fan-Only Coupon!',NULL,NULL,NULL,0,'You received this email because you claimed a Facebook fan-only offer and elected to have it emailed to this address. This email contains the link to print out your deal voucher. If you did not claim a deal, or believe this email is in error, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.','{\"emailHeaderImage\":\"https://s3.amazonaws.com/siteimg.coupsmart.com/email/emailbasic_header_black.jpg\",\"clientCustomText\":\"Visit our shop at the Facebook Page\",\"receiveMessage\":\"You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to inform you that someone made a purchase using the app. If you believe this email is in error, let us know by emailing our Support Team\",\"unsubscribe_link\":\"http://coupsmart.com/unsubscribe\"}'),(41,'target_email_layout1.html','','Smart Email Layout 1',NULL,'all',0,NULL,NULL),(42,'target_email_layout2.html','','Smart Email Layout 2',NULL,'all',0,NULL,NULL),(43,'target_email_layout3.html','','Smart Email Layout 3',NULL,'all',0,NULL,NULL),(44,'mobileoffers_0513.html','Here is a link to the deal you claimed earlier',NULL,NULL,NULL,0,'You received this email because you claimed a Facebook fan-only offer and elected to have it emailed to this address. This email contains the link to print out your deal voucher. If you did not claim a deal, or believe this email is in error, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.','{\"emailHeaderImage\":\"https://s3.amazonaws.com/siteimg.coupsmart.com/email/emailbasic_header_black.jpg\",\"clientCustomText\":\"Visit our shop at the Facebook Page\",\"receiveMessage\":\"You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to inform you that someone made a purchase using the app. If you believe this email is in error, let us know by emailing our Support Team\",\"unsubscribe_link\":\"http://coupsmart.com/unsubscribe\"}'),(45,'company_renewtoken.html','URGENT: Facebook Token Needed','Request customer to renew their access token for Facebook',NULL,'all',1,NULL,NULL),(46,'smartgift0313_notifyOrderUpdatedAddress.html','Updated Order Information','An email notification sent to the clients when the recipient address has been entered.',NULL,NULL,0,'You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to inform you that someone has added new information you an order they have made before on the app. If you believe this email is in error, let us know by emailing our <a href=\"mailto:support@coupsmart.com\">Support Team</a>.',NULL),(47,'fb_notify_reminder.html','Facebook Notification Reminder','An email reminder sent to the buyer if they have not sent an FB Notification to the recipient after the purchase.',NULL,NULL,0,'You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to remind you that you need to send one or more Facebook Notifications to recipients whom you sent SGS gifts. If you believe this email is in error, let us know by emailing our <a href=\"mailto:support@coupsmart.com\">Support Team</a>.',NULL),(48,'smartgift0313_notifyPurchaserOrderUpdatedAddress.html','Updated Order Information','An email notification sent to the buyer when the recipient address has been entered.',NULL,NULL,0,'You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to inform you that someone has added new information you an order they have made before on the app. If you believe this email is in error, let us know by emailing our <a href=\"mailto:support@coupsmart.com\">Support Team</a>.',NULL),(100,'smart_email_lindt2.html','Holiday Treats on Sale Now at the Ghirardelli Gift Shop','An email sent to the lindt users containing a URL for visiting the SGS App Store.',NULL,NULL,0,NULL,NULL),(101,'smartgift0313_notifyPurchaserOfClientsAction.html','Your Order Cancellation Request Has Been Acknowledged','An email notification sent to the purchaser when the company acknowledges an order cancellation and takes action.',NULL,NULL,0,'You have received this email because {$compName} has accepted your cancellation of an order through your Facebook Smart Gifts Store. If you have questions or concerns, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.',NULL),(103,'csc_mobile_offers.html','Here is a link to the deal you claimed earlier',NULL,NULL,NULL,0,'You received this email because you claimed a Facebook fan-only offer and elected to have it emailed to this address. This email contains the link to print out your deal voucher. If you did not claim a deal, or believe this email is in error, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.','{\"emailHeaderImage\":\"https://s3.amazonaws.com/siteimg.coupsmart.com/email/emailbasic_header_black.jpg\",\"clientCustomText\":\"Visit our shop at the Facebook Page\",\"receiveMessage\":\"You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to inform you that someone made a purchase using the app. If you believe this email is in error, let us know by emailing our Support Team\",\"unsubscribe_link\":\"http://coupsmart.com/unsubscribe\"}'),(102,'csc_mobile_offers.html','Here is Your Fan-Only Coupon!',NULL,NULL,NULL,0,'You received this email because you claimed a Facebook fan-only offer and elected to have it emailed to this address. This email contains the link to print out your deal voucher. If you did not claim a deal, or believe this email is in error, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.','{\"emailHeaderImage\":\"https://s3.amazonaws.com/siteimg.coupsmart.com/email/emailbasic_header_black.jpg\",\"clientCustomText\":\"Visit our shop at the Facebook Page\",\"receiveMessage\":\"You received this email because you currently have our Gift Shop App installed on your Facebook Page. This email is to inform you that someone made a purchase using the app. If you believe this email is in error, let us know by emailing our Support Team\",\"unsubscribe_link\":\"http://coupsmart.com/unsubscribe\"}'),(49,'smart_email_lindt.html','Our Favorite Gifts of the Season at the Ghirardelli Chocolate Gift Shop','An email sent to the lindt users containing a URL for visiting the SGS App Store.',NULL,NULL,0,NULL,NULL),(50,'smart_email_lindt.html','Our Favorite Gifts of the Season at the Ghirardelli Chocolate Gift Shop','An email sent to the lindt users containing a URL for visiting the SGS App Store.',NULL,NULL,0,NULL,NULL),(96,'smartgift0313_notifyClientOfCancellation.html','You Have Requested An Order Cancellation','An email notification sent to the client when they request an order cancellation.',NULL,NULL,0,'You have received this email because you have requested a cancellation of an order through Facebook Smart Gifts Store. If you have questions or concerns, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.',NULL),(97,'smartgift0313_notifyPurchaserOfCancellation.html','You Have An Order Cancellation Request','An email notification sent to the company when the recipient requests an order cancellation.',NULL,NULL,0,'You have received this email because a customer has requested a cancellation of an order through your Facebook Smart Gifts Store. If you have questions or concerns, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.',NULL),(98,'smartgift0313_notifyUserOrderNotCancelled.html','Your Order Cancellation Request Has Been Denied','An email notification sent to the purchaser when the company denies an order cancellation.',NULL,NULL,0,'You have received this email because {$compName} has denied your cancellation of an order through your Facebook Smart Gifts Store. If you have questions or concerns, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.',NULL),(99,'smartgift0313_notifyUserOrderCancelled.html','Your Order Cancellation Request Has Been Approved','An email notification sent to the purchaser when the company accepts an order cancellation.',NULL,NULL,0,'You have received this email because {$compName} has accepted your cancellation of an order through your Facebook Smart Gifts Store. If you have questions or concerns, let us know by reporting it to our <a href=\"http://support.coupsmart.com\">Support Center</a> or by emailing us at support@coupsmart.com.',NULL),(104,'notify_coupon_printed.html','Please Share and Show You Care','An email notification sent to the user when they print a coupon.',NULL,NULL,0,'You have received this email because you have just printed a coupon via Coupsmart',NULL),(105,'donationReceipt.html','Thank you for your Donation!','Email that gets sent when a donation is made',NULL,NULL,0,NULL,NULL),(106,'csc_email_template.html','Here\'s your coupon code','Email that gets sent when a coupon code gets claimed.',NULL,NULL,0,NULL,NULL),(107,'client_bug_report.html','Client Bug Report!','Email that gets sent when a Bug Report is submitted by the Client.',NULL,NULL,0,NULL,NULL),(108,'cc-billing-signup.html','CoupSmart Sign Up Successful!','Email that gets sent when a user signs up for Self Service.',NULL,NULL,0,NULL,NULL),(109,'cc-payment-successful.html','Billing Payment successful!','Email that gets sent when a billing payment is successfully performed using the automated script.',NULL,NULL,0,NULL,NULL),(110,'cc-payment-failed.html','Billing Payment failed!','Email that gets sent when a billing payment fails for some reason.',NULL,NULL,0,NULL,NULL),(111,'cc-payment-reminder.html','Reminder - Your yearly license will expire soon!','Email that gets sent when a billing yearly license is about to expire in a few given days.',NULL,NULL,0,NULL,NULL),(112,'csc_email_template_with_claim_URL.html','Here\'s your coupon code','Email that gets sent when a coupon code gets claimed.',NULL,NULL,0,NULL,NULL),(113,'mc_demo_request_email.html','Demo Request Email','',NULL,NULL,0,NULL,NULL),(114,'client-setup-confirmation.html','NEW COUPSMART ACCOUNT NOTIFICATION | Please Complete!','A confirmation email that gets sent to the client when they are setup from the admin->client setup page',NULL,NULL,0,NULL,NULL),(115,'client-credit-card-setup.html','THANK YOU | Notification of Payment Set Up','NOTIFICATION OF Credit Card SETUP',NULL,NULL,0,NULL,NULL),(116,'client-invoice-setup.html','THANK YOU | Notification of Payment Set Up','NOTIFICATION OF INVOICE SETUP',NULL,NULL,0,NULL,NULL),(117,'client-billing-contact-setup.html','THANK YOU | Notification of New Billing Contact','NOTIFICATION OF NEW ACCOUNT REPRESENTATIVE (BILLING CONTACT)',NULL,NULL,0,NULL,NULL),(118,'client-payment-notification.html','THANK YOU | Notification of Payment Received','NOTIFICATION OF PAYMENT RECEIVED',NULL,NULL,0,NULL,NULL),(119,'client-invoice.html','Your Invoice','CLIENT INVOICE',NULL,NULL,0,NULL,NULL),(120,'user-setup-confirmation.html','NEW USER ACCOUNT NOTIFICATION | Please Complete!','A confirmation email that gets sent to the user when they are setup from the admin->client setup page',NULL,NULL,0,NULL,NULL),(121,'daily-campaign-report.html','Coupsmart Daily Campaign Report','Coupsmart Daily Campaign Report',NULL,NULL,0,NULL,NULL),(122,'weekly-campaign-report.html','Coupsmart Weekly Campaign Report','Coupsmart Weekly Campaign Report',NULL,NULL,0,NULL,NULL);

drop table if exists `user_emails`;
CREATE TABLE `user_emails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_template` varchar(255) NOT NULL,
  `sent` datetime NOT NULL,
  `status` enum('failure','success') DEFAULT NULL,
  `foreign_key_table` varchar(50) DEFAULT NULL,
  `foreign_key` int(10) unsigned DEFAULT NULL,
  `user_email_code` varchar(36) DEFAULT NULL,
  `ses_status` int(11) DEFAULT NULL,
  `ses_request_id` varchar(100) DEFAULT NULL,
  `ses_message_id` varchar(100) DEFAULT NULL,
  `ses_error` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userEmailDateIdx` (`user_id`,`email_template`,`sent`),
  KEY `emailIdx` (`email`),
  KEY `userIdIdx` (`user_id`),
  KEY `email_templateIdx` (`email_template`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


drop table if exists `user_email_links`;
CREATE TABLE `user_email_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_email_id` int(11) unsigned DEFAULT NULL,
  `user_email_code` varchar(36) DEFAULT NULL,
  `url_href` text,
  `email_link_code` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_email_code_idx` (`user_email_code`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


drop table if exists `user_email_clicks`;
CREATE TABLE `user_email_clicks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `user_email_link_id` bigint(20) unsigned DEFAULT NULL,
  `user_email_id` int(11) unsigned DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_click_code` varchar(36) DEFAULT NULL,
  `page_loaded` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


drop table if exists `user_emails_opened`;
CREATE TABLE `user_emails_opened` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_email_id` int(11) unsigned DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


drop table if exists `email_template_override`;
CREATE TABLE `email_template_override` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) unsigned DEFAULT NULL,
  `item_id` int(10) unsigned DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  `content` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


drop table if exists `user_company_unsubscribe`;
CREATE TABLE `user_company_unsubscribe` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `company_id` int(11) unsigned NOT NULL,
  `time_unsubscribed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

drop table if exists `referrals`;
CREATE TABLE `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `request_id` varchar(100) DEFAULT NULL,
  `sender_id` varchar(100) DEFAULT NULL,
  `receipient_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','accepted','received') DEFAULT NULL,
  `item_shared` int(11) DEFAULT NULL,
  `item_claimed` int(11) DEFAULT NULL,
  `image_shared` int(11) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `share_method` enum('own_wall','friend_wall','request','own_group','own_page','private_message','countmein','') DEFAULT NULL,
  `parent_id` int(11) DEFAULT '0',
  `level` int(10) DEFAULT '0',
  `company_id` bigint(20) DEFAULT NULL,
  `share_msg` varchar(255) DEFAULT NULL,
  `app_name` enum('smart_deals','sgs','convercial','sdw') DEFAULT NULL,
  `url_shared` text,
  `referral_code` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_created` (`created`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
