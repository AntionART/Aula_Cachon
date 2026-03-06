<?php
require_once __DIR__ . '/../models/SubjectModel.php';

class SubjectController {
    private $subjectModel;

    public function __construct() {
        $this->subjectModel = new SubjectModel();
    }

    public function getAll() {
        return $this->subjectModel->getAll();
    }

    public function getById($id) {
        return $this->subjectModel->getById((int)$id);
    }

    public function create($data) {
        $data['name'] = htmlspecialchars(trim($data['name']));
        $data['code'] = strtoupper(htmlspecialchars(trim($data['code'])));
        $data['credits'] = (int)$data['credits'];

        if (empty($data['name']) || empty($data['code'])) {
            return ['success' => false, 'message' => 'Nombre y código son requeridos.'];
        }
        if ($this->subjectModel->codeExists($data['code'])) {
            return ['success' => false, 'message' => 'El código ya existe.'];
        }
        $this->subjectModel->create($data);
        return ['success' => true, 'message' => 'Materia creada correctamente.'];
    }

    public function update($id, $data) {
        $data['name'] = htmlspecialchars(trim($data['name']));
        $data['code'] = strtoupper(htmlspecialchars(trim($data['code'])));
        $data['credits'] = (int)$data['credits'];
        $data['active'] = isset($data['active']) ? 1 : 0;

        if (empty($data['name']) || empty($data['code'])) {
            return ['success' => false, 'message' => 'Nombre y código son requeridos.'];
        }
        if ($this->subjectModel->codeExists($data['code'], (int)$id)) {
            return ['success' => false, 'message' => 'El código ya existe.'];
        }
        $this->subjectModel->update((int)$id, $data);
        return ['success' => true, 'message' => 'Materia actualizada.'];
    }

    public function delete($id) {
        $result = $this->subjectModel->delete((int)$id);
        if ($result) return ['success' => true, 'message' => 'Materia eliminada.'];
        return ['success' => false, 'message' => 'Error al eliminar. Puede tener cursos asociados.'];
    }
}
