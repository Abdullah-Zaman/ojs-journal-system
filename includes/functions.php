<?php
// ==========================================================
// Shared helper functions used across the whole app
// ==========================================================

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_role() {
    return $_SESSION['role'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_role($role) {
    require_login();
    if (current_role() !== $role) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function redirect_to_dashboard() {
    switch (current_role()) {
        case 'author':
            header('Location: ' . BASE_URL . '/author/dashboard.php');
            break;
        case 'reviewer':
            header('Location: ' . BASE_URL . '/reviewer/dashboard.php');
            break;
        case 'editor':
            header('Location: ' . BASE_URL . '/editor/dashboard.php');
            break;
        default:
            header('Location: ' . BASE_URL . '/index.php');
    }
    exit;
}

// Get up to 2 initials from a full name, for avatar badges
function initials($name) {
    $parts = preg_split('/\s+/', trim($name ?? ''));
    $parts = array_filter($parts);
    if (empty($parts)) {
        return '?';
    }
    if (count($parts) === 1) {
        return mb_strtoupper(mb_substr($parts[0], 0, 2));
    }
    $first = mb_substr(reset($parts), 0, 1);
    $last  = mb_substr(end($parts), 0, 1);
    return mb_strtoupper($first . $last);
}

// Escape output safely (XSS protection)
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// One-time flash messages stored in session
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $class = $f['type'] === 'error' ? 'danger' : $f['type'];
        echo '<div class="alert alert-' . h($class) . ' alert-dismissible fade show" role="alert">'
           . h($f['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['flash']);
    }
}

// Colored status badge used on author/editor/reviewer dashboards
function status_badge($status) {
    $map = [
        'submitted'          => 'secondary',
        'under_review'       => 'info',
        'revision_required'  => 'warning',
        'accepted'           => 'primary',
        'rejected'           => 'danger',
        'published'          => 'success',
        'assigned'           => 'secondary',
        'completed'          => 'success',
    ];
    $color = $map[$status] ?? 'secondary';
    $label = ucwords(str_replace('_', ' ', $status));
    return '<span class="badge bg-' . $color . '">' . h($label) . '</span>';
}
