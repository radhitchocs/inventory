-- MySQL dump 10.13  Distrib 8.0.39, for Linux (x86_64)
--
-- Host: localhost    Database: inventory
-- ------------------------------------------------------
-- Server version	8.0.39-0ubuntu0.24.04.2

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
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kontak` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'ribo','0987654','radhitchocs@gmail.com','$2y$10$xBhoGoodNg9Wlj5km3MTC.v0GL4OORDTDHfy/7NZ7nstxOv9mHZ7i'),(2,'Radhitchocs',NULL,'radhitchocs','password'),(3,'ayam','09876544','ayam@mail.com','$2y$10$/sNpJVIY7CxDMs6nkGyH0usUP2zBicS7qvnIvrFf1eDBHOU9Y3edG');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `barang_id` int NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(100) NOT NULL,
  `jenis_barang` enum('ELEKTRONIK','KITCHEN','FURNITURE','PAKAIAN','BUKU','PERALATAN_OLAH_RAGA','KECANTIKAN','ALAT_TULIS','MAINAN','KENDARAAN') DEFAULT NULL,
  `kuantitas_stok` int DEFAULT '0',
  `lokasi_gudang_id` int DEFAULT NULL,
  `barcode` varchar(50) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  PRIMARY KEY (`barang_id`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `lokasi_gudang_id` (`lokasi_gudang_id`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`lokasi_gudang_id`) REFERENCES `storage_unit` (`gudang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (7,'Laptop','ELEKTRONIK',109,2,'66e5b020ae45f',40000.00),(8,'Hape','ELEKTRONIK',9,2,'66e5b03e2ddd0',4000.00);
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `storage_unit`
--

DROP TABLE IF EXISTS `storage_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `storage_unit` (
  `gudang_id` int NOT NULL AUTO_INCREMENT,
  `nama_gudang` varchar(100) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  PRIMARY KEY (`gudang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storage_unit`
--

LOCK TABLES `storage_unit` WRITE;
/*!40000 ALTER TABLE `storage_unit` DISABLE KEYS */;
INSERT INTO `storage_unit` VALUES (2,'Gudang 1','Lawson'),(3,'Gudang 2','Mexico');
/*!40000 ALTER TABLE `storage_unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_inventory`
--

DROP TABLE IF EXISTS `transaksi_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_inventory` (
  `transaksi_id` int NOT NULL AUTO_INCREMENT,
  `barang_id` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `tipe_transaksi` enum('masuk','keluar') NOT NULL,
  `tanggal_transaksi` datetime DEFAULT CURRENT_TIMESTAMP,
  `admin_id` int DEFAULT NULL,
  PRIMARY KEY (`transaksi_id`),
  KEY `admin_id` (`admin_id`),
  KEY `transaksi_inventory_ibfk_1` (`barang_id`),
  CONSTRAINT `transaksi_inventory_ibfk_1` FOREIGN KEY (`barang_id`) REFERENCES `inventory` (`barang_id`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_inventory_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_inventory`
--

LOCK TABLES `transaksi_inventory` WRITE;
/*!40000 ALTER TABLE `transaksi_inventory` DISABLE KEYS */;
INSERT INTO `transaksi_inventory` VALUES (11,7,100,'masuk','2024-09-15 19:58:11',1);
/*!40000 ALTER TABLE `transaksi_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor`
--

DROP TABLE IF EXISTS `vendor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor` (
  `vendor_id` int NOT NULL AUTO_INCREMENT,
  `nama_vendor` varchar(100) NOT NULL,
  `kontak` varchar(15) DEFAULT NULL,
  `barang_id` int DEFAULT NULL,
  `nomor_invoice` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`vendor_id`),
  KEY `fk_barang_id` (`barang_id`),
  CONSTRAINT `fk_barang_id` FOREIGN KEY (`barang_id`) REFERENCES `inventory` (`barang_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor`
--

LOCK TABLES `vendor` WRITE;
/*!40000 ALTER TABLE `vendor` DISABLE KEYS */;
INSERT INTO `vendor` VALUES (6,'radhit','0987654',8,'0987654321');
/*!40000 ALTER TABLE `vendor` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-15 20:31:09
