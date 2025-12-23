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
        flex: 1;                 /* PREND TOUT L’ESPACE RESTANT */
        padding: 20px;
        overflow-x: hidden;
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
        z-index: 1040;
    }

    /* Ajouté pour modaux Alpine */
    [x-cloak] {
        display: none !important;
    }

    /* styles pour le modal Alpine */
    .modal-alpine-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999; /* Augmenté */
    }

    .modal-alpine {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        border-radius: 0.3rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        z-index: 10000; /* Augmenté */
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-alpine .modal-header {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-alpine .modal-body {
        padding: 1rem;
        overflow-y: auto;
        flex: 1;
    }

    .modal-alpine .modal-footer {
        padding: 0.75rem 1rem;
        border-top: 1px solid #dee2e6;
        background-color: #f8f9fa;
        display: flex;
        justify-content: flex-end;
    }

    /* S'assurer que le modal est au-dessus de tout */
    .modal-alpine-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="statistiques-container">

    <!-- Sidebar filtres -->
    <aside class="filtres-sidebar">
        @include('statistiques.partials.filtres')
    </aside>

    <!-- Contenu principal -->
    <main class="statistiques-content">
        @yield('statistiques-content')
    </main>

</div>

@endsection

@push('scripts')
{{-- Bootstrap DOIT être avant Alpine --}}
<script src="/js/bootstrap.js"></script>
<script src="/js/alpine.min.js" defer></script>
<script src="/js/chart.js"></script>
<script src="/js/axios.min.js"></script>

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

        debugConducteurs() {
            console.log('Conducteurs liste:', this.donnees.conducteursListe);
            console.log('Conducteurs sélectionnés:', this.filtres.conducteurs);
            console.log('Données exces:', this.donnees.exces);
        },

        // Modifier la fonction init() pour formater correctement les conducteurs
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

                // Extraire et formater correctement la liste des conducteurs
                const conducteursSet = new Set();
                this.donnees.exces.forEach(exce => {
                    const conducteurStr = `${exce.matricule} ${exce.nom}`.trim();
                    if (conducteurStr) {
                        conducteursSet.add(conducteurStr);
                    }
                });

                this.donnees.courses.forEach(course => {
                    const conducteurStr = `${course.matricule} ${course.nom}`.trim();
                    if (conducteurStr) {
                        conducteursSet.add(conducteurStr);
                    }
                });

                // Formater pour Alpine.js
                this.donnees.conducteursListe = Array.from(conducteursSet)
                    .sort()
                    .map(c => ({
                        value: c,
                        text: c
                    }));

                // Initialiser la sélection avec TOUS les conducteurs
                this.filtres.conducteurs = this.donnees.conducteursListe.map(c => c.value);

                console.log('Données chargées:', {
                    exces: this.donnees.exces.length,
                    courses: this.donnees.courses.length,
                    conducteurs: this.donnees.conducteursListe.length
                });

                // Émettre l'événement
                window.dispatchEvent(new CustomEvent('statistiques-donnees-chargees'));

                // Appliquer les filtres après chargement
                setTimeout(() => this.appliquerFiltres(), 100);

            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des données.');
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

            console.log('Filtres appliqués:', {
                excesFiltres: donneesFiltrees.exces.length,
                coursesFiltrees: donneesFiltrees.courses.length
            });

            // Émettre l'événement de filtres appliqués
            window.dispatchEvent(new CustomEvent('statistiques-filtres-appliques', {
                detail: donneesFiltrees
            }));

            this.loading = false;
        },

        reinitialiserFiltres() {
            console.log('Réinitialisation des filtres');

            // Réinitialiser les filtres
            this.filtres.categories = ['mineur', 'moyen', 'grave', 'majeur'];
            this.filtres.voies = ['V1', 'V2'];

            // Réinitialiser à TOUS les conducteurs
            if (this.donnees.conducteursListe && this.donnees.conducteursListe.length > 0) {
                this.filtres.conducteurs = this.donnees.conducteursListe.map(c => c.value);
            }

            // Réinitialiser la recherche
            this.filtres.rechercheConducteur = '';

            // Réappliquer les filtres
            this.appliquerFiltres();
        },

        async exporterCSV() {
            // Debug temporaire
            this.debugConducteurs();

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
