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

    /**
     * @param $email
     * @param $password
     * @dataProvider getCredentials
     */
    public function testNewArticleWithUser($email, $password)
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/articles/new');
        $this->assertContains('/login', $this->client->getInternalRequest()->getUri());

        $loginForm = $crawler->selectButton('Sign in')
            ->form([
                'email' => $email,
                'password' => $password,
        ]);
        $this->client->submit($loginForm);
        if(0 < strpos($this->client->getInternalRequest()->getUri(), '/login')) {
            $this->assertSelectorTextContains('.alert.alert-danger', 'Invalid credentials'); // WARNING WITH TRANSLATION
        }
        else {
            $this->assertContains('/articles/new', $this->client->getInternalRequest()->getUri());

            $form = [
                'article[title]' => 'Article creation in functional test',
                'article[excerpt]' => 'Article resume',
                'article[content]' => 'Article content',
                //'article[imageFile]' => null,
                'article[tags]' => 'Functional test, Unit test',
                'article[isActive]' => true,
            ];
            $this->client->submitForm('Save', $form, 'POST');

            $this->assertContains('/articles', $this->client->getInternalRequest()->getUri());
            $this->assertContains('Article creation in functional test', $this->client->getResponse()->getContent());
        }
    }

    public function getCredentials()
    {
        // email, password
        yield ['lorenzo@admin.com', 'lorenzo'];
        yield ['lorenzo@admin.com', 'wrongpassword'];
    }
}