{{-- resources/views/admin/partials/scripts.blade.php --}}
<script>
    /**
     * Update jam digital secara real-time
     */
    function updateClock() {
        const now = new Date();
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.innerText = now.toLocaleTimeString('id-ID');
        }
    }

    /**
     * Profile Dropdown Function
     */
    function initProfileDropdown() {
        const profileBtn = document.getElementById('profileBtn');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const logoutBtn = document.getElementById('logoutDropdownBtn');
        const logoutForm = document.getElementById('logoutForm');

        if (profileBtn && dropdownMenu) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });
        }

        if (logoutBtn && logoutForm) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                    logoutForm.submit();
                }
            });
        }

        document.addEventListener('click', (e) => {
            if (dropdownMenu && profileBtn && !profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && dropdownMenu && dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
            }
        });
    }

    /**
     * Mobile menu handler
     */
    function initMobileMenu() {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function checkMobileMenuVisibility() {
            if (mobileMenuBtn) {
                if (window.innerWidth <= 1024) {
                    mobileMenuBtn.style.display = 'block';
                } else {
                    mobileMenuBtn.style.display = 'none';
                    if (sidebar) sidebar.classList.remove('mobile-open');
                    if (backdrop) backdrop.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            }
        }

        if (mobileMenuBtn && sidebar) {
            checkMobileMenuVisibility();

            mobileMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('mobile-open');
                if (backdrop) backdrop.classList.toggle('active');
                if (sidebar.classList.contains('mobile-open')) {
                    document.body.classList.add('sidebar-open');
                } else {
                    document.body.classList.remove('sidebar-open');
                }
            });

            if (backdrop) {
                backdrop.addEventListener('click', () => {
                    sidebar.classList.remove('mobile-open');
                    backdrop.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                });
            }

            window.addEventListener('resize', () => {
                checkMobileMenuVisibility();
                if (window.innerWidth > 1024) {
                    sidebar.classList.remove('mobile-open');
                    if (backdrop) backdrop.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            });
        }
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-20 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transition-all transform translate-x-0 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            'bg-blue-500'
        } text-white font-semibold text-sm`;

        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Initialize global functions
    document.addEventListener('DOMContentLoaded', () => {
        updateClock();
        setInterval(updateClock, 1000);
        initMobileMenu();
        initProfileDropdown();
    });
</script>