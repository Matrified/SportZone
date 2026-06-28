-- SportZone database
-- CIT6224 Group 16
-- Import this file in phpMyAdmin (it creates the database and all tables,
-- and inserts the categories, products and the admin + customer accounts).

CREATE DATABASE IF NOT EXISTS sportzone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportzone_db;

-- drop old tables if re-importing
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS promo_codes;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- users (both customers and admins)
CREATE TABLE users (
    user_id      INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(100) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    phone        VARCHAR(20)  DEFAULT NULL,
    address      VARCHAR(255) DEFAULT NULL,
    role         ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    status       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- product categories
CREATE TABLE categories (
    category_id  INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(50) NOT NULL,
    slug         VARCHAR(50) NOT NULL UNIQUE,
    icon         VARCHAR(10) DEFAULT NULL
) ENGINE=InnoDB;

-- products
CREATE TABLE products (
    product_id   INT AUTO_INCREMENT PRIMARY KEY,
    category_id  INT NOT NULL,
    name         VARCHAR(150) NOT NULL,
    description  TEXT,
    brand        VARCHAR(50)  DEFAULT NULL,
    price        DECIMAL(10,2) NOT NULL,
    stock        INT NOT NULL DEFAULT 0,
    sizes        VARCHAR(100) DEFAULT NULL,
    image        VARCHAR(255) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- product reviews
CREATE TABLE reviews (
    review_id    INT AUTO_INCREMENT PRIMARY KEY,
    product_id   INT NOT NULL,
    user_id      INT NOT NULL,
    rating       TINYINT NOT NULL,
    comment      TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- shopping cart (saved per logged-in user)
CREATE TABLE cart (
    cart_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    product_id   INT NOT NULL,
    size         VARCHAR(10) DEFAULT NULL,
    quantity     INT NOT NULL DEFAULT 1,
    added_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- wishlist (saved/favourite products per user)
CREATE TABLE wishlist (
    wishlist_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    product_id   INT NOT NULL,
    added_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- orders
CREATE TABLE orders (
    order_id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    full_name       VARCHAR(100) NOT NULL,
    phone           VARCHAR(20) NOT NULL,
    address         VARCHAR(255) NOT NULL,
    city            VARCHAR(50) NOT NULL,
    postal_code     VARCHAR(20) NOT NULL,
    payment_method  ENUM('cod','card') NOT NULL DEFAULT 'cod',
    subtotal        DECIMAL(10,2) NOT NULL,
    shipping_fee    DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_code   VARCHAR(30) DEFAULT NULL,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total           DECIMAL(10,2) NOT NULL,
    status          ENUM('Pending','Processing','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- items inside each order
CREATE TABLE order_items (
    order_item_id  INT AUTO_INCREMENT PRIMARY KEY,
    order_id       INT NOT NULL,
    product_id     INT DEFAULT NULL,
    product_name   VARCHAR(150) NOT NULL,
    size           VARCHAR(10) DEFAULT NULL,
    quantity       INT NOT NULL,
    price          DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- discount / promo codes used at checkout
CREATE TABLE promo_codes (
    promo_id   INT AUTO_INCREMENT PRIMARY KEY,
    code       VARCHAR(30) NOT NULL UNIQUE,
    type       ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    value      DECIMAL(10,2) NOT NULL,
    active     TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- messages submitted through the Contact page
CREATE TABLE contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL,
    message    TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- Accounts
-- Passwords are bcrypt hashes generated with PHP password_hash().
--   admin@sportzone.com    -> admin123
--   customer@sportzone.com -> customer123
-- ----------------------------------------------------------
INSERT INTO users (full_name, email, password, phone, address, role, status) VALUES
('Site Administrator', 'admin@sportzone.com', '$2y$10$qLuTqef1vcFYLhmJMV6X4ebdGF5a40zoLgErKekLFshNYQYDLe3Wy', NULL, NULL, 'admin', 'active'),
('John Customer', 'customer@sportzone.com', '$2y$10$i1Kap64EaGAadjwyYt7LuOfSDIfCG7UDEjOm.QfhsVAaQbH/LIRYa', '+60 12-345 6789', '12 Jalan Multimedia, Cyberjaya', 'customer', 'active');

-- categories
INSERT INTO categories (name, slug, icon) VALUES
('Football',   'football',   '⚽'),
('Basketball', 'basketball', '🏀'),
('Running',    'running',    '🏃'),
('Gym',        'gym',        '🏋'),
('Sportswear', 'sportswear', '👕');

-- products (image column points to the SVG product tile in assets/images/products/)
INSERT INTO products (category_id, name, description, brand, price, stock, sizes, image) VALUES
-- Football
(1,'Match Football','Official size 5 match ball with a durable stitched casing and good shape retention.','Nike',89.90,40,NULL,'football-ball.png'),
(1,'Shin Guards','Lightweight shin guards with a hard outer shell and ankle protection.','Adidas',35.00,60,'S,M,L','football-shin-guards.png'),
(1,'Goalkeeper Gloves','Latex palm goalkeeper gloves with finger support and a snug wrist strap.','Nike',65.00,30,'8,9,10','football-gloves.png'),
(1,'Football Boots','Firm ground boots with moulded studs for grip on natural grass pitches.','Puma',159.00,28,'7,8,9,10,11','football-boots.png'),
-- Basketball
(2,'Basketball','Composite leather basketball that performs well on indoor and outdoor courts.','Spalding',75.00,35,NULL,'basketball-ball.png'),
(2,'High-Top Shoes','High-top basketball shoes with ankle support and cushioned soles.','Nike',349.00,25,'7,8,9,10,11','basketball-shoes.png'),
(2,'Basketball Jersey','Loose-fit breathable mesh jersey that keeps you cool during a game.','Under Armour',69.00,40,'S,M,L,XL','basketball-jersey.png'),
(2,'Knee Pads','Padded knee sleeves that cushion against falls on the court.','McDavid',29.00,45,'S,M,L','basketball-knee-pads.png'),
-- Running
(3,'Running Shoes','Breathable mesh running shoes with responsive foam cushioning.','Adidas',120.00,50,'7,8,9,10,11,12','running-shoes.png'),
(3,'Running Socks','Moisture-wicking compression running socks (pair) that reduce blisters.','Puma',19.99,100,'S,M,L','running-socks.png'),
(3,'GPS Sports Watch','Tracks pace, distance and heart rate with built-in GPS.','Garmin',249.00,18,NULL,'running-watch.png'),
(3,'Compression Tights','Full-length compression tights for support and warmth.','Under Armour',59.00,48,'S,M,L,XL','running-tights.png'),
-- Gym
(4,'Adjustable Dumbbell Set','Adjustable dumbbell pair, 2kg to 20kg per side. Space-saving design.','Reebok',450.00,15,NULL,'gym-dumbbells.png'),
(4,'Yoga Mat','Extra-thick non-slip yoga mat with a carrying strap included.','Reebok',45.00,70,NULL,'gym-yoga-mat.png'),
(4,'Kettlebell 12kg','Cast iron kettlebell with a wide handle and flat base.','Reebok',60.00,35,NULL,'gym-kettlebell.png'),
(4,'Resistance Bands','Set of five looped bands at different resistance levels for full-body workouts.','Fit Simplify',25.00,90,NULL,'gym-bands.png'),
-- Sportswear
(5,'Training T-Shirt','Quick-dry training tee with breathable side panels.','Nike',55.00,80,'S,M,L,XL','sportswear-tshirt.png'),
(5,'Track Jacket','Full-zip track jacket that is lightweight and water-resistant.','Adidas',149.00,30,'S,M,L,XL','sportswear-jacket.png'),
(5,'Hoodie','Soft fleece-lined hoodie for warm-ups and casual wear.','Under Armour',79.00,45,'S,M,L,XL','sportswear-hoodie.png'),
(5,'Sports Cap','Adjustable cap with a sweat-wicking inner band.','Nike',25.00,90,NULL,'sportswear-cap.png');


-- sample promo codes
INSERT INTO promo_codes (code, type, value, active) VALUES
('SPORT10',  'percent', 10.00, 1),
('SAVE20',   'percent', 20.00, 1),
('WELCOME5', 'fixed',    5.00, 1);
