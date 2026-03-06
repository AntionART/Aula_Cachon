<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Auth.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

Auth::startSession();
if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/views/dashboard.php');
    exit;
}

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $result = $controller->register($_POST);
    if ($result['success']) {
        $success = 'Cuenta creada. Ahora puedes iniciar sesión.';
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro — EduVirtual</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">🎓</div>
            <h2>Crear Cuenta</h2>
            <p>Únete a EduVirtual</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nombre completo <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Ej. Juan Pérez" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Correo electrónico <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Rol <span class="required">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>Estudiante</option>
                    <option value="teacher" <?= ($_POST['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Profesor</option>
                </select>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Contraseña <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Mín. 6 caracteres" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar contraseña <span class="required">*</span></label>
                    <input type="password" name="password_confirm" class="form-control" placeholder="Repite la contraseña" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
                Crear Cuenta
            </button>
        </form>

        <div class="divider"></div>
        <div class="text-center text-muted">
            ¿Ya tienes cuenta? <a href="<?= SITE_URL ?>/views/auth/login.php">Inicia sesión</a>
        </div>
    </div>
</div>
<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
</body>
</html>
