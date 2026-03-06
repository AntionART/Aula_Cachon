<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/StudentController.php';

$pageTitle = 'Estudiantes';
$activePage = 'students';

Auth::requireRole('admin');

$controller = new StudentController();
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $result = $controller->create($_POST);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'update' && !empty($_POST['id'])) {
        $result = $controller->update((int)$_POST['id'], $_POST);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'delete' && !empty($_POST['id'])) {
        $result = $controller->delete((int)$_POST['id']);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    }
}

$students = $controller->getAll();
include __DIR__ . '/../partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title"><h1>Estudiantes</h1><p>Gestiona el registro de estudiantes</p></div>
    <div class="topbar-actions">
        <button class="btn btn-primary" onclick="App.openModal('modal-create')">+ Nuevo Estudiante</button>
    </div>
</div>

<div class="page-content">
    <?php if ($message): ?><div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Lista de Estudiantes (<?= count($students) ?>)</span>
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" class="form-control" placeholder="Buscar estudiante..." data-search-target="#students-table">
            </div>
        </div>
        <div class="table-wrapper">
            <table id="students-table">
                <thead>
                    <tr><th>Estudiante</th><th>Código</th><th>Email</th><th>Grado</th><th>Teléfono</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td>
                        <span class="avatar-sm"><?= strtoupper(substr($s['name'], 0, 2)) ?></span>
                        <?= htmlspecialchars($s['name']) ?>
                    </td>
                    <td><span class="code-chip"><?= htmlspecialchars($s['code']) ?></span></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= htmlspecialchars($s['grade'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($s['phone'] ?: '-') ?></td>
                    <td><span class="badge <?= $s['active'] ? 'badge-green' : 'badge-gray' ?>"><?= $s['active'] ? 'Activo' : 'Inactivo' ?></span></td>
                    <td>
                        <button class="btn btn-outline btn-xs" onclick="openEdit(<?= htmlspecialchars(json_encode($s)) ?>)">✎ Editar</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este estudiante?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">👤</div><p>No hay estudiantes registrados.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-create">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Nuevo Estudiante</span>
            <button class="modal-close" data-modal-close>×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group col-span-2">
                        <label class="form-label">Nombre completo <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="Mín. 6 caracteres">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grado</label>
                        <input type="text" name="grade" class="form-control" placeholder="Ej. 10°">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input type="date" name="birth_date" class="form-control">
                    </div>
                    <div class="form-group col-span-2">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Estudiante</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Editar Estudiante</span>
            <button class="modal-close" data-modal-close>×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group col-span-2">
                        <label class="form-label">Nombre completo <span class="required">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" id="edit-email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grado</label>
                        <input type="text" name="grade" id="edit-grade" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" id="edit-phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input type="date" name="birth_date" id="edit-birth_date" class="form-control">
                    </div>
                    <div class="form-group col-span-2">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="address" id="edit-address" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(s) {
    document.getElementById('edit-id').value = s.id;
    document.getElementById('edit-name').value = s.name;
    document.getElementById('edit-email').value = s.email;
    document.getElementById('edit-grade').value = s.grade || '';
    document.getElementById('edit-phone').value = s.phone || '';
    document.getElementById('edit-birth_date').value = s.birth_date || '';
    document.getElementById('edit-address').value = s.address || '';
    App.openModal('modal-edit');
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
