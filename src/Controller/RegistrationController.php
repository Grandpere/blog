<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserForgottenType;
use App\Repository\UserRepository;
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

    /**
     * @Route("/account/resend-token", name="app_resend-token", methods={"GET", "POST"})
     */
    public function resendToken(Request $request, UserRepository $userRepository, Mailer $mailer) : Response
    {
        $form = $this->createForm(UserForgottenType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $user = $userRepository->findOneByEmail($email);
            if(!$user) {
                $this->addFlash(
                    'danger',
                    'Email Inconnu'
                );
                return $this->redirectToRoute('app_resend-token');
            }
            if($user->getAccountValidationToken()) {
                $body = 'email/account-activation.html.twig';
                $context = [
                    'user' => $user
                ];
                $mailer->sendMessage('from@email.com', $user->getEmail(), 'Activation Mail', $body, $context);

                $this->addFlash(
                    'success',
                    'Un nouveau mail d\'activation a été envoyé sur votre email '.$user->getEmail()
                );
                return $this->redirectToRoute('app_resend-token');
            }
            $this->addFlash(
                'danger',
                'Votre compte a déja été activé, vous pouvez vous connecter'
            );
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/resend-token.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
