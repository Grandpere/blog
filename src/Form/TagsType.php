<?php


namespace App\Form;


use App\Form\DataTransformer\TagsTransformer;
use App\Repository\TagRepository;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class TagsType extends AbstractType
{
    /**
     * @var TagRepository
     */
    private $tagRepository;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(TagRepository $tagRepository, RouterInterface $router)
    {
        $this->tagRepository = $tagRepository;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addModelTransformer(new CollectionToArrayTransformer(), true)
            ->addModelTransformer(new TagsTransformer($this->tagRepository), true)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('attr', [
            'class' => 'tag-input',
            'data-autocomplete-url' => $this->router->generate('tag-autocomplete')
        ]);
        $resolver->setDefault('required', false);
    }

    public function getParent() : string
    {
        return TextType::class;
    }
}