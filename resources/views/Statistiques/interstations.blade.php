{{-- resources/views/statistiques/interstations.blade.php --}}
@extends('statistiques.layout')

@section('title', 'Statistiques - Répartition par Inter-station')

@section('statistiques-content')
<div x-data="repartitionInterstations()" x-init="init()">
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
            <i class="fas fa-map-marker-alt me-2"></i>Répartition par inter-station
        </h4>
        <div class="text-muted small" x-text="`Données du ${formatDate($store.statistiques.filtres.debut)} au ${formatDate($store.statistiques.filtres.fin)}`"></div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>Graphique par inter-station
            </h6>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="interstationsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
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
                        <template x-for="interstation in paginatedInterstations" :key="interstation.nom">
                            <tr>
                                <td x-text="interstation.nom"></td>
                                <td x-text="interstation.voie"></td>
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

    <!-- Section carte interactive -->
    @if($site == 'ALG' || $site == 'ORN' || $site == 'CST') <!-- Adapter selon les sites disponibles -->
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="fas fa-map me-2"></i>Carte interactive
            </h6>
        </div>
        <div class="card-body">
            <div style="position: relative; height: 600px; border: 1px solid #dee2e6;">
                <div id="tooltip" style="position: absolute; background: white; padding: 10px; border: 1px solid #ccc; display: none; z-index: 100;"></div>
                <canvas id="carteCanvas" width="1000" height="600"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function repartitionInterstations() {
    return {
        chart: null,
        currentPage: 1,
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

        // Méthodes
        init() {
            this.initChart();

            // Écouter les changements de filtres
            window.addEventListener('statistiques-filtres-appliques', () => {
                this.majChart();
                this.currentPage = 1;
            });

            // Écouter le chargement initial des données
            window.addEventListener('statistiques-donnees-chargees', () => {
                this.majChart();
                this.initCarte();
            });
        },

        initChart() {
            const ctx = document.getElementById('interstationsChart');

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
            });
        },

        majChart() {
            if (!this.chart) return;

            // Limiter à 20 interstations max pour la lisibilité
            const interstations = this.interstationsData.slice(0, 20);

            this.chart.data.labels = interstations.map(i => i.nom);
            this.chart.data.datasets[0].data = interstations.map(i => i.mineur);
            this.chart.data.datasets[1].data = interstations.map(i => i.moyen);
            this.chart.data.datasets[2].data = interstations.map(i => i.grave);
            this.chart.data.datasets[3].data = interstations.map(i => i.majeur);
            this.chart.update();
        },

        initCarte() {
            // Cette fonction initialise la carte interactive
            // Vous devrez adapter les coordonnées selon votre site
            const canvas = document.getElementById('carteCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const tooltip = document.getElementById('tooltip');

            // Nettoyer le canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Ici, vous devrez implémenter la logique de dessin de la carte
            // en fonction des données d'interstations
            // C'est une version simplifiée

            const interstations = this.interstationsData.slice(0, 15);

            // Dessiner des points pour chaque interstation
            interstations.forEach((inter, index) => {
                const x = 50 + (index % 5) * 180;
                const y = 50 + Math.floor(index / 5) * 120;

                // Taille du point proportionnelle au nombre d'excès
                const radius = Math.min(30, 10 + inter.total * 2);

                // Couleur selon la voie
                ctx.fillStyle = inter.voie === 'V1' ? 'rgba(255, 50, 50, 0.7)' : 'rgba(50, 50, 255, 0.7)';
                ctx.beginPath();
                ctx.arc(x, y, radius, 0, Math.PI * 2);
                ctx.fill();

                // Texte avec le nombre d'excès
                ctx.fillStyle = 'white';
                ctx.font = 'bold 12px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(inter.total, x, y);

                // Nom de l'interstation
                ctx.fillStyle = '#333';
                ctx.font = '10px Arial';
                ctx.fillText(inter.nom, x, y + radius + 15);

                // Gestion des événements de souris
                canvas.addEventListener('mousemove', (e) => {
                    const rect = canvas.getBoundingClientRect();
                    const mouseX = e.clientX - rect.left;
                    const mouseY = e.clientY - rect.top;

                    const distance = Math.sqrt(Math.pow(mouseX - x, 2) + Math.pow(mouseY - y, 2));

                    if (distance <= radius) {
                        tooltip.style.left = (e.clientX + 10) + 'px';
                        tooltip.style.top = (e.clientY + 10) + 'px';
                        tooltip.style.display = 'block';
                        tooltip.innerHTML = `
                            <strong>${inter.nom}</strong><br>
                            Voie: ${inter.voie}<br>
                            Total: ${inter.total}<br>
                            Mineur: ${inter.mineur}<br>
                            Moyen: ${inter.moyen}<br>
                            Grave: ${inter.grave}<br>
                            Majeur: ${inter.majeur}
                        `;
                    } else {
                        tooltip.style.display = 'none';
                    }
                });
            });

            canvas.addEventListener('mouseout', () => {
                tooltip.style.display = 'none';
            });
        },

        formatDate(dateStr) {
            return $store.statistiques.formatDateAffichage(dateStr);
        }
    }
}
</script>
@endpush
@endsection
