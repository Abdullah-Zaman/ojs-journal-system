<?php
require_once __DIR__ . '/config/config.php';

// Fetch all published papers, grouped by issue
$sql = "SELECT i.id AS issue_id, i.volume, i.issue_number, i.title AS issue_title, i.published_date,
               s.id AS submission_id, s.title, s.abstract, s.file_path, s.keywords
        FROM issues i
        JOIN published_papers pp ON pp.issue_id = i.id
        JOIN submissions s ON s.id = pp.submission_id
        ORDER BY i.volume DESC, i.issue_number DESC, pp.published_at DESC";
$result = $conn->query($sql);

$issues = [];
$publishedCount = 0;
while ($row = $result->fetch_assoc()) {
    $issues[$row['issue_id']]['volume'] = $row['volume'];
    $issues[$row['issue_id']]['issue_number'] = $row['issue_number'];
    $issues[$row['issue_id']]['title'] = $row['issue_title'];
    $issues[$row['issue_id']]['published_date'] = $row['published_date'];
    $issues[$row['issue_id']]['papers'][] = $row;
    $publishedCount++;
}

// Quick stats for the hero section
$reviewerCount = (int)($conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'reviewer'")->fetch_assoc()['c'] ?? 0);
$authorCount   = (int)($conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'author'")->fetch_assoc()['c'] ?? 0);

// Grab the most recently submitted paper's title, for the hero "live activity" card
$latestSubmission = $conn->query("SELECT title, status FROM submissions ORDER BY submitted_at DESC LIMIT 1")->fetch_assoc();

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="eyebrow-badge"><span class="dot"></span> Peer-Reviewed &middot; Open Access</span>
        <h1 class="hero-title">Publish research that <span>gets seen.</span></h1>
        <p class="hero-lead">A streamlined submission-to-publication workflow for authors, reviewers and editors &mdash; transparent peer review, fast decisions, and open access to every published issue.</p>
        <div class="d-flex gap-3 flex-wrap">
            <?php if (!is_logged_in()): ?>
                <a href="<?= BASE_URL ?>/register.php" class="btn-hero-primary btn">Submit Your Paper <i class="bi bi-arrow-right ms-1"></i></a>
                <a href="#published" class="btn-hero-outline btn">Browse Published Issues</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/<?= h(current_role()) ?>/dashboard.php" class="btn-hero-primary btn">Go to My Dashboard <i class="bi bi-arrow-right ms-1"></i></a>
                <a href="#published" class="btn-hero-outline btn">Browse Published Issues</a>
            <?php endif; ?>
        </div>
        <div class="hero-stats">
          <div><div class="stat-num"><?= $publishedCount ?></div><div class="stat-lbl">Published Article<?= $publishedCount === 1 ? '' : 's' ?></div></div>
          <div><div class="stat-num"><?= $reviewerCount ?></div><div class="stat-lbl">Active Reviewer<?= $reviewerCount === 1 ? '' : 's' ?></div></div>
          <div><div class="stat-num">100%</div><div class="stat-lbl">Open Access</div></div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-visual">
          <div class="mini-row">
            <div class="mini-icon" style="background:#4338ca;"><i class="bi bi-cloud-arrow-up"></i></div>
            <div><div class="mini-title">Manuscript Submitted</div><div class="mini-sub"><?= $latestSubmission ? h(mb_strimwidth($latestSubmission['title'], 0, 42, '...')) : 'Waiting for the first submission' ?></div></div>
          </div>
          <div class="mini-row">
            <div class="mini-icon" style="background:#f59e0b;"><i class="bi bi-clipboard-check"></i></div>
            <div><div class="mini-title">Under Peer Review</div><div class="mini-sub">Assigned reviewers evaluate each submission</div></div>
          </div>
          <div class="mini-row">
            <div class="mini-icon" style="background:#16a34a;"><i class="bi bi-journal-check"></i></div>
            <div><div class="mini-title">Published</div><div class="mini-sub">Open access on the homepage, organized by issue</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Workflow -->
<section class="py-5 my-3">
  <div class="container">
    <div class="text-center mx-auto mb-5" style="max-width:600px;">
      <span class="section-label">How it works</span>
      <h2 class="section-heading">From manuscript to publication</h2>
      <p class="section-sub mx-auto">A simple three-step editorial workflow that keeps authors, reviewers and editors in sync.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="workflow-card">
          <div class="step-num">01</div>
          <div class="wf-icon" style="background:#4338ca;"><i class="bi bi-cloud-arrow-up"></i></div>
          <h5>Submit</h5>
          <p>Authors upload their manuscript along with an abstract and keywords in just a few clicks.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="workflow-card">
          <div class="step-num">02</div>
          <div class="wf-icon" style="background:#f59e0b;"><i class="bi bi-clipboard-check"></i></div>
          <h5>Review</h5>
          <p>Editors assign qualified reviewers, who evaluate the work and submit structured feedback.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="workflow-card">
          <div class="step-num">03</div>
          <div class="wf-icon" style="background:#16a34a;"><i class="bi bi-journal-check"></i></div>
          <h5>Publish</h5>
          <p>Accepted papers are organized into volumes and issues, then published for open access.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Published Issues -->
<section class="issues-section" id="published">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
      <div>
        <span class="section-label">Latest research</span>
        <h2 class="section-heading mb-0">Published Issues</h2>
      </div>
    </div>

    <?php if (empty($issues)): ?>
        <div class="content-card">
            <div class="empty-state">
                <i class="bi bi-journal-x"></i>
                No issues have been published yet.<br>
                <span class="small">As soon as the editor publishes a paper, it will appear here.</span>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($issues as $issue): ?>
            <div class="issue-card">
                <div class="issue-header">
                    <span><i class="bi bi-bookmark-fill me-1"></i> Volume <?= (int)$issue['volume'] ?>, Issue <?= (int)$issue['issue_number'] ?>
                        <?= $issue['title'] ? '&mdash; ' . h($issue['title']) : '' ?>
                    </span>
                    <?php if ($issue['published_date']): ?>
                        <span class="small" style="opacity:.85;"><?= h(date('d M Y', strtotime($issue['published_date']))) ?></span>
                    <?php endif; ?>
                </div>
                <?php foreach ($issue['papers'] as $paper): ?>
                    <div class="paper-row">
                        <?php if (!empty($paper['keywords'])): ?>
                            <span class="paper-meta-tag mb-2 d-inline-block"><i class="bi bi-tag"></i> <?= h(mb_strimwidth(explode(',', $paper['keywords'])[0], 0, 28, '...')) ?></span>
                        <?php endif; ?>
                        <div class="paper-title"><?= h($paper['title']) ?></div>
                        <div class="paper-abstract"><?= h(mb_strimwidth($paper['abstract'], 0, 220, '...')) ?></div>
                        <a href="<?= BASE_URL . '/' . h($paper['file_path']) ?>" class="btn-view-pdf" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> View PDF
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- CTA -->
<?php if (!is_logged_in()): ?>
<div class="container">
  <div class="cta-band">
    <h3>Ready to submit your research?</h3>
    <p>Join as an author or reviewer and be part of a transparent, peer-reviewed publishing process.</p>
    <a href="<?= BASE_URL ?>/register.php" class="btn btn-light btn-lg fw-semibold px-4">Create Your Account</a>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
