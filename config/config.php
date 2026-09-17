<?php
// ==========================================================
// App bootstrap: session + base URL detection + db + helpers
// Every page includes ONLY this file:
//   require_once __DIR__ . '/config/config.php';   (root pages)
//   require_once __DIR__ . '/../config/config.php'; (subfolder pages)
// ==========================================================

// TEMP DEBUG: force PHP errors to show on screen instead of a blank page.
// Remove this block once things are working again.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-detect the base URL so the app works no matter what folder
// it is placed in under htdocs/www (e.g. /ojs-system or /).
$fsRoot   = str_replace('\\', '/', dirname(__DIR__));
$docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$basePath = ($docRoot !== '' && strpos($fsRoot, $docRoot) === 0)
    ? substr($fsRoot, strlen($docRoot))
    : '';
define('BASE_URL', $basePath);
define('APP_ROOT', dirname(__DIR__));

require_once __DIR__ . '/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
