<?php

class AuthController {
    public function register() {
        $data = $this->getRequestData();
        try {
            User::create($data);
            echo json_encode(['message' => 'User registered successfully']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function login() {
        $data = $this->getRequestData();
        $user = User::find(['username' => $data['username']]);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['message' => 'Login successful', 'user' => $user]);
    }

    public function getAuthenticatedUser() {
        if (isset($_SESSION['user_id'])) {
            $user = User::find(['id' => $_SESSION['user_id']]);
            echo json_encode(['user' => $user]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'User not authenticated']);
        }
    }

    private function getRequestData() {
        return isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
            ? json_decode(file_get_contents('php://input'), true)
            : $_POST;
    }


	public function logout() {
        // Destroy user session
        session_destroy();
        echo json_encode(['message' => 'Logged out successfully']);
    }
}