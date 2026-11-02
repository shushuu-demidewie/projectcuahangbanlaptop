<?php
/**
 * File: models/CartModel.php
 * Quản lý Giỏ hàng của khách hàng (Cả Database và Session)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class CartModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Lấy mã khách hàng (maKH) từ tài khoản đang đăng nhập
     */
    public function getCustomerCodeByAccount(?string $maTk): ?string {
        if (empty($maTk)) return null;

        $stmt = $this->db->prepare("SELECT maKH FROM khachhang WHERE maTk = :maTk LIMIT 1");
        $stmt->execute([':maTk' => $maTk]);
        $maKH = $stmt->fetchColumn();

        if ($maKH) {
            return (string)$maKH;
        }

        // Nếu tài khoản chưa có bản ghi khachhang, tự động tạo mới
        $accStmt = $this->db->prepare("SELECT hoTen, email FROM accounts WHERE maTk = :maTk LIMIT 1");
        $accStmt->execute([':maTk' => $maTk]);
        $acc = $accStmt->fetch();

        if ($acc) {
            $newMaKH = 'KH' . strtoupper(substr(uniqid(), -6));
            $insertStmt = $this->db->prepare("INSERT INTO khachhang (maKH, maTk, hoVaTen, email, soDienThoai) VALUES (:maKH, :maTk, :hoTen, :email, :phone)");
            $insertStmt->execute([
                ':maKH'  => $newMaKH,
                ':maTk'  => $maTk,
                ':hoTen' => $acc['hoTen'] ?? 'Khách Hàng',
                ':email' => $acc['email'] ?? null,
                ':phone' => null
            ]);
            return $newMaKH;
        }

        return null;
    }

    /**
     * Đồng bộ giỏ hàng từ Session vào Cơ sở dữ liệu khi khách hàng đăng nhập
     */
    public function syncSessionCartToDatabase(string $maKH): void {
        if (empty($_SESSION['cart']) || empty($maKH)) return;

        foreach ($_SESSION['cart'] as $maSP => $qty) {
            $this->addToCart($maKH, $maSP, (int)$qty);
        }

        // Xóa giỏ hàng tạm trong session sau khi đã đồng bộ
        $_SESSION['cart'] = [];
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng (kèm thông tin laptop & kiểm tra tồn kho)
     */
    public function getCartItems(?string $maKH = null): array {
        if (!empty($maKH)) {
            // Lấy từ Database
            $sql = "SELECT 
                        gh.maGioHang, gh.maKH, gh.maSP, gh.soLuong, gh.ngayCapNhat,
                        sp.tenSP, sp.giaBan, sp.soLuongTon, sp.thoiGianBaoHanh, sp.thongSoKyThuat, sp.hinhAnh,
                        th.tenTH
                    FROM giohang gh
                    INNER JOIN sanpham sp ON gh.maSP = sp.maSP
                    LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                    WHERE gh.maKH = :maKH AND sp.trangThai = 1 AND sp.isDeleted = 0
                    ORDER BY gh.maGioHang DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':maKH' => $maKH]);
            $items = $stmt->fetchAll();
        } else {
            // Lấy từ Session (Khách vãng lai)
            $items = [];
            if (!empty($_SESSION['cart'])) {
                $productCodes = array_keys($_SESSION['cart']);
                if (!empty($productCodes)) {
                    $inClause = implode(',', array_fill(0, count($productCodes), '?'));
                    $sql = "SELECT 
                                sp.maSP, sp.tenSP, sp.giaBan, sp.soLuongTon, sp.thoiGianBaoHanh, sp.thongSoKyThuat, sp.hinhAnh,
                                th.tenTH
                            FROM sanpham sp
                            LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                            WHERE sp.maSP IN ($inClause) AND sp.trangThai = 1 AND sp.isDeleted = 0";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($productCodes);
                    $products = $stmt->fetchAll();

                    foreach ($products as $p) {
                        $maSP = $p['maSP'];
                        $qty = (int)($_SESSION['cart'][$maSP] ?? 1);
                        $items[] = [
                            'maGioHang'       => $maSP, // dùng maSP làm key tạm cho session
                            'maKH'            => null,
                            'maSP'            => $maSP,
                            'soLuong'         => $qty,
                            'tenSP'           => $p['tenSP'],
                            'giaBan'          => $p['giaBan'],
                            'soLuongTon'      => $p['soLuongTon'],
                            'thoiGianBaoHanh' => $p['thoiGianBaoHanh'],
                            'thongSoKyThuat'  => $p['thongSoKyThuat'],
                            'hinhAnh'         => $p['hinhAnh'],
                            'tenTH'           => $p['tenTH']
                        ];
                    }
                }
            }
        }

        // Tính thành tiền và cảnh báo vượt quá tồn kho
        foreach ($items as &$item) {
            $item['donGia'] = (float)$item['giaBan'];
            // Nếu số lượng trong giỏ vượt quá tồn kho thực tế, cảnh báo hoặc điều chỉnh
            $item['isOutOfStock'] = ($item['soLuongTon'] <= 0);
            $item['isOverStock']  = ($item['soLuong'] > $item['soLuongTon']);
            $item['thanhTien']    = $item['donGia'] * (int)$item['soLuong'];
        }

        return $items;
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addToCart(?string $maKH, string $maSP, int $quantity = 1): array {
        // Kiểm tra tồn kho của sản phẩm
        $stmt = $this->db->prepare("SELECT tenSP, soLuongTon, giaBan, hinhAnh FROM sanpham WHERE maSP = :maSP AND trangThai = 1 AND isDeleted = 0 LIMIT 1");
        $stmt->execute([':maSP' => $maSP]);
        $prod = $stmt->fetch();

        if (!$prod) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh!'];
        }

        $stock = (int)$prod['soLuongTon'];
        if ($stock <= 0) {
            return ['success' => false, 'message' => 'Sản phẩm này hiện đang tạm hết hàng!'];
        }

        if (!empty($maKH)) {
            // Đã đăng nhập: Lưu vào bảng giohang
            $checkStmt = $this->db->prepare("SELECT maGioHang, soLuong FROM giohang WHERE maKH = :maKH AND maSP = :maSP LIMIT 1");
            $checkStmt->execute([':maKH' => $maKH, ':maSP' => $maSP]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                $newQty = (int)$existing['soLuong'] + $quantity;
                if ($newQty > $stock) {
                    $newQty = $stock; // Tối đa bằng tồn kho
                }
                $updateStmt = $this->db->prepare("UPDATE giohang SET soLuong = :qty, ngayCapNhat = NOW() WHERE maGioHang = :id");
                $updateStmt->execute([':qty' => $newQty, ':id' => $existing['maGioHang']]);
            } else {
                $insertQty = min($quantity, $stock);
                $insertStmt = $this->db->prepare("INSERT INTO giohang (maKH, maSP, soLuong, ngayCapNhat) VALUES (:maKH, :maSP, :qty, NOW())");
                $insertStmt->execute([
                    ':maKH' => $maKH,
                    ':maSP' => $maSP,
                    ':qty'  => $insertQty
                ]);
            }
        } else {
            // Chưa đăng nhập: Lưu vào session
            $currentQty = (int)($_SESSION['cart'][$maSP] ?? 0);
            $newQty = $currentQty + $quantity;
            if ($newQty > $stock) {
                $newQty = $stock;
            }
            $_SESSION['cart'][$maSP] = $newQty;
        }

        return [
            'success'   => true, 
            'message'   => 'Đã thêm laptop vào giỏ hàng thành công!',
            'cartCount' => $this->getCartCount($maKH),
            'product'   => [
                'maSP'    => $maSP,
                'tenSP'   => $prod['tenSP'],
                'giaBan'  => (float)$prod['giaBan'],
                'hinhAnh' => !empty($prod['hinhAnh']) ? $prod['hinhAnh'] : ('assets/images/products/' . $maSP . '.jpg')
            ]
        ];
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     * Hỗ trợ cả 2 dạng gọi:
     * - updateQuantity($maSP, $quantity)
     * - updateQuantity($maKH, $identifier, $quantity)
     */
    public function updateQuantity($param1, $param2, ?int $param3 = null): array {
        if ($param3 !== null) {
            $maKH       = is_string($param1) ? $param1 : null;
            $identifier = $param2;
            $quantity   = (int)$param3;
        } else {
            // Gọi dạng updateQuantity($identifier, $quantity)
            $identifier = $param1;
            $quantity   = (int)$param2;
            $maKH       = null;

            // Nếu identifier là id bản ghi giohang trong DB
            if (is_numeric($identifier)) {
                $stmt = $this->db->prepare("SELECT maKH FROM giohang WHERE maGioHang = :id LIMIT 1");
                $stmt->execute([':id' => $identifier]);
                $maKH = $stmt->fetchColumn() ?: null;
            } else {
                // Kiểm tra tài khoản hiện tại từ Session
                $currentUser = $_SESSION['user'] ?? null;
                if (!empty($currentUser['maTk'])) {
                    $maKH = $this->getCustomerCodeByAccount($currentUser['maTk']);
                }
            }
        }

        if ($quantity <= 0) {
            return $this->removeItem($identifier, $maKH);
        }

        if (!empty($maKH)) {
            // Đã đăng nhập: Cập nhật trong bảng giohang
            if (is_numeric($identifier)) {
                $stmt = $this->db->prepare("SELECT gh.maGioHang, gh.maSP, sp.soLuongTon FROM giohang gh 
                                            INNER JOIN sanpham sp ON gh.maSP = sp.maSP 
                                            WHERE gh.maGioHang = :id AND gh.maKH = :maKH LIMIT 1");
                $stmt->execute([':id' => $identifier, ':maKH' => $maKH]);
            } else {
                $stmt = $this->db->prepare("SELECT gh.maGioHang, gh.maSP, sp.soLuongTon FROM giohang gh 
                                            INNER JOIN sanpham sp ON gh.maSP = sp.maSP 
                                            WHERE gh.maSP = :maSP AND gh.maKH = :maKH LIMIT 1");
                $stmt->execute([':maSP' => $identifier, ':maKH' => $maKH]);
            }
            $item = $stmt->fetch();

            if (!$item) {
                return ['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng!'];
            }

            $stock = (int)$item['soLuongTon'];
            $finalQty = min($quantity, $stock);

            $updateStmt = $this->db->prepare("UPDATE giohang SET soLuong = :qty, ngayCapNhat = NOW() WHERE maGioHang = :id");
            $updateStmt->execute([':qty' => $finalQty, ':id' => $item['maGioHang']]);
        } else {
            // Chưa đăng nhập: Cập nhật trong Session
            $maSP = (string)$identifier;
            $stmt = $this->db->prepare("SELECT soLuongTon FROM sanpham WHERE maSP = :maSP LIMIT 1");
            $stmt->execute([':maSP' => $maSP]);
            $stock = (int)$stmt->fetchColumn();

            $finalQty = min($quantity, $stock);
            $_SESSION['cart'][$maSP] = $finalQty;
        }

        return [
            'success'   => true,
            'message'   => 'Đã cập nhật số lượng!',
            'cartCount' => $this->getCartCount($maKH)
        ];
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ hàng (theo yêu cầu đề bài removeItem($maSP))
     * Hỗ trợ cả removeItem($maSP) và removeItem($identifier, $maKH)
     */
    public function removeItem(string|int $identifier, ?string $maKH = null): array {
        return $this->removeFromCart($maKH, $identifier);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeFromCart(?string $maKH, string|int $identifier): array {
        if (!empty($maKH)) {
            if (is_numeric($identifier)) {
                $stmt = $this->db->prepare("DELETE FROM giohang WHERE maGioHang = :id AND maKH = :maKH");
                $stmt->execute([':id' => $identifier, ':maKH' => $maKH]);
            } else {
                $stmt = $this->db->prepare("DELETE FROM giohang WHERE maSP = :maSP AND maKH = :maKH");
                $stmt->execute([':maSP' => $identifier, ':maKH' => $maKH]);
            }
        } else {
            $maSP = (string)$identifier;
            unset($_SESSION['cart'][$maSP]);

            // Nếu identifier là maGioHang mà session không có, kiểm tra DB nếu có
            if (is_numeric($identifier)) {
                $stmt = $this->db->prepare("DELETE FROM giohang WHERE maGioHang = :id");
                $stmt->execute([':id' => $identifier]);
            }
        }

        return [
            'success'   => true,
            'message'   => 'Đã xóa sản phẩm khỏi giỏ hàng!',
            'cartCount' => $this->getCartCount($maKH)
        ];
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ của khách hàng (JOIN SanPham) kèm giá bán, số lượng, tính tổng tiền giỏ hàng
     */
    public function getCartByCustomer(?string $maKH = null): array {
        $items = $this->getCartItems($maKH);
        $totalAmount = 0.0;
        foreach ($items as $item) {
            $totalAmount += (float)$item['thanhTien'];
        }
        return [
            'items'       => $items,
            'totalAmount' => $totalAmount,
            'cartCount'   => count($items)
        ];
    }

    /**
     * Cập nhật số lượng trong giỏ
     */
    public function updateCartQuantity(int|string $maGioHang, int $quantity): array {
        if (is_numeric($maGioHang)) {
            $stmt = $this->db->prepare("SELECT maKH FROM giohang WHERE maGioHang = :id LIMIT 1");
            $stmt->execute([':id' => $maGioHang]);
            $maKH = $stmt->fetchColumn() ?: null;
            return $this->updateQuantity($maKH ? (string)$maKH : null, $maGioHang, $quantity);
        }
        return $this->updateQuantity(null, $maGioHang, $quantity);
    }

    /**
     * Xóa một mục khỏi giỏ hàng
     */
    public function removeCartItem(int|string $maGioHang): array {
        if (is_numeric($maGioHang)) {
            $stmt = $this->db->prepare("SELECT maKH FROM giohang WHERE maGioHang = :id LIMIT 1");
            $stmt->execute([':id' => $maGioHang]);
            $maKH = $stmt->fetchColumn() ?: null;
            return $this->removeFromCart($maKH ? (string)$maKH : null, $maGioHang);
        }
        return $this->removeFromCart(null, $maGioHang);
    }

    /**
     * Làm trống giỏ hàng sau khi đặt hàng thành công
     */
    public function clearCart(?string $maKH): void {
        if (!empty($maKH)) {
            $stmt = $this->db->prepare("DELETE FROM giohang WHERE maKH = :maKH");
            $stmt->execute([':maKH' => $maKH]);
        }
        $_SESSION['cart'] = [];
    }

    /**
     * Đếm tổng số lượng món hàng trong giỏ để hiển thị huy hiệu trên Header
     */
    public function getCartCount(?string $maKH = null): int {
        if (!empty($maKH)) {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(soLuong), 0) FROM giohang WHERE maKH = :maKH");
            $stmt->execute([':maKH' => $maKH]);
            return (int)$stmt->fetchColumn();
        }

        if (!empty($_SESSION['cart'])) {
            return (int)array_sum($_SESSION['cart']);
        }

        return 0;
    }
}
