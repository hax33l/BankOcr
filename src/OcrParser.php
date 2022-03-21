<?php

namespace BankOcr;

use BankOcr\dane\Cyfra;
use BankOcr\dane\NumerRachunku;
use Exception;

class OcrParser
{
    const LICZBA_ZNAKOW_CYFR_W_LINII =  27;
    const LICZBA_ZNAKOW_CYFRY_W_LINII = 3;
    const LICZBA_LINII_DLA_NUMERU_RACHUNKU = 4;

    private $numeryRachunkow = [];
    private $logger;
    private $komunikatyBledow = [];

    function __construct(string $sciezka, Logger $logger)
    {
        $this->logger = $logger;
        $this->wczytajNumeryRachunkow($sciezka);
    }

    private function wczytajNumeryRachunkow(string $sciezka)
    {
        $uchwytPliku = @fopen($sciezka, 'r');
        $licznikLinii = 0;

        if (!$uchwytPliku) {
            throw new Exception("Błąd podczas otwierania pliku '$sciezka'");
        }

        while ($wiersz = fgets($uchwytPliku)) {
            $licznikLinii++;
            $wierszeNumeruKonta[] = $this->zwrocPostacZnormalizowana($wiersz);

            $this->sprobujOdczytacPobraneWiersze($wierszeNumeruKonta, $licznikLinii);
        }

        $this->zapiszBledy();
        fclose($uchwytPliku);
    }
    private function sprobujOdczytacPobraneWiersze(array &$wierszeNumeruKonta, int $licznikLinii)
    {
        if (count($wierszeNumeruKonta) % self::LICZBA_LINII_DLA_NUMERU_RACHUNKU == 0) {
            $czyOdczytanoRachunek = $this->odczytajRachunek($wierszeNumeruKonta, $licznikLinii);
            if ($czyOdczytanoRachunek) {
                $wierszeNumeruKonta = [];
            } else {
                array_shift($wierszeNumeruKonta);
            }
        }
    }
    private function odczytajRachunek(array $wierszeNumeruKonta, int $licznikLinii)
    {
        $czyOdczytanoRachunek = false;
        try {
            $this->przerwijGdyNiepoprawne($wierszeNumeruKonta, $licznikLinii);
            $numerRachunku = $this->wczytajNumerRachunku($wierszeNumeruKonta);
            $czyOdczytanoRachunek = $this->czyOdczytanoRachunek($numerRachunku);
            $this->dopiszNumerRachunku($numerRachunku);
        } catch (Exception $e) {
            $komunikatBledu = $e->getMessage();
            $numerRachunku = $this->sprobujNaprawicRachunek($wierszeNumeruKonta);
            if ($numerRachunku) {
                $this->dopiszNumerRachunku($numerRachunku);
                $czyOdczytanoRachunek = true;
                $komunikatBledu .= ", rachunek został naprawiony $numerRachunku";
            }
            $this->dopiszBlad($komunikatBledu);
        }
        return $czyOdczytanoRachunek;
    }

    private function sprobujNaprawicRachunek(array $wierszeNumeruKonta)
    {
        if (!$this->czyPoprawneZnakiWWierszach($wierszeNumeruKonta)) {
            return false;
        }

        $wierszeNumeruKonta = $this->naprawDlugoscWierszy($wierszeNumeruKonta);
        $numerRachunku = $this->wczytajNumerRachunku($wierszeNumeruKonta);

        if ($this->czyOdczytanoRachunek($numerRachunku)) {
            if ($numerRachunku->czyRachunekZawieraNierozpoznaneZnaki()) {
                return $numerRachunku;
            }
        }
        return false;
    }

    private function naprawDlugoscWierszy(array $wierszeNumeruKonta)
    {
        for ($i = 0; $i < count($wierszeNumeruKonta); $i++) {
            if (strlen($wierszeNumeruKonta[$i]) > 27) {
                $wierszeNumeruKonta[$i] = substr($wierszeNumeruKonta[$i], 0, 27);
            } else {
                $wierszeNumeruKonta[$i] = str_pad($wierszeNumeruKonta[$i], 27);
            }
        }
        return $wierszeNumeruKonta;
    }
    private function dopiszBlad(string $blad)
    {
        if (end($this->komunikatyBledow) != $blad) {
            $this->komunikatyBledow[] = $blad;
        }
    }
    private function zapiszBledy()
    {
        $komunikatyBledow = $this->komunikatyBledow;
        foreach ($komunikatyBledow as $komunikatBledu) {
            $this->logger->log($komunikatBledu);
        }
    }

    private function dopiszNumerRachunku(NumerRachunku $numerRachunku)
    {
        $this->numeryRachunkow[] = $numerRachunku;
    }

    private function wczytajNumerRachunku(array $wczytaneLinie)
    {
        $cyfry = [];
        for ($j = 0; $j < self::LICZBA_ZNAKOW_CYFR_W_LINII; $j += self::LICZBA_ZNAKOW_CYFRY_W_LINII) {
            $ciagZnakowCyfry = substr($wczytaneLinie[0], $j, self::LICZBA_ZNAKOW_CYFRY_W_LINII)
                . substr($wczytaneLinie[1], $j, self::LICZBA_ZNAKOW_CYFRY_W_LINII)
                . substr($wczytaneLinie[2], $j, self::LICZBA_ZNAKOW_CYFRY_W_LINII);

            $cyfry[] = new Cyfra($ciagZnakowCyfry);
        }
        $numerRachunku = new NumerRachunku($cyfry);
        return $numerRachunku;
    }

    private function czyOdczytanoRachunek(string $numerRachunku)
    {
        if (substr_count($numerRachunku, '?') == strlen($numerRachunku)) {
            return false;
        }
        return true;
    }

    private function przerwijGdyNiepoprawne(array $wierszeNumeruKonta, int $licznikLinii)
    {
        for ($i = 0; $i < count($wierszeNumeruKonta); $i++) {
            if (!$this->czyPoprawnaLiczbaZnakow($wierszeNumeruKonta[$i])) {
                throw new Exception("Niepoprawna liczba znaków w linii " . $licznikLinii + $i - 3);
            }
            if (!$this->czyPoprawneZnakiWWierszu($wierszeNumeruKonta[$i])) {
                throw new Exception("Niedozwolony znak w linii " . $licznikLinii + $i - 3);
            }
        }
    }

    private function czyPoprawneZnakiWWierszu(string $wiersz)
    {
        $wzor = '/[^_| ]/';
        if (!preg_match($wzor, $wiersz)) {
            return true;
        }
        return false;
    }

    private function czyPoprawneZnakiWWierszach(array $wiersze)
    {
        foreach ($wiersze as $wiersz) {
            if (!$this->czyPoprawneZnakiWWierszu($wiersz)) {
                return false;
            }
        }
        return true;
    }

    private function czyPoprawnaLiczbaZnakow(string $wiersz)
    {
        if (strlen($wiersz) == self::LICZBA_ZNAKOW_CYFR_W_LINII) {
            return true;
        }
        return false;
    }

    private function zwrocPostacZnormalizowana(string $linia)
    {
        return preg_replace('/\r\n?/', "", $linia);
    }

    public function getNumeryRachunkow()
    {
        return $this->numeryRachunkow;
    }
}
