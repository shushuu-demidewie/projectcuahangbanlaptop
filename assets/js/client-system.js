/**
 * File: assets/js/client-system.js
 * Tối ưu hóa trải nghiệm khách hàng với Motion Physics (Animate UI)
 * & Cơ chế In-Memory Cache, AbortController, Lazy Loading (System Design Primer)
 */

(function () {
    'use strict';

    /* ==============================================================
       1. SYSTEM DESIGN: IN-MEMORY CACHE & ABORT CONTROLLER
       ============================================================== */
    const SearchCache = new Map();
    const QuickViewCache = new Map();
    let currentSearchAbortController = null;

    /**
     * Cache helper with TTL (Time-To-Live in milliseconds)
     */
    const CacheService = {
        set(map, key, data, ttlMs = 300000) { // default 5 mins
            map.set(key, {
                data: data,
                expiry: Date.now() + ttlMs
            });
        },
        get(map, key) {
            if (!map.has(key)) return null;
            const item = map.get(key);
            if (Date.now() > item.expiry) {
                map.delete(key);
                return null;
            }
            return item.data;
        }
    };

    /* ==============================================================
       2. ANIMATE UI: SPOTLIGHT MOUSE TRACKING ENGINE
       ============================================================== */
    function initSpotlightCards() {
        const cards = document.querySelectorAll('.laptop-card, .spotlight-card');
        if (!cards.length) return;

        cards.forEach(card => {
            // Remove previous listeners if any
            card.removeEventListener('mousemove', handleCardMouseMove);
            card.addEventListener('mousemove', handleCardMouseMove, { passive: true });
        });
    }

    function handleCardMouseMove(e) {
        const card = e.currentTarget;
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        card.style.setProperty('--mouse-x', `${x}px`);
        card.style.setProperty('--mouse-y', `${y}px`);
    }

    /* ==============================================================
       3. SYSTEM DESIGN & MOTION: LIVE SEARCH VỚI CACHING & ABORT
       ============================================================== */
    function initOptimizedSearch() {
        const searchInput = document.getElementById('headerSearchInput');
        const searchDropdown = document.getElementById('liveSearchResults');
        const searchForm = document.getElementById('headerSearchForm');

        if (!searchInput || !searchDropdown || !searchForm) return;

        let debounceTimer = null;

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 1) {
                searchDropdown.classList.add('d-none');
                searchDropdown.innerHTML = '';
                return;
            }

            // 1. Kiểm tra Cache trong bộ nhớ (0ms latency theo System Design)
            const cachedResults = CacheService.get(SearchCache, query.toLowerCase());
            if (cachedResults) {
                renderSearchResults(cachedResults, query, searchDropdown);
                return;
            }

            // 2. Debounce và hủy request đang chạy dở (AbortController)
            debounceTimer = setTimeout(() => {
                if (currentSearchAbortController) {
                    currentSearchAbortController.abort();
                }
                currentSearchAbortController = new AbortController();

                // Hiển thị skeleton loading trong dropdown
                searchDropdown.innerHTML = `
                    <div class="p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="skeleton-shimmer" style="width: 48px; height: 48px;"></div>
                            <div class="flex-grow-1">
                                <div class="skeleton-shimmer mb-1" style="height: 14px; width: 80%;"></div>
                                <div class="skeleton-shimmer" style="height: 12px; width: 40%;"></div>
                            </div>
                        </div>
                    </div>
                `;
                searchDropdown.classList.remove('d-none');

                fetch('index.php?action=api-search-products&keyword=' + encodeURIComponent(query), {
                    signal: currentSearchAbortController.signal
                })
                .then(res => res.json())
                .then(items => {
                    // Lưu vào cache để tái sử dụng
                    CacheService.set(SearchCache, query.toLowerCase(), items);
                    renderSearchResults(items, query, searchDropdown);
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        console.error('Lỗi tìm kiếm:', err);
                    }
                });
            }, 180); // 180ms debounce tối ưu
        });

        // Đóng dropdown khi click ngoài
        document.addEventListener('click', (e) => {
            if (!searchForm.contains(e.target)) {
                searchDropdown.classList.add('d-none');
            }
        });

        // Mở lại dropdown khi focus
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 1 && searchDropdown.children.length > 0) {
                searchDropdown.classList.remove('d-none');
            }
        });
    }

    function renderSearchResults(items, query, container) {
        if (!items || items.length === 0) {
            container.innerHTML = `
                <div class="live-search-empty py-4 text-center">
                    <i class="bi bi-search d-block fs-3 mb-2 text-secondary opacity-50"></i>
                    Không tìm thấy laptop nào phù hợp với "<strong>${escapeHtml(query)}</strong>"
                </div>
            `;
            container.classList.remove('d-none');
            return;
        }

        let html = items.map(item => {
            const imgTag = item.hinhAnh 
                ? `<img src="${escapeHtml(item.hinhAnh)}" class="live-search-img" alt="${escapeHtml(item.tenSP)}" onerror="this.src='assets/images/products/SP004.jpg'">`
                : `<div class="live-search-img d-flex align-items-center justify-content-center text-secondary"><i class="bi bi-laptop fs-4"></i></div>`;
            
            const brandTag = item.tenTH 
                ? `<span class="live-search-brand-badge">${escapeHtml(item.tenTH)}</span>` 
                : '';

            return `
                <a href="${escapeHtml(item.url)}" class="live-search-item">
                    ${imgTag}
                    <div class="live-search-info">
                        <div class="live-search-name">${escapeHtml(item.tenSP)}</div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="live-search-price">${escapeHtml(item.giaBanFmt)}</span>
                            ${brandTag}
                        </div>
                    </div>
                </a>
            `;
        }).join('');

        html += `
            <a href="index.php?action=client-home&keyword=${encodeURIComponent(query)}" class="live-search-footer">
                Xem tất cả kết quả cho "<strong>${escapeHtml(query)}</strong>" <i class="bi bi-arrow-right ms-1"></i>
            </a>
        `;

        container.innerHTML = html;
        container.classList.remove('d-none');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /* ==============================================================
       4. ANIMATE UI: QUICK VIEW VỚI IN-MEMORY CACHE
       ============================================================== */
    window.openQuickViewOptimized = function (maSP) {
        window.currentQuickViewMaSP = maSP;
        const qvModalEl = document.getElementById('quickViewModal');
        if (!qvModalEl) return;

        let modalInstance = bootstrap.Modal.getInstance(qvModalEl) || new bootstrap.Modal(qvModalEl);

        // Kiểm tra xem dữ liệu SP này đã có trong Cache chưa
        const cachedProduct = CacheService.get(QuickViewCache, maSP);
        if (cachedProduct) {
            renderQuickViewData(cachedProduct);
            modalInstance.show();
            return;
        }

        // Nếu chưa có, mở modal và hiện skeleton
        document.getElementById('qvTitle').textContent = 'Đang tải cấu hình máy...';
        document.getElementById('qvSpecsPills').innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span>';
        document.getElementById('qvQuantity').value = 1;
        modalInstance.show();

        fetch('index.php?action=product-quickview&id=' + encodeURIComponent(maSP))
            .then(res => res.json())
            .then(res => {
                if (res.success && res.product) {
                    CacheService.set(QuickViewCache, maSP, res.product);
                    renderQuickViewData(res.product);
                }
            })
            .catch(err => {
                console.error('Quick view error:', err);
            });
    };

    function renderQuickViewData(p) {
        document.getElementById('qvTitle').textContent = p.tenSP;
        document.getElementById('qvCategory').textContent = p.tenDM;
        document.getElementById('qvBrand').textContent = p.tenTH;
        document.getElementById('qvPrice').textContent = p.giaBanFormatted;
        document.getElementById('qvWarranty').textContent = p.thoiGianBaoHanh;
        document.getElementById('qvImage').src = p.hinhAnh;
        document.getElementById('qvFullDetailLink').href = 'index.php?action=client-product-detail&id=' + encodeURIComponent(p.maSP);

        if (p.soLuongTon > 0) {
            document.getElementById('qvStockBadge').innerHTML = '<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check2 me-1"></i>Còn ' + p.soLuongTon + ' máy</span>';
            document.getElementById('qvBtnAddToCart').disabled = false;
        } else {
            document.getElementById('qvStockBadge').innerHTML = '<span class="badge bg-danger">Tạm hết hàng</span>';
            document.getElementById('qvBtnAddToCart').disabled = true;
        }

        let specsHtml = '';
        if (p.specs) {
            if (p.specs.cpu) specsHtml += '<span class="spec-pill"><i class="bi bi-cpu me-1"></i>' + escapeHtml(p.specs.cpu) + '</span>';
            if (p.specs.ram) specsHtml += '<span class="spec-pill"><i class="bi bi-memory me-1"></i>' + escapeHtml(p.specs.ram) + '</span>';
            if (p.specs.ssd) specsHtml += '<span class="spec-pill"><i class="bi bi-device-hdd me-1"></i>' + escapeHtml(p.specs.ssd) + '</span>';
            if (p.specs.vga) specsHtml += '<span class="spec-pill text-danger fw-bold"><i class="bi bi-gpu-card me-1"></i>' + escapeHtml(p.specs.vga) + '</span>';
            if (p.specs.screen) specsHtml += '<span class="spec-pill"><i class="bi bi-display me-1"></i>' + escapeHtml(p.specs.screen) + '</span>';
        }
        document.getElementById('qvSpecsPills').innerHTML = specsHtml || '<span class="text-muted small">Đang cập nhật cấu hình</span>';
    }

    // Map openQuickView sang phiên bản optimized
    window.openQuickView = window.openQuickViewOptimized;

    /* ==============================================================
       5. ANIMATE UI: OPTIMISTIC CART ADDITION VỚI SPRING BADGE BUMP
       ============================================================== */
    window.addToCartAjaxSpring = function (maSP, quantity = 1, buttonEl = null) {
        let originalHtml = '';
        if (buttonEl) {
            originalHtml = buttonEl.innerHTML;
            buttonEl.disabled = true;
            buttonEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        }

        // Optimistic feedback: kích hoạt hiệu ứng rung nhẹ trên nút
        if (buttonEl) buttonEl.style.transform = 'scale(0.92)';
        setTimeout(() => { if (buttonEl) buttonEl.style.transform = ''; }, 150);

        const formData = new URLSearchParams();
        formData.append('maSP', maSP);
        formData.append('quantity', quantity);

        fetch('index.php?action=cart-add', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {
            if (buttonEl) {
                buttonEl.disabled = false;
                buttonEl.innerHTML = originalHtml;
            }

            if (data.success) {
                // Spring Bump hiệu ứng trên giỏ hàng (Animate UI)
                const headerBadge = document.getElementById('headerCartCount');
                if (headerBadge) {
                    headerBadge.textContent = data.cartCount;
                    headerBadge.classList.remove('bump');
                    void headerBadge.offsetWidth; // trigger reflow
                    headerBadge.classList.add('bump');
                    setTimeout(() => headerBadge.classList.remove('bump'), 350);
                }

                // Hiển thị Toast
                const toastEl = document.getElementById('cartToast');
                if (toastEl) {
                    if (data.product) {
                        const nameEl = document.getElementById('toastProductName');
                        const priceEl = document.getElementById('toastProductPrice');
                        const imgEl = document.getElementById('toastProductImg');
                        if (nameEl) nameEl.textContent = data.product.tenSP;
                        if (priceEl) priceEl.textContent = new Intl.NumberFormat('vi-VN').format(data.product.giaBan) + ' đ';
                        if (imgEl && data.product.hinhAnh) imgEl.src = data.product.hinhAnh;
                    }
                    const toast = bootstrap.Toast.getInstance(toastEl) || new bootstrap.Toast(toastEl);
                    toast.show();
                }
            } else {
                alert(data.message || 'Không thể thêm sản phẩm vào giỏ!');
            }
        })
        .catch(err => {
            if (buttonEl) {
                buttonEl.disabled = false;
                buttonEl.innerHTML = originalHtml;
            }
            console.error('Lỗi thêm giỏ hàng:', err);
        });
    };

    window.addToCartAjax = window.addToCartAjaxSpring;

    /* ==============================================================
       6. FLOATING QUICK DOCK & SCROLL TO TOP
       ============================================================== */
    function initFloatingDock() {
        const btnScroll = document.getElementById('btnScrollToTop');
        if (!btnScroll) return;

        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    if (window.scrollY > 300) {
                        btnScroll.classList.add('visible');
                    } else {
                        btnScroll.classList.remove('visible');
                    }
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        btnScroll.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    /* ==============================================================
       7. IMAGE DECODING & SMOOTH FADE-IN (System Design CLS prevention)
       ============================================================== */
    function initImageEnhancements() {
        const images = document.querySelectorAll('.product-thumb-img, .hero-banner-img');
        images.forEach(img => {
            img.classList.add('img-smooth-load');
            if (img.complete) {
                img.classList.add('is-loaded');
            } else {
                img.addEventListener('load', () => img.classList.add('is-loaded'), { once: true });
            }
        });
    }

    /* ==============================================================
       INITIALIZATION
       ============================================================== */
    document.addEventListener('DOMContentLoaded', () => {
        initSpotlightCards();
        initOptimizedSearch();
        initFloatingDock();
        initImageEnhancements();
    });

})();
