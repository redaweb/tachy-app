<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /*public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            // Seuls les admins, superadmin et DG peuvent gérer les utilisateurs
            if (!in_array(auth()->user()->profil, ['admin', 'superadmin', 'DG']) || true) {
                abort(403, 'Accès non autorisé.');
            }
            return $next($request);
        })->except(['showLoginForm', 'login', 'logout']);
    }*/
    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Recherche par nom
        if ($request->has('search') && !empty($request->search)) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        // Filtre par profil
        if ($request->has('profil') && !empty($request->profil)) {
            $query->where('profil', $request->profil);
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('envBloque', $request->statut);
        }

        // Filtre par site
        if ($request->has('site') && !empty($request->site)) {
            $query->where('site', $request->site);
        }

        // Pagination de 10 éléments par page
        $users = $query->orderBy('iduser', 'desc')->paginate(10);

        // Récupérer la liste des profils uniques pour le filtre
        $profils = User::distinct()->pluck('profil');

        // Sites disponibles
        $sites = ['ALG' => 'Alger', 'ORN' => 'Oran', 'CST' => 'Constantine',
                 'SBA' => 'Sidi Bel Abbès', 'ORG' => 'Ouargla',
                 'STF' => 'Sétif', 'MGM' => 'Mostaganem'];

        return view('users.index', compact('users', 'profils', 'sites'));
    }

    /**
     * Mettre à jour le statut d'un utilisateur (actif/bloqué)
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Empêcher de bloquer son propre compte
            if (Auth::id() == $user->iduser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas modifier le statut de votre propre compte.'
                ], 403);
            }

            $user->envBloque = $request->status;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès.',
                'status' => $user->envBloque
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut.'
            ], 500);
        }
    }

    /**
     * API de recherche rapide
     */
    public function search(Request $request)
    {
        $term = $request->get('q');
        $users = User::where('nom', 'like', '%' . $term . '%')
                    ->limit(10)
                    ->get(['iduser', 'nom', 'profil', 'site']);

        return response()->json($users);
    }


    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:users',
            'motpass' => 'required|string|min:6',
            'profil' => 'required|string|in:admin,user,supervisor',
            'site' => 'nullable|string|max:255',
            'envBloque' => 'sometimes|boolean'
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'motpass' => Hash::make($validated['motpass']),
            'profil' => $validated['profil'],
            'site' => $validated['site'] ?? null,
            'envBloque' => $request->has('envBloque')
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:users,nom,' . $id . ',iduser',
            'profil' => 'required|string|in:admin,user,supervisor',
            'site' => 'nullable|string|max:255',
            'motpass' => 'nullable|string|min:6',
            'envBloque' => 'sometimes|boolean'
        ]);

        $data = [
            'nom' => $validated['nom'],
            'profil' => $validated['profil'],
            'site' => $validated['site'] ?? null,
            'envBloque' => $request->has('envBloque')
        ];

        if (!empty($validated['motpass'])) {
            $data['motpass'] = Hash::make($validated['motpass']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Empêcher la suppression de son propre compte
        if (Auth::id() == $user->iduser) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('users.login');
    }

    /**
     * Traitement de la connexion
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nom' => 'required|string',
            'motpass' => 'required|string',
        ]);

        if (Auth::attempt(['nom' => $credentials['nom'], 'password' => $credentials['motpass']])) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'nom' => 'Les identifiants fournis ne correspondent pas.',
        ])->onlyInput('nom');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
