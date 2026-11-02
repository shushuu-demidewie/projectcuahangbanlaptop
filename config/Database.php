<?php
/**
 * File: config/Database.php
 * Quản lý kết nối Cơ sở dữ liệu MySQL qua PDO (mô hình Singleton)
 */

class Database {
    private static string $host = "localhost";
    private static string $dbName = "ql_cuahang_laptop";
    private static string $username = "root";
    private static string $password = "";
    private static string $charset = "utf8mb4";
    
    private static ?PDO $instance = null;

    // Ngăn chặn khởi tạo đối tượng trực tiếp từ bên ngoài (Singleton)
    private function __construct() {}
    private function __clone() {}

    /**
     * Lấy kết nối PDO duy nhất
     * @return PDO
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbName . ";charset=" . self::$charset;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,     // Ném ngoại lệ khi có lỗi SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Mặc định trả về mảng kết hợp
                PDO::ATTR_EMULATE_PREPARES   => false,                     // Vô hiệu hóa giả lập prepare để chống triệt để SQL Injection
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"        // Đảm bảo hỗ trợ tiếng Việt có dấu đầy đủ
            ];

            try {
                self::$instance = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                // Trong môi trường production, ghi log thay vì hiển thị trực tiếp
                die("<h3>Lỗi kết nối cơ sở dữ liệu:</h3> " . htmlspecialchars($e->getMessage()));
            }
        }

        return self::$instance;
    }
}
