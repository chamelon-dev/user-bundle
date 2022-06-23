<?php

namespace Pantheon\UserBundle\Security\Voter;


use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Security\Rights\CheckRightsServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PageVoter extends Voter
{
    private $decisionManager;
    private $permissionService;

    public function __construct(
        AccessDecisionManagerInterface $decisionManager,
        CheckRightsServiceInterface $checkRightsService

    )
    {
        $this->decisionManager = $decisionManager;
        $this->checkRightsService = $checkRightsService;
    }

    protected function supports($attribute, $subject)
    {
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if($attribute=="IS_AUTHENTICATED_ANONYMOUSLY"){
            return true;
        }
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        $result = $this->checkRightsService->hasPermission($user,$attribute);
        return $result;
    }

}