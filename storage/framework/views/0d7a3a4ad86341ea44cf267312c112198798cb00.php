<style>
    /* Global Back to Top Floating Button */
    .global-back-to-top-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: #414142;
        color: #ffffff;
        border: none;
        outline: none;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        z-index: 990;
        opacity: 0;
        visibility: hidden;
        transform: translateY(12px) scale(0.9);
        transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease, background-color 0.2s ease, box-shadow 0.2s ease;
        padding: 0;
        line-height: 1;
        text-decoration: none !important;
    }

    .global-back-to-top-btn.is-visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    .global-back-to-top-btn:hover {
        background-color: #333333;
        color: #ffffff;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.32);
        transform: translateY(-2px) scale(1.04);
    }

    .global-back-to-top-btn:active {
        transform: translateY(0) scale(0.98);
    }

    .global-back-to-top-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.25);
    }

    @media (max-width: 768px) {
        .global-back-to-top-btn {
            bottom: 22px;
            right: 20px;
            width: 40px;
            height: 40px;
            font-size: 14px;
        }
    }
</style>

<!-- Global Back to Top Button Component -->
<button type="button" id="globalBackToTopBtn" class="global-back-to-top-btn" aria-label="Back to top" title="<?php echo e(__('web.back_to_top') ?? 'Back to top'); ?>">
    <i class="fa fa-chevron-up" aria-hidden="true"></i>
</button>

<script>
(function() {
    function initGlobalBackToTop() {
        var btn = document.getElementById('globalBackToTopBtn');
        if (!btn) return;

        var scrollThreshold = 250;
        var ticking = false;

        function getScrollTop() {
            return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
        }

        function checkScroll() {
            if (getScrollTop() > scrollThreshold) {
                btn.classList.add('is-visible');
            } else {
                btn.classList.remove('is-visible');
            }
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(checkScroll);
                ticking = true;
            }
        }, { passive: true });

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if ('scrollBehavior' in document.documentElement.style) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else if (window.jQuery) {
                window.jQuery('html, body').animate({ scrollTop: 0 }, 400);
            } else {
                window.scrollTo(0, 0);
            }
        });

        checkScroll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlobalBackToTop);
    } else {
        initGlobalBackToTop();
    }
})();
</script>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/back_to_top.blade.php ENDPATH**/ ?>