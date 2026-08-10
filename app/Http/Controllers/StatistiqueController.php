<?php

namespace App\Http\Controllers;

use App\Models\Exces;
use App\Models\Course;
use App\Models\Conducteur;
use App\Models\Journal;
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
                    in_array($user->matricule, [310040, 310020]) ||
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

    // Page journal
    public function journal()
    {
        return view('journal');
    }

    // API pour les données du journal
    public function apiJournal(Request $request)
    {
        $site = session('site', 'ALG');

        try {
            // Récupérer les données du journal
            $query = Journal::where('site', $site);

            if ($request->has('debut') && $request->has('fin')) {
                $debut = $request->debut;
                $fin = $request->fin;
                $query->whereBetween('ladate', [$debut, $fin]);
            }

            $journal = $query->orderBy('ladate', 'desc')
                ->orderBy('heure', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'iduser' => $item->matricule,
                        'nom' => $item->nom,
                        'ladate' => $item->ladate,
                        'heure' => $item->heure,
                        'action' => $item->action,
                        'detail' => $item->detail
                    ];
                })
                ->toArray();

            // Récupérer les courses ouvertes non enregistrées
            $coursesQuery = Course::select(
                    'courses.*',
                    \DB::raw("(SELECT nom FROM journal WHERE action='consulter la course' AND detail=courses.idcourse ORDER BY ladate DESC LIMIT 1) as ouvertPar"),
                    'enveloppe.nom as nomenveloppe'
                )
                ->leftJoin('enveloppe', 'courses.idenveloppe', '=', 'enveloppe.idenveloppe')
                ->where('courses.site', $site)
                ->where(function ($q) {
                    $q->whereNull('courses.valide')->orWhere('courses.valide', false);
                });

            if ($request->has('debut') && $request->has('fin')) {
                $debut = $request->debut;
                $fin = $request->fin;
                $coursesQuery->whereBetween('courses.ladate', [$debut, $fin]);
            }

            $courses = $coursesQuery->get()
                ->map(function ($item) {
                    return [
                        'idcourse' => $item->idcourse,
                        'ladate' => $item->ladate,
                        'heure' => $item->heure,
                        'distance' => $item->distance,
                        'nomenveloppe' => $item->nomenveloppe ?? 'N/A',
                        'commentaire' => $item->commentaire,
                        'ouvertPar' => $item->ouvertPar
                    ];
                })
                ->toArray();

            return response()->json([
                'journal' => $journal,
                'courses' => $courses
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement du journal',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // Page tous les excès
    public function tousExces()
    {
        return view('statistiques.tous-exces');
    }



    // API pour les données d'excès (avec filtres)
    public function apiExces(Request $request)
    {
        $site = session('site', 'ALG');

        try {
            // Requête de base pour les excès (jointure + colonnes nécessaires)
            $queryExces = Exces::select(
                    'exces.idexce',
                    'exces.idcourse',
                    'exces.categorie',
                    'exces.interstation',
                    'exces.debut',
                    'exces.fin',
                    'exces.detail',
                    'exces.aire',
                    'exces.maxx',
                    'exces.autorise',
                    'courses.ladate',
                    'courses.code',
                    'courses.matricule',
                    'enveloppe.voie as voie_enveloppe',
                    'conducteurs.nom',
                    'conducteurs.prenom'
                )
                ->join('courses', 'exces.idcourse', '=', 'courses.idcourse')
                ->leftJoin('enveloppe', 'courses.idenveloppe', '=', 'enveloppe.idenveloppe')
                ->leftJoin('conducteurs', 'courses.matricule', '=', 'conducteurs.matricule')
                ->where('courses.site', $site)
                ->where('courses.valide', true);

            // Requête de base pour les courses
            $queryCourses = Course::select(
                    'courses.*',
                    'enveloppe.voie as voie_enveloppe'
                )
                ->leftJoin('enveloppe', 'courses.idenveloppe', '=', 'enveloppe.idenveloppe')
                ->with('conducteur')
                ->where('courses.site', $site)
                ->where('courses.valide', true);

            // Appliquer les filtres de date
            if ($request->has('debut') && $request->has('fin')) {
                $debut = Carbon::createFromFormat('d/m/Y', $request->debut)->startOfDay();
                $fin = Carbon::createFromFormat('d/m/Y', $request->fin)->endOfDay();

                $queryExces->whereBetween('courses.ladate', [$debut, $fin]);
                $queryCourses->whereBetween('ladate', [$debut, $fin]);
            }

            $exces = $queryExces->get()->map(function ($exce) {
                $nomComplet = trim(($exce->nom ?? '') . ' ' . ($exce->prenom ?? ''));

                return [
                    'id' => $exce->idexce,
                    'idcourse' => $exce->idcourse,
                    'ladate' => $exce->ladate,
                    'categorie' => $exce->categorie,
                    'voie' => $exce->voie_enveloppe,
                    'distance' => $exce->fin - $exce->debut,
                    'matricule' => $exce->matricule,
                    'nom' => $nomComplet,
                    'interstation' => $exce->interstation,
                    'code' => $exce->code !== null ? str_pad($exce->code, 4, '0', STR_PAD_LEFT) : null,
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
                $nomComplet = $course->conducteur
                    ? trim(($course->conducteur->nom ?? '') . ' ' . ($course->conducteur->prenom ?? ''))
                    : '';

                return [
                    'idcourse' => $course->idcourse,
                    'ladate' => $course->ladate,
                    'voie' => $course->voie_enveloppe,
                    'matricule' => $course->matricule,
                    'nom' => $nomComplet,
                    'fichier' => $course->fichier,
                    'heure' => $course->heure,
                    'debut' => $course->debut,
                    'fin' => $course->fin,
                    'code' => $course->code !== null ? str_pad($course->code, 4, '0', STR_PAD_LEFT) : null,
                    'discom' => $course->distance,
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
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement des données',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }



    // Export CSV
    public function exportCSV(Request $request)
    {
        // Implémentez l'export CSV ici
        // ...
    }
}
