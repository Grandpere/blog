<?php

namespace App\Tests\Utils;

use App\Utils\Mailer;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerTest extends TestCase
{
    /** @var Mailer  */
    private $mailer;
    const MAIL_FROM = 'test@test.com';

    public function setUp()
    {
        $mockMailer = $this->createMock(MailerInterface::class);
        $mockMailer->expects($this->once())
            ->method('send');

        $this->mailer = new Mailer($mockMailer);
    }

    /**
     * @dataProvider getEmailDataProvider
     */
    public function testSendMessage($from, $to, $subject, $body, $context)
    {
        $email = $this->mailer->sendMessage($from, $to, $subject, $body, $context);

        $this->assertInstanceOf(TemplatedEmail::class, $email);
        $this->assertSame('Mail send via Unit test', $email->getSubject());
        $this->assertCount(1, $email->getTo());
        $adresses = $email->getTo();
        $this->assertInstanceOf(Address::class, $adresses[0]);
        $this->assertSame('lorenzo@gmail.com', $adresses[0]->getAddress());
        $this->assertEmpty($email->getContext());
    }

    public function getEmailDataProvider()
    {
        yield [self::MAIL_FROM, 'lorenzo@gmail.com', 'Mail send via Unit test', '<h1>Mail Via unit test</h1>', []];
    }
}