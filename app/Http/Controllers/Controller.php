<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {
    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="TODO Application",
     *      description="Simple todo application with laravel"
     * )
     */
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
