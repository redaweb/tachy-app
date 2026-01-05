{{-- resources/views/StatFreinages/types.blade.php --}}
@extends('StatFreinages.layout')

@section('title', 'StatFreinages - Répartition par Catégorie')

@section('StatFreinages-content')
<div x-data="repartitiontypes()" x-init="init()">
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
            <i class="fas fa-chart-pie me-2"></i>Répartition par catégorie de freinage
        </h4>
        <div class="text-muted small" x-text="`Données du ${formatDate($store.StatFreinages.filtres.debut)} au ${formatDate($store.StatFreinages.filtres.fin)}`"></div>
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
                                <th>Type de freinage</th>
                                <th class="text-end">Nombre</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-FU">FU</span></td>
                                <td class="text-end fw-bold" x-text="nbFU"></td>
                                <td class="text-end" x-text="`${pcFU}%`"></td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-patin">patin</span></td>
                                <td class="text-end fw-bold" x-text="nbpatin"></td>
                                <td class="text-end" x-text="`${pcpatin}%`"></td>
                            </tr>
                            <tr>
                            <tr class="table-secondary">
                                <td class="fw-bold">TOTAL</td>
                                <td class="text-end fw-bold" x-text="totalfreinages"></td>
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
                        <canvas id="freinagesChart"></canvas>
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

    function repartitiontypes() {
        return {
            chart: null,
            updateChart: null,

            // Propriétés calculées
            get donneesFiltrees() {
                const store = Alpine.store('StatFreinages');
                if (!store || !store.donnees || !store.donnees.freinages) {
                    return { freinages: [], courses: [] };
                }
                const filtres = store.filtres;
                const donnees = store.donnees;

                const freinagesFiltres = donnees.freinages.filter(freinage => {
                    const datefreinage = new Date(freinage.ladate);
                    const dateDebut = new Date(filtres.debut);
                    const dateFin = new Date(filtres.fin);

                    return datefreinage >= dateDebut &&
                           datefreinage <= dateFin &&
                           filtres.voies.includes(freinage.voie) &&
                           filtres.types.includes(freinage.type) &&
                           filtres.conducteurs.includes(freinage.matricule + ' ' + freinage.nom);
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

                return { freinages: freinagesFiltres, courses: coursesFiltrees };
            },

            get totalfreinages() {
                return this.donneesFiltrees.freinages.length;
            },

            get nbFU() {
                return this.donneesFiltrees.freinages.filter(e => e.type === 'FU').length;
            },

            get pcFU() {
                return this.totalfreinages > 0 ? ((this.nbFU / this.totalfreinages) * 100).toFixed(1) : '0.0';
            },

            get nbpatin() {
                return this.donneesFiltrees.freinages.filter(e => e.type === 'patin').length;
            },

            get pcpatin() {
                return this.totalfreinages > 0 ? ((this.nbpatin / this.totalfreinages) * 100).toFixed(1) : '0.0';
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
                window.addEventListener('StatFreinages-filtres-appliques', () => {
                    this.updateChart();
                });

                // Écouter le chargement initial des données
                window.addEventListener('StatFreinages-donnees-chargees', () => {
                    this.updateChart();
                });

                // Cleanup

            },

            initChart() {
                const canvas = document.getElementById('freinagesChart');
                if (!canvas) {
                    console.warn('Canvas #freinagesChart not found yet');
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
                        labels: ['FU', 'patin'],
                        datasets: [{
                            data: [this.nbFU, this.nbpatin],
                            backgroundColor: [
                                'rgba(75, 192, 40, 0.8)',
                                'rgba(255, 206, 86, 0.8)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 40, 1)',
                                'rgba(255, 206, 86, 1)'
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

                if (this.totalfreinages === 0) {
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
                        this.nbFU || 0,
                        this.nbpatin || 0,
                    ];

                    this.chart.data.labels = [
                        `FU (${this.pcFU}%)`,
                        `patin (${this.pcpatin}%)`
                    ];

                    this.chart.update('none');
                } catch (err) {
                    console.error('Error during chart update, forcing recreation:', err);
                    this.initChart();
                }
            },

            formatDate(dateStr) {
                return Alpine.store('StatFreinages').formatDateAffichage(dateStr);
            }
        }
    }
    </script>
@endpush
@endsection
