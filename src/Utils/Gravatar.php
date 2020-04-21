<?php


namespace App\Utils;


class Gravatar
{
    CONST IMG_URL = 'https://www.gravatar.com/avatar/';
    CONST IMG_SIZE = 200;

    private function getEmailHash(string $email)
    {
        return md5(strtolower(trim($email)));
    }

    private function getGravatarUrl(string $hash)
    {
        return self::IMG_URL.$hash."?s=".self::IMG_SIZE;
    }

    public function getGravatar(string $email)
    {
        $hash = $this->getEmailHash($email);

        return $this->getGravatarUrl($hash);
    }

}