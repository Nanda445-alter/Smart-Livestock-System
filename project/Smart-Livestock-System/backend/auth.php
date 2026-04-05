<?php
// ============================================
// AUTHENTICATION MODULE
// Smart Livestock Management System
// Handles user registration, login, logout
// ============================================

require_once 'config.php';

/**
 * Register a new user
 * @param array $data - User registration data
 * @return array - Result with success status
 */
function registerUser($data) {
    global $pdo;
    
    try {
        // Validate input
        $username = sanitizeInput($data['username'] ?? '');
        $email = sanitizeInput($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $fullName = sanitizeInput($data['full_name'] ?? '');
        $phone = sanitizeInput($data['phone'] ?? '');
        $address = sanitizeInput($data['address'] ?? '');
        $farmName = sanitizeInput($data['farm_name'] ?? '');
        
        // Validation checks
        if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
            return [
                'success' => false,
                'message' => 'All required fields must be filled'
            ];
        }
        
        if (!isValidEmail($email)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }
        
        if (strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters long'
            ];
        }
        
        if ($password !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Passwords do not match'
            ];
        }
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Username already exists'
            ];
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }
        
        // Hash password
        $passwordHash = hashPassword($password);
        
        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, full_name, phone, address, farm_name, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([$username, $email, $passwordHash, $fullName, $phone, $address, $farmName]);
        
        return [
            'success' => true,
            'message' => 'Registration successful! Please login.',
            'user_id' => $pdo->lastInsertId()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Login user
 * @param string $username
 * @param string $password
 * @return array - Result with success status
 */
function loginUser($username, $password) {
    global $pdo;
    
    try {
        $username = sanitizeInput($username);
        
        // Find user by username or email
        $stmt = $pdo->prepare("
            SELECT user_id, password_hash, username, email, full_name, status
            FROM users
            WHERE (username = ? OR email = ?) AND status = 'active'
        ");
        
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['login_time'] = time();
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user_id' => $user['user_id']
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Login failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    // Destroy session
    $_SESSION = [];
    session_destroy();
    
    // Redirect to home
    header('Location: ' . APP_URL . '/frontend/index.html');
    exit();
}

?>
