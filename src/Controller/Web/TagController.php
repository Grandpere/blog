<?php

namespace App\Controller\Web;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/tags", name="web_tags_")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/{slug}", name="show", methods={"GET"}, requirements={"slug"="[a-zA-Z0-9-]+"})
     */
    public function show(Tag $tag = null)
    {
        if(!$tag) {
            throw $this->createNotFoundException('Tag introuvable');
        }
        return $this->render('web/tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }
}
