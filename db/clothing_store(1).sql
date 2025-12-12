-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2025 at 07:56 AM
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
-- Database: `clothing_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `full_name`, `email`, `mobile`, `profile_pic`, `created_at`) VALUES
(1, 'admin', '*01A6717B58FF5C7EAFFF6CB7C96F7428EA65FE4C', 'Administrator', 'admin@gmail.com', '6438729156', NULL, '2025-12-01 10:57:09'),
(2, 'mkahir', '*D60F7A384C2A142617C9BE10EC1E4A125E569D77', 'demo', 'meghanaahir1@gmail.com', '9099112070', 'admin_2_1765260690.jpg', '2025-12-01 10:57:47'),
(3, 'sakshi', '*42C6B814A52F1AED6CC6B9A7FED6CB35853D2DED', 'sakshi vyas', NULL, NULL, NULL, '2025-12-01 10:58:17');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `logo` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `logo`, `created_at`) VALUES
(1, 'Adidas', 'uploads/brands/logo_692eeec2adc2d.jpg', '2025-12-02 19:20:58'),
(2, 'gucci', 'uploads/brands/logo_692ef06c5f5cc.jpg', '2025-12-02 19:28:04'),
(3, 'burberry', 'uploads/brands/logo_692ef0788bf00.jpg', '2025-12-02 19:28:16'),
(4, 'lacoste', 'uploads/brands/logo_692ef094155b2.jpg', '2025-12-02 19:28:44');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(9, 1, 35, 1, '2025-12-10 05:06:18', '2025-12-10 05:06:18');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`, `created_at`, `status`) VALUES
(1, 'Men', NULL, '2025-12-02 00:43:21', 1),
(2, 'Women', NULL, '2025-12-02 00:43:21', 1),
(3, 'Children', NULL, '2025-12-02 00:43:21', 1),
(6, 'tshirt', 1, '2025-12-02 11:20:16', 1),
(7, 'shirts', 1, '2025-12-02 15:06:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','read','replied') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'meghana', 'meghanaahir1@gmail.com', '8320552427', 'for know more', 'bjknlkm;m', 'replied', '2025-12-10 16:46:24');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `title`, `description`, `discount_percentage`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'seasonal offer', 'offer', 20.00, '2025-12-02', '2025-12-05', 'active', '2025-12-02 19:47:47', '2025-12-09 09:48:23');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `address_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `order_status` enum('pending','confirmed','packed','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `shipping_address`, `address_id`, `total_amount`, `discount_amount`, `final_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`, `updated_at`) VALUES
(2, 1, 'vajdi, rajkot, gujarat - 360005, india', 1, 1000.00, 0.00, 1000.00, 'cod', 'pending', 'pending', '2025-12-03 04:11:04', '2025-12-03 04:11:04'),
(3, 1, 'vajdi, rajkot, gujarat - 360005, india', 1, 1000.00, 0.00, 1000.00, 'upi', 'pending', 'cancelled', '2025-12-03 04:31:35', '2025-12-03 04:31:49'),
(4, 1, 'vajdi, rajkot, gujarat - 360005, india', 1, 1000.00, 0.00, 1000.00, 'upi', 'pending', 'delivered', '2025-12-03 04:47:11', '2025-12-10 14:24:42'),
(5, 1, 'vajdi, rajkot, gujarat - 360005, india', 1, 1000.00, 200.00, 800.00, 'upi', 'pending', 'delivered', '2025-12-03 04:53:47', '2025-12-03 05:31:25'),
(6, 1, 'vajdi, rajkot, gujarat - 360005, india', 1, 1000.00, 200.00, 800.00, 'cod', 'pending', 'packed', '2025-12-03 05:16:40', '2025-12-06 16:32:13'),
(7, 1, 'vajdi, rajkot, gujarat - 360005, india', 1, 2198.00, 0.00, 2198.00, 'upi', 'pending', 'confirmed', '2025-12-09 06:07:46', '2025-12-10 14:24:53');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `total`, `created_at`) VALUES
(1, 2, 1, 1, 1000.00, 1000.00, '2025-12-03 04:11:04'),
(2, 3, 1, 1, 1000.00, 1000.00, '2025-12-03 04:31:35'),
(3, 4, 1, 1, 1000.00, 1000.00, '2025-12-03 04:47:11'),
(4, 5, 1, 1, 1000.00, 1000.00, '2025-12-03 04:53:47'),
(5, 6, 1, 1, 1000.00, 1000.00, '2025-12-03 05:16:40'),
(6, 7, 1, 1, 1000.00, 1000.00, '2025-12-09 06:07:46'),
(7, 7, 2, 2, 599.00, 1198.00, '2025-12-09 06:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cod','card','upi','netbanking','wallet') NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','success','failed') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_details`
--

