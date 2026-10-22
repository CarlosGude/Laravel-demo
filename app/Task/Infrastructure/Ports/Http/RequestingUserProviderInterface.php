<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\Dto\Request\RequestingUser;
use Illuminate\Http\Request;

interface RequestingUserProviderInterface
{
    public function fromRequest(Request $request): RequestingUser;
}
