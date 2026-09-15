
@extends('layouts.app')

@section('title', 'Courses du ' . $ladate->format('d/m/Y'))

@section('actions')
    <a href="{{ route('courses.index') }}" class="btn btn-secondary">
        <i class="fas fa-calendar me-1"></i>Retour au calendrier
    </a>
@endsection

@section('content')
<div class="container-fluid py-4">

    @if($courses->isEmpty())
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Aucune course pour cette date.
        </div>
    @else

        <div class="table-responsive">
            <table id="coursesTable" class="table table-striped table-hover">

                <thead class="table-dark">
                    <tr>
                        <th>Heure</th>
                        <th>Distance</th>
                        <th>Ouvrir</th>
                        <th>Matricule nom prenom</th>
                        <th>Fichier</th>
                        <th>Code</th>
                        <th>Statut</th>
                        <th>Enveloppe</th>
                        <th>Commentaires</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($courses as $course)
                        <tr>

                            {{-- Heure --}}
                            <td class="fw-bold">
                                {{ $course->heure }}
                            </td>

                            {{-- Distance --}}
                            <td data-sort="{{ $course->distance }}">
                                {{ $course->distance }} m
                            </td>


                                   
                            

                            {{-- Ouvrir : 3ème colonne --}}
                            <td>
                                <a href="{{ route('courses.show', $course->idcourse) }}"
                                target="_blank"
                                class="btn btn-sm btn-primary"
                                title="Voir les détails">

                                    <i class="fas fa-eye me-1"></i>
                                    Ouvrir

                                </a>
                            </td>

                            {{-- Matricule nom prénom : 4ème colonne --}}
                            <td>
                                {{ $course->matricule . ' ' .
                                    ($course->conducteur->nom ?? '-') . ' ' .
                                    ($course->conducteur->prenom ?? '') }}
                            </td>

                            <td>
                                <code>{{ $course->fichier }}</code>
                            </td>

                            {{-- Code --}}
                            <td data-sort="{{ $course->code ?? 0 }}">
                                @if($course->code)
                                    <span class="badge bg-primary">
                                        {{ sprintf('%04d', $course->code) }}-{{ $course->ladate->year }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Statut --}}
                            <td data-sort="{{ $course->valide ? 1 : 0 }}">

                                @if($course->valide)

                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Contrôlée
                                    </span>

                                @else

                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>
                                        En attente
                                    </span>

                                @endif

                            </td>

                            {{-- Enveloppe --}}
                            <td>
                                @if($course->enveloppe)
                                    <code>{{ $course->enveloppe->nom }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Commentaires --}}
                            <td>
                                @if($course->commentaire)
                                    <span class="text-muted">
                                        {{ $course->commentaire }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td>

                                <a href="{{ route('courses.cancel', $course->idcourse) }}"
                                   class="btn btn-sm btn-danger"
                                   title="Annuler la course"
                                   onclick="return confirm('Confirmez l\'annulation de la course ?')">

                                    <i class="fas fa-trash"></i>

                                </a>

                            </td>

                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    @endif

</div>
@endsection

@section('styles')
<style>

    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }

    code {
        background-color: #f4f4f4;
        padding: 2px 6px;
        border-radius: 3px;
    }

    /* Bouton Ouvrir */
    .btn-primary {
        white-space: nowrap;
    }

    /* En-têtes triables */
    #coursesTable thead th {
        cursor: pointer;
        user-select: none;
    }

    #coursesTable thead th:hover {
        background-color: #343a40;
    }

</style>
@endsection

@section('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {

    const table = document.getElementById('coursesTable');

    if (!table) return;

    const headers = table.querySelectorAll('thead th');

    let sortDirection = {};

    headers.forEach(function (header, columnIndex) {

        // La colonne Actions ne doit pas être triée
        if (columnIndex === 9) return;

        header.addEventListener('click', function () {

            const tbody = table.querySelector('tbody');

            const rows = Array.from(tbody.querySelectorAll('tr'));

            sortDirection[columnIndex] =
                !sortDirection[columnIndex];

            const direction = sortDirection[columnIndex] ? 1 : -1;

            rows.sort(function (a, b) {

                const cellA = a.children[columnIndex];
                const cellB = b.children[columnIndex];

                let valueA = cellA.dataset.sort ||
                             cellA.textContent.trim();

                let valueB = cellB.dataset.sort ||
                             cellB.textContent.trim();

                // Conversion numérique pour distance et code
                if (columnIndex === 1 || columnIndex === 5 || columnIndex === 6) {

                    valueA = parseFloat(valueA) || 0;
                    valueB = parseFloat(valueB) || 0;

                    return (valueA - valueB) * direction;

                }

                // Tri par heure
                if (columnIndex === 0) {

                    valueA = valueA.replace(':', '');
                    valueB = valueB.replace(':', '');

                    return (parseInt(valueA) - parseInt(valueB)) * direction;

                }

                // Tri alphabétique
                return valueA.localeCompare(valueB, 'fr', {
                    numeric: true,
                    sensitivity: 'base'
                }) * direction;

            });

            rows.forEach(function (row) {
                tbody.appendChild(row);
            });

        });

    });

});

</script>
@endsection