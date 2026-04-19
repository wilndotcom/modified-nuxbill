-- Create tbl_admins table
CREATE TABLE IF NOT EXISTS tbl_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100),
    user_type ENUM('Admin', 'SuperAdmin', 'Support') DEFAULT 'Admin',
    email VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin
INSERT INTO tbl_admins (username, password, fullname, user_type, email, status)
VALUES ('admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'Administrator', 'SuperAdmin', 'admin@localhost', 'Active')
ON DUPLICATE KEY UPDATE username = username;
