{{-- resources/views/statistiques/mensuelle.blade.php --}}
@extends('statistiques.layout')

@section('title', 'Statistiques - Synthèse par Mois')

@section('statistiques-content')
<div x-data="syntheseMensuelle()" x-init="init()">
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
            <i class="fas fa-calendar-alt me-2"></i>Synthèse par mois
        </h4>
        <div class="d-flex align-items-center">
            <select x-model="selectedYear" @change="changeYear" class="form-select form-select-sm me-2" style="width: auto;">
                <template x-for="year in years" :key="year">
                    <option :value="year" x-text="year"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <template x-for="(mois, index) in months" :key="index">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" :class="{ active: activeMonth === mois.numero }"
                                @click="activeMonth = mois.numero"
                                x-text="`${mois.nom} (${mois.nbCourses})`"></button>
                    </li>
                </template>
            </ul>
        </div>

        <div class="card-body">
            <!-- Statistiques du mois -->
            <div class="row mb-4" x-show="activeMonth">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Statistiques du mois</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td>Conducteurs contrôlés</td>
                                        <td class="fw-bold" x-text="currentMonthStats.nbConducteurs"></td>
                                    </tr>
                                    <tr>
                                        <td>Courses contrôlées</td>
                                        <td class="fw-bold" x-text="currentMonthStats.nbCourses"></td>
                                    </tr>
                                    <tr>
                                        <td>Kilomètres contrôlés</td>
                                        <td class="fw-bold" x-text="`${(currentMonthStats.kmTotal / 1000).toFixed(1)} km`"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Répartition des excès</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-end">Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge badge-mineur">Mineur</span></td>
                                        <td class="text-end" x-text="currentMonthStats.mineur"></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge badge-moyen">Moyen</span></td>
                                        <td class="text-end" x-text="currentMonthStats.moyen"></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge badge-grave">Grave</span></td>
                                        <td class="text-end" x-text="currentMonthStats.grave"></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge badge-majeur">Majeur</span></td>
                                        <td class="text-end" x-text="currentMonthStats.majeur"></td>
                                    </tr>
                                    <tr class="table-secondary">
                                        <td class="fw-bold">TOTAL</td>
                                        <td class="text-end fw-bold" x-text="currentMonthStats.totalExces"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des courses du mois -->
            <div x-show="activeMonth">
                <h6 class="mb-3">Liste des courses du mois</h6>
                <div class="table-responsive">
                    <table class="table table-hover stat-table">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Rame</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Voie</th>
                                <th>SV</th>
                                <th>SA</th>
                                <th>Matricule</th>
                                <th>Conducteur</th>
                                <th>Mineur</th>
                                <th>Moyen</th>
                                <th>Grave</th>
                                <th>Majeur</th>
                                <th class="table-secondary">Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="course in currentMonthCourses" :key="course.idcourse">
                                <tr>
                                    <td x-text="`{{ session('site') }}-DEX-CPE-${course.code}-${course.ladate.substr(0,4)}`"></td>
                                    <td x-text="course.rame"></td>
                                    <td x-text="formatDate(course.ladate)"></td>
                                    <td x-text="course.heure"></td>
                                    <td x-text="course.voie"></td>
                                    <td x-text="course.SV"></td>
                                    <td x-text="course.SA"></td>
                                    <td x-text="course.matricule"></td>
                                    <td x-text="course.nom"></td>
                                    <td x-text="course.mineur"></td>
                                    <td x-text="course.moyen"></td>
                                    <td x-text="course.grave"></td>
                                    <td x-text="course.majeur"></td>
                                    <td class="table-secondary fw-bold" x-text="course.totalExces"></td>
                                    <td>
                                        <button @click="ouvrirCourse(course.idcourse)" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function syntheseMensuelle() {
    return {
        selectedYear: new Date().getFullYear(),
        activeMonth: null,

        // Propriétés calculées
        get donneesFiltrees() {
            const filtres = $store.statistiques.filtres;
            const donnees = $store.statistiques.donnees;

            if (!donnees.exces || !donnees.courses) {
                return { exces: [], courses: [] };
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

        get years() {
            const dates = this.donneesFiltrees.courses.map(c => new Date(c.ladate).getFullYear());
            const uniqueYears = [...new Set(dates)].sort((a, b) => b - a);
            return uniqueYears.length > 0 ? uniqueYears : [new Date().getFullYear()];
        },

        get months() {
            const moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                             'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

            const monthsData = [];

            for (let i = 1; i <= 12; i++) {
                const monthStr = i.toString().padStart(2, '0');
                const monthCourses = this.donneesFiltrees.courses.filter(c => {
                    const date = new Date(c.ladate);
                    return date.getFullYear() == this.selectedYear &&
                           (date.getMonth() + 1) == i;
                });

                if (monthCourses.length > 0) {
                    monthsData.push({
                        numero: i,
                        nom: moisNoms[i - 1],
                        nbCourses: monthCourses.length
                    });
                }
            }

            // Définir le mois actif au premier mois disponible
            if (monthsData.length > 0 && !this.activeMonth) {
                this.activeMonth = monthsData[0].numero;
            }

            return monthsData;
        },

        get currentMonthStats() {
            if (!this.activeMonth) return {};

            const monthStr = this.activeMonth.toString().padStart(2, '0');
            const monthCourses = this.donneesFiltrees.courses.filter(c => {
                const date = new Date(c.ladate);
                return date.getFullYear() == this.selectedYear &&
                       (date.getMonth() + 1) == this.activeMonth;
            });

            const monthExces = this.donneesFiltrees.exces.filter(e => {
                const date = new Date(e.ladate);
                return date.getFullYear() == this.selectedYear &&
                       (date.getMonth() + 1) == this.activeMonth;
            });

            const conducteurs = new Set(monthCourses.map(c => c.matricule));
            const kmTotal = monthCourses.reduce((total, c) => total + (c.discom || 0), 0);

            return {
                nbConducteurs: conducteurs.size,
                nbCourses: monthCourses.length,
                kmTotal: kmTotal,
                mineur: monthExces.filter(e => e.categorie === 'mineur').length,
                moyen: monthExces.filter(e => e.categorie === 'moyen').length,
                grave: monthExces.filter(e => e.categorie === 'grave').length,
                majeur: monthExces.filter(e => e.categorie === 'majeur').length,
                totalExces: monthExces.length
            };
        },

        get currentMonthCourses() {
            if (!this.activeMonth) return [];

            return this.donneesFiltrees.courses
                .filter(c => {
                    const date = new Date(c.ladate);
                    return date.getFullYear() == this.selectedYear &&
                           (date.getMonth() + 1) == this.activeMonth;
                })
                .map(course => {
                    const excesCourse = this.donneesFiltrees.exces.filter(e => e.idcourse === course.idcourse);
                    return {
                        ...course,
                        mineur: excesCourse.filter(e => e.categorie === 'mineur').length,
                        moyen: excesCourse.filter(e => e.categorie === 'moyen').length,
                        grave: excesCourse.filter(e => e.categorie === 'grave').length,
                        majeur: excesCourse.filter(e => e.categorie === 'majeur').length,
                        totalExces: excesCourse.length
                    };
                })
                .sort((a, b) => new Date(a.ladate) - new Date(b.ladate));
        },

        // Méthodes
        init() {
            // Écouter les changements de filtres
            window.addEventListener('statistiques-filtres-appliques', () => {
                // Réinitialiser l'année sélectionnée
                const dates = this.donneesFiltrees.courses.map(c => new Date(c.ladate).getFullYear());
                if (dates.length > 0) {
                    this.selectedYear = Math.max(...dates);
                }
            });

            // Écouter le chargement initial des données
            window.addEventListener('statistiques-donnees-chargees', () => {
                const dates = this.donneesFiltrees.courses.map(c => new Date(c.ladate).getFullYear());
                if (dates.length > 0) {
                    this.selectedYear = Math.max(...dates);
                }
            });
        },

        changeYear() {
            // Changer l'année réinitialise le mois actif
            if (this.months.length > 0) {
                this.activeMonth = this.months[0].numero;
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
