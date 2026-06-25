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

-- products (image column holds the intended filename - see docs/IMAGE_GUIDE.md)
INSERT INTO products (category_id, name, description, brand, price, stock, sizes, image) VALUES
-- Football
(1,'Match Football (Size 5)','Official size 5 match ball with a durable stitched synthetic leather casing. Good shape retention and consistent flight.','Nike',89.90,40,NULL,'football-match-ball.jpg'),
(1,'Training Football (Size 4)','Hard-wearing training ball for everyday practice sessions on grass or turf.','Adidas',45.00,55,NULL,'football-training-ball.jpg'),
(1,'Shin Guards','Lightweight shin guards with a hard outer shell and ankle protection.','Adidas',35.00,60,'S,M,L','football-shin-guards.jpg'),
(1,'Goalkeeper Gloves','Latex palm goalkeeper gloves with finger support and a snug wrist strap.','Nike',65.00,30,'8,9,10','football-gk-gloves.jpg'),
(1,'Football Boots (Firm Ground)','Firm ground boots with moulded studs for grip on natural grass pitches.','Puma',159.00,28,'7,8,9,10,11','football-boots.jpg'),
(1,'Team Jersey','Breathable team jersey with moisture-wicking fabric. Fits true to size.','Adidas',75.00,50,'S,M,L,XL','football-jersey.jpg'),
(1,'Ball Pump with Needles','Dual-action hand pump that comes with two spare inflation needles.','Mitre',15.00,90,NULL,'football-pump.jpg'),
(1,'Training Cones (Set of 20)','Set of 20 flexible marker cones for drills and agility training.','Nike',22.00,70,NULL,'football-cones.jpg'),
-- Basketball
(2,'Indoor/Outdoor Basketball','Composite leather basketball that performs well on both indoor and outdoor courts.','Spalding',75.00,35,NULL,'basketball-ball.jpg'),
(2,'High-Top Basketball Shoes','High-top shoes with ankle support and cushioned soles for quick movements.','Nike',349.00,25,'7,8,9,10,11','basketball-shoes.jpg'),
(2,'Basketball Jersey','Loose-fit mesh jersey that keeps you cool during a game.','Under Armour',69.00,40,'S,M,L,XL','basketball-jersey.jpg'),
(2,'Portable Basketball Hoop','Height-adjustable portable hoop with a weighted base. Easy to assemble.','Spalding',299.00,12,NULL,'basketball-hoop.jpg'),
(2,'Compression Arm Sleeve','Compression sleeve that helps with circulation and protects the arm.','Nike',19.00,80,'S,M,L','basketball-arm-sleeve.jpg'),
(2,'Basketball Shorts','Lightweight shorts with side pockets and an elastic drawstring waist.','Adidas',39.00,60,'S,M,L,XL','basketball-shorts.jpg'),
(2,'Knee Pads (Pair)','Padded knee sleeves that cushion against falls on the court.','McDavid',29.00,45,'S,M,L','basketball-knee-pads.jpg'),
(2,'Ball Pump','Compact pump for keeping your basketball at the right pressure.','Spalding',14.00,100,NULL,'basketball-pump.jpg'),
-- Running
(3,'Road Running Shoes','Breathable mesh upper with responsive foam cushioning for road running.','Adidas',120.00,50,'7,8,9,10,11,12','running-shoes-road.jpg'),
(3,'Trail Running Shoes','Grippy outsole and protective toe cap built for off-road trails.','Salomon',145.00,30,'7,8,9,10,11','running-shoes-trail.jpg'),
(3,'Performance Socks (Pair)','Moisture-wicking compression socks that reduce blisters on long runs.','Puma',19.99,100,'S,M,L','running-socks.jpg'),
(3,'Running Shorts','Lightweight shorts with a built-in liner and a zip pocket.','Nike',35.00,70,'S,M,L,XL','running-shorts.jpg'),
(3,'GPS Running Watch','Tracks pace, distance and heart rate with built-in GPS.','Garmin',249.00,18,NULL,'running-watch.jpg'),
(3,'Hydration Belt','Adjustable belt with two water bottles and a small storage pouch.','Nathan',39.00,40,NULL,'running-hydration-belt.jpg'),
(3,'Reflective Vest','High-visibility reflective vest for running safely in low light.','Nike',29.00,55,'S,M,L,XL','running-vest.jpg'),
(3,'Compression Tights','Full-length compression tights for support and warmth.','Under Armour',59.00,48,'S,M,L,XL','running-tights.jpg'),
-- Gym
(4,'Adjustable Dumbbell Set','Adjustable dumbbell pair, 2kg to 20kg per side. Space-saving design.','Reebok',450.00,15,NULL,'gym-dumbbells.jpg'),
(4,'Yoga Mat (Premium)','Extra-thick non-slip mat with a carrying strap included.','Reebok',45.00,70,NULL,'gym-yoga-mat.jpg'),
(4,'Resistance Bands (Set of 5)','Five looped bands at different resistance levels for full-body workouts.','Fit Simplify',25.00,90,NULL,'gym-resistance-bands.jpg'),
(4,'Kettlebell 12kg','Cast iron kettlebell with a wide handle and flat base.','Reebok',60.00,35,NULL,'gym-kettlebell.jpg'),
(4,'Skipping Rope','Adjustable steel-wire skipping rope with foam grips.','Nike',18.00,100,NULL,'gym-skipping-rope.jpg'),
(4,'Weightlifting Gloves','Padded gloves with wrist support for lifting.','Under Armour',27.00,65,'S,M,L,XL','gym-gloves.jpg'),
(4,'Foam Roller','High-density foam roller for muscle recovery and stretching.','TriggerPoint',35.00,50,NULL,'gym-foam-roller.jpg'),
(4,'Gym Duffel Bag','Roomy duffel bag with a separate shoe compartment.','Adidas',49.00,40,NULL,'gym-duffel-bag.jpg'),
-- Sportswear
(5,'Dri-Fit Training T-Shirt','Quick-dry training tee with breathable side panels.','Nike',55.00,80,'S,M,L,XL','sportswear-tshirt.jpg'),
(5,'Track Jacket','Full-zip track jacket that is lightweight and water-resistant.','Adidas',149.00,30,'S,M,L,XL','sportswear-track-jacket.jpg'),
(5,'Training Joggers','Tapered joggers with zip pockets and an elastic cuff.','Puma',65.00,55,'S,M,L,XL','sportswear-joggers.jpg'),
(5,'Sports Cap','Adjustable cap with a sweat-wicking inner band.','Nike',25.00,90,NULL,'sportswear-cap.jpg'),
(5,'Hoodie','Soft fleece-lined hoodie for warm-ups and casual wear.','Under Armour',79.00,45,'S,M,L,XL','sportswear-hoodie.jpg'),
(5,'Compression Base Layer','Tight-fit long sleeve base layer that keeps muscles warm.','Nike',49.00,50,'S,M,L,XL','sportswear-base-layer.jpg'),
(5,'Sports Socks (3-Pack)','Cushioned crew socks in a pack of three pairs.','Adidas',22.00,100,NULL,'sportswear-socks.jpg'),
(5,'Windbreaker Jacket','Packable windbreaker with a hood and zip pockets.','Puma',89.00,33,'S,M,L,XL','sportswear-windbreaker.jpg');


-- sample promo codes
INSERT INTO promo_codes (code, type, value, active) VALUES
('SPORT10',  'percent', 10.00, 1),
('SAVE20',   'percent', 20.00, 1),
('WELCOME5', 'fixed',    5.00, 1);
