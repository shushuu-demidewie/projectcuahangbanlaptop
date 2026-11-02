<?php
/**
 * File: models/PosModel.php
 * Nghiệp vụ Bán hàng tại quầy (POS) dành cho Thu ngân / Nhân viên bán hàng
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class PosModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách laptop đang có hàng trong kho (soLuongTon > 0, trangThai = 1, isDeleted = 0)
     */
    public function getInStockProducts(?string $keyword = null, ?string $brandId = null): array {
        $sql = "SELECT 
                    sp.maSP,
                    sp.tenSP,
                    sp.giaBan,
                    sp.soLuongTon,
                    sp.thoiGianBaoHanh,
                    sp.hinhAnh,
                    sp.viTriKho,
                    th.tenTH,
                    th.maTH,
                    dm.tenDM,
                    dm.maDM
                FROM sanpham sp
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                WHERE sp.trangThai = 1 
                  AND sp.isDeleted = 0 
                  AND sp.soLuongTon > 0";

        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (sp.tenSP LIKE :kw1 OR sp.maSP LIKE :kw2 OR th.tenTH LIKE :kw3)";
            $kwVal = '%' . trim($keyword) . '%';
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
        }

        if (!empty($brandId)) {
            $sql .= " AND sp.maTH = :brandId";
            $params[':brandId'] = trim($brandId);
        }

        $sql .= " ORDER BY sp.soLuongTon DESC, sp.tenSP ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách các mã Serial/IMEI cụ thể đang 'Trong kho' của máy được chọn để thu ngân gán cho khách
     */
    public function getAvailableSerials(string $maSP): array {
        $sql = "SELECT 
                    maSerial_IMEI,
                    maSP,
                    trangThai,
                    ghiChu
                FROM serialsanpham
                WHERE maSP = :maSP 
                  AND trangThai = 'Trong kho'
                ORDER BY maSerial_IMEI ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maSP' => $maSP]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm nhanh khách hàng theo số điện thoại
     */
    public function findCustomerByPhone(string $sdt): ?array {
        $sdt = trim($sdt);
        if ($sdt === '') {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM khachhang WHERE soDienThoai = :sdt LIMIT 1");
        $stmt->execute([':sdt' => $sdt]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Thêm nhanh khách hàng mới tại quầy (maTk = NULL)
     */
    public function createQuickCustomer(string $hoTen, string $sdt, ?string $diaChi = null): array {
        $hoTen = trim($hoTen) !== '' ? trim($hoTen) : 'Khách Mua Tại Quầy';
        $sdt   = trim($sdt);
        $diaChi = !empty($diaChi) ? trim($diaChi) : 'Mua trực tiếp tại quầy';

        // Kiểm tra xem khách đã tồn tại chưa
        if (!empty($sdt)) {
            $existing = $this->findCustomerByPhone($sdt);
            if ($existing) {
                return $existing;
            }
        }

        // Tự sinh mã khách hàng mới
        $stmt = $this->db->query("SELECT maKH FROM khachhang WHERE maKH REGEXP '^KH[0-9]+$' ORDER BY LENGTH(maKH) DESC, maKH DESC LIMIT 1");
        $lastKH = $stmt->fetchColumn();
        if ($lastKH && preg_match('/^KH(\d+)$/', (string)$lastKH, $matches)) {
            $nextNum = (int)$matches[1] + 1;
            $newMaKH = sprintf("KH%03d", $nextNum);
        } else {
            $newMaKH = 'KH' . strtoupper(substr(uniqid(), -6));
        }

        $sql = "INSERT INTO khachhang (maKH, maTk, hoVaTen, soDienThoai, diaChi, doiTuongUuTien) 
                VALUES (:maKH, NULL, :hoTen, :sdt, :diaChi, 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':maKH'   => $newMaKH,
            ':hoTen'  => $hoTen,
            ':sdt'    => !empty($sdt) ? $sdt : null,
            ':diaChi' => $diaChi
        ]);

        return [
            'maKH'           => $newMaKH,
            'maTk'           => null,
            'hoVaTen'        => $hoTen,
            'soDienThoai'    => $sdt,
            'diaChi'         => $diaChi,
            'doiTuongUuTien' => 0
        ];
    }

    /**
     * Sinh mã đơn hàng tiếp theo (ví dụ: DH001, DH002...)
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
     * Xử lý tạo Đơn hàng POS tại quầy chuẩn PDO Transaction
     * 
     * @param array $orderData ['maKH', 'hoTen', 'soDienThoai', 'diaChi', 'phuongThucThanhToan', 'tienGiamGia', 'ghiChu']
     * @param array $cartItems Danh sách sản phẩm: [['maSP' => ..., 'soLuong' => ..., 'donGia' => ..., 'serials' => ['SN01', ...]], ...]
     * @param string $cashierId Mã tài khoản nhân viên thu ngân
     * @return array ['success' => bool, 'maDH' => string, 'maHD' => string, 'message' => string]
     */
    public function processPosOrder(array $orderData, array $cartItems, string $cashierId): array {
        if (empty($cartItems)) {
            throw new InvalidArgumentException('Danh sách hàng bán tại quầy không được để trống!');
        }

        $this->db->beginTransaction();

        try {
            // 1. Xác định hoặc tạo nhanh khách hàng
            $maKH = $orderData['maKH'] ?? null;
            $customerName  = trim($orderData['hoTen'] ?? 'Khách Mua Tại Quầy');
            $customerPhone = trim($orderData['soDienThoai'] ?? '');
            $customerAddr  = trim($orderData['diaChi'] ?? 'Tại cửa hàng EmShu Store');

            if (!empty($customerPhone)) {
                $cust = $this->findCustomerByPhone($customerPhone);
                if ($cust) {
                    $maKH = $cust['maKH'];
                    $customerName = $cust['hoVaTen'];
                    $customerAddr = $cust['diaChi'] ?: $customerAddr;
                } else {
                    $newCust = $this->createQuickCustomer($customerName, $customerPhone, $customerAddr);
                    $maKH = $newCust['maKH'];
                }
            } elseif (empty($maKH)) {
                // Khách vãng lai không để lại số điện thoại
                $newCust = $this->createQuickCustomer($customerName, '', $customerAddr);
                $maKH = $newCust['maKH'];
            }

            // 2. Tính toán tổng tiền và kiểm tra số lượng tồn kho + serial hợp lệ
            $tongTienHang = 0.0;
            $validatedItems = [];

            $stmtCheckProduct = $this->db->prepare("SELECT maSP, tenSP, giaBan, soLuongTon, thoiGianBaoHanh FROM sanpham WHERE maSP = :maSP AND trangThai = 1 AND isDeleted = 0 FOR UPDATE");
            $stmtCheckSerial  = $this->db->prepare("SELECT maSerial_IMEI FROM serialsanpham WHERE maSerial_IMEI = :serial AND maSP = :maSP AND trangThai = 'Trong kho' FOR UPDATE");

            foreach ($cartItems as $item) {
                $maSP = $item['maSP'];
                $qty  = max(1, (int)$item['soLuong']);

                $stmtCheckProduct->execute([':maSP' => $maSP]);
                $product = $stmtCheckProduct->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new RuntimeException("Sản phẩm [{$maSP}] không tồn tại hoặc đã ngừng kinh doanh!");
                }

                if ((int)$product['soLuongTon'] < $qty) {
                    throw new RuntimeException("Sản phẩm [{$product['tenSP']}] chỉ còn tồn {$product['soLuongTon']} máy, không đủ số lượng bán ({$qty})!");
                }

                $donGia = isset($item['donGia']) ? (float)$item['donGia'] : (float)$product['giaBan'];
                $thanhTien = $donGia * $qty;
                $tongTienHang += $thanhTien;

                // Kiểm tra các serial được gán
                $serials = $item['serials'] ?? [];
                if (!is_array($serials)) {
                    $serials = [];
                }

                // Nếu số lượng serial không khớp với số lượng máy bán
                if (count($serials) !== $qty) {
                    throw new RuntimeException("Vui lòng chọn đúng {$qty} mã Serial/IMEI cho sản phẩm [{$product['tenSP']}]! (Đã chọn: " . count($serials) . ")");
                }

                foreach ($serials as $sn) {
                    $stmtCheckSerial->execute([':serial' => $sn, ':maSP' => $maSP]);
                    if (!$stmtCheckSerial->fetchColumn()) {
                        throw new RuntimeException("Mã Serial/IMEI [{$sn}] của sản phẩm [{$product['tenSP']}] không còn trong kho hoặc đã bán!");
                    }
                }

                $validatedItems[] = [
                    'maSP'            => $maSP,
                    'tenSP'           => $product['tenSP'],
                    'soLuong'         => $qty,
                    'donGia'          => $donGia,
                    'thanhTien'       => $thanhTien,
                    'thoiGianBaoHanh' => (int)($product['thoiGianBaoHanh'] ?? 12),
                    'serials'         => $serials
                ];
            }

            $maKM = null;
            if (!empty($orderData['maKM'])) {
                $checkKM = $this->db->prepare("SELECT maKM FROM khuyenmai WHERE maKM = :maKM LIMIT 1");
                $checkKM->execute([':maKM' => trim((string)$orderData['maKM'])]);
                if ($checkKM->fetchColumn()) {
                    $maKM = trim((string)$orderData['maKM']);
                }
            }

            $tienGiamGia = max(0, (float)($orderData['tienGiamGia'] ?? 0));
            $tongThanhToan = max(0, $tongTienHang - $tienGiamGia);
            $phuongThucTT = $orderData['phuongThucThanhToan'] ?? 'TIEN_MAT';

            // 3. Tạo Đơn Hàng (DonHang)
            $maDH = $this->generateNextOrderCode();

            $sqlOrder = "INSERT INTO donhang (
                            maDH, maKH, maKM, nguoiPhuTrach, ngayTao, kenhBan,
                            phuongThucThanhToan, phuongThucGiaoHang, phiVanChuyen,
                            tongTienHang, tienGiamGia, tongThanhToan, trangThaiDon
                        ) VALUES (
                            :maDH, :maKH, :maKM, :nguoiPhuTrach, NOW(), 'TAI_QUAY',
                            :phuongThucThanhToan, NULL, 0,
                            :tongTienHang, :tienGiamGia, :tongThanhToan, 'HOAN_THANH'
                        )";

            $stmtOrder = $this->db->prepare($sqlOrder);
            $stmtOrder->execute([
                ':maDH'               => $maDH,
                ':maKH'               => $maKH,
                ':maKM'               => $maKM,
                ':nguoiPhuTrach'      => $cashierId,
                ':phuongThucThanhToan'=> $phuongThucTT,
                ':tongTienHang'       => $tongTienHang,
                ':tienGiamGia'        => $tienGiamGia,
                ':tongThanhToan'      => $tongThanhToan
            ]);

            // 4. Lưu ChiTietDonHang, Cập nhật SerialSanPham, Trừ Tồn Kho SanPham & Tạo PhieuBaoHanh
            $sqlDetail = "INSERT INTO chitietdonhang (maDH, maSP, soLuong, donGia, thanhTien) 
                          VALUES (:maDH, :maSP, :soLuong, :donGia, :thanhTien)";
            $stmtDetail = $this->db->prepare($sqlDetail);

            $sqlUpdateSerial = "UPDATE serialsanpham 
                                SET idChiTietDonHang = :idCTDH, 
                                    trangThai = 'Đã bán', 
                                    ngayXuatKho = NOW(),
                                    ghiChu = CONCAT(IFNULL(ghiChu, ''), ' [Bán tại quầy đơn ', :maDH, ']')
                                WHERE maSerial_IMEI = :serial AND maSP = :maSP";
            $stmtUpdateSerial = $this->db->prepare($sqlUpdateSerial);

            $sqlUpdateStock = "UPDATE sanpham SET soLuongTon = soLuongTon - :qty WHERE maSP = :maSP";
            $stmtUpdateStock = $this->db->prepare($sqlUpdateStock);

            $sqlInsertPBH = "INSERT INTO phieubaohanh (maPhieuBH, maSerial_IMEI, maKH, ngayBatDau, ngayKetThuc, moTaChinhSach, trangThai, ghiChu) 
                             VALUES (:maPhieuBH, :serial, :maKH, NOW(), DATE_ADD(NOW(), INTERVAL :months MONTH), :moTa, 'CON_HAN', :ghiChu)";
            $stmtInsertPBH = $this->db->prepare($sqlInsertPBH);

            foreach ($validatedItems as $vItem) {
                // Thêm chi tiết đơn hàng
                $stmtDetail->execute([
                    ':maDH'      => $maDH,
                    ':maSP'      => $vItem['maSP'],
                    ':soLuong'   => $vItem['soLuong'],
                    ':donGia'    => $vItem['donGia'],
                    ':thanhTien' => $vItem['thanhTien']
                ]);

                $idCTDH = (int)$this->db->lastInsertId();

                // Gán SerialSanPham & Kích hoạt Phiếu bảo hành
                $months = max(1, $vItem['thoiGianBaoHanh']);
                foreach ($vItem['serials'] as $serial) {
                    $stmtUpdateSerial->execute([
                        ':idCTDH' => $idCTDH,
                        ':maDH'   => $maDH,
                        ':serial' => $serial,
                        ':maSP'   => $vItem['maSP']
                    ]);

                    $maPBH = 'PBH_' . strtoupper(substr(uniqid(), -8));
                    $stmtInsertPBH->execute([
                        ':maPhieuBH' => $maPBH,
                        ':serial'    => $serial,
                        ':maKH'      => $maKH,
                        ':months'    => $months,
                        ':moTa'      => "Bảo hành chính hãng {$months} tháng tại quầy theo Đơn hàng {$maDH}",
                        ':ghiChu'    => "Xuất bảo hành tự động tại quầy POS thu ngân"
                    ]);
                }

                // Trừ số lượng tồn kho
                $stmtUpdateStock->execute([
                    ':qty'  => $vItem['soLuong'],
                    ':maSP' => $vItem['maSP']
                ]);
            }

            // 5. Tự động tạo bản ghi Hóa Đơn (HoaDon) gắn với Đơn tại quầy
            $maHD = 'HD_' . date('Ymd') . '_' . strtoupper(substr(uniqid(), -4));
            $sqlHD = "INSERT INTO hoadon (
                        maHD, maDH, ngayXuat, tenKhachHang, soDienThoai, diaChiKhachHang, 
                        soTienThanhToan, trangThaiThanhToan, ghiChu
                      ) VALUES (
                        :maHD, :maDH, NOW(), :tenKhach, :sdt, :diaChi, 
                        :soTien, 'DA_THANH_TOAN', :ghiChu
                      )";

            $stmtHD = $this->db->prepare($sqlHD);
            $stmtHD->execute([
                ':maHD'     => $maHD,
                ':maDH'     => $maDH,
                ':tenKhach' => $customerName,
                ':sdt'      => $customerPhone,
                ':diaChi'   => $customerAddr,
                ':soTien'   => $tongThanhToan,
                ':ghiChu'   => 'Thanh toán trực tiếp tại quầy POS bởi thu ngân ' . $cashierId
            ]);

            // 6. Hoàn tất Transaction
            $this->db->commit();

            return [
                'success' => true,
                'maDH'    => $maDH,
                'maHD'    => $maHD,
                'message' => 'Tạo đơn hàng tại quầy và xuất hóa đơn thành công!'
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
