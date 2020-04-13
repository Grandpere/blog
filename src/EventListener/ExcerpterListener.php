<?php


namespace App\EventListener;


use App\Entity\Article;
use App\Utils\Excerpter;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class ExcerpterListener
{
    private $excerpter;

    public function __construct(Excerpter $excerpter)
    {
        $this->excerpter = $excerpter;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->excerptify($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->excerptify($args);
    }

    public function excerptify(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Article) {
            $excerpt = $this->excerpter::excerptify($entity->getContent());
        } else {
            return;
        }
        $entity->setExcerpt($excerpt);
    }
}