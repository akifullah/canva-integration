<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canva Integration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fff;
        }
        .navbar-custom {
            background: #fff;
            border-bottom: 4px solid #E5E5E5;
            box-shadow: 0 2px 4px rgba(33,112,182,0.03);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: bold;
            font-size: 1.5rem;
        }
        .pulse-logo {
            width: 48px;
            height: 48px;
            margin-right: 10px;
        }
        .pulse-text {
            color: #2170B6;
        }
        .conferences-text {
            color: #F15A23;
        }
        .nav-link {
            color: #2170B6 !important;
            font-weight: 500;
        }
        .nav-link.active, .nav-link:focus, .nav-link:hover {
            color: #F15A23 !important;
        }
        main {
            padding-top: 2rem;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <!-- Logo SVG Placeholder -->
            <svg class="pulse-logo" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="30" cy="30" r="28" stroke="#2170B6" stroke-width="4" fill="#fff"/>
                <path d="M10 40 Q30 10 50 40" stroke="#F15A23" stroke-width="4" fill="none"/>
                <path d="M15 35 Q30 20 45 35" stroke="#2170B6" stroke-width="2" fill="none"/>
            </svg>
            <span class="pulse-text">PULSE</span> <span class="conferences-text ms-1">CONFERENCES</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('canva.index') }}">Designs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('canva.create') }}">Add Design</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-primary ms-2" href="{{ route('canva.auth') }}">Canva Auth</a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('canva.fetch') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="nav-link btn btn-outline-success ms-2" style="border:none;">Fetch Latest Designs</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main>
    @yield('content')
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 