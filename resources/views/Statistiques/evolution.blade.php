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
    </div>
</div>
</div>

@push('scripts')
<script>
function evolutionExces() {
    return {
        store: Alpine.store('statistiques'),
        chart: null,
        loading: true,

        currentPage: 1,
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
            this.initCharts();
        },

        // ─── Données calculées ────────────────────────────────────────
        get excesFiltrees() {
            return this.filtrerExcess();
        },

        get totalExces() {
            return this.excesFiltrees.length;
        },

        get totalPages() {
            return Math.ceil(this.totalExces / this.perPage) || 1;
        },

        get paginatedExces() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.excesFiltrees.slice(start, start + this.perPage);
        },

        // ─── Filtrage ─────────────────────────────────────────────────
        filtrerExcess() {
            if (!this.store?.donnees?.exces) return [];

            const filtres = this.store.filtres;
            let exces = this.store.donnees.exces;
            console.log('Filtrage des excès avec les filtres:', filtres,exces);
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
                        y: { beginAtZero: true, title: { display: true, text: "Nombre d'excès" } }
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

        prepareDataForChart(excesList) {
            console.log('Préparation des données pour le graphique avec', excesList, 'excès');
            const labelsSet = new Set();
            excesList.forEach(ex => {
                const date = new Date(ex.ladate);
                const label = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                labelsSet.add(label);
            });
            const labels = Array.from(labelsSet).sort();
            const mineurByDate = {};
            const moyenByDate = {};
            const graveByDate = {};
            const majeurByDate = {};
            labels.forEach(label => {
                mineurByDate[label] = 0;
                moyenByDate[label] = 0;
                graveByDate[label] = 0;
                majeurByDate[label] = 0;
            });
            excesList.forEach(ex => {
                const date = new Date(ex.ladate);
                const label = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                if (ex.categorie === 'mineur') mineurByDate[label]++;
                else if (ex.categorie === 'moyen') moyenByDate[label]++;
                else if (ex.categorie === 'grave') graveByDate[label]++;
                else if (ex.categorie === 'majeur') majeurByDate[label]++;
            });
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
                labels:labels,
                datasets: [
                    {
                        label: 'Mineur',
                        data: labels.map(label => mineurByDate[label]),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'Moyen',
                        data: labels.map(label => moyenByDate[label]),
                        borderColor: 'rgba(255, 206, 86, 1)',
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'Grave',
                        data: labels.map(label => graveByDate[label]),
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'Majeur',
                        data: labels.map(label => majeurByDate[label]),
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
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
