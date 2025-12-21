<?php

namespace App\Http\Controllers;

use App\Models\Exces;
use App\Models\Course;
use App\Models\Conducteur;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatistiqueController extends Controller
{
    // Page répartition par catégorie
    public function categories()
    {
        return view('statistiques.categories');
    }

    // Page évolution par excès
    public function evolution()
    {
        // Vérifier les autorisations pour les onglets supplémentaires
        $user = auth()->user();
        $showTabs = $user->profil == 'DG' ||
                    in_array($user->matricule, ['310040', '310020']) ||
                    $user->profil == 'managerR';

        return view('statistiques.evolution', compact('showTabs'));
    }

    // Page répartition par conducteur
    public function conducteurs()
    {
        return view('statistiques.conducteurs');
    }

    // Page répartition par inter-station
    public function interstations()
    {
        $site = session('site', 'ALG');
        return view('statistiques.interstations', compact('site'));
    }

    // Page synthèse par mois
    public function mensuelle()
    {
        return view('statistiques.mensuelle');
    }



    // API pour les données d'excès (avec filtres)
    public function apiExces(Request $request)
    {
        $site = session('site');

        // Requête de base pour les excès
        $queryExces = Exces::select(
                'exces.*',
                'courses.ladate',
                'courses.voie',
                'courses.RAME',
                'courses.code',
                'conducteurs.nom',
                'conducteurs.prenom'
            )
            ->join('courses', 'exces.idcourse', '=', 'courses.idcourse')
            ->join('conducteurs', 'courses.matricule', '=', 'conducteurs.matricule')
            ->where('courses.site', $site)
            ->where('courses.valide', true);

        // Requête de base pour les courses
        $queryCourses = Course::with('conducteur')
            ->where('site', $site)
            ->where('valide', true);

        // Appliquer les filtres de date
        if ($request->has('debut') && $request->has('fin')) {
            $debut = Carbon::createFromFormat('d/m/Y', $request->debut)->startOfDay();
            $fin = Carbon::createFromFormat('d/m/Y', $request->fin)->endOfDay();

            $queryExces->whereBetween('courses.ladate', [$debut, $fin]);
            $queryCourses->whereBetween('ladate', [$debut, $fin]);
        }

        $exces = $queryExces->get()->map(function ($exce) {
            return [
                'id' => $exce->idexces,
                'idcourse' => $exce->idcourse,
                'ladate' => $exce->ladate,
                'categorie' => $exce->categorie,
                'voie' => $exce->voie,
                'distance' => $exce->fin - $exce->debut,
                'matricule' => $exce->conducteur->matricule,
                'nom' => $exce->conducteur->nom . ' ' . $exce->conducteur->prenom,
                'interstation' => $exce->interstation,
                'code' => str_pad($exce->code, 4, '0', STR_PAD_LEFT),
                'detail' => $exce->detail,
                'aire' => $exce->aire,
                'max' => $exce->maxx,
                'RAME' => $exce->RAME,
                'autorise' => $exce->autorise,
                'debut' => $exce->debut,
                'fin' => $exce->fin
            ];
        });

        $courses = $queryCourses->get()->map(function ($course) {
            return [
                'idcourse' => $course->idcourse,
                'ladate' => $course->ladate,
                'voie' => $course->voie,
                'matricule' => $course->matricule,
                'nom' => $course->conducteur->nom . ' ' . $course->conducteur->prenom,
                'fichier' => $course->fichier,
                'heure' => $course->heure,
                'debut' => $course->debut,
                'fin' => $course->fin,
                'code' => str_pad($course->code, 4, '0', STR_PAD_LEFT),
                'discom' => $course->dis_com,
                'rame' => $course->RAME,
                'FU' => $course->FU,
                'klaxon' => $course->klaxon,
                'patin' => $course->patin,
                'gong' => $course->gong,
                'SA' => $course->SA,
                'SV' => $course->SV
            ];
        });

        return response()->json([
            'exces' => $exces,
            'courses' => $courses
        ]);
    }

    // API pour tous les excès (sans filtre valide)
    public function apiExcesTous(Request $request)
    {
        $site = session('site');

        $exces = Exces::select(
                'exces.*',
                'courses.ladate',
                'courses.voie',
                'courses.RAME',
                'courses.code',
                'courses.valide as enregistre',
                'conducteurs.nom',
                'conducteurs.prenom'
            )
            ->join('courses', 'exces.idcourse', '=', 'courses.idcourse')
            ->join('conducteurs', 'courses.matricule', '=', 'conducteurs.matricule')
            ->where('courses.site', $site)
            ->get()
            ->map(function ($exce) {
                return [
                    'id' => $exce->idexces,
                    'idcourse' => $exce->idcourse,
                    'ladate' => $exce->ladate,
                    'categorie' => $exce->categorie,
                    'voie' => $exce->voie,
                    'matricule' => $exce->conducteur->matricule,
                    'nom' => $exce->conducteur->nom . ' ' . $exce->conducteur->prenom,
                    'interstation' => $exce->interstation,
                    'code' => str_pad($exce->code, 4, '0', STR_PAD_LEFT),
                    'detail' => $exce->detail,
                    'enregistre' => $exce->enregistre,
                    'commentaire' => $exce->commentaire,
                    'aire' => $exce->aire,
                    'max' => $exce->maxx,
                    'RAME' => $exce->RAME,
                    'autorise' => $exce->autorise
                ];
            });

        return response()->json([
            'exces' => $exces
        ]);
    }

    // API pour le journal d'activités
    public function apiJournal(Request $request)
    {
        $query = LogActivity::query();

        if ($request->has('debut') && $request->has('fin')) {
            $debut = Carbon::parse($request->debut)->startOfDay();
            $fin = Carbon::parse($request->fin)->endOfDay();
            $query->whereBetween('created_at', [$debut, $fin]);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'date' => $log->created_at->format('Y-m-d'),
                    'heure' => $log->created_at->format('H:i:s'),
                    'utilisateur' => $log->user->name ?? 'Inconnu',
                    'matricule' => $log->user->matricule ?? '',
                    'action' => $log->action,
                    'type' => $log->type,
                    'details' => $log->details,
                    'module' => $log->module
                ];
            });

        return response()->json([
            'logs' => $logs
        ]);
    }

    // Export CSV
    public function exportCSV(Request $request)
    {
        // Implémentez l'export CSV ici
        // ...
    }
}
