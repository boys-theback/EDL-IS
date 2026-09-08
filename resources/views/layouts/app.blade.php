<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'EDL Management System' }} · External Dynamic List</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        @auth
            <aside class="sidebar">
                <div class="sidebar-logo"><div class="dswd-brand"><img src="{{ asset('dswd-logo-transparent.png') }}" alt="DSWD logo"><span>EDL Management System</span></div><small>External Dynamic List</small></div>
                <nav class="side-nav">
                    <div class="nav-group"><span class="nav-label">Workspace</span><a class="side-link {{ request()->routeIs('dashboard', 'whitelist.*') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">⌁</span>Whitelist entries</a></div>
                    @if (auth()->user()->isSuperAdmin())
                        <div class="nav-group"><span class="nav-label">Administration</span><a class="side-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}"><span class="nav-icon">♙</span>Users</a><a class="side-link {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}" href="{{ route('admin.audit-logs') }}"><span class="nav-icon">◷</span>Audit log</a></div>
                    @endif
                </nav>
                <div class="sidebar-foot">
                    <div class="user-chip"><div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit">Sign out <span>↗</span></button></form>
                </div>
            </aside>
        @endauth
        <main class="main-content @guest guest-main @endguest">
            @auth
                <header class="app-toolbar"><div class="toolbar-start"><span class="toolbar-menu">☰</span><div class="toolbar-identity"><span class="toolbar-crumb">EDL Management System <b>/</b> {{ request()->routeIs('dashboard') ? 'Whitelist entries' : (request()->routeIs('admin.*') ? 'Administration' : 'Workspace') }}</span></div></div><div class="toolbar-end"><label class="toolbar-search"><span>⌕</span><input placeholder="Search" aria-label="Search"></label><span class="toolbar-divider"></span><details class="profile-menu"><summary class="toolbar-user"><span class="toolbar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role === 'super_admin' ? 'Super admin' : 'Operator' }}</small></span><span class="profile-chevron">⌄</span></summary><div class="profile-dropdown"><div class="profile-heading"><span class="toolbar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span></div><div class="profile-role">{{ auth()->user()->role === 'super_admin' ? 'Super administrator' : 'EDL operator' }}</div><form method="POST" action="{{ route('logout') }}">@csrf<button class="profile-logout" type="submit"><span>↪</span> Sign out</button></form></div></details></div></header>
            @endauth
            @yield('content')
        </main>
    </div>
</body>
</html>
