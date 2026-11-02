<?php
/**
 * File: models/ImportReceiptModel.php
 * Quản lý phiếu nhập kho và chi tiết các đợt nhập hàng
 */

require_once __DIR__ . '/../config/Database.php';

class ImportReceiptModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách phiếu nhập kho (có tìm kiếm, lọc và phân trang)
     */
    public function getReceipts(string $keyword = '', int $page = 1, int $limit = 10): array {
        $offset = ($page - 1) * $limit;
        $params = [];

        $sql = "SELECT 
                    pn.maPhieu, pn.ngayNhap, pn.maNCC, ncc.tenCongTy, 
                    pn.nguoiNhap, acc.hoTen AS tenNguoiNhap, 
                    pn.soHoaDonNCC, pn.tongTienTraTruocNCC, pn.tongTienNhap, 
                    pn.trangThaiThanhToan, pn.trangThaiNhapKho, pn.ghiChu
                FROM phieunhap pn
                LEFT JOIN nhacungcap ncc ON pn.maNCC = ncc.maNCC
                LEFT JOIN accounts acc ON pn.nguoiNhap = acc.maTk
                WHERE 1=1";

        if (!empty($keyword)) {
            $sql .= " AND (pn.maPhieu LIKE :kw1 OR ncc.tenCongTy LIKE :kw2 OR pn.soHoaDonNCC LIKE :kw3)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
        }

        $sql .= " ORDER BY pn.ngayNhap DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số lượng phiếu nhập phục vụ phân trang
     */
    public function countReceipts(string $keyword = ''): int {
        $params = [];
        $sql = "SELECT COUNT(*) 
                FROM phieunhap pn
                LEFT JOIN nhacungcap ncc ON pn.maNCC = ncc.maNCC
                WHERE 1=1";

        if (!empty($keyword)) {
            $sql .= " AND (pn.maPhieu LIKE :kw1 OR ncc.tenCongTy LIKE :kw2 OR pn.soHoaDonNCC LIKE :kw3)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy thông tin chung của 1 phiếu nhập theo mã phiếu
     */
    public function getReceiptById(string $maPhieu): array|false {
        $sql = "SELECT 
                    pn.*, 
                    ncc.tenCongTy, ncc.soDienThoai AS sdtNCC, ncc.email AS emailNCC, ncc.diaChiTruSo,
                    acc.hoTen AS tenNguoiNhap, acc.email AS emailNguoiNhap
                FROM phieunhap pn
                LEFT JOIN nhacungcap ncc ON pn.maNCC = ncc.maNCC
                LEFT JOIN accounts acc ON pn.nguoiNhap = acc.maTk
                WHERE pn.maPhieu = :maPhieu 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maPhieu' => $maPhieu]);
        return $stmt->fetch();
    }

    /**
     * Lấy danh sách chi tiết các dòng máy nhập của 1 phiếu, kèm danh sách số Serial tương ứng
     */
    public function getReceiptDetails(string $maPhieu): array {
        $sql = "SELECT 
                    ctn.id, ctn.maPhieu, ctn.maSP, sp.tenSP, 
                    ctn.soLuongNhap, ctn.giaVonNhap, ctn.thanhTienNhap, ctn.ghiChu
                FROM chitietnhap ctn
                LEFT JOIN sanpham sp ON ctn.maSP = sp.maSP
                WHERE ctn.maPhieu = :maPhieu
                ORDER BY ctn.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maPhieu' => $maPhieu]);
        $details = $stmt->fetchAll();

        // Lấy danh sách serial kèm theo cho từng dòng chi tiết nhập
        $serialStmt = $this->db->prepare("SELECT maSerial_IMEI, trangThai, ghiChu FROM serialsanpham WHERE idChiTietNhap = :id ORDER BY maSerial_IMEI ASC");

        foreach ($details as &$item) {
            $serialStmt->execute([':id' => $item['id']]);
            $item['serials'] = $serialStmt->fetchAll();
        }

        return $details;
    }

    /**
     * Kiểm tra mã phiếu nhập đã tồn tại chưa
     */
    public function isReceiptCodeExists(string $maPhieu): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM phieunhap WHERE maPhieu = :maPhieu");
        $stmt->execute([':maPhieu' => $maPhieu]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Tạo phiếu nhập kho hoàn chỉnh bằng PDO Transaction
     * 1. Thêm PhieuNhap
     * 2. Thêm từng dòng ChiTietNhap
     * 3. Thêm danh sách Serial/IMEI vào SerialSanPham
     * 4. Tự động cộng dồn số lượng tồn vào bảng SanPham
     *
     * @param array $dataReceipt Thông tin phiếu nhập chính
     * @param array $dataDetails Mảng các dòng sản phẩm nhập
     * @param array $serialList  Mảng 2 chiều các mã serial theo từng index dòng
     * @return bool
     * @throws Exception
     */
    public function createReceipt(array $dataReceipt, array $dataDetails, array $serialList): bool {
        $this->db->beginTransaction();

        try {
            // 1. Thêm bản ghi vào phieunhap
            $sqlReceipt = "INSERT INTO phieunhap 
                (maPhieu, ngayNhap, maNCC, nguoiNhap, soHoaDonNCC, tongTienTraTruocNCC, tongTienNhap, trangThaiThanhToan, trangThaiNhapKho, ghiChu) 
                VALUES 
                (:maPhieu, :ngayNhap, :maNCC, :nguoiNhap, :soHoaDonNCC, :tongTienTraTruocNCC, :tongTienNhap, :trangThaiThanhToan, :trangThaiNhapKho, :ghiChu)";

            $stmtReceipt = $this->db->prepare($sqlReceipt);
            $stmtReceipt->execute([
                ':maPhieu'             => $dataReceipt['maPhieu'],
                ':ngayNhap'            => $dataReceipt['ngayNhap'] ?? date('Y-m-d H:i:s'),
                ':maNCC'               => $dataReceipt['maNCC'],
                ':nguoiNhap'           => $dataReceipt['nguoiNhap'],
                ':soHoaDonNCC'         => $dataReceipt['soHoaDonNCC'] ?? '',
                ':tongTienTraTruocNCC' => $dataReceipt['tongTienTraTruocNCC'] ?? 0,
                ':tongTienNhap'        => $dataReceipt['tongTienNhap'] ?? 0,
                ':trangThaiThanhToan'  => $dataReceipt['trangThaiThanhToan'] ?? 'DA_THANH_TOAN',
                ':trangThaiNhapKho'    => $dataReceipt['trangThaiNhapKho'] ?? 'DA_NHAP_KHO',
                ':ghiChu'              => $dataReceipt['ghiChu'] ?? ''
            ]);

            // 2. Chuẩn bị các câu lệnh cho chi tiết và serial
            $sqlDetail = "INSERT INTO chitietnhap 
                (maPhieu, maSP, soLuongNhap, giaVonNhap, thanhTienNhap, ghiChu) 
                VALUES 
                (:maPhieu, :maSP, :soLuongNhap, :giaVonNhap, :thanhTienNhap, :ghiChu)";
            $stmtDetail = $this->db->prepare($sqlDetail);

            $sqlCheckSerial = "SELECT COUNT(*) FROM serialsanpham WHERE maSerial_IMEI = :serial";
            $stmtCheckSerial = $this->db->prepare($sqlCheckSerial);

            $sqlInsertSerial = "INSERT INTO serialsanpham 
                (maSerial_IMEI, maSP, idChiTietNhap, trangThai, ghiChu) 
                VALUES 
                (:maSerial_IMEI, :maSP, :idChiTietNhap, 'Trong kho', :ghiChu)";
            $stmtInsertSerial = $this->db->prepare($sqlInsertSerial);

            $sqlUpdateStock = "UPDATE sanpham SET soLuongTon = soLuongTon + :soLuongNhap WHERE maSP = :maSP";
            $stmtUpdateStock = $this->db->prepare($sqlUpdateStock);

            // Duyệt từng dòng sản phẩm nhập
            foreach ($dataDetails as $index => $item) {
                $maSP        = $item['maSP'];
                $soLuongNhap = (int)$item['soLuongNhap'];
                $giaVonNhap  = (float)$item['giaVonNhap'];
                $thanhTien   = $soLuongNhap * $giaVonNhap;

                // 2.1 Thêm chi tiết nhập
                $stmtDetail->execute([
                    ':maPhieu'       => $dataReceipt['maPhieu'],
                    ':maSP'          => $maSP,
                    ':soLuongNhap'   => $soLuongNhap,
                    ':giaVonNhap'    => $giaVonNhap,
                    ':thanhTienNhap' => $thanhTien,
                    ':ghiChu'        => $item['ghiChu'] ?? ''
                ]);
                $idChiTietNhap = (int)$this->db->lastInsertId();

                // 2.2 Thêm danh sách Serial tương ứng
                $serials = $serialList[$index] ?? [];
                if (count($serials) !== $soLuongNhap) {
                    throw new Exception("Số lượng mã Serial/IMEI ({$maSP}) là " . count($serials) . ", không khớp với số lượng nhập ({$soLuongNhap})!");
                }

                foreach ($serials as $serial) {
                    $serial = trim($serial);
                    if (empty($serial)) continue;

                    // Kiểm tra xem Serial đã tồn tại trong CSDL chưa
                    $stmtCheckSerial->execute([':serial' => $serial]);
                    if ((int)$stmtCheckSerial->fetchColumn() > 0) {
                        throw new Exception("Mã Serial/IMEI [{$serial}] đã tồn tại trong hệ thống. Không thể nhập trùng!");
                    }

                    // Lưu Serial vào bảng serialsanpham
                    $stmtInsertSerial->execute([
                        ':maSerial_IMEI'  => $serial,
                        ':maSP'           => $maSP,
                        ':idChiTietNhap'  => $idChiTietNhap,
                        ':ghiChu'         => 'Nhập từ phiếu ' . $dataReceipt['maPhieu']
                    ]);
                }

                // 2.3 Tự động cộng số lượng tồn kho của Laptop
                $stmtUpdateStock->execute([
                    ':soLuongNhap' => $soLuongNhap,
                    ':maSP'        => $maSP
                ]);
            }

            // Hoàn tất Transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
