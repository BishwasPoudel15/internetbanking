<?php
$pageTitle = 'View Attendees';
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

$database = new Database();
$db = $database->getConnection();

// Get all bookings for this event
$query = "SELECT b.*, u.name, u.email, u.phone FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          WHERE b.event_id = :event_id 
          ORDER BY b.booking_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':event_id', $eventId);
$stmt->execute();
$bookings = $stmt->fetchAll();
?>

<div class="container my-5">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($event['title']); ?></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Date:</strong> <?php echo formatDate($event['event_date']); ?></p>
                    <p><strong>Time:</strong> <?php echo formatTime($event['event_time']); ?></p>
                    <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category_name']); ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-<?php echo $event['status'] === 'upcoming' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($event['status']); ?></span></p>
                    <p><strong>Attendees:</strong> <?php echo $event['current_attendees']; ?> / <?php echo $event['max_attendees']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-users"></i> Attendees List (<?php echo count($bookings); ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($booking['name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['phone'] ?: 'N/A'); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $booking['status'] === 'confirmed' ? 'success' : 
                                                ($booking['status'] === 'attended' ? 'primary' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No attendees yet for this event.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="events.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Events
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
