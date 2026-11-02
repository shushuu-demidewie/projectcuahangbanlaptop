<?php
/**
 * File: helpers/DatabaseBackupService.php
 * Dịch vụ Sao lưu và Phục hồi Cơ sở dữ liệu MySQL cho EmShu Store
 * Thiết kế theo kiến trúc chuẩn từ Borg Backup Server (BBS\Services\ServerBackupService)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class DatabaseBackupService
{
    private PDO $db;
    private string $backupDir;
    private string $dbName = 'ql_cuahang_laptop';

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->backupDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';

        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Tạo bản sao lưu toàn diện cho cơ sở dữ liệu (cả cấu trúc DDL & dữ liệu DML)
     *
     * @param string $note Ghi chú bản sao lưu
     * @return array{success: bool, message: string, filename: ?string, size: int}
     */
    public function createBackup(string $note = ''): array
    {
        try {
            // Tăng giới hạn bộ nhớ và thời gian thực thi cho cơ sở dữ liệu lớn
            @ini_set('memory_limit', '512M');
            @set_time_limit(300);

            $timestamp = date('Y-m-d_H-i-s');
            $cleanNote = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($note));
            $suffix = $cleanNote !== '' ? "_{$cleanNote}" : '';
            $filename = "backup_{$this->dbName}_{$timestamp}{$suffix}.sql";
            $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

            // Mở file ghi luồng (Stream writing) để tiết kiệm RAM
            $handle = fopen($filePath, 'w');
            if (!$handle) {
                return [
                    'success' => false,
                    'message' => 'Không thể tạo file sao lưu trong thư mục lưu trữ.',
                    'filename' => null,
                    'size' => 0
                ];
            }

            // Ghi phần header của file SQL
            $header = "-- ========================================================\n"
                    . "-- EmShu Store Database Backup (Borg Backup Server Engine)\n"
                    . "-- Database: {$this->dbName}\n"
                    . "-- Generated at: " . date('Y-m-d H:i:s') . "\n"
                    . "-- Note: " . ($note ?: 'Bản sao lưu định kỳ hệ thống') . "\n"
                    . "-- ========================================================\n\n"
                    . "SET FOREIGN_KEY_CHECKS = 0;\n"
                    . "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n"
                    . "SET AUTOCOMMIT = 0;\n"
                    . "START TRANSACTION;\n"
                    . "SET NAMES utf8mb4;\n\n";
            fwrite($handle, $header);

            // 1. Lấy danh sách toàn bộ các bảng trong CSDL
            $tablesStmt = $this->db->query("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
            $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Ghi chú bảng
                fwrite($handle, "\n-- --------------------------------------------------------\n");
                fwrite($handle, "-- Table structure and data for table `{$table}`\n");
                fwrite($handle, "-- --------------------------------------------------------\n\n");

                // Drop table if exists
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

                // Lấy cấu trúc CREATE TABLE
                $createStmt = $this->db->query("SHOW CREATE TABLE `{$table}`");
                $createRow = $createStmt->fetch(PDO::FETCH_ASSOC);
                $createSql = $createRow['Create Table'] ?? '';
                if ($createSql !== '') {
                    fwrite($handle, $createSql . ";\n\n");
                }

                // Lấy dữ liệu của bảng
                $dataStmt = $this->db->query("SELECT * FROM `{$table}`");
                $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $colNames = implode('`, `', $columns);

                    // Ghi theo từng batch để tối ưu tốc độ và dung lượng
                    $batchSize = 100;
                    $batch = [];

                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $val) {
                            if ($val === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $this->db->quote((string)$val);
                            }
                        }
                        $batch[] = '(' . implode(', ', $values) . ')';

                        if (count($batch) >= $batchSize) {
                            $insertSql = "INSERT INTO `{$table}` (`{$colNames}`) VALUES\n"
                                       . implode(",\n", $batch) . ";\n";
                            fwrite($handle, $insertSql);
                            $batch = [];
                        }
                    }

                    if (!empty($batch)) {
                        $insertSql = "INSERT INTO `{$table}` (`{$colNames}`) VALUES\n"
                                   . implode(",\n", $batch) . ";\n";
                        fwrite($handle, $insertSql);
                    }
                    fwrite($handle, "\n");
                }
            }

            // Ghi phần footer
            $footer = "\nCOMMIT;\n"
                    . "SET FOREIGN_KEY_CHECKS = 1;\n"
                    . "-- End of Backup\n";
            fwrite($handle, $footer);
            fclose($handle);

            $fileSize = (int)filesize($filePath);

            return [
                'success' => true,
                'message' => "Sao lưu cơ sở dữ liệu thành công! (Dung lượng: " . $this->formatBytes($fileSize) . ")",
                'filename' => $filename,
                'size' => $fileSize
            ];
        } catch (Throwable $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            if (isset($filePath) && file_exists($filePath)) {
                @unlink($filePath);
            }
            return [
                'success' => false,
                'message' => "Lỗi trong quá trình sao lưu: " . $e->getMessage(),
                'filename' => null,
                'size' => 0
            ];
        }
    }

    /**
     * Lấy danh sách các bản sao lưu hiện có trong thư mục storage
     *
     * @return array<int, array{filename: string, size: int, size_formatted: string, created_at: string, path: string}>
     */
    public function getBackupsList(): array
    {
        $files = glob($this->backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');
        if (!$files) {
            return [];
        }

        $list = [];
        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $size = (int)filesize($filePath);
            $mtime = filemtime($filePath);

            $list[] = [
                'filename'       => $filename,
                'size'           => $size,
                'size_formatted' => $this->formatBytes($size),
                'created_at'     => date('d/m/Y H:i:s', $mtime),
                'timestamp'      => $mtime,
                'path'           => $filePath
            ];
        }

        // Sắp xếp bản mới nhất lên đầu
        usort($list, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $list;
    }

    /**
     * Phục hồi cơ sở dữ liệu từ file sao lưu đã chọn
     *
     * @param string $filename Tên file sao lưu
     * @return array{success: bool, message: string}
     */
    public function restoreBackup(string $filename): array
    {
        // Chống lỗ hổng Directory Traversal
        $filename = basename($filename);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filePath) || !is_file($filePath)) {
            return [
                'success' => false,
                'message' => 'File sao lưu không tồn tại hoặc đã bị xóa.'
            ];
        }

        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(300);

            $sqlContent = file_get_contents($filePath);
            if ($sqlContent === false || trim($sqlContent) === '') {
                return [
                    'success' => false,
                    'message' => 'File sao lưu trống hoặc không thể đọc.'
                ];
            }

            // Tắt kiểm tra khóa ngoại tạm thời và thực thi SQL
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $this->db->exec($sqlContent);
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            return [
                'success' => true,
                'message' => "Phục hồi cơ sở dữ liệu thành công từ file [{$filename}]!"
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => "Lỗi trong quá trình phục hồi: " . $e->getMessage()
            ];
        }
    }

    /**
     * Xóa một bản sao lưu
     *
     * @param string $filename Tên file sao lưu
     * @return bool
     */
    public function deleteBackup(string $filename): bool
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($filePath) && is_file($filePath)) {
            return @unlink($filePath);
        }
        return false;
    }

    /**
     * Xuất file tải về an toàn qua HTTP
     *
     * @param string $filename Tên file cần tải
     */
    public function downloadBackup(string $filename): void
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filePath) || !is_file($filePath)) {
            die('File sao lưu không tồn tại!');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Lấy các số liệu thống kê tổng thể về CSDL và bộ nhớ sao lưu
     *
     * @return array{total_tables: int, total_rows: int, total_size_mb: float, backups_count: int, backups_total_size: string, last_backup: ?string}
     */
    public function getDatabaseStats(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as table_count,
                    COALESCE(SUM(TABLE_ROWS), 0) as total_rows,
                    COALESCE(SUM(DATA_LENGTH + INDEX_LENGTH), 0) as total_bytes
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ?
            ");
            $stmt->execute([$this->dbName]);
            $dbStats = $stmt->fetch(PDO::FETCH_ASSOC);

            $backups = $this->getBackupsList();
            $totalBackupBytes = array_sum(array_column($backups, 'size'));
            $lastBackup = !empty($backups) ? $backups[0]['created_at'] : null;

            return [
                'total_tables'       => (int)($dbStats['table_count'] ?? 0),
                'total_rows'         => (int)($dbStats['total_rows'] ?? 0),
                'total_size_mb'      => round((float)($dbStats['total_bytes'] ?? 0) / 1024 / 1024, 2),
                'backups_count'      => count($backups),
                'backups_total_size' => $this->formatBytes($totalBackupBytes),
                'last_backup'        => $lastBackup
            ];
        } catch (Throwable $e) {
            return [
                'total_tables'       => 0,
                'total_rows'         => 0,
                'total_size_mb'      => 0.0,
                'backups_count'      => 0,
                'backups_total_size' => '0 B',
                'last_backup'        => null
            ];
        }
    }

    /**
     * Định dạng kích thước byte sang đơn vị thân thiện (B, KB, MB, GB)
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

