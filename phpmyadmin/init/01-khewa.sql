-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_alias`, `alias`, `search`, `active` FROM `ps_alias`;
    
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

DROP TABLE IF EXISTS `ps_alias`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_alias` (
  `id_alias` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) NOT NULL,
  `search` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_alias`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_alias` WRITE;
/*!40000 ALTER TABLE `ps_alias` DISABLE KEYS */;
INSERT INTO `ps_alias` VALUES (1,'bloose','blouse',1);
INSERT INTO `ps_alias` VALUES (2,'blues','blouse',1);

/*!40000 ALTER TABLE `ps_alias` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cart_rule`, `id_carrier` FROM `ps_cart_rule_carrier`;
    
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

DROP TABLE IF EXISTS `ps_cart_rule_carrier`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cart_rule_carrier` (
  `id_cart_rule` int(10) unsigned NOT NULL,
  `id_carrier` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_cart_rule`,`id_carrier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cart_rule_carrier` WRITE;
/*!40000 ALTER TABLE `ps_cart_rule_carrier` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_cart_rule_carrier` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cart_rule_1`, `id_cart_rule_2` FROM `ps_cart_rule_combination`;
    
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

DROP TABLE IF EXISTS `ps_cart_rule_combination`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cart_rule_combination` (
  `id_cart_rule_1` int(10) unsigned NOT NULL,
  `id_cart_rule_2` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_cart_rule_1`,`id_cart_rule_2`),
  KEY `id_cart_rule_1` (`id_cart_rule_1`),
  KEY `id_cart_rule_2` (`id_cart_rule_2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cart_rule_combination` WRITE;
/*!40000 ALTER TABLE `ps_cart_rule_combination` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_cart_rule_combination` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cart_rule`, `id_country` FROM `ps_cart_rule_country`;
    
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

DROP TABLE IF EXISTS `ps_cart_rule_country`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cart_rule_country` (
  `id_cart_rule` int(10) unsigned NOT NULL,
  `id_country` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_cart_rule`,`id_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cart_rule_country` WRITE;
/*!40000 ALTER TABLE `ps_cart_rule_country` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_cart_rule_country` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cart_rule`, `id_group` FROM `ps_cart_rule_group`;
    
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

DROP TABLE IF EXISTS `ps_cart_rule_group`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cart_rule_group` (
  `id_cart_rule` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_cart_rule`,`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cart_rule_group` WRITE;
/*!40000 ALTER TABLE `ps_cart_rule_group` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_cart_rule_group` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cms_category`, `id_parent`, `level_depth`, `active`, `date_add`, `date_upd`, `position` FROM `ps_cms_category`;
    
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

DROP TABLE IF EXISTS `ps_cms_category`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cms_category` (
  `id_cms_category` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(10) unsigned NOT NULL,
  `level_depth` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_cms_category`),
  KEY `category_parent` (`id_parent`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cms_category` WRITE;
/*!40000 ALTER TABLE `ps_cms_category` DISABLE KEYS */;
INSERT INTO `ps_cms_category` VALUES (1,0,1,1,'2018-07-06 09:18:57','2018-07-06 09:18:57',0);

/*!40000 ALTER TABLE `ps_cms_category` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cms_category`, `id_shop` FROM `ps_cms_category_shop`;
    
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

DROP TABLE IF EXISTS `ps_cms_category_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cms_category_shop` (
  `id_cms_category` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_cms_category`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cms_category_shop` WRITE;
/*!40000 ALTER TABLE `ps_cms_category_shop` DISABLE KEYS */;
INSERT INTO `ps_cms_category_shop` VALUES (1,1);

/*!40000 ALTER TABLE `ps_cms_category_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cms_role`, `name`, `id_cms` FROM `ps_cms_role`;
    
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

DROP TABLE IF EXISTS `ps_cms_role`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cms_role` (
  `id_cms_role` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `id_cms` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_cms_role`,`id_cms`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cms_role` WRITE;
/*!40000 ALTER TABLE `ps_cms_role` DISABLE KEYS */;
INSERT INTO `ps_cms_role` VALUES (1,'LEGAL_CONDITIONS',3);
INSERT INTO `ps_cms_role` VALUES (2,'LEGAL_NOTICE',2);

/*!40000 ALTER TABLE `ps_cms_role` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cms_role`, `id_lang`, `id_shop`, `name` FROM `ps_cms_role_lang`;
    
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

DROP TABLE IF EXISTS `ps_cms_role_lang`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cms_role_lang` (
  `id_cms_role` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id_cms_role`,`id_lang`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cms_role_lang` WRITE;
/*!40000 ALTER TABLE `ps_cms_role_lang` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_cms_role_lang` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_connections`, `id_page`, `time_start`, `time_end` FROM `ps_connections_page`;
    
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

DROP TABLE IF EXISTS `ps_connections_page`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_connections_page` (
  `id_connections` int(10) unsigned NOT NULL,
  `id_page` int(10) unsigned NOT NULL,
  `time_start` datetime NOT NULL,
  `time_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id_connections`,`id_page`,`time_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_connections_page` WRITE;
/*!40000 ALTER TABLE `ps_connections_page` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_connections_page` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_contact`, `id_shop` FROM `ps_contact_shop`;
    
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

DROP TABLE IF EXISTS `ps_contact_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_contact_shop` (
  `id_contact` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_contact`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_contact_shop` WRITE;
/*!40000 ALTER TABLE `ps_contact_shop` DISABLE KEYS */;
INSERT INTO `ps_contact_shop` VALUES (2,1);

/*!40000 ALTER TABLE `ps_contact_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_country`, `id_shop` FROM `ps_country_shop`;
    
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

DROP TABLE IF EXISTS `ps_country_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_country_shop` (
  `id_country` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_country`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_country_shop` WRITE;
/*!40000 ALTER TABLE `ps_country_shop` DISABLE KEYS */;
INSERT INTO `ps_country_shop` VALUES (1,1);
INSERT INTO `ps_country_shop` VALUES (2,1);
INSERT INTO `ps_country_shop` VALUES (3,1);
INSERT INTO `ps_country_shop` VALUES (4,1);
INSERT INTO `ps_country_shop` VALUES (5,1);
INSERT INTO `ps_country_shop` VALUES (6,1);
INSERT INTO `ps_country_shop` VALUES (7,1);
INSERT INTO `ps_country_shop` VALUES (8,1);
INSERT INTO `ps_country_shop` VALUES (9,1);
INSERT INTO `ps_country_shop` VALUES (10,1);
INSERT INTO `ps_country_shop` VALUES (11,1);
INSERT INTO `ps_country_shop` VALUES (12,1);
INSERT INTO `ps_country_shop` VALUES (13,1);
INSERT INTO `ps_country_shop` VALUES (14,1);
INSERT INTO `ps_country_shop` VALUES (15,1);
INSERT INTO `ps_country_shop` VALUES (16,1);
INSERT INTO `ps_country_shop` VALUES (17,1);
INSERT INTO `ps_country_shop` VALUES (18,1);
INSERT INTO `ps_country_shop` VALUES (19,1);
INSERT INTO `ps_country_shop` VALUES (20,1);
INSERT INTO `ps_country_shop` VALUES (21,1);
INSERT INTO `ps_country_shop` VALUES (22,1);
INSERT INTO `ps_country_shop` VALUES (23,1);
INSERT INTO `ps_country_shop` VALUES (24,1);
INSERT INTO `ps_country_shop` VALUES (25,1);
INSERT INTO `ps_country_shop` VALUES (26,1);
INSERT INTO `ps_country_shop` VALUES (27,1);
INSERT INTO `ps_country_shop` VALUES (28,1);
INSERT INTO `ps_country_shop` VALUES (29,1);
INSERT INTO `ps_country_shop` VALUES (30,1);
INSERT INTO `ps_country_shop` VALUES (31,1);
INSERT INTO `ps_country_shop` VALUES (32,1);
INSERT INTO `ps_country_shop` VALUES (33,1);
INSERT INTO `ps_country_shop` VALUES (34,1);
INSERT INTO `ps_country_shop` VALUES (35,1);
INSERT INTO `ps_country_shop` VALUES (36,1);
INSERT INTO `ps_country_shop` VALUES (37,1);
INSERT INTO `ps_country_shop` VALUES (38,1);
INSERT INTO `ps_country_shop` VALUES (39,1);
INSERT INTO `ps_country_shop` VALUES (40,1);
INSERT INTO `ps_country_shop` VALUES (41,1);
INSERT INTO `ps_country_shop` VALUES (42,1);
INSERT INTO `ps_country_shop` VALUES (43,1);
INSERT INTO `ps_country_shop` VALUES (44,1);
INSERT INTO `ps_country_shop` VALUES (45,1);
INSERT INTO `ps_country_shop` VALUES (46,1);
INSERT INTO `ps_country_shop` VALUES (47,1);
INSERT INTO `ps_country_shop` VALUES (48,1);
INSERT INTO `ps_country_shop` VALUES (49,1);
INSERT INTO `ps_country_shop` VALUES (50,1);
INSERT INTO `ps_country_shop` VALUES (51,1);
INSERT INTO `ps_country_shop` VALUES (52,1);
INSERT INTO `ps_country_shop` VALUES (53,1);
INSERT INTO `ps_country_shop` VALUES (54,1);
INSERT INTO `ps_country_shop` VALUES (55,1);
INSERT INTO `ps_country_shop` VALUES (56,1);
INSERT INTO `ps_country_shop` VALUES (57,1);
INSERT INTO `ps_country_shop` VALUES (58,1);
INSERT INTO `ps_country_shop` VALUES (59,1);
INSERT INTO `ps_country_shop` VALUES (60,1);
INSERT INTO `ps_country_shop` VALUES (61,1);
INSERT INTO `ps_country_shop` VALUES (62,1);
INSERT INTO `ps_country_shop` VALUES (63,1);
INSERT INTO `ps_country_shop` VALUES (64,1);
INSERT INTO `ps_country_shop` VALUES (65,1);
INSERT INTO `ps_country_shop` VALUES (66,1);
INSERT INTO `ps_country_shop` VALUES (67,1);
INSERT INTO `ps_country_shop` VALUES (68,1);
INSERT INTO `ps_country_shop` VALUES (69,1);
INSERT INTO `ps_country_shop` VALUES (70,1);
INSERT INTO `ps_country_shop` VALUES (71,1);
INSERT INTO `ps_country_shop` VALUES (72,1);
INSERT INTO `ps_country_shop` VALUES (73,1);
INSERT INTO `ps_country_shop` VALUES (74,1);
INSERT INTO `ps_country_shop` VALUES (75,1);
INSERT INTO `ps_country_shop` VALUES (76,1);
INSERT INTO `ps_country_shop` VALUES (77,1);
INSERT INTO `ps_country_shop` VALUES (78,1);
INSERT INTO `ps_country_shop` VALUES (79,1);
INSERT INTO `ps_country_shop` VALUES (80,1);
INSERT INTO `ps_country_shop` VALUES (81,1);
INSERT INTO `ps_country_shop` VALUES (82,1);
INSERT INTO `ps_country_shop` VALUES (83,1);
INSERT INTO `ps_country_shop` VALUES (84,1);
INSERT INTO `ps_country_shop` VALUES (85,1);
INSERT INTO `ps_country_shop` VALUES (86,1);
INSERT INTO `ps_country_shop` VALUES (87,1);
INSERT INTO `ps_country_shop` VALUES (88,1);
INSERT INTO `ps_country_shop` VALUES (89,1);
INSERT INTO `ps_country_shop` VALUES (90,1);
INSERT INTO `ps_country_shop` VALUES (91,1);
INSERT INTO `ps_country_shop` VALUES (92,1);
INSERT INTO `ps_country_shop` VALUES (93,1);
INSERT INTO `ps_country_shop` VALUES (94,1);
INSERT INTO `ps_country_shop` VALUES (95,1);
INSERT INTO `ps_country_shop` VALUES (96,1);
INSERT INTO `ps_country_shop` VALUES (97,1);
INSERT INTO `ps_country_shop` VALUES (98,1);
INSERT INTO `ps_country_shop` VALUES (99,1);
INSERT INTO `ps_country_shop` VALUES (100,1);
INSERT INTO `ps_country_shop` VALUES (101,1);
INSERT INTO `ps_country_shop` VALUES (102,1);
INSERT INTO `ps_country_shop` VALUES (103,1);
INSERT INTO `ps_country_shop` VALUES (104,1);
INSERT INTO `ps_country_shop` VALUES (105,1);
INSERT INTO `ps_country_shop` VALUES (106,1);
INSERT INTO `ps_country_shop` VALUES (107,1);
INSERT INTO `ps_country_shop` VALUES (108,1);
INSERT INTO `ps_country_shop` VALUES (109,1);
INSERT INTO `ps_country_shop` VALUES (110,1);
INSERT INTO `ps_country_shop` VALUES (111,1);
INSERT INTO `ps_country_shop` VALUES (112,1);
INSERT INTO `ps_country_shop` VALUES (113,1);
INSERT INTO `ps_country_shop` VALUES (114,1);
INSERT INTO `ps_country_shop` VALUES (115,1);
INSERT INTO `ps_country_shop` VALUES (116,1);
INSERT INTO `ps_country_shop` VALUES (117,1);
INSERT INTO `ps_country_shop` VALUES (118,1);
INSERT INTO `ps_country_shop` VALUES (119,1);
INSERT INTO `ps_country_shop` VALUES (120,1);
INSERT INTO `ps_country_shop` VALUES (121,1);
INSERT INTO `ps_country_shop` VALUES (122,1);
INSERT INTO `ps_country_shop` VALUES (123,1);
INSERT INTO `ps_country_shop` VALUES (124,1);
INSERT INTO `ps_country_shop` VALUES (125,1);
INSERT INTO `ps_country_shop` VALUES (126,1);
INSERT INTO `ps_country_shop` VALUES (127,1);
INSERT INTO `ps_country_shop` VALUES (128,1);
INSERT INTO `ps_country_shop` VALUES (129,1);
INSERT INTO `ps_country_shop` VALUES (130,1);
INSERT INTO `ps_country_shop` VALUES (131,1);
INSERT INTO `ps_country_shop` VALUES (132,1);
INSERT INTO `ps_country_shop` VALUES (133,1);
INSERT INTO `ps_country_shop` VALUES (134,1);
INSERT INTO `ps_country_shop` VALUES (135,1);
INSERT INTO `ps_country_shop` VALUES (136,1);
INSERT INTO `ps_country_shop` VALUES (137,1);
INSERT INTO `ps_country_shop` VALUES (138,1);
INSERT INTO `ps_country_shop` VALUES (139,1);
INSERT INTO `ps_country_shop` VALUES (140,1);
INSERT INTO `ps_country_shop` VALUES (141,1);
INSERT INTO `ps_country_shop` VALUES (142,1);
INSERT INTO `ps_country_shop` VALUES (143,1);
INSERT INTO `ps_country_shop` VALUES (144,1);
INSERT INTO `ps_country_shop` VALUES (145,1);
INSERT INTO `ps_country_shop` VALUES (146,1);
INSERT INTO `ps_country_shop` VALUES (147,1);
INSERT INTO `ps_country_shop` VALUES (148,1);
INSERT INTO `ps_country_shop` VALUES (149,1);
INSERT INTO `ps_country_shop` VALUES (150,1);
INSERT INTO `ps_country_shop` VALUES (151,1);
INSERT INTO `ps_country_shop` VALUES (152,1);
INSERT INTO `ps_country_shop` VALUES (153,1);
INSERT INTO `ps_country_shop` VALUES (154,1);
INSERT INTO `ps_country_shop` VALUES (155,1);
INSERT INTO `ps_country_shop` VALUES (156,1);
INSERT INTO `ps_country_shop` VALUES (157,1);
INSERT INTO `ps_country_shop` VALUES (158,1);
INSERT INTO `ps_country_shop` VALUES (159,1);
INSERT INTO `ps_country_shop` VALUES (160,1);
INSERT INTO `ps_country_shop` VALUES (161,1);
INSERT INTO `ps_country_shop` VALUES (162,1);
INSERT INTO `ps_country_shop` VALUES (163,1);
INSERT INTO `ps_country_shop` VALUES (164,1);
INSERT INTO `ps_country_shop` VALUES (165,1);
INSERT INTO `ps_country_shop` VALUES (166,1);
INSERT INTO `ps_country_shop` VALUES (167,1);
INSERT INTO `ps_country_shop` VALUES (168,1);
INSERT INTO `ps_country_shop` VALUES (169,1);
INSERT INTO `ps_country_shop` VALUES (170,1);
INSERT INTO `ps_country_shop` VALUES (171,1);
INSERT INTO `ps_country_shop` VALUES (172,1);
INSERT INTO `ps_country_shop` VALUES (173,1);
INSERT INTO `ps_country_shop` VALUES (174,1);
INSERT INTO `ps_country_shop` VALUES (175,1);
INSERT INTO `ps_country_shop` VALUES (176,1);
INSERT INTO `ps_country_shop` VALUES (177,1);
INSERT INTO `ps_country_shop` VALUES (178,1);
INSERT INTO `ps_country_shop` VALUES (179,1);
INSERT INTO `ps_country_shop` VALUES (180,1);
INSERT INTO `ps_country_shop` VALUES (181,1);
INSERT INTO `ps_country_shop` VALUES (182,1);
INSERT INTO `ps_country_shop` VALUES (183,1);
INSERT INTO `ps_country_shop` VALUES (184,1);
INSERT INTO `ps_country_shop` VALUES (185,1);
INSERT INTO `ps_country_shop` VALUES (186,1);
INSERT INTO `ps_country_shop` VALUES (187,1);
INSERT INTO `ps_country_shop` VALUES (188,1);
INSERT INTO `ps_country_shop` VALUES (189,1);
INSERT INTO `ps_country_shop` VALUES (190,1);
INSERT INTO `ps_country_shop` VALUES (191,1);
INSERT INTO `ps_country_shop` VALUES (192,1);
INSERT INTO `ps_country_shop` VALUES (193,1);
INSERT INTO `ps_country_shop` VALUES (194,1);
INSERT INTO `ps_country_shop` VALUES (195,1);
INSERT INTO `ps_country_shop` VALUES (196,1);
INSERT INTO `ps_country_shop` VALUES (197,1);
INSERT INTO `ps_country_shop` VALUES (198,1);
INSERT INTO `ps_country_shop` VALUES (199,1);
INSERT INTO `ps_country_shop` VALUES (200,1);
INSERT INTO `ps_country_shop` VALUES (201,1);
INSERT INTO `ps_country_shop` VALUES (202,1);
INSERT INTO `ps_country_shop` VALUES (203,1);
INSERT INTO `ps_country_shop` VALUES (204,1);
INSERT INTO `ps_country_shop` VALUES (205,1);
INSERT INTO `ps_country_shop` VALUES (206,1);
INSERT INTO `ps_country_shop` VALUES (207,1);
INSERT INTO `ps_country_shop` VALUES (208,1);
INSERT INTO `ps_country_shop` VALUES (209,1);
INSERT INTO `ps_country_shop` VALUES (210,1);
INSERT INTO `ps_country_shop` VALUES (211,1);
INSERT INTO `ps_country_shop` VALUES (212,1);
INSERT INTO `ps_country_shop` VALUES (213,1);
INSERT INTO `ps_country_shop` VALUES (214,1);
INSERT INTO `ps_country_shop` VALUES (215,1);
INSERT INTO `ps_country_shop` VALUES (216,1);
INSERT INTO `ps_country_shop` VALUES (217,1);
INSERT INTO `ps_country_shop` VALUES (218,1);
INSERT INTO `ps_country_shop` VALUES (219,1);
INSERT INTO `ps_country_shop` VALUES (220,1);
INSERT INTO `ps_country_shop` VALUES (221,1);
INSERT INTO `ps_country_shop` VALUES (222,1);
INSERT INTO `ps_country_shop` VALUES (223,1);
INSERT INTO `ps_country_shop` VALUES (224,1);
INSERT INTO `ps_country_shop` VALUES (225,1);
INSERT INTO `ps_country_shop` VALUES (226,1);
INSERT INTO `ps_country_shop` VALUES (227,1);
INSERT INTO `ps_country_shop` VALUES (228,1);
INSERT INTO `ps_country_shop` VALUES (229,1);
INSERT INTO `ps_country_shop` VALUES (230,1);
INSERT INTO `ps_country_shop` VALUES (231,1);
INSERT INTO `ps_country_shop` VALUES (232,1);
INSERT INTO `ps_country_shop` VALUES (233,1);
INSERT INTO `ps_country_shop` VALUES (234,1);
INSERT INTO `ps_country_shop` VALUES (235,1);
INSERT INTO `ps_country_shop` VALUES (236,1);
INSERT INTO `ps_country_shop` VALUES (237,1);
INSERT INTO `ps_country_shop` VALUES (238,1);
INSERT INTO `ps_country_shop` VALUES (239,1);
INSERT INTO `ps_country_shop` VALUES (240,1);
INSERT INTO `ps_country_shop` VALUES (241,1);
INSERT INTO `ps_country_shop` VALUES (242,1);
INSERT INTO `ps_country_shop` VALUES (243,1);
INSERT INTO `ps_country_shop` VALUES (244,1);

/*!40000 ALTER TABLE `ps_country_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_address`, `id_country`, `id_state`, `name`, `company`, `address1`, `address2`, `city`, `postcode`, `phone`, `origin`, `active`, `date_add`, `date_upd` FROM `ps_cpl_address`;
    
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

DROP TABLE IF EXISTS `ps_cpl_address`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cpl_address` (
  `id_address` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_country` int(10) NOT NULL,
  `id_state` int(10) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `postcode` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `origin` int(10) NOT NULL DEFAULT '1',
  `active` int(10) NOT NULL DEFAULT '1',
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_address`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cpl_address` WRITE;
/*!40000 ALTER TABLE `ps_cpl_address` DISABLE KEYS */;
INSERT INTO `ps_cpl_address` VALUES (1,4,90,'KHEWA','Khewa','737 Riverside','','Wakefield','J0X3G0','8192308282',1,0,'2020-05-06 14:06:06','2020-05-06 14:06:06');

/*!40000 ALTER TABLE `ps_cpl_address` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:09+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_batch`, `date_add`, `date_upd` FROM `ps_cpl_batch`;
    
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

DROP TABLE IF EXISTS `ps_cpl_batch`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cpl_batch` (
  `id_batch` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_batch`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cpl_batch` WRITE;
/*!40000 ALTER TABLE `ps_cpl_batch` DISABLE KEYS */;
INSERT INTO `ps_cpl_batch` VALUES (11,'2020-05-06 14:12:47','2020-05-06 14:12:47');
INSERT INTO `ps_cpl_batch` VALUES (12,'2020-05-06 14:17:27','2020-05-06 14:17:27');
INSERT INTO `ps_cpl_batch` VALUES (14,'2020-05-06 14:29:13','2020-05-06 14:29:13');
INSERT INTO `ps_cpl_batch` VALUES (15,'2020-05-06 14:32:21','2020-05-06 14:32:21');
INSERT INTO `ps_cpl_batch` VALUES (18,'2020-05-28 09:53:50','2020-05-28 09:53:50');
INSERT INTO `ps_cpl_batch` VALUES (19,'2020-05-28 14:06:28','2020-05-28 14:06:28');
INSERT INTO `ps_cpl_batch` VALUES (20,'2020-06-18 16:25:14','2020-06-18 16:25:14');
INSERT INTO `ps_cpl_batch` VALUES (21,'2020-06-23 11:37:47','2020-06-23 11:37:47');
INSERT INTO `ps_cpl_batch` VALUES (22,'2020-06-25 13:14:49','2020-06-25 13:14:49');
INSERT INTO `ps_cpl_batch` VALUES (23,'2020-07-14 15:34:17','2020-07-14 15:34:17');
INSERT INTO `ps_cpl_batch` VALUES (24,'2020-07-28 13:01:39','2020-07-28 13:01:39');
INSERT INTO `ps_cpl_batch` VALUES (25,'2020-07-28 16:42:41','2020-07-28 16:42:41');
INSERT INTO `ps_cpl_batch` VALUES (26,'2020-08-10 09:47:35','2020-08-10 09:47:35');
INSERT INTO `ps_cpl_batch` VALUES (27,'2020-08-12 17:44:52','2020-08-12 17:44:52');
INSERT INTO `ps_cpl_batch` VALUES (28,'2020-08-16 18:08:53','2020-08-16 18:08:53');
INSERT INTO `ps_cpl_batch` VALUES (29,'2020-09-24 14:28:51','2020-09-24 14:28:51');
INSERT INTO `ps_cpl_batch` VALUES (30,'2020-09-24 17:06:23','2020-09-24 17:06:23');
INSERT INTO `ps_cpl_batch` VALUES (31,'2020-11-02 12:02:39','2020-11-02 12:02:39');

/*!40000 ALTER TABLE `ps_cpl_batch` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_group`, `name`, `active`, `date_add`, `date_upd` FROM `ps_cpl_group`;
    
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

DROP TABLE IF EXISTS `ps_cpl_group`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cpl_group` (
  `id_group` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` int(10) NOT NULL DEFAULT '1',
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_group`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cpl_group` WRITE;
/*!40000 ALTER TABLE `ps_cpl_group` DISABLE KEYS */;
INSERT INTO `ps_cpl_group` VALUES (1,'Default',1,'2020-05-06 10:43:57','2020-05-06 10:43:57');

/*!40000 ALTER TABLE `ps_cpl_group` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_manifest`, `poNumber`, `manifestDateTime`, `contractId`, `methodOfPayment`, `totalCost`, `self_link`, `details_link`, `label_link`, `manifest_shipments_link`, `date_add`, `date_upd` FROM `ps_cpl_manifest`;
    
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

DROP TABLE IF EXISTS `ps_cpl_manifest`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cpl_manifest` (
  `id_manifest` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poNumber` varchar(255) DEFAULT NULL,
  `manifestDateTime` datetime DEFAULT NULL,
  `contractId` varchar(255) DEFAULT NULL,
  `methodOfPayment` varchar(255) DEFAULT NULL,
  `totalCost` float unsigned DEFAULT NULL,
  `self_link` varchar(255) DEFAULT NULL,
  `details_link` varchar(255) DEFAULT NULL,
  `label_link` varchar(255) DEFAULT NULL,
  `manifest_shipments_link` varchar(255) DEFAULT NULL,
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_manifest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cpl_manifest` WRITE;
/*!40000 ALTER TABLE `ps_cpl_manifest` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_cpl_manifest` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cpl_rate_discount`, `id_shop` FROM `ps_cpl_rate_discount_shop`;
    
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

DROP TABLE IF EXISTS `ps_cpl_rate_discount_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cpl_rate_discount_shop` (
  `id_cpl_rate_discount` int(10) unsigned NOT NULL,
  `id_shop` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_cpl_rate_discount`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cpl_rate_discount_shop` WRITE;
/*!40000 ALTER TABLE `ps_cpl_rate_discount_shop` DISABLE KEYS */;
INSERT INTO `ps_cpl_rate_discount_shop` VALUES (2,1);

/*!40000 ALTER TABLE `ps_cpl_rate_discount_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_return_shipment`, `id_order`, `id_batch`, `name`, `address1`, `address2`, `city`, `province`, `postal_code`, `tracking_pin`, `service_code`, `return_label_link`, `date_add`, `date_upd` FROM `ps_cpl_return_shipment`;
    
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

DROP TABLE IF EXISTS `ps_cpl_return_shipment`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_cpl_return_shipment` (
  `id_return_shipment` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned DEFAULT NULL,
  `id_batch` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `tracking_pin` varchar(255) DEFAULT NULL,
  `service_code` varchar(255) DEFAULT NULL,
  `return_label_link` varchar(255) DEFAULT NULL,
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_return_shipment`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_cpl_return_shipment` WRITE;
/*!40000 ALTER TABLE `ps_cpl_return_shipment` DISABLE KEYS */;
INSERT INTO `ps_cpl_return_shipment` VALUES (1,2040,0,'Sara tran','56 ANNIE CRAIG DRIVE ','SUITE 1701','TORONTO','ON','M8V 0C8','8202835022608222','DOM.RP','https://soa-gw.canadapost.ca/rs/artifact/3501b2d6afd210d7/10236148261/0','2021-03-29 11:03:47','2021-03-29 11:03:47');

/*!40000 ALTER TABLE `ps_cpl_return_shipment` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_autocomplete_products`, `prd_specify`, `prd_name` FROM `ps_crazy_autocomplete_products`;
    
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

DROP TABLE IF EXISTS `ps_crazy_autocomplete_products`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_crazy_autocomplete_products` (
  `id_autocomplete_products` int(11) NOT NULL AUTO_INCREMENT,
  `prd_specify` longtext,
  `prd_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id_autocomplete_products`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_crazy_autocomplete_products` WRITE;
/*!40000 ALTER TABLE `ps_crazy_autocomplete_products` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_crazy_autocomplete_products` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_crazy_fonts`, `title`, `font_weight`, `font_style`, `woff`, `woff2`, `ttf`, `svg`, `eot`, `active`, `fontname` FROM `ps_crazy_fonts`;
    
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

DROP TABLE IF EXISTS `ps_crazy_fonts`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_crazy_fonts` (
  `id_crazy_fonts` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `font_weight` varchar(255) NOT NULL,
  `font_style` varchar(256) NOT NULL,
  `woff` varchar(256) NOT NULL,
  `woff2` varchar(256) NOT NULL,
  `ttf` varchar(256) NOT NULL,
  `svg` varchar(256) NOT NULL,
  `eot` varchar(256) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `fontname` varchar(100) NOT NULL,
  PRIMARY KEY (`id_crazy_fonts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_crazy_fonts` WRITE;
/*!40000 ALTER TABLE `ps_crazy_fonts` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_crazy_fonts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_elegantalseoessentials_canonicals`, `is_active`, `created_at` FROM `ps_elegantalseoessentials_canonicals`;
    
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

DROP TABLE IF EXISTS `ps_elegantalseoessentials_canonicals`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_elegantalseoessentials_canonicals` (
  `id_elegantalseoessentials_canonicals` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_elegantalseoessentials_canonicals`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_elegantalseoessentials_canonicals` WRITE;
/*!40000 ALTER TABLE `ps_elegantalseoessentials_canonicals` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_elegantalseoessentials_canonicals` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_elegantalseoessentials_canonicals`, `id_lang`, `old_url`, `new_url` FROM `ps_elegantalseoessentials_canonicals_lang`;
    
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

DROP TABLE IF EXISTS `ps_elegantalseoessentials_canonicals_lang`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_elegantalseoessentials_canonicals_lang` (
  `id_elegantalseoessentials_canonicals` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `old_url` text NOT NULL,
  `new_url` text NOT NULL,
  PRIMARY KEY (`id_elegantalseoessentials_canonicals`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_elegantalseoessentials_canonicals_lang` WRITE;
/*!40000 ALTER TABLE `ps_elegantalseoessentials_canonicals_lang` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_elegantalseoessentials_canonicals_lang` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_elegantalseoessentials_canonicals`, `id_shop` FROM `ps_elegantalseoessentials_canonicals_shop`;
    
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

DROP TABLE IF EXISTS `ps_elegantalseoessentials_canonicals_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_elegantalseoessentials_canonicals_shop` (
  `id_elegantalseoessentials_canonicals` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_elegantalseoessentials_canonicals`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_elegantalseoessentials_canonicals_shop` WRITE;
/*!40000 ALTER TABLE `ps_elegantalseoessentials_canonicals_shop` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_elegantalseoessentials_canonicals_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_elegantalseoessentials_html`, `name`, `hooks`, `pages`, `position`, `is_active` FROM `ps_elegantalseoessentials_html`;
    
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

DROP TABLE IF EXISTS `ps_elegantalseoessentials_html`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_elegantalseoessentials_html` (
  `id_elegantalseoessentials_html` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `hooks` text NOT NULL,
  `pages` text NOT NULL,
  `position` int(11) unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_elegantalseoessentials_html`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_elegantalseoessentials_html` WRITE;
/*!40000 ALTER TABLE `ps_elegantalseoessentials_html` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_elegantalseoessentials_html` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_elegantalseoessentials_html`, `id_lang`, `html` FROM `ps_elegantalseoessentials_html_lang`;
    
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

DROP TABLE IF EXISTS `ps_elegantalseoessentials_html_lang`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_elegantalseoessentials_html_lang` (
  `id_elegantalseoessentials_html` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `html` text NOT NULL,
  PRIMARY KEY (`id_elegantalseoessentials_html`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_elegantalseoessentials_html_lang` WRITE;
/*!40000 ALTER TABLE `ps_elegantalseoessentials_html_lang` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_elegantalseoessentials_html_lang` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_elegantalseoessentials_html`, `id_shop` FROM `ps_elegantalseoessentials_html_shop`;
    
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

DROP TABLE IF EXISTS `ps_elegantalseoessentials_html_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_elegantalseoessentials_html_shop` (
  `id_elegantalseoessentials_html` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_elegantalseoessentials_html`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_elegantalseoessentials_html_shop` WRITE;
/*!40000 ALTER TABLE `ps_elegantalseoessentials_html_shop` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_elegantalseoessentials_html_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_elegantalseoessentials_redirects`, `id_product`, `old_url`, `new_url`, `redirect_type`, `is_active`, `created_at`, `expires_at` FROM `ps_elegantalseoessentials_redirects`;
    
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

DROP TABLE IF EXISTS `ps_elegantalseoessentials_redirects`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_elegantalseoessentials_redirects` (
  `id_elegantalseoessentials_redirects` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(11) unsigned DEFAULT NULL,
  `old_url` text NOT NULL,
  `new_url` text NOT NULL,
  `redirect_type` varchar(6) NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_elegantalseoessentials_redirects`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_elegantalseoessentials_redirects` WRITE;
/*!40000 ALTER TABLE `ps_elegantalseoessentials_redirects` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_elegantalseoessentials_redirects` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_elegantalseoessentials_redirects`, `id_shop` FROM `ps_elegantalseoessentials_redirects_shop`;
    
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

DROP TABLE IF EXISTS `ps_elegantalseoessentials_redirects_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_elegantalseoessentials_redirects_shop` (
  `id_elegantalseoessentials_redirects` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_elegantalseoessentials_redirects`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_elegantalseoessentials_redirects_shop` WRITE;
/*!40000 ALTER TABLE `ps_elegantalseoessentials_redirects_shop` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_elegantalseoessentials_redirects_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_etsy_attribute_mapping`, `property_id`, `property_title`, `id_profile_category`, `id_etsy_profiles`, `id_attribute_group`, `date_added`, `date_updated` FROM `ps_etsy_attribute_mapping`;
    
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

DROP TABLE IF EXISTS `ps_etsy_attribute_mapping`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_attribute_mapping` (
  `id_etsy_attribute_mapping` int(10) NOT NULL AUTO_INCREMENT,
  `property_id` varchar(20) NOT NULL,
  `property_title` varchar(255) NOT NULL,
  `id_profile_category` int(10) NOT NULL,
  `id_etsy_profiles` int(10) NOT NULL,
  `id_attribute_group` int(10) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_etsy_attribute_mapping`),
  KEY `property_id` (`property_id`,`property_title`,`id_profile_category`,`id_etsy_profiles`,`id_attribute_group`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_attribute_mapping` WRITE;
/*!40000 ALTER TABLE `ps_etsy_attribute_mapping` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_etsy_attribute_mapping` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_attribute_mapping`, `property_id`, `property_title`, `id_attribute_group`, `date_added`, `date_updated` FROM `ps_etsy_attribute_mapping1`;
    
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

DROP TABLE IF EXISTS `ps_etsy_attribute_mapping1`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_attribute_mapping1` (
  `id_attribute_mapping` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `property_title` varchar(500) NOT NULL,
  `id_attribute_group` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id_attribute_mapping`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_attribute_mapping1` WRITE;
/*!40000 ALTER TABLE `ps_etsy_attribute_mapping1` DISABLE KEYS */;
INSERT INTO `ps_etsy_attribute_mapping1` VALUES (1,14,'',1,'2021-01-26 12:15:11','2021-01-26 12:15:41');
INSERT INTO `ps_etsy_attribute_mapping1` VALUES (2,14,'',2,'2021-01-26 12:15:47','2021-01-26 12:16:50');
INSERT INTO `ps_etsy_attribute_mapping1` VALUES (3,1,'',6,'2021-01-26 12:16:03','2021-01-26 12:16:08');
INSERT INTO `ps_etsy_attribute_mapping1` VALUES (4,0,'',7,'2021-01-26 12:16:29','2021-01-26 12:16:29');
INSERT INTO `ps_etsy_attribute_mapping1` VALUES (5,3,'',16,'2021-01-26 12:16:58','2021-01-26 12:17:19');

/*!40000 ALTER TABLE `ps_etsy_attribute_mapping1` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `attribute_id`, `etsy_property_id`, `etsy_property_title` FROM `ps_etsy_attributes`;
    
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

DROP TABLE IF EXISTS `ps_etsy_attributes`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_attributes` (
  `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `etsy_property_id` int(11) NOT NULL,
  `etsy_property_title` varchar(100) NOT NULL,
  PRIMARY KEY (`attribute_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_attributes` WRITE;
/*!40000 ALTER TABLE `ps_etsy_attributes` DISABLE KEYS */;
INSERT INTO `ps_etsy_attributes` VALUES (1,200,'Color');
INSERT INTO `ps_etsy_attributes` VALUES (2,515,'Device');
INSERT INTO `ps_etsy_attributes` VALUES (3,504,'Diameter');
INSERT INTO `ps_etsy_attributes` VALUES (4,501,'Dimensions');
INSERT INTO `ps_etsy_attributes` VALUES (5,502,'Fabric');
INSERT INTO `ps_etsy_attributes` VALUES (6,500,'Finish');
INSERT INTO `ps_etsy_attributes` VALUES (7,503,'Flavor');
INSERT INTO `ps_etsy_attributes` VALUES (8,505,'Height');
INSERT INTO `ps_etsy_attributes` VALUES (9,506,'Length');
INSERT INTO `ps_etsy_attributes` VALUES (10,507,'Material');
INSERT INTO `ps_etsy_attributes` VALUES (11,508,'Pattern');
INSERT INTO `ps_etsy_attributes` VALUES (12,509,'Scent');
INSERT INTO `ps_etsy_attributes` VALUES (13,510,'Style');
INSERT INTO `ps_etsy_attributes` VALUES (14,100,'Size');
INSERT INTO `ps_etsy_attributes` VALUES (15,511,'Weight');
INSERT INTO `ps_etsy_attributes` VALUES (16,512,'Width');

/*!40000 ALTER TABLE `ps_etsy_attributes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_etsy_categories`, `category_code`, `category_name`, `property_set`, `tag`, `parent_id`, `last_level` FROM `ps_etsy_categories`;
    
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

DROP TABLE IF EXISTS `ps_etsy_categories`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_categories` (
  `id_etsy_categories` int(10) NOT NULL AUTO_INCREMENT,
  `category_code` int(10) NOT NULL,
  `category_name` text CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  `property_set` text NOT NULL,
  `tag` varchar(250) DEFAULT NULL,
  `parent_id` int(1) DEFAULT '0',
  `last_level` int(1) DEFAULT '0',
  PRIMARY KEY (`id_etsy_categories`),
  KEY `category_code` (`category_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3076 DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_categories` WRITE;
/*!40000 ALTER TABLE `ps_etsy_categories` DISABLE KEYS */;
INSERT INTO `ps_etsy_categories` VALUES (1,1,'Accessories','','accessories',0,0);
INSERT INTO `ps_etsy_categories` VALUES (2,1728,'Accessories > Baby Accessories','','accessories.baby_accessories',1,0);
INSERT INTO `ps_etsy_categories` VALUES (3,135,'Accessories > Baby Accessories > Baby Carriers & Wraps','','accessories.baby_accessories.baby_carriers_and_wraps',2,1);
INSERT INTO `ps_etsy_categories` VALUES (4,6078,'Accessories > Baby Accessories > Children\'s Photo Props','','accessories.baby_accessories.childrens_photo_props',2,1);
INSERT INTO `ps_etsy_categories` VALUES (5,2,'Accessories > Belts & Suspenders','','accessories.belts_and_suspenders',1,0);
INSERT INTO `ps_etsy_categories` VALUES (6,3,'Accessories > Belts & Suspenders > Belt Buckles','','accessories.belts_and_suspenders.belt_buckles',5,1);
INSERT INTO `ps_etsy_categories` VALUES (7,4,'Accessories > Belts & Suspenders > Belts','','accessories.belts_and_suspenders.belts',5,1);
INSERT INTO `ps_etsy_categories` VALUES (8,5,'Accessories > Belts & Suspenders > Suspenders','','accessories.belts_and_suspenders.suspenders',5,1);
INSERT INTO `ps_etsy_categories` VALUES (9,10783,'Accessories > Bouquets & Corsages','','accessories.bouquets_and_corsages',1,0);
INSERT INTO `ps_etsy_categories` VALUES (10,603,'Accessories > Bouquets & Corsages > Bouquets','','accessories.bouquets_and_corsages.bouquets',9,1);
INSERT INTO `ps_etsy_categories` VALUES (11,11276,'Accessories > Bouquets & Corsages > Boutonnières','','accessories.bouquets_and_corsages.boutonnieres',9,1);
INSERT INTO `ps_etsy_categories` VALUES (12,604,'Accessories > Bouquets & Corsages > Corsages','','accessories.bouquets_and_corsages.corsages',9,1);
INSERT INTO `ps_etsy_categories` VALUES (13,6,'Accessories > Costume Accessories','','accessories.costume_accessories',1,0);
INSERT INTO `ps_etsy_categories` VALUES (14,7,'Accessories > Costume Accessories > Capes','','accessories.costume_accessories.capes',13,1);
INSERT INTO `ps_etsy_categories` VALUES (15,8,'Accessories > Costume Accessories > Costume Goggles','','accessories.costume_accessories.costume_goggles',13,1);
INSERT INTO `ps_etsy_categories` VALUES (16,9,'Accessories > Costume Accessories > Costume Hats & Headpieces','','accessories.costume_accessories.costume_hats_and_headpieces',13,1);
INSERT INTO `ps_etsy_categories` VALUES (17,13,'Accessories > Costume Accessories > Costume Tails & Ears','','accessories.costume_accessories.costume_tails_and_ears',13,0);
INSERT INTO `ps_etsy_categories` VALUES (18,1761,'Accessories > Costume Accessories > Costume Tails & Ears > Costume Ears','','accessories.costume_accessories.costume_tails_and_ears.costume_ears',17,1);
INSERT INTO `ps_etsy_categories` VALUES (19,1760,'Accessories > Costume Accessories > Costume Tails & Ears > Costume Tails','','accessories.costume_accessories.costume_tails_and_ears.costume_tails',17,1);
INSERT INTO `ps_etsy_categories` VALUES (20,10,'Accessories > Costume Accessories > Costume Weapons','','accessories.costume_accessories.costume_weapons',13,1);
INSERT INTO `ps_etsy_categories` VALUES (21,11,'Accessories > Costume Accessories > Facial Hair','','accessories.costume_accessories.facial_hair',13,1);
INSERT INTO `ps_etsy_categories` VALUES (22,12,'Accessories > Costume Accessories > Masks & Prosthetics','','accessories.costume_accessories.masks_and_prosthetics',13,0);
INSERT INTO `ps_etsy_categories` VALUES (23,1768,'Accessories > Costume Accessories > Masks & Prosthetics > Masks','','accessories.costume_accessories.masks_and_prosthetics.masks',22,1);
INSERT INTO `ps_etsy_categories` VALUES (24,2168,'Accessories > Costume Accessories > Masks & Prosthetics > Prosthetics','','accessories.costume_accessories.masks_and_prosthetics.prosthetics',22,1);
INSERT INTO `ps_etsy_categories` VALUES (25,14,'Accessories > Costume Accessories > Wands','','accessories.costume_accessories.wands',13,1);
INSERT INTO `ps_etsy_categories` VALUES (26,15,'Accessories > Costume Accessories > Wings','','accessories.costume_accessories.wings',13,1);
INSERT INTO `ps_etsy_categories` VALUES (27,16,'Accessories > Gloves & Mittens','','accessories.gloves_and_mittens',1,0);
INSERT INTO `ps_etsy_categories` VALUES (28,17,'Accessories > Gloves & Mittens > Arm Warmers','','accessories.gloves_and_mittens.arm_warmers',27,1);
INSERT INTO `ps_etsy_categories` VALUES (29,18,'Accessories > Gloves & Mittens > Costume Gloves','','accessories.gloves_and_mittens.costume_gloves',27,1);
INSERT INTO `ps_etsy_categories` VALUES (30,19,'Accessories > Gloves & Mittens > Driving Gloves','','accessories.gloves_and_mittens.driving_gloves',27,1);
INSERT INTO `ps_etsy_categories` VALUES (31,20,'Accessories > Gloves & Mittens > Evening & Formal Gloves','','accessories.gloves_and_mittens.evening_and_formal_gloves',27,1);
INSERT INTO `ps_etsy_categories` VALUES (32,21,'Accessories > Gloves & Mittens > Gardening & Work Gloves','','accessories.gloves_and_mittens.gardening_and_work_gloves',27,0);
INSERT INTO `ps_etsy_categories` VALUES (33,1762,'Accessories > Gloves & Mittens > Gardening & Work Gloves > Gardening Gloves','','accessories.gloves_and_mittens.gardening_and_work_gloves.gardening_gloves',32,1);
INSERT INTO `ps_etsy_categories` VALUES (34,1763,'Accessories > Gloves & Mittens > Gardening & Work Gloves > Work Gloves','','accessories.gloves_and_mittens.gardening_and_work_gloves.work_gloves',32,1);
INSERT INTO `ps_etsy_categories` VALUES (35,22,'Accessories > Gloves & Mittens > Mittens & Muffs','','accessories.gloves_and_mittens.mittens_and_muffs',27,0);
INSERT INTO `ps_etsy_categories` VALUES (36,1764,'Accessories > Gloves & Mittens > Mittens & Muffs > Mittens','','accessories.gloves_and_mittens.mittens_and_muffs.mittens',35,1);
INSERT INTO `ps_etsy_categories` VALUES (37,1765,'Accessories > Gloves & Mittens > Mittens & Muffs > Muffs','','accessories.gloves_and_mittens.mittens_and_muffs.muffs',35,1);
INSERT INTO `ps_etsy_categories` VALUES (38,23,'Accessories > Gloves & Mittens > Sports Gloves','','accessories.gloves_and_mittens.sports_gloves',27,1);
INSERT INTO `ps_etsy_categories` VALUES (39,24,'Accessories > Gloves & Mittens > Winter Gloves','','accessories.gloves_and_mittens.winter_gloves',27,1);
INSERT INTO `ps_etsy_categories` VALUES (40,219,'Accessories > Hair Accessories','','accessories.hair_accessories',1,0);
INSERT INTO `ps_etsy_categories` VALUES (41,220,'Accessories > Hair Accessories > Barrettes & Clips','','accessories.hair_accessories.barrettes_and_clips',40,1);
INSERT INTO `ps_etsy_categories` VALUES (42,221,'Accessories > Hair Accessories > Bun Holders & Makers','','accessories.hair_accessories.bun_holders_and_makers',40,1);
INSERT INTO `ps_etsy_categories` VALUES (43,222,'Accessories > Hair Accessories > Decorative Combs','','accessories.hair_accessories.decorative_combs',40,1);
INSERT INTO `ps_etsy_categories` VALUES (44,1758,'Accessories > Hair Accessories > Hair Jewelry','','accessories.hair_accessories.hair_jewelry',40,1);
INSERT INTO `ps_etsy_categories` VALUES (45,6114,'Accessories > Hair Accessories > Hair Picks','','accessories.hair_accessories.hair_picks',40,1);
INSERT INTO `ps_etsy_categories` VALUES (46,224,'Accessories > Hair Accessories > Hair Pins','','accessories.hair_accessories.hair_pins',40,1);
INSERT INTO `ps_etsy_categories` VALUES (47,225,'Accessories > Hair Accessories > Headbands & Turbans','','accessories.hair_accessories.headbands',40,0);
INSERT INTO `ps_etsy_categories` VALUES (48,2906,'Accessories > Hair Accessories > Headbands & Turbans > Baby Headbands','','accessories.hair_accessories.headbands.baby_headbands',47,1);
INSERT INTO `ps_etsy_categories` VALUES (49,226,'Accessories > Hair Accessories > Ties & Elastics','','accessories.hair_accessories.ties_and_elastics',40,1);
INSERT INTO `ps_etsy_categories` VALUES (50,227,'Accessories > Hair Accessories > Wreaths & Tiaras','','accessories.hair_accessories.wreaths_and_tiaras',40,0);
INSERT INTO `ps_etsy_categories` VALUES (51,2114,'Accessories > Hair Accessories > Wreaths & Tiaras > Tiaras','','accessories.hair_accessories.wreaths_and_tiaras.tiaras',50,1);
INSERT INTO `ps_etsy_categories` VALUES (52,2113,'Accessories > Hair Accessories > Wreaths & Tiaras > Wreaths','','accessories.hair_accessories.wreaths_and_tiaras.wreaths',50,1);
INSERT INTO `ps_etsy_categories` VALUES (53,11245,'Accessories > Hand Fans','','accessories.hand_fans',1,1);
INSERT INTO `ps_etsy_categories` VALUES (54,25,'Accessories > Hats & Caps','','accessories.hats_and_caps',1,0);
INSERT INTO `ps_etsy_categories` VALUES (55,26,'Accessories > Hats & Caps > Baseball & Trucker Caps','','accessories.hats_and_caps.baseball_and_trucker_caps',54,1);
INSERT INTO `ps_etsy_categories` VALUES (56,27,'Accessories > Hats & Caps > Berets & Tams','','accessories.hats_and_caps.berets_and_tams',54,0);
INSERT INTO `ps_etsy_categories` VALUES (57,2140,'Accessories > Hats & Caps > Berets & Tams > Berets','','accessories.hats_and_caps.berets_and_tams.berets',56,1);
INSERT INTO `ps_etsy_categories` VALUES (58,2141,'Accessories > Hats & Caps > Berets & Tams > Tams','','accessories.hats_and_caps.berets_and_tams.tams',56,1);
INSERT INTO `ps_etsy_categories` VALUES (59,2535,'Accessories > Hats & Caps > Boaters & Panama Hats','','accessories.hats_and_caps.boaters_and_panama_hats',54,1);
INSERT INTO `ps_etsy_categories` VALUES (60,28,'Accessories > Hats & Caps > Bucket Hats','','accessories.hats_and_caps.bucket_hats',54,1);
INSERT INTO `ps_etsy_categories` VALUES (61,29,'Accessories > Hats & Caps > Cowboy Hats','','accessories.hats_and_caps.cowboy_hats',54,1);
INSERT INTO `ps_etsy_categories` VALUES (62,30,'Accessories > Hats & Caps > Earmuffs & Ear Warmers','','accessories.hats_and_caps.earmuffs_and_ear_warmers',54,0);
INSERT INTO `ps_etsy_categories` VALUES (63,2224,'Accessories > Hats & Caps > Earmuffs & Ear Warmers > Ear Warmers','','accessories.hats_and_caps.earmuffs_and_ear_warmers.ear_warmers',62,1);
INSERT INTO `ps_etsy_categories` VALUES (64,2169,'Accessories > Hats & Caps > Earmuffs & Ear Warmers > Earmuffs','','accessories.hats_and_caps.earmuffs_and_ear_warmers.earmuffs',62,1);
INSERT INTO `ps_etsy_categories` VALUES (65,1727,'Accessories > Hats & Caps > Fascinators & Mini Hats','','accessories.hats_and_caps.fascinators_and_mini_hats',54,0);
INSERT INTO `ps_etsy_categories` VALUES (66,2060,'Accessories > Hats & Caps > Fascinators & Mini Hats > Fascinators','','accessories.hats_and_caps.fascinators_and_mini_hats.fascinators',65,1);
INSERT INTO `ps_etsy_categories` VALUES (67,2061,'Accessories > Hats & Caps > Fascinators & Mini Hats > Mini Hats','','accessories.hats_and_caps.fascinators_and_mini_hats.mini_hats',65,1);
INSERT INTO `ps_etsy_categories` VALUES (68,31,'Accessories > Hats & Caps > Fedoras','','accessories.hats_and_caps.fedoras',54,1);
INSERT INTO `ps_etsy_categories` VALUES (69,32,'Accessories > Hats & Caps > Formal Hats','','accessories.hats_and_caps.formal_hats',54,0);
INSERT INTO `ps_etsy_categories` VALUES (70,2531,'Accessories > Hats & Caps > Formal Hats > Bowler Hats','','accessories.hats_and_caps.formal_hats.bowler_hats',69,1);
INSERT INTO `ps_etsy_categories` VALUES (71,2533,'Accessories > Hats & Caps > Formal Hats > Cloche Hats','','accessories.hats_and_caps.formal_hats.cloche_hats',69,1);
INSERT INTO `ps_etsy_categories` VALUES (72,2534,'Accessories > Hats & Caps > Formal Hats > Pillbox Hats','','accessories.hats_and_caps.formal_hats.pillbox_hats',69,1);
INSERT INTO `ps_etsy_categories` VALUES (73,2532,'Accessories > Hats & Caps > Formal Hats > Top Hats','','accessories.hats_and_caps.formal_hats.top_hats',69,1);
INSERT INTO `ps_etsy_categories` VALUES (74,11316,'Accessories > Hats & Caps > Hat Pins & Stick Pins','','accessories.hats_and_caps.hat_pins_and_stick_pins',54,1);
INSERT INTO `ps_etsy_categories` VALUES (75,2153,'Accessories > Hats & Caps > Helmets','','accessories.hats_and_caps.helmets',54,0);
INSERT INTO `ps_etsy_categories` VALUES (76,2540,'Accessories > Hats & Caps > Helmets > Military Helmets','','accessories.hats_and_caps.helmets.military_helmets',75,1);
INSERT INTO `ps_etsy_categories` VALUES (77,11334,'Accessories > Hats & Caps > Helmets > Motorcycle Helmets','','accessories.hats_and_caps.helmets.motorcycle_helmets',75,1);
INSERT INTO `ps_etsy_categories` VALUES (78,2154,'Accessories > Hats & Caps > Helmets > Sports Helmets','','accessories.hats_and_caps.helmets.sports_helmets',75,0);
INSERT INTO `ps_etsy_categories` VALUES (79,2803,'Accessories > Hats & Caps > Helmets > Sports Helmets > Baseball Helmets','','accessories.hats_and_caps.helmets.sports_helmets.baseball_helmets',78,1);
INSERT INTO `ps_etsy_categories` VALUES (80,2804,'Accessories > Hats & Caps > Helmets > Sports Helmets > Football Helmets','','accessories.hats_and_caps.helmets.sports_helmets.football_helmets',78,1);
INSERT INTO `ps_etsy_categories` VALUES (81,33,'Accessories > Hats & Caps > Newsboy Caps','','accessories.hats_and_caps.newsboy_caps',54,1);
INSERT INTO `ps_etsy_categories` VALUES (82,6080,'Accessories > Hats & Caps > Slouchy Hats','','accessories.hats_and_caps.slouchy_hats',54,1);
INSERT INTO `ps_etsy_categories` VALUES (83,34,'Accessories > Hats & Caps > Sun Hats & Visors','','accessories.hats_and_caps.sun_hats_and_visors',54,0);
INSERT INTO `ps_etsy_categories` VALUES (84,1775,'Accessories > Hats & Caps > Sun Hats & Visors > Sun Hats','','accessories.hats_and_caps.sun_hats_and_visors.sun_hats',83,1);
INSERT INTO `ps_etsy_categories` VALUES (85,1883,'Accessories > Hats & Caps > Sun Hats & Visors > Sunbonnets','','accessories.hats_and_caps.sun_hats_and_visors.sunbonnets',83,1);
INSERT INTO `ps_etsy_categories` VALUES (86,1774,'Accessories > Hats & Caps > Sun Hats & Visors > Visors','','accessories.hats_and_caps.sun_hats_and_visors.visors',83,1);
INSERT INTO `ps_etsy_categories` VALUES (87,35,'Accessories > Hats & Caps > Veils','','accessories.hats_and_caps.veils',54,1);
INSERT INTO `ps_etsy_categories` VALUES (88,36,'Accessories > Hats & Caps > Winter Hats','','accessories.hats_and_caps.winter_hats',54,0);
INSERT INTO `ps_etsy_categories` VALUES (89,37,'Accessories > Hats & Caps > Winter Hats > Skull Caps & Beanies','','accessories.hats_and_caps.winter_hats.skull_caps_and_beanies',88,1);
INSERT INTO `ps_etsy_categories` VALUES (90,38,'Accessories > Hats & Caps > Winter Hats > Trapper Hats','','accessories.hats_and_caps.winter_hats.trapper_hats',88,1);
INSERT INTO `ps_etsy_categories` VALUES (91,164,'Accessories > Keychains & Lanyards','','accessories.keychains_and_lanyards',1,0);
INSERT INTO `ps_etsy_categories` VALUES (92,165,'Accessories > Keychains & Lanyards > Keychains','','accessories.keychains_and_lanyards.keychains',91,1);
INSERT INTO `ps_etsy_categories` VALUES (93,166,'Accessories > Keychains & Lanyards > Lanyards & Badge Holders','','accessories.keychains_and_lanyards.lanyards',91,1);
INSERT INTO `ps_etsy_categories` VALUES (94,167,'Accessories > Keychains & Lanyards > Zipper Charms','','accessories.keychains_and_lanyards.zipper_charms',91,1);
INSERT INTO `ps_etsy_categories` VALUES (95,39,'Accessories > Patches & Pins','','accessories.patches_and_pins',1,0);
INSERT INTO `ps_etsy_categories` VALUES (96,40,'Accessories > Patches & Pins > Patches','','accessories.patches_and_pins.patches',95,1);
INSERT INTO `ps_etsy_categories` VALUES (97,41,'Accessories > Patches & Pins > Pins & Pinback Buttons','','accessories.patches_and_pins.pins_and_pinback_buttons',95,1);
INSERT INTO `ps_etsy_categories` VALUES (98,42,'Accessories > Scarves & Wraps','','accessories.scarves_and_wraps',1,0);
INSERT INTO `ps_etsy_categories` VALUES (99,43,'Accessories > Scarves & Wraps > Bandanas','','accessories.scarves_and_wraps.bandanas',98,1);
INSERT INTO `ps_etsy_categories` VALUES (100,44,'Accessories > Scarves & Wraps > Collars & Bibs','','accessories.scarves_and_wraps.collars_and_bibs',98,0);
INSERT INTO `ps_etsy_categories` VALUES (101,2172,'Accessories > Scarves & Wraps > Collars & Bibs > Bibs','','accessories.scarves_and_wraps.collars_and_bibs.bibs',100,1);
INSERT INTO `ps_etsy_categories` VALUES (102,2171,'Accessories > Scarves & Wraps > Collars & Bibs > Collars','','accessories.scarves_and_wraps.collars_and_bibs.collars',100,1);
INSERT INTO `ps_etsy_categories` VALUES (103,45,'Accessories > Scarves & Wraps > Handkerchiefs','','accessories.scarves_and_wraps.handkerchiefs',98,1);
INSERT INTO `ps_etsy_categories` VALUES (104,46,'Accessories > Scarves & Wraps > Scarves','','accessories.scarves_and_wraps.scarves',98,0);
INSERT INTO `ps_etsy_categories` VALUES (105,6079,'Accessories > Scarves & Wraps > Scarves > Hooded Scarves','','accessories.scarves_and_wraps.scarves.hooded_scarves',104,1);
INSERT INTO `ps_etsy_categories` VALUES (106,47,'Accessories > Scarves & Wraps > Shawls & Wraps','','accessories.scarves_and_wraps.shawls_and_wraps',98,0);
INSERT INTO `ps_etsy_categories` VALUES (107,6065,'Accessories > Scarves & Wraps > Shawls & Wraps > Shawl Pins','','accessories.scarves_and_wraps.shawls_and_wraps.shawl_pins',106,1);
INSERT INTO `ps_etsy_categories` VALUES (108,48,'Accessories > Suit & Tie Accessories','','accessories.suit_and_tie_accessories',1,0);
INSERT INTO `ps_etsy_categories` VALUES (109,49,'Accessories > Suit & Tie Accessories > Ascots','','accessories.suit_and_tie_accessories.ascots',108,1);
INSERT INTO `ps_etsy_categories` VALUES (110,50,'Accessories > Suit & Tie Accessories > Bolo Ties','','accessories.suit_and_tie_accessories.bolo_ties',108,1);
INSERT INTO `ps_etsy_categories` VALUES (111,51,'Accessories > Suit & Tie Accessories > Bow Ties','','accessories.suit_and_tie_accessories.bow_ties',108,1);
INSERT INTO `ps_etsy_categories` VALUES (112,57,'Accessories > Suit & Tie Accessories > Cuff Links & Tie Clips','','accessories.suit_and_tie_accessories.cufflinks_and_tie_tacks',108,0);
INSERT INTO `ps_etsy_categories` VALUES (113,2852,'Accessories > Suit & Tie Accessories > Cuff Links & Tie Clips > Collar Stays','','accessories.suit_and_tie_accessories.cufflinks_and_tie_tacks.collar_stays',112,1);
INSERT INTO `ps_etsy_categories` VALUES (114,52,'Accessories > Suit & Tie Accessories > Cuff Links & Tie Clips > Cuff Links','','accessories.suit_and_tie_accessories.cufflinks_and_tie_tacks.cuff_links',112,1);
INSERT INTO `ps_etsy_categories` VALUES (115,2851,'Accessories > Suit & Tie Accessories > Cuff Links & Tie Clips > Tie Bars','','accessories.suit_and_tie_accessories.cufflinks_and_tie_tacks.tie_bars',112,1);
INSERT INTO `ps_etsy_categories` VALUES (116,2216,'Accessories > Suit & Tie Accessories > Cuff Links & Tie Clips > Tie Clips & Tacks','','accessories.suit_and_tie_accessories.cufflinks_and_tie_tacks.tie_tacks',112,1);
INSERT INTO `ps_etsy_categories` VALUES (117,53,'Accessories > Suit & Tie Accessories > Cummerbunds','','accessories.suit_and_tie_accessories.cummerbunds',108,1);
INSERT INTO `ps_etsy_categories` VALUES (118,54,'Accessories > Suit & Tie Accessories > Neckties','','accessories.suit_and_tie_accessories.neckties',108,1);
INSERT INTO `ps_etsy_categories` VALUES (119,55,'Accessories > Suit & Tie Accessories > Pocket Squares','','accessories.suit_and_tie_accessories.pocket_squares',108,1);
INSERT INTO `ps_etsy_categories` VALUES (120,56,'Accessories > Suit & Tie Accessories > Shirt Studs','','accessories.suit_and_tie_accessories.shirt_studs',108,1);
INSERT INTO `ps_etsy_categories` VALUES (121,58,'Accessories > Sunglasses & Eyewear','','accessories.sunglasses_and_eyewear',1,0);
INSERT INTO `ps_etsy_categories` VALUES (122,11335,'Accessories > Sunglasses & Eyewear > Contact Lens Cases','','accessories.sunglasses_and_eyewear.contact_lens_cases',121,1);
INSERT INTO `ps_etsy_categories` VALUES (123,11358,'Accessories > Sunglasses & Eyewear > Eyeglass Stands','','accessories.sunglasses_and_eyewear.eyeglass_stands',121,1);
INSERT INTO `ps_etsy_categories` VALUES (124,59,'Accessories > Sunglasses & Eyewear > Glasses','','accessories.sunglasses_and_eyewear.glasses',121,1);
INSERT INTO `ps_etsy_categories` VALUES (125,60,'Accessories > Sunglasses & Eyewear > Glasses Cases','','accessories.sunglasses_and_eyewear.glasses_cases',121,1);
INSERT INTO `ps_etsy_categories` VALUES (126,61,'Accessories > Sunglasses & Eyewear > Glasses Chains','','accessories.sunglasses_and_eyewear.glasses_chains',121,1);
INSERT INTO `ps_etsy_categories` VALUES (127,62,'Accessories > Sunglasses & Eyewear > Reading Glasses','','accessories.sunglasses_and_eyewear.reading_glasses',121,1);
INSERT INTO `ps_etsy_categories` VALUES (128,63,'Accessories > Sunglasses & Eyewear > Sports Goggles','','accessories.sunglasses_and_eyewear.sports_goggles',121,1);
INSERT INTO `ps_etsy_categories` VALUES (129,64,'Accessories > Sunglasses & Eyewear > Sunglasses','','accessories.sunglasses_and_eyewear.sunglasses',121,1);
INSERT INTO `ps_etsy_categories` VALUES (130,65,'Accessories > Umbrellas & Rain Accessories','','accessories.umbrellas_and_rain_accessories',1,0);
INSERT INTO `ps_etsy_categories` VALUES (131,1769,'Accessories > Umbrellas & Rain Accessories > Umbrellas','','accessories.umbrellas_and_rain_accessories.umbrellas',130,1);
INSERT INTO `ps_etsy_categories` VALUES (132,66,'Art & Collectibles','','art_and_collectibles',0,0);
INSERT INTO `ps_etsy_categories` VALUES (133,1865,'Art & Collectibles > Artist Trading Cards','','art_and_collectibles.artist_trading_cards',132,1);
INSERT INTO `ps_etsy_categories` VALUES (134,67,'Art & Collectibles > Collectibles','','art_and_collectibles.collectibles',132,0);
INSERT INTO `ps_etsy_categories` VALUES (135,2848,'Art & Collectibles > Collectibles > Advertisements','','art_and_collectibles.collectibles.advertisements',134,1);
INSERT INTO `ps_etsy_categories` VALUES (136,68,'Art & Collectibles > Collectibles > Coins & Money','','art_and_collectibles.collectibles.coins',134,1);
INSERT INTO `ps_etsy_categories` VALUES (137,2781,'Art & Collectibles > Collectibles > Collectible Glass','','art_and_collectibles.collectibles.collectible_glass',134,0);
INSERT INTO `ps_etsy_categories` VALUES (138,2782,'Art & Collectibles > Collectibles > Collectible Glass > Glass Insulators','','art_and_collectibles.collectibles.collectible_glass.glass_insulators',137,1);
INSERT INTO `ps_etsy_categories` VALUES (139,2850,'Art & Collectibles > Collectibles > Collectible Plates','','art_and_collectibles.collectibles.collectible_plates',134,1);
INSERT INTO `ps_etsy_categories` VALUES (140,69,'Art & Collectibles > Collectibles > Figurines & Knick Knacks','','art_and_collectibles.collectibles.figurines',134,1);
INSERT INTO `ps_etsy_categories` VALUES (141,70,'Art & Collectibles > Collectibles > Memorabilia','','art_and_collectibles.collectibles.memorabilia',134,0);
INSERT INTO `ps_etsy_categories` VALUES (142,71,'Art & Collectibles > Collectibles > Memorabilia > Militaria','','art_and_collectibles.collectibles.memorabilia.militaria',141,1);
INSERT INTO `ps_etsy_categories` VALUES (143,72,'Art & Collectibles > Collectibles > Memorabilia > Souvenirs & Events','','art_and_collectibles.collectibles.memorabilia.souvenirs_and_events',141,1);
INSERT INTO `ps_etsy_categories` VALUES (144,73,'Art & Collectibles > Collectibles > Memorabilia > Sports Collectibles','','art_and_collectibles.collectibles.memorabilia.sports_collectibles',141,0);
INSERT INTO `ps_etsy_categories` VALUES (145,1771,'Art & Collectibles > Collectibles > Memorabilia > Sports Collectibles > Collectible Jerseys','','art_and_collectibles.collectibles.memorabilia.sports_collectibles.collectible_jerseys',144,1);
INSERT INTO `ps_etsy_categories` VALUES (146,74,'Art & Collectibles > Collectibles > Memorabilia > Trophies & Awards','','art_and_collectibles.collectibles.memorabilia.trophies_and_awards',141,1);
INSERT INTO `ps_etsy_categories` VALUES (147,1886,'Art & Collectibles > Collectibles > Music Boxes','','art_and_collectibles.collectibles.music_boxes',134,1);
INSERT INTO `ps_etsy_categories` VALUES (148,2783,'Art & Collectibles > Collectibles > Paperweights','','art_and_collectibles.collectibles.paperweights',134,1);
INSERT INTO `ps_etsy_categories` VALUES (149,2847,'Art & Collectibles > Collectibles > Postage Stamps','','art_and_collectibles.collectibles.postage_stamps',134,1);
INSERT INTO `ps_etsy_categories` VALUES (150,1866,'Art & Collectibles > Collectibles > Tobacciana','','art_and_collectibles.collectibles.tobacciana',134,0);
INSERT INTO `ps_etsy_categories` VALUES (151,1868,'Art & Collectibles > Collectibles > Tobacciana > Ashtrays','','art_and_collectibles.collectibles.tobacciana.ashtrays',150,1);
INSERT INTO `ps_etsy_categories` VALUES (152,2539,'Art & Collectibles > Collectibles > Tobacciana > Cigarette Rollers','','art_and_collectibles.collectibles.tobacciana.cigarette_rollers',150,1);
INSERT INTO `ps_etsy_categories` VALUES (153,1867,'Art & Collectibles > Collectibles > Tobacciana > Lighters','','art_and_collectibles.collectibles.tobacciana.lighters',150,1);
INSERT INTO `ps_etsy_categories` VALUES (154,1647,'Art & Collectibles > Collectibles > Tobacciana > Pipes','','art_and_collectibles.collectibles.tobacciana.pipes',150,1);
INSERT INTO `ps_etsy_categories` VALUES (155,1612,'Art & Collectibles > Dolls & Miniatures','','art_and_collectibles.dolls_and_miniatures',132,0);
INSERT INTO `ps_etsy_categories` VALUES (156,2097,'Art & Collectibles > Dolls & Miniatures > Art Dolls','','art_and_collectibles.dolls_and_miniatures.art_dolls_and_figurines',155,0);
INSERT INTO `ps_etsy_categories` VALUES (157,2931,'Art & Collectibles > Dolls & Miniatures > Art Dolls > Goth & Horror Dolls','','art_and_collectibles.dolls_and_miniatures.art_dolls_and_figurines.goth_and_horror_dolls',156,1);
INSERT INTO `ps_etsy_categories` VALUES (158,2895,'Art & Collectibles > Dolls & Miniatures > Dioramas','','art_and_collectibles.dolls_and_miniatures.dioramas',155,1);
INSERT INTO `ps_etsy_categories` VALUES (159,1606,'Art & Collectibles > Dolls & Miniatures > Dollhouses','','art_and_collectibles.dolls_and_miniatures.dollhouses',155,1);
INSERT INTO `ps_etsy_categories` VALUES (160,1799,'Art & Collectibles > Dolls & Miniatures > Figurines','','art_and_collectibles.dolls_and_miniatures.figurines',155,1);
INSERT INTO `ps_etsy_categories` VALUES (161,1798,'Art & Collectibles > Dolls & Miniatures > Miniatures','','art_and_collectibles.dolls_and_miniatures.miniatures',155,0);
INSERT INTO `ps_etsy_categories` VALUES (162,1605,'Art & Collectibles > Dolls & Miniatures > Miniatures > Dollhouse Miniatures','','art_and_collectibles.dolls_and_miniatures.miniatures.dollhouse_miniatures',161,0);
INSERT INTO `ps_etsy_categories` VALUES (163,1604,'Art & Collectibles > Dolls & Miniatures > Miniatures > Dollhouse Miniatures > Doll Furniture','','art_and_collectibles.dolls_and_miniatures.miniatures.dollhouse_miniatures.doll_furniture',162,1);
INSERT INTO `ps_etsy_categories` VALUES (164,75,'Art & Collectibles > Drawing & Illustration','','art_and_collectibles.drawing_and_illustration',132,0);
INSERT INTO `ps_etsy_categories` VALUES (165,2070,'Art & Collectibles > Drawing & Illustration > Architectural Drawings','','art_and_collectibles.drawing_and_illustration.architectural_drawings',164,1);
INSERT INTO `ps_etsy_categories` VALUES (166,76,'Art & Collectibles > Drawing & Illustration > Charcoal','','art_and_collectibles.drawing_and_illustration.charcoal',164,1);
INSERT INTO `ps_etsy_categories` VALUES (167,77,'Art & Collectibles > Drawing & Illustration > Digital','','art_and_collectibles.drawing_and_illustration.digital',164,1);
INSERT INTO `ps_etsy_categories` VALUES (168,78,'Art & Collectibles > Drawing & Illustration > Marker','','art_and_collectibles.drawing_and_illustration.marker',164,1);
INSERT INTO `ps_etsy_categories` VALUES (169,79,'Art & Collectibles > Drawing & Illustration > Pastel','','art_and_collectibles.drawing_and_illustration.pastel',164,1);
INSERT INTO `ps_etsy_categories` VALUES (170,80,'Art & Collectibles > Drawing & Illustration > Pen & Ink','','art_and_collectibles.drawing_and_illustration.pen_and_ink',164,1);
INSERT INTO `ps_etsy_categories` VALUES (171,81,'Art & Collectibles > Drawing & Illustration > Pencil','','art_and_collectibles.drawing_and_illustration.pencil',164,0);
INSERT INTO `ps_etsy_categories` VALUES (172,2808,'Art & Collectibles > Drawing & Illustration > Pencil > Colored Pencil','','art_and_collectibles.drawing_and_illustration.pencil.colored_pencil',171,1);
INSERT INTO `ps_etsy_categories` VALUES (173,2809,'Art & Collectibles > Drawing & Illustration > Pencil > Graphite','','art_and_collectibles.drawing_and_illustration.pencil.graphite',171,1);
INSERT INTO `ps_etsy_categories` VALUES (174,82,'Art & Collectibles > Fiber Arts','','art_and_collectibles.fiber_arts',132,0);
INSERT INTO `ps_etsy_categories` VALUES (175,83,'Art & Collectibles > Fiber Arts > Basket Weaving','','art_and_collectibles.fiber_arts.basket_weaving',174,1);
INSERT INTO `ps_etsy_categories` VALUES (176,84,'Art & Collectibles > Fiber Arts > Batik','','art_and_collectibles.fiber_arts.batik',174,1);
INSERT INTO `ps_etsy_categories` VALUES (177,85,'Art & Collectibles > Fiber Arts > Crewel','','art_and_collectibles.fiber_arts.crewel',174,1);
INSERT INTO `ps_etsy_categories` VALUES (178,86,'Art & Collectibles > Fiber Arts > Crochet','','art_and_collectibles.fiber_arts.crochet',174,1);
INSERT INTO `ps_etsy_categories` VALUES (179,87,'Art & Collectibles > Fiber Arts > Cross Stitch','','art_and_collectibles.fiber_arts.cross_stitch',174,1);
INSERT INTO `ps_etsy_categories` VALUES (180,88,'Art & Collectibles > Fiber Arts > Embroidery','','art_and_collectibles.fiber_arts.embroidery',174,1);
INSERT INTO `ps_etsy_categories` VALUES (181,89,'Art & Collectibles > Fiber Arts > Felting','','art_and_collectibles.fiber_arts.felting',174,1);
INSERT INTO `ps_etsy_categories` VALUES (182,90,'Art & Collectibles > Fiber Arts > Knitting','','art_and_collectibles.fiber_arts.knitting',174,1);
INSERT INTO `ps_etsy_categories` VALUES (183,91,'Art & Collectibles > Fiber Arts > Macrame','','art_and_collectibles.fiber_arts.macrame',174,1);
INSERT INTO `ps_etsy_categories` VALUES (184,92,'Art & Collectibles > Fiber Arts > Needlepoint','','art_and_collectibles.fiber_arts.needlepoint',174,1);
INSERT INTO `ps_etsy_categories` VALUES (185,93,'Art & Collectibles > Fiber Arts > Quilting','','art_and_collectibles.fiber_arts.quilting',174,1);
INSERT INTO `ps_etsy_categories` VALUES (186,94,'Art & Collectibles > Fiber Arts > Rugmaking','','art_and_collectibles.fiber_arts.rugmaking',174,1);
INSERT INTO `ps_etsy_categories` VALUES (187,95,'Art & Collectibles > Fiber Arts > Sashiko','','art_and_collectibles.fiber_arts.sashiko',174,1);
INSERT INTO `ps_etsy_categories` VALUES (188,96,'Art & Collectibles > Fiber Arts > Sewing','','art_and_collectibles.fiber_arts.sewing',174,1);
INSERT INTO `ps_etsy_categories` VALUES (189,97,'Art & Collectibles > Fiber Arts > Tatting & Lace','','art_and_collectibles.fiber_arts.tatting_and_lace',174,1);
INSERT INTO `ps_etsy_categories` VALUES (190,98,'Art & Collectibles > Fiber Arts > Weaving','','art_and_collectibles.fiber_arts.weaving',174,1);
INSERT INTO `ps_etsy_categories` VALUES (191,6096,'Art & Collectibles > Fine Art Ceramics','','art_and_collectibles.fine_art_ceramics',132,1);
INSERT INTO `ps_etsy_categories` VALUES (192,104,'Art & Collectibles > Glass Art','','art_and_collectibles.glass_art',132,0);
INSERT INTO `ps_etsy_categories` VALUES (193,2889,'Art & Collectibles > Glass Art > Glass Sculptures & Figurines','','art_and_collectibles.glass_art.glass_sculptures_and_figurines',192,1);
INSERT INTO `ps_etsy_categories` VALUES (194,2885,'Art & Collectibles > Glass Art > Mosaics','','art_and_collectibles.glass_art.mosaics',192,1);
INSERT INTO `ps_etsy_categories` VALUES (195,2887,'Art & Collectibles > Glass Art > Panels & Wall Hangings','','art_and_collectibles.glass_art.panels_and_wall_hangings',192,1);
INSERT INTO `ps_etsy_categories` VALUES (196,2811,'Art & Collectibles > Glass Art > Suncatchers','','art_and_collectibles.glass_art.suncatchers',192,1);
INSERT INTO `ps_etsy_categories` VALUES (197,99,'Art & Collectibles > Mixed Media & Collage','','art_and_collectibles.mixed_media_and_collage',132,0);
INSERT INTO `ps_etsy_categories` VALUES (198,100,'Art & Collectibles > Mixed Media & Collage > Mosaic','','art_and_collectibles.mixed_media_and_collage.mosaic',197,1);
INSERT INTO `ps_etsy_categories` VALUES (199,101,'Art & Collectibles > Mixed Media & Collage > Other Assemblage','','art_and_collectibles.mixed_media_and_collage.other_assemblage',197,1);
INSERT INTO `ps_etsy_categories` VALUES (200,102,'Art & Collectibles > Mixed Media & Collage > Paint & Canvas','','art_and_collectibles.mixed_media_and_collage.paint_and_canvas',197,1);
INSERT INTO `ps_etsy_categories` VALUES (201,103,'Art & Collectibles > Mixed Media & Collage > Paper','','art_and_collectibles.mixed_media_and_collage.paper',197,1);
INSERT INTO `ps_etsy_categories` VALUES (202,105,'Art & Collectibles > Painting','','art_and_collectibles.painting',132,0);
INSERT INTO `ps_etsy_categories` VALUES (203,106,'Art & Collectibles > Painting > Acrylic','','art_and_collectibles.painting.acrylic',202,1);
INSERT INTO `ps_etsy_categories` VALUES (204,107,'Art & Collectibles > Painting > Combination','','art_and_collectibles.painting.combination',202,1);
INSERT INTO `ps_etsy_categories` VALUES (205,108,'Art & Collectibles > Painting > Encaustics','','art_and_collectibles.painting.encaustics',202,1);
INSERT INTO `ps_etsy_categories` VALUES (206,109,'Art & Collectibles > Painting > Gouache','','art_and_collectibles.painting.gouache',202,1);
INSERT INTO `ps_etsy_categories` VALUES (207,110,'Art & Collectibles > Painting > Ink','','art_and_collectibles.painting.ink',202,1);
INSERT INTO `ps_etsy_categories` VALUES (208,111,'Art & Collectibles > Painting > Mixed','','art_and_collectibles.painting.mixed',202,1);
INSERT INTO `ps_etsy_categories` VALUES (209,112,'Art & Collectibles > Painting > Oil','','art_and_collectibles.painting.oil',202,1);
INSERT INTO `ps_etsy_categories` VALUES (210,113,'Art & Collectibles > Painting > Spray Paint','','art_and_collectibles.painting.spray_paint',202,1);
INSERT INTO `ps_etsy_categories` VALUES (211,114,'Art & Collectibles > Painting > Watercolor','','art_and_collectibles.painting.watercolor',202,1);
INSERT INTO `ps_etsy_categories` VALUES (212,115,'Art & Collectibles > Photography','','art_and_collectibles.photography',132,0);
INSERT INTO `ps_etsy_categories` VALUES (213,116,'Art & Collectibles > Photography > Black & White','','art_and_collectibles.photography.black_and_white',212,1);
INSERT INTO `ps_etsy_categories` VALUES (214,117,'Art & Collectibles > Photography > Color','','art_and_collectibles.photography.color',212,1);
INSERT INTO `ps_etsy_categories` VALUES (215,118,'Art & Collectibles > Photography > Sepia','','art_and_collectibles.photography.sepia',212,1);
INSERT INTO `ps_etsy_categories` VALUES (216,119,'Art & Collectibles > Prints','','art_and_collectibles.prints',132,0);
INSERT INTO `ps_etsy_categories` VALUES (217,2078,'Art & Collectibles > Prints > Digital Prints','','art_and_collectibles.prints.digital_prints',216,1);
INSERT INTO `ps_etsy_categories` VALUES (218,120,'Art & Collectibles > Prints > Etchings & Engravings','','art_and_collectibles.prints.etchings_and_engravings',216,1);
INSERT INTO `ps_etsy_categories` VALUES (219,121,'Art & Collectibles > Prints > Giclée','','art_and_collectibles.prints.giclee',216,1);
INSERT INTO `ps_etsy_categories` VALUES (220,122,'Art & Collectibles > Prints > Letterpress Prints','','art_and_collectibles.prints.letterpress_prints',216,1);
INSERT INTO `ps_etsy_categories` VALUES (221,123,'Art & Collectibles > Prints > Lithographs','','art_and_collectibles.prints.lithographs',216,1);
INSERT INTO `ps_etsy_categories` VALUES (222,124,'Art & Collectibles > Prints > Monotypes','','art_and_collectibles.prints.monotypes',216,1);
INSERT INTO `ps_etsy_categories` VALUES (223,125,'Art & Collectibles > Prints > Music & Movie Posters','','art_and_collectibles.prints.music_and_movie_posters',216,1);
INSERT INTO `ps_etsy_categories` VALUES (224,126,'Art & Collectibles > Prints > Screenprints','','art_and_collectibles.prints.screenprints',216,1);
INSERT INTO `ps_etsy_categories` VALUES (225,127,'Art & Collectibles > Prints > Wood & Linocut Prints','','art_and_collectibles.prints.wood_and_linocut_prints',216,1);
INSERT INTO `ps_etsy_categories` VALUES (226,128,'Art & Collectibles > Sculpture','','art_and_collectibles.sculpture',132,0);
INSERT INTO `ps_etsy_categories` VALUES (227,129,'Art & Collectibles > Sculpture > Art Objects','','art_and_collectibles.sculpture.art_objects',226,1);
INSERT INTO `ps_etsy_categories` VALUES (228,130,'Art & Collectibles > Sculpture > Figurines','','art_and_collectibles.sculpture.figurines',226,1);
INSERT INTO `ps_etsy_categories` VALUES (229,131,'Art & Collectibles > Sculpture > Vessels','','art_and_collectibles.sculpture.vessels',226,1);
INSERT INTO `ps_etsy_categories` VALUES (230,132,'Bags & Purses','','bags_and_purses',0,0);
INSERT INTO `ps_etsy_categories` VALUES (231,133,'Bags & Purses > Accessory Cases','','bags_and_purses.accessory_cases',230,0);
INSERT INTO `ps_etsy_categories` VALUES (232,134,'Bags & Purses > Accessory Cases > Cigarette Cases','','bags_and_purses.accessory_cases.cigarette_cases',231,1);
INSERT INTO `ps_etsy_categories` VALUES (233,11327,'Bags & Purses > Accessory Cases > Pill Boxes','','bags_and_purses.accessory_cases.pill_boxes',231,1);
INSERT INTO `ps_etsy_categories` VALUES (234,136,'Bags & Purses > Backpacks','','bags_and_purses.backpacks',230,1);
INSERT INTO `ps_etsy_categories` VALUES (235,137,'Bags & Purses > Clothing & Shoe Bags','','bags_and_purses.clothing_and_shoe_bags',230,0);
INSERT INTO `ps_etsy_categories` VALUES (236,138,'Bags & Purses > Clothing & Shoe Bags > Hat Boxes','','bags_and_purses.clothing_and_shoe_bags.hat_boxes',235,1);
INSERT INTO `ps_etsy_categories` VALUES (237,139,'Bags & Purses > Clothing & Shoe Bags > Laundry & Lingerie Bags','','bags_and_purses.clothing_and_shoe_bags.laundry_and_lingerie_bags',235,1);
INSERT INTO `ps_etsy_categories` VALUES (238,140,'Bags & Purses > Clothing & Shoe Bags > Shoe Bags','','bags_and_purses.clothing_and_shoe_bags.shoe_bags',235,1);
INSERT INTO `ps_etsy_categories` VALUES (239,141,'Bags & Purses > Cosmetic & Toiletry Storage','','bags_and_purses.cosmetic_and_toiletry_storage',230,0);
INSERT INTO `ps_etsy_categories` VALUES (240,142,'Bags & Purses > Cosmetic & Toiletry Storage > Brush Rolls & Holders','','bags_and_purses.cosmetic_and_toiletry_storage.brush_rolls_and_holders',239,0);
INSERT INTO `ps_etsy_categories` VALUES (241,1777,'Bags & Purses > Cosmetic & Toiletry Storage > Brush Rolls & Holders > Brush Rolls','','bags_and_purses.cosmetic_and_toiletry_storage.brush_rolls_and_holders.brush_rolls',240,1);
INSERT INTO `ps_etsy_categories` VALUES (242,1778,'Bags & Purses > Cosmetic & Toiletry Storage > Brush Rolls & Holders > Brush Storage','','bags_and_purses.cosmetic_and_toiletry_storage.brush_rolls_and_holders.brush_storage',240,1);
INSERT INTO `ps_etsy_categories` VALUES (243,143,'Bags & Purses > Cosmetic & Toiletry Storage > Cosmetic Bags','','bags_and_purses.cosmetic_and_toiletry_storage.cosmetic_bags',239,1);
INSERT INTO `ps_etsy_categories` VALUES (244,145,'Bags & Purses > Cosmetic & Toiletry Storage > Makeup Organizers','','bags_and_purses.cosmetic_and_toiletry_storage.makeup_organizers',239,1);
INSERT INTO `ps_etsy_categories` VALUES (245,146,'Bags & Purses > Cosmetic & Toiletry Storage > Nail Polish Organizers','','bags_and_purses.cosmetic_and_toiletry_storage.nail_polish_organizers',239,1);
INSERT INTO `ps_etsy_categories` VALUES (246,147,'Bags & Purses > Cosmetic & Toiletry Storage > Refillable Containers','','bags_and_purses.cosmetic_and_toiletry_storage.refillable_containers',239,1);
INSERT INTO `ps_etsy_categories` VALUES (247,148,'Bags & Purses > Cosmetic & Toiletry Storage > Toiletry Kits & Travel Cases','','bags_and_purses.cosmetic_and_toiletry_storage.toiletry_kits_and_travel_cases',239,1);
INSERT INTO `ps_etsy_categories` VALUES (248,144,'Bags & Purses > Cosmetic & Toiletry Storage > Vanity Storage','','bags_and_purses.cosmetic_and_toiletry_storage.vanity_storage',239,0);
INSERT INTO `ps_etsy_categories` VALUES (249,2160,'Bags & Purses > Cosmetic & Toiletry Storage > Vanity Storage > Atomizers & Perfume Bottles','','bags_and_purses.cosmetic_and_toiletry_storage.vanity_storage.atomizers_and_perfume_bottles',248,1);
INSERT INTO `ps_etsy_categories` VALUES (250,1780,'Bags & Purses > Cosmetic & Toiletry Storage > Vanity Storage > Compacts','','bags_and_purses.cosmetic_and_toiletry_storage.vanity_storage.compacts',248,1);
INSERT INTO `ps_etsy_categories` VALUES (251,1779,'Bags & Purses > Cosmetic & Toiletry Storage > Vanity Storage > Lipstick Cases','','bags_and_purses.cosmetic_and_toiletry_storage.vanity_storage.lipstick_cases',248,1);
INSERT INTO `ps_etsy_categories` VALUES (252,2159,'Bags & Purses > Cosmetic & Toiletry Storage > Vanity Storage > Powder Boxes','','bags_and_purses.cosmetic_and_toiletry_storage.vanity_storage.powder_boxes',248,1);
INSERT INTO `ps_etsy_categories` VALUES (253,149,'Bags & Purses > Diaper Bags','','bags_and_purses.diaper_bags',230,1);
INSERT INTO `ps_etsy_categories` VALUES (254,150,'Bags & Purses > Fanny Packs','','bags_and_purses.fanny_packs',230,1);
INSERT INTO `ps_etsy_categories` VALUES (255,151,'Bags & Purses > Food & Insulated Bags','','bags_and_purses.food_and_insulated_bags',230,0);
INSERT INTO `ps_etsy_categories` VALUES (256,152,'Bags & Purses > Food & Insulated Bags > Drink Holders & Cozies','','bags_and_purses.food_and_insulated_bags.drink_holders_and_cozies',255,1);
INSERT INTO `ps_etsy_categories` VALUES (257,153,'Bags & Purses > Food & Insulated Bags > Lunch Bags & Boxes','','bags_and_purses.food_and_insulated_bags.lunch_bags_and_boxes',255,1);
INSERT INTO `ps_etsy_categories` VALUES (258,154,'Bags & Purses > Food & Insulated Bags > Picnic Baskets & Bags','','bags_and_purses.food_and_insulated_bags.picnic_baskets_and_bags',255,1);
INSERT INTO `ps_etsy_categories` VALUES (259,155,'Bags & Purses > Food & Insulated Bags > Snack & Sandwich Bags','','bags_and_purses.food_and_insulated_bags.snack_and_sandwich_bags',255,1);
INSERT INTO `ps_etsy_categories` VALUES (260,156,'Bags & Purses > Handbags','','bags_and_purses.handbags',230,0);
INSERT INTO `ps_etsy_categories` VALUES (261,157,'Bags & Purses > Handbags > Clutches & Evening Bags','','bags_and_purses.handbags.clutches_and_evening_bags',260,1);
INSERT INTO `ps_etsy_categories` VALUES (262,158,'Bags & Purses > Handbags > Crossbody Bags','','bags_and_purses.handbags.crossbody_bags',260,1);
INSERT INTO `ps_etsy_categories` VALUES (263,159,'Bags & Purses > Handbags > Hobo Bags','','bags_and_purses.handbags.hobo_bags',260,1);
INSERT INTO `ps_etsy_categories` VALUES (264,160,'Bags & Purses > Handbags > Purse Inserts','','bags_and_purses.handbags.purse_inserts',260,1);
INSERT INTO `ps_etsy_categories` VALUES (265,11296,'Bags & Purses > Handbags > Purse Straps','','bags_and_purses.handbags.purse_straps',260,1);
INSERT INTO `ps_etsy_categories` VALUES (266,161,'Bags & Purses > Handbags > Shoulder Bags','','bags_and_purses.handbags.shoulder_bags',260,1);
INSERT INTO `ps_etsy_categories` VALUES (267,162,'Bags & Purses > Handbags > Top Handle Bags','','bags_and_purses.handbags.top_handle_bags',260,1);
INSERT INTO `ps_etsy_categories` VALUES (268,163,'Bags & Purses > Handbags > Wristlets','','bags_and_purses.handbags.wristlets',260,1);
INSERT INTO `ps_etsy_categories` VALUES (269,168,'Bags & Purses > Luggage & Travel','','bags_and_purses.luggage_and_travel',230,0);
INSERT INTO `ps_etsy_categories` VALUES (270,169,'Bags & Purses > Luggage & Travel > Briefcases & Attaches','','bags_and_purses.luggage_and_travel.briefcases_and_attaches',269,0);
INSERT INTO `ps_etsy_categories` VALUES (271,2084,'Bags & Purses > Luggage & Travel > Briefcases & Attaches > Briefcases','','bags_and_purses.luggage_and_travel.briefcases_and_attaches.briefcases',270,1);
INSERT INTO `ps_etsy_categories` VALUES (272,170,'Bags & Purses > Luggage & Travel > Duffel Bags','','bags_and_purses.luggage_and_travel.duffel_bags',269,1);
INSERT INTO `ps_etsy_categories` VALUES (273,171,'Bags & Purses > Luggage & Travel > Garment Bags','','bags_and_purses.luggage_and_travel.garment_bags',269,1);
INSERT INTO `ps_etsy_categories` VALUES (274,12069,'Bags & Purses > Luggage & Travel > Luggage Covers','','bags_and_purses.luggage_and_travel.luggage_covers',269,1);
INSERT INTO `ps_etsy_categories` VALUES (275,172,'Bags & Purses > Luggage & Travel > Luggage Straps','','bags_and_purses.luggage_and_travel.luggage_straps',269,1);
INSERT INTO `ps_etsy_categories` VALUES (276,173,'Bags & Purses > Luggage & Travel > Luggage Tags','','bags_and_purses.luggage_and_travel.luggage_tags',269,1);
INSERT INTO `ps_etsy_categories` VALUES (277,174,'Bags & Purses > Luggage & Travel > Overnight Bags','','bags_and_purses.luggage_and_travel.overnight_bags',269,1);
INSERT INTO `ps_etsy_categories` VALUES (278,175,'Bags & Purses > Luggage & Travel > Passport Covers','','bags_and_purses.luggage_and_travel.passport_covers',269,1);
INSERT INTO `ps_etsy_categories` VALUES (279,176,'Bags & Purses > Luggage & Travel > Rolling Luggage','','bags_and_purses.luggage_and_travel.rolling_luggage',269,1);
INSERT INTO `ps_etsy_categories` VALUES (280,177,'Bags & Purses > Luggage & Travel > Suitcases','','bags_and_purses.luggage_and_travel.suitcases',269,1);
INSERT INTO `ps_etsy_categories` VALUES (281,178,'Bags & Purses > Luggage & Travel > Train Cases','','bags_and_purses.luggage_and_travel.train_cases',269,1);
INSERT INTO `ps_etsy_categories` VALUES (282,179,'Bags & Purses > Luggage & Travel > Travel Wallets','','bags_and_purses.luggage_and_travel.travel_wallets',269,1);
INSERT INTO `ps_etsy_categories` VALUES (283,180,'Bags & Purses > Market Bags','','bags_and_purses.market_bags',230,1);
INSERT INTO `ps_etsy_categories` VALUES (284,181,'Bags & Purses > Messenger Bags','','bags_and_purses.messenger_bags',230,1);
INSERT INTO `ps_etsy_categories` VALUES (285,182,'Bags & Purses > Pouches & Coin Purses','','bags_and_purses.pouches_and_coin_purses',230,1);
INSERT INTO `ps_etsy_categories` VALUES (286,183,'Bags & Purses > Sports Bags','','bags_and_purses.sports_bags',230,0);
INSERT INTO `ps_etsy_categories` VALUES (287,184,'Bags & Purses > Sports Bags > Arm Bands & Wrist Wallets','','bags_and_purses.sports_bags.arm_bands_and_wrist_wallets',286,1);
INSERT INTO `ps_etsy_categories` VALUES (288,185,'Bags & Purses > Sports Bags > Bowling Ball Bags','','bags_and_purses.sports_bags.bowling_ball_bags',286,1);
INSERT INTO `ps_etsy_categories` VALUES (289,186,'Bags & Purses > Sports Bags > Cycling Bags','','bags_and_purses.sports_bags.cycling_bags',286,1);
INSERT INTO `ps_etsy_categories` VALUES (290,187,'Bags & Purses > Sports Bags > Team Sports & Gym Bags','','bags_and_purses.sports_bags.team_sports_and_gym_bags',286,1);
INSERT INTO `ps_etsy_categories` VALUES (291,188,'Bags & Purses > Sports Bags > Tennis & Golf Bags','','bags_and_purses.sports_bags.tennis_and_golf_bags',286,0);
INSERT INTO `ps_etsy_categories` VALUES (292,2086,'Bags & Purses > Sports Bags > Tennis & Golf Bags > Golf Bags','','bags_and_purses.sports_bags.tennis_and_golf_bags.golf_bags',291,1);
INSERT INTO `ps_etsy_categories` VALUES (293,2085,'Bags & Purses > Sports Bags > Tennis & Golf Bags > Tennis Bags','','bags_and_purses.sports_bags.tennis_and_golf_bags.tennis_bags',291,1);
INSERT INTO `ps_etsy_categories` VALUES (294,1699,'Bags & Purses > Sports Bags > Wet & Beach Bags','','bags_and_purses.sports_bags.wet_and_beach_bags',286,0);
INSERT INTO `ps_etsy_categories` VALUES (295,2059,'Bags & Purses > Sports Bags > Wet & Beach Bags > Wet Bags','','bags_and_purses.sports_bags.wet_and_beach_bags.wet_bags',294,1);
INSERT INTO `ps_etsy_categories` VALUES (296,189,'Bags & Purses > Sports Bags > Yoga Bags','','bags_and_purses.sports_bags.yoga_bags',286,1);
INSERT INTO `ps_etsy_categories` VALUES (297,190,'Bags & Purses > Totes','','bags_and_purses.totes',230,1);
INSERT INTO `ps_etsy_categories` VALUES (298,191,'Bags & Purses > Wallets & Money Clips','','bags_and_purses.wallets_and_money_clips',230,0);
INSERT INTO `ps_etsy_categories` VALUES (299,192,'Bags & Purses > Wallets & Money Clips > Business Card Cases','','bags_and_purses.wallets_and_money_clips.business_card_cases',298,1);
INSERT INTO `ps_etsy_categories` VALUES (300,193,'Bags & Purses > Wallets & Money Clips > Chain Wallets','','bags_and_purses.wallets_and_money_clips.chain_wallets',298,1);
INSERT INTO `ps_etsy_categories` VALUES (301,194,'Bags & Purses > Wallets & Money Clips > Checkbook Covers','','bags_and_purses.wallets_and_money_clips.checkbook_covers',298,1);
INSERT INTO `ps_etsy_categories` VALUES (302,195,'Bags & Purses > Wallets & Money Clips > Coupon Organizer','','bags_and_purses.wallets_and_money_clips.coupon_organizer',298,1);
INSERT INTO `ps_etsy_categories` VALUES (303,196,'Bags & Purses > Wallets & Money Clips > Money Clips','','bags_and_purses.wallets_and_money_clips.money_clips',298,1);
INSERT INTO `ps_etsy_categories` VALUES (304,197,'Bags & Purses > Wallets & Money Clips > Wallets','','bags_and_purses.wallets_and_money_clips.wallets',298,1);
INSERT INTO `ps_etsy_categories` VALUES (305,199,'Bath & Beauty','','bath_and_beauty',0,0);
INSERT INTO `ps_etsy_categories` VALUES (306,200,'Bath & Beauty > Baby & Child Care','','bath_and_beauty.baby_and_child_care',305,0);
INSERT INTO `ps_etsy_categories` VALUES (307,201,'Bath & Beauty > Baby & Child Care > Bibs & Burping','','bath_and_beauty.baby_and_child_care.bibs_and_burping',306,0);
INSERT INTO `ps_etsy_categories` VALUES (308,2087,'Bath & Beauty > Baby & Child Care > Bibs & Burping > Bibs','','bath_and_beauty.baby_and_child_care.bibs_and_burping.bibs',307,1);
INSERT INTO `ps_etsy_categories` VALUES (309,2088,'Bath & Beauty > Baby & Child Care > Bibs & Burping > Burping','','bath_and_beauty.baby_and_child_care.bibs_and_burping.burping',307,1);
INSERT INTO `ps_etsy_categories` VALUES (310,202,'Bath & Beauty > Baby & Child Care > Changing Pads','','bath_and_beauty.baby_and_child_care.changing_pads',306,1);
INSERT INTO `ps_etsy_categories` VALUES (311,203,'Bath & Beauty > Baby & Child Care > Diapers','','bath_and_beauty.baby_and_child_care.diapers',306,1);
INSERT INTO `ps_etsy_categories` VALUES (312,204,'Bath & Beauty > Baby & Child Care > Feeding','','bath_and_beauty.baby_and_child_care.feeding',306,1);
INSERT INTO `ps_etsy_categories` VALUES (313,205,'Bath & Beauty > Baby & Child Care > Nursing','','bath_and_beauty.baby_and_child_care.nursing',306,1);
INSERT INTO `ps_etsy_categories` VALUES (314,206,'Bath & Beauty > Baby & Child Care > Pacifiers & Clips','','bath_and_beauty.baby_and_child_care.pacifiers_and_clips',306,1);
INSERT INTO `ps_etsy_categories` VALUES (315,207,'Bath & Beauty > Baby & Child Care > Sets','','bath_and_beauty.baby_and_child_care.sets',306,1);
INSERT INTO `ps_etsy_categories` VALUES (316,208,'Bath & Beauty > Baby & Child Care > Teething','','bath_and_beauty.baby_and_child_care.teething',306,1);
INSERT INTO `ps_etsy_categories` VALUES (317,210,'Bath & Beauty > Bath Accessories','','bath_and_beauty.bath_accessories',305,0);
INSERT INTO `ps_etsy_categories` VALUES (318,211,'Bath & Beauty > Bath Accessories > Loofahs','','bath_and_beauty.bath_accessories.loofahs',317,1);
INSERT INTO `ps_etsy_categories` VALUES (319,212,'Bath & Beauty > Bath Accessories > Poufs','','bath_and_beauty.bath_accessories.poufs',317,1);
INSERT INTO `ps_etsy_categories` VALUES (320,213,'Bath & Beauty > Bath Accessories > Sponges & Body Brushes','','bath_and_beauty.bath_accessories.sponges_and_body_brushes',317,1);
INSERT INTO `ps_etsy_categories` VALUES (321,214,'Bath & Beauty > Bath Accessories > Washcloths','','bath_and_beauty.bath_accessories.washcloths',317,1);
INSERT INTO `ps_etsy_categories` VALUES (322,215,'Bath & Beauty > Essential Oils','','bath_and_beauty.essential_oils',305,1);
INSERT INTO `ps_etsy_categories` VALUES (323,216,'Bath & Beauty > Fragrances','','bath_and_beauty.fragrances',305,1);
INSERT INTO `ps_etsy_categories` VALUES (324,217,'Bath & Beauty > Hair Care','','bath_and_beauty.hair_care',305,0);
INSERT INTO `ps_etsy_categories` VALUES (325,218,'Bath & Beauty > Hair Care > Conditioners & Treatments','','bath_and_beauty.hair_care.conditioners_and_treatments',324,1);
INSERT INTO `ps_etsy_categories` VALUES (326,228,'Bath & Beauty > Hair Care > Hair Dye & Color','','bath_and_beauty.hair_care.hair_dye_and_color',324,0);
INSERT INTO `ps_etsy_categories` VALUES (327,229,'Bath & Beauty > Hair Care > Hair Dye & Color > Hair Chalks','','bath_and_beauty.hair_care.hair_dye_and_color.hair_chalks',326,1);
INSERT INTO `ps_etsy_categories` VALUES (328,230,'Bath & Beauty > Hair Care > Hair Dye & Color > Hennas','','bath_and_beauty.hair_care.hair_dye_and_color.hennas',326,1);
INSERT INTO `ps_etsy_categories` VALUES (329,231,'Bath & Beauty > Hair Care > Hair Extensions','','bath_and_beauty.hair_care.hair_extensions',324,0);
INSERT INTO `ps_etsy_categories` VALUES (330,2810,'Bath & Beauty > Hair Care > Hair Extensions > Feather Hair Extensions','','bath_and_beauty.hair_care.hair_extensions.feather_hair_extensions',329,1);
INSERT INTO `ps_etsy_categories` VALUES (331,232,'Bath & Beauty > Hair Care > Hair Styling','','bath_and_beauty.hair_care.hair_styling',324,0);
INSERT INTO `ps_etsy_categories` VALUES (332,233,'Bath & Beauty > Hair Care > Hair Styling > Brushes & Combs','','bath_and_beauty.hair_care.hair_styling.brushes_and_combs',331,1);
INSERT INTO `ps_etsy_categories` VALUES (333,234,'Bath & Beauty > Hair Care > Hair Styling > Curling & Curlers','','bath_and_beauty.hair_care.hair_styling.curling_and_curlers',331,1);
INSERT INTO `ps_etsy_categories` VALUES (334,235,'Bath & Beauty > Hair Care > Hair Styling > Hair Sprays','','bath_and_beauty.hair_care.hair_styling.hair_sprays',331,1);
INSERT INTO `ps_etsy_categories` VALUES (335,236,'Bath & Beauty > Hair Care > Hair Styling > Pomades, Waxes & Gels','','bath_and_beauty.hair_care.hair_styling.pomades_waxes_and_gels',331,1);
INSERT INTO `ps_etsy_categories` VALUES (336,237,'Bath & Beauty > Hair Care > Hair Styling > Straighteners','','bath_and_beauty.hair_care.hair_styling.straighteners',331,1);
INSERT INTO `ps_etsy_categories` VALUES (337,238,'Bath & Beauty > Hair Care > Shampoos','','bath_and_beauty.hair_care.shampoos',324,1);
INSERT INTO `ps_etsy_categories` VALUES (338,239,'Bath & Beauty > Hair Care > Shower Caps','','bath_and_beauty.hair_care.shower_caps',324,1);
INSERT INTO `ps_etsy_categories` VALUES (339,240,'Bath & Beauty > Hair Care > Wigs','','bath_and_beauty.hair_care.wigs',324,1);
INSERT INTO `ps_etsy_categories` VALUES (340,241,'Bath & Beauty > Makeup & Cosmetics','','bath_and_beauty.makeup_and_cosmetics',305,0);
INSERT INTO `ps_etsy_categories` VALUES (341,242,'Bath & Beauty > Makeup & Cosmetics > Eyes','','bath_and_beauty.makeup_and_cosmetics.eyes',340,0);
INSERT INTO `ps_etsy_categories` VALUES (342,246,'Bath & Beauty > Makeup & Cosmetics > Eyes > Eye Primers & Correctors','','bath_and_beauty.makeup_and_cosmetics.eyes.eye_primers_and_correctors',341,1);
INSERT INTO `ps_etsy_categories` VALUES (343,247,'Bath & Beauty > Makeup & Cosmetics > Eyes > Eye Shadows','','bath_and_beauty.makeup_and_cosmetics.eyes.eye_shadows',341,1);
INSERT INTO `ps_etsy_categories` VALUES (344,243,'Bath & Beauty > Makeup & Cosmetics > Eyes > Eyebrow Makeup','','bath_and_beauty.makeup_and_cosmetics.eyes.eyebrow_makeup',341,1);
INSERT INTO `ps_etsy_categories` VALUES (345,244,'Bath & Beauty > Makeup & Cosmetics > Eyes > Eyelashes & Mascara','','bath_and_beauty.makeup_and_cosmetics.eyes.eyelashes_and_mascara',341,0);
INSERT INTO `ps_etsy_categories` VALUES (346,2089,'Bath & Beauty > Makeup & Cosmetics > Eyes > Eyelashes & Mascara > Eyelashes','','bath_and_beauty.makeup_and_cosmetics.eyes.eyelashes_and_mascara.eyelashes',345,1);
INSERT INTO `ps_etsy_categories` VALUES (347,2090,'Bath & Beauty > Makeup & Cosmetics > Eyes > Eyelashes & Mascara > Mascara','','bath_and_beauty.makeup_and_cosmetics.eyes.eyelashes_and_mascara.mascara',345,1);
INSERT INTO `ps_etsy_categories` VALUES (348,245,'Bath & Beauty > Makeup & Cosmetics > Eyes > Eyeliners','','bath_and_beauty.makeup_and_cosmetics.eyes.eyeliners',341,1);
INSERT INTO `ps_etsy_categories` VALUES (349,248,'Bath & Beauty > Makeup & Cosmetics > Face','','bath_and_beauty.makeup_and_cosmetics.face',340,0);
INSERT INTO `ps_etsy_categories` VALUES (350,249,'Bath & Beauty > Makeup & Cosmetics > Face > Blush & Cheek Stain','','bath_and_beauty.makeup_and_cosmetics.face.blush_and_cheek_stain',349,1);
INSERT INTO `ps_etsy_categories` VALUES (351,250,'Bath & Beauty > Makeup & Cosmetics > Face > Bronzer','','bath_and_beauty.makeup_and_cosmetics.face.bronzer',349,1);
INSERT INTO `ps_etsy_categories` VALUES (352,251,'Bath & Beauty > Makeup & Cosmetics > Face > Concealers','','bath_and_beauty.makeup_and_cosmetics.face.concealers',349,1);
INSERT INTO `ps_etsy_categories` VALUES (353,6100,'Bath & Beauty > Makeup & Cosmetics > Face > Face & Body Paint','','bath_and_beauty.makeup_and_cosmetics.face.face_and_body_paint',349,1);
INSERT INTO `ps_etsy_categories` VALUES (354,252,'Bath & Beauty > Makeup & Cosmetics > Face > Face Primers & Correctors','','bath_and_beauty.makeup_and_cosmetics.face.face_primers_and_correctors',349,1);
INSERT INTO `ps_etsy_categories` VALUES (355,253,'Bath & Beauty > Makeup & Cosmetics > Face > Foundations','','bath_and_beauty.makeup_and_cosmetics.face.foundations',349,1);
INSERT INTO `ps_etsy_categories` VALUES (356,11297,'Bath & Beauty > Makeup & Cosmetics > Face > Highlighters & Luminizers','','bath_and_beauty.makeup_and_cosmetics.face.highlighters_and_luminizers',349,1);
INSERT INTO `ps_etsy_categories` VALUES (357,254,'Bath & Beauty > Makeup & Cosmetics > Face > Powders','','bath_and_beauty.makeup_and_cosmetics.face.powders',349,1);
INSERT INTO `ps_etsy_categories` VALUES (358,255,'Bath & Beauty > Makeup & Cosmetics > Lips','','bath_and_beauty.makeup_and_cosmetics.lips',340,0);
INSERT INTO `ps_etsy_categories` VALUES (359,256,'Bath & Beauty > Makeup & Cosmetics > Lips > Lip Balms & Glosses','','bath_and_beauty.makeup_and_cosmetics.lips.lip_balms_and_glosses',358,0);
INSERT INTO `ps_etsy_categories` VALUES (360,2091,'Bath & Beauty > Makeup & Cosmetics > Lips > Lip Balms & Glosses > Lip Balms','','bath_and_beauty.makeup_and_cosmetics.lips.lip_balms_and_glosses.lip_balms',359,1);
INSERT INTO `ps_etsy_categories` VALUES (361,2092,'Bath & Beauty > Makeup & Cosmetics > Lips > Lip Balms & Glosses > Lip Glosses','','bath_and_beauty.makeup_and_cosmetics.lips.lip_balms_and_glosses.lip_glosses',359,1);
INSERT INTO `ps_etsy_categories` VALUES (362,257,'Bath & Beauty > Makeup & Cosmetics > Lips > Lip Color','','bath_and_beauty.makeup_and_cosmetics.lips.lip_color',358,1);
INSERT INTO `ps_etsy_categories` VALUES (363,258,'Bath & Beauty > Makeup & Cosmetics > Lips > Lip Scrubs','','bath_and_beauty.makeup_and_cosmetics.lips.lip_scrubs',358,1);
INSERT INTO `ps_etsy_categories` VALUES (364,259,'Bath & Beauty > Makeup & Cosmetics > Makeup Tools & Brushes','','bath_and_beauty.makeup_and_cosmetics.makeup_tools_and_brushes',340,0);
INSERT INTO `ps_etsy_categories` VALUES (365,260,'Bath & Beauty > Makeup & Cosmetics > Makeup Tools & Brushes > Hand & Pocket Mirrors','','bath_and_beauty.makeup_and_cosmetics.makeup_tools_and_brushes.hand_and_pocket_mirrors',364,1);
INSERT INTO `ps_etsy_categories` VALUES (366,262,'Bath & Beauty > Makeup & Cosmetics > Makeup Tools & Brushes > Makeup Remover','','bath_and_beauty.makeup_and_cosmetics.makeup_tools_and_brushes.makeup_remover',364,1);
INSERT INTO `ps_etsy_categories` VALUES (367,270,'Bath & Beauty > Personal Care','','bath_and_beauty.personal_care',305,0);
INSERT INTO `ps_etsy_categories` VALUES (368,271,'Bath & Beauty > Personal Care > Bug Repellent','','bath_and_beauty.personal_care.bug_repellent',367,1);
INSERT INTO `ps_etsy_categories` VALUES (369,272,'Bath & Beauty > Personal Care > Canes & Walking','','bath_and_beauty.personal_care.canes_and_walking',367,1);
INSERT INTO `ps_etsy_categories` VALUES (370,273,'Bath & Beauty > Personal Care > Deodorant','','bath_and_beauty.personal_care.deodorant',367,1);
INSERT INTO `ps_etsy_categories` VALUES (371,274,'Bath & Beauty > Personal Care > Menstrual Care','','bath_and_beauty.personal_care.menstrual_care',367,0);
INSERT INTO `ps_etsy_categories` VALUES (372,11226,'Bath & Beauty > Personal Care > Menstrual Care > Pads & Pantyliners','','bath_and_beauty.personal_care.menstrual_care.pads_and_pantyliners',371,1);
INSERT INTO `ps_etsy_categories` VALUES (373,11227,'Bath & Beauty > Personal Care > Menstrual Care > Tampons','','bath_and_beauty.personal_care.menstrual_care.tampons',371,1);
INSERT INTO `ps_etsy_categories` VALUES (374,275,'Bath & Beauty > Personal Care > Oral Care','','bath_and_beauty.personal_care.oral_care',367,1);
INSERT INTO `ps_etsy_categories` VALUES (375,276,'Bath & Beauty > Personal Care > Sexual Wellness','','bath_and_beauty.personal_care.sexual_wellness',367,0);
INSERT INTO `ps_etsy_categories` VALUES (376,277,'Bath & Beauty > Personal Care > Sexual Wellness > Kegel Exercisers','','bath_and_beauty.personal_care.sexual_wellness.kegel_exercisers',375,1);
INSERT INTO `ps_etsy_categories` VALUES (377,279,'Bath & Beauty > Personal Care > Sexual Wellness > Restraints & Gags','','bath_and_beauty.personal_care.sexual_wellness.restraints_and_gags',375,0);
INSERT INTO `ps_etsy_categories` VALUES (378,1816,'Bath & Beauty > Personal Care > Sexual Wellness > Restraints & Gags > Gags','','bath_and_beauty.personal_care.sexual_wellness.restraints_and_gags.gags',377,1);
INSERT INTO `ps_etsy_categories` VALUES (379,1815,'Bath & Beauty > Personal Care > Sexual Wellness > Restraints & Gags > Restraints','','bath_and_beauty.personal_care.sexual_wellness.restraints_and_gags.restraints',377,1);
INSERT INTO `ps_etsy_categories` VALUES (380,280,'Bath & Beauty > Personal Care > Sexual Wellness > Sex Toys','','bath_and_beauty.personal_care.sexual_wellness.sex_toys',375,0);
INSERT INTO `ps_etsy_categories` VALUES (381,2339,'Bath & Beauty > Personal Care > Sexual Wellness > Sex Toys > Butt Plugs','','bath_and_beauty.personal_care.sexual_wellness.sex_toys.butt_plugs',380,1);
INSERT INTO `ps_etsy_categories` VALUES (382,2337,'Bath & Beauty > Personal Care > Sexual Wellness > Sex Toys > Dildos','','bath_and_beauty.personal_care.sexual_wellness.sex_toys.dildos',380,1);
INSERT INTO `ps_etsy_categories` VALUES (383,2338,'Bath & Beauty > Personal Care > Sexual Wellness > Sex Toys > Vibrators','','bath_and_beauty.personal_care.sexual_wellness.sex_toys.vibrators',380,1);
INSERT INTO `ps_etsy_categories` VALUES (384,281,'Bath & Beauty > Personal Care > Sexual Wellness > Spanking & Flogging','','bath_and_beauty.personal_care.sexual_wellness.spanking_and_flogging',375,0);
INSERT INTO `ps_etsy_categories` VALUES (385,2336,'Bath & Beauty > Personal Care > Sexual Wellness > Spanking & Flogging > Crops','','bath_and_beauty.personal_care.sexual_wellness.spanking_and_flogging.crops',384,1);
INSERT INTO `ps_etsy_categories` VALUES (386,2334,'Bath & Beauty > Personal Care > Sexual Wellness > Spanking & Flogging > Floggers','','bath_and_beauty.personal_care.sexual_wellness.spanking_and_flogging.floggers',384,1);
INSERT INTO `ps_etsy_categories` VALUES (387,2333,'Bath & Beauty > Personal Care > Sexual Wellness > Spanking & Flogging > Paddles','','bath_and_beauty.personal_care.sexual_wellness.spanking_and_flogging.paddles',384,1);
INSERT INTO `ps_etsy_categories` VALUES (388,2335,'Bath & Beauty > Personal Care > Sexual Wellness > Spanking & Flogging > Whips','','bath_and_beauty.personal_care.sexual_wellness.spanking_and_flogging.whips',384,1);
INSERT INTO `ps_etsy_categories` VALUES (389,282,'Bath & Beauty > Personal Care > Shaving & Grooming','','bath_and_beauty.personal_care.shaving_and_grooming',367,0);
INSERT INTO `ps_etsy_categories` VALUES (390,283,'Bath & Beauty > Personal Care > Shaving & Grooming > Aftershave','','bath_and_beauty.personal_care.shaving_and_grooming.aftershave',389,1);
INSERT INTO `ps_etsy_categories` VALUES (391,284,'Bath & Beauty > Personal Care > Shaving & Grooming > Beard & Mustache','','bath_and_beauty.personal_care.shaving_and_grooming.beard_and_mustache',389,0);
INSERT INTO `ps_etsy_categories` VALUES (392,2003,'Bath & Beauty > Personal Care > Shaving & Grooming > Beard & Mustache > Balms & Conditioner','','bath_and_beauty.personal_care.shaving_and_grooming.beard_and_mustache.balms_and_conditioner',391,1);
INSERT INTO `ps_etsy_categories` VALUES (393,2001,'Bath & Beauty > Personal Care > Shaving & Grooming > Beard & Mustache > Combs','','bath_and_beauty.personal_care.shaving_and_grooming.beard_and_mustache.combs',391,1);
INSERT INTO `ps_etsy_categories` VALUES (394,2002,'Bath & Beauty > Personal Care > Shaving & Grooming > Beard & Mustache > Waxes & Pomades','','bath_and_beauty.personal_care.shaving_and_grooming.beard_and_mustache.waxes_and_pomades',391,1);
INSERT INTO `ps_etsy_categories` VALUES (395,285,'Bath & Beauty > Personal Care > Shaving & Grooming > Razors','','bath_and_beauty.personal_care.shaving_and_grooming.razors',389,1);
INSERT INTO `ps_etsy_categories` VALUES (396,286,'Bath & Beauty > Personal Care > Shaving & Grooming > Shaving Brushes & Cups','','bath_and_beauty.personal_care.shaving_and_grooming.shaving_brushes_and_cups',389,1);
INSERT INTO `ps_etsy_categories` VALUES (397,287,'Bath & Beauty > Personal Care > Shaving & Grooming > Shaving Kits & Sets','','bath_and_beauty.personal_care.shaving_and_grooming.shaving_kits_and_sets',389,1);
INSERT INTO `ps_etsy_categories` VALUES (398,288,'Bath & Beauty > Personal Care > Shaving & Grooming > Shaving Lubricants','','bath_and_beauty.personal_care.shaving_and_grooming.shaving_lubricants',389,1);
INSERT INTO `ps_etsy_categories` VALUES (399,289,'Bath & Beauty > Personal Care > Shaving & Grooming > Waxing & Sugaring','','bath_and_beauty.personal_care.shaving_and_grooming.waxing_and_sugaring',389,0);
INSERT INTO `ps_etsy_categories` VALUES (400,1818,'Bath & Beauty > Personal Care > Shaving & Grooming > Waxing & Sugaring > Sugaring','','bath_and_beauty.personal_care.shaving_and_grooming.waxing_and_sugaring.sugaring',399,1);
INSERT INTO `ps_etsy_categories` VALUES (401,1817,'Bath & Beauty > Personal Care > Shaving & Grooming > Waxing & Sugaring > Waxing','','bath_and_beauty.personal_care.shaving_and_grooming.waxing_and_sugaring.waxing',399,1);
INSERT INTO `ps_etsy_categories` VALUES (402,290,'Bath & Beauty > Personal Care > Supplements','','bath_and_beauty.personal_care.supplements',367,1);
INSERT INTO `ps_etsy_categories` VALUES (403,291,'Bath & Beauty > Personal Care > Tinctures','','bath_and_beauty.personal_care.tinctures',367,1);
INSERT INTO `ps_etsy_categories` VALUES (404,292,'Bath & Beauty > Skin Care','','bath_and_beauty.skin_care',305,0);
INSERT INTO `ps_etsy_categories` VALUES (405,294,'Bath & Beauty > Skin Care > Bleaching & Fade Creams','','bath_and_beauty.skin_care.bleaching_and_fade_creams',404,1);
INSERT INTO `ps_etsy_categories` VALUES (406,296,'Bath & Beauty > Skin Care > Exfoliation & Peels','','bath_and_beauty.skin_care.exfoliation_and_peels',404,1);
INSERT INTO `ps_etsy_categories` VALUES (407,297,'Bath & Beauty > Skin Care > Eye Treatments','','bath_and_beauty.skin_care.eye_treatments',404,1);
INSERT INTO `ps_etsy_categories` VALUES (408,298,'Bath & Beauty > Skin Care > Facial Care','','bath_and_beauty.skin_care.facial_care',404,0);
INSERT INTO `ps_etsy_categories` VALUES (409,299,'Bath & Beauty > Skin Care > Facial Care > Face Masks','','bath_and_beauty.skin_care.facial_care.face_masks',408,1);
INSERT INTO `ps_etsy_categories` VALUES (410,300,'Bath & Beauty > Skin Care > Facial Care > Facial Scrubs & Washes','','bath_and_beauty.skin_care.facial_care.facial_scrubs_and_washes',408,1);
INSERT INTO `ps_etsy_categories` VALUES (411,301,'Bath & Beauty > Skin Care > Facial Care > Facial Toner','','bath_and_beauty.skin_care.facial_care.facial_toner',408,1);
INSERT INTO `ps_etsy_categories` VALUES (412,302,'Bath & Beauty > Skin Care > Moisturizers','','bath_and_beauty.skin_care.moisturizers',404,1);
INSERT INTO `ps_etsy_categories` VALUES (413,303,'Bath & Beauty > Skin Care > Salves & Balms','','bath_and_beauty.skin_care.salves_and_balms',404,1);
INSERT INTO `ps_etsy_categories` VALUES (414,304,'Bath & Beauty > Skin Care > Tattooing & Henna','','bath_and_beauty.skin_care.tattooing_and_henna',404,0);
INSERT INTO `ps_etsy_categories` VALUES (415,305,'Bath & Beauty > Skin Care > Tattooing & Henna > Henna','','bath_and_beauty.skin_care.tattooing_and_henna.henna',414,1);
INSERT INTO `ps_etsy_categories` VALUES (416,306,'Bath & Beauty > Skin Care > Tattooing & Henna > Tattoo Care','','bath_and_beauty.skin_care.tattooing_and_henna.tattoo_care',414,1);
INSERT INTO `ps_etsy_categories` VALUES (417,307,'Bath & Beauty > Skin Care > Tattooing & Henna > Tattooing','','bath_and_beauty.skin_care.tattooing_and_henna.tattooing',414,1);
INSERT INTO `ps_etsy_categories` VALUES (418,308,'Bath & Beauty > Soaps','','bath_and_beauty.soaps',305,0);
INSERT INTO `ps_etsy_categories` VALUES (419,309,'Bath & Beauty > Soaps > Bar Soaps','','bath_and_beauty.soaps.bar_soaps',418,1);
INSERT INTO `ps_etsy_categories` VALUES (420,310,'Bath & Beauty > Soaps > Bath Bombs','','bath_and_beauty.soaps.bath_bombs',418,1);
INSERT INTO `ps_etsy_categories` VALUES (421,311,'Bath & Beauty > Soaps > Bath Oils','','bath_and_beauty.soaps.bath_oils',418,1);
INSERT INTO `ps_etsy_categories` VALUES (422,312,'Bath & Beauty > Soaps > Bath Salts & Scrubs','','bath_and_beauty.soaps.bath_salts_and_scrubs',418,1);
INSERT INTO `ps_etsy_categories` VALUES (423,313,'Bath & Beauty > Soaps > Body Washes & Liquid Soaps','','bath_and_beauty.soaps.body_washes_and_liquid_soaps',418,1);
INSERT INTO `ps_etsy_categories` VALUES (424,314,'Bath & Beauty > Soaps > Bubble Bath','','bath_and_beauty.soaps.bubble_bath',418,1);
INSERT INTO `ps_etsy_categories` VALUES (425,315,'Bath & Beauty > Soaps > Sachets & Soaks','','bath_and_beauty.soaps.sachets_and_soaks',418,1);
INSERT INTO `ps_etsy_categories` VALUES (426,316,'Bath & Beauty > Spa & Relaxation','','bath_and_beauty.spa_and_relaxation',305,0);
INSERT INTO `ps_etsy_categories` VALUES (427,317,'Bath & Beauty > Spa & Relaxation > Aromatherapy','','bath_and_beauty.spa_and_relaxation.aromatherapy',426,1);
INSERT INTO `ps_etsy_categories` VALUES (428,318,'Bath & Beauty > Spa & Relaxation > Cold & Heat Packs','','bath_and_beauty.spa_and_relaxation.cold_and_heat_packs',426,1);
INSERT INTO `ps_etsy_categories` VALUES (429,319,'Bath & Beauty > Spa & Relaxation > Massage','','bath_and_beauty.spa_and_relaxation.massage',426,0);
INSERT INTO `ps_etsy_categories` VALUES (430,320,'Bath & Beauty > Spa & Relaxation > Massage > Massage Oils','','bath_and_beauty.spa_and_relaxation.massage.massage_oils',429,1);
INSERT INTO `ps_etsy_categories` VALUES (431,321,'Bath & Beauty > Spa & Relaxation > Massage > Massage Tools','','bath_and_beauty.spa_and_relaxation.massage.massage_tools',429,1);
INSERT INTO `ps_etsy_categories` VALUES (432,322,'Bath & Beauty > Spa & Relaxation > Spa Kits & Gifts','','bath_and_beauty.spa_and_relaxation.spa_kits_and_gifts',426,1);
INSERT INTO `ps_etsy_categories` VALUES (433,323,'Books, Movies & Music','','books_movies_and_music',0,0);
INSERT INTO `ps_etsy_categories` VALUES (434,324,'Books, Movies & Music > Books','','books_movies_and_music.books',433,0);
INSERT INTO `ps_etsy_categories` VALUES (435,335,'Books, Movies & Music > Books > Art & Photography Books','','books_movies_and_music.books.art_and_photography_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (436,336,'Books, Movies & Music > Books > Biographies & Autobiographies','','books_movies_and_music.books.biographies_and_autobiographies',434,1);
INSERT INTO `ps_etsy_categories` VALUES (437,325,'Books, Movies & Music > Books > Blank Books','','books_movies_and_music.books.blank_books',434,0);
INSERT INTO `ps_etsy_categories` VALUES (438,326,'Books, Movies & Music > Books > Blank Books > Journals & Notebooks','','books_movies_and_music.books.blank_books.journals_and_notebooks',437,1);
INSERT INTO `ps_etsy_categories` VALUES (439,327,'Books, Movies & Music > Books > Blank Books > Sketchbooks','','books_movies_and_music.books.blank_books.sketchbooks',437,1);
INSERT INTO `ps_etsy_categories` VALUES (440,328,'Books, Movies & Music > Books > Book Accessories','','books_movies_and_music.books.book_accessories',434,0);
INSERT INTO `ps_etsy_categories` VALUES (441,329,'Books, Movies & Music > Books > Book Accessories > Book Covers','','books_movies_and_music.books.book_accessories.book_covers',440,1);
INSERT INTO `ps_etsy_categories` VALUES (442,331,'Books, Movies & Music > Books > Book Accessories > Bookmarks','','books_movies_and_music.books.book_accessories.bookmarks',440,1);
INSERT INTO `ps_etsy_categories` VALUES (443,332,'Books, Movies & Music > Books > Book Accessories > Bookplates, Stamps, & Embossers','','books_movies_and_music.books.book_accessories.bookplates_stamps_and_embossers',440,1);
INSERT INTO `ps_etsy_categories` VALUES (444,337,'Books, Movies & Music > Books > Book Sets & Collections','','books_movies_and_music.books.book_sets_and_collections',434,1);
INSERT INTO `ps_etsy_categories` VALUES (445,338,'Books, Movies & Music > Books > Children\'s Books','','books_movies_and_music.books.childrens_books',434,0);
INSERT INTO `ps_etsy_categories` VALUES (446,11368,'Books, Movies & Music > Books > Children\'s Books > Board Books','','books_movies_and_music.books.childrens_books.board_books',445,1);
INSERT INTO `ps_etsy_categories` VALUES (447,339,'Books, Movies & Music > Books > Coloring Books','','books_movies_and_music.books.coloring_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (448,340,'Books, Movies & Music > Books > Comics & Graphic Novels','','books_movies_and_music.books.comics_and_graphic_novels',434,0);
INSERT INTO `ps_etsy_categories` VALUES (449,2007,'Books, Movies & Music > Books > Comics & Graphic Novels > Manga','','books_movies_and_music.books.comics_and_graphic_novels.manga',448,1);
INSERT INTO `ps_etsy_categories` VALUES (450,341,'Books, Movies & Music > Books > Cookbooks','','books_movies_and_music.books.cookbooks',434,1);
INSERT INTO `ps_etsy_categories` VALUES (451,343,'Books, Movies & Music > Books > Guides & How Tos','','books_movies_and_music.books.guides_and_how_tos',434,0);
INSERT INTO `ps_etsy_categories` VALUES (452,2909,'Books, Movies & Music > Books > Guides & How Tos > Critiques & Shop Tutorials','','books_movies_and_music.books.guides_and_how_tos.critiques_and_shop_tutorials',451,1);
INSERT INTO `ps_etsy_categories` VALUES (453,344,'Books, Movies & Music > Books > Health & Fitness Books','','books_movies_and_music.books.health_and_fitness_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (454,345,'Books, Movies & Music > Books > History Books','','books_movies_and_music.books.history_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (455,346,'Books, Movies & Music > Books > Humor Books','','books_movies_and_music.books.humor_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (456,347,'Books, Movies & Music > Books > Literature & Fiction','','books_movies_and_music.books.literature_and_fiction',434,0);
INSERT INTO `ps_etsy_categories` VALUES (457,2010,'Books, Movies & Music > Books > Literature & Fiction > Horror','','books_movies_and_music.books.literature_and_fiction.horror',456,1);
INSERT INTO `ps_etsy_categories` VALUES (458,2011,'Books, Movies & Music > Books > Literature & Fiction > Literary Fiction','','books_movies_and_music.books.literature_and_fiction.literary_fiction',456,1);
INSERT INTO `ps_etsy_categories` VALUES (459,2012,'Books, Movies & Music > Books > Literature & Fiction > Mystery & Thriller','','books_movies_and_music.books.literature_and_fiction.mystery_and_thriller',456,1);
INSERT INTO `ps_etsy_categories` VALUES (460,2008,'Books, Movies & Music > Books > Literature & Fiction > Romance','','books_movies_and_music.books.literature_and_fiction.romance',456,1);
INSERT INTO `ps_etsy_categories` VALUES (461,2009,'Books, Movies & Music > Books > Literature & Fiction > Sci Fi & Fantasy','','books_movies_and_music.books.literature_and_fiction.sci_fi_and_fantasy',456,1);
INSERT INTO `ps_etsy_categories` VALUES (462,2013,'Books, Movies & Music > Books > Literature & Fiction > Young Adult','','books_movies_and_music.books.literature_and_fiction.young_adult',456,1);
INSERT INTO `ps_etsy_categories` VALUES (463,349,'Books, Movies & Music > Books > Poetry Books','','books_movies_and_music.books.poetry_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (464,350,'Books, Movies & Music > Books > Reference Books','','books_movies_and_music.books.reference_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (465,351,'Books, Movies & Music > Books > Religion & Spirituality Books','','books_movies_and_music.books.religion_and_spirituality_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (466,352,'Books, Movies & Music > Books > Science & Math Books','','books_movies_and_music.books.science_and_math_books',434,1);
INSERT INTO `ps_etsy_categories` VALUES (467,353,'Books, Movies & Music > Books > Zines & Magazines','','books_movies_and_music.books.zines_and_magazines',434,0);
INSERT INTO `ps_etsy_categories` VALUES (468,2854,'Books, Movies & Music > Books > Zines & Magazines > Magazines','','books_movies_and_music.books.zines_and_magazines.magazines',467,1);
INSERT INTO `ps_etsy_categories` VALUES (469,2853,'Books, Movies & Music > Books > Zines & Magazines > Zines','','books_movies_and_music.books.zines_and_magazines.zines',467,1);
INSERT INTO `ps_etsy_categories` VALUES (470,355,'Books, Movies & Music > Movies','','books_movies_and_music.movies',433,1);
INSERT INTO `ps_etsy_categories` VALUES (471,356,'Books, Movies & Music > Music','','books_movies_and_music.music',433,0);
INSERT INTO `ps_etsy_categories` VALUES (472,357,'Books, Movies & Music > Music > Gig Bags & Instrument Cases','','books_movies_and_music.music.gig_bags_and_instrument_cases',471,1);
INSERT INTO `ps_etsy_categories` VALUES (473,358,'Books, Movies & Music > Music > Instrument Straps','','books_movies_and_music.music.instrument_straps',471,1);
INSERT INTO `ps_etsy_categories` VALUES (474,369,'Books, Movies & Music > Music > Music Cases & Sleeves','','books_movies_and_music.music.music_cases_and_sleeves',471,1);
INSERT INTO `ps_etsy_categories` VALUES (475,359,'Books, Movies & Music > Music > Musical Instruments','','books_movies_and_music.music.musical_instruments',471,0);
INSERT INTO `ps_etsy_categories` VALUES (476,360,'Books, Movies & Music > Music > Musical Instruments > Brass & Horns','','books_movies_and_music.music.musical_instruments.brass_and_horns',475,0);
INSERT INTO `ps_etsy_categories` VALUES (477,2051,'Books, Movies & Music > Music > Musical Instruments > Brass & Horns > Cornet & Other Horns','','books_movies_and_music.music.musical_instruments.brass_and_horns.cornet_and_other_horns',476,1);
INSERT INTO `ps_etsy_categories` VALUES (478,2047,'Books, Movies & Music > Music > Musical Instruments > Brass & Horns > French Horns','','books_movies_and_music.music.musical_instruments.brass_and_horns.french_horns',476,1);
INSERT INTO `ps_etsy_categories` VALUES (479,2050,'Books, Movies & Music > Music > Musical Instruments > Brass & Horns > Trombones','','books_movies_and_music.music.musical_instruments.brass_and_horns.trombones',476,1);
INSERT INTO `ps_etsy_categories` VALUES (480,2046,'Books, Movies & Music > Music > Musical Instruments > Brass & Horns > Trumpets','','books_movies_and_music.music.musical_instruments.brass_and_horns.trumpets',476,1);
INSERT INTO `ps_etsy_categories` VALUES (481,2048,'Books, Movies & Music > Music > Musical Instruments > Brass & Horns > Tubas','','books_movies_and_music.music.musical_instruments.brass_and_horns.tubas',476,1);
INSERT INTO `ps_etsy_categories` VALUES (482,361,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion','','books_movies_and_music.music.musical_instruments.drums_and_percussion',475,0);
INSERT INTO `ps_etsy_categories` VALUES (483,2037,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Bass Drums','','books_movies_and_music.music.musical_instruments.drums_and_percussion.bass_drums',482,1);
INSERT INTO `ps_etsy_categories` VALUES (484,2036,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Bongos','','books_movies_and_music.music.musical_instruments.drums_and_percussion.bongos',482,1);
INSERT INTO `ps_etsy_categories` VALUES (485,2027,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Conga Drums','','books_movies_and_music.music.musical_instruments.drums_and_percussion.conga_drums',482,1);
INSERT INTO `ps_etsy_categories` VALUES (486,2033,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Cymbals','','books_movies_and_music.music.musical_instruments.drums_and_percussion.cymbals',482,1);
INSERT INTO `ps_etsy_categories` VALUES (487,2025,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Drum Kits','','books_movies_and_music.music.musical_instruments.drums_and_percussion.drum_kits',482,1);
INSERT INTO `ps_etsy_categories` VALUES (488,2034,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Drum Thrones','','books_movies_and_music.music.musical_instruments.drums_and_percussion.drum_thrones',482,1);
INSERT INTO `ps_etsy_categories` VALUES (489,2026,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Drumsticks','','books_movies_and_music.music.musical_instruments.drums_and_percussion.drumsticks',482,1);
INSERT INTO `ps_etsy_categories` VALUES (490,6068,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Maracas & Shakers','','books_movies_and_music.music.musical_instruments.drums_and_percussion.maracas_and_shakers',482,1);
INSERT INTO `ps_etsy_categories` VALUES (491,2031,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Marimbas','','books_movies_and_music.music.musical_instruments.drums_and_percussion.marimbas',482,1);
INSERT INTO `ps_etsy_categories` VALUES (492,2029,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Snare Drums','','books_movies_and_music.music.musical_instruments.drums_and_percussion.snare_drums',482,1);
INSERT INTO `ps_etsy_categories` VALUES (493,2035,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Steel Drums','','books_movies_and_music.music.musical_instruments.drums_and_percussion.steel_drums',482,1);
INSERT INTO `ps_etsy_categories` VALUES (494,2028,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Toms','','books_movies_and_music.music.musical_instruments.drums_and_percussion.toms',482,1);
INSERT INTO `ps_etsy_categories` VALUES (495,2030,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Triangles','','books_movies_and_music.music.musical_instruments.drums_and_percussion.triangles',482,1);
INSERT INTO `ps_etsy_categories` VALUES (496,2032,'Books, Movies & Music > Music > Musical Instruments > Drums & Percussion > Xylophones','','books_movies_and_music.music.musical_instruments.drums_and_percussion.xylophones',482,1);
INSERT INTO `ps_etsy_categories` VALUES (497,362,'Books, Movies & Music > Music > Musical Instruments > Microphones','','books_movies_and_music.music.musical_instruments.microphones',475,1);
INSERT INTO `ps_etsy_categories` VALUES (498,363,'Books, Movies & Music > Music > Musical Instruments > Pianos & Keyboards','','books_movies_and_music.music.musical_instruments.pianos_and_keyboards',475,0);
INSERT INTO `ps_etsy_categories` VALUES (499,2024,'Books, Movies & Music > Music > Musical Instruments > Pianos & Keyboards > Accordions','','books_movies_and_music.music.musical_instruments.pianos_and_keyboards.accordions',498,1);
INSERT INTO `ps_etsy_categories` VALUES (500,2022,'Books, Movies & Music > Music > Musical Instruments > Pianos & Keyboards > Keyboards','','books_movies_and_music.music.musical_instruments.pianos_and_keyboards.keyboards',498,1);
INSERT INTO `ps_etsy_categories` VALUES (501,2023,'Books, Movies & Music > Music > Musical Instruments > Pianos & Keyboards > Keytars','','books_movies_and_music.music.musical_instruments.pianos_and_keyboards.keytars',498,1);
INSERT INTO `ps_etsy_categories` VALUES (502,2021,'Books, Movies & Music > Music > Musical Instruments > Pianos & Keyboards > Pianos','','books_movies_and_music.music.musical_instruments.pianos_and_keyboards.pianos',498,1);
INSERT INTO `ps_etsy_categories` VALUES (503,364,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments','','books_movies_and_music.music.musical_instruments.stringed_instruments',475,0);
INSERT INTO `ps_etsy_categories` VALUES (504,2014,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments > Banjos','','books_movies_and_music.music.musical_instruments.stringed_instruments.banjos',503,1);
INSERT INTO `ps_etsy_categories` VALUES (505,2019,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments > Bows','','books_movies_and_music.music.musical_instruments.stringed_instruments.bows',503,1);
INSERT INTO `ps_etsy_categories` VALUES (506,2015,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments > Guitars','','books_movies_and_music.music.musical_instruments.stringed_instruments.guitars',503,1);
INSERT INTO `ps_etsy_categories` VALUES (507,2017,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments > Harps & Lyres','','books_movies_and_music.music.musical_instruments.stringed_instruments.harps_and_lyres',503,1);
INSERT INTO `ps_etsy_categories` VALUES (508,2829,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments > Mandolins','','books_movies_and_music.music.musical_instruments.stringed_instruments.mandolins',503,1);
INSERT INTO `ps_etsy_categories` VALUES (509,2020,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments > Replacement Strings','','books_movies_and_music.music.musical_instruments.stringed_instruments.replacement_strings',503,1);
INSERT INTO `ps_etsy_categories` VALUES (510,2016,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments > Ukuleles','','books_movies_and_music.music.musical_instruments.stringed_instruments.ukuleles',503,1);
INSERT INTO `ps_etsy_categories` VALUES (511,2018,'Books, Movies & Music > Music > Musical Instruments > Stringed Instruments > Violins & Bows','','books_movies_and_music.music.musical_instruments.stringed_instruments.violins_and_bows',503,1);
INSERT INTO `ps_etsy_categories` VALUES (512,365,'Books, Movies & Music > Music > Musical Instruments > Synthesizers & Effects','','books_movies_and_music.music.musical_instruments.synthesizers_and_effects',475,0);
INSERT INTO `ps_etsy_categories` VALUES (513,2406,'Books, Movies & Music > Music > Musical Instruments > Synthesizers & Effects > Drum Machines','','books_movies_and_music.music.musical_instruments.synthesizers_and_effects.drum_machines',512,1);
INSERT INTO `ps_etsy_categories` VALUES (514,2407,'Books, Movies & Music > Music > Musical Instruments > Synthesizers & Effects > Loopers','','books_movies_and_music.music.musical_instruments.synthesizers_and_effects.loopers',512,1);
INSERT INTO `ps_etsy_categories` VALUES (515,2404,'Books, Movies & Music > Music > Musical Instruments > Synthesizers & Effects > Pedals','','books_movies_and_music.music.musical_instruments.synthesizers_and_effects.pedals',512,1);
INSERT INTO `ps_etsy_categories` VALUES (516,2405,'Books, Movies & Music > Music > Musical Instruments > Synthesizers & Effects > Synthesizers','','books_movies_and_music.music.musical_instruments.synthesizers_and_effects.synthesizers',512,1);
INSERT INTO `ps_etsy_categories` VALUES (517,367,'Books, Movies & Music > Music > Musical Instruments > Tuning & Accessories','','books_movies_and_music.music.musical_instruments.tuning_and_accessories',475,0);
INSERT INTO `ps_etsy_categories` VALUES (518,2412,'Books, Movies & Music > Music > Musical Instruments > Tuning & Accessories > Metronomes','','books_movies_and_music.music.musical_instruments.tuning_and_accessories.metronomes',517,1);
INSERT INTO `ps_etsy_categories` VALUES (519,368,'Books, Movies & Music > Music > Musical Instruments > Woodwinds','','books_movies_and_music.music.musical_instruments.woodwinds',475,0);
INSERT INTO `ps_etsy_categories` VALUES (520,2044,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Bagpipes','','books_movies_and_music.music.musical_instruments.woodwinds.bagpipes',519,1);
INSERT INTO `ps_etsy_categories` VALUES (521,2039,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Clarinets','','books_movies_and_music.music.musical_instruments.woodwinds.clarinets',519,1);
INSERT INTO `ps_etsy_categories` VALUES (522,2041,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Didgeridoos','','books_movies_and_music.music.musical_instruments.woodwinds.didgeridoos',519,1);
INSERT INTO `ps_etsy_categories` VALUES (523,2038,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Flutes','','books_movies_and_music.music.musical_instruments.woodwinds.flutes',519,1);
INSERT INTO `ps_etsy_categories` VALUES (524,2043,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Harmonicas','','books_movies_and_music.music.musical_instruments.woodwinds.harmonicas',519,1);
INSERT INTO `ps_etsy_categories` VALUES (525,2040,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Oboes','','books_movies_and_music.music.musical_instruments.woodwinds.oboes',519,1);
INSERT INTO `ps_etsy_categories` VALUES (526,2045,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Recorders','','books_movies_and_music.music.musical_instruments.woodwinds.recorders',519,1);
INSERT INTO `ps_etsy_categories` VALUES (527,2049,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Saxophones','','books_movies_and_music.music.musical_instruments.woodwinds.saxophones',519,1);
INSERT INTO `ps_etsy_categories` VALUES (528,2042,'Books, Movies & Music > Music > Musical Instruments > Woodwinds > Whistles','','books_movies_and_music.music.musical_instruments.woodwinds.whistles',519,1);
INSERT INTO `ps_etsy_categories` VALUES (529,370,'Books, Movies & Music > Music > Picks & Slides','','books_movies_and_music.music.picks_and_slides',471,0);
INSERT INTO `ps_etsy_categories` VALUES (530,2099,'Books, Movies & Music > Music > Picks & Slides > Picks','','books_movies_and_music.music.picks_and_slides.picks',529,1);
INSERT INTO `ps_etsy_categories` VALUES (531,2100,'Books, Movies & Music > Music > Picks & Slides > Slides','','books_movies_and_music.music.picks_and_slides.slides',529,1);
INSERT INTO `ps_etsy_categories` VALUES (532,371,'Books, Movies & Music > Music > Recorded Audio','','books_movies_and_music.music.recorded_audio',471,1);
INSERT INTO `ps_etsy_categories` VALUES (533,372,'Books, Movies & Music > Music > Sheet Music','','books_movies_and_music.music.sheet_music',471,1);
INSERT INTO `ps_etsy_categories` VALUES (534,373,'Books, Movies & Music > Video Cases & Tins','','books_movies_and_music.video_cases_and_tins',433,1);
INSERT INTO `ps_etsy_categories` VALUES (535,374,'Clothing','','clothing',0,0);
INSERT INTO `ps_etsy_categories` VALUES (536,375,'Clothing > Boys\' Clothing','','clothing.boys_clothing',535,0);
INSERT INTO `ps_etsy_categories` VALUES (537,377,'Clothing > Boys\' Clothing > Baby Boys\' Clothing','','clothing.boys_clothing.baby_boys_clothing',536,0);
INSERT INTO `ps_etsy_categories` VALUES (538,378,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Bloomers, Diaper Covers & Underwear','','clothing.boys_clothing.baby_boys_clothing.bloomers_diaper_covers_and_underwear',537,0);
INSERT INTO `ps_etsy_categories` VALUES (539,2180,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Bloomers, Diaper Covers & Underwear > Bloomers','','clothing.boys_clothing.baby_boys_clothing.bloomers_diaper_covers_and_underwear.bloomers',538,1);
INSERT INTO `ps_etsy_categories` VALUES (540,2184,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Bloomers, Diaper Covers & Underwear > Diaper Covers','','clothing.boys_clothing.baby_boys_clothing.bloomers_diaper_covers_and_underwear.diaper_covers',538,1);
INSERT INTO `ps_etsy_categories` VALUES (541,2181,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Bloomers, Diaper Covers & Underwear > Underwear','','clothing.boys_clothing.baby_boys_clothing.bloomers_diaper_covers_and_underwear.underwear',538,1);
INSERT INTO `ps_etsy_categories` VALUES (542,379,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Bodysuits','','clothing.boys_clothing.baby_boys_clothing.bodysuits',537,1);
INSERT INTO `ps_etsy_categories` VALUES (543,380,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Clothing Sets','','clothing.boys_clothing.baby_boys_clothing.clothing_sets',537,1);
INSERT INTO `ps_etsy_categories` VALUES (544,381,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Costumes','','clothing.boys_clothing.baby_boys_clothing.costumes',537,1);
INSERT INTO `ps_etsy_categories` VALUES (545,382,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Hoodies & Sweatshirts','','clothing.boys_clothing.baby_boys_clothing.hoodies_and_sweatshirts',537,0);
INSERT INTO `ps_etsy_categories` VALUES (546,1849,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.boys_clothing.baby_boys_clothing.hoodies_and_sweatshirts.hoodies',545,1);
INSERT INTO `ps_etsy_categories` VALUES (547,2170,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Hoodies & Sweatshirts > Sweatshirts','','clothing.boys_clothing.baby_boys_clothing.hoodies_and_sweatshirts.sweatshirts',545,1);
INSERT INTO `ps_etsy_categories` VALUES (548,383,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Pants','','clothing.boys_clothing.baby_boys_clothing.pants',537,1);
INSERT INTO `ps_etsy_categories` VALUES (549,384,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Socks & Leg Warmers','','clothing.boys_clothing.baby_boys_clothing.socks_and_leg_warmers',537,0);
INSERT INTO `ps_etsy_categories` VALUES (550,1842,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Socks & Leg Warmers > Leg Warmers','','clothing.boys_clothing.baby_boys_clothing.socks_and_leg_warmers.leg_warmers',549,1);
INSERT INTO `ps_etsy_categories` VALUES (551,1841,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Socks & Leg Warmers > Socks','','clothing.boys_clothing.baby_boys_clothing.socks_and_leg_warmers.socks',549,1);
INSERT INTO `ps_etsy_categories` VALUES (552,6091,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Suits','','clothing.boys_clothing.baby_boys_clothing.suits',537,1);
INSERT INTO `ps_etsy_categories` VALUES (553,385,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Sweaters','','clothing.boys_clothing.baby_boys_clothing.sweaters',537,1);
INSERT INTO `ps_etsy_categories` VALUES (554,386,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Tops','','clothing.boys_clothing.baby_boys_clothing.tops',537,1);
INSERT INTO `ps_etsy_categories` VALUES (555,6092,'Clothing > Boys\' Clothing > Baby Boys\' Clothing > Vests','','clothing.boys_clothing.baby_boys_clothing.vests',537,1);
INSERT INTO `ps_etsy_categories` VALUES (556,387,'Clothing > Boys\' Clothing > Bodysuits','','clothing.boys_clothing.bodysuits',536,1);
INSERT INTO `ps_etsy_categories` VALUES (557,388,'Clothing > Boys\' Clothing > Clothing Sets','','clothing.boys_clothing.clothing_sets',536,1);
INSERT INTO `ps_etsy_categories` VALUES (558,389,'Clothing > Boys\' Clothing > Costumes','','clothing.boys_clothing.costumes',536,1);
INSERT INTO `ps_etsy_categories` VALUES (559,390,'Clothing > Boys\' Clothing > Footies & Rompers','','clothing.boys_clothing.footies_and_rompers',536,0);
INSERT INTO `ps_etsy_categories` VALUES (560,2185,'Clothing > Boys\' Clothing > Footies & Rompers > Footies','','clothing.boys_clothing.footies_and_rompers.footies',559,1);
INSERT INTO `ps_etsy_categories` VALUES (561,2186,'Clothing > Boys\' Clothing > Footies & Rompers > Rompers','','clothing.boys_clothing.footies_and_rompers.rompers',559,1);
INSERT INTO `ps_etsy_categories` VALUES (562,391,'Clothing > Boys\' Clothing > Hoodies & Sweatshirts','','clothing.boys_clothing.hoodies_and_sweatshirts',536,0);
INSERT INTO `ps_etsy_categories` VALUES (563,2187,'Clothing > Boys\' Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.boys_clothing.hoodies_and_sweatshirts.hoodies',562,1);
INSERT INTO `ps_etsy_categories` VALUES (564,2188,'Clothing > Boys\' Clothing > Hoodies & Sweatshirts > Sweatshirts','','clothing.boys_clothing.hoodies_and_sweatshirts.sweatshirts',562,1);
INSERT INTO `ps_etsy_categories` VALUES (565,392,'Clothing > Boys\' Clothing > Jackets & Coats','','clothing.boys_clothing.jackets_and_coats',536,1);
INSERT INTO `ps_etsy_categories` VALUES (566,11138,'Clothing > Boys\' Clothing > Jeans','','clothing.boys_clothing.jeans',536,1);
INSERT INTO `ps_etsy_categories` VALUES (567,11221,'Clothing > Boys\' Clothing > Leggings','','clothing.boys_clothing.leggings',536,1);
INSERT INTO `ps_etsy_categories` VALUES (568,11239,'Clothing > Boys\' Clothing > Overalls & Coveralls','','clothing.boys_clothing.overalls_and_coveralls',536,1);
INSERT INTO `ps_etsy_categories` VALUES (569,393,'Clothing > Boys\' Clothing > Pajamas & Robes','','clothing.boys_clothing.pajamas_and_robes',536,0);
INSERT INTO `ps_etsy_categories` VALUES (570,1819,'Clothing > Boys\' Clothing > Pajamas & Robes > Pajamas','','clothing.boys_clothing.pajamas_and_robes.pajamas',569,0);
INSERT INTO `ps_etsy_categories` VALUES (571,11189,'Clothing > Boys\' Clothing > Pajamas & Robes > Pajamas > One-Piece','','clothing.boys_clothing.pajamas_and_robes.pajamas.onepiece',570,1);
INSERT INTO `ps_etsy_categories` VALUES (572,11190,'Clothing > Boys\' Clothing > Pajamas & Robes > Pajamas > Sets','','clothing.boys_clothing.pajamas_and_robes.pajamas.sets',570,1);
INSERT INTO `ps_etsy_categories` VALUES (573,11191,'Clothing > Boys\' Clothing > Pajamas & Robes > Pajamas > Sleep Shorts & Bottoms','','clothing.boys_clothing.pajamas_and_robes.pajamas.sleep_shorts_and_bottoms',570,1);
INSERT INTO `ps_etsy_categories` VALUES (574,11194,'Clothing > Boys\' Clothing > Pajamas & Robes > Pajamas > Tops','','clothing.boys_clothing.pajamas_and_robes.pajamas.tops',570,1);
INSERT INTO `ps_etsy_categories` VALUES (575,1820,'Clothing > Boys\' Clothing > Pajamas & Robes > Robes','','clothing.boys_clothing.pajamas_and_robes.robes',569,1);
INSERT INTO `ps_etsy_categories` VALUES (576,394,'Clothing > Boys\' Clothing > Pants','','clothing.boys_clothing.pants',536,1);
INSERT INTO `ps_etsy_categories` VALUES (577,2832,'Clothing > Boys\' Clothing > Ponchos','','clothing.boys_clothing.ponchos',536,1);
INSERT INTO `ps_etsy_categories` VALUES (578,395,'Clothing > Boys\' Clothing > Shorts','','clothing.boys_clothing.shorts',536,1);
INSERT INTO `ps_etsy_categories` VALUES (579,11212,'Clothing > Boys\' Clothing > Socks & Leg Warmers','','clothing.boys_clothing.socks_and_leg_warmers',536,0);
INSERT INTO `ps_etsy_categories` VALUES (580,11213,'Clothing > Boys\' Clothing > Socks & Leg Warmers > Leg Warmers','','clothing.boys_clothing.socks_and_leg_warmers.leg_warmers',579,1);
INSERT INTO `ps_etsy_categories` VALUES (581,396,'Clothing > Boys\' Clothing > Socks & Leg Warmers > Socks','','clothing.boys_clothing.socks_and_leg_warmers.socks',579,1);
INSERT INTO `ps_etsy_categories` VALUES (582,376,'Clothing > Boys\' Clothing > Sports & Fitness','','clothing.boys_clothing.sports_and_fitness',536,1);
INSERT INTO `ps_etsy_categories` VALUES (583,6089,'Clothing > Boys\' Clothing > Suits','','clothing.boys_clothing.suits',536,1);
INSERT INTO `ps_etsy_categories` VALUES (584,397,'Clothing > Boys\' Clothing > Sweaters','','clothing.boys_clothing.sweaters',536,0);
INSERT INTO `ps_etsy_categories` VALUES (585,11207,'Clothing > Boys\' Clothing > Sweaters > Cardigans','','clothing.boys_clothing.sweaters.cardigans',584,1);
INSERT INTO `ps_etsy_categories` VALUES (586,11208,'Clothing > Boys\' Clothing > Sweaters > Pullover Sweaters','','clothing.boys_clothing.sweaters.pullover_sweaters',584,1);
INSERT INTO `ps_etsy_categories` VALUES (587,11209,'Clothing > Boys\' Clothing > Sweaters > Sweater Vests','','clothing.boys_clothing.sweaters.sweater_vests',584,1);
INSERT INTO `ps_etsy_categories` VALUES (588,398,'Clothing > Boys\' Clothing > Swimwear','','clothing.boys_clothing.swimwear',536,1);
INSERT INTO `ps_etsy_categories` VALUES (589,399,'Clothing > Boys\' Clothing > Tops & Tees','','clothing.boys_clothing.tops_and_tees',536,0);
INSERT INTO `ps_etsy_categories` VALUES (590,11133,'Clothing > Boys\' Clothing > Tops & Tees > Dress Shirts & Button Downs','','clothing.boys_clothing.tops_and_tees.dress_shirts_and_button_downs',589,1);
INSERT INTO `ps_etsy_categories` VALUES (591,11134,'Clothing > Boys\' Clothing > Tops & Tees > Polos','','clothing.boys_clothing.tops_and_tees.polos',589,1);
INSERT INTO `ps_etsy_categories` VALUES (592,11136,'Clothing > Boys\' Clothing > Tops & Tees > T-shirts','','clothing.boys_clothing.tops_and_tees.tshirts',589,0);
INSERT INTO `ps_etsy_categories` VALUES (593,11137,'Clothing > Boys\' Clothing > Tops & Tees > T-shirts > Graphic Tees','','clothing.boys_clothing.tops_and_tees.tshirts.graphic_tees',592,1);
INSERT INTO `ps_etsy_categories` VALUES (594,11135,'Clothing > Boys\' Clothing > Tops & Tees > Tanks','','clothing.boys_clothing.tops_and_tees.tanks',589,0);
INSERT INTO `ps_etsy_categories` VALUES (595,11215,'Clothing > Boys\' Clothing > Tops & Tees > Tanks > Graphic Tanks','','clothing.boys_clothing.tops_and_tees.tanks.graphic_tanks',594,1);
INSERT INTO `ps_etsy_categories` VALUES (596,400,'Clothing > Boys\' Clothing > Underwear','','clothing.boys_clothing.underwear',536,1);
INSERT INTO `ps_etsy_categories` VALUES (597,6090,'Clothing > Boys\' Clothing > Vests','','clothing.boys_clothing.vests',536,1);
INSERT INTO `ps_etsy_categories` VALUES (598,401,'Clothing > Girls\' Clothing','','clothing.girls_clothing',535,0);
INSERT INTO `ps_etsy_categories` VALUES (599,403,'Clothing > Girls\' Clothing > Baby Girls\' Clothing','','clothing.girls_clothing.baby_girls_clothing',598,0);
INSERT INTO `ps_etsy_categories` VALUES (600,404,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Bloomers, Diaper Covers & Underwear','','clothing.girls_clothing.baby_girls_clothing.bloomers_diaper_covers_and_underwear',599,0);
INSERT INTO `ps_etsy_categories` VALUES (601,2189,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Bloomers, Diaper Covers & Underwear > Bloomers','','clothing.girls_clothing.baby_girls_clothing.bloomers_diaper_covers_and_underwear.bloomers',600,1);
INSERT INTO `ps_etsy_categories` VALUES (602,2190,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Bloomers, Diaper Covers & Underwear > Diaper Covers','','clothing.girls_clothing.baby_girls_clothing.bloomers_diaper_covers_and_underwear.diaper_covers',600,1);
INSERT INTO `ps_etsy_categories` VALUES (603,2191,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Bloomers, Diaper Covers & Underwear > Underwear','','clothing.girls_clothing.baby_girls_clothing.bloomers_diaper_covers_and_underwear.underwear',600,1);
INSERT INTO `ps_etsy_categories` VALUES (604,405,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Bodysuits','','clothing.girls_clothing.baby_girls_clothing.bodysuits',599,1);
INSERT INTO `ps_etsy_categories` VALUES (605,406,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Clothing Sets','','clothing.girls_clothing.baby_girls_clothing.clothing_sets',599,1);
INSERT INTO `ps_etsy_categories` VALUES (606,407,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Costumes','','clothing.girls_clothing.baby_girls_clothing.costumes',599,1);
INSERT INTO `ps_etsy_categories` VALUES (607,408,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Dresses','','clothing.girls_clothing.baby_girls_clothing.dresses',599,1);
INSERT INTO `ps_etsy_categories` VALUES (608,409,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Hoodies & Sweatshirts','','clothing.girls_clothing.baby_girls_clothing.hoodies_and_sweatshirts',599,0);
INSERT INTO `ps_etsy_categories` VALUES (609,1850,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.girls_clothing.baby_girls_clothing.hoodies_and_sweatshirts.hoodies',608,1);
INSERT INTO `ps_etsy_categories` VALUES (610,2199,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Hoodies & Sweatshirts > Sweatshirts','','clothing.girls_clothing.baby_girls_clothing.hoodies_and_sweatshirts.sweatshirts',608,1);
INSERT INTO `ps_etsy_categories` VALUES (611,410,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Pajamas & Robes','','clothing.girls_clothing.baby_girls_clothing.pajamas_and_robes',599,0);
INSERT INTO `ps_etsy_categories` VALUES (612,1824,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Pajamas & Robes > Pajamas','','clothing.girls_clothing.baby_girls_clothing.pajamas_and_robes.pajamas',611,1);
INSERT INTO `ps_etsy_categories` VALUES (613,1823,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Pajamas & Robes > Robes','','clothing.girls_clothing.baby_girls_clothing.pajamas_and_robes.robes',611,1);
INSERT INTO `ps_etsy_categories` VALUES (614,411,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Pants','','clothing.girls_clothing.baby_girls_clothing.pants',599,1);
INSERT INTO `ps_etsy_categories` VALUES (615,412,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Skirts','','clothing.girls_clothing.baby_girls_clothing.skirts',599,1);
INSERT INTO `ps_etsy_categories` VALUES (616,413,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Socks & Leg Warmers','','clothing.girls_clothing.baby_girls_clothing.socks_and_leg_warmers',599,0);
INSERT INTO `ps_etsy_categories` VALUES (617,1844,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Socks & Leg Warmers > Leg Warmers','','clothing.girls_clothing.baby_girls_clothing.socks_and_leg_warmers.leg_warmers',616,1);
INSERT INTO `ps_etsy_categories` VALUES (618,1843,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Socks & Leg Warmers > Socks','','clothing.girls_clothing.baby_girls_clothing.socks_and_leg_warmers.socks',616,1);
INSERT INTO `ps_etsy_categories` VALUES (619,414,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Sweaters','','clothing.girls_clothing.baby_girls_clothing.sweaters',599,1);
INSERT INTO `ps_etsy_categories` VALUES (620,415,'Clothing > Girls\' Clothing > Baby Girls\' Clothing > Tops','','clothing.girls_clothing.baby_girls_clothing.tops',599,1);
INSERT INTO `ps_etsy_categories` VALUES (621,416,'Clothing > Girls\' Clothing > Bodysuits','','clothing.girls_clothing.bodysuits',598,1);
INSERT INTO `ps_etsy_categories` VALUES (622,417,'Clothing > Girls\' Clothing > Clothing Sets','','clothing.girls_clothing.clothing_sets',598,1);
INSERT INTO `ps_etsy_categories` VALUES (623,418,'Clothing > Girls\' Clothing > Costumes','','clothing.girls_clothing.costumes',598,1);
INSERT INTO `ps_etsy_categories` VALUES (624,419,'Clothing > Girls\' Clothing > Dresses','','clothing.girls_clothing.dresses',598,1);
INSERT INTO `ps_etsy_categories` VALUES (625,420,'Clothing > Girls\' Clothing > Footies & Rompers','','clothing.girls_clothing.footies_and_rompers',598,0);
INSERT INTO `ps_etsy_categories` VALUES (626,11150,'Clothing > Girls\' Clothing > Footies & Rompers > Footies','','clothing.girls_clothing.footies_and_rompers.footies',625,1);
INSERT INTO `ps_etsy_categories` VALUES (627,11151,'Clothing > Girls\' Clothing > Footies & Rompers > Rompers','','clothing.girls_clothing.footies_and_rompers.rompers',625,1);
INSERT INTO `ps_etsy_categories` VALUES (628,421,'Clothing > Girls\' Clothing > Hoodies & Sweatshirts','','clothing.girls_clothing.hoodies_and_sweatshirts',598,0);
INSERT INTO `ps_etsy_categories` VALUES (629,1851,'Clothing > Girls\' Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.girls_clothing.hoodies_and_sweatshirts.hoodies',628,1);
INSERT INTO `ps_etsy_categories` VALUES (630,2200,'Clothing > Girls\' Clothing > Hoodies & Sweatshirts > Sweatshirts','','clothing.girls_clothing.hoodies_and_sweatshirts.sweatshirts',628,1);
INSERT INTO `ps_etsy_categories` VALUES (631,422,'Clothing > Girls\' Clothing > Jackets & Coats','','clothing.girls_clothing.jackets_and_coats',598,1);
INSERT INTO `ps_etsy_categories` VALUES (632,11147,'Clothing > Girls\' Clothing > Jeans','','clothing.girls_clothing.jeans',598,1);
INSERT INTO `ps_etsy_categories` VALUES (633,11162,'Clothing > Girls\' Clothing > Leggings','','clothing.girls_clothing.leggings',598,1);
INSERT INTO `ps_etsy_categories` VALUES (634,11241,'Clothing > Girls\' Clothing > Overalls','','clothing.girls_clothing.overalls',598,1);
INSERT INTO `ps_etsy_categories` VALUES (635,423,'Clothing > Girls\' Clothing > Pajamas & Robes','','clothing.girls_clothing.pajamas_and_robes',598,0);
INSERT INTO `ps_etsy_categories` VALUES (636,1821,'Clothing > Girls\' Clothing > Pajamas & Robes > Pajamas','','clothing.girls_clothing.pajamas_and_robes.pajamas',635,0);
INSERT INTO `ps_etsy_categories` VALUES (637,11182,'Clothing > Girls\' Clothing > Pajamas & Robes > Pajamas > Night Gowns & Tops','','clothing.girls_clothing.pajamas_and_robes.pajamas.night_gowns_and_tops',636,1);
INSERT INTO `ps_etsy_categories` VALUES (638,11183,'Clothing > Girls\' Clothing > Pajamas & Robes > Pajamas > Rompers & One-Piece','','clothing.girls_clothing.pajamas_and_robes.pajamas.rompers_and_onepiece',636,1);
INSERT INTO `ps_etsy_categories` VALUES (639,11184,'Clothing > Girls\' Clothing > Pajamas & Robes > Pajamas > Sets','','clothing.girls_clothing.pajamas_and_robes.pajamas.sets',636,1);
INSERT INTO `ps_etsy_categories` VALUES (640,11185,'Clothing > Girls\' Clothing > Pajamas & Robes > Pajamas > Sleep Shorts & Bottoms','','clothing.girls_clothing.pajamas_and_robes.pajamas.sleep_shorts_and_bottoms',636,1);
INSERT INTO `ps_etsy_categories` VALUES (641,1822,'Clothing > Girls\' Clothing > Pajamas & Robes > Robes','','clothing.girls_clothing.pajamas_and_robes.robes',635,1);
INSERT INTO `ps_etsy_categories` VALUES (642,424,'Clothing > Girls\' Clothing > Pants & Capris','','clothing.girls_clothing.pants_and_capris',598,1);
INSERT INTO `ps_etsy_categories` VALUES (643,2833,'Clothing > Girls\' Clothing > Ponchos','','clothing.girls_clothing.ponchos',598,1);
INSERT INTO `ps_etsy_categories` VALUES (644,425,'Clothing > Girls\' Clothing > Shorts & Skorts','','clothing.girls_clothing.shorts_and_skorts',598,0);
INSERT INTO `ps_etsy_categories` VALUES (645,11152,'Clothing > Girls\' Clothing > Shorts & Skorts > Shorts','','clothing.girls_clothing.shorts_and_skorts.shorts',644,1);
INSERT INTO `ps_etsy_categories` VALUES (646,11153,'Clothing > Girls\' Clothing > Shorts & Skorts > Skorts','','clothing.girls_clothing.shorts_and_skorts.skorts',644,1);
INSERT INTO `ps_etsy_categories` VALUES (647,426,'Clothing > Girls\' Clothing > Skirts','','clothing.girls_clothing.skirts',598,1);
INSERT INTO `ps_etsy_categories` VALUES (648,427,'Clothing > Girls\' Clothing > Socks & Leg Warmers','','clothing.girls_clothing.socks_and_leg_warmers',598,0);
INSERT INTO `ps_etsy_categories` VALUES (649,1846,'Clothing > Girls\' Clothing > Socks & Leg Warmers > Leg Warmers','','clothing.girls_clothing.socks_and_leg_warmers.girls_leg_warmers',648,1);
INSERT INTO `ps_etsy_categories` VALUES (650,1845,'Clothing > Girls\' Clothing > Socks & Leg Warmers > Socks','','clothing.girls_clothing.socks_and_leg_warmers.girls_socks',648,1);
INSERT INTO `ps_etsy_categories` VALUES (651,11240,'Clothing > Girls\' Clothing > Socks & Leg Warmers > Tights & Hosiery','','clothing.girls_clothing.socks_and_leg_warmers.tights_and_hosiery',648,1);
INSERT INTO `ps_etsy_categories` VALUES (652,402,'Clothing > Girls\' Clothing > Sports & Fitness','','clothing.girls_clothing.sports_and_fitness',598,1);
INSERT INTO `ps_etsy_categories` VALUES (653,428,'Clothing > Girls\' Clothing > Sweaters','','clothing.girls_clothing.sweaters',598,0);
INSERT INTO `ps_etsy_categories` VALUES (654,11204,'Clothing > Girls\' Clothing > Sweaters > Cardigans','','clothing.girls_clothing.sweaters.cardigans',653,1);
INSERT INTO `ps_etsy_categories` VALUES (655,11205,'Clothing > Girls\' Clothing > Sweaters > Pullover Sweaters','','clothing.girls_clothing.sweaters.pullover_sweaters',653,1);
INSERT INTO `ps_etsy_categories` VALUES (656,11225,'Clothing > Girls\' Clothing > Sweaters > Shrugs & Boleros','','clothing.girls_clothing.sweaters.shrugs_and_boleros',653,1);
INSERT INTO `ps_etsy_categories` VALUES (657,11206,'Clothing > Girls\' Clothing > Sweaters > Sweater Vests','','clothing.girls_clothing.sweaters.sweater_vests',653,1);
INSERT INTO `ps_etsy_categories` VALUES (658,429,'Clothing > Girls\' Clothing > Swimwear','','clothing.girls_clothing.swimwear',598,1);
INSERT INTO `ps_etsy_categories` VALUES (659,430,'Clothing > Girls\' Clothing > Tops & Tees','','clothing.girls_clothing.tops_and_tees',598,0);
INSERT INTO `ps_etsy_categories` VALUES (660,11140,'Clothing > Girls\' Clothing > Tops & Tees > Blouses','','clothing.girls_clothing.tops_and_tees.blouses',659,1);
INSERT INTO `ps_etsy_categories` VALUES (661,11154,'Clothing > Girls\' Clothing > Tops & Tees > Crop & Tube Tops','','clothing.girls_clothing.tops_and_tees.crop_and_tube_tops',659,0);
INSERT INTO `ps_etsy_categories` VALUES (662,11155,'Clothing > Girls\' Clothing > Tops & Tees > Crop & Tube Tops > Crop Tops','','clothing.girls_clothing.tops_and_tees.crop_and_tube_tops.crop_tops',661,1);
INSERT INTO `ps_etsy_categories` VALUES (663,11156,'Clothing > Girls\' Clothing > Tops & Tees > Crop & Tube Tops > Tube Tops','','clothing.girls_clothing.tops_and_tees.crop_and_tube_tops.tube_tops',661,1);
INSERT INTO `ps_etsy_categories` VALUES (664,11141,'Clothing > Girls\' Clothing > Tops & Tees > Polos','','clothing.girls_clothing.tops_and_tees.polos',659,1);
INSERT INTO `ps_etsy_categories` VALUES (665,11143,'Clothing > Girls\' Clothing > Tops & Tees > T-shirts','','clothing.girls_clothing.tops_and_tees.tshirts',659,0);
INSERT INTO `ps_etsy_categories` VALUES (666,11144,'Clothing > Girls\' Clothing > Tops & Tees > T-shirts > Graphic Tees','','clothing.girls_clothing.tops_and_tees.tshirts.graphic_tees',665,1);
INSERT INTO `ps_etsy_categories` VALUES (667,11142,'Clothing > Girls\' Clothing > Tops & Tees > Tanks','','clothing.girls_clothing.tops_and_tees.tanks',659,0);
INSERT INTO `ps_etsy_categories` VALUES (668,11216,'Clothing > Girls\' Clothing > Tops & Tees > Tanks > Graphic Tanks','','clothing.girls_clothing.tops_and_tees.tanks.graphic_tanks',667,1);
INSERT INTO `ps_etsy_categories` VALUES (669,11145,'Clothing > Girls\' Clothing > Tops & Tees > Tunics','','clothing.girls_clothing.tops_and_tees.tunics',659,1);
INSERT INTO `ps_etsy_categories` VALUES (670,431,'Clothing > Girls\' Clothing > Underwear','','clothing.girls_clothing.underwear',598,0);
INSERT INTO `ps_etsy_categories` VALUES (671,11157,'Clothing > Girls\' Clothing > Underwear > Camisoles','','clothing.girls_clothing.underwear.camisoles',670,1);
INSERT INTO `ps_etsy_categories` VALUES (672,11161,'Clothing > Girls\' Clothing > Underwear > Panties','','clothing.girls_clothing.underwear.panties',670,1);
INSERT INTO `ps_etsy_categories` VALUES (673,11158,'Clothing > Girls\' Clothing > Underwear > Petticoats','','clothing.girls_clothing.underwear.petticoats',670,1);
INSERT INTO `ps_etsy_categories` VALUES (674,11159,'Clothing > Girls\' Clothing > Underwear > Slips','','clothing.girls_clothing.underwear.slips',670,1);
INSERT INTO `ps_etsy_categories` VALUES (675,11160,'Clothing > Girls\' Clothing > Underwear > Training Bras & Bralettes','','clothing.girls_clothing.underwear.training_bras_and_bralettes',670,1);
INSERT INTO `ps_etsy_categories` VALUES (676,11146,'Clothing > Girls\' Clothing > Vests','','clothing.girls_clothing.vests',598,1);
INSERT INTO `ps_etsy_categories` VALUES (677,432,'Clothing > Men\'s Clothing','','clothing.mens_clothing',535,0);
INSERT INTO `ps_etsy_categories` VALUES (678,434,'Clothing > Men\'s Clothing > Costumes','','clothing.mens_clothing.costumes',677,1);
INSERT INTO `ps_etsy_categories` VALUES (679,11132,'Clothing > Men\'s Clothing > Harnesses','','clothing.mens_clothing.harnesses',677,1);
INSERT INTO `ps_etsy_categories` VALUES (680,435,'Clothing > Men\'s Clothing > Hoodies & Sweatshirts','','clothing.mens_clothing.hoodies_and_sweatshirts',677,0);
INSERT INTO `ps_etsy_categories` VALUES (681,1852,'Clothing > Men\'s Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.mens_clothing.hoodies_and_sweatshirts.hoodies',680,1);
INSERT INTO `ps_etsy_categories` VALUES (682,2201,'Clothing > Men\'s Clothing > Hoodies & Sweatshirts > Sweatshirts','','clothing.mens_clothing.hoodies_and_sweatshirts.sweatshirts',680,1);
INSERT INTO `ps_etsy_categories` VALUES (683,436,'Clothing > Men\'s Clothing > Jackets & Coats','','clothing.mens_clothing.jackets_and_coats',677,1);
INSERT INTO `ps_etsy_categories` VALUES (684,2826,'Clothing > Men\'s Clothing > Jeans','','clothing.mens_clothing.jeans',677,1);
INSERT INTO `ps_etsy_categories` VALUES (685,437,'Clothing > Men\'s Clothing > Kilts & Skirts','','clothing.mens_clothing.kilts_and_skirts',677,1);
INSERT INTO `ps_etsy_categories` VALUES (686,11224,'Clothing > Men\'s Clothing > Leggings','','clothing.mens_clothing.leggings',677,1);
INSERT INTO `ps_etsy_categories` VALUES (687,11131,'Clothing > Men\'s Clothing > Overalls & Coveralls','','clothing.mens_clothing.overalls_and_coveralls',677,1);
INSERT INTO `ps_etsy_categories` VALUES (688,438,'Clothing > Men\'s Clothing > Pajamas & Robes','','clothing.mens_clothing.pajamas_and_robes',677,0);
INSERT INTO `ps_etsy_categories` VALUES (689,11179,'Clothing > Men\'s Clothing > Pajamas & Robes > One-Piece','','clothing.mens_clothing.pajamas_and_robes.onepiece',688,1);
INSERT INTO `ps_etsy_categories` VALUES (690,439,'Clothing > Men\'s Clothing > Pajamas & Robes > Robes','','clothing.mens_clothing.pajamas_and_robes.robes',688,1);
INSERT INTO `ps_etsy_categories` VALUES (691,440,'Clothing > Men\'s Clothing > Pajamas & Robes > Sets','','clothing.mens_clothing.pajamas_and_robes.sets',688,1);
INSERT INTO `ps_etsy_categories` VALUES (692,441,'Clothing > Men\'s Clothing > Pajamas & Robes > Sleep Shorts & Bottoms','','clothing.mens_clothing.pajamas_and_robes.sleep_shorts_and_bottoms',688,0);
INSERT INTO `ps_etsy_categories` VALUES (693,11181,'Clothing > Men\'s Clothing > Pajamas & Robes > Sleep Shorts & Bottoms > Pants','','clothing.mens_clothing.pajamas_and_robes.sleep_shorts_and_bottoms.pants',692,1);
INSERT INTO `ps_etsy_categories` VALUES (694,11180,'Clothing > Men\'s Clothing > Pajamas & Robes > Sleep Shorts & Bottoms > Shorts','','clothing.mens_clothing.pajamas_and_robes.sleep_shorts_and_bottoms.shorts',692,1);
INSERT INTO `ps_etsy_categories` VALUES (695,442,'Clothing > Men\'s Clothing > Pajamas & Robes > Tops','','clothing.mens_clothing.pajamas_and_robes.tops',688,1);
INSERT INTO `ps_etsy_categories` VALUES (696,443,'Clothing > Men\'s Clothing > Pants','','clothing.mens_clothing.pants',677,1);
INSERT INTO `ps_etsy_categories` VALUES (697,2834,'Clothing > Men\'s Clothing > Ponchos','','clothing.mens_clothing.ponchos',677,1);
INSERT INTO `ps_etsy_categories` VALUES (698,444,'Clothing > Men\'s Clothing > Shirts & Tees','','clothing.mens_clothing.shirts_and_tees',677,0);
INSERT INTO `ps_etsy_categories` VALUES (699,445,'Clothing > Men\'s Clothing > Shirts & Tees > Dress Shirts','','clothing.mens_clothing.shirts_and_tees.dress_shirts',698,1);
INSERT INTO `ps_etsy_categories` VALUES (700,446,'Clothing > Men\'s Clothing > Shirts & Tees > Oxfords & Button Downs','','clothing.mens_clothing.shirts_and_tees.oxfords_and_button_downs',698,1);
INSERT INTO `ps_etsy_categories` VALUES (701,447,'Clothing > Men\'s Clothing > Shirts & Tees > Polos','','clothing.mens_clothing.shirts_and_tees.polos',698,1);
INSERT INTO `ps_etsy_categories` VALUES (702,449,'Clothing > Men\'s Clothing > Shirts & Tees > T-shirts','','clothing.mens_clothing.shirts_and_tees.tshirts',698,0);
INSERT INTO `ps_etsy_categories` VALUES (703,11110,'Clothing > Men\'s Clothing > Shirts & Tees > T-shirts > Graphic Tees','','clothing.mens_clothing.shirts_and_tees.tshirts.graphic_tees',702,1);
INSERT INTO `ps_etsy_categories` VALUES (704,448,'Clothing > Men\'s Clothing > Shirts & Tees > Tanks','','clothing.mens_clothing.shirts_and_tees.tanks',698,0);
INSERT INTO `ps_etsy_categories` VALUES (705,11217,'Clothing > Men\'s Clothing > Shirts & Tees > Tanks > Graphic Tanks','','clothing.mens_clothing.shirts_and_tees.tanks.graphic_tanks',704,1);
INSERT INTO `ps_etsy_categories` VALUES (706,450,'Clothing > Men\'s Clothing > Shorts','','clothing.mens_clothing.shorts',677,0);
INSERT INTO `ps_etsy_categories` VALUES (707,1888,'Clothing > Men\'s Clothing > Shorts > Cargo Shorts','','clothing.mens_clothing.shorts.cargo_shorts',706,1);
INSERT INTO `ps_etsy_categories` VALUES (708,451,'Clothing > Men\'s Clothing > Socks','','clothing.mens_clothing.socks',677,0);
INSERT INTO `ps_etsy_categories` VALUES (709,452,'Clothing > Men\'s Clothing > Socks > Athletic Socks','','clothing.mens_clothing.socks.athletic_socks',708,1);
INSERT INTO `ps_etsy_categories` VALUES (710,453,'Clothing > Men\'s Clothing > Socks > Casual Socks','','clothing.mens_clothing.socks.casual_socks',708,1);
INSERT INTO `ps_etsy_categories` VALUES (711,454,'Clothing > Men\'s Clothing > Socks > Dress Socks','','clothing.mens_clothing.socks.dress_socks',708,1);
INSERT INTO `ps_etsy_categories` VALUES (712,433,'Clothing > Men\'s Clothing > Sports & Fitness','','clothing.mens_clothing.sports_and_fitness',677,1);
INSERT INTO `ps_etsy_categories` VALUES (713,455,'Clothing > Men\'s Clothing > Suits & Sport Coats','','clothing.mens_clothing.suits_and_sport_coats',677,0);
INSERT INTO `ps_etsy_categories` VALUES (714,1826,'Clothing > Men\'s Clothing > Suits & Sport Coats > Sport Coats','','clothing.mens_clothing.suits_and_sport_coats.sport_coats',713,1);
INSERT INTO `ps_etsy_categories` VALUES (715,1825,'Clothing > Men\'s Clothing > Suits & Sport Coats > Suits','','clothing.mens_clothing.suits_and_sport_coats.suits',713,1);
INSERT INTO `ps_etsy_categories` VALUES (716,456,'Clothing > Men\'s Clothing > Sweaters','','clothing.mens_clothing.sweaters',677,0);
INSERT INTO `ps_etsy_categories` VALUES (717,457,'Clothing > Men\'s Clothing > Sweaters > Cardigans','','clothing.mens_clothing.sweaters.cardigans',716,1);
INSERT INTO `ps_etsy_categories` VALUES (718,458,'Clothing > Men\'s Clothing > Sweaters > Pullover Sweaters','','clothing.mens_clothing.sweaters.pullover_sweaters',716,1);
INSERT INTO `ps_etsy_categories` VALUES (719,459,'Clothing > Men\'s Clothing > Sweaters > Sweater Vests','','clothing.mens_clothing.sweaters.sweater_vests',716,1);
INSERT INTO `ps_etsy_categories` VALUES (720,460,'Clothing > Men\'s Clothing > Swimwear','','clothing.mens_clothing.swimwear',677,1);
INSERT INTO `ps_etsy_categories` VALUES (721,461,'Clothing > Men\'s Clothing > Underwear','','clothing.mens_clothing.underwear',677,0);
INSERT INTO `ps_etsy_categories` VALUES (722,462,'Clothing > Men\'s Clothing > Underwear > Boxers & Briefs','','clothing.mens_clothing.underwear.boxers_and_briefs',721,0);
INSERT INTO `ps_etsy_categories` VALUES (723,1829,'Clothing > Men\'s Clothing > Underwear > Boxers & Briefs > Boxer Briefs','','clothing.mens_clothing.underwear.boxers_and_briefs.boxer_briefs',722,1);
INSERT INTO `ps_etsy_categories` VALUES (724,1827,'Clothing > Men\'s Clothing > Underwear > Boxers & Briefs > Boxers','','clothing.mens_clothing.underwear.boxers_and_briefs.boxers',722,1);
INSERT INTO `ps_etsy_categories` VALUES (725,1828,'Clothing > Men\'s Clothing > Underwear > Boxers & Briefs > Briefs','','clothing.mens_clothing.underwear.boxers_and_briefs.briefs',722,1);
INSERT INTO `ps_etsy_categories` VALUES (726,463,'Clothing > Men\'s Clothing > Underwear > Jockstraps','','clothing.mens_clothing.underwear.jockstraps',721,1);
INSERT INTO `ps_etsy_categories` VALUES (727,464,'Clothing > Men\'s Clothing > Underwear > Undershirts','','clothing.mens_clothing.underwear.undershirts',721,1);
INSERT INTO `ps_etsy_categories` VALUES (728,2838,'Clothing > Men\'s Clothing > Vests','','clothing.mens_clothing.vests',677,0);
INSERT INTO `ps_etsy_categories` VALUES (729,2839,'Clothing > Men\'s Clothing > Vests > Formal Vests','','clothing.mens_clothing.vests.formal_vests',728,1);
INSERT INTO `ps_etsy_categories` VALUES (730,465,'Clothing > Unisex Adult Clothing','','clothing.unisex_adult_clothing',535,0);
INSERT INTO `ps_etsy_categories` VALUES (731,467,'Clothing > Unisex Adult Clothing > Blazers','','clothing.unisex_adult_clothing.blazers',730,1);
INSERT INTO `ps_etsy_categories` VALUES (732,468,'Clothing > Unisex Adult Clothing > Costumes','','clothing.unisex_adult_clothing.costumes',730,1);
INSERT INTO `ps_etsy_categories` VALUES (733,469,'Clothing > Unisex Adult Clothing > Hoodies & Sweatshirts','','clothing.unisex_adult_clothing.hoodies_and_sweatshirts',730,0);
INSERT INTO `ps_etsy_categories` VALUES (734,1853,'Clothing > Unisex Adult Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.unisex_adult_clothing.hoodies_and_sweatshirts.hoodies',733,1);
INSERT INTO `ps_etsy_categories` VALUES (735,2202,'Clothing > Unisex Adult Clothing > Hoodies & Sweatshirts > Sweatshirts','','clothing.unisex_adult_clothing.hoodies_and_sweatshirts.sweatshirts',733,1);
INSERT INTO `ps_etsy_categories` VALUES (736,2142,'Clothing > Unisex Adult Clothing > Jackets & Coats','','clothing.unisex_adult_clothing.jackets_and_coats',730,1);
INSERT INTO `ps_etsy_categories` VALUES (737,470,'Clothing > Unisex Adult Clothing > Jeans','','clothing.unisex_adult_clothing.jeans',730,1);
INSERT INTO `ps_etsy_categories` VALUES (738,11223,'Clothing > Unisex Adult Clothing > Leggings','','clothing.unisex_adult_clothing.leggings',730,1);
INSERT INTO `ps_etsy_categories` VALUES (739,1756,'Clothing > Unisex Adult Clothing > Overalls & Coveralls','','clothing.unisex_adult_clothing.overalls_and_coveralls',730,1);
INSERT INTO `ps_etsy_categories` VALUES (740,1755,'Clothing > Unisex Adult Clothing > Pajamas & Robes','','clothing.unisex_adult_clothing.pajamas_and_robes',730,0);
INSERT INTO `ps_etsy_categories` VALUES (741,1830,'Clothing > Unisex Adult Clothing > Pajamas & Robes > Pajamas','','clothing.unisex_adult_clothing.pajamas_and_robes.pajamas',740,0);
INSERT INTO `ps_etsy_categories` VALUES (742,11195,'Clothing > Unisex Adult Clothing > Pajamas & Robes > Pajamas > One-Piece','','clothing.unisex_adult_clothing.pajamas_and_robes.pajamas.onepiece',741,1);
INSERT INTO `ps_etsy_categories` VALUES (743,11196,'Clothing > Unisex Adult Clothing > Pajamas & Robes > Pajamas > Sets','','clothing.unisex_adult_clothing.pajamas_and_robes.pajamas.sets',741,1);
INSERT INTO `ps_etsy_categories` VALUES (744,11198,'Clothing > Unisex Adult Clothing > Pajamas & Robes > Pajamas > Sleep Shorts & Bottoms','','clothing.unisex_adult_clothing.pajamas_and_robes.pajamas.sleep_shorts_and_bottoms',741,1);
INSERT INTO `ps_etsy_categories` VALUES (745,11197,'Clothing > Unisex Adult Clothing > Pajamas & Robes > Pajamas > Tops','','clothing.unisex_adult_clothing.pajamas_and_robes.pajamas.tops',741,1);
INSERT INTO `ps_etsy_categories` VALUES (746,1831,'Clothing > Unisex Adult Clothing > Pajamas & Robes > Robes','','clothing.unisex_adult_clothing.pajamas_and_robes.robes',740,1);
INSERT INTO `ps_etsy_categories` VALUES (747,471,'Clothing > Unisex Adult Clothing > Pants','','clothing.unisex_adult_clothing.pants',730,1);
INSERT INTO `ps_etsy_categories` VALUES (748,2836,'Clothing > Unisex Adult Clothing > Ponchos','','clothing.unisex_adult_clothing.ponchos',730,1);
INSERT INTO `ps_etsy_categories` VALUES (749,1754,'Clothing > Unisex Adult Clothing > Shorts','','clothing.unisex_adult_clothing.shorts',730,1);
INSERT INTO `ps_etsy_categories` VALUES (750,11166,'Clothing > Unisex Adult Clothing > Skirts & Kilts','','clothing.unisex_adult_clothing.skirts_and_kilts',730,1);
INSERT INTO `ps_etsy_categories` VALUES (751,472,'Clothing > Unisex Adult Clothing > Socks & Hosiery','','clothing.unisex_adult_clothing.socks_and_hosiery',730,0);
INSERT INTO `ps_etsy_categories` VALUES (752,11163,'Clothing > Unisex Adult Clothing > Socks & Hosiery > Athletic Socks','','clothing.unisex_adult_clothing.socks_and_hosiery.athletic_socks',751,1);
INSERT INTO `ps_etsy_categories` VALUES (753,473,'Clothing > Unisex Adult Clothing > Socks & Hosiery > Casual Socks','','clothing.unisex_adult_clothing.socks_and_hosiery.casual_socks',751,1);
INSERT INTO `ps_etsy_categories` VALUES (754,474,'Clothing > Unisex Adult Clothing > Socks & Hosiery > Dress Socks','','clothing.unisex_adult_clothing.socks_and_hosiery.dress_socks',751,1);
INSERT INTO `ps_etsy_categories` VALUES (755,11164,'Clothing > Unisex Adult Clothing > Socks & Hosiery > Hosiery','','clothing.unisex_adult_clothing.socks_and_hosiery.hosiery',751,1);
INSERT INTO `ps_etsy_categories` VALUES (756,466,'Clothing > Unisex Adult Clothing > Sports & Fitness','','clothing.unisex_adult_clothing.sports_and_fitness',730,1);
INSERT INTO `ps_etsy_categories` VALUES (757,475,'Clothing > Unisex Adult Clothing > Suits','','clothing.unisex_adult_clothing.suits',730,1);
INSERT INTO `ps_etsy_categories` VALUES (758,476,'Clothing > Unisex Adult Clothing > Sweaters','','clothing.unisex_adult_clothing.sweaters',730,1);
INSERT INTO `ps_etsy_categories` VALUES (759,477,'Clothing > Unisex Adult Clothing > Swimwear','','clothing.unisex_adult_clothing.swimwear',730,1);
INSERT INTO `ps_etsy_categories` VALUES (760,478,'Clothing > Unisex Adult Clothing > Tops & Tees','','clothing.unisex_adult_clothing.tops_and_tees',730,0);
INSERT INTO `ps_etsy_categories` VALUES (761,479,'Clothing > Unisex Adult Clothing > Tops & Tees > Oxfords','','clothing.unisex_adult_clothing.tops_and_tees.oxfords',760,1);
INSERT INTO `ps_etsy_categories` VALUES (762,480,'Clothing > Unisex Adult Clothing > Tops & Tees > Polos','','clothing.unisex_adult_clothing.tops_and_tees.polos',760,1);
INSERT INTO `ps_etsy_categories` VALUES (763,482,'Clothing > Unisex Adult Clothing > Tops & Tees > T-shirts','','clothing.unisex_adult_clothing.tops_and_tees.tshirts',760,0);
INSERT INTO `ps_etsy_categories` VALUES (764,11165,'Clothing > Unisex Adult Clothing > Tops & Tees > T-shirts > Graphic Tees','','clothing.unisex_adult_clothing.tops_and_tees.tshirts.graphic_tees',763,1);
INSERT INTO `ps_etsy_categories` VALUES (765,481,'Clothing > Unisex Adult Clothing > Tops & Tees > Tanks','','clothing.unisex_adult_clothing.tops_and_tees.tanks',760,0);
INSERT INTO `ps_etsy_categories` VALUES (766,11218,'Clothing > Unisex Adult Clothing > Tops & Tees > Tanks > Graphic Tanks','','clothing.unisex_adult_clothing.tops_and_tees.tanks.graphic_tanks',765,1);
INSERT INTO `ps_etsy_categories` VALUES (767,483,'Clothing > Unisex Adult Clothing > Underwear','','clothing.unisex_adult_clothing.underwear',730,1);
INSERT INTO `ps_etsy_categories` VALUES (768,2915,'Clothing > Unisex Adult Clothing > Vests','','clothing.unisex_adult_clothing.vests',730,1);
INSERT INTO `ps_etsy_categories` VALUES (769,484,'Clothing > Unisex Kids\' Clothing','','clothing.unisex_kids_clothing',535,0);
INSERT INTO `ps_etsy_categories` VALUES (770,486,'Clothing > Unisex Kids\' Clothing > Bodysuits','','clothing.unisex_kids_clothing.bodysuits',769,1);
INSERT INTO `ps_etsy_categories` VALUES (771,487,'Clothing > Unisex Kids\' Clothing > Clothing Sets','','clothing.unisex_kids_clothing.clothing_sets',769,1);
INSERT INTO `ps_etsy_categories` VALUES (772,488,'Clothing > Unisex Kids\' Clothing > Costumes','','clothing.unisex_kids_clothing.costumes',769,1);
INSERT INTO `ps_etsy_categories` VALUES (773,489,'Clothing > Unisex Kids\' Clothing > Footies & Rompers','','clothing.unisex_kids_clothing.footies_and_rompers',769,1);
INSERT INTO `ps_etsy_categories` VALUES (774,490,'Clothing > Unisex Kids\' Clothing > Hoodies & Sweatshirts','','clothing.unisex_kids_clothing.hoodies_and_sweatshirts',769,0);
INSERT INTO `ps_etsy_categories` VALUES (775,1854,'Clothing > Unisex Kids\' Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.unisex_kids_clothing.hoodies_and_sweatshirts.hoodies',774,1);
INSERT INTO `ps_etsy_categories` VALUES (776,2203,'Clothing > Unisex Kids\' Clothing > Hoodies & Sweatshirts > Sweatshirts','','clothing.unisex_kids_clothing.hoodies_and_sweatshirts.sweatshirts',774,1);
INSERT INTO `ps_etsy_categories` VALUES (777,491,'Clothing > Unisex Kids\' Clothing > Jackets & Coats','','clothing.unisex_kids_clothing.jackets_and_coats',769,1);
INSERT INTO `ps_etsy_categories` VALUES (778,11244,'Clothing > Unisex Kids\' Clothing > Jeans','','clothing.unisex_kids_clothing.jeans',769,1);
INSERT INTO `ps_etsy_categories` VALUES (779,11222,'Clothing > Unisex Kids\' Clothing > Leggings','','clothing.unisex_kids_clothing.leggings',769,1);
INSERT INTO `ps_etsy_categories` VALUES (780,11242,'Clothing > Unisex Kids\' Clothing > Overalls','','clothing.unisex_kids_clothing.overalls',769,1);
INSERT INTO `ps_etsy_categories` VALUES (781,492,'Clothing > Unisex Kids\' Clothing > Pajamas & Robes','','clothing.unisex_kids_clothing.pajamas_and_robes',769,0);
INSERT INTO `ps_etsy_categories` VALUES (782,1832,'Clothing > Unisex Kids\' Clothing > Pajamas & Robes > Pajamas','','clothing.unisex_kids_clothing.pajamas_and_robes.pajamas',781,0);
INSERT INTO `ps_etsy_categories` VALUES (783,11199,'Clothing > Unisex Kids\' Clothing > Pajamas & Robes > Pajamas > One-Piece','','clothing.unisex_kids_clothing.pajamas_and_robes.pajamas.onepiece',782,1);
INSERT INTO `ps_etsy_categories` VALUES (784,11200,'Clothing > Unisex Kids\' Clothing > Pajamas & Robes > Pajamas > Sets','','clothing.unisex_kids_clothing.pajamas_and_robes.pajamas.sets',782,1);
INSERT INTO `ps_etsy_categories` VALUES (785,11202,'Clothing > Unisex Kids\' Clothing > Pajamas & Robes > Pajamas > Sleep Shorts & Bottoms','','clothing.unisex_kids_clothing.pajamas_and_robes.pajamas.sleep_shorts_and_bottoms',782,1);
INSERT INTO `ps_etsy_categories` VALUES (786,11201,'Clothing > Unisex Kids\' Clothing > Pajamas & Robes > Pajamas > Tops','','clothing.unisex_kids_clothing.pajamas_and_robes.pajamas.tops',782,1);
INSERT INTO `ps_etsy_categories` VALUES (787,1833,'Clothing > Unisex Kids\' Clothing > Pajamas & Robes > Robes','','clothing.unisex_kids_clothing.pajamas_and_robes.robes',781,1);
INSERT INTO `ps_etsy_categories` VALUES (788,493,'Clothing > Unisex Kids\' Clothing > Pants','','clothing.unisex_kids_clothing.pants',769,1);
INSERT INTO `ps_etsy_categories` VALUES (789,2837,'Clothing > Unisex Kids\' Clothing > Ponchos','','clothing.unisex_kids_clothing.ponchos',769,1);
INSERT INTO `ps_etsy_categories` VALUES (790,494,'Clothing > Unisex Kids\' Clothing > Shorts','','clothing.unisex_kids_clothing.shorts',769,1);
INSERT INTO `ps_etsy_categories` VALUES (791,11243,'Clothing > Unisex Kids\' Clothing > Skirts & Kilts','','clothing.unisex_kids_clothing.skirts_and_kilts',769,1);
INSERT INTO `ps_etsy_categories` VALUES (792,495,'Clothing > Unisex Kids\' Clothing > Socks & Leg Warmers','','clothing.unisex_kids_clothing.socks_and_leg_warmers',769,0);
INSERT INTO `ps_etsy_categories` VALUES (793,1848,'Clothing > Unisex Kids\' Clothing > Socks & Leg Warmers > Leg Warmers','','clothing.unisex_kids_clothing.socks_and_leg_warmers.leg_warmers',792,1);
INSERT INTO `ps_etsy_categories` VALUES (794,1847,'Clothing > Unisex Kids\' Clothing > Socks & Leg Warmers > Socks','','clothing.unisex_kids_clothing.socks_and_leg_warmers.socks',792,1);
INSERT INTO `ps_etsy_categories` VALUES (795,485,'Clothing > Unisex Kids\' Clothing > Sports & Fitness','','clothing.unisex_kids_clothing.sports_and_fitness',769,1);
INSERT INTO `ps_etsy_categories` VALUES (796,496,'Clothing > Unisex Kids\' Clothing > Sweaters','','clothing.unisex_kids_clothing.sweaters',769,1);
INSERT INTO `ps_etsy_categories` VALUES (797,497,'Clothing > Unisex Kids\' Clothing > Swimwear','','clothing.unisex_kids_clothing.swimwear',769,1);
INSERT INTO `ps_etsy_categories` VALUES (798,498,'Clothing > Unisex Kids\' Clothing > Tops & Tees','','clothing.unisex_kids_clothing.tops_and_tees',769,0);
INSERT INTO `ps_etsy_categories` VALUES (799,11173,'Clothing > Unisex Kids\' Clothing > Tops & Tees > Dress Shirts & Button Downs','','clothing.unisex_kids_clothing.tops_and_tees.dress_shirts_and_button_downs',798,1);
INSERT INTO `ps_etsy_categories` VALUES (800,11169,'Clothing > Unisex Kids\' Clothing > Tops & Tees > Polos','','clothing.unisex_kids_clothing.tops_and_tees.polos',798,1);
INSERT INTO `ps_etsy_categories` VALUES (801,11170,'Clothing > Unisex Kids\' Clothing > Tops & Tees > T-shirts','','clothing.unisex_kids_clothing.tops_and_tees.tshirts',798,0);
INSERT INTO `ps_etsy_categories` VALUES (802,11171,'Clothing > Unisex Kids\' Clothing > Tops & Tees > T-shirts > Graphic Tees','','clothing.unisex_kids_clothing.tops_and_tees.tshirts.graphic_tees',801,1);
INSERT INTO `ps_etsy_categories` VALUES (803,11172,'Clothing > Unisex Kids\' Clothing > Tops & Tees > Tanks','','clothing.unisex_kids_clothing.tops_and_tees.tanks',798,0);
INSERT INTO `ps_etsy_categories` VALUES (804,11219,'Clothing > Unisex Kids\' Clothing > Tops & Tees > Tanks > Graphic Tanks','','clothing.unisex_kids_clothing.tops_and_tees.tanks.graphic_tanks',803,1);
INSERT INTO `ps_etsy_categories` VALUES (805,499,'Clothing > Unisex Kids\' Clothing > Underwear','','clothing.unisex_kids_clothing.underwear',769,1);
INSERT INTO `ps_etsy_categories` VALUES (806,2122,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing','','clothing.unisex_kids_clothing.unisex_baby_clothing',769,0);
INSERT INTO `ps_etsy_categories` VALUES (807,2123,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Bloomers, Diaper Covers & Underwear','','clothing.unisex_kids_clothing.unisex_baby_clothing.bloomers_diaper_covers_and_underwear',806,0);
INSERT INTO `ps_etsy_categories` VALUES (808,2192,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Bloomers, Diaper Covers & Underwear > Bloomers','','clothing.unisex_kids_clothing.unisex_baby_clothing.bloomers_diaper_covers_and_underwear.bloomers',807,1);
INSERT INTO `ps_etsy_categories` VALUES (809,2193,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Bloomers, Diaper Covers & Underwear > Diaper Covers','','clothing.unisex_kids_clothing.unisex_baby_clothing.bloomers_diaper_covers_and_underwear.diaper_covers',807,1);
INSERT INTO `ps_etsy_categories` VALUES (810,2194,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Bloomers, Diaper Covers & Underwear > Underwear','','clothing.unisex_kids_clothing.unisex_baby_clothing.bloomers_diaper_covers_and_underwear.underwear',807,1);
INSERT INTO `ps_etsy_categories` VALUES (811,2124,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Bodysuits','','clothing.unisex_kids_clothing.unisex_baby_clothing.bodysuits',806,1);
INSERT INTO `ps_etsy_categories` VALUES (812,2125,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Clothing Sets','','clothing.unisex_kids_clothing.unisex_baby_clothing.clothing_sets',806,1);
INSERT INTO `ps_etsy_categories` VALUES (813,2126,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Costumes','','clothing.unisex_kids_clothing.unisex_baby_clothing.costumes',806,1);
INSERT INTO `ps_etsy_categories` VALUES (814,2127,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Hoodies & Sweatshirts','','clothing.unisex_kids_clothing.unisex_baby_clothing.hoodies_and_sweatshirts',806,0);
INSERT INTO `ps_etsy_categories` VALUES (815,2128,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.unisex_kids_clothing.unisex_baby_clothing.hoodies_and_sweatshirts.hoodies',814,1);
INSERT INTO `ps_etsy_categories` VALUES (816,2129,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Pajamas & Robes','','clothing.unisex_kids_clothing.unisex_baby_clothing.pajamas_and_robes',806,0);
INSERT INTO `ps_etsy_categories` VALUES (817,2130,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Pajamas & Robes > Pajamas','','clothing.unisex_kids_clothing.unisex_baby_clothing.pajamas_and_robes.pajamas',816,1);
INSERT INTO `ps_etsy_categories` VALUES (818,2131,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Pajamas & Robes > Robes','','clothing.unisex_kids_clothing.unisex_baby_clothing.pajamas_and_robes.robes',816,1);
INSERT INTO `ps_etsy_categories` VALUES (819,2132,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Pants','','clothing.unisex_kids_clothing.unisex_baby_clothing.pants',806,1);
INSERT INTO `ps_etsy_categories` VALUES (820,2133,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Socks & Leg Warmers','','clothing.unisex_kids_clothing.unisex_baby_clothing.socks_and_leg_warmers',806,0);
INSERT INTO `ps_etsy_categories` VALUES (821,2135,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Socks & Leg Warmers > Leg Warmers','','clothing.unisex_kids_clothing.unisex_baby_clothing.socks_and_leg_warmers.leg_warmers',820,1);
INSERT INTO `ps_etsy_categories` VALUES (822,2134,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Socks & Leg Warmers > Socks','','clothing.unisex_kids_clothing.unisex_baby_clothing.socks_and_leg_warmers.socks',820,1);
INSERT INTO `ps_etsy_categories` VALUES (823,2136,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Sweaters','','clothing.unisex_kids_clothing.unisex_baby_clothing.sweaters',806,1);
INSERT INTO `ps_etsy_categories` VALUES (824,2137,'Clothing > Unisex Kids\' Clothing > Unisex Baby Clothing > Tops','','clothing.unisex_kids_clothing.unisex_baby_clothing.tops',806,1);
INSERT INTO `ps_etsy_categories` VALUES (825,11211,'Clothing > Unisex Kids\' Clothing > Vests','','clothing.unisex_kids_clothing.vests',769,1);
INSERT INTO `ps_etsy_categories` VALUES (826,500,'Clothing > Women\'s Clothing','','clothing.womens_clothing',535,0);
INSERT INTO `ps_etsy_categories` VALUES (827,11112,'Clothing > Women\'s Clothing > Blazers & Suits','','clothing.womens_clothing.blazers_and_suits',826,0);
INSERT INTO `ps_etsy_categories` VALUES (828,502,'Clothing > Women\'s Clothing > Blazers & Suits > Blazers','','clothing.womens_clothing.blazers_and_suits.blazers',827,1);
INSERT INTO `ps_etsy_categories` VALUES (829,547,'Clothing > Women\'s Clothing > Blazers & Suits > Suits','','clothing.womens_clothing.blazers_and_suits.suits',827,1);
INSERT INTO `ps_etsy_categories` VALUES (830,11113,'Clothing > Women\'s Clothing > Bodysuits & Catsuits','','clothing.womens_clothing.bodysuits_and_catsuits',826,0);
INSERT INTO `ps_etsy_categories` VALUES (831,503,'Clothing > Women\'s Clothing > Bodysuits & Catsuits > Bodysuits','','clothing.womens_clothing.bodysuits_and_catsuits.bodysuits',830,1);
INSERT INTO `ps_etsy_categories` VALUES (832,11114,'Clothing > Women\'s Clothing > Bodysuits & Catsuits > Catsuits','','clothing.womens_clothing.bodysuits_and_catsuits.catsuits',830,1);
INSERT INTO `ps_etsy_categories` VALUES (833,504,'Clothing > Women\'s Clothing > Costumes','','clothing.womens_clothing.costumes',826,1);
INSERT INTO `ps_etsy_categories` VALUES (834,505,'Clothing > Women\'s Clothing > Dresses','','clothing.womens_clothing.dresses',826,1);
INSERT INTO `ps_etsy_categories` VALUES (835,506,'Clothing > Women\'s Clothing > Hoodies & Sweatshirts','','clothing.womens_clothing.hoodies_and_sweatshirts',826,0);
INSERT INTO `ps_etsy_categories` VALUES (836,1855,'Clothing > Women\'s Clothing > Hoodies & Sweatshirts > Hoodies','','clothing.womens_clothing.hoodies_and_sweatshirts.hoodies',835,1);
INSERT INTO `ps_etsy_categories` VALUES (837,2198,'Clothing > Women\'s Clothing > Hoodies & Sweatshirts > Sweatshirts','','clothing.womens_clothing.hoodies_and_sweatshirts.sweatshirts',835,1);
INSERT INTO `ps_etsy_categories` VALUES (838,507,'Clothing > Women\'s Clothing > Jackets & Coats','','clothing.womens_clothing.jackets_and_coats',826,1);
INSERT INTO `ps_etsy_categories` VALUES (839,508,'Clothing > Women\'s Clothing > Jeans','','clothing.womens_clothing.jeans',826,1);
INSERT INTO `ps_etsy_categories` VALUES (840,509,'Clothing > Women\'s Clothing > Jumpsuits & Rompers','','clothing.womens_clothing.jumpsuits_and_rompers',826,1);
INSERT INTO `ps_etsy_categories` VALUES (841,510,'Clothing > Women\'s Clothing > Leggings','','clothing.womens_clothing.leggings',826,1);
INSERT INTO `ps_etsy_categories` VALUES (842,511,'Clothing > Women\'s Clothing > Lingerie','','clothing.womens_clothing.lingerie',826,0);
INSERT INTO `ps_etsy_categories` VALUES (843,512,'Clothing > Women\'s Clothing > Lingerie > Bloomers','','clothing.womens_clothing.lingerie.bloomers',842,1);
INSERT INTO `ps_etsy_categories` VALUES (844,514,'Clothing > Women\'s Clothing > Lingerie > Bralettes','','clothing.womens_clothing.lingerie.bralettes',842,1);
INSERT INTO `ps_etsy_categories` VALUES (845,515,'Clothing > Women\'s Clothing > Lingerie > Bras','','clothing.womens_clothing.lingerie.bras',842,1);
INSERT INTO `ps_etsy_categories` VALUES (846,513,'Clothing > Women\'s Clothing > Lingerie > Cage Bras & Harnesses','','clothing.womens_clothing.lingerie.cage_bras_and_harnesses',842,1);
INSERT INTO `ps_etsy_categories` VALUES (847,516,'Clothing > Women\'s Clothing > Lingerie > Camisoles','','clothing.womens_clothing.lingerie.camisoles',842,1);
INSERT INTO `ps_etsy_categories` VALUES (848,517,'Clothing > Women\'s Clothing > Lingerie > Corsets','','clothing.womens_clothing.lingerie.corsets',842,0);
INSERT INTO `ps_etsy_categories` VALUES (849,11203,'Clothing > Women\'s Clothing > Lingerie > Corsets > Neck Corsets','','clothing.womens_clothing.lingerie.corsets.neck_corsets',848,1);
INSERT INTO `ps_etsy_categories` VALUES (850,519,'Clothing > Women\'s Clothing > Lingerie > Lingerie Sets','','clothing.womens_clothing.lingerie.lingerie_sets',842,1);
INSERT INTO `ps_etsy_categories` VALUES (851,520,'Clothing > Women\'s Clothing > Lingerie > Panties','','clothing.womens_clothing.lingerie.panties',842,0);
INSERT INTO `ps_etsy_categories` VALUES (852,11228,'Clothing > Women\'s Clothing > Lingerie > Panties > Period Underwear','','clothing.womens_clothing.lingerie.panties.period_underwear',851,1);
INSERT INTO `ps_etsy_categories` VALUES (853,521,'Clothing > Women\'s Clothing > Lingerie > Pasties & Tassels','','clothing.womens_clothing.lingerie.pasties_and_tassels',842,1);
INSERT INTO `ps_etsy_categories` VALUES (854,522,'Clothing > Women\'s Clothing > Lingerie > Petticoats','','clothing.womens_clothing.lingerie.petticoats',842,1);
INSERT INTO `ps_etsy_categories` VALUES (855,523,'Clothing > Women\'s Clothing > Lingerie > Shapewear','','clothing.womens_clothing.lingerie.shapewear',842,1);
INSERT INTO `ps_etsy_categories` VALUES (856,524,'Clothing > Women\'s Clothing > Lingerie > Slips','','clothing.womens_clothing.lingerie.slips',842,1);
INSERT INTO `ps_etsy_categories` VALUES (857,525,'Clothing > Women\'s Clothing > Lingerie > Teddies & Babydolls','','clothing.womens_clothing.lingerie.teddies_and_babydolls',842,1);
INSERT INTO `ps_etsy_categories` VALUES (858,526,'Clothing > Women\'s Clothing > Overalls','','clothing.womens_clothing.overalls',826,1);
INSERT INTO `ps_etsy_categories` VALUES (859,527,'Clothing > Women\'s Clothing > Pajamas & Robes','','clothing.womens_clothing.pajamas_and_robes',826,0);
INSERT INTO `ps_etsy_categories` VALUES (860,528,'Clothing > Women\'s Clothing > Pajamas & Robes > Hospital Gowns','','clothing.womens_clothing.pajamas_and_robes.hospital_gowns',859,1);
INSERT INTO `ps_etsy_categories` VALUES (861,529,'Clothing > Women\'s Clothing > Pajamas & Robes > Night Gowns & Tops','','clothing.womens_clothing.pajamas_and_robes.night_gowns_and_tops',859,1);
INSERT INTO `ps_etsy_categories` VALUES (862,530,'Clothing > Women\'s Clothing > Pajamas & Robes > Robes','','clothing.womens_clothing.pajamas_and_robes.robes',859,1);
INSERT INTO `ps_etsy_categories` VALUES (863,11175,'Clothing > Women\'s Clothing > Pajamas & Robes > Rompers & One-Piece','','clothing.womens_clothing.pajamas_and_robes.rompers_and_onepiece',859,1);
INSERT INTO `ps_etsy_categories` VALUES (864,531,'Clothing > Women\'s Clothing > Pajamas & Robes > Sets','','clothing.womens_clothing.pajamas_and_robes.sets',859,1);
INSERT INTO `ps_etsy_categories` VALUES (865,532,'Clothing > Women\'s Clothing > Pajamas & Robes > Sleep Masks & Blindfolds','','clothing.womens_clothing.pajamas_and_robes.sleep_masks_and_blindfolds',859,0);
INSERT INTO `ps_etsy_categories` VALUES (866,2101,'Clothing > Women\'s Clothing > Pajamas & Robes > Sleep Masks & Blindfolds > Blindfolds','','clothing.womens_clothing.pajamas_and_robes.sleep_masks_and_blindfolds.blindfolds',865,1);
INSERT INTO `ps_etsy_categories` VALUES (867,1834,'Clothing > Women\'s Clothing > Pajamas & Robes > Sleep Masks & Blindfolds > Sleep Masks','','clothing.womens_clothing.pajamas_and_robes.sleep_masks_and_blindfolds.sleep_masks',865,1);
INSERT INTO `ps_etsy_categories` VALUES (868,533,'Clothing > Women\'s Clothing > Pajamas & Robes > Sleep Shorts & Bottoms','','clothing.womens_clothing.pajamas_and_robes.sleep_shorts_and_bottoms',859,0);
INSERT INTO `ps_etsy_categories` VALUES (869,11177,'Clothing > Women\'s Clothing > Pajamas & Robes > Sleep Shorts & Bottoms > Capris','','clothing.womens_clothing.pajamas_and_robes.sleep_shorts_and_bottoms.capris',868,1);
INSERT INTO `ps_etsy_categories` VALUES (870,11178,'Clothing > Women\'s Clothing > Pajamas & Robes > Sleep Shorts & Bottoms > Pants','','clothing.womens_clothing.pajamas_and_robes.sleep_shorts_and_bottoms.pants',868,1);
INSERT INTO `ps_etsy_categories` VALUES (871,11176,'Clothing > Women\'s Clothing > Pajamas & Robes > Sleep Shorts & Bottoms > Shorts','','clothing.womens_clothing.pajamas_and_robes.sleep_shorts_and_bottoms.shorts',868,1);
INSERT INTO `ps_etsy_categories` VALUES (872,534,'Clothing > Women\'s Clothing > Pants & Capris','','clothing.womens_clothing.pants_and_capris',826,0);
INSERT INTO `ps_etsy_categories` VALUES (873,1836,'Clothing > Women\'s Clothing > Pants & Capris > Capris','','clothing.womens_clothing.pants_and_capris.capris',872,1);
INSERT INTO `ps_etsy_categories` VALUES (874,1835,'Clothing > Women\'s Clothing > Pants & Capris > Pants','','clothing.womens_clothing.pants_and_capris.pants',872,1);
INSERT INTO `ps_etsy_categories` VALUES (875,2835,'Clothing > Women\'s Clothing > Ponchos','','clothing.womens_clothing.ponchos',826,1);
INSERT INTO `ps_etsy_categories` VALUES (876,535,'Clothing > Women\'s Clothing > Shorts & Skorts','','clothing.womens_clothing.shorts_and_skorts',826,0);
INSERT INTO `ps_etsy_categories` VALUES (877,1837,'Clothing > Women\'s Clothing > Shorts & Skorts > Shorts','','clothing.womens_clothing.shorts_and_skorts.shorts',876,1);
INSERT INTO `ps_etsy_categories` VALUES (878,1838,'Clothing > Women\'s Clothing > Shorts & Skorts > Skorts','','clothing.womens_clothing.shorts_and_skorts.skorts',876,1);
INSERT INTO `ps_etsy_categories` VALUES (879,536,'Clothing > Women\'s Clothing > Skirts','','clothing.womens_clothing.skirts',826,1);
INSERT INTO `ps_etsy_categories` VALUES (880,537,'Clothing > Women\'s Clothing > Socks & Hosiery','','clothing.womens_clothing.socks_and_hosiery',826,0);
INSERT INTO `ps_etsy_categories` VALUES (881,538,'Clothing > Women\'s Clothing > Socks & Hosiery > Athletic Socks','','clothing.womens_clothing.socks_and_hosiery.athletic_socks',880,1);
INSERT INTO `ps_etsy_categories` VALUES (882,539,'Clothing > Women\'s Clothing > Socks & Hosiery > Boot Socks & Cuffs','','clothing.womens_clothing.socks_and_hosiery.boot_socks_and_cuffs',880,0);
INSERT INTO `ps_etsy_categories` VALUES (883,11117,'Clothing > Women\'s Clothing > Socks & Hosiery > Boot Socks & Cuffs > Boot Cuffs','','clothing.womens_clothing.socks_and_hosiery.boot_socks_and_cuffs.boot_cuffs',882,1);
INSERT INTO `ps_etsy_categories` VALUES (884,11116,'Clothing > Women\'s Clothing > Socks & Hosiery > Boot Socks & Cuffs > Boot Socks','','clothing.womens_clothing.socks_and_hosiery.boot_socks_and_cuffs.boot_socks',882,1);
INSERT INTO `ps_etsy_categories` VALUES (885,540,'Clothing > Women\'s Clothing > Socks & Hosiery > Casual Socks','','clothing.womens_clothing.socks_and_hosiery.casual_socks',880,1);
INSERT INTO `ps_etsy_categories` VALUES (886,541,'Clothing > Women\'s Clothing > Socks & Hosiery > Dress Socks','','clothing.womens_clothing.socks_and_hosiery.dress_socks',880,1);
INSERT INTO `ps_etsy_categories` VALUES (887,542,'Clothing > Women\'s Clothing > Socks & Hosiery > Garter Belts','','clothing.womens_clothing.socks_and_hosiery.garter_belts',880,1);
INSERT INTO `ps_etsy_categories` VALUES (888,543,'Clothing > Women\'s Clothing > Socks & Hosiery > Garters','','clothing.womens_clothing.socks_and_hosiery.garters',880,1);
INSERT INTO `ps_etsy_categories` VALUES (889,544,'Clothing > Women\'s Clothing > Socks & Hosiery > Hosiery','','clothing.womens_clothing.socks_and_hosiery.hosiery',880,1);
INSERT INTO `ps_etsy_categories` VALUES (890,545,'Clothing > Women\'s Clothing > Socks & Hosiery > Leg Warmers','','clothing.womens_clothing.socks_and_hosiery.leg_warmers',880,1);
INSERT INTO `ps_etsy_categories` VALUES (891,546,'Clothing > Women\'s Clothing > Socks & Hosiery > Tights','','clothing.womens_clothing.socks_and_hosiery.tights',880,1);
INSERT INTO `ps_etsy_categories` VALUES (892,501,'Clothing > Women\'s Clothing > Sports & Fitness','','clothing.womens_clothing.sports_and_fitness',826,1);
INSERT INTO `ps_etsy_categories` VALUES (893,548,'Clothing > Women\'s Clothing > Sweaters','','clothing.womens_clothing.sweaters',826,0);
INSERT INTO `ps_etsy_categories` VALUES (894,549,'Clothing > Women\'s Clothing > Sweaters > Cardigans','','clothing.womens_clothing.sweaters.cardigans',893,1);
INSERT INTO `ps_etsy_categories` VALUES (895,550,'Clothing > Women\'s Clothing > Sweaters > Pullover Sweaters','','clothing.womens_clothing.sweaters.pullover_sweaters',893,1);
INSERT INTO `ps_etsy_categories` VALUES (896,551,'Clothing > Women\'s Clothing > Sweaters > Sweater Vests','','clothing.womens_clothing.sweaters.sweater_vests',893,1);
INSERT INTO `ps_etsy_categories` VALUES (897,552,'Clothing > Women\'s Clothing > Swimwear','','clothing.womens_clothing.swimwear',826,0);
INSERT INTO `ps_etsy_categories` VALUES (898,11118,'Clothing > Women\'s Clothing > Swimwear > Bikinis & Sets','','clothing.womens_clothing.swimwear.bikinis_and_sets',897,1);
INSERT INTO `ps_etsy_categories` VALUES (899,11120,'Clothing > Women\'s Clothing > Swimwear > Bottoms','','clothing.womens_clothing.swimwear.bottoms',897,1);
INSERT INTO `ps_etsy_categories` VALUES (900,11124,'Clothing > Women\'s Clothing > Swimwear > Cover-Ups','','clothing.womens_clothing.swimwear.coverups',897,1);
INSERT INTO `ps_etsy_categories` VALUES (901,11121,'Clothing > Women\'s Clothing > Swimwear > One-Piece','','clothing.womens_clothing.swimwear.onepiece',897,1);
INSERT INTO `ps_etsy_categories` VALUES (902,11122,'Clothing > Women\'s Clothing > Swimwear > Rash Guards','','clothing.womens_clothing.swimwear.rash_guards',897,1);
INSERT INTO `ps_etsy_categories` VALUES (903,11123,'Clothing > Women\'s Clothing > Swimwear > Swim Caps','','clothing.womens_clothing.swimwear.swim_caps',897,1);
INSERT INTO `ps_etsy_categories` VALUES (904,11119,'Clothing > Women\'s Clothing > Swimwear > Tops','','clothing.womens_clothing.swimwear.tops',897,1);
INSERT INTO `ps_etsy_categories` VALUES (905,553,'Clothing > Women\'s Clothing > Tops & Tees','','clothing.womens_clothing.tops_and_tees',826,0);
INSERT INTO `ps_etsy_categories` VALUES (906,554,'Clothing > Women\'s Clothing > Tops & Tees > Blouses','','clothing.womens_clothing.tops_and_tees.blouses',905,1);
INSERT INTO `ps_etsy_categories` VALUES (907,555,'Clothing > Women\'s Clothing > Tops & Tees > Crop & Tube Tops','','clothing.womens_clothing.tops_and_tees.crop_and_tube_tops',905,0);
INSERT INTO `ps_etsy_categories` VALUES (908,1839,'Clothing > Women\'s Clothing > Tops & Tees > Crop & Tube Tops > Crop Tops','','clothing.womens_clothing.tops_and_tees.crop_and_tube_tops.crop_tops',907,1);
INSERT INTO `ps_etsy_categories` VALUES (909,1840,'Clothing > Women\'s Clothing > Tops & Tees > Crop & Tube Tops > Tube Tops','','clothing.womens_clothing.tops_and_tees.crop_and_tube_tops.tube_tops',907,1);
INSERT INTO `ps_etsy_categories` VALUES (910,556,'Clothing > Women\'s Clothing > Tops & Tees > Halter Tops','','clothing.womens_clothing.tops_and_tees.halter_tops',905,1);
INSERT INTO `ps_etsy_categories` VALUES (911,557,'Clothing > Women\'s Clothing > Tops & Tees > Polos','','clothing.womens_clothing.tops_and_tees.polos',905,1);
INSERT INTO `ps_etsy_categories` VALUES (912,2904,'Clothing > Women\'s Clothing > Tops & Tees > Shrugs & Boleros','','clothing.womens_clothing.tops_and_tees.shrugs_and_boleros',905,1);
INSERT INTO `ps_etsy_categories` VALUES (913,559,'Clothing > Women\'s Clothing > Tops & Tees > T-shirts','','clothing.womens_clothing.tops_and_tees.tshirts',905,0);
INSERT INTO `ps_etsy_categories` VALUES (914,11115,'Clothing > Women\'s Clothing > Tops & Tees > T-shirts > Graphic Tees','','clothing.womens_clothing.tops_and_tees.tshirts.graphic_tees',913,1);
INSERT INTO `ps_etsy_categories` VALUES (915,558,'Clothing > Women\'s Clothing > Tops & Tees > Tanks','','clothing.womens_clothing.tops_and_tees.tanks',905,0);
INSERT INTO `ps_etsy_categories` VALUES (916,11214,'Clothing > Women\'s Clothing > Tops & Tees > Tanks > Graphic Tanks','','clothing.womens_clothing.tops_and_tees.tanks.graphic_tanks',915,1);
INSERT INTO `ps_etsy_categories` VALUES (917,560,'Clothing > Women\'s Clothing > Tops & Tees > Tunics','','clothing.womens_clothing.tops_and_tees.tunics',905,1);
INSERT INTO `ps_etsy_categories` VALUES (918,561,'Clothing > Women\'s Clothing > Vests','','clothing.womens_clothing.vests',826,1);
INSERT INTO `ps_etsy_categories` VALUES (919,562,'Craft Supplies & Tools','','craft_supplies_and_tools',0,0);
INSERT INTO `ps_etsy_categories` VALUES (920,9205,'Craft Supplies & Tools > Beads, Gems & Cabochons','','craft_supplies_and_tools.beads_gems_and_cabochons',919,0);
INSERT INTO `ps_etsy_categories` VALUES (921,6238,'Craft Supplies & Tools > Beads, Gems & Cabochons > Beads','','craft_supplies_and_tools.beads_gems_and_cabochons.beads',920,1);
INSERT INTO `ps_etsy_categories` VALUES (922,6240,'Craft Supplies & Tools > Beads, Gems & Cabochons > Cabochons','','craft_supplies_and_tools.beads_gems_and_cabochons.cabochons',920,1);
INSERT INTO `ps_etsy_categories` VALUES (923,6239,'Craft Supplies & Tools > Beads, Gems & Cabochons > Charms & Pendants','','craft_supplies_and_tools.beads_gems_and_cabochons.charms_and_pendants',920,0);
INSERT INTO `ps_etsy_categories` VALUES (924,6426,'Craft Supplies & Tools > Beads, Gems & Cabochons > Charms & Pendants > Charms','','craft_supplies_and_tools.beads_gems_and_cabochons.charms_and_pendants.charms',923,1);
INSERT INTO `ps_etsy_categories` VALUES (925,6427,'Craft Supplies & Tools > Beads, Gems & Cabochons > Charms & Pendants > Pendants','','craft_supplies_and_tools.beads_gems_and_cabochons.charms_and_pendants.pendants',923,1);
INSERT INTO `ps_etsy_categories` VALUES (926,6648,'Craft Supplies & Tools > Beads, Gems & Cabochons > Gemstones','','craft_supplies_and_tools.beads_gems_and_cabochons.gemstones',920,1);
INSERT INTO `ps_etsy_categories` VALUES (927,9124,'Craft Supplies & Tools > Beauty Supplies','','craft_supplies_and_tools.beauty_supplies',919,0);
INSERT INTO `ps_etsy_categories` VALUES (928,6222,'Craft Supplies & Tools > Beauty Supplies > Fragrances','','craft_supplies_and_tools.beauty_supplies.fragrances',927,1);
INSERT INTO `ps_etsy_categories` VALUES (929,9126,'Craft Supplies & Tools > Beauty Supplies > Hair Extensions','','craft_supplies_and_tools.beauty_supplies.hair_extensions',927,1);
INSERT INTO `ps_etsy_categories` VALUES (930,6247,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies',927,0);
INSERT INTO `ps_etsy_categories` VALUES (931,6456,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Acrylic & Press On Nails','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.acrylic_and_press_on_nails',930,1);
INSERT INTO `ps_etsy_categories` VALUES (932,9138,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Liquid Nail Tape','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.liquid_nail_tape',930,1);
INSERT INTO `ps_etsy_categories` VALUES (933,9134,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Art Accessories','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_art_accessories',930,0);
INSERT INTO `ps_etsy_categories` VALUES (934,6460,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Art Accessories > Nail Charms','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_art_accessories.nail_charms',933,1);
INSERT INTO `ps_etsy_categories` VALUES (935,6461,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Art Accessories > Nail Decals','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_art_accessories.nail_decals',933,1);
INSERT INTO `ps_etsy_categories` VALUES (936,6462,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Art Accessories > Nail Stamping','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_art_accessories.nail_stamping',933,1);
INSERT INTO `ps_etsy_categories` VALUES (937,9136,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Art Accessories > Nail Stencils','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_art_accessories.nail_stencils',933,1);
INSERT INTO `ps_etsy_categories` VALUES (938,6463,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Art Accessories > Nail Wraps','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_art_accessories.nail_wraps',933,1);
INSERT INTO `ps_etsy_categories` VALUES (939,9135,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Art Accessories > Striping Tape','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_art_accessories.striping_tape',933,1);
INSERT INTO `ps_etsy_categories` VALUES (940,9137,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Glue','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_glue',930,1);
INSERT INTO `ps_etsy_categories` VALUES (941,6457,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Polishes','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_polishes',930,1);
INSERT INTO `ps_etsy_categories` VALUES (942,6458,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Tools & Brushes','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_tools_and_brushes',930,0);
INSERT INTO `ps_etsy_categories` VALUES (943,6815,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Tools & Brushes > Clippers & Scissors','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_tools_and_brushes.clippers_and_scissors',942,1);
INSERT INTO `ps_etsy_categories` VALUES (944,9133,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Tools & Brushes > Cuticle Pushers','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_tools_and_brushes.cuticle_pushers',942,1);
INSERT INTO `ps_etsy_categories` VALUES (945,6816,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Tools & Brushes > Dotting Tools','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_tools_and_brushes.dotting_tools',942,1);
INSERT INTO `ps_etsy_categories` VALUES (946,9131,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Tools & Brushes > Nail Art Brushes','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_tools_and_brushes.nail_art_brushes',942,1);
INSERT INTO `ps_etsy_categories` VALUES (947,9132,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Tools & Brushes > Nail Files','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_tools_and_brushes.nail_files',942,1);
INSERT INTO `ps_etsy_categories` VALUES (948,6459,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Nail Treatments','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.nail_treatments',930,1);
INSERT INTO `ps_etsy_categories` VALUES (949,6817,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Polish Removal','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.polish_removal',930,1);
INSERT INTO `ps_etsy_categories` VALUES (950,9174,'Craft Supplies & Tools > Beauty Supplies > Nail Art Supplies > Top & Base Coats','','craft_supplies_and_tools.beauty_supplies.nail_art_supplies.top_and_base_coats',930,1);
INSERT INTO `ps_etsy_categories` VALUES (951,9125,'Craft Supplies & Tools > Beauty Supplies > Oils & Butters','','craft_supplies_and_tools.beauty_supplies.oils_and_butters',927,0);
INSERT INTO `ps_etsy_categories` VALUES (952,6524,'Craft Supplies & Tools > Beauty Supplies > Oils & Butters > Butters','','craft_supplies_and_tools.beauty_supplies.oils_and_butters.butters',951,1);
INSERT INTO `ps_etsy_categories` VALUES (953,6516,'Craft Supplies & Tools > Beauty Supplies > Oils & Butters > Oils','','craft_supplies_and_tools.beauty_supplies.oils_and_butters.oils',951,1);
INSERT INTO `ps_etsy_categories` VALUES (954,9127,'Craft Supplies & Tools > Beauty Supplies > Soap Supplies','','craft_supplies_and_tools.beauty_supplies.soap_supplies',927,0);
INSERT INTO `ps_etsy_categories` VALUES (955,9128,'Craft Supplies & Tools > Beauty Supplies > Soap Supplies > Felting Soap','','craft_supplies_and_tools.beauty_supplies.soap_supplies.felting_soap',954,1);
INSERT INTO `ps_etsy_categories` VALUES (956,9130,'Craft Supplies & Tools > Beauty Supplies > Soap Supplies > Soap Bases','','craft_supplies_and_tools.beauty_supplies.soap_supplies.soap_bases',954,1);
INSERT INTO `ps_etsy_categories` VALUES (957,9129,'Craft Supplies & Tools > Beauty Supplies > Soap Supplies > Soap Embeds','','craft_supplies_and_tools.beauty_supplies.soap_supplies.soap_embeds',954,1);
INSERT INTO `ps_etsy_categories` VALUES (958,6242,'Craft Supplies & Tools > Blanks','','craft_supplies_and_tools.blanks',919,0);
INSERT INTO `ps_etsy_categories` VALUES (959,6446,'Craft Supplies & Tools > Blanks > Figurines','','craft_supplies_and_tools.blanks.figurines',958,0);
INSERT INTO `ps_etsy_categories` VALUES (960,6782,'Craft Supplies & Tools > Blanks > Figurines > Animal & Natural Shapes','','craft_supplies_and_tools.blanks.figurines.animal_and_natural_shapes',959,1);
INSERT INTO `ps_etsy_categories` VALUES (961,6784,'Craft Supplies & Tools > Blanks > Figurines > Manikins & Artist Figures','','craft_supplies_and_tools.blanks.figurines.manikins_and_artist_figures',959,1);
INSERT INTO `ps_etsy_categories` VALUES (962,6785,'Craft Supplies & Tools > Blanks > Figurines > Monsters & Fantasy','','craft_supplies_and_tools.blanks.figurines.monsters_and_fantasy',959,1);
INSERT INTO `ps_etsy_categories` VALUES (963,6783,'Craft Supplies & Tools > Blanks > Figurines > People Shapes','','craft_supplies_and_tools.blanks.figurines.people_shapes',959,1);
INSERT INTO `ps_etsy_categories` VALUES (964,6445,'Craft Supplies & Tools > Blanks > Forms & Shapes','','craft_supplies_and_tools.blanks.forms_and_shapes',958,1);
INSERT INTO `ps_etsy_categories` VALUES (965,6762,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks','','craft_supplies_and_tools.blanks.hat_and_hair_blanks',958,0);
INSERT INTO `ps_etsy_categories` VALUES (966,7019,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Barrettes','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.barrettes',965,1);
INSERT INTO `ps_etsy_categories` VALUES (967,9196,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Brims & Visors','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.brims_and_visors',965,1);
INSERT INTO `ps_etsy_categories` VALUES (968,7021,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Combs','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.combs',965,1);
INSERT INTO `ps_etsy_categories` VALUES (969,7020,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Hair Clips','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.hair_clips',965,1);
INSERT INTO `ps_etsy_categories` VALUES (970,7023,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Hair Pins','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.hair_pins',965,1);
INSERT INTO `ps_etsy_categories` VALUES (971,7024,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Hair Sticks','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.hair_sticks',965,1);
INSERT INTO `ps_etsy_categories` VALUES (972,7025,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Hair Tie Blanks','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.hair_tie_blanks',965,1);
INSERT INTO `ps_etsy_categories` VALUES (973,7069,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Hat Bases & Bodies','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.hat_bases_and_bodies',965,1);
INSERT INTO `ps_etsy_categories` VALUES (974,7022,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Headband Blanks','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.headband_blanks',965,1);
INSERT INTO `ps_etsy_categories` VALUES (975,9195,'Craft Supplies & Tools > Blanks > Hat & Hair Blanks > Masks','','craft_supplies_and_tools.blanks.hat_and_hair_blanks.masks',965,1);
INSERT INTO `ps_etsy_categories` VALUES (976,6444,'Craft Supplies & Tools > Blanks > Jewelry','','craft_supplies_and_tools.blanks.jewelry',958,0);
INSERT INTO `ps_etsy_categories` VALUES (977,9181,'Craft Supplies & Tools > Blanks > Jewelry > Bead Blanks','','craft_supplies_and_tools.blanks.jewelry.bead_blanks',976,1);
INSERT INTO `ps_etsy_categories` VALUES (978,6770,'Craft Supplies & Tools > Blanks > Jewelry > Belt Buckle Blanks','','craft_supplies_and_tools.blanks.jewelry.belt_buckle_blanks',976,1);
INSERT INTO `ps_etsy_categories` VALUES (979,9188,'Craft Supplies & Tools > Blanks > Jewelry > Blank Key Rings','','craft_supplies_and_tools.blanks.jewelry.blank_key_rings',976,1);
INSERT INTO `ps_etsy_categories` VALUES (980,6771,'Craft Supplies & Tools > Blanks > Jewelry > Bolo & Concho Blanks','','craft_supplies_and_tools.blanks.jewelry.bolo_and_concho_blanks',976,1);
INSERT INTO `ps_etsy_categories` VALUES (981,6765,'Craft Supplies & Tools > Blanks > Jewelry > Bracelets','','craft_supplies_and_tools.blanks.jewelry.bracelets',976,1);
INSERT INTO `ps_etsy_categories` VALUES (982,6769,'Craft Supplies & Tools > Blanks > Jewelry > Brooch & Pin','','craft_supplies_and_tools.blanks.jewelry.brooch_and_pin',976,0);
INSERT INTO `ps_etsy_categories` VALUES (983,7027,'Craft Supplies & Tools > Blanks > Jewelry > Brooch & Pin > Blanks','','craft_supplies_and_tools.blanks.jewelry.brooch_and_pin.blanks',982,1);
INSERT INTO `ps_etsy_categories` VALUES (984,7028,'Craft Supplies & Tools > Blanks > Jewelry > Brooch & Pin > Catches','','craft_supplies_and_tools.blanks.jewelry.brooch_and_pin.catches',982,1);
INSERT INTO `ps_etsy_categories` VALUES (985,7026,'Craft Supplies & Tools > Blanks > Jewelry > Brooch & Pin > Pin Backs','','craft_supplies_and_tools.blanks.jewelry.brooch_and_pin.pin_backs',982,1);
INSERT INTO `ps_etsy_categories` VALUES (986,6763,'Craft Supplies & Tools > Blanks > Jewelry > Cuff Links','','craft_supplies_and_tools.blanks.jewelry.cuff_links',976,1);
INSERT INTO `ps_etsy_categories` VALUES (987,6764,'Craft Supplies & Tools > Blanks > Jewelry > Earrings','','craft_supplies_and_tools.blanks.jewelry.earrings',976,1);
INSERT INTO `ps_etsy_categories` VALUES (988,6767,'Craft Supplies & Tools > Blanks > Jewelry > Pendants','','craft_supplies_and_tools.blanks.jewelry.pendants',976,1);
INSERT INTO `ps_etsy_categories` VALUES (989,6766,'Craft Supplies & Tools > Blanks > Jewelry > Rings','','craft_supplies_and_tools.blanks.jewelry.rings',976,1);
INSERT INTO `ps_etsy_categories` VALUES (990,6772,'Craft Supplies & Tools > Blanks > Jewelry > Tags & Stamping Blanks','','craft_supplies_and_tools.blanks.jewelry.tags_and_stamping_blanks',976,1);
INSERT INTO `ps_etsy_categories` VALUES (991,6768,'Craft Supplies & Tools > Blanks > Jewelry > Tie Bars & Tacks','','craft_supplies_and_tools.blanks.jewelry.tie_bars_and_tacks',976,1);
INSERT INTO `ps_etsy_categories` VALUES (992,6216,'Craft Supplies & Tools > Brushes','','craft_supplies_and_tools.brushes',919,0);
INSERT INTO `ps_etsy_categories` VALUES (993,6250,'Craft Supplies & Tools > Brushes > Airbrushes','','craft_supplies_and_tools.brushes.airbrushes',992,1);
INSERT INTO `ps_etsy_categories` VALUES (994,6251,'Craft Supplies & Tools > Brushes > Makeup Brushes','','craft_supplies_and_tools.brushes.makeup_brushes',992,1);
INSERT INTO `ps_etsy_categories` VALUES (995,6248,'Craft Supplies & Tools > Brushes > Paint Brushes','','craft_supplies_and_tools.brushes.paint_brushes',992,1);
INSERT INTO `ps_etsy_categories` VALUES (996,6249,'Craft Supplies & Tools > Brushes > Paint Rollers','','craft_supplies_and_tools.brushes.paint_rollers',992,1);
INSERT INTO `ps_etsy_categories` VALUES (997,6217,'Craft Supplies & Tools > Canvas & Surfaces','','craft_supplies_and_tools.canvas_and_surfaces',919,0);
INSERT INTO `ps_etsy_categories` VALUES (998,6252,'Craft Supplies & Tools > Canvas & Surfaces > Canvas','','craft_supplies_and_tools.canvas_and_surfaces.canvas',997,0);
INSERT INTO `ps_etsy_categories` VALUES (999,6468,'Craft Supplies & Tools > Canvas & Surfaces > Canvas > Aida Canvas','','craft_supplies_and_tools.canvas_and_surfaces.canvas.aida_canvas',998,1);
INSERT INTO `ps_etsy_categories` VALUES (1000,6467,'Craft Supplies & Tools > Canvas & Surfaces > Canvas > Plastic Canvas','','craft_supplies_and_tools.canvas_and_surfaces.canvas.plastic_canvas',998,1);
INSERT INTO `ps_etsy_categories` VALUES (1001,6466,'Craft Supplies & Tools > Canvas & Surfaces > Canvas > Rug Canvas','','craft_supplies_and_tools.canvas_and_surfaces.canvas.rug_canvas',998,1);
INSERT INTO `ps_etsy_categories` VALUES (1002,6464,'Craft Supplies & Tools > Canvas & Surfaces > Canvas > Stretched Canvas','','craft_supplies_and_tools.canvas_and_surfaces.canvas.stretched_canvas',998,1);
INSERT INTO `ps_etsy_categories` VALUES (1003,6465,'Craft Supplies & Tools > Canvas & Surfaces > Canvas > Unstretched Canvas','','craft_supplies_and_tools.canvas_and_surfaces.canvas.unstretched_canvas',998,0);
INSERT INTO `ps_etsy_categories` VALUES (1004,6818,'Craft Supplies & Tools > Canvas & Surfaces > Canvas > Unstretched Canvas > Canvas Rolls','','craft_supplies_and_tools.canvas_and_surfaces.canvas.unstretched_canvas.canvas_rolls',1003,1);
INSERT INTO `ps_etsy_categories` VALUES (1005,6597,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums',997,0);
INSERT INTO `ps_etsy_categories` VALUES (1006,6838,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Albums','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.albums',1005,0);
INSERT INTO `ps_etsy_categories` VALUES (1007,7048,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Albums > Album Refills','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.albums.album_refills',1006,1);
INSERT INTO `ps_etsy_categories` VALUES (1008,7047,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Albums > Autograph Books','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.albums.autograph_books',1006,1);
INSERT INTO `ps_etsy_categories` VALUES (1009,7046,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Albums > Baby Books','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.albums.baby_books',1006,1);
INSERT INTO `ps_etsy_categories` VALUES (1010,7049,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Albums > Page Protectors','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.albums.page_protectors',1006,1);
INSERT INTO `ps_etsy_categories` VALUES (1011,7045,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Albums > Photo Albums','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.albums.photo_albums',1006,1);
INSERT INTO `ps_etsy_categories` VALUES (1012,6840,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Binders & Covers','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.binders_and_covers',1005,1);
INSERT INTO `ps_etsy_categories` VALUES (1013,6743,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Folders','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.folders',1005,1);
INSERT INTO `ps_etsy_categories` VALUES (1014,6841,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Journaling Spots','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.journaling_spots',1005,1);
INSERT INTO `ps_etsy_categories` VALUES (1015,6837,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Journals','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.journals',1005,1);
INSERT INTO `ps_etsy_categories` VALUES (1016,6839,'Craft Supplies & Tools > Canvas & Surfaces > Journals & Albums > Sketchbooks','','craft_supplies_and_tools.canvas_and_surfaces.journals_and_albums.sketchbooks',1005,1);
INSERT INTO `ps_etsy_categories` VALUES (1017,6364,'Craft Supplies & Tools > Canvas & Surfaces > Linoleum','','craft_supplies_and_tools.canvas_and_surfaces.linoleum',997,1);
INSERT INTO `ps_etsy_categories` VALUES (1018,6253,'Craft Supplies & Tools > Canvas & Surfaces > Panels & Art Boards','','craft_supplies_and_tools.canvas_and_surfaces.panels_and_art_boards',997,1);
INSERT INTO `ps_etsy_categories` VALUES (1019,6338,'Craft Supplies & Tools > Canvas & Surfaces > Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper',997,0);
INSERT INTO `ps_etsy_categories` VALUES (1020,6595,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Blank Cards','','craft_supplies_and_tools.canvas_and_surfaces.paper.blank_cards',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1021,6587,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Bookboard','','craft_supplies_and_tools.canvas_and_surfaces.paper.bookboard',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1022,6578,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Card Stock','','craft_supplies_and_tools.canvas_and_surfaces.paper.card_stock',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1023,6592,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Collage Sheets','','craft_supplies_and_tools.canvas_and_surfaces.paper.collage_sheets',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1024,6574,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Construction Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.construction_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1025,6586,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Decorative Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.decorative_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1026,6594,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Die Cuts','','craft_supplies_and_tools.canvas_and_surfaces.paper.die_cuts',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1027,6576,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Drawing Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.drawing_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1028,6582,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Kraft Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.kraft_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1029,6593,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Mixed Paper Sets','','craft_supplies_and_tools.canvas_and_surfaces.paper.mixed_paper_sets',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1030,6577,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Origami Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.origami_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1031,6580,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Parchment Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.parchment_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1032,6589,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Photo & Imaging Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.photo_and_imaging_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1033,6590,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Poster Board','','craft_supplies_and_tools.canvas_and_surfaces.paper.poster_board',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1034,6591,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Premade Pages','','craft_supplies_and_tools.canvas_and_surfaces.paper.premade_pages',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1035,6581,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Printer Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.printer_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1036,6596,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Recipe Cards','','craft_supplies_and_tools.canvas_and_surfaces.paper.recipe_cards',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1037,6585,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Scrapbooking Ephemera','','craft_supplies_and_tools.canvas_and_surfaces.paper.scrapbooking_ephemera',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1038,6588,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Text Blocks','','craft_supplies_and_tools.canvas_and_surfaces.paper.text_blocks',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1039,6575,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Tissue Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.tissue_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1040,6583,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Tracing Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.tracing_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1041,6584,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Transfer Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.transfer_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1042,6579,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Washi & Rice Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.washi_and_rice_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1043,9182,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Wax Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.wax_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1044,6744,'Craft Supplies & Tools > Canvas & Surfaces > Paper > Writing Paper','','craft_supplies_and_tools.canvas_and_surfaces.paper.writing_paper',1019,1);
INSERT INTO `ps_etsy_categories` VALUES (1045,6256,'Craft Supplies & Tools > Canvas & Surfaces > Printing Plates','','craft_supplies_and_tools.canvas_and_surfaces.printing_plates',997,1);
INSERT INTO `ps_etsy_categories` VALUES (1046,6255,'Craft Supplies & Tools > Canvas & Surfaces > Screens & Mesh','','craft_supplies_and_tools.canvas_and_surfaces.screens_and_mesh',997,1);
INSERT INTO `ps_etsy_categories` VALUES (1047,6598,'Craft Supplies & Tools > Canvas & Surfaces > Stencils, Templates & Transfers','','craft_supplies_and_tools.canvas_and_surfaces.stencils_templates_and_transfers',997,0);
INSERT INTO `ps_etsy_categories` VALUES (1048,6844,'Craft Supplies & Tools > Canvas & Surfaces > Stencils, Templates & Transfers > Clip Art','','craft_supplies_and_tools.canvas_and_surfaces.stencils_templates_and_transfers.clip_art',1047,1);
INSERT INTO `ps_etsy_categories` VALUES (1049,6617,'Craft Supplies & Tools > Canvas & Surfaces > Stencils, Templates & Transfers > Image Transfers','','craft_supplies_and_tools.canvas_and_surfaces.stencils_templates_and_transfers.image_transfers',1047,1);
INSERT INTO `ps_etsy_categories` VALUES (1050,6842,'Craft Supplies & Tools > Canvas & Surfaces > Stencils, Templates & Transfers > Stencils','','craft_supplies_and_tools.canvas_and_surfaces.stencils_templates_and_transfers.stencils',1047,1);
INSERT INTO `ps_etsy_categories` VALUES (1051,6843,'Craft Supplies & Tools > Canvas & Surfaces > Stencils, Templates & Transfers > Templates','','craft_supplies_and_tools.canvas_and_surfaces.stencils_templates_and_transfers.templates',1047,1);
INSERT INTO `ps_etsy_categories` VALUES (1052,9204,'Craft Supplies & Tools > Canvas & Surfaces > Tile','','craft_supplies_and_tools.canvas_and_surfaces.tile',997,1);
INSERT INTO `ps_etsy_categories` VALUES (1053,6219,'Craft Supplies & Tools > Closures & Fasteners','','craft_supplies_and_tools.closures_and_fasteners',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1054,6278,'Craft Supplies & Tools > Closures & Fasteners > Belt Loops','','craft_supplies_and_tools.closures_and_fasteners.belt_loops',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1055,6277,'Craft Supplies & Tools > Closures & Fasteners > Brads','','craft_supplies_and_tools.closures_and_fasteners.brads',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1056,6269,'Craft Supplies & Tools > Closures & Fasteners > Buckles','','craft_supplies_and_tools.closures_and_fasteners.buckles',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1057,6270,'Craft Supplies & Tools > Closures & Fasteners > Buttons','','craft_supplies_and_tools.closures_and_fasteners.buttons',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1058,6279,'Craft Supplies & Tools > Closures & Fasteners > Clothespins','','craft_supplies_and_tools.closures_and_fasteners.clothespins',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1059,6272,'Craft Supplies & Tools > Closures & Fasteners > Eyelets & Grommets','','craft_supplies_and_tools.closures_and_fasteners.eyelets_and_grommets',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1060,6274,'Craft Supplies & Tools > Closures & Fasteners > Frog Closures','','craft_supplies_and_tools.closures_and_fasteners.frog_closures',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1061,6268,'Craft Supplies & Tools > Closures & Fasteners > Hook & Loop','','craft_supplies_and_tools.closures_and_fasteners.hook_and_loop',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1062,6273,'Craft Supplies & Tools > Closures & Fasteners > Hooks & Eyes','','craft_supplies_and_tools.closures_and_fasteners.hooks_and_eyes',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1063,6275,'Craft Supplies & Tools > Closures & Fasteners > Locks','','craft_supplies_and_tools.closures_and_fasteners.locks',1053,0);
INSERT INTO `ps_etsy_categories` VALUES (1064,6469,'Craft Supplies & Tools > Closures & Fasteners > Locks > Purse Locks','','craft_supplies_and_tools.closures_and_fasteners.locks.purse_locks',1063,0);
INSERT INTO `ps_etsy_categories` VALUES (1065,6820,'Craft Supplies & Tools > Closures & Fasteners > Locks > Purse Locks > Thumb Locks','','craft_supplies_and_tools.closures_and_fasteners.locks.purse_locks.thumb_locks',1064,1);
INSERT INTO `ps_etsy_categories` VALUES (1066,6821,'Craft Supplies & Tools > Closures & Fasteners > Locks > Purse Locks > Tuck Locks','','craft_supplies_and_tools.closures_and_fasteners.locks.purse_locks.tuck_locks',1064,1);
INSERT INTO `ps_etsy_categories` VALUES (1067,6819,'Craft Supplies & Tools > Closures & Fasteners > Locks > Purse Locks > Twist Turn Locks','','craft_supplies_and_tools.closures_and_fasteners.locks.purse_locks.twist_turn_locks',1064,1);
INSERT INTO `ps_etsy_categories` VALUES (1068,6276,'Craft Supplies & Tools > Closures & Fasteners > Rivets','','craft_supplies_and_tools.closures_and_fasteners.rivets',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1069,6267,'Craft Supplies & Tools > Closures & Fasteners > Snaps','','craft_supplies_and_tools.closures_and_fasteners.snaps',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1070,6271,'Craft Supplies & Tools > Closures & Fasteners > Zippers','','craft_supplies_and_tools.closures_and_fasteners.zippers',1053,1);
INSERT INTO `ps_etsy_categories` VALUES (1071,6241,'Craft Supplies & Tools > Decorations & Embellishments','','craft_supplies_and_tools.decorations_and_embellishments',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1072,6432,'Craft Supplies & Tools > Decorations & Embellishments > Bells','','craft_supplies_and_tools.decorations_and_embellishments.bells',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1073,6442,'Craft Supplies & Tools > Decorations & Embellishments > Bolos & Conchos','','craft_supplies_and_tools.decorations_and_embellishments.bolos_and_conchos',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1074,6441,'Craft Supplies & Tools > Decorations & Embellishments > Book Headbands','','craft_supplies_and_tools.decorations_and_embellishments.book_headbands',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1075,6433,'Craft Supplies & Tools > Decorations & Embellishments > Confetti','','craft_supplies_and_tools.decorations_and_embellishments.confetti',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1076,6443,'Craft Supplies & Tools > Decorations & Embellishments > Corners','','craft_supplies_and_tools.decorations_and_embellishments.corners',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1077,6434,'Craft Supplies & Tools > Decorations & Embellishments > Doilies','','craft_supplies_and_tools.decorations_and_embellishments.doilies',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1078,6435,'Craft Supplies & Tools > Decorations & Embellishments > Eyes','','craft_supplies_and_tools.decorations_and_embellishments.eyes',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1079,6430,'Craft Supplies & Tools > Decorations & Embellishments > Feathers','','craft_supplies_and_tools.decorations_and_embellishments.feathers',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1080,6436,'Craft Supplies & Tools > Decorations & Embellishments > Filigrees','','craft_supplies_and_tools.decorations_and_embellishments.filigrees',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1081,6428,'Craft Supplies & Tools > Decorations & Embellishments > Glitter','','craft_supplies_and_tools.decorations_and_embellishments.glitter',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1082,6437,'Craft Supplies & Tools > Decorations & Embellishments > Mirrors','','craft_supplies_and_tools.decorations_and_embellishments.mirrors',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1083,6438,'Craft Supplies & Tools > Decorations & Embellishments > Pom Poms','','craft_supplies_and_tools.decorations_and_embellishments.pom_poms',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1084,6429,'Craft Supplies & Tools > Decorations & Embellishments > Rhinestones','','craft_supplies_and_tools.decorations_and_embellishments.rhinestones',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1085,6439,'Craft Supplies & Tools > Decorations & Embellishments > Sequins','','craft_supplies_and_tools.decorations_and_embellishments.sequins',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1086,6440,'Craft Supplies & Tools > Decorations & Embellishments > Shells','','craft_supplies_and_tools.decorations_and_embellishments.shells',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1087,6431,'Craft Supplies & Tools > Decorations & Embellishments > Studs','','craft_supplies_and_tools.decorations_and_embellishments.studs',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1088,9338,'Craft Supplies & Tools > Decorations & Embellishments > Tassels','','craft_supplies_and_tools.decorations_and_embellishments.tassels',1071,1);
INSERT INTO `ps_etsy_categories` VALUES (1089,6245,'Craft Supplies & Tools > Doll & Model Supplies','','craft_supplies_and_tools.doll_and_model_supplies',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1090,6797,'Craft Supplies & Tools > Doll & Model Supplies > Blank Dolls','','craft_supplies_and_tools.doll_and_model_supplies.blank_dolls',1089,1);
INSERT INTO `ps_etsy_categories` VALUES (1091,6793,'Craft Supplies & Tools > Doll & Model Supplies > Doll Clothes & Accessories','','craft_supplies_and_tools.doll_and_model_supplies.doll_clothes_and_accessories',1089,1);
INSERT INTO `ps_etsy_categories` VALUES (1092,6792,'Craft Supplies & Tools > Doll & Model Supplies > Doll Parts','','craft_supplies_and_tools.doll_and_model_supplies.doll_parts',1089,1);
INSERT INTO `ps_etsy_categories` VALUES (1093,6450,'Craft Supplies & Tools > Doll & Model Supplies > Miniatures','','craft_supplies_and_tools.doll_and_model_supplies.miniatures',1089,1);
INSERT INTO `ps_etsy_categories` VALUES (1094,6795,'Craft Supplies & Tools > Doll & Model Supplies > Music Buttons','','craft_supplies_and_tools.doll_and_model_supplies.music_buttons',1089,1);
INSERT INTO `ps_etsy_categories` VALUES (1095,6796,'Craft Supplies & Tools > Doll & Model Supplies > Noise Makers','','craft_supplies_and_tools.doll_and_model_supplies.noise_makers',1089,1);
INSERT INTO `ps_etsy_categories` VALUES (1096,6794,'Craft Supplies & Tools > Doll & Model Supplies > Starter Dollhouses','','craft_supplies_and_tools.doll_and_model_supplies.starter_dollhouses',1089,1);
INSERT INTO `ps_etsy_categories` VALUES (1097,6246,'Craft Supplies & Tools > Fabric & Notions','','craft_supplies_and_tools.fabric_and_notions',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1098,6451,'Craft Supplies & Tools > Fabric & Notions > Fabric','','craft_supplies_and_tools.fabric_and_notions.fabric',1097,1);
INSERT INTO `ps_etsy_categories` VALUES (1099,6453,'Craft Supplies & Tools > Fabric & Notions > Lace & Trims','','craft_supplies_and_tools.fabric_and_notions.lace_and_trims',1097,1);
INSERT INTO `ps_etsy_categories` VALUES (1100,6455,'Craft Supplies & Tools > Fabric & Notions > Notions','','craft_supplies_and_tools.fabric_and_notions.notions',1097,0);
INSERT INTO `ps_etsy_categories` VALUES (1101,6810,'Craft Supplies & Tools > Fabric & Notions > Notions > Appliques & Patches','','craft_supplies_and_tools.fabric_and_notions.notions.appliques_and_patches',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1102,6803,'Craft Supplies & Tools > Fabric & Notions > Notions > Bias Tape','','craft_supplies_and_tools.fabric_and_notions.notions.bias_tape',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1103,6801,'Craft Supplies & Tools > Fabric & Notions > Notions > Bobbins','','craft_supplies_and_tools.fabric_and_notions.notions.bobbins',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1104,6812,'Craft Supplies & Tools > Fabric & Notions > Notions > Boning','','craft_supplies_and_tools.fabric_and_notions.notions.boning',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1105,6802,'Craft Supplies & Tools > Fabric & Notions > Notions > Elastic','','craft_supplies_and_tools.fabric_and_notions.notions.elastic',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1106,6811,'Craft Supplies & Tools > Fabric & Notions > Notions > Fabric Bonding Tape','','craft_supplies_and_tools.fabric_and_notions.notions.fabric_bonding_tape',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1107,9340,'Craft Supplies & Tools > Fabric & Notions > Notions > Fabric Clips','','craft_supplies_and_tools.fabric_and_notions.notions.fabric_clips',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1108,6800,'Craft Supplies & Tools > Fabric & Notions > Notions > Floss','','craft_supplies_and_tools.fabric_and_notions.notions.floss',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1109,6798,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles',1100,0);
INSERT INTO `ps_etsy_categories` VALUES (1110,7033,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Needles','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.needles',1109,0);
INSERT INTO `ps_etsy_categories` VALUES (1111,9202,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Needles > Hand Needles','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.needles.hand_needles',1110,1);
INSERT INTO `ps_etsy_categories` VALUES (1112,7061,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Needles > Knitting Needles','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.needles.knitting_needles',1110,1);
INSERT INTO `ps_etsy_categories` VALUES (1113,7059,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Needles > Punch Needles','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.needles.punch_needles',1110,1);
INSERT INTO `ps_etsy_categories` VALUES (1114,7060,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Needles > Rug Needles','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.needles.rug_needles',1110,1);
INSERT INTO `ps_etsy_categories` VALUES (1115,7058,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Needles > Yarn Needles','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.needles.yarn_needles',1110,1);
INSERT INTO `ps_etsy_categories` VALUES (1116,9203,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pin & Needle Accessories','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pin_and_needle_accessories',1109,0);
INSERT INTO `ps_etsy_categories` VALUES (1117,7038,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pin & Needle Accessories > Needle Cases','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pin_and_needle_accessories.needle_cases',1116,1);
INSERT INTO `ps_etsy_categories` VALUES (1118,7037,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pin & Needle Accessories > Needle Minders','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pin_and_needle_accessories.needle_minders',1116,1);
INSERT INTO `ps_etsy_categories` VALUES (1119,7032,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pin & Needle Accessories > Needle Threaders','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pin_and_needle_accessories.needle_threaders',1116,1);
INSERT INTO `ps_etsy_categories` VALUES (1120,7029,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pin & Needle Accessories > Pincushions','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pin_and_needle_accessories.pincushions',1116,1);
INSERT INTO `ps_etsy_categories` VALUES (1121,6807,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pin & Needle Accessories > Point Protectors','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pin_and_needle_accessories.point_protectors',1116,1);
INSERT INTO `ps_etsy_categories` VALUES (1122,7034,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pin & Needle Accessories > Thimbles','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pin_and_needle_accessories.thimbles',1116,1);
INSERT INTO `ps_etsy_categories` VALUES (1123,7035,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins',1109,0);
INSERT INTO `ps_etsy_categories` VALUES (1124,7064,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins > Ball Point Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins.ball_point_pins',1123,1);
INSERT INTO `ps_etsy_categories` VALUES (1125,7036,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins > Bar Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins.bar_pins',1123,1);
INSERT INTO `ps_etsy_categories` VALUES (1126,7066,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins > Eye Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins.eye_pins',1123,1);
INSERT INTO `ps_etsy_categories` VALUES (1127,7065,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins > Fork Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins.fork_pins',1123,1);
INSERT INTO `ps_etsy_categories` VALUES (1128,7067,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins > Head Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins.head_pins',1123,1);
INSERT INTO `ps_etsy_categories` VALUES (1129,7030,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins > Safety Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins.safety_pins',1123,1);
INSERT INTO `ps_etsy_categories` VALUES (1130,7068,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins > Sequin Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins.sequin_pins',1123,1);
INSERT INTO `ps_etsy_categories` VALUES (1131,7063,'Craft Supplies & Tools > Fabric & Notions > Notions > Pins & Needles > Pins > T-Pins','','craft_supplies_and_tools.fabric_and_notions.notions.pins_and_needles.pins.t_pins',1123,1);
INSERT INTO `ps_etsy_categories` VALUES (1132,6805,'Craft Supplies & Tools > Fabric & Notions > Notions > Pirns','','craft_supplies_and_tools.fabric_and_notions.notions.pirns',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1133,6813,'Craft Supplies & Tools > Fabric & Notions > Notions > Pom Pom Makers','','craft_supplies_and_tools.fabric_and_notions.notions.pom_pom_makers',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1134,6814,'Craft Supplies & Tools > Fabric & Notions > Notions > Purse Notions','','craft_supplies_and_tools.fabric_and_notions.notions.purse_notions',1100,0);
INSERT INTO `ps_etsy_categories` VALUES (1135,7043,'Craft Supplies & Tools > Fabric & Notions > Notions > Purse Notions > Bag Frames','','craft_supplies_and_tools.fabric_and_notions.notions.purse_notions.bag_frames',1134,1);
INSERT INTO `ps_etsy_categories` VALUES (1136,7044,'Craft Supplies & Tools > Fabric & Notions > Notions > Purse Notions > Feet','','craft_supplies_and_tools.fabric_and_notions.notions.purse_notions.feet',1134,1);
INSERT INTO `ps_etsy_categories` VALUES (1137,7042,'Craft Supplies & Tools > Fabric & Notions > Notions > Purse Notions > Handles','','craft_supplies_and_tools.fabric_and_notions.notions.purse_notions.handles',1134,1);
INSERT INTO `ps_etsy_categories` VALUES (1138,7041,'Craft Supplies & Tools > Fabric & Notions > Notions > Purse Notions > Rings','','craft_supplies_and_tools.fabric_and_notions.notions.purse_notions.rings',1134,1);
INSERT INTO `ps_etsy_categories` VALUES (1139,9197,'Craft Supplies & Tools > Fabric & Notions > Notions > Shoe Parts','','craft_supplies_and_tools.fabric_and_notions.notions.shoe_parts',1100,0);
INSERT INTO `ps_etsy_categories` VALUES (1140,9200,'Craft Supplies & Tools > Fabric & Notions > Notions > Shoe Parts > Heels','','craft_supplies_and_tools.fabric_and_notions.notions.shoe_parts.heels',1139,1);
INSERT INTO `ps_etsy_categories` VALUES (1141,9198,'Craft Supplies & Tools > Fabric & Notions > Notions > Shoe Parts > Insoles','','craft_supplies_and_tools.fabric_and_notions.notions.shoe_parts.insoles',1139,1);
INSERT INTO `ps_etsy_categories` VALUES (1142,6395,'Craft Supplies & Tools > Fabric & Notions > Notions > Shoe Parts > Shoe Laces','','craft_supplies_and_tools.fabric_and_notions.notions.shoe_parts.shoe_laces',1139,1);
INSERT INTO `ps_etsy_categories` VALUES (1143,6265,'Craft Supplies & Tools > Fabric & Notions > Notions > Shoe Parts > Shoe Lasts','','craft_supplies_and_tools.fabric_and_notions.notions.shoe_parts.shoe_lasts',1139,1);
INSERT INTO `ps_etsy_categories` VALUES (1144,9199,'Craft Supplies & Tools > Fabric & Notions > Notions > Shoe Parts > Soles','','craft_supplies_and_tools.fabric_and_notions.notions.shoe_parts.soles',1139,1);
INSERT INTO `ps_etsy_categories` VALUES (1145,9201,'Craft Supplies & Tools > Fabric & Notions > Notions > Shoe Parts > Toe & Heel Plates','','craft_supplies_and_tools.fabric_and_notions.notions.shoe_parts.toe_and_heel_plates',1139,1);
INSERT INTO `ps_etsy_categories` VALUES (1146,6804,'Craft Supplies & Tools > Fabric & Notions > Notions > Spools','','craft_supplies_and_tools.fabric_and_notions.notions.spools',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1147,6808,'Craft Supplies & Tools > Fabric & Notions > Notions > Stitch Holders','','craft_supplies_and_tools.fabric_and_notions.notions.stitch_holders',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1148,6809,'Craft Supplies & Tools > Fabric & Notions > Notions > Stitch Markers','','craft_supplies_and_tools.fabric_and_notions.notions.stitch_markers',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1149,6799,'Craft Supplies & Tools > Fabric & Notions > Notions > Thread','','craft_supplies_and_tools.fabric_and_notions.notions.thread',1100,1);
INSERT INTO `ps_etsy_categories` VALUES (1150,6454,'Craft Supplies & Tools > Fabric & Notions > Piping','','craft_supplies_and_tools.fabric_and_notions.piping',1097,1);
INSERT INTO `ps_etsy_categories` VALUES (1151,6452,'Craft Supplies & Tools > Fabric & Notions > Ribbon','','craft_supplies_and_tools.fabric_and_notions.ribbon',1097,1);
INSERT INTO `ps_etsy_categories` VALUES (1152,6220,'Craft Supplies & Tools > Findings','','craft_supplies_and_tools.findings',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1153,6280,'Craft Supplies & Tools > Findings > Bails','','craft_supplies_and_tools.findings.bails',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1154,6287,'Craft Supplies & Tools > Findings > Bead Caps','','craft_supplies_and_tools.findings.bead_caps',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1155,6288,'Craft Supplies & Tools > Findings > Bead Cones','','craft_supplies_and_tools.findings.bead_cones',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1156,6296,'Craft Supplies & Tools > Findings > Bead Inserts','','craft_supplies_and_tools.findings.bead_inserts',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1157,9190,'Craft Supplies & Tools > Findings > Bead Tips','','craft_supplies_and_tools.findings.bead_tips',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1158,6283,'Craft Supplies & Tools > Findings > Chain Links','','craft_supplies_and_tools.findings.chain_links',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1159,6294,'Craft Supplies & Tools > Findings > Chains','','craft_supplies_and_tools.findings.chains',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1160,6281,'Craft Supplies & Tools > Findings > Clasps & Clips','','craft_supplies_and_tools.findings.clasps_and_clips',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1161,6284,'Craft Supplies & Tools > Findings > Connectors','','craft_supplies_and_tools.findings.connectors',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1162,6285,'Craft Supplies & Tools > Findings > Crimps & Crimp Beads','','craft_supplies_and_tools.findings.crimps_and_crimp_beads',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1163,6297,'Craft Supplies & Tools > Findings > Ear Hoops','','craft_supplies_and_tools.findings.ear_hoops',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1164,6290,'Craft Supplies & Tools > Findings > Ear Nuts & Backs','','craft_supplies_and_tools.findings.ear_nuts_and_backs',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1165,6289,'Craft Supplies & Tools > Findings > Ear Wires & Hooks','','craft_supplies_and_tools.findings.ear_wires_and_hooks',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1166,6291,'Craft Supplies & Tools > Findings > End Bars','','craft_supplies_and_tools.findings.end_bars',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1167,9323,'Craft Supplies & Tools > Findings > End Caps','','craft_supplies_and_tools.findings.end_caps',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1168,6286,'Craft Supplies & Tools > Findings > Posts','','craft_supplies_and_tools.findings.posts',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1169,6282,'Craft Supplies & Tools > Findings > Rings','','craft_supplies_and_tools.findings.rings',1152,0);
INSERT INTO `ps_etsy_categories` VALUES (1170,6473,'Craft Supplies & Tools > Findings > Rings > D-Rings','','craft_supplies_and_tools.findings.rings.d_rings',1169,1);
INSERT INTO `ps_etsy_categories` VALUES (1171,6470,'Craft Supplies & Tools > Findings > Rings > Jump Rings','','craft_supplies_and_tools.findings.rings.jump_rings',1169,1);
INSERT INTO `ps_etsy_categories` VALUES (1172,6472,'Craft Supplies & Tools > Findings > Rings > Key Rings','','craft_supplies_and_tools.findings.rings.key_rings',1169,1);
INSERT INTO `ps_etsy_categories` VALUES (1173,6292,'Craft Supplies & Tools > Findings > Separator Bars','','craft_supplies_and_tools.findings.separator_bars',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1174,6293,'Craft Supplies & Tools > Findings > Settings & Bezel Cups','','craft_supplies_and_tools.findings.settings_and_bezel_cups',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1175,6299,'Craft Supplies & Tools > Findings > Stampings','','craft_supplies_and_tools.findings.stampings',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1176,6298,'Craft Supplies & Tools > Findings > Threaders','','craft_supplies_and_tools.findings.threaders',1152,1);
INSERT INTO `ps_etsy_categories` VALUES (1177,6295,'Craft Supplies & Tools > Findings > Watch Findings','','craft_supplies_and_tools.findings.watch_findings',1152,0);
INSERT INTO `ps_etsy_categories` VALUES (1178,6474,'Craft Supplies & Tools > Findings > Watch Findings > Watch Bands','','craft_supplies_and_tools.findings.watch_findings.watch_bands',1177,1);
INSERT INTO `ps_etsy_categories` VALUES (1179,6478,'Craft Supplies & Tools > Findings > Watch Findings > Watch Crystals','','craft_supplies_and_tools.findings.watch_findings.watch_crystals',1177,1);
INSERT INTO `ps_etsy_categories` VALUES (1180,6476,'Craft Supplies & Tools > Findings > Watch Findings > Watch Faces','','craft_supplies_and_tools.findings.watch_findings.watch_faces',1177,1);
INSERT INTO `ps_etsy_categories` VALUES (1181,9189,'Craft Supplies & Tools > Findings > Watch Findings > Watch Links','','craft_supplies_and_tools.findings.watch_findings.watch_links',1177,1);
INSERT INTO `ps_etsy_categories` VALUES (1182,6477,'Craft Supplies & Tools > Findings > Watch Findings > Watch Movements','','craft_supplies_and_tools.findings.watch_findings.watch_movements',1177,1);
INSERT INTO `ps_etsy_categories` VALUES (1183,6475,'Craft Supplies & Tools > Findings > Watch Findings > Watch Stems','','craft_supplies_and_tools.findings.watch_findings.watch_stems',1177,1);
INSERT INTO `ps_etsy_categories` VALUES (1184,6230,'Craft Supplies & Tools > Floral & Garden Supplies','','craft_supplies_and_tools.floral_and_garden_supplies',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1185,6348,'Craft Supplies & Tools > Floral & Garden Supplies > Floral','','craft_supplies_and_tools.floral_and_garden_supplies.floral',1184,0);
INSERT INTO `ps_etsy_categories` VALUES (1186,6624,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Bouquet Holders','','craft_supplies_and_tools.floral_and_garden_supplies.floral.bouquet_holders',1185,1);
INSERT INTO `ps_etsy_categories` VALUES (1187,6625,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Floral Accents','','craft_supplies_and_tools.floral_and_garden_supplies.floral.floral_accents',1185,0);
INSERT INTO `ps_etsy_categories` VALUES (1188,6864,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Floral Accents > Animals','','craft_supplies_and_tools.floral_and_garden_supplies.floral.floral_accents.animals',1187,1);
INSERT INTO `ps_etsy_categories` VALUES (1189,6866,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Floral Accents > Architecture & Structures','','craft_supplies_and_tools.floral_and_garden_supplies.floral.floral_accents.architecture_and_structures',1187,1);
INSERT INTO `ps_etsy_categories` VALUES (1190,6865,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Floral Accents > Figures','','craft_supplies_and_tools.floral_and_garden_supplies.floral.floral_accents.figures',1187,1);
INSERT INTO `ps_etsy_categories` VALUES (1191,6863,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Floral Accents > Fruits & Vegetables','','craft_supplies_and_tools.floral_and_garden_supplies.floral.floral_accents.fruits_and_vegetables',1187,1);
INSERT INTO `ps_etsy_categories` VALUES (1192,6622,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Floral Stems','','craft_supplies_and_tools.floral_and_garden_supplies.floral.floral_stems',1185,1);
INSERT INTO `ps_etsy_categories` VALUES (1193,6619,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Flowers','','craft_supplies_and_tools.floral_and_garden_supplies.floral.flowers',1185,0);
INSERT INTO `ps_etsy_categories` VALUES (1194,6862,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Flowers > Artificial Flowers','','craft_supplies_and_tools.floral_and_garden_supplies.floral.flowers.artificial_flowers',1193,1);
INSERT INTO `ps_etsy_categories` VALUES (1195,6861,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Flowers > Dried Flowers','','craft_supplies_and_tools.floral_and_garden_supplies.floral.flowers.dried_flowers',1193,1);
INSERT INTO `ps_etsy_categories` VALUES (1196,6618,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Foam Forms','','craft_supplies_and_tools.floral_and_garden_supplies.floral.foam_forms',1185,1);
INSERT INTO `ps_etsy_categories` VALUES (1197,6623,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Pin Frogs','','craft_supplies_and_tools.floral_and_garden_supplies.floral.pin_frogs',1185,1);
INSERT INTO `ps_etsy_categories` VALUES (1198,6621,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Stakes & Sticks','','craft_supplies_and_tools.floral_and_garden_supplies.floral.stakes_and_sticks',1185,1);
INSERT INTO `ps_etsy_categories` VALUES (1199,6620,'Craft Supplies & Tools > Floral & Garden Supplies > Floral > Water Picks','','craft_supplies_and_tools.floral_and_garden_supplies.floral.water_picks',1185,1);
INSERT INTO `ps_etsy_categories` VALUES (1200,6349,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening',1184,0);
INSERT INTO `ps_etsy_categories` VALUES (1201,6629,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Greenhouses & Hydroponics','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.greenhouses_and_hydroponics',1200,1);
INSERT INTO `ps_etsy_categories` VALUES (1202,6628,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery',1200,0);
INSERT INTO `ps_etsy_categories` VALUES (1203,6882,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Bushes & Trees','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.bushes_and_trees',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1204,6887,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Cones & Nuts','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.cones_and_nuts',1202,0);
INSERT INTO `ps_etsy_categories` VALUES (1205,7051,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Cones & Nuts > Nuts','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.cones_and_nuts.nuts',1204,1);
INSERT INTO `ps_etsy_categories` VALUES (1206,7050,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Cones & Nuts > Pine Cones','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.cones_and_nuts.pine_cones',1204,1);
INSERT INTO `ps_etsy_categories` VALUES (1207,9324,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Corn Husks','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.corn_husks',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1208,6892,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Garlands','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.garlands',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1209,6883,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Leaves & Thorns','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.leaves_and_thorns',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1210,6886,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Pine Needles','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.pine_needles',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1211,6890,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Plant Bulbs','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.plant_bulbs',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1212,6884,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Reeds & Canes','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.reeds_and_canes',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1213,6889,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Seed Bombs','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.seed_bombs',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1214,6888,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Seeds','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.seeds',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1215,6885,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Straw','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.straw',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1216,6891,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Other Greenery > Wreaths','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.other_greenery.wreaths',1202,1);
INSERT INTO `ps_etsy_categories` VALUES (1217,6627,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories',1200,0);
INSERT INTO `ps_etsy_categories` VALUES (1218,6880,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories > Fertilizer','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories.fertilizer',1217,1);
INSERT INTO `ps_etsy_categories` VALUES (1219,6881,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories > Food & Supplements','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories.food_and_supplements',1217,1);
INSERT INTO `ps_etsy_categories` VALUES (1220,6874,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories > Markers & Stakes','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories.markers_and_stakes',1217,1);
INSERT INTO `ps_etsy_categories` VALUES (1221,6877,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories > Plant Fillers','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories.plant_fillers',1217,1);
INSERT INTO `ps_etsy_categories` VALUES (1222,6876,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories > Plant Hangers','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories.plant_hangers',1217,1);
INSERT INTO `ps_etsy_categories` VALUES (1223,6875,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories > Planters & Pots','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories.planters_and_pots',1217,1);
INSERT INTO `ps_etsy_categories` VALUES (1224,6879,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories > Soil','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories.soil',1217,1);
INSERT INTO `ps_etsy_categories` VALUES (1225,6878,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plant Accessories > Terrarium Containers','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plant_accessories.terrarium_containers',1217,1);
INSERT INTO `ps_etsy_categories` VALUES (1226,6626,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants',1200,0);
INSERT INTO `ps_etsy_categories` VALUES (1227,6867,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants > Air Plants','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants.air_plants',1226,1);
INSERT INTO `ps_etsy_categories` VALUES (1228,6873,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants > Bamboo','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants.bamboo',1226,1);
INSERT INTO `ps_etsy_categories` VALUES (1229,6872,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants > Bonsai','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants.bonsai',1226,1);
INSERT INTO `ps_etsy_categories` VALUES (1230,9120,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants > Fruits & Vegetables','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants.fruits_and_vegetables',1226,1);
INSERT INTO `ps_etsy_categories` VALUES (1231,6870,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants > Herb Plants','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants.herb_plants',1226,1);
INSERT INTO `ps_etsy_categories` VALUES (1232,6871,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants > House Plants','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants.house_plants',1226,1);
INSERT INTO `ps_etsy_categories` VALUES (1233,6868,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants > Moss','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants.moss',1226,1);
INSERT INTO `ps_etsy_categories` VALUES (1234,6869,'Craft Supplies & Tools > Floral & Garden Supplies > Greenery & Gardening > Plants > Succulents','','craft_supplies_and_tools.floral_and_garden_supplies.greenery_and_gardening.plants.succulents',1226,1);
INSERT INTO `ps_etsy_categories` VALUES (1235,6218,'Craft Supplies & Tools > Frames, Hoops & Stands','','craft_supplies_and_tools.frames_hoops_and_stands',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1236,6266,'Craft Supplies & Tools > Frames, Hoops & Stands > Book Cradles','','craft_supplies_and_tools.frames_hoops_and_stands.book_cradles',1235,1);
INSERT INTO `ps_etsy_categories` VALUES (1237,6262,'Craft Supplies & Tools > Frames, Hoops & Stands > Easels','','craft_supplies_and_tools.frames_hoops_and_stands.easels',1235,1);
INSERT INTO `ps_etsy_categories` VALUES (1238,6259,'Craft Supplies & Tools > Frames, Hoops & Stands > Frame Mats','','craft_supplies_and_tools.frames_hoops_and_stands.frame_mats',1235,1);
INSERT INTO `ps_etsy_categories` VALUES (1239,6257,'Craft Supplies & Tools > Frames, Hoops & Stands > Frames','','craft_supplies_and_tools.frames_hoops_and_stands.frames',1235,0);
INSERT INTO `ps_etsy_categories` VALUES (1240,9117,'Craft Supplies & Tools > Frames, Hoops & Stands > Frames > Needlecraft Frames','','craft_supplies_and_tools.frames_hoops_and_stands.frames.needlecraft_frames',1239,1);
INSERT INTO `ps_etsy_categories` VALUES (1241,9118,'Craft Supplies & Tools > Frames, Hoops & Stands > Frames > Picture Frames','','craft_supplies_and_tools.frames_hoops_and_stands.frames.picture_frames',1239,1);
INSERT INTO `ps_etsy_categories` VALUES (1242,6254,'Craft Supplies & Tools > Frames, Hoops & Stands > Frames > Stretcher Frames & Bars','','craft_supplies_and_tools.frames_hoops_and_stands.frames.stretcher_frames_and_bars',1239,1);
INSERT INTO `ps_etsy_categories` VALUES (1243,6258,'Craft Supplies & Tools > Frames, Hoops & Stands > Hoops','','craft_supplies_and_tools.frames_hoops_and_stands.hoops',1235,1);
INSERT INTO `ps_etsy_categories` VALUES (1244,6260,'Craft Supplies & Tools > Frames, Hoops & Stands > Mounting Boards','','craft_supplies_and_tools.frames_hoops_and_stands.mounting_boards',1235,1);
INSERT INTO `ps_etsy_categories` VALUES (1245,6264,'Craft Supplies & Tools > Frames, Hoops & Stands > Stands','','craft_supplies_and_tools.frames_hoops_and_stands.stands',1235,1);
INSERT INTO `ps_etsy_categories` VALUES (1246,6263,'Craft Supplies & Tools > Frames, Hoops & Stands > Tripods','','craft_supplies_and_tools.frames_hoops_and_stands.tripods',1235,1);
INSERT INTO `ps_etsy_categories` VALUES (1247,6261,'Craft Supplies & Tools > Frames, Hoops & Stands > Trivets','','craft_supplies_and_tools.frames_hoops_and_stands.trivets',1235,1);
INSERT INTO `ps_etsy_categories` VALUES (1248,6223,'Craft Supplies & Tools > Glue & Adhesives','','craft_supplies_and_tools.glue_and_adhesives',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1249,6307,'Craft Supplies & Tools > Glue & Adhesives > Adhesives','','craft_supplies_and_tools.glue_and_adhesives.adhesives',1248,0);
INSERT INTO `ps_etsy_categories` VALUES (1250,6541,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Adhesive Machines','','craft_supplies_and_tools.glue_and_adhesives.adhesives.adhesive_machines',1249,1);
INSERT INTO `ps_etsy_categories` VALUES (1251,6542,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Adhesive Removers','','craft_supplies_and_tools.glue_and_adhesives.adhesives.adhesive_removers',1249,1);
INSERT INTO `ps_etsy_categories` VALUES (1252,6546,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Adhesive Sealers & Finishes','','craft_supplies_and_tools.glue_and_adhesives.adhesives.adhesive_sealers_and_finishes',1249,1);
INSERT INTO `ps_etsy_categories` VALUES (1253,6545,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Cellulose Adhesives','','craft_supplies_and_tools.glue_and_adhesives.adhesives.cellulose_adhesives',1249,1);
INSERT INTO `ps_etsy_categories` VALUES (1254,6543,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Contact Cement','','craft_supplies_and_tools.glue_and_adhesives.adhesives.contact_cement',1249,0);
INSERT INTO `ps_etsy_categories` VALUES (1255,6831,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Contact Cement > Rubber Cement','','craft_supplies_and_tools.glue_and_adhesives.adhesives.contact_cement.rubber_cement',1254,1);
INSERT INTO `ps_etsy_categories` VALUES (1256,6540,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Glue','','craft_supplies_and_tools.glue_and_adhesives.adhesives.glue',1249,1);
INSERT INTO `ps_etsy_categories` VALUES (1257,6544,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Paste','','craft_supplies_and_tools.glue_and_adhesives.adhesives.paste',1249,1);
INSERT INTO `ps_etsy_categories` VALUES (1258,6419,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Sealants','','craft_supplies_and_tools.glue_and_adhesives.adhesives.sealants',1249,1);
INSERT INTO `ps_etsy_categories` VALUES (1259,6547,'Craft Supplies & Tools > Glue & Adhesives > Adhesives > Soldering Flux','','craft_supplies_and_tools.glue_and_adhesives.adhesives.soldering_flux',1249,1);
INSERT INTO `ps_etsy_categories` VALUES (1260,6308,'Craft Supplies & Tools > Glue & Adhesives > Magnets','','craft_supplies_and_tools.glue_and_adhesives.magnets',1248,1);
INSERT INTO `ps_etsy_categories` VALUES (1261,6310,'Craft Supplies & Tools > Glue & Adhesives > Mounting Putty & Squares','','craft_supplies_and_tools.glue_and_adhesives.mounting_putty_and_squares',1248,1);
INSERT INTO `ps_etsy_categories` VALUES (1262,6643,'Craft Supplies & Tools > Glue & Adhesives > Releasing Agents','','craft_supplies_and_tools.glue_and_adhesives.releasing_agents',1248,1);
INSERT INTO `ps_etsy_categories` VALUES (1263,6309,'Craft Supplies & Tools > Glue & Adhesives > Suction Cups','','craft_supplies_and_tools.glue_and_adhesives.suction_cups',1248,1);
INSERT INTO `ps_etsy_categories` VALUES (1264,6306,'Craft Supplies & Tools > Glue & Adhesives > Tape','','craft_supplies_and_tools.glue_and_adhesives.tape',1248,0);
INSERT INTO `ps_etsy_categories` VALUES (1265,6538,'Craft Supplies & Tools > Glue & Adhesives > Tape > Binding Tape','','craft_supplies_and_tools.glue_and_adhesives.tape.binding_tape',1264,1);
INSERT INTO `ps_etsy_categories` VALUES (1266,6537,'Craft Supplies & Tools > Glue & Adhesives > Tape > Cellophane Tape','','craft_supplies_and_tools.glue_and_adhesives.tape.cellophane_tape',1264,1);
INSERT INTO `ps_etsy_categories` VALUES (1267,6535,'Craft Supplies & Tools > Glue & Adhesives > Tape > Duct Tape','','craft_supplies_and_tools.glue_and_adhesives.tape.duct_tape',1264,1);
INSERT INTO `ps_etsy_categories` VALUES (1268,6536,'Craft Supplies & Tools > Glue & Adhesives > Tape > Masking Tape','','craft_supplies_and_tools.glue_and_adhesives.tape.masking_tape',1264,1);
INSERT INTO `ps_etsy_categories` VALUES (1269,6539,'Craft Supplies & Tools > Glue & Adhesives > Tape > Mounting Tape','','craft_supplies_and_tools.glue_and_adhesives.tape.mounting_tape',1264,1);
INSERT INTO `ps_etsy_categories` VALUES (1270,6534,'Craft Supplies & Tools > Glue & Adhesives > Tape > Packing Tape','','craft_supplies_and_tools.glue_and_adhesives.tape.packing_tape',1264,1);
INSERT INTO `ps_etsy_categories` VALUES (1271,6533,'Craft Supplies & Tools > Glue & Adhesives > Tape > Washi Tape','','craft_supplies_and_tools.glue_and_adhesives.tape.washi_tape',1264,1);
INSERT INTO `ps_etsy_categories` VALUES (1272,6311,'Craft Supplies & Tools > Glue & Adhesives > Tape Dispensers','','craft_supplies_and_tools.glue_and_adhesives.tape_dispensers',1248,1);
INSERT INTO `ps_etsy_categories` VALUES (1273,9139,'Craft Supplies & Tools > Imaging & Lighting','','craft_supplies_and_tools.imaging_and_lighting',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1274,9140,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging',1273,0);
INSERT INTO `ps_etsy_categories` VALUES (1275,6328,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Camera Lenses','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.camera_lenses',1274,1);
INSERT INTO `ps_etsy_categories` VALUES (1276,6713,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Cameras','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.cameras',1274,1);
INSERT INTO `ps_etsy_categories` VALUES (1277,9141,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Film','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.film',1274,1);
INSERT INTO `ps_etsy_categories` VALUES (1278,6893,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Film Development Chemicals','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.film_development_chemicals',1274,1);
INSERT INTO `ps_etsy_categories` VALUES (1279,6329,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Filters','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.filters',1274,1);
INSERT INTO `ps_etsy_categories` VALUES (1280,6330,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Magnifiers','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.magnifiers',1274,0);
INSERT INTO `ps_etsy_categories` VALUES (1281,6566,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Magnifiers > Loupes','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.magnifiers.loupes',1280,1);
INSERT INTO `ps_etsy_categories` VALUES (1282,6567,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Magnifiers > Magnifying Glasses','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.magnifiers.magnifying_glasses',1280,1);
INSERT INTO `ps_etsy_categories` VALUES (1283,9142,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Slides & Accessories','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.slides_and_accessories',1274,0);
INSERT INTO `ps_etsy_categories` VALUES (1284,6225,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Slides & Accessories > Slide Viewers','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.slides_and_accessories.slide_viewers',1283,1);
INSERT INTO `ps_etsy_categories` VALUES (1285,6327,'Craft Supplies & Tools > Imaging & Lighting > Cameras & Imaging > Slides & Accessories > Slides','','craft_supplies_and_tools.imaging_and_lighting.cameras_and_imaging.slides_and_accessories.slides',1283,1);
INSERT INTO `ps_etsy_categories` VALUES (1286,6226,'Craft Supplies & Tools > Imaging & Lighting > Lighting','','craft_supplies_and_tools.imaging_and_lighting.lighting',1273,0);
INSERT INTO `ps_etsy_categories` VALUES (1287,9143,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Flashes','','craft_supplies_and_tools.imaging_and_lighting.lighting.flashes',1286,1);
INSERT INTO `ps_etsy_categories` VALUES (1288,6333,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Flashlights','','craft_supplies_and_tools.imaging_and_lighting.lighting.flashlights',1286,1);
INSERT INTO `ps_etsy_categories` VALUES (1289,6335,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lanterns','','craft_supplies_and_tools.imaging_and_lighting.lighting.lanterns',1286,1);
INSERT INTO `ps_etsy_categories` VALUES (1290,6334,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lightboxes & Tables','','craft_supplies_and_tools.imaging_and_lighting.lighting.lightboxes_and_tables',1286,1);
INSERT INTO `ps_etsy_categories` VALUES (1291,6337,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lighting Parts','','craft_supplies_and_tools.imaging_and_lighting.lighting.lighting_parts',1286,0);
INSERT INTO `ps_etsy_categories` VALUES (1292,6572,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lighting Parts > Lamp Bases & Parts','','craft_supplies_and_tools.imaging_and_lighting.lighting.lighting_parts.lamp_bases_and_parts',1291,1);
INSERT INTO `ps_etsy_categories` VALUES (1293,6570,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lighting Parts > Light Bulbs','','craft_supplies_and_tools.imaging_and_lighting.lighting.lighting_parts.light_bulbs',1291,1);
INSERT INTO `ps_etsy_categories` VALUES (1294,6568,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lighting Parts > Light Sockets','','craft_supplies_and_tools.imaging_and_lighting.lighting.lighting_parts.light_sockets',1291,1);
INSERT INTO `ps_etsy_categories` VALUES (1295,6569,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lighting Parts > Plugs & Cords','','craft_supplies_and_tools.imaging_and_lighting.lighting.lighting_parts.plugs_and_cords',1291,1);
INSERT INTO `ps_etsy_categories` VALUES (1296,6571,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lighting Parts > Shades','','craft_supplies_and_tools.imaging_and_lighting.lighting.lighting_parts.shades',1291,1);
INSERT INTO `ps_etsy_categories` VALUES (1297,6573,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Lighting Parts > Switches','','craft_supplies_and_tools.imaging_and_lighting.lighting.lighting_parts.switches',1291,1);
INSERT INTO `ps_etsy_categories` VALUES (1298,6336,'Craft Supplies & Tools > Imaging & Lighting > Lighting > String Lights','','craft_supplies_and_tools.imaging_and_lighting.lighting.string_lights',1286,1);
INSERT INTO `ps_etsy_categories` VALUES (1299,6331,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Studio Lighting','','craft_supplies_and_tools.imaging_and_lighting.lighting.studio_lighting',1286,1);
INSERT INTO `ps_etsy_categories` VALUES (1300,6332,'Craft Supplies & Tools > Imaging & Lighting > Lighting > Task Lighting','','craft_supplies_and_tools.imaging_and_lighting.lighting.task_lighting',1286,1);
INSERT INTO `ps_etsy_categories` VALUES (1301,6221,'Craft Supplies & Tools > Kitchen Supplies','','craft_supplies_and_tools.kitchen_supplies',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1302,6303,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations',1301,0);
INSERT INTO `ps_etsy_categories` VALUES (1303,9121,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Cake Toppers & Picks','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.cake_toppers_and_picks',1302,0);
INSERT INTO `ps_etsy_categories` VALUES (1304,9122,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Cake Toppers & Picks > Cake Toppers','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.cake_toppers_and_picks.cake_toppers',1303,1);
INSERT INTO `ps_etsy_categories` VALUES (1305,9123,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Cake Toppers & Picks > Picks','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.cake_toppers_and_picks.picks',1303,1);
INSERT INTO `ps_etsy_categories` VALUES (1306,6513,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Food Coloring','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.food_coloring',1302,1);
INSERT INTO `ps_etsy_categories` VALUES (1307,6510,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Icing & Frosting','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.icing_and_frosting',1302,0);
INSERT INTO `ps_etsy_categories` VALUES (1308,6824,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Icing & Frosting > Fondant','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.icing_and_frosting.fondant',1307,1);
INSERT INTO `ps_etsy_categories` VALUES (1309,6512,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Liners & Cups','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.liners_and_cups',1302,1);
INSERT INTO `ps_etsy_categories` VALUES (1310,6511,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Piping Pens, Bags & Nozzles','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.piping_pens_bags_and_nozzles',1302,0);
INSERT INTO `ps_etsy_categories` VALUES (1311,6826,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Piping Pens, Bags & Nozzles > Frosting Tips','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.piping_pens_bags_and_nozzles.frosting_tips',1310,1);
INSERT INTO `ps_etsy_categories` VALUES (1312,6827,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Piping Pens, Bags & Nozzles > Icing Tubes & Pens','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.piping_pens_bags_and_nozzles.icing_tubes_and_pens',1310,1);
INSERT INTO `ps_etsy_categories` VALUES (1313,6825,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Piping Pens, Bags & Nozzles > Pastry Bags','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.piping_pens_bags_and_nozzles.pastry_bags',1310,1);
INSERT INTO `ps_etsy_categories` VALUES (1314,6509,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Sprinkles, Dust & Flakes','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.sprinkles_dust_and_flakes',1302,1);
INSERT INTO `ps_etsy_categories` VALUES (1315,6514,'Craft Supplies & Tools > Kitchen Supplies > Baking & Cake Decorations > Sticks','','craft_supplies_and_tools.kitchen_supplies.baking_and_cake_decorations.sticks',1302,1);
INSERT INTO `ps_etsy_categories` VALUES (1316,6305,'Craft Supplies & Tools > Kitchen Supplies > Fermentation & Brewing Supplies','','craft_supplies_and_tools.kitchen_supplies.fermentation_and_brewing_supplies',1301,0);
INSERT INTO `ps_etsy_categories` VALUES (1317,6525,'Craft Supplies & Tools > Kitchen Supplies > Fermentation & Brewing Supplies > Bottle Cappers','','craft_supplies_and_tools.kitchen_supplies.fermentation_and_brewing_supplies.bottle_cappers',1316,1);
INSERT INTO `ps_etsy_categories` VALUES (1318,6530,'Craft Supplies & Tools > Kitchen Supplies > Fermentation & Brewing Supplies > Cheese & Yogurt Makers','','craft_supplies_and_tools.kitchen_supplies.fermentation_and_brewing_supplies.cheese_and_yogurt_makers',1316,1);
INSERT INTO `ps_etsy_categories` VALUES (1319,6526,'Craft Supplies & Tools > Kitchen Supplies > Fermentation & Brewing Supplies > Fermenters','','craft_supplies_and_tools.kitchen_supplies.fermentation_and_brewing_supplies.fermenters',1316,1);
INSERT INTO `ps_etsy_categories` VALUES (1320,6527,'Craft Supplies & Tools > Kitchen Supplies > Fermentation & Brewing Supplies > Hops & Pellets','','craft_supplies_and_tools.kitchen_supplies.fermentation_and_brewing_supplies.hops_and_pellets',1316,1);
INSERT INTO `ps_etsy_categories` VALUES (1321,6529,'Craft Supplies & Tools > Kitchen Supplies > Fermentation & Brewing Supplies > Malt & Other Grains','','craft_supplies_and_tools.kitchen_supplies.fermentation_and_brewing_supplies.malt_and_other_grains',1316,1);
INSERT INTO `ps_etsy_categories` VALUES (1322,6528,'Craft Supplies & Tools > Kitchen Supplies > Fermentation & Brewing Supplies > Stoppers & Airlocks','','craft_supplies_and_tools.kitchen_supplies.fermentation_and_brewing_supplies.stoppers_and_airlocks',1316,1);
INSERT INTO `ps_etsy_categories` VALUES (1323,6531,'Craft Supplies & Tools > Kitchen Supplies > Fermentation & Brewing Supplies > Taps & Handles','','craft_supplies_and_tools.kitchen_supplies.fermentation_and_brewing_supplies.taps_and_handles',1316,1);
INSERT INTO `ps_etsy_categories` VALUES (1324,6304,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives',1301,0);
INSERT INTO `ps_etsy_categories` VALUES (1325,6515,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Candy Mixes & Melts','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.candy_mixes_and_melts',1324,1);
INSERT INTO `ps_etsy_categories` VALUES (1326,6523,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Extracts','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.extracts',1324,1);
INSERT INTO `ps_etsy_categories` VALUES (1327,6521,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Flavoring','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.flavoring',1324,1);
INSERT INTO `ps_etsy_categories` VALUES (1328,6518,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Live Cultures & Starters','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.live_cultures_and_starters',1324,0);
INSERT INTO `ps_etsy_categories` VALUES (1329,6829,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Live Cultures & Starters > Kefir','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.live_cultures_and_starters.kefir',1328,1);
INSERT INTO `ps_etsy_categories` VALUES (1330,6830,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Live Cultures & Starters > Rennet','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.live_cultures_and_starters.rennet',1328,1);
INSERT INTO `ps_etsy_categories` VALUES (1331,6828,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Live Cultures & Starters > Scobies','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.live_cultures_and_starters.scobies',1328,1);
INSERT INTO `ps_etsy_categories` VALUES (1332,6517,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Powders & Additives','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.powders_and_additives',1324,1);
INSERT INTO `ps_etsy_categories` VALUES (1333,6519,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Spices','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.spices',1324,1);
INSERT INTO `ps_etsy_categories` VALUES (1334,6520,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Thickening Agents','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.thickening_agents',1324,1);
INSERT INTO `ps_etsy_categories` VALUES (1335,6522,'Craft Supplies & Tools > Kitchen Supplies > Food Starters & Additives > Yeast','','craft_supplies_and_tools.kitchen_supplies.food_starters_and_additives.yeast',1324,1);
INSERT INTO `ps_etsy_categories` VALUES (1336,6301,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils',1301,0);
INSERT INTO `ps_etsy_categories` VALUES (1337,6498,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Cookie Cutters','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.cookie_cutters',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1338,6505,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Funnels','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.funnels',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1339,6503,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Graters','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.graters',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1340,6508,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Kitchen Tongs','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.kitchen_tongs',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1341,6506,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Ladles','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.ladles',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1342,6500,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Mashers & Pounders','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.mashers_and_pounders',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1343,6494,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Measuring Cups & Spoons','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.measuring_cups_and_spoons',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1344,6496,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Mixer Attachments','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.mixer_attachments',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1345,6495,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Mixers & Whisks','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.mixers_and_whisks',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1346,6497,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Mixing Spoons','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.mixing_spoons',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1347,6501,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Muddlers','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.muddlers',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1348,6507,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Paddles','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.paddles',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1349,6499,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Rolling Pins','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.rolling_pins',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1350,6504,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Scoops','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.scoops',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1351,6493,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Spatulas','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.spatulas',1336,1);
INSERT INTO `ps_etsy_categories` VALUES (1352,6502,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Strainers','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.strainers',1336,0);
INSERT INTO `ps_etsy_categories` VALUES (1353,6822,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Strainers > Cheesecloth','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.strainers.cheesecloth',1352,1);
INSERT INTO `ps_etsy_categories` VALUES (1354,6823,'Craft Supplies & Tools > Kitchen Supplies > Kitchen Tools & Utensils > Strainers > Sifters','','craft_supplies_and_tools.kitchen_supplies.kitchen_tools_and_utensils.strainers.sifters',1352,1);
INSERT INTO `ps_etsy_categories` VALUES (1355,6302,'Craft Supplies & Tools > Kitchen Supplies > Mixing Bowls','','craft_supplies_and_tools.kitchen_supplies.mixing_bowls',1301,1);
INSERT INTO `ps_etsy_categories` VALUES (1356,6300,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans',1301,0);
INSERT INTO `ps_etsy_categories` VALUES (1357,6490,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Baking Mats','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.baking_mats',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1358,6487,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Boilers & Kettles','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.boilers_and_kettles',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1359,6484,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Bundt Pans','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.bundt_pans',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1360,6479,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Cake Pans','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.cake_pans',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1361,6480,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Cookie Sheets','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.cookie_sheets',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1362,6485,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Cooling Racks','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.cooling_racks',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1363,6483,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Loaf Pans','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.loaf_pans',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1364,6489,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Mash Tuns','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.mash_tuns',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1365,6482,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Muffin Pans','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.muffin_pans',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1366,6481,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Pie Pans & Plates','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.pie_pans_and_plates',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1367,6491,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Pizza Stones & Paddles','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.pizza_stones_and_paddles',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1368,6488,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Pouring Pots','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.pouring_pots',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1369,6492,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Pressure Cookers','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.pressure_cookers',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1370,6486,'Craft Supplies & Tools > Kitchen Supplies > Pots & Pans > Stock Pots','','craft_supplies_and_tools.kitchen_supplies.pots_and_pans.stock_pots',1356,1);
INSERT INTO `ps_etsy_categories` VALUES (1371,6224,'Craft Supplies & Tools > Knives & Cutting Tools','','craft_supplies_and_tools.knives_and_cutting_tools',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1372,6321,'Craft Supplies & Tools > Knives & Cutting Tools > Axes','','craft_supplies_and_tools.knives_and_cutting_tools.axes',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1373,6314,'Craft Supplies & Tools > Knives & Cutting Tools > Blades','','craft_supplies_and_tools.knives_and_cutting_tools.blades',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1374,6318,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers',1371,0);
INSERT INTO `ps_etsy_categories` VALUES (1375,6559,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Cutters','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.cutters',1374,0);
INSERT INTO `ps_etsy_categories` VALUES (1376,6835,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Cutters > Clay Cutters','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.cutters.clay_cutters',1375,1);
INSERT INTO `ps_etsy_categories` VALUES (1377,6834,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Cutters > Glass Cutting Tools','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.cutters.glass_cutting_tools',1375,1);
INSERT INTO `ps_etsy_categories` VALUES (1378,6836,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Cutters > Paper Cutters','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.cutters.paper_cutters',1375,1);
INSERT INTO `ps_etsy_categories` VALUES (1379,6833,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Cutters > Rotary Cutters','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.cutters.rotary_cutters',1375,1);
INSERT INTO `ps_etsy_categories` VALUES (1380,6832,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Cutters > Wire Cutters','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.cutters.wire_cutters',1375,1);
INSERT INTO `ps_etsy_categories` VALUES (1381,6560,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Nippers','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.nippers',1374,1);
INSERT INTO `ps_etsy_categories` VALUES (1382,6558,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Pruners','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.pruners',1374,1);
INSERT INTO `ps_etsy_categories` VALUES (1383,6562,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Reamers','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.reamers',1374,1);
INSERT INTO `ps_etsy_categories` VALUES (1384,6561,'Craft Supplies & Tools > Knives & Cutting Tools > Cutters & Trimmers > Skivers','','craft_supplies_and_tools.knives_and_cutting_tools.cutters_and_trimmers.skivers',1374,1);
INSERT INTO `ps_etsy_categories` VALUES (1385,6316,'Craft Supplies & Tools > Knives & Cutting Tools > Cutting Mats','','craft_supplies_and_tools.knives_and_cutting_tools.cutting_mats',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1386,6325,'Craft Supplies & Tools > Knives & Cutting Tools > Edgers','','craft_supplies_and_tools.knives_and_cutting_tools.edgers',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1387,6324,'Craft Supplies & Tools > Knives & Cutting Tools > Engravers','','craft_supplies_and_tools.knives_and_cutting_tools.engravers',1371,0);
INSERT INTO `ps_etsy_categories` VALUES (1388,6563,'Craft Supplies & Tools > Knives & Cutting Tools > Engravers > Engraving Machines','','craft_supplies_and_tools.knives_and_cutting_tools.engravers.engraving_machines',1387,1);
INSERT INTO `ps_etsy_categories` VALUES (1389,6565,'Craft Supplies & Tools > Knives & Cutting Tools > Engravers > Graver Handles','','craft_supplies_and_tools.knives_and_cutting_tools.engravers.graver_handles',1387,1);
INSERT INTO `ps_etsy_categories` VALUES (1390,6564,'Craft Supplies & Tools > Knives & Cutting Tools > Engravers > Gravers','','craft_supplies_and_tools.knives_and_cutting_tools.engravers.gravers',1387,1);
INSERT INTO `ps_etsy_categories` VALUES (1391,6313,'Craft Supplies & Tools > Knives & Cutting Tools > Knives','','craft_supplies_and_tools.knives_and_cutting_tools.knives',1371,0);
INSERT INTO `ps_etsy_categories` VALUES (1392,6551,'Craft Supplies & Tools > Knives & Cutting Tools > Knives > Craft Knives','','craft_supplies_and_tools.knives_and_cutting_tools.knives.craft_knives',1391,1);
INSERT INTO `ps_etsy_categories` VALUES (1393,6552,'Craft Supplies & Tools > Knives & Cutting Tools > Knives > Fettling Knives','','craft_supplies_and_tools.knives_and_cutting_tools.knives.fettling_knives',1391,1);
INSERT INTO `ps_etsy_categories` VALUES (1394,6554,'Craft Supplies & Tools > Knives & Cutting Tools > Knives > Palette Knives','','craft_supplies_and_tools.knives_and_cutting_tools.knives.palette_knives',1391,1);
INSERT INTO `ps_etsy_categories` VALUES (1395,6556,'Craft Supplies & Tools > Knives & Cutting Tools > Knives > Paring Knives','','craft_supplies_and_tools.knives_and_cutting_tools.knives.paring_knives',1391,1);
INSERT INTO `ps_etsy_categories` VALUES (1396,6549,'Craft Supplies & Tools > Knives & Cutting Tools > Knives > Swivel Knives','','craft_supplies_and_tools.knives_and_cutting_tools.knives.swivel_knives',1391,1);
INSERT INTO `ps_etsy_categories` VALUES (1397,6553,'Craft Supplies & Tools > Knives & Cutting Tools > Knives > Utility Knives','','craft_supplies_and_tools.knives_and_cutting_tools.knives.utility_knives',1391,1);
INSERT INTO `ps_etsy_categories` VALUES (1398,6550,'Craft Supplies & Tools > Knives & Cutting Tools > Knives > Whittling Knives','','craft_supplies_and_tools.knives_and_cutting_tools.knives.whittling_knives',1391,1);
INSERT INTO `ps_etsy_categories` VALUES (1399,6320,'Craft Supplies & Tools > Knives & Cutting Tools > Punches','','craft_supplies_and_tools.knives_and_cutting_tools.punches',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1400,6702,'Craft Supplies & Tools > Knives & Cutting Tools > Routers','','craft_supplies_and_tools.knives_and_cutting_tools.routers',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1401,6319,'Craft Supplies & Tools > Knives & Cutting Tools > Saws','','craft_supplies_and_tools.knives_and_cutting_tools.saws',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1402,6312,'Craft Supplies & Tools > Knives & Cutting Tools > Scissors & Shears','','craft_supplies_and_tools.knives_and_cutting_tools.scissors_and_shears',1371,0);
INSERT INTO `ps_etsy_categories` VALUES (1403,6548,'Craft Supplies & Tools > Knives & Cutting Tools > Scissors & Shears > Pinking Shears','','craft_supplies_and_tools.knives_and_cutting_tools.scissors_and_shears.pinking_shears',1402,1);
INSERT INTO `ps_etsy_categories` VALUES (1404,6315,'Craft Supplies & Tools > Knives & Cutting Tools > Seam Rippers','','craft_supplies_and_tools.knives_and_cutting_tools.seam_rippers',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1405,6317,'Craft Supplies & Tools > Knives & Cutting Tools > Sharpening Tools','','craft_supplies_and_tools.knives_and_cutting_tools.sharpening_tools',1371,0);
INSERT INTO `ps_etsy_categories` VALUES (1406,6557,'Craft Supplies & Tools > Knives & Cutting Tools > Sharpening Tools > Beading Blocks','','craft_supplies_and_tools.knives_and_cutting_tools.sharpening_tools.beading_blocks',1405,1);
INSERT INTO `ps_etsy_categories` VALUES (1407,6323,'Craft Supplies & Tools > Knives & Cutting Tools > Slicers','','craft_supplies_and_tools.knives_and_cutting_tools.slicers',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1408,6326,'Craft Supplies & Tools > Knives & Cutting Tools > Tear Bars','','craft_supplies_and_tools.knives_and_cutting_tools.tear_bars',1371,1);
INSERT INTO `ps_etsy_categories` VALUES (1409,6227,'Craft Supplies & Tools > Molds','','craft_supplies_and_tools.molds',919,1);
INSERT INTO `ps_etsy_categories` VALUES (1410,6237,'Craft Supplies & Tools > Paints, Inks & Dyes','','craft_supplies_and_tools.paints_inks_and_dyes',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1411,6424,'Craft Supplies & Tools > Paints, Inks & Dyes > Dyes','','craft_supplies_and_tools.paints_inks_and_dyes.dyes',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1412,6425,'Craft Supplies & Tools > Paints, Inks & Dyes > Embossing Powder','','craft_supplies_and_tools.paints_inks_and_dyes.embossing_powder',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1413,6422,'Craft Supplies & Tools > Paints, Inks & Dyes > Enamel Powder','','craft_supplies_and_tools.paints_inks_and_dyes.enamel_powder',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1414,6417,'Craft Supplies & Tools > Paints, Inks & Dyes > Glazes','','craft_supplies_and_tools.paints_inks_and_dyes.glazes',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1415,9191,'Craft Supplies & Tools > Paints, Inks & Dyes > Inks','','craft_supplies_and_tools.paints_inks_and_dyes.inks',1410,0);
INSERT INTO `ps_etsy_categories` VALUES (1416,6414,'Craft Supplies & Tools > Paints, Inks & Dyes > Inks > Ink','','craft_supplies_and_tools.paints_inks_and_dyes.inks.ink',1415,1);
INSERT INTO `ps_etsy_categories` VALUES (1417,6366,'Craft Supplies & Tools > Paints, Inks & Dyes > Inks > Ink Pads','','craft_supplies_and_tools.paints_inks_and_dyes.inks.ink_pads',1415,1);
INSERT INTO `ps_etsy_categories` VALUES (1418,6415,'Craft Supplies & Tools > Paints, Inks & Dyes > Inks > Inkwells','','craft_supplies_and_tools.paints_inks_and_dyes.inks.inkwells',1415,1);
INSERT INTO `ps_etsy_categories` VALUES (1419,6641,'Craft Supplies & Tools > Paints, Inks & Dyes > Lubricants','','craft_supplies_and_tools.paints_inks_and_dyes.lubricants',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1420,6416,'Craft Supplies & Tools > Paints, Inks & Dyes > Paint','','craft_supplies_and_tools.paints_inks_and_dyes.paint',1410,0);
INSERT INTO `ps_etsy_categories` VALUES (1421,6759,'Craft Supplies & Tools > Paints, Inks & Dyes > Paint > Airbrush Paint','','craft_supplies_and_tools.paints_inks_and_dyes.paint.airbrush_paint',1420,1);
INSERT INTO `ps_etsy_categories` VALUES (1422,6756,'Craft Supplies & Tools > Paints, Inks & Dyes > Paint > Art Paint','','craft_supplies_and_tools.paints_inks_and_dyes.paint.art_paint',1420,1);
INSERT INTO `ps_etsy_categories` VALUES (1423,6760,'Craft Supplies & Tools > Paints, Inks & Dyes > Paint > Chalk Paint','','craft_supplies_and_tools.paints_inks_and_dyes.paint.chalk_paint',1420,1);
INSERT INTO `ps_etsy_categories` VALUES (1424,6757,'Craft Supplies & Tools > Paints, Inks & Dyes > Paint > Fabric Paint','','craft_supplies_and_tools.paints_inks_and_dyes.paint.fabric_paint',1420,1);
INSERT INTO `ps_etsy_categories` VALUES (1425,6421,'Craft Supplies & Tools > Paints, Inks & Dyes > Paint > Paint Pens','','craft_supplies_and_tools.paints_inks_and_dyes.paint.paint_pens',1420,1);
INSERT INTO `ps_etsy_categories` VALUES (1426,6758,'Craft Supplies & Tools > Paints, Inks & Dyes > Paint > Spray Paint','','craft_supplies_and_tools.paints_inks_and_dyes.paint.spray_paint',1420,1);
INSERT INTO `ps_etsy_categories` VALUES (1427,9337,'Craft Supplies & Tools > Paints, Inks & Dyes > Paint > Wall Paint','','craft_supplies_and_tools.paints_inks_and_dyes.paint.wall_paint',1420,1);
INSERT INTO `ps_etsy_categories` VALUES (1428,6423,'Craft Supplies & Tools > Paints, Inks & Dyes > Palettes','','craft_supplies_and_tools.paints_inks_and_dyes.palettes',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1429,6420,'Craft Supplies & Tools > Paints, Inks & Dyes > Pigments','','craft_supplies_and_tools.paints_inks_and_dyes.pigments',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1430,6418,'Craft Supplies & Tools > Paints, Inks & Dyes > Primers','','craft_supplies_and_tools.paints_inks_and_dyes.primers',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1431,6639,'Craft Supplies & Tools > Paints, Inks & Dyes > Solvents','','craft_supplies_and_tools.paints_inks_and_dyes.solvents',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1432,6638,'Craft Supplies & Tools > Paints, Inks & Dyes > Stains','','craft_supplies_and_tools.paints_inks_and_dyes.stains',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1433,6640,'Craft Supplies & Tools > Paints, Inks & Dyes > Thinners','','craft_supplies_and_tools.paints_inks_and_dyes.thinners',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1434,6642,'Craft Supplies & Tools > Paints, Inks & Dyes > Varnishes','','craft_supplies_and_tools.paints_inks_and_dyes.varnishes',1410,1);
INSERT INTO `ps_etsy_categories` VALUES (1435,6228,'Craft Supplies & Tools > Party & Gifting','','craft_supplies_and_tools.party_and_gifting',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1436,6342,'Craft Supplies & Tools > Party & Gifting > Labels, Stickers & Tags','','craft_supplies_and_tools.party_and_gifting.labels_stickers_and_tags',1435,0);
INSERT INTO `ps_etsy_categories` VALUES (1437,6616,'Craft Supplies & Tools > Party & Gifting > Labels, Stickers & Tags > Label Makers','','craft_supplies_and_tools.party_and_gifting.labels_stickers_and_tags.label_makers',1436,1);
INSERT INTO `ps_etsy_categories` VALUES (1438,6613,'Craft Supplies & Tools > Party & Gifting > Labels, Stickers & Tags > Labels','','craft_supplies_and_tools.party_and_gifting.labels_stickers_and_tags.labels',1436,1);
INSERT INTO `ps_etsy_categories` VALUES (1439,6614,'Craft Supplies & Tools > Party & Gifting > Labels, Stickers & Tags > Stickers','','craft_supplies_and_tools.party_and_gifting.labels_stickers_and_tags.stickers',1436,1);
INSERT INTO `ps_etsy_categories` VALUES (1440,6615,'Craft Supplies & Tools > Party & Gifting > Labels, Stickers & Tags > Tags','','craft_supplies_and_tools.party_and_gifting.labels_stickers_and_tags.tags',1436,1);
INSERT INTO `ps_etsy_categories` VALUES (1441,6339,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing',1435,0);
INSERT INTO `ps_etsy_categories` VALUES (1442,6602,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing > Bubble & Padded Mailers','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing.bubble_and_padded_mailers',1441,1);
INSERT INTO `ps_etsy_categories` VALUES (1443,6603,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing > Cushioning','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing.cushioning',1441,0);
INSERT INTO `ps_etsy_categories` VALUES (1444,6845,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing > Cushioning > Bubble Wrap','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing.cushioning.bubble_wrap',1443,1);
INSERT INTO `ps_etsy_categories` VALUES (1445,6846,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing > Cushioning > Packing Peanuts','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing.cushioning.packing_peanuts',1443,1);
INSERT INTO `ps_etsy_categories` VALUES (1446,6847,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing > Cushioning > Stuffing & Shredded Packaging','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing.cushioning.stuffing_and_shredded_packaging',1443,1);
INSERT INTO `ps_etsy_categories` VALUES (1447,6599,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing > Envelopes','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing.envelopes',1441,1);
INSERT INTO `ps_etsy_categories` VALUES (1448,6600,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing > Mailing Boxes','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing.mailing_boxes',1441,1);
INSERT INTO `ps_etsy_categories` VALUES (1449,6601,'Craft Supplies & Tools > Party & Gifting > Mailers & Mailing > Mailing Tubes','','craft_supplies_and_tools.party_and_gifting.mailers_and_mailing.mailing_tubes',1441,1);
INSERT INTO `ps_etsy_categories` VALUES (1450,6340,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping',1435,0);
INSERT INTO `ps_etsy_categories` VALUES (1451,6607,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Bags','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.bags',1450,0);
INSERT INTO `ps_etsy_categories` VALUES (1452,6852,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Bags > Gift Bags','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.bags.gift_bags',1451,1);
INSERT INTO `ps_etsy_categories` VALUES (1453,6853,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Bags > Merchandise Bags','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.bags.merchandise_bags',1451,1);
INSERT INTO `ps_etsy_categories` VALUES (1454,6851,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Bags > Party Favor Bags','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.bags.party_favor_bags',1451,1);
INSERT INTO `ps_etsy_categories` VALUES (1455,6606,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Bows & Ribbon','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.bows_and_ribbon',1450,0);
INSERT INTO `ps_etsy_categories` VALUES (1456,6848,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Bows & Ribbon > Bows','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.bows_and_ribbon.bows',1455,1);
INSERT INTO `ps_etsy_categories` VALUES (1457,6849,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Bows & Ribbon > Gift Ribbon','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.bows_and_ribbon.gift_ribbon',1455,1);
INSERT INTO `ps_etsy_categories` VALUES (1458,9183,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Candy Wrappers','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.candy_wrappers',1450,1);
INSERT INTO `ps_etsy_categories` VALUES (1459,6605,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Furoshiki','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.furoshiki',1450,1);
INSERT INTO `ps_etsy_categories` VALUES (1460,6608,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Gift Boxes','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.gift_boxes',1450,1);
INSERT INTO `ps_etsy_categories` VALUES (1461,6604,'Craft Supplies & Tools > Party & Gifting > Packaging & Wrapping > Gift Wrap','','craft_supplies_and_tools.party_and_gifting.packaging_and_wrapping.gift_wrap',1450,1);
INSERT INTO `ps_etsy_categories` VALUES (1462,6341,'Craft Supplies & Tools > Party & Gifting > Party Supplies','','craft_supplies_and_tools.party_and_gifting.party_supplies',1435,0);
INSERT INTO `ps_etsy_categories` VALUES (1463,6610,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Balloons','','craft_supplies_and_tools.party_and_gifting.party_supplies.balloons',1462,1);
INSERT INTO `ps_etsy_categories` VALUES (1464,6611,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Party Candles','','craft_supplies_and_tools.party_and_gifting.party_supplies.party_candles',1462,1);
INSERT INTO `ps_etsy_categories` VALUES (1465,6612,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Party Favors','','craft_supplies_and_tools.party_and_gifting.party_supplies.party_favors',1462,1);
INSERT INTO `ps_etsy_categories` VALUES (1466,6609,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Tableware','','craft_supplies_and_tools.party_and_gifting.party_supplies.tableware',1462,0);
INSERT INTO `ps_etsy_categories` VALUES (1467,6854,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Tableware > Cups','','craft_supplies_and_tools.party_and_gifting.party_supplies.tableware.cups',1466,1);
INSERT INTO `ps_etsy_categories` VALUES (1468,6856,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Tableware > Cutlery','','craft_supplies_and_tools.party_and_gifting.party_supplies.tableware.cutlery',1466,1);
INSERT INTO `ps_etsy_categories` VALUES (1469,6858,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Tableware > Napkins','','craft_supplies_and_tools.party_and_gifting.party_supplies.tableware.napkins',1466,1);
INSERT INTO `ps_etsy_categories` VALUES (1470,6855,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Tableware > Plates','','craft_supplies_and_tools.party_and_gifting.party_supplies.tableware.plates',1466,1);
INSERT INTO `ps_etsy_categories` VALUES (1471,6859,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Tableware > Runners & Tablecloths','','craft_supplies_and_tools.party_and_gifting.party_supplies.tableware.runners_and_tablecloths',1466,1);
INSERT INTO `ps_etsy_categories` VALUES (1472,6857,'Craft Supplies & Tools > Party & Gifting > Party Supplies > Tableware > Straws','','craft_supplies_and_tools.party_and_gifting.party_supplies.tableware.straws',1466,1);
INSERT INTO `ps_etsy_categories` VALUES (1473,6229,'Craft Supplies & Tools > Patterns & How To','','craft_supplies_and_tools.patterns_and_how_to',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1474,6345,'Craft Supplies & Tools > Patterns & How To > Books & Magazines','','craft_supplies_and_tools.patterns_and_how_to.books_and_magazines',1473,1);
INSERT INTO `ps_etsy_categories` VALUES (1475,6346,'Craft Supplies & Tools > Patterns & How To > Kits','','craft_supplies_and_tools.patterns_and_how_to.kits',1473,1);
INSERT INTO `ps_etsy_categories` VALUES (1476,6343,'Craft Supplies & Tools > Patterns & How To > Patterns & Blueprints','','craft_supplies_and_tools.patterns_and_how_to.patterns_and_blueprints',1473,1);
INSERT INTO `ps_etsy_categories` VALUES (1477,6347,'Craft Supplies & Tools > Patterns & How To > Recipes','','craft_supplies_and_tools.patterns_and_how_to.recipes',1473,1);
INSERT INTO `ps_etsy_categories` VALUES (1478,6344,'Craft Supplies & Tools > Patterns & How To > Tutorials','','craft_supplies_and_tools.patterns_and_how_to.tutorials',1473,1);
INSERT INTO `ps_etsy_categories` VALUES (1479,6236,'Craft Supplies & Tools > Pens, Pencils & Marking Tools','','craft_supplies_and_tools.pens_pencils_and_marking_tools',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1480,6407,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Chalk','','craft_supplies_and_tools.pens_pencils_and_marking_tools.chalk',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1481,6409,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Charcoals','','craft_supplies_and_tools.pens_pencils_and_marking_tools.charcoals',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1482,6413,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Correction Fluid & Tape','','craft_supplies_and_tools.pens_pencils_and_marking_tools.correction_fluid_and_tape',1479,0);
INSERT INTO `ps_etsy_categories` VALUES (1483,6754,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Correction Fluid & Tape > Correction Fluid','','craft_supplies_and_tools.pens_pencils_and_marking_tools.correction_fluid_and_tape.correction_fluid',1482,1);
INSERT INTO `ps_etsy_categories` VALUES (1484,6755,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Correction Fluid & Tape > Correction Tape','','craft_supplies_and_tools.pens_pencils_and_marking_tools.correction_fluid_and_tape.correction_tape',1482,1);
INSERT INTO `ps_etsy_categories` VALUES (1485,6408,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Crayons','','craft_supplies_and_tools.pens_pencils_and_marking_tools.crayons',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1486,6410,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Erasers','','craft_supplies_and_tools.pens_pencils_and_marking_tools.erasers',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1487,6405,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Lead Refills','','craft_supplies_and_tools.pens_pencils_and_marking_tools.lead_refills',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1488,6406,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Markers','','craft_supplies_and_tools.pens_pencils_and_marking_tools.markers',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1489,6402,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Nibs & Nib Holders','','craft_supplies_and_tools.pens_pencils_and_marking_tools.nibs_and_nib_holders',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1490,6412,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pastels','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pastels',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1491,6403,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pen Refills','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pen_refills',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1492,6411,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pencil Sharpeners','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pencil_sharpeners',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1493,6404,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pencils','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pencils',1479,0);
INSERT INTO `ps_etsy_categories` VALUES (1494,6753,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pencils > Chalk Pencils','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pencils.chalk_pencils',1493,1);
INSERT INTO `ps_etsy_categories` VALUES (1495,6751,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pencils > Colored Pencils','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pencils.colored_pencils',1493,1);
INSERT INTO `ps_etsy_categories` VALUES (1496,6752,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pencils > Graphite Pencils','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pencils.graphite_pencils',1493,1);
INSERT INTO `ps_etsy_categories` VALUES (1497,6401,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pens','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pens',1479,0);
INSERT INTO `ps_etsy_categories` VALUES (1498,6745,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pens > Ballpoint Pens','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pens.ballpoint_pens',1497,1);
INSERT INTO `ps_etsy_categories` VALUES (1499,9336,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pens > Brush Pens','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pens.brush_pens',1497,1);
INSERT INTO `ps_etsy_categories` VALUES (1500,6750,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pens > Dip Pens','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pens.dip_pens',1497,1);
INSERT INTO `ps_etsy_categories` VALUES (1501,6747,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pens > Felt Tip Pens','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pens.felt_tip_pens',1497,1);
INSERT INTO `ps_etsy_categories` VALUES (1502,6746,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pens > Fountain Pens','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pens.fountain_pens',1497,1);
INSERT INTO `ps_etsy_categories` VALUES (1503,6748,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pens > Gel Pens','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pens.gel_pens',1497,1);
INSERT INTO `ps_etsy_categories` VALUES (1504,6749,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Pens > Rollerball Pens','','craft_supplies_and_tools.pens_pencils_and_marking_tools.pens.rollerball_pens',1497,1);
INSERT INTO `ps_etsy_categories` VALUES (1505,6987,'Craft Supplies & Tools > Pens, Pencils & Marking Tools > Printing Blocks & Type','','craft_supplies_and_tools.pens_pencils_and_marking_tools.printing_blocks_and_type',1479,1);
INSERT INTO `ps_etsy_categories` VALUES (1506,6231,'Craft Supplies & Tools > Raw Materials','','craft_supplies_and_tools.raw_materials',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1507,9186,'Craft Supplies & Tools > Raw Materials > Clay & Ceramic','','craft_supplies_and_tools.raw_materials.clay_and_ceramic',1506,0);
INSERT INTO `ps_etsy_categories` VALUES (1508,9119,'Craft Supplies & Tools > Raw Materials > Clay & Ceramic > Ceramic','','craft_supplies_and_tools.raw_materials.clay_and_ceramic.ceramic',1507,1);
INSERT INTO `ps_etsy_categories` VALUES (1509,6350,'Craft Supplies & Tools > Raw Materials > Clay & Ceramic > Clay','','craft_supplies_and_tools.raw_materials.clay_and_ceramic.clay',1507,1);
INSERT INTO `ps_etsy_categories` VALUES (1510,6363,'Craft Supplies & Tools > Raw Materials > Concrete & Cement','','craft_supplies_and_tools.raw_materials.concrete_and_cement',1506,0);
INSERT INTO `ps_etsy_categories` VALUES (1511,6662,'Craft Supplies & Tools > Raw Materials > Concrete & Cement > Grout','','craft_supplies_and_tools.raw_materials.concrete_and_cement.grout',1510,1);
INSERT INTO `ps_etsy_categories` VALUES (1512,6663,'Craft Supplies & Tools > Raw Materials > Concrete & Cement > Plaster','','craft_supplies_and_tools.raw_materials.concrete_and_cement.plaster',1510,1);
INSERT INTO `ps_etsy_categories` VALUES (1513,6362,'Craft Supplies & Tools > Raw Materials > Cork','','craft_supplies_and_tools.raw_materials.cork',1506,1);
INSERT INTO `ps_etsy_categories` VALUES (1514,6352,'Craft Supplies & Tools > Raw Materials > Glass','','craft_supplies_and_tools.raw_materials.glass',1506,1);
INSERT INTO `ps_etsy_categories` VALUES (1515,6353,'Craft Supplies & Tools > Raw Materials > Leather','','craft_supplies_and_tools.raw_materials.leather',1506,1);
INSERT INTO `ps_etsy_categories` VALUES (1516,6355,'Craft Supplies & Tools > Raw Materials > Metal','','craft_supplies_and_tools.raw_materials.metal',1506,0);
INSERT INTO `ps_etsy_categories` VALUES (1517,6646,'Craft Supplies & Tools > Raw Materials > Metal > Foil','','craft_supplies_and_tools.raw_materials.metal.foil',1516,1);
INSERT INTO `ps_etsy_categories` VALUES (1518,6645,'Craft Supplies & Tools > Raw Materials > Metal > Solder','','craft_supplies_and_tools.raw_materials.metal.solder',1516,1);
INSERT INTO `ps_etsy_categories` VALUES (1519,6356,'Craft Supplies & Tools > Raw Materials > Plastic','','craft_supplies_and_tools.raw_materials.plastic',1506,1);
INSERT INTO `ps_etsy_categories` VALUES (1520,6357,'Craft Supplies & Tools > Raw Materials > Resin','','craft_supplies_and_tools.raw_materials.resin',1506,0);
INSERT INTO `ps_etsy_categories` VALUES (1521,6647,'Craft Supplies & Tools > Raw Materials > Resin > Epoxy Resin','','craft_supplies_and_tools.raw_materials.resin.epoxy_resin',1520,1);
INSERT INTO `ps_etsy_categories` VALUES (1522,6358,'Craft Supplies & Tools > Raw Materials > Silicone & Rubber','','craft_supplies_and_tools.raw_materials.silicone_and_rubber',1506,1);
INSERT INTO `ps_etsy_categories` VALUES (1523,6359,'Craft Supplies & Tools > Raw Materials > Stones & Rocks','','craft_supplies_and_tools.raw_materials.stones_and_rocks',1506,0);
INSERT INTO `ps_etsy_categories` VALUES (1524,6650,'Craft Supplies & Tools > Raw Materials > Stones & Rocks > Fossils & Specimens','','craft_supplies_and_tools.raw_materials.stones_and_rocks.fossils_and_specimens',1523,1);
INSERT INTO `ps_etsy_categories` VALUES (1525,6652,'Craft Supplies & Tools > Raw Materials > Stones & Rocks > Gravel','','craft_supplies_and_tools.raw_materials.stones_and_rocks.gravel',1523,1);
INSERT INTO `ps_etsy_categories` VALUES (1526,6649,'Craft Supplies & Tools > Raw Materials > Stones & Rocks > Minerals','','craft_supplies_and_tools.raw_materials.stones_and_rocks.minerals',1523,1);
INSERT INTO `ps_etsy_categories` VALUES (1527,6653,'Craft Supplies & Tools > Raw Materials > Stones & Rocks > Sand','','craft_supplies_and_tools.raw_materials.stones_and_rocks.sand',1523,1);
INSERT INTO `ps_etsy_categories` VALUES (1528,6651,'Craft Supplies & Tools > Raw Materials > Stones & Rocks > Stones & Pebbles','','craft_supplies_and_tools.raw_materials.stones_and_rocks.stones_and_pebbles',1523,1);
INSERT INTO `ps_etsy_categories` VALUES (1529,6360,'Craft Supplies & Tools > Raw Materials > Wax','','craft_supplies_and_tools.raw_materials.wax',1506,1);
INSERT INTO `ps_etsy_categories` VALUES (1530,6361,'Craft Supplies & Tools > Raw Materials > Wood','','craft_supplies_and_tools.raw_materials.wood',1506,0);
INSERT INTO `ps_etsy_categories` VALUES (1531,6658,'Craft Supplies & Tools > Raw Materials > Wood > Bark','','craft_supplies_and_tools.raw_materials.wood.bark',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1532,6655,'Craft Supplies & Tools > Raw Materials > Wood > Boards & Planks','','craft_supplies_and_tools.raw_materials.wood.boards_and_planks',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1533,9325,'Craft Supplies & Tools > Raw Materials > Wood > Dowels','','craft_supplies_and_tools.raw_materials.wood.dowels',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1534,6660,'Craft Supplies & Tools > Raw Materials > Wood > Driftwood & Branches','','craft_supplies_and_tools.raw_materials.wood.driftwood_and_branches',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1535,6656,'Craft Supplies & Tools > Raw Materials > Wood > Lattice','','craft_supplies_and_tools.raw_materials.wood.lattice',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1536,6657,'Craft Supplies & Tools > Raw Materials > Wood > Logs','','craft_supplies_and_tools.raw_materials.wood.logs',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1537,6654,'Craft Supplies & Tools > Raw Materials > Wood > Plywood & Panels','','craft_supplies_and_tools.raw_materials.wood.plywood_and_panels',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1538,6661,'Craft Supplies & Tools > Raw Materials > Wood > Popsicle Sticks','','craft_supplies_and_tools.raw_materials.wood.popsicle_sticks',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1539,6659,'Craft Supplies & Tools > Raw Materials > Wood > Tree Slices','','craft_supplies_and_tools.raw_materials.wood.tree_slices',1530,1);
INSERT INTO `ps_etsy_categories` VALUES (1540,6244,'Craft Supplies & Tools > Safety & Cleaning Supplies','','craft_supplies_and_tools.safety_and_cleaning_supplies',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1541,6448,'Craft Supplies & Tools > Safety & Cleaning Supplies > Cleaning Supplies','','craft_supplies_and_tools.safety_and_cleaning_supplies.cleaning_supplies',1540,0);
INSERT INTO `ps_etsy_categories` VALUES (1542,6790,'Craft Supplies & Tools > Safety & Cleaning Supplies > Cleaning Supplies > Buckets','','craft_supplies_and_tools.safety_and_cleaning_supplies.cleaning_supplies.buckets',1541,1);
INSERT INTO `ps_etsy_categories` VALUES (1543,6635,'Craft Supplies & Tools > Safety & Cleaning Supplies > Cleaning Supplies > Dropcloths & Tarps','','craft_supplies_and_tools.safety_and_cleaning_supplies.cleaning_supplies.dropcloths_and_tarps',1541,1);
INSERT INTO `ps_etsy_categories` VALUES (1544,9339,'Craft Supplies & Tools > Safety & Cleaning Supplies > Cleaning Supplies > Polishing Cloths','','craft_supplies_and_tools.safety_and_cleaning_supplies.cleaning_supplies.polishing_cloths',1541,1);
INSERT INTO `ps_etsy_categories` VALUES (1545,6791,'Craft Supplies & Tools > Safety & Cleaning Supplies > Cleaning Supplies > Sponges','','craft_supplies_and_tools.safety_and_cleaning_supplies.cleaning_supplies.sponges',1541,1);
INSERT INTO `ps_etsy_categories` VALUES (1546,6447,'Craft Supplies & Tools > Safety & Cleaning Supplies > Safety Supplies','','craft_supplies_and_tools.safety_and_cleaning_supplies.safety_supplies',1540,0);
INSERT INTO `ps_etsy_categories` VALUES (1547,6788,'Craft Supplies & Tools > Safety & Cleaning Supplies > Safety Supplies > Face Shields','','craft_supplies_and_tools.safety_and_cleaning_supplies.safety_supplies.face_shields',1546,1);
INSERT INTO `ps_etsy_categories` VALUES (1548,6787,'Craft Supplies & Tools > Safety & Cleaning Supplies > Safety Supplies > Glasses & Goggles','','craft_supplies_and_tools.safety_and_cleaning_supplies.safety_supplies.glasses_and_goggles',1546,1);
INSERT INTO `ps_etsy_categories` VALUES (1549,6786,'Craft Supplies & Tools > Safety & Cleaning Supplies > Safety Supplies > Gloves','','craft_supplies_and_tools.safety_and_cleaning_supplies.safety_supplies.gloves',1546,1);
INSERT INTO `ps_etsy_categories` VALUES (1550,6789,'Craft Supplies & Tools > Safety & Cleaning Supplies > Safety Supplies > Protective Clothing','','craft_supplies_and_tools.safety_and_cleaning_supplies.safety_supplies.protective_clothing',1546,1);
INSERT INTO `ps_etsy_categories` VALUES (1551,6232,'Craft Supplies & Tools > Stamps & Seals','','craft_supplies_and_tools.stamps_and_seals',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1552,6367,'Craft Supplies & Tools > Stamps & Seals > Seals','','craft_supplies_and_tools.stamps_and_seals.seals',1551,0);
INSERT INTO `ps_etsy_categories` VALUES (1553,6666,'Craft Supplies & Tools > Stamps & Seals > Seals > Embossers','','craft_supplies_and_tools.stamps_and_seals.seals.embossers',1552,1);
INSERT INTO `ps_etsy_categories` VALUES (1554,6664,'Craft Supplies & Tools > Stamps & Seals > Seals > Sealing Stamps','','craft_supplies_and_tools.stamps_and_seals.seals.sealing_stamps',1552,1);
INSERT INTO `ps_etsy_categories` VALUES (1555,6665,'Craft Supplies & Tools > Stamps & Seals > Seals > Wax Seals','','craft_supplies_and_tools.stamps_and_seals.seals.wax_seals',1552,1);
INSERT INTO `ps_etsy_categories` VALUES (1556,6368,'Craft Supplies & Tools > Stamps & Seals > Stamp Blocks','','craft_supplies_and_tools.stamps_and_seals.stamp_blocks',1551,1);
INSERT INTO `ps_etsy_categories` VALUES (1557,6369,'Craft Supplies & Tools > Stamps & Seals > Stamp Holders','','craft_supplies_and_tools.stamps_and_seals.stamp_holders',1551,1);
INSERT INTO `ps_etsy_categories` VALUES (1558,6365,'Craft Supplies & Tools > Stamps & Seals > Stamps','','craft_supplies_and_tools.stamps_and_seals.stamps',1551,1);
INSERT INTO `ps_etsy_categories` VALUES (1559,6233,'Craft Supplies & Tools > Storage & Organization','','craft_supplies_and_tools.storage_and_organization',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1560,6381,'Craft Supplies & Tools > Storage & Organization > Caddies & Holders','','craft_supplies_and_tools.storage_and_organization.caddies_and_holders',1559,1);
INSERT INTO `ps_etsy_categories` VALUES (1561,6372,'Craft Supplies & Tools > Storage & Organization > Containers','','craft_supplies_and_tools.storage_and_organization.containers',1559,0);
INSERT INTO `ps_etsy_categories` VALUES (1562,6378,'Craft Supplies & Tools > Storage & Organization > Containers > Barrels','','craft_supplies_and_tools.storage_and_organization.containers.barrels',1561,1);
INSERT INTO `ps_etsy_categories` VALUES (1563,6370,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars',1561,0);
INSERT INTO `ps_etsy_categories` VALUES (1564,6668,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Bottles','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.bottles',1563,0);
INSERT INTO `ps_etsy_categories` VALUES (1565,6896,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Bottles > Carboys','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.bottles.carboys',1564,1);
INSERT INTO `ps_etsy_categories` VALUES (1566,6895,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Bottles > Squeeze Bottles','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.bottles.squeeze_bottles',1564,1);
INSERT INTO `ps_etsy_categories` VALUES (1567,6669,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Cans & Canisters','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.cans_and_canisters',1563,0);
INSERT INTO `ps_etsy_categories` VALUES (1568,6898,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Cans & Canisters > Canisters','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.cans_and_canisters.canisters',1567,1);
INSERT INTO `ps_etsy_categories` VALUES (1569,6897,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Cans & Canisters > Cans','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.cans_and_canisters.cans',1567,1);
INSERT INTO `ps_etsy_categories` VALUES (1570,6667,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Jars','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.jars',1563,0);
INSERT INTO `ps_etsy_categories` VALUES (1571,6894,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Jars > Canning Jars','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.jars.canning_jars',1570,1);
INSERT INTO `ps_etsy_categories` VALUES (1572,6670,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Lids, Rings & Caps','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.lids_rings_and_caps',1563,0);
INSERT INTO `ps_etsy_categories` VALUES (1573,6899,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Lids, Rings & Caps > Bottle Caps','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.lids_rings_and_caps.bottle_caps',1572,1);
INSERT INTO `ps_etsy_categories` VALUES (1574,6902,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Lids, Rings & Caps > Corks','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.lids_rings_and_caps.corks',1572,1);
INSERT INTO `ps_etsy_categories` VALUES (1575,6901,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Lids, Rings & Caps > Jar Rings','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.lids_rings_and_caps.jar_rings',1572,1);
INSERT INTO `ps_etsy_categories` VALUES (1576,6900,'Craft Supplies & Tools > Storage & Organization > Containers > Bottles, Cans & Jars > Lids, Rings & Caps > Lids','','craft_supplies_and_tools.storage_and_organization.containers.bottles_cans_and_jars.lids_rings_and_caps.lids',1572,1);
INSERT INTO `ps_etsy_categories` VALUES (1577,6371,'Craft Supplies & Tools > Storage & Organization > Containers > Boxes, Baskets & Bins','','craft_supplies_and_tools.storage_and_organization.containers.boxes_baskets_and_bins',1561,0);
INSERT INTO `ps_etsy_categories` VALUES (1578,6673,'Craft Supplies & Tools > Storage & Organization > Containers > Boxes, Baskets & Bins > Baskets','','craft_supplies_and_tools.storage_and_organization.containers.boxes_baskets_and_bins.baskets',1577,1);
INSERT INTO `ps_etsy_categories` VALUES (1579,6672,'Craft Supplies & Tools > Storage & Organization > Containers > Boxes, Baskets & Bins > Bins','','craft_supplies_and_tools.storage_and_organization.containers.boxes_baskets_and_bins.bins',1577,1);
INSERT INTO `ps_etsy_categories` VALUES (1580,6671,'Craft Supplies & Tools > Storage & Organization > Containers > Boxes, Baskets & Bins > Boxes','','craft_supplies_and_tools.storage_and_organization.containers.boxes_baskets_and_bins.boxes',1577,0);
INSERT INTO `ps_etsy_categories` VALUES (1581,6904,'Craft Supplies & Tools > Storage & Organization > Containers > Boxes, Baskets & Bins > Boxes > Hat Boxes','','craft_supplies_and_tools.storage_and_organization.containers.boxes_baskets_and_bins.boxes.hat_boxes',1580,1);
INSERT INTO `ps_etsy_categories` VALUES (1582,6903,'Craft Supplies & Tools > Storage & Organization > Containers > Boxes, Baskets & Bins > Boxes > Tool Boxes','','craft_supplies_and_tools.storage_and_organization.containers.boxes_baskets_and_bins.boxes.tool_boxes',1580,1);
INSERT INTO `ps_etsy_categories` VALUES (1583,6375,'Craft Supplies & Tools > Storage & Organization > Containers > Cases','','craft_supplies_and_tools.storage_and_organization.containers.cases',1561,1);
INSERT INTO `ps_etsy_categories` VALUES (1584,6379,'Craft Supplies & Tools > Storage & Organization > Containers > Kegs','','craft_supplies_and_tools.storage_and_organization.containers.kegs',1561,1);
INSERT INTO `ps_etsy_categories` VALUES (1585,6373,'Craft Supplies & Tools > Storage & Organization > Containers > Sacks & Totes','','craft_supplies_and_tools.storage_and_organization.containers.sacks_and_totes',1561,1);
INSERT INTO `ps_etsy_categories` VALUES (1586,6376,'Craft Supplies & Tools > Storage & Organization > Containers > Tubes','','craft_supplies_and_tools.storage_and_organization.containers.tubes',1561,1);
INSERT INTO `ps_etsy_categories` VALUES (1587,6380,'Craft Supplies & Tools > Storage & Organization > Containers > Yarn Bowls','','craft_supplies_and_tools.storage_and_organization.containers.yarn_bowls',1561,1);
INSERT INTO `ps_etsy_categories` VALUES (1588,6384,'Craft Supplies & Tools > Storage & Organization > Displays','','craft_supplies_and_tools.storage_and_organization.displays',1559,0);
INSERT INTO `ps_etsy_categories` VALUES (1589,6675,'Craft Supplies & Tools > Storage & Organization > Displays > Display Cases & Cabinets','','craft_supplies_and_tools.storage_and_organization.displays.display_cases_and_cabinets',1588,1);
INSERT INTO `ps_etsy_categories` VALUES (1590,6677,'Craft Supplies & Tools > Storage & Organization > Displays > Dress Forms & Mannequins','','craft_supplies_and_tools.storage_and_organization.displays.dress_forms_and_mannequins',1588,0);
INSERT INTO `ps_etsy_categories` VALUES (1591,9326,'Craft Supplies & Tools > Storage & Organization > Displays > Dress Forms & Mannequins > Dress Forms','','craft_supplies_and_tools.storage_and_organization.displays.dress_forms_and_mannequins.dress_forms',1590,1);
INSERT INTO `ps_etsy_categories` VALUES (1592,9327,'Craft Supplies & Tools > Storage & Organization > Displays > Dress Forms & Mannequins > Mannequins','','craft_supplies_and_tools.storage_and_organization.displays.dress_forms_and_mannequins.mannequins',1590,1);
INSERT INTO `ps_etsy_categories` VALUES (1593,6676,'Craft Supplies & Tools > Storage & Organization > Displays > Hat Forms & Stands','','craft_supplies_and_tools.storage_and_organization.displays.hat_forms_and_stands',1588,1);
INSERT INTO `ps_etsy_categories` VALUES (1594,6674,'Craft Supplies & Tools > Storage & Organization > Displays > Jewelry Displays','','craft_supplies_and_tools.storage_and_organization.displays.jewelry_displays',1588,0);
INSERT INTO `ps_etsy_categories` VALUES (1595,6905,'Craft Supplies & Tools > Storage & Organization > Displays > Jewelry Displays > Jewelry Cards','','craft_supplies_and_tools.storage_and_organization.displays.jewelry_displays.jewelry_cards',1594,1);
INSERT INTO `ps_etsy_categories` VALUES (1596,6382,'Craft Supplies & Tools > Storage & Organization > Portfolios','','craft_supplies_and_tools.storage_and_organization.portfolios',1559,1);
INSERT INTO `ps_etsy_categories` VALUES (1597,6374,'Craft Supplies & Tools > Storage & Organization > Racks & Shelves','','craft_supplies_and_tools.storage_and_organization.racks_and_shelves',1559,1);
INSERT INTO `ps_etsy_categories` VALUES (1598,6377,'Craft Supplies & Tools > Storage & Organization > Trays & Boards','','craft_supplies_and_tools.storage_and_organization.trays_and_boards',1559,1);
INSERT INTO `ps_etsy_categories` VALUES (1599,6385,'Craft Supplies & Tools > Storage & Organization > Workbenches','','craft_supplies_and_tools.storage_and_organization.workbenches',1559,0);
INSERT INTO `ps_etsy_categories` VALUES (1600,6680,'Craft Supplies & Tools > Storage & Organization > Workbenches > Bench Blocks','','craft_supplies_and_tools.storage_and_organization.workbenches.bench_blocks',1599,1);
INSERT INTO `ps_etsy_categories` VALUES (1601,6679,'Craft Supplies & Tools > Storage & Organization > Workbenches > Bench Pins','','craft_supplies_and_tools.storage_and_organization.workbenches.bench_pins',1599,1);
INSERT INTO `ps_etsy_categories` VALUES (1602,6678,'Craft Supplies & Tools > Storage & Organization > Workbenches > Benches','','craft_supplies_and_tools.storage_and_organization.workbenches.benches',1599,1);
INSERT INTO `ps_etsy_categories` VALUES (1603,9192,'Craft Supplies & Tools > Storage & Organization > Workbenches > Workbench Storage','','craft_supplies_and_tools.storage_and_organization.workbenches.workbench_storage',1599,1);
INSERT INTO `ps_etsy_categories` VALUES (1604,6234,'Craft Supplies & Tools > String, Cord & Wire','','craft_supplies_and_tools.string_cord_and_wire',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1605,6389,'Craft Supplies & Tools > String, Cord & Wire > Cord','','craft_supplies_and_tools.string_cord_and_wire.cord',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1606,9328,'Craft Supplies & Tools > String, Cord & Wire > Fishing Line','','craft_supplies_and_tools.string_cord_and_wire.fishing_line',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1607,6394,'Craft Supplies & Tools > String, Cord & Wire > Leather Lacing','','craft_supplies_and_tools.string_cord_and_wire.leather_lacing',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1608,6386,'Craft Supplies & Tools > String, Cord & Wire > Rope','','craft_supplies_and_tools.string_cord_and_wire.rope',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1609,6392,'Craft Supplies & Tools > String, Cord & Wire > Stems & Pipe Cleaners','','craft_supplies_and_tools.string_cord_and_wire.stems_and_pipe_cleaners',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1610,6388,'Craft Supplies & Tools > String, Cord & Wire > String','','craft_supplies_and_tools.string_cord_and_wire.string',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1611,6390,'Craft Supplies & Tools > String, Cord & Wire > Ties','','craft_supplies_and_tools.string_cord_and_wire.ties',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1612,6387,'Craft Supplies & Tools > String, Cord & Wire > Twine','','craft_supplies_and_tools.string_cord_and_wire.twine',1604,0);
INSERT INTO `ps_etsy_categories` VALUES (1613,6681,'Craft Supplies & Tools > String, Cord & Wire > Twine > Baker\'s Twine','','craft_supplies_and_tools.string_cord_and_wire.twine.bakers_twine',1612,1);
INSERT INTO `ps_etsy_categories` VALUES (1614,6393,'Craft Supplies & Tools > String, Cord & Wire > Wicks','','craft_supplies_and_tools.string_cord_and_wire.wicks',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1615,6391,'Craft Supplies & Tools > String, Cord & Wire > Wire','','craft_supplies_and_tools.string_cord_and_wire.wire',1604,1);
INSERT INTO `ps_etsy_categories` VALUES (1616,6235,'Craft Supplies & Tools > Tools & Equipment','','craft_supplies_and_tools.tools_and_equipment',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1617,6398,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines',1616,0);
INSERT INTO `ps_etsy_categories` VALUES (1618,6714,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > 3D Printers','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.3d_printers',1617,0);
INSERT INTO `ps_etsy_categories` VALUES (1619,6980,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > 3D Printers > 3D Printer Filament','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.3d_printers.3d_printer_filament',1618,1);
INSERT INTO `ps_etsy_categories` VALUES (1620,6720,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Button Makers','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.button_makers',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1621,9194,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Compressors','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.compressors',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1622,6728,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Crucibles','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.crucibles',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1623,6718,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Die Cut Machines','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.die_cut_machines',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1624,6726,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Grinders','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.grinders',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1625,6719,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Kilns','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.kilns',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1626,6727,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Kneaders','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.kneaders',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1627,6725,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Lathes','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.lathes',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1628,6715,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms',1617,0);
INSERT INTO `ps_etsy_categories` VALUES (1629,6985,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Bead Looms','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.bead_looms',1628,1);
INSERT INTO `ps_etsy_categories` VALUES (1630,6981,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Heddles','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.heddles',1628,1);
INSERT INTO `ps_etsy_categories` VALUES (1631,6983,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Knitting Looms','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.knitting_looms',1628,1);
INSERT INTO `ps_etsy_categories` VALUES (1632,6982,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Shuttles','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.shuttles',1628,1);
INSERT INTO `ps_etsy_categories` VALUES (1633,6986,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Warping Tools','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.warping_tools',1628,0);
INSERT INTO `ps_etsy_categories` VALUES (1634,7053,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Warping Tools > Warping Boards','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.warping_tools.warping_boards',1633,1);
INSERT INTO `ps_etsy_categories` VALUES (1635,7055,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Warping Tools > Warping Mills','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.warping_tools.warping_mills',1633,1);
INSERT INTO `ps_etsy_categories` VALUES (1636,7054,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Warping Tools > Warping Pegs','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.warping_tools.warping_pegs',1633,1);
INSERT INTO `ps_etsy_categories` VALUES (1637,6984,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Looms > Weaving Cards','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.looms.weaving_cards',1628,1);
INSERT INTO `ps_etsy_categories` VALUES (1638,6721,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Pottery Wheels','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.pottery_wheels',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1639,6716,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Presses','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.presses',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1640,6717,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Sewing & Needlework Machines','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.sewing_and_needlework_machines',1617,0);
INSERT INTO `ps_etsy_categories` VALUES (1641,6988,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Sewing & Needlework Machines > Embroidery Machines','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.sewing_and_needlework_machines.embroidery_machines',1640,1);
INSERT INTO `ps_etsy_categories` VALUES (1642,6991,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Sewing & Needlework Machines > Felting Machines','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.sewing_and_needlework_machines.felting_machines',1640,1);
INSERT INTO `ps_etsy_categories` VALUES (1643,6990,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Sewing & Needlework Machines > Knitting Machines','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.sewing_and_needlework_machines.knitting_machines',1640,1);
INSERT INTO `ps_etsy_categories` VALUES (1644,6989,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Sewing & Needlework Machines > Sewing Machines','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.sewing_and_needlework_machines.sewing_machines',1640,1);
INSERT INTO `ps_etsy_categories` VALUES (1645,6723,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Spinning Wheels','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.spinning_wheels',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1646,6722,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Tattoo Guns','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.tattoo_guns',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1647,6729,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Tumblers','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.tumblers',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1648,6724,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Wax Melters','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.wax_melters',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1649,9193,'Craft Supplies & Tools > Tools & Equipment > Equipment & Machines > Welding Machines','','craft_supplies_and_tools.tools_and_equipment.equipment_and_machines.welding_machines',1617,1);
INSERT INTO `ps_etsy_categories` VALUES (1650,6400,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware',1616,0);
INSERT INTO `ps_etsy_categories` VALUES (1651,6736,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Doors & Locks','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.doors_and_locks',1650,0);
INSERT INTO `ps_etsy_categories` VALUES (1652,7005,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Doors & Locks > Doors','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.doors_and_locks.doors',1651,1);
INSERT INTO `ps_etsy_categories` VALUES (1653,7006,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Doors & Locks > Keys','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.doors_and_locks.keys',1651,1);
INSERT INTO `ps_etsy_categories` VALUES (1654,7007,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Doors & Locks > Locks','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.doors_and_locks.locks',1651,1);
INSERT INTO `ps_etsy_categories` VALUES (1655,6741,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Fillers & Spackling','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.fillers_and_spackling',1650,0);
INSERT INTO `ps_etsy_categories` VALUES (1656,7009,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Fillers & Spackling > Spackling','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.fillers_and_spackling.spackling',1655,1);
INSERT INTO `ps_etsy_categories` VALUES (1657,7008,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Fillers & Spackling > Wood Fillers','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.fillers_and_spackling.wood_fillers',1655,1);
INSERT INTO `ps_etsy_categories` VALUES (1658,6735,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Finials','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.finials',1650,1);
INSERT INTO `ps_etsy_categories` VALUES (1659,6737,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Flooring','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.flooring',1650,1);
INSERT INTO `ps_etsy_categories` VALUES (1660,6742,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware',1650,0);
INSERT INTO `ps_etsy_categories` VALUES (1661,7015,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Framing Hardware','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.framing_hardware',1660,1);
INSERT INTO `ps_etsy_categories` VALUES (1662,7018,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Hinges','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.hinges',1660,1);
INSERT INTO `ps_etsy_categories` VALUES (1663,7016,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Mixed Hardware Sets','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.mixed_hardware_sets',1660,1);
INSERT INTO `ps_etsy_categories` VALUES (1664,7010,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Nails','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.nails',1660,1);
INSERT INTO `ps_etsy_categories` VALUES (1665,7012,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Nuts & Bolts','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.nuts_and_bolts',1660,0);
INSERT INTO `ps_etsy_categories` VALUES (1666,7057,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Nuts & Bolts > Bolts','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.nuts_and_bolts.bolts',1665,1);
INSERT INTO `ps_etsy_categories` VALUES (1667,7056,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Nuts & Bolts > Nuts','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.nuts_and_bolts.nuts',1665,1);
INSERT INTO `ps_etsy_categories` VALUES (1668,7017,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Screw Posts','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.screw_posts',1660,1);
INSERT INTO `ps_etsy_categories` VALUES (1669,7013,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Screws','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.screws',1660,1);
INSERT INTO `ps_etsy_categories` VALUES (1670,7011,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Staples','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.staples',1660,1);
INSERT INTO `ps_etsy_categories` VALUES (1671,7014,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Hardware > Tacks','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.hardware.tacks',1660,1);
INSERT INTO `ps_etsy_categories` VALUES (1672,6734,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Knobs & Pulls','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.knobs_and_pulls',1650,1);
INSERT INTO `ps_etsy_categories` VALUES (1673,6740,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Moldings & Trim','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.moldings_and_trim',1650,1);
INSERT INTO `ps_etsy_categories` VALUES (1674,6739,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Plumbing','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.plumbing',1650,0);
INSERT INTO `ps_etsy_categories` VALUES (1675,11351,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Plumbing > Faucets, Handles & Showerheads','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.plumbing.faucets_handles_and_showerheads',1674,1);
INSERT INTO `ps_etsy_categories` VALUES (1676,11352,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Plumbing > Plumbing Pipes & Fittings','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.plumbing.plumbing_pipes_and_fittings',1674,1);
INSERT INTO `ps_etsy_categories` VALUES (1677,11353,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Plumbing > Sinks & Basins','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.plumbing.sinks_and_basins',1674,1);
INSERT INTO `ps_etsy_categories` VALUES (1678,6738,'Craft Supplies & Tools > Tools & Equipment > Home Improvement & Hardware > Windows','','craft_supplies_and_tools.tools_and_equipment.home_improvement_and_hardware.windows',1650,1);
INSERT INTO `ps_etsy_categories` VALUES (1679,6399,'Craft Supplies & Tools > Tools & Equipment > Parts','','craft_supplies_and_tools.tools_and_equipment.parts',1616,0);
INSERT INTO `ps_etsy_categories` VALUES (1680,6732,'Craft Supplies & Tools > Tools & Equipment > Parts > Batteries','','craft_supplies_and_tools.tools_and_equipment.parts.batteries',1679,1);
INSERT INTO `ps_etsy_categories` VALUES (1681,6733,'Craft Supplies & Tools > Tools & Equipment > Parts > Computer Parts','','craft_supplies_and_tools.tools_and_equipment.parts.computer_parts',1679,0);
INSERT INTO `ps_etsy_categories` VALUES (1682,7004,'Craft Supplies & Tools > Tools & Equipment > Parts > Computer Parts > Circuit Boards','','craft_supplies_and_tools.tools_and_equipment.parts.computer_parts.circuit_boards',1681,1);
INSERT INTO `ps_etsy_categories` VALUES (1683,6731,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts',1679,0);
INSERT INTO `ps_etsy_categories` VALUES (1684,7000,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Bulbs & Tubes','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.bulbs_and_tubes',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1685,6996,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Electrode Holders','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.electrode_holders',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1686,6995,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Electrodes','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.electrodes',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1687,7001,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Motors','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.motors',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1688,7002,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Raspberry Pi & Arduino','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.raspberry_pi_and_arduino',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1689,6998,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Robotics Parts','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.robotics_parts',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1690,6999,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Sockets & Wiring','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.sockets_and_wiring',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1691,7003,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Testing & Calibration Supplies','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.testing_and_calibration_supplies',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1692,6997,'Craft Supplies & Tools > Tools & Equipment > Parts > Electrical Parts > Transformers','','craft_supplies_and_tools.tools_and_equipment.parts.electrical_parts.transformers',1683,1);
INSERT INTO `ps_etsy_categories` VALUES (1693,6730,'Craft Supplies & Tools > Tools & Equipment > Parts > Tool Parts & Accessories','','craft_supplies_and_tools.tools_and_equipment.parts.tool_parts_and_accessories',1679,0);
INSERT INTO `ps_etsy_categories` VALUES (1694,6994,'Craft Supplies & Tools > Tools & Equipment > Parts > Tool Parts & Accessories > Burs','','craft_supplies_and_tools.tools_and_equipment.parts.tool_parts_and_accessories.burs',1693,1);
INSERT INTO `ps_etsy_categories` VALUES (1695,9187,'Craft Supplies & Tools > Tools & Equipment > Parts > Tool Parts & Accessories > Dies','','craft_supplies_and_tools.tools_and_equipment.parts.tool_parts_and_accessories.dies',1693,1);
INSERT INTO `ps_etsy_categories` VALUES (1696,6992,'Craft Supplies & Tools > Tools & Equipment > Parts > Tool Parts & Accessories > Drill Bits','','craft_supplies_and_tools.tools_and_equipment.parts.tool_parts_and_accessories.drill_bits',1693,1);
INSERT INTO `ps_etsy_categories` VALUES (1697,6993,'Craft Supplies & Tools > Tools & Equipment > Parts > Tool Parts & Accessories > Rotary Discs','','craft_supplies_and_tools.tools_and_equipment.parts.tool_parts_and_accessories.rotary_discs',1693,1);
INSERT INTO `ps_etsy_categories` VALUES (1698,9185,'Craft Supplies & Tools > Tools & Equipment > Parts > Tool Parts & Accessories > Sewing Machine Parts','','craft_supplies_and_tools.tools_and_equipment.parts.tool_parts_and_accessories.sewing_machine_parts',1693,1);
INSERT INTO `ps_etsy_categories` VALUES (1699,6397,'Craft Supplies & Tools > Tools & Equipment > Tool Sets','','craft_supplies_and_tools.tools_and_equipment.tool_sets',1616,1);
INSERT INTO `ps_etsy_categories` VALUES (1700,6396,'Craft Supplies & Tools > Tools & Equipment > Tools','','craft_supplies_and_tools.tools_and_equipment.tools',1616,0);
INSERT INTO `ps_etsy_categories` VALUES (1701,6682,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1702,6906,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Awls','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.awls',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1703,6907,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Chisels','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.chisels',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1704,6955,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Creasers','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.creasers',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1705,6700,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Edge Bevelers','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.edge_bevelers',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1706,6908,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Picks','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.picks',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1707,6698,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Pricking Irons','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.pricking_irons',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1708,6699,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Pricking Wheels','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.pricking_wheels',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1709,6909,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Scrapers','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.scrapers',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1710,6910,'Craft Supplies & Tools > Tools & Equipment > Tools > Chisels, Picks & Awls > Styluses','','craft_supplies_and_tools.tools_and_equipment.tools.chisels_picks_and_awls.styluses',1701,1);
INSERT INTO `ps_etsy_categories` VALUES (1711,6694,'Craft Supplies & Tools > Tools & Equipment > Tools > Dispensing & Extruding','','craft_supplies_and_tools.tools_and_equipment.tools.dispensing_and_extruding',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1712,6946,'Craft Supplies & Tools > Tools & Equipment > Tools > Dispensing & Extruding > Ball Brause Sprinklers','','craft_supplies_and_tools.tools_and_equipment.tools.dispensing_and_extruding.ball_brause_sprinklers',1711,1);
INSERT INTO `ps_etsy_categories` VALUES (1713,6945,'Craft Supplies & Tools > Tools & Equipment > Tools > Dispensing & Extruding > Caulking Guns','','craft_supplies_and_tools.tools_and_equipment.tools.dispensing_and_extruding.caulking_guns',1711,1);
INSERT INTO `ps_etsy_categories` VALUES (1714,6947,'Craft Supplies & Tools > Tools & Equipment > Tools > Dispensing & Extruding > Clay Extruders','','craft_supplies_and_tools.tools_and_equipment.tools.dispensing_and_extruding.clay_extruders',1711,1);
INSERT INTO `ps_etsy_categories` VALUES (1715,6944,'Craft Supplies & Tools > Tools & Equipment > Tools > Dispensing & Extruding > Pumps','','craft_supplies_and_tools.tools_and_equipment.tools.dispensing_and_extruding.pumps',1711,1);
INSERT INTO `ps_etsy_categories` VALUES (1716,6705,'Craft Supplies & Tools > Tools & Equipment > Tools > Dispensing & Extruding > Tubing & Siphons','','craft_supplies_and_tools.tools_and_equipment.tools.dispensing_and_extruding.tubing_and_siphons',1711,1);
INSERT INTO `ps_etsy_categories` VALUES (1717,6697,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1718,6954,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Bead Rollers','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.bead_rollers',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1719,6683,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Clamps & Vises','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.clamps_and_vises',1717,0);
INSERT INTO `ps_etsy_categories` VALUES (1720,6911,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Clamps & Vises > Clamps','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.clamps_and_vises.clamps',1719,1);
INSERT INTO `ps_etsy_categories` VALUES (1721,6913,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Clamps & Vises > Jigs','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.clamps_and_vises.jigs',1719,1);
INSERT INTO `ps_etsy_categories` VALUES (1722,6914,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Clamps & Vises > Mandrels','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.clamps_and_vises.mandrels',1719,1);
INSERT INTO `ps_etsy_categories` VALUES (1723,6912,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Clamps & Vises > Vises','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.clamps_and_vises.vises',1719,1);
INSERT INTO `ps_etsy_categories` VALUES (1724,6963,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Drills','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.drills',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1725,9116,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Glue Guns','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.glue_guns',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1726,6684,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Hammers','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.hammers',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1727,6685,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Mallets','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.mallets',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1728,6686,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Mauls','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.mauls',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1729,6965,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Nailers & Nail Guns','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.nailers_and_nail_guns',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1730,9333,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Rivet Setters','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.rivet_setters',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1731,6690,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Screwdrivers','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.screwdrivers',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1732,6968,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Seam Stretchers','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.seam_stretchers',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1733,6964,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Soldering Guns & Irons','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.soldering_guns_and_irons',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1734,6967,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Staplers & Staple Guns','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.staplers_and_staple_guns',1717,0);
INSERT INTO `ps_etsy_categories` VALUES (1735,9332,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Staplers & Staple Guns > Staple Guns','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.staplers_and_staple_guns.staple_guns',1734,1);
INSERT INTO `ps_etsy_categories` VALUES (1736,9331,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Staplers & Staple Guns > Staplers','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.staplers_and_staple_guns.staplers',1734,1);
INSERT INTO `ps_etsy_categories` VALUES (1737,6966,'Craft Supplies & Tools > Tools & Equipment > Tools > Fastening & Attaching > Tack Removers','','craft_supplies_and_tools.tools_and_equipment.tools.fastening_and_attaching.tack_removers',1717,1);
INSERT INTO `ps_etsy_categories` VALUES (1738,6692,'Craft Supplies & Tools > Tools & Equipment > Tools > Filing & Sanding','','craft_supplies_and_tools.tools_and_equipment.tools.filing_and_sanding',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1739,6940,'Craft Supplies & Tools > Tools & Equipment > Tools > Filing & Sanding > Files','','craft_supplies_and_tools.tools_and_equipment.tools.filing_and_sanding.files',1738,1);
INSERT INTO `ps_etsy_categories` VALUES (1740,6688,'Craft Supplies & Tools > Tools & Equipment > Tools > Filing & Sanding > Planers & Jointers','','craft_supplies_and_tools.tools_and_equipment.tools.filing_and_sanding.planers_and_jointers',1738,1);
INSERT INTO `ps_etsy_categories` VALUES (1741,6943,'Craft Supplies & Tools > Tools & Equipment > Tools > Filing & Sanding > Polishers','','craft_supplies_and_tools.tools_and_equipment.tools.filing_and_sanding.polishers',1738,1);
INSERT INTO `ps_etsy_categories` VALUES (1742,6941,'Craft Supplies & Tools > Tools & Equipment > Tools > Filing & Sanding > Sanders','','craft_supplies_and_tools.tools_and_equipment.tools.filing_and_sanding.sanders',1738,1);
INSERT INTO `ps_etsy_categories` VALUES (1743,6942,'Craft Supplies & Tools > Tools & Equipment > Tools > Filing & Sanding > Sandpaper','','craft_supplies_and_tools.tools_and_equipment.tools.filing_and_sanding.sandpaper',1738,1);
INSERT INTO `ps_etsy_categories` VALUES (1744,10836,'Craft Supplies & Tools > Tools & Equipment > Tools > Gardening','','craft_supplies_and_tools.tools_and_equipment.tools.gardening',1700,1);
INSERT INTO `ps_etsy_categories` VALUES (1745,6711,'Craft Supplies & Tools > Tools & Equipment > Tools > Hooking','','craft_supplies_and_tools.tools_and_equipment.tools.hooking',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1746,6975,'Craft Supplies & Tools > Tools & Equipment > Tools > Hooking > Carving Hooks','','craft_supplies_and_tools.tools_and_equipment.tools.hooking.carving_hooks',1745,1);
INSERT INTO `ps_etsy_categories` VALUES (1747,6976,'Craft Supplies & Tools > Tools & Equipment > Tools > Hooking > Crochet Hooks','','craft_supplies_and_tools.tools_and_equipment.tools.hooking.crochet_hooks',1745,1);
INSERT INTO `ps_etsy_categories` VALUES (1748,6977,'Craft Supplies & Tools > Tools & Equipment > Tools > Hooking > Heddle Hooks','','craft_supplies_and_tools.tools_and_equipment.tools.hooking.heddle_hooks',1745,1);
INSERT INTO `ps_etsy_categories` VALUES (1749,6974,'Craft Supplies & Tools > Tools & Equipment > Tools > Hooking > Latch Hooks','','craft_supplies_and_tools.tools_and_equipment.tools.hooking.latch_hooks',1745,1);
INSERT INTO `ps_etsy_categories` VALUES (1750,6687,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1751,6919,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Calipers','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.calipers',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1752,6929,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Corner Squares','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.corner_squares',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1753,6920,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Dividers & Compasses','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.dividers_and_compasses',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1754,6918,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Jewelry Sizers','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.jewelry_sizers',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1755,6915,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Levels','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.levels',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1756,6924,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Measuring Grids','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.measuring_grids',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1757,6925,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Needle Gauges','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.needle_gauges',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1758,6927,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Protractors','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.protractors',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1759,6916,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Rulers & Yardsticks','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.rulers_and_yardsticks',1750,0);
INSERT INTO `ps_etsy_categories` VALUES (1760,9329,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Rulers & Yardsticks > Rulers','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.rulers_and_yardsticks.rulers',1759,1);
INSERT INTO `ps_etsy_categories` VALUES (1761,9330,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Rulers & Yardsticks > Yardsticks','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.rulers_and_yardsticks.yardsticks',1759,1);
INSERT INTO `ps_etsy_categories` VALUES (1762,6921,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Scales','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.scales',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1763,6930,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Shims','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.shims',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1764,6923,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Stitch Counters','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.stitch_counters',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1765,6926,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > T-Squares','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.t_squares',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1766,6917,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Tape Measures','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.tape_measures',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1767,6922,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Thermometers','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.thermometers',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1768,6928,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Triangles','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.triangles',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1769,6703,'Craft Supplies & Tools > Tools & Equipment > Tools > Levels & Measuring > Weights','','craft_supplies_and_tools.tools_and_equipment.tools.levels_and_measuring.weights',1750,1);
INSERT INTO `ps_etsy_categories` VALUES (1770,6689,'Craft Supplies & Tools > Tools & Equipment > Tools > Pliers, Tweezers & Tongs','','craft_supplies_and_tools.tools_and_equipment.tools.pliers_tweezers_and_tongs',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1771,6934,'Craft Supplies & Tools > Tools & Equipment > Tools > Pliers, Tweezers & Tongs > Hemostats','','craft_supplies_and_tools.tools_and_equipment.tools.pliers_tweezers_and_tongs.hemostats',1770,1);
INSERT INTO `ps_etsy_categories` VALUES (1772,6933,'Craft Supplies & Tools > Tools & Equipment > Tools > Pliers, Tweezers & Tongs > Pinchers','','craft_supplies_and_tools.tools_and_equipment.tools.pliers_tweezers_and_tongs.pinchers',1770,1);
INSERT INTO `ps_etsy_categories` VALUES (1773,6931,'Craft Supplies & Tools > Tools & Equipment > Tools > Pliers, Tweezers & Tongs > Pliers','','craft_supplies_and_tools.tools_and_equipment.tools.pliers_tweezers_and_tongs.pliers',1770,1);
INSERT INTO `ps_etsy_categories` VALUES (1774,6935,'Craft Supplies & Tools > Tools & Equipment > Tools > Pliers, Tweezers & Tongs > Tongs','','craft_supplies_and_tools.tools_and_equipment.tools.pliers_tweezers_and_tongs.tongs',1770,1);
INSERT INTO `ps_etsy_categories` VALUES (1775,6932,'Craft Supplies & Tools > Tools & Equipment > Tools > Pliers, Tweezers & Tongs > Tweezers','','craft_supplies_and_tools.tools_and_equipment.tools.pliers_tweezers_and_tongs.tweezers',1770,1);
INSERT INTO `ps_etsy_categories` VALUES (1776,9321,'Craft Supplies & Tools > Tools & Equipment > Tools > Raking & Scraping','','craft_supplies_and_tools.tools_and_equipment.tools.raking_and_scraping',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1777,9755,'Craft Supplies & Tools > Tools & Equipment > Tools > Raking & Scraping > Felting Mats','','craft_supplies_and_tools.tools_and_equipment.tools.raking_and_scraping.felting_mats',1776,1);
INSERT INTO `ps_etsy_categories` VALUES (1778,6708,'Craft Supplies & Tools > Tools & Equipment > Tools > Raking & Scraping > Felting Stones','','craft_supplies_and_tools.tools_and_equipment.tools.raking_and_scraping.felting_stones',1776,1);
INSERT INTO `ps_etsy_categories` VALUES (1779,6706,'Craft Supplies & Tools > Tools & Equipment > Tools > Raking & Scraping > Hand Carders','','craft_supplies_and_tools.tools_and_equipment.tools.raking_and_scraping.hand_carders',1776,1);
INSERT INTO `ps_etsy_categories` VALUES (1780,6701,'Craft Supplies & Tools > Tools & Equipment > Tools > Raking & Scraping > Rakes & Combs','','craft_supplies_and_tools.tools_and_equipment.tools.raking_and_scraping.rakes_and_combs',1776,1);
INSERT INTO `ps_etsy_categories` VALUES (1781,6950,'Craft Supplies & Tools > Tools & Equipment > Tools > Raking & Scraping > Squeegees','','craft_supplies_and_tools.tools_and_equipment.tools.raking_and_scraping.squeegees',1776,1);
INSERT INTO `ps_etsy_categories` VALUES (1782,9334,'Craft Supplies & Tools > Tools & Equipment > Tools > Raking & Scraping > Tapestry Beaters','','craft_supplies_and_tools.tools_and_equipment.tools.raking_and_scraping.tapestry_beaters',1776,1);
INSERT INTO `ps_etsy_categories` VALUES (1783,6707,'Craft Supplies & Tools > Tools & Equipment > Tools > Raking & Scraping > Washboards','','craft_supplies_and_tools.tools_and_equipment.tools.raking_and_scraping.washboards',1776,1);
INSERT INTO `ps_etsy_categories` VALUES (1784,6695,'Craft Supplies & Tools > Tools & Equipment > Tools > Rolling & Folding','','craft_supplies_and_tools.tools_and_equipment.tools.rolling_and_folding',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1785,6956,'Craft Supplies & Tools > Tools & Equipment > Tools > Rolling & Folding > Bone Folders','','craft_supplies_and_tools.tools_and_equipment.tools.rolling_and_folding.bone_folders',1784,1);
INSERT INTO `ps_etsy_categories` VALUES (1786,6951,'Craft Supplies & Tools > Tools & Equipment > Tools > Rolling & Folding > Brayers','','craft_supplies_and_tools.tools_and_equipment.tools.rolling_and_folding.brayers',1784,1);
INSERT INTO `ps_etsy_categories` VALUES (1787,6957,'Craft Supplies & Tools > Tools & Equipment > Tools > Rolling & Folding > Burnishers','','craft_supplies_and_tools.tools_and_equipment.tools.rolling_and_folding.burnishers',1784,1);
INSERT INTO `ps_etsy_categories` VALUES (1788,6953,'Craft Supplies & Tools > Tools & Equipment > Tools > Rolling & Folding > Felting Rollers','','craft_supplies_and_tools.tools_and_equipment.tools.rolling_and_folding.felting_rollers',1784,1);
INSERT INTO `ps_etsy_categories` VALUES (1789,6949,'Craft Supplies & Tools > Tools & Equipment > Tools > Rolling & Folding > Seam Rollers','','craft_supplies_and_tools.tools_and_equipment.tools.rolling_and_folding.seam_rollers',1784,1);
INSERT INTO `ps_etsy_categories` VALUES (1790,6948,'Craft Supplies & Tools > Tools & Equipment > Tools > Rolling & Folding > Slab Rollers','','craft_supplies_and_tools.tools_and_equipment.tools.rolling_and_folding.slab_rollers',1784,1);
INSERT INTO `ps_etsy_categories` VALUES (1791,6952,'Craft Supplies & Tools > Tools & Equipment > Tools > Rolling & Folding > Slotted Tools','','craft_supplies_and_tools.tools_and_equipment.tools.rolling_and_folding.slotted_tools',1784,1);
INSERT INTO `ps_etsy_categories` VALUES (1792,6712,'Craft Supplies & Tools > Tools & Equipment > Tools > Shaping & Modeling','','craft_supplies_and_tools.tools_and_equipment.tools.shaping_and_modeling',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1793,6704,'Craft Supplies & Tools > Tools & Equipment > Tools > Shaping & Modeling > Anvils','','craft_supplies_and_tools.tools_and_equipment.tools.shaping_and_modeling.anvils',1792,1);
INSERT INTO `ps_etsy_categories` VALUES (1794,6979,'Craft Supplies & Tools > Tools & Equipment > Tools > Shaping & Modeling > Millgrain Tools','','craft_supplies_and_tools.tools_and_equipment.tools.shaping_and_modeling.millgrain_tools',1792,1);
INSERT INTO `ps_etsy_categories` VALUES (1795,6978,'Craft Supplies & Tools > Tools & Equipment > Tools > Shaping & Modeling > Pushers','','craft_supplies_and_tools.tools_and_equipment.tools.shaping_and_modeling.pushers',1792,1);
INSERT INTO `ps_etsy_categories` VALUES (1796,6696,'Craft Supplies & Tools > Tools & Equipment > Tools > Spinning & Winding','','craft_supplies_and_tools.tools_and_equipment.tools.spinning_and_winding',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1797,6961,'Craft Supplies & Tools > Tools & Equipment > Tools > Spinning & Winding > Ball Winders','','craft_supplies_and_tools.tools_and_equipment.tools.spinning_and_winding.ball_winders',1796,1);
INSERT INTO `ps_etsy_categories` VALUES (1798,9184,'Craft Supplies & Tools > Tools & Equipment > Tools > Spinning & Winding > Cone Holders','','craft_supplies_and_tools.tools_and_equipment.tools.spinning_and_winding.cone_holders',1796,1);
INSERT INTO `ps_etsy_categories` VALUES (1799,6960,'Craft Supplies & Tools > Tools & Equipment > Tools > Spinning & Winding > Niddy Noddies','','craft_supplies_and_tools.tools_and_equipment.tools.spinning_and_winding.niddy_noddies',1796,1);
INSERT INTO `ps_etsy_categories` VALUES (1800,6959,'Craft Supplies & Tools > Tools & Equipment > Tools > Spinning & Winding > Nostepinnes','','craft_supplies_and_tools.tools_and_equipment.tools.spinning_and_winding.nostepinnes',1796,1);
INSERT INTO `ps_etsy_categories` VALUES (1801,6958,'Craft Supplies & Tools > Tools & Equipment > Tools > Spinning & Winding > Spindles','','craft_supplies_and_tools.tools_and_equipment.tools.spinning_and_winding.spindles',1796,1);
INSERT INTO `ps_etsy_categories` VALUES (1802,6962,'Craft Supplies & Tools > Tools & Equipment > Tools > Spinning & Winding > Yarn Swifts','','craft_supplies_and_tools.tools_and_equipment.tools.spinning_and_winding.yarn_swifts',1796,1);
INSERT INTO `ps_etsy_categories` VALUES (1803,6710,'Craft Supplies & Tools > Tools & Equipment > Tools > Torching & Heating','','craft_supplies_and_tools.tools_and_equipment.tools.torching_and_heating',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1804,6709,'Craft Supplies & Tools > Tools & Equipment > Tools > Torching & Heating > Annealing Pans','','craft_supplies_and_tools.tools_and_equipment.tools.torching_and_heating.annealing_pans',1803,1);
INSERT INTO `ps_etsy_categories` VALUES (1805,6971,'Craft Supplies & Tools > Tools & Equipment > Tools > Torching & Heating > Branding Irons','','craft_supplies_and_tools.tools_and_equipment.tools.torching_and_heating.branding_irons',1803,1);
INSERT INTO `ps_etsy_categories` VALUES (1806,6970,'Craft Supplies & Tools > Tools & Equipment > Tools > Torching & Heating > Hand Torches','','craft_supplies_and_tools.tools_and_equipment.tools.torching_and_heating.hand_torches',1803,1);
INSERT INTO `ps_etsy_categories` VALUES (1807,6973,'Craft Supplies & Tools > Tools & Equipment > Tools > Torching & Heating > Marvers','','craft_supplies_and_tools.tools_and_equipment.tools.torching_and_heating.marvers',1803,1);
INSERT INTO `ps_etsy_categories` VALUES (1808,9335,'Craft Supplies & Tools > Tools & Equipment > Tools > Torching & Heating > Pyrography Pens','','craft_supplies_and_tools.tools_and_equipment.tools.torching_and_heating.pyrography_pens',1803,1);
INSERT INTO `ps_etsy_categories` VALUES (1809,6969,'Craft Supplies & Tools > Tools & Equipment > Tools > Torching & Heating > Ribbon Burners','','craft_supplies_and_tools.tools_and_equipment.tools.torching_and_heating.ribbon_burners',1803,1);
INSERT INTO `ps_etsy_categories` VALUES (1810,6972,'Craft Supplies & Tools > Tools & Equipment > Tools > Torching & Heating > Torching Baskets','','craft_supplies_and_tools.tools_and_equipment.tools.torching_and_heating.torching_baskets',1803,1);
INSERT INTO `ps_etsy_categories` VALUES (1811,6691,'Craft Supplies & Tools > Tools & Equipment > Tools > Wrenches, Ratchets & Sockets','','craft_supplies_and_tools.tools_and_equipment.tools.wrenches_ratchets_and_sockets',1700,0);
INSERT INTO `ps_etsy_categories` VALUES (1812,6936,'Craft Supplies & Tools > Tools & Equipment > Tools > Wrenches, Ratchets & Sockets > Adjustable Wrenches','','craft_supplies_and_tools.tools_and_equipment.tools.wrenches_ratchets_and_sockets.adjustable_wrenches',1811,1);
INSERT INTO `ps_etsy_categories` VALUES (1813,6937,'Craft Supplies & Tools > Tools & Equipment > Tools > Wrenches, Ratchets & Sockets > Combo Wrenches','','craft_supplies_and_tools.tools_and_equipment.tools.wrenches_ratchets_and_sockets.combo_wrenches',1811,1);
INSERT INTO `ps_etsy_categories` VALUES (1814,6939,'Craft Supplies & Tools > Tools & Equipment > Tools > Wrenches, Ratchets & Sockets > Ratchets','','craft_supplies_and_tools.tools_and_equipment.tools.wrenches_ratchets_and_sockets.ratchets_',1811,1);
INSERT INTO `ps_etsy_categories` VALUES (1815,6938,'Craft Supplies & Tools > Tools & Equipment > Tools > Wrenches, Ratchets & Sockets > Sockets','','craft_supplies_and_tools.tools_and_equipment.tools.wrenches_ratchets_and_sockets.sockets',1811,1);
INSERT INTO `ps_etsy_categories` VALUES (1816,9322,'Craft Supplies & Tools > Yarn & Fiber','','craft_supplies_and_tools.yarn_and_fiber',919,0);
INSERT INTO `ps_etsy_categories` VALUES (1817,6636,'Craft Supplies & Tools > Yarn & Fiber > Batts','','craft_supplies_and_tools.yarn_and_fiber.batts',1816,1);
INSERT INTO `ps_etsy_categories` VALUES (1818,6634,'Craft Supplies & Tools > Yarn & Fiber > Interfacing & Stabilizers','','craft_supplies_and_tools.yarn_and_fiber.interfacing_and_stabilizers',1816,1);
INSERT INTO `ps_etsy_categories` VALUES (1819,6637,'Craft Supplies & Tools > Yarn & Fiber > Mull Cloth','','craft_supplies_and_tools.yarn_and_fiber.mull_cloth',1816,1);
INSERT INTO `ps_etsy_categories` VALUES (1820,6632,'Craft Supplies & Tools > Yarn & Fiber > Roving','','craft_supplies_and_tools.yarn_and_fiber.roving',1816,1);
INSERT INTO `ps_etsy_categories` VALUES (1821,6631,'Craft Supplies & Tools > Yarn & Fiber > Stuffing, Batting & Filling','','craft_supplies_and_tools.yarn_and_fiber.stuffing_batting_and_filling',1816,1);
INSERT INTO `ps_etsy_categories` VALUES (1822,6243,'Craft Supplies & Tools > Yarn & Fiber > Yarn','','craft_supplies_and_tools.yarn_and_fiber.yarn',1816,1);
INSERT INTO `ps_etsy_categories` VALUES (1823,825,'Electronics & Accessories','','electronics_and_accessories',0,0);
INSERT INTO `ps_etsy_categories` VALUES (1824,826,'Electronics & Accessories > Audio','','electronics_and_accessories.audio',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1825,827,'Electronics & Accessories > Audio > Boomboxes & Portable Audio','','electronics_and_accessories.audio.boomboxes_and_portable_audio',1824,1);
INSERT INTO `ps_etsy_categories` VALUES (1826,828,'Electronics & Accessories > Audio > Headphones & Stands','','electronics_and_accessories.audio.headphones_and_stands',1824,0);
INSERT INTO `ps_etsy_categories` VALUES (1827,2644,'Electronics & Accessories > Audio > Headphones & Stands > Headphone Stands','','electronics_and_accessories.audio.headphones_and_stands.headphone_stands',1826,1);
INSERT INTO `ps_etsy_categories` VALUES (1828,2645,'Electronics & Accessories > Audio > Headphones & Stands > Headphones','','electronics_and_accessories.audio.headphones_and_stands.headphones',1826,1);
INSERT INTO `ps_etsy_categories` VALUES (1829,829,'Electronics & Accessories > Audio > Microphones','','electronics_and_accessories.audio.microphones',1824,1);
INSERT INTO `ps_etsy_categories` VALUES (1830,830,'Electronics & Accessories > Audio > Record Players','','electronics_and_accessories.audio.record_players',1824,1);
INSERT INTO `ps_etsy_categories` VALUES (1831,831,'Electronics & Accessories > Audio > Stereos & Home Audio','','electronics_and_accessories.audio.stereos_and_home_audio',1824,1);
INSERT INTO `ps_etsy_categories` VALUES (1832,832,'Electronics & Accessories > Batteries & Charging','','electronics_and_accessories.batteries_and_charging',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1833,834,'Electronics & Accessories > Batteries & Charging > Chargers','','electronics_and_accessories.batteries_and_charging.chargers',1832,1);
INSERT INTO `ps_etsy_categories` VALUES (1834,835,'Electronics & Accessories > Cables & Cords','','electronics_and_accessories.cables_and_cords',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1835,836,'Electronics & Accessories > Cables & Cords > Cables','','electronics_and_accessories.cables_and_cords.cables',1834,1);
INSERT INTO `ps_etsy_categories` VALUES (1836,837,'Electronics & Accessories > Cables & Cords > Cord Ties & Organizers','','electronics_and_accessories.cables_and_cords.cord_ties_and_organizers',1834,1);
INSERT INTO `ps_etsy_categories` VALUES (1837,846,'Electronics & Accessories > Car Parts & Accessories','','electronics_and_accessories.car_parts_and_accessories',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1838,1895,'Electronics & Accessories > Car Parts & Accessories > Car Accessories','','electronics_and_accessories.car_parts_and_accessories.car_accessories',1837,1);
INSERT INTO `ps_etsy_categories` VALUES (1839,1894,'Electronics & Accessories > Car Parts & Accessories > Car Parts','','electronics_and_accessories.car_parts_and_accessories.car_parts',1837,1);
INSERT INTO `ps_etsy_categories` VALUES (1840,6064,'Electronics & Accessories > Car Parts & Accessories > License Plates','','electronics_and_accessories.car_parts_and_accessories.license_plates',1837,1);
INSERT INTO `ps_etsy_categories` VALUES (1841,847,'Electronics & Accessories > Cell Phone Accessories','','electronics_and_accessories.cell_phone_accessories',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1842,848,'Electronics & Accessories > Cell Phone Accessories > Plugs & Charms','','electronics_and_accessories.cell_phone_accessories.plugs_and_charms',1841,1);
INSERT INTO `ps_etsy_categories` VALUES (1843,849,'Electronics & Accessories > Cell Phone Accessories > Styluses','','electronics_and_accessories.cell_phone_accessories.styluses',1841,1);
INSERT INTO `ps_etsy_categories` VALUES (1844,850,'Electronics & Accessories > Computers & Peripherals','','electronics_and_accessories.computers_and_peripherals',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1845,851,'Electronics & Accessories > Computers & Peripherals > Computers','','electronics_and_accessories.computers_and_peripherals.computers',1844,1);
INSERT INTO `ps_etsy_categories` VALUES (1846,852,'Electronics & Accessories > Computers & Peripherals > Drives & Memory','','electronics_and_accessories.computers_and_peripherals.drives_and_memory',1844,1);
INSERT INTO `ps_etsy_categories` VALUES (1847,853,'Electronics & Accessories > Computers & Peripherals > Keyboards & Mice','','electronics_and_accessories.computers_and_peripherals.keyboards_and_mice',1844,0);
INSERT INTO `ps_etsy_categories` VALUES (1848,1896,'Electronics & Accessories > Computers & Peripherals > Keyboards & Mice > Keyboards','','electronics_and_accessories.computers_and_peripherals.keyboards_and_mice.keyboards',1847,1);
INSERT INTO `ps_etsy_categories` VALUES (1849,1897,'Electronics & Accessories > Computers & Peripherals > Keyboards & Mice > Mice','','electronics_and_accessories.computers_and_peripherals.keyboards_and_mice.mice',1847,1);
INSERT INTO `ps_etsy_categories` VALUES (1850,2006,'Electronics & Accessories > Computers & Peripherals > Keyboards & Mice > Mousepads','','electronics_and_accessories.computers_and_peripherals.keyboards_and_mice.mousepads',1847,1);
INSERT INTO `ps_etsy_categories` VALUES (1851,854,'Electronics & Accessories > Computers & Peripherals > Monitors & Accessories','','electronics_and_accessories.computers_and_peripherals.monitors_and_accessories',1844,0);
INSERT INTO `ps_etsy_categories` VALUES (1852,1906,'Electronics & Accessories > Computers & Peripherals > Monitors & Accessories > Monitors','','electronics_and_accessories.computers_and_peripherals.monitors_and_accessories.monitors',1851,1);
INSERT INTO `ps_etsy_categories` VALUES (1853,855,'Electronics & Accessories > Decals & Skins','','electronics_and_accessories.decals_and_skins',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1854,856,'Electronics & Accessories > Decals & Skins > Keyboard','','electronics_and_accessories.decals_and_skins.keyboard',1853,0);
INSERT INTO `ps_etsy_categories` VALUES (1855,1899,'Electronics & Accessories > Decals & Skins > Keyboard > Keyboard Decals','','electronics_and_accessories.decals_and_skins.keyboard.keyboard_decals',1854,1);
INSERT INTO `ps_etsy_categories` VALUES (1856,1898,'Electronics & Accessories > Decals & Skins > Keyboard > Keyboard Skins','','electronics_and_accessories.decals_and_skins.keyboard.keyboard_skins',1854,1);
INSERT INTO `ps_etsy_categories` VALUES (1857,857,'Electronics & Accessories > Decals & Skins > Laptop','','electronics_and_accessories.decals_and_skins.laptop',1853,0);
INSERT INTO `ps_etsy_categories` VALUES (1858,1901,'Electronics & Accessories > Decals & Skins > Laptop > Laptop Decals','','electronics_and_accessories.decals_and_skins.laptop.laptop_decals',1857,1);
INSERT INTO `ps_etsy_categories` VALUES (1859,1900,'Electronics & Accessories > Decals & Skins > Laptop > Laptop Skins','','electronics_and_accessories.decals_and_skins.laptop.laptop_skins',1857,1);
INSERT INTO `ps_etsy_categories` VALUES (1860,858,'Electronics & Accessories > Decals & Skins > Phone','','electronics_and_accessories.decals_and_skins.phone',1853,0);
INSERT INTO `ps_etsy_categories` VALUES (1861,1902,'Electronics & Accessories > Decals & Skins > Phone > Phone Decals','','electronics_and_accessories.decals_and_skins.phone.phone_decals',1860,1);
INSERT INTO `ps_etsy_categories` VALUES (1862,1903,'Electronics & Accessories > Decals & Skins > Phone > Phone Skins','','electronics_and_accessories.decals_and_skins.phone.phone_skins',1860,1);
INSERT INTO `ps_etsy_categories` VALUES (1863,859,'Electronics & Accessories > Decals & Skins > Tablet','','electronics_and_accessories.decals_and_skins.tablet',1853,0);
INSERT INTO `ps_etsy_categories` VALUES (1864,1905,'Electronics & Accessories > Decals & Skins > Tablet > Tablet Decals','','electronics_and_accessories.decals_and_skins.tablet.tablet_decals',1863,1);
INSERT INTO `ps_etsy_categories` VALUES (1865,1904,'Electronics & Accessories > Decals & Skins > Tablet > Tablet Skins','','electronics_and_accessories.decals_and_skins.tablet.tablet_skins',1863,1);
INSERT INTO `ps_etsy_categories` VALUES (1866,861,'Electronics & Accessories > Docking & Stands','','electronics_and_accessories.docking_and_stands',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1867,862,'Electronics & Accessories > Docking & Stands > Docking Stations','','electronics_and_accessories.docking_and_stands.docking_stations',1866,1);
INSERT INTO `ps_etsy_categories` VALUES (1868,863,'Electronics & Accessories > Docking & Stands > Laptop Trays & Stands','','electronics_and_accessories.docking_and_stands.laptop_trays_and_stands',1866,1);
INSERT INTO `ps_etsy_categories` VALUES (1869,864,'Electronics & Accessories > Docking & Stands > Stands','','electronics_and_accessories.docking_and_stands.stands',1866,1);
INSERT INTO `ps_etsy_categories` VALUES (1870,865,'Electronics & Accessories > Electronics Cases','','electronics_and_accessories.electronics_cases',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1871,866,'Electronics & Accessories > Electronics Cases > Camera Bags & Cases','','electronics_and_accessories.electronics_cases.camera_bags_and_cases',1870,0);
INSERT INTO `ps_etsy_categories` VALUES (1872,867,'Electronics & Accessories > Electronics Cases > Camera Bags & Cases > Bag Inserts','','electronics_and_accessories.electronics_cases.camera_bags_and_cases.bag_inserts',1871,1);
INSERT INTO `ps_etsy_categories` VALUES (1873,839,'Electronics & Accessories > Electronics Cases > Camera Bags & Cases > Camera Accessories','','electronics_and_accessories.electronics_cases.camera_bags_and_cases.camera_accessories',1871,1);
INSERT INTO `ps_etsy_categories` VALUES (1874,869,'Electronics & Accessories > Electronics Cases > Camera Bags & Cases > Camera Straps & Strap Covers','','electronics_and_accessories.electronics_cases.camera_bags_and_cases.camera_straps_and_strap_covers',1871,0);
INSERT INTO `ps_etsy_categories` VALUES (1875,1698,'Electronics & Accessories > Electronics Cases > Camera Bags & Cases > Camera Straps & Strap Covers > Camera Strap Covers','','electronics_and_accessories.electronics_cases.camera_bags_and_cases.camera_straps_and_strap_covers.camera_strap_covers',1874,1);
INSERT INTO `ps_etsy_categories` VALUES (1876,1697,'Electronics & Accessories > Electronics Cases > Camera Bags & Cases > Camera Straps & Strap Covers > Camera Straps','','electronics_and_accessories.electronics_cases.camera_bags_and_cases.camera_straps_and_strap_covers.camera_straps',1874,1);
INSERT INTO `ps_etsy_categories` VALUES (1877,870,'Electronics & Accessories > Electronics Cases > Camera Bags & Cases > Lens Cases','','electronics_and_accessories.electronics_cases.camera_bags_and_cases.lens_cases',1871,1);
INSERT INTO `ps_etsy_categories` VALUES (1878,871,'Electronics & Accessories > Electronics Cases > Laptop Bags','','electronics_and_accessories.electronics_cases.laptop_bags',1870,1);
INSERT INTO `ps_etsy_categories` VALUES (1879,872,'Electronics & Accessories > Electronics Cases > Laptop Sleeves','','electronics_and_accessories.electronics_cases.laptop_sleeves',1870,1);
INSERT INTO `ps_etsy_categories` VALUES (1880,873,'Electronics & Accessories > Electronics Cases > Phone Cases','','electronics_and_accessories.electronics_cases.phone_cases',1870,1);
INSERT INTO `ps_etsy_categories` VALUES (1881,874,'Electronics & Accessories > Electronics Cases > Tablet & E-Reader Cases','','electronics_and_accessories.electronics_cases.tablet_and_reader_cases',1870,1);
INSERT INTO `ps_etsy_categories` VALUES (1882,875,'Electronics & Accessories > Gadgets','','electronics_and_accessories.gadgets',1823,1);
INSERT INTO `ps_etsy_categories` VALUES (1883,886,'Electronics & Accessories > TV & Projection','','electronics_and_accessories.tv_and_projection',1823,0);
INSERT INTO `ps_etsy_categories` VALUES (1884,887,'Electronics & Accessories > TV & Projection > Projectors','','electronics_and_accessories.tv_and_projection.projectors',1883,1);
INSERT INTO `ps_etsy_categories` VALUES (1885,888,'Electronics & Accessories > TV & Projection > Televisions','','electronics_and_accessories.tv_and_projection.televisions',1883,1);
INSERT INTO `ps_etsy_categories` VALUES (1886,889,'Electronics & Accessories > TV & Projection > Video Players','','electronics_and_accessories.tv_and_projection.video_players',1883,1);
INSERT INTO `ps_etsy_categories` VALUES (1887,885,'Electronics & Accessories > Telephones & Handsets','','electronics_and_accessories.telephones_and_handsets',1823,1);
INSERT INTO `ps_etsy_categories` VALUES (1888,890,'Electronics & Accessories > Video Games','','electronics_and_accessories.video_games',1823,1);
INSERT INTO `ps_etsy_categories` VALUES (1889,891,'Home & Living','','home_and_living',0,0);
INSERT INTO `ps_etsy_categories` VALUES (1890,900,'Home & Living > Bathroom','','home_and_living.bathroom',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (1891,901,'Home & Living > Bathroom > Bath Mats & Rugs','','home_and_living.bathroom.bath_mats_and_rugs',1890,0);
INSERT INTO `ps_etsy_categories` VALUES (1892,1882,'Home & Living > Bathroom > Bath Mats & Rugs > Bath Cozies','','home_and_living.bathroom.bath_mats_and_rugs.bath_cozies',1891,1);
INSERT INTO `ps_etsy_categories` VALUES (1893,1915,'Home & Living > Bathroom > Bath Mats & Rugs > Bath Mats','','home_and_living.bathroom.bath_mats_and_rugs.bath_mats',1891,1);
INSERT INTO `ps_etsy_categories` VALUES (1894,1916,'Home & Living > Bathroom > Bath Mats & Rugs > Bath Rugs','','home_and_living.bathroom.bath_mats_and_rugs.bath_rugs',1891,1);
INSERT INTO `ps_etsy_categories` VALUES (1895,904,'Home & Living > Bathroom > Bath Towels','','home_and_living.bathroom.bath_towels',1890,0);
INSERT INTO `ps_etsy_categories` VALUES (1896,907,'Home & Living > Bathroom > Bath Towels > Hooded Towels','','home_and_living.bathroom.bath_towels.hooded_towels',1895,1);
INSERT INTO `ps_etsy_categories` VALUES (1897,902,'Home & Living > Bathroom > Bathroom Décor','','home_and_living.bathroom.bathroom_decor',1890,1);
INSERT INTO `ps_etsy_categories` VALUES (1898,11650,'Home & Living > Bathroom > Bathroom Scales','','home_and_living.bathroom.bathroom_scales',1890,1);
INSERT INTO `ps_etsy_categories` VALUES (1899,903,'Home & Living > Bathroom > Bathroom Vanities','','home_and_living.bathroom.bathroom_vanities',1890,1);
INSERT INTO `ps_etsy_categories` VALUES (1900,11647,'Home & Living > Bathroom > Bathtub Trays','','home_and_living.bathroom.bathtub_trays',1890,1);
INSERT INTO `ps_etsy_categories` VALUES (1901,905,'Home & Living > Bathroom > Beach Towels','','home_and_living.bathroom.beach_towels',1890,1);
INSERT INTO `ps_etsy_categories` VALUES (1902,906,'Home & Living > Bathroom > Cups & Storage','','home_and_living.bathroom.cups_and_storage',1890,0);
INSERT INTO `ps_etsy_categories` VALUES (1903,1917,'Home & Living > Bathroom > Cups & Storage > Cups','','home_and_living.bathroom.cups_and_storage.cups',1902,1);
INSERT INTO `ps_etsy_categories` VALUES (1904,1884,'Home & Living > Bathroom > Cups & Storage > Toothbrush Holders','','home_and_living.bathroom.cups_and_storage.toothbrush_holders',1902,1);
INSERT INTO `ps_etsy_categories` VALUES (1905,11315,'Home & Living > Bathroom > Medicine Cabinets','','home_and_living.bathroom.medicine_cabinets',1890,1);
INSERT INTO `ps_etsy_categories` VALUES (1906,908,'Home & Living > Bathroom > Shower Curtains & Rings','','home_and_living.bathroom.shower_curtains_and_rings',1890,0);
INSERT INTO `ps_etsy_categories` VALUES (1907,1919,'Home & Living > Bathroom > Shower Curtains & Rings > Curtain Rings & Hooks','','home_and_living.bathroom.shower_curtains_and_rings.curtain_rings_and_hooks',1906,1);
INSERT INTO `ps_etsy_categories` VALUES (1908,1918,'Home & Living > Bathroom > Shower Curtains & Rings > Shower Curtains','','home_and_living.bathroom.shower_curtains_and_rings.shower_curtains',1906,1);
INSERT INTO `ps_etsy_categories` VALUES (1909,2197,'Home & Living > Bathroom > Shower Curtains & Rings > Shower Rods & Hardware','','home_and_living.bathroom.shower_curtains_and_rings.shower_rods_and_hardware',1906,1);
INSERT INTO `ps_etsy_categories` VALUES (1910,909,'Home & Living > Bathroom > Soap Dishes & Dispensers','','home_and_living.bathroom.soap_dishes_and_dispensers',1890,0);
INSERT INTO `ps_etsy_categories` VALUES (1911,1920,'Home & Living > Bathroom > Soap Dishes & Dispensers > Soap Dishes','','home_and_living.bathroom.soap_dishes_and_dispensers.soap_dishes',1910,1);
INSERT INTO `ps_etsy_categories` VALUES (1912,1921,'Home & Living > Bathroom > Soap Dishes & Dispensers > Soap Dispensers','','home_and_living.bathroom.soap_dishes_and_dispensers.soap_dispensers',1910,1);
INSERT INTO `ps_etsy_categories` VALUES (1913,910,'Home & Living > Bathroom > Towel Racks & Rods','','home_and_living.bathroom.towel_racks_and_rods',1890,1);
INSERT INTO `ps_etsy_categories` VALUES (1914,911,'Home & Living > Bedding','','home_and_living.bedding',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (1915,11531,'Home & Living > Bedding > Baby Nests','','home_and_living.bedding.baby_nests',1914,1);
INSERT INTO `ps_etsy_categories` VALUES (1916,912,'Home & Living > Bedding > Bed Pillows','','home_and_living.bedding.bed_pillows',1914,0);
INSERT INTO `ps_etsy_categories` VALUES (1917,6058,'Home & Living > Bedding > Bed Pillows > Tooth Fairy Pillows','','home_and_living.bedding.bed_pillows.tooth_fairy_pillows',1916,1);
INSERT INTO `ps_etsy_categories` VALUES (1918,913,'Home & Living > Bedding > Blankets & Throws','','home_and_living.bedding.blankets_and_throws',1914,0);
INSERT INTO `ps_etsy_categories` VALUES (1919,914,'Home & Living > Bedding > Blankets & Throws > Afghans','','home_and_living.bedding.blankets_and_throws.afghans',1918,1);
INSERT INTO `ps_etsy_categories` VALUES (1920,2910,'Home & Living > Bedding > Blankets & Throws > Baby Blankets','','home_and_living.bedding.blankets_and_throws.baby_blankets',1918,1);
INSERT INTO `ps_etsy_categories` VALUES (1921,916,'Home & Living > Bedding > Blankets & Throws > Quilts','','home_and_living.bedding.blankets_and_throws.quilts',1918,1);
INSERT INTO `ps_etsy_categories` VALUES (1922,915,'Home & Living > Bedding > Blankets & Throws > Throws','','home_and_living.bedding.blankets_and_throws.throws',1918,1);
INSERT INTO `ps_etsy_categories` VALUES (1923,1879,'Home & Living > Bedding > Blankets & Throws > Weighted Blankets','','home_and_living.bedding.blankets_and_throws.weighted_blankets',1918,1);
INSERT INTO `ps_etsy_categories` VALUES (1924,11535,'Home & Living > Bedding > Crib Bumpers','','home_and_living.bedding.crib_bumpers',1914,1);
INSERT INTO `ps_etsy_categories` VALUES (1925,11560,'Home & Living > Bedding > Crib Rail Covers','','home_and_living.bedding.crib_rail_covers',1914,1);
INSERT INTO `ps_etsy_categories` VALUES (1926,11556,'Home & Living > Bedding > Crib Skirts','','home_and_living.bedding.crib_skirts',1914,1);
INSERT INTO `ps_etsy_categories` VALUES (1927,917,'Home & Living > Bedding > Duvet Covers','','home_and_living.bedding.duvet_covers',1914,1);
INSERT INTO `ps_etsy_categories` VALUES (1928,918,'Home & Living > Bedding > Sheets & Pillowcases','','home_and_living.bedding.sheets_and_pillowcases',1914,0);
INSERT INTO `ps_etsy_categories` VALUES (1929,1923,'Home & Living > Bedding > Sheets & Pillowcases > Fitted Sheets','','home_and_living.bedding.sheets_and_pillowcases.fitted_sheets',1928,1);
INSERT INTO `ps_etsy_categories` VALUES (1930,1922,'Home & Living > Bedding > Sheets & Pillowcases > Flat Sheets','','home_and_living.bedding.sheets_and_pillowcases.flat_sheets',1928,1);
INSERT INTO `ps_etsy_categories` VALUES (1931,1925,'Home & Living > Bedding > Sheets & Pillowcases > Pillowcases','','home_and_living.bedding.sheets_and_pillowcases.pillowcases',1928,1);
INSERT INTO `ps_etsy_categories` VALUES (1932,1926,'Home & Living > Bedding > Sheets & Pillowcases > Shams','','home_and_living.bedding.sheets_and_pillowcases.shams',1928,1);
INSERT INTO `ps_etsy_categories` VALUES (1933,1924,'Home & Living > Bedding > Sheets & Pillowcases > Sheet Sets','','home_and_living.bedding.sheets_and_pillowcases.sheet_sets',1928,1);
INSERT INTO `ps_etsy_categories` VALUES (1934,919,'Home & Living > Cleaning Supplies','','home_and_living.cleaning_supplies',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (1935,920,'Home & Living > Cleaning Supplies > Air Fresheners','','home_and_living.cleaning_supplies.air_fresheners',1934,1);
INSERT INTO `ps_etsy_categories` VALUES (1936,922,'Home & Living > Cleaning Supplies > Buckets, Mops & Brooms','','home_and_living.cleaning_supplies.buckets_mops_and_brooms',1934,0);
INSERT INTO `ps_etsy_categories` VALUES (1937,1929,'Home & Living > Cleaning Supplies > Buckets, Mops & Brooms > Brooms','','home_and_living.cleaning_supplies.buckets_mops_and_brooms.brooms',1936,1);
INSERT INTO `ps_etsy_categories` VALUES (1938,1927,'Home & Living > Cleaning Supplies > Buckets, Mops & Brooms > Buckets','','home_and_living.cleaning_supplies.buckets_mops_and_brooms.buckets',1936,1);
INSERT INTO `ps_etsy_categories` VALUES (1939,1928,'Home & Living > Cleaning Supplies > Buckets, Mops & Brooms > Mops','','home_and_living.cleaning_supplies.buckets_mops_and_brooms.mops',1936,1);
INSERT INTO `ps_etsy_categories` VALUES (1940,923,'Home & Living > Cleaning Supplies > Cleaning Gloves','','home_and_living.cleaning_supplies.cleaning_gloves',1934,1);
INSERT INTO `ps_etsy_categories` VALUES (1941,924,'Home & Living > Cleaning Supplies > Cleaning Products','','home_and_living.cleaning_supplies.cleaning_products',1934,1);
INSERT INTO `ps_etsy_categories` VALUES (1942,925,'Home & Living > Cleaning Supplies > Cloths & Sponges','','home_and_living.cleaning_supplies.cloths_and_sponges',1934,1);
INSERT INTO `ps_etsy_categories` VALUES (1943,926,'Home & Living > Cleaning Supplies > Laundry Supplies','','home_and_living.cleaning_supplies.laundry_supplies',1934,0);
INSERT INTO `ps_etsy_categories` VALUES (1944,11347,'Home & Living > Cleaning Supplies > Laundry Supplies > Clothespin Bags','','home_and_living.cleaning_supplies.laundry_supplies.clothespin_bags',1943,1);
INSERT INTO `ps_etsy_categories` VALUES (1945,11346,'Home & Living > Cleaning Supplies > Laundry Supplies > Dryer Balls','','home_and_living.cleaning_supplies.laundry_supplies.dryer_balls',1943,1);
INSERT INTO `ps_etsy_categories` VALUES (1946,11348,'Home & Living > Cleaning Supplies > Laundry Supplies > Ironing Board Covers','','home_and_living.cleaning_supplies.laundry_supplies.ironing_board_covers',1943,1);
INSERT INTO `ps_etsy_categories` VALUES (1947,11350,'Home & Living > Cleaning Supplies > Laundry Supplies > Laundry Detergents & Soaps','','home_and_living.cleaning_supplies.laundry_supplies.laundry_detergents_and_soaps',1943,1);
INSERT INTO `ps_etsy_categories` VALUES (1948,1178,'Home & Living > Curtains & Window Treatments','','home_and_living.curtains_and_window_treatments',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (1949,2182,'Home & Living > Curtains & Window Treatments > Curtains','','home_and_living.curtains_and_window_treatments.curtains',1948,1);
INSERT INTO `ps_etsy_categories` VALUES (1950,2183,'Home & Living > Curtains & Window Treatments > Window Treatments','','home_and_living.curtains_and_window_treatments.window_treatments',1948,1);
INSERT INTO `ps_etsy_categories` VALUES (1951,927,'Home & Living > Floor & Rugs','','home_and_living.floor_and_rugs',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (1952,929,'Home & Living > Floor & Rugs > Rugs','','home_and_living.floor_and_rugs.rugs',1951,1);
INSERT INTO `ps_etsy_categories` VALUES (1953,930,'Home & Living > Food & Drink','','home_and_living.food_and_drink',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (1954,931,'Home & Living > Food & Drink > Baked Goods','','home_and_living.food_and_drink.baked_goods',1953,0);
INSERT INTO `ps_etsy_categories` VALUES (1955,932,'Home & Living > Food & Drink > Baked Goods > Biscotti','','home_and_living.food_and_drink.baked_goods.biscotti',1954,1);
INSERT INTO `ps_etsy_categories` VALUES (1956,933,'Home & Living > Food & Drink > Baked Goods > Bread','','home_and_living.food_and_drink.baked_goods.bread',1954,1);
INSERT INTO `ps_etsy_categories` VALUES (1957,934,'Home & Living > Food & Drink > Baked Goods > Brownies','','home_and_living.food_and_drink.baked_goods.brownies',1954,1);
INSERT INTO `ps_etsy_categories` VALUES (1958,935,'Home & Living > Food & Drink > Baked Goods > Cakes','','home_and_living.food_and_drink.baked_goods.cakes',1954,1);
INSERT INTO `ps_etsy_categories` VALUES (1959,936,'Home & Living > Food & Drink > Baked Goods > Cookies','','home_and_living.food_and_drink.baked_goods.cookies',1954,1);
INSERT INTO `ps_etsy_categories` VALUES (1960,937,'Home & Living > Food & Drink > Baked Goods > Cupcakes','','home_and_living.food_and_drink.baked_goods.cupcakes',1954,1);
INSERT INTO `ps_etsy_categories` VALUES (1961,938,'Home & Living > Food & Drink > Baked Goods > Pastries','','home_and_living.food_and_drink.baked_goods.pastries',1954,1);
INSERT INTO `ps_etsy_categories` VALUES (1962,939,'Home & Living > Food & Drink > Baked Goods > Pies','','home_and_living.food_and_drink.baked_goods.pies',1954,1);
INSERT INTO `ps_etsy_categories` VALUES (1963,940,'Home & Living > Food & Drink > Candy','','home_and_living.food_and_drink.candy',1953,0);
INSERT INTO `ps_etsy_categories` VALUES (1964,941,'Home & Living > Food & Drink > Candy > Brittle','','home_and_living.food_and_drink.candy.brittle',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1965,942,'Home & Living > Food & Drink > Candy > Caramels','','home_and_living.food_and_drink.candy.caramels',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1966,943,'Home & Living > Food & Drink > Candy > Chocolates','','home_and_living.food_and_drink.candy.chocolates',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1967,944,'Home & Living > Food & Drink > Candy > Fudge','','home_and_living.food_and_drink.candy.fudge',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1968,945,'Home & Living > Food & Drink > Candy > Lollipops','','home_and_living.food_and_drink.candy.lollipops',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1969,946,'Home & Living > Food & Drink > Candy > Marshmallows','','home_and_living.food_and_drink.candy.marshmallows',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1970,947,'Home & Living > Food & Drink > Candy > Popcorn','','home_and_living.food_and_drink.candy.popcorn',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1971,948,'Home & Living > Food & Drink > Candy > Pretzels','','home_and_living.food_and_drink.candy.pretzels',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1972,949,'Home & Living > Food & Drink > Candy > Toffee','','home_and_living.food_and_drink.candy.toffee',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1973,950,'Home & Living > Food & Drink > Candy > Truffles','','home_and_living.food_and_drink.candy.truffles',1963,1);
INSERT INTO `ps_etsy_categories` VALUES (1974,951,'Home & Living > Food & Drink > Coffee & Tea','','home_and_living.food_and_drink.coffee_and_tea',1953,0);
INSERT INTO `ps_etsy_categories` VALUES (1975,952,'Home & Living > Food & Drink > Coffee & Tea > Coffee','','home_and_living.food_and_drink.coffee_and_tea.coffee',1974,1);
INSERT INTO `ps_etsy_categories` VALUES (1976,953,'Home & Living > Food & Drink > Coffee & Tea > Hot Chocolate & Spoons','','home_and_living.food_and_drink.coffee_and_tea.hot_chocolate_and_spoons',1974,0);
INSERT INTO `ps_etsy_categories` VALUES (1977,2204,'Home & Living > Food & Drink > Coffee & Tea > Hot Chocolate & Spoons > Chocolate Spoons','','home_and_living.food_and_drink.coffee_and_tea.hot_chocolate_and_spoons.chocolate_spoons',1976,1);
INSERT INTO `ps_etsy_categories` VALUES (1978,2205,'Home & Living > Food & Drink > Coffee & Tea > Hot Chocolate & Spoons > Hot Chocolate','','home_and_living.food_and_drink.coffee_and_tea.hot_chocolate_and_spoons.hot_chocolate',1976,1);
INSERT INTO `ps_etsy_categories` VALUES (1979,954,'Home & Living > Food & Drink > Coffee & Tea > Sweeteners','','home_and_living.food_and_drink.coffee_and_tea.sweeteners',1974,1);
INSERT INTO `ps_etsy_categories` VALUES (1980,955,'Home & Living > Food & Drink > Coffee & Tea > Tea','','home_and_living.food_and_drink.coffee_and_tea.tea',1974,1);
INSERT INTO `ps_etsy_categories` VALUES (1981,956,'Home & Living > Food & Drink > Condiments & Sauces','','home_and_living.food_and_drink.condiments_and_sauces',1953,1);
INSERT INTO `ps_etsy_categories` VALUES (1982,957,'Home & Living > Food & Drink > Herbs, Spices & Seasonings','','home_and_living.food_and_drink.herbs_and_spices_and_seasonings',1953,0);
INSERT INTO `ps_etsy_categories` VALUES (1983,958,'Home & Living > Food & Drink > Herbs, Spices & Seasonings > Herbs & Spices','','home_and_living.food_and_drink.herbs_and_spices_and_seasonings.herbs_and_spices',1982,1);
INSERT INTO `ps_etsy_categories` VALUES (1984,959,'Home & Living > Food & Drink > Herbs, Spices & Seasonings > Seasoning Mixes','','home_and_living.food_and_drink.herbs_and_spices_and_seasonings.seasoning_mixes',1982,1);
INSERT INTO `ps_etsy_categories` VALUES (1985,961,'Home & Living > Food & Drink > Preserves','','home_and_living.food_and_drink.preserves',1953,0);
INSERT INTO `ps_etsy_categories` VALUES (1986,2208,'Home & Living > Food & Drink > Preserves > Chutneys','','home_and_living.food_and_drink.preserves.chutneys',1985,1);
INSERT INTO `ps_etsy_categories` VALUES (1987,2210,'Home & Living > Food & Drink > Preserves > Fruit Butters','','home_and_living.food_and_drink.preserves.fruit_butters',1985,1);
INSERT INTO `ps_etsy_categories` VALUES (1988,962,'Home & Living > Food & Drink > Preserves > Jams','','home_and_living.food_and_drink.preserves.jams',1985,1);
INSERT INTO `ps_etsy_categories` VALUES (1989,2206,'Home & Living > Food & Drink > Preserves > Jellies','','home_and_living.food_and_drink.preserves.jellies',1985,1);
INSERT INTO `ps_etsy_categories` VALUES (1990,963,'Home & Living > Food & Drink > Preserves > Marmalades','','home_and_living.food_and_drink.preserves.marmalades',1985,1);
INSERT INTO `ps_etsy_categories` VALUES (1991,2207,'Home & Living > Food & Drink > Preserves > Pickles','','home_and_living.food_and_drink.preserves.pickles',1985,1);
INSERT INTO `ps_etsy_categories` VALUES (1992,965,'Home & Living > Food & Drink > Snacks','','home_and_living.food_and_drink.snacks',1953,1);
INSERT INTO `ps_etsy_categories` VALUES (1993,966,'Home & Living > Food & Drink > Soups & Packaged Mixes','','home_and_living.food_and_drink.soups_and_packaged_mixes',1953,0);
INSERT INTO `ps_etsy_categories` VALUES (1994,2176,'Home & Living > Food & Drink > Soups & Packaged Mixes > Packaged Mixes','','home_and_living.food_and_drink.soups_and_packaged_mixes.packaged_mixes',1993,1);
INSERT INTO `ps_etsy_categories` VALUES (1995,2175,'Home & Living > Food & Drink > Soups & Packaged Mixes > Soups','','home_and_living.food_and_drink.soups_and_packaged_mixes.soups',1993,1);
INSERT INTO `ps_etsy_categories` VALUES (1996,967,'Home & Living > Furniture','','home_and_living.furniture',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (1997,968,'Home & Living > Furniture > Bedroom Furniture','','home_and_living.furniture.bedroom_furniture',1996,0);
INSERT INTO `ps_etsy_categories` VALUES (1998,969,'Home & Living > Furniture > Bedroom Furniture > Beds & Headboards','','home_and_living.furniture.bedroom_furniture.beds_and_headboards',1997,1);
INSERT INTO `ps_etsy_categories` VALUES (1999,970,'Home & Living > Furniture > Bedroom Furniture > Dressers & Armoires','','home_and_living.furniture.bedroom_furniture.dressers_and_armoires',1997,1);
INSERT INTO `ps_etsy_categories` VALUES (2000,971,'Home & Living > Furniture > Bedroom Furniture > Steps & Stools','','home_and_living.furniture.bedroom_furniture.steps_and_stools',1997,1);
INSERT INTO `ps_etsy_categories` VALUES (2001,972,'Home & Living > Furniture > Bedroom Furniture > Vanities & Nightstands','','home_and_living.furniture.bedroom_furniture.vanities_and_nightstands',1997,1);
INSERT INTO `ps_etsy_categories` VALUES (2002,973,'Home & Living > Furniture > Dining Room Furniture','','home_and_living.furniture.dining_room_furniture',1996,0);
INSERT INTO `ps_etsy_categories` VALUES (2003,974,'Home & Living > Furniture > Dining Room Furniture > Buffets & China Cabinets','','home_and_living.furniture.dining_room_furniture.buffets_and_china_cabinets',2002,1);
INSERT INTO `ps_etsy_categories` VALUES (2004,975,'Home & Living > Furniture > Dining Room Furniture > Dining Chairs','','home_and_living.furniture.dining_room_furniture.dining_chairs',2002,1);
INSERT INTO `ps_etsy_categories` VALUES (2005,976,'Home & Living > Furniture > Dining Room Furniture > Dining Sets','','home_and_living.furniture.dining_room_furniture.dining_sets',2002,1);
INSERT INTO `ps_etsy_categories` VALUES (2006,977,'Home & Living > Furniture > Dining Room Furniture > Kitchen & Dining Tables','','home_and_living.furniture.dining_room_furniture.kitchen_and_dining_tables',2002,1);
INSERT INTO `ps_etsy_categories` VALUES (2007,11837,'Home & Living > Furniture > Dining Room Furniture > Kitchen Islands','','home_and_living.furniture.dining_room_furniture.kitchen_islands',2002,1);
INSERT INTO `ps_etsy_categories` VALUES (2008,978,'Home & Living > Furniture > Dining Room Furniture > Stools & Banquettes','','home_and_living.furniture.dining_room_furniture.stools_and_banquettes',2002,1);
INSERT INTO `ps_etsy_categories` VALUES (2009,979,'Home & Living > Furniture > Entryway Furniture','','home_and_living.furniture.entryway_furniture',1996,1);
INSERT INTO `ps_etsy_categories` VALUES (2010,980,'Home & Living > Furniture > Kids\' Furniture','','home_and_living.furniture.kids_furniture',1996,0);
INSERT INTO `ps_etsy_categories` VALUES (2011,981,'Home & Living > Furniture > Kids\' Furniture > Bean Bag Chairs','','home_and_living.furniture.kids_furniture.bean_bag_chairs',2010,1);
INSERT INTO `ps_etsy_categories` VALUES (2012,982,'Home & Living > Furniture > Kids\' Furniture > Benches & Toy Boxes','','home_and_living.furniture.kids_furniture.benches_and_toy_boxes',2010,1);
INSERT INTO `ps_etsy_categories` VALUES (2013,983,'Home & Living > Furniture > Kids\' Furniture > Bookcases','','home_and_living.furniture.kids_furniture.bookcases',2010,1);
INSERT INTO `ps_etsy_categories` VALUES (2014,984,'Home & Living > Furniture > Kids\' Furniture > Cribs & Cradles','','home_and_living.furniture.kids_furniture.cribs_and_cradles',2010,1);
INSERT INTO `ps_etsy_categories` VALUES (2015,985,'Home & Living > Furniture > Kids\' Furniture > Desks, Tables & Chairs','','home_and_living.furniture.kids_furniture.desks_tables_and_chairs',2010,1);
INSERT INTO `ps_etsy_categories` VALUES (2016,986,'Home & Living > Furniture > Kids\' Furniture > Dressers & Drawers','','home_and_living.furniture.kids_furniture.dressers_and_drawers',2010,1);
INSERT INTO `ps_etsy_categories` VALUES (2017,987,'Home & Living > Furniture > Kids\' Furniture > Steps & Stools','','home_and_living.furniture.kids_furniture.steps_and_stools',2010,1);
INSERT INTO `ps_etsy_categories` VALUES (2018,988,'Home & Living > Furniture > Kids\' Furniture > Toddler Beds','','home_and_living.furniture.kids_furniture.toddler_beds',2010,1);
INSERT INTO `ps_etsy_categories` VALUES (2019,989,'Home & Living > Furniture > Living Room Furniture','','home_and_living.furniture.living_room_furniture',1996,0);
INSERT INTO `ps_etsy_categories` VALUES (2020,990,'Home & Living > Furniture > Living Room Furniture > Benches & Trunks','','home_and_living.furniture.living_room_furniture.benches_and_trunks',2019,1);
INSERT INTO `ps_etsy_categories` VALUES (2021,991,'Home & Living > Furniture > Living Room Furniture > Bookshelves','','home_and_living.furniture.living_room_furniture.bookshelves',2019,1);
INSERT INTO `ps_etsy_categories` VALUES (2022,992,'Home & Living > Furniture > Living Room Furniture > Chairs & Ottomans','','home_and_living.furniture.living_room_furniture.chairs_and_ottomans',2019,1);
INSERT INTO `ps_etsy_categories` VALUES (2023,993,'Home & Living > Furniture > Living Room Furniture > Coffee & End Tables','','home_and_living.furniture.living_room_furniture.coffee_and_end_tables',2019,1);
INSERT INTO `ps_etsy_categories` VALUES (2024,994,'Home & Living > Furniture > Living Room Furniture > Console Tables & Cabinets','','home_and_living.furniture.living_room_furniture.console_tables_and_cabinets',2019,0);
INSERT INTO `ps_etsy_categories` VALUES (2025,11355,'Home & Living > Furniture > Living Room Furniture > Console Tables & Cabinets > Console & Sofa Tables','','home_and_living.furniture.living_room_furniture.console_tables_and_cabinets.console_and_sofa_tables',2024,1);
INSERT INTO `ps_etsy_categories` VALUES (2026,11356,'Home & Living > Furniture > Living Room Furniture > Console Tables & Cabinets > TV Stands & Media Centers','','home_and_living.furniture.living_room_furniture.console_tables_and_cabinets.tv_stands_and_media_centers',2024,1);
INSERT INTO `ps_etsy_categories` VALUES (2027,996,'Home & Living > Furniture > Living Room Furniture > Floor Pillows','','home_and_living.furniture.living_room_furniture.floor_pillows',2019,1);
INSERT INTO `ps_etsy_categories` VALUES (2028,997,'Home & Living > Furniture > Living Room Furniture > Slipcovers','','home_and_living.furniture.living_room_furniture.slipcovers',2019,1);
INSERT INTO `ps_etsy_categories` VALUES (2029,998,'Home & Living > Furniture > Living Room Furniture > Sofas & Loveseats','','home_and_living.furniture.living_room_furniture.sofas_and_loveseats',2019,1);
INSERT INTO `ps_etsy_categories` VALUES (2030,999,'Home & Living > Furniture > Office Furniture','','home_and_living.furniture.office_furniture',1996,0);
INSERT INTO `ps_etsy_categories` VALUES (2031,1000,'Home & Living > Furniture > Office Furniture > Desk Chairs','','home_and_living.furniture.office_furniture.desk_chairs',2030,1);
INSERT INTO `ps_etsy_categories` VALUES (2032,1001,'Home & Living > Furniture > Office Furniture > Desks','','home_and_living.furniture.office_furniture.desks',2030,1);
INSERT INTO `ps_etsy_categories` VALUES (2033,892,'Home & Living > Home Appliances','','home_and_living.home_appliances',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (2034,893,'Home & Living > Home Appliances > Appliance Decals','','home_and_living.home_appliances.appliance_decals',2033,1);
INSERT INTO `ps_etsy_categories` VALUES (2035,895,'Home & Living > Home Appliances > Fans & Heaters','','home_and_living.home_appliances.fans_and_heaters',2033,0);
INSERT INTO `ps_etsy_categories` VALUES (2036,1911,'Home & Living > Home Appliances > Fans & Heaters > Fans','','home_and_living.home_appliances.fans_and_heaters.fans',2035,1);
INSERT INTO `ps_etsy_categories` VALUES (2037,1912,'Home & Living > Home Appliances > Fans & Heaters > Heaters','','home_and_living.home_appliances.fans_and_heaters.heaters',2035,1);
INSERT INTO `ps_etsy_categories` VALUES (2038,896,'Home & Living > Home Appliances > Irons & Steamers','','home_and_living.home_appliances.irons_and_steamers',2033,0);
INSERT INTO `ps_etsy_categories` VALUES (2039,1913,'Home & Living > Home Appliances > Irons & Steamers > Irons','','home_and_living.home_appliances.irons_and_steamers.irons',2038,1);
INSERT INTO `ps_etsy_categories` VALUES (2040,1914,'Home & Living > Home Appliances > Irons & Steamers > Steamers','','home_and_living.home_appliances.irons_and_steamers.steamers',2038,1);
INSERT INTO `ps_etsy_categories` VALUES (2041,897,'Home & Living > Home Appliances > Large Appliances','','home_and_living.home_appliances.large_appliances',2033,1);
INSERT INTO `ps_etsy_categories` VALUES (2042,899,'Home & Living > Home Appliances > Weather Instruments','','home_and_living.home_appliances.weather_instruments',2033,1);
INSERT INTO `ps_etsy_categories` VALUES (2043,1002,'Home & Living > Home Décor','','home_and_living.home_decor',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (2044,1003,'Home & Living > Home Décor > Baskets & Bowls','','home_and_living.home_decor.baskets_and_bowls',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2045,6081,'Home & Living > Home Décor > Bells','','home_and_living.home_decor.bells',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2046,330,'Home & Living > Home Décor > Bookends','','home_and_living.home_decor.bookends',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2047,1004,'Home & Living > Home Décor > Candles & Holders','','home_and_living.home_decor.candles_and_holders',2043,0);
INSERT INTO `ps_etsy_categories` VALUES (2048,2212,'Home & Living > Home Décor > Candles & Holders > Candleholders','','home_and_living.home_decor.candles_and_holders.candleholders',2047,0);
INSERT INTO `ps_etsy_categories` VALUES (2049,2786,'Home & Living > Home Décor > Candles & Holders > Candleholders > Bowls','','home_and_living.home_decor.candles_and_holders.candleholders.bowls',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2050,2213,'Home & Living > Home Décor > Candles & Holders > Candleholders > Candelabras','','home_and_living.home_decor.candles_and_holders.candleholders.candelabras',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2051,2792,'Home & Living > Home Décor > Candles & Holders > Candleholders > Candle Warmers','','home_and_living.home_decor.candles_and_holders.candleholders.candle_warmers',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2052,2214,'Home & Living > Home Décor > Candles & Holders > Candleholders > Candlestick Holders','','home_and_living.home_decor.candles_and_holders.candleholders.candlestick_holders',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2053,2787,'Home & Living > Home Décor > Candles & Holders > Candleholders > Chandeliers','','home_and_living.home_decor.candles_and_holders.candleholders.chandeliers',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2054,2790,'Home & Living > Home Décor > Candles & Holders > Candleholders > Hurricanes','','home_and_living.home_decor.candles_and_holders.candleholders.hurricanes',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2055,2789,'Home & Living > Home Décor > Candles & Holders > Candleholders > Lanterns','','home_and_living.home_decor.candles_and_holders.candleholders.lanterns',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2056,2793,'Home & Living > Home Décor > Candles & Holders > Candleholders > Salt Lamps','','home_and_living.home_decor.candles_and_holders.candleholders.salt_lamps',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2057,2788,'Home & Living > Home Décor > Candles & Holders > Candleholders > Sconces','','home_and_living.home_decor.candles_and_holders.candleholders.sconces',2048,1);
INSERT INTO `ps_etsy_categories` VALUES (2058,2791,'Home & Living > Home Décor > Candles & Holders > Candles','','home_and_living.home_decor.candles_and_holders.candles',2047,0);
INSERT INTO `ps_etsy_categories` VALUES (2059,1005,'Home & Living > Home Décor > Candles & Holders > Candles > Container Candles','','home_and_living.home_decor.candles_and_holders.candles.container_candles',2058,1);
INSERT INTO `ps_etsy_categories` VALUES (2060,1006,'Home & Living > Home Décor > Candles & Holders > Candles > Flameless Candles','','home_and_living.home_decor.candles_and_holders.candles.flameless_candles',2058,1);
INSERT INTO `ps_etsy_categories` VALUES (2061,1009,'Home & Living > Home Décor > Candles & Holders > Candles > Pillar Candles','','home_and_living.home_decor.candles_and_holders.candles.pillar_candles',2058,1);
INSERT INTO `ps_etsy_categories` VALUES (2062,1010,'Home & Living > Home Décor > Candles & Holders > Candles > Taper Candles','','home_and_living.home_decor.candles_and_holders.candles.taper_candles',2058,1);
INSERT INTO `ps_etsy_categories` VALUES (2063,1011,'Home & Living > Home Décor > Candles & Holders > Candles > Tea Lights','','home_and_living.home_decor.candles_and_holders.candles.tea_lights',2058,1);
INSERT INTO `ps_etsy_categories` VALUES (2064,1012,'Home & Living > Home Décor > Candles & Holders > Candles > Votive Candles','','home_and_living.home_decor.candles_and_holders.candles.votive_candles',2058,1);
INSERT INTO `ps_etsy_categories` VALUES (2065,1014,'Home & Living > Home Décor > Candles & Holders > Wax Melts','','home_and_living.home_decor.candles_and_holders.wax_melts',2047,1);
INSERT INTO `ps_etsy_categories` VALUES (2066,1015,'Home & Living > Home Décor > Chair Pads & Covers','','home_and_living.home_decor.chair_pads_and_covers',2043,0);
INSERT INTO `ps_etsy_categories` VALUES (2067,2225,'Home & Living > Home Décor > Chair Pads & Covers > Chair Pads','','home_and_living.home_decor.chair_pads_and_covers.chair_pads',2066,1);
INSERT INTO `ps_etsy_categories` VALUES (2068,2226,'Home & Living > Home Décor > Chair Pads & Covers > Chair Slipcovers','','home_and_living.home_decor.chair_pads_and_covers.chair_slipcovers',2066,1);
INSERT INTO `ps_etsy_categories` VALUES (2069,1016,'Home & Living > Home Décor > Clocks','','home_and_living.home_decor.clocks',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2070,1017,'Home & Living > Home Décor > Decorative Pillows','','home_and_living.home_decor.decorative_pillows',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2071,2913,'Home & Living > Home Décor > Decorative Tiles','','home_and_living.home_decor.decorative_tiles',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2072,1018,'Home & Living > Home Décor > Decorative Trays','','home_and_living.home_decor.decorative_trays',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2073,1019,'Home & Living > Home Décor > Doilies','','home_and_living.home_decor.doilies',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2074,1020,'Home & Living > Home Décor > Floral Arrangements','','home_and_living.home_decor.floral_arrangements',2043,0);
INSERT INTO `ps_etsy_categories` VALUES (2075,2911,'Home & Living > Home Décor > Floral Arrangements > Dried Flower Arrangements','','home_and_living.home_decor.floral_arrangements.dried_flowers',2074,1);
INSERT INTO `ps_etsy_categories` VALUES (2076,2067,'Home & Living > Home Décor > Globes & Maps','','home_and_living.home_decor.globes_and_maps',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2077,2858,'Home & Living > Home Décor > Home Fragrances','','home_and_living.home_decor.home_fragrances',2043,0);
INSERT INTO `ps_etsy_categories` VALUES (2078,1007,'Home & Living > Home Décor > Home Fragrances > Incense','','home_and_living.home_decor.home_fragrances.incense',2077,1);
INSERT INTO `ps_etsy_categories` VALUES (2079,1008,'Home & Living > Home Décor > Home Fragrances > Incense Holders','','home_and_living.home_decor.home_fragrances.incense_holders',2077,1);
INSERT INTO `ps_etsy_categories` VALUES (2080,2861,'Home & Living > Home Décor > Home Fragrances > Potpourri','','home_and_living.home_decor.home_fragrances.potpourri',2077,1);
INSERT INTO `ps_etsy_categories` VALUES (2081,2862,'Home & Living > Home Décor > Home Fragrances > Sachets','','home_and_living.home_decor.home_fragrances.sachets',2077,1);
INSERT INTO `ps_etsy_categories` VALUES (2082,1021,'Home & Living > Home Décor > Mirrors','','home_and_living.home_decor.mirrors',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2083,1022,'Home & Living > Home Décor > Mobiles','','home_and_living.home_decor.mobiles',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2084,1023,'Home & Living > Home Décor > Ornaments & Accents','','home_and_living.home_decor.ornaments_and_accents',2043,0);
INSERT INTO `ps_etsy_categories` VALUES (2085,6109,'Home & Living > Home Décor > Ornaments & Accents > Christmas Trees','','home_and_living.home_decor.ornaments_and_accents.christmas_trees',2084,1);
INSERT INTO `ps_etsy_categories` VALUES (2086,1857,'Home & Living > Home Décor > Ornaments & Accents > Ornaments','','home_and_living.home_decor.ornaments_and_accents.ornaments',2084,1);
INSERT INTO `ps_etsy_categories` VALUES (2087,1858,'Home & Living > Home Décor > Ornaments & Accents > Stockings','','home_and_living.home_decor.ornaments_and_accents.stockings',2084,1);
INSERT INTO `ps_etsy_categories` VALUES (2088,2912,'Home & Living > Home Décor > Ornaments & Accents > Tree Skirts','','home_and_living.home_decor.ornaments_and_accents.tree_skirts',2084,1);
INSERT INTO `ps_etsy_categories` VALUES (2089,6108,'Home & Living > Home Décor > Ornaments & Accents > Tree Toppers','','home_and_living.home_decor.ornaments_and_accents.tree_toppers',2084,1);
INSERT INTO `ps_etsy_categories` VALUES (2090,11486,'Home & Living > Home Décor > Piggy Banks','','home_and_living.home_decor.piggy_banks',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2091,1893,'Home & Living > Home Décor > Rocks & Geodes','','home_and_living.home_decor.rocks_and_geodes',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2092,2869,'Home & Living > Home Décor > Statues','','home_and_living.home_decor.statues',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2093,1025,'Home & Living > Home Décor > Taxidermy & Curiosities','','home_and_living.home_decor.taxidermy_and_curiosities',2043,0);
INSERT INTO `ps_etsy_categories` VALUES (2094,6086,'Home & Living > Home Décor > Taxidermy & Curiosities > Animal Mounts','','home_and_living.home_decor.taxidermy_and_curiosities.animal_mounts',2093,1);
INSERT INTO `ps_etsy_categories` VALUES (2095,6087,'Home & Living > Home Décor > Taxidermy & Curiosities > Bones & Skulls','','home_and_living.home_decor.taxidermy_and_curiosities.bones_and_skulls',2093,1);
INSERT INTO `ps_etsy_categories` VALUES (2096,6088,'Home & Living > Home Décor > Taxidermy & Curiosities > Faux Taxidermy','','home_and_living.home_decor.taxidermy_and_curiosities.faux_taxidermy',2093,1);
INSERT INTO `ps_etsy_categories` VALUES (2097,6085,'Home & Living > Home Décor > Taxidermy & Curiosities > Insects','','home_and_living.home_decor.taxidermy_and_curiosities.insects',2093,1);
INSERT INTO `ps_etsy_categories` VALUES (2098,1026,'Home & Living > Home Décor > Vases','','home_and_living.home_decor.vases',2043,1);
INSERT INTO `ps_etsy_categories` VALUES (2099,1027,'Home & Living > Home Décor > Wall Décor','','home_and_living.home_decor.wall_decor',2043,0);
INSERT INTO `ps_etsy_categories` VALUES (2100,1028,'Home & Living > Home Décor > Wall Décor > Wall Decals & Murals','','home_and_living.home_decor.wall_decor.wall_decals_and_murals',2099,0);
INSERT INTO `ps_etsy_categories` VALUES (2101,1986,'Home & Living > Home Décor > Wall Décor > Wall Decals & Murals > Murals','','home_and_living.home_decor.wall_decor.wall_decals_and_murals.murals',2100,1);
INSERT INTO `ps_etsy_categories` VALUES (2102,1987,'Home & Living > Home Décor > Wall Décor > Wall Decals & Murals > Quotations','','home_and_living.home_decor.wall_decor.wall_decals_and_murals.quotations',2100,1);
INSERT INTO `ps_etsy_categories` VALUES (2103,1988,'Home & Living > Home Décor > Wall Décor > Wall Decals & Murals > Shapes','','home_and_living.home_decor.wall_decor.wall_decals_and_murals.shapes',2100,1);
INSERT INTO `ps_etsy_categories` VALUES (2104,1029,'Home & Living > Home Décor > Wall Décor > Wall Hangings','','home_and_living.home_decor.wall_decor.wall_hangings',2099,0);
INSERT INTO `ps_etsy_categories` VALUES (2105,2844,'Home & Living > Home Décor > Wall Décor > Wall Hangings > Signs','','home_and_living.home_decor.wall_decor.wall_hangings.signs',2104,0);
INSERT INTO `ps_etsy_categories` VALUES (2106,2845,'Home & Living > Home Décor > Wall Décor > Wall Hangings > Signs > Address Signs','','home_and_living.home_decor.wall_decor.wall_hangings.signs.address_signs',2105,1);
INSERT INTO `ps_etsy_categories` VALUES (2107,1328,'Home & Living > Home Décor > Wall Décor > Wall Stencils','','home_and_living.home_decor.wall_decor.wall_stencils',2099,1);
INSERT INTO `ps_etsy_categories` VALUES (2108,6112,'Home & Living > Home Décor > Wall Décor > Wallpaper','','home_and_living.home_decor.wall_decor.wallpaper',2099,1);
INSERT INTO `ps_etsy_categories` VALUES (2109,1030,'Home & Living > Home Décor > Wreaths & Door Hangers','','home_and_living.home_decor.wreaths_and_door_hangers',2043,0);
INSERT INTO `ps_etsy_categories` VALUES (2110,1931,'Home & Living > Home Décor > Wreaths & Door Hangers > Door Hangers','','home_and_living.home_decor.wreaths_and_door_hangers.door_hangers',2109,1);
INSERT INTO `ps_etsy_categories` VALUES (2111,1930,'Home & Living > Home Décor > Wreaths & Door Hangers > Wreaths','','home_and_living.home_decor.wreaths_and_door_hangers.wreaths',2109,1);
INSERT INTO `ps_etsy_categories` VALUES (2112,1031,'Home & Living > Kitchen & Dining','','home_and_living.kitchen_and_dining',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (2113,894,'Home & Living > Kitchen & Dining > Coffee & Tea Makers','','home_and_living.kitchen_and_dining.coffee_and_tea_makers',2112,0);
INSERT INTO `ps_etsy_categories` VALUES (2114,1909,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Coffee Makers','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.coffee_makers',2113,0);
INSERT INTO `ps_etsy_categories` VALUES (2115,2340,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Coffee Makers > Coffee Grinders','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.coffee_makers.coffee_grinders',2114,1);
INSERT INTO `ps_etsy_categories` VALUES (2116,2314,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Coffee Makers > Coffee Machines','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.coffee_makers.coffee_machines',2114,1);
INSERT INTO `ps_etsy_categories` VALUES (2117,2315,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Coffee Makers > Espresso Makers','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.coffee_makers.espresso_makers',2114,1);
INSERT INTO `ps_etsy_categories` VALUES (2118,2313,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Coffee Makers > Percolators','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.coffee_makers.percolators',2114,1);
INSERT INTO `ps_etsy_categories` VALUES (2119,2312,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Coffee Makers > Pour Over & Drippers','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.coffee_makers.pour_over_and_drippers',2114,1);
INSERT INTO `ps_etsy_categories` VALUES (2120,2311,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Coffee Makers > Turkish Coffee Sets','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.coffee_makers.turkish_coffee_sets',2114,1);
INSERT INTO `ps_etsy_categories` VALUES (2121,1910,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Tea Makers','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.tea_makers',2113,0);
INSERT INTO `ps_etsy_categories` VALUES (2122,1933,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Tea Makers > Kettles','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.tea_makers.kettles',2121,1);
INSERT INTO `ps_etsy_categories` VALUES (2123,1932,'Home & Living > Kitchen & Dining > Coffee & Tea Makers > Tea Makers > Teapots','','home_and_living.kitchen_and_dining.coffee_and_tea_makers.tea_makers.teapots',2121,1);
INSERT INTO `ps_etsy_categories` VALUES (2124,1033,'Home & Living > Kitchen & Dining > Cookware','','home_and_living.kitchen_and_dining.cookware',2112,0);
INSERT INTO `ps_etsy_categories` VALUES (2125,1034,'Home & Living > Kitchen & Dining > Cookware > Casserole Dishes','','home_and_living.kitchen_and_dining.cookware.casserole_dishes',2124,1);
INSERT INTO `ps_etsy_categories` VALUES (2126,1035,'Home & Living > Kitchen & Dining > Cookware > Colanders & Strainers','','home_and_living.kitchen_and_dining.cookware.colanders_and_strainers',2124,1);
INSERT INTO `ps_etsy_categories` VALUES (2127,1036,'Home & Living > Kitchen & Dining > Cookware > Cooking Utensils & Gadgets','','home_and_living.kitchen_and_dining.cookware.cooking_utensils_and_gadgets',2124,1);
INSERT INTO `ps_etsy_categories` VALUES (2128,1037,'Home & Living > Kitchen & Dining > Cookware > Cutlery & Knives','','home_and_living.kitchen_and_dining.cookware.cutlery_and_knives',2124,0);
INSERT INTO `ps_etsy_categories` VALUES (2129,2325,'Home & Living > Kitchen & Dining > Cookware > Cutlery & Knives > Cutlery','','home_and_living.kitchen_and_dining.cookware.cutlery_and_knives.cutlery',2128,1);
INSERT INTO `ps_etsy_categories` VALUES (2130,2326,'Home & Living > Kitchen & Dining > Cookware > Cutlery & Knives > Knives','','home_and_living.kitchen_and_dining.cookware.cutlery_and_knives.knives',2128,1);
INSERT INTO `ps_etsy_categories` VALUES (2131,1038,'Home & Living > Kitchen & Dining > Cookware > Cutting Boards','','home_and_living.kitchen_and_dining.cookware.cutting_boards',2124,1);
INSERT INTO `ps_etsy_categories` VALUES (2132,1039,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans','','home_and_living.kitchen_and_dining.cookware.pots_and_pans',2124,0);
INSERT INTO `ps_etsy_categories` VALUES (2133,2296,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Lids','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.lids',2132,1);
INSERT INTO `ps_etsy_categories` VALUES (2134,1947,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pans','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pans',2132,0);
INSERT INTO `ps_etsy_categories` VALUES (2135,2293,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pans > Griddles','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pans.griddles',2134,1);
INSERT INTO `ps_etsy_categories` VALUES (2136,2291,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pans > Roasting Pans','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pans.roasting_pans',2134,1);
INSERT INTO `ps_etsy_categories` VALUES (2137,2288,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pans > Saucepans','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pans.saucepans',2134,1);
INSERT INTO `ps_etsy_categories` VALUES (2138,2298,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pans > Sauté Pans','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pans.saute_pans',2134,1);
INSERT INTO `ps_etsy_categories` VALUES (2139,2287,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pans > Skillets','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pans.skillets',2134,0);
INSERT INTO `ps_etsy_categories` VALUES (2140,2297,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pans > Skillets > Cast Iron Skillets','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pans.skillets.cast_iron_skillets',2139,1);
INSERT INTO `ps_etsy_categories` VALUES (2141,1948,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pot & Pan Sets','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pot_and_pan_sets',2132,1);
INSERT INTO `ps_etsy_categories` VALUES (2142,1946,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pots','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pots',2132,0);
INSERT INTO `ps_etsy_categories` VALUES (2143,2289,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pots > Double Boilers','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pots.double_boilers',2142,1);
INSERT INTO `ps_etsy_categories` VALUES (2144,2290,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pots > Dutch Ovens','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pots.dutch_ovens',2142,1);
INSERT INTO `ps_etsy_categories` VALUES (2145,2292,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pots > Fondue Sets','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pots.fondue_sets',2142,1);
INSERT INTO `ps_etsy_categories` VALUES (2146,2299,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pots > Pressure Cookers','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pots.pressure_cookers',2142,1);
INSERT INTO `ps_etsy_categories` VALUES (2147,2286,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Pots > Stockpots','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.pots.stockpots',2142,1);
INSERT INTO `ps_etsy_categories` VALUES (2148,2294,'Home & Living > Kitchen & Dining > Cookware > Pots & Pans > Woks','','home_and_living.kitchen_and_dining.cookware.pots_and_pans.woks',2132,1);
INSERT INTO `ps_etsy_categories` VALUES (2149,1040,'Home & Living > Kitchen & Dining > Cookware > Spoon Rests','','home_and_living.kitchen_and_dining.cookware.spoon_rests',2124,1);
INSERT INTO `ps_etsy_categories` VALUES (2150,1042,'Home & Living > Kitchen & Dining > Cookware > Trivets & Pot Holders','','home_and_living.kitchen_and_dining.cookware.trivets_and_pot_holders',2124,0);
INSERT INTO `ps_etsy_categories` VALUES (2151,6070,'Home & Living > Kitchen & Dining > Cookware > Trivets & Pot Holders > Pot Holders','','home_and_living.kitchen_and_dining.cookware.trivets_and_pot_holders.pot_holders',2150,1);
INSERT INTO `ps_etsy_categories` VALUES (2152,6069,'Home & Living > Kitchen & Dining > Cookware > Trivets & Pot Holders > Trivets','','home_and_living.kitchen_and_dining.cookware.trivets_and_pot_holders.trivets',2150,1);
INSERT INTO `ps_etsy_categories` VALUES (2153,1043,'Home & Living > Kitchen & Dining > Dining & Serving','','home_and_living.kitchen_and_dining.dining_and_serving',2112,0);
INSERT INTO `ps_etsy_categories` VALUES (2154,1044,'Home & Living > Kitchen & Dining > Dining & Serving > Bowls','','home_and_living.kitchen_and_dining.dining_and_serving.bowls',2153,1);
INSERT INTO `ps_etsy_categories` VALUES (2155,1046,'Home & Living > Kitchen & Dining > Dining & Serving > Cake Stands','','home_and_living.kitchen_and_dining.dining_and_serving.cake_stands',2153,1);
INSERT INTO `ps_etsy_categories` VALUES (2156,1047,'Home & Living > Kitchen & Dining > Dining & Serving > Dinnerware Sets','','home_and_living.kitchen_and_dining.dining_and_serving.dinnerware_sets',2153,1);
INSERT INTO `ps_etsy_categories` VALUES (2157,1048,'Home & Living > Kitchen & Dining > Dining & Serving > Flatware & Silverware','','home_and_living.kitchen_and_dining.dining_and_serving.flatware_and_silverware',2153,1);
INSERT INTO `ps_etsy_categories` VALUES (2158,1049,'Home & Living > Kitchen & Dining > Dining & Serving > Plates','','home_and_living.kitchen_and_dining.dining_and_serving.plates',2153,1);
INSERT INTO `ps_etsy_categories` VALUES (2159,1050,'Home & Living > Kitchen & Dining > Dining & Serving > Salt & Pepper Shakers','','home_and_living.kitchen_and_dining.dining_and_serving.salt_and_pepper_shakers',2153,1);
INSERT INTO `ps_etsy_categories` VALUES (2160,1051,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Odds & Ends','','home_and_living.kitchen_and_dining.dining_and_serving.serving_odds_and_ends',2153,0);
INSERT INTO `ps_etsy_categories` VALUES (2161,1045,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Odds & Ends > Butter Dishes','','home_and_living.kitchen_and_dining.dining_and_serving.serving_odds_and_ends.butter_dishes',2160,1);
INSERT INTO `ps_etsy_categories` VALUES (2162,2639,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Odds & Ends > Gravy Boats','','home_and_living.kitchen_and_dining.dining_and_serving.serving_odds_and_ends.gravy_boats',2160,1);
INSERT INTO `ps_etsy_categories` VALUES (2163,2640,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Odds & Ends > Sugar Bowls & Creamers','','home_and_living.kitchen_and_dining.dining_and_serving.serving_odds_and_ends.sugar_bowls_and_creamers',2160,0);
INSERT INTO `ps_etsy_categories` VALUES (2164,2642,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Odds & Ends > Sugar Bowls & Creamers > Creamers','','home_and_living.kitchen_and_dining.dining_and_serving.serving_odds_and_ends.sugar_bowls_and_creamers.creamers',2163,1);
INSERT INTO `ps_etsy_categories` VALUES (2165,2927,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Odds & Ends > Sugar Bowls & Creamers > Honey Pots','','home_and_living.kitchen_and_dining.dining_and_serving.serving_odds_and_ends.sugar_bowls_and_creamers.honey_pots',2163,1);
INSERT INTO `ps_etsy_categories` VALUES (2166,2643,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Odds & Ends > Sugar Bowls & Creamers > Sets','','home_and_living.kitchen_and_dining.dining_and_serving.serving_odds_and_ends.sugar_bowls_and_creamers.sets',2163,1);
INSERT INTO `ps_etsy_categories` VALUES (2167,2641,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Odds & Ends > Sugar Bowls & Creamers > Sugar Bowls','','home_and_living.kitchen_and_dining.dining_and_serving.serving_odds_and_ends.sugar_bowls_and_creamers.sugar_bowls',2163,1);
INSERT INTO `ps_etsy_categories` VALUES (2168,1052,'Home & Living > Kitchen & Dining > Dining & Serving > Serving Utensils','','home_and_living.kitchen_and_dining.dining_and_serving.serving_utensils',2153,1);
INSERT INTO `ps_etsy_categories` VALUES (2169,1053,'Home & Living > Kitchen & Dining > Dining & Serving > Trays & Platters','','home_and_living.kitchen_and_dining.dining_and_serving.trays_and_platters',2153,0);
INSERT INTO `ps_etsy_categories` VALUES (2170,2538,'Home & Living > Kitchen & Dining > Dining & Serving > Trays & Platters > Platters','','home_and_living.kitchen_and_dining.dining_and_serving.trays_and_platters.platters',2169,1);
INSERT INTO `ps_etsy_categories` VALUES (2171,6084,'Home & Living > Kitchen & Dining > Dining & Serving > Trays & Platters > Snack Sets','','home_and_living.kitchen_and_dining.dining_and_serving.trays_and_platters.snack_sets',2169,1);
INSERT INTO `ps_etsy_categories` VALUES (2172,2537,'Home & Living > Kitchen & Dining > Dining & Serving > Trays & Platters > Trays','','home_and_living.kitchen_and_dining.dining_and_serving.trays_and_platters.trays',2169,1);
INSERT INTO `ps_etsy_categories` VALUES (2173,1054,'Home & Living > Kitchen & Dining > Drink & Barware','','home_and_living.kitchen_and_dining.drink_and_barware',2112,0);
INSERT INTO `ps_etsy_categories` VALUES (2174,1863,'Home & Living > Kitchen & Dining > Drink & Barware > Barware','','home_and_living.kitchen_and_dining.drink_and_barware.barware',2173,0);
INSERT INTO `ps_etsy_categories` VALUES (2175,1055,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Bar Carts & Bars','','home_and_living.kitchen_and_dining.drink_and_barware.barware.bar_carts_and_bars',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2176,1057,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Bottle Openers','','home_and_living.kitchen_and_dining.drink_and_barware.barware.bottle_openers',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2177,1058,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Bottle Stoppers','','home_and_living.kitchen_and_dining.drink_and_barware.barware.bottle_stoppers',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2178,1059,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Champagne & Coupe Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.barware.champagne_and_coupe_glasses',2174,0);
INSERT INTO `ps_etsy_categories` VALUES (2179,1934,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Champagne & Coupe Glasses > Champagne Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.barware.champagne_and_coupe_glasses.champagne_glasses',2178,1);
INSERT INTO `ps_etsy_categories` VALUES (2180,1935,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Champagne & Coupe Glasses > Coupe Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.barware.champagne_and_coupe_glasses.coupe_glasses',2178,1);
INSERT INTO `ps_etsy_categories` VALUES (2181,1061,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Cocktail Shakers','','home_and_living.kitchen_and_dining.drink_and_barware.barware.cocktail_shakers',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2182,1064,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Decanters','','home_and_living.kitchen_and_dining.drink_and_barware.barware.decanters',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2183,11274,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Flasks','','home_and_living.kitchen_and_dining.drink_and_barware.barware.flasks',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2184,1065,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Ice Buckets','','home_and_living.kitchen_and_dining.drink_and_barware.barware.ice_buckets',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2185,1066,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Martini & Cocktail Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.barware.martini_and_cocktail_glasses',2174,0);
INSERT INTO `ps_etsy_categories` VALUES (2186,1950,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Martini & Cocktail Glasses > Julep Cups','','home_and_living.kitchen_and_dining.drink_and_barware.barware.martini_and_cocktail_glasses.julep_cups',2185,1);
INSERT INTO `ps_etsy_categories` VALUES (2187,1951,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Martini & Cocktail Glasses > Margarita Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.barware.martini_and_cocktail_glasses.margarita_glasses',2185,1);
INSERT INTO `ps_etsy_categories` VALUES (2188,1949,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Martini & Cocktail Glasses > Martini Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.barware.martini_and_cocktail_glasses.martini_glasses',2185,1);
INSERT INTO `ps_etsy_categories` VALUES (2189,1068,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Shot Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.barware.shot_glasses',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2190,1861,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Steins','','home_and_living.kitchen_and_dining.drink_and_barware.barware.steins',2174,1);
INSERT INTO `ps_etsy_categories` VALUES (2191,1072,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Wine Glasses & Charms','','home_and_living.kitchen_and_dining.drink_and_barware.barware.wine_glasses_and_charms',2174,0);
INSERT INTO `ps_etsy_categories` VALUES (2192,1936,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Wine Glasses & Charms > Wine Charms','','home_and_living.kitchen_and_dining.drink_and_barware.barware.wine_glasses_and_charms.wine_charms',2191,1);
INSERT INTO `ps_etsy_categories` VALUES (2193,1937,'Home & Living > Kitchen & Dining > Drink & Barware > Barware > Wine Glasses & Charms > Wine Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.barware.wine_glasses_and_charms.wine_glasses',2191,1);
INSERT INTO `ps_etsy_categories` VALUES (2194,1862,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware',2173,0);
INSERT INTO `ps_etsy_categories` VALUES (2195,1060,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Coasters','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.coasters',2194,1);
INSERT INTO `ps_etsy_categories` VALUES (2196,1063,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Cozies','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.cozies',2194,0);
INSERT INTO `ps_etsy_categories` VALUES (2197,1881,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Cozies > Tea Cozies','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.cozies.tea_cozies',2196,1);
INSERT INTO `ps_etsy_categories` VALUES (2198,2663,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Juice Sets & Carafes','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.juice_sets_and_carafes',2194,0);
INSERT INTO `ps_etsy_categories` VALUES (2199,2665,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Juice Sets & Carafes > Carafes','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.juice_sets_and_carafes.carafes',2198,1);
INSERT INTO `ps_etsy_categories` VALUES (2200,2664,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Juice Sets & Carafes > Juice Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.juice_sets_and_carafes.juice_glasses',2198,1);
INSERT INTO `ps_etsy_categories` VALUES (2201,2666,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Juice Sets & Carafes > Juice Sets','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.juice_sets_and_carafes.juice_sets',2198,1);
INSERT INTO `ps_etsy_categories` VALUES (2202,2667,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Juice Sets & Carafes > Juicers','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.juice_sets_and_carafes.juicers',2198,1);
INSERT INTO `ps_etsy_categories` VALUES (2203,1062,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Mugs','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.mugs',2194,0);
INSERT INTO `ps_etsy_categories` VALUES (2204,11275,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Mugs > Travel Mugs','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.mugs.travel_mugs',2203,1);
INSERT INTO `ps_etsy_categories` VALUES (2205,1056,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Pint Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.pint_glasses',2194,1);
INSERT INTO `ps_etsy_categories` VALUES (2206,1067,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Pitchers & Drinking Sets','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.pitchers_and_drinking_sets',2194,0);
INSERT INTO `ps_etsy_categories` VALUES (2207,1939,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Pitchers & Drinking Sets > Pitcher Sets','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.pitchers_and_drinking_sets.pitcher_sets',2206,1);
INSERT INTO `ps_etsy_categories` VALUES (2208,1938,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Pitchers & Drinking Sets > Pitchers','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.pitchers_and_drinking_sets.pitchers',2206,1);
INSERT INTO `ps_etsy_categories` VALUES (2209,1880,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Punch Bowls & Sets','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.punch_bowls_and_sets',2194,0);
INSERT INTO `ps_etsy_categories` VALUES (2210,1941,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Punch Bowls & Sets > Punch Bowl Sets','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.punch_bowls_and_sets.punch_bowl_sets',2209,1);
INSERT INTO `ps_etsy_categories` VALUES (2211,1940,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Punch Bowls & Sets > Punch Bowls','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.punch_bowls_and_sets.punch_bowls',2209,1);
INSERT INTO `ps_etsy_categories` VALUES (2212,1069,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Tea Cups & Sets','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.tea_cups_and_sets',2194,0);
INSERT INTO `ps_etsy_categories` VALUES (2213,1942,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Tea Cups & Sets > Tea Cups','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.tea_cups_and_sets.tea_cups',2212,1);
INSERT INTO `ps_etsy_categories` VALUES (2214,1943,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Tea Cups & Sets > Tea Sets','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.tea_cups_and_sets.tea_sets',2212,1);
INSERT INTO `ps_etsy_categories` VALUES (2215,1071,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Tumblers & Water Glasses','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.tumblers_and_water_glasses',2194,1);
INSERT INTO `ps_etsy_categories` VALUES (2216,1070,'Home & Living > Kitchen & Dining > Drink & Barware > Drinkware > Water Bottles','','home_and_living.kitchen_and_dining.drink_and_barware.drinkware.water_bottles',2194,1);
INSERT INTO `ps_etsy_categories` VALUES (2217,1073,'Home & Living > Kitchen & Dining > Kitchen Décor','','home_and_living.kitchen_and_dining.kitchen_decor',2112,0);
INSERT INTO `ps_etsy_categories` VALUES (2218,1074,'Home & Living > Kitchen & Dining > Kitchen Décor > Centerpieces & Table Décor','','home_and_living.kitchen_and_dining.kitchen_decor.centerpieces_and_table_decor',2217,0);
INSERT INTO `ps_etsy_categories` VALUES (2219,1944,'Home & Living > Kitchen & Dining > Kitchen Décor > Centerpieces & Table Décor > Centerpieces','','home_and_living.kitchen_and_dining.kitchen_decor.centerpieces_and_table_decor.centerpieces',2218,1);
INSERT INTO `ps_etsy_categories` VALUES (2220,1075,'Home & Living > Kitchen & Dining > Kitchen Décor > Refrigerator Magnets','','home_and_living.kitchen_and_dining.kitchen_decor.refrigerator_magnets',2217,1);
INSERT INTO `ps_etsy_categories` VALUES (2221,1076,'Home & Living > Kitchen & Dining > Kitchen Storage','','home_and_living.kitchen_and_dining.kitchen_storage',2112,0);
INSERT INTO `ps_etsy_categories` VALUES (2222,1077,'Home & Living > Kitchen & Dining > Kitchen Storage > Jars & Containers','','home_and_living.kitchen_and_dining.kitchen_storage.jars_and_containers',2221,0);
INSERT INTO `ps_etsy_categories` VALUES (2223,6083,'Home & Living > Kitchen & Dining > Kitchen Storage > Jars & Containers > Canister Sets','','home_and_living.kitchen_and_dining.kitchen_storage.jars_and_containers.canister_sets',2222,1);
INSERT INTO `ps_etsy_categories` VALUES (2224,6059,'Home & Living > Kitchen & Dining > Kitchen Storage > Jars & Containers > Cookie Jars','','home_and_living.kitchen_and_dining.kitchen_storage.jars_and_containers.cookie_jars',2222,1);
INSERT INTO `ps_etsy_categories` VALUES (2225,1859,'Home & Living > Kitchen & Dining > Kitchen Storage > Knife Blocks & Storage','','home_and_living.kitchen_and_dining.kitchen_storage.knife_blocks_and_storage',2221,1);
INSERT INTO `ps_etsy_categories` VALUES (2226,1078,'Home & Living > Kitchen & Dining > Kitchen Storage > Napkin Holders','','home_and_living.kitchen_and_dining.kitchen_storage.napkin_holders',2221,1);
INSERT INTO `ps_etsy_categories` VALUES (2227,2295,'Home & Living > Kitchen & Dining > Kitchen Storage > Pot Racks','','home_and_living.kitchen_and_dining.kitchen_storage.pot_racks',2221,1);
INSERT INTO `ps_etsy_categories` VALUES (2228,2921,'Home & Living > Kitchen & Dining > Kitchen Storage > Recipe Storage','','home_and_living.kitchen_and_dining.kitchen_storage.recipe_storage',2221,0);
INSERT INTO `ps_etsy_categories` VALUES (2229,2922,'Home & Living > Kitchen & Dining > Kitchen Storage > Recipe Storage > Recipe Boxes','','home_and_living.kitchen_and_dining.kitchen_storage.recipe_storage.recipe_boxes',2228,1);
INSERT INTO `ps_etsy_categories` VALUES (2230,2923,'Home & Living > Kitchen & Dining > Kitchen Storage > Recipe Storage > Recipe Stands','','home_and_living.kitchen_and_dining.kitchen_storage.recipe_storage.recipe_stands',2228,1);
INSERT INTO `ps_etsy_categories` VALUES (2231,1079,'Home & Living > Kitchen & Dining > Kitchen Storage > Spice Racks','','home_and_living.kitchen_and_dining.kitchen_storage.spice_racks',2221,1);
INSERT INTO `ps_etsy_categories` VALUES (2232,1889,'Home & Living > Kitchen & Dining > Kitchen Storage > Wine & Beer Storage','','home_and_living.kitchen_and_dining.kitchen_storage.wine_and_beer_storage',2221,0);
INSERT INTO `ps_etsy_categories` VALUES (2233,1890,'Home & Living > Kitchen & Dining > Kitchen Storage > Wine & Beer Storage > Beer Caddies','','home_and_living.kitchen_and_dining.kitchen_storage.wine_and_beer_storage.beer_caddies',2232,1);
INSERT INTO `ps_etsy_categories` VALUES (2234,1080,'Home & Living > Kitchen & Dining > Kitchen Storage > Wine & Beer Storage > Wine Racks','','home_and_living.kitchen_and_dining.kitchen_storage.wine_and_beer_storage.wine_racks',2232,1);
INSERT INTO `ps_etsy_categories` VALUES (2235,1081,'Home & Living > Kitchen & Dining > Linens','','home_and_living.kitchen_and_dining.linens',2112,0);
INSERT INTO `ps_etsy_categories` VALUES (2236,1082,'Home & Living > Kitchen & Dining > Linens > Aprons','','home_and_living.kitchen_and_dining.linens.aprons',2235,1);
INSERT INTO `ps_etsy_categories` VALUES (2237,1083,'Home & Living > Kitchen & Dining > Linens > Dishcloths & Kitchen Towels','','home_and_living.kitchen_and_dining.linens.dishcloths_and_kitchen_towels',2235,0);
INSERT INTO `ps_etsy_categories` VALUES (2238,1864,'Home & Living > Kitchen & Dining > Linens > Dishcloths & Kitchen Towels > Tea Towels','','home_and_living.kitchen_and_dining.linens.dishcloths_and_kitchen_towels.tea_towels',2237,1);
INSERT INTO `ps_etsy_categories` VALUES (2239,1085,'Home & Living > Kitchen & Dining > Linens > Table Linens','','home_and_living.kitchen_and_dining.linens.table_linens',2235,0);
INSERT INTO `ps_etsy_categories` VALUES (2240,1953,'Home & Living > Kitchen & Dining > Linens > Table Linens > Napkins','','home_and_living.kitchen_and_dining.linens.table_linens.napkins',2239,1);
INSERT INTO `ps_etsy_categories` VALUES (2241,1952,'Home & Living > Kitchen & Dining > Linens > Table Linens > Placemats','','home_and_living.kitchen_and_dining.linens.table_linens.placemats',2239,1);
INSERT INTO `ps_etsy_categories` VALUES (2242,1955,'Home & Living > Kitchen & Dining > Linens > Table Linens > Sets','','home_and_living.kitchen_and_dining.linens.table_linens.sets',2239,1);
INSERT INTO `ps_etsy_categories` VALUES (2243,1945,'Home & Living > Kitchen & Dining > Linens > Table Linens > Table Runners','','home_and_living.kitchen_and_dining.linens.table_linens.table_runners',2239,1);
INSERT INTO `ps_etsy_categories` VALUES (2244,1954,'Home & Living > Kitchen & Dining > Linens > Table Linens > Tablecloths','','home_and_living.kitchen_and_dining.linens.table_linens.tablecloths',2239,1);
INSERT INTO `ps_etsy_categories` VALUES (2245,2301,'Home & Living > Kitchen & Dining > Small Kitchen Appliances','','home_and_living.kitchen_and_dining.small_kitchen_appliances',2112,1);
INSERT INTO `ps_etsy_categories` VALUES (2246,1086,'Home & Living > Lighting','','home_and_living.lighting',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (2247,1087,'Home & Living > Lighting > Chandeliers & Pendant Lights','','home_and_living.lighting.chandeliers_and_pendant_lights',2246,0);
INSERT INTO `ps_etsy_categories` VALUES (2248,2195,'Home & Living > Lighting > Chandeliers & Pendant Lights > Chandeliers','','home_and_living.lighting.chandeliers_and_pendant_lights.chandeliers',2247,1);
INSERT INTO `ps_etsy_categories` VALUES (2249,2196,'Home & Living > Lighting > Chandeliers & Pendant Lights > Pendant Lights','','home_and_living.lighting.chandeliers_and_pendant_lights.pendant_lights',2247,1);
INSERT INTO `ps_etsy_categories` VALUES (2250,1088,'Home & Living > Lighting > Fixtures','','home_and_living.lighting.fixtures',2246,1);
INSERT INTO `ps_etsy_categories` VALUES (2251,1090,'Home & Living > Lighting > Lamp Shades','','home_and_living.lighting.lamp_shades',2246,1);
INSERT INTO `ps_etsy_categories` VALUES (2252,1089,'Home & Living > Lighting > Lamps','','home_and_living.lighting.lamps',2246,0);
INSERT INTO `ps_etsy_categories` VALUES (2253,2914,'Home & Living > Lighting > Lamps > Accent Lamps','','home_and_living.lighting.lamps.accent_lamps',2252,1);
INSERT INTO `ps_etsy_categories` VALUES (2254,2813,'Home & Living > Lighting > Lamps > Desk Lamps','','home_and_living.lighting.lamps.desk_lamps',2252,1);
INSERT INTO `ps_etsy_categories` VALUES (2255,2814,'Home & Living > Lighting > Lamps > Floor Lamps','','home_and_living.lighting.lamps.floor_lamps',2252,1);
INSERT INTO `ps_etsy_categories` VALUES (2256,2815,'Home & Living > Lighting > Lamps > Table Lamps','','home_and_living.lighting.lamps.table_lamps',2252,1);
INSERT INTO `ps_etsy_categories` VALUES (2257,1091,'Home & Living > Lighting > Lanterns','','home_and_living.lighting.lanterns',2246,1);
INSERT INTO `ps_etsy_categories` VALUES (2258,1092,'Home & Living > Lighting > Light Pulls','','home_and_living.lighting.light_pulls',2246,1);
INSERT INTO `ps_etsy_categories` VALUES (2259,1093,'Home & Living > Lighting > Night Lights','','home_and_living.lighting.night_lights',2246,1);
INSERT INTO `ps_etsy_categories` VALUES (2260,1094,'Home & Living > Lighting > Sconces','','home_and_living.lighting.sconces',2246,1);
INSERT INTO `ps_etsy_categories` VALUES (2261,1095,'Home & Living > Lighting > Switchplates','','home_and_living.lighting.switchplates',2246,1);
INSERT INTO `ps_etsy_categories` VALUES (2262,1096,'Home & Living > Office','','home_and_living.office',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (2263,1097,'Home & Living > Office > Banks & Cash Boxes','','home_and_living.office.banks_and_cash_boxes',2262,1);
INSERT INTO `ps_etsy_categories` VALUES (2264,1098,'Home & Living > Office > Chalkboards','','home_and_living.office.chalkboards',2262,1);
INSERT INTO `ps_etsy_categories` VALUES (2265,1099,'Home & Living > Office > Dry Erase Boards','','home_and_living.office.dry_erase_boards',2262,1);
INSERT INTO `ps_etsy_categories` VALUES (2266,1100,'Home & Living > Office > Message & Bulletin Boards','','home_and_living.office.message_and_bulletin_boards',2262,1);
INSERT INTO `ps_etsy_categories` VALUES (2267,1101,'Home & Living > Office > Office & Desk Storage','','home_and_living.office.office_and_desk_storage',2262,1);
INSERT INTO `ps_etsy_categories` VALUES (2268,1102,'Home & Living > Office > Office & School Supplies','','home_and_living.office.office_and_school_supplies',2262,1);
INSERT INTO `ps_etsy_categories` VALUES (2269,1103,'Home & Living > Office > Paperweights','','home_and_living.office.paperweights',2262,1);
INSERT INTO `ps_etsy_categories` VALUES (2270,1104,'Home & Living > Office > Typewriters','','home_and_living.office.typewriters',2262,1);
INSERT INTO `ps_etsy_categories` VALUES (2271,1105,'Home & Living > Outdoor & Gardening','','home_and_living.outdoor_and_garden',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (2272,1106,'Home & Living > Outdoor & Gardening > Coolers','','home_and_living.outdoor_and_garden.coolers',2271,1);
INSERT INTO `ps_etsy_categories` VALUES (2273,1107,'Home & Living > Outdoor & Gardening > Feeders & Birdhouses','','home_and_living.outdoor_and_garden.feeders_and_birdhouses',2271,0);
INSERT INTO `ps_etsy_categories` VALUES (2274,1956,'Home & Living > Outdoor & Gardening > Feeders & Birdhouses > Bird Feeders','','home_and_living.outdoor_and_garden.feeders_and_birdhouses.bird_feeders',2273,1);
INSERT INTO `ps_etsy_categories` VALUES (2275,1957,'Home & Living > Outdoor & Gardening > Feeders & Birdhouses > Birdhouses','','home_and_living.outdoor_and_garden.feeders_and_birdhouses.birdhouses',2273,1);
INSERT INTO `ps_etsy_categories` VALUES (2276,1108,'Home & Living > Outdoor & Gardening > Fire Pits & Wood','','home_and_living.outdoor_and_garden.fire_pits',2271,0);
INSERT INTO `ps_etsy_categories` VALUES (2277,1974,'Home & Living > Outdoor & Gardening > Fire Pits & Wood > Fire Pit Tools','','home_and_living.outdoor_and_garden.fire_pits.fire_pit_tools',2276,1);
INSERT INTO `ps_etsy_categories` VALUES (2278,1973,'Home & Living > Outdoor & Gardening > Fire Pits & Wood > Fire Pits','','home_and_living.outdoor_and_garden.fire_pits.fire_pits',2276,1);
INSERT INTO `ps_etsy_categories` VALUES (2279,1972,'Home & Living > Outdoor & Gardening > Fire Pits & Wood > Firewood','','home_and_living.outdoor_and_garden.fire_pits.firewood',2276,1);
INSERT INTO `ps_etsy_categories` VALUES (2280,1975,'Home & Living > Outdoor & Gardening > Fire Pits & Wood > Firewood Storage','','home_and_living.outdoor_and_garden.fire_pits.firewood_storage',2276,1);
INSERT INTO `ps_etsy_categories` VALUES (2281,1109,'Home & Living > Outdoor & Gardening > Garden Boxes','','home_and_living.outdoor_and_garden.garden_boxes',2271,1);
INSERT INTO `ps_etsy_categories` VALUES (2282,1110,'Home & Living > Outdoor & Gardening > Garden Decoration','','home_and_living.outdoor_and_garden.garden_decoration',2271,0);
INSERT INTO `ps_etsy_categories` VALUES (2283,2867,'Home & Living > Outdoor & Gardening > Garden Decoration > Outdoor Statues','','home_and_living.outdoor_and_garden.garden_decoration.outdoor_statues',2282,0);
INSERT INTO `ps_etsy_categories` VALUES (2284,2868,'Home & Living > Outdoor & Gardening > Garden Decoration > Outdoor Statues > Garden Gnomes','','home_and_living.outdoor_and_garden.garden_decoration.outdoor_statues.garden_gnomes',2283,1);
INSERT INTO `ps_etsy_categories` VALUES (2285,2785,'Home & Living > Outdoor & Gardening > Garden Decoration > Wind Chimes','','home_and_living.outdoor_and_garden.garden_decoration.wind_chimes',2282,1);
INSERT INTO `ps_etsy_categories` VALUES (2286,2784,'Home & Living > Outdoor & Gardening > Garden Decoration > Yard Art','','home_and_living.outdoor_and_garden.garden_decoration.yard_art',2282,1);
INSERT INTO `ps_etsy_categories` VALUES (2287,1111,'Home & Living > Outdoor & Gardening > Garden Gloves & Aprons','','home_and_living.outdoor_and_garden.garden_gloves_and_aprons',2271,0);
INSERT INTO `ps_etsy_categories` VALUES (2288,1977,'Home & Living > Outdoor & Gardening > Garden Gloves & Aprons > Gardening Aprons','','home_and_living.outdoor_and_garden.garden_gloves_and_aprons.gardening_aprons',2287,1);
INSERT INTO `ps_etsy_categories` VALUES (2289,1976,'Home & Living > Outdoor & Gardening > Garden Gloves & Aprons > Gardening Gloves','','home_and_living.outdoor_and_garden.garden_gloves_and_aprons.gardening_gloves',2287,1);
INSERT INTO `ps_etsy_categories` VALUES (2290,1114,'Home & Living > Outdoor & Gardening > Grills & Accessories','','home_and_living.outdoor_and_garden.grills_and_accessories',2271,0);
INSERT INTO `ps_etsy_categories` VALUES (2291,1979,'Home & Living > Outdoor & Gardening > Grills & Accessories > Grill Tools','','home_and_living.outdoor_and_garden.grills_and_accessories.grill_tools',2290,1);
INSERT INTO `ps_etsy_categories` VALUES (2292,1978,'Home & Living > Outdoor & Gardening > Grills & Accessories > Grills','','home_and_living.outdoor_and_garden.grills_and_accessories.grills',2290,1);
INSERT INTO `ps_etsy_categories` VALUES (2293,1115,'Home & Living > Outdoor & Gardening > Hammocks & Swings','','home_and_living.outdoor_and_garden.hammocks_and_swings',2271,0);
INSERT INTO `ps_etsy_categories` VALUES (2294,1980,'Home & Living > Outdoor & Gardening > Hammocks & Swings > Hammocks','','home_and_living.outdoor_and_garden.hammocks_and_swings.hammocks',2293,1);
INSERT INTO `ps_etsy_categories` VALUES (2295,1981,'Home & Living > Outdoor & Gardening > Hammocks & Swings > Swings','','home_and_living.outdoor_and_garden.hammocks_and_swings.swings',2293,1);
INSERT INTO `ps_etsy_categories` VALUES (2296,1116,'Home & Living > Outdoor & Gardening > Mailboxes','','home_and_living.outdoor_and_garden.mailboxes',2271,1);
INSERT INTO `ps_etsy_categories` VALUES (2297,1117,'Home & Living > Outdoor & Gardening > Outdoor Lighting','','home_and_living.outdoor_and_garden.outdoor_lighting',2271,1);
INSERT INTO `ps_etsy_categories` VALUES (2298,1118,'Home & Living > Outdoor & Gardening > Patio Furniture','','home_and_living.outdoor_and_garden.patio_furniture',2271,0);
INSERT INTO `ps_etsy_categories` VALUES (2299,1984,'Home & Living > Outdoor & Gardening > Patio Furniture > Patio Chairs','','home_and_living.outdoor_and_garden.patio_furniture.patio_chairs',2298,1);
INSERT INTO `ps_etsy_categories` VALUES (2300,1982,'Home & Living > Outdoor & Gardening > Patio Furniture > Patio Sets','','home_and_living.outdoor_and_garden.patio_furniture.patio_sets',2298,1);
INSERT INTO `ps_etsy_categories` VALUES (2301,1983,'Home & Living > Outdoor & Gardening > Patio Furniture > Patio Tables','','home_and_living.outdoor_and_garden.patio_furniture.patio_tables',2298,1);
INSERT INTO `ps_etsy_categories` VALUES (2302,1985,'Home & Living > Outdoor & Gardening > Patio Furniture > Patio Umbrellas','','home_and_living.outdoor_and_garden.patio_furniture.patio_umbrellas',2298,1);
INSERT INTO `ps_etsy_categories` VALUES (2303,1130,'Home & Living > Outdoor & Gardening > Plant Stands','','home_and_living.outdoor_and_garden.plant_stands',2271,1);
INSERT INTO `ps_etsy_categories` VALUES (2304,1119,'Home & Living > Outdoor & Gardening > Planters & Pots','','home_and_living.outdoor_and_garden.planters_and_pots',2271,0);
INSERT INTO `ps_etsy_categories` VALUES (2305,6060,'Home & Living > Outdoor & Gardening > Planters & Pots > Indoor Planters','','home_and_living.outdoor_and_garden.planters_and_pots.indoor_planters',2304,1);
INSERT INTO `ps_etsy_categories` VALUES (2306,6061,'Home & Living > Outdoor & Gardening > Planters & Pots > Outdoor Planters','','home_and_living.outdoor_and_garden.planters_and_pots.outdoor_planters',2304,1);
INSERT INTO `ps_etsy_categories` VALUES (2307,1133,'Home & Living > Outdoor & Gardening > Watering & Hoses','','home_and_living.outdoor_and_garden.watering_and_hoses',2271,1);
INSERT INTO `ps_etsy_categories` VALUES (2308,1134,'Home & Living > Outdoor & Gardening > Wheels & Wheelbarrows','','home_and_living.outdoor_and_garden.wheels_and_wheelbarrows',2271,1);
INSERT INTO `ps_etsy_categories` VALUES (2309,1135,'Home & Living > Spirituality & Religion','','home_and_living.spirituality_and_religion',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (2310,1136,'Home & Living > Spirituality & Religion > Altars, Shrines & Tools','','home_and_living.spirituality_and_religion.altars_shrines_and_tools',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2311,1869,'Home & Living > Spirituality & Religion > Cemetery & Funeral','','home_and_living.spirituality_and_religion.cemetery_and_funeral',2309,0);
INSERT INTO `ps_etsy_categories` VALUES (2312,1870,'Home & Living > Spirituality & Religion > Cemetery & Funeral > Caskets & Urns','','home_and_living.spirituality_and_religion.cemetery_and_funeral.caskets_and_urns',2311,0);
INSERT INTO `ps_etsy_categories` VALUES (2313,1871,'Home & Living > Spirituality & Religion > Cemetery & Funeral > Caskets & Urns > Caskets','','home_and_living.spirituality_and_religion.cemetery_and_funeral.caskets_and_urns.caskets',2312,1);
INSERT INTO `ps_etsy_categories` VALUES (2314,1872,'Home & Living > Spirituality & Religion > Cemetery & Funeral > Caskets & Urns > Urns','','home_and_living.spirituality_and_religion.cemetery_and_funeral.caskets_and_urns.urns',2312,1);
INSERT INTO `ps_etsy_categories` VALUES (2315,1873,'Home & Living > Spirituality & Religion > Cemetery & Funeral > Grave Markers & Decoration','','home_and_living.spirituality_and_religion.cemetery_and_funeral.grave_markers_and_decoration',2311,1);
INSERT INTO `ps_etsy_categories` VALUES (2316,1137,'Home & Living > Spirituality & Religion > Ceremonial Pipes','','home_and_living.spirituality_and_religion.ceremonial_pipes',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2317,1138,'Home & Living > Spirituality & Religion > Divination Tools','','home_and_living.spirituality_and_religion.divination_tools',2309,0);
INSERT INTO `ps_etsy_categories` VALUES (2318,1964,'Home & Living > Spirituality & Religion > Divination Tools > Dowsing','','home_and_living.spirituality_and_religion.divination_tools.dowsing',2317,1);
INSERT INTO `ps_etsy_categories` VALUES (2319,1968,'Home & Living > Spirituality & Religion > Divination Tools > Fortune Cups & China','','home_and_living.spirituality_and_religion.divination_tools.fortune_cups_and_china',2317,1);
INSERT INTO `ps_etsy_categories` VALUES (2320,1971,'Home & Living > Spirituality & Religion > Divination Tools > I Ching','','home_and_living.spirituality_and_religion.divination_tools.i_ching',2317,1);
INSERT INTO `ps_etsy_categories` VALUES (2321,1969,'Home & Living > Spirituality & Religion > Divination Tools > Palmistry','','home_and_living.spirituality_and_religion.divination_tools.palmistry',2317,1);
INSERT INTO `ps_etsy_categories` VALUES (2322,1966,'Home & Living > Spirituality & Religion > Divination Tools > Runes','','home_and_living.spirituality_and_religion.divination_tools.runes',2317,1);
INSERT INTO `ps_etsy_categories` VALUES (2323,1967,'Home & Living > Spirituality & Religion > Divination Tools > Scrying','','home_and_living.spirituality_and_religion.divination_tools.scrying',2317,1);
INSERT INTO `ps_etsy_categories` VALUES (2324,1970,'Home & Living > Spirituality & Religion > Divination Tools > Talking Boards','','home_and_living.spirituality_and_religion.divination_tools.talking_boards',2317,1);
INSERT INTO `ps_etsy_categories` VALUES (2325,1965,'Home & Living > Spirituality & Religion > Divination Tools > Tarot','','home_and_living.spirituality_and_religion.divination_tools.tarot',2317,1);
INSERT INTO `ps_etsy_categories` VALUES (2326,1139,'Home & Living > Spirituality & Religion > Meditation','','home_and_living.spirituality_and_religion.meditation',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2327,1140,'Home & Living > Spirituality & Religion > Natural Curios','','home_and_living.spirituality_and_religion.natural_curios',2309,0);
INSERT INTO `ps_etsy_categories` VALUES (2328,1960,'Home & Living > Spirituality & Religion > Natural Curios > Herbs & Roots','','home_and_living.spirituality_and_religion.natural_curios.herbs_and_roots',2327,1);
INSERT INTO `ps_etsy_categories` VALUES (2329,1959,'Home & Living > Spirituality & Religion > Natural Curios > Mineral','','home_and_living.spirituality_and_religion.natural_curios.mineral',2327,1);
INSERT INTO `ps_etsy_categories` VALUES (2330,1958,'Home & Living > Spirituality & Religion > Natural Curios > Zoological','','home_and_living.spirituality_and_religion.natural_curios.zoological',2327,1);
INSERT INTO `ps_etsy_categories` VALUES (2331,1153,'Home & Living > Spirituality & Religion > Prayer Beads & Charms','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms',2309,0);
INSERT INTO `ps_etsy_categories` VALUES (2332,1154,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Bindis','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.bindis',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2333,2925,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Chaplets','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.chaplets',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2334,1155,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Lucky Charms & Amulets','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.lucky_charms_and_amulets',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2335,1158,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Metaphysical Crystals','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.religious_crystals',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2336,1156,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Prayer Beads','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.prayer_beads',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2337,1157,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Prayer Cards','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.prayer_cards',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2338,1159,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Rosaries','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.rosaries',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2339,1160,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Saints Medals','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.saints_medals',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2340,2926,'Home & Living > Spirituality & Religion > Prayer Beads & Charms > Scapulars','','home_and_living.spirituality_and_religion.religious_jewelry_and_charms.scapulars',2331,1);
INSERT INTO `ps_etsy_categories` VALUES (2341,1141,'Home & Living > Spirituality & Religion > Prayer Mats & Rugs','','home_and_living.spirituality_and_religion.prayer_mats_and_rugs',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2342,6053,'Home & Living > Spirituality & Religion > Psychic Readings','','home_and_living.spirituality_and_religion.psychic_readings',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2343,1142,'Home & Living > Spirituality & Religion > Reiki & Chakras','','home_and_living.spirituality_and_religion.reiki_and_chakras',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2344,1143,'Home & Living > Spirituality & Religion > Religious Candles & Incense','','home_and_living.spirituality_and_religion.religious_candles_and_incense',2309,0);
INSERT INTO `ps_etsy_categories` VALUES (2345,1144,'Home & Living > Spirituality & Religion > Religious Candles & Incense > Incense','','home_and_living.spirituality_and_religion.religious_candles_and_incense.incense',2344,1);
INSERT INTO `ps_etsy_categories` VALUES (2346,1145,'Home & Living > Spirituality & Religion > Religious Candles & Incense > Menorahs','','home_and_living.spirituality_and_religion.religious_candles_and_incense.menorahs',2344,1);
INSERT INTO `ps_etsy_categories` VALUES (2347,1146,'Home & Living > Spirituality & Religion > Religious Candles & Incense > Novena Candles','','home_and_living.spirituality_and_religion.religious_candles_and_incense.novena_candles',2344,1);
INSERT INTO `ps_etsy_categories` VALUES (2348,1147,'Home & Living > Spirituality & Religion > Religious Candles & Incense > Ritual Candles','','home_and_living.spirituality_and_religion.religious_candles_and_incense.ritual_candles',2344,1);
INSERT INTO `ps_etsy_categories` VALUES (2349,1148,'Home & Living > Spirituality & Religion > Religious Candles & Incense > Ritual Oil & Resins','','home_and_living.spirituality_and_religion.religious_candles_and_incense.ritual_oil_and_resins',2344,1);
INSERT INTO `ps_etsy_categories` VALUES (2350,1149,'Home & Living > Spirituality & Religion > Religious Candles & Incense > Spiritual Colognes','','home_and_living.spirituality_and_religion.religious_candles_and_incense.spiritual_colognes',2344,1);
INSERT INTO `ps_etsy_categories` VALUES (2351,1150,'Home & Living > Spirituality & Religion > Religious Clothing','','home_and_living.spirituality_and_religion.religious_clothing',2309,0);
INSERT INTO `ps_etsy_categories` VALUES (2352,1962,'Home & Living > Spirituality & Religion > Religious Clothing > Hijabs & Head Coverings','','home_and_living.spirituality_and_religion.religious_clothing.hijabs_and_head_coverings',2351,1);
INSERT INTO `ps_etsy_categories` VALUES (2353,1963,'Home & Living > Spirituality & Religion > Religious Clothing > Prayer Shawls','','home_and_living.spirituality_and_religion.religious_clothing.prayer_shawls',2351,1);
INSERT INTO `ps_etsy_categories` VALUES (2354,2849,'Home & Living > Spirituality & Religion > Religious Clothing > Vestments','','home_and_living.spirituality_and_religion.religious_clothing.vestments',2351,1);
INSERT INTO `ps_etsy_categories` VALUES (2355,1961,'Home & Living > Spirituality & Religion > Religious Clothing > Yarmulkes','','home_and_living.spirituality_and_religion.religious_clothing.yarmulkes',2351,1);
INSERT INTO `ps_etsy_categories` VALUES (2356,1151,'Home & Living > Spirituality & Religion > Religious Home & Décor','','home_and_living.spirituality_and_religion.religious_home_and_decor',2309,0);
INSERT INTO `ps_etsy_categories` VALUES (2357,1152,'Home & Living > Spirituality & Religion > Religious Home & Décor > Crucifixes & Crosses','','home_and_living.spirituality_and_religion.religious_home_and_decor.crucifixes_and_crosses',2356,1);
INSERT INTO `ps_etsy_categories` VALUES (2358,6099,'Home & Living > Spirituality & Religion > Religious Home & Décor > Nativity Sets','','home_and_living.spirituality_and_religion.religious_home_and_decor.nativity_sets',2356,1);
INSERT INTO `ps_etsy_categories` VALUES (2359,1161,'Home & Living > Spirituality & Religion > Religious Music','','home_and_living.spirituality_and_religion.religious_music',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2360,1163,'Home & Living > Spirituality & Religion > Religious Statuary','','home_and_living.spirituality_and_religion.religious_statuary',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2361,6054,'Home & Living > Spirituality & Religion > Tarot Readings & Divinations','','home_and_living.spirituality_and_religion.tarot_readings_and_divinations',2309,1);
INSERT INTO `ps_etsy_categories` VALUES (2362,1165,'Home & Living > Storage & Organization','','home_and_living.storage_and_organization',1889,0);
INSERT INTO `ps_etsy_categories` VALUES (2363,1166,'Home & Living > Storage & Organization > Baskets','','home_and_living.storage_and_organization.baskets',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2364,1167,'Home & Living > Storage & Organization > Boxes & Bins','','home_and_living.storage_and_organization.boxes_and_bins',2362,0);
INSERT INTO `ps_etsy_categories` VALUES (2365,1891,'Home & Living > Storage & Organization > Boxes & Bins > Remote Control Caddies','','home_and_living.storage_and_organization.boxes_and_bins.remote_control_caddies',2364,1);
INSERT INTO `ps_etsy_categories` VALUES (2366,2856,'Home & Living > Storage & Organization > Boxes & Bins > Tins','','home_and_living.storage_and_organization.boxes_and_bins.tins',2364,1);
INSERT INTO `ps_etsy_categories` VALUES (2367,1168,'Home & Living > Storage & Organization > Cabinets & Food Storage','','home_and_living.storage_and_organization.cabinets_and_food_storage',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2368,11502,'Home & Living > Storage & Organization > Diaper Caddies','','home_and_living.storage_and_organization.diaper_caddies',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2369,11488,'Home & Living > Storage & Organization > Diaper Stackers','','home_and_living.storage_and_organization.diaper_stackers',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2370,1169,'Home & Living > Storage & Organization > Garage Storage','','home_and_living.storage_and_organization.garage_storage',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2371,1860,'Home & Living > Storage & Organization > Hangers & Clothing Storage','','home_and_living.storage_and_organization.hangers_and_clothing_storage',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2372,1170,'Home & Living > Storage & Organization > Hooks & Fixtures','','home_and_living.storage_and_organization.hooks_and_fixtures',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2373,1171,'Home & Living > Storage & Organization > Ladders & Stepstools','','home_and_living.storage_and_organization.ladders_and_stepstools',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2374,1172,'Home & Living > Storage & Organization > Laundry Bags & Hampers','','home_and_living.storage_and_organization.laundry_bags_and_hampers',2362,0);
INSERT INTO `ps_etsy_categories` VALUES (2375,2231,'Home & Living > Storage & Organization > Laundry Bags & Hampers > Hampers','','home_and_living.storage_and_organization.laundry_bags_and_hampers.hampers',2374,1);
INSERT INTO `ps_etsy_categories` VALUES (2376,2230,'Home & Living > Storage & Organization > Laundry Bags & Hampers > Laundry Bags','','home_and_living.storage_and_organization.laundry_bags_and_hampers.laundry_bags',2374,1);
INSERT INTO `ps_etsy_categories` VALUES (2377,1173,'Home & Living > Storage & Organization > Outdoor Storage','','home_and_living.storage_and_organization.outdoor_storage',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2378,1174,'Home & Living > Storage & Organization > Recycling & Trash','','home_and_living.storage_and_organization.recycling_and_trash',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2379,1175,'Home & Living > Storage & Organization > Shelving','','home_and_living.storage_and_organization.shelving',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2380,1176,'Home & Living > Storage & Organization > Shoe Storage','','home_and_living.storage_and_organization.shoe_storage',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2381,1177,'Home & Living > Storage & Organization > Toy Storage','','home_and_living.storage_and_organization.toy_storage',2362,1);
INSERT INTO `ps_etsy_categories` VALUES (2382,1179,'Jewelry','','jewelry',0,0);
INSERT INTO `ps_etsy_categories` VALUES (2383,1181,'Jewelry > Body Jewelry','','jewelry.body_jewelry',2382,0);
INSERT INTO `ps_etsy_categories` VALUES (2384,1180,'Jewelry > Body Jewelry > Anklets','','jewelry.body_jewelry.anklets',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2385,1182,'Jewelry > Body Jewelry > Arm Bands','','jewelry.body_jewelry.arm_bands',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2386,1183,'Jewelry > Body Jewelry > Barbells','','jewelry.body_jewelry.barbells',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2387,1184,'Jewelry > Body Jewelry > Belly Chains','','jewelry.body_jewelry.belly_chains',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2388,1185,'Jewelry > Body Jewelry > Belly Rings','','jewelry.body_jewelry.belly_rings',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2389,1186,'Jewelry > Body Jewelry > Hair Jewelry','','jewelry.body_jewelry.hair_jewelry',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2390,1187,'Jewelry > Body Jewelry > Lip Rings','','jewelry.body_jewelry.lip_rings',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2391,1188,'Jewelry > Body Jewelry > Nipple Jewelry','','jewelry.body_jewelry.nipple_jewelry',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2392,1189,'Jewelry > Body Jewelry > Nose Rings & Studs','','jewelry.body_jewelry.nose_rings_and_studs',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2393,1190,'Jewelry > Body Jewelry > Pinchers & Spirals','','jewelry.body_jewelry.pinchers_and_spirals',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2394,1191,'Jewelry > Body Jewelry > Shoulder Jewelry','','jewelry.body_jewelry.shoulder_jewelry',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2395,1192,'Jewelry > Body Jewelry > Toe Rings','','jewelry.body_jewelry.toe_rings',2383,1);
INSERT INTO `ps_etsy_categories` VALUES (2396,1193,'Jewelry > Bracelets','','jewelry.bracelets',2382,0);
INSERT INTO `ps_etsy_categories` VALUES (2397,1194,'Jewelry > Bracelets > Bangles','','jewelry.bracelets.bangles',2396,1);
INSERT INTO `ps_etsy_categories` VALUES (2398,1195,'Jewelry > Bracelets > Beaded Bracelets','','jewelry.bracelets.beaded_bracelets',2396,1);
INSERT INTO `ps_etsy_categories` VALUES (2399,1196,'Jewelry > Bracelets > Chain & Link Bracelets','','jewelry.bracelets.chain_and_link_bracelets',2396,1);
INSERT INTO `ps_etsy_categories` VALUES (2400,1197,'Jewelry > Bracelets > Charm Bracelets','','jewelry.bracelets.charm_bracelets',2396,1);
INSERT INTO `ps_etsy_categories` VALUES (2401,1198,'Jewelry > Bracelets > Cuff Bracelets','','jewelry.bracelets.cuff_bracelets',2396,1);
INSERT INTO `ps_etsy_categories` VALUES (2402,1199,'Jewelry > Bracelets > ID & Medical Bracelets','','jewelry.bracelets.id_and_medical_bracelets',2396,1);
INSERT INTO `ps_etsy_categories` VALUES (2403,1200,'Jewelry > Bracelets > Woven & Braided Bracelets','','jewelry.bracelets.woven_and_braided_bracelets',2396,0);
INSERT INTO `ps_etsy_categories` VALUES (2404,2929,'Jewelry > Bracelets > Woven & Braided Bracelets > Friendship Bracelets','','jewelry.bracelets.woven_and_braided_bracelets.friendship_bracelets',2403,1);
INSERT INTO `ps_etsy_categories` VALUES (2405,10840,'Jewelry > Brooches, Pins & Clips','','jewelry.brooches_pins_and_clips',2382,0);
INSERT INTO `ps_etsy_categories` VALUES (2406,1201,'Jewelry > Brooches, Pins & Clips > Brooches','','jewelry.brooches_pins_and_clips.brooches',2405,1);
INSERT INTO `ps_etsy_categories` VALUES (2407,1202,'Jewelry > Brooches, Pins & Clips > Clothing & Shoe Clips','','jewelry.brooches_pins_and_clips.clothing_and_shoe_clips',2405,0);
INSERT INTO `ps_etsy_categories` VALUES (2408,2146,'Jewelry > Brooches, Pins & Clips > Clothing & Shoe Clips > Dress Clips','','jewelry.brooches_pins_and_clips.clothing_and_shoe_clips.dress_clips',2407,1);
INSERT INTO `ps_etsy_categories` VALUES (2409,2147,'Jewelry > Brooches, Pins & Clips > Clothing & Shoe Clips > Shoe Clips','','jewelry.brooches_pins_and_clips.clothing_and_shoe_clips.shoe_clips',2407,1);
INSERT INTO `ps_etsy_categories` VALUES (2410,1248,'Jewelry > Brooches, Pins & Clips > Clothing & Shoe Clips > Sweater Clips','','jewelry.brooches_pins_and_clips.clothing_and_shoe_clips.sweater_clips',2407,1);
INSERT INTO `ps_etsy_categories` VALUES (2411,10841,'Jewelry > Brooches, Pins & Clips > Pins & Badges','','jewelry.brooches_pins_and_clips.pins_and_badges',2405,1);
INSERT INTO `ps_etsy_categories` VALUES (2412,10842,'Jewelry > Cremation & Memorial Jewelry','','jewelry.cremation_and_memorial_jewelry',2382,1);
INSERT INTO `ps_etsy_categories` VALUES (2413,1203,'Jewelry > Earrings','','jewelry.earrings',2382,0);
INSERT INTO `ps_etsy_categories` VALUES (2414,1204,'Jewelry > Earrings > Chandelier Earrings','','jewelry.earrings.chandelier_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2415,1205,'Jewelry > Earrings > Clip-On Earrings','','jewelry.earrings.clip_on_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2416,1206,'Jewelry > Earrings > Cluster Earrings','','jewelry.earrings.cluster_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2417,1207,'Jewelry > Earrings > Cuff & Wrap Earrings','','jewelry.earrings.cuff_and_wrap_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2418,1208,'Jewelry > Earrings > Dangle & Drop Earrings','','jewelry.earrings.dangle_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2419,2900,'Jewelry > Earrings > Ear Jackets & Climbers','','jewelry.earrings.ear_jackets_and_climbers',2413,0);
INSERT INTO `ps_etsy_categories` VALUES (2420,2901,'Jewelry > Earrings > Ear Jackets & Climbers > Ear Climbers','','jewelry.earrings.ear_jackets_and_climbers.ear_climbers',2419,1);
INSERT INTO `ps_etsy_categories` VALUES (2421,2211,'Jewelry > Earrings > Ear Jackets & Climbers > Ear Jackets','','jewelry.earrings.ear_jackets_and_climbers.ear_jackets',2419,1);
INSERT INTO `ps_etsy_categories` VALUES (2422,1210,'Jewelry > Earrings > Ear Weights','','jewelry.earrings.ear_weights',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2423,1211,'Jewelry > Earrings > Gauge & Plug Earrings','','jewelry.earrings.gauge_and_plug_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2424,1212,'Jewelry > Earrings > Hoop Earrings','','jewelry.earrings.hoop_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2425,1213,'Jewelry > Earrings > Screw Back Earrings','','jewelry.earrings.screw_back_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2426,1214,'Jewelry > Earrings > Stud Earrings','','jewelry.earrings.stud_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2427,1215,'Jewelry > Earrings > Threader Earrings','','jewelry.earrings.threader_earrings',2413,1);
INSERT INTO `ps_etsy_categories` VALUES (2428,2115,'Jewelry > Jewelry Sets','','jewelry.jewelry_sets',2382,1);
INSERT INTO `ps_etsy_categories` VALUES (2429,1216,'Jewelry > Jewelry Storage','','jewelry.jewelry_storage',2382,0);
INSERT INTO `ps_etsy_categories` VALUES (2430,6102,'Jewelry > Jewelry Storage > Jewelry Boxes','','jewelry.jewelry_storage.jewelry_boxes',2429,1);
INSERT INTO `ps_etsy_categories` VALUES (2431,6104,'Jewelry > Jewelry Storage > Ring Dishes','','jewelry.jewelry_storage.ring_dishes',2429,1);
INSERT INTO `ps_etsy_categories` VALUES (2432,6103,'Jewelry > Jewelry Storage > Ring Trees','','jewelry.jewelry_storage.ring_trees',2429,1);
INSERT INTO `ps_etsy_categories` VALUES (2433,1217,'Jewelry > Necklaces','','jewelry.necklaces',2382,0);
INSERT INTO `ps_etsy_categories` VALUES (2434,1218,'Jewelry > Necklaces > Beaded Necklaces','','jewelry.necklaces.beaded_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2435,1219,'Jewelry > Necklaces > Bib Necklaces','','jewelry.necklaces.bib_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2436,1220,'Jewelry > Necklaces > Cameo Necklaces','','jewelry.necklaces.cameo_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2437,1221,'Jewelry > Necklaces > Chains','','jewelry.necklaces.chains',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2438,1222,'Jewelry > Necklaces > Charm Necklaces','','jewelry.necklaces.charm_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2439,1223,'Jewelry > Necklaces > Chokers','','jewelry.necklaces.chokers',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2440,1224,'Jewelry > Necklaces > Crystal Necklaces','','jewelry.necklaces.crystal_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2441,1225,'Jewelry > Necklaces > Lariat & Y Necklaces','','jewelry.necklaces.lariat_and_y_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2442,1226,'Jewelry > Necklaces > Lockets','','jewelry.necklaces.lockets',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2443,1227,'Jewelry > Necklaces > Monogram & Name Necklaces','','jewelry.necklaces.monogram_and_name_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2444,1228,'Jewelry > Necklaces > Multi-Strand Necklaces','','jewelry.necklaces.multi_strand_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2445,1229,'Jewelry > Necklaces > Pendants','','jewelry.necklaces.pendants',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2446,1230,'Jewelry > Necklaces > Tassel Necklaces','','jewelry.necklaces.tassel_necklaces',2433,1);
INSERT INTO `ps_etsy_categories` VALUES (2447,1231,'Jewelry > Rings','','jewelry.rings',2382,0);
INSERT INTO `ps_etsy_categories` VALUES (2448,1232,'Jewelry > Rings > Bands','','jewelry.rings.bands',2447,1);
INSERT INTO `ps_etsy_categories` VALUES (2449,1234,'Jewelry > Rings > Midi Rings','','jewelry.rings.midi_rings',2447,1);
INSERT INTO `ps_etsy_categories` VALUES (2450,1236,'Jewelry > Rings > Multi-Stone Rings','','jewelry.rings.multistone_rings',2447,1);
INSERT INTO `ps_etsy_categories` VALUES (2451,1237,'Jewelry > Rings > Ring Guards & Spacers','','jewelry.rings.ring_guards_and_spacers',2447,1);
INSERT INTO `ps_etsy_categories` VALUES (2452,2152,'Jewelry > Rings > Signet Rings','','jewelry.rings.signet_rings',2447,0);
INSERT INTO `ps_etsy_categories` VALUES (2453,1233,'Jewelry > Rings > Signet Rings > Fraternal & Class Rings','','jewelry.rings.signet_rings.fraternal_and_class_rings',2452,1);
INSERT INTO `ps_etsy_categories` VALUES (2454,1238,'Jewelry > Rings > Solitaire Rings','','jewelry.rings.solitaire_rings',2447,1);
INSERT INTO `ps_etsy_categories` VALUES (2455,1239,'Jewelry > Rings > Stackable Rings','','jewelry.rings.stackable_rings',2447,1);
INSERT INTO `ps_etsy_categories` VALUES (2456,1240,'Jewelry > Rings > Statement Rings','','jewelry.rings.statement_rings',2447,1);
INSERT INTO `ps_etsy_categories` VALUES (2457,1235,'Jewelry > Rings > Triplet & Double Rings','','jewelry.rings.multifinger_rings',2447,1);
INSERT INTO `ps_etsy_categories` VALUES (2458,1241,'Jewelry > Rings > Wedding & Engagement','','jewelry.rings.wedding_and_engagement',2447,0);
INSERT INTO `ps_etsy_categories` VALUES (2459,1242,'Jewelry > Rings > Wedding & Engagement > Anniversary Rings','','jewelry.rings.wedding_and_engagement.anniversary_rings',2458,1);
INSERT INTO `ps_etsy_categories` VALUES (2460,1243,'Jewelry > Rings > Wedding & Engagement > Bridal Sets','','jewelry.rings.wedding_and_engagement.bridal_sets',2458,1);
INSERT INTO `ps_etsy_categories` VALUES (2461,1244,'Jewelry > Rings > Wedding & Engagement > Claddagh Rings','','jewelry.rings.wedding_and_engagement.claddagh_rings',2458,1);
INSERT INTO `ps_etsy_categories` VALUES (2462,1245,'Jewelry > Rings > Wedding & Engagement > Engagement Rings','','jewelry.rings.wedding_and_engagement.engagement_rings',2458,1);
INSERT INTO `ps_etsy_categories` VALUES (2463,1246,'Jewelry > Rings > Wedding & Engagement > Promise Rings','','jewelry.rings.wedding_and_engagement.promise_rings',2458,1);
INSERT INTO `ps_etsy_categories` VALUES (2464,1247,'Jewelry > Rings > Wedding & Engagement > Wedding Bands','','jewelry.rings.wedding_and_engagement.wedding_bands',2458,1);
INSERT INTO `ps_etsy_categories` VALUES (2465,1249,'Jewelry > Watches','','jewelry.watches',2382,0);
INSERT INTO `ps_etsy_categories` VALUES (2466,2830,'Jewelry > Watches > Pocket Watches','','jewelry.watches.pocket_watches',2465,1);
INSERT INTO `ps_etsy_categories` VALUES (2467,6077,'Jewelry > Watches > Watch Bands & Straps','','jewelry.watches.watch_bands_and_straps',2465,1);
INSERT INTO `ps_etsy_categories` VALUES (2468,2917,'Jewelry > Watches > Watch Necklaces','','jewelry.watches.watch_necklaces',2465,1);
INSERT INTO `ps_etsy_categories` VALUES (2469,2918,'Jewelry > Watches > Watch Rings','','jewelry.watches.watch_rings',2465,1);
INSERT INTO `ps_etsy_categories` VALUES (2470,2831,'Jewelry > Watches > Wrist Watches','','jewelry.watches.wrist_watches',2465,0);
INSERT INTO `ps_etsy_categories` VALUES (2471,6074,'Jewelry > Watches > Wrist Watches > Men\'s Wrist Watches','','jewelry.watches.wrist_watches.mens_watches',2470,1);
INSERT INTO `ps_etsy_categories` VALUES (2472,6076,'Jewelry > Watches > Wrist Watches > Unisex Wrist Watches','','jewelry.watches.wrist_watches.unisex_wrist_watches',2470,1);
INSERT INTO `ps_etsy_categories` VALUES (2473,6075,'Jewelry > Watches > Wrist Watches > Women\'s Wrist Watches','','jewelry.watches.wrist_watches.womens_wrist_watches',2470,1);
INSERT INTO `ps_etsy_categories` VALUES (2474,6110,'Jewelry > Wearable Tech Jewelry','','jewelry.wearable_tech_jewelry',2382,1);
INSERT INTO `ps_etsy_categories` VALUES (2475,1250,'Paper & Party Supplies','','paper_and_party_supplies',0,0);
INSERT INTO `ps_etsy_categories` VALUES (2476,1251,'Paper & Party Supplies > Paper','','paper_and_party_supplies.paper',2475,0);
INSERT INTO `ps_etsy_categories` VALUES (2477,354,'Paper & Party Supplies > Paper > Calendars & Planners','','paper_and_party_supplies.paper.calendars_and_planners',2476,0);
INSERT INTO `ps_etsy_categories` VALUES (2478,6098,'Paper & Party Supplies > Paper > Calendars & Planners > Advent Calendars','','paper_and_party_supplies.paper.calendars_and_planners.advent_calendars',2477,1);
INSERT INTO `ps_etsy_categories` VALUES (2479,1261,'Paper & Party Supplies > Paper > Greeting Cards','','paper_and_party_supplies.paper.greeting_cards',2476,0);
INSERT INTO `ps_etsy_categories` VALUES (2480,1262,'Paper & Party Supplies > Paper > Greeting Cards > Anniversary Cards','','paper_and_party_supplies.paper.greeting_cards.anniversary_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2481,1263,'Paper & Party Supplies > Paper > Greeting Cards > Baby & Expecting Cards','','paper_and_party_supplies.paper.greeting_cards.baby_and_expecting_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2482,1264,'Paper & Party Supplies > Paper > Greeting Cards > Birthday Cards','','paper_and_party_supplies.paper.greeting_cards.birthday_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2483,1265,'Paper & Party Supplies > Paper > Greeting Cards > Blank Cards','','paper_and_party_supplies.paper.greeting_cards.blank_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2484,1266,'Paper & Party Supplies > Paper > Greeting Cards > Congratulations Cards','','paper_and_party_supplies.paper.greeting_cards.congratulations_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2485,1267,'Paper & Party Supplies > Paper > Greeting Cards > Encouragement Cards','','paper_and_party_supplies.paper.greeting_cards.encouragement_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2486,1268,'Paper & Party Supplies > Paper > Greeting Cards > Friendship Cards','','paper_and_party_supplies.paper.greeting_cards.friendship_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2487,1269,'Paper & Party Supplies > Paper > Greeting Cards > Get Well Cards','','paper_and_party_supplies.paper.greeting_cards.get_well_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2488,1270,'Paper & Party Supplies > Paper > Greeting Cards > Good Luck Cards','','paper_and_party_supplies.paper.greeting_cards.good_luck_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2489,1271,'Paper & Party Supplies > Paper > Greeting Cards > Graduation & School Cards','','paper_and_party_supplies.paper.greeting_cards.graduation_and_school_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2490,1272,'Paper & Party Supplies > Paper > Greeting Cards > Holiday & Seasonal Cards','','paper_and_party_supplies.paper.greeting_cards.holiday_and_seasonal_cards',2479,0);
INSERT INTO `ps_etsy_categories` VALUES (2491,1273,'Paper & Party Supplies > Paper > Greeting Cards > Holiday & Seasonal Cards > Christmas Cards','','paper_and_party_supplies.paper.greeting_cards.holiday_and_seasonal_cards.christmas_cards',2490,1);
INSERT INTO `ps_etsy_categories` VALUES (2492,1274,'Paper & Party Supplies > Paper > Greeting Cards > Holiday & Seasonal Cards > Easter Cards','','paper_and_party_supplies.paper.greeting_cards.holiday_and_seasonal_cards.easter_cards',2490,1);
INSERT INTO `ps_etsy_categories` VALUES (2493,1275,'Paper & Party Supplies > Paper > Greeting Cards > Holiday & Seasonal Cards > Hanukkah Cards','','paper_and_party_supplies.paper.greeting_cards.holiday_and_seasonal_cards.hanukkah_cards',2490,1);
INSERT INTO `ps_etsy_categories` VALUES (2494,1276,'Paper & Party Supplies > Paper > Greeting Cards > Holiday & Seasonal Cards > Patriotism & Independence Cards','','paper_and_party_supplies.paper.greeting_cards.holiday_and_seasonal_cards.patriotism_and_independence_cards',2490,1);
INSERT INTO `ps_etsy_categories` VALUES (2495,1277,'Paper & Party Supplies > Paper > Greeting Cards > Holiday & Seasonal Cards > Religious Events Cards','','paper_and_party_supplies.paper.greeting_cards.holiday_and_seasonal_cards.religious_events_cards',2490,1);
INSERT INTO `ps_etsy_categories` VALUES (2496,1278,'Paper & Party Supplies > Paper > Greeting Cards > Holiday & Seasonal Cards > Thanksgiving Cards','','paper_and_party_supplies.paper.greeting_cards.holiday_and_seasonal_cards.thanksgiving_cards',2490,1);
INSERT INTO `ps_etsy_categories` VALUES (2497,1279,'Paper & Party Supplies > Paper > Greeting Cards > Holiday & Seasonal Cards > Valentines Cards','','paper_and_party_supplies.paper.greeting_cards.holiday_and_seasonal_cards.valentines_cards',2490,1);
INSERT INTO `ps_etsy_categories` VALUES (2498,1280,'Paper & Party Supplies > Paper > Greeting Cards > Just Because Cards','','paper_and_party_supplies.paper.greeting_cards.just_because_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2499,1281,'Paper & Party Supplies > Paper > Greeting Cards > Love Cards','','paper_and_party_supplies.paper.greeting_cards.love_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2500,1282,'Paper & Party Supplies > Paper > Greeting Cards > Miss You Cards','','paper_and_party_supplies.paper.greeting_cards.miss_you_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2501,1283,'Paper & Party Supplies > Paper > Greeting Cards > Moving Cards','','paper_and_party_supplies.paper.greeting_cards.moving_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2502,1284,'Paper & Party Supplies > Paper > Greeting Cards > Sorry Cards','','paper_and_party_supplies.paper.greeting_cards.sorry_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2503,1285,'Paper & Party Supplies > Paper > Greeting Cards > Sympathy Cards','','paper_and_party_supplies.paper.greeting_cards.sympathy_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2504,1286,'Paper & Party Supplies > Paper > Greeting Cards > Thank You Cards','','paper_and_party_supplies.paper.greeting_cards.thank_you_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2505,1287,'Paper & Party Supplies > Paper > Greeting Cards > Thinking Of You Cards','','paper_and_party_supplies.paper.greeting_cards.thinking_of_you_cards',2479,1);
INSERT INTO `ps_etsy_categories` VALUES (2506,1288,'Paper & Party Supplies > Paper > Greeting Cards > Wedding & Engagement Cards','','paper_and_party_supplies.paper.greeting_cards.wedding_and_engagement_cards',2479,0);
INSERT INTO `ps_etsy_categories` VALUES (2507,2178,'Paper & Party Supplies > Paper > Greeting Cards > Wedding & Engagement Cards > Engagement Cards','','paper_and_party_supplies.paper.greeting_cards.wedding_and_engagement_cards.engagement_cards',2506,1);
INSERT INTO `ps_etsy_categories` VALUES (2508,2177,'Paper & Party Supplies > Paper > Greeting Cards > Wedding & Engagement Cards > Wedding Cards','','paper_and_party_supplies.paper.greeting_cards.wedding_and_engagement_cards.wedding_cards',2506,1);
INSERT INTO `ps_etsy_categories` VALUES (2509,2179,'Paper & Party Supplies > Paper > Greeting Cards > Wedding & Engagement Cards > Wedding Shower Cards','','paper_and_party_supplies.paper.greeting_cards.wedding_and_engagement_cards.wedding_shower_cards',2506,1);
INSERT INTO `ps_etsy_categories` VALUES (2510,1289,'Paper & Party Supplies > Paper > Invitations & Announcements','','paper_and_party_supplies.paper.invitations_and_announcements',2476,0);
INSERT INTO `ps_etsy_categories` VALUES (2511,1290,'Paper & Party Supplies > Paper > Invitations & Announcements > Announcements','','paper_and_party_supplies.paper.invitations_and_announcements.announcements',2510,1);
INSERT INTO `ps_etsy_categories` VALUES (2512,1291,'Paper & Party Supplies > Paper > Invitations & Announcements > Invitation Kits','','paper_and_party_supplies.paper.invitations_and_announcements.invitation_kits',2510,1);
INSERT INTO `ps_etsy_categories` VALUES (2513,1292,'Paper & Party Supplies > Paper > Invitations & Announcements > Invitations','','paper_and_party_supplies.paper.invitations_and_announcements.invitations',2510,1);
INSERT INTO `ps_etsy_categories` VALUES (2514,1293,'Paper & Party Supplies > Paper > Invitations & Announcements > Save The Dates','','paper_and_party_supplies.paper.invitations_and_announcements.save_the_dates',2510,1);
INSERT INTO `ps_etsy_categories` VALUES (2515,1294,'Paper & Party Supplies > Paper > Invitations & Announcements > Templates','','paper_and_party_supplies.paper.invitations_and_announcements.templates',2510,1);
INSERT INTO `ps_etsy_categories` VALUES (2516,1295,'Paper & Party Supplies > Paper > Origami','','paper_and_party_supplies.paper.origami',2476,0);
INSERT INTO `ps_etsy_categories` VALUES (2517,1296,'Paper & Party Supplies > Paper > Origami > Finished Origami','','paper_and_party_supplies.paper.origami.finished_origami',2516,1);
INSERT INTO `ps_etsy_categories` VALUES (2518,1299,'Paper & Party Supplies > Paper > Origami > Paper Flowers','','paper_and_party_supplies.paper.origami.paper_flowers',2516,1);
INSERT INTO `ps_etsy_categories` VALUES (2519,1303,'Paper & Party Supplies > Paper > Stationery','','paper_and_party_supplies.paper.stationery',2476,0);
INSERT INTO `ps_etsy_categories` VALUES (2520,1304,'Paper & Party Supplies > Paper > Stationery > Business & Calling Cards','','paper_and_party_supplies.paper.stationery.business_and_calling_cards',2519,1);
INSERT INTO `ps_etsy_categories` VALUES (2521,1305,'Paper & Party Supplies > Paper > Stationery > Design & Templates','','paper_and_party_supplies.paper.stationery.design_and_templates',2519,0);
INSERT INTO `ps_etsy_categories` VALUES (2522,1875,'Paper & Party Supplies > Paper > Stationery > Design & Templates > Graphic Design','','paper_and_party_supplies.paper.stationery.design_and_templates.graphic_design',2521,0);
INSERT INTO `ps_etsy_categories` VALUES (2523,1878,'Paper & Party Supplies > Paper > Stationery > Design & Templates > Graphic Design > Letterhead Designs','','paper_and_party_supplies.paper.stationery.design_and_templates.graphic_design.letterhead_designs',2522,1);
INSERT INTO `ps_etsy_categories` VALUES (2524,1877,'Paper & Party Supplies > Paper > Stationery > Design & Templates > Graphic Design > Logos & Branding','','paper_and_party_supplies.paper.stationery.design_and_templates.graphic_design.logos_and_branding',2522,1);
INSERT INTO `ps_etsy_categories` VALUES (2525,769,'Paper & Party Supplies > Paper > Stationery > Design & Templates > Graphic Design > Store Graphics','','paper_and_party_supplies.paper.stationery.design_and_templates.graphic_design.store_graphics',2522,1);
INSERT INTO `ps_etsy_categories` VALUES (2526,1874,'Paper & Party Supplies > Paper > Stationery > Design & Templates > Templates','','paper_and_party_supplies.paper.stationery.design_and_templates.templates',2521,0);
INSERT INTO `ps_etsy_categories` VALUES (2527,1876,'Paper & Party Supplies > Paper > Stationery > Design & Templates > Templates > Résumé Templates','','paper_and_party_supplies.paper.stationery.design_and_templates.templates.resume_templates',2526,1);
INSERT INTO `ps_etsy_categories` VALUES (2528,2818,'Paper & Party Supplies > Paper > Stationery > Design & Templates > Templates > Website Templates','','paper_and_party_supplies.paper.stationery.design_and_templates.templates.website_templates',2526,1);
INSERT INTO `ps_etsy_categories` VALUES (2529,1309,'Paper & Party Supplies > Paper > Stationery > Lists','','paper_and_party_supplies.paper.stationery.lists',2519,1);
INSERT INTO `ps_etsy_categories` VALUES (2530,1310,'Paper & Party Supplies > Paper > Stationery > Note Cards','','paper_and_party_supplies.paper.stationery.note_cards',2519,1);
INSERT INTO `ps_etsy_categories` VALUES (2531,1311,'Paper & Party Supplies > Paper > Stationery > Notepads','','paper_and_party_supplies.paper.stationery.notepads',2519,1);
INSERT INTO `ps_etsy_categories` VALUES (2532,1312,'Paper & Party Supplies > Paper > Stationery > Postcards','','paper_and_party_supplies.paper.stationery.postcards',2519,1);
INSERT INTO `ps_etsy_categories` VALUES (2533,1313,'Paper & Party Supplies > Paper > Stationery > Programs','','paper_and_party_supplies.paper.stationery.programs',2519,1);
INSERT INTO `ps_etsy_categories` VALUES (2534,1315,'Paper & Party Supplies > Paper > Stationery > Stationery Sets','','paper_and_party_supplies.paper.stationery.stationery_sets',2519,1);
INSERT INTO `ps_etsy_categories` VALUES (2535,1317,'Paper & Party Supplies > Paper > Stickers, Labels & Tags','','paper_and_party_supplies.paper.stickers_labels_and_tags',2476,0);
INSERT INTO `ps_etsy_categories` VALUES (2536,1318,'Paper & Party Supplies > Paper > Stickers, Labels & Tags > Address & Shipping Labels','','paper_and_party_supplies.paper.stickers_labels_and_tags.address_and_shipping_labels',2535,1);
INSERT INTO `ps_etsy_categories` VALUES (2537,1319,'Paper & Party Supplies > Paper > Stickers, Labels & Tags > Bumper Stickers','','paper_and_party_supplies.paper.stickers_labels_and_tags.bumper_stickers',2535,1);
INSERT INTO `ps_etsy_categories` VALUES (2538,1320,'Paper & Party Supplies > Paper > Stickers, Labels & Tags > Clings','','paper_and_party_supplies.paper.stickers_labels_and_tags.clings',2535,1);
INSERT INTO `ps_etsy_categories` VALUES (2539,1322,'Paper & Party Supplies > Paper > Stickers, Labels & Tags > Labels','','paper_and_party_supplies.paper.stickers_labels_and_tags.labels',2535,1);
INSERT INTO `ps_etsy_categories` VALUES (2540,1330,'Paper & Party Supplies > Party Supplies','','paper_and_party_supplies.party_supplies',2475,0);
INSERT INTO `ps_etsy_categories` VALUES (2541,1331,'Paper & Party Supplies > Party Supplies > Party Décor','','paper_and_party_supplies.party_supplies.party_decor',2540,0);
INSERT INTO `ps_etsy_categories` VALUES (2542,1332,'Paper & Party Supplies > Party Supplies > Party Décor > Backdrops & Props','','paper_and_party_supplies.party_supplies.party_decor.backdrops_and_props',2541,1);
INSERT INTO `ps_etsy_categories` VALUES (2543,1334,'Paper & Party Supplies > Party Supplies > Party Décor > Banners & Signs','','paper_and_party_supplies.party_supplies.party_decor.banners_and_signs',2541,1);
INSERT INTO `ps_etsy_categories` VALUES (2544,1336,'Paper & Party Supplies > Party Supplies > Party Décor > Centerpieces','','paper_and_party_supplies.party_supplies.party_decor.centerpieces',2541,1);
INSERT INTO `ps_etsy_categories` VALUES (2545,1339,'Paper & Party Supplies > Party Supplies > Party Décor > Garlands, Flags & Bunting','','paper_and_party_supplies.party_supplies.party_decor.garlands_flags_and_bunting',2541,1);
INSERT INTO `ps_etsy_categories` VALUES (2546,1340,'Paper & Party Supplies > Party Supplies > Party Décor > Guest Books','','paper_and_party_supplies.party_supplies.party_decor.guest_books',2541,1);
INSERT INTO `ps_etsy_categories` VALUES (2547,6122,'Paper & Party Supplies > Party Supplies > Party Décor > Paper Fans','','paper_and_party_supplies.party_supplies.party_decor.paper_fans',2541,1);
INSERT INTO `ps_etsy_categories` VALUES (2548,1341,'Paper & Party Supplies > Party Supplies > Party Décor > Party Hats & Crowns','','paper_and_party_supplies.party_supplies.party_decor.party_hats_and_crowns',2541,1);
INSERT INTO `ps_etsy_categories` VALUES (2549,1343,'Paper & Party Supplies > Party Supplies > Party Décor > Piñatas','','paper_and_party_supplies.party_supplies.party_decor.pinatas',2541,1);
INSERT INTO `ps_etsy_categories` VALUES (2550,1344,'Paper & Party Supplies > Party Supplies > Party Décor > Place Cards & Holders','','paper_and_party_supplies.party_supplies.party_decor.place_cards_and_holders',2541,0);
INSERT INTO `ps_etsy_categories` VALUES (2551,2103,'Paper & Party Supplies > Party Supplies > Party Décor > Place Cards & Holders > Place Card Holders','','paper_and_party_supplies.party_supplies.party_decor.place_cards_and_holders.place_card_holders',2550,1);
INSERT INTO `ps_etsy_categories` VALUES (2552,2102,'Paper & Party Supplies > Party Supplies > Party Décor > Place Cards & Holders > Place Cards','','paper_and_party_supplies.party_supplies.party_decor.place_cards_and_holders.place_cards',2550,1);
INSERT INTO `ps_etsy_categories` VALUES (2553,1347,'Paper & Party Supplies > Party Supplies > Party Favors & Games','','paper_and_party_supplies.party_supplies.party_favors_and_games',2540,0);
INSERT INTO `ps_etsy_categories` VALUES (2554,1348,'Paper & Party Supplies > Party Supplies > Party Favors & Games > Favor Bags & Containers','','paper_and_party_supplies.party_supplies.party_favors_and_games.favor_bags_and_containers',2553,1);
INSERT INTO `ps_etsy_categories` VALUES (2555,1349,'Paper & Party Supplies > Party Supplies > Party Favors & Games > Party Favors','','paper_and_party_supplies.party_supplies.party_favors_and_games.party_favors',2553,1);
INSERT INTO `ps_etsy_categories` VALUES (2556,1350,'Paper & Party Supplies > Party Supplies > Party Favors & Games > Party Games','','paper_and_party_supplies.party_supplies.party_favors_and_games.party_games',2553,1);
INSERT INTO `ps_etsy_categories` VALUES (2557,1351,'Pet Supplies','','pet_supplies',0,0);
INSERT INTO `ps_etsy_categories` VALUES (2558,1352,'Pet Supplies > Beekeeping','','pet_supplies.beekeeping',2557,1);
INSERT INTO `ps_etsy_categories` VALUES (2559,1353,'Pet Supplies > Pet Bedding','','pet_supplies.pet_bedding',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2560,1354,'Pet Supplies > Pet Bedding > Liners','','pet_supplies.pet_bedding.liners',2559,1);
INSERT INTO `ps_etsy_categories` VALUES (2561,1355,'Pet Supplies > Pet Bedding > Nesting Supplies','','pet_supplies.pet_bedding.nesting_supplies',2559,1);
INSERT INTO `ps_etsy_categories` VALUES (2562,1356,'Pet Supplies > Pet Bedding > Pet Blankets','','pet_supplies.pet_bedding.pet_blankets',2559,1);
INSERT INTO `ps_etsy_categories` VALUES (2563,1357,'Pet Supplies > Pet Bedding > Pet Mats & Pads','','pet_supplies.pet_bedding.pet_mats_and_pads',2559,1);
INSERT INTO `ps_etsy_categories` VALUES (2564,1358,'Pet Supplies > Pet Carriers & Houses','','pet_supplies.pet_carriers_and_houses',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2565,1360,'Pet Supplies > Pet Carriers & Houses > Aquariums & Tank Décor','','pet_supplies.pet_carriers_and_houses.aquariums_and_tank_decor',2564,1);
INSERT INTO `ps_etsy_categories` VALUES (2566,1361,'Pet Supplies > Pet Carriers & Houses > Bird Cages','','pet_supplies.pet_carriers_and_houses.birdcages',2564,1);
INSERT INTO `ps_etsy_categories` VALUES (2567,1362,'Pet Supplies > Pet Carriers & Houses > Coops','','pet_supplies.pet_carriers_and_houses.coops',2564,1);
INSERT INTO `ps_etsy_categories` VALUES (2568,1363,'Pet Supplies > Pet Carriers & Houses > Nests & Bags','','pet_supplies.pet_carriers_and_houses.nests_and_bags',2564,1);
INSERT INTO `ps_etsy_categories` VALUES (2569,1364,'Pet Supplies > Pet Carriers & Houses > Pet Crates & Kennels','','pet_supplies.pet_carriers_and_houses.pet_crates_and_kennels',2564,1);
INSERT INTO `ps_etsy_categories` VALUES (2570,1365,'Pet Supplies > Pet Carriers & Houses > Pet Houses','','pet_supplies.pet_carriers_and_houses.pet_houses',2564,1);
INSERT INTO `ps_etsy_categories` VALUES (2571,1366,'Pet Supplies > Pet Carriers & Houses > Pet Slings','','pet_supplies.pet_carriers_and_houses.pet_slings',2564,1);
INSERT INTO `ps_etsy_categories` VALUES (2572,1367,'Pet Supplies > Pet Carriers & Houses > Pet Totes','','pet_supplies.pet_carriers_and_houses.pet_totes',2564,1);
INSERT INTO `ps_etsy_categories` VALUES (2573,1368,'Pet Supplies > Pet Clothing, Accessories & Shoes','','pet_supplies.pet_clothing_accessories_and_shoes',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2574,1369,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Accessories','','pet_supplies.pet_clothing_accessories_and_shoes.pet_accessories',2573,0);
INSERT INTO `ps_etsy_categories` VALUES (2575,1370,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Accessories > Pet Bows & Hair Accessories','','pet_supplies.pet_clothing_accessories_and_shoes.pet_accessories.pet_bows_and_hair_accessories',2574,1);
INSERT INTO `ps_etsy_categories` VALUES (2576,1371,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Accessories > Pet Charms & Bells','','pet_supplies.pet_clothing_accessories_and_shoes.pet_accessories.pet_charms_and_bells',2574,1);
INSERT INTO `ps_etsy_categories` VALUES (2577,1373,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Accessories > Pet Neckwear','','pet_supplies.pet_clothing_accessories_and_shoes.pet_accessories.pet_neckwear',2574,1);
INSERT INTO `ps_etsy_categories` VALUES (2578,1372,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Accessories > Pet Sunglasses','','pet_supplies.pet_clothing_accessories_and_shoes.pet_accessories.pet_hats_and_sunglasses',2574,1);
INSERT INTO `ps_etsy_categories` VALUES (2579,1374,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Clothing','','pet_supplies.pet_clothing_accessories_and_shoes.pet_clothing',2573,0);
INSERT INTO `ps_etsy_categories` VALUES (2580,1375,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Clothing > Pet Bottoms','','pet_supplies.pet_clothing_accessories_and_shoes.pet_clothing.pet_bottoms',2579,1);
INSERT INTO `ps_etsy_categories` VALUES (2581,1376,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Clothing > Pet Costumes','','pet_supplies.pet_clothing_accessories_and_shoes.pet_clothing.pet_costumes',2579,1);
INSERT INTO `ps_etsy_categories` VALUES (2582,1377,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Clothing > Pet Diapers & Belly Bands','','pet_supplies.pet_clothing_accessories_and_shoes.pet_clothing.pet_diapers_and_belly_bands',2579,1);
INSERT INTO `ps_etsy_categories` VALUES (2583,1378,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Clothing > Pet Dresses','','pet_supplies.pet_clothing_accessories_and_shoes.pet_clothing.pet_dresses',2579,1);
INSERT INTO `ps_etsy_categories` VALUES (2584,1379,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Clothing > Pet Hats & Wigs','','pet_supplies.pet_clothing_accessories_and_shoes.pet_clothing.pet_hats_and_wigs',2579,1);
INSERT INTO `ps_etsy_categories` VALUES (2585,1380,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Clothing > Pet Jackets & Hoodies','','pet_supplies.pet_clothing_accessories_and_shoes.pet_clothing.pet_jackets_and_hoodies',2579,1);
INSERT INTO `ps_etsy_categories` VALUES (2586,1382,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Clothing > Pet Tops','','pet_supplies.pet_clothing_accessories_and_shoes.pet_clothing.pet_tops',2579,1);
INSERT INTO `ps_etsy_categories` VALUES (2587,1381,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Shoes & Booties','','pet_supplies.pet_clothing_accessories_and_shoes.pet_shoes_and_booties',2573,0);
INSERT INTO `ps_etsy_categories` VALUES (2588,2229,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Shoes & Booties > Pet Booties','','pet_supplies.pet_clothing_accessories_and_shoes.pet_shoes_and_booties.pet_booties',2587,1);
INSERT INTO `ps_etsy_categories` VALUES (2589,2228,'Pet Supplies > Pet Clothing, Accessories & Shoes > Pet Shoes & Booties > Pet Shoes','','pet_supplies.pet_clothing_accessories_and_shoes.pet_shoes_and_booties.pet_shoes',2587,1);
INSERT INTO `ps_etsy_categories` VALUES (2590,1383,'Pet Supplies > Pet Collars & Leashes','','pet_supplies.pet_collars_and_leashes',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2591,11286,'Pet Supplies > Pet Collars & Leashes > Pet Collar & Leash Sets','','pet_supplies.pet_collars_and_leashes.pet_collar_and_leash_sets',2590,1);
INSERT INTO `ps_etsy_categories` VALUES (2592,1384,'Pet Supplies > Pet Collars & Leashes > Pet Collars & Jewelry','','pet_supplies.pet_collars_and_leashes.pet_collars_and_jewelry',2590,1);
INSERT INTO `ps_etsy_categories` VALUES (2593,1385,'Pet Supplies > Pet Collars & Leashes > Pet Harnesses & Backpacks','','pet_supplies.pet_collars_and_leashes.pet_harnesses_and_backpacks',2590,1);
INSERT INTO `ps_etsy_categories` VALUES (2594,1386,'Pet Supplies > Pet Collars & Leashes > Pet ID Tags','','pet_supplies.pet_collars_and_leashes.pet_id_tags',2590,1);
INSERT INTO `ps_etsy_categories` VALUES (2595,1387,'Pet Supplies > Pet Collars & Leashes > Pet Leashes','','pet_supplies.pet_collars_and_leashes.pet_leashes',2590,1);
INSERT INTO `ps_etsy_categories` VALUES (2596,1388,'Pet Supplies > Pet Feeding','','pet_supplies.pet_feeding',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2597,1389,'Pet Supplies > Pet Feeding > Feeding Stands','','pet_supplies.pet_feeding.feeding_stands',2596,1);
INSERT INTO `ps_etsy_categories` VALUES (2598,1390,'Pet Supplies > Pet Feeding > Pet Bowls','','pet_supplies.pet_feeding.pet_bowls',2596,1);
INSERT INTO `ps_etsy_categories` VALUES (2599,1391,'Pet Supplies > Pet Feeding > Pet Feeders & Waterers','','pet_supplies.pet_feeding.pet_feeders_and_waterers',2596,1);
INSERT INTO `ps_etsy_categories` VALUES (2600,1746,'Pet Supplies > Pet Feeding > Pet Food & Treats','','pet_supplies.pet_feeding.pet_food_and_treats',2596,0);
INSERT INTO `ps_etsy_categories` VALUES (2601,2924,'Pet Supplies > Pet Feeding > Pet Food & Treats > Pet Treats','','pet_supplies.pet_feeding.pet_food_and_treats.pet_treats',2600,1);
INSERT INTO `ps_etsy_categories` VALUES (2602,1393,'Pet Supplies > Pet Feeding > Placemats','','pet_supplies.pet_feeding.placemats',2596,1);
INSERT INTO `ps_etsy_categories` VALUES (2603,1394,'Pet Supplies > Pet Furniture','','pet_supplies.pet_furniture',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2604,1395,'Pet Supplies > Pet Furniture > Cage Stands','','pet_supplies.pet_furniture.cage_stands',2603,1);
INSERT INTO `ps_etsy_categories` VALUES (2605,1396,'Pet Supplies > Pet Furniture > Pet Beds & Cots','','pet_supplies.pet_furniture.pet_beds_and_cots',2603,1);
INSERT INTO `ps_etsy_categories` VALUES (2606,1397,'Pet Supplies > Pet Furniture > Pet Hammocks','','pet_supplies.pet_furniture.pet_hammocks',2603,1);
INSERT INTO `ps_etsy_categories` VALUES (2607,1398,'Pet Supplies > Pet Furniture > Pet Ramps','','pet_supplies.pet_furniture.pet_ramps',2603,1);
INSERT INTO `ps_etsy_categories` VALUES (2608,1399,'Pet Supplies > Pet Furniture > Pet Steps','','pet_supplies.pet_furniture.pet_steps',2603,1);
INSERT INTO `ps_etsy_categories` VALUES (2609,1400,'Pet Supplies > Pet Furniture > Play Furniture','','pet_supplies.pet_furniture.play_furniture',2603,1);
INSERT INTO `ps_etsy_categories` VALUES (2610,1401,'Pet Supplies > Pet Gates & Fences','','pet_supplies.pet_gates_and_fences',2557,1);
INSERT INTO `ps_etsy_categories` VALUES (2611,1402,'Pet Supplies > Pet Health & Wellness','','pet_supplies.pet_health_and_wellness',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2612,1403,'Pet Supplies > Pet Health & Wellness > Deodorizers & Perfumes','','pet_supplies.pet_health_and_wellness.deodorizers_and_perfumes',2611,1);
INSERT INTO `ps_etsy_categories` VALUES (2613,1404,'Pet Supplies > Pet Health & Wellness > Pet Dental','','pet_supplies.pet_health_and_wellness.pet_dental',2611,1);
INSERT INTO `ps_etsy_categories` VALUES (2614,1405,'Pet Supplies > Pet Health & Wellness > Pet Grooming','','pet_supplies.pet_health_and_wellness.pet_grooming',2611,0);
INSERT INTO `ps_etsy_categories` VALUES (2615,1406,'Pet Supplies > Pet Health & Wellness > Pet Grooming > Brushes & Combs','','pet_supplies.pet_health_and_wellness.pet_grooming.brushes_and_combs',2614,1);
INSERT INTO `ps_etsy_categories` VALUES (2616,1407,'Pet Supplies > Pet Health & Wellness > Pet Grooming > Nail & Claw Care','','pet_supplies.pet_health_and_wellness.pet_grooming.nail_and_claw_care',2614,1);
INSERT INTO `ps_etsy_categories` VALUES (2617,1408,'Pet Supplies > Pet Health & Wellness > Pet Grooming > Shampoos & Washes','','pet_supplies.pet_health_and_wellness.pet_grooming.shampoos_and_washes',2614,1);
INSERT INTO `ps_etsy_categories` VALUES (2618,1409,'Pet Supplies > Pet Health & Wellness > Pet Pest Control','','pet_supplies.pet_health_and_wellness.pet_pest_control',2611,1);
INSERT INTO `ps_etsy_categories` VALUES (2619,1410,'Pet Supplies > Pet Health & Wellness > Pet Vitamins & Supplements','','pet_supplies.pet_health_and_wellness.pet_vitamins_and_supplements',2611,1);
INSERT INTO `ps_etsy_categories` VALUES (2620,1411,'Pet Supplies > Pet Health & Wellness > Pet Waste','','pet_supplies.pet_health_and_wellness.pet_waste',2611,0);
INSERT INTO `ps_etsy_categories` VALUES (2621,1412,'Pet Supplies > Pet Health & Wellness > Pet Waste > Bag Holders & Scoops','','pet_supplies.pet_health_and_wellness.pet_waste.bag_holders_and_scoops',2620,1);
INSERT INTO `ps_etsy_categories` VALUES (2622,1414,'Pet Supplies > Pet Health & Wellness > Pet Waste > Litter Box Mats & Liners','','pet_supplies.pet_health_and_wellness.pet_waste.litter_box_mats_and_liners',2620,1);
INSERT INTO `ps_etsy_categories` VALUES (2623,1413,'Pet Supplies > Pet Health & Wellness > Pet Waste > Litter Boxes','','pet_supplies.pet_health_and_wellness.pet_waste.litter_boxes',2620,1);
INSERT INTO `ps_etsy_categories` VALUES (2624,1415,'Pet Supplies > Pet Storage','','pet_supplies.pet_storage',2557,1);
INSERT INTO `ps_etsy_categories` VALUES (2625,1416,'Pet Supplies > Pet Toys','','pet_supplies.pet_toys',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2626,1417,'Pet Supplies > Pet Toys > Bird Toys','','pet_supplies.pet_toys.bird_toys',2625,1);
INSERT INTO `ps_etsy_categories` VALUES (2627,1418,'Pet Supplies > Pet Toys > Cat Toys','','pet_supplies.pet_toys.cat_toys',2625,1);
INSERT INTO `ps_etsy_categories` VALUES (2628,1419,'Pet Supplies > Pet Toys > Dog Toys','','pet_supplies.pet_toys.dog_toys',2625,1);
INSERT INTO `ps_etsy_categories` VALUES (2629,1420,'Pet Supplies > Pet Toys > Small Animal Toys','','pet_supplies.pet_toys.small_animal_toys',2625,1);
INSERT INTO `ps_etsy_categories` VALUES (2630,1421,'Pet Supplies > Riding & Farm Animals','','pet_supplies.riding_and_farm_animals',2557,1);
INSERT INTO `ps_etsy_categories` VALUES (2631,1422,'Pet Supplies > Training','','pet_supplies.training',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2632,1423,'Pet Supplies > Training > Muzzles','','pet_supplies.training.muzzles',2631,1);
INSERT INTO `ps_etsy_categories` VALUES (2633,1424,'Pet Supplies > Training > Training Bells & Whistles','','pet_supplies.training.training_bells_and_whistles',2631,1);
INSERT INTO `ps_etsy_categories` VALUES (2634,1425,'Pet Supplies > Urns & Memorials','','pet_supplies.urns_and_memorials',2557,0);
INSERT INTO `ps_etsy_categories` VALUES (2635,1426,'Pet Supplies > Urns & Memorials > Pet Grave Markers','','pet_supplies.urns_and_memorials.pet_grave_markers',2634,1);
INSERT INTO `ps_etsy_categories` VALUES (2636,1427,'Pet Supplies > Urns & Memorials > Pet Memorial Jewelry','','pet_supplies.urns_and_memorials.pet_memorial_jewelry',2634,1);
INSERT INTO `ps_etsy_categories` VALUES (2637,2638,'Pet Supplies > Urns & Memorials > Pet Portraits','','pet_supplies.urns_and_memorials.pet_portraits',2634,1);
INSERT INTO `ps_etsy_categories` VALUES (2638,1428,'Pet Supplies > Urns & Memorials > Pet Urns','','pet_supplies.urns_and_memorials.pet_urns',2634,1);
INSERT INTO `ps_etsy_categories` VALUES (2639,1429,'Shoes','','shoes',0,0);
INSERT INTO `ps_etsy_categories` VALUES (2640,1430,'Shoes > Boys\' Shoes','','shoes.boys_shoes',2639,0);
INSERT INTO `ps_etsy_categories` VALUES (2641,1431,'Shoes > Boys\' Shoes > Booties & Crib Shoes','','shoes.boys_shoes.booties_and_crib_shoes',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2642,1432,'Shoes > Boys\' Shoes > Boots','','shoes.boys_shoes.boots',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2643,1433,'Shoes > Boys\' Shoes > Clogs & Mules','','shoes.boys_shoes.clogs_and_mules',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2644,1434,'Shoes > Boys\' Shoes > Costume Shoes','','shoes.boys_shoes.costume_shoes',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2645,1435,'Shoes > Boys\' Shoes > Loafers & Slip Ons','','shoes.boys_shoes.loafers_and_slip_ons',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2646,1436,'Shoes > Boys\' Shoes > Oxfords & Wingtips','','shoes.boys_shoes.oxfords_and_wingtips',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2647,1437,'Shoes > Boys\' Shoes > Sandals','','shoes.boys_shoes.sandals',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2648,1438,'Shoes > Boys\' Shoes > Slippers','','shoes.boys_shoes.slippers',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2649,1439,'Shoes > Boys\' Shoes > Sneakers & Athletic Shoes','','shoes.boys_shoes.sneakers_and_athletic_shoes',2640,1);
INSERT INTO `ps_etsy_categories` VALUES (2650,1440,'Shoes > Girls\' Shoes','','shoes.girls_shoes',2639,0);
INSERT INTO `ps_etsy_categories` VALUES (2651,1441,'Shoes > Girls\' Shoes > Booties & Crib Shoes','','shoes.girls_shoes.booties_and_crib_shoes',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2652,1442,'Shoes > Girls\' Shoes > Boots','','shoes.girls_shoes.boots',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2653,1443,'Shoes > Girls\' Shoes > Clogs & Mules','','shoes.girls_shoes.clogs_and_mules',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2654,1444,'Shoes > Girls\' Shoes > Costume Shoes','','shoes.girls_shoes.costume_shoes',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2655,1445,'Shoes > Girls\' Shoes > Dance Shoes','','shoes.girls_shoes.dance_shoes',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2656,1446,'Shoes > Girls\' Shoes > Heels','','shoes.girls_shoes.heels',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2657,1447,'Shoes > Girls\' Shoes > Mary Janes','','shoes.girls_shoes.mary_janes',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2658,1448,'Shoes > Girls\' Shoes > Sandals','','shoes.girls_shoes.sandals',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2659,1449,'Shoes > Girls\' Shoes > Slippers','','shoes.girls_shoes.slippers',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2660,1450,'Shoes > Girls\' Shoes > Sneakers & Athletic Shoes','','shoes.girls_shoes.sneakers_and_athletic_shoes',2650,1);
INSERT INTO `ps_etsy_categories` VALUES (2661,1451,'Shoes > Insoles & Accessories','','shoes.insoles_and_accessories',2639,0);
INSERT INTO `ps_etsy_categories` VALUES (2662,1452,'Shoes > Insoles & Accessories > Insoles','','shoes.insoles_and_accessories.insoles',2661,1);
INSERT INTO `ps_etsy_categories` VALUES (2663,1453,'Shoes > Insoles & Accessories > Shoe Care & Cleaning','','shoes.insoles_and_accessories.shoe_care_and_cleaning',2661,1);
INSERT INTO `ps_etsy_categories` VALUES (2664,1457,'Shoes > Insoles & Accessories > Shoe Trees','','shoes.insoles_and_accessories.shoe_trees',2661,1);
INSERT INTO `ps_etsy_categories` VALUES (2665,1455,'Shoes > Insoles & Accessories > Shoehorns','','shoes.insoles_and_accessories.shoehorns',2661,1);
INSERT INTO `ps_etsy_categories` VALUES (2666,1456,'Shoes > Insoles & Accessories > Shoelaces','','shoes.insoles_and_accessories.shoelaces',2661,1);
INSERT INTO `ps_etsy_categories` VALUES (2667,1458,'Shoes > Men\'s Shoes','','shoes.mens_shoes',2639,0);
INSERT INTO `ps_etsy_categories` VALUES (2668,1459,'Shoes > Men\'s Shoes > Boots','','shoes.mens_shoes.boots',2667,0);
INSERT INTO `ps_etsy_categories` VALUES (2669,1460,'Shoes > Men\'s Shoes > Boots > Chukka Boots','','shoes.mens_shoes.boots.chukka_boots',2668,1);
INSERT INTO `ps_etsy_categories` VALUES (2670,1462,'Shoes > Men\'s Shoes > Boots > Cowboy & Western Boots','','shoes.mens_shoes.boots.cowboy_and_western_boots',2668,1);
INSERT INTO `ps_etsy_categories` VALUES (2671,1463,'Shoes > Men\'s Shoes > Boots > Dress Boots','','shoes.mens_shoes.boots.dress_boots',2668,1);
INSERT INTO `ps_etsy_categories` VALUES (2672,1465,'Shoes > Men\'s Shoes > Boots > Motorcycle Boots','','shoes.mens_shoes.boots.motorcycle_boots',2668,1);
INSERT INTO `ps_etsy_categories` VALUES (2673,1466,'Shoes > Men\'s Shoes > Boots > Rain & Snow Boots','','shoes.mens_shoes.boots.rain_and_snow_boots',2668,1);
INSERT INTO `ps_etsy_categories` VALUES (2674,1464,'Shoes > Men\'s Shoes > Boots > Walking & Hiking Boots','','shoes.mens_shoes.boots.hiking_and_walking_boots',2668,1);
INSERT INTO `ps_etsy_categories` VALUES (2675,1461,'Shoes > Men\'s Shoes > Boots > Work & Combat Boots','','shoes.mens_shoes.boots.combat_and_work_boots',2668,1);
INSERT INTO `ps_etsy_categories` VALUES (2676,1467,'Shoes > Men\'s Shoes > Clogs & Mules','','shoes.mens_shoes.clogs_and_mules',2667,1);
INSERT INTO `ps_etsy_categories` VALUES (2677,1468,'Shoes > Men\'s Shoes > Costume Shoes','','shoes.mens_shoes.costume_shoes',2667,1);
INSERT INTO `ps_etsy_categories` VALUES (2678,1469,'Shoes > Men\'s Shoes > Loafers & Slip Ons','','shoes.mens_shoes.loafers_and_slip_ons',2667,1);
INSERT INTO `ps_etsy_categories` VALUES (2679,1470,'Shoes > Men\'s Shoes > Oxfords & Wingtips','','shoes.mens_shoes.oxfords_and_wingtips',2667,1);
INSERT INTO `ps_etsy_categories` VALUES (2680,1471,'Shoes > Men\'s Shoes > Sandals','','shoes.mens_shoes.sandals',2667,0);
INSERT INTO `ps_etsy_categories` VALUES (2681,1472,'Shoes > Men\'s Shoes > Sandals > Fisherman','','shoes.mens_shoes.sandals.fisherman',2680,1);
INSERT INTO `ps_etsy_categories` VALUES (2682,1473,'Shoes > Men\'s Shoes > Sandals > Flip Flops & Thongs','','shoes.mens_shoes.sandals.flip_flops_and_thongs',2680,1);
INSERT INTO `ps_etsy_categories` VALUES (2683,1474,'Shoes > Men\'s Shoes > Sandals > Slides','','shoes.mens_shoes.sandals.slides',2680,1);
INSERT INTO `ps_etsy_categories` VALUES (2684,1475,'Shoes > Men\'s Shoes > Sandals > Sport Sandals','','shoes.mens_shoes.sandals.sport_sandals',2680,1);
INSERT INTO `ps_etsy_categories` VALUES (2685,1476,'Shoes > Men\'s Shoes > Slippers','','shoes.mens_shoes.slippers',2667,1);
INSERT INTO `ps_etsy_categories` VALUES (2686,1477,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes','','shoes.mens_shoes.sneakers_and_athletic_shoes',2667,0);
INSERT INTO `ps_etsy_categories` VALUES (2687,1478,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Boat & Water Shoes','','shoes.mens_shoes.sneakers_and_athletic_shoes.boat_and_water_shoes',2686,1);
INSERT INTO `ps_etsy_categories` VALUES (2688,1479,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Golf Shoes & Cleats','','shoes.mens_shoes.sneakers_and_athletic_shoes.golf_shoes_and_cleats',2686,1);
INSERT INTO `ps_etsy_categories` VALUES (2689,1480,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Hi Tops','','shoes.mens_shoes.sneakers_and_athletic_shoes.hi_tops',2686,1);
INSERT INTO `ps_etsy_categories` VALUES (2690,1481,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Platform & Club Sneakers','','shoes.mens_shoes.sneakers_and_athletic_shoes.platform_and_club_sneakers',2686,1);
INSERT INTO `ps_etsy_categories` VALUES (2691,1482,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Skates','','shoes.mens_shoes.sneakers_and_athletic_shoes.skates',2686,0);
INSERT INTO `ps_etsy_categories` VALUES (2692,1989,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Skates > Ice Skates','','shoes.mens_shoes.sneakers_and_athletic_shoes.skates.ice_skates',2691,1);
INSERT INTO `ps_etsy_categories` VALUES (2693,1991,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Skates > Roller Blades','','shoes.mens_shoes.sneakers_and_athletic_shoes.skates.roller_blades',2691,1);
INSERT INTO `ps_etsy_categories` VALUES (2694,1990,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Skates > Roller Skates','','shoes.mens_shoes.sneakers_and_athletic_shoes.skates.roller_skates',2691,1);
INSERT INTO `ps_etsy_categories` VALUES (2695,1483,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Slip Ons','','shoes.mens_shoes.sneakers_and_athletic_shoes.slip_ons',2686,1);
INSERT INTO `ps_etsy_categories` VALUES (2696,1484,'Shoes > Men\'s Shoes > Sneakers & Athletic Shoes > Tie Sneakers','','shoes.mens_shoes.sneakers_and_athletic_shoes.tie_sneakers',2686,1);
INSERT INTO `ps_etsy_categories` VALUES (2697,1485,'Shoes > Unisex Adult Shoes','','shoes.unisex_adult_shoes',2639,0);
INSERT INTO `ps_etsy_categories` VALUES (2698,1486,'Shoes > Unisex Adult Shoes > Boots','','shoes.unisex_adult_shoes.boots',2697,0);
INSERT INTO `ps_etsy_categories` VALUES (2699,1487,'Shoes > Unisex Adult Shoes > Boots > Chukka Boots','','shoes.unisex_adult_shoes.boots.chukka_boots',2698,1);
INSERT INTO `ps_etsy_categories` VALUES (2700,1488,'Shoes > Unisex Adult Shoes > Boots > Cowboy & Western Boots','','shoes.unisex_adult_shoes.boots.cowboy_and_western_boots',2698,1);
INSERT INTO `ps_etsy_categories` VALUES (2701,1489,'Shoes > Unisex Adult Shoes > Boots > Rain & Snow Boots','','shoes.unisex_adult_shoes.boots.rain_and_snow_boots',2698,1);
INSERT INTO `ps_etsy_categories` VALUES (2702,1490,'Shoes > Unisex Adult Shoes > Boots > Walking & Hiking Boots','','shoes.unisex_adult_shoes.boots.walking_and_hiking_boots',2698,1);
INSERT INTO `ps_etsy_categories` VALUES (2703,1491,'Shoes > Unisex Adult Shoes > Boots > Work & Combat Boots','','shoes.unisex_adult_shoes.boots.work_and_combat_boots',2698,1);
INSERT INTO `ps_etsy_categories` VALUES (2704,2842,'Shoes > Unisex Adult Shoes > Clogs','','shoes.unisex_adult_shoes.clogs',2697,1);
INSERT INTO `ps_etsy_categories` VALUES (2705,1492,'Shoes > Unisex Adult Shoes > Sandals','','shoes.unisex_adult_shoes.sandals',2697,0);
INSERT INTO `ps_etsy_categories` VALUES (2706,1493,'Shoes > Unisex Adult Shoes > Sandals > Fisherman','','shoes.unisex_adult_shoes.sandals.fisherman',2705,1);
INSERT INTO `ps_etsy_categories` VALUES (2707,1494,'Shoes > Unisex Adult Shoes > Sandals > Flip Flops & Thongs','','shoes.unisex_adult_shoes.sandals.flip_flops_and_thongs',2705,1);
INSERT INTO `ps_etsy_categories` VALUES (2708,1495,'Shoes > Unisex Adult Shoes > Sandals > Slides','','shoes.unisex_adult_shoes.sandals.slides',2705,1);
INSERT INTO `ps_etsy_categories` VALUES (2709,2241,'Shoes > Unisex Adult Shoes > Skates','','shoes.unisex_adult_shoes.skates',2697,0);
INSERT INTO `ps_etsy_categories` VALUES (2710,2242,'Shoes > Unisex Adult Shoes > Skates > Ice Skates','','shoes.unisex_adult_shoes.skates.ice_skates',2709,1);
INSERT INTO `ps_etsy_categories` VALUES (2711,2244,'Shoes > Unisex Adult Shoes > Skates > Roller Blades','','shoes.unisex_adult_shoes.skates.roller_blades',2709,1);
INSERT INTO `ps_etsy_categories` VALUES (2712,2243,'Shoes > Unisex Adult Shoes > Skates > Roller Skates','','shoes.unisex_adult_shoes.skates.roller_skates',2709,1);
INSERT INTO `ps_etsy_categories` VALUES (2713,2843,'Shoes > Unisex Adult Shoes > Slippers','','shoes.unisex_adult_shoes.slippers',2697,1);
INSERT INTO `ps_etsy_categories` VALUES (2714,1496,'Shoes > Unisex Adult Shoes > Sneakers & Athletic Shoes','','shoes.unisex_adult_shoes.sneakers_and_athletic_shoes',2697,0);
INSERT INTO `ps_etsy_categories` VALUES (2715,1497,'Shoes > Unisex Adult Shoes > Sneakers & Athletic Shoes > Boat & Water Shoes','','shoes.unisex_adult_shoes.sneakers_and_athletic_shoes.boat_and_water_shoes',2714,1);
INSERT INTO `ps_etsy_categories` VALUES (2716,1498,'Shoes > Unisex Adult Shoes > Sneakers & Athletic Shoes > Golf Shoes & Cleats','','shoes.unisex_adult_shoes.sneakers_and_athletic_shoes.golf_shoes_and_cleats',2714,1);
INSERT INTO `ps_etsy_categories` VALUES (2717,1499,'Shoes > Unisex Adult Shoes > Sneakers & Athletic Shoes > Hi Tops','','shoes.unisex_adult_shoes.sneakers_and_athletic_shoes.hi_tops',2714,1);
INSERT INTO `ps_etsy_categories` VALUES (2718,1500,'Shoes > Unisex Adult Shoes > Sneakers & Athletic Shoes > Platform & Club Sneakers','','shoes.unisex_adult_shoes.sneakers_and_athletic_shoes.platform_and_club_sneakers',2714,1);
INSERT INTO `ps_etsy_categories` VALUES (2719,1501,'Shoes > Unisex Adult Shoes > Sneakers & Athletic Shoes > Skates','','shoes.unisex_adult_shoes.sneakers_and_athletic_shoes.skates',2714,1);
INSERT INTO `ps_etsy_categories` VALUES (2720,1502,'Shoes > Unisex Adult Shoes > Sneakers & Athletic Shoes > Slip Ons','','shoes.unisex_adult_shoes.sneakers_and_athletic_shoes.slip_ons',2714,1);
INSERT INTO `ps_etsy_categories` VALUES (2721,1503,'Shoes > Unisex Adult Shoes > Sneakers & Athletic Shoes > Tie Sneakers','','shoes.unisex_adult_shoes.sneakers_and_athletic_shoes.tie_sneakers',2714,1);
INSERT INTO `ps_etsy_categories` VALUES (2722,1504,'Shoes > Unisex Kids\' Shoes','','shoes.unisex_kids_shoes',2639,0);
INSERT INTO `ps_etsy_categories` VALUES (2723,1505,'Shoes > Unisex Kids\' Shoes > Booties & Crib Shoes','','shoes.unisex_kids_shoes.booties_and_crib_shoes',2722,1);
INSERT INTO `ps_etsy_categories` VALUES (2724,1506,'Shoes > Unisex Kids\' Shoes > Boots','','shoes.unisex_kids_shoes.boots',2722,1);
INSERT INTO `ps_etsy_categories` VALUES (2725,1507,'Shoes > Unisex Kids\' Shoes > Clogs & Mules','','shoes.unisex_kids_shoes.clogs_and_mules',2722,1);
INSERT INTO `ps_etsy_categories` VALUES (2726,1508,'Shoes > Unisex Kids\' Shoes > Costume Shoes','','shoes.unisex_kids_shoes.costume_shoes',2722,1);
INSERT INTO `ps_etsy_categories` VALUES (2727,1509,'Shoes > Unisex Kids\' Shoes > Sandals','','shoes.unisex_kids_shoes.sandals',2722,1);
INSERT INTO `ps_etsy_categories` VALUES (2728,1510,'Shoes > Unisex Kids\' Shoes > Slippers','','shoes.unisex_kids_shoes.slippers',2722,1);
INSERT INTO `ps_etsy_categories` VALUES (2729,1511,'Shoes > Unisex Kids\' Shoes > Sneakers & Athletic Shoes','','shoes.unisex_kids_shoes.sneakers_and_athletic_shoes',2722,1);
INSERT INTO `ps_etsy_categories` VALUES (2730,1512,'Shoes > Women\'s Shoes','','shoes.womens_shoes',2639,0);
INSERT INTO `ps_etsy_categories` VALUES (2731,1513,'Shoes > Women\'s Shoes > Boots','','shoes.womens_shoes.boots',2730,0);
INSERT INTO `ps_etsy_categories` VALUES (2732,1514,'Shoes > Women\'s Shoes > Boots > Booties & Ankle Boots','','shoes.womens_shoes.boots.booties_and_ankle_boots',2731,1);
INSERT INTO `ps_etsy_categories` VALUES (2733,1515,'Shoes > Women\'s Shoes > Boots > Chukka Boots','','shoes.womens_shoes.boots.chukka_boots',2731,1);
INSERT INTO `ps_etsy_categories` VALUES (2734,1516,'Shoes > Women\'s Shoes > Boots > Cowboy & Western Boots','','shoes.womens_shoes.boots.cowboy_and_western_boots',2731,1);
INSERT INTO `ps_etsy_categories` VALUES (2735,1517,'Shoes > Women\'s Shoes > Boots > Rain & Snow Boots','','shoes.womens_shoes.boots.rain_and_snow_boots',2731,0);
INSERT INTO `ps_etsy_categories` VALUES (2736,1812,'Shoes > Women\'s Shoes > Boots > Rain & Snow Boots > Rain Boots','','shoes.womens_shoes.boots.rain_and_snow_boots.rain_boots',2735,1);
INSERT INTO `ps_etsy_categories` VALUES (2737,1813,'Shoes > Women\'s Shoes > Boots > Rain & Snow Boots > Winter Boots','','shoes.womens_shoes.boots.rain_and_snow_boots.winter_boots',2735,1);
INSERT INTO `ps_etsy_categories` VALUES (2738,1518,'Shoes > Women\'s Shoes > Boots > Riding Boots','','shoes.womens_shoes.boots.riding_boots',2731,1);
INSERT INTO `ps_etsy_categories` VALUES (2739,1519,'Shoes > Women\'s Shoes > Boots > Slouch Boots','','shoes.womens_shoes.boots.slouch_boots',2731,1);
INSERT INTO `ps_etsy_categories` VALUES (2740,1520,'Shoes > Women\'s Shoes > Boots > Walking & Hiking Boots','','shoes.womens_shoes.boots.walking_and_hiking_boots',2731,1);
INSERT INTO `ps_etsy_categories` VALUES (2741,1521,'Shoes > Women\'s Shoes > Boots > Work & Combat Boots','','shoes.womens_shoes.boots.work_and_combat_boots',2731,1);
INSERT INTO `ps_etsy_categories` VALUES (2742,1522,'Shoes > Women\'s Shoes > Clogs & Mules','','shoes.womens_shoes.clogs_and_mules',2730,0);
INSERT INTO `ps_etsy_categories` VALUES (2743,1810,'Shoes > Women\'s Shoes > Clogs & Mules > Clogs','','shoes.womens_shoes.clogs_and_mules.clogs',2742,1);
INSERT INTO `ps_etsy_categories` VALUES (2744,1811,'Shoes > Women\'s Shoes > Clogs & Mules > Mules','','shoes.womens_shoes.clogs_and_mules.mules',2742,1);
INSERT INTO `ps_etsy_categories` VALUES (2745,1523,'Shoes > Women\'s Shoes > Costume Shoes','','shoes.womens_shoes.costume_shoes',2730,1);
INSERT INTO `ps_etsy_categories` VALUES (2746,1524,'Shoes > Women\'s Shoes > Mary Janes','','shoes.womens_shoes.mary_janes',2730,1);
INSERT INTO `ps_etsy_categories` VALUES (2747,1525,'Shoes > Women\'s Shoes > Oxfords & Tie Shoes','','shoes.womens_shoes.oxfords_and_tie_shoes',2730,1);
INSERT INTO `ps_etsy_categories` VALUES (2748,1526,'Shoes > Women\'s Shoes > Pumps','','shoes.womens_shoes.pumps',2730,1);
INSERT INTO `ps_etsy_categories` VALUES (2749,1527,'Shoes > Women\'s Shoes > Sandals','','shoes.womens_shoes.sandals',2730,0);
INSERT INTO `ps_etsy_categories` VALUES (2750,1528,'Shoes > Women\'s Shoes > Sandals > Barefoot Sandals','','shoes.womens_shoes.sandals.barefoot_sandals',2749,1);
INSERT INTO `ps_etsy_categories` VALUES (2751,1529,'Shoes > Women\'s Shoes > Sandals > Espadrilles & Wedges','','shoes.womens_shoes.sandals.espadrilles_and_wedges',2749,1);
INSERT INTO `ps_etsy_categories` VALUES (2752,1530,'Shoes > Women\'s Shoes > Sandals > Fisherman','','shoes.womens_shoes.sandals.fisherman',2749,1);
INSERT INTO `ps_etsy_categories` VALUES (2753,1531,'Shoes > Women\'s Shoes > Sandals > Flip Flops & Thongs','','shoes.womens_shoes.sandals.flip_flops_and_thongs',2749,1);
INSERT INTO `ps_etsy_categories` VALUES (2754,1532,'Shoes > Women\'s Shoes > Sandals > Gladiator & Strappy Sandals','','shoes.womens_shoes.sandals.gladiator_and_strappy_sandals',2749,1);
INSERT INTO `ps_etsy_categories` VALUES (2755,1533,'Shoes > Women\'s Shoes > Sandals > Huaraches','','shoes.womens_shoes.sandals.huaraches',2749,1);
INSERT INTO `ps_etsy_categories` VALUES (2756,1534,'Shoes > Women\'s Shoes > Sandals > Slingbacks & Slides','','shoes.womens_shoes.sandals.slingbacks_and_slides',2749,1);
INSERT INTO `ps_etsy_categories` VALUES (2757,1535,'Shoes > Women\'s Shoes > Sandals > T-strap Sandals','','shoes.womens_shoes.sandals.t_strap_sandals',2749,1);
INSERT INTO `ps_etsy_categories` VALUES (2758,1536,'Shoes > Women\'s Shoes > Slip Ons','','shoes.womens_shoes.slip_ons',2730,0);
INSERT INTO `ps_etsy_categories` VALUES (2759,1537,'Shoes > Women\'s Shoes > Slip Ons > Ballet Shoes','','shoes.womens_shoes.slip_ons.ballet_shoes',2758,1);
INSERT INTO `ps_etsy_categories` VALUES (2760,1538,'Shoes > Women\'s Shoes > Slip Ons > Loafers','','shoes.womens_shoes.slip_ons.loafers',2758,1);
INSERT INTO `ps_etsy_categories` VALUES (2761,1539,'Shoes > Women\'s Shoes > Slip Ons > Moccasins','','shoes.womens_shoes.slip_ons.moccasins',2758,1);
INSERT INTO `ps_etsy_categories` VALUES (2762,1540,'Shoes > Women\'s Shoes > Slip Ons > Pointed Toe Flats','','shoes.womens_shoes.slip_ons.pointed_toe_flats',2758,1);
INSERT INTO `ps_etsy_categories` VALUES (2763,1541,'Shoes > Women\'s Shoes > Slippers','','shoes.womens_shoes.slippers',2730,1);
INSERT INTO `ps_etsy_categories` VALUES (2764,1542,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes','','shoes.womens_shoes.sneakers_and_athletic_shoes',2730,0);
INSERT INTO `ps_etsy_categories` VALUES (2765,1543,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Boat & Water Shoes','','shoes.womens_shoes.sneakers_and_athletic_shoes.boat_and_water_shoes',2764,1);
INSERT INTO `ps_etsy_categories` VALUES (2766,1544,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Bowling Shoes','','shoes.womens_shoes.sneakers_and_athletic_shoes.bowling_shoes',2764,1);
INSERT INTO `ps_etsy_categories` VALUES (2767,1545,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Dance Shoes','','shoes.womens_shoes.sneakers_and_athletic_shoes.dance_shoes',2764,1);
INSERT INTO `ps_etsy_categories` VALUES (2768,1546,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Golf Shoes & Cleats','','shoes.womens_shoes.sneakers_and_athletic_shoes.golf_shoes_and_cleats',2764,1);
INSERT INTO `ps_etsy_categories` VALUES (2769,1547,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Hi Tops','','shoes.womens_shoes.sneakers_and_athletic_shoes.hi_tops',2764,1);
INSERT INTO `ps_etsy_categories` VALUES (2770,1548,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Platform & Club Sneakers','','shoes.womens_shoes.sneakers_and_athletic_shoes.platform_and_club_sneakers',2764,1);
INSERT INTO `ps_etsy_categories` VALUES (2771,1549,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Skates','','shoes.womens_shoes.sneakers_and_athletic_shoes.skates',2764,0);
INSERT INTO `ps_etsy_categories` VALUES (2772,2148,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Skates > Ice Skates','','shoes.womens_shoes.sneakers_and_athletic_shoes.skates.ice_skates',2771,1);
INSERT INTO `ps_etsy_categories` VALUES (2773,2150,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Skates > Roller Blades','','shoes.womens_shoes.sneakers_and_athletic_shoes.skates.roller_blades',2771,1);
INSERT INTO `ps_etsy_categories` VALUES (2774,2149,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Skates > Roller Skates','','shoes.womens_shoes.sneakers_and_athletic_shoes.skates.roller_skates',2771,1);
INSERT INTO `ps_etsy_categories` VALUES (2775,1550,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Slip Ons','','shoes.womens_shoes.sneakers_and_athletic_shoes.slip_ons',2764,1);
INSERT INTO `ps_etsy_categories` VALUES (2776,1551,'Shoes > Women\'s Shoes > Sneakers & Athletic Shoes > Tie Sneakers','','shoes.womens_shoes.sneakers_and_athletic_shoes.tie_sneakers',2764,1);
INSERT INTO `ps_etsy_categories` VALUES (2777,1552,'Toys & Games','','toys_and_games',0,0);
INSERT INTO `ps_etsy_categories` VALUES (2778,1553,'Toys & Games > Games & Puzzles','','toys_and_games.games_and_puzzles',2777,0);
INSERT INTO `ps_etsy_categories` VALUES (2779,1554,'Toys & Games > Games & Puzzles > Board Games','','toys_and_games.games_and_puzzles.board_games',2778,0);
INSERT INTO `ps_etsy_categories` VALUES (2780,2392,'Toys & Games > Games & Puzzles > Board Games > Bingo','','toys_and_games.games_and_puzzles.board_games.bingo',2779,1);
INSERT INTO `ps_etsy_categories` VALUES (2781,2388,'Toys & Games > Games & Puzzles > Board Games > Checkers','','toys_and_games.games_and_puzzles.board_games.checkers',2779,1);
INSERT INTO `ps_etsy_categories` VALUES (2782,2389,'Toys & Games > Games & Puzzles > Board Games > Chess','','toys_and_games.games_and_puzzles.board_games.chess',2779,1);
INSERT INTO `ps_etsy_categories` VALUES (2783,2391,'Toys & Games > Games & Puzzles > Board Games > Chinese Checkers','','toys_and_games.games_and_puzzles.board_games.chinese_checkers',2779,1);
INSERT INTO `ps_etsy_categories` VALUES (2784,2390,'Toys & Games > Games & Puzzles > Board Games > Go','','toys_and_games.games_and_puzzles.board_games.go',2779,1);
INSERT INTO `ps_etsy_categories` VALUES (2785,1555,'Toys & Games > Games & Puzzles > Card Games','','toys_and_games.games_and_puzzles.card_games',2778,0);
INSERT INTO `ps_etsy_categories` VALUES (2786,2349,'Toys & Games > Games & Puzzles > Card Games > Cribbage','','toys_and_games.games_and_puzzles.card_games.cribbage',2785,1);
INSERT INTO `ps_etsy_categories` VALUES (2787,2348,'Toys & Games > Games & Puzzles > Card Games > Poker','','toys_and_games.games_and_puzzles.card_games.poker',2785,1);
INSERT INTO `ps_etsy_categories` VALUES (2788,2386,'Toys & Games > Games & Puzzles > Card Games > Standard Card Decks','','toys_and_games.games_and_puzzles.card_games.standard_card_decks',2785,1);
INSERT INTO `ps_etsy_categories` VALUES (2789,2385,'Toys & Games > Games & Puzzles > Card Games > Trading Card Games','','toys_and_games.games_and_puzzles.card_games.trading_card_games',2785,1);
INSERT INTO `ps_etsy_categories` VALUES (2790,1556,'Toys & Games > Games & Puzzles > Dice & Tile Games','','toys_and_games.games_and_puzzles.dice_and_tile_games',2778,0);
INSERT INTO `ps_etsy_categories` VALUES (2791,2350,'Toys & Games > Games & Puzzles > Dice & Tile Games > Dice','','toys_and_games.games_and_puzzles.dice_and_tile_games.dice',2790,1);
INSERT INTO `ps_etsy_categories` VALUES (2792,2351,'Toys & Games > Games & Puzzles > Dice & Tile Games > Dice Games','','toys_and_games.games_and_puzzles.dice_and_tile_games.dice_games',2790,1);
INSERT INTO `ps_etsy_categories` VALUES (2793,2352,'Toys & Games > Games & Puzzles > Dice & Tile Games > Tile Games','','toys_and_games.games_and_puzzles.dice_and_tile_games.tile_games',2790,0);
INSERT INTO `ps_etsy_categories` VALUES (2794,2393,'Toys & Games > Games & Puzzles > Dice & Tile Games > Tile Games > Dominoes','','toys_and_games.games_and_puzzles.dice_and_tile_games.tile_games.dominoes',2793,1);
INSERT INTO `ps_etsy_categories` VALUES (2795,2387,'Toys & Games > Games & Puzzles > Dice & Tile Games > Tile Games > Mahjong','','toys_and_games.games_and_puzzles.dice_and_tile_games.tile_games.mahjong',2793,1);
INSERT INTO `ps_etsy_categories` VALUES (2796,2151,'Toys & Games > Games & Puzzles > Game Pieces','','toys_and_games.games_and_puzzles.game_pieces',2778,1);
INSERT INTO `ps_etsy_categories` VALUES (2797,1557,'Toys & Games > Games & Puzzles > Game Room','','toys_and_games.games_and_puzzles.game_room',2778,0);
INSERT INTO `ps_etsy_categories` VALUES (2798,2383,'Toys & Games > Games & Puzzles > Game Room > Pool & Billiards','','toys_and_games.games_and_puzzles.game_room.pool_and_billiards',2797,1);
INSERT INTO `ps_etsy_categories` VALUES (2799,1558,'Toys & Games > Games & Puzzles > Marbles','','toys_and_games.games_and_puzzles.marbles',2778,1);
INSERT INTO `ps_etsy_categories` VALUES (2800,1559,'Toys & Games > Games & Puzzles > Puzzles','','toys_and_games.games_and_puzzles.puzzles',2778,0);
INSERT INTO `ps_etsy_categories` VALUES (2801,2353,'Toys & Games > Games & Puzzles > Puzzles > Jigsaw Puzzles','','toys_and_games.games_and_puzzles.puzzles.jigsaw_puzzles',2800,1);
INSERT INTO `ps_etsy_categories` VALUES (2802,2394,'Toys & Games > Games & Puzzles > Role Playing Games','','toys_and_games.games_and_puzzles.role_playing_games',2778,1);
INSERT INTO `ps_etsy_categories` VALUES (2803,1560,'Toys & Games > Sports & Outdoor Recreation','','toys_and_games.sports_and_outdoor_games',2777,0);
INSERT INTO `ps_etsy_categories` VALUES (2804,1561,'Toys & Games > Sports & Outdoor Recreation > Balls','','toys_and_games.sports_and_outdoor_games.balls',2803,1);
INSERT INTO `ps_etsy_categories` VALUES (2805,1563,'Toys & Games > Sports & Outdoor Recreation > Bikes & Cycling','','toys_and_games.sports_and_outdoor_games.bikes_and_cycling',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2806,2356,'Toys & Games > Sports & Outdoor Recreation > Bikes & Cycling > Bicycles','','toys_and_games.sports_and_outdoor_games.bikes_and_cycling.bicycles',2805,1);
INSERT INTO `ps_etsy_categories` VALUES (2807,2357,'Toys & Games > Sports & Outdoor Recreation > Bikes & Cycling > Bike Parts','','toys_and_games.sports_and_outdoor_games.bikes_and_cycling.bike_parts',2805,1);
INSERT INTO `ps_etsy_categories` VALUES (2808,2364,'Toys & Games > Sports & Outdoor Recreation > Bikes & Cycling > Cycling Accessories','','toys_and_games.sports_and_outdoor_games.bikes_and_cycling.cycling_accessories',2805,1);
INSERT INTO `ps_etsy_categories` VALUES (2809,2363,'Toys & Games > Sports & Outdoor Recreation > Bikes & Cycling > Panniers','','toys_and_games.sports_and_outdoor_games.bikes_and_cycling.panniers',2805,1);
INSERT INTO `ps_etsy_categories` VALUES (2810,2362,'Toys & Games > Sports & Outdoor Recreation > Bikes & Cycling > Tricycles','','toys_and_games.sports_and_outdoor_games.bikes_and_cycling.tricycles',2805,1);
INSERT INTO `ps_etsy_categories` VALUES (2811,2361,'Toys & Games > Sports & Outdoor Recreation > Bikes & Cycling > Unicycles','','toys_and_games.sports_and_outdoor_games.bikes_and_cycling.unicycles',2805,1);
INSERT INTO `ps_etsy_categories` VALUES (2812,2342,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2813,2343,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports > Boating','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports.boating',2812,1);
INSERT INTO `ps_etsy_categories` VALUES (2814,2346,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports > Canoeing','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports.canoeing',2812,1);
INSERT INTO `ps_etsy_categories` VALUES (2815,2344,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports > Kayaking','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports.kayaking',2812,1);
INSERT INTO `ps_etsy_categories` VALUES (2816,2374,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports > Paddles & Oars','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports.paddles_and_oars',2812,1);
INSERT INTO `ps_etsy_categories` VALUES (2817,2345,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports > Rowing','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports.rowing',2812,1);
INSERT INTO `ps_etsy_categories` VALUES (2818,1577,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports > Sand & Water Toys','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports.sand_and_water_toys',2812,1);
INSERT INTO `ps_etsy_categories` VALUES (2819,2373,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports > Surfing & Boarding','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports.surfing_and_boarding',2812,1);
INSERT INTO `ps_etsy_categories` VALUES (2820,2347,'Toys & Games > Sports & Outdoor Recreation > Boating & Water Sports > Swimming & Diving','','toys_and_games.sports_and_outdoor_games.boating_and_water_sports.swimming_and_diving',2812,1);
INSERT INTO `ps_etsy_categories` VALUES (2821,1565,'Toys & Games > Sports & Outdoor Recreation > Bowling','','toys_and_games.sports_and_outdoor_games.bowling',2803,1);
INSERT INTO `ps_etsy_categories` VALUES (2822,1566,'Toys & Games > Sports & Outdoor Recreation > Camping','','toys_and_games.sports_and_outdoor_games.camping',2803,1);
INSERT INTO `ps_etsy_categories` VALUES (2823,1567,'Toys & Games > Sports & Outdoor Recreation > Fishing','','toys_and_games.sports_and_outdoor_games.fishing',2803,1);
INSERT INTO `ps_etsy_categories` VALUES (2824,1568,'Toys & Games > Sports & Outdoor Recreation > Fitness & Exercise','','toys_and_games.sports_and_outdoor_games.fitness_and_exercise',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2825,2652,'Toys & Games > Sports & Outdoor Recreation > Fitness & Exercise > Yoga & Pilates','','toys_and_games.sports_and_outdoor_games.fitness_and_exercise.yoga_and_pilates',2824,0);
INSERT INTO `ps_etsy_categories` VALUES (2826,2654,'Toys & Games > Sports & Outdoor Recreation > Fitness & Exercise > Yoga & Pilates > Pilates','','toys_and_games.sports_and_outdoor_games.fitness_and_exercise.yoga_and_pilates.pilates',2825,1);
INSERT INTO `ps_etsy_categories` VALUES (2827,2653,'Toys & Games > Sports & Outdoor Recreation > Fitness & Exercise > Yoga & Pilates > Yoga','','toys_and_games.sports_and_outdoor_games.fitness_and_exercise.yoga_and_pilates.yoga',2825,1);
INSERT INTO `ps_etsy_categories` VALUES (2828,1569,'Toys & Games > Sports & Outdoor Recreation > Flying Toys','','toys_and_games.sports_and_outdoor_games.flying_toys',2803,1);
INSERT INTO `ps_etsy_categories` VALUES (2829,1570,'Toys & Games > Sports & Outdoor Recreation > Golf','','toys_and_games.sports_and_outdoor_games.golf',2803,1);
INSERT INTO `ps_etsy_categories` VALUES (2830,1571,'Toys & Games > Sports & Outdoor Recreation > Hunting & Archery','','toys_and_games.sports_and_outdoor_games.hunting_and_archery',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2831,2355,'Toys & Games > Sports & Outdoor Recreation > Hunting & Archery > Archery','','toys_and_games.sports_and_outdoor_games.hunting_and_archery.archery',2830,1);
INSERT INTO `ps_etsy_categories` VALUES (2832,2354,'Toys & Games > Sports & Outdoor Recreation > Hunting & Archery > Hunting','','toys_and_games.sports_and_outdoor_games.hunting_and_archery.hunting',2830,0);
INSERT INTO `ps_etsy_categories` VALUES (2833,6072,'Toys & Games > Sports & Outdoor Recreation > Hunting & Archery > Hunting > Holsters','','toys_and_games.sports_and_outdoor_games.hunting_and_archery.hunting.holsters',2832,1);
INSERT INTO `ps_etsy_categories` VALUES (2834,6073,'Toys & Games > Sports & Outdoor Recreation > Hunting & Archery > Hunting > Pistol Grips & Parts','','toys_and_games.sports_and_outdoor_games.hunting_and_archery.hunting.pistol_grips_and_parts',2832,1);
INSERT INTO `ps_etsy_categories` VALUES (2835,1572,'Toys & Games > Sports & Outdoor Recreation > Ice & Snow Sports','','toys_and_games.sports_and_outdoor_games.ice_and_snow_sports',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2836,2360,'Toys & Games > Sports & Outdoor Recreation > Ice & Snow Sports > Skiing','','toys_and_games.sports_and_outdoor_games.ice_and_snow_sports.skiing',2835,1);
INSERT INTO `ps_etsy_categories` VALUES (2837,2358,'Toys & Games > Sports & Outdoor Recreation > Ice & Snow Sports > Snowboarding','','toys_and_games.sports_and_outdoor_games.ice_and_snow_sports.snowboarding',2835,1);
INSERT INTO `ps_etsy_categories` VALUES (2838,2359,'Toys & Games > Sports & Outdoor Recreation > Ice & Snow Sports > Snowshoeing','','toys_and_games.sports_and_outdoor_games.ice_and_snow_sports.snowshoeing',2835,1);
INSERT INTO `ps_etsy_categories` VALUES (2839,1573,'Toys & Games > Sports & Outdoor Recreation > Juggling & Hula Hoops','','toys_and_games.sports_and_outdoor_games.juggling_and_hula_hoops',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2840,1809,'Toys & Games > Sports & Outdoor Recreation > Juggling & Hula Hoops > Hula Hoops','','toys_and_games.sports_and_outdoor_games.juggling_and_hula_hoops.hula_hoops',2839,1);
INSERT INTO `ps_etsy_categories` VALUES (2841,1808,'Toys & Games > Sports & Outdoor Recreation > Juggling & Hula Hoops > Juggling','','toys_and_games.sports_and_outdoor_games.juggling_and_hula_hoops.juggling',2839,1);
INSERT INTO `ps_etsy_categories` VALUES (2842,1574,'Toys & Games > Sports & Outdoor Recreation > Kites & Pinwheels','','toys_and_games.sports_and_outdoor_games.kites_and_pinwheels',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2843,1806,'Toys & Games > Sports & Outdoor Recreation > Kites & Pinwheels > Kites','','toys_and_games.sports_and_outdoor_games.kites_and_pinwheels.kites',2842,1);
INSERT INTO `ps_etsy_categories` VALUES (2844,1807,'Toys & Games > Sports & Outdoor Recreation > Kites & Pinwheels > Pinwheels','','toys_and_games.sports_and_outdoor_games.kites_and_pinwheels.pinwheels',2842,1);
INSERT INTO `ps_etsy_categories` VALUES (2845,1575,'Toys & Games > Sports & Outdoor Recreation > Lawn Games','','toys_and_games.sports_and_outdoor_games.lawn_games',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2846,2379,'Toys & Games > Sports & Outdoor Recreation > Lawn Games > Bean Bag Toss','','toys_and_games.sports_and_outdoor_games.lawn_games.bean_bag_toss',2845,1);
INSERT INTO `ps_etsy_categories` VALUES (2847,2382,'Toys & Games > Sports & Outdoor Recreation > Lawn Games > Bocce','','toys_and_games.sports_and_outdoor_games.lawn_games.bocce',2845,1);
INSERT INTO `ps_etsy_categories` VALUES (2848,2381,'Toys & Games > Sports & Outdoor Recreation > Lawn Games > Croquet','','toys_and_games.sports_and_outdoor_games.lawn_games.croquet',2845,1);
INSERT INTO `ps_etsy_categories` VALUES (2849,2380,'Toys & Games > Sports & Outdoor Recreation > Lawn Games > Horseshoes','','toys_and_games.sports_and_outdoor_games.lawn_games.horseshoes',2845,1);
INSERT INTO `ps_etsy_categories` VALUES (2850,2370,'Toys & Games > Sports & Outdoor Recreation > Martial Arts & Boxing','','toys_and_games.sports_and_outdoor_games.martial_arts_and_boxing',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2851,2371,'Toys & Games > Sports & Outdoor Recreation > Martial Arts & Boxing > Boxing Gloves','','toys_and_games.sports_and_outdoor_games.martial_arts_and_boxing.boxing_gloves',2850,1);
INSERT INTO `ps_etsy_categories` VALUES (2852,1576,'Toys & Games > Sports & Outdoor Recreation > Racquet Sports','','toys_and_games.sports_and_outdoor_games.racquet_sports',2803,1);
INSERT INTO `ps_etsy_categories` VALUES (2853,1564,'Toys & Games > Sports & Outdoor Recreation > Skateboarding','','toys_and_games.sports_and_outdoor_games.skateboarding',2803,1);
INSERT INTO `ps_etsy_categories` VALUES (2854,1578,'Toys & Games > Sports & Outdoor Recreation > Swings & Slides','','toys_and_games.sports_and_outdoor_games.swings_and_slides',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2855,1805,'Toys & Games > Sports & Outdoor Recreation > Swings & Slides > Slides','','toys_and_games.sports_and_outdoor_games.swings_and_slides.slides',2854,1);
INSERT INTO `ps_etsy_categories` VALUES (2856,1804,'Toys & Games > Sports & Outdoor Recreation > Swings & Slides > Swings','','toys_and_games.sports_and_outdoor_games.swings_and_slides.swings',2854,1);
INSERT INTO `ps_etsy_categories` VALUES (2857,1579,'Toys & Games > Sports & Outdoor Recreation > Team Sports','','toys_and_games.sports_and_outdoor_games.team_sports',2803,0);
INSERT INTO `ps_etsy_categories` VALUES (2858,2365,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Baseball','','toys_and_games.sports_and_outdoor_games.team_sports.baseball',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2859,2366,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Basketball','','toys_and_games.sports_and_outdoor_games.team_sports.basketball',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2860,2367,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Football','','toys_and_games.sports_and_outdoor_games.team_sports.football',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2861,2376,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Gymnastics','','toys_and_games.sports_and_outdoor_games.team_sports.gymnastics',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2862,2377,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Hockey','','toys_and_games.sports_and_outdoor_games.team_sports.hockey',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2863,2368,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Soccer','','toys_and_games.sports_and_outdoor_games.team_sports.soccer',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2864,2369,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Softball','','toys_and_games.sports_and_outdoor_games.team_sports.softball',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2865,2375,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Volleyball','','toys_and_games.sports_and_outdoor_games.team_sports.volleyball',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2866,2372,'Toys & Games > Sports & Outdoor Recreation > Team Sports > Wrestling','','toys_and_games.sports_and_outdoor_games.team_sports.wrestling',2857,1);
INSERT INTO `ps_etsy_categories` VALUES (2867,1580,'Toys & Games > Toys','','toys_and_games.toys',2777,0);
INSERT INTO `ps_etsy_categories` VALUES (2868,1581,'Toys & Games > Toys > Baby & Toddler Toys','','toys_and_games.toys.baby_and_toddler_toys',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2869,2058,'Toys & Games > Toys > Baby & Toddler Toys > Blocks','','toys_and_games.toys.baby_and_toddler_toys.blocks',2868,1);
INSERT INTO `ps_etsy_categories` VALUES (2870,2057,'Toys & Games > Toys > Baby & Toddler Toys > Crib Toys','','toys_and_games.toys.baby_and_toddler_toys.crib_toys',2868,1);
INSERT INTO `ps_etsy_categories` VALUES (2871,2054,'Toys & Games > Toys > Baby & Toddler Toys > Music & Sound','','toys_and_games.toys.baby_and_toddler_toys.music_and_sound',2868,1);
INSERT INTO `ps_etsy_categories` VALUES (2872,2052,'Toys & Games > Toys > Baby & Toddler Toys > Rattles','','toys_and_games.toys.baby_and_toddler_toys.rattles',2868,1);
INSERT INTO `ps_etsy_categories` VALUES (2873,2056,'Toys & Games > Toys > Baby & Toddler Toys > Shapes & Colors','','toys_and_games.toys.baby_and_toddler_toys.shapes_and_colors',2868,1);
INSERT INTO `ps_etsy_categories` VALUES (2874,2055,'Toys & Games > Toys > Baby & Toddler Toys > Stacking & Nesting','','toys_and_games.toys.baby_and_toddler_toys.stacking_and_nesting',2868,1);
INSERT INTO `ps_etsy_categories` VALUES (2875,2053,'Toys & Games > Toys > Baby & Toddler Toys > Teething','','toys_and_games.toys.baby_and_toddler_toys.teething',2868,1);
INSERT INTO `ps_etsy_categories` VALUES (2876,1582,'Toys & Games > Toys > Balls','','toys_and_games.toys.balls',2867,1);
INSERT INTO `ps_etsy_categories` VALUES (2877,1583,'Toys & Games > Toys > Building & Construction','','toys_and_games.toys.building_and_construction',2867,1);
INSERT INTO `ps_etsy_categories` VALUES (2878,1584,'Toys & Games > Toys > Dolls & Action Figures','','toys_and_games.toys.dolls_and_action_figures',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2879,1585,'Toys & Games > Toys > Dolls & Action Figures > Action Figures','','toys_and_games.toys.dolls_and_action_figures.action_figures',2878,1);
INSERT INTO `ps_etsy_categories` VALUES (2880,1586,'Toys & Games > Toys > Dolls & Action Figures > Animals','','toys_and_games.toys.dolls_and_action_figures.animals',2878,1);
INSERT INTO `ps_etsy_categories` VALUES (2881,1587,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing','','toys_and_games.toys.dolls_and_action_figures.doll_clothing',2878,0);
INSERT INTO `ps_etsy_categories` VALUES (2882,1588,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Coats','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.coats',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2883,1589,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Dresses','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.dresses',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2884,1590,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Hats','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.hats',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2885,1591,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Jackets','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.jackets',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2886,1592,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Jewelry','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.jewelry',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2887,1593,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Legwear','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.legwear',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2888,1594,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Pants','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.pants',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2889,1595,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Purses','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.purses',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2890,1596,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Scarves','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.scarves',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2891,1597,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Shirts','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.shirts',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2892,1598,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Shorts','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.shorts',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2893,1599,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Skirts','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.skirts',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2894,1600,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Sleepwear','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.sleepwear',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2895,1601,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Socks','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.socks',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2896,1602,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Sweaters','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.sweaters',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2897,1603,'Toys & Games > Toys > Dolls & Action Figures > Doll Clothing > Underwear','','toys_and_games.toys.dolls_and_action_figures.doll_clothing.underwear',2881,1);
INSERT INTO `ps_etsy_categories` VALUES (2898,1607,'Toys & Games > Toys > Dolls & Action Figures > Dolls','','toys_and_games.toys.dolls_and_action_figures.dolls',2878,0);
INSERT INTO `ps_etsy_categories` VALUES (2899,2930,'Toys & Games > Toys > Dolls & Action Figures > Dolls > Reborn Dolls','','toys_and_games.toys.dolls_and_action_figures.dolls.reborn_dolls',2898,1);
INSERT INTO `ps_etsy_categories` VALUES (2900,2897,'Toys & Games > Toys > Dolls & Action Figures > Toy Dollhouse Furniture','','toys_and_games.toys.dolls_and_action_figures.toy_dollhouse_furniture',2878,1);
INSERT INTO `ps_etsy_categories` VALUES (2901,2896,'Toys & Games > Toys > Dolls & Action Figures > Toy Dollhouses','','toys_and_games.toys.dolls_and_action_figures.toy_dollhouses',2878,1);
INSERT INTO `ps_etsy_categories` VALUES (2902,1608,'Toys & Games > Toys > Electronic Toys','','toys_and_games.toys.electronic_toys',2867,1);
INSERT INTO `ps_etsy_categories` VALUES (2903,1611,'Toys & Games > Toys > Learning & School','','toys_and_games.toys.learning_and_school',2867,1);
INSERT INTO `ps_etsy_categories` VALUES (2904,2898,'Toys & Games > Toys > Miniature Toys','','toys_and_games.toys.miniature_toys',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2905,2899,'Toys & Games > Toys > Miniature Toys > Miniature Food','','toys_and_games.toys.miniature_toys.miniature_food',2904,1);
INSERT INTO `ps_etsy_categories` VALUES (2906,2098,'Toys & Games > Toys > Miniature Toys > Role Playing Miniatures','','toys_and_games.toys.miniature_toys.role_playing_miniatures',2904,1);
INSERT INTO `ps_etsy_categories` VALUES (2907,1613,'Toys & Games > Toys > Play Tents & Playhouses','','toys_and_games.toys.play_tents_and_playhouses',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2908,1794,'Toys & Games > Toys > Play Tents & Playhouses > Play Tents','','toys_and_games.toys.play_tents_and_playhouses.play_tents',2907,1);
INSERT INTO `ps_etsy_categories` VALUES (2909,1795,'Toys & Games > Toys > Play Tents & Playhouses > Playhouses','','toys_and_games.toys.play_tents_and_playhouses.playhouses',2907,1);
INSERT INTO `ps_etsy_categories` VALUES (2910,1614,'Toys & Games > Toys > Pretend Play','','toys_and_games.toys.pretend_play',2867,1);
INSERT INTO `ps_etsy_categories` VALUES (2911,1615,'Toys & Games > Toys > Puppets','','toys_and_games.toys.puppets',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2912,1616,'Toys & Games > Toys > Puppets > Finger Puppets','','toys_and_games.toys.puppets.finger_puppets',2911,1);
INSERT INTO `ps_etsy_categories` VALUES (2913,1617,'Toys & Games > Toys > Puppets > Hand Puppets','','toys_and_games.toys.puppets.hand_puppets',2911,1);
INSERT INTO `ps_etsy_categories` VALUES (2914,1618,'Toys & Games > Toys > Puppets > Marionettes','','toys_and_games.toys.puppets.marionettes',2911,1);
INSERT INTO `ps_etsy_categories` VALUES (2915,1619,'Toys & Games > Toys > Puppets > Shadow Puppets','','toys_and_games.toys.puppets.shadow_puppets',2911,1);
INSERT INTO `ps_etsy_categories` VALUES (2916,1620,'Toys & Games > Toys > Push & Pull Toys','','toys_and_games.toys.push_and_pull_toys',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2917,1621,'Toys & Games > Toys > Push & Pull Toys > Animals','','toys_and_games.toys.push_and_pull_toys.animals',2916,1);
INSERT INTO `ps_etsy_categories` VALUES (2918,1622,'Toys & Games > Toys > Push & Pull Toys > Vehicles','','toys_and_games.toys.push_and_pull_toys.vehicles',2916,1);
INSERT INTO `ps_etsy_categories` VALUES (2919,1623,'Toys & Games > Toys > Push & Pull Toys > Wagons','','toys_and_games.toys.push_and_pull_toys.wagons',2916,1);
INSERT INTO `ps_etsy_categories` VALUES (2920,1624,'Toys & Games > Toys > Ride On & Rocking Toys','','toys_and_games.toys.ride_on_and_rocking_toys',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2921,1625,'Toys & Games > Toys > Ride On & Rocking Toys > Rocking & Spring Toys','','toys_and_games.toys.ride_on_and_rocking_toys.rocking_and_spring_toys',2920,1);
INSERT INTO `ps_etsy_categories` VALUES (2922,1626,'Toys & Games > Toys > Ride On & Rocking Toys > Scooters','','toys_and_games.toys.ride_on_and_rocking_toys.scooters',2920,1);
INSERT INTO `ps_etsy_categories` VALUES (2923,2403,'Toys & Games > Toys > Ride On & Rocking Toys > Stick Horses','','toys_and_games.toys.ride_on_and_rocking_toys.stick_horses',2920,1);
INSERT INTO `ps_etsy_categories` VALUES (2924,1627,'Toys & Games > Toys > Ride On & Rocking Toys > Vehicles','','toys_and_games.toys.ride_on_and_rocking_toys.vehicles',2920,1);
INSERT INTO `ps_etsy_categories` VALUES (2925,11252,'Toys & Games > Toys > Slime & Foam','','toys_and_games.toys.slime_and_foam',2867,1);
INSERT INTO `ps_etsy_categories` VALUES (2926,845,'Toys & Games > Toys > Stereoscopes & Viewfinders','','toys_and_games.toys.stereoscopes_and_viewfinders',2867,1);
INSERT INTO `ps_etsy_categories` VALUES (2927,1628,'Toys & Games > Toys > Stress Balls & Desk Toys','','toys_and_games.toys.stress_balls_and_desk_toys',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2928,2071,'Toys & Games > Toys > Stress Balls & Desk Toys > Bobbleheads','','toys_and_games.toys.stress_balls_and_desk_toys.bobbleheads',2927,1);
INSERT INTO `ps_etsy_categories` VALUES (2929,1793,'Toys & Games > Toys > Stress Balls & Desk Toys > Desk Toys','','toys_and_games.toys.stress_balls_and_desk_toys.desk_toys',2927,1);
INSERT INTO `ps_etsy_categories` VALUES (2930,10847,'Toys & Games > Toys > Stress Balls & Desk Toys > Fidget Spinners','','toys_and_games.toys.stress_balls_and_desk_toys.fidget_spinners',2927,1);
INSERT INTO `ps_etsy_categories` VALUES (2931,2907,'Toys & Games > Toys > Stress Balls & Desk Toys > Kaleidoscopes','','toys_and_games.toys.stress_balls_and_desk_toys.kaleidoscopes',2927,1);
INSERT INTO `ps_etsy_categories` VALUES (2932,1792,'Toys & Games > Toys > Stress Balls & Desk Toys > Stress Balls','','toys_and_games.toys.stress_balls_and_desk_toys.stress_balls',2927,1);
INSERT INTO `ps_etsy_categories` VALUES (2933,1629,'Toys & Games > Toys > Stuffed Animals & Plushies','','toys_and_games.toys.stuffed_animals_and_plushies',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2934,2072,'Toys & Games > Toys > Stuffed Animals & Plushies > Amigurumi','','toys_and_games.toys.stuffed_animals_and_plushies.amigurumi',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2935,2397,'Toys & Games > Toys > Stuffed Animals & Plushies > Bears','','toys_and_games.toys.stuffed_animals_and_plushies.bears',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2936,2919,'Toys & Games > Toys > Stuffed Animals & Plushies > Bunny Rabbits','','toys_and_games.toys.stuffed_animals_and_plushies.bunny_rabbits',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2937,2399,'Toys & Games > Toys > Stuffed Animals & Plushies > Cats','','toys_and_games.toys.stuffed_animals_and_plushies.cats',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2938,6067,'Toys & Games > Toys > Stuffed Animals & Plushies > Dogs','','toys_and_games.toys.stuffed_animals_and_plushies.dogs',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2939,2398,'Toys & Games > Toys > Stuffed Animals & Plushies > Fish & Aquatic','','toys_and_games.toys.stuffed_animals_and_plushies.fish_and_aquatic',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2940,2395,'Toys & Games > Toys > Stuffed Animals & Plushies > Horses','','toys_and_games.toys.stuffed_animals_and_plushies.horses',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2941,2401,'Toys & Games > Toys > Stuffed Animals & Plushies > Monkeys','','toys_and_games.toys.stuffed_animals_and_plushies.monkeys',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2942,2402,'Toys & Games > Toys > Stuffed Animals & Plushies > Monsters','','toys_and_games.toys.stuffed_animals_and_plushies.monsters',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2943,2400,'Toys & Games > Toys > Stuffed Animals & Plushies > People','','toys_and_games.toys.stuffed_animals_and_plushies.people',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2944,2920,'Toys & Games > Toys > Stuffed Animals & Plushies > Robots & Machines','','toys_and_games.toys.stuffed_animals_and_plushies.robots_and_machines',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2945,2396,'Toys & Games > Toys > Stuffed Animals & Plushies > Unicorns','','toys_and_games.toys.stuffed_animals_and_plushies.unicorns',2933,1);
INSERT INTO `ps_etsy_categories` VALUES (2946,1630,'Toys & Games > Toys > Toy Instruments','','toys_and_games.toys.toy_instruments',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2947,2409,'Toys & Games > Toys > Toy Instruments > Accordions','','toys_and_games.toys.toy_instruments.accordions',2946,1);
INSERT INTO `ps_etsy_categories` VALUES (2948,2410,'Toys & Games > Toys > Toy Instruments > Drums & Percussion','','toys_and_games.toys.toy_instruments.drums_and_percussion',2946,0);
INSERT INTO `ps_etsy_categories` VALUES (2949,2908,'Toys & Games > Toys > Toy Instruments > Drums & Percussion > Tambourines','','toys_and_games.toys.toy_instruments.drums_and_percussion.tambourines',2948,1);
INSERT INTO `ps_etsy_categories` VALUES (2950,2408,'Toys & Games > Toys > Toy Instruments > Pianos & Keyboards','','toys_and_games.toys.toy_instruments.pianos_and_keyboards',2946,1);
INSERT INTO `ps_etsy_categories` VALUES (2951,2411,'Toys & Games > Toys > Toy Instruments > Wind & Reed Instruments','','toys_and_games.toys.toy_instruments.wind_and_reed_instruments',2946,1);
INSERT INTO `ps_etsy_categories` VALUES (2952,1631,'Toys & Games > Toys > Wind-Up Toys','','toys_and_games.toys.wind_up_toys',2867,1);
INSERT INTO `ps_etsy_categories` VALUES (2953,1632,'Toys & Games > Toys > Yo-Yos & Tops','','toys_and_games.toys.yo_yos_and_tops',2867,0);
INSERT INTO `ps_etsy_categories` VALUES (2954,1797,'Toys & Games > Toys > Yo-Yos & Tops > Tops','','toys_and_games.toys.yo_yos_and_tops.tops',2953,1);
INSERT INTO `ps_etsy_categories` VALUES (2955,1796,'Toys & Games > Toys > Yo-Yos & Tops > Yo-Yos','','toys_and_games.toys.yo_yos_and_tops.yo_yos',2953,1);
INSERT INTO `ps_etsy_categories` VALUES (2956,1633,'Weddings','','weddings',0,0);
INSERT INTO `ps_etsy_categories` VALUES (2957,1634,'Weddings > Accessories','','weddings.accessories',2956,0);
INSERT INTO `ps_etsy_categories` VALUES (2958,1635,'Weddings > Accessories > Bags & Purses','','weddings.accessories.bags_and_purses',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2959,6095,'Weddings > Accessories > Belts & Sashes','','weddings.accessories.belts_and_sashes',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2960,10784,'Weddings > Accessories > Bouquets & Corsages','','weddings.accessories.bouquets_and_corsages',2957,0);
INSERT INTO `ps_etsy_categories` VALUES (2961,1659,'Weddings > Accessories > Bouquets & Corsages > Bouquets','','weddings.accessories.bouquets_and_corsages.bouquets',2960,1);
INSERT INTO `ps_etsy_categories` VALUES (2962,11253,'Weddings > Accessories > Bouquets & Corsages > Boutonnières','','weddings.accessories.bouquets_and_corsages.boutonnieres',2960,1);
INSERT INTO `ps_etsy_categories` VALUES (2963,10785,'Weddings > Accessories > Bouquets & Corsages > Corsages','','weddings.accessories.bouquets_and_corsages.corsages',2960,1);
INSERT INTO `ps_etsy_categories` VALUES (2964,1636,'Weddings > Accessories > Cover Ups & Scarves','','weddings.accessories.cover_ups_and_scarves',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2965,1637,'Weddings > Accessories > Cummerbunds','','weddings.accessories.cummerbunds',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2966,1638,'Weddings > Accessories > Hair Accessories','','weddings.accessories.hair_accessories',2957,0);
INSERT INTO `ps_etsy_categories` VALUES (2967,1639,'Weddings > Accessories > Hair Accessories > Barrettes & Clips','','weddings.accessories.hair_accessories.barrettes_and_clips',2966,1);
INSERT INTO `ps_etsy_categories` VALUES (2968,1640,'Weddings > Accessories > Hair Accessories > Decorative Combs','','weddings.accessories.hair_accessories.decorative_combs',2966,1);
INSERT INTO `ps_etsy_categories` VALUES (2969,1757,'Weddings > Accessories > Hair Accessories > Fascinators & Mini Hats','','weddings.accessories.hair_accessories.fascinators_and_mini_hats',2966,1);
INSERT INTO `ps_etsy_categories` VALUES (2970,1641,'Weddings > Accessories > Hair Accessories > Hair Jewelry','','weddings.accessories.hair_accessories.hair_jewelry',2966,1);
INSERT INTO `ps_etsy_categories` VALUES (2971,6115,'Weddings > Accessories > Hair Accessories > Hair Picks','','weddings.accessories.hair_accessories.hair_picks',2966,1);
INSERT INTO `ps_etsy_categories` VALUES (2972,1642,'Weddings > Accessories > Hair Accessories > Hair Pins','','weddings.accessories.hair_accessories.hair_pins',2966,1);
INSERT INTO `ps_etsy_categories` VALUES (2973,1645,'Weddings > Accessories > Hair Accessories > Wreaths & Tiaras','','weddings.accessories.hair_accessories.wreaths_and_tiaras',2966,0);
INSERT INTO `ps_etsy_categories` VALUES (2974,1803,'Weddings > Accessories > Hair Accessories > Wreaths & Tiaras > Tiaras','','weddings.accessories.hair_accessories.wreaths_and_tiaras.tiaras',2973,1);
INSERT INTO `ps_etsy_categories` VALUES (2975,1802,'Weddings > Accessories > Hair Accessories > Wreaths & Tiaras > Wreaths','','weddings.accessories.hair_accessories.wreaths_and_tiaras.wreaths',2973,1);
INSERT INTO `ps_etsy_categories` VALUES (2976,1646,'Weddings > Accessories > Hats','','weddings.accessories.hats',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2977,1649,'Weddings > Accessories > Neckties','','weddings.accessories.neckties',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2978,2905,'Weddings > Accessories > Shawls & Wraps','','weddings.accessories.wraps_and_shawls',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2979,1648,'Weddings > Accessories > Something Blue','','weddings.accessories.something_blue',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2980,1650,'Weddings > Accessories > Umbrellas','','weddings.accessories.umbrellas',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2981,1651,'Weddings > Accessories > Veils','','weddings.accessories.veils',2957,1);
INSERT INTO `ps_etsy_categories` VALUES (2982,1652,'Weddings > Clothing','','weddings.clothing',2956,0);
INSERT INTO `ps_etsy_categories` VALUES (2983,1653,'Weddings > Clothing > Dresses','','weddings.clothing.dresses',2982,0);
INSERT INTO `ps_etsy_categories` VALUES (2984,1656,'Weddings > Clothing > Dresses > Bridal Gowns & Separates','','weddings.clothing.dresses.wedding_gowns_and_separates',2983,1);
INSERT INTO `ps_etsy_categories` VALUES (2985,2079,'Weddings > Clothing > Dresses > Bridesmaid Dresses','','weddings.clothing.dresses.bridesmaid_dresses',2983,1);
INSERT INTO `ps_etsy_categories` VALUES (2986,2080,'Weddings > Clothing > Dresses > Flower Girl Dresses','','weddings.clothing.dresses.flower_girl_dresses',2983,1);
INSERT INTO `ps_etsy_categories` VALUES (2987,2081,'Weddings > Clothing > Dresses > Mother of the Bride Dresses','','weddings.clothing.dresses.mother_of_the_bride_dresses',2983,1);
INSERT INTO `ps_etsy_categories` VALUES (2988,11220,'Weddings > Clothing > Jumpsuits & Rompers','','weddings.clothing.jumpsuits_and_rompers',2982,1);
INSERT INTO `ps_etsy_categories` VALUES (2989,1654,'Weddings > Clothing > Lingerie & Garters','','weddings.clothing.lingerie_and_garters',2982,0);
INSERT INTO `ps_etsy_categories` VALUES (2990,1801,'Weddings > Clothing > Lingerie & Garters > Wedding Garters','','weddings.clothing.lingerie_and_garters.wedding_garters',2989,1);
INSERT INTO `ps_etsy_categories` VALUES (2991,1800,'Weddings > Clothing > Lingerie & Garters > Wedding Lingerie','','weddings.clothing.lingerie_and_garters.wedding_lingerie',2989,1);
INSERT INTO `ps_etsy_categories` VALUES (2992,2903,'Weddings > Clothing > Shrugs & Boleros','','weddings.clothing.shrugs_and_boleros',2982,1);
INSERT INTO `ps_etsy_categories` VALUES (2993,1655,'Weddings > Clothing > Suits','','weddings.clothing.suits',2982,0);
INSERT INTO `ps_etsy_categories` VALUES (2994,2119,'Weddings > Clothing > Suits > Boys\' Suits','','weddings.clothing.suits.boys_suits',2993,1);
INSERT INTO `ps_etsy_categories` VALUES (2995,2120,'Weddings > Clothing > Suits > Girls\' Suits','','weddings.clothing.suits.girls_suits',2993,1);
INSERT INTO `ps_etsy_categories` VALUES (2996,2117,'Weddings > Clothing > Suits > Men\'s Suits','','weddings.clothing.suits.mens_suits',2993,1);
INSERT INTO `ps_etsy_categories` VALUES (2997,2118,'Weddings > Clothing > Suits > Women\'s Suits','','weddings.clothing.suits.womens_suits',2993,1);
INSERT INTO `ps_etsy_categories` VALUES (2998,1657,'Weddings > Decorations','','weddings.decorations',2956,0);
INSERT INTO `ps_etsy_categories` VALUES (2999,6057,'Weddings > Decorations > Aisle Runners & Décor','','weddings.decorations.aisle_runners_and_decor',2998,1);
INSERT INTO `ps_etsy_categories` VALUES (3000,1658,'Weddings > Decorations > Baskets & Boxes','','weddings.decorations.baskets_and_boxes',2998,0);
INSERT INTO `ps_etsy_categories` VALUES (3001,1785,'Weddings > Decorations > Baskets & Boxes > Baskets','','weddings.decorations.baskets_and_boxes.baskets',3000,1);
INSERT INTO `ps_etsy_categories` VALUES (3002,1786,'Weddings > Decorations > Baskets & Boxes > Boxes & Containers','','weddings.decorations.baskets_and_boxes.boxes_and_containers',3000,1);
INSERT INTO `ps_etsy_categories` VALUES (3003,6116,'Weddings > Decorations > Baskets & Boxes > Flower Girl Baskets','','weddings.decorations.baskets_and_boxes.flower_girl_baskets',3000,1);
INSERT INTO `ps_etsy_categories` VALUES (3004,1660,'Weddings > Decorations > Cake Toppers','','weddings.decorations.cake_toppers',2998,1);
INSERT INTO `ps_etsy_categories` VALUES (3005,1661,'Weddings > Decorations > Candles & Holders','','weddings.decorations.candles_and_holders',2998,0);
INSERT INTO `ps_etsy_categories` VALUES (3006,1788,'Weddings > Decorations > Candles & Holders > Candle Holders','','weddings.decorations.candles_and_holders.candle_holders',3005,0);
INSERT INTO `ps_etsy_categories` VALUES (3007,6094,'Weddings > Decorations > Candles & Holders > Candle Holders > Unity Candle Holders','','weddings.decorations.candles_and_holders.candle_holders.unity_candle_holders',3006,1);
INSERT INTO `ps_etsy_categories` VALUES (3008,1787,'Weddings > Decorations > Candles & Holders > Candles','','weddings.decorations.candles_and_holders.candles',3005,0);
INSERT INTO `ps_etsy_categories` VALUES (3009,6093,'Weddings > Decorations > Candles & Holders > Candles > Unity Candles','','weddings.decorations.candles_and_holders.candles.unity_candles',3008,1);
INSERT INTO `ps_etsy_categories` VALUES (3010,1662,'Weddings > Decorations > Centerpieces','','weddings.decorations.centerpieces',2998,1);
INSERT INTO `ps_etsy_categories` VALUES (3011,6123,'Weddings > Decorations > Paper Fans','','weddings.decorations.paper_fans',2998,1);
INSERT INTO `ps_etsy_categories` VALUES (3012,1663,'Weddings > Decorations > Plants','','weddings.decorations.plants',2998,1);
INSERT INTO `ps_etsy_categories` VALUES (3013,2828,'Weddings > Decorations > Ring Bearer Pillows','','weddings.decorations.ring_bearer_pillows',2998,1);
INSERT INTO `ps_etsy_categories` VALUES (3014,2819,'Weddings > Decorations > Serving & Dining','','weddings.decorations.serving_and_dining',2998,0);
INSERT INTO `ps_etsy_categories` VALUES (3015,2821,'Weddings > Decorations > Serving & Dining > Cake Servers & Knives','','weddings.decorations.serving_and_dining.cake_servers_and_knives',3014,1);
INSERT INTO `ps_etsy_categories` VALUES (3016,2855,'Weddings > Decorations > Serving & Dining > Table Décor','','weddings.decorations.serving_and_dining.table_decor',3014,0);
INSERT INTO `ps_etsy_categories` VALUES (3017,2928,'Weddings > Decorations > Serving & Dining > Table Décor > Table Numbers','','weddings.decorations.serving_and_dining.table_decor.table_numbers',3016,1);
INSERT INTO `ps_etsy_categories` VALUES (3018,2820,'Weddings > Decorations > Serving & Dining > Wedding Forks','','weddings.decorations.serving_and_dining.wedding_forks',3014,1);
INSERT INTO `ps_etsy_categories` VALUES (3019,6121,'Weddings > Decorations > Signs','','weddings.decorations.signs',2998,1);
INSERT INTO `ps_etsy_categories` VALUES (3020,1664,'Weddings > Gifts & Mementos','','weddings.gifts_and_mementos',2956,0);
INSERT INTO `ps_etsy_categories` VALUES (3021,1665,'Weddings > Gifts & Mementos > Albums & Scrapbooks','','weddings.gifts_and_mementos.albums_and_scrapbooks',3020,1);
INSERT INTO `ps_etsy_categories` VALUES (3022,1666,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts','','weddings.gifts_and_mementos.bridesmaids_gifts',3020,0);
INSERT INTO `ps_etsy_categories` VALUES (3023,11254,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Accessories','','weddings.gifts_and_mementos.bridesmaids_gifts.accessories',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3024,11255,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Bags & Purses','','weddings.gifts_and_mementos.bridesmaids_gifts.bags_and_purses',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3025,11256,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Bath & Beauty','','weddings.gifts_and_mementos.bridesmaids_gifts.bath_and_beauty',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3026,11261,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Bridesmaid Home Décor','','weddings.gifts_and_mementos.bridesmaids_gifts.home_decor',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3027,11257,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Bridesmaid Proposals','','weddings.gifts_and_mementos.bridesmaids_gifts.bridesmaid_proposals',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3028,11258,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Clothing','','weddings.gifts_and_mementos.bridesmaids_gifts.clothing',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3029,11260,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Drink & Barware','','weddings.gifts_and_mementos.bridesmaids_gifts.drink_and_barware',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3030,11259,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Hangover & Survival Kits','','weddings.gifts_and_mementos.bridesmaids_gifts.hangover_and_survival_kits',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3031,11262,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Jewelry','','weddings.gifts_and_mementos.bridesmaids_gifts.jewelry',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3032,11263,'Weddings > Gifts & Mementos > Bridesmaids\' Gifts > Shoes','','weddings.gifts_and_mementos.bridesmaids_gifts.shoes',3022,1);
INSERT INTO `ps_etsy_categories` VALUES (3033,1667,'Weddings > Gifts & Mementos > Gifts For The Couple','','weddings.gifts_and_mementos.gifts_for_the_couple',3020,1);
INSERT INTO `ps_etsy_categories` VALUES (3034,1668,'Weddings > Gifts & Mementos > Groomsmen Gifts','','weddings.gifts_and_mementos.groomsmen_gifts',3020,0);
INSERT INTO `ps_etsy_categories` VALUES (3035,11264,'Weddings > Gifts & Mementos > Groomsmen Gifts > Accessories','','weddings.gifts_and_mementos.groomsmen_gifts.accessories',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3036,11265,'Weddings > Gifts & Mementos > Groomsmen Gifts > Bags & Luggage','','weddings.gifts_and_mementos.groomsmen_gifts.bags_and_luggage',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3037,11266,'Weddings > Gifts & Mementos > Groomsmen Gifts > Cigar & Cigarette Accessories','','weddings.gifts_and_mementos.groomsmen_gifts.cigar_and_cigarette_accessories',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3038,11267,'Weddings > Gifts & Mementos > Groomsmen Gifts > Clothing','','weddings.gifts_and_mementos.groomsmen_gifts.clothing',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3039,11268,'Weddings > Gifts & Mementos > Groomsmen Gifts > Drink & Barware','','weddings.gifts_and_mementos.groomsmen_gifts.drink_and_barware',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3040,11271,'Weddings > Gifts & Mementos > Groomsmen Gifts > Groomsmen Home Décor','','weddings.gifts_and_mementos.groomsmen_gifts.home_decor',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3041,11269,'Weddings > Gifts & Mementos > Groomsmen Gifts > Groomsmen Proposals','','weddings.gifts_and_mementos.groomsmen_gifts.groomsmen_proposals',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3042,11273,'Weddings > Gifts & Mementos > Groomsmen Gifts > Groomsmen Sports Equipment','','weddings.gifts_and_mementos.groomsmen_gifts.sports_equipment',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3043,11270,'Weddings > Gifts & Mementos > Groomsmen Gifts > Hangover & Survival Kits','','weddings.gifts_and_mementos.groomsmen_gifts.hangover_and_survival_kits',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3044,11272,'Weddings > Gifts & Mementos > Groomsmen Gifts > Jewelry','','weddings.gifts_and_mementos.groomsmen_gifts.jewelry',3034,1);
INSERT INTO `ps_etsy_categories` VALUES (3045,1669,'Weddings > Gifts & Mementos > Guest Books','','weddings.gifts_and_mementos.guest_books',3020,1);
INSERT INTO `ps_etsy_categories` VALUES (3046,1670,'Weddings > Gifts & Mementos > Portraits & Frames','','weddings.gifts_and_mementos.portraits_and_frames',3020,0);
INSERT INTO `ps_etsy_categories` VALUES (3047,1784,'Weddings > Gifts & Mementos > Portraits & Frames > Picture Frames','','weddings.gifts_and_mementos.portraits_and_frames.picture_frames',3046,1);
INSERT INTO `ps_etsy_categories` VALUES (3048,1783,'Weddings > Gifts & Mementos > Portraits & Frames > Portraits','','weddings.gifts_and_mementos.portraits_and_frames.portraits',3046,1);
INSERT INTO `ps_etsy_categories` VALUES (3049,6055,'Weddings > Gifts & Mementos > Wedding Dress Hangers','','weddings.gifts_and_mementos.wedding_dress_hangers',3020,1);
INSERT INTO `ps_etsy_categories` VALUES (3050,1671,'Weddings > Gifts & Mementos > Wedding Favors','','weddings.gifts_and_mementos.wedding_favors',3020,1);
INSERT INTO `ps_etsy_categories` VALUES (3051,1672,'Weddings > Invitations & Paper','','weddings.invitations_and_paper',2956,0);
INSERT INTO `ps_etsy_categories` VALUES (3052,1673,'Weddings > Invitations & Paper > Announcements','','weddings.invitations_and_paper.announcements',3051,1);
INSERT INTO `ps_etsy_categories` VALUES (3053,1674,'Weddings > Invitations & Paper > Greeting Cards','','weddings.invitations_and_paper.greeting_cards',3051,1);
INSERT INTO `ps_etsy_categories` VALUES (3054,1675,'Weddings > Invitations & Paper > Invitation Kits','','weddings.invitations_and_paper.invitation_kits',3051,1);
INSERT INTO `ps_etsy_categories` VALUES (3055,1676,'Weddings > Invitations & Paper > Invitations','','weddings.invitations_and_paper.invitations',3051,1);
INSERT INTO `ps_etsy_categories` VALUES (3056,6056,'Weddings > Invitations & Paper > Place Cards','','weddings.invitations_and_paper.place_cards',3051,1);
INSERT INTO `ps_etsy_categories` VALUES (3057,1677,'Weddings > Invitations & Paper > Save The Dates','','weddings.invitations_and_paper.save_the_dates',3051,1);
INSERT INTO `ps_etsy_categories` VALUES (3058,1678,'Weddings > Invitations & Paper > Templates','','weddings.invitations_and_paper.templates',3051,1);
INSERT INTO `ps_etsy_categories` VALUES (3059,1679,'Weddings > Invitations & Paper > Thank You Cards','','weddings.invitations_and_paper.thank_you_cards',3051,1);
INSERT INTO `ps_etsy_categories` VALUES (3060,1680,'Weddings > Jewelry','','weddings.jewelry',2956,0);
INSERT INTO `ps_etsy_categories` VALUES (3061,1681,'Weddings > Jewelry > Bracelets','','weddings.jewelry.bracelets',3060,1);
INSERT INTO `ps_etsy_categories` VALUES (3062,1682,'Weddings > Jewelry > Brooches','','weddings.jewelry.brooches',3060,1);
INSERT INTO `ps_etsy_categories` VALUES (3063,1683,'Weddings > Jewelry > Cuff Links & Tie Clips','','weddings.jewelry.cuff_links_and_tie_tacks',3060,0);
INSERT INTO `ps_etsy_categories` VALUES (3064,1781,'Weddings > Jewelry > Cuff Links & Tie Clips > Cuff Links','','weddings.jewelry.cuff_links_and_tie_tacks.cuff_links',3063,1);
INSERT INTO `ps_etsy_categories` VALUES (3065,2316,'Weddings > Jewelry > Cuff Links & Tie Clips > Shirt Studs','','weddings.jewelry.cuff_links_and_tie_tacks.shirt_studs',3063,1);
INSERT INTO `ps_etsy_categories` VALUES (3066,1782,'Weddings > Jewelry > Cuff Links & Tie Clips > Tie Clips & Tacks','','weddings.jewelry.cuff_links_and_tie_tacks.tie_tacks',3063,1);
INSERT INTO `ps_etsy_categories` VALUES (3067,1684,'Weddings > Jewelry > Earrings','','weddings.jewelry.earrings',3060,1);
INSERT INTO `ps_etsy_categories` VALUES (3068,1685,'Weddings > Jewelry > Jewelry Sets','','weddings.jewelry.jewelry_sets',3060,1);
INSERT INTO `ps_etsy_categories` VALUES (3069,1686,'Weddings > Jewelry > Necklaces','','weddings.jewelry.necklaces',3060,1);
INSERT INTO `ps_etsy_categories` VALUES (3070,1687,'Weddings > Jewelry > Pendants','','weddings.jewelry.pendants',3060,1);
INSERT INTO `ps_etsy_categories` VALUES (3071,1696,'Weddings > Shoes','','weddings.shoes',2956,0);
INSERT INTO `ps_etsy_categories` VALUES (3072,2121,'Weddings > Shoes > Boys\' Wedding Shoes','','weddings.shoes.boys_wedding_shoes',3071,1);
INSERT INTO `ps_etsy_categories` VALUES (3073,1791,'Weddings > Shoes > Girls\' Wedding Shoes','','weddings.shoes.girls_wedding_shoes',3071,1);
INSERT INTO `ps_etsy_categories` VALUES (3074,1789,'Weddings > Shoes > Men\'s Wedding Shoes','','weddings.shoes.mens_wedding_shoes',3071,1);
INSERT INTO `ps_etsy_categories` VALUES (3075,1790,'Weddings > Shoes > Women\'s Wedding Shoes','','weddings.shoes.womens_wedding_shoes',3071,1);

/*!40000 ALTER TABLE `ps_etsy_categories` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_profile_category`, `id_etsy_profiles`, `etsy_category_code`, `prestashop_category`, `date_add`, `date_upd` FROM `ps_etsy_category_mapping`;
    
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

DROP TABLE IF EXISTS `ps_etsy_category_mapping`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_category_mapping` (
  `id_profile_category` int(10) NOT NULL AUTO_INCREMENT,
  `id_etsy_profiles` int(10) NOT NULL,
  `etsy_category_code` text,
  `prestashop_category` text,
  `date_add` datetime NOT NULL,
  `date_upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_profile_category`),
  KEY `id_etsy_profiles` (`id_etsy_profiles`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_category_mapping` WRITE;
/*!40000 ALTER TABLE `ps_etsy_category_mapping` DISABLE KEYS */;
INSERT INTO `ps_etsy_category_mapping` VALUES (1,1,'1','34,54,271,55,269,270,272','2021-02-08 13:43:42','2021-02-08 13:43:42');
INSERT INTO `ps_etsy_category_mapping` VALUES (4,4,'374','56,259,273','2021-03-07 20:56:18','2021-03-15 08:18:35');
INSERT INTO `ps_etsy_category_mapping` VALUES (5,5,'6241','73,280','2021-03-07 20:57:11','2021-03-15 08:20:17');
INSERT INTO `ps_etsy_category_mapping` VALUES (6,6,'6221','18','2021-03-07 21:03:59','2021-03-07 21:03:59');
INSERT INTO `ps_etsy_category_mapping` VALUES (9,9,'1','301','2021-03-09 16:08:30','2021-03-09 16:08:30');

/*!40000 ALTER TABLE `ps_etsy_category_mapping` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_etsy_countries`, `country_id`, `country_name`, `iso_code` FROM `ps_etsy_countries`;
    
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

DROP TABLE IF EXISTS `ps_etsy_countries`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_countries` (
  `id_etsy_countries` int(10) NOT NULL AUTO_INCREMENT,
  `country_id` int(10) NOT NULL,
  `country_name` varchar(255) NOT NULL,
  `iso_code` varchar(3) NOT NULL,
  PRIMARY KEY (`id_etsy_countries`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_countries` WRITE;
/*!40000 ALTER TABLE `ps_etsy_countries` DISABLE KEYS */;
INSERT INTO `ps_etsy_countries` VALUES (1,55,'Afghanistan','AF');
INSERT INTO `ps_etsy_countries` VALUES (2,306,'Åland Islands','AX');
INSERT INTO `ps_etsy_countries` VALUES (3,57,'Albania','AL');
INSERT INTO `ps_etsy_countries` VALUES (4,95,'Algeria','DZ');
INSERT INTO `ps_etsy_countries` VALUES (5,250,'American Samoa','AS');
INSERT INTO `ps_etsy_countries` VALUES (6,228,'Andorra','AD');
INSERT INTO `ps_etsy_countries` VALUES (7,56,'Angola','AO');
INSERT INTO `ps_etsy_countries` VALUES (8,251,'Anguilla','AI');
INSERT INTO `ps_etsy_countries` VALUES (9,10,'Antarctica','AQ');
INSERT INTO `ps_etsy_countries` VALUES (10,252,'Antigua and Barbuda','AG');
INSERT INTO `ps_etsy_countries` VALUES (11,59,'Argentina','AR');
INSERT INTO `ps_etsy_countries` VALUES (12,60,'Armenia','AM');
INSERT INTO `ps_etsy_countries` VALUES (13,253,'Aruba','AW');
INSERT INTO `ps_etsy_countries` VALUES (14,61,'Australia','AU');
INSERT INTO `ps_etsy_countries` VALUES (15,62,'Austria','AT');
INSERT INTO `ps_etsy_countries` VALUES (16,63,'Azerbaijan','AZ');
INSERT INTO `ps_etsy_countries` VALUES (17,229,'Bahamas','BS');
INSERT INTO `ps_etsy_countries` VALUES (18,232,'Bahrain','BH');
INSERT INTO `ps_etsy_countries` VALUES (19,68,'Bangladesh','BD');
INSERT INTO `ps_etsy_countries` VALUES (20,237,'Barbados','BB');
INSERT INTO `ps_etsy_countries` VALUES (21,71,'Belarus','BY');
INSERT INTO `ps_etsy_countries` VALUES (22,65,'Belgium','BE');
INSERT INTO `ps_etsy_countries` VALUES (23,72,'Belize','BZ');
INSERT INTO `ps_etsy_countries` VALUES (24,66,'Benin','BJ');
INSERT INTO `ps_etsy_countries` VALUES (25,225,'Bermuda','BM');
INSERT INTO `ps_etsy_countries` VALUES (26,76,'Bhutan','BT');
INSERT INTO `ps_etsy_countries` VALUES (27,73,'Bolivia','BO');
INSERT INTO `ps_etsy_countries` VALUES (28,535,'Bonaire, Sint Eustatius and Saba','BQ');
INSERT INTO `ps_etsy_countries` VALUES (29,70,'Bosnia and Herzegovina','BA');
INSERT INTO `ps_etsy_countries` VALUES (30,77,'Botswana','BW');
INSERT INTO `ps_etsy_countries` VALUES (31,254,'Bouvet Island','BV');
INSERT INTO `ps_etsy_countries` VALUES (32,74,'Brazil','BR');
INSERT INTO `ps_etsy_countries` VALUES (33,255,'British Indian Ocean Territory','IO');
INSERT INTO `ps_etsy_countries` VALUES (34,231,'British Virgin Islands','VG');
INSERT INTO `ps_etsy_countries` VALUES (35,75,'Brunei','BN');
INSERT INTO `ps_etsy_countries` VALUES (36,69,'Bulgaria','BG');
INSERT INTO `ps_etsy_countries` VALUES (37,67,'Burkina Faso','BF');
INSERT INTO `ps_etsy_countries` VALUES (38,64,'Burundi','BI');
INSERT INTO `ps_etsy_countries` VALUES (39,135,'Cambodia','KH');
INSERT INTO `ps_etsy_countries` VALUES (40,84,'Cameroon','CM');
INSERT INTO `ps_etsy_countries` VALUES (41,79,'Canada','CA');
INSERT INTO `ps_etsy_countries` VALUES (42,222,'Cape Verde','CV');
INSERT INTO `ps_etsy_countries` VALUES (43,247,'Cayman Islands','KY');
INSERT INTO `ps_etsy_countries` VALUES (44,78,'Central African Republic','CF');
INSERT INTO `ps_etsy_countries` VALUES (45,196,'Chad','TD');
INSERT INTO `ps_etsy_countries` VALUES (46,81,'Chile','CL');
INSERT INTO `ps_etsy_countries` VALUES (47,82,'China','CN');
INSERT INTO `ps_etsy_countries` VALUES (48,257,'Christmas Island','CX');
INSERT INTO `ps_etsy_countries` VALUES (49,258,'Cocos (Keeling) Islands','CC');
INSERT INTO `ps_etsy_countries` VALUES (50,86,'Colombia','CO');
INSERT INTO `ps_etsy_countries` VALUES (51,259,'Comoros','KM');
INSERT INTO `ps_etsy_countries` VALUES (52,85,'Congo, Republic of','CG');
INSERT INTO `ps_etsy_countries` VALUES (53,260,'Cook Islands','CK');
INSERT INTO `ps_etsy_countries` VALUES (54,87,'Costa Rica','CR');
INSERT INTO `ps_etsy_countries` VALUES (55,118,'Croatia','HR');
INSERT INTO `ps_etsy_countries` VALUES (56,338,'Curaçao','CW');
INSERT INTO `ps_etsy_countries` VALUES (57,89,'Cyprus','CY');
INSERT INTO `ps_etsy_countries` VALUES (58,90,'Czech Republic','CZ');
INSERT INTO `ps_etsy_countries` VALUES (59,93,'Denmark','DK');
INSERT INTO `ps_etsy_countries` VALUES (60,92,'Djibouti','DJ');
INSERT INTO `ps_etsy_countries` VALUES (61,261,'Dominica','DM');
INSERT INTO `ps_etsy_countries` VALUES (62,94,'Dominican Republic','DO');
INSERT INTO `ps_etsy_countries` VALUES (63,96,'Ecuador','EC');
INSERT INTO `ps_etsy_countries` VALUES (64,97,'Egypt','EG');
INSERT INTO `ps_etsy_countries` VALUES (65,187,'El Salvador','SV');
INSERT INTO `ps_etsy_countries` VALUES (66,111,'Equatorial Guinea','GQ');
INSERT INTO `ps_etsy_countries` VALUES (67,98,'Eritrea','ER');
INSERT INTO `ps_etsy_countries` VALUES (68,100,'Estonia','EE');
INSERT INTO `ps_etsy_countries` VALUES (69,101,'Ethiopia','ET');
INSERT INTO `ps_etsy_countries` VALUES (70,262,'Falkland Islands (Malvinas)','FK');
INSERT INTO `ps_etsy_countries` VALUES (71,241,'Faroe Islands','FO');
INSERT INTO `ps_etsy_countries` VALUES (72,234,'Fiji','FJ');
INSERT INTO `ps_etsy_countries` VALUES (73,102,'Finland','FI');
INSERT INTO `ps_etsy_countries` VALUES (74,103,'France','FR');
INSERT INTO `ps_etsy_countries` VALUES (75,115,'French Guiana','GF');
INSERT INTO `ps_etsy_countries` VALUES (76,263,'French Polynesia','PF');
INSERT INTO `ps_etsy_countries` VALUES (77,264,'French Southern Territories','TF');
INSERT INTO `ps_etsy_countries` VALUES (78,104,'Gabon','GA');
INSERT INTO `ps_etsy_countries` VALUES (79,109,'Gambia','GM');
INSERT INTO `ps_etsy_countries` VALUES (80,106,'Georgia','GE');
INSERT INTO `ps_etsy_countries` VALUES (81,91,'Germany','DE');
INSERT INTO `ps_etsy_countries` VALUES (82,107,'Ghana','GH');
INSERT INTO `ps_etsy_countries` VALUES (83,226,'Gibraltar','GI');
INSERT INTO `ps_etsy_countries` VALUES (84,112,'Greece','GR');
INSERT INTO `ps_etsy_countries` VALUES (85,113,'Greenland','GL');
INSERT INTO `ps_etsy_countries` VALUES (86,245,'Grenada','GD');
INSERT INTO `ps_etsy_countries` VALUES (87,265,'Guadeloupe','GP');
INSERT INTO `ps_etsy_countries` VALUES (88,266,'Guam','GU');
INSERT INTO `ps_etsy_countries` VALUES (89,114,'Guatemala','GT');
INSERT INTO `ps_etsy_countries` VALUES (90,305,'Guernsey','GG');
INSERT INTO `ps_etsy_countries` VALUES (91,108,'Guinea','GN');
INSERT INTO `ps_etsy_countries` VALUES (92,110,'Guinea-Bissau','GW');
INSERT INTO `ps_etsy_countries` VALUES (93,116,'Guyana','GY');
INSERT INTO `ps_etsy_countries` VALUES (94,119,'Haiti','HT');
INSERT INTO `ps_etsy_countries` VALUES (95,267,'Heard Island and McDonald Islands','HM');
INSERT INTO `ps_etsy_countries` VALUES (96,268,'Holy See (Vatican City State)','VA');
INSERT INTO `ps_etsy_countries` VALUES (97,117,'Honduras','HN');
INSERT INTO `ps_etsy_countries` VALUES (98,219,'Hong Kong','HK');
INSERT INTO `ps_etsy_countries` VALUES (99,120,'Hungary','HU');
INSERT INTO `ps_etsy_countries` VALUES (100,126,'Iceland','IS');
INSERT INTO `ps_etsy_countries` VALUES (101,122,'India','IN');
INSERT INTO `ps_etsy_countries` VALUES (102,121,'Indonesia','ID');
INSERT INTO `ps_etsy_countries` VALUES (103,125,'Iraq','IQ');
INSERT INTO `ps_etsy_countries` VALUES (104,123,'Ireland','IE');
INSERT INTO `ps_etsy_countries` VALUES (105,269,'Isle of Man','IM');
INSERT INTO `ps_etsy_countries` VALUES (106,127,'Israel','IL');
INSERT INTO `ps_etsy_countries` VALUES (107,128,'Italy','IT');
INSERT INTO `ps_etsy_countries` VALUES (108,83,'Ivory Coast','IC');
INSERT INTO `ps_etsy_countries` VALUES (109,129,'Jamaica','JM');
INSERT INTO `ps_etsy_countries` VALUES (110,131,'Japan','JP');
INSERT INTO `ps_etsy_countries` VALUES (111,307,'Jersey','JE');
INSERT INTO `ps_etsy_countries` VALUES (112,130,'Jordan','JO');
INSERT INTO `ps_etsy_countries` VALUES (113,132,'Kazakhstan','KZ');
INSERT INTO `ps_etsy_countries` VALUES (114,133,'Kenya','KE');
INSERT INTO `ps_etsy_countries` VALUES (115,270,'Kiribati','KI');
INSERT INTO `ps_etsy_countries` VALUES (116,271,'Kosovo','KV');
INSERT INTO `ps_etsy_countries` VALUES (117,137,'Kuwait','KW');
INSERT INTO `ps_etsy_countries` VALUES (118,134,'Kyrgyzstan','KG');
INSERT INTO `ps_etsy_countries` VALUES (119,138,'Laos','LA');
INSERT INTO `ps_etsy_countries` VALUES (120,146,'Latvia','LV');
INSERT INTO `ps_etsy_countries` VALUES (121,139,'Lebanon','LB');
INSERT INTO `ps_etsy_countries` VALUES (122,143,'Lesotho','LS');
INSERT INTO `ps_etsy_countries` VALUES (123,140,'Liberia','LR');
INSERT INTO `ps_etsy_countries` VALUES (124,141,'Libya','LY');
INSERT INTO `ps_etsy_countries` VALUES (125,272,'Liechtenstein','LI');
INSERT INTO `ps_etsy_countries` VALUES (126,144,'Lithuania','LT');
INSERT INTO `ps_etsy_countries` VALUES (127,145,'Luxembourg','LU');
INSERT INTO `ps_etsy_countries` VALUES (128,273,'Macao','MO');
INSERT INTO `ps_etsy_countries` VALUES (129,151,'Macedonia','MK');
INSERT INTO `ps_etsy_countries` VALUES (130,149,'Madagascar','MG');
INSERT INTO `ps_etsy_countries` VALUES (131,158,'Malawi','MW');
INSERT INTO `ps_etsy_countries` VALUES (132,159,'Malaysia','MY');
INSERT INTO `ps_etsy_countries` VALUES (133,238,'Maldives','MV');
INSERT INTO `ps_etsy_countries` VALUES (134,152,'Mali','ML');
INSERT INTO `ps_etsy_countries` VALUES (135,227,'Malta','MT');
INSERT INTO `ps_etsy_countries` VALUES (136,274,'Marshall Islands','MH');
INSERT INTO `ps_etsy_countries` VALUES (137,275,'Martinique','MQ');
INSERT INTO `ps_etsy_countries` VALUES (138,157,'Mauritania','MR');
INSERT INTO `ps_etsy_countries` VALUES (139,239,'Mauritius','MU');
INSERT INTO `ps_etsy_countries` VALUES (140,276,'Mayotte','YT');
INSERT INTO `ps_etsy_countries` VALUES (141,150,'Mexico','MX');
INSERT INTO `ps_etsy_countries` VALUES (142,277,'Micronesia, Federated States of','FM');
INSERT INTO `ps_etsy_countries` VALUES (143,148,'Moldova','MD');
INSERT INTO `ps_etsy_countries` VALUES (144,278,'Monaco','MC');
INSERT INTO `ps_etsy_countries` VALUES (145,154,'Mongolia','MN');
INSERT INTO `ps_etsy_countries` VALUES (146,155,'Montenegro','ME');
INSERT INTO `ps_etsy_countries` VALUES (147,279,'Montserrat','MS');
INSERT INTO `ps_etsy_countries` VALUES (148,147,'Morocco','MA');
INSERT INTO `ps_etsy_countries` VALUES (149,156,'Mozambique','MZ');
INSERT INTO `ps_etsy_countries` VALUES (150,153,'Myanmar (Burma)','MM');
INSERT INTO `ps_etsy_countries` VALUES (151,160,'Namibia','NA');
INSERT INTO `ps_etsy_countries` VALUES (152,280,'Nauru','NR');
INSERT INTO `ps_etsy_countries` VALUES (153,166,'Nepal','NP');
INSERT INTO `ps_etsy_countries` VALUES (154,243,'Netherlands Antilles','AN');
INSERT INTO `ps_etsy_countries` VALUES (155,233,'New Caledonia','NC');
INSERT INTO `ps_etsy_countries` VALUES (156,167,'New Zealand','NZ');
INSERT INTO `ps_etsy_countries` VALUES (157,163,'Nicaragua','NI');
INSERT INTO `ps_etsy_countries` VALUES (158,161,'Niger','NE');
INSERT INTO `ps_etsy_countries` VALUES (159,162,'Nigeria','NG');
INSERT INTO `ps_etsy_countries` VALUES (160,281,'Niue','NU');
INSERT INTO `ps_etsy_countries` VALUES (161,282,'Norfolk Island','NF');
INSERT INTO `ps_etsy_countries` VALUES (162,283,'Northern Mariana Islands','MP');
INSERT INTO `ps_etsy_countries` VALUES (163,165,'Norway','NO');
INSERT INTO `ps_etsy_countries` VALUES (164,168,'Oman','OM');
INSERT INTO `ps_etsy_countries` VALUES (165,169,'Pakistan','PK');
INSERT INTO `ps_etsy_countries` VALUES (166,284,'Palau','PW');
INSERT INTO `ps_etsy_countries` VALUES (167,285,'Palestine, State of','PS');
INSERT INTO `ps_etsy_countries` VALUES (168,170,'Panama','PA');
INSERT INTO `ps_etsy_countries` VALUES (169,173,'Papua New Guinea','PG');
INSERT INTO `ps_etsy_countries` VALUES (170,178,'Paraguay','PY');
INSERT INTO `ps_etsy_countries` VALUES (171,171,'Peru','PE');
INSERT INTO `ps_etsy_countries` VALUES (172,172,'Philippines','PH');
INSERT INTO `ps_etsy_countries` VALUES (173,174,'Poland','PL');
INSERT INTO `ps_etsy_countries` VALUES (174,177,'Portugal','PT');
INSERT INTO `ps_etsy_countries` VALUES (175,175,'Puerto Rico','PR');
INSERT INTO `ps_etsy_countries` VALUES (176,179,'Qatar','QA');
INSERT INTO `ps_etsy_countries` VALUES (177,304,'Reunion','RE');
INSERT INTO `ps_etsy_countries` VALUES (178,180,'Romania','RO');
INSERT INTO `ps_etsy_countries` VALUES (179,181,'Russia','RU');
INSERT INTO `ps_etsy_countries` VALUES (180,182,'Rwanda','RW');
INSERT INTO `ps_etsy_countries` VALUES (181,308,'Saint Barthélemy','BL');
INSERT INTO `ps_etsy_countries` VALUES (182,286,'Saint Helena','SH');
INSERT INTO `ps_etsy_countries` VALUES (183,287,'Saint Kitts and Nevis','KN');
INSERT INTO `ps_etsy_countries` VALUES (184,244,'Saint Lucia','LC');
INSERT INTO `ps_etsy_countries` VALUES (185,288,'Saint Martin (French part)','MF');
INSERT INTO `ps_etsy_countries` VALUES (186,289,'Saint Pierre and Miquelon','PM');
INSERT INTO `ps_etsy_countries` VALUES (187,249,'Saint Vincent and the Grenadines','VC');
INSERT INTO `ps_etsy_countries` VALUES (188,290,'Samoa','WS');
INSERT INTO `ps_etsy_countries` VALUES (189,291,'San Marino','SM');
INSERT INTO `ps_etsy_countries` VALUES (190,292,'Sao Tome and Principe','ST');
INSERT INTO `ps_etsy_countries` VALUES (191,183,'Saudi Arabia','SA');
INSERT INTO `ps_etsy_countries` VALUES (192,185,'Senegal','SN');
INSERT INTO `ps_etsy_countries` VALUES (193,189,'Serbia','RS');
INSERT INTO `ps_etsy_countries` VALUES (194,891,'Serbia and Montenegro','CS');
INSERT INTO `ps_etsy_countries` VALUES (195,293,'Seychelles','SC');
INSERT INTO `ps_etsy_countries` VALUES (196,186,'Sierra Leone','SL');
INSERT INTO `ps_etsy_countries` VALUES (197,220,'Singapore','SG');
INSERT INTO `ps_etsy_countries` VALUES (198,337,'Sint Maarten (Dutch part)','SX');
INSERT INTO `ps_etsy_countries` VALUES (199,191,'Slovakia','SK');
INSERT INTO `ps_etsy_countries` VALUES (200,192,'Slovenia','SI');
INSERT INTO `ps_etsy_countries` VALUES (201,242,'Solomon Islands','SB');
INSERT INTO `ps_etsy_countries` VALUES (202,188,'Somalia','SO');
INSERT INTO `ps_etsy_countries` VALUES (203,215,'South Africa','ZA');
INSERT INTO `ps_etsy_countries` VALUES (204,294,'South Georgia and the South Sandwich Islands','GS');
INSERT INTO `ps_etsy_countries` VALUES (205,136,'South Korea','KR');
INSERT INTO `ps_etsy_countries` VALUES (206,339,'South Sudan','SS');
INSERT INTO `ps_etsy_countries` VALUES (207,99,'Spain','ES');
INSERT INTO `ps_etsy_countries` VALUES (208,142,'Sri Lanka','LK');
INSERT INTO `ps_etsy_countries` VALUES (209,184,'Sudan','SD');
INSERT INTO `ps_etsy_countries` VALUES (210,190,'Suriname','SR');
INSERT INTO `ps_etsy_countries` VALUES (211,295,'Svalbard and Jan Mayen','SJ');
INSERT INTO `ps_etsy_countries` VALUES (212,194,'Swaziland','SZ');
INSERT INTO `ps_etsy_countries` VALUES (213,193,'Sweden','SE');
INSERT INTO `ps_etsy_countries` VALUES (214,80,'Switzerland','CH');
INSERT INTO `ps_etsy_countries` VALUES (215,204,'Taiwan','TW');
INSERT INTO `ps_etsy_countries` VALUES (216,199,'Tajikistan','TJ');
INSERT INTO `ps_etsy_countries` VALUES (217,205,'Tanzania','TZ');
INSERT INTO `ps_etsy_countries` VALUES (218,198,'Thailand','TH');
INSERT INTO `ps_etsy_countries` VALUES (219,164,'The Netherlands','NL');
INSERT INTO `ps_etsy_countries` VALUES (220,296,'Timor-Leste','TL');
INSERT INTO `ps_etsy_countries` VALUES (221,197,'Togo','TG');
INSERT INTO `ps_etsy_countries` VALUES (222,297,'Tokelau','TK');
INSERT INTO `ps_etsy_countries` VALUES (223,298,'Tonga','TO');
INSERT INTO `ps_etsy_countries` VALUES (224,201,'Trinidad','TT');
INSERT INTO `ps_etsy_countries` VALUES (225,202,'Tunisia','TN');
INSERT INTO `ps_etsy_countries` VALUES (226,203,'Turkey','TR');
INSERT INTO `ps_etsy_countries` VALUES (227,200,'Turkmenistan','TM');
INSERT INTO `ps_etsy_countries` VALUES (228,299,'Turks and Caicos Islands','TC');
INSERT INTO `ps_etsy_countries` VALUES (229,300,'Tuvalu','TV');
INSERT INTO `ps_etsy_countries` VALUES (230,206,'Uganda','UG');
INSERT INTO `ps_etsy_countries` VALUES (231,207,'Ukraine','UA');
INSERT INTO `ps_etsy_countries` VALUES (232,58,'United Arab Emirates','AE');
INSERT INTO `ps_etsy_countries` VALUES (233,105,'United Kingdom','GB');
INSERT INTO `ps_etsy_countries` VALUES (234,209,'United States','US');
INSERT INTO `ps_etsy_countries` VALUES (235,302,'United States Minor Outlying Islands','UM');
INSERT INTO `ps_etsy_countries` VALUES (236,208,'Uruguay','UY');
INSERT INTO `ps_etsy_countries` VALUES (237,248,'U.S. Virgin Islands','VI');
INSERT INTO `ps_etsy_countries` VALUES (238,210,'Uzbekistan','UZ');
INSERT INTO `ps_etsy_countries` VALUES (239,221,'Vanuatu','VU');
INSERT INTO `ps_etsy_countries` VALUES (240,211,'Venezuela','VE');
INSERT INTO `ps_etsy_countries` VALUES (241,212,'Vietnam','VN');
INSERT INTO `ps_etsy_countries` VALUES (242,224,'Wallis and Futuna','WF');
INSERT INTO `ps_etsy_countries` VALUES (243,213,'Western Sahara','EH');
INSERT INTO `ps_etsy_countries` VALUES (244,214,'Yemen','YE');
INSERT INTO `ps_etsy_countries` VALUES (245,216,'Zaire (Democratic Republic of Congo)','CD');
INSERT INTO `ps_etsy_countries` VALUES (246,217,'Zambia','ZM');
INSERT INTO `ps_etsy_countries` VALUES (247,218,'Zimbabwe','ZW');

/*!40000 ALTER TABLE `ps_etsy_countries` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_exclude`, `id_product`, `id_profiles`, `id_shop` FROM `ps_etsy_exclude_product`;
    
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

DROP TABLE IF EXISTS `ps_etsy_exclude_product`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_exclude_product` (
  `id_exclude` int(14) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(14) DEFAULT NULL,
  `id_profiles` int(14) DEFAULT NULL,
  `id_shop` int(10) DEFAULT NULL,
  PRIMARY KEY (`id_exclude`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_exclude_product` WRITE;
/*!40000 ALTER TABLE `ps_etsy_exclude_product` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_etsy_exclude_product` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_etsy_orders_list`, `id_order`, `id_etsy_order`, `is_status_updated`, `is_tracking_updated`, `date_added`, `date_updated` FROM `ps_etsy_orders_list`;
    
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

DROP TABLE IF EXISTS `ps_etsy_orders_list`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_orders_list` (
  `id_etsy_orders_list` int(10) NOT NULL AUTO_INCREMENT,
  `id_order` int(10) NOT NULL,
  `id_etsy_order` bigint(25) NOT NULL,
  `is_status_updated` enum('0','1') NOT NULL DEFAULT '0',
  `is_tracking_updated` enum('0','1') NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_etsy_orders_list`),
  KEY `is_status_updated` (`is_status_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_orders_list` WRITE;
/*!40000 ALTER TABLE `ps_etsy_orders_list` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_etsy_orders_list` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `history_id`, `product_id`, `etsy_list_id`, `expiry_date` FROM `ps_etsy_products_history`;
    
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

DROP TABLE IF EXISTS `ps_etsy_products_history`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_products_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `etsy_list_id` varchar(100) NOT NULL,
  `expiry_date` datetime NOT NULL,
  PRIMARY KEY (`history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_products_history` WRITE;
/*!40000 ALTER TABLE `ps_etsy_products_history` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_etsy_products_history` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_etsy_profiles`, `profile_title`, `customize_product_title`, `id_etsy_shipping_templates`, `etsy_currency`, `is_customizable`, `who_made`, `when_made`, `is_supply`, `recipient`, `occassion`, `enable_max_qty`, `max_qty`, `enable_min_qty`, `min_qty`, `property`, `active`, `date_added`, `date_updated`, `id_etsy_shop_section`, `material_feature`, `custom_pricing`, `custom_price`, `price_type`, `price_reduction`, `etsy_product_type`, `etsy_selected_products`, `should_auto_renew`, `size_chart_image` FROM `ps_etsy_profiles`;
    
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

DROP TABLE IF EXISTS `ps_etsy_profiles`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_profiles` (
  `id_etsy_profiles` int(10) NOT NULL AUTO_INCREMENT,
  `profile_title` varchar(255) NOT NULL,
  `customize_product_title` text,
  `id_etsy_shipping_templates` int(10) NOT NULL,
  `etsy_currency` varchar(5) NOT NULL,
  `is_customizable` enum('1','0') NOT NULL DEFAULT '0',
  `who_made` enum('i_did','collective','someone_else') NOT NULL DEFAULT 'i_did',
  `when_made` varchar(50) DEFAULT NULL,
  `is_supply` enum('1','0') NOT NULL DEFAULT '0',
  `recipient` varchar(50) DEFAULT NULL,
  `occassion` varchar(50) DEFAULT NULL,
  `enable_max_qty` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `max_qty` int(10) unsigned NOT NULL DEFAULT '0',
  `enable_min_qty` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `min_qty` int(10) unsigned NOT NULL DEFAULT '0',
  `property` varchar(255) DEFAULT NULL,
  `active` enum('1','0') NOT NULL DEFAULT '1',
  `date_added` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_etsy_shop_section` int(5) DEFAULT NULL,
  `material_feature` varchar(2) DEFAULT NULL,
  `custom_pricing` int(11) NOT NULL DEFAULT '0',
  `custom_price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `price_type` enum('Fixed','Percentage') DEFAULT NULL,
  `price_reduction` enum('increase','decrease') DEFAULT NULL,
  `etsy_product_type` int(11) NOT NULL DEFAULT '0',
  `etsy_selected_products` text,
  `should_auto_renew` tinyint(1) NOT NULL DEFAULT '0',
  `size_chart_image` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_etsy_profiles`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_profiles` WRITE;
/*!40000 ALTER TABLE `ps_etsy_profiles` DISABLE KEYS */;
INSERT INTO `ps_etsy_profiles` VALUES (1,'Fashion','{product_title}',3,'','0','collective','2020_2021','0','','',1,999,0,1,'','1','2021-02-08 11:43:42','2021-03-16 07:15:45',1,'',0,0.00,'Fixed','increase',0,'',0,0);
INSERT INTO `ps_etsy_profiles` VALUES (4,'CLOTHING','{product_title}',3,'','0','i_did','made_to_order','0','','',1,999,0,1,'','1','2021-03-07 18:56:18','2021-03-16 07:15:30',7,'',0,0.00,'Fixed','increase',0,'',1,1);
INSERT INTO `ps_etsy_profiles` VALUES (5,'DECOR','{product_title}',3,'','0','i_did','made_to_order','0','','',1,999,0,1,'','1','2021-03-07 18:57:11','2021-03-15 08:20:17',6,'',0,0.00,'Fixed','increase',0,'',1,0);
INSERT INTO `ps_etsy_profiles` VALUES (6,'KITCHEN','{product_title}',3,'','0','i_did','made_to_order','0','','',1,999,0,1,'','1','2021-03-07 19:03:59','2021-03-09 13:50:25',4,'',0,0.00,'Fixed','increase',0,'',1,0);
INSERT INTO `ps_etsy_profiles` VALUES (9,'Masks','{product_title}',3,'','0','collective','2020_2021','0','','',1,999,0,1,'','1','2021-03-09 14:08:30','2021-03-11 15:17:38',8,'',0,0.00,'Fixed','increase',0,'',0,1);

/*!40000 ALTER TABLE `ps_etsy_profiles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id`, `filename`, `process_date`, `current_file`, `queue_status`, `queue_date`, `flag`, `total_record`, `processed_record`, `type`, `position` FROM `ps_etsy_queue`;
    
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

DROP TABLE IF EXISTS `ps_etsy_queue`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  `process_date` datetime DEFAULT NULL,
  `current_file` varchar(255) DEFAULT NULL,
  `queue_status` enum('Pending','Processing','Completed') DEFAULT 'Pending',
  `queue_date` datetime DEFAULT NULL,
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `total_record` int(11) DEFAULT NULL,
  `processed_record` int(11) DEFAULT NULL,
  `type` text,
  `position` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_queue` WRITE;
/*!40000 ALTER TABLE `ps_etsy_queue` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_etsy_queue` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_etsy_regions`, `region_id`, `region_name` FROM `ps_etsy_regions`;
    
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

DROP TABLE IF EXISTS `ps_etsy_regions`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_regions` (
  `id_etsy_regions` int(10) NOT NULL AUTO_INCREMENT,
  `region_id` int(10) NOT NULL,
  `region_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id_etsy_regions`),
  KEY `region_id` (`region_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_regions` WRITE;
/*!40000 ALTER TABLE `ps_etsy_regions` DISABLE KEYS */;
INSERT INTO `ps_etsy_regions` VALUES (1,11,'European Union');
INSERT INTO `ps_etsy_regions` VALUES (2,12,'Europe non-EU');
INSERT INTO `ps_etsy_regions` VALUES (3,13,'South America');
INSERT INTO `ps_etsy_regions` VALUES (4,14,'Africa');
INSERT INTO `ps_etsy_regions` VALUES (5,15,'Central America');

/*!40000 ALTER TABLE `ps_etsy_regions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_etsy_shipping_templates`, `shipping_template_id`, `shipping_template_title`, `shipping_origin_country_id`, `shipping_origin_country`, `shipping_primary_cost`, `shipping_secondary_cost`, `shipping_min_process_days`, `shipping_max_process_days`, `renew_flag`, `delete_flag`, `shipping_date_added`, `shipping_date_update` FROM `ps_etsy_shipping_templates`;
    
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

DROP TABLE IF EXISTS `ps_etsy_shipping_templates`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_shipping_templates` (
  `id_etsy_shipping_templates` int(10) NOT NULL AUTO_INCREMENT,
  `shipping_template_id` bigint(25) DEFAULT NULL,
  `shipping_template_title` varchar(255) NOT NULL,
  `shipping_origin_country_id` int(10) NOT NULL,
  `shipping_origin_country` varchar(255) NOT NULL,
  `shipping_primary_cost` decimal(15,2) NOT NULL,
  `shipping_secondary_cost` decimal(15,2) NOT NULL,
  `shipping_min_process_days` int(2) NOT NULL,
  `shipping_max_process_days` int(2) NOT NULL,
  `renew_flag` enum('0','1') NOT NULL DEFAULT '0',
  `delete_flag` enum('0','1') NOT NULL DEFAULT '0',
  `shipping_date_added` datetime NOT NULL,
  `shipping_date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_etsy_shipping_templates`),
  UNIQUE KEY `shipping_template_id` (`shipping_template_id`),
  KEY `renew_flag` (`renew_flag`,`delete_flag`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_shipping_templates` WRITE;
/*!40000 ALTER TABLE `ps_etsy_shipping_templates` DISABLE KEYS */;
INSERT INTO `ps_etsy_shipping_templates` VALUES (3,130005260804,'Canada Post',79,'Canada',0.00,0.00,1,2,'1','0','2021-02-08 12:15:56','2021-03-07 19:53:31');

/*!40000 ALTER TABLE `ps_etsy_shipping_templates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_etsy_shop_section`, `shop_section_title`, `delete_flag`, `renew_flag`, `shop_section_date_added`, `shop_section_date_update`, `shop_section_id` FROM `ps_etsy_shop_section`;
    
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

DROP TABLE IF EXISTS `ps_etsy_shop_section`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_shop_section` (
  `id_etsy_shop_section` int(5) NOT NULL AUTO_INCREMENT,
  `shop_section_title` varchar(25) NOT NULL,
  `delete_flag` int(1) NOT NULL DEFAULT '0',
  `renew_flag` int(1) NOT NULL DEFAULT '0',
  `shop_section_date_added` datetime NOT NULL,
  `shop_section_date_update` datetime NOT NULL,
  `shop_section_id` varchar(20) NOT NULL,
  PRIMARY KEY (`id_etsy_shop_section`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_shop_section` WRITE;
/*!40000 ALTER TABLE `ps_etsy_shop_section` DISABLE KEYS */;
INSERT INTO `ps_etsy_shop_section` VALUES (1,'Fashion',0,0,'2021-01-26 12:18:27','2021-01-26 12:18:27','32365961');
INSERT INTO `ps_etsy_shop_section` VALUES (2,'Coutou Collection',0,0,'2021-03-07 18:48:30','2021-03-07 18:48:30','33032392');
INSERT INTO `ps_etsy_shop_section` VALUES (3,'Foot Wear',0,0,'2021-03-07 19:14:34','2021-03-07 19:14:34','33032668');
INSERT INTO `ps_etsy_shop_section` VALUES (4,'Kitchen ',0,0,'2021-03-07 19:14:41','2021-03-07 19:14:41','33032672');
INSERT INTO `ps_etsy_shop_section` VALUES (6,'Decor',0,0,'2021-03-07 19:14:48','2021-03-07 19:14:48','33032674');
INSERT INTO `ps_etsy_shop_section` VALUES (7,'Clothing',0,0,'2021-03-07 19:14:56','2021-03-07 19:14:56','33032680');
INSERT INTO `ps_etsy_shop_section` VALUES (8,'Masks',0,0,'2021-03-07 19:15:00','2021-03-07 19:15:00','33032682');

/*!40000 ALTER TABLE `ps_etsy_shop_section` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `translation_id`, `id_product`, `listing_id`, `status`, `lang_code`, `date_added`, `date_updated`, `translation_error` FROM `ps_etsy_translation`;
    
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

DROP TABLE IF EXISTS `ps_etsy_translation`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_etsy_translation` (
  `translation_id` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `status` enum('Listed','Pending','Update') NOT NULL,
  `lang_code` varchar(5) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `translation_error` text,
  PRIMARY KEY (`translation_id`),
  UNIQUE KEY `id_product_lang_code` (`id_product`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_etsy_translation` WRITE;
/*!40000 ALTER TABLE `ps_etsy_translation` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_etsy_translation` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_relatedproductsbycateg`, `id_category`, `id_feature`, `id_feature_value`, `id_attribute`, `id_attribute_value`, `reference`, `active` FROM `ps_ewrelatedproductsbycateg`;
    
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

DROP TABLE IF EXISTS `ps_ewrelatedproductsbycateg`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_ewrelatedproductsbycateg` (
  `id_relatedproductsbycateg` int(11) NOT NULL AUTO_INCREMENT,
  `id_category` int(11) unsigned NOT NULL,
  `id_feature` int(11) unsigned DEFAULT '0',
  `id_feature_value` int(11) unsigned DEFAULT '0',
  `id_attribute` int(11) unsigned DEFAULT '0',
  `id_attribute_value` int(11) unsigned DEFAULT '0',
  `reference` varchar(45) DEFAULT '',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_relatedproductsbycateg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_ewrelatedproductsbycateg` WRITE;
/*!40000 ALTER TABLE `ps_ewrelatedproductsbycateg` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_ewrelatedproductsbycateg` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_relatedproductsbycateg`, `id_shop` FROM `ps_ewrelatedproductsbycateg_shop`;
    
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

DROP TABLE IF EXISTS `ps_ewrelatedproductsbycateg_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_ewrelatedproductsbycateg_shop` (
  `id_relatedproductsbycateg` int(11) NOT NULL AUTO_INCREMENT,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_relatedproductsbycateg`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_ewrelatedproductsbycateg_shop` WRITE;
/*!40000 ALTER TABLE `ps_ewrelatedproductsbycateg_shop` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_ewrelatedproductsbycateg_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_feature`, `id_shop` FROM `ps_feature_shop`;
    
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

DROP TABLE IF EXISTS `ps_feature_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_feature_shop` (
  `id_feature` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_feature`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_feature_shop` WRITE;
/*!40000 ALTER TABLE `ps_feature_shop` DISABLE KEYS */;
INSERT INTO `ps_feature_shop` VALUES (1,1);
INSERT INTO `ps_feature_shop` VALUES (2,1);
INSERT INTO `ps_feature_shop` VALUES (3,1);
INSERT INTO `ps_feature_shop` VALUES (4,1);
INSERT INTO `ps_feature_shop` VALUES (5,1);
INSERT INTO `ps_feature_shop` VALUES (6,1);
INSERT INTO `ps_feature_shop` VALUES (7,1);
INSERT INTO `ps_feature_shop` VALUES (8,1);
INSERT INTO `ps_feature_shop` VALUES (9,1);
INSERT INTO `ps_feature_shop` VALUES (10,1);
INSERT INTO `ps_feature_shop` VALUES (11,1);
INSERT INTO `ps_feature_shop` VALUES (12,1);
INSERT INTO `ps_feature_shop` VALUES (13,1);

/*!40000 ALTER TABLE `ps_feature_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_gender`, `type` FROM `ps_gender`;
    
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

DROP TABLE IF EXISTS `ps_gender`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gender` (
  `id_gender` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_gender`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gender` WRITE;
/*!40000 ALTER TABLE `ps_gender` DISABLE KEYS */;
INSERT INTO `ps_gender` VALUES (1,0);
INSERT INTO `ps_gender` VALUES (2,1);
INSERT INTO `ps_gender` VALUES (3,1);

/*!40000 ALTER TABLE `ps_gender` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_image`, `id_lang`, `tags` FROM `ps_giftcard_tags`;
    
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

DROP TABLE IF EXISTS `ps_giftcard_tags`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_giftcard_tags` (
  `id_image` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `tags` text,
  PRIMARY KEY (`id_image`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_giftcard_tags` WRITE;
/*!40000 ALTER TABLE `ps_giftcard_tags` DISABLE KEYS */;
INSERT INTO `ps_giftcard_tags` VALUES (3810,1,'');
INSERT INTO `ps_giftcard_tags` VALUES (3810,3,'');

/*!40000 ALTER TABLE `ps_giftcard_tags` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id`, `status`, `id_shop`, `name`, `type`, `exclusion_value` FROM `ps_gmcp_advanced_exclusion`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_advanced_exclusion`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_advanced_exclusion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL DEFAULT '1',
  `id_shop` int(11) NOT NULL DEFAULT '1',
  `name` char(255) NOT NULL,
  `type` char(255) NOT NULL,
  `exclusion_value` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_advanced_exclusion` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_advanced_exclusion` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_advanced_exclusion` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_brands`, `id_shop` FROM `ps_gmcp_brands`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_brands`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_brands` (
  `id_brands` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL DEFAULT '1',
  UNIQUE KEY `id_brands` (`id_brands`,`id_shop`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_brands` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_brands` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_brands` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_association`, `id_discount`, `id_product`, `channel` FROM `ps_gmcp_discount_association`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_discount_association`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_discount_association` (
  `id_association` int(11) NOT NULL AUTO_INCREMENT,
  `id_discount` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `channel` char(120) NOT NULL DEFAULT 'SHOPPING_ADS',
  PRIMARY KEY (`id_association`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_discount_association` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_discount_association` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_discount_association` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_cat`, `id_shop`, `values` FROM `ps_gmcp_features_by_cat`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_features_by_cat`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_features_by_cat` (
  `id_cat` int(11) NOT NULL DEFAULT '0',
  `id_shop` int(3) NOT NULL DEFAULT '1',
  `values` text NOT NULL,
  PRIMARY KEY (`id_cat`,`id_shop`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_features_by_cat` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_features_by_cat` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_features_by_cat` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_sync`, `generic_email`, `real_email`, `id_shop` FROM `ps_gmcp_gsa_emails`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_gsa_emails`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_gsa_emails` (
  `id_sync` int(11) NOT NULL AUTO_INCREMENT,
  `generic_email` char(50) NOT NULL,
  `real_email` char(50) NOT NULL,
  `id_shop` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_sync`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_gsa_emails` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_gsa_emails` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_gsa_emails` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_sync`, `id_order`, `id_gsa_order`, `gsa_status`, `order_status`, `is_paid`, `is_shipped`, `is_shipped_synch`, `is_prepared`, `is_refunded`, `is_refunded_synch`, `is_product_refunded`, `is_canceled_synch`, `acknowledge`, `is_delivered`, `is_delivered_synch`, `is_product_refunded_synch`, `is_returned`, `is_returned_synch`, `id_shop` FROM `ps_gmcp_gsa_orders_data`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_gsa_orders_data`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_gsa_orders_data` (
  `id_sync` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_gsa_order` char(50) NOT NULL,
  `gsa_status` text NOT NULL,
  `order_status` char(50) NOT NULL,
  `is_paid` int(1) NOT NULL,
  `is_shipped` int(1) NOT NULL,
  `is_shipped_synch` int(1) NOT NULL,
  `is_prepared` int(1) NOT NULL,
  `is_refunded` int(1) NOT NULL,
  `is_refunded_synch` int(1) NOT NULL,
  `is_product_refunded` int(1) NOT NULL,
  `is_canceled_synch` int(1) NOT NULL,
  `acknowledge` int(1) NOT NULL DEFAULT '0',
  `is_delivered` int(1) NOT NULL DEFAULT '0',
  `is_delivered_synch` int(1) NOT NULL DEFAULT '0',
  `is_product_refunded_synch` int(1) NOT NULL,
  `is_returned` int(1) NOT NULL,
  `is_returned_synch` int(1) NOT NULL,
  `id_shop` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_sync`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_gsa_orders_data` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_gsa_orders_data` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_gsa_orders_data` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_sync`, `id_gsa_order`, `shop_product_id`, `shop_product_attribute_id`, `gsa_product_id`, `quantity_ordered`, `quantity_refunded`, `quantity_returned`, `id_shop` FROM `ps_gmcp_gsa_orders_products_data`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_gsa_orders_products_data`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_gsa_orders_products_data` (
  `id_sync` int(11) NOT NULL AUTO_INCREMENT,
  `id_gsa_order` char(50) NOT NULL,
  `shop_product_id` int(20) NOT NULL,
  `shop_product_attribute_id` int(20) NOT NULL,
  `gsa_product_id` char(50) NOT NULL,
  `quantity_ordered` int(20) NOT NULL,
  `quantity_refunded` int(20) NOT NULL,
  `quantity_returned` int(20) NOT NULL,
  `id_shop` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_sync`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_gsa_orders_products_data` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_gsa_orders_products_data` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_gsa_orders_products_data` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_sync`, `id_gsa_return`, `id_order`, `id_gsa_order`, `product_data`, `id_shop` FROM `ps_gmcp_gsa_returns_data`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_gsa_returns_data`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_gsa_returns_data` (
  `id_sync` int(11) NOT NULL AUTO_INCREMENT,
  `id_gsa_return` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_gsa_order` char(50) NOT NULL,
  `product_data` longtext NOT NULL,
  `id_shop` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_sync`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_gsa_returns_data` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_gsa_returns_data` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_gsa_returns_data` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_rule`, `id_product`, `id_product_attribute` FROM `ps_gmcp_product_excluded`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_product_excluded`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_product_excluded` (
  `id_rule` int(11) NOT NULL DEFAULT '0',
  `id_product` int(11) NOT NULL DEFAULT '0',
  `id_product_attribute` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_product_excluded` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_product_excluded` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_product_excluded` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `id_shop`, `name`, `type`, `active`, `position`, `end_date` FROM `ps_gmcp_tags`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags` (
  `id_tag` int(11) NOT NULL AUTO_INCREMENT,
  `id_shop` int(11) NOT NULL DEFAULT '1',
  `name` char(255) NOT NULL,
  `type` char(255) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `position` int(11) NOT NULL,
  `end_date` date DEFAULT NULL,
  PRIMARY KEY (`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `id_brand` FROM `ps_gmcp_tags_brands`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_brands`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_brands` (
  `id_tag` int(11) NOT NULL,
  `id_brand` int(11) NOT NULL,
  UNIQUE KEY `tag_brand` (`id_tag`,`id_brand`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_brands` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_brands` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_brands` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `id_category` FROM `ps_gmcp_tags_cats`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_cats`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_cats` (
  `id_tag` int(11) NOT NULL,
  `id_category` int(11) NOT NULL,
  UNIQUE KEY `tag_cat` (`id_tag`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_cats` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_cats` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_cats` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `amount`, `unit`, `start_date`, `end_date`, `id_product`, `id_shop` FROM `ps_gmcp_tags_dynamic_best_sale`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_dynamic_best_sale`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_dynamic_best_sale` (
  `id_tag` int(11) NOT NULL,
  `amount` char(255) NOT NULL,
  `unit` char(255) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `id_product` char(255) DEFAULT NULL,
  `id_shop` int(11) NOT NULL,
  UNIQUE KEY `tag_best_sales` (`id_tag`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_dynamic_best_sale` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_dynamic_best_sale` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_dynamic_best_sale` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:10+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `id_category`, `id_shop` FROM `ps_gmcp_tags_dynamic_categories`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_dynamic_categories`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_dynamic_categories` (
  `id_tag` int(11) NOT NULL,
  `id_category` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  UNIQUE KEY `tag_feature` (`id_tag`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_dynamic_categories` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_dynamic_categories` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_dynamic_categories` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `id_feature`, `id_shop` FROM `ps_gmcp_tags_dynamic_features`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_dynamic_features`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_dynamic_features` (
  `id_tag` int(11) NOT NULL,
  `id_feature` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  UNIQUE KEY `tag_feature` (`id_tag`,`id_feature`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_dynamic_features` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_dynamic_features` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_dynamic_features` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `from_date`, `id_product`, `id_shop` FROM `ps_gmcp_tags_dynamic_new_product`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_dynamic_new_product`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_dynamic_new_product` (
  `id_tag` int(11) NOT NULL,
  `from_date` datetime DEFAULT NULL,
  `id_product` int(11) DEFAULT NULL,
  `id_shop` int(11) NOT NULL,
  UNIQUE KEY `tag_new_product` (`id_tag`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_dynamic_new_product` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_dynamic_new_product` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_dynamic_new_product` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `price_min`, `price_max`, `id_product` FROM `ps_gmcp_tags_price_range`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_price_range`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_price_range` (
  `id_tag` int(11) NOT NULL,
  `price_min` char(255) NOT NULL,
  `price_max` char(255) DEFAULT NULL,
  `id_product` char(255) DEFAULT NULL,
  UNIQUE KEY `tag_price_range` (`id_tag`,`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_price_range` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_price_range` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_price_range` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `id_product`, `product_name` FROM `ps_gmcp_tags_products`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_products`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_products` (
  `id_tag` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  UNIQUE KEY `tag_product` (`id_tag`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_products` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_products` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_products` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tag`, `id_supplier` FROM `ps_gmcp_tags_suppliers`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tags_suppliers`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tags_suppliers` (
  `id_tag` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  UNIQUE KEY `tag_supplier` (`id_tag`,`id_supplier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tags_suppliers` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tags_suppliers` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tags_suppliers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_category`, `id_shop`, `txt_taxonomy`, `lang` FROM `ps_gmcp_taxonomy_categories`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_taxonomy_categories`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_taxonomy_categories` (
  `id_category` int(11) NOT NULL,
  `id_shop` int(3) NOT NULL DEFAULT '1',
  `txt_taxonomy` text NOT NULL,
  `lang` char(5) NOT NULL,
  KEY `id_category` (`id_category`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_taxonomy_categories` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_taxonomy_categories` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_taxonomy_categories` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id`, `id_shop`, `type`, `exclusion_values` FROM `ps_gmcp_tmp_rules`;
    
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

DROP TABLE IF EXISTS `ps_gmcp_tmp_rules`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gmcp_tmp_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_shop` int(11) NOT NULL DEFAULT '1',
  `type` char(255) NOT NULL,
  `exclusion_values` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gmcp_tmp_rules` WRITE;
/*!40000 ALTER TABLE `ps_gmcp_tmp_rules` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_gmcp_tmp_rules` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_group_reduction`, `id_group`, `id_category`, `reduction` FROM `ps_group_reduction`;
    
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

DROP TABLE IF EXISTS `ps_group_reduction`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_group_reduction` (
  `id_group_reduction` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_group` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  `reduction` decimal(4,3) NOT NULL,
  PRIMARY KEY (`id_group_reduction`),
  UNIQUE KEY `id_group` (`id_group`,`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_group_reduction` WRITE;
/*!40000 ALTER TABLE `ps_group_reduction` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_group_reduction` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_group`, `id_shop` FROM `ps_group_shop`;
    
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

DROP TABLE IF EXISTS `ps_group_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_group_shop` (
  `id_group` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_group`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_group_shop` WRITE;
/*!40000 ALTER TABLE `ps_group_shop` DISABLE KEYS */;
INSERT INTO `ps_group_shop` VALUES (1,1);
INSERT INTO `ps_group_shop` VALUES (2,1);
INSERT INTO `ps_group_shop` VALUES (3,1);
INSERT INTO `ps_group_shop` VALUES (4,1);
INSERT INTO `ps_group_shop` VALUES (5,1);

/*!40000 ALTER TABLE `ps_group_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `link`, `id_shop` FROM `ps_gsitemap_sitemap`;
    
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

DROP TABLE IF EXISTS `ps_gsitemap_sitemap`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_gsitemap_sitemap` (
  `link` varchar(255) DEFAULT NULL,
  `id_shop` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_gsitemap_sitemap` WRITE;
/*!40000 ALTER TABLE `ps_gsitemap_sitemap` DISABLE KEYS */;
INSERT INTO `ps_gsitemap_sitemap` VALUES ('1_en_0_sitemap.xml',1);
INSERT INTO `ps_gsitemap_sitemap` VALUES ('1_qc_0_sitemap.xml',1);

/*!40000 ALTER TABLE `ps_gsitemap_sitemap` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_hook_module_exceptions`, `id_shop`, `id_module`, `id_hook`, `file_name` FROM `ps_hook_module_exceptions`;
    
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

DROP TABLE IF EXISTS `ps_hook_module_exceptions`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_hook_module_exceptions` (
  `id_hook_module_exceptions` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_shop` int(11) unsigned NOT NULL DEFAULT '1',
  `id_module` int(10) unsigned NOT NULL,
  `id_hook` int(10) unsigned NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_hook_module_exceptions`),
  KEY `id_module` (`id_module`),
  KEY `id_hook` (`id_hook`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_hook_module_exceptions` WRITE;
/*!40000 ALTER TABLE `ps_hook_module_exceptions` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_hook_module_exceptions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_import_match`, `name`, `match`, `skip` FROM `ps_import_match`;
    
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

DROP TABLE IF EXISTS `ps_import_match`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_import_match` (
  `id_import_match` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `match` text NOT NULL,
  `skip` int(2) NOT NULL,
  PRIMARY KEY (`id_import_match`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_import_match` WRITE;
/*!40000 ALTER TABLE `ps_import_match` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_import_match` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_info` FROM `ps_info`;
    
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

DROP TABLE IF EXISTS `ps_info`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_info` (
  `id_info` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id_info`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_info` WRITE;
/*!40000 ALTER TABLE `ps_info` DISABLE KEYS */;
INSERT INTO `ps_info` VALUES (1);

/*!40000 ALTER TABLE `ps_info` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_info`, `id_shop` FROM `ps_info_shop`;
    
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

DROP TABLE IF EXISTS `ps_info_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_info_shop` (
  `id_info` int(10) unsigned NOT NULL,
  `id_shop` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_info`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_info_shop` WRITE;
/*!40000 ALTER TABLE `ps_info_shop` DISABLE KEYS */;
INSERT INTO `ps_info_shop` VALUES (1,1);

/*!40000 ALTER TABLE `ps_info_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_lang`, `name`, `active`, `iso_code`, `language_code`, `locale`, `date_format_lite`, `date_format_full`, `is_rtl` FROM `ps_lang`;
    
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

DROP TABLE IF EXISTS `ps_lang`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_lang` (
  `id_lang` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `iso_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `language_code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `date_format_lite` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_format_full` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `is_rtl` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_lang`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_lang` WRITE;
/*!40000 ALTER TABLE `ps_lang` DISABLE KEYS */;
INSERT INTO `ps_lang` VALUES (1,'English (English)',1,'en','en-us','en-US','m/d/Y','m/d/Y H:i:s',0);
INSERT INTO `ps_lang` VALUES (3,'Français (French)',1,'qc','fr-ca','fr-CA','d/m/Y','d/m/Y H:i:s',0);

/*!40000 ALTER TABLE `ps_lang` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_lang`, `id_shop` FROM `ps_lang_shop`;
    
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

DROP TABLE IF EXISTS `ps_lang_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_lang_shop` (
  `id_lang` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_lang`,`id_shop`),
  KEY `IDX_2F43BFC7BA299860` (`id_lang`),
  KEY `IDX_2F43BFC7274A50A0` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_lang_shop` WRITE;
/*!40000 ALTER TABLE `ps_lang_shop` DISABLE KEYS */;
INSERT INTO `ps_lang_shop` VALUES (1,1);
INSERT INTO `ps_lang_shop` VALUES (3,1);

/*!40000 ALTER TABLE `ps_lang_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_linksmenutop`, `id_shop`, `new_window` FROM `ps_linksmenutop`;
    
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

DROP TABLE IF EXISTS `ps_linksmenutop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_linksmenutop` (
  `id_linksmenutop` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_shop` int(11) unsigned NOT NULL,
  `new_window` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_linksmenutop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_linksmenutop` WRITE;
/*!40000 ALTER TABLE `ps_linksmenutop` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_linksmenutop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_linksmenutop`, `id_lang`, `id_shop`, `label`, `link` FROM `ps_linksmenutop_lang`;
    
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

DROP TABLE IF EXISTS `ps_linksmenutop_lang`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_linksmenutop_lang` (
  `id_linksmenutop` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  `label` varchar(128) NOT NULL,
  `link` varchar(128) NOT NULL,
  KEY `id_linksmenutop` (`id_linksmenutop`,`id_lang`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_linksmenutop_lang` WRITE;
/*!40000 ALTER TABLE `ps_linksmenutop_lang` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_linksmenutop_lang` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_memcached_server`, `ip`, `port`, `weight` FROM `ps_memcached_servers`;
    
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

DROP TABLE IF EXISTS `ps_memcached_servers`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_memcached_servers` (
  `id_memcached_server` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(254) NOT NULL,
  `port` int(11) unsigned NOT NULL,
  `weight` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_memcached_server`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_memcached_servers` WRITE;
/*!40000 ALTER TABLE `ps_memcached_servers` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_memcached_servers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_message`, `id_employee`, `date_add` FROM `ps_message_readed`;
    
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

DROP TABLE IF EXISTS `ps_message_readed`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_message_readed` (
  `id_message` int(10) unsigned NOT NULL,
  `id_employee` int(10) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_message`,`id_employee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_message_readed` WRITE;
/*!40000 ALTER TABLE `ps_message_readed` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_message_readed` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_module_preference`, `id_employee`, `module`, `interest`, `favorite` FROM `ps_module_preference`;
    
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

DROP TABLE IF EXISTS `ps_module_preference`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_module_preference` (
  `id_module_preference` int(11) NOT NULL AUTO_INCREMENT,
  `id_employee` int(11) NOT NULL,
  `module` varchar(255) NOT NULL,
  `interest` tinyint(1) DEFAULT NULL,
  `favorite` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_module_preference`),
  UNIQUE KEY `employee_module` (`id_employee`,`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_module_preference` WRITE;
/*!40000 ALTER TABLE `ps_module_preference` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_module_preference` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:11+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_operating_system`, `name` FROM `ps_operating_system`;
    
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

DROP TABLE IF EXISTS `ps_operating_system`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_operating_system` (
  `id_operating_system` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id_operating_system`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_operating_system` WRITE;
/*!40000 ALTER TABLE `ps_operating_system` DISABLE KEYS */;
INSERT INTO `ps_operating_system` VALUES (1,'Windows XP');
INSERT INTO `ps_operating_system` VALUES (2,'Windows Vista');
INSERT INTO `ps_operating_system` VALUES (3,'Windows 7');
INSERT INTO `ps_operating_system` VALUES (4,'Windows 8');
INSERT INTO `ps_operating_system` VALUES (5,'Windows 8.1');
INSERT INTO `ps_operating_system` VALUES (6,'Windows 10');
INSERT INTO `ps_operating_system` VALUES (7,'MacOsX');
INSERT INTO `ps_operating_system` VALUES (8,'Linux');
INSERT INTO `ps_operating_system` VALUES (9,'Android');

/*!40000 ALTER TABLE `ps_operating_system` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:12+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_order_return`, `id_customer`, `id_order`, `state`, `question`, `date_add`, `date_upd` FROM `ps_order_return`;
    
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

DROP TABLE IF EXISTS `ps_order_return`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_order_return` (
  `id_order_return` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL,
  `id_order` int(10) unsigned NOT NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `question` text NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_order_return`),
  KEY `order_return_customer` (`id_customer`),
  KEY `id_order` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_order_return` WRITE;
/*!40000 ALTER TABLE `ps_order_return` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_order_return` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:12+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_order_return`, `id_order_detail`, `id_customization`, `product_quantity` FROM `ps_order_return_detail`;
    
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

DROP TABLE IF EXISTS `ps_order_return_detail`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_order_return_detail` (
  `id_order_return` int(10) unsigned NOT NULL,
  `id_order_detail` int(10) unsigned NOT NULL,
  `id_customization` int(10) unsigned NOT NULL DEFAULT '0',
  `product_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_order_return`,`id_order_detail`,`id_customization`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_order_return_detail` WRITE;
/*!40000 ALTER TABLE `ps_order_return_detail` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_order_return_detail` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:12+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_order_slip_detail`, `id_tax`, `unit_amount`, `total_amount` FROM `ps_order_slip_detail_tax`;
    
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

DROP TABLE IF EXISTS `ps_order_slip_detail_tax`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_order_slip_detail_tax` (
  `id_order_slip_detail` int(11) unsigned NOT NULL,
  `id_tax` int(11) unsigned NOT NULL,
  `unit_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `total_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  KEY `id_order_slip_detail` (`id_order_slip_detail`),
  KEY `id_tax` (`id_tax`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_order_slip_detail_tax` WRITE;
/*!40000 ALTER TABLE `ps_order_slip_detail_tax` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_order_slip_detail_tax` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:12+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_paypal_ipn`, `id_transaction`, `status`, `response`, `date_add` FROM `ps_paypal_ipn`;
    
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

DROP TABLE IF EXISTS `ps_paypal_ipn`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_paypal_ipn` (
  `id_paypal_ipn` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_transaction` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `response` text NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_paypal_ipn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_paypal_ipn` WRITE;
/*!40000 ALTER TABLE `ps_paypal_ipn` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_paypal_ipn` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:12+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_paypal_vaulting`, `id_customer`, `rememberedCards`, `profile_key`, `sandbox`, `date_add`, `date_upd` FROM `ps_paypal_vaulting`;
    
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

DROP TABLE IF EXISTS `ps_paypal_vaulting`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_paypal_vaulting` (
  `id_paypal_vaulting` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL,
  `rememberedCards` varchar(255) NOT NULL,
  `profile_key` varchar(255) NOT NULL,
  `sandbox` tinyint(1) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_paypal_vaulting`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_paypal_vaulting` WRITE;
/*!40000 ALTER TABLE `ps_paypal_vaulting` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_paypal_vaulting` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:12+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_pos_employee_checkin`, `id_employee`, `employee_ip`, `id_shop`, `action`, `img_path`, `date_add` FROM `ps_pos_employee_checkin`;
    
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

DROP TABLE IF EXISTS `ps_pos_employee_checkin`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_pos_employee_checkin` (
  `id_pos_employee_checkin` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_employee` int(10) unsigned NOT NULL,
  `employee_ip` varchar(20) DEFAULT NULL,
  `id_shop` int(10) DEFAULT NULL,
  `action` varchar(20) DEFAULT NULL,
  `img_path` varchar(255) DEFAULT NULL,
  `date_add` datetime DEFAULT NULL,
  PRIMARY KEY (`id_pos_employee_checkin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_pos_employee_checkin` WRITE;
/*!40000 ALTER TABLE `ps_pos_employee_checkin` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_pos_employee_checkin` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:12+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_pos_order`, `status`, `id_employee`, `note`, `show_note` FROM `ps_pos_orders`;
    
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

DROP TABLE IF EXISTS `ps_pos_orders`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_pos_orders` (
  `id_pos_order` int(11) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `id_employee` int(11) DEFAULT NULL,
  `note` text,
  `show_note` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_pos_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_pos_orders` WRITE;
/*!40000 ALTER TABLE `ps_pos_orders` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_pos_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:13+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_product`, `id_country`, `id_tax` FROM `ps_product_country_tax`;
    
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

DROP TABLE IF EXISTS `ps_product_country_tax`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_product_country_tax` (
  `id_product` int(11) NOT NULL,
  `id_country` int(11) NOT NULL,
  `id_tax` int(11) NOT NULL,
  PRIMARY KEY (`id_product`,`id_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_product_country_tax` WRITE;
/*!40000 ALTER TABLE `ps_product_country_tax` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_product_country_tax` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:13+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_product_download`, `id_product`, `display_filename`, `filename`, `date_add`, `date_expiration`, `nb_days_accessible`, `nb_downloadable`, `active`, `is_shareable` FROM `ps_product_download`;
    
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

DROP TABLE IF EXISTS `ps_product_download`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_product_download` (
  `id_product_download` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `display_filename` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_expiration` datetime DEFAULT NULL,
  `nb_days_accessible` int(10) unsigned DEFAULT NULL,
  `nb_downloadable` int(10) unsigned DEFAULT '1',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_shareable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_product_download`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_product_download` WRITE;
/*!40000 ALTER TABLE `ps_product_download` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_product_download` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:13+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_customer`, `id_product` FROM `ps_pwfavorites`;
    
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

DROP TABLE IF EXISTS `ps_pwfavorites`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_pwfavorites` (
  `id_customer` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_customer`,`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_pwfavorites` WRITE;
/*!40000 ALTER TABLE `ps_pwfavorites` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_pwfavorites` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:13+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_referrer`, `name`, `passwd`, `http_referer_regexp`, `http_referer_like`, `request_uri_regexp`, `request_uri_like`, `http_referer_regexp_not`, `http_referer_like_not`, `request_uri_regexp_not`, `request_uri_like_not`, `base_fee`, `percent_fee`, `click_fee`, `date_add` FROM `ps_referrer`;
    
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

DROP TABLE IF EXISTS `ps_referrer`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_referrer` (
  `id_referrer` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `passwd` varchar(255) DEFAULT NULL,
  `http_referer_regexp` varchar(64) DEFAULT NULL,
  `http_referer_like` varchar(64) DEFAULT NULL,
  `request_uri_regexp` varchar(64) DEFAULT NULL,
  `request_uri_like` varchar(64) DEFAULT NULL,
  `http_referer_regexp_not` varchar(64) DEFAULT NULL,
  `http_referer_like_not` varchar(64) DEFAULT NULL,
  `request_uri_regexp_not` varchar(64) DEFAULT NULL,
  `request_uri_like_not` varchar(64) DEFAULT NULL,
  `base_fee` decimal(5,2) NOT NULL DEFAULT '0.00',
  `percent_fee` decimal(5,2) NOT NULL DEFAULT '0.00',
  `click_fee` decimal(5,2) NOT NULL DEFAULT '0.00',
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_referrer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_referrer` WRITE;
/*!40000 ALTER TABLE `ps_referrer` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_referrer` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:13+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_referrer`, `id_shop`, `cache_visitors`, `cache_visits`, `cache_pages`, `cache_registrations`, `cache_orders`, `cache_sales`, `cache_reg_rate`, `cache_order_rate` FROM `ps_referrer_shop`;
    
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

DROP TABLE IF EXISTS `ps_referrer_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_referrer_shop` (
  `id_referrer` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_shop` int(10) unsigned NOT NULL DEFAULT '1',
  `cache_visitors` int(11) DEFAULT NULL,
  `cache_visits` int(11) DEFAULT NULL,
  `cache_pages` int(11) DEFAULT NULL,
  `cache_registrations` int(11) DEFAULT NULL,
  `cache_orders` int(11) DEFAULT NULL,
  `cache_sales` decimal(17,2) DEFAULT NULL,
  `cache_reg_rate` decimal(5,4) DEFAULT NULL,
  `cache_order_rate` decimal(5,4) DEFAULT NULL,
  PRIMARY KEY (`id_referrer`,`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_referrer_shop` WRITE;
/*!40000 ALTER TABLE `ps_referrer_shop` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_referrer_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:13+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_required_field`, `object_name`, `field_name` FROM `ps_required_field`;
    
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

DROP TABLE IF EXISTS `ps_required_field`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_required_field` (
  `id_required_field` int(11) NOT NULL AUTO_INCREMENT,
  `object_name` varchar(32) NOT NULL,
  `field_name` varchar(32) NOT NULL,
  PRIMARY KEY (`id_required_field`),
  KEY `object_name` (`object_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_required_field` WRITE;
/*!40000 ALTER TABLE `ps_required_field` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_required_field` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:13+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_risk`, `percent`, `color` FROM `ps_risk`;
    
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

DROP TABLE IF EXISTS `ps_risk`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_risk` (
  `id_risk` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `percent` tinyint(3) NOT NULL,
  `color` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id_risk`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_risk` WRITE;
/*!40000 ALTER TABLE `ps_risk` DISABLE KEYS */;
INSERT INTO `ps_risk` VALUES (1,0,'#32CD32');
INSERT INTO `ps_risk` VALUES (2,35,'#FF8C00');
INSERT INTO `ps_risk` VALUES (3,75,'#DC143C');
INSERT INTO `ps_risk` VALUES (4,100,'#ec2e15');

/*!40000 ALTER TABLE `ps_risk` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:13+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_search_engine`, `server`, `getvar` FROM `ps_search_engine`;
    
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

DROP TABLE IF EXISTS `ps_search_engine`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_search_engine` (
  `id_search_engine` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server` varchar(64) NOT NULL,
  `getvar` varchar(16) NOT NULL,
  PRIMARY KEY (`id_search_engine`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_search_engine` WRITE;
/*!40000 ALTER TABLE `ps_search_engine` DISABLE KEYS */;
INSERT INTO `ps_search_engine` VALUES (1,'google','q');
INSERT INTO `ps_search_engine` VALUES (2,'aol','q');
INSERT INTO `ps_search_engine` VALUES (3,'yandex','text');
INSERT INTO `ps_search_engine` VALUES (4,'ask.com','q');
INSERT INTO `ps_search_engine` VALUES (5,'nhl.com','q');
INSERT INTO `ps_search_engine` VALUES (6,'yahoo','p');
INSERT INTO `ps_search_engine` VALUES (7,'baidu','wd');
INSERT INTO `ps_search_engine` VALUES (8,'lycos','query');
INSERT INTO `ps_search_engine` VALUES (9,'exalead','q');
INSERT INTO `ps_search_engine` VALUES (10,'search.live','q');
INSERT INTO `ps_search_engine` VALUES (11,'voila','rdata');
INSERT INTO `ps_search_engine` VALUES (12,'altavista','q');
INSERT INTO `ps_search_engine` VALUES (13,'bing','q');
INSERT INTO `ps_search_engine` VALUES (14,'daum','q');
INSERT INTO `ps_search_engine` VALUES (15,'eniro','search_word');
INSERT INTO `ps_search_engine` VALUES (16,'naver','query');
INSERT INTO `ps_search_engine` VALUES (17,'msn','q');
INSERT INTO `ps_search_engine` VALUES (18,'netscape','query');
INSERT INTO `ps_search_engine` VALUES (19,'cnn','query');
INSERT INTO `ps_search_engine` VALUES (20,'about','terms');
INSERT INTO `ps_search_engine` VALUES (21,'mamma','query');
INSERT INTO `ps_search_engine` VALUES (22,'alltheweb','q');
INSERT INTO `ps_search_engine` VALUES (23,'virgilio','qs');
INSERT INTO `ps_search_engine` VALUES (24,'alice','qs');
INSERT INTO `ps_search_engine` VALUES (25,'najdi','q');
INSERT INTO `ps_search_engine` VALUES (26,'mama','query');
INSERT INTO `ps_search_engine` VALUES (27,'seznam','q');
INSERT INTO `ps_search_engine` VALUES (28,'onet','qt');
INSERT INTO `ps_search_engine` VALUES (29,'szukacz','q');
INSERT INTO `ps_search_engine` VALUES (30,'yam','k');
INSERT INTO `ps_search_engine` VALUES (31,'pchome','q');
INSERT INTO `ps_search_engine` VALUES (32,'kvasir','q');
INSERT INTO `ps_search_engine` VALUES (33,'sesam','q');
INSERT INTO `ps_search_engine` VALUES (34,'ozu','q');
INSERT INTO `ps_search_engine` VALUES (35,'terra','query');
INSERT INTO `ps_search_engine` VALUES (36,'mynet','q');
INSERT INTO `ps_search_engine` VALUES (37,'ekolay','q');
INSERT INTO `ps_search_engine` VALUES (38,'rambler','words');

/*!40000 ALTER TABLE `ps_search_engine` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_shop_group`, `name`, `share_customer`, `share_order`, `share_stock`, `active`, `deleted` FROM `ps_shop_group`;
    
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

DROP TABLE IF EXISTS `ps_shop_group`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_shop_group` (
  `id_shop_group` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `share_customer` tinyint(1) NOT NULL,
  `share_order` tinyint(1) NOT NULL,
  `share_stock` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_shop_group`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_shop_group` WRITE;
/*!40000 ALTER TABLE `ps_shop_group` DISABLE KEYS */;
INSERT INTO `ps_shop_group` VALUES (1,'Default',0,0,0,1,0);

/*!40000 ALTER TABLE `ps_shop_group` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_simpleblog_related_post`, `id_simpleblog_post`, `id_product` FROM `ps_simpleblog_related_post`;
    
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

DROP TABLE IF EXISTS `ps_simpleblog_related_post`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_simpleblog_related_post` (
  `id_simpleblog_related_post` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_simpleblog_post` int(11) unsigned NOT NULL,
  `id_product` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_simpleblog_related_post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_simpleblog_related_post` WRITE;
/*!40000 ALTER TABLE `ps_simpleblog_related_post` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_simpleblog_related_post` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_state`, `id_country`, `id_zone`, `name`, `iso_code`, `tax_behavior`, `active` FROM `ps_state`;
    
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

DROP TABLE IF EXISTS `ps_state`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_state` (
  `id_state` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_country` int(11) unsigned NOT NULL,
  `id_zone` int(11) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `iso_code` varchar(7) NOT NULL,
  `tax_behavior` smallint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_state`),
  KEY `id_country` (`id_country`),
  KEY `name` (`name`),
  KEY `id_zone` (`id_zone`)
) ENGINE=InnoDB AUTO_INCREMENT=325 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_state` WRITE;
/*!40000 ALTER TABLE `ps_state` DISABLE KEYS */;
INSERT INTO `ps_state` VALUES (1,21,9,'AA','AA',0,1);
INSERT INTO `ps_state` VALUES (2,21,9,'AE','AE',0,1);
INSERT INTO `ps_state` VALUES (3,21,9,'AP','AP',0,1);
INSERT INTO `ps_state` VALUES (4,21,9,'Alabama','AL',0,1);
INSERT INTO `ps_state` VALUES (5,21,9,'Alaska','AK',0,1);
INSERT INTO `ps_state` VALUES (6,21,9,'Arizona','AZ',0,1);
INSERT INTO `ps_state` VALUES (7,21,9,'Arkansas','AR',0,1);
INSERT INTO `ps_state` VALUES (8,21,9,'California','CA',0,1);
INSERT INTO `ps_state` VALUES (9,21,9,'Colorado','CO',0,1);
INSERT INTO `ps_state` VALUES (10,21,9,'Connecticut','CT',0,1);
INSERT INTO `ps_state` VALUES (11,21,9,'Delaware','DE',0,1);
INSERT INTO `ps_state` VALUES (12,21,9,'Florida','FL',0,1);
INSERT INTO `ps_state` VALUES (13,21,9,'Georgia','GA',0,1);
INSERT INTO `ps_state` VALUES (14,21,9,'Hawaii','HI',0,1);
INSERT INTO `ps_state` VALUES (15,21,9,'Idaho','ID',0,1);
INSERT INTO `ps_state` VALUES (16,21,9,'Illinois','IL',0,1);
INSERT INTO `ps_state` VALUES (17,21,9,'Indiana','IN',0,1);
INSERT INTO `ps_state` VALUES (18,21,9,'Iowa','IA',0,1);
INSERT INTO `ps_state` VALUES (19,21,9,'Kansas','KS',0,1);
INSERT INTO `ps_state` VALUES (20,21,9,'Kentucky','KY',0,1);
INSERT INTO `ps_state` VALUES (21,21,9,'Louisiana','LA',0,1);
INSERT INTO `ps_state` VALUES (22,21,9,'Maine','ME',0,1);
INSERT INTO `ps_state` VALUES (23,21,9,'Maryland','MD',0,1);
INSERT INTO `ps_state` VALUES (24,21,9,'Massachusetts','MA',0,1);
INSERT INTO `ps_state` VALUES (25,21,9,'Michigan','MI',0,1);
INSERT INTO `ps_state` VALUES (26,21,9,'Minnesota','MN',0,1);
INSERT INTO `ps_state` VALUES (27,21,9,'Mississippi','MS',0,1);
INSERT INTO `ps_state` VALUES (28,21,9,'Missouri','MO',0,1);
INSERT INTO `ps_state` VALUES (29,21,9,'Montana','MT',0,1);
INSERT INTO `ps_state` VALUES (30,21,9,'Nebraska','NE',0,1);
INSERT INTO `ps_state` VALUES (31,21,9,'Nevada','NV',0,1);
INSERT INTO `ps_state` VALUES (32,21,9,'New Hampshire','NH',0,1);
INSERT INTO `ps_state` VALUES (33,21,9,'New Jersey','NJ',0,1);
INSERT INTO `ps_state` VALUES (34,21,9,'New Mexico','NM',0,1);
INSERT INTO `ps_state` VALUES (35,21,9,'New York','NY',0,1);
INSERT INTO `ps_state` VALUES (36,21,9,'North Carolina','NC',0,1);
INSERT INTO `ps_state` VALUES (37,21,9,'North Dakota','ND',0,1);
INSERT INTO `ps_state` VALUES (38,21,9,'Ohio','OH',0,1);
INSERT INTO `ps_state` VALUES (39,21,9,'Oklahoma','OK',0,1);
INSERT INTO `ps_state` VALUES (40,21,9,'Oregon','OR',0,1);
INSERT INTO `ps_state` VALUES (41,21,9,'Pennsylvania','PA',0,1);
INSERT INTO `ps_state` VALUES (42,21,9,'Rhode Island','RI',0,1);
INSERT INTO `ps_state` VALUES (43,21,9,'South Carolina','SC',0,1);
INSERT INTO `ps_state` VALUES (44,21,9,'South Dakota','SD',0,1);
INSERT INTO `ps_state` VALUES (45,21,9,'Tennessee','TN',0,1);
INSERT INTO `ps_state` VALUES (46,21,9,'Texas','TX',0,1);
INSERT INTO `ps_state` VALUES (47,21,9,'Utah','UT',0,1);
INSERT INTO `ps_state` VALUES (48,21,9,'Vermont','VT',0,1);
INSERT INTO `ps_state` VALUES (49,21,9,'Virginia','VA',0,1);
INSERT INTO `ps_state` VALUES (50,21,9,'Washington','WA',0,1);
INSERT INTO `ps_state` VALUES (51,21,9,'West Virginia','WV',0,1);
INSERT INTO `ps_state` VALUES (52,21,9,'Wisconsin','WI',0,1);
INSERT INTO `ps_state` VALUES (53,21,9,'Wyoming','WY',0,1);
INSERT INTO `ps_state` VALUES (54,21,9,'Puerto Rico','PR',0,1);
INSERT INTO `ps_state` VALUES (55,21,9,'US Virgin Islands','VI',0,1);
INSERT INTO `ps_state` VALUES (56,21,9,'District of Columbia','DC',0,1);
INSERT INTO `ps_state` VALUES (57,145,11,'Aguascalientes','AGS',0,1);
INSERT INTO `ps_state` VALUES (58,145,11,'Baja California','BCN',0,1);
INSERT INTO `ps_state` VALUES (59,145,11,'Baja California Sur','BCS',0,1);
INSERT INTO `ps_state` VALUES (60,145,11,'Campeche','CAM',0,1);
INSERT INTO `ps_state` VALUES (61,145,11,'Chiapas','CHP',0,1);
INSERT INTO `ps_state` VALUES (62,145,11,'Chihuahua','CHH',0,1);
INSERT INTO `ps_state` VALUES (63,145,11,'Coahuila','COA',0,1);
INSERT INTO `ps_state` VALUES (64,145,11,'Colima','COL',0,1);
INSERT INTO `ps_state` VALUES (65,145,11,'Distrito Federal','DIF',0,1);
INSERT INTO `ps_state` VALUES (66,145,11,'Durango','DUR',0,1);
INSERT INTO `ps_state` VALUES (67,145,11,'Guanajuato','GUA',0,1);
INSERT INTO `ps_state` VALUES (68,145,11,'Guerrero','GRO',0,1);
INSERT INTO `ps_state` VALUES (69,145,11,'Hidalgo','HID',0,1);
INSERT INTO `ps_state` VALUES (70,145,11,'Jalisco','JAL',0,1);
INSERT INTO `ps_state` VALUES (71,145,11,'Estado de México','MEX',0,1);
INSERT INTO `ps_state` VALUES (72,145,11,'Michoacán','MIC',0,1);
INSERT INTO `ps_state` VALUES (73,145,11,'Morelos','MOR',0,1);
INSERT INTO `ps_state` VALUES (74,145,11,'Nayarit','NAY',0,1);
INSERT INTO `ps_state` VALUES (75,145,11,'Nuevo León','NLE',0,1);
INSERT INTO `ps_state` VALUES (76,145,11,'Oaxaca','OAX',0,1);
INSERT INTO `ps_state` VALUES (77,145,11,'Puebla','PUE',0,1);
INSERT INTO `ps_state` VALUES (78,145,11,'Querétaro','QUE',0,1);
INSERT INTO `ps_state` VALUES (79,145,11,'Quintana Roo','ROO',0,1);
INSERT INTO `ps_state` VALUES (80,145,11,'San Luis Potosí','SLP',0,1);
INSERT INTO `ps_state` VALUES (81,145,11,'Sinaloa','SIN',0,1);
INSERT INTO `ps_state` VALUES (82,145,11,'Sonora','SON',0,1);
INSERT INTO `ps_state` VALUES (83,145,11,'Tabasco','TAB',0,1);
INSERT INTO `ps_state` VALUES (84,145,11,'Tamaulipas','TAM',0,1);
INSERT INTO `ps_state` VALUES (85,145,11,'Tlaxcala','TLA',0,1);
INSERT INTO `ps_state` VALUES (86,145,11,'Veracruz','VER',0,1);
INSERT INTO `ps_state` VALUES (87,145,11,'Yucatán','YUC',0,1);
INSERT INTO `ps_state` VALUES (88,145,11,'Zacatecas','ZAC',0,1);
INSERT INTO `ps_state` VALUES (89,4,10,'Ontario','ON',0,1);
INSERT INTO `ps_state` VALUES (90,4,10,'Quebec','QC',0,1);
INSERT INTO `ps_state` VALUES (91,4,10,'British Columbia','BC',0,1);
INSERT INTO `ps_state` VALUES (92,4,10,'Alberta','AB',0,1);
INSERT INTO `ps_state` VALUES (93,4,10,'Manitoba','MB',0,1);
INSERT INTO `ps_state` VALUES (94,4,10,'Saskatchewan','SK',0,1);
INSERT INTO `ps_state` VALUES (95,4,10,'Nova Scotia','NS',0,1);
INSERT INTO `ps_state` VALUES (96,4,10,'New Brunswick','NB',0,1);
INSERT INTO `ps_state` VALUES (97,4,10,'Newfoundland and Labrador','NL',0,1);
INSERT INTO `ps_state` VALUES (98,4,10,'Prince Edward Island','PE',0,1);
INSERT INTO `ps_state` VALUES (99,4,10,'Northwest Territories','NT',0,1);
INSERT INTO `ps_state` VALUES (100,4,10,'Yukon','YT',0,1);
INSERT INTO `ps_state` VALUES (101,4,10,'Nunavut','NU',0,1);
INSERT INTO `ps_state` VALUES (102,44,6,'Buenos Aires','B',0,1);
INSERT INTO `ps_state` VALUES (103,44,6,'Catamarca','K',0,1);
INSERT INTO `ps_state` VALUES (104,44,6,'Chaco','H',0,1);
INSERT INTO `ps_state` VALUES (105,44,6,'Chubut','U',0,1);
INSERT INTO `ps_state` VALUES (106,44,6,'Ciudad de Buenos Aires','C',0,1);
INSERT INTO `ps_state` VALUES (107,44,6,'Córdoba','X',0,1);
INSERT INTO `ps_state` VALUES (108,44,6,'Corrientes','W',0,1);
INSERT INTO `ps_state` VALUES (109,44,6,'Entre Ríos','E',0,1);
INSERT INTO `ps_state` VALUES (110,44,6,'Formosa','P',0,1);
INSERT INTO `ps_state` VALUES (111,44,6,'Jujuy','Y',0,1);
INSERT INTO `ps_state` VALUES (112,44,6,'La Pampa','L',0,1);
INSERT INTO `ps_state` VALUES (113,44,6,'La Rioja','F',0,1);
INSERT INTO `ps_state` VALUES (114,44,6,'Mendoza','M',0,1);
INSERT INTO `ps_state` VALUES (115,44,6,'Misiones','N',0,1);
INSERT INTO `ps_state` VALUES (116,44,6,'Neuquén','Q',0,1);
INSERT INTO `ps_state` VALUES (117,44,6,'Río Negro','R',0,1);
INSERT INTO `ps_state` VALUES (118,44,6,'Salta','A',0,1);
INSERT INTO `ps_state` VALUES (119,44,6,'San Juan','J',0,1);
INSERT INTO `ps_state` VALUES (120,44,6,'San Luis','D',0,1);
INSERT INTO `ps_state` VALUES (121,44,6,'Santa Cruz','Z',0,1);
INSERT INTO `ps_state` VALUES (122,44,6,'Santa Fe','S',0,1);
INSERT INTO `ps_state` VALUES (123,44,6,'Santiago del Estero','G',0,1);
INSERT INTO `ps_state` VALUES (124,44,6,'Tierra del Fuego','V',0,1);
INSERT INTO `ps_state` VALUES (125,44,6,'Tucumán','T',0,1);
INSERT INTO `ps_state` VALUES (126,10,1,'Agrigento','AG',0,1);
INSERT INTO `ps_state` VALUES (127,10,1,'Alessandria','AL',0,1);
INSERT INTO `ps_state` VALUES (128,10,1,'Ancona','AN',0,1);
INSERT INTO `ps_state` VALUES (129,10,1,'Aosta','AO',0,1);
INSERT INTO `ps_state` VALUES (130,10,1,'Arezzo','AR',0,1);
INSERT INTO `ps_state` VALUES (131,10,1,'Ascoli Piceno','AP',0,1);
INSERT INTO `ps_state` VALUES (132,10,1,'Asti','AT',0,1);
INSERT INTO `ps_state` VALUES (133,10,1,'Avellino','AV',0,1);
INSERT INTO `ps_state` VALUES (134,10,1,'Bari','BA',0,1);
INSERT INTO `ps_state` VALUES (135,10,1,'Barletta-Andria-Trani','BT',0,1);
INSERT INTO `ps_state` VALUES (136,10,1,'Belluno','BL',0,1);
INSERT INTO `ps_state` VALUES (137,10,1,'Benevento','BN',0,1);
INSERT INTO `ps_state` VALUES (138,10,1,'Bergamo','BG',0,1);
INSERT INTO `ps_state` VALUES (139,10,1,'Biella','BI',0,1);
INSERT INTO `ps_state` VALUES (140,10,1,'Bologna','BO',0,1);
INSERT INTO `ps_state` VALUES (141,10,1,'Bolzano','BZ',0,1);
INSERT INTO `ps_state` VALUES (142,10,1,'Brescia','BS',0,1);
INSERT INTO `ps_state` VALUES (143,10,1,'Brindisi','BR',0,1);
INSERT INTO `ps_state` VALUES (144,10,1,'Cagliari','CA',0,1);
INSERT INTO `ps_state` VALUES (145,10,1,'Caltanissetta','CL',0,1);
INSERT INTO `ps_state` VALUES (146,10,1,'Campobasso','CB',0,1);
INSERT INTO `ps_state` VALUES (147,10,1,'Carbonia-Iglesias','CI',0,1);
INSERT INTO `ps_state` VALUES (148,10,1,'Caserta','CE',0,1);
INSERT INTO `ps_state` VALUES (149,10,1,'Catania','CT',0,1);
INSERT INTO `ps_state` VALUES (150,10,1,'Catanzaro','CZ',0,1);
INSERT INTO `ps_state` VALUES (151,10,1,'Chieti','CH',0,1);
INSERT INTO `ps_state` VALUES (152,10,1,'Como','CO',0,1);
INSERT INTO `ps_state` VALUES (153,10,1,'Cosenza','CS',0,1);
INSERT INTO `ps_state` VALUES (154,10,1,'Cremona','CR',0,1);
INSERT INTO `ps_state` VALUES (155,10,1,'Crotone','KR',0,1);
INSERT INTO `ps_state` VALUES (156,10,1,'Cuneo','CN',0,1);
INSERT INTO `ps_state` VALUES (157,10,1,'Enna','EN',0,1);
INSERT INTO `ps_state` VALUES (158,10,1,'Fermo','FM',0,1);
INSERT INTO `ps_state` VALUES (159,10,1,'Ferrara','FE',0,1);
INSERT INTO `ps_state` VALUES (160,10,1,'Firenze','FI',0,1);
INSERT INTO `ps_state` VALUES (161,10,1,'Foggia','FG',0,1);
INSERT INTO `ps_state` VALUES (162,10,1,'Forlì-Cesena','FC',0,1);
INSERT INTO `ps_state` VALUES (163,10,1,'Frosinone','FR',0,1);
INSERT INTO `ps_state` VALUES (164,10,1,'Genova','GE',0,1);
INSERT INTO `ps_state` VALUES (165,10,1,'Gorizia','GO',0,1);
INSERT INTO `ps_state` VALUES (166,10,1,'Grosseto','GR',0,1);
INSERT INTO `ps_state` VALUES (167,10,1,'Imperia','IM',0,1);
INSERT INTO `ps_state` VALUES (168,10,1,'Isernia','IS',0,1);
INSERT INTO `ps_state` VALUES (169,10,1,'L\'Aquila','AQ',0,1);
INSERT INTO `ps_state` VALUES (170,10,1,'La Spezia','SP',0,1);
INSERT INTO `ps_state` VALUES (171,10,1,'Latina','LT',0,1);
INSERT INTO `ps_state` VALUES (172,10,1,'Lecce','LE',0,1);
INSERT INTO `ps_state` VALUES (173,10,1,'Lecco','LC',0,1);
INSERT INTO `ps_state` VALUES (174,10,1,'Livorno','LI',0,1);
INSERT INTO `ps_state` VALUES (175,10,1,'Lodi','LO',0,1);
INSERT INTO `ps_state` VALUES (176,10,1,'Lucca','LU',0,1);
INSERT INTO `ps_state` VALUES (177,10,1,'Macerata','MC',0,1);
INSERT INTO `ps_state` VALUES (178,10,1,'Mantova','MN',0,1);
INSERT INTO `ps_state` VALUES (179,10,1,'Massa','MS',0,1);
INSERT INTO `ps_state` VALUES (180,10,1,'Matera','MT',0,1);
INSERT INTO `ps_state` VALUES (181,10,1,'Medio Campidano','VS',0,1);
INSERT INTO `ps_state` VALUES (182,10,1,'Messina','ME',0,1);
INSERT INTO `ps_state` VALUES (183,10,1,'Milano','MI',0,1);
INSERT INTO `ps_state` VALUES (184,10,1,'Modena','MO',0,1);
INSERT INTO `ps_state` VALUES (185,10,1,'Monza e della Brianza','MB',0,1);
INSERT INTO `ps_state` VALUES (186,10,1,'Napoli','NA',0,1);
INSERT INTO `ps_state` VALUES (187,10,1,'Novara','NO',0,1);
INSERT INTO `ps_state` VALUES (188,10,1,'Nuoro','NU',0,1);
INSERT INTO `ps_state` VALUES (189,10,1,'Ogliastra','OG',0,1);
INSERT INTO `ps_state` VALUES (190,10,1,'Olbia-Tempio','OT',0,1);
INSERT INTO `ps_state` VALUES (191,10,1,'Oristano','OR',0,1);
INSERT INTO `ps_state` VALUES (192,10,1,'Padova','PD',0,1);
INSERT INTO `ps_state` VALUES (193,10,1,'Palermo','PA',0,1);
INSERT INTO `ps_state` VALUES (194,10,1,'Parma','PR',0,1);
INSERT INTO `ps_state` VALUES (195,10,1,'Pavia','PV',0,1);
INSERT INTO `ps_state` VALUES (196,10,1,'Perugia','PG',0,1);
INSERT INTO `ps_state` VALUES (197,10,1,'Pesaro-Urbino','PU',0,1);
INSERT INTO `ps_state` VALUES (198,10,1,'Pescara','PE',0,1);
INSERT INTO `ps_state` VALUES (199,10,1,'Piacenza','PC',0,1);
INSERT INTO `ps_state` VALUES (200,10,1,'Pisa','PI',0,1);
INSERT INTO `ps_state` VALUES (201,10,1,'Pistoia','PT',0,1);
INSERT INTO `ps_state` VALUES (202,10,1,'Pordenone','PN',0,1);
INSERT INTO `ps_state` VALUES (203,10,1,'Potenza','PZ',0,1);
INSERT INTO `ps_state` VALUES (204,10,1,'Prato','PO',0,1);
INSERT INTO `ps_state` VALUES (205,10,1,'Ragusa','RG',0,1);
INSERT INTO `ps_state` VALUES (206,10,1,'Ravenna','RA',0,1);
INSERT INTO `ps_state` VALUES (207,10,1,'Reggio Calabria','RC',0,1);
INSERT INTO `ps_state` VALUES (208,10,1,'Reggio Emilia','RE',0,1);
INSERT INTO `ps_state` VALUES (209,10,1,'Rieti','RI',0,1);
INSERT INTO `ps_state` VALUES (210,10,1,'Rimini','RN',0,1);
INSERT INTO `ps_state` VALUES (211,10,1,'Roma','RM',0,1);
INSERT INTO `ps_state` VALUES (212,10,1,'Rovigo','RO',0,1);
INSERT INTO `ps_state` VALUES (213,10,1,'Salerno','SA',0,1);
INSERT INTO `ps_state` VALUES (214,10,1,'Sassari','SS',0,1);
INSERT INTO `ps_state` VALUES (215,10,1,'Savona','SV',0,1);
INSERT INTO `ps_state` VALUES (216,10,1,'Siena','SI',0,1);
INSERT INTO `ps_state` VALUES (217,10,1,'Siracusa','SR',0,1);
INSERT INTO `ps_state` VALUES (218,10,1,'Sondrio','SO',0,1);
INSERT INTO `ps_state` VALUES (219,10,1,'Taranto','TA',0,1);
INSERT INTO `ps_state` VALUES (220,10,1,'Teramo','TE',0,1);
INSERT INTO `ps_state` VALUES (221,10,1,'Terni','TR',0,1);
INSERT INTO `ps_state` VALUES (222,10,1,'Torino','TO',0,1);
INSERT INTO `ps_state` VALUES (223,10,1,'Trapani','TP',0,1);
INSERT INTO `ps_state` VALUES (224,10,1,'Trento','TN',0,1);
INSERT INTO `ps_state` VALUES (225,10,1,'Treviso','TV',0,1);
INSERT INTO `ps_state` VALUES (226,10,1,'Trieste','TS',0,1);
INSERT INTO `ps_state` VALUES (227,10,1,'Udine','UD',0,1);
INSERT INTO `ps_state` VALUES (228,10,1,'Varese','VA',0,1);
INSERT INTO `ps_state` VALUES (229,10,1,'Venezia','VE',0,1);
INSERT INTO `ps_state` VALUES (230,10,1,'Verbano-Cusio-Ossola','VB',0,1);
INSERT INTO `ps_state` VALUES (231,10,1,'Vercelli','VC',0,1);
INSERT INTO `ps_state` VALUES (232,10,1,'Verona','VR',0,1);
INSERT INTO `ps_state` VALUES (233,10,1,'Vibo Valentia','VV',0,1);
INSERT INTO `ps_state` VALUES (234,10,1,'Vicenza','VI',0,1);
INSERT INTO `ps_state` VALUES (235,10,1,'Viterbo','VT',0,1);
INSERT INTO `ps_state` VALUES (236,111,3,'Aceh','ID-AC',0,1);
INSERT INTO `ps_state` VALUES (237,111,3,'Bali','ID-BA',0,1);
INSERT INTO `ps_state` VALUES (238,111,3,'Banten','ID-BT',0,1);
INSERT INTO `ps_state` VALUES (239,111,3,'Bengkulu','ID-BE',0,1);
INSERT INTO `ps_state` VALUES (240,111,3,'Gorontalo','ID-GO',0,1);
INSERT INTO `ps_state` VALUES (241,111,3,'Jakarta','ID-JK',0,1);
INSERT INTO `ps_state` VALUES (242,111,3,'Jambi','ID-JA',0,1);
INSERT INTO `ps_state` VALUES (243,111,3,'Jawa Barat','ID-JB',0,1);
INSERT INTO `ps_state` VALUES (244,111,3,'Jawa Tengah','ID-JT',0,1);
INSERT INTO `ps_state` VALUES (245,111,3,'Jawa Timur','ID-JI',0,1);
INSERT INTO `ps_state` VALUES (246,111,3,'Kalimantan Barat','ID-KB',0,1);
INSERT INTO `ps_state` VALUES (247,111,3,'Kalimantan Selatan','ID-KS',0,1);
INSERT INTO `ps_state` VALUES (248,111,3,'Kalimantan Tengah','ID-KT',0,1);
INSERT INTO `ps_state` VALUES (249,111,3,'Kalimantan Timur','ID-KI',0,1);
INSERT INTO `ps_state` VALUES (250,111,3,'Kalimantan Utara','ID-KU',0,1);
INSERT INTO `ps_state` VALUES (251,111,3,'Kepulauan Bangka Belitug','ID-BB',0,1);
INSERT INTO `ps_state` VALUES (252,111,3,'Kepulauan Riau','ID-KR',0,1);
INSERT INTO `ps_state` VALUES (253,111,3,'Lampung','ID-LA',0,1);
INSERT INTO `ps_state` VALUES (254,111,3,'Maluku','ID-MA',0,1);
INSERT INTO `ps_state` VALUES (255,111,3,'Maluku Utara','ID-MU',0,1);
INSERT INTO `ps_state` VALUES (256,111,3,'Nusa Tengara Barat','ID-NB',0,1);
INSERT INTO `ps_state` VALUES (257,111,3,'Nusa Tenggara Timur','ID-NT',0,1);
INSERT INTO `ps_state` VALUES (258,111,3,'Papua','ID-PA',0,1);
INSERT INTO `ps_state` VALUES (259,111,3,'Papua Barat','ID-PB',0,1);
INSERT INTO `ps_state` VALUES (260,111,3,'Riau','ID-RI',0,1);
INSERT INTO `ps_state` VALUES (261,111,3,'Sulawesi Barat','ID-SR',0,1);
INSERT INTO `ps_state` VALUES (262,111,3,'Sulawesi Selatan','ID-SN',0,1);
INSERT INTO `ps_state` VALUES (263,111,3,'Sulawesi Tengah','ID-ST',0,1);
INSERT INTO `ps_state` VALUES (264,111,3,'Sulawesi Tenggara','ID-SG',0,1);
INSERT INTO `ps_state` VALUES (265,111,3,'Sulawesi Utara','ID-SA',0,1);
INSERT INTO `ps_state` VALUES (266,111,3,'Sumatera Barat','ID-SB',0,1);
INSERT INTO `ps_state` VALUES (267,111,3,'Sumatera Selatan','ID-SS',0,1);
INSERT INTO `ps_state` VALUES (268,111,3,'Sumatera Utara','ID-SU',0,1);
INSERT INTO `ps_state` VALUES (269,111,3,'Yogyakarta','ID-YO',0,1);
INSERT INTO `ps_state` VALUES (270,11,3,'Aichi','23',0,1);
INSERT INTO `ps_state` VALUES (271,11,3,'Akita','05',0,1);
INSERT INTO `ps_state` VALUES (272,11,3,'Aomori','02',0,1);
INSERT INTO `ps_state` VALUES (273,11,3,'Chiba','12',0,1);
INSERT INTO `ps_state` VALUES (274,11,3,'Ehime','38',0,1);
INSERT INTO `ps_state` VALUES (275,11,3,'Fukui','18',0,1);
INSERT INTO `ps_state` VALUES (276,11,3,'Fukuoka','40',0,1);
INSERT INTO `ps_state` VALUES (277,11,3,'Fukushima','07',0,1);
INSERT INTO `ps_state` VALUES (278,11,3,'Gifu','21',0,1);
INSERT INTO `ps_state` VALUES (279,11,3,'Gunma','10',0,1);
INSERT INTO `ps_state` VALUES (280,11,3,'Hiroshima','34',0,1);
INSERT INTO `ps_state` VALUES (281,11,3,'Hokkaido','01',0,1);
INSERT INTO `ps_state` VALUES (282,11,3,'Hyogo','28',0,1);
INSERT INTO `ps_state` VALUES (283,11,3,'Ibaraki','08',0,1);
INSERT INTO `ps_state` VALUES (284,11,3,'Ishikawa','17',0,1);
INSERT INTO `ps_state` VALUES (285,11,3,'Iwate','03',0,1);
INSERT INTO `ps_state` VALUES (286,11,3,'Kagawa','37',0,1);
INSERT INTO `ps_state` VALUES (287,11,3,'Kagoshima','46',0,1);
INSERT INTO `ps_state` VALUES (288,11,3,'Kanagawa','14',0,1);
INSERT INTO `ps_state` VALUES (289,11,3,'Kochi','39',0,1);
INSERT INTO `ps_state` VALUES (290,11,3,'Kumamoto','43',0,1);
INSERT INTO `ps_state` VALUES (291,11,3,'Kyoto','26',0,1);
INSERT INTO `ps_state` VALUES (292,11,3,'Mie','24',0,1);
INSERT INTO `ps_state` VALUES (293,11,3,'Miyagi','04',0,1);
INSERT INTO `ps_state` VALUES (294,11,3,'Miyazaki','45',0,1);
INSERT INTO `ps_state` VALUES (295,11,3,'Nagano','20',0,1);
INSERT INTO `ps_state` VALUES (296,11,3,'Nagasaki','42',0,1);
INSERT INTO `ps_state` VALUES (297,11,3,'Nara','29',0,1);
INSERT INTO `ps_state` VALUES (298,11,3,'Niigata','15',0,1);
INSERT INTO `ps_state` VALUES (299,11,3,'Oita','44',0,1);
INSERT INTO `ps_state` VALUES (300,11,3,'Okayama','33',0,1);
INSERT INTO `ps_state` VALUES (301,11,3,'Okinawa','47',0,1);
INSERT INTO `ps_state` VALUES (302,11,3,'Osaka','27',0,1);
INSERT INTO `ps_state` VALUES (303,11,3,'Saga','41',0,1);
INSERT INTO `ps_state` VALUES (304,11,3,'Saitama','11',0,1);
INSERT INTO `ps_state` VALUES (305,11,3,'Shiga','25',0,1);
INSERT INTO `ps_state` VALUES (306,11,3,'Shimane','32',0,1);
INSERT INTO `ps_state` VALUES (307,11,3,'Shizuoka','22',0,1);
INSERT INTO `ps_state` VALUES (308,11,3,'Tochigi','09',0,1);
INSERT INTO `ps_state` VALUES (309,11,3,'Tokushima','36',0,1);
INSERT INTO `ps_state` VALUES (310,11,3,'Tokyo','13',0,1);
INSERT INTO `ps_state` VALUES (311,11,3,'Tottori','31',0,1);
INSERT INTO `ps_state` VALUES (312,11,3,'Toyama','16',0,1);
INSERT INTO `ps_state` VALUES (313,11,3,'Wakayama','30',0,1);
INSERT INTO `ps_state` VALUES (314,11,3,'Yamagata','06',0,1);
INSERT INTO `ps_state` VALUES (315,11,3,'Yamaguchi','35',0,1);
INSERT INTO `ps_state` VALUES (316,11,3,'Yamanashi','19',0,1);
INSERT INTO `ps_state` VALUES (317,24,5,'Australian Capital Territory','ACT',0,1);
INSERT INTO `ps_state` VALUES (318,24,5,'New South Wales','NSW',0,1);
INSERT INTO `ps_state` VALUES (319,24,5,'Northern Territory','NT',0,1);
INSERT INTO `ps_state` VALUES (320,24,5,'Queensland','QLD',0,1);
INSERT INTO `ps_state` VALUES (321,24,5,'South Australia','SA',0,1);
INSERT INTO `ps_state` VALUES (322,24,5,'Tasmania','TAS',0,1);
INSERT INTO `ps_state` VALUES (323,24,5,'Victoria','VIC',0,1);
INSERT INTO `ps_state` VALUES (324,24,5,'Western Australia','WA',0,1);

/*!40000 ALTER TABLE `ps_state` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_stock`, `id_warehouse`, `id_product`, `id_product_attribute`, `reference`, `ean13`, `isbn`, `upc`, `physical_quantity`, `usable_quantity`, `price_te` FROM `ps_stock`;
    
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

DROP TABLE IF EXISTS `ps_stock`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_stock` (
  `id_stock` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_warehouse` int(11) unsigned NOT NULL,
  `id_product` int(11) unsigned NOT NULL,
  `id_product_attribute` int(11) unsigned NOT NULL,
  `reference` varchar(64) DEFAULT NULL,
  `ean13` varchar(13) DEFAULT NULL,
  `isbn` varchar(32) DEFAULT NULL,
  `upc` varchar(12) DEFAULT NULL,
  `physical_quantity` int(11) unsigned NOT NULL,
  `usable_quantity` int(11) unsigned NOT NULL,
  `price_te` decimal(20,6) DEFAULT '0.000000',
  PRIMARY KEY (`id_stock`),
  KEY `id_warehouse` (`id_warehouse`),
  KEY `id_product` (`id_product`),
  KEY `id_product_attribute` (`id_product_attribute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_stock` WRITE;
/*!40000 ALTER TABLE `ps_stock` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_stock` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_stripe_capture`, `id_payment_intent`, `id_order`, `expired`, `date_catch`, `date_authorize` FROM `ps_stripe_capture`;
    
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

DROP TABLE IF EXISTS `ps_stripe_capture`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_stripe_capture` (
  `id_stripe_capture` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment_intent` varchar(40) NOT NULL,
  `id_order` int(10) NOT NULL,
  `expired` tinyint(1) unsigned NOT NULL,
  `date_catch` datetime NOT NULL,
  `date_authorize` datetime NOT NULL,
  PRIMARY KEY (`id_stripe_capture`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_stripe_capture` WRITE;
/*!40000 ALTER TABLE `ps_stripe_capture` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_stripe_capture` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_supplier`, `name`, `date_add`, `date_upd`, `active` FROM `ps_supplier`;
    
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

DROP TABLE IF EXISTS `ps_supplier`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_supplier` (
  `id_supplier` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_supplier` WRITE;
/*!40000 ALTER TABLE `ps_supplier` DISABLE KEYS */;
INSERT INTO `ps_supplier` VALUES (2,'Collection Coutou','2020-04-28 22:48:54','2020-04-28 22:49:04',1);
INSERT INTO `ps_supplier` VALUES (3,'Bastien Industries','2020-04-29 15:02:36','2020-04-29 15:02:36',1);
INSERT INTO `ps_supplier` VALUES (4,'Laurentian Chief','2020-04-29 15:04:14','2020-05-01 17:03:41',1);

/*!40000 ALTER TABLE `ps_supplier` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_supplier`, `id_lang`, `description`, `meta_title`, `meta_keywords`, `meta_description` FROM `ps_supplier_lang`;
    
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

DROP TABLE IF EXISTS `ps_supplier_lang`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_supplier_lang` (
  `id_supplier` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `description` text,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id_supplier`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_supplier_lang` WRITE;
/*!40000 ALTER TABLE `ps_supplier_lang` DISABLE KEYS */;
INSERT INTO `ps_supplier_lang` VALUES (2,1,'','','','');
INSERT INTO `ps_supplier_lang` VALUES (2,3,'','','','');
INSERT INTO `ps_supplier_lang` VALUES (3,1,'<p>Hiawatha 100% Autochtones</p>','','','');
INSERT INTO `ps_supplier_lang` VALUES (3,3,'','','','');
INSERT INTO `ps_supplier_lang` VALUES (4,1,'<p>Mocassins Slippers Mukluks</p>','','','');
INSERT INTO `ps_supplier_lang` VALUES (4,3,'','','','');

/*!40000 ALTER TABLE `ps_supplier_lang` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_supplier`, `id_shop` FROM `ps_supplier_shop`;
    
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

DROP TABLE IF EXISTS `ps_supplier_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_supplier_shop` (
  `id_supplier` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_supplier`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_supplier_shop` WRITE;
/*!40000 ALTER TABLE `ps_supplier_shop` DISABLE KEYS */;
INSERT INTO `ps_supplier_shop` VALUES (2,1);
INSERT INTO `ps_supplier_shop` VALUES (3,1);
INSERT INTO `ps_supplier_shop` VALUES (4,1);

/*!40000 ALTER TABLE `ps_supplier_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_supply_order`, `id_supplier`, `supplier_name`, `id_lang`, `id_warehouse`, `id_supply_order_state`, `id_currency`, `id_ref_currency`, `reference`, `date_add`, `date_upd`, `date_delivery_expected`, `total_te`, `total_with_discount_te`, `total_tax`, `total_ti`, `discount_rate`, `discount_value_te`, `is_template` FROM `ps_supply_order`;
    
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

DROP TABLE IF EXISTS `ps_supply_order`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_supply_order` (
  `id_supply_order` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_supplier` int(11) unsigned NOT NULL,
  `supplier_name` varchar(64) NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `id_warehouse` int(11) unsigned NOT NULL,
  `id_supply_order_state` int(11) unsigned NOT NULL,
  `id_currency` int(11) unsigned NOT NULL,
  `id_ref_currency` int(11) unsigned NOT NULL,
  `reference` varchar(64) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `date_delivery_expected` datetime DEFAULT NULL,
  `total_te` decimal(20,6) DEFAULT '0.000000',
  `total_with_discount_te` decimal(20,6) DEFAULT '0.000000',
  `total_tax` decimal(20,6) DEFAULT '0.000000',
  `total_ti` decimal(20,6) DEFAULT '0.000000',
  `discount_rate` decimal(20,6) DEFAULT '0.000000',
  `discount_value_te` decimal(20,6) DEFAULT '0.000000',
  `is_template` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_supply_order`),
  KEY `id_supplier` (`id_supplier`),
  KEY `id_warehouse` (`id_warehouse`),
  KEY `reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_supply_order` WRITE;
/*!40000 ALTER TABLE `ps_supply_order` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_supply_order` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_supply_order_detail`, `id_supply_order`, `id_currency`, `id_product`, `id_product_attribute`, `reference`, `supplier_reference`, `name`, `ean13`, `isbn`, `upc`, `exchange_rate`, `unit_price_te`, `quantity_expected`, `quantity_received`, `price_te`, `discount_rate`, `discount_value_te`, `price_with_discount_te`, `tax_rate`, `tax_value`, `price_ti`, `tax_value_with_order_discount`, `price_with_order_discount_te` FROM `ps_supply_order_detail`;
    
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

DROP TABLE IF EXISTS `ps_supply_order_detail`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_supply_order_detail` (
  `id_supply_order_detail` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_supply_order` int(11) unsigned NOT NULL,
  `id_currency` int(11) unsigned NOT NULL,
  `id_product` int(11) unsigned NOT NULL,
  `id_product_attribute` int(11) unsigned NOT NULL,
  `reference` varchar(64) NOT NULL,
  `supplier_reference` varchar(64) NOT NULL,
  `name` varchar(128) NOT NULL,
  `ean13` varchar(13) DEFAULT NULL,
  `isbn` varchar(32) DEFAULT NULL,
  `upc` varchar(12) DEFAULT NULL,
  `exchange_rate` decimal(20,6) DEFAULT '0.000000',
  `unit_price_te` decimal(20,6) DEFAULT '0.000000',
  `quantity_expected` int(11) unsigned NOT NULL,
  `quantity_received` int(11) unsigned NOT NULL,
  `price_te` decimal(20,6) DEFAULT '0.000000',
  `discount_rate` decimal(20,6) DEFAULT '0.000000',
  `discount_value_te` decimal(20,6) DEFAULT '0.000000',
  `price_with_discount_te` decimal(20,6) DEFAULT '0.000000',
  `tax_rate` decimal(20,6) DEFAULT '0.000000',
  `tax_value` decimal(20,6) DEFAULT '0.000000',
  `price_ti` decimal(20,6) DEFAULT '0.000000',
  `tax_value_with_order_discount` decimal(20,6) DEFAULT '0.000000',
  `price_with_order_discount_te` decimal(20,6) DEFAULT '0.000000',
  PRIMARY KEY (`id_supply_order_detail`),
  KEY `id_supply_order` (`id_supply_order`,`id_product`),
  KEY `id_product_attribute` (`id_product_attribute`),
  KEY `id_product_product_attribute` (`id_product`,`id_product_attribute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_supply_order_detail` WRITE;
/*!40000 ALTER TABLE `ps_supply_order_detail` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_supply_order_detail` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_supply_order_history`, `id_supply_order`, `id_employee`, `employee_lastname`, `employee_firstname`, `id_state`, `date_add` FROM `ps_supply_order_history`;
    
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

DROP TABLE IF EXISTS `ps_supply_order_history`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_supply_order_history` (
  `id_supply_order_history` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_supply_order` int(11) unsigned NOT NULL,
  `id_employee` int(11) unsigned NOT NULL,
  `employee_lastname` varchar(255) DEFAULT '',
  `employee_firstname` varchar(255) DEFAULT '',
  `id_state` int(11) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_supply_order_history`),
  KEY `id_supply_order` (`id_supply_order`),
  KEY `id_employee` (`id_employee`),
  KEY `id_state` (`id_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_supply_order_history` WRITE;
/*!40000 ALTER TABLE `ps_supply_order_history` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_supply_order_history` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_supply_order_receipt_history`, `id_supply_order_detail`, `id_employee`, `employee_lastname`, `employee_firstname`, `id_supply_order_state`, `quantity`, `date_add` FROM `ps_supply_order_receipt_history`;
    
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

DROP TABLE IF EXISTS `ps_supply_order_receipt_history`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_supply_order_receipt_history` (
  `id_supply_order_receipt_history` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_supply_order_detail` int(11) unsigned NOT NULL,
  `id_employee` int(11) unsigned NOT NULL,
  `employee_lastname` varchar(255) DEFAULT '',
  `employee_firstname` varchar(255) DEFAULT '',
  `id_supply_order_state` int(11) unsigned NOT NULL,
  `quantity` int(11) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_supply_order_receipt_history`),
  KEY `id_supply_order_detail` (`id_supply_order_detail`),
  KEY `id_supply_order_state` (`id_supply_order_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_supply_order_receipt_history` WRITE;
/*!40000 ALTER TABLE `ps_supply_order_receipt_history` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_supply_order_receipt_history` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_supply_order_state`, `delivery_note`, `editable`, `receipt_state`, `pending_receipt`, `enclosed`, `color` FROM `ps_supply_order_state`;
    
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

DROP TABLE IF EXISTS `ps_supply_order_state`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_supply_order_state` (
  `id_supply_order_state` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_note` tinyint(1) NOT NULL DEFAULT '0',
  `editable` tinyint(1) NOT NULL DEFAULT '0',
  `receipt_state` tinyint(1) NOT NULL DEFAULT '0',
  `pending_receipt` tinyint(1) NOT NULL DEFAULT '0',
  `enclosed` tinyint(1) NOT NULL DEFAULT '0',
  `color` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id_supply_order_state`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_supply_order_state` WRITE;
/*!40000 ALTER TABLE `ps_supply_order_state` DISABLE KEYS */;
INSERT INTO `ps_supply_order_state` VALUES (1,0,1,0,0,0,'#faab00');
INSERT INTO `ps_supply_order_state` VALUES (2,1,0,0,0,0,'#273cff');
INSERT INTO `ps_supply_order_state` VALUES (3,0,0,0,1,0,'#ff37f5');
INSERT INTO `ps_supply_order_state` VALUES (4,0,0,1,1,0,'#ff3e33');
INSERT INTO `ps_supply_order_state` VALUES (5,0,0,1,0,1,'#00d60c');
INSERT INTO `ps_supply_order_state` VALUES (6,0,0,0,0,1,'#666666');

/*!40000 ALTER TABLE `ps_supply_order_state` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_tab_module_preference`, `id_employee`, `id_tab`, `module` FROM `ps_tab_module_preference`;
    
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

DROP TABLE IF EXISTS `ps_tab_module_preference`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_tab_module_preference` (
  `id_tab_module_preference` int(11) NOT NULL AUTO_INCREMENT,
  `id_employee` int(11) NOT NULL,
  `id_tab` int(11) NOT NULL,
  `module` varchar(255) NOT NULL,
  PRIMARY KEY (`id_tab_module_preference`),
  UNIQUE KEY `employee_module` (`id_employee`,`id_tab`,`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_tab_module_preference` WRITE;
/*!40000 ALTER TABLE `ps_tab_module_preference` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_tab_module_preference` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_timezone`, `name` FROM `ps_timezone`;
    
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

DROP TABLE IF EXISTS `ps_timezone`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_timezone` (
  `id_timezone` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id_timezone`)
) ENGINE=InnoDB AUTO_INCREMENT=561 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_timezone` WRITE;
/*!40000 ALTER TABLE `ps_timezone` DISABLE KEYS */;
INSERT INTO `ps_timezone` VALUES (1,'Africa/Abidjan');
INSERT INTO `ps_timezone` VALUES (2,'Africa/Accra');
INSERT INTO `ps_timezone` VALUES (3,'Africa/Addis_Ababa');
INSERT INTO `ps_timezone` VALUES (4,'Africa/Algiers');
INSERT INTO `ps_timezone` VALUES (5,'Africa/Asmara');
INSERT INTO `ps_timezone` VALUES (6,'Africa/Asmera');
INSERT INTO `ps_timezone` VALUES (7,'Africa/Bamako');
INSERT INTO `ps_timezone` VALUES (8,'Africa/Bangui');
INSERT INTO `ps_timezone` VALUES (9,'Africa/Banjul');
INSERT INTO `ps_timezone` VALUES (10,'Africa/Bissau');
INSERT INTO `ps_timezone` VALUES (11,'Africa/Blantyre');
INSERT INTO `ps_timezone` VALUES (12,'Africa/Brazzaville');
INSERT INTO `ps_timezone` VALUES (13,'Africa/Bujumbura');
INSERT INTO `ps_timezone` VALUES (14,'Africa/Cairo');
INSERT INTO `ps_timezone` VALUES (15,'Africa/Casablanca');
INSERT INTO `ps_timezone` VALUES (16,'Africa/Ceuta');
INSERT INTO `ps_timezone` VALUES (17,'Africa/Conakry');
INSERT INTO `ps_timezone` VALUES (18,'Africa/Dakar');
INSERT INTO `ps_timezone` VALUES (19,'Africa/Dar_es_Salaam');
INSERT INTO `ps_timezone` VALUES (20,'Africa/Djibouti');
INSERT INTO `ps_timezone` VALUES (21,'Africa/Douala');
INSERT INTO `ps_timezone` VALUES (22,'Africa/El_Aaiun');
INSERT INTO `ps_timezone` VALUES (23,'Africa/Freetown');
INSERT INTO `ps_timezone` VALUES (24,'Africa/Gaborone');
INSERT INTO `ps_timezone` VALUES (25,'Africa/Harare');
INSERT INTO `ps_timezone` VALUES (26,'Africa/Johannesburg');
INSERT INTO `ps_timezone` VALUES (27,'Africa/Kampala');
INSERT INTO `ps_timezone` VALUES (28,'Africa/Khartoum');
INSERT INTO `ps_timezone` VALUES (29,'Africa/Kigali');
INSERT INTO `ps_timezone` VALUES (30,'Africa/Kinshasa');
INSERT INTO `ps_timezone` VALUES (31,'Africa/Lagos');
INSERT INTO `ps_timezone` VALUES (32,'Africa/Libreville');
INSERT INTO `ps_timezone` VALUES (33,'Africa/Lome');
INSERT INTO `ps_timezone` VALUES (34,'Africa/Luanda');
INSERT INTO `ps_timezone` VALUES (35,'Africa/Lubumbashi');
INSERT INTO `ps_timezone` VALUES (36,'Africa/Lusaka');
INSERT INTO `ps_timezone` VALUES (37,'Africa/Malabo');
INSERT INTO `ps_timezone` VALUES (38,'Africa/Maputo');
INSERT INTO `ps_timezone` VALUES (39,'Africa/Maseru');
INSERT INTO `ps_timezone` VALUES (40,'Africa/Mbabane');
INSERT INTO `ps_timezone` VALUES (41,'Africa/Mogadishu');
INSERT INTO `ps_timezone` VALUES (42,'Africa/Monrovia');
INSERT INTO `ps_timezone` VALUES (43,'Africa/Nairobi');
INSERT INTO `ps_timezone` VALUES (44,'Africa/Ndjamena');
INSERT INTO `ps_timezone` VALUES (45,'Africa/Niamey');
INSERT INTO `ps_timezone` VALUES (46,'Africa/Nouakchott');
INSERT INTO `ps_timezone` VALUES (47,'Africa/Ouagadougou');
INSERT INTO `ps_timezone` VALUES (48,'Africa/Porto-Novo');
INSERT INTO `ps_timezone` VALUES (49,'Africa/Sao_Tome');
INSERT INTO `ps_timezone` VALUES (50,'Africa/Timbuktu');
INSERT INTO `ps_timezone` VALUES (51,'Africa/Tripoli');
INSERT INTO `ps_timezone` VALUES (52,'Africa/Tunis');
INSERT INTO `ps_timezone` VALUES (53,'Africa/Windhoek');
INSERT INTO `ps_timezone` VALUES (54,'America/Adak');
INSERT INTO `ps_timezone` VALUES (55,'America/Anchorage ');
INSERT INTO `ps_timezone` VALUES (56,'America/Anguilla');
INSERT INTO `ps_timezone` VALUES (57,'America/Antigua');
INSERT INTO `ps_timezone` VALUES (58,'America/Araguaina');
INSERT INTO `ps_timezone` VALUES (59,'America/Argentina/Buenos_Aires');
INSERT INTO `ps_timezone` VALUES (60,'America/Argentina/Catamarca');
INSERT INTO `ps_timezone` VALUES (61,'America/Argentina/ComodRivadavia');
INSERT INTO `ps_timezone` VALUES (62,'America/Argentina/Cordoba');
INSERT INTO `ps_timezone` VALUES (63,'America/Argentina/Jujuy');
INSERT INTO `ps_timezone` VALUES (64,'America/Argentina/La_Rioja');
INSERT INTO `ps_timezone` VALUES (65,'America/Argentina/Mendoza');
INSERT INTO `ps_timezone` VALUES (66,'America/Argentina/Rio_Gallegos');
INSERT INTO `ps_timezone` VALUES (67,'America/Argentina/Salta');
INSERT INTO `ps_timezone` VALUES (68,'America/Argentina/San_Juan');
INSERT INTO `ps_timezone` VALUES (69,'America/Argentina/San_Luis');
INSERT INTO `ps_timezone` VALUES (70,'America/Argentina/Tucuman');
INSERT INTO `ps_timezone` VALUES (71,'America/Argentina/Ushuaia');
INSERT INTO `ps_timezone` VALUES (72,'America/Aruba');
INSERT INTO `ps_timezone` VALUES (73,'America/Asuncion');
INSERT INTO `ps_timezone` VALUES (74,'America/Atikokan');
INSERT INTO `ps_timezone` VALUES (75,'America/Atka');
INSERT INTO `ps_timezone` VALUES (76,'America/Bahia');
INSERT INTO `ps_timezone` VALUES (77,'America/Barbados');
INSERT INTO `ps_timezone` VALUES (78,'America/Belem');
INSERT INTO `ps_timezone` VALUES (79,'America/Belize');
INSERT INTO `ps_timezone` VALUES (80,'America/Blanc-Sablon');
INSERT INTO `ps_timezone` VALUES (81,'America/Boa_Vista');
INSERT INTO `ps_timezone` VALUES (82,'America/Bogota');
INSERT INTO `ps_timezone` VALUES (83,'America/Boise');
INSERT INTO `ps_timezone` VALUES (84,'America/Buenos_Aires');
INSERT INTO `ps_timezone` VALUES (85,'America/Cambridge_Bay');
INSERT INTO `ps_timezone` VALUES (86,'America/Campo_Grande');
INSERT INTO `ps_timezone` VALUES (87,'America/Cancun');
INSERT INTO `ps_timezone` VALUES (88,'America/Caracas');
INSERT INTO `ps_timezone` VALUES (89,'America/Catamarca');
INSERT INTO `ps_timezone` VALUES (90,'America/Cayenne');
INSERT INTO `ps_timezone` VALUES (91,'America/Cayman');
INSERT INTO `ps_timezone` VALUES (92,'America/Chicago');
INSERT INTO `ps_timezone` VALUES (93,'America/Chihuahua');
INSERT INTO `ps_timezone` VALUES (94,'America/Coral_Harbour');
INSERT INTO `ps_timezone` VALUES (95,'America/Cordoba');
INSERT INTO `ps_timezone` VALUES (96,'America/Costa_Rica');
INSERT INTO `ps_timezone` VALUES (97,'America/Cuiaba');
INSERT INTO `ps_timezone` VALUES (98,'America/Curacao');
INSERT INTO `ps_timezone` VALUES (99,'America/Danmarkshavn');
INSERT INTO `ps_timezone` VALUES (100,'America/Dawson');
INSERT INTO `ps_timezone` VALUES (101,'America/Dawson_Creek');
INSERT INTO `ps_timezone` VALUES (102,'America/Denver');
INSERT INTO `ps_timezone` VALUES (103,'America/Detroit');
INSERT INTO `ps_timezone` VALUES (104,'America/Dominica');
INSERT INTO `ps_timezone` VALUES (105,'America/Edmonton');
INSERT INTO `ps_timezone` VALUES (106,'America/Eirunepe');
INSERT INTO `ps_timezone` VALUES (107,'America/El_Salvador');
INSERT INTO `ps_timezone` VALUES (108,'America/Ensenada');
INSERT INTO `ps_timezone` VALUES (109,'America/Fort_Wayne');
INSERT INTO `ps_timezone` VALUES (110,'America/Fortaleza');
INSERT INTO `ps_timezone` VALUES (111,'America/Glace_Bay');
INSERT INTO `ps_timezone` VALUES (112,'America/Godthab');
INSERT INTO `ps_timezone` VALUES (113,'America/Goose_Bay');
INSERT INTO `ps_timezone` VALUES (114,'America/Grand_Turk');
INSERT INTO `ps_timezone` VALUES (115,'America/Grenada');
INSERT INTO `ps_timezone` VALUES (116,'America/Guadeloupe');
INSERT INTO `ps_timezone` VALUES (117,'America/Guatemala');
INSERT INTO `ps_timezone` VALUES (118,'America/Guayaquil');
INSERT INTO `ps_timezone` VALUES (119,'America/Guyana');
INSERT INTO `ps_timezone` VALUES (120,'America/Halifax');
INSERT INTO `ps_timezone` VALUES (121,'America/Havana');
INSERT INTO `ps_timezone` VALUES (122,'America/Hermosillo');
INSERT INTO `ps_timezone` VALUES (123,'America/Indiana/Indianapolis');
INSERT INTO `ps_timezone` VALUES (124,'America/Indiana/Knox');
INSERT INTO `ps_timezone` VALUES (125,'America/Indiana/Marengo');
INSERT INTO `ps_timezone` VALUES (126,'America/Indiana/Petersburg');
INSERT INTO `ps_timezone` VALUES (127,'America/Indiana/Tell_City');
INSERT INTO `ps_timezone` VALUES (128,'America/Indiana/Vevay');
INSERT INTO `ps_timezone` VALUES (129,'America/Indiana/Vincennes');
INSERT INTO `ps_timezone` VALUES (130,'America/Indiana/Winamac');
INSERT INTO `ps_timezone` VALUES (131,'America/Indianapolis');
INSERT INTO `ps_timezone` VALUES (132,'America/Inuvik');
INSERT INTO `ps_timezone` VALUES (133,'America/Iqaluit');
INSERT INTO `ps_timezone` VALUES (134,'America/Jamaica');
INSERT INTO `ps_timezone` VALUES (135,'America/Jujuy');
INSERT INTO `ps_timezone` VALUES (136,'America/Juneau');
INSERT INTO `ps_timezone` VALUES (137,'America/Kentucky/Louisville');
INSERT INTO `ps_timezone` VALUES (138,'America/Kentucky/Monticello');
INSERT INTO `ps_timezone` VALUES (139,'America/Knox_IN');
INSERT INTO `ps_timezone` VALUES (140,'America/La_Paz');
INSERT INTO `ps_timezone` VALUES (141,'America/Lima');
INSERT INTO `ps_timezone` VALUES (142,'America/Los_Angeles');
INSERT INTO `ps_timezone` VALUES (143,'America/Louisville');
INSERT INTO `ps_timezone` VALUES (144,'America/Maceio');
INSERT INTO `ps_timezone` VALUES (145,'America/Managua');
INSERT INTO `ps_timezone` VALUES (146,'America/Manaus');
INSERT INTO `ps_timezone` VALUES (147,'America/Marigot');
INSERT INTO `ps_timezone` VALUES (148,'America/Martinique');
INSERT INTO `ps_timezone` VALUES (149,'America/Mazatlan');
INSERT INTO `ps_timezone` VALUES (150,'America/Mendoza');
INSERT INTO `ps_timezone` VALUES (151,'America/Menominee');
INSERT INTO `ps_timezone` VALUES (152,'America/Merida');
INSERT INTO `ps_timezone` VALUES (153,'America/Mexico_City');
INSERT INTO `ps_timezone` VALUES (154,'America/Miquelon');
INSERT INTO `ps_timezone` VALUES (155,'America/Moncton');
INSERT INTO `ps_timezone` VALUES (156,'America/Monterrey');
INSERT INTO `ps_timezone` VALUES (157,'America/Montevideo');
INSERT INTO `ps_timezone` VALUES (158,'America/Montreal');
INSERT INTO `ps_timezone` VALUES (159,'America/Montserrat');
INSERT INTO `ps_timezone` VALUES (160,'America/Nassau');
INSERT INTO `ps_timezone` VALUES (161,'America/New_York');
INSERT INTO `ps_timezone` VALUES (162,'America/Nipigon');
INSERT INTO `ps_timezone` VALUES (163,'America/Nome');
INSERT INTO `ps_timezone` VALUES (164,'America/Noronha');
INSERT INTO `ps_timezone` VALUES (165,'America/North_Dakota/Center');
INSERT INTO `ps_timezone` VALUES (166,'America/North_Dakota/New_Salem');
INSERT INTO `ps_timezone` VALUES (167,'America/Panama');
INSERT INTO `ps_timezone` VALUES (168,'America/Pangnirtung');
INSERT INTO `ps_timezone` VALUES (169,'America/Paramaribo');
INSERT INTO `ps_timezone` VALUES (170,'America/Phoenix');
INSERT INTO `ps_timezone` VALUES (171,'America/Port-au-Prince');
INSERT INTO `ps_timezone` VALUES (172,'America/Port_of_Spain');
INSERT INTO `ps_timezone` VALUES (173,'America/Porto_Acre');
INSERT INTO `ps_timezone` VALUES (174,'America/Porto_Velho');
INSERT INTO `ps_timezone` VALUES (175,'America/Puerto_Rico');
INSERT INTO `ps_timezone` VALUES (176,'America/Rainy_River');
INSERT INTO `ps_timezone` VALUES (177,'America/Rankin_Inlet');
INSERT INTO `ps_timezone` VALUES (178,'America/Recife');
INSERT INTO `ps_timezone` VALUES (179,'America/Regina');
INSERT INTO `ps_timezone` VALUES (180,'America/Resolute');
INSERT INTO `ps_timezone` VALUES (181,'America/Rio_Branco');
INSERT INTO `ps_timezone` VALUES (182,'America/Rosario');
INSERT INTO `ps_timezone` VALUES (183,'America/Santarem');
INSERT INTO `ps_timezone` VALUES (184,'America/Santiago');
INSERT INTO `ps_timezone` VALUES (185,'America/Santo_Domingo');
INSERT INTO `ps_timezone` VALUES (186,'America/Sao_Paulo');
INSERT INTO `ps_timezone` VALUES (187,'America/Scoresbysund');
INSERT INTO `ps_timezone` VALUES (188,'America/Shiprock');
INSERT INTO `ps_timezone` VALUES (189,'America/St_Barthelemy');
INSERT INTO `ps_timezone` VALUES (190,'America/St_Johns');
INSERT INTO `ps_timezone` VALUES (191,'America/St_Kitts');
INSERT INTO `ps_timezone` VALUES (192,'America/St_Lucia');
INSERT INTO `ps_timezone` VALUES (193,'America/St_Thomas');
INSERT INTO `ps_timezone` VALUES (194,'America/St_Vincent');
INSERT INTO `ps_timezone` VALUES (195,'America/Swift_Current');
INSERT INTO `ps_timezone` VALUES (196,'America/Tegucigalpa');
INSERT INTO `ps_timezone` VALUES (197,'America/Thule');
INSERT INTO `ps_timezone` VALUES (198,'America/Thunder_Bay');
INSERT INTO `ps_timezone` VALUES (199,'America/Tijuana');
INSERT INTO `ps_timezone` VALUES (200,'America/Toronto');
INSERT INTO `ps_timezone` VALUES (201,'America/Tortola');
INSERT INTO `ps_timezone` VALUES (202,'America/Vancouver');
INSERT INTO `ps_timezone` VALUES (203,'America/Virgin');
INSERT INTO `ps_timezone` VALUES (204,'America/Whitehorse');
INSERT INTO `ps_timezone` VALUES (205,'America/Winnipeg');
INSERT INTO `ps_timezone` VALUES (206,'America/Yakutat');
INSERT INTO `ps_timezone` VALUES (207,'America/Yellowknife');
INSERT INTO `ps_timezone` VALUES (208,'Antarctica/Casey');
INSERT INTO `ps_timezone` VALUES (209,'Antarctica/Davis');
INSERT INTO `ps_timezone` VALUES (210,'Antarctica/DumontDUrville');
INSERT INTO `ps_timezone` VALUES (211,'Antarctica/Mawson');
INSERT INTO `ps_timezone` VALUES (212,'Antarctica/McMurdo');
INSERT INTO `ps_timezone` VALUES (213,'Antarctica/Palmer');
INSERT INTO `ps_timezone` VALUES (214,'Antarctica/Rothera');
INSERT INTO `ps_timezone` VALUES (215,'Antarctica/South_Pole');
INSERT INTO `ps_timezone` VALUES (216,'Antarctica/Syowa');
INSERT INTO `ps_timezone` VALUES (217,'Antarctica/Vostok');
INSERT INTO `ps_timezone` VALUES (218,'Arctic/Longyearbyen');
INSERT INTO `ps_timezone` VALUES (219,'Asia/Aden');
INSERT INTO `ps_timezone` VALUES (220,'Asia/Almaty');
INSERT INTO `ps_timezone` VALUES (221,'Asia/Amman');
INSERT INTO `ps_timezone` VALUES (222,'Asia/Anadyr');
INSERT INTO `ps_timezone` VALUES (223,'Asia/Aqtau');
INSERT INTO `ps_timezone` VALUES (224,'Asia/Aqtobe');
INSERT INTO `ps_timezone` VALUES (225,'Asia/Ashgabat');
INSERT INTO `ps_timezone` VALUES (226,'Asia/Ashkhabad');
INSERT INTO `ps_timezone` VALUES (227,'Asia/Baghdad');
INSERT INTO `ps_timezone` VALUES (228,'Asia/Bahrain');
INSERT INTO `ps_timezone` VALUES (229,'Asia/Baku');
INSERT INTO `ps_timezone` VALUES (230,'Asia/Bangkok');
INSERT INTO `ps_timezone` VALUES (231,'Asia/Beirut');
INSERT INTO `ps_timezone` VALUES (232,'Asia/Bishkek');
INSERT INTO `ps_timezone` VALUES (233,'Asia/Brunei');
INSERT INTO `ps_timezone` VALUES (234,'Asia/Calcutta');
INSERT INTO `ps_timezone` VALUES (235,'Asia/Choibalsan');
INSERT INTO `ps_timezone` VALUES (236,'Asia/Chongqing');
INSERT INTO `ps_timezone` VALUES (237,'Asia/Chungking');
INSERT INTO `ps_timezone` VALUES (238,'Asia/Colombo');
INSERT INTO `ps_timezone` VALUES (239,'Asia/Dacca');
INSERT INTO `ps_timezone` VALUES (240,'Asia/Damascus');
INSERT INTO `ps_timezone` VALUES (241,'Asia/Dhaka');
INSERT INTO `ps_timezone` VALUES (242,'Asia/Dili');
INSERT INTO `ps_timezone` VALUES (243,'Asia/Dubai');
INSERT INTO `ps_timezone` VALUES (244,'Asia/Dushanbe');
INSERT INTO `ps_timezone` VALUES (245,'Asia/Gaza');
INSERT INTO `ps_timezone` VALUES (246,'Asia/Harbin');
INSERT INTO `ps_timezone` VALUES (247,'Asia/Ho_Chi_Minh');
INSERT INTO `ps_timezone` VALUES (248,'Asia/Hong_Kong');
INSERT INTO `ps_timezone` VALUES (249,'Asia/Hovd');
INSERT INTO `ps_timezone` VALUES (250,'Asia/Irkutsk');
INSERT INTO `ps_timezone` VALUES (251,'Asia/Istanbul');
INSERT INTO `ps_timezone` VALUES (252,'Asia/Jakarta');
INSERT INTO `ps_timezone` VALUES (253,'Asia/Jayapura');
INSERT INTO `ps_timezone` VALUES (254,'Asia/Jerusalem');
INSERT INTO `ps_timezone` VALUES (255,'Asia/Kabul');
INSERT INTO `ps_timezone` VALUES (256,'Asia/Kamchatka');
INSERT INTO `ps_timezone` VALUES (257,'Asia/Karachi');
INSERT INTO `ps_timezone` VALUES (258,'Asia/Kashgar');
INSERT INTO `ps_timezone` VALUES (259,'Asia/Kathmandu');
INSERT INTO `ps_timezone` VALUES (260,'Asia/Katmandu');
INSERT INTO `ps_timezone` VALUES (261,'Asia/Kolkata');
INSERT INTO `ps_timezone` VALUES (262,'Asia/Krasnoyarsk');
INSERT INTO `ps_timezone` VALUES (263,'Asia/Kuala_Lumpur');
INSERT INTO `ps_timezone` VALUES (264,'Asia/Kuching');
INSERT INTO `ps_timezone` VALUES (265,'Asia/Kuwait');
INSERT INTO `ps_timezone` VALUES (266,'Asia/Macao');
INSERT INTO `ps_timezone` VALUES (267,'Asia/Macau');
INSERT INTO `ps_timezone` VALUES (268,'Asia/Magadan');
INSERT INTO `ps_timezone` VALUES (269,'Asia/Makassar');
INSERT INTO `ps_timezone` VALUES (270,'Asia/Manila');
INSERT INTO `ps_timezone` VALUES (271,'Asia/Muscat');
INSERT INTO `ps_timezone` VALUES (272,'Asia/Nicosia');
INSERT INTO `ps_timezone` VALUES (273,'Asia/Novosibirsk');
INSERT INTO `ps_timezone` VALUES (274,'Asia/Omsk');
INSERT INTO `ps_timezone` VALUES (275,'Asia/Oral');
INSERT INTO `ps_timezone` VALUES (276,'Asia/Phnom_Penh');
INSERT INTO `ps_timezone` VALUES (277,'Asia/Pontianak');
INSERT INTO `ps_timezone` VALUES (278,'Asia/Pyongyang');
INSERT INTO `ps_timezone` VALUES (279,'Asia/Qatar');
INSERT INTO `ps_timezone` VALUES (280,'Asia/Qyzylorda');
INSERT INTO `ps_timezone` VALUES (281,'Asia/Rangoon');
INSERT INTO `ps_timezone` VALUES (282,'Asia/Riyadh');
INSERT INTO `ps_timezone` VALUES (283,'Asia/Saigon');
INSERT INTO `ps_timezone` VALUES (284,'Asia/Sakhalin');
INSERT INTO `ps_timezone` VALUES (285,'Asia/Samarkand');
INSERT INTO `ps_timezone` VALUES (286,'Asia/Seoul');
INSERT INTO `ps_timezone` VALUES (287,'Asia/Shanghai');
INSERT INTO `ps_timezone` VALUES (288,'Asia/Singapore');
INSERT INTO `ps_timezone` VALUES (289,'Asia/Taipei');
INSERT INTO `ps_timezone` VALUES (290,'Asia/Tashkent');
INSERT INTO `ps_timezone` VALUES (291,'Asia/Tbilisi');
INSERT INTO `ps_timezone` VALUES (292,'Asia/Tehran');
INSERT INTO `ps_timezone` VALUES (293,'Asia/Tel_Aviv');
INSERT INTO `ps_timezone` VALUES (294,'Asia/Thimbu');
INSERT INTO `ps_timezone` VALUES (295,'Asia/Thimphu');
INSERT INTO `ps_timezone` VALUES (296,'Asia/Tokyo');
INSERT INTO `ps_timezone` VALUES (297,'Asia/Ujung_Pandang');
INSERT INTO `ps_timezone` VALUES (298,'Asia/Ulaanbaatar');
INSERT INTO `ps_timezone` VALUES (299,'Asia/Ulan_Bator');
INSERT INTO `ps_timezone` VALUES (300,'Asia/Urumqi');
INSERT INTO `ps_timezone` VALUES (301,'Asia/Vientiane');
INSERT INTO `ps_timezone` VALUES (302,'Asia/Vladivostok');
INSERT INTO `ps_timezone` VALUES (303,'Asia/Yakutsk');
INSERT INTO `ps_timezone` VALUES (304,'Asia/Yekaterinburg');
INSERT INTO `ps_timezone` VALUES (305,'Asia/Yerevan');
INSERT INTO `ps_timezone` VALUES (306,'Atlantic/Azores');
INSERT INTO `ps_timezone` VALUES (307,'Atlantic/Bermuda');
INSERT INTO `ps_timezone` VALUES (308,'Atlantic/Canary');
INSERT INTO `ps_timezone` VALUES (309,'Atlantic/Cape_Verde');
INSERT INTO `ps_timezone` VALUES (310,'Atlantic/Faeroe');
INSERT INTO `ps_timezone` VALUES (311,'Atlantic/Faroe');
INSERT INTO `ps_timezone` VALUES (312,'Atlantic/Jan_Mayen');
INSERT INTO `ps_timezone` VALUES (313,'Atlantic/Madeira');
INSERT INTO `ps_timezone` VALUES (314,'Atlantic/Reykjavik');
INSERT INTO `ps_timezone` VALUES (315,'Atlantic/South_Georgia');
INSERT INTO `ps_timezone` VALUES (316,'Atlantic/St_Helena');
INSERT INTO `ps_timezone` VALUES (317,'Atlantic/Stanley');
INSERT INTO `ps_timezone` VALUES (318,'Australia/ACT');
INSERT INTO `ps_timezone` VALUES (319,'Australia/Adelaide');
INSERT INTO `ps_timezone` VALUES (320,'Australia/Brisbane');
INSERT INTO `ps_timezone` VALUES (321,'Australia/Broken_Hill');
INSERT INTO `ps_timezone` VALUES (322,'Australia/Canberra');
INSERT INTO `ps_timezone` VALUES (323,'Australia/Currie');
INSERT INTO `ps_timezone` VALUES (324,'Australia/Darwin');
INSERT INTO `ps_timezone` VALUES (325,'Australia/Eucla');
INSERT INTO `ps_timezone` VALUES (326,'Australia/Hobart');
INSERT INTO `ps_timezone` VALUES (327,'Australia/LHI');
INSERT INTO `ps_timezone` VALUES (328,'Australia/Lindeman');
INSERT INTO `ps_timezone` VALUES (329,'Australia/Lord_Howe');
INSERT INTO `ps_timezone` VALUES (330,'Australia/Melbourne');
INSERT INTO `ps_timezone` VALUES (331,'Australia/North');
INSERT INTO `ps_timezone` VALUES (332,'Australia/NSW');
INSERT INTO `ps_timezone` VALUES (333,'Australia/Perth');
INSERT INTO `ps_timezone` VALUES (334,'Australia/Queensland');
INSERT INTO `ps_timezone` VALUES (335,'Australia/South');
INSERT INTO `ps_timezone` VALUES (336,'Australia/Sydney');
INSERT INTO `ps_timezone` VALUES (337,'Australia/Tasmania');
INSERT INTO `ps_timezone` VALUES (338,'Australia/Victoria');
INSERT INTO `ps_timezone` VALUES (339,'Australia/West');
INSERT INTO `ps_timezone` VALUES (340,'Australia/Yancowinna');
INSERT INTO `ps_timezone` VALUES (341,'Europe/Amsterdam');
INSERT INTO `ps_timezone` VALUES (342,'Europe/Andorra');
INSERT INTO `ps_timezone` VALUES (343,'Europe/Athens');
INSERT INTO `ps_timezone` VALUES (344,'Europe/Belfast');
INSERT INTO `ps_timezone` VALUES (345,'Europe/Belgrade');
INSERT INTO `ps_timezone` VALUES (346,'Europe/Berlin');
INSERT INTO `ps_timezone` VALUES (347,'Europe/Bratislava');
INSERT INTO `ps_timezone` VALUES (348,'Europe/Brussels');
INSERT INTO `ps_timezone` VALUES (349,'Europe/Bucharest');
INSERT INTO `ps_timezone` VALUES (350,'Europe/Budapest');
INSERT INTO `ps_timezone` VALUES (351,'Europe/Chisinau');
INSERT INTO `ps_timezone` VALUES (352,'Europe/Copenhagen');
INSERT INTO `ps_timezone` VALUES (353,'Europe/Dublin');
INSERT INTO `ps_timezone` VALUES (354,'Europe/Gibraltar');
INSERT INTO `ps_timezone` VALUES (355,'Europe/Guernsey');
INSERT INTO `ps_timezone` VALUES (356,'Europe/Helsinki');
INSERT INTO `ps_timezone` VALUES (357,'Europe/Isle_of_Man');
INSERT INTO `ps_timezone` VALUES (358,'Europe/Istanbul');
INSERT INTO `ps_timezone` VALUES (359,'Europe/Jersey');
INSERT INTO `ps_timezone` VALUES (360,'Europe/Kaliningrad');
INSERT INTO `ps_timezone` VALUES (361,'Europe/Kiev');
INSERT INTO `ps_timezone` VALUES (362,'Europe/Lisbon');
INSERT INTO `ps_timezone` VALUES (363,'Europe/Ljubljana');
INSERT INTO `ps_timezone` VALUES (364,'Europe/London');
INSERT INTO `ps_timezone` VALUES (365,'Europe/Luxembourg');
INSERT INTO `ps_timezone` VALUES (366,'Europe/Madrid');
INSERT INTO `ps_timezone` VALUES (367,'Europe/Malta');
INSERT INTO `ps_timezone` VALUES (368,'Europe/Mariehamn');
INSERT INTO `ps_timezone` VALUES (369,'Europe/Minsk');
INSERT INTO `ps_timezone` VALUES (370,'Europe/Monaco');
INSERT INTO `ps_timezone` VALUES (371,'Europe/Moscow');
INSERT INTO `ps_timezone` VALUES (372,'Europe/Nicosia');
INSERT INTO `ps_timezone` VALUES (373,'Europe/Oslo');
INSERT INTO `ps_timezone` VALUES (374,'Europe/Paris');
INSERT INTO `ps_timezone` VALUES (375,'Europe/Podgorica');
INSERT INTO `ps_timezone` VALUES (376,'Europe/Prague');
INSERT INTO `ps_timezone` VALUES (377,'Europe/Riga');
INSERT INTO `ps_timezone` VALUES (378,'Europe/Rome');
INSERT INTO `ps_timezone` VALUES (379,'Europe/Samara');
INSERT INTO `ps_timezone` VALUES (380,'Europe/San_Marino');
INSERT INTO `ps_timezone` VALUES (381,'Europe/Sarajevo');
INSERT INTO `ps_timezone` VALUES (382,'Europe/Simferopol');
INSERT INTO `ps_timezone` VALUES (383,'Europe/Skopje');
INSERT INTO `ps_timezone` VALUES (384,'Europe/Sofia');
INSERT INTO `ps_timezone` VALUES (385,'Europe/Stockholm');
INSERT INTO `ps_timezone` VALUES (386,'Europe/Tallinn');
INSERT INTO `ps_timezone` VALUES (387,'Europe/Tirane');
INSERT INTO `ps_timezone` VALUES (388,'Europe/Tiraspol');
INSERT INTO `ps_timezone` VALUES (389,'Europe/Uzhgorod');
INSERT INTO `ps_timezone` VALUES (390,'Europe/Vaduz');
INSERT INTO `ps_timezone` VALUES (391,'Europe/Vatican');
INSERT INTO `ps_timezone` VALUES (392,'Europe/Vienna');
INSERT INTO `ps_timezone` VALUES (393,'Europe/Vilnius');
INSERT INTO `ps_timezone` VALUES (394,'Europe/Volgograd');
INSERT INTO `ps_timezone` VALUES (395,'Europe/Warsaw');
INSERT INTO `ps_timezone` VALUES (396,'Europe/Zagreb');
INSERT INTO `ps_timezone` VALUES (397,'Europe/Zaporozhye');
INSERT INTO `ps_timezone` VALUES (398,'Europe/Zurich');
INSERT INTO `ps_timezone` VALUES (399,'Indian/Antananarivo');
INSERT INTO `ps_timezone` VALUES (400,'Indian/Chagos');
INSERT INTO `ps_timezone` VALUES (401,'Indian/Christmas');
INSERT INTO `ps_timezone` VALUES (402,'Indian/Cocos');
INSERT INTO `ps_timezone` VALUES (403,'Indian/Comoro');
INSERT INTO `ps_timezone` VALUES (404,'Indian/Kerguelen');
INSERT INTO `ps_timezone` VALUES (405,'Indian/Mahe');
INSERT INTO `ps_timezone` VALUES (406,'Indian/Maldives');
INSERT INTO `ps_timezone` VALUES (407,'Indian/Mauritius');
INSERT INTO `ps_timezone` VALUES (408,'Indian/Mayotte');
INSERT INTO `ps_timezone` VALUES (409,'Indian/Reunion');
INSERT INTO `ps_timezone` VALUES (410,'Pacific/Apia');
INSERT INTO `ps_timezone` VALUES (411,'Pacific/Auckland');
INSERT INTO `ps_timezone` VALUES (412,'Pacific/Chatham');
INSERT INTO `ps_timezone` VALUES (413,'Pacific/Easter');
INSERT INTO `ps_timezone` VALUES (414,'Pacific/Efate');
INSERT INTO `ps_timezone` VALUES (415,'Pacific/Enderbury');
INSERT INTO `ps_timezone` VALUES (416,'Pacific/Fakaofo');
INSERT INTO `ps_timezone` VALUES (417,'Pacific/Fiji');
INSERT INTO `ps_timezone` VALUES (418,'Pacific/Funafuti');
INSERT INTO `ps_timezone` VALUES (419,'Pacific/Galapagos');
INSERT INTO `ps_timezone` VALUES (420,'Pacific/Gambier');
INSERT INTO `ps_timezone` VALUES (421,'Pacific/Guadalcanal');
INSERT INTO `ps_timezone` VALUES (422,'Pacific/Guam');
INSERT INTO `ps_timezone` VALUES (423,'Pacific/Honolulu');
INSERT INTO `ps_timezone` VALUES (424,'Pacific/Johnston');
INSERT INTO `ps_timezone` VALUES (425,'Pacific/Kiritimati');
INSERT INTO `ps_timezone` VALUES (426,'Pacific/Kosrae');
INSERT INTO `ps_timezone` VALUES (427,'Pacific/Kwajalein');
INSERT INTO `ps_timezone` VALUES (428,'Pacific/Majuro');
INSERT INTO `ps_timezone` VALUES (429,'Pacific/Marquesas');
INSERT INTO `ps_timezone` VALUES (430,'Pacific/Midway');
INSERT INTO `ps_timezone` VALUES (431,'Pacific/Nauru');
INSERT INTO `ps_timezone` VALUES (432,'Pacific/Niue');
INSERT INTO `ps_timezone` VALUES (433,'Pacific/Norfolk');
INSERT INTO `ps_timezone` VALUES (434,'Pacific/Noumea');
INSERT INTO `ps_timezone` VALUES (435,'Pacific/Pago_Pago');
INSERT INTO `ps_timezone` VALUES (436,'Pacific/Palau');
INSERT INTO `ps_timezone` VALUES (437,'Pacific/Pitcairn');
INSERT INTO `ps_timezone` VALUES (438,'Pacific/Ponape');
INSERT INTO `ps_timezone` VALUES (439,'Pacific/Port_Moresby');
INSERT INTO `ps_timezone` VALUES (440,'Pacific/Rarotonga');
INSERT INTO `ps_timezone` VALUES (441,'Pacific/Saipan');
INSERT INTO `ps_timezone` VALUES (442,'Pacific/Samoa');
INSERT INTO `ps_timezone` VALUES (443,'Pacific/Tahiti');
INSERT INTO `ps_timezone` VALUES (444,'Pacific/Tarawa');
INSERT INTO `ps_timezone` VALUES (445,'Pacific/Tongatapu');
INSERT INTO `ps_timezone` VALUES (446,'Pacific/Truk');
INSERT INTO `ps_timezone` VALUES (447,'Pacific/Wake');
INSERT INTO `ps_timezone` VALUES (448,'Pacific/Wallis');
INSERT INTO `ps_timezone` VALUES (449,'Pacific/Yap');
INSERT INTO `ps_timezone` VALUES (450,'Brazil/Acre');
INSERT INTO `ps_timezone` VALUES (451,'Brazil/DeNoronha');
INSERT INTO `ps_timezone` VALUES (452,'Brazil/East');
INSERT INTO `ps_timezone` VALUES (453,'Brazil/West');
INSERT INTO `ps_timezone` VALUES (454,'Canada/Atlantic');
INSERT INTO `ps_timezone` VALUES (455,'Canada/Central');
INSERT INTO `ps_timezone` VALUES (456,'Canada/East-Saskatchewan');
INSERT INTO `ps_timezone` VALUES (457,'Canada/Eastern');
INSERT INTO `ps_timezone` VALUES (458,'Canada/Mountain');
INSERT INTO `ps_timezone` VALUES (459,'Canada/Newfoundland');
INSERT INTO `ps_timezone` VALUES (460,'Canada/Pacific');
INSERT INTO `ps_timezone` VALUES (461,'Canada/Saskatchewan');
INSERT INTO `ps_timezone` VALUES (462,'Canada/Yukon');
INSERT INTO `ps_timezone` VALUES (463,'CET');
INSERT INTO `ps_timezone` VALUES (464,'Chile/Continental');
INSERT INTO `ps_timezone` VALUES (465,'Chile/EasterIsland');
INSERT INTO `ps_timezone` VALUES (466,'CST6CDT');
INSERT INTO `ps_timezone` VALUES (467,'Cuba');
INSERT INTO `ps_timezone` VALUES (468,'EET');
INSERT INTO `ps_timezone` VALUES (469,'Egypt');
INSERT INTO `ps_timezone` VALUES (470,'Eire');
INSERT INTO `ps_timezone` VALUES (471,'EST');
INSERT INTO `ps_timezone` VALUES (472,'EST5EDT');
INSERT INTO `ps_timezone` VALUES (473,'Etc/GMT');
INSERT INTO `ps_timezone` VALUES (474,'Etc/GMT+0');
INSERT INTO `ps_timezone` VALUES (475,'Etc/GMT+1');
INSERT INTO `ps_timezone` VALUES (476,'Etc/GMT+10');
INSERT INTO `ps_timezone` VALUES (477,'Etc/GMT+11');
INSERT INTO `ps_timezone` VALUES (478,'Etc/GMT+12');
INSERT INTO `ps_timezone` VALUES (479,'Etc/GMT+2');
INSERT INTO `ps_timezone` VALUES (480,'Etc/GMT+3');
INSERT INTO `ps_timezone` VALUES (481,'Etc/GMT+4');
INSERT INTO `ps_timezone` VALUES (482,'Etc/GMT+5');
INSERT INTO `ps_timezone` VALUES (483,'Etc/GMT+6');
INSERT INTO `ps_timezone` VALUES (484,'Etc/GMT+7');
INSERT INTO `ps_timezone` VALUES (485,'Etc/GMT+8');
INSERT INTO `ps_timezone` VALUES (486,'Etc/GMT+9');
INSERT INTO `ps_timezone` VALUES (487,'Etc/GMT-0');
INSERT INTO `ps_timezone` VALUES (488,'Etc/GMT-1');
INSERT INTO `ps_timezone` VALUES (489,'Etc/GMT-10');
INSERT INTO `ps_timezone` VALUES (490,'Etc/GMT-11');
INSERT INTO `ps_timezone` VALUES (491,'Etc/GMT-12');
INSERT INTO `ps_timezone` VALUES (492,'Etc/GMT-13');
INSERT INTO `ps_timezone` VALUES (493,'Etc/GMT-14');
INSERT INTO `ps_timezone` VALUES (494,'Etc/GMT-2');
INSERT INTO `ps_timezone` VALUES (495,'Etc/GMT-3');
INSERT INTO `ps_timezone` VALUES (496,'Etc/GMT-4');
INSERT INTO `ps_timezone` VALUES (497,'Etc/GMT-5');
INSERT INTO `ps_timezone` VALUES (498,'Etc/GMT-6');
INSERT INTO `ps_timezone` VALUES (499,'Etc/GMT-7');
INSERT INTO `ps_timezone` VALUES (500,'Etc/GMT-8');
INSERT INTO `ps_timezone` VALUES (501,'Etc/GMT-9');
INSERT INTO `ps_timezone` VALUES (502,'Etc/GMT0');
INSERT INTO `ps_timezone` VALUES (503,'Etc/Greenwich');
INSERT INTO `ps_timezone` VALUES (504,'Etc/UCT');
INSERT INTO `ps_timezone` VALUES (505,'Etc/Universal');
INSERT INTO `ps_timezone` VALUES (506,'Etc/UTC');
INSERT INTO `ps_timezone` VALUES (507,'Etc/Zulu');
INSERT INTO `ps_timezone` VALUES (508,'Factory');
INSERT INTO `ps_timezone` VALUES (509,'GB');
INSERT INTO `ps_timezone` VALUES (510,'GB-Eire');
INSERT INTO `ps_timezone` VALUES (511,'GMT');
INSERT INTO `ps_timezone` VALUES (512,'GMT+0');
INSERT INTO `ps_timezone` VALUES (513,'GMT-0');
INSERT INTO `ps_timezone` VALUES (514,'GMT0');
INSERT INTO `ps_timezone` VALUES (515,'Greenwich');
INSERT INTO `ps_timezone` VALUES (516,'Hongkong');
INSERT INTO `ps_timezone` VALUES (517,'HST');
INSERT INTO `ps_timezone` VALUES (518,'Iceland');
INSERT INTO `ps_timezone` VALUES (519,'Iran');
INSERT INTO `ps_timezone` VALUES (520,'Israel');
INSERT INTO `ps_timezone` VALUES (521,'Jamaica');
INSERT INTO `ps_timezone` VALUES (522,'Japan');
INSERT INTO `ps_timezone` VALUES (523,'Kwajalein');
INSERT INTO `ps_timezone` VALUES (524,'Libya');
INSERT INTO `ps_timezone` VALUES (525,'MET');
INSERT INTO `ps_timezone` VALUES (526,'Mexico/BajaNorte');
INSERT INTO `ps_timezone` VALUES (527,'Mexico/BajaSur');
INSERT INTO `ps_timezone` VALUES (528,'Mexico/General');
INSERT INTO `ps_timezone` VALUES (529,'MST');
INSERT INTO `ps_timezone` VALUES (530,'MST7MDT');
INSERT INTO `ps_timezone` VALUES (531,'Navajo');
INSERT INTO `ps_timezone` VALUES (532,'NZ');
INSERT INTO `ps_timezone` VALUES (533,'NZ-CHAT');
INSERT INTO `ps_timezone` VALUES (534,'Poland');
INSERT INTO `ps_timezone` VALUES (535,'Portugal');
INSERT INTO `ps_timezone` VALUES (536,'PRC');
INSERT INTO `ps_timezone` VALUES (537,'PST8PDT');
INSERT INTO `ps_timezone` VALUES (538,'ROC');
INSERT INTO `ps_timezone` VALUES (539,'ROK');
INSERT INTO `ps_timezone` VALUES (540,'Singapore');
INSERT INTO `ps_timezone` VALUES (541,'Turkey');
INSERT INTO `ps_timezone` VALUES (542,'UCT');
INSERT INTO `ps_timezone` VALUES (543,'Universal');
INSERT INTO `ps_timezone` VALUES (544,'US/Alaska');
INSERT INTO `ps_timezone` VALUES (545,'US/Aleutian');
INSERT INTO `ps_timezone` VALUES (546,'US/Arizona');
INSERT INTO `ps_timezone` VALUES (547,'US/Central');
INSERT INTO `ps_timezone` VALUES (548,'US/East-Indiana');
INSERT INTO `ps_timezone` VALUES (549,'US/Eastern');
INSERT INTO `ps_timezone` VALUES (550,'US/Hawaii');
INSERT INTO `ps_timezone` VALUES (551,'US/Indiana-Starke');
INSERT INTO `ps_timezone` VALUES (552,'US/Michigan');
INSERT INTO `ps_timezone` VALUES (553,'US/Mountain');
INSERT INTO `ps_timezone` VALUES (554,'US/Pacific');
INSERT INTO `ps_timezone` VALUES (555,'US/Pacific-New');
INSERT INTO `ps_timezone` VALUES (556,'US/Samoa');
INSERT INTO `ps_timezone` VALUES (557,'UTC');
INSERT INTO `ps_timezone` VALUES (558,'W-SU');
INSERT INTO `ps_timezone` VALUES (559,'WET');
INSERT INTO `ps_timezone` VALUES (560,'Zulu');

/*!40000 ALTER TABLE `ps_timezone` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_warehouse`, `id_currency`, `id_address`, `id_employee`, `reference`, `name`, `management_type`, `deleted` FROM `ps_warehouse`;
    
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

DROP TABLE IF EXISTS `ps_warehouse`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_warehouse` (
  `id_warehouse` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_currency` int(11) unsigned NOT NULL,
  `id_address` int(11) unsigned NOT NULL,
  `id_employee` int(11) unsigned NOT NULL,
  `reference` varchar(64) DEFAULT NULL,
  `name` varchar(45) NOT NULL,
  `management_type` enum('WA','FIFO','LIFO') NOT NULL DEFAULT 'WA',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_warehouse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_warehouse` WRITE;
/*!40000 ALTER TABLE `ps_warehouse` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_warehouse` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_carrier`, `id_warehouse` FROM `ps_warehouse_carrier`;
    
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

DROP TABLE IF EXISTS `ps_warehouse_carrier`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_warehouse_carrier` (
  `id_carrier` int(11) unsigned NOT NULL,
  `id_warehouse` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_warehouse`,`id_carrier`),
  KEY `id_warehouse` (`id_warehouse`),
  KEY `id_carrier` (`id_carrier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_warehouse_carrier` WRITE;
/*!40000 ALTER TABLE `ps_warehouse_carrier` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_warehouse_carrier` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_warehouse_product_location`, `id_product`, `id_product_attribute`, `id_warehouse`, `location` FROM `ps_warehouse_product_location`;
    
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

DROP TABLE IF EXISTS `ps_warehouse_product_location`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_warehouse_product_location` (
  `id_warehouse_product_location` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(11) unsigned NOT NULL,
  `id_product_attribute` int(11) unsigned NOT NULL,
  `id_warehouse` int(11) unsigned NOT NULL,
  `location` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id_warehouse_product_location`),
  UNIQUE KEY `id_product` (`id_product`,`id_product_attribute`,`id_warehouse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_warehouse_product_location` WRITE;
/*!40000 ALTER TABLE `ps_warehouse_product_location` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_warehouse_product_location` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_shop`, `id_warehouse` FROM `ps_warehouse_shop`;
    
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

DROP TABLE IF EXISTS `ps_warehouse_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_warehouse_shop` (
  `id_shop` int(11) unsigned NOT NULL,
  `id_warehouse` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_warehouse`,`id_shop`),
  KEY `id_warehouse` (`id_warehouse`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_warehouse_shop` WRITE;
/*!40000 ALTER TABLE `ps_warehouse_shop` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_warehouse_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_web_browser`, `name` FROM `ps_web_browser`;
    
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

DROP TABLE IF EXISTS `ps_web_browser`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_web_browser` (
  `id_web_browser` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id_web_browser`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_web_browser` WRITE;
/*!40000 ALTER TABLE `ps_web_browser` DISABLE KEYS */;
INSERT INTO `ps_web_browser` VALUES (1,'Safari');
INSERT INTO `ps_web_browser` VALUES (2,'Safari iPad');
INSERT INTO `ps_web_browser` VALUES (3,'Firefox');
INSERT INTO `ps_web_browser` VALUES (4,'Opera');
INSERT INTO `ps_web_browser` VALUES (5,'IE 6');
INSERT INTO `ps_web_browser` VALUES (6,'IE 7');
INSERT INTO `ps_web_browser` VALUES (7,'IE 8');
INSERT INTO `ps_web_browser` VALUES (8,'IE 9');
INSERT INTO `ps_web_browser` VALUES (9,'IE 10');
INSERT INTO `ps_web_browser` VALUES (10,'IE 11');
INSERT INTO `ps_web_browser` VALUES (11,'Chrome');

/*!40000 ALTER TABLE `ps_web_browser` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_webservice_account`, `key`, `description`, `class_name`, `is_module`, `module_name`, `active` FROM `ps_webservice_account`;
    
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

DROP TABLE IF EXISTS `ps_webservice_account`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_webservice_account` (
  `id_webservice_account` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `description` text,
  `class_name` varchar(50) NOT NULL DEFAULT 'WebserviceRequest',
  `is_module` tinyint(2) NOT NULL DEFAULT '0',
  `module_name` varchar(50) DEFAULT NULL,
  `active` tinyint(2) NOT NULL,
  PRIMARY KEY (`id_webservice_account`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_webservice_account` WRITE;
/*!40000 ALTER TABLE `ps_webservice_account` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_webservice_account` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_webservice_account`, `id_shop` FROM `ps_webservice_account_shop`;
    
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

DROP TABLE IF EXISTS `ps_webservice_account_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_webservice_account_shop` (
  `id_webservice_account` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_webservice_account`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_webservice_account_shop` WRITE;
/*!40000 ALTER TABLE `ps_webservice_account_shop` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_webservice_account_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_webservice_permission`, `resource`, `method`, `id_webservice_account` FROM `ps_webservice_permission`;
    
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

DROP TABLE IF EXISTS `ps_webservice_permission`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_webservice_permission` (
  `id_webservice_permission` int(11) NOT NULL AUTO_INCREMENT,
  `resource` varchar(50) NOT NULL,
  `method` enum('GET','POST','PUT','DELETE','HEAD') NOT NULL,
  `id_webservice_account` int(11) NOT NULL,
  PRIMARY KEY (`id_webservice_permission`),
  UNIQUE KEY `resource_2` (`resource`,`method`,`id_webservice_account`),
  KEY `resource` (`resource`),
  KEY `method` (`method`),
  KEY `id_webservice_account` (`id_webservice_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_webservice_permission` WRITE;
/*!40000 ALTER TABLE `ps_webservice_permission` DISABLE KEYS */;

/*!40000 ALTER TABLE `ps_webservice_permission` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_zone`, `name`, `active` FROM `ps_zone`;
    
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

DROP TABLE IF EXISTS `ps_zone`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_zone` (
  `id_zone` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_zone`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_zone` WRITE;
/*!40000 ALTER TABLE `ps_zone` DISABLE KEYS */;
INSERT INTO `ps_zone` VALUES (1,'Europe',1);
INSERT INTO `ps_zone` VALUES (2,'North America',1);
INSERT INTO `ps_zone` VALUES (3,'Asia',1);
INSERT INTO `ps_zone` VALUES (4,'Africa',1);
INSERT INTO `ps_zone` VALUES (5,'Oceania',1);
INSERT INTO `ps_zone` VALUES (6,'South America',1);
INSERT INTO `ps_zone` VALUES (7,'Europe (non-EU)',1);
INSERT INTO `ps_zone` VALUES (8,'Central America/Antilla',1);
INSERT INTO `ps_zone` VALUES (9,'United States',1);
INSERT INTO `ps_zone` VALUES (10,'Canada',1);
INSERT INTO `ps_zone` VALUES (11,'Mexico',1);

/*!40000 ALTER TABLE `ps_zone` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- <?php exit; __halt_compiler(); // Protect the file from being visited via web
-- Orion backup format
-- Generated at: 2021-06-08T00:47:14+00:00 by ClonerDBAdapter; PHP v7.3.23
-- Selected via: SELECT `id_zone`, `id_shop` FROM `ps_zone_shop`;
    
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

DROP TABLE IF EXISTS `ps_zone_shop`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `ps_zone_shop` (
  `id_zone` int(11) unsigned NOT NULL,
  `id_shop` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_zone`,`id_shop`),
  KEY `id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `ps_zone_shop` WRITE;
/*!40000 ALTER TABLE `ps_zone_shop` DISABLE KEYS */;
INSERT INTO `ps_zone_shop` VALUES (1,1);
INSERT INTO `ps_zone_shop` VALUES (2,1);
INSERT INTO `ps_zone_shop` VALUES (3,1);
INSERT INTO `ps_zone_shop` VALUES (4,1);
INSERT INTO `ps_zone_shop` VALUES (5,1);
INSERT INTO `ps_zone_shop` VALUES (6,1);
INSERT INTO `ps_zone_shop` VALUES (7,1);
INSERT INTO `ps_zone_shop` VALUES (8,1);
INSERT INTO `ps_zone_shop` VALUES (9,1);
INSERT INTO `ps_zone_shop` VALUES (10,1);
INSERT INTO `ps_zone_shop` VALUES (11,1);

/*!40000 ALTER TABLE `ps_zone_shop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
