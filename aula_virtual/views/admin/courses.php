<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/CourseController.php';
require_once __DIR__ . '/../../models/SubjectModel.php';
require_once __DIR__ . '/../../models/TeacherModel.php';
require_once __DIR__ . '/../../models/StudentModel.php';

$pageTitle = 'Cursos';
$activePage = 'courses';

Auth::requireRole('admin');

$courseController = new CourseController();
$subjectModel = new SubjectModel();
$teacherModel = new TeacherModel();
$studentModel = new StudentModel();

$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $result = $courseController->create($_POST);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'update' && !empty($_POST['id'])) {
        $result = $courseController->update((int)$_POST['id'], $_POST);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'delete' && !empty($_POST['id'])) {
        $result = $courseController->delete((int)$_POST['id']);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'enroll' && !empty($_POST['course_id']) && !empty($_POST['student_id'])) {
        $result = $courseController->enrollStudent((int)$_POST['course_id'], (int)$_POST['student_id']);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    } elseif ($action === 'unenroll' && !empty($_POST['course_id']) && !empty($_POST['student_id'])) {
        $result = $courseController->unenrollStudent((int)$_POST['course_id'], (int)$_POST['student_id']);
        $result['success'] ? $message = $result['message'] : $error = $result['message'];
    }
}

$courses = $courseController->getAll();
$subjects = $subjectModel->getAll();
$teachers = $teacherModel->getAll();

$selectedCourse = null;
$enrolledStudents = [];
$availableStudents = [];
if (!empty($_GET['manage']) && is_numeric($_GET['manage'])) {
    $selectedCourse = $courseController->getById((int)$_GET['manage']);
    if ($selectedCourse) {
        $enrolledStudents = $courseController->getStudents((int)$_GET['manage']);
        $availableStudents = $studentModel->getNotInCourse((int)$_GET['manage']);
    }
}

include __DIR__ . '/../partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title"><h1>Cursos</h1><p>Gestiona los cursos del colegio</p></div>
    <div class="topbar-actions">
        <button class="btn btn-primary" onclick="App.openModal('modal-create')">+ Nuevo Curso</button>
    </div>
</div>

