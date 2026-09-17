<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?>OJS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= @filemtime(APP_ROOT . '/assets/css/style.css') ?: 1 ?>" rel="stylesheet">
</head>
<body>
<div class="dash-wrapper">

    <?php $role = current_role(); $page = basename($_SERVER['SCRIPT_NAME']); ?>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="ojs-logo-title"><span class="ul-letter">O</span>J<span class="ul-letter">S</span></div>
            <div class="ojs-logo-subtitle">Open Journal System</div>
        </div>

        <nav class="sidebar-nav">
            <?php if ($role === 'author'): ?>
                <a href="<?= BASE_URL ?>/author/dashboard.php" class="sidebar-link <?= $page === 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="<?= BASE_URL ?>/author/submit.php" class="sidebar-link <?= $page === 'submit.php' ? 'active' : '' ?>"><i class="bi bi-cloud-arrow-up"></i> Submit Paper</a>
                <a href="<?= BASE_URL ?>/author/my_submissions.php" class="sidebar-link <?= $page === 'my_submissions.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-text"></i> My Submissions</a>
            <?php elseif ($role === 'reviewer'): ?>
                <a href="<?= BASE_URL ?>/reviewer/dashboard.php" class="sidebar-link <?= $page === 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="<?= BASE_URL ?>/reviewer/dashboard.php" class="sidebar-link"><i class="bi bi-clipboard-check"></i> Assigned Reviews</a>
            <?php elseif ($role === 'editor'): ?>
                <a href="<?= BASE_URL ?>/editor/dashboard.php" class="sidebar-link <?= $page === 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="<?= BASE_URL ?>/editor/submissions.php" class="sidebar-link <?= $page === 'submissions.php' || $page === 'view_submission.php' ? 'active' : '' ?>"><i class="bi bi-inboxes"></i> All Submissions</a>
                <a href="<?= BASE_URL ?>/editor/issues.php" class="sidebar-link <?= $page === 'issues.php' ? 'active' : '' ?>"><i class="bi bi-journal-bookmark"></i> Issues</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/index.php" class="sidebar-link"><i class="bi bi-globe2"></i> Public Site</a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>/logout.php" class="btn-logout-sidebar"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <div class="dash-main">
        <header class="dash-topbar">
            <div class="d-flex align-items-center gap-2">
                <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary d-md-none"><i class="bi bi-list"></i></button>
                <div class="page-heading-block ms-1">
                    <span class="eyebrow"><?= h(ucfirst($role)) ?> Panel</span>
                    <h4><?= isset($pageTitle) ? h($pageTitle) : 'Dashboard' ?></h4>
                    <?php if (!empty($pageSubtitle)): ?>
                        <div class="sub"><?= h($pageSubtitle) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="user-pill">
                <div class="avatar"><?= h(initials($_SESSION['name'])) ?></div>
                <div>
                    <div class="u-name"><?= h($_SESSION['name']) ?></div>
                    <div class="u-role"><?= h($role) ?></div>
                </div>
            </div>
        </header>

        <div class="dash-content">
            <?php show_flash(); ?>
