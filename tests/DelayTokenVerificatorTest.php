<?php

namespace App\Tests;

use App\Utils\DelayTokenVerificator;
use PHPUnit\Framework\TestCase;

class DelayTokenVerificatorTest extends TestCase
{
    public function testIsValidToken()
    {
        $delayTokenVerificator = new DelayTokenVerificator();

        // >>>VALID TOKEN
        $now = new \DateTime('now');
        $result = $delayTokenVerificator->isValidToken($now);
        $this->assertEquals(true, $result);

        $requestedDate = (clone $now)->modify('-22 hours');
        $result = $delayTokenVerificator->isValidToken($requestedDate);
        $this->assertEquals(true, $result);

        $yesterday = (clone $now)->modify('-1 day');
        $result = $delayTokenVerificator->isValidToken($yesterday);
        $this->assertEquals(true, $result);
        // <<<VALID TOKEN

        // >>>INVALID TOKEN
        $requestedDate = (clone $now)->modify('-25 hours');
        $result = $delayTokenVerificator->isValidToken($requestedDate);
        $this->assertEquals(false, $result);

        $twoDaysBefore = (clone $now)->modify('-2 day');
        $result = $delayTokenVerificator->isValidToken($twoDaysBefore);
        $this->assertEquals(false, $result);

        $requestedDate = \DateTime::createFromFormat('d/m/Y H:i:s','02/05/2020 17:00:00')->setTimezone(new \DateTimeZone('Europe/Paris'));
        $result = $delayTokenVerificator->isValidToken($requestedDate);
        $this->assertEquals(false, $result);
        // <<<INVALID TOKEN

        // >>>IMPOSSIBLE SITUATION
        $tomorrow = (clone $now)->modify('+1 day');
        $result = $delayTokenVerificator->isValidToken($tomorrow);
        $this->assertEquals(true, $result);

        $twoDaysAfter = (clone $now)->modify('+2 day');
        $result = $delayTokenVerificator->isValidToken($twoDaysAfter);
        $this->assertEquals(false, $result);
        // <<<VALID TOKEN BUT IMPOSSIBLE SITUATION
    }
}
