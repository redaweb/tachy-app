<?php

if (!function_exists('proche')) {
    function proche($courses, $pk, $ecart)
    {
        $count = count($courses);
        $proch = 0;

        // Position de départ sécurisée
        $k = $pk + $ecart;
        if ($k >= $count) $k = $count - 1;
        if ($k < 0) $k = 0;

        // 🔹 Recherche vers l’arrière
        $i = $k;
        while ($i >= 0) {
            if (isset($courses[$i]) && $courses[$i]['dsstop'] == 1) break;
            $i--;
        }

        // 🔹 Recherche vers l’avant
        $j = $k;
        while ($j < $count) {
            if (isset($courses[$j]) && $courses[$j]['dsstop'] == 1) break;
            $j++;
        }

        // 🔹 Si aucun stop trouvé
        if ($i < 0 && $j >= $count) return 1000;

        // 🔹 Si stop uniquement en avant
        if ($i < 0) return $j - $pk;

        // 🔹 Si stop uniquement en arrière
        if ($j >= $count) return $i - $pk;

        // 🔹 Choix du stop le plus proche
        return abs($i - $k) > abs($j - $k)
            ? $j - $pk
            : $i - $pk;
    }
}

if (!function_exists('decale')) {
    function decale($courses, $enveloppe)
    {
        $nbstop = 0;
        $def = 0;

        foreach ($enveloppe as $env) {
            if (($env["stp"] ?? 0) == 1) {
                $def += abs(proche($courses, $env["x"], 0));
                $nbstop++;
            }
        }

        return $nbstop > 0 ? $def / $nbstop : 0;
    }
}

if (!function_exists('reEnv')) {
    function reEnv($enveloppe, $courses)
    {
        $dec = 0;

        foreach ($enveloppe as $key => $env) {

            $decPrec = $dec;

            if (($env["stp"] ?? 0) == 1) {
                $dec = proche($courses, $env["x"], $decPrec);
            }

            // Si décalage anormal → reset
            if (abs($dec - $decPrec) > 50) {
                $dec = 0;
            }

            // Appliquer le décalage
            $enveloppe[$key]["x"] += $dec;
        }

        return $enveloppe;
    }
}
