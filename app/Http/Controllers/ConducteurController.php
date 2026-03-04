<?php
// app/Http/Controllers/ConducteurController.php

namespace App\Http\Controllers;

use App\Models\Conducteur;
use Illuminate\Http\Request;

class ConducteurController extends Controller
{
    public function index(Request $request)
    {
        $query = Conducteur::query()->withCount('courses');

        // Recherche par matricule, nom ou prénom
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('matricule', 'like', '%' . $search . '%')
                  ->orWhere('nom', 'like', '%' . $search . '%')
                  ->orWhere('prenom', 'like', '%' . $search . '%');
            });
        }

        // Filtre par site
        if ($request->has('site') && !empty($request->site)) {
            $query->where('site', $request->site);
        }

        // Filtre par nombre de courses minimum
        if ($request->has('min_courses') && is_numeric($request->min_courses)) {
            $query->having('courses_count', '>=', $request->min_courses);
        }

        // Tri
        $sortField = $request->get('sort', 'matricule');
        $sortDirection = $request->get('direction', 'asc');

        if (in_array($sortField, ['matricule', 'nom', 'prenom', 'site', 'courses_count'])) {
            if ($sortField === 'courses_count') {
                $query->orderBy('courses_count', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        // Pagination de 10 éléments par page
        $conducteurs = $query->paginate(10);

        // Récupérer la liste des sites uniques pour le filtre
        $sites = Conducteur::distinct()->whereNotNull('site')->pluck('site');

        return view('conducteurs.index', compact('conducteurs', 'sites'));
    }

    /**
     * API de recherche rapide pour l'autocomplétion
     */
    public function search(Request $request)
    {
        $term = $request->get('q');
        $site = $request->get('site');

        $query = Conducteur::query();

        if ($term) {
            $query->where(function($q) use ($term) {
                $q->where('matricule', 'like', '%' . $term . '%')
                  ->orWhere('nom', 'like', '%' . $term . '%')
                  ->orWhere('prenom', 'like', '%' . $term . '%');
            });
        }

        if ($site) {
            $query->where('site', $site);
        }

        $conducteurs = $query->limit(10)->get(['matricule', 'nom', 'prenom', 'site']);

        return response()->json($conducteurs);
    }

    public function create()
    {
        return view('conducteurs.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'site' => 'nullable|string|max:5',
        ]);

        Conducteur::create($validated);

        return redirect()->route('conducteurs.index')
                        ->with('success', 'Conducteur créé avec succès.');
    }

    public function show($matricule)
    {
        $conducteur = Conducteur::with(['courses.enveloppe', 'courses.exces'])
                               ->findOrFail($matricule);

        return view('conducteurs.show', compact('conducteur'));
    }

    public function edit($matricule)
    {
        $conducteur = Conducteur::findOrFail($matricule);
        return view('conducteurs.form', compact('conducteur'));
    }

    public function update(Request $request, $matricule)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'site' => 'nullable|string|max:5',
        ]);

        $conducteur = Conducteur::findOrFail($matricule);
        $conducteur->update($validated);

        return redirect()->route('conducteurs.index')
                        ->with('success', 'Conducteur modifié avec succès.');
    }

    public function destroy($matricule)
    {
        $conducteur = Conducteur::findOrFail($matricule);
        $conducteur->delete();

        return redirect()->route('conducteurs.index')
                        ->with('success', 'Conducteur supprimé avec succès.');
    }
}
