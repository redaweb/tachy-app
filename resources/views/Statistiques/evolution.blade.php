{{-- resources/views/statistiques/evolution.blade.php --}}
@extends('statistiques.layout')

@section('title', 'Statistiques - Évolution par Excès')

@section('statistiques-content')
<div x-data="evolutionExces()" x-init="init()">
    <!-- Overlay de chargement -->
    <div x-show="$store.statistiques.loading" class="loading-overlay">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des données...</p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-chart-line me-2"></i>Évolution par excès
        </h4>
        <div class="text-muted small" x-text="`Données du ${formatDate($store.statistiques.filtres.debut)} au ${formatDate($store.statistiques.filtres.fin)}`"></div>
    </div>

    @if(auth()->user()->profil == 'DG' || in_array(auth()->user()->matricule, ['310040', '310020']) || auth()->user()->profil == 'managerR')
    <ul class="nav nav-tabs mb-4" id="evolutionTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                Tous les excès
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="filtered-tab" data-bs-toggle="tab" data-bs-target="#filtered" type="button" role="tab">
                Excès non exclus
            </button>
        </li>
    </ul>
    @endif

    <div class="tab-content">
        <!-- Onglet Tous les excès -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Évolution temporelle
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-table me-2"></i>Liste des excès
                    </h6>
                    <div class="d-flex align-items-center">
                        <span class="me-2 small">Afficher</span>
                        <select x-model="perPage" @change="currentPage = 1" class="form-select form-select-sm" style="width: auto;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 stat-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Catégorie</th>
                                    <th>Matricule</th>
                                    <th>Rame</th>
                                    <th>Conducteur</th>
                                    <th>Inter-station</th>
                                    <th>Détails</th>
                                    @if(auth()->user()->profil == 'DG' || in_array(auth()->user()->matricule, ['310040', '310020']) || auth()->user()->profil == 'managerR')
                                    <th>Autre</th>
                                    @endif
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(exce, index) in paginatedExces" :key="exce.id">
                                    <tr>
                                        <td x-text="formatDate(exce.ladate)"></td>
                                        <td>
                                            <span class="badge" :class="{
                                                'badge-mineur': exce.categorie === 'mineur',
                                                'badge-moyen': exce.categorie === 'moyen',
                                                'badge-grave': exce.categorie === 'grave',
                                                'badge-majeur': exce.categorie === 'majeur'
                                            }" x-text="exce.categorie.charAt(0).toUpperCase() + exce.categorie.slice(1)"></span>
                                        </td>
                                        <td x-text="exce.matricule"></td>
                                        <td x-text="exce.RAME"></td>
                                        <td x-text="exce.nom"></td>
                                        <td x-text="exce.interstation"></td>
                                        <td x-text="exce.detail"></td>
                                        @if(auth()->user()->profil == 'DG' || in_array(auth()->user()->matricule, ['310040', '310020']) || auth()->user()->profil == 'managerR')
                                        <td>
                                            <small>
                                                <span x-text="regle(exce) ? 'non exclu' : 'exclu'"></span><br>
                                                <span x-text="`Vitesse autorisée: ${exce.autorise}`"></span><br>
                                                <span x-text="`Tolérance: ${tolerance(exce)}`"></span>
                                            </small>
                                        </td>
                                        @endif
                                        <td>
                                            <button @click="ouvrirCourse(exce.idcourse)" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3">
                        <div x-text="`Affichage de ${((currentPage - 1) * perPage) + 1} à ${Math.min(currentPage * perPage, totalExces)} sur ${totalExces} excès`"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="currentPage--">&laquo;</button>
                                </li>
                                <template x-for="page in totalPages" :key="page">
                                    <li class="page-item" :class="{ active: page === currentPage }">
                                        <button class="page-link" @click="currentPage = page" x-text="page"></button>
                                    </li>
                                </template>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <button class="page-link" @click="currentPage++">&raquo;</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->profil == 'DG' || in_array(auth()->user()->matricule, ['310040', '310020']) || auth()->user()->profil == 'managerR')
        <!-- Onglet Excès non exclus -->
        <div class="tab-pane fade" id="filtered" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Évolution temporelle (excès non exclus)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="evolutionFilteredChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-table me-2"></i>Liste des excès non exclus
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 stat-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Catégorie</th>
                                    <th>Matricule</th>
                                    <th>Rame</th>
                                    <th>Conducteur</th>
                                    <th>Inter-station</th>
                                    <th>Détails</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(exce, index) in filteredPaginatedExces" :key="exce.id">
                                    <tr>
                                        <td x-text="formatDate(exce.ladate)"></td>
                                        <td>
                                            <span class="badge" :class="{
                                                'badge-mineur': exce.categorie === 'mineur',
                                                'badge-moyen': exce.categorie === 'moyen',
                                                'badge-grave': exce.categorie === 'grave',
                                                'badge-majeur': exce.categorie === 'majeur'
                                            }" x-text="exce.categorie.charAt(0).toUpperCase() + exce.categorie.slice(1)"></span>
                                        </td>
                                        <td x-text="exce.matricule"></td>
                                        <td x-text="exce.RAME"></td>
                                        <td x-text="exce.nom"></td>
                                        <td x-text="exce.interstation"></td>
                                        <td x-text="exce.detail"></td>
                                        <td>
                                            <button @click="ouvrirCourse(exce.idcourse)" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3">
                        <div x-text="`Affichage de ${((currentPageFiltered - 1) * perPage) + 1} à ${Math.min(currentPageFiltered * perPage, totalFilteredExces)} sur ${totalFilteredExces} excès`"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: currentPageFiltered === 1 }">
                                    <button class="page-link" @click="currentPageFiltered--">&laquo;</button>
                                </li>
                                <template x-for="page in totalFilteredPages" :key="page">
                                    <li class="page-item" :class="{ active: page === currentPageFiltered }">
                                        <button class="page-link" @click="currentPageFiltered = page" x-text="page"></button>
                                    </li>
                                </template>
                                <li class="page-item" :class="{ disabled: currentPageFiltered === totalFilteredPages }">
                                    <button class="page-link" @click="currentPageFiltered++">&raquo;</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function evolutionExces() {
    return {
        store: Alpine.store('statistiques'),
        chart: null,
        filteredChart: null,
        loading: true,

        currentPage: 1,
        currentPageFiltered: 1,
        perPage: 10,

        init() {
            this.loading = true;

            // Écouteurs d'événements
            window.addEventListener('statistiques-donnees-chargees', () => this.handleDataLoaded());
            window.addEventListener('statistiques-filtres-appliques', () => this.handleDataLoaded());

            // Premier chargement si données déjà présentes
            if (this.store?.donnees?.excess) {
                this.handleDataLoaded();
            }
        },

        handleDataLoaded() {
            this.loading = false;
            this.currentPage = 1;
            this.currentPageFiltered = 1;
            this.initCharts();
        },

        // ─── Données calculées ────────────────────────────────────────
        get excesFiltrees() {
            return this.filtrerExcess(false);
        },

        get excesNonExclus() {
            return this.filtrerExcess(true);
        },

        get totalExces() {
            return this.excesFiltrees.length;
        },

        get totalFilteredExces() {
            return this.excesNonExclus.length;
        },

        get totalPages() {
            return Math.ceil(this.totalExces / this.perPage) || 1;
        },

        get totalFilteredPages() {
            return Math.ceil(this.totalFilteredExces / this.perPage) || 1;
        },

        get paginatedExces() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.excesFiltrees.slice(start, start + this.perPage);
        },

        get filteredPaginatedExces() {
            const start = (this.currentPageFiltered - 1) * this.perPage;
            return this.excesNonExclus.slice(start, start + this.perPage);
        },

        // ─── Filtrage ─────────────────────────────────────────────────
        filtrerExcess(onlyNonExclus = false) {
            if (!this.store?.donnees?.exces) return [];

            const filtres = this.store.filtres;
            let exces = this.store.donnees.exces;

            // Filtrage date + filtres classiques
            exces = exces.filter(ex => {
                const date = new Date(ex.ladate);
                const debut = new Date(filtres.debut);
                const fin   = new Date(filtres.fin);

                return date >= debut &&
                       date <= fin &&
                       filtres.voies.includes(ex.voie) &&
                       filtres.categories.includes(ex.categorie) &&
                       filtres.conducteurs.includes(ex.matricule + ' ' + ex.nom);
            });

            // Filtre supplémentaire : seulement les non exclus ?
            if (onlyNonExclus) {
                exces = exces.filter(ex => !this.regleExclu(ex));
            }

            // Optionnel : tri par date descendant
            return exces.sort((a, b) => new Date(b.ladate) - new Date(a.ladate));
        },

        // ─── Logique exclusion (à adapter selon votre règle réelle) ───
        regleExclu(exces) {
            // Exemple : à remplacer par votre vraie logique
            return exces.autorise && (exces.vitesse || 0) <= (exces.autorise * 1.05); // tolérance 5% par ex.
        },

        // ─── Gestion des graphiques ───────────────────────────────────
        initCharts() {
            this.initChart('evolutionChart', this.prepareDataForChart(this.excesFiltrees));
            this.initChart('evolutionFilteredChart', this.prepareDataForChart(this.excesNonExclus));
        },

        initChart(canvasId, chartData) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            // Destruction propre de l'ancien graphique
            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();

            const ctx = canvas.getContext('2d');

            const chartInstance = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: "Nombre d'excès" } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            if (canvasId === 'evolutionChart') {
                this.chart = chartInstance;
            } else {
                this.filteredChart = chartInstance;
            }
        },

        prepareDataForChart(excesList) {
            // Compter par catégorie
            const counts = {
                mineur: 0,
                moyen: 0,
                grave: 0,
                majeur: 0
            };

            excesList.forEach(ex => {
                if (counts.hasOwnProperty(ex.categorie)) {
                    counts[ex.categorie]++;
                }
            });

            return {
                labels: ['Mineur', 'Moyen', 'Grave', 'Majeur'],
                datasets: [{
                    label: 'Nombre d\'excès',
                    data: [counts.mineur, counts.moyen, counts.grave, counts.majeur],
                    backgroundColor: [
                        'rgba(75,192,40,0.8)',
                        'rgba(255,206,86,0.8)',
                        'rgba(200,50,0,0.8)',
                        'rgba(255,50,50,0.8)'
                    ],
                    borderColor: [
                        '#4bc028',
                        '#ffce56',
                        '#c83200',
                        '#ff3232'
                    ],
                    borderWidth: 1
                }]
            };
        },

        // Utilitaires
        formatDate(dateStr) {
            return this.store?.formatDateAffichage?.(dateStr) || dateStr;
        },

        ouvrirCourse(idcourse) {
            if (idcourse && this.store?.ouvrirCourse) {
                this.store.ouvrirCourse(idcourse);
            }
        }
    }
}
</script>
@endpush
@endsection
