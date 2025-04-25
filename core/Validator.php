<?php

class Validator
{
    protected $data;
    protected $rules;
    public $errors = [];
    protected $pdo;
    private $db;
    protected $customFieldNames = [];

    public function __construct(array $data, array $rules, $db = null)
    {
        $this->data  = $data;
        $this->rules = $rules;
        $this->db    = $db;
    }

    public function passes()
    {
        foreach ($this->rules as $field => $rulesString) {
            $rules = explode('|', $rulesString);
            $value = $this->data[$field] ?? null;

            if (isset($_FILES[$field])) {
                // Handle file validation
                $value = $_FILES[$field];
            }

            foreach ($rules as $rule) {
                $ruleName = $rule;
                $param = null;

                if (strpos($rule, ':') !== false) {
                    list($ruleName, $param) = explode(':', $rule, 2);
                }

                $method = 'validate' . ucfirst($ruleName);
                if (method_exists($this, $method)) {
                    $this->$method($field, $value, $param);
                }
            }
        }

        return empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function setCustomFieldNames(array $customFieldNames)
    {
        $this->customFieldNames = $customFieldNames;
    }

    protected function validateRequired($field, $value, $param)
    {
        if (is_array($value) && isset($value['error']) && $value['error'] === UPLOAD_ERR_NO_FILE) {
            $this->addError($field, "The :attribute field is required.");
        } elseif (is_null($value) || trim($value) === '') {
            $this->addError($field, "The :attribute field is required.");
        }
    }

    protected function validateEmail($field, $value, $param)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "The :attribute must be a valid email address.");
        }
    }

    protected function validateMin($field, $value, $param)
    {
        if (strlen($value) < (int)$param) {
            $this->addError($field, "The :attribute must be at least $param characters.");
        }
    }

    protected function validateMax($field, $value, $param)
    {
        if (strlen($value) > (int)$param) {
            $this->addError($field, "The :attribute may not be greater than $param characters.");
        }
    }

    protected function validateSame($field, $value, $param)
    {
        if (!isset($this->data[$param]) || $value !== $this->data[$param]) {
            $this->addError($field, "The :attribute must match $param.");
        }
    }

    protected function validateNumeric($field, $value, $param)
    {
        if (!is_numeric($value)) {
            $this->addError($field, "The :attribute must be numeric.");
        }
    }

    protected function validateInteger($field, $value, $param)
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "The :attribute must be an integer.");
        }
    }

    protected function validateDate($field, $value, $param)
    {
        if (!strtotime($value)) {
            $this->addError($field, "The :attribute is not a valid date.");
        }
    }

    protected function validateRegex($field, $value, $param)
    {
        if (@preg_match($param, '') === false) {
            $this->addError($field, "Invalid regex pattern.");
            return;
        }

        if (!preg_match($param, $value)) {
            $this->addError($field, "The :attribute format is invalid.");
        }
    }

    protected function validateUnique($field, $value, $param) {
		if (!$this->db) {
			throw new Exception("DB handler required for unique validation.");
		}
	
		// Parse parameters
		$parts = explode(',', $param);
		$table = $parts[0] ?? null;
		$column = $parts[1] ?? $field;
	
		if (!$table) {
			throw new Exception("Table name is required for unique validation.");
		}
	
		$excludeId = null;
		$additionalConditions = [];
	
		foreach (array_slice($parts, 2) as $part) {
			if (strpos($part, '=') !== false) {
				list($key, $val) = explode('=', $part, 2);
				$additionalConditions[$key] = $val;
			} elseif (is_numeric($part)) {
				$excludeId = $part;
			}
		}
	
		// Check if the 'deleted_at' column exists
		$query = "SHOW COLUMNS FROM {$table} LIKE 'deleted_at'";
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		$deletedAtExists = $stmt->rowCount() > 0;
	
		// Build query dynamically
		$query = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
		$params = ['value' => $value];
	
		if ($excludeId) {
			$query .= " AND id != :excludeId";
			$params['excludeId'] = $excludeId;
		}
	
		foreach ($additionalConditions as $key => $val) {
			$query .= " AND {$key} = :{$key}";
			$params[$key] = $val;
		}
	
		// Only include 'deleted_at IS NULL' if the column exists
		if ($deletedAtExists) {
			$query .= " AND deleted_at IS NULL";
		}
	
		// Execute query
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);
		$count = $stmt->fetchColumn();
	
		if ($count > 0) {
			$this->addError($field, "The :attribute must be unique.");
		}
	}

    protected function validateExists($field, $value, $param) {
		if (!$this->db) {
			throw new Exception("DB handler required for exists validation.");
		}
	
		// Parse parameters
		$parts = array_filter(array_map('trim', explode(',', $param)));
		$table = $parts[0] ?? null;
		$column = $parts[1] ?? $field;
	
		if (!$table || !$column) {
			$this->addError($field, "Invalid, the :attribute does not exist.");
			return;
		}
	
		// Additional conditions
		$conditions = [$column => $value];
		for ($i = 2; $i < count($parts); $i++) {
			if (strpos($parts[$i], '=') !== false) {
				list($key, $val) = explode('=', $parts[$i], 2);
				$conditions[$key] = $val;
			}
		}
	
		// Check if the 'deleted_at' column exists
		$query = "SHOW COLUMNS FROM {$table} LIKE 'deleted_at'";
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		$deletedAtExists = $stmt->rowCount() > 0;
	
		// Build query dynamically
		$query = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
		$params = ['value' => $value];
	
		foreach ($conditions as $key => $val) {
			$query .= " AND {$key} = :{$key}";
			$params[$key] = $val;
		}
	
		// Only include 'deleted_at IS NULL' if the column exists
		if ($deletedAtExists) {
			$query .= " AND deleted_at IS NULL";
		}
	
		// Execute query
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);
		$count = $stmt->fetchColumn();
	
		if ($count === 0) {
			$this->addError($field, "The :attribute does not exist.");
		}
	}

	protected function validateConfirm($field, $value, $param) {
		// Check if the field to confirm is present in the data
		if (!isset($this->data[$param]) || $value !== $this->data[$param]) {
			$this->addError($field, "The :attribute must match {$param}.");
		}
	}
	
    protected function validateFileType($field, $file, $param)
    {
        $allowedTypes = explode(',', $param);

        if (isset($file['tmp_name']) && !in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
            $this->addError($field, "The :attribute must be one of: " . implode(', ', $allowedTypes) . ".");
        }
    }

    protected function validateFileSize($field, $file, $param)
	{
		$maxSize = (int)$param * 1024; // Convert kilobytes (KB) to bytes

		if (isset($file['size']) && $file['size'] > $maxSize) {
			$this->addError($field, "The :attribute size must be less than " . ($param / 1024) . " MB.");
		}
	}

    protected function addError($field, $message)
    {
        $displayName = $this->customFieldNames[$field] ?? $field;
        $message = str_replace(":attribute", $displayName, $message);
        $this->errors[$field][] = $message;
    }
}
