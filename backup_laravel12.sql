-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: laravel12
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-ai:analysis:NVDA:dfed3297b8ab970912ac02b2860f072252d814d9051d30f4647f3e6204b36731','a:3:{s:2:\"ok\";b:0;s:8:\"analysis\";N;s:5:\"error\";s:79:\"AI วิเคราะห์ไม่สำเร็จ (Anthropic API error)\";}',1780714591),('laravel-cache-twelvedata:quote:NVDA','a:7:{s:2:\"ok\";b:1;s:6:\"symbol\";s:4:\"NVDA\";s:4:\"name\";s:18:\"NVIDIA Corporation\";s:5:\"close\";d:205.10001;s:6:\"change\";d:-13.56;s:14:\"percent_change\";d:-6.20141;s:5:\"error\";N;}',1780713690),('laravel-cache-twelvedata:time_series:NVDA:200','a:5:{s:2:\"ok\";b:1;s:6:\"symbol\";s:4:\"NVDA\";s:4:\"name\";N;s:6:\"values\";a:200:{i:0;a:2:{s:8:\"datetime\";s:10:\"2025-08-20\";s:5:\"close\";d:175.39999;}i:1;a:2:{s:8:\"datetime\";s:10:\"2025-08-21\";s:5:\"close\";d:174.98;}i:2;a:2:{s:8:\"datetime\";s:10:\"2025-08-22\";s:5:\"close\";d:177.99001;}i:3;a:2:{s:8:\"datetime\";s:10:\"2025-08-25\";s:5:\"close\";d:179.81;}i:4;a:2:{s:8:\"datetime\";s:10:\"2025-08-26\";s:5:\"close\";d:181.77;}i:5;a:2:{s:8:\"datetime\";s:10:\"2025-08-27\";s:5:\"close\";d:181.60001;}i:6;a:2:{s:8:\"datetime\";s:10:\"2025-08-28\";s:5:\"close\";d:180.17;}i:7;a:2:{s:8:\"datetime\";s:10:\"2025-08-29\";s:5:\"close\";d:174.17999;}i:8;a:2:{s:8:\"datetime\";s:10:\"2025-09-02\";s:5:\"close\";d:170.78;}i:9;a:2:{s:8:\"datetime\";s:10:\"2025-09-03\";s:5:\"close\";d:170.62;}i:10;a:2:{s:8:\"datetime\";s:10:\"2025-09-04\";s:5:\"close\";d:171.66;}i:11;a:2:{s:8:\"datetime\";s:10:\"2025-09-05\";s:5:\"close\";d:167.020004;}i:12;a:2:{s:8:\"datetime\";s:10:\"2025-09-08\";s:5:\"close\";d:168.31;}i:13;a:2:{s:8:\"datetime\";s:10:\"2025-09-09\";s:5:\"close\";d:170.75999;}i:14;a:2:{s:8:\"datetime\";s:10:\"2025-09-10\";s:5:\"close\";d:177.33;}i:15;a:2:{s:8:\"datetime\";s:10:\"2025-09-11\";s:5:\"close\";d:177.17;}i:16;a:2:{s:8:\"datetime\";s:10:\"2025-09-12\";s:5:\"close\";d:177.82001;}i:17;a:2:{s:8:\"datetime\";s:10:\"2025-09-15\";s:5:\"close\";d:177.75;}i:18;a:2:{s:8:\"datetime\";s:10:\"2025-09-16\";s:5:\"close\";d:174.88;}i:19;a:2:{s:8:\"datetime\";s:10:\"2025-09-17\";s:5:\"close\";d:170.28999;}i:20;a:2:{s:8:\"datetime\";s:10:\"2025-09-18\";s:5:\"close\";d:176.24001;}i:21;a:2:{s:8:\"datetime\";s:10:\"2025-09-19\";s:5:\"close\";d:176.67;}i:22;a:2:{s:8:\"datetime\";s:10:\"2025-09-22\";s:5:\"close\";d:183.61;}i:23;a:2:{s:8:\"datetime\";s:10:\"2025-09-23\";s:5:\"close\";d:178.42999;}i:24;a:2:{s:8:\"datetime\";s:10:\"2025-09-24\";s:5:\"close\";d:176.97;}i:25;a:2:{s:8:\"datetime\";s:10:\"2025-09-25\";s:5:\"close\";d:177.69;}i:26;a:2:{s:8:\"datetime\";s:10:\"2025-09-26\";s:5:\"close\";d:178.19;}i:27;a:2:{s:8:\"datetime\";s:10:\"2025-09-29\";s:5:\"close\";d:181.85001;}i:28;a:2:{s:8:\"datetime\";s:10:\"2025-09-30\";s:5:\"close\";d:186.58;}i:29;a:2:{s:8:\"datetime\";s:10:\"2025-10-01\";s:5:\"close\";d:187.24001;}i:30;a:2:{s:8:\"datetime\";s:10:\"2025-10-02\";s:5:\"close\";d:188.89;}i:31;a:2:{s:8:\"datetime\";s:10:\"2025-10-03\";s:5:\"close\";d:187.62;}i:32;a:2:{s:8:\"datetime\";s:10:\"2025-10-06\";s:5:\"close\";d:185.53999;}i:33;a:2:{s:8:\"datetime\";s:10:\"2025-10-07\";s:5:\"close\";d:185.039993;}i:34;a:2:{s:8:\"datetime\";s:10:\"2025-10-08\";s:5:\"close\";d:189.11;}i:35;a:2:{s:8:\"datetime\";s:10:\"2025-10-09\";s:5:\"close\";d:192.57001;}i:36;a:2:{s:8:\"datetime\";s:10:\"2025-10-10\";s:5:\"close\";d:183.16;}i:37;a:2:{s:8:\"datetime\";s:10:\"2025-10-13\";s:5:\"close\";d:188.32001;}i:38;a:2:{s:8:\"datetime\";s:10:\"2025-10-14\";s:5:\"close\";d:180.029999;}i:39;a:2:{s:8:\"datetime\";s:10:\"2025-10-15\";s:5:\"close\";d:179.83;}i:40;a:2:{s:8:\"datetime\";s:10:\"2025-10-16\";s:5:\"close\";d:181.81;}i:41;a:2:{s:8:\"datetime\";s:10:\"2025-10-17\";s:5:\"close\";d:183.22;}i:42;a:2:{s:8:\"datetime\";s:10:\"2025-10-20\";s:5:\"close\";d:182.64;}i:43;a:2:{s:8:\"datetime\";s:10:\"2025-10-21\";s:5:\"close\";d:181.16;}i:44;a:2:{s:8:\"datetime\";s:10:\"2025-10-22\";s:5:\"close\";d:180.28;}i:45;a:2:{s:8:\"datetime\";s:10:\"2025-10-23\";s:5:\"close\";d:182.16;}i:46;a:2:{s:8:\"datetime\";s:10:\"2025-10-24\";s:5:\"close\";d:186.25999;}i:47;a:2:{s:8:\"datetime\";s:10:\"2025-10-27\";s:5:\"close\";d:191.49001;}i:48;a:2:{s:8:\"datetime\";s:10:\"2025-10-28\";s:5:\"close\";d:201.029999;}i:49;a:2:{s:8:\"datetime\";s:10:\"2025-10-29\";s:5:\"close\";d:207.039993;}i:50;a:2:{s:8:\"datetime\";s:10:\"2025-10-30\";s:5:\"close\";d:202.89;}i:51;a:2:{s:8:\"datetime\";s:10:\"2025-10-31\";s:5:\"close\";d:202.49001;}i:52;a:2:{s:8:\"datetime\";s:10:\"2025-11-03\";s:5:\"close\";d:206.88;}i:53;a:2:{s:8:\"datetime\";s:10:\"2025-11-04\";s:5:\"close\";d:198.69;}i:54;a:2:{s:8:\"datetime\";s:10:\"2025-11-05\";s:5:\"close\";d:195.21001;}i:55;a:2:{s:8:\"datetime\";s:10:\"2025-11-06\";s:5:\"close\";d:188.080002;}i:56;a:2:{s:8:\"datetime\";s:10:\"2025-11-07\";s:5:\"close\";d:188.14999;}i:57;a:2:{s:8:\"datetime\";s:10:\"2025-11-10\";s:5:\"close\";d:199.050003;}i:58;a:2:{s:8:\"datetime\";s:10:\"2025-11-11\";s:5:\"close\";d:193.16;}i:59;a:2:{s:8:\"datetime\";s:10:\"2025-11-12\";s:5:\"close\";d:193.8;}i:60;a:2:{s:8:\"datetime\";s:10:\"2025-11-13\";s:5:\"close\";d:186.86;}i:61;a:2:{s:8:\"datetime\";s:10:\"2025-11-14\";s:5:\"close\";d:190.17;}i:62;a:2:{s:8:\"datetime\";s:10:\"2025-11-17\";s:5:\"close\";d:186.60001;}i:63;a:2:{s:8:\"datetime\";s:10:\"2025-11-18\";s:5:\"close\";d:181.36;}i:64;a:2:{s:8:\"datetime\";s:10:\"2025-11-19\";s:5:\"close\";d:186.52;}i:65;a:2:{s:8:\"datetime\";s:10:\"2025-11-20\";s:5:\"close\";d:180.64;}i:66;a:2:{s:8:\"datetime\";s:10:\"2025-11-21\";s:5:\"close\";d:178.88;}i:67;a:2:{s:8:\"datetime\";s:10:\"2025-11-24\";s:5:\"close\";d:182.55;}i:68;a:2:{s:8:\"datetime\";s:10:\"2025-11-25\";s:5:\"close\";d:177.82001;}i:69;a:2:{s:8:\"datetime\";s:10:\"2025-11-26\";s:5:\"close\";d:180.25999;}i:70;a:2:{s:8:\"datetime\";s:10:\"2025-11-28\";s:5:\"close\";d:177;}i:71;a:2:{s:8:\"datetime\";s:10:\"2025-12-01\";s:5:\"close\";d:179.92;}i:72;a:2:{s:8:\"datetime\";s:10:\"2025-12-02\";s:5:\"close\";d:181.46001;}i:73;a:2:{s:8:\"datetime\";s:10:\"2025-12-03\";s:5:\"close\";d:179.59;}i:74;a:2:{s:8:\"datetime\";s:10:\"2025-12-04\";s:5:\"close\";d:183.38;}i:75;a:2:{s:8:\"datetime\";s:10:\"2025-12-05\";s:5:\"close\";d:182.41;}i:76;a:2:{s:8:\"datetime\";s:10:\"2025-12-08\";s:5:\"close\";d:185.55;}i:77;a:2:{s:8:\"datetime\";s:10:\"2025-12-09\";s:5:\"close\";d:184.97;}i:78;a:2:{s:8:\"datetime\";s:10:\"2025-12-10\";s:5:\"close\";d:183.78;}i:79;a:2:{s:8:\"datetime\";s:10:\"2025-12-11\";s:5:\"close\";d:180.92999;}i:80;a:2:{s:8:\"datetime\";s:10:\"2025-12-12\";s:5:\"close\";d:175.020004;}i:81;a:2:{s:8:\"datetime\";s:10:\"2025-12-15\";s:5:\"close\";d:176.28999;}i:82;a:2:{s:8:\"datetime\";s:10:\"2025-12-16\";s:5:\"close\";d:177.72;}i:83;a:2:{s:8:\"datetime\";s:10:\"2025-12-17\";s:5:\"close\";d:170.94;}i:84;a:2:{s:8:\"datetime\";s:10:\"2025-12-18\";s:5:\"close\";d:174.14;}i:85;a:2:{s:8:\"datetime\";s:10:\"2025-12-19\";s:5:\"close\";d:180.99001;}i:86;a:2:{s:8:\"datetime\";s:10:\"2025-12-22\";s:5:\"close\";d:183.69;}i:87;a:2:{s:8:\"datetime\";s:10:\"2025-12-23\";s:5:\"close\";d:189.21001;}i:88;a:2:{s:8:\"datetime\";s:10:\"2025-12-24\";s:5:\"close\";d:188.61;}i:89;a:2:{s:8:\"datetime\";s:10:\"2025-12-26\";s:5:\"close\";d:190.53;}i:90;a:2:{s:8:\"datetime\";s:10:\"2025-12-29\";s:5:\"close\";d:188.22;}i:91;a:2:{s:8:\"datetime\";s:10:\"2025-12-30\";s:5:\"close\";d:187.53999;}i:92;a:2:{s:8:\"datetime\";s:10:\"2025-12-31\";s:5:\"close\";d:186.5;}i:93;a:2:{s:8:\"datetime\";s:10:\"2026-01-02\";s:5:\"close\";d:188.85001;}i:94;a:2:{s:8:\"datetime\";s:10:\"2026-01-05\";s:5:\"close\";d:188.12;}i:95;a:2:{s:8:\"datetime\";s:10:\"2026-01-06\";s:5:\"close\";d:187.24001;}i:96;a:2:{s:8:\"datetime\";s:10:\"2026-01-07\";s:5:\"close\";d:189.11;}i:97;a:2:{s:8:\"datetime\";s:10:\"2026-01-08\";s:5:\"close\";d:185.039993;}i:98;a:2:{s:8:\"datetime\";s:10:\"2026-01-09\";s:5:\"close\";d:184.86;}i:99;a:2:{s:8:\"datetime\";s:10:\"2026-01-12\";s:5:\"close\";d:184.94;}i:100;a:2:{s:8:\"datetime\";s:10:\"2026-01-13\";s:5:\"close\";d:185.81;}i:101;a:2:{s:8:\"datetime\";s:10:\"2026-01-14\";s:5:\"close\";d:183.14;}i:102;a:2:{s:8:\"datetime\";s:10:\"2026-01-15\";s:5:\"close\";d:187.050003;}i:103;a:2:{s:8:\"datetime\";s:10:\"2026-01-16\";s:5:\"close\";d:186.23;}i:104;a:2:{s:8:\"datetime\";s:10:\"2026-01-20\";s:5:\"close\";d:178.070007;}i:105;a:2:{s:8:\"datetime\";s:10:\"2026-01-21\";s:5:\"close\";d:183.32001;}i:106;a:2:{s:8:\"datetime\";s:10:\"2026-01-22\";s:5:\"close\";d:184.84;}i:107;a:2:{s:8:\"datetime\";s:10:\"2026-01-23\";s:5:\"close\";d:187.67;}i:108;a:2:{s:8:\"datetime\";s:10:\"2026-01-26\";s:5:\"close\";d:186.47;}i:109;a:2:{s:8:\"datetime\";s:10:\"2026-01-27\";s:5:\"close\";d:188.52;}i:110;a:2:{s:8:\"datetime\";s:10:\"2026-01-28\";s:5:\"close\";d:191.52;}i:111;a:2:{s:8:\"datetime\";s:10:\"2026-01-29\";s:5:\"close\";d:192.50999;}i:112;a:2:{s:8:\"datetime\";s:10:\"2026-01-30\";s:5:\"close\";d:191.13;}i:113;a:2:{s:8:\"datetime\";s:10:\"2026-02-02\";s:5:\"close\";d:185.61;}i:114;a:2:{s:8:\"datetime\";s:10:\"2026-02-03\";s:5:\"close\";d:180.34;}i:115;a:2:{s:8:\"datetime\";s:10:\"2026-02-04\";s:5:\"close\";d:174.19;}i:116;a:2:{s:8:\"datetime\";s:10:\"2026-02-05\";s:5:\"close\";d:171.88;}i:117;a:2:{s:8:\"datetime\";s:10:\"2026-02-06\";s:5:\"close\";d:185.41;}i:118;a:2:{s:8:\"datetime\";s:10:\"2026-02-09\";s:5:\"close\";d:190.039993;}i:119;a:2:{s:8:\"datetime\";s:10:\"2026-02-10\";s:5:\"close\";d:188.53999;}i:120;a:2:{s:8:\"datetime\";s:10:\"2026-02-11\";s:5:\"close\";d:190.050003;}i:121;a:2:{s:8:\"datetime\";s:10:\"2026-02-12\";s:5:\"close\";d:186.94;}i:122;a:2:{s:8:\"datetime\";s:10:\"2026-02-13\";s:5:\"close\";d:182.81;}i:123;a:2:{s:8:\"datetime\";s:10:\"2026-02-17\";s:5:\"close\";d:184.97;}i:124;a:2:{s:8:\"datetime\";s:10:\"2026-02-18\";s:5:\"close\";d:187.98;}i:125;a:2:{s:8:\"datetime\";s:10:\"2026-02-19\";s:5:\"close\";d:187.89999;}i:126;a:2:{s:8:\"datetime\";s:10:\"2026-02-20\";s:5:\"close\";d:189.82001;}i:127;a:2:{s:8:\"datetime\";s:10:\"2026-02-23\";s:5:\"close\";d:191.55;}i:128;a:2:{s:8:\"datetime\";s:10:\"2026-02-24\";s:5:\"close\";d:192.85001;}i:129;a:2:{s:8:\"datetime\";s:10:\"2026-02-25\";s:5:\"close\";d:195.56;}i:130;a:2:{s:8:\"datetime\";s:10:\"2026-02-26\";s:5:\"close\";d:184.89;}i:131;a:2:{s:8:\"datetime\";s:10:\"2026-02-27\";s:5:\"close\";d:177.19;}i:132;a:2:{s:8:\"datetime\";s:10:\"2026-03-02\";s:5:\"close\";d:182.48;}i:133;a:2:{s:8:\"datetime\";s:10:\"2026-03-03\";s:5:\"close\";d:180.050003;}i:134;a:2:{s:8:\"datetime\";s:10:\"2026-03-04\";s:5:\"close\";d:183.039993;}i:135;a:2:{s:8:\"datetime\";s:10:\"2026-03-05\";s:5:\"close\";d:183.34;}i:136;a:2:{s:8:\"datetime\";s:10:\"2026-03-06\";s:5:\"close\";d:177.82001;}i:137;a:2:{s:8:\"datetime\";s:10:\"2026-03-09\";s:5:\"close\";d:182.64999;}i:138;a:2:{s:8:\"datetime\";s:10:\"2026-03-10\";s:5:\"close\";d:184.77;}i:139;a:2:{s:8:\"datetime\";s:10:\"2026-03-11\";s:5:\"close\";d:186.029999;}i:140;a:2:{s:8:\"datetime\";s:10:\"2026-03-12\";s:5:\"close\";d:183.14;}i:141;a:2:{s:8:\"datetime\";s:10:\"2026-03-13\";s:5:\"close\";d:180.25;}i:142;a:2:{s:8:\"datetime\";s:10:\"2026-03-16\";s:5:\"close\";d:183.22;}i:143;a:2:{s:8:\"datetime\";s:10:\"2026-03-17\";s:5:\"close\";d:181.92999;}i:144;a:2:{s:8:\"datetime\";s:10:\"2026-03-18\";s:5:\"close\";d:180.39999;}i:145;a:2:{s:8:\"datetime\";s:10:\"2026-03-19\";s:5:\"close\";d:178.56;}i:146;a:2:{s:8:\"datetime\";s:10:\"2026-03-20\";s:5:\"close\";d:172.7;}i:147;a:2:{s:8:\"datetime\";s:10:\"2026-03-23\";s:5:\"close\";d:175.64;}i:148;a:2:{s:8:\"datetime\";s:10:\"2026-03-24\";s:5:\"close\";d:175.2;}i:149;a:2:{s:8:\"datetime\";s:10:\"2026-03-25\";s:5:\"close\";d:178.67999;}i:150;a:2:{s:8:\"datetime\";s:10:\"2026-03-26\";s:5:\"close\";d:171.24001;}i:151;a:2:{s:8:\"datetime\";s:10:\"2026-03-27\";s:5:\"close\";d:167.52;}i:152;a:2:{s:8:\"datetime\";s:10:\"2026-03-30\";s:5:\"close\";d:165.17;}i:153;a:2:{s:8:\"datetime\";s:10:\"2026-03-31\";s:5:\"close\";d:174.39999;}i:154;a:2:{s:8:\"datetime\";s:10:\"2026-04-01\";s:5:\"close\";d:175.75;}i:155;a:2:{s:8:\"datetime\";s:10:\"2026-04-02\";s:5:\"close\";d:177.39;}i:156;a:2:{s:8:\"datetime\";s:10:\"2026-04-06\";s:5:\"close\";d:177.64;}i:157;a:2:{s:8:\"datetime\";s:10:\"2026-04-07\";s:5:\"close\";d:178.10001;}i:158;a:2:{s:8:\"datetime\";s:10:\"2026-04-08\";s:5:\"close\";d:182.080002;}i:159;a:2:{s:8:\"datetime\";s:10:\"2026-04-09\";s:5:\"close\";d:183.91;}i:160;a:2:{s:8:\"datetime\";s:10:\"2026-04-10\";s:5:\"close\";d:188.63;}i:161;a:2:{s:8:\"datetime\";s:10:\"2026-04-13\";s:5:\"close\";d:189.31;}i:162;a:2:{s:8:\"datetime\";s:10:\"2026-04-14\";s:5:\"close\";d:196.50999;}i:163;a:2:{s:8:\"datetime\";s:10:\"2026-04-15\";s:5:\"close\";d:198.87;}i:164;a:2:{s:8:\"datetime\";s:10:\"2026-04-16\";s:5:\"close\";d:198.35001;}i:165;a:2:{s:8:\"datetime\";s:10:\"2026-04-17\";s:5:\"close\";d:201.67999;}i:166;a:2:{s:8:\"datetime\";s:10:\"2026-04-20\";s:5:\"close\";d:202.059998;}i:167;a:2:{s:8:\"datetime\";s:10:\"2026-04-21\";s:5:\"close\";d:199.88;}i:168;a:2:{s:8:\"datetime\";s:10:\"2026-04-22\";s:5:\"close\";d:202.5;}i:169;a:2:{s:8:\"datetime\";s:10:\"2026-04-23\";s:5:\"close\";d:199.64;}i:170;a:2:{s:8:\"datetime\";s:10:\"2026-04-24\";s:5:\"close\";d:208.27;}i:171;a:2:{s:8:\"datetime\";s:10:\"2026-04-27\";s:5:\"close\";d:216.61;}i:172;a:2:{s:8:\"datetime\";s:10:\"2026-04-28\";s:5:\"close\";d:213.17;}i:173;a:2:{s:8:\"datetime\";s:10:\"2026-04-29\";s:5:\"close\";d:209.25;}i:174;a:2:{s:8:\"datetime\";s:10:\"2026-04-30\";s:5:\"close\";d:199.57001;}i:175;a:2:{s:8:\"datetime\";s:10:\"2026-05-01\";s:5:\"close\";d:198.45;}i:176;a:2:{s:8:\"datetime\";s:10:\"2026-05-04\";s:5:\"close\";d:198.48;}i:177;a:2:{s:8:\"datetime\";s:10:\"2026-05-05\";s:5:\"close\";d:196.5;}i:178;a:2:{s:8:\"datetime\";s:10:\"2026-05-06\";s:5:\"close\";d:207.83;}i:179;a:2:{s:8:\"datetime\";s:10:\"2026-05-07\";s:5:\"close\";d:211.5;}i:180;a:2:{s:8:\"datetime\";s:10:\"2026-05-08\";s:5:\"close\";d:215.2;}i:181;a:2:{s:8:\"datetime\";s:10:\"2026-05-11\";s:5:\"close\";d:219.44;}i:182;a:2:{s:8:\"datetime\";s:10:\"2026-05-12\";s:5:\"close\";d:220.78;}i:183;a:2:{s:8:\"datetime\";s:10:\"2026-05-13\";s:5:\"close\";d:225.83;}i:184;a:2:{s:8:\"datetime\";s:10:\"2026-05-14\";s:5:\"close\";d:235.74001;}i:185;a:2:{s:8:\"datetime\";s:10:\"2026-05-15\";s:5:\"close\";d:225.32001;}i:186;a:2:{s:8:\"datetime\";s:10:\"2026-05-18\";s:5:\"close\";d:222.32001;}i:187;a:2:{s:8:\"datetime\";s:10:\"2026-05-19\";s:5:\"close\";d:220.61;}i:188;a:2:{s:8:\"datetime\";s:10:\"2026-05-20\";s:5:\"close\";d:223.47;}i:189;a:2:{s:8:\"datetime\";s:10:\"2026-05-21\";s:5:\"close\";d:219.50999;}i:190;a:2:{s:8:\"datetime\";s:10:\"2026-05-22\";s:5:\"close\";d:215.33;}i:191;a:2:{s:8:\"datetime\";s:10:\"2026-05-26\";s:5:\"close\";d:214.86;}i:192;a:2:{s:8:\"datetime\";s:10:\"2026-05-27\";s:5:\"close\";d:212.60001;}i:193;a:2:{s:8:\"datetime\";s:10:\"2026-05-28\";s:5:\"close\";d:214.25;}i:194;a:2:{s:8:\"datetime\";s:10:\"2026-05-29\";s:5:\"close\";d:211.14;}i:195;a:2:{s:8:\"datetime\";s:10:\"2026-06-01\";s:5:\"close\";d:224.36;}i:196;a:2:{s:8:\"datetime\";s:10:\"2026-06-02\";s:5:\"close\";d:222.82001;}i:197;a:2:{s:8:\"datetime\";s:10:\"2026-06-03\";s:5:\"close\";d:214.75;}i:198;a:2:{s:8:\"datetime\";s:10:\"2026-06-04\";s:5:\"close\";d:218.66;}i:199;a:2:{s:8:\"datetime\";s:10:\"2026-06-05\";s:5:\"close\";d:205.10001;}}s:5:\"error\";N;}',1780713690),('laravel-cache-twelvedata:time_series:NVDA:30','a:5:{s:2:\"ok\";b:1;s:6:\"symbol\";s:4:\"NVDA\";s:4:\"name\";N;s:6:\"values\";a:30:{i:0;a:2:{s:8:\"datetime\";s:10:\"2026-04-24\";s:5:\"close\";d:208.27;}i:1;a:2:{s:8:\"datetime\";s:10:\"2026-04-27\";s:5:\"close\";d:216.61;}i:2;a:2:{s:8:\"datetime\";s:10:\"2026-04-28\";s:5:\"close\";d:213.17;}i:3;a:2:{s:8:\"datetime\";s:10:\"2026-04-29\";s:5:\"close\";d:209.25;}i:4;a:2:{s:8:\"datetime\";s:10:\"2026-04-30\";s:5:\"close\";d:199.57001;}i:5;a:2:{s:8:\"datetime\";s:10:\"2026-05-01\";s:5:\"close\";d:198.45;}i:6;a:2:{s:8:\"datetime\";s:10:\"2026-05-04\";s:5:\"close\";d:198.48;}i:7;a:2:{s:8:\"datetime\";s:10:\"2026-05-05\";s:5:\"close\";d:196.5;}i:8;a:2:{s:8:\"datetime\";s:10:\"2026-05-06\";s:5:\"close\";d:207.83;}i:9;a:2:{s:8:\"datetime\";s:10:\"2026-05-07\";s:5:\"close\";d:211.5;}i:10;a:2:{s:8:\"datetime\";s:10:\"2026-05-08\";s:5:\"close\";d:215.2;}i:11;a:2:{s:8:\"datetime\";s:10:\"2026-05-11\";s:5:\"close\";d:219.44;}i:12;a:2:{s:8:\"datetime\";s:10:\"2026-05-12\";s:5:\"close\";d:220.78;}i:13;a:2:{s:8:\"datetime\";s:10:\"2026-05-13\";s:5:\"close\";d:225.83;}i:14;a:2:{s:8:\"datetime\";s:10:\"2026-05-14\";s:5:\"close\";d:235.74001;}i:15;a:2:{s:8:\"datetime\";s:10:\"2026-05-15\";s:5:\"close\";d:225.32001;}i:16;a:2:{s:8:\"datetime\";s:10:\"2026-05-18\";s:5:\"close\";d:222.32001;}i:17;a:2:{s:8:\"datetime\";s:10:\"2026-05-19\";s:5:\"close\";d:220.61;}i:18;a:2:{s:8:\"datetime\";s:10:\"2026-05-20\";s:5:\"close\";d:223.47;}i:19;a:2:{s:8:\"datetime\";s:10:\"2026-05-21\";s:5:\"close\";d:219.50999;}i:20;a:2:{s:8:\"datetime\";s:10:\"2026-05-22\";s:5:\"close\";d:215.33;}i:21;a:2:{s:8:\"datetime\";s:10:\"2026-05-26\";s:5:\"close\";d:214.86;}i:22;a:2:{s:8:\"datetime\";s:10:\"2026-05-27\";s:5:\"close\";d:212.60001;}i:23;a:2:{s:8:\"datetime\";s:10:\"2026-05-28\";s:5:\"close\";d:214.25;}i:24;a:2:{s:8:\"datetime\";s:10:\"2026-05-29\";s:5:\"close\";d:211.14;}i:25;a:2:{s:8:\"datetime\";s:10:\"2026-06-01\";s:5:\"close\";d:224.36;}i:26;a:2:{s:8:\"datetime\";s:10:\"2026-06-02\";s:5:\"close\";d:222.82001;}i:27;a:2:{s:8:\"datetime\";s:10:\"2026-06-03\";s:5:\"close\";d:214.75;}i:28;a:2:{s:8:\"datetime\";s:10:\"2026-06-04\";s:5:\"close\";d:218.66;}i:29;a:2:{s:8:\"datetime\";s:10:\"2026-06-05\";s:5:\"close\";d:205.10001;}}s:5:\"error\";N;}',1780713691);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_24_070612_create_pdt_ordh_table',1),(5,'2026_02_24_070614_create_pdt_ordd_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdt_ordd`
--

DROP TABLE IF EXISTS `pdt_ordd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pdt_ordd` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DOCNO` varchar(255) NOT NULL,
  `SN` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdt_ordd_docno_foreign` (`DOCNO`),
  CONSTRAINT `pdt_ordd_docno_foreign` FOREIGN KEY (`DOCNO`) REFERENCES `pdt_ordh` (`DOCNO`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdt_ordd`
--

LOCK TABLES `pdt_ordd` WRITE;
/*!40000 ALTER TABLE `pdt_ordd` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdt_ordd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdt_ordh`
--

DROP TABLE IF EXISTS `pdt_ordh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pdt_ordh` (
  `DOCNO` varchar(255) NOT NULL,
  `PDTCD` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`DOCNO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdt_ordh`
--

LOCK TABLES `pdt_ordh` WRITE;
/*!40000 ALTER TABLE `pdt_ordh` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdt_ordh` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
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

-- Dump completed on 2026-06-06  9:49:16
