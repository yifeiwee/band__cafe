-- Database Schema for Band Cafe Application

-- Users table (stores user accounts and roles)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    instrument VARCHAR(50),
    section VARCHAR(50)
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

-- Practice Records table (stores attendance and points for completed practice sessions)
CREATE TABLE practice_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    attended TINYINT(1) NOT NULL DEFAULT 0, -- 0 = Not Confirmed, 1 = Confirmed by Admin
    points INT DEFAULT 0,
    FOREIGN KEY (request_id) REFERENCES practice_requests(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample data
-- Default admin user (password: admin123)
-- Test user 1 (password: 1234)  
-- Test user 2 (password: test123)
INSERT INTO users (username, password, role, instrument, section) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Conductor', 'Admin'),
('testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Trumpet', 'Brass'),
('jane_smith', '$2y$10$vWV0HE0lO3SRpQZ95.xtmu9/K7sGrfGZNOPfQ7JrF1mQ9dSlb22v.', 'user', 'Clarinet', 'Woodwind');
