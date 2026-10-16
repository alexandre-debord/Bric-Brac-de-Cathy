<?php

namespace App\Tests\Service;

use App\Service\CalculateurTotal;
use PHPUnit\Framework\TestCase;   // TestCase, pas WebTestCase : pas de Symfony, pas de base

class CalculateurTotalTest extends TestCase
{
    public function testUneLigne(): void
    {
        $calculateur = new CalculateurTotal();
        $this->assertSame(2980, $calculateur->total([['prix' => '14.90', 'quantite' => 2]]));
    }

    public function testPlusieursLignes(): void
    {
        $calculateur = new CalculateurTotal();
        $total = $calculateur->total([
            ['prix' => '14.90', 'quantite' => 2],
            ['prix' => '32.00', 'quantite' => 1],
        ]);
        $this->assertSame(6180, $total);
    }

    public function testPanierVideDonneZero(): void
    {
        $this->assertSame(0, (new CalculateurTotal())->total([]));
    }

    public function testPasDErreurDArrondi(): void
    {
        // En float, 19.99 * 100 = 1998.9999999999998 : sans round(), on perdrait 1 centime
        $this->assertSame(1999, (new CalculateurTotal())->total([['prix' => '19.99', 'quantite' => 1]]));
    }

    public function testFormatage(): void
    {
        $calculateur = new CalculateurTotal();
        $this->assertSame('29.80', $calculateur->formater(2980));
        $this->assertSame('0.05', $calculateur->formater(5));   // 5 centimes
    }
}
