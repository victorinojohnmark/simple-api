<?php
class PizzaModel extends Model {
    protected static $tableName = 'pizzas';

    protected static $casts = [
        'id' => 'int',
        'price' => 'float',
        'created_at' => 'int',
    ];

    public static function findById($id) {
        return self::fetchRecord("SELECT * FROM pizzas WHERE id = :id", ['id' => $id]);
    }

    public static function listPizzas() {
        return self::fetchList("SELECT * FROM pizzas ORDER BY created_at DESC");
    }

    public static function createPizza(array $data) {
        $filteredData = [
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'created_at' => $data['created_at'],
            'created_by' => $data['created_by'],
        ];
        return self::create("INSERT INTO pizzas (name, price, description, created_at, created_by) VALUES (:name, :price, :description, :created_at, :created_by)", $filteredData);
    }

    public static function updatePizza($id, array $data) {
        $data['id'] = $id; // Add ID to params
        return self::update("UPDATE pizzas SET name = :name, price = :price, description = :description WHERE id = :id", $data);
    }

    public static function deletePizza($id) {
        return self::delete("DELETE FROM pizzas WHERE id = :id", ['id' => $id]);
    }
}