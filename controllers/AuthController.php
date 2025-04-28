<?php

class AuthController extends Controller {

	public function __construct() {
		parent::__construct();
	}

    public function register() {
		$data = $this->requestData;
	
		$rules = [
			'name' => 'required|string|min:3|max:255',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|string|min:6',
			'password_confirmation' => 'required|string|confirm:password'
		];
	
		$validator = new Validator($data, $rules, DB::connect());
	
		if (!$validator->passes()) {

			Response::json([
				'success' => false,
				'message' => 'Validation failed. Please review the errors and try again.',
				'errors' => $validator->errors()
			], 422);
			return;
		}
	
		$data['created_at'] = time();
		$user = UserModel::createUser($data);
	
		// Automatically log the user in by generating a new CSRF token
		Csrf::generateToken(true);
    	$newCsrfToken = $_SESSION['csrf_token'];
		$_SESSION['user_id'] = $user['id'];

		Response::json([
			'success' => true,
			'message' => 'User registered successfully and logged in.',
			'data' => [
				'user' => $user,
				'csrf_token' => $newCsrfToken // Include the generated token in the response
			]
		]);
	}
	
    public function login() {
		$data = $this->requestData;
	
		// Check if user is already logged in
		if (isset($_SESSION['user_id'])) {
			$user = UserModel::find(['id' => $_SESSION['user_id']]);
	
			if ($user) {
				$csrfToken = $_SESSION['csrf_token']; // Retrieve existing CSRF token

				Response::json([
					'success' => false,
					'message' => 'User already logged in',
					'data' => [
						'user' => $user,
						'csrf_token' => $csrfToken // Include the existing token in the response
					]
				], 400);
				return;
			}
		}
	
		// Proceed with the usual login process
		$user = UserModel::find(['email' => $data['email']], true);
	
		if (!$user || !password_verify($data['password'], $user['password'])) {
			Response::json([
				'success' => false,
				'message' => 'Invalid credentials'
			], 401);
			return;
		}
	
		// Regenerate CSRF token on successful login
		Csrf::generateToken(true);
		$newCsrfToken = $_SESSION['csrf_token'];
	
		unset($user['password']); // Remove password from user data before sending it in the response
	
		$_SESSION['user_id'] = $user['id']; // Set user ID in session

		Response::json([
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

		Response::json([
			'success' => true,
			'message' => 'Logged out successfully'
		]);
    }

    public function getAuthenticatedUser() {
        if (isset($_SESSION['user_id'])) {
            $user = UserModel::find(['id' => $_SESSION['user_id']]);

			Response::json([
				'data' => [
					'user' => $user
				]
			]);
        } else {
			Response::json([
				'message' => 'User not authenticated'
			], 401);

        }
    }

}