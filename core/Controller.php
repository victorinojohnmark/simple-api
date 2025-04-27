<?php

class Controller {
    protected $requestData;

    public function __construct() {
        $this->requestData = $this->getRequestData();
    }

    protected function getRequestData() {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            // Handle JSON request body
            return json_decode(file_get_contents('php://input'), true);
        }
    
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Handle GET query parameters
            return $_GET;
        }
    
        // Default to POST data
        return $_POST;
    }
    
}
