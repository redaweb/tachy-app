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
            <i class="fas fa-info-circle me-2"></i>Aucune course pour cette date.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Heure</th>
                        <th>Distance</th>
                        <th>Matricule nom prenom</th>
                        <th>Fichier</th>
                        <th>Code</th>
                        <th>Statut</th>
                        <th>Enveloppe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                        <tr>
                            <td class="fw-bold">{{ $course->heure }}</td>
                            <td>{{ $course->distance }} m</td>
                            <td>{{ $course->matricule.' '.($course->conducteur->nom ?? '-').' '.($course->conducteur->prenom ?? '') }}</td>

                            <td>
                                <code>{{ $course->fichier }}</code>
                            </td>
                            <td>
                                @if($course->code)
                                    <span class="badge bg-primary">{{ sprintf('%04d', $course->code) }}-{{ $course->ladate->year }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($course->valide)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Contrôlée
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>En attente
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($course->enveloppe)
                                    <code>{{ $course->enveloppe->nom }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            <td>
                                <a href="{{ route('courses.show', $course->idcourse) }}" target="_blank" class="btn btn-sm btn-primary" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
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
</style>
@endsection
