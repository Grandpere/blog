<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    private $tag;

    public function setUp()
    {
      $this->tag = new Tag();
    }

    public function testAfterConstructor()
    {
        $this->assertInstanceOf(ArrayCollection::class, $this->tag->getArticles());
        $this->assertEmpty($this->tag->getArticles());
        $this->assertInstanceOf(\DateTime::class, $this->tag->getCreatedAt());
        $this->assertSame(true, $this->tag->getIsActive());
    }

    /**
     * @param $columnName
     * @dataProvider getStringColumnName
     */
    public function testSetStringColumnWithCorrectType($columnName)
    {
        $this->tag->{'set'.$columnName}('Test '.$columnName);
        $this->assertEquals('Test '.$columnName, $this->tag->{'get'.$columnName}());

        $this->tag->{'set'.$columnName}('');
        $this->assertEquals('', $this->tag->{'get'.$columnName}());

        $this->tag->{'set'.$columnName}('Test '.$columnName.$i=1);
        $this->assertEquals('Test '.$columnName.$i, $this->tag->{'get'.$columnName}());
    }

    public function getStringColumnName()
    {
        return [
            ['title'],
            ['slug'],
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
        $this->tag->{'set'.$columnName}($columnData);
    }

    public function getStringColumnNameAndInvalidData()
    {
        return [
            ['title', null],
            ['title', []],
            ['slug', null],
            ['slug', []],
        ];
    }

    public function testSetUpdatedAtWithCorrectType()
    {
        $this->assertNull($this->tag->getUpdatedAt());

        $this->tag->setUpdatedAt(new \DateTime('2020-05-05'));
        $this->assertEquals('2020-05-05', $this->tag->getUpdatedAt()->format('Y-m-d'));
    }

    public function testSetUpdatedAtWithIncorrectType()
    {
        $this->expectException('\TypeError');
        $this->tag->setUpdatedAt('2020-05-05');
    }

    public function testSetIsActiveWithCorrectType()
    {
        $this->assertEquals(true, $this->tag->getIsActive());

        $this->tag->setIsActive(false);
        $this->assertEquals(false, $this->tag->getIsActive());
    }

    public function testArticles()
    {
        $articleOne =  new Article();
        $articleOne->setTitle('Article test 1');
        $articleTwo = new Article();
        $articleTwo->setTitle('Article test 2');
        $this->tag->addArticle($articleOne);
        $this->tag->addArticle($articleTwo);
        $articles = $this->tag->getArticles();

        $this->assertInstanceOf(ArrayCollection::class, $articles);
        $this->assertInstanceOf(Article::class, $articles[0]);
        $this->assertSame('Article test 1', $articles[0]->getTitle());

        $this->assertCount(2, $articles);
        $this->tag->removeArticle($articleTwo);
        $this->assertCount(1, $articles);
    }

    public function testToString()
    {
        $this->tag->setTitle('Tag test');

        $this->assertEquals('Tag test', $this->tag);
    }
}
