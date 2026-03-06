<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller_auth = new stdClass();
    Auth::startSession();
    Auth::logout();
}
header('Location: ' . SITE_URL . '/views/auth/login.php');
exit;
