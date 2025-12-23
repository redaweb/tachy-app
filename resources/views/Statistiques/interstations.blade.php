{{-- resources/views/statistiques/interstations.blade.php --}}
@extends('statistiques.layout')

@section('title', 'Statistiques - Répartition par Inter-station')

@section('statistiques-content')
<div x-data="repartitionInterstations()" x-init="init()">
    <!-- Overlay de chargement -->
    <div x-show="Alpine.store('statistiques').loading" class="loading-overlay">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des données...</p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-map-marker-alt me-2"></i>Répartition par inter-station
            <span class="badge bg-primary ms-2">{{ session('site') }}</span>
        </h4>
        <div class="text-muted small" x-text="`Données du ${formatDate(Alpine.store('statistiques').filtres.debut)} au ${formatDate(Alpine.store('statistiques').filtres.fin)}`"></div>
    </div>

    <!-- Graphique -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>Top 20 des inter-stations par nombre d'excès
            </h6>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="interstationsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tableau des interstations -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-table me-2"></i>Liste des inter-stations
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
                            <th>#</th>
                            <th>Inter-station</th>
                            <th>Voie</th>
                            <th class="table-secondary">Total excès</th>
                            <th>Excès mineur</th>
                            <th>Excès moyen</th>
                            <th>Excès grave</th>
                            <th>Excès majeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(interstation, index) in paginatedInterstations" :key="interstation.nom">
                            <tr>
                                <td x-text="((currentPage - 1) * perPage) + index + 1"></td>
                                <td x-text="interstation.nom"></td>
                                <td>
                                    <span class="badge" :class="interstation.voie === 'V1' ? 'bg-danger' : 'bg-primary'">
                                        <i class="fas fa-road me-1"></i><span x-text="interstation.voie"></span>
                                    </span>
                                </td>
                                <td class="table-secondary fw-bold" x-text="interstation.total"></td>
                                <td x-text="interstation.mineur"></td>
                                <td x-text="interstation.moyen"></td>
                                <td x-text="interstation.grave"></td>
                                <td x-text="interstation.majeur"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-3">
                <div x-text="`Affichage de ${((currentPage - 1) * perPage) + 1} à ${Math.min(currentPage * perPage, totalInterstations)} sur ${totalInterstations} inter-stations`"></div>
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

    <!-- Carte interactive -->
    @php
        $site = session('site', 'ALG');
    @endphp

    <div class="card">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="fas fa-map me-2"></i>Carte interactive de la ligne
                <small class="text-muted ms-2">(Cliquez sur un point pour voir les détails)</small>
            </h6>
        </div>
        <div class="card-body">
            <div style="position: relative; height: 600px; border: 1px solid #dee2e6; background-color: #f8f9fa;">
                <!-- Tooltip -->
                <div id="carteTooltip" style="position: absolute; background: white; padding: 15px; border: 1px solid #ddd; border-radius: 5px; display: none; z-index: 100; max-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <h6 class="mb-2" id="tooltipTitle"></h6>
                    <table class="table table-sm mb-2" id="tooltipStats"></table>
                    <div class="text-end">
                        <button class="btn btn-sm btn-outline-primary" id="btnVoirDetails">
                            <i class="fas fa-external-link-alt me-1"></i>Voir les excès
                        </button>
                    </div>
                </div>

                <!-- Canvas -->
                <canvas id="carteCanvas" width="1000" height="600" style="cursor: crosshair;"></canvas>

                <!-- Légende -->
                <div style="position: absolute; bottom: 10px; right: 10px; background: white; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <small class="fw-bold">Légende:</small>
                    <div class="d-flex align-items-center mt-1">
                        <div style="width: 15px; height: 15px; background: rgba(255, 0, 0, 0.7); border-radius: 50%; margin-right: 5px;"></div>
                        <small>Voie V1</small>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <div style="width: 15px; height: 15px; background: rgba(0, 0, 255, 0.7); border-radius: 50%; margin-right: 5px;"></div>
                        <small>Voie V2</small>
                    </div>
                    <div class="mt-2">
                        <small>Taille proportionnelle au nombre d'excès</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #carteTooltip table {
        font-size: 0.85rem;
    }

    #carteTooltip th {
        font-weight: 600;
        padding-right: 10px;
    }

    #carteTooltip td {
        text-align: right;
    }
</style>
@endpush

@push('scripts')

<!-- Charger la carto spécifique au site -->
@if(file_exists(public_path('js/carto/carto ' . $site .'.js')))
<script src="{{ asset('js/carto/carto ' . $site .'.js') }}"></script>
@endif

