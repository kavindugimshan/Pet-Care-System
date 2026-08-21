<?php
// ============================================================
// Pet Care System - Admin Login Page
// University Web Application Development Project
// Member 1: Core Auth
// ============================================================

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If admin is already authenticated, send them straight to the dashboard
if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Read any error message passed back from authenticate.php
$error = '';
if (isset($_GET['error']) && $_GET['error'] === 'invalid') {
    $error = 'Invalid username or password. Please try again.';
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pet Care System Admin Login">
    <title><?php echo htmlspecialchars($pageTitle . ' | Pet Care System'); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/common.css">

    <style>
        /* Extra login-page specific polish */
        body { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
        .main-content { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    </style>
</head>
<body>

<main class="main-content">
    <div class="auth-card">
        <div class="auth-logo">🐾</div>
        <h1 class="auth-title">Admin Portal</h1>
        <p class="auth-subtitle">Pet Care System &mdash; Administration</p>

        <?php if ($error): ?>
            <div class="alert alert-error" id="loginError">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="authenticate.php" method="POST" novalidate id="loginForm">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    placeholder="Enter your username"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
                Sign In
            </button>
        </form>

        <p class="text-center text-sm text-muted mt-lg">
            <a href="../index.php">← Back to Website</a>
        </p>
    </div>
</main>

<script>
    // Disable button on submit to prevent double submission
    document.getElementById('loginForm').addEventListener('submit', function () {
        document.getElementById('loginBtn').disabled = true;
        document.getElementById('loginBtn').textContent = 'Signing in…';
    });
</script>

</body>
</html>
