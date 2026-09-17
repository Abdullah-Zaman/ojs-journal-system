<?php
require_once __DIR__ . '/config/config.php';

if (is_logged_in()) {
    redirect_to_dashboard();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            redirect_to_dashboard();
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> - OJS Journal System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= @filemtime(APP_ROOT . '/assets/css/style.css') ?: 1 ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">

    <div class="auth-brand-panel d-none d-lg-flex">
        <div class="brand-stack">
            <span class="brand-mark"><i class="bi bi-journal-richtext"></i></span>
            <div class="brand-text">
                <span class="brand-title"><span class="ul-letter">O</span>J<span class="ul-letter">S</span></span>
                <span class="brand-subtitle">Open Journal System</span>
            </div>
        </div>
        <h2>Welcome back.</h2>
        <p class="lead-text">Sign in to manage submissions, peer reviews, and published issues from one place.</p>

        <div class="auth-feature">
            <i class="bi bi-cloud-arrow-up"></i>
            <div><strong>Submit</strong><br><span class="auth-feature-text">Upload your research paper in a few clicks.</span></div>
        </div>
        <div class="auth-feature">
            <i class="bi bi-clipboard-check"></i>
            <div><strong>Review</strong><br><span class="auth-feature-text">A transparent, role-based peer review process.</span></div>
        </div>
        <div class="auth-feature">
            <i class="bi bi-journal-check"></i>
            <div><strong>Publish</strong><br><span class="auth-feature-text">Get your accepted work published online.</span></div>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="auth-card">
            <div class="text-center mb-4 d-lg-none">
                <div class="brand-stack brand-stack-dark justify-content-center">
                    <span class="brand-mark"><i class="bi bi-journal-richtext"></i></span>
                    <div class="brand-text text-start">
                        <span class="brand-title"><span class="ul-letter">O</span>J<span class="ul-letter">S</span></span>
                        <span class="brand-subtitle">Open Journal System</span>
                    </div>
                </div>
            </div>

            <h3 class="fw-bold mb-1">Sign In</h3>
            <p class="text-muted mb-4">Enter your credentials to access your dashboard.</p>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= h($e) ?></div>
            <?php endforeach; ?>
            <?php show_flash(); ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control form-control-lg" required
                           value="<?= h($_POST['email'] ?? '') ?>" placeholder="you@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Sign In</button>
            </form>

            <p class="text-center mt-4 mb-1">Don&rsquo;t have an account? <a href="<?= BASE_URL ?>/register.php" class="fw-semibold">Register</a></p>
            <p class="text-center text-muted small mb-0">Demo editor login: <strong>editor@ojs.com</strong> / <strong>editor123</strong></p>
            <p class="text-center mt-3 mb-0"><a href="<?= BASE_URL ?>/index.php" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to homepage</a></p>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
