<?php

namespace App\Http\Controllers;

use App\Models\Enveloppe;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class EnveloppeController extends Controller
{
    // Constantes pour les profils autorisés
    private const ADMIN_PROFILES = ['ADMIN', 'ladmin'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des enveloppes
     */
    public function index(Request $request)
    {
        $query = Enveloppe::query();
        $user = Auth::user();
        $envBloque = $user->envBloque ?? false;

        // Recherche par nom, lieu début ou lieu fin
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', '%' . $search . '%')
                  ->orWhere('lieudebut', 'like', '%' . $search . '%')
                  ->orWhere('lieufin', 'like', '%' . $search . '%');
            });
        }

        // Filtre par voie
        if ($request->has('voie') && !empty($request->voie)) {
            $query->where('voie', $request->voie);
        }

        // Filtre par statut (archivée/non archivée)
        if ($request->has('statut') && $request->statut !== '') {
            if ($request->statut === 'archivee') {
                $query->where('figer', true);
            } elseif ($request->statut === 'active') {
                $query->where('figer', false);
            }
        }

        // Filtre par période
        if ($request->has('date_debut') && !empty($request->date_debut)) {
            $query->whereDate('importation', '>=', $request->date_debut);
        }
        if ($request->has('date_fin') && !empty($request->date_fin)) {
            $query->whereDate('importation', '<=', $request->date_fin);
        }

        // Filtre par distance minimale/maximale
        if ($request->has('distance_min') && is_numeric($request->distance_min)) {
            $query->where('dis_com', '>=', $request->distance_min);
        }
        if ($request->has('distance_max') && is_numeric($request->distance_max)) {
            $query->where('dis_com', '<=', $request->distance_max);
        }

        // Tri
        $sortField = $request->get('sort', 'importation');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSortFields = ['nom', 'lieudebut', 'lieufin', 'voie', 'dis_com', 'importation', 'figer'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination de 10 éléments par page
        $enveloppes = $query->paginate(10);

        // Statistiques
        $stats = [
            'total' => Enveloppe::count(),
            'archivees' => Enveloppe::where('figer', true)->count(),
            'actives' => Enveloppe::where('figer', false)->count(),
            'voies' => Enveloppe::distinct('voie')->count('voie'),
        ];

        // Liste des voies pour le filtre
        $voies = Enveloppe::distinct()->pluck('voie');

        return view('enveloppes.index', compact('enveloppes', 'stats', 'voies', 'envBloque', 'user'));
    }

    /**
     * API de recherche rapide
     */
    public function search(Request $request)
    {
        $term = $request->get('q');
        $enveloppes = Enveloppe::where('nom', 'like', '%' . $term . '%')
                    ->orWhere('lieudebut', 'like', '%' . $term . '%')
                    ->orWhere('lieufin', 'like', '%' . $term . '%')
                    ->limit(10)
                    ->get(['idenveloppe', 'nom', 'lieudebut', 'lieufin', 'voie']);

        return response()->json($enveloppes);
    }

    /**
     * Enregistrer une nouvelle enveloppe
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fichier' => 'required|file|mimes:csv,txt|max:10240',
            'debut' => 'required|string|max:255',
            'fin' => 'required|string|max:255',
            'voie' => 'required|in:V1,V2',
            'distance' => 'required|numeric|min:0',
        ], [
            'fichier.required' => 'Le fichier est obligatoire.',
            'fichier.mimes' => 'Le fichier doit être au format CSV ou TXT.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
            'debut.required' => 'Le terminus de départ est obligatoire.',
            'fin.required' => 'Le terminus d\'arrivée est obligatoire.',
            'voie.required' => 'La voie est obligatoire.',
            'distance.required' => 'La distance est obligatoire.',
            'distance.numeric' => 'La distance doit être un nombre.',
        ]);

        $site = $this->getSite($request);
        $file = $request->file('fichier');
        $filename = $file->getClientOriginalName();

        // Vérifier si le fichier existe déjà
        $path = "enveloppe/{$site}/{$filename}";
        if (Storage::exists($path)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['fichier' => 'Ce fichier existe déjà. Veuillez choisir un autre fichier.']);
        }

        DB::beginTransaction();
        try {
            // Enregistrer le fichier
            $file->storeAs("enveloppe/{$site}", $filename);

            // Créer l'enregistrement en base
            $enveloppe = Enveloppe::create([
                'nom' => $filename,
                'importation' => now(),
                'lieudebut' => $validated['debut'],
                'lieufin' => $validated['fin'],
                'voie' => $validated['voie'],
                'dis_com' => $validated['distance'],
                'site' => $site,
                'figer' => false,
            ]);

            // Journalisation
            $this->logAction('importer une enveloppe', $enveloppe->idenveloppe);

            DB::commit();

            return redirect()->route('enveloppes.index')
                ->with('success', 'L\'enveloppe a été importée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Supprimer le fichier en cas d'erreur
            if (Storage::exists($path)) {
                Storage::delete($path);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Une erreur est survenue lors de l\'importation. Veuillez réessayer.']);
        }
    }

    /**
     * Supprimer une enveloppe
     */
    public function destroy($id)
    {
        $enveloppe = Enveloppe::findOrFail($id);
        $site = $this->getSite();

        // Vérifier que l'enveloppe appartient au site actuel
        if ($enveloppe->site !== $site) {
            return redirect()->back()
                ->withErrors(['error' => 'Cette enveloppe n\'appartient pas au site sélectionné.']);
        }

        DB::beginTransaction();
        try {
            // Supprimer les courses associées non validées
            DB::table('courses')
                ->where('idenveloppe', $id)
                ->whereNull('valide')
                ->update([
                    'idenveloppe' => null,
                    'valide' => null,
                    'code' => null
                ]);

            // Supprimer le fichier
            $filePath = "enveloppe/{$site}/{$enveloppe->nom}";
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }

            $enveloppeId = $enveloppe->idenveloppe;

            // Supprimer l'enregistrement
            $enveloppe->delete();

            // Journalisation
            $this->logAction('supprimer une enveloppe', $enveloppeId);

            DB::commit();

            return redirect()->route('enveloppes.index')
                ->with('success', 'L\'enveloppe a été supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la suppression. Veuillez réessayer.']);
        }
    }

    /**
     * Basculer le statut de figement d'une enveloppe
     */
    public function toggleFreeze(Request $request)
    {
        $validated = $request->validate([
            'enve' => 'required|exists:enveloppe,idenveloppe',
            'check' => 'required|in:vrai,faux',
        ]);

        try {
            $enveloppe = Enveloppe::findOrFail($validated['enve']);
            $freeze = $validated['check'] === 'faux';

            // Vérifier que l'enveloppe appartient au site actuel
            if ($enveloppe->site !== $this->getSite()) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette enveloppe n\'appartient pas au site sélectionné.'
                    ], 403);
                }
                return redirect()->back()
                    ->withErrors(['error' => 'Cette enveloppe n\'appartient pas au site sélectionné.']);
            }

            $enveloppe->update(['figer' => $freeze]);

            // Journalisation
            $action = $freeze ? 'figer enveloppe' : 'liberer enveloppe';
            $this->logAction($action, $enveloppe->idenveloppe);

            $message = $freeze ? 'L\'enveloppe a été archivée.' : 'L\'enveloppe a été libérée.';

            // Retourner une réponse JSON pour les requêtes AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'figer' => $freeze
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la modification du statut.'
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la modification du statut.']);
        }
    }

    /**
     * Lire le contenu d'une enveloppe
     */
    public function readEnvelope(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
        ]);

        $site = $this->getSite();
        $filename = $request->nom;
        $path = storage_path("app/enveloppe/{$site}/{$filename}");

        if (!file_exists($path)) {
            return response()->json(['error' => 'Fichier non trouvé'], 404);
        }

        $rows = [];
        $lineNumber = 0;

        try {
            if (($handle = fopen($path, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                    // Ignorer la première ligne (en-tête)
                    if ($lineNumber > 0) {
                        $rows[] = [
                            'libelle' => mb_convert_encoding($data[0] ?? '', 'UTF-8', 'auto'),
                            'distance' => $data[1] ?? '',
                            'vitesse_max' => $data[2] ?? '',
                            'station' => isset($data[3]) && $data[3] == 1 ? 'oui' : '',
                        ];
                    }
                    $lineNumber++;
                }
                fclose($handle);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la lecture du fichier'], 500);
        }

        return view('enveloppes.partials.content', compact('rows'));
    }

    /**
     * Obtenir le site depuis la session ou la requête
     */
    private function getSite(Request $request = null): string
    {
        $site = Session::get('site');

        if ($request && $request->has('site')) {
            $site = $request->get('site');
            Session::put('site', $site);
        }

        if (!$site) {
            $site = Auth::user()->site ?? 'ALG';
            Session::put('site', $site);
        }

        return $site;
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    private function isAdmin($user): bool
    {
        return in_array($user->profil ?? '', self::ADMIN_PROFILES);
    }

    /**
     * Journaliser une action
     */
    private function logAction(string $action, int $enveloppeId): void
    {
        $user = Auth::user();

        Journal::create([
            'matricule' => $user->iduser ? (string) $user->iduser : null,
            'nom' => $user->nom ?? 'Inconnu',
            'ladate' => now()->toDateString(),
            'heure' => now()->format('H:i:s'),
            'action' => $action,
            'detail' => "Enveloppe ID: {$enveloppeId}",
            'site' => Session::get('site'),
        ]);
    }
    /**
     * Exporter les enveloppes au format CSV
     */
    public function export(Request $request)
    {
        $query = Enveloppe::query();

        // Appliquer les mêmes filtres que l'index
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', '%' . $search . '%')
                ->orWhere('lieudebut', 'like', '%' . $search . '%')
                ->orWhere('lieufin', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('voie') && !empty($request->voie)) {
            $query->where('voie', $request->voie);
        }

        if ($request->has('statut') && $request->statut !== '') {
            if ($request->statut === 'archivee') {
                $query->where('figer', true);
            } elseif ($request->statut === 'active') {
                $query->where('figer', false);
            }
        }

        if ($request->has('date_debut') && !empty($request->date_debut)) {
            $query->whereDate('importation', '>=', $request->date_debut);
        }
        if ($request->has('date_fin') && !empty($request->date_fin)) {
            $query->whereDate('importation', '<=', $request->date_fin);
        }

        if ($request->has('distance_min') && is_numeric($request->distance_min)) {
            $query->where('dis_com', '>=', $request->distance_min);
        }
        if ($request->has('distance_max') && is_numeric($request->distance_max)) {
            $query->where('dis_com', '<=', $request->distance_max);
        }

        // Filtrer par site actuel
        $site = $this->getSite($request);
        $query->where('site', $site);

        $enveloppes = $query->orderBy('importation', 'desc')->get();

        // Générer le CSV
        $filename = 'enveloppes_' . $site . '_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w+');

        // BOM pour UTF-8
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // En-têtes CSV
        fputcsv($handle, [
            'ID',
            'Nom',
            'Lieu Départ',
            'Lieu Arrivée',
            'Voie',
            'Distance (m)',
            'Date Importation',
            'Archivée'
        ], ';');

        // Données
        foreach ($enveloppes as $enveloppe) {
            fputcsv($handle, [
                $enveloppe->idenveloppe,
                $enveloppe->nom,
                $enveloppe->lieudebut,
                $enveloppe->lieufin,
                $enveloppe->voie,
                $enveloppe->dis_com,
                $enveloppe->importation->format('d/m/Y H:i'),
                $enveloppe->figer ? 'Oui' : 'Non'
            ], ';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        // Journalisation
        $this->logAction('exporter enveloppes', 0);

        return response($content)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
