-- MySQL dump 10.13  Distrib 5.7.44, for Linux (x86_64)
--
-- Host: localhost    Database: goobinator
-- ------------------------------------------------------
-- Server version	5.7.44-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ItemSeller`
--

DROP TABLE IF EXISTS `ItemSeller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ItemSeller` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ItemID` int(11) DEFAULT NULL,
  `InventoryID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Price` int(11) DEFAULT NULL,
  `TimeCreated` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ItemSeller_Unique` (`ItemID`,`InventoryID`,`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ItemSeller`
--

LOCK TABLES `ItemSeller` WRITE;
/*!40000 ALTER TABLE `ItemSeller` DISABLE KEYS */;
/*!40000 ALTER TABLE `ItemSeller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ItemSellerHistory`
--

DROP TABLE IF EXISTS `ItemSellerHistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ItemSellerHistory` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UTLID` int(11) DEFAULT NULL,
  `ItemID` int(11) DEFAULT NULL,
  `SellerID` int(11) DEFAULT NULL,
  `BuyerID` int(11) DEFAULT NULL,
  `Price` int(11) DEFAULT NULL,
  `TimeSale` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ItemSellerHistory`
--

LOCK TABLES `ItemSellerHistory` WRITE;
/*!40000 ALTER TABLE `ItemSellerHistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `ItemSellerHistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UserTrade`
--

DROP TABLE IF EXISTS `UserTrade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UserTrade` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RequesterID` int(11) DEFAULT NULL,
  `ReceiverID` int(11) DEFAULT NULL,
  `Status` int(11) DEFAULT '0',
  `Time` int(11) DEFAULT NULL,
  `Expires` int(11) DEFAULT NULL,
  `GivingOne` int(11) DEFAULT '0',
  `GivingTwo` int(11) DEFAULT '0',
  `GivingThree` int(11) DEFAULT '0',
  `GivingFour` int(11) DEFAULT '0',
  `GivingCredits` int(11) DEFAULT '0',
  `WantOne` int(11) DEFAULT '0',
  `WantTwo` int(11) DEFAULT '0',
  `WantThree` int(11) DEFAULT '0',
  `WantFour` int(11) DEFAULT '0',
  `WantCredits` int(11) DEFAULT '0',
  `UpdatedOn` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UserTrade`
--