INSERT INTO `payment_details` (`id`, `order_id`, `payment_method`, `transaction_id`, `amount`, `payment_status`, `payment_date`) VALUES
(1, 2, 'cod', NULL, 1000.00, 'pending', '2025-12-03 04:11:04'),
(2, 3, 'upi', NULL, 1000.00, 'pending', '2025-12-03 04:31:35'),
(3, 4, 'upi', NULL, 1000.00, 'pending', '2025-12-03 04:47:11'),
(4, 5, 'upi', NULL, 800.00, 'pending', '2025-12-03 04:53:47'),
(5, 6, 'cod', NULL, 800.00, 'pending', '2025-12-03 05:16:40'),
(6, 7, 'upi', NULL, 2198.00, 'pending', '2025-12-09 06:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category_main` varchar(255) DEFAULT NULL,
  `category_sub` varchar(255) DEFAULT NULL,
  `category_type` varchar(255) DEFAULT NULL,
  `category_brand` varchar(255) DEFAULT NULL,
  `sizes` text DEFAULT NULL,
  `fabric` varchar(255) DEFAULT NULL,
  `highlight` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `price`, `category_main`, `category_sub`, `category_type`, `category_brand`, `sizes`, `fabric`, `highlight`, `description`, `images`, `status`, `created_at`, `updated_at`, `stock`) VALUES
