<?php
require_once __DIR__ . '/../config/config.php';

class Auth {
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }

    public static function login($user) {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
    }

    public static function logout() {
        self::startSession();
        $_SESSION = [];
        session_destroy();
    }

    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/views/auth/login.php');
            exit;
        }
    }

    public static function requireRole($roles) {
        self::requireLogin();
        if (!in_array($_SESSION['user_role'], (array)$roles)) {
            header('Location: ' . SITE_URL . '/views/dashboard.php');
            exit;
        }
    }

    public static function getRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUserName() {
        return $_SESSION['user_name'] ?? null;
    }
}