<script>
function repartitionInterstations() {
    return {
        chart: null,
        currentPage: 1,
        perPage: 10,
        carte: null,
        selectedInterstation: null,

        // Propriétés calculées
        get donneesFiltrees() {
            const filtres = Alpine.store('statistiques').filtres;
            const donnees = Alpine.store('statistiques').donnees;

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

        get interstationsData() {
            const interstationsMap = new Map();

            this.donneesFiltrees.exces.forEach(exce => {
                const key = exce.interstation;
                if (!interstationsMap.has(key)) {
                    interstationsMap.set(key, {
                        nom: exce.interstation,
                        voie: exce.voie,
                        mineur: 0,
                        moyen: 0,
                        grave: 0,
                        majeur: 0,
                        total: 0
                    });
                }

                const interstation = interstationsMap.get(key);
                interstation.total++;

                switch(exce.categorie) {
                    case 'mineur': interstation.mineur++; break;
                    case 'moyen': interstation.moyen++; break;
                    case 'grave': interstation.grave++; break;
                    case 'majeur': interstation.majeur++; break;
                }
            });

            // Convertir en tableau et trier par total décroissant
            return Array.from(interstationsMap.values())
                .sort((a, b) => b.total - a.total);
        },

        get totalInterstations() {
            return this.interstationsData.length;
        },

        get totalPages() {
            return Math.ceil(this.totalInterstations / this.perPage);
        },

        get paginatedInterstations() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.interstationsData.slice(start, start + this.perPage);
        },

        get excesForSelectedInterstation() {
            if (!this.selectedInterstation) return [];
            return this.donneesFiltrees.exces.filter(e =>
                e.interstation === this.selectedInterstation.nom
            );
        },

        // Méthodes
        init() {
            this.initChart();

            // Écouter les changements de filtres
            window.addEventListener('statistiques-filtres-appliques', () => {
                this.majChart();
                this.currentPage = 1;
                this.majCarte();
            });

            // Écouter le chargement initial des données
            window.addEventListener('statistiques-donnees-chargees', () => {
                this.majChart();
                this.initCarte();
            });
        },

        initChart() {
            const ctx = document.getElementById('interstationsChart');
            if (!ctx) return;

            this.chart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Excès mineur',
                            data: [],
                            backgroundColor: 'rgba(75, 192, 40, 0.8)',
                            borderColor: 'rgba(75, 192, 40, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Excès moyen',
                            data: [],
                            backgroundColor: 'rgba(255, 206, 86, 0.8)',
                            borderColor: 'rgba(255, 206, 86, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Excès grave',
                            data: [],
                            backgroundColor: 'rgba(200, 50, 0, 0.8)',
                            borderColor: 'rgba(200, 50, 0, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Excès majeur',
                            data: [],
                            backgroundColor: 'rgba(255, 50, 50, 0.8)',
                            borderColor: 'rgba(255, 50, 50, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label || '';
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    return `${label}: ${value}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
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
            });
        },

        majChart() {
            if (!this.chart) return;

            // Limiter à 20 interstations max pour la lisibilité
            const interstations = this.interstationsData.slice(0, 20);

            this.chart.data.labels = interstations.map(i => i.nom.substring(0, 20) + (i.nom.length > 20 ? '...' : ''));
            this.chart.data.datasets[0].data = interstations.map(i => i.mineur);
            this.chart.data.datasets[1].data = interstations.map(i => i.moyen);
            this.chart.data.datasets[2].data = interstations.map(i => i.grave);
            this.chart.data.datasets[3].data = interstations.map(i => i.majeur);
            this.chart.update();
        },

        async initCarte() {
            const canvas = document.getElementById('carteCanvas');
            const tooltip = document.getElementById('carteTooltip');
            const btnDetails = document.getElementById('btnVoirDetails');
            const site = '{{ session("site", "ALG") }}';

            if (!canvas) return;

            // Vérifier si la carto existe pour ce site
            const cartoModule = window['carto' + site.toUpperCase()];

            if (!cartoModule || !cartoModule.listeInter) {
                this.afficherMessageCarte("Cartographie non disponible pour ce site");
                return;
            }

            this.carte = cartoModule;
            await this.majCarte();

            // Gestionnaire d'événements pour le canvas
            canvas.addEventListener('mousemove', (e) => this.onCanvasMouseMove(e));
            canvas.addEventListener('click', (e) => this.onCanvasClick(e));

            // Bouton voir les détails
            if (btnDetails) {
                btnDetails.addEventListener('click', () => this.afficherDetailsInterstation());
            }

            // Cacher la tooltip quand on quitte le canvas
            canvas.addEventListener('mouseleave', () => {
                tooltip.style.display = 'none';
            });
        },

        onCanvasMouseMove(e) {
            if (!this.carte) return;

            const canvas = document.getElementById('carteCanvas');
            const tooltip = document.getElementById('carteTooltip');
            const rect = canvas.getBoundingClientRect();

            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const found = this.carte.getInterstationAtPosition(x, y, this.interstationsData, 20);

            if (found) {
                tooltip.style.left = (e.clientX + 20) + 'px';
                tooltip.style.top = (e.clientY + 20) + 'px';
                tooltip.style.display = 'block';

                // Mettre à jour le contenu de la tooltip
                document.getElementById('tooltipTitle').textContent = found.inter.nom;

                const statsHtml = `
                    <tr><th>Voie:</th><td>${found.inter.voie}</td></tr>
                    <tr><th>Total excès:</th><td class="fw-bold">${found.data.total}</td></tr>
                    <tr><th>Mineur:</th><td><span class="badge badge-mineur">${found.data.mineur}</span></td></tr>
                    <tr><th>Moyen:</th><td><span class="badge badge-moyen">${found.data.moyen}</span></td></tr>
                    <tr><th>Grave:</th><td><span class="badge badge-grave">${found.data.grave}</span></td></tr>
                    <tr><th>Majeur:</th><td><span class="badge badge-majeur">${found.data.majeur}</span></td></tr>
                `;

                document.getElementById('tooltipStats').innerHTML = statsHtml;

                // Stocker l'interstation sélectionnée pour le clic
                this.selectedInterstation = found;
            } else {
                tooltip.style.display = 'none';
                this.selectedInterstation = null;
            }
        },

        onCanvasClick(e) {
            if (this.selectedInterstation) {
                this.afficherDetailsInterstation();
            }
        },

        afficherDetailsInterstation() {
            if (!this.selectedInterstation) return;

            const interstation = this.selectedInterstation.inter.nom;
            const exces = this.excesForSelectedInterstation;

            // Ouvrir une modal ou un nouvel onglet avec les détails
            const modalHtml = `
                <div class="modal fade" id="modalInterstationDetails" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Détails pour: ${interstation}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Voie:</strong> ${this.selectedInterstation.inter.voie}</p>
                                <p><strong>Nombre d'excès:</strong> ${exces.length}</p>

                                ${exces.length > 0 ? `
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Conducteur</th>
                                                <th>Catégorie</th>
                                                <th>Détails</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${exces.slice(0, 20).map(exce => `
                                                <tr>
                                                    <td>${this.formatDate(exce.ladate)}</td>
                                                    <td>${exce.nom}</td>
                                                    <td><span class="badge ${this.getBadgeClass(exce.categorie)}">${exce.categorie}</span></td>
                                                    <td>${exce.detail}</td>
                                                    <td>
                                                        <button onclick="window.open('/lacourse?id=${exce.idcourse}', '_blank')"
                                                                class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                    ${exces.length > 20 ? `<p class="text-muted">... et ${exces.length - 20} autres excès</p>` : ''}
                                </div>
                                ` : '<p class="text-muted">Aucun excès détaillé disponible</p>'}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Injecter et afficher la modal
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHtml;
            document.body.appendChild(modalContainer);

            const modal = new bootstrap.Modal(document.getElementById('modalInterstationDetails'));
            modal.show();

            // Nettoyer après fermeture
            document.getElementById('modalInterstationDetails').addEventListener('hidden.bs.modal', () => {
                modalContainer.remove();
            });
        },

        async majCarte() {
            const canvas = document.getElementById('carteCanvas');
            if (!canvas || !this.carte) return;

            try {
                await this.carte.initCarto(canvas, this.interstationsData);
            } catch (error) {
                this.afficherMessageCarte("Erreur de chargement de la carte");
            }
        },

        afficherMessageCarte(message) {
            const canvas = document.getElementById('carteCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#f8f9fa';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#6c757d';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(message, canvas.width / 2, canvas.height / 2);
        },

        getBadgeClass(categorie) {
            const classes = {
                'mineur': 'badge-mineur',
                'moyen': 'badge-moyen',
                'grave': 'badge-grave',
                'majeur': 'badge-majeur'
            };
            return classes[categorie] || 'bg-secondary';
        },

        formatDate(dateStr) {
            return Alpine.store('statistiques').formatDateAffichage(dateStr);
        }
    }
}
</script>
@endpush
@endsection
