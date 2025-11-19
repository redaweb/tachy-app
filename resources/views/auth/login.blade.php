@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="matricule" class="form-label">Adresse matricule</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-envelope"></i>
            </span>
            <input id="matricule" 
                   type="text" 
                   class="form-control @error('matricule') is-invalid @enderror" 
                   name="matricule" 
                   value="{{ old('matricule') }}" 
                   required 
                   autocomplete="matricule" 
                   autofocus
                   placeholder="310">
            @error('matricule')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>
            <input id="password" 
                   type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password" 
                   required 
                   autocomplete="current-password"
                   placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="mb-3 form-check">
        <input class="form-check-input" 
               type="checkbox" 
               name="remember" 
               id="remember" 
               {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">
            Se souvenir de moi
        </label>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
        </button>
    </div>
</form>
@endsection

@section('footer')
@if (Route::has('password.request'))
    <a href="{{ route('password.request') }}">
        <i class="fas fa-key me-1"></i>Mot de passe oublié ?
    </a>
@endif
@if (Route::has('register'))
    <div class="mt-2">
        Pas encore de compte ? <a href="{{ route('register') }}">S'inscrire</a>
    </div>
@endif
@endsection
