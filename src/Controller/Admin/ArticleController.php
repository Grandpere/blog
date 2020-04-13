<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller\Admin
 * @Route("/admin/article", name="admin_article_")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('admin/article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    /**
     * @Route("/create", name="new")
     * @IsGranted("ROLE_ADMIN_ARTICLE")
     */
    public function new()
    {
        $form = $this->createForm(ArticleType::class);

        return $this->render('admin/article/new.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit")
     * @IsGranted("MANAGE", subject="article")
     */
    public function edit(Article $article)
    {
        // if Article $article are argument, IsGranted annotation with subject don't works
        // this only works because we used the feature that automatically queries for the Article object and passes it as an argument
        // Without this use : $this->denyAccessUnlessGranted('MANAGE', $article);
        // $this->denyAccessUnlessGranted('MANAGE', $article);

        dd($article);

        //return $this->json(['ok']);
    }
}
