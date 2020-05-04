<?php

namespace App\Tests;

use App\Utils\Gravatar;
use PHPUnit\Framework\TestCase;

class GravatarTest extends TestCase
{
    /** @var Gravatar  */
    private $gravatar;

    public function setUp()
    {
        $this->gravatar = new Gravatar();
    }

    public function testGetGravatarWithValidEmail()
    {
        $result = $this->gravatar->getGravatar('test@gmail.com');
        $this->assertContains('1aedb8d9dc4751e229a335e371db8058', $result);
    }

    public function testGetGravatarWithBeginOrEndSpace()
    {
        $result = $this->gravatar->getGravatar('    test@gmail.com     ');
        $this->assertContains('1aedb8d9dc4751e229a335e371db8058', $result);
    }

    public function testGetGravatarWithEmptyEmail()
    {
        $result = $this->gravatar->getGravatar('');
        $this->assertContains('d41d8cd98f00b204e9800998ecf8427e', $result);
    }

    public function testGetGravatarWithoutEmailParam()
    {
        $result = $this->gravatar->getGravatar();
        $this->assertContains('d41d8cd98f00b204e9800998ecf8427e', $result);
    }
}
