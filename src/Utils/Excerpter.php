<?php

namespace App\Utils;

class Excerpter
{
    CONST LIMIT = 200;
    
    public static function excerptify(string $string) : string
    {
        return substr($string, 0, SELF::LIMIT);
    }
}