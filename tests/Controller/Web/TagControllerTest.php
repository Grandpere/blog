<?php

namespace App\Tests\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TagControllerTest extends WebTestCase
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
     * @dataProvider getTags
     */
    public function testShowArticlesByTag($url)
    {
        $crawler = $this->client->request('GET', $url);
        if(Response::HTTP_NOT_FOUND === $this->client->getResponse()->getStatusCode()) {
            $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        }
        else {
            $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
            $countArticles = $crawler->filter('article.post')->count();
            if(0 === $countArticles) {
                $this->assertTrue(0 === $countArticles);
            }
            else {
                $this->assertTrue($crawler->filter('article.post')->count() > 0);
            }
        }

    }

    public function getTags()
    {
        yield['/tags/slug-php'];
        yield['/tags/slug-symfony'];
        yield['/tags/slug-javascript'];
        yield['/tags/slug-mysql'];
        yield['/tags/slug-postgresql'];
        yield['/tags/slug-twig'];
        yield['/tags/slug-react'];
        yield['/tags/slug-nodejs'];
        yield['/tags/slug-angular'];
        yield['/tags/slug-vuejs'];
        yield['/tags/slug-html'];
        yield['/tags/slug-css'];
        yield['/tags/slug-UNKNOWN'];
        // TODO : none tag articles
    }
}
