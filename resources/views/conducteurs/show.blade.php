{{-- resources/views/conducteurs/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Détails Conducteur')

@section('actions')
    <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Informations Conducteur</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Matricule:</th>
                        <td>{{ $conducteur->matricule }}</td>
                    </tr>
                    <tr>
                        <th>Nom:</th>
                        <td>{{ $conducteur->nom }}</td>
                    </tr>
                    <tr>
                        <th>Prénom:</th>
                        <td>{{ $conducteur->prenom }}</td>
                    </tr>
                    <tr>
                        <th>Site:</th>
                        <td>{{ $conducteur->site }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Historique des Courses</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Enveloppe</th>
                                <th>Distance</th>
                                <th>FU</th>
                                <th>Klaxon</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conducteur->courses as $course)
                            <tr>
                                <td>{{ $course->ladate->format('d/m/Y') }}</td>
                                <td>{{ $course->enveloppe->nom ?? 'N/A' }}</td>
                                <td>{{ $course->distance }} km</td>
                                <td>{{ $course->FU }}</td>
                                <td>{{ $course->klaxon }}</td>
                                <td>
                                    @if($course->valide)
                                        <span class="badge bg-success">Validé</span>
                                    @else
                                        <span class="badge bg-warning">En attente</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection