<?php

namespace App\Exceptions;

use Exception;

class ModelNotFoundException extends Exception
{
   public function report(){
       return false;
   }

   public function render($request){
       if ($request->is('api/*')) {
           return response()->json([
               'errors' => 'Item not found.'
           ], 404);
       }
   }
}
