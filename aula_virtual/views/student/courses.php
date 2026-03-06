<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/CourseController.php';
require_once __DIR__ . '/../../models/StudentModel.php';

$pageTitle = 'Mis Cursos';
$activePage = 'student_courses';

Auth::requireRole('student');

$studentModel = new StudentModel();
$courseController = new CourseController();

$student = $studentModel->getByUserId(Auth::getUserId());
$courses = $student ? $courseController->getByStudent($student['id']) : [];

include __DIR__ . '/../partials/header.php';
?>
<div class="topbar">
    <div class="topbar-title"><h1>Mis Cursos</h1><p>Cursos en los que estás matriculado</p></div>
</div>

<div class="page-content">
    <?php if (empty($courses)): ?>
    <div class="card"><div class="card-body"><div class="empty-state"><div class="empty-icon">📖</div><p>Aún no estás matriculado en ningún curso. Contacta al administrador.</p></div></div></div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
        <?php foreach ($courses as $c): ?>
        <div class="card" style="transition:border-color 0.2s">
            <div style="padding:20px">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
                    <div style="width:44px;height:44px;background:var(--primary-dim);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px">📖</div>
                    <span class="badge badge-green">Activo</span>
                </div>
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($c['name']) ?></h3>
                <p style="font-size:0.82rem;color:var(--text-3);margin-bottom:14px"><?= htmlspecialchars($c['subject_name']) ?></p>
                <div style="display:flex;flex-direction:column;gap:4px;font-size:0.8rem;color:var(--text-2)">
                    <span>👨‍🏫 <?= htmlspecialchars($c['teacher_name']) ?></span>
                    <?php if ($c['schedule']): ?><span>🕐 <?= htmlspecialchars($c['schedule']) ?></span><?php endif; ?>
                    <?php if ($c['classroom']): ?><span>📍 <?= htmlspecialchars($c['classroom']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
