-- SportZone sample data (OPTIONAL)
-- Import this AFTER sportzone.sql to populate the dashboard, sales chart and
-- order history for screenshots / the demo. It adds reviews, orders and a few
-- contact messages for the demo customer (user_id = 2).

USE sportzone_db;

-- product reviews from the demo customer (all for products the customer has ordered,
-- so they show the "Verified Purchase" badge)
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
(1, 2, 5, 'Really good match ball, keeps its shape well after a few games.'),
(1, 2, 4, 'Solid ball for the price. Stitching feels strong.'),
(9, 2, 5, 'Super comfortable for road runs, lightweight too.'),
(5, 2, 5, 'Great grip on the outdoor court, very happy with it.'),
(17, 2, 4, 'Breathable and dries fast during training.'),
(10, 2, 4, 'Comfortable socks, no blisters on long runs.');

-- orders (statuses and dates vary so the sales chart shows data)
INSERT INTO orders (user_id, full_name, phone, address, city, postal_code, payment_method, subtotal, shipping_fee, total, status, created_at) VALUES
(2,'John Customer','+60 12-345 6789','12 Jalan Multimedia','Cyberjaya','63000','cod', 144.90, 10.00, 154.90, 'Delivered', NOW() - INTERVAL 5 DAY),
(2,'John Customer','+60 12-345 6789','12 Jalan Multimedia','Cyberjaya','63000','card', 120.00, 10.00, 130.00, 'Processing', NOW() - INTERVAL 3 DAY),
(2,'John Customer','+60 12-345 6789','12 Jalan Multimedia','Cyberjaya','63000','cod', 134.97, 10.00, 144.97, 'Pending', NOW() - INTERVAL 1 DAY),
(2,'John Customer','+60 12-345 6789','12 Jalan Multimedia','Cyberjaya','63000','cod', 149.00, 10.00, 159.00, 'Pending', NOW());

-- order 1 items (Match Football + Training T-Shirt)
INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price) VALUES
(1, 1, 'Match Football', NULL, 1, 89.90),
(1, 17, 'Training T-Shirt', 'L', 1, 55.00);

-- order 2 items (Running Shoes)
INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price) VALUES
(2, 9, 'Running Shoes', '9', 1, 120.00);

-- order 3 items (Basketball + Running Socks x3)
INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price) VALUES
(3, 5, 'Basketball', NULL, 1, 75.00),
(3, 10, 'Running Socks', 'M', 3, 19.99);

-- order 4 items (Track Jacket)
INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price) VALUES
(4, 18, 'Track Jacket', 'M', 1, 149.00);

-- a couple of contact messages for the admin Messages page
INSERT INTO contact_messages (name, email, message, created_at) VALUES
('Aisha Rahman', 'aisha.r@example.com', 'Hi, do the running shoes come in half sizes? Thanks!', NOW() - INTERVAL 2 DAY),
('Daniel Lim', 'daniel.lim@example.com', 'When will the adjustable dumbbell set be back in larger quantities?', NOW() - INTERVAL 1 DAY);
