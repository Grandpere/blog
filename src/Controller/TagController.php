<?php

namespace App\Controller;

use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    /**
     * @Route("/api/v1/tags.json", name="tag-autocomplete", methods={"GET"})
     */
    public function index(TagRepository $tagRepository, Request $request)
    {
        if($request->query->get('q')) {
            $tags = $tagRepository->findAllMatching($request->query->get('q'));
        }
        else {
            $tags = $tagRepository->findAll();
        }

        return $this->json($tags,200, [], ['groups' => ['autocomplete']]);
    }
}
