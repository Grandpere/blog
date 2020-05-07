<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use App\Entity\Comment;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    private $comment;

    public function setUp()
    {
        $this->comment = new Comment();
    }

    public function testAfterConstructor()
    {
        $this->assertInstanceOf(ArrayCollection::class, $this->comment->getChildrens());
        $this->assertEmpty($this->comment->getChildrens());
        $this->assertInstanceOf(\DateTime::class, $this->comment->getCreatedAt());
        $this->assertSame(true, $this->comment->getIsActive());
        $this->assertSame(0, $this->comment->getDepth());
    }

    /**
     * @param $columnName
     * @dataProvider getStringColumnName
     */
    public function testSetStringColumnWithCorrectType($columnName)
    {
        $this->comment->{'set'.$columnName}('Test '.$columnName);
        $this->assertEquals('Test '.$columnName, $this->comment->{'get'.$columnName}());

        $this->comment->{'set'.$columnName}('');
        $this->assertEquals('', $this->comment->{'get'.$columnName}());

        $this->comment->{'set'.$columnName}('Test '.$columnName.$i=1);
        $this->assertEquals('Test '.$columnName.$i, $this->comment->{'get'.$columnName}());
    }

    public function getStringColumnName()
    {
        return [
            ['content'],
            ['authorName'],
            ['authorEmail'],
            ['authorWebsite'],
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
        $this->comment->{'set'.$columnName}($columnData);
    }

    public function getStringColumnNameAndInvalidData()
    {
        return [
            ['content', null],
            ['content', []],
            ['authorName', null],
            ['authorName', []],
            ['authorEmail', null],
            ['authorEmail', []],
            ['authorWebsite', []],
        ];
    }

    public function testSetUpdatedAtWithCorrectType()
    {
        $this->assertNull($this->comment->getUpdatedAt());

        $this->comment->setUpdatedAt(new \DateTime('2020-05-05'));
        $this->assertEquals('2020-05-05', $this->comment->getUpdatedAt()->format('Y-m-d'));
    }

    public function testSetUpdatedAtWithIncorrectType()
    {
        $this->expectException('\TypeError');
        $this->comment->setUpdatedAt('2020-05-05');
    }

    public function testSetIsActiveWithCorrectType()
    {
        $this->assertEquals(true, $this->comment->getIsActive());

        $this->comment->setIsActive(false);
        $this->assertEquals(false, $this->comment->getIsActive());
    }

    public function testChildrens()
    {
        $childCommentOne =  new Comment();
        $childCommentOne->setContent('Child comment test 1');
        $childCommentTwo = new Comment();
        $childCommentTwo->setContent('Child comment test 2');

        $this->comment->addChildren($childCommentOne);
        $this->comment->addChildren($childCommentTwo);
        $childrens = $this->comment->getChildrens();

        $this->assertInstanceOf(ArrayCollection::class, $childrens);
        $this->assertInstanceOf(Comment::class, $childrens[0]);
        $this->assertSame('Child comment test 1', $childrens[0]->getContent());

        $this->assertCount(2, $childrens);
        $this->comment->removeChildren($childCommentTwo);
        $this->assertCount(1, $childrens);
    }

    public function testSetArticle()
    {
        $article = new Article();
        $article->setTitle('Article test');

        $this->comment->setArticle($article);

        $this->assertInstanceOf(Article::class, $this->comment->getArticle());
        $this->assertEquals('Article test', $this->comment->getArticle()->getTitle());
    }

    /**
     * @param $columnData
     * @dataProvider getIncorrectArticleType
     */
    public function testSetAuthorWithIncorrectType($columnData)
    {
        $this->expectException('\TypeError');
        $this->comment->setArticle($columnData);
    }

    public function getIncorrectArticleType()
    {
        return [
            ['Article test'],
            [
                ['title' => 'Article test'],
            ],
        ];
    }

    public function testDepth()
    {
        $this->comment->increaseDepth();
        $this->assertEquals(1, $this->comment->getDepth());
        $this->comment->increaseDepth();
        $this->assertEquals(2, $this->comment->getDepth());
        $this->comment->decreaseDepth();
        $this->assertEquals(1, $this->comment->getDepth());
    }

    /**
     * @param $columnData
     * @dataProvider getDepthType
     */
    public function testSetDepth($columnData)
    {
        if(!is_int($columnData) && (intval($columnData) == 0) || is_array($columnData)) {
            $this->expectException('\TypeError');
        }
        $this->comment->setDepth($columnData);
        $this->assertEquals($columnData, $this->comment->getDepth());
    }

    public function getDepthType()
    {
        return [
            [10],
            ['10'],
            ['test'],
            [null],
            [[]],
            [[1]],
        ];
    }
}
