<?php
/**
 * File: models/ClientOrderModel.php
 * Xử lý kiểm tra voucher khuyến mãi và tạo đơn hàng Online
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class ClientOrderModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Kiểm tra tính hợp lệ của mã Voucher khuyến mãi
     */
    public function checkVoucher(string $voucherCode, float $subtotal): array {
        $code = trim($voucherCode);
        if (empty($code)) {
            return ['valid' => false, 'discount' => 0, 'message' => ''];
        }

        $sql = "SELECT maKM, tenKM, loaiKM, giaTriGiam, mucGiamToiDa, donHangToiThieu, ngayBatDau, ngayKetThuc, trangThai 
                FROM khuyenmai 
                WHERE maKM = :code AND trangThai = 1 LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        $km = $stmt->fetch();

        if (!$km) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Mã khuyến mãi không tồn tại hoặc đã hết hiệu lực!'];
        }

        $now = date('Y-m-d H:i:s');
        if ($now < $km['ngayBatDau'] || $now > $km['ngayKetThuc']) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Chương trình khuyến mãi này chưa bắt đầu hoặc đã kết thúc!'];
        }

        $minOrder = (float)$km['donHangToiThieu'];
        if ($subtotal < $minOrder) {
            return [
                'valid' => false, 
                'discount' => 0, 
                'message' => 'Đơn hàng tối thiểu ' . number_format($minOrder, 0, ',', '.') . ' đ mới được áp dụng mã này!'
            ];
        }

        // Tính tiền giảm giá
        $discount = 0.0;
        if ($km['loaiKM'] === 'PHAN_TRAM') {
            $discount = ($subtotal * (float)$km['giaTriGiam']) / 100.0;
            if (!empty($km['mucGiamToiDa']) && (float)$km['mucGiamToiDa'] > 0) {
                $discount = min($discount, (float)$km['mucGiamToiDa']);
            }
        } else {
            // TIEN_MAT
            $discount = (float)$km['giaTriGiam'];
        }

        $discount = min($discount, $subtotal);

        return [
            'valid'    => true,
            'maKM'     => $km['maKM'],
            'tenKM'    => $km['tenKM'],
            'discount' => $discount,
            'message'  => 'Áp dụng mã giảm giá thành công: -' . number_format($discount, 0, ',', '.') . ' đ'
        ];
    }

    /**
     * Sinh mã đơn hàng tiếp theo (ví dụ: DH005)
     */
    public function generateNextOrderCode(): string {
        $stmt = $this->db->query("SELECT maDH FROM donhang WHERE maDH REGEXP '^DH[0-9]+$' ORDER BY LENGTH(maDH) DESC, maDH DESC LIMIT 1");
        $lastCode = $stmt->fetchColumn();

        if ($lastCode && preg_match('/^DH(\d+)$/', (string)$lastCode, $matches)) {
            $nextNum = (int)$matches[1] + 1;
            return sprintf("DH%03d", $nextNum);
        }

        return "DH" . date('ymd') . rand(100, 999);
    }

    /**
     * Đảm bảo khách hàng có mã KH (nếu vãng lai thì tự tạo bản ghi khachhang)
     */
    public function getOrCreateCustomer(array $customerInfo, ?string $maTk = null): string {
        $phone = trim($customerInfo['sdtNhanHang'] ?? '');
        $name  = trim($customerInfo['tenNguoiNhan'] ?? 'Khách Mua Online');
        $email = trim($customerInfo['email'] ?? '');
        $addr  = trim($customerInfo['diaChiGiaoHang'] ?? '');

        // Nếu đã có maTk
        if (!empty($maTk)) {
            $stmt = $this->db->prepare("SELECT maKH FROM khachhang WHERE maTk = :maTk LIMIT 1");
            $stmt->execute([':maTk' => $maTk]);
            $maKH = $stmt->fetchColumn();
            if ($maKH) return (string)$maKH;
        }

        // Tìm theo số điện thoại
        if (!empty($phone)) {
            $stmt = $this->db->prepare("SELECT maKH FROM khachhang WHERE soDienThoai = :phone LIMIT 1");
            $stmt->execute([':phone' => $phone]);
            $maKH = $stmt->fetchColumn();
            if ($maKH) return (string)$maKH;
        }

        // Tự tạo mã KH mới
        $stmt = $this->db->query("SELECT maKH FROM khachhang WHERE maKH REGEXP '^KH[0-9]+$' ORDER BY LENGTH(maKH) DESC, maKH DESC LIMIT 1");
        $lastKH = $stmt->fetchColumn();
        if ($lastKH && preg_match('/^KH(\d+)$/', (string)$lastKH, $matches)) {
            $nextNum = (int)$matches[1] + 1;
            $newMaKH = sprintf("KH%03d", $nextNum);
        } else {
            $newMaKH = "KH" . rand(100, 999);
        }

        $insertStmt = $this->db->prepare("INSERT INTO khachhang (maKH, maTk, hoVaTen, soDienThoai, email, diaChi) VALUES (:maKH, :maTk, :name, :phone, :email, :addr)");
        $insertStmt->execute([
            ':maKH'  => $newMaKH,
            ':maTk'  => $maTk ?: null,
            ':name'  => $name,
            ':phone' => !empty($phone) ? $phone : null,
            ':email' => $email ?: null,
            ':addr'  => $addr
        ]);

        return $newMaKH;
    }

    /**
     * Tạo Đơn Hàng Mua Trực Tuyến (Online Order) với PDO Transaction
     * 
     * Hỗ trợ 2 kiểu gọi:
     * 1. createOnlineOrder(array $customerInfo, array $cartItems, string $paymentMethod = 'COD', string $note = '')
     * 2. createOnlineOrder(?string $maKH, array $customerInfo, array $cartItems, string $voucherCode = '', string $paymentMethod = 'COD')
     * 
     * @return array|string Trả về maDH (hoặc mảng ['success' => true, 'maDH' => $maDH])
     */
    public function createOnlineOrder(
        $param1,
        array $param2 = [],
        $param3 = 'COD',
        $param4 = '',
        ?string $param5 = null,
        string $param6 = ''
    ): array|string {
        // Phân tích kiểu tham số đầu vào
        if (is_array($param1)) {
            // Kiểu 1: createOnlineOrder($customerInfo, $cartItems, $paymentMethod, $note)
            $customerInfo  = $param1;
            $cartItems     = $param2;
            $paymentMethod = is_string($param3) ? $param3 : 'COD';
            $note          = is_string($param4) ? $param4 : ($customerInfo['ghiChuKhachHang'] ?? '');
            $maKH          = $param5;
            $voucherCode   = $param6 ?: ($customerInfo['voucherCode'] ?? $_SESSION['applied_voucher'] ?? '');
            $asString      = true; // Trả về trực tiếp maDH theo đặc tả
        } else {
            // Kiểu 2: createOnlineOrder($maKH, $customerInfo, $cartItems, $voucherCode, $paymentMethod)
            $maKH          = $param1;
            $customerInfo  = $param2;
            $cartItems     = is_array($param3) ? $param3 : [];
            $voucherCode   = is_string($param4) ? $param4 : '';
            $paymentMethod = is_string($param5) ? $param5 : 'COD';
            $note          = $customerInfo['ghiChuKhachHang'] ?? '';
            $asString      = false;
        }

        if (empty($cartItems)) {
            if ($asString) {
                throw new Exception('Giỏ hàng của bạn đang trống!');
            }
            return ['success' => false, 'message' => 'Giỏ hàng của bạn đang trống!'];
        }

        // 1. Tính tổng tiền hàng và kiểm tra lại tồn kho thực tế
        $tongTienHang = 0.0;
        foreach ($cartItems as $item) {
            $checkStmt = $this->db->prepare("SELECT tenSP, giaBan, soLuongTon FROM sanpham WHERE maSP = :maSP AND trangThai = 1 AND isDeleted = 0");
            $checkStmt->execute([':maSP' => $item['maSP']]);
            $prod = $checkStmt->fetch();

            if (!$prod) {
                $err = "Sản phẩm {$item['tenSP']} đã ngừng kinh doanh!";
                if ($asString) throw new Exception($err);
                return ['success' => false, 'message' => $err];
            }

            if ((int)$prod['soLuongTon'] < (int)$item['soLuong']) {
                $err = "Sản phẩm {$prod['tenSP']} chỉ còn {$prod['soLuongTon']} máy trong kho, không đủ đáp ứng số lượng bạn đặt!";
                if ($asString) throw new Exception($err);
                return ['success' => false, 'message' => $err];
            }

            $tongTienHang += (float)$prod['giaBan'] * (int)$item['soLuong'];
        }

        // 2. Tính phí vận chuyển (Cố định 50.000đ cho đơn Online)
        $phiVanChuyen = 50000.0;

        // 3. Kiểm tra voucher nếu có
        $tienGiamGia = 0.0;
        $maKM = null;
        if (!empty($voucherCode)) {
            $voucherRes = $this->checkVoucher($voucherCode, $tongTienHang);
            if ($voucherRes['valid']) {
                $tienGiamGia = $voucherRes['discount'];
                $maKM = $voucherRes['maKM'];
            }
        }

        $tongThanhToan = max(0, $tongTienHang + $phiVanChuyen - $tienGiamGia);

        // 4. Bắt đầu PDO Transaction an toàn tuyệt đối
        try {
            $this->db->beginTransaction();

            // Lấy mã khách hàng: nếu khách đã đăng nhập lấy maKH; nếu vãng lai tạo mới (maTk = NULL)
            if (empty($maKH)) {
                $currentUser = $_SESSION['user'] ?? null;
                $maTk = !empty($currentUser['maTk']) ? $currentUser['maTk'] : null;
                $maKH = $this->getOrCreateCustomer($customerInfo, $maTk);
            }

            // Tự động sinh mã đơn hàng tiếp theo (ví dụ: DH005, DH006)
            $maDH = $this->generateNextOrderCode();

            // Tạo bản ghi DonHang (kenhBan = 'ONLINE', trangThaiDon = 'CHO_DUYET', phiVanChuyen = 50000)
            $orderSql = "INSERT INTO donhang (
                            maDH, maKH, maKM, nguoiPhuTrach, ngayTao, kenhBan,
                            phuongThucThanhToan, phuongThucGiaoHang, phiVanChuyen,
                            tongTienHang, tienGiamGia, tongThanhToan, trangThaiDon
                        ) VALUES (
                            :maDH, :maKH, :maKM, NULL, NOW(), 'ONLINE',
                            :paymentMethod, 'Giao hàng tận nơi', :phiVanChuyen,
                            :tongTienHang, :tienGiamGia, :tongThanhToan, 'CHO_DUYET'
                        )";

            $orderStmt = $this->db->prepare($orderSql);
            $orderStmt->execute([
                ':maDH'          => $maDH,
                ':maKH'          => $maKH,
                ':maKM'          => $maKM,
                ':paymentMethod' => $paymentMethod,
                ':phiVanChuyen'  => $phiVanChuyen,
                ':tongTienHang'  => $tongTienHang,
                ':tienGiamGia'   => $tienGiamGia,
                ':tongThanhToan' => $tongThanhToan
            ]);

            // Tạo bản ghi UserDatHang lưu: tenNguoiNhan, sdtNhanHang, diaChiGiaoHang, ghiChuKhachHang, trangThaiThanhToan
            $userOrderSql = "INSERT INTO userdathang (
                                maDH, tenNguoiNhan, sdtNhanHang, diaChiGiaoHang,
                                ghiChuKhachHang, maGiaoDichOnline, trangThaiThanhToan
                            ) VALUES (
                                :maDH, :tenNguoiNhan, :sdtNhanHang, :diaChiGiaoHang,
                                :ghiChu, :maGD, :trangThaiTT
                            )";

            $userStmt = $this->db->prepare($userOrderSql);
            $userStmt->execute([
                ':maDH'            => $maDH,
                ':tenNguoiNhan'    => trim($customerInfo['tenNguoiNhan'] ?? ''),
                ':sdtNhanHang'     => trim($customerInfo['sdtNhanHang'] ?? ''),
                ':diaChiGiaoHang'  => trim($customerInfo['diaChiGiaoHang'] ?? ''),
                ':ghiChu'          => trim(!empty($note) ? $note : ($customerInfo['ghiChuKhachHang'] ?? '')),
                ':maGD'            => ($paymentMethod === 'BANK_TRANSFER') ? ('BANK_' . date('His') . rand(10, 99)) : null,
                ':trangThaiTT'     => ($paymentMethod === 'BANK_TRANSFER') ? 'DANG_XU_LY' : 'CHUA_THANH_TOAN'
            ]);

            // Ghi từng sản phẩm trong giỏ vào ChiTietDonHang & cập nhật trừ tồn kho SanPham
            $detailSql = "INSERT INTO chitietdonhang (maDH, maSP, soLuong, donGia, thanhTien) 
                          VALUES (:maDH, :maSP, :soLuong, :donGia, :thanhTien)";
            $detailStmt = $this->db->prepare($detailSql);

            $updateStockSql = "UPDATE sanpham SET soLuongTon = soLuongTon - :qty WHERE maSP = :maSP";
            $updateStockStmt = $this->db->prepare($updateStockSql);

            foreach ($cartItems as $item) {
                $qty   = (int)$item['soLuong'];
                $price = (float)$item['donGia'];
                $total = $price * $qty;

                $detailStmt->execute([
                    ':maDH'      => $maDH,
                    ':maSP'      => $item['maSP'],
                    ':soLuong'   => $qty,
                    ':donGia'    => $price,
                    ':thanhTien' => $total
                ]);
                $idChiTietDH = (int)$this->db->lastInsertId();

                // Trừ tồn kho thực tế
                $updateStockStmt->execute([
                    ':qty'  => $qty,
                    ':maSP' => $item['maSP']
                ]);

                // Tự động gán số Serial/IMEI còn trong kho cho sản phẩm này
                $findSerialsSql = "SELECT maSerial_IMEI FROM serialsanpham 
                                   WHERE maSP = :maSP AND trangThai = 'Trong kho' AND idChiTietDonHang IS NULL 
                                   ORDER BY maSerial_IMEI ASC 
                                   LIMIT " . (int)$qty;
                $findSerialsStmt = $this->db->prepare($findSerialsSql);
                $findSerialsStmt->execute([':maSP' => $item['maSP']]);
                $availSerials = $findSerialsStmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($availSerials)) {
                    $assignSerialSql = "UPDATE serialsanpham 
                                        SET idChiTietDonHang = :idCT, 
                                            trangThai = 'Đã bán', 
                                            ngayXuatKho = NOW() 
                                        WHERE maSerial_IMEI = :serial";
                    $assignSerialStmt = $this->db->prepare($assignSerialSql);
                    foreach ($availSerials as $ser) {
                        $assignSerialStmt->execute([
                            ':idCT'   => $idChiTietDH,
                            ':serial' => $ser
                        ]);
                    }
                }
            }

            // Xóa giỏ hàng (cả trong DB lẫn trong Session)
            require_once __DIR__ . '/CartModel.php';
            $cartModel = new CartModel();
            $cartModel->clearCart($maKH);

            // Hoàn tất Transaction
            $this->db->commit();

            if ($asString) {
                return $maDH;
            }

            return [
                'success' => true,
                'maDH'    => $maDH,
                'message' => 'Đặt hàng thành công!'
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($asString) {
                throw $e;
            }
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo đơn hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Phương thức đặt mua đơn hàng mới chuẩn Transaction (placeOrder)
     * Thêm vào DonHang, UserDatHang, ChiTietDonHang, trừ tồn kho và làm trống giỏ hàng
     */
    public function placeOrder(array $orderData, array $shippingData, array $cartItems): string {
        $maKH = $orderData['maKH'] ?? null;
        $customerInfo = [
            'tenNguoiNhan'    => $shippingData['tenNguoiNhan'] ?? ($orderData['tenNguoiNhan'] ?? ''),
            'sdtNhanHang'     => $shippingData['sdtNhanHang'] ?? ($orderData['sdtNhanHang'] ?? ''),
            'email'           => $shippingData['email'] ?? ($orderData['email'] ?? ''),
            'diaChiGiaoHang'  => $shippingData['diaChiGiaoHang'] ?? ($orderData['diaChiGiaoHang'] ?? ''),
            'ghiChuKhachHang' => $shippingData['ghiChuKhachHang'] ?? ($orderData['ghiChuKhachHang'] ?? '')
        ];
        $voucherCode = $orderData['voucherCode'] ?? '';
        $paymentMethod = $orderData['phuongThucThanhToan'] ?? ($shippingData['phuongThucThanhToan'] ?? 'COD');

        $result = $this->createOnlineOrder($maKH, $customerInfo, $cartItems, $voucherCode, $paymentMethod);
        if ($result['success']) {
            return (string)$result['maDH'];
        }
        throw new Exception($result['message'] ?? 'Không thể khởi tạo đơn hàng!');
    }

    /**
     * Lấy thông tin đơn hàng để hiển thị trang thành công
     */
    public function getOrderSummary(string $maDH): array|false {
        $sql = "SELECT 
                    dh.*,
                    udh.tenNguoiNhan, udh.sdtNhanHang, udh.diaChiGiaoHang, udh.ghiChuKhachHang, 
                    udh.maGiaoDichOnline, udh.trangThaiThanhToan AS trangThaiOnline,
                    km.tenKM
                FROM donhang dh
                LEFT JOIN userdathang udh ON dh.maDH = udh.maDH
                LEFT JOIN khuyenmai km ON dh.maKM = km.maKM
                WHERE dh.maDH = :maDH LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maDH' => $maDH]);
        $order = $stmt->fetch();

        if ($order) {
            $itemsSql = "SELECT ctd.*, sp.tenSP, sp.hinhAnh, sp.thoiGianBaoHanh, th.tenTH,
                                GROUP_CONCAT(sr.maSerial_IMEI SEPARATOR ', ') AS maSerial_IMEI
                         FROM chitietdonhang ctd
                         LEFT JOIN sanpham sp ON ctd.maSP = sp.maSP
                         LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                         LEFT JOIN serialsanpham sr ON ctd.id = sr.idChiTietDonHang
                         WHERE ctd.maDH = :maDH
                         GROUP BY ctd.id, ctd.maDH, ctd.maSP, ctd.soLuong, ctd.donGia, ctd.thanhTien, sp.tenSP, sp.hinhAnh, sp.thoiGianBaoHanh, th.tenTH";
            $itemStmt = $this->db->prepare($itemsSql);
            $itemStmt->execute([':maDH' => $maDH]);
            $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $order;
    }

    /**
     * Lấy danh sách sản phẩm theo mã đơn (maDH) kèm số Serial/IMEI và thời gian bảo hành
     *
     * @param string $maDH Mã đơn hàng
     * @return array Danh sách chi tiết sản phẩm kèm maSerial_IMEI
     */
    public function getOrderItems(string $maDH): array {
        $sql = "SELECT ctdh.*, sp.tenSP, sp.thoiGianBaoHanh, sp.hinhAnh,
                       GROUP_CONCAT(sr.maSerial_IMEI SEPARATOR ', ') AS maSerial_IMEI
                FROM chitietdonhang ctdh
                INNER JOIN sanpham sp ON ctdh.maSP = sp.maSP
                LEFT JOIN serialsanpham sr ON ctdh.id = sr.idChiTietDonHang
                WHERE ctdh.maDH = :maDH
                GROUP BY ctdh.id, ctdh.maDH, ctdh.maSP, ctdh.soLuong, ctdh.donGia, ctdh.thanhTien, sp.tenSP, sp.hinhAnh, sp.thoiGianBaoHanh";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maDH' => $maDH]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
