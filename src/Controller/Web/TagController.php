<?php

namespace App\Controller\Web;

use App\Entity\Tag;
use App\Repository\ArticleRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/tags", name="web_tags_")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/{slug}/{page}", name="show", methods={"GET"}, requirements={"slug"="[a-zA-Z0-9-]+", "page"="\d+"})
     */
    public function show(Tag $tag = null, ArticleRepository $articleRepository, $page = 1)
    {
        if(!$tag) {
            throw $this->createNotFoundException('Tag introuvable');
        }

        $maxArticlePerPage = $this->getParameter('max_article_per_page');

        $articles = $articleRepository->findAllActiveByTagOrderedByNewest($tag, $page, $maxArticlePerPage);
        $pagination = array(
            'page' => $page,
            'route' => 'web_tags_show',
            'pages_count' => max(ceil(count($articles) / $maxArticlePerPage), 1),
            'route_params' => array(
                'slug' => $tag->getSlug(),
            )
        );

        return $this->render('web/tag/show.html.twig', [
            'tag' => $tag,
            'articles' => $articles,
            'pagination' => $pagination,
        ]);
    }
}
