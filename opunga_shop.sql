
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
DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;

/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;

/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;

/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `mpesa_code` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;

/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (2,'HP Elite','Laptops',45000.00,'elite.jpg',20),(3,'Roku Smart TV','Televisions',35000.00,'roku.jpg',8),(4,'Samsung A14','Phones',34000.00,'a14.jpg',20),(5,'dell desktop ','computer',15000.00,'dell.jpg',15);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `service_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `device` varchar(100) DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `quoted_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Requested',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `service_bookings` WRITE;
/*!40000 ALTER TABLE `service_bookings` DISABLE KEYS */;

/*!40000 ALTER TABLE `service_bookings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'Virus and Malware Removal','Cyber Security','Full antivirus scan, malware and spyware removal, and cleanup of suspicious startup programs that slow your machine down.',1500.00,'&#128737;',1),(2,'Data Recovery','Cyber Security','Recovery of files deleted by accident, from damaged drives, and from phones that will not boot. Diagnosis is free before any work starts.',3000.00,'&#128190;',1),(3,'Cyber Security Audit','Cyber Security','A full review of your devices and accounts: firewall and antivirus settings, password strength, suspicious apps, and backup readiness.',2500.00,'&#128274;',1),(4,'Firewall and Antivirus Setup','Cyber Security','Installation and configuration of a firewall and antivirus, plus scheduled scans so your devices stay protected after we leave.',1800.00,'&#128737;',1),(5,'Data Backup Setup','Cyber Security','Automatic backups to an external drive or cloud storage, tested and set up so you can recover your files if a device fails.',1200.00,'&#128190;',1),(6,'Cyber Awareness Training','Cyber Security','Group session for staff or students on phishing, strong passwords, and safe everyday use of phones, email and social media.',5000.00,'&#128218;',1),(7,'Laptop and Desktop Servicing','Computer Servicing','Diagnosis and repair of slow machines, overheating, fan noise, crashes, and faulty power supply or battery problems.',1200.00,'&#128187;',1),(8,'System Reformatting','Computer Servicing','Clean reinstall of your operating system with drivers and your documents backed up first. A good fix for a badly corrupted system.',2000.00,'&#128190;',1),(9,'Software Installation','Computer Servicing','Installation and setup of Windows, Office, drivers and other applications, properly licensed and updated.',800.00,'&#128230;',1),(10,'Hardware Upgrade','Computer Servicing','Fitting more RAM, a faster SSD, or a better graphics card to give an older computer a new life.',1000.00,'&#128421;',1),(11,'Computer Cleaning and Tuning','Computer Servicing','Internal dust removal, thermal paste replacement, and tuning so a hot and noisy machine runs cool and quiet again.',1000.00,'&#129525;',1),(12,'Wi-Fi and Network Setup','Networking','Router installation and configuration for a home or small office, including Wi-Fi coverage, password security, and device connections.',1500.00,'&#128225;',1),(13,'Wi-Fi Troubleshooting','Networking','On-site diagnosis of dropped connections, weak signal, and dead zones, with a clear explanation of the cause before repair.',1000.00,'&#128225;',1),(14,'Network Cabling','Networking','Structured cabling for an office or home, tested and labelled so every point is known to work.',2500.00,'&#128240;',1),(15,'Printer Installation and Printing','Networking','Printer setup, Wi-Fi printing, cartridge and drum replacement, and bulk printing for offices, schools and events.',800.00,'&#128424;',1),(16,'Document Scanning and Printing','Networking','We scan your documents to PDF or email and handle printing jobs, finishing and binding for reports and coursework.',300.00,'&#128196;',1),(17,'Phone Screen Replacement','Phone Repair','Replacement of cracked or dead screens on Samsung, Tecno, Infinix, Oppo and other common models.',3500.00,'&#128241;',1),(18,'Phone Battery Replacement','Phone Repair','New battery fitted when your phone dies suddenly, refuses to charge, or shuts down at low percentage.',2000.00,'&#128267;',1),(19,'Phone Software Repair','Phone Repair','Fix for phones that boot to a logo and restart, hang on the logo, or are stuck on a black screen. Data is preserved where possible.',1500.00,'&#128241;',1),(20,'Phone Unlocking','Phone Repair','Network unlock for phones bought locked to a single carrier, so you can use any SIM. Proof of ownership required.',1500.00,'&#128273;',1);
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'customer',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Opunga Nickson','opunga@gmail.com','$2y$10$Kc.TAn3la0psyG/4.yrsRe8I26BVs9OBnJCNZIUw7U6GvxcSVL/U6',NULL,NULL,'admin');
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

