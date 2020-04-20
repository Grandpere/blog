<?php

namespace App\Utils;

use Twig\Environment as twig;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    private $twig;
    private $mailer;

    public function __construct(twig $twig, MailerInterface $mailer)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    public function sendMessage($from, $to, $subject, $body, $context)
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->replyTo($from)
            ->htmlTemplate($body)
            ->context($context)
        ;
        return $this->mailer->send($email);
    }
}