<?php
require_once __DIR__ . '/../config/config.php';
require_role('editor');

$stats = ['total' => 0, 'submitted' => 0, 'under_review' => 0, 'revision_required' => 0, 'accepted' => 0, 'rejected' => 0, 'published' => 0];
$res = $conn->query('SELECT status, COUNT(*) AS c FROM submissions GROUP BY status');
while ($row = $res->fetch_assoc()) {
    $stats['total'] += (int)$row['c'];
    $stats[$row['status']] = (int)$row['c'];
}

$recent = $conn->query("SELECT s.id, s.title, s.status, s.submitted_at, u.name AS author_name
                         FROM submissions s JOIN users u ON u.id = s.author_id
                         ORDER BY s.submitted_at DESC LIMIT 8");

$pageTitle = 'Editor Dashboard';
$pageSubtitle = 'Manage submissions, reviewers and published issues';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#4338ca;"><i class="bi bi-inboxes"></i></div>
            <div class="stat-value"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Submissions</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#0dcaf0;"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value"><?= $stats['submitted'] + $stats['under_review'] ?></div>
            <div class="stat-label">Awaiting Review</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#0d6efd;"><i class="bi bi-check-circle"></i></div>
            <div class="stat-value"><?= $stats['accepted'] ?></div>
            <div class="stat-label">Ready to Publish</div>
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

<div class="content-card">
    <div class="content-card-header">
        Recent Submissions
        <a href="<?= BASE_URL ?>/editor/submissions.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <?php if ($recent->num_rows === 0): ?>
        <div class="empty-state"><i class="bi bi-inbox"></i>No submissions yet.</div>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Title</th><th>Author</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
            <tbody>
                <?php while ($row = $recent->fetch_assoc()): ?>
                <tr>
                    <td><?= h($row['title']) ?></td>
                    <td><?= h($row['author_name']) ?></td>
                    <td><?= status_badge($row['status']) ?></td>
                    <td class="text-muted small"><?= h(date('d M Y', strtotime($row['submitted_at']))) ?></td>
                    <td><a href="<?= BASE_URL ?>/editor/view_submission.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
