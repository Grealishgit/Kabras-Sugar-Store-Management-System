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
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `town` varchar(100) DEFAULT NULL,
  `type` enum('individual','business') DEFAULT 'individual',
  `status` enum('active','inactive') DEFAULT 'active',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_code` (`customer_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,NULL,'Eugene Hunter','hunter@yahoo.com','0712345678','Machakos',NULL,'individual','active',NULL,'2025-09-28 21:52:08','2025-09-28 21:52:08');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `method` enum('cash','mpesa','card','bank') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('completed','pending','partial') DEFAULT 'completed',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,NULL,1,9,'2025-09-28 23:09:19','mpesa',20.00,'completed',NULL,NULL,'2025-09-28 23:09:19','2025-09-28 23:09:19'),(2,NULL,1,9,'2025-09-28 23:11:08','cash',20000.00,'completed','','','2025-09-28 23:11:08','2025-09-28 23:11:08'),(3,NULL,1,9,'2025-09-28 23:12:07','mpesa',2500.00,'completed','','','2025-09-28 23:12:07','2025-09-28 23:12:07'),(4,NULL,1,9,'2025-09-28 23:13:15','bank',45000.00,'completed','','','2025-09-28 23:13:15','2025-09-28 23:13:15'),(5,NULL,1,9,'2025-09-28 23:15:50','card',50100.00,'completed',NULL,NULL,'2025-09-28 23:15:50','2025-09-28 23:15:50'),(6,NULL,1,9,'2025-09-28 23:18:14','mpesa',5500.00,'completed',NULL,NULL,'2025-09-28 23:18:14','2025-09-28 23:18:14'),(7,NULL,1,9,'2025-09-28 23:24:00','mpesa',4500.00,'completed','TMKY7673JFC','Paid','2025-09-28 23:24:00','2025-09-28 23:24:00'),(8,NULL,1,9,'2025-09-28 23:26:11','card',20500.00,'completed','Equity-BJWA8ASAIA83','Paid Via Equity Bank','2025-09-28 23:26:11','2025-09-28 23:26:11'),(9,NULL,1,9,'2025-09-28 23:27:19','mpesa',56000.00,'completed','TMK01GH233SE4','Paid through Mpesa','2025-09-28 23:27:19','2025-09-28 23:27:19'),(10,NULL,1,9,'2025-09-28 23:28:32','bank',32000.00,'completed','FAM-GHYJ72GE','Paid Via Family Bank','2025-09-28 23:28:32','2025-09-28 23:28:32'),(11,NULL,1,9,'2025-09-28 23:30:58','mpesa',6070.00,'completed','TMK0FV83233SC2','Paid Mpesa','2025-09-28 23:30:58','2025-09-28 23:30:58');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int DEFAULT '0',
  `unit` varchar(20) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `production_date` date DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Sugar','sugar','Brown sugar',1500.00,7,'2','12345678','2024-09-30','2025-09-12','Hunter Suppliers','inactive',9,'2025-09-28 14:17:24','2025-09-29 02:39:29'),(2,'Molasses','Molasses','Molasses for feeds',800.00,20,'5','66345678','2025-10-02','2025-09-02','Alpine Suppliers','active',10,'2025-09-29 02:03:17','2025-09-29 02:03:17'),(3,'Baggasse','Bagasse','Eveniet hic distinctio impedit.',3199.00,622,'15','643','2026-03-17','2026-07-10','Eastern Millers','active',10,'2025-09-29 02:45:47','2025-09-29 02:48:31'),(4,'Ethanol','Ethanol','Natus tempora perspiciatis repellat.',5900.00,329,'23','230','2026-03-11','2025-02-11','Quadra Chemicals','active',10,'2025-09-29 02:46:37','2025-09-29 02:46:37'),(5,'Cane Trash','Cane Trash','Voluptates ea assumenda adipisci voluptas nemo distinctio.',503.00,91,'42','39678','2026-08-04','2024-10-21','Nyanza Cane Co','inactive',10,'2025-09-29 02:47:13','2025-09-29 03:00:16'),(6,'Cane Stack','Sugarcane','Illum sint repellendus amet quia voluptatum expedita corporis.',20000.00,5,'13','505','2024-12-07','2024-10-25','Sharp Millers','active',10,'2025-09-29 02:48:12','2025-09-29 02:48:12'),(7,'Ethanol','Ethanol','Rerum repellendus saepe soluta autem.',4500.00,111,'12','876','2026-06-17','2025-05-17','Sharp Millers','active',10,'2025-09-29 02:59:34','2025-09-29 02:59:34');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sale_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (1,1,1,20,1500.00),(2,2,1,1,1500.00),(3,3,1,3,1500.00),(4,4,1,1,1500.00);
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `sale_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (1,1,9,2300.00,'2025-09-27 21:00:00'),(2,1,9,1500.00,'2025-09-28 20:27:32'),(3,1,9,4500.00,'2025-09-28 20:28:20'),(4,1,9,1500.00,'2025-09-28 23:54:56');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

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
  `last_login` datetime DEFAULT NULL,
  `role` enum('Admin','StoreKeeper','Cashier','Manager','Accountant') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (3,'Main Admin','admin@kabrasugar.com','0742636835','40310909','$2y$12$RpRx0a9k3CRcMF3dAJ71Gefy1m05wXW4VYZzrFKJbxtuXzIbm7YFm','2025-09-29 06:08:44','Admin','2025-09-26 23:47:08','2025-09-29 03:08:44'),(4,'Eugene Hunter','hunter@gmail.com','0742636835','12345678','$2y$12$F1CeDEgALlKjuKdE17K8OO1z3uL1yKpyoeKsQGb4mtiV3eAxtfOcS',NULL,'Accountant','2025-09-27 12:53:21','2025-09-28 10:16:21'),(5,'kabras cashier','cashier@kbrasugar.com','0712345678','12345678','$2y$12$lj7xv9Hkl3i/SYIxzh9F..sp/dJSwlrnO.oHI3B/p2PSiAXIKs5mm',NULL,'Cashier','2025-09-27 13:59:46','2025-09-28 09:55:35'),(6,'kabras accountant','accountant@kbrasugar.com','0712345679','12345678','$2y$12$vfuHT3CY/z8.N0B4d.E8nOSe.7ZlARI.zpsTRlM.ZyO/bwI.5fnwK',NULL,'StoreKeeper','2025-09-27 14:00:21','2025-09-28 09:55:54'),(7,'kabras manager','manager@kbrasugar.com','0712345689','12345678','$2y$12$.7cnA6ISsmfGtOX/oACUVOmStSEl/.a9vgvK3MmtHkhQY1ZiSCN26',NULL,'Manager','2025-09-27 14:00:45','2025-09-28 09:56:09'),(8,'kabras storekeeper','storekeeper@kbrasugar.com','0712347689','12345678','$2y$12$gOd4wCiYa.YYdL/bm/jsrey5Qw8G0REbkC6aNoiFyx01xK52H9KaS',NULL,'StoreKeeper','2025-09-27 14:01:13','2025-09-28 15:32:06'),(9,'Eugene new','hunter1@gmail.com','0742636835','12345678','$2y$12$ZuVqcPe..I.zfOTnt/hT1eSw4mWegptznpVwGZvad9odF/XHH98I.','2025-09-29 03:42:58','Cashier','2025-09-28 11:29:07','2025-09-29 00:42:58'),(10,'New Storekeeper','hunter2@gmail.com','0112346785','40310910','$2y$12$wITps52gqCHBgKfJ.P6/au40XhCM6qH0lElqGFc04ZUrTBUnxxt8G','2025-09-29 05:55:20','StoreKeeper','2025-09-29 00:48:30','2025-09-29 02:55:20');
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

-- Dump completed on 2025-09-29  6:09:09
