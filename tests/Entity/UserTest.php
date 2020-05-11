<?php

namespace App\Tests\Entity;

use App\Entity\ApiToken;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $user;

    public function setUp()
    {
        $this->user = new User();
    }

    public function testAfterConstructor()
    {
        $this->assertInstanceOf(ArrayCollection::class, $this->user->getArticles());
        $this->assertEmpty($this->user->getArticles());
        $this->assertInstanceOf(ArrayCollection::class, $this->user->getApiTokens());
        $this->assertEmpty($this->user->getApiTokens());
        $this->assertSame(false, $this->user->getIsActive());
    }

    /**
     * @param $columnName
     * @dataProvider getStringColumnName
     */
    public function testSetStringColumnWithCorrectType($columnName)
    {
        $this->user->{'set'.$columnName}('Test '.$columnName);
        $this->assertEquals('Test '.$columnName, $this->user->{'get'.$columnName}());

        $this->user->{'set'.$columnName}('');
        $this->assertEquals('', $this->user->{'get'.$columnName}());

        $this->user->{'set'.$columnName}('Test '.$columnName.$i=1);
        $this->assertEquals('Test '.$columnName.$i, $this->user->{'get'.$columnName}());
    }

    public function getStringColumnName()
    {
        return [
            ['email'],
            ['password'],
            ['firstname'],
            ['lastname'],
            ['avatar'],
            ['accountValidationToken'],
            ['resetPasswordToken'],
        ];
    }

    /**
     * @param $columnName
     * @param $columnData
     * @dataProvider getStringColumnNameAndInvalidData
     */
    public function testSetStringColumnWithIncorrectType($columnName, $columnData)
    {
        $this->expectException('\TypeError');
        $this->user->{'set'.$columnName}($columnData);
    }

    public function getStringColumnNameAndInvalidData()
    {
        return [
            ['email', null],
            ['email', []],
            ['password', null],
            ['password', []],
            ['firstname', []],
            ['lastname', []],
            ['avatar', []],
            ['accountValidationToken', []],
            ['resetPasswordToken', []],
        ];
    }

    /**
     * @param $columnName
     * @dataProvider getDateTimeColumn
     */
    public function testDateTimeColumnWithIncorrectType($columnName)
    {
        $this->expectException('\TypeError');
        $this->user->{'set'.$columnName}('01/01/2020');
    }

    /**
     * @param $columnName
     * @throws \Exception
     * @dataProvider getDateTimeColumn
     */
    public function testDateTimeColumnWithCorrectType($columnName)
    {
        $this->assertNull($this->user->{'get'.$columnName}());
        $this->user->{'set'.$columnName}(new \DateTime('01/01/2020'));
        $this->assertSame('01/01/2020', $this->user->{'get'.$columnName}()->format('d/m/Y'));
    }

    public function getDateTimeColumn()
    {
        return [
            ['LastLogin'],
            ['ValidationTokenCreatedAt'],
            ['PasswordTokenCreatedAt'],
            ['AgreedTermsAt'],
        ];
    }

    public function testUserName()
    {
        $this->assertEquals($this->user->getEmail(), $this->user->getUsername());
    }

    public function testAgreeTerms()
    {
        $this->user->agreeTerms();
        $this->assertNotNull($this->user->getAgreedTermsAt());
    }

    public function testSetIsActive()
    {
        $this->assertEquals(false, $this->user->getIsActive());

        $this->user->setIsActive(true);
        $this->assertEquals(true, $this->user->getIsActive());
    }

    public function testRoles()
    {
        $this->assertContainsOnly('string', $this->user->getRoles());
        $this->assertContains('ROLE_USER', $this->user->getRoles());

        $roles = ['ROLE_USER', 'ROLE_MODERATOR', 'ROLE_ADMIN'];
        $this->user->setRoles($roles);
        $this->assertCount(3, $this->user->getRoles());

        $roles = ['ROLE_MODERATOR', 'ROLE_ADMIN'];
        $this->user->setRoles($roles);
        $this->assertCount(3, $this->user->getRoles());

        $roles = ['ROLE_ADMIN'];
        $this->user->setRoles($roles);
        $this->assertCount(2, $this->user->getRoles());
    }

    public function testArticle()
    {
        $articleOne = new Article();
        $articleOne->setTitle('Article test 1');
        $articleTwo = new Article();
        $articleTwo->setTitle('Article test 2');
        $this->user->addArticle($articleOne);
        $this->user->addArticle($articleTwo);
        $articles = $this->user->getArticles();

        $this->assertInstanceOf(ArrayCollection::class, $articles);
        $this->assertInstanceOf(Article::class, $articles[0]);
        $this->assertSame('Article test 1', $articles[0]->getTitle());

        $this->assertCount(2, $articles);
        $this->user->removeArticle($articleTwo);
        $this->assertCount(1, $articles);
    }

    /*
    public function testApiToken()
    {
        $tokenOne = new ApiToken($this->user);
        $tokenTwo = new ApiToken($this->user);
        $this->user->addApiToken($tokenOne);
        $this->user->addApiToken($tokenTwo);
        $tokens = $this->user->getApiTokens();

        $this->assertInstanceOf(ArrayCollection::class, $tokens);
        $this->assertInstanceOf(ApiToken::class, $tokens[0]);

        dump($this->user->getApiTokens());
        $this->assertCount(2, $tokens);
        $this->user->removeApiToken($tokenTwo); //TYPEERROR TODO:
        $this->assertCount(1, $tokens);
    }*/
}
