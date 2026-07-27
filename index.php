<?php
require_once 'config.php';

// Enforce Student Login Protection
require_student_login();

// Fetch Statistics safely
$total_rooms = 0;
$available_beds = 0;
$pending_requests = 0;
$resolved_complaints = 0;
$featured_rooms = [];

if (isset($pdo)) {
    try {
        $total_rooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
        $available_beds = $pdo->query("SELECT SUM(total_beds - occupied_beds) FROM rooms WHERE status = 'Available'")->fetchColumn() ?: 0;
        $pending_requests = $pdo->query("SELECT COUNT(*) FROM room_requests WHERE status = 'Pending'")->fetchColumn();
        $resolved_complaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Resolved'")->fetchColumn();

        $stmt = $pdo->query("SELECT * FROM rooms ORDER BY id DESC LIMIT 6");
        $featured_rooms = $stmt->fetchAll();
    } catch (Exception $e) {}
}

if (empty($featured_rooms)) {
    $total_rooms = 12;
    $available_beds = 18;
    $pending_requests = 4;
    $resolved_complaints = 15;
    $featured_rooms = [
        ['room_number' => 'A-101', 'block_name' => 'Block A (Boys)', 'floor' => 1, 'room_type' => 'Single', 'air_conditioned' => 1, 'total_beds' => 1, 'occupied_beds' => 0, 'monthly_rent' => 8500, 'status' => 'Available'],
        ['room_number' => 'A-102', 'block_name' => 'Block A (Boys)', 'floor' => 1, 'room_type' => 'Double', 'air_conditioned' => 1, 'total_beds' => 2, 'occupied_beds' => 1, 'monthly_rent' => 6500, 'status' => 'Available'],
        ['room_number' => 'B-101', 'block_name' => 'Block B (Girls)', 'floor' => 1, 'room_type' => 'Single', 'air_conditioned' => 1, 'total_beds' => 1, 'occupied_beds' => 0, 'monthly_rent' => 9000, 'status' => 'Available'],
        ['room_number' => 'B-102', 'block_name' => 'Block B (Girls)', 'floor' => 1, 'room_type' => 'Double', 'air_conditioned' => 0, 'total_beds' => 2, 'occupied_beds' => 1, 'monthly_rent' => 6000, 'status' => 'Available']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Stay | Hostel Room Requirement & Allocation Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand-logo">
                <i class="fa-solid fa-building-user"></i> CampusStay
            </a>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li><a href="rooms.php" class="nav-link">Rooms</a></li>
                <li><a href="request_room.php" class="nav-link">Request Room</a></li>
                <li><a href="track_status.php" class="nav-link">Track Status</a></li>
                <li><a href="complaints.php" class="nav-link">Complaints</a></li>
                <li style="display: flex; align-items: center; gap: 0.75rem; margin-left: 0.5rem;">
                    <span style="color: var(--accent-cyan); font-weight: 600; font-size: 0.9rem;">
                        <i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?>
                    </span>
                    <a href="logout.php" class="btn btn-sm btn-secondary" title="Logout Account">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </li>
                <li><a href="admin_dashboard.php" class="nav-link btn-nav-primary" title="Warden Portal"><i class="fa-solid fa-user-shield"></i> Warden Admin</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section container">
        <div class="hero-badge">
            <i class="fa-solid fa-user-check"></i> Welcome, <?= htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?>
        </div>
        <h1 class="hero-title">
            Smart & Transparent <span>Hostel Room Tracker</span>
        </h1>
        <p class="hero-subtitle">
            Find available rooms, submit your hostel allocation requirement, track real-time application approval status, and manage complaints with ease.
        </p>
        <div class="hero-buttons">
            <a href="request_room.php" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Apply for Room Allocation
            </a>
            <a href="track_status.php" class="btn btn-secondary">
                <i class="fa-solid fa-magnifying-glass"></i> Track Request Status
            </a>
        </div>
    </header>

    <!-- Live Statistics Cards -->
    <div class="container">
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon indigo">
                    <i class="fa-solid fa-bed"></i>
                </div>
                <div class="stat-info">
                    <h3><?= htmlspecialchars($total_rooms); ?></h3>
                    <p>Total Hostel Rooms</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon emerald">
                    <i class="fa-solid fa-door-open"></i>
                </div>
                <div class="stat-info">
                    <h3><?= htmlspecialchars($available_beds); ?></h3>
                    <p>Available Beds</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="stat-info">
                    <h3><?= htmlspecialchars($pending_requests); ?></h3>
                    <p>Pending Applications</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="stat-info">
                    <h3><?= htmlspecialchars($resolved_complaints); ?></h3>
                    <p>Resolved Support Tickets</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Rooms Section -->
    <main class="container">
        <div class="section-header">
            <div class="section-title">
                <h2>Featured Rooms</h2>
                <p>Browse available room options and facilities</p>
            </div>
            <a href="rooms.php" class="btn btn-secondary">View All Rooms <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="room-grid">
            <?php foreach ($featured_rooms as $room): ?>
                <div class="room-card">
                    <div class="room-card-header">
                        <span class="room-number"><i class="fa-solid fa-key"></i> <?= htmlspecialchars($room['room_number']); ?></span>
                        <span class="badge badge-<?= strtolower($room['status']); ?>">
                            <?= htmlspecialchars($room['status']); ?>
                        </span>
                    </div>
                    <div class="room-card-body">
                        <ul class="room-features">
                            <li class="room-feature">
                                <i class="fa-solid fa-building"></i> <?= htmlspecialchars($room['block_name']); ?> (Floor <?= htmlspecialchars($room['floor']); ?>)
                            </li>
                            <li class="room-feature">
                                <i class="fa-solid fa-user-group"></i> <?= htmlspecialchars($room['room_type']); ?> Bed Occupancy
                            </li>
                            <li class="room-feature">
                                <i class="fa-solid fa-snowflake"></i> <?= $room['air_conditioned'] ? 'Air Conditioned (AC)' : 'Non-AC Room'; ?>
                            </li>
                            <li class="room-feature">
                                <i class="fa-solid fa-bed"></i> Available: <?= htmlspecialchars($room['total_beds'] - $room['occupied_beds']); ?> / <?= htmlspecialchars($room['total_beds']); ?> Beds
                            </li>
                        </ul>
                        <div class="room-price">
                            ₹<?= number_format($room['monthly_rent'], 2); ?> <span>/ month</span>
                        </div>
                    </div>
                    <div class="room-card-footer">
                        <a href="request_room.php?pref_room=<?= urlencode($room['room_type']); ?>&ac=<?= $room['air_conditioned']; ?>" class="btn btn-primary btn-block">
                            Select Preference
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y'); ?> CampusStay Hostel Room Required & Allocation Tracker. Built for College Project.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
