<?php


namespace App\EventSubscriber;


use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminCommentUpdateSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityUpdatedEvent::class => ['deactivateChildrenComments'],
        ];
    }

    public function deactivateChildrenComments(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if(!$entity instanceof Comment) {
            return;
        }
        if($entity->getIsModerate()) {
            $childrens = $entity->getChildrens();
            // deactivate childs
            foreach ($childrens as $children) {
                $children->setIsActive(false);

                $childs = $children->getChildrens();
                // deactivate child's childs
                foreach ($childs as $child) {
                    $child->setIsActive(false);
                }
            }
        }
    }
}