<?php
require_once 'config.php';

// Enforce Student Login Protection
require_student_login();

$success_message = '';
$error_message = '';
$generated_code = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = sanitize($_POST['student_name'] ?? '');
    $roll_number  = sanitize($_POST['roll_number'] ?? '');
    $email        = sanitize($_POST['email'] ?? '');
    $phone        = sanitize($_POST['phone'] ?? '');
    $gender       = sanitize($_POST['gender'] ?? 'Male');
    $course       = sanitize($_POST['course'] ?? '');
    $year_of_study= (int)($_POST['year_of_study'] ?? 1);
    $pref_type    = sanitize($_POST['preferred_room_type'] ?? 'Single');
    $ac_pref      = isset($_POST['ac_preference']) ? 1 : 0;
    $special_req  = sanitize($_POST['special_requirements'] ?? '');

    if (!empty($student_name) && !empty($roll_number) && !empty($email) && !empty($phone)) {
        $generated_code = generateRequestCode();

        if (isset($pdo)) {
            try {
                $sql = "INSERT INTO room_requests (request_code, student_name, roll_number, email, phone, gender, course, year_of_study, preferred_room_type, ac_preference, special_requirements, status)
                        VALUES (:code, :name, :roll, :email, :phone, :gender, :course, :year, :pref, :ac, :req, 'Pending')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':code'   => $generated_code,
                    ':name'   => $student_name,
                    ':roll'   => $roll_number,
                    ':email'  => $email,
                    ':phone'  => $phone,
                    ':gender' => $gender,
                    ':course' => $course,
                    ':year'   => $year_of_study,
                    ':pref'   => $pref_type,
                    ':ac'     => $ac_pref,
                    ':req'    => $special_req
                ]);
                $success_message = "Your room allocation request has been submitted successfully! Save your Request Code: <strong>{$generated_code}</strong> to track status.";
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        } else {
            $success_message = "Request Submitted! Save your Request Code: <strong>{$generated_code}</strong> to track status.";
        }
    } else {
        $error_message = "Please fill in all required fields (Name, Roll No, Email, Phone).";
    }
}

// Prefill from GET URL parameters & Session data
$pre_room = $_GET['pref_room'] ?? 'Single';
$pre_ac   = (isset($_GET['ac']) && $_GET['ac'] == 1) ? 1 : 0;
$user_name = $_SESSION['student_name'] ?? '';
$user_email = $_SESSION['student_email'] ?? '';
$user_roll = $_SESSION['roll_number'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Allocation Request | Campus Stay</title>
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
                <li><a href="request_room.php" class="nav-link active">Request Room</a></li>
                <li><a href="track_status.php" class="nav-link">Track Status</a></li>
                <li><a href="complaints.php" class="nav-link">Complaints</a></li>
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

    <div class="container" style="max-width: 850px; margin-top: 2rem;">

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible">
                <i class="fa-solid fa-circle-check" style="font-size: 1.5rem;"></i>
                <div>
                    <h4>Application Submitted!</h4>
                    <p><?= $success_message; ?></p>
                    <a href="track_status.php?code=<?= urlencode($generated_code); ?>" class="btn btn-sm btn-primary" style="margin-top: 0.5rem;">
                        <i class="fa-solid fa-magnifying-glass"></i> Track Request Now
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <div class="section-title" style="margin-bottom: 1.5rem;">
                <h2><i class="fa-solid fa-file-signature" style="color: var(--primary);"></i> Submit Room Requirement Request</h2>
                <p>Fill out your details and room preferences. Warden will review and assign room based on availability.</p>
            </div>

            <form action="request_room.php" method="POST">
                <!-- Personal & Academic Details -->
                <h4 style="color: var(--accent-cyan); margin-bottom: 1rem;"><i class="fa-solid fa-id-card"></i> Student Information</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="student_name" class="form-control" value="<?= htmlspecialchars($user_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">College Roll Number *</label>
                        <input type="text" name="roll_number" class="form-control" value="<?= htmlspecialchars($user_roll); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile number" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Branch / Course *</label>
                        <input type="text" name="course" class="form-control" placeholder="e.g. B.Tech CSE" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year of Study *</label>
                        <select name="year_of_study" class="form-control" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                </div>

                <hr style="border-color: var(--glass-border); margin: 1.5rem 0;">

                <!-- Room Preferences -->
                <h4 style="color: var(--accent-purple); margin-bottom: 1rem;"><i class="fa-solid fa-sliders"></i> Room Preferences</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Preferred Room Type *</label>
                        <select name="preferred_room_type" class="form-control" required>
                            <option value="Single" <?= $pre_room === 'Single' ? 'selected' : ''; ?>>Single Bed Room</option>
                            <option value="Double" <?= $pre_room === 'Double' ? 'selected' : ''; ?>>Double Sharing Room</option>
                            <option value="Triple" <?= $pre_room === 'Triple' ? 'selected' : ''; ?>>Triple Sharing Room</option>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.75rem; margin-top: 1.8rem;">
                        <input type="checkbox" name="ac_preference" id="ac_preference" value="1" style="width: 20px; height: 20px; accent-color: var(--primary);" <?= $pre_ac ? 'checked' : ''; ?>>
                        <label for="ac_preference" class="form-label" style="margin: 0; cursor: pointer;">
                            Request Air Conditioned (AC) Room
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Special Requirements / Medical Remarks (Optional)</label>
                    <textarea name="special_requirements" class="form-control" rows="3" placeholder="e.g. Ground floor preference due to knee injury, quiet room preference, etc..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem;">
                    <i class="fa-solid fa-paper-plane"></i> Submit Allocation Application
                </button>
            </form>
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
