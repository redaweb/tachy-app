<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de contrôle - {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}</title>
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            .saut-page { page-break-after: always; }
            @page { 
                size: landscape; 
                margin: 5mm;
            }
        }
        
        @media screen {
            body { 
                background: #f5f5f5; 
                padding: 10px;
                margin: 0;
            }
            .print-container { 
                background: white; 
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 4px;
                padding: 10px;
            }
        }

        .print-container {
            width: 1220px;
            margin-left: auto;
            margin-right: auto;
            font-family: parisine-office-std, sans-serif;
            padding: 5px;
        }
        .header-section {
            height: 60px;
            width: 100%;
            margin-bottom: 10px;
        }
        .section-title {
            color: #1fb2ac;
            float: left;
            font-weight: bold;
            font-size: 14px;
        }
        .page-break {
            page-break-after: always;
        }
        .saut-page {
            page-break-after: always;
        }
        .signature-cell {
            height: 60px;
            vertical-align: top;
        }
        .pied-print {
            margin-top: 15px;
            font-size: 10px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        /* Styles des tableaux - réduits */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #999;
            padding: 4px 6px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #1fb2ac;
            color: white;
            font-weight: bold;
            padding: 6px 8px;
        }
        #nbexces th {
            background-color: #004081;
            font-size: 10px;
            padding: 4px 6px;
        }
        
        /* Styles pour les statistiques */
        .stats-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 8px 10px;
            margin: 8px 0;
            font-size: 12px;
        }
        
        /* Bouton PDF */
        .pdf-button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 10px;
            transition: background 0.3s;
        }
        .pdf-button:hover {
            background: #c82333;
        }
        
        /* Amélioration du canvas */
        #minigraphe {
            border: 1px solid #dee2e6;
            border-radius: 3px;
            background: white;
            margin: 5px 0;
        }
        
        /* Style pour les excès */
        .exces-majeur { background-color: #ffebee; }
        .exces-grave { background-color: #fff3e0; }
        .exces-moyen { background-color: #fff8e1; }
        .exces-mineur { background-color: #f1f8e9; }

        /* Réduction des conteneurs principaux */
        .page-section {
            padding: 0;
            margin: 0;
        }

        /* Titre principal réduit */
        .main-title-container {
            width: 1200px;
            padding: 8px;
            border: 1px solid #1fb2ac;
            height: 20px;
            font-size: 14px;
            vertical-align: baseline;
            background: #f8f9fa;
            margin-bottom: 8px;
        }

        /* Référence réduite */
        .reference-container {
            font-size: 12px;
            margin: 8px 2px;
            padding: 6px 8px;
            background: #e3f2fd;
            border-radius: 3px;
        }

        /* Conteneurs d'information réduits */
        .info-container {
            background: white;
            padding: 8px 10px;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        /* Radio buttons plus compacts */
        .radio-container {
            background: #f8f9fa;
            padding: 6px 8px;
            border-radius: 3px;
            margin-bottom: 8px;
            font-size: 11px;
        }

        /* Signatures plus compactes */
        .signature-table {
            margin-top: 15px;
        }
        .signature-table th,
        .signature-table td {
            padding: 3px 5px;
            font-size: 11px;
        }
    </style>
</head>
<body>
<div class="print-container">
    <div id="main-content">
        
        <!-- PAGE 1 -->
        <div class="page-section">
            <!-- En-tête -->
            <div class="header-section">
                <img src="{{ asset('logosetram.png') }}" style="border-color: white;float: left; height: 50px;">
                <img src="{{ asset('cerclesetram.png') }}" style="border-color: white;float: right; height: 50px;">
            </div>
            
            <!-- Titre du rapport -->
            <div class="main-title-container">
                <div class="section-title">Rapport de contrôle des paramètres d'exploitation</div>
                <div style="float: right; font-weight: bold; font-size: 12px;">Code: DG-PEX-FOR-0034-1</div>
            </div>

            @php 
                $ville = session('site', 'ALG');
                $referenceCode = $ville . '-DE-CPE-' . sprintf('%04d', $course->code) . '-' . $lannee;
            @endphp
            
            <!-- Référence -->
            <div class="reference-container">
                <strong>Référence :</strong> <span style="color:#1fb2ac; font-weight: bold;">{{ $referenceCode }}</span>
            </div>

            <!-- Sélection de la ville -->
            <div class="radio-container">
                <table style="font-size: 11px; border: none;">
                    <tr>
                        <td style="border: none; padding: 2px 8px;"><input type="radio" class="radio" {{ $ville == "ALG" ? 'checked="checked"' : 'disabled' }} name="ville"> Alger</td>
                        <td style="border: none; padding: 2px 8px;"><input type="radio" class="radio" {{ $ville == "ORN" ? 'checked="checked"' : 'disabled' }} name="ville"> Oran</td>
                        <td style="border: none; padding: 2px 8px;"><input type="radio" class="radio" {{ $ville == "CST" ? 'checked="checked"' : 'disabled' }} name="ville"> Constantine</td>
                        <td style="border: none; padding: 2px 8px;"><input type="radio" class="radio" {{ $ville == "SBA" ? 'checked="checked"' : 'disabled' }} name="ville"> Sidi Bel Abbès</td>
                        <td style="border: none; padding: 2px 8px;"><input type="radio" class="radio" {{ $ville == "ORG" ? 'checked="checked"' : 'disabled' }} name="ville"> Ouargla</td>
                        <td style="border: none; padding: 2px 8px;"><input type="radio" class="radio" {{ $ville == "STF" ? 'checked="checked"' : 'disabled' }} name="ville"> Sétif</td>      
                    </tr>
                </table>
            </div>

            <!-- Informations de la course -->
            <div class="info-container">
                <table style="font-size: 11px; border: none;">
                    <tr>
                        <td style="border: none; padding: 2px 10px;"><strong>Date:</strong> {{ $course->ladate }}</td>
                        <td style="border: none; padding: 2px 10px;"><strong>Heure de début:</strong> <span id="tdebut">{{ $course->heure }}</span></td>
                        <td style="border: none; padding: 2px 10px;"><strong>Lieu de début:</strong> {{ $course->lieudebut }}</td>
                        <td style="border: none; padding: 2px 10px;"><strong>Lieu de fin:</strong> {{ $course->lieufin }}</td>
                        <td style="border: none; padding: 2px 10px;"><strong>Voie:</strong> {{ $course->voie }}</td>
                    </tr>
                </table>
            </div>

            <!-- Informations du conducteur -->
            <div class="info-container">
                <table style="font-size: 11px; border: none;">
                    <tr>
                        <td style="border: none; padding: 2px 10px;"><strong>Conducteur:</strong> {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}</td>
                        <td style="border: none; padding: 2px 10px;"><strong>Matricule:</strong> {{ $course->conducteur->matricule ?? '' }}</td>
                        <td style="border: none; padding: 2px 10px;"><strong>Service agent:</strong> {{ $course->conducteur->SA ?? '' }}</td>
                        <td style="border: none; padding: 2px 10px;"><strong>Tramway n°:</strong> {{ $course->conducteur->RAME ?? '' }}</td>
                        <td style="border: none; padding: 2px 10px;"><strong>Service véhicule:</strong> {{ $course->conducteur->SV ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Graphique -->
            <div style="text-align: center; margin: 8px 0;">
                <canvas id="minigraphe" width=1198 height=350 style="border:1px solid #aaa; max-width: 100%;"></canvas>
            </div>

            <!-- Statistiques d'utilisation -->
            <div class="stats-container">
                <div style="font-size: 14px; font-weight: bold; margin-bottom: 6px; color: #004081;">
                    Statistiques d'utilisation
                </div>
                <strong>Nombre d'utilisation du gong:</strong> <span style="color:#004081; font-weight: bold;">{{ $nbGong }}</span><br>
                <strong>Nombre d'utilisation du klaxon:</strong> <span style="color:#004081; font-weight: bold;">{{ $nbKlaxon }}</span><br>
                <strong>Nombre d'utilisation du F.U. manipulateur:</strong> <span style="color:#004081; font-weight: bold;">{{ $nbFU }}</span><br>
            </div>
        </div>

        <!-- SAUT DE PAGE -->
        <div class="saut-page"></div>

        <!-- PAGE 2 -->
        <div class="page-section">
            <!-- En-tête page 2 -->
            <div class="header-section">
                <img src="{{ asset('logosetram.png') }}" style="border-color: white;float: left; height: 50px;">
                <img src="{{ asset('cerclesetram.png') }}" style="border-color: white;float: right; height: 50px;">
            </div>
            
            <div class="main-title-container">
                <div style="color: #1fb2ac;float: left; font-weight: bold;">Rapport de contrôle des paramètres d'exploitation</div>
                <div style="float: right; font-weight: bold; font-size: 12px;">Code: DG-PEX-FOR-0034-1</div>
            </div>

            <!-- Tableau des excès -->
            <div style="margin-top: 10px;">
                <div style="font-size: 16px; font-weight: bold; color: #004081; margin-bottom: 8px; text-align: center;">
                    Excès de vitesse constatés
                </div>
                
                <table id="exces" style="font-size: 10px;">
                    <thead>
                        <tr style="background-color:#1fb2ac;color: white">
                            <th style="padding: 4px 6px;">Vitesse autorisée (km/h)</th>
                            <th style="padding: 4px 6px;">Vitesse atteinte (km/h)</th>
                            <th style="padding: 4px 6px;">Distance (m)</th>
                            <th style="padding: 4px 6px;">Interstation</th>
                            <th style="padding: 4px 6px;">Détails</th>
                            <th style="padding: 4px 6px;">Catégorie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $jk = 0;
                            $excesParPage = 25; // Plus d'excès par page avec moins de padding
                        @endphp
                        
                        @foreach($exait as $item)
                            @if(($item['aire'] ?? 0) > 10)
                                @php
                                    $dist = ($item['fin'] ?? 0) - ($item['debut'] ?? 0);
                                    $categorieClass = 'exces-' . ($item['categorie'] ?? 'mineur');
                                @endphp
                                <tr class="{{ $categorieClass }}">
                                    <td style="text-align: center; padding: 3px 5px;">{{ intval($item['limite'] ?? 0) }}</td>
                                    <td style="text-align: center; padding: 3px 5px;">{{ intval($item['max'] ?? 0) }}</td>
                                    <td style="text-align: center; padding: 3px 5px;">{{ $dist }}</td>
                                    <td style="padding: 3px 5px;">{{ $item['interstation'] ?? '--' }}</td>
                                    <td style="padding: 3px 5px;">{{ $item['detail'] ?? '' }}</td>
                                    <td style="text-align: center; font-weight: bold; padding: 3px 5px;">{{ ucfirst($item['categorie'] ?? 'mineur') }}</td>
                                </tr>
                                @php $jk++; @endphp
                                
                                <!-- Saut de page après 25 excès -->
                                @if($jk % $excesParPage == 0 && !$loop->last)
                                    </tbody>
                                </table>
                                <div class="saut-page"></div>
                                <table id="exces" style="font-size: 10px;">
                                    <thead>
                                        <tr style="background-color:#1fb2ac;color: white">
                                            <th style="padding: 4px 6px;">Vitesse autorisée (km/h)</th>
                                            <th style="padding: 4px 6px;">Vitesse atteinte (km/h)</th>
                                            <th style="padding: 4px 6px;">Distance (m)</th>
                                            <th style="padding: 4px 6px;">Interstation</th>
                                            <th style="padding: 4px 6px;">Détails</th>
                                            <th style="padding: 4px 6px;">Catégorie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                @endif
                            @endif
                        @endforeach
                        
                        <!-- Lignes vides si moins d'excès -->
                        @if($jk <= 15)
                            @for($i = $jk; $i < 15; $i++)
                                <tr style='height:20px'>
                                    <td style="padding: 3px 5px;">
                                        @if($i == 0 && $jk == 0)
                                            Aucun excès de vitesse observé.
                                        @endif
                                    </td>
                                    <td style="padding: 3px 5px;"></td>
                                    <td style="padding: 3px 5px;"></td>
                                    <td style="padding: 3px 5px;"></td>
                                    <td style="padding: 3px 5px;"></td>
                                    <td style="padding: 3px 5px;"></td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Tableau du nombre d'excès -->
            <div style="margin: 10px 0;">
                <table id="nbexces" style="font-size: 12px;">
                    <tr style="background-color:#004081;color: white;font-size: 11px;">
                        <th style="text-align: center; padding: 4px 6px;">Excès mineurs</th>
                        <th style="text-align: center; padding: 4px 6px;">Excès moyens</th>
                        <th style="text-align: center; padding: 4px 6px;">Excès graves</th>
                        <th style="text-align: center; padding: 4px 6px;">Excès majeurs</th>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-size: 14px; font-weight: bold; padding: 6px 8px;">{{ $nbmineur }}</td>
                        <td style="text-align: center; font-size: 14px; font-weight: bold; padding: 6px 8px;">{{ $nbmoyen }}</td>
                        <td style="text-align: center; font-size: 14px; font-weight: bold; padding: 6px 8px;">{{ $nbgrave }}</td>
                        <td style="text-align: center; font-size: 14px; font-weight: bold; padding: 6px 8px;">{{ $nbmajeur }}</td>
                    </tr>
                </table>
            </div>

            <!-- Tableau des signatures -->
            <div class="signature-table">
                <table style="font-size: 12px;">
                    <tr style="background-color:#1fb2ac;color: white;">
                        <th style="width: 75%; padding: 6px 8px;">Commentaires</th>
                        <th style="padding: 6px 8px;">Signature</th>
                    </tr>
                    <tr style="height: 60px">
                        <td style="vertical-align: top; padding: 4px 6px;">Agent de maitrise ayant réalisé le contrôle</td>
                        <td style="border-bottom: 1px solid #aaa; padding: 4px 6px;"></td>
                    </tr>
                    <tr style="height: 60px">
                        <td id="signature" style="vertical-align: top; padding: 4px 6px;">Conducteur: {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}</td>
                        <td style="border-bottom: 1px solid #aaa; padding: 4px 6px;"></td>
                    </tr>
                    <tr style="height: 60px">
                        <td style="vertical-align: top; padding: 4px 6px;">Agent de maitrise référent</td>
                        <td style="border-bottom: 1px solid #aaa; padding: 4px 6px;"></td>
                    </tr>
                </table>
            </div>

            <!-- Pied de page -->
            <div class="pied-print">
                Ce document est la propriété de SETRAM spa. Il ne peut être utilisé, reproduit ou communiqué sans son autorisation
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/mongraph3.js') }}"></script>

<script>
    // Fonction pour générer le PDF
    function generatePDF() {
        const element = document.getElementById('main-content');
        const options = {
            margin: [5, 5, 5, 5],
            filename: '{{ $referenceCode }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(options).from(element).save();
    }

    // Initialisation du graphique
    $(document).ready(function() {
        console.log("Début du script de graphique");
        
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

        // Vérifier que le canvas existe
        var canvas = document.getElementById('minigraphe');
        if (canvas && typeof leminigraphe !== 'undefined') {
            console.log("Appel de leminigraphe");
            leminigraphe(data, env, 0, data.values.length);
            generatePDF();
        } else {
            console.error("Canvas ou fonction non trouvée");
        }
    });
</script>
</body>
</html>