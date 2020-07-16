<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\View;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin_index")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(CrudUrlGenerator::class)->build();

        return $this->redirect($routeBuilder->setController(ArticleCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Blog Api Admin Interface');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard home', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'icon class', EntityClass::class);

        yield MenuItem::subMenu('Blog', 'far fa-newspaper')->setSubItems([
            MenuItem::linkToCrud('Articles','fas fa-newspaper', Article::class),
            MenuItem::linkToCrud('Tags','fas fa-tag', Tag::class),
            MenuItem::linkToCrud('Comments','fas fa-comment', Comment::class),
        ]);

        yield MenuItem::subMenu('Statistics', 'fas fa-info-circle')->setSubItems([
            MenuItem::linkToCrud('Likes','fas fa-thumbs-up', Like::class),
            MenuItem::linkToCrud('Views','fas fa-eye', View::class),
        ]);

        yield MenuItem::subMenu('Users', 'fas fa-users')->setSubItems([
            MenuItem::linkToCrud('Users','fas fa-user', User::class),
        ]);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setAvatarUrl($user->getAvatar());
    }

}
