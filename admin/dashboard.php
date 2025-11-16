<?php
$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';

requireLogin();
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total events
$query = "SELECT COUNT(*) as total FROM events";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_events'] = $stmt->fetch()['total'];

// Upcoming events
$query = "SELECT COUNT(*) as total FROM events WHERE status = 'upcoming' AND event_date >= CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['upcoming_events'] = $stmt->fetch()['total'];

// Total users
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_users'] = $stmt->fetch()['total'];

// Total bookings
$query = "SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_bookings'] = $stmt->fetch()['total'];

// Recent events
$query = "SELECT e.*, c.name as category_name FROM events e 
          LEFT JOIN categories c ON e.category_id = c.id 
          ORDER BY e.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_events = $stmt->fetchAll();
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
        <a href="events.php" class="btn btn-primary">
            <i class="fas fa-calendar-plus"></i> Manage Events
        </a>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Events</h6>
                            <h2 class="mb-0"><?php echo $stats['total_events']; ?></h2>
                        </div>
                        <div class="stats-icon text-primary">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Upcoming Events</h6>
                            <h2 class="mb-0"><?php echo $stats['upcoming_events']; ?></h2>
                        </div>
                        <div class="stats-icon text-success">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Users</h6>
                            <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                        </div>
                        <div class="stats-icon text-info">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Bookings</h6>
                            <h2 class="mb-0"><?php echo $stats['total_bookings']; ?></h2>
                        </div>
                        <div class="stats-icon text-warning">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Events -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Recent Events</h5>
        </div>
        <div class="card-body">
            <?php if (count($recent_events) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Attendees</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($event['category_name']); ?></span></td>
                                    <td><?php echo formatDate($event['event_date']); ?></td>
                                    <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                    <td><?php echo $event['current_attendees']; ?>/<?php echo $event['max_attendees']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $event['status'] === 'upcoming' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($event['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view-attendees.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-users"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No events found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
