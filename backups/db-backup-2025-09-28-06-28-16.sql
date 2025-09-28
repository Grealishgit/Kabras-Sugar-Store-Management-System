-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: kabras_store
-- ------------------------------------------------------
-- Server version	8.0.43

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
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `national_id` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','StoreKeeper','Cashier','Manager','Accountant') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (3,'Main Admin','admin@kabrasugar.com','0742636835','40310909','$2y$12$RpRx0a9k3CRcMF3dAJ71Gefy1m05wXW4VYZzrFKJbxtuXzIbm7YFm','Admin','2025-09-26 23:47:08','2025-09-28 10:16:10'),(4,'Eugene Hunter','hunter@gmail.com','0742636835','12345678','$2y$12$F1CeDEgALlKjuKdE17K8OO1z3uL1yKpyoeKsQGb4mtiV3eAxtfOcS','Accountant','2025-09-27 12:53:21','2025-09-28 10:16:21'),(5,'kabras cashier','cashier@kbrasugar.com','0712345678','12345678','$2y$12$lj7xv9Hkl3i/SYIxzh9F..sp/dJSwlrnO.oHI3B/p2PSiAXIKs5mm','Cashier','2025-09-27 13:59:46','2025-09-28 09:55:35'),(6,'kabras accountant','accountant@kbrasugar.com','0712345679','12345678','$2y$12$vfuHT3CY/z8.N0B4d.E8nOSe.7ZlARI.zpsTRlM.ZyO/bwI.5fnwK','StoreKeeper','2025-09-27 14:00:21','2025-09-28 09:55:54'),(7,'kabras manager','manager@kbrasugar.com','0712345689','12345678','$2y$12$.7cnA6ISsmfGtOX/oACUVOmStSEl/.a9vgvK3MmtHkhQY1ZiSCN26','Manager','2025-09-27 14:00:45','2025-09-28 09:56:09'),(8,'kabras storekeeper','storekeeper@kbrasugar.com','0712347689','12345678','$2y$12$gOd4wCiYa.YYdL/bm/jsrey5Qw8G0REbkC6aNoiFyx01xK52H9KaS','Cashier','2025-09-27 14:01:13','2025-09-28 10:05:53');
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

-- Dump completed on 2025-09-28 13:28:17
