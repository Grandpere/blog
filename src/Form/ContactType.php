<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Name',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name',
                    ]),
                ],
            ])
            ->add('email', null, [
                'label' => 'Email',
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email',
                    ]),
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ]),
                ],
            ])
            ->add('subject', ChoiceType::class, [
                'label' => 'Subject',
                'placeholder' => 'Choose One',
                'choices' => [
                    'Job' => 'job',
                    'Suggestions' => 'suggestions',
                    'Bug' => 'bug',
                    'Other' => 'other',
                ],
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 9,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a message',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
