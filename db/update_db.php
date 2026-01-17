<?php
require_once 'db_connect.php';

echo "<h2>Updating Database Schema...</h2>";

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        cart_id INT,
        payment_method VARCHAR(50) NOT NULL,
        shipping_address TEXT NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 5.00,
        total DECIMAL(10,2) NOT NULL,
        status ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_orders_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE SET NULL,
        INDEX idx_orders_user (user_id),
        INDEX idx_orders_status (status)
    )");
    echo "<p>✅ Checked/Created 'orders' table</p>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
        INDEX idx_order_items_order (order_id)
    )");
    echo "<p>✅ Checked/Created 'order_items' table</p>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        order_id INT,
        rating INT NOT NULL,
        review_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
        UNIQUE KEY unique_user_order_product (user_id, product_id, order_id),
        INDEX idx_reviews_product (product_id),
        INDEX idx_reviews_rating (rating)
    )");
    echo "<p>✅ Checked/Created 'reviews' table</p>";

    $stmt = $pdo->query("SHOW INDEX FROM reviews WHERE Key_name = 'unique_user_product_review'");
    if ($stmt->fetch()) {
        $pdo->exec("ALTER TABLE reviews DROP INDEX unique_user_product_review");
        echo "<p>✅ Dropped old 'unique_user_product_review' constraint</p>";
    }
    
    $stmt = $pdo->query("SHOW INDEX FROM reviews WHERE Key_name = 'unique_user_order_product'");
    if (!$stmt->fetch()) {
        try {
            $pdo->exec("ALTER TABLE reviews ADD UNIQUE KEY unique_user_order_product (user_id, product_id, order_id)");
            echo "<p>✅ Added new 'unique_user_order_product' constraint</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Could not add new constraint (might already exist): " . $e->getMessage() . "</p>";
        }
    }

    echo "<h3>Database update complete!</h3>";
    echo "<p><a href='../index.php'>Go to Home</a></p>";

} catch (PDOException $e) {
    echo "<h3>Error: " . $e->getMessage() . "</h3>";
}
?>