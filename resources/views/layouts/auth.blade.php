<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Connexion') - Dorcas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2b3593 0%, #2dc4ea 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-container {
            background: white;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(43, 53, 147, 0.28);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .auth-header {
            background: #2b3593;
            color: #fff;
            padding: 2rem;
            text-align: center;
            border-bottom: 6px solid #fdcf41;
        }
        .auth-logo {
            display: block;
            width: min(180px, 70%);
            height: auto;
            margin: 0 auto 1rem;
            background: #fff;
            border-radius: .5rem;
            padding: .5rem .75rem;
        }
        .auth-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .auth-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #2dc4ea;
            box-shadow: 0 0 0 0.2rem rgba(45, 196, 234, 0.25);
        }
        .btn-primary {
            background: #2b3593;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            background: #2dc4ea;
            box-shadow: 0 5px 15px rgba(45, 196, 234, 0.4);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus + .input-group-text,
        .form-control:focus {
            border-color: #2dc4ea;
        }
        .auth-footer {
            text-align: center;
            padding: 1rem 2rem 2rem;
            color: #6c757d;
        }
        .auth-footer a {
            color: #2b3593;
            text-decoration: none;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img class="auth-logo" src="{{ asset('images/logosetram.png') }}" alt="Logo SETRAM">
            <h2>Dorcas</h2>
            <p class="mb-0 mt-2">@yield('header-text', 'Connexion à votre compte')</p>
        </div>

        <div class="auth-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <div class="auth-footer">
            @yield('footer')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>




