<?php
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../config/Auth.php';

class StudentController {
    private $studentModel;
    private $userModel;

    public function __construct() {
        $this->studentModel = new StudentModel();
        $this->userModel = new UserModel();
    }

    public function getAll() {
        return $this->studentModel->getAll();
    }

    public function getById($id) {
        return $this->studentModel->getById((int)$id);
    }

    public function create($data) {
        $data['name'] = htmlspecialchars(trim($data['name']));
        $data['email'] = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'Todos los campos requeridos deben completarse.'];
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email inválido.'];
        }
        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'El email ya existe.'];
        }
        $data['role'] = 'student';
        $userId = $this->userModel->create($data);
        $this->studentModel->create($userId, $data);
        return ['success' => true, 'message' => 'Estudiante creado correctamente.'];
    }

    public function update($id, $data) {
        $student = $this->studentModel->getById((int)$id);
        if (!$student) return ['success' => false, 'message' => 'Estudiante no encontrado.'];

        $data['name'] = htmlspecialchars(trim($data['name']));
        $data['email'] = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email inválido.'];
        }
        if ($this->userModel->emailExists($data['email'], $student['user_id'])) {
            return ['success' => false, 'message' => 'El email ya existe.'];
        }
        $this->userModel->update($student['user_id'], $data);
        $this->studentModel->update((int)$id, $data);
        return ['success' => true, 'message' => 'Estudiante actualizado.'];
    }

    public function delete($id) {
        $result = $this->studentModel->delete((int)$id);
        if ($result) return ['success' => true, 'message' => 'Estudiante eliminado.'];
        return ['success' => false, 'message' => 'Error al eliminar.'];
    }
}
