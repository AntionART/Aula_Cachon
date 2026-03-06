<?php
require_once __DIR__ . '/../database/Database.php';

class AssignmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByCourse($courseId) {
        $stmt = $this->db->prepare("SELECT a.*, u.name AS teacher_name FROM assignments a JOIN teachers t ON a.teacher_id = t.id JOIN users u ON t.user_id = u.id WHERE a.course_id = ? ORDER BY a.due_date");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function getByTeacher($teacherId) {
        $stmt = $this->db->prepare("SELECT a.*, c.name AS course_name FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.teacher_id = ? ORDER BY a.due_date DESC");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public function getByStudent($studentId) {
        $stmt = $this->db->prepare("SELECT a.*, c.name AS course_name, sub.id AS submission_id, sub.score, sub.submitted_at FROM assignments a JOIN courses c ON a.course_id = c.id JOIN course_students cs ON c.id = cs.course_id LEFT JOIN submissions sub ON a.id = sub.assignment_id AND sub.student_id = ? WHERE cs.student_id = ? ORDER BY a.due_date");
        $stmt->execute([$studentId, $studentId]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT a.*, c.name AS course_name FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO assignments (course_id, teacher_id, title, description, due_date, max_score) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['course_id'], $data['teacher_id'], $data['title'], $data['description'], $data['due_date'], $data['max_score']]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE assignments SET title = ?, description = ?, due_date = ?, max_score = ? WHERE id = ?");
        return $stmt->execute([$data['title'], $data['description'], $data['due_date'], $data['max_score'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM assignments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function submit($assignmentId, $studentId, $content, $filePath = null) {
        $stmt = $this->db->prepare("INSERT INTO submissions (assignment_id, student_id, content, file_path) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content), file_path = COALESCE(VALUES(file_path), file_path), submitted_at = NOW()");
        return $stmt->execute([$assignmentId, $studentId, $content, $filePath]);
    }

    public function getSubmissions($assignmentId) {
        $stmt = $this->db->prepare("SELECT sub.*, u.name AS student_name, s.code FROM submissions sub JOIN students s ON sub.student_id = s.id JOIN users u ON s.user_id = u.id WHERE sub.assignment_id = ?");
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll();
    }

    public function gradeSubmission($submissionId, $score, $feedback) {
        $stmt = $this->db->prepare("UPDATE submissions SET score = ?, feedback = ?, graded_at = NOW() WHERE id = ?");
        return $stmt->execute([$score, $feedback, $submissionId]);
    }

    public function getSubmission($assignmentId, $studentId) {
        $stmt = $this->db->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
        $stmt->execute([$assignmentId, $studentId]);
        return $stmt->fetch();
    }
}
