<?php

class PizzaController {
    public function listPizzas() {
        $pizzas = Pizza::getAll(); // Use model to fetch all pizzas
        echo json_encode($pizzas);
    }

    public function createPizza() {
        // Handle text input (JSON or form data)
        $data = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
            ? json_decode(file_get_contents('php://input'), true)
            : $_POST;

        // Initialize File handler
        $fileHandler = new File(UPLOAD_DIR); // Use the directory from config.php

        // Handle file upload
        if (isset($_FILES['image'])) {
            $uploadedFilePath = $fileHandler->upload($_FILES['image'], 'image');

            if ($uploadedFilePath) {
                $data['image_path'] = $uploadedFilePath; // Save the file path in the data array
            } else {
                // Handle file upload error
                http_response_code(500);
                echo json_encode(['error' => 'Failed to upload file']);
                return;
            }
        }

        // Save to database via model
        Pizza::create($data); // Pass the full data array to the model
        echo json_encode(['message' => 'Pizza created', 'data' => $data]);
    }

    public function deletePizza($pizzaId) {
        // Fetch the pizza record from the database
        $pizza = Pizza::find($pizzaId);

        if ($pizza && !empty($pizza['image_path'])) {
            $fileHandler = new File(UPLOAD_DIR);

            // Delete the associated file
            $fileHandler->delete($pizza['image_path']);
        }

        // Delete the pizza record from the database
        Pizza::delete($pizzaId);

        echo json_encode(['message' => 'Pizza deleted']);
    }
}
