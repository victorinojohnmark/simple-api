<?php

class User {
    public static function create($data) {
		// Hash password before storing
		$data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
	
		// Insert user into database
		DB::query("INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, :created_at)", [
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => $data['password'],

			'created_at' => time(),
		]);

		$newUser = DB::query("SELECT id, name, email, created_at FROM users WHERE email = :email", [
			'email' => $data['email']
		])->fetch(PDO::FETCH_ASSOC);
		
		return $newUser;
		


	}

    public static function find($data) {
        // Check for either ID or username
        if (!isset($data['id']) && !isset($data['username'])) {
            throw new Exception("Either 'id' or 'username' is required.");
        }

        $query = "SELECT * FROM users WHERE ";
        $params = [];

        if (isset($data['id'])) {
            $query .= "id = :id";
            $params['id'] = $data['id'];
        } elseif (isset($data['username'])) {
            $query .= "username = :username";
            $params['username'] = $data['username'];
        }

        $user = DB::query($query, $params)->fetch(PDO::FETCH_ASSOC);
		unset($user['password']);
		
		return $user;

    }
}