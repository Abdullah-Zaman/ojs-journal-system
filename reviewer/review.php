<?php
require_once __DIR__ . '/../config/config.php';
require_role('reviewer');

$reviewId = (int)($_GET['id'] ?? 0);
$reviewerId = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT r.*, s.title, s.abstract, s.keywords, s.file_path
                         FROM reviews r JOIN submissions s ON s.id = r.submission_id
                         WHERE r.id = ? AND r.reviewer_id = ?');
$stmt->bind_param('ii', $reviewId, $reviewerId);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();

if (!$review) {
    set_flash('error', 'Review not found or you do not have access.');
    header('Location: ' . BASE_URL . '/reviewer/dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $review['status'] === 'assigned') {
    $comments = trim($_POST['comments'] ?? '');
    $recommendation = $_POST['recommendation'] ?? '';
    $validRecs = ['accept', 'minor_revision', 'major_revision', 'reject'];

    if ($comments === '') {
        $errors[] = 'Comments are required.';
    } elseif (!in_array($recommendation, $validRecs, true)) {
        $errors[] = 'Please select a recommendation.';
    } else {
        $upd = $conn->prepare("UPDATE reviews SET comments = ?, recommendation = ?, status = 'completed', reviewed_at = NOW() WHERE id = ?");
        $upd->bind_param('ssi', $comments, $recommendation, $reviewId);
        $upd->execute();
        set_flash('success', 'Review submitted. Thank you!');
        header('Location: ' . BASE_URL . '/reviewer/dashboard.php');
        exit;
    }
}

$pageTitle = 'Review Paper';
$pageSubtitle = 'Read the manuscript and submit your recommendation';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<a href="<?= BASE_URL ?>/reviewer/dashboard.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>

<div class="content-card mb-4">
    <div class="content-card-header">
        <span><?= h($review['title']) ?></span>
        <?= status_badge($review['status']) ?>
    </div>
    <div class="p-4">
        <p><?= nl2br(h($review['abstract'])) ?></p>
        <?php if ($review['keywords']): ?>
            <p class="small text-muted">Keywords: <?= h($review['keywords']) ?></p>
        <?php endif; ?>
        <a href="<?= BASE_URL . '/' . h($review['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> View Paper File</a>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">Your Review</div>
    <div class="p-4">
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= h($e) ?></div>
        <?php endforeach; ?>

        <?php if ($review['status'] === 'completed'): ?>
            <p class="text-muted small mb-1">Recommendation: <strong><?= h(ucwords(str_replace('_', ' ', $review['recommendation']))) ?></strong></p>
            <p><?= nl2br(h($review['comments'])) ?></p>
            <p class="small text-muted mb-0">Submitted: <?= h(date('d M Y, h:i A', strtotime($review['reviewed_at']))) ?></p>
        <?php else: ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Comments for the Author / Editor</label>
                    <textarea name="comments" rows="6" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Recommendation</label>
                    <select name="recommendation" class="form-select" required>
                        <option value="">Select Recommendation</option>
                        <option value="accept">Accept</option>
                        <option value="minor_revision">Minor Revision</option>
                        <option value="major_revision">Major Revision</option>
                        <option value="reject">Reject</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send-check"></i> Submit Review</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
