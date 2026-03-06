<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Auth.php';

Auth::startSession();
if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/views/dashboard.php');
} else {
    header('Location: ' . SITE_URL . '/views/auth/login.php');
}
exit;
