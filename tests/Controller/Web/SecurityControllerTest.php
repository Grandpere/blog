<?php


namespace App\Tests\Controller\Web;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
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
     * @param $email
     * @param $password
     * @param $isbadCredential
     * @dataProvider getCredentials
     */
    public function testLogin($email, $password, $isbadCredential)
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/login');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $loginForm = $crawler->selectButton('Sign in')
            ->form([
                'email' => $email,
                'password' => $password
            ]);
        $crawler = $this->client->submit($loginForm);

        if(true === $isbadCredential) {
            $this->assertContains('/login', $this->client->getInternalRequest()->getUri());
            $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
            $this->assertSelectorTextContains('.alert.alert-danger', 'Invalid credentials'); // WARNING WITH TRANSLATION
        }
        else {
            $this->assertContains('/articles', $this->client->getInternalRequest()->getUri());
            $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
            $this->assertGreaterThan(0, $crawler->filter('article.post')->count());
        }
    }

    public function getCredentials()
    {
        // email, password, badCredential
        yield ['lorenzo@admin.com', 'lorenzo', false];
        yield ['lorenzo@admin.com', 'fake', true];
    }

    /**
     * @param $url
     * @dataProvider getAuthenticatedUrl
     */
    public function testAuthenticatedUrl($url)
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('/login', $this->client->getInternalRequest()->getUri());
    }

    public function getAuthenticatedUrl()
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
        // WARNING URL WITH DYNAMIC PARAMS ARE NOT TESTED
        // examples : /articles/{id]/edit or /account/{id}/edit or /account/{id}/edit-password
    }
}

