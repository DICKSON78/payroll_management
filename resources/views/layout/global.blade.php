<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TanzaniaPay - @yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Tailwind CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * { font-family: 'Poppins', sans-serif; }
        .sidebar { background: linear-gradient(135deg, #1a365d 0%, #153e75 100%); transition: width 0.3s ease-in-out; width: 256px; }
        .sidebar.collapsed { width: 64px; }
        .sidebar.collapsed .sidebar-text { display: none; }
        .sidebar.collapsed .sidebar-link { justify-content: center; padding: 0.75rem 0; }
        .sidebar-text { visibility: visible; opacity: 1; transition: visibility 0s linear 0.2s, opacity 0.2s ease-in-out 0.2s; animation: fadeIn 0.2s ease-in-out; }
        .sidebar-link { transition: all 0.2s; padding: 0.75rem 1rem; border-radius: 0.375rem; display: flex; align-items: center; }
        .sidebar-link:hover { background-color: rgba(255, 255, 255, 0.1); }
        .sidebar-link.active { background: linear-gradient(135deg, #10a37f 0%, #1a7f64 100%); }
        .sidebar.collapsed .sidebar-link.active { border-radius: 0.375rem; width: 48px; margin: 0 auto; }
        .card { transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); background: white; border-radius: 0.75rem; padding: 1.5rem; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .btn-primary { background: linear-gradient(135deg, #10a37f 0%, #1a7f64 100%); color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .status-badge { display: inline-flex; align-items: center; padding: 0.4em 0.8em; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; line-height: 1; }
        .status-pending { background-color: #fffbeb; color: #f59e0b; }
        .status-paid { background-color: #d1fae5; color: #10b981; }
        .status-processing { background-color: #dbeafe; color: #3b82f6; }
        .payroll-badge { background: linear-gradient(135deg, #10a37f 0%, #1a7f64 100%); color: white; }
        .stat-card-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .nav-item { transition: all 0.2s; }
        .nav-item:hover { background-color: #f3f4f6; border-radius: 0.5rem; }
        .header { background: #ffffff; border-bottom: 1px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); padding: 1rem 2rem; }
        .main-content { transition: margin-left 0.3s ease-in-out; }
        .main-content.collapsed { margin-left: 64px; }
        .modal-content { transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out; }
        #addEmployeeModal:not(.hidden) .modal-content, #editEmployeeModal:not(.hidden) .modal-content,
        #deactivateConfirmModal:not(.hidden) .modal-content, #deleteConfirmModal:not(.hidden) .modal-content,
        #viewEmployeeModal:not(.hidden) .modal-content { transform: scale(1); opacity: 1; }
        #addEmployeeModal.hidden .modal-content, #editEmployeeModal.hidden .modal-content,
        #deactivateConfirmModal.hidden .modal-content, #deleteConfirmModal.hidden .modal-content,
        #viewEmployeeModal.hidden .modal-content { transform: scale(0.95); opacity: 0; }
        .notification-container { position: fixed; top: 20px; right: 20px; z-index: 1000; width: 320px; }
        .notification { background: white; border-radius: 8px; padding: 16px; margin-bottom: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); display: flex; align-items: center; animation: slideIn 0.3s ease-in-out; }
        .notification.success { border-left: 4px solid #10b981; }
        .notification.error { border-left: 4px solid #ef4444; }
        .notification .close-btn { margin-left: auto; cursor: pointer; color: #6b7280; }
        .logo-container { width: 100%; display: flex; align-items: center; justify-content: center; padding: 8px 16px; }
        .logo-container img { width: 100%; max-height: 64px; object-fit: contain; }
        .sidebar.collapsed .logo-container { padding: 8px; }
        .sidebar.collapsed .logo-container img { max-height: 48px; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeIn { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
        @media (max-width: 768px) {
            .sidebar { width: 256px; transform: translateX(-100%); position: fixed; z-index: 50; height: 100vh; }
            .sidebar.active { transform: translateX(0); }
            .sidebar.collapsed { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .main-content.collapsed { margin-left: 0; }
            .notification-container { width: 90%; right: 5%; }
            .logo-container { padding: 8px; }
            .logo-container img { max-height: 48px; }
        }
        /* Native date input styling to match design */
        input[type="date"], input[type="month"], input[type="number"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 100%;
            min-width: 10rem;
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.875rem;
            color: #374151;
            background-color: #fff;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        input[type="date"]:focus, input[type="month"]:focus, input[type="number"]:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="month"]::-webkit-calendar-picker-indicator {
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'/%3E%3C/svg%3E") no-repeat center;
            background-size: 1.5rem;
            width: 1.5rem;
            height: 1.5rem;
            cursor: pointer;
            opacity: 0.6;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="month"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .flatpickr-input[readonly] {
            background-color: #f9fafb;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <div class="sidebar text-white p-6 flex flex-col fixed h-full" id="sidebar">
            <div class="flex items-center mb-10 justify-center">
                <div class="logo-container">
                    <img src="{{ asset('assets/banner.jpg') }}" alt="TanzaniaPay Logo" class="h-10 w-auto">
                </div>
            </div>
            <nav class="flex-1">
                <ul class="space-y-2">
                    @php $userRole = Auth::user(); @endphp

                    @if($userRole && in_array(strtolower($userRole->role), ['admin', 'hr manager']))
                        {{-- Admin / HR Links --}}
                        <li><a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt mr-3"></i><span class="sidebar-text">Dashboard</span></a></li>
                        <li><a href="{{ route('employees.index') }}" class="sidebar-link {{ request()->routeIs('employees*') ? 'active' : '' }}"><i class="fas fa-userRoles mr-3"></i><span class="sidebar-text">Employees</span></a></li>
                        <li><a href="{{ route('payroll') }}" class="sidebar-link {{ request()->routeIs('payroll*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar mr-3"></i><span class="sidebar-text">Payroll</span></a></li>
                        <li><a href="{{ route('reports') }}" class="sidebar-link {{ request()->routeIs('reports*') ? 'active' : '' }}"><i class="fas fa-chart-bar mr-3"></i><span class="sidebar-text">Reports</span></a></li>
                        <li><a href="{{ route('compliance.index') }}" class="sidebar-link {{ request()->routeIs('compliance*') ? 'active' : '' }}"><i class="fas fa-shield-alt mr-3"></i><span class="sidebar-text">Compliance</span></a></li>
                        <li><a href="{{ route('dashboard.attendance') }}" class="sidebar-link {{ request()->routeIs('dashboard.attendance') ? 'active' : '' }}"><i class="fas fa-clock mr-3"></i><span class="sidebar-text">Attendance</span></a></li>
                        <li><a href="{{ route('employee.portal') }}" class="sidebar-link {{ request()->routeIs('employee.portal') ? 'active' : '' }}"><i class="fas fa-userRole-circle mr-3"></i><span class="sidebar-text">Employee Portal</span></a></li>
                        <li><a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings*') ? 'active' : '' }}"><i class="fas fa-cog mr-3"></i><span class="sidebar-text">Settings</span></a></li>
                    @elseif($userRole)
                        {{-- Employee Links --}}
                        <li><a href="{{ route('portal.attendance') }}" class="sidebar-link {{ request()->routeIs('portal.attendance') ? 'active' : '' }}"><i class="fas fa-clock mr-3"></i><span class="sidebar-text">My Attendance</span></a></li>
                        <li><a href="{{ route('employee.portal') }}" class="sidebar-link {{ request()->routeIs('employee.portal') ? 'active' : '' }}"><i class="fas fa-userRole-circle mr-3"></i><span class="sidebar-text">Employee Portal</span></a></li>
                    @endif
                </ul>
            </nav>
        </div>

        <main class="ml-64 flex-1 overflow-y-auto main-content" id="main-content">
            <header class="header flex justify-between items-center">
                <div class="flex items-center">
                    <button id="toggleSidebar" aria-label="Toggle Sidebar" class="text-gray-600 hover:text-gray-800 mr-4 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">@yield('header-title')</h2>
                        <p class="text-sm text-gray-600">@yield('header-subtitle')</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button id="notificationToggle" class="fas fa-bell text-gray-500 text-xl focus:outline-none"></button>
                        <span id="notificationDot" class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full hidden"></span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ \Carbon\Carbon::now()->format('l, F d, Y') }}
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center mr-2">
                            <i class="fas fa-userRole text-white"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $userRole ? $userRole->name : 'Guest' }}</p>
                            <p class="text-xs text-gray-500">{{ $userRole ? ucfirst($userRole->role ?? 'Employee') : 'Guest' }}</p>
                        </div>
                    </div>
                    @if($userRole)
                        <a href="#"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="text-gray-600 hover:text-green-600 p-2 rounded-full hover:bg-gray-100 transition">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-gray-600 hover:text-green-600 p-2 rounded-full hover:bg-gray-100 transition">
                            <i class="fas fa-sign-in-alt"></i>
                        </a>
                    @endif
                </div>
            </header>

            <div class="notification-container" id="notificationContainer"></div>

            <div class="p-8">
                @yield('content')
            </div>
        </main>
    </div>

    @yield('modals')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleButton = document.getElementById('toggleSidebar');
            const toggleIcon = toggleButton.querySelector('i');
            const notificationToggle = document.getElementById('notificationToggle');
            const notificationDot = document.getElementById('notificationDot');
            const notificationContainer = document.getElementById('notificationContainer');

            // Sidebar toggle
            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('collapsed');

                if (sidebar.classList.contains('collapsed')) {
                    toggleIcon.classList.remove('fa-times');
                    toggleIcon.classList.add('fa-bars');
                    sidebar.classList.remove('active');
                } else {
                    toggleIcon.classList.remove('fa-bars');
                    toggleIcon.classList.add('fa-times');
                    if (window.innerWidth <= 768) {
                        sidebar.classList.add('active');
                    }
                }
            });

            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('active');
                mainContent.classList.add('collapsed');
            }

            // Notification system
            let notifications = [];

            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <span>${message}</span>
                    <span class="close-btn fas fa-times"></span>
                `;
                notificationContainer.appendChild(notification);

                notifications.push(notification);

                setTimeout(() => {
                    notification.style.animation = 'slideOut 0.3s ease-in-out';
                    setTimeout(() => {
                        notification.remove();
                        notifications = notifications.filter(n => n !== notification);
                        updateNotificationDot();
                    }, 300);
                }, 5000);

                const closeBtn = notification.querySelector('.close-btn');
                closeBtn.addEventListener('click', () => {
                    notification.style.animation = 'slideOut 0.3s ease-in-out';
                    setTimeout(() => {
                        notification.remove();
                        notifications = notifications.filter(n => n !== notification);
                        updateNotificationDot();
                    }, 300);
                });

                updateNotificationDot();
            }

            function updateNotificationDot() {
                notificationDot.classList.toggle('hidden', notifications.length === 0);
            }

            notificationToggle.addEventListener('click', () => {
                if (notifications.length === 0) {
                    showNotification('No new notifications', 'success');
                }
            });

            // Check if welcome notification has been shown
            @if($userRole)
                const hasSeenWelcome = sessionStorage.getItem('hasSeenWelcome');
                if (!hasSeenWelcome) {
                    showNotification('Welcome to TanzaniaPay!', 'success');
                    sessionStorage.setItem('hasSeenWelcome', 'true');
                }
            @endif

            // Add slideOut animation
            const styleSheet = document.createElement('style');
            styleSheet.textContent = `
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(styleSheet);
        });
    </script>
</body>
</html>