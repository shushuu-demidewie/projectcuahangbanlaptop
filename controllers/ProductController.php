<?php
/**
 * File: controllers/ProductController.php
 * Controller hiển thị chi tiết sản phẩm và xem nhanh laptop cho khách hàng
 */

declare(strict_types=1);

require_once __DIR__ . '/ProductDetailController.php';

class ProductController extends ProductDetailController {
    // Kế thừa toàn bộ logic detail($maSP) và quickView($maSP) từ ProductDetailController
}
