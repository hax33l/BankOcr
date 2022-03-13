<?php

namespace BankOcr\dane;

class Cyfra
{
    private $wzorceCyfr = [
        0 => "_ | ||_|",
        1 => "|  |",
        2 => "_  _||_",
        3 => "_  _| _|",
        4 => "|_|  |",
        5 => "_ |_  _|",
        6 => "_ |_ |_|",
        7 => "_   |  |",
        8 => "_ |_||_|",
        9 => "_ |_| _|"
    ];

    public $znakiCyfry;
    public $wartosc;

    function __construct($znakiCyfry)
    {
        $this->znakiCyfry = $znakiCyfry;
        $cyfra = $this->wyszukajWzorzec($znakiCyfry);
        $this->wartosc = ($cyfra !== false) ? $cyfra : '?';
    }

    public function getWartosc()
    {
        return $this->wartosc;
    }

    public function getCiagZnakow()
    {
        return $this->znakiCyfry;
    }

    private function wyszukajWzorzec($znakiCyfry)
    {
        $dopasowanaCyfra = array_search(trim($znakiCyfry), $this->wzorceCyfr);
        return (is_numeric($dopasowanaCyfra)) ? $dopasowanaCyfra : false;
    }
    public function zwrocAlternatywneCyfry()
    {
        $tablicaZnakow = ['_', '|', ' '];
        $alternatywneCyfry = [];
        $tablicaZnakowCyfry = str_split($this->znakiCyfry);

        foreach ($tablicaZnakowCyfry as $indeks => $znak) {
            $tymczasowyCiagZnakow = $this->znakiCyfry;

            foreach ($tablicaZnakow as $alternatywnyZnak) {
                if ($alternatywnyZnak != $znak) {
                    $tymczasowyCiagZnakow[$indeks] = $alternatywnyZnak;
                    $cyfra = new Cyfra($tymczasowyCiagZnakow);
                    if ($cyfra->getWartosc() != '?') {
                        $alternatywneCyfry[] = $cyfra;
                    }
                }
            }
        }
        return $alternatywneCyfry;
    }
}
