<?php

namespace App\Console\Commands;

use App\User\Domain\Repository\UserRepositoryInterface;
use Illuminate\Console\Command;

class PromoteUserToAdminCommand extends Command
{
    protected $signature = 'user:make-admin {email : The email of an already registered user}';

    protected $description = 'Promote an already registered user to the admin role';

    public function handle(UserRepositoryInterface $userRepository): int
    {
        $email = $this->argument('email');

        if (!is_string($email)) {
            throw new \UnexpectedValueException('The email argument must be a string.');
        }

        $user = $userRepository->findByEmail($email);

        if ($user === null) {
            $this->error("No user found with email \"{$email}\".");

            return self::FAILURE;
        }

        $userRepository->update($user->promoteToAdmin());

        $this->info("User \"{$email}\" is now an admin.");

        return self::SUCCESS;
    }
}
