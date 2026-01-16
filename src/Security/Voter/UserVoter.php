<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    public const VIEW = 'USER_VIEW';
    public const CREATE = 'USER_CREATE';
    public const EDIT = 'USER_EDIT';
    public const DELETE = 'USER_DELETE';
    public const LIST = 'USER_LIST';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Supports list without subject
        if (in_array($attribute, [self::LIST, self::CREATE])) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // User must be logged in
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Only ROLE_ADMIN can manage users
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return false;
        }

        // Admin can do everything
        return match($attribute) {
            self::LIST, self::CREATE, self::VIEW, self::EDIT => true,
            self::DELETE => $subject !== $user, // Cannot delete yourself
            default => false,
        };
    }
}
