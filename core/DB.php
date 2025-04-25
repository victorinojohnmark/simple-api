<?php

class DB {
    private static $pdo;

    public static function connect() {
        if (!self::$pdo) {
            $config = require './config/database.php';
            self::$pdo = new PDO($config['dsn'], $config['user'], $config['password']);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    public static function query($query, $params = []) {
        $stmt = self::connect()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
}