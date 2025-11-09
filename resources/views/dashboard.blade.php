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
    <div class="col-md-6">
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
                            @foreach($recentCourses as $course)
                            <tr>
                                <td>{{ $course->ladate->format('d/m/Y') }}</td>
                                <td>{{ $course->conducteur->prenom }} {{ $course->conducteur->nom }}</td>
                                <td>{{ $course->distance }} km</td>
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
    <div class="col-md-6">
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
                            @foreach($recentExces as $exces)
                            <tr>
                                <td>{{ Str::limit($exces->interstation, 20) }}</td>
                                <td class="text-danger">{{ $exces->maxx }} km/h</td>
                                <td>{{ $exces->autorise }} km/h</td>
                                <td>{{ $exces->course->conducteur->prenom ?? 'N/A' }}</td>
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