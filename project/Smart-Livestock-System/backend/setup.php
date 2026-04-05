<?php
/**
 * Database Setup Script
 * Initializes the livestock.db SQLite database and creates all necessary tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_file = __DIR__ . '/../database/livestock.db';

try {
    // Connect to SQLite
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Setup Started...</h2>";
    echo "<pre>";
    
    // Read schema file
    $schema_file = __DIR__ . '/../database/schema_sqlite.sql';
    if (!file_exists($schema_file)) {
        die("Schema file not found: $schema_file");
    }
    
    $schema = file_get_contents($schema_file);
    
    // Split by semicolon and execute each query
    $queries = array_filter(
        array_map('trim', explode(';', $schema)),
        fn($q) => !empty($q) && substr($q, 0, 2) !== '--'
    );
    
    $count = 0;
    foreach ($queries as $query) {
        if (empty(trim($query))) continue;
        
        try {
            $pdo->exec($query);
            echo "✓ Query executed successfully\n";
            $count++;
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            echo "Query: " . substr($query, 0, 100) . "...\n";
        }
    }
    
    echo "\n✓ Database setup completed! ($count queries executed)\n";
    echo "</pre>";
    
    // Test connection
    echo "<h3>Testing PDO Connection...</h3>";
    echo "<p style='color: green;'>✓ PDO connection successful!</p>";
    
    // Show tables
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "<h4>Tables created:</h4>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Count sample data
    echo "<h4>Sample Data:</h4>";
    $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $supplements = $pdo->query("SELECT COUNT(*) FROM supplements")->fetchColumn();
    $farms = $pdo->query("SELECT COUNT(*) FROM farms_network")->fetchColumn();

    echo "<ul>";
    echo "<li>Users: $users (farmer_john, farmer_sarah, farmer_mike)</li>";
    echo "<li>Supplements: $supplements (with easy nutrition/cost ratios for Greedy algorithm)</li>";
    echo "<li>Farm Network: $farms farms (for BFS disease spread simulation)</li>";
    echo "</ul>";

    echo "<h4>Perfect for Demonstrations:</h4>";
    echo "<ul>";
    echo "<li><strong>Greedy Algorithm:</strong> Supplements with ratios 2.0, 1.5, 1.0, 0.8</li>";
    echo "<li><strong>Binary Search:</strong> Animals with IDs 1-10 (perfect for demo)</li>";
    echo "<li><strong>Dynamic Programming:</strong> Clear increasing/stable/decreasing trends</li>";
    echo "<li><strong>Graph/BFS:</strong> 4 farms with simple connections</li>";
    echo "<li><strong>CPU Scheduling:</strong> Round Robin quantum=2, processes with burst times 4,6,8,10</li>";
    echo "</ul>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='/frontend/index.html'>Access the application</a></li>";
    echo "<li>Register a new account or login with test credentials</li>";
    echo "<li>Test the features</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
