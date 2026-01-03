{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tachy - @yield('title')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
    @yield('styles')
    <style>
        :root {
        --primary-color: #2a3b90;
        --secondary-color: #52ceff;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-bg: #f8f9fa;
        --border-color: #dee2e6;
    }
        .btn-primary{
            background-color: var(--primary-color);
        }
        .btn-secondary{
            background-color: var(--secondary-color);
        }
        .navbar-top {
            background-color: #343a40;
            border-bottom: 1px solid #495057;
            padding: 0.75rem 1rem;
        }
        .navbar-top .navbar-brand {
            color: #fff;
            font-weight: 600;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .main-content {
            background-color: #f8f9fa;
        }
        .table-responsive {
            font-size: 0.9rem;
        }
        .user-info {
            color: #fff;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-info .badge {
            font-size: 0.85rem;
        }
        .dropdown-toggle::after {
            margin-left: 0.5rem;
        }
        .navbar-site-select {
            background-color: #495057;
            border: 1px solid #6c757d;
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .navbar-site-select:focus {
            background-color: #495057;
            border-color: #6c757d;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
        }
        .navbar-site-select option {
            background-color: #343a40;
            color: #fff;
        }
    </style>

</head>
<body>
    @auth
    <!-- Navbar Top -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-tachometer-alt me-2"></i>Tachy App
            </a>
            <div class="ms-auto d-flex align-items-center">
                <div class="user-info">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ auth()->user()->nom ?? 'Utilisateur' }}</span>
                        @php
                            $currentSite = session('site');
                            $sites = ['ALG' => 'Alger', 'ORN' => 'Oran', 'CST' => 'Constantine', 'SBA' => 'Sidi Bel Abbès', 'ORG' => 'Ouargla', 'STF' => 'Sétif', 'MGM' => 'Mostaganem'];
                            $siteLabel = $sites[$currentSite] ?? $currentSite ?? 'Non défini';
                        @endphp
                        @if(auth()->user()->profil === 'ADMIN' || auth()->user()->profil === 'superadmin')
                            <form action="{{ route('site.set') }}" method="POST" class="d-inline">
                                @csrf
                                <select name="site" class="navbar-site-select" onchange="this.form.submit()" style="width: auto; display: inline-block; cursor: pointer;">
                                    @foreach($sites as $code => $label)
                                        <option value="{{ $code }}" {{ $currentSite === $code ? 'selected' : '' }}>{{ $label }} ({{ $code }})</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <span class="badge bg-info">{{ $siteLabel }} ({{ $currentSite ?? '-' }})</span>
                        @endif
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link text-white text-decoration-none dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    @auth
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3">Menu</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('conducteurs.index') }}">
                                <i class="fas fa-users me-2"></i>Conducteurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('courses.index') }}">
                                <i class="fas fa-route me-2"></i>Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('enveloppes.index') }}">
                                <i class="fas fa-map me-2"></i>Enveloppes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('exces.index') }}">
                                <i class="fas fa-exclamation-triangle me-2"></i>Excès
                            </a>
                        </li>
                        @if(in_array(auth()->user()->profil, ['DG', 'managerR', 'ADMIN', 'superadmin']) ||
                            in_array(auth()->user()->matricule, ['310040', '310020']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="statistiquesDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Statistiques
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="statistiquesDropdown">
                                <li><a class="dropdown-item" href="{{ route('statistiques.categories') }}">Répartition par catégorie</a></li>
                                <li><a class="dropdown-item" href="{{ route('statistiques.evolution') }}">Évolution par excès</a></li>
                                <li><a class="dropdown-item" href="{{ route('statistiques.conducteurs') }}">Répartition par conducteur</a></li>
                                <li><a class="dropdown-item" href="{{ route('statistiques.interstations') }}">Répartition par inter-station</a></li>
                                <li><a class="dropdown-item" href="{{ route('statistiques.mensuelle') }}">Synthèse par mois</a></li>

                                @if(in_array(auth()->user()->matricule, ['310040', '310020']) ||
                                    auth()->user()->profil === 'DG' ||
                                    auth()->user()->profil === 'managerR')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('statistiques.tous-exces') }}">Tous les excès</a></li>
                                @endif

                                @if(in_array(auth()->user()->matricule, ['310040', '310020']))
                                <li><a class="dropdown-item" href="{{ route('statistiques.journal') }}">Journal</a></li>
                                @endif
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="statistiquesDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Stat-Freinages
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="statistiquesDropdown">
                                <li><a class="dropdown-item" href="{{ route('stat-freinages.categories') }}">Répartition par catégorie</a></li>
                                <li><a class="dropdown-item" href="{{ route('stat-freinages.evolution') }}">Évolution par excès</a></li>
                                <li><a class="dropdown-item" href="{{ route('stat-freinages.conducteurs') }}">Répartition par conducteur</a></li>
                                <li><a class="dropdown-item" href="{{ route('stat-freinages.interstations') }}">Répartition par inter-station</a></li>
                                
                                @if(in_array(auth()->user()->matricule, ['310040', '310020']) ||
                                    auth()->user()->profil === 'DG' ||
                                    auth()->user()->profil === 'managerR')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('statistiques.tous-exces') }}">Tous les excès</a></li>
                                @endif

                                @if(in_array(auth()->user()->matricule, ['310040', '310020']))
                                <li><a class="dropdown-item" href="{{ route('statistiques.journal') }}">Journal</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('title')</h1>
                    <div class="d-flex align-items-center gap-3">
                        <div class="btn-toolbar mb-2 mb-md-0">
                            @yield('actions')
                        </div>
                    </div>
                </div>

                <!-- Messages flash -->
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
            </main>
        </div>
    </div>
    @else
        @yield('content')
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
