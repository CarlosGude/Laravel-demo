<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class IndexController extends Controller
{

    public function __invoke(): string
    {
        return 'Hello World!';
    }
}
