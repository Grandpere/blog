<?php


namespace App\Utils;


class Gravatar
{
    CONST IMG_URL = 'https://www.gravatar.com/avatar/';

    private function getEmailHash(string $email)
    {
        return md5(strtolower(trim($email)));
    }

    private function getGravatarUrl(string $hash)
    {
        return self::IMG_URL.$hash;
    }

    public function getGravatar(string $email)
    {
        $hash = $this->getEmailHash($email);
        $url = $this->getGravatarUrl($hash);

        return $url;
    }

}