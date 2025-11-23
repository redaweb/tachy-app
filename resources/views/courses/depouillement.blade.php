<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de contrôle</title>
    
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Réinitialisation de base */
        body { 
            margin: 0; 
            padding: 0; 
            font-family: parisine-office-std, sans-serif;
            background: white;
            -webkit-print-color-adjust: exact; /* Force l'impression des couleurs de fond */
        }

        /* Conteneur principal */
        .print-container {
            width: 100%; /* S'adapte à la largeur définie dans Browsershot */
            max-width: 1220px;
            margin: 0 auto;
        }

        /* --- GESTION DES SAUTS DE PAGE --- */
        .saut-page {
            page-break-after: always;
            break-after: page;
            height: 0;
            display: block;
            visibility: hidden;
        }

        /* Protection contre la coupure des éléments importants */
        .no-break-inside {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Vos styles existants (nettoyés) */
        .header-section { height: 60px; width: 100%; margin-bottom: 10px; }
        .section-title { color: #1fb2ac; float: left; font-weight: bold; font-size: 14px; }
        
        .pied-print {
            margin-top: 15px;
            font-size: 10px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            /* Le pied de page reste collé en bas si nécessaire, 
               mais ici il suit le flux normal */
        }

        /* Tableaux */
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; font-size: 11px; }
        th { background-color: #1fb2ac; color: white; font-weight: bold; }
        
        #nbexces th { background-color: #004081; font-size: 10px; }

        /* Utilitaires */
        .stats-container { background: #f8f9fa; border: 1px solid #dee2e6; padding: 8px 10px; margin: 8px 0; font-size: 12px; }
        .radio-container { background: #f8f9fa; padding: 6px 8px; margin-bottom: 8px; font-size: 11px; }
        .info-container { background: white; padding: 8px 10px; border: 1px solid #dee2e6; margin-bottom: 8px; font-size: 12px; }
        .main-title-container { width: 100%; padding: 8px; border: 1px solid #1fb2ac; background: #f8f9fa; margin-bottom: 8px; box-sizing: border-box; }
        .reference-container { font-size: 12px; margin: 8px 2px; padding: 6px 8px; background: #e3f2fd; }

        /* Couleurs Excès */
        .exces-majeur { background-color: #ffebee !important; }
        .exces-grave { background-color: #fff3e0 !important; }
        .exces-moyen { background-color: #fff8e1 !important; }
        .exces-mineur { background-color: #f1f8e9 !important; }
    </style>
</head>
<body>

<div class="print-container">
    
    <div class="page-section">
        <div class="header-section">
            <img src="{{ asset('logosetram.png') }}" style="height: 50px; float: left;">
            <img src="{{ asset('cerclesetram.png') }}" style="height: 50px; float: right;">
        </div>

        <div class="main-title-container">
            <div class="section-title">Rapport de contrôle des paramètres d'exploitation</div>
            <div style="float: right; font-weight: bold; font-size: 12px;">Code: DG-PEX-FOR-0034-1</div>
            <div style="clear: both;"></div> </div>

        @php 
            $ville = session('site', 'ALG');
            $referenceCode = $ville . '-DE-CPE-' . sprintf('%04d', $course->code) . '-' . $lannee;
        @endphp
        
        <div class="reference-container">
            <strong>Référence :</strong> <span style="color:#1fb2ac; font-weight: bold;">{{ $referenceCode }}</span>
        </div>

        <div class="radio-container">
            <table style="border: none;">
                <tr>
                    @foreach(['ALG'=>'Alger', 'ORN'=>'Oran', 'CST'=>'Constantine', 'SBA'=>'Sidi Bel Abbès', 'ORG'=>'Ouargla', 'STF'=>'Sétif'] as $code => $nom)
                    <td style="border: none;">
                        <input type="radio" {{ $ville == $code ? 'checked' : 'disabled' }}> {{ $nom }}
                    </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <div class="info-container">
            <table style="border: none;">
                <tr>
                    <td style="border: none;"><strong>Date:</strong> {{ $course->ladate }}</td>
                    <td style="border: none;"><strong>Heure:</strong> {{ $course->heure }}</td>
                    <td style="border: none;"><strong>Début:</strong> {{ $course->lieudebut }}</td>
                    <td style="border: none;"><strong>Fin:</strong> {{ $course->lieufin }}</td>
                    <td style="border: none;"><strong>Voie:</strong> {{ $course->voie }}</td>
                </tr>
            </table>
        </div>

        <div class="info-container">
            <table style="border: none;">
                <tr>
                    <td style="border: none;"><strong>Cond:</strong> {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}</td>
                    <td style="border: none;"><strong>Mat:</strong> {{ $course->conducteur->matricule ?? '' }}</td>
                    <td style="border: none;"><strong>SA:</strong> {{ $course->conducteur->SA ?? '' }}</td>
                    <td style="border: none;"><strong>Rame:</strong> {{ $course->conducteur->RAME ?? '' }}</td>
                    <td style="border: none;"><strong>SV:</strong> {{ $course->conducteur->SV ?? '' }}</td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin: 8px 0;" class="no-break-inside">
            <canvas id="minigraphe" width="1198" height="350" style="border:1px solid #aaa; max-width: 100%;"></canvas>
        </div>

        <div class="stats-container no-break-inside">
            <div style="font-size: 14px; font-weight: bold; margin-bottom: 6px; color: #004081;">Statistiques d'utilisation</div>
            <strong>Gong:</strong> {{ $nbGong }} | 
            <strong>Klaxon:</strong> {{ $nbKlaxon }} | 
            <strong>F.U.:</strong> {{ $nbFU }}
        </div>
    </div>

    <div class="saut-page"></div>

    <div class="page-section">
        <div class="header-section">
            <img src="{{ asset('logosetram.png') }}" style="height: 50px; float: left;">
            <img src="{{ asset('cerclesetram.png') }}" style="height: 50px; float: right;">
        </div>
        
        <div class="main-title-container">
            <div class="section-title">Rapport de contrôle des paramètres d'exploitation</div>
            <div style="float: right; font-weight: bold; font-size: 12px;">Code: DG-PEX-FOR-0034-1</div>
            <div style="clear: both;"></div>
        </div>

        <div style="margin-top: 10px;">
            <div style="font-size: 16px; font-weight: bold; color: #004081; margin-bottom: 8px; text-align: center;">
                Excès de vitesse constatés
            </div>
            
            <table id="exces">
                <thead>
                    <tr style="background-color:#1fb2ac;color: white">
                        <th>Vitesse auto.</th><th>Vitesse att.</th><th>Dist.</th><th>Interstation</th><th>Détails</th><th>Catégorie</th>
                    </tr>
                </thead>
                <tbody>
                    @php $jk = 0; $excesParPage = 25; @endphp
                    @foreach($exait as $item)
                        @if(($item['aire'] ?? 0) > 10)
                            <tr class="exces-{{ $item['categorie'] ?? 'mineur' }}">
                                <td>{{ intval($item['limite'] ?? 0) }}</td>
                                <td>{{ intval($item['max'] ?? 0) }}</td>
                                <td>{{ ($item['fin'] ?? 0) - ($item['debut'] ?? 0) }}</td>
                                <td>{{ $item['interstation'] ?? '--' }}</td>
                                <td>{{ $item['detail'] ?? '' }}</td>
                                <td>{{ ucfirst($item['categorie'] ?? 'mineur') }}</td>
                            </tr>
                            @php $jk++; @endphp

                            {{-- Gestion du saut de page tableau --}}
                            @if($jk % $excesParPage == 0 && !$loop->last)
                                </tbody></table>
                                <div class="saut-page"></div>
                                <table id="exces"><thead><tr style="background-color:#1fb2ac;color: white"><th>Vitesse auto.</th><th>Vitesse att.</th><th>Dist.</th><th>Interstation</th><th>Détails</th><th>Catégorie</th></tr></thead><tbody>
                            @endif
                        @endif
                    @endforeach
                    
                    @if($jk == 0)
                        <tr><td colspan="6" style="text-align:center">Aucun excès de vitesse observé.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="no-break-inside">
            <table id="nbexces">
                <tr style="background-color:#004081;color: white;">
                    <th>Excès mineurs</th><th>Excès moyens</th><th>Excès graves</th><th>Excès majeurs</th>
                </tr>
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $nbmineur }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $nbmoyen }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $nbgrave }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $nbmajeur }}</td>
                </tr>
            </table>
        </div>

        <div class="signature-table no-break-inside">
            <table>
                <tr style="background-color:#1fb2ac;color: white;">
                    <th style="width: 75%;">Commentaires</th><th>Signature</th>
                </tr>
                <tr style="height: 60px">
                    <td style="vertical-align: top;">Agent de maitrise</td><td></td>
                </tr>
                <tr style="height: 60px">
                    <td style="vertical-align: top;">Conducteur: {{ $course->conducteur->nom ?? '' }}</td><td></td>
                </tr>
                <tr style="height: 60px">
                    <td style="vertical-align: top;">Référent</td><td></td>
                </tr>
            </table>
        </div>

        <div class="pied-print">
            Ce document est la propriété de SETRAM spa.
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/mongraph3.js') }}"></script> 

<script>
    // Note: Plus besoin de generatePDF() ni de html2pdf
    
    $(document).ready(function() {
        // Préparation des données pour leminigraphe
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
        if (canvas && typeof leminigraphe !== 'undefined') {
            // Dessine le graphique
            leminigraphe(data, env, 0, data.values.length);
            
            // ASTUCE IMPORTANTE : 
            // Si leminigraphe fait une animation, Browsershot risque de prendre la photo trop tôt.
            // Si possible, modifiez "js/mongraph3.js" pour accepter une option "animation: false".
        }
    });
</script>
</body>
</html>