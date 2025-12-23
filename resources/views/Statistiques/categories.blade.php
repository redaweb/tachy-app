{{-- resources/views/statistiques/categories.blade.php --}}
@extends('statistiques.layout')

@section('title', 'Statistiques - Répartition par Catégorie')

@section('statistiques-content')
<div x-data="repartitionCategories()" x-init="init()">
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
            <i class="fas fa-chart-pie me-2"></i>Répartition par catégorie d'excès
        </h4>
        <div class="text-muted small" x-text="`Données du ${formatDate($store.statistiques.filtres.debut)} au ${formatDate($store.statistiques.filtres.fin)}`"></div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-table me-2"></i>Récapitulatif
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 stat-table">
                        <thead>
                            <tr>
                                <th>Type d'excès</th>
                                <th class="text-end">Nombre</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-mineur">Excès mineur</span></td>
                                <td class="text-end fw-bold" x-text="nbmineur"></td>
                                <td class="text-end" x-text="`${pcmineur}%`"></td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-moyen">Excès moyen</span></td>
                                <td class="text-end fw-bold" x-text="nbmoyen"></td>
                                <td class="text-end" x-text="`${pcmoyen}%`"></td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-grave">Excès grave</span></td>
                                <td class="text-end fw-bold" x-text="nbgrave"></td>
                                <td class="text-end" x-text="`${pcgrave}%`"></td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-majeur">Excès majeur</span></td>
                                <td class="text-end fw-bold" x-text="nbmajeur"></td>
                                <td class="text-end" x-text="`${pcmajeur}%`"></td>
                            </tr>
                            <tr class="table-secondary">
                                <td class="fw-bold">TOTAL</td>
                                <td class="text-end fw-bold" x-text="totalExces"></td>
                                <td class="text-end fw-bold">100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Indicateurs
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 stat-table">
                        <tbody>
                            <tr>
                                <th>Courses contrôlées</th>
                                <td class="text-end" x-text="nbtotal"></td>
                            </tr>
                            <tr>
                                <th>Conducteurs contrôlés</th>
                                <td class="text-end" x-text="nbcdr"></td>
                            </tr>
                            <tr>
                                <th>Kilomètres contrôlés (V1)</th>
                                <td class="text-end" x-text="`${(KMV1 / 1000).toFixed(1)} km`"></td>
                            </tr>
                            <tr>
                                <th>Kilomètres contrôlés (V2)</th>
                                <td class="text-end" x-text="`${(KMV2 / 1000).toFixed(1)} km`"></td>
                            </tr>
                            <tr class="table-primary">
                                <th class="fw-bold">Kilomètres totaux</th>
                                <td class="text-end fw-bold" x-text="`${(KM / 1000).toFixed(1)} km`"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Répartition graphique
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="excesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function repartitionCategories() {
        return {
            chart: null,
            updateChart: null,

            // Propriétés calculées
            get donneesFiltrees() {
                const store = Alpine.store('statistiques');
                if (!store || !store.donnees || !store.donnees.exces) {
                    return { exces: [], courses: [] };
                }
                const filtres = store.filtres;
                const donnees = store.donnees;

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

                const coursesFiltrees = donnees.courses.filter(course => {
                    const dateCourse = new Date(course.ladate);
                    const dateDebut = new Date(filtres.debut);
                    const dateFin = new Date(filtres.fin);

                    return dateCourse >= dateDebut &&
                           dateCourse <= dateFin &&
                           filtres.voies.includes(course.voie) &&
                           filtres.conducteurs.includes(course.matricule + ' ' + course.nom);
                });

                return { exces: excesFiltres, courses: coursesFiltrees };
            },

            get totalExces() {
                return this.donneesFiltrees.exces.length;
            },

            get nbmineur() {
                return this.donneesFiltrees.exces.filter(e => e.categorie === 'mineur').length;
            },

            get pcmineur() {
                return this.totalExces > 0 ? ((this.nbmineur / this.totalExces) * 100).toFixed(1) : '0.0';
            },

            get nbmoyen() {
                return this.donneesFiltrees.exces.filter(e => e.categorie === 'moyen').length;
            },

            get pcmoyen() {
                return this.totalExces > 0 ? ((this.nbmoyen / this.totalExces) * 100).toFixed(1) : '0.0';
            },

            get nbgrave() {
                return this.donneesFiltrees.exces.filter(e => e.categorie === 'grave').length;
            },

            get pcgrave() {
                return this.totalExces > 0 ? ((this.nbgrave / this.totalExces) * 100).toFixed(1) : '0.0';
            },

            get nbmajeur() {
                return this.donneesFiltrees.exces.filter(e => e.categorie === 'majeur').length;
            },

            get pcmajeur() {
                return this.totalExces > 0 ? ((this.nbmajeur / this.totalExces) * 100).toFixed(1) : '0.0';
            },

            get nbtotal() {
                return this.donneesFiltrees.courses.length;
            },

            get nbcdr() {
                const conducteurs = new Set(this.donneesFiltrees.courses.map(c => c.matricule));
                return conducteurs.size;
            },

            get KMV1() {
                const coursesV1 = this.donneesFiltrees.courses.filter(c => c.voie === 'V1');
                return coursesV1.reduce((total, c) => total + (c.discom || 0), 0);
            },

            get KMV2() {
                const coursesV2 = this.donneesFiltrees.courses.filter(c => c.voie === 'V2');
                return coursesV2.reduce((total, c) => total + (c.discom || 0), 0);
            },

            get KM() {
                return this.KMV1 + this.KMV2;
            },

            // Méthodes
            init() {
                this.updateChart = debounce(this.majChart.bind(this), 200);

                // Appel initial
                this.updateChart();

                // Écouter les changements de filtres
                window.addEventListener('statistiques-filtres-appliques', () => {
                    this.updateChart();
                });

                // Écouter le chargement initial des données
                window.addEventListener('statistiques-donnees-chargees', () => {
                    this.updateChart();
                });

                // Cleanup

            },

            initChart() {
                const canvas = document.getElementById('excesChart');
                if (!canvas) {
                    console.warn('Canvas #excesChart not found yet');
                    setTimeout(() => this.initChart(), 100);
                    return;
                }

                const existingChart = Chart.getChart(canvas);
                if (existingChart) {
                    existingChart.destroy();
                }

                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }

                const ctx = canvas.getContext('2d');

                this.chart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Excès mineur', 'Excès moyen', 'Excès grave', 'Excès majeur'],
                        datasets: [{
                            data: [this.nbmineur, this.nbmoyen, this.nbgrave, this.nbmajeur],
                            backgroundColor: [
                                'rgba(75, 192, 40, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(200, 50, 0, 0.8)',
                                'rgba(255, 50, 50, 0.8)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 40, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(200, 50, 0, 1)',
                                'rgba(255, 50, 50, 1)'
                            ],
                            borderWidth: 1,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            },

            majChart() {
                console.log('majchart called');

                if (this.totalExces === 0) {
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                    return;
                }

                if (!this.chart || !Chart.getChart(this.chart.canvas)) {
                    console.warn('Chart instance no longer valid → recreating');
                    this.initChart();
                    return;
                }

                try {
                    this.chart.data.datasets[0].data = [
                        this.nbmineur || 0,
                        this.nbmoyen || 0,
                        this.nbgrave || 0,
                        this.nbmajeur || 0
                    ];

                    this.chart.data.labels = [
                        `Excès mineur (${this.pcmineur}%)`,
                        `Excès moyen (${this.pcmoyen}%)`,
                        `Excès grave (${this.pcgrave}%)`,
                        `Excès majeur (${this.pcmajeur}%)`
                    ];

                    this.chart.update('none');
                } catch (err) {
                    console.error('Error during chart update, forcing recreation:', err);
                    this.initChart();
                }
            },

            formatDate(dateStr) {
                return Alpine.store('statistiques').formatDateAffichage(dateStr);
            }
        }
    }
    </script>
@endpush
@endsection
