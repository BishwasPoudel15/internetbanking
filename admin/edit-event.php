<?php
$pageTitle = 'Edit Event';
require_once '../includes/header.php';

requireLogin();
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$eventId = (int)$_GET['id'];
$event = getEventById($eventId);

if (!$event) {
    header('Location: events.php');
    exit();
}

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
        $image = $event['image'];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImage = uploadImage($_FILES['image']);
            if ($newImage !== false) {
                $image = $newImage;
            }
        }
        
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE events SET title = :title, description = :description, category_id = :category_id, 
                  venue = :venue, event_date = :event_date, event_time = :event_time, duration = :duration, 
                  max_attendees = :max_attendees, image = :image, status = :status 
                  WHERE id = :id";
        
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
        $stmt->bindParam(':id', $eventId);
        
        if ($stmt->execute()) {
            $success = 'Event updated successfully!';
            $event = getEventById($eventId); // Refresh data
        } else {
            $error = 'Failed to update event. Please try again.';
        }
    }
}

$categories = getAllCategories();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Event</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Event Title *</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $event['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="upcoming" <?php echo $event['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="ongoing" <?php echo $event['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="completed" <?php echo $event['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $event['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="venue" class="form-label">Venue *</label>
                                <input type="text" class="form-control" id="venue" name="venue" value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="event_time" class="form-label">Event Time *</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" value="<?php echo $event['event_time']; ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="duration" class="form-label">Duration</label>
                                <input type="text" class="form-control" id="duration" name="duration" value="<?php echo htmlspecialchars($event['duration']); ?>" placeholder="e.g., 2 hours">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="max_attendees" class="form-label">Max Attendees *</label>
                                <input type="number" class="form-control" id="max_attendees" name="max_attendees" value="<?php echo $event['max_attendees']; ?>" min="1" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Event Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                <small class="text-muted">Max size: 5MB. Formats: JPG, PNG, GIF</small>
                            </div>
                            
                            <?php if ($event['image']): ?>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Current Image</label><br>
                                    <img src="../<?php echo htmlspecialchars($event['image']); ?>" alt="Current" style="max-width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="col-md-12 mb-3">
                                <img id="imagePreview" src="" alt="Preview" style="display:none; max-width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="events.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
