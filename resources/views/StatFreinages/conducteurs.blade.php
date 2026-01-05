{{-- resources/views/StatFreinages/conducteurs.blade.php --}}
@extends('StatFreinages.layout')

@section('title', 'StatFreinages - Répartition par Conducteur')

@section('StatFreinages-content')
<div x-data="repartitionConducteurs()" x-init="init()">
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
            <i class="fas fa-users me-2"></i>Répartition par conducteur
        </h4>
        <div class="text-muted small" x-text="`Données du ${formatDate($store.StatFreinages.filtres.debut)} au ${formatDate($store.StatFreinages.filtres.fin)}`"></div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>Graphique par conducteur
            </h6>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="conducteursChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-table me-2"></i>Liste des conducteurs
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
                            <th>Conducteur</th>
                            <th>Matricule</th>
                            <th>Contrôles réalisés</th>
                            <th class="table-secondary">Total freinages</th>
                            <th>FU</th>
                            <th>patin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="conducteur in paginatedConducteurs" :key="conducteur.matricule">
                            <tr>
                                <td x-text="conducteur.nom"></td>
                                <td x-text="conducteur.matricule"></td>
                                <td x-text="conducteur.nbCourses"></td>
                                <td class="table-secondary fw-bold" x-text="conducteur.totalfreinages"></td>
                                <td x-text="conducteur.FU"></td>
                                <td x-text="conducteur.patin"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-3">
                <div x-text="`Affichage de ${((currentPage - 1) * perPage) + 1} à ${Math.min(currentPage * perPage, totalConducteurs)} sur ${totalConducteurs} conducteurs`"></div>
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

@push('scripts')
<script>
function repartitionConducteurs() {
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
            if (this.store?.donnees?.freinages && this.store?.donnees?.courses) {
                this.handleDataLoaded();
            }
        },

        handleDataLoaded() {
            console.log('Data loaded or filters applied, updating conducteurs stats');
            this.loading = false;
            this.currentPage = 1;
            this.initChart();
            this.majChart();
        },

        // Propriétés calculées
        get donneesFiltrees() {
            const filtres = this.store.filtres;
            const donnees = this.store.donnees;

            if (!donnees.freinages || !donnees.courses) {
                return { freinages: [], courses: [] };
            }

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

        get conducteursData() {
            const conducteursMap = new Map();

            // Compter les courses par conducteur
            this.donneesFiltrees.courses.forEach(course => {
                const key = `${course.matricule}|${course.nom}`;
                if (!conducteursMap.has(key)) {
                    conducteursMap.set(key, {
                        nom: course.nom,
                        matricule: course.matricule,
                        nbCourses: 0,
                        FU: 0,
                        patin: 0,
                        totalfreinages: 0
                    });
                }
                conducteursMap.get(key).nbCourses++;
            });

            // Compter les freinages par conducteur
            this.donneesFiltrees.freinages.forEach(freinage => {
                const key = `${freinage.matricule}|${freinage.nom}`;
                if (conducteursMap.has(key)) {
                    const conducteur = conducteursMap.get(key);
                    conducteur.totalfreinages++;

                    switch(freinage.type) {
                        case 'FU': conducteur.FU++; break;
                        case 'patin': conducteur.patin++; break;
                    }
                }
            });

            // Convertir en tableau et trier par total freinages décroissant
            return Array.from(conducteursMap.values())
                .sort((a, b) => b.totalfreinages - a.totalfreinages);
        },

        get totalConducteurs() {
            return this.conducteursData.length;
        },

        get totalPages() {
            return Math.ceil(this.totalConducteurs / this.perPage);
        },

        get paginatedConducteurs() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.conducteursData.slice(start, start + this.perPage);
        },

        // Méthodes
        initChart() {
            const canvas = document.getElementById('conducteursChart');
            if (!canvas) {
                console.warn('Canvas #conducteursChart not found yet');
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

            this.chart = Alpine.raw(new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'FU',
                            data: [],
                            backgroundColor: 'rgba(75, 192, 40, 0.8)',
                            borderColor: 'rgba(75, 192, 40, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'patin',
                            data: [],
                            backgroundColor: 'rgba(255, 206, 86, 0.8)',
                            borderColor: 'rgba(255, 206, 86, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    //maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            }));
        },

        majChart() {
            if (this.isUpdating) return;
            this.isUpdating = true;

            if (!this.chart || this.totalConducteurs === 0) {
                this.isUpdating = false;
                return;
            }

            const conducteurs = this.conducteursData.filter(c => c.totalfreinages > 0);

            requestAnimationFrame(() => {
                try {
                    this.chart.data.labels = conducteurs.map(c => c.nom.split(' ')[0]);
                    this.chart.data.datasets[0].data = conducteurs.map(c => c.FU);
                    this.chart.data.datasets[1].data = conducteurs.map(c => c.patin);

                    this.chart.update();
                } catch (err) {
                    console.error('Chart update skipped:', err);
                } finally {
                    this.isUpdating = false;
                }
            });
        },

        formatDate(dateStr) {
            return this.store?.formatDateAffichage?.(dateStr) || dateStr;
        }
    }
}
</script>
@endpush
@endsection
