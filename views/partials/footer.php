    </main>

    <footer class="site-footer" id="lien-he">
        <div class="container">
            <div class="row gy-4 footer-top">
                <div class="col-lg-4 col-md-6">
                    <a class="footer-brand" href="<?= BASE_URL ?>">
                        <span class="brand-badge"><i class="bi bi-bowl-hot"></i></span>
                        <span class="brand-text">An<span>LaMe</span></span>
                    </a>
                    <p class="footer-desc">
                        AnLaMe mang món ngon từ những căn bếp tận tâm đến tận cửa nhà bạn.
                        Đặt món dễ dàng, giao nhanh trong 20 phút, chuẩn vị mỗi ngày.
                    </p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                        <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="footer-title">Khám phá</h5>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                        <li><a href="#thuc-don">Thực đơn</a></li>
                        <li><a href="#khuyen-mai">Khuyến mãi</a></li>
                        <li><a href="#">Thương hiệu</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="footer-title">Hỗ trợ</h5>
                    <ul class="footer-links">
                        <li><a href="#">Câu hỏi thường gặp</a></li>
                        <li><a href="#">Chính sách giao hàng</a></li>
                        <li><a href="#">Đổi trả &amp; hoàn tiền</a></li>
                        <li><a href="#">Điều khoản sử dụng</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Liên hệ</h5>
                    <ul class="footer-contact">
                        <li><i class="bi bi-geo-alt-fill"></i> 123 Nguyễn Văn Linh, Đà Nẵng</li>
                        <li><i class="bi bi-telephone-fill"></i> 1900 1234</li>
                        <li><i class="bi bi-envelope-fill"></i> hotro@anlame.vn</li>
                    </ul>

                    <form class="newsletter" onsubmit="return false;">
                        <input type="email" placeholder="Email của bạn" aria-label="Email đăng ký nhận ưu đãi">
                        <button type="submit"><i class="bi bi-send-fill"></i></button>
                    </form>
                    <small class="newsletter-note">Đăng ký để nhận ưu đãi mới nhất từ AnLaMe.</small>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> AnLaMe. Món ngon mỗi ngày, niềm vui mỗi bữa.</p>
                <div class="footer-payments">
                    <i class="bi bi-credit-card-2-front"></i>
                    <i class="bi bi-wallet2"></i>
                    <i class="bi bi-bank"></i>
                    <i class="bi bi-qr-code"></i>
                </div>
            </div>
        </div>
    </footer>

    <button class="back-to-top" id="backToTop" type="button" aria-label="Lên đầu trang">
        <i class="bi bi-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>

</html>
