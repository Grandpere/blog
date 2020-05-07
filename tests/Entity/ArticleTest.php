<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private $article;

    public function setUp()
    {
        $this->article = new Article();
    }

    public function testAfterConstructor()
    {
        $this->assertInstanceOf(ArrayCollection::class, $this->article->getTags());
        $this->assertEmpty($this->article->getTags());
        $this->assertInstanceOf(\DateTime::class, $this->article->getCreatedAt());
        $this->assertSame(false, $this->article->getIsActive());
        $this->assertInstanceOf(ArrayCollection::class, $this->article->getComments());
    }

    /**
     * @param $columnName
     * @dataProvider getStringColumnName
     */
    public function testSetStringColumnWithCorrectType($columnName)
    {
        $this->article->{'set'.$columnName}('Test '.$columnName);
        $this->assertEquals('Test '.$columnName, $this->article->{'get'.$columnName}());

        $this->article->{'set'.$columnName}('');
        $this->assertEquals('', $this->article->{'get'.$columnName}());

        $this->article->{'set'.$columnName}('Test '.$columnName.$i=1);
        $this->assertEquals('Test '.$columnName.$i, $this->article->{'get'.$columnName}());
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
     * @param $columnName
     * @param $columnData
     * @dataProvider getStringColumnNameAndInvalidData
     */
    public function testSetStringColumnWithIncorrectType($columnName, $columnData)
    {
        $this->expectException('\TypeError');
        $this->article->{'set'.$columnName}($columnData);
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

    public function testSetUpdatedAtWithCorrectType()
    {
        $this->assertNull($this->article->getUpdatedAt());

        $this->article->setUpdatedAt(new \DateTime('2020-05-05'));
        $this->assertEquals('2020-05-05', $this->article->getUpdatedAt()->format('Y-m-d'));
    }

    public function testSetUpdatedAtWithIncorrectType()
    {
        $this->expectException('\TypeError');
        $this->article->setUpdatedAt('2020-05-05');
    }

    public function testSetIsActiveWithCorrectType()
    {
        $this->assertEquals(false, $this->article->getIsActive());

        $this->article->setIsActive(true);
        $this->assertEquals(true, $this->article->getIsActive());
    }

    public function testSetAuthor()
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $this->article->setAuthor($user);

        $this->assertInstanceOf(User::class, $this->article->getAuthor());
        $this->assertEquals('test@test.com', $this->article->getAuthor()->getEmail());
    }

    public function testSetAuthorWithIncorrectType()
    {
        $this->expectException('\TypeError');
        $this->article->setAuthor('user');
    }

    public function testTags()
    {
        $tagOne = new Tag();
        $tagOne->setTitle('tag1');
        $tagTwo = new Tag();
        $tagTwo->setTitle('tag2');
        $this->article->addTag($tagOne);
        $this->article->addTag($tagTwo);
        $tags = $this->article->getTags();

        $this->assertInstanceOf(ArrayCollection::class, $tags);
        $this->assertInstanceOf(Tag::class, $tags[0]);
        $this->assertSame('tag1', $tags[0]->getTitle());

        $this->assertCount(2, $tags);
        $this->article->removeTag($tagTwo);
        $this->assertCount(1, $tags);
    }

    public function testComments()
    {
        $commentOne = new Comment();
        $commentOne->setContent('comment test 1');
        $commentTwo = new Comment();
        $commentTwo->setContent('comment test 2');
        $this->article->addComment($commentOne);
        $this->article->addComment($commentTwo);
        $comments = $this->article->getComments();

        $this->assertInstanceOf(ArrayCollection::class, $comments);
        $this->assertInstanceOf(Comment::class, $comments[0]);
        $this->assertSame('comment test 1', $comments[0]->getContent());

        $this->assertCount(2, $comments);
        $this->article->removeComment($commentTwo);
        $this->assertCount(1, $comments);
    }

    /**
     * @param $dataColumn
     * @param $action
     * @dataProvider getInvalidTagAndCommentType
     */
    public function testCommentsOrTagsWithIncorrectType($columnData, $columnName, $action)
    {
        $this->expectException('\TypeError');

        switch ($action) {
            case 'add' :
                $this->article->{'add'.$columnName}($columnData);
                break;
            case 'remove' :
                $this->article->{'remove'.$columnName}($columnData);
                break;
        }
    }

    public function getInvalidTagAndCommentType()
    {
        return [
            ['test', 'Tag', 'add'],
            ['test', 'Tag', 'remove'],
            ['test', 'Comment', 'add'],
            ['test', 'Comment', 'remove'],
        ];
    }

    public function testImageFile()
    {
        $this->markTestSkipped('imageFile are managed by VichUploaderBundle');
    }
}
