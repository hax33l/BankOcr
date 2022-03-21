<?php

namespace BankOcr;

use Exception;

class FileLogger implements Logger
{
    private $plikWynikowy;

    function __construct(string $sciezkaPlikuDoZapisu)
    {
        $this->plikWynikowy = fopen($sciezkaPlikuDoZapisu, 'a');
        if(!$this->plikWynikowy){
            throw new Exception("Niepowodzenie podczas otwarcia pliku " . $sciezkaPlikuDoZapisu);
        }
    }

    function __destruct()
    {
        fclose($this->plikWynikowy);
    }

    public function log(string $wiersz)
    {
        if(!fwrite($this->plikWynikowy, $wiersz . PHP_EOL)){
            throw new Exception("Niepowodzenie podczas zapisu do pliku ");
        }
    }
}
