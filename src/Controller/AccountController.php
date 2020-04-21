<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForgottenType;
use App\Form\UserPasswordResetType;
use App\Repository\UserRepository;
use App\Utils\DelayTokenVerificator;
use App\Utils\Mailer;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/", name="web_account_index")
     */
    public function index(LoggerInterface $logger)
    {
        $logger->debug('Checking account page for '.$this->getUser()->getEmail());
        return $this->render('account/index.html.twig', [
        ]);
    }

    /**
     * @Route("/api/account", name="api_account")
     */
    public function accountApi()
    {
        $user = $this->getUser();

        return $this->json($user, 200, [], [
            'groups' => ['main'],
        ]);
    }

    /**
     * @Route("/forgotten-password", name="app_forgotten-password", methods={"GET","POST"})
     */
    public function forgottenPassword(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGenerator, Mailer $mailer)
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
                return $this->redirectToRoute('app_forgotten-password');
            }

            $token = $tokenGenerator->generateToken();
            $user->setResetPasswordToken($token);
            $user->setPasswordRequestedAt(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Demande de réinitialisation du mot de passe effectué avec succès'
            );

            $body = 'email/account-recovery.html.twig';
            $context = [
                'user' => $user
            ];
            $mailer->sendMessage('from@email.com', $user->getEmail(), 'Account Recovery', $body, $context);

            $this->addFlash(
                'success',
                'Un mail de réinitialisation a été envoyé à l\'adresse '.$user->getEmail()
            );

            return $this->redirectToRoute('app_login');
        }
        return $this->render('account/forgotten-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset-password/{resetPasswordToken}", name="app_reset-password", methods={"GET","POST"})
     */
    public function resetPassword(Request $request, User $user = null, UserPasswordEncoderInterface $passwordEncoder, DelayTokenVerificator $tokenVerificator)
    {
        if(!$user || $tokenVerificator->isValidToken($user->getPasswordRequestedAt()) === false) {
            $this->addFlash(
                'danger',
                'Expired token'
            );
            return $this->redirectToRoute('app_forgotten-password');
        }

        $form = $this->createForm(UserPasswordResetType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setResetPasswordToken(null);
            $user->setPasswordRequestedAt(null);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash(
                'success',
                'Mot de passe modifié avec succès'
            );

            return $this->redirectToRoute('app_login');
        }
        return $this->render('account/reset-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
