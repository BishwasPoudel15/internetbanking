<?php
$pageTitle = 'Add Event';
require_once '../includes/header.php';

requireLogin();
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $venue = sanitize($_POST['venue']);
    $event_date = sanitize($_POST['event_date']);
    $event_time = sanitize($_POST['event_time']);
    $duration = sanitize($_POST['duration']);
    $max_attendees = (int)$_POST['max_attendees'];
    $status = sanitize($_POST['status']);
    
    if (empty($title) || empty($description) || empty($venue) || empty($event_date) || empty($event_time)) {
        $error = 'Please fill in all required fields.';
    } else {
        $image = null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadImage($_FILES['image']);
            if ($image === false) {
                $error = 'Failed to upload image. Please check file type and size.';
            }
        }
        
        if (empty($error)) {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "INSERT INTO events (title, description, category_id, venue, event_date, event_time, duration, max_attendees, image, status, created_by) 
                      VALUES (:title, :description, :category_id, :venue, :event_date, :event_time, :duration, :max_attendees, :image, :status, :created_by)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':venue', $venue);
            $stmt->bindParam(':event_date', $event_date);
            $stmt->bindParam(':event_time', $event_time);
            $stmt->bindParam(':duration', $duration);
            $stmt->bindParam(':max_attendees', $max_attendees);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':created_by', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Event added successfully!';
            } else {
                $error = 'Failed to add event. Please try again.';
            }
        }
    }
}

$categories = getAllCategories();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar-plus"></i> Add New Event</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <a href="events.php">View all events</a>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Event Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="venue" class="form-label">Venue *</label>
                                <input type="text" class="form-control" id="venue" name="venue" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="event_time" class="form-label">Event Time *</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="duration" class="form-label">Duration</label>
                                <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g., 2 hours">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="max_attendees" class="form-label">Max Attendees *</label>
                                <input type="number" class="form-control" id="max_attendees" name="max_attendees" min="1" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Event Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                <small class="text-muted">Max size: 5MB. Formats: JPG, PNG, GIF</small>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <img id="imagePreview" src="" alt="Preview" style="display:none; max-width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="events.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
