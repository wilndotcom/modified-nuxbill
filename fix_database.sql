-- Fix Database - Create Tables and Test User
-- Run this in phpMyAdmin

-- Create customers table if not exists
CREATE TABLE IF NOT EXISTS tbl_customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100),
    email VARCHAR(100),
    phonenumber VARCHAR(20),
    address TEXT,
    status VARCHAR(20) DEFAULT 'Active',
    balance DECIMAL(10,2) DEFAULT 0,
    service_type VARCHAR(50),
    account_type VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    photo VARCHAR(50) DEFAULT 'default'
);

-- Create tickets table if not exists
CREATE TABLE IF NOT EXISTS tbl_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT,
    category VARCHAR(50),
    priority VARCHAR(20) DEFAULT 'medium',
    status VARCHAR(20) DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME,
    assigned_to INT
);

-- Create ticket replies table if not exists
CREATE TABLE IF NOT EXISTS tbl_ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    customer_id INT,
    admin_id INT,
    message TEXT NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create ticket categories table if not exists
CREATE TABLE IF NOT EXISTS tbl_ticket_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    color VARCHAR(10) DEFAULT '#007bff',
    enabled TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO tbl_ticket_categories (name, description, color, enabled) VALUES
('General Inquiry', 'General questions and inquiries', '#007bff', 1),
('Technical Support', 'Technical issues and troubleshooting', '#dc3545', 1),
('Billing', 'Billing and payment related questions', '#28a745', 1),
('Feature Request', 'Requests for new features', '#ffc107', 1)
ON DUPLICATE KEY UPDATE name=name;

-- Insert test customer (password: 'password')
INSERT INTO tbl_customers (username, password, fullname, email, phonenumber, address, status, balance, service_type, account_type, created_at)
VALUES ('testuser', '5f4dcc3b5aa765d61d8327deb882cf99', 'Test User', 'test@test.com', '1234567890', 'Test Address', 'Active', 0, 'Personal', 'Member', NOW())
ON DUPLICATE KEY UPDATE username=username;
