-- ============================================
-- SMART LIVESTOCK MANAGEMENT SYSTEM
-- SQLite Database Schema
-- ============================================

-- ============================================
-- TABLE 1: USERS (Authentication & Authorization)
-- Purpose: Store farmer/user credentials and info
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    phone TEXT,
    address TEXT,
    farm_name TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive'))
);

CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- ============================================
-- TABLE 2: ANIMALS (Livestock Details)
-- Purpose: Store information about each animal
-- Foreign Key: Links to users table
-- ============================================
CREATE TABLE IF NOT EXISTS animals (
    animal_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    animal_name TEXT NOT NULL,
    animal_type TEXT NOT NULL CHECK (animal_type IN ('cow', 'buffalo', 'goat', 'sheep')),
    breed TEXT,
    age INTEGER,
    weight REAL,
    milk_yield_daily REAL,
    health_status TEXT DEFAULT 'healthy' CHECK (health_status IN ('healthy', 'sick', 'recovering', 'under_observation')),
    last_health_check DATE,
    vaccination_status TEXT,
    feed_type TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_animals_user_id ON animals(user_id);
CREATE INDEX IF NOT EXISTS idx_animals_animal_type ON animals(animal_type);
CREATE INDEX IF NOT EXISTS idx_animals_health_status ON animals(health_status);

-- ============================================
-- TABLE 3: SUPPLEMENTS (Nutritional Products)
-- Purpose: Store available supplements database
-- Note: Used in Greedy Algorithm for optimization
-- ============================================
CREATE TABLE IF NOT EXISTS supplements (
    supplement_id INTEGER PRIMARY KEY AUTOINCREMENT,
    supplement_name TEXT NOT NULL UNIQUE,
    protein_content REAL,
    fat_content REAL,
    minerals REAL,
    cost_per_kg REAL NOT NULL,
    description TEXT,
    animal_type TEXT DEFAULT 'all' CHECK (animal_type IN ('cow', 'buffalo', 'goat', 'sheep', 'all')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_supplements_animal_type ON supplements(animal_type);

-- ============================================
-- TABLE 4: RECOMMENDATIONS (AI Supplement Suggestions)
-- Purpose: Store recommendation history
-- Note: Generated using Greedy Algorithm
-- ============================================
CREATE TABLE IF NOT EXISTS recommendations (
    recommendation_id INTEGER PRIMARY KEY AUTOINCREMENT,
    animal_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    budget REAL,
    recommended_supplements TEXT,
    total_nutrition_score REAL,
    total_cost REAL,
    recommendation_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(animal_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_recommendations_animal_id ON recommendations(animal_id);
CREATE INDEX IF NOT EXISTS idx_recommendations_user_id ON recommendations(user_id);

-- ============================================
-- TABLE 5: UPLOADS (File Management)
-- Purpose: Track file uploads for each user/animal
-- Note: Demonstrates OS File Management concepts
-- ============================================
CREATE TABLE IF NOT EXISTS uploads (
    upload_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    animal_id INTEGER,
    file_name TEXT NOT NULL,
    file_path TEXT NOT NULL,
    file_size INTEGER,
    file_type TEXT,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animals(animal_id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_uploads_user_id ON uploads(user_id);
CREATE INDEX IF NOT EXISTS idx_uploads_animal_id ON uploads(animal_id);

-- ============================================
-- TABLE 6: MILK_PRODUCTION (Daily Production Tracking)
-- Purpose: Track milk production over time
-- Note: Used in Dynamic Programming for prediction
-- ============================================
CREATE TABLE IF NOT EXISTS milk_production (
    production_id INTEGER PRIMARY KEY AUTOINCREMENT,
    animal_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    production_date DATE NOT NULL,
    morning_yield REAL,
    evening_yield REAL,
    total_yield REAL,
    quality_score INTEGER CHECK (quality_score >= 1 AND quality_score <= 10),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(animal_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE(animal_id, production_date)
);

CREATE INDEX IF NOT EXISTS idx_milk_production_animal_id ON milk_production(animal_id);
CREATE INDEX IF NOT EXISTS idx_milk_production_date ON milk_production(production_date);

-- ============================================
-- TABLE 7: PROCESS_LOG (OS Process Simulation)
-- Purpose: Log process management operations
-- ============================================
CREATE TABLE IF NOT EXISTS process_log (
    log_id INTEGER PRIMARY KEY AUTOINCREMENT,
    process_id INTEGER NOT NULL,
    process_name TEXT NOT NULL,
    operation TEXT NOT NULL CHECK (operation IN ('create', 'suspend', 'resume', 'terminate')),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    details TEXT
);

-- ============================================
-- TABLE 8: RESOURCE_LOCKS (Deadlock Simulation)
-- Purpose: Simulate resource allocation and deadlocks
-- ============================================
CREATE TABLE IF NOT EXISTS resource_locks (
    lock_id INTEGER PRIMARY KEY AUTOINCREMENT,
    process_id INTEGER NOT NULL,
    resource_name TEXT NOT NULL,
    lock_type TEXT NOT NULL CHECK (lock_type IN ('read', 'write')),
    acquired_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    released_at DATETIME,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'released'))
);

-- ============================================
-- TABLE 9: FARMS_NETWORK (Graph Structure)
-- Purpose: Represent farm connections for disease spread analysis
-- Note: Used in Graph/BFS algorithms
-- ============================================
CREATE TABLE IF NOT EXISTS farms_network (
    farm_id INTEGER PRIMARY KEY AUTOINCREMENT,
    farm_name TEXT NOT NULL,
    location TEXT,
    total_animals INTEGER DEFAULT 0,
    disease_risk_level TEXT DEFAULT 'low' CHECK (disease_risk_level IN ('low', 'medium', 'high'))
);

-- ============================================
-- TABLE 10: FARM_CONNECTIONS (Graph Edges)
-- Purpose: Define connections between farms
-- ============================================
CREATE TABLE IF NOT EXISTS farm_connections (
    connection_id INTEGER PRIMARY KEY AUTOINCREMENT,
    farm1_id INTEGER NOT NULL,
    farm2_id INTEGER NOT NULL,
    distance REAL,
    connection_type TEXT DEFAULT 'road' CHECK (connection_type IN ('road', 'rail', 'air')),
    travel_time INTEGER,
    FOREIGN KEY (farm1_id) REFERENCES farms_network(farm_id) ON DELETE CASCADE,
    FOREIGN KEY (farm2_id) REFERENCES farms_network(farm_id) ON DELETE CASCADE,
    UNIQUE(farm1_id, farm2_id)
);

-- ============================================
-- TRIGGERS for updated_at
-- ============================================

-- Trigger for users table
CREATE TRIGGER IF NOT EXISTS update_users_updated_at
    AFTER UPDATE ON users
    FOR EACH ROW
    BEGIN
        UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE user_id = NEW.user_id;
    END;

-- Trigger for animals table
CREATE TRIGGER IF NOT EXISTS update_animals_updated_at
    AFTER UPDATE ON animals
    FOR EACH ROW
    BEGIN
        UPDATE animals SET updated_at = CURRENT_TIMESTAMP WHERE animal_id = NEW.animal_id;
    END;

-- ============================================
-- SAMPLE DATA INSERTION
-- ============================================

-- Insert sample users
INSERT OR IGNORE INTO users (username, email, password_hash, full_name, farm_name) VALUES
('farmer_john', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Smith', 'Green Valley Farm'),
('farmer_sarah', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', 'Sunny Meadows Farm'),
('farmer_mike', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Wilson', 'River Bend Farm');

-- Insert sample supplements
INSERT OR IGNORE INTO supplements (supplement_name, protein_content, fat_content, minerals, cost_per_kg, description, animal_type) VALUES
('Premium Protein Mix', 25.5, 5.2, 8.1, 45.00, 'High protein supplement for dairy cows', 'cow'),
('Mineral Block Plus', 2.1, 1.5, 35.0, 12.50, 'Essential minerals for all livestock', 'all'),
('Energy Booster', 15.0, 12.0, 5.0, 38.00, 'Fat-based energy supplement', 'cow'),
('Goat Nutrition Pack', 18.0, 6.0, 10.0, 32.00, 'Balanced nutrition for goats', 'goat'),
('Sheep Vitamin Mix', 20.0, 4.0, 12.0, 28.00, 'Vitamin-rich supplement for sheep', 'sheep');

-- Insert sample farms for network analysis
INSERT OR IGNORE INTO farms_network (farm_name, location, total_animals, disease_risk_level) VALUES
('Green Valley Farm', 'North District', 150, 'low'),
('Sunny Meadows Farm', 'East District', 200, 'medium'),
('River Bend Farm', 'South District', 120, 'low'),
('Mountain View Farm', 'West District', 80, 'high');

-- Insert sample farm connections
INSERT OR IGNORE INTO farm_connections (farm1_id, farm2_id, distance, connection_type, travel_time) VALUES
(1, 2, 25.5, 'road', 45),
(1, 3, 30.2, 'road', 50),
(2, 4, 40.0, 'road', 75),
(3, 4, 35.8, 'rail', 30);