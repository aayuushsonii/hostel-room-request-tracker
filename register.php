<?php
require_once 'config.php';

// If already logged in, redirect to home page
if (is_student_logged_in()) {
    header("Location: index.php");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = sanitize($_POST['full_name'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $roll_number = sanitize($_POST['roll_number'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (!empty($full_name) && !empty($email) && !empty($roll_number) && !empty($password)) {
        if (isset($pdo)) {
            try {
                // Check if email or roll number already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = :email OR roll_number = :roll");
                $stmt->execute([':email' => $email, ':roll' => $roll_number]);
                if ($stmt->fetchColumn() > 0) {
                    $error_message = "A student account with this Email or Roll Number already exists.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO students (full_name, email, roll_number, password) VALUES (:name, :email, :roll, :pass)");
                    $stmt->execute([
                        ':name'  => $full_name,
                        ':email' => $email,
                        ':roll'  => $roll_number,
                        ':pass'  => $hashed_password
                    ]);
                    $success_message = "Registration successful! You can now login with your Email & Password.";
                }
            } catch (Exception $e) {
                $error_message = "Error creating account: " . $e->getMessage();
            }
        } else {
            $success_message = "Registration successful! Proceed to Login.";
        }
    } else {
        $error_message = "Please fill in all mandatory fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | Campus Stay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem;">

    <div class="glass-card" style="width: 100%; max-width: 480px; padding: 2.5rem 2rem;">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div class="brand-logo" style="justify-content: center; font-size: 2rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-building-user"></i> CampusStay
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Create New Student Account</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <?= $success_message; ?><br>
                    <a href="login.php" class="btn btn-sm btn-primary" style="margin-top: 0.5rem;">Go to Login Page</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" placeholder="e.g. Vikas Patel" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Student Email / Gmail *</label>
                    <input type="email" name="email" class="form-control" placeholder="student@college.edu" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Roll Number *</label>
                    <input type="text" name="roll_number" class="form-control" placeholder="2023CS105" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Create a strong password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 0.9rem; font-size: 1rem; margin-top: 1rem;">
                <i class="fa-solid fa-user-plus"></i> Register Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; border-top: 1px solid var(--glass-border); padding-top: 1.25rem;">
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Already registered? <a href="login.php" style="color: var(--accent-cyan); font-weight: 600;">Login Here</a>
            </p>
        </div>

    </div>

</body>
</html>
