<nav class="navbar navbar-expand-lg navbar-glass py-3">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
      <span class="brand-stack brand-stack-dark">
        <span class="brand-mark"><i class="bi bi-journal-richtext"></i></span>
        <span class="brand-text">
            <span class="brand-title"><span class="ul-letter">O</span>J<span class="ul-letter">S</span></span>
            <span class="brand-subtitle">Open Journal System</span>
        </span>
      </span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link px-3 active" href="<?= BASE_URL ?>/index.php">Home</a></li>

        <?php if (is_logged_in()): ?>
            <?php if (current_role() === 'author'): ?>
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/author/dashboard.php">My Dashboard</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/author/submit.php">Submit Paper</a></li>
            <?php elseif (current_role() === 'reviewer'): ?>
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/reviewer/dashboard.php">Review Dashboard</a></li>
            <?php elseif (current_role() === 'editor'): ?>
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/editor/dashboard.php">Editor Dashboard</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/editor/issues.php">Issues</a></li>
            <?php endif; ?>
            <li class="nav-item ms-lg-2"><span class="nav-link text-muted px-2">Hi, <?= h($_SESSION['name']) ?> <span class="badge bg-secondary text-uppercase"><?= h(current_role()) ?></span></span></li>
            <li class="nav-item ms-lg-1"><a class="btn-logout-nav" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/login.php">Login</a></li>
            <li class="nav-item ms-lg-1"><a class="btn btn-hero-primary btn-sm px-3" href="<?= BASE_URL ?>/register.php">Get Started</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
