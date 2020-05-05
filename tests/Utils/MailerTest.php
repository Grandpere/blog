<?php

namespace App\Tests\Utils;

use App\Utils\Mailer;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerTest extends TestCase
{
    private $mailer;
    const MAIL_FROM = 'test@test.com';

    public function setUp()
    {
        $mockMailer = $this->createMock(MailerInterface::class);

        $this->mailer = new Mailer($mockMailer);
    }

    public function testSendMessage()
    {
        $from = 'test@test.com';
        $to = 'lorenzo.marozzo@gmail.com';
        $subject = 'mail send via unit test';
        $body = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Hello</title></head><body><h1>Hello</h1><p>Message envoyé depuis un test unitaire</p></body></html>';
        $context = [];

        $email = $$this->mailer->sendMessage($from, $to, $subject, $body, $context);

        $this->assertInstanceOf(TemplatedEmail::class, $email);
        $this->assertSame('mail send via unit test', $email->getSubject());
        $this->assertCount(1, $email->getTo());
        $adresses = $email->getTo();
        $this->assertInstanceOf(Address::class, $adresses[0]);
        $this->assertSame('lorenzo.marozzo@gmail.com', $adresses[0]->getAddress());
        $this->assertEmpty($email->getContext());
    }
}