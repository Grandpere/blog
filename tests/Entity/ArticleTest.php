<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testArticleAfterConstructor()
    {
        $article = new Article();

        $this->assertInstanceOf(ArrayCollection::class, $article->getTags());
        $this->assertEmpty($article->getTags());
        $this->assertInstanceOf(\DateTime::class, $article->getCreatedAt());
        $this->assertIsBool(false);
        $this->assertInstanceOf(ArrayCollection::class, $article->getComments());
    }

    /**
     * @param $columnName
     * @dataProvider getStringColumnName
     */
    public function testSetStringColumnWithCorrectType($columnName)
    {
        $article = new Article();

        $article->{'set'.$columnName}('Test '.$columnName);
        $this->assertEquals('Test '.$columnName, $article->{'get'.$columnName}());

        $article->{'set'.$columnName}('');
        $this->assertEquals('', $article->{'get'.$columnName}());

        $article->{'set'.$columnName}('Test '.$columnName.$i=1);
        $this->assertEquals('Test '.$columnName.$i, $article->{'get'.$columnName}());
    }

    public function getStringColumnName()
    {
        return [
            ['title'],
            ['slug'],
            ['content'],
            ['excerpt'],
            ['coverImage'],
        ];
    }

    /**
     * @dataProvider getStringColumnNameAndInvalidData
     * @param $columnName
     * @param $columnData
     */
    public function testSetStringColumnWithIncorrectType($columnName, $columnData)
    {
        $article = new Article();

        $this->expectException('\TypeError');
        $article->{'set'.$columnName}($columnData);
    }

    public function getStringColumnNameAndInvalidData()
    {
        return [
            ['title', null],
            ['title', []],
            ['slug', null],
            ['slug', []],
            ['content', null],
            ['content', []],
            ['excerpt', null],
            ['excerpt', []],
            ['coverImage', []],
        ];
    }

    public function testSetUpdatedAt()
    {
        $article = new Article();

        $this->assertNull($article->getUpdatedAt());

        $article->setUpdatedAt(new \DateTime('2020-05-05'));
        $this->assertEquals('2020-05-05', $article->getUpdatedAt()->format('Y-m-d'));

        // TODO : incorrect type or data
    }

    public function testSetIsActiveArticle()
    {
        $article = new Article();

        $this->assertIsBool(false);
        $this->assertEquals(false, $article->getIsActive());

        $article->setIsActive(true);
        $this->assertIsBool(true);
        $this->assertEquals(true, $article->getIsActive());

        // TODO : incorrect type or data
    }

    public function testImageFile()
    {
        $this->markTestIncomplete('TODO');
    }

    public function testSetAuthor()
    {
        $this->markTestIncomplete('TODO');
    }

    public function testTags()
    {
        $this->markTestIncomplete('TODO');
    }

    public function testComments()
    {
        $this->markTestIncomplete('TODO');
    }
}
