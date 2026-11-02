<?php
/**
 * File: models/SerialModel.php
 * Quản lý & tra cứu danh sách số Serial/IMEI từng chiếc laptop trong hệ thống
 */

require_once __DIR__ . '/../config/Database.php';

class SerialModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách số Serial/IMEI kèm tên laptop, mã phiếu nhập và trạng thái
     */
    public function getSerials(string $maSP = '', string $trangThai = '', string $keyword = '', int $page = 1, int $limit = 15): array {
        $offset = ($page - 1) * $limit;
        $params = [];

        $sql = "SELECT 
                    s.maSerial_IMEI, s.maSP, sp.tenSP, 
                    s.idChiTietNhap, ctn.maPhieu, pn.ngayNhap,
                    s.idChiTietDonHang, s.ngayXuatKho, 
                    s.trangThai, s.ghiChu
                FROM serialsanpham s
                LEFT JOIN sanpham sp ON s.maSP = sp.maSP
                LEFT JOIN chitietnhap ctn ON s.idChiTietNhap = ctn.id
                LEFT JOIN phieunhap pn ON ctn.maPhieu = pn.maPhieu
                WHERE 1=1";

        if (!empty($maSP)) {
            $sql .= " AND s.maSP = :maSP";
            $params[':maSP'] = $maSP;
        }

        if (!empty($trangThai)) {
            $sql .= " AND s.trangThai = :trangThai";
            $params[':trangThai'] = $trangThai;
        }

        if (!empty($keyword)) {
            $sql .= " AND (s.maSerial_IMEI LIKE :kw1 OR sp.tenSP LIKE :kw2 OR ctn.maPhieu LIKE :kw3)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
        }

        $sql .= " ORDER BY s.idChiTietNhap DESC, s.maSerial_IMEI ASC LIMIT :limit OFFSET :offset";

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
     * Đếm tổng số Serial thỏa mãn bộ lọc (phục vụ phân trang)
     */
    public function countSerials(string $maSP = '', string $trangThai = '', string $keyword = '',): int {
        $params = [];
        $sql = "SELECT COUNT(*) 
                FROM serialsanpham s
                LEFT JOIN sanpham sp ON s.maSP = sp.maSP
                LEFT JOIN chitietnhap ctn ON s.idChiTietNhap = ctn.id
                WHERE 1=1";

        if (!empty($maSP)) {
            $sql .= " AND s.maSP = :maSP";
            $params[':maSP'] = $maSP;
        }

        if (!empty($trangThai)) {
            $sql .= " AND s.trangThai = :trangThai";
            $params[':trangThai'] = $trangThai;
        }

        if (!empty($keyword)) {
            $sql .= " AND (s.maSerial_IMEI LIKE :kw1 OR sp.tenSP LIKE :kw2 OR ctn.maPhieu LIKE :kw3)";
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
     * Kiểm tra một mã Serial/IMEI đã tồn tại trong CSDL hay chưa
     */
    public function checkSerialExists(string $serial): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM serialsanpham WHERE maSerial_IMEI = :serial");
        $stmt->execute([':serial' => $serial]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Lấy danh sách Serial thuộc một dòng chi tiết nhập (idChiTietNhap)
     */
    public function getSerialsByImportDetailId(int $idChiTietNhap): array {
        $stmt = $this->db->prepare("SELECT * FROM serialsanpham WHERE idChiTietNhap = :id ORDER BY maSerial_IMEI ASC");
        $stmt->execute([':id' => $idChiTietNhap]);
        return $stmt->fetchAll();
    }

    /**
     * Thống kê tổng số lượng Serial theo từng trạng thái
     */
    public function getSerialStatusCounts(): array {
        $sql = "SELECT trangThai, COUNT(*) as sl FROM serialsanpham GROUP BY trangThai";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $counts = [
            'total'         => 0,
            'Trong kho'     => 0,
            'Đã bán'        => 0,
            'Đang bảo hành' => 0,
            'Lỗi'           => 0
        ];
        foreach ($rows as $r) {
            $st = $r['trangThai'];
            $sl = (int)$r['sl'];
            $counts['total'] += $sl;
            if (isset($counts[$st])) {
                $counts[$st] = $sl;
            }
        }
        return $counts;
    }
}
