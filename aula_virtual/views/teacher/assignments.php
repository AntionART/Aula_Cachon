<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AssignmentController.php';
require_once __DIR__ . '/../../controllers/CourseController.php';
require_once __DIR__ . '/../../models/TeacherModel.php';

$pageTitle = 'Tareas';
$activePage = 'teacher_assignments';

Auth::requireRole('teacher');

$teacherModel = new TeacherModel();
$assignmentController = new AssignmentController();
$courseController = new CourseController();

$teacher = $teacherModel->getByUserId(Auth::getUserId());
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create' && $teacher) {
        $_POST['teacher_id'] = $teacher['id'];
        $result = $assignmentController->create($_POST);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'update' && !empty($_POST['id'])) {
        $result = $assignmentController->update((int)$_POST['id'], $_POST);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'delete' && !empty($_POST['id'])) {
        $result = $assignmentController->delete((int)$_POST['id']);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'grade' && !empty($_POST['submission_id'])) {
        $result = $assignmentController->gradeSubmission((int)$_POST['submission_id'], $_POST['score'], $_POST['feedback'] ?? '');
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    }
}

$assignments = $teacher ? $assignmentController->getByTeacher($teacher['id']) : [];
$myCourses = $teacher ? $courseController->getByTeacher($teacher['id']) : [];

$viewSubmissions = null;
$submissions = [];
if (!empty($_GET['submissions']) && is_numeric($_GET['submissions'])) {
    $viewSubmissions = $assignmentController->getById((int)$_GET['submissions']);
    if ($viewSubmissions) {
        $submissions = $assignmentController->getSubmissions((int)$_GET['submissions']);
    }
}

include __DIR__ . '/../partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title"><h1>Tareas</h1><p>Gestiona y califica tareas</p></div>
    <div class="topbar-actions">
        <button class="btn btn-primary" onclick="App.openModal('modal-create')">+ Nueva Tarea</button>
    </div>
</div>

<div class="page-content">
    <?php if ($message): ?><div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if ($viewSubmissions): ?>
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <span class="card-title">Entregas — <?= htmlspecialchars($viewSubmissions['title']) ?></span>
            <a href="<?= SITE_URL ?>/views/teacher/assignments.php" class="btn btn-outline btn-sm">← Volver</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Estudiante</th><th>Entregada</th><th>Contenido</th><th>Calificación</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($submissions as $sub): ?>
                <tr>
                    <td><span class="avatar-sm"><?= strtoupper(substr($sub['student_name'],0,2)) ?></span><?= htmlspecialchars($sub['student_name']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($sub['submitted_at'])) ?></td>
                    <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($sub['content'] ?: '-') ?></td>
                    <td><?= $sub['score'] !== null ? '<span class="badge badge-green">' . $sub['score'] . '</span>' : '<span class="badge badge-gray">Sin calificar</span>' ?></td>
                    <td>
                        <button class="btn btn-outline btn-xs" onclick="openGrade(<?= htmlspecialchars(json_encode($sub)) ?>, <?= (float)$viewSubmissions['max_score'] ?>)">✎ Calificar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($submissions)): ?>
                <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">📝</div><p>No hay entregas aún.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><span class="card-title">Mis Tareas (<?= count($assignments) ?>)</span></div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Tarea</th><th>Curso</th><th>Fecha Límite</th><th>Puntaje Máx.</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($assignments as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['title']) ?></td>
                    <td><?= htmlspecialchars($a['course_name']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($a['due_date'])) ?></td>
                    <td><?= $a['max_score'] ?></td>
                    <td><?= strtotime($a['due_date']) > time() ? '<span class="badge badge-green">Activa</span>' : '<span class="badge badge-red">Vencida</span>' ?></td>
                    <td style="white-space:nowrap">
                        <a href="?submissions=<?= $a['id'] ?>" class="btn btn-outline btn-xs">📋 Entregas</a>
                        <button class="btn btn-outline btn-xs" onclick="openEdit(<?= htmlspecialchars(json_encode($a)) ?>)">✎</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar esta tarea?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($assignments)): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">📝</div><p>No has creado tareas aún.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-create">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Nueva Tarea</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group col-span-2"><label class="form-label">Título <span class="required">*</span></label><input type="text" name="title" class="form-control" required></div>
                    <div class="form-group">
                        <label class="form-label">Curso <span class="required">*</span></label>
                        <select name="course_id" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($myCourses as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Puntaje máximo</label><input type="number" name="max_score" class="form-control" value="100" min="1" step="0.5"></div>
                    <div class="form-group col-span-2"><label class="form-label">Fecha límite <span class="required">*</span></label><input type="datetime-local" name="due_date" class="form-control" required></div>
                    <div class="form-group col-span-2"><label class="form-label">Descripción</label><textarea name="description" class="form-control" rows="4"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Tarea</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Editar Tarea</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group col-span-2"><label class="form-label">Título <span class="required">*</span></label><input type="text" name="title" id="edit-title" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Puntaje máximo</label><input type="number" name="max_score" id="edit-max_score" class="form-control" step="0.5"></div>
                    <div class="form-group"><label class="form-label">Fecha límite <span class="required">*</span></label><input type="datetime-local" name="due_date" id="edit-due_date" class="form-control" required></div>
                    <div class="form-group col-span-2"><label class="form-label">Descripción</label><textarea name="description" id="edit-description" class="form-control" rows="4"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal-grade">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Calificar Entrega</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="grade">
            <input type="hidden" name="submission_id" id="grade-id">
            <div class="modal-body">
                <div class="form-group"><label class="form-label">Puntaje <span class="required">*</span></label><input type="number" name="score" id="grade-score" class="form-control" required step="0.5" min="0"></div>
                <div class="form-group"><label class="form-label">Retroalimentación</label><textarea name="feedback" id="grade-feedback" class="form-control" rows="4"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-success">Guardar Calificación</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(a) {
    document.getElementById('edit-id').value = a.id;
    document.getElementById('edit-title').value = a.title;
    document.getElementById('edit-max_score').value = a.max_score;
    document.getElementById('edit-due_date').value = a.due_date ? a.due_date.replace(' ','T').slice(0,16) : '';
    document.getElementById('edit-description').value = a.description || '';
    App.openModal('modal-edit');
}
function openGrade(sub, maxScore) {
    document.getElementById('grade-id').value = sub.id;
    document.getElementById('grade-score').max = maxScore;
    document.getElementById('grade-score').value = sub.score || '';
    document.getElementById('grade-feedback').value = sub.feedback || '';
    App.openModal('modal-grade');
}
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
