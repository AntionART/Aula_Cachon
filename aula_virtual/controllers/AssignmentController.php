<?php
require_once __DIR__ . '/../models/AssignmentModel.php';

class AssignmentController {
    private $assignmentModel;

    public function __construct() {
        $this->assignmentModel = new AssignmentModel();
    }

    public function getByCourse($courseId) {
        return $this->assignmentModel->getByCourse((int)$courseId);
    }

    public function getByTeacher($teacherId) {
        return $this->assignmentModel->getByTeacher((int)$teacherId);
    }

    public function getByStudent($studentId) {
        return $this->assignmentModel->getByStudent((int)$studentId);
    }

    public function getById($id) {
        return $this->assignmentModel->getById((int)$id);
    }

    public function create($data) {
        $data['title'] = htmlspecialchars(trim($data['title']));
        $data['max_score'] = (float)($data['max_score'] ?? 100);

        if (empty($data['title']) || empty($data['due_date'])) {
            return ['success' => false, 'message' => 'Título y fecha límite son requeridos.'];
        }
        $this->assignmentModel->create($data);
        return ['success' => true, 'message' => 'Tarea creada correctamente.'];
    }

    public function update($id, $data) {
        $data['title'] = htmlspecialchars(trim($data['title']));
        $data['max_score'] = (float)($data['max_score'] ?? 100);

        if (empty($data['title']) || empty($data['due_date'])) {
            return ['success' => false, 'message' => 'Título y fecha límite son requeridos.'];
        }
        $this->assignmentModel->update((int)$id, $data);
        return ['success' => true, 'message' => 'Tarea actualizada.'];
    }

    public function delete($id) {
        $result = $this->assignmentModel->delete((int)$id);
        if ($result) return ['success' => true, 'message' => 'Tarea eliminada.'];
        return ['success' => false, 'message' => 'Error al eliminar.'];
    }

    public function submit($assignmentId, $studentId, $content, $file = null) {
        $content = htmlspecialchars(trim($content));
        $filePath = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg'];
            if (in_array(strtolower($ext), $allowed) && $file['size'] <= 5242880) {
                $filename = uniqid('sub_') . '.' . $ext;
                $dest = UPLOAD_PATH . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $filePath = $filename;
                }
            }
        }
        $this->assignmentModel->submit((int)$assignmentId, (int)$studentId, $content, $filePath);
        return ['success' => true, 'message' => 'Tarea entregada correctamente.'];
    }

    public function getSubmissions($assignmentId) {
        return $this->assignmentModel->getSubmissions((int)$assignmentId);
    }

    public function gradeSubmission($submissionId, $score, $feedback) {
        $feedback = htmlspecialchars(trim($feedback));
        $score = (float)$score;
        $this->assignmentModel->gradeSubmission((int)$submissionId, $score, $feedback);
        return ['success' => true, 'message' => 'Calificación guardada.'];
    }
}
