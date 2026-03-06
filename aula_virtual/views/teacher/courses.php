<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/CourseController.php';
require_once __DIR__ . '/../../models/TeacherModel.php';

$pageTitle = 'Mis Cursos';
$activePage = 'teacher_courses';

Auth::requireRole('teacher');

$teacherModel = new TeacherModel();
$courseController = new CourseController();

$teacher = $teacherModel->getByUserId(Auth::getUserId());
$courses = $teacher ? $courseController->getByTeacher($teacher['id']) : [];

$selectedCourse = null;
$students = [];
if (!empty($_GET['course']) && is_numeric($_GET['course'])) {
    $selectedCourse = $courseController->getById((int)$_GET['course']);
    if ($selectedCourse && $teacher && $selectedCourse['teacher_id'] == $teacher['id']) {
        $students = $courseController->getStudents((int)$_GET['course']);
    } else {
        $selectedCourse = null;
    }
}

include __DIR__ . '/../partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title"><h1>Mis Cursos</h1><p>Cursos que tienes asignados</p></div>
</div>

<div class="page-content">
    <?php if ($selectedCourse): ?>
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <span class="card-title">Estudiantes — <?= htmlspecialchars($selectedCourse['name']) ?></span>
            <a href="<?= SITE_URL ?>/views/teacher/courses.php" class="btn btn-outline btn-sm">← Volver</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Estudiante</th><th>Código</th><th>Grado</th></tr></thead>
                <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td><span class="avatar-sm"><?= strtoupper(substr($s['name'],0,2)) ?></span><?= htmlspecialchars($s['name']) ?></td>
                    <td><span class="code-chip"><?= htmlspecialchars($s['code']) ?></span></td>
                    <td><?= htmlspecialchars($s['grade'] ?: '-') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                <tr><td colspan="3"><div class="empty-state"><div class="empty-icon">👤</div><p>Sin estudiantes matriculados.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><span class="card-title">Mis Cursos (<?= count($courses) ?>)</span></div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Curso</th><th>Materia</th><th>Período</th><th>Horario</th><th>Aula</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['subject_name']) ?></td>
                    <td><?= htmlspecialchars($c['period']) ?></td>
                    <td><?= htmlspecialchars($c['schedule'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($c['classroom'] ?: '-') ?></td>
                    <td><a href="?course=<?= $c['id'] ?>" class="btn btn-outline btn-xs">👥 Ver Estudiantes</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🏫</div><p>No tienes cursos asignados.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
