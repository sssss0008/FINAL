CREATE DATABASE kamp;
USE kamp;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100),
    role ENUM('client','freelancer','admin'),
    skills TEXT,
    profile_pic VARCHAR(255) DEFAULT 'default.jpg'
);

CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    budget DECIMAL(10,2),
    client_id INT,
    image VARCHAR(255),
    category VARCHAR(100),
    status ENUM('open','awarded','completed') DEFAULT 'open',
    awarded_to INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    freelancer_id INT,
    amount DECIMAL(10,2),
    proposal TEXT,
    status ENUM('pending','accepted','paid') DEFAULT 'pending'
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    job_id INT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bid_id INT,
    transaction_uuid VARCHAR(100),
    amount DECIMAL(10,2),
    status VARCHAR(20),
    ref_id VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name,email,password,role,skills) VALUES
('Admin','admin@kamp.com','admin123','admin',''),
('Ram Bahadur','ram@kamp.com','ram123','freelancer','PHP, Laravel, JavaScript'),
('Sita Sharma','sita@kamp.com','sita123','client',''),
('Hari Prasad','hari@kamp.com','hari123','freelancer','React, Node.js'),
('Gita Devi','gita@kamp.com','gita123','freelancer','Graphic Design');

INSERT INTO jobs (title,description,budget,client_id,image,category) VALUES
('E-commerce Website','Full website with payment',50000,3,'ecom.jpg','Web Development'),
('Logo Design','Modern logo',5000,3,'logo.jpg','Graphic Design'),
('Mobile App','Delivery app',80000,3,'app.jpg','Mobile App');