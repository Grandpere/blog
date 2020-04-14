<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllCommentsByArticleOrderedByNewest($article, $page = 1, $maxResults = 10)
    {
        if(!is_numeric($page)) {
            throw new InvalidArgumentException('$page argument are incorrect (value : '.$page. ').');
        }

        if($page < 1) {
            throw new NotFoundHttpException('This page doesn\'t exist');
        }

        if(!is_numeric($maxResults)) {
            throw new InvalidArgumentException('$maxResults argument are incorrect (value : '.$maxResults. ').');
        }

        $query = $this->createQueryBuilder('c')
            ->innerJoin('c.article', 'a')
            ->addSelect('a')
            ->innerJoin('c.author', 'u')
            ->addSelect('u')
            ->andWhere('c.article = :article')
            ->setParameter('article', $article)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $maxResults)
            ->setMaxResults($maxResults)
        ;
        return new Paginator($query);
    }
}
