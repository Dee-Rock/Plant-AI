-- Database schema for Plant-AI application
-- Drop existing tables if they exist
DROP TABLE IF EXISTS `plant_comments`;
DROP TABLE IF EXISTS `plant_identifications`;
DROP TABLE IF EXISTS `user_plants`;
DROP TABLE IF EXISTS `plant_diseases`;
DROP TABLE IF EXISTS `plant_care_tips`;
DROP TABLE IF EXISTS `plants`;
DROP TABLE IF EXISTS `users`;

-- Users table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100),
    `profile_image` VARCHAR(255),
    `bio` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plants table
CREATE TABLE `plants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `common_name` VARCHAR(100) NOT NULL,
    `scientific_name` VARCHAR(100) NOT NULL,
    `family` VARCHAR(50),
    `description` TEXT,
    `care_level` ENUM('easy', 'moderate', 'difficult') DEFAULT 'moderate',
    `water_needs` VARCHAR(50),
    `light_requirements` VARCHAR(100),
    `mature_size` VARCHAR(50),
    `image_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User's personal plant collection
CREATE TABLE `user_plants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `plant_id` INT NOT NULL,
    `nickname` VARCHAR(50),
    `purchase_date` DATE,
    `last_watered` DATETIME,
    `notes` TEXT,
    `health_status` ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plant_id`) REFERENCES `plants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Disease information
CREATE TABLE `plant_diseases` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `scientific_name` VARCHAR(100),
    `description` TEXT,
    `causes` TEXT,
    `prevention` TEXT,
    `treatment` TEXT,
    `common_plants` VARCHAR(255),
    `image_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plant identification history
CREATE TABLE `plant_identifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `plant_id` INT,
    `disease_id` INT,
    `image_path` VARCHAR(255) NOT NULL,
    `confidence` DECIMAL(5,2),
    `notes` TEXT,
    `identified_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`plant_id`) REFERENCES `plants`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`disease_id`) REFERENCES `plant_diseases`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Care tips and reminders
CREATE TABLE `plant_care_tips` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plant_id` INT NOT NULL,
    `tip_type` ENUM('watering', 'fertilizing', 'pruning', 'repotting', 'general') NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `frequency_days` INT,
    `season` ENUM('spring', 'summer', 'fall', 'winter', 'all'),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`plant_id`) REFERENCES `plants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments on plant identifications
CREATE TABLE `plant_comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `identification_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `comment` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`identification_id`) REFERENCES `plant_identifications`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data
INSERT INTO `users` (`username`, `email`, `password_hash`) VALUES
('plantlover', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Insert some common plants
INSERT INTO `plants` (`common_name`, `scientific_name`, `family`, `description`, `care_level`, `water_needs`, `light_requirements`) VALUES
('Snake Plant', 'Sansevieria trifasciata', 'Asparagaceae', 'A hardy, low-maintenance plant with tall, stiff leaves.', 'easy', 'Low', 'Low to bright indirect light'),
('Monstera', 'Monstera deliciosa', 'Araceae', 'Tropical plant with large, split leaves.', 'moderate', 'Moderate', 'Bright indirect light'),
('ZZ Plant', 'Zamioculcas zamiifolia', 'Araceae', 'A tough plant with glossy, dark green leaves.', 'easy', 'Low', 'Low to bright indirect light');

-- Insert some common plant diseases
INSERT INTO `plant_diseases` (`name`, `scientific_name`, `description`, `causes`, `prevention`, `treatment`) VALUES
('Root Rot', 'Phytophthora spp.', 'A disease that causes roots to decay, often due to overwatering.', 'Overwatering, poor drainage, fungal pathogens', 'Allow soil to dry between waterings, use well-draining soil', 'Remove affected roots, repot in fresh soil, reduce watering'),
('Powdery Mildew', 'Erysiphales', 'White powdery spots on leaves and stems.', 'High humidity, poor air circulation', 'Ensure good air flow, avoid wetting foliage', 'Apply fungicide, remove affected leaves, improve conditions'),
('Spider Mites', 'Tetranychus urticae', 'Tiny pests that cause stippling on leaves.', 'Dry conditions, dust on leaves', 'Mist plants, clean leaves regularly', 'Use insecticidal soap, increase humidity, isolate plant');

-- Insert some care tips
INSERT INTO `plant_care_tips` (`plant_id`, `tip_type`, `title`, `description`, `frequency_days`, `season`) VALUES
(1, 'watering', 'Watering Snake Plant', 'Water only when soil is completely dry, about every 2-3 weeks. Less in winter.', 14, 'all'),
(2, 'fertilizing', 'Monstera Fertilizing', 'Fertilize monthly during growing season with balanced houseplant fertilizer.', 30, 'summer'),
(3, 'general', 'ZZ Plant Care', 'ZZ plants are drought tolerant. Water when soil is completely dry, about every 3-4 weeks.', 21, 'all');
