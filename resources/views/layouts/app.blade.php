<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Control Panel' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <style>
        :root {
            --page-bg: #f5f7f6;
            --surface: #ffffff;
            --line: #dfe5e1;
            --soft-line: #edf0ee;
            --text: #1f2723;
            --muted: #6d7772;
            --accent: #27745f;
            --accent-dark: #1f5d4d;
            --accent-soft: #e7f3ef;
        }
        body {
            background:
                radial-gradient(circle at top left, rgba(39,116,95,.08), transparent 30rem),
                var(--page-bg);
            color: var(--text);
            font-size: .94rem;
        }
        .app-frame { min-height: 100vh; display:flex; }
        .sidebar {
            width: 220px;
            flex: 0 0 220px;
            background: rgba(255,255,255,.94);
            border-right: 1px solid var(--line);
            box-shadow: 14px 0 40px rgba(31,39,35,.045);
            backdrop-filter: blur(10px);
            transition: width .18s ease, flex-basis .18s ease;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 20;
        }
        .sidebar.collapsed {
            width: 64px;
            flex-basis: 64px;
        }
        .app-main { min-width:0; flex:1; }
        .topbar {
            height: 52px;
            background: rgba(255,255,255,.82);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 15;
            display:flex;
            align-items:center;
        }
        .page-shell { max-width: 1480px; }
        .sidebar-brand {
            min-height: 52px;
            display:flex;
            align-items:center;
            justify-content:flex-start;
            gap:.6rem;
            padding: 0 .75rem;
            font-weight: 760;
            color: var(--text);
            border-bottom: 1px solid var(--line);
        }
        .brand-mark {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display:grid;
            place-items:center;
            background: var(--accent);
            color:#fff;
            font-weight:800;
            flex: 0 0 34px;
        }
        .sidebar.collapsed .sidebar-label,
        .sidebar.collapsed .brand-text,
        .sidebar.collapsed .sidebar-user { display:none; }
        .sidebar-nav { padding:.65rem .55rem; }
        .sidebar-link {
            display:flex;
            align-items:center;
            gap:.55rem;
            border-radius: 9px;
            color: var(--muted);
            padding: .58rem .62rem;
            margin-bottom: .18rem;
            text-decoration:none;
            font-weight:650;
        }
        .sidebar-link:hover,
        .sidebar-link:focus {
            background: var(--accent-soft);
            color: var(--accent-dark);
        }
        .sidebar-icon {
            width:28px;
            flex:0 0 28px;
            text-align:center;
            font-weight:800;
        }
        .sidebar.collapsed .sidebar-link {
            justify-content:center;
            padding-left:.3rem;
            padding-right:.3rem;
        }
        .sidebar.collapsed .sidebar-brand {
            justify-content:center;
            padding-left:.3rem;
            padding-right:.3rem;
        }
        .sidebar-footer {
            padding:.65rem .55rem;
            margin-top:auto;
            border-top:1px solid var(--line);
        }
        .sidebar-shell { height:100%; display:flex; flex-direction:column; }
        .icon-button {
            width: 34px;
            height: 34px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            line-height:1;
            border-radius: 8px;
            border:1px solid var(--line);
            background:#fff;
            color:var(--text);
            padding:0;
        }
        .icon-button:hover {
            background: var(--accent-soft);
            color: var(--accent-dark);
        }
        main.page-shell { padding-top: 1rem !important; padding-bottom:1rem !important; }
        .card {
            border-radius: 8px;
            border: 1px solid var(--line);
            box-shadow: 0 14px 35px rgba(31,39,35,.055);
            background: var(--surface);
        }
        .card-body { padding: .95rem; }
        .table {
            --bs-table-border-color: var(--soft-line);
            color: var(--text);
        }
        .table thead th {
            white-space: nowrap;
            background:#eef4f1;
            color:#27312d;
            font-size:.8rem;
            font-weight: 700;
            border-bottom-color: #d9e3df;
        }
        .table td { vertical-align: middle; }
        .btn { border-radius:7px; font-weight:600; }
        .btn:not(.btn-sm) { padding:.48rem .85rem; }
        .btn-primary, .btn-success {
            background: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover, .btn-success:hover {
            background: var(--accent-dark);
            border-color: var(--accent-dark);
        }
        .btn-dark {
            background: #24332d;
            border-color: #24332d;
        }
        .btn-outline-secondary {
            border-color:#cfd6d2;
            color:#43504a;
        }
        .form-control, .form-select {
            border-radius:7px;
            border-color:#d8dedb;
            min-height: 2.45rem;
            padding-top:.38rem;
            padding-bottom:.38rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: rgba(39,116,95,.55);
            box-shadow: 0 0 0 .2rem rgba(39,116,95,.12);
        }
        .form-label {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 700;
            margin-bottom: .32rem;
        }
        .text-muted { color: var(--muted) !important; }
        .alert { border-radius: 8px; border: 0; box-shadow: 0 10px 24px rgba(31,39,35,.06); }
        .money-negative { color:#c62828; font-weight: 700; }
        .excel-input { min-width: 96px; border:1px solid transparent; background:transparent; border-radius:4px; padding:.28rem .35rem; }
        .excel-input.is-negative { color:#c62828; font-weight:700; }
        .excel-input.is-dirty { background:#fff8d8; border-color:#e3b341; }
        .excel-input:focus { background:#fff; border-color:#5b8def; box-shadow:0 0 0 .15rem rgba(91,141,239,.18); outline:0; }
        .percent-col { width: 105px; min-width: 105px; max-width: 105px; }
        .percent-input { width: 92px; min-width: 92px; }
        .auto-table { table-layout:auto; width:auto; min-width:100%; }
        .auto-table th, .auto-table td { white-space:nowrap; }
        .toolbar-input { min-width: 150px; }
        .status-pill { min-width: 64px; }
        .summary-number { font-size: 1.25rem; font-weight: 750; }
        .summary-tile {
            border: 1px solid var(--soft-line);
            border-radius: 8px;
            padding: .8rem .9rem;
            background: #fbfcfb;
            min-height: 76px;
        }
        .sticky-action { position: sticky; right: 0; background: inherit; }
        h1.h3 { font-size:1.55rem; }
        h2.h5 { font-size:1.05rem; }
        .topbar .page-shell { max-width:none; height:52px; }
        .login-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            background:
                linear-gradient(135deg, rgba(39,116,95,.12), transparent 42%),
                radial-gradient(circle at bottom right, rgba(215,185,85,.16), transparent 34rem),
                #f5f7f6;
        }
        .login-card {
            width: min(100%, 410px);
            border: 1px solid rgba(223,229,225,.95);
            border-radius: 8px;
            box-shadow: 0 26px 70px rgba(31,39,35,.13);
            background: rgba(255,255,255,.96);
        }
        .login-card .card-body { padding: 2rem; }
        .login-title {
            font-size: 1.12rem;
            font-weight: 750;
            margin-bottom: .25rem;
        }
        .login-subtitle {
            color: var(--muted);
            margin-bottom: 1.4rem;
            font-size: .9rem;
        }
        @media (max-width: 991.98px) {
            .app-frame { display:block; }
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                transform: translateX(-100%);
                transition: transform .18s ease;
            }
            .sidebar.mobile-open { transform: translateX(0); }
            .sidebar.collapsed { width:250px; flex-basis:250px; }
            .sidebar.collapsed .sidebar-label,
            .sidebar.collapsed .brand-text,
            .sidebar.collapsed .sidebar-user { display:block; }
        }
    </style>
    @stack('head')
</head>
<body>
@auth
<div class="app-frame">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-shell">
            <a class="sidebar-brand text-decoration-none" href="{{ route('dashboard') }}">
                <span class="brand-mark">CP</span>
                <span class="brand-text">Control Panel</span>
            </a>
            <nav class="sidebar-nav">
                <a class="sidebar-link" href="{{ route('dashboard') }}"><span class="sidebar-icon">D</span><span class="sidebar-label">Dashboard</span></a>
                <a class="sidebar-link" href="{{ route('matches.index') }}"><span class="sidebar-icon">M</span><span class="sidebar-label">Matches</span></a>
                <a class="sidebar-link" href="{{ route('agents.index') }}"><span class="sidebar-icon">A</span><span class="sidebar-label">Agents</span></a>
                <a class="sidebar-link" href="{{ route('settings.edit') }}"><span class="sidebar-icon">S</span><span class="sidebar-label">Settings</span></a>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user text-muted small mb-2">{{ auth()->user()->username }} - {{ strtoupper(auth()->user()->role) }}</div>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm w-100"><span class="sidebar-label">Logout</span><span class="sidebar-icon d-none">X</span></button>
                </form>
            </div>
        </div>
    </aside>
    <div class="app-main">
        <div class="topbar">
            <div class="container-fluid page-shell h-100 d-flex align-items-center justify-content-between">
                <button class="icon-button" id="sidebar-toggle" type="button" aria-label="Toggle sidebar">☰</button>
                <div class="text-muted small d-flex align-items-center gap-3 h-100">
                    <span>US {{ now(config('app.timezone'))->format('Y-m-d H:i') }}</span>
                    <span>MY {{ now('Asia/Singapore')->format('Y-m-d H:i') }}</span>
                </div>
                <div class="text-muted small d-flex align-items-center h-100">{{ auth()->user()->username }} - {{ strtoupper(auth()->user()->role) }}</div>
            </div>
        </div>
@endauth

<main class="container-fluid page-shell py-4">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    {{ $slot ?? '' }}
    @yield('content')
</main>
@auth
    </div>
</div>
@endauth

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
<script>
    document.querySelectorAll('[data-datatable]').forEach(table => new DataTable(table, { pageLength: 25, order: [] }));
    const sidebar = document.querySelector('#sidebar');
    const sidebarToggle = document.querySelector('#sidebar-toggle');
    if (sidebar && sidebarToggle) {
        const saved = localStorage.getItem('sidebar-collapsed') === '1';
        if (saved) sidebar.classList.add('collapsed');
        sidebarToggle.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 991.98px)').matches) {
                sidebar.classList.toggle('mobile-open');
                return;
            }
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
        });
    }
</script>
@stack('scripts')
</body>
</html>
