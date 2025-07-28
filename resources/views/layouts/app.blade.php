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
            width: 230px;
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
            <img src="{{ asset('logo.jpg') }}" alt="Logo" class="pulse-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ route('canva.index') }}">Designs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('canva.create') }}">Add Design</a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link  ms-2" href="{{ route('canva.auth') }}">Canva Auth</a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('canva.fetch') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="nav-link  ms-2" style="border:none;">Re-Convert All PDFs</button>
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