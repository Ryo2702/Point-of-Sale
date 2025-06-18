-- Sales table
CREATE TABLE sales (
    sale_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(255),
    total_amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    amount_paid DECIMAL(10,2),
    change_amount DECIMAL(10,2),
    cashier_id INT,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sale items table  
CREATE TABLE sale_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT,
    product_id INT,
    product_name VARCHAR(255),
    quantity INT,
    unit_price DECIMAL(10,2),
    total_price DECIMAL(10,2),
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id)
);