-- ========================================================
-- EmShu Store Database Backup (Borg Backup Server Engine)
-- Database: ql_cuahang_laptop
-- Generated at: 2026-10-29 14:56:29
-- Note: khoi_tao_he_thong
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET NAMES utf8mb4;


-- --------------------------------------------------------
-- Table structure and data for table `accounts`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `maTk` varchar(50) NOT NULL,
  `hoTen` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tenTaiKhoan` varchar(50) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `ngayTao` datetime NOT NULL DEFAULT current_timestamp(),
  `role` varchar(30) NOT NULL DEFAULT 'user' COMMENT 'user, admin, nv_kho, nv_ban_hang, ke_toan, thu_ngan, ky_thuat_vien',
  `trangThai` varchar(20) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`maTk`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `tenTaiKhoan` (`tenTaiKhoan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `accounts` (`maTk`, `hoTen`, `email`, `tenTaiKhoan`, `matKhau`, `ngayTao`, `role`, `trangThai`) VALUES
('TK_5413F523', 'Ang Nhang Nhang', 'shushuu186@gmail.com', 'shushuu186', '$2y$10$i.OC6jr8KKi1ReDcb1Z6p.NLrnmcoa0.EmJvWrpF56PVO2/UyNRXK', '2026-10-25 13:25:37', 'user', 'active'),
('TK001', 'Nguyễn Quản Trị', 'admin@laptopstore.vn', 'admin', '$2y$10$3fzgeEsvdmJJYuCOs2fQpOYJeWiyXeERHSIJtZuCL0nRycFuW76xa', '2026-01-01 08:00:00', 'admin', 'active'),
('TK002', 'Trần Văn Kho', 'kho@laptopstore.vn', 'nvkho01', '$2y$10$Lol/bQBTDKZvZSiZr1M2TupAXVFHg3vacxGOzRMqljH9/so7444Ly', '2026-01-05 08:30:00', 'nv_kho', 'active'),
('TK003', 'Lê Thị Bán Hàng', 'sales@laptopstore.vn', 'nvsales01', 'e10adc3949ba59abbe56e057f20f883e', '2026-01-05 09:00:00', 'nv_ban_hang', 'active'),
('TK004', 'Phạm Thu Ngân', 'cashier@laptopstore.vn', 'thungan01', '$2y$10$fv68QVleeng9vdhMlGcdfeMluJ5una/JCeNJsmL7B96Gol4/CXJ56', '2026-01-06 08:00:00', 'thu_ngan', 'active'),
('TK005', 'Hoàng Kế Toán', 'accounting@laptopstore.vn', 'ketoan01', 'e10adc3949ba59abbe56e057f20f883e', '2026-01-06 08:15:00', 'ke_toan', 'active'),
('TK006', 'Vũ Kỹ Thuật', 'tech@laptopstore.vn', 'kythuat01', 'e10adc3949ba59abbe56e057f20f883e', '2026-01-07 08:00:00', 'ky_thuat_vien', 'active'),
('TK007', 'Đỗ Minh Tuấn', 'minhtuan@gmail.com', 'minhtuan99', 'e10adc3949ba59abbe56e057f20f883e', '2026-02-10 14:20:00', 'user', 'active'),
('TK008', 'Nguyễn Lan Anh', 'lananh.ng@gmail.com', 'lananh_ng', '$2y$10$NTx1QWSLybTf6Xy.2kf7x.XBb3caHSOFaTH7pHu6R2psPQdeX/iwi', '2026-03-12 19:45:00', 'user', 'active'),
('TK009', 'Bùi Hoàng Nam', 'hoangnam.dev@gmail.com', 'hoangnam_it', 'e10adc3949ba59abbe56e057f20f883e', '2026-04-01 10:10:00', 'user', 'active');


