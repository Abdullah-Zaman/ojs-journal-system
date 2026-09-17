<?php
require_once __DIR__ . '/../config/config.php';
require_role('reviewer');

$reviewerId = $_SESSION['user_id'];

$stats = ['assigned' => 0, 'completed' => 0];
$res = $conn->prepare('SELECT status, COUNT(*) AS c FROM reviews WHERE reviewer_id = ? GROUP BY status');
$res->bind_param('i', $reviewerId);
$res->execute();
$result = $res->get_result();
while ($row = $result->fetch_assoc()) {
    $stats[$row['status']] = (int)$row['c'];
}

$stmt = $conn->prepare("SELECT r.id AS review_id, r.status AS review_status, s.title, s.submitted_at
                         FROM reviews r JOIN submissions s ON s.id = r.submission_id
                         WHERE r.reviewer_id = ? ORDER BY r.assigned_at DESC");
$stmt->bind_param('i', $reviewerId);
$stmt->execute();
$assignments = $stmt->get_result();

$pageTitle = 'Reviewer Dashboard';
$pageSubtitle = 'Papers assigned to you for review';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#0dcaf0;"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value"><?= $stats['assigned'] ?></div>
            <div class="stat-label">Pending Reviews</div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#198754;"><i class="bi bi-check-circle"></i></div>
            <div class="stat-value"><?= $stats['completed'] ?></div>
            <div class="stat-label">Completed Reviews</div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#4338ca;"><i class="bi bi-clipboard-data"></i></div>
            <div class="stat-value"><?= $stats['assigned'] + $stats['completed'] ?></div>
            <div class="stat-label">Total Assigned</div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">My Assigned Papers</div>
    <?php if ($assignments->num_rows === 0): ?>
        <div class="empty-state"><i class="bi bi-inbox"></i>No papers have been assigned to you yet.</div>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Title</th><th>Status</th><th>Assigned</th><th></th></tr></thead>
            <tbody>
                <?php while ($row = $assignments->fetch_assoc()): ?>
                <tr>
                    <td><?= h($row['title']) ?></td>
                    <td><?= status_badge($row['review_status']) ?></td>
                    <td class="text-muted small"><?= h(date('d M Y', strtotime($row['submitted_at']))) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/reviewer/review.php?id=<?= (int)$row['review_id'] ?>" class="btn btn-sm btn-outline-primary">
                            <?= $row['review_status'] === 'completed' ? 'View Review' : 'Review Now' ?>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
