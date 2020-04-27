<?php


namespace App\Form\DataTransformer;


use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TagsTransformer implements DataTransformerInterface
{
    /**
     * @var TagRepository
     */
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    // data from DB, file, url, etc...
    public function transform($value) : string
    {
        /** @var Tag $value */
        return implode(',', $value);
    }

    // submit form
    public function reverseTransform($string) : array
    {
        $titles = array_unique(array_filter(array_map('trim', explode(',', $string))));
        $tags = $this->tagRepository->findBy([
            'title' => $titles
        ]);
        $newTitles = array_diff($titles, $tags);
        foreach ($newTitles as $title) {
            $tag = new Tag();
            $tag->setTitle($title);
            $tags[] = $tag;
        }
        return $tags;
    }

}