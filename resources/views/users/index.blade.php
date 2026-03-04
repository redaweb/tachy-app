{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('actions')
<div class="btn-group">
    <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus"></i> Nouvel Utilisateur
    </a>
</div>
@endsection

@section('content')
<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Recherche par nom</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search"
                               placeholder="Nom d'utilisateur..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="profil" class="form-label">Filtrer par profil</label>
                    <select class="form-select" id="profil" name="profil" onchange="this.form.submit()">
                        <option value="">Tous les profils</option>
                        @foreach($profils as $profil)
                            <option value="{{ $profil }}" {{ request('profil') == $profil ? 'selected' : '' }}>
                                {{ $profil }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="statut" class="form-label">Filtrer par statut</label>
                    <select class="form-select" id="statut" name="statut" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Actif</option>
                        <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Bloqué</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="site" class="form-label">Filtrer par site</label>
                    <select class="form-select" id="site" name="site" onchange="this.form.submit()">
                        <option value="">Tous les sites</option>
                        @foreach($sites as $code => $label)
                            <option value="{{ $code }}" {{ request('site') == $code ? 'selected' : '' }}>
                                {{ $label }} ({{ $code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    @if(request()->has('search') || request()->has('profil') || request()->has('statut') || request()->has('site'))
                        <a href="{{ route('users.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tableau des utilisateurs -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Profil</th>
                        <th>Site</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->iduser }}</td>
                        <td>{{ $user->nom }}</td>
                        <td>
                            @php
                                $badgeClass = match(strtolower($user->profil)) {
                                    'admin', 'superadmin', 'dg' => 'danger',
                                    'manager', 'managerr', 'supervisor' => 'warning',
                                    default => 'info'
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}">
                                {{ $user->profil }}
                            </span>
                        </td>
                        <td>
                            @if($user->site)
                                {{ $sites[$user->site] ?? $user->site }} ({{ $user->site }})
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if(auth()->id() != $user->iduser)
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-switch" type="checkbox"
                                           role="switch"
                                           id="status_{{ $user->iduser }}"
                                           data-user-id="{{ $user->iduser }}"
                                           {{ $user->envBloque ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_{{ $user->iduser }}">
                                        <span class="badge {{ $user->envBloque ? 'bg-danger' : 'bg-success' }}"
                                              id="status_label_{{ $user->iduser }}">
                                            {{ $user->envBloque ? 'Bloqué' : 'Actif' }}
                                        </span>
                                    </label>
                                </div>
                            @else
                                <span class="badge {{ $user->envBloque ? 'bg-danger' : 'bg-success' }}">
                                    {{ $user->envBloque ? 'Bloqué' : 'Actif' }}
                                </span>
                                <small class="text-muted d-block">(votre compte)</small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.show', $user->iduser) }}" class="btn btn-info" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user->iduser) }}" class="btn btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(auth()->id() != $user->iduser)
                                <form action="{{ route('users.destroy', $user->iduser) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Supprimer"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun utilisateur trouvé</p>
                            @if(request()->has('search') || request()->has('profil') || request()->has('statut') || request()->has('site'))
                                <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-times"></i> Réinitialiser les filtres
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination et informations -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Affichage de {{ $users->firstItem() ?? 0 }} à {{ $users->lastItem() ?? 0 }}
                sur {{ $users->total() }} utilisateurs
            </div>
            <div>
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        margin-right: 0.5em;
        cursor: pointer;
    }
    .form-switch .form-check-input:checked {
        background-color: #dc3545;
        border-color: #dc3545;
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des switches de statut
    document.querySelectorAll('.status-switch').forEach(switch_ => {
        switch_.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const status = this.checked ? 1 : 0;
            const label = document.getElementById('status_label_' + userId);
            const originalText = label.innerText;

            // Désactiver le switch pendant la requête
            this.disabled = true;

            // Afficher un indicateur de chargement
            label.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...';

            fetch(`/users/${userId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mise à jour réussie
                    label.className = 'badge ' + (status ? 'bg-danger' : 'bg-success');
                    label.innerText = status ? 'Bloqué' : 'Actif';

                    // Notification de succès (optionnel)
                    showNotification('success', data.message);
                } else {
                    // Erreur - revert le switch
                    this.checked = !status;
                    label.className = 'badge ' + (this.checked ? 'bg-danger' : 'bg-success');
                    label.innerText = this.checked ? 'Bloqué' : 'Actif';

                    // Afficher l'erreur
                    showNotification('danger', data.message || 'Erreur lors de la mise à jour');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Erreur - revert le switch
                this.checked = !status;
                label.className = 'badge ' + (this.checked ? 'bg-danger' : 'bg-success');
                label.innerText = this.checked ? 'Bloqué' : 'Actif';

                showNotification('danger', 'Erreur de connexion');
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // Fonction pour afficher les notifications
    function showNotification(type, message) {
        // Vous pouvez utiliser Toast ou une notification personnalisée
        // Pour l'instant, on utilise alert (à remplacer par votre système de notification)
        if (type === 'success') {
            // Optionnel: afficher une notification discrète
            console.log('Success:', message);
        } else {
            alert(message);
        }
    }

    // Recherche en temps réel (optionnel - décommentez si vous voulez la recherche automatique)
    /*
    let searchTimeout;
    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
    */
});
</script>
@endpush
