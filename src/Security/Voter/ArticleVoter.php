<?php

namespace App\Security\Voter;

use App\Entity\Article;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleVoter extends Voter
{
    CONST EDIT = 'edit';
    CONST CREATE = 'create';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::CREATE])
            && $subject instanceof Article;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Article object, thanks to `supports()`
        /** @var Article $article */
        $article = $subject;

        switch ($attribute) {
            case self::EDIT:
                // this is the author!
                if($article->getAuthor() == $user) {
                    return true;
                }
                if ($this->security->isGranted('ROLE_ADMIN_ARTICLE')) {
                    return true;
                }
                return false;

            case self::CREATE:
                if ($this->security->isGranted('ROLE_CREATE_ARTICLE')) {
                    return true;
                }
                return false;
        }

        return false;
    }
}
