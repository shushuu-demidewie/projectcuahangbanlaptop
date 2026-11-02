<?php
/**
 * File: models/AccountManagerModel.php
 * Model Quản lý Toàn diện Tài khoản Hệ thống (Dành riêng cho Ban Quản Trị Admin)
 */

require_once __DIR__ . '/../config/Database.php';

class AccountManagerModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách tài khoản theo bộ lọc và phân trang
     *
     * @param string $role Chức vụ lọc ('admin', 'nv_kho', 'nv_ban_hang', 'thu_ngan', 'ke_toan', 'ky_thuat_vien', 'user', hoặc '' cho tất cả)
     * @param string $status Trạng thái ('active', 'locked', hoặc '')
     * @param string $keyword Từ khóa tìm kiếm (họ tên, username, email, sđt)
     * @param int $page Trang hiện tại
     * @param int $limit Số lượng bản ghi trên một trang
     * @return array
     */
    public function getAllAccounts(string $role = '', string $status = '', string $keyword = '', int $page = 1, int $limit = 10): array {
        $offset = ($page - 1) * $limit;
        $params = [];

        $sql = "SELECT 
                    acc.maTk,
                    acc.hoTen,
                    acc.email,
                    acc.tenTaiKhoan,
                    acc.ngayTao,
                    acc.role,
                    acc.trangThai,
                    kh.maKH,
                    kh.soDienThoai,
                    kh.diaChi
                FROM accounts acc
                LEFT JOIN khachhang kh ON acc.maTk = kh.maTk
                WHERE 1 = 1";

        if ($role !== '') {
            $sql .= " AND acc.role = :role";
            $params[':role'] = $role;
        }

        if ($status !== '') {
            $sql .= " AND acc.trangThai = :status";
            $params[':status'] = $status;
        }

        if ($keyword !== '') {
            $sql .= " AND (acc.hoTen LIKE :kw1 OR acc.tenTaiKhoan LIKE :kw2 OR acc.email LIKE :kw3 OR kh.soDienThoai LIKE :kw4)";
            $kw = '%' . $keyword . '%';
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
        }

        $sql .= " ORDER BY acc.ngayTao DESC, acc.maTk DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số lượng tài khoản theo điều kiện lọc để phân trang
     */
    public function countAccounts(string $role = '', string $status = '', string $keyword = ''): int {
        $params = [];
        $sql = "SELECT COUNT(*) 
                FROM accounts acc
                LEFT JOIN khachhang kh ON acc.maTk = kh.maTk
                WHERE 1 = 1";

        if ($role !== '') {
            $sql .= " AND acc.role = :role";
            $params[':role'] = $role;
        }

        if ($status !== '') {
            $sql .= " AND acc.trangThai = :status";
            $params[':status'] = $status;
        }

        if ($keyword !== '') {
            $sql .= " AND (acc.hoTen LIKE :kw1 OR acc.tenTaiKhoan LIKE :kw2 OR acc.email LIKE :kw3 OR kh.soDienThoai LIKE :kw4)";
            $kw = '%' . $keyword . '%';
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy chi tiết thông tin 1 tài khoản (kèm thông tin khách hàng nếu có)
     */
    public function getAccountDetail(string $maTk): array|false {
        $sql = "SELECT 
                    acc.maTk,
                    acc.hoTen,
                    acc.email,
                    acc.tenTaiKhoan,
                    acc.ngayTao,
                    acc.role,
                    acc.trangThai,
                    kh.maKH,
                    kh.soDienThoai,
                    kh.diaChi,
                    kh.doiTuongUuTien
                FROM accounts acc
                LEFT JOIN khachhang kh ON acc.maTk = kh.maTk
                WHERE acc.maTk = :maTk
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maTk' => $maTk]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tự động sinh mã tài khoản tiếp theo dạng TK001, TK002...
     */
    public function generateNextMaTk(): string {
        $stmt = $this->db->query("SELECT maTk FROM accounts WHERE maTk REGEXP '^TK[0-9]+$' ORDER BY CAST(SUBSTRING(maTk, 3) AS UNSIGNED) DESC LIMIT 1");
        $lastCode = $stmt->fetchColumn();

        if ($lastCode) {
            $num = (int)substr($lastCode, 2) + 1;
            return 'TK' . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
        }

        return 'TK001';
    }

    /**
     * Thêm mới tài khoản nhân viên nội bộ
     *
     * @param array $data ['hoTen', 'email', 'tenTaiKhoan', 'matKhau', 'role']
     * @return string|false Trả về maTk nếu thành công, false nếu thất bại
     */
    public function createEmployeeAccount(array $data): string|false {
        $maTk           = $this->generateNextMaTk();
        $hoTen          = trim($data['hoTen']);
        $email          = trim($data['email']);
        $tenTaiKhoan    = trim($data['tenTaiKhoan']);
        $matKhauRaw     = trim($data['matKhau']);
        $role           = trim($data['role']);
        $trangThai      = 'active';
        $ngayTao        = date('Y-m-d H:i:s');

        // Băm mật khẩu bằng password_hash Bcrypt
        $hashedPassword = password_hash($matKhauRaw, PASSWORD_DEFAULT);

        $sql = "INSERT INTO accounts (maTk, hoTen, email, tenTaiKhoan, matKhau, ngayTao, role, trangThai)
                VALUES (:maTk, :hoTen, :email, :tenTaiKhoan, :matKhau, :ngayTao, :role, :trangThai)";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':maTk'        => $maTk,
            ':hoTen'       => $hoTen,
            ':email'       => $email,
            ':tenTaiKhoan' => $tenTaiKhoan,
            ':matKhau'     => $hashedPassword,
            ':ngayTao'     => $ngayTao,
            ':role'        => $role,
            ':trangThai'   => $trangThai,
        ]);

        return $success ? $maTk : false;
    }

    /**
     * Cập nhật quyền hạn (Role) và Trạng thái hoạt động (active / locked)
     */
    public function updateAccountRoleAndStatus(string $maTk, string $newRole, string $newStatus): bool {
        $sql = "UPDATE accounts 
                SET role = :role, trangThai = :trangThai 
                WHERE maTk = :maTk";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':role'      => $newRole,
            ':trangThai' => $newStatus,
            ':maTk'      => $maTk
        ]);
    }

    /**
     * Đặt lại mật khẩu mới cho tài khoản
     */
    public function resetAccountPassword(string $maTk, string $newPassword): bool {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE accounts SET matKhau = :matKhau WHERE maTk = :maTk";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':matKhau' => $hashedPassword,
            ':maTk'    => $maTk
        ]);
    }

    /**
     * Thống kê tổng số lượng tài khoản theo từng nhóm quyền
     */
    public function countAccountsByRole(): array {
        $sql = "SELECT role, trangThai, COUNT(*) as sl FROM accounts GROUP BY role, trangThai";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $stats = [
            'total'     => 0,
            'admin'     => 0,
            'staff'     => 0,
            'customer'  => 0,
            'locked'    => 0,
            'byRole'    => [
                'admin'         => 0,
                'nv_kho'        => 0,
                'nv_ban_hang'   => 0,
                'thu_ngan'      => 0,
                'ke_toan'       => 0,
                'ky_thuat_vien' => 0,
                'user'          => 0,
            ]
        ];

        $staffRoles = ['nv_kho', 'nv_ban_hang', 'thu_ngan', 'ke_toan', 'ky_thuat_vien'];

        foreach ($rows as $r) {
            $role  = $r['role'];
            $st    = $r['trangThai'];
            $count = (int)$r['sl'];

            $stats['total'] += $count;

            if ($st === 'locked') {
                $stats['locked'] += $count;
            }

            if ($role === 'admin') {
                $stats['admin'] += $count;
            } elseif (in_array($role, $staffRoles, true)) {
                $stats['staff'] += $count;
            } elseif ($role === 'user') {
                $stats['customer'] += $count;
            }

            if (isset($stats['byRole'][$role])) {
                $stats['byRole'][$role] += $count;
            }
        }

        return $stats;
    }

    /**
     * Kiểm tra username đã tồn tại chưa (có thể loại trừ maTk khi sửa)
     */
    public function isUsernameExists(string $username, ?string $excludeMaTk = null): bool {
        $sql = "SELECT COUNT(*) FROM accounts WHERE tenTaiKhoan = :username";
        $params = [':username' => $username];

        if ($excludeMaTk) {
            $sql .= " AND maTk != :excludeMaTk";
            $params[':excludeMaTk'] = $excludeMaTk;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Kiểm tra email đã tồn tại chưa (có thể loại trừ maTk khi sửa)
     */
    public function isEmailExists(string $email, ?string $excludeMaTk = null): bool {
        $sql = "SELECT COUNT(*) FROM accounts WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeMaTk) {
            $sql .= " AND maTk != :excludeMaTk";
            $params[':excludeMaTk'] = $excludeMaTk;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }
}
