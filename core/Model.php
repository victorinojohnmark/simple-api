<?php

abstract class Model {
    protected static $db;
    protected static $casts = []; // Optional static casting rules
	protected static $tableName; // Explicistly define the table name in the model


    // Initialize the database connection
    protected static function initDB() {
        if (!isset(self::$db)) {
            self::$db = DB::connect();
            self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            self::$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        }
    }

    // Static method to fetch a list of records
    public static function fetchList(string $query, array $params = []): array {
        self::initDB(); // Ensure the database connection is initialized

        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($results === false) {
			return [];
		}

        return array_map([self::class, 'applyCasts'], $results);
    }

    // Static method to fetch a single record
    public static function fetchRecord(string $query, array $params = []): array {
        self::initDB();

        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($result === false) {
			return [];
		}

        return self::applyCasts($result);
    }

    // Apply type casting if $casts is defined
    protected static function applyCasts(array $data): array {
        if (!$data || empty(static::$casts)) {
            return $data; // Skip casting if $casts is not defined or data is null
        }

        foreach (static::$casts as $attribute => $type) {
            if (isset($data[$attribute])) {
                switch ($type) {
                    case 'int':
                        $data[$attribute] = (int) $data[$attribute];
                        break;
					case 'string':
						$data[$attribute] = (string) $data[$attribute];
						break;
                    case 'float':
                        $data[$attribute] = (float) $data[$attribute];
                        break;
                    case 'bool':
                        $data[$attribute] = (bool) $data[$attribute];
                        break;
                    case 'datetime':
                        $data[$attribute] = new DateTime($data[$attribute]);
                        break;
                }
            }
        }
        return $data;
    }

    // Insert a new record
    public static function create(string $query, array $params) {
		self::initDB();

		// Filter parameters based on query placeholders
		$filteredParams = self::filterParams($query, $params);

		$stmt = self::$db->prepare($query);
		if (!$stmt->execute($filteredParams)) {
			throw new PDOException("Failed to execute query");
		}
	
		$lastInsertId = self::$db->lastInsertId();
		if ($lastInsertId) {
			return self::fetchRecord("SELECT * FROM " . self::getTableName() . " WHERE id = :id", ['id' => $lastInsertId]);
		}

		return null;	
    }

    // Update an existing record
    public static function update(string $query, array $params) {
		self::initDB();

		$filteredParams = self::filterParams($query, $params);
        $filteredParams = self::filterParams($query, $params);

		$stmt = self::$db->prepare($query);
		if (!$stmt->execute($filteredParams)) {
			throw new PDOException("Failed to execute query");
		}

		if (isset($params['id'])) {
			return self::fetchRecord("SELECT * FROM " . self::getTableName() . " WHERE id = :id", ['id' => $params['id']]);
		}
	
		return null;

    }

    // Delete a record
    public static function delete(string $query, array $params): bool {
		self::initDB();

        $stmt = self::$db->prepare($query);
        return $stmt->execute($params);
    }

	protected static function filterParams(string $query, array $params): array {
		// Extract placeholders from the query
		preg_match_all('/:(\w+)/', $query, $matches);
		$placeholders = $matches[1];
	
		// Filter the params array to include only keys matching the placeholders
		$filteredParams = array_filter($params, function ($key) use ($placeholders) {
			return in_array($key, $placeholders);
		}, ARRAY_FILTER_USE_KEY);
	
		return $filteredParams;
	}

	protected static function getTableName(): string {
        if (isset(static::$tableName)) {
            return static::$tableName;
        }

        throw new Exception("Table name is not defined in the model.");
    }

	
}