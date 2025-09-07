-- orders.php
BEGIN TRANSACTION;
INSERT INTO orders (...) VALUES (...);
INSERT INTO order_items (order_id, menu_id, quantity) VALUES (...);
COMMIT;