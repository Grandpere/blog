<?php

namespace App\Controller\Web;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
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
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request)
    {
        $article = new Article();

        $this->denyAccessUnlessGranted('create', $article);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $article->setAuthor($this->getUser());
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Article Created'
            );

            return $this->redirectToRoute('web_articles_index');
        }

        return $this->render('web/article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"}, requirements={"slug"="[a-zA-Z0-9-]+"})
     */
    public function edit(Request $request, Article $article = null)
    {
        if(!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }

        $this->denyAccessUnlessGranted('edit', $article);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUpdatedAt(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Updated Article'
            );

            return $this->redirectToRoute('web_articles_show', ['slug' => $article->getSlug()]);
        }

        return $this->render('web/article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="show", methods={"GET"}, requirements={"slug"="[a-zA-Z0-9-]+"})
     */
    public function show(string $slug, ArticleRepository $articleRepository)
    {
        $article = $articleRepository->findOneBySlugWithTags($slug);
        if(!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }

        $article->incrementViews();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $this->render('web/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{slug}/comments/{page}", name="comments", methods={"GET", "POST"}, requirements={"slug"="[a-zA-Z0-9-]+", "page"="\d+"})
     */
    public function comments(Request $request, string $slug, ArticleRepository $articleRepository, CommentRepository $commentRepository, $page = 1)
    {
        $article = $articleRepository->findOneBySlugWithTags($slug);
        if(!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setArticle($article);

            if($request->request->has('parentId') && "null" !== $request->request->get('parentId')) {
                $parentId = $request->request->get('parentId');
                $parent = $commentRepository->find($parentId);
                if($parent && $parent->getDepth() < 2) { // max depth : 2, si la condition n'est pas respecté le commentaire ne sera pas ajouté en tant qu'enfant
                    $comment->setParent($parent);
                    $comment->setDepth($parent->getDepth())->increaseDepth();
                }
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Comment Created'
            );

            return $this->redirectToRoute('web_articles_comments', ['slug' => $article->getSlug(), 'page' => $page]);
        }

        /*
         // OLD METHOD WITH PAGINATION BUT TOO MANY REQUESTS FOR CHILD COMMENTS
        $maxCommentPerPage = $this->getParameter('max_comment_per_page');

        $comments = $commentRepository->findAllByArticleOrderedByNewest($article, $page, $maxCommentPerPage);
        //$commentsCount = $commentRepository->countActiveComments($article);

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
            //'commentsCount' => $commentsCount,
            'comments' => $comments,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
        */

        $comments = $commentRepository->findAllActiveByArticleOrderedByNewest($article);
        $results = [];
        // limitation a une profondeur de 2 donc parent -> enfant -> petit-enfant
        foreach ($comments as $comment) {
            if(0 === $comment->getDepth()) {
                // commentaire parent
                $results[$comment->getId()]['comment'] = $comment;
            }
            elseif(1 === $comment->getDepth()) {
                // commentaire enfant d'un commentaire parent
                $commentParent = $comment->getParent();
                $results[$commentParent->getId()]['childrens'][$comment->getId()]['comment'] = $comment;
            }
            elseif(2 === $comment->getDepth()) {
                // commentaire enfant d'un commentaire déja enfant
                $commentParent = $comment->getParent();
                $commentElderParent = $commentParent->getParent();
                $results[$commentElderParent->getId()]['childrens'][$commentParent->getId()]['childrens'][$comment->getId()]['comment'] = $comment;
            }
        }

        return $this->render('web/article/comments.html.twig', [
            'article' => $article,
            'comments' => $comments,
            'results' => $results,
            'form' => $form->createView(),
        ]);
    }
}
