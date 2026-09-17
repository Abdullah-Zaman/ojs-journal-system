<?php
require_once __DIR__ . '/../config/config.php';
require_role('editor');

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT s.*, u.name AS author_name, u.email AS author_email FROM submissions s JOIN users u ON u.id = s.author_id WHERE s.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();

if (!$submission) {
    set_flash('error', 'Submission not found.');
    header('Location: ' . BASE_URL . '/editor/submissions.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'assign_reviewer') {
        $reviewerId = (int)($_POST['reviewer_id'] ?? 0);
        if ($reviewerId <= 0) {
            $errors[] = 'Please select a reviewer.';
        } else {
            $check = $conn->prepare('SELECT id FROM reviews WHERE submission_id = ? AND reviewer_id = ?');
            $check->bind_param('ii', $id, $reviewerId);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $errors[] = 'This reviewer is already assigned.';
            } else {
                $ins = $conn->prepare('INSERT INTO reviews (submission_id, reviewer_id) VALUES (?, ?)');
                $ins->bind_param('ii', $id, $reviewerId);
                $ins->execute();

                if ($submission['status'] === 'submitted') {
                    $upd = $conn->prepare("UPDATE submissions SET status = 'under_review' WHERE id = ?");
                    $upd->bind_param('i', $id);
                    $upd->execute();
                }
                set_flash('success', 'Reviewer has been assigned successfully.');
                header('Location: ' . BASE_URL . '/editor/view_submission.php?id=' . $id);
                exit;
            }
        }
    } elseif ($action === 'decide') {
        $decision = $_POST['decision'] ?? '';
        $validDecisions = ['accepted', 'rejected', 'revision_required'];
        if (!in_array($decision, $validDecisions, true)) {
            $errors[] = 'Please select a valid decision.';
        } else {
            $upd = $conn->prepare('UPDATE submissions SET status = ? WHERE id = ?');
            $upd->bind_param('si', $decision, $id);
            $upd->execute();
            set_flash('success', 'Decision has been saved successfully.');
            header('Location: ' . BASE_URL . '/editor/view_submission.php?id=' . $id);
            exit;
        }
    }
}

// Reload fresh data
$stmt = $conn->prepare('SELECT s.*, u.name AS author_name, u.email AS author_email FROM submissions s JOIN users u ON u.id = s.author_id WHERE s.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();

$rstmt = $conn->prepare('SELECT r.*, u.name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.reviewer_id WHERE r.submission_id = ? ORDER BY r.assigned_at DESC');
$rstmt->bind_param('i', $id);
$rstmt->execute();
$reviews = $rstmt->get_result();

$availableReviewersStmt = $conn->prepare("SELECT id, name FROM users WHERE role = 'reviewer' AND id NOT IN (SELECT reviewer_id FROM reviews WHERE submission_id = ?) ORDER BY name");
$availableReviewersStmt->bind_param('i', $id);
$availableReviewersStmt->execute();
$availableReviewers = $availableReviewersStmt->get_result();

$pageTitle = 'Manage Submission';
$pageSubtitle = 'Assign reviewers and record the editorial decision';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<a href="<?= BASE_URL ?>/editor/submissions.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to All Submissions</a>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= h($e) ?></div>
<?php endforeach; ?>

<div class="content-card mb-4">
    <div class="content-card-header">
        <span><?= h($submission['title']) ?></span>
        <?= status_badge($submission['status']) ?>
    </div>
    <div class="p-4">
        <p class="text-muted small mb-1">Author: <strong><?= h($submission['author_name']) ?></strong> (<?= h($submission['author_email']) ?>)</p>
        <p class="text-muted small">Submitted: <?= h(date('d M Y, h:i A', strtotime($submission['submitted_at']))) ?></p>
        <p><?= nl2br(h($submission['abstract'])) ?></p>
        <?php if ($submission['keywords']): ?>
            <p class="small text-muted">Keywords: <?= h($submission['keywords']) ?></p>
        <?php endif; ?>
        <a href="<?= BASE_URL . '/' . h($submission['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> View Paper File</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="content-card mb-4">
            <div class="content-card-header">Assign Reviewer</div>
            <div class="p-4">
                <?php if ($availableReviewers->num_rows === 0): ?>
                    <p class="text-muted small mb-0">All reviewers are already assigned, or no reviewer has registered yet.</p>
                <?php else: ?>
                    <form method="post" class="d-flex gap-2">
                        <input type="hidden" name="action" value="assign_reviewer">
                        <select name="reviewer_id" class="form-select" required>
                            <option value="">Select Reviewer</option>
                            <?php while ($rev = $availableReviewers->fetch_assoc()): ?>
                                <option value="<?= (int)$rev['id'] ?>"><?= h($rev['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-primary text-nowrap"><i class="bi bi-person-plus"></i> Assign</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-card">
            <div class="content-card-header">Editorial Decision</div>
            <div class="p-4">
                <form method="post">
                    <input type="hidden" name="action" value="decide">
                    <select name="decision" class="form-select mb-3" required>
                        <option value="">Select Decision</option>
                        <option value="accepted">Accept</option>
                        <option value="revision_required">Request Revision</option>
                        <option value="rejected">Reject</option>
                    </select>
                    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-check2-square"></i> Save Decision</button>
                </form>
                <?php if ($submission['status'] === 'accepted'): ?>
                    <p class="small text-muted mt-3 mb-0">This paper has been accepted. Go to the <a href="<?= BASE_URL ?>/editor/issues.php">Issues</a> page to publish it.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="content-card">
            <div class="content-card-header">Reviews</div>
            <?php if ($reviews->num_rows === 0): ?>
                <div class="empty-state"><i class="bi bi-clipboard"></i>No reviewer has been assigned yet.</div>
            <?php else: ?>
                <div class="p-3">
                <?php while ($rev = $reviews->fetch_assoc()): ?>
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong><?= h($rev['reviewer_name']) ?></strong>
                            <?= status_badge($rev['status']) ?>
                        </div>
                        <?php if ($rev['status'] === 'completed'): ?>
                            <div class="small text-muted mt-1">Recommendation: <strong><?= h(ucwords(str_replace('_', ' ', $rev['recommendation']))) ?></strong></div>
                            <div class="mt-1"><?= nl2br(h($rev['comments'])) ?></div>
                        <?php else: ?>
                            <div class="small text-muted mt-1">Review is still pending.</div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
