-- Database Schema for Band Cafe Application

-- Users table (stores user accounts and roles)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user'
);

-- Practice Requests table (stores practice session requests)
CREATE TABLE practice_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    transport_to_venue TINYINT(1) NOT NULL DEFAULT 0, -- 0 = No, 1 = Yes
    transport_to_home TINYINT(1) NOT NULL DEFAULT 0, -- 0 = No, 1 = Yes
    pickup_time TIME,
    pickup_address VARCHAR(255),
    dropoff_time TIME,
    dropoff_address VARCHAR(255),
    target_goal VARCHAR(255),
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);
