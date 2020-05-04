<?php

namespace App\Utils;

class DelayTokenVerificator
{
    // token validity = 24 hours = 86400 seconds
    CONST VALIDITY = 86400;

    public function isValidToken(\DateTimeInterface $requestedDate = null)
    {
        if ($requestedDate === null)
        {
            return false;
        }

        $now = new \DateTime();
        $diff = $requestedDate->getTimestamp() - $now->getTimestamp();

        if($diff > 0) {
            return false;
        }

        return abs($diff) <= self::VALIDITY ? true : false;
    }
}