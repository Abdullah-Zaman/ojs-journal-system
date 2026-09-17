<?php
require_once __DIR__ . '/../config/config.php';
require_role('editor');

$statusFilter = $_GET['status'] ?? '';
$validStatuses = ['submitted', 'under_review', 'revision_required', 'accepted', 'rejected', 'published'];

$sql = "SELECT s.id, s.title, s.status, s.submitted_at, u.name AS author_name
        FROM submissions s JOIN users u ON u.id = s.author_id";
if (in_array($statusFilter, $validStatuses, true)) {
    $sql .= ' WHERE s.status = ?';
}
$sql .= ' ORDER BY s.submitted_at DESC';

$stmt = $conn->prepare($sql);
if (in_array($statusFilter, $validStatuses, true)) {
    $stmt->bind_param('s', $statusFilter);
}
$stmt->execute();
$submissions = $stmt->get_result();

$pageTitle = 'All Submissions';
$pageSubtitle = 'Every paper submitted to the journal';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="content-card">
    <div class="content-card-header flex-wrap gap-2">
        <span>All Submissions</span>
        <div class="btn-group btn-group-sm flex-wrap">
            <a href="?status=" class="btn btn-outline-secondary <?= $statusFilter === '' ? 'active' : '' ?>">All</a>
            <?php foreach ($validStatuses as $st): ?>
                <a href="?status=<?= $st ?>" class="btn btn-outline-secondary <?= $statusFilter === $st ? 'active' : '' ?>"><?= h(ucwords(str_replace('_', ' ', $st))) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if ($submissions->num_rows === 0): ?>
        <div class="empty-state"><i class="bi bi-inbox"></i>No submissions found.</div>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Title</th><th>Author</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
            <tbody>
                <?php while ($row = $submissions->fetch_assoc()): ?>
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
