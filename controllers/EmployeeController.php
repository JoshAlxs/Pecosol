<?php
// controllers/EmployeeController.php

class EmployeeController {
    private $saleModel;
    private $productModel;

    public function __construct() {
        // 1) Arrancar sesión si no exista
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2) Si no hay usuario logueado, redirigir a login
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 3) Verificar que el rol sea 'employee'
        if ($_SESSION['role'] !== 'employee') {
            // Si no es empleado, enviarlo al dashboard de admin
            header('Location: index.php?controller=dashboard&action=adminHome');
            exit;
        }

        // 4) Instanciar modelos necesarios
        require_once __DIR__ . '/../models/Sale.php';
        require_once __DIR__ . '/../models/Product.php';
        $this->saleModel    = new Sale();
        $this->productModel = new Product();
    }

    /**
     * addSaleForm()
     * - Muestra el formulario para que el empleado agregue una venta.
     * - Necesita la lista de productos (filtrados por stock > 0 en la vista).
     */
    public function addSaleForm() {
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/employee/ventas/add_sale.php';
    }

    /**
     * storeSale()
     * - Recibe el POST del formulario, valida datos, crea la venta,
     *   actualiza stock y registra movimiento en stock_movements.
     */
    public function storeSale() {
        $productId   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity    = isset($_POST['quantity'])   ? (int)$_POST['quantity']   : 0;
        $description = trim($_POST['description'] ?? '');

        $error = '';
        if ($productId <= 0 || $quantity <= 0) {
            $error = 'Debe seleccionar un producto y una cantidad válida.';
        } else {
            $producto = $this->productModel->findById($productId);
            if (!$producto) {
                $error = 'El producto seleccionado no existe.';
            } elseif ($quantity > $producto->stock) {
                $error = 'La cantidad solicitada supera el stock disponible.';
            }
        }

        if (!empty($error)) {
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/employee/ventas/add_sale.php';
            return;
        }

        $unitPrice  = (float) $producto->price;
        $totalPrice = $unitPrice * $quantity;
        $userId     = $_SESSION['user_id'];

        $newSaleId = $this->saleModel->createSale(
            $userId,
            $productId,
            $quantity,
            $unitPrice,
            $totalPrice,
            $description
        );

        if (!$newSaleId) {
            $error = 'Ocurrió un error al registrar la venta. Intenta nuevamente.';
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/employee/ventas/add_sale.php';
            return;
        }

        // Actualizar stock y registrar movimiento
        $nuevoStock = $producto->stock - $quantity;
        $this->productModel->updateStock($productId, $nuevoStock);
        $this->saleModel->registerStockMovement(
            $productId,
            $userId,
            -$quantity,
            'salida',
            "Venta ID: {$newSaleId}"
        );

        header('Location: index.php?controller=dashboard&action=employeeHome');
        exit;
    }

    /**
     * listSalesEmployee()
     * - Muestra todas las ventas que este empleado ha registrado.
     */
    public function listSalesEmployee() {
        // 1) Asegurar sesión y rol
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 2) Obtener las ventas de este empleado
        $userId = $_SESSION['user_id'];
        $ventas = $this->saleModel->getAllSalesByUser($userId);

        // 3) Cargar la vista CORRECTA dentro de employee/ventas/
        require_once __DIR__ . '/../views/employee/ventas/list_sales.php';
    }

    public function listProductsEmployee() {
        // 1) Validar sesión y rol
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 2) Obtener todos los productos (podrás filtrarlos por stock en la vista)
        $productos = $this->productModel->getAll();

        // 3) Cargar la vista
        require_once __DIR__ . '/../views/employee/productos/list_products.php';
    }
}