LOCK TABLES `UserTrade` WRITE;
/*!40000 ALTER TABLE `UserTrade` DISABLE KEYS */;
/*!40000 ALTER TABLE `UserTrade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `maintenance` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true/false-enabled/disabled',
  `maintenanceCode` varchar(255) NOT NULL DEFAULT 'vawawawgawg1g2g2g2gdsggsdgxcbbsfqafgaaFAFAW',
  `alertText` varchar(255) NOT NULL,
  PRIMARY KEY (`maintenance`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuration`
--

LOCK TABLES `configuration` WRITE;
/*!40000 ALTER TABLE `configuration` DISABLE KEYS */;
/*!40000 ALTER TABLE `configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_posts`
--

DROP TABLE IF EXISTS `forum_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `subforum` int(11) NOT NULL DEFAULT '1',
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `lastposttime` int(11) NOT NULL,
  `lastpostauthor` int(11) DEFAULT NULL,
  `lastpostid` int(11) DEFAULT NULL,
  `timemade` int(11) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_author` (`author`),
  KEY `idx_subforum` (`subforum`),
  KEY `idx_lastpostauthor` (`lastpostauthor`),
  KEY `idx_lastpostid` (`lastpostid`),
  KEY `idx_timemade` (`timemade`),
  KEY `idx_pinned` (`pinned`),
  KEY `idx_locked` (`locked`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_posts`
--

LOCK TABLES `forum_posts` WRITE;
/*!40000 ALTER TABLE `forum_posts` DISABLE KEYS */;
INSERT INTO `forum_posts` VALUES (2,54,'i dont nate higgers guys','pls dont kill me','2025-09-03 01:47:24','2025-10-23 00:36:25',1,0,0,1761179785,54,2,1756864044,3),(3,54,'What\'s better, chicken sandwich or pb and j','which one do y\'all want','2025-09-03 12:02:31','2025-12-15 18:04:41',2,0,0,1765821881,54,3,1756900951,2),(4,54,'sigma ligma','ggdffsafadf','2025-10-19 19:24:38','2025-10-19 19:41:25',1,0,0,1760901878,54,4,1760901878,1),(5,54,'Looking for people to help me with fixing the website fully','look if you have a computer and a brain you can help out, AI can sometimes help fix the stuff in the site, but I can fix the stuff that happens, just ive been real busy with school so I need someone to help also.','2025-10-28 11:31:43','2025-10-28 11:46:30',1,1,0,1761651103,54,5,1761651103,1),(6,54,'Bricktaria is literally so tuff fr fr ong','if you think otherwise you will infact get tickled brotato','2025-12-16 12:32:16','2025-12-16 12:32:16',3,0,0,1765888336,54,6,1765888336,1);
/*!40000 ALTER TABLE `forum_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_replies`
--

DROP TABLE IF EXISTS `forum_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `author` int(11) NOT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `thread` int(11) NOT NULL,
  `timemade` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_author` (`author`),
  KEY `idx_post_id` (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_replies`
--

LOCK TABLES `forum_replies` WRITE;
/*!40000 ALTER TABLE `forum_replies` DISABLE KEYS */;
INSERT INTO `forum_replies` VALUES (1,1,54,'waddup','2025-09-03 02:11:20','2025-09-03 02:11:20',1,'2025-09-02 22:11:20'),(2,1,54,'waddup','2025-09-03 02:12:42','2025-09-03 02:12:42',1,'2025-09-02 22:12:42'),(3,2,69,'right..','2025-10-23 00:35:59','2025-10-23 00:35:59',2,'2025-10-22 20:35:59'),(4,2,54,'goober','2025-10-23 00:36:25','2025-10-23 00:36:25',2,'2025-10-22 20:36:25'),(5,3,54,'goober','2025-12-15 18:04:41','2025-12-15 18:04:41',3,'2025-12-15 13:04:41');
/*!40000 ALTER TABLE `forum_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `sender` int(11) DEFAULT NULL,
  `receiver` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friends`
--

LOCK TABLES `friends` WRITE;
/*!40000 ALTER TABLE `friends` DISABLE KEYS */;
/*!40000 ALTER TABLE `friends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `item` int(11) DEFAULT NULL,
  `serial` int(11) DEFAULT NULL,
  `price` int(11) NOT NULL DEFAULT '0',
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_bans`
--

DROP TABLE IF EXISTS `ip_bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(250) DEFAULT NULL,
  `num_rows` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_bans`
--

LOCK TABLES `ip_bans` WRITE;
/*!40000 ALTER TABLE `ip_bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_bans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_comments`
--

DROP TABLE IF EXISTS `item_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_comments` (
  `id` int(11) NOT NULL,
  `item` int(11) DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `body` varchar(256) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_comments`
--

LOCK TABLES `item_comments` WRITE;
/*!40000 ALTER TABLE `item_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `item_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `desc` varchar(1000) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `creator` int(11) DEFAULT NULL,
  `offsale` int(11) NOT NULL DEFAULT '0',
  `cost` int(11) DEFAULT NULL,
  `sales` int(11) NOT NULL DEFAULT '0',
  `time_created` int(11) DEFAULT NULL,
  `time_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_attempts`
--

DROP TABLE IF EXISTS `post_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip_timestamp` (`ip`,`timestamp`),
  KEY `idx_user_timestamp` (`user_id`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_attempts`
--

LOCK TABLES `post_attempts` WRITE;
/*!40000 ALTER TABLE `post_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocodes`
--

DROP TABLE IF EXISTS `promocodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promocodes` (
  `code` varchar(255) NOT NULL,
  `itemid` varchar(100) NOT NULL DEFAULT '0',
  `uses` int(11) NOT NULL DEFAULT '-1',
  `used` int(11) NOT NULL DEFAULT '0',
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  `expirationdate` timestamp NOT NULL DEFAULT '2037-12-31 00:00:00',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocodes`
--

LOCK TABLES `promocodes` WRITE;
/*!40000 ALTER TABLE `promocodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `promocodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `read`
--

DROP TABLE IF EXISTS `read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `read` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `postId` int(11) NOT NULL,
  `readAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_post` (`userId`,`postId`),
  KEY `idx_userId` (`userId`),
  KEY `idx_postId` (`postId`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `read`
--

LOCK TABLES `read` WRITE;
/*!40000 ALTER TABLE `read` DISABLE KEYS */;
INSERT INTO `read` VALUES (1,54,2,'2025-09-03 01:48:45'),(2,54,1,'2025-09-03 01:52:26'),(3,55,1,'2025-09-03 07:22:38'),(4,58,1,'2025-09-03 10:29:29'),(5,54,3,'2025-09-03 12:02:31'),(6,59,2,'2025-09-03 13:26:12'),(7,62,2,'2025-09-03 18:07:10'),(8,62,3,'2025-09-03 18:07:36'),(9,54,4,'2025-10-19 19:24:38'),(10,69,2,'2025-10-23 00:35:14'),(11,54,5,'2025-10-28 11:31:43'),(12,54,6,'2025-12-16 12:32:16');
/*!40000 ALTER TABLE `read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_logs`
--

DROP TABLE IF EXISTS `security_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `details` text,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_logs`
--

LOCK TABLES `security_logs` WRITE;
/*!40000 ALTER TABLE `security_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sitesettings`
--

DROP TABLE IF EXISTS `sitesettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sitesettings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Maintenance` tinyint(1) NOT NULL DEFAULT '0',
  `Registration` tinyint(4) DEFAULT '1',
  `Upgrades` tinyint(4) DEFAULT '1',
  `CatalogPurchases` tinyint(4) DEFAULT '1',
  `BannerAlertMessage` varchar(1000) COLLATE utf8mb4_bin DEFAULT NULL,
  `AllowGroups` tinyint(4) DEFAULT '1',
  `AllowEditCharacter` tinyint(4) DEFAULT '1',
  `AllowTrades` tinyint(4) DEFAULT '1',
  `GameMaintenance` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sitesettings`
--

LOCK TABLES `sitesettings` WRITE;
/*!40000 ALTER TABLE `sitesettings` DISABLE KEYS */;
/*!40000 ALTER TABLE `sitesettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_comments`
--

DROP TABLE IF EXISTS `store_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `comment` text NOT NULL,
  `timecommented` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moderation` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_comments`
--

LOCK TABLES `store_comments` WRITE;
/*!40000 ALTER TABLE `store_comments` DISABLE KEYS */;
INSERT INTO `store_comments` VALUES (1,63,2,'drip','2025-09-03 22:00:46',1),(2,63,1,'so professional','2025-09-03 22:01:52',1),(3,59,2,'am i first or second here','2025-10-23 00:42:17',1),(4,54,87,'Hello','2025-11-28 00:24:18',1);
/*!40000 ALTER TABLE `store_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_items`
--

DROP TABLE IF EXISTS `store_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT 'UNNAMEDITEM',
  `about` varchar(250) DEFAULT NULL,
  `creator` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `tag1` varchar(50) DEFAULT NULL,
  `tag2` varchar(50) DEFAULT NULL,
  `tag3` varchar(50) DEFAULT NULL,
  `price` int(11) NOT NULL DEFAULT '0',
  `tradeable` tinyint(1) NOT NULL DEFAULT '0',
  `offsale` tinyint(1) NOT NULL DEFAULT '1',
  `moderation` tinyint(1) NOT NULL DEFAULT '0',
  `obj` varchar(250) DEFAULT NULL,
  `special` int(11) NOT NULL DEFAULT '0',
  `texture` varchar(250) DEFAULT NULL,
  `image` varchar(250) DEFAULT NULL,
  `owners` int(11) NOT NULL DEFAULT '0',
  `timeupdated` int(11) DEFAULT NULL,
  `timecreated` int(11) DEFAULT NULL,
  `public` int(11) NOT NULL DEFAULT '1',
  `stock` int(11) NOT NULL DEFAULT '-1',
  `allowcomments` int(11) NOT NULL DEFAULT '1',
  `expire` int(11) DEFAULT NULL,
  `isFavorited` int(11) NOT NULL DEFAULT '0',
  `isOnWishlist` int(11) NOT NULL DEFAULT '0',
  `wishlistCount` int(11) NOT NULL DEFAULT '0',
  `favorites` int(11) NOT NULL DEFAULT '0',
  `TradeLock` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_items`
--

LOCK TABLES `store_items` WRITE;
/*!40000 ALTER TABLE `store_items` DISABLE KEYS */;
INSERT INTO `store_items` VALUES (1,'goob','deez',54,8,NULL,NULL,NULL,3,0,0,1,NULL,0,NULL,'1a10c0d200b5622af60ce8da05a1c573',1,1756865920,1756865920,1,-1,1,NULL,0,0,0,0,0),(2,'gray epicness','best avatoria item',63,3,NULL,NULL,NULL,4,0,0,1,NULL,0,NULL,'90f93f9339baf78de30cc0b5d9998a82',2,1756936813,1756936813,1,-1,1,NULL,0,0,0,0,0),(6,'Cappier cap','Automatically added from existing files',54,1,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'f6ec8998cfa43b0806e7ab7c96dce788',2,1764189338,1764188447,1,-1,1,NULL,0,0,0,0,0),(7,'Cap? LOL','Automatically added from existing files',54,1,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'0385dc0a341ccd62498357ef98ad8ed1',1,1764189349,1764188449,1,-1,1,NULL,0,0,0,0,0),(8,'Cap','Automatically added from existing files',54,1,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'b0ea13d47def888a9531418770561008',0,1764189326,1764188451,1,-1,1,NULL,0,0,0,0,0),(65,'Gear #65','Automatically added from existing files',54,5,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'60462e2fb2bd097edf4a9ac0cb0a42c7',0,1764188437,1764188437,1,-1,1,NULL,0,0,0,0,0),(66,'Gear #66','Automatically added from existing files',54,5,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'377f809e34d831ec1ee54b54c7a8b72f',0,1764188439,1764188439,1,-1,1,NULL,0,0,0,0,0),(80,'Gear #80','Automatically added from existing files',54,5,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'fdc3c5202ffc40ee698c3b7d4a6f7514',0,1764188441,1764188441,1,-1,1,NULL,0,0,0,0,0),(87,'Gear #87','Automatically added from existing files',54,5,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'6094de806709129a8bc0646b4e867678',0,1764188443,1764188443,1,-1,1,NULL,0,0,0,0,0),(89,'Gear #89','Automatically added from existing files',54,5,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'2ddae37801fdacc6d7a1ad171f73440f',0,1764188445,1764188445,1,-1,1,NULL,0,0,0,0,0),(172,'Gear #172','Rotten banana',54,5,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'772ea21264bd10dcac60c11931e45f3a',0,1766537558,1764188430,1,-1,1,NULL,0,0,0,0,0),(269,'Gear #269','Automatically added from existing files',54,5,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'0f5c19aafaad028a7cb2b7d2adf115fb',0,1764188432,1764188432,1,-1,1,NULL,0,0,0,0,0),(270,'Gear #270','Automatically added from existing files',54,5,NULL,NULL,NULL,0,1,0,1,NULL,0,NULL,'7fd1742025ad3f92cc3eeb90cfcc9f63',1,1764188434,1764188434,1,-1,1,NULL,0,0,0,0,0);
/*!40000 ALTER TABLE `store_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_ads`
--

DROP TABLE IF EXISTS `user_ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` varchar(255) NOT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_ads`
--

LOCK TABLES `user_ads` WRITE;
/*!40000 ALTER TABLE `user_ads` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_ads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_adverts`
--

DROP TABLE IF EXISTS `user_adverts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_adverts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT 'Advertisement',
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(3000) NOT NULL,
  `budget` int(11) NOT NULL DEFAULT '0',
  `type` int(11) DEFAULT NULL,
  `duration` int(12) DEFAULT '0',
  `moderation` int(11) NOT NULL DEFAULT '0',
  `running` int(11) NOT NULL DEFAULT '1',
  `impressions` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_adverts`
--

LOCK TABLES `user_adverts` WRITE;
/*!40000 ALTER TABLE `user_adverts` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_adverts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_badges`
--

DROP TABLE IF EXISTS `user_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL,
  `buid` int(11) DEFAULT NULL,
  `bnam` text COLLATE utf8mb4_unicode_ci,
  `bimg` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_badges`
--

LOCK TABLES `user_badges` WRITE;
/*!40000 ALTER TABLE `user_badges` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_bans`
--

DROP TABLE IF EXISTS `user_bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reason` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_bans`
--

LOCK TABLES `user_bans` WRITE;
/*!40000 ALTER TABLE `user_bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_bans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_colors`
--

DROP TABLE IF EXISTS `user_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `head` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  `torso` varchar(255) NOT NULL DEFAULT '0.033105, 0.313989, 0.708376',
  `left_arm` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  `right_arm` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  `left_leg` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  `right_leg` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_colors`
--

LOCK TABLES `user_colors` WRITE;
/*!40000 ALTER TABLE `user_colors` DISABLE KEYS */;
INSERT INTO `user_colors` VALUES (54,54,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.806952, 0.070360, 0.045186','0, 0, 0'),(55,55,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(56,56,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(57,57,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(58,58,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(59,59,'0.527115, 0.039546, 0.024158','0.033105, 0.313989, 0.708376','0.527115, 0.039546, 0.024158','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(60,60,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(61,61,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(62,62,'1.000000, 0.708376, 0.412543','0.527115, 0.039546, 0.024158','1.000000, 0.708376, 0.412543','1.000000, 0.708376, 0.412543','0.806952, 0.070360, 0.045186','0.723055, 0.791298, 0.806952'),(63,63,'1.000000, 0.708376, 0.412543','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.033105, 0.313989, 0.708376'),(64,64,'0.508881, 0.545725, 0.577581','0.270498, 0.056128, 0.417885','0.723055, 0.791298, 0.806952','0.371238, 0.124772, 0.520996','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(65,65,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(66,66,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(67,67,'0.887923, 0.558341, 0.005605','0.270498, 0.056128, 0.417885','0.887923, 0.558341, 0.005605','0.887923, 0.558341, 0.005605','0.027321, 0.603827, 0.162029','0.723055, 0.791298, 0.806952'),(68,68,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(69,69,'0.723055, 0.791298, 0.806952','0.046665, 0.090842, 0.155926','0.723055, 0.791298, 0.806952','0.887923, 0.558341, 0.005605','0.013702, 0.502886, 0.332452','0.723055, 0.791298, 0.806952'),(70,70,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(71,71,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(72,72,'0.508881, 0.545725, 0.577581','0.527115, 0.039546, 0.024158','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(73,73,'0.806952, 0.070360, 0.045186','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(74,74,'0, 0, 0','0, 0, 0','0, 0, 0','0, 0, 0','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(75,75,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(76,76,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952'),(77,77,'0.723055, 0.791298, 0.806952','0.033105, 0.313989, 0.708376','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952','0.723055, 0.791298, 0.806952');
/*!40000 ALTER TABLE `user_colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_data`
--

DROP TABLE IF EXISTS `user_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `hat1` int(11) DEFAULT NULL,
  `hat2` int(11) DEFAULT '0',
  `hat3` int(11) DEFAULT '0',
  `face` varchar(11) NOT NULL DEFAULT 'default',
  `accessory` int(11) DEFAULT '0',
  `tshirt` int(11) DEFAULT '0',
  `shirt` int(11) DEFAULT '0',
  `pants` int(11) DEFAULT '0',
  `gear` int(11) DEFAULT '0',
  `head` int(11) DEFAULT '0',
  `torso` int(11) DEFAULT '0',
  `leftarm` int(11) DEFAULT '0',
  `rightarm` int(11) DEFAULT '0',
  `leftleg` int(11) DEFAULT '0',
  `rightleg` int(11) DEFAULT '0',
  `avatar_refresh` int(11) NOT NULL DEFAULT '1640026419',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_data`
--

LOCK TABLES `user_data` WRITE;
/*!40000 ALTER TABLE `user_data` DISABLE KEYS */;
INSERT INTO `user_data` VALUES (54,54,8,0,0,'0',0,1,0,0,66,0,0,0,0,0,0,1640026419),(55,55,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(56,56,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(57,57,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(58,58,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(59,59,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(60,60,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(61,61,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(62,62,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(63,63,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(64,64,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(65,65,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(66,66,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(67,67,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(68,68,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(69,69,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(70,70,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(71,71,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(72,72,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(73,73,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(74,74,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(75,75,6,0,0,'0',0,0,0,0,270,0,0,0,0,0,0,1640026419),(76,76,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419),(77,77,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,1640026419);
/*!40000 ALTER TABLE `user_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_friends`
--

DROP TABLE IF EXISTS `user_friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_friends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) DEFAULT NULL,
  `receiver` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_friends`
--

LOCK TABLES `user_friends` WRITE;
/*!40000 ALTER TABLE `user_friends` DISABLE KEYS */;
INSERT INTO `user_friends` VALUES (1,54,57,0,1756900713),(2,59,54,1,1756905905),(3,54,61,0,1756912776),(4,62,54,1,1756917436),(5,62,55,0,1756917450),(6,62,60,0,1756917488),(7,62,61,0,1756917492),(8,63,54,1,1756934781),(9,64,54,1,1756990784),(10,59,63,1,1757003404),(11,67,54,1,1758201551),(12,54,69,1,1760904713),(13,54,70,0,1761783200),(14,54,71,1,1764348581),(15,54,73,1,1765201865),(16,74,54,1,1765919432),(17,54,75,1,1765979415);
/*!40000 ALTER TABLE `user_friends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group_members`
--

DROP TABLE IF EXISTS `user_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `groupid` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `timejoined` int(11) DEFAULT NULL,
  `logo` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group_members`
--

LOCK TABLES `user_group_members` WRITE;
/*!40000 ALTER TABLE `user_group_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group_ranks`
--

DROP TABLE IF EXISTS `user_group_ranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group_ranks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `groupid` int(11) DEFAULT NULL,
  `indicator` int(11) DEFAULT NULL,
  `members` int(11) NOT NULL DEFAULT '0',
  `allow_edit_comments` tinyint(1) NOT NULL DEFAULT '0',
  `allow_edit_members` tinyint(1) NOT NULL DEFAULT '0',
  `allow_edit_group` tinyint(1) NOT NULL DEFAULT '0',
  `allow_all` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group_ranks`
--

LOCK TABLES `user_group_ranks` WRITE;
/*!40000 ALTER TABLE `user_group_ranks` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group_ranks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(600) DEFAULT NULL,
  `creator` int(11) DEFAULT NULL,
  `members` int(11) DEFAULT NULL,
  `moderation_status` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `logo` varchar(250) DEFAULT NULL,
  `timecreated` int(11) DEFAULT NULL,
  `allowcomments` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_groups`
--

LOCK TABLES `user_groups` WRITE;
/*!40000 ALTER TABLE `user_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_history`
--

DROP TABLE IF EXISTS `user_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `action` varchar(1000) DEFAULT NULL,
  `timemoderated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_history`
--

LOCK TABLES `user_history` WRITE;
/*!40000 ALTER TABLE `user_history` DISABLE KEYS */;
INSERT INTO `user_history` VALUES (1,54,54,'Granted Premium',1756866161),(2,54,2,'Put item offsale',1764057220),(3,54,2,'Put item onsale',1764057221);
/*!40000 ALTER TABLE `user_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_inboxes`
--

DROP TABLE IF EXISTS `user_inboxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_inboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) DEFAULT NULL,
  `recipient` int(11) DEFAULT NULL,
  `subject` varchar(120) NOT NULL DEFAULT 'No Subject Given',
  `content` varchar(4000) NOT NULL DEFAULT 'No Content Given.',
  `archived` int(11) NOT NULL DEFAULT '0',
  `hasread` int(11) NOT NULL DEFAULT '0',
  `timesent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_inboxes`
--

LOCK TABLES `user_inboxes` WRITE;
/*!40000 ALTER TABLE `user_inboxes` DISABLE KEYS */;
INSERT INTO `user_inboxes` VALUES (1,54,61,'hello cro','wasup',0,0,'2025-09-03 15:20:27'),(2,54,63,'hey dude','did you see the new additions I made',0,1,'2025-09-03 21:29:45'),(3,54,62,'the icon is a discord app image because','Idk someone did that before me but I&#039;ll fix it up dont worry',0,1,'2025-09-03 21:32:05'),(4,54,62,'ill fix the verification when i get back from school','trust trust ',0,1,'2025-09-04 16:23:11'),(5,68,69,'yoo','gay',0,1,'2025-10-23 00:49:53'),(6,54,73,'goob','youre a goob',0,0,'2025-12-08 14:06:52');
/*!40000 ALTER TABLE `user_inboxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_inventory`
--

DROP TABLE IF EXISTS `user_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `item` int(11) DEFAULT NULL,
  `timebought` int(11) DEFAULT NULL,
  `serial` int(11) DEFAULT NULL,
  `serialID` int(11) DEFAULT NULL,
  `CanTrade` int(15) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_inventory`
--

LOCK TABLES `user_inventory` WRITE;
/*!40000 ALTER TABLE `user_inventory` DISABLE KEYS */;
INSERT INTO `user_inventory` VALUES (1,54,1,1756865920,NULL,NULL,1),(2,63,2,1756936813,NULL,NULL,1),(3,54,2,1764057192,NULL,0,1),(4,54,172,1764188430,NULL,NULL,1),(5,55,172,1764188430,NULL,NULL,1),(6,56,172,1764188430,NULL,NULL,1),(7,57,172,1764188430,NULL,NULL,1),(8,58,172,1764188430,NULL,NULL,1),(9,59,172,1764188430,NULL,NULL,1),(10,60,172,1764188430,NULL,NULL,1),(11,61,172,1764188430,NULL,NULL,1),(12,62,172,1764188430,NULL,NULL,1),(13,63,172,1764188430,NULL,NULL,1),(14,64,172,1764188430,NULL,NULL,1),(15,65,172,1764188430,NULL,NULL,1),(16,66,172,1764188430,NULL,NULL,1),(17,67,172,1764188430,NULL,NULL,1),(18,68,172,1764188430,NULL,NULL,1),(19,69,172,1764188430,NULL,NULL,1),(20,70,172,1764188430,NULL,NULL,1),(21,54,269,1764188432,NULL,NULL,1),(22,55,269,1764188432,NULL,NULL,1),(23,56,269,1764188432,NULL,NULL,1),(24,57,269,1764188432,NULL,NULL,1),(25,58,269,1764188432,NULL,NULL,1),(26,59,269,1764188432,NULL,NULL,1),(27,60,269,1764188432,NULL,NULL,1),(28,61,269,1764188432,NULL,NULL,1),(29,62,269,1764188432,NULL,NULL,1),(30,63,269,1764188432,NULL,NULL,1),(31,64,269,1764188432,NULL,NULL,1),(32,65,269,1764188432,NULL,NULL,1),(33,66,269,1764188432,NULL,NULL,1),(34,67,269,1764188432,NULL,NULL,1),(35,68,269,1764188432,NULL,NULL,1),(36,69,269,1764188432,NULL,NULL,1),(37,70,269,1764188432,NULL,NULL,1),(38,54,270,1764188435,NULL,NULL,1),(39,55,270,1764188435,NULL,NULL,1),(40,56,270,1764188435,NULL,NULL,1),(41,57,270,1764188435,NULL,NULL,1),(42,58,270,1764188435,NULL,NULL,1),(43,59,270,1764188435,NULL,NULL,1),(44,60,270,1764188435,NULL,NULL,1),(45,61,270,1764188435,NULL,NULL,1),(46,62,270,1764188435,NULL,NULL,1),(47,63,270,1764188435,NULL,NULL,1),(48,64,270,1764188435,NULL,NULL,1),(49,65,270,1764188435,NULL,NULL,1),(50,66,270,1764188435,NULL,NULL,1),(51,67,270,1764188435,NULL,NULL,1),(52,68,270,1764188435,NULL,NULL,1),(53,69,270,1764188435,NULL,NULL,1),(54,70,270,1764188435,NULL,NULL,1),(55,54,65,1764188437,NULL,NULL,1),(56,55,65,1764188437,NULL,NULL,1),(57,56,65,1764188437,NULL,NULL,1),(58,57,65,1764188437,NULL,NULL,1),(59,58,65,1764188437,NULL,NULL,1),(60,59,65,1764188437,NULL,NULL,1),(61,60,65,1764188437,NULL,NULL,1),(62,61,65,1764188437,NULL,NULL,1),(63,62,65,1764188437,NULL,NULL,1),(64,63,65,1764188437,NULL,NULL,1),(65,64,65,1764188437,NULL,NULL,1),(66,65,65,1764188437,NULL,NULL,1),(67,66,65,1764188437,NULL,NULL,1),(68,67,65,1764188437,NULL,NULL,1),(69,68,65,1764188437,NULL,NULL,1),(70,69,65,1764188437,NULL,NULL,1),(71,70,65,1764188437,NULL,NULL,1),(72,54,66,1764188439,NULL,NULL,1),(73,55,66,1764188439,NULL,NULL,1),(74,56,66,1764188439,NULL,NULL,1),(75,57,66,1764188439,NULL,NULL,1),(76,58,66,1764188439,NULL,NULL,1),(77,59,66,1764188439,NULL,NULL,1),(78,60,66,1764188439,NULL,NULL,1),(79,61,66,1764188439,NULL,NULL,1),(80,62,66,1764188439,NULL,NULL,1),(81,63,66,1764188439,NULL,NULL,1),(82,64,66,1764188439,NULL,NULL,1),(83,65,66,1764188439,NULL,NULL,1),(84,66,66,1764188439,NULL,NULL,1),(85,67,66,1764188439,NULL,NULL,1),(86,68,66,1764188439,NULL,NULL,1),(87,69,66,1764188439,NULL,NULL,1),(88,70,66,1764188439,NULL,NULL,1),(89,54,80,1764188441,NULL,NULL,1),(90,55,80,1764188441,NULL,NULL,1),(91,56,80,1764188441,NULL,NULL,1),(92,57,80,1764188441,NULL,NULL,1),(93,58,80,1764188441,NULL,NULL,1),(94,59,80,1764188441,NULL,NULL,1),(95,60,80,1764188441,NULL,NULL,1),(96,61,80,1764188441,NULL,NULL,1),(97,62,80,1764188441,NULL,NULL,1),(98,63,80,1764188441,NULL,NULL,1),(99,64,80,1764188441,NULL,NULL,1),(100,65,80,1764188441,NULL,NULL,1),(101,66,80,1764188441,NULL,NULL,1),(102,67,80,1764188441,NULL,NULL,1),(103,68,80,1764188441,NULL,NULL,1),(104,69,80,1764188441,NULL,NULL,1),(105,70,80,1764188441,NULL,NULL,1),(106,54,87,1764188443,NULL,NULL,1),(107,55,87,1764188443,NULL,NULL,1),(108,56,87,1764188443,NULL,NULL,1),(109,57,87,1764188443,NULL,NULL,1),(110,58,87,1764188443,NULL,NULL,1),(111,59,87,1764188443,NULL,NULL,1),(112,60,87,1764188443,NULL,NULL,1),(113,61,87,1764188443,NULL,NULL,1),(114,62,87,1764188443,NULL,NULL,1),(115,63,87,1764188443,NULL,NULL,1),(116,64,87,1764188443,NULL,NULL,1),(117,65,87,1764188443,NULL,NULL,1),(118,66,87,1764188443,NULL,NULL,1),(119,67,87,1764188443,NULL,NULL,1),(120,68,87,1764188443,NULL,NULL,1),(121,69,87,1764188443,NULL,NULL,1),(122,70,87,1764188443,NULL,NULL,1),(123,54,89,1764188445,NULL,NULL,1),(124,55,89,1764188445,NULL,NULL,1),(125,56,89,1764188445,NULL,NULL,1),(126,57,89,1764188445,NULL,NULL,1),(127,58,89,1764188445,NULL,NULL,1),(128,59,89,1764188445,NULL,NULL,1),(129,60,89,1764188445,NULL,NULL,1),(130,61,89,1764188445,NULL,NULL,1),(131,62,89,1764188445,NULL,NULL,1),(132,63,89,1764188445,NULL,NULL,1),(133,64,89,1764188445,NULL,NULL,1),(134,65,89,1764188445,NULL,NULL,1),(135,66,89,1764188445,NULL,NULL,1),(136,67,89,1764188445,NULL,NULL,1),(137,68,89,1764188445,NULL,NULL,1),(138,69,89,1764188445,NULL,NULL,1),(139,70,89,1764188445,NULL,NULL,1),(140,54,6,1764188447,NULL,NULL,1),(141,55,6,1764188447,NULL,NULL,1),(142,56,6,1764188447,NULL,NULL,1),(143,57,6,1764188447,NULL,NULL,1),(144,58,6,1764188447,NULL,NULL,1),(145,59,6,1764188447,NULL,NULL,1),(146,60,6,1764188447,NULL,NULL,1),(147,61,6,1764188447,NULL,NULL,1),(148,62,6,1764188447,NULL,NULL,1),(149,63,6,1764188447,NULL,NULL,1),(150,64,6,1764188447,NULL,NULL,1),(151,65,6,1764188447,NULL,NULL,1),(152,66,6,1764188447,NULL,NULL,1),(153,67,6,1764188447,NULL,NULL,1),(154,68,6,1764188447,NULL,NULL,1),(155,69,6,1764188447,NULL,NULL,1),(156,70,6,1764188447,NULL,NULL,1),(157,54,7,1764188449,NULL,NULL,1),(158,55,7,1764188449,NULL,NULL,1),(159,56,7,1764188449,NULL,NULL,1),(160,57,7,1764188449,NULL,NULL,1),(161,58,7,1764188449,NULL,NULL,1),(162,59,7,1764188449,NULL,NULL,1),(163,60,7,1764188449,NULL,NULL,1),(164,61,7,1764188449,NULL,NULL,1),(165,62,7,1764188449,NULL,NULL,1),(166,63,7,1764188449,NULL,NULL,1),(167,64,7,1764188449,NULL,NULL,1),(168,65,7,1764188449,NULL,NULL,1),(169,66,7,1764188449,NULL,NULL,1),(170,67,7,1764188449,NULL,NULL,1),(171,68,7,1764188449,NULL,NULL,1),(172,69,7,1764188449,NULL,NULL,1),(173,70,7,1764188449,NULL,NULL,1),(174,54,8,1764188451,NULL,NULL,1),(175,55,8,1764188451,NULL,NULL,1),(176,56,8,1764188451,NULL,NULL,1),(177,57,8,1764188451,NULL,NULL,1),(178,58,8,1764188451,NULL,NULL,1),(179,59,8,1764188451,NULL,NULL,1),(180,60,8,1764188451,NULL,NULL,1),(181,61,8,1764188451,NULL,NULL,1),(182,62,8,1764188451,NULL,NULL,1),(183,63,8,1764188451,NULL,NULL,1),(184,64,8,1764188451,NULL,NULL,1),(185,65,8,1764188451,NULL,NULL,1),(186,66,8,1764188451,NULL,NULL,1),(187,67,8,1764188451,NULL,NULL,1),(188,68,8,1764188451,NULL,NULL,1),(189,69,8,1764188451,NULL,NULL,1),(190,70,8,1764188451,NULL,NULL,1),(191,75,6,1765979228,NULL,0,1),(192,75,270,1765981429,NULL,0,1),(193,76,7,1766550962,NULL,0,1),(194,77,6,1766550976,NULL,0,1);
/*!40000 ALTER TABLE `user_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profile_customization`
--

DROP TABLE IF EXISTS `user_profile_customization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_profile_customization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `theme` varchar(50) DEFAULT 'default',
  `background_color` varchar(7) DEFAULT '#1a1a2e',
  `accent_color` varchar(7) DEFAULT '#6c5ce7',
  `text_color` varchar(7) DEFAULT '#ffffff',
  `background_image` text,
  `background_music` text,
  `custom_css` text,
  `profile_font` varchar(50) DEFAULT 'Inter',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_profile_customization_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profile_customization`
--

LOCK TABLES `user_profile_customization` WRITE;
/*!40000 ALTER TABLE `user_profile_customization` DISABLE KEYS */;
INSERT INTO `user_profile_customization` VALUES (1,54,'custom','#f093fb','#f5576c','#ec7979','https://hips.hearstapps.com/hmg-prod/images/f6ggka-67a4b1aa28d9f.jpg?crop=0.655xw:1.00xh;0.0612xw,0&resize=640:*','','','Inter','2025-09-03 20:40:03','2025-11-23 04:40:08'),(2,63,'default','#1e3c72','#6c5ce7','#ffffff','','','','Inter','2025-09-03 21:29:06','2025-09-03 21:30:22'),(3,64,'pink','#f093fb','#dcb2b8','#ffffff','','','','Inter','2025-09-04 12:57:20','2025-09-04 12:58:47'),(4,59,'sunset','#fa709a','#fee140','#ffffff','','','','Comic Sans MS','2025-09-04 16:30:18','2025-09-04 16:30:54'),(5,62,'sunset','#ff3838','#eb6c05','#ffffff','','','','Inter','2025-09-04 17:05:03','2025-09-04 17:12:20'),(6,67,'sunset','#ffbd2e','#ff0000','#ffffff','','','','Roboto','2025-09-18 13:15:42','2025-09-18 13:16:48'),(7,68,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-06 03:28:52','2025-10-06 03:28:52'),(8,57,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-07 04:03:26','2025-10-07 04:03:26'),(9,69,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-19 18:25:50','2025-10-19 18:25:50'),(10,66,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-20 00:04:02','2025-10-20 00:04:02'),(11,55,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-20 06:25:59','2025-10-20 06:25:59'),(12,56,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-20 06:26:00','2025-10-20 06:26:00'),(13,58,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-20 06:26:02','2025-10-20 06:26:02'),(14,60,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-20 06:26:03','2025-10-20 06:26:03'),(15,61,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-20 06:26:05','2025-10-20 06:26:05'),(16,65,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-20 06:26:10','2025-10-20 06:26:10'),(17,70,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-10-30 00:13:16','2025-10-30 00:13:16'),(18,71,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-11-28 16:41:37','2025-11-28 16:41:37'),(19,72,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-12-01 12:16:59','2025-12-01 12:16:59'),(20,73,'custom','#0000f5','#524400','#5f1b1b','','','','Orbitron','2025-12-08 13:16:56','2025-12-08 13:50:45'),(21,74,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-12-16 21:11:26','2025-12-16 21:11:26'),(22,75,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-12-17 13:47:43','2025-12-17 13:47:43'),(23,76,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-12-24 04:34:45','2025-12-24 04:34:45'),(24,77,'default','#1a1a2e','#6c5ce7','#ffffff','','','','Inter','2025-12-24 04:35:22','2025-12-24 04:35:22');
/*!40000 ALTER TABLE `user_profile_customization` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_promocodes`
--

DROP TABLE IF EXISTS `user_promocodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_promocodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user` int(11) NOT NULL,
  `timeredeemed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_promocodes`
--

LOCK TABLES `user_promocodes` WRITE;
/*!40000 ALTER TABLE `user_promocodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_promocodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_statuses`
--

DROP TABLE IF EXISTS `user_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_statuses`
--

LOCK TABLES `user_statuses` WRITE;
/*!40000 ALTER TABLE `user_statuses` DISABLE KEYS */;
INSERT INTO `user_statuses` VALUES (1,69,'the',1761179705),(2,59,'the',1761180080),(3,54,'goobler',1761180233);
/*!40000 ALTER TABLE `user_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_trades`
--

DROP TABLE IF EXISTS `user_trades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_trades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) DEFAULT NULL,
  `recipient` int(11) DEFAULT NULL,
  `trade_data` varchar(65000) NOT NULL,
  `add_coins` int(11) DEFAULT '0',
  `add_cash` int(11) NOT NULL DEFAULT '0',
  `UpdatedOn` int(11) DEFAULT NULL,
  `Status` int(11) DEFAULT NULL,
  `trade_date` int(11) DEFAULT NULL,
  `Expires` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_trades`
--

LOCK TABLES `user_trades` WRITE;
/*!40000 ALTER TABLE `user_trades` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_trades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_transaction`
--

DROP TABLE IF EXISTS `user_transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(1000) DEFAULT NULL,
  `item_type` int(11) DEFAULT NULL,
  `buyer_name` varchar(1000) DEFAULT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `date` int(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_transaction`
--

LOCK TABLES `user_transaction` WRITE;
/*!40000 ALTER TABLE `user_transaction` DISABLE KEYS */;
INSERT INTO `user_transaction` VALUES (1,'gray epicness',2,'BellTower',54,4,1764057192),(2,'Cappier cap',6,'thestank2',75,0,1765979228),(3,'Gear #270',270,'thestank2',75,0,1765981429),(4,'Cap? LOL',7,'nig',76,0,1766550962),(5,'Cappier cap',6,'0064',77,0,1766550976);
/*!40000 ALTER TABLE `user_transaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_usernames`
--

DROP TABLE IF EXISTS `user_usernames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_usernames` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_usernames`
--

LOCK TABLES `user_usernames` WRITE;
/*!40000 ALTER TABLE `user_usernames` DISABLE KEYS */;
INSERT INTO `user_usernames` VALUES (54,54,'BellTower','2025-10-19 16:11:32'),(55,55,'Delta','2025-10-19 16:11:32'),(56,56,'Ben','2025-10-19 16:11:32'),(57,57,'Bomb','2025-10-19 16:11:32'),(58,58,'FTK','2025-10-19 16:11:32'),(59,59,'phil','2025-10-19 16:11:32'),(60,60,'daburger','2025-10-19 16:11:32'),(61,61,'CowCow','2025-10-19 16:11:32'),(62,62,'gox','2025-10-19 16:11:32'),(63,63,'r10az','2025-10-19 16:11:32'),(64,64,'Misa','2025-10-19 16:11:32'),(65,65,'IPay69','2025-10-19 16:11:32'),(66,66,'unicorngirl954','2025-10-19 16:11:32'),(67,67,'rougeshrimp6969','2025-10-19 16:11:32'),(68,68,'Oblivion','2025-10-19 16:11:32'),(69,69,'thestank','2025-10-19 16:11:32'),(70,70,'odd','2025-10-29 18:52:53'),(71,71,'Era','2025-11-28 11:40:29'),(72,72,'myn','2025-11-29 20:28:31'),(73,73,'Sorry_snow','2025-12-08 07:57:01'),(74,74,'null','2025-12-16 16:09:58'),(75,75,'thestank2','2025-12-17 08:46:33'),(76,76,'nig','2025-12-23 23:32:47'),(77,77,'0064','2025-12-23 23:34:12');
/*!40000 ALTER TABLE `user_usernames` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `useractionlogs`
--

DROP TABLE IF EXISTS `useractionlogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `useractionlogs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `Action` text,
  `TimeLog` int(11) DEFAULT NULL,
  `IP` varchar(60) DEFAULT NULL,
  `ArchiveID` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `UserActionLog_UserID_FK_idx` (`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `useractionlogs`
--

LOCK TABLES `useractionlogs` WRITE;
/*!40000 ALTER TABLE `useractionlogs` DISABLE KEYS */;
INSERT INTO `useractionlogs` VALUES (2,54,'Logged Into Their Account.',1753334105,'192.168.0.109',0),(3,54,'Logged Into Their Account.',1753334110,'192.168.0.109',0),(4,54,'Logged Into Their Account.',1753858287,'192.168.0.112',0),(5,54,'Logged Into Their Account.',1753858292,'192.168.0.112',0),(6,54,'Logged Into Their Account.',1753858296,'192.168.0.112',0),(7,54,'Logged Into Their Account.',1753858301,'192.168.0.112',0),(8,54,'Logged Into Their Account.',1753858306,'192.168.0.112',0),(9,54,'Logged Into Their Account.',1754405362,'192.168.0.112',0),(10,54,'Logged Into Their Account.',1755698390,'74.82.60.62',0),(11,54,'Logged Into Their Account.',1755839081,'192.168.0.112',0),(12,54,'Logged Into Their Account.',1755839085,'192.168.0.112',0),(13,54,'Logged Into Their Account.',1756144392,'65.49.2.18',0),(14,54,'Logged Into Their Account.',1756332045,'192.168.0.112',0),(15,54,'Logged Into Their Account.',1756745148,'192.168.0.110',0),(16,54,'Logged Into Their Account.',1756787121,'192.168.0.110',0),(17,55,'Logged Into Their Account.',1756884087,'24.251.101.67',0),(18,55,'Logged Into Their Account.',1756884092,'24.251.101.67',0),(19,55,'Logged Into Their Account.',1756884097,'24.251.101.67',0),(20,54,'Logged Into Their Account.',1756900672,'65.49.68.35',0),(21,61,'Logged Into Their Account.',1756909827,'84.234.249.153',0),(22,61,'Logged Into Their Account.',1756910090,'84.234.249.153',0),(23,NULL,'Logged Out Of Their Account.',1756910294,'84.234.249.153',0),(24,NULL,'Logged Out Of Their Account.',1756910307,'84.234.249.153',0),(25,62,'Logged Into Their Account.',1756917263,'82.0.32.255',0),(26,59,'Logged Into Their Account.',1756929630,'189.97.3.186',0),(27,59,'Logged Into Their Account.',1756929635,'189.97.3.186',0),(28,NULL,'Logged Out Of Their Account.',1756932046,'192.168.0.110',0),(29,63,'Logged Into Their Account.',1756936920,'73.63.91.69',0),(30,54,'Logged Into Their Account.',1756949885,'192.168.0.110',0),(31,54,'Logged Into Their Account.',1756960491,'192.168.0.110',0),(32,54,'Logged Into Their Account.',1756960495,'192.168.0.110',0),(33,54,'Logged Into Their Account.',1756960500,'192.168.0.110',0),(34,54,'Logged Into Their Account.',1756960505,'192.168.0.110',0),(35,54,'Logged Into Their Account.',1756960510,'192.168.0.110',0),(36,54,'Logged Into Their Account.',1756960514,'192.168.0.110',0),(37,54,'Logged Into Their Account.',1756960519,'192.168.0.110',0),(38,54,'Logged Into Their Account.',1756990591,'65.49.2.61',0),(39,62,'Logged Into Their Account.',1757001478,'82.0.32.255',0),(40,59,'Logged Into Their Account.',1757003306,'189.97.3.186',0),(41,63,'Logged Into Their Account.',1757054478,'73.63.91.69',0),(42,54,'Logged Into Their Account.',1757072198,'65.49.2.31',0),(43,54,'Logged Into Their Account.',1757072203,'65.49.2.31',0),(44,62,'Logged Into Their Account.',1757090133,'82.0.32.255',0),(45,62,'Logged Into Their Account.',1757152828,'82.0.32.255',0),(46,62,'Logged Into Their Account.',1757223264,'82.0.32.255',0),(47,62,'Logged Into Their Account.',1757362785,'82.0.32.255',0),(48,62,'Logged Into Their Account.',1757426585,'82.0.32.255',0),(49,62,'Logged Into Their Account.',1757623068,'82.0.32.255',0),(50,59,'Logged Into Their Account.',1758145927,'179.84.88.66',0),(51,59,'Logged Into Their Account.',1758145932,'179.84.88.66',0),(52,59,'Logged Into Their Account.',1758219819,'179.84.88.66',0),(53,62,'Logged Into Their Account.',1758359880,'82.0.32.255',0),(54,62,'Logged Into Their Account.',1758470789,'82.0.32.255',0),(55,59,'Logged Into Their Account.',1758558535,'179.84.88.66',0),(56,59,'Logged Into Their Account.',1758637321,'179.84.88.66',0),(57,54,'Logged Into Their Account.',1759596447,'192.168.0.110',0),(58,62,'Logged Into Their Account.',1759690277,'82.0.32.255',0),(59,68,'Logged Into Their Account.',1759721166,'73.107.68.129',0),(60,57,'Logged Into Their Account.',1759809802,'174.221.158.164',0),(61,54,'Logged Into Their Account.',1760898163,'192.168.0.7',0),(62,54,'Logged Into Their Account.',1760898168,'192.168.0.7',0),(63,54,'Logged Into Their Account.',1760898173,'192.168.0.7',0),(64,54,'Logged Into Their Account.',1760899333,'192.168.0.110',0),(65,69,'Logged Into Their Account.',1761179697,'172.56.1.2',0),(66,54,'Logged Into Their Account.',1761179717,'192.168.0.110',0),(67,54,'Logged Into Their Account.',1761179722,'192.168.0.110',0),(68,59,'Logged Into Their Account.',1761180067,'179.84.88.178',0),(69,68,'Logged Into Their Account.',1761180547,'73.107.68.129',0),(70,68,'Logged Into Their Account.',1761180552,'73.107.68.129',0),(71,54,'Logged Into Their Account.',1761218053,'204.109.64.85',0),(72,54,'Logged Into Their Account.',1761228468,'74.82.60.24',0),(73,54,'Logged Into Their Account.',1761228473,'74.82.60.24',0),(74,54,'Logged Into Their Account.',1761304096,'204.109.64.85',0),(75,54,'Logged Into Their Account.',1761304101,'204.109.64.85',0),(76,54,'Logged Into Their Account.',1761310741,'204.109.64.85',0),(77,54,'Logged Into Their Account.',1761318091,'64.62.219.66',0),(78,54,'Logged Into Their Account.',1761318096,'64.62.219.66',0),(79,54,'Logged Into Their Account.',1761318100,'64.62.219.66',0),(80,54,'Logged Into Their Account.',1761430043,'107.123.33.76',0),(81,54,'Logged Into Their Account.',1761430048,'107.123.33.76',0),(82,54,'Logged Into Their Account.',1761452748,'192.168.0.110',0),(83,54,'Logged Into Their Account.',1761486684,'107.122.105.44',0),(84,54,'Logged Into Their Account.',1761486688,'107.122.105.44',0),(85,54,'Logged Into Their Account.',1761626147,'192.168.0.110',0),(86,54,'Logged Into Their Account.',1761650673,'204.109.64.85',0),(87,54,'Logged Into Their Account.',1761650677,'204.109.64.85',0),(88,54,'Logged Into Their Account.',1761650682,'204.109.64.85',0),(89,54,'Logged Into Their Account.',1761653638,'65.49.2.18',0),(90,54,'Logged Into Their Account.',1761701122,'192.168.0.110',0),(91,54,'Logged Into Their Account.',1761715570,'192.168.0.110',0),(92,54,'Logged Into Their Account.',1761736110,'204.109.64.85',0),(93,54,'Logged Into Their Account.',1761736589,'204.109.64.85',0),(94,54,'Logged Into Their Account.',1761736594,'204.109.64.85',0),(95,NULL,'Logged Out Of Their Account.',1761736664,'204.109.64.85',0),(96,NULL,'Logged Out Of Their Account.',1761736672,'204.109.64.85',0),(97,54,'Logged Into Their Account.',1761744615,'107.122.105.47',0),(98,54,'Logged Into Their Account.',1761761830,'107.123.33.109',0),(99,54,'Logged Into Their Account.',1761761834,'107.123.33.109',0),(100,70,'Logged Into Their Account.',1761778394,'109.79.157.26',0),(101,54,'Logged Into Their Account.',1761783168,'192.168.0.110',0),(102,54,'Logged Into Their Account.',1761956821,'192.168.0.110',0),(103,54,'Logged Into Their Account.',1761956826,'192.168.0.110',0),(104,54,'Logged Into Their Account.',1761972099,'192.168.0.114',0),(105,54,'Logged Into Their Account.',1763853965,'192.168.0.114',0),(106,54,'Logged Into Their Account.',1763870577,'192.168.0.7',0),(107,54,'Logged Into Their Account.',1763870582,'192.168.0.7',0),(108,54,'Logged Into Their Account.',1763885336,'192.168.0.7',0),(109,54,'Logged Into Their Account.',1763885340,'192.168.0.7',0),(110,54,'Logged Into Their Account.',1763885345,'192.168.0.7',0),(111,54,'Logged Into Their Account.',1763908561,'100.88.230.16',0),(112,54,'Logged Into Their Account.',1763909508,'107.123.33.9',0),(113,54,'Logged Into Their Account.',1763909512,'107.123.33.9',0),(114,54,'Logged Into Their Account.',1763924069,'192.168.0.114',0),(115,54,'Logged Into Their Account.',1763924074,'192.168.0.114',0),(116,54,'Logged Into Their Account.',1764039180,'192.168.0.107',0),(117,54,'Logged Into Their Account.',1764043400,'192.168.0.107',0),(118,54,'Logged Into Their Account.',1764057181,'192.168.0.107',0),(119,54,'Logged Into Their Account.',1764275282,'192.168.0.111',0),(120,54,'Logged Into Their Account.',1764275287,'192.168.0.111',0),(121,54,'Logged Into Their Account.',1764275291,'192.168.0.111',0),(122,54,'Logged Into Their Account.',1764275296,'192.168.0.111',0),(123,54,'Logged Into Their Account.',1764277293,'107.123.33.71',0),(124,54,'Logged Into Their Account.',1764277298,'107.123.33.71',0),(125,54,'Logged Into Their Account.',1764282695,'107.123.33.71',0),(126,54,'Logged Into Their Account.',1764287609,'107.123.33.71',0),(127,54,'Logged Into Their Account.',1764295415,'192.168.0.111',0),(128,54,'Logged Into Their Account.',1764300104,'192.168.0.7',0),(129,54,'Logged Into Their Account.',1764300108,'192.168.0.7',0),(130,54,'Logged Into Their Account.',1764300113,'192.168.0.7',0),(131,54,'Logged Into Their Account.',1764300118,'192.168.0.7',0),(132,54,'Logged Into Their Account.',1764348139,'192.168.0.7',0),(133,54,'Logged Into Their Account.',1764348143,'192.168.0.7',0),(134,71,'Logged Into Their Account.',1764348238,'64.114.211.84',0),(135,71,'Logged Into Their Account.',1764353900,'64.180.177.15',0),(136,54,'Logged Into Their Account.',1764441838,'192.168.0.7',0),(137,54,'Logged Into Their Account.',1764441843,'192.168.0.7',0),(138,54,'Logged Into Their Account.',1764441848,'192.168.0.7',0),(139,54,'Logged Into Their Account.',1764591355,'204.109.64.85',0),(140,54,'Logged Into Their Account.',1764591359,'204.109.64.85',0),(141,54,'Logged Into Their Account.',1764591364,'204.109.64.85',0),(142,54,'Logged Into Their Account.',1764591369,'204.109.64.85',0),(143,54,'Logged Into Their Account.',1764596883,'204.109.64.85',0),(144,54,'Logged Into Their Account.',1764615803,'166.199.100.46',0),(145,54,'Logged Into Their Account.',1764658725,'192.168.0.111',0),(146,54,'Logged Into Their Account.',1764856550,'172.56.0.236',0),(147,54,'Logged Into Their Account.',1764856554,'172.56.0.236',0),(148,54,'Logged Into Their Account.',1764858316,'172.56.0.236',0),(149,54,'Logged Into Their Account.',1765196485,'204.109.64.85',0),(150,54,'Logged Into Their Account.',1765199831,'204.109.64.85',0),(151,54,'Logged Into Their Account.',1765199836,'204.109.64.85',0),(152,54,'Logged Into Their Account.',1765248322,'192.168.0.111',0),(153,54,'Logged Into Their Account.',1765367691,'107.122.105.41',0),(154,54,'Logged Into Their Account.',1765367696,'107.122.105.41',0),(155,54,'Logged Into Their Account.',1765369129,'204.109.64.85',0),(156,54,'Logged Into Their Account.',1765385101,'107.115.159.12',0),(157,54,'Logged Into Their Account.',1765385105,'107.115.159.12',0),(158,54,'Logged Into Their Account.',1765454418,'204.109.64.85',0),(159,54,'Logged Into Their Account.',1765492040,'192.168.0.111',0),(160,54,'Logged Into Their Account.',1765540949,'204.109.64.85',0),(161,54,'Logged Into Their Account.',1765719974,'192.168.0.7',0),(162,54,'Logged Into Their Account.',1765724286,'166.199.100.116',0),(163,54,'Logged Into Their Account.',1765724290,'166.199.100.116',0),(164,54,'Logged Into Their Account.',1765741637,'192.168.0.7',0),(165,54,'Logged Into Their Account.',1765741642,'192.168.0.7',0),(166,54,'Logged Into Their Account.',1765801986,'204.109.64.85',0),(167,54,'Logged Into Their Account.',1765816474,'100.77.161.68',0),(168,54,'Logged Into Their Account.',1765841372,'192.168.0.111',0),(169,54,'Logged Into Their Account.',1765886610,'204.109.64.85',0),(170,54,'Logged Into Their Account.',1765892173,'166.199.100.109',0),(171,54,'Logged Into Their Account.',1765917557,'192.168.0.7',0),(172,54,'Logged Into Their Account.',1765923375,'192.168.0.7',0),(173,54,'Logged Into Their Account.',1765923379,'192.168.0.7',0),(174,54,'Logged Into Their Account.',1765923384,'192.168.0.7',0),(175,54,'Logged Into Their Account.',1765923683,'192.168.0.111',0),(176,54,'Logged Into Their Account.',1765950836,'192.168.0.111',0),(177,54,'Logged Into Their Account.',1765967448,'192.168.0.7',0),(178,54,'Logged Into Their Account.',1765967453,'192.168.0.7',0),(179,54,'Logged Into Their Account.',1765969712,'166.199.100.29',0),(180,75,'Logged Into Their Account.',1765981390,'172.56.0.152',0),(181,54,'Logged Into Their Account.',1765991790,'204.109.64.85',0),(182,54,'Logged Into Their Account.',1765999827,'108.147.177.1',0),(183,54,'Logged Into Their Account.',1765999832,'108.147.177.1',0),(184,54,'Logged Into Their Account.',1766004186,'108.147.177.1',0),(185,54,'Logged Into Their Account.',1766004190,'108.147.177.1',0),(186,54,'Logged Into Their Account.',1766011062,'192.168.0.7',0),(187,54,'Logged Into Their Account.',1766013004,'192.168.0.111',0),(188,54,'Logged Into Their Account.',1766100455,'192.168.0.111',0),(189,54,'Logged Into Their Account.',1766189461,'107.122.105.141',0),(190,54,'Logged Into Their Account.',1766189465,'107.122.105.141',0),(191,54,'Logged Into Their Account.',1766334113,'192.168.0.111',0),(192,54,'Logged Into Their Account.',1766334117,'192.168.0.111',0),(193,54,'Logged Into Their Account.',1766522032,'192.168.0.111',0),(194,54,'Logged Into Their Account.',1766522037,'192.168.0.111',0),(195,54,'Logged Into Their Account.',1766550761,'192.168.0.108',0),(196,54,'Logged Into Their Account.',1766550766,'192.168.0.108',0);
/*!40000 ALTER TABLE `useractionlogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `emailVerifyCode` int(15) DEFAULT '0',
  `ip` varchar(250) DEFAULT NULL,
  `last_ip` varchar(250) DEFAULT NULL,
  `avatar_hash` varchar(250) NOT NULL DEFAULT 'default',
  `next_reward` int(15) DEFAULT NULL,
  `premium_reward` int(15) DEFAULT NULL,
  `premium_expired` int(15) DEFAULT NULL,
  `Currency` int(255) NOT NULL DEFAULT '0',
  `cash` int(255) NOT NULL DEFAULT '0',
  `status` varchar(250) DEFAULT '',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `experiments` tinyint(11) DEFAULT '0',
  `flood` int(11) DEFAULT NULL,
  `administrator` tinyint(1) NOT NULL DEFAULT '0',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `premium` tinyint(1) NOT NULL DEFAULT '0',
  `newexpire` tinyint(250) DEFAULT NULL,
  `forumposts` int(11) NOT NULL DEFAULT '0',
  `profileviews` int(255) DEFAULT '0',
  `expire` int(11) DEFAULT NULL,
  `joined` int(11) DEFAULT NULL,
  `forum_xp` int(11) DEFAULT '0',
  `forumReplies` int(11) NOT NULL DEFAULT '0',
  `prestige` int(11) NOT NULL DEFAULT '0',
  `blurb` varchar(250) DEFAULT 'y',
  `Theme` varchar(250) DEFAULT NULL,
  `allowcomments` int(11) NOT NULL DEFAULT '1',
  `Gender` varchar(255) DEFAULT '0',
  `enjoy` int(15) DEFAULT NULL,
  `dislike` int(11) DEFAULT NULL,
  `bday` int(11) DEFAULT NULL,
  `bmonth` int(11) DEFAULT NULL,
  `byear` int(11) DEFAULT NULL,
  `emailcodeTime` int(11) DEFAULT NULL,
  `TradeSettings` tinyint(4) NOT NULL DEFAULT '0',
  `NotificationSettingsIncomingTrades` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (54,'BellTower','$2y$12$MH/dhhEDi7MC0RIxUNFyX.nCqzHxSpfXUULLG3U1xiZKdG3c9TI26','josemo360@yahoo.com',0,'192.168.0.71',NULL,'default',1766608434,1766608434,1756952561,1446,0,'goobler',1,0,1766081704,1,0,1,NULL,0,0,1766552535,1753329693,75,0,0,'I\'m the owner kid, sybau',NULL,1,'0',NULL,NULL,0,8,2,NULL,0,1),(55,'Delta','$2y$12$3qbWJnrkYbWtj4m8zwr54u4iuwVuhxf4bzo03BQpoOpm5jv.4pXVG','deadmemesv47@gmail.com',0,'24.251.101.67',NULL,'default',1756970489,NULL,NULL,20,0,'',1,0,NULL,0,1,0,NULL,0,0,1756884180,1755839218,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,5,8,NULL,0,1),(56,'Ben','$2y$12$G6wcZVzmu3bhAc8FEN0DGuR3RExXkdH9fDYKYKsoYI8aD2HePQpAi','onamadone@yahoo.com',0,'82.132.239.210',NULL,'default',1756418467,NULL,NULL,10,0,'',1,0,1756332104,0,1,0,NULL,0,0,1756332181,1756332067,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,2,2,NULL,0,1),(57,'Bomb','$2y$12$kzP3DXNIrn/XYNp7eVPSB.TgiZTgSr2wZwhVYFHNUhMDsxmG8IbQa','bregraphy@gmail.com',0,'174.233.70.202',NULL,'default',1759896204,NULL,NULL,20,0,'',1,0,NULL,0,1,0,NULL,0,0,1759811293,1756866765,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,6,4,NULL,0,1),(58,'FTK','$2y$12$WzhXDTpelHolCbjGvWISue3jpnuAdCIDW8MXZr88vt/SZ8RpD2Yim','gmatthewson858@gmail.com',0,'174.218.241.144',NULL,'default',1756981640,NULL,NULL,10,0,'',1,0,NULL,0,1,0,NULL,0,0,1756895463,1756895239,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,1,9,NULL,0,1),(59,'phil','$2y$12$3kyEZ5Z5mhj3z.YkUFIxdujOpKRrTMjTrur35/SoVyRwQ1QGd0v.i','filipemargonarmoreira614@gmail.com',0,'189.97.3.186',NULL,'default',1761266469,NULL,NULL,50,0,'the',1,0,1761180331,0,1,0,NULL,0,0,1761180421,1756905649,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,8,0,NULL,0,1),(60,'daburger','$2y$12$ZUSX77sxutCaibIYf9sFRep7KGWAQMgyt78AUqQHJbDvP3agsxf7a','transfurinfection@gmail.com',0,'77.105.54.252',NULL,'default',1756994718,NULL,NULL,10,0,'',1,0,NULL,0,1,0,NULL,0,0,1756908409,1756908318,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,4,1,6,NULL,0,1),(61,'CowCow','$2y$12$VMGeBOUCiHoNOiaba6hehuXd1Rwy.T1LDrcs.OoI7Ru/vt8Y/mJeW','backyarddegentasharen@gmail.com',0,'84.234.249.153',NULL,'default',1756996034,NULL,NULL,10,0,'',1,0,1756910010,0,1,0,NULL,0,0,1756910313,1756909634,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,1,2,NULL,0,1),(62,'gox','$2y$12$q0Z4a0Utor0OKXOLrG58UeTUrbSS9PL6kE439Ogr/mYIrYsNijQRq','playznooby0@gmail.com',0,'82.0.32.255',NULL,'default',1759776679,NULL,NULL,90,0,'',1,0,1757165720,0,1,0,NULL,0,0,1759690305,1756909961,0,0,0,'dead site',NULL,1,'0',NULL,NULL,0,1,2,NULL,0,1),(63,'r10az','$2y$12$rS5h4/mVn7/ihbyW1XkDDObVVjrKrVCOtuk8.G51RAMpbYoQqalty','r10azgamer@gmail.com',0,'73.63.91.69',NULL,'default',1757140880,NULL,NULL,23,0,'',1,0,1757054974,0,1,0,NULL,0,0,1757054969,1756934693,0,0,0,'Professional',NULL,1,'0',NULL,NULL,0,0,1,NULL,0,1),(64,'Misa','$2y$12$HcLh8ZW9Yup.Lj0qyfL68uTan59Wk.2ynnNKq/AbN3cOON4rXf/cG','srtdfgdfg@gmail.com',0,'204.109.64.85',NULL,'default',1757077006,NULL,NULL,10,0,'',1,0,1756990689,0,1,0,NULL,0,0,1756990826,1756990606,0,0,0,'LOL',NULL,1,'0',NULL,NULL,0,0,5,NULL,0,1),(65,'IPay69','$2y$12$sOJec7tXrziETPV89955u.iRBcKMNgGFFXDw19qYcjw2pBS4gFYHy','abisaadelie542@gmail.com',0,'178.135.23.156',NULL,'default',1757141445,NULL,NULL,10,0,'',1,0,NULL,0,1,0,NULL,0,0,1757055045,1757055045,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,0,1,NULL,0,1),(66,'unicorngirl954','$2y$12$TCMWvvJQJZk5pxhEnvnQUer5cPF92QhlZF9Px/c8qm4pC3oBZcxeS','mkdraws954@gmail.com',0,'172.58.134.170',NULL,'default',1757766718,NULL,NULL,10,0,'',1,0,NULL,0,1,0,NULL,0,0,1757703795,1757680318,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,8,7,NULL,0,1),(67,'rougeshrimp6969','$2y$12$uKprITapYvYMmCo3iN7YieWKwkhaFcDqSI/W0j45BGMSOO8GBB5fW','noahmbrand@gmail.com',0,'204.109.64.85',NULL,'default',1758287713,NULL,NULL,10,0,'',1,0,1758201483,0,1,0,NULL,0,0,1758201581,1758201313,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,7,7,NULL,0,1),(68,'Oblivion','$2y$12$9apfvSLUVCE5Tn4Q23RSvuz/cGZqHnduhdWXz6nmk9hqsaoNxpCue','oblivionwm357@gmail.com',0,'73.107.68.129',NULL,'default',1761266949,NULL,NULL,20,0,'',1,0,1761180623,0,1,0,NULL,0,0,1761180607,1759721142,0,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,9,9,6,NULL,0,1),(69,'thestank','$2y$12$MnIXeZPxU6ZWL7TPUwHIQ.edJoZIC9NtDOqcV4ap5ZRSDHPj6PVq.','thestank@gmai.com',0,'172.56.18.254',NULL,'default',1761266099,NULL,NULL,20,0,'the',1,0,1761179789,0,1,0,NULL,0,0,1761180612,1760898223,2,0,0,'Welcome to Avatoria!',NULL,1,'0',NULL,NULL,0,6,6,NULL,0,1),(70,'odd','$2y$12$pYtvxwkONBXbtr1p6ei3E.4gZFunYcke0wz.QZBWGZ0MblxKqkhX6','davisorrie@gmail.com',0,'109.79.157.26',NULL,'default',1761864773,NULL,NULL,10,0,'',0,0,NULL,0,1,0,NULL,0,0,1761778419,1761778373,0,0,0,'Welcome to Bricktaria!',NULL,1,'0',NULL,NULL,0,7,6,NULL,0,1),(71,'Era','$2y$12$G/rY2C.qL.daF7FOtoP72Or6ZBjptqE7UTD9E.8.LeQfQpfGXaan6','jamesispro9@gmail.com',0,'209.53.30.81',NULL,'default',1764434430,NULL,NULL,10,0,'',0,0,NULL,0,0,0,NULL,0,0,1764353927,1764348029,0,0,0,'Welcome to Bricktaria!',NULL,1,'0',NULL,NULL,0,8,4,NULL,0,1),(72,'myn','$2y$12$itJ7h1scmEldvRps4iNGie1n.Qxesi8WFg2tQxhtJyM2GLE9U5A/m','fillipemargonarmoreira@gmail.com',0,'168.197.104.115',NULL,'default',1764552512,NULL,NULL,10,0,'',0,0,NULL,0,0,0,NULL,0,0,1764466171,1764466111,0,0,0,'Welcome to Bricktaria!',NULL,1,'0',NULL,NULL,0,8,0,NULL,0,1),(73,'Sorry_snow','$2y$12$V9en5kii7La4CQ6VyCe4m..jSJecZ/hz5b8.BWYzJ9MtgqsYC/H96','kennynh2007@gmail.com',0,'204.109.64.85',NULL,'default',1765285021,NULL,NULL,10,0,'',0,0,NULL,0,0,0,NULL,0,0,1765284885,1765198621,0,0,0,'Welcome to Bricktaria!',NULL,1,'0',NULL,NULL,0,7,2,NULL,0,1),(74,'null','$2y$12$n.vVnUOTSimSDWQ2m9EEQeOp.hGq6u50mXEMNIsnGmpc1E18inp1y','filipecreations@gmail.com',0,'186.212.13.16',NULL,'default',1766005799,NULL,NULL,10,0,'',0,0,NULL,0,0,0,NULL,0,0,1765919582,1765919398,0,0,0,'Welcome to Bricktaria!',NULL,1,'0',NULL,NULL,0,8,0,NULL,0,1),(75,'thestank2','$2y$12$vOwf5EAiZ6TDq7.uHeUSVOVPCB9Q2TMSZ0GalpChq4I80zmeK9J4G','fallsballs@gmail.com',0,'172.56.0.152',NULL,'default',1766065594,NULL,NULL,10,0,'',0,0,1765981439,0,0,0,NULL,0,0,1765981460,1765979193,0,0,0,'Welcome to Bricktaria!',NULL,1,'0',NULL,NULL,0,6,1,NULL,0,1),(76,'nig','$2y$12$k/jjMWNDrYpvFvtJ4Pjda.MtlLOzKZ46tjJMtDakcRRYpjG873Qg.','makehisribsshow@gmail.com',0,'173.172.202.158',NULL,'default',1766637167,NULL,NULL,10,0,'',0,0,1766550972,0,0,0,NULL,0,0,1766550963,1766550767,0,0,0,'Welcome to Bricktaria!',NULL,1,'0',NULL,NULL,0,4,6,NULL,0,1),(77,'0064','$2y$12$.eWQVmOOKvnOek6ey3lEoewRrvrCX/9MO5DibO7g3DhaCxxVKEw3i','uwfCMVtrFGaN6u6M5U9kqe1Z95JeSRuH1yp8Eh5L@proton.me',0,'23.234.88.248',NULL,'default',1766637252,NULL,NULL,10,0,'',0,0,1766550986,0,0,0,NULL,0,0,1766551001,1766550852,0,0,0,'Welcome to Bricktaria!',NULL,1,'0',NULL,NULL,0,0,1,NULL,0,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userwall`
--

DROP TABLE IF EXISTS `userwall`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userwall` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `comment` text NOT NULL,
  `timecommented` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moderation` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userwall`
--

LOCK TABLES `userwall` WRITE;
/*!40000 ALTER TABLE `userwall` DISABLE KEYS */;
/*!40000 ALTER TABLE `userwall` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'goobinator'
--

--
-- Dumping routines for database 'goobinator'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-24  1:30:01
