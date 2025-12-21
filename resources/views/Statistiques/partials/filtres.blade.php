{{-- resources/views/statistiques/partials/filtres.blade.php --}}
<div x-data="{
    tousSelectionnes: true,
    indetermine: false,

    get conducteursFiltres() {
        if (!$store.statistiques.donnees.conducteursListe) return [];
        return $store.statistiques.donnees.conducteursListe.filter(c =>
            c.text.toLowerCase().includes($store.statistiques.filtres.rechercheConducteur.toLowerCase())
        );
    },

    toggleTous() {
        if (this.tousSelectionnes) {
            $store.statistiques.filtres.conducteurs = [];
        } else {
            $store.statistiques.filtres.conducteurs = $store.statistiques.donnees.conducteursListe.map(c => c.value);
        }
        this.majSelection();
        $store.statistiques.appliquerFiltres();
    },

    majSelection() {
        const total = $store.statistiques.donnees.conducteursListe?.length || 0;
        const selectionnes = $store.statistiques.filtres.conducteurs.length;

        if (selectionnes === 0) {
            this.tousSelectionnes = false;
            this.indetermine = false;
        } else if (selectionnes === total) {
            this.tousSelectionnes = true;
            this.indetermine = false;
        } else {
            this.tousSelectionnes = false;
            this.indetermine = true;
        }
    }
}" x-init="$watch('$store.statistiques.donnees.conducteursListe', () => majSelection())">
    <h6 class="fw-bold mb-3" style="color: var(--primary-color);">
        <i class="fas fa-filter me-2"></i>Filtres
    </h6>

    <!-- Filtre par date -->
    <div class="mb-4">
        <label class="form-label fw-bold small mb-2" style="color: var(--primary-color);">
            <i class="far fa-calendar me-1"></i> Période
        </label>
        <div class="mb-2">
            <label class="form-label small mb-1">Du :</label>
            <input type="date" x-model="$store.statistiques.filtres.debut"
                   @change="$store.statistiques.appliquerFiltres()"
                   class="form-control form-control-sm">
        </div>
        <div>
            <label class="form-label small mb-1">Au :</label>
            <input type="date" x-model="$store.statistiques.filtres.fin"
                   @change="$store.statistiques.appliquerFiltres()"
                   class="form-control form-control-sm">
        </div>
    </div>

    <!-- Filtre par catégorie -->
    <div class="mb-4">
        <label class="form-label fw-bold small mb-2" style="color: var(--primary-color);">
            <i class="fas fa-tags me-1"></i> Catégories
        </label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="mineur"
                   x-model="$store.statistiques.filtres.categories"
                   @change="$store.statistiques.appliquerFiltres()"
                   id="cat-mineur">
            <label class="form-check-label small" for="cat-mineur">
                <span class="badge badge-mineur px-2 py-1">Excès mineur</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="moyen"
                   x-model="$store.statistiques.filtres.categories"
                   @change="$store.statistiques.appliquerFiltres()"
                   id="cat-moyen">
            <label class="form-check-label small" for="cat-moyen">
                <span class="badge badge-moyen px-2 py-1">Excès moyen</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="grave"
                   x-model="$store.statistiques.filtres.categories"
                   @change="$store.statistiques.appliquerFiltres()"
                   id="cat-grave">
            <label class="form-check-label small" for="cat-grave">
                <span class="badge badge-grave px-2 py-1">Excès grave</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="majeur"
                   x-model="$store.statistiques.filtres.categories"
                   @change="$store.statistiques.appliquerFiltres()"
                   id="cat-majeur">
            <label class="form-check-label small" for="cat-majeur">
                <span class="badge badge-majeur px-2 py-1">Excès majeur</span>
            </label>
        </div>
    </div>

    <!-- Filtre par voie -->
    <div class="mb-4">
        <label class="form-label fw-bold small mb-2" style="color: var(--primary-color);">
            <i class="fas fa-road me-1"></i> Voies
        </label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="V1"
                   x-model="$store.statistiques.filtres.voies"
                   @change="$store.statistiques.appliquerFiltres()"
                   id="voie-v1">
            <label class="form-check-label small" for="voie-v1">Voie 1 (V1)</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="V2"
                   x-model="$store.statistiques.filtres.voies"
                   @change="$store.statistiques.appliquerFiltres()"
                   id="voie-v2">
            <label class="form-check-label small" for="voie-v2">Voie 2 (V2)</label>
        </div>
    </div>

    <!-- Filtre par conducteur -->
    <div class="mb-4">
        <label class="form-label fw-bold small mb-2" style="color: var(--primary-color);">
            <i class="fas fa-user me-1"></i> Conducteurs
            <span class="badge bg-secondary ms-1" x-text="$store.statistiques.filtres.conducteurs.length"></span>
        </label>

        <button type="button" class="btn btn-sm btn-outline-primary w-100 mb-2"
                data-bs-toggle="modal" data-bs-target="#modalConducteurs">
            <i class="fas fa-users me-1"></i> Sélectionner
        </button>
    </div>

    <!-- Boutons d'action -->
    <div class="mt-4">
        <button type="button" class="btn btn-sm btn-outline-secondary w-100 mb-2"
                @click="$store.statistiques.reinitialiserFiltres()">
            <i class="fas fa-redo me-1"></i> Réinitialiser
        </button>

        <button type="button" class="btn btn-sm btn-primary w-100"
                @click="$store.statistiques.exporterCSV()">
            <i class="fas fa-file-export me-1"></i> Exporter CSV
        </button>
    </div>

    <!-- Modal pour la sélection des conducteurs -->
    <div class="modal fade" id="modalConducteurs" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users me-2"></i>Sélection des conducteurs
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3"
                           placeholder="Rechercher un conducteur..."
                           x-model="$store.statistiques.filtres.rechercheConducteur">

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox"
                               :checked="tousSelectionnes"
                               :indeterminate="indetermine"
                               @change="toggleTous">
                        <label class="form-check-label fw-bold">Tous les conducteurs</label>
                    </div>

                    <div style="max-height: 300px; overflow-y: auto;">
                        <template x-for="conducteur in conducteursFiltres" :key="conducteur.value">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       :value="conducteur.value"
                                       x-model="$store.statistiques.filtres.conducteurs"
                                       @change="majSelection(); $store.statistiques.appliquerFiltres()"
                                       :id="'cdr-' + conducteur.value">
                                <label class="form-check-label small" :for="'cdr-' + conducteur.value"
                                       x-text="conducteur.text"></label>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
