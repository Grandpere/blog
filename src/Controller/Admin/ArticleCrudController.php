<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnDetail(),
            TextField::new('title'),
            SlugField::new('slug')->setTargetFieldName('title')->hideOnIndex()->hideOnForm(),
            TextEditorField::new('content'),
            TextareaField::new('excerpt')->hideOnIndex(),
            ImageField::new('coverImage')->setLabel('Cover')->setBasePath('/uploads/articles/covers')->hideOnForm(),
            ImageField::new('imageFile')->setFormType(VichImageType::class)->setLabel('Cover')->hideOnIndex()->hideOnDetail(),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
            BooleanField::new('isActive', 'Active'),
            BooleanField::new('isReported', 'Reported'),
            BooleanField::new('isModerate', 'Moderated'),
            AssociationField::new('author')->hideOnIndex()->autocomplete(),
            AssociationField::new('views')->hideOnForm()->hideOnIndex(),
            AssociationField::new('likes')->hideOnForm()->hideOnIndex(),
            AssociationField::new('comments')->hideOnForm()->hideOnIndex()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('isActive')
            ->add('isReported')
            ->add('isModerate')
            ->add('coverImage')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('author')
            ;
    }
}
