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
-- Dumping data for table `tbl_users`
--

LOCK TABLES `tbl_users` WRITE;
/*!40000 ALTER TABLE `tbl_users` DISABLE KEYS */;
INSERT INTO `tbl_users` VALUES (1,'$2a$08$AjMKPO9lS3VOcuFJPgrdUuLltnEZO1d8/QKJJxD4c44NQG782qA3C','2017-01-01 01:00:00','2017-01-01 01:00:00','2017-01-01 01:00:00',0,10,'system@system','System','User',NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `tbl_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tbl_users_meta_data`
--

LOCK TABLES `tbl_users_meta_data` WRITE;
/*!40000 ALTER TABLE `tbl_users_meta_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_users_meta_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tbl_users_rights`
--

LOCK TABLES `tbl_users_rights` WRITE;
/*!40000 ALTER TABLE `tbl_users_rights` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_users_rights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tbl_users_status`
--

LOCK TABLES `tbl_users_status` WRITE;
/*!40000 ALTER TABLE `tbl_users_status` DISABLE KEYS */;
INSERT INTO `tbl_users_status` VALUES (1,'New Pending Verification'),(2,'Verified'),(9,'Closed by Admin'),(3,'Bounced Verification Email'),(10,'System User');
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

-- Dump completed on 2017-02-14  3:07:06
