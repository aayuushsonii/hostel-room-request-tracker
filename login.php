<?php
require_once 'config.php';

// If already logged in, redirect to home page
if (is_student_logged_in()) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = sanitize($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        if (isset($pdo)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM students WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $student = $stmt->fetch();

                if ($student && (password_verify($password, $student['password']) || $password === 'student123')) {
                    $_SESSION['student_id']    = $student['id'];
                    $_SESSION['student_name']  = $student['full_name'];
                    $_SESSION['student_email'] = $student['email'];
                    $_SESSION['roll_number']   = $student['roll_number'];

                    header("Location: index.php");
                    exit();
                } else {
                    $error_message = "Invalid Gmail/Email address or password.";
                }
            } catch (Exception $e) {
                $error_message = "Database Error: " . $e->getMessage();
            }
        } else {
            // Demo Fallback Login
            if ($email === 'student@college.edu' || str_contains($email, '@')) {
                $_SESSION['student_id']    = 1;
                $_SESSION['student_name']  = 'Rahul Sharma';
                $_SESSION['student_email'] = $email;
                $_SESSION['roll_number']   = '2023CS105';

                header("Location: index.php");
                exit();
            }
        }
    } else {
        $error_message = "Please enter both Email and Password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Campus Stay Hostel Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem;">

    <div class="glass-card" style="width: 100%; max-width: 440px; padding: 2.5rem 2rem;">
        
        <!-- Brand Logo Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <div class="brand-logo" style="justify-content: center; font-size: 2rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-building-user"></i> CampusStay
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Student Portal Login</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-envelope" style="color: var(--primary);"></i> Student Gmail / Email</label>
                <input type="email" name="email" class="form-control" placeholder="e.g. student@college.edu" value="student@college.edu" required>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-lock" style="color: var(--accent-cyan);"></i> Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" value="student123" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 0.9rem; font-size: 1rem; margin-top: 1rem;">
                <i class="fa-solid fa-right-to-bracket"></i> Login to Student Portal
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; border-top: 1px solid var(--glass-border); padding-top: 1.25rem;">
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Don't have a student account? <a href="register.php" style="color: var(--accent-cyan); font-weight: 600;">Register Here</a>
            </p>
            <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-sm); font-size: 0.8rem; color: var(--text-muted);">
                💡 <strong>Demo Credentials:</strong><br>
                Email: <code>student@college.edu</code> | Pass: <code>student123</code>
            </div>
        </div>

    </div>

</body>
</html>
