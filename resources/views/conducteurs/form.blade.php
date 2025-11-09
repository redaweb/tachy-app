{{-- resources/views/conducteurs/form.blade.php --}}
@extends('layouts.app')

@section('title', isset($conducteur) ? 'Modifier Conducteur' : 'Nouveau Conducteur')

@section('actions')
    <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" 
              action="{{ isset($conducteur) ? route('conducteurs.update', $conducteur->matricule) : route('conducteurs.store') }}">
            @csrf
            @if(isset($conducteur))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom *</label>
                        <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                               id="nom" name="nom" 
                               value="{{ old('nom', $conducteur->nom ?? '') }}" required>
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom *</label>
                        <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                               id="prenom" name="prenom" 
                               value="{{ old('prenom', $conducteur->prenom ?? '') }}" required>
                        @error('prenom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="site" class="form-label">Site</label>
                <input type="text" class="form-control @error('site') is-invalid @enderror" 
                       id="site" name="site" 
                       value="{{ old('site', $conducteur->site ?? '') }}">
                @error('site')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>
                {{ isset($conducteur) ? 'Modifier' : 'Créer' }}
            </button>
        </form>
    </div>
</div>
@endsection