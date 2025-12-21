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
        chart: null,
        chartFiltered: null,
        currentPage: 1,
        currentPageFiltered: 1,
        perPage: 10,

        // Propriétés calculées
        get donneesFiltrees() {
            const filtres = $store.statistiques.filtres;
            const donnees = $store.statistiques.donnees;

            if (!donnees.exces) {
                return { exces: [] };
            }

            const excesFiltres = donnees.exces.filter(exce => {
                const dateExce = new Date(exce.ladate);
                const dateDebut = new Date(filtres.debut);
                const dateFin = new Date(filtres.fin);

                return dateExce >= dateDebut &&
                       dateExce <= dateFin &&
                       filtres.voies.includes(exce.voie) &&
                       filtres.categories.includes(exce.categorie) &&
                       filtres.conducteurs.includes(exce.matricule + ' ' + exce.nom);
            });

            return { exces: excesFiltres };
        },

        get excesFiltres() {
            return this.donneesFiltrees.exces;
        },

        get excesFiltresNonExclus() {
            return this.excesFiltres.filter(this.regle);
        },

        get totalExces() {
            return this.excesFiltres.length;
        },

        get totalFilteredExces() {
            return this.excesFiltresNonExclus.length;
        },

        get totalPages() {
            return Math.ceil(this.totalExces / this.perPage);
        },

        get totalFilteredPages() {
            return Math.ceil(this.totalFilteredExces / this.perPage);
        },

        get paginatedExces() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.excesFiltres.slice(start, start + this.perPage);
        },

        get filteredPaginatedExces() {
            const start = (this.currentPageFiltered - 1) * this.perPage;
            return this.excesFiltresNonExclus.slice(start, start + this.perPage);
        },

        // Méthodes de règles
        regle(exce) {
            let tolerance = 10;
            if (exce.autorise > 10) tolerance = 19;
            if (exce.autorise > 20) tolerance = 28;
            if (exce.autorise > 30) tolerance = 37;
            if (exce.autorise > 40) tolerance = 46;
            if (exce.autorise > 50) tolerance = 55;
            if (exce.autorise > 60) tolerance = 64;
            return exce.aire >= tolerance;
        },

        tolerance(exce) {
            let tolerance = 10;
            if (exce.autorise > 10) tolerance = 19;
            if (exce.autorise > 20) tolerance = 28;
            if (exce.autorise > 30) tolerance = 37;
            if (exce.autorise > 40) tolerance = 46;
            if (exce.autorise > 50) tolerance = 55;
            if (exce.autorise > 60) tolerance = 64;
            return tolerance;
        },

        // Méthodes
        init() {
            this.initCharts();

            // Écouter les changements de filtres
            window.addEventListener('statistiques-filtres-appliques', () => {
                this.majCharts();
                this.currentPage = 1;
                this.currentPageFiltered = 1;
            });

            // Écouter le chargement initial des données
            window.addEventListener('statistiques-donnees-chargees', () => {
                this.majCharts();
            });
        },

        initCharts() {
            // Chart pour tous les excès
            const ctx = document.getElementById('evolutionChart');
            if (ctx) {
                this.chart = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [
                            {
                                label: 'Excès mineur',
                                data: [],
                                backgroundColor: 'rgba(75, 192, 40, 0.2)',
                                borderColor: 'rgba(75, 192, 40, 1)',
                                borderWidth: 2,
                                tension: 0.1,
                                fill: false
                            },
                            {
                                label: 'Excès moyen',
                                data: [],
                                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 2,
                                tension: 0.1,
                                fill: false
                            },
                            {
                                label: 'Excès grave',
                                data: [],
                                backgroundColor: 'rgba(200, 50, 0, 0.2)',
                                borderColor: 'rgba(200, 50, 0, 1)',
                                borderWidth: 2,
                                tension: 0.1,
                                fill: false
                            },
                            {
                                label: 'Excès majeur',
                                data: [],
                                backgroundColor: 'rgba(255, 50, 50, 0.2)',
                                borderColor: 'rgba(255, 50, 50, 1)',
                                borderWidth: 2,
                                tension: 0.1,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // Chart pour les excès non exclus
            const ctxFiltered = document.getElementById('evolutionFilteredChart');
            if (ctxFiltered) {
                this.chartFiltered = new Chart(ctxFiltered.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [
                            {
                                label: 'Excès mineur',
                                data: [],
                                backgroundColor: 'rgba(75, 192, 40, 0.2)',
                                borderColor: 'rgba(75, 192, 40, 1)',
                                borderWidth: 2,
                                tension: 0.1,
                                fill: false
                            },
                            {
                                label: 'Excès moyen',
                                data: [],
                                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 2,
                                tension: 0.1,
                                fill: false
                            },
                            {
                                label: 'Excès grave',
                                data: [],
                                backgroundColor: 'rgba(200, 50, 0, 0.2)',
                                borderColor: 'rgba(200, 50, 0, 1)',
                                borderWidth: 2,
                                tension: 0.1,
                                fill: false
                            },
                            {
                                label: 'Excès majeur',
                                data: [],
                                backgroundColor: 'rgba(255, 50, 50, 0.2)',
                                borderColor: 'rgba(255, 50, 50, 1)',
                                borderWidth: 2,
                                tension: 0.1,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        },

        majCharts() {
            // Préparer les données par date
            const dates = [...new Set(this.excesFiltres.map(e => e.ladate))].sort();
            const datesFiltered = [...new Set(this.excesFiltresNonExclus.map(e => e.ladate))].sort();

            // Données pour tous les excès
            const dataMineur = dates.map(date =>
                this.excesFiltres.filter(e => e.ladate === date && e.categorie === 'mineur').length
            );
            const dataMoyen = dates.map(date =>
                this.excesFiltres.filter(e => e.ladate === date && e.categorie === 'moyen').length
            );
            const dataGrave = dates.map(date =>
                this.excesFiltres.filter(e => e.ladate === date && e.categorie === 'grave').length
            );
            const dataMajeur = dates.map(date =>
                this.excesFiltres.filter(e => e.ladate === date && e.categorie === 'majeur').length
            );

            // Données pour les excès non exclus
            const dataMineurFiltered = datesFiltered.map(date =>
                this.excesFiltresNonExclus.filter(e => e.ladate === date && e.categorie === 'mineur').length
            );
            const dataMoyenFiltered = datesFiltered.map(date =>
                this.excesFiltresNonExclus.filter(e => e.ladate === date && e.categorie === 'moyen').length
            );
            const dataGraveFiltered = datesFiltered.map(date =>
                this.excesFiltresNonExclus.filter(e => e.ladate === date && e.categorie === 'grave').length
            );
            const dataMajeurFiltered = datesFiltered.map(date =>
                this.excesFiltresNonExclus.filter(e => e.ladate === date && e.categorie === 'majeur').length
            );

            // Mettre à jour les charts
            if (this.chart) {
                this.chart.data.labels = dates.map(d => this.formatDate(d));
                this.chart.data.datasets[0].data = dataMineur;
                this.chart.data.datasets[1].data = dataMoyen;
                this.chart.data.datasets[2].data = dataGrave;
                this.chart.data.datasets[3].data = dataMajeur;
                this.chart.update();
            }

            if (this.chartFiltered) {
                this.chartFiltered.data.labels = datesFiltered.map(d => this.formatDate(d));
                this.chartFiltered.data.datasets[0].data = dataMineurFiltered;
                this.chartFiltered.data.datasets[1].data = dataMoyenFiltered;
                this.chartFiltered.data.datasets[2].data = dataGraveFiltered;
                this.chartFiltered.data.datasets[3].data = dataMajeurFiltered;
                this.chartFiltered.update();
            }
        },

        ouvrirCourse(idcourse) {
            window.open(`/lacourse?id=${idcourse}`, '_blank');
        },

        formatDate(dateStr) {
            return $store.statistiques.formatDateAffichage(dateStr);
        }
    }
}
</script>
@endpush
@endsection
