<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Setting::get('app_name', env('APP_NAME', 'SOF2Panel')) }} - @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand-title" style="margin:0; font-size: 1.5rem;">{{ \App\Models\Setting::get('app_name', env('APP_NAME', 'SOF2Panel')) }}</h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i data-feather="grid"></i> Dashboard
                </a>
                
                <a href="{{ route('servers.index') }}" class="nav-item {{ request()->routeIs('servers.*') ? 'active' : '' }}">
                    <i data-feather="server"></i> Servers
                </a>

                <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                    <i data-feather="life-buoy"></i> Support
                </a>

                @if(auth()->user()->isAdmin())
                <a href="{{ route('hosts.index') }}" class="nav-item {{ request()->routeIs('hosts.*') ? 'active' : '' }}">
                    <i data-feather="hard-drive"></i> Hosts
                </a>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i data-feather="users"></i> Users
                </a>
                <a href="{{ route('games.index') }}" class="nav-item {{ request()->routeIs('games.*') ? 'active' : '' }}">
                    <i data-feather="cpu"></i> Games
                </a>
                <a href="{{ route('api-keys.index') }}" class="nav-item {{ request()->routeIs('api-keys.*') ? 'active' : '' }}">
                    <i data-feather="key"></i> API Keys
                </a>
                <a href="{{ route('logs.index') }}" class="nav-item {{ request()->routeIs('logs.*') ? 'active' : '' }}">
                    <i data-feather="activity"></i> Logs
                </a>
                <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i data-feather="settings"></i> Settings
                </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="avatar">{{ substr(auth()->user()->username ?? 'U', 0, 1) }}</div>
                    <div class="user-details">
                        <span class="user-name">{{ auth()->user()->username ?? 'Unknown User' }}</span>
                        <span class="user-role">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout">
                        <i data-feather="log-out"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i data-feather="menu"></i>
                </button>
                <div class="page-title">
                    <h2>@yield('title', 'Dashboard')</h2>
                </div>
                
                <a href="https://github.com/belalgalhom/SoF2Panel" target="_blank" style="margin-left: auto; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='var(--text-muted)'">
                    <i data-feather="github" style="width: 18px; height: 18px;"></i>
                    <span class="hide-mobile">GitHub</span>
                </a>
            </header>

            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>
    
    <script>
        feather.replace();

        // Mobile Menu Logic
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.querySelector('.sidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');

        function toggleMenu() {
            sidebar.classList.toggle('open');
            mobileOverlay.classList.toggle('active');
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleMenu);
        }
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', toggleMenu);
        }

        function showToast(message, type = 'success') {
            Toastify({
                text: message,
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
                style: {
                    background: type === 'success' ? "var(--success)" : "var(--danger)",
                    color: "#fff",
                    borderRadius: "8px",
                    padding: "12px 24px",
                    boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                    fontFamily: "'Inter', sans-serif"
                },
                escapeMarkup: false
            }).showToast();
        }

        @if(session('success'))
            showToast("{{ addslashes(session('success')) }}", 'success');
        @endif
        @if(session('error'))
            showToast("{{ addslashes(session('error')) }}", 'error');
        @endif
    </script>
</body>
</html>
