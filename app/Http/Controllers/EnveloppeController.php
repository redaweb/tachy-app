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
        $user = Auth::user();
        $site = $this->getSite($request);

        $envBloque = $user->envBloque ?? false;
        $isAdmin = $this->isAdmin($user);

        $query = Enveloppe::forSite($site);

        // Les non-admins ne voient que les enveloppes non figées
        if (!$isAdmin) {
            $query->notFrozen();
        }

        // Filtre pour les admins
        if ($isAdmin) {
            $filter = $request->get('filter', 'tous');
            $query->byFreezeStatus($filter);
        }

        $enveloppes = $query->orderBy('importation', 'desc')->get();

        return view('enveloppes.index', compact('enveloppes', 'envBloque', 'user', 'site'));
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
}
