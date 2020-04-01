<?php

namespace App\Controller\Api\v1;

use App\Utils\Slugger;
use App\Entity\Article;
use App\Utils\Excerpter;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/articles", name="api_articles_")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="read", methods={"GET"})
     */
    public function read(ArticleRepository $articleRepository, SerializerInterface $serializer)
    {
        $articles = $articleRepository->findByIsActive(1);
        $articlesJson = $serializer->serialize($articles, 'json', ['groups' => 'article_read']);

        return JsonResponse::fromJsonString($articlesJson);
    }

    /**
     * @Route("/{id}", name="readOne", methods={"GET"}, requirements = {"id"="\d+"})
     */
    public function readOne($id, ArticleRepository $articleRepository, SerializerInterface $serializer)
    {
        $article = $articleRepository->find($id);
        if(!$article) {
            return $this->json($data = [
                'status' => 404,
                'message' => 'Article non trouvé'
            ], $status = 404);
        }

        $articleJson = $serializer->serialize($article, 'json', ['groups' => 'article_readOne']);

        return JsonResponse::fromJsonString($articleJson);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer)
    {
        // TODO: gestion des erreurs et validations des informations
        $content = $request->getContent();
        $data = json_decode($content, true); // 2nd paramètre à true pour avoir un tableau associatif
        //$article = $serializer->deserialize($content, 'App\Entity\Article', 'json');
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid()) {
            $article->setSlug(Slugger::slugify($article->getTitle()));
            $article->setExcerpt(Excerpter::excerptify($article->getContent()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $responseData = [
                'status' => 201,
                'message' => 'Article crée',
                'link' => $this->generateUrl('api_articles_readOne', array('id' => $article->getId()), UrlGeneratorInterface::ABSOLUTE_URL)
            ];
            $response = new JsonResponse($responseData);
            $response->headers->set('Location', $responseData['link']);
            $response->setStatusCode($responseData['status'], $responseData['message']);
    
            return $response;
        }
        return $this->json($data = [
            'status' => 400,
            'message' => (string) $form->getErrors(true, false)
        ], $status = 400);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT", "PATCH"}, requirements = {"id"="\d+"})
     */
    public function update($id, ArticleRepository $articleRepository, Request $request)
    {
        $article = $articleRepository->find($id);
        if(!$article) {
            return $this->json($data = [
                'status' => 404,
                'message' => 'Article non trouvé'
            ], $status = 404);
        }
        $content = $request->getContent(); 
        $data = json_decode($content, true);

        $form = $this->createForm(ArticleType::class, $article);
        $clearMissing=  $request->getMethod() != 'PATCH'; //if patch, $clearMissing = false else $clearMissing = true
        $form->submit($data, $clearMissing);

        if($form->isSubmitted() && $form->isValid()) {
            $article->setSlug(Slugger::slugify($article->getTitle()));
            $article->setUpdatedAt(new \Datetime());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
    
            return $this->json($data = [
                'status' => 200,
                'message' => 'Tag mis à jour'
            ], $status = 200);
        }
        return $this->json($data = [
            'status' => 400,
            'message' => (string) $form->getErrors(true, false)
        ], $status = 400);

        // 204 No Content
    }

        /**
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements = {"id"="\d+"})
     */
    public function delete(Article $article = null, SerializerInterface $serializer)
    {
        if(!$article) {
            return $this->json($data = [
                'status' => 404,
                'message' => 'Article non trouvé'
            ], $status = 404);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();

        return $this->json($data = [
            'status' => 200,
            'message' => 'Article supprimé'
        ], $status = 200);
    }
}