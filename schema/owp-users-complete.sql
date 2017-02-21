-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: owp_users
-- ------------------------------------------------------
-- Server version	5.7.17-0ubuntu0.16.04.1

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
-- Table structure for table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_users` (
  `userID` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `passwd` varchar(200) NOT NULL,
  `user_created_datetime` datetime NOT NULL,
  `user_updated_datetime` datetime NOT NULL,
  `user_last_login_datetime` datetime NOT NULL,
  `login_count` int(10) NOT NULL DEFAULT '0',
  `statusID` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(200) NOT NULL,
  `first_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `uuid` varchar(35) DEFAULT NULL,
  `welcome_email_sent` tinyint(1) NOT NULL,
  `reset_pass_uuid` varchar(35) DEFAULT NULL,
  `user_ip` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email` (`email`) USING BTREE,
  KEY `uuid` (`uuid`) USING BTREE,
  KEY `reset_pass_uuid` (`reset_pass_uuid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_users`
--

LOCK TABLES `tbl_users` WRITE;
/*!40000 ALTER TABLE `tbl_users` DISABLE KEYS */;
INSERT INTO `tbl_users` VALUES (1,'$2a$08$AjMKPO9lS3VOcuFJPgrdUuLltnEZO1d8/QKJJxD4c44NQG782qA3C','2017-01-01 01:00:00','2017-01-01 01:00:00','2017-01-01 01:00:00',0,10,'system@system','System','User',NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `tbl_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_users_meta_data`
--

DROP TABLE IF EXISTS `tbl_users_meta_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_users_meta_data` (
  `userID` int(15) unsigned NOT NULL,
  `key_name` varchar(50) NOT NULL,
  `key_value` varchar(500) DEFAULT NULL,
  `updated_ts` datetime NOT NULL,
  PRIMARY KEY (`userID`,`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_users_meta_data`
--

LOCK TABLES `tbl_users_meta_data` WRITE;
/*!40000 ALTER TABLE `tbl_users_meta_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_users_meta_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_users_rights`
--

DROP TABLE IF EXISTS `tbl_users_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_users_rights` (
  `userID` int(15) unsigned NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `hide_ads` tinyint(1) NOT NULL DEFAULT '0',
  `is_dev` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_users_rights`
--

LOCK TABLES `tbl_users_rights` WRITE;
/*!40000 ALTER TABLE `tbl_users_rights` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_users_rights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_users_status`
--

DROP TABLE IF EXISTS `tbl_users_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_users_status` (
  `statusID` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `status_label` varchar(100) NOT NULL,
  PRIMARY KEY (`statusID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_users_status`
--

LOCK TABLES `tbl_users_status` WRITE;
/*!40000 ALTER TABLE `tbl_users_status` DISABLE KEYS */;
INSERT INTO `tbl_users_status` VALUES (1,'New Pending Verification'),(2,'Verified'),(4,'Password Lost'),(9,'Closed by Admin'),(3,'Bounced Verification Email'),(10,'System User');
/*!40000 ALTER TABLE `tbl_users_status` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-14  6:30:38
