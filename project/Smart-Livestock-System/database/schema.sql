-- ============================================
-- SMART LIVESTOCK MANAGEMENT SYSTEM
-- Database Schema
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS livestock_db;
USE livestock_db;

-- ============================================
-- TABLE 1: USERS (Authentication & Authorization)
-- Purpose: Store farmer/user credentials and info
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address VARCHAR(255),
    farm_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 2: ANIMALS (Livestock Details)
-- Purpose: Store information about each animal
-- Foreign Key: Links to users table
-- ============================================
CREATE TABLE IF NOT EXISTS animals (
    animal_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    animal_name VARCHAR(100) NOT NULL,
    animal_type ENUM('cow', 'buffalo', 'goat', 'sheep') NOT NULL,
    breed VARCHAR(100),
    age INT,
    weight DECIMAL(8, 2),
    milk_yield_daily DECIMAL(8, 2) COMMENT 'Daily milk production in liters',
    health_status ENUM('healthy', 'sick', 'recovering', 'under_observation') DEFAULT 'healthy',
    last_health_check DATE,
    vaccination_status VARCHAR(255),
    feed_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_animal_type (animal_type),
    INDEX idx_health_status (health_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 3: SUPPLEMENTS (Nutritional Products)
-- Purpose: Store available supplements database
-- Note: Used in Greedy Algorithm for optimization
-- ============================================
CREATE TABLE IF NOT EXISTS supplements (
    supplement_id INT PRIMARY KEY AUTO_INCREMENT,
    supplement_name VARCHAR(100) NOT NULL UNIQUE,
    protein_content DECIMAL(5, 2) COMMENT 'Percentage of protein',
    fat_content DECIMAL(5, 2),
    minerals DECIMAL(5, 2),
    cost_per_kg DECIMAL(8, 2) NOT NULL,
    description TEXT,
    animal_type ENUM('cow', 'buffalo', 'goat', 'sheep', 'all') DEFAULT 'all',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_animal_type (animal_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 4: RECOMMENDATIONS (AI Supplement Suggestions)
-- Purpose: Store recommendation history
-- Note: Generated using Greedy Algorithm
-- ============================================
CREATE TABLE IF NOT EXISTS recommendations (
    recommendation_id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT NOT NULL,
    user_id INT NOT NULL,
    budget DECIMAL(10, 2),
    recommended_supplements JSON COMMENT 'Array of supplement recommendations',
    total_nutrition_score DECIMAL(5, 2),
    total_cost DECIMAL(10, 2),
    recommendation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(animal_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_animal_id (animal_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 5: UPLOADS (File Management)
-- Purpose: Track file uploads for each user/animal
-- Note: Demonstrates OS File Management concepts
-- ============================================
CREATE TABLE IF NOT EXISTS uploads (
    upload_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    animal_id INT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT COMMENT 'Size in bytes',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    status ENUM('active', 'deleted') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animals(animal_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_animal_id (animal_id),
    INDEX idx_upload_date (upload_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 6: MILK_PRODUCTION (Tracking & Analytics)
-- Purpose: Track daily milk production for analytics
-- Note: Used in Dynamic Programming for prediction
-- ============================================
CREATE TABLE IF NOT EXISTS milk_production (
    production_id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT NOT NULL,
    user_id INT NOT NULL,
    production_date DATE NOT NULL,
    quantity_liters DECIMAL(8, 2) NOT NULL,
    quality_score INT COMMENT 'Score 1-10 for milk quality',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(animal_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_animal_id (animal_id),
    INDEX idx_production_date (production_date),
    UNIQUE KEY unique_daily_record (animal_id, production_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 7: FARMS_NETWORK (Graph Structure for BFS)
-- Purpose: Store farm network for disease spread simulation
-- Note: Used in Graph/BFS Algorithm
-- ============================================
CREATE TABLE IF NOT EXISTS farms_network (
    farm_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    farm_name VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 8: FARM_CONNECTIONS (Graph Edges for BFS)
-- Purpose: Define connections between farms
-- ============================================
CREATE TABLE IF NOT EXISTS farm_connections (
    connection_id INT PRIMARY KEY AUTO_INCREMENT,
    farm1_id INT NOT NULL,
    farm2_id INT NOT NULL,
    distance_km DECIMAL(8, 2),
    FOREIGN KEY (farm1_id) REFERENCES farms_network(farm_id) ON DELETE CASCADE,
    FOREIGN KEY (farm2_id) REFERENCES farms_network(farm_id) ON DELETE CASCADE,
    UNIQUE KEY unique_connection (farm1_id, farm2_id),
    INDEX idx_farm1_id (farm1_id),
    INDEX idx_farm2_id (farm2_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 9: PROCESS_LOG (OS Process Management Simulation)
-- Purpose: Log simulated OS processes
-- ============================================
CREATE TABLE IF NOT EXISTS process_log (
    process_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    request_type VARCHAR(100),
    process_state ENUM('ready', 'running', 'waiting', 'completed', 'blocked') DEFAULT 'ready',
    priority INT DEFAULT 0,
    arrival_time TIMESTAMP,
    start_time TIMESTAMP NULL,
    completion_time TIMESTAMP NULL,
    burst_time INT COMMENT 'CPU burst time in ms',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 10: RESOURCE_LOCKS (Deadlock Simulation)
-- Purpose: Track resource locks for deadlock avoidance
-- ============================================
CREATE TABLE IF NOT EXISTS resource_locks (
    lock_id INT PRIMARY KEY AUTO_INCREMENT,
    resource_name VARCHAR(100) NOT NULL,
    locked_by_process_id INT,
    lock_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lock_status ENUM('locked', 'released', 'waiting') DEFAULT 'locked',
    FOREIGN KEY (locked_by_process_id) REFERENCES process_log(process_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INDEXES FOR OPTIMIZATION (All indexes are now inline with table definitions)
-- ============================================

-- ============================================
-- VIEW: ANIMAL_SUMMARY (For Quick Dashboard)
-- ============================================
CREATE VIEW IF NOT EXISTS animal_summary AS
SELECT 
    a.animal_id,
    a.animal_name,
    a.animal_type,
    a.age,
    a.weight,
    a.milk_yield_daily,
    a.health_status,
    u.username,
    u.farm_name,
    COUNT(DISTINCT m.production_id) as total_records,
    AVG(m.quantity_liters) as avg_milk_production
FROM animals a
LEFT JOIN users u ON a.user_id = u.user_id
LEFT JOIN milk_production m ON a.animal_id = m.animal_id
GROUP BY a.animal_id;

-- ============================================
-- TRIGGERS
-- ============================================

-- Auto-update user's updated_at timestamp
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_users_timestamp
BEFORE UPDATE ON users
FOR EACH ROW
SET NEW.updated_at = CURRENT_TIMESTAMP;
//

-- Auto-update animals' updated_at timestamp
CREATE TRIGGER IF NOT EXISTS update_animals_timestamp
BEFORE UPDATE ON animals
FOR EACH ROW
SET NEW.updated_at = CURRENT_TIMESTAMP;
//
DELIMITER ;

-- ============================================
-- SAMPLE DATA INSERTION (PRESENTATION-FRIENDLY)
-- ============================================

-- USERS: 3 farmers with simple names
INSERT INTO users (username, email, password_hash, full_name, phone, address, farm_name, status) VALUES
('farmer_john', 'john@farm.com', '$2y$12$B3Y8kHpFHzvOj72Hlwqhjui8CdjkEBFfI3bh5ThywJx4ZyEwgg/I2', 'John Smith', '9876543210', 'Green Valley Farm, California', 'Green Valley Dairy', 'active'),
('farmer_sarah', 'sarah@farm.com', '$2y$12$fhfBEH7Jru5ciui93T8KR.YasGH0VJvilX8PkPuUKgNtz2mFeo2ti', 'Sarah Johnson', '9876543211', 'Sunny Meadows Farm, Texas', 'Sunny Meadows Ranch', 'active'),
('farmer_mike', 'mike@farm.com', '$2y$12$MqGHpgLI9WrWjDJBGtq2ge5.bJQ1dpgY4FmzVwXbGs8w7lvaukfCS', 'Mike Wilson', '9876543212', 'Blue Ridge Farm, Colorado', 'Blue Ridge Cattle', 'active');

-- ANIMALS: 10 animals with IDs 1-10 (perfect for binary search demo)
INSERT INTO animals (animal_id, user_id, animal_name, animal_type, breed, age, weight, milk_yield_daily, health_status, last_health_check, feed_type) VALUES
(1, 1, 'Bella', 'cow', 'Holstein', 4, 500.00, 20.0, 'healthy', '2026-04-01', 'grass'),
(2, 1, 'Daisy', 'cow', 'Jersey', 3, 400.00, 15.0, 'healthy', '2026-04-02', 'hay'),
(3, 1, 'Luna', 'buffalo', 'Murrah', 5, 600.00, 25.0, 'healthy', '2026-04-03', 'mixed'),
(4, 2, 'Molly', 'cow', 'Guernsey', 2, 350.00, 12.0, 'healthy', '2026-04-04', 'silage'),
(5, 2, 'Zara', 'goat', 'Alpine', 1, 50.00, 2.0, 'healthy', '2026-04-05', 'browse'),
(6, 2, 'Nala', 'sheep', 'Merino', 2, 60.00, 1.5, 'healthy', '2026-04-06', 'pasture'),
(7, 3, 'Ruby', 'cow', 'Brown Swiss', 6, 550.00, 18.0, 'healthy', '2026-04-07', 'concentrate'),
(8, 3, 'Tina', 'buffalo', 'Jaffarabadi', 4, 650.00, 22.0, 'healthy', '2026-04-08', 'mixed'),
(9, 3, 'Penny', 'goat', 'Saanen', 1, 45.00, 1.8, 'healthy', '2026-04-09', 'hay'),
(10, 1, 'Max', 'cow', 'Ayrshire', 3, 450.00, 16.0, 'healthy', '2026-04-10', 'grass');

-- SUPPLEMENTS: Easy-to-calculate nutrition/cost ratios for Greedy algorithm
INSERT INTO supplements (supplement_name, protein_content, fat_content, minerals, cost_per_kg, animal_type, description) VALUES
('Premium Protein', 50.0, 5.0, 10.0, 25.0, 'all', 'High protein supplement (ratio: 2.0)'),
('Energy Boost', 30.0, 8.0, 5.0, 20.0, 'all', 'Energy supplement (ratio: 1.5)'),
('Basic Feed', 20.0, 3.0, 8.0, 20.0, 'all', 'Standard supplement (ratio: 1.0)'),
('Mineral Plus', 10.0, 2.0, 15.0, 12.5, 'all', 'Mineral supplement (ratio: 0.8)'),
('Dairy Special', 40.0, 6.0, 12.0, 30.0, 'cow', 'Cow-specific supplement (ratio: 1.33)'),
('Buffalo Mix', 35.0, 7.0, 9.0, 28.0, 'buffalo', 'Buffalo supplement (ratio: 1.25)');

-- MILK PRODUCTION: Clear trends for DP algorithm (30 days of data)
INSERT INTO milk_production (animal_id, user_id, production_date, quantity_liters, quality_score) VALUES
-- Bella (ID:1) - Steady increasing trend
(1, 1, '2026-03-01', 18.0, 8), (1, 1, '2026-03-02', 18.5, 8), (1, 1, '2026-03-03', 19.0, 8),
(1, 1, '2026-03-04', 19.2, 8), (1, 1, '2026-03-05', 19.5, 8), (1, 1, '2026-03-06', 19.8, 8),
(1, 1, '2026-03-07', 20.0, 8), (1, 1, '2026-03-08', 20.2, 8), (1, 1, '2026-03-09', 20.3, 8),
(1, 1, '2026-03-10', 20.5, 8), (1, 1, '2026-03-11', 20.6, 8), (1, 1, '2026-03-12', 20.8, 8),
(1, 1, '2026-03-13', 21.0, 8), (1, 1, '2026-03-14', 21.1, 8), (1, 1, '2026-03-15', 21.2, 8),
(1, 1, '2026-03-16', 21.3, 8), (1, 1, '2026-03-17', 21.4, 8), (1, 1, '2026-03-18', 21.5, 8),
(1, 1, '2026-03-19', 21.6, 8), (1, 1, '2026-03-20', 21.7, 8), (1, 1, '2026-03-21', 21.8, 8),
(1, 1, '2026-03-22', 21.9, 8), (1, 1, '2026-03-23', 22.0, 8), (1, 1, '2026-03-24', 22.1, 8),
(1, 1, '2026-03-25', 22.2, 8), (1, 1, '2026-03-26', 22.3, 8), (1, 1, '2026-03-27', 22.4, 8),
(1, 1, '2026-03-28', 22.5, 8), (1, 1, '2026-03-29', 22.6, 8), (1, 1, '2026-03-30', 22.7, 8),

-- Daisy (ID:2) - Fluctuating but overall stable
(2, 1, '2026-03-01', 14.0, 7), (2, 1, '2026-03-02', 14.5, 7), (2, 1, '2026-03-03', 13.8, 7),
(2, 1, '2026-03-04', 14.2, 7), (2, 1, '2026-03-05', 14.8, 7), (2, 1, '2026-03-06', 14.1, 7),
(2, 1, '2026-03-07', 14.5, 7), (2, 1, '2026-03-08', 14.9, 7), (2, 1, '2026-03-09', 14.3, 7),
(2, 1, '2026-03-10', 14.7, 7), (2, 1, '2026-03-11', 15.0, 7), (2, 1, '2026-03-12', 14.6, 7),
(2, 1, '2026-03-13', 14.8, 7), (2, 1, '2026-03-14', 14.4, 7), (2, 1, '2026-03-15', 14.9, 7),
(2, 1, '2026-03-16', 14.7, 7), (2, 1, '2026-03-17', 15.1, 7), (2, 1, '2026-03-18', 14.8, 7),
(2, 1, '2026-03-19', 14.6, 7), (2, 1, '2026-03-20', 15.0, 7), (2, 1, '2026-03-21', 14.9, 7),
(2, 1, '2026-03-22', 14.7, 7), (2, 1, '2026-03-23', 15.2, 7), (2, 1, '2026-03-24', 14.8, 7),
(2, 1, '2026-03-25', 14.9, 7), (2, 1, '2026-03-26', 15.1, 7), (2, 1, '2026-03-27', 14.7, 7),
(2, 1, '2026-03-28', 15.0, 7), (2, 1, '2026-03-29', 14.8, 7), (2, 1, '2026-03-30', 15.0, 7),

-- Luna (ID:3) - Decreasing trend (needs attention)
(3, 1, '2026-03-01', 26.0, 9), (3, 1, '2026-03-02', 25.8, 9), (3, 1, '2026-03-03', 25.5, 9),
(3, 1, '2026-03-04', 25.3, 9), (3, 1, '2026-03-05', 25.0, 9), (3, 1, '2026-03-06', 24.8, 9),
(3, 1, '2026-03-07', 24.5, 9), (3, 1, '2026-03-08', 24.3, 9), (3, 1, '2026-03-09', 24.0, 9),
(3, 1, '2026-03-10', 23.8, 9), (3, 1, '2026-03-11', 23.5, 9), (3, 1, '2026-03-12', 23.3, 9),
(3, 1, '2026-03-13', 23.0, 9), (3, 1, '2026-03-14', 22.8, 9), (3, 1, '2026-03-15', 22.5, 9),
(3, 1, '2026-03-16', 22.3, 9), (3, 1, '2026-03-17', 22.0, 9), (3, 1, '2026-03-18', 21.8, 9),
(3, 1, '2026-03-19', 21.5, 9), (3, 1, '2026-03-20', 21.3, 9), (3, 1, '2026-03-21', 21.0, 9),
(3, 1, '2026-03-22', 20.8, 9), (3, 1, '2026-03-23', 20.5, 9), (3, 1, '2026-03-24', 20.3, 9),
(3, 1, '2026-03-25', 20.0, 9), (3, 1, '2026-03-26', 19.8, 9), (3, 1, '2026-03-27', 19.5, 9),
(3, 1, '2026-03-28', 19.3, 9), (3, 1, '2026-03-29', 19.0, 9), (3, 1, '2026-03-30', 18.8, 9);

-- RECOMMENDATIONS: Sample algorithm results
INSERT INTO recommendations (animal_id, user_id, budget, recommended_supplements, total_nutrition_score, total_cost, recommendation_reason, created_at) VALUES
(1, 1, 100.00, '[{"name": "Premium Protein", "quantity": 2, "cost": 50}, {"name": "Energy Boost", "quantity": 1, "cost": 20}]', 85.00, 70.00, 'Greedy algorithm optimization for maximum nutrition within budget', '2026-04-01 10:00:00'),
(2, 1, 50.00, '[{"name": "Basic Feed", "quantity": 2, "cost": 40}]', 60.00, 40.00, 'Cost-effective supplement selection', '2026-04-02 11:00:00');

-- UPLOADS: Sample file uploads
INSERT INTO uploads (user_id, animal_id, file_name, file_path, file_size, upload_date, status) VALUES
(1, 1, 'bella_health_report.pdf', 'uploads/user_1/bella_health_report.pdf', 245760, '2026-04-01', 'active'),
(1, 2, 'daisy_vaccination.jpg', 'uploads/user_1/daisy_vaccination.jpg', 512000, '2026-04-02', 'active'),
(2, 4, 'molly_feed_chart.png', 'uploads/user_2/molly_feed_chart.png', 384000, '2026-04-03', 'active');

-- FARMS NETWORK: Simple graph for BFS algorithm (4 farms)
INSERT INTO farms_network (farm_id, user_id, farm_name, latitude, longitude) VALUES
(1, 1, 'Green Valley Dairy', 37.7749, -122.4194),
(2, 1, 'North Pasture', 37.7849, -122.4094),
(3, 2, 'Sunny Meadows', 37.7649, -122.4294),
(4, 3, 'Blue Ridge', 37.7549, -122.4394);

-- FARM CONNECTIONS: Simple graph edges for disease spread simulation
INSERT INTO farm_connections (farm1_id, farm2_id, distance_km) VALUES
(1, 2, 2.5),    -- Green Valley ↔ North Pasture
(1, 3, 3.0),    -- Green Valley ↔ Sunny Meadows
(2, 3, 1.8),    -- North Pasture ↔ Sunny Meadows
(3, 4, 4.2),    -- Sunny Meadows ↔ Blue Ridge
(1, 4, 5.5);    -- Green Valley ↔ Blue Ridge

-- PROCESS LOG: Sample processes for OS scheduling (Round Robin: quantum=2)
INSERT INTO process_log (user_id, request_type, process_state, priority, burst_time, arrival_time) VALUES
(1, 'milk_collection', 'ready', 1, 4, '2026-04-01 08:00:00'),  -- Process P1: 4 units
(1, 'feed_distribution', 'ready', 2, 6, '2026-04-01 08:05:00'), -- Process P2: 6 units
(1, 'health_check', 'ready', 1, 8, '2026-04-01 08:10:00'),      -- Process P3: 8 units
(1, 'data_backup', 'ready', 3, 10, '2026-04-01 08:15:00'),      -- Process P4: 10 units
(2, 'inventory_update', 'ready', 2, 5, '2026-04-01 08:20:00'),  -- Process P5: 5 units
(2, 'report_generation', 'ready', 1, 7, '2026-04-01 08:25:00'); -- Process P6: 7 units

-- RESOURCE LOCKS: Sample for deadlock demonstration
INSERT INTO resource_locks (resource_name, locked_by_process_id, lock_time) VALUES
('printer', 1, '2026-04-01 09:00:00'),
('database', 2, '2026-04-01 09:05:00'),
('file_system', 3, '2026-04-01 09:10:00');
