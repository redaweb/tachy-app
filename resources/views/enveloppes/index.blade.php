{{-- resources/views/enveloppes/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestion des enveloppes')

@section('actions')
<div class="btn-group">
    @if(in_array($user->profil, ['ADMIN', 'ladmin']) && !$envBloque)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEnveloppeModal">
            <i class="fas fa-plus me-1"></i>Nouvelle Enveloppe
        </button>
    @endif
    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
        <i class="fas fa-filter me-1"></i>Filtres
    </button>
    @if(in_array($user->profil, ['ADMIN', 'ladmin']))
        <button type="button" class="btn btn-info" onclick="exportEnveloppes()">
            <i class="fas fa-download me-1"></i>Exporter
        </button>
    @endif
</div>
@endsection

@section('content')
<!-- Filtres avancés -->
<div class="collapse {{ request()->has('search') || request()->has('voie') || request()->has('statut') || request()->has('date_debut') ? 'show' : '' }}" id="filtersCollapse">
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('enveloppes.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Nom, lieu départ ou arrivée..."
                                   value="{{ request('search') }}">
                            @if(request('search'))
                            <a href="{{ route('enveloppes.index', array_merge(request()->except(['search', 'page']))) }}"
                               class="btn btn-outline-secondary" title="Effacer la recherche">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="voie" class="form-label">Filtrer par voie</label>
                        <select class="form-select" id="voie" name="voie" onchange="this.form.submit()">
                            <option value="">Toutes les voies</option>
                            <option value="V1" {{ request('voie') == 'V1' ? 'selected' : '' }}>V1</option>
                            <option value="V2" {{ request('voie') == 'V2' ? 'selected' : '' }}>V2</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="statut" class="form-label">Filtrer par statut</label>
                        <select class="form-select" id="statut" name="statut" onchange="this.form.submit()">
                            <option value="">Toutes</option>
                            <option value="active" {{ request('statut') == 'active' ? 'selected' : '' }}>Actives</option>
                            <option value="archivee" {{ request('statut') == 'archivee' ? 'selected' : '' }}>Archivées</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="date_debut" class="form-label">Date début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut"
                               value="{{ request('date_debut') }}" onchange="this.form.submit()">
                    </div>

                    <div class="col-md-2">
                        <label for="date_fin" class="form-label">Date fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin"
                               value="{{ request('date_fin') }}" onchange="this.form.submit()">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label for="distance_min" class="form-label">Distance min (m)</label>
                        <input type="number" class="form-control" id="distance_min" name="distance_min"
                               value="{{ request('distance_min') }}" placeholder="Min" onchange="this.form.submit()">
                    </div>

                    <div class="col-md-3">
                        <label for="distance_max" class="form-label">Distance max (m)</label>
                        <input type="number" class="form-control" id="distance_max" name="distance_max"
                               value="{{ request('distance_max') }}" placeholder="Max" onchange="this.form.submit()">
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        @if(request()->hasAny(['search', 'voie', 'statut', 'date_debut', 'date_fin', 'distance_min', 'distance_max']))
                            <a href="{{ route('enveloppes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo-alt me-1"></i>Réinitialiser
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Champs cachés pour le tri -->
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

{{-- Messages de succès/erreur --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Statistiques --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Enveloppes</h6>
                        <h2 class="mt-2 mb-0">{{ $stats['total'] }}</h2>
                    </div>
                    <i class="fas fa-envelope fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Actives</h6>
                        <h2 class="mt-2 mb-0">{{ $stats['actives'] }}</h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Archivées</h6>
                        <h2 class="mt-2 mb-0">{{ $stats['archivees'] }}</h2>
                    </div>
                    <i class="fas fa-archive fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tableau des enveloppes --}}
<div class="card">
    <div class="card-header bg-info text-white">
        <i class="fas fa-list me-2"></i>Liste des enveloppes
    </div>
    <div class="card-body">
        @if($enveloppes->isEmpty())
            <div class="alert alert-info text-center py-4">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h5>Aucune enveloppe trouvée</h5>
                @if(request()->hasAny(['search', 'voie', 'statut', 'date_debut', 'date_fin']))
                    <p class="mb-0">Aucun résultat ne correspond à vos critères.</p>
                    <a href="{{ route('enveloppes.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('enveloppes.index', array_merge(request()->query(), ['sort' => 'nom', 'direction' => request('sort') == 'nom' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-dark text-decoration-none">
                                    Nom
                                    @if(request('sort') == 'nom')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('enveloppes.index', array_merge(request()->query(), ['sort' => 'importation', 'direction' => request('sort') == 'importation' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-dark text-decoration-none">
                                    Date d'importation
                                    @if(request('sort') == 'importation')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Terminus de départ</th>
                            <th>Terminus d'arrivée</th>
                            <th>Voie</th>
                            <th>Distance commerciale (m)</th>
                            @if(in_array($user->profil, ['ADMIN', 'ladmin']) && !$envBloque)
                                <th>Archiver</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enveloppes as $enveloppe)
                            <tr data-figer="{{ $enveloppe->figer ? '1' : '0' }}">
                                <td>{{ $enveloppe->nom }}</td>
                                <td>{{ $enveloppe->importation->format('d/m/Y') }}</td>
                                <td>{{ $enveloppe->lieudebut }}</td>
                                <td>{{ $enveloppe->lieufin }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $enveloppe->voie }}</span>
                                </td>
                                <td>{{ number_format($enveloppe->dis_com, 0, ',', ' ') }}</td>
                                @if(in_array($user->profil, ['ADMIN', 'ladmin']) && !$envBloque)
                                    <td class="text-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input freeze-switch"
                                                   type="checkbox"
                                                   data-enveloppe-id="{{ $enveloppe->idenveloppe }}"
                                                   {{ $enveloppe->figer ? 'checked' : '' }}
                                                   role="switch"
                                                   aria-label="Archiver l'enveloppe">
                                        </div>
                                    </td>
                                @endif
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button"
                                                class="btn btn-sm btn-info view-enveloppe"
                                                data-filename="{{ $enveloppe->nom }}"
                                                title="Ouvrir l'enveloppe">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(in_array($user->profil, ['ADMIN', 'ladmin']) && !$envBloque)
                                            <button type="button"
                                                    class="btn btn-sm btn-danger delete-enveloppe"
                                                    data-enveloppe-id="{{ $enveloppe->idenveloppe }}"
                                                    data-enveloppe-nom="{{ $enveloppe->nom }}"
                                                    title="Supprimer l'enveloppe">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Affichage de {{ $enveloppes->firstItem() }} à {{ $enveloppes->lastItem() }}
                    sur {{ $enveloppes->total() }} enveloppes
                </div>
                <div>
                    {{ $enveloppes->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modals (conservés identiques à votre version) --}}
<div class="modal fade" id="addEnveloppeModal" tabindex="-1" aria-labelledby="addEnveloppeModalLabel" aria-hidden="true">
    {{-- Votre modal d'ajout existant --}}
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addEnveloppeModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter une nouvelle enveloppe
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('enveloppes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="debut" class="form-label">Terminus de départ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="debut" name="debut" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fin" class="form-label">Terminus d'arrivée <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fin" name="fin" required>
                        </div>
                        <div class="col-md-4">
                            <label for="voie" class="form-label">Voie <span class="text-danger">*</span></label>
                            <select class="form-select" id="voie" name="voie" required>
                                <option value="">Sélectionner...</option>
                                <option value="V1">V1</option>
                                <option value="V2">V2</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="distance" class="form-label">Distance (m) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="distance" name="distance"
                                   min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label for="fichier" class="form-label">Fichier <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="fichier" name="fichier"
                                   accept=".csv,.txt" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    {{-- Votre modal de suppression existant --}}
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmation de suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Attention !</strong>
                    <p class="mb-0 mt-2">La suppression de cette enveloppe entraînera la suppression des courses contrôlées associées.</p>
                </div>
                <p>Voulez-vous vraiment supprimer l'enveloppe <strong id="enveloppe-nom-to-delete"></strong> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewEnveloppeModal" tabindex="-1" aria-hidden="true">
    {{-- Votre modal de visualisation existant --}}
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewEnveloppeModalLabel">
                    <i class="fas fa-file-alt me-2"></i>Contenu de l'enveloppe
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Libellé</th>
                                <th>Distance</th>
                                <th>Vitesse max</th>
                                <th>Station</th>
                            </tr>
                        </thead>
                        <tbody id="enveloppe-content">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .opacity-50 {
        opacity: 0.5;
    }
    .freeze-switch {
        cursor: pointer;
    }
    .freeze-switch:checked {
        background-color: #ffc107;
        border-color: #ffc107;
    }
    .pagination {
        margin-bottom: 0;
    }
</style>
@endpush

{{-- resources/views/enveloppes/index.blade.php --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé - Enveloppes');

    // Gestion du switch d'archivage
    const switches = document.querySelectorAll('.freeze-switch');

    switches.forEach(function(switch_) {
        switch_.addEventListener('change', function() {
            const enveloppeId = this.dataset.enveloppeId;
            const isFrozen = this.checked;
            //alert('Switch cliqué: ' + enveloppeId + ' - ' + (isFrozen ? 'Archiver' : 'Désarchiver'));
            const check = isFrozen ? "vrai" : "faux";
            const previousState = !isFrozen;
            const row = this.closest('tr');

            console.log('Switch cliqué:', enveloppeId, isFrozen);

            // Désactiver le switch
            this.disabled = true;

            // Créer FormData
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('enve', enveloppeId);
            formData.append('check', check);

            // Envoyer la requête
            fetch('{{ route("enveloppes.toggle-freeze") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',  // Important !
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Succès:', data.message);
                    // Optionnel: recharger la page pour voir le changement
                    // location.reload();
                } else {
                    console.error('Erreur:', data.message);
                    this.checked = previousState;
                    alert(data.message || 'Erreur');
                }
            })
            .catch(error => {
                console.error('Erreur réseau:', error);
                this.checked = previousState;
                alert('Erreur de communication');
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // Visualisation de l'enveloppe
    const viewButtons = document.querySelectorAll('.view-enveloppe');

    viewButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const filename = this.dataset.filename;
            console.log('View:', filename);

            const modal = new bootstrap.Modal(document.getElementById('viewEnveloppeModal'));
            document.getElementById('viewEnveloppeModalLabel').innerHTML =
                '<i class="fas fa-file-alt me-2"></i>' + filename;

            document.getElementById('enveloppe-content').innerHTML =
                '<tr><td colspan="4" class="text-center">' +
                '<div class="spinner-border text-primary"></div>' +
                '</td></tr>';

            modal.show();

            fetch('{{ route("enveloppes.read") }}?nom=' + encodeURIComponent(filename))
                .then(response => response.text())
                .then(html => {
                    document.getElementById('enveloppe-content').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('enveloppe-content').innerHTML =
                        '<tr><td colspan="4" class="text-center text-danger">Erreur</td></tr>';
                });
        });
    });

    // Suppression
    let deleteId = null;

    document.querySelectorAll('.delete-enveloppe').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteId = this.dataset.enveloppeId;
            document.getElementById('enveloppe-nom-to-delete').textContent = this.dataset.enveloppeNom;
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        });
    });

    document.getElementById('confirm-delete-btn')?.addEventListener('click', function() {
        if (!deleteId) return;

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'DELETE');

        fetch('{{ route("enveloppes.destroy", "") }}/' + deleteId, {
            method: 'POST',
            body: formData
        })
        .then(() => location.reload())
        .catch(() => alert('Erreur suppression'));
    });
});
</script>
@endpush
