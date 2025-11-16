<?php
$pageTitle = 'Manage Events';
require_once '../includes/header.php';

requireLogin();
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $query = "DELETE FROM events WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $deleteId);
    
    if ($stmt->execute()) {
        $message = 'Event deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to delete event.';
        $messageType = 'danger';
    }
}

// Get all events
$query = "SELECT e.*, c.name as category_name FROM events e 
          LEFT JOIN categories c ON e.category_id = c.id 
          ORDER BY e.event_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll();
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar"></i> Manage Events</h2>
        <a href="add-event.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Event
        </a>
    </div>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <?php if (count($events) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date & Time</th>
                                <th>Venue</th>
                                <th>Attendees</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo $event['id']; ?></td>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($event['category_name']); ?></span></td>
                                    <td>
                                        <?php echo formatDate($event['event_date']); ?><br>
                                        <small class="text-muted"><?php echo formatTime($event['event_time']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                    <td><?php echo $event['current_attendees']; ?>/<?php echo $event['max_attendees']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $event['status'] === 'upcoming' ? 'success' : 
                                                ($event['status'] === 'ongoing' ? 'primary' : 
                                                ($event['status'] === 'completed' ? 'secondary' : 'danger')); 
                                        ?>">
                                            <?php echo ucfirst($event['status']); ?>
                                        </span>
                                    </td>
                                    <td class="table-actions">
                                        <a href="../event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view-attendees.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary" title="Attendees">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a href="?delete=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirmDelete('Are you sure you want to delete this event?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No events found. <a href="add-event.php">Add your first event</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
