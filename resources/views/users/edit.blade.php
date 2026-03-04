{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Modifier un utilisateur')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Modification de l'utilisateur : {{ $user->nom }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user->iduser) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nom') is-invalid @enderror"
                               id="nom" name="nom" value="{{ old('nom', $user->nom) }}" required>
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="motpass" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control @error('motpass') is-invalid @enderror"
                               id="motpass" name="motpass">
                        <small class="text-muted">Laissez vide pour conserver le mot de passe actuel</small>
                        @error('motpass')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="profil" class="form-label">Profil <span class="text-danger">*</span></label>
                        <select class="form-select @error('profil') is-invalid @enderror"
                                id="profil" name="profil" required>
                            <option value="">Sélectionnez un profil</option>
                            <option value="admin" {{ old('profil', $user->profil) == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="supervisor" {{ old('profil', $user->profil) == 'supervisor' ? 'selected' : '' }}>Superviseur</option>
                            <option value="user" {{ old('profil', $user->profil) == 'user' ? 'selected' : '' }}>Utilisateur</option>
                            @if(in_array(auth()->user()->profil, ['superadmin', 'DG']))
                            <option value="DG" {{ old('profil', $user->profil) == 'DG' ? 'selected' : '' }}>Directeur Général</option>
                            <option value="managerR" {{ old('profil', $user->profil) == 'managerR' ? 'selected' : '' }}>Manager Régional</option>
                            <option value="superadmin" {{ old('profil', $user->profil) == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                            @endif
                        </select>
                        @error('profil')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="site" class="form-label">Site</label>
                        <select class="form-select @error('site') is-invalid @enderror"
                                id="site" name="site">
                            <option value="">Sélectionnez un site</option>
                            @php
                                $sites = ['ALG' => 'Alger', 'ORN' => 'Oran', 'CST' => 'Constantine',
                                         'SBA' => 'Sidi Bel Abbès', 'ORG' => 'Ouargla',
                                         'STF' => 'Sétif', 'MGM' => 'Mostaganem'];
                            @endphp
                            @foreach($sites as $code => $label)
                                <option value="{{ $code }}" {{ old('site', $user->site) == $code ? 'selected' : '' }}>
                                    {{ $label }} ({{ $code }})
                                </option>
                            @endforeach
                        </select>
                        @error('site')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="envBloque" name="envBloque"
                                   value="1" {{ old('envBloque', $user->envBloque) ? 'checked' : '' }}>
                            <label class="form-check-label" for="envBloque">
                                Bloquer l'utilisateur
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
