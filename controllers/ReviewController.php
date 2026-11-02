<?php
/**
 * File: controllers/ReviewController.php
 * Xử lý tiếp nhận Đánh giá & Chấm sao sản phẩm
 */

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/ReviewModel.php';

class ReviewController {
    private ReviewModel $reviewModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->reviewModel = new ReviewModel();
    }

    /**
     * Gửi đánh giá sản phẩm (Hỗ trợ cả AJAX và POST truyền thống)
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=home");
            exit();
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $maSP        = trim($_POST['maSP'] ?? '');
        $hoTen       = trim($_POST['hoTen'] ?? '');
        $soDienThoai = trim($_POST['soDienThoai'] ?? '');
        $soSao       = (int)($_POST['soSao'] ?? 5);
        $noiDung     = trim($_POST['noiDung'] ?? '');

        // 1. Kiểm tra tính hợp lệ
        if (empty($maSP) || empty($hoTen) || empty($noiDung)) {
            $msg = 'Vui lòng nhập đầy đủ Họ tên và Nội dung đánh giá!';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            $_SESSION['flash_client_error'] = $msg;
            header("Location: index.php?action=client-detail&id=" . urlencode($maSP));
            exit();
        }

        if (strlen($noiDung) < 5) {
            $msg = 'Nội dung cảm nhận nên chứa ít nhất 5 ký tự!';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            $_SESSION['flash_client_error'] = $msg;
            header("Location: index.php?action=client-detail&id=" . urlencode($maSP));
            exit();
        }

        $success = $this->reviewModel->addReview([
            'maSP'        => $maSP,
            'hoTen'       => $hoTen,
            'soDienThoai' => $soDienThoai,
            'soSao'       => $soSao,
            'noiDung'     => $noiDung
        ]);

        if ($success) {
            $msg = 'Cảm ơn quý khách đã gửi đánh giá cho sản phẩm! Đánh giá đã được ghi nhận thành công.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => $msg]);
                exit();
            }
            $_SESSION['flash_client_success'] = $msg;
        } else {
            $msg = 'Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại!';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            $_SESSION['flash_client_error'] = $msg;
        }

        header("Location: index.php?action=client-detail&id=" . urlencode($maSP) . "#reviews");
        exit();
    }
}
