@extends('layouts.app')

@section('title', 'Calendrier des Courses')

@section('actions')
    <form action="{{ route('courses.upload') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
        @csrf
        <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv,.CSV" required style="max-width: 200px;">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-upload me-1"></i>Upload
        </button>
    </form>
@endsection

@section('styles')
<style>
    .calendar-header {
        background: #f8f9fa;
        border-radius: 0.3rem;
    }
    .nav-btn:hover {
        background: #e9ecef;
        cursor: pointer;
    }
    .day-cell {
        height: 100px;
        vertical-align: top;
        transition: background 0.2s;
    }
    .day-cell:hover {
        background: #f8f9fa;
    }
    .course-badge {
        font-size: 0.75rem;
        margin: 1px;
        display: block;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div x-data="calendarApp()" class="container-fluid py-4">

        <!-- Loading Spinner -->
        <template x-if="loading">
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement du calendrier...</p>
            </div>
        </template>

        <!-- En-tête avec navigation -->
        <div class="calendar-header p-3 mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button @click="previousMonth()" class="nav-btn btn p-2" :disabled="loading">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </button>
                <h3 x-text="`${months[currentMonth]} ${currentYear}`" class="mb-0"></h3>
                <button @click="nextMonth()" class="nav-btn btn p-2" :disabled="loading">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select" x-model.number="currentMonth" @change="loadCourses()" :disabled="loading">
                        <template x-for="(month, index) in months" :key="index">
                            <option :value="index" x-text="month" x-bind:selected="currentMonth == index"></option>
                        </template>
                    </select>
                    <input type="number" class="form-control" style="width: 100px;" min="2015"
                           x-model.number="currentYear" @change="loadCourses()" :disabled="loading">
                </div>
            </div>
            <button @click="goToToday()" class="btn btn-primary" :disabled="loading">Aujourd'hui</button>
        </div>

        <!-- Tableau du calendrier -->
        <div class="table-responsive">
            <table class="table table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center">Dim</th>
                        <th scope="col" class="text-center">Lun</th>
                        <th scope="col" class="text-center">Mar</th>
                        <th scope="col" class="text-center">Mer</th>
                        <th scope="col" class="text-center">Jeu</th>
                        <th scope="col" class="text-center">Ven</th>
                        <th scope="col" class="text-center">Sam</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(week, weekIndex) in planning" :key="weekIndex">
                        <tr>
                            <template x-for="(day, dayIndex) in week" :key="`${weekIndex}-${dayIndex}`">
                                <td class="day-cell position-relative">
                                    <div class="fw-bold" x-text="day.jour"></div>
                                    <template x-if="day.events && day.events.length > 0">
                                        <div class="mt-1">
                                            <template x-for="event in day.events" :key="event.start">
                                                <a :href="event.url" class="course-badge btn btn-primary btn-sm w-100 text-start mb-1" :title="event.title">
                                                    <small x-text="event.title"></small>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!day.events || day.events.length === 0">
                                        <div class="text-muted small mt-1">Aucun événement</div>
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/alpine.js') }}" defer></script>
<script>
function calendarApp() {
    return {
        months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        planning: [],
        events: [],
        loading: false,
        currentYear: new Date().getFullYear(),
        currentMonth: new Date().getMonth(),

        init() {
            this.loadCourses();
        },

        loadCourses() {
            this.loading = true;
            const month = this.currentMonth + 1;
            console.log(`Chargement des événements pour ${this.currentYear}-${month}`);
            fetch(`/courses/calendar/${this.currentYear}/${month}`)
                .then(async response => {
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        throw { response, errorData };
                    }
                    return response.json();
                })
                .then(data => {
                    this.events = data;
                    this.updateCalendar();
                    this.loading = false;
                })
                .catch(error => {
                    console.error("Erreur Fetch complète :", error);

                    if (error.response) {
                        console.error("Status :", error.response.status);
                        console.error("Headers :", error.response.headers);
                        console.error("Data (message Laravel) :", error.errorData);
                        alert("Erreur serveur : " + (error.errorData.message ?? 'Erreur inconnue'));
                    }
                    else {
                        console.error("Erreur lors de la configuration :", error.message || error);
                        alert("Erreur Fetch : " + (error.message || error));
                    }

                    this.loading = false;
                });

        },

        updateCalendar() {
            const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            const firstDayIndex = new Date(this.currentYear, this.currentMonth, 1).getDay();

            let matrice = [];
            let dayCounter = 1;

            for (let week = 0; week < 6; week++) {
                matrice[week] = [];

                for (let day = 0; day < 7; day++) {
                    if ((week === 0 && day < firstDayIndex) || dayCounter > lastDay) {
                        matrice[week][day] = { jour: '', events: [] };
                    } else {
                        const currentDate = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(dayCounter).padStart(2, '0')}`;

                        // Récupérer les événements pour cette date
                        const dayEvents = this.events.filter(event => event.start === currentDate);

                        matrice[week][day] = {
                            jour: dayCounter++,
                            events: dayEvents
                        };
                    }
                }
            }

            this.planning = matrice;
        },

        previousMonth() {
            this.currentMonth--;
            if (this.currentMonth < 0) {
                this.currentMonth = 11;
                this.currentYear--;
            }
            this.loadCourses();
        },

        nextMonth() {
            this.currentMonth++;
            if (this.currentMonth > 11) {
                this.currentMonth = 0;
                this.currentYear++;
            }
            this.loadCourses();
        },

        goToToday() {
            const today = new Date();
            this.currentYear = today.getFullYear();
            this.currentMonth = today.getMonth();
            this.loadCourses();
        }
    }
}
</script>
@endsection
