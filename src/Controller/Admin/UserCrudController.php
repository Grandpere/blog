<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCrudController extends AbstractCrudController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email'),
            ArrayField::new('roles'),
            TextField::new('plainPassword')->hideOnIndex()->hideOnDetail(),
            TextField::new('firstName'),
            TextField::new('lastName'),
            AvatarField::new('avatar')->hideOnIndex(),
            UrlField::new('website')->hideOnIndex(),
            UrlField::new('twitter')->hideOnIndex(),
            UrlField::new('linkedin')->hideOnIndex(),
            UrlField::new('github')->hideOnIndex(),
            UrlField::new('stackoverflow')->hideOnIndex(),
            BooleanField::new('isActive', 'Active'),
            DateTimeField::new('lastLogin')->hideOnForm(),
            AssociationField::new('articles')->hideOnForm(),
            TextField::new('accountValidationToken')->onlyOnDetail(),
            DateTimeField::new('validationTokenCreatedAt')->onlyOnDetail(),
            TextField::new('resetPasswordToken')->onlyOnDetail(),
            DateTimeField::new('passwordTokenCreatedAt')->onlyOnDetail(),
        ];

        if(Crud::PAGE_EDIT !== $pageName) {
            $fields[] = DateTimeField::new('agreedTermsAt', 'Agreed terms');
        }

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        $impersonate = Action::new('impersonate', 'Impersonate')
            ->linkToRoute('web_account_index', function (User $entity) {
               return [
                    'id' => $entity->getId(),
                   '_switch_user' => $entity->getEmail()
               ];
            })
        ;

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $impersonate)
            ;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function encodePassword($user)
    {
        if(!$user instanceof User || !$user->getPlainPassword()) {
            return;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
    }
}