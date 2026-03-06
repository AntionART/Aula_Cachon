<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/TeacherModel.php';
require_once __DIR__ . '/../models/SubjectModel.php';
require_once __DIR__ . '/../models/CourseModel.php';
require_once __DIR__ . '/../models/AssignmentModel.php';
require_once __DIR__ . '/../models/TeacherModel.php';

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

Auth::requireLogin();
$role = Auth::getRole();
$userId = Auth::getUserId();

$studentModel = new StudentModel();
$teacherModel = new TeacherModel();
$subjectModel = new SubjectModel();
$courseModel = new CourseModel();
$assignmentModel = new AssignmentModel();

$stats = [];
if ($role === 'admin') {
    $stats = [
        ['icon' => '👤', 'label' => 'Estudiantes', 'value' => $studentModel->count(), 'color' => 'blue'],
        ['icon' => '👨‍🏫', 'label' => 'Profesores', 'value' => $teacherModel->count(), 'color' => 'green'],
        ['icon' => '📚', 'label' => 'Materias', 'value' => $subjectModel->count(), 'color' => 'orange'],
        ['icon' => '🏫', 'label' => 'Cursos Activos', 'value' => $courseModel->count(), 'color' => 'red'],
    ];
    $recentCourses = $courseModel->getAll();
} elseif ($role === 'teacher') {
    $teacher = $teacherModel->getByUserId($userId);
    $myCourses = $teacher ? $courseModel->getByTeacher($teacher['id']) : [];
    $myAssignments = $teacher ? $assignmentModel->getByTeacher($teacher['id']) : [];
    $stats = [
        ['icon' => '🏫', 'label' => 'Mis Cursos', 'value' => count($myCourses), 'color' => 'blue'],
        ['icon' => '📝', 'label' => 'Tareas Creadas', 'value' => count($myAssignments), 'color' => 'green'],
    ];
} elseif ($role === 'student') {
    $student = $studentModel->getByUserId($userId);
    $myCourses = $student ? $courseModel->getByStudent($student['id']) : [];
    $myAssignments = $student ? $assignmentModel->getByStudent($student['id']) : [];
    $pending = array_filter($myAssignments, fn($a) => !$a['submission_id'] && strtotime($a['due_date']) > time());
    $stats = [
        ['icon' => '📖', 'label' => 'Mis Cursos', 'value' => count($myCourses), 'color' => 'blue'],
        ['icon' => '✏️', 'label' => 'Tareas Pendientes', 'value' => count($pending), 'color' => 'orange'],
        ['icon' => '✅', 'label' => 'Entregadas', 'value' => count(array_filter($myAssignments, fn($a) => $a['submission_id'])), 'color' => 'green'],
    ];
}

include __DIR__ . '/partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title">
        <h1>Dashboard</h1>
        <p><?= date('l, d \d\e F \d\e Y') ?></p>
    </div>
    <div class="topbar-actions">
        <span class="badge badge-<?= $role === 'admin' ? 'red' : ($role === 'teacher' ? 'green' : 'blue') ?>">
            <?= $role === 'admin' ? 'Admin' : ($role === 'teacher' ? 'Profesor' : 'Estudiante') ?>
        </span>
    </div>
</div>

<div class="page-content">
    <div class="stats-grid">
        <?php foreach ($stats as $stat): ?>
        <div class="stat-card">
            <div class="stat-icon <?= $stat['color'] ?>"><?= $stat['icon'] ?></div>
            <div>
                <div class="stat-value"><?= $stat['value'] ?></div>
                <div class="stat-label"><?= $stat['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($role === 'admin'): ?>
    <div class="card">
        <div class="card-header">
            <span class="card-title">Cursos Recientes</span>
            <a href="<?= SITE_URL ?>/views/admin/courses.php" class="btn btn-outline btn-sm">Ver todos →</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Curso</th><th>Materia</th><th>Profesor</th><th>Período</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($recentCourses, 0, 8) as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><span class="code-chip"><?= htmlspecialchars($c['subject_code']) ?></span> <?= htmlspecialchars($c['subject_name']) ?></td>
                    <td><?= htmlspecialchars($c['teacher_name']) ?></td>
                    <td><?= htmlspecialchars($c['period']) ?></td>
                    <td><span class="badge <?= $c['active'] ? 'badge-green' : 'badge-gray' ?>"><?= $c['active'] ? 'Activo' : 'Inactivo' ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentCourses)): ?>
                <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">🏫</div><p>No hay cursos registrados.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php elseif ($role === 'teacher'): ?>
    <div class="card">
        <div class="card-header">
            <span class="card-title">Mis Cursos</span>
            <a href="<?= SITE_URL ?>/views/teacher/courses.php" class="btn btn-outline btn-sm">Ver todos →</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Curso</th><th>Materia</th><th>Horario</th></tr></thead>
                <tbody>
                <?php foreach ($myCourses as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['subject_name']) ?></td>
                    <td><?= htmlspecialchars($c['schedule'] ?: '-') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($myCourses)): ?>
                <tr><td colspan="3"><div class="empty-state"><div class="empty-icon">🏫</div><p>Aún no tienes cursos asignados.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php elseif ($role === 'student'): ?>
    <div class="card">
        <div class="card-header">
            <span class="card-title">Mis Tareas</span>
            <a href="<?= SITE_URL ?>/views/student/assignments.php" class="btn btn-outline btn-sm">Ver todas →</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Tarea</th><th>Curso</th><th>Entrega</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($myAssignments, 0, 8) as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['title']) ?></td>
                    <td><?= htmlspecialchars($a['course_name']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($a['due_date'])) ?></td>
                    <td>
                        <?php if ($a['submission_id']): ?>
                            <span class="badge badge-green">Entregada <?= $a['score'] !== null ? '(' . $a['score'] . ')' : '' ?></span>
                        <?php else: ?>
                            <?php echo strtotime($a['due_date']) < time() ? '<span class="badge badge-red">Vencida</span>' : '<span class="badge badge-orange">Pendiente</span>'; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($myAssignments)): ?>
                <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">📝</div><p>No tienes tareas asignadas.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
