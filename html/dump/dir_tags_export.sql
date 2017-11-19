-- MySQL dump 10.13  Distrib 5.7.12, for Win64 (x86_64)
--
-- Host: localhost    Database: parsyii
-- ------------------------------------------------------
-- Server version	5.7.20

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
-- Table structure for table `dir_tags_export`
--

DROP TABLE IF EXISTS `dir_tags_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dir_tags_export` (
  `dte_id` int(11) NOT NULL AUTO_INCREMENT,
  `dte_cust_id` int(11) DEFAULT NULL,
  `dte_dt_id` int(11) DEFAULT NULL,
  `dte_cmf_field` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`dte_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COMMENT='Имена полей для импорта и их сообтветствие в таблице dir_tags';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dir_tags_export`
--

LOCK TABLES `dir_tags_export` WRITE;
/*!40000 ALTER TABLE `dir_tags_export` DISABLE KEYS */;
INSERT INTO `dir_tags_export` (`dte_id`, `dte_cust_id`, `dte_dt_id`, `dte_cmf_field`) VALUES (1,1,NULL,'	ID	'),(2,1,NULL,'	Active (0/1)	'),(3,1,NULL,'	Name *	'),(4,1,NULL,'	Categories (x,y,z...)	'),(5,1,NULL,'	Price tax excluded or Price tax included	'),(6,1,NULL,'	Tax rules ID	'),(7,1,NULL,'	Wholesale price	'),(8,1,NULL,'	On sale (0/1)	'),(9,1,NULL,'	Discount amount	'),(10,1,NULL,'	Discount percent	'),(11,1,NULL,'	Discount from (yyyy-mm-dd)	'),(12,1,NULL,'	Discount to (yyyy-mm-dd)	'),(13,1,NULL,'	Reference #	'),(14,1,NULL,'	Supplier reference #	'),(15,1,NULL,'	Supplier	'),(16,1,NULL,'	Manufacturer	'),(17,1,NULL,'	EAN13	'),(18,1,NULL,'	UPC	'),(19,1,NULL,'	Ecotax	'),(20,1,NULL,'	Width	'),(21,1,NULL,'	Height	'),(22,1,NULL,'	Depth	'),(23,1,NULL,'	Weight	'),(24,1,NULL,'	Quantity	'),(25,1,NULL,'	Minimal quantity	'),(26,1,NULL,'	Visibility	'),(27,1,NULL,'	Additional shipping cost	'),(28,1,NULL,'	Unity	'),(29,1,NULL,'	Unit price ratio	'),(30,1,NULL,'	Short description	'),(31,1,NULL,'	Description	'),(32,1,NULL,'	Tags (x,y,z...)	'),(33,1,NULL,'	Meta title	'),(34,1,NULL,'	Meta keywords	'),(35,1,NULL,'	Meta description	'),(36,1,NULL,'	URL rewritten	'),(37,1,NULL,'	Text when in stock	'),(38,1,NULL,'	Text when backorder allowed	'),(39,1,NULL,'	Available for order (0 = No, 1 = Yes)	'),(40,1,NULL,'	Product available date	'),(41,1,NULL,'	Product creation date	'),(42,1,NULL,'	Show price (0 = No, 1 = Yes)	'),(43,1,NULL,'	Image URLs (x,y,z...)	'),(44,1,NULL,'	Delete existing images (0 = No, 1 = Yes)	'),(45,1,NULL,'	Feature(Name:Value:Position)	'),(46,1,NULL,'	Available online only (0 = No, 1 = Yes)	'),(47,1,NULL,'	Condition	'),(48,1,NULL,'	Customizable (0 = No, 1 = Yes)	'),(49,1,NULL,'	Uploadable files (0 = No, 1 = Yes)	'),(50,1,NULL,'	Text fields (0 = No, 1 = Yes)	'),(51,1,NULL,'	Out of stock	'),(52,1,NULL,'	ID / Name of shop	'),(53,1,NULL,'	Advanced stock management	'),(54,1,NULL,'	Depends On Stock	'),(55,1,NULL,'	Warehouse	');
/*!40000 ALTER TABLE `dir_tags_export` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'parsyii'
--

--
-- Dumping routines for database 'parsyii'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-11-20  1:00:37
