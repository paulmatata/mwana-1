@extends('layouts.app')

@section('title', 'Mwana - One platform for schools, teachers and parents')

@push('styles')
<style>
    .hero {
        background: linear-gradient(145deg, var(--mc-blue) 0%, var(--mc-blue) 55%, var(--mc-green) 55%, var(--mc-green) 100%);
        color: #fff;
        padding: 72px 0 88px;
    }
    .hero h1 { font-size: 2.6rem; margin-bottom: 16px; max-width: 720px; font-weight: 800; }
    .hero p.lead { font-size: 1.15rem; max-width: 600px; color: #eaf1f7; margin-bottom: 32px; }
    .hero .btn {
        display: inline-block;
        background: #fff;
        color: var(--mc-blue-dark);
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        font-size: 1rem;
        transition: transform 0.15s ease;
    }
    .hero .btn:hover { transform: translateY(-2px); }
    .hero .btn.secondary {
        background: transparent;
        border: 2px solid #fff;
        color: #fff;
        margin-left: 12px;
    }
    section.explain { padding: 64px 0; }
    section.explain h2 { font-size: 1.8rem; margin-bottom: 8px; }
    section.explain p.subtitle { color: #5a7185; margin-bottom: 40px; max-width: 640px; }
    .roles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
    }
    .role-card {
        background: #fff;
        border: 1px solid var(--mc-border);
        border-radius: 16px;
        padding: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .role-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(11,61,105,0.08); }
    .role-card .tag {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--mc-green);
        margin-bottom: 8px;
    }
    .role-card h3 { margin: 0 0 8px; font-size: 1.15rem; }
    .role-card p { margin: 0; font-size: 0.95rem; color: #52697d; }

    section.parent-flow {
        background: #fff;
        padding: 64px 0;
        border-top: 1px solid var(--mc-border);
        border-bottom: 1px solid var(--mc-border);
    }
    .steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 32px;
    }
    .step {
        position: relative;
        padding: 28px 20px 20px;
        background: var(--mc-bg);
        border-radius: 14px;
        border: 1px solid var(--mc-border);
    }
    .step .num {
        position: absolute;
        top: -14px;
        left: 20px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--mc-blue);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
    }
    .step h4 { margin: 8px 0 8px; font-size: 1rem; }
    .step p { margin: 0; font-size: 0.9rem; color: #52697d; }

    .note {
        background: #fff8e1;
        border-left: 4px solid var(--mc-warning);
        padding: 16px 20px;
        border-radius: 10px;
        margin-top: 32px;
        font-size: 0.92rem;
        color: #6b5300;
    }

    section.features { padding: 64px 0; }
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-top: 32px;
    }
    .feature h4 { margin: 0 0 6px; font-size: 1.05rem; }
    .feature p { margin: 0; font-size: 0.92rem; color: #52697d; }
    .feature .icon { font-size: 1.5rem; margin-bottom: 10px; }
</style>
@endpush

@section('content')

<section class="hero">
    <div class="container">
        <h1>One platform connecting every school, every classroom, every parent.</h1>
        <p class="lead">
            Mwana brings principals, teachers and parents onto a single system for managing classes,
            approving results, sharing fee information, and keeping families informed &mdash; built for
            schools across Kenya, one school at a time.
        </p>
        <a href="{{ url('/login') }}" class="btn">Log in to Mwana</a>
        <a href="#how-it-works" class="btn secondary">See how it works</a>
    </div>
</section>

<section class="explain" id="what-is-mwana">
    <div class="container">
        <h2>What is Mwana?</h2>
        <p class="subtitle">
            Mwana is a multi-school management platform. Each school on Mwana runs independently &mdash;
            its own classes, teachers, students and results &mdash; while everyone accesses it through
            one shared system.
        </p>
        <div class="roles-grid">
            <div class="role-card">
                <span class="tag">Platform Owner</span>
                <h3>Super Admin</h3>
                <p>Onboards new schools onto Mwana and sets up each school's principal account.</p>
            </div>
            <div class="role-card">
                <span class="tag">School Leadership</span>
                <h3>Principal</h3>
                <p>Sets up classes and schedules for their school, and reviews and approves results before they reach parents.</p>
            </div>
            <div class="role-card">
                <span class="tag">Classroom</span>
                <h3>Teacher</h3>
                <p>Manages students, uploads marks and class updates, and checks their own teaching timetable.</p>
            </div>
            <div class="role-card">
                <span class="tag">Family</span>
                <h3>Parent</h3>
                <p>Views their child's approved results, fee details and school updates &mdash; and nothing belonging to anyone else's child.</p>
            </div>
        </div>
    </div>
</section>

<section class="parent-flow" id="how-it-works">
    <div class="container">
        <h2>How parents use Mwana</h2>
        <p class="subtitle">
            The very first time you log in, Mwana walks you through a short, guided process. This exists
            for one reason: to make sure you only ever see your own child's information &mdash; never another
            family's.
        </p>
        <div class="steps">
            <div class="step">
                <div class="num">1</div>
                <h4>Select the school</h4>
                <p>Choose your child's school from the list of schools registered on Mwana.</p>
            </div>
            <div class="step">
                <div class="num">2</div>
                <h4>Select the class</h4>
                <p>Choose the class your child is currently in at that school.</p>
            </div>
            <div class="step">
                <div class="num">3</div>
                <h4>Enter admission number &amp; name</h4>
                <p>Enter your child's admission number and full name exactly as registered by the school.</p>
            </div>
            <div class="step">
                <div class="num">4</div>
                <h4>Set your password</h4>
                <p>Once matched, create a password for your account. Future logins are instant &mdash; no need to repeat these steps.</p>
            </div>
        </div>
        <div class="note">
            <strong>Note:</strong> if your details don't match what the school has on record, the system
            won't let you through. This is intentional &mdash; it protects every family's privacy. If you
            believe there's an error, contact your child's school directly.
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2>What you can do once logged in</h2>
        <div class="features-grid">
            <div class="feature">
                <div class="icon">📄</div>
                <h4>View approved results</h4>
                <p>See your child's marks once the school's principal has reviewed and approved them.</p>
            </div>
            <div class="feature">
                <div class="icon">💰</div>
                <h4>Check fee details</h4>
                <p>See what's due, what's been recorded as paid, and when the school expects fees to be sent in. Mwana displays this information only &mdash; payments are made directly to the school, not through this portal.</p>
            </div>
            <div class="feature">
                <div class="icon">📢</div>
                <h4>Stay updated</h4>
                <p>Read notices and updates teachers or the school post for your child's class.</p>
            </div>
        </div>
    </div>
</section>

@endsection
