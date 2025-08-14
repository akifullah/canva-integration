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
            box-shadow: 0 2px 4px rgba(33, 112, 182, 0.03);
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

        .nav-link.active,
        .nav-link:focus,
        .nav-link:hover {
            color: #F15A23 !important;
        }

        main {
            padding-top: 2rem;
        }

        .text-cell {
            max-width: 400px;
        }

        .name-text {
            position: relative;
        }

        .name-text::after {
            content: attr(data-text);
            display: none;
            width: fit-content;
            height: auto;
            position: absolute;
            left: 0;
            bottom: 100%;
            background: #333;
            color: #fefefe;
            font-size: 12px;
            z-index: 100;
            padding: 3px;
            border: 1px solid gray;
        }

        .name-text:hover:after {
            display: block;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('logo.jpg') }}" alt="Logo" class="pulse-logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
                    @if (session('site_authenticated'))
                        <li class="nav-item">
                            <a class="nav-link  ms-2" href="{{ route('logout') }}">Logout</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  ms-2" href="#exampleModal" data-bs-toggle="modal">Update password</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  ms-2" href="{{ route('canva.auth') }}">Canva Auth</a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <form method="GET" action="{{ route('canva.fetch') }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="nav-link  ms-2" style="border:none;">Update All PDFs</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Update Username & Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                @php
                    $user = \App\Models\UserPassword::first();
                @endphp
                <div class="modal-body">
                    <form method="POST" action="{{ route('updatePassword.submit') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="{{ $user->username }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="old_password" class="form-label">Old Password</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <main>
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
