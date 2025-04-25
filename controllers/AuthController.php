<?php

class AuthController {
    public function register() {
        // Get request data (JSON or form)
        $data = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
            ? json_decode(file_get_contents('php://input'), true)
            : $_POST;

        // Validate required fields
        if (empty($data['username']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password are required']);
            return;
        }

        // Hash the password for security
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // Insert user into the database
        DB::query("INSERT INTO users (username, password) VALUES (:username, :password)", [
            'username' => $data['username'],
            'password' => $hashedPassword
        ]);

        echo json_encode(['message' => 'User registered successfully']);
    }

    public function login() {
        // Get request data
        $data = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
            ? json_decode(file_get_contents('php://input'), true)
            : $_POST;

        // Validate required fields
        if (empty($data['username']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password are required']);
            return;
        }

        // Fetch user from database
        $user = DB::query("SELECT * FROM users WHERE username = :username", ['username' => $data['username']])->fetch();

        // Verify credentials
        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid username or password']);
            return;
        }

        // Generate a session token (or JWT if needed)
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['message' => 'Login successful', 'user' => $user]);
    }

    public function logout() {
        // Destroy user session
        session_destroy();
        echo json_encode(['message' => 'Logged out successfully']);
    }

    public function getAuthenticatedUser() {
        // Check if user is logged in
        if (isset($_SESSION['user_id'])) {
            $user = DB::query("SELECT * FROM users WHERE id = :id", ['id' => $_SESSION['user_id']])->fetch();
            echo json_encode(['user' => $user]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'User not authenticated']);
        }
    }
}