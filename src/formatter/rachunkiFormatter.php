<?php

namespace BankOcr\formatter;

use BankOcr\dane\NumerRachunku;

class rachunkiFormatter
{
    public function zwrocWiersz(NumerRachunku $numerRachunku)
    {
        $wiersz = $numerRachunku;

        if($numerRachunku->czyPoprawnyRachunek()){
            return $wiersz;
        }
        if($numerRachunku->czyIstniejeJednaAlternatywa()){
            $skorygowanyNumerRachunku = $numerRachunku->wyszukajMozliweAlternatywy();
            $wiersz = array_shift($skorygowanyNumerRachunku);
            return $wiersz;
        }
        if(!$numerRachunku->czyRozpoznanoWszystkieZnaki() && !$numerRachunku->czyIstniejaAlternatywy()){
            $wiersz .= " ILL";
            return $wiersz;
        }
        if(!$numerRachunku->czySumaKontrolnaJestPoprawna() && !$numerRachunku->czyIstniejaAlternatywy()){
            $wiersz .= " ERR";
            return $wiersz;
        }
        $wiersz .= " AMB [" . implode(", ", $numerRachunku->wyszukajMozliweAlternatywy()) . "]";
        return $wiersz;
    }
}
