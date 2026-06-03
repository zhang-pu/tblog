<?php
/**
 * Flow Template - Footer
 */
$site_name = get_setting('site_name', '张璞博客');
?>
            </div>
        </div>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> <?php echo e($site_name); ?> · Apache 2.0</p>
        </div>
    </footer>
    <script>
        function toggleMobileMenu() {
            var nav = document.querySelector('.main-nav');
            nav.classList.toggle('show');
        }
    </script>
</body>
</html>
