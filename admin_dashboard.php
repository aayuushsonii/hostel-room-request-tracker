<?php
require_once 'config.php';

$action_msg = '';
$action_error = '';

// Handle Actions (Approve/Reject Request, Add Room, Update Complaint)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (isset($pdo)) {
        try {
            // 1. Process Room Allocation Request
            if ($action === 'update_request') {
                $req_id = (int)$_POST['request_id'];
                $status = sanitize($_POST['status']);
                $allocated_room_id = !empty($_POST['allocated_room_id']) ? (int)$_POST['allocated_room_id'] : null;
                $remarks = sanitize($_POST['admin_remarks'] ?? '');

                $sql = "UPDATE room_requests SET status = :status, allocated_room_id = :room_id, admin_remarks = :remarks WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':status'  => $status,
                    ':room_id' => $allocated_room_id,
                    ':remarks' => $remarks,
                    ':id'      => $req_id
                ]);

                // Update room occupancy if allocated
                if ($status === 'Allocated' && $allocated_room_id) {
                    $pdo->exec("UPDATE rooms SET occupied_beds = LEAST(total_beds, occupied_beds + 1) WHERE id = $allocated_room_id");
                }

                $action_msg = "Request status updated successfully!";
            }

            // 2. Add New Room
            if ($action === 'add_room') {
                $room_number = sanitize($_POST['room_number']);
                $block_name  = sanitize($_POST['block_name']);
                $floor       = (int)$_POST['floor'];
                $room_type   = sanitize($_POST['room_type']);
                $ac          = isset($_POST['air_conditioned']) ? 1 : 0;
                $total_beds  = (int)$_POST['total_beds'];
                $rent        = (float)$_POST['monthly_rent'];

                $sql = "INSERT INTO rooms (room_number, block_name, floor, room_type, air_conditioned, total_beds, occupied_beds, monthly_rent, status)
                        VALUES (:rno, :block, :fl, :rtype, :ac, :beds, 0, :rent, 'Available')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':rno'   => $room_number,
                    ':block' => $block_name,
                    ':fl'    => $floor,
                    ':rtype' => $room_type,
                    ':ac'    => $ac,
                    ':beds'  => $total_beds,
                    ':rent'  => $rent
                ]);
                $action_msg = "New room {$room_number} added successfully!";
            }

            // 3. Update Complaint Status
            if ($action === 'update_complaint') {
                $tkt_id = (int)$_POST['complaint_id'];
                $c_status = sanitize($_POST['status']);
                $stmt = $pdo->prepare("UPDATE complaints SET status = :st WHERE id = :id");
                $stmt->execute([':st' => $c_status, ':id' => $tkt_id]);
                $action_msg = "Complaint ticket status updated!";
            }
        } catch (Exception $e) {
            $action_error = "Error executing action: " . $e->getMessage();
        }
    } else {
        $action_msg = "Demo Mode: Action simulated successfully!";
    }
}

// Fetch Records
$requests = [];
$rooms_list = [];
$complaints = [];

if (isset($pdo)) {
    try {
        $requests = $pdo->query("SELECT r.*, rm.room_number FROM room_requests r LEFT JOIN rooms rm ON r.allocated_room_id = rm.id ORDER BY r.id DESC")->fetchAll();
        $rooms_list = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC")->fetchAll();
        $complaints = $pdo->query("SELECT * FROM complaints ORDER BY id DESC")->fetchAll();
    } catch (Exception $e) {}
}

// Mock fallback for demo presentation
if (empty($requests)) {
    $requests = [
        ['id' => 1, 'request_code' => 'REQ-2026-101', 'student_name' => 'Rahul Sharma', 'roll_number' => '2023CS105', 'course' => 'B.Tech CSE', 'preferred_room_type' => 'Single', 'ac_preference' => 1, 'status' => 'Allocated', 'room_number' => 'A-101', 'created_at' => date('Y-m-d')],
        ['id' => 2, 'request_code' => 'REQ-2026-102', 'student_name' => 'Priya Verma', 'roll_number' => '2024EC210', 'course' => 'B.Tech ECE', 'preferred_room_type' => 'Double', 'ac_preference' => 1, 'status' => 'Pending', 'room_number' => null, 'created_at' => date('Y-m-d')]
    ];
}

if (empty($rooms_list)) {
    $rooms_list = [
        ['id' => 1, 'room_number' => 'A-101', 'block_name' => 'Block A (Boys)', 'floor' => 1, 'room_type' => 'Single', 'air_conditioned' => 1, 'total_beds' => 1, 'occupied_beds' => 1, 'monthly_rent' => 8500, 'status' => 'Available'],
        ['id' => 2, 'room_number' => 'A-102', 'block_name' => 'Block A (Boys)', 'floor' => 1, 'room_type' => 'Double', 'air_conditioned' => 1, 'total_beds' => 2, 'occupied_beds' => 1, 'monthly_rent' => 6500, 'status' => 'Available']
    ];
}

