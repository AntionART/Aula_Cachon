<?php
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
        Auth::startSession();
    }

    public function login($email, $password) {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email inválido.'];
        }
        $user = $this->userModel->findByEmail($email);
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Credenciales incorrectas.'];
        }
        Auth::login($user);
        return ['success' => true, 'role' => $user['role']];
    }

    public function register($data) {
        $data['name'] = htmlspecialchars(trim($data['name']));
        $data['email'] = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);

        if (empty($data['name']) || strlen($data['name']) < 3) {
            return ['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.'];
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email inválido.'];
        }
        if (strlen($data['password']) < 6) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.'];
        }
        if ($data['password'] !== $data['password_confirm']) {
            return ['success' => false, 'message' => 'Las contraseñas no coinciden.'];
        }
        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'El email ya está registrado.'];
        }
        $userId = $this->userModel->create($data);
        return ['success' => true, 'user_id' => $userId];
    }

    public function logout() {
        Auth::logout();
    }
}
