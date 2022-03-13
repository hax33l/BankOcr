<?php

namespace BankOcr\parser;

use BankOcr\dane\NumerRachunku;
use BankOcr\dane\Cyfra;

class OcrParser
{
    const LICZBA_ZNAKOW_W_LINII =  27;
    const LICZBA_ZNAKOW_CYFRY_W_LINII = 3;
    const LICZBA_LINII_DLA_NUMERU_RACHUNKU = 4;

    private $numeryRachunkow = [];
    private $sciezka;

    function __construct($sciezka)
    {
        $this->sciezka = $sciezka;
    }

    public function odczytajNumeryRachunkow()
    {
        $linie = file($this->sciezka);
        $liczbaWczytanychLinii = count($linie);

        for ($i = 0; $i < $liczbaWczytanychLinii; $i += self::LICZBA_LINII_DLA_NUMERU_RACHUNKU) {
            $cyfry = [];
            for ($j = 0; $j < self::LICZBA_ZNAKOW_W_LINII; $j += self::LICZBA_ZNAKOW_CYFRY_W_LINII) {
                $ciagZnakowCyfry = substr($linie[$i], $j, self::LICZBA_ZNAKOW_CYFRY_W_LINII)
                    . substr($linie[$i + 1], $j, self::LICZBA_ZNAKOW_CYFRY_W_LINII)
                    . substr($linie[$i + 2], $j, self::LICZBA_ZNAKOW_CYFRY_W_LINII);

                $cyfry[] = new Cyfra($ciagZnakowCyfry);
            }
            if (NumerRachunku::czyPoprawnaDlugoscNumeruRachunku($cyfry)) {
                $this->numeryRachunkow[] = new NumerRachunku($cyfry);
            }
        }
        return $this->numeryRachunkow;
    }

    public function getNumeryRachunkow()
    {
        return $this->numeryRachunkow;
    }
}
