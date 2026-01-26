{{-- resources/views/StatFreinages/evolution.blade.php --}}
@extends('StatFreinages.layout')

@section('title', 'StatFreinages - Évolution par freinage')

@section('StatFreinages-content')
<div x-data="evolutionfreinages()" x-init="init()">
    <!-- Overlay de chargement -->
    <div x-show="$store.StatFreinages.loading" class="loading-overlay">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des données...</p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-chart-line me-2"></i>Évolution par freinage
        </h4>
        <div class="text-muted small" x-text="`Données du ${formatDate($store.StatFreinages.filtres.debut)} au ${formatDate($store.StatFreinages.filtres.fin)}`"></div>
    </div>

    @if(auth()->user()->profil == 'DG' || in_array(auth()->user()->matricule, ['310040', '310020']) || auth()->user()->profil == 'managerR')
    <ul class="nav nav-tabs mb-4" id="evolutionTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                Tous les freinages
            </button>
        </li>
    </ul>
    @endif

    <div class="tab-content">
        <!-- Onglet Tous les freinages -->
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
                        <i class="fas fa-table me-2"></i>Liste des freinages
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
                                <template x-for="(freinage, index) in paginatedfreinages" :key="freinage.id">
                                    <tr>
                                        <td x-text="formatDate(freinage.ladate)"></td>
                                        <td>
                                            <span class="badge" :class="{
                                                'badge-FU': freinage.type === 'FU',
                                                'badge-patin': freinage.type === 'patin',
                                            }" x-text="freinage.type.charAt(0).toUpperCase() + freinage.type.slice(1)"></span>
                                        </td>
                                        <td x-text="freinage.matricule"></td>
                                        <td x-text="freinage.RAME"></td>
                                        <td x-text="freinage.nom"></td>
                                        <td x-text="freinage.interstation"></td>
                                        <td x-text="freinage.detail"></td>

                                        <td>
                                            <button @click="ouvrirCourse(freinage.idcourse)" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3">
                        <div x-text="`Affichage de ${((currentPage - 1) * perPage) + 1} à ${Math.min(currentPage * perPage, totalfreinages)} sur ${totalfreinages} freinages`"></div>
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
    </div>
</div>
</div>

@push('scripts')
<script>
function evolutionfreinages() {
    return {
        store: Alpine.store('StatFreinages'),
        chart: null,
        loading: true,

        currentPage: 1,
        perPage: 10,

        init() {
            this.loading = true;

            // Écouteurs d'événements
            window.addEventListener('StatFreinages-donnees-chargees', () => this.handleDataLoaded());
            window.addEventListener('StatFreinages-filtres-appliques', () => this.handleDataLoaded());

            // Premier chargement si données déjà présentes
            if (this.store?.donnees?.freinagess) {
                this.handleDataLoaded();
            }
        },

        handleDataLoaded() {
            this.loading = false;
            this.currentPage = 1;
            this.initCharts();
        },

        // ─── Données calculées ────────────────────────────────────────
        get freinagesFiltrees() {
            return this.filtrerfreinagess();
        },

        get totalfreinages() {
            return this.freinagesFiltrees.length;
        },

        get totalPages() {
            return Math.ceil(this.totalfreinages / this.perPage) || 1;
        },

        get paginatedfreinages() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.freinagesFiltrees.slice(start, start + this.perPage);
        },

        // ─── Filtrage ─────────────────────────────────────────────────
        filtrerfreinagess() {
            if (!this.store?.donnees?.freinages) return [];

            const filtres = this.store.filtres;
            let freinages = this.store.donnees.freinages;
            console.log('Filtrage des freinages avec les filtres:', filtres,freinages);
            // Filtrage date + filtres classiques
            freinages = freinages.filter(ex => {
                const date = new Date(ex.ladate);
                const debut = new Date(filtres.debut);
                const fin   = new Date(filtres.fin);

                return date >= debut &&
                       date <= fin &&
                       filtres.voies.includes(ex.voie) &&
                       filtres.types.includes(ex.type) &&
                       filtres.conducteurs.includes(ex.matricule + ' ' + ex.nom);
            });



            // Optionnel : tri par date descendant
            return freinages.sort((a, b) => new Date(b.ladate) - new Date(a.ladate));
        },



        // ─── Gestion des graphiques ───────────────────────────────────
        initCharts() {
            this.initChart('evolutionChart', this.prepareDataForChart(this.freinagesFiltrees));
        },

        initChart(canvasId, chartData) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            // Destruction propre de l'ancien graphique
            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();

            const ctx = canvas.getContext('2d');
            console.log('Initialisation du graphique', chartData);
            const chartInstance = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: "Nombre de freinages" } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            if (canvasId === 'evolutionChart') {
                this.chart = chartInstance;
            }
        },

        prepareDataForChart(freinagesList) {
            console.log('Préparation des données pour le graphique avec', freinagesList, 'freinages');
            const labelsSet = new Set();
            freinagesList.forEach(ex => {
                const date = new Date(ex.ladate);
                const label = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                labelsSet.add(label);
            });
            const labels = Array.from(labelsSet).sort();
            const FUByDate = {};
            const patinByDate = {};
            labels.forEach(label => {
                FUByDate[label] = 0;
                patinByDate[label] = 0;
            });
            freinagesList.forEach(ex => {
                const date = new Date(ex.ladate);
                const label = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                if (ex.type === 'FU') FUByDate[label]++;
                else if (ex.type === 'patin') patinByDate[label]++;
            });
            // Compter par catégorie
            const counts = {
                FU: 0,
                patin: 0,
            };

            freinagesList.forEach(ex => {
                if (counts.hasOwnProperty(ex.type)) {
                    counts[ex.type]++;
                }
            });

            return {
                labels:labels,
                datasets: [
                    {
                        label: 'FU',
                        data: labels.map(label => FUByDate[label]),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'patin',
                        data: labels.map(label => patinByDate[label]),
                        borderColor: 'rgba(255, 206, 86, 1)',
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        fill: false,
                        tension: 0.1
                    }
                ]
            };
        },

        // Utilitaires
        formatDate(dateStr) {
            return this.store?.formatDateAffichage?.(dateStr) || dateStr;
        },

        ouvrirCourse(idcourse) {
            window.open(`/courses/${idcourse}`, '_blank');
        }
    }
}
</script>
@endpush
@endsection
