<?php

namespace App\Http\Controllers;

use App\Helpers\EnveloppeHelper;
use App\Models\Course;
use App\Models\CourseCsv;
use App\Models\Enveloppe;
use App\Models\Exces;
use App\Models\Journal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
        * Gérer l'upload de fichiers CSV
    */

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:100240' // 10MB max
        ]);

        try {
            DB::beginTransaction();

            $site = session('site', 'ALG');
            $file = $request->file('csv_file');
            $originalName = $file->getClientOriginalName();

            // Vérifier si le fichier existe déjà
            $storagePath = "enregistrement/{$site}/";
            if (Storage::exists($storagePath . $originalName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Echec dans l\'importation du fichier (fichier déjà existant)'
                ], 400);
            }

            // Stocker le fichier
            $filePath = $file->storeAs($storagePath, $originalName);

            // Traiter le fichier CSV
            $this->processCSVFile($filePath, $site, $originalName);

            DB::commit();

            // Supprimer le fichier après traitement
            Storage::delete($filePath);

            return redirect()->route('courses.index')->with('success', 'Le fichier a été importé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur upload CSV: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Echec dans l\'importation du fichier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traiter le fichier CSV et créer les courses
     */
    private function processCSVFile($filePath, $site, $fileName)
    {
        $fichier = [];
        $lignecsv = [];
        $ln = 1;

        // Configuration CSV selon le site
        $csvSiteKey = $site === 'ALG' ? 'ALGER' : $site;
        $csvConfig = config('csv.' . $csvSiteKey, config('csv.ALGER'));

        if (($handle = fopen(storage_path('app/' . $filePath), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($ln > 2) {
                    $fichier[$ln-2] = [
                        $data[1], // temps
                        $data[8], // M1
                        $data[9], // M2
                        str_replace(",", ".", $data[0]) // km
                    ];

                    $lignecsv[$ln-2] = [
                        "km" => str_replace(",", ".", $data[$csvConfig['km']]),
                        "temps" => $data[$csvConfig['temps']],
                        "vitesse" => str_replace(",", ".", $data[$csvConfig['vitesse']]),
                        "dsstop" => $data[$csvConfig['dsstop']],
                        "FU" => $data[$csvConfig['FU']],
                        "M1" => $data[$csvConfig['M1']],
                        "M2" => $data[$csvConfig['M2']],
                        "gong" => $data[$csvConfig['gong']],
                        "freinage" => $data[$csvConfig['freinage']],
                        "traction" => $data[$csvConfig['traction']],
                        "patin" => $data[$csvConfig['patin']],
                        "klaxon" => $data[$csvConfig['klaxon']]
                    ];
                }
                $ln++;
            }
            fclose($handle);
        }

        // Détecter les courses
        $listecourse = $this->detectCourses($fichier, $fileName, $site);

        // Insérer dans la base de données
        $this->insertCoursesAndData($listecourse, $fichier, $lignecsv, $site);
    }



    /**
     * Détecter les courses dans le fichier
     */
    private function detectCourses($fichier, $fileName, $site)
    {
        $listecourse = [];
        $nbcourses = 0;
        $listecourse[$nbcourses][0] = $fileName;

        // Trouver le début de la première course
        $trouve = FALSE;
        $i = 1;
        for (; !$trouve && isset($fichier[$i]); $i++) {
            if ($fichier[$i][1] == 1 || $fichier[$i][2] == 1) {
                $trouve = TRUE;
            }
        }

        if (!$trouve) {
            throw new \Exception("Aucune course détectée dans le fichier");
        }

        $cabine = ($fichier[$i][1] == 1) ? 1 : 2;
        $listecourse[$nbcourses][1] = $fichier[$i][0]; // temps
        $listecourse[$nbcourses][2] = $cabine;
        $k = $i + 2;
        $listecourse[$nbcourses][3] = $k; // début

        // Détecter les changements de cabine
        for ($j = $i; isset($fichier[$j]); $j++) {
            if ($cabine == 1 && $fichier[$j][2] == 1) {
                $cabine = 2;
                $k = $j + 1;
                $listecourse[$nbcourses][4] = $k; // fin
                $nbcourses++;
                $listecourse[$nbcourses][0] = $fileName;
                $listecourse[$nbcourses][1] = $fichier[$j][0];
                $listecourse[$nbcourses][2] = $cabine;
                $k = $j + 2;
                $listecourse[$nbcourses][3] = $k;
            } elseif ($cabine == 2 && $fichier[$j][1] == 1) {
                $cabine = 1;
                $k = $j + 1;
                $listecourse[$nbcourses][4] = $k;
                $nbcourses++;
                $listecourse[$nbcourses][0] = $fileName;
                $listecourse[$nbcourses][1] = $fichier[$j][0];
                $listecourse[$nbcourses][2] = $cabine;
                $k = $j + 2;
                $listecourse[$nbcourses][3] = $k;
            }
        }

        // Fin de la dernière course
        $listecourse[$nbcourses][4] = count($fichier) + 2;

        // Supprimer les courses de 0 distance
        return $this->cleanZeroDistanceCourses($listecourse, $fichier, $nbcourses);
    }

    /**
     * Nettoyer les courses avec distance 0
     */
    private function cleanZeroDistanceCourses($listecourse, $fichier, $nbcourses)
    {
        for ($i = 0; $i <= $nbcourses; $i++) {
            $listecourse[$i][5] = ($fichier[$listecourse[$i][4] - 2][3] - $fichier[$listecourse[$i][3] - 2][3]) * 1000;

            if ($i > 0 && $i < $nbcourses && $listecourse[$i][5] == 0) {
                $listecourse[$i-1][4] = $listecourse[$i+1][4];
                $listecourse[$i-1][5] = ($fichier[$listecourse[$i-1][4] - 2][3] - $fichier[$listecourse[$i-1][3] - 2][3]) * 1000;

                for ($j = $i; $j <= $nbcourses - 2; $j++) {
                    $listecourse[$j] = $listecourse[$j + 2];
                }
                $nbcourses -= 2;
                $i--; // Revenir en arrière pour vérifier à nouveau
            }
        }

        return array_slice($listecourse, 0, $nbcourses + 1);
    }

    /**
     * Insérer les courses et données CSV
     */
    private function insertCoursesAndData($listecourse, $fichier, $lignecsv, $site)
    {
        foreach ($listecourse as $i => $course) {
            // Calculer la distance
            $distance = ($fichier[$course[4] - 2][3] - $fichier[$course[3] - 2][3]) * 1000;

            // Formater la date
            $datetime = $course[1];
            $ladate = Carbon::createFromFormat('d/m/Y H:i:s', $datetime)->format('Y-m-d');
            $heure = Carbon::createFromFormat('d/m/Y H:i:s', $datetime)->format('H:i:s');

            // Créer la course
            $courseModel = Course::create([
                'fichier' => $course[0],
                'debut' => $course[3],
                'fin' => $course[4],
                'distance' => $distance,
                'ladate' => $ladate,
                'heure' => $heure,
                'importation' => now(),
                'site' => $site,
                'source' => 'BDD'
            ]);

            $listecourse[$i][7] = $courseModel->idcourse;

            // Insérer les données CSV
            $this->insertCourseCsvData($courseModel->idcourse, $course, $lignecsv);
        }
    }

    /**
     * Insérer les données CSV de la course
     */
    private function insertCourseCsvData($idcourse, $course, $lignecsv)
    {
        $courses = [];
        $lncourse = 0;

        for ($ln = $course[3] - 2; $ln <= $course[4] - 2; $ln++) {
            if (!isset($lignecsv[$ln])) continue;

            $data = $lignecsv[$ln];

            if ($lncourse == 0 || ($courses[$lncourse-1] && $courses[$lncourse-1]['km'] != $data["km"])) {
                $courses[$lncourse] = $data;
                $courses[$lncourse]["idcourse"] = $idcourse;
                $lncourse++;

                if ($lncourse == 2 && $courses[$lncourse-1]['vitesse'] < 2) {
                    $courses[0] = $courses[1];
                    $lncourse = 1;
                }
            } else {
                // Modifier l'enregistrement existant
                if ($courses[$lncourse-1]['vitesse'] < 4 && $data["vitesse"] < 4) {
                    $courses[$lncourse-1]['vitesse'] = min($courses[$lncourse-1]['vitesse'], $data["vitesse"]);
                } else {
                    $courses[$lncourse-1]['vitesse'] = max($courses[$lncourse-1]['vitesse'], $data["vitesse"]);
                }

                if ($courses[$lncourse-1]['dsstop'] == 1) {
                    $courses[$lncourse-1]['vitesse'] = 0;
                }

                $courses[$lncourse-1]['dsstop'] = max($courses[$lncourse-1]['dsstop'], $data["dsstop"]);
                $courses[$lncourse-1]['M1'] = max($courses[$lncourse-1]['M1'], $data["M1"]);
                $courses[$lncourse-1]['M2'] = max($courses[$lncourse-1]['M2'], $data["M2"]);
                $courses[$lncourse-1]['gong'] = max($courses[$lncourse-1]['gong'], $data["gong"]);
                $courses[$lncourse-1]['freinage'] = max($courses[$lncourse-1]['freinage'], $data["freinage"]);
                $courses[$lncourse-1]['traction'] = max($courses[$lncourse-1]['traction'], $data["traction"]);
            }

            if ($lncourse > 0 && empty($courses[$lncourse-1]['vitesse'])) {
                $courses[$lncourse-1]['vitesse'] = 0;
            }
        }

        // Insérer dans coursecsv
        foreach ($courses as $courseData) {
            $datetime = $courseData['temps'];
            $datetime_mysql = Carbon::createFromFormat('d/m/Y H:i:s', $datetime)->format('Y-m-d H:i:s');

            CourseCsv::create([
                'km' => $courseData['km'],
                'temps' => $datetime_mysql,
                'vitesse' => $courseData['vitesse'],
                'dsstop' => $courseData['dsstop'],
                'FU' => $courseData['FU'],
                'M1' => $courseData['M1'],
                'M2' => $courseData['M2'],
                'gong' => $courseData['gong'],
                'freinage' => $courseData['freinage'],
                'traction' => $courseData['traction'],
                'patin' => $courseData['patin'],
                'klaxon' => $courseData['klaxon'],
                'idcourse' => $courseData['idcourse']
            ]);
        }

    return redirect()->route('courses.index')->with('success', 'Le fichier a été importé avec succès');
    }



    /**
     * Récupérer les événements pour le calendrier
     */
    public function calendar($year, $month)
    {
        try {
            $site = session('site', 'ALG');
            $profil = Auth::user()->profil ?? 'user';

            $events = [];

            // Construction de la requête selon le profil
            $query = Course::where('site', $site)
                ->whereYear('ladate', $year)
                ->whereMonth('ladate', $month);

            if ($profil === "ADMIN" || $profil === "ladmin" || $profil === "user") {
                // Compter total et valides, grouper par date
                $courses = $query->selectRaw('COUNT(*) AS total, SUM(CASE WHEN valide = true THEN 1 ELSE 0 END) AS valide_count, ladate')
                    ->groupBy('ladate')
                    ->orderBy('ladate')
                    ->get();

                foreach ($courses as $row) {
                    $title = sprintf(
                        "%d course%s, %d controlée%s",
                        $row->total,
                        $row->total > 1 ? 's' : '',
                        $row->valide_count ?? 0,
                        ($row->valide_count ?? 0) > 1 ? 's' : ''
                    );

                    $startDate = is_string($row->ladate) ? $row->ladate : $row->ladate->format('Y-m-d');

                    $events[] = [
                        'title' => $title,
                        'url' => route('courses.bydate', ['ladate' => $startDate]),
                        'start' => $startDate
                    ];
                }
            } else if ($profil === "DG" || $profil === "managerR") {
                // Compter valides et idenveloppe
                $courses = $query->selectRaw('SUM(CASE WHEN valide = true THEN 1 ELSE 0 END) AS valide_count, SUM(CASE WHEN idenveloppe IS NOT NULL THEN 1 ELSE 0 END) AS idenveloppe_count, ladate')
                    ->groupBy('ladate')
                    ->orderBy('ladate')
                    ->get();

                foreach ($courses as $row) {
                    $non_controlees = ($row->idenveloppe_count ?? 0) - ($row->valide_count ?? 0);
                    $title = sprintf(
                        "%s %s %s %s",
                        ($row->valide_count ?? 0) > 0 ? $row->valide_count : "",
                        ($row->valide_count ?? 0) > 1 ? 'controlées,' : (($row->valide_count ?? 0) == 0 ? '' : 'controlée,'),
                        $non_controlees > 0 ? $non_controlees : "",
                        $non_controlees > 1 ? 'non controlées' : ($non_controlees == 0 ? '' : 'non controlée')
                    );

                    $startDate = is_string($row->ladate) ? $row->ladate : $row->ladate->format('Y-m-d');

                    $events[] = [
                        'title' => $title,
                        'url' => route('courses.bydate', ['ladate' => $startDate]),
                        'start' => $startDate
                    ];
                }
            } else {
                // Autres profils : compter les controlées
                $courses = $query->where('valide', true)
                    ->selectRaw('COUNT(*) AS total, ladate')
                    ->groupBy('ladate')
                    ->orderBy('ladate')
                    ->get();

                foreach ($courses as $row) {
                    $title = sprintf(
                        "%d controlée%s",
                        $row->total,
                        $row->total > 1 ? 's' : ''
                    );

                    $startDate = is_string($row->ladate) ? $row->ladate : $row->ladate->format('Y-m-d');

                    $events[] = [
                        'title' => $title,
                        'url' => route('courses.bydate', ['ladate' => $startDate]),
                        'start' => $startDate
                    ];
                }
            }

            return response()->json($events);

        } catch (\Exception $e) {
            \Log::error('Erreur calendrier: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Récupérer les statistiques générales (API)
     */
    public function stats()
    {
        try {
            $stats = [
                'total' => Course::count(),
                'valide' => Course::where('valide', true)->count(),
                'non_valide' => Course::where('valide', false)->count(),
                'ce_mois' => Course::whereYear('ladate', now()->year)
                                ->whereMonth('ladate', now()->month)
                                ->count(),
                'avec_exces' => Course::has('exces')->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            \Log::error('Erreur stats: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Recherche de courses (API)
     */
    public function search(Request $request)
    {
        try {
            $query = Course::query();

            if ($request->filled('matricule')) {
                $query->where('matricule', 'LIKE', '%' . $request->matricule . '%');
            }

            if ($request->filled('date')) {
                $query->where('ladate', $request->date);
            }

            if ($request->filled('site')) {
                $query->where('site', $request->site);
            }

            $courses = $query->orderBy('ladate', 'desc')
                            ->orderBy('heure', 'desc')
                            ->limit(50)
                            ->get()
                            ->map(function($course) {
                                return [
                                    'id' => $course->id,
                                    'ladate' => $course->ladate->format('d/m/Y'),
                                    'heure' => $course->heure,
                                    'matricule' => $course->matricule,
                                    'fichier' => $course->fichier,
                                    'code' => $course->code,
                                    'valide' => $course->valide,
                                    'url' => route('courses.show', $course->id)
                                ];
                            });

            return response()->json($courses);

        } catch (\Exception $e) {
            \Log::error('Erreur recherche: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
    /**
     * Enregistrer les informations de la course
     */
    public function enregistrer(Request $request)
    {
        $request->validate([
            'idcourse' => 'required|integer',
            'RAME' => 'required|string',
            'SV' => 'required|string',
            'SA' => 'required|string',
            'matricule' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $course = Course::findOrFail($request->idcourse);

            // Mettre à jour la course
            $course->update([
                'RAME' => $request->RAME,
                'SV' => $request->SV,
                'SA' => $request->SA,
                'matricule' => $request->matricule,
                'valide' => true
            ]);

            // Générer le code si nécessaire (identique à votre logique PHP)
            if (!$course->code && $course->matricule) {
                $annee = $course->ladate->year;
                $site = session('site', 'ALG');

                // Récupérer tous les codes existants pour l'année et le site
                $codesExistants = Course::where('site', $site)
                    ->whereYear('ladate', $annee)
                    ->whereNotNull('code')
                    ->orderBy('code', 'asc')
                    ->pluck('code');

                $nouveauCode = 1;

                // Trouver le premier numéro disponible (identique à votre logique)
                foreach ($codesExistants as $code) {
                    if ($code != $nouveauCode) break;
                    $nouveauCode++;
                }

                $course->update(['code' => $nouveauCode]);
            }

            // Journalisation
            Journal::create([
            'matricule' => Auth::user()->matricule,
            'nom' => Auth::user()->name,
            'ladate' => now()->toDateString(), // Format date seulement
            'heure' => now()->format('H:i:s'), // Format heure seulement
            'action' => 'enregistrer la course',
            'detail' => (string) $request->idcourse, // Stocker l'id de la course dans detail
            'site' => session('site', 'ALG')
        ]);

            DB::commit();

            // Récupérer le code formaté pour l'affichage (identique à votre code PHP)
            $codeFormate = $course->code ? sprintf("%04d", $course->code) . "-" . $course->ladate->year : null;

            return response()->json([
                'message' => 'L\'analyse a été enregistrée avec succès',
                'code' => $codeFormate
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Echec dans l\'enregistrement'], 500);
        }
    }

    /**
     * Ajouter un commentaire
     */
    public function commenter(Request $request)
    {
        $request->validate([
            'idcourse' => 'required|integer',
            'commentaire' => 'required|string'
        ]);

        try {
            $course = Course::findOrFail($request->idcourse);
            $course->update([
                'commentaire' => $request->commentaire
            ]);

            return response()->json(['message' => 'Le commentaire a été ajouté avec succès']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Echec dans l\'enregistrement du commentaire'], 500);
        }
    }

    /**
     * Compter le nombre de contrôles pour un conducteur
     */
    public function nbControles(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string'
        ]);

        $mois = now()->month;
        $annee = now()->year;

        $nbControles = Course::where('matricule', $request->matricule)
            ->where('valide', true)
            ->whereMonth('ladate', $mois)
            ->whereYear('ladate', $annee)
            ->count();

        return response()->json(['nbControles' => $nbControles]);
    }
    public function index()
    {
        return view('courses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Construire les données d'analyse partagées pour show/depouillement
     */
    private function buildCourseViewData(Course $course)
    {
        $lannee = Carbon::parse($course->ladate)->format('Y');
        $site = Session::get('site', $course->site ?? 'ALG');

        // Charger les enveloppes pour le site
        $enveloppes = Enveloppe::where('site', $site)->get();
        $env = [];

        foreach ($enveloppes as $enveloppe) {
            $filePath = storage_path('app/enveloppe/' . $site . '/' . $enveloppe->nom);
            $matrice = EnveloppeHelper::loadEnveloppe($filePath, $enveloppe->idenveloppe, $enveloppe->figer);
            $env[$enveloppe->idenveloppe] = (object)[
                'matrice' => $matrice,
                'idenveloppe' => $enveloppe->idenveloppe,
                'figer' => $enveloppe->figer
            ];
        }

        // Récupérer les informations de la course
        $getladate = $course->ladate;
        $getheure = $course->heure;
        $getdebut = $course->debut;
        $getfin = $course->fin;
        $lenregistrement = storage_path('app/enregistrement/' . $site . '/' . $course->fichier);
        $source = $course->source ?? 'fichier';

        $pointcourses = [];
        $lnpointcourse = 0;
        $nbdsstop = 0;

        // Charger les données CSV selon la source
        if ($source == "fichier" && file_exists($lenregistrement)) {
            // Charger depuis fichier CSV
            $csvSiteKey = $site === 'ALG' ? 'ALGER' : $site;
            $csvConfig = config('csv.' . $csvSiteKey, config('csv.ALGER'));

            ini_set('memory_limit', '1024M');

            if (($handle = fopen($lenregistrement, "r")) !== FALSE) {
                $ln = 1;
                while (($data = fgetcsv($handle, 1024, ";")) !== FALSE) {
                    if ($ln >= $getdebut && $ln <= $getfin) {
                        if ($lnpointcourse == 0 || ($pointcourses[$lnpointcourse - 1]['km'] ?? null) != ($data[$csvConfig['km']] ?? null)) {
                            $pointcourses[$lnpointcourse] = [
                                'km' => $data[$csvConfig['km']] ?? 0,
                                'temps' => $data[$csvConfig['temps']] ?? '',
                                'vitesse' => (float)($data[$csvConfig['vitesse']] ?? 0),
                                'dsstop' => $data[$csvConfig['dsstop']] ?? 0,
                                'FU' => $data[$csvConfig['FU']] ?? 0,
                                'M1' => $data[$csvConfig['M1']] ?? 0,
                                'M2' => $data[$csvConfig['M2']] ?? 0,
                                'gong' => $data[$csvConfig['gong']] ?? 0,
                                'freinage' => $data[$csvConfig['freinage']] ?? 0,
                                'traction' => $data[$csvConfig['traction']] ?? 0,
                                'patin' => $data[$csvConfig['patin']] ?? 0,
                                'klaxon' => $data[$csvConfig['klaxon']] ?? 0,
                            ];
                            $lnpointcourse++;

                            if ($lnpointcourse == 2 && ($pointcourses[$lnpointcourse - 1]['vitesse'] ?? 0) < 2) {
                                $pointcourses[0] = $pointcourses[1];
                                $lnpointcourse = 1;
                            }
                        } else {
                            if (($pointcourses[$lnpointcourse - 1]['dsstop'] ?? 0) == 1) $nbdsstop++;

                            if (($pointcourses[$lnpointcourse - 1]['vitesse'] ?? 0) < 4 && ($data[$csvConfig['vitesse']] ?? 0) < 4) {
                                $pointcourses[$lnpointcourse - 1]['vitesse'] = min(($pointcourses[$lnpointcourse - 1]['vitesse'] ?? 0), ($data[$csvConfig['vitesse']] ?? 0));
                            } else {
                                $pointcourses[$lnpointcourse - 1]['vitesse'] = max(($pointcourses[$lnpointcourse - 1]['vitesse'] ?? 0), ($data[$csvConfig['vitesse']] ?? 0));
                            }

                            if (($pointcourses[$lnpointcourse - 1]['dsstop'] ?? 0) == 1) $pointcourses[$lnpointcourse - 1]['vitesse'] = 0;

                            $pointcourses[$lnpointcourse - 1]['dsstop'] = max(($pointcourses[$lnpointcourse - 1]['dsstop'] ?? 0), ($data[$csvConfig['dsstop']] ?? 0));
                            $pointcourses[$lnpointcourse - 1]['M1'] = max(($pointcourses[$lnpointcourse - 1]['M1'] ?? 0), ($data[$csvConfig['M1']] ?? 0));
                            $pointcourses[$lnpointcourse - 1]['M2'] = max(($pointcourses[$lnpointcourse - 1]['M2'] ?? 0), ($data[$csvConfig['M2']] ?? 0));
                            $pointcourses[$lnpointcourse - 1]['FU'] = max(($pointcourses[$lnpointcourse - 1]['FU'] ?? 0), ($data[$csvConfig['FU']] ?? 0));
                            $pointcourses[$lnpointcourse - 1]['gong'] = max(($pointcourses[$lnpointcourse - 1]['gong'] ?? 0), ($data[$csvConfig['gong']] ?? 0));
                            $pointcourses[$lnpointcourse - 1]['freinage'] = max(($pointcourses[$lnpointcourse - 1]['freinage'] ?? 0), ($data[$csvConfig['freinage']] ?? 0));
                            $pointcourses[$lnpointcourse - 1]['traction'] = max(($pointcourses[$lnpointcourse - 1]['traction'] ?? 0), ($data[$csvConfig['traction']] ?? 0));
                        }

                        if ($lnpointcourse > 0 && (($pointcourses[$lnpointcourse - 1]['vitesse'] ?? '') === "")) {
                            $pointcourses[$lnpointcourse - 1]['vitesse'] = 0;
                        }
                    }
                    $ln++;
                }
                fclose($handle);
            }
        } else {
            // Charger depuis la base de données (coursecsv)
            $courseCsvData = $course->courseCsv()->orderBy('temps')->get();
            $lnpointcourse = 0;

            foreach ($courseCsvData as $row) {
                $pointcourses[$lnpointcourse] = [
                    'km' => $row->km ?? 0,
                    'temps' => $row->temps ?? '',
                    'vitesse' => $row->vitesse ?? 0,
                    'dsstop' => $row->dsstop ?? 0,
                    'FU' => $row->FU ?? 0,
                    'M1' => $row->M1 ?? 0,
                    'M2' => $row->M2 ?? 0,
                    'gong' => $row->gong ?? 0,
                    'freinage' => $row->freinage ?? 0,
                    'traction' => $row->traction ?? 0,
                    'patin' => $row->patin ?? 0,
                    'klaxon' => $row->klaxon ?? 0,
                ];
                $lnpointcourse++;
            }
        }

        // Marquer le premier point comme arrêt
        if (count($pointcourses) > 0) {
            $pointcourses[0]['dsstop'] = 1;
        }

        // Calculer l'enveloppe appropriée
        $dec = 10000;
        $quelenv = null;

        if (isset($getdebut)) {
            if (!$course->valide) {
                foreach ($env as $lenv) {
                    if ($lenv->figer == 0) {
                        $decCalculated = decale($pointcourses, $lenv->matrice);
                        if ($dec > $decCalculated) {
                            $dec = $decCalculated;
                            $quelenv = $lenv->idenveloppe;
                        }
                    }
                }
            } else {
                $quelenv = $course->idenveloppe;
                if (isset($env[$quelenv])) {
                    $dec = decale($pointcourses, $env[$quelenv]->matrice);
                }
            }
        }
        // Si aucune enveloppe trouvée, rediriger
        if (!isset($quelenv)) {
            return redirect()->route('courses.index')->with('error', 'Aucune enveloppe trouvée pour cette course.');
        }

        // Appliquer reEnv() sur l'enveloppe sélectionnée
        $nouveauEnv = [];
        if (isset($env[$quelenv])) {
            $nouveauEnv = reEnv($env[$quelenv]->matrice, $pointcourses);
        }

        // Calcul des excès
        $nbexait = -1;
        $exait = [];
        for ($i = 0; $i < count($pointcourses); $i++) {
            $j = 0;
            $pointcourses[$i]['difference'] = 0;

            // Trouver vitesse limite
            while ($j < count($nouveauEnv) && ($nouveauEnv[$j]["x"] ?? PHP_INT_MAX) <= $i) {
                $j++;
            }

            if ($j == 0) {
                $j = 1;
            }

            $pointcourses[$i]['limite'] = $nouveauEnv[$j - 1]["y"] ?? 0; // limite
            $pointcourses[$i]['difference'] = (float) ($pointcourses[$i]['vitesse'] ?? 0) - (float) ($pointcourses[$i]['limite'] ?? 0);

            if ($pointcourses[$i]['difference'] < 1) {
                $pointcourses[$i]['couleur'] = "#2a3b90";
                $pointcourses[$i]['text'] = "vitesse";
            } else {
                $pointcourses[$i]['couleur'] = "#f00";
                $pointcourses[$i]['text'] = "depassement";
            }

            if ($pointcourses[$i]['difference'] > 0) {
                // Les excès
                if ($i == 0 || ($pointcourses[$i - 1]['difference'] ?? 0) < 1) {
                    if ($nbexait == -1 || ($exait[$nbexait]["aire"] ?? 0) > 9) {
                        $nbexait++;
                    }
                    $exait[$nbexait]["aire"] = 0;
                    $exait[$nbexait]["debut"] = $i;
                    $exait[$nbexait]["fin"] = $i;
                } elseif (($pointcourses[$i - 1]['difference'] ?? 0) > 2 && $pointcourses[$i]['difference'] > 2) {
                    $exait[$nbexait]["aire"] += (($pointcourses[$i - 1]['difference'] ?? 0) + $pointcourses[$i]['difference']) / 2;
                    $exait[$nbexait]["fin"] = $i;
                }

                if ($nbexait > -1 && $i > 0 && ($pointcourses[$i - 1]['difference'] ?? 0) < 3 && $pointcourses[$i]['difference'] > 2 && ($exait[$nbexait]["aire"] ?? 0) == 0) {
                    $exait[$nbexait]["debut"] = $i;
                }

                if ($nbexait > -1 && $pointcourses[$i]['difference'] > 2) {
                    $exait[$nbexait]["fin"] = $i;
                }
            } elseif ($i > 0 && ((($pointcourses[$i]['vitesse'] ?? 0) - ($pointcourses[$i - 1]['limite'] ?? 0)) > 2) && ($pointcourses[$i - 1]['difference'] ?? 0) > 2) {
                $exait[$nbexait]["aire"] += (($pointcourses[$i - 1]['difference'] ?? 0) + (($pointcourses[$i]['vitesse'] ?? 0) - ($pointcourses[$i - 1]['limite'] ?? 0))) / 2;
                $exait[$nbexait]["fin"] = $i;
            }
        }

        // Si le décalage est trop important, vider les excès
        if ($dec > 1000) {
            $exait = [];
        }

        // Traitement des excès et insertion en base de données
        $ladate = $course->ladate ? $course->ladate->format('Y-m-d') : date('Y-m-d');

        // Supprimer les anciens excès de cette course
        Exces::where('idcourse', $course->idcourse)->delete();

        // Récupérer l'enveloppe pour les informations de lieu
        $enveloppeModel = Enveloppe::find($quelenv);

        // Traitement des excès
        foreach ($exait as $i => &$item) {

            // Récupérer la limite depuis les données de course
            if (isset($pointcourses[$item["debut"]])) {
                $item["limite"] = $pointcourses[$item["debut"]]['limite'] ?? 0;
            }

            // Calcul de la tolérance
            $tolerance = 10;
            $autorise = $item["limite"] ?? 0;
            if ($autorise > 10) $tolerance = 19;
            if ($autorise > 20) $tolerance = 28;
            if ($autorise > 30) $tolerance = 37;
            if ($autorise > 40) $tolerance = 46;
            if ($autorise > 50) $tolerance = 55;
            if ($autorise > 60) $tolerance = 64;

            // Condition pour les dates
            if ($ladate < '2025-11-01') $tolerance = 10;

            $item["passe"] = ($item["aire"] ?? 0) >= $tolerance;

            // Catégorisation
            if (($item["aire"] ?? 0) > 200) {
                $item["categorie"] = "majeur";
            } elseif (($item["aire"] ?? 0) > 130) {
                $item["categorie"] = "grave";
            } elseif (($item["aire"] ?? 0) > 60) {
                $item["categorie"] = "moyen";
            } else {
                $item["categorie"] = "mineur";
            }

            // Trouver la vitesse maximale dans la zone d'excès
            for ($j = $item["debut"]; $j <= $item["fin"] && $j < count($pointcourses); $j++) {
                if ($j == $item["debut"]) {
                    $item["max"] = $pointcourses[$j]['vitesse'] ?? 0;
                } elseif (intval($item["max"] ?? 0) < intval($pointcourses[$j]['vitesse'] ?? 0)) {
                    $item["max"] = $pointcourses[$j]['vitesse'] ?? 0;
                }
            }

            $item["max"] = str_replace(",", ".", (string)($item["max"] ?? 0));

            // Trouver les lieux de début et fin (interstation)
            $lieudebut = "--";
            for ($j = 0; $j < count($nouveauEnv) && ($nouveauEnv[$j]['x'] ?? PHP_INT_MAX) < $item["debut"]; $j++) {
                if (isset($nouveauEnv[$j]['stp']) && $nouveauEnv[$j]['stp'] == 1) {
                    // Si l'enveloppe a un label, l'utiliser, sinon utiliser les infos de l'enveloppe
                    if (isset($nouveauEnv[$j]['label'])) {
                        $lieudebut = $nouveauEnv[$j]['label'];
                    } elseif ($enveloppeModel) {
                        $lieudebut = $enveloppeModel->lieudebut ?? "--";
                    }
                }
            }

            $lieufin = "--";
            for ($j = 0; $j < count($nouveauEnv) && ($nouveauEnv[$j]['x'] ?? PHP_INT_MAX) < $item["fin"]; $j++);
            for ($k = $j; $k < count($nouveauEnv) && isset($nouveauEnv[$k]['stp']) && $nouveauEnv[$k]['stp'] == 0; $k++);
            if ($k < count($nouveauEnv)) {
                if (isset($nouveauEnv[$k]['label'])) {
                    $lieufin = $nouveauEnv[$k]['label'];
                } elseif ($enveloppeModel) {
                    $lieufin = $enveloppeModel->lieufin ?? "--";
                }
            }

            $item["interstation"] = $lieudebut . " -- " . $lieufin;

            // Trouver le détail
            $detail = "";
            for ($j = 0; $j < count($nouveauEnv) && ($nouveauEnv[$j]['x'] ?? PHP_INT_MAX) <= $item["debut"]; $j++);
            if (isset($nouveauEnv[$j - 1]['label'])) {
                $detail = $nouveauEnv[$j - 1]['label'];
            } elseif (isset($nouveauEnv[$j]['label'])) {
                $detail = $nouveauEnv[$j]['label'];
            } elseif ($enveloppeModel) {
                $detail = $enveloppeModel->nom ?? "";
            }
            $item["detail"] = $detail;

            $item["dist"] = $item["fin"] - $item["debut"];
            // Insérer dans la base de données
            if($item["passe"]) Exces::create([
                'idcourse' => $course->idcourse,
                'aire' => round($item["aire"] ?? 0, 2),
                'maxx' => round((float)str_replace(",", ".", $item["max"] ?? 0), 2),
                'autorise' => round($item["limite"] ?? 0, 2),
                'categorie' => $item["categorie"] ?? "mineur",
                'interstation' => $item["interstation"] ?? "--",
                'debut' => $item["debut"] ?? 0,
                'fin' => $item["fin"] ?? 0,
                'detail' => $item["detail"] ?? "",
            ]);

            $item["dd"] = ($item["debut"] ?? 0) - 10;
            $item["ff"] = ($item["fin"] ?? 0) + 10;
        }
        $exait = array_filter($exait, function($item) {
            return $item["passe"] ?? false;
        });

        // Récupérer les excès mis à jour
        $exces = $course->exces()->get();
        $enveloppeSelected = $nouveauEnv;

        // Traitement des freinages
        $nbbrake = 0;
        $brake = [];
        foreach ($pointcourses as $i => $pointcourse) {
            if(($pointcourses[$i]['FU'] ?? 0) == 1) if($i==0 || ($pointcourses[$i-1]['FU'] ?? 0) == 0){
                $station_avant = array_reduce($nouveauEnv,function($t,$v) use($i,$nouveauEnv){
                    if(($i-($v['x'] ?? 0)) < ($i-($t['x'] ?? 0)) && ($v['x'] ?? 0) <= $i && (($v['stp'] ?? 0) == 1 || $v==$nouveauEnv[0])) return $v; return $t;
                },$nouveauEnv[0]);
                if(($station_avant['stp'] ?? 0) != 1) $station_avant = "--";
                $station_apres = array_reduce($nouveauEnv,function($t,$v) use($i,$nouveauEnv){
                    if(($t['x'] ?? -1) < $i && ($v['x'] ?? PHP_INT_MAX) > $i && (($v['stp'] ?? 0) == 1 || $v==$nouveauEnv[count($nouveauEnv)-1])) return $v; return $t;
                },is_array($station_avant) ? $station_avant : $nouveauEnv[0]);
                if(($station_apres['stp'] ?? 0) != 1) $station_apres = "--";
                $detail = array_reduce($nouveauEnv,function($t,$v) use($i,$nouveauEnv){
                    if(($i-($v['x'] ?? 0)) < ($i-($t['x'] ?? 0)) && ($v['x'] ?? 0) <= $i) return $v; return $t;
                },$nouveauEnv[0]);

                $brake[$nbbrake]["interstation"] = (is_array($station_avant)?$station_avant['label']:"--") . " -- " . (is_array($station_apres)?$station_apres['label']:"--");
                $brake[$nbbrake]['type'] = "FU";
                $brake[$nbbrake]['detail'] = is_array($detail)?$detail['label']:"--";
                $brake[$nbbrake]['heure'] = substr($pointcourses[$i]['temps'],11);
                $brake[$nbbrake]['vitesse'] = intval($pointcourses[$i]['vitesse']);

                $nbbrake++;
            }
            if(($pointcourses[$i]['patin'] ?? 0) == 1) if($i==0 || ($pointcourses[$i-1]['patin'] ?? 0) == 0){
                $station_avant = array_reduce($nouveauEnv,function($t,$v) use($i,$nouveauEnv){
                    if($i-($v['x'] ?? 0) < $i-($t['x'] ?? 0) && ($v['x'] ?? 0) < $i && (($v['stp'] ?? 0) == 1 || $v==$nouveauEnv[0])) return $v; return $t;
                },$nouveauEnv[0]);
                $station_apres = array_reduce($nouveauEnv,function($t,$v) use($i,$nouveauEnv){
                    if(($t['x'] ?? -1) < $i && ($v['x'] ?? PHP_INT_MAX) > $i && (($v['stp'] ?? 0) == 1 || $v==$nouveauEnv[count($nouveauEnv)-1])) return $v; return $t;
                },is_array($station_avant) ? $station_avant : $nouveauEnv[0]);

                $detail = array_reduce($nouveauEnv,function($t,$v) use($i,$nouveauEnv){
                    if(($i-($v['x'] ?? 0)) < ($i-($t['x'] ?? 0)) && ($v['x'] ?? 0) <= $i) return $v; return $t;
                },$nouveauEnv[0]);

                $brake[$nbbrake]["interstation"] = (is_array($station_avant)?$station_avant['label']:"--") . " -- " . (is_array($station_apres)?$station_apres['label']:"--");
                $brake[$nbbrake]['type'] = "patin";
                $brake[$nbbrake]['detail'] = is_array($detail)?$detail['label']:"--";
                $brake[$nbbrake]['heure'] = substr($pointcourses[$i]['temps'],11);
                $brake[$nbbrake]['vitesse'] = intval($pointcourses[$i]['vitesse']);

                $nbbrake++;
            }
        }

        // temps de parcours relevé
        $f=0;
        $parcours=[];
        $nbparcours=0;
        for ($i=0,$prec=0; $i < count($nouveauEnv) && ($nouveauEnv[$i]['x'] ?? PHP_INT_MAX) < count($pointcourses) ; $i++) if(($nouveauEnv[$i]['stp'] ?? 0) == 1 && $dec<=150) {
            $parcours[$nbparcours]['distance'] = $nouveauEnv[$i]['x'];
            $parcours[$nbparcours]['vitesse_autorisee'] = $nouveauEnv[$i]['y'];
            $parcours[$nbparcours]['station'] = $nouveauEnv[$i]['label'];
            $parcours[$nbparcours]['heure_passage'] = $pointcourses[$nouveauEnv[$i]['x']]['temps']
                ->format('H:i:s');
            if ($i == 0) {
                $diff = $pointcourses[$nouveauEnv[$i]['x']]['temps']->diff($pointcourses[0]['temps']);
            } else {
                $diff = $pointcourses[$nouveauEnv[$i]['x']]['temps']->diff($pointcourses[$nouveauEnv[$f]['x']]['temps']);
            }

            $parcours[$nbparcours]['temps_roulage'] = $diff->format('%H:%I:%S');

            if(isset($pointcourses[$nouveauEnv[$i]['x']+1]['temps'])){
                $parcours[$nbparcours]['temps_echange'] = $pointcourses[$nouveauEnv[$i]['x']+1]['temps']->diffInSeconds($pointcourses[$nouveauEnv[$i]['x']]['temps']);
            } else {
                $parcours[$nbparcours]['temps_echange'] = 0;
            }

            $parcours[$nbparcours]['heure_depart'] = $pointcourses[$nouveauEnv[$i]['x']]['temps']
                ->addSeconds($parcours[$nbparcours]['temps_echange'])
                ->format('H:i:s');
            if($i==0) Course::where('idcourse', request('id'))
                ->update([
                    'heure' => $parcours[$nbparcours]['heure_depart']
                ]);
                
            $ecart = proche($pointcourses,$env[$quelenv]->matrice[$i]['x'],$prec);
            $parcours[$nbparcours]['difference'] = $ecart-$prec;
            if(abs($ecart-$prec)<=50) $prec=$ecart;

            $f=$i;
            $nbparcours++;
        }

        // Compter les occurrences
        $nbGong = 0;
        $nbKlaxon = 0;
        $nbFU = 0;
        $nbPatin = 0;

        $nbmajeur = array_count_values(array_column($exait, 'categorie'))['majeur'] ?? 0;
        $nbgrave = array_count_values(array_column($exait, 'categorie'))['grave'] ?? 0;
        $nbmoyen = array_count_values(array_column($exait, 'categorie'))['moyen'] ?? 0;
        $nbmineur = array_count_values(array_column($exait, 'categorie'))['mineur'] ?? 0;
        foreach ($pointcourses as $point) {
            $nbGong += $point['gong'] ?? 0;
            $nbKlaxon += $point['klaxon'] ?? 0;
            $nbFU += $point['FU'] ?? 0;
            $nbPatin += $point['patin'] ?? 0;
        }
        return compact(
            'parcours',
            'brake',
            'nouveauEnv',
            'course',
            'exces',
            'pointcourses',
            'env',
            'quelenv',
            'dec',
            'nbdsstop',
            'enveloppeSelected',
            'exait',
            'nbmineur',
            'nbmoyen',
            'nbgrave',
            'nbmajeur',
            'enveloppeModel',
            'nbGong',
            'nbKlaxon',
            'nbFU',
            'nbPatin',
            'lannee'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show( Course $course)
    {
        $data = $this->buildCourseViewData($course);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $course->idenveloppe = $data['quelenv'] ?? null;
        $course->gong=$data['nbGong'] ?? 0;
        $course->klaxon=$data['nbKlaxon'] ?? 0;
        $course->FU=$data['nbFU'] ?? 0;
        $course->patin=$data['nbPatin'] ?? 0;
        $course->save();

        //dd($data);
        return view('courses.show', $data);
    }

    public function depouillement($idcourse)
    {   
        try{
            $course=Course::findOrFail($idcourse);
            $data = $this->buildCourseViewData($course);
            if ($data instanceof \Illuminate\Http\RedirectResponse) {
                return $data;
            }
            return view('courses.depouillement', $data);
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('courses.index')
                ->with('error', 'Course non trouvée.');
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        //
    }

    /**
     * Afficher les courses d'une date donnée
     */
    public function bydate($ladate)
    {
        try {
            $site = session('site', 'ALG');
            $profil = Auth::user()->profil ?? 'user';

            $query = Course::where('site', $site)
                ->where('ladate', $ladate)
                ->orderBy('heure');

            // Filtrer selon le profil
            if ($profil !== "ADMIN" && $profil !== "ladmin" && $profil !== "user") {
                $query->where('valide', true);
            }

            $courses = $query->get();

            // Convertir la chaîne de date en objet Carbon pour la vue
            $ladate = \Carbon\Carbon::createFromFormat('Y-m-d', $ladate);

            return view('courses.bydate', compact('courses', 'ladate'));

        } catch (\Exception $e) {
            \Log::error('Erreur bydate: ' . $e->getMessage());
            return redirect()->route('courses.index')->with('error', 'Erreur lors de la récupération des courses');
        }
    }
}
