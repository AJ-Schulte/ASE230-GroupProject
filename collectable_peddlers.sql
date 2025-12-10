-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 09, 2025 at 04:32 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `collectable_peddlers`
--
-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `listing_id`, `added_at`) VALUES
(1, 1, 1, '2025-12-09 15:05:11'),
(2, 1, 2, '2025-12-09 15:05:11'),
(3, 2, 3, '2025-12-09 15:05:11'),
(4, 3, 1, '2025-12-09 15:05:11');


-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `name`, `description`) VALUES
(1, 'Trading Cards', 'Collectible card games and singles'),
(2, 'Comics', 'Comic books and graphic novels'),
(3, 'Figures', 'Collectible figurines and statues');

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `collection_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection`
--

INSERT INTO `collection` (`collection_id`, `user_id`, `name`, `created_at`) VALUES
(1, 1, 'AJ’s Favorite Cards', '2025-11-11 11:01:58'),
(2, 1, 'AJ’s Comic Finds', '2025-11-11 11:01:58'),
(3, 2, 'Riley’s Mecha Collection', '2025-11-11 11:01:58');

-- --------------------------------------------------------

--
-- Table structure for table `collection_listing`
--

CREATE TABLE `collection_listing` (
  `collection_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection_listing`
--

INSERT INTO `collection_listing` (`collection_id`, `listing_id`, `added_at`) VALUES
(1, 1, '2025-11-11 11:01:58'),
(2, 2, '2025-11-11 11:01:58'),
(3, 3, '2025-11-11 11:01:58');

-- --------------------------------------------------------

--
-- Table structure for table `listing`
--

CREATE TABLE `listing` (
  `listing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `condition` enum('New','Like New','Used','Damaged') NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` > 0),
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `sold_at` datetime DEFAULT NULL,
  `status` enum('active','sold','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listing`
--

INSERT INTO `listing` (`listing_id`, `user_id`, `title`, `condition`, `description`, `price`, `image_url`, `created_at`, `sold_at`, `status`) VALUES
(1, 1, 'Blue-Eyes White Dragon', 'Like New', 'Original Yu-Gi-Oh! holographic card', 250.00, 'images/blueeyes.jpg', '2025-11-11 11:01:58', NULL, 'active'),
(2, 2, 'Spider-Man #1 Comic', 'Used', 'Vintage comic in great condition', 120.00, 'images/spiderman.jpg', '2025-11-11 11:01:58', NULL, 'active'),
(3, 3, 'Gundam RX-78 Figure', 'New', 'Unopened original model kit', 85.00, 'images/gundam.jpg', '2025-11-11 11:01:58', NULL, 'active'),
(4, 3, '1999 Fender American Fat Telecaster', 'Used', 'Good condition 1999 Fender Telecaster. All stock hardware, except for strap pins. Some dings in the body and a small finish chip.', 1300.00, 'images/telecaster.jpg', '2025-12-08 22:44:42', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `listing_category`
--

CREATE TABLE `listing_category` (
  `listing_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listing_category`
--

INSERT INTO `listing_category` (`listing_id`, `category_id`) VALUES
(1, 1),
(2, 2),
(3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','canceled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`transaction_id`, `buyer_id`, `seller_id`, `transaction_date`, `total_price`, `status`) VALUES
(1, 2, 1, '2025-11-11 11:01:58', 250.00, 'completed'),
(2, 3, 2, '2025-11-11 11:01:58', 120.00, 'completed'),
(3, 1, 3, '2025-11-11 11:01:58', 85.00, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_listing`
--

CREATE TABLE `transaction_listing` (
  `transaction_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price_at_sale` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_listing`
--

INSERT INTO `transaction_listing` (`transaction_id`, `listing_id`, `quantity`, `price_at_sale`) VALUES
(1, 1, 1, 250.00),
(2, 2, 1, 120.00),
(3, 3, 1, 85.00);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone_num` varchar(20) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `name`, `phone_num`, `is_admin`) VALUES
(1, 'aj_schulte', 'aj@example.com', 'pass1234', 'AJ Schulte', '123-456-7890', 1),
(2, 'riley_fit', 'riley@example.com', 'pass1234', 'Riley Fitzgerald', '555-111-2222', 0),
(3, 'joe_g', 'joe@example.com', 'pass1234', 'Joseph Gallucci', '555-333-4444', 0),
(7, 'joe@example.com', 'joe@example.com@example.com', 'pass1234', NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`collection_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `collection_listing`
--
ALTER TABLE `collection_listing`
  ADD PRIMARY KEY (`collection_id`,`listing_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `listing`
--
ALTER TABLE `listing`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `listing_category`
--
ALTER TABLE `listing_category`
  ADD PRIMARY KEY (`listing_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `transaction_listing`
--
ALTER TABLE `transaction_listing`
  ADD PRIMARY KEY (`transaction_id`,`listing_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `listing`
--
ALTER TABLE `listing`
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `collection`
--
ALTER TABLE `collection`
  ADD CONSTRAINT `collection_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `collection_listing`
--
ALTER TABLE `collection_listing`
  ADD CONSTRAINT `collection_listing_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_listing_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `listing`
--
ALTER TABLE `listing`
  ADD CONSTRAINT `listing_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `listing_category`
--
ALTER TABLE `listing_category`
  ADD CONSTRAINT `listing_category_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `listing_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `transaction_listing`
--
ALTER TABLE `transaction_listing`
  ADD CONSTRAINT `transaction_listing_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_listing_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
