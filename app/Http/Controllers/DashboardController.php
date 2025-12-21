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
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        // Récupérer le site depuis la session, ou utiliser celui de l'utilisateur connecté, ou 'ALG' par défaut
        $site = Session::get('site') ?? auth()->user()->site ?? 'ALG';

        // Si la session n'a pas de site, l'initialiser
        if (!Session::has('site')) {
            Session::put('site', $site);
        }
        $courseIds = Course::where('site', $site)->where('valide', true)
            ->pluck('idcourse');

        $stats = cache()->remember("dashboard_stats_{$site}", 300, function () use ($site, $courseIds) {
            return [
                'conducteurs' => Conducteur::where('site', $site)->count(),
                'courses'     => $courseIds->count(),
                'enveloppes'  => Enveloppe::where('site', $site)->count(),
                'exces'       => Exces::whereIn('idcourse', $courseIds)->count(),
                'freinages'   => Freinage::whereIn('idcourse', $courseIds)->count(),
                'journal'     => Journal::where('site', $site)->count(),
                'courseCsv'   => CourseCsv::whereIn('idcourse', $courseIds)->count(),
                'users'       => User::count(),
            ];
        });



        $recentCourses = Course::with('conducteur')
            ->where('site', $site)->where('valide', true)
            ->orderBy('ladate', 'desc')
            ->limit(5)
            ->get();

        $recentExces = Exces::with('course.conducteur')
            ->whereHas('course', function ($query) use ($site) {
                $query->where('site', $site)->where('valide', true);
            })
            ->orderBy('idexce', 'desc')
            ->limit(5)
            ->get();

        $recentFreinages = Freinage::with('course.conducteur')
            ->whereHas('course', function ($query) use ($site) {
                $query->where('site', $site)->where('valide', true);
            })
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $recentJournal = Journal::orderBy('ladate', 'desc')
            ->where('site', $site)
            ->orderBy('heure', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentCourses', 'recentExces', 'recentFreinages', 'recentJournal'));
    }
}
