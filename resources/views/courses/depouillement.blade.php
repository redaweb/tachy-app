<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $ville = session('site', 'ORN');
        $referenceCode = $ville . '-DEX-RA-' . sprintf('%04d', $course->code ?? 0) . '-' . ($lannee ?? date('Y'));
    @endphp

    <title>{{ $referenceCode }}</title>

    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ========== IMPRESSION PAYSAGE ========== */
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Parisine Office Std', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #fff;
            color: #1e293b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-container {
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
            padding: 5px 0;
        }

        /* -------- SAUTS DE PAGE -------- */
        .saut-page {
            page-break-after: always;
            break-after: page;
            height: 0;
            visibility: hidden;
            display: block;
        }

        .no-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* -------- HEADER -------- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 55px;
            margin-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
        }

        .header-section img {
            height: 44px;
            object-fit: contain;
        }

        /* -------- BANNER TITRE & CODE -------- */
        .main-title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #2a3b90 0%, #1e296b 100%);
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 6px;
            margin-bottom: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .doc-code {
            font-size: 11px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* -------- REFERENCE & CITES -------- */
        .reference-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0284c7;
            padding: 6px 12px;
            border-radius: 4px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .reference-title {
            font-weight: 600;
            color: #475569;
        }

        .reference-code {
            color: #0284c7;
            font-weight: 700;
            font-family: monospace;
            font-size: 13px;
        }

        /* BADGES VILLES */
        .villes-grid {
            display: flex;
            gap: 5px;
            margin-bottom: 8px;
        }

        .ville-badge {
            flex: 1;
            text-align: center;
            padding: 5px 4px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #64748b;
        }

        .ville-badge.active {
            background: #2a3b90;
            color: #ffffff;
            border-color: #2a3b90;
            box-shadow: 0 2px 4px rgba(42, 59, 144, 0.2);
        }

        /* -------- BLOCS CARTES D'INFORMATIONS -------- */
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 12px;
            margin-bottom: 6px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 1px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
        }

        /* -------- GRAPHIC CANVAS -------- */
        .canvas-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #fafafa;
            padding: 6px;
            margin-bottom: 8px;
            text-align: center;
        }

        canvas#minigraphe {
            width: 100%;
            max-width: 1120px;
            height: 260px;              /* Réduit pour le paysage */
            border-radius: 4px;
        }

        /* -------- CARTES STATISTIQUES -------- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 8px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #2a3b90;
            border-radius: 6px;
            padding: 6px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .stat-title {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
        }

        .stat-number {
            font-size: 16px;
            font-weight: 800;
            color: #2a3b90;
        }

        /* -------- TABLEAUX -------- */
        table.custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 8px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            font-size: 11px;            /* Réduit pour plus de lignes */
        }

        table.custom-table th {
            background: #2a3b90;
            color: #ffffff;
            font-weight: 600;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.custom-table td {
            padding: 5px 8px;           /* Padding réduit */
            border-top: 1px solid #e2e8f0;
            color: #334155;
        }

        /* CATEGORIES EXCES */
        .exces-mineur { background-color: #f0fdf4; }
        .exces-moyen  { background-color: #fefce8; }
        .exces-grave  { background-color: #fff7ed; }
        .exces-majeur { background-color: #fef2f2; }

        /* TABLEAU RECAP EXCES */
        .recap-exces-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 10px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
        }

        .recap-exces-table th {
            background: #0284c7;
            color: #ffffff;
            padding: 5px 8px;
            font-size: 10px;
            text-transform: uppercase;
            text-align: center;
        }

        .recap-exces-table td {
            padding: 6px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }

        .recap-exces-table td:last-child {
            border-right: none;
        }

        /* -------- SIGNATURE TABLE -------- */
        .signature-section {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .signature-header {
            background: #2a3b90;
            color: #ffffff;
            font-weight: 600;
            font-size: 11px;
            padding: 5px 12px;
            display: flex;
            justify-content: space-between;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid #cbd5e1;
        }

        .signature-box {
            padding: 6px 12px;
            height: 70px;
            border-right: 1px solid #e2e8f0;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .signature-box:last-child {
            border-right: none;
        }

        .signature-role {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
        }

        .signature-date {
            font-size: 9px;
            color: #64748b;
        }

        .signature-img {
            max-height: 45px;
            object-fit: contain;
            align-self: flex-end;
        }

        /* -------- FOOTER -------- */
        .pied-print {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            text-align: center;
            color: #64748b;
        }

        /* Petits titres de section */
        .section-titre-exces {
            font-size: 13px;
            margin: 8px 0 5px 0;
            color: #2a3b90;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>

<div class="print-container">

    <!-- ============================================================ -->
    <!-- ===================== PAGE 1 ================================ -->
    <!-- ============================================================ -->
    <div class="page-section">

        <div class="header-section">
            <a href="#"><img src="{{ asset('images/logosetram.png') }}" alt="SETRAM Logo"></a>
            <!-- <img src="{{ asset('cerclesetram.png') }}" alt="SETRAM Emblem"> -->
        </div>

        <div class="main-title-container">
            <div class="section-title">Rapport de contrôle des paramètres d’exploitation</div>
            <div class="doc-code">Code : DG-DEX-FOR-0034-B</div>
        </div>

        <div class="reference-bar">
            <div>
                <span class="reference-title">Référence du rapport : </span>
                <span class="reference-code">{{ $ville }}-DEX-RA-{{ sprintf('%04d', $course->code ?? 0) }}-{{ $lannee ?? date('Y') }}</span>
            </div>
            <div style="font-size: 10px; color: #64748b;">
                <i class="fa-regular fa-calendar-check"></i> Document officiel SETRAM
            </div>
        </div>

        <div class="villes-grid">
            @php
                $villesList = [
                    'ALG' => 'Alger',
                    'ORN' => 'Oran',
                    'CST' => 'Constantine',
                    'SBA' => 'Sidi Bel Abbès',
                    'ORG' => 'Ouargla',
                    'STF' => 'Sétif',
                    'MST' => 'Mostaganem'
                ];
            @endphp
            @foreach($villesList as $c => $n)
                <div class="ville-badge {{ $ville == $c ? 'active' : '' }}">
                    @if($ville == $c) <i class="fa-solid fa-circle-check"></i> @endif {{ $n }}
                </div>
            @endforeach
        </div>

        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Date</span>
                    <span class="info-value">{{ $course->ladate ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Heure Début</span>
                    <span class="info-value">{{ $course->heure ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Lieu Début</span>
                    <span class="info-value">{{ $course->lieudebut ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Lieu Fin</span>
                    <span class="info-value">{{ $course->lieufin ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Voie</span>
                    <span class="info-value">{{ $course->voie ?? '--' }}</span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Conducteur</span>
                    <span class="info-value">{{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Matricule</span>
                    <span class="info-value">{{ $course->conducteur->matricule ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Service Agent (SA)</span>
                    <span class="info-value">{{ $course->conducteur->SA ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tramway N°</span>
                    <span class="info-value">{{ $course->conducteur->RAME ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Service Véhicule (SV)</span>
                    <span class="info-value">{{ $course->conducteur->SV ?? '--' }}</span>
                </div>
            </div>
        </div>

        <div class="canvas-card no-break">
            <canvas id="minigraphe" width="1120" height="260"></canvas>
        </div>

        <div class="stats-grid no-break">
            <div class="stat-card">
                <span class="stat-title">Utilisation Gong</span>
                <span class="stat-number">{{ $nbGong ?? 0 }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Utilisation Klaxon</span>
                <span class="stat-number">{{ $nbKlaxon ?? 0 }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">F.U. Manipulateur</span>
                <span class="stat-number">{{ $nbFU ?? 0 }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Patins Magnétiques</span>
                <span class="stat-number">{{ $nbPatins ?? 0 }}</span>
            </div>
        </div>

    </div> <!-- fin page 1 -->

    <!-- ============================================================ -->
    <!-- ===================== PAGE 2 ================================ -->
    <!-- ============================================================ -->
    <div class="saut-page"></div>
    <div class="page-section">

        <div class="header-section">
            <a href="#"><img src="{{ asset('images/logosetram.png') }}" alt="SETRAM Logo"></a>
            <!-- <img src="{{ asset('cerclesetram.png') }}" alt="SETRAM Emblem"> -->
        </div>

        <div class="main-title-container">
            <div class="section-title">Rapport de contrôle des paramètres d’exploitation</div>
            <div class="doc-code">Code : DG-DEX-FOR-0034-B</div>
        </div>

        <h3 class="section-titre-exces">
            <i class="fa-solid fa-triangle-exclamation"></i> Excès de vitesse constatés
        </h3>

        <!-- TABLEAU DES EXCES (page 2) -->
        <table class="custom-table" id="exces">
            <thead>
                <tr>
                    <th style="width: 15%;">Vitesse aut. (km/h)</th>
                    <th style="width: 15%;">Vitesse att. (km/h)</th>
                    <th style="width: 12%;">Distance (m)</th>
                    <th style="width: 23%;">Interstation</th>
                    <th style="width: 20%;">Détails</th>
                    <th style="width: 15%;">Catégorie</th>
                </tr>
            </thead>
            <tbody>
                @php $jk = 0; @endphp
                @foreach($exait as $item)
                    @if(($item['aire'] ?? 0) > 10)
                        <tr class="exces-{{ $item['categorie'] ?? 'mineur' }}">
                            <td style="font-weight: 700;">{{ intval($item['limite'] ?? 0) }}</td>
                            <td style="font-weight: 700; color: #dc2626;">{{ intval($item['max'] ?? 0) }}</td>
                            <td>{{ ($item['fin'] ?? 0) - ($item['debut'] ?? 0) }}</td>
                            <td>{{ $item['interstation'] ?? '--' }}</td>
                            <td>{{ $item['detail'] ?? '' }}</td>
                            <td>
                                <span style="font-weight: 700; font-size: 10px; text-transform: uppercase;">
                                    @switch($item['categorie'] ?? 'mineur')
                                        @case('moyen') <i class="fas fa-exclamation-circle severity-icon me-1"></i> @break
                                        @case('grave') <i class="fas fa-exclamation-triangle severity-icon me-1"></i> @break
                                        @case('majeur') <i class="fas fa-skull-crossbones severity-icon me-1"></i> @break
                                        @default <i class="fas fa-info-circle severity-icon me-1"></i>
                                    @endswitch
                                    {{ ucfirst($item['categorie'] ?? 'mineur') }}
                                </span>
                            </td>
                        </tr>
                        @php $jk++; @endphp
                    @endif
                @endforeach

                @if($jk == 0)
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 15px; color: #16a34a; font-weight: 600;">
                            <i class="fa-solid fa-circle-check"></i> Aucun excès de vitesse observé durant ce parcours
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- ========================================================== -->
        <!-- SI PEU D'EXCES (≤ 10) : récap + signatures sur la PAGE 2 -->
        <!-- ========================================================== -->
        @if($jk <= 10)
            <table class="recap-exces-table no-break" id="nbexces">
                <thead>
                    <tr>
                        <th>Excès mineurs</th>
                        <th>Excès moyens</th>
                        <th>Excès graves</th>
                        <th>Excès majeurs</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="background: #f0fdf4; color: #166534;">{{ $nbmineur ?? 0 }}</td>
                        <td style="background: #fefce8; color: #854d0e;">{{ $nbmoyen ?? 0 }}</td>
                        <td style="background: #fff7ed; color: #9a3412;">{{ $nbgrave ?? 0 }}</td>
                        <td style="background: #fef2f2; color: #991b1b;">{{ $nbmajeur ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="signature-section no-break">
                <div class="signature-header">
                    <span>Validation et Signatures</span>
                    <span>Date d'évaluation : {{ date('d/m/Y') }}</span>
                </div>
                <div class="signature-grid">
                    <div class="signature-box">
                        <span class="signature-role">Agent de maîtrise (Contrôleur)</span>
                        @if(isset($signatureImage))
                            <img src="{{ asset($signatureImage) }}" class="signature-img" alt="Signature Agent">
                        @endif
                    </div>
                    <div class="signature-box">
                        <span class="signature-role">Conducteur :</span>
                        <span style="font-size: 11px; font-weight: 600; color: #1e293b;">
                            {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}
                        </span>
                    </div>
                    <div class="signature-box">
                        <span class="signature-role">Agent de maîtrise référent</span>
                    </div>
                </div>
            </div>

            <div class="pied-print">
                Ce document est la propriété exclusive de SETRAM spa. Il ne peut être utilisé, reproduit ou communiqué sans autorisation préalable.
            </div>
        @endif

    </div> <!-- fin page 2 -->

    <!-- ============================================================ -->
    <!-- ===================== PAGE 3 (conditionnelle) ============== -->
    <!-- ============================================================ -->
    @if($jk > 10)
        <div class="saut-page"></div>
        <div class="page-section">

            <div class="header-section">
                <a href="#"><img src="{{ asset('images/logosetram.png') }}" alt="SETRAM Logo"></a>
                <!-- <img src="{{ asset('cerclesetram.png') }}" alt="SETRAM Emblem"> -->
            </div>

            <div class="main-title-container">
                <div class="section-title">Rapport de contrôle – Suite et validation</div>
                <div class="doc-code">Code : DG-DEX-FOR-0034-B</div>
            </div>

            <!-- RÉCAPITULATIF DES EXCES -->
            <table class="recap-exces-table no-break" id="nbexces">
                <thead>
                    <tr>
                        <th>Excès mineurs</th>
                        <th>Excès moyens</th>
                        <th>Excès graves</th>
                        <th>Excès majeurs</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="background: #f0fdf4; color: #166534;">{{ $nbmineur ?? 0 }}</td>
                        <td style="background: #fefce8; color: #854d0e;">{{ $nbmoyen ?? 0 }}</td>
                        <td style="background: #fff7ed; color: #9a3412;">{{ $nbgrave ?? 0 }}</td>
                        <td style="background: #fef2f2; color: #991b1b;">{{ $nbmajeur ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- SIGNATURES -->
            <div class="signature-section no-break">
                <div class="signature-header">
                    <span>Validation et Signatures</span>
                    <span>Date d'évaluation : {{ date('d/m/Y') }}</span>
                </div>
                <div class="signature-grid">
                    <div class="signature-box">
                        <span class="signature-role">Agent de maîtrise (Contrôleur)</span>
                        @if(isset($signatureImage))
                            <img src="{{ asset($signatureImage) }}" class="signature-img" alt="Signature Agent">
                        @endif
                    </div>
                    <div class="signature-box">
                        <span class="signature-role">Conducteur :</span>
                        <span style="font-size: 11px; font-weight: 600; color: #1e293b;">
                            {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}
                        </span>
                    </div>
                    <div class="signature-box">
                        <span class="signature-role">Agent de maîtrise référent</span>
                    </div>
                </div>
            </div>

            <div class="pied-print">
                Ce document est la propriété exclusive de SETRAM spa. Il ne peut être utilisé, reproduit ou communiqué sans autorisation préalable.
            </div>

        </div> <!-- fin page 3 -->
    @endif

</div> <!-- fin print-container -->

<!-- ============ SCRIPTS ============ -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/mongraph3.js') }}"></script>
<script>
    $(function() {
        var data = { values: [
            @foreach($pointcourses as $i => $point)
                {
                    X: {{ $i }},
                    Y: {{ intval($point['vitesse']) }},
                    color: '{{ $point['couleur'] }}',
                    nom: '{{ $point['text'] }}',
                    gong: '{{ $point['gong'] }}',
                    traction: '{{ ($point['freinage']==1?1:($point['traction']==1?2:0)) }}',
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

        var canvas = document.getElementById('minigraphe');
        if (canvas) {
            if (typeof leminigraphe === 'function') {
                leminigraphe(data, env, 0, data.values.length);
            } else {
                setTimeout(function() {
                    if (typeof leminigraphe === 'function') {
                        leminigraphe(data, env, 0, data.values.length);
                    }
                }, 300);
            }
        }
    });
</script>

</body>
</html>
