<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class IndexController extends Controller
{

    public function __invoke(string $name = 'Carlos'): JsonResponse
    {
        return response()->json([
            'message' => str_replace('{name}', ucfirst($name), 'Hello {name}!')
        ]);
    }
}
