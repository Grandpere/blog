<?php


namespace App\Tests\Controller\Web;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
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

    /**
     * @param $url
     * @dataProvider getIncorrectUrls
     */
    public function testHomepageWithIncorrectUrls($url)
    {
        $this->client->request('GET', $url);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function getIncorrectUrls()
    {
        yield ['/article'];
        yield ['/articless'];
        yield ['/tag'];
        yield ['/tagss'];
    }

    public function testHomepageWithUrlEndedByBackslashWithoutRedirection()
    {
        $this->client->request('GET', '/articles/');
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse()->getStatusCode());
    }

    public function testHomepageWithUrlEndedByBackslashWithRedirection()
    {
        $this->client->followRedirects();
        $this->client->request('GET', '/articles/');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testHomepage()
    {
        $this->client->request('GET', '/articles');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param $url
     * @dataProvider getAuthenticationUrl
     */
    public function testAuthenticationWithUrl($url)
    {
        $this->client->followRedirects();
        $this->client->request('GET', $url);

        $this->assertContains('/login', $this->client->getInternalRequest()->getUri());
        $this->assertContains('Log in!', $this->client->getResponse()->getContent());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function getAuthenticationUrl()
    {
        yield ['/admin/articles'];
        yield ['/admin/articles/new'];
        yield ['/admin/comments'];
        yield ['/admin/comments/new'];
        yield ['/admin/tags'];
        yield ['/admin/tags/new'];
        yield ['/admin/users'];
        yield ['/admin/users/new'];
        yield ['/account'];
        yield ['/articles/new'];
    }
}