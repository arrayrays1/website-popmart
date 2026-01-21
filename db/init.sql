-- create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS webpopmart_db;
USE webpopmart_db;

-- create users table for PopMart website
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- create series table (must be created before products)
CREATE TABLE IF NOT EXISTS series (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  image_path VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_series_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- create products table for dynamic product pages (must be created before cart_items)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  series_id INT NOT NULL,
  description TEXT,
  image_path VARCHAR(512) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  category VARCHAR(100) NULL,
  stock INT NOT NULL DEFAULT 100,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_series FOREIGN KEY (series_id) REFERENCES series(id) ON DELETE RESTRICT,
  INDEX idx_category (category)
);

-- carts: one open cart per user (status=open). A future order flow can checkout and set status=ordered.
CREATE TABLE IF NOT EXISTS carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('open','ordered','abandoned') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_carts_user_status (user_id, status)
);

-- cart items referencing products
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    UNIQUE KEY uniq_cart_product (cart_id, product_id)
);

-- contact form submissions
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- orders table to store order information
CREATE TABLE IF NOT EXISTS orders (
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
);

-- order_items table to store individual items in an order
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_items_order (order_id)
);

-- reviews table for product reviews and ratings
CREATE TABLE IF NOT EXISTS reviews (
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
);

-- Insert series data first
INSERT IGNORE INTO series (name) VALUES
('crybaby'),
('hirono'),
('miffy'),
('mofusand'),
('smiski');

-- ================= CRYBABY =================
INSERT INTO products (name, series_id, description, image_path, price, category, stock)
SELECT 'CRYBABY Powerpuff Girls Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-1.png', 300.00, 'crybaby', 100 FROM series s WHERE s.name = 'crybaby'
UNION ALL SELECT 'CRYBABY Crying for Love Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-2.png', 300.00, 'crybaby', 100 FROM series s WHERE s.name = 'crybaby'
UNION ALL SELECT 'CRYBABY Wild but Cute Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-3.png', 300.00, 'crybaby', 100 FROM series s WHERE s.name = 'crybaby'
UNION ALL SELECT 'CRYBABY Crying Again 1 Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-4.png', 300.00, 'crybaby', 100 FROM series s WHERE s.name = 'crybaby'
UNION ALL SELECT 'CRYBABY Crying Parade Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-5.png', 300.00, 'crybaby', 100 FROM series s WHERE s.name = 'crybaby'
UNION ALL SELECT 'CRYBABY Sad Club Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-6.png', 300.00, 'crybaby', 100 FROM series s WHERE s.name = 'crybaby'
UNION ALL SELECT 'CRYBABY Crying Again 2 Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-7.png', 300.00, 'crybaby', 100 FROM series s WHERE s.name = 'crybaby'
UNION ALL SELECT 'CRYBABY Sunset Concert Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-8.png', 300.00, 'crybaby', 100 FROM series s WHERE s.name = 'crybaby';
-- ================= HIRONO =================
INSERT INTO products (name, series_id, description, image_path, price, category, stock)
SELECT 'HIRONO The Other One Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-1.png', 500.00, 'hirono', 100 FROM series s WHERE s.name = 'hirono'
UNION ALL SELECT 'HIRONO x Keith Haring Figurine', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-2.png', 999.00, 'hirono', 100 FROM series s WHERE s.name = 'hirono'
UNION ALL SELECT 'HIRONO Little Mischief Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-3.png', 500.00, 'hirono', 100 FROM series s WHERE s.name = 'hirono'
UNION ALL SELECT 'HIRONO City of Mercy Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-4.png', 500.00, 'hirono', 100 FROM series s WHERE s.name = 'hirono'
UNION ALL SELECT 'HIRONO Echo Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-5.png', 500.00, 'hirono', 100 FROM series s WHERE s.name = 'hirono'
UNION ALL SELECT 'HIRONO Little Prince', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-6.png', 500.00, 'hirono', 100 FROM series s WHERE s.name = 'hirono'
UNION ALL SELECT 'HIRONO Mime Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-7.png', 500.00, 'hirono', 100 FROM series s WHERE s.name = 'hirono'
UNION ALL SELECT 'HIRONO The Pianist', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-8.png', 999.00, 'hirono', 100 FROM series s WHERE s.name = 'hirono';

