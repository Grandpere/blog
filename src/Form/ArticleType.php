<?php

namespace App\Form;

use App\Entity\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('excerpt', TextareaType::class, [
                'label' => 'Resume',
                'attr' => [
                    'rows' => 2,
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Content',
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'label' => 'Cover Image',
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'asset_helper' => true,
            ])
            ->add('isActive')
            /*
            ->add('tags', null, [
                'choice_label' => 'title',
                'expanded' => true,
            ])*/
            ->add('tags', TagsType::class, [
                'help' => 'Each tag must br separate by comma ","'
            ])
            // TODO: voir si bug dans API avec champ tag ajouté
            // TODO BUG: lors de l'ajout via API pas d'author_id
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
