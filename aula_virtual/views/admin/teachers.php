<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/TeacherController.php';

$pageTitle = 'Profesores';
$activePage = 'teachers';

Auth::requireRole('admin');

$controller = new TeacherController();
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

$teachers = $controller->getAll();
include __DIR__ . '/../partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title"><h1>Profesores</h1><p>Gestiona el cuerpo docente</p></div>
    <div class="topbar-actions">
        <button class="btn btn-primary" onclick="App.openModal('modal-create')">+ Nuevo Profesor</button>
    </div>
</div>

<div class="page-content">
    <?php if ($message): ?><div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Lista de Profesores (<?= count($teachers) ?>)</span>
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" class="form-control" placeholder="Buscar profesor..." data-search-target="#teachers-table">
            </div>
        </div>
        <div class="table-wrapper">
            <table id="teachers-table">
                <thead><tr><th>Profesor</th><th>Código</th><th>Email</th><th>Especialidad</th><th>Teléfono</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($teachers as $t): ?>
                <tr>
                    <td><span class="avatar-sm"><?= strtoupper(substr($t['name'], 0, 2)) ?></span><?= htmlspecialchars($t['name']) ?></td>
                    <td><span class="code-chip"><?= htmlspecialchars($t['code']) ?></span></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td><?= htmlspecialchars($t['specialty'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($t['phone'] ?: '-') ?></td>
                    <td><span class="badge <?= $t['active'] ? 'badge-green' : 'badge-gray' ?>"><?= $t['active'] ? 'Activo' : 'Inactivo' ?></span></td>
                    <td>
                        <button class="btn btn-outline btn-xs" onclick="openEdit(<?= htmlspecialchars(json_encode($t)) ?>)">✎ Editar</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este profesor?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($teachers)): ?>
                <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">👨‍🏫</div><p>No hay profesores registrados.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-create">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Nuevo Profesor</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group col-span-2"><label class="form-label">Nombre completo <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Email <span class="required">*</span></label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Contraseña <span class="required">*</span></label><input type="password" name="password" class="form-control" required placeholder="Mín. 6 caracteres"></div>
                    <div class="form-group"><label class="form-label">Especialidad</label><input type="text" name="specialty" class="form-control" placeholder="Ej. Matemáticas"></div>
                    <div class="form-group"><label class="form-label">Teléfono</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group col-span-2"><label class="form-label">Biografía</label><textarea name="bio" class="form-control" rows="3"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Profesor</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Editar Profesor</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group col-span-2"><label class="form-label">Nombre completo <span class="required">*</span></label><input type="text" name="name" id="edit-name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Email <span class="required">*</span></label><input type="email" name="email" id="edit-email" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Especialidad</label><input type="text" name="specialty" id="edit-specialty" class="form-control"></div>
                    <div class="form-group col-span-2"><label class="form-label">Teléfono</label><input type="text" name="phone" id="edit-phone" class="form-control"></div>
                    <div class="form-group col-span-2"><label class="form-label">Biografía</label><textarea name="bio" id="edit-bio" class="form-control" rows="3"></textarea></div>
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
function openEdit(t) {
    document.getElementById('edit-id').value = t.id;
    document.getElementById('edit-name').value = t.name;
    document.getElementById('edit-email').value = t.email;
    document.getElementById('edit-specialty').value = t.specialty || '';
    document.getElementById('edit-phone').value = t.phone || '';
    document.getElementById('edit-bio').value = t.bio || '';
    App.openModal('modal-edit');
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
