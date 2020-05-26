<?php


namespace App\Tests\Controller\Web;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContactControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function tearDown(): void
    {
        $this->client = null;
    }

    public function testGetContact()
    {
        // TODO : tests env variable for prevent error 500
        $crawler = $this->client->request('GET', '/contact');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}