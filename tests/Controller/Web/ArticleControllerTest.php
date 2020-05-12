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
        yield ['/articles/FAKE-URL'];
    }

    public function testHomepageWithFinalBackslashWithoutRedirection()
    {
        $this->client->request('GET', '/articles/');
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse()->getStatusCode());
    }

    public function testHomepageWithFinalBackslashWithRedirection()
    {
        $this->client->followRedirects();
        $this->client->request('GET', '/articles/');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testHomepage()
    {
        $crawler = $this->client->request('GET', '/articles');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('article.post')->count());
    }

    public function testNewArticleWithAnonymousUser()
    {
        $this->client->followRedirects();
        $this->client->request('GET', '/articles/new');

        $this->assertContains('/login', $this->client->getInternalRequest()->getUri());
        $this->assertContains('Log in!', $this->client->getResponse()->getContent()); // WARNING WITH TRANSLATION
        $this->assertSelectorTextContains('html h1', 'Please sign in'); // WARNING WITH TRANSLATION
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testNewArticleWithUser()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/articles/new');
        $this->assertContains('/login', $this->client->getInternalRequest()->getUri());

        $loginForm = $crawler->selectButton('Sign in')
            ->form([
                'email' => 'lorenzo@admin.com', // TODO: submit incorrect email or password
                'password' => 'lorenzo',
        ]);
        $crawler = $this->client->submit($loginForm);
        $this->assertContains('/articles/new', $this->client->getInternalRequest()->getUri());

        $form = [
            'article[title]' => 'Article creation in functional test',
            'article[excerpt]' => 'Article resume',
            'article[content]' => 'Article content',
            //'article[imageFile]' => null,
            'article[tags]' => 'Functional test, Unit test',
            'article[isActive]' => true,
        ];
        $crawler = $this->client->submitForm('Save', $form, 'POST');

        $this->assertContains('/articles', $this->client->getInternalRequest()->getUri());
        $this->assertContains('Article creation in functional test', $this->client->getResponse()->getContent());
        dump($this->client);
    }

    public function getAuthenticationUrl()
    {
        /*
        yield ['/admin/articles'];
        yield ['/admin/articles/new'];
        yield ['/admin/comments'];
        yield ['/admin/comments/new'];
        yield ['/admin/tags'];
        yield ['/admin/tags/new'];
        yield ['/admin/users'];
        yield ['/admin/users/new'];
        yield ['/account'];
        */
    }
}