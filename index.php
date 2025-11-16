<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Build query
$query = "SELECT e.*, c.name as category_name 
          FROM events e 
          LEFT JOIN categories c ON e.category_id = c.id 
          WHERE e.status = 'upcoming' AND e.event_date >= CURDATE()";

if (!empty($search)) {
    $query .= " AND (e.title LIKE :search OR e.description LIKE :search OR e.venue LIKE :search)";
}

if (!empty($category)) {
    $query .= " AND e.category_id = :category";
}

$query .= " ORDER BY e.event_date ASC, e.event_time ASC";

$stmt = $db->prepare($query);

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bindParam(':search', $searchParam);
}

if (!empty($category)) {
    $stmt->bindParam(':category', $category);
}

$stmt->execute();
$events = $stmt->fetchAll();

// Get all categories for filter
$categories = getAllCategories();
?>

<div class="hero-section">
    <div class="container text-center">
        <h1><i class="fas fa-calendar-check"></i> Welcome to Event Management System</h1>
        <p class="lead">Discover and book amazing events happening around you</p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-light btn-lg me-2">
                <i class="fas fa-user-plus"></i> Get Started
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="container mb-5">
    <!-- Search and Filter Section -->
    <div class="filter-section">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Events Grid -->
    <h2 class="mb-4">Upcoming Events</h2>
    
    <?php if (count($events) > 0): ?>
        <div class="row g-4">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card event-card" data-category="<?php echo $event['category_id']; ?>">
                        <?php if ($event['image']): ?>
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x200?text=Event+Image" class="card-img-top" alt="Event">
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <span class="badge bg-primary badge-category"><?php echo htmlspecialchars($event['category_name']); ?></span>
                            </div>
                            <p class="card-text text-muted">
                                <?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...
                            </p>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> <?php echo formatDate($event['event_date']); ?>
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> <?php echo formatTime($event['event_time']); ?>
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['venue']); ?>
                                </small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> 
                                    <?php echo $event['current_attendees']; ?>/<?php echo $event['max_attendees']; ?> Attendees
                                </small>
                                <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No upcoming events found. Please check back later!
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
