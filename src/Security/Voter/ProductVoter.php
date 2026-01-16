<?php

namespace App\Security\Voter;

use App\Entity\Product;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductVoter extends Voter
{
    public const VIEW = 'PRODUCT_VIEW';
    public const CREATE = 'PRODUCT_CREATE';
    public const EDIT = 'PRODUCT_EDIT';
    public const DELETE = 'PRODUCT_DELETE';
    public const LIST = 'PRODUCT_LIST';
    public const EXPORT = 'PRODUCT_EXPORT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Supports list, create, export without subject
        if (in_array($attribute, [self::LIST, self::CREATE, self::EXPORT])) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Product;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // User must be logged in
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Read access for all authenticated users
        if (in_array($attribute, [self::VIEW, self::LIST])) {
            return true;
        }

        // Write access (create, edit, delete, export) for ADMIN only
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;
    }
}
