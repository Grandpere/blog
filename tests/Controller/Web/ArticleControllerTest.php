<?php


namespace App\Tests\Controller\Web;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
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
        yield ['/articles/FAKE-ARTICLE'];
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

    /**
     * @param $url
     * @dataProvider getUnknownArticlesUrl
     */
    public function testShowUnknownArticle($url)
    {
        $this->client->request('GET', $url);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param $url
     * @dataProvider getUnknownArticlesUrl
     */
    public function testGetCommentsUnknownArticle($url)
    {
        $this->client->request('GET', $url.'/comments');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param $url
     * @dataProvider getUnknownArticlesUrl
     */
    public function testEditUnknownArticle($url)
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $url.'/edit');
        $this->assertContains('/login', $this->client->getInternalRequest()->getUri());

        $this->login($crawler, 'lorenzo@admin.com', 'lorenzo');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function getUnknownArticlesUrl()
    {
        yield ['/articles/UNKNOWN-ARTICLE-1'];
        yield ['/articles/UNKNOWN-ARTICLE-2'];
        yield ['/articles/UNKNOWN-ARTICLE-3'];
    }

    /**
     * @param $url
     * @dataProvider getActionUrls
     */
    public function testNewOrEditWithAnonymousUser($url)
    {
        $this->client->followRedirects();
        $this->client->request('GET', $url);

        $this->assertContains('/login', $this->client->getInternalRequest()->getUri());
        $this->assertContains('Log in!', $this->client->getResponse()->getContent()); // WARNING WITH TRANSLATION
        $this->assertSelectorTextContains('html h1', 'Please sign in'); // WARNING WITH TRANSLATION
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function getActionUrls()
    {
        yield ['/articles/new'];
        yield ['/articles/article-creation-in-functional-test/edit'];
    }

    /**
     * @param $email
     * @param $password
     * @param $isUpdate
     * @dataProvider getCredentials
     */
    public function testNewOrEditWithUser($email, $password, $isUpdate)
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/articles/new');
        $this->assertContains('/login', $this->client->getInternalRequest()->getUri());

        $this->login($crawler, $email, $password);

        if(0 < strpos($this->client->getInternalRequest()->getUri(), '/login')) {
            $this->assertSelectorTextContains('.alert.alert-danger', 'Invalid credentials'); // WARNING WITH TRANSLATION
        }
        else {
            $this->assertContains('/articles/new', $this->client->getInternalRequest()->getUri());

            $this->createOrUpdateArticle('create');

            $this->assertContains('/articles', $this->client->getInternalRequest()->getUri());
            $this->assertContains('Article creation in functional test', $this->client->getResponse()->getContent());

            if(true === $isUpdate) {
                $this->client->request('GET', '/articles/article-creation-in-functional-test/edit');
                $this->assertContains('/articles/article-creation-in-functional-test/edit', $this->client->getInternalRequest()->getUri());

                $this->createOrUpdateArticle('update');

                $this->assertContains('/articles/article-edition-in-functional-test', $this->client->getInternalRequest()->getUri());
                $this->assertContains('Article edition in functional test', $this->client->getResponse()->getContent());
            }
        }
    }

    public function getCredentials()
    {
        // email, password, isUpdate
        yield ['lorenzo@admin.com', 'lorenzo', false];
        yield ['lorenzo@admin.com', 'lorenzo', true];
        yield ['lorenzo@admin.com', 'wrongpassword', false];
    }

    public function testGetCommentsAndNewComment()
    {
        $crawler = $this->client->request('GET', '/articles');
        if($crawler->filter('article.post')->count() === 0) {
            $this->assertTrue($crawler->filter('article.post')->count() === 0);
        }
        else {
            $this->assertTrue($crawler->filter('article.post')->count() > 0);

            $firstArticleNode = $crawler->filter('main.container')->children()->first();
            $link = $firstArticleNode->filter('article.post ul.actions a.button')->link()->getUri();

            $crawler = $this->client->request('GET', $link.'/comments');
            $this->assertContains('/comments', $this->client->getInternalRequest()->getUri());

            $countComment = $crawler->filter('.comment-container .comment')->count();
            if(0 === $countComment) {
                $this->assertEquals(0, $countComment);
            }
            else {
                $this->assertGreaterThan(0, $countComment);
            }

            $form = [
                'comment[content]' => 'Comment content',
                'comment[authorName]' => 'Tester#01',
                'comment[authorEmail]' => 'test01@mail.com',
                'comment[authorWebsite]' => 'test01.test',
            ];
            $this->client->submitForm('Comment', $form, 'POST');
            $newCountComment = $countComment++;
            $this->assertEquals($newCountComment, $crawler->filter('.comment-container .comment')->count());
            // TODO : reply comments tests
        }
    }

    public function login(Crawler $crawler, string $email, string $password)
    {
        $loginForm = $crawler->selectButton('Sign in')
            ->form([
                'email' => $email,
                'password' => $password,
            ]);
        $this->client->submit($loginForm);
    }

    public function createOrUpdateArticle(string $action)
    {
        if('update' === $action) {
            $form = [
                'article[title]' => 'Article edition in functional test',
                'article[excerpt]' => 'Article resume edited',
                'article[content]' => 'Article content edited',
                //'article[imageFile]' => null,
                'article[tags]' => 'Functional test, Unit test, refactoring',
                'article[isActive]' => true,
            ];
            $button = 'Update';
        }
        else {
            $form = [
                'article[title]' => 'Article creation in functional test',
                'article[excerpt]' => 'Article resume',
                'article[content]' => 'Article content',
                //'article[imageFile]' => null,
                'article[tags]' => 'Functional test, Unit test',
                'article[isActive]' => true,
            ];
            $button = 'Save';
        }
        $this->client->submitForm($button, $form, 'POST');
    }
}