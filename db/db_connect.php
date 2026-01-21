<?php
// database configuration
// update these credentials to match your MySQL setup
$host = 'localhost:3307'; //your mysql host and port
$db   = 'webpopmart_db';
$user = 'root'; // your mysql username
$pass = ''; // your mysql password
$charset = 'utf8mb4';

// PDO Connection Options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// marker file path to track if database has been initialized
$install_marker = __DIR__ . '/.installed';
$init_sql_file = __DIR__ . '/init.sql';

/*
 initialize the database by running the init.sql script
 this function creates the database, tables, and inserts sample data
 */
function initializeDatabase($host, $user, $pass, $init_sql_file) {
    try {
        // first connect without specifying database to create it
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // read and execute the initialization SQL script
        if (!file_exists($init_sql_file)) {
            throw new Exception("Initialization SQL file not found: $init_sql_file");
        }
        
        $sql = file_get_contents($init_sql_file);
        if ($sql === false) {
            throw new Exception("Could not read initialization SQL file");
        }
        
        // splits SQL into individual statements and execute them
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        return true;
        
    } catch (PDOException $e) {
        throw new Exception("Database initialization failed: " . $e->getMessage());
    } catch (Exception $e) {
        throw new Exception("Initialization error: " . $e->getMessage());
    }
}

/*
  creates installation marker file to prevent re-initialization
 */
function createInstallMarker($marker_file) {
    $marker_content = [
        'installed' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0'
    ];
    
    file_put_contents($marker_file, json_encode($marker_content, JSON_PRETTY_PRINT));
}

// main connection logic
try {
    // checks if database has been initialized
    if (!file_exists($install_marker)) {
        // echo "<!-- PopMart: Initializing database for first time... -->\n";
        
        // initialize the database
        initializeDatabase($host, $user, $pass, $init_sql_file);
        
        // create marker file to prevent re-initialization
        createInstallMarker($install_marker);
        
        // echo "<!-- PopMart: Database initialization completed successfully -->\n";
    }
    
    // connect to the database
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset;sslmode=DISABLED"; // added sslmode=DISABLED to work on Linux MySQL/MariaDB newer version
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // store PDO connection globally for backward compatibility
    $conn = $pdo;
    
} catch (PDOException $e) {
    // handle connection errors
    $error_message = "Database connection failed: " . $e->getMessage();
    
    // log error for debugging (you can modify this path as needed)
    error_log("PopMart DB Error: " . $error_message);
    
    // display user-friendly error message
    die("
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #e74c3c; border-radius: 5px; background-color: #fdf2f2;'>
        <h2 style='color: #e74c3c; margin-top: 0;'>Database Connection Error</h2>
        <p><strong>PopMart Website Setup Required</strong></p>
        <p>Please check your database configuration in <code>db/db_connect.php</code>:</p>
        <ul>
            <li>Verify MySQL server is running</li>
            <li>Check host, username, and password credentials</li>
            <li>Ensure MySQL user has proper permissions</li>
        </ul>
        <p><small>Technical details: " . htmlspecialchars($error_message) . "</small></p>
    </div>");
    
} catch (Exception $e) {
    // handle initialization errors
    $error_message = $e->getMessage();
    
    // log error for debugging
    error_log("PopMart Init Error: " . $error_message);
    
    // display error message
    die("
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #e74c3c; border-radius: 5px; background-color: #fdf2f2;'>
        <h2 style='color: #e74c3c; margin-top: 0;'>Database Setup Error</h2>
        <p><strong>PopMart Website Initialization Failed</strong></p>
        <p>There was an error setting up the database. Please:</p>
        <ul>
            <li>Check your MySQL server is running and accessible</li>
            <li>Verify database credentials are correct</li>
            <li>Ensure the init.sql file exists and is readable</li>
            <li>Check file permissions in the db/ directory</li>
        </ul>
        <p><small>Technical details: " . htmlspecialchars($error_message) . "</small></p>
    </div>");
}

// success message for development (remove in production)
if (isset($_GET['debug']) && $_GET['debug'] === 'db') {
    // echo "<!-- PopMart: Database connected successfully -->\n";
}
