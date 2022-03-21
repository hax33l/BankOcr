<?php

namespace BankOcr;

class ConsoleLogger implements Logger
{
    public function log(string $wiersz)
    {
        echo($wiersz . PHP_EOL);
    }
}