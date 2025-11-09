{{-- resources/views/conducteurs/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Liste des Conducteurs')

@section('actions')
    <a href="{{ route('conducteurs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau Conducteur
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if($conducteurs->isEmpty())
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>Aucun conducteur trouvé.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Site</th>
                            <th>Nb. Courses</th>
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
                                <span class="badge bg-info">{{ $conducteur->courses_count }}</span>
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
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce conducteur ?')" 
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
            
            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Affichage de {{ $conducteurs->firstItem() }} à {{ $conducteurs->lastItem() }} 
                    sur {{ $conducteurs->total() }} conducteurs
                </div>
                <div>
                    {{ $conducteurs->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection