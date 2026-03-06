<?php
require_once __DIR__ . '/../database/Database.php';

class TeacherModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT t.*, u.name, u.email, u.active FROM teachers t JOIN users u ON t.user_id = u.id ORDER BY u.name");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT t.*, u.name, u.email FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("SELECT t.*, u.name, u.email FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function create($userId, $data) {
        $code = 'TCH-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $this->db->prepare("INSERT INTO teachers (user_id, code, specialty, phone, bio) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $code, $data['specialty'], $data['phone'], $data['bio']]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE teachers SET specialty = ?, phone = ?, bio = ? WHERE id = ?");
        return $stmt->execute([$data['specialty'], $data['phone'], $data['bio'], $id]);
    }

    public function delete($id) {
        $teacher = $this->getById($id);
        if ($teacher) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$teacher['user_id']]);
        }
        return false;
    }

    public function count() {
        return $this->db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    }
}
