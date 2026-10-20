<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class IndexController extends Controller
{

    public function __invoke(): JsonResponse
    {
        return response()->json(['message' => 'Hello World!']);
    }
}
