@extends('layouts.app')

@section('title', 'Gestion des enveloppes')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.dataTables.min.css">
@endpush

@push('scripts')
<!-- jQuery doit être chargé avant DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
@endpush

@section('content')
<div class="container-fluid">
    <h2 class="titre mb-4">Gestion des enveloppes</h2>

    <div class="enveloppes-container">
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

        {{-- Formulaire d'ajout (Admin uniquement) --}}
        @if(session('profil') == 'ADMIN' || session('profil') == 'ladmin' && !$envBloque)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter une nouvelle enveloppe
                </div>
                <div class="card-body">
                    <form action="{{ route('enveloppes.store') }}" method="POST" enctype="multipart/form-data" id="enveloppe-form">
                @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="debut" class="form-label">Terminus de départ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('debut') is-invalid @enderror"
                                       id="debut" name="debut" value="{{ old('debut') }}" required>
                                @error('debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="fin" class="form-label">Terminus d'arrivée <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('fin') is-invalid @enderror"
                                       id="fin" name="fin" value="{{ old('fin') }}" required>
                                @error('fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="voie" class="form-label">Voie <span class="text-danger">*</span></label>
                                <select class="form-select @error('voie') is-invalid @enderror" id="voie" name="voie" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="V1" {{ old('voie') == 'V1' ? 'selected' : '' }}>V1</option>
                                    <option value="V2" {{ old('voie') == 'V2' ? 'selected' : '' }}>V2</option>
                </select>
                                @error('voie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="distance" class="form-label">Distance (m) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('distance') is-invalid @enderror"
                                       id="distance" name="distance" value="{{ old('distance') }}"
                                       min="0" step="0.01" required>
                                @error('distance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="fichier" class="form-label">Fichier <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('fichier') is-invalid @enderror"
                                       id="fichier" name="fichier" accept=".csv,.txt" required>
                                @error('fichier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload me-2"></i>Importer l'enveloppe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Filtres (Admin uniquement) --}}
        @if(session('profil') == 'ADMIN' || session('profil') == 'ladmin')
            <div class="mb-3">
                <label for="filter" class="form-label">Filtrer par statut :</label>
                <select id="filter" class="form-select d-inline-block" style="width: auto;">
                    <option value="tous" {{ request('filter') == 'tous' ? 'selected' : '' }}>Toutes les enveloppes</option>
                    <option value="figer" {{ request('filter') == 'figer' ? 'selected' : '' }}>Archivées</option>
                    <option value="nonfiger" {{ request('filter') == 'nonfiger' ? 'selected' : '' }}>Non archivées</option>
                </select>
            </div>
        @endif

        {{-- Tableau des enveloppes --}}
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-list me-2"></i>Liste des enveloppes
            </div>
            <div class="card-body">
                @if($enveloppes->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Aucune enveloppe trouvée.
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="enveloppes-table" class="table table-striped table-bordered table-hover">
            <thead>
                                <tr>
                    <th>Nom</th>
                    <th>Date d'importation</th>
                    <th>Terminus de départ</th>
                    <th>Terminus d'arrivée</th>
                    <th>Voie</th>
                                    <th>Distance commerciale (m)</th>
                                    @if(session('profil') == 'ADMIN' || session('profil') == 'ladmin' && !$envBloque)
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
                                        @if(session('profil') == 'ADMIN' || session('profil') == 'ladmin' && !$envBloque)
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
                                                @if(session('profil') == 'ADMIN' || session('profil') == 'ladmin' && !$user->envBloque)
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
                @endif
            </div>
        </div>
    </div>
    </div>

{{-- Modals --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmation de suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>Attention !</strong>
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

<div class="modal fade" id="viewEnveloppeModal" tabindex="-1" aria-labelledby="viewEnveloppeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewEnveloppeModalLabel">
                    <i class="fas fa-file-alt me-2"></i>Contenu de l'enveloppe
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
    .enveloppes-container {
        max-width: 100%;
        margin: 0 auto;
    }
    .freeze-switch {
        cursor: pointer;
    }
    #enveloppes-table {
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let enveloppeIdToDelete = null;
        let enveloppeNomToDelete = null;
        let table;

        // Initialiser DataTable
        table = $('#enveloppes-table').DataTable({
            language: {
                processing: "Traitement en cours...",
                search: "Rechercher&nbsp;:",
                lengthMenu: "Afficher _MENU_ éléments",
                info: "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
                infoEmpty: "Affichage de l'élément 0 à 0 sur 0 éléments",
                infoFiltered: "(filtré de _MAX_ éléments au total)",
                loadingRecords: "Chargement en cours...",
                zeroRecords: "Aucun élément à afficher",
                emptyTable: "Aucune donnée disponible",
                paginate: {
                    first: "Premier",
                    previous: "Précédent",
                    next: "Suivant",
                    last: "Dernier"
                }
            },
            order: [[1, 'desc']], // Trier par date d'importation décroissante
            pageLength: 25,
            responsive: true
        });

        // Filtre par statut (Admin uniquement)
        @if(session('profil') == 'ADMIN' || session('profil') == 'ladmin')
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const filter = $("#filter").val();
                if (filter === "tous") return true;

                // Récupérer l'élément de la ligne pour vérifier l'attribut data-figer
                const row = settings.aoData[dataIndex].nTr;
                if (!row) return true;

                const isFrozen = $(row).attr('data-figer') === '1';

                if (filter === "figer") return isFrozen;
                if (filter === "nonfiger") return !isFrozen;

                return true;
            }
        );

        $("#filter").on('change', function() {
            table.draw();
        });
        @endif

        // Gestion du switch d'archivage
        $(document).on('change', '.freeze-switch', function() {
            const $switch = $(this);
            const $row = $switch.closest('tr');
            const enveloppeId = $switch.data('enveloppe-id');
            const isFrozen = $switch.is(':checked');
            const check = isFrozen ? "faux" : "vrai";
            const previousState = !isFrozen; // État précédent pour restauration en cas d'erreur

            // Mettre à jour l'attribut data-figer immédiatement pour le filtre
            $row.attr('data-figer', isFrozen ? '1' : '0');

            // Désactiver le switch pendant la requête
            $switch.prop('disabled', true);

            $.ajax({
                url: "{{ route('enveloppes.toggle-freeze') }}",
                method: 'POST',
                dataType: 'json',
                data: {
                    _token: "{{ csrf_token() }}",
                    enve: enveloppeId,
                    check: check
                },
                success: function(response) {
                    // Réactiver le switch
                    $switch.prop('disabled', false);

                    if (response.success) {
                        // Afficher un message de succès
                        showNotification(response.message, 'success');
                        // Redessiner le tableau pour appliquer le filtre si nécessaire
                        table.draw();
                    } else {
                        // Restaurer l'état précédent en cas d'erreur
                        $switch.prop('checked', previousState);
                        $row.attr('data-figer', previousState ? '1' : '0');
                        showNotification(response.message || 'Une erreur est survenue.', 'error');
                    }
                },
                error: function(xhr) {
                    // Réactiver le switch
                    $switch.prop('disabled', false);
                    // Restaurer l'état précédent
                    $switch.prop('checked', previousState);
                    $row.attr('data-figer', previousState ? '1' : '0');

                    let errorMessage = 'Une erreur est survenue lors de la modification du statut.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification(errorMessage, 'error');
                }
            });
        });

        // Fonction pour afficher des notifications
        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${icon} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            // Insérer l'alerte en haut de la page
            $('.enveloppes-container').prepend(alertHtml);

            // Supprimer automatiquement après 5 secondes
            setTimeout(function() {
                $('.alert').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }

        // Ouvrir une enveloppe
        $('.view-enveloppe').on('click', function() {
            const filename = $(this).data('filename');
            const modal = new bootstrap.Modal(document.getElementById('viewEnveloppeModal'));

            $('#viewEnveloppeModalLabel').html('<i class="fas fa-file-alt me-2"></i>' + filename);
            $('#enveloppe-content').html('<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></td></tr>');

            modal.show();

            $.get("{{ route('enveloppes.read') }}", { nom: filename })
                .done(function(data) {
                    $('#enveloppe-content').html(data);
                })
                .fail(function() {
                    $('#enveloppe-content').html('<tr><td colspan="4" class="text-center text-danger">Erreur lors du chargement du fichier.</td></tr>');
                });
        });

        // Supprimer une enveloppe
        $('.delete-enveloppe').on('click', function() {
            enveloppeIdToDelete = $(this).data('enveloppe-id');
            enveloppeNomToDelete = $(this).data('enveloppe-nom');

            $('#enveloppe-nom-to-delete').text(enveloppeNomToDelete);
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
        });

        $('#confirm-delete-btn').on('click', function() {
            if (!enveloppeIdToDelete) return;

            $.ajax({
                url: "{{ route('enveloppes.destroy', '') }}/" + enveloppeIdToDelete,
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('Une erreur est survenue lors de la suppression.');
    }
            });
        });
    });
</script>
@endpush
