<?php

class AuthController {
    public function register() {
		$data = $this->getRequestData();
	
		$rules = [
			'name' => 'required|string|min:3|max:255',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|string|min:6',
			'password_confirmation' => 'required|string|confirm:password'
		];
	
		$validator = new Validator($data, $rules, DB::connect());
	
		if (!$validator->passes()) {
			http_response_code(422);
			echo json_encode([
				'success' => false,
				'errors' => $validator->errors()
			]);
			return;
		}
	
		$user = User::create($data);
	
		// Automatically log the user in by generating a new CSRF token
		Csrf::generateToken(true);
    	$newCsrfToken = $_SESSION['csrf_token'];
		$_SESSION['user_id'] = $user['id'];
	
		echo json_encode([
			'success' => true,
			'message' => 'User registered successfully and logged in.',
			'data' => [
				'user' => $user,
				'csrf_token' => $newCsrfToken // Include the generated token in the response
			]
		]);
	}
	
    public function login() {
        $data = $this->getRequestData();
        $user = User::find(['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

		// Regenerate CSRF token on successful login
		Csrf::generateToken(true);
    	$newCsrfToken = $_SESSION['csrf_token'];

        $_SESSION['user_id'] = $user['id'];
		http_response_code(200); // OK
		echo json_encode([
			'success' => true,
			'message' => 'Login successful',
			'data' => [
				'user' => $user,
				'csrf_token' => $newCsrfToken // Include the generated token in the response
			]
		]);
    }

	public function logout() {
        // Destroy user session
        session_destroy();
        echo json_encode(['message' => 'Logged out successfully']);
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


	
}