<div class="page-content">
    <?php if ($message): ?><div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if ($selectedCourse): ?>
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <span class="card-title">Estudiantes en: <?= htmlspecialchars($selectedCourse['name']) ?></span>
            <a href="<?= SITE_URL ?>/views/admin/courses.php" class="btn btn-outline btn-sm">← Volver</a>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <div>
                    <p style="font-size:0.8rem;font-weight:600;color:var(--text-3);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px">Matriculados (<?= count($enrolledStudents) ?>)</p>
                    <?php if (empty($enrolledStudents)): ?>
                    <div class="empty-state"><div class="empty-icon">👤</div><p>Sin estudiantes matriculados</p></div>
                    <?php else: ?>
                    <?php foreach ($enrolledStudents as $st): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--bg-3);border-radius:var(--radius-sm);margin-bottom:6px;border:1px solid var(--border)">
                        <span style="font-size:0.875rem"><span class="avatar-sm"><?= strtoupper(substr($st['name'],0,2)) ?></span><?= htmlspecialchars($st['name']) ?></span>
                        <form method="POST" style="display:inline" onsubmit="return confirm('¿Desmatricular este estudiante?')">
                            <input type="hidden" name="action" value="unenroll">
                            <input type="hidden" name="course_id" value="<?= $selectedCourse['id'] ?>">
                            <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">✕</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div>
                    <p style="font-size:0.8rem;font-weight:600;color:var(--text-3);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px">Disponibles (<?= count($availableStudents) ?>)</p>
                    <?php if (empty($availableStudents)): ?>
                    <div class="empty-state"><div class="empty-icon">✓</div><p>Todos matriculados</p></div>
                    <?php else: ?>
                    <?php foreach ($availableStudents as $st): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--bg-3);border-radius:var(--radius-sm);margin-bottom:6px;border:1px solid var(--border)">
                        <span style="font-size:0.875rem"><span class="avatar-sm"><?= strtoupper(substr($st['name'],0,2)) ?></span><?= htmlspecialchars($st['name']) ?></span>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="enroll">
                            <input type="hidden" name="course_id" value="<?= $selectedCourse['id'] ?>">
                            <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                            <button type="submit" class="btn btn-success btn-xs">+ Matricular</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Lista de Cursos (<?= count($courses) ?>)</span>
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" class="form-control" placeholder="Buscar curso..." data-search-target="#courses-table">
            </div>
        </div>
        <div class="table-wrapper">
            <table id="courses-table">
                <thead><tr><th>Curso</th><th>Materia</th><th>Profesor</th><th>Período</th><th>Aula</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['subject_name']) ?></td>
                    <td><?= htmlspecialchars($c['teacher_name']) ?></td>
                    <td><?= htmlspecialchars($c['period']) ?></td>
                    <td><?= htmlspecialchars($c['classroom'] ?: '-') ?></td>
                    <td><span class="badge <?= $c['active'] ? 'badge-green' : 'badge-gray' ?>"><?= $c['active'] ? 'Activo' : 'Inactivo' ?></span></td>
                    <td style="white-space:nowrap">
                        <a href="?manage=<?= $c['id'] ?>" class="btn btn-outline btn-xs">👥 Estudiantes</a>
                        <button class="btn btn-outline btn-xs" onclick="openEdit(<?= htmlspecialchars(json_encode($c)) ?>)">✎</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este curso?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?>
                <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🏫</div><p>No hay cursos registrados.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-create">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Nuevo Curso</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group col-span-2"><label class="form-label">Nombre del Curso <span class="required">*</span></label><input type="text" name="name" class="form-control" required placeholder="Ej. Matemáticas 10A"></div>
                    <div class="form-group">
                        <label class="form-label">Materia <span class="required">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($subjects as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Profesor <span class="required">*</span></label>
                        <select name="teacher_id" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($teachers as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Período <span class="required">*</span></label><input type="text" name="period" class="form-control" required placeholder="Ej. 2025-1"></div>
                    <div class="form-group"><label class="form-label">Horario</label><input type="text" name="schedule" class="form-control" placeholder="Ej. Lun-Mié 8:00-10:00"></div>
                    <div class="form-group"><label class="form-label">Aula</label><input type="text" name="classroom" class="form-control" placeholder="Ej. Aula 101"></div>
                    <div class="form-group"><label class="form-label">Cupo máximo</label><input type="number" name="max_students" class="form-control" value="30" min="1"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Curso</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <div class="modal-header"><span class="modal-title">Editar Curso</span><button class="modal-close" data-modal-close>×</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group col-span-2"><label class="form-label">Nombre <span class="required">*</span></label><input type="text" name="name" id="edit-name" class="form-control" required></div>
                    <div class="form-group">
                        <label class="form-label">Materia</label>
                        <select name="subject_id" id="edit-subject_id" class="form-control">
                            <?php foreach ($subjects as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Profesor</label>
                        <select name="teacher_id" id="edit-teacher_id" class="form-control">
                            <?php foreach ($teachers as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Período</label><input type="text" name="period" id="edit-period" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Horario</label><input type="text" name="schedule" id="edit-schedule" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Aula</label><input type="text" name="classroom" id="edit-classroom" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Cupo</label><input type="number" name="max_students" id="edit-max_students" class="form-control"></div>
                    <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;padding-bottom:4px">
                        <input type="checkbox" name="active" id="edit-active" value="1" style="width:18px;height:18px">
                        <label class="form-label" for="edit-active" style="margin:0">Activo</label>
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
function openEdit(c) {
    document.getElementById('edit-id').value = c.id;
    document.getElementById('edit-name').value = c.name;
    document.getElementById('edit-subject_id').value = c.subject_id;
    document.getElementById('edit-teacher_id').value = c.teacher_id;
    document.getElementById('edit-period').value = c.period;
    document.getElementById('edit-schedule').value = c.schedule || '';
    document.getElementById('edit-classroom').value = c.classroom || '';
    document.getElementById('edit-max_students').value = c.max_students;
    document.getElementById('edit-active').checked = c.active == 1;
    App.openModal('modal-edit');
}
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
