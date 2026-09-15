<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mwana Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Makueni County brand palette (blue, green, white - from the county flag),
               matching the identity already established at
               ict-centres-makueni-county.onrender.com */
            --mc-blue: #0B3D69;
            --mc-blue-dark: #072844;
            --mc-blue-light: #1D6FB8;
            --mc-green: #1B7A3D;
            --mc-green-light: #2E9E52;
            --mc-bg: #F7F9FB;
            --mc-warning: #ffc107;
            --mc-danger: #dc3545;
            --mc-border: #dde3ea;

            /* Old variable names kept as aliases so every existing view that
               references them re-themes automatically - only the values changed. */
            --savanna-green: var(--mc-blue);
            --savanna-green-dark: var(--mc-blue-dark);
            --sun-gold: var(--mc-warning);
            --laterite: var(--mc-danger);
            --cream: var(--mc-bg);
            --ink: #1C2733;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--mc-bg); color: var(--ink); }
        .shell { display: flex; min-height: 100vh; }
        aside {
            width: 240px;
            background: var(--mc-blue-dark);
            color: #fff;
            padding: 24px 0;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        aside .brand-row { display: flex; align-items: center; justify-content: space-between; padding: 0 24px 24px; }
        aside .brand { font-family: 'Poppins', sans-serif; font-size: 1.3rem; font-weight: 800; color: #fff; }
        aside .brand span { color: var(--mc-green-light); }
        #mobile-nav-toggle { display: none; }
        aside nav a {
            display: block;
            padding: 11px 24px;
            color: #c7d6e4;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        aside nav a:hover, aside nav a.active {
            background: rgba(255,255,255,0.08);
            color: #fff;
            border-left-color: var(--mc-green-light);
        }
        aside .role-tag {
            padding: 0 24px 16px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #8fa8bd;
        }
        main.content { flex: 1; min-width: 0; }
        header.topbar {
            background: #fff;
            border-bottom: 1px solid var(--mc-border);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* --- Mobile: sidebar becomes a top bar, nav collapses behind a toggle --- */
        @media (max-width: 820px) {
            .shell { flex-direction: column; }
            aside {
                width: 100%;
                height: auto;
                position: sticky;
                top: 0;
                z-index: 20;
                padding: 14px 0;
            }
            aside .brand-row { padding: 0 16px; }
            #mobile-nav-toggle {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.28);
                color: #fff;
                padding: 6px 12px;
                border-radius: 8px;
                font-size: 0.82rem;
                cursor: pointer;
            }
            aside .role-tag { padding: 0 16px 8px; }
            aside nav { display: none; max-height: 60vh; overflow-y: auto; border-top: 1px solid rgba(255,255,255,0.14); margin-top: 8px; }
            aside.nav-open nav { display: block; }
            header.topbar { padding: 14px 16px; }
            header.topbar h1 { font-size: 1.05rem; }
            .page { padding: 18px; }
        }
        header.topbar h1 { font-size: 1.2rem; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 700; }
        header.topbar form { margin: 0; }
        header.topbar button {
            background: none;
            border: 1px solid var(--mc-border);
            padding: 7px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ink);
            transition: background 0.15s ease;
        }
        header.topbar button:hover { background: #f2f5f8; }
        .page { padding: 32px; }
        .flash {
            background: #e6f4ea;
            border-left: 4px solid var(--mc-green);
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.92rem;
            color: #123d20;
        }
        .errors { background: #fbe7e9; border-left: 4px solid var(--mc-danger); padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; color: #7a1620; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 14px; overflow: hidden; }
        th, td { text-align: left; padding: 12px 16px; border-bottom: 1px solid var(--mc-border); font-size: 0.92rem; }
        th { background: #eef3f8; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; color: #345779; }
        .card { background: #fff; border: 1px solid var(--mc-border); border-radius: 18px; padding: 24px; margin-bottom: 24px; transition: box-shadow 0.2s ease; }
        .btn {
            display: inline-block; background: var(--mc-blue); color: #fff; padding: 10px 20px; border-radius: 10px;
            text-decoration: none; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }
        .btn:hover { background: var(--mc-blue-light); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(11,61,105,0.18); }
        .btn.secondary { background: transparent; color: var(--mc-blue); border: 1px solid var(--mc-blue); }
        .btn.secondary:hover { background: #eaf1f7; box-shadow: none; }
        .btn.danger { background: var(--mc-danger); }
        .btn.danger:hover { background: #c62b3a; }
        .btn.small { padding: 5px 10px; font-size: 0.78rem; border-radius: 8px; }
        .btn.tiny { padding: 3px 8px; font-size: 0.72rem; border-radius: 6px; }
        .btn.tiny:hover, .btn.small:hover { transform: none; }
        td .btn, td form { margin: 0; }
        td.actions-cell { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        label { display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"], select, textarea {
            width: 100%; padding: 11px 14px; border: 1px solid var(--mc-border); border-radius: 12px; font-size: 0.92rem;
            margin-bottom: 16px; font-family: inherit; transition: border-color 0.15s ease;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--mc-blue-light); }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; }
        .status-active { background: #e6f4ea; color: var(--mc-green); }
        .status-suspended { background: #fbe7e9; color: var(--mc-danger); }
        .empty-state { text-align: center; padding: 48px 24px; color: #8496a6; }
        .mwana-pagination { display: flex; align-items: center; justify-content: center; gap: 20px; padding: 8px 0; }
        .mwana-page-link { color: var(--mc-blue); text-decoration: none; font-weight: 600; font-size: 0.88rem; padding: 6px 10px; border-radius: 8px; }
        .mwana-page-link:hover { background: #eaf1f7; }
        .mwana-page-link.disabled { color: #b7c3cd; pointer-events: none; }
        .mwana-page-info { font-size: 0.82rem; color: #8496a6; }
        /* Wide tables scroll within their own card instead of overflowing into
           a sibling column - this was the cause of the "Add a class" panel
           visually overlapping the table on the Classes page. */
        .card { overflow-x: auto; }
        table { min-width: 100%; }
    </style>
    @stack('styles')
</head>
<body>
<div class="shell">
    <aside id="sidebar">
        <div class="brand-row">
            <div class="brand">Mwana<span>.</span></div>
            <button type="button" id="mobile-nav-toggle" onclick="document.getElementById('sidebar').classList.toggle('nav-open')">☰ Menu</button>
        </div>
        <div class="role-tag">@yield('role-label', 'Dashboard')</div>
        <nav>
            @yield('nav')
        </nav>
    </aside>
    <main class="content">
        <header class="topbar">
            <h1>@yield('page-title', 'Dashboard')</h1>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </header>
        <div class="page">
            @if (session('success'))
                <div class="flash">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
@stack('scripts')
</body>
</html>
