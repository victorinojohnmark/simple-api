<?php

class User {
    public static function create($data) {
        // Extract fields from array
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            throw new Exception("Username and password are required");
        }

        // Hash password before storing
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert into database
        return DB::query("INSERT INTO users (username, password) VALUES (:username, :password)", [
            'username' => $username,
            'password' => $hashedPassword
        ]);
    }

    public static function find($data) {
        // Extract ID or username from array
        $id = $data['id'] ?? null;
        $username = $data['username'] ?? null;

        if ($id) {
            return DB::query("SELECT * FROM users WHERE id = :id", ['id' => $id])->fetch();
        } elseif ($username) {
            return DB::query("SELECT * FROM users WHERE username = :username", ['username' => $username])->fetch();
        }

        return null;
    }
}