-- MySQL dump 10.13  Distrib 8.0.41, for Linux (x86_64)
--
-- Host: localhost    Database: bandcafe_db
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `practice_records`
--

DROP TABLE IF EXISTS `practice_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `practice_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT '0',
  `points` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `practice_records_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `practice_requests` (`id`),
  CONSTRAINT `practice_records_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practice_records`
--

LOCK TABLES `practice_records` WRITE;
/*!40000 ALTER TABLE `practice_records` DISABLE KEYS */;
INSERT INTO `practice_records` VALUES (2,7,19,'2025-06-26',1,2),(3,8,17,'2025-06-26',1,2),(4,12,19,'2025-07-01',1,3),(5,15,19,'2025-07-22',1,3),(6,17,20,'2025-07-29',1,2),(7,19,19,'2025-07-29',1,2),(8,21,10,'2025-07-29',1,2),(9,23,11,'2025-07-29',1,2),(10,24,23,'2025-08-04',1,3),(11,18,21,'2025-08-04',1,3),(12,25,19,'2025-08-05',1,3),(13,26,19,'2025-08-26',1,3),(14,28,19,'2025-09-09',1,2),(15,34,19,'2025-10-09',1,2),(16,31,19,'2025-10-01',1,2),(17,30,19,'2025-09-23',1,2);
/*!40000 ALTER TABLE `practice_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practice_requests`
--

DROP TABLE IF EXISTS `practice_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `practice_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `transport_to_venue` tinyint(1) NOT NULL DEFAULT '0',
  `transport_to_home` tinyint(1) NOT NULL DEFAULT '0',
  `pickup_time` time DEFAULT NULL,
  `pickup_address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dropoff_time` time DEFAULT NULL,
  `dropoff_address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `target_goal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `practice_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practice_requests`
--

LOCK TABLES `practice_requests` WRITE;
/*!40000 ALTER TABLE `practice_requests` DISABLE KEYS */;
INSERT INTO `practice_requests` VALUES (7,19,'2025-06-26','04:15:00','07:00:00',0,0,'00:00:00','','00:00:00','','更加熟悉clarinet，以便下一天的表演能发指地更好','approved'),(8,17,'2025-06-26','14:30:00','16:00:00',0,0,NULL,NULL,NULL,NULL,'Practice Blue','approved'),(12,19,'2025-07-01','14:15:00','17:00:00',0,0,'00:00:00','','00:00:00','','学会clarinet的Rolling in the deep','approved'),(13,10,'2025-07-08','14:30:00','16:30:00',1,1,'14:20:00','Chung Ling High School','16:30:00','Shinevilla Park 61A Condominium','Practice my skills and test out new song','rejected'),(14,11,'2025-07-22','14:50:00','16:50:00',1,0,'14:30:00','SMJK Chung Ling',NULL,NULL,'Practice the greatest showman and blue, discussion with committee, improve intonation','rejected'),(15,19,'2025-07-22','14:30:00','17:00:00',0,0,'00:00:00','','00:00:00','','练clarinet and horn','approved'),(16,19,'2025-07-23','17:00:00','19:00:00',0,0,NULL,NULL,NULL,NULL,'练clarinet and horn','rejected'),(17,20,'2025-07-29','14:30:00','16:30:00',0,0,NULL,NULL,NULL,NULL,'','approved'),(18,21,'2025-08-04','14:30:00','17:00:00',1,0,'14:00:00',' 23, Kampung Baharu, 11400 Ayer Itam, Pulau Pinang',NULL,NULL,'Learn how to play with coordination with the band','approved'),(19,19,'2025-07-29','14:30:00','17:00:00',0,0,NULL,NULL,NULL,NULL,'练Horn 和教人打drum set','approved'),(20,16,'2025-07-30','14:30:00','16:30:00',0,0,NULL,NULL,NULL,NULL,'to go home :))','rejected'),(21,10,'2025-07-29','14:30:00','16:30:00',0,0,NULL,NULL,NULL,NULL,'Practice for performance','approved'),(22,11,'2025-07-29','14:30:00','16:30:00',0,0,NULL,NULL,NULL,NULL,'','pending'),(23,11,'2025-07-29','14:30:00','16:30:00',0,0,NULL,NULL,NULL,NULL,'Practice songs to perform during WM on Wednesday (at BB House)','approved'),(24,23,'2025-08-04','14:30:00','17:00:00',1,0,'14:30:00','Chung Ling High School ',NULL,NULL,'.','approved'),(25,19,'2025-08-05','14:00:00','17:00:00',0,0,NULL,NULL,NULL,NULL,'练horn和教人打鼓','approved'),(26,19,'2025-08-26','14:00:00','17:00:00',0,0,NULL,NULL,NULL,NULL,'Train concert C/D/E/G/A Major scale','approved'),(27,19,'2025-09-01','16:10:00','18:00:00',1,0,'16:00:00','SMK AIR ITAM',NULL,NULL,'Training tuba','rejected'),(28,19,'2025-09-09','14:00:00','17:00:00',0,0,NULL,NULL,NULL,NULL,'Teach Eu Jun drum set and teach Zhi Feng bandsman badge practical part','approved'),(29,19,'2025-09-10','14:00:00','17:00:00',0,0,NULL,NULL,NULL,NULL,'teach Zhi Feng bandsman badge practical part','rejected'),(30,19,'2025-09-23','14:30:00','16:30:00',0,0,NULL,NULL,NULL,NULL,'Teach Eu Jun playing drum set','approved'),(31,19,'2025-10-01','14:30:00','17:30:00',0,0,NULL,NULL,NULL,NULL,'Teach Han Xuan trumpet','approved'),(33,19,'2025-10-07','14:30:00','16:30:00',0,0,NULL,NULL,NULL,NULL,'Teach Eu Jun drumset','pending'),(34,19,'2025-10-09','14:00:00','16:30:00',0,0,NULL,NULL,NULL,NULL,'练let it go 和另一首歌','approved'),(35,19,'2025-10-23','14:00:00','04:30:00',0,0,NULL,NULL,NULL,NULL,'练trumpet,写谱','pending'),(36,19,'2025-10-17','13:00:00','17:00:00',0,0,NULL,NULL,NULL,NULL,'练trumpet,写谱','pending'),(37,12,'2025-11-04','14:30:00','17:30:00',1,0,'14:05:00','Chung Ling High School Gate A',NULL,NULL,'teaching Kean Chung Basic flute(getting the hang of woodwind instrument)','pending'),(38,19,'2025-10-28','14:00:00','05:00:00',0,0,NULL,NULL,NULL,NULL,'练习PPW吹的歌因为星期六要tutti了。','pending'),(39,19,'2025-10-30','14:00:00','17:30:00',0,0,NULL,NULL,NULL,NULL,'教人吹horn','pending');
/*!40000 ALTER TABLE `practice_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `instrument` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `section` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','bandadmin','admin','Percussion','Percussion'),(8,'hon2838','$2y$10$93wdTcpXQNPTFDJbhTQCGODITi3vK9r3bgpBzs0CwGT0ZQiU64qwi','user',NULL,NULL),(10,'Ayden Lee','$2y$10$FQMOSBgoTff29.oSn46cA.6GWmBzdml93ym8jNgk.qQwxRRLz1xiK','user',NULL,NULL),(11,'WLun','$2y$10$oj9O5fdVGZ03./pViOMTkeq/Klo743EGgxSRvhO0urZIU64c9DE0G','user',NULL,NULL),(12,'liqi','$2y$10$YQbwd.0S0ooCdthGzpB4g.2GMAipuu51HHNMUdD9hxI1rCSqD7HiW','user',NULL,NULL),(13,'Ang Yi','$2y$10$/biD7EnK1WWmWE7YGUlHF.MzC4a6JSiVQVvmX0ExMZSuKM59It7f6','user',NULL,NULL),(14,'Ayden','$2y$10$NCeo8dWcce0xl7T3xAxgW.EH1.YQ.OEtVIc3oNc9Ob/g3jSr.Ccy.','user','Clarinet','Woodwind'),(15,'Yi Fei','$2y$10$HwFmIONax/U20X2vg0g6NuIFdgqKn3ofuXew8gWO0PYIHqN38UMMC','admin','0','Percussion'),(16,'Calvin Goh','$2y$10$CyPn.9BqH1b.MGHsGoXmyeScO3o9yE.8RorXp0.n6YEuGEDYn1W8i','user',NULL,NULL),(17,'Kenji','$2y$10$mUo6oorNJereDvpj6aBNLuybMULOM/82DwLH8WizQ70ImcYpi3u6u','user',NULL,NULL),(18,'zzz','$2y$10$pUrHPBNp4wT1agblVfAitO8zXhprJRiGhJi4KGncUl79fDNNGOG.O','user',NULL,NULL),(19,'Kean Chung','$2y$10$rAVIQjdIlzfc6j2pnVFwUOD4eBo8GL0oYDiXLPi2rOu8NpnQm/goS','user',NULL,NULL),(20,'kcyong','$2y$10$qtAkgX2gT5zEhMHhU6CBWOBUPrc07vI26MyjdoyQL0HkE/.og4RB6','user',NULL,NULL),(21,'Kok Zi Rui','$2y$10$halUwvo9/Yt9k4pelRU2cOFAyBKOzACtsxL3xnexD0qtnlcSg5pq2','user',NULL,NULL),(22,'Marcus','$2y$10$E.cgE9bJKwvZI2Aq/vY15.R62V9mSn02WIZmmMB4ywKFJxctQt7ky','user',NULL,NULL),(23,'Marcus Yeap Zi Sheng ','$2y$10$BeItlIltwj3JZJNm25Ga7OTWjHvIpPiaKxummVlOB3Coy2UcExXlW','user',NULL,NULL),(24,'Lucius','$2y$10$TlWyBF0oiABBsY5EWN.ZdeI23e/BpVWxOR8dcUKOg3iGSHR4W6IPu','user',NULL,NULL);
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

-- Dump completed on 2025-11-19 15:40:17
