-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 09 sep. 2025 à 19:19
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `resto_platform`
--

-- --------------------------------------------------------

--
-- Structure de la table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `menus`
--

INSERT INTO `menus` (`id`, `restaurant_id`, `name`, `description`, `price`, `category`, `is_available`, `created_at`, `updated_at`, `image_url`) VALUES
(1, 1, 'Menu du Jour', 'Entr?e + Plat + Dessert', 3000.00, 'menu', 1, '2025-08-01 21:09:27', '2025-08-25 11:27:29', '/resto_plateform/assets/img/products/68ab7b39d7324.jpeg'),
(2, 1, 'Steak Frites', 'Steak de boeuf avec frites maison', 2500.00, 'plat principal', 1, '2025-08-01 21:09:27', '2025-08-25 11:27:55', '/resto_plateform/assets/img/products/68ab7b4f9d194.jpeg'),
(4, 2, 'Pizza Pepperoni', 'Tomate, mozzarella, pepperoni', 1800.00, 'pizza', 1, '2025-08-01 21:09:27', '2025-08-25 11:28:55', '/resto_plateform/assets/img/products/68ab7b89eb7e4.jpeg'),
(5, 1, 'pizza complet', 'boeuf et viandes', 3000.00, 'pizza', 1, '2025-08-17 14:13:46', '2025-08-25 11:27:41', '/resto_plateform/assets/img/products/68ab7b45a3bbd.jpeg'),
(6, 2, 'Pizza Margherita', 'Tomate, mozzarella, basilic', 1800.00, 'burger', 1, '2025-08-17 16:23:22', '2025-08-25 11:28:39', '/resto_plateform/assets/img/products/68ab7b748aa6d.jpeg'),
(7, 2, 'burger', 'viande hacher', 2000.00, 'pizza', 1, '2025-08-17 16:50:57', '2025-08-25 11:28:47', '/resto_plateform/assets/img/products/68ab7b7d9d29b.jpeg'),
(8, 6, 'Pizza Margherita', 'nouveau produit', 3000.00, 'menu', 1, '2025-08-17 22:02:22', '2025-08-25 11:27:08', '/resto_plateform/assets/img/products/68abcbfb40f58.jpeg'),
(10, 7, 'pazza', 'culinaire a chaud', 3000.00, 'pizza', 1, '2025-08-23 22:51:31', '2025-08-25 11:28:28', '/resto_plateform/assets/img/products/68ab7bb89c3c3.jpeg'),
(11, 7, 'burger', 'burger complet', 1500.00, 'burger', 1, '2025-08-23 23:02:28', '2025-08-25 11:28:09', '/resto_plateform/assets/img/products/68ab7ba3e224d.jpeg'),
(12, 7, 'burger', 'a bnbgefhijdz,euhvneughivnfueghnjvfceuhvjneugfhvcnufhdddddddddddddddddddddddddddddddfggggghhhhhhhhhhhhhhhhhhhhhhhggggggggggggggggggg', 1300.00, 'burger', 1, '2025-08-24 01:26:31', '2025-08-27 16:57:10', '/resto_plateform/assets/img/products/68ab7bae99888.jpeg'),
(13, 7, 'Pizza Margherita', 'exemplaire a la sauce', 12.00, 'pizza', 1, '2025-09-01 17:47:33', '2025-09-01 17:47:33', '/resto_plateform/assets/img/products/68b5dc359d7b3.jpeg');

-- --------------------------------------------------------

--
-- Structure de la table `notifications_log`
--

CREATE TABLE `notifications_log` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `restaurant_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','preparing','ready','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(20) DEFAULT 'cash',
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_city` varchar(100) DEFAULT NULL,
  `delivery_building` varchar(100) DEFAULT NULL,
  `delivery_apartment` varchar(100) DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `delivery_phone` varchar(20) DEFAULT NULL,
  `client_first_name` varchar(50) DEFAULT NULL,
  `client_last_name` varchar(50) DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `guest_email`, `guest_phone`, `guest_name`, `restaurant_id`, `total_price`, `status`, `payment_method`, `delivery_address`, `delivery_city`, `delivery_building`, `delivery_apartment`, `delivery_instructions`, `delivery_phone`, `client_first_name`, `client_last_name`, `client_email`, `created_at`, `updated_at`) VALUES
