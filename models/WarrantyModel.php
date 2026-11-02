<?php
/**
 * File: models/WarrantyModel.php
 * Quản lý phiếu bảo hành và tra cứu bảo hành theo Serial/IMEI & SĐT khách hàng
 */

require_once __DIR__ . '/../config/Database.php';

class WarrantyModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Tra cứu thông tin bảo hành công khai bằng số Serial/IMEI, SĐT khách hàng hoặc Mã phiếu
     * @param string $keyword
     * @return array
     */
    public function searchWarrantyByKeyword(string $keyword): array {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        $sql = "SELECT 
                    pbh.maPhieuBH,
                    pbh.maSerial_IMEI,
                    pbh.maKH,
                    pbh.ngayBatDau,
                    pbh.ngayKetThuc,
                    pbh.moTaChinhSach,
                    pbh.trangThai AS trangThaiPhieu,
                    pbh.ghiChu AS ghiChuBaoHanh,
                    sn.trangThai AS trangThaiSerial,
                    sn.ngayXuatKho,
                    sp.maSP,
                    sp.tenSP,
                    sp.hinhAnh,
                    sp.thoiGianBaoHanh,
                    th.tenTH,
                    dm.tenDM,
                    kh.hoVaTen,
                    kh.soDienThoai,
                    kh.email,
                    kh.diaChi,
                    DATEDIFF(pbh.ngayKetThuc, NOW()) AS soNgayConLai
                FROM phieubaohanh pbh
                JOIN serialsanpham sn ON pbh.maSerial_IMEI = sn.maSerial_IMEI
                JOIN sanpham sp ON sn.maSP = sp.maSP
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN khachhang kh ON pbh.maKH = kh.maKH
                WHERE pbh.maSerial_IMEI LIKE :kw1
                   OR kh.soDienThoai LIKE :kw2
                   OR pbh.maPhieuBH LIKE :kw3
                ORDER BY pbh.ngayKetThuc DESC";

        $kw = '%' . $keyword . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':kw1' => $kw,
            ':kw2' => $kw,
            ':kw3' => $kw
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách toàn bộ phiếu bảo hành phía quản trị có lọc theo trạng thái và tìm kiếm
     */
    public function getWarrantyTickets(string $status = '', string $keyword = ''): array {
        $params = [];
        $sql = "SELECT 
                    pbh.maPhieuBH,
                    pbh.maSerial_IMEI,
                    pbh.maKH,
                    pbh.ngayBatDau,
                    pbh.ngayKetThuc,
                    pbh.moTaChinhSach,
                    pbh.trangThai AS trangThaiPhieu,
                    pbh.ghiChu AS ghiChuBaoHanh,
                    sn.trangThai AS trangThaiSerial,
                    sp.maSP,
                    sp.tenSP,
                    sp.hinhAnh,
                    th.tenTH,
                    kh.hoVaTen,
                    kh.soDienThoai,
                    DATEDIFF(pbh.ngayKetThuc, NOW()) AS soNgayConLai
                FROM phieubaohanh pbh
                JOIN serialsanpham sn ON pbh.maSerial_IMEI = sn.maSerial_IMEI
                JOIN sanpham sp ON sn.maSP = sp.maSP
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                LEFT JOIN khachhang kh ON pbh.maKH = kh.maKH
                WHERE 1 = 1";

        if ($status !== '') {
            $sql .= " AND pbh.trangThai = :status";
            $params[':status'] = $status;
        }

        if ($keyword !== '') {
            $sql .= " AND (pbh.maPhieuBH LIKE :kw1 OR pbh.maSerial_IMEI LIKE :kw2 OR kh.hoVaTen LIKE :kw3 OR kh.soDienThoai LIKE :kw4 OR sp.tenSP LIKE :kw5)";
            $kw = '%' . $keyword . '%';
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
            $params[':kw5'] = $kw;
        }

        $sql .= " ORDER BY pbh.ngayKetThuc ASC, pbh.maPhieuBH DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin chi tiết một phiếu bảo hành theo mã
     */
    public function getWarrantyById(string $maPhieuBH): array|false {
        $sql = "SELECT 
                    pbh.maPhieuBH,
                    pbh.maSerial_IMEI,
                    pbh.maKH,
                    pbh.ngayBatDau,
                    pbh.ngayKetThuc,
                    pbh.moTaChinhSach,
                    pbh.trangThai AS trangThaiPhieu,
                    pbh.ghiChu AS ghiChuBaoHanh,
                    sn.trangThai AS trangThaiSerial,
                    sn.ngayXuatKho,
                    sp.maSP,
                    sp.tenSP,
                    sp.hinhAnh,
                    sp.thoiGianBaoHanh,
                    th.tenTH,
                    dm.tenDM,
                    kh.hoVaTen,
                    kh.soDienThoai,
                    kh.email,
                    kh.diaChi,
                    DATEDIFF(pbh.ngayKetThuc, NOW()) AS soNgayConLai
                FROM phieubaohanh pbh
                JOIN serialsanpham sn ON pbh.maSerial_IMEI = sn.maSerial_IMEI
                JOIN sanpham sp ON sn.maSP = sp.maSP
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN khachhang kh ON pbh.maKH = kh.maKH
                WHERE pbh.maPhieuBH = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $maPhieuBH]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật trạng thái phiếu bảo hành và đồng bộ trạng thái Serial máy
     */
    public function updateWarrantyStatus(string $maPhieuBH, string $trangThaiPhieu, string $trangThaiSerial, string $ghiChu = ''): bool {
        try {
            $this->db->beginTransaction();

            // 1. Cập nhật phiếu bảo hành
            $stmt = $this->db->prepare("UPDATE phieubaohanh SET trangThai = :trangThai, ghiChu = :ghiChu WHERE maPhieuBH = :maPhieuBH");
            $stmt->execute([
                ':trangThai' => $trangThaiPhieu,
                ':ghiChu'    => $ghiChu,
                ':maPhieuBH' => $maPhieuBH
            ]);

            // 2. Lấy mã Serial tương ứng
            $stmt = $this->db->prepare("SELECT maSerial_IMEI FROM phieubaohanh WHERE maPhieuBH = :maPhieuBH");
            $stmt->execute([':maPhieuBH' => $maPhieuBH]);
            $serial = $stmt->fetchColumn();

            // 3. Cập nhật trạng thái Serial máy nếu tìm thấy
            if ($serial && $trangThaiSerial !== '') {
                $stmt = $this->db->prepare("UPDATE serialsanpham SET trangThai = :trangThaiSerial WHERE maSerial_IMEI = :serial");
                $stmt->execute([
                    ':trangThaiSerial' => $trangThaiSerial,
                    ':serial'          => $serial
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("updateWarrantyStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tạo phiếu bảo hành mới
     */
    public function createWarrantyTicket(array $data): bool {
        $sql = "INSERT INTO phieubaohanh (maPhieuBH, maSerial_IMEI, maKH, ngayBatDau, ngayKetThuc, moTaChinhSach, trangThai, ghiChu)
                VALUES (:maPhieuBH, :maSerial_IMEI, :maKH, :ngayBatDau, :ngayKetThuc, :moTaChinhSach, :trangThai, :ghiChu)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':maPhieuBH'     => $data['maPhieuBH'],
            ':maSerial_IMEI' => $data['maSerial_IMEI'],
            ':maKH'          => $data['maKH'],
            ':ngayBatDau'    => $data['ngayBatDau'],
            ':ngayKetThuc'   => $data['ngayKetThuc'],
            ':moTaChinhSach' => $data['moTaChinhSach'] ?? 'Bảo hành chính hãng',
            ':trangThai'     => $data['trangThai'] ?? 'Đang hiệu lực',
            ':ghiChu'        => $data['ghiChu'] ?? ''
        ]);
    }
}
