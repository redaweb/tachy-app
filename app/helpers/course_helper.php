<?php

if (!function_exists('proche')) {
    function proche($courses, $pk, $ecart)
    {
        $proch = 0;
        if (($pk + $ecart) >= count($courses)) {
            $k = count($courses) - 1;
        } elseif (($pk + $ecart) < 0) {
            $k = 0;
        } else {
            $k = floor($pk + $ecart);
        }

        for ($i = $k; $i >= 0 && $courses[$i]['dsstop'] != 1; $i--);
        for ($j = $k; $j < count($courses) && $courses[$j]['dsstop'] != 1; $j++);

        if ($i < 0 && $j >= count($courses)) return 1000;
        if ($i < 0) $proch = $j - $pk;
        if ($j >= count($courses)) $proch = $i - $pk;
        if ($i >= 0 && $j < count($courses)) {
            $proch = abs($i - $pk - $ecart) > abs($j - $pk - $ecart)
                ? $j - $pk
                : $i - $pk;
        }
        return $proch;
    }
}

if (!function_exists('decale')) {
    function decale($courses, $enveloppe)
    {
        $nbstop = 0;
        $def = 0;
        for ($i = 0; $i < count($enveloppe); $i++) {
            if ($enveloppe[$i]["stp"] == 1) {
                $def += abs(proche($courses, $enveloppe[$i]["x"], 0));
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
        for ($i = 0; $i < count($enveloppe); $i++) {
            $decPrec = $dec;
            if ($enveloppe[$i]["stp"] == 1) {
                $dec = proche($courses, $enveloppe[$i]["x"], $decPrec);
            }
            if (abs($dec - $decPrec) > 50) $dec = 0;
            $enveloppe[$i]["x"] += $dec;
        }
        return $enveloppe;
    }
}
