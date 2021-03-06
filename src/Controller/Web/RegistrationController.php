<?php

namespace App\Controller\Web;

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
        /** @var User $user */
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

            // be absolutely sure they agree
            if (true === $form['agreeTerms']->getData()) {
                $user->agreeTerms();
            }

            $user->setAvatar($gravatar->getGravatar($user->getEmail()));

            $token = $tokenGenerator->generateToken();
            $user->setAccountValidationToken($token);
            $user->setValidationTokenCreatedAt(new \DateTime());

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

        return $this->render('web/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activate/{accountValidationToken}", name="app_account-activate", methods={"GET"})
     */
    public function activate(Request $request, User $user = null, DelayTokenVerificator $tokenVerificator, GuardAuthenticatorHandler $guardAuthenticatorHandler, LoginFormAuthenticator $loginFormAuthenticator) : Response
    {
        if(!$user || $tokenVerificator->isValidToken($user->getValidationTokenCreatedAt()) === false) {
            $this->addFlash(
                'danger',
                'Expired token'
            );
            return $this->redirectToRoute('app_login');
        }

        $user->setAccountValidationToken(null);
        $user->setValidationTokenCreatedAt(null);
        $user->setIsActive(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

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
     * @Route("/resend-token", name="app_resend-token", methods={"GET", "POST"})
     */
    public function resendToken(Request $request, UserRepository $userRepository, Mailer $mailer, DelayTokenVerificator $tokenVerificator) : Response
    {
        $form = $this->createForm(UserForgottenType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $user = $userRepository->findOneBy([
                'email' => $email,
            ]);

            if(!$user) {
                $this->addFlash(
                    'danger',
                    'Email Inconnu'
                );
                return $this->redirectToRoute('app_resend-token');
            }
            if($user->getAccountValidationToken()) {
                // user found & uset have accountValidationToken (token valid or invalid)
                if($tokenVerificator->isValidToken($user->getValidationTokenCreatedAt()) === false) {
                    // generate new delay for activation
                    $user->setValidationTokenCreatedAt(new \DateTime());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();
                }

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

        return $this->render('web/registration/resend-token.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
