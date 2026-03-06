<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/SubjectController.php';

$pageTitle = 'Materias';
$activePage = 'subjects';

Auth::requireRole('admin');

$controller = new SubjectController();
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

$subjects = $controller->getAll();
include __DIR__ . '/../partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title"><h1>Materias</h1><p>Gestiona las materias académicas</p></div>
    <div class="topbar-actions">
        <button class="btn btn-primary" onclick="App.openModal('modal-create')">+ Nueva Materia</button>
    </div>
</div>

<div class="page-content">
    <?php if ($message): ?><div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Lista de Materias (<?= count($subjects) ?>)</span>
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" class="form-control" placeholder="Buscar materia..." data-search-target="#subjects-table">
            </div>
        </div>
        <div class="table-wrapper">
            <table id="subjects-table">
                <thead><tr><th>Materia</th><th>Código</th><th>Créditos</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($subjects as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><span class="code-chip"><?= htmlspecialchars($s['code']) ?></span></td>
                    <td><?= $s['credits'] ?></td>
                    <td style="max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($s['description'] ?: '-') ?></td>
                    <td><span class="badge <?= $s['active'] ? 'badge-green' : 'badge-gray' ?>"><?= $s['active'] ? 'Activa' : 'Inactiva' ?></span></td>
                    <td>
                        <button class="btn btn-outline btn-xs" onclick="openEdit(<?= htmlspecialchars(json_encode($s)) ?>)">✎ Editar</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar esta materia?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($subjects)): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">📚</div><p>No hay materias registradas.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-create">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Nueva Materia</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">Nombre <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Código <span class="required">*</span></label><input type="text" name="code" class="form-control" required placeholder="Ej. MAT-001"></div>
                    <div class="form-group col-span-2"><label class="form-label">Descripción</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                    <div class="form-group"><label class="form-label">Créditos</label><input type="number" name="credits" class="form-control" value="3" min="0" max="10"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Materia</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Editar Materia</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">Nombre <span class="required">*</span></label><input type="text" name="name" id="edit-name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Código <span class="required">*</span></label><input type="text" name="code" id="edit-code" class="form-control" required></div>
                    <div class="form-group col-span-2"><label class="form-label">Descripción</label><textarea name="description" id="edit-description" class="form-control" rows="3"></textarea></div>
                    <div class="form-group"><label class="form-label">Créditos</label><input type="number" name="credits" id="edit-credits" class="form-control" min="0" max="10"></div>
                    <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;padding-bottom:4px">
                        <input type="checkbox" name="active" id="edit-active" value="1" style="width:18px;height:18px">
                        <label class="form-label" for="edit-active" style="margin:0">Activa</label>
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
    document.getElementById('edit-code').value = s.code;
    document.getElementById('edit-description').value = s.description || '';
    document.getElementById('edit-credits').value = s.credits;
    document.getElementById('edit-active').checked = s.active == 1;
    App.openModal('modal-edit');
}
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
