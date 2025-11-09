<?php
// app/Http/Controllers/ConducteurController.php

namespace App\Http\Controllers;

use App\Models\Conducteur;
use Illuminate\Http\Request;

class ConducteurController extends Controller
{
    public function index()
    {
        // Remplacer get() par paginate()
        $conducteurs = Conducteur::withCount('courses')
                                ->orderBy('matricule', 'desc')
                                ->paginate(10); // 10 éléments par page
        
        return view('conducteurs.index', compact('conducteurs'));
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