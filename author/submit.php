<?php
require_once __DIR__ . '/../config/config.php';
require_role('author');

$errors = [];
$old = ['title' => '', 'abstract' => '', 'keywords' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['title']     = trim($_POST['title'] ?? '');
    $old['abstract']  = trim($_POST['abstract'] ?? '');
    $old['keywords']  = trim($_POST['keywords'] ?? '');

    if ($old['title'] === '' || $old['abstract'] === '') {
        $errors[] = 'Title and abstract are required.';
    }

    $uploadedPath = null;

    if (!isset($_FILES['paper_file']) || $_FILES['paper_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please attach a paper file (PDF/DOC/DOCX).';
    } elseif ($_FILES['paper_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'There was a problem uploading the file. Please try again.';
    } else {
        $file = $_FILES['paper_file'];
        $allowedExt = ['pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Only PDF, DOC or DOCX files are allowed.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'File must be smaller than 10MB.';
        } else {
            $uploadsDir = dirname(__DIR__) . '/uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            $newName = 'paper_' . uniqid() . '_' . time() . '.' . $ext;
            $destination = $uploadsDir . '/' . $newName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $uploadedPath = 'uploads/' . $newName;
            } else {
                $errors[] = 'The file could not be saved. Please try again.';
            }
        }
    }

    if (empty($errors) && $uploadedPath) {
        $authorId = $_SESSION['user_id'];
        $stmt = $conn->prepare('INSERT INTO submissions (author_id, title, abstract, keywords, file_path) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issss', $authorId, $old['title'], $old['abstract'], $old['keywords'], $uploadedPath);
        if ($stmt->execute()) {
            $stmt->close();
            set_flash('success', 'Your paper has been submitted successfully!');
            header('Location: ' . BASE_URL . '/author/my_submissions.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
}

$pageTitle = 'Submit Paper';
$pageSubtitle = 'Upload your manuscript for peer review';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="content-card">
    <div class="content-card-header">Submit New Paper</div>
    <div class="p-4">
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= h($e) ?></div>
        <?php endforeach; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Paper Title</label>
                <input type="text" name="title" class="form-control" required value="<?= h($old['title']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Abstract</label>
                <textarea name="abstract" rows="6" class="form-control" required><?= h($old['abstract']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Keywords <span class="text-muted small">(comma separated)</span></label>
                <input type="text" name="keywords" class="form-control" value="<?= h($old['keywords']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Paper File <span class="text-muted small">(PDF, DOC, DOCX &mdash; max 10MB)</span></label>
                <input type="file" name="paper_file" class="form-control" accept=".pdf,.doc,.docx" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up"></i> Submit Paper</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
