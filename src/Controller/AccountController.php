<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/account", name="web_account_")
 * @IsGranted("ROLE_USER")
 */
class AccountController extends BaseController
{
    /**
     * @Route("/", name="index")
     */
    public function index(LoggerInterface $logger)
    {
        $logger->debug('Checking account page for '.$this->getUser()->getEmail());
        return $this->render('account/index.html.twig', [
        ]);
    }

    /**
     * @Route("/validate/{id}", name="validate")
     * @IsGranted("ROLE_ADMIN")
     */
    public function validate($id)
    {
        
    }
}
