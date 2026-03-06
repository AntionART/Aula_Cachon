<?php
require_once __DIR__ . '/../database/Database.php';

class CourseModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT c.*, s.name AS subject_name, s.code AS subject_code, u.name AS teacher_name FROM courses c JOIN subjects s ON c.subject_id = s.id JOIN teachers t ON c.teacher_id = t.id JOIN users u ON t.user_id = u.id ORDER BY c.name");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT c.*, s.name AS subject_name, u.name AS teacher_name FROM courses c JOIN subjects s ON c.subject_id = s.id JOIN teachers t ON c.teacher_id = t.id JOIN users u ON t.user_id = u.id WHERE c.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByTeacher($teacherId) {
        $stmt = $this->db->prepare("SELECT c.*, s.name AS subject_name FROM courses c JOIN subjects s ON c.subject_id = s.id WHERE c.teacher_id = ? ORDER BY c.name");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public function getByStudent($studentId) {
        $stmt = $this->db->prepare("SELECT c.*, s.name AS subject_name, u.name AS teacher_name FROM courses c JOIN subjects s ON c.subject_id = s.id JOIN teachers t ON c.teacher_id = t.id JOIN users u ON t.user_id = u.id JOIN course_students cs ON c.id = cs.course_id WHERE cs.student_id = ? ORDER BY c.name");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO courses (name, subject_id, teacher_id, period, schedule, classroom, max_students) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['subject_id'], $data['teacher_id'], $data['period'], $data['schedule'], $data['classroom'], $data['max_students']]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE courses SET name = ?, subject_id = ?, teacher_id = ?, period = ?, schedule = ?, classroom = ?, max_students = ?, active = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['subject_id'], $data['teacher_id'], $data['period'], $data['schedule'], $data['classroom'], $data['max_students'], $data['active'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function enrollStudent($courseId, $studentId) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO course_students (course_id, student_id) VALUES (?, ?)");
        return $stmt->execute([$courseId, $studentId]);
    }

    public function unenrollStudent($courseId, $studentId) {
        $stmt = $this->db->prepare("DELETE FROM course_students WHERE course_id = ? AND student_id = ?");
        return $stmt->execute([$courseId, $studentId]);
    }

    public function getStudents($courseId) {
        $stmt = $this->db->prepare("SELECT s.id, u.name, s.code, s.grade FROM course_students cs JOIN students s ON cs.student_id = s.id JOIN users u ON s.user_id = u.id WHERE cs.course_id = ? ORDER BY u.name");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function count() {
        return $this->db->query("SELECT COUNT(*) FROM courses WHERE active = 1")->fetchColumn();
    }
}
