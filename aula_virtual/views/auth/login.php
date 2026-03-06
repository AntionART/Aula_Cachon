<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Auth.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

Auth::startSession();
if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/views/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $result = $controller->login($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['success']) {
        header('Location: ' . SITE_URL . '/views/dashboard.php');
        exit;
    }
    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesión — EduVirtual</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">🎓</div>
            <h2>EduVirtual</h2>
            <p>Ingresa a tu aula virtual</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Correo electrónico <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña <span class="required">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
                Iniciar Sesión →
            </button>
        </form>

        <div class="divider"></div>
        <div class="text-center text-muted">
            ¿No tienes cuenta? <a href="<?= SITE_URL ?>/views/auth/register.php">Regístrate aquí</a>
        </div>

        <div style="margin-top:20px;padding:14px;background:var(--bg-3);border-radius:var(--radius-sm);border:1px solid var(--border);">
            <p style="font-size:0.72rem;color:var(--text-3);margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Cuentas de demo</p>
            <p style="font-size:0.78rem;color:var(--text-2);">admin@aula.edu — maria@aula.edu — carlos@aula.edu</p>
            <p style="font-size:0.78rem;color:var(--text-3);">Contraseña: <code style="color:var(--accent)">password</code></p>
        </div>
    </div>
</div>
</body>
</html>
