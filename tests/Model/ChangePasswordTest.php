<?php

namespace App\Tests\Model;

use App\Model\ChangePassword;
use PHPUnit\Framework\TestCase;

class ChangePasswordTest extends TestCase
{
    private $changePassword;

    public function setUp()
    {
        $this->changePassword = new ChangePassword();
    }

    public function testAfterConstructor()
    {
        $this->assertNull($this->changePassword->getOldPassword());
        $this->assertNull($this->changePassword->getPassword());
    }

    public function testPassword()
    {
        $this->changePassword->setPassword('password');
        $this->assertEquals('password', $this->changePassword->getPassword());
    }

    public function testOldPassword()
    {
        $this->changePassword->setOldPassword('oldpassword');
        $this->assertEquals('oldpassword', $this->changePassword->getOldPassword());
    }
}
