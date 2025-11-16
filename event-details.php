<?php
$pageTitle = 'Event Details';
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$eventId = (int)$_GET['id'];
$event = getEventById($eventId);

if (!$event) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';

// Check if user has already booked
$hasBooked = false;
if (isLoggedIn()) {
    $hasBooked = hasUserBookedEvent($_SESSION['user_id'], $eventId);
}

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_event'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    if ($hasBooked) {
        $message = 'You have already booked this event.';
        $messageType = 'warning';
    } elseif ($event['current_attendees'] >= $event['max_attendees']) {
        $message = 'Sorry, this event is fully booked.';
        $messageType = 'danger';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Create booking
            $query = "INSERT INTO bookings (event_id, user_id, status) VALUES (:event_id, :user_id, 'confirmed')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            // Update event attendees count
            $query = "UPDATE events SET current_attendees = current_attendees + 1 WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $eventId);
            $stmt->execute();
            
            $db->commit();
            
            $message = 'Event booked successfully!';
            $messageType = 'success';
            $hasBooked = true;
            
            // Refresh event data
            $event = getEventById($eventId);
        } catch (Exception $e) {
            $db->rollBack();
            $message = 'Booking failed. Please try again.';
            $messageType = 'danger';
        }
    }
}
?>

<div class="container my-5">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <?php if ($event['image']): ?>
                <img src="<?php echo htmlspecialchars($event['image']); ?>" class="event-detail-image mb-4" alt="<?php echo htmlspecialchars($event['title']); ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/800x400?text=Event+Image" class="event-detail-image mb-4" alt="Event">
            <?php endif; ?>
            
            <h1 class="mb-3"><?php echo htmlspecialchars($event['title']); ?></h1>
            
            <div class="mb-4">
                <span class="badge bg-primary me-2"><?php echo htmlspecialchars($event['category_name']); ?></span>
                <span class="badge bg-<?php echo $event['status'] === 'upcoming' ? 'success' : 'secondary'; ?>">
                    <?php echo ucfirst($event['status']); ?>
                </span>
            </div>
            
            <h4>About This Event</h4>
            <p class="lead"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Event Information</h5>
                    
                    <div class="info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <strong>Date</strong><br>
                            <?php echo formatDate($event['event_date']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Time</strong><br>
                            <?php echo formatTime($event['event_time']); ?>
                        </div>
                    </div>
                    
                    <?php if ($event['duration']): ?>
                        <div class="info-item">
                            <i class="fas fa-hourglass-half"></i>
                            <div>
                                <strong>Duration</strong><br>
                                <?php echo htmlspecialchars($event['duration']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Venue</strong><br>
                            <?php echo htmlspecialchars($event['venue']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <strong>Attendees</strong><br>
                            <?php echo $event['current_attendees']; ?> / <?php echo $event['max_attendees']; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if ($hasBooked): ?>
                            <button class="btn btn-success w-100" disabled>
                                <i class="fas fa-check-circle"></i> Already Booked
                            </button>
                        <?php elseif ($event['current_attendees'] >= $event['max_attendees']): ?>
                            <button class="btn btn-danger w-100" disabled>
                                <i class="fas fa-times-circle"></i> Fully Booked
                            </button>
                        <?php else: ?>
                            <form method="POST" action="">
                                <button type="submit" name="book_event" class="btn btn-primary w-100">
                                    <i class="fas fa-ticket-alt"></i> Book This Event
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt"></i> Login to Book
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Events
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
