<?php
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Format time
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

// Get user by ID
function getUserById($userId) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, name, email, phone, role FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    return $stmt->fetch();
}

// Get all categories
function getAllCategories() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM categories ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

// Get event by ID
function getEventById($eventId) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT e.*, c.name as category_name, u.name as creator_name 
              FROM events e 
              LEFT JOIN categories c ON e.category_id = c.id 
              LEFT JOIN users u ON e.created_by = u.id 
              WHERE e.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $eventId);
    $stmt->execute();
    
    return $stmt->fetch();
}

// Check if user has booked an event
function hasUserBookedEvent($userId, $eventId) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id FROM bookings WHERE user_id = :user_id AND event_id = :event_id AND status = 'confirmed'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':event_id', $eventId);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}

// Upload image
function uploadImage($file, $targetDir = 'assets/images/events/') {
    $targetDir = __DIR__ . '/../' . $targetDir;
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return 'assets/images/events/' . $newFileName;
    }
    
    return false;
}
?>
