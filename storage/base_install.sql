-- MySQL dump 10.13  Distrib 8.0.19, for macos10.15 (x86_64)
--
-- Host: 127.0.0.1    Database: chattrackpro
-- ------------------------------------------------------
-- Server version	8.0.19

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `adjustments_log`
--

DROP TABLE IF EXISTS `adjustments_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adjustments_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversion_id` int NOT NULL,
  `user_id` int unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `conversion_id` (`conversion_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `adjustments_log_ibfk_1` FOREIGN KEY (`conversion_id`) REFERENCES `conversions` (`id`),
  CONSTRAINT `adjustments_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustments_log`
--

LOCK TABLES `adjustments_log` WRITE;
/*!40000 ALTER TABLE `adjustments_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `adjustments_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `affiliate_email_pools`
--

DROP TABLE IF EXISTS `affiliate_email_pools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliate_email_pools` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `email_pool_id` int unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `affiliate_email_pools_user_id_foreign` (`user_id`),
  KEY `affiliate_email_pools_email_pool_id_foreign` (`email_pool_id`),
  CONSTRAINT `affiliate_email_pools_email_pool_id_foreign` FOREIGN KEY (`email_pool_id`) REFERENCES `email_pools` (`id`),
  CONSTRAINT `affiliate_email_pools_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affiliate_email_pools`
--

LOCK TABLES `affiliate_email_pools` WRITE;
/*!40000 ALTER TABLE `affiliate_email_pools` DISABLE KEYS */;
/*!40000 ALTER TABLE `affiliate_email_pools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banned_users`
--

DROP TABLE IF EXISTS `banned_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banned_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_unique` (`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `banned_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banned_users`
--

LOCK TABLES `banned_users` WRITE;
/*!40000 ALTER TABLE `banned_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `banned_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bonus`
--

DROP TABLE IF EXISTS `bonus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bonus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sales_required` int NOT NULL,
  `payout` double NOT NULL,
  `author` int unsigned NOT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `timestamp` int NOT NULL,
  `inheritable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_author` (`author`),
  CONSTRAINT `FK_author` FOREIGN KEY (`author`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bonus`
--

LOCK TABLES `bonus` WRITE;
/*!40000 ALTER TABLE `bonus` DISABLE KEYS */;
/*!40000 ALTER TABLE `bonus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bonus_offers`
--

DROP TABLE IF EXISTS `bonus_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bonus_offers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `required_sales` int unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `offer_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bonus_offers_offer_id_unique` (`offer_id`),
  CONSTRAINT `bonus_offers_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offer` (`idoffer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bonus_offers`
--

LOCK TABLES `bonus_offers` WRITE;
/*!40000 ALTER TABLE `bonus_offers` DISABLE KEYS */;
/*!40000 ALTER TABLE `bonus_offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaigns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `timestamp` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
INSERT INTO `campaigns` VALUES (2,'DEFAULT',1);
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `click_bonus`
--

DROP TABLE IF EXISTS `click_bonus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `click_bonus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bonus_id` int NOT NULL,
  `aff_id` int unsigned NOT NULL,
  `timestamp` int NOT NULL,
  `payout` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_bonus_id` (`bonus_id`),
  KEY `aff_id` (`aff_id`),
  KEY `date_index` (`timestamp`),
  CONSTRAINT `click_bonus_ibfk_1` FOREIGN KEY (`aff_id`) REFERENCES `rep` (`idrep`),
  CONSTRAINT `FK_bonus_id` FOREIGN KEY (`bonus_id`) REFERENCES `bonus` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `click_bonus`
--

LOCK TABLES `click_bonus` WRITE;
/*!40000 ALTER TABLE `click_bonus` DISABLE KEYS */;
/*!40000 ALTER TABLE `click_bonus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `click_geo`
--

DROP TABLE IF EXISTS `click_geo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `click_geo` (
  `click_id` int unsigned NOT NULL,
  `iso_code` varchar(255) NOT NULL,
  `postal` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  KEY `click_id` (`click_id`),
  CONSTRAINT `click_geo_ibfk_1` FOREIGN KEY (`click_id`) REFERENCES `clicks` (`idclicks`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `click_geo`
--

LOCK TABLES `click_geo` WRITE;
/*!40000 ALTER TABLE `click_geo` DISABLE KEYS */;
/*!40000 ALTER TABLE `click_geo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `click_has_bonus`
--

DROP TABLE IF EXISTS `click_has_bonus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `click_has_bonus` (
  `click_bonus_id` int NOT NULL,
  `click_id` int unsigned NOT NULL,
  KEY `FK_click_bonus_id` (`click_bonus_id`),
  KEY `FK_click_id` (`click_id`),
  CONSTRAINT `FK_click_bonus_id` FOREIGN KEY (`click_bonus_id`) REFERENCES `click_bonus` (`id`),
  CONSTRAINT `FK_click_id` FOREIGN KEY (`click_id`) REFERENCES `clicks` (`idclicks`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `click_has_bonus`
--

LOCK TABLES `click_has_bonus` WRITE;
/*!40000 ALTER TABLE `click_has_bonus` DISABLE KEYS */;
/*!40000 ALTER TABLE `click_has_bonus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `click_vars`
--

DROP TABLE IF EXISTS `click_vars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `click_vars` (
  `click_id` int unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `sub1` varchar(255) NOT NULL DEFAULT '',
  `sub2` varchar(255) NOT NULL DEFAULT '',
  `sub3` varchar(255) NOT NULL DEFAULT '',
  `sub4` varchar(255) NOT NULL DEFAULT '',
  `sub5` varchar(255) NOT NULL DEFAULT '',
  KEY `click_id` (`click_id`),
  CONSTRAINT `click_vars_ibfk_1` FOREIGN KEY (`click_id`) REFERENCES `clicks` (`idclicks`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `click_vars`
--

LOCK TABLES `click_vars` WRITE;
/*!40000 ALTER TABLE `click_vars` DISABLE KEYS */;
/*!40000 ALTER TABLE `click_vars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clicks`
--

DROP TABLE IF EXISTS `clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clicks` (
  `idclicks` int unsigned NOT NULL AUTO_INCREMENT,
  `first_timestamp` datetime DEFAULT NULL,
  `rep_idrep` int unsigned NOT NULL,
  `offer_idoffer` int unsigned NOT NULL,
  `ip_address` varchar(25) DEFAULT NULL,
  `browser_agent` varchar(500) NOT NULL,
  `click_type` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`idclicks`),
  UNIQUE KEY `idclicks_UNIQUE` (`idclicks`),
  KEY `fk_clicks_rep1_idx` (`rep_idrep`),
  KEY `fk_clicks_offer1_idx` (`offer_idoffer`) USING BTREE,
  KEY `date1` (`first_timestamp`),
  KEY `date2` (`first_timestamp`),
  KEY `v2` (`rep_idrep`,`offer_idoffer`,`click_type`,`first_timestamp`) USING BTREE,
  CONSTRAINT `fk_clicks_offer1` FOREIGN KEY (`offer_idoffer`) REFERENCES `offer` (`idoffer`),
  CONSTRAINT `fk_clicks_rep1` FOREIGN KEY (`rep_idrep`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB AUTO_INCREMENT=51202 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clicks`
--

LOCK TABLES `clicks` WRITE;
/*!40000 ALTER TABLE `clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `shortHand` varchar(255) NOT NULL,
  `subDomain` varchar(30) NOT NULL,
  `companyName` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `telephone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `skype` varchar(255) NOT NULL,
  `colors` varchar(255) NOT NULL DEFAULT '484848;FFFFFF;2A58AD;1D4C9E;82A7EB;FCED16;EAEEF1;FFFFFF;404452;999999',
  `uid` varchar(5) NOT NULL,
  `db_version` double NOT NULL,
  `login_url` varchar(255) NOT NULL,
  `landing_page` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company`
--

LOCK TABLES `company` WRITE;
/*!40000 ALTER TABLE `company` DISABLE KEYS */;
/*!40000 ALTER TABLE `company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_login_urls`
--

DROP TABLE IF EXISTS `company_login_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_login_urls` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `status` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `company_login_urls_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_login_urls`
--

LOCK TABLES `company_login_urls` WRITE;
/*!40000 ALTER TABLE `company_login_urls` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_login_urls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversions`
--

DROP TABLE IF EXISTS `conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `click_id` int unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `click_id` (`click_id`),
  KEY `date` (`timestamp`),
  CONSTRAINT `conversions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`),
  CONSTRAINT `conversions_ibfk_2` FOREIGN KEY (`click_id`) REFERENCES `clicks` (`idclicks`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversions`
--

LOCK TABLES `conversions` WRITE;
/*!40000 ALTER TABLE `conversions` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `country_list`
--

DROP TABLE IF EXISTS `country_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `country_list` (
  `idcountry_list` int unsigned NOT NULL AUTO_INCREMENT,
  `country_name` varchar(45) DEFAULT NULL,
  `country_code` varchar(45) DEFAULT NULL,
  `geo_rule_idgeo_rule` int unsigned NOT NULL,
  PRIMARY KEY (`idcountry_list`),
  UNIQUE KEY `idcountry_list_UNIQUE` (`idcountry_list`),
  KEY `fk_country_list_geo_rule_idx` (`geo_rule_idgeo_rule`),
  CONSTRAINT `fk_country_list_geo_rule` FOREIGN KEY (`geo_rule_idgeo_rule`) REFERENCES `geo_rule` (`idgeo_rule`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `country_list`
--

LOCK TABLES `country_list` WRITE;
/*!40000 ALTER TABLE `country_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `country_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deductions`
--

DROP TABLE IF EXISTS `deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deductions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversion_id` int NOT NULL,
  `deduction_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_conversion_index` (`conversion_id`),
  CONSTRAINT `deductions_ibfk_1` FOREIGN KEY (`conversion_id`) REFERENCES `conversions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deductions`
--

LOCK TABLES `deductions` WRITE;
/*!40000 ALTER TABLE `deductions` DISABLE KEYS */;
/*!40000 ALTER TABLE `deductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_list`
--

DROP TABLE IF EXISTS `device_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_list` (
  `iddevice_list` int NOT NULL AUTO_INCREMENT,
  `device_type` varchar(255) NOT NULL,
  `device_rule_iddevice_rule` int unsigned NOT NULL,
  PRIMARY KEY (`iddevice_list`),
  KEY `FK_id_device` (`device_rule_iddevice_rule`),
  CONSTRAINT `FK_id_device` FOREIGN KEY (`device_rule_iddevice_rule`) REFERENCES `device_rule` (`iddevice_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_list`
--

LOCK TABLES `device_list` WRITE;
/*!40000 ALTER TABLE `device_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_rule`
--

DROP TABLE IF EXISTS `device_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_rule` (
  `iddevice_rule` int unsigned NOT NULL AUTO_INCREMENT,
  `rule_idrule` int unsigned NOT NULL,
  PRIMARY KEY (`iddevice_rule`),
  UNIQUE KEY `iddevice_rule_UNIQUE` (`iddevice_rule`),
  KEY `fk_device_rule_rule1_idx` (`rule_idrule`),
  CONSTRAINT `fk_device_rule_rule1` FOREIGN KEY (`rule_idrule`) REFERENCES `rule` (`idrule`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_rule`
--

LOCK TABLES `device_rule` WRITE;
/*!40000 ALTER TABLE `device_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_pools`
--

DROP TABLE IF EXISTS `email_pools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_pools` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_pools`
--

LOCK TABLES `email_pools` WRITE;
/*!40000 ALTER TABLE `email_pools` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_pools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emails` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email_pool_id` int unsigned NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `emails_email_pool_id_foreign` (`email_pool_id`),
  CONSTRAINT `emails_email_pool_id_foreign` FOREIGN KEY (`email_pool_id`) REFERENCES `email_pools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails`
--

LOCK TABLES `emails` WRITE;
/*!40000 ALTER TABLE `emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `error_logs`
--

DROP TABLE IF EXISTS `error_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `error_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class` varchar(255) NOT NULL,
  `error` text NOT NULL,
  `url` text,
  `time_stamp` int NOT NULL,
  `ip` text NOT NULL,
  `error_number` int NOT NULL,
  `resolved` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3668 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `error_logs`
--

LOCK TABLES `error_logs` WRITE;
/*!40000 ALTER TABLE `error_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `error_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `free_sign_ups`
--

DROP TABLE IF EXISTS `free_sign_ups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `free_sign_ups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `click_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `click_id` (`click_id`),
  KEY `user_id` (`user_id`),
  KEY `timestamp` (`timestamp`),
  CONSTRAINT `free_sign_ups_ibfk_1` FOREIGN KEY (`click_id`) REFERENCES `clicks` (`idclicks`),
  CONSTRAINT `free_sign_ups_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `free_sign_ups`
--

LOCK TABLES `free_sign_ups` WRITE;
/*!40000 ALTER TABLE `free_sign_ups` DISABLE KEYS */;
/*!40000 ALTER TABLE `free_sign_ups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_rule`
--

DROP TABLE IF EXISTS `geo_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_rule` (
  `idgeo_rule` int unsigned NOT NULL AUTO_INCREMENT,
  `rule_idrule` int unsigned NOT NULL,
  PRIMARY KEY (`idgeo_rule`),
  UNIQUE KEY `idgeo_rule_UNIQUE` (`idgeo_rule`),
  KEY `fk_geo_rule_rule1_idx` (`rule_idrule`),
  CONSTRAINT `fk_geo_rule_rule1` FOREIGN KEY (`rule_idrule`) REFERENCES `rule` (`idrule`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_rule`
--

LOCK TABLES `geo_rule` WRITE;
/*!40000 ALTER TABLE `geo_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_blacklist`
--

DROP TABLE IF EXISTS `ip_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_blacklist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `start` int unsigned NOT NULL,
  `end` int unsigned NOT NULL,
  `timestamp` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `start` (`start`,`end`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blacklist`
--

LOCK TABLES `ip_blacklist` WRITE;
/*!40000 ALTER TABLE `ip_blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_blacklist_log`
--

DROP TABLE IF EXISTS `ip_blacklist_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_blacklist_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` int unsigned NOT NULL,
  `timestamp` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blacklist_log`
--

LOCK TABLES `ip_blacklist_log` WRITE;
/*!40000 ALTER TABLE `ip_blacklist_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_blacklist_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log` (
  `idlog` int unsigned NOT NULL AUTO_INCREMENT,
  `class` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `time_stamp` int DEFAULT NULL,
  `ip` varchar(255) NOT NULL,
  PRIMARY KEY (`idlog`),
  UNIQUE KEY `idlog_UNIQUE` (`idlog`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logins`
--

DROP TABLE IF EXISTS `logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `repid` int DEFAULT NULL,
  `rep_username` varchar(255) DEFAULT NULL,
  `success` int DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `date` varchar(255) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `last_action_time` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=909 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logins`
--

LOCK TABLES `logins` WRITE;
/*!40000 ALTER TABLE `logins` DISABLE KEYS */;
INSERT INTO `logins` VALUES (908,1,'god',1,'127.0.0.1','2020-08-07','23ee8cec410bcfcb0faa9d7a786c286b0a573d50528948789113f770110b62e3',1596764300);
/*!40000 ALTER TABLE `logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (18,'2018_06_13_205030_create_email_pools_table',1),(19,'2018_06_13_205213_create_emails_table',1),(20,'2018_06_13_205612_create_affilaite_email_pools_table',1),(21,'2018_06_14_160804_add_email_pools_permission',1),(28,'2018_06_25_182010_remove_sale_log_permission',2),(29,'2018_06_25_194216_create_sms_clients_table',2),(30,'2018_06_26_144510_alter_sms_clients_table_user_id_unique',2),(31,'2018_06_27_153016_alter_permissions_table_add_sms_chat_column',2),(32,'2018_07_03_175114_create_bonus_offer_table',2),(33,'2018_07_09_162327_alter_table_bonus_offers_add_unique_index_for_offer_id',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` varchar(500) NOT NULL,
  `timestamp` int NOT NULL,
  `author` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author` (`author`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`author`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer`
--

DROP TABLE IF EXISTS `offer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offer` (
  `idoffer` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by` int NOT NULL,
  `offer_name` varchar(155) DEFAULT NULL,
  `description` varchar(555) DEFAULT NULL,
  `url` varchar(555) NOT NULL,
  `offer_type` tinyint NOT NULL DEFAULT '0',
  `is_public` tinyint DEFAULT '0',
  `payout` decimal(10,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `offer_timestamp` datetime DEFAULT NULL,
  `campaign_id` int NOT NULL,
  `parent` int unsigned DEFAULT NULL,
  PRIMARY KEY (`idoffer`),
  UNIQUE KEY `idoffer_UNIQUE` (`idoffer`),
  KEY `campaign_id` (`campaign_id`),
  KEY `fk_parent` (`parent`),
  KEY `status_index` (`status`),
  CONSTRAINT `fk_parent` FOREIGN KEY (`parent`) REFERENCES `offer` (`idoffer`),
  CONSTRAINT `offer_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=902 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer`
--

LOCK TABLES `offer` WRITE;
/*!40000 ALTER TABLE `offer` DISABLE KEYS */;
/*!40000 ALTER TABLE `offer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_caps`
--

DROP TABLE IF EXISTS `offer_caps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offer_caps` (
  `offer_idoffer` int unsigned NOT NULL,
  `type` int NOT NULL,
  `time_interval` int NOT NULL,
  `interval_cap` int NOT NULL,
  `redirect_offer` int unsigned NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1 is enabled, 0 is disabled',
  `is_capped` tinyint NOT NULL DEFAULT '0',
  KEY `offer_idoffer` (`offer_idoffer`),
  KEY `redirect_offer` (`redirect_offer`),
  CONSTRAINT `offer_caps_ibfk_1` FOREIGN KEY (`offer_idoffer`) REFERENCES `offer` (`idoffer`),
  CONSTRAINT `offer_caps_ibfk_2` FOREIGN KEY (`redirect_offer`) REFERENCES `offer` (`idoffer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_caps`
--

LOCK TABLES `offer_caps` WRITE;
/*!40000 ALTER TABLE `offer_caps` DISABLE KEYS */;
/*!40000 ALTER TABLE `offer_caps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_urls`
--

DROP TABLE IF EXISTS `offer_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offer_urls` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `status` tinyint NOT NULL,
  `company_id` int unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_urls`
--

LOCK TABLES `offer_urls` WRITE;
/*!40000 ALTER TABLE `offer_urls` DISABLE KEYS */;
/*!40000 ALTER TABLE `offer_urls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `repid` int NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `verify` varchar(255) NOT NULL,
  `time_stamp` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `active` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pending_conversions`
--

DROP TABLE IF EXISTS `pending_conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pending_conversions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `click_id` int unsigned NOT NULL,
  `payout` double NOT NULL,
  `converted` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `click_id` (`click_id`),
  CONSTRAINT `pending_conversions_ibfk_1` FOREIGN KEY (`click_id`) REFERENCES `clicks` (`idclicks`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pending_conversions`
--

LOCK TABLES `pending_conversions` WRITE;
/*!40000 ALTER TABLE `pending_conversions` DISABLE KEYS */;
/*!40000 ALTER TABLE `pending_conversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff_id` int unsigned NOT NULL,
  `create_admins` tinyint NOT NULL DEFAULT '0',
  `create_managers` tinyint NOT NULL DEFAULT '0',
  `create_affiliates` tinyint NOT NULL DEFAULT '0',
  `create_offers` tinyint NOT NULL DEFAULT '0',
  `view_postback` tinyint NOT NULL DEFAULT '0',
  `edit_referrals` tinyint NOT NULL DEFAULT '0',
  `edit_offer_rules` tinyint NOT NULL DEFAULT '0',
  `view_fraud_data` tinyint NOT NULL DEFAULT '0',
  `edit_aff_payout` tinyint NOT NULL DEFAULT '0',
  `create_notifications` tinyint NOT NULL DEFAULT '0',
  `create_bonuses` tinyint DEFAULT '0',
  `assign_bonuses` tinyint DEFAULT '0',
  `edit_salaries` tinyint NOT NULL DEFAULT '0',
  `pay_salaries` tinyint NOT NULL DEFAULT '0',
  `edit_offer_urls` tinyint NOT NULL DEFAULT '0',
  `approve_offer_requests` tinyint NOT NULL DEFAULT '0',
  `approve_affiliate_sign_ups` tinyint NOT NULL DEFAULT '0',
  `edit_affiliates` tinyint(1) NOT NULL DEFAULT '0',
  `edit_report_permissions` tinyint NOT NULL DEFAULT '0',
  `adjust_sales` tinyint(1) NOT NULL DEFAULT '0',
  `ban_users` tinyint(1) NOT NULL DEFAULT '0',
  `email_pools` tinyint NOT NULL DEFAULT '0',
  `sms_chat` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `aff_id` (`aff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `privileges`
--

DROP TABLE IF EXISTS `privileges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `privileges` (
  `idprivileges` int unsigned NOT NULL AUTO_INCREMENT,
  `rep_idrep` int unsigned NOT NULL,
  `is_god` tinyint(1) DEFAULT '0',
  `is_manager` tinyint(1) DEFAULT '0',
  `is_admin` tinyint(1) DEFAULT '0',
  `is_rep` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idprivileges`),
  UNIQUE KEY `idprivileges_UNIQUE` (`idprivileges`),
  KEY `fk_privileges_rep1_idx` (`rep_idrep`),
  CONSTRAINT `fk_privileges_rep1` FOREIGN KEY (`rep_idrep`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privileges`
--

LOCK TABLES `privileges` WRITE;
/*!40000 ALTER TABLE `privileges` DISABLE KEYS */;
INSERT INTO `privileges` VALUES (1,1,1,0,0,0);
/*!40000 ALTER TABLE `privileges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referral_deductions`
--

DROP TABLE IF EXISTS `referral_deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referral_deductions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `referrals_paid_id` int unsigned NOT NULL,
  `deduction_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referral_paid` (`referrals_paid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral_deductions`
--

LOCK TABLES `referral_deductions` WRITE;
/*!40000 ALTER TABLE `referral_deductions` DISABLE KEYS */;
/*!40000 ALTER TABLE `referral_deductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referrals` (
  `referrer_user_id` int unsigned NOT NULL,
  `aff_id` int unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL COMMENT 'null = indefinite ',
  `referral_type` varchar(255) NOT NULL COMMENT 'Flat Fee or Percentage ',
  `commission_basis` varchar(255) NOT NULL DEFAULT 'revenue',
  `min_payment_threshhold` int unsigned NOT NULL DEFAULT '0',
  `payout` double unsigned NOT NULL COMMENT 'Amount or Percentage of Commission',
  `is_active` tinyint DEFAULT '1',
  KEY `referrer_user_id` (`referrer_user_id`),
  KEY `aff_id` (`aff_id`),
  CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`referrer_user_id`) REFERENCES `rep` (`idrep`),
  CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`aff_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals`
--

LOCK TABLES `referrals` WRITE;
/*!40000 ALTER TABLE `referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referrals_paid`
--

DROP TABLE IF EXISTS `referrals_paid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referrals_paid` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff_id` int unsigned NOT NULL,
  `referred_aff_id` int unsigned NOT NULL,
  `paid` double NOT NULL,
  `conversion_id` int DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversion_dupe_check` (`conversion_id`),
  KEY `aff_id` (`aff_id`),
  KEY `referred_aff_id` (`referred_aff_id`),
  KEY `date_index` (`timestamp`),
  CONSTRAINT `referrals_paid_ibfk_1` FOREIGN KEY (`aff_id`) REFERENCES `rep` (`idrep`),
  CONSTRAINT `referrals_paid_ibfk_2` FOREIGN KEY (`referred_aff_id`) REFERENCES `rep` (`idrep`),
  CONSTRAINT `referrals_paid_ibfk_3` FOREIGN KEY (`conversion_id`) REFERENCES `conversions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals_paid`
--

LOCK TABLES `referrals_paid` WRITE;
/*!40000 ALTER TABLE `referrals_paid` DISABLE KEYS */;
/*!40000 ALTER TABLE `referrals_paid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rep`
--

DROP TABLE IF EXISTS `rep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rep` (
  `idrep` int unsigned NOT NULL AUTO_INCREMENT COMMENT '	',
  `first_name` varchar(155) DEFAULT NULL,
  `last_name` varchar(155) DEFAULT NULL,
  `cell_phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `user_name` varchar(155) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `referrer_repid` int unsigned NOT NULL,
  `rep_timestamp` datetime DEFAULT NULL,
  `lft` int DEFAULT NULL,
  `rgt` int DEFAULT NULL,
  `skype` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  PRIMARY KEY (`idrep`),
  UNIQUE KEY `idrep_UNIQUE` (`idrep`),
  KEY `fk_rep_rep1_idx` (`referrer_repid`),
  KEY `status_index` (`status`),
  KEY `tree_left` (`lft`),
  KEY `tree_right` (`rgt`),
  CONSTRAINT `fk_rep_rep1` FOREIGN KEY (`referrer_repid`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB AUTO_INCREMENT=1003 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rep`
--

LOCK TABLES `rep` WRITE;
/*!40000 ALTER TABLE `rep` DISABLE KEYS */;
INSERT INTO `rep` VALUES (1,'god','god',NULL,NULL,'god','$2y$10$N4YeNHRQH4i20jGSIPY1GOdNn2wV7dnKhftQzsywivLZo.Z6bLo0O',1,0,'2018-06-23 01:45:44',1,1,NULL,'');
/*!40000 ALTER TABLE `rep` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rep_has_offer`
--

DROP TABLE IF EXISTS `rep_has_offer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rep_has_offer` (
  `idrep_has_offer` int unsigned NOT NULL AUTO_INCREMENT,
  `rep_idrep` int unsigned NOT NULL,
  `offer_idoffer` int unsigned NOT NULL,
  `payout` double NOT NULL,
  `postback_url` text,
  `deduction_postback` varchar(255) NOT NULL DEFAULT '',
  `free_sign_up_postback` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idrep_has_offer`),
  UNIQUE KEY `idrep_has_offer_UNIQUE` (`idrep_has_offer`),
  UNIQUE KEY `rep_idrep` (`rep_idrep`,`offer_idoffer`),
  KEY `fk_rep_has_offer_offer1_idx` (`offer_idoffer`),
  KEY `fk_rep_has_offer_rep_idx` (`rep_idrep`),
  CONSTRAINT `fk_rep_has_offer_offer1` FOREIGN KEY (`offer_idoffer`) REFERENCES `offer` (`idoffer`),
  CONSTRAINT `fk_rep_has_offer_rep` FOREIGN KEY (`rep_idrep`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB AUTO_INCREMENT=555 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rep_has_offer`
--

LOCK TABLES `rep_has_offer` WRITE;
/*!40000 ALTER TABLE `rep_has_offer` DISABLE KEYS */;
/*!40000 ALTER TABLE `rep_has_offer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_permissions`
--

DROP TABLE IF EXISTS `report_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_permissions` (
  `user_id` int unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` tinyint(1) NOT NULL DEFAULT '1',
  `offer_name` tinyint(1) NOT NULL DEFAULT '1',
  `raw_clicks` tinyint(1) NOT NULL DEFAULT '1',
  `unique_clicks` tinyint(1) NOT NULL DEFAULT '1',
  `conversions` tinyint(1) NOT NULL DEFAULT '1',
  `revenue` tinyint(1) NOT NULL DEFAULT '1',
  `epc` tinyint(1) NOT NULL DEFAULT '1',
  `free_sign_ups` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `report_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_permissions`
--

LOCK TABLES `report_permissions` WRITE;
/*!40000 ALTER TABLE `report_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rule`
--

DROP TABLE IF EXISTS `rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rule` (
  `idrule` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `offer_idoffer` int unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `redirect_offer` int NOT NULL,
  `is_active` tinyint unsigned NOT NULL DEFAULT '1',
  `deny` tinyint NOT NULL DEFAULT '0' COMMENT '1 is denied, 0 is allowed',
  PRIMARY KEY (`idrule`),
  UNIQUE KEY `idrule_UNIQUE` (`idrule`),
  KEY `fk_rule_offer1_idx` (`offer_idoffer`),
  CONSTRAINT `fk_rule_offer1` FOREIGN KEY (`offer_idoffer`) REFERENCES `offer` (`idoffer`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rule`
--

LOCK TABLES `rule` WRITE;
/*!40000 ALTER TABLE `rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary`
--

DROP TABLE IF EXISTS `salary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `salary` int unsigned NOT NULL,
  `timestamp` int NOT NULL,
  `last_update` int NOT NULL,
  `status` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `salary_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary`
--

LOCK TABLES `salary` WRITE;
/*!40000 ALTER TABLE `salary` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_log`
--

DROP TABLE IF EXISTS `salary_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `salary_id` int NOT NULL,
  `payout` int NOT NULL DEFAULT '0',
  `reason` varchar(255) DEFAULT NULL,
  `timestamp` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_id` (`salary_id`),
  CONSTRAINT `salary_log_ibfk_1` FOREIGN KEY (`salary_id`) REFERENCES `salary` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_log`
--

LOCK TABLES `salary_log` WRITE;
/*!40000 ALTER TABLE `salary_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_log`
--

DROP TABLE IF EXISTS `sale_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sale_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversion_id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversion_dupe_check` (`conversion_id`),
  KEY `conver_id` (`conversion_id`),
  CONSTRAINT `sale_log_ibfk_1` FOREIGN KEY (`conversion_id`) REFERENCES `conversions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_log`
--

LOCK TABLES `sale_log` WRITE;
/*!40000 ALTER TABLE `sale_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_clients`
--

DROP TABLE IF EXISTS `sms_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_clients` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int unsigned DEFAULT NULL,
  `client_secret` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sms_user_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sms_clients_user_id_unique` (`user_id`),
  CONSTRAINT `sms_clients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_clients`
--

LOCK TABLES `sms_clients` WRITE;
/*!40000 ALTER TABLE `sms_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_has_bonus`
--

DROP TABLE IF EXISTS `user_has_bonus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_has_bonus` (
  `bonus_id` int NOT NULL,
  `user_id` int unsigned NOT NULL,
  KEY `FK_bonus` (`bonus_id`),
  KEY `FK_user` (`user_id`),
  CONSTRAINT `FK_bonus` FOREIGN KEY (`bonus_id`) REFERENCES `bonus` (`id`),
  CONSTRAINT `FK_user` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_has_bonus`
--

LOCK TABLES `user_has_bonus` WRITE;
/*!40000 ALTER TABLE `user_has_bonus` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_has_bonus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_has_notification`
--

DROP TABLE IF EXISTS `user_has_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_has_notification` (
  `notification_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `seen` tinyint NOT NULL DEFAULT '0',
  `deleted` tinyint NOT NULL DEFAULT '0',
  KEY `FK_notification_id` (`notification_id`),
  KEY `FK_user_id` (`user_id`),
  CONSTRAINT `FK_notification_id` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`),
  CONSTRAINT `FK_user_id` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_has_notification`
--

LOCK TABLES `user_has_notification` WRITE;
/*!40000 ALTER TABLE `user_has_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_has_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_postbacks`
--

DROP TABLE IF EXISTS `user_postbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_postbacks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `free_sign_up_url` varchar(255) NOT NULL DEFAULT '',
  `deduction_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_postbacks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_postbacks`
--

LOCK TABLES `user_postbacks` WRITE;
/*!40000 ALTER TABLE `user_postbacks` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_postbacks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-08-13 20:52:14