-- ================= MIFFY =================
INSERT INTO products (name, series_id, description, image_path, price, category, stock)
SELECT 'MIFFY Doing Things Blind Box', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-1.png', 300.00, 'miffy', 100 FROM series s WHERE s.name = 'miffy'
UNION ALL SELECT 'MIFFY Goes Outside Blind Box', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-2.png', 300.00, 'miffy', 100 FROM series s WHERE s.name = 'miffy'
UNION ALL SELECT 'MIFFY in the Snow Blind Box', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-3.png', 300.00, 'miffy', 100 FROM series s WHERE s.name = 'miffy'
UNION ALL SELECT 'MIFFY and friends Bundle of Lights', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-4.png', 499.00, 'miffy', 100 FROM series s WHERE s.name = 'miffy'
UNION ALL SELECT 'MIFFY 14-inch Stuffy Plush', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-5.png', 799.00, 'miffy', 100 FROM series s WHERE s.name = 'miffy'
UNION ALL SELECT 'MIFFY Bluetooth Earphones', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-6.png', 1499.00, 'miffy', 100 FROM series s WHERE s.name = 'miffy'
UNION ALL SELECT 'MIFFY Silicone Storage Bag', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-7.png', 349.00, 'miffy', 100 FROM series s WHERE s.name = 'miffy'
UNION ALL SELECT 'MIFFY Character Sling Bag', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-8.png', 699.00, 'miffy', 100 FROM series s WHERE s.name = 'miffy';

-- ================= MOFUSAND =================
INSERT INTO products (name, series_id, description, image_path, price, category, stock)
SELECT 'MOFUSAND Pastries', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-1.png', 300.00, 'mofusand', 100 FROM series s WHERE s.name = 'mofusand'
UNION ALL SELECT 'MOFUSAND Journey', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-2.png', 300.00, 'mofusand', 100 FROM series s WHERE s.name = 'mofusand'
UNION ALL SELECT 'MOFUSAND Hippers', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-3.png', 249.00, 'mofusand', 100 FROM series s WHERE s.name = 'mofusand'
UNION ALL SELECT 'MOFUSAND Sharks', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-4.png', 399.00, 'mofusand', 100 FROM series s WHERE s.name = 'mofusand'
UNION ALL SELECT 'MOFUSAND Tempura', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-5.png', 300.00, 'mofusand', 100 FROM series s WHERE s.name = 'mofusand'
UNION ALL SELECT 'MOFUSAND Plushies', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-6.png', 999.00, 'mofusand', 100 FROM series s WHERE s.name = 'mofusand'
UNION ALL SELECT 'MOFUSAND Berry', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-7.png', 999.00, 'mofusand', 100 FROM series s WHERE s.name = 'mofusand'
UNION ALL SELECT 'MOFUSAND Fluffy', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-8.png', 300.00, 'mofusand', 100 FROM series s WHERE s.name = 'mofusand';

-- ================= SMISKI =================
INSERT INTO products (name, series_id, description, image_path, price, category, stock)
SELECT 'SMISKI Museum Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-1.png', 300.00, 'smiski', 100 FROM series s WHERE s.name = 'smiski'
UNION ALL SELECT 'SMISKI Sunday Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-2.png', 300.00, 'smiski', 100 FROM series s WHERE s.name = 'smiski'
UNION ALL SELECT 'SMISKI Moving Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-3.png', 300.00, 'smiski', 100 FROM series s WHERE s.name = 'smiski'
UNION ALL SELECT 'SMISKI Classic Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-4.png', 300.00, 'smiski', 100 FROM series s WHERE s.name = 'smiski'
UNION ALL SELECT 'SMISKI Birthday Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-5.png', 300.00, 'smiski', 100 FROM series s WHERE s.name = 'smiski'
UNION ALL SELECT 'SMISKI Hippers', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-6.png', 300.00, 'smiski', 100 FROM series s WHERE s.name = 'smiski'
UNION ALL SELECT 'SMISKI Bed Series', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-7.png', 300.00, 'smiski', 100 FROM series s WHERE s.name = 'smiski'
UNION ALL SELECT 'SMISKI Touch Light', s.id, 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-8.png', 300.00, 'smiski', 100 FROM series s WHERE s.name = 'smiski';
