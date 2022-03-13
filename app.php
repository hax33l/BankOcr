<?php

use BankOcr\parser\OcrParser;
use BankOcr\formatter\rachunkiFormatter;

require_once realpath("vendor/autoload.php");

$sciezkaPlikuDoWczytania = $argv[1] ?? readline("Podaj ścieżkę pliku zawierającego dane do wczytania: ");

while (!file_exists($sciezkaPlikuDoWczytania)) {
    echo "Podano niepoprawną ścieżkę\n";
    $sciezkaPlikuDoWczytania = readline("Podaj ścieżkę pliku zawierającego dane do wczytania: ");
}

$ocrParser = new OcrParser($sciezkaPlikuDoWczytania);
$rachunkiFormatter = new rachunkiFormatter;

$ocrParser->odczytajNumeryRachunkow();
$rachunkiDoWyswietlenia = $ocrParser->getNumeryRachunkow();

if ($argc == 3) {
    $sciezkaPlikuDoZapisu = $argv[2];
    $plikWynikowy = fopen($sciezkaPlikuDoZapisu, "w");
    foreach ($rachunkiDoWyswietlenia as $numerRachunku) {
        fwrite($plikWynikowy, $rachunkiFormatter->zwrocWiersz($numerRachunku) . PHP_EOL);
    }
    fclose($plikWynikowy);
} else {
    foreach ($rachunkiDoWyswietlenia as $numerRachunku) {
        echo ($rachunkiFormatter->zwrocWiersz($numerRachunku) . PHP_EOL);
    }
}
