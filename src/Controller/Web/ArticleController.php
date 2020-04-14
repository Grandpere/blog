<?php

namespace App\Controller\Web;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/articles", name="web_articles_")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository)
    {
        $articles = $articleRepository->findAllOrderedByNewest();
        return $this->render('web/article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/{slug}", name="show", methods={"GET"}, requirements={"slug"="[a-zA-Z0-9-]+"})
     */
    public function show(Article $article = null)
    {
        if(!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }
        return $this->render('web/article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
