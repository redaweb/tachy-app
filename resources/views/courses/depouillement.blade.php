<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de contrôle</title>

    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: parisine-office-std, sans-serif;
            background: #fff;
            color: #222;
            -webkit-print-color-adjust: exact;
        }

        .print-container {
            max-width: 1220px;
            margin: auto;
            padding: 10px 14px;
            box-sizing: border-box;
        }

        /* -------- PAGE BREAK -------- */
        .saut-page {
            page-break-after: always;
            break-after: page;
            height: 0;
            visibility: hidden;
        }

        .no-break-inside {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* -------- HEADER -------- */
        .header-section {
            height: 60px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .header-section img {
            height: 50px;
        }

        /* -------- TITRE -------- */
        .main-title-container {
            display: table;
            width: 100%;
            background: #f4fbfa;
            border: 1px solid #1fb2ac;
            padding: 8px 12px;
            margin-bottom: 8px;
        }

        .section-title {
            display: table-cell;
            font-size: 15px;
            font-weight: bold;
            color: #1fb2ac;
            vertical-align: middle;
        }

        .doc-code {
            display: table-cell;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            color: #004081;
            vertical-align: middle;
        }

        /* -------- BLOCS -------- */
        .info-container,
        .radio-container,
        .stats-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .reference-container {
            background: #e3f2fd;
            border-left: 4px solid #1fb2ac;
            padding: 6px 10px;
            font-size: 12px;
            margin-bottom: 8px;
        }

        /* -------- TABLEAUX -------- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th, td {
            border: 1px solid #999;
            padding: 5px 6px;
            font-size: 11px;
        }

        th {
            background: #1fb2ac;
            color: #fff;
            text-align: center;
            font-weight: bold;
        }

        #nbexces th {
            background: #004081;
        }

        /* -------- EXCES -------- */
        .exces-mineur { background: #f1f8e9; }
        .exces-moyen  { background: #fff8e1; }
        .exces-grave  { background: #fff3e0; }
        .exces-majeur { background: #ffebee; }

        /* -------- CANVAS -------- */
        .canvas-container {
            border: 1px solid #ccc;
            background: #fafafa;
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 4px;
            text-align: center;
        }

        /* -------- SIGNATURE -------- */
        .signature-table td {
            height: 55px;
            vertical-align: top;
        }

        /* -------- FOOTER -------- */
        .pied-print {
            margin-top: 12px;
            font-size: 10px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>

<body>

<div class="print-container">

    <!-- ================= PAGE 1 ================= -->
    <div class="page-section">

        <div class="header-section">
            <img src="{{ asset('logosetram.png') }}" style="float:left">
            <img src="{{ asset('cerclesetram.png') }}" style="float:right">
        </div>

        <div class="main-title-container">
            <div class="section-title">Rapport de contrôle des paramètres d'exploitation</div>
            <div class="doc-code">Code : DG-PEX-FOR-0034-1</div>
        </div>

        @php
            $ville = session('site', 'ALG');
            $referenceCode = $ville . '-DE-CPE-' . sprintf('%04d', $course->code) . '-' . $lannee;
        @endphp

        <div class="reference-container">
            <strong>Référence :</strong>
            <span style="color:#1fb2ac;font-weight:bold">{{ $referenceCode }}</span>
        </div>

        <div class="radio-container">
            <table style="border:none">
                <tr>
                    @foreach(['ALG'=>'Alger','ORN'=>'Oran','CST'=>'Constantine','SBA'=>'Sidi Bel Abbès','ORG'=>'Ouargla','STF'=>'Sétif'] as $c=>$n)
                        <td style="border:none">
                            <input type="radio" {{ $ville==$c?'checked':'disabled' }}> {{ $n }}
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <div class="info-container">
            <table style="border:none">
                <tr>
                    <td style="border:none"><strong>Date :</strong> {{ $course->ladate }}</td>
                    <td style="border:none"><strong>Heure :</strong> {{ $course->heure }}</td>
                    <td style="border:none"><strong>Début :</strong> {{ $course->lieudebut }}</td>
                    <td style="border:none"><strong>Fin :</strong> {{ $course->lieufin }}</td>
                    <td style="border:none"><strong>Voie :</strong> {{ $course->voie }}</td>
                </tr>
            </table>
        </div>

        <div class="info-container">
            <table style="border:none">
                <tr>
                    <td style="border:none"><strong>Cond :</strong> {{ $course->conducteur->nom ?? '' }} {{ $course->conducteur->prenom ?? '' }}</td>
                    <td style="border:none"><strong>Mat :</strong> {{ $course->conducteur->matricule ?? '' }}</td>
                    <td style="border:none"><strong>SA :</strong> {{ $course->conducteur->SA ?? '' }}</td>
                    <td style="border:none"><strong>Rame :</strong> {{ $course->conducteur->RAME ?? '' }}</td>
                    <td style="border:none"><strong>SV :</strong> {{ $course->conducteur->SV ?? '' }}</td>
                </tr>
            </table>
        </div>

        <div class="canvas-container no-break-inside">
            <canvas id="minigraphe" width="1198" height="350" style="max-width:100%"></canvas>
        </div>

        <div class="stats-container no-break-inside">
            <strong>Gong :</strong> {{ $nbGong }} |
            <strong>Klaxon :</strong> {{ $nbKlaxon }} |
            <strong>F.U. :</strong> {{ $nbFU }}
        </div>

    </div>

    <div class="saut-page"></div>

    <!-- ================= PAGE 2 ================= -->
    <div class="page-section">

        <div class="header-section">
            <img src="{{ asset('logosetram.png') }}" style="float:left">
            <img src="{{ asset('cerclesetram.png') }}" style="float:right">
        </div>

        <div class="main-title-container">
            <div class="section-title">Rapport de contrôle des paramètres d'exploitation</div>
            <div class="doc-code">Code : DG-PEX-FOR-0034-1</div>
        </div>

        <h3 style="text-align:center;color:#004081;margin:10px 0">
            Excès de vitesse constatés
        </h3>

        <table id="exces">
            <thead>
                <tr>
                    <th>Vitesse auto.</th>
                    <th>Vitesse att.</th>
                    <th>Dist.</th>
                    <th>Interstation</th>
                    <th>Détails</th>
                    <th>Catégorie</th>
                </tr>
            </thead>
            <tbody>
                @php $jk=0; $max=25; @endphp
                @foreach($exait as $item)
                    @if(($item['aire']??0)>10)
                        <tr class="exces-{{ $item['categorie']??'mineur' }}">
                            <td>{{ intval($item['limite']??0) }}</td>
                            <td>{{ intval($item['max']??0) }}</td>
                            <td>{{ ($item['fin']??0)-($item['debut']??0) }}</td>
                            <td>{{ $item['interstation']??'--' }}</td>
                            <td>{{ $item['detail']??'' }}</td>
                            <td>{{ ucfirst($item['categorie']??'mineur') }}</td>
                        </tr>
                        @php $jk++; @endphp
                    @endif
                @endforeach
                @if($jk==0)
                    <tr><td colspan="6" style="text-align:center">Aucun excès observé</td></tr>
                @endif
            </tbody>
        </table>

        <table id="nbexces" class="no-break-inside">
            <tr>
                <th>Mineurs</th><th>Moyens</th><th>Graves</th><th>Majeurs</th>
            </tr>
            <tr>
                <td style="text-align:center;font-weight:bold">{{ $nbmineur }}</td>
                <td style="text-align:center;font-weight:bold">{{ $nbmoyen }}</td>
                <td style="text-align:center;font-weight:bold">{{ $nbgrave }}</td>
                <td style="text-align:center;font-weight:bold">{{ $nbmajeur }}</td>
            </tr>
        </table>

        <table class="signature-table no-break-inside">
            <tr>
                <th>Commentaires</th><th>Signature</th>
            </tr>
            <tr><td>Agent de maitrise</td><td></td></tr>
            <tr><td>Conducteur</td><td></td></tr>
            <tr><td>Référent</td><td></td></tr>
        </table>

        <div class="pied-print">
            Ce document est la propriété de SETRAM spa.
        </div>

    </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/mongraph3.js') }}"></script>
<script>
    $(function() {
        // Préparation des données pour leminigraphe (utilise la fonction définie dans mongraph3.js)
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
            // Appel de la fonction fournie par mongraph3.js
            if (typeof leminigraphe === 'function') {
                leminigraphe(data, env, 0, data.values.length);
            } else {
                // Au cas où le script n'est pas encore chargé, tenter après un court délai
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
