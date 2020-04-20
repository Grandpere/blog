<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Utils\DelayTokenVerificator;
use App\Utils\Gravatar;
use App\Utils\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register", methods={"GET", "POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Gravatar $gravatar, TokenGeneratorInterface $tokenGenerator, Mailer $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setAvatar($gravatar->getGravatar($user->getEmail()));

            $token = $tokenGenerator->generateToken();
            $user->setAccountValidationToken($token);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Compte créé avec succès'
            );

            $body = 'email/account-activation.html.twig';
            $context = [
                'user' => $user
            ];
            $mailer->sendMessage('from@email.com', $user->getEmail(), 'Account Activation', $body, $context);

            $this->addFlash(
                'success',
                'Un mail d\'activation a été envoyé à l\'adresse '.$user->getEmail()
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account/activate/{accountValidationToken}", name="app_account-activate", methods={"GET"})
     */
    public function activate(Request $request, User $user = null, DelayTokenVerificator $tokenVerificator, GuardAuthenticatorHandler $guardAuthenticatorHandler, LoginFormAuthenticator $loginFormAuthenticator) : Response
    {
        if(!$user || $tokenVerificator->isValidToken($user->getCreatedAt()) === false) {
            $this->addFlash(
                'danger',
                'Expired token'
            );
            return $this->redirectToRoute('app_login');
            // TODO: faire méthode pour renvoyer le token par mail pour ne pas se retrouver bloqué
            // TODO: la méthode actuelle renvoie uniquement le mail au cas ou l'on a perdu le mail mais en cas de token expiré, il en faut un nouveau
        }

        $user->setAccountValidationToken(null);
        $user->setIsActive(true);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash(
            'success',
            'Compte activé avec succès'
        );

        return $guardAuthenticatorHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $loginFormAuthenticator,
            'main'
        );
    }
}
