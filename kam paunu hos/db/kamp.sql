-- Create the database (remove this line if database already exists)
CREATE DATABASE IF NOT EXISTS kamp;
USE kamp;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),  -- Increased length for hashed passwords (recommended)
    role ENUM('client','freelancer','admin') DEFAULT 'client',
    skills TEXT,
    profile_pic VARCHAR(255) DEFAULT 'default.jpg'
);

-- Categories table (this was missing and caused the error)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Jobs table
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    budget DECIMAL(10,2),
    client_id INT,
    image VARCHAR(255),
    category VARCHAR(100),  -- You can later change this to category_id INT if you want proper relation
    status ENUM('open','awarded','completed') DEFAULT 'open',
    awarded_to INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bids table
CREATE TABLE bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    freelancer_id INT,
    amount DECIMAL(10,2),
    proposal TEXT,
    status ENUM('pending','accepted','paid') DEFAULT 'pending',
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    job_id INT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bid_id INT,
    transaction_uuid VARCHAR(100),
    amount DECIMAL(10,2),
    status VARCHAR(20),
    ref_id VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bid_id) REFERENCES bids(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, slug, description) VALUES
('Web Development', 'web-development', 'Websites, web apps, e-commerce, Laravel, PHP, etc.'),
('Graphic Design', 'graphic-design', 'Logos, banners, branding, UI/UX design'),
('Mobile App', 'mobile-app', 'Android, iOS, Flutter, React Native apps'),
('Digital Marketing', 'digital-marketing', 'SEO, social media, ads, content marketing'),
('Writing & Translation', 'writing-translation', 'Articles, copywriting, translation'),
('Video & Animation', 'video-animation', 'Video editing, motion graphics, animation');

-- Insert sample users
INSERT INTO users (name, email, password, role, skills) VALUES
('Admin', 'admin@kamp.com', 'admin123', 'admin', ''),
('Ram Bahadur', 'ram@kamp.com', 'ram123', 'freelancer', 'PHP, Laravel, JavaScript'),
('Sita Sharma', 'sita@kamp.com', 'sita123', 'client', ''),
('Hari Prasad', 'hari@kamp.com', 'hari123', 'freelancer', 'React, Node.js'),
('Gita Devi', 'gita@kamp.com', 'gita123', 'freelancer', 'Graphic Design');

-- Insert sample jobs (client_id 3 = Sita Sharma)
INSERT INTO jobs (title, description, budget, client_id, image, category) VALUES
('E-commerce Website', 'Full website with payment integration', 50000.00, 3, 'ecom.jpg', 'Web Development'),
('Logo Design', 'Modern and creative logo for new brand', 5000.00, 3, 'logo.jpg', 'Graphic Design'),
('Mobile App', 'Food delivery app with tracking', 80000.00, 3, 'app.jpg', 'Mobile App');