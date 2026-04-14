SET NAMES utf8mb4;

INSERT INTO users (id, username, full_name, password_hash, role, status) VALUES
    (1, 'admin', 'System Administrator', 'pbkdf2_sha256$120000$Wi578z95tv3XpK4DzPI2OQ==$r14V3dN6h1gTsNitQ49Nt7oNy/9falLMQ3OkyydbH7A=', 'admin', 'active'),
    (2, 'manager', 'Warehouse Manager', 'pbkdf2_sha256$120000$CUCHVtToos+siNHQehABjg==$7xJdAwddhjeSFgBUrfDK1xmWdO0RVJ6oKs/+bIA1Tc4=', 'manager', 'active'),
    (3, 'cashier', 'Store Cashier', 'pbkdf2_sha256$120000$8adRflk6IBaooQQmqDjYLA==$PdobT58e5nlnTGTGUQm3jrrtJuGYSXLy2xV1DZbx/Qs=', 'cashier', 'active');

INSERT INTO categories (id, name, status) VALUES
    (1, 'Газированные напитки', 'active'),
    (2, 'Вода', 'active'),
    (3, 'Соки', 'active'),
    (4, 'Закуски', 'active');

INSERT INTO warehouses (id, name, location, status) VALUES
    (1, 'Основной склад', 'Баку, центральный склад', 'active'),
    (2, 'Торговый зал', 'Баку, магазин', 'active'),
    (3, 'Резервный склад', 'Баку, резерв', 'active');

INSERT INTO products (id, name, barcode, article, category_id, purchase_price, sale_price, stock_qty, unit, status) VALUES
    (1, 'Coca-Cola 0.5L', '5449000000996', 'COCA-05', 1, 0.70, 1.20, 120.000, 'bottle', 'active'),
    (2, 'Pepsi 0.5L', '5601234567890', 'PEPSI-05', 1, 0.65, 1.10, 95.000, 'bottle', 'active'),
    (3, 'Вода 1L', '4601234000012', 'WATER-1L', 2, 0.25, 0.55, 180.000, 'bottle', 'active'),
    (4, 'Апельсиновый сок 1L', '4821234567001', 'JUICE-OR-1L', 3, 1.10, 1.85, 60.000, 'pack', 'active'),
    (5, 'Яблочный сок 1L', '4821234567002', 'JUICE-AP-1L', 3, 1.05, 1.80, 48.000, 'pack', 'active');

INSERT INTO stock_balances (warehouse_id, product_id, qty) VALUES
    (1, 1, 80.000),
    (1, 2, 70.000),
    (1, 3, 120.000),
    (1, 4, 40.000),
    (1, 5, 30.000),
    (2, 1, 30.000),
    (2, 2, 20.000),
    (2, 3, 40.000),
    (2, 4, 15.000),
    (2, 5, 10.000),
    (3, 1, 10.000),
    (3, 2, 5.000),
    (3, 3, 20.000),
    (3, 4, 5.000),
    (3, 5, 8.000);
