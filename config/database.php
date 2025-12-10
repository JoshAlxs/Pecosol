<?php
// config/database.php

class Database {
    // Cambia estos datos si tu usuario/contraseña de MySQL difieren
    private static $host     = "localhost";
    private static $db_name  = "pecosol_db";
    private static $username = "root";
    private static $password = "";   // Si tu XAMPP tiene contraseña, colócala aquí
    public static  $conn;

    public static function connect() {
        self::$conn = null;
        try {
            self::$conn = new PDO(
                "mysql:host=" . self::$host . ";dbname=" . self::$db_name,
                self::$username,
                self::$password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
            );
            // Para que lance excepciones en caso de error
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Muestra error si no se conecta (solo para desarrollo)
            echo "Error de conexión a la base de datos: " . $exception->getMessage();
            exit;
        }
        return self::$conn;
    }
}


