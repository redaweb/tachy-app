{{-- resources/views/journal.blade.php --}}
@extends('layouts.app')

@section('title', 'Journal des activités')

@section('content')
<div class="row">
    <!-- Barre latérale de filtrage -->
    <div class="col-md-3">
        <div class="card sticky-top">
            <div class="card-header bg-light">
                <h6 class="mb-0">Filtres</h6>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <!-- Filtre par dates -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Période</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Du</span>
                            <input type="date" id="debut" class="form-control" />
                        </div>
                        <div class="input-group input-group-sm mt-2">
                            <span class="input-group-text">Au</span>
                            <input type="date" id="fin" class="form-control" />
                        </div>
                    </div>

                    <!-- Filtre par actions (onglet Journal) -->
                    <div id="actionFilter" class="mb-3">
                        <label class="form-label fw-bold">Actions</label>
                        <div id="actionCheckboxes" class="form-check-group">
                            <!-- Sera rempli dynamiquement -->
                        </div>
                    </div>

                    <!-- Filtre par utilisateurs -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Utilisateurs</label>
                        <button type="button" class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#userModal">
                            <i class="fas fa-users me-2"></i>Sélectionner (<span id="userCount">0</span>)
                        </button>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="d-grid gap-2">
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-redo me-2"></i>Réinitialiser
                        </button>
                        <button type="button" id="exportBtn" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download me-2"></i>Export CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">
                <!-- Onglets -->
                <ul class="nav nav-tabs mb-3" id="journalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="journal-tab" data-bs-toggle="tab" data-bs-target="#journal" type="button" role="tab">
                            <i class="fas fa-book me-2"></i>Journal
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">
                            <i class="fas fa-route me-2"></i>Courses ouvertes non enregistrées
                        </button>
                    </li>
                </ul>

                <!-- Contenu des onglets -->
                <div class="tab-content" id="journalTabContent">
                    <!-- Onglet Journal -->
                    <div class="tab-pane fade show active" id="journal" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Matricule</th>
                                        <th>Nom</th>
                                        <th>Action</th>
                                        <th>Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="journalTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Chargement...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination justify-content-center" id="journalPagination"></ul>
                        </nav>
                    </div>

                    <!-- Onglet Courses -->
                    <div class="tab-pane fade" id="courses" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Distance</th>
                                        <th>Enveloppe</th>
                                        <th>Commentaire</th>
                                        <th>Ouverte par</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="coursesTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Chargement...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination justify-content-center" id="coursesPagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sélection des utilisateurs -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sélectionner les utilisateurs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="userSearch" class="form-control" placeholder="Rechercher un utilisateur...">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="selectAllUsers">
                    <label class="form-check-label" for="selectAllUsers">
                        Tous les utilisateurs
                    </label>
                </div>
                <div id="userCheckboxes" class="form-check-group">
                    <!-- Sera rempli dynamiquement -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const journal = {
        data: {
            journalData: [],
            coursesData: [],
            filteredJournal: [],
            filteredCourses: [],
            users: new Set(),
            actions: new Set(),
            selectedUsers: new Set(),
            selectedActions: new Set(),
            currentPageJournal: 1,
            currentPageCourses: 1,
            perPage: 10
        },

        init() {
            this.setDefaultDates();
            this.attachEventListeners();
            this.loadData();
        },

        setDefaultDates() {
            const debut = new Date();
            debut.setMonth(debut.getMonth() - 1);
            document.getElementById('debut').valueAsDate = debut;
            document.getElementById('fin').valueAsDate = new Date();
        },

        attachEventListeners() {
            document.getElementById('debut').addEventListener('change', () => this.loadData());
            document.getElementById('fin').addEventListener('change', () => this.loadData());
            document.getElementById('resetBtn').addEventListener('click', () => this.reset());
            document.getElementById('exportBtn').addEventListener('click', () => this.exportCSV());
            document.getElementById('selectAllUsers').addEventListener('change', (e) => this.selectAllUsers(e.target.checked));
        },

        loadData() {
            const debut = document.getElementById('debut').value;
            const fin = document.getElementById('fin').value;

            fetch(`{{ route('api.journal') }}?debut=${debut}&fin=${fin}`)
                .then(response => response.json())
                .then(data => {
                    this.data.journalData = data.journal || [];
                    this.data.coursesData = data.courses || [];

                    this.data.users.clear();
                    this.data.actions.clear();

                    this.data.journalData.forEach(item => {
                        this.data.users.add(`${item.iduser} ${item.nom}`);
                        this.data.actions.add(item.action);
                    });

                    this.renderActionFilters();
                    this.renderUserFilters();
                    this.selectAllItems();
                    this.filterAndRender();
                })
                .catch(error => console.error('Erreur:', error));
        },

        renderActionFilters() {
            const container = document.getElementById('actionCheckboxes');
            container.innerHTML = '';

            Array.from(this.data.actions).sort().forEach(action => {
                const div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = `
                    <input class="form-check-input action-checkbox" type="checkbox" value="${action}" id="action_${action}" checked>
                    <label class="form-check-label" for="action_${action}">${action}</label>
                `;
                container.appendChild(div);
            });

            document.querySelectorAll('.action-checkbox').forEach(el => {
                el.addEventListener('change', () => this.filterAndRender());
            });
        },

        renderUserFilters() {
            const container = document.getElementById('userCheckboxes');
            container.innerHTML = '';

            Array.from(this.data.users).sort().forEach(user => {
                const div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = `
                    <input class="form-check-input user-checkbox" type="checkbox" value="${user}" id="user_${user}" checked>
                    <label class="form-check-label" for="user_${user}">${user}</label>
                `;
                container.appendChild(div);
            });

            document.querySelectorAll('.user-checkbox').forEach(el => {
                el.addEventListener('change', () => this.updateUserCount());
            });
        },

        selectAllItems() {
            this.data.selectedUsers = new Set(this.data.users);
            this.data.selectedActions = new Set(this.data.actions);
            this.updateUserCount();
        },

        selectAllUsers(checked) {
            document.querySelectorAll('.user-checkbox').forEach(el => {
                el.checked = checked;
            });
            this.updateUserCount();
        },

        updateUserCount() {
            const count = document.querySelectorAll('.user-checkbox:checked').length;
            document.getElementById('userCount').textContent = count;
            this.filterAndRender();
        },

        filterAndRender() {
            this.data.selectedUsers = new Set(
                Array.from(document.querySelectorAll('.user-checkbox:checked')).map(el => el.value)
            );
            this.data.selectedActions = new Set(
                Array.from(document.querySelectorAll('.action-checkbox:checked')).map(el => el.value)
            );

            this.data.filteredJournal = this.data.journalData.filter(item =>
                this.data.selectedUsers.has(`${item.iduser} ${item.nom}`) &&
                this.data.selectedActions.has(item.action)
            );

            this.data.filteredCourses = this.data.coursesData;
            this.renderTables();
        },

        renderTables() {
            this.renderJournalTable();
            this.renderCoursesTable();
        },

        renderJournalTable() {
            const tbody = document.getElementById('journalTableBody');
            const start = (this.data.currentPageJournal - 1) * this.data.perPage;
            const end = start + this.data.perPage;
            const pageData = this.data.filteredJournal.slice(start, end);

            if (pageData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Aucun enregistrement</td></tr>';
            } else {
                tbody.innerHTML = pageData.map(item => `
                    <tr>
                        <td>${item.ladate}</td>
                        <td>${item.heure || '-'}</td>
                        <td>${item.iduser}</td>
                        <td>${item.nom}</td>
                        <td><span class="badge bg-info">${item.action}</span></td>
                        <td>${item.detail || '-'}</td>
                    </tr>
                `).join('');
            }

            this.renderPagination('journalPagination', this.data.filteredJournal.length, 'journal');
        },

        renderCoursesTable() {
            const tbody = document.getElementById('coursesTableBody');
            const start = (this.data.currentPageCourses - 1) * this.data.perPage;
            const end = start + this.data.perPage;
            const pageData = this.data.filteredCourses.slice(start, end);

            if (pageData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Aucune course</td></tr>';
            } else {
                tbody.innerHTML = pageData.map(item => `
                    <tr>
                        <td>${item.ladate}</td>
                        <td>${item.heure || '-'}</td>
                        <td>${item.distance || '-'}</td>
                        <td>${item.nomenveloppe}</td>
                        <td>${item.commentaire || '-'}</td>
                        <td>${item.ouvertPar || '-'}</td>
                        <td>
                            <a href="{{ route('courses.show', '') }}/${item.idcourse}" class="btn btn-sm btn-info">
                                Ouvrir
                            </a>
                        </td>
                    </tr>
                `).join('');
            }

            this.renderPagination('coursesPagination', this.data.filteredCourses.length, 'courses');
        },

        renderPagination(elementId, totalItems, type) {
            const totalPages = Math.ceil(totalItems / this.data.perPage);
            const container = document.getElementById(elementId);
            container.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === (type === 'journal' ? this.data.currentPageJournal : this.data.currentPageCourses) ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                li.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (type === 'journal') {
                        this.data.currentPageJournal = i;
                    } else {
                        this.data.currentPageCourses = i;
                    }
                    this.renderTables();
                });
                container.appendChild(li);
            }
        },

        reset() {
            this.setDefaultDates();
            this.selectAllItems();
            this.loadData();
        },

        exportCSV() {
            const activeTab = document.querySelector('.tab-pane.active').id;
            let csv = '';
            let filename = '';

            if (activeTab === 'journal') {
                csv = 'Date;Heure;Matricule;Nom;Action;Detail\n';
                this.data.filteredJournal.forEach(item => {
                    csv += `${item.ladate};${item.heure || ''};${item.iduser};${item.nom};${item.action};${item.detail || ''}\n`;
                });
                filename = 'journal.csv';
            } else {
                csv = 'Date;Heure;Distance;Enveloppe;Commentaire;Ouverte par\n';
                this.data.filteredCourses.forEach(item => {
                    csv += `${item.ladate};${item.heure || ''};${item.distance || ''};${item.nomenveloppe};${item.commentaire || ''};${item.ouvertPar || ''}\n`;
                });
                filename = 'courses.csv';
            }

            const link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
            link.download = filename;
            link.click();
        }
    };

    journal.init();
});
</script>
@endsection
