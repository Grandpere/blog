<?php

namespace App\Utils;

class DelayTokenVerificator
{
    public function isValidToken(\DateTimeInterface $requestedDate = null)
    {
        if ($requestedDate === null)
        {
            return false;
        }

        $now = new \DateTime();
        $interval = $requestedDate->diff($now)->format('%r%a');

        return $interval > 1 ? false : true;
    }
}