<?php
// models/Product.php

require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table = 'products';

    public function __construct() {
        $this->conn = Database::connect();
    }

    /**
     * getAll()
     * - Devuelve todos los productos como arreglo de objetos.
     */
    public function getAll(): array {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * findById($id)
     * - Encuentra un producto por su ID.
     * - Devuelve un objeto o false si no existe.
     */
    public function findById(int $id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * updateStock($productId, $newStock)
     * - Actualiza únicamente la columna `stock` de un producto.
     * - Recibe el ID del producto y la nueva cantidad de stock.
     * - Devuelve true si la actualización fue exitosa, false en caso contrario.
     */
    public function updateStock(int $productId, int $newStock): bool {
        $sql = "
            UPDATE {$this->table}
            SET stock = :stock
            WHERE id = :id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':stock', $newStock, PDO::PARAM_INT);
        $stmt->bindParam(':id',    $productId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * create($name, $description, $price, $stock)
     * - Inserta un nuevo producto en la BD.
     * - Devuelve true si la inserción fue exitosa, false en caso contrario.
     */
    public function create(string $name, string $description, float $price, int $stock): bool {
        $sql = "
            INSERT INTO {$this->table} (name, description, price, stock)
            VALUES (:name, :description, :price, :stock)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name',        $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price',       $price);
        $stmt->bindParam(':stock',       $stock);
        return $stmt->execute();
    }

    /**
     * update($id, $name, $description, $price, $stock)
     * - Actualiza un producto existente (nombre, descripción, precio, stock).
     * - Devuelve true si la actualización fue exitosa, false en caso contrario.
     */
    public function update(int $id, string $name, string $description, float $price, int $stock): bool {
        $sql = "
            UPDATE {$this->table}
            SET name        = :name,
                description = :description,
                price       = :price,
                stock       = :stock
            WHERE id = :id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name',        $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price',       $price);
        $stmt->bindParam(':stock',       $stock);
        $stmt->bindParam(':id',          $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * delete($id)
     * - Elimina un producto por su ID.
     * - Devuelve true si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * getTotalStock()
     * - Devuelve la sumatoria de la columna `stock` en la tabla products.
     * - Útil, por ejemplo, en el dashboard de administrador.
     */
    public function getTotalStock(): int {
        $sql = "SELECT COALESCE(SUM(stock), 0) AS total_stock FROM {$this->table}";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int)$row->total_stock;
    }
}
