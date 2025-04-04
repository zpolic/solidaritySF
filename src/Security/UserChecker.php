<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use App\Entity\User;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Vaš nalog je deaktiviran.');
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('Email adresa nije verifikonova.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // You can leave this empty if you don't need post-auth checks
    }
}