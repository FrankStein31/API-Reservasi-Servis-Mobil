/*
SQLyog Enterprise v13.1.1 (64 bit)
MySQL - 8.0.30 : Database - reservasiservismobil
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`reservasiservismobil` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `reservasiservismobil`;

/*Table structure for table `admins` */

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nin` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` enum('1','0') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `level` enum('Admin','Manager','Staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Staff',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `admins` */

insert  into `admins`(`id`,`name`,`username`,`password`,`nin`,`phone`,`address`,`photo`,`is_active`,`level`,`created_at`,`updated_at`) values 
(1,'Admin','admin','$2y$10$/DKDq4ZVXDlF0IyKuvxK4.9rfoOPNzCF8QnAAp7yfsVNCTK8wNZ1e',NULL,NULL,NULL,NULL,'1','Admin',NULL,NULL),
(2,'Manajer','manajer','$2y$10$2PUI1aWG.LwnL2ePOJAOZ.nndaVYzETemfTLK4G9mpgGlelIxWYv6',NULL,NULL,NULL,NULL,'1','Manager',NULL,NULL),
(3,'Karyawan 1','karyawan','$2y$10$JAzJNN0UitozkEfzjkuzHe.rAC1lvVN2LQE8WM9D4MtOtPFYyFpni',NULL,NULL,NULL,NULL,'1','Staff',NULL,NULL);

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `about` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` enum('1','0') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`about`,`is_active`,`created_at`,`updated_at`) values 
(1,'Suku Cadang','Et quisquam modi qui in quia aut.','1',NULL,NULL),
(2,'Aksesories','Rerum fugit quisquam ut rem optio suscipit.','1',NULL,NULL),
(3,'Oli','Voluptas ea error ut animi vel voluptatem.','1',NULL,NULL);

/*Table structure for table `customers` */

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('M','F') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `customers` */

insert  into `customers`(`id`,`name`,`username`,`email`,`email_verified_at`,`password`,`gender`,`phone`,`address`,`photo`,`created_at`,`updated_at`) values 
(1,'Budi','budi','budi@gmail.com','2025-02-05 01:36:19','$2y$10$KPapjJS/4jWJxbMO3jU1eeYAJ6RBn4TYMLeqJS0nRFZk/WoZZb/d6','M',NULL,NULL,NULL,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(2,'Katheryn Ferry','ymarvin','gregoria.krajcik@example.org','2025-02-05 01:36:19','$2y$10$JrY3G4yQpM8SvoqZiH1Ld.JZ30z30B9MLuG48oO2RkGjRTVhf40e2','M',NULL,NULL,NULL,'2025-02-05 01:36:20','2025-02-05 01:36:20'),
(3,'Mr. Archibald Hammes I','hammes.meta','wbergnaum@example.net','2025-02-05 01:36:19','$2y$10$kTZgjEPA6SIE9VWdG1aPoua58nGvtUeG1skNm5dQSqNY35Lb3GYxK','F',NULL,NULL,NULL,'2025-02-05 01:36:20','2025-02-05 01:36:20'),
(4,'Miss Rose Bartell DVM','deven.hettinger','lebsack.oswaldo@example.com','2025-02-05 01:36:20','$2y$10$65aIJr9CrckzMHcjC7I5a.tv6/kwDthsH620nZcuI1H.fgVIwO/mK','M',NULL,NULL,NULL,'2025-02-05 01:36:20','2025-02-05 01:36:20'),
(6,'Sherman Nader DDS','nicole31','schultz.susie@example.net','2025-02-05 01:36:20','$2y$10$H7Nf3Dr.q/fhJJ8gxE.jWe3uH6UdkVVqZh2tjOfaqoPWw4worz8ku','M',NULL,NULL,NULL,'2025-02-05 01:36:20','2025-02-05 01:36:20'),
(7,'Tono','tono','koko0@gmail.com',NULL,'$2y$10$jUJsUN2c5mPEiVIhYcpgWe373FYO8QHV2hc8tpZeDtGE5I/XdaigC','M','6289602093439','Kediri',NULL,'2025-02-25 21:35:39','2025-02-25 21:35:39'),
(8,'frankie','frank','frankie.steinlie@gmail.com',NULL,'$2y$10$Cf6o6rBp9oIyCkyXrlhhLO/or2HiNFV2.L3bsGOu7CnnmoX7W0086','M','6289602093439','medan',NULL,NULL,NULL),
(9,'Steinlie','stein','steinlie@gmail.com',NULL,'$2y$10$1aww7jcvg/wEc4fWoVxg5e1CLaLwQf4t0JrCVBl1.fBm0OB6MnPtu','M','62881036568273','kediri',NULL,NULL,NULL),
(10,'coba','coba','coba@gmail.com',NULL,'$2y$10$gYO3u7ZUTTjMwNgVHnojk.jqvsprgTcPX7OO68yy93r8S/9fsm0m6','M','6285645127013','medan',NULL,NULL,NULL);

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `failed_jobs` */

/*Table structure for table `mechanics` */

DROP TABLE IF EXISTS `mechanics`;

CREATE TABLE `mechanics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nin` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` enum('1','0') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `mechanics` */

insert  into `mechanics`(`id`,`name`,`nin`,`phone`,`address`,`photo`,`is_active`,`created_at`,`updated_at`) values 
(1,'Mr. Rowland Waters','3528533889345960',NULL,NULL,NULL,'1','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(2,'Mrs. Kira Klein','3589430571197203',NULL,NULL,NULL,'1','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(3,'Dee Von','2221141677663594',NULL,NULL,NULL,'1','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(4,'Theo Murray DDS','4716185960163703',NULL,NULL,NULL,'1','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(5,'Chris Barton','5224297603284895',NULL,NULL,NULL,'1','2025-02-05 01:36:20','2025-02-05 01:36:20');

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values 
(1,'2014_10_12_000000_create_users_table',1),
(2,'2014_10_12_100000_create_password_resets_table',1),
(3,'2016_06_01_000001_create_oauth_auth_codes_table',1),
(4,'2016_06_01_000002_create_oauth_access_tokens_table',1),
(5,'2016_06_01_000003_create_oauth_refresh_tokens_table',1),
(6,'2016_06_01_000004_create_oauth_clients_table',1),
(7,'2016_06_01_000005_create_oauth_personal_access_clients_table',1),
(8,'2019_08_19_000000_create_failed_jobs_table',1),
(9,'2021_06_09_182715_create_admins_table',1),
(10,'2021_06_09_183433_create_mechanics_table',1),
(11,'2021_06_09_183600_create_customers_table',1),
(12,'2021_06_09_184039_create_vehicles_table',1),
(13,'2021_06_09_184751_create_categories_table',1),
(14,'2021_06_09_184820_create_products_table',1),
(15,'2021_06_12_075124_create_packages_table',1),
(16,'2021_06_12_130605_create_package_products_table',1),
(17,'2021_06_12_130924_create_reservations_table',1),
(18,'2021_06_12_133641_create_services_table',1),
(19,'2021_06_12_135736_create_payments_table',1),
(20,'2021_08_04_174823_create_months_table',1),
(21,'2021_08_15_042959_create_notifications_table',1);

/*Table structure for table `months` */

DROP TABLE IF EXISTS `months`;

CREATE TABLE `months` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `months` */

insert  into `months`(`id`,`number`,`name`,`created_at`,`updated_at`) values 
(1,'1','Januari',NULL,NULL),
(2,'2','Februari',NULL,NULL),
(3,'3','Maret',NULL,NULL),
(4,'4','April',NULL,NULL),
(5,'5','Mei',NULL,NULL),
(6,'6','Juni',NULL,NULL),
(7,'7','Juli',NULL,NULL),
(8,'8','Agustus',NULL,NULL),
(9,'9','September',NULL,NULL),
(10,'10','Oktober',NULL,NULL),
(11,'11','November',NULL,NULL),
(12,'12','Desember',NULL,NULL);

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `recipient_id` int DEFAULT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` enum('1','0') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `notifications` */

/*Table structure for table `oauth_access_tokens` */

DROP TABLE IF EXISTS `oauth_access_tokens`;

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `client_id` bigint unsigned NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `oauth_access_tokens` */

/*Table structure for table `oauth_auth_codes` */

DROP TABLE IF EXISTS `oauth_auth_codes`;

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `oauth_auth_codes` */

/*Table structure for table `oauth_clients` */

DROP TABLE IF EXISTS `oauth_clients`;

CREATE TABLE `oauth_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `oauth_clients` */

/*Table structure for table `oauth_personal_access_clients` */

DROP TABLE IF EXISTS `oauth_personal_access_clients`;

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `oauth_personal_access_clients` */

/*Table structure for table `oauth_refresh_tokens` */

DROP TABLE IF EXISTS `oauth_refresh_tokens`;

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `oauth_refresh_tokens` */

/*Table structure for table `package_products` */

DROP TABLE IF EXISTS `package_products`;

CREATE TABLE `package_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_products_package_id_foreign` (`package_id`),
  KEY `package_products_product_id_foreign` (`product_id`),
  CONSTRAINT `package_products_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `package_products` */

insert  into `package_products`(`id`,`package_id`,`product_id`,`created_at`,`updated_at`) values 
(1,1,1,NULL,NULL),
(2,1,2,NULL,NULL),
(3,2,2,NULL,NULL),
(4,2,3,NULL,NULL),
(5,3,1,NULL,NULL);

/*Table structure for table `packages` */

DROP TABLE IF EXISTS `packages`;

CREATE TABLE `packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `about` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `packages` */

insert  into `packages`(`id`,`name`,`about`,`created_at`,`updated_at`) values 
(1,'Ganti Oli A','Voluptatem dolorum quia facilis ut molestias.',NULL,NULL),
(2,'Ganti Oli B','In et blanditiis magni aliquam aliquam nemo.',NULL,NULL),
(3,'Full Servis','Totam accusamus et voluptas magnam sunt.',NULL,NULL);

/*Table structure for table `password_resets` */

DROP TABLE IF EXISTS `password_resets`;

CREATE TABLE `password_resets` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `password_resets` */

/*Table structure for table `payments` */

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint unsigned NOT NULL,
  `admin_id` bigint unsigned NOT NULL DEFAULT '1',
  `bill` int NOT NULL,
  `pay` int NOT NULL,
  `change` int NOT NULL,
  `method` enum('Cash','Card') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Cash',
  `note` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_service_id_foreign` (`service_id`),
  KEY `payments_admin_id_foreign` (`admin_id`),
  CONSTRAINT `payments_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`),
  CONSTRAINT `payments_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `payments` */

insert  into `payments`(`id`,`service_id`,`admin_id`,`bill`,`pay`,`change`,`method`,`note`,`snap_token`,`created_at`,`updated_at`) values 
(14,28,1,225000,225000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-28-1744695849','89a887d4-493b-43e2-94c3-37a15caf01f7',NULL,NULL),
(15,29,1,225000,225000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-29-1744697772','128066a9-79a3-4242-891d-467bf1c1dc46',NULL,NULL),
(16,32,1,185000,185000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-32-1745135436','c7b71cfe-e128-44d7-9fa2-e92d208a9eb9',NULL,NULL),
(19,41,1,185000,185000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-41-1745724741','1c0979db-e441-47b3-8958-a69ac10fb07e',NULL,NULL),
(20,44,1,225000,225000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-44-1745725022','520d387d-7a23-4666-bb7e-4231ca934d45',NULL,NULL),
(21,46,1,225000,225000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-46-1746006822','83914e62-1ef2-4651-83ad-96cf3f675e0a',NULL,NULL),
(22,47,1,225000,225000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-47-1746010413','0c39375f-523c-42d4-88c9-ae1ddeec59c4',NULL,NULL),
(23,48,1,185000,185000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-48-1746052290','b5d5a0fb-01fd-4619-8c49-c1d563f87816',NULL,NULL),
(24,49,1,75000,75000,0,'Card','Pembayaran via Midtrans | Order ID: SERVICE-49-1746053517','e96370cc-570e-4f70-b26e-a31b5e52063d',NULL,NULL);

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `about` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` enum('1','0') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `category_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `products` */

insert  into `products`(`id`,`name`,`price`,`about`,`picture`,`is_active`,`category_id`,`created_at`,`updated_at`) values 
(1,'AliceBlue 847',75000,'And yesterday things I.',NULL,'1',3,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(2,'MidnightBlue 548',150000,'Northumbria, declared.',NULL,'1',2,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(3,'LightCyan 005',35000,'March Hare. The King.',NULL,'1',3,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(4,'NavajoWhite 010',150000,'Who Stole the accident.',NULL,'1',3,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(5,'HotPink 682',85000,'Footman seemed too much.',NULL,'1',3,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(6,'SlateGray 163',35000,'Hatter went mad, you.',NULL,'1',3,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(7,'Coral 554',35000,'William the King. On.',NULL,'1',2,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(8,'RoyalBlue 507',50000,'I am to get in a row of.',NULL,'1',1,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(9,'Cornsilk 368',50000,'The moment down with.',NULL,'1',2,'2025-02-05 01:36:19','2025-02-05 01:36:19'),
(10,'MintCream 386',75000,'Alice\'s great hall, but.',NULL,'1',2,'2025-02-05 01:36:19','2025-02-05 01:36:19');

/*Table structure for table `reservations` */

DROP TABLE IF EXISTS `reservations`;

CREATE TABLE `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `package_id` bigint unsigned DEFAULT NULL,
  `vehicle_id` bigint unsigned DEFAULT NULL,
  `package_detail` json NOT NULL,
  `vehicle_complaint` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `reservation_origin` enum('Online','Offline') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Online',
  `attendance_confirmation` enum('Present','Not Present') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendance_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservations_customer_id_foreign` (`customer_id`),
  KEY `reservations_package_id_foreign` (`package_id`),
  KEY `reservations_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `reservations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `reservations_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`),
  CONSTRAINT `reservations_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `reservations` */

insert  into `reservations`(`id`,`customer_id`,`package_id`,`vehicle_id`,`package_detail`,`vehicle_complaint`,`reservation_date`,`reservation_time`,`reservation_origin`,`attendance_confirmation`,`attendance_message`,`created_at`,`updated_at`) values 
(28,8,1,12,'{\"id\": 1, \"name\": \"Ganti Oli A\", \"price\": 225000, \"products\": [{\"id\": 1, \"name\": \"AliceBlue 847\", \"price\": 75000}, {\"id\": 2, \"name\": \"MidnightBlue 548\", \"price\": 150000}], \"description\": null}','asdasd','2025-04-16','09:00:00','Online',NULL,NULL,NULL,NULL),
(29,8,1,11,'{\"id\": 1, \"name\": \"Ganti Oli A\", \"price\": 225000, \"products\": [{\"id\": 1, \"name\": \"AliceBlue 847\", \"price\": 75000}, {\"id\": 2, \"name\": \"MidnightBlue 548\", \"price\": 150000}], \"description\": null}','aasdasd','2025-04-16','11:00:00','Online',NULL,NULL,NULL,NULL),
(32,9,2,16,'{\"id\": 2, \"name\": \"Ganti Oli B\", \"price\": 185000, \"products\": [{\"id\": 2, \"name\": \"MidnightBlue 548\", \"price\": 150000}, {\"id\": 3, \"name\": \"LightCyan 005\", \"price\": 35000}], \"description\": null}','werwr','2025-04-21','10:00:00','Online',NULL,NULL,NULL,NULL),
(41,8,2,12,'{\"id\": 2, \"name\": \"Ganti Oli B\", \"price\": 185000, \"products\": [{\"id\": 2, \"name\": \"MidnightBlue 548\", \"price\": 150000}, {\"id\": 3, \"name\": \"LightCyan 005\", \"price\": 35000}], \"description\": null}','gabut','2025-04-28','13:00:00','Online',NULL,NULL,NULL,NULL),
(44,10,1,19,'{\"id\": 1, \"name\": \"Ganti Oli A\", \"price\": 225000, \"products\": [{\"id\": 1, \"name\": \"AliceBlue 847\", \"price\": 75000}, {\"id\": 2, \"name\": \"MidnightBlue 548\", \"price\": 150000}], \"description\": null}','coba fonte','2025-04-28','13:00:00','Online',NULL,NULL,NULL,NULL),
(46,8,1,11,'{\"id\": 1, \"name\": \"Ganti Oli A\", \"price\": 225000, \"products\": [{\"id\": 1, \"name\": \"AliceBlue 847\", \"price\": 75000}, {\"id\": 2, \"name\": \"MidnightBlue 548\", \"price\": 150000}], \"description\": null}','wasd coba','2025-05-01','15:00:00','Online',NULL,NULL,NULL,NULL),
(47,8,1,12,'{\"id\": 1, \"name\": \"Ganti Oli A\", \"price\": 225000, \"products\": [{\"id\": 1, \"name\": \"AliceBlue 847\", \"price\": 75000}, {\"id\": 2, \"name\": \"MidnightBlue 548\", \"price\": 150000}], \"description\": null}','asd','2025-05-01','14:00:00','Online',NULL,NULL,NULL,NULL),
(48,8,2,12,'{\"id\": 2, \"name\": \"Ganti Oli B\", \"price\": 185000, \"products\": [{\"id\": 2, \"name\": \"MidnightBlue 548\", \"price\": 150000}, {\"id\": 3, \"name\": \"LightCyan 005\", \"price\": 35000}], \"description\": null}','gaada','2025-05-02','14:00:00','Online',NULL,NULL,NULL,NULL),
(49,10,3,19,'{\"id\": 3, \"name\": \"Full Servis\", \"price\": 75000, \"products\": [{\"id\": 1, \"name\": \"AliceBlue 847\", \"price\": 75000}], \"description\": null}','oli bocor','2025-05-05','14:00:00','Online',NULL,NULL,NULL,NULL);

/*Table structure for table `services` */

DROP TABLE IF EXISTS `services`;

CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `service_date` date DEFAULT NULL,
  `next_service_date` date DEFAULT NULL,
  `status` enum('Pending','Process','Finish','Selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `note` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fee` int DEFAULT NULL,
  `mechanic_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_reservation_id_foreign` (`reservation_id`),
  KEY `services_mechanic_id_foreign` (`mechanic_id`),
  CONSTRAINT `services_mechanic_id_foreign` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanics` (`id`),
  CONSTRAINT `services_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `services` */

insert  into `services`(`id`,`reservation_id`,`service_date`,`next_service_date`,`status`,`note`,`fee`,`mechanic_id`,`created_at`,`updated_at`) values 
(28,28,'2025-04-15',NULL,'Finish',NULL,NULL,1,NULL,'2025-04-15 12:43:49'),
(29,29,'2025-04-15',NULL,'Finish',NULL,NULL,3,NULL,'2025-04-15 13:15:44'),
(32,32,'2025-04-20',NULL,'Finish',NULL,NULL,4,NULL,'2025-04-20 14:49:20'),
(41,41,'2025-04-27',NULL,'Finish',NULL,NULL,3,NULL,'2025-04-27 10:31:37'),
(44,44,'2025-04-27',NULL,'Finish',NULL,NULL,1,NULL,'2025-04-27 10:35:40'),
(46,46,'2025-04-30',NULL,'Finish',NULL,NULL,NULL,NULL,'2025-04-30 16:53:00'),
(47,47,'2025-04-30',NULL,'Finish',NULL,NULL,3,NULL,'2025-04-30 17:53:14'),
(48,48,'2025-05-01',NULL,'Finish',NULL,NULL,2,NULL,'2025-05-01 05:25:41'),
(49,49,'2025-05-01',NULL,'Finish',NULL,NULL,2,NULL,'2025-05-01 05:51:37');

/*Table structure for table `vehicles` */

DROP TABLE IF EXISTS `vehicles`;

CREATE TABLE `vehicles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plate_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chassis_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicles_customer_id_foreign` (`customer_id`),
  CONSTRAINT `vehicles_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `vehicles` */

insert  into `vehicles`(`id`,`customer_id`,`name`,`brand`,`year`,`plate_number`,`chassis_number`,`created_at`,`updated_at`) values 
(1,3,'Apt. 617','North','2007','AA 89306 XX','1074659286','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(2,4,'Suite 517','East','1991','AA 78302 XX','1082276448','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(3,3,'Suite 936','Port','2022','AA 56071 XX','1624135602','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(4,4,'Apt. 824','Port','1982','AA 22076 XX','88021563','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(5,4,'Apt. 635','New','2013','AA 87398 XX','1080830173','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(6,3,'Suite 103','Port','2018','AA 03860 XX','273152051','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(7,2,'Apt. 165','Port','1977','AA 02043 XX','610434211','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(8,4,'Apt. 642','Lake','2009','AA 12729 XX','507593700','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(9,1,'Suite 600','Port','1978','AA 93781 XX','1101357538','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(10,1,'Suite 812','New','1997','AA 37720 XX','1353142104','2025-02-05 01:36:20','2025-02-05 01:36:20'),
(11,8,'vario','honda','2024','ag 2222 xx','123123123',NULL,NULL),
(12,8,'beat','honda','2111','ad 1123 aa','123123123',NULL,NULL),
(13,9,'vario','hondaaaaaa','1111','AG 1111 bb','123123123',NULL,NULL),
(14,9,'civic','honda','2013','ad 1212','123123123',NULL,NULL),
(16,9,'apa','apa','3131','aa 1212','112312',NULL,NULL),
(18,9,'coba','coba','2222','aa 1111 as','123123',NULL,NULL),
(19,10,'avanza','toyota','2021','ag 1123 ag','123123',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
