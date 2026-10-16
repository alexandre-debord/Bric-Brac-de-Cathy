<?php

namespace App\Service;

class CalculateurTotal
{
    /** Convertit un prix "14.90" en centimes (1490), sans erreur d'arrondi */
    public function enCentimes(string $prix): int
    {
        return (int) round((float) $prix * 100);
    }

    /**
     * Total en centimes d'une liste de lignes.
     * @param array<array{prix: string, quantite: int}> $lignes
     */
    public function total(array $lignes): int
    {
        $total = 0;
        foreach ($lignes as $ligne) {
            $total += $this->enCentimes($ligne['prix']) * $ligne['quantite'];
        }
        return $total;
    }

    /** 2980 → "29.80" (format attendu par la colonne DECIMAL) */
    public function formater(int $centimes): string
    {
        return number_format($centimes / 100, 2, '.', '');
    }
}