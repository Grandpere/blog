<?php

namespace App\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class ChangePassword
{
    /**
     * @SecurityAssert\UserPassword(
     *     message = "Wrong value for your current password"
     * )
     */
    private $oldPassword;

    private $password;

    function getOldPassword() {
        return $this->oldPassword;
    }

    function getPassword() {
        return $this->password;
    }

    function setOldPassword($oldPassword) {
        $this->oldPassword = $oldPassword;
        return $this;
    }

    function setPassword($password) {
        $this->password = $password;
        return $this;
    }
}