-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2021 at 04:26 AM
-- Server version: 5.6.41-84.1
-- PHP Version: 7.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zlunxwmy_wp96`
--

-- --------------------------------------------------------

--
-- Table structure for table `wpd5_termmeta`
--

CREATE TABLE `wpd5_termmeta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wpd5_termmeta`
--

INSERT INTO `wpd5_termmeta` (`meta_id`, `term_id`, `meta_key`, `meta_value`) VALUES
(1, 62, 'order', '0'),
(2, 64, 'order', '0'),
(3, 65, 'order', '0'),
(4, 68, 'order', '0'),
(5, 70, 'order', '0'),
(6, 71, 'order', '0'),
(7, 74, 'order_pa_color', '0'),
(8, 77, 'order', '0'),
(9, 78, 'order_pa_size', '0'),
(10, 79, 'order_pa_size', '0'),
(11, 87, 'order', '0'),
(12, 90, 'order_pa_color', '0'),
(13, 94, 'order', '0'),
(14, 96, 'order', '0'),
(15, 97, 'order', '0'),
(16, 99, 'order', '0'),
(17, 100, 'order', '0'),
(18, 102, 'order', '0'),
(19, 104, 'order', '0'),
(20, 105, 'order', '0'),
(21, 106, 'order', '0'),
(22, 107, 'order', '0'),
(23, 109, 'order', '0'),
(24, 111, 'order', '0'),
(25, 112, 'order', '0'),
(26, 113, 'order', '0'),
(27, 114, 'order', '0'),
(28, 115, 'order', '0'),
(29, 116, 'order', '0'),
(30, 117, 'order', '0'),
(31, 118, 'order', '0'),
(32, 121, 'order', '0'),
(33, 122, 'order', '0'),
(34, 137, 'order', '0'),
(35, 138, 'order', '0'),
(36, 140, 'order', '0'),
(37, 189, 'order', '0'),
(38, 190, 'order', '0'),
(39, 191, 'order', '0'),
(40, 192, 'order', '0'),
(41, 61, 'product_count_product_tag', '16'),
(42, 63, 'product_count_product_tag', '11'),
(43, 66, 'product_count_product_tag', '5'),
(44, 67, 'product_count_product_tag', '23'),
(45, 69, 'product_count_product_tag', '62'),
(46, 72, 'product_count_product_tag', '71'),
(47, 73, 'product_count_product_tag', '21'),
(48, 75, 'product_count_product_tag', '16'),
(49, 76, 'product_count_product_tag', '6'),
(50, 85, 'product_count_product_tag', '13'),
(51, 86, 'product_count_product_tag', '37'),
(52, 88, 'product_count_product_tag', '8'),
(53, 89, 'product_count_product_tag', '19'),
(54, 91, 'product_count_product_tag', '5'),
(55, 92, 'product_count_product_tag', '55'),
(56, 93, 'product_count_product_tag', '119'),
(57, 95, 'product_count_product_tag', '24'),
(58, 98, 'product_count_product_tag', '21'),
(59, 101, 'product_count_product_tag', '5'),
(60, 103, 'product_count_product_tag', '61'),
(61, 108, 'product_count_product_tag', '51'),
(62, 110, 'product_count_product_tag', '11'),
(63, 119, 'product_count_product_tag', '4'),
(64, 120, 'product_count_product_tag', '10'),
(65, 123, 'product_count_product_tag', '24'),
(66, 124, 'product_count_product_tag', '16'),
(67, 125, 'product_count_product_tag', '38'),
(68, 126, 'product_count_product_tag', '3'),
(69, 127, 'product_count_product_tag', '26'),
(70, 128, 'product_count_product_tag', '10'),
(71, 129, 'product_count_product_tag', '3'),
(72, 130, 'product_count_product_tag', '5'),
(73, 131, 'product_count_product_tag', '16'),
(74, 132, 'product_count_product_tag', '11'),
(75, 133, 'product_count_product_tag', '6'),
(76, 134, 'product_count_product_tag', '10'),
(77, 135, 'product_count_product_tag', '10'),
(78, 136, 'product_count_product_tag', '5'),
(79, 139, 'product_count_product_tag', '5'),
(80, 141, 'product_count_product_tag', '7'),
(81, 142, 'product_count_product_tag', '5'),
(82, 143, 'product_count_product_tag', '11'),
(83, 144, 'product_count_product_tag', '2'),
(84, 145, 'product_count_product_tag', '20'),
(85, 146, 'product_count_product_tag', '2'),
(86, 147, 'product_count_product_tag', '5'),
(87, 148, 'product_count_product_tag', '5'),
(88, 165, 'product_count_product_tag', '1'),
(89, 175, 'product_count_product_tag', '7'),
(90, 176, 'product_count_product_tag', '7'),
(91, 180, 'product_count_product_tag', '4'),
(92, 181, 'product_count_product_tag', '6'),
(93, 182, 'product_count_product_tag', '6'),
(94, 183, 'product_count_product_tag', '4'),
(95, 184, 'product_count_product_tag', '2'),
(96, 185, 'product_count_product_tag', '2'),
(97, 186, 'product_count_product_tag', '3'),
(98, 187, 'product_count_product_tag', '1'),
(99, 188, 'product_count_product_tag', '1'),
(100, 62, 'product_count_product_cat', '99'),
(101, 64, 'product_count_product_cat', '52'),
(102, 65, 'product_count_product_cat', '62'),
(103, 68, 'product_count_product_cat', '161'),
(104, 70, 'product_count_product_cat', '227'),
(105, 71, 'product_count_product_cat', '317'),
(106, 77, 'product_count_product_cat', '160'),
(107, 16, 'product_count_product_cat', '1'),
(108, 87, 'product_count_product_cat', '51'),
(109, 94, 'product_count_product_cat', '33'),
(110, 96, 'product_count_product_cat', '4'),
(111, 97, 'product_count_product_cat', '107'),
(112, 99, 'product_count_product_cat', '4'),
(113, 100, 'product_count_product_cat', '18'),
(114, 102, 'product_count_product_cat', '15'),
(115, 104, 'product_count_product_cat', '28'),
(116, 105, 'product_count_product_cat', '55'),
(117, 106, 'product_count_product_cat', '18'),
(118, 107, 'product_count_product_cat', '21'),
(119, 109, 'product_count_product_cat', '13'),
(120, 111, 'product_count_product_cat', '9'),
(121, 112, 'product_count_product_cat', '82'),
(122, 113, 'product_count_product_cat', '14'),
(123, 114, 'product_count_product_cat', '17'),
(124, 115, 'product_count_product_cat', '21'),
(125, 116, 'product_count_product_cat', '16'),
(126, 117, 'product_count_product_cat', '109'),
(127, 118, 'product_count_product_cat', '12'),
(128, 121, 'product_count_product_cat', '2'),
(129, 122, 'product_count_product_cat', '16'),
(130, 137, 'product_count_product_cat', '21'),
(131, 138, 'product_count_product_cat', '18'),
(132, 140, 'product_count_product_cat', '6'),
(133, 189, 'product_count_product_cat', '42'),
(134, 190, 'product_count_product_cat', '12'),
(135, 191, 'product_count_product_cat', '2'),
(136, 192, 'product_count_product_cat', '6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wpd5_termmeta`
--
ALTER TABLE `wpd5_termmeta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `term_id` (`term_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wpd5_termmeta`
--
ALTER TABLE `wpd5_termmeta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
