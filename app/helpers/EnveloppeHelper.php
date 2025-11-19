<?php

namespace App\Helpers;

class EnveloppeHelper
{
    /**
     * Charge une enveloppe depuis un fichier
     *
     * @param string $filePath Chemin vers le fichier d'enveloppe
     * @param int $idenveloppe ID de l'enveloppe
     * @param int $figer Statut figer
     * @return array Matrice de l'enveloppe
     */
    public static function loadEnveloppe($filePath, $idenveloppe, $figer)
    {
        $matrice = [];

        if (!file_exists($filePath)) {
            return $matrice;
        }
        $ln=0;
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1024, ";")) !== FALSE) {
                if ($ln>0 && count($data) >= 3) {
                    $matrice[] = [
                        'x' => (float)$data[1],
                        'y' => (float)$data[2],
                        'stp' => (int)$data[3],
                        'label'=>$data[0]
                    ];
                }
                $ln++;
            }
            fclose($handle);
        }

        return $matrice;
    }
}

