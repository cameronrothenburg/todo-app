<?php

namespace App\Exceptions;

use Exception;

class InvalidMIMETypeException extends Exception
{
    public function render($request){
        if ($request->is('api/*')) {
            return response()->json([
                'errors' => 'The MIME type is not supported',
            ], 422);
        }
    }
}
