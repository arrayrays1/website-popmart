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

-- create products table for dynamic product pages (must be created before cart_items)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  image_path VARCHAR(512) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  category VARCHAR(100) NOT NULL,
  stock INT NOT NULL DEFAULT 100,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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

-- ================= CRYBABY =================
INSERT INTO products (name, description, image_path, price, category, stock)
VALUES
('CRYBABY Powerpuff Girls Series', 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-1.png', 300.00, 'crybaby', 100),
('CRYBABY Crying for Love Series', 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-2.png', 300.00, 'crybaby', 100),
('CRYBABY Wild but Cute Series', 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-3.png', 300.00, 'crybaby', 100),
('CRYBABY Crying Again 1 Series', 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-4.png', 300.00, 'crybaby', 100),
('CRYBABY Crying Parade Series', 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-5.png', 300.00, 'crybaby', 100),
('CRYBABY Sad Club Series', 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-6.png', 300.00, 'crybaby', 100),
('CRYBABY Crying Again 2 Series', 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-7.png', 300.00, 'crybaby', 100),
('CRYBABY Sunset Concert Series', 'Description here', '/website-popmart/img/products-img-banner/products-crybaby/crybaby-8.png', 300.00, 'crybaby', 100);

-- ================= HIRONO =================
INSERT INTO products (name, description, image_path, price, category, stock)
VALUES
('HIRONO The Other One Series', 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-1.png', 500.00, 'hirono', 100),
('HIRONO x Keith Haring Figurine', 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-2.png', 999.00, 'hirono', 100),
('HIRONO Little Mischief Series', 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-3.png', 500.00, 'hirono', 100),
('HIRONO City of Mercy Series', 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-4.png', 500.00, 'hirono', 100),
('HIRONO Echo Series', 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-5.png', 500.00, 'hirono', 100),
('HIRONO Little Prince', 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-6.png', 500.00, 'hirono', 100),
('HIRONO Mime Series', 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-7.png', 500.00, 'hirono', 100),
('HIRONO The Pianist', 'Description here', '/website-popmart/img/products-img-banner/products-hirono/hirono-8.png', 999.00, 'hirono', 100);

-- ================= MIFFY =================
INSERT INTO products (name, description, image_path, price, category, stock)
VALUES
('MIFFY Doing Things Blind Box', 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-1.png', 300.00, 'miffy', 100),
('MIFFY Goes Outside Blind Box', 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-2.png', 300.00, 'miffy', 100),
('MIFFY in the Snow Blind Box', 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-3.png', 300.00, 'miffy', 100),
('MIFFY and friends Bundle of Lights', 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-4.png', 499.00, 'miffy', 100),
('MIFFY 14-inch Stuffy Plush', 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-5.png', 799.00, 'miffy', 100),
('MIFFY Bluetooth Earphones', 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-6.png', 1499.00, 'miffy', 100),
('MIFFY Silicone Storage Bag', 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-7.png', 349.00, 'miffy', 100),
('MIFFY Character Sling Bag', 'Description here', '/website-popmart/img/products-img-banner/products-miffy/miffy-8.png', 699.00, 'miffy', 100);

-- ================= MOFUSAND =================
INSERT INTO products (name, description, image_path, price, category, stock)
VALUES
('MOFUSAND Pastries', 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-1.png', 300.00, 'mofusand', 100),
('MOFUSAND Journey', 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-2.png', 300.00, 'mofusand', 100),
('MOFUSAND Hippers', 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-3.png', 249.00, 'mofusand', 100),
('MOFUSAND Sharks', 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-4.png', 399.00, 'mofusand', 100),
('MOFUSAND Tempura', 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-5.png', 300.00, 'mofusand', 100),
('MOFUSAND Plushies', 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-6.png', 999.00, 'mofusand', 100),
('MOFUSAND Berry', 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-7.png', 999.00, 'mofusand', 100),
('MOFUSAND Fluffy', 'Description here', '/website-popmart/img/products-img-banner/products-mofusand/mofusand-8.png', 300.00, 'mofusand', 100);

-- ================= SMISKI =================
INSERT INTO products (name, description, image_path, price, category, stock)
VALUES
('SMISKI Museum Series', 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-1.png', 300.00, 'smiski', 100),
('SMISKI Sunday Series', 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-2.png', 300.00, 'smiski', 100),
('SMISKI Moving Series', 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-3.png', 300.00, 'smiski', 100),
('SMISKI Classic Series', 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-4.png', 300.00, 'smiski', 100),
('SMISKI Birthday Series', 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-5.png', 300.00, 'smiski', 100),
('SMISKI Hippers', 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-6.png', 300.00, 'smiski', 100),
('SMISKI Bed Series', 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-7.png', 300.00, 'smiski', 100),
('SMISKI Touch Light', 'Description here', '/website-popmart/img/products-img-banner/products-smiski/smiski-8.png', 300.00, 'smiski', 100);
