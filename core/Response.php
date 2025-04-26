<?php

class Response
{
    /**
     * Send a JSON response.
     *
     * @param array $data The data to send as JSON.
     * @param int $statusCode The HTTP status code.
     */
    public static function json(array $data, int $statusCode = 200)
    {
        // Set the Content-Type header for JSON responses
        header('Content-Type: application/json');
        http_response_code($statusCode); // Set HTTP status code
        echo json_encode($data, JSON_PRETTY_PRINT); // Output the JSON-encoded data
        exit(); // End the script execution after sending the response
    }
}
