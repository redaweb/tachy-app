<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Conducteur;
use App\Models\Course;
use App\Models\Enveloppe;
use App\Models\Exces;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'conducteurs' => Conducteur::count(),
            'courses' => Course::count(),
            'enveloppes' => Enveloppe::count(),
            'exces' => Exces::count(),
        ];

        $recentCourses = Course::with('conducteur')
            ->orderBy('ladate', 'desc')
            ->limit(5)
            ->get();

        $recentExces = Exces::with('course.conducteur')
            ->orderBy('idexce', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentCourses', 'recentExces'));
    }
}