(1, 'Carbon Heavyweight T-Shirt \\u2013 Sorona', 1000.00, '1', 'top wear', 'T-Shirts', 'Adidas', 's,m,l,xl,2xl,3xl,4xl,5xl', 'cotton blend', '\"sorona cotton  blend,4 waystretch,durable\\r\\nProduct Features\\r\\n\\r\\nBuilt to Last: Durable fabric with a premium feel\\r\\n\\r\\n\\r\\nPre Shrunk: Retains shape post wash\\r\\n\\r\\n\\r\\nAnti-Fade: Strong color fastness\"', '\"Sustainable Comfort & Performance: Made from a carefully crafted blend of Sorona\\u2122 (corn-based fiber) and organic cotton, this heavyweight tee offers exceptional softness, breathability, and natural hypoallergenic and antibacterial properties\\r\\n\\r\\nDurable Design That Lasts: With a structured 220 GSM weight and premium finishing, the Carbon Heavyweight T-Shirt retains its shape and vibrant color even after repeated washes\\r\\n\\r\\nEffortless Style & Fit: Tailored for a confident, modern fit, this elevated basic provides a clean, structured silhouette that looks sharp whether you dress it up or down, making it the perfect go-to for any occasion\"', '[\"images\\/product\\/1764666204_d218bb3a1e.jpg\",\"images\\/product\\/1764666204_aba61833b1.jpg\"]', 'active', '2025-12-02 11:09:32', '2025-12-10 18:16:17', 5),
(2, 'Classic Navy Blue T-Shirt', 599.00, '1', 'top wear', 'T-Shirts', 'Adidas', 's,m,l,xl,2xl', 'cotton', 'Breathable fabric, Pre-shrunk, Anti-fade', 'Premium quality navy blue t-shirt perfect for casual wear. Made from 100% cotton for maximum comfort.', '[\"images\\/product\\/1764858224_b1b5281155.jpg\",\"images\\/product\\/1764858224_17e105d460.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 50),
(3, 'Black Graphic T-Shirt', 799.00, '1', 'top wear', 'T-Shirts', 'gucci', 's,m,l,xl,2xl,3xl', 'cotton blend', 'Unique graphic design, Soft fabric, Durable print', 'Trendy black graphic t-shirt with modern design. Perfect for streetwear style.', '[\"images\\/product\\/1765250961_6784804306.jpg\",\"images\\/product\\/1765250961_976c34adb2.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 45),
(4, 'White Basic T-Shirt', 499.00, '1', 'top wear', 'T-Shirts', 'Adidas', 's,m,l,xl,2xl,3xl,4xl', 'cotton', 'Essential basic, Versatile, Easy care', 'Classic white t-shirt - a wardrobe essential. Perfect for layering or wearing solo.', '[\"uploads\\/products\\/1765251503_29b55b85e1.jpg\",\"uploads\\/products\\/1765251503_cfcfb90697.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 60),
(6, 'White Formal Shirt', 1199.00, '1', 'top wear', 'Shirts', 'burberry', 's,m,l,xl,2xl,3xl', 'cotton', 'Classic white, Versatile, Premium quality', 'Crisp white formal shirt perfect for business meetings and formal occasions.', '[\"https://via.placeholder.com/400x500/FFFFFF/000000?text=White+Formal\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 40),
(7, 'Checkered Casual Shirt', 999.00, '1', 'top wear', 'Shirts', 'lacoste', 's,m,l,xl,2xl', 'cotton blend', 'Trendy pattern, Casual style, Breathable', 'Stylish checkered casual shirt for weekend outings and casual Fridays.', '[\"https://via.placeholder.com/400x500/FF6347/FFFFFF?text=Checkered\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 30),
(8, 'Red Polo Shirt', 899.00, '1', 'top wear', 'Polo', 'lacoste', 's,m,l,xl,2xl', 'pique cotton', 'Classic polo style, Collar design, Sporty look', 'Classic red polo shirt with signature collar. Perfect for smart casual occasions.', '[\"https://via.placeholder.com/400x500/DC143C/FFFFFF?text=Red+Polo\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 25),
(9, 'Grey Hoodie Sweatshirt', 1599.00, '1', 'top wear', 'Hoodies', 'Adidas', 's,m,l,xl,2xl,3xl', 'fleece', 'Warm and cozy, Kangaroo pocket, Adjustable hood', 'Comfortable grey hoodie perfect for cold weather. Features drawstring hood and front pocket.', '[\"https://via.placeholder.com/400x500/808080/FFFFFF?text=Grey+Hoodie\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 40),
(10, 'Black Pullover Sweatshirt', 1399.00, '1', 'top wear', 'Sweatshirts', 'Adidas', 's,m,l,xl,2xl', 'cotton fleece', 'Soft interior, Ribbed cuffs, Comfortable fit', 'Classic black pullover sweatshirt for everyday comfort and style.', '[\"https://via.placeholder.com/400x500/000000/FFFFFF?text=Black+Pullover\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 35),
(11, 'Blue Denim Jeans', 1899.00, '1', 'bottom wear', 'Jeans', 'gucci', '28,30,32,34,36,38', 'denim', 'Classic fit, Durable denim, 5-pocket design', 'Premium blue denim jeans with classic fit. Perfect for casual and semi-formal occasions.', '[\"https://via.placeholder.com/400x500/4169E1/FFFFFF?text=Blue+Jeans\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 50),
(12, 'Beige Chino Pants', 1699.00, '1', 'bottom wear', 'Chinos', 'burberry', '28,30,32,34,36,38', 'cotton twill', 'Smart casual, Versatile color, Comfortable fit', 'Elegant beige chino pants suitable for both office and casual wear.', '[\"https://via.placeholder.com/400x500/F5F5DC/000000?text=Beige+Chinos\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 45),
(13, 'Khaki Casual Shorts', 899.00, '1', 'bottom wear', 'Shorts', 'lacoste', 's,m,l,xl,2xl', 'cotton', 'Summer essential, Multiple pockets, Comfortable', 'Comfortable khaki shorts perfect for summer days and casual outings.', '[\"https://via.placeholder.com/400x500/C3B091/000000?text=Khaki+Shorts\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 55),
(14, 'Black Leather Jacket', 4999.00, '1', 'outerwear', 'Jackets', 'gucci', 's,m,l,xl,2xl', 'genuine leather', 'Premium leather, Stylish design, Warm lining', 'Luxurious black leather jacket for a bold and sophisticated look.', '[\"https://via.placeholder.com/400x500/000000/FFFFFF?text=Leather+Jacket\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 20),
(15, 'Navy Blue Blazer', 3499.00, '1', 'outerwear', 'Blazers', 'burberry', 's,m,l,xl,2xl', 'wool blend', 'Formal elegance, Tailored fit, Professional', 'Sharp navy blue blazer perfect for business meetings and formal events.', '[\"https://via.placeholder.com/400x500/000080/FFFFFF?text=Navy+Blazer\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 25),
(16, 'Burgundy Knit Sweater', 1799.00, '1', 'top wear', 'Sweaters', 'lacoste', 's,m,l,xl,2xl', 'wool blend', 'Warm knit, Stylish color, Comfortable', 'Cozy burgundy sweater perfect for layering in cold weather.', '[\"https://via.placeholder.com/400x500/800020/FFFFFF?text=Burgundy+Sweater\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 30),
(17, 'Floral Summer Dress', 1999.00, '2', 'dresses', 'Summer Dress', 'gucci', 's,m,l,xl', 'cotton', 'Floral print, Lightweight, Breathable', 'Beautiful floral summer dress perfect for warm weather and outdoor events.', '[\"https://via.placeholder.com/400x500/FFB6C1/000000?text=Floral+Dress\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 40),
(18, 'Red Evening Dress', 3499.00, '2', 'dresses', 'Evening Dress', 'gucci', 's,m,l,xl', 'silk blend', 'Elegant design, Luxurious fabric, Party wear', 'Stunning red evening dress for special occasions and formal events.', '[\"https://via.placeholder.com/400x500/DC143C/FFFFFF?text=Red+Evening\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 25),
(19, 'Black Cocktail Dress', 2799.00, '2', 'dresses', 'Cocktail Dress', 'burberry', 's,m,l,xl', 'polyester blend', 'Sophisticated, Versatile, Flattering fit', 'Chic black cocktail dress suitable for parties and semi-formal events.', '[\"https://via.placeholder.com/400x500/000000/FFFFFF?text=Black+Cocktail\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 30),
(20, 'White Cotton Blouse', 1299.00, '2', 'top wear', 'Blouses', 'burberry', 's,m,l,xl,2xl', 'cotton', 'Professional look, Breathable, Easy care', 'Classic white cotton blouse perfect for office wear and formal occasions.', '[\"https://via.placeholder.com/400x500/FFFFFF/000000?text=White+Blouse\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 45),
(21, 'Silk Floral Top', 1599.00, '2', 'top wear', 'Tops', 'gucci', 's,m,l,xl', 'silk', 'Luxurious silk, Floral pattern, Elegant', 'Elegant silk top with beautiful floral print for a sophisticated look.', '[\"https://via.placeholder.com/400x500/FFC0CB/000000?text=Silk+Floral\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 35),
(26, 'Beige Palazzo Pants', 1499.00, '2', 'bottom wear', 'Pants', 'burberry', 's,m,l,xl', 'cotton blend', 'Flowy design, Comfortable, Elegant', 'Comfortable beige palazzo pants perfect for both casual and formal wear.', '[\"https://via.placeholder.com/400x500/F5F5DC/000000?text=Palazzo+Pants\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 45),
(27, 'Floral Midi Skirt', 1399.00, '2', 'bottom wear', 'Skirts', 'gucci', 's,m,l,xl', 'cotton', 'Floral print, Midi length, Feminine', 'Beautiful floral midi skirt perfect for spring and summer.', '[\"https://via.placeholder.com/400x500/FFB6C1/000000?text=Floral+Skirt\"]', 'disabled', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 40),
(30, 'Cream Cardigan', 1599.00, '2', 'top wear', 'Cardigans', 'lacoste', 's,m,l,xl', 'wool blend', 'Soft knit, Cozy, Versatile layering', 'Comfortable cream cardigan perfect for layering over any outfit.', '[\"uploads\\/products\\/1765252768_ac6e084014.jpg\",\"uploads\\/products\\/1765252768_6c62dd766c.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 35),
(35, 'Kids Denim Jeans', 899.00, '3', 'bottom wear', 'Jeans', 'gucci', '2-3y,4-5y,6-7y,8-9y,10-11y,12-13y', 'denim', 'Durable denim, Comfortable fit, Adjustable waist', 'Sturdy denim jeans perfect for active kids who love to explore.', '[\"uploads\\/products\\/1765252737_92609bac4e.jpg\",\"uploads\\/products\\/1765252737_ddfb19ddfb.jpg\",\"uploads\\/products\\/1765252737_584de049f0.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 50),
(36, 'Kids Cargo Shorts', 599.00, '3', 'bottom wear', 'Shorts', 'lacoste', '2-3y,4-5y,6-7y,8-9y,10-11y', 'cotton', 'Multiple pockets, Comfortable, Durable', 'Practical cargo shorts with plenty of pockets for kids adventures.', '[\"uploads\\/products\\/1765252671_e81fb899e3.jpg\",\"uploads\\/products\\/1765252671_cf541c2f13.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 65),
(37, 'Kids Hoodie Sweatshirt', 999.00, '3', 'top wear', 'Hoodies', 'Adidas', '2-3y,4-5y,6-7y,8-9y,10-11y', 'fleece', 'Warm and cozy, Fun colors, Soft interior', 'Cozy hoodie to keep kids warm during cold weather and outdoor activities.', '[\"uploads\\/products\\/1765252595_eb8e009984.jpg\",\"uploads\\/products\\/1765252595_a389a10657.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 45),
(39, 'Girls Pink Dress', 1199.00, '3', 'dresses', 'Casual Dress', 'gucci', '2-3y,4-5y,6-7y,8-9y,10-11y', 'cotton', 'Pretty design, Comfortable, Easy care', 'Adorable pink dress perfect for parties and special occasions.', '[\"uploads\\/products\\/1765252438_11c39c7bf6.jpg\",\"uploads\\/products\\/1765252438_8fe523106e.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 50),
(40, 'Girls Floral Dress', 1299.00, '3', 'dresses', 'Party Dress', 'gucci', '2-3y,4-5y,6-7y,8-9y,10-11y', 'cotton blend', 'Floral print, Elegant, Comfortable', 'Beautiful floral dress for birthday parties and celebrations.', '[\"uploads\\/products\\/1765252381_d9710e29c1.jpg\",\"uploads\\/products\\/1765252381_3927ca7393.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 45),
(42, 'Kids Polo Shirt', 699.00, '3', 'top wear', 'Polo', 'lacoste', '2-3y,4-5y,6-7y,8-9y,10-11y', 'pique cotton', 'Smart casual, Collar design, Comfortable', 'Classic polo shirt perfect for school and semi-formal occasions.', '[\"uploads\\/products\\/1765252319_7923dd2a9b.jpg\",\"uploads\\/products\\/1765252319_97a4918c09.jpg\",\"uploads\\/products\\/1765252319_142d75250b.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 20:05:53', 20),
(43, 'Kids Sweatpants', 799.00, '3', 'bottom wear', 'Pants', 'Adidas', '2-3y,4-5y,6-7y,8-9y,10-11y', 'cotton fleece', 'Comfortable, Elastic waist, Soft fabric', 'Comfortable sweatpants perfect for lounging and casual wear.', '[\"uploads\\/products\\/1765252163_1163ef910f.jpg\",\"uploads\\/products\\/1765252163_c42a176aec.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 55),
(44, 'Girls Leggings', 499.00, '3', 'bottom wear', 'Leggings', 'lacoste', '2-3y,4-5y,6-7y,8-9y,10-11y', 'cotton spandex', 'Stretchy, Comfortable, Versatile', 'Comfortable leggings perfect for active play and everyday wear.', '[\"uploads\\/products\\/1765252112_bbfab9f871.jpg\",\"uploads\\/products\\/1765252112_7d9da8bcea.webp\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 60),
(45, 'Kids Denim Jacket', 1499.00, '3', 'outerwear', 'Jackets', 'gucci', '2-3y,4-5y,6-7y,8-9y,10-11y', 'denim', 'Classic style, Durable, Versatile', 'Trendy denim jacket that pairs well with any outfit.', '[\"uploads\\/products\\/1765252017_f836d4f2cc.jpg\",\"uploads\\/products\\/1765252017_1bb753877f.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 40),
(46, 'Kids Raincoat', 899.00, '3', 'outerwear', 'Raincoats', 'Adidas', '2-3y,4-5y,6-7y,8-9y,10-11y', 'waterproof polyester', 'Waterproof, Bright colors, Hood', 'Bright and waterproof raincoat to keep kids dry during rainy days.', '[\"uploads\\/products\\/1765251954_82f89e664d.jpg\",\"uploads\\/products\\/1765251954_574751681e.jpg\"]', 'active', '2025-12-04 19:44:16', '2025-12-10 18:16:17', 45);

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `discount_percentage` decimal(10,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `description`, `code`, `discount_percentage`, `discount_amount`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'code', 'add code to get discount', 'MK123', 10.00, 0.00, '2025-12-02', '2025-12-05', 'active', '2025-12-02 14:18:26', '2025-12-09 04:16:16');

-- --------------------------------------------------------

--
-- Table structure for table `rating_reviews`
--

CREATE TABLE `rating_reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rating_reviews`
--

INSERT INTO `rating_reviews` (`id`, `user_id`, `product_id`, `rating`, `review`, `created_at`) VALUES
(1, 1, 1, 4, 'nice', '2025-12-03 05:32:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `phone`, `profile_pic`, `gender`, `status`, `created_at`, `updated_at`) VALUES
(1, 'megha', 'meghanaahir1@gmail.com', '$2y$10$.Z0rwXH0yzD/AndTzNTLge90jRjE3.DepDVknXF/uCY8JgAjNtJli', '9099112071', NULL, 'female', 'active', '2025-12-02 12:13:19', '2025-12-10 14:14:37');

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Unknown',
  `address_type` enum('home','office','other') DEFAULT 'home',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`address_id`, `user_id`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `address_type`, `created_at`) VALUES
(1, 1, 'vajdi', '', 'rajkot', 'gujarat', '360005', 'india', 'home', '2025-12-02 12:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `user_verification`
--

CREATE TABLE `user_verification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `email_otp` varchar(6) DEFAULT NULL,
  `email_otp_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_verification`
--

INSERT INTO `user_verification` (`id`, `user_id`, `reset_token`, `reset_token_expiry`, `email_verified`, `email_verified_at`, `verification_token`, `email_otp`, `email_otp_expiry`, `created_at`, `updated_at`) VALUES
(1, 1, '86359bbf78084fee2f373915d1d4a99994307cf60ccecd3a7dd55e0ae3d588a9', '2025-12-09 10:51:01', 1, NULL, NULL, NULL, NULL, '2025-12-03 04:50:56', '2025-12-09 04:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(6, 1, 1, '2025-12-03 04:11:30'),
(7, 1, 44, '2025-12-09 04:22:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `rating_reviews`
--
ALTER TABLE `rating_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_verification`
--
ALTER TABLE `user_verification`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `reset_token` (`reset_token`),
  ADD KEY `verification_token` (`verification_token`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rating_reviews`
--
ALTER TABLE `rating_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_verification`
--
ALTER TABLE `user_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `user_address` (`address_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `payment_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `rating_reviews`
--
ALTER TABLE `rating_reviews`
  ADD CONSTRAINT `rating_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rating_reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_verification`
--
ALTER TABLE `user_verification`
  ADD CONSTRAINT `user_verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
