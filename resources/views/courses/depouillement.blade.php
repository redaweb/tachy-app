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
            @page { size: landscape; }
        }
        
        @media screen {
            body { background: #f5f5f5; padding: 20px; }
            .print-container { 
                background: white; 
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                border-radius: 8px;
                padding: 20px;
            }
        }

        .print-container {
            width: 1200px;
            margin-left: auto;
            margin-right: auto;
            font-family: parisine-office-std, sans-serif;
        }
        .header-section {
            height: 80px;
            width: 100%;
            margin-bottom: 20px;
        }
        .section-title {
            color: #1fb2ac;
            float: left;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
        .saut-page {
            page-break-after: always;
        }
        .signature-cell {
            height: 70px;
            vertical-align: top;
        }
        .pied-print {
            margin-top: 30px;
            font-size: 11px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        /* Styles des tableaux */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #1fb2ac;
            color: white;
            font-weight: bold;
        }
        #nbexces th {
            background-color: #004081;
            font-size: 11px;
        }
        
        /* Styles pour les statistiques */
        .stats-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        
        /* Bouton PDF */
        .pdf-button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        .pdf-button:hover {
            background: #c82333;
        }
        
        /* Amélioration du canvas */
        #minigraphe {
            border: 2px solid #dee2e6;
            border-radius: 5px;
            background: white;
        }
        
        /* Style pour les excès */
        .exces-majeur { background-color: #ffebee; }
        .exces-grave { background-color: #fff3e0; }
        .exces-moyen { background-color: #fff8e1; }
        .exces-mineur { background-color: #f1f8e9; }
    </style>
</head>
<body>
<div class="print-container">
    
    <!-- Bouton PDF -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button class="pdf-button" onclick="generatePDF()">
            <i class="fas fa-file-pdf"></i> Télécharger en PDF
        </button>
    </div>

    <div id="main-content">
        
        <!-- PAGE 1 -->
        <div class="page-section">
            <!-- En-tête -->
            <div class="header-section">
                <img src="{{ asset('logosetram.png') }}" style="border-color: white;float: left; height: 70px;">
                <img src="{{ asset('cerclesetram.png') }}" style="border-color: white;float: right; height: 70px;">
            </div>
            
            <!-- Titre du rapport -->
            <div style="width: 1190px;padding: 12px;border:2px solid #1fb2ac;height: 25px;font-size: 16px; vertical-align: baseline; background: #f8f9fa;">
                <div class="section-title">Rapport de contrôle des paramètres d'exploitation</div>
                <div style="float: right; font-weight: bold;">Code: DG-PEX-FOR-0034-1</div>
            </div>

            @php 
                $ville = session('site', 'ALG');
                $referenceCode = $ville . '-DE-CPE-' . sprintf('%04d', $course->code) . '-' . $lannee;
            @endphp
            
            <!-- Référence -->
            <div style="font-size: 14px; margin: 15px 3px; padding: 10px; background: #e3f2fd; border-radius: 5px;">
                <strong>Référence :</strong> <span style="color:#1fb2ac; font-weight: bold;">{{ $referenceCode }}</span>
            </div>

            <!-- Sélection de la ville -->
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <table style="font-size: 13px; border: none;">
                    <tr>
                        <td style="border: none;"><input type="radio" class="radio" {{ $ville == "ALG" ? 'checked="checked"' : 'disabled' }} name="ville"> Alger</td>
                        <td style="border: none;"><input type="radio" class="radio" {{ $ville == "ORN" ? 'checked="checked"' : 'disabled' }} name="ville"> Oran</td>
                        <td style="border: none;"><input type="radio" class="radio" {{ $ville == "CST" ? 'checked="checked"' : 'disabled' }} name="ville"> Constantine</td>
                        <td style="border: none;"><input type="radio" class="radio" {{ $ville == "SBA" ? 'checked="checked"' : 'disabled' }} name="ville"> Sidi Bel Abbès</td>
                        <td style="border: none;"><input type="radio" class="radio" {{ $ville == "ORG" ? 'checked="checked"' : 'disabled' }} name="ville"> Ouargla</td>
                        <td style="border: none;"><input type="radio" class="radio" {{ $ville == "STF" ? 'checked="checked"' : 'disabled' }} name="ville"> Sétif</td>      
                    </tr>
                </table>
            </div>

            <!-- Informations de la course -->
            <div style="background: white; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 15px;">
                <table style="font-size: 13px; border: none;">
                    <tr>
                        <td style="border: none;"><strong>Date:</strong> {{ $course->ladate }}</td>
                        <td style="border: none;"><strong>Heure de début:</strong> <span id="tdebut">{{ $course->heure }}</span></td>
                        <td style="border: none;"><strong>Lieu de début:</strong> {{ $course->lieudebut }}</td>
                        <td style="border: none;"><strong>Lieu de fin:</strong> {{ $course->lieufin }}</td>
                        <td style="border: none;"><strong>Voie:</strong> {{ $course->voie }}</td>
                    </tr>
                </table>
            </div>

            <!-- Informations du conducteur -->
            <div style="background: white; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 20px;">
                <table style="font-size: 13px; border: none;">
                    <tr>
                        <td style="border: none;"><strong>Conducteur:</strong> {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}</td>
                        <td style="border: none;"><strong>Matricule:</strong> {{ $course->conducteur->matricule ?? '' }}</td>
                        <td style="border: none;"><strong>Service agent:</strong> {{ $course->conducteur->SA ?? '' }}</td>
                        <td style="border: none;"><strong>Tramway n°:</strong> {{ $course->conducteur->RAME ?? '' }}</td>
                        <td style="border: none;"><strong>Service véhicule:</strong> {{ $course->conducteur->SV ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Graphique -->
            <div style="text-align: center; margin: 20px 0;">
                <canvas id="minigraphe" width=1198 height=400 style="border:1px solid #aaa; max-width: 100%;"></canvas>
            </div>

            <!-- Statistiques d'utilisation -->
            <div class="stats-container">
                <div style="font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #004081;">
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
                <img src="{{ asset('logosetram.png') }}" style="border-color: white;float: left; height: 70px;">
                <img src="{{ asset('cerclesetram.png') }}" style="border-color: white;float: right; height: 70px;">
            </div>
            
            <div style="width: 1190px;padding: 12px;border:2px solid #1fb2ac;height: 25px;font-size: 16px; background: #f8f9fa;">
                <div style="color: #1fb2ac;float: left; font-weight: bold;">Rapport de contrôle des paramètres d'exploitation</div>
                <div style="float: right; font-weight: bold;">Code: DG-PEX-FOR-0034-1</div>
            </div>

            <!-- Tableau des excès -->
            <div style="margin-top: 20px;">
                <div style="font-size: 18px; font-weight: bold; color: #004081; margin-bottom: 15px; text-align: center;">
                    Excès de vitesse constatés
                </div>
                
                <table id="exces" style="font-size: 11px;">
                    <thead>
                        <tr style="background-color:#1fb2ac;color: white">
                            <th>Vitesse autorisée (km/h)</th>
                            <th>Vitesse atteinte (km/h)</th>
                            <th>Distance (m)</th>
                            <th>Interstation</th>
                            <th>Détails</th>
                            <th>Catégorie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $jk = 0;
                            $excesParPage = 20; // Plus d'excès par page en paysage
                        @endphp
                        
                        @foreach($exait as $item)
                            @if(($item['aire'] ?? 0) > 10)
                                @php
                                    $dist = ($item['fin'] ?? 0) - ($item['debut'] ?? 0);
                                    $categorieClass = 'exces-' . ($item['categorie'] ?? 'mineur');
                                @endphp
                                <tr class="{{ $categorieClass }}">
                                    <td style="text-align: center;">{{ intval($item['limite'] ?? 0) }}</td>
                                    <td style="text-align: center;">{{ intval($item['max'] ?? 0) }}</td>
                                    <td style="text-align: center;">{{ $dist }}</td>
                                    <td>{{ $item['interstation'] ?? '--' }}</td>
                                    <td>{{ $item['detail'] ?? '' }}</td>
                                    <td style="text-align: center; font-weight: bold;">{{ ucfirst($item['categorie'] ?? 'mineur') }}</td>
                                </tr>
                                @php $jk++; @endphp
                                
                                <!-- Saut de page après 20 excès (plus en paysage) -->
                                @if($jk % $excesParPage == 0 && !$loop->last)
                                    </tbody>
                                </table>
                                <div class="saut-page"></div>
                                <table id="exces" style="font-size: 11px;">
                                    <thead>
                                        <tr style="background-color:#1fb2ac;color: white">
                                            <th>Vitesse autorisée (km/h)</th>
                                            <th>Vitesse atteinte (km/h)</th>
                                            <th>Distance (m)</th>
                                            <th>Interstation</th>
                                            <th>Détails</th>
                                            <th>Catégorie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                @endif
                            @endif
                        @endforeach
                        
                        <!-- Lignes vides si moins de 12 excès -->
                        @if($jk <= 12)
                            @for($i = $jk; $i < 12; $i++)
                                <tr style='height:23px'>
                                    <td>
                                        @if($i == 0 && $jk == 0)
                                            Aucun excès de vitesse observé.
                                        @endif
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Tableau du nombre d'excès -->
            <div style="margin: 20px 0;">
                <table id="nbexces" style="font-size: 14px;">
                    <tr style="background-color:#004081;color: white;font-size: 12px;">
                        <th style="text-align: center;">Excès mineurs</th>
                        <th style="text-align: center;">Excès moyens</th>
                        <th style="text-align: center;">Excès graves</th>
                        <th style="text-align: center;">Excès majeurs</th>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-size: 16px; font-weight: bold;">{{ $nbmineur }}</td>
                        <td style="text-align: center; font-size: 16px; font-weight: bold;">{{ $nbmoyen }}</td>
                        <td style="text-align: center; font-size: 16px; font-weight: bold;">{{ $nbgrave }}</td>
                        <td style="text-align: center; font-size: 16px; font-weight: bold;">{{ $nbmajeur }}</td>
                    </tr>
                </table>
            </div>

            <!-- Tableau des signatures -->
            <div style="margin-top: 30px;">
                <table style="font-size: 14px;">
                    <tr style="background-color:#1fb2ac;color: white;">
                        <th style="width: 75%;">Commentaires</th>
                        <th>Signature</th>
                    </tr>
                    <tr style="height: 80px">
                        <td style="vertical-align: top;">Agent de maitrise ayant réalisé le contrôle</td>
                        <td style="border-bottom: 1px solid #aaa;"></td>
                    </tr>
                    <tr style="height: 80px">
                        <td id="signature" style="vertical-align: top;">Conducteur: {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}</td>
                        <td style="border-bottom: 1px solid #aaa;"></td>
                    </tr>
                    <tr style="height: 80px">
                        <td style="vertical-align: top;">Agent de maitrise référent</td>
                        <td style="border-bottom: 1px solid #aaa;"></td>
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
            margin: [10, 10, 10, 10],
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
        } else {
            console.error("Canvas ou fonction non trouvée");
        }
    });
</script>
</body>
</html>