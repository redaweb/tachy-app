{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Créer un utilisateur')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations du nouvel utilisateur</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nom') is-invalid @enderror"
                               id="nom" name="nom" value="{{ old('nom') }}" required>
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="motpass" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('motpass') is-invalid @enderror"
                               id="motpass" name="motpass" required>
                        <small class="text-muted">Minimum 6 caractères</small>
                        @error('motpass')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="profil" class="form-label">Profil <span class="text-danger">*</span></label>
                        <select class="form-select @error('profil') is-invalid @enderror"
                                id="profil" name="profil" required>
                            <option value="">Sélectionnez un profil</option>
                            <option value="admin" {{ old('profil') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="supervisor" {{ old('profil') == 'supervisor' ? 'selected' : '' }}>Superviseur</option>
                            <option value="user" {{ old('profil') == 'user' ? 'selected' : '' }}>Utilisateur</option>
                            @if(in_array(auth()->user()->profil, ['superadmin', 'DG']))
                            <option value="DG" {{ old('profil') == 'DG' ? 'selected' : '' }}>Directeur Général</option>
                            <option value="managerR" {{ old('profil') == 'managerR' ? 'selected' : '' }}>Manager Régional</option>
                            <option value="superadmin" {{ old('profil') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
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
                                <option value="{{ $code }}" {{ old('site') == $code ? 'selected' : '' }}>
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
                                   value="1" {{ old('envBloque') ? 'checked' : '' }}>
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
                            <i class="fas fa-save"></i> Créer l'utilisateur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
