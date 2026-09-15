<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mwana - School Management Platform')</title>
    <meta name="description" content="Mwana is a multi-school platform connecting principals, teachers and parents around results, fees visibility and school schedules.">
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
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: var(--ink);
            background: var(--mc-bg);
            line-height: 1.6;
        }
        h1, h2, h3, .brand { font-family: 'Poppins', sans-serif; font-weight: 700; }
        a { color: inherit; }
        .container { max-width: 1080px; margin: 0 auto; padding: 0 24px; }
        header.site {
            background: var(--mc-blue-dark);
            color: #fff;
            padding: 18px 0;
        }
        header.site .container { display: flex; justify-content: space-between; align-items: center; }
        .brand { font-size: 1.4rem; font-weight: 800; color: #fff; text-decoration: none; }
        .brand span { color: var(--mc-green-light); }
        nav.site a {
            text-decoration: none;
            color: #fff;
            margin-left: 20px;
            font-weight: 500;
            font-size: 0.95rem;
        }
        nav.site a.cta {
            background: #fff;
            color: var(--mc-blue-dark);
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 700;
            transition: transform 0.15s ease;
            display: inline-block;
        }
        nav.site a.cta:hover { transform: translateY(-1px); }
        footer.site {
            background: var(--mc-blue-dark);
            color: #cfe0ee;
            padding: 32px 0;
            margin-top: 60px;
            font-size: 0.9rem;
        }
        footer.site a { color: var(--mc-green-light); text-decoration: none; }
    </style>
    @stack('styles')
</head>
<body>
    <header class="site">
        <div class="container">
            <a href="{{ url('/') }}" class="brand">Mwana<span>.</span></a>
            <nav class="site">
                <a href="{{ url('/#how-it-works') }}">How it works</a>
                <a href="{{ url('/login') }}" class="cta">Log in</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site">
        <div class="container">
            <p>Mwana &mdash; built by CLOUDP TECH for schools across Kenya.</p>
            <p>Questions? Reach out to your school's administration office.</p>
        </div>
    </footer>
</body>
</html>
