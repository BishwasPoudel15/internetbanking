<?php
$pageTitle = 'My Bookings';
require_once '../includes/header.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Handle cancellation
if (isset($_GET['cancel'])) {
    $bookingId = (int)$_GET['cancel'];
    
    // Verify booking belongs to user
    $query = "SELECT event_id FROM bookings WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $bookingId);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $booking = $stmt->fetch();
        
        try {
            $db->beginTransaction();
            
            // Update booking status
            $query = "UPDATE bookings SET status = 'cancelled' WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $bookingId);
            $stmt->execute();
            
            // Decrease event attendees count
            $query = "UPDATE events SET current_attendees = current_attendees - 1 WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $booking['event_id']);
            $stmt->execute();
            
            $db->commit();
            
            $message = 'Booking cancelled successfully.';
            $messageType = 'success';
        } catch (Exception $e) {
            $db->rollBack();
            $message = 'Failed to cancel booking.';
            $messageType = 'danger';
        }
    }
}

// Get all user bookings
$query = "SELECT b.*, e.title, e.description, e.venue, e.event_date, e.event_time, e.image, c.name as category_name 
          FROM bookings b 
          JOIN events e ON b.event_id = e.id 
          LEFT JOIN categories c ON e.category_id = c.id 
          WHERE b.user_id = :user_id 
          ORDER BY e.event_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$bookings = $stmt->fetchAll();
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-list"></i> My Bookings</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if (count($bookings) > 0): ?>
        <div class="row g-4">
            <?php foreach ($bookings as $booking): ?>
                <div class="col-md-12">
                    <div class="card booking-card <?php echo $booking['status'] === 'cancelled' ? 'cancelled' : ''; ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <?php if ($booking['image']): ?>
                                        <img src="../<?php echo htmlspecialchars($booking['image']); ?>" class="img-fluid rounded" alt="Event">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/150" class="img-fluid rounded" alt="Event">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="mb-2">
                                        <?php echo htmlspecialchars($booking['title']); ?>
                                        <?php if ($booking['status'] === 'cancelled'): ?>
                                            <span class="badge bg-danger ms-2">Cancelled</span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="text-muted mb-2"><?php echo substr(htmlspecialchars($booking['description']), 0, 150); ?>...</p>
                                    <p class="mb-1">
                                        <i class="fas fa-calendar text-primary"></i> 
                                        <strong>Date:</strong> <?php echo formatDate($booking['event_date']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-clock text-success"></i> 
                                        <strong>Time:</strong> <?php echo formatTime($booking['event_time']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt text-danger"></i> 
                                        <strong>Venue:</strong> <?php echo htmlspecialchars($booking['venue']); ?>
                                    </p>
                                    <small class="text-muted">
                                        Booked on: <?php echo date('M j, Y g:i A', strtotime($booking['booking_date'])); ?>
                                    </small>
                                </div>
                                <div class="col-md-3 text-end">
                                    <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($booking['category_name']); ?></span><br>
                                    <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'attended' ? 'primary' : 'danger'); ?> mb-3">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span><br>
                                    <a href="../event-details.php?id=<?php echo $booking['event_id']; ?>" class="btn btn-sm btn-primary mb-2 w-100">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <?php if ($booking['status'] === 'confirmed' && strtotime($booking['event_date']) >= strtotime('today')): ?>
                                        <a href="?cancel=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger w-100" 
                                           onclick="return confirmDelete('Are you sure you want to cancel this booking?')">
                                            <i class="fas fa-times"></i> Cancel Booking
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You don't have any bookings yet. <a href="../index.php">Browse events</a> to book your first event!
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
