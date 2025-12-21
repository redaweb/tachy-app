{{-- resources/views/statistiques/layout.blade.php --}}
@extends('layouts.app')

@section('styles')
<style>
    .statistiques-container {
        display: flex;
        min-height: calc(100vh - 140px);
    }

    .filtres-sidebar {
        width: 250px;
        min-width: 250px;
        background-color: #f8f9fa;
        border-right: 1px solid #dee2e6;
        padding: 20px 15px;
        position: sticky;
        top: 56px;
        height: calc(100vh - 140px);
        overflow-y: auto;
    }

    .statistiques-content {
        flex: 1;
        padding: 20px;
        overflow-x: auto;
    }

    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }

    .stat-table {
        font-size: 0.85rem;
    }

    .stat-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-mineur {
        background-color: rgba(75, 192, 40, 0.9);
        color: white;
    }

    .badge-moyen {
        background-color: rgba(255, 206, 86, 0.9);
        color: #212529;
    }

    .badge-grave {
        background-color: rgba(200, 50, 0, 0.9);
        color: white;
    }

    .badge-majeur {
        background-color: rgba(255, 50, 50, 0.9);
        color: white;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1060;
    }
</style>
@endsection

@section('content')
<div class="statistiques-container">
    <!-- Barre latérale des filtres -->
    <div class="filtres-sidebar">
        @include('statistiques.partials.filtres')
    </div>

    <!-- Contenu principal -->
    <div class="statistiques-content">
        @yield('statistiques-content')
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// Store Alpine.js global pour les statistiques
document.addEventListener('alpine:init', () => {
    Alpine.store('statistiques', {
        loading: false,
        filtres: {
            debut: '',
            fin: '',
            categories: ['mineur', 'moyen', 'grave', 'majeur'],
            voies: ['V1', 'V2'],
            conducteurs: [],
            rechercheConducteur: ''
        },
        donnees: {
            exces: [],
            courses: [],
            conducteursListe: []
        },

        init() {
            // Initialiser les dates (mois précédent)
            const aujourdhui = new Date();
            const debutMois = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth() - 1, 1);
            const finMois = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth(), 0);

            this.filtres.debut = debutMois.toISOString().split('T')[0];
            this.filtres.fin = finMois.toISOString().split('T')[0];

            this.chargerDonnees();
        },

        async chargerDonnees() {
            this.loading = true;

            try {
                const response = await axios.get('{{ route("statistiques.api.exces") }}', {
                    params: {
                        debut: this.formatDatePourAPI(this.filtres.debut),
                        fin: this.formatDatePourAPI(this.filtres.fin)
                    }
                });

                this.donnees = response.data;

                // Extraire la liste des conducteurs
                const conducteursSet = new Set();
                this.donnees.courses.forEach(course => {
                    conducteursSet.add(`${course.matricule} ${course.nom}`);
                });

                this.donnees.conducteursListe = Array.from(conducteursSet).sort().map(c => ({
                    value: c,
                    text: c
                }));

                // Initialiser la sélection des conducteurs
                this.filtres.conducteurs = this.donnees.conducteursListe.map(c => c.value);

                // Émettre l'événement de chargement terminé
                window.dispatchEvent(new CustomEvent('statistiques-donnees-chargees'));

            } catch (error) {
                console.error('Erreur lors du chargement des données:', error);
                alert('Erreur lors du chargement des données. Veuillez réessayer.');
            } finally {
                this.loading = false;
            }
        },

        formatDatePourAPI(dateStr) {
            const date = new Date(dateStr);
            return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
        },

        formatDateAffichage(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
        },

        appliquerFiltres() {
            this.loading = true;

            // Filtrer les données côté client
            const donneesFiltrees = {
                exces: this.donnees.exces.filter(exce => {
                    const dateExce = new Date(exce.ladate);
                    const dateDebut = new Date(this.filtres.debut);
                    const dateFin = new Date(this.filtres.fin);

                    return dateExce >= dateDebut &&
                           dateExce <= dateFin &&
                           this.filtres.voies.includes(exce.voie) &&
                           this.filtres.categories.includes(exce.categorie) &&
                           this.filtres.conducteurs.includes(exce.matricule + ' ' + exce.nom);
                }),
                courses: this.donnees.courses.filter(course => {
                    const dateCourse = new Date(course.ladate);
                    const dateDebut = new Date(this.filtres.debut);
                    const dateFin = new Date(this.filtres.fin);

                    return dateCourse >= dateDebut &&
                           dateCourse <= dateFin &&
                           this.filtres.voies.includes(course.voie) &&
                           this.filtres.conducteurs.includes(course.matricule + ' ' + course.nom);
                })
            };

            // Émettre l'événement de filtres appliqués
            window.dispatchEvent(new CustomEvent('statistiques-filtres-appliques', {
                detail: donneesFiltrees
            }));

            this.loading = false;
        },

        reinitialiserFiltres() {
            this.filtres.categories = ['mineur', 'moyen', 'grave', 'majeur'];
            this.filtres.voies = ['V1', 'V2'];
            this.filtres.conducteurs = this.donnees.conducteursListe.map(c => c.value);
            this.appliquerFiltres();
        },

        async exporterCSV() {
            try {
                const params = new URLSearchParams({
                    debut: this.formatDatePourAPI(this.filtres.debut),
                    fin: this.formatDatePourAPI(this.filtres.fin),
                    conducteurs: this.filtres.conducteurs.join(','),
                    categories: this.filtres.categories.join(','),
                    voies: this.filtres.voies.join(',')
                });

                window.location.href = `{{ route("statistiques.export-csv") }}?${params.toString()}`;
            } catch (error) {
                console.error('Erreur lors de l\'export:', error);
                alert('Erreur lors de l\'export. Veuillez réessayer.');
            }
        }
    });
});
</script>
@endpush