-- --------------------------------------------------------
-- Table structure and data for table `chitietdonhang`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `chitietdonhang`;
CREATE TABLE `chitietdonhang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maDH` varchar(50) NOT NULL,
  `maSP` varchar(50) NOT NULL,
  `soLuong` int(11) NOT NULL,
  `donGia` double NOT NULL,
  `thanhTien` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chitietdh_dh_sp` (`maDH`,`maSP`),
  KEY `fk_chitietdh_sanpham` (`maSP`),
  CONSTRAINT `fk_chitietdh_donhang` FOREIGN KEY (`maDH`) REFERENCES `donhang` (`maDH`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_chitietdh_sanpham` FOREIGN KEY (`maSP`) REFERENCES `sanpham` (`maSP`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chitietdonhang` (`id`, `maDH`, `maSP`, `soLuong`, `donGia`, `thanhTien`) VALUES
('1', 'DH001', 'SP001', '1', '21990000', '21990000'),
('2', 'DH002', 'SP003', '1', '15490000', '15490000'),
('3', 'DH003', 'SP002', '1', '20490000', '20490000'),
('4', 'DH004', 'SP004', '1', '27990000', '27990000'),
('5', 'DH005', 'SP004', '1', '27990000', '27990000'),
('6', 'DH005', 'SP001', '1', '21990000', '21990000'),
('11', 'DH006', 'SP002', '1', '20490000', '20490000'),
('12', 'DH007', 'SP002', '1', '20490000', '20490000'),
('13', 'DH008', 'SP003', '1', '15490000', '15490000');


-- --------------------------------------------------------
-- Table structure and data for table `chitietnhap`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `chitietnhap`;
CREATE TABLE `chitietnhap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maPhieu` varchar(50) NOT NULL,
  `maSP` varchar(50) NOT NULL,
  `soLuongNhap` int(11) NOT NULL,
  `giaVonNhap` double NOT NULL,
  `thanhTienNhap` double NOT NULL,
  `ghiChu` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chitietnhap_phieu_sp` (`maPhieu`,`maSP`),
  KEY `fk_chitietnhap_sanpham` (`maSP`),
  CONSTRAINT `fk_chitietnhap_phieunhap` FOREIGN KEY (`maPhieu`) REFERENCES `phieunhap` (`maPhieu`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_chitietnhap_sanpham` FOREIGN KEY (`maSP`) REFERENCES `sanpham` (`maSP`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chitietnhap` (`id`, `maPhieu`, `maSP`, `soLuongNhap`, `giaVonNhap`, `thanhTienNhap`, `ghiChu`) VALUES
('1', 'PN2026_001', 'SP001', '2', '18000000', '36000000', 'Hàng nguyên seal chính hãng ASUS VN'),
('2', 'PN2026_001', 'SP002', '2', '17000000', '34000000', 'Hàng nguyên seal Lenovo VN'),
('3', 'PN2026_002', 'SP003', '2', '12500000', '25000000', 'Đủ phụ kiện sạc cáp zin'),
('4', 'PN2026_002', 'SP004', '3', '22333333', '67000000', 'Mã SA/A phân phối chính hãng'),
('5', 'PN-20261026-496D', 'SP002', '3', '15000000', '45000000', ''),
('6', 'PN-20261026-496D', 'SP001', '4', '20000000', '80000000', '');


-- --------------------------------------------------------
-- Table structure and data for table `danhgia`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `danhgia`;
CREATE TABLE `danhgia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maSP` varchar(50) NOT NULL,
  `hoTen` varchar(100) NOT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `soSao` int(11) NOT NULL DEFAULT 5,
  `noiDung` text NOT NULL,
  `ngayDanhGia` datetime NOT NULL DEFAULT current_timestamp(),
  `trangThai` varchar(20) NOT NULL DEFAULT 'approved',
  PRIMARY KEY (`id`),
  KEY `idx_maSP` (`maSP`),
  KEY `idx_ngay` (`ngayDanhGia`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `danhgia` (`id`, `maSP`, `hoTen`, `soDienThoai`, `soSao`, `noiDung`, `ngayDanhGia`, `trangThai`) VALUES
('1', 'SP001', 'Nguyễn Quốc Cường', '0981234567', '5', 'Máy ASUS TUF F15 tản nhiệt rất mát, chiến GTA V và Valorant mượt mà 144Hz. Bàn phím gõ nảy, đóng gói rất cẩn thận!', '2026-10-25 17:06:26', 'approved'),
('2', 'SP001', 'Trần Minh Đức', '0972345678', '5', 'Giao hàng hỏa tốc trong 2 tiếng, nhân viên tư vấn nhiệt tình, máy đẹp keng nguyên seal.', '2026-10-25 17:06:26', 'approved'),
('3', 'SP002', 'Lê Thanh Tùng', '0913456789', '5', 'Lenovo LOQ hoàn thiện chắc chắn, thiết kế hầm hố vừa phải, mang đi học đi làm đều rất ổn.', '2026-10-25 17:06:26', 'approved'),
('4', 'SP003', 'Phạm Thu Trang', '0934567890', '5', 'Dell Inspiron mỏng nhẹ, mở Excel và Canva mượt, pin dùng liên tục được hơn 5 tiếng rất tiện.', '2026-10-25 17:06:26', 'approved'),
('5', 'SP004', 'Đoàn Nhật Minh', '0945678901', '5', 'MacBook Air M3 cực kỳ đẳng cấp, màn hình sắc nét và loa nghe nhạc hay vượt trội. Rất hài lòng!', '2026-10-25 17:06:26', 'approved'),
('6', 'SP005', 'Hoàng Anh Tuấn', '0966778899', '5', 'MSI Creator M16 render Premiere và 3Ds Max rất nhanh, màn chuẩn màu 100% sRGB cho đồ họa.', '2026-10-25 17:06:26', 'approved');


-- --------------------------------------------------------
-- Table structure and data for table `danhmuc`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `danhmuc`;
CREATE TABLE `danhmuc` (
  `maDM` int(11) NOT NULL AUTO_INCREMENT,
  `tenDM` varchar(100) NOT NULL,
  `maKM` varchar(50) DEFAULT NULL COMMENT 'Null nếu danh mục đang không có khuyến mãi',
  `moTa` text DEFAULT NULL,
  `trangThai` int(11) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Ẩn',
  PRIMARY KEY (`maDM`),
  KEY `fk_danhmuc_khuyenmai` (`maKM`),
  CONSTRAINT `fk_danhmuc_khuyenmai` FOREIGN KEY (`maKM`) REFERENCES `khuyenmai` (`maKM`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `danhmuc` (`maDM`, `tenDM`, `maKM`, `moTa`, `trangThai`) VALUES
('1', 'Laptop Gaming', 'KM_GAMING1M', 'Laptop cấu hình cao, card đồ họa rời chuyên dụng cho game thủ', '1'),
('2', 'Laptop Văn phòng - Học tập', 'KM_BACK2SCHOOL', 'Thiết kế mỏng nhẹ, pin lâu, tối ưu cho sinh viên và nhân viên văn phòng', '1'),
('3', 'MacBook', NULL, 'Các dòng máy tính xách tay chính hãng Apple Việt Nam', '1'),
('4', 'Laptop Đồ họa - Kỹ thuật', NULL, 'Máy trạm di động màn hình chuẩn màu, CPU/GPU hiệu năng cao', '1');


-- --------------------------------------------------------
-- Table structure and data for table `donhang`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `donhang`;
CREATE TABLE `donhang` (
  `maDH` varchar(50) NOT NULL,
  `maKH` varchar(50) NOT NULL,
  `maKM` varchar(50) DEFAULT NULL,
  `nguoiPhuTrach` varchar(50) DEFAULT NULL COMMENT 'Nhân viên thu ngân tại quầy hoặc nhân viên duyệt đơn Online',
  `ngayTao` datetime NOT NULL DEFAULT current_timestamp(),
  `kenhBan` varchar(20) NOT NULL COMMENT 'TAI_QUAY / ONLINE',
  `phuongThucThanhToan` varchar(50) NOT NULL COMMENT 'TIEN_MAT / CHUYEN_KHOAN / COD / VNPay...',
  `phuongThucGiaoHang` varchar(100) DEFAULT NULL COMMENT 'Null nếu mua trực tiếp tại quầy',
  `phiVanChuyen` double NOT NULL DEFAULT 0 COMMENT 'Bằng 0 nếu mua tại quầy',
  `tongTienHang` double NOT NULL DEFAULT 0,
  `tienGiamGia` double NOT NULL DEFAULT 0,
  `tongThanhToan` double NOT NULL DEFAULT 0,
  `trangThaiDon` varchar(30) NOT NULL COMMENT 'Tại quầy: HOAN_THANH; Online: CHO_DUYET -> DANG_GIAO -> HOAN_THANH / DA_HUY',
  PRIMARY KEY (`maDH`),
  KEY `fk_donhang_khachhang` (`maKH`),
  KEY `fk_donhang_khuyenmai` (`maKM`),
  KEY `fk_donhang_accounts` (`nguoiPhuTrach`),
  CONSTRAINT `fk_donhang_accounts` FOREIGN KEY (`nguoiPhuTrach`) REFERENCES `accounts` (`maTk`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_donhang_khachhang` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`) ON UPDATE CASCADE,
  CONSTRAINT `fk_donhang_khuyenmai` FOREIGN KEY (`maKM`) REFERENCES `khuyenmai` (`maKM`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `donhang` (`maDH`, `maKH`, `maKM`, `nguoiPhuTrach`, `ngayTao`, `kenhBan`, `phuongThucThanhToan`, `phuongThucGiaoHang`, `phiVanChuyen`, `tongTienHang`, `tienGiamGia`, `tongThanhToan`, `trangThaiDon`) VALUES
('DH001', 'KH004', 'KM_GAMING1M', 'TK004', '2026-10-01 10:15:00', 'TAI_QUAY', 'CHUYEN_KHOAN', NULL, '0', '21990000', '1000000', '20990000', 'HOAN_THANH'),
('DH002', 'KH001', 'KM_BACK2SCHOOL', 'TK003', '2026-10-05 15:30:00', 'ONLINE', 'VNPay', 'Giao hàng nhanh (GHN)', '50000', '15490000', '774500', '14765500', 'HOAN_THANH'),
('DH003', 'KH002', 'KM_GAMING1M', 'TK003', '2026-10-20 09:20:00', 'ONLINE', 'COD', 'Viettel Post Hỏa tốc', '45000', '20490000', '1000000', '19535000', 'DANG_GIAO'),
('DH004', 'KH003', NULL, 'TK001', '2026-10-24 20:00:00', 'ONLINE', 'CHUYEN_KHOAN', 'Giao nội thành Hà Nội', '30000', '27990000', '0', '28020000', 'DA_HUY'),
('DH005', 'KH002', NULL, 'TK001', '2026-10-25 18:12:14', 'ONLINE', 'COD', 'Giao hàng tận nơi', '50000', '49980000', '0', '50030000', 'DA_HUY'),
('DH006', 'KH8EC82C', NULL, 'TK004', '2026-10-26 12:54:26', 'ONLINE', 'COD', 'Giao hàng tận nơi', '50000', '20490000', '0', '20540000', 'HOAN_THANH'),
('DH007', 'KH8EC82C', NULL, 'TK004', '2026-10-26 14:27:50', 'ONLINE', 'COD', 'Giao hàng tận nơi', '50000', '20490000', '0', '20540000', 'DA_HUY'),
('DH008', 'KH8EC82C', NULL, 'TK001', '2026-10-26 14:29:37', 'ONLINE', 'COD', 'Giao hàng tận nơi', '50000', '15490000', '0', '15540000', 'HOAN_THANH');


-- --------------------------------------------------------
-- Table structure and data for table `giohang`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `giohang`;
CREATE TABLE `giohang` (
  `maGioHang` int(11) NOT NULL AUTO_INCREMENT,
  `maKH` varchar(50) NOT NULL,
  `maSP` varchar(50) NOT NULL,
  `soLuong` int(11) NOT NULL DEFAULT 1,
  `ngayCapNhat` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`maGioHang`),
  UNIQUE KEY `uq_giohang_kh_sp` (`maKH`,`maSP`),
  KEY `fk_giohang_sanpham` (`maSP`),
  CONSTRAINT `fk_giohang_khachhang` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_giohang_sanpham` FOREIGN KEY (`maSP`) REFERENCES `sanpham` (`maSP`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `giohang` (`maGioHang`, `maKH`, `maSP`, `soLuong`, `ngayCapNhat`) VALUES
('1', 'KH001', 'SP004', '1', '2026-10-24 21:00:00'),
('3', 'KH003', 'SP002', '2', '2026-10-24 22:00:00'),
('7', 'KHF90A6C', 'SP003', '1', '2026-10-25 22:00:10');


-- --------------------------------------------------------
-- Table structure and data for table `hoadon`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `hoadon`;
CREATE TABLE `hoadon` (
  `maHD` varchar(50) NOT NULL,
  `maDH` varchar(50) NOT NULL,
  `ngayXuat` datetime NOT NULL DEFAULT current_timestamp(),
  `tenKhachHang` varchar(100) NOT NULL,
  `soDienThoai` varchar(20) NOT NULL,
  `diaChiKhachHang` varchar(255) DEFAULT NULL,
  `maSoThueKH` varchar(50) DEFAULT NULL,
  `soTienThanhToan` double NOT NULL,
  `trangThaiThanhToan` varchar(50) NOT NULL,
  `ghiChu` text DEFAULT NULL,
  PRIMARY KEY (`maHD`),
  UNIQUE KEY `maDH` (`maDH`),
  CONSTRAINT `fk_hoadon_donhang` FOREIGN KEY (`maDH`) REFERENCES `donhang` (`maDH`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hoadon` (`maHD`, `maDH`, `ngayXuat`, `tenKhachHang`, `soDienThoai`, `diaChiKhachHang`, `maSoThueKH`, `soTienThanhToan`, `trangThaiThanhToan`, `ghiChu`) VALUES
('HD_20261026_65C1', 'DH008', '2026-10-26 14:30:04', 'Ang Nhang Nhang', '0987654321', '120 yen lang', NULL, '15540000', 'DA_THANH_TOAN', 'Xuất hóa đơn tự động khi hoàn thành đơn hàng DH008'),
('HD_20261026_A86B', 'DH006', '2026-10-26 12:55:02', 'Ang Nhang Nhang', '0987654321', '120 an lieng', NULL, '20540000', 'DA_THANH_TOAN', 'Xuất hóa đơn tự động khi hoàn thành đơn hàng DH006'),
('HD_VAT_001', 'DH001', '2026-10-01 10:25:00', 'Lê Văn Hùng', '0933445566', 'Số 18 Nguyễn Trãi, Thanh Xuân, Hà Nội', NULL, '20990000', 'DA_THANH_TOAN', 'Đã in hóa đơn giấy tại quầy thu ngân'),
('HD_VAT_002', 'DH002', '2026-10-06 16:00:00', 'Đỗ Minh Tuấn', '0912345678', 'Số 45 Xuân Thủy, Cầu Giấy, Hà Nội', '0109876543', '14765500', 'DA_THANH_TOAN', 'Xuất hóa đơn điện tử qua email');


-- --------------------------------------------------------
-- Table structure and data for table `khachhang`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `khachhang`;
CREATE TABLE `khachhang` (
  `maKH` varchar(50) NOT NULL,
  `maTk` varchar(50) DEFAULT NULL COMMENT 'Null nếu khách mua tại quầy không tạo tài khoản',
  `hoVaTen` varchar(100) NOT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `doiTuongUuTien` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`maKH`),
  UNIQUE KEY `soDienThoai` (`soDienThoai`),
  UNIQUE KEY `maTk` (`maTk`),
  CONSTRAINT `fk_khachhang_accounts` FOREIGN KEY (`maTk`) REFERENCES `accounts` (`maTk`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `khachhang` (`maKH`, `maTk`, `hoVaTen`, `soDienThoai`, `email`, `diaChi`, `doiTuongUuTien`) VALUES
('KH001', 'TK007', 'Đỗ Minh Tuấn', '0912345678', 'minhtuan@gmail.com', 'Số 45 Xuân Thủy, Cầu Giấy, Hà Nội', '1'),
('KH002', 'TK008', 'Nguyễn Lan Anh', '0987654321', 'lananh.ng@gmail.com', 'Số 120 Giải Phóng, Hai Bà Trưng, Hà Nội', '0'),
('KH003', 'TK009', 'Bùi Hoàng Nam', '0905112233', 'hoangnam.dev@gmail.com', 'KĐT Vinhomes Smart City, Nam Từ Liêm, Hà Nội', '1'),
('KH004', NULL, 'Lê Văn Hùng', '0933445566', 'vanhung.le@yahoo.com', 'Số 18 Nguyễn Trãi, Thanh Xuân, Hà Nội', '0'),
('KH005', NULL, 'Trần Thu Hà', '0977889900', NULL, 'Thị trấn Phùng, Đan Phượng, Hà Nội', '0'),
('KH105BFA', 'TK004', 'Phạm Thu Ngân', NULL, 'cashier@laptopstore.vn', NULL, '0'),
('KH8EC82C', 'TK_5413F523', 'Ang Nhang Nhang', NULL, 'shushuu186@gmail.com', NULL, '0'),
('KHF90A6C', 'TK001', 'Nguyễn Quản Trị', '', 'admin@laptopstore.vn', NULL, '0');


-- --------------------------------------------------------
-- Table structure and data for table `khuyenmai`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `khuyenmai`;
CREATE TABLE `khuyenmai` (
  `maKM` varchar(50) NOT NULL,
  `tenKM` varchar(150) NOT NULL,
  `loaiKM` varchar(20) NOT NULL COMMENT 'PHAN_TRAM / TIEN_MAT',
  `giaTriGiam` double NOT NULL,
  `mucGiamToiDa` double DEFAULT NULL,
  `donHangToiThieu` double NOT NULL DEFAULT 0,
  `ngayBatDau` datetime NOT NULL,
  `ngayKetThuc` datetime NOT NULL,
  `trangThai` int(11) NOT NULL DEFAULT 1 COMMENT '1: Đang diễn ra, 0: Tạm dừng / Hết hạn',
  PRIMARY KEY (`maKM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `khuyenmai` (`maKM`, `tenKM`, `loaiKM`, `giaTriGiam`, `mucGiamToiDa`, `donHangToiThieu`, `ngayBatDau`, `ngayKetThuc`, `trangThai`) VALUES
('KM_BACK2SCHOOL', 'Chào tân sinh viên - Giảm 5%', 'PHAN_TRAM', '5', '1500000', '15000000', '2026-08-01 00:00:00', '2026-11-30 23:59:59', '1'),
('KM_GAMING1M', 'Trợ giá Laptop Gaming 1.000.000đ', 'TIEN_MAT', '1000000', '1000000', '20000000', '2026-09-01 00:00:00', '2026-12-31 23:59:59', '1'),
('KM_HE2026', 'Khuyến mãi Hè rực rỡ', 'TIEN_MAT', '500000', '500000', '10000000', '2026-05-01 00:00:00', '2026-07-31 23:59:59', '0');


-- --------------------------------------------------------
-- Table structure and data for table `nhacungcap`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `nhacungcap`;
CREATE TABLE `nhacungcap` (
  `maNCC` varchar(50) NOT NULL,
  `tenCongTy` varchar(150) NOT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `diaChiTruSo` varchar(255) DEFAULT NULL,
  `quocTich` varchar(100) DEFAULT NULL,
  `maSoThue` varchar(50) DEFAULT NULL,
  `trangThai` int(11) NOT NULL DEFAULT 1 COMMENT '1: Đang hợp tác, 0: Ngừng hợp tác',
  PRIMARY KEY (`maNCC`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `nhacungcap` (`maNCC`, `tenCongTy`, `soDienThoai`, `email`, `diaChiTruSo`, `quocTich`, `maSoThue`, `trangThai`) VALUES
('NCC_DIGIWORLD', 'Công ty Cổ phần Thế Giới Số (Digiworld)', '02839290059', 'support@dgw.com.vn', 'Số 195-197 Nguyễn Thái Bình, Quận 1, TP.HCM', 'Việt Nam', '0302891122', '1'),
('NCC_PSD', 'Công ty Cổ phần Dịch vụ Phân phối Tổng hợp Dầu khí (PSD)', '02839115577', 'info@psd.com.vn', 'Lầu 2, Tòa nhà PetroVietnam, Quận 1, TP.HCM', 'Việt Nam', '0305432198', '1'),
('NCC_SYNNEX', 'Công ty Cổ phần Synnex FPT', '02473006666', 'contact@synnexfpt.com', 'Số 17 Duy Tân, Cầu Giấy, Hà Nội', 'Việt Nam', '0101234567', '1');


-- --------------------------------------------------------
-- Table structure and data for table `phieubaohanh`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `phieubaohanh`;
CREATE TABLE `phieubaohanh` (
  `maPhieuBH` varchar(50) NOT NULL,
  `maSerial_IMEI` varchar(100) NOT NULL,
  `maKH` varchar(50) NOT NULL,
  `ngayBatDau` datetime NOT NULL,
  `ngayKetThuc` datetime NOT NULL,
  `moTaChinhSach` text DEFAULT NULL,
  `trangThai` varchar(50) NOT NULL,
  `ghiChu` text DEFAULT NULL,
  PRIMARY KEY (`maPhieuBH`),
  KEY `fk_phieubh_serial` (`maSerial_IMEI`),
  KEY `fk_phieubh_khachhang` (`maKH`),
  CONSTRAINT `fk_phieubh_khachhang` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`) ON UPDATE CASCADE,
  CONSTRAINT `fk_phieubh_serial` FOREIGN KEY (`maSerial_IMEI`) REFERENCES `serialsanpham` (`maSerial_IMEI`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `phieubaohanh` (`maPhieuBH`, `maSerial_IMEI`, `maKH`, `ngayBatDau`, `ngayKetThuc`, `moTaChinhSach`, `trangThai`, `ghiChu`) VALUES
('BH2026_001', 'SN_ASUS_F15_0001', 'KH004', '2026-10-01 10:20:00', '2028-10-01 10:20:00', 'Bảo hành chính hãng ASUS 24 tháng (1 đổi 1 trong 30 ngày đầu nếu lỗi NSX)', 'Đang hiệu lực', NULL),
('BH2026_002', 'SN_DELL_3520_0001', 'KH001', '2026-10-05 16:30:00', '2027-10-05 16:30:00', 'Bảo hành chính hãng Dell ProSupport 12 tháng tận nơi', 'Đang tiếp nhận bảo hành', NULL),
('PBH_D1A80871', 'SN_DELL_3520_0002', 'KH8EC82C', '2026-10-26 21:00:58', '2027-10-26 21:00:58', 'Bảo hành chính hãng 12 tháng tính từ ngày hoàn tất đơn hàng DH008', 'CON_HAN', NULL),
('PBH_D266465B', 'SN_LENOVO_LOQ_036', 'KH8EC82C', '2026-10-26 21:01:10', '2028-10-26 21:01:10', 'Bảo hành chính hãng 24 tháng tính từ ngày hoàn tất đơn hàng DH006', 'CON_HAN', NULL);


-- --------------------------------------------------------
-- Table structure and data for table `phieunhap`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `phieunhap`;
CREATE TABLE `phieunhap` (
  `maPhieu` varchar(50) NOT NULL,
  `ngayNhap` datetime NOT NULL DEFAULT current_timestamp(),
  `maNCC` varchar(50) NOT NULL,
  `nguoiNhap` varchar(50) NOT NULL,
  `soHoaDonNCC` varchar(50) DEFAULT NULL,
  `tongTienTraTruocNCC` double NOT NULL DEFAULT 0,
  `tongTienNhap` double NOT NULL DEFAULT 0,
  `trangThaiThanhToan` varchar(50) DEFAULT NULL,
  `trangThaiNhapKho` varchar(50) DEFAULT NULL,
  `ghiChu` text DEFAULT NULL,
  PRIMARY KEY (`maPhieu`),
  KEY `fk_phieunhap_ncc` (`maNCC`),
  KEY `fk_phieunhap_accounts` (`nguoiNhap`),
  CONSTRAINT `fk_phieunhap_accounts` FOREIGN KEY (`nguoiNhap`) REFERENCES `accounts` (`maTk`) ON UPDATE CASCADE,
  CONSTRAINT `fk_phieunhap_ncc` FOREIGN KEY (`maNCC`) REFERENCES `nhacungcap` (`maNCC`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `phieunhap` (`maPhieu`, `ngayNhap`, `maNCC`, `nguoiNhap`, `soHoaDonNCC`, `tongTienTraTruocNCC`, `tongTienNhap`, `trangThaiThanhToan`, `trangThaiNhapKho`, `ghiChu`) VALUES
('PN-20261026-496D', '2026-10-26 07:01:44', 'NCC_DIGIWORLD', 'TK001', 'HD_NCC_6146', '50000000', '125000000', 'CON_NO', 'DA_NHAP_KHO', ''),
('PN2026_001', '2026-09-10 09:30:00', 'NCC_SYNNEX', 'TK002', 'HD_FPT_8821', '70000000', '70000000', 'DA_THANH_TOAN', 'DA_NHAP_KHO', 'Nhập lô ASUS TUF và Lenovo LOQ đầu kỳ'),
('PN2026_002', '2026-09-15 14:00:00', 'NCC_DIGIWORLD', 'TK002', 'HD_DGW_4410', '50000000', '92000000', 'THANH_TOAN_MOT_PHAN', 'DA_NHAP_KHO', 'Nhập lô Dell Inspiron và MacBook Air M3');


-- --------------------------------------------------------
-- Table structure and data for table `sanpham`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sanpham`;
CREATE TABLE `sanpham` (
  `maSP` varchar(50) NOT NULL,
  `tenSP` varchar(200) NOT NULL,
  `giaBan` double NOT NULL,
  `soLuongTon` int(11) NOT NULL DEFAULT 0,
  `maTH` varchar(50) NOT NULL,
  `maDM` int(11) NOT NULL,
  `thoiGianBaoHanh` int(11) NOT NULL DEFAULT 12 COMMENT 'Đơn vị: Tháng',
  `thongSoKyThuat` text DEFAULT NULL,
  `moTa` text DEFAULT NULL,
  `hinhAnh` varchar(255) DEFAULT NULL,
  `viTriKho` varchar(100) DEFAULT NULL,
  `trangThai` int(11) NOT NULL DEFAULT 1 COMMENT '1: Đang kinh doanh, 0: Ngừng kinh doanh',
  `isDeleted` int(11) NOT NULL DEFAULT 0 COMMENT '0: Chưa xóa, 1: Đã xóa mềm',
  PRIMARY KEY (`maSP`),
  KEY `fk_sanpham_thuonghieu` (`maTH`),
  KEY `fk_sanpham_danhmuc` (`maDM`),
  CONSTRAINT `fk_sanpham_danhmuc` FOREIGN KEY (`maDM`) REFERENCES `danhmuc` (`maDM`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sanpham_thuonghieu` FOREIGN KEY (`maTH`) REFERENCES `thuonghieu` (`maTH`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sanpham` (`maSP`, `tenSP`, `giaBan`, `soLuongTon`, `maTH`, `maDM`, `thoiGianBaoHanh`, `thongSoKyThuat`, `moTa`, `hinhAnh`, `viTriKho`, `trangThai`, `isDeleted`) VALUES
('SP001', 'ASUS TUF Gaming F15 FX507ZC4', '21990000', '5', 'TH_ASUS', '1', '24', 'CPU: Intel Core i5-12500H | RAM: 16GB DDR4 | SSD: 512GB NVMe | VGA: RTX 3050 4GB | Màn hình: 15.6 inch FHD 144Hz', 'Laptop gaming quốc dân chuẩn độ bền quân đội Mỹ MIL-STD-810H', 'assets/images/products/SP001.jpg', 'Kệ A1 - Tầng 1', '1', '0'),
('SP002', 'Lenovo LOQ 15IAX9', '20490000', '3', 'TH_LENOVO', '1', '24', 'CPU: Intel Core i5-12450HX | RAM: 16GB DDR5 | SSD: 512GB | VGA: RTX 3050 6GB | Màn hình: 15.6 inch FHD 144Hz 100% sRGB', 'Thiết kế tản nhiệt tối ưu, màn hình chuẩn màu cho cả gaming và đồ họa', 'assets/images/products/SP002.jpg', 'Kệ A2 - Tầng 1', '1', '0'),
('SP003', 'Dell Inspiron 15 3520', '15490000', '0', 'TH_DELL', '2', '12', 'CPU: Intel Core i5-1235U | RAM: 16GB | SSD: 512GB | Màn hình: 15.6 inch FHD 120Hz', 'Dòng laptop văn phòng bền bỉ, màn hình tần số quét cao mượt mà', 'assets/images/products/SP003.jpg', 'Kệ B1 - Tầng 2', '1', '0'),
('SP004', 'Apple MacBook Air M3 13 inch 2024', '27990000', '2', 'TH_APPLE', '3', '12', 'Chip: Apple M3 8-core CPU, 8-core GPU | RAM: 16GB Unified | SSD: 256GB | Màn hình: 13.6 inch Liquid Retina', 'Siêu mỏng nhẹ, thời lượng pin lên đến 18 giờ làm việc liên tục', 'assets/images/products/SP004.jpg', 'Tủ Kính C1 - Tầng 1', '1', '0'),
('SP005', 'MSI Creator M16 HX', '38990000', '0', 'TH_MSI', '4', '24', 'CPU: Intel Core i7-14700HX | RAM: 32GB DDR5 | SSD: 1TB | VGA: RTX 4060 8GB | Màn hình: 16 inch QHD+ 240Hz', 'Máy trạm sáng tạo nội dung chuyên nghiệp', 'assets/images/products/SP005.jpg', 'Kệ D1 - Tầng 2', '0', '0');


-- --------------------------------------------------------
-- Table structure and data for table `serialsanpham`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `serialsanpham`;
CREATE TABLE `serialsanpham` (
  `maSerial_IMEI` varchar(100) NOT NULL,
  `maSP` varchar(50) NOT NULL,
  `idChiTietNhap` int(11) NOT NULL,
  `idChiTietDonHang` int(11) DEFAULT NULL COMMENT 'Null khi máy đang còn trong kho, chưa gắn vào đơn hàng',
  `ngayXuatKho` datetime DEFAULT NULL,
  `trangThai` varchar(50) NOT NULL DEFAULT 'Trong kho' COMMENT 'Trong kho / Đã bán / Đang bảo hành / Lỗi',
  `ghiChu` text DEFAULT NULL,
  PRIMARY KEY (`maSerial_IMEI`),
  KEY `fk_serial_sanpham` (`maSP`),
  KEY `fk_serial_chitietnhap` (`idChiTietNhap`),
  KEY `fk_serial_chitietdonhang` (`idChiTietDonHang`),
  CONSTRAINT `fk_serial_chitietdonhang` FOREIGN KEY (`idChiTietDonHang`) REFERENCES `chitietdonhang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_serial_chitietnhap` FOREIGN KEY (`idChiTietNhap`) REFERENCES `chitietnhap` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_serial_sanpham` FOREIGN KEY (`maSP`) REFERENCES `sanpham` (`maSP`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `serialsanpham` (`maSerial_IMEI`, `maSP`, `idChiTietNhap`, `idChiTietDonHang`, `ngayXuatKho`, `trangThai`, `ghiChu`) VALUES
('SN_ASUS_F15_0001', 'SP001', '1', '1', '2026-10-01 10:20:00', 'Đã bán', 'Đã xuất bán cho đơn tại quầy DH001'),
('SN_ASUS_F15_0002', 'SP001', '1', NULL, NULL, 'Trong kho', 'Hàng nguyên seal tại Kệ A1'),
('SN_ASUS_F15_0018', 'SP001', '6', NULL, NULL, 'Trong kho', 'Nhập từ phiếu PN-20261026-496D'),
('SN_ASUS_F15_0036', 'SP001', '6', NULL, NULL, 'Trong kho', 'Nhập từ phiếu PN-20261026-496D'),
('SN_ASUS_F15_0069', 'SP001', '6', NULL, NULL, 'Trong kho', 'Nhập từ phiếu PN-20261026-496D'),
('SN_ASUS_F15_0183', 'SP001', '6', NULL, NULL, 'Trong kho', 'Nhập từ phiếu PN-20261026-496D'),
('SN_DELL_3520_0001', 'SP003', '3', '2', '2026-10-05 16:30:00', 'Đang bảo hành', 'Khách mang qua kiểm tra định kỳ quạt tản nhiệt'),
('SN_DELL_3520_0002', 'SP003', '3', '13', '2026-10-26 21:00:58', 'Đã bán', 'Hàng nguyên seal tại Kệ B1 [Bán tại quầy đơn DH006]'),
('SN_LENOVO_LOQ_0001', 'SP002', '2', '3', '2026-10-20 11:00:00', 'Đã bán', 'Đang đóng gói giao cho đơn online DH003'),
('SN_LENOVO_LOQ_0002', 'SP002', '2', NULL, NULL, 'Trong kho', 'Hàng nguyên seal tại Kệ A2 [Bán tại quầy đơn DH007]'),
('SN_LENOVO_LOQ_018', 'SP002', '5', NULL, NULL, 'Trong kho', 'Nhập từ phiếu PN-20261026-496D'),
('SN_LENOVO_LOQ_036', 'SP002', '5', '11', '2026-10-26 21:01:10', 'Đã bán', 'Nhập từ phiếu PN-20261026-496D'),
('SN_LENOVO_LOQ_054', 'SP002', '5', NULL, NULL, 'Trong kho', 'Nhập từ phiếu PN-20261026-496D'),
('SN_MAC_M3_0001', 'SP004', '4', NULL, NULL, 'Trong kho', 'Chờ gắn serial khi duyệt đơn DH004'),
('SN_MAC_M3_0002', 'SP004', '4', NULL, NULL, 'Lỗi', 'Phát hiện cấn nhẹ vỏ hộp khi kiểm kho, chờ đổi trả NCC');


-- --------------------------------------------------------
-- Table structure and data for table `thuonghieu`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `thuonghieu`;
CREATE TABLE `thuonghieu` (
  `maTH` varchar(50) NOT NULL,
  `tenTH` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `xuatXu` varchar(100) DEFAULT NULL,
  `moTa` text DEFAULT NULL,
  `trangThai` int(11) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Ẩn',
  PRIMARY KEY (`maTH`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `thuonghieu` (`maTH`, `tenTH`, `logo`, `xuatXu`, `moTa`, `trangThai`) VALUES
('TH_APPLE', 'Apple', '/images/logos/apple.png', 'Hoa Kỳ', 'Hệ sinh thái MacBook Air và MacBook Pro sử dụng chip Apple Silicon', '1'),
('TH_ASUS', 'ASUS', '/images/logos/asus.png', 'Đài Loan', 'Thương hiệu laptop đa phân khúc từ văn phòng Vivobook, Zenbook đến ROG, TUF Gaming', '1'),
('TH_DELL', 'Dell', '/images/logos/dell.png', 'Hoa Kỳ', 'Dòng laptop bền bỉ cho doanh nghiệp và đồ họa: Inspiron, XPS, Alienware', '1'),
('TH_LENOVO', 'Lenovo', '/images/logos/lenovo.png', 'Trung Quốc', 'Nổi tiếng với dòng ThinkPad doanh nhân và Legion chuyên game', '1'),
('TH_MSI', 'MSI', '/images/logos/msi.png', 'Đài Loan', 'Chuyên gia laptop gaming và máy trạm đồ họa hiệu năng cao', '1');


-- --------------------------------------------------------
-- Table structure and data for table `userdathang`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `userdathang`;
CREATE TABLE `userdathang` (
  `idDatHang` int(11) NOT NULL AUTO_INCREMENT,
  `maDH` varchar(50) NOT NULL COMMENT 'Chỉ khởi tạo khi DonHang.kenhBan == ONLINE',
  `tenNguoiNhan` varchar(100) NOT NULL,
  `sdtNhanHang` varchar(20) NOT NULL,
  `diaChiGiaoHang` varchar(255) NOT NULL,
  `ghiChuKhachHang` text DEFAULT NULL,
  `maGiaoDichOnline` varchar(100) DEFAULT NULL COMMENT 'Mã tham chiếu từ VNPay/Momo/Ngân hàng',
  `trangThaiThanhToan` varchar(30) NOT NULL COMMENT 'CHUA_THANH_TOAN / DA_THANH_TOAN / HOAN_TIEN',
  PRIMARY KEY (`idDatHang`),
  UNIQUE KEY `maDH` (`maDH`),
  CONSTRAINT `fk_userdathang_donhang` FOREIGN KEY (`maDH`) REFERENCES `donhang` (`maDH`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `userdathang` (`idDatHang`, `maDH`, `tenNguoiNhan`, `sdtNhanHang`, `diaChiGiaoHang`, `ghiChuKhachHang`, `maGiaoDichOnline`, `trangThaiThanhToan`) VALUES
('1', 'DH002', 'Đỗ Minh Tuấn', '0912345678', 'Số 45 Xuân Thủy, Cầu Giấy, Hà Nội', 'Giao trong giờ hành chính giúp mình', 'VNPAY_20261005_99812', 'DA_THANH_TOAN'),
('2', 'DH003', 'Nguyễn Lan Anh', '0987654321', 'Số 120 Giải Phóng, Hai Bà Trưng, Hà Nội', 'Gọi trước khi giao 15 phút', NULL, 'CHUA_THANH_TOAN'),
('3', 'DH004', 'Bùi Hoàng Nam', '0905112233', 'Tòa S2.01 Vinhomes Smart City, Nam Từ Liêm, Hà Nội', 'Cài sẵn Windows 11 và Office bản quyền', 'VCB_20261024_77123', 'DA_THANH_TOAN'),
('4', 'DH005', 'Nguyễn Lan Anh', '0987654321', 'Số 120 Giải Phóng, Hai Bà Trưng, Hà Nội', '', NULL, 'CHUA_THANH_TOAN'),
('7', 'DH006', 'Ang Nhang Nhang', '0987654321', '120 an lieng', 'nhang nhnag', NULL, 'CHUA_THANH_TOAN'),
('8', 'DH007', 'Ang Nhang Nhang', '0987654321', '120 yen lang', '', NULL, 'CHUA_THANH_TOAN'),
('9', 'DH008', 'Ang Nhang Nhang', '0987654321', '120 yen lang', '', NULL, 'CHUA_THANH_TOAN');


COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
-- End of Backup
