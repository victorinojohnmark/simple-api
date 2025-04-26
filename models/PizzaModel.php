<?php

class PizzaModel {
    public static function getAll() {
        return DB::query("SELECT * FROM pizzas")->fetchAll();
    }

    public static function create($data) {
        $db = DB::connect(); // Get the PDO instance

        try {
            $db->beginTransaction(); // Start the transaction

            // Extract fields from the data array
            $name = $data['name'] ?? null;
            $imagePath = $data['image_path'] ?? null;

            // Insert the pizza into the database
            $stmt = $db->prepare("INSERT INTO pizzas (name, image_path) VALUES (:name, :image_path)");
            $stmt->execute(['name' => $name, 'image_path' => $imagePath]);

            $pizzaId = $db->lastInsertId(); // Get the ID of the newly created pizza

            // (Optional) Perform additional operations here if needed
            // e.g., logging the creation of the pizza in another table

            $db->commit(); // Commit the transaction
            return $pizzaId;

        } catch (Exception $e) {
            $db->rollBack(); // Rollback the transaction on failure
            throw new Exception("Failed to create pizza: " . $e->getMessage());
        }
    }

    public static function find($id) {
        return DB::query("SELECT * FROM pizzas WHERE id = :id", ['id' => $id])->fetch();
    }

    public static function delete($id) {
        $db = DB::connect(); // Get the PDO instance

        try {
            $db->beginTransaction(); // Start the transaction

            // Find the pizza record
            $pizza = self::find($id);

            if ($pizza && !empty($pizza['image_path'])) {
                // Delete the associated image file
                $fileHandler = new File(UPLOAD_DIR);
                $fileHandler->delete($pizza['image_path']);
            }

            // Delete the pizza record from the database
            $stmt = $db->prepare("DELETE FROM pizzas WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $db->commit(); // Commit the transaction

        } catch (Exception $e) {
            $db->rollBack(); // Rollback the transaction on failure
            throw new Exception("Failed to delete pizza: " . $e->getMessage());
        }
    }
}
