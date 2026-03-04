{{-- resources/views/users/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Détails de l\'utilisateur')

@section('actions')
<div class="btn-group">
    <a href="{{ route('users.edit', $user->iduser) }}" class="btn btn-sm btn-warning">
        <i class="fas fa-edit"></i> Modifier
    </a>
    <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-list"></i> Retour à la liste
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations de l'utilisateur</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">ID</th>
                        <td>{{ $user->iduser }}</td>
                    </tr>
                    <tr>
                        <th>Nom d'utilisateur</th>
                        <td>{{ $user->nom }}</td>
                    </tr>
                    <tr>
                        <th>Profil</th>
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
                    </tr>
                    <tr>
                        <th>Site</th>
                        <td>
                            @if($user->site)
                                @php
                                    $sites = ['ALG' => 'Alger', 'ORN' => 'Oran', 'CST' => 'Constantine',
                                             'SBA' => 'Sidi Bel Abbès', 'ORG' => 'Ouargla',
                                             'STF' => 'Sétif', 'MGM' => 'Mostaganem'];
                                @endphp
                                {{ $sites[$user->site] ?? $user->site }} ({{ $user->site }})
                            @else
                                <span class="text-muted">Non défini</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Statut</th>
                        <td>
                            @if($user->envBloque)
                                <span class="badge bg-danger">Bloqué</span>
                            @else
                                <span class="badge bg-success">Actif</span>
                            @endif
                        </td>
                    </tr>
                </table>

                @if(auth()->id() != $user->iduser)
                <div class="mt-3">
                    <form action="{{ route('users.destroy', $user->iduser) }}" method="POST"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer cet utilisateur
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
