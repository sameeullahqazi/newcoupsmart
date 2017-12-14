-- MySQL dump 10.13  Distrib 5.6.12, for osx10.7 (x86_64)
--
-- Host: localhost    Database: dev_coupsmart
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
) ENGINE=MyISAM AUTO_INCREMENT=123 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `address_last_verified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
) ENGINE=InnoDB AUTO_INCREMENT=459326 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=1171 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=2411 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=1290 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=2411 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=1093252 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=1837218 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=MyISAM AUTO_INCREMENT=282 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=MyISAM AUTO_INCREMENT=1355916 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=8172037 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-12-04 17:07:57

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
) ENGINE=MyISAM AUTO_INCREMENT=7941 DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM AUTO_INCREMENT=245 DEFAULT CHARSET=utf8;

CREATE TABLE `blocked_app_users` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `facebook_id` varchar(255) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `app_name` enum('fan_deals','sgs','instore','countmein','booking','web','convercial') DEFAULT NULL,
  `reason` text,
  `automatically_banned` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

CREATE TABLE `suspicious_user_activity` (
  `id` bigint(32) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(10) DEFAULT NULL,
  `reason` enum('no_friends','fraudulent_activity') DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

CREATE TABLE `customer_supplied_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_code` varchar(255) NOT NULL,
  `issued_status` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `deal_id` int(11) DEFAULT NULL,
  `user_item_id` bigint(20) unsigned DEFAULT NULL,
  `date_printed` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `facebook_id` bigint(20) unsigned DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `index_deal_id` (`deal_id`)
) ENGINE=MyISAM AUTO_INCREMENT=501000 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB AUTO_INCREMENT=210 DEFAULT CHARSET=utf8;

CREATE TABLE `users_companies_campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` bigint(20) unsigned DEFAULT NULL,
  `companies_id` int(10) unsigned NOT NULL,
  `campaigns_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4455 DEFAULT CHARSET=utf8;

