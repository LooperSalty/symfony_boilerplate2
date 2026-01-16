<?php

namespace App\Security\Voter;

use App\Entity\Client;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ClientVoter extends Voter
{
    public const VIEW = 'CLIENT_VIEW';
    public const CREATE = 'CLIENT_CREATE';
    public const EDIT = 'CLIENT_EDIT';
    public const DELETE = 'CLIENT_DELETE';
    public const LIST = 'CLIENT_LIST';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::LIST, self::CREATE])) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Client;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Admin and Manager can access clients
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_MANAGER', $roles)) {
            return true;
        }

        return false;
    }
}
