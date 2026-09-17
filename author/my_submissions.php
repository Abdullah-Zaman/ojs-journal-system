<?php
require_once __DIR__ . '/../config/config.php';
require_role('author');

$authorId = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT * FROM submissions WHERE author_id = ? ORDER BY submitted_at DESC');
$stmt->bind_param('i', $authorId);
$stmt->execute();
$submissions = $stmt->get_result();

$pageTitle = 'My Submissions';
$pageSubtitle = 'Status of every paper you have submitted';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="content-card">
    <div class="content-card-header">My Submissions</div>

    <?php if ($submissions->num_rows === 0): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            You haven't submitted any papers yet.<br>
            <a href="<?= BASE_URL ?>/author/submit.php" class="btn btn-primary btn-sm mt-2">Submit Your First Paper</a>
        </div>
    <?php else: ?>
        <div class="accordion accordion-flush" id="subAccordion">
        <?php $i = 0; while ($sub = $submissions->fetch_assoc()): $i++; ?>
            <?php
                $rstmt = $conn->prepare("SELECT comments, recommendation, status FROM reviews WHERE submission_id = ?");
                $rstmt->bind_param('i', $sub['id']);
                $rstmt->execute();
                $reviews = $rstmt->get_result();
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sub<?= $i ?>">
                        <div class="flex-grow-1 d-flex justify-content-between align-items-center me-3">
                            <span><?= h($sub['title']) ?></span>
                            <span class="ms-2"><?= status_badge($sub['status']) ?></span>
                        </div>
                    </button>
                </h2>
                <div id="sub<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#subAccordion">
                    <div class="accordion-body">
                        <p class="text-muted"><?= nl2br(h($sub['abstract'])) ?></p>
                        <p>
                            <a href="<?= BASE_URL . '/' . h($sub['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> View Submitted File
                            </a>
                        </p>
                        <p class="small text-muted mb-0">Submitted: <?= h(date('d M Y, h:i A', strtotime($sub['submitted_at']))) ?></p>

                        <?php if ($reviews->num_rows > 0): ?>
                            <hr>
                            <h6>Reviewer Feedback</h6>
                            <?php while ($rev = $reviews->fetch_assoc()): ?>
                                <div class="border rounded p-2 mb-2">
                                    <?php if ($rev['status'] === 'completed'): ?>
                                        <div class="small text-muted mb-1">
                                            Recommendation: <strong><?= h(ucwords(str_replace('_', ' ', $rev['recommendation']))) ?></strong>
                                        </div>
                                        <div><?= nl2br(h($rev['comments'])) ?></div>
                                    <?php else: ?>
                                        <div class="small text-muted">Review pending...</div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
