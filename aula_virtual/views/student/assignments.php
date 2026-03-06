<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AssignmentController.php';
require_once __DIR__ . '/../../models/StudentModel.php';

$pageTitle = 'Tareas';
$activePage = 'student_assignments';

Auth::requireRole('student');

$studentModel = new StudentModel();
$assignmentController = new AssignmentController();

$student = $studentModel->getByUserId(Auth::getUserId());
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $student) {
    $action = $_POST['action'] ?? '';
    if ($action === 'submit' && !empty($_POST['assignment_id'])) {
        $file = !empty($_FILES['file']['name']) ? $_FILES['file'] : null;
        $result = $assignmentController->submit((int)$_POST['assignment_id'], $student['id'], $_POST['content'] ?? '', $file);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    }
}

$assignments = $student ? $assignmentController->getByStudent($student['id']) : [];

$pending = array_filter($assignments, fn($a) => !$a['submission_id'] && strtotime($a['due_date']) > time());
$submitted = array_filter($assignments, fn($a) => $a['submission_id']);
$overdue = array_filter($assignments, fn($a) => !$a['submission_id'] && strtotime($a['due_date']) <= time());

include __DIR__ . '/../partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title"><h1>Mis Tareas</h1><p>Revisa y entrega tus asignaciones</p></div>
    <div style="display:flex;gap:8px">
        <span class="badge badge-orange"><?= count($pending) ?> pendientes</span>
        <span class="badge badge-green"><?= count($submitted) ?> entregadas</span>
        <?php if (count($overdue)): ?><span class="badge badge-red"><?= count($overdue) ?> vencidas</span><?php endif; ?>
    </div>
</div>

<div class="page-content">
    <?php if ($message): ?><div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if (!empty($pending) || !empty($overdue)): ?>
    <div style="margin-bottom:20px">
        <h2 style="font-size:0.9rem;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px">Pendientes de Entrega</h2>
        <?php foreach (array_merge(array_values($pending), array_values($overdue)) as $a): ?>
        <?php $isOverdue = strtotime($a['due_date']) <= time(); ?>
        <div class="card" style="margin-bottom:10px">
            <div style="padding:18px 22px">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
                    <div style="flex:1">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                            <h3 style="font-size:0.95rem;font-weight:700"><?= htmlspecialchars($a['title']) ?></h3>
                            <?= $isOverdue ? '<span class="badge badge-red">Vencida</span>' : '<span class="badge badge-orange">Pendiente</span>' ?>
                        </div>
                        <p style="font-size:0.8rem;color:var(--text-3);margin-bottom:8px">📖 <?= htmlspecialchars($a['course_name']) ?> &nbsp;|&nbsp; 🏆 Máx. <?= $a['max_score'] ?> pts</p>
                        <?php if ($a['description']): ?><p style="font-size:0.85rem;color:var(--text-2);margin-bottom:8px"><?= nl2br(htmlspecialchars($a['description'])) ?></p><?php endif; ?>
                        <p style="font-size:0.8rem;color:<?= $isOverdue ? 'var(--danger)' : 'var(--warn)' ?>">⏰ Fecha límite: <?= date('d/m/Y H:i', strtotime($a['due_date'])) ?></p>
                    </div>
                    <?php if (!$isOverdue): ?>
                    <button class="btn btn-primary btn-sm" onclick="openSubmit(<?= $a['id'] ?>, <?= htmlspecialchars(json_encode($a['title'])) ?>)">Entregar</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($submitted)): ?>
    <div>
        <h2 style="font-size:0.9rem;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px">Entregadas</h2>
        <div class="card">
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Tarea</th><th>Curso</th><th>Entregada</th><th>Calificación</th><th>Retroalimentación</th></tr></thead>
                    <tbody>
                    <?php foreach ($submitted as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['title']) ?></td>
                        <td><?= htmlspecialchars($a['course_name']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($a['submitted_at'])) ?></td>
                        <td>
                            <?php if ($a['score'] !== null): ?>
                            <span class="badge badge-green" style="font-size:0.85rem"><?= $a['score'] ?> / <?= $a['max_score'] ?></span>
                            <?php else: ?>
                            <span class="badge badge-gray">Sin calificar</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:0.82rem;color:var(--text-2)"><?= htmlspecialchars($a['feedback'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($assignments)): ?>
    <div class="card"><div class="card-body"><div class="empty-state"><div class="empty-icon">✏️</div><p>No tienes tareas asignadas aún.</p></div></div></div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="modal-submit">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="submit-modal-title">Entregar Tarea</span>
            <button class="modal-close" data-modal-close>×</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="submit">
            <input type="hidden" name="assignment_id" id="submit-assignment-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tu respuesta / comentarios</label>
                    <textarea name="content" class="form-control" rows="5" placeholder="Escribe tu respuesta aquí..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Archivo adjunto (opcional)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg">
                    <small style="color:var(--text-3);font-size:0.75rem">Formatos permitidos: PDF, Word, TXT, imágenes. Máx. 5MB.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-success">Enviar Tarea ✓</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSubmit(assignmentId, title) {
    document.getElementById('submit-assignment-id').value = assignmentId;
    document.getElementById('submit-modal-title').textContent = 'Entregar: ' + title;
    App.openModal('modal-submit');
}
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
