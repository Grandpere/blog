<?php

namespace App\Tests;

use App\Utils\Gravatar;
use PHPUnit\Framework\TestCase;

class GravatarTest extends TestCase
{
    public function testGetEmailHash()
    {
        $gravatar = new Gravatar();

        $result = $gravatar->getGravatar('test@gmail.com');
        $this->assertContains('1aedb8d9dc4751e229a335e371db8058', $result);

        $result = $gravatar->getGravatar('    test@gmail.com     ');
        $this->assertContains('1aedb8d9dc4751e229a335e371db8058', $result);

        $result = $gravatar->getGravatar('');
        $this->assertContains('d41d8cd98f00b204e9800998ecf8427e', $result);

        $result = $gravatar->getGravatar(' ');
        $this->assertContains('d41d8cd98f00b204e9800998ecf8427e', $result);

        $result = $gravatar->getGravatar();
        $this->assertContains('d41d8cd98f00b204e9800998ecf8427e', $result);
    }
}
