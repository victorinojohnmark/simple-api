<?php

class File
{
    protected $uploadDir;

    public function __construct($uploadDir = UPLOAD_DIR)
    {
        $this->uploadDir = $uploadDir;

        // Ensure the upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true); // Create directory with appropriate permissions
        }
    }

    public function upload($file, $field)
	{
		if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
			return false; // Return false if file upload failed
		}

		// Create a subdirectory for the specific field
		$fieldDir = $this->uploadDir . DIRECTORY_SEPARATOR . $field;
		if (!is_dir($fieldDir)) {
			mkdir($fieldDir, 0777, true); // Create the directory if it doesn't exist
		}

		// Generate a unique filename to avoid duplication
		$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
		$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
		$uniqueName = $originalName . '_' . uniqid() . '.' . $extension;

		// Full path to save the file
		$filePath = $fieldDir . DIRECTORY_SEPARATOR . $uniqueName;

		// Move the uploaded file to the target directory
		if (move_uploaded_file($file['tmp_name'], $filePath)) {
			return $filePath; // Return the file path if the upload is successful
		}

		return false; // Return false if upload fails
	}

    public function delete($filePath)
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

	public function getFileDetails($filePath)
    {
        if (file_exists($filePath)) {
            return [
                'basename' => basename($filePath),
                'size' => filesize($filePath), // File size in bytes
                'mime_type' => mime_content_type($filePath), // MIME type
                'last_modified' => filemtime($filePath) // Last modification time (timestamp)
            ];
        }

        return null; // File does not exist
    }
}
