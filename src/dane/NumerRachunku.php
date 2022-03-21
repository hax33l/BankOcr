<?php

namespace BankOcr\dane;

use BankOcr\dane\Cyfra;

class NumerRachunku
{
    const LICZBA_CYFR_NUMERU_RACHUNKU = 9;
    
    public $numerRachunku;

    function __construct(array $cyfry)
    {
        $this->numerRachunku = $cyfry;
    }

    function __toString()
    {
        return $this->getNumerRachunku();
    }

    public function getNumerRachunku()
    {
        $numerRachunku = '';
        foreach ($this->numerRachunku as $cyfra) {
            $numerRachunku .= $cyfra->getWartosc();
        }
        return $numerRachunku;
    }

    public function zastapCyfre($indeks, Cyfra $alternatywnaCyfra)
    {
        $this->numerRachunku[$indeks] = $alternatywnaCyfra;
    }

    public function czyIstniejaAlternatywy()
    {
        if ($this->wyszukajMozliweAlternatywy()) {
            return true;
        }
        return false;
    }

    public function czyIstniejeJednaAlternatywa()
    {
        if (count($this->wyszukajMozliweAlternatywy()) == 1) {
            return true;
        }
        return false;
    }

    public function wyszukajMozliweAlternatywy()
    {
        $alternatywneNumery = [];

        foreach ($this->numerRachunku as $indeks => $cyfra) {
            $alternatywneCyfry = [];
            $alternatywneCyfry = $cyfra->zwrocAlternatywneCyfry();
            foreach ($alternatywneCyfry as $alternatywnaCyfra) {
                $alternatywnyNumer = new NumerRachunku($this->numerRachunku);
                $alternatywnyNumer->zastapCyfre($indeks, $alternatywnaCyfra);
                if ($alternatywnyNumer->czyPoprawnyRachunek()) {
                    $alternatywneNumery[] = $alternatywnyNumer;
                }
            }
        }

        return $alternatywneNumery;
    }


    public function czyPoprawnyRachunek()
    {
        if (!$this->czyRozpoznanoWszystkieZnaki()) {
            return false;
        }
        if (!$this->czySumaKontrolnaJestPoprawna()) {
            return false;
        }
        return true;
    }

    public function czyRozpoznanoWszystkieZnaki()
    {
        if (str_contains($this->getNumerRachunku(), '?')) {
            return false;
        }
        return true;
    }

    public function czySumaKontrolnaJestPoprawna()
    {
        $numerRachunku = $this->getNumerRachunku();
        $wspolczynnik = strlen($numerRachunku);
        $sumaKontrolna = 0;
        for ($i = 0; $i < strlen($numerRachunku); $i++, $wspolczynnik--) {
            $sumaKontrolna += (int) $numerRachunku[$i] * $wspolczynnik;
        }
        return $sumaKontrolna % 11 == 0;
    }

    public function czyRachunekZawieraNierozpoznaneZnaki()
    {
        if(substr_count($this->getNumerRachunku(), '?') == 0){
            return true;
        } else {
            return false;
        }
    }

    public static function czyPoprawnaDlugoscNumeruRachunku(array $cyfry)
    {
        if (count($cyfry) != self::LICZBA_CYFR_NUMERU_RACHUNKU) {
            return false;
        }
        return true;
    }
}
