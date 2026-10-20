<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Task Manager API',
    description: 'API for managing tasks and their categories.'
)]
#[OA\Server(
    url: '/',
    description: 'Default server'
)]
final class OpenApiInfo
{
}
