<?php
require_once 'config.php';

// Enforce Student Login Protection
require_student_login();

$rooms = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
        $rooms = $stmt->fetchAll();
    } catch (Exception $e) {}
}

if (empty($rooms)) {
    $rooms = [
        ['room_number' => 'A-101', 'block_name' => 'Block A (Boys)', 'floor' => 1, 'room_type' => 'Single', 'air_conditioned' => 1, 'total_beds' => 1, 'occupied_beds' => 0, 'monthly_rent' => 8500, 'status' => 'Available'],
        ['room_number' => 'A-102', 'block_name' => 'Block A (Boys)', 'floor' => 1, 'room_type' => 'Double', 'air_conditioned' => 1, 'total_beds' => 2, 'occupied_beds' => 1, 'monthly_rent' => 6500, 'status' => 'Available'],
        ['room_number' => 'A-201', 'block_name' => 'Block A (Boys)', 'floor' => 2, 'room_type' => 'Triple', 'air_conditioned' => 0, 'total_beds' => 3, 'occupied_beds' => 3, 'monthly_rent' => 4500, 'status' => 'Full'],
        ['room_number' => 'B-101', 'block_name' => 'Block B (Girls)', 'floor' => 1, 'room_type' => 'Single', 'air_conditioned' => 1, 'total_beds' => 1, 'occupied_beds' => 0, 'monthly_rent' => 9000, 'status' => 'Available'],
        ['room_number' => 'B-102', 'block_name' => 'Block B (Girls)', 'floor' => 1, 'room_type' => 'Double', 'air_conditioned' => 0, 'total_beds' => 2, 'occupied_beds' => 1, 'monthly_rent' => 6000, 'status' => 'Available'],
        ['room_number' => 'B-205', 'block_name' => 'Block B (Girls)', 'floor' => 2, 'room_type' => 'Double', 'air_conditioned' => 1, 'total_beds' => 2, 'occupied_beds' => 0, 'monthly_rent' => 7000, 'status' => 'Maintenance']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Hostel Rooms | Campus Stay</title>
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
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="rooms.php" class="nav-link active">Rooms</a></li>
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
                <li><a href="admin_dashboard.php" class="nav-link btn-nav-primary"><i class="fa-solid fa-user-shield"></i> Warden Admin</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="section-header" style="margin-top: 1rem;">
            <div class="section-title">
                <h2>Browse Hostel Rooms</h2>
                <p>Filter by occupancy, AC preferences, or search block/room number</p>
            </div>
            <a href="request_room.php" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Apply for Room</a>
        </div>

        <!-- Filter Controls -->
        <div class="glass-card" style="margin-bottom: 2rem; padding: 1.25rem;">
            <div class="form-row">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label"><i class="fa-solid fa-magnifying-glass"></i> Search Room / Block</label>
                    <input type="text" id="searchRoom" class="form-control" placeholder="Search e.g. A-101 or Block B...">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label"><i class="fa-solid fa-users"></i> Room Type</label>
                    <select id="filterRoomType" class="form-control">
                        <option value="all">All Room Types</option>
                        <option value="single">Single Bed</option>
                        <option value="double">Double Bed</option>
                        <option value="triple">Triple Bed</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label"><i class="fa-solid fa-snowflake"></i> AC Facility</label>
                    <select id="filterAc" class="form-control">
                        <option value="all">All AC / Non-AC</option>
                        <option value="1">Air Conditioned (AC)</option>
                        <option value="0">Non-AC</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Room Cards List -->
        <div class="room-grid">
            <?php foreach ($rooms as $room): ?>
                <div class="room-card" data-type="<?= strtolower($room['room_type']); ?>" data-ac="<?= $room['air_conditioned']; ?>">
                    <div class="room-card-header">
                        <span class="room-number"><i class="fa-solid fa-door-closed"></i> <?= htmlspecialchars($room['room_number']); ?></span>
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
                                <i class="fa-solid fa-bed"></i> Type: <?= htmlspecialchars($room['room_type']); ?> Bed Occupancy
                            </li>
                            <li class="room-feature">
                                <i class="fa-solid fa-snowflake"></i> <?= $room['air_conditioned'] ? 'Air Conditioned' : 'Standard Non-AC'; ?>
                            </li>
                            <li class="room-feature">
                                <i class="fa-solid fa-users"></i> Capacity: <?= htmlspecialchars($room['total_beds'] - $room['occupied_beds']); ?> / <?= htmlspecialchars($room['total_beds']); ?> Available
                            </li>
                        </ul>
                        <div class="room-price">
                            ₹<?= number_format($room['monthly_rent'], 2); ?> <span>/ month</span>
                        </div>
                    </div>
                    <div class="room-card-footer">
                        <?php if ($room['status'] === 'Available'): ?>
                            <a href="request_room.php?pref_room=<?= urlencode($room['room_type']); ?>&ac=<?= $room['air_conditioned']; ?>" class="btn btn-primary btn-block">
                                <i class="fa-solid fa-check-circle"></i> Request This Room
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-block" disabled>
                                <?= $room['status'] === 'Full' ? 'Room Full' : 'Under Maintenance'; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y'); ?> CampusStay Hostel Room Required & Allocation Tracker. Built for College Project.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
