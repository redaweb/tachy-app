{{-- resources/views/conducteurs/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestion des Conducteurs')

@section('actions')
<div class="btn-group">
    <a href="{{ route('conducteurs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau Conducteur
    </a>
    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
        <i class="fas fa-filter me-1"></i>Filtres
    </button>
</div>

<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@endsection

@section('content')
<!-- Filtres avancés -->
<div class="collapse {{ request()->has('search') || request()->has('site') || request()->has('min_courses') ? 'show' : '' }}" id="filtersCollapse">
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('conducteurs.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Matricule, nom ou prénom..."
                                   value="{{ request('search') }}">
                            @if(request('search'))
                            <a href="{{ route('conducteurs.index', array_merge(request()->except(['search', 'page']))) }}"
                               class="btn btn-outline-secondary" title="Effacer la recherche">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="site" class="form-label">Filtrer par site</label>
                        <select class="form-select" id="site" name="site" onchange="this.form.submit()">
                            <option value="">Tous les sites</option>
                            @foreach($sites as $site)
                                <option value="{{ $site }}" {{ request('site') == $site ? 'selected' : '' }}>
                                    {{ $site }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="min_courses" class="form-label">Nombre minimum de courses</label>
                        <input type="number" class="form-control" id="min_courses" name="min_courses"
                               min="0" value="{{ request('min_courses') }}"
                               placeholder="Min. courses" onchange="this.form.submit()">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        @if(request()->has('search') || request()->has('site') || request()->has('min_courses') || request()->has('sort'))
                            <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-redo-alt me-1"></i>Réinitialiser
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Champs cachés pour garder le tri -->
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                @if(request('direction'))
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Statistiques rapides -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Conducteurs</h6>
                        <h2 class="mt-2 mb-0">{{ $conducteurs->total() }}</h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Sites distincts</h6>
                        <h2 class="mt-2 mb-0">{{ $sites->count() }}</h2>
                    </div>
                    <i class="fas fa-map-marker-alt fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Moyenne courses</h6>
                        <h2 class="mt-2 mb-0">
                            {{ round($conducteurs->avg('courses_count'), 1) }}
                        </h2>
                    </div>
                    <i class="fas fa-chart-line fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Cette page</h6>
                        <h2 class="mt-2 mb-0">{{ $conducteurs->count() }}</h2>
                    </div>
                    <i class="fas fa-list fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau des conducteurs -->
<div class="card">
    <div class="card-body">
        @if($conducteurs->isEmpty())
            <div class="alert alert-info text-center py-4">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h5>Aucun conducteur trouvé</h5>
                @if(request()->has('search') || request()->has('site') || request()->has('min_courses'))
                    <p class="mb-0">Aucun résultat ne correspond à vos critères de recherche.</p>
                    <a href="{{ route('conducteurs.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-times"></i> Réinitialiser les filtres
                    </a>
                @else
                    <p class="mb-0">Commencez par ajouter un nouveau conducteur.</p>
                    <a href="{{ route('conducteurs.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Nouveau Conducteur
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <a href="{{ route('conducteurs.index', array_merge(request()->query(), ['sort' => 'matricule', 'direction' => request('sort') == 'matricule' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-white text-decoration-none">
                                    Matricule
                                    @if(request('sort') == 'matricule')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-white-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('conducteurs.index', array_merge(request()->query(), ['sort' => 'nom', 'direction' => request('sort') == 'nom' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-white text-decoration-none">
                                    Nom
                                    @if(request('sort') == 'nom')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-white-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('conducteurs.index', array_merge(request()->query(), ['sort' => 'prenom', 'direction' => request('sort') == 'prenom' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-white text-decoration-none">
                                    Prénom
                                    @if(request('sort') == 'prenom')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-white-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('conducteurs.index', array_merge(request()->query(), ['sort' => 'site', 'direction' => request('sort') == 'site' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-white text-decoration-none">
                                    Site
                                    @if(request('sort') == 'site')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-white-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('conducteurs.index', array_merge(request()->query(), ['sort' => 'courses_count', 'direction' => request('sort') == 'courses_count' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-white text-decoration-none">
                                    Nb. Courses
                                    @if(request('sort') == 'courses_count')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-white-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conducteurs as $conducteur)
                        <tr>
                            <td><strong>{{ $conducteur->matricule }}</strong></td>
                            <td>{{ $conducteur->nom }}</td>
                            <td>{{ $conducteur->prenom }}</td>
                            <td>
                                @if($conducteur->site)
                                    <span class="badge bg-secondary">{{ $conducteur->site }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $conducteur->courses_count > 0 ? 'bg-info' : 'bg-secondary' }}">
                                    {{ $conducteur->courses_count }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('conducteurs.show', $conducteur->matricule) }}"
                                       class="btn btn-info" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('conducteurs.edit', $conducteur->matricule) }}"
                                       class="btn btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('conducteurs.destroy', $conducteur->matricule) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce conducteur ?\n\nCette action supprimera également toutes ses courses associées.')"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination et informations -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Affichage de {{ $conducteurs->firstItem() }} à {{ $conducteurs->lastItem() }}
                    sur {{ $conducteurs->total() }} conducteurs
                </div>
                <div>
                    {{ $conducteurs->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .opacity-50 {
        opacity: 0.5;
    }
    .table th a:hover {
        text-decoration: underline !important;
    }
    .pagination {
        margin-bottom: 0;
    }
    .page-link {
        color: var(--primary-color);
    }
    .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .card.text-white .opacity-50 {
        color: rgba(255, 255, 255, 0.5);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche en temps réel (optionnel)
    let searchTimeout;
    const searchInput = document.getElementById('search');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });
    }

    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
