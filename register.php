<?php
require_once __DIR__ . '/config/config.php';

if (is_logged_in()) {
    redirect_to_dashboard();
}

$errors = [];
$old = ['name' => '', 'email' => '', 'role' => 'author', 'affiliation' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']        = trim($_POST['name'] ?? '');
    $old['email']       = trim($_POST['email'] ?? '');
    $old['affiliation'] = trim($_POST['affiliation'] ?? '');
    $old['role']        = in_array($_POST['role'] ?? '', ['author', 'reviewer'], true) ? $_POST['role'] : 'author';
    $password           = $_POST['password'] ?? '';
    $confirm            = $_POST['confirm_password'] ?? '';

    if ($old['name'] === '' || $old['email'] === '' || $password === '') {
        $errors[] = 'Name, email and password are required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'This email is already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, password, role, affiliation) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $old['name'], $old['email'], $hash, $old['role'], $old['affiliation']);
        if ($stmt->execute()) {
            $stmt->close();
            set_flash('success', 'Registration successful! Please log in.');
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        } else {
            $errors[] = 'Registration failed: ' . $conn->error;
        }
    }
}

$pageTitle = 'Register';
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
        <h2>Join the journal.</h2>
        <p class="lead-text">Create an account to submit your research or help review submissions from other authors.</p>

        <div class="auth-feature">
            <i class="bi bi-person-badge"></i>
            <div><strong>Author</strong><br><span class="auth-feature-text">Submit papers and track their review status.</span></div>
        </div>
        <div class="auth-feature">
            <i class="bi bi-clipboard-data"></i>
            <div><strong>Reviewer</strong><br><span class="auth-feature-text">Evaluate submissions and give feedback.</span></div>
        </div>
        <div class="auth-feature">
            <i class="bi bi-shield-check"></i>
            <div><strong>Secure</strong><br><span class="auth-feature-text">Your password is encrypted and never shared.</span></div>
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

            <h3 class="fw-bold mb-1">Create an Account</h3>
            <p class="text-muted mb-4">Get started in less than a minute.</p>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= h($e) ?></div>
            <?php endforeach; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required value="<?= h($old['name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" required value="<?= h($old['email']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Affiliation <span class="text-muted small">(University / Organization)</span></label>
                    <input type="text" name="affiliation" class="form-control" value="<?= h($old['affiliation']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Register as</label>
                    <select name="role" class="form-select">
                        <option value="author" <?= $old['role'] === 'author' ? 'selected' : '' ?>>Author</option>
                        <option value="reviewer" <?= $old['role'] === 'reviewer' ? 'selected' : '' ?>>Reviewer</option>
                    </select>
                    <div class="form-text">Editor accounts are created by the administrator.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Create Account</button>
            </form>

            <p class="text-center mt-4 mb-0">Already have an account? <a href="<?= BASE_URL ?>/login.php" class="fw-semibold">Log in</a></p>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
