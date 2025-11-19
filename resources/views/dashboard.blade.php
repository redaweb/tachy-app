{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['conducteurs'] }}</h4>
                        <p>Conducteurs</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['courses'] }}</h4>
                        <p>Courses</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-route fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['enveloppes'] }}</h4>
                        <p>Enveloppes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-map fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['exces'] }}</h4>
                        <p>Excès de vitesse</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white" style="background-color: #6c757d;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['freinages'] }}</h4>
                        <p>Freinages</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-stop-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white" style="background-color: #17a2b8;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['journal'] }}</h4>
                        <p>Journal</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-book fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white" style="background-color: #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['courseCsv'] }}</h4>
                        <p>Données CSV</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-csv fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white" style="background-color: #6610f2;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['users'] }}</h4>
                        <p>Utilisateurs</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-shield fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Dernières courses</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Conducteur</th>
                                <th>Distance</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCourses as $course)
                            <tr>
                                <td>{{ $course->ladate->format('d/m/Y') }}</td>
                                <td>{{ $course->conducteur->prenom ?? '' }} {{ $course->conducteur->nom ?? 'N/A' }}</td>
                                <td>{{ $course->distance ?? 0 }} km</td>
                                <td>
                                    @if($course->valide)
                                        <span class="badge bg-success">Validé</span>
                                    @else
                                        <span class="badge bg-warning">En attente</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune course récente</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Excès récents</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Interstation</th>
                                <th>Vitesse max</th>
                                <th>Autorisé</th>
                                <th>Conducteur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExces as $exces)
                            <tr>
                                <td>{{ Str::limit($exces->interstation ?? 'N/A', 20) }}</td>
                                <td class="text-danger">{{ $exces->maxx ?? 0 }} km/h</td>
                                <td>{{ $exces->autorise ?? 0 }} km/h</td>
                                <td>{{ $exces->course->conducteur->prenom ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucun excès récent</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Freinages récents</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Vitesse</th>
                                <th>Interstation</th>
                                <th>Conducteur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentFreinages as $freinage)
                            <tr>
                                <td>{{ $freinage->type ?? 'N/A' }}</td>
                                <td>{{ $freinage->vitesse ?? 0 }} km/h</td>
                                <td>{{ Str::limit($freinage->interstation ?? 'N/A', 20) }}</td>
                                <td>{{ $freinage->course->conducteur->prenom ?? 'N/A' }} {{ $freinage->course->conducteur->nom ?? '' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucun freinage récent</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Journal récent</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Action</th>
                                <th>Détail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentJournal as $journal)
                            <tr>
                                <td>{{ $journal->ladate->format('d/m/Y') }}</td>
                                <td>{{ $journal->heure ?? 'N/A' }}</td>
                                <td>{{ $journal->action ?? 'N/A' }}</td>
                                <td>{{ Str::limit($journal->detail ?? 'N/A', 30) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune entrée récente</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection