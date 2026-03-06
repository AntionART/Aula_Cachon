<?php
require_once __DIR__ . '/../models/CourseModel.php';

class CourseController {
    private $courseModel;

    public function __construct() {
        $this->courseModel = new CourseModel();
    }

    public function getAll() {
        return $this->courseModel->getAll();
    }

    public function getById($id) {
        return $this->courseModel->getById((int)$id);
    }

    public function getByTeacher($teacherId) {
        return $this->courseModel->getByTeacher((int)$teacherId);
    }

    public function getByStudent($studentId) {
        return $this->courseModel->getByStudent((int)$studentId);
    }

    public function create($data) {
        $data['name'] = htmlspecialchars(trim($data['name']));
        $data['max_students'] = (int)($data['max_students'] ?? 30);

        if (empty($data['name']) || empty($data['subject_id']) || empty($data['teacher_id'])) {
            return ['success' => false, 'message' => 'Nombre, materia y profesor son requeridos.'];
        }
        $this->courseModel->create($data);
        return ['success' => true, 'message' => 'Curso creado correctamente.'];
    }

    public function update($id, $data) {
        $data['name'] = htmlspecialchars(trim($data['name']));
        $data['max_students'] = (int)($data['max_students'] ?? 30);
        $data['active'] = isset($data['active']) ? 1 : 0;

        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'El nombre es requerido.'];
        }
        $this->courseModel->update((int)$id, $data);
        return ['success' => true, 'message' => 'Curso actualizado.'];
    }

    public function delete($id) {
        $result = $this->courseModel->delete((int)$id);
        if ($result) return ['success' => true, 'message' => 'Curso eliminado.'];
        return ['success' => false, 'message' => 'Error al eliminar.'];
    }

    public function enrollStudent($courseId, $studentId) {
        $this->courseModel->enrollStudent((int)$courseId, (int)$studentId);
        return ['success' => true, 'message' => 'Estudiante matriculado.'];
    }

    public function unenrollStudent($courseId, $studentId) {
        $this->courseModel->unenrollStudent((int)$courseId, (int)$studentId);
        return ['success' => true, 'message' => 'Estudiante desmatriculado.'];
    }

    public function getStudents($courseId) {
        return $this->courseModel->getStudents((int)$courseId);
    }
}
