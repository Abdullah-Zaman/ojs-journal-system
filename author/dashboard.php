<?php
require_once __DIR__ . '/../config/config.php';
require_role('author');

$authorId = $_SESSION['user_id'];

$stats = ['total' => 0, 'under_review' => 0, 'accepted' => 0, 'published' => 0];
$stmt = $conn->prepare("SELECT status, COUNT(*) AS c FROM submissions WHERE author_id = ? GROUP BY status");
$stmt->bind_param('i', $authorId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $stats['total'] += (int)$row['c'];
    if (in_array($row['status'], ['submitted', 'under_review', 'revision_required'], true)) {
        $stats['under_review'] += (int)$row['c'];
    }
    if ($row['status'] === 'accepted') $stats['accepted'] += (int)$row['c'];
    if ($row['status'] === 'published') $stats['published'] += (int)$row['c'];
}
$stmt->close();

$stmt = $conn->prepare("SELECT id, title, status, submitted_at FROM submissions WHERE author_id = ? ORDER BY submitted_at DESC LIMIT 5");
$stmt->bind_param('i', $authorId);
$stmt->execute();
$recent = $stmt->get_result();

$pageTitle = 'Author Dashboard';
$pageSubtitle = 'Track your submissions and their review progress';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#4338ca;"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-value"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Submissions</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#0dcaf0;"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value"><?= $stats['under_review'] ?></div>
            <div class="stat-label">In Review Pipeline</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#0d6efd;"><i class="bi bi-check-circle"></i></div>
            <div class="stat-value"><?= $stats['accepted'] ?></div>
            <div class="stat-label">Accepted</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#198754;"><i class="bi bi-journal-check"></i></div>
            <div class="stat-value"><?= $stats['published'] ?></div>
            <div class="stat-label">Published</div>
        </div>
    </div>
</div>

<div class="content-card mb-4">
    <div class="content-card-header">
        Recent Submissions
        <a href="<?= BASE_URL ?>/author/submit.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Submit New Paper</a>
    </div>
    <?php if ($recent->num_rows === 0): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            You haven't submitted any papers yet.
        </div>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Title</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
            <tbody>
                <?php while ($row = $recent->fetch_assoc()): ?>
                <tr>
                    <td><?= h($row['title']) ?></td>
                    <td><?= status_badge($row['status']) ?></td>
                    <td class="text-muted small"><?= h(date('d M Y', strtotime($row['submitted_at']))) ?></td>
                    <td><a href="<?= BASE_URL ?>/author/my_submissions.php" class="btn btn-sm btn-outline-secondary">View</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
