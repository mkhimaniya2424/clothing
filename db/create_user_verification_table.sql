-- =============================================
-- Create user_verification table for password reset functionality
-- =============================================

CREATE TABLE IF NOT EXISTS `user_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `reset_token` (`reset_token`),
  KEY `verification_token` (`verification_token`),
  CONSTRAINT `user_verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Insert verification records for existing users
-- =============================================

INSERT INTO `user_verification` (`user_id`, `email_verified`)
SELECT `id`, 1 FROM `users`
WHERE `id` NOT IN (SELECT `user_id` FROM `user_verification`)
ON DUPLICATE KEY UPDATE `user_id` = `user_id`;
