<?php

namespace App\Tests\Utils;

use App\Utils\DelayTokenVerificator;
use PHPUnit\Framework\TestCase;

class DelayTokenVerificatorTest extends TestCase
{
    /** @var DelayTokenVerificator */
    private $delayTokenVerificator;
    /** @var \DateTime */
    private $now;

    public function setUp()
    {
        $this->delayTokenVerificator = new DelayTokenVerificator();

        $this->now = new \DateTime('now');
    }

    public function testIsValidTokenWithoutParameter()
    {
        $result = $this->delayTokenVerificator->isValidToken();
        $this->assertEquals(false, $result);
    }

    public function testIsValidTokenWithTokenCreatedNow()
    {
        $result = $this->delayTokenVerificator->isValidToken($this->now);
        $this->assertEquals(true, $result, 'a token that is at least 24 hours old should be considered expired');
    }

    public function testIsValidTokenWithTokenCreatedToday()
    {
        $result = $this->delayTokenVerificator->isValidToken(new \DateTime('today'));
        $this->assertEquals(true, $result, 'a token that is at least 24 hours old should be considered expired');
    }

    public function testIsValidTokenWithTokenCreated22HoursAgo()
    {
        $requestedDate = (clone $this->now)->modify('-22 hours');
        $result = $this->delayTokenVerificator->isValidToken($requestedDate);
        $this->assertEquals(true, $result, 'a token that is at least 24 hours old should be considered expired');
    }

    public function testIsValidTokenWithTokenCreated1DayAgo()
    {
        $requestedDate = (clone $this->now)->modify('-1 day');
        $result = $this->delayTokenVerificator->isValidToken($requestedDate);
        $this->assertEquals(true, $result, 'a token that is at least 24 hours old should be considered expired');
    }

    public function testIsValidTokenWithTokenCreated25HoursAgo()
    {
        $requestedDate = (clone $this->now)->modify('-25 hours');
        $result = $this->delayTokenVerificator->isValidToken($requestedDate);
        $this->assertEquals(false, $result, 'a token that is at least 24 hours old should be considered expired');
    }

    public function testIsValidTokenWithTokenCreated2DaysAgo()
    {
        $requestedDate = (clone $this->now)->modify('-2 days');
        $result = $this->delayTokenVerificator->isValidToken($requestedDate);
        $this->assertEquals(false, $result, 'a token that is at least 24 hours old should be considered expired');
    }

    public function testIsValidTokenWithTokenCreated1DayAfter()
    {
        $requestedDate = (clone $this->now)->modify('+1 day');
        $result = $this->delayTokenVerificator->isValidToken($requestedDate);
        $this->assertEquals(false, $result, 'a not existing token on the current date cannot be valid');
    }

    public function testIsValidTokenWith()
    {
        $this->expectException('\TypeError');
        $this->delayTokenVerificator->isValidToken('02/05/2020 17:00:00');

    }
}