(6, 1, NULL, NULL, NULL, 7, 2500.00, 'pending', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-30 15:14:19', '2025-08-30 15:14:19'),
(7, 1, NULL, NULL, NULL, 6, 2500.00, 'pending', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-30 15:22:41', '2025-08-30 15:22:41'),
(8, 1, NULL, NULL, NULL, 7, 3800.00, 'pending', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-30 15:31:10', '2025-08-30 15:31:10'),
(9, 1, NULL, NULL, NULL, 7, 2500.00, 'pending', 'cash', NULL, NULL, '', '', '', NULL, NULL, NULL, NULL, '2025-08-31 16:48:07', '2025-08-31 16:48:07'),
(10, 1, NULL, NULL, NULL, 7, 2500.00, 'pending', 'cash', NULL, NULL, '', '', '', NULL, NULL, NULL, NULL, '2025-08-31 16:51:52', '2025-08-31 16:51:52'),
(11, 1, NULL, NULL, NULL, 7, 2500.00, 'delivered', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 17:15:21', '2025-08-31 17:20:29'),
(12, 1, NULL, NULL, NULL, 7, 2500.00, 'cancelled', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 17:15:43', '2025-08-31 18:25:50'),
(13, 1, NULL, NULL, NULL, 1, 2500.00, 'cancelled', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 17:21:59', '2025-08-31 18:25:34'),
(14, 1, NULL, NULL, NULL, 1, 10000.00, 'cancelled', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 18:09:55', '2025-08-31 18:25:17'),
(15, 1, NULL, NULL, NULL, 6, 10000.00, 'delivered', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 18:28:35', '2025-08-31 21:11:21'),
(16, 1, NULL, NULL, NULL, 1, 10000.00, 'delivered', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 18:43:55', '2025-08-31 21:06:19'),
(17, 1, NULL, NULL, NULL, 7, 13000.00, 'pending', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 21:24:07', '2025-08-31 21:24:07'),
(18, 1, NULL, NULL, NULL, 7, 2500.00, 'pending', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 00:03:09', '2025-09-01 00:03:09'),
(19, 1, NULL, NULL, NULL, 2, 3000.00, 'ready', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 00:04:46', '2025-09-01 01:43:22'),
(20, 1, NULL, NULL, NULL, 7, 2500.00, 'pending', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 09:16:28', '2025-09-01 09:16:28'),
(21, 1, NULL, NULL, NULL, 7, 2500.00, 'preparing', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 13:35:41', '2025-09-01 17:32:30'),
(22, 1, NULL, NULL, NULL, 7, 4000.00, 'delivered', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 19:06:49', '2025-09-02 22:37:28'),
(23, 1, NULL, NULL, NULL, 1, 4000.00, 'delivered', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-02 22:39:30', '2025-09-02 22:40:01'),
(24, 1, NULL, NULL, NULL, 6, 7000.00, 'pending', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-03 01:54:56', '2025-09-03 01:54:56'),
(25, 1, NULL, NULL, NULL, 7, 7000.00, 'pending', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-03 01:58:43', '2025-09-03 01:58:43'),
(26, 1, NULL, NULL, NULL, 7, 7000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 02:10:10', '2025-09-03 02:10:10'),
(27, 1, NULL, NULL, NULL, 6, 7000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 02:16:50', '2025-09-03 02:16:50'),
(28, 1, NULL, NULL, NULL, 6, 7000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 15:31:44', '2025-09-03 15:31:44'),
(29, 1, NULL, NULL, NULL, 6, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 15:42:40', '2025-09-03 15:42:40'),
(30, 1, NULL, NULL, NULL, 6, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 16:16:52', '2025-09-03 16:16:52'),
(31, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 16:22:19', '2025-09-03 16:22:19'),
(32, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 16:34:05', '2025-09-03 16:34:05'),
(33, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 16:57:25', '2025-09-03 16:57:25'),
(34, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 17:05:50', '2025-09-03 17:05:50'),
(35, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 17:30:08', '2025-09-03 17:30:08'),
(36, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 17:31:53', '2025-09-03 17:31:53'),
(37, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 17:37:19', '2025-09-03 17:37:19'),
(38, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 18:37:40', '2025-09-03 18:37:40'),
(39, 1, NULL, NULL, NULL, 7, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 18:52:40', '2025-09-03 18:53:53'),
(40, 1, NULL, NULL, NULL, 6, 2500.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 22:49:29', '2025-09-03 22:49:29'),
(41, 1, NULL, NULL, NULL, 6, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 23:02:41', '2025-09-03 23:02:41'),
(42, 1, NULL, NULL, NULL, 6, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 23:05:38', '2025-09-03 23:05:38'),
(43, 1, NULL, NULL, NULL, 6, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 23:06:49', '2025-09-03 23:06:49'),
(44, 1, NULL, NULL, NULL, 6, 4000.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 23:12:02', '2025-09-03 23:12:02'),
(45, 1, NULL, NULL, NULL, 7, 2500.00, 'pending', 'cash', 'Thiés/mbour1', 'Thiés', '', '', '', NULL, NULL, NULL, NULL, '2025-09-03 23:12:48', '2025-09-03 23:12:48');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `options` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `quantity`, `unit_price`, `options`) VALUES
(1, 6, 11, 1, 1500.00, NULL),
(2, 7, 11, 1, 1500.00, NULL),
(3, 8, 11, 1, 1500.00, NULL),
(4, 8, 12, 1, 1300.00, NULL),
(5, 11, 11, 1, 1500.00, NULL),
(6, 12, 11, 1, 1500.00, NULL),
(7, 13, 11, 1, 1500.00, NULL),
(8, 14, 5, 3, 3000.00, NULL),
(9, 15, 5, 3, 3000.00, NULL),
(10, 16, 5, 3, 3000.00, NULL),
(11, 17, 5, 4, 3000.00, NULL),
(12, 18, 11, 1, 1500.00, NULL),
(13, 19, 7, 1, 2000.00, NULL),
(14, 20, 11, 1, 1500.00, NULL),
(15, 21, 11, 1, 1500.00, NULL),
(16, 22, 10, 1, 3000.00, NULL),
(17, 23, 10, 1, 3000.00, NULL),
(18, 24, 8, 2, 3000.00, NULL),
(19, 25, 8, 2, 3000.00, NULL),
(20, 26, 8, 2, 3000.00, NULL),
(21, 27, 8, 2, 3000.00, NULL),
(22, 28, 8, 2, 3000.00, NULL),
(23, 29, 8, 1, 3000.00, NULL),
(24, 30, 8, 1, 3000.00, NULL),
(25, 31, 8, 1, 3000.00, NULL),
(26, 32, 8, 1, 3000.00, NULL),
(27, 33, 8, 1, 3000.00, NULL),
(28, 34, 8, 1, 3000.00, NULL),
(29, 35, 8, 1, 3000.00, NULL),
(30, 36, 8, 1, 3000.00, NULL),
(31, 37, 8, 1, 3000.00, NULL),
(32, 38, 8, 1, 3000.00, NULL),
(33, 39, 8, 1, 3000.00, NULL),
(34, 40, 11, 1, 1500.00, NULL),
(35, 41, 8, 1, 3000.00, NULL),
(36, 42, 8, 1, 3000.00, NULL),
(37, 43, 8, 1, 3000.00, NULL),
(38, 44, 8, 1, 3000.00, NULL),
(39, 45, 11, 1, 1500.00, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('wave','orange','free','visa') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `placeholder_id` int(11) DEFAULT 1,
  `placeholder_pattern` varchar(50) DEFAULT 'default',
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `has_delivery` tinyint(1) DEFAULT 1,
  `has_pickup` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `restaurants`
--

INSERT INTO `restaurants` (`id`, `user_id`, `name`, `description`, `address`, `phone`, `image_url`, `logo_url`, `created_at`, `updated_at`, `placeholder_id`, `placeholder_pattern`, `lat`, `lng`, `has_delivery`, `has_pickup`) VALUES
(1, 2, 'Restaurant...', 'Cuisine fran?aise traditionnelle', '123 Rue de la Paix, Paris', '0123456789', '/resto_plateform/assets/img/restaurants/68ab83223e21f.jpeg', NULL, '2025-08-01 21:09:21', '2025-08-29 23:16:23', 1, 'default', NULL, NULL, 0, 0),
(2, 2, 'Restaurant...', 'Pizzas artisanales au feu de bois', '456 Avenue des Champs, Lyon', '0987654321', '/resto_plateform/assets/img/restaurants/68ab8334d1028.jpeg', NULL, '2025-08-01 21:09:21', '2025-08-29 23:16:39', 1, 'default', NULL, NULL, 1, 1),
(6, 19, 'Restaurant...', 'votre restaurant moin cher', 'Thiés/mbour1', '781155609', '/resto_plateform/assets/img/restaurants/68ab831895bd6.jpeg', NULL, '2025-08-17 16:45:56', '2025-08-29 23:16:06', 1, 'default', NULL, NULL, 1, 1),
(7, 245, 'Restaurant...', 'tous vos plat preferé', 'palais d or restaurant Thiès', '764021932', '/resto_plateform/assets/img/restaurants/68ab830dc8dc6.jpeg', NULL, '2025-08-23 22:08:43', '2025-08-29 23:22:16', 1, 'default', NULL, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','restaurateur','client') NOT NULL DEFAULT 'client',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `email`, `phone`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Mouhamed', 'samb', 'Sambmouhamed593@gmail.com', '781155609', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2025-08-01 21:09:21', '2025-09-01 15:36:49'),
(2, 'resto1', NULL, NULL, 'resto1@example.com', NULL, '$2y$10$examplehash', 'restaurateur', 1, '2025-08-01 21:09:21', '2025-08-01 21:09:21'),
(3, 'client1', NULL, NULL, 'client1@example.com', NULL, '$2y$10$examplehash', 'client', 1, '2025-08-01 21:09:21', '2025-08-01 21:09:21'),
(4, 'madiba', NULL, NULL, 'madiba@restaurant.com', NULL, '$2y$10$aeJyxuawbUqQBYCN4bNhKuPAwv04lDmzj6hygZ/7/OU9HzBtT5i.e', 'restaurateur', 1, '2025-08-16 23:40:18', '2025-08-16 23:40:18'),
(19, 'croissantmagique', NULL, NULL, 'croissantmagique@restaurant.com', NULL, '$2y$10$eqGIMhWEAMxArZr38TbcI.5wHGNfpX3cuh.rhhWKtEdlIylpY8g7a', 'restaurateur', 1, '2025-08-17 16:45:56', '2025-08-17 16:45:56'),
(245, 'palaisd&#039;or', NULL, NULL, 'palaisd&#039;or@restaurant.com', NULL, '$2y$10$FFDh0NmOWK2q/.KePmFyD.br/RR/y5jv1mC2f5mrkXB3Rph6Q/9BO', 'restaurateur', 1, '2025-08-23 22:08:43', '2025-08-23 22:08:43');

-- --------------------------------------------------------

--
-- Structure de la table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `building` varchar(100) DEFAULT NULL,
  `apartment` varchar(100) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address`, `building`, `apartment`, `city`, `delivery_instructions`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 1, 'Thiés/mbour1', '', '', 'Thiés', '', 1, '2025-08-30 02:00:49', '2025-08-30 20:40:56'),
(2, 1, 'mbour 1', '', '', 'Thiés', '', 0, '2025-08-30 15:14:19', '2025-08-30 15:14:19'),
(3, 1, 'Thiés/mbour1', '', '', 'Thiés', '', 0, '2025-08-30 15:22:41', '2025-08-30 15:22:41');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Index pour la table `notifications_log`
--
ALTER TABLE `notifications_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Index pour la table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `notifications_log`
--
ALTER TABLE `notifications_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;

--
-- AUTO_INCREMENT pour la table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications_log`
--
ALTER TABLE `notifications_log`
  ADD CONSTRAINT `notifications_log_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
