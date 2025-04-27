<?php

class UserModel extends Model {
    protected static $tableName = 'users';

    protected static $casts = [
        'id' => 'int',
        'created_at' => 'int',
    ];

    public static function createUser($data) {
		// Hash password before storing
		$data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // create a new user in the database
        self::create("INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, :created_at)", $data);

        $newUser = self::fetchRecord("SELECT id, name, email, created_at FROM users WHERE email = :email", ['email' => $data['email']]);
		
		return $newUser;
	}

    public static function find($data, $withPassword = false) {

        $query = "SELECT * FROM users WHERE ";
        $params = [];

        if (isset($data['id'])) {
            $query .= "id = :id";
            $params['id'] = $data['id'];
        } elseif (isset($data['email'])) {
            $query .= "email = :email";
            $params['email'] = $data['email'];
        }

        $user = self::fetchRecord($query, $params);
        if(!$withPassword) {
            unset($user['password']); // Remove password from user data before sending it in the response
        }
		
		return $user;
    }
}