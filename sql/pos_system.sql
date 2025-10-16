-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2025 at 05:04 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `banking`
--

CREATE TABLE `banking` (
  `id` int(11) NOT NULL,
  `type` enum('withdrawal','transfer','deposit') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banking`
--

INSERT INTO `banking` (`id`, `type`, `amount`, `user_id`, `created_at`) VALUES
(1, 'deposit', 500.00, 1, '2025-09-19 09:01:30'),
(2, 'withdrawal', 200.00, 2, '2025-09-19 09:01:30'),
(3, 'transfer', 300.00, 3, '2025-09-19 09:01:30'),
(4, 'deposit', 150.00, 4, '2025-09-19 09:01:30'),
(5, 'withdrawal', 50.00, 5, '2025-09-19 09:01:30'),
(7, 'transfer', 100.00, 7, '2025-09-19 09:01:30'),
(8, 'withdrawal', 75.00, 8, '2025-09-19 09:01:30'),
(9, 'deposit', 250.00, 9, '2025-09-19 09:01:30'),
(10, 'transfer', 600.00, 10, '2025-09-19 09:01:30'),
(11, 'withdrawal', 450001.00, 5, '2025-09-19 10:07:39'),
(12, 'withdrawal', 567.00, 5, '2025-09-21 12:42:29');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Electronics'),
(2, 'Groceries'),
(3, 'Clothing'),
(4, 'Stationery'),
(5, 'Toys'),
(6, 'Household'),
(7, 'Beverages'),
(8, 'Cosmetics'),
(9, 'Hardware'),
(10, 'Sports'),
(11, 'Utilities'),
(12, 'Transportation'),
(13, 'Maintenance'),
(14, 'Office Supplies'),
(15, 'Food & Beverages');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `name`, `amount`, `category_id`, `user_id`, `created_at`) VALUES
(2, 'Water', 50.00, 1, 2, '2025-09-19 09:01:30'),
(3, 'Stationery', 30.00, 4, 3, '2025-09-19 09:01:30'),
(4, 'Snacks', 20.00, 2, 4, '2025-09-19 09:01:30'),
(5, 'Cleaning', 40.00, 6, 5, '2025-09-19 09:01:30'),
(6, 'Transport', 25.00, 10, 6, '2025-09-19 09:01:30'),
(7, 'Maintenance', 60.00, 9, 7, '2025-09-19 09:01:30'),
(8, 'Internet', 80.00, 1, 8, '2025-09-19 09:01:30'),
(9, 'Fuel', 70.00, 10, 9, '2025-09-19 09:01:30'),
(10, 'Printer Ink', 35.00, 4, 10, '2025-09-19 09:01:30'),
(13, NULL, 3.00, 8, 5, '2025-09-21 18:07:12'),
(14, NULL, 5.00, 8, 5, '2025-09-21 18:07:50'),
(15, 'dresses', 6.00, 1, 5, '2025-09-21 18:11:00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `barcode`, `price`, `stock`, `category_id`, `created_at`) VALUES
(1, 'Laptop', 'B1001', 1200.50, 10, 1, '2025-09-19 09:01:30'),
(2, 'Apple', 'B1002', 0.50, 100, 2, '2025-09-19 09:01:30'),
(3, 'T-shirt', 'B1003', 15.00, 50, 3, '2025-09-19 09:01:30'),
(4, 'Notebook', 'B1004', 2.50, 200, 4, '2025-09-19 09:01:30'),
(5, 'Toy Car', 'B1005', 10.00, 30, 5, '2025-09-19 09:01:30'),
(6, 'Vacuum Cleaner', 'B1006', 150.00, 5, 6, '2025-09-19 09:01:30'),
(7, 'Coke', 'B1007', 1.00, 150, 7, '2025-09-19 09:01:30'),
(8, 'Lipstick', 'B1008', 12.50, 29, 8, '2025-09-19 09:01:30'),
(9, 'Hammer', 'B1009', 20.00, 4, 9, '2025-09-19 09:01:30'),
(10, 'Football', 'B1010', 25.00, 20, 10, '2025-09-19 09:01:30');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('sales','products','expenses','banking') DEFAULT NULL,
  `content` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `start_date`, `end_date`, `generated_by`, `created_at`, `type`, `content`) VALUES
(11, '2025-09-19', '2025-09-19', 5, '2025-09-19 16:06:49', 'sales', '[]'),
(12, '2025-09-12', '2025-09-19', 5, '2025-09-19 16:12:00', 'sales', '[]'),
(13, '2025-09-19', '2025-09-19', 5, '2025-09-19 17:39:06', 'sales', '[]'),
(14, '2025-09-19', '2025-09-19', 5, '2025-09-19 18:10:17', 'banking', '[]'),
(15, '2025-09-20', '2025-09-20', 5, '2025-09-20 18:33:16', 'sales', '[]'),
(16, '2025-09-21', '2025-09-21', 6, '2025-09-21 13:42:07', 'sales', '[]'),
(17, '2025-09-22', '2025-09-22', 5, '2025-09-22 09:54:23', 'sales', '[]');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `total_price`, `payment_method`, `created_at`) VALUES
(1, 1, 240.00, 'cash', '2025-09-21 07:00:00'),
(2, 2, 400.00, 'card', '2025-09-21 07:30:00'),
(3, 1, 600.00, 'mobile_money', '2025-09-21 08:00:00'),
(4, 3, 800.00, 'cash', '2025-09-21 08:30:00'),
(5, 2, 600.00, 'card', '2025-09-21 09:00:00'),
(6, 3, 500.00, 'mobile_money', '2025-09-21 09:30:00'),
(16, 6, 100.00, 'cash', '2025-09-21 13:15:31');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`) VALUES
(2, 16, 9, 5, 20.00),
(3, 1, 1, 2, 10.00),
(4, 2, 2, 1, 15.00),
(5, 3, 3, 5, 20.00),
(6, 4, 4, 3, 12.50),
(7, 5, 5, 4, 8.75);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('cashier','manager','admin','inventory') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `role`, `created_at`, `password`) VALUES
(1, 'Alice', 'alice@example.com', '0712345671', 'manager', '2025-09-19 09:01:30', '$2y$10$zE4Qdg8hsUN40KqTNL.ef.3qVXAuFF7mL59FSi52PnMfU4.U.JDji'),
(2, 'Bob', 'bob@example.com', '0712345672', 'cashier', '2025-09-19 09:01:30', '$2y$10$z7aFxR1snkdlubuh5yTh7O2FrrWHk4C1K/ilg7Mep2EF3AAQ2FELS'),
(3, 'Carol', 'carol@example.com', '0712345673', 'cashier', '2025-09-19 09:01:30', '$2y$10$VJNVIe9p3btZRnd4DHTYV.NtZIA0YMCgHH.cN/wKZQjRRKyh4gi6i'),
(4, 'David', 'david@example.com', '0712345674', 'inventory', '2025-09-19 09:01:30', '$2y$10$n1l5MQBookVmS5Xmg2kfc.8p163jntXJWKbggrUlqjxrU2dc2Vjea'),
(5, 'Eve', 'eve@example.com', '0712345675', 'admin', '2025-09-19 09:01:30', '$2y$10$qkTuUggB6.Pp1cEiTN83C.lSGXfMPBSY8FqtET7pClQ8Py69/oUl2'),
(6, 'Frank', 'frank@example.com', '0712345676', 'cashier', '2025-09-19 09:01:30', '$2y$10$kkIMjWJzBSuEZgRCxvc2k.m.l5A1YacacDEXpPzYi.0i0jBrxevPG'),
(7, 'Grace', 'grace@example.com', '0712345677', 'manager', '2025-09-19 09:01:30', '$2y$10$e4NUc7gCZAJ4INTSzz651OX0eHNW2QpW7YeewXoqk6caBKoHiY5yW'),
(8, 'Hank', 'hank@example.com', '0712345678', 'inventory', '2025-09-19 09:01:30', '$2y$10$5emM4TAtBdomtNAnRDLjZ.VOdqkLWbnnG9PsG8K6jBWcTNIJZMFQS'),
(9, 'Ivy', 'ivy@example.com', '0712345679', 'cashier', '2025-09-19 09:01:30', '$2y$10$HMTaCHc5hpMkc0Rh0RFdhuGAGANQu5nk9sTWsn.ECCuUopRB/0TyC'),
(10, 'Jack', 'jack@example.com', '0712345680', 'manager', '2025-09-19 09:01:30', '$2y$10$gQ30PBvYTSs2WkxdhB6QjO3CnsH98xdxftsoiMTE1anzb6a8ARxnm');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banking`
--
ALTER TABLE `banking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banking`
--
ALTER TABLE `banking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `banking`
--
ALTER TABLE `banking`
  ADD CONSTRAINT `banking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
