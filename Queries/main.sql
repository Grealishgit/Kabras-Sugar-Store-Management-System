users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  phone VARCHAR(15),
  national_id VARCHAR(20),
  password VARCHAR(255),
  role ENUM('admin','manager','staff') DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    unit VARCHAR(20),
    batch_number VARCHAR(50),
    expiry_date DATE,
    production_date DATE,
    supplier VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active',
    created_by INT, -- User ID of cashier/admin who added the product
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  contact VARCHAR(100),
  address TEXT
);

purchases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT,
  total_cost DECIMAL(10,2),
  purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

purchase_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT,
  product_id INT,
  quantity INT,
  unit_cost DECIMAL(10,2),
  FOREIGN KEY (purchase_id) REFERENCES purchases(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL, -- If you want to track customers, otherwise can be removed
    user_id INT NOT NULL, -- Cashier/admin who processed the sale
    total_amount DECIMAL(10,2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);


CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,                -- Link to the sale
    customer_id INT NULL,                -- Optional: link to customer
    user_id INT NOT NULL,                -- Cashier who recorded the payment
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    method ENUM('cash', 'mpesa', 'card', 'bank') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('completed', 'pending', 'partial') DEFAULT 'completed',
    reference_number VARCHAR(100) NULL,  -- e.g. M-Pesa code, card ref
    notes TEXT NULL,                     -- Any remarks
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(50) UNIQUE,      -- Internal/customer reference ID
    name VARCHAR(150) NOT NULL,            -- Full name / business name
    email VARCHAR(100) NULL,               -- Optional email
    phone VARCHAR(20) NULL,                -- Phone / M-Pesa number
    address VARCHAR(255) NULL,             -- Physical address
    town VARCHAR(100) NULL,                -- Town/City
    type ENUM('individual', 'business') DEFAULT 'individual', -- Type of customer
    status ENUM('active', 'inactive') DEFAULT 'active',       -- Active status
    notes TEXT NULL,                        -- Extra info
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    vendor VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id) -- assuming you have a users table
);


CREATE TABLE compliance_audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    audit_type ENUM('Financial', 'Stock', 'Safety', 'Regulatory') NOT NULL,
    conducted_by INT NOT NULL, -- user_id of the inspector
    status ENUM('Pending', 'Passed', 'Failed') NOT NULL DEFAULT 'Pending',
    comments TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conducted_by) REFERENCES users(id)
);


CREATE TABLE compliance_violations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    violation_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    category ENUM('Financial', 'Stock', 'Safety', 'Legal') NOT NULL,
    reported_by INT NOT NULL, -- user_id of reporter
    description TEXT NOT NULL,
    severity ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Low',
    status ENUM('Pending', 'Resolved') NOT NULL DEFAULT 'Pending',
    resolution_notes TEXT,
    resolved_by INT DEFAULT NULL, -- user_id of person who resolved it
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id)
);

