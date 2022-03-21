<?php

$uchwytPliku = @fopen("input.txt", 'r');
$output = @fopen("testinput.txt", 'w');

while ($wiersz = fgets($uchwytPliku)) {
    for ($i = 0; $i < 100; $i++) {
        fwrite($output, $wiersz . PHP_EOL);
    }
}
