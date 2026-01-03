<?php

namespace App\Http\Controllers;

use App\Models\Freinage;
use App\Models\Course;
use App\Models\Conducteur;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatFreinageController extends Controller
{
    // Page répartition par catégorie
    public function categories()
    {
        return view('StatFreinages.categories');
    }

    // Page évolution par excès
    public function evolution()
    {
        // Vérifier les autorisations pour les onglets supplémentaires
        $user = auth()->user();
        $showTabs = $user->profil == 'DG' ||
                    in_array($user->matricule, ['310040', '310020']) ||
                    $user->profil == 'managerR';

        return view('StatFreinages.evolution', compact('showTabs'));
    }

    // Page répartition par conducteur
    public function conducteurs()
    {
        return view('StatFreinages.conducteurs');
    }

    // Page répartition par inter-station
    public function interstations()
    {
        $site = session('site', 'ALG');
        return view('StatFreinages.interstations', compact('site'));
    }

    // Page synthèse par mois
    public function mensuelle()
    {
        return view('StatFreinages.mensuelle');
    }



    // API pour les données de freinage (avec filtres)
    public function apiFreinage(Request $request)
    {
        $site = session('site', 'ALG');

        try {
            /* =========================
            * Requête FREINAGE
            * ========================= */
            $queryFreinage = Freinage::select(
                    'freinage.id',
                    'freinage.type',
                    'freinage.vitesse',
                    'freinage.interstation',
                    'freinage.details',
                    'freinage.heure',
                    'freinage.idcourse',
                    'courses.ladate',
                    'courses.code',
                    'courses.matricule',
                    'enveloppe.voie as voie_enveloppe',
                    'conducteurs.nom',
                    'conducteurs.prenom'
                )
                ->join('courses', 'freinage.idcourse', '=', 'courses.idcourse')
                ->leftJoin('enveloppe', 'courses.idenveloppe', '=', 'enveloppe.idenveloppe')
                ->leftJoin('conducteurs', 'courses.matricule', '=', 'conducteurs.matricule')
                ->where('courses.site', $site)
                ->where('courses.valide', true);

            /* =========================
            * Requête COURSES
            * ========================= */
            $queryCourses = Course::select(
                    'courses.*',
                    'enveloppe.voie as voie_enveloppe'
                )
                ->leftJoin('enveloppe', 'courses.idenveloppe', '=', 'enveloppe.idenveloppe')
                ->with('conducteur')
                ->where('courses.site', $site)
                ->where('courses.valide', true);

            /* =========================
            * Filtres de dates
            * ========================= */
            if ($request->filled(['debut', 'fin'])) {
                $debut = Carbon::createFromFormat('d/m/Y', $request->debut)->startOfDay();
                $fin   = Carbon::createFromFormat('d/m/Y', $request->fin)->endOfDay();

                $queryFreinage->whereBetween('courses.ladate', [$debut, $fin]);
                $queryCourses->whereBetween('ladate', [$debut, $fin]);
            }

            /* =========================
            * Mapping FREINAGE
            * ========================= */
            $freinages = $queryFreinage->get()->map(function ($f) {
                $nomComplet = trim(($f->nom ?? '') . ' ' . ($f->prenom ?? ''));

                return [
                    'id'            => $f->id,
                    'idcourse'      => $f->idcourse,
                    'ladate'        => $f->ladate,
                    'type'          => $f->type,
                    'vitesse'       => $f->vitesse,
                    'interstation'  => $f->interstation,
                    'details'       => $f->details,
                    'heure'         => $f->heure,
                    'voie'          => $f->voie_enveloppe,
                    'matricule'     => $f->matricule,
                    'nom'           => $nomComplet,
                    'code'          => $f->code !== null
                                        ? str_pad($f->code, 4, '0', STR_PAD_LEFT)
                                        : null,
                ];
            });

            /* =========================
            * Mapping COURSES
            * ========================= */
            $courses = $queryCourses->get()->map(function ($course) {
                $nomComplet = $course->conducteur
                    ? trim(($course->conducteur->nom ?? '') . ' ' . ($course->conducteur->prenom ?? ''))
                    : '';

                return [
                    'idcourse' => $course->idcourse,
                    'ladate'   => $course->ladate,
                    'voie'     => $course->voie_enveloppe,
                    'matricule'=> $course->matricule,
                    'nom'      => $nomComplet,
                    'fichier'  => $course->fichier,
                    'heure'    => $course->heure,
                    'debut'    => $course->debut,
                    'fin'      => $course->fin,
                    'code'     => $course->code !== null
                                    ? str_pad($course->code, 4, '0', STR_PAD_LEFT)
                                    : null,
                    'discom'   => $course->dis_com,
                    'rame'     => $course->RAME,
                    'FU'       => $course->FU,
                    'klaxon'   => $course->klaxon,
                    'patin'    => $course->patin,
                    'gong'     => $course->gong,
                    'SA'       => $course->SA,
                    'SV'       => $course->SV,
                ];
            });

            return response()->json([
                'freinages' => $freinages,
                'courses'   => $courses
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement des données',
                'error'   => config('app.debug') ? $e->getMessage() : null,
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
