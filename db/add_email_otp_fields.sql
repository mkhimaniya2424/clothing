-- Add email OTP fields to user_verification table
ALTER TABLE `user_verification` 
ADD COLUMN `email_otp` VARCHAR(6) DEFAULT NULL AFTER `verification_token`,
ADD COLUMN `email_otp_expiry` DATETIME DEFAULT NULL AFTER `email_otp`,
ADD COLUMN `email_verified_at` DATETIME DEFAULT NULL AFTER `email_verified`;