if (empty($complaints)) {
    $complaints = [
        ['id' => 1, 'ticket_id' => 'TKT-901', 'student_name' => 'Rahul Sharma', 'room_number' => 'A-101', 'category' => 'Electrical', 'description' => 'AC remote fan speed control button not functioning.', 'status' => 'In Progress'],
        ['id' => 2, 'ticket_id' => 'TKT-902', 'student_name' => 'Vikas Patel', 'room_number' => 'A-201', 'category' => 'Wi-Fi', 'description' => 'Wi-Fi router on 2nd floor disconnecting frequently.', 'status' => 'Open']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden Admin Dashboard | Campus Stay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand-logo">
                <i class="fa-solid fa-building-user"></i> CampusStay <span style="font-size: 0.8rem; color: var(--accent-cyan); font-weight: 500;">[Warden Admin]</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link">Student View</a></li>
                <li><a href="admin_dashboard.php" class="nav-link active">Admin Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 2rem;">

        <?php if ($action_msg): ?>
            <div class="alert alert-success alert-dismissible">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($action_msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($action_error): ?>
            <div class="alert alert-danger alert-dismissible">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($action_error); ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Header & Quick Actions -->
        <div class="section-header">
            <div class="section-title">
                <h2><i class="fa-solid fa-user-shield" style="color: var(--primary);"></i> Hostel Warden Control Panel</h2>
                <p>Manage student applications, room inventory, and maintenance logs</p>
            </div>
        </div>

        <!-- 1. Manage Applications Section -->
        <div class="glass-card" style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.25rem; color: var(--accent-cyan);"><i class="fa-solid fa-paper-plane"></i> Student Room Requirement Applications</h3>
            
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Student</th>
                            <th>Roll No</th>
                            <th>Preference</th>
                            <th>Status</th>
                            <th>Allocated Room</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($req['request_code']); ?></strong></td>
                                <td><?= htmlspecialchars($req['student_name']); ?> (<?= htmlspecialchars($req['course']); ?>)</td>
                                <td><?= htmlspecialchars($req['roll_number']); ?></td>
                                <td><?= htmlspecialchars($req['preferred_room_type']); ?> Bed (<?= $req['ac_preference'] ? 'AC' : 'Non-AC'; ?>)</td>
                                <td>
                                    <span class="badge badge-<?= strtolower($req['status']); ?>">
                                        <?= htmlspecialchars($req['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= $req['room_number'] ? htmlspecialchars($req['room_number']) : 'Not Assigned'; ?></strong>
                                </td>
                                <td>
                                    <!-- Action Form -->
                                    <form action="admin_dashboard.php" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="action" value="update_request">
                                        <input type="hidden" name="request_id" value="<?= $req['id']; ?>">
                                        
                                        <select name="status" class="form-control" style="padding: 0.35rem 0.5rem; font-size: 0.85rem;">
                                            <option value="Pending" <?= $req['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Approved" <?= $req['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="Allocated" <?= $req['status'] === 'Allocated' ? 'selected' : ''; ?>>Allocated</option>
                                            <option value="Rejected" <?= $req['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>

                                        <select name="allocated_room_id" class="form-control" style="padding: 0.35rem 0.5rem; font-size: 0.85rem;">
                                            <option value="">Assign Room...</option>
                                            <?php foreach ($rooms_list as $rm): ?>
                                                <option value="<?= $rm['id']; ?>" <?= ($req['room_number'] === $rm['room_number']) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($rm['room_number']); ?> (<?= $rm['room_type']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary" title="Save Status">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Room Inventory & Add Room Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; margin-bottom: 2.5rem;">
            
            <!-- Add New Room Form -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1.25rem; color: var(--accent-emerald);"><i class="fa-solid fa-plus-circle"></i> Add New Hostel Room</h3>
                
                <form action="admin_dashboard.php" method="POST">
                    <input type="hidden" name="action" value="add_room">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Room Number *</label>
                            <input type="text" name="room_number" class="form-control" placeholder="e.g. C-101" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Block Name *</label>
                            <input type="text" name="block_name" class="form-control" placeholder="e.g. Block C" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Floor Number *</label>
                            <input type="number" name="floor" class="form-control" value="1" min="1" max="10" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Room Type *</label>
                            <select name="room_type" class="form-control" required>
                                <option value="Single">Single Bed</option>
                                <option value="Double">Double Bed</option>
                                <option value="Triple">Triple Bed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Total Beds *</label>
                            <input type="number" name="total_beds" class="form-control" value="2" min="1" max="5" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Monthly Rent (₹) *</label>
                            <input type="number" name="monthly_rent" class="form-control" placeholder="6500" required>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="air_conditioned" id="admin_ac" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <label for="admin_ac" class="form-label" style="margin: 0; cursor: pointer;">Air Conditioned Room (AC)</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-square-plus"></i> Save Room to Database
                    </button>
                </form>
            </div>

            <!-- Complaint Ticket Management -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1.25rem; color: var(--accent-amber);"><i class="fa-solid fa-headset"></i> Complaint Tickets Management</h3>
                
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Room</th>
                                <th>Category</th>
                                <th>Status Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($c['ticket_id']); ?></strong></td>
                                    <td><?= htmlspecialchars($c['room_number']); ?></td>
                                    <td><?= htmlspecialchars($c['category']); ?></td>
                                    <td>
                                        <form action="admin_dashboard.php" method="POST" style="display: flex; gap: 0.4rem;">
                                            <input type="hidden" name="action" value="update_complaint">
                                            <input type="hidden" name="complaint_id" value="<?= $c['id']; ?>">
                                            
                                            <select name="status" class="form-control" style="padding: 0.3rem; font-size: 0.8rem;">
                                                <option value="Open" <?= $c['status'] === 'Open' ? 'selected' : ''; ?>>Open</option>
                                                <option value="In Progress" <?= $c['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="Resolved" <?= $c['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                            
                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
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
