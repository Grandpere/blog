<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('content'),
            DateTimeField::new('createdAt'),
            DateTimeField::new('updatedAt'),
            BooleanField::new('isActive', 'Active'),
            BooleanField::new('isReported', 'Reported'),
            BooleanField::new('isModerate', 'Moderate'),
            AssociationField::new('article')->hideOnIndex(),
            TextField::new('authorName')->hideOnIndex(),
            EmailField::new('authorEmail')->hideOnIndex(),
            UrlField::new('authorWebsite')->hideOnIndex(),
            IntegerField::new('depth'),
            AssociationField::new('parent'),
            AssociationField::new('childrens')
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('isActive')
            ->add('isReported')
            ->add('isModerate')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('authorName')
            ->add('authorEmail')
            ->add('authorWebsite')
            ->add('depth')
            ;
    }
}
