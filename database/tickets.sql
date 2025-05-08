CREATE TABLE ticket_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,             -- e.g., General Admission, VIP
    description TEXT,                       -- optional details
    price DECIMAL(10,2) NOT NULL DEFAULT 0, -- e.g., 49.99
    quantity INT NOT NULL DEFAULT 0,        -- number of tickets available
    payment ENUM('pending', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
