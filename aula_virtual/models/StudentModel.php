<?php
require_once __DIR__ . '/../database/Database.php';

class StudentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT s.*, u.name, u.email, u.active FROM students s JOIN users u ON s.user_id = u.id ORDER BY u.name");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT s.*, u.name, u.email, u.role FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("SELECT s.*, u.name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function create($userId, $data) {
        $code = 'STU-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $this->db->prepare("INSERT INTO students (user_id, code, grade, birth_date, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $code, $data['grade'], $data['birth_date'] ?: null, $data['phone'], $data['address']]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE students SET grade = ?, birth_date = ?, phone = ?, address = ? WHERE id = ?");
        return $stmt->execute([$data['grade'], $data['birth_date'] ?: null, $data['phone'], $data['address'], $id]);
    }

    public function delete($id) {
        $student = $this->getById($id);
        if ($student) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$student['user_id']]);
        }
        return false;
    }

    public function count() {
        return $this->db->query("SELECT COUNT(*) FROM students")->fetchColumn();
    }

    public function getNotInCourse($courseId) {
        $stmt = $this->db->prepare("SELECT s.id, u.name, s.code FROM students s JOIN users u ON s.user_id = u.id WHERE s.id NOT IN (SELECT student_id FROM course_students WHERE course_id = ?) ORDER BY u.name");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }
}
