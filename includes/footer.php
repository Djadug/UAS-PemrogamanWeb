<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
?>
    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About <?= SITE_NAME ?></h5>
                    <p>Track and reduce your carbon footprint while connecting with an eco-conscious community.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= url('modules/education/articles.php') ?>">Articles</a></li>
                        <li><a href="<?= url('modules/education/tips.php') ?>">Eco Tips</a></li>
                        <li><a href="<?= url('modules/community/challenges.php') ?>">Challenges</a></li>
                        <li><a href="<?= url('modules/community/forums.php') ?>">Community</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect With Us</h5>
                    <div class="social-links">
                        <a href="#" class="me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="<?= url('views/privacy-policy.php') ?>">Privacy Policy</a> |
                    <a href="<?= url('views/terms.php') ?>">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Files -->
    <script src="<?= asset('js/jquery.min.js') ?>"></script>
    <script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
