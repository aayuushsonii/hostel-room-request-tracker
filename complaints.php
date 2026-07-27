<?php
require_once 'config.php';

// Enforce Student Login Protection
require_student_login();

$msg_success = '';
$msg_error = '';
$generated_ticket = '';

$user_name = $_SESSION['student_name'] ?? '';
$user_roll = $_SESSION['roll_number'] ?? '';

// Submit Complaint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = sanitize($_POST['student_name'] ?? '');
    $roll_number  = sanitize($_POST['roll_number'] ?? '');
    $room_number  = sanitize($_POST['room_number'] ?? '');
    $category     = sanitize($_POST['category'] ?? 'Plumbing');
    $description  = sanitize($_POST['description'] ?? '');

    if (!empty($student_name) && !empty($roll_number) && !empty($room_number) && !empty($description)) {
        $generated_ticket = generateTicketId();

        if (isset($pdo)) {
            try {
                $sql = "INSERT INTO complaints (ticket_id, student_name, roll_number, room_number, category, description, status)
                        VALUES (:tkt, :name, :roll, :room, :cat, :desc, 'Open')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':tkt'  => $generated_ticket,
                    ':name' => $student_name,
                    ':roll' => $roll_number,
                    ':room' => $room_number,
                    ':cat'  => $category,
                    ':desc' => $description
                ]);
                $msg_success = "Complaint registered! Ticket ID: <strong>{$generated_ticket}</strong>";
            } catch (Exception $e) {
                $msg_error = "Database Error: " . $e->getMessage();
            }
        } else {
            $msg_success = "Complaint registered! Ticket ID: <strong>{$generated_ticket}</strong>";
        }
    } else {
        $msg_error = "Please fill in all mandatory fields.";
    }
}

// Fetch Complaint History
$complaints_list = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM complaints ORDER BY id DESC LIMIT 10");
        $complaints_list = $stmt->fetchAll();
    } catch (Exception $e) {}
}

if (empty($complaints_list)) {
    $complaints_list = [
        ['ticket_id' => 'TKT-901', 'student_name' => 'Rahul Sharma', 'roll_number' => '2023CS105', 'room_number' => 'A-101', 'category' => 'Electrical', 'description' => 'AC remote fan speed control button not functioning.', 'status' => 'In Progress', 'created_at' => date('Y-m-d')],
        ['ticket_id' => 'TKT-902', 'student_name' => 'Vikas Patel', 'roll_number' => '2022CS089', 'room_number' => 'A-201', 'category' => 'Wi-Fi', 'description' => 'Wi-Fi router on 2nd floor disconnecting frequently.', 'status' => 'Open', 'created_at' => date('Y-m-d')]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Complaints | Campus Stay</title>
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
                <li><a href="track_status.php" class="nav-link">Track Status</a></li>
                <li><a href="complaints.php" class="nav-link active">Complaints</a></li>
                <li style="display: flex; align-items: center; gap: 0.75rem; margin-left: 0.5rem;">
                    <span style="color: var(--accent-cyan); font-weight: 600; font-size: 0.9rem;">
                        <i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($user_name); ?>
                    </span>
                    <a href="logout.php" class="btn btn-sm btn-secondary" title="Logout Account">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </li>
                <li><a href="admin_dashboard.php" class="nav-link btn-nav-primary"><i class="fa-solid fa-user-shield"></i> Warden Admin</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 2rem;">

        <?php if ($msg_success): ?>
            <div class="alert alert-success alert-dismissible">
                <i class="fa-solid fa-check-circle" style="font-size: 1.5rem;"></i>
                <div>
                    <h4>Ticket Raised Successfully!</h4>
                    <p><?= $msg_success; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($msg_error): ?>
            <div class="alert alert-danger alert-dismissible">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($msg_error); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 2rem;">
            
            <!-- Submit Ticket Card -->
            <div class="glass-card">
                <div class="section-title" style="margin-bottom: 1.5rem;">
                    <h2><i class="fa-solid fa-wrench" style="color: var(--accent-amber);"></i> Raise Maintenance Ticket</h2>
                    <p>Report electrical, plumbing, Wi-Fi or room repair issues.</p>
                </div>

                <form action="complaints.php" method="POST">
                    <div class="form-group">
                        <label class="form-label">Student Name *</label>
                        <input type="text" name="student_name" class="form-control" value="<?= htmlspecialchars($user_name); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Roll Number *</label>
                            <input type="text" name="roll_number" class="form-control" value="<?= htmlspecialchars($user_roll); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Room Number *</label>
                            <input type="text" name="room_number" class="form-control" placeholder="e.g. A-101" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Issue Category *</label>
                        <select name="category" class="form-control" required>
                            <option value="Electrical">Electrical (Lights, Fan, Switch)</option>
                            <option value="Plumbing">Plumbing (Tap, Basin, Flush)</option>
                            <option value="Wi-Fi">Wi-Fi & Internet</option>
                            <option value="Furniture">Furniture (Bed, Table, Cupboard)</option>
                            <option value="Cleaning">Cleaning & Hygiene</option>
                            <option value="Other">Other Issues</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Issue Description *</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the problem in detail..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-paper-plane"></i> Submit Maintenance Ticket
                    </button>
                </form>
            </div>

            <!-- Recent Tickets Table -->
            <div class="glass-card">
                <div class="section-title" style="margin-bottom: 1.5rem;">
                    <h2><i class="fa-solid fa-list-check" style="color: var(--accent-cyan);"></i> Active Tickets Log</h2>
                    <p>Track recent maintenance complaints</p>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Room</th>
                                <th>Category</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints_list as $tkt): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($tkt['ticket_id']); ?></strong></td>
                                    <td><?= htmlspecialchars($tkt['room_number']); ?></td>
                                    <td><?= htmlspecialchars($tkt['category']); ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower(str_replace(' ', '', $tkt['status'])); ?>">
                                            <?= htmlspecialchars($tkt['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

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
