<?php

namespace App\Config;

use Dotenv\Dotenv;

class Env
{
    public static function load(string $path): void
    {
        Dotenv::createImmutable($path)->load();
    }
}
