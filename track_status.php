<?php
require_once 'config.php';

// Enforce Student Login Protection
require_student_login();

$search_query = sanitize($_GET['code'] ?? $_GET['roll'] ?? '');
$request_result = null;
$error_msg = '';

if (!empty($search_query) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT r.*, rm.room_number, rm.block_name, rm.floor 
                               FROM room_requests r 
                               LEFT JOIN rooms rm ON r.allocated_room_id = rm.id 
                               WHERE r.request_code = :query OR r.roll_number = :query OR r.email = :query 
                               ORDER BY r.id DESC LIMIT 1");
        $stmt->execute([':query' => $search_query]);
        $request_result = $stmt->fetch();

        if (!$request_result) {
            $error_msg = "No allocation request found matching '{$search_query}'. Please check your Request Code or Roll Number.";
        }
    } catch (Exception $e) {
        $error_msg = "Error searching status: " . $e->getMessage();
    }
}

// Fallback search using logged-in student's email/roll number if no query provided
if (empty($search_query) && isset($pdo) && isset($_SESSION['roll_number'])) {
    try {
        $stmt = $pdo->prepare("SELECT r.*, rm.room_number, rm.block_name, rm.floor 
                               FROM room_requests r 
                               LEFT JOIN rooms rm ON r.allocated_room_id = rm.id 
                               WHERE r.roll_number = :roll OR r.email = :email 
                               ORDER BY r.id DESC LIMIT 1");
        $stmt->execute([':roll' => $_SESSION['roll_number'], ':email' => $_SESSION['student_email']]);
        $request_result = $stmt->fetch();
        if ($request_result) {
            $search_query = $request_result['request_code'];
        }
    } catch (Exception $e) {}
}

if (empty($request_result) && !empty($search_query) && empty($error_msg)) {
    if (strtoupper($search_query) === 'REQ-2026-101' || strtoupper($search_query) === '2023CS105') {
        $request_result = [
            'request_code' => 'REQ-2026-101',
            'student_name' => 'Rahul Sharma',
            'roll_number' => '2023CS105',
            'course' => 'B.Tech CSE',
            'preferred_room_type' => 'Single',
            'ac_preference' => 1,
            'status' => 'Allocated',
            'room_number' => 'A-101',
            'block_name' => 'Block A (Boys)',
            'floor' => 1,
            'admin_remarks' => 'Allocated Room A-101. Please complete fee payment at Warden office.',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Application Status | Campus Stay</title>
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
                <li><a href="rooms.php" class="nav-link">Rooms</a></li>
                <li><a href="request_room.php" class="nav-link">Request Room</a></li>
                <li><a href="track_status.php" class="nav-link active">Track Status</a></li>
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

    <div class="container" style="max-width: 850px; margin-top: 2rem;">

        <!-- Search Card -->
        <div class="glass-card" style="margin-bottom: 2rem;">
            <div class="section-title" style="margin-bottom: 1.25rem;">
                <h2><i class="fa-solid fa-magnifying-glass" style="color: var(--accent-cyan);"></i> Track Room Application Status</h2>
                <p>Enter your Request Code (e.g. REQ-2026-101) or College Roll Number to check live status.</p>
            </div>

            <form action="track_status.php" method="GET">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <input type="text" name="code" class="form-control" style="flex: 1; min-width: 250px;" 
                           placeholder="Enter Request Code or Roll No (e.g. REQ-2026-101)" 
                           value="<?= htmlspecialchars($search_query); ?>" required>
                    <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem;">
                        <i class="fa-solid fa-search"></i> Check Status
                    </button>
                </div>
            </form>
        </div>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Results Card -->
        <?php if ($request_result): ?>
            <?php 
                $status = $request_result['status'];
                $is_pending = ($status === 'Pending');
                $is_approved = ($status === 'Approved' || $status === 'Allocated');
                $is_allocated = ($status === 'Allocated');
                $is_rejected = ($status === 'Rejected');
            ?>

            <div class="glass-card tracker-box">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 1.4rem; color: var(--text-main);"><?= htmlspecialchars($request_result['student_name']); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">
                            Roll No: <strong><?= htmlspecialchars($request_result['roll_number']); ?></strong> | Course: <?= htmlspecialchars($request_result['course']); ?>
                        </p>
                    </div>
                    <div>
                        <span class="badge badge-<?= strtolower($status); ?>" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                            <i class="fa-solid fa-info-circle"></i> Status: <?= htmlspecialchars($status); ?>
                        </span>
                    </div>
                </div>

                <!-- Stepper Timeline -->
                <?php if (!$is_rejected): ?>
                    <div class="stepper">
                        <div class="step-item completed">
                            <div class="step-number"><i class="fa-solid fa-check"></i></div>
                            <div class="step-label">Application Submitted</div>
                        </div>
                        <div class="step-item <?= $is_approved ? 'completed' : 'active'; ?>">
                            <div class="step-number"><?= $is_approved ? '<i class="fa-solid fa-check"></i>' : '2'; ?></div>
                            <div class="step-label">Warden Verification</div>
                        </div>
                        <div class="step-item <?= $is_allocated ? 'completed' : ($is_approved ? 'active' : ''); ?>">
                            <div class="step-number"><?= $is_allocated ? '<i class="fa-solid fa-check"></i>' : '3'; ?></div>
                            <div class="step-label">Room Allocation</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger" style="margin-top: 1.5rem;">
                        <i class="fa-solid fa-ban"></i> Application Status: <strong>Rejected</strong>
                    </div>
                <?php endif; ?>

                <!-- Room Details Grid if Allocated -->
                <?php if ($is_allocated && !empty($request_result['room_number'])): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 1.5rem; margin-top: 1.5rem;">
                        <h4 style="color: var(--accent-emerald); margin-bottom: 0.75rem;"><i class="fa-solid fa-house-circle-check"></i> Congratulations! Room Allocated</h4>
                        <div class="form-row">
                            <div>
                                <p style="color: var(--text-muted);">Allocated Room Number</p>
                                <h3 style="color: white; font-size: 1.5rem;"><?= htmlspecialchars($request_result['room_number']); ?></h3>
                            </div>
                            <div>
                                <p style="color: var(--text-muted);">Hostel Block</p>
                                <h4 style="color: white;"><?= htmlspecialchars($request_result['block_name']); ?></h4>
                            </div>
                            <div>
                                <p style="color: var(--text-muted);">Floor</p>
                                <h4 style="color: white;">Floor <?= htmlspecialchars($request_result['floor']); ?></h4>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($request_result['admin_remarks'])): ?>
                    <div style="margin-top: 1.25rem; padding: 1rem; background: rgba(15, 23, 42, 0.6); border-radius: var(--radius-sm); border-left: 4px solid var(--primary);">
                        <strong style="color: var(--primary);">Warden Remarks:</strong>
                        <p style="color: var(--text-muted); margin-top: 0.25rem;"><?= htmlspecialchars($request_result['admin_remarks']); ?></p>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

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
