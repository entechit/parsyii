-- MySQL dump 10.13  Distrib 5.7.12, for Win64 (x86_64)
--
-- Host: 172.16.18.15    Database: parsyii
-- ------------------------------------------------------
-- Server version	5.7.12

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
-- Table structure for table `dir_cms`
--

DROP TABLE IF EXISTS `dir_cms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dir_cms` (
  `dc_id` int(11) NOT NULL AUTO_INCREMENT,
  `dc_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`dc_id`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dir_cms`
--

LOCK TABLES `dir_cms` WRITE;
/*!40000 ALTER TABLE `dir_cms` DISABLE KEYS */;
INSERT INTO `dir_cms` VALUES (1,'Drupal'),(2,'WordPress'),(3,'DLE'),(4,'Joomla'),(5,'MODx'),(6,'Textpattern'),(7,'OSCommerce'),(8,'e107'),(9,'Danneo'),(10,'1C:Битрикс'),(11,'NetCat'),(12,'TYPO3'),(13,'Plone'),(14,'CMS Made Simple'),(15,'Movable Type'),(16,'InstantCMS'),(17,'MaxSite CMS'),(18,'UMI.CMS'),(19,'HostCMS'),(20,'Amiro CMS'),(21,'Magento'),(22,'S.Builder'),(23,'ABO.CMS'),(24,'Twilight CMS'),(25,'PHP-Fusion'),(26,'Melbis'),(27,'Miva Merchant'),(28,'phpwcms'),(29,'N2 CMS'),(30,'Explay CMS'),(31,'ExpressionEngine'),(32,'Klarnet CMS'),(33,'SEQUNDA'),(34,'SiteDNK'),(35,'CM5'),(36,'Site Sapiens'),(37,'Cetera CMS'),(38,'Hitmaster'),(39,'DSite'),(40,'SiteEdit'),(41,'TrinetCMS'),(42,'Adlabs.CMS'),(43,'Introweb-CMS'),(44,'iNTERNET.cms'),(45,'Kentico CMS'),(46,'LiveStreet'),(47,'vBulletin'),(48,'phpBB'),(49,'Invision Power Board'),(50,'Cmsimple'),(51,'OpenCMS'),(52,'slaed'),(53,'PHP-Nuke'),(54,'RUNCMS'),(55,'eZ publish'),(56,'Koobi'),(57,'Simple Machines Forum (SMF)'),(58,'MediaWiki'),(59,'LightMon'),(60,'diafan.CMS'),(61,'ImageCMS'),(62,'ocStore'),(63,'Joostina'),(64,'PHPShop'),(65,'Santafox'),(66,'Webasyst'),(67,'OpenCart'),(68,'PrestaShop');
/*!40000 ALTER TABLE `dir_cms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `source_site`
--

DROP TABLE IF EXISTS `source_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `source_site` (
  `ss_id` int(11) NOT NULL AUTO_INCREMENT,
  `ss_url` varchar(255) DEFAULT NULL,
  `ss_format` varchar(250) DEFAULT NULL,
  `ss_descript` varchar(250) DEFAULT NULL,
  `ss_dataadd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ss_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='источники сайтов для парсинга';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source_site`
--

LOCK TABLES `source_site` WRITE;
/*!40000 ALTER TABLE `source_site` DISABLE KEYS */;
INSERT INTO `source_site` VALUES (1,'http://pex8.com/','PrestaShop','Viatcheslav Moukhamediarov','2017-09-18 07:11:20'),(2,'https://ukesa.com.ua/',' WebAsyst Shop-Script','https://itrack.ru/whatcms/ Тренажор','2017-09-18 07:14:22');
/*!40000 ALTER TABLE `source_site` ENABLE KEYS */;
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

-- Dump completed on 2017-09-18 20:13:46
