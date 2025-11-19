@extends('layouts.app')

@section('title', 'Détails de la Course')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>


    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid var(--border-color);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .stat-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--primary-color);
    }

    .stat-header i {
        font-size: 24px;
        color: var(--primary-color);
        margin-right: 15px;
    }

    .stat-header h3 {
        color: var(--primary-color);
        margin: 0;
        font-weight: 600;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: white;
        border-radius: 10px;
        border-left: 4px solid var(--primary-color);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .stat-label {
        color: var(--primary-color);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .stat-value {
        color: var(--secondary-color);
        font-weight: 600;
        font-size: 1.1em;
    }

    .excess-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .badge-mineur { background-color: #17a2b8; color: white; }
    .badge-moyen { background-color: #ffc107; color: #212529; }
    .badge-grave { background-color: #fd7e14; color: white; }
    .badge-majeur { background-color: #dc3545; color: white; }

    /* SUPPRESSION TOTALE DU DÉCALAGE JQUERY UI */
    #slider-range {
        box-sizing: border-box !important;
    }

    #slider-range .ui-slider-track {
        margin: 0 !important;
        height: 8px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
    }

    #slider-range .ui-slider-range {
        background: rgba(42, 59, 144, 0.5) !important;
        height: 100px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
    }

    #slider-range .ui-slider-handle {
        width: 20px !important;
        height: 20px !important;
        margin-left: -10px !important;   /* centre parfaitement la poignée */
        margin-top: -10px !important;    /* centre verticalement */
        top: 50% !important;
        background: var(--primary-color) !important;
        border: 3px solid white !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.4) !important;
        cursor: grab !important;
    }

    #slider-range .ui-slider-handle:active {
        cursor: grabbing !important;
    }

    /* Optionnel : effet au survol */
    #slider-range:hover .ui-slider-handle {
        transform: scale(1.2);
    }

    /* Optionnel : petite ombre sur le canvas pour mieux voir le slider */
    #minigraph {
        box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
    }

    .graph-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        border: 1px solid var(--border-color);
    }

    .control-panel {
        background: var(--light-bg);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-custom {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.3s ease;
    }

    .btn-custom:hover {
        background: #1e2a6d;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .table-custom {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .table-custom thead {
        background: var(--primary-color);
        color: white;
    }

    .table-custom th {
        border: none;
        padding: 15px;
        font-weight: 600;
    }

    .table-custom td {
        padding: 12px 15px;
        vertical-align: middle;
        border-color: var(--border-color);
    }

    .table-custom tbody tr:hover {
        background-color: rgba(42, 59, 144, 0.05);
    }

    .section-title {
        color: var(--primary-color);
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 3px solid var(--secondary-color);
        display: inline-block;
    }

    .info-badge {
        background: var(--secondary-color);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.9em;
        margin: 5px;
    }

    #lepoint {
        height: 12px;
        width: 12px;
        border-radius: 50%;
        border: 3px solid var(--secondary-color);
        position: absolute;
        left: -2000px;
        top: 100px;
        box-shadow: 0 0 10px rgba(82, 206, 255, 0.8);
    }

    #tip {
        background-color: white;
        border: 2px solid var(--primary-color);
        position: absolute;
        left: -2000px;
        top: 100px;
        opacity: 0.95;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        padding: 10px;
    }

    .alert-custom {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0" style="color: var(--primary-color);">
                        <i class="fas fa-chart-line me-2"></i>Détails de la Course
                    </h1>
                    <p class="text-muted mb-0">Analyse détaillée des performances et des excès de vitesse</p>
                </div>
                <div class="info-badge">
                    <i class="fas fa-calendar me-1"></i>
                    {{ $course->ladate ? $course->ladate->format('d/m/Y') : 'Date non définie' }}
                </div>
                <button class="btn btn-success" id="btn-enregistrer">
                        <i class="fas fa-save me-2"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>
    <!-- Modal d'enregistrement -->
    <div id="dialog-form" class="dialog-form" title="Enregistrer">
        <span style="color:#283593">Conducteur contrôlé </span>
        <span style="color:#2dc4ea" id="nbcontrole">0</span>
        <span style="color:#283593"> fois dans ce mois.</span>

        <form onsubmit="enregistrer({{ $course->idcourse }}, $('#SV').val(), $('#SA').val(), $('#matricule').val(), $('#RAME').val()); return false;">
            <div class="mb-3">
                <label for="matricule" class="form-label">Matricule :</label>
                <input autocomplete="off" list="cdrs" type="text" id="matricule" name="matricule"
                       placeholder="EX: 310xxx" value="{{$course->matricule}}" class="form-control" required
                       oninput="nbcontrol($('#matricule').val().substr(0,6))"
                       onchange="nbcontrol($('#matricule').val().substr(0,6))">
                <datalist id="cdrs">
                    <option>220361 KEHAL Facih</option>
                    <option>220417 MADANI Abdelaziz</option>
                    <option>310055 MOSTEFAI Nour-Eddine</option>
                    <option>310063 LAHMAR Mohamed Zakaria</option>
                    <option>310091 KHALIFA Mohamed Amine</option>
                    <option>310093 BELLOUTI Abdessamad</option>
                    <option>310094 OUHIBI Ahmed</option>
                    <option>310096 BOUHARAOUA Boumediene</option>
                    <option>310097 SOLTANI Mohamed Amine</option>
                    <option>310099 IMAM Belabbes</option>
                    <option>310101 ZERROUKI Sofiane</option>
                    <!-- Ajoutez les autres options ici -->
                </datalist>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="SV" class="form-label">SV :</label>
                    <input type="text" id="SV" name="SV" placeholder="EX: 40" value="{{$course->SV}}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="SA" class="form-label">SA :</label>
                    <input type="text" id="SA" name="SA" placeholder="EX: 1" value="{{$course->SA}}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="RAME" class="form-label">Tramway :</label>
                    <input type="text" id="RAME" name="RAME" placeholder="EX: 130" value="1{{substr($course->fichier,2,2)}}" class="form-control" required>
                </div>
            </div>

            <div class="d-flex gap-2 mb-3">
                <input type="submit" class="btn btn-primary" name="ok" value="Enregistrer">
                <input type="button" class="btn btn-primary" value="Imprimer" onclick="window.print()">
            </div>
        </form>

        <form onsubmit="commenter({{ $course->idcourse }}, $('#commentaire').val()); return false;">
            <div class="mb-3">
                <label for="commentaire" class="form-label">Commentaire :</label>
                <input id="commentaire" name="commentaire" autocomplete="on" value="{{ $course->commentaire }}"
                       placeholder="Commentaire" class="form-control">
            </div>
            <input type="submit" class="btn bg-primary" name="ok" value="Commenter">
        </form>
    </div>
    <!-- Cartes de Statistiques -->
    <div class="row">
        <!-- Informations Générales -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Informations Course</h3>
                </div>
                <div class="stat-grid">
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-file-alt"></i>Fichier
                        </span>
                        <span class="stat-value">{{ $course->fichier }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-clock"></i>Heure
                        </span>
                        <span class="stat-value">{{ $course->heure ?? 'N/A' }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-map-marker-alt"></i>Enveloppe
                        </span>
                        <span class="stat-value">{{ $enveloppeModel->nom ?? 'Non définie' }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-arrows-alt-h"></i>Décalage
                        </span>
                        <span class="stat-value">{{ $dec ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des Excès -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Excès de Vitesse</h3>
                </div>
                <div class="stat-grid">
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-circle text-info"></i>Mineurs
                        </span>
                        <span class="stat-value">
                            <span class="excess-badge badge-mineur">{{ $nbmineur ?? 0 }}</span>
                        </span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-circle text-warning"></i>Moyens
                        </span>
                        <span class="stat-value">
                            <span class="excess-badge badge-moyen">{{ $nbmoyen ?? 0 }}</span>
                        </span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-circle text-orange"></i>Graves
                        </span>
                        <span class="stat-value">
                            <span class="excess-badge badge-grave">{{ $nbgrave ?? 0 }}</span>
                        </span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-circle text-danger"></i>Majeurs
                        </span>
                        <span class="stat-value">
                            <span class="excess-badge badge-majeur">{{ $nbmajeur ?? 0 }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Événements -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-bell"></i>
                    <h3>Événements</h3>
                </div>
                <div class="stat-grid">
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-bell"></i>Gongs
                        </span>
                        <span class="stat-value">{{ $nbGong ?? 0 }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-volume-up"></i>Klaxons
                        </span>
                        <span class="stat-value">{{ $nbKlaxon ?? 0 }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-exclamation"></i>FU
                        </span>
                        <span class="stat-value">{{ $nbFU ?? 0 }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fas fa-magnet"></i>Patins
                        </span>
                        <span class="stat-value">{{ $nbPatin ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contrôles du Graphique -->
    <div class="row">
        <div class="col-12">
            <div class="control-panel">
                <button class="btn btn-custom" onclick="legraph(data,env,0,data.values.length);document.getElementById('debut').value=0;document.getElementById('fin').value=data.values.length;sliderr()">
                    <i class="fas fa-expand-arrows-alt me-2"></i>Zoom 100%
                </button>
                <span class="text-muted ms-3">
                    <i class="fas fa-sliders-h me-1"></i>Utilisez le slider pour zoomer sur une plage spécifique
                </span>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
<!-- Mini-graphique + Slider parfaitement alignés -->
<div class="row">
    <div class="col-12">
        <div class="graph-container" style="position: relative; overflow: hidden;">
            <div id="minigraph-container" style="position: relative; height: 100px; margin: 20px 0;">
                <!-- Le canvas du mini-graphique -->
                <canvas id="minigraph"
                        width="1500"
                        height="100"
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: block;"></canvas>

                <!-- Le slider EXACTEMENT sur le canvas -->
                <div id="slider-range"
                     style="position: absolute;
                            top: 0; left: 30px; right: 0; bottom: 0;
                            margin: 0 !important;
                            padding: 0 !important;
                            background: transparent !important;
                            border: none !important;
                            height: 100% !important;
                            z-index: 10;"></div>
            </div>

            <small class="text-muted d-block text-center mt-2">
                <i class="fas fa-sliders-h me-1"></i>
                Faites glisser pour zoomer sur une portion du trajet
            </small>
        </div>
    </div>
</div>

    <div class="row">
        <div class="col-12">
            <div class="graph-container">
                <div id="wrapper" style="position: relative; width: 100%; height: 500px;">
                    <canvas id="graph" width="1500" height="500" style="width: 100%; height: 100%;"></canvas>
                    <canvas id="tip" width="200" height="25"></canvas>
                    <span id="lepoint"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableaux des Données -->
    <div class="row">
        <!-- Excès de Vitesse -->
        <div class="col-12 mb-5">
            <h2 class="section-title">
                <i class="fas fa-tachometer-alt me-2"></i>Liste des Excès de Vitesse
            </h2>

            @if(count($exait) > 0)
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Aire</th>
                                <th>Catégorie</th>
                                <th>Vitesse Max</th>
                                <th>Limite</th>
                                <th>Interstation</th>
                                <th>Détail</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exait as $index => $ex)
                                <tr>
                                    <td><strong>{{ $index + 1 }}</strong></td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">{{ $ex['aire'] }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = 'badge-mineur';
                                            if($ex['categorie'] == 'moyen') $badgeClass = 'badge-moyen';
                                            elseif($ex['categorie'] == 'grave') $badgeClass = 'badge-grave';
                                            elseif($ex['categorie'] == 'majeur') $badgeClass = 'badge-majeur';
                                        @endphp
                                        <span class="excess-badge {{ $badgeClass }}">{{ ucfirst($ex['categorie']) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-bold">{{ $ex['max'] }} km/h</span>
                                    </td>
                                    <td>{{ $ex['limite'] }} km/h</td>
                                    <td>
                                        <small class="text-muted">{{ $ex['interstation'] }}</small>
                                    </td>
                                    <td>
                                        <span class="text-info">{{ $ex['detail'] }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick='legraph(data,env,{{$ex["dd"]}},{{$ex["ff"]}});document.getElementById("debut").value={{$ex["dd"]}};document.getElementById("fin").value={{$ex["ff"]}};sliderr()'>
                                            <i class="fas fa-search me-1"></i>Voir
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-success alert-custom text-center">
                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                    <h4 class="alert-heading">Aucun excès de vitesse détecté</h4>
                    <p class="mb-0">Félicitations ! Cette course respecte toutes les limites de vitesse.</p>
                </div>
            @endif
        </div>

        <!-- Temps de Parcours -->
        <div class="col-12">
            <h2 class="section-title" style="color: var(--primary-color);">
                <i class="fas fa-clock me-2"></i>Temps de Parcours Relevés
            </h2>

            @if(count($parcours) > 0)
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Distance</th>
                                <th>Vitesse Autorisée</th>
                                <th>Station</th>
                                <th>Heure Passage</th>
                                <th>Temps Roulage</th>
                                <th>Temps Échange</th>
                                <th>Heure Départ</th>
                                <th>Différence</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcours as $index => $par)
                                <tr>
                                    <td><strong>{{ $index + 1 }}</strong></td>
                                    <td>{{ $par['distance'] }} m</td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">{{ $par['vitesse_autorisee'] }} km/h</span>
                                    </td>
                                    <td>
                                        <strong>{{ $par['station'] }}</strong>
                                    </td>
                                    <td>
                                        <code class="bg-light">{{ $par['heure_passage'] }}</code>
                                    </td>
                                    <td>{{ $par['temps_roulage'] }}</td>
                                    <td>{{ $par['temps_echange'] }}</td>
                                    <td>
                                        <code class="bg-light">{{ $par['heure_depart'] }}</code>
                                    </td>
                                    <td>
                                        @if($par['difference'] > 0)
                                            <span class="text-success">+{{ $par['difference'] }}</span>
                                        @elseif($par['difference'] < 0)
                                            <span class="text-danger">{{ $par['difference'] }}</span>
                                        @else
                                            <span class="text-muted">{{ $par['difference'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning alert-custom text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <h4 class="alert-heading">Aucun temps de parcours</h4>
                    <p class="mb-0">Aucune donnée de temps de parcours disponible pour cette course.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Champs cachés pour le fonctionnement -->
<input type="number" id="debut" style="display: none;" name="debut" value="1" min="0" max="{{ count($pointcourses) }}">
<input type="number" id="fin" style="display: none;" name="fin" value="{{ count($pointcourses) }}" min="0" max="{{ count($pointcourses) }}">
<input type="button" id="zoom" style="display: none;" value="zoom" name="zoom">
@endsection

@section('scripts')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/mongraph3.js') }}"></script>

<script>
    function sliderr(){
        $("#slider-range").slider("option","values",[$("#debut").val(),$("#fin").val()]);
    }

    // Fonctions pour le formulaire d'enregistrement
    function enregistrer(idcourse, sv, sa, matricule, rame) {
        $.post("{{ route('courses.enregistrer') }}", {
            idcourse: idcourse,
            SV: sv,
            SA: sa,
            matricule: parseInt(matricule.substr(0,6)),
            RAME: rame,
            _token: "{{ csrf_token() }}"
        }).done(function(data) {
            alert(data.message || "Enregistrement réussi");
            $("#dialog-form").dialog("close");
        }).fail(function(xhr) {
            console.log(xhr);
            if (xhr.responseJSON && xhr.responseJSON.error) {
                alert(xhr.responseJSON.error);
            } else {
                alert("Erreur lors de l'enregistrement");
            }
        });
    }

    function commenter(idcourse, commentaire) {
        $.post("{{ route('courses.commenter') }}", {
            idcourse: idcourse,
            commentaire: commentaire,
            _token: "{{ csrf_token() }}"
        }).done(function(data) {
            alert(data.message || "Commentaire enregistré");
        }).fail(function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.error) {
                alert(xhr.responseJSON.error);
            } else {
                alert("Erreur lors de l'enregistrement du commentaire");
            }
        });
    }

    function nbcontrol(matricule) {
        if (matricule.length >= 6) {
            $.get("{{ route('courses.nbcontroles') }}", {
                matricule: matricule,
                _token: "{{ csrf_token() }}"
            })
            .done(function(data) {
                $("#nbcontrole").text(data.nbControles || 0);
            })
            .fail(function() {
                $("#nbcontrole").text("0");
            });
        } else {
            $("#nbcontrole").text("0");
        }
    }

    // Vos données existantes...
    var data = { values: [
        @foreach($pointcourses as $i => $point)
            @php
                $traction = 0;
                if($point['freinage'] == 1) $traction = 1;
                if($point['traction'] == 1) $traction = 2;
            @endphp
            {
                X: {{ $i }},
                Y: {{ intval($point['vitesse']) }},
                color: '{{ $point['couleur'] }}',
                nom: '{{ $point['text'] }}',
                gong: '{{ $point['gong'] }}',
                traction: '{{ $traction }}',
                heure: '{{ substr($point['temps'], 11) }}',
                FU: '{{ $point['FU'] }}',
                klaxon: '{{ $point['klaxon'] }}',
                patin: '{{ $point['patin'] }}'
            }@if(!$loop->last),@endif
        @endforeach
    ]};

    var env = { values: [
        @foreach($nouveauEnv as $i => $envPoint)
            {
                X: '{{ floor($envPoint['x']) }}',
                Y: '{{ $envPoint['y'] }}',
                nom: '{{ addslashes(utf8_encode($envPoint['label'])) }}',
                sta: '{{ $envPoint['stp'] }}'
            }@if(!$loop->last),@endif
        @endforeach
    ]};

    @if($dec > 150)
        env.values.forEach(x => x.Y = 60);
        data.values.forEach(x => x.color = '#283593');
    @endif

    $(document).ready(function() {
        // Initialisation des graphiques
        legraph(data, env, 0, data.values.length);
        leminigraph(data, env, 0, data.values.length);

        if($("#minigraphe").val() != undefined) {
            leminigraphe(data, env, 0, data.values.length);
        }

        // Configuration du slider
        $("#slider-range").slider({
            range: true,
            min: 0,
            max: data.values.length,
            values: [0, data.values.length],
            slide: function(event, ui) {
                $("#debut").val(ui.values[0]);
                $("#fin").val(ui.values[1]);
                legraph(data, env, ui.values[0], ui.values[1]);
            }
        });

        // Configuration de la dialog d'enregistrement
        $("#dialog-form").dialog({
            autoOpen: false,
            height: 600,
            width: 500,
            modal: true,
            buttons: {
                "Fermer": function() {
                    $(this).dialog("close");
                }
            },
            close: function() {
                // Réinitialiser le formulaire
                $(this).find("form")[0].reset();
                $("#nbcontrole").text("0");
            }
        });

        // Ouvrir la dialog au clic sur le bouton
        $("#btn-enregistrer").click(function() {
            $("#dialog-form").dialog("open");
        });

        // Animation des cartes au chargement
        $('.stat-card').each(function(i) {
            $(this).delay(i * 200).animate({opacity: 1, marginTop: 0}, 500);
        });
    });
</script>
@endsection
