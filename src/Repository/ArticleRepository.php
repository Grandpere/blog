<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllOrderedByNewest($page = 1, $maxResults = 10)
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

        $query = $this->createQueryBuilder('a')
            ->innerJoin('a.author', 'u')
            ->addSelect('u')
            ->innerJoin('a.tags', 't')
            ->addSelect('t')
            ->orderBy('a.createdAt', 'DESC')
        ;

        // TODO : criteria with isActive for reusing this or andWhere

        $firstResults = ($page - 1) * $maxResults;
        $query
            ->setFirstResult($firstResults)
            ->setMaxResults($maxResults)
        ;

        $paginator = new Paginator($query);

        if (($paginator->count() <= $firstResults) && $page != 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas.'); // page 404, sauf pour la première page
        }

        return $paginator;
    }
}
