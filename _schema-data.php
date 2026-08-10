<?php

/**
 * Auto-generated embedded schema dump for install.php (DO NOT EDIT).
 * Generated: 2026-08-03T20:53:48+01:00
 * Source database: bli_admin_laravel
 */

define('EMBEDDED_SCHEMA_SQL', '-- TWCA embedded schema dump (generated 2026-08-03T20:53:48+01:00)
-- Database: bli_admin_laravel

DROP TABLE IF EXISTS `about_us`;
CREATE TABLE `about_us` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `subtitle` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE=utf8_unicode_ci,
  `icon_class` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `section_type` varchar(191) COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'custom\' COMMENT \'mission, vision, values, quote, custom\',
  `quote_author` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT \'0\',
  `status` enum(\'published\',\'draft\') COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'draft\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_date` date NOT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `males` int NOT NULL DEFAULT \'0\',
  `females` int NOT NULL DEFAULT \'0\',
  `first_timers` int NOT NULL DEFAULT \'0\',
  `total` int GENERATED ALWAYS AS ((`males` + `females`)) STORED,
  `recorded_by` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `center_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `attendance_center_id_foreign` (`center_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `author` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `purchase_link` varchar(255) DEFAULT NULL,
  `download_link` varchar(255) DEFAULT NULL,
  `pdf_file` text,
  `allow_pdf_download` tinyint(1) NOT NULL DEFAULT \'0\',
  `image_id` int DEFAULT NULL,
  `status` enum(\'draft\',\'published\') DEFAULT \'draft\',
  `available` tinyint(1) DEFAULT \'1\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `books_slug_unique` (`slug`),
  KEY `image_id` (`image_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `value` mediumtext COLLATE=utf8_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `center_locations`;
CREATE TABLE `center_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `address` text COLLATE=utf8_unicode_ci,
  `phone` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `service_times` text COLLATE=utf8_unicode_ci,
  `description` text COLLATE=utf8_unicode_ci,
  `image` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT \'0\',
  `status` enum(\'published\',\'draft\') COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'draft\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `church_members`;
CREATE TABLE `church_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `other_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_joined` date NOT NULL,
  `membership_status` enum(\'active\',\'inactive\',\'deceased\') DEFAULT \'active\',
  `marital_status` enum(\'single\',\'married\',\'divorced\',\'widowed\') DEFAULT \'single\',
  `gender` enum(\'male\',\'female\') DEFAULT \'male\',
  `occupation` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `center_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `church_members_center_id_foreign` (`center_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `church_staff`;
CREATE TABLE `church_staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum(\'active\',\'inactive\') DEFAULT \'active\',
  `salary` decimal(10,2) DEFAULT NULL,
  `responsibilities` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum(\'unread\',\'read\',\'replied\') DEFAULT \'unread\',
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `devotionals`;
CREATE TABLE `devotionals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) CHARACTER SET utf8 COLLATE=utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8 COLLATE=utf8_unicode_ci NOT NULL,
  `scripture_reference` varchar(100) CHARACTER SET utf8 COLLATE=utf8_unicode_ci DEFAULT NULL,
  `scripture_text` text CHARACTER SET utf8 COLLATE=utf8_unicode_ci,
  `prayer` text CHARACTER SET utf8 COLLATE=utf8_unicode_ci,
  `reflection_questions` text CHARACTER SET utf8 COLLATE=utf8_unicode_ci,
  `author` varchar(100) CHARACTER SET utf8 COLLATE=utf8_unicode_ci DEFAULT NULL,
  `devotional_date` date NOT NULL,
  `image_id` int DEFAULT NULL,
  `status` enum(\'draft\',\'published\',\'scheduled\') CHARACTER SET utf8 COLLATE=utf8_unicode_ci DEFAULT \'draft\',
  `featured` tinyint(1) DEFAULT \'0\',
  `views_count` int DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`devotional_date`),
  UNIQUE KEY `devotionals_slug_unique` (`slug`),
  KEY `image_id` (`image_id`),
  CONSTRAINT `devotionals_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum(\'draft\',\'published\') DEFAULT \'draft\',
  `expires` tinyint(1) NOT NULL DEFAULT \'1\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `connection` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `queue` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE=utf8_unicode_ci NOT NULL,
  `exception` longtext COLLATE=utf8_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `financial_accounts`;
CREATE TABLE `financial_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `type` enum(\'bank\',\'cash\',\'mobile_money\') COLLATE=utf8_unicode_ci NOT NULL,
  `account_number` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `bank_name` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `branch` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `opening_balance` decimal(12,2) NOT NULL DEFAULT \'0.00\',
  `current_balance` decimal(12,2) NOT NULL DEFAULT \'0.00\',
  `is_active` tinyint(1) NOT NULL DEFAULT \'1\',
  `notes` text COLLATE=utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `financial_campaigns`;
CREATE TABLE `financial_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `description` text COLLATE=utf8_unicode_ci,
  `target_amount` decimal(12,2) NOT NULL,
  `raised_amount` decimal(12,2) NOT NULL DEFAULT \'0.00\',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum(\'active\',\'completed\',\'cancelled\') COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'active\',
  `cover_image` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `financial_funds`;
CREATE TABLE `financial_funds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `description` text COLLATE=utf8_unicode_ci,
  `target_amount` decimal(12,2) DEFAULT NULL,
  `current_amount` decimal(12,2) NOT NULL DEFAULT \'0.00\',
  `is_active` tinyint(1) NOT NULL DEFAULT \'1\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `financial_pledges`;
CREATE TABLE `financial_pledges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint unsigned DEFAULT NULL,
  `campaign_id` bigint unsigned DEFAULT NULL,
  `pledge_amount` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL DEFAULT \'0.00\',
  `payment_schedule` text COLLATE=utf8_unicode_ci,
  `status` enum(\'active\',\'completed\',\'cancelled\') COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'active\',
  `pledge_date` date NOT NULL,
  `notes` text COLLATE=utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `financial_pledges_member_id_foreign` (`member_id`),
  KEY `financial_pledges_campaign_id_foreign` (`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `financial_transactions`;
CREATE TABLE `financial_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` enum(\'inflow\',\'outflow\') COLLATE=utf8_unicode_ci NOT NULL,
  `category` enum(\'tithe\',\'offering\',\'special_offering\',\'building_fund\',\'pledge\',\'other_income\',\'ministry_expense\',\'administrative\',\'utilities\',\'salary\',\'maintenance\',\'missions\',\'other_expense\') COLLATE=utf8_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum(\'cash\',\'bank_transfer\',\'check\',\'mobile_money\',\'other\') COLLATE=utf8_unicode_ci NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `fund_id` bigint unsigned DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `reference_number` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `member_id` bigint unsigned DEFAULT NULL,
  `recorded_by` bigint unsigned NOT NULL,
  `status` enum(\'pending\',\'approved\',\'rejected\') COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'approved\',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `reconciled` tinyint(1) NOT NULL DEFAULT \'0\',
  `notes` text COLLATE=utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `financial_transactions_account_id_foreign` (`account_id`),
  KEY `financial_transactions_fund_id_foreign` (`fund_id`),
  KEY `financial_transactions_member_id_foreign` (`member_id`),
  KEY `financial_transactions_recorded_by_foreign` (`recorded_by`),
  KEY `financial_transactions_approved_by_foreign` (`approved_by`),
  KEY `financial_transactions_transaction_date_index` (`transaction_date`),
  KEY `financial_transactions_type_index` (`type`),
  KEY `financial_transactions_category_index` (`category`),
  KEY `financial_transactions_status_index` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `hero_settings`;
CREATE TABLE `hero_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE=utf8_unicode_ci DEFAULT NULL COMMENT \'Global hero title (replaces per-slider titles)\',
  `badge_text` varchar(255) COLLATE=utf8_unicode_ci DEFAULT NULL COMMENT \'Text shown in the glass badge (e.g. "Worship With Us")\',
  `prefix_text` varchar(255) COLLATE=utf8_unicode_ci DEFAULT NULL COMMENT \'Text before the title (e.g. "Welcome to")\',
  `suffix_text` varchar(255) COLLATE=utf8_unicode_ci DEFAULT NULL COMMENT \'Text after the title (e.g. "Ministries")\',
  `description` text COLLATE=utf8_unicode_ci COMMENT \'Global hero description (replaces per-slider descriptions)\',
  `button_text` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL COMMENT \'Text for the hero CTA button\',
  `button_link` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL COMMENT \'URL for the hero CTA button\',
  `show_button` tinyint(1) NOT NULL DEFAULT \'1\',
  `show_badge` tinyint(1) NOT NULL DEFAULT \'1\',
  `show_description` tinyint(1) NOT NULL DEFAULT \'1\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `homepage_sections`;
CREATE TABLE `homepage_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `content` text,
  `column_layout` enum(\'single\',\'two-column\',\'three-column\',\'four-column\') DEFAULT \'single\',
  `image_id` int DEFAULT NULL,
  `display_order` int DEFAULT \'0\',
  `status` enum(\'draft\',\'published\') DEFAULT \'draft\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`),
  KEY `image_id` (`image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `homepage_services`;
CREATE TABLE `homepage_services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `display_order` int DEFAULT \'0\',
  `status` enum(\'draft\',\'published\') DEFAULT \'draft\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `homepage_sliders`;
CREATE TABLE `homepage_sliders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text,
  `button_text` varchar(50) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `image_id` int unsigned DEFAULT NULL,
  `video_id` int unsigned DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `display_order` int DEFAULT \'0\',
  `status` enum(\'published\',\'draft\') NOT NULL DEFAULT \'draft\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `image_id` (`image_id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE=utf8_unicode_ci NOT NULL,
  `options` mediumtext COLLATE=utf8_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE=utf8_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `target` enum(\'_self\',\'_blank\') DEFAULT \'_self\',
  `order_number` int DEFAULT \'0\',
  `status` enum(\'active\',\'inactive\') DEFAULT \'active\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_cta` tinyint(1) DEFAULT \'0\',
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(50) NOT NULL,
  `display_order` int DEFAULT \'0\',
  `status` enum(\'active\',\'inactive\') DEFAULT \'active\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `message_replies`;
CREATE TABLE `message_replies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message_id` int NOT NULL,
  `reply_subject` varchar(255) NOT NULL,
  `reply_message` text NOT NULL,
  `sent_by` int NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  KEY `sent_by` (`sent_by`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE=utf8_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `ministry_columns`;
CREATE TABLE `ministry_columns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `column_type` varchar(191) COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'ministry\',
  `icon_class` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `title` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `subtitle` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE=utf8_unicode_ci,
  `quote_author` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT \'0\',
  `status` varchar(191) COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'published\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `verification_token` varchar(64) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'pending\',
  `subscribed_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `unsubscribe_token` varchar(64) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `bounced_at` timestamp NULL DEFAULT NULL,
  `bounce_reason` text COLLATE=utf8_unicode_ci,
  `complaint_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_subscribers_email_unique` (`email`),
  UNIQUE KEY `newsletter_subscribers_verification_token_unique` (`verification_token`),
  UNIQUE KEY `newsletter_subscribers_unsubscribe_token_unique` (`unsubscribe_token`),
  KEY `newsletter_subscribers_verification_token_index` (`verification_token`),
  KEY `newsletter_subscribers_unsubscribe_token_index` (`unsubscribe_token`),
  KEY `newsletter_subscribers_status_index` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `newsletter_tracking`;
CREATE TABLE `newsletter_tracking` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `newsletter_id` bigint unsigned NOT NULL,
  `subscriber_id` bigint unsigned NOT NULL,
  `event` varchar(20) COLLATE=utf8_unicode_ci NOT NULL,
  `link_url` varchar(2048) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `newsletter_tracking_subscriber_id_foreign` (`subscriber_id`),
  KEY `newsletter_tracking_newsletter_id_subscriber_id_index` (`newsletter_id`,`subscriber_id`),
  KEY `newsletter_tracking_event_index` (`event`),
  KEY `newsletter_tracking_occurred_at_index` (`occurred_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `newsletters`;
CREATE TABLE `newsletters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE=utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE=utf8_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE=utf8_unicode_ci NOT NULL DEFAULT \'draft\',
  `sent_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `total_sent` int unsigned NOT NULL DEFAULT \'0\',
  `opens_count` int unsigned NOT NULL DEFAULT \'0\',
  `clicks_count` int unsigned NOT NULL DEFAULT \'0\',
  `test_email` varchar(191) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `newsletters_status_index` (`status`),
  KEY `newsletters_scheduled_at_index` (`scheduled_at`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `offerings`;
CREATE TABLE `offerings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_date` date NOT NULL,
  `service_type` enum(\'sunday_service\',\'midweek_service\',\'special_service\',\'other\') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `offering_type` enum(\'tithe\',\'offering\',\'special_offering\',\'building_fund\',\'other\') NOT NULL,
  `payment_method` enum(\'cash\',\'bank_transfer\',\'check\',\'other\') NOT NULL,
  `recorded_by` int NOT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `content` text,
  `meta_description` varchar(255) DEFAULT NULL,
  `status` enum(\'draft\',\'published\') DEFAULT \'draft\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE=utf8_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `attempts` tinyint unsigned NOT NULL DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_resets_email_index` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `quotes`;
CREATE TABLE `quotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `status` enum(\'draft\',\'published\') DEFAULT \'draft\',
  `display_order` int DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `image_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE=utf8_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE=utf8_unicode_ci,
  `is_super_admin` tinyint(1) DEFAULT \'0\',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sermon_media`;
CREATE TABLE `sermon_media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sermon_id` int NOT NULL,
  `media_id` int NOT NULL,
  `track_order` smallint unsigned NOT NULL DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sermon_id` (`sermon_id`),
  KEY `media_id` (`media_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sermon_series`;
CREATE TABLE `sermon_series` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `image_id` int DEFAULT NULL,
  `status` enum(\'draft\',\'published\') DEFAULT \'draft\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `image_id` (`image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sermons`;
CREATE TABLE `sermons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text,
  `preacher` varchar(100) DEFAULT NULL,
  `sermon_date` date DEFAULT NULL,
  `series_id` int DEFAULT NULL,
  `media_id` int NOT NULL,
  `image_id` int DEFAULT NULL,
  `track_number` int DEFAULT \'1\',
  `status` enum(\'draft\',\'published\') DEFAULT \'draft\',
  `featured` tinyint(1) DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sermons_slug_unique` (`slug`(191)),
  KEY `series_id` (`series_id`),
  KEY `media_id` (`media_id`),
  KEY `image_id` (`image_id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `service_types`;
CREATE TABLE `service_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT \'1\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  `setting_group` varchar(50) DEFAULT \'general\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`,`setting_group`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `song_media`;
CREATE TABLE `song_media` (
  `song_id` int NOT NULL,
  `media_id` int NOT NULL,
  `track_order` smallint unsigned NOT NULL DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`song_id`,`media_id`),
  KEY `media_id` (`media_id`),
  CONSTRAINT `song_media_ibfk_1` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `song_media_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `songs`;
CREATE TABLE `songs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `image_id` bigint unsigned DEFAULT NULL,
  `status` enum(\'published\',\'draft\') DEFAULT \'draft\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `featured` tinyint(1) DEFAULT \'0\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `songs_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `two_factor_codes`;
CREATE TABLE `two_factor_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `attempts` tinyint unsigned NOT NULL DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `two_factor_codes_user_id_expires_at_index` (`user_id`,`expires_at`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8 COLLATE=utf8_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8 COLLATE=utf8_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE=utf8_unicode_ci NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8 COLLATE=utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8 COLLATE=utf8_unicode_ci DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `status` enum(\'active\',\'inactive\') CHARACTER SET utf8 COLLATE=utf8_unicode_ci DEFAULT \'active\',
  `remember_token` varchar(100) COLLATE=utf8_unicode_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

');

define('EMBEDDED_MIGRATIONS_SQL', '-- Migration records (so artisan migrate --force is a no-op)
DELETE FROM migrations;
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_08_213939_update_recorded_by_column_in_attendance_table\', 1);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_09_113144_add_updated_at_to_site_settings_table\', 2);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_15_000000_add_video_fields_to_homepage_sliders_table\', 3);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_16_000000_make_image_id_nullable_in_homepage_sliders\', 4);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_17_000000_create_hero_settings_table\', 5);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_18_000000_add_title_and_description_to_hero_settings\', 6);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_19_000000_make_slider_text_fields_nullable\', 7);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_09_203426_remove_show_deco_ring_from_hero_settings\', 8);
INSERT INTO migrations (migration, batch) VALUES (\'0001_01_01_000001_create_cache_table\', 10);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_10_000000_add_recurring_fields_to_events_table\', 11);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_10_100000_drop_image_id_from_events_table\', 12);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_10_213644_alter_quotes_make_author_nullable\', 13);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_11_000001_create_about_us_table\', 14);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_11_000002_create_center_locations_table\', 14);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_11_000000_create_galleries_table\', 15);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_11_000001_create_gallery_albums_table\', 16);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_12_230024_create_consent_logs_table\', 17);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000001_add_slug_to_devotionals_table\', 18);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000002_add_slug_to_songs_table\', 18);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000003_add_slug_to_sermons_table\', 1);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000004_add_slug_to_books_table\', 1);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_09_203427_create_ministry_columns_table\', 19);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_15_000000_remove_song_fields_and_categories\', 20);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_16_000000_add_image_id_to_songs_table\', 21);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000001_create_financial_accounts_table\', 22);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000002_create_financial_funds_table\', 22);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000003_create_financial_campaigns_table\', 22);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000004_create_financial_pledges_table\', 22);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_14_000005_create_financial_transactions_table\', 22);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_16_000001_restructure_attendance_table\', 23);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_17_005632_add_other_name_and_center_id_to_church_members_table\', 24);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_19_085214_drop_consent_logs_table\', 25);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_19_000000_remove_secondary_button_from_hero_settings\', 26);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_19_000000_remove_primary_button_from_hero_settings\', 27);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_23_000001_create_password_resets_table\', 28);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_000001_add_city_state_country_nationality_to_church_members_table\', 29);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_000002_create_newsletter_subscribers_table\', 30);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_000003_create_newsletters_table\', 30);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_000010_add_compliance_fields_to_newsletter_subscribers_table\', 31);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_000011_add_tracking_and_schedule_fields_to_newsletters_table\', 32);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_000012_create_newsletter_tracking_table\', 33);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_000000_add_track_order_to_sermon_and_song_media_tables\', 34);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_000013_add_pdf_file_to_books_table\', 35);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_27_204531_add_allow_pdf_download_to_books_table\', 36);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_28_092210_add_button_fields_to_hero_settings_table\', 37);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_31_000000_add_remember_token_to_users_table\', 38);
INSERT INTO migrations (migration, batch) VALUES (\'2026_07_31_100000_create_two_factor_codes_table\', 39);
INSERT INTO migrations (migration, batch) VALUES (\'0001_01_01_000002_create_jobs_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_02_000000_create_activity_logs_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_03_000000_create_attendance_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_04_000000_create_books_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_05_000000_create_church_members_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_06_000000_create_church_staff_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_07_000000_create_contact_messages_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_08_000000_create_devotionals_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_11_000000_create_events_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_12_000000_create_homepage_sections_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_13_000000_create_homepage_services_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_14_000000_create_homepage_sliders_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_16_000000_create_mail_templates_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_17_000000_create_media_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_18_000000_create_member_groups_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_19_000000_create_member_group_assignments_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_20_000000_create_menus_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_21_000000_create_menu_items_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_22_000000_create_message_replies_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_24_000000_create_news_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_25_000000_create_offerings_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_27_000000_create_permissions_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_28_000000_create_quotes_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_29_000000_create_roles_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_30_000000_create_role_permissions_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_01_31_000000_create_sermons_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_01_000000_create_sermon_media_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_02_000000_create_sermon_series_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_03_000000_create_services_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_04_000000_create_service_types_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_05_000000_create_site_settings_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_06_000000_create_songs_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_07_000000_create_song_categories_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_08_000000_create_song_media_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_17_000000_create_users_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2025_02_18_000000_create_user_devotional_progress_table\', 40);
INSERT INTO migrations (migration, batch) VALUES (\'2026_08_02_000000_drop_legacy_orphaned_tables\', 41);
');
