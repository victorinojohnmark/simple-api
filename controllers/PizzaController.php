<?php

class PizzaController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function listPizzas() {
        $pizzas = PizzaModel::listPizzas(); // Use model to fetch all pizzas
        Response::json([
            'data' => $pizzas,
        ]);
    }

    public function getPizza($pizzaId)
    {
        if ($pizzaId) {
            $pizza = PizzaModel::findById($pizzaId); // Use model to fetch pizza by ID
            if ($pizza) {
                Response::json([
                    'data' => $pizza,
                ]);
            } else {
                Response::json([
                    'error' => 'Pizza not found',
                ], 404);
            }
        } else {
            Response::json([
                'error' => 'Pizza ID is required',
            ], 400);
        }
    }

    public function createPizza() {
        $data = $this->requestData;
    
        // Initialize File handler (if needed)
        // $fileHandler = new File(UPLOAD_DIR); // Use the directory from config.php
        // if (isset($_FILES['image'])) {
        //     $uploadedFilePath = $fileHandler->upload($_FILES['image'], 'image');
        //     if ($uploadedFilePath) {
        //         $data['image_path'] = $uploadedFilePath; // Save the file path in the data array
        //     } else {
        //         Response::json(['error' => 'Failed to upload file'], 500);
        //         return;
        //     }
        // }
    
        // Validation rules
        $validator = new Validator($data, [
            'name' => 'required|string|max:255|unique:pizzas,name',
            'description' => 'string|max:1000',
            'price' => 'required|numeric|min:0',
        ]);
    
        $validator->setCustomMessages([
            'name.unique' => 'Existing na ung pizza.',
        ]);
    
        // Add additional fields
        $data['created_at'] = time();
        $data['created_by'] = $_REQUEST['user_id']; // Use user ID from the JWT payload
    
        if ($validator->passes()) {
            // Create the pizza
            $pizza = PizzaModel::createPizza($data);
            Response::json([
                'success' => true,
                'message' => 'Pizza created successfully',
                'data' => $pizza,
            ]);
        } else {
            Response::json([
                'success' => false,
                'message' => 'Validation failed. Please review the errors and try again.',
                'errors' => $validator->errors()
            ], 422);
        }
    }

    public function updatePizza($pizzaId) {
        $data = $this->requestData;

        $pizza = PizzaModel::findById($pizzaId);

        if($pizza) {
            // validate
            $validator = new Validator($data, [
                'name' => 'required|string|max:255|unique:pizzas,name,' . $pizza['id'],
                'description' => 'string|max:1000',
                'price' => 'required|numeric|min:0',
            ]);

            if($validator->passes()) {
                // Update the pizza record in the database
                $pizza = PizzaModel::updatePizza($pizza['id'], $data);

                Response::json([
                    'success' => true,
                    'message' => 'Pizza updated',
                    'data' => $pizza,
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
                return;
            }
        } else {
            Response::json([
                'success' => false,
                'error' => 'Pizza not found',
            ], 404);
        }
        
    }

    public function deletePizza($pizzaId) {
        $pizza = PizzaModel::findById($pizzaId);

        if($pizza) {
            PizzaModel::deletePizza($pizza['id']);

            Response::json([
                'success' => true,
                'message' => 'Pizza deleted',
            ]);
        } else {
            Response::json([
                'success' => false,
                'error' => 'Pizza not found',
            ], 404);
        }
        
    }
}
