<?php

namespace App\Controller\Web;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(ArticleRepository $articleRepository, $page = 1)
    {
        $maxArticlePerPage = $this->getParameter('max_article_per_page');

        $articles = $articleRepository->findAllOrderedByNewest($page, $maxArticlePerPage);
        $pagination = array(
            'page' => $page,
            'route' => 'web_articles_index',
            'pages_count' => max(ceil(count($articles) / $maxArticlePerPage), 1),
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
     * @Route("/{slug}/comments/{page}", name="comments", methods={"GET", "POST"}, requirements={"slug"="[a-zA-Z0-9-]+", "page"="\d+"})
     */
    public function comments(Request $request, Article $article = null, CommentRepository $commentRepository, $page = 1)
    {
        if(!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $comment->setArticle($article);

            $em->persist($comment);
            $em->flush();

            $this->addFlash(
                'success',
                'Enregistrement effectué'
            );

            return $this->redirectToRoute('web_articles_comments', ['slug' => $article->getSlug(), 'page' => $page]);
        }

        $maxCommentPerPage = $this->getParameter('max_comment_per_page');

        $comments = $commentRepository->findAllCommentsByArticleOrderedByNewest($article, $page, $maxCommentPerPage);
        $pagination = array(
            'page' => $page,
            'route' => 'web_articles_comments',
            'pages_count' => max(ceil(count($comments) / $maxCommentPerPage), 1),
            'route_params' => array(
                'slug' => $article->getSlug(),
            )
        );
        return $this->render('web/article/comments.html.twig', [
            'article' => $article,
            'comments' => $comments,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }
}
