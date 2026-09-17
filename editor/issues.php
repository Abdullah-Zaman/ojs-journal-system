<?php
require_once __DIR__ . '/../config/config.php';
require_role('editor');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_issue') {
        $volume = (int)($_POST['volume'] ?? 0);
        $issueNumber = (int)($_POST['issue_number'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $publishedDate = trim($_POST['published_date'] ?? '');

        if ($volume <= 0 || $issueNumber <= 0) {
            $errors[] = 'Volume and issue number are required.';
        } else {
            $publishedDateVal = $publishedDate !== '' ? $publishedDate : null;
            $stmt = $conn->prepare('INSERT INTO issues (volume, issue_number, title, published_date) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('iiss', $volume, $issueNumber, $title, $publishedDateVal);
            $stmt->execute();
            set_flash('success', 'Naya issue ban gaya.');
            header('Location: ' . BASE_URL . '/editor/issues.php');
            exit;
        }
    } elseif ($action === 'publish') {
        $submissionId = (int)($_POST['submission_id'] ?? 0);
        $issueId = (int)($_POST['issue_id'] ?? 0);

        if ($submissionId <= 0 || $issueId <= 0) {
            $errors[] = 'Please select both a paper and an issue.';
        } else {
            $ins = $conn->prepare('INSERT INTO published_papers (submission_id, issue_id) VALUES (?, ?)');
            $ins->bind_param('ii', $submissionId, $issueId);
            $ins->execute();

            $upd = $conn->prepare("UPDATE submissions SET status = 'published' WHERE id = ?");
            $upd->bind_param('i', $submissionId);
            $upd->execute();

            set_flash('success', 'Paper has been published successfully!');
            header('Location: ' . BASE_URL . '/editor/issues.php');
            exit;
        }
    }
}

$issues = $conn->query('SELECT * FROM issues ORDER BY volume DESC, issue_number DESC');
$acceptedPapers = $conn->query("SELECT id, title FROM submissions WHERE status = 'accepted' ORDER BY submitted_at DESC");
$allIssues = $conn->query('SELECT id, volume, issue_number, title FROM issues ORDER BY volume DESC, issue_number DESC');

$pageTitle = 'Issues & Publishing';
$pageSubtitle = 'Create issues and publish accepted papers';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= h($e) ?></div>
<?php endforeach; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="content-card">
            <div class="content-card-header">Create New Issue</div>
            <div class="p-4">
                <form method="post" class="row g-2">
                    <input type="hidden" name="action" value="create_issue">
                    <div class="col-6">
                        <label class="form-label small">Volume</label>
                        <input type="number" name="volume" class="form-control" min="1" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Issue Number</label>
                        <input type="number" name="issue_number" class="form-control" min="1" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Title (optional)</label>
                        <input type="text" name="title" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Published Date</label>
                        <input type="date" name="published_date" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Create Issue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="content-card">
            <div class="content-card-header">Publish an Accepted Paper</div>
            <div class="p-4">
                <?php if ($acceptedPapers->num_rows === 0): ?>
                    <p class="text-muted small mb-0">No accepted paper is ready to publish yet.</p>
                <?php elseif ($allIssues->num_rows === 0): ?>
                    <p class="text-muted small mb-0">Please create an issue first.</p>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="action" value="publish">
                        <div class="mb-3">
                            <label class="form-label small">Select Paper</label>
                            <select name="submission_id" class="form-select" required>
                                <option value="">Choose accepted paper</option>
                                <?php while ($p = $acceptedPapers->fetch_assoc()): ?>
                                    <option value="<?= (int)$p['id'] ?>"><?= h($p['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Select Issue</label>
                            <select name="issue_id" class="form-select" required>
                                <option value="">Choose issue</option>
                                <?php while ($i = $allIssues->fetch_assoc()): ?>
                                    <option value="<?= (int)$i['id'] ?>">Vol <?= (int)$i['volume'] ?>, Issue <?= (int)$i['issue_number'] ?><?= $i['title'] ? ' - ' . h($i['title']) : '' ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-send-check"></i> Publish</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">All Issues</div>
    <?php if ($issues->num_rows === 0): ?>
        <div class="empty-state"><i class="bi bi-journal-x"></i>No issues have been created yet.</div>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Volume</th><th>Issue</th><th>Title</th><th>Published Date</th><th>Papers</th></tr></thead>
            <tbody>
                <?php while ($iss = $issues->fetch_assoc()):
                    $countStmt = $conn->prepare('SELECT COUNT(*) AS c FROM published_papers WHERE issue_id = ?');
                    $countStmt->bind_param('i', $iss['id']);
                    $countStmt->execute();
                    $count = $countStmt->get_result()->fetch_assoc()['c'];
                ?>
                <tr>
                    <td><?= (int)$iss['volume'] ?></td>
                    <td><?= (int)$iss['issue_number'] ?></td>
                    <td><?= h($iss['title'] ?: '—') ?></td>
                    <td class="text-muted small"><?= $iss['published_date'] ? h(date('d M Y', strtotime($iss['published_date']))) : '—' ?></td>
                    <td><span class="badge bg-secondary"><?= (int)$count ?> papers</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
