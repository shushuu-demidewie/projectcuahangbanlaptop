<?php
/**
 * File: models/AccountModel.php
 * Model thao tác với bảng `Accounts` trong Cơ sở dữ liệu
 */

require_once __DIR__ . '/../config/Database.php';

class AccountModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Kiểm tra thông tin đăng nhập và xác thực mật khẩu
     * @param string $tenTaiKhoan Tên đăng nhập hoặc Email
     * @param string $matKhau Mật khẩu thô nhập từ form
     * @return array|false Trả về mảng dữ liệu tài khoản nếu thành công, false nếu thất bại
     */
    public function checkLogin(string $tenTaiKhoan, string $matKhau): array|false {
        $sql = "SELECT maTk, hoTen, email, tenTaiKhoan, matKhau, role, trangThai 
                FROM accounts 
                WHERE (tenTaiKhoan = :tenTaiKhoan OR email = :email) 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenTaiKhoan' => $tenTaiKhoan,
            ':email'       => $tenTaiKhoan
        ]);

        $account = $stmt->fetch();

        if (!$account) {
            return false; // Không tìm thấy tài khoản
        }

        // 1. Kiểm tra trạng thái hoạt động của tài khoản
        if ($account['trangThai'] !== 'active') {
            return false; // Tài khoản đang bị khóa hoặc chưa kích hoạt
        }

        $passwordHash = $account['matKhau'];
        $isPasswordMatched = false;

        // 1. Kiểm tra mật khẩu chuẩn Bcrypt/Argon2 (password_verify)
        if (password_verify($matKhau, $passwordHash)) {
            $isPasswordMatched = true;
        } 
        // 2. Kiểm tra mật khẩu MD5 cũ (32 ký tự hex)
        elseif (md5($matKhau) === $passwordHash) {
            $isPasswordMatched = true;
            // Tự động nâng cấp sang password_hash an toàn
            $this->updatePasswordHash($account['maTk'], password_hash($matKhau, PASSWORD_DEFAULT));
        }
        // 3. Kiểm tra mật khẩu lưu dạng văn bản thô (Plaintext - ví dụ: admin123@)
        elseif ($matKhau === $passwordHash) {
            $isPasswordMatched = true;
            // Tự động băm và nâng cấp mật khẩu sang password_hash an toàn
            $this->updatePasswordHash($account['maTk'], password_hash($matKhau, PASSWORD_DEFAULT));
        }

        if ($isPasswordMatched) {
            // Không lưu mật khẩu hash trong kết quả trả về
            unset($account['matKhau']);
            return $account;
        }

        return false;
    }

    /**
     * Lấy thông tin tài khoản theo mã tài khoản (maTk)
     * @param string $maTk
     * @return array|false
     */
    public function getAccountById(string $maTk): array|false {
        $sql = "SELECT maTk, hoTen, email, tenTaiKhoan, ngayTao, role, trangThai 
                FROM accounts 
                WHERE maTk = :maTk 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maTk' => $maTk]);
        return $stmt->fetch();
    }

    /**
     * Đăng ký tài khoản mới cho khách hàng (Role mặc định: 'user')
     * @param string|null $maTk Mã tài khoản (nếu để null sẽ tự sinh tự động)
     * @param string $hoTen
     * @param string $email
     * @param string $tenTaiKhoan
     * @param string $matKhau
     * @return bool
     */
    public function register(?string $maTk, string $hoTen, string $email, string $tenTaiKhoan, string $matKhau): bool {
        // Tự sinh mã tài khoản dạng TK_... nếu chưa có
        if (empty($maTk)) {
            $maTk = 'TK_' . strtoupper(substr(uniqid(), -8));
        }

        // Băm mật khẩu an toàn bằng thuật toán mặc định của PHP (Bcrypt)
        $hashedPassword = password_hash($matKhau, PASSWORD_DEFAULT);
        $role = 'user';
        $trangThai = 'active';
        $ngayTao = date('Y-m-d H:i:s');

        $sql = "INSERT INTO accounts (maTk, hoTen, email, tenTaiKhoan, matKhau, ngayTao, role, trangThai) 
                VALUES (:maTk, :hoTen, :email, :tenTaiKhoan, :matKhau, :ngayTao, :role, :trangThai)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':maTk'        => $maTk,
            ':hoTen'       => $hoTen,
            ':email'       => $email,
            ':tenTaiKhoan' => $tenTaiKhoan,
            ':matKhau'     => $hashedPassword,
            ':ngayTao'     => $ngayTao,
            ':role'        => $role,
            ':trangThai'   => $trangThai,
        ]);

        if ($result && $role === 'user') {
            try {
                $newMaKH = 'KH' . strtoupper(substr(uniqid(), -6));
                $khStmt = $this->db->prepare("INSERT INTO khachhang (maKH, maTk, hoVaTen, email, soDienThoai) VALUES (:maKH, :maTk, :hoTen, :email, NULL)");
                $khStmt->execute([
                    ':maKH'  => $newMaKH,
                    ':maTk'  => $maTk,
                    ':hoTen' => $hoTen,
                    ':email' => $email
                ]);
            } catch (Exception $e) {
                // Nếu đã tồn tại hoặc bỏ qua lỗi khách hàng để không chặn đăng ký
                error_log("Error creating khachhang on register: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Kiểm tra tên tài khoản đã tồn tại chưa
     * @param string $tenTaiKhoan
     * @return bool
     */
    public function isUsernameExists(string $tenTaiKhoan): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM accounts WHERE tenTaiKhoan = :tenTaiKhoan");
        $stmt->execute([':tenTaiKhoan' => $tenTaiKhoan]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Kiểm tra email đã tồn tại chưa
     * @param string $email
     * @return bool
     */
    public function isEmailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM accounts WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Nâng cấp mật khẩu sang hash chuẩn
     */
    private function updatePasswordHash(string $maTk, string $newHash): void {
        $stmt = $this->db->prepare("UPDATE accounts SET matKhau = :newHash WHERE maTk = :maTk");
        $stmt->execute([':newHash' => $newHash, ':maTk' => $maTk]);
    }
}
