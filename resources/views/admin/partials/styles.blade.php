{{-- resources/views/admin/partials/styles.blade.php --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    /* Base Styles */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
        background: #f8fafc;
    }

    /* Sidebar Styles */
    .sidebar {
        background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 16rem;
        overflow-y: auto;
        z-index: 50;
    }

    .sidebar::-webkit-scrollbar { width: 6px; }
    .sidebar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.1); }
    .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.3); border-radius: 3px; }

    /* Logo Container */
    .logo-container {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 12px;
    }

    /* Menu Styles */
    .nav-item { margin-bottom: 0.5rem; }
    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        color: white;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        margin-left: -3px;
    }
    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        padding-left: 1.1rem;
    }
    .active-menu {
        background-color: rgba(255, 255, 255, 0.2);
        border-left: 3px solid #60a5fa;
        margin-left: 0;
        padding-left: 1rem;
        font-weight: 500;
    }

    /* Dashboard Layout */
    .dashboard-content {
        background-color: #f8fafc;
        margin-left: 16rem;
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Header Sticky */
    .header-sticky {
        position: sticky;
        top: 0;
        z-index: 40;
        background: white;
        flex-shrink: 0;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Main Content Scroll */
    .main-scroll {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        -webkit-overflow-scrolling: touch;
    }

    /* Profile Dropdown */
    .profile-dropdown { position: relative; cursor: pointer; }
    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 0.5rem;
        width: 240px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        z-index: 50;
        display: none;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .dropdown-menu.show { display: block; animation: fadeIn 0.2s ease; }
    .dropdown-header { padding: 1rem; border-bottom: 1px solid #e5e7eb; background: #f8fafc; }
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: #374151;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }
    .dropdown-item:hover { background-color: #f1f5f9; }
    .dropdown-item i { width: 20px; color: #6b7280; }
    .dropdown-divider { height: 1px; background-color: #e5e7eb; margin: 0.25rem 0; }
    .logout-item { color: #dc2626; }
    .logout-item i { color: #dc2626; }
    .logout-item:hover { background-color: #fef2f2; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: #1e40af;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem;
        cursor: pointer;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .dashboard-content { margin-left: 0; }
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .sidebar.mobile-open { transform: translateX(0); }
        .mobile-menu-btn { display: block !important; }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .sidebar-backdrop.active { display: block; }
        body.sidebar-open { overflow: hidden; }
        .header-sticky { padding-left: 4.5rem !important; }
        .main-scroll { padding: 1rem; }
        .dropdown-menu { width: 200px; right: -10px; }
    }
    @media (min-width: 1025px) { .mobile-menu-btn { display: none !important; } }
</style>