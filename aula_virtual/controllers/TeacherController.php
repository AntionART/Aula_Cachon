<?php
require_once __DIR__ . '/../models/TeacherModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class TeacherController {
    private $teacherModel;
    private $userModel;

    public function __construct() {
        $this->teacherModel = new TeacherModel();
        $this->userModel = new UserModel();
    }

    public function getAll() {
        return $this->teacherModel->getAll();
    }

    public function getById($id) {
        return $this->teacherModel->getById((int)$id);
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
        $data['role'] = 'teacher';
        $userId = $this->userModel->create($data);
        $this->teacherModel->create($userId, $data);
        return ['success' => true, 'message' => 'Profesor creado correctamente.'];
    }

    public function update($id, $data) {
        $teacher = $this->teacherModel->getById((int)$id);
        if (!$teacher) return ['success' => false, 'message' => 'Profesor no encontrado.'];

        $data['name'] = htmlspecialchars(trim($data['name']));
        $data['email'] = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email inválido.'];
        }
        if ($this->userModel->emailExists($data['email'], $teacher['user_id'])) {
            return ['success' => false, 'message' => 'El email ya existe.'];
        }
        $this->userModel->update($teacher['user_id'], $data);
        $this->teacherModel->update((int)$id, $data);
        return ['success' => true, 'message' => 'Profesor actualizado.'];
    }

    public function delete($id) {
        $result = $this->teacherModel->delete((int)$id);
        if ($result) return ['success' => true, 'message' => 'Profesor eliminado.'];
        return ['success' => false, 'message' => 'Error al eliminar.'];
    }
}
