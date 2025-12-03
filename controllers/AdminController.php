<?php
// controllers/AdminController.php

class AdminController {
    private $productModel;
    private $userModel;
    private $saleModel;

    public function __construct() {
        // 1) Arrancar sesión si no existe
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2) Si no hay usuario logueado, redirigir a login
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 3) Verificar que el rol sea 'admin'
        if ($_SESSION['role'] !== 'admin') {
            // Si no es admin, enviarlo al dashboard de empleado
            header('Location: index.php?controller=dashboard&action=employeeHome');
            exit;
        }

        // 4) Instanciar modelos necesarios
        require_once __DIR__ . '/../models/Product.php';
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Sale.php';

        $this->productModel = new Product();
        $this->userModel    = new User();
        $this->saleModel    = new Sale();
    }

    /**************************************************************************
     * PRODUCTOS: CRUD completo
     **************************************************************************/

    public function listProducts() {
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/productos/list_products.php';
    }

    public function addProductForm() {
        require_once __DIR__ . '/../views/admin/productos/add_product.php';
    }

    public function storeProduct() {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $stock       = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;

        $error = '';
        if ($name === '' || $price <= 0 || $stock < 0) {
            $error = 'Debes completar el nombre, un precio mayor a 0 y un stock válido (>= 0).';
        }

        if (!empty($error)) {
            require_once __DIR__ . '/../views/admin/productos/add_product.php';
            return;
        }

        $creado = $this->productModel->create($name, $description, $price, $stock);
        if (!$creado) {
            $error = 'No se pudo agregar el producto. Intenta nuevamente.';
            require_once __DIR__ . '/../views/admin/productos/add_product.php';
            return;
        }

        header('Location: index.php?controller=admin&action=listProducts');
        exit;
    }

    public function editProductForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listProducts');
            exit;
        }

        $producto = $this->productModel->findById($id);
        if (!$producto) {
            header('Location: index.php?controller=admin&action=listProducts');
            exit;
        }

        require_once __DIR__ . '/../views/admin/productos/edit_product.php';
    }

    public function updateProduct() {
        $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $stock       = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;

        $error = '';
        if ($id <= 0 || $name === '' || $price <= 0 || $stock < 0) {
            $error = 'ID inválido, nombre vacío, precio menor o igual a 0 o stock inválido.';
        } else {
            $productoExistente = $this->productModel->findById($id);
            if (!$productoExistente) {
                $error = 'El producto que intentas editar no existe.';
            }
        }

        if (!empty($error)) {
            $producto = (object)[
                'id'          => $id,
                'name'        => $name,
                'description' => $description,
                'price'       => $price,
                'stock'       => $stock
            ];
            require_once __DIR__ . '/../views/admin/productos/edit_product.php';
            return;
        }

        $actualizado = $this->productModel->update($id, $name, $description, $price, $stock);
        if (!$actualizado) {
            $error = 'No se pudo actualizar el producto. Intenta nuevamente.';
            $producto = (object)[
                'id'          => $id,
                'name'        => $name,
                'description' => $description,
                'price'       => $price,
                'stock'       => $stock
            ];
            require_once __DIR__ . '/../views/admin/productos/edit_product.php';
            return;
        }

        header('Location: index.php?controller=admin&action=listProducts');
        exit;
    }

    public function deleteProduct() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listProducts');
            exit;
        }

        $numVentas = $this->saleModel->countSalesByProduct($id);
        if ($numVentas > 0) {
            $_SESSION['error_product_delete'] = "No se puede eliminar el producto porque tiene {$numVentas} venta(s) asociada(s).";
            header('Location: index.php?controller=admin&action=listProducts');
            exit;
        }

        $this->productModel->delete($id);
        header('Location: index.php?controller=admin&action=listProducts');
        exit;
    }

    /**************************************************************************
     * EMPLEADOS: CRUD completo
     **************************************************************************/

    public function listEmployees() {
        $empleados = $this->userModel->getAllEmployees();
        require_once __DIR__ . '/../views/admin/employee/list_employees.php';
    }

    public function addEmployeeForm() {
        require_once __DIR__ . '/../views/admin/employee/add_employee.php';
    }

  public function storeEmployee() {
    $username  = trim($_POST['username']  ?? '');
    $password  = trim($_POST['password']  ?? '');
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $role      = trim($_POST['role']      ?? '');

    $error = '';
    // Validar campos
    if ($username === '' || $password === '' || $fullName === '' || $email === '' || $role === '') {
        $error = 'Todos los campos son obligatorios, incluyendo el rol.';
    } elseif (!in_array($role, ['employee','admin'], true)) {
        $error = 'Rol no válido.';
    } else {
        // Verificar usuario existente
        $existe = $this->userModel->findByUsername($username);
        if ($existe) {
            $error = 'El nombre de usuario ya existe.';
        }
    }

    if (!empty($error)) {
        // Reenviar a la vista con el error y valores ya escritos
        require_once __DIR__ . '/../views/admin/employee/add_employee.php';
        return;
    }

    // Crear el usuario con el rol elegido
    $creado = $this->userModel->create($username, $password, $fullName, $email, $role);
    if (!$creado) {
        $error = 'No se pudo crear el usuario. Intenta nuevamente.';
        require_once __DIR__ . '/../views/admin/employee/add_employee.php';
        return;
    }

    // Redirigir de vuelta al listado
    header('Location: index.php?controller=admin&action=listEmployees');
    exit;
    }


    public function editEmployeeForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listEmployees');
            exit;
        }

        $empleado = $this->userModel->findById($id);
        if (!$empleado || $empleado->role !== 'employee') {
            header('Location: index.php?controller=admin&action=listEmployees');
            exit;
        }

        require_once __DIR__ . '/../views/admin/employee/edit_employee.php';

    }

    public function updateEmployee() {
        $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $fullName  = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $changePwd = isset($_POST['change_password']) && $_POST['change_password'] === 'on';
        $newPwd    = trim($_POST['new_password'] ?? '');

        $error = '';
        $empleadoExistente = null;

        if ($id <= 0 || $fullName === '' || $email === '') {
            $error = 'ID inválido o campos obligatorios vacíos.';
        } else {
            $empleadoExistente = $this->userModel->findById($id);
            if (!$empleadoExistente || $empleadoExistente->role !== 'employee') {
                $error = 'El empleado no existe.';
            }
            if (empty($error) && $changePwd && $newPwd === '') {
                $error = 'Para cambiar contraseña, ingresa la nueva contraseña.';
            }
        }

        if (!empty($error)) {
            $empleado = (object)[
                'id'        => $id,
                'username'  => $empleadoExistente->username ?? '',
                'full_name' => $fullName,
                'email'     => $email,
                'role'      => 'employee'
            ];
            require_once __DIR__ . '/../views/admin/employee/edit_employee.php';
            return;
        }

        $actualizado = $this->userModel->update($id, $fullName, $email, 'employee');
        if (!$actualizado) {
            $error = 'Error al actualizar los datos del empleado. Intenta nuevamente.';
            $empleado = (object)[
                'id'        => $id,
                'username'  => $empleadoExistente->username,
                'full_name' => $fullName,
                'email'     => $email,
                'role'      => 'employee'
            ];
            require_once __DIR__ . '/../views/admin/employee/edit_employee.php';
            return;
        }

        if ($changePwd) {
            $pwdActualizado = $this->userModel->updatePassword($id, $newPwd);
            if (!$pwdActualizado) {
                $_SESSION['error_employee_pwd'] = 'No se pudo cambiar la contraseña.';
            }
        }

        header('Location: index.php?controller=admin&action=listEmployees');
        exit;
    }

    public function deleteEmployee() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listEmployees');
            exit;
        }

        $numVentas = $this->saleModel->countSalesByUser($id);
        if ($numVentas > 0) {
            $_SESSION['error_employee_delete'] = "No se puede eliminar al empleado porque tiene {$numVentas} venta(s).";
            header('Location: index.php?controller=admin&action=listEmployees');
            exit;
        }

        $this->userModel->delete($id);
        header('Location: index.php?controller=admin&action=listEmployees');
        exit;
    }

    /**************************************************************************
     * VENTAS (ADMIN): CRUD completo
     **************************************************************************/

    public function listSalesAdmin() {
        // Obtener todas las ventas (sin límite) ordenadas por fecha descendente
        $ventas = $this->saleModel->getAllSales();
        $hoy = date('Y-m-d');
        $totalHoy = $this->saleModel->getTotalSalesByDate($hoy, $hoy);
        require_once __DIR__ . '/../views/admin/ventas/list_sales.php';
    }

    public function addSaleAdminForm() {
        // Obtener todos los empleados y productos para los selects
        $empleados = $this->userModel->getAllEmployees();
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/ventas/add_sale_admin.php';
    }

    public function storeSaleAdmin() {
        $userId      = isset($_POST['user_id'])    ? (int)$_POST['user_id']    : 0;
        $productId   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity    = isset($_POST['quantity'])   ? (int)$_POST['quantity']   : 0;
        $description = trim($_POST['description'] ?? '');

        $error = '';
        if ($userId <= 0 || $productId <= 0 || $quantity <= 0) {
            $error = 'Empleado, producto y cantidad válidos son obligatorios.';
        } else {
            $empleado = $this->userModel->findById($userId);
            if (!$empleado || $empleado->role !== 'employee') {
                $error = 'El empleado seleccionado no es válido.';
            }
            $producto = $this->productModel->findById($productId);
            if (!$producto) {
                $error = 'El producto seleccionado no existe.';
            } elseif ($quantity > $producto->stock) {
                $error = 'La cantidad supera el stock disponible.';
            }
        }

        if (!empty($error)) {
            $empleados = $this->userModel->getAllEmployees();
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/ventas/add_sale_admin.php';
            return;
        }

        $unitPrice  = (float)$producto->price;
        $totalPrice = $unitPrice * $quantity;

        $newSaleId = $this->saleModel->createSale(
            $userId,
            $productId,
            $quantity,
            $unitPrice,
            $totalPrice,
            $description
        );
        if (!$newSaleId) {
            $error = 'Error al registrar la venta. Intenta nuevamente.';
            $empleados = $this->userModel->getAllEmployees();
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/ventas/add_sale_admin.php';
            return;
        }

        // Actualizar stock del producto
        $nuevoStock = $producto->stock - $quantity;
        $this->productModel->updateStock($productId, $nuevoStock);

        // Registrar movimiento de stock
        $this->saleModel->registerStockMovement(
            $productId,
            $userId,
            -$quantity,
            'salida',
            "Venta ID: {$newSaleId}"
        );

        header('Location: index.php?controller=admin&action=listSalesAdmin');
        exit;
    }

    public function editSaleAdminForm() {
        $saleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($saleId <= 0) {
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        // Obtener la venta con detalles y renombrar a $venta
        $venta = $this->saleModel->findByIdWithDetails($saleId);
        if (!$venta) {
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        $empleados = $this->userModel->getAllEmployees();
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/ventas/edit_sale_admin.php';
    }

    public function updateSaleAdmin() {
        $id          = isset($_POST['id'])         ? (int)$_POST['id']         : 0;
        $userId      = isset($_POST['user_id'])    ? (int)$_POST['user_id']    : 0;
        $productId   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity    = isset($_POST['quantity'])   ? (int)$_POST['quantity']   : 0;
        $description = trim($_POST['description'] ?? '');

        $error = '';
        $venta = null;
        if ($id <= 0 || $userId <= 0 || $productId <= 0 || $quantity <= 0) {
            $error = 'Todos los campos (empleado, producto, cantidad) son obligatorios.';
        } else {
            // Traer la venta original (antes de editar)
            $venta = $this->saleModel->findByIdWithDetails($id);
            if (!$venta) {
                $error = 'La venta que intentas editar no existe.';
            }
            $empleado = $this->userModel->findById($userId);
            if (!$empleado || $empleado->role !== 'employee') {
                $error = 'El empleado seleccionado no es válido.';
            }
            $producto = $this->productModel->findById($productId);
            if (!$producto) {
                $error = 'El producto seleccionado no existe.';
            }
        }

        if (!empty($error)) {
            // Si hay error, recargar vista usando la variable $venta
            $empleados = $this->userModel->getAllEmployees();
            $productos = $this->productModel->getAll();

            // Si no existe, crear un objeto fallback con los datos mínimos
            if (!$venta) {
                $venta = (object)[
                    'id'            => $id,
                    'user_id'       => $userId,
                    'product_id'    => $productId,
                    'quantity'      => $quantity,
                    'unit_price'    => 0,
                    'total_price'   => 0,
                    'description'   => $description,
                    'sale_date'     => '',
                    'user_name'     => '',
                    'product_name'  => '',
                    'current_stock' => 0
                ];
            }

            require_once __DIR__ . '/../views/admin/ventas/edit_sale_admin.php';
            return;
        }

        // 1) Restaurar stock de la venta anterior
        $prevQuantity  = $venta->quantity;
        $prevProductId = $venta->product_id;
        $prodPrev      = $this->productModel->findById($prevProductId);
        $this->productModel->updateStock($prevProductId, $prodPrev->stock + $prevQuantity);

        // 2) Ajustar stock para el nuevo producto
        $prodNew = $this->productModel->findById($productId);
        if ($quantity > $prodNew->stock) {
            $error = 'La nueva cantidad supera el stock disponible.';
            $empleados = $this->userModel->getAllEmployees();
            $productos = $this->productModel->getAll();
            $venta = $this->saleModel->findByIdWithDetails($id);
            require_once __DIR__ . '/../views/admin/ventas/edit_sale_admin.php';
            return;
        }
        $this->productModel->updateStock($productId, $prodNew->stock - $quantity);

        // 3) Calcular nuevos precios
        $unitPrice  = (float)$prodNew->price;
        $totalPrice = $unitPrice * $quantity;

        // 4) Actualizar la venta en BD
        $actualizado = $this->saleModel->updateSale(
            $id,
            $userId,
            $productId,
            $quantity,
            $unitPrice,
            $totalPrice,
            $description
        );
        if (!$actualizado) {
            $error = 'No se pudo actualizar la venta. Intenta nuevamente.';
            $empleados = $this->userModel->getAllEmployees();
            $productos = $this->productModel->getAll();
            $venta = $this->saleModel->findByIdWithDetails($id);
            require_once __DIR__ . '/../views/admin/ventas/edit_sale_admin.php';
            return;
        }

        // 5) Registrar movimiento de stock por la edición
        $this->saleModel->registerStockMovement(
            $productId,
            $userId,
            -$quantity,
            'salida',
            "Edición Venta ID: {$id}"
        );

        header('Location: index.php?controller=admin&action=listSalesAdmin');
        exit;
    }

    public function deleteSaleAdmin() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        $venta = $this->saleModel->findByIdWithDetails($id);
        if (!$venta) {
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        // Restaurar stock del producto
        $prod = $this->productModel->findById($venta->product_id);
        $this->productModel->updateStock(
            $venta->product_id,
            $prod->stock + $venta->quantity
        );

        // Eliminar la venta
        $this->saleModel->deleteSale($id);

        // Registrar movimiento de stock por eliminación
        $this->saleModel->registerStockMovement(
            $venta->product_id,
            $venta->user_id,
            $venta->quantity,
            'ingreso',
            "Eliminación Venta ID: {$id}"
        );

        header('Location: index.php?controller=admin&action=listSalesAdmin');
        exit;
    }
}

