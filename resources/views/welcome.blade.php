<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dorcas | Dorcas</title>
    <style>
        :root { --blue: #2b3593; --cyan: #2dc4ea; --yellow: #fdcf41; --ink: #19204f; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; color: var(--ink); font-family: "Segoe UI", Arial, sans-serif; background: #f4fbff; }

        .page { min-height: 100vh; display: grid; grid-template-columns: 1.15fr .85fr; }
        .welcome-panel { position: relative; overflow: hidden; display: flex; flex-direction: column; padding: clamp(2rem, 5vw, 5rem); color: #fff; background: linear-gradient(135deg, var(--blue), #202979); }
        .welcome-panel::before, .welcome-panel::after { position: absolute; content: ""; border-radius: 50%; opacity: .18; }
        .welcome-panel::before { width: 30rem; height: 30rem; top: -17rem; right: -11rem; background: var(--cyan); }
        .welcome-panel::after { width: 22rem; height: 22rem; bottom: -13rem; left: -9rem; background: var(--yellow); }
        .brand, .welcome-content, .welcome-footer { position: relative; z-index: 1; }
        .brand { display: flex; align-items: center; gap: 1rem; }
        .brand img { width: 120px; height: auto; padding: .45rem .7rem; border-radius: .5rem; background: #fff; }
        .brand span { font-size: .78rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .welcome-content { max-width: 580px; margin: auto 0; padding: 4rem 0; }
        .welcome-content p { margin: 0 0 1rem; color: var(--yellow); font-size: .9rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(2.5rem, 5vw, 4.8rem); line-height: 1.04; letter-spacing: -.05em; }
        .description { max-width: 480px; margin-top: 1.5rem; color: rgba(255,255,255,.82); font-size: 1.08rem; line-height: 1.7; }
        .dorcas-logo { width: min(290px, 70%); height: auto; margin-top: 2.2rem; padding: .75rem; border-radius: .8rem; background: #fff; }
        .welcome-footer { color: rgba(255,255,255,.65); font-size: .8rem; }

        .login-panel { display: grid; place-items: center; padding: 2rem; background: #fff; }
        .login-box { width: min(390px, 100%); }
        .login-box h2 { margin: 0; color: var(--blue); font-size: 2rem; letter-spacing: -.03em; }
        .login-intro { margin: .65rem 0 2rem; color: #68708a; line-height: 1.55; }
        .field { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: .45rem; color: var(--blue); font-size: .9rem; font-weight: 700; }
        input[type="text"], input[type="password"] { width: 100%; padding: .9rem 1rem; border: 1px solid #d9e3ee; border-radius: .6rem; outline: none; color: var(--ink); font: inherit; transition: border-color .2s, box-shadow .2s; }
        input:focus { border-color: var(--cyan); box-shadow: 0 0 0 .22rem rgba(45,196,234,.18); }
        .error { margin: .45rem 0 0; color: #c53030; font-size: .84rem; }
        .options { display: flex; align-items: center; gap: .55rem; margin: 1.5rem 0; color: #68708a; font-size: .9rem; }
        .options input { accent-color: var(--blue); }
        .submit-button { width: 100%; padding: .95rem 1.2rem; border: 0; border-radius: .65rem; background: var(--blue); box-shadow: 0 .7rem 1.3rem rgba(43,53,147,.22); color: #fff; cursor: pointer; font: 700 1rem "Segoe UI", Arial, sans-serif; transition: background .2s, transform .2s; }
        .submit-button:hover, .submit-button:focus-visible { background: var(--cyan); transform: translateY(-2px); }
        .login-footer { margin-top: 1.5rem; color: #78819a; font-size: .82rem; text-align: center; }

        @media (max-width: 800px) { .page { grid-template-columns: 1fr; } .welcome-panel { min-height: auto; padding: 2rem; } .welcome-content { margin: 2rem 0 0; padding: 0; } .dorcas-logo { display: none; } .welcome-footer { margin-top: 2rem; } .login-panel { padding: 3rem 1.5rem; } }
    </style>
</head>
<body>
    <main class="page">
        <section class="welcome-panel">
            <header class="brand">
                <img src="{{ asset('images/logosetram.png') }}" alt="SETRAM">
                <span>Dorcas</span>
            </header>

            <div class="welcome-content">
                <p>Bienvenue</p>
                <h1>Votre espace<br>de suivi Dorcas.</h1>
                <div class="description">Retrouvez vos outils de gestion et vos informations dans un espace sécurisé.</div>
                <img class="dorcas-logo" src="{{ asset('images/logo dorcas.jpg') }}" alt="Dorcas">
            </div>

            <footer class="welcome-footer">© {{ date('Y') }} SETRAM — Dorcas</footer>
        </section>

        <section class="login-panel" aria-label="Connexion">
            <div class="login-box">
                <h2>Connexion</h2>
                <p class="login-intro">Saisissez vos identifiants pour accéder à votre compte.</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="field">
                        <label for="matricule">Adresse matricule</label>
                        <input id="matricule" type="text" name="matricule" value="{{ old('matricule') }}" required autofocus autocomplete="matricule" placeholder="310">
                        @error('matricule')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Mot de passe</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                        @error('password')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <label class="options" for="remember"><input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Se souvenir de moi</label>
                    <button class="submit-button" type="submit">Se connecter</button>
                </form>

                <p class="login-footer">Accès réservé aux utilisateurs autorisés.</p>
            </div>
        </section>
    </main>
</body>
</html>
