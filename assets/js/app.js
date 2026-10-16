document.addEventListener('DOMContentLoaded', function () {
    // AOS initialization
    AOS.init({
        duration: 400,
        once: true
    });

    // Mobile menu toggle with slide-in
    window.openMobileMenu = function () {
        var mobileMenu = document.getElementById('mobile-menu');
        var mobileOverlay = document.getElementById('mobile-overlay');

        if (!mobileMenu || !mobileOverlay) return;
        mobileMenu.classList.remove('hidden');
        mobileOverlay.classList.remove('hidden');
        void mobileMenu.offsetHeight;
        mobileOverlay.classList.remove('opacity-0');
        mobileMenu.classList.remove('translate-x-full');
        mobileMenu.classList.add('translate-x-0');
        document.body.classList.add('overflow-hidden');
    };

    window.closeMobileMenu = function () {
        var mobileMenu = document.getElementById('mobile-menu');
        var mobileOverlay = document.getElementById('mobile-overlay');

        if (!mobileMenu || !mobileOverlay) return;
        mobileOverlay.classList.add('opacity-0');
        mobileMenu.classList.remove('translate-x-0');
        mobileMenu.classList.add('translate-x-full');
        document.body.classList.remove('overflow-hidden');
        setTimeout(function () {
            mobileOverlay.classList.add('hidden');
            mobileMenu.classList.add('hidden');
        }, 300);
    };

    var mobileBtnEl = document.getElementById('mobile-menu-btn');
    if (mobileBtnEl) {
        mobileBtnEl.addEventListener('click', function (e) { e.stopPropagation(); window.openMobileMenu(); });
    }

    var overlayEl = document.getElementById('mobile-overlay');
    if (overlayEl) {
        overlayEl.addEventListener('click', function (e) {
            if (e.target === overlayEl) window.closeMobileMenu();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') window.closeMobileMenu();
    });

    // Confirm delete handler
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Image preview before upload
    const imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
    imageInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const previewId = this.dataset.preview;
            if (!previewId) return;
            const preview = document.getElementById(previewId);
            if (!preview) return;
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Skeleton loader for featured images
    document.querySelectorAll('img[data-skeleton]').forEach(function (img) {
        var skeleton = img.parentElement.querySelector('.skeleton');
        if (!skeleton) return;
        function loaded() {
            skeleton.classList.add('skeleton-loaded');
            img.classList.remove('opacity-0');
            setTimeout(function () { skeleton.style.display = 'none'; }, 500);
        }
        if (img.complete && img.naturalWidth > 0) {
            loaded();
        } else {
            img.addEventListener('load', loaded);
            img.addEventListener('error', function () {
                skeleton.classList.add('skeleton-loaded');
                setTimeout(function () { skeleton.style.display = 'none'; }, 500);
            });
        }
    });
});
