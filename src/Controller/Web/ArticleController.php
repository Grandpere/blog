<?php

namespace App\Controller\Web;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/articles", name="web_articles_")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/{page}", name="index", methods={"GET"}, requirements={"page"="\d+"})
     */
    public function index(ArticleRepository $articleRepository, $page = 1, $maxResults = 10) // TODO: mettre ce paramètre 10 dans .ENV
    {
        $articles = $articleRepository->findAllOrderedByNewest($page);
        $pagination = array(
            'page' => $page,
            'route' => 'web_articles_index',
            'pages_count' => ceil(count($articles) / $maxResults),
            'route_params' => array()
        );
        return $this->render('web/article/index.html.twig', [
            'articles' => $articles,
            'pagination' => $pagination,
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

    /**
     * @Route("/{slug}/comments/{page}", name="comments", methods={"GET"}, requirements={"slug"="[a-zA-Z0-9-]+", "page"="\d+"})
     */
    public function getComments(Article $article = null, $page = 1, $maxResults = 10, CommentRepository $commentRepository)
    {
        if(!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }
        $comments = $commentRepository->findAllCommentsByArticleOrderedByNewest($article, $page);
        $pagination = array(
            'page' => $page,
            'route' => 'web_articles_comments',
            'pages_count' => ceil(count($comments) / $maxResults),
            'route_params' => array(
                'slug' => $article->getSlug(),
            )
        );
        return $this->render('web/article/comments.html.twig', [
            'article' => $article,
            'comments' => $comments,
            'pagination' => $pagination,
        ]);
    }
}
