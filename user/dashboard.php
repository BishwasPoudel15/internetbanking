<?php
$pageTitle = 'User Dashboard';
require_once '../includes/header.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get user's bookings count
$query = "SELECT COUNT(*) as total FROM bookings WHERE user_id = :user_id AND status = 'confirmed'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$totalBookings = $stmt->fetch()['total'];

// Get upcoming events count
$query = "SELECT COUNT(*) as total FROM events WHERE status = 'upcoming' AND event_date >= CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$upcomingEvents = $stmt->fetch()['total'];

// Get user's upcoming bookings
$query = "SELECT e.*, c.name as category_name, b.booking_date, b.status as booking_status 
          FROM bookings b 
          JOIN events e ON b.event_id = e.id 
          LEFT JOIN categories c ON e.category_id = c.id 
          WHERE b.user_id = :user_id AND e.event_date >= CURDATE() 
          ORDER BY e.event_date ASC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$myBookings = $stmt->fetchAll();
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card stats-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">My Bookings</h6>
                            <h2 class="mb-0"><?php echo $totalBookings; ?></h2>
                        </div>
                        <div class="stats-icon text-success">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card stats-card info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Available Events</h6>
                            <h2 class="mb-0"><?php echo $upcomingEvents; ?></h2>
                        </div>
                        <div class="stats-icon text-info">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="../index.php" class="text-decoration-none">
                <div class="card text-center h-100 border-primary">
                    <div class="card-body">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h5>Browse Events</h5>
                        <p class="text-muted">Discover upcoming events</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="my-bookings.php" class="text-decoration-none">
                <div class="card text-center h-100 border-success">
                    <div class="card-body">
                        <i class="fas fa-list fa-3x text-success mb-3"></i>
                        <h5>My Bookings</h5>
                        <p class="text-muted">View all your bookings</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="profile.php" class="text-decoration-none">
                <div class="card text-center h-100 border-info">
                    <div class="card-body">
                        <i class="fas fa-user fa-3x text-info mb-3"></i>
                        <h5>My Profile</h5>
                        <p class="text-muted">Update your information</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Upcoming Bookings -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> My Upcoming Events</h5>
        </div>
        <div class="card-body">
            <?php if (count($myBookings) > 0): ?>
                <div class="row g-3">
                    <?php foreach ($myBookings as $booking): ?>
                        <div class="col-md-12">
                            <div class="card booking-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-2"><?php echo htmlspecialchars($booking['title']); ?></h5>
                                            <p class="mb-1">
                                                <i class="fas fa-calendar text-primary"></i> 
                                                <?php echo formatDate($booking['event_date']); ?> at <?php echo formatTime($booking['event_time']); ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                                <?php echo htmlspecialchars($booking['venue']); ?>
                                            </p>
                                            <small class="text-muted">
                                                Booked on: <?php echo date('M j, Y', strtotime($booking['booking_date'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($booking['category_name']); ?></span><br>
                                            <a href="../event-details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="my-bookings.php" class="btn btn-primary">View All Bookings</a>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">You don't have any upcoming bookings. <a href="../index.php">Browse events</a> to get started!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
