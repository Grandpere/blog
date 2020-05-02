<?php

namespace App\Controller\Web;

use App\Form\ContactType;
use App\Utils\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    const SUBJECT = [
        'job' => 'Proposition d\'emploi',
        'bug' => 'Bug sur le site',
        'suggestions' => 'Suggestions à propos du site et/ou contenu',
        'other' => 'Autre thématique...'
    ];
    /**
     * @Route("/contact", name="web_contact", methods={"GET", "POST"})
     */
    public function index(Request $request, Mailer $mailer)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();
            $email = $form->get('email')->getData();
            $subject = $form->get('subject')->getData();
            $message = $form->get('message')->getData();

            $body = 'email/contact.html.twig';
            $context = [
                'name' => $name,
                'mail' => $email,
                'subject' => $subject,
                'message' => $message,
            ];
            $mailer->sendMessage($email, 'lorenzo.marozzo@gmail.com', 'Mail de contact du site - sujet : '.self::SUBJECT[$subject], $body, $context);

            $this->addFlash(
                'success',
                'Mail sent'
            );

            return $this->redirectToRoute('web_contact');
        }

        return $this->render('web/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
