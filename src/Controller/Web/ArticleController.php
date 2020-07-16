<?php

namespace App\Controller\Web;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\View;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\LikeRepository;
use App\Repository\ViewRepository;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $articles = $articleRepository->findAllActiveOrderedByNewest($page, $maxArticlePerPage);
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
    public function show(string $slug, ArticleRepository $articleRepository, Request $request, ViewRepository $viewRepository)
    {
        $article = $articleRepository->findOneActiveBySlug($slug);
        if(!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }

        //$clientIp = $request->getClientIps();
        $clientIp = $request->server->get('REMOTE_ADDR');
        $userAgent = $request->headers->get('User-Agent');
        $defaultOptions = [
            'clientIp' => $clientIp,
            'userAgent' => $userAgent,
            'article' => $article
        ];
        $user = $this->getUser();
        $anonymousAlreadyViewed = $viewRepository->findOneBy(['clientIp' => $clientIp, 'userAgent' => $userAgent, 'article' => $article]);

        if(!$user && !$anonymousAlreadyViewed) { // anonymous user : never viewed as anonymous
            $this->createObjectView($defaultOptions);
        }
        elseif($user) { // logged user
            if(!$anonymousAlreadyViewed) { // never viewed as anonymous
                $this->createObjectView($defaultOptions, $user);
            }
            else { // viewed as anonymous
                $anonymousAlreadyViewed->setUserLogged($user);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
            }

        }

        return $this->render('web/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    private function createObjectView(array $options, $user = null) {
        if(!array_key_exists('clientIp', $options)) {
            throw new \InvalidArgumentException('Missing array key clientIp');
        }
        if(!array_key_exists('userAgent', $options)) {
            throw new \InvalidArgumentException('Missing array key userAgent');
        }
        if(!array_key_exists('article', $options)) {
            throw new \InvalidArgumentException('Missing array key article');
        }
        $view = new View();
        $view->setClientIp($options['clientIp']);
        $view->setUserAgent($options['userAgent']);
        $view->setArticle($options['article']);

        if($user) {
            $view->setUserLogged($user);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($view);
        $entityManager->flush();
    }

    /**
     * @Route("/{slug}/report", name="report", methods={"POST"}, requirements={"slug"="[a-zA-Z0-9-]+"})
     */
    public function report(Article $article = null, Request $request)
    {
        if(!$article) {
            return new JsonResponse(['code' => 404, 'message' => 'Article Not Found'], 404);
        }

        if ($this->isCsrfTokenValid('article-report'.$article->getId(), $request->request->get('_token'))) {
            if(!$article->getIsReported()) {
                $article->SetIsReported(true);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
            }
            return new JsonResponse(['code' => 200, 'message' => 'Article reported'], 200);
        }

        return new JsonResponse(['code' => 400, 'message' => 'Invalid Csrf Token'], 400);
    }

    /**
     * @Route("/comments/{commentId}/report", name="comments_report", methods={"POST"}, requirements={"slug"="[a-zA-Z0-9-]+", "commentId"="\d+"})
     */
    public function reportComment($commentId, CommentRepository $commentRepository, Request $request)
    {
        $comment = $commentRepository->find($commentId);
        if(!$comment) {
            return new JsonResponse(['code' => 404, 'message' => 'Comment Not Found'], 404);
        }

        if ($this->isCsrfTokenValid('comment-report'.$comment->getId(), $request->request->get('_token'))) {
            if(!$comment->getIsReported()) {
                $comment->SetIsReported(true);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
            }
            return new JsonResponse(['code' => 200, 'message' => 'Comment reported'], 200);
        }

        return new JsonResponse(['code' => 400, 'message' => 'Invalid Csrf Token'], 400);
    }

    /**
     * @Route("/{slug}/like", name="like", methods={"POST"}, requirements={"slug"="[a-zA-Z0-9-]+"})
     */
    public function like(Article $article = null, Request $request, LikeRepository $likeRepository)
    {
        if(!$article) {
            return new JsonResponse(['code' => 404, 'message' => 'Article Not Found'], 404);
        }

        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(['code' => 403, 'message' => 'Unauthorized'], 403);
        }

        $entityManager = $this->getDoctrine()->getManager();

        if ($this->isCsrfTokenValid('article-like'.$article->getId(), $request->request->get('_token'))) {
            if($article->isLikedByUser($user)) {
                $like = $likeRepository->findOneBy(['user' => $user, 'article' => $article]);
                $entityManager->remove($like);
                $entityManager->flush();

                return new JsonResponse(['code' => 200, 'message' => 'Unliked article', 'likes' => $likeRepository->count(['article' => $article])], 200);
            }

            $like = new Like();
            $like->setUser($user);
            $like->setArticle($article);

            $entityManager->persist($like);
            $entityManager->flush();

            return new JsonResponse(['code' => 200, 'message' => 'Liked article', 'likes' => $likeRepository->count(['article' => $article])], 200);
        }

        return new JsonResponse(['code' => 400, 'message' => 'Invalid Csrf Token'], 400);
    }

    /**
     * @Route("/{slug}/comments/{page}", name="comments", methods={"GET", "POST"}, requirements={"slug"="[a-zA-Z0-9-]+", "page"="\d+"})
     */
    public function comments(Request $request, string $slug, ArticleRepository $articleRepository, CommentRepository $commentRepository, $page = 1)
    {
        $article = $articleRepository->findOneActiveBySlug($slug);
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

        $comments = $article->getActiveAndNotModerateComments();
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
            'results' => $results,
            'form' => $form->createView(),
        ]);
    }
}
