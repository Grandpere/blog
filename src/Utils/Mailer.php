<?php

namespace App\Utils;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMessage($from, $to, $subject, $body, $context): TemplatedEmail
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->replyTo($from)
            ->htmlTemplate($body)
            ->context($context)
        ;
        $this->mailer->send($email);

        return $email;
    }
}