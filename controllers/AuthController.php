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

        // Generate JWT
        $payload = ['sub' => $user['id'], 'email' => $user['email']];
        $token = Jwt::generate($payload);

        Response::json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function login() {
        $data = $this->requestData;

        // Find user by email
        $user = UserModel::find(['email' => $data['email']], true);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
            return;
        }

        unset($user['password']); // Remove sensitive password data

        // Generate JWT
        $payload = ['sub' => $user['id'], 'email' => $user['email']];
        $token = Jwt::generate($payload);

        Response::json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function logout() {
        // No session to destroy in stateless architecture
        Response::json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function getAuthenticatedUser() {
        // Validate JWT (Middleware would typically handle this)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            Response::json(['message' => 'Unauthorized'], 401);
            return;
        }

        $jwt = substr($authHeader, 7); // Extract token
        try {
            $payload = Jwt::validate($jwt);
            $user = UserModel::find(['id' => $payload['sub']]);

            Response::json([
                'success' => true,
                'data' => [
                    'user' => $user
                ]
            ]);
        } catch (Exception $e) {
            Response::json(['message' => $e->getMessage()], 401);
        }
    }
}