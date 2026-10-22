<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\Dto\Request\RequestingUser;
use App\User\Domain\Entity\UserRole;
use App\User\Infrastructure\Models\UserModel;
use Illuminate\Http\Request;

final class SanctumRequestingUserProvider implements RequestingUserProviderInterface
{
    public function fromRequest(Request $request): RequestingUser
    {
        /** @var UserModel $user */
        $user = $request->user();

        return new RequestingUser(
            id: $user->id,
            isAdmin: $user->role === UserRole::Admin,
        );
    }
}
