<?php
// app/Http/Controllers/ExcesController.php

namespace App\Http\Controllers;

use App\Models\Exces;
use App\Models\Course;
use Illuminate\Http\Request;

class ExcesController extends Controller
{
    public function index()
    {
        $exces = Exces::with('course.conducteur')
                     ->orderBy('idexce', 'desc')
                     ->paginate(15);
        
        return view('exces.index', compact('exces'));
    }

    public function create()
    {
        $courses = Course::all();
        return view('exces.form', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'idcourse' => 'required|exists:courses,idcourse',
            'aire' => 'nullable|integer',
            'maxx' => 'required|integer',
            'autorise' => 'required|integer',
            'categorie' => 'nullable|string|max:50',
            'interstation' => 'nullable|string|max:100',
            'debut' => 'nullable|integer',
            'fin' => 'nullable|integer',
            'detail' => 'nullable|string|max:100',
        ]);

        Exces::create($validated);

        return redirect()->route('exces.index')
                        ->with('success', 'Excès enregistré avec succès.');
    }

    public function show($idexce)
    {
        $exces = Exces::with('course.conducteur')
                     ->findOrFail($idexce);
        
        return view('exces.show', compact('exces'));
    }

    public function edit($idexce)
    {
        $exces = Exces::findOrFail($idexce);
        $courses = Course::all();
        
        return view('exces.form', compact('exces', 'courses'));
    }

    public function update(Request $request, $idexce)
    {
        $validated = $request->validate([
            'idcourse' => 'required|exists:courses,idcourse',
            'aire' => 'nullable|integer',
            'maxx' => 'required|integer',
            'autorise' => 'required|integer',
            'categorie' => 'nullable|string|max:50',
            'interstation' => 'nullable|string|max:100',
            'debut' => 'nullable|integer',
            'fin' => 'nullable|integer',
            'detail' => 'nullable|string|max:100',
        ]);

        $exces = Exces::findOrFail($idexce);
        $exces->update($validated);

        return redirect()->route('exces.index')
                        ->with('success', 'Excès modifié avec succès.');
    }

    public function destroy($idexce)
    {
        $exces = Exces::findOrFail($idexce);
        $exces->delete();

        return redirect()->route('exces.index')
                        ->with('success', 'Excès supprimé avec succès.');
    }
}