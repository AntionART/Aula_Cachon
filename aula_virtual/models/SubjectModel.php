<?php
require_once __DIR__ . '/../database/Database.php';

class SubjectModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM subjects ORDER BY name");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO subjects (name, code, description, credits) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['code'], $data['description'], $data['credits']]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE subjects SET name = ?, code = ?, description = ?, credits = ?, active = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['code'], $data['description'], $data['credits'], $data['active'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM subjects WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function count() {
        return $this->db->query("SELECT COUNT(*) FROM subjects WHERE active = 1")->fetchColumn();
    }

    public function codeExists($code, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM subjects WHERE code = ? AND id != ?");
            $stmt->execute([$code, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM subjects WHERE code = ?");
            $stmt->execute([$code]);
        }
        return $stmt->fetch() !== false;
    }
}
