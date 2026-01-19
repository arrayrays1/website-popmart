<?php
require_once __DIR__ . '/db_connect.php';
header('Content-Type: text/html; charset=utf-8');

echo '<h2>Admin DB Updater</h2>';
try {
  // 1) Add users.role if missing
  $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
  if (!$col) {
    $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin','customer') NOT NULL DEFAULT 'customer' AFTER password");
    echo '<p>✅ Added users.role</p>';
  } else {
    echo '<p>ℹ️ users.role already exists</p>';
  }

  // 2) Create inventory table if missing
  $pdo->exec("CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    status ENUM('In Stock','Low Stock','Out of Stock') NOT NULL DEFAULT 'In Stock',
    CONSTRAINT fk_inventory_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_inventory_product (product_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  echo '<p>✅ Ensured inventory table exists</p>';

  // 3) Create customer_queries table if missing
  $pdo->exec("CREATE TABLE IF NOT EXISTS customer_queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_registered TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  echo '<p>✅ Ensured customer_queries table exists</p>';

  // 4) Ensure series table (normalized) and required columns
  $pdo->exec("CREATE TABLE IF NOT EXISTS series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image_path VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_series_name (name)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  echo '<p>✅ Ensured series table exists</p>';

  // 5) Add products.series_id and backfill from products.category
  $hasSeriesId = $pdo->query("SHOW COLUMNS FROM products LIKE 'series_id'")->fetch();
  if (!$hasSeriesId) {
    $pdo->exec("ALTER TABLE products ADD COLUMN series_id INT NULL AFTER name");
    echo '<p>✅ Added products.series_id</p>';
  }
  // Backfill: create series from distinct category values and map
  try {
    $pdo->exec("INSERT IGNORE INTO series(name)
                SELECT DISTINCT category FROM products
                WHERE category IS NOT NULL AND category <> ''");
    // Map products.category -> series_id
    $pdo->exec("UPDATE products p
                JOIN series s ON s.name = p.category
                SET p.series_id = s.id
                WHERE (p.series_id IS NULL OR p.series_id = 0)");
  } catch (Exception $e) { /* ignore */ }
  // Enforce NOT NULL and add FK
  try { $pdo->exec("ALTER TABLE products MODIFY series_id INT NOT NULL"); } catch (Exception $e) { /* might already be NOT NULL */ }
  try { $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_products_series FOREIGN KEY (series_id) REFERENCES series(id) ON DELETE RESTRICT"); } catch (Exception $e) { /* already exists */ }
  // Drop legacy category column if present
  $hasCategory = $pdo->query("SHOW COLUMNS FROM products LIKE 'category'")->fetch();
  if ($hasCategory) {
    try { $pdo->exec("ALTER TABLE products DROP COLUMN category"); echo '<p>✅ Dropped products.category</p>'; } catch (Exception $e) { echo '<p>ℹ️ Could not drop products.category</p>'; }
  }

  // 6) Normalize orders.status to required enum and values
  // If orders table missing, create with correct enum
  $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      cart_id INT,
      payment_method VARCHAR(50) NOT NULL,
      shipping_address TEXT NOT NULL,
      subtotal DECIMAL(10,2) NOT NULL,
      shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 5.00,
      total DECIMAL(10,2) NOT NULL,
      status ENUM('Pending','To Ship','To Deliver','Completed') DEFAULT 'Pending',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
      CONSTRAINT fk_orders_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE SET NULL,
      INDEX idx_orders_user (user_id),
      INDEX idx_orders_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Try to convert known legacy values if column exists
  $colStatus = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'")->fetch();
  if ($colStatus) {
    // Map common legacy statuses to new ones
    $map = [
      'pending' => 'Pending',
      'confirmed' => 'To Ship',
      'shipped' => 'To Deliver',
      'delivered' => 'Completed',
      'cancelled' => 'Pending'
    ];
    foreach ($map as $old => $new) {
      try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE status = ?");
        $stmt->execute([$new, $old]);
      } catch (Exception $e) { /* ignore */ }
    }

    // Ensure enum matches target values
    try {
      $pdo->exec("ALTER TABLE orders MODIFY COLUMN status ENUM('Pending','To Ship','To Deliver','Completed') NOT NULL DEFAULT 'Pending'");
    } catch (Exception $e) { /* ignore if already applied */ }
  }

  echo '<h3>Done.</h3><p><a href="../index.php">Return to site</a></p>';
} catch (PDOException $e) {
  echo '<p style="color:red">Error: '.htmlspecialchars($e->getMessage()).'</p>';
}
