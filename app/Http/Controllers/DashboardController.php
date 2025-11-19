<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Conducteur;
use App\Models\Course;
use App\Models\CourseCsv;
use App\Models\Enveloppe;
use App\Models\Exces;
use App\Models\Freinage;
use App\Models\Journal;
use App\Models\User;
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
            'freinages' => Freinage::count(),
            'journal' => Journal::count(),
            'courseCsv' => CourseCsv::count(),
            'users' => User::count(),
        ];

        $recentCourses = Course::with('conducteur')
            ->orderBy('ladate', 'desc')
            ->limit(5)
            ->get();

        $recentExces = Exces::with('course.conducteur')
            ->orderBy('idexce', 'desc')
            ->limit(5)
            ->get();

        $recentFreinages = Freinage::with('course.conducteur')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $recentJournal = Journal::orderBy('ladate', 'desc')
            ->orderBy('heure', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentCourses', 'recentExces', 'recentFreinages', 'recentJournal'));
    }
}