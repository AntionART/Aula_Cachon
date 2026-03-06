<?php
require_once __DIR__ . '/../../config/Auth.php';
Auth::requireLogin();
$role = Auth::getRole();
$userName = Auth::getUserName();
$siteUrl = SITE_URL;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Aula Virtual' ?> — EduVirtual</title>
<link rel="stylesheet" href="<?= $siteUrl ?>/assets/css/style.css">
</head>
<body>
<div id="toast-container" class="toast-container"></div>
<div class="layout">
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🎓</div>
        <div>
            <div class="brand-name">EduVirtual</div>
            <div class="brand-sub">Plataforma Escolar</div>
        </div>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
            <div class="user-role"><?= $role === 'admin' ? 'Administrador' : ($role === 'teacher' ? 'Profesor' : 'Estudiante') ?></div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a href="<?= $siteUrl ?>/views/dashboard.php" class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">⊞</span> Dashboard
        </a>

        <?php if ($role === 'admin'): ?>
        <div class="nav-section-label">Gestión</div>
        <a href="<?= $siteUrl ?>/views/admin/students.php" class="nav-item <?= ($activePage ?? '') === 'students' ? 'active' : '' ?>">
            <span class="nav-icon">👤</span> Estudiantes
        </a>
        <a href="<?= $siteUrl ?>/views/admin/teachers.php" class="nav-item <?= ($activePage ?? '') === 'teachers' ? 'active' : '' ?>">
            <span class="nav-icon">👨‍🏫</span> Profesores
        </a>
        <a href="<?= $siteUrl ?>/views/admin/subjects.php" class="nav-item <?= ($activePage ?? '') === 'subjects' ? 'active' : '' ?>">
            <span class="nav-icon">📚</span> Materias
        </a>
        <a href="<?= $siteUrl ?>/views/admin/courses.php" class="nav-item <?= ($activePage ?? '') === 'courses' ? 'active' : '' ?>">
            <span class="nav-icon">🏫</span> Cursos
        </a>
        <?php endif; ?>

        <?php if ($role === 'teacher'): ?>
        <div class="nav-section-label">Mi Aula</div>
        <a href="<?= $siteUrl ?>/views/teacher/courses.php" class="nav-item <?= ($activePage ?? '') === 'teacher_courses' ? 'active' : '' ?>">
            <span class="nav-icon">🏫</span> Mis Cursos
        </a>
        <a href="<?= $siteUrl ?>/views/teacher/assignments.php" class="nav-item <?= ($activePage ?? '') === 'teacher_assignments' ? 'active' : '' ?>">
            <span class="nav-icon">📝</span> Tareas
        </a>
        <?php endif; ?>

        <?php if ($role === 'student'): ?>
        <div class="nav-section-label">Mi Aprendizaje</div>
        <a href="<?= $siteUrl ?>/views/student/courses.php" class="nav-item <?= ($activePage ?? '') === 'student_courses' ? 'active' : '' ?>">
            <span class="nav-icon">📖</span> Mis Cursos
        </a>
        <a href="<?= $siteUrl ?>/views/student/assignments.php" class="nav-item <?= ($activePage ?? '') === 'student_assignments' ? 'active' : '' ?>">
            <span class="nav-icon">✏️</span> Tareas
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <form method="POST" action="<?= $siteUrl ?>/views/auth/logout.php">
            <button type="submit" class="logout-btn">
                <span>⬢</span> Cerrar Sesión
            </button>
        </form>
    </div>
</aside>
<main class="main-content">
