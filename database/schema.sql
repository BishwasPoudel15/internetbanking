-- Event Management System Database Schema

CREATE DATABASE IF NOT EXISTS event_management;
USE event_management;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events Table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    venue VARCHAR(200) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    duration VARCHAR(50),
    max_attendees INT DEFAULT 0,
    current_attendees INT DEFAULT 0,
    image VARCHAR(255),
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmed', 'cancelled', 'attended') DEFAULT 'confirmed',
    notes TEXT,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking (event_id, user_id)
);

-- Insert Default Admin User (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Default Categories
INSERT INTO categories (name, description) VALUES 
('Conference', 'Professional conferences and seminars'),
('Workshop', 'Hands-on training and workshops'),
('Seminar', 'Educational seminars and talks'),
('Meetup', 'Casual meetups and networking events'),
('Concert', 'Music concerts and performances'),
('Sports', 'Sports events and competitions'),
('Exhibition', 'Art exhibitions and showcases'),
('Other', 'Other types of events');

-- Insert Sample Events
INSERT INTO events (title, description, category_id, venue, event_date, event_time, duration, max_attendees, created_by) VALUES 
('Tech Conference 2025', 'Annual technology conference featuring latest innovations', 1, 'Convention Center, Hall A', '2025-12-15', '09:00:00', '8 hours', 500, 1),
('Web Development Workshop', 'Learn modern web development with React and Node.js', 2, 'Tech Hub, Room 201', '2025-11-25', '14:00:00', '4 hours', 50, 1),
('Digital Marketing Seminar', 'Master digital marketing strategies for 2025', 3, 'Business Center, Auditorium', '2025-11-30', '10:00:00', '3 hours', 200, 1);
