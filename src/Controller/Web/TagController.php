<?php

namespace App\Controller\Web;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TagController extends AbstractController
{
    /**
     * @Route("/web/tag", name="web_tag")
     */
    public function index()
    {
        return $this->render('web/tag/index.html.twig', [
            'controller_name' => 'TagController',
        ]);
    }
}
