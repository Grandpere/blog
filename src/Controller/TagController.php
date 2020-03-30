<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Utils\Slugger;
use App\Repository\TagRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/tags", name="api_tags_")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="read", methods={"GET"})
     *
     * @param TagRepository $tagRepository
     * @param SerializerInterface $serializer
     * @return void
     */
    public function read(TagRepository $tagRepository, SerializerInterface $serializer)
    {
        $tags = $tagRepository->findByIsActive(1);
        $tagsJson = $serializer->serialize($tags, 'json', ['groups' => 'tag_read']);

        return JsonResponse::fromJsonString($tagsJson);
    }

    /**
     * @Route("/{id}", name="readOne", methods={"GET"})
     *
     * @param int $id
     * @param TagRepository $tagRepository
     * @param SerializerInterface $serializer
     * @return void
     */
    public function readOne($id, TagRepository $tagRepository, SerializerInterface $serializer)
    {
        $tag = $tagRepository->find($id);
        if(!$tag) {
            return $this->json($data = [
                'status' => 404,
                'message' => 'Tag non trouvé'
            ], $status = 404);
        }

        $tagJson = $serializer->serialize($tag, 'json', ['groups' => 'tag_readOne']);

        return JsonResponse::fromJsonString($tagJson);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return void
     */
    public function create(Request $request)
    {
        // TODO: validations des informations (comme injections de script, etc...)    
        $content = $request->getContent(); 
        $data = json_decode($content, true);

        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->submit($data);
        
        if($form->isSubmitted() && $form->isValid()) {
            $tag->setSlug(Slugger::slugify($tag->getTitle()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            $responseData = [
                'status' => 201,
                'message' => 'Tag crée',
                'link' => $this->generateUrl('api_tags_readOne', array('id' => $tag->getId()), UrlGeneratorInterface::ABSOLUTE_URL)
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
     * @Route("/{id}", name="update", methods={"PUT", "PATCH"})
     *
     * @param int $id
     * @param TagRepository $tagRepository
     * @param Request $request
     * @return void
     */
    public function update($id, TagRepository $tagRepository, Request $request)
    {
        $tag = $tagRepository->find($id);
        if(!$tag) {
            return $this->json($data = [
                'status' => 404,
                'message' => 'Tag non trouvé'
            ], $status = 404);
        }
        $content = $request->getContent(); 
        $data = json_decode($content, true);

        $form = $this->createForm(TagType::class, $tag);
        $clearMissing=  $request->getMethod() != 'PATCH'; //if patch, $clearMissing = false else $clearMissing = true
        $form->submit($data, $clearMissing);

        if($form->isSubmitted() && $form->isValid()) {
            $tag->setSlug(Slugger::slugify($tag->getTitle()));
            $tag->setUpdatedAt(new \Datetime());
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
     * @Route("/{id}", name="delete", methods={"DELETE"})
     *
     * @param App\Entity\Tag $tag
     * @param SerializerInterface $serializer
     * @return void
     */
    public function delete(Tag $tag = null, SerializerInterface $serializer)
    {
        if(!$tag) {
            return $this->json($data = [
                'status' => 404,
                'message' => 'Tag non trouvé'
            ], $status = 404);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($tag);
        $em->flush();

        return $this->json($data = [
            'status' => 200,
            'message' => 'Tag supprimé'
        ], $status = 200);
    }
}