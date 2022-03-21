<?php

use BankOcr\ConsoleLogger;
use BankOcr\FileLogger;
use BankOcr\OcrParser;
use BankOcr\RachunkiFormatter;

require_once realpath("vendor/autoload.php");

$sciezkaPlikuDoWczytania = $argv[1] ?? readline("Podaj ścieżkę pliku zawierającego dane do wczytania: ");
$sciezkaPlikuDoZapisu = $argv[2] ?? NULL;

while (!file_exists($sciezkaPlikuDoWczytania)) {
    echo "Podano niepoprawną ścieżkę\n";
    $sciezkaPlikuDoWczytania = readline("Podaj ścieżkę pliku zawierającego dane do wczytania: ");
}

try {
    if ($sciezkaPlikuDoZapisu) {
        $logger = new FileLogger($sciezkaPlikuDoZapisu);
    } else {
        $logger = new ConsoleLogger();
    }

    $ocrParser = new OcrParser($sciezkaPlikuDoWczytania, new FileLogger('log.txt'));
    $rachunkiFormatter = new RachunkiFormatter;

    $rachunkiDoWyswietlenia = $ocrParser->getNumeryRachunkow();

    foreach ($rachunkiDoWyswietlenia as $numerRachunku) {
        $logger->log($rachunkiFormatter->zwrocWiersz($numerRachunku));
    }
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}